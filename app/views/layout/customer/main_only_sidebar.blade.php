@if(!Request::ajax())
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if(Session::get('user_site_configrations.FavIcon')!='')<link href="{{Session::get('user_site_configrations.FavIcon')}}" rel="icon">@endif
    <?php
    $domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $_SERVER['HTTP_HOST']);
    $domainUrl_key = strtoupper(preg_replace('/-+/', '_',$domainUrl_key));
    ?>
    <title>{{cus_lang("THEMES_".$domainUrl_key."_TITLE")}}</title>

    @include('includes.login-css')


        <!--[if lt IE 9]><script src="<?php echo URL::to('/'); ?>/assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="<?php echo URL::to('/'); ?>/assets/js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript">
        var baseurl = '<?php echo URL::to('/'); ?>';
        var MSG_DATA_NOT_AVAILABLE = '{{cus_lang("MESSAGE_DATA_NOT_AVAILABLE")}}';
        var TABLE_TOTAL = '{{cus_lang("TABLE_TOTAL")}}';
        var BUTTON_EXPORT_CSV_CAPTION = '{{cus_lang("BUTTON_EXPORT_CSV_CAPTION")}}';
        var BUTTON_EXPORT_EXCEL_CAPTION = '{{cus_lang("BUTTON_EXPORT_EXCEL_CAPTION")}}';
    </script>

    @if(Session::get('user_site_configrations.CustomCss'))
        <style>
            {{Session::get('user_site_configrations.CustomCss')}}
        </style>
    @endif

</head>

<body class="page-body">

<div class="page-container">
    @if(Session::get('reseller')==1)
        @include('includes.resellerpanel.sidebar')
    @else
        @include('includes.customer.sidebar')
    @endif

    <div class="main-content">

        <div id="content">
            @yield('content')
        </div>

        @include('includes.customer.footer')

    </div>

</div>

@yield('footer_ext')

@include('includes.customer.login-js')

</body>
</html>
@else
    @yield('content')
@endif