<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Interactive_Map_Contents_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'interactive_map_contents';
    }

    public function get_title() {
        return __( 'Interactive Map Contents', 'interactive-map-contents-widget' );
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return [ 'basic' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Map Contents Settings', 'interactive-map-contents-widget' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'container_id',
            [
                'label' => __( 'Contents Container ID', 'interactive-map-contents-widget' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'map-contents',
                'description' => __( 'This ID will be used to connect to the Interactive Map widget.', 'interactive-map-contents-widget' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>

        <div id="<?php echo esc_attr($settings['container_id']); ?>" class="map-contents-container">
            <div class="map-contents-region-content"></div>
        </div>

        <?php
    }

    protected function _content_template() {
        ?>
        <div id="{{ settings.container_id }}" class="map-contents-container">
            <p><?php _e( 'Click a region on the map to see its title here.', 'interactive-map-contents-widget' ); ?></p>
            <div class="map-contents-region-title"></div>
        </div>
        <?php
    }
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Interactive_Map_Contents_Widget() );
