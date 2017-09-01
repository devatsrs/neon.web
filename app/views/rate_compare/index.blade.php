@extends('layout.main')
@section('content')
    <style>
        .lowest_rate{
            background-color: #ff6600;
        }
    </style>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Rate Compare</strong>
        </li>
    </ol>
    <h3>Rate Compare</h3>

    <br>
    <div class="row">
        <div class="col-md-12">
            <form role="form" id="rate-compare-search-form" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Filter
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="form-group">

                            <label for="field-1" class="col-sm-1 control-label">Code</label>
                            <div class="col-sm-2">
                                <input type="text" name="Code" class="form-control" id="field-1" placeholder="" value="" />
                            </div>

                            <label for="field-1" class="col-sm-1 control-label">Description</label>
                            <div class="col-sm-2">
                                <input type="text" name="Description" class="form-control" id="field-1" placeholder="" value="" />
                            </div>

                            <label for="field-1" class="col-sm-1 control-label">Trunk</label>
                            <div class="col-sm-2">
                                {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2")) }}

                            </div>
                            <label for="field-1" class="col-sm-1 control-label">CodeDeck</label>
                            <div class="col-sm-2">
                                {{ Form::select('CodeDeckId', $codedecklist, '' , array("class"=>"select2")) }}

                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-1 control-label">Currency</label>
                            <div class="col-sm-2">
                                {{Form::select('Currency', $currencies, $CurrencyID ,array("class"=>"form-control select2"))}}
                            </div>

                            <label for="field-1" class="col-sm-1 control-label">Group By</label>
                            <div class="col-sm-2">
                                {{Form::select('GroupBy', ["code"=>"Code", "description" => "Description"], $GroupBy ,array("class"=>"form-control select2"))}}
                            </div>

                            <label for="field-1" class="col-sm-1 control-label">Effective</label>
                            <div class="col-sm-2">
                                {{Form::select('Effective', ["Now"=>"Current", "Future" => "Future", "Selected" => "Selected"], 'Now' ,array("class"=>"form-control select2"))}}
                            </div>
                            <div class="SelectedEffectiveDate_Class hidden">
                                <label for="field-1" class="col-sm-1 control-label">Date</label>
                                <div class="col-sm-2">
                                    {{Form::text('SelectedEffectiveDate', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-start-date"=>"" ,"data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-1 control-label">Source</label>
                            <label for="field-1" class="col-sm-1 control-label">Vendors</label>
                            <div class="col-sm-2">
                                {{Form::select('SourceVendors[]', $all_vendors, array() ,array("class"=>"form-control select2",'multiple'))}}
                            </div>

                            <label for="field-1" class="col-sm-1 control-label">Customers</label>
                            <div class="col-sm-2">
                                {{Form::select('SourceCustomers[]', $all_customers, array() ,array("class"=>"form-control select2",'multiple'))}}
                            </div>

                            <label for="field-1" class="col-sm-1 control-label">Rate Tables</label>
                            <div class="col-sm-2">
                                {{Form::select('SourceRateTables[]', $rate_table, array() ,array("class"=>"form-control select2",'multiple'))}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="field-1" class="col-sm-1 control-label">Destination</label>
                            <label for="field-1" class="col-sm-1 control-label">Vendors</label>
                            <div class="col-sm-2">
                                {{Form::select('DestinationVendors[]', $all_vendors, array() ,array("class"=>"form-control select2",'multiple'))}}
                            </div>

                            <label for="field-1" class="col-sm-1 control-label">Customers</label>
                            <div class="col-sm-2">
                                {{Form::select('DestinationCustomers[]', $all_customers, array() ,array("class"=>"form-control select2",'multiple'))}}
                            </div>

                            <label for="field-1" class="col-sm-1 control-label">Rate Tables</label>
                            <div class="col-sm-2">
                                {{Form::select('DestinationRateTables[]', $rate_table, array() ,array("class"=>"form-control select2",'multiple'))}}
                            </div>
                        </div>
                        <p style="text-align: right;">
                            <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                <i class="glyphicon glyphicon-circle-arrow-up"></i>
                                Search
                            </button>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
        </tr>

        </thead>

        <tbody>
        <tr class="main">

        </tr>

        </tbody>
    </table>


    <script type="text/javascript">
        jQuery(document).ready(function($) {
            //var data_table;


            $('select[name="Effective"]').on( "change",function(e) {
                var selection = $(this).val();
                var hidden = false;
                if ($(this).hasClass('hidden')) {
                    hidden = true;
                }
                $(".SelectedEffectiveDate_Class").addClass("hidden");
                console.log(selection);

                if(selection == 'Selected') {
                    $(".SelectedEffectiveDate_Class").removeClass("hidden");
                }

            });



            $("#rate-compare-search-form").submit(function(e) {



                var Code, Description, Currency,CodeDeck,Trunk,GroupBy,Effective,SelectedEffectiveDate, SourceVendors,SourceCustomers,SourceRateTables,DestinationVendors,DestinationCustomers,DestinationRateTables;
                Trunk = $("#rate-compare-search-form select[name='Trunk']").val();
                CodeDeck = $("#rate-compare-search-form select[name='CodeDeckId']").val();
                Currency = $("#rate-compare-search-form select[name='Currency']").val();
                Code = $("#rate-compare-search-form input[name='Code']").val();
                Description = $("#rate-compare-search-form input[name='Description']").val();
                GroupBy = $("#rate-compare-search-form select[name='GroupBy']").val();
                Effective = $("#rate-compare-search-form select[name='Effective']").val();
                SelectedEffectiveDate = $("#rate-compare-search-form input[name='SelectedEffectiveDate']").val();
                SourceVendors = $("#rate-compare-search-form select[name='SourceVendors[]']").val();
                SourceCustomers = $("#rate-compare-search-form select[name='SourceCustomers[]']").val();
                SourceRateTables = $("#rate-compare-search-form select[name='SourceRateTables[]']").val();
                DestinationVendors = $("#rate-compare-search-form select[name='DestinationVendors[]']").val();
                DestinationCustomers = $("#rate-compare-search-form select[name='DestinationCustomers[]']").val();
                DestinationRateTables = $("#rate-compare-search-form select[name='DestinationRateTables[]']").val();

                if(typeof Trunk  == 'undefined' || Trunk == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a Trunk", "Error", toastr_opts);
                    return false;
                }
                if(typeof CodeDeck  == 'undefined' || CodeDeck == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a CodeDeck", "Error", toastr_opts);
                    return false;
                }
                if((typeof Code  == 'undefined' || Code == '' ) && (typeof Description  == 'undefined' || Description == '' )){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Enter a Code Or Description", "Error", toastr_opts);
                    return false;
                }
                if(typeof Currency  == 'undefined' || Currency == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a Currency", "Error", toastr_opts);
                    return false;
                }

                if(SourceVendors == null && SourceCustomers == null && SourceRateTables == null && DestinationVendors == null && DestinationCustomers == null && DestinationRateTables == null  ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please select a Source or a Destination", "Error", toastr_opts);
                    return false;
                }


                var aoColumns = [
                    { "bSortable": false },

                ];
                var aoColumnDefs = [
                    { "sClass": "", "aTargets": [ 0 ] } ,

                ];


                data_table = $("#table-4").dataTable({
                    "bDestroy": true, // Destroy when resubmit form
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/rate_compare/search_ajax_datagrid/json",
                    "fnServerParams": function(aoData) {
                        aoData.push({ "name" : "Code"  , "value" : Code },{ "name" : "Description"  , "value" : Description },{ "name" : "Currency"  , "value" : Currency },{ "name" : "CodeDeck"  , "value" : CodeDeck },{ "name" : "Trunk"  , "value" : Trunk },{ "name" : "GroupBy"  , "value" : GroupBy },{ "name" : "Effective"  , "value" : Effective },{ "name" : "SelectedEffectiveDate"  , "value" : SelectedEffectiveDate },{ "name" : "SourceVendors"  , "value" : SourceVendors },{ "name" : "SourceCustomers"  , "value" : SourceCustomers },{ "name" : "SourceRateTables"  , "value" : SourceRateTables },{ "name" : "DestinationVendors"  , "value" : DestinationVendors },{ "name" : "DestinationCustomers"  , "value" : DestinationCustomers },{ "name" : "DestinationRateTables"  , "value" : DestinationRateTables });

                        data_table_extra_params.length = 0;

                        data_table_extra_params.push({ "name" : "Code"  , "value" : Code },{ "name" : "Description"  , "value" : Description },{ "name" : "Currency"  , "value" : Currency },{ "name" : "CodeDeck"  , "value" : CodeDeck },{ "name" : "Trunk"  , "value" : Trunk },{ "name" : "GroupBy"  , "value" : GroupBy },{ "name" : "Effective"  , "value" : Effective },{ "name" : "SelectedEffectiveDate"  , "value" : SelectedEffectiveDate },{ "name" : "SourceVendors"  , "value" : SourceVendors },{ "name" : "SourceCustomers"  , "value" : SourceCustomers },{ "name" : "SourceRateTables"  , "value" : SourceRateTables },{ "name" : "DestinationVendors"  , "value" : DestinationVendors },{ "name" : "DestinationCustomers"  , "value" : DestinationCustomers },{ "name" : "DestinationRateTables"  , "value" : DestinationRateTables },{"name":"Export","value":1});
                    },
                    "iDisplayLength": 10,
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, "asc"]],
                    "aoColumnDefs": aoColumnDefs,
                    "aoColumns":aoColumns,
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/rate_compare/search_ajax_datagrid/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/rate_compare/search_ajax_datagrid/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
                    "fnDrawCallback": function(results) {

                        $('.btn.btn').button('reset');

                        var source_column_index = [];
                        var destination_column_index = [];

                        if( typeof results.jqXHR.responseJSON.sColumns != 'undefined') {

                            $("#table-4"+'>thead').html('<tr></tr>');
                            $.each(results.jqXHR.responseJSON.sColumns, function (k, col) {
                                console.log(k + col);
                                var _class = "";

                                if (col.indexOf("Source") >= 0) {
                                    _class = "source";
                                    source_column_index.push(k);

                                } else if (col.indexOf("Destination:") >= 0) {
                                    _class = "destination";
                                    destination_column_index.push(k);
                                }


                                if(col == 'Destination') {
                                    col = '';
                                }


                                str = '<th class="'+ _class +'">' + col + '</th>';
                                $(str).appendTo("#table-4"+'>thead>tr');



                            });


                        }


                        if( typeof results.jqXHR.responseJSON.aaData != 'undefined') {

                            $("#table-4"+'>tbody').html('<tr></tr>');
                            if(results.jqXHR.responseJSON.aaData.length == 0) {
                                html = "<td ><center>No Data found</center></td>";
                                $(html).appendTo("#table-4"+'>tbody>tr:last');

                            }
                            $.each(results.jqXHR.responseJSON.aaData, function (k, row) {

                                console.log(k + row);
                                var _class = html = "";

                                for(var i = 0 ; i < row.length ; i++ ){
                                    var str = _class = "";


                                    str = row[i] ;
                                    if($.inArray( i, source_column_index ) != -1 ){
                                        _class = "source";
                                    }else if($.inArray( i, destination_column_index ) != -1 ){
                                        _class = "destination";

                                    }

                                    html += '<td class="'+ _class +'">' + str + '</td>';

                                }

                                $(html).appendTo("#table-4"+'>tbody>tr:last');
                                $('<tr class="dynamic"></tr>').appendTo("#table-4"+'>tbody');

                            });


                        }


                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }
                });

                return false;
            });


            // Replace Checboxes
            $(".pagination a").click(function(ev) {
                replaceCheckboxes();
            });

        });
    </script>
    <style>
        .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data{
            right: 30px !important;
        }
        .dataTable th.source , .dataTable td.source {
            background: #d7ef87 !important;
        }

        .dataTable th.destination , .dataTable td.destination {
            background: #ffc8c8  !important;
        }
    </style>
@stop