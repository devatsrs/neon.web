@if(!empty($CustomerLastInvoiceDate))
<div class="col-md-2">
    <div class="form-group">
        <label for="field-5" class="control-label">Last Invoice Date</label>
        <div> {{$CustomerLastInvoiceDate}}</div>
    </div>
</div>
@endif
@if(!empty($CustomerEndDate))
<div class="col-md-2">
    <div class="form-group">
        <label for="field-5" class="control-label">Next Invoice Date</label>
        <div> {{$CustomerEndDate}}</div>
    </div>
</div>
@endif
@if(!empty($lastInvoicePeriod) )
    <div class="col-md-4">
        <div class="form-group">
            <label for="field-5" class="control-label">Last Invoice Period</label>
            <div> {{$lastInvoicePeriod->StartDate.' - '.$lastInvoicePeriod->EndDate}}</div>
        </div>
    </div>
@endif
<input type="hidden" name="LastInvoiceDate" value="{{$CustomerLastInvoiceDate}}">
<input type="hidden" name="NextInvoiceDate" value="{{$CustomerEndDate}}">
<div class="clear"></div>
<div class="col-md-6">
    <div class="form-group">
        <label for="field-5" class="control-label">Period From</label>
        <input value="{{!empty(Input::get('PeriodFrom'))?Input::get('PeriodFrom'):''}}" name="PeriodFrom" class="form-control datepicker" id="field-5" data-date-format="yyyy-mm-dd" placeholder="" type="text" data-enddate="{{$yesterday}}">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="field-5" class="control-label">Period To</label>
        <input value="{{!empty(Input::get('PeriodTo'))?Input::get('PeriodTo'):''}}" name="PeriodTo" class="form-control datepicker" id="field-5" placeholder="" data-date-format="yyyy-mm-dd" type="text" data-enddate="{{$yesterday}}">
    </div>
</div>
<ul class="nav tabs-vertical">
    <li class="active"><a href="#customer" data-toggle="tab">Customer</a></li>
    <li ><a href="#vendor" data-toggle="tab">Vendor</a></li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="customer" >
        <div class="row">
            <div class="col-md-12">
            <table class="table table-bordered datatable">
                <thead>
                <tr>
                    <th width="30%">Unbilled Period </th>
                    <th width="30%">Duration (minutes)</th>
                    <th width="40%">Charged Amount</th>
                </tr>
                </thead>
                <tbody>
                <?php $totalSecond = $totalcost = 0;?>
                @if(count($CustomerNextBilling))
                    @foreach($CustomerNextBilling as $CustomerNextBillingRow)
                        <?php
                        $totalSecond += $CustomerNextBillingRow['TotalMinutes'];
                        $totalcost += $CustomerNextBillingRow['TotalAmount'];
                        ?>
                        <tr>
                            <td>{{$CustomerNextBillingRow['StartDate'].' - '.$CustomerNextBillingRow['EndDate']}}</td>
                            <td>{{intval($CustomerNextBillingRow['TotalMinutes']/60)}}</td>
                            <td>{{$CurrencySymbol.$CustomerNextBillingRow['TotalAmount']}}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3">No Data</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>{{intval($totalSecond/60)}}</strong></td>
                    <td><strong>{{$CurrencySymbol.$totalcost}}</strong></td>
                </tr>
                </tfoot>
            </table>
        </div>
        </div>
    </div>

    <div class="tab-pane" id="vendor" >
        <div class="row">
            <div class="col-md-12">
            <table class="table table-bordered datatable">
                <thead>
                <tr>
                    <th width="30%">Unbilled Period</th>
                    <th width="30%">Duration (minutes)</th>
                    <th width="40%">Charged Amount</th>
                </tr>
                </thead>
                <tbody>
                <?php $totalSecond = $totalcost = 0;?>
                @if(count($VendorNextBilling))
                    @foreach($VendorNextBilling as $VendorNextBillingRow)
                        <?php
                        $totalSecond += $VendorNextBillingRow['TotalMinutes'];
                        $totalcost += $VendorNextBillingRow['TotalAmount'];
                        ?>
                        <tr>

                            <td>{{$VendorNextBillingRow['StartDate'].' - '.$VendorNextBillingRow['EndDate']}}</td>
                            <td>{{intval($VendorNextBillingRow['TotalMinutes']/60)}}</td>
                            <td>{{$CurrencySymbol.$VendorNextBillingRow['TotalAmount']}}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3">No Data</td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>{{intval($totalSecond/60)}}</strong></td>
                    <td><strong>{{$CurrencySymbol.$totalcost}}</strong></td>
                </tr>
                </tfoot>
            </table>
        </div>
        </div>
    </div>
    
</div>
<script>
    // Datepicker
    if ($.isFunction($.fn.datepicker))
    {
        $(".datepicker").each(function(i, el)
        {
            var $this = $(el),
                    opts = {
                        //format: attrDefault($this, 'format', 'dd/mm/yyyy'),
                        startDate: attrDefault($this, 'startdate', ''),
                        endDate: attrDefault($this, 'enddate', ''),
                        daysOfWeekDisabled: attrDefault($this, 'disableddays', ''),
                        startView: attrDefault($this, 'startview', 0),
                        rtl: rtl()
                    },
                    $n = $this.next(),
                    $p = $this.prev();

            $this.datepicker(opts);

            if ($n.is('.input-group-addon') && $n.has('a'))
            {
                $n.on('click', function(ev)
                {
                    ev.preventDefault();

                    $this.datepicker('show');
                });
            }

            if ($p.is('.input-group-addon') && $p.has('a'))
            {
                $p.on('click', function(ev)
                {
                    ev.preventDefault();

                    $this.datepicker('show');
                });
            }
        });
    }
</script>