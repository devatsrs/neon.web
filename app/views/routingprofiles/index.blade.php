@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Routing Profiles</strong>
    </li>
</ol>
<h3>Routing Profiles</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">
    <!-- We need to add permission - AHTSHAM -->
    <a href="#" data-action="showAddModal" data-type="routingcategory" data-modal="add-new-modal-routingcategory" id="addnewroutpro" class="btn btn-primary addnewroutpro" >
        <i class="entypo-plus"></i>
        Add New
    </a>
    
    <a data-id="" href="assignrouting" title="Assign" class="btn-success btn btn-danger btn-sm">Assign</a>
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="30%">Name</th>
        <th width="25%">Description</th>
        <th width="25%">Status</th>
        <th width="25%">Action</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/routingprofiles/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "aoColumns":
            [
                {  "bSortable": true },  //0  Name', '', '', '
                {  "bSortable": true },  //0  Descs', '', '', '
                {  "bSortable": true },  //0  Descs', '', '', '
                {                       //3  ID
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                        action = '<div class = "hiddenRowData" >';
                        action += '<input type = "hidden"  name = "Name" value = "' + (full[0] != null ? full[0] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Description" value = "' + (full[1] != null ? full[1] : '') + '" / >';
                        action += '<input type = "hidden"  name = "RoutingPolicy" value = "' + (full[3] != null ? full[3] : '') + '" / ></div>';
                        
                        action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" title="Edit" class="edit-category btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        action += ' <a data-id="'+ id +'" title="Delete" class="delete-category btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
                        
                       action += ' <a data-id="" href="lcr" title="test routing" class="btn-success btn btn-danger btn-sm">Test</a>';
                        
                        
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/routingprofiles/exports/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/routingprofiles/exports/csv",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
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
                           showAjaxScript( baseurl + "/routingprofiles/"+id+"/delete" ,"",FnDeleteCurrencySuccess );
                       }
                       return false;
                   }
                   $(".delete-category").click(FnDeleteCurrency); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $('table tbody').on('click','#addnewroutpro',function(ev){
            console.log('---pp');
            $("#RoutingCategory option:selected").prop("selected", false);
            $("#RoutingCategory option:selected").removeAttr("selected");
            var rcategory = $('#RoutingCategory').bootstrapDualListbox();   
            rcategory.bootstrapDualListbox('refresh');
        })
    $('table tbody').on('click','.edit-category',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-routingcategory-form').trigger("reset");
        $.post(baseurl + "/routingprofiles/ajaxcall/"+$(this).attr('data-id'), '', function(response) {
            SaveCat = $.parseJSON(response);
            $.each(SaveCat, function(index, value) {
                var RoutingCategoryID = value.RoutingCategoryID;
                var NAME = value.NAME;
                $("#RoutingCategory option[value='" + RoutingCategoryID + "']").prop("selected", true);
            });

            setTimeout(function(){
               var rcategory = $('#RoutingCategory').bootstrapDualListbox();   
               rcategory.bootstrapDualListbox('refresh'); 
            }, 500);
            
        });
                        
        Name = $(this).prev("div.hiddenRowData").find("input[name='Name']").val();
        Description = $(this).prev("div.hiddenRowData").find("input[name='Description']").val();
        RoutingPolicy = $(this).prev("div.hiddenRowData").find("input[name='RoutingPolicy']").val();
        console.log(RoutingPolicy);
        $("#RoutingPolicy").val(RoutingPolicy);
        
        $("#add-new-routingcategory-form [name='Name']").val(Name);
        $("#add-new-routingcategory-form [name='Description']").val(Description);
        $("#add-new-routingcategory-form [name='RoutingProfileID']").val($(this).attr('data-id'));
        $('#add-new-modal-routingcategory h3').html('Edit Routing Profile');
        $('#add-new-modal-routingcategory').modal('show');
    })

    });

/*function ajax_update(fullurl,data){
//alert(data)
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#currency-update").button('reset');
            $(".btn").button('reset');
            $('#modal-Currency').modal('hide');

            if (response.status == 'success') {
                $('#add-new-modal-routingcategory').modal('hide');
                toastr.success(response.message, "Success", toastr_opts);
                if( typeof data_table !=  'undefined'){
                    data_table.fnFilter('', 0);
                }
            } else {
                toastr.error(response.message, "Error", toastr_opts);
            }
        },
        data: data,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false
    });
}*/

</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
</style>
@include('routingprofiles.routingmodel')
@stop

