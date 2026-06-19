<script>
const tour = brCreateTour('new-blog-post');
const new_blog_post_steps = [
    {
        id: 'step-1',
        title: "<?= __("Blog Post Editor","bluerabbit"); ?>",
        text: "<?= __("Blog posts are news and announcements players see on the adventure's blog.","bluerabbit"); ?>",
        buttons: [
            brSkipBtn('new-blog-post', "<?= esc_js(__("Skip","bluerabbit")); ?>"),
            brNextBtn("<?= esc_js(__("Start Tutorial","bluerabbit")); ?>")
        ]
    },
    {
        id: 'post-1',
        title: "<?= __("Headline & Image","bluerabbit"); ?>",
        text: "<?= __("Title the post and upload a cover image — the image is required.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_title', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'post-2',
        title: "<?= __("Level & Start Date","bluerabbit"); ?>",
        text: "<?= __("Optionally require a minimum level to see it, and schedule when it becomes visible.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_level', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'post-3',
        title: "<?= __("Display Style","bluerabbit"); ?>",
        text: "<?= __("Choose how it's laid out on the blog page: text on the right, text on the left, a news highlight, or headline only.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_style', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'post-4',
        title: "<?= __("Available For","bluerabbit"); ?>",
        text: "<?= __("Optionally restrict it to players on a specific path.","bluerabbit"); ?>",
        attachTo: { element: '#the_achievement_id', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'post-5',
        title: "<?= __("Secondary Headline & Content","bluerabbit"); ?>",
        text: "<?= __("A short subheading, then the full body of the post.","bluerabbit"); ?>",
        attachTo: { element: '#the_quest_secondary_headline', on: 'right' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'post-6',
        title: "<?= __("Status & Save","bluerabbit"); ?>",
        text: "<?= __("Publish, save as draft, or trash it — then save your changes here.","bluerabbit"); ?>",
        attachTo: { element: '#submit-button', on: 'top' },
        buttons: [ brNextBtn() ]
    },
    {
        id: 'step-last',
        title: "<?= __("Need a Refresher?","bluerabbit"); ?>",
        text: "<?= __("You can replay this tutorial anytime from here.","bluerabbit"); ?>",
        attachTo: { element: '#tutorial-button-start', on: 'bottom' },
        buttons: [ brDoneBtn('new-blog-post', "<?= esc_js(__("Close","bluerabbit")); ?>") ]
    }
];
tour.addSteps(new_blog_post_steps);
</script>
