@extends('layout.main')

@section('content')
<style>
    .tables_ui {
  display:inline-block;
  margin:2px 2%;
  border-spacing:0;
}
.tables_ui ul li {
  min-width: 200px;
}
.dragging li.ui-state-hover {
  min-width: 240px;
}
.dragging .ui-state-hover a {
  color:green !important;
  font-weight: bold;
}
.tables_ui th,td {
  text-align: right;
  padding: 2px 4px;
  border: 1px solid;
}
.t_sortable tr, .ui-sortable-helper {
  cursor: move;
}
.t_sortable tr:first-child {
  cursor: default;
}
.ui-sortable-placeholder {
  background: yellow;
}
</style>
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Routing Profile category </strong>
    </li>
</ol>

@include('includes.errors')
@include('includes.success')


<div class="modal-body">
                       

                        <div class="panel panel-primary" data-collapsed="0" >
                            <div class="panel-heading">
                                <div class="panel-title" >
                                    <b>Manage Routing Profiles Category</b>
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>
                            <div class="panel-body" style="display: block;">
                            <table class="tables_ui" id="t_draggable1">
                                <caption><h4>Categories</h4></caption>
<tbody class="t_sortable">
  <tr>
      <th ><input type="checkbox"></th>
      <th colspan="4" style="text-align:left"><b>Category</b></th>  
  </tr>
  <tr>
        <td><input type="checkbox"></td>                                                                                         
        <td>Routing Profile Category 1</td>                                                               
  </tr>
  <tr>
        <td><input type="checkbox"></td>                                                                                         
        <td>Routing Profile Category 2</td>                                                               
  </tr>
  <tr>
        <td><input type="checkbox"></td>                                                                                         
        <td>Routing Profile Category 3</td>                                                               
  </tr>
</tbody></table>
                            
<table class="tables_ui" id="t_draggable2"><caption><h4>Select Categories</h4></caption>
<tbody class="t_sortable">
   <tr>
      <th ><input type="checkbox"></th>
      <th colspan="4" style="text-align:left"><b>Category</b></th>  
  </tr>
  <tr>
        <td><input type="checkbox"></td>                                                                                         
        <td>Routing Profile Category 1</td>                                                               
  </tr>
  <tr>
        <td><input type="checkbox"></td>                                                                                         
        <td>Routing Profile Category 2</td>                                                               
  </tr>
  <tr>
        <td><input type="checkbox"></td>                                                                                         
        <td>Routing Profile Category 3</td>                                                               
  </tr>
</tbody></table>
                            </div>
                            
                        </div>
                    </div>





<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);



        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('table tbody').on('click','.edit-category',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-routingcategory-form').trigger("reset");

        catName = $(this).prev("div.hiddenRowData").find("input[name='catName']").val();
        Description = $(this).prev("div.hiddenRowData").find("input[name='Description']").val();

        $("#add-new-routingcategory-form [name='catName']").val(catName);
        $("#add-new-routingcategory-form [name='Description']").val(Description);
        $("#add-new-routingcategory-form [name='RoutingCategoryID']").val($(this).attr('data-id'));
        $('#add-new-modal-routingcategory h4').html('Edit Routing Category');
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

