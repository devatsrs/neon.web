@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="rategenerator_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="Search" class="control-label">Name</label>
                    <input class="form-control" name="Search" id="Search"  type="text" >
                </div>
                <div class="form-group">
                    <label for="Active" class="control-label">Trunk</label>
                    {{ Form::select('Trunk', $Trunks, 1, array("class"=>"form-control select2 small","id"=>"Trunk")) }}
                </div>
                <div class="form-group">
                    <label for="Active" class="control-label">Active</label>
                    <?php $active = [""=>"Both","1"=>"Active","0"=>"Inactive"]; ?>
                    {{ Form::select('Active', $active, 1, array("class"=>"form-control select2 small","id"=>"Active")) }}
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
  <li> <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Rate Generator</strong> </li>
</ol>
<h3>Rate Generator </h3>
<div class="float-right"> @if(User::checkCategoryPermission('RateGenerator','Add')) <a href="{{URL::to('rategenerators/create')}}" class="btn add btn-primary btn-sm btn-icon icon-left"> <i class="entypo-floppy"></i> Add New </a> @endif </div>
<br>
<br>
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

        $('#filter-button-toggle').show();

        var update_rate_table_url;
        $('#rategenerator_filter').submit(function(e) {
            e.preventDefault();
            $searchFilter.Active = $('#rategenerator_filter [name="Active"]').val();
			$searchFilter.Search = $('#rategenerator_filter [name="Search"]').val();
			$searchFilter.Trunk  = $('#rategenerator_filter [name="Trunk"]').val();
			
            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/rategenerators/ajax_datagrid",
                "fnServerParams": function (aoData) {
                    aoData.push({ "name": "Active", "value": $searchFilter.Active },{ "name": "Search", "value": $searchFilter.Search },{ "name": "Trunk", "value": $searchFilter.Trunk });
                },
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
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
                                status_link = ' <a title="Deactivate" href="' + active_ + '"  class="btn btn-default change_status btn-danger btn-sm" data-loading-text="Loading..."><i class="entypo-minus-circled"></i></a>';
                            } else {
                                active_ = "{{ URL::to('/rategenerators/{id}/change_status/1')}}";
                                status_link = ' <a title="Activate" href="' + active_ + '"    class="btn btn-default change_status btn-success btn-sm" data-loading-text="Loading..."><i class="entypo-check"></i></a>';
                            }


                            edit_ = edit_.replace('{id}', id);
                            delete_ = delete_.replace('{id}', id);
                            generate_new_rate_table_ = generate_new_rate_table_.replace('{id}', id);
                            update_existing_rate_table_ = update_existing_rate_table_.replace('{id}', id);
                            status_link = status_link.replace('{id}', id);
                            action = '';

                            <?php if(User::checkCategoryPermission('RateGenerator','Edit')) { ?>
                            action += ' <a title="Edit" href="' + edit_ + '" class="btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a> '
                            action += status_link;
							
							 @if(User::checkCategoryPermission('RateGenerator','Delete'))
                                action += ' <a title="Delete" href="' + delete_ + '" data-redirect="{{URL::to("rategenerators")}}" data-id = '+id+'  class="btn btn-default btn-sm  delete btn-danger"><i class="entypo-trash"></i></a> '
                            @endif
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
                        var url = baseurl + '/rategenerators/'+id+'/ajax_existing_rategenerator_cronjob';
                        $('#delete-rate-generator-form [name="RateGeneratorID"]').val(id);
                        if(confirm('Are you sure you want to delete selected rate generator?')) {
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'html',
                                success: function (response) {
                                    $(".btn.delete").button('reset');
                                    if (response) {
                                        $('#modal-delete-rategenerator .container').html(response);
                                        $('#modal-delete-rategenerator').modal('show');
                                    }else{
                                        $('#delete-rate-generator-form').submit();
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

                    $(".generate_rate.create").click(function (e) {
                        e.preventDefault();
                        $('#update-rate-generator-form').trigger("reset");
                        $('#modal-update-rate').modal('show', {backdrop: 'static'});
                        $('.radio-replace').removeClass('checked');
                        $('#defaultradiorate').addClass('checked');
                        $('#RateTableIDid').hide();
                        $('#RateTableNameid').show();
                        $('#RateTableReplaceRate').hide();
                        $('#RateTableEffectiveRate').show();
                        $('.when_update_rate_generator').hide();
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
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }
            });
        });

        $('#rategenerator_filter').submit();
        $('body').on('click', '.generate_rate.update', function (e) {

            e.preventDefault();
            $('#modal-update-rate').modal('show', {backdrop: 'static'});
            $('#update-rate-generator-form').trigger("reset");
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
                    $("#modal-update-rate #DropdownRateTableID select.select2").addClass('visible');
                    $("#modal-update-rate #DropdownRateTableID select.select2").select2();

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

            $('.radio-replace').removeClass('checked');
            $('#defaultradiorate').addClass('checked');

            $('#RateTableIDid').show();
            $('#RateTableReplaceRate').show();
            $('#RateTableEffectiveRate').show();
            $('#RateTableNameid').hide();
            $('.when_update_rate_generator').show();
            $('#modal-update-rate h4').html('Update Rate Table');
        });

        $('#update-rate-generator-form').submit(function (e) {
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
                    data: $('#update-rate-generator-form').serialize(),
                    cache: false

                });
            }else{
                $(".btn").button('reset');
                $('#modal-update-rate').modal('hide');
                toastr.info('Nothing Changed. Try again', "info", toastr_opts);
            }
        });

        $('#delete-rate-generator-form').submit(function (e) {
            e.preventDefault();
            if($('#modal-delete-rategenerator .container').is(':empty')) {
                var RateGeneratorID = $(this).find('[name="RateGeneratorID"]').val();
                var url = baseurl + '/rategenerators/' + RateGeneratorID + '/delete';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                            reloadJobsDrodown(0);
                            $('#modal-delete-rategenerator').modal('hide');
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".save.TrunkSelect").button('reset');


                    },
                    // Form data
                    data: $('#update-rate-generator-form').serialize(),
                    cache: false

                });
            }else{
                var SelectedIDs = getselectedIDs("cronjob-table");
                if (SelectedIDs.length == 0) {
                    alert('No cron job selected.');
                    $("#rategenerator-select").button('reset');
                    return false;
                }else{
                    var deleteid = SelectedIDs.join(",");
                    cronjobsdelete(deleteid);
                }
            }
        });

        function cronjobsdelete(deleteid){
            if(confirm('Are you sure you want to delete selected cron job?')){
                var rateGeneratorID = $('#delete-rate-generator-form [name="RateGeneratorID"]').val();
                var url = baseurl + "/rategenerators/"+rateGeneratorID+"/deletecronjob";
                var cronjobs = deleteid;
                $('#modal-delete-rategenerator .container').html('');
                $('#modal-delete-rategenerator').modal('hide');
                $.ajax({
                    url: url,
                    type:'POST',
                    data:{cronjobs:cronjobs},
                    datatype:'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            toastr.success(response.message,'Success', toastr_opts);
                            var url = baseurl + '/rategenerators/'+rateGeneratorID+'/ajax_existing_rategenerator_cronjob';
                            $('#delete-rate-generator-form [name="RateGeneratorID"]').val(rateGeneratorID);
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'html',
                                success: function (response) {
                                    $(".btn.delete").button('reset');
                                    if (response) {
                                        $('#modal-delete-rategenerator .container').html(response);
                                        $('#modal-delete-rategenerator').modal('show');
                                    }else{
                                        $('#delete-rate-generator-form').submit();
                                    }
                                },

                                // Form data
                                //data: {},
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }

                });
            }
        }

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
@include('rategenerators.rategenerator_models')
@stop