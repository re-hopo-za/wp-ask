<?php

namespace HWP_Ask\includes;







use WP_REST_Request;
use WP_REST_Server;


class AskRest{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public $namespace;
    public $version;
    public $endpoint;
    public $api;
    private $params;
    private $userID;



    public function __construct(){

        add_action('rest_api_init' , [ $this , 'routes' ] ) ;

        $this->namespace = 'hamfy';
        $this->version   = 'v1.1';
        $this->endpoint  = 'ask';
        $this->api       = $this->namespace.'/'.$this->version.'/'.$this->endpoint ;
    }


    public function routes(){

        register_rest_route(  $this->namespace , $this->version.'/'.$this->endpoint , [
            'methods'  => WP_REST_Server::READABLE          ,
            'callback' => [ $this , 'read' ]           ,
            'args'     => $this->argsValidator('READABLE')   ,
            'permission_callback' => [ $this , 'authentication' ],
        ]);

        register_rest_route(   $this->namespace , $this->version.'/'.$this->endpoint , [
            'methods'  => WP_REST_Server::CREATABLE         ,
            'callback' => [ $this , 'create' ]         ,
            'args'     => $this->argsValidator('CREATABLE')  ,
            'permission_callback' => [ $this , 'authentication' ]
        ]);

        register_rest_route(   $this->namespace , $this->version.'/'.$this->endpoint , [
            'methods'  => WP_REST_Server::EDITABLE           ,
            'callback' => [ $this , 'update' ]          ,
            'args'     => $this->argsValidator('EDITABLE')   ,
            'permission_callback' => [ $this   , 'authentication' ],
        ]);
    }


    public function authentication( WP_REST_Request $request ){
        $this->params  = (object) $request->get_params();
        if(isset($request->get_headers()['usertoken'][0]) && !empty( $request->get_headers()['usertoken'][0] ) ){
            $this->userID  = AskFunctions::decryptID( $request->get_headers()['usertoken'][0]);
        }else{
            $this->userID = 0;
        }
        return true;
    }


    public function read(){
        $params = $this->params;
        if (  isset( $params->id )  ){
            if ( $params->id !== 'new' ){
                wp_send_json( ['result' => AskUI::single( (int)$params->id ,$this->userID ,true ) , 'c_user' => $this->userID ,'ask_id' => $params->id  ] , 200  );
            }else{
                wp_send_json( ['result' => AskUI::new( $this->userID ,true )  ] ,200  );
            }
        }else{
            wp_send_json( ['result' => AskUI::all( (array) $params ,$this->userID ,true ) , 'c_user' =>  $this->userID  ] , 200  );
        }
    }


    public function create(){
        $params = $this->params;
        $act    = $params->act;
        AskPermission::get_instance()->checkPermission( $params->token , $params->action ,$this->userID );
        if ( 'new-reply' == $act ){
            AskDB::get_instance()::checkParentAsk( $params->parent_id );
            AskDB::get_instance()::newReply( $params  ,$this->userID );
            wp_send_json( ['result' => 'created' ] , 200  );
        }elseif( 'new-ask' == $act ){
            AskDB::get_instance()::newAsk( $params  ,$this->userID );
            wp_send_json( ['result' => 'saved' ] , 200  );
        }
    }


    public function update(){
        $params = $this->params;
        $action = $params->action;
        if ( AskPermission::userValidator( $this->userID ) && is_super_admin( $this->userID ) ){
            if ( $action === 'ask_update_like' ){
                $result = AskDB::get_instance()::updateLike( (int)$params->id  ,$this->userID );
                if ( is_array( $result ) ){
                    wp_send_json( ['result' => $result['result'] ,'status' => $result['status']  ] , 200  );
                }
                wp_send_json( ['result' => 'An Error Occurred On Server' ] , 500  );
            }

            if ( $action === 'ask_update_dislike' ){
                $result = AskDB::get_instance()::updateDislike( (int)$params->id  ,$this->userID );
                if ( is_array( $result ) ){
                    wp_send_json( ['result' => $result['result'] , 'status' => $result['status']  ] , 200  );
                }
                wp_send_json( ['result' => 'An Error Occurred On Server' ] , 500  );
            }

            if ( $action === 'ask_accept_question' ){
                AskFunctions::capCheckerDashboard( $this->userID );
                $result = AskDB::get_instance()::acceptAsk( (int)$params->id  , $this->userID );
                if ( $result == 200 ){
                    wp_send_json( ['result' => 'Updated'  ] , 200  );
                }
                wp_send_json( ['result' => 'An Error Occurred On Server' ] , 500  );
            }

            if ( $action === 'ask_reject_question' ){
                AskFunctions::capCheckerDashboard( $this->userID );
                $result = AskDB::get_instance()::rejectAsk( (int)$params->id  , $this->userID , $params->comment );
                if ( $result == 200 ){
                    wp_send_json( ['result' => 'Updated'  ] , 200  );
                }
                wp_send_json( ['result' => 'An Error Occurred On Server' ] , 500  );
            }

            if ( $action === 'ask_update_question' ){
                AskFunctions::capCheckerDashboard( $this->userID );
                $result = AskDB::get_instance()::updateQuestion( $params  , $this->userID );
                if ( $result == 200 ){
                    wp_send_json( ['result' => 'Updated'  ] , 200  );
                }
                wp_send_json( ['result' => 'An Error Occurred On Server' ] , 500  );
            }

            if ( $action === 'ask_update_reply' ){
                AskFunctions::capCheckerDashboard( $this->userID );
                $result = AskDB::get_instance()::updateReply( $params ,$this->userID );
                if ( $result == 200 ){
                    wp_send_json( ['result' => 'Updated'  ] , 200  );
                }
                wp_send_json( ['result' => 'An Error Occurred On Server' ] , 500  );
            }

            if ( $action === 'add_to_bookmark_list' ){
                $result = AskProcess::get_instance()::addBookmark( (int) $params->ask_id  , $this->userID );
                if ( $result != '' ){
                    wp_send_json( ['result' => $result  ] , 200  );
                }
                wp_send_json( ['result' => 'An Error Occurred On Server' ] , 500  );
            }

            if ( $action === 'update_view_count' ){
                $result = AskDB::get_instance()::updateViews( (int)$params->ask_id );
                if ( $result != '' ){
                    wp_send_json( ['result' => $result  ] , 200  );
                }
                wp_send_json( ['result' => 'An Error Occurred On Server' ] , 500  );
            }

        }else{
            wp_send_json( ['result' => 'You must logged in to do any action ' ] , 500  );
        }
    }

    public function argsValidator( $which ){

        $args=[];
        if ( $which == 'READABLE' ){
            $args['id']=[
                'required'           => false        ,
                'description'        => 'شناسه پرسش' ,
                'type'               => 'string'        ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer($value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return  is_numeric( $value ) || $value == 'new' ;
                },
            ];

            $args['limit']=[
                'required'           => false                   ,
                'description'        => 'تعداد تیکت قابل نمایش' ,
                'type'               => 'int'                   ,
                'default'            => 10                      ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return $value >  10 || $value <= 50;
                },
            ];

            $args['page']=[
                'required'           => false                ,
                'description'        => 'صفحه در حال نمایش ' ,
                'type'               => 'int'                ,
                'default'            => null                 ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return $value >=  0;
                },
            ];

            $args['search']=[
                'required'           => false      ,
                'description'        => 'جستجو'    ,
                'type'               => 'string'   ,
                'default'            => null       ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer( $value  ,'sanitize_text_field,trim' );
                },
                'validate_callback'  => function( $value ){
                    return true;
                },
            ];
        }
        elseif ( $which == 'CREATABLE' ){


            $args['act'] = [
                'required'           => false        ,
                'description'        => 'عملیات'     ,
                'type'               => 'string'     ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer($value ,'sanitize_text_field,trim'); 
                },
                'validate_callback'  => function( $value ){
                    return  $value == 'new-ask' || $value == 'new-reply' ;
                },
            ];

            $args['title'] = [
                'required'           => false        ,
                'description'        => 'موضوع سوال' ,
                'type'               => 'string'     ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return strlen($value) <= 250 ;
                },
            ];

            $args['content']=[
                'required'           => true        ,
                'description'        => 'متن سوال '  ,
                'type'               => 'string'    ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizeAskContent(  $value );
                },
                'validate_callback'  => function( $value ){
                    return strlen($value)<=900000 && strlen($value)>=2;
                },
            ];

            $args['reply_id']=[ 
                'required'           => false           ,
                'description'        => 'شناسه پاسخ داده شده' ,
                'type'               => 'int'         ,
                'default'            =>  0            ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function($value){
                    return is_numeric( $value );
                },
            ];

            $args['parent_id']=[
                'required'           => false           ,
                'description'        => 'شناسه پرنت سوال'       ,
                'type'               => 'int'         ,
                'default'            =>  0            ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function($value){
                    return is_numeric( $value );
                },
            ];

        } elseif ( $which == 'EDITABLE' ){
            $args['action'] = [
                'required'           => true        ,
                'description'        => 'نام عملیات بروزرسانی' ,
                'type'               => 'string'     ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return strlen( $value ) <= 50;
                },
            ];

            $args['ask_id'] = [
                'required'           => false        ,
                'description'        => ' شناسه والد' ,
                'type'               => 'string'     ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return is_numeric( $value );
                },
            ];

            $args['title'] = [
                'required'           => false        ,
                'description'        => 'موضوع سوال' ,
                'type'               => 'string'     ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return strlen($value) <= 250 ;
                },
            ];

            $args['comment'] = [
                'required'           => false           ,
                'description'        => 'متن کامنت  '   ,
                'type'               => 'string'        ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return strlen( $value ) <= 250;
                },
            ];

            $args['tags'] = [
                'required'           => false           ,
                'description'        => 'متن کامنت  '   ,
                'type'               => 'string'        ,
                'sanitize_callback'  => function( $value ){
                    return array_map( function ( $index ){
                        return AskFunctions::sanitizer( $index ,'sanitize_text_field,trim');
                    } , $value );
                },
                'validate_callback'  => function( $value ){
                    return is_array( $value );
                },
            ];

            $args['accept'] = [
                'required'           => false           ,
                'description'        => 'وضعیت تایید سوال'   ,
                'type'               => 'string'        ,
                'sanitize_callback'  => function( $value ){
                    return AskFunctions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return $value == 'true' || $value == 'false';
                },
            ];


        }



        return $args;
    }








}


