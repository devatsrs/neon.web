@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="dynamicfield_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Field Name</label>
                    {{ Form::text('FieldName', '', array("class"=>"form-control")) }}
                </div>

                <div class="form-group">
                    <label for="field-5" class="control-label">DOM Type </label>
                    <?php
                    $FieldDomTypes=[''=>'Select DOM Type','string'=>'String','numeric'=>'Numeric','textarea'=>'Text Area','select'=>'Select','file'=>'File','datetime'=>'DateTime','boolean'=>'Boolean'];
                    ?>
                    {{Form::select('FieldDomType',$FieldDomTypes,'',array("class"=>"form-control select2 small"))}}
                </div>

                <div class="form-group">
                    <label for="field-5" class="control-label">Item Type </label>
                    {{Form::select('ItemTypeID',$itemtypes,$ItemTypeID,array("class"=>"form-control select2 small"))}}
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
        <li>
            <a href="{{URL::to('products/itemtypes')}}"><i class=""></i>Item Types</a>
        </li>
        <li class="active">
            <strong>Dynamic Fields</strong>
        </li>
    </ol>

    <h3>Dynamic Fields</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
                <div class="row">
                    <div  class="col-md-12">
                        {{--<a href="{{ URL::to('/products')  }}" class="btn btn-primary pull-right">
                            <i class=""></i>
                            Back
                        </a>--}}
                        <a href="{{ URL::to('/products/itemtypes')  }}" class="btn btn-danger btn-md btn-icon icon-left pull-right"> <i class="entypo-cancel"></i> Close </a>
                        @if(User::checkCategoryPermission('DynamicField','Edit'))
                        <div class="input-group-btn pull-right hidden dropdown" style="width:78px;">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                                @if(User::checkCategoryPermission('DynamicField','Edit'))
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
                                    <li class="li_delete">
                                        <a class="type_active_deactive" type_ad="delete" href="javascript:void(0);" >
                                            <i class="fa fa-trash"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div><!-- /btn-group -->
                        @endif

                        @if( User::is_admin() || User::is('BillingAdmin'))
                            @if(User::checkCategoryPermission('DynamicField','Add'))

                                <a href="#" data-action="showAddModal" id="add-new-dynamicfield" data-type="Dynamic Field" data-modal="add-edit-modal-dynamicfield" class="btn btn-primary pull-right">
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
                    <th width="10%">Item Type</th>
                    <th width="20%">Field Name</th>
                    <th width="20%">DOM Types</th>
                    <th width="20%">Created At</th>
                    <th width="10%">Active</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var checked = '';
                var list_fields  = ['DynamicFieldsID','title','FieldName','FieldDomType','created_at','Status','FieldDescription','FieldOrder','FieldSlug','Type','ItemTypeID','Minimum','Maximum','DefaultValue','SelectVal'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {

                    $('#filter-button-toggle').show();

                    public_vars.$body = $("body");
                    $searchFilter.FieldName = $("#dynamicfield_filter [name='FieldName']").val();
                    $searchFilter.FieldDomType = $("#dynamicfield_filter [name='FieldDomType']").val();
                    $searchFilter.ItemTypeID = $("#dynamicfield_filter [name='ItemTypeID']").val();
                    $searchFilter.Active = $("#dynamicfield_filter select[name='Active']").val();

                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/products/dynamicfields/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push({ "name": "FieldName", "value": $searchFilter.FieldName },
                                        { "name": "FieldDomType", "value": $searchFilter.FieldDomType },
                                        { "name": "ItemTypeID", "value": $searchFilter.ItemTypeID },
                                        { "name": "Active", "value": $searchFilter.Active });

                            data_table_extra_params.length = 0;
                            data_table_extra_params.push({ "name": "FieldName", "value": $searchFilter.FieldName },
                                                        { "name": "FieldDomType", "value": $searchFilter.FieldDomType },
                                                        { "name": "ItemTypeID", "value": $searchFilter.ItemTypeID },
                                                        { "name": "Active", "value": $searchFilter.Active },
                                                        { "name": "Export", "value": 1});

                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[0, 'desc']],
                        "aoColumns": [
                            {"bSortable": false,
                                mRender: function(id, type, full) {
                                    // checkbox for bulk action
                                    return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            },
                            {  "bSortable": true},  // 1 Item Type
                            {  "bSortable": true },  // 2 FieldName
                            {  "bSortable": true },  // 3 FieldDomType
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

                                    var delete_ = "{{ URL::to('products/dynamicfields/{id}/delete')}}";
                                    delete_  = delete_ .replace( '{id}', full[0] );

                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }

                                    action += '</div>';
                                    <?php if(User::checkCategoryPermission('DynamicField','Edit')){ ?>
                                        action += ' <a data-name = "' + full[1] + '" data-id="' + full[0] + '" title="Edit" class="edit-dynamicfield btn btn-default btn-sm btn-smtooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    <?php } ?>
                                    <?php if(User::checkCategoryPermission('DynamicField','Delete') ){ ?>
                                        action += ' <a href="'+delete_+'" data-redirect="{{ URL::to('products')}}" title="Delete"  class="btn delete btn-danger btn-default btn-sm btn-smtooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-trash"></i></a>';
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
                                    "sUrl": baseurl + "/products/dynamicfields/ajax_datagrid/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/products/dynamicfields/ajax_datagrid/csv",
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
                            alert("Please Select Atleast One Dynamic Field.");
                            return false;
                        }

                        if(type_active_deactive=='delete'){
                            response = confirm('Are you sure?');
                            if(!response){
                                return false;
                            }
                            item_update_status_url =  '{{ URL::to('products/dynamicfields/delete_bulk_dynamicfields')}}';
                        }else{
                            item_update_status_url =  '{{ URL::to('products/dynamicfields/update_bulk_dynamicfields_status')}}';
                        }
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
                                "FieldName":$("#dynamicfield_filter [name='FieldName']").val(),
                                "FieldDomType":$("#dynamicfield_filter [name='FieldDomType']").val(),
                                "ItemTypeID":$("#dynamicfield_filter [name='ItemTypeID']").val(),
                                "Active":$("#dynamicfield_filter [name='Active']").val(),
                                "SelectedIDs":SelectedIDs,
                                "criteria_ac":criteria_ac,
                                "type_active_deactive":type_active_deactive,
                            }

                        });

                    });

                    $("#dynamicfield_filter").submit(function(e){
                        e.preventDefault();
                        $searchFilter.FieldName = $("#dynamicfield_filter [name='FieldName']").val();
                        $searchFilter.FieldDomType = $("#dynamicfield_filter [name='FieldDomType']").val();
                        $searchFilter.ItemTypeID = $("#dynamicfield_filter [name='ItemTypeID']").val();
                        $searchFilter.Active = $("#dynamicfield_filter [name='Active']").val();
                         data_table.fnFilter('', 0);
                        return false;
                    });

                    $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('#add-edit-modal-dynamicfield').on('hidden.bs.modal', function () {
                       $("#add-edit-dynamicfield-form").find("input:hidden[name=ItemTypeID]").remove();
                       $("#add-edit-dynamicfield-form").find("input:hidden[name=FieldDomType]").remove();
                    });

                    $('table tbody').on('click', '.edit-dynamicfield', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#add-edit-dynamicfield-form').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            if(list_fields[i] == 'ItemTypeID'){
                                var ItemTypeID=cur_obj.find("input[name='"+list_fields[i]+"']").val();
                                if(ItemTypeID=='' || typeof (ItemTypeID)=='undefined'){
                                    ItemTypeID=0;
                                }
                                $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").val(ItemTypeID).trigger("change");
                                var valitemid=$("input[name='"+list_fields[i]+"']").val();
                                if(ItemTypeID > 0){
                                    $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").attr("disabled",true);
                                    var h_ItemTypeID='<input type="hidden" name="ItemTypeID" value="'+valitemid+'" />';
                                    $("#add-edit-dynamicfield-form").append(h_ItemTypeID);
                                }


                            }
                            if(list_fields[i] == 'FieldDomType'){
                                $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val()).trigger("change");
                                var valdomtype=$("input[name='"+list_fields[i]+"']").val();
                                $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").attr("disabled",true);
                                var h_FieldDomType='<input type="hidden" name="FieldDomType" value="'+valdomtype+'" />';
                                $("#add-edit-dynamicfield-form").append(h_FieldDomType);
                            }
                            if(list_fields[i] == 'Status'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    $('#add-edit-dynamicfield-form [name="Active"]').prop('checked',true)
                                }else{
                                    $('#add-edit-dynamicfield-form [name="Active"]').prop('checked',false)
                                }
                            }else{
                                if(list_fields[i] == 'Minimum' && (cur_obj.find("input[name='FieldDomType']").val() == 'string' || cur_obj.find("input[name='FieldDomType']").val() == 'numeric')){
                                    var min=cur_obj.find("input[name='"+list_fields[i]+"']").val();
                                    var minmax='<div class="form-group"><label for="field-5" class="control-label">Default Value </label>{{ Form::text("DefaultValue", "", array("class"=>"form-control"))  }}</div><div class="form-group"><label for="field-5" class="control-label">Min </label>{{ Form::text("Minimum", "", array("class"=>"form-control"))  }}</div><div class="form-group"><label for="field-5" class="control-label">Max </label>{{ Form::text("Maximum", "", array("class"=>"form-control"))  }}</div>';
                                    $("#minmaxdiv").html(minmax);
                                }
                                if(list_fields[i] == 'SelectVal' && (cur_obj.find("input[name='FieldDomType']").val() == 'select')){
                                    var SelectVal=cur_obj.find("input[name='"+list_fields[i]+"']").val();
                                    var SelectValDiv='<div class="form-group"><label for="field-5" class="control-label">Select Value (separated by comma) </label>{{ Form::text("SelectVal", "", array("class"=>"form-control"))  }}</div>';
                                    $("#minmaxdiv").html(SelectValDiv);
                                    //$("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").attr("disabled",true);
                                    
                                }
                                $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());

                            }
                        }

                        $("#add-edit-modal-dynamicfield [name='ProductClone']").val(0);
                        $('#add-edit-modal-dynamicfield h4').html('Edit Dynamic Field');
                        $('#add-edit-modal-dynamicfield').modal('show');
                    });
                    $('table tbody').on('click', '.clone-product', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#add-edit-dynamicfield-form').trigger("reset");
                        var cur_obj = $(this).prev().prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){

                            if(list_fields[i] == 'Active'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    $('#add-edit-dynamicfield-form [name="Active"]').prop('checked',true)
                                }else if(list_fields[i] == 'AppliedTo'){
                                    $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val()).trigger("change");
                                }else{
                                    $('#add-edit-dynamicfield-form [name="Active"]').prop('checked',false)
                                }
                            }else{
                                $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }
                        }
                        var DynamicFields = $(this).prev().find('input[name^=DynamicFields]');
                        for(var j=0;j<DynamicFields.length;j++) {
                            var dfName = DynamicFields[j].getAttribute('name');
                            var dfValue = DynamicFields[j].value;
                            $('#add-edit-dynamicfield-form').find('input[name^=DynamicFields]').each(function(){
                                if($(this).attr('name') == dfName){
                                    $(this).val(dfValue);
                                }
                            });
                        }
                        $("#add-edit-modal-dynamicfield [name='ProductClone']").val(1);
                        $('#add-edit-modal-dynamicfield h4').html('Clone Item');
                        $('#add-edit-modal-dynamicfield').modal('show');
                    });

                    $('#add-new-dynamicfield').click(function (ev) {
                        $('#add-edit-dynamicfield-form').trigger("reset");
                        $("#add-edit-modal-dynamicfield [name='ProductClone']").val(0);
                        $("#add-edit-modal-dynamicfield [name='ItemTypeID']").removeAttr('disabled');
                        $("#add-edit-modal-dynamicfield [name='FieldDomType']").removeAttr('disabled');
                        var ItemTypeid=$("#dynamicfield_filter [name='ItemTypeID']").val();
                        setTimeout(function(){
                            console.log("ItemTypeID="+ItemTypeid);
                            $("#add-edit-modal-dynamicfield [name='ItemTypeID']").val(ItemTypeid).trigger('change');
                        }, 1000);

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
    @include("products.dynamicfields.dynamicfieldmodal")
@stop
