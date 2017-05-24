<?php
/*
Plugin Name: Code Sample
Plugin URI: https://payone-code-sample.7js.us/index.php/payone-code-sample/
Description: Code sample for PAYONE
Version: 1.0
Author: PlatinPower.com GmbH
Author URI: http://www.platinpower.com
*/

namespace Pp\CodeSample;

if (defined( 'WP_CLI' ) && WP_CLI) {
    // by cli/scheduler do nothing
    return;
}

if (defined('DOING_AJAX') && DOING_AJAX) {
    // by ajax do nothing
    return;
}

if (is_admin()) {
    // by admin do nothing
    return;
}

// autoload of plugin classes
require_once 'autoload.php';

$objectManager = \Bfc\Core\Object\Registry::getInstance(\Bfc\Core\Object\ObjectManager::class);
/** @var CodeSampleController $CodeSampleController */
$CodeSampleController = $objectManager->get(CodeSampleController::class);

/**
 * Shortcode [code_sample].
 */
add_shortcode('code_sample', function () use ($CodeSampleController) {
    $CodeSampleController->initializeView();
});

// Include CSS and JavaScripts.
add_action('wp_enqueue_scripts', function () {
    // include styles
    wp_enqueue_style('jquery-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('theme-patch-css', plugins_url('/Resources/Assets/Css/ThemePatch.css', __FILE__));
    wp_enqueue_style('styles-css', plugins_url('/Resources/Assets/Css/Styles.css', __FILE__));

    // include javascript
    wp_enqueue_script('jquery-js', '//code.jquery.com/jquery-2.2.4.min.js');
    wp_enqueue_script('jquery-ui-js', '//code.jquery.com/ui/1.12.1/jquery-ui.min.js');
    wp_enqueue_script('recaptcha-js', '//www.google.com/recaptcha/api.js');
    wp_enqueue_script('frontend-js', plugins_url('/Resources/Assets/JavaScript/Frontend.js', __FILE__));
}, 100);