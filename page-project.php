<?php include (get_stylesheet_directory() . '/header.php');
global $br_project;
global $n;
$project = $br_project->get_project_by_id($_GET['project_id'] ?? 0);
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
                    <div class="dashboard-input-field-container" style="grid-column: 1 / span 12; grid-row: 1 / span 3;">
                        <h3 class="dashboard-grid-cell-headline"><?= __("Project name","bluerabbit");?></h3>
                        <?php if(isset($project)) { ?><input type="hidden" id="br_project_id" value="<?= $project->project_id; ?>"><?php } ?>
                        <input class="form-ui font _30 w-full" type="text" value="<?= isset($project) ? $project->name : ""; ?>" id="br_project_name" name="br_project_name" placeholder="<?= __("Project name","bluerabbit"); ?>" required>
                    </div>
                    <div class="dashboard-input-field-container" style="grid-column: 1 / span 12; grid-row: 3 / span 1;">
                        <h3 class="dashboard-grid-cell-headline"><?= __("PDF File","bluerabbit");?></h3>
                        <label for="br_pdf_file">Upload PDF File:</label>
                        <input type="file" name="br_pdf_file" id="br_pdf_file" accept=".pdf" required />
                    </div>
                    <div class="dashboard-text-area-container" style="grid-column: 1 / span 12; grid-row: 4 / span 6;">
                        <h3 class="dashboard-grid-cell-headline"><?= __("Description","bluerabbit");?></h3>
                        <?php 
                        if($roles[0]=="administrator"){
                            $wp_editor_settings = array( 'quicktags'=> true,'editor_height'=>200);
                        }else{
                            $wp_editor_settings = array( 'quicktags'=> false ,'editor_height'=>200);
                        }
                        if(isset($project)){ 
                            wp_editor( $project->description, 'br_project_description',$wp_editor_settings); 	
                        }else{
                            wp_editor('', 'br_project_description',$wp_editor_settings); 	
                        }
                        ?>
                    </div>
                    <div class="dashboard-grid-cell-container" style="grid-column: 13 / span 12; grid-row: 1 / span 10;">
                        <input type="hidden" name="action" value="br_ai_upload_file" />
                        <button class="form-ui blue" type="submit">Upload and Preview</button>
                    </div>

                </div>

            </form>
        </div>
    </div>
</div>


<script>
jQuery(document).ready(function($) {
    $('#br-ai-upload-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

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
