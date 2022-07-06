
jQuery(function ($) {

    const ask_object    = hamfy_ask_objects;
    const admin_url     = ask_object.admin_url;
    const ask_nonce     = ask_object.nonce;
    const rest_url      = ask_object.root;
    const user_token    = ask_object.user_token;





    const add_tag  = '<li class="ask-tag-action">\n' +
                     '    <span class="add-tag-element">*</span>\n' +
                     '    <input type="text" placeholder="عنوان" id="ask-save-new-tag-input">\n' +
                     '    <button id="ask-add-tag-to-lists"> ذخیره </button>\n' +
                     '</li>';

    const tag_item = '<li data-tag-name="[tag-name]" >\n' +
                     '    <span>*</span>\n' +
                     '    <p>[item]</p>\n' +
                     '</li>';

    const btn_loader ='<div class="loadingio-spinner-eclipse-9d1xzg08wno">' +
                        '<div class="ldio-vfpy9yjhvcs">\n' +
                            '<div></div>\n' +
                        '</div>' +
                     '</div>'
    const ask_loader ='<svg style="margin: auto; background: rgb(255, 255, 255); display: block; shape-rendering: auto;" width="204px" height="204px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">\n' +
                    '    <path d="M6 50A44 44 0 0 0 94 50A44 46 0 0 1 6 50" fill="#4b4949" stroke="none">\n' +
                    '      <animateTransform attributeName="transform" type="rotate" dur="0.12033694344163658s" repeatCount="indefinite" keyTimes="0;1" values="0 50 51;360 50 51"></animateTransform>\n' +
                    '    </path>\n' +
                    ' </svg>';
    let add_tag_element_status   = false;





    let tags_selector = $(document).find('.ask-new-tag');
    if ( tags_selector.length ){
        tags_selector.find('select').selectize({
            persist: false,
            createOnBlur: true,
            create: true
        });
    }


    $(document).on('click' , '.ask-tag-container li span ' , function (){
        $(this).parent().remove();
        if ( $(this).hasClass( 'add-tag-element' ) ){
            add_tag_element_status = false;
        }
    });

    $(document).on('click' , '.ask-save-tags-btn button.add' , function (){
        if ( !add_tag_element_status ){
            $(document).find('.ask-tag-container ul').append( add_tag );
            add_tag_element_status = true;
        }
    });

    $(document).on('click' , '#ask-add-tag-to-lists' , function (){
        let $this = $(this);
        let new_tag = $this.siblings('input').val();
        let new_item  = tag_item.replace( "[item]" , new_tag );
            new_item  = new_item.replace( "[tag-name]" , new_tag );
        $(document).find('.ask-tag-container ul').append( new_item );
        $this.parent().remove();
        add_tag_element_status = false;
    });


    $(document).on('click' , '.ask-save-tags-btn button.update' , function (){

        let items = [];
        let $this = $(this);
        if ( confirm('ذخیره شود ؟؟') )  {
            $this.html(btn_loader);
            $(document).find('.ask-tag-container ul li').each(function (index, element) {
                items.push( $(element).data('tag-name') );
            });
            $.ajax({
                url      : admin_url ,
                method   : 'POST'    ,
                data: {
                    action    : 'ask_update_tags',
                    tags_item : items ,
                    nonce : ask_nonce
                },
                success: function (data) {
                    if ( data.result !== 200 ){
                        iziToast.error({
                            title: 'خطا!!',
                            message:  'خطا هنگام بارگیری محتوا',
                            position: 'topRight',
                            rtl: true
                        });
                    }else {
                        $this.html('ذخیره تغییرات ');
                    }
                },
            }).always(function (jqXHR, textStatus, jqXHR2) {

            });

        }
    });

    $(document).on('click' , '.ask-list-item li' , function () {
        let id = '.'+$(this).attr('id');
        $(document).find('.ask-content section').hide();
        $(document).find(id).show();
    });

    $(document).on('click' , '#ask-message-tab li' , function () {
        let id = '.'+$(this).attr('id');
        $(document).find('#ask-message-tab li').removeClass('active');
        $(this).addClass('active');
        $(document).find('.ask-message-content>div').hide();
        $(document).find(id).show();
    });

    function get_dashboard(){
        $.ajax({
            url      : admin_url  ,
            method   : 'POST'     ,
            dataType : 'html'     ,
            data: {
                action :'ask_get_dashboard' ,
                nonce  : ask_nonce
            },
            success: function (data) {
            },
        }).always(function ( jqXHR, textStatus, xhr ) {
            if ( xhr.status === 200  ){
                $(document).find('.ask-dashboard-con').html( jqXHR );
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

    $(document).on('click' , '.btn-container .reject' , function (){
        reject( $(this) );
    });

    async function reject( $this ){
        let reject_text = '';

        iziToast.info({
            timeout: 20000,
            overlay: true,
            displayMode: 'once',
            id: 'inputs',
            zindex: 999,
            position: 'center',
            drag: false,
            inputs: [
                ['<input type="text">', 'change', function ( instance, toast, input, e) {
                    reject_text = input.value;
                }]
            ],
            buttons: [
                ['<button><b>ذخیره</b></button>', function ( instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    $this.html( btn_loader );
                    $this.addClass('active');
                    let id = $this.data('id');
                    $.ajax({
                        url      : rest_url  ,
                        method   : 'PUT'     ,
                        dataType : 'json'    ,
                        data: {
                            action  : 'ask_reject_question' ,
                            id      : id                    ,
                            comment : reject_text
                        },
                        headers: {
                            'usertoken': user_token
                        },
                        success: function (data) {
                        },
                    }).always(function (jqXHR, textStatus, xhr) {

                        if ( xhr.status === 200  ){
                            get_dashboard();
                        }else {
                            iziToast.error({
                                title: 'خطا!!',
                                message:  jqXHR.status,
                                position: 'topRight',
                                rtl: true
                            });
                            $this.html('رد کردن');
                        }
                    });
                }, true]
            ]
        });
    }

    $(document).on('click' , '.btn-container .accept' , function (){

        let $this = $(this);

        if ( confirm('پذیرفته شود ؟؟') ) {

            $this.html( btn_loader );
            $this.addClass('active');
            let id = $this.data('id');

            $.ajax({
                url      : rest_url  ,
                method   : 'PUT'     ,
                dataType : 'json'    ,
                data: {
                    action : 'ask_accept_question' ,
                    id     : id
                },
                headers: {
                    'usertoken': user_token
                },
                success: function (data) {
                },
            }).always(function ( jqXHR, textStatus, xhr ) {

                if ( xhr.status === 200  ){
                    get_dashboard();
                }else {
                    iziToast.error({
                        title: 'خطا!!',
                        message:  jqXHR.status,
                        position: 'topRight',
                        rtl: true
                    });
                    $this.html('پذیرفتن');
                }
            });
        }
    });

    $(document).on('click' , '.btn-container .edit' , function (){
        let $this = $(this);
        let id = $this.data('id');
        $this.siblings('div#edit-board').show();
        if ( window.editor ){
            window.editor.destroy();
        }
        ClassicEditor
        .create( document.querySelector( '#ask-editor-edit-'+id ), {
            toolbar: {
                items: [
                    'heading',
                    'bold',
                    'link',
                    'bulletedList',
                    '|',
                    'alignment',
                    '|',
                    'specialCharacters',
                    'codeBlock',
                    '|'
                ]
            },
            licenseKey: '',
        } )
        .then(  editor => {
            window.editor = editor;
            $(document).find('.ask-new-tag').selectize();
        } )
        .catch( error => {
            console.error( 'Oops, something went wrong!'+ error );
        });

    });

    $(document).on('click' , '.ask-edit-cancel button' , function (){
        $(this).parent().parent().parent().hide();
    });


    $(document).on('click' , '.edit-save .update' , function ( e ){
        e.preventDefault();
        let $this    = $(this);
        let id       = $this.data('id');
        let form     = '#form-'+id;
        let items    = [];
        let content  = editor.getData();
        let accept   = $(this).hasClass('accept-plus');
        form         = $(form);
        let title    = form.find('#ask-edit-title').val();
        $this.html( btn_loader );
        $this.removeClass( 'update' );

        $(form).find('.ask-new-tag option').each( function ( index, element) {
            items.push( $(element).val() );
        });

        if ( title.length && content.length ) {
            $.ajax({
                url      : rest_url  ,
                method   : 'PUT'     ,
                dataType : 'json'    ,
                data: {
                    action   : 'ask_update_question' ,
                    id       : id      ,
                    title    : title   ,
                    content  : content ,
                    tags     : items   ,
                    accept   : accept
                },
                headers: {
                    'usertoken': user_token
                },
                success: function (data) {
                },
            }).always(function (jqXHR, textStatus, xhr) {
                if ( xhr.status === 200  ){
                    get_dashboard();
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

    $(document).on('click' , '.edit-save .update-reply' , function ( e ){
        e.preventDefault();
        let $this    = $(this);
        let id       = $this.data('id');
        let content  = editor.getData();
        let accept   = $(this).hasClass('accept-plus');
        $this.html( btn_loader );
        $this.removeClass( 'update' );

        if ( content.length ) {
            $.ajax({
                url      : rest_url  ,
                method   : 'PUT'     ,
                dataType : 'json'    ,
                data: {
                    action   : 'ask_update_reply' ,
                    id       : id      ,
                    content  : content ,
                    accept   : accept
                },
                headers: {
                    'usertoken': user_token
                },
                success: function (data) {
                },
            }).always(function (jqXHR, textStatus, xhr) {
                if ( xhr.status === 200  ){
                    get_dashboard();
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




    $(document).on('click' , '.expand svg' , function (){
        let which   = $(this).attr('id');
        let content = $(this).parent().parent().attr('id');
        content    = '.bottom #content-'+content ;
        let para   = $(document).find(content);
        if ( which === 'top' ){
            para.slideUp();
            $(this).css('fill' , '#eee');
            $(this).siblings('svg').css('fill' , '#999');
        }else{
            para.slideDown();
            $(this).css('fill' , '#eee');
            $(this).siblings('svg').css('fill' , '#999');
        }
    });

    $(document).on('click' , '.parent-content-shower' , function ( e ){
        let $this = $(this);
        if( $this.is(e.target) ){
            $this.children('div').first().toggle();
        }
    });


    let get_all_ask_dashboard_status = true;
    function get_all_ask_dashboard( offset ){
        if ( get_all_ask_dashboard_status ){
            get_all_ask_dashboard_status = false;
            $(document).find('.ask-all-ask ul').html( ask_loader );
            $.ajax({
                url      : admin_url  ,
                method   : 'POST'     ,
                dataType : 'html'     ,
                data: {
                    action :'ask_get_all_ask_ui' ,
                    offset : offset ,
                    nonce  : ask_nonce
                },
                success: function (data) {
                },
            }).always(function ( jqXHR, textStatus, xhr ) {
                if ( xhr.status === 200  ){
                    $(document).find('.ask-all-ask ul').html( jqXHR );
                }else {
                    iziToast.error({
                        title: 'خطا!!',
                        message:  jqXHR.status,
                        position: 'topRight',
                        rtl: true
                    });
                }
                get_all_ask_dashboard_status = true;
            });
        }
    }

    let get_all_replies_dashboard_status = true;
    function get_all_replies_dashboard( offset ){
        if ( get_all_replies_dashboard_status ){
            get_all_replies_dashboard_status = false;
            $(document).find('.ask-all-replies ul').html( ask_loader );
            $.ajax({
                url      : admin_url  ,
                method   : 'POST'     ,
                dataType : 'html'     ,
                data: {
                    action :'ask_get_all_replies_ui' ,
                    offset : offset ,
                    nonce  : ask_nonce
                },
                success: function (data) {
                },
            }).always(function ( jqXHR, textStatus, xhr ) {
                if ( xhr.status === 200  ){
                    $(document).find('.ask-all-replies ul').html( jqXHR );
                }else {
                    iziToast.error({
                        title: 'خطا!!',
                        message:  'هنگام دریافت اطلاعات صفحه اصلی ',
                        position: 'topRight',
                        rtl: true
                    });
                }
                get_all_replies_dashboard_status = true;
            });
        }

    }



    let get_all_users_list_dashboard_status = true;
    function get_all_users_list_dashboard( offset ){
        if ( get_all_users_list_dashboard_status ){
            get_all_users_list_dashboard_status = false;
            $(document).find('.users-list').html( ask_loader );
            $.ajax({
                url      : admin_url  ,
                method   : 'POST'     ,
                dataType : 'html'     ,
                data: {
                    action :'ask_get_all_users_list_ui' ,
                    offset : offset ,
                    nonce  : ask_nonce
                },
                success: function (data) {
                },
            }).always(function ( jqXHR, textStatus, xhr ) {
                if ( xhr.status === 200  ){
                    $(document).find('.users-list').html( jqXHR );
                }else {
                    iziToast.error({
                        title: 'خطا!!',
                        message:  'هنگام دریافت اطلاعات صفحه اصلی ',
                        position: 'topRight',
                        rtl: true
                    });
                }
                get_all_users_list_dashboard_status = true;
            });
        }

    }


    $(document).on('click' , '#ask-all-ask' , function ( e ){
        get_all_ask_dashboard( 0 );
    });
    $(document).on('click' , '#ask-all-replies' , function ( e ){
        get_all_replies_dashboard( 0 );
    });
    $(document).on('click' , '.all-users-list' , function ( e ){
        get_all_users_list_dashboard( 0 );
    });


});