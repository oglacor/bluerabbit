<?php include (get_stylesheet_directory() . '/header.php');
$br_project = new Project();
?>
    <div class="dashboard">
        <div class="dashboard-content white-bg">
            <div class="dashboard-container">
                <h2>AI generated Milestones</h2>
                <?php
                    $result_id = intval($_GET['result_id'] ?? 0);

                    if ($result_id) {
                        global $wpdb;
                        $json = $wpdb->get_var($wpdb->prepare(
                            "SELECT data FROM {$wpdb->prefix}br_ai_temp_results WHERE ai_temp_id = %d",
                            $result_id
                        ));

                        echo $br_project->br_render_ai_milestones_preview($json);
                    } else {
                        echo '<p>No preview data found.</p>';
                    }
                ?>
            </div>
        </div>
    </div>
<?php include (get_stylesheet_directory() . '/footer.php'); ?>


