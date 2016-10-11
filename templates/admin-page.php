<div class="wrap">
    <h2><?php print __('Opening Hours'); ?></h2>
    <hr>
    <form action="options.php" method="post">
        <?php settings_errors(WOOCOMMERCE_TIMETABLE_OPTIONS); ?>
        <?php settings_fields(WOOCOMMERCE_TIMETABLE_OPTIONS); ?>
        <?php do_settings_sections(WOOCOMMERCE_TIMETABLE_ADMIN_PAGE); ?>
        <?php submit_button(__('save')); ?>
    </form>
</div>
