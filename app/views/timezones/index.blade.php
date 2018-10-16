@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" action="javascript:void(0);" method="get" id="timezones-search">
                <div class="form-group">
                    <label for="Title" class="control-label">Title</label>
                    <input class="form-control" name="Title" id="Title"  type="text" >
                </div>
                <div class="form-group">
                    <label for="Title" class="control-label">Status</label>
                    <p class="make-switch switch-small">
                        {{Form::checkbox('Status', '1', true, [])}}
                    </p>
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
            <a href="javascript:void(0)">Timezones</a>
        </li>
    </ol>

    <h3>Timezones</h3>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="clear"></div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="input-group-btn pull-right" style="width:76px;" id="btn-action">
                        @if(User::checkCategoryPermission('Timezones','Edit'))
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                                @if(User::checkCategoryPermission('Timezones','Edit'))
                                    <li>
                                        <a class="changeSelectedStatus" id="ActiveSelected" href="javascript:;" >
                                            Activate
                                        </a>
                                    </li>
                                    <li>
                                        <a class="changeSelectedStatus" id="DeactiveSelected" href="javascript:;" >
                                            Deactivate
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>

                    @if(User::checkCategoryPermission('Timezones','Add'))
                        <p style="text-align: right;">
                            <a id="btn-add-new-timezones" href="javascript:void(0);" class="btn btn-primary ">
                                <i class="entypo-plus"></i>
                                Add New
                            </a>
                        </p>
                    @endif
                </div>
                <div class="clear"></div>
            </div>

            <table class="table table-bordered datatable" id="TimezoneDataTable">
                <thead>
                <tr>
                    <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                    <th>Title</th>
                    <th>From Time</th>
                    <th>To Time</th>
                    <th>Days Of Week</th>
                    <th>Days Of Month</th>
                    <th>Months</th>
                    <th>Apply IF</th>
                    <th>Modified Date</th>
                    <th>Modified By</th>
                    <th width="8%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var ApplyIF,DaysOfWeeks,Months;
                var $searchFilter = {};
                var checked='';
                var list_fields  = ['TimezonesID','Title','FromTime','ToTime','DaysOfWeek','DaysOfMonth','Months','ApplyIF','updated_at','updated_by','Status'];

                jQuery(document).ready(function ($) {
                    getTimezonesVariables();
                    $('#filter-button-toggle').show();

                    $("#timezones-search").submit(function(e) {
                        $searchFilter.Title = Title = $("#timezones-search input[name='Title']").val();
                        $searchFilter.Status = Status = $("#timezones-search input[name='Status']").is(":checked") ? 1 : 0;

                        data_table = $("#TimezoneDataTable").dataTable({
                            "bDestroy": true,
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/timezones/search_ajax_datagrid/type",
                            "fnServerParams": function (aoData) {
                                aoData.push({"name": "Title", "value": Title},{"name": "Status", "value": Status});
                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "Title", "value": Title},{"name": "Status", "value": Status},{"name":"Export","value":1});
                            },
                            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
                                {
                                    "bSortable": false, //TimezonesID
                                    mRender: function(id, type, full) {
                                        if(id !=1) { // if not default timezone
                                            return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                        } else {
                                            return '';
                                        }
                                    }
                                }, // 0 //TimezonesID
                                {  "bSortable": true },  // 1 Timezones Name (Title)
                                {  "bSortable": true },  // 2 From Time
                                {  "bSortable": true },  // 3 To Time
                                {
                                    "bSortable": true,
                                    mRender: function (id, type, full) {
                                        var days    = id.split(',');
                                        var days2   = Array();
                                        $.each(days,function(index, value) {
                                            days2.push(DaysOfWeeks[value]);
                                        });
                                        return days2;
                                    }
                                },  // 4 DaysOfWeek
                                {  "bSortable": true },  // 5 DaysOfMonth
                                {
                                    "bSortable": true,
                                    mRender: function (id, type, full) {
                                        var months    = id.split(',');
                                        var months2   = Array();
                                        $.each(months,function(index, value) {
                                            months2.push(Months[value]);
                                        });
                                        return months2;
                                    }
                                },  // 6 Months
                                {
                                    "bSortable": true,
                                    mRender: function (id, type, full) {
                                        return ApplyIF[id];
                                    }
                                },  // 7 ApplyIF
                                {  "bSortable": false }, // 8 Modified at
                                {  "bSortable": false }, // 9 Modified By
                                {  // 10 Status //Action
                                    "bSortable": false,
                                    mRender: function (id, type, full) {
                                        var action, edit_, delete_;
                                        delete_ = "{{ URL::to('timezones/{id}/delete')}}";
                                        edit_   = "{{ URL::to('timezones/{id}/edit')}}";
                                        delete_ = delete_ .replace( '{id}', full[0] );
                                        edit_   = edit_.replace( '{id}', full[0] );

                                        action = '<div class = "hiddenRowData" >';
                                        for(var i = 0 ; i<list_fields.length; i++){
                                            action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                        }
                                        action += '</div>';
                                        if(full[0] != 1) {// can't edit/delete default timezone, default timezone id is 1
                                            <?php if(User::checkCategoryPermission('Timezones', 'Edit') ){ ?>
                                                    action += ' <a href="' + edit_ + '" title="Edit" class="edit-timezones btn btn-default btn-xs" data-name="Edit Timezones"><i class="entypo-pencil"></i>&nbsp;</a>';
                                            <?php } ?>
                                            <?php if(User::checkCategoryPermission('Timezones', 'Delete') ){ ?>
                                            action += ' <a href="'+delete_+'" title="Delete" class="btn delete btn-danger btn-default btn-xs"><i class="entypo-trash"></i></a>';
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
                                        "sUrl": baseurl + "/timezones/search_ajax_datagrid/xlsx",
                                        sButtonClass: "save-collection btn-sm"
                                    },
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "CSV",
                                        "sUrl": baseurl + "/timezones/search_ajax_datagrid/csv",
                                        sButtonClass: "save-collection btn-sm"
                                    }
                                ]
                            },
                            "fnDrawCallback": function () {
                                $(".dataTables_wrapper select").select2({
                                    minimumResultsForSearch: -1
                                });

                                $("#selectall").click(function(ev) {
                                    var is_checked = $(this).is(':checked');
                                    $('#TimezoneDataTable tbody tr').each(function(i, el) {
                                        if (is_checked) {
                                            $(this).find('.rowcheckbox').prop("checked", true);
                                            $(this).addClass('selected');
                                        } else {
                                            $(this).find('.rowcheckbox').prop("checked", false);
                                            $(this).removeClass('selected');
                                        }
                                    });
                                });

                            }

                        });
                    });

                    $('#TimezoneDataTable tbody').on('click', 'tr', function() {
                        if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                            if (checked == '') {
                                $(this).toggleClass('selected');
                                if ($(this).hasClass('selected')) {
                                    $(this).find('.rowcheckbox').prop("checked", true);
                                } else {
                                    $(this).find('.rowcheckbox').prop("checked", false);
                                }
                            }
                        }
                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('body').on('click', '.btn.delete', function (e) {
                        e.preventDefault();
                        var url = $(this).attr("href");

                        response = confirm('Are you sure?');

                        if (response) {
                            $.ajax({
                                url: url+'/type',
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    if(response.status == 'pending') {
                                        confirmation = confirm(response.message);

                                        if (confirmation) {
                                            $.ajax({
                                                url: url+'/deleteall',
                                                type: 'POST',
                                                dataType: 'json',
                                                success: function (response) {
                                                    $(".btn.delete").button('reset');
                                                    if (response.status == 'success') {
                                                        toastr.success(response.message, "Success", toastr_opts);
                                                        $("#timezones-search").submit();
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
                                    } else {
                                        $(".btn.delete").button('reset');
                                        if (response.status == 'success') {
                                            toastr.success(response.message, "Success", toastr_opts);
                                            $("#timezones-search").submit();
                                        } else {
                                            toastr.error(response.message, "Error", toastr_opts);
                                        }
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

                    $('#add-edit-timezones-form').submit(function(e){
                        e.preventDefault();
                        var modal       = $(this).parents('.modal');
                        var TimezonesID = $("#add-edit-timezones-form [name='TimezonesID']").val();

                        if( typeof TimezonesID != 'undefined' && TimezonesID != ''){
                            update_new_url = baseurl + '/timezones/update/'+TimezonesID;
                        }else{
                            update_new_url = baseurl + '/timezones/store';
                        }

                        showAjaxScript(update_new_url, new FormData(($('#add-edit-timezones-form')[0])), function(response){
                            $(".btn").button('reset');
                            if (response.status == 'success') {
                                modal.modal('hide');
                                toastr.success(response.message, "Success", toastr_opts);
                                $("#timezones-search").submit();
                            }else{
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        });
                    });

                    $('#btn-add-new-timezones').on('click', function() {
                        $('#add-edit-timezones-form').trigger('reset');
                        $('#add-edit-timezones-form .select2').val('').trigger('change');
                        $('#add-edit-timezones-form').find('input[name=TimezonesID]').val('');
                        $('#add-edit-timezones-form').find('input[name=ApplyIF][value=start]').attr('checked','checked');
                        $("#add-edit-timezones-form").find('input[name=Status]').attr('checked','checked');
                        $('#add-edit-modal-timezones h4').html('Add New Timezones');
                        $('#add-edit-modal-timezones').modal('show');
                    });

                    $(document).on('click','.edit-timezones',function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $('#add-edit-timezones-form').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){

                            if(list_fields[i] == 'ApplyIF'){
                                var val = cur_obj.find("input[name='"+list_fields[i]+"']").val();
                                $('#add-edit-timezones-form [name="ApplyIF"]').prop('checked',false);
                                $('#add-edit-timezones-form [name="ApplyIF"][value='+val+']').prop('checked',true);
                            }else if(list_fields[i] == 'DaysOfWeek' || list_fields[i] == 'DaysOfMonth' || list_fields[i] == 'Months'){
                                var val = cur_obj.find("input[name='"+list_fields[i]+"']").val().split(',');
                                $("#add-edit-timezones-form [name='"+list_fields[i]+"[]']").select2('val',val);
                            }else if(list_fields[i] == 'Status'){
                                var val = cur_obj.find("input[name='"+list_fields[i]+"']").val();
                                if(val == 1) {
                                    $("#add-edit-timezones-form [name='"+list_fields[i]+"']").attr('checked','checked');
                                } else {
                                    $("#add-edit-timezones-form [name='"+list_fields[i]+"']").removeAttr('checked');
                                }
                            }else{
                                $("#add-edit-timezones-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }
                        }
                        $('#add-edit-modal-timezones h4').html('Edit Timezones');
                        $('#add-edit-modal-timezones').modal('show');
                    });

                    $("#timezones-search").trigger('submit');

                    //Change Selected Status Button (Active/Deactive)
                    $(".changeSelectedStatus").off('click');
                    $(".changeSelectedStatus").click(function(ev) {
                        ev.stopPropagation();

                        if($("#ActiveSelected").prop("disabled")) {
                            return false;
                        } else {

                            $(".changeSelectedStatus").prop( "disabled", true );
                            $(".changeSelectedStatus").attr('disabled','disabled');

                            var id = $(this).attr('id');
                            var type;
                            if (id == 'ActiveSelected') {
                                type = 'Active';
                            } else if (id == 'DeactiveSelected') {
                                type = 'Deactive';
                            }

                            var TimezonesIDs = [];
                            var i = 0;
                            $('#TimezoneDataTable tr .rowcheckbox:checked').each(function (i, el) {
                                TimezonesID = $(this).val();
                                TimezonesIDs[i] = TimezonesID;
                                i++;
                            });

                            if (TimezonesIDs.length) {
                                response = confirm('Are you sure?');
                                if (response) {
                                    var formdata = new FormData();
                                    formdata.append('TimezonesIDs', TimezonesIDs);
                                    $.ajax({
                                        url: baseurl + "/timezones/changeSelectedStatus/" + type,
                                        type: 'POST',
                                        dataType: 'json',
                                        success: function (response) {
                                            $(".changeSelectedStatus").prop( "disabled", false );
                                            $(".changeSelectedStatus").removeAttr('disabled');
                                            if (response.status == 'success') {
                                                toastr.success(response.message, "Success", toastr_opts);
                                                $("#timezones-search").trigger('submit');
                                            } else {
                                                toastr.error(response.message, "Error", toastr_opts);
                                            }
                                        },
                                        // Form data
                                        data: formdata,
                                        cache: false,
                                        contentType: false,
                                        processData: false
                                    });
                                }
                            }
                        }
                    });

                });

                function getTimezonesVariables() {
                    $.ajax({
                        url: baseurl + '/timezones/getTimezonesVariables',
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            ApplyIF     = response.ApplyIF;
                            DaysOfWeeks = response.DaysOfWeek;
                            Months      = response.Months;
                        },
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }

            </script>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
@section('footer_ext')
    @parent
    @include("timezones.addeditmodal")
@stop
