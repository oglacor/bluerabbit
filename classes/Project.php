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
        add_action('wp_ajax_br_process_chunk', [$this, 'send_to_openai']);
    }

    public function handle_upload() {
        error_log("🐛 br_ai_upload_file called");

        try {
            global $wpdb;
            $current_user = wp_get_current_user();

            // Sanitize input
            $posted_project_id = intval($_POST['br_project_id']);
            $project_name = sanitize_text_field($_POST['br_project_name'] ?? '');
            $project_desc = sanitize_textarea_field($_POST['br_project_description'] ?? '');
            $story_meta = [
                'custom_prompt' => sanitize_text_field($_POST['br_custom_prompt'] ?? ''),
                'narrative' => sanitize_text_field($_POST['br_story_narrative'] ?? ''),
                'tone'      => sanitize_text_field($_POST['br_story_tone'] ?? ''),
                'audience'  => sanitize_text_field($_POST['br_story_audience'] ?? ''),
                'subject'   => sanitize_text_field($_POST['br_story_subject'] ?? ''),
            ];

			$project_sql="INSERT INTO {$wpdb->prefix}br_projects (`project_id`,`user_id`, `name`, `description`) VALUES (%d, %d, %s, %s) ON DUPLICATE KEY UPDATE `name`=%s , `description`=%s";
			$project_sql = $wpdb->prepare( $project_sql, $posted_project_id, $current_user->ID, $project_name, $project_desc, $project_name, $project_desc);
			$project_sql = $wpdb->query($project_sql);
            $project_id = $wpdb->insert_id;


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
            
            foreach ($chunks as $c) {
                $wpdb->insert("{$wpdb->prefix}br_ai_chunks", [
                    'file_id'   => $file_id,
                    'chunk_index'   => $c['chunk_index'],
                    'chunk_text'          => $c['text'],
                    'chars'         => $c['chars'],
                    'est_tokens'    => $c['est_tokens'],
                    'status'        => 'ready',
                    'created_at'    => current_time('mysql'),
                    'project_id' => $project_id,
                    'user_id' => $current_user->ID,
                ]);
            }



                /*
                    //$ai_response = $this->send_to_openai($chunk_text, $new_project_name, $story_meta);
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
                */
            /*
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
            */
            $redirect_url = site_url('/document-chunks?project_id=' . $project_id);
            wp_send_json_success([
                'redirect' => $redirect_url
            ]);
            error_log("✅ File uploaded and linked to project ID: $project_id");
        } catch (Exception $e) {
            error_log("❌ Exception: " . $e->getMessage());
            wp_send_json_error('Server error: ' . $e->getMessage());
        } 
    }

    public function send_to_openai($text=null, $p_story_meta = []) {
        global $wpdb;
        $current_user = wp_get_current_user();
        error_log('AJAX reached');

        if($p_story_meta){
            $chunk_meta = [
                'custom_prompt' => $p_story_meta['custom_prompt'] ?? '',
                'narrative' => $p_story_meta['narrative'] ?? 'epic fantasy',
                'tone'      => $p_story_meta['tone'] ?? 'mentor-like',
                'audience'  => $p_story_meta['audience'] ?? 'high school students',
                'subject'   => $p_story_meta['subject'] ?? 'general education',
                'min_milestones'   => $p_story_meta['min_milestones'] ?? 5,
            ];
        } else {
            $chunk_meta = [];
        }

        $gpt_model = ($_POST['gpt_model'] ? $_POST['gpt_model'] : "gpt-3.5-turbo");
        $chunk_text = ($_POST['br_chunk_text'] ? $_POST['br_chunk_text'] : $text);
        $custom_prompt = ($_POST['br_custom_prompt'] ? $_POST['br_custom_prompt'] : $p_story_meta['custom_prompt'] ?? '');
        $min_milestones = ($_POST['br_minimum_milestones'] ? $_POST['br_minimum_milestones'] : $p_story_meta['min_milestones'] ?? 5);
        $narrative = ($_POST['br_story_narrative'] ? $_POST['br_story_narrative'] : $p_story_meta['narrative'] ?? '');
        $tone = ($_POST['br_story_tone'] ? $_POST['br_story_tone'] : $p_story_meta['tone'] ?? '');
        $audience = ($_POST['br_story_audience'] ? $_POST['br_story_audience'] : $p_story_meta['audience'] ?? '');
        $subject = ($_POST['br_story_subject'] ? $_POST['br_story_subject'] : $p_story_meta['subject'] ?? '');
        $br_chunk_id = ($_POST['br_chunk_id'] ?? 0);
        $br_project_id = ($_POST['br_project_id'] ?? 0);

        $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : null;

        if (!$api_key) {
            return ['error' => 'OpenAI API key missing'];
        }


        $instructions = <<<PROMPT
            
            Your task is to turn the content below into a Dialouge between characters explaining content from the uploaded text to the user. 
            
            After creating the dialogue, you will return a JSON array of {$min_milestones} milestones with 7 steps each , using the following structure and only outputting VALID JSON. Each milestone should be a significant part of the conversation and such conversation should be relevant to the content of the chunk.

            Each step is one dialogue of that conversation. 
           
            Do NOT explain. Do NOT explain. Do NOT explain. 

            Create at least {$min_milestones} milestones with 7 steps each. Make sure to use the content of the chunk to create the dialogue and narrative content. The dialogue and story must be relevant to the subject matter of the chunk.

            Each milestone should represent a significant concept or section from the content. Each milestone should have a clear title and a brief description of its purpose.
            Each milestone contains XP, bloo (currency), level, and steps. Each step should be written like part of a game or story use character dialogue, open challenges, or narrative messaging. 
            You can invent system messages and characters if needed.

            Example milestone titles: 
            - \"The Philosopher\'s Dilemma\"
            - \"Unlocking the Secrets of Well-being\"
            - \"Exploring the Temple of Definitions\"

            Example step types:
            - dialogue: Character explains something in-game
            - sys-message: System gives feedback or instruction

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
            
            Do NOT add markdown blocks like \`\`\`. Just output the JSON structure.

            Here is the input from the designer of the course:
            {$custom_prompt}

            Narrative style: {$narrative}
            Tone: {$tone}
            Audience: {$audience}
            Subject: {$subject}

            Content:
            """
            $chunk_text
            """
        PROMPT;

        $body = [
            'model' => $gpt_model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are an assistant for learning experience design.'],
                ['role' => 'user', 'content' => $instructions]
            ],
            'temperature' => 0.5,
        ];

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body' => json_encode($body),
            'timeout' => 45,
        ]);

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
        
        error_log("🧠 OpenAI request size: " . strlen($text) . " chars");
        error_log("🧠 OpenAI prompt: " . print_r($instructions, true));
        error_log("🧠 OpenAI request body: " . json_encode($body));
        error_log("🧠 OpenAI raw response: " . wp_remote_retrieve_body($response));
        error_log("🧠 OpenAI processing_style: {$narrative} - {$tone} - {$audience} - {$subject}");
        error_log("🧠 OpenAI gpt_model: $gpt_model");

        //`processed_chunk_id`, `chunk_id`, `project_id`, `processing_style`, `depth`, `prompt_settings`, `ai_response`, `token_usage`, `cost_estimate`, `created_at`
        $wpdb->insert("{$wpdb->prefix}br_ai_processed_chunks", [
            'user_id' => $current_user->ID,
            'chunk_id' => $br_chunk_id,
            'project_id' => $br_project_id,
            'prompt_settings' => print_r($instructions, true),
            'gpt_model' => "$gpt_model",
            'processing_style' => "{$narrative} - {$tone} - {$audience} - {$subject}",
            'ai_response' => is_string($data['choices'][0]['message']['content']) ? $data['choices'][0]['message']['content'] : json_encode($data['choices'][0]['message']['content'])
        ]);

        
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
    private function chunk_pdf_text(string $text, int $target_words = 2800, int $max_words_hardcap = 3400, int $overlap_paras = 1): array {
        $chunks = [];

        // 1) Normalize whitespace: keep blank lines as paragraph boundaries
        $text = preg_replace("/\r\n?/", "\n", $text);                 // normalize newlines
        $text = preg_replace("/[ \t]+\n/", "\n", $text);              // trim trailing spaces
        $text = preg_replace("/[ \t]+/", " ", $text);                  // collapse inline spaces
        $text = trim($text);

        if ($text === '') return $chunks;

        // 2) Split into raw paragraphs on blank lines (>=1 empty line)
        //    Also guard against PDFs that hard-wrap every line: we stitch single newlines inside a paragraph.
        $raw_paras = preg_split("/\n{2,}/", $text); // split on 2+ newlines

        $paras = [];
        foreach ($raw_paras as $p) {
            $p = trim($p);
            if ($p === '') continue;
            // Join single newlines that are likely hard wraps inside the same paragraph.
            // Heuristic: replace lone \n (not preceded by punctuation) with space.
            $p = preg_replace("/(?<![\.!?:])\n(?!\n)/u", " ", $p);
            // Collapse extra spaces again just in case
            $p = preg_replace("/\s+/u", " ", $p);
            $paras[] = $p;
        }

        if (empty($paras)) return $chunks;

        // Word counter helper
        $count_words = function(string $s): int {
            // Unicode-aware split on whitespace
            $arr = preg_split('/\s+/u', trim($s), -1, PREG_SPLIT_NO_EMPTY);
            return $arr ? count($arr) : 0;
        };

        // 3) Pack paragraphs into chunks respecting target/hardcap words
        $index = 0;
        $i = 0;
        $n = count($paras);

        while ($i < $n) {
            $chunk_paras = [];
            $words = 0;

            // Fill until target (soft), but never exceed hardcap (hard)
            while ($i < $n) {
                $p = $paras[$i];
                $pw = $count_words($p);

                if (empty($chunk_paras)) {
                    // First paragraph always goes in (even if big)
                    $chunk_paras[] = $p;
                    $words += $pw;
                    $i++;
                    continue;
                }

                if ($words + $pw <= $target_words || ($words < $max_words_hardcap && $words + $pw <= $max_words_hardcap)) {
                    $chunk_paras[] = $p;
                    $words += $pw;
                    $i++;
                } else {
                    break; // stop packing; this paragraph will start the next chunk
                }
            }

            // Overlap: carry the last K paragraphs into the next chunk’s start (without advancing $i for those)
            if ($overlap_paras > 0 && $i < $n && !empty($chunk_paras)) {
                $k = min($overlap_paras, count($chunk_paras));
                // Move pointer back by K so the next chunk starts with these again
                $i -= $k;
            }

            // Build chunk text
            $chunk_text = implode("\n\n", $chunk_paras);

            // Optional: finish on sentence boundary by peeking a little ahead (small polish)
            // (Disabled by default—paragraph boundaries usually suffice.)

            $chars = mb_strlen($chunk_text, 'UTF-8');
            $est_tokens = max(1, (int)ceil($chars / 4)); // rough estimate

            $chunks[] = [
                'chunk_index'   => $index++,
                'text'          => $chunk_text,
                'chars'         => $chars,
                'est_tokens'    => $est_tokens,
            ];
        }

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
    public function get_projects($user_id){
        global $wpdb;

        $projects = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_projects WHERE user_id = %d ORDER BY project_id DESC",
            $user_id
        ));
        return $projects ? $projects : [];
    }
    public function get_project_chunks($project_id) {
        global $wpdb;
        $current_user = wp_get_current_user();

        $chunks = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}br_ai_chunks WHERE project_id = %d AND user_id = %d ORDER BY chunk_index",
            $project_id, $current_user->ID
        ));
        return $chunks ? $chunks : [];
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
