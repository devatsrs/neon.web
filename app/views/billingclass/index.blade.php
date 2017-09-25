@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <div id="table_filter" method="get" action="#" >
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    <input type="text" name="Name" class="form-control" value="" />
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="filter_submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop


@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Billing Class</strong>
        </li>
    </ol>
    <h3>Billing Class</h3>

    @include('includes.errors')
    @include('includes.success')
    @if(User::checkCategoryPermission('BillingClass','Edit'))
    <p style="text-align: right;">
        <a  id="add-button" href="{{URL::to('billing_class/create')}}" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
    </p>
    @endif

    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="20%">Name</th>
            <th width="15%">Modified By</th>
            <th width="15%">Modified Date</th>
            <th width="20%">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        /**
         * JQuery Plugin for dataTable
         * */

        jQuery(document).ready(function ($) {

            $('#filter-button-toggle').show();

            var list_fields  = ["Name","UpdatedBy","updated_at","BillingClassID","Applied"];
            //public_vars.$body = $("body");
            var $search = {};
            var edit_url = baseurl + "/billing_class/edit/{id}";
            var delete_url = baseurl + "/billing_class/delete/{id}";
            var datagrid_url = baseurl + "/billing_class/ajax_datagrid";

            $("#filter_submit").click(function(e) {
                e.preventDefault();

                $search.Name = $("#table_filter").find('[name="Name"]').val();

                data_table = $("#table-list").dataTable({
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Name", "value": $search.Name}


                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "Export", "value": 1}
                        );

                    },
                    "aoColumns": [
                        {  "bSortable": true },  // 0 Name
                        {  "bSortable": true },  // 2 UpdatedBy
                        {  "bSortable": true },  // 2 updated_at
                        {  "bSortable": false,
                            mRender: function ( id, type, full ) {
                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';
                                action += ' <a href="' + edit_url.replace("{id}",id) +'" title="Edit" class="edit-button btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                @if(User::checkCategoryPermission('BillingClass','Delete'))
                                if(full[4]== 0) {
                                    action += ' <a href="' + delete_url.replace("{id}", id) + '" title="Delete" class="delete-button btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                                }
                                @endif
                                return action;
                            }
                        },  // 0 Created


                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "Export Data",
                                "sUrl": datagrid_url,
                                sButtonClass: "save-collection"
                            }
                        ]
                    },
                    "fnDrawCallback": function() {
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }

                });
            });


            $('#filter_submit').trigger('click');
            //inst.myMethod('I am a method');


            $('table tbody').on('click', '.delete-button', function (ev) {
                ev.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    var delete_url  = $(this).attr("href");
                    submit_ajax_datatable( delete_url,"",0,data_table);
                }
                return false;
            });

            $("#modal-form").submit(function(e){
                e.preventDefault();
                var _url  = $(this).attr("action");
                submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
            });



        });
    </script>

@stop