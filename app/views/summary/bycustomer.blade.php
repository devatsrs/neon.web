@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Summary Reports By Customer</strong>
    </li>
</ol>
<h3>Summary Reports By Customer</h3>

@include('includes.errors')
@include('includes.success')

<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="summer_filter">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" for="field-1">Start date</label>
                        <div class="col-sm-2">
                            <input type="text" name="StartDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}"/>
                        </div>
                        <label class="col-sm-1 control-label" for="field-1">End date</label>
                        <div class="col-sm-2">
                            <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d',strtotime(" +1 day"))}}" data-enddate="{{date('Y-m-d', strtotime('+1 day') )}}" />
                        </div>
                        <label class="col-sm-1 control-label" for="field-1">Gateway Account</label>
                        <div class="col-sm-2">
                            {{ Form::select('AccountID',$account,'', array("class"=>"select2")) }}
                        </div>
                        <label class="col-sm-1 control-label" for="field-1">Gateway</label>
                        <div class="col-sm-2">
                            {{ Form::select('GatewayID',$gateway,'', array("class"=>"select2")) }}
                        </div>

                    </div>
                    <p style="text-align: right;">
                        <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                            <i class="entypo-search"></i>
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
        <th width="20%">Customer Name</th>
        <th width="20%">No. of Calls</th>
        <th width="20%">Duration<br>mm:ss</th>
        <th width="20%">Billed Duration<br>mm:ss</th>
        <th width="20%">Charged Amount</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");

        //show_loading_bar(40);
        $('#summer_filter').submit(function(e){
        e.preventDefault();
        $searchFilter.StartDate = $("#summer_filter [name='StartDate']").val();
        $searchFilter.EndDate = $("#summer_filter [name='EndDate']").val();
        $searchFilter.AccountID = $("#summer_filter [name='AccountID']").val();
        $searchFilter.GatewayID = $("#summer_filter [name='GatewayID']").val();
        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/summaryreport/ajax_datagrid/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"StartDate","value":$searchFilter.StartDate},{"name":"EndDate","value":$searchFilter.EndDate},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"GatewayID","value":$searchFilter.GatewayID},{"name":"report",value:"customer"});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"StartDate","value":$searchFilter.StartDate},{"name":"Export","value":1},{"name":"EndDate","value":$searchFilter.EndDate},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"GatewayID","value":$searchFilter.GatewayID},{"name":"report",value:"customer"});
            },
             "aoColumns":
            [
                {  "bSortable": true },  //0   name
                {  "bSortable": true },  //1   name
                {  "bSortable": true },  //1   name
                {  "bSortable": true },  //1   name
                {  "bSortable": true },  //1   name
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/summaryreport/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/summaryreport/ajax_datagrid/csv", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });
            }

        });
        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
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
</style>
@stop

