
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Stripe {{ cus_lang('CUST_PANEL_PAGE_PAYOUT_TITLE') }}
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div class="text-right">
            <a id="add-new-payout" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_BUTTON_ADD_NEW') }}</a>
            <div class="clear clearfix"><br></div>
        </div>
        <table class="table table-bordered datatable" id="table-55">
            <thead>
            <tr>
                <th width="10%">{{ cus_lang('CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_TITLE') }}</th>
                <th width="10%">{{ cus_lang('CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_STATUS') }}</th>
                <th width="10%">{{ cus_lang('CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_DEFAULT') }}</th>
                <th width="10%">{{ cus_lang('CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_PAYMENT_METHOD') }}</th>
                <th width="20%">{{ cus_lang('CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TBL_CREATED_DATE') }}</th>
                <th width="40%">{{ cus_lang('TABLE_COLUMN_ACTION') }}</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <script type="text/javascript">
            var update_new_url_payout;
            var postdata_payout;
            var deletePayoutMethod_url = "{{ URL::to('customer/Payout/{id}/delete')}}";

            jQuery(document).ready(function ($) {
                data_table_payout = $("#table-55").dataTable({
                    "oLanguage": {
                        "sUrl": baseurl + "/translate/datatable_Label"
                    },
                    "bDestroy": true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": ajax_payout_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[5, 'desc']],
                    "aoColumns": [
                        {
                            "bSortable": true //Title
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

                                var Active_Cardx = "{{ URL::to('customer/Payout/{id}/payout_status/active')}}";
                                var DeActive_Cardx = "{{ URL::to('customer/Payout/{id}/payout_status/deactive')}}";
                                var set_defaultx = "{{ URL::to('customer/Payout/{id}/set_default')}}";
                                Active_Cardx = Active_Cardx.replace('{id}', id);
                                DeActive_Cardx = DeActive_Cardx.replace('{id}', id);
                                set_defaultx = set_defaultx.replace('{id}', id);

                                action = '<div class = "hiddenRowData" >';
                                action += '<input type = "hidden"  name = "payoutAccountID" value = "' + id + '" / >';
                                action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                                action += '</div>';

                                //action += ' <a class="edit-card btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                                action += ' <a data-id="'+ id +'" class="delete-payout btn delete btn-danger btn-sm"><i class="entypo-trash"></i></a>';

                                if (full[1]=="1") {
                                    action += ' <button href="' + DeActive_Cardx + '"  class="btn  btn-danger btn-sm disablepayout" data-loading-text="{{ cus_lang('BUTTON_LOADING_CAPTION') }}">{{ cus_lang('BUTTON_DEACTIVATE_CAPTION') }}</button>';
                                } else {
                                    action += ' <button href="' + Active_Cardx+ '"    class="btn  btn-success btn-sm activepayout" data-loading-text="{{ cus_lang('BUTTON_LOADING_CAPTION') }}">{{ cus_lang('BUTTON_ACTIVATE_CAPTION') }}</button>';
                                }

                                if(full[2]!=1){
                                    action += ' <a href="' + set_defaultx + '" class="set-default-payout btn btn-success btn-sm btn-icon icon-left"><i class="entypo-check"></i>{{ cus_lang('CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_SET_DEFAULT') }}</a> ';
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
                        FnDeletePayoutSuccess = function(response){

                            if (response.status == 'success') {
                                ShowToastr("success",response.message);
                                data_table_payout.fnFilter('', 0);
                            }else{
                                ShowToastr("error",response.message);
                            }
                            $('#table-55_processing').css('visibility','hidden');
                        };
                        //onDelete Click
                        FnDeletePayout = function(e){
                            result = confirm("{{ cus_lang('MESSAGE_ARE_YOU_SURE') }}");
                            if(result){
                                var id  = $(this).attr("data-id");
                                $('#table-55_processing').css('visibility','visible');
                                var url = deletePayoutMethod_url;
                                url = url.replace('{id}', id);
                                showAjaxScript( url ,"",FnDeletePayoutSuccess );
                            }
                            return false;
                        };
                        $(".delete-payout").click(FnDeletePayout); // Delete Card
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }
                });


                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

                $('table tbody').on('click', '.activepayout , .disablepayout', function (e) {
                    e.preventDefault();
                    var self = $(this);
                    if(self.hasClass("activepayout")){
                        var msg = "{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_TBL_ACTIVE_MSG') }}";
                    }else{
                        var msg = "{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_TBL_DISABLE_MSG') }}";
                    }

                    if (!confirm(msg)) {
                        return;
                    }
                    $('#table-55_processing').css('visibility','visible');
                    ajax_Add_Update_Payout(self.attr("href"));
                    return false;
                });

                $('table tbody').on('click', '.set-default-payout', function (e) {
                    e.preventDefault();
                    var self = $(this);
                    if (!confirm("{{ cus_lang('CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_SET_DEFAULT_CARD_MSG') }}")) {
                        return;
                    }
                    $('#table-55_processing').css('visibility','visible');
                    ajax_Add_Update_Payout(self.attr("href"));
                    return false;
                });

                $('#add-new-payout').click(function (ev) {
                    ev.preventDefault();

                    var pggid = '{{PaymentGateway::getPayoutGatewayIDBYAccount($account->AccountID)}}';
                    $("#add-payout-form")[0].reset();
                    add_payout_type();
                    $("#add-payout-form").find('input[name="payoutAccountID"]').val('');
                    $("#add-payout-form [name='ExpirationMonth']").val('').trigger("change");
                    $("#add-payout-form [name='ExpirationYear']").val('').trigger("change");
                    $("#add-payout-form").find('input[name="PaymentGatewayID"]').val(pggid);
                    $("#add-payout-form").find('input[name="AccountID"]').val('{{$account->AccountID}}');
                    $("#add-payout-form").find('input[name="CompanyID"]').val('{{$account->CompanyId}}');
                    $('#add-modal-payout').modal('show');
                });

                $('#add-payout-form').submit(function(e){
                    e.preventDefault();
                    $('#table-55_processing').css('visibility','visible');
                    var payoutAccountID = $("#add-payout-form").find('[name="payoutAccountID"]').val();
                    if(payoutAccountID!=""){
                        update_new_url_payout = baseurl + '/customer/Payout/update';
                    }else{
                        update_new_url_payout = baseurl + '/customer/Payout/create';
                    }
                    ajax_Add_Update_Payout(update_new_url_payout);
                });

            });


            function ajax_Add_Update_Payout(fullurl){
                var data = new FormData($('#add-payout-form')[0]);
                //show_loading_bar(0);

                $.ajax({
                    url:fullurl, //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $("#payout-update").button('reset');
                        $(".btn").button('reset');
                        if (response.status == 'success') {
                            $('#add-modal-payout').modal('hide');
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-modal-payout').modal('hide');
                            if( typeof data_table_payout !=  'undefined'){
                                data_table_payout.fnFilter('', 0);
                            }
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $('#table-55_processing').css('visibility','hidden');
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
        @include('includes.errors')
        @include('includes.success')

    </div>
</div>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-modal-payout" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-payout-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_TITLE') }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="field-35" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_PAYOUT_FIELD_TITLE')}}</label>
                            <input type="text" name="Title" class="form-control" id="field-35" placeholder="">
                        </div>
                        <div class="form-group">
                            <label for="field-5555" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_DOB') }}</label>
                            <input autocomplete="off" id="5555" type="text" name="DOB" class="form-control datepicker" data-date-format="yyyy-mm-dd" value="" placeholder="YYYY-MM-DD"/>
                        </div>
                        <input type="hidden" name="payoutAccountID" />
                        <input type="hidden" name="AccountID" />
                        <input type="hidden" name="CompanyID" />
                        <input type="hidden" name="PaymentGatewayID" />
                        <div class="form-group">
                            <p class="control-label">Select Payout Method</p>
                            <label for="field-36" class="control-label">
                                <input type="radio" checked name="PayoutType" id="field-36" value="card">
                                {{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_PAYOUT_FIELD_DEBIT_CARD') }}
                            </label>
                            &nbsp; &nbsp;
                            <label for="field-37" class="control-label">
                                <input type="radio" name="PayoutType" id="field-37" value="bank">
                                {{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_PAYOUT_FIELD_BANK_ACCOUNT') }}
                            </label>
                        </div>
                        <div class="payout-card-form">
                            <div class="form-group">
                                <label for="field-55" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_CARD_FIELD_NAME_ON_CARD') }}</label>
                                <input type="text" name="NameOnCard" autocomplete="off" class="form-control" id="field-55" placeholder="">
                            </div>
                            <div class="form-group">
                                <label for="field-335" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_CARD_FIELD_DEBIT_CARD_NUMBER') }}</label>
                                <input type="text" name="CardNumber" autocomplete="off" class="form-control" id="field-335" placeholder="">
                            </div>
                            <div class="form-group">
                                <label for="field-2115" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_CARD_FIELD_CARD_TYPE') }}</label>
                                {{ Form::select('CardType',Payment::$debit_card_type,'', array("class"=>"select2 small")) }}
                            </div>
                            <div class="form-group">
                                <label for="field-125" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_CARD_FIELD_CVV_NUMBER') }}</label>
                                <input type="text" data-mask="decimal" name="CVVNumber" autocomplete="off" class="form-control" id="field-125" placeholder="">
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_CARD_FIELD_EXPIRY_DATE') }}</label>
                                    </div>
                                    <div class="col-md-4">
                                        {{ Form::select('ExpirationMonth', getMonths(), date('m'), array("class"=>"select2 small")) }}
                                    </div>
                                    <div class="col-md-4">
                                        {{ Form::select('ExpirationYear', getYears(), date('Y'), array("class"=>"select2 small")) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="payout-bank-form">
                            <div class="form-group">
                                <label for="field-235" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_BANK_AC_FIELD_AC_HOLDER_NAME') }}</label>
                                <input type="text" name="AccountHolderName" autocomplete="off" class="form-control" id="field-235" placeholder="">
                            </div>
                            <div class="form-group">
                                <label for="field-534" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_BANK_AC_FIELD_AC_NUMBER') }}</label>
                                <input type="text" name="AccountNumber" autocomplete="off" class="form-control" id="field-534" placeholder="">
                            </div>
                            <div class="form-group">
                                <label for="field-526" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_BANK_AC_FIELD_ROUTING_NUMBER') }}</label>
                                <input type="text" name="RoutingNumber" autocomplete="off" class="form-control" id="field-526" placeholder="">
                            </div>
                            <div class="form-group">
                                <label for="field-5213" class="control-label">{{ cus_lang('CUST_PANEL_PAGE_PAYOUT_MODAL_ADD_NEW_BANK_AC_FIELD_AC_HOLDER_TYPE') }}</label>
                                {{ Form::select('AccountHolderType',Payment::$account_holder_type,'', array("class"=>"select2 small")) }}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="payout-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="{{ cus_lang('BUTTON_LOADING_CAPTION') }}">
                            <i class="entypo-floppy"></i>
                            {{ cus_lang('BUTTON_SAVE_CAPTION') }}
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            {{ cus_lang('BUTTON_CLOSE_CAPTION') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function add_payout_type(){
            var payoutMethod = $("input[name='PayoutType']:checked").val();
            if(payoutMethod == "bank"){
                $(".payout-card-form").hide();
                $(".payout-bank-form").show();
            } else {
                $(".payout-card-form").show();
                $(".payout-bank-form").hide();
            }
        }
        $(function () {
            $('.datepicker[name="DOB"]').datepicker({
                endDate: '-5y'
            });
            $("input[name='PayoutType']").on("change", function (e) {
                add_payout_type();
            });
            add_payout_type();
        });
    </script>
@stop

