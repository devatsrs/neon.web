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
    <li>

        <a href="{{URL::to('accounts')}}">Accounts</a>
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
                            {{Form::select('GroupBy', ["code"=>"Code", "description" => "Description"], '' ,array("class"=>"form-control select2"))}}
                        </div>

                        <label for="field-1" class="col-sm-1 control-label">Effective</label>
                        <div class="col-sm-2">
                            {{Form::select('Effective', ["Now"=>"Now", "Future" => "Future", "Selected" => "Selected"], 'Now' ,array("class"=>"form-control select2"))}}
                        </div>
                        <div class="SelectedEffectiveDate_Class hidden">
                            <label for="field-1" class="col-sm-1 control-label">Date</label>
                            <div class="col-sm-2">
                                {{Form::text('SelectedEffectiveDate', '' ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-start-date"=>"" ,"data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
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
            <th>Destination</th>
            <th class="source">Source:Vendor<br> 4G-Network LTD</th>
            <th class="source">Source:Vendor<br> 2Way Solution</th>
            <th class="source">Source:Customer<br> 4G-Network LTD</th>
            <th class="source">Source:Rate Table<br> Rate Table 1</th>
            <th class="destination">Destination:Vendor<br> 4G-Network LTD</th>
            <th class="destination">Destination:Vendor<br> 2Way Solution</th>
            <th class="destination">Destination:Customer<br> 4G-Network LTD</th>
            <th class="destination">Destination:Rate Table<br> Rate Table 2</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>91 - India</td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
        </tr>
        <tr>
            <td>911 - India</td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
        </tr>
        <tr>
            <td>912 - India</td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
        </tr>
        <tr>
            <td>913 - India</td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
        </tr>
        <tr>
            <td>914 - India</td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
        </tr>
        <tr>
            <td>91 - India</td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="source">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
            <td class="destination">0.01  <span class="float-right"><a href="#" class="btn btn-default btn-sm"><i class="entypo-pencil"></i> &nbsp;</a></span></td>
        </tr>
    </tbody>
</table>


<script type="text/javascript">
    jQuery(document).ready(function($) {
        //var data_table;


        $('select[name="SelectedEffectiveDate"]').on( "change",function(e) {
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
        
        /*if('{{$LCRPosition}}'=='5'){
            setTimeout(function(){
                $('#dt_company6').addClass("hidden");
                $('#dt_company7').addClass("hidden");
                $('#dt_company8').addClass("hidden");
                $('#dt_company9').addClass("hidden");
                $('#dt_company10').addClass("hidden");
            },10);
        }else{
            setTimeout(function(){
                $('#dt_company6').removeClass("hidden");
                $('#dt_company7').removeClass("hidden");
                $('#dt_company8').removeClass("hidden");
                $('#dt_company9').removeClass("hidden");
                $('#dt_company10').removeClass("hidden");
            },10);
        }*/


        $("#rate-compare-search-form").submit(function(e) {


            var Code, Description, Currency,CodeDeck,Trunk,GroupBy,Effective, SourceVendors,SourceCustomers,SourceRateTables,DestinationVendors,DestinationCustomers,DestinationRateTables,aoColumns,aoColumnDefs;
            Trunk = $("#rate-compare-search-form select[name='Trunk']").val();
            CodeDeck = $("#rate-compare-search-form select[name='CodeDeckId']").val();
            Currency = $("#rate-compare-search-form select[name='Currency']").val();
            Code = $("#rate-compare-search-form input[name='Code']").val();
            Description = $("#rate-compare-search-form input[name='Description']").val();
            GroupBy = $("#rate-compare-search-form select[name='GroupBy']").val();
            Effective = $("#rate-compare-search-form select[name='Effective']").val();
            SourceVendors = $("#rate-compare-search-form select[name='SourceVendors']").val();
            SourceCustomers = $("#rate-compare-search-form select[name='SourceCustomers']").val();
            SourceRateTables = $("#rate-compare-search-form select[name='SourceRateTables']").val();
            DestinationVendors = $("#rate-compare-search-form select[name='DestinationVendors']").val();
            DestinationCustomers = $("#rate-compare-search-form select[name='DestinationCustomers']").val();
            DestinationRateTables = $("#rate-compare-search-form select[name='DestinationRateTables']").val();

            if(true){
                setTimeout(function(){
                    $('#dt_company6').addClass("hidden");
                    $('#dt_company7').addClass("hidden");
                    $('#dt_company8').addClass("hidden");
                    $('#dt_company9').addClass("hidden");
                    $('#dt_company10').addClass("hidden");
                },10);
                 aoColumns = [
                     {}, //1 Destination
                     { "bSortable": false}, //2 Company 1
                     { "bSortable": false}, //3 Company 2
                     { "bSortable": false}, //4 Company 3
                     { "bSortable": false}, //5 Company 4
                     { "bSortable": false}, //6 Company 5
                     { "bVisible": false}, //7 Company 6
                     { "bVisible": false}, //8 Company 7
                     { "bVisible": false}, //9 Company 8
                     { "bVisible": false}, //10 Company 9
                     { "bVisible": false} //11 Company 10

                ];

                 aoColumnDefs = [
                    {    "sClass": "destination", "aTargets": [ 0 ] },
                    {    "sClass": "rate1_class", "aTargets": [ 1 ] },
                    {    "sClass": "rate2_class", "aTargets": [ 2 ] },
                    {    "sClass": "rate3_class", "aTargets": [ 3 ] },
                    {    "sClass": "rate4_class", "aTargets": [ 4 ] },
                    {    "sClass": "rate5_class", "aTargets": [ 5 ] }
                ];
            }else{
                setTimeout(function(){
                    $('#dt_company6').removeClass("hidden");
                    $('#dt_company7').removeClass("hidden");
                    $('#dt_company8').removeClass("hidden");
                    $('#dt_company9').removeClass("hidden");
                    $('#dt_company10').removeClass("hidden");
                },10);
                 aoColumns = [
                    {}, //1 Destination
                    { "bSortable": false}, //2 Company 1
                    { "bSortable": false}, //3 Company 2
                    { "bSortable": false}, //4 Company 3
                    { "bSortable": false}, //5 Company 4
                    { "bSortable": false}, //6 Company 5
                    { "bSortable": false}, //7 Company 6
                    { "bSortable": false}, //8 Company 7
                    { "bSortable": false}, //9 Company 8
                    { "bSortable": false}, //10 Company 9
                    { "bSortable": false} //11 Company 10

                ];

                 aoColumnDefs = [
                    {    "sClass": "destination", "aTargets": [ 0 ] },
                    {    "sClass": "rate1_class", "aTargets": [ 1 ] },
                    {    "sClass": "rate2_class", "aTargets": [ 2 ] },
                    {    "sClass": "rate3_class", "aTargets": [ 3 ] },
                    {    "sClass": "rate4_class", "aTargets": [ 4 ] },
                    {    "sClass": "rate5_class", "aTargets": [ 5 ] },
                    {    "sClass": "rate6_class", "aTargets": [ 6 ] },
                    {    "sClass": "rate7_class", "aTargets": [ 7 ] },
                    {    "sClass": "rate8_class", "aTargets": [ 8 ] },
                    {    "sClass": "rate9_class", "aTargets": [ 9 ] },
                    {    "sClass": "rate10_class", "aTargets": [ 10 ] }
                ];
            }
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
            data_table = $("#table-4").dataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/rate_compare/search_ajax_datagrid",
                "fnServerParams": function(aoData) {
                    aoData.push({ "name" : "Code"  , "value" : Code },{ "name" : "Description"  , "value" : Description },{ "name" : "Currency"  , "value" : Currency },{ "name" : "CodeDeck"  , "value" : CodeDeck },{ "name" : "Trunk"  , "value" : Trunk },{ "name" : "GroupBy"  , "value" : GroupBy },{ "name" : "Effective"  , "value" : Effective },{ "name" : "SourceVendors"  , "value" : SourceVendors },{ "name" : "SourceCustomers"  , "value" : SourceCustomers },{ "name" : "SourceRateTables"  , "value" : SourceRateTables },{ "name" : "DestinationVendors"  , "value" : DestinationVendors },{ "name" : "DestinationCustomers"  , "value" : DestinationCustomers },{ "name" : "DestinationRateTables"  , "value" : DestinationRateTables });

                    data_table_extra_params.length = 0;

                    data_table_extra_params.push({ "name" : "Code"  , "value" : Code },{ "name" : "Description"  , "value" : Description },{ "name" : "Currency"  , "value" : Currency },{ "name" : "CodeDeck"  , "value" : CodeDeck },{ "name" : "Trunk"  , "value" : Trunk },{ "name" : "GroupBy"  , "value" : GroupBy },{ "name" : "Effective"  , "value" : Effective },{ "name" : "SourceVendors"  , "value" : SourceVendors },{ "name" : "SourceCustomers"  , "value" : SourceCustomers },{ "name" : "SourceRateTables"  , "value" : SourceRateTables },{ "name" : "DestinationVendors"  , "value" : DestinationVendors },{ "name" : "DestinationCustomers"  , "value" : DestinationCustomers },{ "name" : "DestinationRateTables"  , "value" : DestinationRateTables },{"name":"Export","value":1});
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
                                    "sUrl": baseurl + "/lcr/search_ajax_datagrid/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/lcr/search_ajax_datagrid/csv",
                                    sButtonClass: "save-collection btn-sm"
                                }
                            ]
                        },
                "fnDrawCallback": function(results) {

                        //results.aoData[0]._aData[0]="---------";


                        $('.btn.btn').button('reset');

                        //Clear All Fields on Refresh
                        $('#dt_company1').html("");
                        $('#dt_company2').html("");
                        $('#dt_company3').html("");
                        $('#dt_company4').html("");
                        $('#dt_company5').html("");
                        $('#dt_company6').html("");
                        $('#dt_company7').html("");
                        $('#dt_company8').html("");
                        $('#dt_company9').html("");
                        $('#dt_company10').html("");

                        // console.log(data_table.oApi.aoColumns);
                        //data_table.Columns[0].ColumnName = "newColumnName";
                        if (typeof results.jqXHR.responseJSON.sColumns[1] != 'undefined') {
                            $('#dt_company1').html( results.jqXHR.responseJSON.sColumns[1] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[2] != 'undefined') {
                            $('#dt_company2').html( results.jqXHR.responseJSON.sColumns[2] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[3] != 'undefined') {
                            $('#dt_company3').html( results.jqXHR.responseJSON.sColumns[3] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[4] != 'undefined') {
                            $('#dt_company4').html( results.jqXHR.responseJSON.sColumns[4] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[5] != 'undefined') {
                            $('#dt_company5').html( results.jqXHR.responseJSON.sColumns[5] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[6] != 'undefined') {
                            $('#dt_company6').html( results.jqXHR.responseJSON.sColumns[6] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[7] != 'undefined') {
                            $('#dt_company7').html( results.jqXHR.responseJSON.sColumns[7] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[8] != 'undefined') {
                            $('#dt_company8').html( results.jqXHR.responseJSON.sColumns[8] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[9] != 'undefined') {
                            $('#dt_company9').html( results.jqXHR.responseJSON.sColumns[9] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[10] != 'undefined') {
                            $('#dt_company10').html( results.jqXHR.responseJSON.sColumns[10] );
                        }

                        //mark_lowest_rate_selected(results);

                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }
            });
            e.stopPropagation();
            return false;
        });


        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });

        function mark_lowest_rate_selected(result){
            var rates = new Array();
            var selected_index , min_rate;
            $('#table-4 > tbody > tr').each(function(index, row){

                var i = 0;
                min_rate = 0;
                selected_index = -1;

                $(row).find("td").each(function(){

                    if(i++ > 0){

                        td_rate_val  = $(this).html();
                        var br_index = td_rate_val.indexOf("<br>");
                        if( br_index > 0){
                            if(min_rate==0){
                                min_rate = td_rate_val.substr(0,br_index);
                                selected_index = i;
                            }else{
                                rate  = td_rate_val.substr(0,br_index);
                                if(rate < min_rate){
                                    min_rate = rate;
                                    selected_index = i;
                                }
                            }
                        }
                    }
                });
                console.log(i +" min_rate  " +min_rate);
                console.log(i +" selected_index"+selected_index);
                if(selected_index > -1 ){
                    $(row).find("td:nth-child("+selected_index+")" ).addClass( "lowest_rate" );
                }
            });
        }
    });
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
th.source , td.source{
    background: #eff5da !important;
}

th.destination , td.destination {
    background: #ffc8c8  !important;
}
</style>
@stop