@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-modal-itemtype">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Dynamic Fields</h4>
                </div>
                @include('servicetemplate.servicetemplatetype.subcriptiontypeform')
            </div>
        </div>
    </div>
@stop