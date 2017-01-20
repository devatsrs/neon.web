@if(count($response->data->aaData))
@foreach($response->data->aaData as $alert)
<tr>
    <td class="">{{$alert[0]}}</td>
    <td class="">{{$alertType[$alert[1]]}}</td>
    <td class=" sorting_1">{{$alert[2]}}</td>
    <td class="">
        <div class="hiddenRowData">
            <div class="hidden" name="Name">{{$alert[0]}}</div>
            <div class="hidden" name="AlertType">{{$alertType[$alert[1]]}}</div>
            <div class="hidden" name="send_at">{{$alert[2]}}</div>
            <div class="hidden" name="Subject">{{$alert[3]}}</div>
            <div class="hidden" name="Message">{{$alert[4]}}</div>
        </div>
        <a class="view-alert btn btn-default btn-sm tooltip-primary" data-original-title="View" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-eye"></i></a>
    </td>
</tr>
@endforeach
@else
    <tr><td colspan="4" valign="top">No alerts sent.</td></tr>
@endif