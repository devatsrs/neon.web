@yield('filter')

<script>
    $( function() {
        $('[data-toggle="datatable-filter"]').each(function(i, el)
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

        var FilterVisible = getCookie('FilterVisible');
        if(FilterVisible == 1) {
            $('.page-container').addClass('filter-visible');
        } else {
            $('.page-container').removeClass('filter-visible');
        }

        var SidebarVisible = getCookie('SidebarVisible');
        if(SidebarVisible == 1) {
            $('.page-container').addClass('sidebar-collapsed');
        } else {
            $('.page-container').removeClass('sidebar-collapsed');
        }

        $("body").on('click', '.sidebar-collapse', function(ev) {
            if($('.page-container').hasClass('sidebar-collapsed')) {
                setCookie('SidebarVisible',0,'30');
            } else {
                setCookie('SidebarVisible',1,'30');
            }
        });

        $("body").on('click', '.filter-close', function(ev)
        {
            ev.preventDefault();

            hideFilter();
        });

        $("body").on('click', '.filter-open', function(ev)
        {
            ev.preventDefault();

            showFilter();
        });
        $(".top_filter").click(function(ev)
        {
            if($(this).val() == 'none'){
                $('.top_filter_data').attr('disabled','disabled');
            }else{
                $('.top_filter_data').removeAttr('disabled');
            }
        });
        $(".top_filter_none").trigger('click');

        $(".condition_filter").click(function(ev)
        {
            if($(this).val() == 'none'){
                $('.condition_filter_data').attr('disabled','disabled');
            }else{
                $('.condition_filter_data').removeAttr('disabled');
            }
        });
        $(".condition_filter_none").trigger('click');

        var sidebar_default_is_open = ! $(".page-container").hasClass('sidebar-collapsed');
        // Filter Toggle
        $("body").on('click', '#filter-toggle-button', function(ev)
        {
            ev.preventDefault();

            var $this = $(this),
                    with_animation = $this.is('[data-animate]'),
                    collapse_sidebar = $this.is('[data-collapse-sidebar]');



            var _func = public_vars.$pageContainer.hasClass('filter-visible') ? 'hideFilter' : 'showFilter';


            if(isxs())
            {
                _func = public_vars.$pageContainer.hasClass('toggle-click') ? 'hideFilter' : 'showFilter';
            }

            if(_func == 'hideFilter'){
                setCookie('FilterVisible',0,'30');
                hideFilter()
            }else{
                setCookie('FilterVisible',1,'30');
                showFilter()
            }

            /*if(collapse_sidebar)
            {
                if(sidebar_default_is_open)
                {
                    if(_func == 'hideFilter') // Hide Sidebar
                    {
                        show_sidebar_menu(with_animation);
                    }
                    else
                    {
                        hide_sidebar_menu(with_animation);
                    }
                }
            }*/
        });

    });

    function hideFilter() {
        var visible_class = 'filter-visible';


        if(isxs())
        {
            visible_class += ' toggle-click';
        }

        public_vars.$pageContainer.removeClass(visible_class);
        setCookie('FilterVisible',0,'30');
    }

    function showFilter() {
        var visible_class = 'filter-visible';

        if(isxs())
        {
            visible_class += ' toggle-click';
        }

        public_vars.$pageContainer.addClass(visible_class);
        setCookie('FilterVisible',1,'30');
    }
</script>