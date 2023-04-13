<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Woolentor_Admin_Fields_Pro {

    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Woolentor_Admin_Fields]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        add_filter('woolentor_admin_fields',[ $this, 'admin_fields' ], 10, 1 );

        // Template Builder
        add_filter('woolentor_template_menu_tabs',[ $this, 'template_menu_navs' ], 10, 1 );
        add_filter('woolentor_template_types',[ $this, 'template_type' ], 10, 1 );
        
    }

     /**
     * [admin_fields] Admin Fields
     * @return [array]
     */
    public function admin_fields( $fields ){

        $fields['woolentor_woo_template_tabs'] = array(

            array(
                'name'  => 'enablecustomlayout',
                'label' => esc_html__( 'Enable / Disable Template Builder', 'woolentor-pro' ),
                'desc'  => esc_html__( 'You can enable/disable template builder from here.', 'woolentor-pro' ),
                'type'  => 'checkbox',
                'default' => 'on'
            ),

            array(
                'name'  => 'shoppageproductlimit',
                'label' => esc_html__( 'Product Limit', 'woolentor-pro' ),
                'desc'  => esc_html__( 'You can handle the product limit for the Shop page limit', 'woolentor-pro' ),
                'min'               => 1,
                'max'               => 100,
                'step'              => '1',
                'type'              => 'number',
                'default'           => '2',
                'sanitize_callback' => 'floatval',
                'class'             => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'singleproductpage',
                'label'   => esc_html__( 'Single Product Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'You can select a custom template for the product details page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('single') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productarchivepage',
                'label'   => esc_html__( 'Product Shop Page Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'You can select a custom template for the Shop page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('shop','archive') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productallarchivepage',
                'label'   => esc_html__( 'Product Archive Page Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'You can select a custom template for the Product Archive page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('shop','archive') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productcartpage',
                'label'   => esc_html__( 'Cart Page Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'You can select a template for the Cart page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('cart') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productemptycartpage',
                'label'   => esc_html__( 'Empty Cart Page Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'You can select Custom empty cart page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('emptycart') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productcheckoutpage',
                'label'   => esc_html__( 'Checkout Page Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'You can select a template for the Checkout page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('checkout') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productcheckouttoppage',
                'label'   => esc_html__( 'Checkout Page Top Content', 'woolentor-pro' ),
                'desc'    => esc_html__( 'You can checkout top content(E.g: Coupon form, login form etc)', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('checkouttop') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productthankyoupage',
                'label'   => esc_html__( 'Thank You Page Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'Select a template for the Thank you page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('thankyou') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productmyaccountpage',
                'label'   => esc_html__( 'My Account Page Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'Select a template for the My Account page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('myaccount') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productmyaccountloginpage',
                'label'   => esc_html__( 'My Account Login page Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'Select a template for the Login page layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('myaccountlogin') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'productquickview',
                'label'   => esc_html__( 'Product Quick View Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'Select a template for the product\'s quick view layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('quickview') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

            array(
                'name'    => 'mini_cart_layout',
                'label'   => esc_html__( 'Mini Cart Template', 'woolentor-pro' ),
                'desc'    => esc_html__( 'Select a template for the mini cart layout', 'woolentor-pro' ),
                'type'    => 'selectgroup',
                'default' => '0',
                'options' => [
                    'group'=>[
                        'woolentor' => [
                            'label' => __( 'WooLentor', 'woolentor' ),
                            'options' => function_exists('woolentor_wltemplate_list') ? woolentor_wltemplate_list( array('minicart') ) : null
                        ],
                        'elementor' => [
                            'label' => __( 'Elementor', 'woolentor' ),
                            'options' => woolentor_elementor_template()
                        ]
                    ]
                ],
                'class'   => 'depend_enable_custom_layout',
            ),

        );

        $fields['woolentor_elements_tabs'] = array(

            array(
                'name'  => 'product_tabs',
                'label' => esc_html__( 'Product Tab', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'universal_product',
                'label' => esc_html__( 'Universal Product', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'product_curvy',
                'label' => esc_html__( 'WL: Product Curvy', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'product_image_accordion',
                'label' => esc_html__( 'WL: Product Image Accordion', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'product_accordion',
                'label' => esc_html__( 'WL: Product Accordion', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'add_banner',
                'label' => esc_html__( 'Ads Banner', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'special_day_offer',
                'label' => esc_html__( 'Special Day Offer', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_customer_review',
                'label' => esc_html__( 'Customer Review', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_image_marker',
                'label' => esc_html__( 'Image Marker', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_category',
                'label' => esc_html__( 'Category List', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_category_grid',
                'label' => esc_html__( 'Category Grid', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_onepage_slider',
                'label' => esc_html__( 'One page slider', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_testimonial',
                'label' => esc_html__( 'Testimonial', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_product_grid',
                'label' => esc_html__( 'Product Grid', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_product_expanding_grid',
                'label' => esc_html__( 'Product Expanding Grid', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_product_filterable_grid',
                'label' => esc_html__( 'Product Filterable Grid', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_store_features',
                'label' => esc_html__( 'Store Features', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_faq',
                'label' => esc_html__( 'Faq', 'woolentor' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_brand',
                'label' => esc_html__( 'Brand Logo', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_archive_product',
                'label' => esc_html__( 'Product Archive', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_product_filter',
                'label' => esc_html__( 'Product Filter', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_product_horizontal_filter',
                'label' => esc_html__( 'Product Horizontal Filter', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_title',
                'label' => esc_html__( 'Product Title', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_related',
                'label' => esc_html__( 'Related Product', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_add_to_cart',
                'label' => esc_html__( 'Add to Cart Button', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_additional_information',
                'label' => esc_html__( 'Additional Information', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_data_tab',
                'label' => esc_html__( 'Product data Tab', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_description',
                'label' => esc_html__( 'Product Description', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_short_description',
                'label' => esc_html__( 'Product Short Description', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_price',
                'label' => esc_html__( 'Product Price', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_rating',
                'label' => esc_html__( 'Product Rating', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_reviews',
                'label' => esc_html__( 'Product Reviews', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_image',
                'label' => esc_html__( 'Product Image', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_product_video_gallery',
                'label' => esc_html__( 'Product Video Gallery', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_upsell',
                'label' => esc_html__( 'Product Upsell', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_stock',
                'label' => esc_html__( 'Product Stock Status', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_meta',
                'label' => esc_html__( 'Product Meta Info', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_call_for_price',
                'label' => esc_html__( 'Call for Price', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_suggest_price',
                'label' => esc_html__( 'Suggest Price', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wb_product_qr_code',
                'label' => esc_html__( 'QR Code', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_custom_archive_layout',
                'label' => esc_html__( 'Product Archive Layout', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_cart_table',
                'label' => esc_html__( 'Product Cart Table', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_cart_total',
                'label' => esc_html__( 'Product Cart Total', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_cartempty_message',
                'label' => esc_html__( 'Empty Cart Message', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_cartempty_shopredirect',
                'label' => esc_html__( 'Empty Cart Redirect Button', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_cross_sell',
                'label' => esc_html__( 'Product Cross Sell', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_cross_sell_custom',
                'label' => esc_html__( 'Cross Sell Product..( Custom )', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_checkout_additional_form',
                'label' => esc_html__( 'Checkout Additional..', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_checkout_billing',
                'label' => esc_html__( 'Checkout Billing Form', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_checkout_shipping_form',
                'label' => esc_html__( 'Checkout Shipping Form', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_checkout_payment',
                'label' => esc_html__( 'Checkout Payment', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_checkout_coupon_form',
                'label' => esc_html__( 'Checkout Coupon Form', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_checkout_login_form',
                'label' => esc_html__( 'Checkout Login Form', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_order_review',
                'label' => esc_html__( 'Checkout Order Review', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_account',
                'label' => esc_html__( 'My Account', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_navigation',
                'label' => esc_html__( 'My Account Navigation', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_dashboard',
                'label' => esc_html__( 'My Account Dashboard', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_download',
                'label' => esc_html__( 'My Account Download', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_edit_account',
                'label' => esc_html__( 'My Account Edit', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_address',
                'label' => esc_html__( 'My Account Address', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_login_form',
                'label' => esc_html__( 'Login Form', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_register_form',
                'label' => esc_html__( 'Registration Form', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_logout',
                'label' => esc_html__( 'My Account Logout', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_myaccount_order',
                'label' => esc_html__( 'My Account Order', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_thankyou_order',
                'label' => esc_html__( 'Thank You Order', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_thankyou_customer_address_details',
                'label' => esc_html__( 'Thank You Cus.. Address', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_thankyou_order_details',
                'label' => esc_html__( 'Thank You Order Details', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_product_advance_thumbnails',
                'label' => __( 'Advance Product Image', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),
            
            array(
                'name'  => 'wl_product_advance_thumbnails_zoom',
                'label' => __( 'Product Image With Zoom', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_social_shere',
                'label' => esc_html__( 'Product Social Share', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_stock_progress_bar',
                'label' => esc_html__( 'Stock Progressbar', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_single_product_sale_schedule',
                'label' => esc_html__( 'Product Sale Schedule', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_related_product',
                'label' => esc_html__( 'Related Product..( Custom )', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_product_upsell_custom',
                'label' => esc_html__( 'Upsell Product..( Custom )', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_quickview_product_image',
                'label' => esc_html__( 'Quick view .. image', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),
            
            array(
                'name'  => 'wl_template_selector',
                'label' => esc_html__( 'Template Selector', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

            array(
                'name'  => 'wl_mini_cart',
                'label' => esc_html__( 'Mini Cart', 'woolentor-pro' ),
                'type'  => 'element',
                'default' => 'on'
            ),

        );

        $fields['woolentor_others_tabs'] = array(

            'modules'=> array(

                array(
                    'name'     => 'rename_label_settings',
                    'label'    => esc_html__( 'Rename Label', 'woolentor' ),
                    'type'     => 'module',
                    'default'  => 'off',
                    'section'  => 'woolentor_rename_label_tabs',
                    'option_id'=> 'enablerenamelabel',
                    'require_settings'  => true,
                    'setting_fields' => array(
                        
                        array(
                            'name'  => 'enablerenamelabel',
                            'label' => esc_html__( 'Enable / Disable', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'You can enable / disable rename label from here.', 'woolentor-pro' ),
                            'type'  => 'checkbox',
                            'default' => 'off',
                            'class'   =>'enablerenamelabel woolentor-action-field-left',
                        ),
        
                        array(
                            'name'      => 'shop_page_heading',
                            'headding'  => esc_html__( 'Shop Page', 'woolentor-pro' ),
                            'type'      => 'title',
                            'class'     => 'depend_enable_rename_label',
                        ),
                        
                        array(
                            'name'        => 'wl_shop_add_to_cart_txt',
                            'label'       => esc_html__( 'Add to Cart Button Text', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Change the Add to Cart button text for the Shop page.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'placeholder' => esc_html__( 'Add to Cart', 'woolentor-pro' ),
                            'class'       => 'depend_enable_rename_label woolentor-action-field-left',
                        ),
        
                        array(
                            'name'      => 'product_details_page_heading',
                            'headding'  => esc_html__( 'Product Details Page', 'woolentor-pro' ),
                            'type'      => 'title',
                            'class'     => 'depend_enable_rename_label',
                        ),
        
                        array(
                            'name'        => 'wl_add_to_cart_txt',
                            'label'       => esc_html__( 'Add to Cart Button Text', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Change the Add to Cart button text for the Product details page.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'placeholder' => esc_html__( 'Add to Cart', 'woolentor-pro' ),
                            'class'       => 'depend_enable_rename_label woolentor-action-field-left',
                        ),
        
                        array(
                            'name'        => 'wl_description_tab_menu_title',
                            'label'       => esc_html__( 'Description', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Change the tab title for the product description.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'placeholder' => esc_html__( 'Description', 'woolentor-pro' ),
                            'class'       => 'depend_enable_rename_label woolentor-action-field-left',
                        ),
                        
                        array(
                            'name'        => 'wl_additional_information_tab_menu_title',
                            'label'       => esc_html__( 'Additional Information', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Change the tab title for the product additional information', 'woolentor-pro' ),
                            'type'        => 'text',
                            'placeholder' => esc_html__( 'Additional information', 'woolentor-pro' ),
                            'class'       => 'depend_enable_rename_label woolentor-action-field-left',
                        ),
                        
                        array(
                            'name'        => 'wl_reviews_tab_menu_title',
                            'label'       => esc_html__( 'Reviews', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Change the tab title for the product review', 'woolentor-pro' ),
                            'type'        => 'text',
                            'placeholder' => __( 'Reviews', 'woolentor-pro' ),
                            'class'       =>'depend_enable_rename_label woolentor-action-field-left',
                        ),
        
                        array(
                            'name'      => 'checkout_page_heading',
                            'headding'  => esc_html__( 'Checkout Page', 'woolentor-pro' ),
                            'type'      => 'title',
                            'class'     => 'depend_enable_rename_label',
                        ),
        
                        array(
                            'name'        => 'wl_checkout_placeorder_btn_txt',
                            'label'       => esc_html__( 'Place order', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Change the label for the Place order field.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'placeholder' => esc_html__( 'Place order', 'woolentor-pro' ),
                            'class'       => 'depend_enable_rename_label woolentor-action-field-left',
                        ),

                    )
                ),

                array(
                    'name'     => 'sales_notification_settings',
                    'label'    => esc_html__( 'Sales Notification', 'woolentor-pro' ),
                    'type'     => 'module',
                    'default'  => 'off',
                    'section'  => 'woolentor_sales_notification_tabs',
                    'option_id'=> 'enableresalenotification',
                    'require_settings'=> true,
                    'setting_fields' => array(

                        array(
                            'name'  => 'enableresalenotification',
                            'label' => esc_html__( 'Enable / Disable', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'You can enable / disable sales notification from here.', 'woolentor-pro' ),
                            'type'  => 'checkbox',
                            'default' => 'off',
                            'class' => 'woolentor-action-field-left'
                        ),
                        
                        array(
                            'name'    => 'notification_content_type',
                            'label'   => esc_html__( 'Notification Content Type', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Select Content Type', 'woolentor-pro' ),
                            'type'    => 'radio',
                            'default' => 'actual',
                            'options' => array(
                                'actual' => esc_html__('Real','woolentor-pro'),
                                'fakes'  => esc_html__('Manual','woolentor-pro'),
                            ),
                            'class' => 'woolentor-action-field-left'
                        ),
        
                        array(
                            'name'    => 'noification_fake_data',
                            'label'   => esc_html__( 'Choose Template', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Choose template for manual notification.', 'woolentor-pro' ),
                            'type'    => 'multiselect',
                            'default' => '',
                            'options' => woolentor_elementor_template(),
                            'class'   => 'notification_fake',
                        ),
        
                        array(
                            'name'    => 'notification_pos',
                            'label'   => esc_html__( 'Position', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Set the position of the Sales Notification Position on frontend.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => 'bottomleft',
                            'options' => array(
                                'topleft'       => esc_html__( 'Top Left','woolentor-pro' ),
                                'topright'      => esc_html__( 'Top Right','woolentor-pro' ),
                                'bottomleft'    => esc_html__( 'Bottom Left','woolentor-pro' ),
                                'bottomright'   => esc_html__( 'Bottom Right','woolentor-pro' ),
                            ),
                            'class' => 'woolentor-action-field-left'
                        ),
        
                        array(
                            'name'    => 'notification_layout',
                            'label'   => esc_html__( 'Image Position', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Set the image position of the notification.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => 'imageleft',
                            'options' => array(
                                'imageleft'   => esc_html__( 'Image Left','woolentor-pro' ),
                                'imageright'  => esc_html__( 'Image Right','woolentor-pro' ),
                            ),
                            'class'   => 'notification_real woolentor-action-field-left'
                        ),
        
                        array(
                            'name'    => 'notification_timing_area_title',
                            'headding'=> esc_html__( 'Notification Timing', 'woolentor-pro' ),
                            'type'    => 'title',
                            'size'    => 'margin_0 regular',
                            'class'   => 'element_section_title_area',
                        ),
        
                        array(
                            'name'    => 'notification_loadduration',
                            'label'   => esc_html__( 'First loading time', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'When to start notification load duration.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => '3',
                            'options' => array(
                                '2'    => esc_html__( '2 seconds','woolentor-pro' ),
                                '3'    => esc_html__( '3 seconds','woolentor-pro' ),
                                '4'    => esc_html__( '4 seconds','woolentor-pro' ),
                                '5'    => esc_html__( '5 seconds','woolentor-pro' ),
                                '6'    => esc_html__( '6 seconds','woolentor-pro' ),
                                '7'    => esc_html__( '7 seconds','woolentor-pro' ),
                                '8'    => esc_html__( '8 seconds','woolentor-pro' ),
                                '9'    => esc_html__( '9 seconds','woolentor-pro' ),
                                '10'   => esc_html__( '10 seconds','woolentor-pro' ),
                                '20'   => esc_html__( '20 seconds','woolentor-pro' ),
                                '30'   => esc_html__( '30 seconds','woolentor-pro' ),
                                '40'   => esc_html__( '40 seconds','woolentor-pro' ),
                                '50'   => esc_html__( '50 seconds','woolentor-pro' ),
                                '60'   => esc_html__( '1 minute','woolentor-pro' ),
                                '90'   => esc_html__( '1.5 minutes','woolentor-pro' ),
                                '120'  => esc_html__( '2 minutes','woolentor-pro' ),
                            ),
                            'class' => 'woolentor-action-field-left'
                        ),
        
                        array(
                            'name'    => 'notification_time_showing',
                            'label'   => esc_html__( 'Notification showing time', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'How long to keep the notification.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => '4',
                            'options' => array(
                                '2'   => esc_html__( '2 seconds','woolentor-pro' ),
                                '4'   => esc_html__( '4 seconds','woolentor-pro' ),
                                '5'   => esc_html__( '5 seconds','woolentor-pro' ),
                                '6'   => esc_html__( '6 seconds','woolentor-pro' ),
                                '7'   => esc_html__( '7 seconds','woolentor-pro' ),
                                '8'   => esc_html__( '8 seconds','woolentor-pro' ),
                                '9'   => esc_html__( '9 seconds','woolentor-pro' ),
                                '10'  => esc_html__( '10 seconds','woolentor-pro' ),
                                '20'  => esc_html__( '20 seconds','woolentor-pro' ),
                                '30'  => esc_html__( '30 seconds','woolentor-pro' ),
                                '40'  => esc_html__( '40 seconds','woolentor-pro' ),
                                '50'  => esc_html__( '50 seconds','woolentor-pro' ),
                                '60'  => esc_html__( '1 minute','woolentor-pro' ),
                                '90'  => esc_html__( '1.5 minutes','woolentor-pro' ),
                                '120' => esc_html__( '2 minutes','woolentor-pro' ),
                            ),
                            'class' => 'woolentor-action-field-left'
                        ),
        
                        array(
                            'name'    => 'notification_time_int',
                            'label'   => esc_html__( 'Time Interval', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Set the interval time between notifications.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => '4',
                            'options' => array(
                                '2'   => esc_html__( '2 seconds','woolentor-pro' ),
                                '4'   => esc_html__( '4 seconds','woolentor-pro' ),
                                '5'   => esc_html__( '5 seconds','woolentor-pro' ),
                                '6'   => esc_html__( '6 seconds','woolentor-pro' ),
                                '7'   => esc_html__( '7 seconds','woolentor-pro' ),
                                '8'   => esc_html__( '8 seconds','woolentor-pro' ),
                                '9'   => esc_html__( '9 seconds','woolentor-pro' ),
                                '10'  => esc_html__( '10 seconds','woolentor-pro' ),
                                '20'  => esc_html__( '20 seconds','woolentor-pro' ),
                                '30'  => esc_html__( '30 seconds','woolentor-pro' ),
                                '40'  => esc_html__( '40 seconds','woolentor-pro' ),
                                '50'  => esc_html__( '50 seconds','woolentor-pro' ),
                                '60'  => esc_html__( '1 minute','woolentor-pro' ),
                                '90'  => esc_html__( '1.5 minutes','woolentor-pro' ),
                                '120' => esc_html__( '2 minutes','woolentor-pro' ),
                            ),
                            'class' => 'woolentor-action-field-left'
                        ),
        
                        array(
                            'name'    => 'notification_product_display_option_title',
                            'headding'=> esc_html__( 'Product Query Option', 'woolentor-pro' ),
                            'type'    => 'title',
                            'size'    => 'margin_0 regular',
                            'class'   => 'element_section_title_area notification_real',
                        ),
        
                        array(
                            'name'              => 'notification_limit',
                            'label'             => esc_html__( 'Limit', 'woolentor-pro' ),
                            'desc'              => esc_html__( 'Set the number of notifications to display.', 'woolentor-pro' ),
                            'min'               => 1,
                            'max'               => 100,
                            'default'           => '5',
                            'step'              => '1',
                            'type'              => 'number',
                            'sanitize_callback' => 'number',
                            'class'       => 'notification_real woolentor-action-field-left',
                        ),
        
                        array(
                            'name'  => 'showallproduct',
                            'label' => esc_html__( 'Show/Display all products from each order', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Manage show all product from each order.', 'woolentor-pro' ),
                            'type'  => 'checkbox',
                            'default' => 'off',
                            'class'   => 'notification_real woolentor-action-field-left',
                        ),
        
                        array(
                            'name'    => 'notification_uptodate',
                            'label'   => esc_html__( 'Order Upto', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Do not show purchases older than.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => '7',
                            'options' => array(
                                '1'   => esc_html__( '1 day','woolentor-pro' ),
                                '2'   => esc_html__( '2 days','woolentor-pro' ),
                                '3'   => esc_html__( '3 days','woolentor-pro' ),
                                '4'   => esc_html__( '4 days','woolentor-pro' ),
                                '5'   => esc_html__( '5 days','woolentor-pro' ),
                                '6'   => esc_html__( '6 days','woolentor-pro' ),
                                '7'   => esc_html__( '1 week','woolentor-pro' ),
                                '10'  => esc_html__( '10 days','woolentor-pro' ),
                                '14'  => esc_html__( '2 weeks','woolentor-pro' ),
                                '21'  => esc_html__( '3 weeks','woolentor-pro' ),
                                '28'  => esc_html__( '4 weeks','woolentor-pro' ),
                                '35'  => esc_html__( '5 weeks','woolentor-pro' ),
                                '42'  => esc_html__( '6 weeks','woolentor-pro' ),
                                '49'  => esc_html__( '7 weeks','woolentor-pro' ),
                                '56'  => esc_html__( '8 weeks','woolentor-pro' ),
                            ),
                            'class'       => 'notification_real woolentor-action-field-left',
                        ),
        
                        array(
                            'name'    => 'notification_animation_area_title',
                            'headding'=> esc_html__( 'Animation', 'woolentor-pro' ),
                            'type'    => 'title',
                            'size'    => 'margin_0 regular',
                            'class'   => 'element_section_title_area',
                        ),
        
                        array(
                            'name'    => 'notification_inanimation',
                            'label'   => esc_html__( 'Animation In', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Choose entrance animation.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => 'fadeInLeft',
                            'options' => array(
                                'bounce'            => esc_html__( 'bounce','woolentor-pro' ),
                                'flash'             => esc_html__( 'flash','woolentor-pro' ),
                                'pulse'             => esc_html__( 'pulse','woolentor-pro' ),
                                'rubberBand'        => esc_html__( 'rubberBand','woolentor-pro' ),
                                'shake'             => esc_html__( 'shake','woolentor-pro' ),
                                'swing'             => esc_html__( 'swing','woolentor-pro' ),
                                'tada'              => esc_html__( 'tada','woolentor-pro' ),
                                'wobble'            => esc_html__( 'wobble','woolentor-pro' ),
                                'jello'             => esc_html__( 'jello','woolentor-pro' ),
                                'heartBeat'         => esc_html__( 'heartBeat','woolentor-pro' ),
                                'bounceIn'          => esc_html__( 'bounceIn','woolentor-pro' ),
                                'bounceInDown'      => esc_html__( 'bounceInDown','woolentor-pro' ),
                                'bounceInLeft'      => esc_html__( 'bounceInLeft','woolentor-pro' ),
                                'bounceInRight'     => esc_html__( 'bounceInRight','woolentor-pro' ),
                                'bounceInUp'        => esc_html__( 'bounceInUp','woolentor-pro' ),
                                'fadeIn'            => esc_html__( 'fadeIn','woolentor-pro' ),
                                'fadeInDown'        => esc_html__( 'fadeInDown','woolentor-pro' ),
                                'fadeInDownBig'     => esc_html__( 'fadeInDownBig','woolentor-pro' ),
                                'fadeInLeft'        => esc_html__( 'fadeInLeft','woolentor-pro' ),
                                'fadeInLeftBig'     => esc_html__( 'fadeInLeftBig','woolentor-pro' ),
                                'fadeInRight'       => esc_html__( 'fadeInRight','woolentor-pro' ),
                                'fadeInRightBig'    => esc_html__( 'fadeInRightBig','woolentor-pro' ),
                                'fadeInUp'          => esc_html__( 'fadeInUp','woolentor-pro' ),
                                'fadeInUpBig'       => esc_html__( 'fadeInUpBig','woolentor-pro' ),
                                'flip'              => esc_html__( 'flip','woolentor-pro' ),
                                'flipInX'           => esc_html__( 'flipInX','woolentor-pro' ),
                                'flipInY'           => esc_html__( 'flipInY','woolentor-pro' ),
                                'lightSpeedIn'      => esc_html__( 'lightSpeedIn','woolentor-pro' ),
                                'rotateIn'          => esc_html__( 'rotateIn','woolentor-pro' ),
                                'rotateInDownLeft'  => esc_html__( 'rotateInDownLeft','woolentor-pro' ),
                                'rotateInDownRight' => esc_html__( 'rotateInDownRight','woolentor-pro' ),
                                'rotateInUpLeft'    => esc_html__( 'rotateInUpLeft','woolentor-pro' ),
                                'rotateInUpRight'   => esc_html__( 'rotateInUpRight','woolentor-pro' ),
                                'slideInUp'         => esc_html__( 'slideInUp','woolentor-pro' ),
                                'slideInDown'       => esc_html__( 'slideInDown','woolentor-pro' ),
                                'slideInLeft'       => esc_html__( 'slideInLeft','woolentor-pro' ),
                                'slideInRight'      => esc_html__( 'slideInRight','woolentor-pro' ),
                                'zoomIn'            => esc_html__( 'zoomIn','woolentor-pro' ),
                                'zoomInDown'        => esc_html__( 'zoomInDown','woolentor-pro' ),
                                'zoomInLeft'        => esc_html__( 'zoomInLeft','woolentor-pro' ),
                                'zoomInRight'       => esc_html__( 'zoomInRight','woolentor-pro' ),
                                'zoomInUp'          => esc_html__( 'zoomInUp','woolentor-pro' ),
                                'hinge'             => esc_html__( 'hinge','woolentor-pro' ),
                                'jackInTheBox'      => esc_html__( 'jackInTheBox','woolentor-pro' ),
                                'rollIn'            => esc_html__( 'rollIn','woolentor-pro' ),
                                'rollOut'           => esc_html__( 'rollOut','woolentor-pro' ),
                            ),
                            'class' => 'woolentor-action-field-left'
                        ),
        
                        array(
                            'name'    => 'notification_outanimation',
                            'label'   => esc_html__( 'Animation Out', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Choose exit animation.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => 'fadeOutRight',
                            'options' => array(
                                'bounce'             => esc_html__( 'bounce','woolentor-pro' ),
                                'flash'              => esc_html__( 'flash','woolentor-pro' ),
                                'pulse'              => esc_html__( 'pulse','woolentor-pro' ),
                                'rubberBand'         => esc_html__( 'rubberBand','woolentor-pro' ),
                                'shake'              => esc_html__( 'shake','woolentor-pro' ),
                                'swing'              => esc_html__( 'swing','woolentor-pro' ),
                                'tada'               => esc_html__( 'tada','woolentor-pro' ),
                                'wobble'             => esc_html__( 'wobble','woolentor-pro' ),
                                'jello'              => esc_html__( 'jello','woolentor-pro' ),
                                'heartBeat'          => esc_html__( 'heartBeat','woolentor-pro' ),
                                'bounceOut'          => esc_html__( 'bounceOut','woolentor-pro' ),
                                'bounceOutDown'      => esc_html__( 'bounceOutDown','woolentor-pro' ),
                                'bounceOutLeft'      => esc_html__( 'bounceOutLeft','woolentor-pro' ),
                                'bounceOutRight'     => esc_html__( 'bounceOutRight','woolentor-pro' ),
                                'bounceOutUp'        => esc_html__( 'bounceOutUp','woolentor-pro' ),
                                'fadeOut'            => esc_html__( 'fadeOut','woolentor-pro' ),
                                'fadeOutDown'        => esc_html__( 'fadeOutDown','woolentor-pro' ),
                                'fadeOutDownBig'     => esc_html__( 'fadeOutDownBig','woolentor-pro' ),
                                'fadeOutLeft'        => esc_html__( 'fadeOutLeft','woolentor-pro' ),
                                'fadeOutLeftBig'     => esc_html__( 'fadeOutLeftBig','woolentor-pro' ),
                                'fadeOutRight'       => esc_html__( 'fadeOutRight','woolentor-pro' ),
                                'fadeOutRightBig'    => esc_html__( 'fadeOutRightBig','woolentor-pro' ),
                                'fadeOutUp'          => esc_html__( 'fadeOutUp','woolentor-pro' ),
                                'fadeOutUpBig'       => esc_html__( 'fadeOutUpBig','woolentor-pro' ),
                                'flip'               => esc_html__( 'flip','woolentor-pro' ),
                                'flipOutX'           => esc_html__( 'flipOutX','woolentor-pro' ),
                                'flipOutY'           => esc_html__( 'flipOutY','woolentor-pro' ),
                                'lightSpeedOut'      => esc_html__( 'lightSpeedOut','woolentor-pro' ),
                                'rotateOut'          => esc_html__( 'rotateOut','woolentor-pro' ),
                                'rotateOutDownLeft'  => esc_html__( 'rotateOutDownLeft','woolentor-pro' ),
                                'rotateOutDownRight' => esc_html__( 'rotateOutDownRight','woolentor-pro' ),
                                'rotateOutUpLeft'    => esc_html__( 'rotateOutUpLeft','woolentor-pro' ),
                                'rotateOutUpRight'   => esc_html__( 'rotateOutUpRight','woolentor-pro' ),
                                'slideOutUp'         => esc_html__( 'slideOutUp','woolentor-pro' ),
                                'slideOutDown'       => esc_html__( 'slideOutDown','woolentor-pro' ),
                                'slideOutLeft'       => esc_html__( 'slideOutLeft','woolentor-pro' ),
                                'slideOutRight'      => esc_html__( 'slideOutRight','woolentor-pro' ),
                                'zoomOut'            => esc_html__( 'zoomOut','woolentor-pro' ),
                                'zoomOutDown'        => esc_html__( 'zoomOutDown','woolentor-pro' ),
                                'zoomOutLeft'        => esc_html__( 'zoomOutLeft','woolentor-pro' ),
                                'zoomOutRight'       => esc_html__( 'zoomOutRight','woolentor-pro' ),
                                'zoomOutUp'          => esc_html__( 'zoomOutUp','woolentor-pro' ),
                                'hinge'              => esc_html__( 'hinge','woolentor-pro' ),
                            ),
                            'class' => 'woolentor-action-field-left'
                        ),
                        
                        array(
                            'name'    => 'notification_style_area_title',
                            'headding'=> esc_html__( 'Style', 'woolentor-pro' ),
                            'type'    => 'title',
                            'size'    => 'margin_0 regular',
                            'class' => 'element_section_title_area',
                        ),
        
                        array(
                            'name'        => 'notification_width',
                            'label'       => esc_html__( 'Width', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'You can handle the notificaton width.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( '550px', 'woolentor-pro' ),
                            'placeholder' => esc_html__( '550px', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left'
                        ),
        
                        array(
                            'name'        => 'notification_mobile_width',
                            'label'       => esc_html__( 'Width for mobile', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'You can handle the notificaton width.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( '90%', 'woolentor-pro' ),
                            'placeholder' => esc_html__( '90%', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left'
                        ),
        
                        array(
                            'name'  => 'background_color',
                            'label' => esc_html__( 'Background Color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Set the background color of the notification.', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'notification_real woolentor-action-field-left',
                        ),
        
                        array(
                            'name'  => 'heading_color',
                            'label' => esc_html__( 'Heading Color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Set the heading color of the notification.', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'notification_real woolentor-action-field-left',
                        ),
        
                        array(
                            'name'  => 'content_color',
                            'label' => esc_html__( 'Content Color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Set the content color of the notification.', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'notification_real woolentor-action-field-left',
                        ),
        
                        array(
                            'name'  => 'cross_color',
                            'label' => esc_html__( 'Cross Icon Color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Set the cross icon color of the notification.', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),

                    )
                ),

                array(
                    'name'     => 'shopify_checkout_settings',
                    'label'    => esc_html__( 'Shopify Style Checkout', 'woolentor-pro' ),
                    'type'     => 'module',
                    'default'  => 'off',
                    'section'  => 'woolentor_shopify_checkout_settings',
                    'option_id'=> 'enable',
                    'require_settings'  => true,
                    'setting_fields' => array(

                        array(
                            'name'  => 'enable',
                            'label' => esc_html__( 'Enable / Disable', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'You can enable / disable shopify style checkout page from here.', 'woolentor-pro' ),
                            'type'  => 'checkbox',
                            'default' => 'off',
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'    => 'logo',
                            'label'   => esc_html__( 'Logo', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'You can upload your logo for shopify style checkout page from here.', 'woolentor-pro' ),
                            'type'    => 'image_upload',
                            'options' => [
                                'button_label'        => esc_html__( 'Upload', 'woolentor-pro' ),   
                                'button_remove_label' => esc_html__( 'Remove', 'woolentor-pro' ),   
                            ],
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'    => 'custommenu',
                            'label'   => esc_html__( 'Bottom Menu', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'You can choose menu for shopify style checkout page.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => '0',
                            'options' => function_exists('woolentor_get_all_create_menus') ? array( '0'=> esc_html__('Select Menu','woolentor-pro') ) + woolentor_get_all_create_menus() : null,
                            'class' => 'woolentor-action-field-left'
                        ),
                        
                    )

                ),
                
                array(
                    'name'     => 'woolentor_flash_sale_event_settings',
                    'label'    => esc_html__( 'Flash Sale Countdown', 'woolentor' ),
                    'type'     => 'module',
                    'default'  => 'off',
                    'section'  => 'woolentor_flash_sale_settings',
                    'option_id'=> 'enable',
                    'require_settings'  => true,
                    'setting_fields' => array(

                        array(
                            'name'  => 'enable',
                            'label' => esc_html__( 'Enable / Disable', 'woolentor' ),
                            'desc'  => esc_html__( 'You can enable / disable flash sale from here.', 'woolentor' ),
                            'type'  => 'checkbox',
                            'default' => 'off',
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'    => 'override_sale_price',
                            'label'   => esc_html__( 'Override Sale Price', 'woolentor' ),
                            'type'    => 'checkbox',
                            'default' => 'off',
                            'class'   => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'    => 'enable_countdown_on_product_details_page',
                            'label'   => esc_html__( 'Show Countdown On Product Details Page', 'woolentor' ),
                            'type'    => 'checkbox',
                            'default' => 'on',
                            'class'   => 'woolentor-action-field-left'
                        ),

                         array(
                             'name'        => 'countdown_position',
                             'label'       => esc_html__( 'Countdown Position', 'woolentor' ),
                             'type'        => 'select',
                             'options'     => array(
                                'woocommerce_before_add_to_cart_form'      => esc_html__('Add to cart - Before', 'woolentor'),
                                'woocommerce_after_add_to_cart_form'       => esc_html__('Add to cart - After', 'woolentor'),
                                'woocommerce_product_meta_start'           => esc_html__('Product meta - Before', 'woolentor'),
                                'woocommerce_product_meta_end'             => esc_html__('Product meta - After', 'woolentor'),
                                'woocommerce_single_product_summary'       => esc_html__('Product summary - Before', 'woolentor'),
                                'woocommerce_after_single_product_summary' => esc_html__('Product summary - After', 'woolentor'),
                             ),
                             'class'       => 'woolentor-action-field-left'
                         ),

                        array(
                            'name'    => 'countdown_timer_title',
                            'label'   => esc_html__( 'Countdown Timer Title', 'woolentor' ),
                            'type'    => 'text',
                            'default' => esc_html__('Hurry Up! Offer ends in', 'woolentor'),
                            'class'   => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'        => 'deals',
                            'label'       => esc_html__( 'Sale Events', 'woolentor' ),
                            'desc'        => esc_html__( 'Repeater field description', 'woolentor' ),
                            'type'        => 'repeater',
                            'title_field' => 'title',
                            'fields'  => [

                                array(
                                    'name'        => 'status',
                                    'label'       => esc_html__( 'Enable', 'woolentor' ),
                                    'desc'        => esc_html__( 'Enable / Disable', 'woolentor' ),
                                    'type'        => 'checkbox',
                                    'default'     => 'on',
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'        => 'title',
                                    'label'       => esc_html__( 'Event Name', 'woolentor' ),
                                    'type'        => 'text',
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'        => 'start_date',
                                    'label'       => esc_html__( 'Valid From', 'woolentor' ),
                                    'desc'        => __( 'The date and time the event should be enabled. Please set time based on your server time settings. Current Server Date / Time: '. current_time('Y M d'), 'woolentor' ),
                                    'type'        => 'date',
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'        => 'end_date',
                                    'label'       => esc_html__( 'Valid To', 'woolentor' ),
                                    'desc'        => esc_html__( 'The date and time the event should be disabled.', 'woolentor' ),
                                    'type'        => 'date',
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'        => 'apply_on_all_products',
                                    'label'       => esc_html__( 'Apply On All Products', 'woolentor' ),
                                    'type'        => 'checkbox',
                                    'default'     => 'off',
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'        => 'categories',
                                    'label'       => esc_html__( 'Select Categories', 'woolentor' ),
                                    'desc'        => esc_html__( 'Select the categories in wich products the discount will be applied.', 'woolentor' ),
                                    'type'        => 'multiselect',
                                    'options'     => woolentor_taxonomy_list('product_cat','term_id'),
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'        => 'products',
                                    'label'       => esc_html__( 'Select Products', 'woolentor' ),
                                    'desc'        => esc_html__( 'Select individual products in wich the discount will be applied.', 'woolentor' ),
                                    'type'        => 'multiselect',
                                    'options'     => woolentor_post_name( 'product' ),
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'        => 'exclude_products',
                                    'label'       => esc_html__( 'Exclude Products', 'woolentor' ),
                                    'type'        => 'multiselect',
                                    'options'     => woolentor_post_name( 'product' ),
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'        => 'discount_type',
                                    'label'       => esc_html__( 'Discount Type', 'woolentor' ),
                                    'type'        => 'select',
                                    'options'     => array(
                                        'fixed_discount'      => esc_html__( 'Fixed Discount', 'woolentor' ),
                                        'percentage_discount' => esc_html__( 'Percentage Discount', 'woolentor' ),
                                        'fixed_price'         => esc_html__( 'Fixed Price', 'woolentor' ),
                                    ),
                                    'class'       => 'woolentor-action-field-left'
                                ),

                                array(
                                    'name'  => 'discount_value',
                                    'label' => esc_html__( 'Discount Value', 'woolentor-pro' ),
                                    'min'               => 0.0,
                                    'step'              => 0.01,
                                    'type'              => 'number',
                                    'default'           => '50',
                                    'sanitize_callback' => 'floatval',
                                    'class'             => 'woolentor-action-field-left',
                                ),

                                array(
                                    'name'        => 'apply_discount_only_for_registered_customers',
                                    'label'       => esc_html__( 'Apply Discount Only For Registered Customers', 'woolentor' ),
                                    'type'        => 'checkbox',
                                    'class'       => 'woolentor-action-field-left'
                                ),

                            ]
                        ),
                        
                    )

                ),

                array(
                    'name'     => 'partial_payment',
                    'label'    => esc_html__( 'Partial Payment', 'woolentor-pro' ),
                    'type'     => 'module',
                    'default'  => 'off',
                    'section'  => 'woolentor_partial_payment_settings',
                    'option_id'=> 'enable',
                    'require_settings'  => true,
                    'setting_fields' => array(

                        array(
                            'name'  => 'enable',
                            'label' => esc_html__( 'Enable / Disable', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'You can enable / disable partial payment from here.', 'woolentor-pro' ),
                            'type'  => 'checkbox',
                            'default' => 'off',
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'    => 'amount_type',
                            'label'   => esc_html__( 'Amount Type', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Choose how you want to received the partial payment.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => 'percentage',
                            'options' => [
                                'fixedamount' => esc_html__('Fixed Amount','woolentor-pro'),
                                'percentage' => esc_html__('Percentage','woolentor-pro'),
                            ],
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'  => 'amount',
                            'label' => esc_html__( 'Amount', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Enter the partial payment amount based on the amount type you chose above (should not be more than 99 for percentage or more than order total for fixed )', 'woolentor-pro' ),
                            'min'               => 0.0,
                            'step'              => 0.01,
                            'type'              => 'number',
                            'default'           => '50',
                            'sanitize_callback' => 'floatval',
                            'class'             => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'    => 'default_selected',
                            'label'   => esc_html__( 'Default payment type', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Select a payment type that you want to set by default.', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => 'partial',
                            'options' => [
                                'partial' => esc_html__('Partial Payment','woolentor-pro'),
                                'full'    => esc_html__('Full Payment','woolentor-pro'),
                            ],
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'    => 'disallowed_payment_method_ppf',
                            'label'   => esc_html__( 'Disallowed payment method for first installment', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Select payment methods that you want to disallow for first installment.', 'woolentor-pro' ),
                            'type'    => 'multiselect',
                            'options' => function_exists('woolentor_get_payment_method') ? woolentor_get_payment_method() : ['notfound'=>esc_html__('Not Found','woolentor-pro')],
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'    => 'disallowed_payment_method_pps',
                            'label'   => esc_html__( 'Disallowed payment method for second installment', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Select payment methods that you want to disallow for second installment.', 'woolentor-pro' ),
                            'type'    => 'multiselect',
                            'options' => function_exists('woolentor_get_payment_method') ? woolentor_get_payment_method() : ['notfound'=>esc_html__('Not Found','woolentor-pro')],
                            'class' => 'woolentor-action-field-left'
                        ),

                        // array(
                        //     'name'  => 'payment_reminder',
                        //     'label' => esc_html__( 'Second installment payment reminder date in day', 'woolentor-pro' ),
                        //     'desc'  => esc_html__( 'Send a reminder email before second payment due date', 'woolentor-pro' ),
                        //     'type'              => 'number',
                        //     'default'           => '5',
                        //     'sanitize_callback' => 'floatval',
                        //     'class'             => 'woolentor-action-field-left',
                        // ),

                        array(
                            'name'    => 'shop_loop_btn_area_title',
                            'headding'=> esc_html__( 'Shop / Product Loop', 'woolentor-pro' ),
                            'type'    => 'title',
                            'size'    => 'margin_0 regular',
                            'class'   => 'element_section_title_area',
                        ),

                        array(
                            'name'        => 'partial_payment_loop_btn_text',
                            'label'       => esc_html__( 'Add to cart button text', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'You can change the add to cart button text for the products that allow partial payment.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'placeholder' => esc_html__( 'Partial Payment', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'    => 'single_product_custom_text_title',
                            'headding'=> esc_html__( 'Single Product', 'woolentor-pro' ),
                            'type'    => 'title',
                            'size'    => 'margin_0 regular',
                            'class'   => 'element_section_title_area',
                        ),

                        array(
                            'name'        => 'partial_payment_button_text',
                            'label'       => esc_html__( 'Partial payment button label', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Insert the label for the partial payment option.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'placeholder' => esc_html__( 'Partial Payment', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'        => 'full_payment_button_text',
                            'label'       => esc_html__( 'Full payment button label', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Insert the label for the full payment option.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'Full Payment', 'woolentor-pro' ),
                            'placeholder' => esc_html__( 'Full Payment', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'        => 'partial_payment_discount_text',
                            'label'       => esc_html__( 'First deposit label', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Insert the first deposit label from here.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'First Installment', 'woolentor-pro' ),
                            'placeholder' => esc_html__( 'First Installment', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'    => 'checkout_custom_text_title',
                            'headding'=> esc_html__( 'Cart / Checkout', 'woolentor-pro' ),
                            'type'    => 'title',
                            'size'    => 'margin_0 regular',
                            'class'   => 'element_section_title_area',
                        ),

                        array(
                            'name'        => 'first_installment_text',
                            'label'       => esc_html__( 'First installment amount label', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Enter the first installment amount label.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'First Installment', 'woolentor-pro' ),
                            'placeholder' => esc_html__( 'First Installment', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'        => 'second_installment_text',
                            'label'       => esc_html__( 'Second installment amount label', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Enter the second installment amount label.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'Second Installment', 'woolentor-pro' ),
                            'placeholder' => esc_html__( 'Second Installment', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'        => 'to_pay',
                            'label'       => esc_html__( 'Amount to pay label', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Enter the label for amount to pay.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'To Pay', 'woolentor-pro' ),
                            'placeholder' => esc_html__( 'To Pay', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),
                        
                    )

                ),

                array(
                    'name'     => 'pre_orders',
                    'label'    => esc_html__( 'Pre Orders', 'woolentor-pro' ),
                    'type'     => 'module',
                    'default'  => 'off',
                    'section'  => 'woolentor_pre_order_settings',
                    'option_id'=> 'enable',
                    'require_settings'  => true,
                    'setting_fields' => array(

                        array(
                            'name'  => 'enable',
                            'label' => esc_html__( 'Enable / Disable', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'You can enable / disable pre orders from here.', 'woolentor-pro' ),
                            'type'  => 'checkbox',
                            'default' => 'off',
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'        => 'add_to_cart_btn_text',
                            'label'       => esc_html__( 'Add to cart button text', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'You can change the add to cart button text for the products that allow pre order.', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__('Pre Order','woolentor-pro'),
                            'placeholder' => esc_html__( 'Pre Order', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'        => 'manage_price_lavel',
                            'label'       => esc_html__( 'Manage Price Label', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Manage how you want the price labels to appear, or leave it blank to display only the pre-order price without any labels. Available placeholders: {original_price}, {preorder_price}', 'woolentor-pro' ),
                            'default'     => esc_html__( '{original_price} Pre order price: {preorder_price}', 'woolentor-pro' ),
                            'type'        => 'text',
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'        => 'availability_date',
                            'label'       => esc_html__( 'Availability date label', 'woolentor-pro' ),
                            'desc'        => esc_html__( 'Manage how you want the availability date labels to appear. Available placeholders: {availability_date}', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'Available on: {availability_date}', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                        array(
                            'name'  => 'show_countdown',
                            'label' => esc_html__( 'Show Countdown', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'You can enable / disable pre orders countdown from here.', 'woolentor-pro' ),
                            'type'  => 'checkbox',
                            'default' => 'on',
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'    => 'countdown_heading_title',
                            'headding'=> esc_html__( 'Countdown Custom Label', 'woolentor-pro' ),
                            'type'    => 'title',
                            'size'    => 'margin_0 regular',
                            'class'   => 'element_section_title_area',
                        ),

                        array(
                            'name'        => 'customlabel_days',
                            'label'       => esc_html__( 'Days', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'Days', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),
                        array(
                            'name'        => 'customlabel_hours',
                            'label'       => esc_html__( 'Hours', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'Hours', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),
                        array(
                            'name'        => 'customlabel_minutes',
                            'label'       => esc_html__( 'Minutes', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'Min', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),
                        array(
                            'name'        => 'customlabel_seconds',
                            'label'       => esc_html__( 'Seconds', 'woolentor-pro' ),
                            'type'        => 'text',
                            'default'     => esc_html__( 'Sec', 'woolentor-pro' ),
                            'class'       => 'woolentor-action-field-left',
                        ),

                    ),
                ),

                array(
                    'name'  => 'ajaxsearch',
                    'label' => esc_html__( 'Ajax Search Widget', 'woolentor-pro' ),
                    'desc'  => esc_html__( 'AJAX Search Widget', 'woolentor-pro' ),
                    'type'   => 'element',
                    'default'=> 'off'
                ),
    
                array(
                    'name'   => 'ajaxcart_singleproduct',
                    'label'  => esc_html__( 'Single Product Ajax Add To Cart', 'woolentor-pro' ),
                    'desc'   => esc_html__( 'AJAX Add to Cart on Single Product page', 'woolentor-pro' ),
                    'type'   => 'element',
                    'default'=> 'off'
                ),
    
                array(
                    'name'   => 'single_product_sticky_add_to_cart',
                    'label'  => esc_html__( 'Single Product Sticky Add To Cart', 'woolentor-pro' ),
                    'desc'   => esc_html__( 'Sticky Add to Cart on Single Product page', 'woolentor-pro' ),
                    'type'   => 'element',
                    'default'=> 'off',
                    'class'  =>'single_product_sticky_add_to_cart',
                    'require_settings'  => true,
                    'setting_fields' => array(
                        
                        array(
                            'name'  => 'sps_add_to_cart_color',
                            'label' => esc_html__( 'Sticky cart button color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Single product sticky add to cart button color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left',
                        ),
            
                        array(
                            'name'  => 'sps_add_to_cart_bg_color',
                            'label' => esc_html__( 'Sticky cart button background color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Single product sticky add to cart button background color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left',
                        ),
            
                        array(
                            'name'  => 'sps_add_to_cart_hover_color',
                            'label' => esc_html__( 'Sticky cart button hover color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Single product sticky add to cart button hover color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left',
                        ),
            
                        array(
                            'name'  => 'sps_add_to_cart_bg_hover_color',
                            'label' => esc_html__( 'Sticky cart button background color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Single product sticky add to cart button background color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left',
                        ),
            
                        array(
                            'name'    => 'sps_add_to_cart_padding',
                            'label'   => esc_html__( 'Sticky cart button padding', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Single product sticky add to cart button padding', 'woolentor-pro' ),
                            'type'    => 'dimensions',
                            'options' => [
                                'top'   => esc_html__( 'Top', 'woolentor-pro' ),
                                'right' => esc_html__( 'Right', 'woolentor-pro' ),   
                                'bottom'=> esc_html__( 'Bottom', 'woolentor-pro' ),   
                                'left'  => esc_html__( 'Left', 'woolentor-pro' ),
                                'unit'  => esc_html__( 'Unit', 'woolentor-pro' ),
                            ]
                        ),

                    )
                ),

                array(
                    'name'   => 'mini_side_cart',
                    'label'  => esc_html__( 'Side Mini Cart', 'woolentor-pro' ),
                    'type'   => 'element',
                    'default'=> 'off',
                    'class'  =>'side_mini_cart',
                    'require_settings'  => true,
                    'setting_fields' => array(
                        
                        array(
                            'name'    => 'mini_cart_position',
                            'label'   => esc_html__( 'Mini Cart Position', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'Set the position of the Mini Cart .', 'woolentor-pro' ),
                            'type'    => 'select',
                            'default' => 'left',
                            'options' => array(
                                'left'   => esc_html__( 'Left','woolentor-pro' ),
                                'right'  => esc_html__( 'Right','woolentor-pro' ),
                            ),
                            'class' => 'woolentor-action-field-left',
                        ),
            
                        array(
                            'name'    => 'mini_cart_icon',
                            'label'   => esc_html__( 'Mini Cart Icon', 'woolentor-pro' ),
                            'desc'    => esc_html__( 'You can manage the side mini cart toggler icon.', 'woolentor-pro' ),
                            'type'    => 'text',
                            'default' => 'sli sli-basket-loaded',
                            'class'   => 'woolentor_icon_picker woolentor-action-field-left'
                        ),
            
                        array(
                            'name'  => 'mini_cart_icon_color',
                            'label' => esc_html__( 'Mini cart icon color', 'woolentor' ),
                            'desc'  => esc_html__( 'Side mini cart icon color', 'woolentor' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),
            
                        array(
                            'name'  => 'mini_cart_icon_bg_color',
                            'label' => esc_html__( 'Mini cart icon background color', 'woolentor' ),
                            'desc'  => esc_html__( 'Side mini cart icon background color', 'woolentor' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),
            
                        array(
                            'name'  => 'mini_cart_icon_border_color',
                            'label' => esc_html__( 'Mini cart icon border color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Side mini cart icon border color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),
            
                        array(
                            'name'  => 'mini_cart_counter_color',
                            'label' => esc_html__( 'Mini cart counter color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Side mini cart counter color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),
            
                        array(
                            'name'  => 'mini_cart_counter_bg_color',
                            'label' => esc_html__( 'Mini cart counter background color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Side mini cart counter background color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'      => 'mini_cart_button_heading',
                            'headding'  => esc_html__( 'Buttons', 'woolentor-pro' ),
                            'type'      => 'title'
                        ),

                        array(
                            'name'  => 'mini_cart_buttons_color',
                            'label' => esc_html__( 'Mini cart buttons color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Side mini cart buttons color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),
                        array(
                            'name'  => 'mini_cart_buttons_bg_color',
                            'label' => esc_html__( 'Mini cart buttons background color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Side mini cart buttons background color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),

                        array(
                            'name'  => 'mini_cart_buttons_hover_color',
                            'label' => esc_html__( 'Mini cart buttons hover color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Side mini cart buttons hover color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),
                        array(
                            'name'  => 'mini_cart_buttons_hover_bg_color',
                            'label' => esc_html__( 'Mini cart buttons hover background color', 'woolentor-pro' ),
                            'desc'  => esc_html__( 'Side mini cart buttons hover background color', 'woolentor-pro' ),
                            'type'  => 'color',
                            'class' => 'woolentor-action-field-left'
                        ),

                    )
                ),

                array(
                    'name'   => 'redirect_add_to_cart',
                    'label'  => esc_html__( 'Redirect to Checkout on Add to Cart', 'woolentor-pro' ),
                    'type'   => 'element',
                    'default'=> 'off',
                ),
    
                array(
                    'name'   => 'multi_step_checkout',
                    'label'  => esc_html__( 'Multi Step Checkout', 'woolentor-pro' ),
                    'type'   => 'element',
                    'default'=> 'off',
                ),

            ),

            'others' => array(

                array(
                    'name'  => 'loadproductlimit',
                    'label' => esc_html__( 'Load Products in Elementor Widget', 'woolentor-pro' ),
                    'desc'  => esc_html__( 'Set the number of products to load in Elementor Widgets.', 'woolentor-pro' ),
                    'min'               => 1,
                    'max'               => 100,
                    'step'              => '1',
                    'type'              => 'number',
                    'default'           => '20',
                    'sanitize_callback' => 'floatval'
                )

            ),

        );

        // Post Duplicator Condition
        if( !is_plugin_active('ht-mega-for-elementor/htmega_addons_elementor.php') ){
            
            $post_types = woolentor_get_post_types( array('defaultadd'=>'all') );
            if ( did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' ) ) {
                $post_types['elementor_library'] = esc_html__( 'Templates', 'woolentor' );
            }

            $fields['woolentor_others_tabs']['modules'][] = [
                'name'  => 'postduplicator',
                'label'  => esc_html__( 'Post Duplicator', 'woolentor-pro' ),
                'type'  => 'element',
                'default'=>'off',
                'require_settings'  => true,
                'setting_fields' => array(
                    
                    array(
                        'name'    => 'postduplicate_condition',
                        'label'   => esc_html__( 'Post Duplicator Condition', 'woolentor' ),
                        'desc'    => esc_html__( 'You can enable duplicator for individual post.', 'woolentor' ),
                        'type'    => 'multiselect',
                        'default' => '',
                        'options' => $post_types
                    )

                )
            ];

        }

        // Wishsuite Addons
        if( is_plugin_active('wishsuite/wishsuite.php') ){
            $fields['woolentor_elements_tabs'][] = [
                'name'   => 'wb_wishsuite_table',
                'label'  => esc_html__( 'WishSuite Table', 'woolentor' ),
                'type'   => 'element',
                'default' => 'on'
            ];
        }

        // Ever Compare Addons
        if( is_plugin_active('ever-compare/ever-compare.php') ){
            $fields['woolentor_elements_tabs'][] = [
                'name'   => 'wb_ever_compare_table',
                'label'  => esc_html__( 'Ever Compare', 'woolentor' ),
                'type'   => 'element',
                'default' => 'on'
            ];
        }

        // JustTable Addons
        if( is_plugin_active('just-tables/just-tables.php') || is_plugin_active('just-tables-pro/just-tables-pro.php') ){
            $fields['woolentor_elements_tabs'][] = [
                'name'   => 'wb_just_table',
                'label'  => esc_html__( 'JustTable', 'woolentor' ),
                'type'   => 'element',
                'default' => 'on'
            ];
        }

        // whols Addons
        if( is_plugin_active('whols/whols.php') || is_plugin_active('whols-pro/whols-pro.php') ){
            $fields['woolentor_elements_tabs'][] = [
                'name'   => 'wb_whols',
                'label'  => esc_html__( 'Whols', 'woolentor' ),
                'type'   => 'element',
                'default' => 'on'
            ];
        }

        // Multicurrency Addons
        if( is_plugin_active('wc-multi-currency/wcmilticurrency.php') || is_plugin_active('multicurrencypro/multicurrencypro.php') ){
            $fields['woolentor_elements_tabs'][] = [
                'name'   => 'wb_wc_multicurrency',
                'label'  => esc_html__( 'Multi Currency', 'woolentor' ),
                'type'   => 'element',
                'default' => 'on'
            ];
        }

        return $fields;

    }

     /**
     * [template_menu_navs] Admin Post Type tabs
     * @return [array]
     */
    public function template_menu_navs( $navs ){

        $tabs = [
			'shop' => [
				'label' =>__('Shop','woolentor')
			],
			'archive' => [
				'label'		=>__('Archive','woolentor')
			],
			'single' => [
				'label' => __('Single','woolentor')
			],
			'cart' => [
				'label'		=>__('Cart','woolentor'),
				'submenu' 	=>[
					'emptycart' => [
						'label'	=>__('Empty Cart','woolentor-pro')
					],
					'minicart' => [
						'label'		=> __('Side Mini Cart' ,'woolentor-pro')
					],
				]
			],
			'checkout' => [
				'label'	=>__('Checkout','woolentor-pro'),
				'submenu' => [
					'checkouttop' => [
						'label'	=>__('Checkout Top','woolentor-pro')
					],
				]
			],
			'thankyou' => [
				'label'	=>__('Thank You','woolentor')
			],
			'myaccount' => [
				'label'	  =>__('My Account','woolentor'),
				'submenu' => [
					'myaccountlogin' => [
						'label'	=> __('Login / Register','woolentor-pro')
					],
					'dashboard' => [
						'label'	=> __('Dashboard','woolentor-pro')
					],
					'orders' => [
						'label'	=> __('Orders','woolentor-pro')
					],
					'downloads' => [
						'label'	=> __('Downloads','woolentor-pro')
					],
					'edit-address' => [
						'label'	=> __('Address','woolentor-pro')
					],
					'edit-account' => [
						'label'	=> __('Account Details','woolentor-pro')
					],
				]
			],
			'quickview' => [
				'label'	=> __('QuickView','woolentor-pro')
			],
			
		];

        $navs = $tabs;

        return $navs;

    }

     /**
     * [template_type] Template types
     * @return [array]
     */
    function template_type( $types ){

        $template_type = [
			'shop' 	=> [
				'label'		=>__('Shop','woolentor'),
				'optionkey'	=> 'productarchivepage'
			],
			'archive' => [
				'label'		=>__('Archive','woolentor'),
				'optionkey'	=>'productallarchivepage'
			],
			'single' => [
				'label' 	=> __('Single','woolentor'),
				'optionkey' => 'singleproductpage'
			],
			'cart' => [
				'label'		=>__('Cart','woolentor'),
				'optionkey'	=>'productcartpage'
			],
			'emptycart' => [
				'label'		=>__('Empty Cart','woolentor'),
				'optionkey'	=>'productemptycartpage'
			],
			'checkout' => [
				'label'		=>__('Checkout','woolentor'),
				'optionkey'	=>'productcheckoutpage'
			],
			'checkouttop' => [
				'label'		=>__('Checkout Top','woolentor'),
				'optionkey'	=>'productcheckouttoppage'
			],
			'thankyou' => [
				'label'		=>__('Thank You','woolentor'),
				'optionkey'	=>'productthankyoupage'
			],
			'myaccount' => [
				'label'		=>__('My Account','woolentor'),
				'optionkey'	=>'productmyaccountpage'
			],
			'myaccountlogin' => [
				'label'		=> __('My Account Login / Register','woolentor'),
				'optionkey'	=> 'productmyaccountloginpage'
			],
            'dashboard' => [
                'label'	    => __('My Account Dashboard','woolentor-pro'),
                'optionkey'	=> 'dashboard'
            ],
            'orders' => [
                'label'	=> __('My Account Orders','woolentor-pro'),
                'optionkey'	=> 'orders'
            ],
            'downloads' => [
                'label'	=> __('My Account Downloads','woolentor-pro'),
                'optionkey'	=> 'downloads'
            ],
            'edit-address' => [
                'label'	=> __('My Account Address','woolentor-pro'),
                'optionkey'	=> 'edit-address'
            ],
            'edit-account' => [
                'label'	=> __('My Account Details','woolentor-pro'),
                'optionkey'	=> 'edit-account'
            ],
			'quickview' => [
				'label'		=> __('QuickView','woolentor'),
				'optionkey'	=> 'productquickview'
			],
			'minicart' => [
				'label'		=> __('Side Mini Cart' ,'woolentor'),
				'optionkey'	=> 'mini_cart_layout'
			],
		];

        $types = $template_type;

        return $types;

    }

}

Woolentor_Admin_Fields_Pro::instance();