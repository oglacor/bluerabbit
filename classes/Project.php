<?php 
require_once get_template_directory() . '/libs/pdfparser/Smalot/PdfParser/Parser.php';
spl_autoload_register(function ($class) {
    // Only load Smalot PDFParser classes
    if (strpos($class, 'Smalot\\PdfParser\\') === 0) {
        $base_dir = get_template_directory() . '/libs/pdfparser/';
        $class_path = str_replace('\\', '/', $class) . '.php';
        $file = $base_dir . $class_path;
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

use Smalot\PdfParser\Parser;
$parser = new Parser();

class Project {
    public function __construct() {
        add_action('wp_ajax_br_ai_upload_file', [$this, 'handle_upload']);
        add_action('wp_ajax_br_update_project', [$this, 'update_project']);
    }

    public function handle_upload() {
        error_log("🐛 br_ai_upload_file called");

        try {
            global $wpdb;
            $current_user = wp_get_current_user();

            // Sanitize input
            $project_id = intval($_POST['br_project_id']);
            $new_project_name = sanitize_text_field($_POST['br_new_project_name'] ?? '');
            $new_project_desc = sanitize_textarea_field($_POST['br_new_project_description'] ?? '');
            $story_meta = [
                'narrative' => sanitize_text_field($_POST['br_story_narrative'] ?? ''),
                'tone'      => sanitize_text_field($_POST['br_story_tone'] ?? ''),
                'audience'  => sanitize_text_field($_POST['br_story_audience'] ?? ''),
                'subject'   => sanitize_text_field($_POST['br_story_subject'] ?? ''),
            ];

            // Create project if needed
            if (!$project_id && $new_project_name) {
                $wpdb->insert("{$wpdb->prefix}br_projects", [
                    'user_id' => $current_user->ID,
                    'name' => $new_project_name,
                    'description' => $new_project_desc,
                ]);
                $project_id = $wpdb->insert_id;
            }

            // Handle file upload
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            $uploaded_file = $_FILES['br_pdf_file'] ?? null;

            if (!$uploaded_file || $uploaded_file['error'] !== UPLOAD_ERR_OK) {
                wp_send_json_error('Upload failed.');
            }

            $upload_overrides = ['test_form' => false];
            $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

            if (!$movefile || isset($movefile['error'])) {
                wp_send_json_error($movefile['error'] ?? 'Unknown error.');
            }

            // Save file in br_ai_files
            $wpdb->insert("{$wpdb->prefix}br_ai_files", [
                'user_id' => $current_user->ID,
                'file_name' => basename($movefile['file']),
                'file_path' => $movefile['file'],
            ]);
            $file_id = $wpdb->insert_id;
            $wpdb->insert("{$wpdb->prefix}br_project_files", [
                'project_id' => $project_id,
                'file_id' => $file_id,
            ]);

            $parser = new Parser();
            $pdf = $parser->parseFile($movefile['file']);

            $text = $pdf->getText();
            $chunks = $this->chunk_pdf_text($text);
            // Send chunks to OpenAI
            $milestones = [];
            foreach ($chunks as $key=> $chunk) {
                $chunk_text = $chunk['chapter_title'] ?? '';
                $story_meta['chapter_title'] = "Part " . ($key + 1);
               // error_log("📤 Sending chunk to OpenAI: " . substr($chunk_text, 0, 100));
                $wpdb->insert("{$wpdb->prefix}br_ai_temp_results", [
                    'user_id' => $current_user->ID,
                    'data' => print_r($chunk,true)
                ]);
                
                $ai_response = $this->send_to_openai($chunk_text, $new_project_name, $story_meta);
                //$ai_response = "";
                $wpdb->insert("{$wpdb->prefix}br_ai_temp_results", [
                    'user_id' => $current_user->ID,
                    'data' => is_string($ai_response) ? $ai_response : json_encode($ai_response)
                ]);
                
                error_log("📥 OpenAI raw response: " . substr(print_r($ai_response, true), 0, 500));

                // Try decoding response (could be JSON string)

                $raw_responses[] = $ai_response;
                if (is_string($ai_response)) {
                    $clean_json = $this->sanitize_openai_json_response($ai_response);
                    error_log("🧪 Clean JSON: " . substr($clean_json, 0, 500));

                    $decoded = json_decode($clean_json, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log("❌ JSON error: " . json_last_error_msg());
                        wp_send_json_error(['error' => 'OpenAI returned invalid JSON', 'raw' => $ai_response]);
                    }

                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['milestones'])) {
                        foreach ($decoded['milestones'] as $milestone) {
                            $milestones[] = $milestone;
                        }
                    } else {
                         error_log("⚠️ No milestones key found in decoded output.");
                        $milestones[] = [
                            'quest_title' => 'Chunk ' . $chunk['chunk_index'] . ' parsing failed',
                            'quest_content' => 'Could not decode AI response.',
                            'steps' => []
                        ];
                    }
                }else{
                    error_log("❗ OpenAI response was not a string.");
                }
                
            }
            $final_structure = [
                'milestones' => $milestones
            ];
            error_log("✅ Milestones count: " . count($milestones));

            if (empty($milestones)) {
                wp_send_json_error(['error' => 'No milestones generated from any chunk.']);
            }

            // Save in temp_results for preview
            $wpdb->insert("{$wpdb->prefix}br_ai_temp_results", [
                'user_id' => $current_user->ID,
                'data' => json_encode($final_structure)
            ]);

            $temp_id = $wpdb->insert_id;

            $redirect_url = site_url('/project#chunks?result_id=' . $temp_id);

            wp_send_json_success([
                'redirect' => $redirect_url
            ]);

            error_log("✅ File uploaded and linked to project ID: $project_id");

        } catch (Exception $e) {
            error_log("❌ Exception: " . $e->getMessage());
            wp_send_json_error('Server error: ' . $e->getMessage());
        } 
    }

    private function send_to_openai($text, $project_name = '', $story_meta= []) {
        $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : null;

        if (!$api_key) {
            return ['error' => 'OpenAI API key missing'];
        }

        $narrative = $story_meta['narrative'] ?? 'epic fantasy';
        $tone      = $story_meta['tone'] ?? 'mentor-like';
        $audience  = $story_meta['audience'] ?? 'high school students';
        $subject   = $story_meta['subject'] ?? 'general education';
        $chapter_title = $story_meta['chapter_title'] ?? '';

        $instructions = <<<PROMPT
            You are a gamification designer for interactive education experiences.
            Your task is to turn the content below into a JSON array of gamified milestones , using the following structure and only outputting VALID JSON.

            Return 6-10 milestones from this content. Each one should represent a unique learning moment or idea.
            Each milestone should represent one focused concept or scene in the educational narrative.

            Each milestone is a "quest", and contains XP, bloo (currency), level, and steps. Each step should be written like part of a game or story — use character dialogue, open challenges, or narrative messaging. 
            You can invent system messages, characters, and backgrounds if needed.

            Example milestone titles: 
            - "The Philosopher's Dilemma"
            - "Unlocking the Secrets of Well-being"
            - "Exploring the Temple of Definitions"

            Example step types:
            - dialogue: Character explains something in-game
            - sys-message: System gives feedback or instruction
            - open: User types something to continue
            - win/fail: Used to complete or retry the milestone

            Do NOT explain. Do NOT add markdown blocks like \`\`\`. Just output the JSON structure.

            The JSON structure MUST follow this:

            {
            "milestones": [
                {
                "quest_title": "",
                "quest_type": "quest",
                "quest_content": "",
                "mech_badge": "",
                "mech_level": 1,
                "mech_xp": 1000,
                "mech_bloo": 100,
                "milestone_x": 0,
                "milestone_y": 0,
                "milestone_z": 0,
                "steps": [
                    {
                    "step_title": "",
                    "type": "dialogue | sys-message | open | win | fail",
                    "step_content": "",
                    "step_character_name": "",
                    "step_character_image": "",
                    "step_attach": "left | right",
                    "step_background": "",
                    "step_order": 1,
                    "quest_id": 0
                    }
                ]
                }
            ]
            }

            Narrative style: {$narrative}
            Tone: {$tone}
            Audience: {$audience}
            Subject: {$subject}

            Content:
            """
            {$chapter_title}
            """
        PROMPT;


        $body = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an assistant for gamified curriculum design.'],
                ['role' => 'user', 'content' => $instructions]
            ],
            'temperature' => 0.7,
        ];

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body' => json_encode($body),
            'timeout' => 45,
        ]);
        error_log("🧠 OpenAI request size: " . strlen($text) . " chars");

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);

        // TEMP: dump full OpenAI response for debugging
        if (!isset($data['choices'][0]['message']['content'])) {
            return [
                'error' => 'No response from OpenAI',
                'raw' => $data
            ];
        }

        return $data['choices'][0]['message']['content'];
    }
    public function br_render_ai_milestones_preview($json_string) {
        $data = json_decode($json_string, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['milestones'])) {
            return '<div class="error">Invalid milestone data.</div>';
        }

        $output = '<div class="br-ai-preview">';
        foreach ($data['milestones'] as $i => $milestone) {
            $output .= '<hr width="100%">';
            $output .= '<div class="milestone">';
            $output .= '<h3>📍 ' . ($milestone['quest_title']) . '</h3>';
            $output .= '<p>' . ($milestone['quest_content']) . '</p>';

            if (!empty($milestone['steps'])) {
                $output .= '<ol>';
                foreach ($milestone['steps'] as $step) {
                    $output .= '<li><h3>' . ($step['step_title']).'</h3>';
                    $output .= $step['step_content'];
                    $output .= '</li>';
                }
                $output .= '</ol>';
            }

            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }
    
    private function sanitize_openai_json_response($content) {
        $clean = trim($content);
        if (str_starts_with($clean, '```json')) {
            $clean = substr($clean, 7);
        }
        $clean = trim($clean, "` \n\r");
        return $clean;
    }
    private function chunk_pdf_text($text) {
        $chunks = [];

        // Regex splits on "Chapter" style headings and keeps the titles and content
        $chapter_splits = preg_split(
            '/\b(?:Chapter|CHAPTER|Cap[ií]tulo|CAP[IÍ]TULO)\s+\d+[^\n]*\n?/i',
            $text,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        // Now we loop in pairs: title + content
        if (count($chapter_splits) > 1) {
            for ($i = 0; $i < count($chapter_splits) - 1; $i += 2) {
                $chapter_title = trim($chapter_splits[$i]);
                $chapter_text  = trim($chapter_splits[$i + 1]);

                if (empty($chapter_text)) continue;

                // Optional: re-chunk large chapters
                $max_len = 12000;
                if (mb_strlen($chapter_text) > $max_len) {
                    $sub_parts = str_split($chapter_text, $max_len);
                    foreach ($sub_parts as $j => $sub_chunk) {
                        $chunks[] = [
                            'chunk_index' => count($chunks),
                            'chapter_title' => $chapter_title . ' (Part ' . ($j + 1) . ')',
                            'text' => $sub_chunk,
                        ];
                    }
                } else {
                    $chunks[] = [
                        'chunk_index' => count($chunks),
                        'chapter_title' => $chapter_title,
                        'text' => $chapter_text,
                    ];
                }
            }
        } else {
            // Fallback: split entire text by length
            $max_len = 12000;
            $parts = str_split($text, $max_len);
            foreach ($parts as $i => $chunk_text) {
                $chunks[] = [
                    'chunk_index' => $i,
                    'chapter_title' => null,
                    'text' => $chunk_text,
                ];
            }
        }

        error_log("🧩 Final chunk count: " . count($chunks));
        return $chunks;
    }
    public function get_project_by_id($project_id) {
        global $wpdb;
        $current_user = wp_get_current_user();

        $project = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_projects WHERE project_id = %d AND user_id = %d",
            $project_id, $current_user->ID
        ));
        return $project ? $project : null;
    }
    public function update_project() {
        global $wpdb;
        $current_user = wp_get_current_user();

        // Sanitize input
        $project_id = intval($_POST['project_id']);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');

        if (!$project_id || !$name) {
            wp_send_json_error('Invalid project data.');
        }

        // Update project
        $wpdb->update("{$wpdb->prefix}br_projects", [
            'name' => $name,
            'description' => $description,
        ], ['project_id' => $project_id, 'user_id' => $current_user->ID]);

        wp_send_json_success(['message' => 'Project updated successfully.']);
    }
}
