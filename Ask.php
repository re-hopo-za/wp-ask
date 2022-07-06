<?php
/**
 * Plugin Name:       Hamyar Ask
 * Version:           1.0.0
 * Author:            Hossein pour
 */


namespace HWP_Ask;



use HWP_Ask\includes\AskCustomRoute;
use HWP_Ask\includes\AskDB;
use HWP_Ask\includes\AskProcess;
use HWP_Ask\includes\AskRest;

use HWP_Ask\includes\AskEnqueues;




class Ask
{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct(){
        add_action('init' , [$this , 'run'] );
        self::define();
    }


    public static function define()
    {
        if ( !defined( 'HWP_ASK_ROOT' ) ){
            define('HWP_ASK_ROOT'            , plugin_dir_path(__FILE__));
            define('HWP_ASK_ASSETS'          , plugin_dir_url(__FILE__) . 'assets/');
            define('HWP_ASK_VERSION'         , '1.0.2');
            define('HWP_ASK_INCLUDES'        , plugin_dir_path(__FILE__) . '/includes/');
            define('HWP_PAGE_TEMPLATE'       , HWP_ASK_ROOT.'page-template/');
            define('HWP_ASK_DEVELOPER_MODE'  , true );
            define('HWP_ASK_SCRIPTS_VERSION' ,
                HWP_ASK_DEVELOPER_MODE ? time()  : HWP_ASK_VERSION
            );
        }
        require_once HWP_ASK_ROOT . 'vendor/autoload.php';
    }


    public static function install()
    {
        AskDB::get_instance()::createNeedDatabase();
    }

    public static function run()
    {
        AskCustomRoute::get_instance();
        AskEnqueues::get_instance();
        AskRest::get_instance();
        AskProcess::get_instance();
    }
}

new Ask();
register_activation_hook( __FILE__ , [ Ask::get_instance() , 'install'] );

// افزودن لایک یک سوال به لایک همان سوال  یکی افزوده خواهد شد و یکی به لایک کاربر آن سوال و همچنین افزودن دیسلایک هم