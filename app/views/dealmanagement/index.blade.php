@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="deal_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Search</label>
                    {{ Form::text('Search', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Account</label>
                    {{ Form::select('AccountID', $accounts, '' , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Deal Type</label>
                    {{Form::select('DealType',$dealTypes, '',array("class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Start Date</label>
                    {{ Form::text('StartDate', '', array("class"=>"form-control small-date-input datepicker", "data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}<!-- Time formate Updated by Abubakar -->
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">End Date</label>
                    {{ Form::text('EndDate', '', array("class"=>"form-control small-date-input datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Status</label>
                    {{Form::select('Status',Deal::$StatusDropDown, 'Active',array("class"=>"select2"))}}
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
        <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
        <li class="active"> <strong>Deal Management</strong> </li>
    </ol>
    <h3>Deal Management</h3>
    @include('includes.errors')
    @include('includes.success')

    <div class="row">
        <div  class="col-md-12">
            @if(User::checkCategoryPermission('DealManagement','Add'))
                <a href="{{URL::to("dealmanagement/create")}}" id="add-new-deal" class="btn btn-primary pull-right"> <i class="entypo-plus"></i> Add New</a>
            @endif
        </div>
        <div class="clear"></div>
    </div>
    <br>
    <table class="table table-bordered" id="table-deal">
        <thead>
        <tr>
            <th width="5%">
                <div class="pull-left">
                    <input type="checkbox" id="selectall" name="checkbox[]" class="" />
                </div>
            </th>
            <th width="10%">Title</th>
            <th width="10%">Account</th>
            <th width="10%">Codedeck</th>
            <th width="10%">Start Date</th>
            <th width="10%">End Date</th>
            <th width="10%">Alert Email</th>
            <th width="10%">Deal Type</th>
            <th width="7%">Status</th>
            <th width="15%">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        var list_fields_activity  = ['Search','AccountID','DealType','StartDate','EndDate','Status'];
        var update_new_url;
        var postdata;
        jQuery(document).ready(function ($) {
            public_vars.$body = $("body");
            var $search = {};
            $search.Search      = $("#deal_filter").find('[name="Search"]').val();
            $search.AccountID   = $("#deal_filter").find('[name="AccountID"]').val();
            $search.DealType    = $("#deal_filter").find('[name="DealType"]').val();
            $search.StartDate   = $("#deal_filter").find('[name="StartDate"]').val();
            $search.EndDate     = $("#deal_filter").find('[name="EndDate"]').val();
            $search.Status      = $("#deal_filter").find('[name="Status"]').val();
            data_table_deal = $("#table-deal").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/dealmanagement/ajax_datagrid",
                "fnServerParams": function (aoData) {
                    aoData.push(
                            {"name": "Title", "value": $search.Title},
                            {"name": "Search", "value": $search.Search},
                            {"name": "AccountID", "value": $search.AccountID},
                            {"name": "DealType", "value": $search.DealType},
                            {"name": "StartDate", "value": $search.StartDate},
                            {"name": "EndDate", "value": $search.EndDate},
                            {"name": "Status", "value": $search.Status},
                            {"name": "Export", "value": 0}
                    );

                    data_table_extra_params.length = 0;
                    data_table_extra_params.push(
                            {"name": "Title", "value": $search.Title},
                            {"name": "Search", "value": $search.Search},
                            {"name": "AccountID", "value": $search.AccountID},
                            {"name": "DealType", "value": $search.DealType},
                            {"name": "StartDate", "value": $search.StartDate},
                            {"name": "EndDate", "value": $search.EndDate},
                            {"name": "Status", "value": $search.Status},
                            {"name": "Export", "value": 1});

                },
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'r>",
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {
                        "bSortable": false, //Account
                        mRender: function (id, type, full) {
                            var chackbox = '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + full[0] + '" class="rowcheckbox" ></div>';
                            if($('#Recall_on_off').prop("checked")){
                                chackbox='';
                            }
                            return chackbox;
                        }
                    },
                    {"bSortable": true},  // 1 Title
                    {"bSortable": true},  // 2 Account
                    {"bSortable": true},  // 3 Codedeck
                    {"bSortable": true},  // 4 Start Date
                    {"bSortable": true},  // 5 End Date
                    {"bSortable": true},  // 6 Alter Email
                    {"bSortable": true},  // 7 Deal Type
                    {"bSortable": true},  // 8 Status
                    {                       //  9  Action
                        "bSortable": false,
                        mRender: function (id, type, full) {

                            var edit_ = "{{ URL::to('/dealmanagement/{id}/edit/')}}";
                            var delete_ = "{{ URL::to('/dealmanagement/{id}/delete/')}}";
                            edit_ = edit_.replace('{id}', full[0]);
                            delete_ = delete_.replace('{id}', full[0]);

                            action = '<div class = "hiddenRowData" >';
                            for (var i = 0; i < list_fields_activity.length; i++) {
                                action += '<input type = "hidden"  name = "' + list_fields_activity[i] + '"       value = "' + (full[i] != null ? full[i] : '') + '" / >';
                            }
                            action += '</div>';
                            action += ' <a href="' + edit_ + '" data-redirect="{{ URL::to('dealmanagement')}}" title="Delete" class="btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                            action += ' <a href="' + delete_ + '" data-redirect="{{ URL::to('dealmanagement')}}" title="Delete" class="btn delete btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                            return action;
                        }
                    }
                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "Export Data",
                            "sUrl": baseurl + "/dealmanagement/ajax_datagrid", //baseurl + "/generate_xls.php",
                            sButtonClass: "save-collection"
                        }
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });

                    $("#table-service tbody input[type=checkbox]").each(function (i, el) {
                        var $this = $(el),
                                $p = $this.closest('tr');

                        $(el).on('change', function () {
                            var is_checked = $this.is(':checked');

                            $p[is_checked ? 'addClass' : 'removeClass']('selected');
                        });
                    });


                    $('#selectall').removeClass('hidden');

                    //select all record
                    $('#selectallbutton').click(function(){
                        if($('#selectallbutton').is(':checked')){
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            $('#table-service tbody tr').each(function (i, el) {
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                            });
                        }else{
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            $('#table-service tbody tr').each(function (i, el) {
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                $(this).removeClass('selected');
                            });
                        }
                    });
                }

            });
            $("#deal_filter").submit(function(e) {
                e.preventDefault();
                $search.Search      = $("#deal_filter").find('[name="Search"]').val();
                $search.AccountID   = $("#deal_filter").find('[name="AccountID"]').val();
                $search.DealType    = $("#deal_filter").find('[name="DealType"]').val();
                $search.StartDate   = $("#deal_filter").find('[name="StartDate"]').val();
                $search.EndDate     = $("#deal_filter").find('[name="EndDate"]').val();
                $search.Status      = $("#deal_filter").find('[name="Status"]').val();
                data_table_activity.fnFilter('', 0);
            });

            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

        });

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
                            data_table_activity.fnFilter('', 0);
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
        #table-4 .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data{
            right: 30px !important;
        }
        #table-5_filter label{
            display:block !important;
        }
        #selectcheckbox{
            padding: 15px 10px;
        }
    </style>
    @stop
    @section('footer_ext')
    @parent
            <!-- Job Modal  (Ajax Modal)-->
@stop