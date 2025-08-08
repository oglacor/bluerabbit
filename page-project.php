<?php include (get_stylesheet_directory() . '/header.php');
$br_project = new Project();
$project = get_project_by_id($_GET['project_id'] ?? 0);

?>
<div class="dashboard">
    <div class="dashboard-content white-bg">
        <div class="dashboard-container">
            <form id="br-ai-upload-form" enctype="multipart/form-data">

                <h1 class="dashboard-title">
                    <?php if(isset($project)){ ?>
                        <?php _e("Edit Project","bluerabbit"); ?>
                    <?php }else{ ?>
                        <?php _e("New Project","bluerabbit"); ?>
                    <?php }?>
                </h1>
                <div class="dashboard-grid">
                    <div class="dashboard-input-field-container" style="grid-column: 6 / span 7; grid-row: 1 / span 2;">
                        <h3 class="dashboard-grid-cell-headline"><?= __("Item name","bluerabbit");?></h3>
                        <?php if(isset($i)) { ?><input type="hidden" id="the_item_id" value="<?= $i->item_id; ?>"><?php } ?>
                        <input type="hidden" id="the_item_type" value="<?= (isset($i) && $i->item_type) ? $i->item_type : 'consumable'; ?>">
                        <input class="form-ui font _30 w-full" type="text" value="<?= isset($i) ? $i->item_name : ""; ?>" id="the_item_name">
                    </div>

                </div>

            </form>
        </div>
    </div>
</div>

<h2>1. Choose or Create a Project</h2>

<div id="br-project-selection">
    <p><strong>Create a New Project (optional):</strong></p>
    <input type="text" name="br_new_project_name" placeholder="Project name">
    <textarea name="br_new_project_description" placeholder="Project description"></textarea>

    <h1>AI Document Upload</h1>

    <form id="br-ai-upload-form" enctype="multipart/form-data">
        <label for="br_project_id">Select Project:</label>
        <select name="br_project_id" id="br_project_id" required>
            <option value="0">-- Choose a Project --</option>
            <?php
            global $wpdb;
            $projects = $wpdb->get_results("SELECT project_id, name FROM {$wpdb->prefix}br_projects WHERE user_id = {$current_user->ID}");
            foreach ($projects as $project) {
                echo "<option value='{$project->project_id}'>{$project->name}</option>";
            }
            ?>
        </select>

        <label for="br_pdf_file">Upload PDF File:</label>
        <input type="file" name="br_pdf_file" id="br_pdf_file" accept=".pdf" required />


        <div id="br-ai-preview" style="margin-top: 30px;"></div>

        <h2>2. Define Story Style</h2>
        <p>These inputs help the AI generate quests in the tone and theme you want.</p>

        <label for="br_story_narrative">Narrative Style:</label>
        <input type="text" name="br_story_narrative" id="br_story_narrative" placeholder="e.g. epic fantasy, sci-fi mystery">

        <label for="br_story_tone">Tone of Voice:</label>
        <select name="br_story_tone" id="br_story_tone">
            <option value="">-- Choose Tone --</option>
            <option value="mentor-like">Mentor-like</option>
            <option value="playful">Playful</option>
            <option value="serious">Serious</option>
            <option value="humorous">Humorous</option>
        </select>

        <label for="br_story_audience">Audience (Age/Level):</label>
        <input type="text" name="br_story_audience" id="br_story_audience" placeholder="e.g. 10th grade students">

        <label for="br_story_subject">Course Subject:</label>
        <input type="text" name="br_story_subject" id="br_story_subject" placeholder="e.g. Biology, World History">

        
        <input type="hidden" name="action" value="br_ai_upload_file" />
        <button type="submit">Upload and Preview</button>
    </form>
    
</div>

<script>
jQuery(document).ready(function($) {
    $('#br_project_id').on('change', function() {
        if ($(this).val() === '') {
            $('#br-new-project-fields').show();
            $('#br_new_project_name').prop('required', true);
        } else {
            $('#br-new-project-fields').hide();
            $('#br_new_project_name').prop('required', false);
        }
    });
    $('#br-ai-upload-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: runAJAX.ajaxurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#br-ai-preview').html('<p>Uploading and processing file...</p>');
            },
            success: function(response) {
                const data = response.data;
                $('#br-ai-preview').html(`
                    <h3>Extracted Text Preview:</h3>
                    <pre style="max-height: 300px; overflow-y: auto;">${data.preview_text}</pre>
                    <p><strong>Project ID:</strong> ${data.project_id} | <strong>File ID:</strong> ${data.file_id}</p>
                    <button id="br-ai-summarize" data-file="${data.file_id}" data-project="${data.project_id}">Summarize with AI</button>
                `);

                if (response.success && response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    alert("Upload complete, but no redirect given.");
                }



            },
            error: function(xhr) {
                $('#br-ai-preview').html('<p>Error uploading file.</p>');
            }
        });
    });
});
</script>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>
