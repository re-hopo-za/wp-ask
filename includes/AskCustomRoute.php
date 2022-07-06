<?php
//is ok

namespace HWP_Ask\includes;


class AskCustomRoute
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
        self::addRewrite();
        add_filter('theme_page_templates', [ $this, 'addTemplate' ] , 10, 4);
        add_filter('template_include', [ $this, 'loadTemplate'] );
    }


    public function addTemplate( $post_templates, $wp_theme, $post, $post_type)
    {
        $post_templates['ask-template.php'] = 'Asks';
        return $post_templates;
    }


    public function loadTemplate($template){

        if (get_page_template_slug() === 'ask-template.php') {
            if ($theme_file = locate_template( ['ask-template.php'] )) {
                $template = $theme_file;
            } else {
                $template = HWP_PAGE_TEMPLATE . 'ask-template.php';
            }
        }
        return $template;
    }

    public static function addRewrite(){
        add_rewrite_rule('ask/([0-9]*)', 'index.php/ask?ask=$1');
    }


}


