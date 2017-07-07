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
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="field-5" class="control-label">Cube</label>
                                {{Form::select('Cube',Report::$cube,(isset($report_settings['Cube'])?$report_settings['Cube']:''),array("class"=>"select2 small"))}}
                            </div>
                            <div class="col-sm-9 vertical-border border_left">
                                <input type="hidden" id="hidden_row" name="row" value="{{$report_settings['row'] or ''}}">
                                <input type="hidden" id="hidden_columns" name="column" value="{{$report_settings['column'] or ''}}">
                                <input type="hidden" id="hidden_filter" name="filter" value="{{$report_settings['filter'] or ''}}">
                                <input type="hidden" id="hidden_filter_col" name="filter_col_name" value="{{$report_settings['filter_col_name'] or ''}}">
                                <input type="hidden" id="hidden_setting" name="filter_settings" value='{{$report_settings['filter_settings'] or ''}}'>
                                <label for="field-5" class="control-label">Columns</label>
                                <div id="Columns_Drop" class="form-control ui-widget-content ui-state-default select2-container select2-container-multi">
                                    <ul class=" select2-choices ui-helper-reset">
                                        @if(isset($report_settings['column']) && $selectedColumns = array_filter(explode(',',$report_settings['column'])))
                                        @foreach($selectedColumns as $selectedColumn)
                                            <li class="dd-item select2-search-choice {{isset($dimensions[$report_settings['Cube']][$selectedColumn])?'dimension':'measures'}} ui-draggable" data-cube="{{$report_settings['Cube']}}" data-val="{{$selectedColumn}}">
                                                <div class="dd-handle">
                                                    {{$dimensions[$report_settings['Cube']][$selectedColumn] or $measures[$report_settings['Cube']][$selectedColumn]}}
                                                </div>
                                            </li>
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <label for="field-5" class="control-label">Row</label>
                                <div id="Row_Drop" class="form-control ui-widget-content ui-state-default select2-container select2-container-multi">
                                    <ul class=" select2-choices ui-helper-reset">
                                        @if(isset($report_settings['row']) && $selectedRows = array_filter(explode(',',$report_settings['row'])))
                                        @foreach($selectedRows as $selectedRow)
                                            <li class="dd-item select2-search-choice {{isset($dimensions[$report_settings['Cube']][$selectedRow])?'dimension':'measures'}} ui-draggable" data-cube="{{$report_settings['Cube']}}" data-val="{{$selectedRow}}">
                                                <div class="dd-handle">
                                                    {{$dimensions[$report_settings['Cube']][$selectedRow] or $measures[$report_settings['Cube']][$selectedRow]}}
                                                </div>
                                            </li>
                                        @endforeach
                                        @endif
                                    </ul>
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
                                                @foreach($dimensions as $cube => $dimension)
                                                    @foreach($dimension as $dimension_key => $dimension_val)
                                                        <li class="dd-item select2-search-choice dimension" data-cube="{{$cube}}" data-val="{{$dimension_key}}">
                                                            <div class="dd-handle">
                                                                {{$dimension_val}}
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @endforeach
                                            </ul>

                                        </div>
                                    </div>
                                    <div class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <label for="field-5" class="control-label">Measures</label>
                                    </div>
                                    <div class="col-sm-12 vertical-border" style="margin-top: 15px;padding-top: 15px">
                                        <div id="list-1" class="nested-list dd with-margins">
                                            <ul id="Measures" class="dd-list">
                                                @foreach($measures as $cube => $measure)
                                                    @foreach($measure as $measure_key => $measure_val)
                                                        <li class="dd-item select2-search-choice measures" data-cube="{{$cube}}" data-val="{{$measure_key}}">
                                                            <div class="dd-handle">
                                                                {{$measure_val}}
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @endforeach
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

    </style>
    <script>
        var checked = '';
        var filter_settings = {};
        $( function() {

            // There's the Dimension and the Measures
            var $Dimension = $( "#Dimension" ),
                $Measures = $( "#Measures" ),
                $Columns = $( "#Columns_Drop" ),
                $Filter = $( "#Filter_Drop" ),
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
            // Let the Dimension items be draggable
            $( "li", $Columns ).draggable({
                helper: "clone"
            });

            // Let the Measures items be draggable
            $( "li", $Filter ).draggable({
                helper: "clone"
            });
            // Let the Dimension items be draggable
            $( "li", $Row ).draggable({
                helper: "clone"
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
                out: function( event, ui ) {
                    var drop_ele_val = $(ui.draggable).attr('data-val');
                    if( $Dimension.find('[data-val="'+drop_ele_val+'"]').length == 1 || $Measures.find('[data-val="'+drop_ele_val+'"]').length) {
                        $Columns.find('[data-val="'+drop_ele_val+'"]').remove();
                        update_columns(ui.draggable,'remove',1);
                    }
                },
                drop: function( event, ui ) {

                    deleteImage( ui.draggable,$Columns );
                    update_rows(ui.draggable,'remove',0);
                    update_columns(ui.draggable,'add',1);

                }
            });

            // Let the Measures be droppable, accepting the Dimension items
            $Row.droppable({
                accept: function(d) {
                    if(d.hasClass("dimension")){
                        return true;
                    }
                },
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                out: function( event, ui ) {
                    var drop_ele_val = $(ui.draggable).attr('data-val');
                    if( $Dimension.find('[data-val="'+drop_ele_val+'"]').length == 1 || $Measures.find('[data-val="'+drop_ele_val+'"]').length) {
                        $Row.find('[data-val="'+drop_ele_val+'"]').remove();
                        update_rows(ui.draggable,'remove',1);
                    }
                },
                drop: function( event, ui ) {
                    deleteImage( ui.draggable,$Row );
                    update_columns(ui.draggable,'remove',0);
                    update_rows(ui.draggable,'add',1);

                }
            });

            // Let the Measures be droppable, accepting the Dimension items
            $Filter.droppable({
                accept: function(d) {
                    if(d.hasClass("dimension")){
                        return true;
                    }
                },
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                out: function( event, ui ) {
                    var drop_ele_val = $(ui.draggable).attr('data-val');
                    if( $Dimension.find('[data-val="'+drop_ele_val+'"]').length == 1 || $Measures.find('[data-val="'+drop_ele_val+'"]').length) {
                        $Filter.find('[data-val="'+drop_ele_val+'"]').remove();
                        update_filter(ui.draggable,'remove',1);

                    }
                },
                drop: function( event, ui ) {
                    deleteImage( ui.draggable,$Filter );
                    update_filter(ui.draggable,'add',0);
                    show_filter(ui.draggable);
                    //update_rows(ui.draggable,'add',1);

                }
            });



            function deleteImage( $item, $droppable) {
                var element=$item.clone();
                var drop_ele_val = $(element).attr('data-val');
                if( $droppable.find('[data-val="'+drop_ele_val+'"]').length == 0){
                    var $list = $( "ul", $droppable ).length ?
                            $( "ul", $droppable ) :
                            $( "<ul class=' select2-choices ui-helper-reset'/>" ).appendTo( $droppable );
                    $(element).draggable({helper: 'clone'});
                    $(element).appendTo( $list ).fadeIn();
                }
            }


            //reload_table();
            function reload_table(){
                var data = $("#report-row-col").serialize()+'&'+$("#add-new-filter-form").serialize();
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

            function update_rows($item,action,trigger) {
                var rows = [];
                var previous_val = $("#hidden_row").val();
                if($("#hidden_row").val() != '') {
                    rows = $("#hidden_row").val().split(',');
                }
                //if(action == 'remove') {
                    var index = rows.indexOf($item.attr('data-val'));
                    if (index > -1) {
                        rows.splice(index, 1);
                    }
                //}
                if(action == 'add') {
                    rows[rows.length] = $item.attr('data-val');
                }
                $("#hidden_row").val(rows.join(","));
                if($("#hidden_row").val() != previous_val && trigger == 1){
                    $("#hidden_row").trigger('change');
                }

            }
            function update_columns($item,action,trigger) {
                var columns = [];
                var previous_val = $("#hidden_columns").val();
                if($("#hidden_columns").val() != '') {
                    columns = $("#hidden_columns").val().split(',');
                }
                //if(action == 'remove') {
                    var index = columns.indexOf($item.attr('data-val'));
                    if (index > -1) {
                        columns.splice(index, 1);
                    }
                //}
                if(action == 'add') {
                    columns[columns.length] = $item.attr('data-val');
                }
                $("#hidden_columns").val(columns.join(","));

                if($("#hidden_columns").val() != previous_val && trigger == 1){
                    $("#hidden_columns").trigger('change');
                }

            }
            function show_filter($items){
                $items.attr('data-val');
                var data = $("#report-row-col").serialize();
                var date_fields = {{json_encode(Report::$date_fields)}};
                if($.inArray($("#hidden_filter_col").val(),date_fields) > -1){
                    $(".filter_data_table").hide();
                    $(".filter_data_wildcard").hide();
                    $("li.date_filters a").trigger('click');
                    $(".date_filters").show();
                }else{
                    $(".filter_data_table").show();
                    $(".filter_data_wildcard").show();
                    $("li.filter_data_table a").trigger('click');
                    $(".date_filters").hide();
                    filter_data_table();
                }



                $('#add-new-modal-filter').modal('show');
            }

            function update_filter($item,action,trigger) {
                var rows = [];
                var previous_val = $("#hidden_filter").val();
                if($("#hidden_filter").val() != '') {
                    rows = $("#hidden_filter").val().split(',');
                }
                //if(action == 'remove') {
                var index = rows.indexOf($item.attr('data-val'));
                if (index > -1) {
                    rows.splice(index, 1);
                }
                //}
                if(action == 'add') {
                    rows[rows.length] = $item.attr('data-val');
                    $('#hidden_filter_col').val($item.attr('data-val'));
                }
                $("#hidden_filter").val(rows.join(","));
                if($("#hidden_filter").val() != previous_val && trigger == 1){
                    $("#hidden_filter").trigger('change');
                }

            }
            $('#report-update').click(function(e){
                filter_settings[$("#hidden_filter_col").val()] = $("#add-new-filter-form").serialize();
                $('#hidden_setting').val(JSON.stringify(filter_settings));
                e.preventDefault();
                reload_table();
            });

            $(document).on('click', '#table-filter-list tbody tr', function() {
                if (checked =='') {
                    $(this).toggleClass('selected');
                    if($(this).is('tr')) {
                        if ($(this).hasClass('selected')) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                        }
                    }
                }
            });
            @if(empty($report_settings))
            $("#hidden_filter").val('');
            $("#hidden_row").val('');
            $("#hidden_columns").val('');
            @else
                    reload_table();
            @endif
        } );

        function filter_data_table(){
            data_table_filter = $("#table-filter-list").dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/report/getdatalist",
                "iDisplayLength": 10,
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'> f>r> t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'asc']],
                "fnServerParams": function(aoData) {
                    aoData.push(
                            {"name":"filter_col_name","value":$("#hidden_filter_col").val()},
                            {"name":"Cube","value":$("#report-row-col [name='Cube']").val()}

                    );
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push(
                            {"name":"filter_col_name","value":$("#hidden_filter_col").val()},
                            {"name":"Cube","value":$("#report-row-col [name='Cube']").val()},
                            {"name":"Export","value":1}
                    );
                },
                "aoColumns":
                        [
                            {"bSortable": false,
                                mRender: function(id, type, full) {
                                    return '<div class="checkbox "><input type="checkbox" name="'+$("#hidden_filter_col").val()+'[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            }, //0Checkbox
                            { "bSortable": true}
                        ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/currency/exports/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/currency/exports/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                }
            });
            $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });
            $("#table-filter-list tbody input[type=checkbox]").each(function (i, el) {
                var $this = $(el),
                        $p = $this.closest('tr');

                $(el).on('change', function () {
                    var is_checked = $this.is(':checked');

                    $p[is_checked ? 'addClass' : 'removeClass']('highlight');
                });
            });
            $("#selectall").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#table-filter-list tbody tr').each(function(i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });
            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });
            //select all record
            $('#selectallbutton').click(function(){
                if($('#selectallbutton').is(':checked')){
                    checked = 'checked=checked disabled';
                    $("#selectall").prop("checked", true).prop('disabled', true);
                    $('#table-filter-list tbody tr').each(function (i, el) {
                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                        $(this).addClass('selected');
                    });

                }else{
                    checked = '';
                    $("#selectall").prop("checked", false).prop('disabled', false);
                    $('#table-filter-list tbody tr').each(function (i, el) {
                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                        $(this).removeClass('selected');
                    });
                }
            });
        }

    </script>
@include('report.filter')
@stop