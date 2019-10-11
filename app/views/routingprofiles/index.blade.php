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
                    <label class="control-label">Status</label><br/>
                    <p class="make-switch switch-small">
                        <input name="Status" type="checkbox" value="" checked="checked">
                    </p>
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
        <strong>Routing Profiles</strong>
    </li>
</ol>
<h3>Routing Profiles</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">
    <!-- We need to add permission - AHTSHAM -->
    <a href="#" data-action="showAddModal" data-type="routingcategory" data-modal="add-new-modal-routingcategory" id="addnewroutpro" class="btn btn-primary addnewroutpro" >
        <i class="entypo-plus addnewroutpro"></i>
        Add New
    </a>
    <a href="assignrouting"  style="background:#00a651;border-color:#00a651" id="addnewroutpro" class="btn btn-primary " >
        <i class="entypo-plus addnewroutpro"></i>
        Assign
    </a>
</p>

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="20%">Name</th>
        <th width="20%">Description</th>
        <th width="20%">Selection Code</th>
        <th width="5%">Status</th>
        <th width="20%">Routing Category</th>
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
        
        $('#filter-button-toggle').show();
        
        var $search = {};
        var add_url = baseurl + "/routingprofiles/store";
        var edit_url = baseurl + "/routingprofiles/update/{id}";
        var view_url = baseurl + "/routingprofiles/show/{id}";
        var delete_url = baseurl + "/routingprofiles/delete/{id}";
        var datagrid_url = baseurl + "/routingprofiles/ajax_datagrid";
        
         $("#filter_submit").click(function(e) {
            e.preventDefault();

            $search.Name = $("#table_filter").find('[name="Name"]').val();
            $search.Status = $("#table_filter").find('[name="Status"]').prop("checked");

        // routing categories table...

            
        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/routingprofiles/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "Status", "value": $search.Status},
                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "Status", "value": $search.Status},
                                {"name": "Export", "value": 1}
                        );
                    },
            "aoColumns":
            [
                {  "bSortable": true },  //0  Name', '', '', '
                {  "bSortable": true },  //0  Name', '', '', '
                {  "bSortable": true },  //0  Descs', '', '', '
                {  "bSortable": true,
                    mRender: function ( id, type, full ) {
                         var action , edit_ , show_ , delete_;
                         //console.log(id);
                         if(id==1){
                           action='<i class="entypo-check" style="font-size:22px;color:green"></i>';  
                         }else{
                             action='<i class="entypo-cancel" style="font-size:22px;color:red"></i>';
                         }                         
                       return action; 
                    } 
                },  //0  Status', '', '', '
               
                {  "bSortable": true,
                    mRender: function ( id, type, full ) {
                         var action , edit_ , show_ , delete_;
                           
                       return full[5]; 
                      
                    } 
                },  //0  Status', '', '', '
                
                {                       //3  ID
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                        action = '<div class = "hiddenRowData" >';
                        action += '<input type = "hidden"  name = "Name" value = "' + (full[0] != null ? full[0] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Description" value = "' + (full[1] != null ? full[1] : '') + '" / >';
                        action += '<input type = "hidden"  name = "RoutingPolicy" value = "' + (full[5] != null ? full[5] : '') + '" / >'
                        action += '<input type = "hidden"  name = "SelectionCode" value = "' + (full[2] != null ? full[2] : '') + '" / >'
                        action += '<input type = "hidden"  name = "Status" value = "' + (full[3] != null ? full[3] : '') + '" / ></div>';
                        action += ' <a data-name = "'+full[0]+'" data-id="'+ full[4] +'" title="Edit" class="edit-category btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        action += ' <a data-id="'+ full[4] +'" title="Delete" class="delete-category btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';                        
                        action += '';
                    
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
                        //console.log(response);
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
 });
 $('#addnewroutpro').click(function(){
     $('.tbody').html("");
 })

$('#filter_submit').trigger('click');

// Replace Checboxes
$(".pagination a").click(function (ev) {
    replaceCheckboxes();
});

$('#addnewroutpro').click(function () {  
     
    $('#add-new-modal-routingcategory h3').html('Add Routing Profile');
    $("#RoutingCategory option:selected").prop("selected", false);
    $("#RoutingCategory option:selected").removeAttr("selected");
    var rcategory = $('#RoutingCategory').bootstrapDualListbox();
    $('.dataTables_processing').css("visibility","visible");   
    rcategory.bootstrapDualListbox('refresh');
    $('#RoutingCategories').html("<option></option>");
    $.ajax({
        url : 'routingprofiles/ajaxCategories' ,
        type: 'get',
        success:function(response){
            $.map( response, function( val, i ) {
                $('#RoutingCategories').append("<option value='"+ val.RoutingCategoryID +"'>"+val.Name+"</option>");
                $('.dataTables_processing').css("visibility","hidden");                  
            });  
        }
    });
 });
$('table tbody').on('click','.addnewroutpro',function(ev){
    $("#RoutingCategory option:selected").prop("selected", false);
    $("#RoutingCategory option:selected").removeAttr("selected");
    var rcategory = $('#RoutingCategory').bootstrapDualListbox();   
    rcategory.bootstrapDualListbox('refresh');
});
    $('table tbody').on('click','.edit-category',function(ev){
        var data = $(this).data('id');
        $('.tbody').html("");    
        $('.dataTables_processing').css("visibility","visible");
        $('#RoutingCategories').empty();
        $.ajax({
            url : 'routingprofiles/ajaxCategories' ,
            type: 'get',
            success:function(response){
                $.map( response, function( val, i ) {
                    $('#RoutingCategories').append("<option value='"+ val.RoutingCategoryID +"'>"+val.Name+"</option>");                  
            });  
            }
        })
        setTimeout(function(){
        $.ajax({
            url: 'routingprofiles/ajaxedit',
            type:'post',
            data:{data:data},
            success:function(response){
                $('.dataTables_processing').css("visibility","hidden");
                
                $.map( response, function( val, i ) {
                    Array.prototype.forEach.call(val, function(el) {
                    // Do stuff here
                    $('.tbody').append("<tr data-id="+el.RoutingCategoryID + "><td><input type='number' min='0' value='"+ el.Order +"' name='Orders[]' class='form-control' /><input type='hidden' name='RoutingCategory[]' value='"+ el.RoutingCategoryID +"'/></td><td>"+ el.Name +"</td><td>"+ el.Description +"</td><td><a class='btn btn-danger btn-sm' id='"+ el.RoutingCategoryID +"' onclick='deleteRoute(this.id)'><i class='entypo-trash'></i></a></td></tr>");
                        
                 }); 
                    $('.tbody tr').each(function() {                                   
                        var id = $(this).data('id');
                        $('#RoutingCategories option[value='+id+']').remove();                                                        
                        });
                        $('#RoutingCategories').val('').trigger('change');                          
                    });                          
            }           
        });
        }, 1000
    );
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
        SelectionCode = $(this).prev("div.hiddenRowData").find("input[name='SelectionCode']").val();
        Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
        console.log(Status);

        RoutingPolicy = $(this).prev("div.hiddenRowData").find("input[name='RoutingPolicy']").val();
        //console.log(RoutingPolicy);
        $("#RoutingPolicy").val(RoutingPolicy);
        $("#RoutingPolicy").val(RoutingPolicy).trigger("chosen:updated");
        
        $("#add-new-routingcategory-form [id='RoutingPolicy']").select2().select2('val', RoutingPolicy);
        
        $("#add-new-routingcategory-form [name='Name']").val(Name);
        $("#add-new-routingcategory-form [name='Description']").val(Description);
        $("#add-new-routingcategory-form [name='SelectionCode']").val(SelectionCode);
        $("#add-new-routingcategory-form [name='Status']").prop('checked', Status == 1);
        $("#add-new-routingcategory-form [name='RoutingProfileID']").val($(this).attr('data-id'));
        $('#add-new-modal-routingcategory h3').html('Edit Routing Profile');
        $('#add-new-modal-routingcategory').modal('show');

        });  
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

