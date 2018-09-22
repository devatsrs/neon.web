/**
 *	Neon Main JavaScript File
 *
 *	Theme by: www.laborator.co
 **/

var public_vars = public_vars || {};
var data_table ;
/* Dev updated */
toastr_opts = {
    "closeButton": true,
    "debug": false,
    "positionClass": "toast-top-right",
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

(function($, window, undefined) {

    "use strict";

    $(document).ready(function()
    {
		
		$(document).on('click','[redirecto]',function(){
    var url = $(this).attr('redirecto');
    window.location.href=url;
});

        // Sidebar Menu var
        public_vars.$body = $("body");
        public_vars.$pageContainer = public_vars.$body.find(".page-container");
        public_vars.$chat = public_vars.$pageContainer.find('#chat');
        public_vars.$horizontalMenu = public_vars.$pageContainer.find('header.navbar');
        public_vars.$sidebarMenu = public_vars.$pageContainer.find('.sidebar-menu');
        public_vars.$mainMenu = public_vars.$sidebarMenu.find('#main-menu');
        public_vars.$mainContent = public_vars.$pageContainer.find('.main-content');
        public_vars.$sidebarUserEnv = public_vars.$sidebarMenu.find('.sidebar-user-info');
        public_vars.$sidebarUser = public_vars.$sidebarUserEnv.find('.user-link');
        public_vars.$sidebarUser=
            public_vars.$body.addClass('loaded');

        // Just to make sure...
        $(window).on('error', function(ev)
        {
            // Do not let page without showing if JS fails somewhere
            init_page_transitions();
        });

        if (public_vars.$pageContainer.hasClass('right-sidebar'))
        {
            public_vars.isRightSidebar = true;
        }




        // Sidebar Menu Setup
        setup_sidebar_menu();




        // Horizontal Menu Setup
        setup_horizontal_menu();




        // Sidebar Collapse icon
        public_vars.$sidebarMenu.find(".sidebar-collapse-icon").on('click', function(ev)
        {
            ev.preventDefault();

            var with_animation = $(this).hasClass('with-animation');

            toggle_sidebar_menu(with_animation);
        });




        // Mobile Sidebar Collapse icon
        public_vars.$sidebarMenu.find(".sidebar-mobile-menu a").on('click', function(ev)
        {
            ev.preventDefault();

            var with_animation = $(this).hasClass('with-animation');

            if (with_animation)
            {
                public_vars.$mainMenu.stop().slideToggle('normal', function()
                {
                    public_vars.$mainMenu.css('height', 'auto');
                });
            }
            else
            {
                public_vars.$mainMenu.toggle();
            }
        });




        // Mobile Horizontal Menu Collapse icon
        public_vars.$horizontalMenu.find(".horizontal-mobile-menu a").on('click', function(ev)
        {
            ev.preventDefault();

            var $menu = public_vars.$horizontalMenu.find('.navbar-nav'),
                with_animation = $(this).hasClass('with-animation');

            if (with_animation)
            {
                $menu.stop().slideToggle('normal', function()
                {
                    $menu.attr('height', 'auto');

                    if ($menu.css('display') == 'none')
                    {
                        $menu.attr('style', '');
                    }
                });
            }
            else
            {
                $menu.toggle();
            }
        });




        // Close Sidebar if Tablet Screen is visible
        public_vars.$sidebarMenu.data('initial-state', (public_vars.$pageContainer.hasClass('sidebar-collapsed') ? 'closed' : 'open'));

        if (is('tabletscreen'))
        {
            hide_sidebar_menu(false);
        }




        // NiceScroll
        if ($.isFunction($.fn.niceScroll))
        {
            var nicescroll_defaults = {
                cursorcolor: '#d4d4d4',
                cursorborder: '1px solid #ccc',
                railpadding: {right: 3},
                cursorborderradius: 1,
                autohidemode: true,
                sensitiverail: true
            };

            public_vars.$body.find('.dropdown .scroller').niceScroll(nicescroll_defaults);

            $(".dropdown").on("shown.bs.dropdown", function()
            {
                $(".scroller").getNiceScroll().resize();
                $(".scroller").getNiceScroll().show();
            });

            var fixed_sidebar = $(".sidebar-menu.fixed");

            if (fixed_sidebar.length == 1)
            {
                var fs_tm = 0;

                fixed_sidebar.niceScroll({
                    cursorcolor: '#454a54',
                    cursorborder: '1px solid #454a54',
                    railpadding: {right: 3},
                    railalign: 'right',
                    cursorborderradius: 1
                });

                fixed_sidebar.on('click', 'li a', function()
                {
                    fixed_sidebar.getNiceScroll().resize();
                    fixed_sidebar.getNiceScroll().show();

                    window.clearTimeout(fs_tm);

                    fs_tm = setTimeout(function()
                    {
                        fixed_sidebar.getNiceScroll().resize();
                    }, 500);
                });
            }
        }




        // Scrollable
        if ($.isFunction($.fn.slimScroll))
        {
            $(".scrollable").each(function(i, el)
            {
                var $this = $(el),
                    height = attrDefault($this, 'height', $this.height());

                if ($this.is(':visible'))
                {
                    $this.removeClass('scrollable');

                    if ($this.height() < parseInt(height, 10))
                    {
                        height = $this.outerHeight(true) + 10;
                    }

                    $this.addClass('scrollable');
                }

                $this.css({maxHeight: ''}).slimScroll({
                    height: height,
                    position: attrDefault($this, 'scroll-position', 'right'),
                    color: attrDefault($this, 'rail-color', '#000'),
                    size: attrDefault($this, 'rail-width', 6),
                    borderRadius: attrDefault($this, 'rail-radius', 3),
                    opacity: attrDefault($this, 'rail-opacity', .3),
                    alwaysVisible: parseInt(attrDefault($this, 'autohide', 1), 10) == 1 ? false : true
                });
            });
            public_vars.$body.find('.dropdown .scroller').each(function(i, el) {
                var $this = $(el),
                    height = attrDefault($this, 'height', $this.height());
                $this.slimScroll({
                    height: height,
                    position: attrDefault($this, 'scroll-position', 'right'),
                    color: attrDefault($this, 'rail-color', '#000'),
                    size: attrDefault($this, 'rail-width', 6),
                    borderRadius: attrDefault($this, 'rail-radius', 3),
                    opacity: attrDefault($this, 'rail-opacity', .3),
                    alwaysVisible: parseInt(attrDefault($this, 'autohide', 1), 10) == 1 ? false : true
                });
            });

        }




        // Panels

        // Added on v1.1.4 - Fixed collapsing effect with panel tables
        $(".panel-heading").each(function(i, el)
        {
            var $this = $(el),
                $body = $this.next('table');

            $body.wrap('<div class="panel-body with-table"></div>');

            $body = $this.next('.with-table').next('table');
            $body.wrap('<div class="panel-body with-table"></div>');

        });

        continueWrappingPanelTables();
        // End of: Added on v1.1.4


        $('body').on('click', '.panel > .panel-heading > .panel-options > a[data-rel="reload"]', function(ev)
        {
            ev.preventDefault();

            var $this = jQuery(this).closest('.panel');

            blockUI($this);
            $this.addClass('reloading');

            setTimeout(function()
            {
                unblockUI($this)
                $this.removeClass('reloading');

            }, 900);

        }).on('click', '.panel > .panel-heading > .panel-options > a[data-rel="close"]', function(ev)
        {
            ev.preventDefault();

            var $this = $(this),
                $panel = $this.closest('.panel');

            var t = new TimelineLite({
                onComplete: function()
                {
                    $panel.slideUp(function()
                    {
                        $panel.remove();
                    });
                }
            });

            t.append(TweenMax.to($panel, .2, {css: {scale: 0.95}}));
            t.append(TweenMax.to($panel, .5, {css: {autoAlpha: 0, transform: "translateX(100px) scale(.95)"}}));

        }).on('click', '.panel > .panel-heading > .panel-options > a[data-rel="collapse"]', function(ev)
        {
            ev.preventDefault();

            var $this = $(this),
                $panel = $this.closest('.panel'),
                $body = $panel.children('.panel-body, .table'),
                do_collapse = !$panel.hasClass('panel-collapse');

            if ($panel.is('[data-collapsed="1"]'))
            {
                $panel.attr('data-collapsed', 0);
                $body.hide();
                do_collapse = false;
            }

            if (do_collapse)
            {
                $body.slideUp('normal', fit_main_content_height);
                $panel.addClass('panel-collapse');
            }
            else
            {
                $body.slideDown('normal', fit_main_content_height);
                $panel.removeClass('panel-collapse');
            }
        });




        // Data Toggle for Radio and Checkbox Elements
        $('[data-toggle="buttons-radio"]').each(function()
        {
            var $buttons = $(this).children();

            $buttons.each(function(i, el)
            {
                var $this = $(el);

                $this.click(function(ev)
                {
                    $buttons.removeClass('active');
                });
            });
        });

        $('[data-toggle="buttons-checkbox"]').each(function()
        {
            var $buttons = $(this).children();

            $buttons.each(function(i, el)
            {
                var $this = $(el);

                $this.click(function(ev)
                {
                    $this.removeClass('active');
                });
            });
        });

        /*$('[data-loading-text]').each(function(i, el) // Temporary for demo purpose only
         {
         var $this = $(el);
         $this.button('reset');
         $this.on('click', function(ev)
         {
         $this.button('loading');
         //setTimeout(function(){ $this.button('reset'); }, 1800);
         });
         });*/
        $('body').on('click', '[data-loading-text]', function() {
            var curnt_obj = $(this);
            var formId
            $("form").submit(function (e) {
                formId = this.id;  // "this" is a reference to the submitted form
            });
            setTimeout(function(){
                if(formId){
                    curnt_obj.button('reset');
                    curnt_obj.button('loading');
                }
            },5);

        });




        // Popovers and tooltips
        $('[data-toggle="popover"]').each(function(i, el)
        {
            var $this = $(el),
                placement = attrDefault($this, 'placement', 'right'),
                trigger = attrDefault($this, 'trigger', 'click'),
                popover_class = $this.hasClass('popover-secondary') ? 'popover-secondary' : ($this.hasClass('popover-primary') ? 'popover-primary' : ($this.hasClass('popover-default') ? 'popover-default' : ''));

            $this.popover({
                placement: placement,
                trigger: trigger
            });

            $this.on('shown.bs.popover', function(ev)
            {
                var $popover = $this.next();

                $popover.addClass(popover_class);
            });
        });
        $("body").tooltip({
            selector: '[data-toggle="tooltip"]'
        });
		$('[data-toggle="tooltip"]').click(function() {
            $('.tooltip').fadeOut('fast', function() {
                $('.tooltip').remove();
            });
        });
        $('[data-toggle="tooltip"]').each(function(i, el)
        {
            var $this = $(el),
                placement = attrDefault($this, 'placement', 'top'),
                trigger = attrDefault($this, 'trigger', 'hover'),
                popover_class = $this.hasClass('tooltip-secondary') ? 'tooltip-secondary' : ($this.hasClass('tooltip-primary') ? 'tooltip-primary' : ($this.hasClass('tooltip-default') ? 'tooltip-default' : ''));

            $this.tooltip({
                placement: placement,
                trigger: trigger
            });

            $this.on('shown.bs.tooltip', function(ev)
            {
                var $tooltip = $this.next();

                $tooltip.addClass(popover_class);
            });
        });




        // jQuery Knob
        if ($.isFunction($.fn.knob))
        {
            $(".knob").knob({
                change: function(value) {
                },
                release: function(value) {
                },
                cancel: function() {
                },
                draw: function() {

                    if (this.$.data('skin') == 'tron') {

                        var a = this.angle(this.cv) // Angle
                            ,
                            sa = this.startAngle // Previous start angle
                            ,
                            sat = this.startAngle // Start angle
                            ,
                            ea // Previous end angle
                            , eat = sat + a // End angle
                            ,
                            r = 1;

                        this.g.lineWidth = this.lineWidth;

                        this.o.cursor && (sat = eat - 0.3) && (eat = eat + 0.3);

                        if (this.o.displayPrevious) {
                            ea = this.startAngle + this.angle(this.v);
                            this.o.cursor && (sa = ea - 0.3) && (ea = ea + 0.3);
                            this.g.beginPath();
                            this.g.strokeStyle = this.pColor;
                            this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
                            this.g.stroke();
                        }

                        this.g.beginPath();
                        this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
                        this.g.stroke();

                        this.g.lineWidth = 2;
                        this.g.beginPath();
                        this.g.strokeStyle = this.o.fgColor;
                        this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
                        this.g.stroke();

                        return false;
                    }
                }
            });
        }




        // Slider
        if ($.isFunction($.fn.slider))
        {
            $(".slider").each(function(i, el)
            {
                var $this = $(el),
                    $label_1 = $('<span class="ui-label"></span>'),
                    $label_2 = $label_1.clone(),
                    orientation = attrDefault($this, 'vertical', 0) != 0 ? 'vertical' : 'horizontal',
                    prefix = attrDefault($this, 'prefix', ''),
                    postfix = attrDefault($this, 'postfix', ''),
                    fill = attrDefault($this, 'fill', ''),
                    $fill = $(fill),
                    step = attrDefault($this, 'step', 1),
                    value = attrDefault($this, 'value', 5),
                    min = attrDefault($this, 'min', 0),
                    max = attrDefault($this, 'max', 100),
                    min_val = attrDefault($this, 'min-val', 10),
                    max_val = attrDefault($this, 'max-val', 90),
                    is_range = $this.is('[data-min-val]') || $this.is('[data-max-val]'),
                    reps = 0;


                // Range Slider Options
                if (is_range)
                {
                    $this.slider({
                        range: true,
                        orientation: orientation,
                        min: min,
                        max: max,
                        values: [min_val, max_val],
                        step: step,
                        slide: function(e, ui)
                        {
                            var opts = $this.data('uiSlider').options,
                                min_val = (prefix ? prefix : '') + ui.values[0] + (postfix ? postfix : ''),
                                max_val = (prefix ? prefix : '') + ui.values[1] + (postfix ? postfix : '');

                            $label_1.html(min_val);
                            $label_2.html(max_val);

                            if (fill)
                                $fill.val(min_val + ',' + max_val);

                            reps++;
                        },
                        change: function(ev, ui)
                        {
                            if (reps == 1)
                            {
                                var opts = $this.data('uiSlider').options,
                                    min_val = (prefix ? prefix : '') + ui.values[0] + (postfix ? postfix : ''),
                                    max_val = (prefix ? prefix : '') + ui.values[1] + (postfix ? postfix : '');

                                $label_1.html(min_val);
                                $label_2.html(max_val);

                                if (fill)
                                    $fill.val(min_val + ',' + max_val);
                            }

                            reps = 0;
                        }
                    });

                    var $handles = $this.find('.ui-slider-handle');

                    $label_1.html((prefix ? prefix : '') + min_val + (postfix ? postfix : ''));
                    $handles.first().append($label_1);

                    $label_2.html((prefix ? prefix : '') + max_val + (postfix ? postfix : ''));
                    $handles.last().append($label_2);
                }
                // Normal Slider
                else
                {

                    $this.slider({
                        range: attrDefault($this, 'basic', 0) ? false : "min",
                        orientation: orientation,
                        min: min,
                        max: max,
                        value: value,
                        step: step,
                        slide: function(ev, ui)
                        {
                            var opts = $this.data('uiSlider').options,
                                val = (prefix ? prefix : '') + opts.value + (postfix ? postfix : '');

                            $label_1.html(val);

                            if (fill)
                                $fill.val(val);

                            reps++;
                        },
                        change: function(ev, ui)
                        {
                            if (reps == 1)
                            {
                                var opts = $this.data('uiSlider').options,
                                    val = (prefix ? prefix : '') + opts.value + (postfix ? postfix : '');

                                $label_1.html(val);

                                if (fill)
                                    $fill.val(val);
                            }

                            reps = 0;
                        }
                    });

                    var $handles = $this.find('.ui-slider-handle');
                    //$fill = $('<div class="ui-fill"></div>');

                    $label_1.html((prefix ? prefix : '') + value + (postfix ? postfix : ''));
                    $handles.html($label_1);

                    //$handles.parent().prepend( $fill );

                    //$fill.width($handles.get(0).style.left);
                }

            })
        }




        // Radio Toggle
        if ($.isFunction($.fn.bootstrapSwitch))
        {

            $('.make-switch.is-radio').on('switch-change', function() {
                $('.make-switch.is-radio').bootstrapSwitch('toggleRadioState');
            });
        }




        // Select2 Dropdown replacement
        if ($.isFunction($.fn.select2))
        {
            $(".select2").each(function(i, el) {
                buildselect2(el);
            }).promise().done(function(){
                $('.select2').css('visibility','visible');
            });

            if ($.isFunction($.fn.perfectScrollbar))
            {
                nicescroll();
            }

        }

        function formatState (state) {
            console.log(state);
            if (!state.id) { return state.text; }
            var $state = $(
                '<span><img src="vendor/images/flags/' + state.element.value.toLowerCase() + '.png" class="img-flag" /> ' + state.text + '</span>'
            );
            return $state;
        };

        function addUserPic (opt) {
            console.log(opt);
            if (!opt.id) {
                return opt.text;
            }
            var optimage = $(opt.element).data('image');
            if(!optimage){
                return opt.text;
            } else {
                var $opt = $(
                    '<span class="userName"><i class="entypo-plus"></i>' + $(opt.element).text() + '</span>'
                );
                return $opt;
            }
        };



        // SelectBoxIt Dropdown replacement
        if ($.isFunction($.fn.selectBoxIt))
        {
            $("select.selectboxit").each(function(i, el)
            {
                var $this = $(el),
                    opts = {
                        showFirstOption: attrDefault($this, 'first-option', true),
                        'native': attrDefault($this, 'native', false),
                        defaultText: attrDefault($this, 'text', '')
                    };

                $this.addClass('visible');
                $this.selectBoxIt(opts);
            });
        }




        // Auto Size for Textarea
        if ($.isFunction($.fn.autosize))
        {
            $("textarea.autogrow, textarea.autosize").autosize();
        }




        // Tagsinput
        if ($.isFunction($.fn.tagsinput))
        {
            $(".tagsinput").tagsinput();
        }




        // Typeahead
        if ($.isFunction($.fn.typeahead))
        {
            $(".typeahead").each(function(i, el)
            {
                var $this = $(el),
                    opts = {
                        name: $this.attr('name') ? $this.attr('name') : ($this.attr('id') ? $this.attr('id') : 'tt')
                    };

                if ($this.hasClass('tagsinput'))
                    return;

                if ($this.data('local'))
                {
                    var local = $this.data('local');

                    local = local.replace(/\s*,\s*/g, ',').split(',');

                    opts['local'] = local;
                }

                if ($this.data('prefetch'))
                {
                    var prefetch = $this.data('prefetch');

                    opts['prefetch'] = prefetch;
                }

                if ($this.data('remote'))
                {
                    var remote = $this.data('remote');

                    opts['remote'] = remote;
                }

                if ($this.data('template'))
                {
                    var template = $this.data('template');

                    opts['template'] = template;
                    opts['engine'] = Hogan;
                }

                $this.typeahead(opts);
            });
        }




        // Datepicker
        if ($.isFunction($.fn.datepicker))
        {
            $(".datepicker").each(function(i, el)
            {
                var $this = $(el),
                    opts = {
                        //format: attrDefault($this, 'format', 'dd/mm/yyyy'),
                        startDate: attrDefault($this, 'startdate', ''),
                        endDate: attrDefault($this, 'enddate', ''),
                        daysOfWeekDisabled: attrDefault($this, 'disableddays', ''),
                        startView: attrDefault($this, 'startview', 0),
                        rtl: rtl()
                    },
                    $n = $this.next(),
                    $p = $this.prev();

                $this.datepicker(opts);

                if ($n.is('.input-group-addon') && $n.has('a'))
                {
                    $n.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.datepicker('show');
                    });
                }

                if ($p.is('.input-group-addon') && $p.has('a'))
                {
                    $p.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.datepicker('show');
                    });
                }
            });
        }




        // Timepicker
        if ($.isFunction($.fn.timepicker))
        {
            $(".timepicker").each(function(i, el)
            {
                var $this = $(el),
                    opts = {
                        template: attrDefault($this, 'template', false),
                        showSeconds: attrDefault($this, 'showSeconds', false),
                        defaultTime: attrDefault($this, 'defaultTime', 'current'),
                        showMeridian: attrDefault($this, 'showMeridian', true),
                        minuteStep: attrDefault($this, 'minuteStep', 15),
                        secondStep: attrDefault($this, 'secondStep', 15)
                    },
                    $n = $this.next(),
                    $p = $this.prev();

                $this.timepicker(opts);

                if ($n.is('.input-group-addon') && $n.has('a'))
                {
                    $n.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.timepicker('showWidget');
                    });
                }

                if ($p.is('.input-group-addon') && $p.has('a'))
                {
                    $p.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.timepicker('showWidget');
                    });
                }
            });
        }




        // Colorpicker
        if ($.isFunction($.fn.colorpicker))
        {
            $(".colorpicker").each(function(i, el)
            {
                var $this = $(el),
                    opts = {
                        //format: attrDefault($this, 'format', false)
                    },
                    $n = $this.next(),
                    $p = $this.prev(),
                    $preview = $this.siblings('.input-group-addon').find('.color-preview');

                $this.colorpicker(opts);

                if ($n.is('.input-group-addon') && $n.has('a'))
                {
                    $n.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.colorpicker('show');
                    });
                }

                if ($p.is('.input-group-addon') && $p.has('a'))
                {
                    $p.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.colorpicker('show');
                    });
                }

                if ($preview.length)
                {
                    $this.on('changeColor', function(ev) {

                        $preview.css('background-color', ev.color.toHex());
                    });

                    if ($this.val().length)
                    {
                        $preview.css('background-color', $this.val());
                    }
                }
            });
        }




        // Date Range Picker
        if ($.isFunction($.fn.daterangepicker))
        {
            $(".daterange").each(function(i, el)
            {
                // Change the range as you desire
                var ranges = {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                };

                var $this = $(el),
                    opts = {
                        locale:{
                            format: attrDefault($this, 'format', 'MM/DD/YYYY'),
                            separator: attrDefault($this, 'separator', ' - ')
                        },
                        timePicker: attrDefault($this, 'timePicker', false),
                        timePickerIncrement: attrDefault($this, 'timePickerIncrement', false),
                        timePicker24Hour:attrDefault($this, 'timePicker24Hour', true),
                        timePickerSeconds:attrDefault($this, 'timePickerSeconds', true),
                        autoApply:attrDefault($this, 'autoApply', true),
                        autoUpdateInput: false

                    },
                    min_date = attrDefault($this, 'minDate', ''),
                    max_date = attrDefault($this, 'maxDate', ''),
                    start_date = attrDefault($this, 'startDate', ''),
                    end_date = attrDefault($this, 'endDate', '');

                if ($this.hasClass('add-ranges'))
                {
                    opts['ranges'] = ranges;
                }

                if (min_date.length)
                {
                    opts['minDate'] = min_date;
                }

                if (max_date.length)
                {
                    opts['maxDate'] = max_date;
                }

                if (start_date.length)
                {
                    opts['startDate'] = start_date;
                }

                if (end_date.length)
                {
                    opts['endDate'] = end_date;
                }


                $this.daterangepicker(opts, function(start, end)
                {
                    var drp = $this.data('daterangepicker');

                    if ($this.is('[data-callback]'))
                    {
                        //daterange_callback(start, end);
                        callback_test(start, end);
                    }

                    if ($this.hasClass('daterange-inline'))
                    {
                        $this.find('span').html(start.format(drp.format) + drp.separator + end.format(drp.format));
                    }
                });
            });
            $('.daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(attrDefault($(this), 'format', 'MM/DD/YYYY')) + ' - ' + picker.endDate.format(attrDefault($(this), 'format', 'MM/DD/YYYY')));
            });

            $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }




        // Input Mask
        if ($.isFunction($.fn.inputmask))
        {
            $("[data-mask]").each(function(i, el)
            {
                var $this = $(el),
                    mask = $this.data('mask').toString(),
                    opts = {
                        numericInput: attrDefault($this, 'numeric', false),
                        radixPoint: attrDefault($this, 'radixPoint', ''),
                        rightAlignNumerics: attrDefault($this, 'numericAlign', 'left') == 'right'
                    },
                    placeholder = attrDefault($this, 'placeholder', ''),
                    is_regex = attrDefault($this, 'isRegex', '');


                if (placeholder.length)
                {
                    opts[placeholder] = placeholder;
                }

                switch (mask.toLowerCase())
                {
                    case "phone":
                        mask = "(999) 999-9999 99999";
                        break;

                    case "currency":
                    case "rcurrency":

                        var sign = attrDefault($this, 'sign', '$');
                        ;

                        mask = "999,999,999.99";

                        if ($this.data('mask').toLowerCase() == 'rcurrency')
                        {
                            mask += ' ' + sign;
                        }
                        else
                        {
                            mask = sign + ' ' + mask;
                        }

                        opts.numericInput = true;
                        opts.rightAlignNumerics = false;
                        opts.radixPoint = '.';
                        break;

                    case "email":
                        mask = 'Regex';
                        opts.regex = "[a-zA-Z0-9._%-]+@[a-zA-Z0-9-]+\\.[a-zA-Z]{2,4}";
                        break;

                    case "fdecimal":
                        mask = 'decimal';
                        $.extend(opts, {
                            autoGroup: true,
                            groupSize: 3,
                            radixPoint: attrDefault($this, 'rad', '.'),
                            groupSeparator: attrDefault($this, 'dec', ',')
                        });
                }

                if (is_regex)
                {
                    opts.regex = mask;
                    mask = 'Regex';
                }

                $this.inputmask(mask, opts);
            });
        }




        // Form Validation
        if ($.isFunction($.fn.validate))
        {
            $("form.validate").each(function(i, el)
            {
                var $this = $(el),
                    opts = {
                        rules: {},
                        messages: {},
                        errorElement: 'span',
                        errorClass: 'validate-has-error',
                        highlight: function(element) {
                            $(element).closest('.form-group').addClass('validate-has-error');
                        },
                        unhighlight: function(element) {
                            $(element).closest('.form-group').removeClass('validate-has-error');
                        },
                        errorPlacement: function(error, element)
                        {
                            if (element.closest('.has-switch').length)
                            {
                                error.insertAfter(element.closest('.has-switch'));
                            }
                            else
                            if (element.parent('.checkbox, .radio').length || element.parent('.input-group').length)
                            {
                                error.insertAfter(element.parent());
                            }
                            else
                            {
                                error.insertAfter(element);
                            }
                        }
                    },
                    $fields = $this.find('[data-validate]');


                $fields.each(function(j, el2)
                {
                    var $field = $(el2),
                        name = $field.attr('name'),
                        validate = attrDefault($field, 'validate', '').toString(),
                        _validate = validate.split(',');

                    for (var k in _validate)
                    {
                        var rule = _validate[k],
                            params,
                            message;

                        if (typeof opts['rules'][name] == 'undefined')
                        {
                            opts['rules'][name] = {};
                            opts['messages'][name] = {};
                        }

                        if ($.inArray(rule, ['required', 'url', 'email', 'number', 'date', 'creditcard']) != -1)
                        {
                            opts['rules'][name][rule] = true;

                            message = $field.data('message-' + rule);

                            if (message)
                            {
                                opts['messages'][name][rule] = message;
                            }
                        }
                        // Parameter Value (#1 parameter)
                        else
                        if (params = rule.match(/(\w+)\[(.*?)\]/i))
                        {
                            if ($.inArray(params[1], ['min', 'max', 'minlength', 'maxlength', 'equalTo']) != -1)
                            {
                                opts['rules'][name][params[1]] = params[2];


                                message = $field.data('message-' + params[1]);

                                if (message)
                                {
                                    opts['messages'][name][params[1]] = message;
                                }
                            }
                        }
                    }
                });

                //console.log(opts);
                $this.validate(opts);
            });
        }




        // Replaced File Input
        $("input.file2[type=file]").each(function(i, el)
        {
            var $this = $(el),
                label = attrDefault($this, 'label', 'Browse');

            $this.bootstrapFileInput(label);
        });




        // Jasny Bootstrap | Fileinput
        if ($.isFunction($.fn.fileinput))
        {
            $(".fileinput").fileinput()
        }




        // Multi-select
        if ($.isFunction($.fn.multiSelect))
        {
            $(".multi-select").multiSelect();
        }




        // Form Wizard
        if ($.isFunction($.fn.bootstrapWizard))
        {
            $(".form-wizard").each(function(i, el)
            {
                var $this = $(el),
                    $progress = $this.find(".steps-progress div"),
                    _index = $this.find('> ul > li.active').index();

                // Validation
                var checkFormWizardValidaion = function(tab, navigation, index)
                {
                    if ($this.hasClass('validate'))
                    {
                        var $valid = $this.valid();

                        if (!$valid)
                        {
                            $this.data('validator').focusInvalid();
                            return false;
                        }
                    }

                    return true;
                };


                $this.bootstrapWizard({
                    tabClass: "",
                    onTabShow: function($tab, $navigation, index)
                    {
                        setCurrentProgressTab($this, $navigation, $tab, $progress, index);
                    },
                    onNext: checkFormWizardValidaion,
                    onTabClick: checkFormWizardValidaion
                });

                $this.data('bootstrapWizard').show(_index);

                /*$(window).on('neon.resize', function()
                 {
                 $this.data('bootstrapWizard').show( _index );
                 });*/
            });
        }




        // Wysiwyg Editor
        if ($.isFunction($.fn.wysihtml5))
        {
            $(".wysihtml5").each(function(i, el)
            {
                var $this = $(el),
                    stylesheets = attrDefault($this, 'stylesheet-url', '')

                $(".wysihtml5").wysihtml5({
                    stylesheets: stylesheets.split(',')
                });
            });
        }




        // CKeditor WYSIWYG
        if ($.isFunction($.fn.ckeditor))
        {
            $(".ckeditor").ckeditor({
                contentsLangDirection: rtl() ? 'rtl' : 'ltr'
            });
        }




        // Checkbox/Radio Replacement
        replaceCheckboxes();




        // Tile Progress
        $(".tile-progress").each(function(i, el)
        {
            var $this = $(el),
                $pct_counter = $this.find('.pct-counter'),
                $progressbar = $this.find('.tile-progressbar span'),
                percentage = parseFloat($progressbar.data('fill')),
                pct_len = percentage.toString().length;

            if (typeof scrollMonitor == 'undefined')
            {
                $progressbar.width(percentage + '%');
                $pct_counter.html(percentage);
            }
            else
            {
                var tile_progress = scrollMonitor.create(el);

                tile_progress.fullyEnterViewport(function() {
                    $progressbar.width(percentage + '%');
                    tile_progress.destroy();

                    var o = {pct: 0};
                    TweenLite.to(o, 1, {pct: percentage, ease: Quint.easeInOut, onUpdate: function()
                    {
                        var pct_str = o.pct.toString().substring(0, pct_len);

                        $pct_counter.html(pct_str);
                    }
                    });
                });
            }
        });




        // Tile Stats
        $(".tile-stats").each(function(i, el)
        {
            var $this = $(el),
                $num = $this.find('.num'),
                start = attrDefault($num, 'start', 0),
                end = attrDefault($num, 'end', 0),
                prefix = attrDefault($num, 'prefix', ''),
                postfix = attrDefault($num, 'postfix', ''),
                duration = attrDefault($num, 'duration', 1000),
                delay = attrDefault($num, 'delay', 1000);

            if (start < end)
            {
                if (typeof scrollMonitor == 'undefined')
                {
                    $num.html(prefix + end + postfix);
                }
                else
                {
                    var tile_stats = scrollMonitor.create(el);

                    tile_stats.fullyEnterViewport(function() {

                        var o = {curr: start};

                        TweenLite.to(o, duration / 1000, {curr: end, ease: Power1.easeInOut, delay: delay / 1000, onUpdate: function()
                        {
                            $num.html(prefix + Math.round(o.curr) + postfix);
                        }
                        });

                        tile_stats.destroy()
                    });
                }
            }
        });




        // Tocify Table
        if ($.isFunction($.fn.tocify) && $("#toc").length)
        {
            $("#toc").tocify({
                context: '.tocify-content',
                selectors: "h2,h3,h4,h5"
            });


            var $this = $(".tocify"),
                watcher = scrollMonitor.create($this.get(0));

            $this.width($this.parent().width());

            watcher.lock();

            watcher.stateChange(function()
            {
                $($this.get(0)).toggleClass('fixed', this.isAboveViewport)
            });
        }



        // Modal Static
        public_vars.$body.on('click', '.modal[data-backdrop="static"]', function(ev)
        {
            if ($(ev.target).is('.modal'))
            {
                var $modal_dialog = $(this).find('.modal-dialog .modal-content'),
                    tt = new TimelineMax({paused: true});

                tt.append(TweenMax.to($modal_dialog, .1, {css: {scale: 1.1}, ease: Expo.easeInOut}));
                tt.append(TweenMax.to($modal_dialog, .3, {css: {scale: 1}, ease: Back.easeOut}));

                tt.play();
            }
        });


        // Added on v1.1

        // Sidebar User Links Popup
        if (public_vars.$sidebarUserEnv.length)
        {
            var $su_normal = public_vars.$sidebarUserEnv.find('.sui-normal'),
                $su_hover = public_vars.$sidebarUserEnv.find('.sui-hover');

            if ($su_normal.length && $su_hover.length)
            {
                public_vars.$sidebarUser.on('click', function(ev)
                {
                    ev.preventDefault();
                    $su_hover.addClass('visible');
                });

                $su_hover.on('click', '.close-sui-popup', function(ev)
                {
                    ev.preventDefault();
                    $su_hover.addClass('going-invisible');
                    $su_hover.removeClass('visible');

                    setTimeout(function() {
                        $su_hover.removeClass('going-invisible');
                    }, 220);
                });
            }
        }
        // End of: Added on v1.1


        // Added on v1.1.4
        $(".input-spinner").each(function(i, el)
        {
            var $this = $(el),
                $minus = $this.find('button:first'),
                $plus = $this.find('button:last'),
                $input = $this.find('input'),
                minus_step = attrDefault($minus, 'step', -1),
                plus_step = attrDefault($minus, 'step', 1),
                min = attrDefault($input, 'min', null),
                max = attrDefault($input, 'max', null);


            $this.find('button').on('click', function(ev)
            {
                ev.preventDefault();

                var $this = $(this),
                    val = $input.val(),
                    step = attrDefault($this, 'step', $this[0] == $minus[0] ? -1 : 1);

                if (!step.toString().match(/^[0-9-\.]+$/))
                {
                    step = $this[0] == $minus[0] ? -1 : 1;
                }

                if (!val.toString().match(/^[0-9-\.]+$/))
                {
                    val = 0;
                }

                $input.val(parseFloat(val) + step).trigger('keyup');
            });

            $input.keyup(function()
            {
                if (min != null && parseFloat($input.val()) < min)
                {
                    $input.val(min);
                }
                else

                if (max != null && parseFloat($input.val()) > max)
                {
                    $input.val(max);
                }
            });

        });


        // Search Results Tabs
        var $search_results_env = $(".search-results-env");

        if ($search_results_env.length)
        {
            var $sr_nav_tabs = $search_results_env.find(".nav-tabs li"),
                $sr_tab_panes = $search_results_env.find('.search-results-panes .search-results-pane');

            $sr_nav_tabs.find('a').on('click', function(ev)
            {
                ev.preventDefault();

                var $this = $(this),
                    $tab_pane = $sr_tab_panes.filter($this.attr('href'));

                $sr_nav_tabs.not($this.parent()).removeClass('active');
                $this.parent().addClass('active');

                $sr_tab_panes.not($tab_pane).fadeOut('fast', function()
                {
                    $tab_pane.fadeIn('fast');
                });
            });
        }
        // End of: Added on v1.1.4




        // Fit main content height
        fit_main_content_height();

        var fmch = 0,
            fmch_fn = function() {

                window.clearTimeout(fmch);
                fit_main_content_height();

                fmch = setTimeout(fmch_fn, 800);
            };

        fmch_fn();


        // Apply Page Transition
        onPageAppear(init_page_transitions);

        $('input.icheck').iCheck({
            checkboxClass: 'icheckbox_minimal',
            radioClass: 'iradio_minimal'
        });


        $("#user_language ul li").click(function(){
            var language=$(this).attr("lang-key");

            $.ajax({
                url: baseurl + "/translate/change/"+language,
                type: 'POST',
                dataType: "json",
                success:function(data) {
                    location.reload();
                },
                cache: false
            });
        });
        $("#user_language.language-selector .dropdown-toggle img").attr("src",$("#user_language ul li.active img").attr("src"));
        $("#user_language.language-selector .dropdown-toggle span").html($("#user_language ul li.active span").html());
        if(typeof customer_alignment!="undefined" && customer_alignment=="right"){
            $('.pull-right, .pull-left').addClass('flip');
        }

    });



    // Enable/Disable Resizable Event
    var wid = 0;

    $(window).resize(function() {
        clearTimeout(wid);
        wid = setTimeout(trigger_resizable, 200);
    });

		 // odometer
        if ($.isFunction($.fn.Odometer) && $(".odometer").length)
        { 
			window.odometerOptions = {
			  format: '(ddd).dd'
			};
			 var od = new Odometer({
			  el: $(".odometer"),
			  value: 0
			});
        }
    $(".ddl_language").select2({
        formatResult: format,
        formatSelection: format,
        escapeMarkup: function(m) { return m; },
        minimumResultsForSearch: -1
    });
})(jQuery, window);

/* Functions */
function format(state) {
    var img = $(state.element).data('flag');
    if(img==""){
        return state.text;
    }
    return "<div class='lang_div'><img class='lang_flag' src='"+ baseurl+ "/assets/images/flag/" + img + "' />" + state.text + "</div>";
}
function buildselect2(el){
    var $this = $(el),
        opts = {
            allowClear: attrDefault($this, 'allowClear', false),
            formatResult: function(item) {
                if(item.id=='select2-add'){
                    return '<span class="select2-add"><i class="entypo-plus-circled"></i>'+item.text+'</span>';
                }
                return '<span class="select2-match"></span>'+ item.text ;
            }
        };
    if($this.hasClass('small')){
        opts['minimumResultsForSearch'] = attrDefault($this, 'allowClear', Infinity);
        opts['dropdownCssClass'] = attrDefault($this, 'allowClear', 'no-search');
    }
    if($this.hasClass('select2add')){
        $this.prepend('<option value="select2-add" disabled="disabled">Add</option>');
    }

    $this.select2(opts);
    if($this.hasClass('small')){
        $this.select2('container').find('.select2-search').addClass ('hidden') ;
    }
}

function nicescroll(){
    $(".select2-results").niceScroll({
        cursorcolor: '#d4d4d4',
        cursorborder: '1px solid #ccc',
        railpadding: {right: 3}
    });
}


function fit_main_content_height()
{
    var $ = jQuery;

    if (public_vars.$sidebarMenu.length && public_vars.$sidebarMenu.hasClass('fixed') == false)
    {
        public_vars.$sidebarMenu.css('min-height', '');
        public_vars.$mainContent.css('min-height', '');

        if (isxs())
        {
            public_vars.$sidebarMenu.css('display','block');
            public_vars.$mainContent.css('display','inherit');

            if (typeof fit_calendar_container_height != 'undefined')
                reset_calendar_container_height();
            return;
        }else{
            public_vars.$sidebarMenu.css('display','table-cell');
            public_vars.$mainContent.css('display','table-cell');
        }

        var sm_height = public_vars.$sidebarMenu.outerHeight(),
            mc_height = public_vars.$mainContent.outerHeight(),
            doc_height = $(document).height(),
            win_height = $(window).height(),
            sm_height_real = 0;

        if (win_height > doc_height)
        {
            doc_height = win_height;
        }

        if (public_vars.$horizontalMenu.length > 0)
        {
            var hm_height = public_vars.$horizontalMenu.outerHeight();

            doc_height -= hm_height;
            sm_height -= hm_height;
        }

        public_vars.$mainContent.css('min-height', doc_height);
        public_vars.$sidebarMenu.css('min-height', doc_height);
        public_vars.$chat.css('min-height', doc_height);

        if (typeof fit_mail_container_height != 'undefined')
            fit_mail_container_height();

        if (typeof fit_calendar_container_height != 'undefined')
            fit_calendar_container_height();
    }
}


// Sidebar Menu Setup
function setup_sidebar_menu()
{
    var $ = jQuery,
        $items_with_submenu = public_vars.$sidebarMenu.find('li:has(ul)'),
        submenu_options = {
            submenu_open_delay: 0.5,
            submenu_open_easing: Sine.easeInOut,
            submenu_opened_class: 'opened'
        },
        root_level_class = 'root-level',
        is_multiopen = public_vars.$mainMenu.hasClass('multiple-expanded');

    public_vars.$mainMenu.find('> li').addClass(root_level_class);

    $items_with_submenu.each(function(i, el)
    {
        var $this = $(el),
            $link = $this.find('> a'),
            $submenu = $this.find('> ul');

        $this.addClass('has-sub');

        $link.click(function(ev)
        {
            ev.preventDefault();

            if (!is_multiopen && $this.hasClass(root_level_class))
            {
                var close_submenus = public_vars.$mainMenu.find('.' + root_level_class).not($this).find('> ul');

                close_submenus.each(function(i, el)
                {
                    var $sub = $(el);
                    menu_do_collapse($sub, $sub.parent(), submenu_options);
                });
            }

            if (!$this.hasClass(submenu_options.submenu_opened_class))
            {
                var current_height;

                if (!$submenu.is(':visible'))
                {
                    menu_do_expand($submenu, $this, submenu_options);
                }
            }
            else
            {
                menu_do_collapse($submenu, $this, submenu_options);
            }

            fit_main_content_height();
        });

    });

    // Open the submenus with "opened" class
    public_vars.$mainMenu.find('.' + submenu_options.submenu_opened_class + ' > ul').addClass('visible');

    // Well, somebody may forgot to add "active" for all inhertiance, but we are going to help you (just in case) - we do this job for you for free :P!
    if (public_vars.$mainMenu.hasClass('auto-inherit-active-class'))
    {
        menu_set_active_class_to_parents(public_vars.$mainMenu.find('.active'));
    }

    // Search Input
    var $search_input = public_vars.$mainMenu.find('#search input[type="text"]'),
        $search_el = public_vars.$mainMenu.find('#search');

    public_vars.$mainMenu.find('#search form').submit(function(ev)
    {
        var is_collapsed = public_vars.$pageContainer.hasClass('sidebar-collapsed');

        if (is_collapsed)
        {
            if ($search_el.hasClass('focused') == false)
            {
                ev.preventDefault();
                $search_el.addClass('focused');

                $search_input.focus();

                return false;
            }
        }
    });

    $search_input.on('blur', function(ev)
    {
        var is_collapsed = public_vars.$pageContainer.hasClass('sidebar-collapsed');

        if (is_collapsed)
        {
            $search_el.removeClass('focused');
        }
    });


    // Collapse Icon (mobile device visible)
    var show_hide_menu = $('');

    public_vars.$sidebarMenu.find('.logo-env').append(show_hide_menu);
}


function menu_do_expand($submenu, $this, options)
{
    $submenu.addClass('visible').height('');
    current_height = $submenu.outerHeight();

    var props_from = {
            opacity: .2,
            height: 0,
            top: -20
        },
        props_to = {
            height: current_height,
            opacity: 1,
            top: 0
        };

    if (isxs())
    {
        delete props_from['opacity'];
        delete props_from['top'];

        delete props_to['opacity'];
        delete props_to['top'];
    }

    TweenMax.set($submenu, {css: props_from});

    $this.addClass(options.submenu_opened_class);

    TweenMax.to($submenu, options.submenu_open_delay, {css: props_to, ease: options.submenu_open_easing, onComplete: function()
    {
        $submenu.attr('style', '');
        fit_main_content_height();
    }});
}


function menu_do_collapse($submenu, $this, options)
{
    if (public_vars.$pageContainer.hasClass('sidebar-collapsed') && $this.hasClass('root-level'))
    {
        return;
    }

    $this.removeClass(options.submenu_opened_class);

    TweenMax.to($submenu, options.submenu_open_delay, {css: {height: 0, opacity: .2}, ease: options.submenu_open_easing, onComplete: function()
    {
        $submenu.removeClass('visible');
        fit_main_content_height();
    }});
}


function menu_set_active_class_to_parents($active_element)
{
    if ($active_element.length)
    {
        var $parent = $active_element.parent().parent();

        $parent.addClass('active');

        if (!$parent.hasClass('root-level'))
            menu_set_active_class_to_parents($parent)
    }
}



// Horizontal Menu Setup
function setup_horizontal_menu()
{
    var $ = jQuery,
        $nav_bar_menu = public_vars.$horizontalMenu.find('.navbar-nav'),
        $items_with_submenu = $nav_bar_menu.find('li:has(ul)'),
        $search = public_vars.$horizontalMenu.find('li#search'),
        $search_input = $search.find('.search-input'),
        $search_submit = $search.find('form'),
        root_level_class = 'root-level'
    is_multiopen = $nav_bar_menu.hasClass('multiple-expanded'),
        submenu_options = {
            submenu_open_delay: 0.5,
            submenu_open_easing: Sine.easeInOut,
            submenu_opened_class: 'opened'
        };

    $nav_bar_menu.find('> li').addClass(root_level_class);

    $items_with_submenu.each(function(i, el)
    {
        var $this = $(el),
            $link = $this.find('> a'),
            $submenu = $this.find('> ul');

        $this.addClass('has-sub');

        setup_horizontal_menu_hover($this, $submenu);

        // xs devices only
        $link.click(function(ev)
        {
            if (isxs())
            {
                ev.preventDefault();

                if (!is_multiopen && $this.hasClass(root_level_class))
                {
                    var close_submenus = $nav_bar_menu.find('.' + root_level_class).not($this).find('> ul');

                    close_submenus.each(function(i, el)
                    {
                        var $sub = $(el);
                        menu_do_collapse($sub, $sub.parent(), submenu_options);
                    });
                }

                if (!$this.hasClass(submenu_options.submenu_opened_class))
                {
                    var current_height;

                    if (!$submenu.is(':visible'))
                    {
                        menu_do_expand($submenu, $this, submenu_options);
                    }
                }
                else
                {
                    menu_do_collapse($submenu, $this, submenu_options);
                }

                fit_main_content_height();
            }
        });

    });


    // Search Input
    if ($search.hasClass('search-input-collapsed'))
    {
        $search_submit.submit(function(ev)
        {
            if ($search.hasClass('search-input-collapsed'))
            {
                ev.preventDefault();
                $search.removeClass('search-input-collapsed');
                $search_input.focus();

                return false;
            }
        });

        $search_input.on('blur', function(ev)
        {
            $search.addClass('search-input-collapsed');
        });
    }
}

jQuery(public_vars, {
    hover_index: 4
});

function setup_horizontal_menu_hover($item, $sub)
{
    var del = 0.5,
        trans_x = -10,
        ease = Quad.easeInOut;

    TweenMax.set($sub, {css: {autoAlpha: 0, transform: "translateX(" + trans_x + "px)"}});

    $item.hoverIntent({
        over: function()
        {
            if (isxs())
                return false;

            if ($sub.css('display') == 'none')
            {
                $sub.css({display: 'block', visibility: 'hidden'});
            }

            $sub.css({zIndex: ++public_vars.hover_index});
            TweenMax.to($sub, del, {css: {autoAlpha: 1, transform: "translateX(0px)"}, ease: ease});
        },
        out: function()
        {
            if (isxs())
                return false;

            TweenMax.to($sub, del, {css: {autoAlpha: 0, transform: "translateX(" + trans_x + "px)"}, ease: ease, onComplete: function()
            {
                TweenMax.set($sub, {css: {transform: "translateX(" + trans_x + "px)"}});
                $sub.css({display: 'none'});
            }});
        },
        timeout: 300,
        interval: 50
    });

}



// Block UI Helper
function blockUI($el)
{
    $el.block({
        message: '',
        css: {
            border: 'none',
            padding: '0px',
            backgroundColor: 'none'
        },
        overlayCSS: {
            backgroundColor: '#fff',
            opacity: .3,
            cursor: 'wait'
        }
    });
}

function unblockUI($el)
{
    $el.unblock();
}


// Element Attribute Helper
function attrDefault($el, data_var, default_val)
{
    if (typeof $el.data(data_var) != 'undefined')
    {
        return $el.data(data_var);
    }

    return default_val;
}



// Test function
function callback_test()
{
    alert("Callback function executed! No. of arguments: " + arguments.length + "\n\nSee console log for outputed of the arguments.");

    console.log(arguments);
}


// Root Wizard Current Tab
function setCurrentProgressTab($rootwizard, $nav, $tab, $progress, index)
{
    $tab.prevAll().addClass('completed');
    $tab.nextAll().removeClass('completed');

    var items = $nav.children().length,
        pct = parseInt((index + 1) / items * 100, 10),
        $first_tab = $nav.find('li:first-child'),
        margin = (1 / (items * 2) * 100) + '%';//$first_tab.find('span').position().left + 'px';

    if ($first_tab.hasClass('active'))
    {
        $progress.width(0);
    }
    else
    {
        if (rtl())
        {
            $progress.width($progress.parent().outerWidth(true) - $tab.prev().position().left - $tab.find('span').width() / 2);
        }
        else
        {
            $progress.width(((index - 1) / (items - 1)) * 100 + '%'); //$progress.width( $tab.prev().position().left - $tab.find('span').width()/2 );
        }
    }


    $progress.parent().css({
        marginLeft: margin,
        marginRight: margin
    });

    /*var m = $first_tab.find('span').position().left - $first_tab.find('span').width() / 2;

     $rootwizard.find('.tab-content').css({
     marginLeft: m,
     marginRight: m
     });*/
}


// Replace Checkboxes
function replaceCheckboxes()
{
    var $ = jQuery;

    $(".checkbox-replace:not(.neon-cb-replacement), .radio-replace:not(.neon-cb-replacement)").each(function(i, el)
    {
        var $this = $(el),
            $input = $this.find('input:first'),
            $wrapper = $('<label class="cb-wrapper" />'),
            $checked = $('<div class="checked" />'),
            checked_class = 'checked',
            is_radio = $input.is('[type="radio"]'),
            $related,
            name = $input.attr('name');


        $this.addClass('neon-cb-replacement');


        $input.wrap($wrapper);

        $wrapper = $input.parent();

        $wrapper.append($checked).next('label').on('click', function(ev)
        {
            $wrapper.click();
        });

        $input.on('change', function(ev)
        {
            if (is_radio)
            {
                //$(".neon-cb-replacement input[type=radio][name='"+name+"']").closest('.neon-cb-replacement').removeClass(checked_class);
                $(".neon-cb-replacement input[type=radio][name='" + name + "']:not(:checked)").closest('.neon-cb-replacement').removeClass(checked_class);
            }

            if ($input.is(':disabled'))
            {
                $wrapper.addClass('disabled');
            }

            $this[$input.is(':checked') ? 'addClass' : 'removeClass'](checked_class);

        }).trigger('change');
    });
}



// Scroll to Bottom
function scrollToBottom($el)
{
    var $ = jQuery;

    if (typeof $el == 'string')
        $el = $($el);

    $el.get(0).scrollTop = $el.get(0).scrollHeight;
}


// Check viewport visibility (entrie element)
function elementInViewport(el)
{
    var top = el.offsetTop;
    var left = el.offsetLeft;
    var width = el.offsetWidth;
    var height = el.offsetHeight;

    while (el.offsetParent) {
        el = el.offsetParent;
        top += el.offsetTop;
        left += el.offsetLeft;
    }

    return (
    top >= window.pageYOffset &&
    left >= window.pageXOffset &&
    (top + height) <= (window.pageYOffset + window.innerHeight) &&
    (left + width) <= (window.pageXOffset + window.innerWidth)
    );
}

// X Overflow
function disableXOverflow()
{
    public_vars.$body.addClass('overflow-x-disabled');
}

function enableXOverflow()
{
    public_vars.$body.removeClass('overflow-x-disabled');
}


// Page Transitions
function init_page_transitions()
{
    fit_main_content_height();

    var transitions = ['page-fade', 'page-left-in', 'page-right-in', 'page-fade-only'];

    for (var i in transitions)
    {
        var transition_name = transitions[i];

        if (public_vars.$body.hasClass(transition_name))
        {
            public_vars.$body.addClass(transition_name + '-init')

            setTimeout(function()
            {
                public_vars.$body.removeClass(transition_name + ' ' + transition_name + '-init');

            }, 850);

            return;
        }
    }
}


// Page Visibility API
function onPageAppear(callback)
{

    var hidden, state, visibilityChange;

    if (typeof document.hidden !== "undefined")
    {
        hidden = "hidden";
        visibilityChange = "visibilitychange";
        state = "visibilityState";
    }
    else if (typeof document.mozHidden !== "undefined")
    {
        hidden = "mozHidden";
        visibilityChange = "mozvisibilitychange";
        state = "mozVisibilityState";
    }
    else if (typeof document.msHidden !== "undefined")
    {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
        state = "msVisibilityState";
    }
    else if (typeof document.webkitHidden !== "undefined")
    {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
        state = "webkitVisibilityState";
    }

    if (document[state] || typeof document[state] == 'undefined')
    {
        callback();
    }

    document.addEventListener(visibilityChange, callback, false);
}


function continueWrappingPanelTables()
{
    var $tables = jQuery(".panel-body.with-table + table");

    if ($tables.length)
    {
        $tables.wrap('<div class="panel-body with-table"></div>');
        continueWrappingPanelTables();
    }
}


function show_loading_bar(options)
{
    var defaults = {
        pct: 0,
        delay: 1.3,
        wait: 0,
        before: function() {
        },
        finish: function() {
        },
        resetOnEnd: true
    };

    if (typeof options == 'object')
        defaults = jQuery.extend(defaults, options);
    else
    if (typeof options == 'number')
        defaults.pct = options;


    if (defaults.pct > 100)
        defaults.pct = 100;
    else
    if (defaults.pct < 0)
        defaults.pct = 0;

    var $ = jQuery,
        $loading_bar = $(".neon-loading-bar");

    if ($loading_bar.length == 0)
    {
        $loading_bar = $('<div class="neon-loading-bar progress-is-hidden"><span data-pct="0"></span></div>');
        public_vars.$body.append($loading_bar);
    }

    var $pct = $loading_bar.find('span'),
        current_pct = $pct.data('pct'),
        is_regress = current_pct > defaults.pct;


    defaults.before(current_pct);

    TweenMax.to($pct, defaults.delay, {css: {width: defaults.pct + '%'}, delay: defaults.wait, ease: is_regress ? Expo.easeOut : Expo.easeIn,
        onStart: function()
        {
            $loading_bar.removeClass('progress-is-hidden');
        },
        onComplete: function()
        {
            var pct = $pct.data('pct');

            if (pct == 100 && defaults.resetOnEnd)
            {
                hide_loading_bar();
            }

            defaults.finish(pct);
        },
        onUpdate: function()
        {
            $pct.data('pct', parseInt($pct.get(0).style.width, 10));
        }});
}

function hide_loading_bar()
{
    var $ = jQuery,
        $loading_bar = $(".neon-loading-bar"),
        $pct = $loading_bar.find('span');

    $loading_bar.addClass('progress-is-hidden');
    $pct.width(0).data('pct');
}

//Dev Custom Functions

String.prototype.nl2br = function()
{
    return this.replace(/<br>/g, "\r");

}

function ShowToastr(type, message) {

    var opts = {
        "closeButton": true,
        "debug": false,
        "positionClass": rtl() || public_vars.$pageContainer.hasClass('right-sidebar') ? "toast-top-left" : "toast-top-right ",
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    if (type == "success") {
        toastr.success(message, "Success", opts);
    } else {
        toastr.error(message, "Error", opts);
    }

}
//@TODO: Pass dataType in function
function showAjaxScript(url, formData, FnSucces, dataType) {
    if (typeof(dataType)==='undefined') dataType = 'json';
    $.ajax({
        url: url, //Server script to process data
        type: 'POST',
        dataType: dataType,
        success: FnSucces,
        error: function(error) {
            $(".btn").button('reset');
            ShowToastr("error", error);
        },
        // Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });
}

function showJobAjaxModal(id)
{
    jQuery('#modal-job').modal('show', {backdrop: 'static'});

    jQuery('#modal-job .modal-body').html("Content is loading...");
    $.ajax({
        url: baseurl + "/jobs/" + id + "/show",
        success: function(response)
        {
            jQuery('#modal-job .modal-body').html(response);
            jobID = id;
            jobRead(jobID);

        }
    });
}

function showEmailMessageAjaxModal(id)
{
    jQuery('#modal-mailmsg').modal('show', {backdrop: 'static'});

    jQuery('#modal-mailmsg .modal-body').html("Content is loading...");
    $.ajax({
        url: baseurl + "/emailmessages/" + id + "/show",
        success: function(response)
        {
            jQuery('#modal-mailmsg .modal-body').html(response);
            jobID = id;
            jobRead(jobID);

        }
    });
}
function showAjaxModal(ajaxurl, modalID)
{
    modalID = '#' + modalID;
    jQuery(modalID).modal('show', {backdrop: 'static'});

    jQuery(modalID + ' .modal-body').html("Content is loading...");
    $.ajax({
        url: baseurl + ajaxurl,
        success: function(response)
        {
            jQuery(modalID + ' .modal-body').html(response);
        }
    });
}
if (typeof TableTools != 'undefined') {

    /* dataTable Export Button */
    var data_table_extra_params  = [];

    TableTools.BUTTONS.download = {
        "sAction": "text",
        "sTag": "default",
        "sFieldBoundary": "",
        "sFieldSeperator": "\t",
        "sNewLine": "<br>",
        "sToolTip": "",
        "sButtonClass": "DTTT_button_text",
        "sButtonClassHover": "DTTT_button_text_hover",
        "sButtonText": "Download",
        "mColumns": "all",
        "bHeader": true,
        "bFooter": true,
        "sDiv": "",
        "fnMouseover": null,
        "fnMouseout": null,
        "fnClick": function(nButton, oConfig) {

            var oParams = this.s.dt.oApi._fnAjaxParameters(this.s.dt);
            var aoPost = [
                //{"name": "hello", "value": "world"}
            ];
            var aoGet = [];

            /* Create an IFrame to do the request */
            nIFrame = document.createElement('iframe');
            nIFrame.setAttribute('id', 'RemotingIFrame');
            nIFrame.style.border = '0px';
            nIFrame.style.width = '0px';
            nIFrame.style.height = '0px';

            document.body.appendChild(nIFrame);
            var nContentWindow = nIFrame.contentWindow;
            nContentWindow.document.open();
            nContentWindow.document.close();

            var nForm = nContentWindow.document.createElement('form');
            nForm.setAttribute('method', 'post');

            /* Add POST data */
            for (var i = 0; i < aoPost.length; i++)
            {
                nInput = nContentWindow.document.createElement('input');
                nInput.setAttribute('name', aoPost[i].name);
                nInput.setAttribute('type', 'text');
                nInput.value = aoPost[i].value;
                nForm.appendChild(nInput);
            }
            //var params = data_table.oApi._fnAjaxParameters(data_table.dataTable().fnSettings());
            var params = this.s.dt.oApi._fnAjaxParameters(this.s.dt);
            //console.log(params);

            /* Add All Params data to the URL */
            var sUrlAddition = '';
            for (var i = 0; i < params.length; i++)
            {
                sUrlAddition += params[i].name + '=' + params[i].value + '&';
            }
            for (var i = 0; i < data_table_extra_params.length; i++)
            {
                sUrlAddition += data_table_extra_params[i].name + '=' + data_table_extra_params[i].value + '&';
            }
            if(oConfig.sButtonClass == 'save-collection sageexport') {
                if (confirm('Do you want to change the status of selected invoices to Paid?')) {
                    sUrlAddition += 'MarkPaid=1&';
                }
            }
            //console.log(sUrlAddition);
            /* Add GET data to the URL */
            //var sUrlAddition = '';
            for (var i = 0; i < aoGet.length; i++)
            {
                sUrlAddition += aoGet[i].name + '=' + aoGet[i].value + '&';
            }
            console.log(oConfig.sUrl + '?' + sUrlAddition);

            nForm.setAttribute('action', oConfig.sUrl + '?' + sUrlAddition);

            /* Add the form and the iframe */
            nContentWindow.document.body.appendChild(nForm);

            /* Send the request */
            nForm.submit();
        },
        "fnSelect": null,
        "fnComplete": null,
        "fnInit": null
    };
}

/*
 * Reset Job Counter onClick
 * */
bindResetCounter = function(){
    $('.dropdown-toggle.jobs').on('click', function (e) {
        /* Load Data only when Dropdown open */
        if(!$(this).parent().hasClass("open")){
            reloadJobsDrodown(1);
            // Remove this function resetJobsAlertCounter();
        }
    });
}

bindResetCounterEmailMsgs = function(){
    $('.dropdown-toggle.msgs').on('click', function (e) {
        /* Load Data only when Dropdown open */
        if(!$(this).parent().hasClass("open")){
            reloadMsgDrodown(1);
        }
    });
}

/*
 * Reset the new job Alert Counter to 0
 * */
resetJobsAlertCounter = function(){
    $.get( baseurl + "/jobs/reset", function( data ) {
        //  reloadJobsDrodown(1);
    });

};

/*
 * Reload Dashboard Top Jobs Dropdown
 * */
reloadJobsDrodown = function(reset){
    if( typeof reset == 'undefined' ){
        reset = 0;
    }
    if(customer[0].customer!=1){
        $.get( baseurl + "/jobs/loadDashboardJobsDropDown?reset="+reset, function( data ) {

            $( ".notifications.jobs.dropdown" ).html( data );

            //Add Scroller
            if ($.isFunction($.fn.niceScroll))
            {
                public_vars.$body.find('.dropdown .scroller').niceScroll({
                    cursorcolor: '#d4d4d4',
                    cursorborder: '1px solid #ccc',
                    railpadding: {right: 3},
                    cursorborderradius: 1,
                    autohidemode: true,
                    sensitiverail: true
                });
            }
            bindResetCounter();
        });
    }
	
};

checkFailingCronJob = function(){

    var timeDelay=30;//minutes
    var today = new Date();
    var isFirstTime=false;

    if(getCookie("lastCronJobCheckingDate")==""){
        setCookie("lastCronJobCheckingDate",today,365);
        isFirstTime=true;
    }
    var oldDate = new Date(getCookie("lastCronJobCheckingDate"));
    var diffMs = (today - oldDate);
    var diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000);
    //console.log("diffMins "+diffMins+" isf "+isFirstTime);
    if((diffMins>timeDelay || isFirstTime) && typeof customer[0].customer != 'undefined' &&  customer[0].customer != 1){
        $.get( baseurl + "/cronjobs/check_failing", function( response ) {
            if(typeof response.message != 'undefined' ) {
                setCookie("lastCronJobCheckingDate",today,365);
                if (response.message == '') {
                    setCookie("CronJobNotifications","true",365);
                    $(".notifications.cron_jobs.dropdown").find("#failing_placeholder").addClass("hidden");
                } else {
                    setCookie("CronJobNotifications","false",365);
                    $(".notifications.cron_jobs.dropdown").find("#failing_placeholder").removeClass("hidden");
                }
            }
        });
    }else{
        if (getCookie("CronJobNotifications")=="true") {
            $(".notifications.cron_jobs.dropdown").find("#failing_placeholder").addClass("hidden");
        } else {
            $(".notifications.cron_jobs.dropdown").find("#failing_placeholder").removeClass("hidden");
        }
    }
};
try{
    setTimeout(function(){ reloadJobsDrodown(0); reloadMsgDrodown(0); }, 2000);
    bindResetCounter();
	bindResetCounterEmailMsgs();
}catch(er){

}


reloadMsgDrodown = function(reset){ return false;
	 if(customer[0].customer!=1){
        $.get( baseurl + "/loadDashboardMsgsDropDown?reset="+reset, function( data ) {

            $( ".notifications.msgs.dropdown" ).html( data );

            //Add Scroller
            if ($.isFunction($.fn.niceScroll))
            {
                public_vars.$body.find('.dropdown .scroller').niceScroll({
                    cursorcolor: '#d4d4d4',
                    cursorborder: '1px solid #ccc',
                    railpadding: {right: 3},
                    cursorborderradius: 1,
                    autohidemode: true,
                    sensitiverail: true
                });
            }
			bindResetCounterEmailMsgs();
        });		
    }
	
};

/*
 * Ajax: Job Read
 * */
jobRead = function(id){
    if( id > 0 ){
        $.get( baseurl + "/jobs/"+id+"/jobRead", function( data ) {
            reloadJobsDrodown();
        });
    }
};

/* common ajax error handler */

$( document ).ajaxError(function( event, jqXHR, ajaxSettings, thrownError) {
    //$('.modal').modal('hide');
    $('.btn[data-loading-text]').button('reset');
    switch(jqXHR.status) {
        case 500:
            toastr.error(HTTP_STATUS_500_MSG, "Error", toastr_opts);
            break;
        case 503:
            toastr.error(HTTP_STATUS_503_MSG, "Error", toastr_opts);
            break;
        case 504:
            toastr.error(HTTP_STATUS_504_MSG, "Error", toastr_opts);
            break;
        case 400:
            toastr.error(HTTP_STATUS_400_MSG, "Error", toastr_opts);
            break;
        case 404:
            toastr.error(HTTP_STATUS_404_MSG, "Error", toastr_opts);
            break;
        case 401:
            console.log("event");
            console.log(event);
            console.log("jqXHR");
            console.log(jqXHR);
            console.log("ajaxSettings");
            console.log(ajaxSettings);
            console.log("thrownError");
            console.log(thrownError);
            //toastr.error('Unauthorized', "Error", toastr_opts);
            /*toastr.error('Session expired now redirecting to login page', "Error", toastr_opts);
            setTimeout(function() {
                window.location.href = baseurl + '/logout';
            }, 100);*/
            break;
        case 403:
            toastr.error(HTTP_STATUS_403_MSG, "Error", toastr_opts);
            break;
        case 408:
            toastr.error(HTTP_STATUS_408_MSG, "Error", toastr_opts);
            break;
        case 410:
            toastr.error(HTTP_STATUS_410_MSG, "Error", toastr_opts);
            break;
        default:
            if(thrownError != ''){
                toastr.error(thrownError, "Error", toastr_opts);
            }
    }
});

//Start Block Added by Abubakar
$('.modal').on('show.bs.modal', function (e) {
    //Code for smart devices
    if (isxs()) {
     $('.modal').find('.pull-left,.pull-right').each(function(){
         $(this).removeClass('pull-left').removeClass('pull-right'); 		
		 });
    }
});
//Code for add button
$(document).on('click','[data-action="showAddModal"]' ,function(e) {
    e.preventDefault();
    var self = $(this);
    var modal = $('#'+self.attr('data-modal'));
    var forms = modal.find('form');
    forms.each(function(index,form){
        resetForm($(form),self.attr('data-type'));
    });
    modal.modal('show');
    modal.find('h4').html("Add New"+getTitle(self.attr('data-type')));
});
//Code for dropdown add button
$(document).on('select2-open','.select2add' ,function(e) {
    var self = $(e.target);
    var modal = $('#'+self.attr('data-modal'));
    var forms = modal.find('form');
    $('select[data-type="'+self.attr('data-type')+'"]').attr('data-active',0);
    $(self).attr('data-active',1);
    $('.select2-results .select2-add').parents('li').on('click', function(e) {
        e.stopPropagation();
        self.select2("close");
        forms.each(function(index,form){
            resetForm($(form),self.attr('data-type'));
        });
        if(self.attr('data-composite')!== undefined){
            modal.removeClass('composite').addClass('composite');
        }
        modal.modal('show');
        modal.find('h4').html("Add New"+getTitle(self.attr('data-type')));
    });
});

function resetForm(form,type){
    //form.trigger("reset");
    $.each(form[0].elements, function(index,field) {
        field = $(field);
        setDefaultValue(type,field);
        if(field.is("select")){
            field.trigger('change');
        }
        if(field.attr('type') == 'hidden'){
            field.prop('disabled',false);
        }
    });
    showHideControls(form);
}

//Set default value on the bases of set value in object.
function setDefaultValue(type,field){
    var property = 'value';
    var value = '';
    var defaultValue = {};
    /*without specifying attribute, value will be set to "value" attribute
    Otherwise value set to specified attribute */
    defaultValue.emailtemplate = {Email_template_privacy:0};
    defaultValue.invoice_template = {CompanyLogoUrl:{src:"http://placehold.it/250x100"}};
    defaultValue.billing_class = {PaymentDueInDays:1,RoundChargesAmount:2};
    defaultValue.currency = {Code:{readonly:false}};
    defaultValue.item_and_Subscription = {CurrencyID:{disabled:true}};
    defaultValue.ticket_bulk_option = {Type:0,Status:0,Priority:0,Group:0,Agent:0};
    if(defaultValue.hasOwnProperty(type)){
        var sub = defaultValue[type];
        if(sub.hasOwnProperty(field.attr('name'))){
            var prop = sub[field.attr('name')];
            if(typeof(prop)=='object'){
                property = Object.keys(prop)[0];
                value = prop[Object.keys(prop)[0]];
            }else{
                value = prop;
            }
        }
    }
    field.prop(property,value);
}

function getTitle(string){
    var title = '';
    if(string.indexOf('_')!=-1){
        var arr = string.split('_');
        for(var i=0;i<arr.length;i++){
            title+= ' '+(arr[i]=='and'?'/':arr[i].ucfirst());
        }
    }else{
        title = ' '+string.ucfirst();
    }
    return title;
}

function showHideControls(form){
    var toBeHide = [];
    var toBeShow = [];
    if(form.attr('id')=="add-new-invoice_template-form"){
        toBeHide[0] = ".LastInvoiceNumber";
        toBeHide[1] = ".LastEstimateNumber";
        toBeHide[2] = ".LastCreditNotesNumber";
        toBeShow[0] = "#InvoiceStartNumberToggle";
        toBeShow[1] = "#EstimateStartNumberToggle";
        toBeShow[2] = "#CreditNotesStartNumberToggle";
    }
    if(toBeHide.length > 0) {
        for (var i = 0; i < toBeHide.length; i++) {
            form.find(toBeHide[i]).addClass('hidden');
        }
    }

    if(toBeShow.length > 0) {
        for (var i = 0; i < toBeHide.length; i++) {
            form.find(toBeShow[i]).removeClass('hidden');
        }
    }
}

function rebuildSelect2(el,data,defualtText){
    el.empty();

    if(defualtText.length > 0){
        $('<option />').html(defualtText).appendTo(el);
    }

    $.each(data,function(key,value){
        if(typeof value == 'object'){
            var group = $('<optgroup label="' + key + '" />');
            $.each(value, function(key2,value2){
                $('<option />').val(key2).html(value2).appendTo(group);
            });
            group.appendTo(el);
        }else{
            $('<option />').val(key).html(value).appendTo(el);
        }
    });

    if(el.hasClass('select2add')){
        el.prepend('<option value="select2-add" disabled="disabled">Add</option>');
    }
    el.trigger('change');
}

function setSelection(self){
    var tr = self.parents('tr');
    if(tr.is('tr') && !tr.hasClass('selected')) {
        tr.find('.rowcheckbox').prop("checked", true);
        tr.addClass('selected');
    }
}

String.prototype.ucfirst = function() {
    return this.charAt(0).toUpperCase() + this.substr(1);
};

$(document).on('click','[redirecto]',function(){
    var url = $(this).attr('redirecto');
    window.location.href=url;
});

$(document).ajaxComplete(function(event, xhr, settings) {
    $('.make-switch').each(function(index, elem) {
        //Initialize all switches if they haven't been already
        if (!$(elem).hasClass('has-switch')) {
            $(elem).bootstrapSwitch();
        }
    });
    if (isxs()){
        $('.dataTables_wrapper').each(function(){
            var self = $(this);
            setTimeout(resetWidth, 3000,self);
            self.css('overflow-x','scroll').css('overflow-y','hidden');
        });
    }
});

$(document).on('change','#drp_toandfro_jump',function(){
    var val = $(this).val();
    if(val!="") {

        var url = window.location.href.replace(baseurl,"");
        var p = new RegExp('(\\/)(\\d+)', ["i"]);
        var m = p.exec(url);
        if (m != null) {
            url = url.replace(m[2], val);
            window.location.href = baseurl + url;
        }
    }
});

//End Block Added by Abubakar

/* Firefox Modal Position : fixed issue and chrome rate field edit issue  */
/*$('.modal').on('show.bs.modal', function (e) {
    $('.modal').css('top', $(document).scrollTop() + 20);
})*/
function submit_ajax(fullurl,data,refreshjob){
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $(".btn").button('reset');
            if (response.status == 'success') {
                $('.modal').modal('hide');
                toastr.success(response.message, "Success", toastr_opts);
                if( typeof data_table !=  'undefined'){
                    data_table.fnFilter('', 0);
                }
                if(refreshjob){
                    reloadJobsDrodown(0);
					reloadMsgDrodown(0);
                }
                if(typeof response.redirect != 'undefined' && response.redirect != ''){
                    window.location = response.redirect;
                }
            } else {
                toastr.error(response.message, "Error", toastr_opts);
            }
        },
        data: data,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false
    });
}
function submit_ajax_datatable(fullurl,data,refreshjob,data_table_reload){
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $(".btn").button('reset');
            if (response.status == 'success') {
                $('.modal').modal('hide');
                toastr.success(response.message, "Success", toastr_opts);
                if( typeof data_table_reload !=  'undefined'){
                    data_table_reload.fnFilter('', 0);
                }
                if(refreshjob){
                    reloadJobsDrodown(0);
					reloadMsgDrodown(0);
                }
            } else {
                toastr.error(response.message, "Error", toastr_opts);
            }
        },
        data: data,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false
    });
}
function submit_ajax_withfile(fullurl,formData,refreshjob,loading_bar) {
    $.ajax({
        url: fullurl,  //Server script to process data
        type: 'POST',
        dataType: 'json',
        beforeSend: function(){
            if(loading_bar) {
                show_loading_bar({
                    pct: 50,
                    delay: 5
                });
            }

        },
        success: function (response) {
            if(response.status =='success'){
                toastr.success(response.message, "Success", toastr_opts);
                $('.modal').modal('hide');
                if( typeof data_table !=  'undefined'){
                    data_table.fnFilter('', 0);
                }
                if(refreshjob){
                    reloadJobsDrodown(0);
					reloadMsgDrodown(0);
                }
                if(loading_bar){
                    show_loading_bar({
                        pct: 100,
                        delay: 2
                    });
                }
            }else{
                toastr.error(response.message, "Error", toastr_opts);
            }
            $(".btn").button('reset');

        },
        // Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });
}
function submit_ajaxbtn(fullurl,data,refreshjob,btn,reload){
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            btn.button('reset');
            if (response.status == 'success') {
                $('.modal').modal('hide');
                toastr.success(response.message, "Success", toastr_opts);
                if( typeof data_table !=  'undefined'){
                    data_table.fnFilter('', 0);
                }
                if(refreshjob){
                    reloadJobsDrodown(0);
					reloadMsgDrodown(0);
                }
                if(reload){
                    location.reload();
                }
            } else {
                toastr.error(response.message, "Error", toastr_opts);
            }
        },
        data: data,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false
    });
}
function pie_chart(class_name,data){
    Morris.Donut({
        element: class_name,
        data: data,
        labelColor: '#303641',
        colors: ['#0A61D7', '#269FFF', '#7CB0E9', '#90D1FF','#BBE4FF']

    });
}

function ajax_json(fullurl,data,callback){
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: callback,
        data: data,
        cache: false
    });
}
function get_decimal_places(num) {
    var match = (''+num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
    if (!match) { return 0; }
    return Math.max(
        0,
        // Number of digits right of decimal point.
        (match[1] ? match[1].length : 0)
            // Adjust for scientific notation.
        - (match[2] ? +match[2] : 0));
}

/* Delay showing search button to avoid form posting in search filter before page load.
* */
$('button[type="submit"]').css('visibility','hidden');
window.addEventListener('load',function(){
    setTimeout(function(){
        $('button[type="submit"]').css('visibility','visible');
    },300);

});

function getTableFieldValue(controller_url, id,field ,callback){
    var get_url = baseurl +'/' + controller_url +'/'+id+'/get/'+field;
    $.get( get_url, callback, "json" );
}

function resetWidth(self){
    var table = self.find('table');
    var width = 0;
    if(table.hasClass('hidden')){
        table.removeClass('hidden');
        width = self.find('table').outerWidth();
        table.addClass('hidden');
    }else{
        width = self.find('table').outerWidth();
    }
    self.find('div.row').each(function(index,item){
        $(item).css('width',width);
        $(item).css('margin',0);
        $(item).find('.col-xs-6').css('padding',0);
    }.bind(width));
}

function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

  $(document).on('mouseover','.shortname',
		function(){
			var a = $(this).attr('FullName');
			$(this).html(a);
		}
  ).on('mouseout',function(){
		var a = $(this).attr('ShortName');                
		$(this).html(a);
	});

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function bytesToSize(filesize) {
  var sizeInMB = (filesize / (1024*1024)).toFixed(2);
  if(sizeInMB>max_file_size)
  {return 1;}else{return 0;}  
}

function table_row_select(table,selectallbutton) {
    $('#' + table + ' tbody').on('click', 'tr', function () {
        var checked = '';
        if ($('#' + selectallbutton).is(':checked')) {
            checked = 'checked=checked disabled';
        }
        if (checked == '') {
            if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                $(this).toggleClass('selected');
                if ($(this).hasClass('selected')) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            }
        }
    });
}

function selected_all(id,table) {
    $("#" + id).click(function (ev) {
        var is_checked = $(this).is(':checked');
        $('#' + table + ' tbody tr').each(function (i, el) {
            if (is_checked) {
                $(this).find('.rowcheckbox').prop("checked", true);
                $(this).addClass('selected');
            } else {
                $(this).find('.rowcheckbox').prop("checked", false);
                $(this).removeClass('selected');
            }
        });
    });
}

function default_row_selected(table,selectall,selectallbutton) {
    $('#' + table + ' tbody tr').each(function (i, el) {
        var checked = '';
        if ($('#' + selectallbutton).is(':checked')) {
            checked = 'checked=checked disabled';
        }
        if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
            if (checked != '') {
                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                $(this).addClass('selected');
                $('#' + selectall).prop("checked", true);
            } else {
                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                $(this).removeClass('selected');
            }
        }
    });
}

function select_all_top(selectallbutton,table,selectall) {
    //select all record
    $('#' + selectallbutton).click(function () {
        if ($('#' + selectallbutton).is(':checked')) {
            //checked = 'checked=checked disabled';
            $("#" + selectall).prop("checked", true).prop('disabled', true);
            $('#' + table + ' tbody tr').each(function (i, el) {
                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                $(this).addClass('selected');
            });
        } else {
            //checked = '';
            $("#" + selectall).prop("checked", false).prop('disabled', false);
            $('#' + table + ' tbody tr').each(function (i, el) {
                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                $(this).removeClass('selected');
            });
        }
    });
}
function openInNewTab(url) {
    var redirectWindow = window.open(url, '_blank');
    redirectWindow.location;
}

try{
    if(typeof customer[0].customer != 'undefined' &&  customer[0].customer != 1 && $(".notifications.cron_jobs.dropdown").has("#failing_placeholder").length > 0 ) {
        setInterval(function () {
            checkFailingCronJob();
        }, 1000 ); // where X is your every X minutes
    }
}catch(er){
    console.log(er.message);
}

show_summernote = function (element,options){

    if(!!document.createRange) {
        document.getSelection().removeAllRanges();
    }

    element.addClass("hidden");
    /**
     * @todo editor remove html automatically
     */
    if(!('withOutDestroy' in options) || options.withOutDestroy==false){
        element.summernote('destroy');
    }

     element.summernote({
         onInit: function() {
             //console.log('Summernote is launched');
         },
         toolbar: [
            ['neon_placeholders', ['neon_placeholders']], // here, for example
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['media', 'link', 'hr', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']]
        ],
        height: 200,
        tabsize: 2,
        dialogsInBody: true,
        defaultOptions : options
    });

};

/**
 * this function is use in invoice template page
 * */
show_summerinvoicetemplate = function (element,options){

    if(!!document.createRange) {
        document.getSelection().removeAllRanges();
    }

    element.addClass("hidden");
    element.summernote('destroy');

    element.summernote({
        onInit: function() {
            console.log('Summernote is launched');
        },
        toolbar: [
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['media', 'link', 'hr', 'picture']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']]
        ],
        height: 200,
        tabsize: 2,
        dialogsInBody: true,
        defaultOptions : options
    });

};

function rebuildSelectComposite(el,data,label){
    //el.empty();
    el.find('optgroup').not('optgroup:first').remove();
    options = [];
    $.each(data,function(key,value){
        if(typeof value == 'object'){
            key = value.id;
            value = value.text;
        }
        options.push(new Option(value, key, false, false));
    });
    options.sort();
    options.reverse();


    $('<optGroup/>').attr('label', label).appendTo(el);
    el.find('optGroup').last().append(options);
    el.trigger('change');
}

function reinitializeSelect2(ajax_config_html){
    $("#"+ajax_config_html+" .select2").each(function(i, el) {
        buildselect2(el);
    }).promise().done(function(){
        $('.select2').css('visibility','visible');
    });


    if ($.isFunction($.fn.perfectScrollbar))
    {
        $(".select2-results").niceScroll({
            cursorcolor: '#d4d4d4',
            cursorborder: '1px solid #ccc',
            railpadding: {right: 3}
        });
    }
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function setCookie(cname,cvalue,exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
