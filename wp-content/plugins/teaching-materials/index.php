<?php
/*
Plugin Name: Materjalide muutmise Plugin
Description: Plugin õppematerjalide struktuuri muutmiseks
Author: Madis Dreifeld
Version: 1.01
*/

add_action('admin_menu', 'category_edit_menu');
require_once 'ajax_functions.php';
require_once 'category_functions.php';

function category_edit_menu()
{
    add_menu_page('Materjalide muutmine', 'Materjalid', 'manage_options', 'materjalid', 'init_page');
}

function init_page()
{
    registerTeachingMaterialPluginScripts();
    registerTeachingMaterialPluginStylesheets();

    $categories = getMainCategory();
    $uncategorized = getUncategorized();

    switch ($_GET['task']) {
        case 'migrate':
            $cats = getChildren(1);
            migrate($cats);
            die();
            break;
    }

    if (!$_GET['task']) {
        require_once 'views/index.php';
    }
}


/**
 * Helper function
 * @param $data
 */

function pr($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function loadTemplate($template, $variables = [])
{
    ob_start();

    if (@count($variables)) {
        @extract($variables);
    }

    require $template;
    return ob_get_clean();
}

/**
 * Registers all necessary scripts
 */
function registerTeachingMaterialPluginScripts()
{
    wp_register_script('latest_bootstrap_script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
    wp_register_script('jquery_ui_plugin', '/wp-content/plugins/teaching-materials/js/jqueryui/jquery-ui.min.js');
    wp_register_script('sweet_alert_js', '/wp-content/plugins/teaching-materials/plugins/sweetalert/sweetalert.min.js');
    wp_register_script('main_script', '/wp-content/plugins/teaching-materials/js/main.js');
    wp_enqueue_script('latest_bootstrap_script');
    wp_enqueue_script('jquery_ui_plugin');
    wp_enqueue_script('sweet_alert_js');
    wp_enqueue_script('main_script');
    wp_localize_script('main_script', 'ajax', ['url' => admin_url('admin-ajax.php')]);

}

/**
 * Registers all necessary stylesheets
 */
function registerTeachingMaterialPluginStylesheets()
{
    wp_register_style('latest_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
    wp_enqueue_style('latest_bootstrap');
    wp_register_style('sweet_alert_css', '/wp-content/plugins/teaching-materials/plugins/sweetalert/sweetalert.css');
    wp_register_style('edit_style', '/wp-content/plugins/teaching-materials/css/edit-style.css');
    wp_enqueue_style('sweet_alert_css');
    wp_enqueue_style('edit_style');
}


?>