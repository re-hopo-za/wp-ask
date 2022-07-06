<?php


namespace HWP_Ask\includes;




class AskProcess
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
        add_action('wp_ajax_ask_update_tags'   , [$this , 'updateTags'] );
        add_action('wp_ajax_ask_get_dashboard' , [$this , 'dashboard'] );
        add_action('wp_ajax_ask_get_all_ask_ui'     , [$this , 'getAllAsksUI'] );
        add_action('wp_ajax_ask_get_all_replies_ui' , [$this , 'getAllRepliesUI'] );
        add_action('wp_ajax_ask_get_all_users_list_ui' , [$this , 'getAllUsersUI'] );
        add_action('wp_ajax_ask_get_profire_ui' , [$this , 'getProfile'] );
        add_shortcode( 'ask_shortcode', [$this , 'shortCode'] );
    }


//ok
    public static function updateTags()
    {
        AskFunctions::nonceChecker( );
        if ( isset( $_POST['tags_item'] ) && is_countable( $_POST['tags_item'] )){
            $ask_options = get_option('hamfy_ask_options' );
            if( empty( $ask_options ) || !is_array( $ask_options ) ){
                $ask_options = [];
            }
            $ask_options['tags']  = $_POST['tags_item'];
            update_option( 'hamfy_ask_options' , $ask_options );
            wp_send_json( ['result' => 200 ] , 200 );
        }
        wp_send_json(['result' => 'Tags is Required ' ]  , 403 );
    }


    public static function addBookmark( $askID , $userID )
    {
        $user_meta = maybe_unserialize( get_user_meta( (int) $userID ,'ask_user_reputation' ,true ) );
        if ( !is_array( $user_meta ) ){
            $user_meta = AskFunctions::initialUserActions();
        }

        if ( isset( $user_meta['bookmark_list'] ) ) {
            if ( array_key_exists( $askID ,$user_meta['bookmark_list'] ) ){
                unset( $user_meta['bookmark_list'][$askID] );
                $status = 'removed';
            }else{
                $user_meta['bookmark_list'][$askID] = '';
                $status = 'added';
            }
        }else{
            $status = 'added';
            $user_meta['bookmark_list'][$askID] = '';
        }
        update_user_meta( $userID , 'ask_user_reputation' , $user_meta );
        return $status;
    }

//ok
    public static function checkBookmark( $askID , $userID )
    {
        $bookmark_list = get_user_meta( $userID , 'ask_user_reputation' , true  );
        if ( isset( $bookmark_list['bookmark_list']) ) {
            if ( array_key_exists( (int) $askID , $bookmark_list['bookmark_list'] ) ){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //ok
    public static function getBookmark( $userID )
    {
        $users_details = get_user_meta( (int) $userID , 'ask_user_reputation' , true );
        if ( isset( $users_details['bookmark_list'] ) && is_array( $users_details['bookmark_list'] ) ) {
            return AskDB::get_instance()::getAsksWithIDs( array_keys( $users_details['bookmark_list'] ) );
        }
        return [];
    }




    //ok
    public function dashboard()
    {
        AskFunctions::nonceChecker( $_POST['nonce'] );
        AskFunctions::askManagerChecker( get_current_user_id() );
        AskUI::dashboard();
    }

    //ok
    public function getAllAsksUI()
    {
        AskFunctions::nonceChecker( $_POST['nonce'] );
        AskFunctions::askManagerChecker( get_current_user_id() );
        AskUI::allAskDashboard();
    }

    //ok
    public function getAllRepliesUI()
    {
        AskFunctions::nonceChecker( $_POST['nonce'] );
        AskFunctions::askManagerChecker( get_current_user_id() );
        AskUI::allRepliesDashboard();
    }

    //ok
    public function getAllUsersUI()
    {
        AskFunctions::nonceChecker( $_POST['nonce'] );
        AskFunctions::askManagerChecker( get_current_user_id() );
        AskUI::dashboardUsersList();
    }




    public function shortCode( $attributes ) {
        global $wpdb;
        $ask_table = AskDB::get_instance()::$asks;

        $atts = shortcode_atts( [
            'title'   => '' ,
            'tags'    => '' ,
            'search'  => '' ,
            'count'   => 4  ,
            'details' => true
        ], $attributes );

        $title   = !empty( $atts['title'] ) ?  sanitize_text_field( $atts['title'] ) : '';
        $tags    = !empty( $atts['tags'] )  ? sanitize_text_field(  $atts['tags'] ) : '';
        $search  = !empty( $atts['search'] ) ? sanitize_text_field(  $atts['search'] ) : '';
        $count   = !empty( $atts['count'] ) ? (int) $atts['count'] : 4;
        $details = !empty( $atts['details'] ) ? $atts['details'] : true;


        $where     = " WHERE approved ='accept' ";

        if( !empty( $title ) && $title != 'this_page' ){
            $where .= " AND title LIKE '%$title%' ";
        }elseif( !empty( $title ) && $title == 'this_page' ){
            $title  = get_the_title();
            $where .= " AND title LIKE '%$title%' ";
        }

        if( !empty( $tags ) ){
            $tags = explode( ','  , $tags );
            if ( !empty( $tags ) ){
                $tags = implode("','" , $tags );
                $where .= " AND tags IN ('$tags')";
            }
        }

        if ( !empty( $search ) ){
            $search  = str_replace("'" ,'' , $search  );
            $where .= " AND ( title LIKE '%$search%' OR  content LIKE '%$search%' OR tags LIKE '%$search%' ) ";
        }
        $result = $wpdb->get_results(  "SELECT * FROM {$ask_table} {$where}  limit {$count} ;" );

        $ask_list = [];
        if ( !is_wp_error( $result ) && !empty( $result ) ){
            foreach ( $result as $item ){
                $ask_list[] = [
                    'id'             => $item->id  ,
                    'title'          => $item->title ,
                    'content'        => substr( strip_tags( $item->content) , 0, 20 ) ,
                    'tags'           => AskFunctions::getTags( $item->tags ,true ) ,
                    'views'          => $item->views  ,
                    'created_time'   => date_i18n('Y/m/d ' , strtotime( $item->created_date) ) ,
                    'likes'          => $item->likes
                ];
            }
        }

        ob_start();
        AskUI::shortCodeUI( $ask_list , $details );
        return ob_get_clean();

    }

//ok
    public static function getProfile()
    {
        echo AskUI::profile( get_current_user_id() );
    }



}