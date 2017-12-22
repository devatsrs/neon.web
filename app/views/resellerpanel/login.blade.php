@extends('layout.resellerpanel.login')
@section('content')
    <div class="login-container">
        <div class="login-header login-caret">
            <div class="login-content">
                @if(Session::get('user_site_configrations.Logo')!='')
                    <a href="<?php echo URL::to('/'); ?>">
                        <img src="{{Session::get('user_site_configrations.Logo')}}" width="120" alt="" />
                    </a>
                @endif


                <p class="description" style="color:#fff">{{Session::get('user_site_configrations.LoginMessage')}}</p>
                <!--<p class="description" style="color:#fff">Dear user, log in to access your account!</p>-->
                <!-- progress bar indicator -->
                <div class="login-progressbar-indicator">
                    <h3>43%</h3>
                    <span>logging in...</span>
                </div>
            </div>
        </div>
        <div class="login-progressbar">
            <div></div>
        </div>
        <div class="login-form">
            <div class="login-content">
                <div class="form-login-error">
                    <h3>Invalid login</h3>
                    <p>Enter correct login and password.</p>
                </div>
                <form method="post" role="form" id="form_reseller_login">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="entypo-mail"></i>
                            </div>
                            <input type="text" class="form-control" name="email" id="email" placeholder="Email" autocomplete="off" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="entypo-key"></i>
                            </div>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="off" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block btn-login">
                            <i class="entypo-login"></i>
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop