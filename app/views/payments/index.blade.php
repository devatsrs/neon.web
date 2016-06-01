@extends('layout.main')

@section('content')
<style>
.small_fld{width:80.6667%;}
.small_label{width:5.0%;}
.col-sm-e2{width:15%;}
#table-4_wrapper{padding-left:15px; padding-right:15px;}
.small-date-input{width:11%;}
</style>
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <a href="javascript:void(0)">Payments</a> </li>
</ol>
<h3>Payments</h3>
<p style="text-align: right;"> <a href="javascript:;" id="upload-payments" class="btn upload btn-primary "> <i class="entypo-upload"></i> Upload </a> </p>
<div class="tab-content">
  <div class="tab-pane active" id="customer_rate_tab_content">
    <div class="row">
      <div class="col-md-12">
        <form role="form" id="payment-table-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
          <div class="panel panel-primary" data-collapsed="0">
          <div class="panel-heading">
            <div class="panel-title"> Filter </div>
            <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label for="field-1" class="col-sm-1 control-label small_label">Account</label>
              <div class="col-sm-2 col-sm-e2"> {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }} </div>
              <label class="col-sm-1 control-label small_label">Invoice No</label>
              <div class="col-sm-2 col-sm-e2">
                <input type="text" name="InvoiceNo" class="form-control" id="field-1" placeholder="" value="{{Input::get('InvoiceNo')}}" />
              </div>
              <label for="field-1" class="col-sm-1 control-label small_label">Status</label>
              <div class="col-sm-2 "> {{ Form::select('Status', Payment::$status, (!empty(Input::get('Status'))?Input::get('Status'):'Pending Approval'), array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }} </div>
              <label for="field-1" class="col-sm-1 control-label small_label">Action</label>
              <div class="col-sm-2 col-sm-e2"> {{ Form::select('type', Payment::$action, '', array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }} </div>
                     <label class="col-sm-1 control-label">Recalled</label>
              <div class="col-sm-1">
                <p class="make-switch switch-small">
                  <input id="Recall_on_off" name="recall_on_off" type="checkbox" value="1">
                </p>
              </div>
            </div>
 
            <!--payment date start -->
            <div class="form-group">
              <label class="col-sm-1 control-label small_label" for="PaymentDate_StartDate">Date From</label>
              <div class="col-sm-2 small-date-input" >
                <input autocomplete="off" type="text" name="PaymentDate_StartDate" id="PaymentDate_StartDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="{{Input::get('StartDate')}}" data-enddate="{{date('Y-m-d')}}" />
              </div>
              <div class="col-sm-2  small-date-input">
                <input type="text" name="PaymentDate_StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00:01" data-show-seconds="true" data-template="dropdown" placeholder="00:00:00" class="form-control timepicker">
              </div>
              <label  class="col-sm-1 control-label small_label" for="PaymentDate_EndDate">End Date</label>
              <div class="col-sm-2  small-date-input">
                <input autocomplete="off" type="text" name="PaymentDate_EndDate" id="PaymentDate_EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{Input::get('EndDate')}}" data-enddate="{{date('Y-m-d')}}" />
              </div>
              <div class="col-sm-2  small-date-input">
                <input type="text" name="PaymentDate_EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" placeholder="00:00:00" data-template="dropdown" class="form-control timepicker">
              </div>
           
            <!--payment date end -->
                       
              <label for="field-1" class="col-sm-1 control-label" style="width: 6%;">Payment Method</label>
              <div class="col-sm-2"> {{ Form::select('paymentmethod', Payment::$method, Input::get('paymentmethod') , array("class"=>"selectboxit","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }} </div>
              
              <label for="field-1" class="col-sm-2 control-label" style="width: 7%;">Currency</label>
            <div class="col-sm-2" style="padding:0; width: 14%;"> {{Form::select('CurrencyID',Currency::getCurrencyDropdownIDList(),$DefaultCurrencyID,array("class"=>"selectboxit"))}} </div>
       
            </div> 
            <p style="text-align: right;">
              <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left"> <i class="entypo-search"></i> Search </button>
            </p>
          </div>
          </div>
        </form>
      </div>
    </div>
    <div class="clear"></div>
    <div class="row hidden" id="add-template">
      <div class="col-md-12">
        <form id="add-template-form" method="post">
          <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
              <div class="panel-title"> Payment Mapping </div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
              <div class="form-group">
                <label for="field-1" class="col-sm-2 control-label">Template Name:</label>
                <div class="col-sm-4">
                  <input type="text" class="form-control" name="TemplateName" value="" />
                </div>
              </div>
              <br />
              <br />
              <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                  <div class="panel-title"> Payments CSV Importer </div>
                  <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
                </div>
                <div class="panel-body">
                  <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Delimiter:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="option[Delimiter]" value="," />
                    </div>
                    <label for="field-1" class="col-sm-2 control-label">Enclosure:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="option[Enclosure]" value="" />
                    </div>
                  </div>
                  <div class="form-group"> <br />
                    <br />
                    <label class="col-sm-2 control-label">Escape:</label>
                    <div class="col-sm-4">
                      <input type="text" class="form-control" name="option[Escape]" value="" />
                    </div>
                    <label for="field-1" class="col-sm-2 control-label">First row:</label>
                    <div class="col-sm-4"> {{Form::select('option[Firstrow]', array('columnname'=>'Column Name','data'=>'Data'),'',array("class"=>"selectboxit"))}} </div>
                  </div>
                  <p style="text-align: right;"> <br />
                    <br />
                    <button class="check btn btn-primary btn-sm btn-icon icon-left"> <i class="entypo-floppy"></i> Check </button>
                  </p>
                </div>
              </div>
              <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                  <div class="panel-title"> Field Remapping </div>
                  <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
                </div>
                <div class="panel-body" id="mapping">
                  <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Account Name*</label>
                    <div class="col-sm-4"> {{Form::select('selection[AccountName]', array(),'',array("class"=>"selectboxit"))}} </div>
                    <label for="field-1" class="col-sm-2 control-label">Payment Date*</label>
                    <div class="col-sm-4"> {{Form::select('selection[PaymentDate]', array(),'',array("class"=>"selectboxit"))}} </div>
                  </div>
                  <div class="form-group"> <br />
                    <br />
                    <label for="field-1" class="col-sm-2 control-label">Payment Method*</label>
                    <div class="col-sm-4"> {{Form::select('selection[PaymentMethod]', array(),'',array("class"=>"selectboxit"))}} </div>
                    <label for="field-1" class="col-sm-2 control-label">Action*</label>
                    <div class="col-sm-4"> {{Form::select('selection[PaymentType]', array(),'',array("class"=>"selectboxit"))}} </div>
                  </div>
                  <div class="form-group"> <br />
                    <br />
                    <label for="field-1" class="col-sm-2 control-label">Amount*</label>
                    <div class="col-sm-4"> {{Form::select('selection[Amount]', array(),'',array("class"=>"selectboxit"))}} </div>
                    <label for="field-1" class="col-sm-2 control-label">Invoice</label>
                    <div class="col-sm-4"> {{Form::select('selection[InvoiceNo]', array(),'',array("class"=>"selectboxit"))}} </div>
                  </div>
                  <div class="form-group"> <br />
                    <br />
                    <label for="field-1" class="col-sm-2 control-label">Note</label>
                    <div class="col-sm-4"> {{Form::select('selection[Notes]', array(),'',array("class"=>"selectboxit"))}} </div>
                    <label for=" field-1" class="col-sm-2 control-label">Date Format</label>
                    <div class="col-sm-4"> {{Form::select('selection[DateFormat]',Company::$date_format ,'',array("class"=>"selectboxit"))}} </div>
                  </div>
                </div>
              </div>
              <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                  <div class="panel-title"> CSV File to be loaded </div>
                  <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
                </div>
                <div class="panel-body">
                  <div id="table-4_processing" class="dataTables_processing hidden">Processing...</div>
                  <table class="table table-bordered datatable" id="tablemapping">
                    <thead>
                      <tr> </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
              <p style="text-align: right;">
                <input type="hidden" name="PaymentUploadTemplateID" />
                <input type="hidden" name="TemplateFile" value="" />
                <input type="hidden" name="TempFileName" value="" />
                <input type="hidden" name="ProcessID" value="" />
                <button id="payments-upload" type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
              </p>
            </div>
          </div>
        </form>
      </div>
    </div>
    <br>
    @if(User::can('Payments','Add'))
    <p style="text-align: right;"> <a href="#" id="add-new-payment" class="btn btn-primary "> <i class="entypo-plus"></i> Add New Payment Request </a> </p>
    @endif
    <table class="table table-bordered datatable" id="table-4">
      <thead>
        <tr>
          <th width="10%">Account Name</th>
          <th width="10%">Invoice No</th>
          <th width="10%">Amount</th>
          <th width="8%">Type</th>
          <th width="10%">Payment Date</th>
          <th width="10%">Status</th>
          <th width="10%">CreatedBy</th>
          <th width="10%">Notes</th>
          <th width="15%">Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    </div>
    <script type="text/javascript">
	
	 var currency_signs = {{$currency_ids}};
                var list_fields  = ['PaymentID','AccountName','AccountID','Amount','PaymentType','Currency','PaymentDate','Status','CreatedBy','PaymentProof','InvoiceNo','PaymentMethod','Notes','Recall','RecallReasoan','RecallBy'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {
                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/payments/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push(
                                    {"name": "AccountID", "value": $searchFilter.AccountID},
                                    {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                    {"name": "Status","value": $searchFilter.Status},
                                    {"name": "type","value": $searchFilter.type},
                                    {"name": "paymentmethod","value": $searchFilter.paymentmethod},
                                    {"name": "recall_on_off","value": $searchFilter.recall_on_off},
									{"name": "PaymentDate_StartDate","value": $searchFilter.PaymentDate_StartDate},
									{"name": "PaymentDate_StartTime","value": $searchFilter.PaymentDate_StartTime},
									{"name": "PaymentDate_EndDate","value": $searchFilter.PaymentDate_EndDate},
									{"name": "PaymentDate_EndTime","value": $searchFilter.PaymentDate_EndTime},
									{"name": "CurrencyID","value": $searchFilter.CurrencyID}

                            );
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push(
                                    {"name": "AccountID", "value": $searchFilter.AccountID},
                                    {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                    {"name": "Status","value": $searchFilter.Status},
                                    {"name": "type","value": $searchFilter.type},
                                    {"name": "paymentmethod","value": $searchFilter.paymentmethod},
                                    {"name": "recall_on_off","value": $searchFilter.recall_on_off},
									{"name": "PaymentDate_StartDate","value": $searchFilter.PaymentDate_StartDate},
									{"name": "PaymentDate_StartTime","value": $searchFilter.PaymentDate_StartTime},
									{"name": "PaymentDate_EndDate","value": $searchFilter.PaymentDate_EndDate},
									{"name": "PaymentDate_EndTime","value": $searchFilter.PaymentDate_EndTime},
									{"name": "CurrencyID","value": $searchFilter.CurrencyID},
                                    {"name":"Export","value":1}
                            );

                        },
                        "iDisplayLength": '{{Config::get('app.pageSize')}}',
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[4, 'desc']],
                        "aoColumns": [
                            {
                                "bSortable": true, //Account
                                mRender: function (id, type, full) {
                                    return full[1]
                                }
                            }, //1   CurrencyDescription
                            {
                                "bSortable": true, //Account
                                mRender: function (id, type, full) {
                                    return full[10]
                                }
                            }, //1   CurrencyDescription
                            {
                                "bSortable": true, //Amount
                                mRender: function (id, type, full) {
                                    var a = parseFloat(Math.round(full[3] * 100) / 100).toFixed(2);
                                    a = a.toString();
                                    return full[16]
                                }
                            },
                            {
                                "bSortable": true, //Type
                                mRender: function (id, type, full) {
                                    return full[4]
                                }
                            },

                            {
                                "bSortable": true, //paymentDate
                                mRender: function (id, type, full) {
                                    return full[6]
                                }
                            },
                            {
                                "bSortable": true, //status
                                mRender: function (id, type, full) {
                                    return full[7]
                                }
                            },
                            {
                                "bSortable": true, //Created by
                                mRender: function (id, type, full) {
                                    return full[8]
                                }
                            },
							{
                                "bSortable": true, //Created by
                                mRender: function (id, type, full) {
                                    return full[12]
                                }
                            },
                            {                       //3  Action

                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    var action, edit_, show_, recall_;
                                    var Approve_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/approve')}}";
                                    var Reject_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/reject')}}";
                                    var recall_ = "{{ URL::to('payments/{id}/recall')}}";
                                    Approve_Payment = Approve_Payment.replace('{id}', full[0]);
                                    Reject_Payment = Reject_Payment.replace('{id}', full[0]);
                                    recall_  = recall_ .replace( '{id}', full[0]);
                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '</div>';
                                    action += ' <a data-name = "' + full[0] + '" data-id="' + full[0] + '" class="view-payment btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View </a>';
                                    @if(User::is('BillingAdmin') || User::is_admin())
                                    if(full[7] != "Approved"){
                                        action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Approve/Reject <span class="caret"></span></button>'
                                        action += '<ul class="dropdown-menu dropdown-green" role="menu"><li><a href="' + Approve_Payment+ '" class="approvepayment" >Approve</a></li><li><a href="' + Reject_Payment + '" class="rejectpayment">Reject</a></li></ul></div>';
                                    }
                                    @endif

                                    //action += ' <a data-name = "' + full[0] + '" data-id="' + full[0] + '" class="edit-payment btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                    <?php if(User::checkCategoryPermission('Payments','Recall')) {?>
                                    if(full[13]==0 && full[7]!='Rejected' ){
                                        action += '<a href="'+recall_+'" data-redirect="{{ URL::to('payments')}}"  class="btn recall btn-danger btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Recall </a>';
                                    }
                                    <?php } ?>
                                    if(full[9]!= null){
                                        action += '<span class="col-md-offset-1"><a class="btn btn-success btn-sm btn-icon icon-left"  href="{{URL::to('payments/download_doc')}}/'+full[0]+'" title="" ><i class="entypo-down"></i>Download</a></span>'
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
                                    "sUrl": baseurl + "/payments/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                                    sButtonClass: "save-collection"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/payments/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                                    sButtonClass: "save-collection"
                                }
                            ]
                        },
                        "fnDrawCallback": function () {
							get_total_grand();
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


                    $('table tbody').on('click', '.view-payment', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#view-modal-payment').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){							
                            if(list_fields[i] == 'Amount'){
                                $("#view-modal-payment [name='" + list_fields[i] + "']").text(cur_obj.find("input[name='AmountWithSymbol']").val());
                            }else if(list_fields[i] == 'Currency'){ 							
							var currency_sign_show = currency_signs[cur_obj.find("input[name='" + list_fields[i] + "']").val()];
								if(currency_sign_show!='Select a Currency'){								
									$("#view-modal-payment [name='" + list_fields[i] + "']").text(currency_sign_show);	
								 }else{
									 $("#view-modal-payment [name='" + list_fields[i] + "']").text("Currency Not Found");	
									 }
							}else {
                                $("#view-modal-payment [name='" + list_fields[i] + "']").text(cur_obj.find("input[name='" + list_fields[i] + "']").val());
                            }
                        }

                        $('#view-modal-payment h4').html('View Payment');
                        $('#view-modal-payment').modal('show');
                    });

                    $('table tbody').on('click', '.edit-payment', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();

                        var cur_obj = $(this).siblings("div.hiddenRowData");
                        var select = ['AccountID','PaymentMethod','PaymentType'];
                        for(var i = 0 ; i< list_fields.length; i++){
                            if(select.indexOf(list_fields[i])!=-1){
                                $("#add-edit-payment-form [name='"+list_fields[i]+"']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }else if(list_fields[i] == 'PaymentProof'){

                            }else{
                                $("#add-edit-payment-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }
                        }

                        $('#add-edit-modal-payment h4').html('Edit Payment');
                        $('#add-edit-modal-payment').modal('show');
                    });

                    $('body').on('click', '.btn.recall', function (e) {
                        e.preventDefault();
                        $('#recall-payment-form').trigger("reset");
                        $('#recall-payment-form').attr("action",$(this).attr('href'));
                        $('#recall-modal-payment').modal('show');
                    });

                    $('#recall-payment-form').submit(function(e){
                        e.preventDefault();
                        var formData = new FormData($('#recall-payment-form')[0]);
                        $.ajax({
                            url: $(this).attr("action"),
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $(".btn.save").button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('#recall-modal-payment').modal('hide');
                                    data_table.fnFilter('', 0);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },
                            // Form data
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });

                    $("#payment-status-form").submit(function(e){
                        e.preventDefault();
                        submit_ajax($(this).find("input[name='URL']").val(),$(this).serialize());
                    });

                    $("#payments-upload").click(function(e){
                        e.preventDefault();
                        var url = '{{URL::to('payments/upload/validate_column_mapping')}}';  // 0 Validate maping
                        var formData = new FormData($('#add-template-form')[0]);

                        $.ajax({
                            url:url, //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                                $('.btn.save').button('loading');
                            },
                            success: function(response) {
                                $(".btn.save").button('reset');
                                if (response.status == 'success') {
                                    var ProcessID = response.ProcessID;
                                    if(response.message) {
                                        $('#confirm-modal-payment h4').text('Confirm Payment');
                                        message = response.message.replace(new RegExp('\r\n', 'g'), '<br>');
                                        $('#add-template').find('[name="ProcessID"]').val(ProcessID);
                                        $('#confirm-modal-payment').modal('show');
                                        $('#confirm-payment-form .warnings').html(message);
                                    }else{
                                        $('#add-template').find('[name="ProcessID"]').val(ProcessID);
                                        $("#confirm-payments").click();
                                    }

                                } else {

                                    $('#confirm-modal-payment h4').text('File validation');
                                    message = '<b>Warnings</b><br/>'+ response.message.replace(new RegExp('\r\n', 'g'), '<br>');
                                    $('#add-template').find('[name="ProcessID"]').val(ProcessID);
                                    if(!response.confirmshow){
                                        $('#confirm-payments').addClass('hidden');
                                    }
                                    $('#confirm-modal-payment').modal('show');
                                    $('#confirm-payment-form .warnings').html(message);

                                }
                            },
                            data: formData,
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });


                    $("#confirm-payments").click(function(e){
                        e.preventDefault();
                        var url = '{{URL::to('payments/upload/confirm_bulk_upload')}}';  // Confirm Upload Payment.

                        var formData = new FormData($('#add-template-form')[0]);
                        $.ajax({
                            url:url, //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                                $('.btn.save').button('loading');
                            },
                            success: function(response) {
                                $(".btn.save").button('reset');
                                if (response.status == 'success') {
                                    $('#confirm-modal-payment').modal('hide');
                                    toastr.success(response.message, "Success", toastr_opts);
                                    location.reload();
                                } else {
                                    var message;
                                    if( response.messagestatus == 'Error' ){   // Column maping - File validation

                                        $('#confirm-payments').addClass('hidden');
                                        $('#confirm-modal-payment h4').text('Confirm Payment');
                                        message = '<b>Warnings</b><br/>' + response.message.replace(new RegExp('\n\r', 'g'), '<br>');
                                        var ProcessID = response.ProcessID;
                                        $('#add-template').find('[name="ProcessID"]').val(ProcessID);
                                        $('#confirm-modal-payment').modal('show');
                                        $('#confirm-payment-form [name="warnings"]').html(message);

                                    }else if(response.messagestatus == 'Success' ){   //
                                        $('#confirm-modal-payment').modal('hide');
                                    }else{                                      // Error
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                }
                            },
                            data: formData,
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });

                    $('#confirm-modal-payment').on('hidden.bs.modal', function(event){
                        $('#confirm-payments').removeClass('hidden');
                    });

                    $('.btn.check').click(function(e){
                        e.preventDefault();
                        $('#table-4_processing').removeClass('hidden');
                        var formData = new FormData($('#add-template-form')[0]);
                        $.ajax({
                            url:'{{URL::to('payments/ajaxfilegrid')}}',
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                                $('.btn.check').button('loading');
                            },
                            success: function(response) {
                                $('.btn.check').button('reset');
                                if (response.status == 'success') {
                                    var data = response.data;
                                    createGrid(data);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $('#table-4_processing').addClass('hidden');
                            },
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });

                    $('table tbody').on('click', '.approvepayment , .rejectpayment', function (e) {
                        e.preventDefault();
                        var self = $(this);
                        var text = (self.hasClass("approvepayment")?'Approve':'Reject');
                        if (!confirm('Are you sure you want to '+ text +' the payment?')) {
                            return;
                        }
                        $("#payment-status-form").find("input[name='Notes']").val('');
                        $("#payment-status-form").find("input[name='URL']").val($(this).attr('href'));
                        $("#payment-status").modal('show', {backdrop: 'static'});
                        return false;
                    });

                    $("#add-edit-payment-form [name='AccountID']").change(function(){

                        $("#add-edit-payment-form [name='AccountName']").val( $("#add-edit-payment-form [name='AccountID'] option:selected").text());

                        var AccountID = $("#add-edit-payment-form [name='AccountID'] option:selected").val()

                        if(AccountID > 0 ) {
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

                    $('#add-new-payment').click(function (ev) {
                        ev.preventDefault();
                        $('#add-edit-payment-form').trigger("reset");
                        $("#add-edit-payment-form [name='AccountID']").select2().select2('val','');
                        $("#add-edit-payment-form [name='PaymentMethod']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        $("#add-edit-payment-form [name='PaymentType']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        $("#add-edit-payment-form [name='PaymentID']").val('')
                        $('#add-edit-modal-payment h4').html('Add New Payment');
                        $('.file-input-name').text('');
                        $('#add-edit-modal-payment').modal('show');
                    });

                    $('#add-edit-payment-form').submit(function(e){
                        e.preventDefault();
                        var PaymentID = $("#add-edit-payment-form [name='PaymentID']").val();
                        if( typeof PaymentID != 'undefined' && PaymentID != ''){
                            update_new_url = baseurl + '/payments/'+PaymentID+'/update';
                        }else{
                            update_new_url = baseurl + '/payments/create';
                        }
                        ajax_Add_update(update_new_url);
                    });
                    $("#payment-table-search").submit();

                    $("#form-upload").submit(function (e) {
                        e.preventDefault();
                        //if($('#form-upload').find('select[name="uploadtemplate"]').val()>0){
                        //$("#form-upload").submit();
                        //}else{
                        var PaymentUploadTemplateID = $(this).find('[name="PaymentUploadTemplateID"]').val();
                        $('#add-template').find('[name="PaymentUploadTemplateID"]').val(PaymentUploadTemplateID);
                        var formData = new FormData($('#form-upload')[0]);
                        show_loading_bar(0);
                        $.ajax({
                            url:  '{{URL::to('payments/check_upload')}}',  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                                $('.btn.upload').button('loading');
                                show_loading_bar({
                                    pct: 50,
                                    delay: 5
                                });

                            },
                            afterSend: function(){
                                console.log("Afer Send");
                            },
                            success: function (response) {
                                show_loading_bar({
                                    pct: 100,
                                    delay: 2
                                });

                                if (response.status == 'success') {
                                    $('#upload-modal-payments').modal('hide');
                                    var data = response.data;
                                    createGrid(data);
                                    $('#add-template').removeClass('hidden');
                                    var scrollTo = $('#add-template').offset().top;
                                    $('html, body').animate({scrollTop:scrollTo}, 1000);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                //alert(response.message);
                                $('.btn.upload').button('reset');
                            },
                            // Form data
                            data: formData,
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                        //}
                    });

                    function createGrid(data){
                        var tr = $('#tablemapping thead tr');
                        var body = $('#tablemapping tbody');
                        tr.empty();
                        body.empty();
                        $.each( data.columns, function( key, value ) {
                            tr.append('<th>'+value+'</th>');
                        });

                        $.each( data.rows, function(key, row) {
                            var tr = '<tr>';
                            $.each( row, function(key, item) {
                                if(typeof item == 'object' && item != null ){
                                    tr+='<td>'+item.date+'</td>';
                                }else{
                                    tr+='<td>'+(!item?'':item)+'</td>';
                                }
                            });
                            tr += '</tr>';
                            body.append(tr);
                        });
                        $("#mapping select").each(function(i, el){
                            if(el.name !='selection[DateFormat]'){
                                $(el).data("selectBox-selectBoxIt").remove();
                                $(el).data("selectBox-selectBoxIt").add({ value: '', text: 'Skip loading' });
                                $.each(data.columns,function(key,value){
                                    $(el).data("selectBox-selectBoxIt").add({ value: key, text: value });
                                });
                            }
                        });
                        if ( data.PaymentUploadTemplate ) {
                            $.each( data.PaymentUploadTemplate, function( optionskey, option_value ) {
                                if(optionskey == 'Title'){
                                    $('#add-template-form').find('[name="TemplateName"]').val(option_value)
                                }
                                if(optionskey == 'Options'){
                                    $.each( option_value.option, function( key, value ) {

                                        if(typeof $("#add-template-form [name='option["+key+"]']").val() != 'undefined'){
                                            $('#add-template-form').find('[name="option['+key+']"]').val(value)
                                            if(key == 'Firstrow'){
                                                $("#add-template-form [name='option["+key+"]']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(value);
                                            }
                                        }

                                    });
                                    $.each( option_value.selection, function( key, value ) {
                                        if(typeof $("#add-template-form input[name='selection["+key+"]']").val() != 'undefined'){
                                            $('#add-template-form').find('input[name="selection['+key+']"]').val(value)
                                        }else if(typeof $("#add-template-form select[name='selection["+key+"]']").val() != 'undefined'){
                                            $("#add-template-form [name='selection["+key+"]']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(value);
                                        }
                                    });
                                }
                            });
                        }

                        $('#add-template-form').find('[name="TemplateFile"]').val(data.filename);
                        $('#add-template-form').find('[name="TempFileName"]').val(data.tempfilename);
                    }
					
			function get_total_grand()
			{
				$('.total_ajax').remove();
			 $.ajax({
					url: baseurl + "/payments/ajax_datagrid_total",
					type: 'GET',
					dataType: 'json',
					data:{
				"AccountID":$("#payment-table-search select[name='AccountID']").val(),
				"InvoiceNo":$("#payment-table-search input[name='InvoiceNo']").val(),
				"Status":$("#payment-table-search select[name='Status']").val(),
				"type":$("#payment-table-search select[name='type']").val(),				
				"paymentmethod":$("#payment-table-search select[name='paymentmethod']").val(),
				"PaymentDate_StartDate":$("#payment-table-search input[name='PaymentDate_StartDate']").val(),
				"PaymentDate_StartTime":$("#payment-table-search input[name='PaymentDate_StartTime']").val(),
				"PaymentDate_EndDate":$("#payment-table-search input[name='PaymentDate_EndDate']").val(),
				"PaymentDate_EndTime":$("#payment-table-search input[name='PaymentDate_EndTime']").val(),
				"CurrencyID":$("#payment-table-search select[name='CurrencyID']").val(),	
				"recall_on_off":$searchFilter.recall_on_off = $("#payment-table-search [name='recall_on_off']").prop("checked"),				
				"bDestroy": true,
				"bProcessing":true,
				"bServerSide":true,
				"sAjaxSource": baseurl + "/payments/ajax_datagrid_total",
				"iDisplayLength": '{{Config::get('app.pageSize')}}',
				"sPaginationType": "bootstrap",
				"sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
				"aaSorting": [[4, 'desc']],},
					success: function(response1) {
						console.log("sum of result"+response1);
						
						if(response1.total_grand!=null)
						{ 
							$('#table-4 tbody').append('<tr class="total_ajax"><td colspan="2"><strong>Total</strong></td><td><strong>'+response1.total_grand+'</strong></td><td colspan="6"></td></tr>');	
						}
						
	
						},
				});	
		}


                });

                $("#payment-table-search").submit(function(e) {
                    e.preventDefault();
                    public_vars.$body = $("body");
                    //show_loading_bar(40);
                    $searchFilter.AccountID = $("#payment-table-search select[name='AccountID']").val();
                    $searchFilter.InvoiceNo = $("#payment-table-search [name='InvoiceNo']").val();
                    $searchFilter.Status = $("#payment-table-search select[name='Status']").val();
                    $searchFilter.type = $("#payment-table-search select[name='type']").val();
                    $searchFilter.paymentmethod = $("#payment-table-search select[name='paymentmethod']").val();
					$searchFilter.PaymentDate_StartDate = $("#payment-table-search input[name='PaymentDate_StartDate']").val();
					$searchFilter.PaymentDate_StartTime = $("#payment-table-search input[name='PaymentDate_StartTime']").val();
					$searchFilter.PaymentDate_EndDate   = $("#payment-table-search input[name='PaymentDate_EndDate']").val();
					$searchFilter.PaymentDate_EndTime   = $("#payment-table-search input[name='PaymentDate_EndTime']").val();					
					$searchFilter.CurrencyID 			= $("#payment-table-search select[name='CurrencyID']").val();
                    if($("#payment-table-search select[name='recall_on_off']")) {
                        $searchFilter.recall_on_off = $("#payment-table-search [name='recall_on_off']").prop("checked");
                    }else{
                        $searchFilter.recall_on_off = 0;
                    }
                    data_table.fnFilter('', 0);
                    return false;
                });

                function ajax_Add_update(fullurl){
                    var data = new FormData($('#add-edit-payment-form')[0]);
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
                            $("#payment-update").button('reset');
                            $(".btn").button('reset');
                            $('#modal-payment').modal('hide');

                            if (response.status == 'success') {
                                $('#add-edit-modal-payment').modal('hide');
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
                    var $label = $('#add-edit-payment-form [for="PaymentProof"]');
                    $label.parents('.form-group').find('a,span').remove();
                    $label.parents('.form-group').find('div').append('<input id="PaymentProof" name="PaymentProof" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class=\'glyphicon glyphicon-circle-arrow-up\'></i>&nbsp;   Browse" />');
                    var $this = $('#PaymentProof');
                    var label = attrDefault($this, 'label', 'Browse');
                    $this.bootstrapFileInput(label);
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

@stop
@section('footer_ext')
    @parent
<div class="modal fade" id="add-edit-modal-payment">
  <div class="modal-dialog">
    <div class="modal-content">
    <form id="add-edit-payment-form" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Add New payment Request</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Account Name * <span id="currency"></span></label>
              {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
              <input type="hidden" name="AccountName" />
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Payment Date *</label>
              <input type="text" name="PaymentDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" id="field-5" placeholder="">
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Payment Method *</label>
              {{ Form::select('PaymentMethod', Payment::$method, '', array("class"=>"selectboxit")) }} </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Action *</label>
              {{ Form::select('PaymentType', Payment::$action, '', array("class"=>"selectboxit","id"=>"PaymentTypeAuto")) }} </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Amount *</label>
              <input type="text" name="Amount" class="form-control" id="field-5" placeholder="">
              <input type="hidden" name="PaymentID" >
              <input type="hidden" name="Currency" >
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Invoice</label>
              <input type="text" id="InvoiceAuto" name="InvoiceNo" class="form-control" id="field-5" placeholder="">
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Notes</label>
              <textarea name="Notes" class="form-control" id="field-5" placeholder=""></textarea>
              <input type="hidden" name="PaymentID" >
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="PaymentProof" class="control-label">Upload (.pdf, .jpg, .png, .gif)</label>
                <div class="clear clearfix"></div>
                <input id="PaymentProof" name="PaymentProof" type="file" class="form-control file2 inline btn btn-primary" data-label="
                        <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" id="payment-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
      </div>
      </div>
    </form>
  </div>
</div>
</div>
<div class="modal fade" id="view-modal-payment">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">View Payment</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Account Name</label>
              <div class="col-sm-12" name="AccountName"></div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Currency</label>
              <div class="col-sm-12" name="Currency"></div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Invoice</label>
              <div class="col-sm-12" name="InvoiceNo"></div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Payment Date</label>
              <div class="col-sm-12" name="PaymentDate"></div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Payment Method</label>
              <div class="col-sm-12" name="PaymentMethod"></div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Action</label>
              <div class="col-sm-12" name="PaymentType"></div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Amount</label>
              <div class="col-sm-12" name="Amount"></div>
              <input type="hidden" name="PaymentID" >
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Notes</label>
              <div class="col-sm-12" name="Notes"></div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Recall Reasoan</label>
              <div class="col-sm-12" name="RecallReasoan"></div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-5" class="control-label">Recall By</label>
              <div class="col-sm-12" name="RecallBy"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade in" id="payment-status">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="payment-status-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Payment Notes</h4>
        </div>
        <div class="modal-body">
          <div id="text-boxes" class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Notes</label>
                <input type="text" name="Notes" class="form-control"  value="" />
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="payment-status" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
          <i class="entypo-floppy"></i>
          <input type="hidden" name="URL" value="">
          Save
          </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="modal-fileformat">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Payment File Format</h4>
      </div>
      <div class="modal-body">
        <p>All columns are mandatory except Invoice and Note and the first line should have the column headings.</p>
        <table class="table responsive">
          <thead>
            <tr>
              <th class="hide_country">Account Name</th>
              <th>Payment Date</th>
              <th>Payment Method</th>
              <th>Action</th>
              <th>Amount</th>
              <th>Invoice(opt)</th>
              <th>Note(opt)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="hide_country">abc</td>
              <td>2013-05-21</td>
              <td><span data-original-title="Payment Method" data-content="CASH, PAYPAL, CHEQUE, CREDIT CARD, BANK TRANSFER" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
              <td>Payment In</td>
              <td>500.00</td>
              <td>INV-1</td>
              <td>NOTE</td>
            </tr>
            <tr>
              <td class="hide_country">abc</td>
              <td>2013-05-21</td>
              <td><span data-original-title="Payment Method" data-content="CASH, PAYPAL, CHEQUE, CREDIT CARD, BANK TRANSFER" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
              <td>Payment Out</td>
              <td>500.00</td>
              <td>INV-2</td>
              <td>NOTE</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="upload-modal-payments" >
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" id="form-upload" method="post" action="{{URL::to('payments/upload')}}"
                      class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Upload Payments</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="field-1" class="col-sm-3 control-label">Upload Template</label>
            <div class="col-sm-5"> {{ Form::select('PaymentUploadTemplateID', $PaymentUploadTemplates, '' , array("class"=>"select2")) }} </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">File Select</label>
            <div class="col-sm-5">
              <input type="file" id="excel" type="file" name="excel" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Note</label>
            <div class="col-sm-5">
              <p>Allowed Extension .xls, .xlxs, .csv</p>
              <p>Please upload the file in given <span style="cursor: pointer" onclick="jQuery('#modal-fileformat').modal('show');jQuery('#modal-fileformat').css('z-index',1999)" class="label label-info">Format</span></p>
              <p>Sample File <a class="btn btn-success btn-sm btn-icon icon-left" href="{{URL::to('payments/download_sample_excel_file')}}"><i class="entypo-down"></i>Download</a></p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="codedeck-update"  class="btn upload btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-upload"></i> Upload </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="recall-modal-payment">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="recall-payment-form" action="{{URL::to('payments/recall')}}" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Recall Payment</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="form-Group"> <br />
              <label for="field-1" class="col-sm-12 control-label">Recall Reason</label>
              <div class="col-sm-12">
                <textarea class="form-control message" name="RecallReasoan"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="payment-recall"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Recall </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="confirm-modal-payment">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="confirm-payment-form" action="" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Confirm Payment</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group warnings"> </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="confirm-payments"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Confirm </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop