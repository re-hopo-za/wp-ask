<?php

namespace HWP_Ask\includes;





class AskDB
{

    // public static  $charset; //not need
    public static  $asks;
    // public static  $prefix; //not need



    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public  function __construct() {
        global $wpdb;
        self::$asks      = $wpdb->prefix.'tango_asks';
    }


    public static function createNeedDatabase(){ 
        $create_query   = [];
        $create_query[] = self::createTableAsks(); 
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        
        foreach ( $create_query as $query ) {
            $table_checker = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( self::$asks ) );
            if ( ! $wpdb->get_var( $table_checker ) ==  self::$asks ) {
                dbDelta( $query );
            }
        }
    }



    protected static function createTableAsks()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table           = self::$asks;
        return "
        CREATE TABLE {$table}  (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
          `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
          `creator` bigint(20) UNSIGNED NOT NULL,
          `tags` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL,
          `parent_ask` bigint(20) UNSIGNED NULL DEFAULT NULL,
          `likes` smallint(6) NULL DEFAULT 0,
          `dislikes` smallint(6) NULL DEFAULT 0,
          `status` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
          `created_date` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
          `updated_date` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
          `approved` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT 'pending',
          `views` mediumint(9) NOT NULL DEFAULT 0,
          `reply_count` tinyint(4) NULL DEFAULT 0,
          `like_list` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL,
          `dislike_list` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL,
          `log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL,
          `comment` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
          PRIMARY KEY (`id`) USING BTREE
        ) {$charset_collate}";
    }

//ok
    public static function pagination( $params ,$count ){
        $html  = '';
        $limit = $params['limit'];
        $page  = $params['page'];
        $list_count = absint(  $count / $limit );
        $before = $page - ($limit * 1);
        $before = $before <= 0  ? 0 : $before;
        $after  = $page + ($limit * 3);
        $loop   = 1;

        if ( $count > $page ){
            for ( $i = $before; $i <= $count; $i += $limit ){
                if (  $i >= $before && $i <= $after ){
                    $page_count = ($i/$limit) + 1;
                    if ( $i == $page ){
                        $html .= '<a href="'.get_site_url().'/ask/page/'.$page_count.'" data-page="'.$page.'" class="active" onclick="return false;" >'.$page_count.'</a>';
                    }else{
                        if ( $i ==  $before && $before > ( $limit * 2 ) ){
                            $html .=' 
                                <a href="'.get_site_url().'/ask/page/'.$page_count.'" class="'.$limit.'" data-page="'.( $limit ).'" onclick="return false;">قبلی</a> 
                                <a href="'.get_site_url().'/ask/page/1" class="0" data-page="0" onclick="return false;">1</a>
                                <span>...</span>';
                        }else if ( $i <= ($after-$limit) ){
                            $html .= ' <a href="'.get_site_url().'/ask/page/'.$page_count.'" data-page="'.$i.'"  class="'.$i.'" onclick="return false;">'.$page_count.'</a> ';
                        }else{
                            $html .='
                                <span>...</span>
                                <a href="'.get_site_url().'/ask/page/'.$page_count.'" data-page="'.($list_count*$limit).'" onclick="return false;">'.$list_count.'</a>
                                <a href="'.get_site_url().'/ask/page/'.$page_count.'" data-page="'.(  $limit ).'" onclick="return false;">بعدی</a>';
                        }
                    }
                }
                $loop++;
            }
        }
        return $html;
    }



    public static function setQuery( $params ,$pagination  )
    {
        global $wpdb;
        $table = self::$asks;
        $where = '';
        $count = ' COUNT(id) AS count ';
        if ( isset( $params['tag'] ) && !empty( $params['tag'] )) {
            $tags   = $params['tag'];
            $where .= " AND tags LIKE '%$tags%' ";
        }

        if ( isset( $params['search'] ) && !empty( $params['search'] )) {
            $keyword = str_replace("'" ,'' ,$params['search']  );
            $where  .= " AND ( title LIKE '%$keyword%' OR content LIKE '%$keyword%' OR tags LIKE '%$keyword%' ) ";
        }

        if ( isset(  $params['creator'] ) && !empty( $params['creator'] )) {
            $creator = $params['creator'];
            $where  .= " AND creator='$creator' ";
        }

        if ( isset( $params['response'] ) && !empty( $params['response'] ) && $params['response'] != 'all' ){
            if ( $params['response'] == 'has' ){
                $where  .= " AND reply_count <> 0 ";
            }elseif ( $params['response'] == 'no' ){
                $where  .= " AND reply_count = 0 ";
            }
        }

        if ( isset(  $params['order_by'] ) && !empty( $params['order_by'] )) {
            $order_by = $params['order_by'];
            $where   .= "ORDER BY $order_by ";
        }else{
            $where   .= "ORDER BY id ASC ";
        }

        if ( $pagination ){
            $limit = $params['limit'] <= 60 ?  $params['limit'] : 60;
            $where .= "LIMIT {$limit} OFFSET {$params['page']}";
            $count  = '*';
        }

        $query  = "SELECT {$count} FROM {$table} WHERE parent_ask IS NULL AND approved = 'accept' {$where};";
        return $wpdb->get_results( $query );
    }


    public static function getAll( $params )
    {
        $parents    = self::setQuery( $params , true );
        $rep_count  = self::setQuery( $params , false );
        $users_data = self::getBulkUsers( array_column( $parents ,'creator' ) );
        $parent_date = [];
        $parent_date['main']['pagination'] = self::pagination( $params ,$rep_count[0]->count  );

        if ( !empty( $parents ) && !is_wp_error( $parents ) ){
            date_default_timezone_set('Asia/Tehran');
            foreach ( $parents as $parent ){
                $parent_date['loop'][] = [
                    'id'             => $parent->id  ,
                    'title'          => $parent->title ,
                    'content'        => AskFunctions::excerpt( $parent->content , 400 ) ,
                    'tags'           => AskFunctions::getTags( $parent->tags ) ,
                    'views'          => $parent->views  ,
                    'reply_count'    => $parent->reply_count  ,
                    'creator_id'     => (int) $parent->creator ,
                    'creator_link'   => home_url().'/ask?creator='. $parent->creator  ,
                    'profile_image'  => AskFunctions::get_avatar( $parent->creator ,50 ,
                                        AskFunctions::getUserData( $users_data ,$parent->creator ,'profile_pic') ,true ) ,
                    'created_time'   => human_time_diff( time() , strtotime( $parent->created_date ) ).'   قبل '   ,
                    'link'           => home_url().'/ask/'.$parent->id ,
                    'creator'        => AskFunctions::getUserData( $users_data ,$parent->creator ,'first_name').' '.
                                        AskFunctions::getUserData( $users_data ,$parent->creator ,'last_name')
                ];
            }
        }
        return $parent_date;
    }



    public static function getBulkUsers( $usersIDs )
    {
        global $wpdb;
        $final_users = ['ask_user_reputation' => [] ,'first_name' => '' ,'last_name' => '' ,'force_verified_mobile' => '' ,'profile_pic' => [] ];
        if ( !empty( $usersIDs ) && is_array( $usersIDs ) ){
            $implode = implode( "','" ,$usersIDs );
            $query   = "SELECT * FROM {$wpdb->usermeta} WHERE user_id IN ('$implode') AND meta_key 
                        IN ('ask_user_reputation' ,'first_name' ,'last_name','force_verified_mobile','profile_pic');";
            $users   = $wpdb->get_results( $query );
            foreach ( $users as $user ){
                    $final_users[ $user->user_id ][$user->meta_key] = $user->meta_value;
            }
        }
        return $final_users;
    }


    public static function getSpecific( $askID ,$valid = true ,$not_reply = true )
    {
        global $wpdb;
        $ask_table  = self::$asks;
        $valid      = $valid ? ' AND approved="accept" ' : '';
        $not_reply  = $not_reply ? ' parent_ask IS NULL AND' : '';
        $single = $wpdb->get_row(
            "SELECT * FROM {$ask_table} WHERE {$not_reply} id={$askID} {$valid} GROUP BY id limit 1"
        );
        if ( !is_wp_error( $single ) && !empty( $single ) ){
            return $single;
        }else{
            return [];
        }
    }


    public static function getRepliesList( $askID )
    {
        global $wpdb;
        $ask_table  = self::$asks;
        $replies    = $wpdb->get_results(
            "SELECT * FROM {$ask_table} WHERE parent_ask = {$askID} AND approved='accept' GROUP BY id "
        );
        if ( !is_wp_error( $replies ) && !empty( $replies ) ){
            return $replies;
        }else{
            return [];
        }
    }


    public static function getSingle( $askID ,$userID )
    {
        $replies_items = [];
        $result        = [];
        $single        = self::getSpecific( $askID );
        if ( !empty( $single ) && !is_wp_error( $single ) ){
            $users_data = self::getBulkUsers( [$single->creator] );
            date_default_timezone_set('Asia/Tehran');
            $result['main'] =  [
                'id'             => $single->id  ,
                'title'          => $single->title ,
                'content'        => $single->content ,
                'tags'           => AskFunctions::getTags( $single->tags ,true ) ,
                'views'          => $single->views  ,
                'creator_id'     => $single->creator ,
                'created_time'   => human_time_diff( time() , strtotime( $single->created_date ) )  ,
                'creator_link'   => home_url().'/ask?creator='. $single->creator  ,
                'profile_image'  => AskFunctions::get_avatar( $single->creator ,50 ,
                                    AskFunctions::getUserData( $users_data ,$single->creator ,'profile_pic') ,true ) ,
                'creator'        => AskFunctions::getUserData( $users_data ,$single->creator ,'first_name').' '.
                                    AskFunctions::getUserData( $users_data ,$single->creator ,'last_name') ,
                'is_booked'      => AskFunctions::reputationData(
                                    AskFunctions::getUserData( $users_data ,$single->creator ,'ask_user_reputation' ) ,'bookmark_list' ,$single->id ,false )
            ];

            if ( !empty( $result['main'] ) && is_countable( $result['main'] )){
                $replies    = self::getRepliesList( $askID );
                $users_data = self::getBulkUsers( array_column( $replies ,'creator' ) );
                if ( !empty( $replies ) && !is_wp_error( $replies ) ){
                    foreach ( $replies as $reply ){
                        $replies_items[] =
                            [   'id'             => $reply->id  ,
                                'title'          => $reply->title ,
                                'content'        => $reply->content ,
                                'like_list'      => $reply->like_list ,
                                'dislike_list'   => $reply->dislike_list ,
                                'creator_id'     => $reply->creator  ,
                                'creator_link'   => home_url().'/ask?creator='. $reply->creator  ,
                                'created_time'   => human_time_diff( time() , strtotime( $reply->created_date ) )  ,
                                'like_svg_fill'  => AskFunctions::iconHandler( $reply ,$userID ,'like_icon')      ,
                                'like_svg_class' => AskFunctions::iconHandler( $reply ,$userID ,'like_class')     ,
                                'dislike_s_fill' => AskFunctions::iconHandler( $reply ,$userID ,'dislike_icon')   ,
                                'dislike_class'  => AskFunctions::iconHandler( $reply ,$userID ,'dislike_class')  ,
                                'rate'           => $reply->likes - $reply->dislikes ,
                                'ask_likes'      => $reply->likes ,
                                'ask_dislikes'   => $reply->dislikes ,
                                'profile_image'  => AskFunctions::get_avatar( $reply->creator ,50 ,
                                                    AskFunctions::getUserData( $users_data ,$reply->creator ,'profile_pic') ,true ) ,
                                'creator'        => AskFunctions::getUserData( $users_data ,$reply->creator ,'first_name').' '.
                                                    AskFunctions::getUserData( $users_data ,$reply->creator ,'last_name') ,
                                'user_likes'     => AskFunctions::reputationData(
                                                    AskFunctions::getUserData( $users_data ,$reply->creator ,'ask_user_reputation' ) ,'likes' ,null ,0 ),
                                'user_dislikes'  => AskFunctions::reputationData(
                                                    AskFunctions::getUserData( $users_data ,$reply->creator ,'ask_user_reputation' ) ,'dislikes' ,null ,0 )
                            ];
                    }
                    $result['replies'] = $replies_items;
                }else{
                    $result['replies'] = 404;
                }
            }
        }elseif( empty( $single )  ){
            return 404;
        }elseif ( is_wp_error( $single )){
            return 500;
        }

        return $result;
    }


//ok
    public static function replyCount( $parent )
    {
        global $wpdb;
        $ask_table      = self::$asks;
        $count = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(id) FROM {$ask_table} WHERE parent_ask= %d AND approved='accept';", $parent )
        );
        if (!is_wp_error( $count )){
            return $count;
        }else{
            return 0;
        }
    }


    //ok
    public static function newAsk( object $params ,$userID )
    {
        global $wpdb;
        $table = self::$asks;

        $wpdb->insert(  $table , [
            'title'        => $params->title       ,
            'content'      => $params->content     ,
            'creator'      => $userID              ,
            'tags'         => AskFunctions::tagsSeparator( $params->tags ) ,
            'status'       => 'active'             ,
            'approved'     => 'pending'
        ],
            ['%s','%s','%d','%s','%s','%s' ] );
        $id = $wpdb->insert_id;

        if ( !is_integer( $id ) ){
            wp_send_json([
                'An error occurred on saving ask'
            ] , 500 );
        }
        return $id;
    }

    //ok
    public static function newReply( object $params ,$userID )
    {
        global $wpdb;
        $table = self::$asks;
        $status = AskFunctions::isAdmin( $userID ) ? 'accept' : 'pending';
        $wpdb->insert(  $table , [
            'content'        => $params->content   , 
            'creator'        => $userID            ,
            'parent_ask'     => $params->parent_id ,
            'status'         => 'active'           ,
            'approved'       => $status
        ],
            ['%s','%d','%d','%s','%s' ] );
        $id = $wpdb->insert_id;
        if ( !is_integer( $id ) ){
            wp_send_json([
                'An error occurred on saving ask'
            ] , 500 );
        }
        return $params->parent_id;
    }


//ok
    public static function checkParentAsk( $parentID )
    {
        global $wpdb;
        $ask_table = self::$asks;
        $parent = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$ask_table} WHERE id= %d AND status = 'deactivate' AND approved='accept' ;", $parentID)
        );
        if ( empty( $parent ) && is_wp_error( $parent ) ) {
            wp_send_json('This parent id not exists or deactivate' , 403 );
        }
        return true;
    }


    public static function getNotApproved()
    {
        global $wpdb;
        $ask_table    = self::$asks;
        $parent_date  = [];
        $parents      = $wpdb->get_results("SELECT * FROM {$ask_table} WHERE parent_ask IS NULL AND approved='pending';" );
        $users_data   = self::getBulkUsers( array_column( $parents ,'creator' ) );
        if ( !empty( $parents ) && !is_wp_error( $parents ) ){
            foreach ( $parents as $parent ){
                if( !empty( $parent->tags ) ){
                    $tags_option = explode(',' , $parent->tags );
                }else{
                    $tags_option = [];
                }
                $parent_date[] = [
                    'id'            => $parent->id  ,
                    'title'         => $parent->title ,
                    'content'       => $parent->content  ,
                    'tags'          => $tags_option  ,
                    'views'         => $parent->views  ,
                    'reply_count'   => $parent->reply_count  ,
                    'creator_id'    => $parent->creator ,
                    'created_date'  => $parent->created_date ,
                    'time_ago'      => human_time_diff( time() , strtotime( $parent->created_date ) ).'   قبل '   ,
                    'ask_like'      => $parent->likes ,
                    'ask_dislike'   => $parent->dislikes,
                    'link'          => home_url().'/ask/'.$parent->id ,
                    'profile_image' => AskFunctions::get_avatar( $parent->creator ,50 ,
                                       AskFunctions::getUserData( $users_data ,$parent->creator ,'profile_pic') ,true ) ,
                    'creator_mob'   => AskFunctions::getUserData( $users_data ,$parent->creator ,'force_verified_mobile') ,
                    'creator'       => AskFunctions::getUserData( $users_data ,$parent->creator ,'first_name').' '.
                                       AskFunctions::getUserData( $users_data ,$parent->creator ,'last_name') ,
                    'user_likes'    => AskFunctions::reputationData(
                                       AskFunctions::getUserData( $users_data ,$parent->creator ,'ask_user_reputation' ) ,'likes' ,null ,0 ),
                    'user_dislikes' => AskFunctions::reputationData(
                                       AskFunctions::getUserData( $users_data ,$parent->creator ,'ask_user_reputation' ) ,'dislikes' ,null ,0 )
                ];
            }
            return $parent_date;
        }else{
            return 404;
        }
    }


    public static function getNewReplies()
    {
        global $wpdb;
        $ask_table    = self::$asks;
        $parent_date  = [];
        $children     = $wpdb->get_results("SELECT * FROM {$ask_table} WHERE parent_ask IS NOT NULL AND approved='pending' LIMIT 50;" );
        $parent_list  = self::parentList( array_column( $children , 'parent_ask') );
        $users_data   = self::getBulkUsers( array_column( $children ,'creator' ) );
        if ( !empty( $children ) && !is_wp_error( $children ) ){
            foreach ( $children as $child ){
                $parent_date[] = [
                    'id'            => $child->id  ,
                    'content'       => $child->content ,
                    'views'         => $child->views  ,
                    'creator_id'    => $child->creator ,
                    'time_ago'      => human_time_diff( time() , strtotime( $child->created_date ) ).'   قبل '   ,
                    'like'          => $child->likes ,
                    'dislike'       => $child->dislikes ,
                    'link'          => home_url().'/ask/'.$child->id ,
                    'parent_ask'    => $parent_list[$child->parent_ask] ?? [],
                    'profile_image' => AskFunctions::get_avatar( $child->creator ,50 ,
                                       AskFunctions::getUserData( $users_data ,$child->creator ,'profile_pic') ,true ) ,
                    'creator_mob'   => AskFunctions::getUserData( $users_data ,$child->creator ,'force_verified_mobile') ,
                    'creator'       => AskFunctions::getUserData( $users_data ,$child->creator ,'first_name').' '.
                                       AskFunctions::getUserData( $users_data ,$child->creator ,'last_name') ,
                    'user_likes'    => AskFunctions::reputationData(
                                       AskFunctions::getUserData( $users_data ,$child->creator ,'ask_user_reputation' ) ,'likes' ,null ,0 ),
                    'user_dislikes' => AskFunctions::reputationData(
                                       AskFunctions::getUserData( $users_data ,$child->creator ,'ask_user_reputation' ) ,'dislikes' ,null ,0 )
                ];
            }
            return $parent_date;
        }else{
            return 404;
        }
    }


    public static function parentList( $parents_ids )
    {
        $parent_list  = [];
        if ( !empty( $parents_ids ) ){
            global $wpdb;
            $ask_table   = self::$asks;
            $parents_ids = implode("','" , $parents_ids );
            $parents_ask = $wpdb->get_results("SELECT * FROM {$ask_table} WHERE id IN('$parents_ids')" );
            $users_data  = self::getBulkUsers( array_column( $parents_ask ,'creator' ) );
            if ( !empty( $parents_ask ) ){
                foreach ( $parents_ask as $parent ){
                    $parent_list[ $parent->id ] = [
                        'id'            => $parent->id  ,
                        'title'         => $parent->title ,
                        'content'       => $parent->content ,
                        'creator'       => AskFunctions::getUserData( $users_data ,$parent->creator ,'first_name').' '.
                                           AskFunctions::getUserData( $users_data ,$parent->creator ,'last_name') ,
                        'creator_id'    => $parent->creator
                    ];
                }
            }
        }
        return $parent_list;
    }


    public static function getAllOld()
    {
        global $wpdb;
        $ask_table    = self::$asks;
        $parent_date  = [];
        $parents      = $wpdb->get_results("SELECT * FROM {$ask_table} WHERE parent_ask IS NULL LIMIT 30" );
        $users_data   = self::getBulkUsers( array_column( $parents ,'creator' ) );
        if ( !empty( $parents ) && !is_wp_error( $parents ) ){
            foreach ( $parents as $parent ){
                $parent_date[] = [
                    'id'            => $parent->id  ,
                    'title'         => $parent->title ,
                    'content'       => strip_tags( $parent->content ) ,
                    'reply_count'   => $parent->reply_count  ,
                    'creator_id'    => $parent->creator ,
                    'time_ago'      => human_time_diff( time() , strtotime( $parent->created_date ) ).'   قبل '   ,
                    'ask_like'      => $parent->likes  ,
                    'ask_dislike'   => $parent->dislikes ,
                    'link'          => home_url().'/ask/'.$parent->id ,
                    'approved'      => $parent->approved ,
                    'log'           => $parent->log ,
                    'profile_image' => AskFunctions::get_avatar( $parent->creator ,50 ,
                                       AskFunctions::getUserData( $users_data ,$parent->creator ,'profile_pic') ,true ) ,
                    'creator_mob'   => AskFunctions::getUserData( $users_data ,$parent->creator ,'force_verified_mobile') ,
                    'creator'       => AskFunctions::getUserData( $users_data ,$parent->creator ,'first_name').' '.
                                       AskFunctions::getUserData( $users_data ,$parent->creator ,'last_name')
                ];
            }

            return $parent_date;
        }else{
            return 404;
        }
    }


    public static function getAllReplies()
    {
        global $wpdb;
        $ask_table    = self::$asks;
        $parent_date  = [];
        $parents      = $wpdb->get_results("SELECT * FROM {$ask_table} WHERE parent_ask IS NOT NULL LIMIT 30" );
        $users_data   = self::getBulkUsers( array_column( $parents ,'creator' ) );
        if ( !empty( $parents ) && !is_wp_error( $parents ) ){
            foreach ( $parents as $parent ){
                $parent_date[] = [
                    'id'            => $parent->id  ,
                    'title'         => $parent->title ,
                    'content'       => $parent->content  ,
                    'reply_count'   => $parent->reply_count  ,
                    'creator_id'    => $parent->creator  ,
                    'profile_image' => AskFunctions::get_avatar( $parent->creator ,50 ,
                                       AskFunctions::getUserData( $users_data ,$parent->creator ,'profile_pic') ,true ) ,
                    'creator_mob'   => AskFunctions::getUserData( $users_data ,$parent->creator ,'force_verified_mobile') ,
                    'time_ago'      => human_time_diff( time() , strtotime( $parent->created_date ) ).'   قبل '   ,
                    'ask_likes'     => $parent->likes ,
                    'ask_dislikes'  => $parent->dislikes,
                    'link'          => home_url().'/ask/'.$parent->id ,
                    'approved'      => $parent->approved ,
                    'log'           => $parent->log ,
                    'creator'       => AskFunctions::getUserData( $users_data ,$parent->creator ,'first_name' ).' '.
                                       AskFunctions::getUserData( $users_data ,$parent->creator ,'last_name' )
                ];
            }

            return $parent_date;
        }else{
            return 404;
        }
    }


    public static function userAsksList( $userID )
    {
        global $wpdb;
        $ask_table    = self::$asks;
        $parent_date  = [];
        $parents      = $wpdb->get_results("SELECT * FROM {$ask_table} WHERE parent_ask IS NULL AND creator={$userID} ORDER BY approved" );

        if ( !empty( $parents ) && !is_wp_error( $parents ) ){
            foreach ( $parents as $parent ){
                $parent_date[] = [
                    'id'            => $parent->id  ,
                    'title'         => $parent->title ,
                    'reply_count'   => $parent->reply_count  ,
                    'time_ago'      => human_time_diff( time() , strtotime( $parent->created_date ) ).'   قبل '   ,
                    'ask_likes'     => $parent->likes ,
                    'ask_dislikes'  => $parent->dislikes ,
                    'link'          => home_url().'/ask/'.$parent->id ,
                    'approved'      => $parent->approved ,
                    'views'         => $parent->views ,
                    'comment'       => $parent->comment ,
                    'log'           => $parent->log
                ];
            }
            return $parent_date;
        }else{
            return 404;
        }
    }


//ok
    public static function userRepliesList( $userID )
    {
        global $wpdb;
        $ask_table    = self::$asks;
        $parent_date  = [];
        $parents      = $wpdb->get_results("SELECT * FROM {$ask_table} WHERE parent_ask IS NOT NULL AND creator={$userID} " );

        if ( !empty( $parents ) && !is_wp_error( $parents ) ){
            foreach ( $parents as $parent ){
                $parent_date[] = [
                    'id'            => $parent->id  ,
                    'parent_ask'    => $parent->parent_ask ,
                    'content'       => $parent->content ,
                    'time_ago'      => human_time_diff( time() , strtotime( $parent->created_date ) ).'   قبل '   ,
                    'like'          => $parent->likes ,
                    'dislike'       => $parent->dislikes,
                    'link'          => home_url().'/ask/'.$parent->id ,
                    'approved'      => $parent->approved ,
                    'log'           => $parent->log ,
                    'comment'       => $parent->comment ,
                ];
            }
            return $parent_date;
        }else{
            return 404;
        }
    }


    public static function getAsksWithIDs( $IDs )
    {
        $list = [];
        if ( is_array( $IDs ) ){
            array_walk( $IDs, function( &$value ){
                $value=(int)$value;
            });
            $IDs = array_filter($IDs);
            $IDs = array_unique( $IDs );

            global $wpdb;
            $ask_table  = self::$asks;
            $IDs  = implode("','"  , $IDs );
            $asks = $wpdb->get_results( "SELECT * FROM {$ask_table} WHERE id IN ('$IDs') ;" );
            if( !empty( $asks ) ){
                foreach ( $asks as $item ) {
                    $list[] = [
                        'id' => $item->id,
                        'title' => $item->title,
                        'like' => $item->likes,
                        'dislike' => $item->dislikes,
                        'time_ago' => human_time_diff(time(), strtotime($item->created_date)) . '   قبل ',
                        'link' => home_url() . '/ask/' . $item->id,
                        'log' => $item->log,
                        'views' => $item->views,
                        'reply_count' => $item->reply_count
                    ];
                }
            }
        }
        return $list;
    }


//ok
    public static function getUserDetails( $userID )
    {
        global $wpdb;
        $ask_table = self::$asks;
        $details   = [];
        $result    = $wpdb->get_results( "SELECT SUM(likes) as likes , SUM(dislikes) as dislikes , SUM(views) as views FROM {$ask_table} WHERE creator ={$userID} ;" );
        $question_count = $wpdb->get_var( "SELECT COUNT(id) as question_count FROM {$ask_table} WHERE creator ={$userID} AND parent_ask IS NULL AND approved='accept';" );
        $replies_count  = $wpdb->get_var( "SELECT COUNT(id) as replies_count FROM {$ask_table} WHERE creator ={$userID} AND parent_ask IS NOT NULL AND approved='accept';" );
        foreach ( $result as $detail ){
            $details['user_likes']    = (int) $detail->likes;
            $details['user_dislikes'] = (int) $detail->dislikes;
            $details['views']         = (int) $detail->views;
        }
        $details['picture']        = AskFunctions::get_avatar( $userID , 120 ,get_user_meta( $userID, 'profile_pic', true )  , true );
        $details['question_count'] = (int) $question_count ;
        $details['replies_count']  = (int) $replies_count ;

        $user = get_user_by('id' ,$userID );
        if ( !empty( $user ) ){
            $details['name'] = $user->first_name .' '. $user ->last_name;
        }
        return $details;
    }

//ok
    public static function getUserslist()
    {
        global $wpdb;
        $ask_table = self::$asks;
        $users     = [];
        $result    = $wpdb->get_results(
            "SELECT * FROM {$ask_table} limit 200;"
        );
        $users_details = self::getUsersDetails( array_column( $result , 'creator' ) );

        foreach ( $result as $user ){
            if( isset( $users[ $user->creator ]['ask'] ) && empty( $user->parent_ask ) ){
                $users[ $user->creator ]['ask'] = $users[ $user->creator ]['ask'] + 1;
            }else{
                $users[ $user->creator ]['ask'] = 1;
            }
            if( !empty( $user->parent_ask ) ){
                if ( isset( $users[ $user->creator ]['replies'] ) ){
                    $users[ $user->creator ]['replies'] = $users[ $user->creator ]['replies'] + 1;
                }else{
                    $users[ $user->creator ]['replies'] = 1;
                }
            }
            if( isset( $users[ $user->creator ]['views'] )  ){
                $users[ $user->creator ]['views'] = $users[ $user->creator ]['views'] + $user->views ;
            }else{
                $users[ $user->creator ]['views'] = $user->views ;
            }
            if( isset( $users[ $user->creator ]['likes'] )  ){
                $users[ $user->creator ]['likes'] = $users[ $user->creator ]['likes'] + $user->likes ;
            }else{
                $users[ $user->creator ]['likes'] = $user->likes ;
            }
            if( isset( $users[ $user->creator ]['dislikes'] )  ){
                $users[ $user->creator ]['dislikes'] = $users[ $user->creator ]['dislikes'] + $user->dislikes ;
            }else{
                $users[ $user->creator ]['dislikes'] = $user->dislikes ;
            }
            if( !isset( $users[ $user->creator ]['user_id'] ) && isset( $users_details[$user->creator] ) ){
                $users[ $user->creator ]['user_id'] = $users_details[$user->creator]['user_id'];
                $users[ $user->creator ]['name']    = $users_details[$user->creator]['name'];
                $users[ $user->creator ]['image']   = $users_details[$user->creator]['image'];
            }
        }
        return $users;
    }


//ok
    public static function getUsersDetails( $ids )
    {
        $users_details = get_users( ['include' => $ids , 'fields'   => [ 'ID', 'display_name'  ]  ] );
        $users  = [];
        if ( !is_wp_error( $users_details ) && !empty( $users_details ) ){
            foreach ( $users_details as $user ){
                $users[ $user->ID ] = [
                    'user_id' => $user->ID ,
                    'name'    => $user->display_name ,
                    'image'   => get_avatar($user->ID, 55 )
                ];
            }
        }
        return $users;
    }



    public static function updateQuestion( $params , $userID )
    {
        global $wpdb;
        $table   = self::$asks;
        $askID   = (int) $params->id;
        $title   = $params->title;
        $content = $params->content;
        $tags    = AskFunctions::tagsSeparator( $params->tags );

        if ( $askID && $title && $content ){
            if ( $params->accept == 'true' ){
                $logs   = AskFunctions::updateLogs( $askID , $userID ,'update_question_accept' );
                $data   = [ 'updated_date' => date('Y-m-d H:i:s') , 'approved' => 'accept'  ,'title' => $title , 'content'=> $content ,'tags'=> $tags ,'log'=> $logs  ];
                $format = [ '%s' ,'%s' ,'%s' ,'%s' ,'%s' ,'%s' ];
            }else{
                $logs   = AskFunctions::updateLogs( $askID , $userID ,'update_question' );
                $data   = [ 'updated_date' => date('Y-m-d H:i:s') ,'title' => $title , 'content'=> $content ,'tags'=> $tags ,'log'=> $logs   ];
                $format = [ '%s' ,'%s' ,'%s' ,'%s' ,'%s' ];
            }
            $where      = [ 'id' => (int) $askID ];
            $where_format = [ '%d' ];
            $wpdb->update( $table ,$data ,$where ,$format ,$where_format );

            if ( !is_wp_error( $wpdb ) ){
                return 200;
            }
            return 500;
        }
        return 403;
    }


//ok
    public static function updateParent( $parentID )
    {
        global $wpdb;
        $table      = self::$asks;
        $rep_count  = self::getSpecific( $parentID );
        if( !empty( $rep_count ) && is_array( $rep_count ) ) {
            $rep_count = count( $rep_count );
        }else {
            $rep_count = 0;
        }
        $data   = [ 'updated_date' => date('Y-m-d H:i:s') , 'reply_count' => ( $rep_count + 1 ) ];
        $where  = [ 'id' => $parentID ];
        $format = [ '%s' ];
        $where_format = [ '%d' ];
        $result = $wpdb->update( $table ,$data ,$where ,$format ,$where_format );

        if ( is_wp_error( $result ) ){
            wp_send_json('An error occurred on updating parent' , 500 );
        }
        return true;
    }


    //ok
    public static function updateReply( $params , $userID )
    {
        global $wpdb;
        $table   =self::$asks;
        $askID   = (int) $params->id;
        $content = $params->content;

        if ( $askID && $content ){
            if ( $params->accept == 'true'){
                $logs   = AskFunctions::updateLogs( $askID , $userID ,'update_reply_accept' );
                $data   = [ 'updated_date' => date('Y-m-d H:i:s') , 'approved' => 'accept' , 'content'=> $content ,'log'=> $logs  ];
                $format = [ '%s' ,'%s' ,'%s' ,'%s' ];
            }else{
                $logs   = AskFunctions::updateLogs( $askID , $userID ,'update_reply' );
                $data   = [ 'updated_date' => date('Y-m-d H:i:s') , 'content'=> $content ,'log'=> $logs   ];
                $format = [ '%s' ,'%s' ,'%s' ];
            }
            $where      = [ 'id' => (int) $askID ];
            $where_format = [ '%d' ];
            $wpdb->update( $table ,$data ,$where ,$format ,$where_format );

            if ( !is_wp_error( $wpdb ) ){
                return 200;
            }
            return 500;
        }
        return 403;
    }

    //ok
    public static function updateViews( $askID )
    {
        global $wpdb;
        $table  = self::$asks;
        $views  = self::getSpecific( $askID );
        $data   = [ 'updated_date' => date('Y-m-d H:i:s') , 'views' => ( $views->views + 1 ) ];
        $where  = [ 'id' => $askID ];
        $format = [ '%s' ];
        $where_format = [ '%d' ];
        $result = $wpdb->update( $table ,$data ,$where ,$format ,$where_format );

        if ( is_wp_error( $result ) ){
            wp_send_json('An error occurred on updating ask ' , 500 );
        }
        return true;
    }

    //ok
    public static function updateLike( $askID ,$userID  )
    {
        $ask  = self::getSpecific( (int) $askID ,true ,false );
       if ( !empty( $ask ) ){
            $like_list    = explode( ',' , $ask->like_list );
            if( $like_list[0] == "" ) unset( $like_list[0] );
            $dislike_list = explode( ',' , $ask->dislike_list );
            if( $dislike_list[0] == "" ) unset( $dislike_list[0] );

            if ( is_numeric( $userID ) && $ask->creator !== $userID ){
                if ( !in_array( $userID ,$dislike_list ) ){
                    if ( !in_array( $userID ,$like_list )  ){
                        $like      =  (int) $ask->likes + 1;
                        $status    = 'increment';
                        array_push( $like_list ,$userID );
                        $like_list = implode( ',', $like_list );

                    }else{
                        $like   =  (int) $ask->likes - 1;
                        $status = 'decrement';
                        $index  = array_search ( $userID , $like_list );
                        unset( $like_list[$index] );
                        if ( isset( $like_list[0] ) && $like_list[0] == '' ){
                            unset( $like_list[0] );
                        }
                        if ( !empty( $like_list ) ){
                            $like_list = implode( ',', $like_list );
                        }else{
                            $like_list = '';
                        }
                    }
                    global $wpdb;
                    $table  = self::$asks;
                    $data   = [ 'updated_date' => date('Y-m-d H:i:s') , 'likes' => $like , 'like_list' => $like_list  ];
                    $where  = [ 'id' => $ask->id ];
                    $format = [ '%s' ,'%d' ,'%s' ];
                    $where_format = [ '%d' ];
                    $result = $wpdb->update( $table ,$data ,$where ,$format ,$where_format );

                    if ( is_wp_error( $result ) ){
                        wp_send_json('An error occurred on updating like action' , 500 );
                    }
                    AskFunctions::reputationAction( (int) $ask->creator , 'likes' , $status );
                    return [
                        'result' => $like - (int)$ask->dislikes ,
                        'status' => $status
                    ];

                }else{
                    wp_send_json('You can not like this ask !!!!!' , 403 );
                }

            }else{
                wp_send_json('You can not like this ask ' , 403 );
            }
        }else{
            wp_send_json('This ask not found or Occurred an error ' , 404 );
        }
    }

//ok
    public static function updateDislike( $askID ,$userID  )
    {
        $ask          = self::getSpecific( (int) $askID ,true ,false );
        $dislike_list = explode( ',' , $ask->dislike_list );
        if( $dislike_list[0] == "" ) unset( $dislike_list[0] );
        $like_list    = explode( ',' , $ask->like_list );
        if( $like_list[0] == "" ) unset( $like_list[0] );

        if ( !empty( $ask ) && !is_wp_error( $ask ) ){
            if ( $ask->creator !== $userID ){
                if ( !in_array( $userID ,$like_list ) ){
                    if ( !in_array( $userID ,$dislike_list )  ){
                        $dislike      =  (int) $ask->dislikes + 1;
                        $status       = 'increment';
                        array_push( $dislike_list ,$userID );
                        $dislike_list = implode( ',', $dislike_list );
                    }else{
                        $dislike   =  (int) $ask->dislikes - 1;
                        $status    = 'decrement';
                        $index     = array_search ( $userID , $dislike_list );
                        unset( $dislike_list[$index] );
                        if ( isset( $dislike_list[0] ) && $dislike_list[0] == '' ){
                            unset( $dislike_list[0] );
                        }
                        if ( !empty( $dislike_list ) ){
                            $dislike_list = implode( ',', $dislike_list );
                        }else{
                            $dislike_list = '';
                        }

                    }
                    global $wpdb;
                    $table  = self::$asks;
                    $data   = [ 'updated_date' => date('Y-m-d H:i:s') , 'dislikes' => $dislike , 'dislike_list' => $dislike_list  ];
                    $where  = [ 'id' => $ask->id ];
                    $format = [ '%s' ,'%d' ,'%s' ];
                    $where_format = [ '%d' ];
                    $result = $wpdb->update( $table ,$data ,$where ,$format ,$where_format );
                    if ( is_wp_error( $result ) ){
                        wp_send_json('An error occurred on updating dislike action' , 500 );
                    }
                    AskFunctions::reputationAction( (int) $ask->creator ,'dislikes' ,$status );

                    return [
                        'result' => (int)$ask->likes - $dislike ,
                        'status' => $status
                    ];

                }else{
                    wp_send_json('You can not dislike this ask !!!!!' , 403 );
                }

            }else{
                wp_send_json('You can not dislike this ask ' , 403 );
            }
        }else{
            wp_send_json('This ask not found or Occurred an error ' , 404 );
        }
    }

//ok
    public static function acceptAsk( $askID , $userID )
    {
        $table = self::$asks;
        global $wpdb;
        if ( $askID ){
            $logs   = AskFunctions::updateLogs( $askID , $userID ,'accept_user' );

            $data   = [ 'updated_date' => date('Y-m-d H:i:s') ,'approved' => 'accept' ,'log' => $logs ];
            $where  = [ 'id' => (int) $askID ];
            $format = [ '%s' ,'%s' , '%s' ];
            $where_format = [ '%d' ];
            $wpdb->update( $table ,$data ,$where ,$format ,$where_format );

            self::updateParent( $askID );
            return 200;
        }
        return 500;
    }

//ok
    public static function rejectAsk( $askID , $userID ,$comment )
    {
        $table =self::$asks;
        global $wpdb;
        if ( isset( $askID , $comment ) ){
            $logs   = AskFunctions::updateLogs( $askID , $userID ,'reject_user' );

            $data   = [ 'updated_date' => date('Y-m-d H:i:s') ,'approved' => 'reject' ,'log' => $logs , 'comment' => $comment ];
            $where  = [ 'id' => (int) $askID ];
            $format = [ '%s' ,'%s' , '%s' , '%s'  ];
            $where_format = [ '%d' ];
            $wpdb->update( $table ,$data ,$where ,$format ,$where_format );
            return 200;
        }
        return 500;
    }









}
