<?php
//is ok

namespace HWP_Ask\includes;




class AskEnqueues
{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }



    public function __construct()
    {
        add_action('wp_enqueue_scripts' , [ $this , 'frontScripts' ], 99);
        add_action('wp_enqueue_scripts' , [ $this , 'dashboard' ], 99);

    }

    public function frontScripts(){

            $is_dashboard_page = in_array('dashboard' , explode('/' , $_SERVER['REQUEST_URI']) );
        if (  is_page('ask' ) && !$is_dashboard_page  ){

            wp_enqueue_script(
                'hamfy_selectize' ,
                HWP_ASK_ASSETS.'selectize.min.js' ,
                [ 'jquery' ] ,
                '5.7.0' ,
                false
            );

            wp_enqueue_script(
                'hamfy_prism_js' ,
                HWP_ASK_ASSETS.'prism.js' ,
                [ 'jquery' ] ,
                '5.7.0' ,
                false
            );

            wp_enqueue_script(
                'hamfy_ckeditor' ,
                HWP_ASK_ASSETS.'ckeditor.js' ,
                [],
                time(),
                false
            );

            wp_enqueue_script(
                'izi_toast_js' ,
                HWP_ASK_ASSETS.'iziToast.min.js' ,
                [],
                '1.4.0',
                false
            );

            wp_enqueue_script(
                'hamfy_ask_js' ,
                HWP_ASK_ASSETS.'hamfy-ask.js' ,
                [ 'jquery'  ,'hamfy_ckeditor' ,'hamfy_selectize' ,'hamfy_prism_js','izi_toast_js' ] ,
                HWP_ASK_VERSION,
                false
            );

            wp_localize_script(
                'hamfy_ask_js' ,
                'hamfy_ask_objects' ,
                [
                    'home_url'        => home_url(),
                    'admin_url'       => admin_url( 'admin-ajax.php' ),
                    'nonce'           => wp_create_nonce('ask_dashboard_nonce') ,
                    'captcha'         => (function_exists('hamyar_feature_recaptcha_site_key')) ? hamyar_feature_recaptcha_site_key(false):'' ,
                    'params'          => AskFunctions::getQueryString() ,
                    'ask_btn_loader'  => file_get_contents( HWP_PAGE_TEMPLATE.'partial/ask-btn-loader.html' ),
                    'root'            => get_rest_url(null, 'hamfy/v1.1/ask/') ,
                    'user_token'      => AskFunctions::getEncryptCode(),
                    'user_status'     => AskPermission::canCreate() ? 'true' : 'false'
                ]
            );

            wp_enqueue_style(
                'hamfy_selectize_css' ,
                HWP_ASK_ASSETS.'selectize.legacy.css'
            );

            wp_enqueue_style(
                'hamfy_prism_css' ,
                HWP_ASK_ASSETS.'prism.css'
            );

            wp_enqueue_style(
                'izi_toast_css' ,
                HWP_ASK_ASSETS.'iziToast.min.css'
            );


            wp_enqueue_style(
                'hamfy_ask_css' ,
                HWP_ASK_ASSETS.'hamfy-ask.css' ,
                ['hamfy_selectize_css' ,'izi_toast_css' ],
                HWP_ASK_VERSION

            );
        }
    }


    public static function dashboard()
    {
        $is_dashboard_page = in_array('dashboard' , explode('/' , $_SERVER['REQUEST_URI']) );
        if ( is_page('ask' ) && current_user_can('administrator') && $is_dashboard_page ){
            wp_enqueue_style(
                'hamfy_selectize_css' ,
                HWP_ASK_ASSETS.'selectize.legacy.css'
            );
            wp_enqueue_style(
                'hamfy_ask_dashboard_css' ,
                HWP_ASK_ASSETS.'hamfy-ask-dashboard.css' ,
                null,
                HWP_ASK_VERSION
            );
            wp_enqueue_script(
                'hamfy_selectize' ,
                HWP_ASK_ASSETS.'selectize.min.js' ,
                null ,
                '5.7.0' ,
                false
            );
            wp_enqueue_script(
                'hamfy_ckeditor' ,
                HWP_ASK_ASSETS.'ckeditor.js' ,
                null,
                '5.1',
                false
            );

            wp_enqueue_script(
                'hamfy_izi_toast_js' ,
                HWP_ASK_ASSETS.'iziToast.min.js' ,
                null,
                '1.4',
                false
            );


            wp_enqueue_script(
                'hamfy_ask_dashboard_js' ,
                HWP_ASK_ASSETS.'ask-dashboard.js' ,
                [ 'jquery' ,'hamfy_ckeditor' ,'hamfy_selectize' ,'hamfy_izi_toast_js' ] ,
                HWP_ASK_VERSION ,
                false
            );

            wp_localize_script(
                'hamfy_ask_dashboard_js' ,
                'hamfy_ask_objects' ,
                [
                    'home_url'     => home_url(),
                    'admin_url'    => admin_url( 'admin-ajax.php' ),
                    'root'         => get_rest_url(null, 'hamfy/v1.1/ask/')  ,
                    'nonce'        => wp_create_nonce('ask_dashboard_nonce') ,
                    'user_token'   => AskFunctions::getEncryptCode()
                ]
            );
        }
    }
}