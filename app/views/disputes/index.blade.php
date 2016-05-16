@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <a href="javascript:void(0)">Disputes</a> </li>
</ol>
<h3>Disputes</h3>
<div class="tab-content">
  <div class="tab-pane active" id="customer_rate_tab_content">
    <div class="row">
      <div class="col-md-12">
        <form role="form" id="dispute-table-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
          <div class="panel panel-primary" data-collapsed="0">
          <div class="panel-heading">
            <div class="panel-title"> Filter </div>
            <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
          </div>
          <div class="panel-body">
              <div class="form-group">
                  <label for="field-1" class="col-sm-1 control-label">Account</label>
                  <div class="col-sm-2 "> {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }} </div>
                  <label class="col-sm-1 control-label small_label" for="DisputeDate_StartDate">Start Date</label>
                  <div class="col-sm-2 ">
                      <input autocomplete="off" type="text" name="DisputeDate_StartDate" id="DisputeDate_StartDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d',strtotime("-1 week"))}}" data-enddate="{{date('Y-m-d')}}" />
                  </div>
                  <label  class="col-sm-1 control-label" for="DisputeDate_EndDate">End Date</label>
                  <div class="col-sm-2 ">
                      <input autocomplete="off" type="text" name="DisputeDate_EndDate" id="DisputeDate_EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}" />
                  </div>
              </div>

            <div class="form-group">


                <label class="col-sm-1 control-label">Invoice Type</label>
              <div class="col-sm-2">
                  {{Form::select('InvoiceType',Invoice::$invoice_type,'',array("class"=>"selectboxit"))}}
              </div>
                <label class="col-sm-1 control-label">Invoice No</label>
              <div class="col-sm-2">
                <input type="text" name="InvoiceNo" class="form-control" id="field-1" placeholder="" value="{{Input::get('InvoiceNo')}}" />
              </div>
              <label for="field-1" class="col-sm-1 control-label small_label">Status</label>
              <div class="col-sm-2 "> {{ Form::select('Status', Dispute::$Status, Dispute::PENDING, array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }} </div>
            </div>
 

            <p style="text-align: right;">
              <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left"> <i class="entypo-search"></i> Search </button>
            </p>
          </div>
        </form>
      </div>
    </div>
    <div class="clear"></div>
     <p class="col-md-12" style=" text-align: right;"> <a href="#" id="add-new-dispute" class="btn btn-primary "> <i class="entypo-plus"></i>Add New Dispute</a> </p>
    <br>
    <table class="table table-bordered datatable" id="table-4">
      <thead>
        <tr>
          <th width="5%"></th>
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
     var list_fields  = ['InvoiceType','AccountName','InvoiceNo','DisputeAmount','Status','created_at', 'CreatedBy','ShortNotes','DisputeID','Attachment','AccountID','Notes'];

     var $searchFilter = {};
     $searchFilter.Status = $("#dispute-table-search select[name='Status']").val();
     $searchFilter.DisputeDate_StartDate = $("#dispute-table-search input[name='DisputeDate_StartDate']").val();
     $searchFilter.DisputeDate_EndDate   = $("#dispute-table-search input[name='DisputeDate_EndDate']").val();
     $searchFilter.InvoiceType   = $("#dispute-table-search select[name='InvoiceType']").val();
     $searchFilter.AccountID   = $("#dispute-table-search select[name='AccountID']").val();
     $searchFilter.InvoiceNo   = $("#dispute-table-search input[name='InvoiceNo']").val();
     $searchFilter.Status   = $("#dispute-table-search select[name='Status']").val();

     var update_new_url;
     var postdata;
     var dispute_status = {{json_encode(Dispute::$Status);}};

     jQuery(document).ready(function ($) {
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
									{"name": "DisputeDate_EndDate","value": $searchFilter.DisputeDate_EndDate}

                            );
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push(
                                    {"name": "AccountID", "value": $searchFilter.AccountID},
                                    {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                    {"name": "InvoiceType","value": $searchFilter.InvoiceType},
                                    {"name": "Status","value": $searchFilter.Status},
									{"name": "DisputeDate_StartDate","value": $searchFilter.DisputeDate_StartDate},
									{"name": "DisputeDate_EndDate","value": $searchFilter.DisputeDate_EndDate},
                                    {"name":"Export","value":1});

                        },
                        "iDisplayLength": '{{Config::get('app.pageSize')}}',
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[9, 'desc']],
                        "aoColumns": [
                            {
                                "bSortable": true, //InvoiceType
                                mRender: function ( id, type, full ) {
                                    if (id == '{{Invoice::INVOICE_IN}}'){
                                        invoiceType = ' <button class=" btn btn-primary pull-right" title="Invoice Received"><i class="entypo-right-bold"></i>RCV</a>';
                                    }else{
                                        invoiceType = ' <button class=" btn btn-primary pull-right" title="Invoice Sent"><i class="entypo-left-bold"></i>SNT</a>';

                                    }
                                    return invoiceType;
                                }
                            },{
                                "bSortable": true, //Account
                            },
                            {
                                "bSortable": true, //InvoiceNo
                            },
                            {
                                "bSortable": true, //DisputeAmount
                                mRender: function (id, type, full) {
                                    return parseFloat(id).toFixed(2);
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
                                    if('{{User::checkCategoryPermission('Disputes','Edit')}}' ){
                                        action += ' <a href="" class="edit-dispute btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit</a>';
                                    }
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
                                        action += '<span class="col-md-offset-1"><a class="btn btn-success btn-sm btn-icon icon-left"  href="'+downloads_+'" title="" ><i class="entypo-down"></i>Download</a></span>'
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
                        }

                    });


                        // Replace Checboxes
                        $(".pagination a").click(function (ev) {
                            replaceCheckboxes();
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

                        $("#add-edit-dispute-form [name='AccountID']").select2().select2('val','');
                        $("#add-edit-dispute-form [name='InvoiceType']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        $('#add-edit-dispute-form').find("input, textarea, select").val("");
                        $('.file-input-name').text('');


                        var cur_obj = $(this).prev("div.hiddenRowData");
                        var select = ['AccountID','InvoiceType'];
                        for(var i = 0 ; i< list_fields.length; i++){
                            field_value = cur_obj.find("input[name='"+list_fields[i]+"']").val();

                            if(select.indexOf(list_fields[i])!=-1){

                                if($("#add-edit-dispute-form [name='"+list_fields[i]+"']").hasClass("select2")){

                                    $("#add-edit-dispute-form [name='"+list_fields[i]+"']").select2().select2('val',field_value);

                                }else if($("#add-edit-dispute-form [name='"+list_fields[i]+"']").hasClass("selectboxit")){

                                    $("#add-edit-dispute-form [name='InvoiceType']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(field_value);
                                }


                            }else{
                                if(list_fields[i] != 'Attachment'){

                                    $("#add-edit-dispute-form [name='"+list_fields[i]+"']").val(field_value);
                                }
                            }
                            response[list_fields[i]] = field_value;
                        }

                        $('#add-edit-modal-dispute h4').html('Edit Dispute');
                        $('#add-edit-modal-dispute').modal('show');

                        //set_dispute(response);

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
                                    $('input.typeahead').typeahead({
                                        //source: InvoiceNumbers,
                                        local: InvoiceNumbers

                                    });

                                }

                            });

                        }
                    });

                    $('#add-new-dispute').click(function (ev) {
                        ev.preventDefault();
                        $('#add-edit-dispute-form').trigger("reset");
                        $("#add-edit-dispute-form [name='AccountID']").select2().select2('val','');
                        $("#add-edit-dispute-form [name='InvoiceType']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
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


                    data_table.fnFilter('', 0);
                    return false;
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
    @include('includes.success') </div>
</div>
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
                {{Form::select('InvoiceType',$InvoiceTypes,'',array("class"=>"selectboxit"))}}
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
                    <input type="text" id="InvoiceAuto" name="InvoiceNo" class="form-control typeahead" id="field-5" placeholder="">
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
              <label for="field-5" class="control-label">Notes</label>
              <textarea name="Notes" class="form-control" id="field-5" rows="10" placeholder=""></textarea>
            </div>
          </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="Attachment" class="control-label">Attachment (pdf,png,jpg,gif,xls,csv,xlsx)</label>
                    <div class="clear clearfix"></div>
                    <input id="Attachment" name="Attachment" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                </div>
            </div>
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