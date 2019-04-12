
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            <?php
            $title = PaymentGateway::getPaymentGatewayNameBYAccount($account->AccountID);
            if($title=='Ingenico'){
                $title='Ingenico';
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
            <a  id="add-new-card" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_TITLE')</a>
            <div class="clear clearfix"><br></div>
        </div>
        <table class="table table-bordered datatable" id="table-4">
            <thead>
            <tr>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_TITLE')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_TOKEN')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_HOLDER_NAME')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_EXPIRY_DATE')</th>
                <th width="10%">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_LAST_DIGIT')</th>
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
                            "bSortable": false, //CardToken
                            mRender: function (status, type, full) {
                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);

                                return verify_obj.CardToken;
                            }
                        },
                        {
                            "bSortable": false, //CardHolderName
                            mRender: function (status, type, full) {
                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);

                                return verify_obj.CardHolderName;
                            }
                        },
                        {
                            "bSortable": false, //ExpirationMonth/ExpirationYear
                            mRender: function (status, type, full) {
                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);

                                return verify_obj.ExpirationMonth + ' / ' + verify_obj.ExpirationYear.substring(2,4);
                            }
                        },
                        {
                            "bSortable": false, //LastDigit
                            mRender: function (status, type, full) {
                                var Options = full[6];
                                var verify_obj = jQuery.parseJSON(Options);

                                return verify_obj.LastDigit;
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
                                action += '<input type = "hidden"  name = "CardToken" value = "' + verify_obj.CardToken + '" / >';
                                action += '<input type = "hidden"  name = "CardHolderName" value = "' + verify_obj.CardHolderName + '" / >';
                                action += '<input type = "hidden"  name = "ExpirationMonth" value = "' + verify_obj.ExpirationMonth + '" / >';
                                action += '<input type = "hidden"  name = "ExpirationYear" value = "' + verify_obj.ExpirationYear + '" / >';
                                action += '<input type = "hidden"  name = "LastDigit" value = "' + verify_obj.LastDigit + '" / >';
                                action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                                action += '</div>';

                                action += ' <a class="edit-card btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                action += ' <a data-id="'+ full[5] +'" class="delete-card btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-trash"></i>Delete </a>';

                                if (full[1]=="1") {
                                    action += ' <button href="' + DeActive_Card + '" class="btn change_status btn-orange btn-sm btn-icon icon-left disablecard" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')"><i class="entypo-block"></i>@lang('routes.BUTTON_DEACTIVATE_CAPTION')</button>';
                                } else {
                                    action += ' <button href="' + Active_Card + '" class="btn change_status btn-success btn-sm btn-icon icon-left activecard" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')"><i class="entypo-check"></i>@lang('routes.BUTTON_ACTIVATE_CAPTION')</button>';
                                }

                                if(full[2]!=1){
                                    action += ' <a href="' + set_default+ '" class="set-default btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-check"></i>@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_SET_DEFAULT') </a> ';
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

                $('table tbody').on('click', '.set-verify', function (e) {
                    e.preventDefault();
                    var self = $(this);
                    cardID = self.attr("data-id");
                    $("#verify-card-form")[0].reset();
                    $("#verify-card-form").find('input[name="MicroDeposit1"]').val('');
                    $("#verify-card-form").find('input[name="MicroDeposit2"]').val('');
                    $("#verify-card-form").find('input[name="cardID"]').val(cardID);
                    //cardID = $(this).prev("div.hiddenRowData").find("input[name='cardID']").val();
                    $('#verify-modal-card').modal('show');
                    return false;
                });

                $('#add-new-card').click(function (ev) {
                    ev.preventDefault();
                    var pgid = '{{PaymentGateway::getPaymentGatewayIDBYAccount($account->AccountID)}}';
                    $("#add-card-form")[0].reset();
                    $('#add-modal-card .modal-title').text('Add New Card');
                    $("#add-card-form").find('[name="AccountPaymentProfileID"]').val('');

                    $("#add-card-form").find('input[name="cardID"]').val('');
                    $("#add-card-form").find('input[name="PaymentGatewayID"]').val(pgid);
                    $("#add-card-form").find('input[name="AccountID"]').val('{{$account->AccountID}}');
                    $("#add-card-form").find('input[name="CompanyID"]').val('{{$account->CompanyId}}');
                    $('#add-modal-card').modal('show');
                });

                $('#add-card-form').submit(function(e){
                    e.preventDefault();
                    $('#table-4_processing').css('visibility','visible');

                    if($("#add-card-form").find('[name="AccountPaymentProfileID"]').val() != '') {
                        update_new_url = baseurl + '/customer/PaymentMethodProfiles/update_profile';
                    } else {
                        update_new_url = baseurl + '/customer/PaymentMethodProfiles/create';
                    }

                    ajax_Add_update(update_new_url);
                });

                $('table tbody').on('click','.edit-card',function(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                    $("#add-card-form")[0].reset();
                    $('#add-modal-card .modal-title').text('Update Card');

                    AccountPaymentProfileID = $(this).prev("div.hiddenRowData").find("input[name='AccountPaymentProfileID']").val();
                    Title                   = $(this).prev("div.hiddenRowData").find("input[name='Title']").val();
                    CardToken               = $(this).prev("div.hiddenRowData").find("input[name='CardToken']").val();
                    CardHolderName          = $(this).prev("div.hiddenRowData").find("input[name='CardHolderName']").val();
                    ExpirationMonth         = $(this).prev("div.hiddenRowData").find("input[name='ExpirationMonth']").val();
                    ExpirationYear          = $(this).prev("div.hiddenRowData").find("input[name='ExpirationYear']").val();
                    LastDigit               = $(this).prev("div.hiddenRowData").find("input[name='LastDigit']").val();

                    $("#add-card-form").find('[name="AccountPaymentProfileID"]').val(AccountPaymentProfileID);
                    $("#add-card-form").find('[name="Title"]').val(Title);
                    $("#add-card-form").find('[name="CardToken"]').val(CardToken);
                    $("#add-card-form").find('[name="CardHolderName"]').val(CardHolderName);
                    $("#add-card-form").find('[name="ExpirationMonth"]').val(ExpirationMonth).trigger('change');
                    $("#add-card-form").find('[name="ExpirationYear"]').val(ExpirationYear).trigger('change');
                    $("#add-card-form").find('[name="LastDigit"]').val(LastDigit);
                    $('#add-modal-card').modal('show');
                })

            });


            function ajax_Add_update(fullurl){
                var data = new FormData($('#add-card-form')[0]);
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
                <form id="add-card-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_TITLE')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_TITLE')</label>
                                    <input type="text" name="Title" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_TOKEN')</label>
                                    <input type="text" name="CardToken" autocomplete="off" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_HOLDER_NAME')</label>
                                    <input type="text" name="CardHolderName" autocomplete="off" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_EXPIRY_DATE')</label>
                                    <div class="row">
                                        {{ Form::select('ExpirationMonth', getMonths(), date('m'), array("class"=>"select2 small col-md-6")) }}
                                        {{ Form::select('ExpirationYear', getYears(), date('Y'), array("class"=>"select2 small col-md-6")) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 clear">
                                <div class="form-group">
                                    <label class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_FIELD_LAST_DIGIT')</label>
                                    <input type="text" name="LastDigit" autocomplete="off" class="form-control" placeholder="">
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

