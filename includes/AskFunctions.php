<?php
//todo need to check with sabzevary

namespace HWP_Ask\includes;


use Hashids\Hashids;

class AskFunctions
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
    }

//ok
    public static function _404(){
        ?>
        <script>
            let body_404 = document.querySelector("body");
            body_404.classList.add("error404");
        </script>
        <?php
        include get_stylesheet_directory().'/404.php';
        exit();
    }


    public static function tagsSeparator( $tags )
    {
        if ( !empty( $tags ) && is_array($tags) ){
            return implode( ',' ,  $tags ) ;
        }
        return '';
    }

    //ok
    public static function get_avatar( $identification ,$size ,$link  ,$url = false )
    {
        if (is_numeric($identification))
            $user = get_user_by('id', (int)$identification);
        elseif (is_email($identification))
            $user = get_user_by('email', $identification);
        elseif ( $identification instanceof \WP_User) {
            $user = $identification;
        }

        if (!isset($user->ID)) {
            if ($url) return get_template_directory_uri() . '/assets/public/img/person.png?1';
            return sprintf('<img alt="" src="%1$s/assets/public/img/person.png"  height="%2$d" width="%2$d">', get_template_directory_uri(), $size);
        }

        if (!empty( $link )) {
            $img_url = wp_get_attachment_image_src( (int) $link, 'thumbnail', false);
            $img_url = (isset ($img_url[0])) ? $img_url[0] : '';
            if ( $url ) return $img_url;
            return sprintf('<img alt="" src="%1$s"  height="%2$d" width="%2$d">', $img_url, $size);

        } elseif (!isset($user->user_email) || !is_email($user->user_email)) {
            if ( $url ) return get_template_directory_uri() . '/assets/public/img/person.png?2';
            return sprintf('<img alt="" src="%1$s/assets/public/img/person.png"  height="%2$d" width="%2$d">',
                get_template_directory_uri(), $size);

        } else {
            if (function_exists('get_avatar')) {
                if ( $url ) return get_avatar_url($user->user_email, $size);
                return get_avatar($user->user_email, $size);
            } else {
                $grav_url = "http://www.gravatar.com/avatar/" .
                    md5(strtolower($user->user_email));
                if ( $url ) return $grav_url;
                return sprintf('<img alt="" src="%1$s"  height="%2$d" width="%2$d">', $grav_url, $size);
            }
        }
    }

    public static function reputationData( $data ,$witch ,$askID ,$default )
    {
        if ( !empty( $data ) ){
            $data = unserialize( $data );
            if ( !empty( $data ) && empty( $askID ) && isset( $data[$witch] ) ){
                return $data[$witch];
            }elseif ( !empty( $data ) && !empty( $askID ) && isset( $data[$witch] ) ){
                return $data[$witch][$askID];
            }
        }
        return $default;
    }


    public static function initialUserActions()
    {
        return [ 'likes' => 0 ,'dislikes' => 0 ,'bookmark_list' => [] ];
    }

    public static function isSetFilter( $filter )
    {
        if ( !empty( $filter['search'] )   ||
             !empty( $filter['tag'] )      ||
             !empty( $filter['creator'] )  ||
             $filter['response'] !== 'all' ||
             $filter['order_by'] !== 'created_date ASC' ){
            return true;
        }
        return false;
    }


    public static function reputationAction( $creator ,$action ,$status )
    {
        $reputation = get_user_meta( (int) $creator ,'ask_user_reputation' , true  );
        if( empty( $reputation )  ){
            $reputation =  self::initialUserActions();
        }
        if ( $status == 'increment' ){
            $reputation[$action] = $reputation[$action] + 1;
        }else{
            $reputation[$action] = $reputation[$action] - 1;
        }
        update_user_meta( (int) $creator , 'ask_user_reputation' ,$reputation  );
        return true;
    }

//ok
    public static function nonceChecker()
    {
        if ( isset( $_POST['nonce']) && wp_verify_nonce( $_POST['nonce'],'ask_dashboard_nonce')  ){
            return true;
        }
        wp_send_json('Access Denied!!!' , 403 );
    }

//ok
    public static function updateLogs( $askID ,$userID ,$action )
    {
        $logs = (object) AskDB::get_instance()::getSpecific( (int) $askID , false );

        if ( isset( $logs->log ) && !empty( $logs->log ) && strlen( $logs->log ) > 5 ){
            $logs  = unserialize( $logs->log );
            if ( !is_array( $logs ) ){
                $logs = [];
            }
            $logs[$action]['user'] = $userID;
            $logs[$action]['time'] = date('Y-m-d H:i:s');
         }else{
            $logs = [];
            $logs[$action]['user'] = $userID;
            $logs[$action]['time'] = date('Y-m-d H:i:s');

        }
        return serialize( $logs );
    }

    //ok
    public static function excerpt( $string ,$length )
    {
        $str_len = strlen( $string );
        $string  = strip_tags( $string );

        if ( $str_len > $length ) {
            $stringCut = mb_substr( $string, 0, $length-15 );
            $string = $stringCut.'.....'.mb_substr( $string, $str_len-10, $str_len-1 );
        }
        return $string;
    }


    //ok
    public static function switchColor( $like ,$dislike ,$which )
    {
        $active     = '#FF9698';
        $deactivate = '#eeeeee';
        $null       = '#888888';

        if ( !$like && !$dislike ){
            return $null;
        }elseif ( $like && !$dislike && $which =='like' ){
            return $active;
        }elseif ( $like && !$dislike && $which =='dislike' ){
            return $deactivate;
        }elseif ( !$like && $dislike && $which =='like' ){
            return $deactivate;
        }elseif ( !$like && $dislike && $which =='dislike' ){
            return $active;
        }
        return $null;
    }

//ok
    public static function likeStatus( $ask , $userID )
    {
        $like_list = explode( ',' , $ask->like_list );
        if ( in_array( $userID ,$like_list ) ){
            return true;
        }
        return false;
    }

    //ok
    public static function dislikeStatus($ask , $userID )
    {
        $dislike_list = explode( ',' , $ask->dislike_list ) ;
        if ( in_array( $userID ,$dislike_list ) ){
            return true;
        }
        return false;
    }

//ok
    public static function iconHandler( $ask ,$current ,$output )
    {
        $return = '';
        if ( $ask->creator != $current ){
            switch ( $output ){
                case 'like_icon' :
                    $return = AskFunctions::switchColor( AskFunctions::likeStatus( $ask ,$current ) ,
                        AskFunctions::dislikeStatus( $ask ,$current ) ,'like' );
                    break;
                case 'dislike_icon' :
                    $return = AskFunctions::switchColor( AskFunctions::likeStatus( $ask ,$current ) ,
                        AskFunctions::dislikeStatus( $ask ,$current ) ,'dislike' );
                    break;
                case 'like_class' :
                    $return = !AskFunctions::dislikeStatus( $ask ,$current ) ? 'ask-arrow-up-svg' : '';
                    break;
                case 'dislike_class' :
                    $return = !AskFunctions::likeStatus( $ask ,$current ) ? 'ask-arrow-down-svg' : '';
                    break;
            }
        }
        return $return;
    }


    //ok
    public static function getFaveAction( $userID )
    {
        $fave = [ 'like' => 0 , 'dislike' => 0 ];
        $creator_m   = get_user_meta( $userID,'ask_user_reputation' ,true );
        if ( !empty( $creator_m ) ){
            $creator_m = unserialize( $creator_m );
            $fave['like']    = $creator_m['like'] ?? 0;
            $fave['dislike'] = $creator_m['dislike'] ?? 0;
        }
        return $fave;
    }

    //ok
    public static function statusTranslate( $status )
    {
        $return = '';
        switch ( $status ){
            case 'pending':
                $return = 'در حال بررسی';
                break;
            case 'accept':
                $return = 'تایید شد';
                break;
            case 'reject':
                $return = 'رد شد';
                break;
        }
        return $return;
    }


    //ok
    public static function statusColor( $status )
    {
        $return = '';
        switch ( $status ){
            case 'pending':
                $return = '#FF9500';
                break;
            case 'accept':
                $return = '#3DFF00';
                break;
            case 'reject':
                $return = '#FF0049';
                break;
        }
        return $return;
    }

//ok
    public static function getTags( $tags ,$single = false )
    {
        $all_tags = '';
        $single   = $single ? 'class="call-in-single-page"' : '';
        if ( !empty( $tags ) ){
            $tags  = explode(',' , $tags );
            if ( !empty( $tags ) ){
                if ( is_array( $tags ) ) {
                    foreach ( $tags as $tag ){
                        $all_tags .=  '<a onclick="return false; " '.$single.' href="'. home_url().'/ask/?tag='.$tag.'">'.$tag.'</a>';
                    }
                }
            }
        }
        return $all_tags;
    }

    public static function notApprovesTags( $itemTags ,$element )
    {
        $final_tags  = '';
        $ask_options = maybe_unserialize( get_option('hamfy_ask_options' ) );
        $tags = $ask_options['tags'] ?? [];
        if ( !empty( $ask_options ) && is_array( $tags ) ){
            foreach ( $tags as $tag ){
                $selected = '';
                if ( in_array( $tag ,$itemTags ) ){
                    $selected = 'selected';
                }
                $final_tags .= '<'.$element.' value="'.$tag.'" '.$selected.'>  '.$tag.' </'.$element.'>';
            }
        }
        if ( !empty( $itemTags ) && is_array( $itemTags ) ){
            foreach ( $itemTags as $t_s ){
                if ( !in_array( $t_s ,$tags ) ){
                    $final_tags .= '<'.$element.' value="'.$t_s.'" selected >  '.$t_s.' </'.$element.'>';
                }
            }
        }
        return $final_tags;
    }

    public static function insertedTags( $itemTags )
    {
        $final_tags = '';
        if ( !empty( $itemTags ) && is_array( $itemTags ) ){
            foreach ( $itemTags as $t_s ){
                $final_tags .= '<a>  '.$t_s.' </a>';
            }
        }
        return $final_tags;
    }


    public static function getQueryString( )
    {
        $string = $_SERVER['QUERY_STRING'];
        if ( empty( $string ) ) $string = '';
            parse_str( $string , $query );
            !isset( $query['limit'] )    ? $query['limit']    = 15    : false ;
            !isset( $query['page'] )     ? $query['page']     = 0     : false ;
            !isset( $query['search'] )   ? $query['search']   = ''    : false ;
            !isset( $query['tag'] )      ? $query['tag']      = ''    : false ;
            !isset( $query['creator'] )  ? $query['creator']  = ''    : false ;
            !isset( $query['response'] ) ? $query['response'] = 'all' : false ;
            !isset( $query['order_by'] ) ? $query['order_by'] = 'created_date ASC'  : false ;
        return $query;
    }

//ok
    public static function askManagerChecker( $userID )
    {
        if ( !user_can( $userID , 'administrator') ){
             $userCap = get_user_meta( $userID ,'ask_manager_capability' ,true );
             if ( !is_wp_error( $userCap ) && !empty( $userCap ) && $userCap === 'can' ){
                 return true;
             }else{
                 return false;
             }
        }else{
            return true;
        }
    }

    //ok
    public static function capCheckerDashboard( $userID )
    {
        $result = self::askManagerChecker( $userID );
        if ( !$result ){
            wp_send_json( ['result' => 'access denied!!!' , 403 ] );
        }
        return true;
    }

    //todo for what 
    public static function calculateUserScore( $like , $dislike , $views )
    {
        $like    = $like * 3;
        $dislike = $dislike * 2;
        $like    = $like + $views;
        return $like - $dislike;
    }

    //todo must check with sabzevari
    public static function sanitizeAskContent( $ticket_content )
    {
        $allowed_html = [
            'div' => [
                'class' => [
                    'code-toolbar'
                ],
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'p' => [
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'strong' => [
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'span' => [
                'class' => [] ,
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'pre' => [
                'class' => [
                    'language-html' ,
                    'language-css' ,
                    'language-javascript' ,
                    'language-php' ,
                    'language-sql'
                ],
                'tabindex' => [] ,
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'code' => [
                'class' => [
                    'ck-code_selected',
                    'language-html' ,
                    'language-css' ,
                    'language-javascript' ,
                    'language-php' ,
                    'language-sql'
                ],
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'h1' => [
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'h2' => [
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'h3' => [
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
            'li' => [
                'style' => [
                    'text-align:right;',
                    'text-align:center;',
                    'text-align:left;',
                    'text-align:justify;',
                ]
            ],
        ];
        return wp_kses( $ticket_content ,$allowed_html , ['https' ,'http'] );
    }



    public static function isUserLoggedIn( $userID ,$class_id )
    {
        if ( $userID > 0 ){
            return $class_id;
        }
        return '';
    }


    public static function isUserNotLoggedIn( $userID ,$option = '' )
    {
        if ( $userID < 1 ){
            return " onclick=\"return hfl_show_login_form(this,'$option')\" ";
        }
        return '';
    }


    public static function getEncryptCode()
    {
        $current_user_id = get_current_user_id();
        if ( is_numeric( $current_user_id ) && $current_user_id > 0 ){
            return self::encryptID( $current_user_id );
        }
        return '';
    }

    public static function encryptID( $id ){
        $endOfDay   = strtotime("tomorrow", strtotime("today", time()) );
        $key        = get_option('hamfy_token_options');
        $hashID     = new Hashids( $endOfDay+(int)$key  );
        return $hashID->encode( $id.$key );
    }

//OK
    public static function decryptID( $hashedID ){
        $endOfDay        = strtotime("tomorrow", strtotime("today", time() ) );
        $key             = get_option('hamfy_token_options' , true );
        $hashID          = new Hashids( $endOfDay+(int)$key );
        $user_hashed_id  = $hashID->decode( $hashedID )[0];
        $outputUser      = (int) str_replace( $key , '' , $user_hashed_id );
        if ( is_numeric( $outputUser ) and  $outputUser > 0 ){
            return $outputUser;
        }else{
            return false;
        }
    }

//ok
    public static function sanitizer( $value ,$functions ){
        $functions = explode(',', $functions);
        foreach ( $functions as $function ) {
            if ( function_exists( $function ) ) {
                $value = $function( $value );
            }
        }
        return $value;
    }


    public static function getUserData( $userData ,$userID ,$metaKey )
    {
        if ( !empty( $userData ) && !empty( $userID ) && !empty( $metaKey ) ){
            if ( isset( $userData[$userID][$metaKey] ) ){
                return $userData[$userID][$metaKey];
            }
        }
        return '';
    }


    public static function calculaterVote( $like ,$dislike ,$view )
    {
        return (( $like + $view ) * 1000 ) - ( $dislike * 500 );
    }


    public static function isAdmin( $userID )
    {
        if( user_can( $userID ,'administrator') ){
            return true;
        }
        return false;
    }
}