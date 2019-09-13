
        <div class="row">
            <div class="col-md-12">
            <table class="table table-bordered datatable">
                <thead>
                <tr>
                    <th width="10%">Type</th>
                    <th width="30%">Description</th>
                    <th width="30%">Period</th>
                    <th width="15%">Amount</th>
                    <th width="15%">Date</th>
                </tr>
                </thead>
                <tbody>
                <?php $totalSecond = $totalcost = 0;?>
                @if(count($UnbilledResult))
                    @foreach($UnbilledResult as $UnbilledResultRaw)
                        <tr>
                            <td>{{$UnbilledResultRaw->Type}}</td>
                            <td>{{$UnbilledResultRaw->Description}}</td>
                            <td>{{$UnbilledResultRaw->Period}}</td>
                            <td>{{$CurrencySymbol.$UnbilledResultRaw->Amount}}</td>
                            <td>{{$UnbilledResultRaw->created_at}}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3">No Data</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
        </div>