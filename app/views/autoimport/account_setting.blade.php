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
                    <label for="Search" class="control-label">Search</label>
                    <input class="form-control" name="Search" id="Search"  type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Trunk</label>
                    {{ Form::select('TrunkID', $trunks, '', array("class"=>"select2","data-type"=>"trunk")) }}
                </div>

                <div class="form-group">
                    <label for="Search" class="control-label">Vendor</label>
                    {{Form::select('TypePKIDVendorReteTable', $all_accounts, '' ,array("class"=>"form-control select2"))}}
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
    <li>
        <a href="{{URL::to('/auto_rate_import/autoimport')}}">Auto Import</a>
    </li>
    <li class="active">
        <strong>Vendor Setting </strong>
    </li>
</ol>
<h3>Vendor Setting</h3>
<p style="text-align: right;">
@if(User::checkCategoryPermission('AutoRateImport','Add'))
    <a href="#" id="add-new-account-setting" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New Setting
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
                                        <th >Trunk</th>
                                        <th >Import File Template</th>
                                        <th >Subject Match</th>
                                        <th >Filename Match</th>
                                        <th >Sender Match</th>
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
        $searchFilter.TypePKID = $("#ratetable_filter [name='TypePKIDVendorReteTable']").val();
		$searchFilter.Search = $('#ratetable_filter [name="Search"]').val();
        $searchFilter.SettingType = 1;
        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/auto_rate_import/ajax_datagrid/1",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "oTableTools": {},
            "aaSorting": [[0, "asc"]],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"TrunkID","value":$searchFilter.TrunkID},{"name":"SettingType","value":$searchFilter.SettingType},{"name":"TypePKID","value":$searchFilter.TypePKID},{"name":"Search","value":$searchFilter.Search});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"TrunkID","value":$searchFilter.TrunkID},{"name":"SettingType","value":$searchFilter.SettingType},{"name":"TypePKID","value":$searchFilter.TypePKID},{"name":"Search","value":$searchFilter.Search},{"name":"Export","value":1});
            },
            "fnRowCallback": function(nRow, aData) {
                $(nRow).attr("id", "host_row_" + aData[2]);
            },
            "aoColumns":
                    [
                        {},
                        {},
                        {},
                        {},
                        {},
                        {},
                        {
                            mRender: function(id, type, full) {
                                var action, delete_;
                                delete_ = "{{ URL::to('/auto_rate_import/{id}/delete')}}";

                                delete_ = delete_.replace('{id}', full[9]);
                                @if(User::checkCategoryPermission('AutoRateImport','Add'))
                                action = '<a title="Edit" data-id="'+id+'" data-AutoImportSettingID="'+full[9]+'" data-TrunkID="'+full[7]+'" data-uploadtemplate="'+full[8]+'" data-subject="'+full[3]+'" data-sendor="'+full[5]+'" data-fileName="'+full[4]+'" class="edit-autoImportSetting btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';
                                @endif

                                <?php if(User::checkCategoryPermission('AutoRateImport','Delete') ) { ?>
                                    action += ' <a title="Delete" href="' + delete_ + '" data-redirect="{{URL::to("/rate_tables")}}"  class="btn btn-default delete btn-danger btn-sm" data-loading-text="Loading..."><i class="entypo-trash"></i></a>';
                                <?php } ?>
                                //action += status_link;
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
                                "sUrl": baseurl + "/auto_rate_import/ajax_datagrid/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/auto_rate_import/ajax_datagrid/csv",
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
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
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
        $('table tbody').on('click','.edit-autoImportSetting',function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            $("#add-new-form").trigger("reset");
            $("#add-new-form .select2").trigger("change.select2");
            $('#modal-add-new-account-setting').trigger("reset");
            $('#modal-add-new-account-setting .modal-title').html("Edit Vendor Setting");
            $("#modal-add-new-account-setting [name='TypePKID']").select2('val', $(this).attr('data-id'));
            $("#modal-add-new-account-setting [name='ImportFileTempleteID']").select2('val', $(this).attr('data-uploadtemplate'));
            $("#modal-add-new-account-setting [name='TrunkID']").select2('val', $(this).attr('data-TrunkID'));
            $("#modal-add-new-account-setting [name='Subject']").val($(this).attr('data-subject'));
            $("#modal-add-new-account-setting [name='FileName']").val($(this).attr('data-fileName'));
            $("#modal-add-new-account-setting [name='SendorEmail']").val($(this).attr('data-sendor'));
            $("#modal-add-new-account-setting [name='AutoImportSettingID']").val($(this).attr('data-AutoImportSettingID'));
            $('#modal-add-new-account-setting').modal('show');
        });
        $("#ratetable_filter").submit(function(e) {
            e.preventDefault();
            $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
            $searchFilter.TypePKID = $("#ratetable_filter [name='TypePKIDVendorReteTable']").val();
			$searchFilter.Search = $('#ratetable_filter [name="Search"]').val();
            $searchFilter.SettingType = 1;
            data_table.fnFilter('', 0);
            return false;
         });
        $("#add-new-account-setting").click(function(ev) {
             ev.preventDefault();
             $("#add-new-form").trigger("reset");
             $("#add-new-form .select2").trigger("change.select2");
            $('#modal-add-new-account-setting .modal-title').html("Add New Vendor Setting");
             $("#modal-add-new-account-setting [name='AutoImportSettingID']").val('');
             $('#modal-add-new-account-setting').modal('show', {backdrop: 'static'});
         });
         $("#add-new-form").submit(function(ev){
            ev.preventDefault();
            update_new_url = baseurl + '/auto_rate_import/account_setting/store';
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
                    <h4 class="modal-title">Add New Vendor Setting</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Vendor</label>
                                {{Form::select('TypePKID', $all_accounts, '' ,array("class"=>"form-control select2"))}}
                                <input type="hidden" name="Type" value="1">
                                <input type="hidden" name="AutoImportSettingID">
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
                               <label for="field-5" class="control-label">Upload Template</label>
                               {{ Form::select('ImportFileTempleteID', $uploadtemplate, '' , array("class"=>"select2")) }}
                           </div>
                       </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Subject</label>
                                <input type="text" name="Subject" class="form-control" value="" />
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">FileName</label>
                                <input type="text" name="FileName" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Sendor Email</label>
                                <input type="text" name="SendorEmail" class="form-control" value="" />
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