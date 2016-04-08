@extends('layout.main') @section('content')

<ol class="breadcrumb bc-3">
    <li><a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a></li>
    <li><a href="{{URL::to('/invoice')}}">Invoice Log</a></li>
    <li class="active"><strong>View Invoice Log</strong></li>
</ol>
<h3>View Invoice Log</h3>
<div class="float-right" >
    <a href="{{URL::to('/invoice')}}"  class="btn btn-primary btn-sm btn-icon icon-left" >
        <i class="entypo-floppy"></i>
        Back
    </a>


</div>
<div class="row">
    <div class="col-md-12">
        <form role="form" id="rate-table-search"  method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">

        </form>
    </div>
</div>
<table class="table table-bordered datatable" id="table-5">
    <thead>
        <tr>
            <th width="15%">Invoice Number</th>
            <th width="15%">Notes</th>
            <th width="20%">Status</th>
            <th width="20%">Date</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<h3>View Transaction Log</h3>
<div class="row">
    <div class="col-md-12">
        <form role="form" id="rate-table-search"  method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">

        </form>
    </div>
</div>
<table class="table table-bordered datatable" id="table-4">
    <thead>
        <tr>
            <th width="15%">Invoice Number</th>
            <th width="15%">Transaction</th>
            <th width="20%">Transaction Notes</th>
            <th width="15%">Amount</th>
            <th width="15%">Status</th>
            <th width="20%">Date</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>





<script type="text/javascript">
    var $searchFilter = {};
    var list_fields  = ['InvoiceNumber','Transaction','Notes','Amount','Status','created_at','InvoiceID'];
    var data_table_invoice_log;
    var invoicelogstatus = {{json_encode(InVoiceLog::$log_status)}};
    jQuery(document).ready(function($) {

            data_table = $("#table-4").dataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing": true,
                "bServerSide": true,
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": baseurl + "/invoice_log/ajax_datagrid/{{$id}}/type",
                "fnServerParams": function(aoData) {
                    aoData.push();
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"Export","value":1});
                },
                "iDisplayLength":'{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[5, "desc"]],
                "aoColumns":
                        [
                            {}, //1 Invoice Number
                            {}, //2 Transaction
                            {}, //3 Transaction Notes
                            {}, //4 Amount
                            {
                                mRender: function(status, type, full) {
                                    if (status == 1)
                                        return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                    else
                                        return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                                }
                            }, //5 Status
                            {} //5 Date

                        ],
                        "oTableTools":
                        {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/invoice_log/ajax_datagrid/{{$id}}/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/invoice_log/ajax_datagrid/{{$id}}/csv",
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
            data_table_invoice_log = $("#table-5").dataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing": true,
                "bServerSide": true,
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": baseurl + "/invoice_log/ajax_invoice_datagrid/{{$id}}/type",
                "fnServerParams": function(aoData) {
                    aoData.push();
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"Export","value":1});
                },
                "iDisplayLength":'{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[3, "desc"]],
                "aoColumns":
                        [
                            {}, //1 Invoice Number
                            {}, //4 Amount
                            {
                                mRender: function(status, type, full) {
                                    return invoicelogstatus[status];
                                }
                            }, //5 Status
                            {} //5 Date

                        ],
                        "oTableTools":
                        {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/invoice_log/ajax_invoice_datagrid/{{$id}}/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/invoice_log/ajax_invoice_datagrid/{{$id}}/csv",
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

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });
    });

</script>
@stop
