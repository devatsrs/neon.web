@extends('layout.main')
@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('report')}}">Report</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">{{$report->Name or ''}}</a>
        </li>
    </ol>

    @include('includes.errors')
    @include('includes.success')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary" data-collapsed="0">
                <!-- panel head -->
                <div class="panel-heading">
                    <div class="panel-title">Report</div>
                    <div class="panel-options">
                        <a href="#" data-toggle="report_filter" data-collapse-sidebar="1">
                            <i class="fa fa-filter"></i>
                        </a>
                    </div>

                </div>
                <!-- panel body -->
                <div class="panel-body">
                    <form role="form" class="form-horizontal form-groups-bordered" id="report-row-col">
                        <div class="form-group {{Input::get('report')=='run'?'hidden':''}}" >
                            <div class="col-sm-3">
                                <label for="field-5" class="control-label">Cube</label>
                                {{Form::select('Cube',Report::$cube,(isset($report_settings['Cube'])?$report_settings['Cube']:''),array("class"=>"select2 small",$disable))}}

                                @if(!empty($report))
                                    <input type="hidden" id="hidden_cube" name="Cube" value="{{$report_settings['Cube'] or ''}}">
                                @endif
                            </div>
                            <div class="col-sm-9 vertical-border border_left ">
                                <input type="hidden" id="hidden_row" name="row" value="{{$report_settings['row'] or ''}}">
                                <input type="hidden" id="hidden_columns" name="column" value="{{$report_settings['column'] or ''}}">
                                <input type="hidden" id="hidden_filter" name="filter" value="{{$report_settings['filter'] or ''}}">
                                <input type="hidden" id="hidden_filter_col" name="filter_col_name" value="{{$report_settings['filter_col_name'] or ''}}">
                                <input type="hidden" id="hidden_setting" name="filter_settings" value='{{$report_settings['filter_settings'] or ''}}'>
                                <label for="field-5" class="control-label">Columns</label>
                                <div id="Columns_Drop" class="form-control tree ui-widget-content ui-state-default select2-container select2-container-multi">

                                    <ul class=" select2-choices ui-helper-reset">
                                        @if(isset($report_settings['column']) && $selectedColumns = array_filter(explode(',',$report_settings['column'])))
                                        @foreach($selectedColumns as $selectedColumn)
                                            <li class="{{isset($measures[$report_settings['Cube']][$selectedColumn])?'measures':'dimension'}} ui-draggable" data-cube="{{$report_settings['Cube']}}" data-val="{{$selectedColumn}}">
                                                <span><i class="fa fa-arrows"></i>
                                                    <?php
                                                    $selected_dimension = '';
                                                    if(isset($measures[$report_settings['Cube']][$selectedColumn])){
                                                        $selected_dimension = $measures[$report_settings['Cube']][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']][$selectedColumn]) && !is_array($dimensions[$report_settings['Cube']][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['date'][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']]['date'][$selectedColumn];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Customer'][$selectedColumn])){
                                                        $selected_dimension = $dimensions[$report_settings['Cube']]['Customer'][$selectedColumn];
                                                    }
                                                    ?>
                                                    {{$selected_dimension}}

                                                </span>
                                            </li>
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <label for="field-5" class="control-label">Row</label>
                                <div id="Row_Drop" class="form-control tree ui-widget-content ui-state-default select2-container select2-container-multi">
                                    <ul class=" select2-choices ui-helper-reset">
                                        @if(isset($report_settings['row']) && $selectedRows = array_filter(explode(',',$report_settings['row'])))
                                        @foreach($selectedRows as $selectedRow)
                                            <li class="{{isset($measures[$report_settings['Cube']][$selectedRow])?'measures':'dimension'}} ui-draggable" data-cube="{{$report_settings['Cube']}}" data-val="{{$selectedRow}}">
                                                <span><i class="fa fa-arrows"></i>
                                                    <?php
                                                    $selected_measures = '';
                                                    if(isset($measures[$report_settings['Cube']][$selectedRow])){
                                                        $selected_measures = $measures[$report_settings['Cube']][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']][$selectedRow]) && !is_array($dimensions[$report_settings['Cube']][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['date'][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['date'][$selectedRow];
                                                    } else if(isset($dimensions[$report_settings['Cube']]['Customer'][$selectedRow])){
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['Customer'][$selectedRow];
                                                        $selected_measures = $dimensions[$report_settings['Cube']]['Customer'][$selectedRow];
                                                    }
                                                    ?>
                                                    {{$selected_measures}}
                                                </span>
                                            </li>
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 vertical-border border_right {{Input::get('report')=='run'?'hidden':''}}">
                                <div class="row">
                                    <div class="col-sm-12 vertical-border border_bottom">
                                        <label for="field-5" class="control-label">Dimension</label>
                                    </div>
                                    <div   class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <div class="nested-list with-margins tree">

                                            <ul id="Dimension" class=" ui-helper-reset ui-helper-clearfix">

                                            </ul>

                                        </div>
                                    </div>
                                    <div class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <label for="field-5" class="control-label">Measures</label>
                                    </div>
                                    <div class="col-sm-12 vertical-border" style="margin-top: 15px;padding-top: 15px">
                                        <div id="list-1" class="nested-list tree with-margins">
                                            <ul id="Measures" class="dd-list">

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ Input::get('report')=='run'?'col-sm-12':'col-sm-9'}} table_report_overflow loading">


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
            padding:19px;
            margin-bottom:20px;
            -webkit-border-radius:4px;
            -moz-border-radius:4px;
            border-radius:4px;
            -webkit-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05);
            -moz-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05);
            box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05)
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
            -moz-border-radius:5px;
            -webkit-border-radius:5px;
            border:1px solid #999;
            border-radius:5px;
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
            border:1px solid #94a0b4;
            color:#000
        }

        .select2-container-multi .select2-choices .select2-search-choice{
            padding: 0px;
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

        .li_active{display:none;}
        .table_report_overflow{
       @if(Input::get('report') == 'run')
            width: 99%;
       @else
            width: 74%;
       @endif
        }

    </style>
@include('report.script')
@include('report.filter')
@stop