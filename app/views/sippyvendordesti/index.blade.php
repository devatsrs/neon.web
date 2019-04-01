@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" method="get" id="ratetable_filter">
                <div class="form-group">
                    <label for="Search" class="control-label">CodeRule</label>
                    <input class="form-control" name="CodeRule" id="CodeRule"  type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Trunk</label>
                    {{ Form::select('TrunkID', $trunks, '', array("class"=>"select2","data-type"=>"trunk")) }}
                </div>

                <div class="form-group">
                    <label for="Search" class="control-label">Vendor</label>
                    {{Form::select('VendorID', $all_accounts, '' ,array("class"=>"form-control select2"))}}
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
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>

    <li class="active">
        <strong>Sippy Vendor Destination </strong>
    </li>
</ol>
<h3>Sippy Vendor Destination</h3>
<p style="text-align: right;">
@if(User::checkCategoryPermission('AutoRateImport','Add'))
    <a href="#" id="add-new-account-setting" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif

</p>

<div class="cler row">
    <div class="col-md-12">
        <form role="form" id="form1" method="post" class="form-horizontal form-groups-bordered validate" novalidate>
            <div class="form-group">
                        <div class="col-md-12">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                    <tr>
                                        <th >Vendor Name</th>
                                        <th >Code-Rule</th>
                                        <th >Trunk</th>
                                        <th >Destination Set</th>
                                        <th >Created At</th>
                                        <th >Modified By</th>

                                        <th >Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                </tbody>
                            </table>
                        </div>
                    </div>
        </form>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {

    $('#filter-button-toggle').show();

    var $searchFilter = {};
    var update_new_url;
        $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
        $searchFilter.VendorID = $("#ratetable_filter [name='VendorID']").val();
		$searchFilter.CodeRule = $('#ratetable_filter [name="CodeRule"]').val();
        $searchFilter.SettingType = 1;
        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/sippy_vendor_destination/ajax_datagrid/1",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[4, "desc"]],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"TrunkID","value":$searchFilter.TrunkID},{"name":"SettingType","value":$searchFilter.SettingType},{"name":"VendorID","value":$searchFilter.VendorID},{"name":"CodeRule","value":$searchFilter.CodeRule});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"TrunkID","value":$searchFilter.TrunkID},{"name":"SettingType","value":$searchFilter.SettingType},{"name":"VendorID","value":$searchFilter.VendorID},{"name":"CodeRule","value":$searchFilter.CodeRule},{"name":"Export","value":1});
            },
            "fnRowCallback": function(nRow, aData) {
                $(nRow).attr("id", "host_row_" + aData[2]);
            },
            "aoColumns":
                    [
                        {"bSortable": true},
                        {"bSortable": false},
                        {"bSortable": false},
                        {"bSortable": false},
                        {"bSortable": true},
                        {"bSortable": false},

                        {
                            mRender: function(id, type, full) {
                                var action, delete_;
                                delete_ = "{{ URL::to('/sippy_vendor_destination/{id}/delete')}}";

                                delete_ = delete_.replace('{id}', id);
                                @if(User::checkCategoryPermission('AutoRateImport','Add'))
                                action = '<a title="Edit" data-id="'+id+'" data-VendorID="'+full[7]+'" data-TrunkID="'+full[8]+'" data-DestinationSet="'+full[9]+'" data-CodeRule="'+full[1]+'" class="edit-sippyVendorDesti btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';
                                action += ' <a data-name = "' + full[1] + '" data-id="' + id + '" data-VendorID="'+full[7]+'" data-TrunkID="'+full[8]+'" data-DestinationSet="'+full[9]+'" data-CodeRule="'+full[1]+'" title="CLone" class="clone-product btn btn-default btn-smtooltip-primary" data-original-title="Clone" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-clone"></i>&nbsp;</a>';
                                @endif

                                <?php if(User::checkCategoryPermission('AutoRateImport','Delete') ) { ?>
                                    action += ' <a title="Delete" href="' + delete_ + '"  class="btn btn-default delete btn-danger btn-sm" data-loading-text="Loading..."><i class="entypo-trash"></i></a>';
                                <?php } ?>
                                //action += status_link;
                                return action;
                            }
                        }
                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/sippy_vendor_destination/ajax_datagrid/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/sippy_vendor_destination/ajax_datagrid/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    }, 
            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                $(".btn.delete").click(function(e) {
                    e.preventDefault();
                    response = confirm('Are you sure?');
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                    if (response) {
                        $(this).text('Loading..');
                        $('#table-4_processing').css('visibility','visible');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                                //    $(this).text('Loading..');
                            },
                            success: function(response) {
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                    data_table.fnFilter('', 0);
                                }
                                $('#table-4_processing').css('visibility','hidden');
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
                $(".btn.change_status").click(function(e) {
                   $(this).button('loading');
                    $.ajax({
                        url: $(this).attr("href"),
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $(this).button('reset');
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
                    return false;
                });

            }
        });

        $('table tbody').on('click','.edit-sippyVendorDesti',function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            $("#add-new-form").trigger("reset");
            $("#add-new-form .select2").trigger("change.select2");
            $('#modal-add-new-account-setting').trigger("reset");
            $('#modal-add-new-account-setting .modal-title').html("Edit Sippy Vendor Destination");

            $("#modal-add-new-account-setting [name='VendorID']").select2('val', $(this).attr('data-vendorid'));
            var TrunkID=$(this).attr('data-trunkid');

            $("#modal-add-new-account-setting [name='TrunkID']").val(TrunkID).trigger("change");
            var destinationset=$(this).attr('data-destinationset');

            $("#modal-add-new-account-setting [name='DestinationSetID']").val(destinationset).trigger("change");
            $("#modal-add-new-account-setting [name='SippyVendorDestiMapID']").val($(this).attr('data-id'));
            $("#modal-add-new-account-setting [name='CodeRule']").val($(this).attr('data-CodeRule'));

            $("#modal-add-new-account-setting [name='ProductClone']").val('');
            $('#modal-add-new-account-setting').modal('show');
        });

    $('table tbody').on('click', '.clone-product', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-form').trigger("reset");

        $("#modal-add-new-account-setting [name='VendorID']").select2('val', $(this).attr('data-vendorid'));
        var TrunkID=$(this).attr('data-trunkid');
        if(TrunkID==0){
            TrunkID='';
        }

        $("#modal-add-new-account-setting [name='TrunkID']").val(TrunkID).trigger("change");
        var destinationset=$(this).attr('data-destinationset');
        if(destinationset==0){
            destinationset='';
        }

        $("#modal-add-new-account-setting [name='DestinationSetID']").val(destinationset).trigger("change");
        $("#modal-add-new-account-setting [name='SippyVendorDestiMapID']").val($(this).attr('data-id'));
        $("#modal-add-new-account-setting [name='CodeRule']").val($(this).attr('data-CodeRule'));


        $("#modal-add-new-account-setting [name='ProductClone']").val(1);
        $('#modal-add-new-account-setting h4').html('Clone Sippy Vendor Destination');
        $('#modal-add-new-account-setting').modal('show');
    });

        $("#ratetable_filter").submit(function(e) {
            e.preventDefault();
            $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
            $searchFilter.CodeRule = $("#ratetable_filter [name='CodeRule']").val();
			$searchFilter.VendorID = $('#ratetable_filter [name="VendorID"]').val();
            $searchFilter.SettingType = 1;
            data_table.fnFilter('', 0);
            return false;
         });
        $("#add-new-account-setting").click(function(ev) {
             ev.preventDefault();
             $("#add-new-form").trigger("reset");
             $("#add-new-form .select2").trigger("change.select2");
            $('#modal-add-new-account-setting .modal-title').html("Add New Vendor Destination Setting");
             $("#modal-add-new-account-setting [name='AutoImportSettingID']").val('');
             $('#modal-add-new-account-setting').modal('show', {backdrop: 'static'});
         });
         $("#add-new-form").submit(function(ev){
            ev.preventDefault();
             var SippyVendorDestiMapID = $("#add-new-form [name='SippyVendorDestiMapID']").val();
             var ProductClone = $("#add-new-form [name='ProductClone']").val();

             if( typeof SippyVendorDestiMapID != 'undefined' && SippyVendorDestiMapID != '' && ProductClone==''){
                 update_new_url = baseurl + '/sippy_vendor_destination/'+SippyVendorDestiMapID+'/update';
             }else if(typeof ProductClone != 'undefined' && ProductClone==1){
                 update_new_url = baseurl + '/sippy_vendor_destination/store';
             }
             else{
                 update_new_url = baseurl + '/sippy_vendor_destination/store';
             }

            console.log(update_new_url);
            submit_ajax(update_new_url,$("#add-new-form").serialize());
         });

        $("select[name='TypePKID']").on('change', function(){
            var TypePKID   = $("select[name=TypePKID]").val();
            if(TypePKID!=""){
                getTrunk("vendor",TypePKID);
            }else{
                toastr.error("Please Select One Vendor", "Error", toastr_opts);
            }
        });

    });
    function getTrunk($RateUploadType,id) {
        return $.ajax({
            url: '{{URL::to('rate_upload/getTrunk')}}/'+$RateUploadType,
            data: 'Type='+$RateUploadType+'&id='+id,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    var html = '';
                    var Trunks = response.trunks;

                    for(key in Trunks) {
                        if(Trunks[key] == 'Select') {
                            html += '<option value="'+key+'" selected>'+Trunks[key]+'</option>';
                        } else {
                            html += '<option value="'+key+'">'+Trunks[key]+'</option>';
                        }
                    }
                    $("select[name=TrunkID]").html(html).trigger('change');
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            },
            error: function () {
                toastr.error("error", "Error", toastr_opts);
            }
        });
    }
</script>
@include('includes.errors')
@include('includes.success')
@stop
@section('footer_ext')
@parent

<div class="modal fade" id="modal-add-new-account-setting">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Vendor Destination Setting</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Vendor *</label>
                                {{Form::select('VendorID', $all_accounts, '' ,array("class"=>"form-control select2"))}}
                                <input type="hidden" name="SippyVendorDestiMapID">
                                <input type="hidden" name="ProductClone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Trunk</label>
                                {{Form::SelectControl('trunk')}}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Code Rule</label>
                                <input type="text" name="CodeRule" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Destination Set</label>
                                {{Form::select('DestinationSetID', $DestinationSet, '' ,array("class"=>"form-control select2"))}}
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop