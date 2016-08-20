@if(!empty($cronJobs))
    <p>Following Cron jobs are found related to current ratetable.
    Please delete cron job first and than press delete button at bottom.</p>
<table class="table table-bordered datatable" id="cronjob-table">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
        <th width="50%">Cron Job</th>
        <th width="5%">Status</th>
        <th width="25%">Created by</th>
        <th width="15%">Action</th>
    </tr>
    </thead>
    <tbody>
    @foreach($cronJobs as $row)
        <td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="{{$row['CronJobID']}}" class="rowcheckbox" ></div></td>
        <td>{{$row['JobTitle']}}</td>
        <td>{{$row['Status']==1?'<i style="font-size:22px;color:green" class="entypo-check"></i>':'<i style="font-size:28px;color:red" class="entypo-cancel"></i>'}}</td>
        <td>{{$row['created_by']}}</td>
        <td><a href="javascript:void(0)" data-id = '{{$row['CronJobID']}}'  class="btn cronjobedelete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete</a></td>
    @endforeach
    </tbody>
</table>
@endif