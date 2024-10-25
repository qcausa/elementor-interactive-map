<?php
/**
 * Plugin Name: Interactive Map Elementor Widget
 * Description: An Elementor widget for displaying an interactive map.
 * Version: 1.0
 * Author: Your Name
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;

// Include the widget class
function register_interactive_map_widget() {
    require_once plugin_dir_path( __FILE__ ) . 'widget.php';
    require_once plugin_dir_path( __FILE__ ) . 'interactive-map-contents.php';
    require_once plugin_dir_path( __FILE__ ) . 'test-widget.php';
}
add_action( 'elementor/widgets/widgets_registered', 'register_interactive_map_widget' );

// Enqueue necessary scripts and styles
function interactive_map_widget_scripts() {
    wp_enqueue_script( 'd3-js', 'https://d3js.org/d3.v7.min.js', array(), null, true );
    wp_enqueue_script( 'topojson-js', 'https://cdn.jsdelivr.net/npm/topojson@3', array(), null, true );
    wp_enqueue_script( 'interactive-map-script', plugins_url( '/map.js', __FILE__ ), array('jquery', 'd3-js', 'topojson-js'), null, true );
    wp_enqueue_script( 'test-widget', plugins_url( '/test-widget.js', __FILE__ ), array('jquery'), null, true );
    wp_enqueue_style( 'interactive-map-style', plugins_url( '/style.css', __FILE__ ) );
   
}
add_action( 'wp_enqueue_scripts', 'interactive_map_widget_scripts' );
