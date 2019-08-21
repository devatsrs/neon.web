<?php
 if(empty($PageRefresh)){
     $PageRefresh='';
 }
?>
@section('footer_ext')
    @parent
    <div class="modal fade" id="details-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-routingcategory-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title">User Activity Details</h3>
                    </div>
                    <div class="modal-body">
                            <div class="" style="overflow: auto;">
                                <table id="table-list2" class="table table-bordered datatable">
                                                              
                                </table>
                            </div>    
                    </div>
                    <div class="modal-footer">
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop