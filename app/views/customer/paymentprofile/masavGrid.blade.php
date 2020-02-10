
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            <?php
            $title = 'MASAV';
            ?>
            {{$title}} @lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TITLE')
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div class="text-right">
            <a  id="add-new-bank" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_BANK_AC_TITLE')</a>
            <div class="clear clearfix"><br></div>
        </div>
        <table class="table table-bordered datatable" id="table-4">
            <thead>
            <tr>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_ACCOUNT_NO')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_BRANCH_NO')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_BANK_CODE')</th>

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
                            "bSortable": false, //Bank Account
                            mRender: function (status, type, full) {
                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);

                                return verify_obj.BankAccount;
                            }
                        },
                        {
                            "bSortable": false, //BranchNo
                            mRender: function (status, type, full) {
                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);

                                return verify_obj.BranchNo;
                            }
                        },
                        {
                            "bSortable": false, //BankCode
                            mRender: function (status, type, full) {
                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);

                                return verify_obj.BankCode;
                            }
                        },
                        {
                            "bSortable": true, //Status
                            mRender: function (status, type, full) {
                                if(full[1]==1)
                                    return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                else
                                    return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                            }
                        },
                        {
                            "bSortable": true, //Default
                            mRender: function (isDefault, type, full) {
                                if(full[2]==1)
                                    return 'Default';
                                else
                                    return ''
                            }
                        },
                        {
                            "bVisible": false, //Gateway
                            "bSortable": true,
                            mRender: function (isDefault, type, full) {
                                return full[3]
                            }
                        },
                        {
                            "bSortable": true, //Create at
                            mRender: function (isDefault, type, full) {
                                return full[4]
                            }
                        },
                        {                       //3  Action
                            "bSortable": false,
                            mRender: function (id, type, full) {
                                var action='';

                                var Active_Card = "{{ URL::to('customer/PaymentMethodProfiles/{id}/card_status/active')}}";
                                var DeActive_Card = "{{ URL::to('customer/PaymentMethodProfiles/{id}/card_status/deactive')}}";
                                var set_default = "{{ URL::to('customer/PaymentMethodProfiles/{id}/set_default')}}";
                                Active_Card = Active_Card.replace('{id}', full[5]);
                                DeActive_Card = DeActive_Card.replace('{id}', full[5]);
                                set_default = set_default.replace('{id}', full[5]);

                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);

                                action = '<div class = "hiddenRowData" >';
                                action += '<input type = "hidden"  name = "AccountPaymentProfileID" value = "' + full[5] + '" / >';
                                action += '<input type = "hidden"  name = "BankAccount" value = "' + verify_obj.BankAccount + '" / >';
                                action += '<input type = "hidden"  name = "BranchNo" value = "' + verify_obj.BranchNo + '" / >';
                                action += '<input type = "hidden"  name = "BankCode" value = "' + verify_obj.BankCode + '" / >';
                                
                                action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                                action += '</div>';

                                action += ' <a class="edit-bank btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                action += ' <a data-id="'+ full[5] +'" class="delete-card btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-trash"></i>Delete </a>';

                                if (full[1]=="1") {
                                    action += ' <button href="' + DeActive_Card + '" class="btn change_status btn-orange btn-sm btn-icon icon-left disablecard" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')"><i class="entypo-block"></i>@lang('routes.BUTTON_DEACTIVATE_CAPTION')</button>';
                                } else {
                                    action += ' <button href="' + Active_Card + '" class="btn change_status btn-success btn-sm btn-icon icon-left activecard" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')"><i class="entypo-check"></i>@lang('routes.BUTTON_ACTIVATE_CAPTION')</button>';
                                }

                                if(full[2]!=1){
                                    action += ' <a href="' + set_default+ '" class="set-default btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-check"></i>@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_SET_DEFAULT')</a> ';
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
                        FnDeletecardSuccess = function(response){

                            if (response.status == 'success') {
                                ShowToastr("success",response.message);
                                data_table.fnFilter('', 0);
                            }else{
                                ShowToastr("error",response.message);
                            }
                            $('#table-4_processing').css('visibility','hidden');
                        }
                        //onDelete Click
                        FnDeletecard = function(e){
                            result = confirm("@lang('routes.MESSAGE_ARE_YOU_SURE')");
                            if(result){
                                var id  = $(this).attr("data-id");
                                $('#table-4_processing').css('visibility','visible');
                                var url = deletePaymentMethodProfile_url;
                                url = url.replace('{id}', id);
                                showAjaxScript( url ,"",FnDeletecardSuccess );
                            }
                            return false;
                        }
                        $(".delete-card").click(FnDeletecard); // Delete Bank Account
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

                $('#add-new-bank').click(function (ev) {
                    ev.preventDefault();
                    var pgid = '{{PaymentGateway::getPaymentGatewayIDBYAccount($account->AccountID)}}';
                    $("#add-bank-form")[0].reset();
                    $('#add-modal-card .modal-title').text("@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_BANK_AC_TITLE')");
                    $("#add-bank-form").find('[name="AccountPaymentProfileID"]').val('');
                    $("#add-bank-form").find('input[name="PaymentGatewayID"]').val(pgid);
                    $("#add-bank-form").find('input[name="AccountID"]').val('{{$account->AccountID}}');
                    $("#add-bank-form").find('input[name="CompanyID"]').val('{{$account->CompanyId}}');
                    $('#add-modal-card').modal('show');
                });

                $('#add-bank-form').submit(function(e){
                    e.preventDefault();
                    $('#table-4_processing').css('visibility','visible');

                    if($("#add-bank-form").find('[name="AccountPaymentProfileID"]').val() != '') {
                        update_new_url = baseurl + '/customer/PaymentMethodProfiles/update_profile';
                    } else {
                        update_new_url = baseurl + '/customer/PaymentMethodProfiles/create';
                    }

                    ajax_Add_update(update_new_url);
                });

                $('table tbody').on('click','.edit-bank',function(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                    $("#add-bank-form")[0].reset();
                    $('#add-modal-card .modal-title').text("@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_UPDATE_BANK_AC_TITLE')");

                    AccountPaymentProfileID = $(this).prev("div.hiddenRowData").find("input[name='AccountPaymentProfileID']").val();
                    Title                   = $(this).prev("div.hiddenRowData").find("input[name='Title']").val();
                    BankAccount             = $(this).prev("div.hiddenRowData").find("input[name='BankAccount']").val();
                    BranchNo                     = $(this).prev("div.hiddenRowData").find("input[name='BranchNo']").val();
                    BankCode                     = $(this).prev("div.hiddenRowData").find("input[name='BankCode']").val();
                    //AccountHolderName       = $(this).prev("div.hiddenRowData").find("input[name='AccountHolderName']").val();

                    $("#add-bank-form").find('[name="AccountPaymentProfileID"]').val(AccountPaymentProfileID);
                    $("#add-bank-form").find('[name="Title"]').val(Title);
                    $("#add-bank-form").find('[name="BankCode"]').val(BankCode);
                    $("#add-bank-form").find('[name="BankAccount"]').val(BankAccount);
                    $("#add-bank-form").find('[name="BranchNo"]').val(BranchNo);
                    $('#add-modal-card').modal('show');

                })

            });


            function ajax_Add_update(fullurl){
                var data = new FormData($('#add-bank-form')[0]);
                //show_loading_bar(0);
                $.ajax({
                    url:fullurl, //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $("#card-update").button('reset');
                        $(".btn").button('reset');
                        if (response.status == 'success') {
                            $('#add-modal-card').modal('hide');
                            toastr.success(response.message, "Success", toastr_opts);

                            if( typeof data_table !=  'undefined'){
                                data_table.fnFilter('', 0);
                            }
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $('#table-4_processing').css('visibility','hidden');
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
    <div class="modal fade" id="add-modal-card" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-bank-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_BANK_AC_TITLE')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_BANK_CODE')</label>
                                    <input type="text" name="BankCode" autocomplete="off" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_BRANCH_NO')</label>
                                    <input type="text" name="BranchNo" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_ACCOUNT_NO')</label>
                                    <input type="text" name="BankAccount" autocomplete="off" class="form-control" placeholder="">
                                </div>
                            </div>
                            
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="hidden" name="AccountID" />
                                    <input type="hidden" name="CompanyID" />
                                    <input type="hidden" name="PaymentGatewayID" />
                                    <input type="hidden" name="AccountPaymentProfileID" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="card-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">
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

