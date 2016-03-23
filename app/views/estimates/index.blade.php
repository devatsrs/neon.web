@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Estimate</strong> </li>
</ol>
<h3>Estimate</h3>
@include('includes.errors')
@include('includes.success')
<p style="text-align: right;"> @if(User::checkCategoryPermission('Invoice','Add')) <a href="{{URL::to("estimate/create")}}" id="add-new-estimate" class="btn btn-primary "> <i class="entypo-plus"></i> Add New Estimate </a> @endif
  <!-- <a href="javascript:;" id="bulk-estimate" class="btn upload btn-primary ">
        <i class="entypo-upload"></i>
        Bulk Estimate Generate.
    </a>--> 
</p>
<div class="row">
  <div class="col-md-12">
    <form id="estimate_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title"> Filter </div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Account</label>
            <div class="col-sm-2"> {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }} </div>
            <label for="field-1" class="col-sm-2 control-label">Estimate Status</label>
            <div class="col-sm-2"> {{ Form::select('EstimateStatus', Estimate::get_estimate_status(), '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }} </div>
                   <label for="field-1" class="col-sm-2 control-label">Currency</label>
                     <div class="col-sm-2">
                     {{Form::select('CurrencyID',Currency::getCurrencyDropdownIDList(),$DefaultCurrencyID,array("class"=>"select2"))}}
                      </div> 
          </div>
          <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Estimate Number</label>
            <div class="col-sm-2"> {{ Form::text('EstimateNumber', '', array("class"=>"form-control")) }} </div>
            <label for="field-1" class="col-sm-2 control-label">Issue Date Start</label>
            <div class="col-sm-2"> {{ Form::text('IssueDateStart', '', array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}<!-- Time formate Updated by Abubakar --> 
            </div>
            <label for="field-1" class="col-sm-2 control-label">Issue Date End</label>
            <div class="col-sm-2"> {{ Form::text('IssueDateEnd', '', array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }} </div>
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
    <div class="input-group-btn pull-right" style="width:70px;"> 
      
     
      @if( User::checkCategoryPermission('Invoice','Edit,Send,Generate,Email'))
      <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
      <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #1f232a; border-color: #1f232a; margin-top:0px;">
        @if(User::checkCategoryPermission('Invoice','Edit'))
        <li> <a class="delete_bulk" id="delete_bulk" href="javascript:;" > Delete </a> </li>
        @endif       
        @if(User::checkCategoryPermission('Invoice','Edit'))
        <li> <a class="convert_invoice" id="convert_invoice" href="javascript:;" >Accept and generate invoice</a> </li>
        @endif
      </ul>
      @endif
      <form id="clear-bulk-rate-form" >
        <input type="hidden" name="CustomerRateIDs" value="">
      </form>
    </div>
    <!-- /btn-group --> 
  </div>
  <div class="clear"></div>
</div>
<br>
<table class="table table-bordered datatable" id="table-4">
  <thead>
    <tr>
      <th width="5%"><div class="pull-left">
          <input type="checkbox" id="selectall" name="checkbox[]" class="" />
        </div></th>
      <th width="20%">Account Name</th>
      <th width="10%">Estimate Number</th>
      <th width="15%">Issue Date</th>
      <th width="10%">Grand Total</th>
      <th width="10%">Estimate Status</th>
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
                   
					},
			});	
		
			//////////////////////////////////////////
			
		});
		
        public_vars.$body = $("body");
        //show_loading_bar(40); 
		var base_url_estimate 		= 	"{{ URL::to('estimate')}}";
        var estimatestatus 			=	{{$estimate_status_json}};
        var estimate_Status_Url 	= 	"{{ URL::to('estimate/estimate_change_Status')}}";
		var delete_url_bulk 		= 	"{{ URL::to('estimate/estimate_delete_bulk')}}";
        var list_fields  			= 	['AccountName','EstimateNumber','IssueDate','GrandTotal','EstimateStatus','EstimateID','Description','Attachment','AccountID','BillingEmail'];
		
        $searchFilter.AccountID 			= 	$("#estimate_filter select[name='AccountID']").val();
        $searchFilter.EstimateStatus 		= 	$("#estimate_filter select[name='EstimateStatus']").val();
        $searchFilter.EstimateNumber 		= 	$("#estimate_filter [name='EstimateNumber']").val();
        $searchFilter.IssueDateStart 		= 	$("#estimate_filter [name='IssueDateStart']").val();
        $searchFilter.IssueDateEnd 			= 	$("#estimate_filter [name='IssueDateEnd']").val();	
		$searchFilter.CurrencyID            =   $("#estimate_filter [name='CurrencyID']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/estimate/ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[3, 'desc']],
             "fnServerParams": function(aoData) {				
                aoData.push({"name":"EstimateType","value":$searchFilter.EstimateType},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"EstimateNumber","value":$searchFilter.EstimateNumber},{"name":"EstimateStatus","value":$searchFilter.EstimateStatus},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{"name":"CurrencyID","value":$searchFilter.CurrencyID});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"EstimateType","value":$searchFilter.EstimateType},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"EstimateNumber","value":$searchFilter.EstimateNumber},{"name":"EstimateStatus","value":$searchFilter.EstimateStatus},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{ "name": "Export", "value": 1});
            },
             "aoColumns":
            [
                {  "bSortable": false,
                                mRender: function ( id, type, full ) {									
                                     var action , action = '<div class = "hiddenRowData" >';    
                                      //if (full[4] != 'accepted')
									  {
                                        action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="'+full[5]+'" name="EstimateID[]"></div>';
                                      }
									  action += '</div>';
                                        return action;
                                     }

                                    },  // 0 AccountName
                {  "bSortable": true,

                mRender:function( id, type, full){
                                        var output , account_url;
										
                                        output = '<a href="{url}" target="_blank" >{account_name}';
                                        if(full[9] =='')
										{
                                        	output+= '<br> <span class="text-danger"><small>(Email not setup)</small></span>';
                                        }
                                        output+= '</a>';
                                        account_url = baseurl + "/accounts/"+ full[8] + "/show";
                                        output = output.replace("{url}",account_url);
                                        output = output.replace("{account_name}",full[0]);
                                        return output;
                                     }

                },  // 1 EstimateNumber
                {  "bSortable": true,

                mRender:function( id, type, full){
                                                        var output , account_url;
                                                        output = '<a href="{url}" target="_blank"> ' +full[1] + '</a>';
                                                        account_url = baseurl + "/estimate/"+ full[5] + "/estimate_preview";
                                                        output = output.replace("{url}",account_url);
                                                        //output = output.replace("{account_name}",full[1]);
                                                        return output;
                                                     }

                },  // 2 IssueDate
                {  "bSortable": true,

                mRender:function( id, type, full){
                                                        var output = full[2];
                                                        return output;
                                                     } },  // 3 IssueDate
                {  "bSortable": true,

                mRender:function( id, type, full){
                                                        var output = full[3];
                                                        return output;
                                                     } },  // 4 GrandTotal
                {  "bSortable": true,
                    mRender:function( id, type, full){
                        return  estimatestatus[full[4]]; 
                    }

                },  // 5 EstimateStatus
                {
                   "bSortable": false,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_,view_url,edit_url,download_url,estimate_preview,delete_url;
                         
							action 				= 	'<div class = "hiddenRowData" >';
                            edit_url 			= 	(baseurl + "/estimate/{id}/edit").replace("{id}",full[5]);
							delete_url 			= 	(baseurl + "/estimate/{id}/delete").replace("{id}",full[5]);
                            estimate_preview	= 	(baseurl + "/estimate/{id}/estimate_preview").replace("{id}",full[5]);
                        

                         for(var i = 0 ; i< list_fields.length; i++)
						 {
                            action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                         }
						 
                         action += '</div>';

                          /*Multiple Dropdown*/              			
                            action += '<div class="btn-group">';
                            action += '<a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">Action<span class="caret"></span></a>';
                            action += '<ul class="dropdown-menu multi-level dropdown-menu-left" role="menu" aria-labelledby="dropdownMenu">';

                                if('{{User::checkCategoryPermission('Invoice','Edit')}}')
								{
									//if(full[4] != 'accepted')
									{
                                        action += ' <li><a class="icon-left"  href="' + (baseurl + "/estimate/{id}/edit").replace("{id}",full[5]) +'"><i class="entypo-pencil"></i>Edit </a></li>';
									}
										
                                }
                          
							
                            if (estimate_preview)
							{                                
                                action += '<li><a class="icon-left"  target="_blank" href="' + estimate_preview +'"><i class="entypo-pencil"></i>View </a></li>';
                            }
							
							if ('{{User::checkCategoryPermission('Invoice','Edit')}}' && delete_url)
							{     
								//if(full[4] != 'accepted')
								{                           
                                	action += '<li><a class="icon-left delete_link"  target="_blank" href="' + delete_url +'"><i class="entypo-cancel"></i>Delete</a></li>';				}
                            }
                            
							//if(full[11]== 'N')
							{
	                                   action += ' <li><a class="icon-left send_estimate"  estimate="'+full[5]+'"><i class="entypo-mail"></i>Send</a></li>';    
											action += ' <li><a class="icon-left convert_estimate"  estimate="'+full[5]+'"><i class="entypo-check"></i>Accept and generate invoice</a></li>';
						}
                            

                            action += '</ul>';
                            action += '</div>';
							
							//if(full[4] != 'accepted')
							{
                             action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Change Status <span class="caret"></span></button>'
                             action += '<ul class="dropdown-menu dropdown-green" role="menu">';
                             $.each(estimatestatus, function( index, value ) {
                                 
                                     action +='<li><a data-estimatestatus="' + index+ '" data-estimateid="' + full[5]+ '" href="' + estimate_Status_Url+ '" class="changestatus" >'+value+'</a></li>';
                                 

                             });
							 
                             action += '</ul>' +
                             '</div>';
							}
                       
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": baseurl + "/estimate/ajax_datagrid", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
           "fnDrawCallback": function() {
			  get_total_grand();
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
                   //After Delete done
                   FnDeleteEstimateTemplateSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteEstimateTemplate = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/estimate/"+id+"/delete" ,"",FnDeleteEstimateTemplateSuccess );
                       }
                       return false;
                   }
                   $(".delete-estimate").click(FnDeleteEstimateTemplate); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
               $('#selectallbutton').click(function(ev) {
                   if($(this).is(':checked')){
                       checked = 'checked=checked disabled';
                       $("#selectall").prop("checked", true).prop('disabled', true);
                       if(!$('#changeSelectedEstimate').hasClass('hidden')){
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
                       if(!$('#changeSelectedEstimate').hasClass('hidden')){
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

        $("#estimate_filter").submit(function(e){
            e.preventDefault();
            $searchFilter.AccountID 		= 	$("#estimate_filter select[name='AccountID']").val();
            $searchFilter.EstimateNumber 	= 	$("#estimate_filter [name='EstimateNumber']").val();
            $searchFilter.EstimateStatus 	= 	$("#estimate_filter select[name='EstimateStatus']").val();
            $searchFilter.IssueDateStart 	= 	$("#estimate_filter [name='IssueDateStart']").val();
            $searchFilter.IssueDateEnd 		= 	$("#estimate_filter [name='IssueDateEnd']").val();			
			$searchFilter.CurrencyID 		= 	$("#estimate_filter [name='CurrencyID']").val();			
            data_table.fnFilter('', 0);
			//get_total_grand();
            return false;
        });
		
		function get_total_grand()
		{
			 $.ajax({
                url: baseurl + "/estimate/ajax_datagrid_total",
                type: 'GET',
                dataType: 'json',
				data:{
			"AccountID":$("#estimate_filter select[name='AccountID']").val(),
			"EstimateNumber":$("#estimate_filter [name='EstimateNumber']").val(),
			"EstimateStatus":$("#estimate_filter select[name='EstimateStatus']").val(),
			"IssueDateStart":$("#estimate_filter [name='IssueDateStart']").val(),
			"IssueDateEnd":$("#estimate_filter [name='IssueDateEnd']").val(),
			"CurrencyID":$("#estimate_filter [name='CurrencyID']").val(),
			"bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/estimate/ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[3, 'desc']],},
                success: function(response1) {
					console.log("sum of result"+response1);
					
					if(response1.total_grand!=null)
					{ 
						var selected_currency = $("#estimate_filter [name='CurrencyID']").val();
						var concat_currency   = '';
						if(selected_currency!='')
						{	
							var currency_txt =   $('#table-4 tbody tr').eq(0).find('td').eq(4).html();						
							var concat_currency = currency_txt.substr(0,1);
							//concat_currency  =    $("#estimate_filter [name='CurrencyID'] option:selected").text()+' ';		
						}
						$('#table-4 tbody').append('<tr><td><strong>Total</strong></td><td align="right" colspan="3"></td><td><strong>'+concat_currency+response1.total_grand+'</strong></td><td colspan="2"></td></tr>');	
					}
					

					},
			});	
		}

         $('#estimate-in').click(function(ev){
                ev.preventDefault();
                $('#add-estimate_in_template-form').trigger("reset");
                $('#modal-estimate-in h4').html('Add Estimate');
                $("#add-estimate_in_template-form [name='AccountID']").select2().select2('val','');
                $("#add-estimate_in_template-form [name='EstimateID']").val('');
                $('#modal-estimate-in').modal('show');
        });
         $("#add-estimate_in_template-form [name='AccountID']").change(function(){
            $("#add-estimate_in_template-form [name='AccountName']").val( $("#add-estimate_in_template-form [name='AccountID'] option:selected").text());
            var url = baseurl + '/payments/getcurrency/'+$("#add-estimate_in_template-form [name='AccountID'] option:selected").val();
            $.get( url, function( Currency ) {
                $("#currency").text('('+Currency+')');
                $("#add-estimate_in_template-form [name='Currency']").val(Currency);
            });
        });
        $("#add-estimate_in_template-form").submit(function(e){
            e.preventDefault();
            var formData = new FormData($('#add-estimate_in_template-form')[0]);
             var EstimateID = $("#add-estimate_in_template-form [name='EstimateID']").val()
            if( typeof EstimateID != 'undefined' && EstimateID != ''){
                update_new_url = baseurl + '/estimate/update_estimate_in/'+EstimateID;
            }else{
                update_new_url = baseurl + '/estimate/add_estimate_in';
            }
            submit_ajax_withfile(update_new_url,formData)
       });
        $('table tbody').on('click', '.edit-estimate-in', function (ev) {
            $('#add-estimate_in_template-form').trigger("reset");
            $('#modal-estimate-in h4').html('Edit Estimate');
            //var cur_obj = $(this).prev("div.hiddenRowData");
             var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");
             EstimateID = cur_obj.find("input[name='EstimateID']").val();
             $.ajax({
                 url: baseurl + '/estimate/getEstimateDetail',
                 data: 'EstimateID='+EstimateID,
                 dataType: 'json',
                 success: function (response) {
                     $("#add-estimate_in_template-form [name='StartDate']").val(response.StartDate);
                     $("#add-estimate_in_template-form [name='StartTime']").val(response.StartTime);
                     $("#add-estimate_in_template-form [name='EndDate']").val(response.EndDate);
                     $("#add-estimate_in_template-form [name='EndTime']").val(response.EndTime);
                     $("#add-estimate_in_template-form [name='Description']").val(response.Description);
                     $("#add-estimate_in_template-form [name='EstimateDetailID']").val(response.EstimateDetailID);
                 },
                 type: 'POST'
             });
            for(var i = 0 ; i< list_fields.length; i++){
                if(list_fields[i] != 'Attachment'){
                    $("#add-estimate_in_template-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    //$("#add-estimate_in_template-form .file-input-name").text(cur_obj.find("input[name='Attachment']").val());
                    if(list_fields[i] == 'AccountID'){
                        $("#add-estimate_in_template-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }
                }
            }
             $('#modal-estimate-in').modal('show');
        });
        $('table tbody').on('click', '.view-estimate-in', function (ev) {
            //var cur_obj = $(this).prev().prev("div.hiddenRowData");
              var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");
            for(var i = 0 ; i< list_fields.length; i++){
            $("#modal-estimate-in-view").find("[data-id='"+list_fields[i]+"']").html('');
                if(list_fields[i] == 'Attachment'){
                    if(cur_obj.find("input[name='"+list_fields[i]+"']").val() != ''){
                        var down_html = ' <a href="' + baseurl +'/estimate/download_doc_file/'+cur_obj.find("input[name='EstimateID']").val() +'" class="edit-estimate btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>'
                        $("#modal-estimate-in-view").find("[data-id='"+list_fields[i]+"']").html(down_html);
                    }
                }else{
                    $("#modal-estimate-in-view").find("[data-id='"+list_fields[i]+"']").html(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                }
            }
            $('#modal-estimate-in-view').modal('show');
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
            if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
            $(this).toggleClass('selected');
            if ($(this).hasClass('selected')) {
                $(this).find('.rowcheckbox').prop("checked", true);
            } else {
                $(this).find('.rowcheckbox').prop("checked", false);
            }
            }
        });
		
		$('#convert_invoice').click(function(e) {			
	        e.preventDefault();
            var self = $(this);
            var text = self.text();
			
			var EstimateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                EstimateID = $(this).val();
                if(typeof EstimateID != 'undefined' && EstimateID != null && EstimateID != 'null'){
                    EstimateIDs[i++] = EstimateID;
                }
            });
			var all_chceked = 0;
			if($('#selectallbutton').is(':checked')){
				all_chceked=1;
			}
			
			if(EstimateIDs.length<1)
			{
				alert("Please select atleast one estimate.");
				return false;
			}
            console.log(EstimateIDs);
			
            if (!confirm('Are you sure you to change status of selected estimates ?')) {
                return;
            }

            $.ajax({
                url: estimate_Status_Url+'_Bulk',
                type: 'POST',
                dataType: 'json',
				data:{
				'EstimateIDs':EstimateIDs,					
				"AccountID":$("#estimate_filter select[name='AccountID']").val(),
				"EstimateNumber":$("#estimate_filter [name='EstimateNumber']").val(),
				"EstimateStatus":$("#estimate_filter select[name='EstimateStatus']").val(),
				"IssueDateStart":$("#estimate_filter [name='IssueDateStart']").val(),
				"IssueDateEnd":$("#estimate_filter [name='IssueDateEnd']").val(),
				"CurrencyID":$("#estimate_filter [name='CurrencyID']").val(),
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
		
	   $(document).on( 'click', '.send_estimate', function(e){			
			  estimate_id = $(this).attr('estimate');
        $('#send-modal-estimate').find(".modal-body").html("Loading Content...");
        var ajaxurl = "/estimate/"+estimate_id+"/estimate_email";
        showAjaxModal(ajaxurl,'send-modal-estimate');
        $("#send-estimate-form")[0].reset();
        $('#send-modal-estimate').modal('show');
    });
	
	$(document).on( 'click', '.convert_estimate', function(e){			
			  estimate_id = $(this).attr('estimate');
       
        var ajaxurl_convert = base_url_estimate+"/"+estimate_id+"/convert_estimate";
		
		   $.ajax({
			url: ajaxurl_convert,
			type: 'POST',
			dataType: 'json',
			data:{'eid':estimate_id,'convert':1},
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
    });
	
	
		
		$('#delete_bulk').click(function(e) {
			
	        e.preventDefault();
            var self = $(this);
            var text = self.text();
			
			var EstimateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                EstimateID = $(this).val();
                if(typeof EstimateID != 'undefined' && EstimateID != null && EstimateID != 'null'){
                    EstimateIDs[i++] = EstimateID;
                }
            });
			
			if(EstimateIDs.length<1)
			{
				alert("Please select atleast one estimate.");
				return false;
			}
            console.log(EstimateIDs);
			
            if (!confirm('Are you sure to delete selected estimates?')) {
                return;
            }

            $.ajax({
                url: delete_url_bulk,
                type: 'POST',
                dataType: 'json',
				data:'del_ids='+EstimateIDs,
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
		
        $("#changeSelectedEstimate").click(function(ev) {
            var criteria='';
            if($('#selectallbutton').is(':checked')){
                 criteria = JSON.stringify($searchFilter);
            }
            var EstimateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                //console.log($(this).val());
                EstimateID = $(this).val();
                if(typeof EstimateID != 'undefined' && EstimateID != null && EstimateID != 'null'){
                    EstimateIDs[i++] = EstimateID;
                }
                
				if(EstimateIDs.length)
				{
                    $("#selected-estimate-status-form").find("input[name='EstimateIDs']").val(EstimateIDs.join(","));
                    $("#selected-estimate-status-form").find("input[name='criteria']").val(criteria);
                    $('#selected-estimate-status').modal('show');
                    $("#selected-estimate-status-form [name='EstimateStatus']").select2().select2('val','');
                    $("#selected-estimate-status-form [name='CancelReason']").val('');
                    $('#statuscancel').hide();
                }
            });
        });

        $("#selected-estimate-status-form").submit(function(e){
            e.preventDefault();
            var EstimateStatus = $(this).find("select[name='EstimateStatus']").val();

            if(EstimateStatus != '')
            {
                    formData = $("#selected-estimate-status-form").serialize();
                    update_new_url = baseurl +'/estimate/estimate_change_Status';
                    submit_ajax(update_new_url,formData)
               
            }else{
            toastr.error("Please Select Estimates Status", "Error", toastr_opts);
            $(this).find(".cancelbutton]").button("reset");
            return false;
            }

       });
       $("#selected-estimate-status-form [name='EstimateStatus']").change(function(e){
            e.preventDefault();
            $('#statuscancel').hide();
            var status = $(this).val();
       });

       $("#estimate-status-cancel-form").submit(function(e){
           e.preventDefault();
           if($(this).find("input[name='CancelReason']").val().trim() != ''){
                submit_ajax(estimate_Status_Url,$(this).serialize())
           }
       });
       $('table tbody').on('click', '.changestatus', function (e) {
            e.preventDefault();
            var self = $(this);
            var text = self.text();
            if (!confirm('Are you sure you want to change the estimate status to '+ text +'?')) {
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
                data:'EstimateStatus='+$(this).attr('data-estimatestatus')+'&EstimateIDs='+$(this).attr('data-estimateid')

            });
            return false;
        });

        $('table tbody').on('click', '.send-estimate', function (ev) {
            //var cur_obj = $(this).prevAll("div.hiddenRowData");
            var cur_obj 	= 	$(this).parent().parent().parent().parent().find("div.hiddenRowData");
            EstimateID 		= 	cur_obj.find("[name=EstimateID]").val();
            send_url 		=  	("/estimate/{id}/estimate_email").replace("{id}",EstimateID);
            console.log(send_url)
            showAjaxModal( send_url ,'send-modal-estimate');
            $('#send-modal-estimate').modal('show');
        });

        $("#send-estimate-form").submit(function(e){
            e.preventDefault();
            var post_data  = $(this).serialize();
            var EstimateID = $(this).find("[name=EstimateID]").val();
            var _url = baseurl + '/estimate/'+EstimateID+'/send';
            submit_ajax(_url,post_data);
        });

        $("#bulk-estimate-send").click(function(ev) {
            var criteria='';
            if($('#selectallbutton').is(':checked')){
                 criteria = JSON.stringify($searchFilter);
            }
            var EstimateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
				//console.log($(this).val());
                EstimateID = $(this).val();
                if(typeof EstimateID != 'undefined' && EstimateID != null && EstimateID != 'null'){
                    EstimateIDs[i++] = EstimateID;
                }
            });
            console.log(EstimateIDs);

            if(EstimateIDs.length){
                if (!confirm('Are you sure you want to send selected Estimates?')) {
                    return;
                }
                $.ajax({
                    url: baseurl + '/estimate/bulk_send_estimate_mail',
                    data: 'EstimateIDs='+EstimateIDs+'&criteria='+criteria,
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
                toastr.error('Please select sample estimate', "Error", toastr_opts);
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
<link rel="stylesheet" href="assets/js/wysihtml5/bootstrap-wysihtml5.css">
<script src="assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script> 
<script src="assets/js/wysihtml5/bootstrap-wysihtml5.js"></script> 
@stop
@section('footer_ext')
@parent 
<!-- Job Modal  (Ajax Modal)-->
<div class="modal fade custom-width" id="print-modal-estimate">
  <div class="modal-dialog" style="width: 60%;">
    <div class="modal-content">
      <form id="add-new-estimate_template-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
        <div class="modal-header">
          <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
          <h4 class="modal-title"> <a class="btn btn-primary print btn-sm btn-icon icon-left" href=""> <i class="entypo-print"></i> Print </a> </h4>
        </div>
        <div class="modal-body"> Content is loading... </div>
        <div class="modal-footer">
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade custom-width" id="modal-estimate-bulk">
  <div class="modal-dialog" style="width: 60%;">
    <div class="modal-content">
      <form id="add-bulk-estimate_template-form" method="post" class="form-horizontal form-groups-bordered">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title">Generate Estimate</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Start Date</label>
            <div class="col-sm-2">
              <input type="text" name="StartDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" value="" data-enddate="2016-01-10">
            </div>
            <div class="col-sm-2">
              <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">End Date</label>
            <div class="col-sm-2">
              <input type="text" name="EndDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" value="" data-enddate="2016-01-11">
            </div>
            <div class="col-sm-2">
              <input type="text" name="EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
            </div>
          </div>
          <div class="form-group">
            <div id="table-5_wrapper" class="dataTables_wrapper form-inline" role="grid">
              <div class="row">
                <div class="col-xs-12 border_left">
                  <div class="dataTables_filter" id="table-5_filter">
                    <label>Search:
                      <input type="text" aria-controls="table-5">
                    </label>
                  </div>
                </div>
              </div>
              <table class="table table-bordered datatable dataTable" id="table-5">
                <thead>
                  <tr role="row">
                    <th class="sorting_disabled" role="columnheader" rowspan="1" colspan="1" aria-label="" style="width: 0px;"><input type="checkbox" id="selectallcust" name="AccountID[]" class=""></th>
                    <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-5" rowspan="1" colspan="1" aria-label="Customer Name: activate to sort column ascending" style="width: 0px;">Customer Name</th>
                  </tr>
                </thead>
                <tbody role="alert" aria-live="polite" aria-relevant="all">
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3110" class="rowcheckbox">
                      </div></td>
                    <td class="">Rafid Net</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3" class="rowcheckbox">
                      </div></td>
                    <td class="">Mamum</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="4" class="rowcheckbox">
                      </div></td>
                    <td class="">Asif</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="5" class="rowcheckbox">
                      </div></td>
                    <td class="">Xpertel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="7" class="rowcheckbox">
                      </div></td>
                    <td class="">BlueBird</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="8" class="rowcheckbox">
                      </div></td>
                    <td class="">Artel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="9" class="rowcheckbox">
                      </div></td>
                    <td class="">Global Teknoloji Telekomunikasyon BEOX</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="10" class="rowcheckbox">
                      </div></td>
                    <td class="">Dollar Phone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="11" class="rowcheckbox">
                      </div></td>
                    <td class="">Green Voip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="12" class="rowcheckbox">
                      </div></td>
                    <td class="">Gulf Dail</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="13" class="rowcheckbox">
                      </div></td>
                    <td class="">Junction</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="14" class="rowcheckbox">
                      </div></td>
                    <td class="">QGC BD</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="16" class="rowcheckbox">
                      </div></td>
                    <td class="">TARIQ</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="17" class="rowcheckbox">
                      </div></td>
                    <td class="">TATA COMM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="18" class="rowcheckbox">
                      </div></td>
                    <td class="">Tele</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="19" class="rowcheckbox">
                      </div></td>
                    <td class="">Verscom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="20" class="rowcheckbox">
                      </div></td>
                    <td class="">Voice Tec</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="21" class="rowcheckbox">
                      </div></td>
                    <td class="">VOiceTrade</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="22" class="rowcheckbox">
                      </div></td>
                    <td class="">A to Z Dailer</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="23" class="rowcheckbox">
                      </div></td>
                    <td class="">RoseTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="24" class="rowcheckbox">
                      </div></td>
                    <td class="">VoiceTel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="25" class="rowcheckbox">
                      </div></td>
                    <td class="">Future</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="26" class="rowcheckbox">
                      </div></td>
                    <td class="">Maxtelco</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="27" class="rowcheckbox">
                      </div></td>
                    <td class="">GLOBALVOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="28" class="rowcheckbox">
                      </div></td>
                    <td class="">Ctg</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="29" class="rowcheckbox">
                      </div></td>
                    <td class="">A Cell Teleservices</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="30" class="rowcheckbox">
                      </div></td>
                    <td class="">Universal Voip Shenglitel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="31" class="rowcheckbox">
                      </div></td>
                    <td class="">Quickcom Global</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="32" class="rowcheckbox">
                      </div></td>
                    <td class="">Rudra Global</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="33" class="rowcheckbox">
                      </div></td>
                    <td class="">XL Call</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="34" class="rowcheckbox">
                      </div></td>
                    <td class="">Iven Company</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="35" class="rowcheckbox">
                      </div></td>
                    <td class="">Juniper</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="36" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Switch</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="37" class="rowcheckbox">
                      </div></td>
                    <td class="">Tvoice solution</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="38" class="rowcheckbox">
                      </div></td>
                    <td class="">S9 Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="39" class="rowcheckbox">
                      </div></td>
                    <td class="">Arweentel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="40" class="rowcheckbox">
                      </div></td>
                    <td class="">Speedflow Communications Limited</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="41" class="rowcheckbox">
                      </div></td>
                    <td class="">Icall</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="42" class="rowcheckbox">
                      </div></td>
                    <td class="">Call2world</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="43" class="rowcheckbox">
                      </div></td>
                    <td class="">Tringlo</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="44" class="rowcheckbox">
                      </div></td>
                    <td class="">RS Communications</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="45" class="rowcheckbox">
                      </div></td>
                    <td class="">ALKAIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="46" class="rowcheckbox">
                      </div></td>
                    <td class="">Saddamtel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="47" class="rowcheckbox">
                      </div></td>
                    <td class="">Bharti Airtel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="48" class="rowcheckbox">
                      </div></td>
                    <td class="">Pooinfratech</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="49" class="rowcheckbox">
                      </div></td>
                    <td class="">Telcom Global</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="50" class="rowcheckbox">
                      </div></td>
                    <td class="">BTI</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="51" class="rowcheckbox">
                      </div></td>
                    <td class="">Manor IT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="52" class="rowcheckbox">
                      </div></td>
                    <td class="">Jazz Voip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="54" class="rowcheckbox">
                      </div></td>
                    <td class="">Captioned</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="55" class="rowcheckbox">
                      </div></td>
                    <td class="">Voice Glow</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="56" class="rowcheckbox">
                      </div></td>
                    <td class="">Wains Sol</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="57" class="rowcheckbox">
                      </div></td>
                    <td class="">MYNICTALK</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="58" class="rowcheckbox">
                      </div></td>
                    <td class="">National Telecom Ltd</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="59" class="rowcheckbox">
                      </div></td>
                    <td class="">Talkmore</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="60" class="rowcheckbox">
                      </div></td>
                    <td class="">Crystal Voip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="61" class="rowcheckbox">
                      </div></td>
                    <td class="">ALM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="62" class="rowcheckbox">
                      </div></td>
                    <td class="">CCT Communications</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="63" class="rowcheckbox">
                      </div></td>
                    <td class="">Online Dialer</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="64" class="rowcheckbox">
                      </div></td>
                    <td class="">Carrier Operation</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="67" class="rowcheckbox">
                      </div></td>
                    <td class="">Juttvoip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="68" class="rowcheckbox">
                      </div></td>
                    <td class="">Myfreetel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="69" class="rowcheckbox">
                      </div></td>
                    <td class="">Wateen</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="70" class="rowcheckbox">
                      </div></td>
                    <td class="">Quick Net</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="71" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Mizan</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="72" class="rowcheckbox">
                      </div></td>
                    <td class="">VOICEBUY</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="73" class="rowcheckbox">
                      </div></td>
                    <td class="">Itellz</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="74" class="rowcheckbox">
                      </div></td>
                    <td class="">SPD Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="75" class="rowcheckbox">
                      </div></td>
                    <td class="">Freewaretel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="76" class="rowcheckbox">
                      </div></td>
                    <td class="">Ivoco</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="77" class="rowcheckbox">
                      </div></td>
                    <td class="">Usman Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="78" class="rowcheckbox">
                      </div></td>
                    <td class="">Ashantel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="79" class="rowcheckbox">
                      </div></td>
                    <td class="">Union Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="80" class="rowcheckbox">
                      </div></td>
                    <td class="">Vocal Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="81" class="rowcheckbox">
                      </div></td>
                    <td class="">SR Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="82" class="rowcheckbox">
                      </div></td>
                    <td class="">Somoy Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="83" class="rowcheckbox">
                      </div></td>
                    <td class="">LIBRA TELCO</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="84" class="rowcheckbox">
                      </div></td>
                    <td class="">Rovex</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="85" class="rowcheckbox">
                      </div></td>
                    <td class="">DVL group</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="86" class="rowcheckbox">
                      </div></td>
                    <td class="">Spacher Tech</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="87" class="rowcheckbox">
                      </div></td>
                    <td class="">Lanck</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="88" class="rowcheckbox">
                      </div></td>
                    <td class="">Ditels</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="89" class="rowcheckbox">
                      </div></td>
                    <td class="">Route Trader</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="90" class="rowcheckbox">
                      </div></td>
                    <td class="">Server Host</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="91" class="rowcheckbox">
                      </div></td>
                    <td class="">Morning Teecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="93" class="rowcheckbox">
                      </div></td>
                    <td class="">VOIP CALL SHOP INC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="94" class="rowcheckbox">
                      </div></td>
                    <td class="">MFT</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="95" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Mumin</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="96" class="rowcheckbox">
                      </div></td>
                    <td class="">Call2asia</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="97" class="rowcheckbox">
                      </div></td>
                    <td class="">SOFTOFFER</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="98" class="rowcheckbox">
                      </div></td>
                    <td class="">My Route Shop</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="99" class="rowcheckbox">
                      </div></td>
                    <td class="">ANAS</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="100" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Carrier</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="101" class="rowcheckbox">
                      </div></td>
                    <td class="">NZvoip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="102" class="rowcheckbox">
                      </div></td>
                    <td class="">Dockland</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="103" class="rowcheckbox">
                      </div></td>
                    <td class="">UTN</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="104" class="rowcheckbox">
                      </div></td>
                    <td class="">Zerib</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="105" class="rowcheckbox">
                      </div></td>
                    <td class="">MetroFi</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="106" class="rowcheckbox">
                      </div></td>
                    <td class="">Kamy International</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="107" class="rowcheckbox">
                      </div></td>
                    <td class="">CinqTech</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="108" class="rowcheckbox">
                      </div></td>
                    <td class="">Global IP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="109" class="rowcheckbox">
                      </div></td>
                    <td class="">Parkwell</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="110" class="rowcheckbox">
                      </div></td>
                    <td class="">Sujan</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="111" class="rowcheckbox">
                      </div></td>
                    <td class="">East west Phone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="113" class="rowcheckbox">
                      </div></td>
                    <td class="">Fortis</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="114" class="rowcheckbox">
                      </div></td>
                    <td class="">XICOMM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="115" class="rowcheckbox">
                      </div></td>
                    <td class="">Newage Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="116" class="rowcheckbox">
                      </div></td>
                    <td class="">ACE telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="117" class="rowcheckbox">
                      </div></td>
                    <td class="">Hoover Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="118" class="rowcheckbox">
                      </div></td>
                    <td class="">Ifone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="119" class="rowcheckbox">
                      </div></td>
                    <td class="">Pioneer Voice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="120" class="rowcheckbox">
                      </div></td>
                    <td class="">Ebazzar Networks</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="121" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Rony</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="122" class="rowcheckbox">
                      </div></td>
                    <td class="">INTELCOM LINE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="124" class="rowcheckbox">
                      </div></td>
                    <td class="">VOIP SHOP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="126" class="rowcheckbox">
                      </div></td>
                    <td class="">Brilliant Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="127" class="rowcheckbox">
                      </div></td>
                    <td class="">PTCL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="128" class="rowcheckbox">
                      </div></td>
                    <td class="">Sido</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="129" class="rowcheckbox">
                      </div></td>
                    <td class="">Versitel LLC</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="138" class="rowcheckbox">
                      </div></td>
                    <td class="">Keyhan Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="139" class="rowcheckbox">
                      </div></td>
                    <td class="">Key West</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="140" class="rowcheckbox">
                      </div></td>
                    <td class="">Real Voice</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="141" class="rowcheckbox">
                      </div></td>
                    <td class="">Firm Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="142" class="rowcheckbox">
                      </div></td>
                    <td class="">VAZQ</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="143" class="rowcheckbox">
                      </div></td>
                    <td class="">PROEMA</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="144" class="rowcheckbox">
                      </div></td>
                    <td class="">Reliance</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="145" class="rowcheckbox">
                      </div></td>
                    <td class="">Global Net holding</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="146" class="rowcheckbox">
                      </div></td>
                    <td class="">Prime Linx</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="147" class="rowcheckbox">
                      </div></td>
                    <td class="">IDT</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="148" class="rowcheckbox">
                      </div></td>
                    <td class="">Trade Me Thailand CO</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="149" class="rowcheckbox">
                      </div></td>
                    <td class="">PCCW</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="150" class="rowcheckbox">
                      </div></td>
                    <td class="">VOIPMONSTER</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="151" class="rowcheckbox">
                      </div></td>
                    <td class="">Awan Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="152" class="rowcheckbox">
                      </div></td>
                    <td class="">Public Voip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="153" class="rowcheckbox">
                      </div></td>
                    <td class="">4B Gentel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="154" class="rowcheckbox">
                      </div></td>
                    <td class="">CKRI</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="155" class="rowcheckbox">
                      </div></td>
                    <td class="">Callnet Communications</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="156" class="rowcheckbox">
                      </div></td>
                    <td class="">NR Callshop</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="157" class="rowcheckbox">
                      </div></td>
                    <td class="">Green Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="158" class="rowcheckbox">
                      </div></td>
                    <td class="">CallsLink</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="159" class="rowcheckbox">
                      </div></td>
                    <td class="">AIR VOICE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="160" class="rowcheckbox">
                      </div></td>
                    <td class="">Red Voip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="161" class="rowcheckbox">
                      </div></td>
                    <td class="">Kashif</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="162" class="rowcheckbox">
                      </div></td>
                    <td class="">Aljazeera Net</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="163" class="rowcheckbox">
                      </div></td>
                    <td class="">Best Care-tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="164" class="rowcheckbox">
                      </div></td>
                    <td class="">Ray Cororation</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="165" class="rowcheckbox">
                      </div></td>
                    <td class="">Glory Comm</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="166" class="rowcheckbox">
                      </div></td>
                    <td class="">Sarada Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="167" class="rowcheckbox">
                      </div></td>
                    <td class="">Olinda telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="168" class="rowcheckbox">
                      </div></td>
                    <td class="">Paki Phone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="169" class="rowcheckbox">
                      </div></td>
                    <td class="">Albatross Communications</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="170" class="rowcheckbox">
                      </div></td>
                    <td class="">VIVA</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="171" class="rowcheckbox">
                      </div></td>
                    <td class="">Callathon</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="172" class="rowcheckbox">
                      </div></td>
                    <td class="">Vasudev</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="173" class="rowcheckbox">
                      </div></td>
                    <td class="">Affix</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="174" class="rowcheckbox">
                      </div></td>
                    <td class="">Auktel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="175" class="rowcheckbox">
                      </div></td>
                    <td class="">Axistel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="176" class="rowcheckbox">
                      </div></td>
                    <td class="">Cheap IP call</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="177" class="rowcheckbox">
                      </div></td>
                    <td class="">Communesolution</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="178" class="rowcheckbox">
                      </div></td>
                    <td class="">Golden Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="179" class="rowcheckbox">
                      </div></td>
                    <td class="">Hivetechno</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="180" class="rowcheckbox">
                      </div></td>
                    <td class="">Maher Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="181" class="rowcheckbox">
                      </div></td>
                    <td class="">Modina-Tell</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="182" class="rowcheckbox">
                      </div></td>
                    <td class="">NS telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="183" class="rowcheckbox">
                      </div></td>
                    <td class="">Nextel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="184" class="rowcheckbox">
                      </div></td>
                    <td class="">Razi tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="185" class="rowcheckbox">
                      </div></td>
                    <td class="">Sai Venus Voice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="186" class="rowcheckbox">
                      </div></td>
                    <td class="">Stoneeadge</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="187" class="rowcheckbox">
                      </div></td>
                    <td class="">TEAM NETWORK</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="188" class="rowcheckbox">
                      </div></td>
                    <td class="">G Link</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="189" class="rowcheckbox">
                      </div></td>
                    <td class="">XS-TONE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="190" class="rowcheckbox">
                      </div></td>
                    <td class="">RJR Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="192" class="rowcheckbox">
                      </div></td>
                    <td class="">Amzad Voip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="193" class="rowcheckbox">
                      </div></td>
                    <td class="">Cool Tone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="194" class="rowcheckbox">
                      </div></td>
                    <td class="">Net-Talk</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="195" class="rowcheckbox">
                      </div></td>
                    <td class="">Ample Comm</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="196" class="rowcheckbox">
                      </div></td>
                    <td class="">A.A. SmartComTech</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="197" class="rowcheckbox">
                      </div></td>
                    <td class="">IXC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="198" class="rowcheckbox">
                      </div></td>
                    <td class="">Breezecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="199" class="rowcheckbox">
                      </div></td>
                    <td class="">Moving Golf</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="200" class="rowcheckbox">
                      </div></td>
                    <td class="">Giant-tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="201" class="rowcheckbox">
                      </div></td>
                    <td class="">Talk Today</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="202" class="rowcheckbox">
                      </div></td>
                    <td class="">Zaintel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="203" class="rowcheckbox">
                      </div></td>
                    <td class="">IXC Global</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="204" class="rowcheckbox">
                      </div></td>
                    <td class="">World Hub</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="205" class="rowcheckbox">
                      </div></td>
                    <td class="">Wave Retail</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="206" class="rowcheckbox">
                      </div></td>
                    <td class="">Chit chat</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="207" class="rowcheckbox">
                      </div></td>
                    <td class="">Mynicevoip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="208" class="rowcheckbox">
                      </div></td>
                    <td class="">Cool Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="209" class="rowcheckbox">
                      </div></td>
                    <td class="">Star Point</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="210" class="rowcheckbox">
                      </div></td>
                    <td class="">Maxcall</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="211" class="rowcheckbox">
                      </div></td>
                    <td class="">Telekomerz</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="212" class="rowcheckbox">
                      </div></td>
                    <td class="">Bjay Chaudry</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="214" class="rowcheckbox">
                      </div></td>
                    <td class="">Onevoip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="215" class="rowcheckbox">
                      </div></td>
                    <td class="">SAIF</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="217" class="rowcheckbox">
                      </div></td>
                    <td class="">Syl-telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="218" class="rowcheckbox">
                      </div></td>
                    <td class="">Media Mobile</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="219" class="rowcheckbox">
                      </div></td>
                    <td class="">PEOPLETEL, S.A.</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="220" class="rowcheckbox">
                      </div></td>
                    <td class="">VoicenVoice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="221" class="rowcheckbox">
                      </div></td>
                    <td class="">VOICECOMM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="222" class="rowcheckbox">
                      </div></td>
                    <td class="">Ipage Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="223" class="rowcheckbox">
                      </div></td>
                    <td class="">TRC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="224" class="rowcheckbox">
                      </div></td>
                    <td class="">NYX Telecommunications Limited</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="225" class="rowcheckbox">
                      </div></td>
                    <td class="">Focal Point</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="226" class="rowcheckbox">
                      </div></td>
                    <td class="">Awal Wave</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="227" class="rowcheckbox">
                      </div></td>
                    <td class="">DXBCALLS</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="228" class="rowcheckbox">
                      </div></td>
                    <td class="">PhoneToAll</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="229" class="rowcheckbox">
                      </div></td>
                    <td class="">BHAOO</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="231" class="rowcheckbox">
                      </div></td>
                    <td class="">Ivoice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="232" class="rowcheckbox">
                      </div></td>
                    <td class="">SETU</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="233" class="rowcheckbox">
                      </div></td>
                    <td class="">KOL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="234" class="rowcheckbox">
                      </div></td>
                    <td class="">Chintal</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="236" class="rowcheckbox">
                      </div></td>
                    <td class="">SK Global</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="237" class="rowcheckbox">
                      </div></td>
                    <td class="">TALKFREE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="238" class="rowcheckbox">
                      </div></td>
                    <td class="">Teleworld</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="239" class="rowcheckbox">
                      </div></td>
                    <td class="">Favourite Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="240" class="rowcheckbox">
                      </div></td>
                    <td class="">PHONETIME</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="241" class="rowcheckbox">
                      </div></td>
                    <td class="">TELAXIA</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="242" class="rowcheckbox">
                      </div></td>
                    <td class="">Around Call</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="243" class="rowcheckbox">
                      </div></td>
                    <td class="">Chetterstrom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="244" class="rowcheckbox">
                      </div></td>
                    <td class="">Ocean technology</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="245" class="rowcheckbox">
                      </div></td>
                    <td class="">NORDIV</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="246" class="rowcheckbox">
                      </div></td>
                    <td class="">CLoud9</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="247" class="rowcheckbox">
                      </div></td>
                    <td class="">G5</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="248" class="rowcheckbox">
                      </div></td>
                    <td class="">Madina Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="249" class="rowcheckbox">
                      </div></td>
                    <td class="">TELCONNECT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="250" class="rowcheckbox">
                      </div></td>
                    <td class="">Skyvia</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="251" class="rowcheckbox">
                      </div></td>
                    <td class="">ACE Limited</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="252" class="rowcheckbox">
                      </div></td>
                    <td class="">KDDI-EUROPE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="253" class="rowcheckbox">
                      </div></td>
                    <td class="">BOLOTEL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="254" class="rowcheckbox">
                      </div></td>
                    <td class="">BDM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="255" class="rowcheckbox">
                      </div></td>
                    <td class="">Friendscom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="256" class="rowcheckbox">
                      </div></td>
                    <td class="">Arus Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="257" class="rowcheckbox">
                      </div></td>
                    <td class="">DESH</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="258" class="rowcheckbox">
                      </div></td>
                    <td class="">UPBEATBIZ</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="259" class="rowcheckbox">
                      </div></td>
                    <td class="">ANS</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="260" class="rowcheckbox">
                      </div></td>
                    <td class="">DAS</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="261" class="rowcheckbox">
                      </div></td>
                    <td class="">ADG</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="262" class="rowcheckbox">
                      </div></td>
                    <td class="">IRFA</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="263" class="rowcheckbox">
                      </div></td>
                    <td class="">TelQom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="264" class="rowcheckbox">
                      </div></td>
                    <td class="">ONEWORLD</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="266" class="rowcheckbox">
                      </div></td>
                    <td class="">EhsanVOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="267" class="rowcheckbox">
                      </div></td>
                    <td class="">Rabica Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="268" class="rowcheckbox">
                      </div></td>
                    <td class="">Makkah Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="269" class="rowcheckbox">
                      </div></td>
                    <td class="">Lensol Systems</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="270" class="rowcheckbox">
                      </div></td>
                    <td class="">ChinaTYH</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="271" class="rowcheckbox">
                      </div></td>
                    <td class="">EvoiceBD</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="272" class="rowcheckbox">
                      </div></td>
                    <td class="">GlobeTouch</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="273" class="rowcheckbox">
                      </div></td>
                    <td class="">VLINK</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="274" class="rowcheckbox">
                      </div></td>
                    <td class="">REDFACE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="275" class="rowcheckbox">
                      </div></td>
                    <td class="">Yutele Technology Ltd</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="278" class="rowcheckbox">
                      </div></td>
                    <td class="">SIFY</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="279" class="rowcheckbox">
                      </div></td>
                    <td class="">Calls2net</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="280" class="rowcheckbox">
                      </div></td>
                    <td class="">Smart Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="281" class="rowcheckbox">
                      </div></td>
                    <td class="">EasyTalk</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="282" class="rowcheckbox">
                      </div></td>
                    <td class="">Evox-IT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="283" class="rowcheckbox">
                      </div></td>
                    <td class="">Eco-Carrier</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="284" class="rowcheckbox">
                      </div></td>
                    <td class="">Spectron</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="285" class="rowcheckbox">
                      </div></td>
                    <td class="">Passport-Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="286" class="rowcheckbox">
                      </div></td>
                    <td class="">SMG-Global</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="287" class="rowcheckbox">
                      </div></td>
                    <td class="">Voicepundit</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="288" class="rowcheckbox">
                      </div></td>
                    <td class="">Angel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="289" class="rowcheckbox">
                      </div></td>
                    <td class="">Condortel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="290" class="rowcheckbox">
                      </div></td>
                    <td class="">Citycom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="293" class="rowcheckbox">
                      </div></td>
                    <td class="">INTERMATICA</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="294" class="rowcheckbox">
                      </div></td>
                    <td class="">4G Business Solution</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="295" class="rowcheckbox">
                      </div></td>
                    <td class="">A TEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="296" class="rowcheckbox">
                      </div></td>
                    <td class="">Ali-Ashraf</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="298" class="rowcheckbox">
                      </div></td>
                    <td class="">Z Call</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="299" class="rowcheckbox">
                      </div></td>
                    <td class="">Fast Tech</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="300" class="rowcheckbox">
                      </div></td>
                    <td class="">Media Orientalis</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="301" class="rowcheckbox">
                      </div></td>
                    <td class="">Condor</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="302" class="rowcheckbox">
                      </div></td>
                    <td class="">MVOIPCALL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="303" class="rowcheckbox">
                      </div></td>
                    <td class="">Aclass Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="304" class="rowcheckbox">
                      </div></td>
                    <td class="">Call In motion</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="305" class="rowcheckbox">
                      </div></td>
                    <td class="">Telintel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="306" class="rowcheckbox">
                      </div></td>
                    <td class="">AVyS Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="307" class="rowcheckbox">
                      </div></td>
                    <td class="">ALODIGA</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="308" class="rowcheckbox">
                      </div></td>
                    <td class="">Omtelentia LTD</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="309" class="rowcheckbox">
                      </div></td>
                    <td class="">VOCOM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="311" class="rowcheckbox">
                      </div></td>
                    <td class="">Surfmedia</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="312" class="rowcheckbox">
                      </div></td>
                    <td class="">Acme Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="314" class="rowcheckbox">
                      </div></td>
                    <td class="">DIVOX FZ LLC</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="316" class="rowcheckbox">
                      </div></td>
                    <td class="">QUANTUM TELECOM, SAU</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="317" class="rowcheckbox">
                      </div></td>
                    <td class="">Serene Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="318" class="rowcheckbox">
                      </div></td>
                    <td class="">Lastmile</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="319" class="rowcheckbox">
                      </div></td>
                    <td class="">AliXinA</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="320" class="rowcheckbox">
                      </div></td>
                    <td class="">FTDL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="321" class="rowcheckbox">
                      </div></td>
                    <td class="">MEA SYSTEM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="322" class="rowcheckbox">
                      </div></td>
                    <td class="">Allianz</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="323" class="rowcheckbox">
                      </div></td>
                    <td class="">Voipsolutions</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="324" class="rowcheckbox">
                      </div></td>
                    <td class="">Luxor</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="325" class="rowcheckbox">
                      </div></td>
                    <td class="">Altcomtech</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="326" class="rowcheckbox">
                      </div></td>
                    <td class="">Phoneserve Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="327" class="rowcheckbox">
                      </div></td>
                    <td class="">Nexen</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="328" class="rowcheckbox">
                      </div></td>
                    <td class="">S-K GLobal</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="329" class="rowcheckbox">
                      </div></td>
                    <td class="">Oryx Tech</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="330" class="rowcheckbox">
                      </div></td>
                    <td class="">Money-Talks</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="331" class="rowcheckbox">
                      </div></td>
                    <td class="">BBOX</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="332" class="rowcheckbox">
                      </div></td>
                    <td class="">Royal Plus</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="333" class="rowcheckbox">
                      </div></td>
                    <td class="">Mariana Traders Ltd</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="334" class="rowcheckbox">
                      </div></td>
                    <td class="">Voice Connection</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="335" class="rowcheckbox">
                      </div></td>
                    <td class="">DDTVOIP2</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="336" class="rowcheckbox">
                      </div></td>
                    <td class="">Synterra</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="337" class="rowcheckbox">
                      </div></td>
                    <td class="">Synterra UK</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="338" class="rowcheckbox">
                      </div></td>
                    <td class="">Benbrook</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="339" class="rowcheckbox">
                      </div></td>
                    <td class="">CAPITAL CITY SERVICES</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="340" class="rowcheckbox">
                      </div></td>
                    <td class="">Eris-Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="341" class="rowcheckbox">
                      </div></td>
                    <td class="">Green Communication</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="343" class="rowcheckbox">
                      </div></td>
                    <td class="">VIZON-WAVE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="344" class="rowcheckbox">
                      </div></td>
                    <td class="">Singularity</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="345" class="rowcheckbox">
                      </div></td>
                    <td class="">Ruztel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="346" class="rowcheckbox">
                      </div></td>
                    <td class="">SMART-TEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="347" class="rowcheckbox">
                      </div></td>
                    <td class="">GILAT-SATCOM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="348" class="rowcheckbox">
                      </div></td>
                    <td class="">Synectiv</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="349" class="rowcheckbox">
                      </div></td>
                    <td class="">Vox Carrier</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="350" class="rowcheckbox">
                      </div></td>
                    <td class="">AWC</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="351" class="rowcheckbox">
                      </div></td>
                    <td class="">Zakan Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="352" class="rowcheckbox">
                      </div></td>
                    <td class="">DBL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="353" class="rowcheckbox">
                      </div></td>
                    <td class="">Teltac</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="354" class="rowcheckbox">
                      </div></td>
                    <td class="">Annecto</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="355" class="rowcheckbox">
                      </div></td>
                    <td class="">Global-Ringer</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="356" class="rowcheckbox">
                      </div></td>
                    <td class="">Berrysun</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="357" class="rowcheckbox">
                      </div></td>
                    <td class="">Swiss-Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="358" class="rowcheckbox">
                      </div></td>
                    <td class="">IT-HUB</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="359" class="rowcheckbox">
                      </div></td>
                    <td class="">Moin Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="360" class="rowcheckbox">
                      </div></td>
                    <td class="">TG-Transit</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="361" class="rowcheckbox">
                      </div></td>
                    <td class="">Star Group</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="362" class="rowcheckbox">
                      </div></td>
                    <td class="">CHT</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="363" class="rowcheckbox">
                      </div></td>
                    <td class="">Acube Infotech</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="364" class="rowcheckbox">
                      </div></td>
                    <td class="">Hk-Express</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="365" class="rowcheckbox">
                      </div></td>
                    <td class="">Callsat</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="366" class="rowcheckbox">
                      </div></td>
                    <td class="">RAZA</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="367" class="rowcheckbox">
                      </div></td>
                    <td class="">TSN</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="368" class="rowcheckbox">
                      </div></td>
                    <td class="">LTEX</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="369" class="rowcheckbox">
                      </div></td>
                    <td class="">A2Co</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="370" class="rowcheckbox">
                      </div></td>
                    <td class="">FizanTelecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="371" class="rowcheckbox">
                      </div></td>
                    <td class="">Roots-Network</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="372" class="rowcheckbox">
                      </div></td>
                    <td class="">Techzone</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="373" class="rowcheckbox">
                      </div></td>
                    <td class="">18Y</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="374" class="rowcheckbox">
                      </div></td>
                    <td class="">FLASH CALLS</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="375" class="rowcheckbox">
                      </div></td>
                    <td class="">Raihan</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="376" class="rowcheckbox">
                      </div></td>
                    <td class="">Jana Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="377" class="rowcheckbox">
                      </div></td>
                    <td class="">GLOBALCALLING</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="378" class="rowcheckbox">
                      </div></td>
                    <td class="">Gold Line</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="381" class="rowcheckbox">
                      </div></td>
                    <td class="">TELASCO COMMUNICATIONS LTD</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="382" class="rowcheckbox">
                      </div></td>
                    <td class="">Shahid</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="383" class="rowcheckbox">
                      </div></td>
                    <td class="">SaifCall</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="384" class="rowcheckbox">
                      </div></td>
                    <td class="">Telesnet</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="385" class="rowcheckbox">
                      </div></td>
                    <td class="">Vocal Solutions</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="386" class="rowcheckbox">
                      </div></td>
                    <td class="">HD-WorldWide</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="387" class="rowcheckbox">
                      </div></td>
                    <td class="">Indulge Voip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="388" class="rowcheckbox">
                      </div></td>
                    <td class="">Globe Teleservices GTS</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="389" class="rowcheckbox">
                      </div></td>
                    <td class="">Calltime</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="390" class="rowcheckbox">
                      </div></td>
                    <td class="">Makecall</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="391" class="rowcheckbox">
                      </div></td>
                    <td class="">GSFVOICE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="392" class="rowcheckbox">
                      </div></td>
                    <td class="">Yaband</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="393" class="rowcheckbox">
                      </div></td>
                    <td class="">Kalam Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="395" class="rowcheckbox">
                      </div></td>
                    <td class="">Skyline</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="396" class="rowcheckbox">
                      </div></td>
                    <td class="">Wicworld</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="401" class="rowcheckbox">
                      </div></td>
                    <td class="">Safa Plus</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="402" class="rowcheckbox">
                      </div></td>
                    <td class="">Progressive Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="403" class="rowcheckbox">
                      </div></td>
                    <td class="">Mainberg</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="404" class="rowcheckbox">
                      </div></td>
                    <td class="">Trade92</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="405" class="rowcheckbox">
                      </div></td>
                    <td class="">MIR PAPEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="406" class="rowcheckbox">
                      </div></td>
                    <td class="">Jeevan Talk</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="407" class="rowcheckbox">
                      </div></td>
                    <td class="">SIA LEXICO TELECOM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="408" class="rowcheckbox">
                      </div></td>
                    <td class="">SLIZTEC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="409" class="rowcheckbox">
                      </div></td>
                    <td class="">Love4India</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="411" class="rowcheckbox">
                      </div></td>
                    <td class="">Remixtel Networks</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="412" class="rowcheckbox">
                      </div></td>
                    <td class="">SAHEL TALK</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="413" class="rowcheckbox">
                      </div></td>
                    <td class="">Megahertz</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="414" class="rowcheckbox">
                      </div></td>
                    <td class="">Sophia-techology</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="417" class="rowcheckbox">
                      </div></td>
                    <td class="">ApolloCall</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="418" class="rowcheckbox">
                      </div></td>
                    <td class="">VOIP-ASIA</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="419" class="rowcheckbox">
                      </div></td>
                    <td class="">Hafiz Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="420" class="rowcheckbox">
                      </div></td>
                    <td class="">AK Tech Services</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="422" class="rowcheckbox">
                      </div></td>
                    <td class="">Cyberfone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="423" class="rowcheckbox">
                      </div></td>
                    <td class="">B&amp;C</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="424" class="rowcheckbox">
                      </div></td>
                    <td class="">Eco-Calls</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="425" class="rowcheckbox">
                      </div></td>
                    <td class="">Life Call Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="426" class="rowcheckbox">
                      </div></td>
                    <td class="">Josen</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="427" class="rowcheckbox">
                      </div></td>
                    <td class="">Sunny Pacific</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="428" class="rowcheckbox">
                      </div></td>
                    <td class="">Futon</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="429" class="rowcheckbox">
                      </div></td>
                    <td class="">AdnanTel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="430" class="rowcheckbox">
                      </div></td>
                    <td class="">Rooby Gold</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="432" class="rowcheckbox">
                      </div></td>
                    <td class="">Tele foonik</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="433" class="rowcheckbox">
                      </div></td>
                    <td class="">Intrachannel SRL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="434" class="rowcheckbox">
                      </div></td>
                    <td class="">Abid Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="435" class="rowcheckbox">
                      </div></td>
                    <td class="">IVY Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="436" class="rowcheckbox">
                      </div></td>
                    <td class="">FatimahTel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="437" class="rowcheckbox">
                      </div></td>
                    <td class="">Layer5</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="439" class="rowcheckbox">
                      </div></td>
                    <td class="">Neekava Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="440" class="rowcheckbox">
                      </div></td>
                    <td class="">Ali VoIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="442" class="rowcheckbox">
                      </div></td>
                    <td class="">HTS</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="443" class="rowcheckbox">
                      </div></td>
                    <td class="">BD Explore</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="445" class="rowcheckbox">
                      </div></td>
                    <td class="">CTech</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="446" class="rowcheckbox">
                      </div></td>
                    <td class="">MadniVoIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="447" class="rowcheckbox">
                      </div></td>
                    <td class="">IPTEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="448" class="rowcheckbox">
                      </div></td>
                    <td class="">MKY</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="449" class="rowcheckbox">
                      </div></td>
                    <td class="">1-to-All</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="451" class="rowcheckbox">
                      </div></td>
                    <td class="">SpeedVoice</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="452" class="rowcheckbox">
                      </div></td>
                    <td class="">ISD NETWORKS</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="453" class="rowcheckbox">
                      </div></td>
                    <td class="">Digitixers</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="454" class="rowcheckbox">
                      </div></td>
                    <td class="">Skytel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="455" class="rowcheckbox">
                      </div></td>
                    <td class="">ICSCOM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="457" class="rowcheckbox">
                      </div></td>
                    <td class="">Emperion</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="458" class="rowcheckbox">
                      </div></td>
                    <td class="">Telewhite</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="459" class="rowcheckbox">
                      </div></td>
                    <td class="">TulyNet</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="460" class="rowcheckbox">
                      </div></td>
                    <td class="">YMAX</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="462" class="rowcheckbox">
                      </div></td>
                    <td class="">Wasif Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="463" class="rowcheckbox">
                      </div></td>
                    <td class="">Taqbeer Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="465" class="rowcheckbox">
                      </div></td>
                    <td class="">MadniTel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="466" class="rowcheckbox">
                      </div></td>
                    <td class="">CRYSTAL FONE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="467" class="rowcheckbox">
                      </div></td>
                    <td class="">Dial Plus</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="469" class="rowcheckbox">
                      </div></td>
                    <td class="">V-Verse</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="470" class="rowcheckbox">
                      </div></td>
                    <td class="">MK VOIP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="471" class="rowcheckbox">
                      </div></td>
                    <td class="">Afinna</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="472" class="rowcheckbox">
                      </div></td>
                    <td class="">SPTalk</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="473" class="rowcheckbox">
                      </div></td>
                    <td class="">Talk Shop LLC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="475" class="rowcheckbox">
                      </div></td>
                    <td class="">Real Tech Communication</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="476" class="rowcheckbox">
                      </div></td>
                    <td class="">Roisa Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="477" class="rowcheckbox">
                      </div></td>
                    <td class="">Gupta Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="478" class="rowcheckbox">
                      </div></td>
                    <td class="">iCom Voice</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="479" class="rowcheckbox">
                      </div></td>
                    <td class="">West Mount Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="481" class="rowcheckbox">
                      </div></td>
                    <td class="">VVN</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="482" class="rowcheckbox">
                      </div></td>
                    <td class="">MMJ</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="483" class="rowcheckbox">
                      </div></td>
                    <td class="">BisTalk</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="485" class="rowcheckbox">
                      </div></td>
                    <td class="">JK Express</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="500" class="rowcheckbox">
                      </div></td>
                    <td class="">Global Telegate Canada GTC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="501" class="rowcheckbox">
                      </div></td>
                    <td class="">Kamsar</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="502" class="rowcheckbox">
                      </div></td>
                    <td class="">N.J Networks</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="503" class="rowcheckbox">
                      </div></td>
                    <td class="">OXNP  TELECOM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="504" class="rowcheckbox">
                      </div></td>
                    <td class="">Mediatel Ltd</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="505" class="rowcheckbox">
                      </div></td>
                    <td class="">MnM Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="507" class="rowcheckbox">
                      </div></td>
                    <td class="">Hamid Sourcing</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="509" class="rowcheckbox">
                      </div></td>
                    <td class="">Samara Call</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="510" class="rowcheckbox">
                      </div></td>
                    <td class="">AMB Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="511" class="rowcheckbox">
                      </div></td>
                    <td class="">Sabbir VOIP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="513" class="rowcheckbox">
                      </div></td>
                    <td class="">MS Thin Technologies</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="514" class="rowcheckbox">
                      </div></td>
                    <td class="">Call Point</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="515" class="rowcheckbox">
                      </div></td>
                    <td class="">SSH NETWORK</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="517" class="rowcheckbox">
                      </div></td>
                    <td class="">Anisa-Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="518" class="rowcheckbox">
                      </div></td>
                    <td class="">Shoquat VOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="519" class="rowcheckbox">
                      </div></td>
                    <td class="">Pingmar Tech</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="520" class="rowcheckbox">
                      </div></td>
                    <td class="">AmpleFone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="521" class="rowcheckbox">
                      </div></td>
                    <td class="">ATMPHONE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="523" class="rowcheckbox">
                      </div></td>
                    <td class="">ATM PHONE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="524" class="rowcheckbox">
                      </div></td>
                    <td class="">Wenda Com</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="525" class="rowcheckbox">
                      </div></td>
                    <td class="">MSLIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="526" class="rowcheckbox">
                      </div></td>
                    <td class="">VOICE IP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="527" class="rowcheckbox">
                      </div></td>
                    <td class="">Intezar Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="528" class="rowcheckbox">
                      </div></td>
                    <td class="">INTEZAR TELCOM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="529" class="rowcheckbox">
                      </div></td>
                    <td class="">TELCORE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="530" class="rowcheckbox">
                      </div></td>
                    <td class="">MAGIK TELECOM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="531" class="rowcheckbox">
                      </div></td>
                    <td class="">GOLDEN CALL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="532" class="rowcheckbox">
                      </div></td>
                    <td class="">VOIP LINKER</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="533" class="rowcheckbox">
                      </div></td>
                    <td class="">Time up call</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="534" class="rowcheckbox">
                      </div></td>
                    <td class="">Teleonic</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="535" class="rowcheckbox">
                      </div></td>
                    <td class="">Mahmudtel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="536" class="rowcheckbox">
                      </div></td>
                    <td class="">Alina7 VOIP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="537" class="rowcheckbox">
                      </div></td>
                    <td class="">Kryptos Global</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="538" class="rowcheckbox">
                      </div></td>
                    <td class="">AV telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="539" class="rowcheckbox">
                      </div></td>
                    <td class="">Vodatel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="540" class="rowcheckbox">
                      </div></td>
                    <td class="">Connection Technologies LTD.</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="541" class="rowcheckbox">
                      </div></td>
                    <td class="">Cloudcom Incorporated</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="543" class="rowcheckbox">
                      </div></td>
                    <td class="">Nokia tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="544" class="rowcheckbox">
                      </div></td>
                    <td class="">Calling Box</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="545" class="rowcheckbox">
                      </div></td>
                    <td class="">Adams Trading</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="546" class="rowcheckbox">
                      </div></td>
                    <td class="">Amicus Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="548" class="rowcheckbox">
                      </div></td>
                    <td class="">Antako Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="549" class="rowcheckbox">
                      </div></td>
                    <td class="">VoiZAR</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="550" class="rowcheckbox">
                      </div></td>
                    <td class="">Axis Com</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="551" class="rowcheckbox">
                      </div></td>
                    <td class="">Mohammad  Bourji</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="552" class="rowcheckbox">
                      </div></td>
                    <td class="">Excila Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="555" class="rowcheckbox">
                      </div></td>
                    <td class="">FL Connect</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="556" class="rowcheckbox">
                      </div></td>
                    <td class="">FLED</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="557" class="rowcheckbox">
                      </div></td>
                    <td class="">Mediafon Carrier Services,UAB</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="558" class="rowcheckbox">
                      </div></td>
                    <td class="">Pink Voip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="559" class="rowcheckbox">
                      </div></td>
                    <td class="">MMD Smart</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="560" class="rowcheckbox">
                      </div></td>
                    <td class="">Nova Fone</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="561" class="rowcheckbox">
                      </div></td>
                    <td class="">Fast Voiz</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="562" class="rowcheckbox">
                      </div></td>
                    <td class="">VR TELECOM SL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="563" class="rowcheckbox">
                      </div></td>
                    <td class="">BRIGHT TELECOM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="564" class="rowcheckbox">
                      </div></td>
                    <td class="">Anwar Al Taiyyeb Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="566" class="rowcheckbox">
                      </div></td>
                    <td class="">Marconi - Balmo Networks &amp; Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="567" class="rowcheckbox">
                      </div></td>
                    <td class="">Voicelynx</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="568" class="rowcheckbox">
                      </div></td>
                    <td class="">Comdata</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="569" class="rowcheckbox">
                      </div></td>
                    <td class="">GVTL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="570" class="rowcheckbox">
                      </div></td>
                    <td class="">IGlobal Voice</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="571" class="rowcheckbox">
                      </div></td>
                    <td class="">Wosip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="572" class="rowcheckbox">
                      </div></td>
                    <td class="">A2Z GLOBAL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="573" class="rowcheckbox">
                      </div></td>
                    <td class="">Rid Com</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="574" class="rowcheckbox">
                      </div></td>
                    <td class="">Genusys</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="575" class="rowcheckbox">
                      </div></td>
                    <td class="">Deen call</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="576" class="rowcheckbox">
                      </div></td>
                    <td class="">U2Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="577" class="rowcheckbox">
                      </div></td>
                    <td class="">Youthfuture</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="578" class="rowcheckbox">
                      </div></td>
                    <td class="">Si2i</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="579" class="rowcheckbox">
                      </div></td>
                    <td class="">VLEADZ Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="580" class="rowcheckbox">
                      </div></td>
                    <td class="">Cordial Communications</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="582" class="rowcheckbox">
                      </div></td>
                    <td class="">Axiocomm</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="583" class="rowcheckbox">
                      </div></td>
                    <td class="">Core3 Network</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="584" class="rowcheckbox">
                      </div></td>
                    <td class="">RateMax</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="585" class="rowcheckbox">
                      </div></td>
                    <td class="">Arman Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="586" class="rowcheckbox">
                      </div></td>
                    <td class="">Aria Telekom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="587" class="rowcheckbox">
                      </div></td>
                    <td class="">Encore Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="588" class="rowcheckbox">
                      </div></td>
                    <td class="">Heer Teleservices</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="590" class="rowcheckbox">
                      </div></td>
                    <td class="">Trance Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="591" class="rowcheckbox">
                      </div></td>
                    <td class="">XOtel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="592" class="rowcheckbox">
                      </div></td>
                    <td class="">Telecall</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="593" class="rowcheckbox">
                      </div></td>
                    <td class="">Mediatel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="594" class="rowcheckbox">
                      </div></td>
                    <td class="">Gencom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="595" class="rowcheckbox">
                      </div></td>
                    <td class="">MS-Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="596" class="rowcheckbox">
                      </div></td>
                    <td class="">Quadcom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="597" class="rowcheckbox">
                      </div></td>
                    <td class="">Brother Comm</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="598" class="rowcheckbox">
                      </div></td>
                    <td class="">Telekom Asia</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="599" class="rowcheckbox">
                      </div></td>
                    <td class="">Fountain Panel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="600" class="rowcheckbox">
                      </div></td>
                    <td class="">BTS</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="601" class="rowcheckbox">
                      </div></td>
                    <td class="">TIGO GLOBAL COMMUNICATIONS</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="602" class="rowcheckbox">
                      </div></td>
                    <td class="">Singitel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="603" class="rowcheckbox">
                      </div></td>
                    <td class="">Justcall</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="604" class="rowcheckbox">
                      </div></td>
                    <td class="">MyCountry Mobile</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="605" class="rowcheckbox">
                      </div></td>
                    <td class="">Boila Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="606" class="rowcheckbox">
                      </div></td>
                    <td class="">Boila Tel </td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="607" class="rowcheckbox">
                      </div></td>
                    <td class="">Mehriatel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="608" class="rowcheckbox">
                      </div></td>
                    <td class="">AHMSERVICESINC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="609" class="rowcheckbox">
                      </div></td>
                    <td class="">Shine Town</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="610" class="rowcheckbox">
                      </div></td>
                    <td class="">LIGA Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="611" class="rowcheckbox">
                      </div></td>
                    <td class="">AlxTel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="612" class="rowcheckbox">
                      </div></td>
                    <td class="">BADR VOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="613" class="rowcheckbox">
                      </div></td>
                    <td class="">One world Voip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="614" class="rowcheckbox">
                      </div></td>
                    <td class="">382 Communications</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="615" class="rowcheckbox">
                      </div></td>
                    <td class="">Cyber-Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="616" class="rowcheckbox">
                      </div></td>
                    <td class="">Gutso</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="617" class="rowcheckbox">
                      </div></td>
                    <td class="">SICA</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="618" class="rowcheckbox">
                      </div></td>
                    <td class="">UVTelecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="620" class="rowcheckbox">
                      </div></td>
                    <td class="">CogniTell</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="621" class="rowcheckbox">
                      </div></td>
                    <td class="">AMITG LTD</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="622" class="rowcheckbox">
                      </div></td>
                    <td class="">Callslink Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="625" class="rowcheckbox">
                      </div></td>
                    <td class="">Mobiglobe</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="626" class="rowcheckbox">
                      </div></td>
                    <td class="">Amantel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="627" class="rowcheckbox">
                      </div></td>
                    <td class="">Zafartel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="630" class="rowcheckbox">
                      </div></td>
                    <td class="">Way2Call</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="631" class="rowcheckbox">
                      </div></td>
                    <td class="">Techstatic</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="632" class="rowcheckbox">
                      </div></td>
                    <td class="">Bernet SC</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="633" class="rowcheckbox">
                      </div></td>
                    <td class="">Talk2all</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="634" class="rowcheckbox">
                      </div></td>
                    <td class="">VP Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="635" class="rowcheckbox">
                      </div></td>
                    <td class="">FMVOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="636" class="rowcheckbox">
                      </div></td>
                    <td class="">Sip Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="637" class="rowcheckbox">
                      </div></td>
                    <td class="">Digital Line</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="638" class="rowcheckbox">
                      </div></td>
                    <td class="">Next Page</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="639" class="rowcheckbox">
                      </div></td>
                    <td class="">SalamTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="640" class="rowcheckbox">
                      </div></td>
                    <td class="">SK VOIP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="641" class="rowcheckbox">
                      </div></td>
                    <td class="">Sipstatus Corp</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="642" class="rowcheckbox">
                      </div></td>
                    <td class="">Star GroupA-Z</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="643" class="rowcheckbox">
                      </div></td>
                    <td class="">WaveCrest</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="644" class="rowcheckbox">
                      </div></td>
                    <td class="">Pioneer Trading</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="647" class="rowcheckbox">
                      </div></td>
                    <td class="">JTGlobal</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="648" class="rowcheckbox">
                      </div></td>
                    <td class="">MEGA VOIP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="649" class="rowcheckbox">
                      </div></td>
                    <td class="">ABC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="650" class="rowcheckbox">
                      </div></td>
                    <td class="">Monday</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="653" class="rowcheckbox">
                      </div></td>
                    <td class="">InterTechnic</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="654" class="rowcheckbox">
                      </div></td>
                    <td class="">Videocon Telecommunications Limited</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="656" class="rowcheckbox">
                      </div></td>
                    <td class="">HansaTelecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="660" class="rowcheckbox">
                      </div></td>
                    <td class="">Uno Communication</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="661" class="rowcheckbox">
                      </div></td>
                    <td class="">Genexinfocom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="663" class="rowcheckbox">
                      </div></td>
                    <td class="">Datora</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="664" class="rowcheckbox">
                      </div></td>
                    <td class="">JanaCom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="668" class="rowcheckbox">
                      </div></td>
                    <td class="">Gi Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="673" class="rowcheckbox">
                      </div></td>
                    <td class="">Promac solutions</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="674" class="rowcheckbox">
                      </div></td>
                    <td class="">Hilf telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="676" class="rowcheckbox">
                      </div></td>
                    <td class="">SRG Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="678" class="rowcheckbox">
                      </div></td>
                    <td class="">Aircel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="679" class="rowcheckbox">
                      </div></td>
                    <td class="">AirTelecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="680" class="rowcheckbox">
                      </div></td>
                    <td class="">Tidal</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="681" class="rowcheckbox">
                      </div></td>
                    <td class="">Connecto Group</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="682" class="rowcheckbox">
                      </div></td>
                    <td class="">Libyatele</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="683" class="rowcheckbox">
                      </div></td>
                    <td class="">PTGi</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="684" class="rowcheckbox">
                      </div></td>
                    <td class="">US Matrix</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="687" class="rowcheckbox">
                      </div></td>
                    <td class="">Callbuddy</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="689" class="rowcheckbox">
                      </div></td>
                    <td class="">Blue-Berry</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="690" class="rowcheckbox">
                      </div></td>
                    <td class="">Teknolab</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="691" class="rowcheckbox">
                      </div></td>
                    <td class="">Electron Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="692" class="rowcheckbox">
                      </div></td>
                    <td class="">Voizcom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="694" class="rowcheckbox">
                      </div></td>
                    <td class="">Mashal</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="695" class="rowcheckbox">
                      </div></td>
                    <td class="">BT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="696" class="rowcheckbox">
                      </div></td>
                    <td class="">DIALGLOBE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="698" class="rowcheckbox">
                      </div></td>
                    <td class="">Immense Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="699" class="rowcheckbox">
                      </div></td>
                    <td class="">Vesper</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="700" class="rowcheckbox">
                      </div></td>
                    <td class="">Go Call</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="714" class="rowcheckbox">
                      </div></td>
                    <td class="">Voice Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="720" class="rowcheckbox">
                      </div></td>
                    <td class="">SNR Group</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="721" class="rowcheckbox">
                      </div></td>
                    <td class="">Nexcess Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="722" class="rowcheckbox">
                      </div></td>
                    <td class="">Telcopath</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="723" class="rowcheckbox">
                      </div></td>
                    <td class="">Globe Plus</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="724" class="rowcheckbox">
                      </div></td>
                    <td class="">Best Callshop</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="725" class="rowcheckbox">
                      </div></td>
                    <td class="">Ember Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="726" class="rowcheckbox">
                      </div></td>
                    <td class="">Zen Talk</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="727" class="rowcheckbox">
                      </div></td>
                    <td class="">Business24hours</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="728" class="rowcheckbox">
                      </div></td>
                    <td class="">GTSH Limited</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="729" class="rowcheckbox">
                      </div></td>
                    <td class="">Voxmage</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="731" class="rowcheckbox">
                      </div></td>
                    <td class="">Paramtel Solutions (Axis Convergence)</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="732" class="rowcheckbox">
                      </div></td>
                    <td class="">Excelcom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="736" class="rowcheckbox">
                      </div></td>
                    <td class="">PRTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="737" class="rowcheckbox">
                      </div></td>
                    <td class="">Prolinks</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="738" class="rowcheckbox">
                      </div></td>
                    <td class="">Voipvize</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="740" class="rowcheckbox">
                      </div></td>
                    <td class="">VoxPace</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="741" class="rowcheckbox">
                      </div></td>
                    <td class="">ISRAJ VOICE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="742" class="rowcheckbox">
                      </div></td>
                    <td class="">Shabeer Voip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="743" class="rowcheckbox">
                      </div></td>
                    <td class="">AudioTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="744" class="rowcheckbox">
                      </div></td>
                    <td class="">ORB Telemedia</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="745" class="rowcheckbox">
                      </div></td>
                    <td class="">Scaffnet PTE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="746" class="rowcheckbox">
                      </div></td>
                    <td class="">ACTS Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="748" class="rowcheckbox">
                      </div></td>
                    <td class="">GCN TELECOM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="749" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Connect</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="750" class="rowcheckbox">
                      </div></td>
                    <td class="">Nas Point</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="751" class="rowcheckbox">
                      </div></td>
                    <td class="">Fancy Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="752" class="rowcheckbox">
                      </div></td>
                    <td class="">RTG-Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="753" class="rowcheckbox">
                      </div></td>
                    <td class="">Local Phone</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="754" class="rowcheckbox">
                      </div></td>
                    <td class="">Green Solution</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="755" class="rowcheckbox">
                      </div></td>
                    <td class="">Bluestartelecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="757" class="rowcheckbox">
                      </div></td>
                    <td class="">EZTELCO</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="758" class="rowcheckbox">
                      </div></td>
                    <td class="">ICX</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="759" class="rowcheckbox">
                      </div></td>
                    <td class="">EasyWay</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="760" class="rowcheckbox">
                      </div></td>
                    <td class="">DXB</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="761" class="rowcheckbox">
                      </div></td>
                    <td class="">CallBiz</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="762" class="rowcheckbox">
                      </div></td>
                    <td class="">Banglatel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="763" class="rowcheckbox">
                      </div></td>
                    <td class="">JoodTelecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="764" class="rowcheckbox">
                      </div></td>
                    <td class="">Star Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="765" class="rowcheckbox">
                      </div></td>
                    <td class="">AER Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="766" class="rowcheckbox">
                      </div></td>
                    <td class="">CYBIZ</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="767" class="rowcheckbox">
                      </div></td>
                    <td class="">Voice Courier</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="768" class="rowcheckbox">
                      </div></td>
                    <td class="">Stella Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="769" class="rowcheckbox">
                      </div></td>
                    <td class="">Fahadtel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="771" class="rowcheckbox">
                      </div></td>
                    <td class="">WIZ</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="772" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Skyline</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="773" class="rowcheckbox">
                      </div></td>
                    <td class="">Aim</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="774" class="rowcheckbox">
                      </div></td>
                    <td class="">Vivitel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="775" class="rowcheckbox">
                      </div></td>
                    <td class="">CAVOXNET Inc</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="776" class="rowcheckbox">
                      </div></td>
                    <td class="">CommHub</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="778" class="rowcheckbox">
                      </div></td>
                    <td class="">FineFone</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="779" class="rowcheckbox">
                      </div></td>
                    <td class="">AMC-Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="780" class="rowcheckbox">
                      </div></td>
                    <td class="">Madeenatel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="781" class="rowcheckbox">
                      </div></td>
                    <td class="">Aeris-Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="782" class="rowcheckbox">
                      </div></td>
                    <td class="">Upstream Carrier</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="783" class="rowcheckbox">
                      </div></td>
                    <td class="">Tooways</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="784" class="rowcheckbox">
                      </div></td>
                    <td class="">STARSSIP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="785" class="rowcheckbox">
                      </div></td>
                    <td class="">ALSOUT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="786" class="rowcheckbox">
                      </div></td>
                    <td class="">VoipNpr</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="788" class="rowcheckbox">
                      </div></td>
                    <td class="">SPEAK2CALL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="789" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Routes</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="790" class="rowcheckbox">
                      </div></td>
                    <td class="">Flexhar</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="791" class="rowcheckbox">
                      </div></td>
                    <td class="">L2MC</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="792" class="rowcheckbox">
                      </div></td>
                    <td class="">PakPolite</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="794" class="rowcheckbox">
                      </div></td>
                    <td class="">BTL Group</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="795" class="rowcheckbox">
                      </div></td>
                    <td class="">Alvinex</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="796" class="rowcheckbox">
                      </div></td>
                    <td class="">CallsAvoice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="798" class="rowcheckbox">
                      </div></td>
                    <td class="">Alfa Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="799" class="rowcheckbox">
                      </div></td>
                    <td class="">Dialtel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="804" class="rowcheckbox">
                      </div></td>
                    <td class="">VOWSOFT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="805" class="rowcheckbox">
                      </div></td>
                    <td class="">Instant Hubbing</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="806" class="rowcheckbox">
                      </div></td>
                    <td class="">Next Solution</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="807" class="rowcheckbox">
                      </div></td>
                    <td class="">Nobel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="808" class="rowcheckbox">
                      </div></td>
                    <td class="">Boraklink</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="820" class="rowcheckbox">
                      </div></td>
                    <td class="">AeeZee</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="834" class="rowcheckbox">
                      </div></td>
                    <td class="">BESTCALL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="931" class="rowcheckbox">
                      </div></td>
                    <td class="">Nexzen</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="933" class="rowcheckbox">
                      </div></td>
                    <td class="">NOMAD COM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="945" class="rowcheckbox">
                      </div></td>
                    <td class="">PositiveD-BUY</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="955" class="rowcheckbox">
                      </div></td>
                    <td class="">Robert</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="995" class="rowcheckbox">
                      </div></td>
                    <td class="">TESTING supplier</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1007" class="rowcheckbox">
                      </div></td>
                    <td class="">Vinculum communication - Vintalk</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1021" class="rowcheckbox">
                      </div></td>
                    <td class="">VoicePace</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1022" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip2Internet</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1027" class="rowcheckbox">
                      </div></td>
                    <td class="">VR Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1044" class="rowcheckbox">
                      </div></td>
                    <td class="">TEST  DEV  VENDOR  ACCOUNT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1058" class="rowcheckbox">
                      </div></td>
                    <td class="">Beep Life</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1060" class="rowcheckbox">
                      </div></td>
                    <td class="">Manana Trading</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1061" class="rowcheckbox">
                      </div></td>
                    <td class="">PROBASHI TEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1062" class="rowcheckbox">
                      </div></td>
                    <td class="">AJWATEL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1063" class="rowcheckbox">
                      </div></td>
                    <td class="">FAAZCO</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1064" class="rowcheckbox">
                      </div></td>
                    <td class="">Naxla</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1065" class="rowcheckbox">
                      </div></td>
                    <td class="">BSA COM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1066" class="rowcheckbox">
                      </div></td>
                    <td class="">Manshib Telecom Limited</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1068" class="rowcheckbox">
                      </div></td>
                    <td class="">LOGIK COMMUNICATIONS</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1070" class="rowcheckbox">
                      </div></td>
                    <td class="">Energetix</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1071" class="rowcheckbox">
                      </div></td>
                    <td class="">OneTouch Technology</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1072" class="rowcheckbox">
                      </div></td>
                    <td class="">Red Sky Global</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1073" class="rowcheckbox">
                      </div></td>
                    <td class="">API Connect</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1074" class="rowcheckbox">
                      </div></td>
                    <td class="">Fighter Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1076" class="rowcheckbox">
                      </div></td>
                    <td class="">Red Tele</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1078" class="rowcheckbox">
                      </div></td>
                    <td class="">INT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1079" class="rowcheckbox">
                      </div></td>
                    <td class="">ASIA INTEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1080" class="rowcheckbox">
                      </div></td>
                    <td class="">Orbitel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1081" class="rowcheckbox">
                      </div></td>
                    <td class="">GO 4 BIZ</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1082" class="rowcheckbox">
                      </div></td>
                    <td class="">Voiceestimate</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1083" class="rowcheckbox">
                      </div></td>
                    <td class="">Solutions9</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1084" class="rowcheckbox">
                      </div></td>
                    <td class="">TNT</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1085" class="rowcheckbox">
                      </div></td>
                    <td class="">RIZ-IPTEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1086" class="rowcheckbox">
                      </div></td>
                    <td class="">ASHABIA</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1087" class="rowcheckbox">
                      </div></td>
                    <td class="">Voiceworld HK</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1088" class="rowcheckbox">
                      </div></td>
                    <td class="">TANVEER</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1089" class="rowcheckbox">
                      </div></td>
                    <td class="">Inspire Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1090" class="rowcheckbox">
                      </div></td>
                    <td class="">Mobtel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1091" class="rowcheckbox">
                      </div></td>
                    <td class="">Vox-Beam</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1092" class="rowcheckbox">
                      </div></td>
                    <td class="">NGN</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1093" class="rowcheckbox">
                      </div></td>
                    <td class="">Symphonictel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1094" class="rowcheckbox">
                      </div></td>
                    <td class="">FDI</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1095" class="rowcheckbox">
                      </div></td>
                    <td class="">SCT</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1096" class="rowcheckbox">
                      </div></td>
                    <td class="">Bestvoip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1097" class="rowcheckbox">
                      </div></td>
                    <td class="">Hafsa-835</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1098" class="rowcheckbox">
                      </div></td>
                    <td class="">Pricom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1099" class="rowcheckbox">
                      </div></td>
                    <td class="">Kingtel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1101" class="rowcheckbox">
                      </div></td>
                    <td class="">Fonza</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1102" class="rowcheckbox">
                      </div></td>
                    <td class="">Trident Global</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1103" class="rowcheckbox">
                      </div></td>
                    <td class="">Bitz Communications</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1104" class="rowcheckbox">
                      </div></td>
                    <td class="">Telitune</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1105" class="rowcheckbox">
                      </div></td>
                    <td class="">GM Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1106" class="rowcheckbox">
                      </div></td>
                    <td class="">Ridavoip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1107" class="rowcheckbox">
                      </div></td>
                    <td class="">VOIP AXIS</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1108" class="rowcheckbox">
                      </div></td>
                    <td class="">MH Services</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1109" class="rowcheckbox">
                      </div></td>
                    <td class="">Letztalk</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1110" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Noori</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1111" class="rowcheckbox">
                      </div></td>
                    <td class="">Inaani</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1112" class="rowcheckbox">
                      </div></td>
                    <td class="">Shayona Global</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1113" class="rowcheckbox">
                      </div></td>
                    <td class="">Unicorn</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1114" class="rowcheckbox">
                      </div></td>
                    <td class="">Twist Communications</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1115" class="rowcheckbox">
                      </div></td>
                    <td class="">Telebiz</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1116" class="rowcheckbox">
                      </div></td>
                    <td class="">test customer (do not delete)</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1117" class="rowcheckbox">
                      </div></td>
                    <td class="">DNR TECHNOLOGY</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1118" class="rowcheckbox">
                      </div></td>
                    <td class="">Cascade Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1119" class="rowcheckbox">
                      </div></td>
                    <td class="">Ekushe Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1120" class="rowcheckbox">
                      </div></td>
                    <td class="">Toptel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1121" class="rowcheckbox">
                      </div></td>
                    <td class="">Arabnetglobal</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1122" class="rowcheckbox">
                      </div></td>
                    <td class="">My5Call</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1123" class="rowcheckbox">
                      </div></td>
                    <td class="">Talkshawk</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1124" class="rowcheckbox">
                      </div></td>
                    <td class="">Musa Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1125" class="rowcheckbox">
                      </div></td>
                    <td class="">Roger</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1127" class="rowcheckbox">
                      </div></td>
                    <td class="">Rush Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1128" class="rowcheckbox">
                      </div></td>
                    <td class="">Raj Telecommunication</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1129" class="rowcheckbox">
                      </div></td>
                    <td class="">Voip Platinum</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1130" class="rowcheckbox">
                      </div></td>
                    <td class="">Ishfaq Voip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1131" class="rowcheckbox">
                      </div></td>
                    <td class="">IVON</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1132" class="rowcheckbox">
                      </div></td>
                    <td class="">Hfonica</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1133" class="rowcheckbox">
                      </div></td>
                    <td class="">Gsoft</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1134" class="rowcheckbox">
                      </div></td>
                    <td class="">VOIP and Voip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1135" class="rowcheckbox">
                      </div></td>
                    <td class="">OneAce</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1136" class="rowcheckbox">
                      </div></td>
                    <td class="">RafeTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1137" class="rowcheckbox">
                      </div></td>
                    <td class="">ETELIX</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1138" class="rowcheckbox">
                      </div></td>
                    <td class="">VoIPNeed</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1139" class="rowcheckbox">
                      </div></td>
                    <td class="">Calling Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1140" class="rowcheckbox">
                      </div></td>
                    <td class="">Call Login</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1142" class="rowcheckbox">
                      </div></td>
                    <td class="">AMJ TEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1144" class="rowcheckbox">
                      </div></td>
                    <td class="">AR Technologies</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1145" class="rowcheckbox">
                      </div></td>
                    <td class="">Iaccess</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1146" class="rowcheckbox">
                      </div></td>
                    <td class="">Laccess</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1147" class="rowcheckbox">
                      </div></td>
                    <td class="">Andday Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1148" class="rowcheckbox">
                      </div></td>
                    <td class="">VOIPCELL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1149" class="rowcheckbox">
                      </div></td>
                    <td class="">Manzar BB</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1150" class="rowcheckbox">
                      </div></td>
                    <td class="">RN TELECOM</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1151" class="rowcheckbox">
                      </div></td>
                    <td class="">NEWTEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1152" class="rowcheckbox">
                      </div></td>
                    <td class="">Federar Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1153" class="rowcheckbox">
                      </div></td>
                    <td class="">Vikky Plus</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1154" class="rowcheckbox">
                      </div></td>
                    <td class="">GreenTrack</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1155" class="rowcheckbox">
                      </div></td>
                    <td class="">Dubai Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1156" class="rowcheckbox">
                      </div></td>
                    <td class="">Shezitel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1157" class="rowcheckbox">
                      </div></td>
                    <td class="">ConfiarTech</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1158" class="rowcheckbox">
                      </div></td>
                    <td class="">IconicTelecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1159" class="rowcheckbox">
                      </div></td>
                    <td class="">Sahara Group</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1160" class="rowcheckbox">
                      </div></td>
                    <td class="">VDigit</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1161" class="rowcheckbox">
                      </div></td>
                    <td class="">Moov India</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1162" class="rowcheckbox">
                      </div></td>
                    <td class="">MinaTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1163" class="rowcheckbox">
                      </div></td>
                    <td class="">WaveAsia</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1164" class="rowcheckbox">
                      </div></td>
                    <td class="">WanaTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1165" class="rowcheckbox">
                      </div></td>
                    <td class="">Vocal Telecom FZE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1166" class="rowcheckbox">
                      </div></td>
                    <td class="">Matrix Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1167" class="rowcheckbox">
                      </div></td>
                    <td class="">CHOICETEC</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1168" class="rowcheckbox">
                      </div></td>
                    <td class="">In2Net Tech</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1169" class="rowcheckbox">
                      </div></td>
                    <td class="">Owishi Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1170" class="rowcheckbox">
                      </div></td>
                    <td class="">Voic3liknz</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1171" class="rowcheckbox">
                      </div></td>
                    <td class="">CallBlue</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1172" class="rowcheckbox">
                      </div></td>
                    <td class="">Rain Comm</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1173" class="rowcheckbox">
                      </div></td>
                    <td class="">Mimtel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1174" class="rowcheckbox">
                      </div></td>
                    <td class="">Vehron Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1175" class="rowcheckbox">
                      </div></td>
                    <td class="">CTN Voice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1176" class="rowcheckbox">
                      </div></td>
                    <td class="">Centavo</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1180" class="rowcheckbox">
                      </div></td>
                    <td class="">88 Goodmayes Road</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1181" class="rowcheckbox">
                      </div></td>
                    <td class="">LiveTone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1182" class="rowcheckbox">
                      </div></td>
                    <td class="">Khan Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1183" class="rowcheckbox">
                      </div></td>
                    <td class="">ALMVOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1266" class="rowcheckbox">
                      </div></td>
                    <td class="">ICC NETWORKS</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1267" class="rowcheckbox">
                      </div></td>
                    <td class="">Imran Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1268" class="rowcheckbox">
                      </div></td>
                    <td class="">KATZ TEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1269" class="rowcheckbox">
                      </div></td>
                    <td class="">MadeenPlus</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1272" class="rowcheckbox">
                      </div></td>
                    <td class="">Cbizuk</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1273" class="rowcheckbox">
                      </div></td>
                    <td class="">Best Cal</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1274" class="rowcheckbox">
                      </div></td>
                    <td class="">EAST Avenue Telecom LTD</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1281" class="rowcheckbox">
                      </div></td>
                    <td class="">DooraFone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1285" class="rowcheckbox">
                      </div></td>
                    <td class="">SMTEC</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1304" class="rowcheckbox">
                      </div></td>
                    <td class="">Welcome VOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1313" class="rowcheckbox">
                      </div></td>
                    <td class="">AFK TRADER</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1315" class="rowcheckbox">
                      </div></td>
                    <td class="">NECXON</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1316" class="rowcheckbox">
                      </div></td>
                    <td class="">Namastelco</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1320" class="rowcheckbox">
                      </div></td>
                    <td class="">Fear Services</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1321" class="rowcheckbox">
                      </div></td>
                    <td class="">Starlink Communication</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1322" class="rowcheckbox">
                      </div></td>
                    <td class="">INDIGO11 SERVICES</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1323" class="rowcheckbox">
                      </div></td>
                    <td class="">Limecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1324" class="rowcheckbox">
                      </div></td>
                    <td class="">Reem Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1325" class="rowcheckbox">
                      </div></td>
                    <td class="">Optimus telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1327" class="rowcheckbox">
                      </div></td>
                    <td class="">MYVOIP SOLUTION</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1328" class="rowcheckbox">
                      </div></td>
                    <td class="">IMTelecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1329" class="rowcheckbox">
                      </div></td>
                    <td class="">VEGA Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1330" class="rowcheckbox">
                      </div></td>
                    <td class="">Akash Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1331" class="rowcheckbox">
                      </div></td>
                    <td class="">KDDI GLOBAL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1335" class="rowcheckbox">
                      </div></td>
                    <td class="">Quantum Global Communication</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1336" class="rowcheckbox">
                      </div></td>
                    <td class="">Easy communication</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1337" class="rowcheckbox">
                      </div></td>
                    <td class="">ONE POINT</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1338" class="rowcheckbox">
                      </div></td>
                    <td class="">Zaf Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1339" class="rowcheckbox">
                      </div></td>
                    <td class="">HD DATA Network</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1340" class="rowcheckbox">
                      </div></td>
                    <td class="">Saronac</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1341" class="rowcheckbox">
                      </div></td>
                    <td class="">MASKTEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1342" class="rowcheckbox">
                      </div></td>
                    <td class="">Amain System Networks</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1343" class="rowcheckbox">
                      </div></td>
                    <td class="">Connect2nation</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1344" class="rowcheckbox">
                      </div></td>
                    <td class="">Voiz India</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1345" class="rowcheckbox">
                      </div></td>
                    <td class="">Worldwide Network</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1346" class="rowcheckbox">
                      </div></td>
                    <td class="">We-Talk Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1347" class="rowcheckbox">
                      </div></td>
                    <td class="">FAISAL TEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1348" class="rowcheckbox">
                      </div></td>
                    <td class="">Vonca Communications</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1349" class="rowcheckbox">
                      </div></td>
                    <td class="">MOBTEL A2Z</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1350" class="rowcheckbox">
                      </div></td>
                    <td class="">MARWATECH</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1351" class="rowcheckbox">
                      </div></td>
                    <td class="">ALVINEX LTD</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1352" class="rowcheckbox">
                      </div></td>
                    <td class="">Jamir Express</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1354" class="rowcheckbox">
                      </div></td>
                    <td class="">TASMI COMMUNICATION</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1355" class="rowcheckbox">
                      </div></td>
                    <td class="">Komsumuz</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1356" class="rowcheckbox">
                      </div></td>
                    <td class="">FASTCALL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1357" class="rowcheckbox">
                      </div></td>
                    <td class="">Call2phone</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1358" class="rowcheckbox">
                      </div></td>
                    <td class="">VoipParadise</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1359" class="rowcheckbox">
                      </div></td>
                    <td class="">Get A Call</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1360" class="rowcheckbox">
                      </div></td>
                    <td class="">SADIQUE UAE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1363" class="rowcheckbox">
                      </div></td>
                    <td class="">Smile bit</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1391" class="rowcheckbox">
                      </div></td>
                    <td class="">AT IT</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1392" class="rowcheckbox">
                      </div></td>
                    <td class="">TTCvoice</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1393" class="rowcheckbox">
                      </div></td>
                    <td class="">Jabeel Voice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1394" class="rowcheckbox">
                      </div></td>
                    <td class="">Saimd Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1395" class="rowcheckbox">
                      </div></td>
                    <td class="">Ringberry</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1396" class="rowcheckbox">
                      </div></td>
                    <td class="">Delco Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1397" class="rowcheckbox">
                      </div></td>
                    <td class="">Amtelfone</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1402" class="rowcheckbox">
                      </div></td>
                    <td class="">Spinzar tech</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1403" class="rowcheckbox">
                      </div></td>
                    <td class="">REMIX TEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1404" class="rowcheckbox">
                      </div></td>
                    <td class="">REMIX TEL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1405" class="rowcheckbox">
                      </div></td>
                    <td class="">Vconnect</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1406" class="rowcheckbox">
                      </div></td>
                    <td class="">Flame Call</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1407" class="rowcheckbox">
                      </div></td>
                    <td class="">ECO CALLP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1408" class="rowcheckbox">
                      </div></td>
                    <td class="">Royal Call</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1410" class="rowcheckbox">
                      </div></td>
                    <td class="">TELELINK BD COMMUNICATION</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1411" class="rowcheckbox">
                      </div></td>
                    <td class="">Jasir Com</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1412" class="rowcheckbox">
                      </div></td>
                    <td class="">NODI TELECOM</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1414" class="rowcheckbox">
                      </div></td>
                    <td class="">Dev Test Account</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1415" class="rowcheckbox">
                      </div></td>
                    <td class="">Simba Voice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1416" class="rowcheckbox">
                      </div></td>
                    <td class="">Sastha Wave</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1418" class="rowcheckbox">
                      </div></td>
                    <td class="">First Communications</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1419" class="rowcheckbox">
                      </div></td>
                    <td class="">Wavetel Retail</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1420" class="rowcheckbox">
                      </div></td>
                    <td class="">Skywardtel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1421" class="rowcheckbox">
                      </div></td>
                    <td class="">MyRouteShop</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1422" class="rowcheckbox">
                      </div></td>
                    <td class="">Brevox Global</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1423" class="rowcheckbox">
                      </div></td>
                    <td class="">Link-Technology</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1424" class="rowcheckbox">
                      </div></td>
                    <td class="">Rene-Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1425" class="rowcheckbox">
                      </div></td>
                    <td class="">DialoVOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1426" class="rowcheckbox">
                      </div></td>
                    <td class="">QBTEL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1427" class="rowcheckbox">
                      </div></td>
                    <td class="">AbdullahTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1428" class="rowcheckbox">
                      </div></td>
                    <td class="">KingdomVoip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1430" class="rowcheckbox">
                      </div></td>
                    <td class="">JR Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1431" class="rowcheckbox">
                      </div></td>
                    <td class="">NasTel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1432" class="rowcheckbox">
                      </div></td>
                    <td class="">LeedTel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1433" class="rowcheckbox">
                      </div></td>
                    <td class="">Bhavna Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1434" class="rowcheckbox">
                      </div></td>
                    <td class="">PhoneXP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1435" class="rowcheckbox">
                      </div></td>
                    <td class="">DIVULGITELE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1436" class="rowcheckbox">
                      </div></td>
                    <td class="">Industrial Design And Telecom  Services LLC</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1437" class="rowcheckbox">
                      </div></td>
                    <td class="">NR Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1438" class="rowcheckbox">
                      </div></td>
                    <td class="">Bh Yaser</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1439" class="rowcheckbox">
                      </div></td>
                    <td class="">MH Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1440" class="rowcheckbox">
                      </div></td>
                    <td class="">Links Technology</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1441" class="rowcheckbox">
                      </div></td>
                    <td class="">Qatar Voice</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1442" class="rowcheckbox">
                      </div></td>
                    <td class="">GETelecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1443" class="rowcheckbox">
                      </div></td>
                    <td class="">Ladlatelecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1444" class="rowcheckbox">
                      </div></td>
                    <td class="">Mehedi Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1445" class="rowcheckbox">
                      </div></td>
                    <td class="">Oneiro</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1446" class="rowcheckbox">
                      </div></td>
                    <td class="">Trusted Network</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1447" class="rowcheckbox">
                      </div></td>
                    <td class="">2WayGolden</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1448" class="rowcheckbox">
                      </div></td>
                    <td class="">Space Network</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1449" class="rowcheckbox">
                      </div></td>
                    <td class="">Rocket Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1450" class="rowcheckbox">
                      </div></td>
                    <td class="">Zihan Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1451" class="rowcheckbox">
                      </div></td>
                    <td class="">Trulynx</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1452" class="rowcheckbox">
                      </div></td>
                    <td class="">Log Flip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1453" class="rowcheckbox">
                      </div></td>
                    <td class="">Advance Vision</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1454" class="rowcheckbox">
                      </div></td>
                    <td class="">BFT Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1455" class="rowcheckbox">
                      </div></td>
                    <td class="">ISSL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1456" class="rowcheckbox">
                      </div></td>
                    <td class="">Global Track</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1457" class="rowcheckbox">
                      </div></td>
                    <td class="">IDTS GLOBAL</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1458" class="rowcheckbox">
                      </div></td>
                    <td class="">Yasir Tel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1459" class="rowcheckbox">
                      </div></td>
                    <td class="">DJ Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1460" class="rowcheckbox">
                      </div></td>
                    <td class="">AY Technologies</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1462" class="rowcheckbox">
                      </div></td>
                    <td class="">YesTelcom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1463" class="rowcheckbox">
                      </div></td>
                    <td class="">VISAVOIZ</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1464" class="rowcheckbox">
                      </div></td>
                    <td class="">NazirTel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1465" class="rowcheckbox">
                      </div></td>
                    <td class="">GTECH</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1466" class="rowcheckbox">
                      </div></td>
                    <td class="">Nucleus Networks</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1467" class="rowcheckbox">
                      </div></td>
                    <td class="">GTNS VOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1468" class="rowcheckbox">
                      </div></td>
                    <td class="">BurjTelecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1469" class="rowcheckbox">
                      </div></td>
                    <td class="">VIO Voice</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1470" class="rowcheckbox">
                      </div></td>
                    <td class="">IPN</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1471" class="rowcheckbox">
                      </div></td>
                    <td class="">DS Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1473" class="rowcheckbox">
                      </div></td>
                    <td class="">Madinafone</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1474" class="rowcheckbox">
                      </div></td>
                    <td class="">ORANGE ESP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1475" class="rowcheckbox">
                      </div></td>
                    <td class="">Global telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1476" class="rowcheckbox">
                      </div></td>
                    <td class="">WireGlobe</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1477" class="rowcheckbox">
                      </div></td>
                    <td class="">Metrovoiz</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1478" class="rowcheckbox">
                      </div></td>
                    <td class="">ComAdvance</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1479" class="rowcheckbox">
                      </div></td>
                    <td class="">Gowin Telecommunications(HK) Limited</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1480" class="rowcheckbox">
                      </div></td>
                    <td class="">Flavien</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1481" class="rowcheckbox">
                      </div></td>
                    <td class="">Space Tele Services</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1482" class="rowcheckbox">
                      </div></td>
                    <td class="">Crystal Soft Solutions</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1483" class="rowcheckbox">
                      </div></td>
                    <td class="">Afruza Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1484" class="rowcheckbox">
                      </div></td>
                    <td class="">GMS</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1485" class="rowcheckbox">
                      </div></td>
                    <td class="">Riyaz Tel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1486" class="rowcheckbox">
                      </div></td>
                    <td class="">Sharp Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1487" class="rowcheckbox">
                      </div></td>
                    <td class="">Carrier1</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1488" class="rowcheckbox">
                      </div></td>
                    <td class="">Borsha</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1489" class="rowcheckbox">
                      </div></td>
                    <td class="">Suma BD</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1490" class="rowcheckbox">
                      </div></td>
                    <td class="">Gvantage Technologies</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1491" class="rowcheckbox">
                      </div></td>
                    <td class="">Sagor Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1492" class="rowcheckbox">
                      </div></td>
                    <td class="">HILAACTEL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1493" class="rowcheckbox">
                      </div></td>
                    <td class="">Hellovoip</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1494" class="rowcheckbox">
                      </div></td>
                    <td class="">KGL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1495" class="rowcheckbox">
                      </div></td>
                    <td class="">Smart Tech</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1496" class="rowcheckbox">
                      </div></td>
                    <td class="">OPTION TECH</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1497" class="rowcheckbox">
                      </div></td>
                    <td class="">Viber</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1498" class="rowcheckbox">
                      </div></td>
                    <td class="">VoxBridge</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1499" class="rowcheckbox">
                      </div></td>
                    <td class="">RASHEDAUTOFLEXI</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1501" class="rowcheckbox">
                      </div></td>
                    <td class="">Grain VOIP</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1503" class="rowcheckbox">
                      </div></td>
                    <td class="">ABC2</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1505" class="rowcheckbox">
                      </div></td>
                    <td class="">Neekava PTE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1506" class="rowcheckbox">
                      </div></td>
                    <td class="">Janani Express</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1507" class="rowcheckbox">
                      </div></td>
                    <td class="">Mohammed Telecom</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1508" class="rowcheckbox">
                      </div></td>
                    <td class="">NITBD</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="1509" class="rowcheckbox">
                      </div></td>
                    <td class="">D Globe</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3096" class="rowcheckbox">
                      </div></td>
                    <td class="">GM ENTERPRISE</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3097" class="rowcheckbox">
                      </div></td>
                    <td class="">RIGALO PTE</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3098" class="rowcheckbox">
                      </div></td>
                    <td class="">Smart World Telecom</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3099" class="rowcheckbox">
                      </div></td>
                    <td class="">AMCTEL SIA</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3100" class="rowcheckbox">
                      </div></td>
                    <td class="">GTax Networks</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3101" class="rowcheckbox">
                      </div></td>
                    <td class="">FreerTalk</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3102" class="rowcheckbox">
                      </div></td>
                    <td class="">Sohar Express</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3103" class="rowcheckbox">
                      </div></td>
                    <td class="">TITAN</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3104" class="rowcheckbox">
                      </div></td>
                    <td class="">Unicon Corporation</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3105" class="rowcheckbox">
                      </div></td>
                    <td class="">OPTIMAL LINKS</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3106" class="rowcheckbox">
                      </div></td>
                    <td class="">Hellobd</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3108" class="rowcheckbox">
                      </div></td>
                    <td class="">Noorshed</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3109" class="rowcheckbox">
                      </div></td>
                    <td class="">Rafudnet</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3111" class="rowcheckbox">
                      </div></td>
                    <td class="">ZTL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3112" class="rowcheckbox">
                      </div></td>
                    <td class="">CALL WORLD</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3117" class="rowcheckbox">
                      </div></td>
                    <td class="">Telematrix</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3118" class="rowcheckbox">
                      </div></td>
                    <td class="">Quick Talk</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3119" class="rowcheckbox">
                      </div></td>
                    <td class="">PANDA TEL</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3120" class="rowcheckbox">
                      </div></td>
                    <td class="">KING VOIP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3121" class="rowcheckbox">
                      </div></td>
                    <td class="">Centex</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3122" class="rowcheckbox">
                      </div></td>
                    <td class="">Shozitel</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3123" class="rowcheckbox">
                      </div></td>
                    <td class="">DialTheWorld</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3124" class="rowcheckbox">
                      </div></td>
                    <td class="">ALkhan</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3125" class="rowcheckbox">
                      </div></td>
                    <td class="">Mizan Voip</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3126" class="rowcheckbox">
                      </div></td>
                    <td class="">HELLO VOIP</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3127" class="rowcheckbox">
                      </div></td>
                    <td class="">Qubeel</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3128" class="rowcheckbox">
                      </div></td>
                    <td class="">Telco Infinity</td>
                  </tr>
                  <tr class="even">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3129" class="rowcheckbox">
                      </div></td>
                    <td class="">G Venture</td>
                  </tr>
                  <tr class="odd">
                    <td class=" sorting_1"><div class="checkbox">
                        <input type="checkbox" name="AccountID[]" value="3130" class="rowcheckbox">
                      </div></td>
                    <td class="">RIDCOM</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit" id="genrate-send" style="visibility: visible;">
          <i class="glyphicon glyphicon-circle-arrow-up"></i> Generate &amp; Send
          <input type="hidden" name="GenerateSend" value="">
          </button>
          <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit" id="genrate" style="visibility: visible;"> <i class="glyphicon glyphicon-circle-arrow-up"></i> Generate </button>
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade custom-width" id="modal-estimate-in">
  <div class="modal-dialog" style="width: 60%;">
    <div class="modal-content">
      <form id="add-estimate_in_template-form" method="post" class="form-horizontal form-groups-bordered">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add Estimate</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="field-5" class="col-sm-2 control-label">Account Name<span id="currency"></span></label>
            <div class="col-sm-4"> {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
              <input type="hidden" name="Currency" >
              <input type="hidden" name="EstimateID" >
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Start Date</label>
            <div class="col-sm-2">
              <input type="text" name="StartDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d',strtotime(" -1 day"))}}" />
            </div>
            <div class="col-sm-2">
              <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">End Date</label>
            <div class="col-sm-2">
              <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}" />
            </div>
            <div class="col-sm-2">
              <input type="text" name="EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Issue Date</label>
            <div class="col-sm-4">
              <input type="text" name="IssueDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Estimate Number</label>
            <div class="col-sm-4">
              <input type="text" name="EstimateNumber" class="form-control"  value="" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Grand Total</label>
            <div class="col-sm-4">
              <input type="text" name="GrandTotal" class="form-control"  value="" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Description</label>
            <div class="col-sm-4">
              <input type="text" name="Description" class="form-control"  value="" />
              <input type="hidden" name="EstimateDetailID" class="form-control"  value="" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Attachment(.pdf, .jpg, .png, .gif)</label>
            <div class="col-sm-4">
              <input id="Attachment" name="Attachment" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
              
              <!--<br><span class="file-input-name"></span>--> 
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit"> <i class="entypo-pencil"></i> Save Estimate </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade custom-width" id="modal-estimate-in-view">
  <div class="modal-dialog" style="width: 60%;">
    <div class="modal-content">
      <form class="form-horizontal form-groups-bordered">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">View Estimate</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="field-5" class="col-sm-2 control-label">Account Name<span id="currency"></span></label>
            <div class="col-sm-4 control-label"> <span data-id="AccountName">abcs</span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Issue Date</label>
            <div class="col-sm-4 control-label"> <span data-id="IssueDate"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Estimate Number</label>
            <div class="col-sm-4 control-label"> <span data-id="EstimateNumber"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Grand Total</label>
            <div class="col-sm-4 control-label"> <span data-id="GrandTotal"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Description</label>
            <div class="col-sm-4 control-label"> <span data-id="Description"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Attachment</label>
            <div class="col-sm-4 control-label"> <span data-id="Attachment"></span> </div>
          </div>
        </div>
        <div class="modal-footer">
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade in" id="send-modal-estimate">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="send-estimate-form" method="post" class="form-horizontal form-groups-bordered">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Send Estimate By Email</h4>
        </div>
        <div class="modal-body"> </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-mail"></i> Send </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade in" id="selected-estimate-status">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="selected-estimate-status-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Change Selected Estimate Status</h4>
        </div>
        <div class="modal-body">
          <div id="text-boxes" class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label">Estimate Status</label>
                {{ Form::select('EstimateStatus', Estimate::get_estimate_status(), '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }}
                 </div>
            </div>
            <div class="col-md-6" id="statuscancel">
              <div class="form-group">
                <label for="field-5" class="control-label">Cancel Reason</label>
                <input type="text" name="CancelReason" class="form-control"  value="" />
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left cancelbutton" data-loading-text="Loading...">
          <i class="entypo-floppy"></i>
          <input type="hidden" name="EstimateIDs" value="">
          <input type="hidden" name="criteria" />
          Save
          </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade in" id="send-modal-estimate">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="send-estimate-form" method="post" class="form-horizontal form-groups-bordered">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Send Estimate By Email</h4>
                </div>
                <div class="modal-body">


                   </div>
                <div class="modal-footer">
                     <button type="submit" class="btn btn-primary send btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-mail"></i>
                        Send
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