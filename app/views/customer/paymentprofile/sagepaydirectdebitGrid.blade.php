
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            <?php
            $title = PaymentGateway::getPaymentGatewayNameBYAccount($account->AccountID);
                if($title=='SagePayDirectDebit'){
                    $title='SagePay Direct Debit';
                }
            ?>
            {{$title}} @lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TITLE')
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div class="text-right">
            <a  id="add-new-bankaccount" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_ADD_BANK_ACCOUNT')</a>
            <div class="clear clearfix"><br></div>
        </div>
        <table class="table table-bordered datatable" id="table-4">
            <thead>
            <tr>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_TITLE')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_STATUS')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_DEFAULT')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_PAYMENT_METHOD')</th>
                <th width="20%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_CREATED_DATE')</th>
                <th width="40%">@lang('routes.TABLE_COLUMN_ACTION')</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <script type="text/javascript">
            var update_new_url;
            var postdata;
                var deletePaymentMethodProfile_url = "{{ URL::to('customer/PaymentMethodProfiles/{id}/delete')}}";
            jQuery(document).ready(function ($) {
                data_table = $("#table-4").dataTable({
                    "oLanguage": {
                        "sUrl": baseurl + "/translate/datatable_Label"
                    },
                    "bDestroy": true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": ajax_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[5, 'desc']],
                    "aoColumns": [
                        {
                            "bSortable": true, //Title
                            mRender: function (title, type, full) {
                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);
                                if((verify_obj.VerifyStatus=='undefined' || verify_obj.VerifyStatus=='null' || verify_obj.VerifyStatus!='verified')){
                                    return title;
                                }else{
                                    return title+'(Verified)';
                                }
                            }
                        },
                        {
                            "bSortable": true, //Status
                            mRender: function (status, type, full) {
                                if(status==1)
                                    return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                else
                                    return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                            }
                        },
                        {
                            "bSortable": true, //Default
                            mRender: function (isDefault, type, full) {
                                if(isDefault==1)
                                   return 'Default';
                                else
                                    return ''
                            }
                        },
                        {
                            "bSortable": true //Gateway
                        },
                        {
                            "bSortable": true //Create at
                        },
                        {                       //3  Action
                            "bSortable": false,
                            mRender: function (id, type, full) {
                                var action='';

                                    var Active_Card = "{{ URL::to('customer/PaymentMethodProfiles/{id}/card_status/active')}}";
                                    var DeActive_Card = "{{ URL::to('customer/PaymentMethodProfiles/{id}/card_status/deactive')}}";
                                    var set_default = "{{ URL::to('customer/PaymentMethodProfiles/{id}/set_default')}}";
                                    var Verify = "{{ URL::to('customer/PaymentMethodProfiles/{id}/verify_bankaccount')}}";
                                    Active_Card = Active_Card.replace('{id}', id);
                                    DeActive_Card = DeActive_Card.replace('{id}', id);
                                    set_default = set_default.replace('{id}', id);

                                    var Options = full[6];
                                    var verify_obj = jQuery.parseJSON(Options);

                                    action = '<div class = "hiddenRowData" >';
                                    action += '<input type = "hidden"  name = "cardID" value = "' + id + '" / >';
                                    action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                                    action += '<input type = "hidden"  name = "VerifyStatus" value = "' + verify_obj.VerifyStatus + '" / >';
                                    action += '</div>';

                                    //action += ' <a class="edit-card btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                                    action += ' <a data-id="'+ id +'" class="delete-bankaccount btn delete btn-danger btn-sm"><i class="entypo-trash"></i></a>';

                                    if (full[1]=="1") {
                                        action += ' <button href="' + DeActive_Card + '"  class="btn change_status btn-danger btn-sm disablecard" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">@lang('routes.BUTTON_DEACTIVATE_CAPTION')</button>';
                                    } else {
                                        action += ' <button href="' + Active_Card + '"    class="btn change_status btn-success btn-sm activecard" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">@lang('routes.BUTTON_ACTIVATE_CAPTION')</button>';
                                    }

                                    if(full[2]!=1){
                                        action += ' <a href="' + set_default+ '" class="set-default btn btn-success btn-sm btn-icon icon-left"><i class="entypo-check"></i>@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_SET_DEFAULT') </a> ';
                                    }

                                    if((verify_obj.VerifyStatus=='undefined' || verify_obj.VerifyStatus=='null' || verify_obj.VerifyStatus!='verified')){
                                        action += ' <a href="#" data-id="'+id+'" class="set-verify btn btn-success btn-sm btn-icon icon-left"><i class="entypo-check"></i>@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_VERIFY') </a> ';
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
                                "sUrl": baseurl + "/payments/base_exports", //baseurl + "/generate_xls.php",
                                sButtonClass: "save-collection"
                            }
                        ]
                    },
                    "fnDrawCallback": function () {
                        FnDeleteBankAccountSuccess = function(response){

                            if (response.status == 'success') {
                                ShowToastr("success",response.message);
                                data_table.fnFilter('', 0);
                            }else{
                                ShowToastr("error",response.message);
                            }
                            $('#table-4_processing').css('visibility','hidden');
                        }
                        //onDelete Click
                        FnDeleteBankAccount = function(e){
                            result = confirm("@lang('routes.MESSAGE_ARE_YOU_SURE')");
                            if(result){
                                var id  = $(this).attr("data-id");
                                $('#table-4_processing').css('visibility','visible');
                                var url = deletePaymentMethodProfile_url;
                                url = url.replace('{id}', id);
                                showAjaxScript( url ,"",FnDeleteBankAccountSuccess );
                            }
                            return false;
                        }
                        $(".delete-bankaccount").click(FnDeleteBankAccount); // Delete Bank Account
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }

                });


                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

                $('table tbody').on('click', '.activecard , .disablecard', function (e) {
                    e.preventDefault();
                    var self = $(this);
                    if(self.hasClass("activecard")){
                        var msg = "@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_ACTIVE_CARD_MSG')";
                    }else{
                        var msg = "@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_DISABLE_CARD_MSG')";
                    }
                    if (!confirm(msg)) {
                        return;
                    }
                    $('#table-4_processing').css('visibility','visible');
                    ajax_Add_update(self.attr("href"));
                    return false;
                });

                $('table tbody').on('click', '.set-default', function (e) {
                    e.preventDefault();
                    var self = $(this);
                    if (!confirm("@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_SET_DEFAULT_CARD_MSG')")) {
                        return;
                    }
                    $('#table-4_processing').css('visibility','visible');
                    ajax_Add_update(self.attr("href"));
                    return false;
                });

                $('table tbody').on('click', '.set-verify', function (e) {
                    e.preventDefault();
                    var self = $(this);
                    var cardID = self.attr("data-id");
                    $('#table-4_processing').css('visibility','visible');
                    $.ajax({
                        url: baseurl + '/customer/PaymentMethodProfiles/verify_bankaccount',
                        data: 'cardID=' + cardID +'&Sage=1',
                        dataType: 'json',
                        success: function (response) {
                            $("#bankaccount-update").button('reset');
                            $(".btn").button('reset');
                            $('#table-4_processing').css('visibility','hidden');
                            if (response.status == 'success') {
                                $('#add-modal-bankaccount').modal('hide');
                                toastr.success(response.message, "Success", toastr_opts);
                                $('#add-modal-bankaccount').modal('hide');
                                if( typeof data_table !=  'undefined'){
                                    data_table.fnFilter('', 0);
                                }
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $('.btn.upload').button('reset');
                        },
                        type: 'POST'
                    });
                    return false;
                });

                $('#add-new-bankaccount').click(function (ev) {
                    ev.preventDefault();
                    var pgid = '{{PaymentGateway::getPaymentGatewayIDBYAccount($account->AccountID)}}';
                    $("#add-bankaccount-form")[0].reset();
                    $("#add-bankaccount-form").find('input[name="cardID"]').val('');                    
                    $("#add-bankaccount-form").find('input[name="PaymentGatewayID"]').val(pgid);
                    $("#add-bankaccount-form").find('input[name="AccountID"]').val('{{$account->AccountID}}');
                    $("#add-bankaccount-form").find('input[name="CompanyID"]').val('{{$account->CompanyId}}');
                    $('#add-modal-bankaccount').modal('show');
                });

                $('#add-bankaccount-form').submit(function(e){
                    e.preventDefault();
                    $('#table-4_processing').css('visibility','visible');
                    var cardID = $("#add-bankaccount-form").find('[name="cardID"]').val();
                    if(cardID!=""){
                        update_new_url = baseurl + '/customer/PaymentMethodProfiles/update';
                    }else{
                        update_new_url = baseurl + '/customer/PaymentMethodProfiles/create';
                    }
                    ajax_Add_update(update_new_url);
                });
            });


            function ajax_Add_update(fullurl){
                var data = new FormData($('#add-bankaccount-form')[0]);
                //show_loading_bar(0);

                $.ajax({
                    url:fullurl, //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $("#bankaccount-update").button('reset');
                        $(".btn").button('reset');
                        $('#table-4_processing').css('visibility','hidden');
                        if (response.status == 'success') {
                            $('#add-modal-bankaccount').modal('hide');
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-modal-bankaccount').modal('hide');
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

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-modal-bankaccount" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-bankaccount-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_SAGEPAY_ADD_NEW_BANK_AC_TITLE')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_SAGEPAY_ADD_NEW_BANK_AC_FIELD_TITLE')</label>
                                    <input type="text" name="Title" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_SAGEPAY_ADD_NEW_BANK_AC_FIELD_SAGEPAY_AC_NAME')</label>
                                    <input type="text" name="AccountName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_SAGEPAY_ADD_NEW_BANK_AC_FIELD_AC_NAME')</label>
                                    <input type="text" name="BankAccountName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_SAGEPAY_ADD_NEW_BANK_AC_FIELD_AC_NUMBER')</label>
                                    <input type="text" name="AccountNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                                    <input type="hidden" name="cardID" />
                                    <input type="hidden" name="AccountID" />
                                    <input type="hidden" name="CompanyID" />
                                    <input type="hidden" name="PaymentGatewayID" />
                                </div>
                            </div>
							<div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_SAGEPAY_ADD_NEW_BANK_AC_FIELD_BRANCH_CODE')</label>
                                    <input type="text" name="BranchCode" autocomplete="off" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_SAGEPAY_ADD_NEW_BANK_AC_FIELD_AC_HOLDER_TYPE')</label>
                                    {{ Form::select('AccountHolderType',Payment::$account_holder_sagepay_type,'', array("class"=>"select2 small")) }}
                                </div>
                            </div>                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="bankaccount-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">
                            <i class="entypo-floppy"></i>
                            @lang('routes.BUTTON_SAVE_CAPTION')
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            @lang('routes.BUTTON_CLOSE_CAPTION')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

