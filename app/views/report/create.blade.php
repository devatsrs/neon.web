@extends($layout)
@section('content')
    @if(Input::get('report')!='run')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('report')}}">Report</a>
        </li>
        @if(!empty($report))
        <li>
            <a><span>{{report_tables_dropbox($report->ReportID,$report->CompanyID)}}</span></a>
        </li>
        @else
        <li class="active">
            <a href="javascript:void(0)">{{$report->Name or ''}}</a>
        </li>
        @endif
    </ol>
    @endif

    @include('includes.errors')
    @include('includes.success')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary" data-collapsed="0">
                <!-- panel head -->
                <div class="panel-heading">
                    <div class="panel-title">{{Input::get('report')=='run'?'<strong>'.$report->Name.'</strong>':'Report'}}</div>
                    @if(User::checkCategoryPermission('Report','Update') )
                    <div class="panel-options dropdown">
                        <a href="{{URL::to('report')}}"  data-original-title="Back" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-times"></i></a>
                        <a type="submit" id="save_report"  data-original-title="Save" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-floppy"></i></a>
                        @if(empty(Input::get('report')) && !empty($report))
                            <a href="{{URL::to('report/edit/'.$report->ReportID)}}?report=run"  data-original-title="Run" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-play"></i>&nbsp;</a>
                        @elseif(!empty($report) && !empty(Input::get('report')))
                            <a href="{{URL::to('report/edit/'.$report->ReportID)}}"  data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i>&nbsp;</a>
                        @endif
                            <a  data-original-title="Export" title="" data-placement="top" data-toggle="dropdown" aria-expanded="true" class="dropdown-toggle"><i class="fa fa-download"></i>&nbsp;</a>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px; min-width: 0">
                                <li>
                                    <a href="#" class="save-report-data" data-format="{{Report::XLS}}">
                                        <span>Excel</span>
                                    </a>
                                </li><li>
                                    <a href="#" class="save-report-data" data-format="{{Report::PNG}}">
                                        <span>{{Report::PNG}}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="save-report-data" data-format="{{Report::PDF}}">
                                        <span>{{Report::PDF}}</span>
                                    </a>
                                </li>

                            </ul>



                    @if(!empty($ReportSchedule))
                            <a href="{{URL::to('report/schedule_update/'.$ReportSchedule->ReportScheduleID)}}" class="schedule_report"  data-original-title="Scheduling" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-calendar-times-o"></i>&nbsp;</a>
                            <div class = "hiddenRowData pull-left" >
                                @foreach($schedule_settings as $schedule_settings_key=> $schedule_settings_val)
                                    <input disabled type = "hidden"  name = "{{$schedule_settings_key}}"       value = "{{is_array($schedule_settings_val)?implode(',',$schedule_settings_val):$schedule_settings_val}}" />
                                @endforeach
                                    <input disabled name="Name" value="{{$ReportSchedule->Name}}" type="hidden">
                                    <input disabled name="ReportID" value="{{$ReportSchedule->ReportID}}" type="hidden">
                                    <input disabled name="ReportScheduleID" value="{{$ReportSchedule->ReportScheduleID}}" type="hidden">
                                    <input disabled name="Status" value="{{$ReportSchedule->Status}}" type="hidden" />
                            </div>
                    @elseif(!empty($report))
                            <a href="{{URL::to('report/add_schedule')}}" class="schedule_report"  data-original-title="Scheduling" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-calendar-times-o"></i>&nbsp;</a>
                            <div class = "hiddenRowData pull-left" >
                                <input disabled name="Name" value="{{$report->Name}}" type="hidden">
                                <input disabled name="ReportID" value="{{$report->ReportID}}" type="hidden">
                            </div>
                    @endif
                    </div>

                    @endif

                </div>
                <!-- panel body -->
                <div class="panel-body">
                    <form role="form" class="form-horizontal form-groups-bordered" id="report-row-col">
                        <div class="form-group " >
                            <div class="col-sm-2 {{Input::get('report')=='run'?'hidden':''}}">
                                <label for="field-5" class="control-label popover-primary" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Cube is a logical schema which contains measures and dimensions.For Example Customer CDR,Payment,Invoice" data-original-title="Cube">Cube</label>
                                <br>
                                <br>
                                {{Form::select('Cube',Report::$cube,(isset($report_settings['Cube'])?$report_settings['Cube']:''),array("class"=>"select2 small",$disable))}}

                                @if(!empty($report))
                                    <input type="hidden" id="hidden_cube" name="Cube" value="{{$report_settings['Cube'] or ''}}">
                                @endif
                            </div>
                            <div class="{{ Input::get('report')=='run'?'col-sm-12':'col-sm-10'}}  vertical-border border_left droppable ">
                                <input type="hidden" id="hidden_row" name="row" value="{{$report_settings['row'] or ''}}">
                                <input type="hidden" id="hidden_columns" name="column" value="{{$report_settings['column'] or ''}}">
                                <input type="hidden" id="hidden_filter" name="filter" value="{{$report_settings['filter'] or ''}}">
                                <input type="hidden" id="hidden_filter_col" name="filter_col_name" value="{{$report_settings['filter_col_name'] or ''}}">
                                <input type="hidden" id="hidden_setting" name="filter_settings" value='{{$report_settings['filter_settings'] or ''}}'>
                                <input type="hidden" id="hidden_setting_rename" name="setting_rename" value='{{$report_settings['setting_rename'] or ''}}'>
                                <input type="hidden" id="hidden_setting_ag" name="setting_ag" value='{{$report_settings['setting_ag'] or ''}}'>
                                <label for="field-5" class="control-label popover-primary {{Input::get('report')=='run'?'hidden':''}}" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="You can drop dimension or measures here which you want to see in columns. For example year or grand total you can select as column" data-original-title="Column">Columns</label>
                                <div id="Columns_Drop" class="form-control {{Input::get('report')=='run'?'hidden':''}} report_multi_select row-fluid ui-droppable ui-sortable">

                                    <ul class=" multi_ul nav nav-pills span9">
                                        @if(isset($report_settings['column']) && $selectedColumns = array_filter(explode(',',$report_settings['column'])))
                                        @foreach($selectedColumns as $selectedColumn)
                                            <li class="{{isset($measures[$report_settings['Cube']][$selectedColumn])?'measures':'dimension'}} dropdown ui-draggable" data-cube="{{$report_settings['Cube']}}" data-val="{{$selectedColumn}}">
                                                <a class="dropdown-toggle" data-toggle="dropdown" data-text="">
                                                    <span><i class="fa fa-arrows"></i>
                                                    <?php
                                                    $selected_dimension = '';
                                                    if(isset($measures[$report_settings['Cube']][$selectedColumn])){
                                                        $selected_dimension = $measures[$report_settings['Cube']][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']][$selectedColumn]) && !is_array($dimensions[$report_settings['Cube']][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Date'][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']]['Date'][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Customer'][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']]['Customer'][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Product'][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']]['Product'][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['CDR'][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']]['CDR'][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['AccountBalance'][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']]['AccountBalance'][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Account'][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']]['Account'][$selectedColumn];
                                                    }
                                                    ?>
                                                        <span class="col-name">{{$selected_dimension}}</span>
                                                        (<span class="col-agg">
                                                            @if(isset($setting_ag[$selectedColumn]))
                                                                {{$setting_ag[$selectedColumn]}}
                                                            @elseif(isset($measures[$report_settings['Cube']][$selectedColumn]))
                                                                Sum
                                                            @else
                                                                Actual
                                                            @endif
                                                            </span>)

                                                </span>
                                                    </a>
                                                @if(isset($measures[$report_settings['Cube']][$selectedColumn]))
                                                    <ul class="dropdown-menu" style="background-color: #000; border-color: #000; margin-top:0px; min-width: 0"><li><a class="column_function" data-action="">Actual</a></li><li><a class="column_function" data-action="sum">Sum</a></li><li><a class="column_function" data-action="avg">Average</a></li><li><a class="column_function" data-action="count">Count</a></li><li><a class="column_function" data-action="count_distinct">Count Distinct</a></li><li><a class="column_function" data-action="min">Min</a></li><li><a class="column_function" data-action="max">Max</a></li><li class="divider"></li><li><a class="add_label" >Rename Column</a></li></ul>
                                                @else
                                                    <ul class="dropdown-menu" style="background-color: #000; border-color: #000; margin-top:0px; min-width: 0"><li><a class="column_function" data-action="" >Actual</a></li><li><a class="column_function" data-action="top5" >Top 5</a></li><li><a class="column_function" data-action="top10" >Top 10</a></li><li><a class="column_function" data-action="bottom5" >Bottom 5</a></li><li><a class="column_function" data-action="bottom10" >Bottom 10</a></li></ul>
                                                @endif
                                            </li>
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <label for="field-5" class="control-label popover-primary {{Input::get('report')=='run'?'hidden':''}}" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="You can drop dimension here which you want to see in row. For example year you can select as row " data-original-title="Row">Row</label>
                                <div id="Row_Drop" class="form-control {{Input::get('report')=='run'?'hidden':''}} report_multi_select row-fluid ui-droppable ui-sortable">
                                    <ul class=" multi_ul nav nav-pills span9">
                                        @if(isset($report_settings['row']) && $selectedRows = array_filter(explode(',',$report_settings['row'])))
                                        @foreach($selectedRows as $selectedRow)
                                            <li class="{{isset($measures[$report_settings['Cube']][$selectedRow])?'measures':'dimension'}} ui-draggable" data-cube="{{$report_settings['Cube']}}" data-val="{{$selectedRow}}">
                                                <a class="dropdown-toggle" data-toggle="dropdown" data-text=""><span><i class="fa fa-arrows"></i>
                                                    <?php
                                                    $selected_measures = '';
                                                    if(isset($measures[$report_settings['Cube']][$selectedRow])){
                                                        $selected_measures = $measures[$report_settings['Cube']][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']][$selectedRow]) && !is_array($dimensions[$report_settings['Cube']][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Date'][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['Date'][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Customer'][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['Customer'][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Product'][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['Product'][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['CDR'][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['CDR'][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['AccountBalance'][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['AccountBalance'][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Account'][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['Account'][$selectedRow];
                                                    }
                                                    ?>
                                                        <span class="col-name">{{$selected_measures}}</span>
                                                        (<span class="col-agg">
                                                        @if(isset($setting_ag[$selectedRow]))
                                                            {{$setting_ag[$selectedRow]}}
                                                        @elseif(isset($measures[$report_settings['Cube']][$selectedRow]))
                                                            Sum
                                                        @else
                                                            Actual
                                                        @endif
                                                        </span>)

                                                </span>
                                                    </a>
                                                @if(isset($measures[$report_settings['Cube']][$selectedRow]))
                                                    <ul class="dropdown-menu" style="background-color: #000; border-color: #000; margin-top:0px; min-width: 0"><li><a class="column_function" data-action="">Actual</a></li><li><a class="column_function" data-action="sum">Sum</a></li><li><a class="column_function" data-action="avg">Average</a></li><li><a class="column_function" data-action="count">Count</a></li><li><a class="column_function" data-action="count_distinct">Count Distinct</a></li><li><a class="column_function" data-action="min">Min</a></li><li><a class="column_function" data-action="max">Max</a></li><li class="divider"></li><li><a class="add_label" >Rename Column</a></li></ul>
                                                @else
                                                    <ul class="dropdown-menu" style="background-color: #000; border-color: #000; margin-top:0px; min-width: 0"><li><a class="column_function" data-action="" >Actual</a></li><li><a class="column_function" data-action="top5" >Top 5</a></li><li><a class="column_function" data-action="top10" >Top 10</a></li><li><a class="column_function" data-action="bottom5" >Bottom 5</a></li><li><a class="column_function" data-action="bottom10" >Bottom 10</a></li></ul>
                                                @endif
                                            </li>
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <label for="field-5" class="control-label popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="You can apply filter by dropping dimension here. For example date you can select start date and end date filter" data-original-title="Filter">Filter</label>
                                <div id="Filter_Drop" class="form-control report_multi_select row-fluid ui-droppable ui-sortable">
                                    <ul class="multi_ul nav nav-pills span9">
                                        @if(isset($report_settings['filter_settings']) && $selectedColumns = array_filter(json_decode($report_settings['filter_settings'],true)))
                                            @foreach($selectedColumns as $selectedColumn => $extraarray)
                                                <li class="{{isset($measures[$report_settings['Cube']][$selectedColumn])?'measures':'dimension'}} ui-draggable" data-cube="{{$report_settings['Cube']}}" data-val="{{$selectedColumn}}">
                                <span><i class="fa fa-arrows"></i>
                                    <?php
                                    $filter = '';
                                    if(isset($measures[$report_settings['Cube']][$selectedColumn])){
                                        $filter = $measures[$report_settings['Cube']][$selectedColumn];
                                    } else if(isset($dimensions[$report_settings['Cube']][$selectedColumn]) && !is_array($dimensions[$report_settings['Cube']][$selectedColumn])){
                                        $filter = $dimensions[$report_settings['Cube']][$selectedColumn];
                                    } else if(isset($dimensions[$report_settings['Cube']]['Date'][$selectedColumn])){
                                        $filter = $dimensions[$report_settings['Cube']]['Date'][$selectedColumn];
                                    } else if(isset($dimensions[$report_settings['Cube']]['Customer'][$selectedColumn])){
                                        $filter = $dimensions[$report_settings['Cube']]['Customer'][$selectedColumn];
                                    } else if(isset($dimensions[$report_settings['Cube']]['Product'][$selectedColumn])){
                                        $filter = $dimensions[$report_settings['Cube']]['Product'][$selectedColumn];
                                    } else if(isset($dimensions[$report_settings['Cube']]['CDR'][$selectedColumn])){
                                        $filter = $dimensions[$report_settings['Cube']]['CDR'][$selectedColumn];
                                    } else if(isset($dimensions[$report_settings['Cube']]['AccountBalance'][$selectedColumn])){
                                        $filter = $dimensions[$report_settings['Cube']]['AccountBalance'][$selectedColumn];
                                    } else if(isset($dimensions[$report_settings['Cube']]['Account'][$selectedColumn])){
                                        $filter = $dimensions[$report_settings['Cube']]['Account'][$selectedColumn];
                                    }
                                    ?>
                                    {{$filter}}
                                </span>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2 vertical-border border_right {{Input::get('report')=='run'?'hidden':''}}">
                                <div class="row">
                                    <div class="col-sm-12 vertical-border border_bottom">
                                        <label for="field-5" class="control-label  popover-primary" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Dimensions are qualitative and do not total a sum. For example, account, currency, account ip , or date are dimensions.Dimensions you can drop in Columns,Row,Filter" data-original-title="Dimensions">Dimension</label>
                                    </div>
                                    <div   class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <div class="nested-list with-margins tree">

                                            <ul id="Dimension" class=" ui-helper-reset ui-helper-clearfix">

                                            </ul>

                                        </div>
                                    </div>
                                    <div class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <label for="field-5" class="control-label popover-primary" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="Measures are numerical values that mathematical functions work on. For example, a grand total column is a measure because you can find out a total the data.Measures you can drop only in Columns." data-original-title="Measures">Measures</label>
                                    </div>
                                    <div class="col-sm-12 vertical-border" style="margin-top: 15px;padding-top: 15px">
                                        <div id="list-1" class="nested-list tree with-margins">
                                            <ul id="Measures" class="dd-list">

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ Input::get('report')=='run'?'col-sm-12':'col-sm-10'}} table_report_overflow loading">
                            </div>
                        </div>
                        <div class="form-group">
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    <style>
        .tree {
            min-height:20px;
            /*padding:19px;
            margin-bottom:20px;*/
        }
        .tree li {
            list-style-type:none;
            margin:0;
            padding:10px 5px 0 5px;
            position:relative
        }
        .ui-widget-content li {
            padding:10px 5px;
        }
        .tree li::before, .tree li::after {
            content:'';
            left:-20px;
            position:absolute;
            right:auto
        }
        .tree li::before {
            border-left:1px solid #999;
            bottom:50px;
            height:100%;
            top:0;
            width:1px
        }
        .tree li::after {
            border-top:1px solid #999;
            height:20px;
            top:25px;
            width:25px
        }
        .tree li span {
            display:inline-block;
            padding:3px 8px;
            text-decoration:none
        }
        .tree li.parent_li>span {
            cursor:pointer
        }
        .tree>ul>li::before, .tree>ul>li::after {
            border:0
        }
        .tree li:last-child::before {
            height:30px
        }
        .tree li.parent_li>span:hover, .tree li.parent_li>span:hover+ul li span {
            background:#eee;
            color:#000
        }


        .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data{
            right: 30px !important;
        }
        #table-filter-list_wrapper label{
            display:block !important;
        }
        #selectcheckbox{
            padding: 15px 10px;
        }
        .report_multi_select .multi_ul{
            min-height:25px;
            height:auto;
            /*width:250px;*/
            background:#ffffff;
            margin:0px;
            display:inline-block;
            padding-left:6px;
            padding-right:6px;
            padding-top: 4px;
            /*
            border-radius:3px;
            -webkit-border-radius:3px;
            -moz-border-radius:3px;
            */
            vertical-align:center;
        }

        .report_multi_select .multi_ul li a{
            padding:2px 2px 1px 15px;
        }
        .report_multi_select .multi_ul li a.dropdown-toggle,.report_multi_select .multi_ul li > span{
            color:black;
            padding-top:4px;
            padding-bottom:4px;
            padding-left:3px;
            padding-right:3px;
        }
        .report_multi_select{
            height: auto !important;
            padding: 0px;
        }
        .dropdown-menu{
            z-index: 1999;
        }

        .li_active{display:none;}
        .table_report_overflow{
       @if(Input::get('report') == 'run')
            width: 99%;
       @else
            width: 82%;
       @endif
        }

    </style>
@include('report.script')
    @include('report.schedule_modal')
@stop
@section('footer_ext')
    @parent
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
                            <li class="active filter_data_table"><a href="#general" data-toggle="tab">General</a></li>
                            <li class="filter_data_wildcard"><a href="#wildcard" data-toggle="tab" >Wildcard</a></li>
                            <li class="date_filters"><a href="#date_filter" data-toggle="tab" >Date Filter</a></li>
                            <li class="number_filter"><a href="#number_filter" data-toggle="tab">Number Filter</a></li>
                            {{--<li ><a href="#top" data-toggle="tab">Top</a></li>--}}
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="general" >
                                <div class="row margin-top filter_data_table" style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
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
                            <div class="tab-pane " id="wildcard" >
                                <div class="row margin-top filter_data_wildcard">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="field-5" class="control-label">Match Value</label>
                                            <input type="text"  name="wildcard_match_val" class="form-control" id="field-5" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="date_filter" >
                                <div class="row margin-top">
                                    <div class="col-md-6 clear">
                                        <div class="form-group ">
                                            <label for="field-5" class="control-label">Start Date</label>
                                            <input type="text"  name="start_date" class="form-control datepicker" id="field-5" placeholder="" data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            <label for="field-5" class="control-label">End Date</label>
                                            <input type="text"  name="end_date" class="form-control datepicker" id="field-5" placeholder="" data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="number_filter"  >
                                <div class="row margin-top number_filter">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{Form::select('number_agg',Report::$aggregator,'',array("class"=>"select2 small number_filter_data"))}}
                                        </div>
                                    </div>
                                    <div class="col-md-6 clear">
                                        <div class="form-group">
                                            {{Form::select('number_sign',Report::$condition,'',array("class"=>"select2 small number_filter_data"))}}
                                        </div>
                                    </div>
                                    <div class="col-md-6 number_agg_val">
                                        <div class="form-group">
                                            <input type="text" name="number_agg_val" value="" class="form-control number_filter_data" id="field-5" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6 clear number_filter_range">
                                        <div class="form-group">
                                            <label for="field-5" class="control-label">Range Min</label>
                                            <input type="text" name="number_agg_range_min" value="" class="form-control number_filter_data" id="field-5" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6 number_filter_range">
                                        <div class="form-group">
                                            <label for="field-5" class="control-label">Range Max</label>
                                            <input type="text" name="number_agg_range_max" value="" class="form-control number_filter_data" id="field-5" placeholder="">
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
                                                    <input type="radio"  value="none" checked name="top" class="top_filter top_filter_none" id="field-5" placeholder="">
                                                    None
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="top" value="top_active" class="top_filter" id="field-5" placeholder="">
                                                    By Field
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 clear">
                                        <div class="form-group">
                                            {{Form::select('top_agg_con',Report::$top,'',array("class"=>"select2 small top_filter_data"))}}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" name="top_agg" value="" class="form-control top_filter_data" id="field-5" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6 clear">
                                        <div class="form-group">
                                            {{Form::select('condition_col',$Columns,'',array("class"=>"select2 small top_filter_data"))}}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{Form::select('condition_agg',Report::$aggregator,'',array("class"=>"select2 small top_filter_data"))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="report-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="fa fa-filter"></i>
                            Filter
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
<div class="modal fade" id="add-new-modal-report">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-report-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{!isset($report->ReportID)?'Add New':'Edit'}} Report</h4>
                </div>
                <div class="modal-body">
                    <div class="row margin-top">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Name</label>
                                <input type="text"  name="Name" class="form-control" id="field-5" placeholder="" value="{{$report->Name or ''}}">
                                <input type="hidden"  name="ReportID" value="{{$report->ReportID or ''}}">
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
    <div class="modal fade" id="rename-column-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="report-rename-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Column</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row margin-top">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name</label>
                                    <input type="text"  name="Name" class="form-control" id="field-5" placeholder="" value="">
                                    <input type="hidden"  name="ActualName" class="form-control" id="field-5" placeholder="" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="report-rename-col"  class="save btn btn-primary btn-sm btn-icon icon-left">
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