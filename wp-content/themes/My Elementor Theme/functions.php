<?php

function my_elementor_theme_scripts() {
    wp_enqueue_style( 'my-elementor-theme-style', get_stylesheet_uri() );
}

add_action( 'wp_enqueue_scripts', 'my_elementor_theme_scripts' );

register_nav_menu('primary','Primary menu');


