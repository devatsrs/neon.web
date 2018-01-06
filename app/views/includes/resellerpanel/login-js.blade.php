
<?php
if ( Request::is('/') || Request::is('login') || Request::is('reseller/login') || Request::is('forgot_password') || Request::is('registration') || Request::is('super_admin') || Request::is('global_user_select_company') ) {
    $js = [        
		"assets/js/jquery-1.11.0.min.js",
        "assets/js/gsap/main-gsap.js",
        "assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js",
        "assets/js/bootstrap.js",
        "assets/js/joinable.js",
        "assets/js/resizeable.js",
        "assets/js/neon-api.js",
        "assets/js/jquery.validate.min.js",
        "assets/js/neon-login.js",
        "assets/js/neon-register.js",
        "assets/js/neon-forgotpassword.js",
        "assets/js/neon-resetpassword.js",
        "assets/js/neon-demo.js",
        "assets/js/jquery.sparkline.min.js",
        "assets/js/rickshaw/vendor/d3.v3.js",
        "assets/js/rickshaw/rickshaw.min.js",
        "assets/js/raphael-min.js",
        "assets/js/morris.min.js",
        "assets/js/toastr.js",
        "assets/js/fullcalendar/fullcalendar.min.js",
        "assets/js/neon-chat.js",

        "assets/js/jquery.inputmask.bundle.min.js",


    ];
}else{  /*if (Request::is('users') || Request::is('users/add') || Request::is('users/edit/*') || Request::is('trunks') || Request::is('trunk/*') || Request::is('trunks/*')  || Request::is('codedecks') ) {*/

    $js = [
        "assets/js/gsap/main-gsap.js",
        "assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js",
        "assets/js/bootstrap.js",
        "assets/js/joinable.js",
        "assets/js/resizeable.js",
        "assets/js/neon-api.js",
        "assets/js/jquery.validate.min.js",
        "assets/js/jquery.dataTables.min.js",

        "assets/js/selectboxit/jquery.selectBoxIt.min.js",   //

        "assets/js/datatables/TableTools.min.js",
        "assets/js/dataTables.bootstrap.js",
        "assets/js/datatables/jquery.dataTables.columnFilter.js",
        "assets/js/datatables/lodash.min.js",
        "assets/js/datatables/responsive/js/datatables.responsive.js",
        "assets/js/select2/select2.min.js",
        "assets/js/neon-chat.js",
        "assets/js/neon-custom.js",
        "assets/js/neon-demo.js",
        "assets/js/bootstrap-switch.min.js",
        "assets/js/jquery.inputmask.bundle.min.js",
        "assets/js/fullcalendar/fullcalendar.min.js",
        "assets/js/toastr.js",  // Popup toaster
        "assets/js/bootstrap-datepicker.js", //Date Picker
        "assets/js/bootstrap-timepicker.min.0.5.2.js", //Date Picker
        "assets/js/icheck/icheck.min.js", //Chebkbox
        "assets/js/datatables/ZeroClipboard.js",
        "assets/js/morris.min.js",
        "assets/js/raphael-min.js",
        "assets/js/jquery.sparkline.min.js",
        "assets/bootstrap3-editable/js/bootstrap-editable.js",
        "assets/js/fileinput.js",
        "assets/js/icheck/icheck.min.js",
        "assets/js/daterangepicker/moment.min.js",
        "assets/js/daterangepicker/daterangepicker.js",

		//New editor
		"assets/js/summernote/summernote.min.js",
		"assets/js/summernote/plugin/neonplaceholder/neonplaceholder.js",

    ];
}
?>
<script>
    var customer = JSON.parse('[{"customer":"{{Session::get('customer')}}"}]');
</script>
@foreach ($js as $addjs)
@if( strstr($addjs,"http") )

<script type="text/javascript" src="{{$addjs}}" ></script>
@else
<script type="text/javascript" src="<?php echo URL::to('/'); ?>/{{$addjs}}" ></script>
@endif
@endforeach