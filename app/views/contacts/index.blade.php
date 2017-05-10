@extends('layout.main')

@section('content')


<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Contacts</strong>
    </li>
</ol>
<h3>Contacts</h3>

@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
@if(User::checkCategoryPermission('Contacts','Add'))
    <a href="{{URL::to('contacts/create')}}" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th>Contact Name</th>
        <th>Contact Owner</th>
        <th>Phone</th>
        <th>Email</th>
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
            "sAjaxSource": baseurl + "/contacts/ajax_datagrid",
             "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sPaginationType": "bootstrap",
            "oTableTools": {},
            "aaSorting"   : [[4, 'desc']],
            "aoColumns":
             [
                { "bSortable": true }, //0 Fill name
                { "bSortable": true }, //1 Owner
                { "bSortable": true }, //2 Phone
                { "bSortable": true }, //3 Email
                {  // 4 Contact ID
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ ;
                        edit_ = "{{ URL::to('contacts/{id}/edit')}}";
                        show_ = "{{ URL::to('contacts/{id}/show')}}";
                        delete_ = "{{ URL::to('contacts/{id}/delete')}}";

                        edit_ = edit_.replace( '{id}', id );
                        show_ = show_.replace( '{id}', id );
                        delete_  = delete_ .replace( '{id}', id );
                        action = '';
                        <?php if(User::checkCategoryPermission('Contacts','Edit') ){ ?>
                        action += ' <a href="'+edit_+'" title="Edit" class="btn btn-default btn-sm"><i class="entypo-pencil"></i></a>';
                        <?php } ?>
                        action += ' <a href="'+show_+'" Title="View" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
                        <?php if(User::checkCategoryPermission('Contacts','Delete') ){ ?>
                        action += ' <a href="'+delete_+'" title="Delete"  class="btn btn-danger btn-default btn-sm"><i class="entypo-trash"></i></a>';
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
                        "sUrl": baseurl + "/contacts/exports/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/contacts/exports/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            }
        });

        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Highlighted rows
        $("#table-2 tbody input[type=checkbox]").each(function (i, el) {
            var $this = $(el),
                $p = $this.closest('tr');

            $(el).on('change', function () {
                var is_checked = $this.is(':checked');

                $p[is_checked ? 'addClass' : 'removeClass']('highlight');
            });
        });

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