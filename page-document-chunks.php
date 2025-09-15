<?php include (get_stylesheet_directory() . '/header.php');
global $br_project;
global $n;
$project_id = $_GET['project_id'] ?? 0;
$chunks = $br_project->get_project_chunks($project_id);
?>
<div class="dashboard">
    <div class="dashboard-content white-bg">
        <div class="dashboard-container">
            <?php if ($project_id) { ?>
                <?php if (!empty($chunks)) { ?>
                    <h3 class="dashboard-grid-cell-headline"><?= __("Document Chunks","bluerabbit"); ?></h1>
                    <div class="dashboard-sidebar">
                        <h2>Define Your Style</h2>
                        <p>Settings for OpenAI.</p>
                        <div class="input-group">
                            <label for="gpt_model">GPT Model:</label>
                            <select name="gpt_model" id="gpt_model" class="form-ui">
                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo (cheap, solid)</option>
                                <option value="gpt-4o-mini">GPT-4o Mini (fastest, cheapest)</option>
                                <option value="gpt-4o">GPT-4o (highest quality)</option>
                                <option value="gpt-4">GPT-4 (highest quality, most expensive)</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="br_custom_prompt">Custom Prompt:</label>
                            <textarea name="br_custom_prompt" id="br_custom_prompt" class="form-ui" rows="5"></textarea>
                        </div>
                        <div class="input-group">

                            <label for="br_story_narrative">Min Milestones:</label>
                            <input type="number" class="form-ui"  name="br_story_narrative" id="br_minimum_milestones" placeholder="5" value="5" min="1" max="10">
                        </div>
                        <div class="input-group">

                            <label for="br_story_narrative">Narrative Style:</label>
                            <input type="text" class="form-ui"  name="br_story_narrative" id="br_story_narrative" placeholder="e.g. epic fantasy, sci-fi mystery">
                        </div>
                        <div class="input-group">

                            <label for="br_story_tone">Tone of Voice:</label>
                            <select class="form-ui"  name="br_story_tone" id="br_story_tone">
                                <option value="">-- Choose Tone --</option>
                                <option value="mentor-like">Mentor-like</option>
                                <option value="playful">Playful</option>
                                <option value="serious">Serious</option>
                                <option value="humorous">Humorous</option>
                            </select>
                        </div>
                        <div class="input-group">

                            <label for="br_story_audience">Audience (Age/Level):</label>
                            <input class="form-ui"  type="text" name="br_story_audience" id="br_story_audience" placeholder="e.g. 10th grade students">
                        </div>
                        <div class="input-group">

                            <label for="br_story_subject">Course Subject:</label>
                            <input class="form-ui"  type="text" name="br_story_subject" id="br_story_subject" placeholder="e.g. Biology, World History">
                        </div>
                    </div>
                    <div class="dashboard-grid">
                        <?php foreach ($chunks as $key=> $chunk) { ?>
                            <div class="cell chunk">
                                <form class="br-process-chunk-form" enctype="multipart/form-data">
                                <textarea readonly class="form-ui br_chunk_text"  name="br_chunk_text" rows="10"><?= esc_html($chunk->chunk_text); ?></textarea>
                                <input type="hidden" name="br_chunk_id" value="<?= esc_html($chunk->chunk_id); ?>" />
                                <input type="hidden" name="br_project_id" value="<?= esc_html($project_id); ?>" />
                                <span class="chunk-index">Chunk Index: <?= esc_html($chunk->chunk_index); ?></span>
                                    <input type="hidden" name="action" value="br_process_chunk" />
                                    <button class="form-ui blue" type="submit">Process Chunk</button>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <h1 class="dashboard-title"><?= __("No Document Chunks Found","bluerabbit"); ?></h1>
                    <a href="<?= get_bloginfo('url')."/project/?project_id=$project_id"; ?>" class="button"><?= __("Go to Project","bluerabbit"); ?></a>
                <?php } ?>
            <?php } else { ?>
                <h1 class="dashboard-title"><?= __("No Project Selected","bluerabbit"); ?></h1>
                <a href="<?= get_bloginfo('url')."/projects"; ?>" class="button"><?= __("View Projects","bluerabbit"); ?></a>
            <?php } ?>

        </div>
    </div>
</div>
<script>
jQuery(document).ready(function($) {
    $('.br-process-chunk-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        var chunkText = $(this).find('textarea[name="br_chunk_text"]').val();
        formData.set('br_chunk_text', chunkText);
        
        formData.append('gpt_model', $('#gpt_model').val());
        formData.append('br_custom_prompt', $('#br_custom_prompt').val());
        formData.append('br_minimum_milestones', $('#br_minimum_milestones').val());
        formData.append('br_story_narrative', $('#br_story_narrative').val());
        formData.append('br_story_tone', $('#br_story_tone').val());
        formData.append('br_story_audience', $('#br_story_audience').val());
        formData.append('br_story_subject', $('#br_story_subject').val());

        $.ajax({
            url: runAJAX.ajaxurl,
            type: 'POST',
            data: formData,
            timeout: 120000, // generous for PDF parse + OpenAI prep
            dataType: 'json', // expect JSON; helps surface parse errors
            contentType: false,
            processData: false,
            beforeSend: function() {
                showLoader();
            },
            success: function(res, status, xhr){
                console.log('Upload success:', res, status);
                if (res && res.success && res.data && res.data.redirect) {
                    window.location.assign(res.data.redirect);
                } else if (res && res.data && res.data.message) {
                    alert(res.data.message);
                } else {
                    alert('Upload completed, but response had no redirect.');
                    console.debug('Raw response:', res);
                }
            },
            error: function(xhr) {
                console.error('Upload error:', {status, xhr});
                console.error('Status Code:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                console.error('Response Headers:', xhr.getAllResponseHeaders());
                let msg = 'Error uploading file.';
                if (xhr.status === 413) msg = 'File too large (HTTP 413).';
                if (xhr.status === 415) msg = 'Unsupported Media Type (HTTP 415).';
                if (status === 'parsererror') msg = 'Invalid JSON from server (check PHP notices).';
                notify(msg);
            }
        });
    });
});
</script>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
