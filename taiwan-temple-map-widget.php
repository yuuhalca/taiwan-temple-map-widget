<?php
/*
Plugin Name: 台湾寺院マップウィジェット
Description: Elementor用のGoogle Mapsウィジェット。投稿のカスタムフィールドからマーカーを表示。
Version: 1.0
Author: あなたの名前
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Elementorウィジェット登録
add_action('elementor/widgets/widgets_registered', function() {
    if (defined('ELEMENTOR_PATH') || class_exists('Elementor\Widget_Base')) {
        require_once __DIR__ . '/widget-taiwan-temple-map.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Taiwan_Temple_Map_Widget());
    }
});
