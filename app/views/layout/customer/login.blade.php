<!DOCTYPE html>
<html lang="{{App::getLocale()}}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="{{Session::get('user_site_configrations.FavIcon')}}" rel="icon">
        <?php
        $domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $_SERVER['HTTP_HOST']);
        $domainUrl_key = strtoupper(preg_replace('/-+/', '_',$domainUrl_key));
        ?>
        <title>{{cus_lang("THEMES_".$domainUrl_key."_TITLE")}}</title>

         @include('includes.customer.login-css')
         

        <!--[if lt IE 9]><script src="<?php echo URL::to('/'); ?>/assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
       <script type="text/javascript">
           var baseurl = '<?php echo URL::to('/');?>';
           var customer_alignment = '<?php echo $customer_alignment ?>';
           {{js_labels()}}
       </script>
        @if(Session::get('user_site_configrations.CustomCss'))
            <style>
                {{Session::get('user_site_configrations.CustomCss')}}
            </style>
        @endif
    </head>
    <body class="page-body login-page login-form-fall" data-url="">
        <!-- This is needed when you send requests via Ajax -->

              @yield('content')

        
       @include('includes.customer.login-js')

    </body>
</html>