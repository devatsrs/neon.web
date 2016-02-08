@extends('layout.login')

@section('content')

   <div class="login-container">

   	<div class="login-header login-caret">

   		<div class="login-content">

   			<a href="index.html" class="logo">
   				<img src="assets/images/logo@2x.png" width="120" alt="" />
   			</a>

   			<p class="description" style="color: #fff">Create an account, it's free and takes few moments only!</p>

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
                <h3>Problem Registering</h3>
                <p></p>
            </div>
            <div class="form-register-success">
                <i class="entypo-check"></i>
                <h3>You have been successfully registered.</h3>
                <p>We have emailed you the confirmation link for your account.</p>
            </div>
   			<form method="post" role="form" id="form_register">




   						<div class="form-group">
   							<div class="input-group">
   								<div class="input-group-addon">
   									<i class="entypo-user"></i>
   								</div>
   								<input type="text" class="form-control" name="CompanyName" id="CompanyName" placeholder="Company Name" autocomplete="off" />
   							</div>
   						</div>
   						<div class="form-group">
   							<div class="input-group">
   								<div class="input-group-addon">
   									<i class="entypo-user"></i>
   								</div>

   								<input type="text" class="form-control" name="FirstName" id="FirstName" placeholder="First Name" autocomplete="off" />
   							</div>
   						</div>
   						<div class="form-group">
   							<div class="input-group">
   								<div class="input-group-addon">
   									<i class="entypo-user"></i>
   								</div>

   								<input type="text" class="form-control" name="LastName" id="LastName" placeholder="Last Name" autocomplete="off" />
   							</div>
   						</div>

   						<div class="form-group">
   							<div class="input-group">
   								<div class="input-group-addon">
   									<i class="entypo-phone"></i>
   								</div>

   								<input type="text" class="form-control" name="Phone" id="Phone" placeholder="Phone Number" data-mask="phone" autocomplete="off" />
   							</div>
   						</div>

   						<div class="form-group">
   							<div class="input-group">
   								<div class="input-group-addon">
   									<i class="entypo-mail"></i>
   								</div>

   								<input type="text" class="form-control" name="Email" id="Email" data-mask="email" placeholder="E-mail" autocomplete="off" />
   							</div>
   						</div>

   						<div class="form-group">
   							<div class="input-group">
   								<div class="input-group-addon">
   									<i class="entypo-lock"></i>
   								</div>

   								<input type="password" class="form-control" name="Password" id="Password" placeholder="Choose Password" autocomplete="off" />
   							</div>
   						</div>



   						<div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-login">
                                <i class="entypo-right-open-mini"></i>
                                Register
                            </button>
                        </div>



   			</form>


   			<div class="login-bottom-links">

   				<a href="{{URL::to('/login')}}" class="link">
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