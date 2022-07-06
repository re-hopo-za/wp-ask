<?php


namespace HWP_Ask\includes;



class AskUI{


    public static function all( $filter  ,$userID ,$callRest = false ){
        $all_asks = AskDB::get_instance()::getAll( $filter );
        if( $callRest ){
            ob_start();
        }
        ?>
        <div class="ask" >
            <section class="ask-root">
                <div class="ask-header-con">
                    <div class="ask-main-btn">
                        <a class="ask-create-new"
                           href="<?php echo home_url().'/ask/new'; ?>"
                           <?php echo AskFunctions::isUserLoggedIn( $userID ,'onclick="return false"');
                                 echo AskFunctions::isUserNotLoggedIn( $userID ,'click' ); ?>
                           id="<?php echo AskFunctions::isUserLoggedIn( $userID ,'ask-new');  ?>" >  سوال جدید</a>
                    </div>
                    <div class="ask-search-con">
                        <div>
                            <svg height="24" viewBox="0 0 24 24" width="24" >
                                <path fill="none" d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                                     stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                            <input type="search" style="width: 1px" id="ask-main-search" placeholder="جستجو بر اساس [ عنوان , تگ ,  متن ] ">
                        </div>
                    </div>
                </div>
                <div class="main-filter-con" style="<?php echo AskFunctions::isSetFilter( $filter ) ? '' : 'display: none'; ?>">
                    <div class="ask-extra-filter-con">
                        <div class="filter-con">
                            <div class="order-by">
                                <label for="order-by-select">
                                    ترتیب نمایش:
                                </label>
                                <select name="order-by-select" id="order-by-select">
                                    <option value="created_date ASC"> جدیدترین </option>
                                    <option value="created_date DESC"> قدیمیترین </option>
                                    <option value="likes DESC"> محبوبترین </option>
                                    <option value="reply_count ASC"> بیشسترین پاسخ </option>
                                    <option value="reply_count DESC"> کمترین پاسخ </option>
                                </select>
                            </div>
                            <div class="ask-response-filter">
                                <label for="ask-response-input">
                                    فیلتر پاسخ :
                                </label>
                                <select name="ask-response-input" id="ask-response-input">
                                    <option value="all">  تمامی پاسخ ها </option>
                                    <option value="has">پاسخ دار ها </option>
                                    <option value="no"> بدون پاسخ ها  </option>
                                </select>
                            </div>
                        </div>
                        <div class="ask-filter-show-con" >
                            <div class="title">
                                <button  id="ask-refresh" > حذف تمامی فیلتر ها </button>
                            </div>
                            <div class="items">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ask-content-con">
                    <ul>
                        <?php
                        if ( !empty( $all_asks['loop'] ) && $all_asks['loop'] !== 404 ){
                            foreach ( $all_asks['loop'] as $item ){
                            $item = (object) $item;
                            ?>
                            <li id="<?php echo $item->id; ?>" >
                                <div class="right">
                                    <div class="ask-reply-con"><span><?php echo AskDB::get_instance()::replyCount( $item->id ); ?></span> <span>پاسخ</span> </div>
                                    <div class="ask-views-con"><span><?php echo $item->views; ?></span> <span>رویت</span></div>
                                </div>
                                <div class="left">
                                    <div class="top">
                                        <div class="content">
                                            <div>
                                                <a onclick="return false;" class="ask-item-link" data-ask-id="<?php echo $item->id; ?>" href="<?php echo home_url().'/ask/'.$item->id; ?>">
                                                    <?php echo strip_tags( $item->title ); ?>
                                                </a>
                                            </div>
                                            <p>
                                                <?php echo $item->content; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="bottom">
                                        <div class="tags">
                                            <?php echo $item->tags; ?>
                                        </div>
                                        <div class="extra-data">
                                            <div class="profile">
                                                <div class="profile-img">
                                                    <img src="<?php echo $item->profile_image ?>" alt="<?php echo $item->profile_image ?>">
                                                </div>
                                                <div class="profile-details">
                                                    <a onclick="return false;" data-creator="<?php echo $item->creator_id; ?>" href="https://hamyar.co/ask/?creator=<?php echo $item->creator_id; ?>"><?php echo $item->creator; ?></a>
                                                    <span>
                                                        <i>
                                                            <?php echo $item->created_time; ?>
                                                        </i>
                                                          گذشته
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <?php
                            }
                        }else{
                        ?>
                            <li class="empty-content">
                                <div>
                                    <p>سوالی یافت نشد</p>
                                </div>
                                <div>
                                    <svg height="65" viewBox="0 -35 512 512" width="65" xmlns="http://www.w3.org/2000/svg">
                                        <path d="m442 232.910156v-205.410156c0-15.164062-12.335938-27.5-27.5-27.5h-387c-15.164062 0-27.5 12.335938-27.5 27.5v311c0 15.164062 12.335938 27.5 27.5 27.5h39.167969c4.140625 0 7.5-3.359375 7.5-7.5s-3.359375-7.5-7.5-7.5h-39.167969c-6.894531 0-12.5-5.605469-12.5-12.5v-270.5h412v160.472656c-7.414062-1.613281-15.109375-2.472656-23-2.472656-20.28125 0-39.269531 5.621094-55.5 15.386719v-84.386719c0-4.140625-3.355469-7.5-7.5-7.5s-7.5 3.359375-7.5 7.5v53h-42.5c-1.378906 0-2.5-1.121094-2.5-2.5v-50.5c0-4.140625-3.355469-7.5-7.5-7.5s-7.5 3.359375-7.5 7.5v50.5c0 9.648438 7.851562 17.5 17.5 17.5h42.5v26.695312c0 .179688.015625.355469.027344.53125-19.871094 17.152344-33.441406 41.402344-36.742188 68.773438h-251.785156v-223h352v100.5c0 4.140625 3.355469 7.5 7.5 7.5s7.5-3.359375 7.5-7.5v-103c0-6.894531-5.605469-12.5-12.5-12.5h-357c-6.894531 0-12.5 5.605469-12.5 12.5v228c0 6.894531 5.605469 12.5 12.5 12.5h253.523438c.09375 5.09375.539062 10.101562 1.320312 15h-195.675781c-4.144531 0-7.5 3.359375-7.5 7.5s3.355469 7.5 7.5 7.5h199.171875c13.671875 43.976562 54.746094 76 103.160156 76 59.550781 0 108-48.449219 108-108 0-46.183594-29.140625-85.683594-70-101.089844zm-427-179.910156v-25.5c0-6.894531 5.605469-12.5 12.5-12.5h387c6.894531 0 12.5 5.605469 12.5 12.5v25.5zm389 374c-51.28125 0-93-41.71875-93-93s41.71875-93 93-93 93 41.71875 93 93-41.71875 93-93 93zm0 0"/>
                                        <path d="m44 25c-4.964844 0-9 4.039062-9 9s4.035156 9 9 9 9-4.039062 9-9-4.035156-9-9-9zm0 0"/>
                                        <path d="m82 25c-4.964844 0-9 4.039062-9 9s4.035156 9 9 9 9-4.039062 9-9-4.035156-9-9-9zm0 0"/>
                                        <path d="m120 25c-4.964844 0-9 4.039062-9 9s4.035156 9 9 9 9-4.039062 9-9-4.035156-9-9-9zm0 0"/>
                                        <path d="m161 270.5c4.144531 0 7.5-3.359375 7.5-7.5v-106c0-4.140625-3.355469-7.5-7.5-7.5s-7.5 3.359375-7.5 7.5v53h-42.5c-1.378906 0-2.5-1.121094-2.5-2.5v-50.5c0-4.140625-3.355469-7.5-7.5-7.5s-7.5 3.359375-7.5 7.5v50.5c0 9.648438 7.851562 17.5 17.5 17.5h42.5v38c0 4.140625 3.355469 7.5 7.5 7.5zm0 0"/>
                                        <path d="m201 150c-9.648438 0-17.5 7.851562-17.5 17.5v85c0 9.648438 7.851562 17.5 17.5 17.5h40c9.648438 0 17.5-7.851562 17.5-17.5v-85c0-9.648438-7.851562-17.5-17.5-17.5zm42.5 17.5v85c0 1.378906-1.121094 2.5-2.5 2.5h-40c-1.378906 0-2.5-1.121094-2.5-2.5v-85c0-1.378906 1.121094-2.5 2.5-2.5h40c1.378906 0 2.5 1.121094 2.5 2.5zm0 0"/>
                                        <path d="m221 187.5c-4.144531 0-7.5 3.359375-7.5 7.5v30c0 4.140625 3.355469 7.5 7.5 7.5s7.5-3.359375 7.5-7.5v-30c0-4.140625-3.355469-7.5-7.5-7.5zm0 0"/>
                                        <path d="m360.5 306.175781c-4.144531 0-7.5 3.359375-7.5 7.5v23c0 4.140625 3.355469 7.5 7.5 7.5s7.5-3.359375 7.5-7.5v-23c0-4.140625-3.355469-7.5-7.5-7.5zm0 0"/>
                                        <path d="m447.5 306.175781c-4.144531 0-7.5 3.359375-7.5 7.5v23c0 4.140625 3.355469 7.5 7.5 7.5s7.5-3.359375 7.5-7.5v-23c0-4.140625-3.355469-7.5-7.5-7.5zm0 0"/>
                                        <path d="m404 347.175781c-11.722656 0-22.609375 5.289063-29.871094 14.507813-2.5625 3.253906-2 7.96875 1.253906 10.53125 3.253907 2.5625 7.972657 2.003906 10.53125-1.25 4.398438-5.585938 10.988282-8.789063 18.085938-8.789063s13.6875 3.203125 18.085938 8.789063c1.480468 1.878906 3.679687 2.859375 5.898437 2.859375 1.621094 0 3.257813-.523438 4.632813-1.605469 3.253906-2.5625 3.816406-7.277344 1.253906-10.535156-7.257813-9.21875-18.144532-14.507813-29.871094-14.507813zm0 0"/>
                                    </svg>
                                </div>
                                <div>
                                    <a href=https://reza.test/ask/new" id="ask-new" onclick="return false;" > ساختن سوال </a>
                                </div>
                            </li>
                       <?php } ?>
                    </ul>
                </div>


                <div class="ask-footer-con">
                    <?php if ( AskDB::get_instance()::replyCount( $item->id ) > 15 ){ ?>
                        <div class="pagination-con">
                            <?php echo $all_asks['main']['pagination'] ; ?>
                        </div>
                        <div class="limit-con">
                            <a href="<?php echo home_url() ; ?>/ask"  data-limit="15" onclick="return false;">15</a>
                            <a href="<?php echo home_url() ; ?>/ask"  data-limit="30" onclick="return false;">30</a>
                            <a href="<?php echo home_url() ; ?>/ask"  data-limit="60" onclick="return false;">60</a>
                            <span>در هر صفحه</span>
                        </div>
                    <?php } ?>
                </div>
            </section>
        </div>
        <div class="ask-sidbar-closer"></div>
        <div class="ask-profile-root"> <?php self::profile( $userID ); ?> </div>
        <?php

        if( $callRest ){
            return ob_get_clean();
        }

    }


    public static function single( $askId ,$userID ,$callRest = false ){
        $single  = AskDB::get_instance()::getSingle( $askId ,$userID );
        if ( $single !== 404 ){
            $main    = (object) $single['main'];
            $owner   = $main->creator_id == $userID;

            if( $callRest ){
                ob_start();
            }
            ?>
            <div class="ask" data-ask-id ="<?php echo $main->id; ?>" >
                <section class="ask-single-row">
                    <div class="ask-single-btn">
                        <a class="ask-create-new" href="<?php echo home_url().'/ask/new'; ?>" id="ask-new" onclick="return false;">سوال  جدید</a>
                        <a class="ask-all-questions-btn" id="ask-home" onclick="return false;"> تمامی سوالات </a>
                    </div>
                    <div class="ask-single-header-con">
                        <div class="top">
                            <h2> <?php echo $main->title; ?></h2>
                        </div>
                        <div class="bottom">
                            <div class="profile-con">
                                <div class="ask-single-reply-profile">
                                    <div class="reply-profile-con">
                                        <img src="<?php echo $main->profile_image; ?>" alt="profile image">
                                        <div>
                                            <a onclick="return false;" ><?php echo $main->creator; ?>  </a>
                                            <p> <?php echo $main->created_time; ?> </p>
                                        </div>
                                    </div>
                                    <div class="reply-bottom-left-action">
                                        <?php if ( !$owner ) { ?>
                                            <div class="<?php echo AskFunctions::isUserLoggedIn( $userID ,'ask-bookmark'); ?>" <?php echo AskFunctions::isUserNotLoggedIn( $userID ); ?>
                                                 data-ask-id="<?php echo $main->id; ?>" data-booked-status="<?php echo AskProcess::checkBookmark( $main->id ,$userID ); ?>" >
                                                <svg x="0px" y="0px" fill="<?php echo AskProcess::checkBookmark( $main->id ,$userID ) ? '#f22d33' : '#A7A7A7' ?>"
                                                     viewBox="0 0 490.6 490.6" >
                                                    <g>
                                                        <path d="M393,0H97.6c-14,0-25.3,11.3-25.3,25.3v332.6c0,11.5,5.6,22.2,15,28.8l140.8,98.5c10.3,7.2,24.1,7.2,34.4,0l140.8-98.5
                                                                c9.4-6.6,15-17.3,15-28.8V25.3C418.3,11.3,407,0,393,0z M351.5,189.9l-48.1,40.9l15,61.5c2.8,11.6-9.7,20.8-19.8,14.5l-53.3-33.5
                                                                l-53.4,33.4c-10.1,6.3-22.6-2.9-19.8-14.5l15-61.5L139,189.8c-9-7.7-4.3-22.6,7.6-23.4l62.7-4.6l23.7-58.7c4.5-11,20-11,24.5,0
                                                                l23.7,58.7l62.7,4.6C355.8,167.3,360.6,182.2,351.5,189.9z"/>
                                                    </g>
                                                </svg>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="ask-hr">
                    <div class="single-content-con">
                        <div class="ask-content-text" <?php echo $owner ? 'width="100%!important;"' :''; ?> >
                            <?php echo $main->content; ?>
                        </div>
                        <?php if (!empty( $main->tags ) ){ ?>
                            <div class="tags">
                                <?php echo $main->tags; ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="extra-details-single">
                        <p><b> <?php echo AskDB::get_instance()::replyCount( $askId ); ?> </b> پاسخ </p>
                        <h5> لیست پاسخ ها </h5>
                    </div>
                    <div class="ask-reply-con">
                        <div class="ask-replies-con">
                            <div id="ask-reply-replies">
                                <ul>
                                    <?php
                                    if ( $single['replies'] != 404  ){
                                        $replies = (object) $single['replies'];

                                        foreach ( $replies as $reply ){
                                        $reply        = (object) $reply;
                                        $owner_reply  = $reply->creator_id == $userID;
                                    ?>
                                    <li id="<?php echo $reply->id; ?>">
                                        <div class="single-content-con">
                                            <div class="ask-content-text" >
                                                <?php echo $reply->content; ?>
                                            </div>
                                        </div>
                                        <div class="profile-con">
                                            <div class="ask-single-reply-profile">
                                                <div class="reply-profile-con">
                                                    <img src="<?php echo $reply->profile_image ?>" alt="<?php echo $reply->profile_image ?>">
                                                    <div>
                                                        <a href="<?php echo home_url().'/ask?creator='.$reply->creator_id; ?>" onclick="return false;"
                                                           data-user-id="<?php echo $reply->creator_id; ?>" ><?php echo $reply->creator; ?> </a>
                                                        <p>
                                                            <i> <?php echo $reply->created_time; ?> </i>
                                                            پیش </p>
                                                    </div>
                                                </div>
                                                <div class="reply-bottom-left-action">
                                                    <div class="ask-action-con">
                                                    <?php if ( !$owner_reply ) { ?>
                                                        <div class="ask-svg-con">
                                                            <div class="<?php echo AskFunctions::isUserLoggedIn( $userID ,'ask-arrow-up   up_'.$main->id );?> " >
                                                                <svg x="0px" y="0px" data-ask-id="<?php echo $reply->id; ?>"
                                                                     viewBox="0 0 213.333 213.333" id="<?php echo 'down_'.$reply->id; ?>"
                                                                     fill="<?php echo $reply->like_svg_fill; ?>"
                                                                     class="<?php echo $reply->like_svg_class; ?> " <?php echo AskFunctions::isUserNotLoggedIn( $userID ); ?>  >
                                                                    <g>
                                                                        <g>
                                                                            <polygon points="106.667,53.333 0,160 213.333,160 "/>
                                                                        </g>
                                                                    </g>
                                                                </svg>
                                                            </div>
                                                            <div class="<?php echo AskFunctions::isUserLoggedIn( $userID ,'ask-arrow-down down_'.$main->id ) ; ?>" >
                                                                <svg  xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" data-ask-id="<?php echo $reply->id; ?>"
                                                                      viewBox="0 0 213.333 213.333" xml:space="preserve" id="<?php echo 'up_'.$reply->id; ?>"
                                                                      fill="<?php echo $reply->dislike_s_fill; ?>"
                                                                      class="<?php echo $reply->dislike_class; ?> " <?php echo AskFunctions::isUserNotLoggedIn( $userID ); ?>>
                                                                    <g>
                                                                        <g>
                                                                            <polygon points="0,53.333 106.667,160 213.333,53.333 "/>
                                                                        </g>
                                                                    </g>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                        <output> <?php echo $reply->ask_likes; ?></output>
                                                        <span> امتیاز</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <?php }
                                    }else{
                                        echo '<li class="empty"> پاسخی ثبت نشده </li>';
                                    } ?>
                                </ul>
                            </div>
                        </div>
                        <?php
                            if( $userID > 0 ){
                        ?>
                            <div class="ask-reply-editor-con" id="ask-reply-editor-con" data-parent-id="<?php echo $main->id ?> ">
                                <div class="reply-editor-header-con">
                                    <h5 class="your-reply"> پاسخ شما </h5>
                                    <div class="char-counter-con">
                                        <p>Characters : <span class="demo-update__words"> </span></p>
                                    </div>
                                </div>
                                <div id="ask-reply-editor">
                                </div>
                                <div class="editor-botton-container">`
                                    <div class="ask-reply-submit">
                                        <button class="<?php echo AskFunctions::isUserLoggedIn( $userID ,'ask-create-new-reply' );  ?>" <?php echo AskFunctions::isUserNotLoggedIn( $userID ); ?> > ثبت پاسخ </button>
                                    </div>
                                </div>
                            </div>
                        <?php
                            }else{ ?>
                                 <div class="not-loggedin-con">
                                    <a href="javascript:void(0);" class="not-loggedin">
                                        برای ثبت پاسخ وارد شوید
                                    </a>
                                 </div>
                                <?php
                            }
                        ?>
                    </div>
                </section>
            </div>
            <div class="ask-sidbar-closer"></div>
            <div class="ask-profile-root"> <?php self::profile( $userID ); ?> </div>
        <?php
            if( $callRest ){
                return ob_get_clean();
            }
        }else{
            AskFunctions::_404();
        }

    }


    public static function new( $userID ,$callRest = false ){
        if( $callRest ){
            ob_start();
        }
        ?>
        <div class="ask">
            <section class="ask-new-root">
                <div class="new-main-container ask-new-editor-con" >

                    <div class="header-con">
                        <h3> یک سوال جدید بسازید </h3>
                        <a id="ask-home" class="ask-all-questions-btn"> تمامی سوالات </a>
                    </div>

                    <div class="title">
                        <label for="title-input"> عنوان </label>
                        <input type="text" class="title-input" id="title-input" placeholder="برای دریافت پاسخ های متناسب بایستی از عنوان مشخصی استفاده شود ">
                    </div>
                    <div class="body">
                        <p> متن </p>
                        <textarea dir="ltr" id="ask-new-editor"></textarea>
                    </div>
                    <hr class="ask-hr">
                    <div class="tags">
                        <label for="select-unlocked"> هشتک </label>
                        <select id="select-unlocked" class="ask-new-tag" multiple placeholder=" وارد کردن تگ ">
                            <?php
                            $ask_options = maybe_unserialize( get_option('hamfy_ask_options' ) );
                            if ( !empty( $ask_options ) && is_array( $ask_options ) ){
                                $tags = $ask_options['tags'];
                                if ( !empty( $tags) && is_array( $tags )  ){
                                    foreach ( $tags as $tag ){
                                        echo '<option value="'.$tag.'" > 
                                                '.$tag.'
                                             </option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="demo-update__controls editor-botton-container">
                        <div class="char-counter-con">
                            <p>Characters : <span class="demo-update__words"> </span>  </p>
                        </div>
                        <div class="ask-new-submit">
                            <button class="ask-create-button-style ask-create-new-ask" <?php echo AskFunctions::isUserNotLoggedIn( $userID ) ?> > ثبت سوال </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="ask-sidbar-closer"></div>
        <div class="ask-profile-root"> <?php self::profile( $userID ); ?> </div>
        <?php
        if( $callRest ){
            return ob_get_clean();
        }
    }


    public static function dashboard()
    {
        ?>
        <div class="ask-dashboard-con">
            <div class="tabs">

                <input type="radio" id="tab1" name="tab-control" checked>
                <input type="radio" id="tab2" name="tab-control">
                <input type="radio" id="tab3" name="tab-control">
                <input type="radio" id="tab4" name="tab-control">
                <ul class="ask-list-item">
                    <li class="active" id="za-tab-1">
                        <label for="tab1" role="button">
                            <svg   xmlns="http://www.w3.org/2000/svg"  width="21" height="21"
                                 viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 511.999 511.999;" xml:space="preserve">
                                <g>
                                    <g>
                                        <path d="M113.304,396.177c-41.405-40.668-66.722-93.368-72.6-150.216c-22.006,27.818-33.938,61.937-33.883,97.745
                                            c0.037,23.29,5.279,46.441,15.212,67.376L1.551,470.689c-3.521,10.247-0.949,21.373,6.713,29.036
                                            c5.392,5.392,12.501,8.264,19.812,8.264c3.076,0,6.188-0.508,9.223-1.551l59.609-20.483c20.935,9.933,44.086,15.175,67.376,15.212
                                            c36.509,0.049,71.256-12.338,99.361-35.168C207.133,460.886,154.416,436.556,113.304,396.177z"/>
                                    </g>
                                </g>
                                <g>
                                    <g>
                                        <path d="M510.156,401.842L480.419,315.3c14.334-29.302,21.909-61.89,21.96-94.679c0.088-57.013-21.97-110.92-62.112-151.79
                                        C400.117,27.952,346.615,4.942,289.615,4.039C230.51,3.104,174.954,25.586,133.187,67.352
                                        C91.42,109.119,68.934,164.674,69.87,223.782c0.903,56.999,23.913,110.502,64.79,150.652
                                        c40.79,40.064,94.56,62.116,151.451,62.114c0.112,0,0.23,0,0.34,0c32.79-0.051,65.378-7.626,94.68-21.96l86.544,29.738
                                        c3.606,1.239,7.304,1.843,10.959,1.843c8.688,0,17.136-3.412,23.545-9.822C511.284,427.241,514.34,414.021,510.156,401.842z
                                         M307.004,295.328H195.331c-8.416,0-15.238-6.823-15.238-15.238c0-8.416,6.823-15.238,15.238-15.238h111.672
                                        c8.416,0,15.238,6.823,15.238,15.238C322.241,288.506,315.42,295.328,307.004,295.328z M376.892,232.659h-181.56
                                        c-8.416,0-15.238-6.823-15.238-15.238s6.823-15.238,15.238-15.238h181.56c8.416,0,15.238,6.823,15.238,15.238
                                        S385.308,232.659,376.892,232.659z M376.892,169.988h-181.56c-8.416,0-15.238-6.823-15.238-15.238
                                        c0-8.416,6.823-15.238,15.238-15.238h181.56c8.416,0,15.238,6.823,15.238,15.238C392.13,163.165,385.308,169.988,376.892,169.988z
                                        "/>
                                    </g>
                                </g>
                            </svg>
                            <br>
                            <span> پیام ها</span>
                        </label>
                    </li>
                    <li id="za-tab-2">
                        <label for="tab2" role="button">
                            <svg width="21" height="21" viewBox="0 0 24 24" >
                                <g>
                                    <path d="m9.25 0h-7.5c-.965 0-1.75.785-1.75 1.75v4.5c0 .965.785 1.75 1.75 1.75h7.5c.965 0 1.75-.785 1.75-1.75v-4.5c0-.965-.785-1.75-1.75-1.75z"/>
                                    <path d="m9.25 10h-7.5c-.965 0-1.75.785-1.75 1.75v10.5c0 .965.785 1.75 1.75 1.75h7.5c.965 0 1.75-.785 1.75-1.75v-10.5c0-.965-.785-1.75-1.75-1.75z"/>
                                    <path d="m22.25 16h-7.5c-.965 0-1.75.785-1.75 1.75v4.5c0 .965.785 1.75 1.75 1.75h7.5c.965 0 1.75-.785 1.75-1.75v-4.5c0-.965-.785-1.75-1.75-1.75z"/>
                                    <path d="m22.25 0h-7.5c-.965 0-1.75.785-1.75 1.75v10.5c0 .965.785 1.75 1.75 1.75h7.5c.965 0 1.75-.785 1.75-1.75v-10.5c0-.965-.785-1.75-1.75-1.75z"/>
                                </g>
                            </svg>
                            <br>
                            <span>پیشخوان</span>
                        </label>
                    </li>
                    <li id="za-tab-3" class="all-users-list">
                        <label for="tab3" role="button">
                            <svg  width="21" height="21" xmlns="http://www.w3.org/2000/svg"   x="0px" y="0px"
                                  viewBox="0 0 460.8 460.8" style="enable-background:new 0 0 460.8 460.8;" xml:space="preserve">
                                <g>
                                    <g>
                                        <path d="M230.432,0c-65.829,0-119.641,53.812-119.641,119.641s53.812,119.641,119.641,119.641s119.641-53.812,119.641-119.641
                                            S296.261,0,230.432,0z"/>
                                    </g>
                                </g>
                                <g>
                                    <g>
                                        <path d="M435.755,334.89c-3.135-7.837-7.314-15.151-12.016-21.943c-24.033-35.527-61.126-59.037-102.922-64.784
                                        c-5.224-0.522-10.971,0.522-15.151,3.657c-21.943,16.196-48.065,24.555-75.233,24.555s-53.29-8.359-75.233-24.555
                                        c-4.18-3.135-9.927-4.702-15.151-3.657c-41.796,5.747-79.412,29.257-102.922,64.784c-4.702,6.792-8.882,14.629-12.016,21.943
                                        c-1.567,3.135-1.045,6.792,0.522,9.927c4.18,7.314,9.404,14.629,14.106,20.898c7.314,9.927,15.151,18.808,24.033,27.167
                                        c7.314,7.314,15.673,14.106,24.033,20.898c41.273,30.825,90.906,47.02,142.106,47.02s100.833-16.196,142.106-47.02
                                        c8.359-6.269,16.718-13.584,24.033-20.898c8.359-8.359,16.718-17.241,24.033-27.167c5.224-6.792,9.927-13.584,14.106-20.898
                                        C436.8,341.682,437.322,338.024,435.755,334.89z"/>
                                    </g>
                                </g>
                            </svg>
                            <br>
                            <span>کاربران</span>
                        </label>
                    </li>
                    <li id="za-tab-4">
                        <label for="tab4" role="button">
                            <svg width="21" height="21" viewBox="0 0 512.013 512.013" >
                                <g>
                                    <path d="m369.871 280.394-34.14 34.141-.001-.001-235.826-235.827 18.124-18.124-75.151-60.569-42.877 42.877
                                    60.569 75.15 18.125-18.124 235.827 235.827-34.141 34.141 48.69 48.689 89.49-89.49z"/>
                                    <path d="m439.773 350.297-89.49 89.49 53.692 53.692c11.952 11.952 27.843 18.534 44.746 18.534 16.902 0
                                    32.793-6.582 44.745-18.534 24.672-24.673 24.672-64.817 0-89.49z"/>
                                </g>
                                <path d="m259.136 322.785-69.9-69.9-51.176 51.169c-37.59-11.78-78.61-1.94-106.9 26.36-20.09 20.09-31.16 46.799-31.16
                                75.22 0 28.41 11.07 55.13 31.16 75.22 20.74 20.74 47.98 31.11 75.22 31.11s54.49-10.37 75.22-31.11c28.3-28.29 38.14-69.31
                                 26.36-106.9zm-128.406 107.188c-13.45 13.45-35.25 13.45-48.69 0-13.45-13.44-13.45-35.24 0-48.69 13.44-13.44 35.24-13.44
                                 48.69 0 13.44 13.45 13.44 35.251 0 48.69z"/>
                                <g>
                                    <path d="m505.04 64.163-8.36-21.35-53.67 53.67-21.67-5.81-5.81-21.67 53.67-53.66-21.35-8.37c-37.43-14.66-79.97-5.78-108.38
                                     22.63-26.02 26.02-35.66 63.91-25.82 98.86l-60.777 60.784 69.9 69.9 60.777-60.784c9.02 2.54 18.22 3.78 27.37 3.78 26.33-.01
                                      52.18-10.29 71.49-29.6 28.41-28.409 37.29-70.949 22.63-108.38z"/>
                                </g>
                            </svg>
                            <br>
                            <span> تنظیمات</span>
                        </label>
                    </li>
                </ul>
                <div class="slider">
                    <div class="indicator"></div>
                </div>
                <div class="content ask-content">
                    <section class="za-tab-1" id="za-tab-first">
                        <div class="ask-message-con">
                            <ul id="ask-message-tab">
                                <?php $not_approved = AskDB::get_instance()::getNotApproved();  ?>
                                <?php $new_replies  = AskDB::get_instance()::getNewReplies();  ?>
                                <li id="ask-questions" class="active" > سوال در انتظار  <span><?php echo $not_approved != 404 ? count( $not_approved ) : 0; ?> </span></li>
                                <li id="ask-replies" >پاسخ در انتظار  <span><?php echo $new_replies != 404 ? count( $new_replies ) : 0; ?></span></li>
                                <li id="ask-all-ask" > تمامی سوال ها </li>
                                <li id="ask-all-replies" > تمامی پاسخ ها </span></li>
                            </ul>
                            <div class="ask-message-content">
                                <div class="ask-questions active" >
                                    <ul>
                                        <?php
                                            if ( $not_approved !== 404 ){
                                                foreach ( $not_approved as $item ){
                                                    $item = (object) $item;
                                                ?>
                                                <li id="<?php echo 'questions'.$item->id ?>" >
                                                    <div class="top">
                                                        <div class="right">
                                                            <div class="profile-img">
                                                                <img src="<?php echo $item->profile_image ?>" alt="profile">
                                                            </div>
                                                            <div class="profile">
                                                                <p> نام:<span> <?php echo $item->creator ?></span></p>
                                                                <p> ثبت نام:<span> <?php echo $item->created_date ?> </span></p>

                                                            </div>
                                                            <div class="question-details">
                                                                <p> زمان ارسال:<span> <?php echo $item->time_ago ?> </span></p>
                                                                <p> تلفن:<span> <?php echo $item->creator_mob ?> </span></p>
                                                            </div>
                                                        </div>
                                                        <div class="left">
                                                            <div class="btn-container">
                                                                    <button  class="accept" data-id="<?php echo $item->id ?>" >پذیرفتن</button>
                                                                    <button  class="reject" data-id="<?php echo $item->id ?>" data-which="questions" >رد کردن</button>
                                                                    <button class="edit" data-id="<?php echo $item->id ?>" >ویرایش</button>
                                                                    <div id="edit-board">
                                                                        <div id="form-<?php echo $item->id ?>" >
                                                                            <div class="ask-edit-cancel">
                                                                                <button>  لغو ویرایش</button>
                                                                            </div>
                                                                            <div class="ask-edit-title">
                                                                                <label for="ask-edit-title"> عنوان </label>
                                                                                <input type="text" id="ask-edit-title" value="<?php echo $item->title ?>" required >
                                                                            </div>
                                                                            <div class="ask-edit-content">
                                                                                <div id="ask-editor-edit-<?php echo $item->id ?>" >
                                                                                    <?php echo $item->content ?>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ask-edit-tag">
                                                                                <label for="select-unlocked"> هشتک </label>
                                                                                <select id="select-unlocked" class="ask-new-tag" multiple >
                                                                                    <?php
                                                                                    echo AskFunctions::notApprovesTags( $item->tags ,'option' );
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="edit-save">
                                                                                <button data-id="<?php echo $item->id ?>" class="update" > بروزرسانی </button>
                                                                                <button data-id="<?php echo $item->id ?>" class="update accept-plus" > بروزرسانی و پذیرفتن </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bottom">
                                                        <h3><?php echo $item->title ?></h3>
                                                        <div class="ask-action-pending-para" id="content-<?php echo $item->id ?>">
                                                            <?php echo str_replace('ace_scroller' ,'' ,$item->content ); ?>
                                                        </div>
                                                        <div class="bottom-actions">
                                                            <div class="tags">
                                                                <?php echo AskFunctions::insertedTags( $item->tags );  ?>
                                                            </div>
                                                            <div class="expand" id="<?php echo $item->id ?>" >
                                                                <div>
                                                                    <svg id="top" xmlns="http://www.w3.org/2000/svg"  x="0px" y="0px"
                                                                         width="15" height="15" viewBox="0 0 292.362 292.361" fill="#eee" >
                                                                        <g>
                                                                            <path d="M286.935,197.287L159.028,69.381c-3.613-3.617-7.895-5.424-12.847-5.424s-9.233,1.807-12.85,5.424L5.424,197.287
                                                                        C1.807,200.904,0,205.186,0,210.134s1.807,9.233,5.424,12.847c3.621,3.617,7.902,5.425,12.85,5.425h255.813
                                                                        c4.949,0,9.233-1.808,12.848-5.425c3.613-3.613,5.427-7.898,5.427-12.847S290.548,200.904,286.935,197.287z"/>
                                                                        </g>
                                                                    </svg>
                                                                    <svg id="bottom" fill="#999" xmlns="http://www.w3.org/2000/svg"  x="0px" y="0px" width="15" height="15" viewBox="0 0 292.362 292.362" >
                                                                        <g>
                                                                            <path d="M286.935,69.377c-3.614-3.617-7.898-5.424-12.848-5.424H18.274c-4.952,0-9.233,1.807-12.85,5.424
                                                                    C1.807,72.998,0,77.279,0,82.228c0,4.948,1.807,9.229,5.424,12.847l127.907,127.907c3.621,3.617,7.902,5.428,12.85,5.428
                                                                    s9.233-1.811,12.847-5.428L286.935,95.074c3.613-3.617,5.427-7.898,5.427-12.847C292.362,77.279,290.548,72.998,286.935,69.377z"/>
                                                                        </g>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </li>
                                            <?php
                                                }
                                            }
                                        ?>
                                    </ul>
                                </div>
                                <div class="ask-replies">
                                    <ul>
                                        <?php
                                        if ( $new_replies !== 404 ){
                                            foreach ( $new_replies as $rep ){
                                                $rep = (object) $rep;
                                                ?>
                                                <li id="<?php echo 'replies'.$rep->id ?>">
                                                    <div class="top">
                                                        <div class="right">
                                                            <div class="profile-img">
                                                                <img src="<?php echo $rep->profile_image ?>" alt="profile">
                                                            </div>
                                                            <div class="profile">
                                                                <p> نام:<span> <?php echo $rep->creator ?></span></p>
                                                            </div>
                                                            <div class="question-details">
                                                                <p> زمان ارسال:<span> <?php echo $rep->time_ago ?></span></p>
                                                                <p> تلفن:<span> <?php echo $rep->creator_mob ?> </span></p>
                                                            </div>
                                                        </div>
                                                        <div class="left">
                                                            <div class="btn-container reply-btn-action">
                                                                <button class="accept" data-id="<?php echo $rep->id ?>" >پذیرفتن</button>
                                                                <button  class="reject" data-id="<?php echo $rep->id ?>" data-which="replies" >رد کردن</button>
                                                                <button  class="edit" data-id="<?php echo $rep->id ?>" >ویرایش</button>
                                                                <div id="edit-board">
                                                                    <div id="form-<?php echo $rep->id ?>" >
                                                                        <div class="ask-edit-cancel">
                                                                            <button> لغو ویرایش</button>
                                                                        </div>
                                                                        <div class="ask-edit-content">
                                                                            <div id="ask-editor-edit-<?php echo $rep->id ?>" >
                                                                                <?php echo $rep->content; ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="edit-save">
                                                                            <button data-id="<?php echo $rep->id; ?>" class="update-reply" > بروزرسانی </button>
                                                                            <button data-id="<?php echo $rep->id; ?>" class="update-reply accept-plus" > بروزرسانی و پذیرفتن </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bottom">
                                                        <div class="parent-ask-con">
                                                            <div class="parent-title">
                                                                <?php echo $rep->parent_ask['title'];  ?>
                                                                <div class="parent-content">
                                                                    <div class="parent-content-shower">
                                                                        نمایش محتوا
                                                                        <div>
                                                                            <?php echo $rep->parent_ask['content'];  ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="ask-action-pending-para" id="content-<?php echo $rep->id; ?>">
                                                            <?php echo $rep->content  ?>
                                                        </div>
                                                    </div>
                                                    <div class="expand" id="<?php echo $rep->id ?>">
                                                        <div>
                                                            <svg id="top" xmlns="http://www.w3.org/2000/svg"  x="0px" y="0px"
                                                                 width="15" height="15" viewBox="0 0 292.362 292.361" fill="#eee" >
                                                                <g>
                                                                    <path d="M286.935,197.287L159.028,69.381c-3.613-3.617-7.895-5.424-12.847-5.424s-9.233,1.807-12.85,5.424L5.424,197.287
                                                                        C1.807,200.904,0,205.186,0,210.134s1.807,9.233,5.424,12.847c3.621,3.617,7.902,5.425,12.85,5.425h255.813
                                                                        c4.949,0,9.233-1.808,12.848-5.425c3.613-3.613,5.427-7.898,5.427-12.847S290.548,200.904,286.935,197.287z"/>
                                                                </g>
                                                            </svg>
                                                            <svg id="bottom" xmlns="http://www.w3.org/2000/svg"  x="0px" y="0px"
                                                                 width="15" height="15" viewBox="0 0 292.362 292.362" fill="#999" >
                                                                <g>
                                                                    <path d="M286.935,69.377c-3.614-3.617-7.898-5.424-12.848-5.424H18.274c-4.952,0-9.233,1.807-12.85,5.424
                                                                    C1.807,72.998,0,77.279,0,82.228c0,4.948,1.807,9.229,5.424,12.847l127.907,127.907c3.621,3.617,7.902,5.428,12.85,5.428
                                                                    s9.233-1.811,12.847-5.428L286.935,95.074c3.613-3.617,5.427-7.898,5.427-12.847C292.362,77.279,290.548,72.998,286.935,69.377z"/>
                                                                </g>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </li>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <div class="ask-all-ask">
                                    <ul></ul>
                                </div>
                                <div class="ask-all-replies">
                                    <ul></ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="za-tab-2">
                        <h2>Delivery Contents</h2>
                    </section>

                    <section class="za-tab-3" >
                        <h2>لیست کاربران فعال </h2>
                        <div class="users-list">  </div>
                    </section>

                    <section class="za-tab-4">
                        <div class="ask-tag-container">
                            <h2> لیست تگ ها </h2>
                            <ul>
                                <?php
                                $ask_options = maybe_unserialize( get_option('hamfy_ask_options' ) );
                                if ( !empty( $ask_options )){
                                    $tags = $ask_options['tags'];
                                    if ( !empty( $tags) ){
                                        foreach ( $tags as $tag ){
                                            echo '<li data-tag-name="'.$tag.'" > 
                                                 <span>*</span> 
                                                 <p>'.$tag.'</p> 
                                             </li>';
                                        }
                                    }
                                }
                                ?>
                            </ul>
                            <div class="ask-save-tags-btn">
                                <button class="add"> افزودن تگ ها </button>
                                <button class="update"> ذخیره تغییرات </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <?php
        exit();
    }


    public static function allAskDashboard()
    {
        $all_asks = AskDB::get_instance()::getAllOld();
        if ( $all_asks !== 404 ){
            foreach ( $all_asks as $item ){
                $item = (object) $item;
                ?>
                <li>
                    <div class="top">
                        <div class="right">
                            <div class="profile-img">
                                <img src="<?php echo $item->profile_image ?>" alt="profile">
                            </div>
                            <div class="profile">
                                <a href="<?php echo home_url().'/ask/'.$item->id ?>" target="_blank"> #شناسه:<span>
                                        <?php echo $item->id ?></span></a>
                                <p> نام:<span> <?php echo $item->creator ?></span></p>
                                <p> تلفن:<span> <?php echo $item->creator_mob ?> </span></p>
                            </div>
                        </div>
                        <div class="left">
                            <div class="status-container">
                                <div>
                                    <?php
                                    $log = unserialize( $item->log );
                                    if ($item->approved === 'accept'){
                                        $updater = (int) $log['accept_user']['user'];
                                        $up_time = $log['accept_user']['time'];
                                        if ( $updater > 0 ) {
                                            $user =  get_user_by('id' , $updater );
                                            $updater = $user->first_name .' '.$user->last_name;
                                        }else{
                                            $updater = 'نامشخص ';
                                        }
                                        ?>
                                        <div class="accepted">
                                            پذیرفته شده توسط
                                            <p>
                                                <?php echo $updater; ?>
                                            </p>
                                            در
                                            <p>
                                                <?php echo isset( $up_time ) ? date_i18n('j F  Y  ساعت  H ' , strtotime( $up_time )) : 'نامشخص'; ?>
                                            </p>
                                        </div>
                                        <?php
                                    }elseif ($item->approved === 'reject'){
                                        $updater = (int) $log['reject_user']['user'];
                                        $up_time = $log['reject_user']['time'];
                                        if ( $updater > 0 ) {
                                            $user =  get_user_by('id' , $updater );
                                            $updater = $user->first_name .' '.$user->last_name;
                                        }else{
                                            $updater = 'نامشخص ';
                                        }
                                        ?>
                                        <div class="rejected">
                                            رد شده توسط
                                            <p>
                                                <?php echo $updater; ?>
                                            </p>
                                            در
                                            <p>
                                                <?php echo isset( $up_time ) ? date_i18n('j F  Y  ساعت  H ' ,strtotime( $up_time )) : 'نامشخص'; ?>
                                            </p>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <?php
            }
        }
        exit();
    }


    public static function allRepliesDashboard()
    {
        $all_replies = AskDB::get_instance()::getAllReplies();
        if ( $all_replies !== 404 ){
            foreach ( $all_replies as $reply ){
                $reply = (object) $reply;
                ?>
                <li>
                    <div class="top">
                        <div class="right">
                            <div class="profile-img">
                                <img src="<?php echo $reply->profile_image ?>" alt="profile">
                            </div>
                            <div class="profile">
                                <a href="<?php echo home_url().'/ask/'.$reply->id ?>" target="_blank"> #شناسه:<span>
                                        <?php echo $reply->id ?></span></a>
                                <p> نام:<span> <?php echo $reply->creator ?></span></p>
                                <p> تلفن:<span> <?php echo $reply->creator_mob ?> </span></p>
                            </div>
                        </div>
                        <div class="left">
                            <div class="status-container">
                                <div>
                                    <?php
                                    $log = unserialize( $reply->log );
                                    if ( $reply->approved === 'accept' ){
                                        $updater = (int) $log['accept_user']['user'];
                                        $up_time = $log['accept_user']['time'];
                                        if ( $updater > 0 ) {
                                            $user =  get_user_by('id' , $updater );
                                            $updater = $user->first_name .' '.$user->last_name;
                                        }else{
                                            $updater = 'نامشخص ';
                                        }
                                        ?>
                                        <div class="accepted">
                                            <p>
                                                پذیرفته شده توسط
                                            </p>
                                            <p>
                                                <?php echo $updater; ?>
                                                <span> در</span>
                                            </p>
                                            <p>
                                                <?php echo isset( $up_time ) ? date_i18n('j F  Y  ساعت  H ',strtotime( $up_time )) : 'نامشخص'; ?>
                                            </p>
                                        </div>
                                        <?php
                                    }elseif ( $reply->approved === 'reject' ){
                                        $updater = (int) $log['reject_user']['user'];
                                        $up_time = $log['reject_user']['time'];
                                        if ( $updater > 0 ) {
                                            $user =  get_user_by('id' , $updater );
                                            $updater = $user->first_name .' '.$user->last_name;
                                        }else{
                                            $updater = 'نامشخص ';
                                        }
                                        ?>
                                        <div class="rejected">
                                            رد شده توسط
                                            <p>
                                                <?php echo $updater; ?>
                                                <span> در</span>
                                            </p>
                                            <p>
                                                <?php echo isset( $up_time ) ? date_i18n('j F  Y  ساعت  H ',strtotime( $up_time )) : 'نامشخص'; ?>
                                            </p>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <?php
            }
        }
        exit();
    }


    public static function profile( $userID )
    {
        if ( $userID <= 0 ) return '';
        ?>
            <div class="ask-profile-con">
                <h4>پرفایل</h4>
                <div class="ask-profile-item">
                    <div data-name="پرفایل" class="profile">
                        <svg height="30px" width="30px" viewBox="0 0 512 512"  xmlns="http://www.w3.org/2000/svg">
                            <path d="m471.386719 325.011719c-16.96875-14.910157-37.546875-27.792969-61.167969-38.289063-10.097656-4.484375-21.914062.0625-26.398438 10.15625-4.484374 10.09375.0625 21.910156 10.15625 26.398438 19.917969 8.851562 37.082032 19.542968 51.007813 31.78125 17.167969 15.085937 27.015625 36.929687 27.015625 59.941406v37c0 11.027344-8.972656 20-20 20h-392c-11.027344 0-20-8.972656-20-20v-37c0-23.011719 9.847656-44.855469 27.015625-59.941406 20.207031-17.757813 79.082031-59.058594 188.984375-59.058594 81.605469 0 148-66.394531 148-148s-66.394531-148-148-148-148 66.394531-148 148c0 47.707031 22.695312 90.207031 57.851562 117.289062-64.328124 14.140626-104.34375 41.359376-125.238281 59.722657-25.808593 22.675781-40.613281 55.472656-40.613281 89.988281v37c0 33.085938 26.914062 60 60 60h392c33.085938 0 60-26.914062 60-60v-37c0-34.515625-14.804688-67.3125-40.613281-89.988281zm-323.386719-177.011719c0-59.550781 48.449219-108 108-108s108 48.449219 108 108-48.449219 108-108 108-108-48.449219-108-108zm0 0"/>
                        </svg>
                    </div>
                    <div data-name="سوالات" class="question">
                        <svg  x="0px" y="0px" height="30px" width="30px"
                              viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                            <g>
                                <g>
                                    <g>
                                        <circle cx="256" cy="378.5" r="25"/>
                                        <path d="M256,0C114.516,0,0,114.497,0,256c0,141.484,114.497,256,256,256c141.484,0,256-114.497,256-256
                                            C512,114.516,397.503,0,256,0z M256,472c-119.377,0-216-96.607-216-216c0-119.377,96.607-216,216-216
                                            c119.377,0,216,96.607,216,216C472,375.377,375.393,472,256,472z"/>
                                        <path d="M256,128.5c-44.112,0-80,35.888-80,80c0,11.046,8.954,20,20,20s20-8.954,20-20c0-22.056,17.944-40,40-40
                                            c22.056,0,40,17.944,40,40c0,22.056-17.944,40-40,40c-11.046,0-20,8.954-20,20v50c0,11.046,8.954,20,20,20
                                            c11.046,0,20-8.954,20-20v-32.531c34.466-8.903,60-40.26,60-77.469C336,164.388,300.112,128.5,256,128.5z"/>
                                    </g>
                                </g>
                            </g>
                        </svg>

                    </div>
                    <div data-name="نشانک" class="bookmark">
                        <svg  height="30px" width="30px" xmlns="http://www.w3.org/2000/svg"  x="0px" y="0px"
                             viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                            <g>
                                <g>
                                    <path d="M429.998,431.996c-11.046,0-20,8.954-20,20c0,5.576-1.889,10.268-5.615,13.947c-3.91,3.861-9.583,6.098-15.132,6.054
                                        c-5.475-0.07-9.919-1.935-13.587-5.705L270.333,358.048c-3.765-3.869-8.935-6.052-14.333-6.052s-10.568,2.183-14.333,6.052
                                        L136.335,466.292c-3.641,3.742-8.118,5.608-13.686,5.706c-5.61,0.115-11.257-2.12-15.14-5.936
                                        c-3.655-3.591-5.508-8.206-5.508-13.718V79.999c0-22.056,17.944-40,40-40h227.997c22.056,0,40,17.944,40,40v272.997
                                        c0,11.046,8.954,20,20,20c11.046,0,20-8.954,20-20V79.999C449.997,35.888,414.11,0,369.998,0H142.001
                                        C97.889,0,62.002,35.888,62.002,79.999v372.345c0,16.174,6.206,31.179,17.474,42.25C90.77,505.69,106.32,512,122.288,512
                                        c0.355,0,0.711-0.003,1.066-0.009c16.191-0.285,30.592-6.441,41.648-17.804L256,400.673l90.998,93.514
                                        c11.136,11.444,25.572,17.601,41.747,17.806c16.236,0.167,32.215-6.204,43.742-17.588c11.292-11.149,17.51-26.211,17.51-42.409
                                        C449.998,440.95,441.044,431.996,429.998,431.996z"/>
                                </g>
                            </g>
                        </svg>
                    </div>
                </div>
                <div class="profile-list-con">
                    <?php
                    $profile = (object) AskDB::get_instance()::getUserDetails( $userID );
                    ?>

                    <div id="profile">
                        <div class="top">
                            <img src="<?php echo $profile->picture; ?>" alt="profile">
                            <p><?php echo $profile->name; ?> </p>
                        </div>
                        <div class="middle">
                            <p> <span> <?php echo $profile->question_count; ?> </span> سوال </p>
                            <p> <span> <?php echo $profile->replies_count; ?> </span> پاسخ </p>
                        </div>
                        <div class="bottom">
                            <p> <span> <?php echo $profile->user_likes; ?> </span> likes </p>
                            <p> <span> <?php echo $profile->user_dislikes; ?> </span> dislikes </p>
                            <p> <span> <?php echo $profile->views; ?> </span> view </p>
                        </div>
                    </div>
                    <div id="question">
                       <div class="ask-profile-question-con">
                           <p class="ask-profile-questions">سوال ها</p>
                           <p class="ask-profile-replies" >پاسخ ها</p>
                       </div>
                        <div class="ask-profile-content" >
                            <div id="ask-profile-questions">
                                <ul>
                                    <?php
                                    $user_ask_list = AskDB::userAsksList( $userID );
                                    if ( $user_ask_list !== 404 &&  !empty( $user_ask_list ) ){
                                        foreach ( $user_ask_list as $item ){
                                            $item = (object) $item;
                                        ?>
                                            <li>
                                                <div class="ask-header">
                                                    <a href="<?php echo home_url().'/ask/'.$item->id; ?>"
                                                       class="ask-item-link"
                                                       data-ask-id="<?php echo $item->id; ?>"
                                                       onclick="return false;" >
                                                        <?php echo $item->title; ?>
                                                    </a>
                                                    <div class="status">
                                                        <p>
                                                            <span style="color:<?php echo AskFunctions::statusColor( $item->approved ); ?>">
                                                                <?php echo AskFunctions::statusTranslate( $item->approved ); ?>
                                                            </span>
                                                            <?php
                                                            if( $item->approved != 'pending') {
                                                                echo '<span style="color:#bc0b0b">'. $item->comment.' </span>';
                                                            } ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <?php if ( $item->approved === 'accept' ){ ?>
                                                    <div class="ask-footer">
                                                        <div > <span><?php echo $item->ask_likes + $item->ask_dislikes; ?></span> <span>رای</span> </div>
                                                        <div > <span><?php echo $item->reply_count; ?></span> <span>پاسخ</span> </div>
                                                        <div > <span><?php echo $item->views; ?></span> <span>رویت</span></div>
                                                    </div>
                                                <?php } ?>
                                            </li>
                                        <?php }
                                    } ?>
                                </ul>
                            </div>
                            <div id="ask-profile-replies">
                                <ul>
                                    <?php
                                    $user_ask_list = AskDB::userRepliesList( $userID );
                                    if ( $user_ask_list !== 404 && !empty( $user_ask_list ) ){
                                        foreach ( $user_ask_list as $item ){
                                            $item = (object) $item;
                                            ?>
                                            <li>
                                                <div class="ask-header">
                                                    <a href="<?php echo home_url().'/ask/'.$item->id; ?>"  onclick="return false;"
                                                       class="ask-item-link"
                                                       data-ask-id="<?php echo $item->parent_ask; ?>"
                                                    >
                                                        <?php echo AskFunctions::excerpt( $item->content ,300) ; ?>
                                                    </a>
                                                    <div class="status">
                                                        <p>
                                                            <span><?php echo AskFunctions::statusTranslate( $item->approved ); ?>  </span>
                                                            <?php echo $item->comment ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <?php if ( $item->approved === 'accept' ){ ?>
                                                    <div class="ask-footer">
                                                        <div > <span><?php echo $item->like + $item->dislike; ?></span> <span>رای</span> </div>
                                                    </div>
                                                <?php } ?>
                                            </li>
                                        <?php }
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div id="bookmark">
                        <ul>
                        <?php
                        $user_ask_list = AskProcess::getBookmark( $userID );
                        if ( $user_ask_list !== 404 && !empty( $user_ask_list ) ){
                            foreach ( $user_ask_list as $item ){
                                $item = (object) $item;
                                ?>
                                <li>
                                    <div class="ask-header">
                                        <a href="<?php echo home_url().'/ask/'.$item->id; ?>"  onclick="return false;">
                                            <?php echo $item->title; ?>
                                        </a>
                                        <div class="ask-bookmark" data-ask-id="<?php echo $item->id; ?>" data-booked-status="true">
                                            <svg xmlns="http://www.w3.org/2000/svg"  x="0px" y="0px" fill="#f22d33"
                                                 viewBox="0 0 490.6 490.6"   xml:space="preserve" width="25px" height="25px">
                                                <g>
                                                    <path d="M393,0H97.6c-14,0-25.3,11.3-25.3,25.3v332.6c0,11.5,5.6,22.2,15,28.8l140.8,98.5c10.3,7.2,24.1,7.2,34.4,0l140.8-98.5
                                                        c9.4-6.6,15-17.3,15-28.8V25.3C418.3,11.3,407,0,393,0z M351.5,189.9l-48.1,40.9l15,61.5c2.8,11.6-9.7,20.8-19.8,14.5l-53.3-33.5
                                                        l-53.4,33.4c-10.1,6.3-22.6-2.9-19.8-14.5l15-61.5L139,189.8c-9-7.7-4.3-22.6,7.6-23.4l62.7-4.6l23.7-58.7c4.5-11,20-11,24.5,0
                                                        l23.7,58.7l62.7,4.6C355.8,167.3,360.6,182.2,351.5,189.9z"/>
                                                </g>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ask-footer">
                                        <div > <span><?php echo $item->like + $item->dislike; ?></span> <span>رای</span> </div>
                                        <div > <span><?php echo $item->reply_count; ?></span> <span>پاسخ</span> </div>
                                        <div > <span><?php echo $item->views; ?></span> <span>رویت</span></div>
                                    </div>
                                </li>
                            <?php }
                        }else{ ?>
                                <li> موردی یافت نشد </li>
                        <?php
                        } ?>
                        </ul>
                    </div>
                </div>bottom
            </div>
        <?php
    }


    public static function dashboardUsersList()
    {
        $users = AskDB::get_instance()::getUserslist();
        foreach ( $users as $user ){
            ?>
            <div>
                <div class="top">
                    <div class="img">
                        <?php echo $user['image']; ?>
                    </div>
                    <div class="user-details">
                        <span> <?php echo $user['name']; ?> </span>
                    </div>
                    <div class="user-rate">
                        <span>
                            <b>
                                <?php echo AskFunctions::calculateUserScore( $user['likes'] ,$user['dislikes'] ,$user['views'] ); ?>
                            </b>
                        </span>
                    </div>
                </div>
                <div class="bottom">
                    <div>
                        <p> تعداد سوال :<span> <?php echo $user['ask']; ?>  </span></p>
                        <p> تعداد پاسخ :<span> <?php echo $user['replies']; ?>  </span></p>
                    </div>
                    <div>
                        <p>لایک :<span> <?php echo $user['likes']; ?> </span></p>
                        <p> دیس لایک :<span> <?php echo $user['dislikes']; ?> </span></p>
                    </div>
                    <p>بازدید :<span><?php echo $user['views']; ?> </span></p>
                </div>
            </div>
            <?php
        }
        exit();
    }


    public static function shortCodeUI( $items ,$details )
    {
        if ( !empty( $items ) ){
            ?>
            <div class="ask-shortcode-con">
                <div class="ask-shortcode-main">
                    <h3>سوالات مرتبط</h3>
                    <section>
                    <?php  foreach ( $items as $item ){  ?>
                        <div style="width:<?php echo ( 100/count( $items ) - count( $items )  ).'%'; ?>;" >
                            <div class="ask-title">
                                <span> <?php echo $item['created_time']; ?> </span>
                                <a target="_blank" href="<?php echo home_url().'/ask/'.$item['id']; ?>">
                                    <?php echo $item['title']; ?>
                                </a>
                            </div>
                            <div class="ask-content">
                                <p><?php echo $item['content']; ?></p>
                            </div>
                            <div class="ask-tags">
                                <?php echo $item['tags']; ?>
                            </div>
                            <?php  if ( $details ){  ?>
                                <div class="ask-details">
                                    <p>رویت :<span> <?php echo $item['views']; ?> </span></p>
                                    <p> امتیاز :<span> <?php echo $item['likes']; ?> </span></p>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                  </section>
               </div>
            </div>
            <?php
        }
    }


}