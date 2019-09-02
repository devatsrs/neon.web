<div class="modal fade" id="minutes_report-modal">
    <div class="modal-dialog" style="width: 70%;">
        <div class="modal-content">
            <form id="add-minutes_report-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><strong> Discount Plan Detail</strong></h4>
                </div>
                <div class="modal-body">
                    <div class="row" id="used_minutes_report">

                    </div>
                </div>
                <div class="modal-footer">
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade in" id="modal-subscription">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="subscription-form" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Subscription</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Subscription*</label>
                                        {{ Form::select('SubscriptionID', BillingSubscription::getSubscriptionsList(getParentCompanyIdIfReseller(User::get_companyID()), "All"), '' , array("class"=>"select2")) }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">No</label>
                                        <input type="text" name="SequenceNo" class="form-control" placeholder="AUTO" value=""  />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="ActivationFee" class="control-label">Activation Fee*</label>
                                        <input type="text" name="ActivationFee" id="ActivationFee" class="form-control" value="" />
                                    </div>
                                </div>
                            </div>
                             <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="WeeklyFee" class="control-label">Weekly Fee*</label>
                                        <input type="text" name="WeeklyFee" id="WeeklyFee" class="form-control" value="" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="QuarterlyFee" class="control-label">Quarterly Fee*</label>
                                        <input type="text" name="QuarterlyFee" class="form-control"   maxlength="10" id="QuarterlyFee" placeholder="" value="" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5221" class="control-label">Activation Fee Currency*</label>
                                        {{ Form::select('OneOffCurrencyID', Currency::getCurrencyDropdownIDList(), '', array("class"=>"select2 small")) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Start Date*</label>
                                        <input type="text" name="StartDate" id="StartDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""    />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Discount</label>
                                        <input type="text" name="DiscountAmount" class="form-control" value=""  />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Frequency*</label>
                                        {{ Form::select('Frequency',$frequency,'',['class' => 'form-control select2 small']) }}
                                    </div>
                                </div>
                            </div>    
                            
                            <!-- -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Exempt From Tax</label>
                                        <div class="clear">
                                            <p class="make-switch switch-small">
                                                <input type="checkbox" name="ExemptTax" value="0">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            

                            
                            
                            

                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Invoice Description</label>
                                        <input type="text" name="InvoiceDescription" class="form-control" value="" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Qty</label>
                                        <input type="text" name="Qty" class="form-control" value="1" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="DailyFee" class="control-label">Daily Fee*</label>
                                        <input type="text" name="DailyFee" id="DailyFee" class="form-control" value="" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="MonthlyFee" class="control-label">Monthly Fee*</label>
                                        <input type="text" name="MonthlyFee" class="form-control"   maxlength="10" id="MonthlyFee" placeholder="" value="" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="AnnuallyFee" class="control-label">Yearly Fee*</label>
                                        <input type="text" name="AnnuallyFee" class="form-control"   maxlength="10" id="AnnuallyFee" placeholder="" value="" />
                                    </div>
                                </div>
                            </div>
                            
                            <!-- -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-521" class="control-label">Recurring Fee Currency*</label>
                                        {{ Form::select('RecurringCurrencyID', Currency::getCurrencyDropdownIDList(), '', array("class"=>"select2 small")) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">End Date*</label>
                                        <input type="text" name="EndDate" id="EndDate"  class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""  />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Discount Type</label>
                                        {{ Form::select('DiscountType', array('Flat' => 'Flat', 'Percentage' => 'Percentage') ,'', array("class"=>"form-control") ) }}
                                    </div>
                                </div>
                            </div>

                            

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="field-5" class="control-label">Active</label>
                                        <div class="clear">
                                            <p class="make-switch switch-small">
                                                <input type="checkbox" name="Status" value="1" checked="checked">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="row" id="add-dynamice-fields-show">
                            </div>
                        </div>
                    </div>
                </div>
                @if(isset($AccountService))
                    <input type="hidden" name="AccountSubscriptionID" value="{{  $AccountSubscriptionID }}">
                    <input type="hidden" name="AccountServiceID" value="{{$AccountService->AccountServiceID}}">
                @else
                    <input type="hidden" name="AccountSubscriptionID" value="0">
                    <input type="hidden" name="AccountServiceID" value="0">
                @endif
                <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
<div class="modal fade in" id="modal-add_discountplan">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add_discountplan_form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Account</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account Name</label>
                                <input type="text" name="AccountName" class="form-control namehide" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account CLI</label>
                                <input type="text" name="AccountCLI" class="form-control namehide" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Inbound Discount Plan</label>
                                <!--<input type="text" name="InboundDiscountPlans" class="form-control" value="" />-->
                                {{Form::select('InboundDiscountPlans',$DiscountPlan,'',array('class'=>'form-control select2'))}}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Outbound Discount Plan</label>
                                <!--<input type="text" name="OutboundDiscountPlans" class="form-control" value="" />-->
                                {{Form::select('OutboundDiscountPlans',$DiscountPlan,'',array('class'=>'form-control select2'))}}
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                <input type="hidden" name="AccountSubscriptionID_dp">
                <input type="hidden" name="SubscriptionDiscountPlanID">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

<div class="modal fade in" id="modal-bulkedit_discountplan">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkedit_discountplan_form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Account</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input id="InboundCheckbox" name="InboundCheckbox" type="checkbox" value="0" >
                                <label for="InboundCheckbox" class="control-label">Inbound Discount Plan</label>
                                {{Form::select('BulkInboundDiscountPlans',$DiscountPlan,'',array('class'=>'form-control select2'))}}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input id="OutboundCheckbox" name="OutboundCheckbox" type="checkbox" value="0" >
                                <label for="OutboundCheckbox" class="control-label">Outbound Discount Plan</label>
                                {{Form::select('BulkOutboundDiscountPlans',$DiscountPlan,'',array('class'=>'form-control select2'))}}
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                <input type="hidden" name="AccountSubscriptionID_bulk">
                <input type="hidden" name="AllSubscriptionDiscountPlanID">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
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