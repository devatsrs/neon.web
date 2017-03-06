<script type="text/javascript">
    $(document).ready(function(){
        var txtSubscription = '{{Product::$TypetoProducts[Product::SUBSCRIPTION]}}';
        $('#add-new-billing_subscription-form').submit(function(e){
            e.preventDefault();
            var modal = $(this).parents('.modal');
            var composit = modal.hasClass('composite')?1:0;
            var datatype = 'select[data-type="billingsubscription"]';
            var SubscriptionID = $("#add-new-billing_subscription-form [name='SubscriptionID']").val()
            if( typeof SubscriptionID != 'undefined' && SubscriptionID != ''){
                update_new_url = baseurl + '/billing_subscription/update/'+SubscriptionID;
            }else{
                update_new_url = baseurl + '/billing_subscription/create';
            }

            showAjaxScript(update_new_url, new FormData($(this)[0]), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    modal.modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    if( typeof data_table !=  'undefined'){
                        data_table.fnFilter('', 0);
                    }else {
                        if (composit==1) {
                            datatype = '.optgroup_'+txtSubscription;
                        }
                        $(datatype).each(function (key, el) {
                            if (composit==1) {
                                var self = $(el).parent();
                            }else{
                                var self = $(el).clone();
                            }
                            if (self.attr('data-active') == 1) {
                                var newState = new Option(response.newcreated.Name, response.newcreated.SubscriptionID, true, true);
                            } else {
                                var newState = new Option(response.newcreated.Name, response.newcreated.SubscriptionID, false, false);
                            }
                            $(el).append(newState);
                            self.trigger('change');
                            $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                                return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                            }));
                        });
                        $('select[data-active="1"]').change();
                    }
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
            return false;
        });
    });

    function txtChange(obj){

        var name = $(obj).attr('name');
        console.log(name)
        var Yearly = '';
        var quarterly = '';
        var monthly = '';
        var decimal_places = 2;
        if(name=='AnnuallyFee'){
            var t = $(obj).val();
            t = parseFloat(t);
            monthly = t/12;
            quarterly = monthly * 3;
        }else if(name=='QuarterlyFee'){
            var t = $(obj).val();
            t = parseFloat(t);
            monthly = t / 3;
            //Yearly  = monthly * 12;
        } else if(name=='MonthlyFee'){
            var monthly = $(obj).val();
            monthly = parseFloat(monthly);
            //Yearly  = monthly * 12;
            //quarterly = monthly * 3;
        }

        var weekly =  parseFloat(monthly / 30 * 7);
        var daily = parseFloat(monthly / 30);

        if(Yearly != '') {
            $('#add-new-billing_subscription-form [name="AnnuallyFee"]').val(Yearly.toFixed(decimal_places));
        }
        if(quarterly != '') {
            $('#add-new-billing_subscription-form [name="QuarterlyFee"]').val(quarterly.toFixed(decimal_places));
        }
        if(monthly != '' && name != 'MonthlyFee') {
            $('#add-new-billing_subscription-form [name="MonthlyFee"]').val(monthly.toFixed(decimal_places));
        }

        $('#add-new-billing_subscription-form [name="WeeklyFee"]').val(weekly.toFixed(decimal_places));
        $('#add-new-billing_subscription-form [name="DailyFee"]').val(daily.toFixed(decimal_places));
    }
</script>

<form id="add-new-billing_subscription-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Name</label>
                    <div class="col-sm-4">
                        <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Description</label>
                    <div class="col-sm-4">
                        <input type="text" name="Description" class="form-control" id="field-1" placeholder="" value="" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Invoice Line Description</label>
                    <div class="col-sm-4">
                        <input type="text" name="InvoiceLineDescription" class="form-control" id="field-1" placeholder="" value="" />
                    </div>
                    <label for="field-1" class="col-sm-2 control-label">Yearly Fee</label>
                    <div class="col-sm-4">
                        <input type="text" name="AnnuallyFee" onchange="txtChange(this)" class="form-control" id="field-1" placeholder="" value="" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Quarterly Fee</label>
                    <div class="col-sm-4">
                        <input type="text" name="QuarterlyFee" onchange="txtChange(this)" class="form-control"   maxlength="10" id="field-1" placeholder="" value="" />
                    </div>
                    <label for="field-1" class="col-sm-2 control-label">Monthly Fee</label>
                    <div class="col-sm-4">
                        <input type="text" name="MonthlyFee" onchange="txtChange(this)" class="form-control"   maxlength="10" id="field-1" placeholder="" value="" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Weekly Fee</label>
                    <div class="col-sm-4">
                        <input type="text" name="WeeklyFee" class="form-control"   maxlength="10" id="field-1" placeholder="" value="" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Daily Fee</label>
                    <div class="col-sm-4">
                        <input type="text" name="DailyFee" class="form-control" maxlength="10" id="field-1" placeholder="" value="" />
                    </div>

                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Currency</label>
                    <div class="col-sm-4">
                        {{Form::SelectControl('currency',0,isset($CurrencyID)?$CurrencyID:'')}}
                        @if(isset($CurrencyID))
                            {{Form::hidden('CurrencyID',$CurrencyID)}}
                        @endif
                        <!--{Form::select('CurrencyID', $currencies, '' ,array("class"=>"form-control select2 small"))}}-->
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Activation Fee</label>
                    <div class="col-sm-4">
                        <input type="text" name="ActivationFee" class="form-control" maxlength="10" placeholder="" value=""  />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Advance Subscription</label>
                    <div class="col-sm-4">
                        <p class="make-switch switch-small">
                            <input id="Advance" name="Advance" type="checkbox" value="1" >
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="hidden" name="SubscriptionID" value="" />
        <button type="submit" id="billing_subscription-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>
        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
            <i class="entypo-cancel"></i>
            Close
        </button>
    </div>
</form>