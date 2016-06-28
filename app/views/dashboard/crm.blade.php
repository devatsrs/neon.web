@extends('layout.main')
@section('content')
<br />
<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>My Tasks ()</h3>
                    
                </div>

                <div class="panel-options">
                    {{ Form::select('CompanyGatewayID', array("All"=>"All","duetoday"=>"Due Today","duesoon"=>"Due Soon","overdue"=>"Overdue"), 1, array('id'=>'TaskType','class'=>'select_gray')) }}
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                    <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body" style="max-height: 450px; overflow-y: auto; overflow-x: hidden;">
                <table id="UsersTasks" class="table table-responsive">
                    <thead>
                    <tr>
                        <th width="25%">Task Subject</th>
                        <th width="25%">Task Status</th>
                        <th width="25%">Due Date</th>
                        <th width="25%">Related To</th>
                    </tr>

                    </thead>

                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(function() {
     GetUsersTasks();
	 
	  $("#TaskType").change(function(){
        GetUsersTasks();
    });
});


function loadingUnload(table,bit){
    var panel = jQuery(table).closest('.panel');
    if(bit==1){
        blockUI(panel);
        panel.addClass('reloading');
    }else{
        unblockUI(panel);
        panel.removeClass('reloading');
    }
}

$('body').on('click', '.panel > .panel-heading > .panel-options > a[data-rel="reload"]', function(e){
    e.preventDefault();
    var id = $(this).parents('.panel-primary').find('table').attr('id');
    if(id=='UsersTasks'){
        GetUsersTasks();
    }
});
function GetUsersTasks(){
    var table = $('#UsersTasks');
    loadingUnload(table,1);
    var url = baseurl+'/dashboard/GetUsersTasks';
	var Tasktype = $('#TaskType').val();
    $.ajax({
        url: url,  //Server script to process data
        type: 'POST',
        dataType: 'json',
		data:{TaskTypeData:Tasktype}, 
        success: function (response) {
            var accounts = response.UserTasks;
            html = '';
            table.parents('.panel-primary').find('.panel-title h3').html('My Tasks ('+accounts.length+')');
            table.find('tbody').html('');
            if(accounts.length > 0){
                for (i = 0; i < accounts.length; i++) {
                    html +='<tr>';
                    html +='      <td>'+accounts[i]["Subject"]+'</td>';
					html +='      <td>'+accounts[i]["Status"]+'</td>';
					html +='      <td>'+accounts[i]["DueDate"]+'</td>';
					html +='      <td>'+accounts[i]["Company"]+'</td>';
                    html +='</tr>';
                }
            }else{
                html = '<td colspan="4">No Records found.</td>';
            }
            table.find('tbody').html(html);
            loadingUnload(table,0);
        },    
    });
}
    function dataGrid(Pincode,Startdate,Enddate,PinExt,CurrencyID){
        $("#pin_grid_main").removeClass('hidden');
        if(PinExt == 'pincode'){
            $('.pin_expsense_report').find('h3').html('Pincode '+Pincode+' Detail Report');
        }
        if(PinExt == 'extension'){
            $('.pin_expsense_report').find('h3').html('Extension'+Pincode+' Detail Report');

        }
        data_table = $("#pin_grid").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/type",
            "fnServerParams": function (aoData) {
                aoData.push(
                        {"name": "Pincode", "value": Pincode},
                        {"name": "Startdate", "value": Startdate},
                        {"name": "Enddate", "value": Enddate},
                        {"name": "PinExt", "value": PinExt},
                        {"name": "CurrencyID", "value": CurrencyID}
                );

                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name": "Pincode", "value": Pincode},
                        {"name": "Startdate", "value": Startdate},
                        {"name": "Enddate", "value": Enddate},
                        {"name": "PinExt", "value": PinExt},
                        {"name": "CurrencyID", "value": CurrencyID},
                        {"name":"Export","value":1}
                );

            },
            "iDisplayLength": '10',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "aoColumns": [
                {"bSortable": true},  // 1 Destination Number
                {"bSortable": true},  // 2 Total Cost
                {"bSortable": true}  // 3 Number of Times Dialed
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
            "fnDrawCallback": function () {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });
            }

        });
    }
</script>
@stop