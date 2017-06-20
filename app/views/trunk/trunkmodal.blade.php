
<script>
    $(document).ready(function ($) {
        $('#form-trunk-add').submit(function(e){
            e.preventDefault();
            var TrunkID = $("#form-trunk-add [name='TrunkID']").val()
            if( typeof TrunkID != 'undefined' && TrunkID != ''){
                update_new_url = baseurl + '/trunks/update/'+TrunkID;
            }else{
                update_new_url = baseurl + '/trunks/store';
            }

           // showAjaxScript(update_new_url, new FormData(($('#form-trunk-add')[0])), function(response){
		    showAjaxScript(update_new_url, new FormData(($('#form-trunk-add')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-trunk').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    $('select[data-type="trunk"]').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.Trunk, response.newcreated.TrunkID, true, true);
                        }else{
                            var newState = new Option(response.newcreated.Trunk, response.newcreated.TrunkID, false, false);
                        }
                        $(el).append(newState).trigger('change');
                        $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                            return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                        }));
                    });
                    $('#TrunkStatus').trigger('change');
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        })
    });
</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-trunk">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-trunk-add" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New trunk</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-1" class="control-label">Title</label>
                                    <input type="text" class="form-control" id="Trunk" name="Trunk" placeholder="Title" value="{{Input::old('Trunk')}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Rate Prefix</label>
                                    <input type="text" class="form-control" name="RatePrefix" data-mask="999999999999" placeholder="Rate Prefix" value="{{Input::old('RatePrefix')}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Area Prefix</label>
                                    <input type="text" class="form-control" data-mask="999999999999" name="AreaPrefix" placeholder="Area Prefix" value="{{Input::old('AreaPrefix')}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Prefix</label>
                                    <input type="text" class="form-control" data-mask="999999999999" name="Prefix" placeholder="Prefix" value="{{Input::old('Prefix')}}">

                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-12 control-label">Active</label>                                    

                                </div>
                                <div class="form-group">                                    
                                    <div class="make-switch switch-small">
                                        <input type="checkbox" name="Status"  @if(Input::old('Status') =='' ) checked="" @else  @if( ( Input::old('Status') !='' ) && Input::old('Status') == 1 ) checked=""  @endif @endif value="1">
                                    </div>

                                </div>
                            </div>
                        </div>                        
                        
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="TrunkID" />
                        <button type="submit" id="trunk-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
@stop