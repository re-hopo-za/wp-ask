<?php
//ok
/*
 * Template Name: Ask Template
 * Description: A Page Template For hamfy Plugin.
 */


use HWP_Ask\includes\AskFunctions;
use HWP_Ask\includes\AskUI;



wp_head();
get_header();


$params           = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$first_parameter  = $params[0];
$second_parameter = $params[1] ?? '';
$count            = count( $params );
$currentUser      = get_current_user_id();


//echo do_shortcode( '[ask_shortcode search="" tags="" title="this_page" count="4" details="0"]' );

    if ( $count <= 3 ) {
        if ( $count === 1 || ( $count === 2 && $first_parameter == 'ask' &&
                $second_parameter !== 'new' && $second_parameter !== 'dashboard' && !is_numeric( $second_parameter ) ) ) {
            AskUI::all( AskFunctions::getQueryString() ,$currentUser );

        } elseif ( $count === 2 && $first_parameter == 'ask' && $second_parameter == 'new' ) {
            AskUI::new( $currentUser );

        } elseif ( $count >= 2 && $first_parameter == 'ask' && is_numeric( $second_parameter )  ){
            AskUI::single( (int) $second_parameter ,$currentUser );

        }elseif ( $count >= 2 && $first_parameter == 'ask' && $second_parameter == 'dashboard' && current_user_can('administrator')  ){
            AskUI::dashboard();

        } else{
            AskFunctions::_404();
        }

    } else {
        AskFunctions::_404();
    }



get_footer();
wp_footer();
