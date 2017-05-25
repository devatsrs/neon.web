@extends('layout.main')

@section('content')
    <ol class="breadcrumb bc-3">
        <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
        <li class="active"> <strong>Import Rules</strong> </li>
    </ol>
    <h3>Import Rules</h3>
    @if( User::checkCategoryPermission('TicketImportRules','Add'))<p class="text-right"> <a href="{{ URL::to('/tickets/importrules/add') }}" class="btn btn-primary"> <i class="entypo-plus"></i> Add New </a></p> @endif

    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        var $searchFilter = {};
        jQuery(document).ready(function($) {

            data_table = $("#table-4").dataTable({
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/tickets/importrules/ajax_datagrid",
                "iDisplayLength": parseInt({{CompanyConfiguration::get('PAGE_SIZE')}}),
                "sPaginationType": "bootstrap",
                //"sDom": 'T<"clear">lfrtip',
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[1, 'asc']],
                "fnServerParams": function (aoData) {
                     data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "Export", "value": 1});
                },
                "aoColumns":
                        [     
						{"bSortable": true},
						{
                        mRender: function (status, type, full) {
                            if (status == 1)
                                return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                            else
                                return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                        }
                    },
                             {
                                "bSortable": true,
                                mRender: function(id, type, full) {
                                    var action, edit_, show_,delete_,clone_;
                                    edit_ = "{{ URL::to('tickets/importrules/{id}/edit')}}";
                                    edit_ = edit_.replace('{id}', full[2]);
									
									clone_ = "{{ URL::to('tickets/importrules/{id}/clone')}}";
                                    clone_ = clone_.replace('{id}', full[2]);

                                    var action = '';

                                    @if(User::checkCategoryPermission('TicketImportRules','Edit'))
                                          action += '<a  href="' + edit_ + '" title="Edit" class="btn btn-sm btn-default"><i class="entypo-pencil"></i></a>';                                    @endif
									
									 @if(User::checkCategoryPermission('TicketImportRules','Add'))
                                            action += '&nbsp;<a title="Clone"  href="' + clone_ + '" class="btn btn-sm btn-default"><i class="fa fa-clone"></i></a>';
                                    @endif

                                            @if(User::checkCategoryPermission('TicketImportRules','Delete'))											
                                            action += '&nbsp;<a title="Delete" grouptickets="'+id+'" data-id="'+full[2]+'" id="group-'+full[2]+'" class="delete-ticketrule btn-sm btn delete btn-danger "><i class="entypo-trash"></i></a>';
											
                                    @endif									
                                            return action;
                                }
                            },

                        ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/tickets/importrules/exports/xlsx", //baseurl + "/generate_xlsx.php",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/tickets/importrules/exports/csv", //baseurl + "/generate_csv.php",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {
                    //After Delete done
                    FnDeleteTicketruleSuccess = function(response){
                        if (response.status == 'success') {                            
                            data_table.fnFilter('', 0);
							ShowToastr("success",response.message);
                        }else{
                            ShowToastr("error",response.message);
                        }
                    }
                    //onDelete Click
                    FnDeleteGroup = function(e){                        
                        result = confirm("Are you sure you want to delete?");
                        if(result){
                            var id  = $(this).attr("data-id");
                            showAjaxScript( baseurl + "/tickets/importrules/"+id+"/delete" ,"",FnDeleteTicketruleSuccess );
                        }
                        return false;
                    }
                    $(".delete-ticketrule").click(FnDeleteGroup); // Delete Note
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }

            });
            // data_table.fnFilter(1, 0);


            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });


            // Replace Checboxes
            $(".pagination a").click(function(ev) {
                replaceCheckboxes();
            });

         });

    </script>
@stop