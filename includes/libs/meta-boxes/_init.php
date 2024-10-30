<?php
/**
 * NOO Meta Boxes Package
 *
 * Initialize NOO Meta Boxes
 * This file initialize NOO Meta Boxes, it include materials and start the Meta Boxes for Post, Page and Portfolio.
 *
 * @package    NOO Framework
 * @subpackage NOO Meta Boxes
 * @version    0.1.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */


require_once plugin_dir_path(dirname(__FILE__)) . '/meta-boxes/generate-meta-box.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/meta-boxes/class-helper.php';

if (!function_exists('jlt_enqueue_meta_boxes_js')) :
    function jlt_enqueue_meta_boxes_js($hook)
    {

        if ($hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php') {
            return;
        }

        wp_register_script('jlt-meta-boxes-js', JLT_PLUGIN_URL . 'admin/js/jlt-meta-boxes.js', array('jquery', 'media-upload', 'thickbox'), NULL, true);
        wp_enqueue_script('jlt-meta-boxes-js');
    }

    add_action('admin_enqueue_scripts', 'jlt_enqueue_meta_boxes_js');
endif;

if (!function_exists('jlt_enqueue_meta_boxes_css')) :
    function jlt_enqueue_meta_boxes_css($hook)
    {

        if ($hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php') {
            return;
        }

        wp_register_style('jlt-meta-boxes-css', JLT_PLUGIN_URL . 'admin/css/jlt-meta-boxes.css', NULL, NULL, 'all');
        wp_enqueue_style('jlt-meta-boxes-css');

    }

    add_action('admin_enqueue_scripts', 'jlt_enqueue_meta_boxes_css');
endif;