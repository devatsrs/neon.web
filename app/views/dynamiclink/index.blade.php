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
                    <label for="field-1" class="control-label">Title</label>
                    {{ Form::text('Title', '', array("class"=>"form-control")) }}
                </div>

                <div class="form-group">
                    <label for="field-5" class="control-label">Currency </label>
                    {{Form::select('CurrencyID',$Currency,'',array("class"=>"form-control select2 small"))}}
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
        <li class="active">
            <a href="javascript:void(0)">Dynamic Link</a>
        </li>
    </ol>

    <h3>Dynamic Link</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
                <div class="row">
                    <div  class="col-md-12">
                        @if(User::checkCategoryPermission('Dynamiclink','Add'))
                        <a href="#" data-action="showAddModal" id="add-new-dynamiclink" data-type="Dynamic Link" data-modal="add-edit-modal-dynamicfield" class="btn btn-primary pull-right">
                            <i class="entypo-plus"></i>
                            Add New
                        </a>
                        @endif
                    </div>
                    <div class="clear"></div>
                </div>
            <br>
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    {{--<th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>--}}
                    <th width="10%">Title</th>
                    <th width="20%">Link</th>
                    <th width="20%">Currency</th>
                    <th width="20%">Created Date</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var checked = '';
                var list_fields  = ['Title','Link','Currency','created_at','CurrencyID','DynamicLinkID'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {

                    $('#filter-button-toggle').show();

                    public_vars.$body = $("body");
                    $searchFilter.Title = $("#dynamicfield_filter [name='Title']").val();
                    $searchFilter.CurrencyID = $("#dynamicfield_filter [name='CurrencyID']").val();

                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/dynamiclink/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push({ "name": "Title", "value": $searchFilter.Title },
                                        { "name": "CurrencyID", "value": $searchFilter.CurrencyID }
                                        );

                            data_table_extra_params.length = 0;
                            data_table_extra_params.push({ "name": "Title", "value": $searchFilter.Title },
                                                        { "name": "CurrencyID", "value": $searchFilter.CurrencyID },
                                                        { "name": "Export", "value": 1});

                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left 'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[4, 'desc']],
                        "aoColumns": [
                            /*{"bSortable": false,
                                mRender: function(id, type, full) {
                                    // checkbox for bulk action
                                    return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            },*/
                            {  "bSortable": true},  // 1 Title
                            {  "bSortable": false },  // 2 Link
                            {  "bSortable": true },  // 3 Currency
                            {  "bSortable": true },  // 4 created_at
                            {
                                //  5  Action
                                "bSortable": false,
                                mRender: function (id, type, full) {

                                    var delete_ = "{{ URL::to('/dynamiclink/{id}/delete')}}";
                                    delete_  = delete_ .replace( '{id}', full[5] );

                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '</div>';
                                    <?php if(User::checkCategoryPermission('Dynamiclink','Edit')){ ?>
                                    action += ' <a data-name = "' + full[0] + '" data-id="' + full[5] + '" title="Edit" class="edit-dynamicfield btn btn-default btn-sm btn-smtooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    <?php } ?>
                                    <?php if(User::checkCategoryPermission('Dynamiclink','Delete') ){ ?>
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
                                    "sUrl": baseurl + "/dynamiclink/ajax_datagrid/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/dynamiclink/ajax_datagrid/csv",
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
                    
                    $("#dynamicfield_filter").submit(function(e){
                        e.preventDefault();
                        $searchFilter.Title = $("#dynamicfield_filter [name='Title']").val();
                        $searchFilter.CurrencyID = $("#dynamicfield_filter [name='CurrencyID']").val();
                         data_table.fnFilter('', 0);
                        return false;
                    });

                    $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });


                    $('table tbody').on('click', '.edit-dynamicfield', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#add-edit-dynamicfield-form').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            if(list_fields[i] == 'CurrencyID'){
                                var cid=cur_obj.find("input[name='CurrencyID']").val();
                                console.log(cid);
                                if(cid==0){
                                    $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").val('').trigger("change");
                                }else{
                                    $("#add-edit-dynamicfield-form [name='"+list_fields[i]+"']").val(cid).trigger("change");
                                }

                            }
                            else {
                                $("#add-edit-dynamicfield-form [name='" + list_fields[i] + "']").val(cur_obj.find("input[name='" + list_fields[i] + "']").val());
                            }
                        }

                        $('#add-edit-modal-dynamicfield h4').html('Edit Dynamic Link');
                        $('#add-edit-modal-dynamicfield').modal('show');
                    });

                    $('#add-new-dynamiclink').click(function (ev) {
                        $('#add-edit-dynamicfield-form').trigger("reset");
                        $("#add-edit-modal-dynamicfield [name='DynamicLinkID']").removeAttr('disabled');
                        $("#add-edit-modal-dynamicfield [name='Currency']").val('0').trigger('change');
                    });

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
    @include("dynamiclink.dynamiclinkmodal")
@stop
