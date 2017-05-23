@extends('layout.login')

@section('content')

<div class="login-container">

    <div class="login-header login-caret">

        <div class="login-content">

            <a href="<?php echo URL::to('/'); ?>" class="logo">
                <img src="<?php echo URL::to('/'); ?>/assets/images/logo@2x.png" width="120" alt="" />
            </a>

            <p class="description" style="color:#fff">Select Company!</p>

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

            <form method="post" role="form" id="super_admin_form_login">

				<div class="form-group">

					<div class="input-group">
						<div class="input-group-addon">
							<i class="entypo-user"></i>
						</div>

                          {{Form::select('Company', $companies, Input::old('Company') ,array( "id" => "Company_SA", "class"=>"form-control", "style"=>"background:#373e4a" ))}}

					</div>

				</div>

				<div class="form-group">

					<div class="input-group">
						<div class="input-group-addon">
							<i class="entypo-user"></i>
						</div>

                        <div id="user_drodown_here">

                        </div>

					</div>

				</div>

				<div class="form-group">
					<button type="submit" class="btn btn-primary btn-block btn-login">
						<i class="entypo-login"></i>
						Login
					</button>
				</div>



			</form>


            <div class="login-bottom-links">

                <a href="<?php echo URL::to('forgot_password'); ?>" class="link">Forgot your password?</a>

                <br />



            </div>

        </div>

    </div>

</div>
<script src="<?php echo URL::to('/'); ?>/assets/js/jquery-1.11.0.min.js"></script>
<script>

$(document).ready(function(){
$("#Company_SA").change(function(){
    var selected_company, data, url;
    selected_company = $("#Company_SA").val();
    data = {company: selected_company};

    url = baseurl  +  "/sa_get_user_dropdown/"+selected_company;
    $.get(url,function(data,status){

        //console.log(data);
        $("#user_drodown_here").html(data);


    },'html');



});
});
function getUrlParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
}
</script>
@stop