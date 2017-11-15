<div class="row">
<div class="col-md-12">
<table class="table table-bordered datatable" id="account_table">
    <thead>
    <tr>
        <th width="20%">Account</th>
        <th width="20%">No. of Calls</th>
        <th width="10%">Billed Duration (Min.)</th>
        <th width="10%">Charged Amount</th>
        <th width="10%">ACD (mm:ss)</th>
        <th width="10%">ASR (%)</th>
        @if((int)Session::get('customer') == 0)
        <th width="10%">Margin</th>
        <th width="10%">Margin (%)</th>
        @endif
    </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
    <tr>

    </tr>
    </tfoot>
</table>
    </div>
</div>