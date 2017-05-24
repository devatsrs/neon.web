@extends('layout.login')

@section('content')
<div class="login-container">

    <div class="login-header login-caret">

        <div class="login-content">
            @if(Session::get('user_site_configrations.Logo')!='')
            <a href="" class="logo">
                <img src="{{Session::get('user_site_configrations.Logo')}}" width="120" alt="" />
            </a>
            @endif
            <p class="description" style="color:#fff">Enter your email, and we will send the reset link.</p>

            <!-- progress bar indicator -->
            <div class="login-progressbar-indicator">
                <h3>43%</h3>
                <span>loading...</span>
            </div>
        </div>

    </div>

    <div class="login-progressbar">
        <div></div>
    </div>

    <div class="login-form">

        <div class="login-content">

            <form method="post" role="form" id="form_forgot_password">

                <div class="form-forgotpassword-success">
                    <i class="entypo-check"></i>
                    <h3>Reset email has been sent.</h3>
                    <p>Please check your email </p>
                </div>
                <div class="form-login-error">
                    <h3>Failed</h3>
                    <p>Please enter correct Email.</p>
                </div>

                <div class="form-steps">

                    <div class="step current" id="step-1">

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="entypo-mail"></i>
                                </div>

                                <input type="text" class="form-control" name="email" id="email" placeholder="Email" data-mask="email" autocomplete="off" />
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-info btn-block btn-login">Submit</button>
                        </div>

                    </div>

                </div>

            </form>


            <div class="login-bottom-links">

                <a href="<?php echo URL::to('/'); ?>" class="link">
                    <i class="entypo-lock"></i>
                    Return to Login Page
                </a>

                <br />



            </div>

        </div>

    </div>

</div>
@stop