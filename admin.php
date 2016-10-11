<?php
// Prevent direct access to this script file.
defined('ABSPATH') or die();

define('WOOCOMMERCE_TIMETABLE_ADMIN_PAGE', 'woocommerce-timetable-admin');

add_action('admin_menu', 'woocommerce_timetable_admin_menu');

function woocommerce_timetable_admin_menu() {
    add_menu_page(
        'WooCommerce Timetable - Opening Hours',
        'Opening Hours',
        'manage_options',
        WOOCOMMERCE_TIMETABLE_ADMIN_PAGE,
        'woocommerce_timetable_admin_page',
        'dashicons-clock',
        58
    );
}

function woocommerce_timetable_admin_page() {
    include dirname(__FILE__) . '/templates/admin-page.php';
}

add_action('admin_init', 'woocommerce_timetable_admin_init');
function woocommerce_timetable_admin_init() {
    register_setting(
        WOOCOMMERCE_TIMETABLE_OPTIONS,
        WOOCOMMERCE_TIMETABLE_OPTIONS,
        'woocommerce_timetable_settings_validate'
    );

    $section = 'woocommerce_timetable_settings_main';
    add_settings_section(
        $section,
        '', // No section title.
        'woocommerce_timetable_settings_section_main',
        WOOCOMMERCE_TIMETABLE_ADMIN_PAGE
    );

    add_settings_field(
        'woocommerce_timetable_closed',
        __('Closed'),
        'woocommerce_timetable_settings_field_closed',
        WOOCOMMERCE_TIMETABLE_ADMIN_PAGE,
        $section
    );
    add_settings_field(
        'woocommerce_timetable_timetable',
        __('Timetable'),
        'woocommerce_timetable_settings_field_timetable',
        WOOCOMMERCE_TIMETABLE_ADMIN_PAGE,
        $section
    );
}

function woocommerce_timetable_settings_section_main() {
    // No text.
}

function woocommerce_timetable_settings_field_closed() {
    $options = get_option(WOOCOMMERCE_TIMETABLE_OPTIONS);
    include dirname(__FILE__) . '/templates/admin-page-field-closed.php';
}

function woocommerce_timetable_settings_field_timetable() {
    $options = get_option(WOOCOMMERCE_TIMETABLE_OPTIONS);
    include dirname(__FILE__) . '/templates/admin-page-field-timetable.php';
}

function woocommerce_timetable_settings_validate($options) {
    require_once dirname(__FILE__) . '/timetable_checker.php';
    $timetable_checker = new WooCommerceTimetable_TimetableChecker($options['timetable']);
    if (!$timetable_checker->isValid()) {
        add_settings_error(
            WOOCOMMERCE_TIMETABLE_OPTIONS,
            'woocommerce_timetable_timetable',
            __('The timetable is invalid.'),
            'error'
        );
    }
    return $options;
}
