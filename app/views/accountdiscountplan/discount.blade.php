<div class="col-md-12">
    <table class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="40%">Discount Period</th>
            <th width="30%">Date Applied</th>
            <th width="30%">Applied By</th>
        </tr>
        </thead>
        <tbody>
        @if(count($AccountDiscountPlan))
            @foreach($AccountDiscountPlan as $AccountDiscountPlanRow)
        <tr>
            <td>{{$AccountDiscountPlanRow->StartDate.' to '.Date('Y-m-d',strtotime('-1 day',strtotime($AccountDiscountPlanRow->EndDate)))}}</td>
            <td>{{Date('Y-m-d',strtotime($AccountDiscountPlanRow->created_at))}}</td>
            <td>{{$AccountDiscountPlanRow->CreatedBy}}</td>
        </tr>
                <?php break; ?>
            @endforeach
        @endif
        </tbody>
    </table>
    <table class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="20%">Discount</th>
            <th width="20%">Threshold</th>
            <th width="20%">Unlimited</th>
            <th width="40%">Used Minutes(%)</th>
        </tr>
        </thead>
        <tbody>
        @if(count($AccountDiscountPlan))
            @foreach($AccountDiscountPlan as $AccountDiscountPlanRow)
                <?php
                $UsedPercent = 100;
                    if($AccountDiscountPlanRow->Threshold > 0){
                        $UsedPercent = number_format(($AccountDiscountPlanRow->MinutesUsed/$AccountDiscountPlanRow->Threshold)*100,2);
                    }
                ?>
                <tr>
                    <td>{{$AccountDiscountPlanRow->Name}}</td>
                    <td>{{$AccountDiscountPlanRow->Threshold}}</td>
                    <td>{{$AccountDiscountPlanRow->Unlimited}}</td>
                    <td>
                        <div class="progress discount_progress">
                            <div style="width: {{$UsedPercent}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{$UsedPercent}}" role="progressbar" class="progress-bar progress-bar-success">
                                <span>
                                    @if($AccountDiscountPlanRow->Threshold > 0)
                                        {{$UsedPercent}}% Used ( {{$AccountDiscountPlanRow->MinutesUsed}} Minutes Used )
                                    @else
                                        {{$AccountDiscountPlanRow->MinutesUsed}} Minutes Used
                                    @endif

                                </span>
                            </div>
                        </div>
                    </td>
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
        </tr>
        </tfoot>
    </table>
</div>
