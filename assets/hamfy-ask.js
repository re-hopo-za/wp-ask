
jQuery(function ($) {

    const ask_object      = hamfy_ask_objects;
    const ask_rest_url    = ask_object.root ;
    const admin_url       = ask_object.admin_url ;
    const ask_nonce       = ask_object.nonce ;
    const ask_home_url    = ask_object.home_url ;

    const captcha_pub_key = ask_object.captcha;
    const user_token      = ask_object.user_token;
    const btn_loader      = ask_object.ask_btn_loader;
    const params          = ask_object.params;
    let   ask_all         = ask_object.ask_all;
    let   ask_all_loop    = ask_object.ask_all_loop;
    let   ask_single      = ask_object.ask_single;
    let   ask_single_loop = ask_object.ask_single_loop;
    let   ask_new         = ask_object.ask_new;
    let   ask_reply_form  = ask_object.ask_reply_form;
    let   ask_not_found   = ask_object.ask_not_found;

    let   filter  =
        {
          limit   : params.limit   , page     : params.page     ,
          search  : params.search  , tag      : params.tag      ,
          creator : params.creator , order_by : params.order_by ,
          response : params.response
        }



    function filter_handler(){
        if ( filter.search !== ''){
            let search = filter.search;
            if ( search != null && search.indexOf( "'") > -1 ){
                search = search.replace( "'" , '' )
            }
            $(document).find('#ask-main-search').val( search );
        }
        if ( filter.response != null ){
            $(document).find('#ask-response-input').val(filter.response );
        }
        $(document).find('#order-by-select').val( filter.order_by );
        $(document).find('.limit-con a[data-limit='+filter.limit+']').addClass('active');
        $(document).find('.pagination-con a[data-page="'+filter.page+'"]').addClass('active');
    }
    filter_handler();


    function init_reply_editor(){
        let reply_editor = $(document).find('#ask-reply-editor');
        if ( reply_editor.length ){
            editor_creator( '#ask-reply-editor' )
        }
    }
    init_reply_editor();


    function init_new_editor(){
        let new_editor = $(document).find('#ask-new-editor');
        if ( new_editor.length ){
            editor_creator( '#ask-new-editor' )
        }
    }
    init_new_editor();


    function editor_creator( editor_creator ){
        if ( window.editor ){
            window.editor.destroy()
                .then( () => {
                    editor.ui.view.toolbar.element.remove();
                    editor.ui.view.editable.element.remove();
                } );
        }
        const maxCharacters  = 500;
        const characters_con = $(document).find('.demo-update__words' );
        ClassicEditor
        .create( document.querySelector( editor_creator), {
            toolbar: {
                items: [
                    'heading',
                    'bold',
                    'code',
                    '|',
                    'codeBlock',
                    'specialCharacters',
                    '|',
                    'alignment'
                ]
            },
            wordCount: {
                onUpdate: stats => {
                    const isLimitExceeded = stats.characters > maxCharacters;
                    characters_con.text( stats.characters ).css('color' , '#999');
                    characters_con.text(  stats.characters  ).css('color' , 'red');
                }
            },
            language: 'fa',
            licenseKey: ''
        } )
        .then( editor => {
            window.editor = editor;
        } )
        .catch( error => {
            console.error( 'Oops, something went wrong!'+ error );
        } );
    }

    function init_tags_container(){
        let tags_selector = $(document).find('.ask-new-tag');
        if ( tags_selector.length ){
            $('select').selectize({create: true});
            tags_selector.selectize();
        }
    }
    init_tags_container();


    function get_profile(){
        $.ajax({
            url      : admin_url  ,
            method   : 'POST'     ,
            dataType : 'html'     ,
            data: {
                action :'ask_get_profire_ui' ,
                nonce  : ask_nonce
            },
            success: function (data) {
            },
        }).always(function ( jqXHR, textStatus, xhr ) {
            if ( xhr.status === 200  ){
                $(document).find('.ask-profile-root').html( jqXHR );
            }else {
                iziToast.error({
                    title: 'خطا!!',
                    message:  'هنگام دریافت اطلاعات صفحه اصلی ',
                    position: 'topRight',
                    rtl: true
                });
            }
        });
    }


    $(document).on('click' ,'body.logged-in .ask-create-new-ask', function (e){
        e.preventDefault();
        let $this   = $(document).find('.ask-new-editor-con');
        let title   = $this.find('#title-input').val();
        let content = editor.getData();
        let tags    = $this.find('.ask-new-tag').val();

        if ( !title || title.length < 10 ){
            iziToast.error({
                title: 'خطا!!',
                message: ' عنوان خالی یا کمتر از 10 کارکتر میباشد' ,
                position: 'topRight',
                rtl: true
            });
            return;
        }
        if( !content || content.length < 50  ){
            iziToast.error({
                title: 'خطا!!',
                message: ' محتوا خالی یا کمتر از 30 کارکتر میباشد',
                position: 'topRight',
                rtl: true
            });
            return;
        }
        try {
            iziToast.question({
                timeout: 20000,
                close: false,
                overlay: true,
                zindex: 99999,
                title: 'سوال شما ثبت شود',
                position: 'center',
                rtl: true,
                buttons: [
                    ['<button><b>ثبت شود</b></button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                        hamfy_loader(true);
                        if (captcha_pub_key.length > 0) {
                            grecaptcha.ready(function () {
                                grecaptcha.execute(captcha_pub_key,
                                    {action: 'submit'}).then(function ( token ) {
                                    if ( token ) {
                                        send_ask_request(title, content, tags, token)
                                    }
                                });
                            });
                        } else {
                            send_ask_request(title, content, tags, '')
                        }
                    }, true],
                    ['<button>خیر</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    }],
                ],
            });

        } catch (err) {
            hamfy_loader( false );
            iziToast.error({
                title: 'خطا!!',
                message: err.message,
                position: 'topRight',
                rtl: true
            });
        }

    });

    function send_ask_request(title, content, tags, token) {

        $.ajax({
            url: ask_rest_url  ,
            method: 'POST'   ,
            dataType: 'json' ,
            data: {
                token   : token     ,
                action  : 'submit'  ,
                act     : 'new-ask' ,
                title   : title     ,
                content : content   ,
                tags    : tags
            },
            headers: {
                'usertoken': user_token
            },
            success: function (data) {
            },
        }).always(function (jqXHR, textStatus, xhr ) {
            hamfy_loader( false );
            if (  xhr.status === 200 ){
                iziToast.question({
                    timeout: 20000,
                    close: false,
                    overlay: true,
                    zindex: 99999,
                    title: 'سوال شما پس از تایید منتشر میشود',
                    position: 'center',
                    rtl: true,
                    buttons: [
                        ['<button><b> متوجه شدم </b></button>', function (instance, toast) {
                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                            hamfy_loader( true );
                            get_main_page( true );
                        }, true],
                    ],
                    onClosing: function(instance, toast, closedBy){
                    },
                    onClosed: function(instance, toast, closedBy){
                    }

                });
                get_profile();
            }else {
                iziToast.error({
                    title: 'خطا!!',
                    message: 'خطا هنگام ذخیره سوال',
                    position: 'topRight',
                    rtl: true
                });
            }
        });
    }
    
    $(document).on('click' ,'body.logged-in .ask-create-new-reply', function (e){
        e.preventDefault();
        let content   = editor.getData();
        let parent_id = parseInt( $(document).find('#ask-reply-editor-con').data('parent-id') );

        if( !content || content.length < 50  ){
            iziToast.error({
                title: 'خطا!!',
                message: ' محتوا خالی یا کمتر از 30 کارکتر میباشد',
                position: 'topRight',
                rtl: true
            });
            return true;
        }
        try {
            iziToast.question({
                timeout: 20000,
                close: false,
                overlay: true,
                zindex: 99999,
                title: 'پاسخ شما ثبت شود',
                position: 'center',
                rtl: true,
                buttons: [
                    ['<button><b>ثبت شود</b></button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                        hamfy_loader(true);
                        if (captcha_pub_key.length > 0) {
                            grecaptcha.ready( function (){
                                grecaptcha.execute(captcha_pub_key,
                                    {action: 'submit'}).then( function ( token ) {
                                    if ( token ) {
                                        ask_send_reply(content,parent_id,token);
                                    }
                                });
                            });
                        } else {
                            ask_send_reply(content,parent_id,'');
                        }
                    }, true],
                    ['<button>خیر</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    }],
                ],
                onClosing: function(instance, toast, closedBy){
                },
                onClosed: function(instance, toast, closedBy){
                }
            });
        } catch (err) {
            hamfy_loader( false );
            iziToast.error({
                title: 'خطا!!',
                message: err.message,
                position: 'topRight',
                rtl: true
            });
            console.log(err);
        }
    });

    function ask_send_reply(content,parent_id,token) {
        $.ajax({
            url: ask_rest_url ,
            method: 'POST'    ,
            dataType: 'json'  ,
            data: {
                token     : token       ,
                action    : 'submit'    ,
                act       : 'new-reply' ,
                content   : content     ,
                parent_id : parent_id

            },
            headers: {
                'usertoken': user_token
            },
            success: function (data) {
            },
        }).always(function (jqXHR, textStatus, xhr) {
            hamfy_loader( false );
            if (  xhr.status === 200   ){
                editor.setData('پاسخ شما پس از بررسی منتشر میشود')
                iziToast.info({
                    title: 'توجه',
                    message: ' پاسخ شما پس از بررسی منتشر میشود ',
                    position: 'topRight',
                    rtl: true
                });
                get_profile();
            }else {
                iziToast.error({
                    title: 'توجه',
                    message: 'خطا هنگام ثبت پاسخ',
                    position: 'topRight',
                    rtl: true
                });
            }
        });
    }

    $(document).on('click' ,'.not-loggedin', function (e){
          hfl_show_login_form( this ,' ' );
    })

    $(document).on('click' , '#ask-new' , function (e){
        hamfy_loader(true );
        get_new_ask_data();
    });


    function get_new_ask_data(){
        $.ajax({
            url     : ask_rest_url ,
            method  : 'GET'   ,
            dataType: 'json'  ,
            data: {
                id : 'new'
            },
            headers: {
                'usertoken': user_token
            },
            success: function (data) {
                if ( data.result ){
                    hamfy_loader(false );
                    $(document).find('main.main-container .ask').replaceWith( data.result );
                    init_new_editor();
                    init_tags_container();
                    history.pushState( 'new' , "New Ask", "/ask/new/" );
                }else {
                    hamfy_loader(false );
                    iziToast.error({
                        title: 'خطا!!',
                        message: 'خطا هنگام بارگیری محتوا',
                        position: 'topRight',
                        rtl: true
                    });
                }
            },
        });
    }

    $(document).on('click' , '.ask-item-link' , function (){
        let askID = $(this).data('ask-id');
        if ( askID ){
            hamfy_loader( true );
            get_single_page( askID );
        }
    });


    function get_single_page( askID ){
        $.ajax({
            url     : ask_rest_url ,
            method  : 'GET'   ,
            dataType: 'json'  ,
            data: {
                id   : askID
            },
            headers: {
                'usertoken': user_token
            },
        }).always(function ( jqXHR, textStatus, jqXHR2 ) {
            if ( jqXHR.result ){
                $(document).find('main.main-container .ask').replaceWith( jqXHR.result );
                init_reply_editor();
                reloadPrism();
                history.pushState(  'single' , "Single Ask", "/ask/"+jqXHR.ask_id+'/');
                hamfy_loader(false );
                update_ask_views( askID );
            }else {
                hamfy_loader( false );
                iziToast.error({
                    title: 'خطا!!',
                    message: 'خطا هنگام بارگیری محتوا',
                    position: 'topRight',
                    rtl: true
                });
            }
        });
    }

    function calculaterVote( like ,dislike ,view ){
        return ( ( like + dislike ) * 1000 ) - ( view * 500 );
    }



    $(document).on('click' , 'body.logged-in .ask-arrow-up .ask-arrow-up-svg' , function (){
        let $this = $(this);
        $this.css('fill','#eee').removeClass('ask-arrow-up-svg');
        let askID = $this.data('ask-id');
        let parent_id = '.'+$this.attr('id');
        if ( askID ){
            $.ajax({
                url      : ask_rest_url ,
                method   : 'PUT'        ,
                dataType : 'json'       ,
                data: {
                    action : 'ask_update_like' ,
                    id     : askID
                },
                headers: {
                    'usertoken': user_token
                },
                success: function (data) {
                },
            }).always(function (jqXHR, textStatus, xhr) {
                if ( xhr.status === 200  ){
                    $this.parent().siblings('.ask-count').html( '<output> '+ jqXHR.result +' </output>' );
                    if ( jqXHR.status === 'decrement'){
                        $this.css('fill','#888888').addClass('ask-arrow-up-svg');
                        $(parent_id).find('svg').css('fill','#888888').addClass('ask-arrow-down-svg');
                    }else{
                        $this.css('fill','#FF9698').addClass('ask-arrow-up-svg');
                        $(parent_id).find('svg').css('fill','#eeeeee').removeClass('ask-arrow-down-svg');
                    }
                }else {
                    iziToast.error({
                        title: 'خطا!!',
                        message:  jqXHR.status,
                        position: 'topRight',
                        rtl: true
                    });
                }
            });
        }
    });





    $(document).on('click' , 'body.logged-in .ask-arrow-down .ask-arrow-down-svg' , function (){
        let $this = $(this);
        $this.css('fill','#eee').removeClass('ask-arrow-down-svg');
        $this.parent().siblings('.ask-count').html( btn_loader );
        let askID = $this.data('ask-id');
        let parent_id = '.'+$this.attr('id');
        if ( askID ){
            $.ajax({
                url      : ask_rest_url ,
                method   : 'PUT'        ,
                dataType : 'json'       ,
                data: {
                    action : 'ask_update_dislike' ,
                    id     : askID
                },
                headers: {
                    'usertoken': user_token
                },
                success: function (data) {
                },
            }).always(function (jqXHR, textStatus, xhr) {

                if ( xhr.status === 200  ){
                    $this.parent().siblings('.ask-count').html( '<output> '+ jqXHR.result +' </output>'  );
                    if ( jqXHR.status === 'decrement'){
                        $this.css('fill','#888888').addClass('ask-arrow-down-svg');
                        $(parent_id).find('svg').css('fill','#888888').addClass('ask-arrow-up-svg');
                    }else{
                        $this.css('fill','#FF9698').addClass('ask-arrow-down-svg');
                        $(parent_id).find('svg').css('fill','#eeeeee').removeClass('ask-arrow-up-svg');
                    }
                }else {
                    iziToast.error({
                        title: 'خطا!!',
                        message:  jqXHR.status,
                        position: 'topRight',
                        rtl: true
                    });
                }
            });
        }

    });



    $(document).on('click' , '.limit-con a' , function (){
        hamfy_loader(true );
        filter.limit  = parseInt( $(this).data('limit') );
        filter.page   = 0;
        get_main_page();
    });

    $(document).on('click' , '.pagination-con a' , function (){
        hamfy_loader(true );
        filter.page   = parseInt( $(this).data('page') );
        get_main_page();
    });

    $(document).on('click' , '.tags a' , function (){
        hamfy_loader(true );
        filter.tag = $(this).text();
        get_main_page( $(this).hasClass('call-in-single-page') );
    });

    $(document).on('click' , '.profile-details a' , function (){
        hamfy_loader(true );
        filter.creator = $(this).data('creator');
        get_main_page( $(this).hasClass('call-in-single-page') );
    });

    $(document).on('click' , '#remove-filter' , function (){
        hamfy_loader( true );
        filter.limit    = 15;
        filter.page     = 0;
        filter.search   = null;
        filter.order_by = 'id';
        filter.response = 'all';
        get_main_page();
    });

    $(document).on('change' , '#order-by-select' , function (){
        hamfy_loader(true );
        filter.order_by = $(this).val();
        get_main_page();
    });

    $(document).on('change' , '#ask-response-input' , function (){
        hamfy_loader(true );
        filter.response = $(this).val();
        get_main_page();
    });




    $(document).find('.ask-search-con input').keypress(function (e) {
        if (e.which === 13) {
            search( $(this).val() );
        }
    });


    function search( keyword ){
        if ( keyword !== '' &&  keyword.length > 2 ){
            hamfy_loader(true );
            filter.search   = keyword;
            get_main_page();
        }
    }

    $(document).on('click' , '#ask-refresh , #ask-home' ,function (){
        hamfy_loader(true );
        filter.page     = 0;
        filter.limit    = 15;
        filter.search   = '';
        filter.tag      = '';
        filter.creator  = '';
        filter.order_by = 'created_date ASC';
        filter.response = 'all';
        replace_url_in_single( true );
        get_main_page( true );
    });


    function get_main_page( whole = false ){
        $.ajax({
            url: ask_rest_url ,
            method: 'GET'     ,
            dataType: 'json'  ,
            data: {
                page     : filter.page     ,
                limit    : filter.limit    ,
                search   : filter.search   ,
                tag      : filter.tag      ,
                creator  : filter.creator  ,
                order_by : filter.order_by ,
                response : filter.response
            },
            headers: {
                'usertoken': user_token
            },
            success: function (data) {
            },
        }).always( function ( jqXHR ,textStatus ,xhr ) {
            if ( xhr.status === 200   ){
                $(document).find('main.main-container .ask').replaceWith( jqXHR.result );
                replace_url_in_single( whole );
                show_enable_filter();
            }
            hamfy_loader( false );
        });
    }



    $(document).on('click' , '.ask-profile-item div' , function (){
        let $this = $(this);
        let menu = $this.attr('class');
        let name = $this.data('name');
        $('.ask-profile-item svg').css( 'fill' ,'#000' );
        $this.children('svg').css('fill' , '#f22d33');
        $(document).find('.ask-profile-con>h4').text(name);
        $(document).find('.profile-list-con>div').hide();
        $(document).find('#'+menu).show();
    });


    $(document).on('click' , '#ask-sidebar-handler' , function (){
        $(document).find('.ask-profile-root').addClass('open');
        $(document).find('.ask-sidbar-closer').addClass('open');
    });


    $(document).on('click','.ask-sidbar-closer' , function(){
        $(document).find('.ask-profile-root').removeClass('open');
        $(this).removeClass('open');
    });


    $(document).on('click' , '.ask-profile-question-con p' , function (){
        let $this = $(this);
        let menu = $this.attr('class');
        $(document).find('.ask-profile-question-con p').css('backgroundColor' , '#888');
        $this.css('backgroundColor' , 'f22d33');
        $(document).find('.ask-profile-content>div').hide();
        $(document).find('#'+menu).show();
    });


    $(document).on('click' , 'body.logged-in .ask-bookmark' , function (){
        let $this    = $(this);
        let ask_id   = $this.data('ask-id');
        let svg      = $this.html();
        let status   = $this.data('booked-status');
        let book_msg = {'title' : status ? 'آیا از لیست نشانک های شما حذف شود ؟؟' : 'آیا به لیست نشانک های شما اضافه شود ؟؟' ,
                        'question' : status ? 'حذف از نشانک' : 'افزودن به نشانک'  }
        iziToast.question({
            timeout: 20000,
            close: false,
            overlay: true,
            zindex: 99999,
            title: book_msg.title,
            position: 'center',
            rtl: true,
            buttons: [
                ['<button><b>'+ book_msg.question +'</b></button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    $this.html( btn_loader );
                    $.ajax({
                        url      : ask_rest_url  ,
                        method   : 'PUT'     ,
                        dataType : 'json'    ,
                        data: {
                            action : 'add_to_bookmark_list' ,
                            ask_id : ask_id
                        },
                        headers: {
                            'usertoken': user_token
                        },
                        success: function (data) {
                        },
                    }).always(function (jqXHR, textStatus, xhr) {
                        if ( xhr.status === 200  ){
                            $this.html( svg );

                            if ( jqXHR.result === 'added' ){
                                $this.children( 'svg' ).css('fill' , '#f22d33');
                                $this.data('booked-status' , true );
                            }else{
                                $this.children( 'svg' ).css('fill' , '#A7A7A7');
                                $this.data('booked-status' , false );
                            }
                            get_profile();
                        }else {
                            $this.html( svg );
                            iziToast.info({
                                title: 'خطا!!',
                                message:  jqXHR.status,
                                position: 'topRight',
                                rtl: true
                            });
                        }
                    });
                }, true],
                ['<button>خیر</button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                }],
            ],
            onClosing: function(instance, toast, closedBy){
            },
            onClosed: function(instance, toast, closedBy){
            }
        });

    });


    function update_ask_views( askID ){

        if ( askID && askID > 0 ){
            $.ajax({
                url: ask_rest_url ,
                method: 'PUT'     ,
                dataType: 'json'  ,
                data: {
                    action  : 'update_view_count'  ,
                    ask_id  :askID
                },
                headers: {
                    'usertoken': user_token
                },
                success: function (data) {
                },
            }).always(function ( jqXHR ,textStatus ,xhr ) {
            });
        }
    }
    update_ask_views( parseInt( $(document).find('.ask').data('ask-id') ) );


    function hamfy_loader( status ) {
        let  body = $('body') ;
        if ( status === true ){
            body.addClass('ask_loader');
        }else{
            body.removeClass('ask_loader');
        }
    }

    let home = false;
    window.onpopstate = function ( event) {
        let href = event.target.location.href;
        if ( href ){
            hamfy_loader( true );
            if ( href == ask_home_url+'/ask/' ){
                get_main_page();
                home = false;
            }else if ( href == ask_home_url+'/ask/new/' ){
                get_new_ask_data();
            }else {
                let ask_id = href.split('/');
                if ( !isNaN( parseInt( ask_id[4] ) ) && home == false ){
                    get_single_page( ask_id[4] );
                    home = true;
                }else {
                    get_main_page();
                }
            }
        }
    }


    function filter_translate( keyword ){
        let translated = '';
        switch ( keyword ){
            case 'search':
                translated = 'جستجو';
                break;
            case 'tag':
                translated = 'هشتگ';
                break;
            case 'creator':
                translated = 'سازنده';
                break;
            case 'order_by':
                translated = 'ترتیب نمایش';
                break;
            case 'response':
                translated = 'فیلتر پاسخ';
                break;
        }
        return translated;
    }

    function orderby_translate( keyword ){
        let translated = '';
        switch ( keyword ){
            case 'created_date ASC':
                translated = 'جدیدترین ';
                break;
            case 'created_date DESC':
                translated = 'قدیمیترین';
                break;
            case 'likes DESC':
                translated = 'محبوبترین';
                break;
            case 'reply_count ASC':
                translated = 'بیشسترین پاسخ';
                break;
            case 'reply_count DESC':
                translated = 'کمترین پاسخ';
                break;
        }
        return translated;
    }

    function reply_translate( keyword ){
        let translated = '';
        switch ( keyword ){
            case 'all':
                translated = 'تمامی پاسخ ها ';
                break;
            case 'has':
                translated = 'پاسخدار ها';
                break;
            case 'no':
                translated = 'بدون پاسخ ها';
                break;
        }
        return translated;
    }

    function filter_default_value( key ){
        switch ( key ){
            case 'search':
                filter.search = '';
                break;
            case 'tag':
                filter.tag = '';
                break;
            case 'creator':
                filter.creator = '';
                break;
            case 'order_by':
                filter.order_by = 'created_date ASC';
                break;
            case 'response':
                filter.response = 'all';
                break;
        }
    }


    $(document).on('click' , '.ask-filter-show-con svg' , function (){
        hamfy_loader(true );
        filter_default_value( $(this).parent().data('which-filter') );
        get_main_page();
    });

    function show_enable_filter(){
        let filter_section_status = false;
        let filter_item_enable    = '';

        for ( const [key, values] of Object.entries( filter ) ) {
            if ( key !== 'limit' && key !== 'page' && values != '' &&
                 values !='all' && values !='created_date ASC'  ){
                filter_section_status = true;
                let translate = values;
                if ( key === 'order_by' ){
                    translate = orderby_translate( values );
                }else if( key === 'response' ){
                    translate = reply_translate( values );
                }
                addQueryStringParameter( key, values );
                filter_item_enable +=
                    '<div class="item '+key+'" data-which-filter="'+key+'" >' +
                    '    <svg class="filter-remover" x="0px" y="0px" width="10" height="10" viewBox="0 0 455.111 455.111" >\n' +
                    '        <circle style="fill:#E24C4B;" cx="227.556" cy="227.556" r="227.556"/>\n' +
                    '        <path style="fill:#D1403F;" d="M455.111,227.556c0,125.156-102.4,227.556-227.556,227.556c-72.533,0-136.533-32.711-177.778-85.333\n' +
                    '            c38.4,31.289,88.178,49.778,142.222,49.778c125.156,0,227.556-102.4,227.556-227.556c0-54.044-18.489-103.822-49.778-142.222\n' +
                    '            C422.4,91.022,455.111,155.022,455.111,227.556z"/>\n' +
                    '        <path style="fill:#FFFFFF;" d="M331.378,331.378c-8.533,8.533-22.756,8.533-31.289,0l-72.533-72.533l-72.533,72.533\n' +
                    '            c-8.533,8.533-22.756,8.533-31.289,0c-8.533-8.533-8.533-22.756,0-31.289l72.533-72.533l-72.533-72.533\n' +
                    '            c-8.533-8.533-8.533-22.756,0-31.289c8.533-8.533,22.756-8.533,31.289,0l72.533,72.533l72.533-72.533\n' +
                    '            c8.533-8.533,22.756-8.533,31.289,0c8.533,8.533,8.533,22.756,0,31.289l-72.533,72.533l72.533,72.533\n' +
                    '            C339.911,308.622,339.911,322.844,331.378,331.378z"/>\n' +
                    '    </svg>' +
                    '    <p> '+ filter_translate( key ) +' :' +
                    '        <span> '+  translate +' </span>\n' +
                    '    </p>' +
                    '</div>';
            }else{
                $(document).find('.ask-filter-show-con .'+key ).remove();
                if ( key !== 'limit' && key !== 'page' ){
                    removeQueryStringParameter( key );
                }
            }
        }

        if ( filter_section_status === false ){
            $(document).find('.ask-filter-show-con').hide();
        }else{
            $(document).find('.ask-filter-show-con').show();
            $(document).find('.ask-filter-show-con .items').html( filter_item_enable );
        }
    }
    show_enable_filter();


    function addQueryStringParameter( key ,value ) {
        let uri = window.location.href;
        let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        let separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if ( uri.match( re ) ) {
            uri = uri.replace( re, '$1' + key + "=" + value + '$2');
        }else{
            uri = uri + separator + key + "=" + value;
        }
        history.pushState(  'all' , "All Asks", uri );
    }


    function removeQueryStringParameter( parameter ) {
        let url = window.location.href;
        let urlparts = url.split('?');
        if (urlparts.length >= 2) {

            let prefix = encodeURIComponent( parameter ) + '=';
            let pars = urlparts[1].split(/[&;]/g);
            for (var i = pars.length; i-- > 0;) {
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }
            history.pushState(  'all' , "All Asks",  urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '')  );
        }
    }


    function replace_url_in_single( has_class ){
        if ( has_class ){
            history.pushState(  'all' , "All Asks", ask_home_url+'/ask/' );
        }
    }



    function reloadPrism(){
        Prism.highlightAll();
    }
    reloadPrism();


    $(document).on('click' , '.not-loggedin' , function ( e ){
        e.preventDefault();
        hfl_show_login_form( this ,' ' );
    });

    $(document).on('click' ,'.ask-search-con svg' , function (){
        let $this = $(this);
        if ( $this.hasClass('openned') ){
            let input_value = $this.siblings('input').val();
            if ( input_value.length > 0 ){
                hamfy_loader( true );
                filter.search = input_value
                get_main_page();
            }else {
                $this.siblings('input').css('width' , '1px');
                $this.parent().removeClass('search-border');
                $(document).find('.main-filter-con').slideUp();
                $this.removeClass('openned');
            }
        }else {
            $this.siblings('input').css('width' , '300px');
            $this.parent().addClass('search-border');
            $(document).find('.main-filter-con').slideDown();
            $this.addClass('openned');
        }
    });



    function close_serach_box(){
        $(document).find('.ask-search-con input').css('width' , '1px');
    }



})






