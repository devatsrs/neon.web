@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="itemtype_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Title</label>
                    {{ Form::text('title', '', array("class"=>"form-control")) }}
                </div>

                <div class="form-group">
                    <label for="field-1" class="control-label">Active</label>
                        <?php $active = [""=>"Select","1"=>"Active","0"=>"Inactive"]; ?>
                    {{ Form::select('Active', $active, '', array("class"=>"form-control select2 small")) }}
                </div>

                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('products')}}"><i class=""></i>Items</a>
        </li>
        <li class="active">
            <strong>Item Types</strong>
        </li>
    </ol>

    <h3>Item Types</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
                <div class="row">
                    <div  class="col-md-12">
                        {{--<a href="{{ URL::to('/products')  }}" class="btn btn-primary pull-right">
                            <i class=""></i>
                            Back
                        </a>--}}
                        <a href="{{ URL::to('/products')  }}" class="btn btn-danger btn-md btn-icon icon-left pull-right" > <i class="entypo-cancel"></i> Close </a>
                        @if(User::checkCategoryPermission('ItemType','Edit'))
                        <div class="input-group-btn pull-right hidden dropdown" style="width:78px;">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                                @if(User::checkCategoryPermission('ItemType','Edit'))
                                    <li class="li_active">
                                        <a class="type_active_deactive" type_ad="active" href="javascript:void(0);" >
                                            <i class="fa fa-plus-circle"></i>
                                            <span>Activate</span>
                                        </a>
                                    </li>
                                    <li class="li_deactive">
                                        <a class="type_active_deactive" type_ad="deactive" href="javascript:void(0);" >
                                            <i class="fa fa-minus-circle"></i>
                                            <span>Deactivate</span>
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </div><!-- /btn-group -->
                        @endif

                        @if( User::is_admin() || User::is('BillingAdmin'))
                            @if(User::checkCategoryPermission('ItemType','Add'))

                                <a href="#" data-action="showAddModal" id="add-new-itemtype" data-type="item" data-modal="add-edit-modal-itemtype" class="btn btn-primary pull-right">
                                    <i class="entypo-plus"></i>
                                    Add New
                                </a>

                            @endif

                        @endif

                    </div>
                    <div class="clear"></div>
                </div>
            <br>
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                    <th width="26%">Title</th>
                    <th width="20%">Last Updated</th>
                    <th width="10%">Active</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var checked = '';
                var list_fields  = ['ItemTypeID','Title','updated_at','Active'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {

                    $('#filter-button-toggle').show();

                    public_vars.$body = $("body");
                    $searchFilter.title = $("#itemtype_filter [name='title']").val();
                    $searchFilter.Active = $("#itemtype_filter select[name='Active']").val();

                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/products/itemtypes/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push({ "name": "title", "value": $searchFilter.title },
                                        { "name": "Active", "value": $searchFilter.Active });

                            data_table_extra_params.length = 0;
                            data_table_extra_params.push({ "name": "title", "value": $searchFilter.title },
                                                        { "name": "Active", "value": $searchFilter.Active },
                                                        { "name": "Export", "value": 1});

                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[1, 'asc']],
                        "aoColumns": [
                            {"bSortable": false,
                                mRender: function(id, type, full) {
                                    // checkbox for bulk action
                                    return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            },
                            {  "bSortable": true },  // 1 Title
                            {  "bSortable": true },  // 2 updated_at
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

                                    var delete_ = "{{ URL::to('products/itemtypes/{id}/delete')}}";
                                    delete_  = delete_ .replace( '{id}', full[0] );

                                    var url_="{{ URL::to('products/dynamicfields/{id}/view') }}";
                                    url_  = url_ .replace( '{id}', full[0] );

                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }

                                    action += '</div>';
                                    <?php if(User::checkCategoryPermission('ItemType','Edit')){ ?>
                                        action += ' <a data-name = "' + full[1] + '" data-id="' + full[0] + '" title="Edit" class="edit-itemtype btn btn-default btn-sm btn-smtooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    <?php } ?>
                                    <?php if(User::checkCategoryPermission('ItemType','Delete') ){ ?>
                                        action += ' <a href="'+delete_+'" data-redirect="{{ URL::to('products')}}" title="Delete"  class="btn delete btn-danger btn-default btn-sm btn-smtooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-trash"></i></a>';
                                     <?php } ?>
                                     if(full[3]==1) {
                                         <?php if(User::checkCategoryPermission('DynamicField', 'View') ){ ?>
                                                 action += '<a href="'+url_+'" data-toggle="tooltip" title="Dynamic Fields"  class="btn btn-default btn-sm btn-smtooltip-primary" style="margin-left:3px;"><i class="glyphicon glyphicon-th"></i> </a>';
                                         <?php } ?>
                                     }
                                    return action;
                                }
                            }
                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/products/itemtypes/ajax_datagrid/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/products/itemtypes/ajax_datagrid/csv",
                                    sButtonClass: "save-collection btn-sm"
                                }
                            ]
                        },
                        "fnDrawCallback": function () {
                            $(".dataTables_wrapper select").select2({
                                minimumResultsForSearch: -1
                            });
                            $(".dropdown").removeClass("hidden");

                            $('#table-4 tbody tr').each(function (i, el) {
                                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
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

                            //select all record
                            $('#selectallbutton').click(function(){
                                if($('#selectallbutton').is(':checked')){
                                    checked = 'checked=checked disabled';
                                    $("#selectall").prop("checked", true).prop('disabled', true);
                                    //if($('.gridview').is(':visible')){
                                    $('.gridview li div.box').each(function(i,el){
                                        $(this).addClass('selected');
                                    });
                                    //}else{
                                    $('#table-4 tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    });
                                    //}
                                }else{
                                    checked = '';
                                    $("#selectall").prop("checked", false).prop('disabled', false);
                                    //if($('.gridview').is(':visible')){
                                    $('.gridview li div.box').each(function(i,el){
                                        $(this).removeClass('selected');
                                    });
                                    //}else{
                                    $('#table-4 tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    });
                                    //}
                                }
                            });
                        }

                    });

                    // select all records which are showing in list
                    $("#selectall").click(function (ev) {
                        var is_checked = $(this).is(':checked');
                        $('#table-4 tbody tr').each(function (i, el) {
                            if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
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
                    // select single record which row is clicked
                    $('#table-4 tbody').on('click', 'tr', function () {
                        if (checked == '') {
                            if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                $(this).toggleClass('selected');
                                if ($(this).hasClass('selected')) {
                                    $(this).find('.rowcheckbox').prop("checked", true);
                                } else {
                                    $(this).find('.rowcheckbox').prop("checked", false);
                                }
                            }
                        }
                    });
                    //done above

                    $('.type_active_deactive').click(function(e) {

                        var type_active_deactive  =  $(this).attr('type_ad');
                        var SelectedIDs 		  =  getselectedIDs();
                        var criteria_ac			  =  '';

                        if($('#selectallbutton').is(':checked')){
                            criteria_ac = 'criteria';
                        }else{
                            criteria_ac = 'selected';
                        }

                        if(SelectedIDs=='' || criteria_ac=='')
                        {
                            alert("Please select atleast one account.");
                            return false;
                        }

                        item_update_status_url =  '{{ URL::to('products/itemtypes/update_bulk_itemtypes_status')}}';
                        $.ajax({
                            url: item_update_status_url,
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
                                    $('#selectall').removeAttr('checked');
                                    if(jQuery('#selectallbutton').is(':checked'))
                                        $('#selectallbutton').click();
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },
                            data: {
                                "title":$("#itemtype_filter [name='title']").val(),
                                "Active":$("#itemtype_filter [name='Active']").val(),
                                "SelectedIDs":SelectedIDs,
                                "criteria_ac":criteria_ac,
                                "type_active_deactive":type_active_deactive,
                            }

                        });

                    });

                    $("#itemtype_filter").submit(function(e){
                        e.preventDefault();
                        $searchFilter.title = $("#itemtype_filter [name='title']").val();
                        $searchFilter.Active = $("#itemtype_filter [name='Active']").val();
                         data_table.fnFilter('', 0);
                        return false;
                    });

                    $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('table tbody').on('click', '.edit-itemtype', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#add-edit-itemtype-form').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){

                            if(list_fields[i] == 'Active'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    $('#add-edit-itemtype-form [name="Active"]').prop('checked',true)
                                }else{
                                    $('#add-edit-itemtype-form [name="Active"]').prop('checked',false)
                                }
                            }else{
                                $("#add-edit-itemtype-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }
                        }

                        $("#add-edit-modal-itemtype [name='ProductClone']").val(0);
                        $('#add-edit-modal-itemtype h4').html('Edit Item Type');
                        $('#add-edit-modal-itemtype').modal('show');
                    });
                    $('table tbody').on('click', '.clone-product', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#add-edit-itemtype-form').trigger("reset");
                        var cur_obj = $(this).prev().prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){

                            if(list_fields[i] == 'Active'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    $('#add-edit-itemtype-form [name="Active"]').prop('checked',true)
                                }else if(list_fields[i] == 'AppliedTo'){
                                    $("#add-edit-itemtype-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val()).trigger("change");
                                }else{
                                    $('#add-edit-itemtype-form [name="Active"]').prop('checked',false)
                                }
                            }else{
                                $("#add-edit-itemtype-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }
                        }
                        var DynamicFields = $(this).prev().find('input[name^=DynamicFields]');
                        for(var j=0;j<DynamicFields.length;j++) {
                            var dfName = DynamicFields[j].getAttribute('name');
                            var dfValue = DynamicFields[j].value;
                            $('#add-edit-itemtype-form').find('input[name^=DynamicFields]').each(function(){
                                if($(this).attr('name') == dfName){
                                    $(this).val(dfValue);
                                }
                            });
                        }
                        $("#add-edit-modal-itemtype [name='ProductClone']").val(1);
                        $('#add-edit-modal-itemtype h4').html('Clone Item');
                        $('#add-edit-modal-itemtype').modal('show');
                    });

                    $('#add-new-itemtype').click(function (ev) {
                        $("#add-edit-modal-itemtype [name='ProductClone']").val(0);
                    });
                    /*$('#add-new-itemtype').click(function (ev) {
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

                function getselectedIDs(){
                    var SelectedIDs = [];
                    $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                        leadID = $(this).val();
                        SelectedIDs[i++] = leadID;
                    });
                    return SelectedIDs;
                }

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

            <style>
                #selectcheckbox{
                    padding: 15px 10px;
                }
            </style>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
    @include("products.itemtypes.productitemmodal")
@stop
