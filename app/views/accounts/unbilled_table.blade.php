<div class="col-md-12">
    <table class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="30%">Date</th>
            <th width="30%">Billed Duration (sec)</th>
            <th width="40%">Charged Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php $totalSecond = $totalcost = 0;?>
        @if(count($UnbilledResult))
        @foreach($UnbilledResult as $UnbilledResultRaw)
            <?php
                $totalSecond += $UnbilledResultRaw->TotalMinutes;
                $totalcost += $UnbilledResultRaw->TotalCost;
            ?>
            <tr>
                <td>{{$UnbilledResultRaw->date}}</td>
                <td>{{$UnbilledResultRaw->TotalMinutes}}</td>
                <td>{{$CurrencySymbol.$UnbilledResultRaw->TotalCost}}</td>
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
            <td><strong>{{$totalSecond}}</strong></td>
            <td><strong>{{$CurrencySymbol.$totalcost}}</strong></td>
        </tr>
        </tfoot>
    </table>
</div>
