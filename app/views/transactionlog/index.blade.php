@extends('layout.main') @section('content')

<ol class="breadcrumb bc-3">
    <li><a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a></li>
    <li><a href="{{URL::to('/invoice')}}">Invoices</a></li>
    <li class="active"><strong>View Invoice Log ({{$invoice->InvoiceNumber}})</strong></li>
</ol>

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
    var currency_signs = {{$currency_ids}};
    var list_fields  = ['InvoiceNumber','Transaction','Notes','Amount','Status','created_at','InvoiceID'];
    var list_fields_payments  = ['PaymentID','AccountName','AccountID','Amount','PaymentType','Currency','PaymentDate','Status','CreatedBy','PaymentProof','InvoiceNo','PaymentMethod','Notes','Recall','RecallReasoan','RecallBy','AmountWithSymbol'];
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
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[4, "desc"]],
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
                            {} //6 Date

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
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[2, "desc"]],
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
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[2, "desc"]],
            "aoColumns":
                    [
                        {   //0 Amount
                            "bSortable": true,
                            mRender: function(status, type, full) {
                                return full[3];
                            }
                        },
                        {   //1 PaymentType
                            "bSortable": true,
                            mRender: function(status, type, full) {
                                return full[4];
                            }
                        },
                        {   //2 PaymentDate
                            "bSortable": true,
                            mRender: function(status, type, full) {
                                return full[6];
                            }
                        },
                        {   //4 Status
                            "bSortable": true,
                            mRender: function(status, type, full) {
                                return full[7];
                            }
                        },
                        {   //5CreatedBy
                            "bSortable": true,
                            mRender: function(status, type, full) {
                                return full[8];
                            }
                        },
                        {   //Notes
                            "bSortable": true,
                            mRender: function(status, type, full) {
                                return full[12];
                            }
                        },
                        {   //Action
                            "bSortable": false,
                            mRender: function (id, type, full) {
                                var action, edit_, show_, recall_;
                                var Approve_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/approve')}}";
                                var Reject_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/reject')}}";
                                var recall_ = "{{ URL::to('payments/{id}/recall')}}";
                                Approve_Payment = Approve_Payment.replace('{id}', full[0]);
                                Reject_Payment = Reject_Payment.replace('{id}', full[0]);
                                recall_ = recall_.replace('{id}', full[0]);
                                action = '<div class = "hiddenRowData" >';
                                for (var i = 0; i < list_fields_payments.length; i++) {
                                    action += '<input type = "hidden"  name = "' + list_fields_payments[i] + '" value = "' + (full[i] != null ? full[i] : '') + '" / >';
                                }
                                action += '</div>';
                                action += ' <a data-name = "' + full[0] + '" data-id="' + full[0] + '" title="View" class="view-payment btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
                                <?php if(User::checkCategoryPermission('Payments','Recall')) {?>
                                if (full[13] == 0 && full[7] != 'Rejected') {
                                    action += ' <a href="' + recall_ + '" data-redirect="{{ URL::to('payments')}}"  class="btn recall btn-danger btn-sm btn-icon icon-left"><i class="entypo-ccw"></i>Recall </a>';
                                }
                                <?php } ?>
                                if (full[9] != null) {
                                    action += '<span class="col-md-offset-1"><a class="btn btn-success btn-sm btn-icon icon-left"  href="{{URL::to('payments/download_doc')}}/' + full[0] + '" title="" ><i class="entypo-down"></i>Download</a></span>'
                                }
                                return action;
                            }
                        }

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

        $('table tbody').on('click', '.view-payment', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            $('#view-modal-payment').trigger("reset");
            var cur_obj = $(this).prev("div.hiddenRowData");
            for(var i = 0 ; i< list_fields_payments.length; i++){
                if(list_fields_payments[i] == 'AmountWithSymbol'){
                    $("#view-modal-payment [name='Amount']").text(cur_obj.find("input[name='AmountWithSymbol']").val());
                }else if(list_fields_payments[i] == 'Currency'){
                    var currency_sign_show = currency_signs[cur_obj.find("input[name='" + list_fields_payments[i] + "']").val()];
                    if(currency_sign_show!='Select a Currency'){
                        $("#view-modal-payment [name='" + list_fields_payments[i] + "']").text(currency_sign_show);
                    }else{
                        $("#view-modal-payment [name='" + list_fields_payments[i] + "']").text("Currency Not Found");
                    }
                }else {
                    $("#view-modal-payment [name='" + list_fields_payments[i] + "']").text(cur_obj.find("input[name='" + list_fields_payments[i] + "']").val());
                }
            }

            $('#view-modal-payment h4').html('View Payment');
            $('#view-modal-payment').modal('show');
        });

        $('body').on('click', '.btn.recall,.recall', function (e) {
            e.preventDefault();
            $('#recall-payment-form').trigger("reset");
            if($(this).hasClass('btn')){
                $('#recall-payment-form').attr("action",$(this).attr('href'));
            }else{
                var PaymentIDs = getselectedIDs();
                $('#recall-payment-form [name="PaymentIDs"]').val(PaymentIDs);
            }
            $('#recall-modal-payment').modal('show');
        });

        $('#recall-payment-form').submit(function(e){
            e.preventDefault();
            var formData = new FormData($('#recall-payment-form')[0]);
            $.ajax({
                url: $(this).attr("action"),
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    $(".btn.save").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#recall-modal-payment').modal('hide');
                        data_table_payments.fnFilter('', 0);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                // Form data
                data: formData,
                cache: false,
                contentType: false,
                processData: false
            });
        });

    });

</script>
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="view-modal-payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">View Payment</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Account Name</label>
                                <div name="AccountName"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Currency</label>
                                <div name="Currency"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Invoice</label>
                                <div name="InvoiceNo"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Payment Date</label>
                                <div name="PaymentDate"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Payment Method</label>
                                <div name="PaymentMethod"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Action</label>
                                <div name="PaymentType"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Amount</label>
                                <div name="Amount"></div>
                                <input type="hidden" name="PaymentID" >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Notes</label>
                                <div name="Notes"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Recall Reasoan</label>
                                <div name="RecallReasoan"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label text-left bold">Recall By</label>
                                <div name="RecallBy"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="recall-modal-payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="recall-payment-form" action="{{URL::to('payments/0/recall')}}" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Recall Payment</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group"> <br />
                                <label for="field-1" class="col-sm-12 control-label">Recall Reason</label>
                                <div class="col-sm-12">
                                    <textarea class="form-control message" name="RecallReasoan"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="PaymentIDs" />
                        <button type="submit" id="payment-recall"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Recall </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop