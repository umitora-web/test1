<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WL_Checkout_Coupon_Form_Element extends Widget_Base {

    public function get_name() {
        return 'wl-checkout-coupon-form';
    }
    
    public function get_title() {
        return __( 'WL: Checkout Coupon Form', 'woolentor-pro' );
    }

    public function get_icon() {
        return ' eicon-form-horizontal';
    }

    public function get_categories() {
        return array( 'woolentor-addons-pro' );
    }

    public function get_style_depends(){
        return [
            'woolentor-widgets-pro',
        ];
    }

    public function get_script_depends(){
        return [
            'woolentor-checkout',
        ];
    }

    public function get_keywords(){
        return ['checkout form','coupon form','coupon field','checkout'];
    }

    protected function register_controls() {

        // Heading
        $this->start_controls_section(
            'form_area_style',
            array(
                'label' => __( 'Style', 'woolentor-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                array(
                    'name'      => 'form_area_typography',
                    'label'     => __( 'Typography', 'woolentor-pro' ),
                    'selector'  => '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info',
                )
            );

            $this->add_control(
                'form_area_text_color',
                [
                    'label' => __( 'Color', 'woolentor-pro' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'form_area_link_color',
                [
                    'label' => __( 'Link Color', 'woolentor-pro' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info a' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'form_area_link_hover_color',
                [
                    'label' => __( 'Link Hover Color', 'woolentor-pro' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info a:hover' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'form_area_icon_color',
                [
                    'label' => __( 'Left Icon Color', 'woolentor-pro' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info::before' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'form_area_border',
                    'label' => __( 'Border', 'woolentor-pro' ),
                    'selector' => '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info',
                ]
            );

            $this->add_control(
                'form_area_top_border_color',
                [
                    'label' => __( 'Top Border Color', 'woolentor-pro' ),
                    'type' => Controls_Manager::COLOR,
                    'condition' => [
                        'form_area_border_border' => '',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info' => 'border-top-color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_responsive_control(
                'form_area_border_radius',
                [
                    'label' => __( 'Border Radius', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name' => 'form_area_background',
                    'label' => __( 'Background', 'woolentor-pro' ),
                    'types' => [ 'classic', 'gradient' ],
                    'selector' => '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info',
                ]
            );

            $this->add_responsive_control(
                'form_area_margin',
                [
                    'label' => __( 'Margin', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-info' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'form_area_padding',
                [
                    'label' => __( 'Padding', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-info' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'form_area_content_align',
                [
                    'label'        => __( 'Alignment', 'woolentor-pro' ),
                    'type'         => Controls_Manager::CHOOSE,
                    'options'      => [
                        'left'   => [
                            'title' => __( 'Left', 'woolentor-pro' ),
                            'icon'  => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'woolentor-pro' ),
                            'icon'  => 'eicon-text-align-center',
                        ],
                        'right'  => [
                            'title' => __( 'Right', 'woolentor-pro' ),
                            'icon'  => 'eicon-text-align-right',
                        ],
                        'justify' => [
                            'title' => __( 'Justified', 'woolentor-pro' ),
                            'icon' => 'eicon-text-align-justify',
                        ],
                    ],
                    'default'   => 'left',
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info' => 'text-align: {{VALUE}}',
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .checkout-coupon-toggle .woocommerce-info::before' => 'position: static;margin-right:10px;',
                    ],
                ]
            );

        $this->end_controls_section();

        // Form
        $this->start_controls_section(
            'form_form_style',
            array(
                'label' => __( 'Form', 'woolentor-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
            
            $this->add_group_control(
                Group_Control_Typography::get_type(),
                array(
                    'name'      => 'form_box_typography',
                    'label'     => __( 'Typography', 'woolentor-pro' ),
                    'selector'  => '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form p',
                )
            );

            $this->add_control(
                'form_box_color',
                [
                    'label' => __( 'Color', 'woolentor-pro' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form p' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'form_box_border',
                    'label' => __( 'Border', 'woolentor-pro' ),
                    'selector' => '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form',
                ]
            );

            $this->add_responsive_control(
                'form_box_border_radius',
                [
                    'label' => __( 'Border Radius', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'form_box_margin',
                [
                    'label' => __( 'Margin', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'form_box_padding',
                [
                    'label' => __( 'Padding', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

        $this->end_controls_section();

        // Input box
        $this->start_controls_section(
            'form_input_box_style',
            array(
                'label' => esc_html__( 'Input Box', 'woolentor-pros' ),
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
            
            $this->add_control(
                'form_input_box_text_color',
                [
                    'label' => __( 'Color', 'woolentor-pro' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form input.input-text' => 'color: {{VALUE}}',
                    ]
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                array(
                    'name'      => 'form_input_box_typography',
                    'label'     => esc_html__( 'Typography', 'woolentor-pro' ),
                    'selector'  => '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form input.input-text',
                )
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'form_input_box_border',
                    'label' => __( 'Border', 'woolentor-pro' ),
                    'selector' => '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form input.input-text',
                ]
            );

            $this->add_responsive_control(
                'form_input_box_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form input.input-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ]
                ]
            );
            
            $this->add_responsive_control(
                'form_input_box_padding',
                [
                    'label' => esc_html__( 'Padding', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form input.input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ]
                ]
            );
            
            $this->add_responsive_control(
                'form_input_box_margin',
                [
                    'label' => esc_html__( 'Margin', 'woolentor-pro' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form input.input-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' => 'after',
                ]
            );

        $this->end_controls_section();

        // Submit button box
        $this->start_controls_section(
            'form_submit_button_style',
            array(
                'label' => esc_html__( 'Submit Button', 'woolentor-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
            )
        );
            
            $this->start_controls_tabs('submit_button_style_tabs');
                
                $this->start_controls_tab(
                    'submit_button_normal_tab',
                    [
                        'label' => __( 'Normal', 'woolentor-pro' ),
                    ]
                );

                    $this->add_control(
                        'form_submit_button_text_color',
                        [
                            'label' => __( 'Color', 'woolentor-pro' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'form_submit_button_background_color',
                        [
                            'label' => __( 'Background Color', 'woolentor-pro' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button' => 'background-color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Typography::get_type(),
                        array(
                            'name'      => 'form_submit_button_typography',
                            'label'     => esc_html__( 'Typography', 'woolentor-pro' ),
                            'selector'  => '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button',
                        )
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'form_submit_button_border',
                            'label' => __( 'Border', 'woolentor-pro' ),
                            'selector' => '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button',
                        ]
                    );

                    $this->add_responsive_control(
                        'form_submit_button_border_radius',
                        [
                            'label' => esc_html__( 'Border Radius', 'woolentor-pro' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => [ 'px', 'em', '%'],
                            'selectors' => [
                                '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            ],
                        ]
                    );
                    
                    $this->add_responsive_control(
                        'form_submit_button_padding',
                        [
                            'label' => esc_html__( 'Padding', 'woolentor-pro' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => [ 'px', 'em', '%'],
                            'selectors' => [
                                '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            ],
                        ]
                    );
                    
                    $this->add_responsive_control(
                        'form_submit_button_margin',
                        [
                            'label' => esc_html__( 'Margin', 'woolentor-pro' ),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => [ 'px', 'em', '%'],
                            'selectors' => [
                                '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            ],
                            'separator' => 'after'
                        ]
                    );

                $this->end_controls_tab();

                $this->start_controls_tab(
                    'submit_button_hover_tab',
                    [
                        'label' => __( 'Hover', 'woolentor-pro' ),
                    ]
                );
                    $this->add_control(
                        'form_submit_button_hover_color',
                        [
                            'label' => __( 'Color', 'woolentor-pro' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button:hover' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_control(
                        'form_submit_button_hover_background_color',
                        [
                            'label' => __( 'Background Color', 'woolentor-pro' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button:hover' => 'background-color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'form_submit_button_hover_border',
                            'label' => __( 'Border', 'woolentor-pro' ),
                            'selector' => '{{WRAPPER}} .woolentor-checkout-coupon-form .coupon-form button.button:hover',
                        ]
                    );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        if ( Plugin::instance()->editor->is_edit_mode() ) {
            // woocommerce_checkout_coupon_form();
            ?>
                <div class="woolentor-checkout-coupon-form">

                    <div class="checkout-coupon-toggle">
                        <div class="woocommerce-info">
                            <?php echo esc_html( apply_filters('woocommerce_checkout_coupon_message', esc_html__('Have a coupon?', 'woolentor') ) ); ?>
                            <a href="#" class="showcoupon"><?php echo esc_html__('Click here to enter your code', 'woolentor') ?></a>
                        </div>
                    </div>

                    <div class="coupon-form" style="display:none;">
                        <p><?php echo esc_html('If you have a coupon code, please apply it below.', 'woolentor');?></p>

                        <p class="form-row form-row-first">
                            <input type="text" name="coupon_code" class="input-text" placeholder="<?php echo esc_attr('Coupon code', 'woolentor');?>" id="coupon_code" value="" />
                        </p>
                        <p class="form-row form-row-last">
                            <button type="button" class="button" name="apply_coupon" value="<?php echo esc_attr('Apply coupon', 'woolentor');?>"><?php echo esc_html('Apply coupon', 'shopengine');?></button>
                        </p>

                        <div class="clear"></div>
                    </div>

                </div>
            <?php
        }else{
            if( is_checkout() ){
                // woocommerce_checkout_coupon_form();
                ?>
                    <div class="woolentor-checkout-coupon-form">

                        <div class="checkout-coupon-toggle">
                            <div class="woocommerce-info">
                                <?php echo esc_html( apply_filters( 'woocommerce_checkout_coupon_message', esc_html__('Have a coupon?', 'woolentor') ) ); ?>
                                <a href="#" class="show-coupon"><?php echo esc_html__('Click here to enter your code', 'woolentor') ?></a>
                            </div>
                        </div>

                        <div class="coupon-form" style="display:none;">
                            <p><?php echo esc_html('If you have a coupon code, please apply it below.', 'woolentor');?></p>

                            <p class="form-row form-row-first">
                                <input type="text" name="coupon_code" class="input-text" placeholder="<?php echo esc_attr('Coupon code', 'woolentor');?>" id="coupon_code" value="" />
                            </p>
                            <p class="form-row form-row-last">
                                <button type="button" class="button" name="apply_coupon" value="<?php echo esc_attr('Apply coupon', 'woolentor');?>"><?php echo esc_html('Apply coupon', 'shopengine');?></button>
                            </p>

                            <div class="clear"></div>
                        </div>

                    </div>

                <?php

            }
        }
    }

}

Plugin::instance()->widgets_manager->register_widget_type( new WL_Checkout_Coupon_Form_Element() );