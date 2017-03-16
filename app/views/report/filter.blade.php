@section('filter')

<!-- report fiter Section -->
<div id="report_filter" class="new_filter fixed"  data-order-by-status="1" data-max-filter-history="25">

    <div class="filter-inner">


        <h2 class="filter-header">
            <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>

            <i class="fa fa-filter"></i>
            Filter

            <span class="badge badge-success is-hidden">0</span>
        </h2>


        <div class="filter-group" id="group-1">
            <label for="field-1" class="col-sm-12 control-label">Name</label>
            <div class="col-sm-12">
                <input class="form-control" name="account_name"  type="text" >
            </div>
            <label for="field-1" class="col-sm-12 control-label">Number</label>
            <div class="col-sm-12">
                <input class="form-control" name="account_number"  type="text" >
            </div>
            <label class="col-sm-12 control-label"  >Customer</label>
            <div class="col-sm-12">
                <p class="make-switch switch-small">
                    <input id="Customer_on_off" name="customer_on_off" type="checkbox" value="1" >
                </p>
            </div>
            <p style="text-align: right;">
                <button type="submit" class="btn btn-green btn-sm btn-icon icon-left">
                    <i class="entypo-search"></i>
                    Search
                </button>
            </p>
        </div>

    </div>



</div>
<script>
    $( function() {
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
        var sidebar_default_is_open = ! $(".page-container").hasClass('sidebar-collapsed');
        // Filter Toggle
        $("body").on('click', '[data-toggle="report_filter"]', function(ev)
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
                hideFilter()
            }else{
                showFilter()
            }

            if(collapse_sidebar)
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
            }
        });

    });

    function hideFilter() {
        var visible_class = 'filter-visible';


        if(isxs())
        {
            visible_class += ' toggle-click';
        }

        public_vars.$pageContainer.removeClass(visible_class);

    }

    function showFilter() {
        var visible_class = 'filter-visible';

        if(isxs())
        {
            visible_class += ' toggle-click';
        }

        public_vars.$pageContainer.addClass(visible_class);

    }
</script>
@stop