@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-modal-itemtype">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Access Category</h4>
                </div>
                @include('DIDCategory.DIDCategoryform')
            </div>
        </div>
    </div>
@stop