@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-billingclass">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Billing Class</h4>
                </div>
                <div class="modal-body">
                    @include('billingclass.billingclass')
                </div>
                <div class="modal-footer">
                    <button id="save_billing" href="{{URL::to('billing_class/store/1')}}" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop