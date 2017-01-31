@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Items</a>
        </li>
    </ol>

    <h3>Items</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
            <br>
            @if( User::is_admin() || User::is('BillingAdmin'))
                <p style="text-align: right;">
                    <a href="#" data-action="showAddModal" data-type="item" data-modal="add-edit-modal-product" class="btn btn-primary ">
                        <i class="entypo-plus"></i>
                        Add New Item
                    </a>
                </p>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <form id="product_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
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
                                    <label for="field-1" class="col-sm-2 control-label">Name</label>
                                    <div class="col-sm-2">
                                        {{ Form::text('Name', '', array("class"=>"form-control")) }}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Code</label>
                                     <div class="col-sm-2">
                                           {{ Form::text('Code', '', array("class"=>"form-control")) }}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Active</label>
                                    <div class="col-sm-2">
                                           <?php $active = [""=>"Both","1"=>"Active","0"=>"Inactive"]; ?>
                                          {{ Form::select('Active', $active, '', array("class"=>"form-control select2 small")) }}
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
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th width="30%">Name</th>
                    <th width="10%">Code</th>
                    <th width="10%">Unit Cost</th>
                    <th width="20%">Last Updated</th>
                    <th width="10%">Active</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var list_fields  = ['Name','Code','Amount','updated_at','Active','ProductID','Description','Note'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {
                        public_vars.$body = $("body");
                        $searchFilter.Name = $("#product_filter [name='Name']").val();
                        $searchFilter.Code = $("#product_filter [name='Code']").val();
                        $searchFilter.Active = $("#product_filter select[name='Active']").val();
                        data_table = $("#table-4").dataTable({
                            "bDestroy": true,
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/products/ajax_datagrid/type",
                            "fnServerParams": function (aoData) {
                                aoData.push({ "name": "Name", "value": $searchFilter.Name },
                                            { "name": "Code","value": $searchFilter.Code },
                                            { "name": "Active", "value": $searchFilter.Active });

                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({ "name": "Name", "value": $searchFilter.Name },
                                                            { "name": "Code","value": $searchFilter.Code },
                                                            { "name": "Active", "value": $searchFilter.Active },{ "name": "Export", "value": 1});

                            },
                            "iDisplayLength": parseInt('{{Config::get('app.pageSize')}}'),
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
                                {  "bSortable": true },  // 1 Item Name
                                {  "bSortable": true },  // 2 Item Code
                                {  "bSortable": true },  // 3 Unit Cost
                                {  "bSortable": true },  // 4 updated_at
                                {  "bSortable": true,
                                    mRender: function (val){
                                        if(val==1){
                                            return   '<i class="entypo-check" style="font-size:22px;color:green"></i>'
                                        }else {
                                            return '<i class="entypo-cancel" style="font-size:22px;color:red"></i>'
                                        }
                                    }

                                 },  // 4 Active
                                {                       //  5  Action
                                    "bSortable": false,
                                    mRender: function (id, type, full) {

                                        var delete_ = "{{ URL::to('products/{id}/delete')}}";
                                        delete_  = delete_ .replace( '{id}', full[5] );

                                        action = '<div class = "hiddenRowData" >';
                                        for(var i = 0 ; i< list_fields.length; i++){
                                            action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                        }
                                        action += '</div>';
                                        <?php if(User::checkCategoryPermission('Products','Edit')){ ?>
                                            action += ' <a data-name = "' + full[0] + '" data-id="' + full[5] + '" title="Edit" class="edit-product btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                                        <?php } ?>
                                        <?php if(User::checkCategoryPermission('Products','Delete') ){ ?>
                                            action += ' <a href="'+delete_+'" data-redirect="{{ URL::to('products')}}" title="Delete"  class="btn delete btn-danger btn-default btn-sm"><i class="entypo-trash"></i></a>';
                                         <?php } ?>   
                                        return action;
                                    }
                                }
                            ],
                            "oTableTools": {
                                "aButtons": [
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "EXCEL",
                                        "sUrl": baseurl + "/products/ajax_datagrid/xlsx",
                                        sButtonClass: "save-collection btn-sm"
                                    },
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "CSV",
                                        "sUrl": baseurl + "/products/ajax_datagrid/csv",
                                        sButtonClass: "save-collection btn-sm"
                                    }
                                ]
                            },
                            "fnDrawCallback": function () {
                                $(".dataTables_wrapper select").select2({
                                    minimumResultsForSearch: -1
                                });
                            }

                        });
                        $("#product_filter").submit(function(e){
                            e.preventDefault();
                            $searchFilter.Name = $("#product_filter [name='Name']").val();
                            $searchFilter.Code = $("#product_filter [name='Code']").val();
                            $searchFilter.Active = $("#product_filter [name='Active']").val();
                             data_table.fnFilter('', 0);
                            return false;
                        });


                        // Replace Checboxes
                        $(".pagination a").click(function (ev) {
                            replaceCheckboxes();
                        });

                        $('table tbody').on('click', '.edit-product', function (ev) {
                            ev.preventDefault();
                            ev.stopPropagation();
                            $('#add-edit-product-form').trigger("reset");
                            var cur_obj = $(this).prev("div.hiddenRowData");
                            for(var i = 0 ; i< list_fields.length; i++){

                                if(list_fields[i] == 'Active'){
                                    if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                        $('#add-edit-product-form [name="Active"]').prop('checked',true)
                                    }else{
                                        $('#add-edit-product-form [name="Active"]').prop('checked',false)
                                    }
                                }else{
                                    $("#add-edit-product-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                                }
                            }
                            $('#add-edit-modal-product h4').html('Edit Item');
                            $('#add-edit-modal-product').modal('show');
                        });


                    /*$('#add-new-product').click(function (ev) {
                        ev.preventDefault();
                        $('#add-edit-product-form').trigger("reset");
                        $("#add-edit-product-form [name='ProductID']").val('');
                        $('#add-edit-modal-product h4').html('Add New Item');
                        $('#add-edit-modal-product').modal('show');
                    });


                    $('#add-edit-product-form').submit(function(e){
                        e.preventDefault();
                        var ProductID = $("#add-edit-product-form [name='ProductID']").val()
                        if( typeof ProductID != 'undefined' && ProductID != ''){
                            update_new_url = baseurl + '/products/'+ProductID+'/update';
                        }else{
                            update_new_url = baseurl + '/products/create';
                        }
                        $.ajax({
                            url: update_new_url,  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('#add-edit-modal-product').modal('hide');
                                    data_table.fnFilter('', 0);
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $("#product-update").button('reset');
                            },
                            // Form data
                            data: $('#add-edit-product-form').serialize(),
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false
                        });
                    });*/
                });

                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

                $('body').on('click', '.btn.delete', function (e) {
                    e.preventDefault();

                    response = confirm('Are you sure?');
                    if( typeof $(this).attr("data-redirect")=='undefined'){
                        $(this).attr("data-redirect",'{{ URL::previous() }}')
                    }
                    redirect = $(this).attr("data-redirect");
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

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
    @include("products.productmodal")
@stop
