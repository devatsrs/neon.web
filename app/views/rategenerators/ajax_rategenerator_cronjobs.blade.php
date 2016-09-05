@if(!empty($cronJobs))
    <p>Rate Generator cannot be deleted. Following Cron jobs are setup against Rate Generator.
        <br>Please delete cron job first.</p>
<table class="table table-bordered datatable" id="cronjob-table">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
        <th width="50%">Cron Job</th>
        <th width="5%">Status</th>
        <th width="25%">Created by</th>
    </tr>
    </thead>
    <tbody>
    @foreach($cronJobs as $row)
    <tr>
        <td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="{{$row['CronJobID']}}" class="rowcheckbox" ></div></td>
        <td>{{$row['JobTitle']}}</td>
        <td>{{$row['Status']==1?'<i style="font-size:22px;color:green" class="entypo-check"></i>':'<i style="font-size:28px;color:red" class="entypo-cancel"></i>'}}</td>
        <td>{{$row['created_by']}}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif