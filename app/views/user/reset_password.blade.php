@extends('layout.login')

@section('content')
<div class="login-container">

    <div class="login-header login-caret">
        <div class="login-content">

            <a href="" class="logo">
                <img src="<?php echo URL::to('/'); ?>/assets/images/logo@2x.png" width="120" alt="" />
            </a>

            <p class="description" style="color:#fff">Enter your New password.</p>
            <div class="login-progressbar-indicator">
                            <span style="font-size: 20px !important;">loading...</span>
                        </div>
        </div>

    </div>

    <div class="login-form">

        <div class="login-content">

             <form method="post" role="form" id="form_reset_password">
                    <div class="form-resetpassword-success">
                        <i class="entypo-check"></i>
                        <h3>Your password is reset.</h3>
                        <!--<p>Please check your email, reset password link will expire in 24 hours.</p>-->
                    </div>
                    <div class="form-login-error">
                        <h3>Failed</h3>
                        <p>Please Check Password.</p>
                    </div>
                     <div class="form-steps">

                         <div class="step current" id="reset-step-1">

                             <div class="form-group">
                                 <div class="input-group">
                                     <div class="input-group-addon">
                                         <i class="entypo-password"></i>
                                     </div>
                                    <input type="hidden" name="remember_token" id="remember_token" value="{{Input::get('remember_token')}}">
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" data-mask="email" autocomplete="off" />
                                 </div>
                             </div>

                            <div class="form-group">
                                 <div class="input-group">
                                 <div class="input-group-addon">
                                     <i class="entypo-password"></i>
                                 </div>
                                 <input type="password" class="form-control" name="confirmpassword" id="confirmpassword" placeholder="Confirm Password" data-mask="email" autocomplete="off" />
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

                <a href="#">ToS</a>  - <a href="#">Privacy Policy</a>

            </div>

        </div>

    </div>

</div>
@stop