
var gulp    = require("gulp"),

    uglify  = require('gulp-uglify'),

    concat = require('gulp-concat'),
    
    minifyCSS = require('gulp-minify-css'),
    
    prefix = require('gulp-autoprefixer');
    
// Minifies JS
gulp.task('login-js', function(){
    return gulp.src(
        
        [
            "public/assets/js/jquery-1.11.0.min.js",
            "public/assets/js/gsap/main-gsap.js",
            "public/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js",
            "public/assets/js/bootstrap.js",
            "public/assets/js/joinable.js",
            "public/assets/js/resizeable.js",
            "public/assets/js/neon-api.js",
            "public/assets/js/jquery.validate.min.js",
            "public/assets/js/neon-login.js",
            "public/assets/js/neon-register.js",
            "public/assets/js/neon-forgotpassword.js",
            "public/assets/js/neon-resetpassword.js",
            "public/assets/js/neon-demo.js",
            "public/assets/js/jquery.sparkline.min.js",
            "public/assets/js/rickshaw/vendor/d3.v3.js",
            "public/assets/js/rickshaw/rickshaw.min.js",
            "public/assets/js/raphael-min.js",
            "public/assets/js/morris.min.js",
            "public/assets/js/toastr.js",
            "public/assets/js/fullcalendar/fullcalendar.min.js",
            "public/assets/js/neon-chat.js",

            "public/assets/js/jquery.inputmask.bundle.min.js",
        ]

    )
   .pipe(concat("login-js.js"))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))
});

// css 
gulp.task('login-styles', function(){
    return gulp.src(
        
        [
            "public/assets/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css",
            "public/assets/css/font-icons/entypo/css/entypo.css",
            "public/assets/css/font-icons/font-awesome/css/font-awesome.css",
            "https://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic",
            // Dashboard
            "public/assets/js/rickshaw/rickshaw.min.css",
            "public/assets/css/bootstrap.css",
            "public/assets/css/neon-core.css",
            "public/assets/css/neon-theme.css",
            "public/assets/css/neon-forms.css",
            "public/assets/css/custom.css",
            "public/assets/css/dark-bottom.css",
            'public/assets/css/skins/black.css'
    
        ]

    )
    .pipe(concat('login-css.css'))
    .pipe(minifyCSS())
    .pipe(prefix('last 2 versions'))
    .pipe(gulp.dest('public/css'))
});

 


// Minifies JS
gulp.task('main-js', function(){
    return gulp.src(
        
        [
            "public/assets/js/gsap/main-gsap.js",
            "public/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js",
            "public/assets/js/bootstrap.js",
            "public/assets/js/joinable.js",
            "public/assets/js/resizeable.js",
            "public/assets/js/neon-api.js",
            "public/assets/js/jquery.validate.min.js",
            "public/assets/js/jquery.dataTables.min.js",
            "public/assets/js/selectboxit/jquery.selectBoxIt.min.js",   //

            "public/assets/js/datatables/TableTools.min.js",
            "public/assets/js/dataTables.bootstrap.js",
            "public/assets/js/datatables/jquery.dataTables.columnFilter.js",
            "public/assets/js/datatables/lodash.min.js",
            "public/assets/js/datatables/responsive/js/datatables.responsive.js",
            "public/assets/js/select2/select2.min.js",
            "public/assets/js/neon-chat.js",
            "public/assets/js/neon-custom.js",
            "public/assets/js/neon-demo.js",
            "public/assets/js/bootstrap-switch.min.js",
            "public/assets/js/jquery.inputmask.bundle.min.js",
            "public/assets/js/fullcalendar/fullcalendar.min.js",
            "public/assets/js/toastr.js",  // Popup toaster
            "public/assets/js/bootstrap-datepicker.js", //Date Picker
            "public/assets/js/bootstrap-timepicker.min.0.5.2.js", //Date Picker
            "public/assets/js/icheck/icheck.min.js", //Chebkbox
            "public/assets/js/datatables/ZeroClipboard.js",
            "public/assets/js/morris.min.js",
            "public/assets/js/raphael-min.js",
            "public/assets/js/jquery.sparkline.min.js",
            "public/assets/bootstrap3-editable/js/bootstrap-editable.js",
            "public/assets/js/fileinput.js",
            "public/assets/js/icheck/icheck.min.js",
            "public/assets/js/typeahead.min.js",
            "public/assets/js/bootstrap-colorpicker.min.js",
            "public/assets/js/Knob/dist/jquery.knob.min.js",
            "public/assets/js/perfectScroll/js/perfect-scrollbar.jquery.min.js",
            "public/assets/js/odometer/odometer.js",
            "public/assets/js/daterangepicker/moment.min.js",
            "public/assets/js/daterangepicker/daterangepicker.js",
             ///New editor
            "public/assets/js/summernote/summernote.min.js",
            "public/assets/js/summernote/plugin/neonplaceholder/neonplaceholder.js",

            //vendor rate and rate table
            "public/assets/js/jquery.dataTables.1.10.15.min.js"
            ]

    )
   .pipe(concat("main-js.js"))
    .pipe(uglify())
    .pipe(gulp.dest('public/js'))
});

// css 
gulp.task('main-styles', function(){
    return gulp.src(
        
        [
            "public/assets/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css",
            "public/assets/css/font-icons/entypo/css/entypo.css",
            "public/assets/css/font-icons/font-awesome/css/font-awesome.css",
            "https://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic",
            "public/assets/css/bootstrap.css",
            "public/assets/css/neon-core.css",
            "public/assets/css/neon-theme.css",
            "public/assets/css/neon-forms.css",
            "public/assets/js/datatables/responsive/css/datatables.responsive.css",
            "public/assets/js/select2/select2-bootstrap.css",
            "public/assets/js/select2/select2.css",
            "public/assets/js/selectboxit/jquery.selectBoxIt.css",
            "public/assets/bootstrap3-editable/css/bootstrap-editable.css",
            "public/assets/js/icheck/skins/minimal/_all.css",
            "public/assets/js/perfectScroll/css/perfect-scrollbar.css",
            "public/assets/js/odometer/themes/odometer-theme-default.css",	
            "public/assets/js/daterangepicker/daterangepicker.css",
            // New editor
            "public/assets/js/summernote/summernote.css",
            "public/assets/css/custom.css",
            "public/assets/css/dark-bottom.css",
            "public/assets/css/dark-bottom.css",
            'public/assets/css/skins/black.css'
    
        ]

    )
    .pipe(concat('main-css.css'))
    .pipe(minifyCSS())
    .pipe(prefix('last 2 versions'))
    .pipe(gulp.dest('public/css'))
});



gulp.task('default', function() {
    gulp.run('login-styles')
    gulp.run('login-js');
    
    gulp.run('main-styles')
    gulp.run('main-js');
    
 });
