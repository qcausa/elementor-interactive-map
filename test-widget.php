<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Simple_Text_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'simple_text_widget';
    }

    public function get_title() {
        return __( 'Simple Text Widget', 'interactive-map-widget' );
    }

    public function get_icon() {
        return 'eicon-text';
    }

    public function get_categories() {
        return [ 'basic' ];
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'interactive-map-widget' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'text_control',
            [
                'label' => __( 'Text', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __( 'Default Text', 'interactive-map-widget' ),
                'placeholder' => __( 'Enter your text', 'interactive-map-widget' ),
                'frontend_available' => true,  // Make this control available on the frontend
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        echo '<div class="simple-text-widget">';
        echo '<span class="simple-text-content">' . esc_html( $settings['text_control'] ) . '</span>';
        echo '</div>';
    }

    protected function _content_template() {
        ?>
        <#
        var text = settings.text_control;
        #>
        <div class="simple-text-widget">
            <span class="simple-text-content">{{{ text }}}</span>
        </div>
        <?php
    }
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Simple_Text_Widget() );