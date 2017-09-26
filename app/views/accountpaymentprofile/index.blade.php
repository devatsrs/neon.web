
            <div class="col-md-6 text-left">
                    <p>
                   Total Payment: <span id="outstanding_amount"></span>
                    </p>

            </div>
            @if($PaymentGatewayID!='' && $PaymentMethod!='SagePayDirectDebit')
                {{--
            <div class="col-md-6 text-right">

                <p>
                     <a  id="add-new-card" data-id="{{$PaymentGatewayID}}" data-name="{{$PaymentMethod}}" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
                </p>
            </div>
            --}}
            @endif
            <div class="clear"></div>
            <table class="table table-bordered datatable" id="ajxtable-4">
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
                var update_new_url,ajax_data_table;
                var postdata;
                var list_fields  = ["Title","Status","isDefault","PaymentMethod","CreatedDate","AccountPaymentProfileID"];

               // jQuery(document).ready(function ($) {
                        ajax_data_table = $("#ajxtable-4").dataTable({
                            "bDestroy": true,
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/paymentprofile/{{$AccountID}}/ajax_datagrid",
                            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
                                {"bSortable": true}, //Title
                                {
                                    "bSortable": true, //Status
                                    mRender: function (id, type, full) {
                                        var status = '';
                                        if(id==1){status='Active'}else{status='Disable'}
                                        return status;
                                    }
                                },
                                {
                                    "bSortable": true, //Default
                                    mRender: function (id, type, full) {
                                        var status = '';
                                        if(id==1){status='Default'}else{status=''}
                                        return status;
                                    }
                                },
                                {"bSortable": true }, //PaymentMethod
                                {"bSortable": true }, //CreatedDate
                                {                       //3  Action
                                    "bSortable": false,
                                    mRender: function (id, type, full) {
                                        var action;

                                        action = '<div class = "hiddenRowData" >';
                                        for(var i = 0 ; i< list_fields.length; i++){
                                             action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                         }
                                         action += '</div>';
                                        if(full[3]!='SagePayDirectDebit') {
                                            action += '<button class="btn paynow btn-success btn-sm " data-loading-text="Loading...">Pay Now </button>';
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

                    $('#add-new-card').click(function (ev) {
                        ev.preventDefault();
                        var pid = $(this).attr('data-id');
                        var gatewayname = $(this).attr('data-name');
                        if(gatewayname=='StripeACH'){
                            $("#add-bankaccount-form")[0].reset();
                            $("#add-bankaccount-form [name='PaymentGatewayID']").val(pid);
                            $('#add-modal-bankaccount').modal('show');
                        }else if(gatewayname=='AuthorizeNet' || gatewayname=='Stripe'){
                            $("#add-credit-card-form")[0].reset();
                            $("#add-credit-card-form [name='PaymentGatewayID']").val(pid);
                            $('#add-modal-card').modal('show');
                        }else{
                            return false;
                        }

                    });
                    var paymentInvoiceIDs = [];
                    var k = 0;
                    $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                        InvoiceID = $(this).val();
                        var tr_obj = $(this).parent().parent().parent().parent();
                        var accoutid = tr_obj.children().find('[name=AccountID]').val();
                        if(accoutid == '{{$AccountID}}'){
                             paymentInvoiceIDs[k++] = InvoiceID;
                        }
                    });

                    $.ajax({
                        url:baseurl+'/accounts/getoutstandingamount/{{$AccountID}}', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        data:'InvoiceIDs='+paymentInvoiceIDs.join(","),
                        success: function(response) {
                            if (response.status == 'success') {
                                $('#outstanding_amount').html(response.outstadingtext);
                            } else {
                                $('#outstanding_amount').html('');
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $('.btn.upload').button('reset');
                        }
                    });

               // });

                function ajax_Add_update(fullurl){
                    var data = new FormData($('#add-credit-card-form')[0]);
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
                            $("#card-update").button('reset');
                            $(".btn").button('reset');
                            $('#add-modal-card').modal('hide');

                            if (response.status == 'success') {
                                $('#add-modal-card').modal('hide');
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
                }
                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });
                $('table tbody').on('click', '.btn.paynow', function (ev) {
                        var InvoiceIDs = [];
                        var i = 0;
                    var cur_obj = $(this).prev("div.hiddenRowData");
                    AccountPaymentProfileID = cur_obj.find("[name=AccountPaymentProfileID]").val();
                    $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                        InvoiceID = $(this).val();
                        var tr_obj = $(this).parent().parent().parent().parent();
                        var accoutid = tr_obj.children().find('[name=AccountID]').val();
                        if(accoutid == '{{$AccountID}}'){
                             InvoiceIDs[i++] = InvoiceID;
                        }
                    });
                    if(AccountPaymentProfileID > 0 && InvoiceIDs.length){
                    $.ajax({
                        url:baseurl+'/accounts/paynow/{{$AccountID}}', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {

                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                if( typeof data_table !=  'undefined'){
                                    data_table.fnFilter('', 0);
                                }
                                $('#pay_now_modal').modal('hide');
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        data: 'AccountPaymentProfileID='+AccountPaymentProfileID+'&InvoiceIDs='+InvoiceIDs.join(",")
                    });
                    }else{
                        toastr.error('please select invoice from one Account', "Error", toastr_opts);
                    }
                    return false;
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





