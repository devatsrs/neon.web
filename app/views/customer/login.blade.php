@extends('layout.customer.login')
@section('content')
    <div class="login-container">
        <div class="login-header login-caret">
            <div class="login-content">
                @if(Session::get('user_site_configrations.Logo')!='')
                    <a href="<?php echo URL::to('/'); ?>">
                        <img src="{{Session::get('user_site_configrations.Logo')}}" width="120" alt="" />
                    </a>
                @endif

                <?php
                $domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $_SERVER['HTTP_HOST']);
                $domainUrl_key = strtoupper(preg_replace('/-+/', '_',$domainUrl_key));
                ?>
                <p class="description" style="color:#fff">{{cus_lang("THEMES_".$domainUrl_key."_LOGIN_MSG")}}</p>
                <!--<p class="description" style="color:#fff">Dear user, log in to access your account!</p>-->
                <!-- progress bar indicator -->
                <div class="login-progressbar-indicator">
                    <h3>43%</h3>
                    <span>@lang("routes.CUST_PANEL_PAGE_LOGIN_MSG_LOGGING_PROCESS")</span>
                </div>
            </div>
        </div>
        <div class="login-progressbar">
            <div></div>
        </div>
        <div class="login-form">
            <div class="login-content">
                <div class="form-login-error">
                    <h3>@lang("routes.CUST_PANEL_PAGE_LOGIN_HEADING_INVALID_LOGIN_TITLE")</h3>
                    <p>@lang("routes.CUST_PANEL_PAGE_LOGIN_HEADING_INVALID_LOGIN_MSG")</p>
                </div>
                <form method="post" role="form" id="form_customer_login">

                        <div class="input-group ddl-language col-md-12">
{{--                            {{ Form::select('user_language', Translation::getLanguageDropdownList(), $language , array("class"=>"form-control ","id"=>"user_language")) }}--}}
                            <ul class="list-inline links-list pull-right">
                                <li class="dropdown language-selector" id="user_language">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true" aria-expanded="false">
                                        <img src="" width="30" />
                                        <span></span>
                                        <i class="entypo-down-open-mini"></i>
                                    </a>
                                    <ul class="dropdown-menu pull-right">
                                        <?php
//                                        $language=NeonCookie::getCookie('customer_language',"en");
                                        ?>
                                        @foreach( Translation::getLanguageDropdownWithFlagList() as $key=>$value )
                                            <?php
                                            $selected="";
                                            if($language==$key){
                                                $selected="active";
                                            }
                                            ?>
                                            <li class="{{$selected}}" lang-key="{{$key}}">
                                                <a href="javascripe:void(0);">
                                                    <img src="{{URL::to('/assets/images/flag/'.$value["languageFlag"])}}" width="30" />
                                                    <span>{{$value["languageName"]}}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            </ul>

                        </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="entypo-mail"></i>
                            </div>
                            <input type="text" class="form-control" name="email" id="email" placeholder="@lang("routes.PLACEHOLDER_EMAIL")" autocomplete="off" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="entypo-key"></i>
                            </div>
                            <input type="password" class="form-control" name="password" id="password" placeholder="@lang("routes.PLACEHOLDER_PASSWORD")" autocomplete="off" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block btn-login">
                            <i class="entypo-login"></i>
                            @lang("routes.BUTTON_LOGIN_CAPTION")
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop