<?php
 if(empty($PageRefresh)){
     $PageRefresh='';
 }
?>
<link rel="stylesheet" type="text/css" href="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/bootstrap-duallistbox.css'; ?>">
<script src="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/jquery.bootstrap-duallistbox.min.js'; ?>" ></script>

        
<style>
    .modal-ku {
  width: 750px;
  margin: auto;
}
</style>
<script>
    $(document).ready(function ($) {
        $('#add-new-routingcategory-form').submit(function(e){
            e.preventDefault();
            var PageRefresh = '{{$PageRefresh}}';
            var RoutingCategoryID = $("#add-new-routingcategory-form [name='RoutingProfileID']").val();
            console.log(RoutingCategoryID);
            if( RoutingCategoryID != ''){
                update_new_url = baseurl + '/routingprofiles/update/'+RoutingCategoryID;
            }else{
                update_new_url = baseurl + '/routingprofiles/create';
            }
            reorderingoptions();
            setTimeout(function(){
                showAjaxScript(update_new_url, new FormData(($('#add-new-routingcategory-form')[0])), function(response){
                    console.log(response);
                    $(".btn").button('reset');
                    if (response.status == 'success') {
                        $('#add-new-modal-routingcategory').modal('hide');
                        data_table.fnFilter('', 0);

                        toastr.success(response.message, "Success", toastr_opts);
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
        })
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
                                        <div  id="routingcategory_box">
                                            <select id="RoutingCategory" name="RoutingCategory[]" multiple>
                                            <?php 
                                            foreach($RoutingCategory as $key_cat => $cat_data){ ?>
                                                <option value="<?php echo $key_cat;?>">
                                                    <?php echo $cat_data;?>
                                                </option>
                                                <?php
                                            } ?>
                                            </select>
<!--                                        {{Form::select('RoutingCategory[]',$RoutingCategory,array(),array("id"=>"RoutingCategory","class"=>"","multiple"=>"multiple"))}}-->
                                            <div id="priority">
                                                <div style="float:right;">
                                                    <button type="button" class="btn remove btn-default" id="move-up" title="Remove selected" value="">       
                                                        <i class="glyphicon glyphicon-arrow-up"></i>     
                                                    </button>
                                                    <button type="button" class="btn remove btn-default" id="move-down" title="Remove selected" value="">       
                                                        <i class="glyphicon glyphicon-arrow-down"></i>     
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
jQuery(document).ready(function ($) {
    var RoutingCategory = $('#RoutingCategory').bootstrapDualListbox({
        nonselectedlistlabel: 'Non-selected',
        selectedlistlabel: 'Remove',
        filterPlaceHolder: 'Search',
        moveOnSelect: false,
        infoText:false,
        preserveselectiononmove: 'moved'
    });
//    
//    
//    var vendors = $('#VendorConnection').bootstrapDualListbox({
//        nonselectedlistlabel: 'Non-selected',
//        selectedlistlabel: 'Selected',
//        filterPlaceHolder: 'Search',
//        moveonselect: false,
//        preserveselectiononmove: 'moved',
//    });


$(".btn.download").click(function () {});
$(".dataTables_wrapper select").select2({
minimumResultsForSearch: -1
});

});

$(document).ready(function() {
    $('#move-up').click(moveUp);
    $('#move-down').click(moveDown);
});  
function moveUp() {
    $('.box2 select :selected').each(function(i, selected) {
        if (!$(this).prev().length) return false;
        $(this).insertBefore($(this).prev());
        console.log($(this).attr('data-sortindex'));
    });
    $('.box2 select').focus().blur();
    
}
function reorderingoptions(){
    
    $('#RoutingCategory').find('option').remove();
    $('#routingcategory_box .box2 select > option').each(function(i, selected) {
        console.log($(this).attr('data-sortindex'));
       // $("#RoutingCategory").append(new Option($(this).text(), $(this).val()));
       $("#RoutingCategory").append('<option selected value="'+$(this).val()+'">'+$(this).text()+'</option>');
        $(this).attr('data-sortindex', i);
    });
    $('#routingcategory_box .box1 select > option').each(function(i, selected) {
       $("#RoutingCategory").append('<option value="'+$(this).val()+'">'+$(this).text()+'</option>');
        $(this).attr('data-sortindex', i);
    });
    
}
function moveDown() {
    $($('.box2 select :selected').get().reverse()).each(function(i, selected) {
        if (!$(this).next().length) return false;
        $(this).insertAfter($(this).next());
    });
    $('#selected-items select').focus().blur();
    //setTimeout(function(){reorderingoptions();}, 200);
}
</script>
    
@stop