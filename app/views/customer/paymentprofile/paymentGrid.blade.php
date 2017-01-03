
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Payment Method Profiles
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div class="text-right">
            <a  id="add-new-card" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New Card</a>
            <div class="clear clearfix"><br></div>
        </div>
        <table class="table table-bordered datatable" id="table-4">
            <thead>
            <tr>
                <th width="10%">Title</th>
                <th width="10%">Status</th>
                <th width="10%">Default</th>
                <th width="10%">Payment Method</th>
                <th width="20%">Created Date</th>
                <th width="40%">Action</th>
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
                    "bDestroy": true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": ajax_url,
                    "iDisplayLength": '{{Config::get('app.pageSize')}}',
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

                                    var Active_Card = "{{ URL::to('customer/PaymentMethodProfiles/{id}/card_status/active')}}";
                                    var DeActive_Card = "{{ URL::to('customer/PaymentMethodProfiles/{id}/card_status/deactive')}}";
                                    var set_default = "{{ URL::to('customer/PaymentMethodProfiles/{id}/set_default')}}";
                                    Active_Card = Active_Card.replace('{id}', id);
                                    DeActive_Card = DeActive_Card.replace('{id}', id);
                                    set_default = set_default.replace('{id}', id);

                                    action = '<div class = "hiddenRowData" >';
                                    action += '<input type = "hidden"  name = "cardID" value = "' + id + '" / >';
                                    action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                                    action += '</div>';

                                    //action += ' <a class="edit-card btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                                    action += ' <a data-id="'+ id +'" class="delete-card btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';

                                    if (full[1]=="1") {
                                        action += ' <button href="' + DeActive_Card + '"  class="btn change_status btn-danger btn-sm disablecard" data-loading-text="Loading...">Deactivate</button>';
                                    } else {
                                        action += ' <button href="' + Active_Card + '"    class="btn change_status btn-success btn-sm activecard" data-loading-text="Loading...">Activate</button>';
                                    }

                                    if(full[2]!=1){
                                        action += ' <a href="' + set_default+ '" class="set-default btn btn-success btn-sm btn-icon icon-left"><i class="entypo-check"></i>Set Default </a> ';
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
                        FnDeleteCardSuccess = function(response){

                            if (response.status == 'success') {
                                ShowToastr("success",response.message);
                                data_table.fnFilter('', 0);
                            }else{
                                ShowToastr("error",response.message);
                            }
                            $("#table-4_processing").hide();
                        }
                        //onDelete Click
                        FnDeleteCard = function(e){
                            result = confirm("Are you Sure?");
                            if(result){
                                var id  = $(this).attr("data-id");
                                $("#table-4_processing").show();
                                var url = deletePaymentMethodProfile_url;
                                url = url.replace('{id}', id);
                                showAjaxScript( url ,"",FnDeleteCardSuccess );
                            }
                            return false;
                        }
                        $(".delete-card").click(FnDeleteCard); // Delete Card
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
                    var text = (self.hasClass("activecard")?'Active':'Disable');
                    if (!confirm('Are you sure you want to '+ text +' the Card?')) {
                        return;
                    }
                    ajax_Add_update(self.attr("href"));
                    return false;
                });

                $('table tbody').on('click', '.set-default', function (e) {
                    e.preventDefault();
                    var self = $(this);
                    if (!confirm('Are you sure you want to set Default this Card?')) {
                        return;
                    }
                    ajax_Add_update(self.attr("href"));
                    return false;
                });

                $('#add-new-card').click(function (ev) {
                    ev.preventDefault();
                    var pgid = '{{PaymentGateway::getPaymentGatewayID()}}';
                    $("#add-credit-card-form")[0].reset();
                    $("#add-credit-card-form").find('input[name="cardID"]').val('');
                    $("#add-credit-card-form [name='ExpirationMonth']").val('').trigger("change");
                    $("#add-credit-card-form [name='ExpirationYear']").val('').trigger("change");
                    $("#add-credit-card-form").find('input[name="PaymentGatewayID"]').val(pgid);
                    $('#add-modal-card').modal('show');
                });

                $('#add-credit-card-form').submit(function(e){
                    e.preventDefault();
                    $("#table-4_processing").show();
                    var cardID = $("#add-credit-card-form").find('[name="cardID"]').val();
                    if(cardID!=""){
                        update_new_url = baseurl + '/customer/PaymentMethodProfiles/update';
                    }else{
                        update_new_url = baseurl + '/paymentprofile/create';
                        if(customer[0].customer==1){
                            update_new_url = baseurl + '/customer/PaymentMethodProfiles/create';
                        }
                    }
                    ajax_Add_update(update_new_url);
                });

                $('table tbody').on('click','.edit-card',function(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                    $("#add-credit-card-form")[0].reset();
                    $("#add-credit-card-form [name='ExpirationMonth']").val('').trigger("change");
                    $("#add-credit-card-form [name='ExpirationYear']").val('').trigger("change");
                    cardID = $(this).prev("div.hiddenRowData").find("input[name='cardID']").val();
                    Title = $(this).prev("div.hiddenRowData").find("input[name='Title']").val();
                    $("#add-credit-card-form").find('[name="cardID"]').val(cardID);
                    $("#add-credit-card-form").find('[name="Title"]').val(Title);
                    $('#add-modal-card').modal('show');
                })

            });


            function ajax_Add_update(fullurl){
                var data = new FormData($('#add-credit-card-form')[0]);
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
                            $('#add-modal-card').modal('hide');
                            if( typeof data_table !=  'undefined'){
                                data_table.fnFilter('', 0);
                            }
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#table-4_processing").hide();
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
                <form id="add-credit-card-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Card</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Title</label>
                                    <input type="text" name="Title" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name on card*</label>
                                    <input type="text" name="NameOnCard" autocomplete="off" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Credit Card Number *</label>
                                    <input type="text" name="CardNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                                    <input type="hidden" name="cardID" />
                                    <input type="hidden" name="AccountID" />
                                    <input type="hidden" name="PaymentGatewayID" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Card Type*</label>
                                    {{ Form::select('CardType',Payment::$credit_card_type,'', array("class"=>"select2 small")) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">CVV Number*</label>
                                    <input type="text" data-mask="decimal" name="CVVNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-md-4">
                                        <label for="field-5" class="control-label">Expiry Date *</label>
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
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="card-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
@stop

