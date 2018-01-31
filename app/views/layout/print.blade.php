<!doctype html>
<html lang="{{App::getLocale()}}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, user-scalable=no">
        @if(Session::get('user_site_configrations.FavIcon')!='')<link href="{{Session::get('user_site_configrations.FavIcon')}}" rel="icon">@endif         
         @if(isset($print_type) && $print_type!='')
         <title>{{$print_type}}</title>
         @endif
        <style>
        .row{
        margin: 0;padding: 0;
        }
        </style>
    </head>

    <body class="page-body gray">
         @yield('content')
    </body>
</html>