<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, user-scalable=no">
        <title>Neon</title>
        <link href="<?php echo URL::to('/'); ?>/assets/images/favicon.ico" rel="icon">


        <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/font-icons/entypo/css/entypo.css" />
        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic" />
        <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/neon-core.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/neon-theme.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/neon-forms.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/custom.css" />

        @yield('extrajs')
    </head>

    <body class="page-body gray">
         @yield('content')
    </body>
</html>