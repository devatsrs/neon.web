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
            <a href="javascript:void(0)">New Report</a>
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
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="field-5" class="control-label">Cube</label>
                                {{Form::select('Cube',Report::$cube,'',array("class"=>"select2 small"))}}
                            </div>
                            <div class="col-sm-9 vertical-border border_left">
                                <input type="hidden" id="hidden_row" name="row">
                                <input type="hidden" id="hidden_columns" name="column">
                                <label for="field-5" class="control-label">Columns</label>
                                <div id="Columns_Drop" class="form-control ui-widget-content ui-state-default select2-container select2-container-multi">
                                    <ul class=" select2-choices ui-helper-reset"></ul>
                                </div>
                                <label for="field-5" class="control-label">Row</label>
                                <div id="Row_Drop" class="form-control ui-widget-content ui-state-default select2-container select2-container-multi">
                                    <ul class=" select2-choices ui-helper-reset"></ul>
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 vertical-border border_right">
                                <div class="row">
                                    <div class="col-sm-12 vertical-border border_bottom">
                                        <label for="field-5" class="control-label">Dimension</label>
                                    </div>
                                    <div   class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <div class="nested-list dd with-margins">
                                            <ul id="Dimension" class=" ui-helper-reset ui-helper-clearfix">
                                                <li class="dd-item">
                                                    <div>
                                                        DateTime
                                                    </div>
                                                    <ul class="dd-list">
                                                        <li class="dd-item select2-search-choice dimension" data-val="year">
                                                            <div class="dd-handle">
                                                                Year
                                                            </div>
                                                        </li>
                                                        <li class="dd-item select2-search-choice dimension" data-val="quarter">
                                                            <div class="dd-handle">
                                                                Quarter
                                                            </div>
                                                        </li>
                                                        <li class="dd-item select2-search-choice dimension" data-val="month">
                                                            <div class="dd-handle">
                                                                Month
                                                            </div>
                                                        </li>
                                                        <li class="dd-item select2-search-choice dimension" data-val="week">
                                                            <div class="dd-handle">
                                                                Week
                                                            </div>
                                                        </li>
                                                        <li class="dd-item select2-search-choice dimension" data-val="day">
                                                            <div class="dd-handle">
                                                                Day
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension" data-val="AccountID">
                                                    <div class="dd-handle">
                                                        Account
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension" data-val="CompanyGatewayID">
                                                    <div class="dd-handle">
                                                        Gateway
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension" data-val="Trunk">
                                                    <div class="dd-handle">
                                                        Trunk
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension" data-val="CountryID">
                                                    <div class="dd-handle">
                                                        Country
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension" data-val="AreaPrefix">
                                                    <div class="dd-handle">
                                                        Prefix
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension" data-val="vendor">
                                                    <div class="dd-handle">
                                                        Vendor
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension" data-val="currency">
                                                    <div class="dd-handle">
                                                        Currency
                                                    </div>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>
                                    <div class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <label for="field-5" class="control-label">Measures</label>
                                    </div>
                                    <div class="col-sm-12 vertical-border" style="margin-top: 15px;padding-top: 15px">
                                        <div id="list-1" class="nested-list dd with-margins">
                                            <ul id="Measures" class="dd-list">
                                                <li class="dd-item select2-search-choice measures" data-val="TotalCharges">
                                                    <div class="dd-handle">
                                                        Cost
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice measures" data-val="TotalBilledDuration">
                                                    <div class="dd-handle">
                                                        Duration
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice measures" data-val="NoOfCalls">
                                                    <div class="dd-handle">
                                                        No Of Calls
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice measures" data-val="NoOfFailCalls">
                                                    <div class="dd-handle">
                                                        No Of Failed Calls
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-9 table_report_overflow loading">

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

        .select2-container-multi .select2-choices .select2-search-choice{
            padding: 0px;
        }

    </style>
    <script>
        $( function() {

            // There's the Dimension and the Measures
            var $Dimension = $( "#Dimension" ),
                $Measures = $( "#Measures" ),
                $Columns = $( "#Columns_Drop" ),
                $Row = $( "#Row_Drop" );

            // Let the Dimension items be draggable
            $( "li", $Dimension ).draggable({
                helper: "clone",
                cursor: "move"
            });

            // Let the Measures items be draggable
            $( "li", $Measures ).draggable({
                helper: "clone",
                cursor: "move"
            });

            // Let the Measures be droppable, accepting the Dimension items
            $Columns.droppable({
                accept:  function(d) {
                    if(d.hasClass("dimension")|| d.hasClass("measures")){
                        return true;
                    }
                },
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function( event, ui ) {

                    deleteImage( ui.draggable,$Columns );
                    update_columns(ui.draggable,'add');

                }
            });

            // Let the Measures be droppable, accepting the Dimension items
            $Row.droppable({
                accept: function(d) {
                    if(d.hasClass("dimension")|| d.hasClass("measures")){
                        return true;
                    }
                },
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function( event, ui ) {
                    deleteImage( ui.draggable,$Row );
                    update_rows(ui.draggable,'add');

                }
            });

            // Let the Dimension be droppable as well, accepting items from the Measures
            $Dimension.droppable({
                accept: ".dimension",
                classes: {
                    "ui-droppable-active": "custom-state-active"
                },
                drop: function( event, ui ) {
                    recycleImage( ui.draggable,$Dimension);
                    update_rows(ui.draggable,'remove');
                    update_columns(ui.draggable,'remove');

                }
            });

            // Let the Dimension be droppable as well, accepting items from the Measures
            $Measures.droppable({
                accept: ".measures",
                classes: {
                    "ui-droppable-active": "custom-state-active"
                },
                drop: function( event, ui ) {
                    recycleImage( ui.draggable, $Measures);
                    update_rows(ui.draggable,'remove');
                    update_columns(ui.draggable,'remove');

                }
            });


            function deleteImage( $item, $droppable) {
                $item.fadeOut(function() {
                    var $list = $( "ul", $droppable ).length ?
                        $( "ul", $droppable ) :
                        $( "<ul class=' select2-choices ui-helper-reset'/>" ).appendTo( $droppable );

                    //$item.find( "a.ui-icon-trash" ).remove();
                    $item.appendTo( $list ).fadeIn();
                });
            }

            var trash_icon = '';
            function recycleImage( $item ,$droppable) {
                $item.fadeOut(function() {
                    $item
                        .find( "a.ui-icon-refresh" )
                        .remove()
                        .end()
                        .append( trash_icon )
                        .find( "img" )
                        .end()
                        .appendTo( $droppable )
                        .fadeIn();
                });
            }
            //reload_table();
            function reload_table(){
                var data = $("#report-row-col").serialize();
                loading_table('.table_report_overflow',1);
                $.ajax({
                    url:baseurl +'/report/getdatagrid', //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        loading_table(".table_report_overflow",0);
                        $('.table_report_overflow').html(response);
                    },
                    data: data,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false
                });
            }
            $("#hidden_row").on('change', function () {
                reload_table();
            });
            $("#hidden_columns").on('change', function () {
                reload_table();
            });
            function loading_table(table,bit){
                var panel = jQuery(table).closest('.loading');
                if(bit==1){
                    blockUI(panel);
                    panel.addClass('reloading');
                }else{
                    unblockUI(panel);
                    panel.removeClass('reloading');
                }
            }

            function update_rows($item,action) {
                var rows = [];
                var previous_val = $("#hidden_row").val();
                if($("#hidden_row").val() != '') {
                    rows = $("#hidden_row").val().split(',');
                }
                if(action == 'remove') {
                    var index = rows.indexOf($item.attr('data-val'));
                    if (index > -1) {
                        rows.splice(index, 1);
                    }
                }
                if(action == 'add') {
                    rows[rows.length] = $item.attr('data-val');
                }
                $("#hidden_row").val(rows.join(","));
                if($("#hidden_row").val() != previous_val){
                    $("#hidden_row").trigger('change');
                }

            }
            function update_columns($item,action) {
                var columns = [];
                var previous_val = $("#hidden_columns").val();
                if($("#hidden_columns").val() != '') {
                    columns = $("#hidden_columns").val().split(',');
                }
                if(action == 'remove') {
                    var index = columns.indexOf($item.attr('data-val'));
                    if (index > -1) {
                        columns.splice(index, 1);
                    }
                }
                if(action == 'add') {
                    columns[columns.length] = $item.attr('data-val');
                }
                $("#hidden_columns").val(columns.join(","));

                if($("#hidden_columns").val() != previous_val){
                    $("#hidden_columns").trigger('change');
                }

            }
            $("#hidden_row").val('');
            $("#hidden_columns").val('');
        } );

    </script>
@include('report.filter')
@stop