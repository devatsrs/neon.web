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
                    {{ Form::text('searchText', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Account</label>
                    {{ Form::select('AccountID', $accounts, '' , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Deal Type</label>
                    <select class="select2" name="DealType">
                        <option value="Revenue">Revenue</option>
                        <option value="Payment">Payment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Start Date</label>
                    {{ Form::text('StartDate', !empty(Input::get('StartDate'))?Input::get('StartDate'):'', array("class"=>"form-control small-date-input datepicker", "data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}<!-- Time formate Updated by Abubakar -->
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">End Date</label>
                    {{ Form::text('EndDate', !empty(Input::get('EndDate'))?Input::get('EndDate'):'', array("class"=>"form-control small-date-input datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Status</label>
                    <select class="select2" name="Status">
                        <option value="Active">Active</option>
                        <option value="Pending">Pending</option>
                        <option value="Closed">Closed</option>
                    </select>
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
    <table class="table table-bordered datatable" id="table-4">
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
            <th width="10%">Notes</th>
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
        var $searchFilter 	= 	{};
        var checked			=	'';
        var update_new_url;
        var postdata;
        jQuery(document).ready(function ($) {

            $('#filter-button-toggle').show();

            var status 					        =	'{{$status_json}}';
            var temp_path						=	"{{ CompanyConfiguration::get('TEMP_PATH') }}";
            public_vars.$body 					= 	$("body");
            var base_url_theme 					= 	"{{ URL::to('dealmanagement')}}";
            var delete_url_bulk 				= 	"{{ URL::to('dealmanagement/deal_delete_bulk')}}";
            var Status_Url 				        = 	"{{ URL::to('dealmanagement/deal_change_Status')}}";
            // var list_fields  					= 	['AccountName','EstimateNumber','IssueDate','GrandTotal','EstimateStatus','EstimateID','Description','Attachment','AccountID','BillingEmail'];
            data_table = $("#table-4").dataTable({

                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }
            });

            $searchFilter.AccountID  = $("#deal_filter select[name='AccountID']").val();
            $searchFilter.DealType 	 = $("#deal_filter select[name='DealType']").val();
            $searchFilter.StartDate  = $("#deal_filter [name='StartDate']").val();
            $searchFilter.EndDate 	 = $("#deal_filter [name='EndDate']").val();
            $searchFilter.Status 	 = $("#deal_filter select[name='Status']").val() != null ? $("#deal_filter select[name='Status']").val() : '';

            $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');

            $("#deal_filter").submit(function(e){
                e.preventDefault();
                $searchFilter.searchText 		= 	$("#deal_filter [name='searchText']").val();
                $searchFilter.Status 		= 	$("#deal_filter select[name='Status']").val();
                data_table.fnFilter('', 0);
                return false;
            });




            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $("#selectall").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#table-4 tbody tr').each(function(i, el) {
                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
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
            $('#table-4 tbody').on('click', 'tr', function() {
                if (checked =='') {
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



            $('#delete_bulk').click(function(e) {

                e.preventDefault();
                var self = $(this);
                var text = self.text();

                var DealIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                    DealID = $(this).val();
                    if(typeof DealID != 'undefined' && DealID != null && DealID != 'null'){
                        DealIDs[i++] = DealID;
                    }
                });

                if(DealIDs.length<1)
                {
                    alert("Please select atleast one theme.");
                    return false;
                }
                console.log(DealIDs);

                if (!confirm('Are you sure to delete selected themes?')) {
                    return;
                }

                $.ajax({
                    url: delete_url_bulk,
                    type: 'POST',
                    dataType: 'json',
                    data:'del_ids='+DealIDs,
                    success: function(response) {
                        $(this).button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }


                });
                return false;
            });

            $('table tbody').on('click', '.changestatus', function (e) {
                e.preventDefault();
                var self = $(this);
                var text = self.text();
                if (!confirm('Are you sure you want to change the deal status to '+ text +'?')) {
                    return;
                }

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
                    data:'Status='+$(this).attr('data-status')+'&DealIDs='+$(this).attr('data-themeid')

                });
                return false;
            });


            $('.alert').click(function(e){
                e.preventDefault();
                var email = $('#TestMail-form').find('[name="EmailAddress"]').val();
                var accontID = $('.hiddenRowData').find('.rowcheckbox').val();
                if(email==''){
                    toastr.error('Email field should not empty.', "Error", toastr_opts);
                    $(".alert").button('reset');
                    return false;
                }else if(accontID==''){
                    toastr.error('Please select sample estimate', "Error", toastr_opts);
                    $(".alert").button('reset');
                    return false;
                }
                $('#BulkMail-form').find('[name="testEmail"]').val(email);
                $('#BulkMail-form').find('[name="SelectedIDs"]').val(accontID);
                $("#BulkMail-form").submit();
                $('#modal-TestMail').modal('hide');

            });

            jQuery(document).on( 'click', '.delete_link', function(event){
                event.preventDefault();
                var url_del = jQuery(this).attr('href');


                $.ajax({
                    url: url_del,
                    type: 'POST',
                    dataType: 'json',
                    data:{"del":1},
                    success: function(response_del) {
                        if (response_del.status == 'success')
                        {
                            jQuery(this).parent().parent().parent().hide('slow').remove();
                            data_table.fnFilter('', 0);
                        }
                        else
                        {
                            ShowToastr("error",response.message);
                        }

                    },
                });


            });
/////////////////

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