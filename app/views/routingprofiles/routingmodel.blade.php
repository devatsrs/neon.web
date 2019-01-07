<?php
 if(empty($PageRefresh)){
     $PageRefresh='';
 }
?>
<link rel="stylesheet" type="text/css" href="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/bootstrap-duallistbox.css'; ?>">
<script src="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/jquery.bootstrap-duallistbox.js'; ?>" ></script>

        
<style>
    .modal-ku {
  width: 750px;
  margin: auto;
}
.display{
    display:none;
}
</style>
<script>
    $(document).ready(function ($) {
        $('#add-new-routingcategory-form').submit(function(e){
            e.preventDefault();
            
            var PageRefresh = '{{ $PageRefresh }}';
            var RoutingCategoryID = $("#add-new-routingcategory-form [name='RoutingProfileID']").val();
            //console.log(RoutingCategoryID);
            if( RoutingCategoryID != ''){
                update_new_url = baseurl + '/routingprofiles/update/'+RoutingCategoryID;
            }else{
                update_new_url = baseurl + '/routingprofiles/create';
            }
            setTimeout(function(){
                showAjaxScript(update_new_url, new FormData(($('#add-new-routingcategory-form')[0])), function(response){
                    //console.log(response);
                    $(".btn").button('reset');
                    if (response.status == 'success') {
                        $('#add-new-modal-routingcategory').modal('hide');
                        $('#RoutingCategories').html("<option><option>");
                        $.ajax({
                            url : 'routingprofiles/ajaxCategories' ,
                            type: 'get',
                            success:function(response){
                                $.map( response, function( val, i ) {
                                    $('#RoutingCategories').append("<option value='"+ val.RoutingCategoryID +"'>"+val.Name+"</option>");                  
                            });  
                            }
                        });
                        data_table.fnFilter('', 0);

                        toastr.success(response.message, "Success", toastr_opts);
                        $('.tbody').html("");
                        $('select[data-type="routingcategory"]').each(function(key,el){
                            if($(el).attr('data-active') == 1) {
                                var newState = new Option(response.newcreated.Code, response.newcreated.RoutingCategoryID, true, true);
                            }else{
                                var newState = new Option(response.newcreated.Code, response.newcreated.RoutingCategoryID, false, false);
                            }
                            $(el).append(newState).trigger('change');
                            $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                                return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                            }));
                        });
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                });
            }, 100);
        });

        return false;
    });

</script>
@section('footer_ext')
    @parent  
    <div class="modal fade" id="add-new-modal-routingcategory">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <form id="add-new-routingcategory-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title">Add New Routing Profile</h3>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name</label>
                                    <input type="text"  name="Name" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Description</label>
                                    <textarea name="Description" class="form-control" id="field-6" placeholder=""></textarea>
                                    <input type="hidden" name="RoutingProfileID" >
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Selection Code</label>
                                    <textarea type="text"  name="SelectionCode" class="form-control" id="field-7"></textarea>
                                </div>
                            </div>        
                                                
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Routing Policy</label>
                                    
                                    <?php $nameprefix_array = array("" => "","LCR Policy" => "LCR Policy", "LCR + Prefix Policy" => "LCR + Prefix Policy"); ?>
                                    {{Form::select('RoutingPolicy', $nameprefix_array, Input::old('RoutingPolicy'),array("id"=>"RoutingPolicy","class"=>"select2 small"))}}
                                    
                                </div>
                            </div>                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Status</label>
                                    <p class="make-switch switch-small">
                                        {{Form::checkbox('Status', '1', true, [])}}
                                    </p>
                                </div>
                            </div>
                                                  
                         <div class="col-md-12">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#lefttab1" data-toggle="tab">Category</a></li>
<!--                            <li><a href="#lefttab2" id="leftgroup" data-toggle="tab">Connection</a></li>-->
                        </ul>
                             <br />
                        <div class="tab-content">
                            <div class="tab-pane active" id="lefttab1">
                                <div class="form-group">
                                    <div class="scroll">
                                        <div class="col-md-12" id="routingcategory_box">
                                            <select id="RoutingCategories" name="" class="select2 small select2-offscreen form-control" tabindex="-1" style="visibility: visible;" >
                                                    <option id="opt3" value=""></option>
                                                    <?php 
                                                    foreach($RoutingCategory as $key_cat => $cat_data){ 
                                                        ?>
                                                        <option value="<?php echo $key_cat;?>">
                                                            <?php echo $cat_data;?>
                                                        </option>
                                                        <?php
                                                    } ?>
                                            </select>
                                            <br>
                                            <br>
<!--                                        {{Form::select('RoutingCategory[]',$RoutingCategory,array(),array("id"=>"RoutingCategory","class"=>"","multiple"=>"multiple"))}}-->
                                           
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                            <div id="table-4_processing" class="dataTables_processing process">Processing...</div> 
                            <div class="col-md-12">
                                    <div class="form-group"><input type="text" id="searchFilter" name="searchFilter" class="form-control" id="field-5" placeholder="Search">
                                            <br>
                                        <table id="servicetable" class="table table-bordered datatable">

                                            <thead>
                                            <tr>
                                                <th width="10%">Orders</th>
                                                <th width="30%">Name</th>
                                                <th width="50%">Description</td>
                                                <th width="50%">Action</td>
                                                <input type="hidden" id="selectedSubscription" name="selectedSubscription" value=""/>
                                            </tr>
                                            </thead>
                                            <tbody class="tbody">
                                                    <!-- Insertion From Jquery -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <div class="tab-pane" id="lefttab2">
                                <div class="form-group">
                                    <div  id="vendor_box">
                                            {{Form::select('VendorConnection[]',$VendorConnection,array(),array("id"=>"VendorConnection","class"=>"","multiple"=>"multiple"))}}
                                            <br/>
                                        </div>                                    
                                </div>
                            </div>                        
                        </div>
                    </div>                            
                        </div>                           
                    </div>
                   
                    <div class="modal-footer">
                        <button type="submit" id="currency-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button  type="button" class="btn  btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<script type="text/javascript">
$(document).ready(function () {
    $('.dataTables_processing').css("visibility","hidden");
    $(".btn.download").click(function () {});
        $(".dataTables_wrapper select").select2({
        minimumResultsForSearch: -1
        });
    });

    $("#searchFilter").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#servicetable tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
 
 
        $('#RoutingCategories').change(function(){
           
            $("#option2").remove();
            var data = $('#RoutingCategories option:selected').val();
            $('#RoutingCategories').select2()                   
            var id = data;
            if(data == "" || data == null)
            {
                return false;
            }
            $('#RoutingCategories option:selected').remove();
            $('.process').css("visibility","visible");
            
            $.ajax({
                url : 'routingprofiles/ajaxfetch',
                type: 'post',
                data:{data:data},
                success:function(data){
                    $('.tbody').append("<tr><td><input type='number' min='0' value='99' name='Orders[]' class='form-control' /><input type='hidden' name='RoutingCategory[]' value='"+ data.RoutingCategoryID +"'/></td><td>"+ data.Name +"</td><td>"+ data.Description +"</td><td><a class='btn btn-danger btn-sm' id='"+ id +"' onclick='deleteRoute(this.id)'>DELETE</a></td></tr>");
                    $('.process').css("visibility","hidden");
                    $('#RoutingCategories').select2().select2('val', 'Yes');                
                }, 
                error: function(){
                    $('.process').css("visibility","hidden");
                    toastr.error("Database Error.", "Error", toastr_opts);
                }  
            });
        });

    function deleteRoute(id) {
        var text = $("#"+id).closest('tr').children('td:eq(1)').text();
        var getID = id;
        var data = {
                    id: getID,
                    text: text
                };
        var newOption = new Option(data.text, data.id, false, false);
        var selLength = $('#RoutingCategories option').length;
        if(selLength == 0){
            $('#RoutingCategories').append("<option></option>");    
        }
        $('#RoutingCategories').append(newOption);
        $("#"+id).closest('tr').remove();     
    }
</script>
    
@stop