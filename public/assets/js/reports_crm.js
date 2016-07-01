function loading(table,bit){
    var panel = jQuery(table).closest('.loading');
    if(bit==1){
        blockUI(panel);
        panel.addClass('reloading');
    }else{
        unblockUI(panel);
        panel.removeClass('reloading');
    }
}
function getPipleLineData(chart_type,submitdata){
	loadingUnload(".crmdpipeline",1);
    $.ajax({
        type: 'POST',
        url: baseurl+'/dashboard/getpiplelinepata',
        dataType: 'html',
        data:submitdata,
        aysync: true,
        success: function(data1) {
			$('#crmdpipeline1').html('');
			
           loadingUnload(".crmdpipeline",0);
            if(data1!='') {	 		
			var dataObj = JSON.parse(data1);
			var line_chart_demo_2 = $(".crmdpipeline");
            var crmpipelinedata =  [];
			
			for(var s=0;s<4;s++)			
			{
	              crmpipelinedata[s] =
					{
					 'status': dataObj.data[s+1].status,
					 'Worth': dataObj.data[s+1].Worth			
					 }
			}
			Morris.Bar({
			  element: 'crmdpipeline1',
			  data: crmpipelinedata,
			  xkey: 'status',
			  ykeys: ['Worth'],
			  barColors: ['#3399FF', '#333399'],
			  labels: ['']
			});	
			
			$('.PipeLineResult').html('<h3>'+dataObj.CurrencyCode+dataObj.TotalWorth + " Total Value  - "+dataObj.TotalOpportunites + " Opportunities </h3>");
           }else{
                $('.crmdpipeline').html('NO DATA!!');
            }
			
		}
    });
}
function set_search_parameter(submit_form){
	$searchFilter.UsersID = $(submit_form).find("[name='UsersID']").val();
    $searchFilter.CurrencyID = $(submit_form).find("[name='CurrencyID']").val();
}
function getForecast(chart_type,submit_data){
    loading(".bar_chart_"+chart_type,1);
    $.ajax({
        type: 'GET',
        url: baseurl+'/analysis/getAnalysisBarData',
        dataType: 'json',
        data:submit_data,
        aysync: true,
        success: function(data) {
            loading(".bar_chart_"+chart_type,0);
            if(data.categories != '' && data.categories.split(',').length > 0) {
                $('.bar_chart_'+chart_type).highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: data.Title
                    },
                    xAxis: {
                        categories: data.categories.split(','),
                        title: {
                            text: null
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Calls',
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    plotOptions: {
                        bar: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -40,
                        y: 80,
                        floating: true,
                        borderWidth: 1,
                        backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                        shadow: true
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Call Count',
                        data: data.CallCount.split(',').map(parseFloat)
                    }, {
                        name: 'Call Cost',
                        data: data.CallCost.split(',').map(parseFloat)
                    }, {
                        name: 'Call Minutes',
                        data: data.CallMinutes.split(',').map(parseFloat)
                    }]
                });
            }else{
                $('.bar_chart_'+chart_type).html('NO DATA!!');
            }
        }
    });
}

function reloadCrmCharts(pageSize,$searchFilter){

    /* get destination data for today and display in pie three chart*/
    getPipleLineData($searchFilter.chart_type,$searchFilter);

    /* get data by time in bar chart*/
    //getForecast($searchFilter.chart_type,$searchFilter);
	 GetUsersTasks();
  
}


////////////////////////////////////////crm page code///////////////////////////////////////////////////////////////////////////////
 var $searchFilter = {};
  
  
$(function() {   
	 
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
    var id = $(this).parents().attr('id');
	var submit_form = $("#crm_dashboard")
	 set_search_parameter(submit_form);
	 ;
    if(id=='UsersTasks'){
        GetUsersTasks();
    }
	if(id=='Pipeline'){
        getPipleLineData('',$searchFilter);
    }
});
function GetUsersTasks(){
    var table = $('#UsersTasksTable');
    loadingUnload(table,1);
    var url = baseurl+'/dashboard/GetUsersTasks';
	var Tasktype = $('#TaskType').val();
	var UsersID  = $("#crm_dashboard [name='UsersID']").val();
    $.ajax({
        url: url,  //Server script to process data
        type: 'POST',
        dataType: 'json',
		data:{TaskTypeData:Tasktype,UsersID:UsersID}, 
        success: function (response) {
            var accounts = response.UserTasks;
            html = '';
            table.parents('.panel-primary').find('.panel-title h3').html('My Active Tasks ('+accounts.length+')');
            table.find('tbody').html('');
            if(accounts.length > 0){
                for (i = 0; i < accounts.length; i++) {
                    html +='<tr>';
					if(accounts[i]["Priority"]==1){
						html +='      <td><div class="priority inlinetable">&nbsp;</div>  <div class="inlinetable">'+accounts[i]["Subject"]+'</div></td>';
					}else{
						html +='      <td><div class="normal inlinetable">&nbsp;</div> <div class="inlinetable">'+accounts[i]["Subject"]+'</div></td>';
					}
					
					if(accounts[i]["DueDate"]=='0000-00-00 00:00:00')
					{
						accounts[i]["DueDate"] = '';
					}
                    
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
