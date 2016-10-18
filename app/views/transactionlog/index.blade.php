@extends('layout.main') @section('content')

<ol class="breadcrumb bc-3">
    <li><a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a></li>
    <li><a href="{{URL::to('/invoice')}}">Invoices</a></li>
    <li class="active"><strong>View Invoice Log ({{$invoice->InvoiceNumber}})</strong></li>
</ol>

<h3>Invoice No({{$invoice->InvoiceNumber}})</h3>

<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Invoice Log
        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body">

        <form role="form" id="rate-table-search"  method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">

        </form>
        <table class="table table-bordered datatable" id="table-5">
            <thead>
            <tr>
                <th width="15%">Notes</th>
                <th width="20%">Status</th>
                <th width="20%">Date</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Transaction Log
        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body">
        <form role="form" id="rate-table-search"  method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">

        </form>
        <table class="table table-bordered datatable" id="table-4">
            <thead>
            <tr>
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
    </div>
</div>

<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Payments
        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body">
        <table class="table table-bordered datatable" id="table-3">
            <thead>
            <tr>
                <th width="1%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                <th width="9%">Amount</th>
                <th width="8%">Type</th>
                <th width="10%">Payment Date</th>
                <th width="10%">Status</th>
                <th width="10%">CreatedBy</th>
                <th width="10%">Notes</th>
                <th width="15%">Action</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

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

        data_table_payments = $("#table-3").dataTable({
            "bDestroy": true, // Destroy when resubmit form
            "bProcessing": true,
            "bServerSide": true,
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sAjaxSource": baseurl + "/invoice_log/ajax_payments_datagrid/{{$id}}/type",
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
                        {}, //0 Amount
                        {}, //1 PaymentType
                        {}, //2 PaymentDate
                        {},//3 Date
                        {},//4 Status
                        {}, //5CreatedBy
                        {}, //Notes

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
