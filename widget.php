<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Interactive_Map_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'interactive_map';
    }

    public function get_title() {
        return __( 'Interactive Map', 'interactive-map-widget' );
    }

    public function get_icon() {
        return 'eicon-map-pin';
    }

    public function get_categories() {
        return [ 'basic' ];
    }

    private function get_post_categories() {
        $categories = get_categories();
        $options = [];
    
        foreach ( $categories as $category ) {
            $options[$category->term_id] = $category->name;
        }
    
        return $options;
    }
    

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Map Settings', 'interactive-map-widget' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
    
        // Add control for display type (Region or Marker)
        $this->add_control(
            'display_type',
            [
                'label' => __( 'Display Type', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'region',
                'options' => [
                    'region' => __( 'Region', 'interactive-map-widget' ),
                    'marker' => __( 'Marker', 'interactive-map-widget' ),
                ],
            ]
        );

        // Add control for Global Content
        $this->add_control(
            'global_content',
            [
                'label' => __( 'Global Content', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => '',
                'description' => __( 'Enter content or use dynamic tags to select an Elementor template.', 'interactive-map-widget' ),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
    
        // Add Display Options control
        $this->add_control(
            'display_options',
            [
                'label' => __( 'Display Options', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'full_world',
                'options' => [
                    'full_world' => __( 'Show Full World', 'interactive-map-widget' ),
                    'selected_regions' => __( 'Show Only Selected Regions', 'interactive-map-widget' ),
                ],
            ]
        );
    
        // Repeater for multiple regions
        $repeater = new \Elementor\Repeater();
    
        // Region selector inside the repeater
        $repeater->add_control(
            'region_to_display',
            [
                'label' => __( 'Region', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'US',
                'options' => [
                    'US' => __( 'United States', 'interactive-map-widget' ),
                    'CA' => __( 'Canada', 'interactive-map-widget' ),
                    'LATAM' => __( 'Latin America', 'interactive-map-widget' ),
                ],
            ]
        );

        // Post category selector inside the repeater
        $repeater->add_control(
            'region_post_category',
            [
                'label' => __( 'Link to Post Category', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => false,
                'options' => $this->get_post_categories(),
                'label_block' => true,
            ]
        );
    
        // Link field inside the repeater
        $repeater->add_control(
            'region_link',
            [
                'label' => __( 'Region Link', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __( 'https://your-link.com', 'interactive-map-widget' ),
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                ],
                'show_external' => true,
            ]
        );

        // Region-specific content field inside the repeater
        $repeater->add_control(
            'region_content',
            [
                'label' => __( 'Region Content', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => '',
                'description' => __( 'Enter content specific to this region. If left empty, global content will be used.', 'interactive-map-widget' ),
            ]
        );
    
        // Add repeater to the control panel
        $this->add_control(
            'regions_list',
            [
                'label' => __( 'Regions', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ region_to_display }}}',
            ]
        );
    
        // Add controls for region color and region border color
        $this->add_control(
            'region_color',
            [
                'label' => __( 'Region Color', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff0000',
            ]
        );
    
        $this->add_control(
            'region_border_color',
            [
                'label' => __( 'Region Border Color', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
            ]
        );
    
        $this->end_controls_section();

        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Map Settings', 'interactive-map-widget' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
    
        // Add control for selecting the contents container
        $this->add_control(
            'contents_container',
            [
                'label' => __( 'Select Contents Container', 'interactive-map-widget' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'map-contents',
                'description' => __( 'Enter the ID of the Interactive Map Contents widget to connect.', 'interactive-map-widget' ),
                'frontend_available' => true,
            ]
        );
    }
    

    protected function render() {
    $settings = $this->get_settings_for_display();
    ?>

    <div id="interactive-map-widget"
         data-display-type="<?php echo esc_attr( $settings['display_type'] ); ?>"
         data-display-options="<?php echo esc_attr( $settings['display_options'] ); ?>"
         data-region-color="<?php echo esc_attr( $settings['region_color'] ); ?>"
         data-region-border-color="<?php echo esc_attr( $settings['region_border_color'] ); ?>"
         data-contents-container="<?php echo esc_attr( $settings['contents_container'] ); ?>">

         <!-- Pass the regions and links to the JavaScript -->
         <script type="application/json" id="map-regions-data">
            <?php echo json_encode($settings['regions_list']); ?>
         </script>

         <!-- Global Content -->
         <div class="interactive-map-global-content" style="display: none;">
            <?php 
            $global_content = $settings['global_content'];
            $global_content = str_replace('[elementor-template id="2379"]', '[elementor-template id="2379" category_id=""]', $global_content);
            echo $global_content; 
            ?>
         </div>
    </div>

    <?php
}

    
    

    protected function _content_template() {
        ?>
        <div id="interactive-map-widget" data-region="{{ settings.region_to_display }}"
             data-region-color="{{ settings.region_color }}"
             data-region-border-color="{{ settings.region_border_color }}">
        </div>
        <?php
    }
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Interactive_Map_Widget() );
