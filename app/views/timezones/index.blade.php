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
            @if(User::checkCategoryPermission('Timezones','Add'))
                <p style="text-align: right;">
                    <a id="btn-add-new-timezones" href="javascript:void(0);" class="btn btn-primary ">
                        <i class="entypo-plus"></i>
                        Add New
                    </a>
                </p>
            @endif
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>From Time</th>
                    <th>To Time</th>
                    <th>Days Of Week</th>
                    <th>Days Of Month</th>
                    <th>Months</th>
                    <th>Apply IF</th>
                    <th>Created Date</th>
                    <th>Created By</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var ApplyIF,DaysOfWeeks,Months;
                var $searchFilter = {};
                var list_fields  = ['Title','FromTime','ToTime','DaysOfWeek','DaysOfMonth','Months','ApplyIF','created_at','created_by','TimezonesID','Status'];

                jQuery(document).ready(function ($) {
                    getTimezonesVariables();
                    $('#filter-button-toggle').show();

                    $("#timezones-search").submit(function(e) {
                        $searchFilter.Title = Title = $("#timezones-search input[name='Title']").val();

                        data_table = $("#table-4").dataTable({
                            "bDestroy": true,
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/timezones/search_ajax_datagrid/type",
                            "fnServerParams": function (aoData) {
                                aoData.push({"name": "Title", "value": Title});
                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "Title", "value": Title},{"name":"Export","value":1});
                            },
                            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
                                {  "bSortable": true },  // 0 Timezones Name (Title)
                                {  "bSortable": true },  // 1 From Time
                                {  "bSortable": true },  // 2 To Time
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
                                },  // 3 DaysOfWeek
                                {  "bSortable": true },  // 4 DaysOfMonth
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
                                },  // 5 Months
                                {
                                    "bSortable": true,
                                    mRender: function (id, type, full) {
                                        return ApplyIF[id];
                                    }
                                },  // 6 ApplyIF
                                {  "bSortable": false }, // 7 Created at
                                {  "bSortable": false }, // 8 Created By
                                {  // 9 Action
                                    "bSortable": false,
                                    mRender: function (id, type, full) {
                                        var action, edit_, delete_;
                                        delete_ = "{{ URL::to('timezones/{id}/delete')}}";
                                        edit_   = "{{ URL::to('timezones/{id}/edit')}}";
                                        delete_ = delete_ .replace( '{id}', id );
                                        edit_   = edit_.replace( '{id}', id );

                                        action = '<div class = "hiddenRowData" >';
                                        for(var i = 0 ; i<list_fields.length; i++){
                                            action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                        }
                                        action += '</div>';
                                        <?php if(User::checkCategoryPermission('Timezones','Edit') ){ ?>
                                                action += ' <a href="'+edit_+'" title="Edit" class="edit-timezones btn btn-default btn-xs" data-name="Edit Timezones"><i class="entypo-pencil"></i>&nbsp;</a>';
                                        <?php } ?>
                                                <?php if(User::checkCategoryPermission('Timezones','Delete') ){ ?>
                                                //action += ' <a href="'+delete_+'" title="Delete" class="btn delete btn-danger btn-default btn-sm"><i class="entypo-trash"></i></a>';
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
                            }

                        });
                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    /*$('body').on('click', '.btn.delete', function (e) {
                        e.preventDefault();

                        response = confirm('Are you sure?');

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
                    });*/

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
