@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Trunks</strong>
    </li>
</ol>
<h3>Trunks</h3>
<p class="text-right">
@if(User::checkCategoryPermission('Trunk','Add'))
    <a href="#" data-action="showAddModal" data-type="trunk" data-modal="add-new-modal-trunk" class="btn btn-primary">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>
<div class="form-group">
    <label class="control-label">Status</label>
        <p class="make-switch switch-small mar-left-5 mar-top-5" >
            <input id="TrunkStatus" type="checkbox" checked>
        </p>
</div>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th>Status</th>
        <th>Title</th>
        <th>Rate Prefix</th>
        <th>Area Prefix</th>
        <th>Prefix</th>
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
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sAjaxSource": baseurl + "/trunks/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "aaSorting"   : [[5, 'desc']],    
            "aoColumns": 
             [
                { "bVisible": false, "bSortable": true  },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                { "bSortable": true },
                {
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ ;
                        edit_ = "{{ URL::to('trunk/edit/{id}')}}";
                         
                        edit_ = edit_.replace( '{id}', id );
                        <?php if(User::checkCategoryPermission('Trunk','Edit')){ ?>
                            action = '<a href="'+edit_+'" title="View" class="btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        <?php } ?> 
                        return action;
                      }
                  },
            ],
            "oTableTools":
            {
                "aButtons": [
                {
                    "sExtends": "download",
                    "sButtonText": "EXCEL",
                    "sUrl": baseurl + "/trunks/exports/xlsx",
                    sButtonClass: "save-collection btn-sm"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/trunks/exports/csv",
                    sButtonClass: "save-collection btn-sm"
                }
                ]
            }
        });
        $('#TrunkStatus').change(function() {
             if ($(this).is(":checked")) {
                data_table.fnFilter(1,0);  // 1st value 2nd column index
            } else {
                data_table.fnFilter(0,0);
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
</script>
@include('trunk.trunkmodal')
@stop