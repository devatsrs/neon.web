
            <div class="col-md-12 text-left">
                    <p>
                   Total Payment: <span id="outstanding_amount"></span>
                    </p>

            </div>


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
                            "sAjaxSource": baseurl + "/customer/PaymentMethodProfiles/ajax_datagrid",
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
                                        if(full[3]!='SagePayDirectDebit'){
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
                                        "sButtonText": "EXCEL",
                                        "sUrl": baseurl + "/payments/base_exports/xlsx", //baseurl + "/generate_xls.php",
                                        sButtonClass: "save-collection btn-sm"
                                    },
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "CSV",
                                        "sUrl": baseurl + "/payments/base_exports/csv", //baseurl + "/generate_csv.php",
                                        sButtonClass: "save-collection btn-sm"
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
                        $("#add-credit-card-form")[0].reset();
                        $('#add-modal-card').modal('show');
                    });

                    $('#add-credit-card-form').submit(function(e){
                        e.preventDefault();
                        update_new_url = baseurl + '/customer/card/create';
                        ajax_Add_update(update_new_url);
                    });
                    var paymentInvoiceIDs = [];
                    var k = 0;
                    $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                        InvoiceID = $(this).val();
                        var tr_obj = $(this).parent().parent().parent().parent();
                        var accoutid = tr_obj.children().find('[name=AccountID]').val();
                        paymentInvoiceIDs[k++] = InvoiceID;
                    });
                    $.ajax({
                        url:baseurl+'/customer/getoutstandingamount', //Server script to process data
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
                        InvoiceIDs[i++] = InvoiceID;
                    });
                    if(AccountPaymentProfileID > 0 && InvoiceIDs.length){
                    $.ajax({
                        url:baseurl+'/customer/invoice/pay_now', //Server script to process data
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
                .export-data{
                    display: none;
                }
            </style>





