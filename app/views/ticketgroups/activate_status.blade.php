@extends('layout.login')

@section('content')
<div class="login-container">
<div class="login-header login-caret">

        <div class="login-content">

            <a href="#" class="logo">
                <img src="<?php echo URL::to('/'); ?>/assets/images/logo@2x.png" width="120" alt="" />
            </a>
            <p class="description form-login-<?php if($data['status']=='success'){echo "success";}else if($data['status']=='error'){echo "error";} ?>">{{$data['message']}}.</p>
        </div>

    </div>
  <div class="login-form">
    <div class="login-content">      
      <div class="login-bottom-links"> <a href="{{URL::to('/login')}}" class="link"> <i class="entypo-lock"></i> Return to Login Page </a> <br />
         </div>
    </div>
  </div>
</div>
@stop