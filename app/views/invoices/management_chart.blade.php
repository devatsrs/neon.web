<?php $ManagementReportTemplate = json_decode($InvoiceTemplate->ManagementReport,true);
$reportcount=1;?>
<div class="row">
    @foreach((array)$ManagementReportTemplate as $ManagementReportTemplateRow)
        @if($ManagementReportTemplateRow['Status'] == 1)
            <?php
            $reportcount++;
            if($ManagementReportTemplateRow['Title'] == 'Longest Calls'){
                $table_data = $ManagementReports['LongestCalls'];
            } else if($ManagementReportTemplateRow['Title'] == 'Most Expensive Calls'){
                $table_data = $ManagementReports['ExpensiveCalls'];
            } else if($ManagementReportTemplateRow['Title'] == 'Frequently Called Numbers'){
                $table_data = $ManagementReports['DialledNumber'];
            } else if($ManagementReportTemplateRow['Title'] == 'Daily Summary'){
                $table_data = $ManagementReports['DailySummary'];
            } else if($ManagementReportTemplateRow['Title'] == 'Usage by Category'){
                $table_data = $ManagementReports['UsageCategory'];
            }
            ?>
            @if($reportcount%2 == 0)
                <div class="clear-both"></div>
            @endif
            <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title"><strong>{{$ManagementReportTemplateRow['UsageName']}}</strong></div>
            </div>
            <div class="panel-body with-table">
                <table class="table table-bordered table-responsive" id="LongestCalls">
                    <thead>
                    <tr>
                        @if($ManagementReportTemplateRow['Title'] == 'Longest Calls' || $ManagementReportTemplateRow['Title'] == 'Most Expensive Calls' || $ManagementReportTemplateRow['Title'] == 'Frequently Called Numbers')
                            <th>From</th>
                        @elseif($ManagementReportTemplateRow['Title'] == 'Daily Summary')
                            <th>Date</th>
                        @elseif($ManagementReportTemplateRow['Title'] == 'Usage by Category')
                            <th>Description</th>
                        @endif

                        @if($ManagementReportTemplateRow['Title'] == 'Longest Calls' || $ManagementReportTemplateRow['Title'] == 'Most Expensive Calls')
                            <th>To</th>
                            <th>Mins</th>
                            <th>Charge</th>
                        @elseif($ManagementReportTemplateRow['Title'] == 'Frequently Called Numbers' || $ManagementReportTemplateRow['Title'] == 'Daily Summary' || $ManagementReportTemplateRow['Title'] == 'Usage by Category')
                            <th>Calls</th>
                            <th>Mins</th>
                            <th>Charge</th>
                        @endif

                    </tr>
                    </thead>
                    <tbody>
                    <?php  $billed_duration = $cost = $call_count = 0?>
                    @foreach($table_data as $call_row)
                        <?php
                        if($ManagementReportTemplateRow['Title'] == 'Frequently Called Numbers' || $ManagementReportTemplateRow['Title'] == 'Daily Summary' || $ManagementReportTemplateRow['Title'] == 'Usage by Category'){
                            $call_count += $call_row['col2'];
                        }

                        $billed_duration += $call_row['col3'];
                        $cost += $call_row['col4'];
                        ?>
                        <tr>
                            <td>{{$call_row['col1']}}</td>
                            <td>{{$call_row['col2']}}</td>
                            <td>{{$call_row['col3']}}</td>
                            <td>{{$CurrencySymbol.number_format($call_row['col4'],$RoundChargesAmount)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td>
                            @if($ManagementReportTemplateRow['Title'] == 'Frequently Called Numbers' || $ManagementReportTemplateRow['Title'] == 'Daily Summary' || $ManagementReportTemplateRow['Title'] == 'Usage by Category')
                                {{$call_count}}
                            @endif
                        </td>
                        <td>{{$billed_duration}}</td>
                        <td>{{$CurrencySymbol.number_format($cost,$RoundChargesAmount)}}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
        @endif
    @endforeach

</div>
