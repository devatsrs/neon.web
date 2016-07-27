<?php
if ( Request::is('/') || Request::is('login') || Request::is('forgot_password') || Request::is('dashboard') || Request::is('registration') || Request::is('super_admin') || Request::is('global_user_select_company') ||  Request::is('reset_password')) {

    $css = [

        "assets/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css",
        "/assets/css/font-icons/entypo/css/entypo.css",
        "http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic",
        "/assets/css/bootstrap.css",
        "/assets/css/neon-core.css",
        "/assets/css/neon-theme.css",
        "/assets/css/neon-forms.css",
        "/assets/css/custom.css",
// Dashboard
        "assets/js/jvectormap/jquery-jvectormap-1.2.2.css",
        "assets/js/rickshaw/rickshaw.min.css",




    ];
}else{ /*if ( Request::is('users') || Request::is('users/add') || Request::is('users/edit/*') || Request::is('trunks') || Request::is('trunk/*') || Request::is('trunks/*')  || Request::is('codedecks') ) {*/

    $css = [

        "assets/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css",
        "assets/css/font-icons/entypo/css/entypo.css",
        "assets/css/font-icons/font-awesome/css/font-awesome.css",
        "http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic",
        "assets/css/bootstrap.css",
        "assets/css/neon-core.css",
        "assets/css/neon-theme.css",
        "assets/css/neon-forms.css",
        "assets/css/custom.css",
        "assets/js/datatables/responsive/css/datatables.responsive.css",
        "assets/js/select2/select2-bootstrap.css",
        "assets/js/select2/select2.css",
        "assets/js/selectboxit/jquery.selectBoxIt.css",
        "assets/bootstrap3-editable/css/bootstrap-editable.css",
        "assets/js/icheck/skins/minimal/_all.css",
		"assets/js/perfectScroll/css/perfect-scrollbar.css",
		"assets/js/odometer/themes/odometer-theme-default.css",	
		"assets/js/daterangepicker/daterangepicker-bs3.css"	
		


    ];
}
?>
@foreach ($css as $addcss)
@if( strstr($addcss,"http"))
<link rel="stylesheet" type="text/css" href="{{$addcss}}" />
@else
<link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/{{$addcss}}" />
@endif
@endforeach

