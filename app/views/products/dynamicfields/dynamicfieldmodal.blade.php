@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-modal-dynamicfield">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Dynamic Field</h4>
                </div>
                @include('products.dynamicfields.dynamicfieldform')
            </div>
        </div>
    </div>
@stop