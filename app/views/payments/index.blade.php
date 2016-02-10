@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Payments</a>
        </li>
    </ol>

    <h3>Payments</h3>
    <p style="text-align: right;">
            <a href="javascript:;" id="upload-payments" class="btn upload btn-primary ">
                <i class="entypo-upload"></i>
                Upload
            </a>
    </p>
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
                                    <label for="field-1" class="col-sm-1 control-label">Account Name</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                                    </div>

                                    <label class="col-sm-1 control-label">Invoice No</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="InvoiceNo" class="form-control" id="field-1" placeholder="" value="{{Input::get('InvoiceNo')}}" />

                                    </div>
                                    <label for="field-1" class="col-sm-1 control-label">Status</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('Status', Payment::$status, 'Pending Approval', array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }}
                                    </div>

                                    <label for="field-1" class="col-sm-1 control-label">Action</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('type', Payment::$action, '', array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }}
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-1 control-label">Payment Method</label>
                                    <div class="col-sm-3">
                                        {{ Form::select('paymentmethod', Payment::$method, Input::get('paymentmethod') , array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }}
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
            @if(User::is_admin() or User::is('AccountManager'))
                <p style="text-align: right;">
                    <a href="#" id="add-new-payment" class="btn btn-primary ">
                        <i class="entypo-plus"></i>
                        Add New Payment Request
                    </a>
                </p>
            @endif
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th width="15%">AccountName</th>
                    <th width="10%">Invoice No</th>
                    <th width="10%">Amount</th>
                    <th width="8%">Type</th>
                    <th width="10%">Payment Date</th>
                    <th width="10%">Status</th>
                    <th width="12%">CreatedBy</th>
                    <th width="31%">Action</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <script type="text/javascript">
                var list_fields  = ['PaymentID','AccountName','AccountID','Amount','PaymentType','Currency','PaymentDate','Status','CreatedBy','PaymentProof','InvoiceNo','PaymentMethod','Notes'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {
                        data_table = $("#table-4").dataTable({
                            "bDestroy": true,
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/payments/ajax_datagrid",
                            "fnServerParams": function (aoData) {
                                aoData.push(
                                    {"name": "AccountID", "value": $searchFilter.AccountID},
                                    {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                    {"name": "Status","value": $searchFilter.Status},
                                    {"name": "type","value": $searchFilter.type},
                                    {"name": "paymentmethod","value": $searchFilter.paymentmethod}
                                    );
                                data_table_extra_params.length = 0;
                                data_table_extra_params.push(
                                {"name": "AccountID", "value": $searchFilter.AccountID},
                                {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                {"name": "Status","value": $searchFilter.Status},
                                {"name": "type","value": $searchFilter.type},
                                {"name": "paymentmethod","value": $searchFilter.paymentmethod},
                                {"name":"Export","value":1});

                            },
                            "iDisplayLength": '{{Config::get('app.pageSize')}}',
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[5, 'desc']],
                            "aoColumns": [
                                {
                                    "bSortable": true, //Account
                                    mRender: function (id, type, full) {
                                        return full[1]
                                    }
                                }, //1   CurrencyDescription
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
                                        var action, edit_, show_, recall_;
                                        var Approve_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/approve')}}";
                                        var Reject_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/reject')}}";
                                        var recall_ = "{{ URL::to('payments/{id}/recall')}}";
                                        Approve_Payment = Approve_Payment.replace('{id}', full[0]);
                                        Reject_Payment = Reject_Payment.replace('{id}', full[0]);
                                        recall_  = recall_ .replace( '{id}', full[0]);
                                        action = '<div class = "hiddenRowData" >';
                                        for(var i = 0 ; i< list_fields.length; i++){
                                            action += '<input type = "hidden"  name = "' + list_fields[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                        }
                                        action += '</div>';
                                        action += ' <a data-name = "' + full[0] + '" data-id="' + full[0] + '" class="view-payment btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View </a>'
                                        @if(User::is('BillingAdmin') || User::is_admin() )
                                        if(full[7] != "Approved"){
                                            action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Approve/Reject <span class="caret"></span></button>'
                                            action += '<ul class="dropdown-menu dropdown-green" role="menu"><li><a href="' + Approve_Payment+ '" class="approvepayment" >Approve</a></li><li><a href="' + Reject_Payment + '" class="rejectpayment">Reject</a></li></ul></div>';
                                        }
                                        @endif
                                        {{--@if(User::checkCategoryPermission('Payments','Edit'))
                                            action += ' <a data-name = "' + full[0] + '" data-id="' + full[0] + '" class="edit-payment btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                        @endif
                                        @if(User::checkCategoryPermission('Payments','Recall'))
                                            action += '<a href="'+recall_+'" data-redirect="{{ URL::to('payments')}}"  class="btn recall btn-default btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Recall </a>';
                                        @endif --}}
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
                                        "sUrl": baseurl + "/payments/ajax_datagrid", //baseurl + "/generate_xls.php",
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

                    $('#upload-payments').click(function(ev){
                        ev.preventDefault();
                        $('#upload-modal-payments').modal('show');
                    });


                    $('table tbody').on('click', '.view-payment', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#view-modal-payment').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            $("#view-modal-payment [name='"+list_fields[i]+"']").text(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                        }

                        $('#view-modal-payment h4').html('View Payment');
                        $('#view-modal-payment').modal('show');
                    });

                    $('table tbody').on('click', '.edit-payment', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();

                        var cur_obj = $(this).siblings("div.hiddenRowData");
                        var select = ['AccountID','PaymentMethod','PaymentType'];
                        for(var i = 0 ; i< list_fields.length; i++){
                            if(select.indexOf(list_fields[i])!=-1){
                                $("#add-edit-payment-form [name='"+list_fields[i]+"']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }else if(list_fields[i] == 'PaymentProof'){

                            }else{
                                $("#add-edit-payment-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }
                        }

                        $('#add-edit-modal-payment h4').html('Edit Payment');
                        $('#add-edit-modal-payment').modal('show');
                    });

                    $('body').on('click', '.btn.recall', function (e) {
                        e.preventDefault();

                        response = confirm('Are you sure?');
                        if( typeof $(this).attr("data-redirect")=='undefined'){
                            $(this).attr("data-redirect",'{{ URL::previous() }}')
                        }
                        redirect = $(this).attr("data-redirect");
                        if (response) {
                            $.ajax({
                                url: $(this).attr("href"),
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    $(".btn.delete").button('reset');
                                    if (response.status == 'success') {
                                        toastr.success(response.message, "Success", toastr_opts);
                                        data_table.fnFilter('', 0);
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                },
                                // Form data
                                //data: {},
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }
                        return false;
                    });

                    $("#payment-status-form").submit(function(e){
                       e.preventDefault();
                       if($(this).find("input[name='Notes']").val().trim() == ''){
                            toastr.error("Please Enter a Notes", "Error", toastr_opts);
                            return false;
                       }
                       if($(this).find("input[name='Notes']").val().trim() !== ''){
                            submit_ajax($(this).find("input[name='URL']").val(),$(this).serialize())
                       }
                   });

                    $("#form-upload").submit(function () {
                        // return false;
                        var formData = new FormData($('#form-upload')[0]);
                        show_loading_bar(100);
                        $.ajax({
                            url: baseurl + '/payments/upload',  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            /* xhr: function() {  // Custom XMLHttpRequest
                             var myXhr = $.ajaxSettings.xhr();
                             if(myXhr.upload){ // Check if upload property exists
                             myXhr.upload.addEventListener('progress',function(e){
                             if (e.lengthComputable) {
                             //$('progress').attr({value:e.loaded,max:e.total});
                             }
                             }, false); // For handling the progress of the upload
                             }
                             return myXhr;
                             },*/
                            //Ajax events
                            beforeSend: function(){
                                $('.btn.upload').button('loading');
                            },
                            afterSend: function(){
                                console.log("Afer Send");
                            },
                            success: function (response) {

                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('#upload-modal-codedeck').modal('hide');
                                    reloadJobsDrodown(0);

                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $('.btn.upload').button('reset');
                            },
                            // Form data
                            data: formData,
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                        return false;

                    });

                    $('table tbody').on('click', '.approvepayment , .rejectpayment', function (e) {
                        e.preventDefault();
                        var self = $(this);
                        var text = (self.hasClass("approvepayment")?'Approve':'Reject');
                        if (!confirm('Are you sure you want to '+ text +' the payment?')) {
                            return;
                        }
                        $("#payment-status-form").find("input[name='Notes']").val('');
                        $("#payment-status-form").find("input[name='URL']").val($(this).attr('href'));
                        $("#payment-status").modal('show', {backdrop: 'static'});
                        return false;
                    });

                    $("#add-edit-payment-form [name='AccountID']").change(function(){
                        $("#add-edit-payment-form [name='AccountName']").val( $("#add-edit-payment-form [name='AccountID'] option:selected").text());
                        var url = baseurl + '/payments/getcurrency/'+$("#add-edit-payment-form [name='AccountID'] option:selected").val();
                        $.get( url, function( Currency ) {
                            $("#currency").text('('+Currency+')');
                            $("#add-edit-payment-form [name='Currency']").val(Currency);
                        });
                    });

                    $('#add-new-payment').click(function (ev) {
                        ev.preventDefault();
                        $('#add-edit-payment-form').trigger("reset");
                        $("#add-edit-payment-form [name='AccountID']").select2().select2('val','');
                        $("#add-edit-payment-form [name='Currency']").val('');
                        $("#add-edit-payment-form [name='PaymentMethod']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        $("#add-edit-payment-form [name='PaymentType']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        $("#add-edit-payment-form [name='PaymentID']").val('')
                        $('#add-edit-modal-payment h4').html('Add New Payment');
                        $('#add-edit-modal-payment').modal('show');
                    });

                    $('#add-edit-payment-form').submit(function(e){
                        e.preventDefault();
                        var PaymentID = $("#add-edit-payment-form [name='PaymentID']").val();
                        if( typeof PaymentID != 'undefined' && PaymentID != ''){
                            update_new_url = baseurl + '/payments/'+PaymentID+'/update';
                        }else{
                            update_new_url = baseurl + '/payments/create';
                        }
                        ajax_Add_update(update_new_url);
                    });
                    $("#payment-table-search").submit();
                     $('#PaymentTypeAuto').change(function (ev) {
                         if($(this).val() == 'Payment In'){
                            //$('#InvoiceAuto').addClass('typeahead');
                            var typeahead_opts = {
                                                    name: $(this).attr('name') ? $(this).attr('name') : ($(this).attr('id') ? $(this).attr('id') : 'tt')
                                                };
                            if ($("#InvoiceAuto").attr('data-local'))
                                {
                                    var local = $("#InvoiceAuto").attr('data-local');
                                    local = local.replace(/\s*,\s*/g, ',').split(',');
                                    typeahead_opts['local'] = local;
                                    $('#InvoiceAuto').typeahead(typeahead_opts);
                                }
                         }
                     })

                });

                $("#payment-table-search").submit(function(e) {
                    e.preventDefault();
                    public_vars.$body = $("body");
                    //show_loading_bar(40);
                    $searchFilter.AccountID = $("#payment-table-search select[name='AccountID']").val();
                    $searchFilter.InvoiceNo = $("#payment-table-search [name='InvoiceNo']").val();
                    $searchFilter.Status = $("#payment-table-search select[name='Status']").val();
                    $searchFilter.type = $("#payment-table-search select[name='type']").val();
                    $searchFilter.paymentmethod = $("#payment-table-search select[name='paymentmethod']").val();
                    data_table.fnFilter('', 0);
                    return false;
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
                                    <label for="field-5" class="control-label">Account Name * <span id="currency"></span></label>
                                    {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                                    <input type="hidden" name="AccountName" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Payment Date *</label>
                                    <input type="text" name="PaymentDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Payment Method *</label>
                                    {{ Form::select('PaymentMethod', Payment::$method, '', array("class"=>"selectboxit")) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Action *</label>
                                    {{ Form::select('PaymentType', Payment::$action, '', array("class"=>"selectboxit","id"=>"PaymentTypeAuto")) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Amount *</label>
                                    <input type="text" name="Amount" class="form-control" id="field-5" placeholder="">
                                    <input type="hidden" name="PaymentID" >
                                    <input type="hidden" name="Currency" >
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Invoice</label>
                                    <input type="text" id="InvoiceAuto" data-local="{{$invoice}}" name="InvoiceNo" class="form-control" id="field-5" placeholder="">
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

<div class="modal fade" id="modal-fileformat">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Code Decks File Format</h4>
            </div>



            <div class="modal-body">
                <p>All columns are mandatory and the first line should have the column headings.</p>
                <table class="table responsive">
                    <thead>
                    <tr>
                        <th class="hide_country">Account Name</th>
                        <th>Payment Date</th>
                        <th>Payment Method</th>
                        <th>Action</th>
                        <th>Amount</th>
                        <th>Invoice</th>
                        <th>Note</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="hide_country">WaveTel</td>
                        <td>2013-05-21 14:41:00 </td>
                        <td><span data-original-title="Payment Method" data-content="CASH, PAYPAL, CHEQUE, CREDIT CARD, BANK TRANSFER" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                        <td>Payment In</td>
                        <td>500.00</td>
                        <td>INV-1</td>
                        <td>NOTE</td>
                    </tr>
                    <tr>
                        <td class="hide_country">WaveTel</td>
                        <td>2013-05-21 14:41:00 </td>
                        <td><span data-original-title="Payment Method" data-content="CASH, PAYPAL, CHEQUE, CREDIT CARD, BANK TRANSFER" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                        <td>Payment Out</td>
                        <td>500.00</td>
                        <td>INV-2</td>
                        <td>NOTE</td>
                    </tr>
                    </tbody>
                </table>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="upload-modal-payments" >
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" id="form-upload" method="post" action="{{URL::to('payments/upload')}}"
                  class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Upload Payments</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">File Select</label>
                        <div class="col-sm-5">
                            <input type="file" id="excel" type="file" name="excel" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            <input name="codedeckid" value="{{$id}}" type="hidden" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Note</label>
                        <div class="col-sm-5">
                            <p>Allowed Extension .xls, .xlxs, .csv</p>
                            <p>Please upload the file in given <span style="cursor: pointer" onclick="jQuery('#modal-fileformat').modal('show');jQuery('#modal-fileformat').css('z-index',1999)" class="label label-info">Format</span></p>
                            <p>Sample File <a class="btn btn-success btn-sm btn-icon icon-left" href="{{URL::to('payments/download_sample_excel_file')}}"><i class="entypo-down"></i>Download</a></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="codedeck-update"  class="btn upload btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-upload"></i>
                        Upload
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
