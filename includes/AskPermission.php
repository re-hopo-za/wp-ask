<?php
//ok

namespace HWP_Ask\includes;


class AskPermission
{


    protected static $_instance = null;
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct()
    {
    }

    //ok
    public static function canCreate()
    {
        $userID = get_current_user_id();
        if ( isset( $userID ) && is_numeric( $userID ) && $userID > 0 ){
            return true;
        }else{
            return false;
        }
    }


    // ok
    public function checkPermission( $gToken , $gAction ,$userID  ){
        self::checkRecaptcha( $gToken  ,$gAction );
        self::userValidator( $userID );
        return true;
    }


    // ok
    public static function userValidator( $userID )
    {
        if ( empty( $userID ) || $userID == 0 ){
            wp_send_json( ['Result' => 'User Are Not Access'] , 403 );
        }
        $user = get_user_by('id', $userID );
        if ( !isset( $user->ID ) ){
            wp_send_json( ['Result' => 'User Not Found'] , 403 );
        }
        return true;
    }


    // ok
    public static function checkRecaptcha( $token , $action ){
        if (!function_exists('hamyar_feature_is_recaptcha_enabled') || !hamyar_feature_is_recaptcha_enabled()) return true;
        $message = hamyar_feature_recaptcha_validate( $token );
        if ( $message !== true ){
            wp_send_json( $message );
        }
        return true;
    }

}