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
                    <label for="field-1" class="control-label">Name</label>
                    <input type="text" name="Name" class="form-control" value="" />
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
        <strong>Routing Category</strong>
    </li>
</ol>
<h3>Routing Category</h3>

@include('includes.errors')
@include('includes.success')
<!--
<script type="text/javascript" src="https://mpryvkin.github.io/jquery-datatables-row-reordering/1.2.3/jquery.dataTables.rowReordering.js"></script>-->

<p style="text-align: right;">
    <!-- We need to add permission - AHTSHAM -->
    <a href="#" data-action="showAddModal" data-type="routingcategory" data-modal="add-new-modal-routingcategory" class="btn btn-primary">
        <i class="entypo-plus"></i>
        Add New
    </a>

</p>
<form id="RoutCategoryDataFrom" method="POST" />

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="30%">Name</th>
        <th width="25%">Description</th>
        <th width="25%">Action</th>
    </tr>
    </thead>
    <tbody id="sortable">


    </tbody>
</table>
<input type="hidden" name="main_fields_sort" id="main_fields_sort" value="">
</form>
<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);
        $('#filter-button-toggle').show();
        
        var list_fields  = ["Name","CodeDeckName","CreatedBy","created_at","DestinationGroupSetID","CodedeckID","CompanyID"];
            //public_vars.$body = $("body");
        var $search = {};
        var add_url = baseurl + "/routingcategory/store";
        var edit_url = baseurl + "/routingcategory/update/{id}";
        var view_url = baseurl + "/routingcategory/show/{id}";
        var delete_url = baseurl + "/routingcategory/delete/{id}";
        var datagrid_url = baseurl + "/routingcategory/ajax_datagrid";
        
        $("#filter_submit").click(function(e) {
            e.preventDefault();

            $search.Name = $("#table_filter").find('[name="Name"]').val();
            
            data_table = $("#table-4").dataTable({
                "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                    $(nRow).attr('data-id', aData[2]);
                },
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/routingcategory/ajax_datagrid",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'asc']],
                "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Name", "value": $search.Name},

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "Export", "value": 1}
                        );

                    },
                "aoColumns":
                [
                    {  "bSortable": true },  //0  Name', '', '', '
                    {  "bSortable": true },  //0  Descs', '', '', '
                    {                       //3  ID
                       "bSortable": true,
                        mRender: function ( id, type, full ) {
                            var action , edit_ , show_ , delete_;
                            action = '<div class = "hiddenRowData" >';
                            action += '<input type = "hidden"  name = "Name" value = "' + (full[0] != null ? full[0] : '') + '" / >';
                            action += '<input type = "hidden"  name = "Description" value = "' + (full[1] != null ? full[1] : '') + '" / ></div>';
                            action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" title="Edit" class="edit-category btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                            action += ' <a data-id="'+ id +'" title="Delete" class="delete-category btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';

                            return action;
                          }
                      },
                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/routingcategory/exports/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/routingcategory/exports/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
               "fnDrawCallback": function() {
                    initSortable();
                       //After Delete done
                       FnDeleteCurrencySuccess = function(response){
                            console.log(response);
                           if (response.status == 'success') {
                               $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                               ShowToastr("success",response.message);
                               data_table.fnFilter('', 0);
                           }else{
                               ShowToastr("error",response.message);
                           }
                       }
                       //onDelete Click
                       FnDeleteCurrency = function(e){
                           result = confirm("Are you Sure?");
                           if(result){
                               var id  = $(this).attr("data-id");
                               showAjaxScript( baseurl + "/routingcategory/"+id+"/delete" ,"",FnDeleteCurrencySuccess );
                           }
                           return false;
                       }
                       $(".delete-category").click(FnDeleteCurrency); // Delete Note
                       $(".dataTables_wrapper select").select2({
                           minimumResultsForSearch: -1
                       });
               }

            });
        });
            
        $('#filter_submit').trigger('click');

        ///data_table.rowReordering();

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('table tbody').on('click','.edit-category',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-routingcategory-form').trigger("reset");

        Name = $(this).prev("div.hiddenRowData").find("input[name='Name']").val();
        Description = $(this).prev("div.hiddenRowData").find("input[name='Description']").val();

        $("#add-new-routingcategory-form [name='Name']").val(Name );
        $("#add-new-routingcategory-form [name='Description']").val(Description);
        $("#add-new-routingcategory-form [name='RoutingCategoryID']").val($(this).attr('data-id'));
        $('#add-new-modal-routingcategory h3').html('Edit Routing Category');
        $('#add-new-modal-routingcategory').modal('show');
    })

    });

        function initSortable(){
            // Code using $ as usual goes here.
            $('#sortable').sortable({
                connectWith: '#sortable',
                placeholder: 'placeholder',
                start: function() {
                    //setting current draggable item
                    currentDrageable = $('#sortable');
                },
                stop: function(ev,ui) {
                   // saveOrder();
                    //de-setting draggable item after submit order.
                    currentDrageable = '';
                }
            });
        }
    function saveOrder() {
        var Ticketfields_array   = 	new Array();
        $('#sortable tr').each(function(index, element) {
            var TicketfieldsSortArray  =  {};
            TicketfieldsSortArray["data_id"] = $(element).attr('data-id');
            TicketfieldsSortArray["Order"] = index+1;

            Ticketfields_array.push(TicketfieldsSortArray);
        });
        var data_sort_fields =  JSON.stringify(Ticketfields_array);
        $('#main_fields_sort').val(data_sort_fields);
        $('#RoutCategoryDataFrom').submit();
    }

        $('#RoutCategoryDataFrom').submit(function(e){
            e.stopPropagation();
            e.preventDefault();

            var formData = new FormData($(this)[0]);
            var url		 = baseurl + '/routingcategory/update_fields_sorting';

            $.ajax({
                url: url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            return false;
        });    
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
</style>
@include('routingcategory.routingmodel')
@stop

