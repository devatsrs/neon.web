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
                    <label for="Search" class="control-label">RateTable</label>
                    {{Form::select('TypePKIDVendorReteTable', $rateTable, '' ,array("class"=>"form-control select2"))}}
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
            <a>Auto Import</a>
        </li>
        <li class="active">
            <strong>RateTable Settings </strong>
        </li>
    </ol>
    <h3>RateTable Setting</h3>
    <p style="text-align: right;">
        @if(User::checkCategoryPermission('RateTables','Add'))
            <a href="#" id="add-new-rate-table-setting" class="btn btn-primary ">
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
                                <th >RateTable</th>
                                <th >Import File Template</th>
                                <th >Subject Match</th>
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
            $searchFilter.SettingType = 2;
            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/auto_rate_import/ajax_datagrid/2",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "oTableTools": {},
                "aaSorting": [[3, "desc"]],
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"TrunkID","value":$searchFilter.TrunkID},{"name":"SettingType","value":$searchFilter.SettingType},{"name":"TypePKID","value":$searchFilter.TypePKID},{"name":"Search","value":$searchFilter.Search});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"TrunkID","value":$searchFilter.TrunkID},{"name":"SettingType","value":$searchFilter.SettingType},{"name":"TypePKID","value":$searchFilter.TypePKID},{"name":"Search","value":$searchFilter.Search});
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
                            {
                                mRender: function(id, type, full) {
                                    var action,  delete_;
                                    delete_ = "{{ URL::to('/auto_rate_import/{id}/delete')}}";

                                    delete_ = delete_.replace('{id}', full[7]);

                                    action = '<a title="Edit" data-id="'+full[4]+'" data-AutoImportSettingID="'+full[7]+'" data-uploadtemplate="'+full[5]+'" data-subject="'+full[2]+'" data-sendor="'+full[3]+'" data-fileName="'+full[6]+'" class="edit-RateTableSetting btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';

                                    <?php if(User::checkCategoryPermission('RateTables','Delete') ) { ?>
                                            action += ' <a title="Delete" href="' + delete_ + '" data-redirect="{{URL::to("/rate_tables")}}"  class="btn btn-default delete btn-danger btn-sm" data-loading-text="Loading..."><i class="entypo-trash"></i></a>';
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
                }
            });
            $('table tbody').on('click','.edit-RateTableSetting',function(ev){
                ev.preventDefault();
                ev.stopPropagation();
                $('#modal-add-new-rate-table-setting').trigger("reset");
                $("#modal-add-new-rate-table-setting [name='TypePKID']").select2('val', $(this).attr('data-id'));
                $("#modal-add-new-rate-table-setting [name='ImportFileTempleteID']").select2('val', $(this).attr('data-uploadtemplate'));
                $("#modal-add-new-rate-table-setting [name='Subject']").val($(this).attr('data-subject'));
                $("#modal-add-new-rate-table-setting [name='FileName']").val($(this).attr('data-fileName'));
                $("#modal-add-new-rate-table-setting [name='SendorEmail']").val($(this).attr('data-sendor'));
                $("#modal-add-new-rate-table-setting [name='AutoImportSettingID']").val($(this).attr('data-AutoImportSettingID'));

                $('#modal-add-new-rate-table-setting').modal('show');
            });
            $("#ratetable_filter").submit(function(e) {
                e.preventDefault();
                $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
                $searchFilter.TypePKID = $("#ratetable_filter [name='TypePKIDVendorReteTable']").val();
                $searchFilter.Search = $('#ratetable_filter [name="Search"]').val();
                $searchFilter.SettingType = 2;
                data_table.fnFilter('', 0);
                return false;
            });
            $("#add-new-rate-table-setting").click(function(ev) {

                ev.preventDefault();
                $("#modal-add-new-rate-table-setting [name='AutoImportSettingID']").val('');
                $('#modal-add-new-rate-table-setting').modal('show', {backdrop: 'static'});
            });
            $("#add-new-form").submit(function(ev){
                ev.preventDefault();
                update_new_url = baseurl + '/auto_rate_import/rateTable_setting/store';
                submit_ajax(update_new_url,$("#add-new-form").serialize());
            });

        });

    </script>
    @include('includes.errors')
    @include('includes.success')
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-add-new-rate-table-setting">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New RateTable Setting</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">RateTable</label>
                                    {{Form::select('TypePKID', $rateTable, array("class"=>"form-control select2"))}}
                                    <input type="hidden" name="Type" value="2">
                                    <input type="hidden" name="AutoImportSettingID">
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