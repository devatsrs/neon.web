@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Tickets</strong> </li>
</ol>
<h3>Tickets</h3>
<p class="text-right"> @if( User::checkCategoryPermission('tickets','Add')) <a href="{{ URL::to('/tickets/add') }}" class="btn btn-primary"> <i class="entypo-plus"></i> Add New </a> @endif </p>
<div class="row">
  <div class="col-md-12">
    <form role="form" id="tickets_filter" method="post" action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title"> Filter </div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body" id="paymentsearch">
          <div class="form-group">
            <label for="field-1" class="col-sm-1 control-label small_label">Search</label>
            <div class="col-sm-2">  {{ Form::text('search', '', array("class"=>"form-control")) }} </div>            
            <label for="field-1" class="col-sm-1 control-label small_label">Status</label>
            <div class="col-sm-2"> {{Form::select('status', $status, '' ,array("class"=>"select2"))}} </div>  
            
            <label for="field-1" class="col-sm-1 control-label small_label">Type</label>
            <div class="col-sm-2"> {{Form::select('type', $Type, '' ,array("class"=>"select2"))}} </div>  
                 
            </div>
             <div class="form-group">             
            <label for="field-1" class="col-sm-1 control-label small_label">Priority</label>
            <div class="col-sm-2"> {{Form::select('priority', $Priority, '' ,array("class"=>"select2"))}} </div>  
             
             <label for="field-1" class="col-sm-1 control-label small_label">Group</label>
            <div class="col-sm-2"> {{Form::select('group', $Groups, '' ,array("class"=>"select2"))}} </div>    
              <label for="field-1" class="col-sm-1 control-label small_label">Agent</label>
            <div class="col-sm-2"> {{Form::select('agent', $Agents, '' ,array("class"=>"select2"))}} </div>    
            
          </div>
          <p style="text-align: right;">
            <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left"> <i class="entypo-search"></i> Search </button>
          </p>
        </div>
      </div>
    </form>
  </div>
</div>
<table class="table table-bordered datatable" id="table-4">
  <thead>
    <tr>
      <th width="1%">&nbsp;</th>
      <th width="15%">Subject</th>
      <th width="15%">Requester</th>
      <th width="15%">Type</th>
      <th width="10%">Status</th>
      <th width="10%">Priority</th>
      <th width="10%">Group</th>
      <th width="10%">Agent</th>
      <th width="14%">Actions</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<script type="text/javascript">
	var $searchFilter = {};
    jQuery(document).ready(function($) {
		// return EscalationTimes[full[7]];
		$searchFilter.status 		= 	$("#tickets_filter select[name='status']").val();
		$searchFilter.type 			= 	$("#tickets_filter select[name='type']").val();
		$searchFilter.priority 		= 	$("#tickets_filter select[name='priority']").val();
		$searchFilter.group 		= 	$("#tickets_filter select[name='group']").val();
		$searchFilter.agent 		= 	$("#tickets_filter select[name='agent']").val();	
		$searchFilter.Search 		= 	$("#tickets_filter [name='search']").val();
		
        data_table = $("#table-4").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/tickets/ajax_datagrid/type",
            "iDisplayLength": {{Config::get('app.pageSize')}},
            "sPaginationType": "bootstrap",
            //"sDom": 'T<"clear">lfrtip',
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[1, 'asc']],
			"fnServerParams": function (aoData) {
                    aoData.push(
                            {"name": "status", "value": $searchFilter.status},
							{"name": "type", "value": $searchFilter.type},
							{"name": "priority", "value": $searchFilter.priority},
							{"name": "group", "value": $searchFilter.group},
							{"name": "agent", "value": $searchFilter.agent},
							{"name": "Search", "value": $searchFilter.Search}							
							);
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push(
					{"name": "Export", "value": 1},
                    {"name": "status", "value": $searchFilter.status},
					{"name": "type", "value": $searchFilter.type},
					{"name": "priority", "value": $searchFilter.priority},
					{"name": "group", "value": $searchFilter.group},
					{"name": "agent", "value": $searchFilter.agent},
					{"name": "Search", "value": $searchFilter.Search}							
					);
                },
            "aoColumns":
                    [
                        {
                        "bSortable": false,
                        mRender: function (id, type, full) {
                            var action, action = '<div class = "hiddenRowData" >';  
                     action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="' + full[0] + '" name="GroupID[]"></div>';
                            return action;
                        }

                     },
					 	{"bSortable": true },
						{"bSortable": true },
                        {"bSortable": true },
                        {"bSortable": true },
						{"bSortable": true },
						{"bSortable": true,mRender: function(id, type, full) { return id; } },
                        {"bSortable": true,mRender: function(id, type, full) { return id  } },
                        {
                            "bSortable": true,
                            mRender: function(id, type, full) { 
                                var action, edit_, show_,delete_;
                                edit_ = "{{ URL::to('tickets/{id}/edit')}}";								
                                edit_ = edit_.replace('{id}', full[0]);
								show_ = "{{ URL::to('tickets/{id}/show')}}";
								show_ = show_.replace('{id}', full[0]);
								
                                action =  '';
                                <?php if(User::checkCategoryPermission('tickets','Edit')){ ?>
                                   action = '<a href="' + edit_ + '" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                <?php } ?>
								//action += '<a href="' + show_ + '" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-search"></i>View </a>';
								
								<?php if(User::checkCategoryPermission('tickets','Delete')){ ?>
                                   action += '<br><a data-id="'+full[0]+'" id="group-'+full[0]+'" class="delete-ticket btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
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
                        "sUrl": baseurl + "/tickets/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/tickets/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
       "fnDrawCallback": function() {
               //After Delete done
               FnDeleteGroupSuccess = function(response){

                   if (response.status == 'success') {
                       $("#group-"+response.GroupID).parent().parent().fadeOut('fast');
                       ShowToastr("success",response.message);
                       data_table.fnFilter('', 0);
                   }else{
                       ShowToastr("error",response.message);
                   }
               }
               //onDelete Click
               FnDeleteGroup = function(e){
                   result = confirm("Are you Sure?");
                   if(result){
                       var id  = $(this).attr("data-id");
                       showAjaxScript( baseurl + "/tickets/"+id+"/delete" ,"",FnDeleteGroupSuccess );
                   }
                   return false;
               }
               $(".delete-ticket").click(FnDeleteGroup); // Delete Note
               $(".dataTables_wrapper select").select2({
                   minimumResultsForSearch: -1
               });
       }

        });
        data_table.fnFilter(1, 0);


        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Highlighted rows
        $("#table-2 tbody input[type=checkbox]").each(function(i, el) {
            var $this = $(el),
                    $p = $this.closest('tr');

            $(el).on('change', function() {
                var is_checked = $this.is(':checked');

                $p[is_checked ? 'addClass' : 'removeClass']('highlight');
            });
        });

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });
		
		 $("#tickets_filter").submit(function (e) {
                e.preventDefault();
             	$searchFilter.status 		= 	$("#tickets_filter select[name='status']").val();
				$searchFilter.type 			= 	$("#tickets_filter select[name='type']").val();
				$searchFilter.priority 		= 	$("#tickets_filter select[name='priority']").val();
				$searchFilter.group 		= 	$("#tickets_filter select[name='group']").val();
				$searchFilter.agent 		= 	$("#tickets_filter select[name='agent']").val();	
				$searchFilter.Search 		= 	$("#tickets_filter [name='search']").val();
                data_table.fnFilter('', 0);
                return false;
            });
    });

</script> 
@stop 