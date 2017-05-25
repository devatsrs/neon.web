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
            <label for="field-5" class="control-label">Filters</label>
            <div id="Filter_Drop" class="col-sm-12 ui-widget-content ui-state-default select2-container select2-container-multi">
                <ul class=" select2-choices ui-helper-reset"></ul>
            </div>
            <p style="text-align: right;">
                <button type="submit" class="btn btn-green btn-sm btn-icon icon-left">
                    <i class="entypo-floppy"></i>
                    Save
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

<div class="modal fade" id="add-new-modal-filter">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-filter-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Filter</h4>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs refresh_tab">
                            <li class="active"><a href="#general" data-toggle="tab">General</a></li>
                            <li ><a href="#wildcard" data-toggle="tab">Wildcard</a></li>
                            <li ><a href="#condition" data-toggle="tab">Condition</a></li>
                            <li ><a href="#top" data-toggle="tab">Top</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" >
                            <div class="row margin-top">
                                <div class="col-md-12">
                                    <table class="table table-bordered datatable" id="table-filter-list">
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                                            <th>Name</th>
                                        </tr>
                                        </thead>
                                        <tbody>


                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="wildcard" >
                            <div class="row margin-top">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Match Value</label>
                                        <input type="text"  name="wildcard_match_val" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="condition" >
                            <div class="row margin-top">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="radio">
                                            <label>
                                                <input type="radio"  value="none" checked name="condition" class="" id="field-5" placeholder="">None
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="condition" value="condition_active" class="" id="field-5" placeholder="">
                                                By Field
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 clear">
                                    <div class="form-group">
                                        {{Form::select('condition_col',$Columns,'',array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{Form::select('condition_agg',Report::$aggregator,'',array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                                <div class="col-md-6 clear">
                                    <div class="form-group">
                                        {{Form::select('Condition_sign',Report::$condition,'',array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" name="condition_agg_val" value="" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                                <div class="col-md-6 clear">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Range Min</label>
                                        <input type="text" name="condition_agg_range_min" value="" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Range Max</label>
                                        <input type="text" name="condition_agg_range_max" value="" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="top" >
                            <div class="row margin-top">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="radio">
                                            <label>
                                                <input type="radio"  value="none" checked name="top" class="" id="field-5" placeholder="">
                                                None
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="top" value="top_active" class="" id="field-5" placeholder="">
                                                By Field
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 clear">
                                    <div class="form-group">
                                        {{Form::select('top_agg_con',Report::$top,'',array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" name="top_agg" value="" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                                <div class="col-md-6 clear">
                                    <div class="form-group">
                                        {{Form::select('condition_col',$Columns,'',array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{Form::select('condition_agg',Report::$aggregator,'',array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="report-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop