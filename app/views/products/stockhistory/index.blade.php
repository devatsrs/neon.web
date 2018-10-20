@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="stockhistoty_filter" method="get"  class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    {{--{{ Form::text('Name', '', array("class"=>"form-control")) }}--}}
                    {{Form::select('Name',$products,'',array("class"=>"form-control select2 small"))}}
                </div>

                <div class="form-group">
                    <label for="field-5" class="control-label">Item Type </label>
                    {{Form::select('ItemTypeID',$itemtypes,'',array("class"=>"form-control select2 small"))}}
                </div>

                <div class="form-group">
                    <label for="field-1" class="control-label">Invoice Number</label>
                    {{ Form::text('InvoiceNumber', '', array("class"=>"form-control")) }}
                </div>

                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Stock History</a>
        </li>
    </ol>

    <h3>Stock History</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
                <div class="row">
                    <div  class="col-md-12">
                        @if( User::is_admin() || User::is('BillingAdmin'))
                            <a href="{{ URL::to('/products')  }}" class="btn btn-danger btn-sm btn-icon icon-left pull-right"> <i class="entypo-cancel"></i> Close </a>
                        @endif

                    </div>
                    <div class="clear"></div>
                </div>
            <br>
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th width="15%">Item Type</th>
                    <th width="12%">Name</th>
                    <th width="5%">Stock</th>
                    <th width="5%">Quantity</th>
                    <th width="10%">Invoice Number</th>
                    <th width="35%">Reason</th>
                    <th width="25%">Created At</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var checked = '';
                var list_fields  = ['title','Name','Stock','Quantity','InvoiceNumber','Reason','created_at'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {

                    $('#filter-button-toggle').show();

                    public_vars.$body = $("body");
                    $searchFilter.Name = $("#stockhistoty_filter [name='Name']").val();
                    $searchFilter.ItemTypeID = $("#stockhistoty_filter select[name='ItemTypeID']").val();
                    $searchFilter.InvoiceNumber = $("#stockhistoty_filter [name='InvoiceNumber']").val();

                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/products/stockhistory/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push({ "name": "Name", "value": $searchFilter.Name },
                                        { "name": "ItemTypeID", "value": $searchFilter.ItemTypeID },
                                        { "name": "InvoiceNumber", "value": $searchFilter.InvoiceNumber });

                            data_table_extra_params.length = 0;
                            data_table_extra_params.push({ "name": "Name", "value": $searchFilter.Name },
                                                        { "name": "ItemTypeID", "value": $searchFilter.ItemTypeID },
                                                        { "name": "InvoiceNumber", "value": $searchFilter.InvoiceNumber },
                                                        { "name": "Export", "value": 1});

                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[6, 'desc']],
                        "aoColumns": [
                            {  "bSortable": true },  // 1 Item Type
                            {  "bSortable": true },  // 2 Pname
                            {  "bSortable": true },  // 3 Stock
                            {  "bSortable": true },  // 4 Quantity
                            {  "bSortable": true },  // 5 InvoiceNo
                            {  "bSortable": false }, // 6 Reason
                            {  "bSortable": true },  // 7 Created_at

                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/products/stockhistory/ajax_datagrid/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/products/stockhistory/ajax_datagrid/csv",
                                    sButtonClass: "save-collection btn-sm"
                                }
                            ]
                        },
                        "fnDrawCallback": function () {
                            $(".dataTables_wrapper select").select2({
                                minimumResultsForSearch: -1
                            });
                            $(".dropdown").removeClass("hidden");

                        }

                    });



                    $("#stockhistoty_filter").submit(function(e){
                        e.preventDefault();
                        $searchFilter.Name = $("#stockhistoty_filter [name='Name']").val();
                        $searchFilter.ItemTypeID = $("#stockhistoty_filter [name='ItemTypeID']").val();
                        $searchFilter.InvoiceNumber = $("#stockhistoty_filter [name='InvoiceNumber']").val();
                         data_table.fnFilter('', 0);
                        return false;
                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                });

                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

            </script>


            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
