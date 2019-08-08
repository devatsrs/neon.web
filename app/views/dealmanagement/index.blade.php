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
                    <label for="field-1" class="control-label">CodeDeck</label>
                    {{ Form::select('CodeDeckId', $codedecklist, $DefaultCodedeck , array("class"=>"select2")) }}
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
            <div class="input-group-btn pull-right" style="width:70px;">
                @if( User::checkCategoryPermission('DealManagement','Edit'))
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                        <li> <a class="delete_bulk" id="delete_bulk" href="javascript:;" > Delete </a> </li>
                    </ul>
                @endif
                <form id="clear-bulk-rate-form" >
                    <input type="hidden" name="DealIDs" value="">
                </form>
            </div>

            @if(User::checkCategoryPermission('DealManagement','Add'))
                <a href="{{URL::to("dealmanagement/create")}}" id="add-new-deal" class="btn btn-primary pull-right"> <i class="entypo-plus"></i> Add New</a>
                @endif
                        <!-- /btn-group -->
        </div>
        <div class="clear"></div>
    </div>
    <br>
    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th width="5%"><div class="pull-left">
                    <input type="checkbox" id="selectall" name="checkbox[]" class="" />
                </div></th>
            <th width="20%">Title</th>
            <th width="15%">Account</th>
            <th width="10%">Codedeck</th>
            <th width="15%">Start Date</th>
            <th width="10%">End Date</th>
            <th width="10%">Notes</th>
            <th width="10%">Alert Email</th>
            <th width="10%">Deal Type</th>
            <th width="10%">Status</th>
            <th width="20%">Action</th>
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

            var status 					        =	'{{$themes_status_json}}';
            var temp_path						=	"{{ CompanyConfiguration::get('TEMP_PATH') }}";
            public_vars.$body 					= 	$("body");
            var base_url_theme 					= 	"{{ URL::to('dealmanagement')}}";
            var delete_url_bulk 				= 	"{{ URL::to('dealmanagement/deal_delete_bulk')}}";
            var Status_Url 				        = 	"{{ URL::to('dealmanagement/deal_change_Status')}}";
            // var list_fields  					= 	['AccountName','EstimateNumber','IssueDate','GrandTotal','EstimateStatus','EstimateID','Description','Attachment','AccountID','BillingEmail'];

            $searchFilter.AccountID 			= 	$("#deal_filter select[name='AccountID']").val();
            $searchFilter.EstimateStatus 		= 	$("#deal_filter select[name='EstimateStatus']").val();
            $searchFilter.EstimateNumber 		= 	$("#deal_filter [name='EstimateNumber']").val();
            $searchFilter.IssueDateStart 		= 	$("#deal_filter [name='IssueDateStart']").val();
            $searchFilter.IssueDateEnd 			= 	$("#deal_filter [name='IssueDateEnd']").val();
            $searchFilter.CurrencyID            =   $("#deal_filter [name='CurrencyID']").val();

            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/dealmanagement/ajax_datagrid",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[1, 'desc']],
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"searchText","value":$searchFilter.searchText},{"name":"Status","value":$searchFilter.Status});
                },
                "aoColumns":
                        [
                            {  "bSortable": false,
                                mRender: function ( id, type, full ) {
                                    var action , action = '<div class = "hiddenRowData" >';
                                    //if (full[4] != 'accepted')
                                    {
                                        action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="'+id+'" name="EstimateID[]"></div>';
                                    }
                                    action += '</div>';
                                    return action;
                                }

                            },  // 0 checbox
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    return id;
                                }

                            },  // 0 checbox
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    return id;
                                }

                            },  // 0 checbox
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    return id;
                                }

                            },  // 0 checbox
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    return id;
                                }

                            },  // 0 checbox
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    return id;
                                }

                            },  // 0 checbox
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    return id;
                                }

                            },  // 0 checbox
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    return id;
                                }

                            },  // 0 checbox
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    return id;
                                }

                            },  // 1 server regex
                            {  "bSortable": true,

                                mRender:function( id, type, full){
                                    return id;
                                }

                            },  // 5 EstimateStatus
                            {
                                "bSortable": false,
                                mRender: function ( id, type, full ) {
                                    var action , edit_ , show_ , delete_,view_url,edit_url,download_url,delete_url;

                                    action 				= 	'<div class = "hiddenRowData" >';
                                    edit_url 			= 	(baseurl + "/dealmanagement/{id}/edit").replace("{id}",full[0]);
                                    delete_url 			= 	(baseurl + "/dealmanagement/{id}/delete").replace("{id}",full[0]);


                                    for(var i = 0 ; i< list_fields.length; i++)
                                    {
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }

                                    action += '</div>';

                                    /*Multiple Dropdown*/
                                    action += '<div class="btn-group">';
                                    action += ' <a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">Action<span class="caret"></span></a>';
                                    action += '<ul class="dropdown-menu multi-level dropdown-menu-left" role="menu" aria-labelledby="dropdownMenu">';

                                    if('{{User::checkCategoryPermission('DealManagement','Edit')}}')
                                    {
                                        //if(full[4] != 'accepted')
                                        {
                                            action += ' <li><a class="icon-left"  href="' +edit_url+'"><i class="entypo-pencil"></i>Edit </a></li>';
                                        }
                                    }


                                    if ('{{User::checkCategoryPermission('DealManagement','Edit')}}' && delete_url)
                                    {
                                        action += '<li><a class="icon-left delete_link"  target="_blank" href="' + delete_url +'"><i class="entypo-trash"></i>Delete</li>';
                                    }


                                    action += '</ul>';
                                    action += '</div>';

                                    //if(full[4] != 'accepted')
                                    {
                                        //action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Change Status <span class="caret"></span></button>'
                                        action += '<ul class="dropdown-menu dropdown-green" role="menu">';
                                        $.each(status, function( index, value ) {

                                            action +='<li><a data-status="' + index+ '" data-id="' + full[0]+ '" href="' + Status_Url+ '" class="changestatus" >'+value+'</a></li>';


                                        });

                                        action += '</ul>' +
                                                '</div>';
                                    }

                                    return action;
                                }
                            },
                        ],
                "oTableTools": {
                    "aButtons": []
                },
                "fnDrawCallback": function() {
                    $('#table-4 tbody tr').each(function(i, el) {
                        if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
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
                    //After Delete done
                    FnDeleteEstimateTemplateSuccess = function(response){

                        if (response.status == 'success') {
                            $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                            ShowToastr("success",response.message);
                            data_table.fnFilter('', 0);
                        }else{
                            ShowToastr("error",response.message);
                        }
                    }
                    //onDelete Click
                    FnDeleteEstimateTemplate = function(e){
                        result = confirm("Are you Sure?");
                        if(result){
                            var id  = $(this).attr("data-id");
                            showAjaxScript( baseurl + "/dealmanagement/"+id+"/delete" ,"",FnDeleteEstimateTemplateSuccess );
                        }
                        return false;
                    }
                    $(".delete-estimate").click(FnDeleteEstimateTemplate); // Delete Note
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $('#selectallbutton').click(function(ev) {
                        if($(this).is(':checked')){
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            if(!$('#changeSelectedEstimate').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    }
                                });
                            }
                        }else{
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            if(!$('#changeSelectedEstimate').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    }
                                });
                            }
                        }
                    });
                }

            });

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