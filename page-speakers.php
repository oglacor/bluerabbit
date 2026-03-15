<?php include (get_stylesheet_directory() . '/header.php'); ?>
<?php $speakers = $wpdb->get_results("
	SELECT speakers.*, players.player_first, players.player_last, players.player_display_name, players.player_picture, players.player_bio, players.player_company, players.player_website, players.player_linkedin FROM {$wpdb->prefix}br_speakers speakers
    LEFT JOIN {$wpdb->prefix}br_players players ON speakers.player_id = players.player_id
	WHERE speakers.adventure_id=$adventure->adventure_id
	ORDER BY speakers.speaker_first_name, speakers.speaker_last_name
"); 
$sessions = getSessions($adventure->adventure_id,'publish');
?>
<?php
$taglines = [
    __("I know my stuff","bluerabbit"),
    __("Call me expert","bluerabbit"),
    __("Master & Monster","bluerabbit"),
    __("Listen to me","bluerabbit"),
    __("The chosen one","bluerabbit"),
    __("No, you don't","bluerabbit"),
]
?>

<div class="speakers">
    <div class="headline">
        <h1><?= __("Speakers","bluerabbit"); ?></h1>
    </div>
    <div class="speakers-grid">
        <?php if($speakers){ ?>
			<?php foreach($speakers as $key=>$s){ ?>
                <div class="speaker-item">
                    <div class="speaker-picture" >
                        <div class="speaker-tagline">
                            <?php $randTagLine = rand(0, 5); ?>
                            <?= $taglines[$randTagLine]; ?>
                        </div>
                        <?php $speaker_image = $s->player_picture ? $s->player_picture : $s->speaker_picture;  ?>

                        <svg class="speaker-art-picture" data-name="Speaker Art SVG" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 269.51 270.52">
                            <defs>
                                <clipPath id="speakerClip">
                                    <rect x="50.43" y="52.44" width="191.73" height="191.73"></rect>
                                </clipPath>
                            </defs>
                            <path class="yellow-fill" d="M256.67,26.75H53c0,8.13-3.7,15.4-9.51,20.21h0c-2.81,2.33-6.12,4.08-9.74,5.08-.08.02-.16.05-.24.07-.64.17-1.3.31-1.96.44-.12.02-.23.04-.35.06-.66.11-1.32.2-2,.27-.1,0-.21.02-.31.02-.71.06-1.42.09-2.14.09,0,0,0,0,0,0-.44,0-.89-.01-1.33-.03-.22-.01-.44-.03-.67-.05-.21-.02-.42-.03-.63-.05-.02,0-.05,0-.07,0v206.51c4.23,4.23,6.59,6.59,10.82,10.82h221.8l10.82-10.82V37.57c-4.23-4.23-6.59-6.59-10.82-10.82Z"/>
                            <image
                                href="<?= $speaker_image; ?>"
                                x="50.43"
                                y="52.44"
                                width="191.73"
                                height="191.73"
                                clip-path="url(#speakerClip)"
                                preserveAspectRatio="xMidYMid slice"
                            />
                            <rect class="picture-bg" x="50.43" y="52.44" width="191.73" height="191.73"/>
                            <polyline class="yellow-line" points="45.35 8.24 50.24 3.35 70.29 3.35"/>
                            <path class="blue-line" d="M261.53,59.85l-8.76,15.17h14.5c1.34,0,2.18-1.45,1.51-2.62l-7.25-12.56Z"/>
                            <path class="yellow-line" d="M8.53,37.45c6.88,0,10.86-6.97,17.64-6.97s12.42,8.31,17.32,8.31"/>
                            <path class="" d="M26.75,45.91c-3.51,0-6.86,2.84-9.92,5.14,3.06,1.25,6.41,1.95,9.92,1.95,3.16,0,6.18-.56,8.99-1.58-2.8-2.53-5.83-5.51-8.99-5.51Z"/>
                            <path class="blue-line" d="M22.66,24.61c1.14-.2,2.31-.33,3.51-.33s2.37.12,3.51.33c2.6-1.29,4.39-3.97,4.39-7.07,0-4.36-3.54-7.9-7.9-7.9s-7.9,3.54-7.9,7.9c0,3.1,1.79,5.77,4.39,7.07Z"/>
                            <circle class="blue-line" cx="26.75" cy="26.75" r="26.25"/>
                            <path class="blue-line" d="M26.16,24.32c-9.92,0-17.96,8.04-17.96,17.96,0,1.15.12,2.26.32,3.35,4.72,4.56,11.14-6.97,18.22-6.97,6.36,0,12.2,12.07,16.74,8.31.4-1.5.64-3.06.64-4.68,0-9.92-8.04-17.96-17.96-17.96Z"/>
                            <polyline class="dotted yellow-line" points="75.4 26.44 250.71 26.44 261.53 37.26 261.53 53.85"/>
                            <polyline class="dotted blue-line" points="261.53 239.63 261.53 250.28 252.18 259.63 90.48 259.63"/>
                            <circle class="blue-line"cx="86.33" cy="259.63" r="4.15"/>
                            <line class="blue-line"x1="28.91" y1="95.15" x2="28.91" y2="53.49"/>
                            <line class="blue-line"x1="24.58" y1="95.15" x2="24.58" y2="53.49"/>
                            <line class="blue-line"x1="28.91" y1="198.85" x2="28.91" y2="122.14"/>
                            <line class="blue-line"x1="24.58" y1="192.47" x2="24.58" y2="115.76"/>
                            <line class="blue-line"x1="24.58" y1="254.8" x2="24.58" y2="241.64"/>
                            <line class="blue-line"x1="261.53" y1="95.15" x2="261.53" y2="80"/>
                            <line class="blue-line"x1="261.53" y1="210.08" x2="261.53" y2="118.95"/>
                            <circle class="yellow-line" cx="72.85" cy="3.35" r="2.55"/>
                            <line class="yellow-line" x1="268.02" y1="95.15" x2="268.02" y2="86.49"/>
                            <line class="yellow-line" x1="268.02" y1="220.73" x2="268.02" y2="130.42"/>
                            <polyline class="yellow-line" points="268.02 232.33 268.02 255.21 253.2 270.02 37.42 270.02 28.91 261.51 28.91 237.01"/>
                            
                        </svg>


                        <a href="<?= get_bloginfo('url')."/speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id"; ?> alt="<?php echo "$s->speaker_first_name $s->speaker_last_name"; ?>"></a>
                        <div class="social-icons">
                            <?php if($s->speaker_website){ ?>
                            <div class="social-icon-container">
                                <a href="<?php echo $s->speaker_website; ?>" target="_blank">
                                    <svg class="globe-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"> 
                                        <circle cx="50" cy="50" r="50"/>
                                        <ellipse cx="50" cy="50" rx="50" ry="17.73"/>
                                        <ellipse cx="50" cy="50" rx="50" ry="33.22"/>
                                        <ellipse cx="50" cy="50" rx="17.19" ry="50"/>
                                        <ellipse cx="50" cy="50" rx="33.22" ry="50"/>
                                        <line x1="0" y1="50" x2="100" y2="50"/>
                                        <line x1="50" y1="0" x2="50" y2="100"/>
                                    </svg>
                                </a>
                            </div>
                            <?php } ?>
                            <?php if($s->speaker_linkedin){ ?>
                            <div class="social-icon-container">
                                <a href="<?php echo $s->speaker_linkedin; ?>" target="_blank">
                                    <svg class="social-media-logo linkedin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                                        <path d="M92.65,0H7.35C3.29,0,0,3.29,0,7.35v85.29c0,4.06,3.29,7.35,7.35,7.35h85.29c4.06,0,7.35-3.29,7.35-7.35V7.35c0-4.06-3.29-7.35-7.35-7.35ZM29.81,85.24h-14.92v-47.74h14.92v47.74ZM22.3,31.02c-4.77,0-8.64-3.87-8.64-8.64s3.87-8.64,8.64-8.64,8.64,3.87,8.64,8.64-3.87,8.64-8.64,8.64ZM85.38,57.68v27.56h-14.92v-25.91h0c.05-4.21-1.09-10.06-7.79-10.06s-8.35,5.01-8.74,8.72v27.25h-14.92v-47.74h14.92v5.75c.78-1.3,4.69-6.94,13.82-6.94,19.49,0,17.64,21.38,17.64,21.38Z"/>
                                    </svg>
                                </a>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="speaker-content">

                        <div class="speaker-name">
                            <a href="<?php echo get_bloginfo('url')."/speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id"; ?>">
                                <?php
                                if($s->player_display_name){
                                    echo $s->player_display_name;
                                }else{
                                    echo "$s->speaker_first_name $s->speaker_last_name"; 
                                }
                                ?>
                            </a>
                        </div>
                        <div class="speaker-info">
                           	<?php
                                if($s->player_display_name){
                                    $speaker_info = wp_trim_words($s->player_bio, 30, '...');
                                }else{
                                    $speaker_info = wp_trim_words($s->speaker_bio, 30, '...');
                                }
                                echo $speaker_info;
                            ?>
                        </div>
                    </div>
                    <div class="speaker-link">
                        <a href="<?php echo get_bloginfo('url')."/speaker/?adventure_id=$adventure->adventure_id&speaker_id=$s->speaker_id"; ?>" class="speaker-button">
                            <?php $speaker_first_name = $s->player_first ? $s->player_first : $s->speaker_first_name;  ?>
                            <?= __("More","bluerabbit")." $speaker_first_name"; ?>
                        </a>
                    </div>
                </div>
			<?php } ?>
        <?php } ?>
    </div>
</div>

<?php include (get_stylesheet_directory() . '/footer.php'); ?>
