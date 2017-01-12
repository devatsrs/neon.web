@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Ticket Groups</strong> </li>
</ol>
<h3>Ticket Groups</h3>
<p class="text-right"> @if( User::checkCategoryPermission('ticketgroups','Add')) <a href="{{ URL::to('/ticketgroups/add') }}" class="btn btn-primary"> <i class="entypo-plus"></i> Add New </a> @endif </p>
<div class="row">
  <div class="col-md-12">
    <form role="form" id="ticketgroup_filter" method="post" action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title"> Filter </div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body" id="paymentsearch">
          <div class="form-group">
             <label for="field-1" class="col-sm-1 control-label small_label">Search</label>
            <div class="col-sm-2">  {{ Form::text('Search', '', array("class"=>"form-control")) }} </div>
            
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
      <th>&nbsp;</th>
      <th>Name</th>
      <th>Email Address</th>
      <th>Total Agents</th>
      <th>Escalation Time</th>
      <th>Escalation User</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<script type="text/javascript">
	var $searchFilter = {};
    jQuery(document).ready(function($) {
		var EscalationTimes = {{$EscalationTimes_json}};
		// return EscalationTimes[full[7]];
		//$searchFilter.UsersID = $("#ticketgroup_filter select[name='UsersID']").val();
		$searchFilter.Search = $("#ticketgroup_filter [name='Search']").val();
		
        data_table = $("#table-4").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/ticketgroups/ajax_datagrid_groups/type",
            "iDisplayLength": {{Config::get('app.pageSize')}},
            "sPaginationType": "bootstrap",
            //"sDom": 'T<"clear">lfrtip',
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[1, 'asc']],
			"fnServerParams": function (aoData) {
                    aoData.push(
                            {"name": "Search", "value": $searchFilter.Search});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "Search","value": $searchFilter.Search},                          
                            {"name": "Export", "value": 1});
                },
            "aoColumns":
                    [
                        {
                        "bSortable": false,
                        mRender: function (id, type, full) {
                            var action, action = '<div class = "hiddenRowData" >';  
                     action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="' + full[0] + '" name="GroupID[]"></div></div>';
                            return action;
                        }

                     },
                        {"bSortable": true },
                        {"bSortable": true,mRender: function(id, type, full) { if(id==null){return '';}  return "<span class='wrap-text'>"+id+"</span>";  } },
						{"bSortable": true },
						{"bSortable": true,mRender: function(id, type, full) { return EscalationTimes[id]; } },
                        {"bSortable": true,mRender: function(id, type, full) { if(id){return id;}else{return 'None';} } },
                        {
                            "bSortable": true,
                            mRender: function(id, type, full) { 
                                var action, edit_, show_,delete_;
                                edit_ = "{{ URL::to('ticketgroups/{id}/edit')}}";
                                edit_ = edit_.replace('{id}', full[0]);
								
                                action =  '';
                                <?php if(User::checkCategoryPermission('TicketGroups','Edit')){ ?>
                                   action = '<a href="' + edit_ + '" class="btn btn-default"><i class="entypo-pencil"></i></a>';
                                <?php } ?>
								<?php if(User::checkCategoryPermission('TicketGroups','Delete')){ ?>
                                   action += '<br><a data-id="'+full[0]+'" id="group-'+full[0]+'" class="delete-ticket_group btn delete btn-danger "><i class="entypo-cancel"></i></a>';
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
                        "sUrl": baseurl + "/ticketgroups/ajax_datagrid_groups/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/ticketgroups/ajax_datagrid_groups/csv", //baseurl + "/generate_csv.php",
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
                       showAjaxScript( baseurl + "/ticketgroups/"+id+"/delete" ,"",FnDeleteGroupSuccess );
                   }
                   return false;
               }
               $(".delete-ticket_group").click(FnDeleteGroup); // Delete Note
               $(".dataTables_wrapper select").select2({
                   minimumResultsForSearch: -1
               });
       }

        });
       // data_table.fnFilter(1, 0);


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
		
		 $("#ticketgroup_filter").submit(function (e) {
                e.preventDefault();
               // $searchFilter.UsersID = $("#ticketgroup_filter select[name='UsersID']").val();
                $searchFilter.Search  = $("#ticketgroup_filter [name='Search']").val();
                data_table.fnFilter('', 0);
                return false;
            });
    });

</script> 
<style>
.table-bordered  tr th:first-child{display:none; }
.table-bordered  tr td:first-child{display:none; }
.wrap-text{text-wrap:normal;}
</style>
@stop 