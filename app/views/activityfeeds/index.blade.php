@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <div id="table_filter" method="get" action="#" >   
                <div class="form-group">
                    <label for="field-1" class="control-label">User</label>
                    {{Form::select('User', $users, '' ,array( "class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Action</label>
                    <select name="Actions" id="Actions" class="select2">
                        <option value="" selected>All</option>
                        <option value="View">View</option>
                        <option value="Add">Add</option>
                        <option value="Edit">Edit</option>
                        <option value="Delete">Delete</option>
                        <option value="Export">Export</option>
                        <option value="Search">Search</option>
                        <option value="Upload">Upload</option>
                        <option value="Send">Send</option>
                        <option value="Recall">Recall</option>
                        <option value="Bulk Edit">Bulk Edit</option>
                        <option value="Bulk Delete">Bulk Delete</option>
                    </select>
                </div>   
                <div class="form-group">
                    <label class="control-label">Date From</label>
                    <input type="text" data-date-format="yyyy-mm-dd" class="form-control datepicker" id="DateFrom" name="DateFrom">
                </div>
                <div class="form-group">
                    <label class="control-label">Date To</label>
                    <input type="text" data-date-format="yyyy-mm-dd" class="form-control datepicker" id="DateTo" name="DateTo">
                </div>
                <div class="form-group">
                    <label class="control-label">Search</label>
                    <input type="text" class="form-control" name="Search">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="filter_submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop


@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Activity Feed</strong>
        </li>
    </ol>
    <h3>Activity Feed</h3>

    @include('includes.errors')
    @include('includes.success')
    {{-- @if(User::checkCategoryPermission('BillingClass','Edit'))
    <p style="text-align: right;">
        <a  id="add-button" href="{{URL::to('billing_class/create')}}" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
    </p>
    @endif --}}

    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="25%">User</th>
            <th width="30%">Date</th>
            <th width="30%">Action</th>
            <th width="15%">Action Details</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        /**
         * JQuery Plugin for dataTable
         * */

        jQuery(document).ready(function ($) {

            var d = new Date();
            var firstDay = new Date(d.getFullYear(), d.getMonth(), 1);
            var lastDay = new Date(d.getFullYear(), d.getMonth() + 1, 0)
            
            $("#DateFrom").datepicker("setDate", firstDay);
            $("#DateTo").datepicker("setDate", lastDay);


            $('#filter-button-toggle').show();

            var list_fields  = ["Who","Action"];
            //public_vars.$body = $("body");
            var $search = {};
            var datagrid_url = baseurl + "/activity/ajax_datagrid";
            var export_url = baseurl + "/activity/exports";

            $("#filter_submit").click(function(e) {
                e.preventDefault();
                
                startDate = $('#DateFrom').val();
                endDate = $('#DateTo').val();
                if(startDate != '' || endDate != ''){
                    if(startDate == ''){
                        toastr.error("Date From Field Required", "Error", toastr_opts);
                        return false;
                    }
                    if(endDate == ''){
                        toastr.error("Date To Field Required", "Error", toastr_opts);
                        return false;
                    }
                    if(startDate == endDate){
                        toastr.error("Date To Field Always Greater Than Date From Field", "Error", toastr_opts);
                        return false;
                    }
                }

                $search.Action = $("#table_filter").find('[name="Actions"]').val();
                $search.User = $("#table_filter").find('[name="User"]').val();
                $search.DateFrom = $("#table_filter").find('[name="DateFrom"]').val();
                $search.DateTo = $("#table_filter").find('[name="DateTo"]').val();
                $search.Search = $("#table_filter").find('[name="Search"]').val();

                data_table = $("#table-list").dataTable({
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[1, 'desc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Actions", "value": $search.Action},
                                {"name": "User", "value": $search.User},
                                {"name": "DateFrom", "value": $search.DateFrom},
                                {"name": "DateTo", "value": $search.DateTo},
                                {"name": "Search", "value": $search.Search}

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "User", "value": $search.User},
                                {"name": "DateFrom", "value": $search.DateFrom},
                                {"name": "DateTo", "value": $search.DateTo},
                                {"name": "Search", "value": $search.Search},
                                {"name": "Export", "value": 1}
                        );

                    },
                    "aoColumns": [
                        {  "bSortable": true },  // 0 Name
                        {  "bSortable": true },  // 2 UpdatedBy
                        {  "bSortable": true, 
                        mRender: function (id, type, full) {
                            process = full[4];
                            if(process == '' || process == null){
                                process = "";
                            }else{
                                process =  '('+ full[4] +')';
                            }
                            action = full[2] + ' ' + process + ' ' + full[3] ; 
                            return action;
                        }
                        }, 
                        {
                            "bSortable": false,
                            mRender: function (id, type, full) {

                                var output = full[5];
                                    if(output != ''){
                                        action = '<button class="btn btn-default btn-sm" title="Details" data-placement="top" data-toggle="tooltip" onclick="getDetails('+full[6]+')" value="'+ full[6] +' "><i class="entypo-back-in-time"></i></button>'
                                    }else{
                                    action = '';
                                    }
                                return action;
                                }
                            },
                        // {  "bSortable": true },  // 2 updated_at
                        // {  "bSortable": false,
                        //     mRender: function ( id, type, full ) {
                        //         action = '<div class = "hiddenRowData" >';
                        //         for(var i = 0 ; i< list_fields.length; i++){
                        //             action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                        //         }
                        //         action += '</div>';
                        //         action += ' <a href="' + edit_url.replace("{id}",id) +'" title="Edit" class="edit-button btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                        //         @if(User::checkCategoryPermission('BillingClass','Delete'))
                        //         if(full[4]== 0) {
                        //             action += ' <a href="' + delete_url.replace("{id}", id) + '" title="Delete" class="delete-button btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                        //         }
                        //         @endif
                        //         return action;
                        //     }
                        // },  // 0 Created


                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "Export Data",
                                "sUrl": export_url,
                                sButtonClass: "save-collection"
                            }
                        ]
                    },
                    "fnDrawCallback": function() {
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }

                });
            });


            $('#filter_submit').trigger('click');
           


            $("#DateFrom").datepicker({
                todayBtn:  1,
                autoclose: true
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                var endDate = $('#DateTo');
                endDate.datepicker('setStartDate', minDate);
                if(endDate.val() && new Date(endDate.val()) != undefined) {
                    if(minDate > new Date(endDate.val()))
                        endDate.datepicker("setDate", minDate)
                }
            });

            $("#DateTo").datepicker({autoclose: true})
                .on('changeDate', function (selected) {
                    var maxDate = new Date(selected.date.valueOf());
                    //$('#StartDate').datepicker('setEndDate', maxDate);
            });

            if(new Date($('#DateFrom').val()) != undefined){
                $("#DateTo").datepicker('setStartDate', new Date($('#DateFrom').val()))
            }
            //inst.myMethod('I am a method');

          


            // $('table tbody').on('click', '.delete-button', function (ev) {
            //     ev.preventDefault();
            //     result = confirm("Are you Sure?");
            //     if(result){
            //         var delete_url  = $(this).attr("href");
            //         submit_ajax_datatable( delete_url,"",0,data_table);
            //     }
            //     return false;
            // });

            // $("#modal-form").submit(function(e){
            //     e.preventDefault();
            //     var _url  = $(this).attr("action");
            //     submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
            // });



        });
        function getDetails(id){
            $('#table-list2').empty();
          $.ajax({
              url : baseurl + "/activity/get_details/" + id,
              type: 'POST',
              dataType: 'json',
              success: function (response) {
                  if(response.status != 'failed'){
                    $.each(response, function(key, value) {
                        var str = key;
                        str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                            return letter.toUpperCase();
                        });
                        if(typeof value !='object'){
                            $('#table-list2').append('<tr><th>'+ str +'</th><td>'+ value +'</td></tr>');
                        }
                    })
                    $('#details-modal').modal('show');
                  }else{
                    $('#table-list2').append('<tr><th class="text-center">No More Details</th></tr>')
                    $('#details-modal').modal('show');
                  }
              
              }
          });   
        }
    </script>
@include('activityfeeds.details_modal')
@stop