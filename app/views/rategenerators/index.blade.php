@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Rate Generator</strong>
    </li>
</ol>


<h3>Rate Generator </h3>
<div class="float-right">
@if(User::checkCategoryPermission('RateGenerator','Add'))
    <a href="{{URL::to('rategenerators/create')}}" class="btn add btn-primary btn-sm btn-icon icon-left">
        <i class="entypo-floppy"></i>
        Add New
    </a>
@endif

</div>
<br>
<br>
<div class="row">
    <div class="col-md-12">
        <form id="ratetable_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
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
                        <label for="field-1" class="col-sm-2 control-label">Active</label>
                        <div class="col-sm-2">
                            <?php $active = [""=>"Both","1"=>"Active","0"=>"Inactive"]; ?>
                            {{ Form::select('Active', $active, 1, array("class"=>"form-control selectboxit")) }}
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


<br>

<div class=" clear row">
    <div class="col-md-12">
        <table class="table table-bordered datatable" id="table-4">
            <thead>
                <tr>
                    <th width="25%">Name</th>
                    <th width="25%">Trunk</th>
                    <th width="10%">Currency</th>
                    <th width="10%">Status</th>
                    <th width="25%">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>



    </div>
</div>

<script type="text/javascript">
    var $searchFilter = {};
    var data_table = '';
    jQuery(document).ready(function($) {
        var update_rate_table_url;
        $('#ratetable_filter').submit(function(e) {
            e.preventDefault();
            $searchFilter.Active = $('#ratetable_filter [name="Active"]').val();
            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/rategenerators/ajax_datagrid",
                "fnServerParams": function (aoData) {
                    aoData.push({ "name": "Active", "value": $searchFilter.Active });
                },
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[3, "desc"]],
                "aoColumns": [
                    {},
                    {},
                    {},
                    {
                        mRender: function (status, type, full) {
                            if (status == 1)
                                return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                            else
                                return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                        }
                    },
                    {
                        mRender: function (id, type, full) {
                            var action, edit_, delete_;
                            edit_ = "{{ URL::to('rategenerators/{id}/edit')}}";
                            delete_ = "{{ URL::to('rategenerators/{id}/delete')}}";
                            generate_new_rate_table_ = "{{ URL::to('rategenerators/{id}/generate_rate_table/create')}}";
                            update_existing_rate_table_ = "{{ URL::to('rategenerators/{id}/generate_rate_table/update')}}";
                            var status_link = active_ = "";
                            if (full[3] == "1") {
                                active_ = "{{ URL::to('/rategenerators/{id}/change_status/0')}}";
                                status_link = ' <button href="' + active_ + '"  class="btn change_status btn-danger btn-sm" data-loading-text="Loading...">Deactivate</button>';
                            } else {
                                active_ = "{{ URL::to('/rategenerators/{id}/change_status/1')}}";
                                status_link = ' <button href="' + active_ + '"    class="btn change_status btn-success btn-sm " data-loading-text="Loading...">Activate</button>';
                            }


                            edit_ = edit_.replace('{id}', id);
                            delete_ = delete_.replace('{id}', id);
                            generate_new_rate_table_ = generate_new_rate_table_.replace('{id}', id);
                            update_existing_rate_table_ = update_existing_rate_table_.replace('{id}', id);
                            status_link = status_link.replace('{id}', id);
                            action = '';

                            <?php if(User::checkCategoryPermission('RateGenerator','Edit')) { ?>
                            action += '<a href="' + edit_ + '" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit</a> '
                            action += status_link;
                            action += ' <a href="' + delete_ + '" data-redirect="{{URL::to("rategenerators")}}" data-id = '+id+'  class="btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete</a> '
                            if (full[3] == 1) { /* When Status is 1 */
                                action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Generate Rate Table <span class="caret"></span></button>'
                                action += '<ul class="dropdown-menu dropdown-green" role="menu"><li><a href="' + generate_new_rate_table_ + '" class="generate_rate create" >Create New Rate Table</a></li><li><a href="' + update_existing_rate_table_ + '" class="generate_rate update" data-trunk="' + full[5] + '" data-codedeck="' + full[6] + '" data-currency="' + full[7] + '">Update Existing Rate Table</a></li></ul></div>';
                            }
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
                            "sUrl": baseurl + "/rategenerators/exports/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/rategenerators/exports/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function () {

                    $(".btn.delete").click(function (e) {
                        e.preventDefault();
                        var id = $(this).attr('data-id');
                        var url = baseurl + '/rategenerators/'+id+'/ajax_existing_ratetable_cronjob';
                        $('#delete-rate-table-form [name="RateGeneratorID"]').val(id);
                        $('#modal-delete-ratetables .container').html('');
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'html',
                            success: function (response) {
                                $(".btn.delete").button('reset');
                                if(response) {
                                    $('#modal-delete-ratetables .container').html(response);
                                }
                                $('#modal-delete-ratetables').modal('show');
                            },

                            // Form data
                            //data: {},
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                        return false;

                    });

                    $(".generate_rate.create").click(function (e) {
                        e.preventDefault();
                        $('#update-rate-table-form').trigger("reset");
                        $('#modal-update-rate').modal('show', {backdrop: 'static'});
                        $('#RateTableIDid').hide();
                        $('#RateTableNameid').show();
                        $('#modal-update-rate h4').html('Generate Rate Table');
                        update_rate_table_url = $(this).attr("href");

                        return false;

                    });
                    $(".btn.change_status").click(function (e) {
                        //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                        $(this).button('loading');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
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
        });
        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        $('#ratetable_filter').submit();
        $('body').on('click', '.generate_rate.update', function (e) {

            e.preventDefault();
            $('#modal-update-rate').modal('show', {backdrop: 'static'});
            $('#update-rate-table-form').trigger("reset");
            var trunkID = $(this).attr("data-trunk");
            var codeDeckId = $(this).attr("data-codedeck");
            var CurrencyID = $(this).attr("data-currency");
            $.ajax({
                url: baseurl + "/rategenerators/ajax_load_rate_table_dropdown",
                type: 'GET',
                dataType: 'text',
                success: function(response) {

                    $("#modal-update-rate #DropdownRateTableID").html('');
                    $("#modal-update-rate #DropdownRateTableID").html(response);
                    $("#modal-update-rate #DropdownRateTableID select.selectboxit").addClass('visible');
                    $("#modal-update-rate #DropdownRateTableID select.selectboxit").selectBoxIt();

                },
                // Form data
                data: "TrunkID="+trunkID+'&CodeDeckId='+codeDeckId+'&CurrencyID='+CurrencyID ,
                cache: false,
                contentType: false,
                processData: false
            });
            /*
            * Submit and Generate Joblog
            * */
            update_rate_table_url = $(this).attr("href");
            $('#RateTableIDid').show();
            $('#RateTableNameid').hide();
            $('#modal-update-rate h4').html('Update Rate Table');
        });

        $('#update-rate-table-form').submit(function (e) {
            e.preventDefault();
            if( typeof update_rate_table_url != 'undefined' && update_rate_table_url != '' ){
                $.ajax({
                    url: update_rate_table_url,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $(".btn.generate").button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            reloadJobsDrodown(0);
                            $('#modal-update-rate').modal('hide');
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".btn.generate").button('reset');
                        $(".save.TrunkSelect").button('reset');

                    },
                    // Form data
                    data: $('#update-rate-table-form').serialize(),
                    cache: false

                });
            }else{
                $(".btn").button('reset');
                $('#modal-update-rate').modal('hide');
                toastr.info('Nothing Changed. Try again', "info", toastr_opts);
            }
        });

        $('#delete-rate-table-form').submit(function (e) {
            e.preventDefault();
            var RateGeneratorID = $(this).find('[name="RateGeneratorID"]').val();
            var url = baseurl + '/rategenerators/'+RateGeneratorID+'/delete';
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        data_table.fnFilter('', 0);
                        reloadJobsDrodown(0);
                        $('#modal-delete-ratetables').modal('hide');
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $(".save.TrunkSelect").button('reset');

                },
                // Form data
                data: $('#update-rate-table-form').serialize(),
                cache: false

            });
        });

        $(document).on('click','.cronjobedelete',function(){
            if($(this).hasClass('icon-left')){
                var tr = $(this).parents('tr');
                tr.addClass('selected');
                tr.find('.rowcheckbox').prop("checked", true);
            }
            var SelectedIDs = getselectedIDs("cronjob-table");
            if (SelectedIDs.length == 0) {
                toastr.error('Please select at least one cronjob.', "Error", toastr_opts);
                return false;
            }else{
                if(confirm('Are you sure you want to delete selected cron job?')){
                    var rateGeneratorID = $('#delete-rate-table-form [name="RateGeneratorID"]').val();
                    var url = baseurl + "/rategenerators/"+rateGeneratorID+"/deletecronjob";
                    var cronjobs = SelectedIDs.join(",");
                    $.ajax({
                        url: url,
                        type:'POST',
                        data:{cronjobs:cronjobs},
                        datatype:'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                $('#modal-delete-ratetables .container').html(response.table);
                                $('.selectall').prop("checked", false);
                                toastr.success(response.message,'Success', toastr_opts);
                            }else{
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        }

                    });
                }
            }
        });

        $(document).on('click', '#cronjob-table tbody tr', function() {
            $(this).toggleClass('selected');
            if($(this).is('tr')) {
                if ($(this).hasClass('selected')) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            }
        });

        $(document).on('click','#selectall',function(){
            if($(this).is(':checked')){
                checked = 'checked=checked';
                $(this).prop("checked", true);
                $(this).parents('table').find('tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                    $(this).addClass('selected');
                });
            }else{
                checked = '';
                $(this).prop("checked", false);
                $(this).parents('table').find('tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", false);
                    $(this).removeClass('selected');
                });
            }
        });

        function getselectedIDs(table){
            var SelectedIDs = [];
            $('#'+table+' tr .rowcheckbox:checked').each(function (i, el) {
                var cronjob = $(this).val();
                SelectedIDs[i++] = cronjob;
            });
            return SelectedIDs;
        }
    });

</script>
@include('includes.errors')
@include('includes.success')

<!--Only for Delete operation-->
@include('includes.ajax_submit_script', array('formID'=>'' , 'url' => ('')))
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="modal-update-rate" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="update-rate-table-form" method="post" >

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Update Rate Table</h4>
                </div>

                <div class="modal-body">

                    <div class="row" id="RateTableIDid">
                        <div class="col-md-12">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Select Rate Table</label>
                                <div id="DropdownRateTableID">

                                </div>

                            </div>

                        </div>

                    </div>
                    <div class="row" id="RateTableNameid">
                        <div class="col-md-12">
                            <div class="form-group" >
                                <label for="field-4" class="control-label">Rate Table Name</label>
                                <input type="text" name="RateTableName" class="form-control"  value="" />
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group" >
                                <label for="field-4" class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}"  data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateGeneratorID" value="">
                    <button type="submit"  class="save TrunkSelect btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Ok
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

<div class="modal fade" id="modal-delete-ratetables" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="delete-rate-table-form" method="post" >

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Delete Rate Table</h4>
                </div>

                <div class="modal-body">
                    <div class="container col-md-12"></div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateGeneratorID" value="">
                    <button type="submit"  class="save TrunkSelect btn btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-cancel"></i>
                        Delete
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