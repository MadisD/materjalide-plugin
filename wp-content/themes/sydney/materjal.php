<?php

/*

Template Name: Materjal

*/

global $wpdb;
$task = $_GET['task'];

switch ($task) {
    case 'root':
        header('Content-type: application/json');
        $result = getItems(1, 1, 1);

        $tipp = [
            'name' => 'Materjalid',
            'children' => $result,
            'className' => 'tipp',
            'height' => 0
        ];

        die(json_encode($tipp));
        break;
}
if ($task == 'content') {
    header('Content-type: application/json');
    $parentId = $_GET['parent_id'];
    $height = $_GET['height'];
    $tipp = getSingleCategory($parentId);

    if ($tipp) {
        $tipp->className = $tipp->type == 'node' ? 'tipp' : 'leht';
        $tipp->height = $height;
        $tipp->children = getItems($parentId, $height);
        $tipp->isRoot = true;
    }

    die(json_encode($tipp));
}


function getSingleCategory($id)
{
    global $wpdb;
    return $wpdb->get_row('SELECT * FROM category WHERE id = ' . $id);
}

function getItems($parentId, $height, $maxLevel = null)
{
    global $wpdb;

    $result = $wpdb->get_results('SELECT * FROM category WHERE category.parent_id = ' . $parentId . ' ORDER BY category.order');

    if (sizeof($result) > 0) {

        foreach ($result as $index => $category) {
            $className = $category->type == 'node' ? 'tipp' : 'leht';
            $result[$index]->className = $className;
            $result[$index]->height = $height;
            $result[$index]->parentId = $parentId;

            if (!$maxLevel || $height < $maxLevel) {
                $children = getItems($category->id, $height + 1, $maxLevel);

                if ($children) {
                    $result[$index]->children = $children;
                }
            }

        }
        return $result;
    } else {
        return null;
    }
}


function insert_jquery()
{
    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js', array(), null, false);
    wp_enqueue_script('fitvids', "/wp-content/themes/sydney/fitvids/jquery.fitvids.js");
}

add_filter('wp_enqueue_scripts', 'insert_jquery');

?>

<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Sydney
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <?php if (!function_exists('has_site_icon') || !has_site_icon()) : ?>
        <?php if (get_theme_mod('site_favicon')) : ?>
            <link rel="shortcut icon" href="<?php echo esc_url(get_theme_mod('site_favicon')); ?>"/>
        <?php endif; ?>
    <?php endif; ?>

    <?php wp_head(); ?>
    <link rel="stylesheet" href="https://cdn.rawgit.com/FortAwesome/Font-Awesome/master/css/font-awesome.min.css">
    <link rel="stylesheet" href="/wp-content/themes/sydney/orgchart/css/jquery.orgchart.css">
    <link rel="stylesheet" href="/wp-content/themes/sydney/orgchart/css/style.css">
    <link rel="stylesheet" href="/wp-content/themes/sydney/chart-style.css">
</head>

<body <?php body_class(); ?>>


<div id="page" class="hfeed site">
    <a class="skip-link screen-reader-text" href="#content"><?php _e('Skip to content', 'sydney'); ?></a>


    <header id="masthead" class="site-header" role="banner">
        <div class="header-wrap">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-sm-8 col-xs-12">
                        <?php if (get_theme_mod('site_logo')) : ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php bloginfo('name'); ?>"><img
                                        class="site-logo" src="<?php echo esc_url(get_theme_mod('site_logo')); ?>"
                                        alt="<?php bloginfo('name'); ?>"/></a>
                        <?php else : ?>
                            <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>"
                                                      rel="home"><?php bloginfo('name'); ?></a></h1>
                            <h2 class="site-description"><?php bloginfo('description'); ?></h2>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8 col-sm-4 col-xs-12">
                        <div class="btn-menu"></div>
                        <nav id="mainnav" class="mainnav" role="navigation">
                            <?php wp_nav_menu(array('theme_location' => 'primary', 'fallback_cb' => 'sydney_menu_fallback')); ?>
                        </nav><!-- #site-navigation -->
                    </div>
                </div>
            </div>
        </div>
    </header><!-- #masthead -->


    <div class="sydney-hero-area">
        <?php sydney_slider_template(); ?>
        <div class="header-image">
            <?php sydney_header_overlay(); ?>
            <img class="header-inner" src="<?php header_image(); ?>"
                 width="<?php echo esc_attr(get_custom_header()->width); ?>" alt="<?php bloginfo('name'); ?>"
                 title="<?php bloginfo('name'); ?>">
        </div>
        <?php sydney_header_video(); ?>

    </div>


    <div id="content" class="page-wrap">
        <div class="container content-wrapper">
            <div class="row">


                <script src="/wp-content/themes/sydney/orgchart/js/jquery.orgchart.js"></script>

                <div id="chart-container">
                </div>

                <script>
                    jQuery(document).ready(function ($) {

                        $(document).find('.page-wrap').removeClass();
                        $(document).find('.content-wrapper').removeClass();
                        $(document).find('#colophon').remove();

                        function initContainer(url) {
                            $('#chart-container').orgchart({
                                'data': url,
                                'nodeContent': 'content',
                                'pan': true,
                                'zoom': false,
                                'toggleSiblingsResp': false,
                                'createNode': function (node, data) {
                                    $(node).find('i').remove();
                                    if (!data.content) {
                                        $(node).find('.content').remove();
                                    }

                                    $(node).data('parent-id', data.parentId);

                                    if (data.height === 1) {

                                        var drillDownIcon = $('<i>', {
                                            'class': 'fa fa-arrow-circle-down drill-icon',
                                            'click': function () {
                                                $('#chart-container').html('');
                                                initContainer('?task=content&parent_id=' + data.id + '&height=' + data.height);
                                            }
                                        });

                                        $(node).append(drillDownIcon);

                                    }

                                    if (data.isRoot) {
                                        var drillUpIcon = $('<i>', {
                                            'class': 'fa fa-arrow-circle-up drill-icon',
                                            'click': function () {
                                                $('#chart-container').html('');
                                                initContainer('?task=root');
                                            }
                                        });
                                        $(node).prepend(drillUpIcon);
                                    }
                                }
                            });
                        }

                        initContainer('?task=root');

                    });
                </script>

            </div>
        </div>
    </div><!-- #content -->

    <?php do_action('sydney_before_footer'); ?>

    <?php if (is_active_sidebar('footer-1')) : ?>
        <?php get_sidebar('footer'); ?>
    <?php endif; ?>

    <a class="go-top"><i class="fa fa-angle-up"></i></a>

    <?php do_action('sydney_after_footer'); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
