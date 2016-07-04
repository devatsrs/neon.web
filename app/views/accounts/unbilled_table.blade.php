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
        @foreach($UnbilledResult as $UnbilledResultRaw)
            <tr>
                <td>{{$UnbilledResultRaw->date}}</td>
                <td>{{$UnbilledResultRaw->TotalMinutes}}</td>
                <td>{{$UnbilledResultRaw->TotalCost}}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>

        </tr>
        </tfoot>
    </table>
</div>
