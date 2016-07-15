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
					 'Worth': dataObj.data[s+1].Worth,
					 'CurrencyCode':dataObj.data[s+1].CurrencyCode		
					 }
			}
			Morris.Bar({
			  element: 'crmdpipeline1',
			  data: crmpipelinedata,
			  xkey: 'status',
			  ykeys: ['Worth'],
			  barColors: ['#3399FF', '#333399'],
			  labels: [''],
				hoverCallback:function (index, options, content, row) {
					return '<div class="morris-hover-row-label">'+row.CurrencyCode+row.Worth+'</div>'
				}
			});	
			
			$('.PipeLineResult').html('<h3>'+dataObj.CurrencyCode+dataObj.TotalWorth + " Total Value  - "+dataObj.TotalOpportunites + " Oppurtunities </h3>");
           }else{
                $('.crmdpipeline').html('NO DATA!!');
            }
			
		}
    });
}
function set_search_parameter(submit_form){
	$searchFilter.UsersID = $(submit_form).find("[name='UsersID[]']").val();
    $searchFilter.CurrencyID = $(submit_form).find("[name='CurrencyID']").val();
}

function reloadCrmCharts(pageSize,$searchFilter){

    /* get destination data for today and display in pie three chart*/
    getPipleLineData($searchFilter.chart_type,$searchFilter);

    /* get data by time in bar chart*/
    //getForecast($searchFilter.chart_type,$searchFilter);
	 GetUsersTasks();
	 
	  GetForecastData();
  
}


////////////////////////////////////////crm page code///////////////////////////////////////////////////////////////////////////////
 var $searchFilter = {};
  
  
$(function() {   
	 
	  $("#TaskType").change(function(){
        GetUsersTasks();
    });
	
	$('#crm_dashboard_forecast').submit(function(e) {
		e.stopImmediatePropagation();
		e.preventDefault();
        GetForecastData();         
    });
	$('#dateendid').change(function(e) {
		e.stopImmediatePropagation();
		e.preventDefault();
     	//GetForecastData();
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
	
	if(id=='Forecast'){
        GetForecastData();
    }	
});


function GetForecastData(){
	loadingUnload(".crmdforecast",1);	 
	var UsersID  	= $("#crm_dashboard [name='UsersID[]']").val();
	var CurrencyID  = $("#crm_dashboard [name='CurrencyID']").val();
	var Closingdate   = $("#crm_dashboard_forecast [name='Closingdate']").val();
	var Status   	= $("#crm_dashboard_forecast input.statusCheckbox:checked").map(function() {  return this.value; }).get().join();

    $.ajax({
        type: 'POST',
        url: baseurl+'/dashboard/getforecastdata',
        dataType: 'html',
        data:{CurrencyID:CurrencyID,UsersID:UsersID,Closingdate:Closingdate,Status:Status},
        aysync: true,
        success: function(data11) {
			$('#crmdforecast1').html('');
            loadingUnload(".crmdforecast",0);
			var dataObj = JSON.parse(data11);
			 if(dataObj.status!='failed') {	
            if(dataObj.TotalWorth>0) {				
			var line_chart_demo_2 = $(".crmdforecast");
            var crmdforecastdata =  [];
			for(var s=0;s<dataObj.data.length;s++)			
			{
	              crmdforecastdata[s] =
					{
					 'ClosingDate': dataObj.data[s].ClosingDate,
					 'Worth': dataObj.data[s].TotalWorth,	
					 'CurrencyCode':dataObj.data[s].CurrencyCode,
					 'Opportunites':dataObj.data[s].Opportunites,
					 'StatusStr':dataObj.data[s].StatusStr
					 }
			}
			Morris.Bar({
			  element: 'crmdforecast1',
			  data: crmdforecastdata,
			  xkey: 'ClosingDate',
			  ykeys: ['Worth'],
			  barColors: ['#3399FF', '#333399'],
			  labels: [''],
				hoverCallback:function (index, options, content, row) {
					var ReturnStr = '<div class="morris-hover-row-label"><div class="morris-hover-row-label">Opportunities : '+row.Opportunites+'</div><div class="morris-hover-row-label">Total Worth : '+row.CurrencyCode+row.Worth+'</div>';
					if(row.StatusStr.length>0){
						for(var loop=0;loop<row.StatusStr.length;loop++){							
							ReturnStr += '<div class="crm_dashboard_'+row.StatusStr[loop].Status+' morris-hover-point">'+row.StatusStr[loop].Status +' : '+row.CurrencyCode+row.StatusStr[loop].worth+'</div>';							
						}
					}
					
					ReturnStr +='</div>';				
					return ReturnStr;
				}
			});	
			
			$('.forecastResult').html('<h3>'+dataObj.CurrencyCode+dataObj.TotalWorth + " Forecast"+"</h3>");
	           	}else{
                	$('.crmdforecast').html('<br><h3>NO DATA!!</h3>');
					$('.forecastResult').html('');
            	}
			 }else
			 {
					toastr.error(dataObj.message, "Error", toastr_opts);
			}
		}
    });
}

function GetUsersTasks(){
    var table = $('#UsersTasksTable');
    loadingUnload(table,1);
    var url = baseurl+'/dashboard/GetUsersTasks';
	var Tasktype = $('#TaskType').val();
	var UsersID  = $("#crm_dashboard [name='UsersID[]']").val();
    $.ajax({
        url: url,  //Server script to process data
        type: 'POST',
        dataType: 'json',
		data:{TaskTypeData:Tasktype,UsersID:UsersID}, 
        success: function (response) {
            var accounts 	= 	response.UserTasks;
            html 			= 	'';
            table.parents('.panel-primary').find('.panel-title h3').html('Active Tasks ('+accounts.length+')');
            table.find('tbody').html('');
            if(accounts.length > 0){
                for (i = 0; i < accounts.length; i++) {
                    html +='<tr>';
					if(accounts[i]["Priority"]==1){
						html +='      <td><div class="priority inlinetable">&nbsp;</div>  <div class="inlinetable">'+accounts[i]["Subject"]+'</div></td>';
					}else{
						html +='      <td><div class="normal inlinetable">&nbsp;</div> <div class="inlinetable">'+accounts[i]["Subject"]+'</div></td>';
					}
					
					if(accounts[i]["DueDate"]=='0000-00-00 00:00:00'){
						accounts[i]["DueDate"] = '';
					}
                    
					html +='<td>'+accounts[i]["Status"]+'</td>';
					html +='<td>'+accounts[i]["DueDate"]+'</td>';
					html +='<td>'+accounts[i]["Company"]+'</td>';
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