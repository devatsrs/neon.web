@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
    <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li><a href="{{URL::to('invoice')}}">Invoice</a></li>
    <li class="active"> <strong>Recurring Profiles</strong> </li>
</ol>
<h3>Recurring Profiles</h3>
@include('includes.errors')
@include('includes.success')
<p style="text-align: right;"> @if(User::checkCategoryPermission('RecurringProfile','Add')) <a href="{{URL::to("recurringprofiles/create")}}" id="add-new-recurringinvoices" class="btn btn-primary "> <i class="entypo-plus"></i> Add New </a> @endif
  <!-- <a href="javascript:;" id="bulk-recurringinvoices" class="btn upload btn-primary ">
        <i class="entypo-upload"></i>
        Bulk recurringinvoices Generate.
    </a>-->
</p>
<div class="row">
  <div class="col-md-12">
    <form id="recurringinvoices_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title"> Filter </div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Account</label>
            <div class="col-sm-2"> {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }} </div>
            <label for="field-1" class="col-sm-2 control-label">Status</label>
            <div class="col-sm-2"> {{ Form::select('Status', RecurringInvoice::get_recurringinvoices_status(), RecurringInvoice::ACTIVE , array("class"=>"select2 small","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }} </div>
          </div>
          <p style="text-align: right;">
            <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left"> <i class="entypo-search"></i> Search </button>
          </p>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="row">
  <div  class="col-md-12">
    <div class="input-group-btn pull-right" style="width:70px;"> @if( User::checkCategoryPermission('RecurringProfile','Edit,Delete'))
      <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
      <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
          @if(User::checkCategoryPermission('RecurringProfile','Edit'))
              <li> <a data-action="changestatus_bulk" title="Active" data-startstop="1" href="javascript:void(0);" ><i class="entypo-check"></i>Active</a> </li>
              <li> <a data-action="changestatus_bulk" title="In Active" data-startstop="0" href="javascript:void(0);" ><i class="glyphicon glyphicon-ban-circle"></i> In Active</a> </li>
          @endif
          @if(User::checkCategoryPermission('RecurringProfile','Delete'))
              <li> <a data-action="delete_bulk" href="javascript:;" ><i class="entypo-trash"></i> Delete </a> </li>
          @endif
          <!--if(User::checkCategoryPermission('RecurringInvoice','Send'))
              <li> <a data-action="sendinvoice_bulk" title="Start" data-startstop="1" href="javascript:void(0);" ><i class="entypo-mail"></i> Send</a> </li>
          endif-->
      </ul>
      @endif
      <form id="invoice-form" >
          <input type="hidden" name="criteria" value="">
          <input type="hidden" name="selectedIDs" value="">
      </form>
    </div>
    <!-- /btn-group --> 
  </div>
  <div class="clear"></div>
</div>
<br>
</form>
<table class="table table-bordered datatable" id="table-4">
  <thead>
    <tr>
        <th width="5%"><div class="pull-left">
          <input type="checkbox" id="selectall" name="checkbox[]" class="" />
        </div>
        </th>
        <th width="18%">Title</th>
        <th width="17%">Account Name</th>
        <th width="10%">Date</th>
        <th width="10%">Next Invoice Date</th>
        <th width="10%">Grand Total</th>
        <th width="5%">Status</th>
        <th width="10%">Frequency/Occurrence</th>
        <th width="20%">Action</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<script type="text/javascript">
var $searchFilter 	= 	{};
var checked			=	'';
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
		
		jQuery(document).on( 'click', '.delete_link', function(event){			
			event.preventDefault();
			var url_del = jQuery(this).attr('href');
			
			//////////////////////////////////////
			
			 $.ajax({
                url: url_del,
                type: 'POST',
                dataType: 'json',
				data:{"del":1},
                success: function(response_del) {
                       if (response_del.status == 'success')
					   {
						   jQuery(this).parent().parent().parent().hide('slow').remove();                          
                           data_table.fnFilter('', 0);
                       }
					   else
					   {
                           ShowToastr("error",response.message);
                       }
                   
					}
			});	
		
			//////////////////////////////////////////
			
		});
		
        public_vars.$body = $("body");
        //show_loading_bar(40);
        var billingCyleType = {{json_encode(SortBillingType())}};
		var base_url_recurringinvoices 		= 	"{{ URL::to('recurringinvoices')}}";
        var recurringinvoicesstatus 			=	{{$recurringinvoices_status_json}};
        var recurringinvoices_Status_Url 	= 	"{{ URL::to('recurringprofiles/recurringinvoices_change_Status')}}";
		var delete_url_bulk 		= 	"{{ URL::to('recurringprofiles/recurringinvoices_delete_bulk')}}";
        var list_fields  			= 	['AccountName','RecurringInvoiceNumber','IssueDate','GrandTotal','Status','RecurringInvoiceID','Description','Attachment','AccountID','BillingEmail'];
		
        $searchFilter.AccountID 			= 	$("#recurringinvoices_filter select[name='AccountID']").val();
        $searchFilter.Status 		= 	$("#recurringinvoices_filter select[name='Status']").val();
		$searchFilter.CurrencyID            =   $("#recurringinvoices_filter [name='CurrencyID']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/recurringprofiles/ajax_datagrid/type",
            "iDisplayLength": '{{CompanyConfiguration::get('PAGE_SIZE')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[4, 'desc']],
             "fnServerParams": function(aoData) {				
                aoData.push({"name":"AccountID","value":$searchFilter.AccountID},{"name":"Status","value":$searchFilter.Status});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"AccountID","value":$searchFilter.AccountID},{"name":"Status","value":$searchFilter.Status},{ "name": "Export", "value": 1});
            },
             "aoColumns":
            [
                {  "bSortable": false, //0 RecurringInvoiceID
                    mRender: function ( id, type, full ) {
                            var chackbox = '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + full[0] + '" class="rowcheckbox" ></div>';
                            return chackbox;
                    }
                },
                {}, // 1 Title
                {}, // 2 AccountName
                {}, // 3 Invoice StartDate
                {}, // 4 Next InvoiceDate
                {}, // 5 GrandTotal
                {   // 6 Status
                    "bSortable":false,
                    mRender:function( status, type, full){
                        if (status == 1)
                            return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                        else
                            return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                    }
                },
                {  // 7 Frequency/Occurence
                    "bSortable": false,
                    mRender: function ( id, type, full ) {
                        var cycle = billingCyleType[full[9]];
                        var anniversary = ((full[10])?full[10]:'');
                        if(cycle.indexOf('anniversary')!=-1){
                            var d = new Date(full[10]);
                            anniversary = d.getDate();
                        }
                       // var invoice_log = (baseurl + "/recurringprofiles/{id}/log/{{RecurringInvoiceLog::SENT}}").replace("{id}", full[0]);
					   var invoice_log = (baseurl + "/recurringprofiles/{id}/log").replace("{id}", full[0]);
                        var str = '<div><strong>Frequency:</strong><span>'+billingCyleType[full[9]]+((full[10])?'('+anniversary+')':'');+'</span></div>';
                        str += '<div><strong>Occurrence:</strong><span>'+full[7]+'</span></div>';
						str += '<div><strong>Log:</strong><span><a href="' + invoice_log + '" target="_blank">'+(full[8]?full[8]:0)+'</a></span></div>';
                        return str;
                    }
                },
                {  // 8 Action
                    "bSortable": false,
                    mRender: function ( id, type, full ) {
                        var action = '';
                        id = full[0];
                        var edit_url = (baseurl + "/recurringprofiles/{id}/edit").replace("{id}", id);
                        var invoice_log = (baseurl + "/recurringprofiles/{id}/log").replace("{id}", id);
                        //var delete_ = (baseurl + "/recurringprofiles/{id}/delete").replace("{id}", id);



                        action += '<div class="btn-group">';
                        action += ' <a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">Action<span class="caret"></span></a>';
                        action += '<ul class="dropdown-menu multi-level dropdown-menu-left" role="menu" aria-labelledby="dropdownMenu">';


                        if ('{{User::checkCategoryPermission('RecurringProfile','Edit')}}') {
                            action += '<li><a class="icon-left"  href="' + edit_url + '"><i class="entypo-pencil"></i>Edit </a></li>';
                        }

                        if ('{{User::checkCategoryPermission('RecurringProfile','Delete')}}') {
                            action += '<li><a href="#" data-action="delete_row" class="icon-left"><i class="entypo-trash"></i>Delete </a></li>';
                        }

                        /*if('{{User::checkCategoryPermission('RecurringProfile','Send')}}') {
                            action += '<li><a href="#" data-action="sendinvoice_row" class="icon-left"><i class="entypo-mail"></i>Send </a></li>';
                        }*/

                        action += '<li><a href="' + invoice_log + '" class="icon-left" target="_blank"><i class="entypo-list"></i>Log </a></li>';

                        action += '</ul>';
                        action += '</div>';

                        if(full[6] == 1 ) {
                            action += '&nbsp;<button data-startstop="0" data-action="changestatus_row" class="btn btn-red btn-sm" type="button" title="InActive" data-placement="top" data-toggle="tooltip"><i class="glyphicon glyphicon-ban-circle" ></i></button>';
                        }else {
                            action += '&nbsp;<button data-startstop="1" data-action="changestatus_row" class="btn btn-green btn-sm" type="button" title="Active" data-placement="top" data-toggle="tooltip"><i class="entypo-check"></i></button>';
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
                        "sUrl": baseurl + "/recurringprofiles/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/recurringprofiles/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
			  //get_total_grand();
                $('#table-4 tbody tr').each(function(i, el) {
                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
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
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
               $('#selectallbutton').click(function(ev) {
                   if($(this).is(':checked')){
                       checked = 'checked=checked disabled';
                       $("#selectall").prop("checked", true).prop('disabled', true);
                       if(!$('#changeSelectedRecurringInvoice').hasClass('hidden')){
                           $('#table-4 tbody tr').each(function(i, el) {
                               if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                   $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                   $(this).addClass('selected');
                               }
                           });
                       }
                   }else{
                       checked = '';
                       $("#selectall").prop("checked", false).prop('disabled', false);
                       if(!$('#changeSelectedRecurringInvoice').hasClass('hidden')){
                           $('#table-4 tbody tr').each(function(i, el) {
                               if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

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

        $("#recurringinvoices_filter").submit(function(e){
            e.preventDefault();
            $searchFilter.AccountID 			= 	$("#recurringinvoices_filter select[name='AccountID']").val();
            $searchFilter.Status 		= 	$("#recurringinvoices_filter select[name='Status']").val();
            $searchFilter.CurrencyID            =   $("#recurringinvoices_filter [name='CurrencyID']").val();
            data_table.fnFilter('', 0);
            return false;
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

        $(document).on('click', '[data-action$="_bulk"], [data-action$="_row"]', function (e) { //All bulk and row actions
            e.preventDefault();
            e.stopPropagation();
            var url = "";
            var row = 0;
            var self = $(this);
            var RecurringInvoiceIDs =[];
            $('#invoice-form').find('[name="selectedIDs"]').val('');
            $('#invoice-form').find('[name="criteria"]').val('');
            if(self.attr('data-action').indexOf('_row')!=-1){ //For row action
                setSelection(self);
                var tr = self.parents('tr');
                var ID = tr.find('.rowcheckbox:checked').val();
                RecurringInvoiceIDs[0] = ID;
                $("#send-invoice-form").find('[name="RecurringInvoiceID"]').val(ID);
            }else if(self.attr('data-action').indexOf('_bulk')!=-1){ //For bulk action
                RecurringInvoiceIDs = getselectedIDs();
            }

            if(self.attr('data-action').indexOf('changestatus')!=-1) { //For start and Stop the recurring invoice
                url = (baseurl + '/recurringprofiles/startstop/{status}').replace("{status}", self.attr('data-startstop'));
            }else if(self.attr('data-action').indexOf('delete')!=-1) { //For deleting recurring invoice template
                url = baseurl+'/recurringprofiles/delete';
            }else if(self.attr('data-action').indexOf('sendinvoice')!=-1) { //For send invoice using recurring invoice template
                if(self.attr('data-action').indexOf('_row')!=-1){ //Checking for one selectedID to show sendmail modal
                    row = 1;
                }
                url = baseurl+'/recurringprofiles/sendinvoice';
            }

            var criteria			  =  '';
            if($('#selectallbutton').is(':checked')){
                criteria = 'criteria';
                $('#invoice-form').find('[name="criteria"]').val(JSON.stringify($searchFilter));
            }else{
                $('#invoice-form').find('[name="selectedIDs"]').val(RecurringInvoiceIDs);
            }

            if(RecurringInvoiceIDs.length==0 && criteria=='') {
                alert("Please select atleast one account.");
                return false;
            }
            var formData = new FormData($('#invoice-form')[0]);
            if(row==1){ //Only For showing send mail modal on Single Invoice.
                $('#send-modal-invoice .modal-body').html("Content is loading...");
                showAjaxScript(url,formData,function(response){
                    if (response.status == 'success') {
                        if(response.invoiceID>0) {
                            var send_url = (baseurl + "/invoice/{id}/invoice_email").replace("{id}", response.invoiceID);
                            showAjaxScript(send_url, formData, function (response) {
                                $('#send-modal-invoice .modal-body').html(response);
                                $('#send-modal-invoice').modal('show');
                            }, 'html');
                        }
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },'json');
            }else {
                showAjaxScript(url,formData,function(response){
                    $(".btn.save").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#selectallbutton').prop('checked', false);
                        data_table.fnFilter('', 0);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                })
            }

        });

        $('#generate-new-invoice').click(function (e) {
            e.preventDefault();
            update_new_url = "{{URL::to("recurringprofiles/generate")}}";
            submit_ajax(update_new_url, $('#send-invoice-form').serialize(), 1)
        });

        $("#send-invoice-form").submit(function (e) {
            e.preventDefault();
            var post_data = $(this).serialize();
            var InvoiceID = $(this).find("[name=InvoiceID]").val();
            var _url = baseurl + '/invoice/' + InvoiceID + '/send';
            submit_ajax(_url, post_data);
            data_table.fnFilter('', 0);
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

        function getselectedIDs(){
            var SelectedIDs = [];
            $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                var accountIDs = $(this).val().trim();
                SelectedIDs[i++] = accountIDs;
            });
            return SelectedIDs;
        }
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

<div class="modal fade in" id="send-modal-invoice">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="send-invoice-form" method="post" class="form-horizontal form-groups-bordered">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Send Invoice By Email</h4>
                </div>
                <div class="modal-body">


                </div>
                <input type="hidden" name="RecurringInvoice" value="1" >
                <input type="hidden" name="RecurringInvoiceID" >
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left"
                            data-loading-text="Loading...">
                        <i class="entypo-mail"></i>
                        Send
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop