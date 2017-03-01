@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Exchange Rate</strong>
    </li>
</ol>
<h3>Exchange Rate</h3>

@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">

    @if( User::checkCategoryPermission('ExchangeRate','Add'))
        <a href="#" id="add-new-exchangerate" class="btn btn-primary ">
            <i class="entypo-floppy"></i>
            Save
        </a>                
@endif
</p>

<div class="row">
<div class="col-md-12">
<form id="currency-form" method="post" class="form-horizontal form-groups-bordered validate">
<div class="panel panel-primary" data-collapsed="0">
<div class="panel-heading">
                    <div class="panel-title">
                        Add Exchange Rate
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
@foreach ($currencyarray as $currencylist)
    <div class="form-group">
        <label for="field-1" class="col-sm-1 control-label">{{$currencylist->Code}}</label>
            <input type="hidden" name="ExchangeRate[{{$currencylist->CurrencyID}}][CurrencyID]" value="{{$currencylist->CurrencyID}}" @if($currencylist->CurrencyID==$CurrencyId)disabled @endif>
        <div class="col-sm-2">
            <input type="text" name="ExchangeRate[{{$currencylist->CurrencyID}}][Value]" class="form-control" value="{{$currencylist->Amount}}" @if($currencylist->CurrencyID==$CurrencyId)disabled @endif/>
        </div>
        <div class="col-sm-9"></div>
    </div>
@endforeach
</div>
</div>
</form>
</div>
</div>

<h3>Exchange Rate History</h3>

<table id="table-4" class="table table-bordered datatable">
            <thead>
            <tr>
                <th width="33%">Code</th>
                <th width="33%">Exchange Rate</th>
                <th width="34%">EffectiveDate</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
var list_fields  = ['Code','Value','EffectiveDate'];
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/currency_conversion/ajax_datagrid_history",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[2, 'desc']],
             "aoColumns":
            [
                {  "bSortable": true },  //0  CurrencyCode', '', '', '
                {  "bSortable": true },
                {  "bSortable": true }
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": baseurl + "/currency_conversion/base_exports", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
           "fnDrawCallback": function() {

                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
    });
       $('#add-new-exchangerate').click(function(e){
            e.preventDefault();
            var url = baseurl + '/currency_conversion/create';
            var data = $('#currency-form').serialize();
            $.ajax({
                url:url, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $("#update").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        if( typeof data_table !=  'undefined'){
                            data_table.fnFilter('', 0);
                        }
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: data,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });
       });
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
    display:none !important;
}
</style>
@stop