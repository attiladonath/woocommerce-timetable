<?php
/**
* Plugin Name: WooCommerce Timetable
* Plugin URI: https://github.com/attiladonath/woocommerce-timetable
* Description: This plugin provides a minimalist way to handle opening hours with WooCommerce.
* Version: 1.0
* Author: Attila Donath
* Author URI: http://attiladonath.com
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access to this script file.
defined('ABSPATH') or die();

include dirname(__FILE__) . '/admin.php';

define('WOOCOMMERCE_TIMETABLE_CLOSED_MESSAGE', __('The shop is closed, you cannot order now.'));
define('WOOCOMMERCE_TIMETABLE_OPTIONS', 'woocommerce-timetable-options');

add_action('woocommerce_before_checkout_process', 'woocommerce_timetable_prevent_checkout');
function woocommerce_timetable_prevent_checkout() {
    if (woocommerce_timetable_shop_is_closed()) {
        throw new Exception(WOOCOMMERCE_TIMETABLE_CLOSED_MESSAGE);
    }
}

add_action('woocommerce_before_cart', 'woocommerce_timetable_show_closed_notice');
add_action('woocommerce_before_checkout_form', 'woocommerce_timetable_show_closed_notice');
function woocommerce_timetable_show_closed_notice() {
    if (woocommerce_timetable_shop_is_closed()) {
        wc_print_notice(WOOCOMMERCE_TIMETABLE_CLOSED_MESSAGE, 'notice');
    }
}

add_filter('woocommerce_order_button_html', 'woocommerce_timetable_remove_checkout_button');
function woocommerce_timetable_remove_checkout_button($html) {
    if (woocommerce_timetable_shop_is_closed()) {
        return '';
    }
    return $html;
}

add_filter('wc_get_template', 'woocommerce_timetable_remove_proceed_link', NULL, 5);
function woocommerce_timetable_remove_proceed_link($located, $template_name, $args, $template_path, $default_path) {
    if (woocommerce_timetable_shop_is_closed() && 'cart/proceed-to-checkout-button.php' == $template_name) {
        return '';
    }
    return $located;
}

function woocommerce_timetable_shop_is_closed() {
    static $is_closed;

    if (!isset($is_closed)) {
        $is_closed = woocommerce_timetable_shop_is_closed_check();
    }
    return $is_closed;
}

function woocommerce_timetable_shop_is_closed_check() {
    $options = get_option(WOOCOMMERCE_TIMETABLE_OPTIONS);
    if (isset($options['closed'])) {
        return TRUE;
    }

    require_once dirname(__FILE__) . '/timetable_checker.php';
    $timetable_checker = new WooCommerceTimetable_TimetableChecker($options['timetable']);
    return !$timetable_checker->isOpenNow();
}
