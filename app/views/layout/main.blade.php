@if(!Request::ajax())
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
         @if(Session::get('user_site_configrations.FavIcon')!='')<link href="{{Session::get('user_site_configrations.FavIcon')}}" rel="icon">@endif         
        <title>{{Session::get('user_site_configrations.Title')}}</title>

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
            {{js_labels()}}
        </script>
        @if(Session::get('user_site_configrations.CustomCss'))
        <style>		
        {{Session::get('user_site_configrations.CustomCss')}}
        </style>
        @endif

        <style>

            input[type="radio"].js {
                display: none;
            }

            .newredio.js {
                display: block;
                float: left;
                margin-right: 10px;
                border: 1px solid #ababab;
                color: #ababab;
                text-align: center;
                padding: 25px;
                height:25%;
                width: 25%;
                cursor: pointer;
            }

            .newredio.js.active {
                border: 1px solid #21a9e1;
                color: #ababab;
                font-weight: bold;
            }

            .newredio i {
                color:green;
            }
        </style>
    </head>

    <body class="page-body gray">

        <div class="page-container">

            @if(Session::get('reseller')==1)
                @include('includes.resellerpanel.sidebar')
            @else
                @include('includes.sidebar')
            @endif


            <div class="main-content">

                @if(Session::get('reseller')==1)
                    @include('includes.resellerpanel.dashboard-top')
                @else
                    @include('includes.dashboard-top')
                @endif
                <div id="content">
                    @yield('content')
                </div>

                @include('includes.footer')

            </div>


            @include('layout.filter')

        </div>

            @yield('footer_ext')

            @include('includes.login-js')

    </body>
</html>
@else
    @yield('content')
@endif