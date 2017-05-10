@extends('layout.main')

@section('content')


<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Business Hours</strong>
    </li>
</ol>
<h3>Business Hours</h3>

@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
@if(User::checkCategoryPermission('BusinessHours','Add'))
    <a href="{{URL::to('businesshours/create')}}" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

         data_table = $("#table-4").dataTable({

            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/businesshours/ajax_datagrid",
             "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sPaginationType": "bootstrap",
            "oTableTools": {},
            "aaSorting"   : [[0, 'desc']],
            "aoColumns":
             [
                { "bSortable": true,

                                mRender: function (name, type, full) { 
                                    var text = name;
                                    if(typeof full[3] != 'undefined' && full[3] == 1){
                                        text += ' <span class="badge badge-primary badge-roundless">Default</span>';
                                    }
                                    return text;
                                }
                }, //0 Fill name
                { "bSortable": true }, //1 Owner
                {  // 4 Contact ID
                   "bSortable": true,
                    mRender: function ( id, type, full ) { console.log(id);
                        var action , edit_ , show_ ;
                        edit_ = "{{ URL::to('businesshours/{id}/edit')}}";
                        delete_ = "{{ URL::to('businesshours/{id}/delete')}}";

                        edit_ = edit_.replace( '{id}', id );
                        delete_  = delete_ .replace( '{id}', id );
                        action = '';
                        <?php if(User::checkCategoryPermission('BusinessHours','Edit') ){ ?>
                        action += ' <a href="'+edit_+'" title="Edit" class="btn btn-default btn-sm"><i class="entypo-pencil"></i></a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('BusinessHours','Delete') ){ ?>
						if(full[3] == 0){
                        	action += ' <a href="'+delete_+'" title="Delete"  class="btn btn-danger btn-default btn-sm"><i class="entypo-trash"></i></a>';
						}
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
                        "sUrl": baseurl + "/businesshours/exports/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/businesshours/exports/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            }
        });

        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Highlighted rows

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
    });
    $('body').on('click', 'a[title="Delete"]', function (e) {
                e.preventDefault();
                var response = confirm('Are you sure?');
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
@stop