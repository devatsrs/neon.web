<script src="{{ URL::asset('assets/js/billing_subscription.js') }}"></script>
@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-modal-product-subscription">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New product and subscription</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="nav nav-tabs bordered">
                                <li class="active">
                                    <a href="#product" data-toggle="tab">
                                        <span class="visible-xs"><i class="entypo-home"></i></span>
                                        <span class="hidden-xs">Item</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#subscription" data-toggle="tab">
                                        <span class="visible-xs"><i class="entypo-user"></i></span>
                                        <span class="hidden-xs">Subscription</span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <form id="add-edit-product-subscription" method="post">
                                    <input type="hidden" name="productsubscription" value="1">
                                </form>
                                <div class="tab-pane active" id="product">
                                    @include('products.productform')
                                </div>
                                <div class="tab-pane" id="subscription">
                                    @include('billingsubscription.subscriptionform')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop