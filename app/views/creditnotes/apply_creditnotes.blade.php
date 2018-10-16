@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="creditnotes_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Invoice No.</label>
                    {{ Form::select('InvoiceNumber', $invoicenumbers, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Invoice")) }}
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
    <ol class="breadcrumb bc-3">
        <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
        <li class="active">
            <a href="{{URL::to('creditnotes')}}">CreditNotes</a>
        </li>
        <li class="active"> <strong>Apply Credit Note</strong> </li>
    </ol>
    <h3>Apply Credit Note</h3>
    @include('includes.errors')
    @include('includes.success')
            <!-- <a href="javascript:;" id="bulk-creditnotes" class="btn upload btn-primary ">
        <i class="entypo-upload"></i>
        Bulk CreditNotes Generate.
    </a>-->
    </p>
    <div class="clearfix margin-bottom "></div>
    <div class="row">
        <div  class="col-md-12">
            <div class="form-group">
                <div class="col-sm-6">
                    <div class="col-sm-6"> Credit Note No.: </div>
                    <div class="clearfix margin-bottom ">{{$CreditNotes->CreditNotesNumber;}}</div>
                    <div class="col-sm-6"> Credit Note Date : </div>
                    <div class="clearfix margin-bottom ">{{date('Y-m-d',strtotime($CreditNotes->IssueDate));}}</div>
                </div>

                <div class="col-sm-6">
                    <div class="col-sm-6"> Client :  </div>
                    <div class="clearfix margin-bottom ">{{$AccountName;}}</div>
                    <div class="col-sm-6"> Available Credits : </div>
                    <div class="clearfix margin-bottom ">{{$CreditNotes->GrandTotal - $CreditNotes->PaidAmount;}}</div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <br>
    <div class="form-group pull-right">
        <button value="Save" name="Save" id="SaveButton" class="btn save btn-primary btn-icon btn-sm icon-left hidden-print" >
            <i class="entypo-floppy"></i>Save</button>
        <a class="btn btn-danger btn-sm btn-icon icon-left" href="{{URL::to('creditnotes')}}">
            <i class="entypo-back"></i>Back</a>
    </div>
    <div class="clear"></div>
    <form name="apply_creditnotes" id="apply_creditnotes" role="form" method="post">
    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th width="15%">Invoice No.</th>
            <th width="20%">Invoice Date</th>
            <th width="15%">Amount</th>
            <th width="10%">Paid Amount</th>
            <th width="10%">Amount To Credit</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    </form>
    <!--<button value="Save" name="Save" id="SaveButton" class="btn save btn-primary btn-icon btn-sm icon-left hidden-print" >
        <i class="entypo-floppy"></i>Save</button>
    <a class="btn btn-danger btn-sm btn-icon icon-left" href="{{URL::to('creditnotes')}}">
        <i class="entypo-back"></i>Back</a>-->

    <script type="text/javascript">
        var $searchFilter 	= 	{};
        var checked			=	'';
        var update_new_url;
        var postdata;
        jQuery(document).ready(function ($) {

            $('#filter-button-toggle').show();

            public_vars.$body = $("body");
            //show_loading_bar(40);
            var base_url_creditnotes 		= 	"{{ URL::to('creditnotes')}}";
            var list_fields  			= 	['InvoiceNumber'];
            //var AccountID = {{$AccountID}};
            $searchFilter.InvoiceNumber 			= 	$("#creditnotes_filter select[name='InvoiceNumber']").val();

            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/creditnotes/{{$AccountID}}/apply_creditnote_datagrid",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
              //  "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[1, 'desc']],
                "fnServerParams": function(aoData) {
                    aoData.push(
                            {"name":"InvoiceNumber","value":$searchFilter.InvoiceNumber}
                           );
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"InvoiceNumber","value":$searchFilter.InvoiceNumber});
                },
                "aoColumns":
                        [

                            {  "bSortable": true,
                                mRender: function (id, type, full) {
                                    var action = '<input type = "hidden"  name = "invoice_id[]" value = "'+full[0]+'" / >';
                                    action +='<input type = "hidden"  name = "AccountID" value = "{{$AccountID}}" / >';
                                    action +='<input type = "hidden"  name = "CompanyID" value = "{{$CompanyID}}" / >';
                                    action +='<input type = "hidden"  name = "CreditNotesID" value = "{{$CreditNotesID}}" / >';
                                    action +='<input type = "hidden"  name = "CreditNoteNumber" value = "{{$CreditNotes->CreditNotesNumber}}" / >';
                                    action +='<input type = "hidden"  name = "invoice_number[]" value = "'+full[1]+'" / >';
                                    action += full[1];
                                    return action;
                                }
                            },  // 1 CreditNotesNumber
                            {  "bSortable": true,
                                mRender: function (id, type, full) {
                                    return full[2];
                                }
                            },  // 2 IssueDate
                            {  "bSortable": false,
                                mRender: function (id, type, full) {
                                    return full[3];
                                }
                            },  // 3 IssueDate
                            {
                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    return full[4];
                                }
                            },
                            {
                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    var action = '<input type="number" name="payment[]" class="form-control"/>';
                                    return action;
                                }
                            }
                        ],
                "oTableTools": {
                    "aButtons": [

                    ]
                },
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }

            });

            $("#creditnotes_filter").submit(function(e){
                e.preventDefault();
                $searchFilter.InvoiceNumber 		= 	$("#creditnotes_filter select[name='InvoiceNumber']").val();
                data_table.fnFilter('', 0);
                //get_total_grand();
                return false;
            });

            $("#SaveButton").click(function(){
                var formData = $("#apply_creditnotes").serialize();
                //alert(JSON.stringify(formData));
                update_new_url = baseurl +'/creditnotes/store_creditnotes';
                submit_ajax(update_new_url,formData)

            });
            $("#add-creditnotes_in_template-form").submit(function(e){
                e.preventDefault();
                var formData = new FormData($('#add-creditnotes_in_template-form')[0]);
                var CreditNotesID = $("#add-creditnotes_in_template-form [name='CreditNotesID']").val()
                if( typeof CreditNotesID != 'undefined' && CreditNotesID != ''){
                    update_new_url = baseurl + '/creditnotes/update_creditnotes_in/'+CreditNotesID;
                }else{
                    update_new_url = baseurl + '/creditnotes/add_creditnotes_in';
                }
                submit_ajax_withfile(update_new_url,formData)
            });


            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $("#selectall").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#table-4 tbody tr').each(function(i, el) {
                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                        if (is_checked) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            $(this).addClass('selected');
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            $(this).removeClass('selected');
                        }
                    }
                });
            });
            $('#table-4 tbody').on('click', 'tr', function() {
                if (checked =='') {
                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        $(this).toggleClass('selected');
                        if ($(this).hasClass('selected')) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                        }
                    }
                }
            });

            $('#convert_invoice').click(function(e) {
                e.preventDefault();
                var self = $(this);
                var text = self.text();

                var CreditNotesIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                    CreditNotesID = $(this).val();
                    if(typeof CreditNotesID != 'undefined' && CreditNotesID != null && CreditNotesID != 'null'){
                        CreditNotesIDs[i++] = CreditNotesID;
                    }
                });
                var all_chceked = 0;
                if($('#selectallbutton').is(':checked')){
                    all_chceked=1;
                }

                if(CreditNotesIDs.length<1)
                {
                    alert("Please select atleast one creditnotes.");
                    return false;
                }
                console.log(CreditNotesIDs);

                if (!confirm('Are you sure you to change status of selected creditnotess ?')) {
                    return;
                }

                $.ajax({
                    url: creditnotes_Status_Url+'_Bulk',
                    type: 'POST',
                    dataType: 'json',
                    data:{
                        'CreditNotesIDs':CreditNotesIDs,
                        "AccountID":$("#creditnotes_filter select[name='AccountID']").val(),
                        "CreditNotesNumber":$("#creditnotes_filter [name='CreditNotesNumber']").val(),
                        "CreditNotesStatus":$("#creditnotes_filter select[name='CreditNotesStatus']").val(),
                        "IssueDateStart":$("#creditnotes_filter [name='IssueDateStart']").val(),
                        "IssueDateEnd":$("#creditnotes_filter [name='IssueDateEnd']").val(),
                        "CurrencyID":$("#creditnotes_filter [name='CurrencyID']").val(),
                        "AllChecked":all_chceked
                    },
                    success: function(response) {
                        $(this).button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }


                });
                return false;
            });

            $(document).on( 'click', '.send_creditnotes', function(e){
                creditnotes_id = $(this).attr('creditnotes');
                $('#send-modal-creditnotes').find(".modal-body").html("Loading Content...");
                var ajaxurl = "/creditnotes/"+creditnotes_id+"/creditnotes_email";
                showAjaxModal(ajaxurl,'send-modal-creditnotes');
                $("#send-creditnotes-form")[0].reset();
                $('#send-modal-creditnotes').modal('show');
            });

            $(document).on( 'click', '.convert_creditnotes', function(e){
                $(this).attr('disabled', 'disabled');
                $(this).button('loading');
                $('.dataTables_processing').css("visibility","visible");
                creditnotes_id = $(this).attr('creditnotes');
                var ajaxurl_convert = base_url_creditnotes+"/"+creditnotes_id+"/convert_creditnotes";

                $.ajax({
                    url: ajaxurl_convert,
                    type: 'POST',
                    dataType: 'json',
                    data:{'eid':creditnotes_id,'convert':1},
                    success: function(response) {
                        $(this).button('reset');
                        $('.dataTables_processing').css("visibility","hidden");
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }


                });
            });



            $('#delete_bulk').click(function(e) {

                e.preventDefault();
                var self = $(this);
                var text = self.text();

                var CreditNotesIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                    CreditNotesID = $(this).val();
                    if(typeof CreditNotesID != 'undefined' && CreditNotesID != null && CreditNotesID != 'null'){
                        CreditNotesIDs[i++] = CreditNotesID;
                    }
                });

                if(CreditNotesIDs.length<1)
                {
                    alert("Please select atleast one creditnotes.");
                    return false;
                }
                console.log(CreditNotesIDs);

                if (!confirm('Are you sure to delete selected creditnotess?')) {
                    return;
                }

                $.ajax({
                    url: delete_url_bulk,
                    type: 'POST',
                    dataType: 'json',
                    data:'del_ids='+CreditNotesIDs,
                    success: function(response) {
                        $(this).button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }


                });
                return false;
            });

            $("#changeSelectedCreditNotes").click(function(ev) {
                var criteria='';
                if($('#selectallbutton').is(':checked')){
                    criteria = JSON.stringify($searchFilter);
                }
                var CreditNotesIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                    //console.log($(this).val());
                    CreditNotesID = $(this).val();
                    if(typeof CreditNotesID != 'undefined' && CreditNotesID != null && CreditNotesID != 'null'){
                        CreditNotesIDs[i++] = CreditNotesID;
                    }

                    if(CreditNotesIDs.length)
                    {
                        $("#selected-creditnotes-status-form").find("input[name='CreditNotesIDs']").val(CreditNotesIDs.join(","));
                        $("#selected-creditnotes-status-form").find("input[name='criteria']").val(criteria);
                        $('#selected-creditnotes-status').modal('show');
                        $("#selected-creditnotes-status-form [name='CreditNotesStatus']").select2().select2('val','');
                        $("#selected-creditnotes-status-form [name='CancelReason']").val('');
                        $('#statuscancel').hide();
                    }
                });
            });

            $("#selected-creditnotes-status-form").submit(function(e){
                e.preventDefault();
                var CreditNotesStatus = $(this).find("select[name='CreditNotesStatus']").val();

                if(CreditNotesStatus != '')
                {
                    formData = $("#selected-creditnotes-status-form").serialize();
                    update_new_url = baseurl +'/creditnotes/creditnotes_change_Status';
                    submit_ajax(update_new_url,formData)

                }else{
                    toastr.error("Please Select CreditNotess Status", "Error", toastr_opts);
                    $(this).find(".cancelbutton]").button("reset");
                    return false;
                }

            });
            $("#selected-creditnotes-status-form [name='CreditNotesStatus']").change(function(e){
                e.preventDefault();
                $('#statuscancel').hide();
                var status = $(this).val();
            });

            $("#creditnotes-status-cancel-form").submit(function(e){
                e.preventDefault();
                if($(this).find("input[name='CancelReason']").val().trim() != ''){
                    submit_ajax(creditnotes_Status_Url,$(this).serialize())
                }
            });
            $('table tbody').on('click', '.changestatus', function (e) {
                e.preventDefault();
                var self = $(this);
                var text = self.text();
                if (!confirm('Are you sure you want to change the creditnotes status to '+ text +'?')) {
                    return;
                }

                $(this).button('loading');
                $.ajax({
                    url: $(this).attr("href"),
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $(this).button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    data:'CreditNotesStatus='+$(this).attr('data-creditnotesstatus')+'&CreditNotesIDs='+$(this).attr('data-creditnotesid')

                });
                return false;
            });

            $('table tbody').on('click', '.send-creditnotes', function (ev) {
                //var cur_obj = $(this).prevAll("div.hiddenRowData");
                var cur_obj 	= 	$(this).parent().parent().parent().parent().find("div.hiddenRowData");
                CreditNotesID 		= 	cur_obj.find("[name=CreditNotesID]").val();
                send_url 		=  	("/creditnotes/{id}/creditnotes_email").replace("{id}",CreditNotesID);
                console.log(send_url)
                showAjaxModal( send_url ,'send-modal-creditnotes');
                $('#send-modal-creditnotes').modal('show');
            });

            $('#send-modal-creditnotes').on('shown.bs.modal', function (event) {
                //setTimeout(function(){ console.log('select2');  $("#send-modal-creditnotes").find(".select22").select2();  }, 700);
            });

            $("#send-creditnotes-form").submit(function(e){
                e.preventDefault();
                var post_data  = $(this).serialize();
                var CreditNotesID = $(this).find("[name=CreditNotesID]").val();
                var _url = baseurl + '/creditnotes/'+CreditNotesID+'/send';
                submit_ajax(_url,post_data);
            });

            $("#bulk-creditnotes-send").click(function(ev) {
                var criteria='';
                if($('#selectallbutton').is(':checked')){
                    criteria = JSON.stringify($searchFilter);
                }
                var CreditNotesIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                    //console.log($(this).val());
                    CreditNotesID = $(this).val();
                    if(typeof CreditNotesID != 'undefined' && CreditNotesID != null && CreditNotesID != 'null'){
                        CreditNotesIDs[i++] = CreditNotesID;
                    }
                });
                console.log(CreditNotesIDs);

                if(CreditNotesIDs.length){
                    if (!confirm('Are you sure you want to send selected CreditNotess?')) {
                        return;
                    }
                    $.ajax({
                        url: baseurl + '/creditnotes/bulk_send_creditnotes_mail',
                        data: 'CreditNotesIDs='+CreditNotesIDs+'&criteria='+criteria,
                        error: function () {
                            toastr.error("error", "Error", toastr_opts);
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        type: 'POST'
                    });

                }

            });


            $("#test").click(function(e){
                e.preventDefault();
                $("#BulkMail-form").find('[name="test"]').val(1);
                $('#TestMail-form').find('[name="EmailAddress"]').val('');
                $('#modal-TestMail').modal({show: true});
            });
            $('.alert').click(function(e){
                e.preventDefault();
                var email = $('#TestMail-form').find('[name="EmailAddress"]').val();
                var accontID = $('.hiddenRowData').find('.rowcheckbox').val();
                if(email==''){
                    toastr.error('Email field should not empty.', "Error", toastr_opts);
                    $(".alert").button('reset');
                    return false;
                }else if(accontID==''){
                    toastr.error('Please select sample creditnotes', "Error", toastr_opts);
                    $(".alert").button('reset');
                    return false;
                }
                $('#BulkMail-form').find('[name="testEmail"]').val(email);
                $('#BulkMail-form').find('[name="SelectedIDs"]').val(accontID);
                $("#BulkMail-form").submit();
                $('#modal-TestMail').modal('hide');

            });

            $('#modal-TestMail').on('hidden.bs.modal', function(event){
                var modal = $(this);
                modal.find('[name="test"]').val(0);
            });


        });

    </script>
    <style>
        #table-4 .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data{
            right: 30px !important;
        }
        #table-5_filter label{
            display:block !important;
        }
        #selectcheckbox{
            padding: 15px 10px;
        }
    </style>
    @stop
    @section('footer_ext')
    @parent
 
    <div class="modal fade in" id="send-modal-creditnotes">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="send-creditnotes-form" method="post" >
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Send Credit Note By Email</h4>
                    </div>
                    <div class="modal-body"> </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary send btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-mail"></i> Send </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop