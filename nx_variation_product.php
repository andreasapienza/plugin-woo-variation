<?php

/**
 * Plugin Name:     nx-variation-product
 * Description:     Plugin per visualizzione personalizzata variazione prodotti
 * Author:          Andrea Sapienza
 * Author URI:      www.test.it
 * Text Domain:     nx-variation-product
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Nx_Variation_Product
 */


if ( !defined('ABSPATH')) {
    exit;
}


define( 'NX_PLUGIN_VERSION', '1.0.0' );


function nxvar_admin_style()
{
    if ( !is_admin() ) {
        wp_enqueue_style('nxvar_css', plugins_url('css/style_frontend.css', __FILE__));
        wp_enqueue_script('jquery');
        wp_enqueue_script('nxvar_js', plugins_url('js/main.js', __FILE__));
    }
}
add_action('wp_enqueue_scripts', 'nxvar_admin_style');


add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'nx_display_variations', 20, 2 );
function nx_display_variations($output) {
    global $product;

    if ( $product->is_type( 'variable' ) ) {
        if(empty($product->get_available_variations())) {
            return $output;
        }

        $output = '<div style="display:none">' . $output . '</div>';


        $variations = $product->get_available_variations();

        if ( ! empty( $variations ) ) {
            $output .= '<div class="nx_variation__container">';

            foreach ( $variations as $variation ) {

                $variation_id = $variation['variation_id'];
                $product_variation = new WC_Product_Variation( $variation_id );

                $variation_attributes = $product_variation->get_attributes();
                $variation_slug = reset($variation_attributes);
                $variation_name = str_replace("-", " ", $variation_slug);
                $variation_price_html = $product_variation->get_price_html();
                $variation_price = $product_variation->get_price();
                //$variation_description = $product_variation->get_description();
                $variation_weight = $product_variation->get_weight(); // Get weight from variation
                $show_price_gr = '';

                if ( $variation_weight ) {

                    $variation_weight = intval( $variation_weight );
                    //$variation_price = intval($variation_price);
                    $variation_price = number_format($variation_price, 2, '.', '');
                    $calc_price_gr = $variation_price / $variation_weight;

                    $show_price_gr =  number_format((float)$calc_price_gr, 2, ',', '')  . __( '€/gr', 'nx-variation-product' );
                }

                $output .= '<div class="nx_variation__button" role="button" data-value="' . esc_attr($variation_slug) . '">';
                $output .= '<span class="nx_variation__button__top"><span class="nx_variation__button__topname">'. esc_attr($variation_name).'</span>';
                //$output .= '<span class="nx_variation__button__topdescritption">'. esc_attr($variation_description) .'</span></span>';
                $output .= '<span class="nx_variation__button__topdescritption">'. $show_price_gr .'</span></span>';
                $output .= '<span class="nx_variation__button__price">'. $variation_price_html .'</span>';
                $output .= '</div>';
            }

            $output .= '</ul>';
            return $output;
        }
    }
    return $output;
}


// Show lowest price / gram in loop for variable products
add_filter( 'woocommerce_variable_price_html', 'nx_variable_prices_range', 100, 2 );
function nx_variable_prices_range( $price, $product ){
    global $woocommerce_loop;
    // Not on single products
    if ( ( is_product() && isset($woocommerce_loop['name']) && ! empty($woocommerce_loop['name']) ) || ! is_product() )
    {
        $variations = $product->get_available_variations();
        if ( ! empty( $variations ) ) {
            $array_price_gr = [];
            foreach ( $variations as $variation ) {
                $variation_id = $variation['variation_id'];
                $product_variation = new WC_Product_Variation( $variation_id );
                $variation_price = $product_variation->get_price();
                $variation_weight = $product_variation->get_weight(); // Get weight from variation
                if ( $variation_weight ) {

                    $variation_weight = intval( $variation_weight );
                    $variation_price = intval($variation_price);
                    $calc_price_gr = $variation_price / $variation_weight;

                    $price_gr =  number_format((float)$calc_price_gr, 2, ',', '');
                    array_push($array_price_gr, $price_gr);
                }

                if (empty($array_price_gr)) {
                    $active_price_min = $product->get_variation_price( 'min', true );
                    $show_price_gr = __( 'Da €', 'nx-variation-product' ) . $active_price_min;
                } else {
                    $array_price_gr = array_map(function($value) {
                        return floatval(str_replace(",", ".", $value));
                    }, $array_price_gr);

                    $smallest = min($array_price_gr);

                    $smallest_formatted = number_format($smallest, 2, ",", ".");
                    $show_price_gr =  __( 'Da ', 'nx-variation-product' ) . $smallest_formatted . __( '€/gr', 'nx-variation-product' );
                }

            }
        }
        $price = $show_price_gr;
    }
    return $price;
}

// hide buttons to reset variations choice
add_filter('woocommerce_reset_variations_link', '__return_empty_string');