@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>CRM Template</strong>
    </li>
</ol>
<h3>Templates</h3>

@include('includes.errors')
@include('includes.success')

<div class="row">
    <div class="col-md-12">
        <form id="template_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Template Privacy</label>
                        <div class="col-sm-2">
                            {{Form::select('template_privacy',$privacy,'',array("class"=>"select2 small"))}}
                        </div>
                        <!--<label class="col-sm-2 control-label">Template Type</label>
                        <div class="col-sm-2">
                            {{Form::select('template_type',$type,'',array("class"=>"select2 small"))}}
                        </div>-->
                         <label class="col-sm-2 control-label">Status</label>
                         <div class="col-sm-2">
                            <p class="make-switch switch-small">
                                   <input type="checkbox" checked=""  name="template_status" value="1">
                                   </p>
                        </div>
                        
                         
                    </div>
                    <p style="text-align: right;">
                        <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-search"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

<p style="text-align: right;">
@if(User::checkCategoryPermission('EmailTemplate','Add'))
    <a href="#" id="add-new-template" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New Template
    </a>
@endif    
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="20%">Template name</th>
        <th width="20%">Subject</th>
        <!--<th width="10%">Type</th>-->
        <th width="15%">Created By</th>
        <th width="15%">updated Date</th>
        <th width="10%">Status</th>
        <th width="10%">Action</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
var template_type_val =0;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);
        var tempatetype						= 	{{json_encode($type)}};
        $searchFilter.template_privacy 		= 	$("#template_filter [name='template_privacy']").val();
        $searchFilter.template_type 		= 	$("#template_filter [name='template_type']").val();
		$searchFilter.template_status 		= 	$("#template_filter [name='template_status']").prop("checked");

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/email_template/ajax_datagrid",
            "iDisplayLength": parseInt('{{Config::get('app.pageSize')}}'),
            "fnServerParams": function(aoData) {
                aoData.push({"name":"template_privacy","value":$searchFilter.template_privacy},{"name":"type","value":$searchFilter.template_type},{"name":"Status","value":$searchFilter.template_status});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"template_privacy","value":$searchFilter.template_privacy},{"name":"type","value":$searchFilter.template_type},{"name":"Status","value":$searchFilter.template_status});
            },
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
             "aoColumns":
            [
                {  "bSortable": true },  //0  Template Name', '', '', '
                {  "bSortable": true }, //1   CreatedBy
                /*{  "bSortable": true,
                        mRender: function ( id, type, full ) {
                            return tempatetype[id];
                     }
                 },*/ //updated Date
                {  "bSortable": true }, //updated Date
                {  "bSortable": true }, //updated Date
				{  "bSortable": true,
                    mRender: function ( id, type, full ) { 
					if(id){					
						action = '<p class="make-switch switch-small"><input type="checkbox" data-id="'+full[5]+'" checked=""  class="changestatus"  name="template_status" value="1"></p>';
					}else{
						action = '<p class="make-switch switch-small"><input type="checkbox" data-id="'+full[5]+'" class="changestatus"  name="template_status" value="1"></p>';
					} return action;
					
					 } }, //status
                {
                   "bSortable": true,
                    mRender: function ( id, type, full ) { 
                         action = '<div class = "hiddenRowData" >';
                         action += '<input type = "hidden"  name = "templateID" value = "' + id + '" / >';
                         action += '</div>';
                        <?php if(User::checkCategoryPermission('EmailTemplate','Edit')) { ?>
                            action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" class="edit-template btn btn-default btn-sm"><i class="entypo-pencil"></i></a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('EmailTemplate','Delete')) { ?>
						if(full[6]==0){
                            action += ' <a data-id="'+id+'" class="delete-template btn delete btn-danger btn-sm"><i class="fa fa-trash"></i></a>'; }
                        <?php } ?>
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/email_template/exports/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/email_template/exports/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteTemplateSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteTemplate = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/email_template/"+id+"/delete" ,"",FnDeleteTemplateSuccess );
                       }
                       return false;
                   }
                   $(".delete-template").click(FnDeleteTemplate); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }
        });

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
        $('#template_filter').submit(function(e){
            e.preventDefault();
            $searchFilter.template_privacy = $("#template_filter [name='template_privacy']").val();
            $searchFilter.template_type = $("#template_filter [name='template_type']").val();
			$searchFilter.template_status = $("#template_filter [name='template_status']").prop("checked");
            data_table.fnFilter('', 0);
            return false;
        });

    $('#add-new-template').click(function(ev){
        ev.preventDefault();
        $('#add-new-template-form').trigger("reset");
        $("#add-new-template-form [name='TemplateID']").val('');
        $("#add-new-template-form [name='Email_template_privacy']").val(0).trigger("change");
        $("#add-new-template-form [name='Type']").val('').trigger("change");
        $('#add-new-modal-template h4').html('Add New template');
        $('#add-new-modal-template').modal('show');
		template_type_val = $('#add-new-modal-template').find('.template_type').val();
    });
	
	
	$('table tbody').on('change','.changestatus',function(eve){
		var current_status  = 	$(this).prop("checked");
		var current_id 		= 	$(this).attr("data-id");
		
		if(current_id && !isNaN(current_id))
		{
			setTimeout(update_template_status(current_id,current_status),2000);
		}
   });
   
   function update_template_status(current_id,current_status){ 
     
		var ajax_url 		= 	baseurl+'/email_template/'+current_id+'/changestatus';
		 $.ajax({
			url: ajax_url,
			type: 'POST',
			dataType: 'json',
			async :false,
			data:{s:1,status:current_status},
			success: function(response){
				 if (response.status == 'success')
				 {
					 toastr.success(response.message, "Success", toastr_opts);
					 data_table.fnFilter('', 0);
				 } else {
					 toastr.error(response.message, "Error", toastr_opts);
				 }
			}
		});		
   }
	
    $('table tbody').on('click','.edit-template',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-template-form').trigger("reset");

        templateID = $(this).prev("div.hiddenRowData").find("input[name='templateID']").val();
        var url = baseurl + '/email_template/'+templateID+'/edit';
        $.get(url, function(data, status){
            if(Status="success"){
                $('#add-new-template-form').trigger("reset");
                $("#add-new-template-form [name='TemplateID']").val(data['TemplateID']);
                $("#add-new-template-form [name='TemplateName']").val(data['TemplateName']);
                $("#add-new-template-form [name='Subject']").val(data['Subject']);
                $("#add-new-template-form [name='TemplateBody']").val(data['TemplateBody']);
                $("#add-new-template-form [name='Type']").val(data['Type']).trigger("change"); 
				if(data['Privacy']== '' || data['Privacy']=== null){data['Privacy']=0;} 
                $("#add-new-template-form [name='Email_template_privacy']").val(data['Privacy']).trigger("change");
				if(data['Status'])
				{ 	
					$('.status_switch').bootstrapSwitch('setState', true);

				}else{ 
					$('.status_switch').bootstrapSwitch('setState', false);
				}
                $('#add-new-modal-template h4').html('Edit template');
				template_type_val = $('#add-new-modal-template').find('.template_type').val();
                $('#add-new-modal-template').modal('show');
            }else{
                toastr.error(status, "Error", toastr_opts);
            }
        });


        $("#add-new-template-form [name='templateID']").val($(this).attr('data-id'));
        $('#add-new-modal-template h4').html('Edit template');
        $('#add-new-modal-template').modal('show');
		replaceCheckboxes();
    });		
	$('.unclick').click(function(e) {
		e.preventDefault();
		console.log('unclick');
        return false;
    });
    });
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}

.unclick{background:#ccc !important; color:#fff !important;}
.unclick:hover{background:#ccc !important; color:#fff !important;}
.unclick a{cursor:not-allowed; }
.dropdown-menu>li.unclick>a:hover{background:#ccc !important; color:#fff !important;}
.wysihtml5-toolbar > .dropdown > .dropdown-menu > li.unclick > a{color:#fff !important;}
.TicketsScroll{z-index:999 !important; }
.wysihtml5-sandbox{z-index:0 !important; clear:both !important; position:relative !important; float:left !important;}
.TicketsScroll div .ps-scrollbar-y{
	  clear:both !important; display:block !important;
}
</style>
@include('emailtemplate.emailtemplatemodal')
@stop