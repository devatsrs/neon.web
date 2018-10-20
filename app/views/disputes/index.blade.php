@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form role="form" id="dispute-table-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Account</label>
                    {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                </div>
                <div class="form-group">
                    <label class="control-label small_label" for="DisputeDate_StartDate">Date From</label>
                    <input autocomplete="off" type="text" name="DisputeDate_StartDate" id="DisputeDate_StartDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d',strtotime("-1 week"))}}" data-enddate="{{date('Y-m-d')}}" />
                </div>
                <div class="form-group">
                    <label  class="control-label" for="DisputeDate_EndDate">Date To</label>
                    <input autocomplete="off" type="text" name="DisputeDate_EndDate" id="DisputeDate_EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}" />
                </div>
                <div class="form-group">
                    <label class="control-label">Invoice Type</label>
                    {{Form::select('InvoiceType',Invoice::$invoice_type,'',array("class"=>"select2 small"))}}
                </div>
                <div class="form-group">
                    <label class="control-label">Invoice No</label>
                    <input type="text" name="InvoiceNo" class="form-control" id="field-1" placeholder="" value="{{Input::get('InvoiceNo')}}" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label small_label">Status</label>
                    {{ Form::select('Status', Dispute::$Status, Dispute::PENDING, array("class"=>"select2 small","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }}
                </div>
                <div class="form-group">
                    <label class="control-label">Account Tag</label>
                    <input class="form-control tags" name="tag" type="text" >
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
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <a href="javascript:void(0)">Disputes</a> </li>
</ol>
<h3>Disputes</h3>
<div class="tab-content">
  <div class="tab-pane active" id="customer_rate_tab_content">
    <div class="clear"></div>
      <div class="row">
          <div class="col-md-12 action-buttons">
              @if(User::checkCategoryPermission('Disputes','Add'))
                  <!-- <p class="text-right"><a href="#" id="add-new-dispute" class="btn btn-primary "><i class="entypo-plus"></i>Add New</a></p> -->
                  <div class="input-group-btn">
                      <button href="#" id="add-new-dispute" class="btn btn-primary tooltip-primary pull-right" data-original-title="Add New" title="" data-placement="top" data-toggle="tooltip" style="margin-right:5px;" > Add New</button>
                  </div>
              @endif

                  <div class="input-group-btn">
                      @if( User::checkCategoryPermission('Disputes','Email'))
                          <button type="button" class="btn btn-primary dropdown-toggle pull-right" data-toggle="dropdown"
                                  aria-expanded="false">Action <span class="caret"></span></button>
                          <ul class="dropdown-menu dropdown-menu-right" role="menu"
                              style="background-color: #000; border-color: #000; margin-top:0px;">
                             {{-- @if(User::checkCategoryPermission('Disputes','Email'))
                                  --}}{{--<li> <a class="pay_now create" id="bulk_email" href="javascript:;"> Bulk Email </a> </li>--}}{{--
                              @endif--}}
                              @if(User::checkCategoryPermission('Disputes','Send'))
                                  <li> <a class="generate_rate create" id="bulk-dispute-send" href="javascript:;"
                                          style="width:100%"> Send</a> </li>
                              @endif

                          </ul>
                      @endif
                  </div>

              </div>
          </div>

      <div class="clear"><br>
      </div>
  </div>
</div>


     <table class="table table-bordered datatable" id="table-4">
          <thead>
            <tr>
                <th width="8%"> <div class="pull-left">
                        <input type="checkbox" id="selectall" name="checkbox[]" class=""/>
                    </div>
                </th>
              <th width="10%">Account Name</th>
              <th width="8%">Invoice No</th>
              <th width="8%">Dispute Total</th>
              <th width="5%">Status</th>
              <th width="8%">Created Date</th>
              <th width="8%">Created By</th>
              <th width="15%">Notes</th>
              <th width="16%">Action</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>

     <script type="text/javascript">
	
	 var currency_signs = {{$currency_ids}};
     var checked = '';
     var $searchFilter = {};
     var update_new_url;
     var postdata;
     var toFixed = '{{get_round_decimal_places()}}';
     var editor_options 	  =  		{};

     jQuery(document).ready(function ($) {

         $('#filter-button-toggle').show();

         var dispute_status = {{json_encode(Dispute::$Status);}};
         var list_fields  = ['InvoiceType','AccountName','InvoiceNo','DisputeAmount','Status','created_at', 'CreatedBy','ShortNotes','DisputeID','Attachment','AccountID','Notes','Ref'];

         $searchFilter.Status = $("#dispute-table-search select[name='Status']").val();
         $searchFilter.DisputeDate_StartDate = $("#dispute-table-search input[name='DisputeDate_StartDate']").val();
         $searchFilter.DisputeDate_EndDate   = $("#dispute-table-search input[name='DisputeDate_EndDate']").val();
         $searchFilter.InvoiceType   = $("#dispute-table-search select[name='InvoiceType']").val();
         $searchFilter.AccountID   = $("#dispute-table-search select[name='AccountID']").val();
         $searchFilter.InvoiceNo   = $("#dispute-table-search input[name='InvoiceNo']").val();
         $searchFilter.Status   = $("#dispute-table-search select[name='Status']").val();
         $searchFilter.tag   = $("#dispute-table-search select[name='tag']").val();

         data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/disputes/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push(
                                    {"name": "AccountID", "value": $searchFilter.AccountID},
                                    {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                    {"name": "InvoiceType","value": $searchFilter.InvoiceType},
                                    {"name": "Status","value": $searchFilter.Status},
									{"name": "DisputeDate_StartDate","value": $searchFilter.DisputeDate_StartDate},
									{"name": "DisputeDate_EndDate","value": $searchFilter.DisputeDate_EndDate},
									{"name": "tag","value": $searchFilter.tag}

                            );
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push(
                                    {"name": "AccountID", "value": $searchFilter.AccountID},
                                    {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                    {"name": "InvoiceType","value": $searchFilter.InvoiceType},
                                    {"name": "Status","value": $searchFilter.Status},
									{"name": "DisputeDate_StartDate","value": $searchFilter.DisputeDate_StartDate},
									{"name": "DisputeDate_EndDate","value": $searchFilter.DisputeDate_EndDate},
									{"name": "tag","value": $searchFilter.tag},
                                    {"name":"Export","value":1});

                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[5, 'desc']],
                        "aoColumns": [
                            {
                                "bSortable": false, //InvoiceType
                                mRender: function ( id, type, full ) {
                                    var action, action = '<div class = "hiddenRowData" >';
                                    if (id == '{{Invoice::INVOICE_IN}}'){
                                        invoiceType = ' <button class=" btn btn-primary pull-right" title="Invoice Received"><i class="entypo-right-bold"></i>RCV</a>';
                                    }else{
                                        invoiceType = ' <button class=" btn btn-primary pull-right" title="Invoice Sent"><i class="entypo-left-bold"></i>SNT</a>';

                                    }
                                    //if (full[0] != '{{Invoice::INVOICE_IN}}') {
                                        action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="' + full[8] + '" name="DisputeID[]"></div>';
                                    //}
                                    action += invoiceType;
                                    return action;
                                }
                            },{
                                "bSortable": true, //Account
                                mRender: function (id, type, full) {
                                    var output, account_url;
                                    output = '<a href="{url}" target="_blank" >{account_name}';
                                    output += '</a>';
                                    account_url = baseurl + "/accounts/" + full[10] + "/show";
                                    output = output.replace("{url}", account_url);
                                    output = output.replace("{account_name}", id);
                                    return output;
                                }
                            },
                            {
                                "bSortable": true, //InvoiceNo
                            },
                            {
                                "bSortable": true, //DisputeAmount
                                mRender: function (id, type, full) {
                                    return parseFloat(id).toFixed(toFixed);
                                }
                            },
                            {
                                "bSortable": true, //status
                            },
                            {
                                "bSortable": true, //created_at
                            },
							{
                                "bSortable": true, //CreatedBy
                            },
                            {
                                "bSortable": true, //Notes
                            },
                            {                       //Action

                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    var action, edit_, show_, recall_;

                                    var delete_ = "{{ URL::to('disputes/{id}/delete')}}";
                                    delete_  = delete_ .replace( '{id}', id );
                                    var dispute_status_url = "{{ URL::to('disputes/change_status')}}";

                                    var downloads_ = "{{ URL::to('disputes/{id}/download_attachment')}}";
                                    downloads_  = downloads_ .replace( '{id}', id );

                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '</div>';

                                    action += '<div class="btn-group">';
                                    action += '<a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">Action<span class="caret"></span></a>';
                                    action += '<ul class="dropdown-menu multi-level dropdown-menu-left" role="menu" aria-labelledby="dropdownMenu">';
                                    if('{{User::checkCategoryPermission('Disputes','Edit')}}' ) {
                                        action += '<li><a data-id="' + id + '" class="edit-dispute icon-left"><i class="entypo-pencil"></i>Edit </a></li>';
                                    }
                                    if('{{User::checkCategoryPermission('Disputes','Send')}}' ) {
                                        action += '<li><a data-id="' + id + '" class="send-disputes icon-left"><i class="entypo-mail"></i>Send </a></li>';
                                    }
                                    if('{{User::checkCategoryPermission('Disputes','Delete')}}' ) {
                                        action += '<li><a href="' + delete_ + '" data-redirect="{{ URL::to('disputes')}}" title="Delete"  class="dispute_delete" data-original-title="Delete" title="" data-placement="top"><i class="entypo-trash"></i>Delete </a></li>';
                                    }
                                    action += '</ul>';
                                    action += '</div>';


                                    if('{{User::checkCategoryPermission('Disputes','ChangeStatus')}}') {
                                        action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Change Status <span class="caret"></span></button>'
                                        action += '<ul class="dropdown-menu dropdown-green" role="menu">';
                                        $.each(dispute_status, function( index, value ) {
                                            if(index!=''){
                                                action +='<li><a data-dispute_status="' + index+ '" data-disputeid="' + id+ '"  href="' + dispute_status_url + '" class="changestatus" >'+value+'</a></li>';
                                            }

                                        });
                                        action += '</ul>' +
                                                '</div>';
                                    }

                                    if(full[9]!= ""){
                                        action += '<div class="btn-group"><span class="col-md-offset-1"><a class="btn btn-success btn-sm "  href="'+downloads_+'" title="" ><i class="entypo-down"></i></a></span></div>'
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
                                    "sUrl": baseurl + "/disputes/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                                    sButtonClass: "save-collection"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/disputes/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                                    sButtonClass: "save-collection"
                                }
                            ]
                        },
                        "fnDrawCallback": function () {
                            $(".dataTables_wrapper select").select2({
                                minimumResultsForSearch: -1
                            });

                            $('#table-4 tbody tr').each(function (i, el) {
                                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                    if (checked != '') {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                        $('#selectallbutton').prop("checked", true);
                                    } else {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        ;
                                        $(this).removeClass('selected');
                                    }
                                }
                            });

                            $('#selectallbutton').click(function (ev) {
                                if ($(this).is(':checked')) {
                                    checked = 'checked=checked disabled';
                                    $("#selectall").prop("checked", true).prop('disabled', true);
                                    if (!$('#changeSelectedInvoice').hasClass('hidden')) {
                                        $('#table-4 tbody tr').each(function (i, el) {
                                            if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                                $(this).addClass('selected');
                                            }
                                        });
                                    }
                                } else {
                                    checked = '';
                                    $("#selectall").prop("checked", false).prop('disabled', false);
                                    if (!$('#changeSelectedInvoice').hasClass('hidden')) {
                                        $('#table-4 tbody tr').each(function (i, el) {
                                            if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                                $(this).removeClass('selected');
                                            }
                                        });
                                    }
                                }
                            });

                        }

                    });

         $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
                        // Replace Checboxes
                        $(".pagination a").click(function (ev) {
                            replaceCheckboxes();
                        });

                     $("#selectall").click(function (ev) {
                         var is_checked = $(this).is(':checked');
                         $('#table-4 tbody tr').each(function (i, el) {
                             if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
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
                     $('#table-4 tbody').on('click', 'tr', function () {
                         if (checked == '') {
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

                    $('#upload-payments').click(function(ev){
                        ev.preventDefault();
                        $('#upload-modal-payments').modal('show');
                    });


                         $('table tbody').on('click', '.changestatus', function (e) {
                             e.preventDefault();
                             var status_value = $(this).attr("data-dispute_status");
                             var dispute_id = $(this).attr("data-disputeid");
                             var status_text = $(this).text();

                             if (!confirm('Are you sure you want to change dispute status to '+ status_text +'?')) {
                                 return;
                             }
                             $("#dispute-status-form").find("textarea[name='Notes']").val('');
                             $("#dispute-status-form").find("input[name='URL']").val($(this).attr('href'));
                             $("#dispute-status-form").find("input[name='DisputeID']").val(dispute_id);
                             $("#dispute-status-form").find("input[name='Status']").val(status_value);
                             $("#dispute-status").modal('show', {backdrop: 'static'});
                             return false;
                         });



                    $('table tbody').on('click', '.view-dispute', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#view-modal-dispute').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            if(list_fields[i] == 'Amount'){
                                $("#view-modal-dispute [name='" + list_fields[i] + "']").text(cur_obj.find("input[name='AmountWithSymbol']").val());
                            }else if(list_fields[i] == 'Currency'){
							var currency_sign_show = currency_signs[cur_obj.find("input[name='" + list_fields[i] + "']").val()];
								if(currency_sign_show!='Select a Currency'){
									$("#view-modal-dispute [name='" + list_fields[i] + "']").text(currency_sign_show);
								 }else{
									 $("#view-modal-dispute [name='" + list_fields[i] + "']").text("Currency Not Found");
									 }
							}else {
                                $("#view-modal-dispute [name='" + list_fields[i] + "']").text(cur_obj.find("input[name='" + list_fields[i] + "']").val());
                            }
                        }

                        $('#view-modal-dispute h4').html('View Dispute');
                        $('#view-modal-dispute').modal('show');
                    });

                    $('table tbody').on('click', '.edit-dispute', function (ev) {

                        ev.preventDefault();
                        ev.stopPropagation();
                        var response = new Array();

                        $("#add-edit-dispute-form [name='AccountID']").val('').trigger("change");
                        $("#add-edit-dispute-form [name='InvoiceType']").val('').trigger("change");
                        $('#add-edit-dispute-form').find("input, textarea, select").val("");
                        $('.file-input-name').text('');
                        $("#download_attach").html("");

                        var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");
                        console.log(list_fields[0]);
                        var select = ['AccountID','InvoiceType'];
                        for(var i = 0 ; i< list_fields.length; i++){
                            field_value = cur_obj.find("input[name='"+list_fields[i]+"']").val();
                            if(select.indexOf(list_fields[i])!=-1){
                                if($("#add-edit-dispute-form [name='"+list_fields[i]+"']").hasClass("select2")){
                                    $("#add-edit-dispute-form [name='"+list_fields[i]+"']").val(field_value).trigger("change");
                                }
                            }else{
                                if(list_fields[i] != 'Attachment'){
                                    $("#add-edit-dispute-form [name='"+list_fields[i]+"']").val(field_value);
                                }else{
                                    //For Attachment
                                    if(field_value!='' && typeof(field_value)!='undefined'){
                                        var id=$(this).attr('data-id');
                                        var downloads_ = "{{ URL::to('disputes/{id}/download_attachment')}}";
                                        downloads_  = downloads_ .replace( '{id}', id );
                                        var download_html = '<div class="btn-group"><span class="col-md-offset-1"><a class="btn btn-success btn-sm "  href="'+downloads_+'" title="" ><i class="entypo-down">Download</i></a></span></div>';
                                        $("#download_attach").html(download_html);
                                    }
                                }
                            }
                            response[list_fields[i]] = field_value;

                        }

                        $('#add-edit-modal-dispute h4').html('Edit Dispute');
                        $('#add-edit-modal-dispute').modal('show');

                        //set_dispute(response);

                    });

                     $('table tbody').on('click', '.send-disputes', function (ev) {
                         var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");
                         DisputeID = cur_obj.find("[name=DisputeID]").val();
                         send_url = ("/disputes/{id}/disputes_email").replace("{id}", DisputeID);
                         showAjaxModal(send_url, 'send-modal-disputes');

                         $('#send-modal-disputes').modal('show');
                         emailFileList = [];
                     });

                     $("#send-disputes-form").submit(function (e) {
                         e.preventDefault();
                         var post_data = $(this).serialize();
                         var DisputeID = $(this).find("[name=DisputeID]").val();
                         var _url = baseurl + '/disputes/' + DisputeID + '/send';
                         submit_ajax(_url, post_data);

                     });


                    $("#dispute-status-form").submit(function(e){
                        e.preventDefault();
                        submit_ajax($(this).find("input[name='URL']").val(),$(this).serialize());
                    });

                    $('body').on('click', '.btn.delete-dispute', function (e) {
                        e.preventDefault();
                        if (confirm('Are you sure?')) {
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



                    $("#add-edit-dispute-form [name='AccountID']").change(function(){
                        $("#add-edit-dispute-form [name='AccountName']").val( $("#add-edit-dispute-form [name='AccountID'] option:selected").text());

                        var AccountID = $("#add-edit-dispute-form [name='AccountID'] option:selected").val()

                        if(AccountID >0) {
                            var url = baseurl + '/payments/get_currency_invoice_numbers/'+AccountID;
                            $.get(url, function (response) {

                                console.log(response);
                                if( typeof response.status != 'undefined' && response.status == 'success'){

                                    $("#currency").text('(' + response.Currency_Symbol + ')');

                                    var InvoiceNumbers = response.InvoiceNumbers;
                                    $('input[name=InvoiceNo]').typeahead({
                                        //source: InvoiceNumbers,
                                        local: InvoiceNumbers

                                    });

                                }

                            });

                        }
                    });

                    $('#add-new-dispute').click(function (ev) {
                        ev.preventDefault();
                        $("#download_attach").html("");
                        $('#add-edit-dispute-form').trigger("reset");
                        $("#add-edit-dispute-form [name='AccountID']").val('').trigger("change");
                        $("#add-edit-dispute-form [name='InvoiceType']").val('').trigger("change");
                        $('#add-edit-dispute-form').find("input, textarea, select").val("");
                        $('.file-input-name').text('');
                        $('#add-edit-modal-dispute h4').html('Add New Dispute');
                        $('#add-edit-modal-dispute').modal('show');
                    });

                    $('#add-edit-dispute-form').submit(function(e){
                        e.preventDefault();

                        var DisputeID = $("#add-edit-dispute-form [name='DisputeID']").val();
                        if( typeof DisputeID != 'undefined' && DisputeID > 0 ){
                            submit_url = baseurl + '/disputes/'+DisputeID+'/update';
                        }else{
                            submit_url = baseurl + '/disputes/create';
                        }

                        var formData = new FormData($('#add-edit-dispute-form')[0]);
                        submit_ajax_withfile(submit_url,formData);

                    });

                     $("#dispute-table-search").submit(function(e) {
                         e.preventDefault();

                         //show_loading_bar(40);
                         $searchFilter.AccountID = $("#dispute-table-search select[name='AccountID']").val();
                         $searchFilter.InvoiceNo = $("#dispute-table-search [name='InvoiceNo']").val();
                         $searchFilter.InvoiceType = $("#dispute-table-search [name='InvoiceType']").val();
                         $searchFilter.Status = $("#dispute-table-search select[name='Status']").val();
                         $searchFilter.DisputeDate_StartDate = $("#dispute-table-search input[name='DisputeDate_StartDate']").val();
                         $searchFilter.DisputeDate_EndDate   = $("#dispute-table-search input[name='DisputeDate_EndDate']").val();
                         $searchFilter.tag   = $("#dispute-table-search input[name='tag']").val();


                         data_table.fnFilter('', 0);
                         return false;
                     });


                     $("#bulk_email").click(function () {
                         document.getElementById('BulkMail-form').reset();
                         $("#modal-BulkMail").find('.file-input-name').html("");
                         $("#BulkMail-form [name='email_template']").val('').trigger("change");
                         $("#BulkMail-form [name='template_option']").val('').trigger("change");
                         $("#BulkMail-form").trigger('reset');
                         $("#modal-BulkMail").modal('show');
                     });
                     $('#modal-BulkMail').on('shown.bs.modal', function (event) {
                         var modal = $(this);

                         show_summernote(modal.find(".message"),editor_options);

                     });

                     $('#modal-BulkMail').on('hidden.bs.modal', function (event) {
                         var modal = $(this);


                     });
                     $("#BulkMail-form [name=email_template]").change(function (e) {
                         var templateID = $(this).val();
                         if (templateID > 0) {
                             var url = baseurl + '/accounts/' + templateID + '/ajax_template';
                             $.get(url, function (data, status) {
                                 if (Status = "success") {
                                     var modal = $("#modal-BulkMail");

                                     modal.find('.message').show();

                                     var EmailTemplate = data['EmailTemplate'];
                                     modal.find('[name="subject"]').val(EmailTemplate.Subject);
                                     modal.find('.message').val(EmailTemplate.TemplateBody);

                                     show_summernote(modal.find(".message"),editor_options);
                                 } else {
                                     toastr.error(status, "Error", toastr_opts);
                                 }
                             });
                         }
                     });
                     $("#BulkMail-form [name=template_option]").change(function (e) {
                         if ($(this).val() == 1) {
                             $('#templatename').removeClass("hidden");
                         } else {
                             $('#templatename').addClass("hidden");
                         }
                     });
                     $("#BulkMail-form").submit(function (e) {
                         e.preventDefault();
                         var SelectedIDs = [];
                         var i = 0;
                         if ($("#BulkMail-form").find('[name="test"]').val() == 0) {
                             if (!$('#selectallbutton').is(':checked')) {
                                 $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                                     SelectedID = $(this).val();
                                     SelectedIDs[i++] = SelectedID;
                                 });
                             }
                             var criteria = JSON.stringify($searchFilter);
                             $("#BulkMail-form").find("input[name='criteria']").val(criteria);
                             $("#BulkMail-form").find("input[name='SelectedIDs']").val(SelectedIDs.join(","));

                             if ($("#BulkMail-form").find("input[name='SelectedIDs']").val() != "" && confirm("Are you sure to send mail to selected Accounts") != true) {
                                 $(".btn").button('reset');
                                 $(".savetest").button('reset');
                                 $('#modal-BulkMail').modal('hide');
                                 return false;
                             }
                         }

                         var formData = new FormData($('#BulkMail-form')[0]);
                         var url = baseurl + "/accounts/bulk_mail"
                         $.ajax({
                             url: url,  //Server script to process data
                             type: 'POST',
                             dataType: 'json',
                             success: function (response) {
                                 if (response.status == 'success') {
                                     toastr.success(response.message, "Success", toastr_opts);
                                     $(".save").button('reset');
                                     $(".savetest").button('reset');
                                     $('#modal-BulkMail').modal('hide');
                                     data_table.fnFilter('', 0);
                                     reloadJobsDrodown(0);
                                 } else {
                                     toastr.error(response.message, "Error", toastr_opts);
                                     $(".save").button('reset');
                                     $(".savetest").button('reset');
                                 }
                                 $('.file-input-name').text('');
                                 $('#attachment').val('');
                             },
                             // Form data
                             data: formData,
                             //Options to tell jQuery not to process data or worry about content-type.
                             cache: false,
                             contentType: false,
                             processData: false
                         });
                     });

                     $("#bulk-dispute-send").click(function (ev) {
                         var criteria = '';
                         if ($('#selectallbutton').is(':checked')) {
                             criteria = JSON.stringify($searchFilter);
                         }
                         var DisputeIDs = [];
                         var i = 0;
                         $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                             //console.log($(this).val());
                             DisputeID = $(this).val();
                             if (typeof DisputeID != 'undefined' && DisputeID != null && DisputeID != 'null') {
                                 DisputeIDs[i++] = DisputeID;
                             }
                         });
                         console.log(DisputeIDs);

                         if (DisputeIDs.length) {
                             if (!confirm('Are you sure you want to send selected disputes?')) {
                                 return;
                             }
                             $.ajax({
                                 url: baseurl + '/disputes/bulk_send_dispute_mail',
                                 data: 'DisputeIDs=' + DisputeIDs + '&criteria=' + criteria,
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

                     $("#test").click(function (e) {
                         e.preventDefault();
                         $("#BulkMail-form").find('[name="test"]').val(1);
                         $('#TestMail-form').find('[name="EmailAddress"]').val('');
                         $('#modal-TestMail').modal({show: true});
                     });
                     $('.alerta').click(function (e) {
                         e.preventDefault();
                         var email = $('#TestMail-form').find('[name="EmailAddress"]').val();
                         var accontID = $('.hiddenRowData').find('.rowcheckbox').val();
                         if (email == '') {
                             toastr.error('Email field should not empty.', "Error", toastr_opts);
                             $(".alerta").button('reset');
                             return false;
                         } else if (accontID == '') {
                             toastr.error('Please select sample invoice', "Error", toastr_opts);
                             $(".alerta").button('reset');
                             return false;
                         }
                         $('#BulkMail-form').find('[name="testEmail"]').val(email);
                         $('#BulkMail-form').find('[name="SelectedIDs"]').val(accontID);
                         $("#BulkMail-form").submit();
                         $('#modal-TestMail').modal('hide');

                     });

                     $('#modal-TestMail').on('hidden.bs.modal', function (event) {
                         var modal = $(this);
                         modal.find('[name="test"]').val(0);
                     });
                     $('#BulkMail-form [name="email_template_privacy"]').change(function (e) {
                         var privacyID = $(this).val();
                         var url = baseurl + '/invoice/' + privacyID + '/ajax_getEmailTemplate';
                         $.get(url, function (data, status) {
                             if (Status = "success") {
                                 var modal = $("#modal-BulkMail");
                                 var el = modal.find('#BulkMail-form [name=email_template]');
                                 rebuildSelect2(el,data,'');
                             } else {
                                 toastr.error(status, "Error", toastr_opts);
                             }
                         });
                     });

                });

                 // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

                // not in use
                $('body').on('click', '.btn.reconcile', function (e) {

                     e.preventDefault();
                     var curnt_obj = $(this);
                     curnt_obj.button('loading');


                     var formData =$('#add-edit-dispute-form').serializeArray();

                     reconcile_url = baseurl + '/disputes/reconcile';
                     ajax_json(reconcile_url,formData, function(response){

                         $(".btn").button('reset');

                         if (response.status == 'success') {

                            // console.log(response);
                             //set_dispute(response);
                         }

                     });


                 });

                    // not in use
                 function set_dispute(response){

                     if(typeof response.DisputeID != 'undefined'){

                         $('#add-edit-dispute-form').find("input[name=DisputeID]").val(response.DisputeID);

                     }else{

                         $('#add-edit-dispute-form').find("input[name=DisputeID]").val("");

                     }

                     if(typeof response.DisputeTotal == 'undefined'){

                         $(".reconcile_table").addClass("hidden");
                         $(".btn.ignore").addClass("hidden");


                     }else{

                         $(".reconcile_table").removeClass("hidden");
                         $(".btn.ignore").removeClass("hidden");
                     }



                     $('#add-edit-dispute-form').find("table .DisputeTotal").text(response.DisputeTotal);
                     $('#add-edit-dispute-form').find("table .DisputeDifference").text(response.DisputeDifference);
                     $('#add-edit-dispute-form').find("table .DisputeDifferencePer").text(response.DisputeDifferencePer);

                     $('#add-edit-dispute-form').find("input[name=DisputeTotal]").val(response.DisputeTotal);
                     $('#add-edit-dispute-form').find("input[name=DisputeDifference]").val(response.DisputeDifference);
                     $('#add-edit-dispute-form').find("input[name=DisputeDifferencePer]").val(response.DisputeDifferencePer);

                     $('#add-edit-dispute-form').find("table .DisputeMinutes").text(response.DisputeMinutes);
                     $('#add-edit-dispute-form').find("table .MinutesDifference").text(response.MinutesDifference);
                     $('#add-edit-dispute-form').find("table .MinutesDifferencePer").text(response.MinutesDifferencePer);

                     $('#add-edit-dispute-form').find("input[name=DisputeMinutes]").val(response.DisputeMinutes);
                     $('#add-edit-dispute-form').find("input[name=MinutesDifference]").val(response.MinutesDifference);
                     $('#add-edit-dispute-form').find("input[name=MinutesDifferencePer]").val(response.MinutesDifferencePer);


                 }

                // not in use
                 function reset_dispute() {


                     $('#add-edit-dispute-form').find("table .DisputeTotal").text("");
                     $('#add-edit-dispute-form').find("table .DisputeDifference").text("");
                     $('#add-edit-dispute-form').find("table .DisputeDifferencePer").text("");

                     $('#add-edit-dispute-form').find("input[name=DisputeTotal]").val("");
                     $('#add-edit-dispute-form').find("input[name=DisputeDifference]").val("");
                     $('#add-edit-dispute-form').find("input[name=DisputeDifferencePer]").val("");

                     $('#add-edit-dispute-form').find("table .DisputeMinutes").text("");
                     $('#add-edit-dispute-form').find("table .MinutesDifference").text("");
                     $('#add-edit-dispute-form').find("table .MinutesDifferencePer").text("");

                     $('#add-edit-dispute-form').find("input[name=DisputeMinutes]").val("");
                     $('#add-edit-dispute-form').find("input[name=MinutesDifference]").val("");
                     $('#add-edit-dispute-form').find("input[name=MinutesDifferencePer]").val("");

                     $(".reconcile_table").addClass("hidden");
                     $(".btn.ignore").addClass("hidden");

                 }


                //delete Dispute
                 $('body').on('click', '.dispute_delete', function (e) {
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

            </script>
    <style>
                .dataTables_filter label{
                    display:none !important;
                }
                .dataTables_wrapper .export-data{
                    right: 30px !important;
                }
                #selectcheckbox {
                    padding: 15px 10px;
                }
            </style>
    @include('includes.errors')
    @include('includes.success')

    @include('accounts.bulk_email')


 @stop
@section('footer_ext')
@parent
<div class="modal fade" id="add-edit-modal-dispute">
  <div class="modal-dialog">
    <div class="modal-content">
    <form id="add-edit-dispute-form" method="post">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Dispute</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Invoice Type *<span id="currency"></span></label>
                {{Form::select('InvoiceType',$InvoiceTypes,'',array("class"=>"select2 small"))}}
            </div>
          </div>
            <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Account Name * <span id="currency"></span></label>
              {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
            </div>
          </div>
          <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Invoice Number</label>
                    <input type="text" id="InvoiceAuto" name="InvoiceNo" class="form-control" id="field-5" placeholder="">
                </div>
          </div>
          <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Dispute Amount*</label>
                    <input type="text" name="DisputeAmount" class="form-control" id="field-5" placeholder="" >
                </div>
          </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Ref</label>
                    <input type="text" id="Ref" name="Ref" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Notes</label>
              <textarea name="Notes" class="form-control" id="field-5" rows="10" placeholder=""></textarea>
            </div>
          </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="Attachment" class="control-label">Attachment (pdf,png,jpg,gif,xls,csv,xlsx)</label>
                    <div class="clear clearfix"></div>
                    <input id="Attachment" name="Attachment" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                    <div id="download_attach" class="pull-right" style="margin-right: 250px;"></div>
                </div>
            </div>
            {{--<div class="col-md-12">
                <div class="form-group">
                    <label class="control-label"  >Send Email to Customer</label>
                        <p class="make-switch switch-small">
                            <input id="senEMail" name="sendEmail" type="checkbox" value="1" >
                        </p>
                </div>
            </div>--}}
        </div>
      </div>
      <div class="modal-footer">
          <input type="hidden" name="DisputeID" >
          <input type="hidden" name="Currency" >
          {{--<input type="hidden" name="InvoiceID" >--}}
          <button type="submit" id="dispute-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
      </div>
    </form>
  </div>
</div>
</div>

<div class="modal fade in" id="send-modal-disputes">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="send-disputes-form" method="post" action="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Send Disputes By Email</h4>
                </div>
                <div class="modal-body"> </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left"
                            data-loading-text="Loading..."> <i class="entypo-mail"></i> Send </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade in" id="dispute-status">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="dispute-status-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Dispute Notes</h4>
        </div>
        <div class="modal-body">
          <div id="text-boxes" class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Notes</label>
                <textarea type="text" name="Notes" class="form-control"  ></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
            <input type="hidden" name="URL" value="">
            <input type="hidden" name="DisputeID" value="">
            <input type="hidden" name="Status" value="">

          <button type="submit" id="dispute-status" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
          <i class="entypo-floppy"></i>

          Save
          </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop