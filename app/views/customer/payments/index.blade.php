@extends('layout.Customer.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>Payments</a>
        </li>
    </ol>

    <h3>Payments</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="row">
                <div class="col-md-12">
                    <form role="form" id="payment-table-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                        <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Search
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="form-group">

                                    <label class="col-sm-1 control-label">Invoice No</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="InvoiceNo" class="form-control" id="field-1" placeholder="" value="{{Input::get('InvoiceNo')}}" />

                                    </div>
                                    <label for="field-1" class="col-sm-1 control-label">Action</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('type', $action, '', array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }}
                                    </div>

                                    <label for="field-1" class="col-sm-1 control-label">Payment Method</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('paymentmethod', $method, Input::get('paymentmethod') , array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }}
                                    </div>
                                </div>
                                <p style="text-align: right;">
                                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                        <i class="entypo-search"></i>
                                        Search
                                    </button>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="clear"></div>
            <br>
            <p style="text-align: right;">
                <a href="#" id="add-new-payment" class="btn btn-primary ">
                    <i class="entypo-plus"></i>
                    Add New Payment Request
                </a>
            </p>
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th width="14%">Invoice No</th>
                    <th width="14%">Amount</th>
                    <th width="14%">Type</th>
                    <th width="15%">Payment Date</th>
                    <th width="14%">Status</th>
                    <th width="15%">CreatedBy</th>
                    <th width="14%">Action</th>
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
                    $("#payment-table-search").submit(function(e) {
                        e.preventDefault();
                        public_vars.$body = $("body");
                        //show_loading_bar(40);
                        $searchFilter.AccountID = $("#payment-table-search select[name='AccountID']").val();
                        $searchFilter.InvoiceNo = $("#payment-table-search [name='InvoiceNo']").val();
                        $searchFilter.type = $("#payment-table-search select[name='type']").val();
                        $searchFilter.paymentmethod = $("#payment-table-search select[name='paymentmethod']").val();
                        data_table = $("#table-4").dataTable({
                            "bDestroy": true,
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/customer/payments/ajax_datagrid",
                            "fnServerParams": function (aoData) {
                                aoData.push({"name": "AccountID", "value": $searchFilter.AccountID}, {
                                    "name": "InvoiceNo",
                                    "value": $searchFilter.InvoiceNo
                                },{
                                    "name": "type",
                                    "value": $searchFilter.type
                                }, {
                                    "name": "paymentmethod",
                                    "value": $searchFilter.paymentmethod
                                });
                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "AccountID", "value": $searchFilter.AccountID}, {
                                    "name": "InvoiceNo",
                                    "value": $searchFilter.InvoiceNo
                                },{
                                    "name": "type",
                                    "value": $searchFilter.type
                                }, {
                                    "name": "paymentmethod",
                                    "value": $searchFilter.paymentmethod
                                });

                            },
                            "iDisplayLength": '{{Config::get('app.pageSize')}}',
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[5, 'desc']],
                            "aoColumns": [
                                {
                                    "bSortable": true, //Account
                                    mRender: function (id, type, full) {
                                        return full[10]
                                    }
                                }, //1   CurrencyDescription
                                {
                                    "bSortable": true, //Amount
                                    mRender: function (id, type, full) {
                                        var a = parseFloat(Math.round(full[3] * 100) / 100).toFixed(2);
                                        a = a.toString();
                                        return a + ' ' + full[5]
                                    }
                                },
                                {
                                    "bSortable": true, //Type
                                    mRender: function (id, type, full) {
                                        return full[4]
                                    }
                                },

                                {
                                    "bSortable": true, //paymentDate
                                    mRender: function (id, type, full) {
                                        return full[6]
                                    }
                                },
                                {
                                    "bSortable": true, //status
                                    mRender: function (id, type, full) {
                                        return full[7]
                                    }
                                },
                                {
                                    "bSortable": true, //Created by
                                    mRender: function (id, type, full) {
                                        return full[8]
                                    }
                                },
                                {                       //3  Action

                                    "bSortable": false,
                                    mRender: function (id, type, full) {
                                        var action, edit_, show_, delete_;
                                        var Approve_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/approve')}}";
                                        var Reject_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/reject')}}";
                                        Approve_Payment = Approve_Payment.replace('{id}', full[0]);
                                        Reject_Payment = Reject_Payment.replace('{id}', full[0]);
                                        action = '<div class = "hiddenRowData" >';
                                        action += '<input type = "hidden"  name = "PaymentID" value = "' + full[0] + '" / >';
                                        action += '<input type = "hidden"  name = "AccountName" value = "' + full[1] + '" / >';
                                        action += '<input type = "hidden"  name = "AccountID" value = "' + full[2] + '" / >';
                                        action += '<input type = "hidden"  name = "Amount" value = "' + parseFloat(Math.round(full[3] * 100) / 100).toFixed(2) + '" / >';
                                        action += '<input type = "hidden"  name = "PaymentType" value = "' + full[4] + '" / >';
                                        action += '<input type = "hidden"  name = "Currency" value = "' + full[5] + '" / >';
                                        action += '<input type = "hidden"  name = "PaymentDate" value = "' + full[6] + '" / >';
                                        action += '<input type = "hidden"  name = "Status" value = "' + full[7] + '" / >';
                                        action += '<input type = "hidden"  name = "CreatedBy" value = "' + full[8] + '" / >';
                                        action += '<input type = "hidden"  name = "InvoiceNo" value = "' + full[10] + '" / >';
                                        action += '<input type = "hidden"  name = "PaymentMethod" value = "' + full[11] + '" / >';
                                        action += '<input type = "hidden"  name = "Notes" value = "' + full[12] + '" / >';
                                        action += '</div>';
                                        action += ' <a data-name = "' + full[0] + '" data-id="' + full[0] + '" class="edit-payment btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View </a>'
                                        <?php if(User::is('BillingAdmin')){?>
                                        if(full[7] != "Approved"){
                                            action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Approve/Reject <span class="caret"></span></button>'
                                            action += '<ul class="dropdown-menu dropdown-green" role="menu"><li><a href="' + Approve_Payment+ '" class="approvepayment" >Approve</a></li><li><a href="' + Reject_Payment + '" class="rejectpayment">Reject</a></li></ul></div>';
                                        }
                                        <?php } ?>
                                        if(full[9]!= null){
                                            action += '<span class="col-md-offset-1"><a class="btn btn-success btn-sm btn-icon icon-left"  href="{{URL::to('payments/download_doc')}}/'+full[0]+'" title="" ><i class="entypo-down"></i>Download</a></span>'
                                        }
                                        return action;
                                    }
                                }
                            ],
                            "oTableTools": {
                                "aButtons": [
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "Export Data",
                                        "sUrl": baseurl + "/customer/payments/exports", //baseurl + "/generate_xls.php",
                                        sButtonClass: "save-collection"
                                    }
                                ]
                            },
                            "fnDrawCallback": function () {
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

                    $('table tbody').on('click', '.edit-payment', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#view-modal-payment').trigger("reset");

                        Code = $(this).prev("div.hiddenRowData").find("input[name='Code']").val();
                        Description = $(this).prev("div.hiddenRowData").find("input[name='Description']").val();

                        $('#view-modal-payment').trigger("reset");

                        PaymentID = $(this).prev("div.hiddenRowData").find("input[name='PaymentID']").val();
                        AccountName = $(this).prev("div.hiddenRowData").find("input[name='AccountName']").val();
                        AccountID = $(this).prev("div.hiddenRowData").find("input[name='AccountID']").val();
                        Amount = $(this).prev("div.hiddenRowData").find("input[name='Amount']").val();
                        PaymentType = $(this).prev("div.hiddenRowData").find("input[name='PaymentType']").val();
                        Currency = $(this).prev("div.hiddenRowData").find("input[name='Currency']").val();
                        PaymentDate = $(this).prev("div.hiddenRowData").find("input[name='PaymentDate']").val();
                        Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
                        CreatedBy = $(this).prev("div.hiddenRowData").find("input[name='CreatedBy']").val();
                        InvoiceNo = $(this).prev("div.hiddenRowData").find("input[name='InvoiceNo']").val();
                        PaymentMethod = $(this).prev("div.hiddenRowData").find("input[name='PaymentMethod']").val();
                        Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
                        Notes = $(this).prev("div.hiddenRowData").find("input[name='Notes']").val();


                        $("#view-modal-payment [name='PaymentID']").text(PaymentID);
                        $("#view-modal-payment [name='AccountName']").text(AccountName);
                        $("#view-modal-payment [name='InvoiceNo']").text(InvoiceNo);
                        $("#view-modal-payment [name='PaymentDate']").text(PaymentDate);
                        $("#view-modal-payment [name='PaymentMethod']").text(PaymentMethod);
                        $("#view-modal-payment [name='PaymentType']").text(PaymentType);
                        $("#view-modal-payment [name='Currency']").text(Currency);
                        $("#view-modal-payment [name='Amount']").text(Amount);
                        $("#view-modal-payment [name='Notes']").text(Notes);

                        $('#view-modal-payment h4').html('View Payment');
                        $('#view-modal-payment').modal('show');
                    });

                    $('#add-new-payment').click(function (ev) {
                        ev.preventDefault();
                        $('#add-edit-payment-form').trigger("reset");
                        $("#add-edit-payment-form [name='PaymentMethod']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        $("#add-edit-payment-form [name='PaymentType']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        $("#add-edit-payment-form [name='PaymentID']").val('')
                        $('#add-edit-modal-payment h4').html('Add New Payment');
                        $('#add-edit-modal-payment').modal('show');
                    });

                    $('#add-edit-payment-form').submit(function(e){
                        e.preventDefault();
                        var PaymentID = $("#add-edit-payment-form [name='PaymentID']").val()
                        if( typeof PaymentID != 'undefined' && PaymentID != ''){
                            update_new_url = baseurl + '/customer/payments/update/'+PaymentID;
                        }else{
                            update_new_url = baseurl + '/customer/payments/create';
                        }
                        ajax_Add_update(update_new_url);
                    });
                    $("#payment-table-search").submit();

                });

                function ajax_Add_update(fullurl){
                    var data = new FormData($('#add-edit-payment-form')[0]);
                    //show_loading_bar(0);

                    $.ajax({
                        url:fullurl, //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function(){
                            /*$('.btn.upload').button('loading');
                             show_loading_bar({
                             pct: 50,
                             delay: 5
                             });*/

                        },
                        afterSend: function(){
                            console.log("Afer Send");
                        },

                        success: function(response) {
                            $("#payment-update").button('reset');
                            $(".btn").button('reset');
                            $('#modal-payment').modal('hide');

                            if (response.status == 'success') {
                                $('#add-edit-modal-payment').modal('hide');
                                toastr.success(response.message, "Success", toastr_opts);
                                if( typeof data_table !=  'undefined'){
                                    data_table.fnFilter('', 0);
                                }
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $('.btn.upload').button('reset');
                        },
                        data: data,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    var $label = $('#add-edit-payment-form [for="PaymentProof"]');
                    $label.parents('.form-group').find('a,span').remove();
                    $label.parents('.form-group').find('div').append('<input id="PaymentProof" name="PaymentProof" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class=\'glyphicon glyphicon-circle-arrow-up\'></i>&nbsp;   Browse" />');
                    var $this = $('#PaymentProof');
                    var label = attrDefault($this, 'label', 'Browse');
                    $this.bootstrapFileInput(label);
                }

                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
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

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
@section('footer_ext')
    @parent

    <div class="modal fade" id="add-edit-modal-payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-edit-payment-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New payment Request</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Payment Date *</label>
                                    <input type="text" name="PaymentDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Payment Method *</label>
                                    {{ Form::select('PaymentMethod', $method, '', array("class"=>"selectboxit")) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Action *</label>
                                    {{ Form::select('PaymentType', $action, '', array("class"=>"selectboxit")) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Amount *</label>
                                    <input type="text" name="Amount" class="form-control" id="field-5" placeholder="">
                                    <input type="hidden" name="PaymentID" >
                                    <input type="hidden" name="Currency" value="{{$currency}}" >
                                    <input type="hidden" name="AccountID" value="{{$AccountID}}"
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Invoice</label>
                                    <input type="text" name="InvoiceNo" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Notes</label>
                                    <textarea name="Notes" class="form-control" id="field-5" placeholder=""></textarea>
                                    <input type="hidden" name="PaymentID" >
                                </div>
                            </div>
                            @if(User::is_admin() OR User::is('AccountManager'))
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="PaymentProof" class="col-sm-2 control-label">Upload (.pdf, .jpg, .png, .gif)</label>
                                        <div class="col-sm-6">
                                            <input id="PaymentProof" name="PaymentProof" type="file" class="form-control file2 inline btn btn-primary" data-label="
                            <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="payment-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                                <label for="field-5" class="control-label">Account Name</label>
                                <div class="col-sm-12" name="AccountName"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Currency</label>
                                <div class="col-sm-12" name="Currency"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Invoice</label>
                                <div class="col-sm-12" name="InvoiceNo"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Payment Date</label>
                                <div class="col-sm-12" name="PaymentDate"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Payment Method</label>
                                <div class="col-sm-12" name="PaymentMethod"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Action</label>
                                <div class="col-sm-12" name="PaymentType"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Amount</label>
                                <div class="col-sm-12" name="Amount"></div>
                                <input type="hidden" name="PaymentID" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Notes</label>
                                <div class="col-sm-12" name="Notes"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade in" id="payment-status">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payment-status-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Payment Notes</h4>
                </div>
                <div class="modal-body">
                <div id="text-boxes" class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Notes</label>
                            <input type="text" name="Notes" class="form-control"  value="" />
                        </div>
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                     <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        <input type="hidden" name="URL" value="">
                        Save
                     </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
