        var chart_type = '#destination';
        jQuery(document).ready(function ($) {
			$(document).on( "click",".available", function() {
				if($(this).hasClass('prev'))	{
					return false;
				}
				if($(this).hasClass('next'))	{
					return false;
				}
				console.log(this);
				$('.applyBtn').click();
				});
			$(document).on( "click","#taskGrid  tbody tr", function() {
				  $(this).find('.edit-deal').click();
				});
				
				$(document).on( "click","#opportunityGrid  tbody tr", function() {
				  $(this).find('.edit-deal').click();
				});
			
			        var opportunity = [
                'BoardColumnID',
                'BoardColumnName',
                'OpportunityID',
                'OpportunityName',
                'BackGroundColour',
                'TextColour',
                'Company',
                'Title',
                'FirstName',
                'LastName',
                'Owner',
                'UserID',
                'Phone',
                'Email',
                'BoardID',
                'AccountID',
                'Tags',
                'Rating',
                'TaggedUsers',
                'Status',
                'Worth',
                'OpportunityClosed',
                'ClosingDate',
                'ExpectedClosing'
            ];
			      var task = [
                'BoardColumnID',
                'BoardColumnName',
                'SetCompleted',
                'TaskID',
                'UsersIDs',
                'Users',
                'AccountIDs',
                'company',
                'Subject',
                'Description',
                'DueDate',
                'StartTime',
                'TaskStatus',
                'Priority',
                'PriorityText',
                'TaggedUsers',
                'BoardID',
                'userName',
                'taskClosed'
            ];
		   $("#crm_dashboard").submit(function(e) {
                e.preventDefault();
                public_vars.$body = $("body");
                //show_loading_bar(40);
			    set_search_parameter($("#crm_dashboard"));
				$searchFilter.UsersID = $("#crm_dashboard").find("[name='UsersID[]']").val();
    			$searchFilter.CurrencyID = $("#crm_dashboard").find("[name='CurrencyID']").val();
                reloadCrmCharts(pageSize,$searchFilter);
                return false;
            });
           
        var $searchFilter = {};
	   		$searchFilter.AccountOwner = $("#crm_dashboard select[name='UsersID[]']").val();
			$searchFilter.CurrencyID = $("#crm_dashboard select[name='CurrencyID']").val();
			console.log($searchFilter);
			//opportunites grid start
        data_table = $("#opportunityGrid").dataTable({
		        "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/crmdashboard/ajax_opportunity_grid",
                "fnServerParams": function (aoData) {
						$searchFilter.AccountOwner = $("#crm_dashboard select[name='UsersID[]']").val();
			$searchFilter.CurrencyID = $("#crm_dashboard select[name='CurrencyID']").val();
					$('.loaderopportunites').show();
                    aoData.push(
                            {"name": "AccountOwner","value": $searchFilter.AccountOwner},
                            {"name": "CurrencyID","value": $searchFilter.CurrencyID}
                    );
                },
                "iDisplayLength": pageSize,
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'desc']],
                "aoColumns": [
                    {
                        "bSortable": true, //Opportunity Name
                        mRender: function (id, type, full) {
                            return full[3];
                            //return '<div class="'+(full[13] == "1"?'priority':'normal')+' inlinetable">&nbsp;</div>'+'<div class="inlinetable">'+full[5]+'</div>';
                        }
                    },
                    {
                        "bSortable": true, //Status
                        mRender: function (id, type, full) {
                            return opportunitystatus[full[19]];
                        }
                    },
                    {
                        "bSortable": true, //Assign To
                        mRender: function (id, type, full) {
                            return full[10];
                        }
                    },
                    {
                        "bSortable": true, //Related To
                        mRender: function (id, type, full) {
                            return full[6];
                        }
                    },
                    {
                        "bSortable": true, //Expected Closing
                        mRender: function (id, type, full) {
                            return full[23];
                        }
                    },
                    {
                        "bSortable": true, //Value
                        mRender: function (id, type, full) {
                            return full[20];
                        }
                    },
                    {
                        "bSortable": true, //Rating
                        mRender: function (id, type, full) {
                            return '<input type="text" class="knob" data-min="0" data-max="5" data-width="40" data-height="40" name="Rating" value="'+full[17]+'" />';
                        }
                    },
                    {
                        "bSortable": false, //action
                        mRender: function (id, type, full) {
                            action = '<div class = "hiddenRowData" >';
                            for(var i = 0 ; i< opportunity.length; i++){
                                action += '<input type = "hidden"  name = "' + opportunity[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                            }
                            action += '</div>';
                           if(task_edit==1){
                            action += ' <button type="button" data-id="' + full[2] + '" class="edit-deal btn btn-default"><i class="entypo-pencil"></i></button>';
							}
							$('.loaderopportunites').hide();
                            return action;
                        }
                    }
                ],
                "oTableTools": {
                    "aButtons": [
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    if($('#tools .active').hasClass('grid')){
                        $('#opportunityGrid_wrapper').addClass('hidden');
                        $('#opportunityGrid').addClass('hidden');
                    }else{
                        $('#opportunityGrid_wrapper').removeClass('hidden');
                        $('#opportunityGrid').removeClass('hidden');
                    }
                    $('#opportunityGrid .knob').knob({"readOnly":true});
                }
		});
			//opportunites grid end
			
			///task grid start
			 data_table1 = $("#taskGrid").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/crmdashboard/ajax_task_grid",
                "fnServerParams": function (aoData) {
					$searchFilter.AccountOwner   = $("#crm_dashboard select[name='UsersID[]']").val();
					$searchFilter.DueDateFilter  = $("#DueDateFilter").val();
                    aoData.push(
                            {"name": "AccountOwner","value": $searchFilter.AccountOwner},
							{"name": "DueDateFilter","value": $searchFilter.DueDateFilter},
							{"name": "id","value": TaskBoardID},
                            {"name": "taskClosed","value": 0}
                    );
                },
                "iDisplayLength": pageSize,
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[1, 'desc']],
                "aoColumns": [
                    {
                        "bSortable": true, //Subject
                        mRender: function (id, type, full) {
                            return '<div class="'+(full[13] == "1"?'priority':'normal')+' inlinetable">&nbsp;</div>'+'<div class="inlinetable">'+full[8]+'</div>';
                        }
                    },
                    {
                        "bSortable": true, //Due Date
                        mRender: function (id, type, full) {
                            return full[10]!='0000-00-00'?full[10]:'';
                        }
                    },
                    {
                        "bSortable": true, //Status
                        mRender: function (id, type, full) {
                            return full[1];
                        }
                    },
                    {
                        "bSortable": true, //Assign To
                        mRender: function (id, type, full) {
                            return full[5];
                        }
                    },
                    {
                        "bSortable": true, //Related To
                        mRender: function (id, type, full) {
                            return full[7];
                        }
                    },
                    {
                        "bSortable": false, //action
                        mRender: function (id, type, full) {
                            action = '<div class = "hiddenRowData" >';
                            for(var i = 0 ; i< task.length; i++){
                                action += '<input type = "hidden"  name = "' + task[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                            }
                            action += '</div>';
                           if(task_edit==1){
                            action += ' <button type="button" data-id="' + full[2] + '" class="edit-deal btn btn-default"><i class="entypo-pencil"></i></button>'; }
                            return action;
                        }
                    }
                ],
                "oTableTools": {
                    "aButtons": [
                    ]
                },
                "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                    $('td:eq(0)', nRow).css('padding', '0');
                }
                ,
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $(this)
                    if($('#tools .active').hasClass('grid')){
                        $('#taskGrid_wrapper').addClass('hidden');
                        $('#taskGrid').addClass('hidden');
                    }else{
                        $('#taskGrid_wrapper').removeClass('hidden');
                        $('#taskGrid').removeClass('hidden');
                    }
                }

            });
			///task grid end
			
			 set_search_parameter($("#crm_dashboard"));
            Highcharts.theme = {
                colors: ['#3366cc', '#ff9900' ,'#dc3912' , '#109618', '#66aa00', '#dd4477','#0099c6', '#990099', '#143DFF']
            };
            // Apply the theme
            Highcharts.setOptions(Highcharts.theme);
            reloadCrmCharts(pageSize,$searchFilter);
			

   

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
	var UsersID  	= $("#crm_dashboard [name='UsersID[]']").val();
	var CurrencyID  = $("#crm_dashboard [name='CurrencyID']").val();
    $.ajax({
        type: 'POST',
        url: baseurl+'/dashboard/getpiplelinepata',
        dataType: 'html',
        data:{UsersID:UsersID,CurrencyID:CurrencyID},
        aysync: true,
        success: function(data1) {
			$('#crmdpipeline1').html('');
			
           loadingUnload(".crmdpipeline",0);
            if(data1!='') {	 
					
			var dataObj = JSON.parse(data1);
			var line_chart_demo_2 = $(".crmdpipeline");
            var crmpipelinedata =  [];
			if(dataObj.data.length>0){
			for(var s=0;s<dataObj.data.length;s++)			
			{
	              crmpipelinedata[s] =
					{
					 'Worth': dataObj.data[s].Worth,
					 'CurrencyCode':dataObj.data[s].CurrencyCode,
					 'User':dataObj.data[s].User					 
					 }
			}
			
			//////////////////////////////
			 var crmdSalesdata =  [];
			
			
			 $('#crmdpipeline1').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        categories:dataObj.users.split(','),
                        title: {
                            text: ""
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '',
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
					tooltip: {
						headerFormat: '<span style="color:{series.color};">&#9679;</span>&nbsp;<span style="padding:0">{point.key}:{point.y}</span>',
						pointFormat: '',
						footerFormat: '',
						shared: true,
						useHTML: true
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
                        floating: false,
                        borderWidth: 1,
                        backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                        shadow: false
                    },
                    credits: {
                        enabled: false
                    },
					series: [{
						showInLegend: false, 
						name:dataObj.users.split(','),
						data: dataObj.worth.split(',').map(parseFloat) 
			
					} ]
                });
			
			////////////////////////////////
			/*Morris.Bar({
			  element: 'crmdpipeline1',
			  data: crmpipelinedata,
			  xkey: 'User',
			  ykeys: ['Worth'],
			  barColors: ['#3399FF', '#333399'],
			  labels: [''],
				hoverCallback:function (index, options, content, row) {
					if(row.CurrencyCode==null){	row.CurrencyCode = '';	} 
					return '<div class="morris-hover-row-label">'+row.CurrencyCode+row.Worth+'</div>'
				}
			});	*/
			}else
			{
				$('.crmdpipeline').html('No Data');
			}
			$('.PipeLineResult').html('<div class="panel-title">'+dataObj.CurrencyCode+dataObj.TotalWorth + " Total Value  - "+dataObj.TotalOpportunites + "  Opportunities</div>");
           }else{
                $('.crmdpipeline').html('No Data');
            }
			
		}
    });
	}
function reloadCrmCharts(pageSize,$searchFilter){
    /* get destination data for today and display in pie three chart*/
        getPipleLineData($searchFilter.chart_type,$searchFilter);
  /* get data by time in bar chart*/
    //getSales($searchFilter.chart_type,$searchFilter);
	 GetUsersTasks();
     GetSalesData();
	 GetForecastData();
	 data_table.fnFilter('',0);
}


////////////////////////////////////////crm page code///////////////////////////////////////////////////////////////////////////////
 var $searchFilter = {};
  
  
$(function() {   
	 
	  $("#DueDateFilter").change(function(){
       data_table1.fnFilter('',0);
    });
	
	$('#crm_dashboard_Sales').submit(function(e) {
		e.stopImmediatePropagation();
		e.preventDefault();
        GetSalesData();         
    });
	$('#crm_dashboard_Forecast').submit(function(e) {
		e.stopImmediatePropagation();
		e.preventDefault();
        GetForecastData();         
    });
	
	$('#dateendid').change(function(e) {
		e.stopImmediatePropagation();
		e.preventDefault();
     	//GetSalesData();
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
	var submit_form = $("#crm_dashboard");
	 set_search_parameter(submit_form);
    if(id=='UsersTasks'){
      //  GetUsersTasks();
	  data_table1.fnFilter('',0);
	  return false;
    }
	if(id=='Pipeline'){
        getPipleLineData('',$searchFilter);
		return false;
    }
	
	if(id=='Sales'){
        GetSalesData();
		return false;
    }
	
	if(id=='Forecast'){
        GetForecastData();
		return false;
    }
	
	if(id='UsersOpportunities'){
		$('.loaderopportunites').show();
		data_table.fnFilter('',0);
		return false;
	}
	
});



function GetForecastData(){
	
	//////////////////////////////////
	loadingUnload(".crmdForecast",1);	 
	var UsersID  	= $("#crm_dashboard [name='UsersID[]']").val();
	var CurrencyID  = $("#crm_dashboard [name='CurrencyID']").val();
	var Closingdate   = $("#crm_dashboard_Forecast [name='Closingdate']").val();

    $.ajax({
        type: 'POST',
        url: baseurl+'/dashboard/GetForecastData',
        dataType: 'html',
        data:{CurrencyID:CurrencyID,UsersID:UsersID,Closingdate:Closingdate},
        aysync: true,
        success: function(data11) {
			$('#crmdForecast1').html('');
            loadingUnload(".crmdForecast",0);
			var dataObj = JSON.parse(data11);
			 if(dataObj.status!='failed') {	
            if(dataObj.count>0) {				
			 var crmdForecastdata =  [];
			for(var s=0;s<dataObj.data.length;s++)			
			{
	             	 crmdForecastdata[s] =
					{
					showInLegend: false, 
					 'name': dataObj.data[s].user,
					 'data': dataObj.data[s].worth.split(',').map(parseFloat) 
					 };
			}
			
			 $('#crmdForecast1').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        categories: dataObj.dates.split(','),
                        title: {
                            text: ""
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '',
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
					
                    series: crmdForecastdata
                });
			
			$('.ForecastResult').html('<div class="panel-title">'+dataObj.CurrencyCode+dataObj.TotalWorth + " Total value - "+dataObj.TotalOpportunites+" Opportunities</div>");
	           	}else{
                	$('.crmdForecast').html('<br><h4>No Data</h4>');
					$('.ForecastResult').html('');
            	}
			 }else
			 {
					toastr.error(dataObj.message, "Error", toastr_opts);
			}
		}
    });
}

function GetSalesData(){
	
	//////////////////////////////////
	loadingUnload(".crmdSales",1);	 
	var UsersID  	= $("#crm_dashboard [name='UsersID[]']").val();
	var CurrencyID  = $("#crm_dashboard [name='CurrencyID']").val();
	var Closingdate   = $("#crm_dashboard_Sales [name='Closingdate']").val();
	//var Status   	= $("#crm_dashboard_Sales input.statusCheckbox:checked").map(function() {  return this.value; }).get().join();
	var Status   	= $("#crm_dashboard_Sales select[name='Status[]']").val();

    $.ajax({
        type: 'POST',
        url: baseurl+'/dashboard/getSalesdata',
        dataType: 'html',
        data:{CurrencyID:CurrencyID,UsersID:UsersID,Closingdate:Closingdate,Status:Status},
        aysync: true,
        success: function(data11) {
			$('#crmdSales1').html('');
            loadingUnload(".crmdSales",0);
			var dataObj = JSON.parse(data11);
			 if(dataObj.status!='failed') {	
            if(dataObj.count>0) {				
			var line_chart_demo_2 = $(".crmdSales");
           
			 var crmdSalesdata =  [];
			for(var s=0;s<dataObj.data.length;s++)			
			{
	             	 crmdSalesdata[s] =
					{
						showInLegend: false, 
					 'name': dataObj.data[s].user,
					 'data': dataObj.data[s].worth.split(',').map(parseFloat) 
					 };
			}
			
			 $('#crmdSales1').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        categories: dataObj.dates.split(','),
                        title: {
                            text: ""
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '',
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
                        floating: false,
                        borderWidth: 1,
                        backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                        shadow: false
                    },
                    credits: {
                        enabled: false
                    },
					
                    series: crmdSalesdata
                });
			
			$('.SalesResult').html('<div class="panel-title">'+dataObj.CurrencyCode+dataObj.TotalWorth + " Total Sales - "+dataObj.TotalOpportunites+" Opportunities</div>");
	           	}else{
                	$('.crmdSales').html('<br><h4>No Data</h4>');
					$('.SalesResult').html('');
            	}
			 }else
			 {
					toastr.error(dataObj.message, "Error", toastr_opts);
			}
		}
    });
}

function GetUsersTasks(){
	data_table1.fnFilter('',0);
}


//opportunity edit start
            $(document).on('click','#board-start ul.sortable-list li button.edit-deal,#opportunityGrid .edit-deal',function(e){
				 if(Opportunity_edit==1){
	            e.stopPropagation();
                if($(this).is('button')){
                    var rowHidden = $(this).prev('div.hiddenRowData');
                }else {
                    var rowHidden = $(this).parents('.tile-stats').children('div.row-hidden');
                }
                var select = ['UserID','BoardID','TaggedUsers','Title','Status'];
                var color = ['BackGroundColour','TextColour'];
                var OpportunityClosed = 0;
                $('.closedDate').addClass('hidden');
                $('#closedDate').text('');
                for(var i = 0 ; i< opportunity.length; i++){
                    var val = rowHidden.find('input[name="'+opportunity[i]+'"]').val();
                    var elem = $('#edit-opportunity-form [name="'+opportunity[i]+'"]');
                    //console.log(opportunity[i]+' '+val);
                    if(select.indexOf(opportunity[i])!=-1){
                        if(opportunity[i]=='TaggedUsers'){
                            var taggedUsers = rowHidden.find('[name="TaggedUsers"]').val();
                            $('#edit-opportunity-form [name="TaggedUsers[]"]').select2('val', taggedUsers.split(','));
                        }else {
                            elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption(val);
                        }
                    } else{
                        elem.val(val);
                        if(color.indexOf(opportunity[i])!=-1){
                        }else if(opportunity[i]=='Rating'){
                            elem.val(val).trigger('change');
                        }else if(opportunity[i]=='Tags'){
                            elem.val(val).trigger("change");
                        }else if(opportunity[i]=='OpportunityClosed'){
                            if(val==1){
                                OpportunityClosed = 1;
                                biuldSwicth('.make','#edit-opportunity-form','checked');
                            }else{
                                biuldSwicth('.make','#edit-opportunity-form','');
                            }
                        }else if(opportunity[i]=='ClosingDate'){
                            if(OpportunityClosed==1){
                                $('.closedDate').removeClass('hidden');
                                $('#closedDate').text(val);
                            }
                        }
                        else if(opportunity[i]=='ExpectedClosing'){
                            if(val=='0000-00-00'){
                                elem.val('');
                            }
                        }
                    }
                }
                $('#edit-modal-opportunity h4').text('Edit Opportunity');
                $('#edit-modal-opportunity').modal('show');
             }
            });
          
/// opportunity edit end
//task edit start
 $(document).on('click','#board-start ul.sortable-list li button.edit-deal,#taskGrid .edit-deal',function(e){
                e.stopPropagation();
                if($(this).is('button')){
                    var rowHidden = $(this).prev('div.hiddenRowData');
                }else {
                    var rowHidden = $(this).parents('.tile-stats').children('div.row-hidden');
                }
                var select = ['UsersIDs','AccountIDs','TaskStatus','TaggedUsers'];
                var color = ['BackGroundColour','TextColour'];
                for(var i = 0 ; i< task.length; i++){
                    var val = rowHidden.find('input[name="'+task[i]+'"]').val();
                    var elem = $('#edit-task-form [name="'+task[i]+'"]');
                    if(select.indexOf(task[i])!=-1){
                        if(task[i]=='TaggedUsers') {
                            $('#edit-task-form [name="' + task[i] + '[]"]').select2('val', val.split(','));
                        }else {
                            elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption(val);
                        }
                    } else{
                        elem.val(val);
                        if(color.indexOf(task[i])!=-1){
                            /*setcolor(elem,val);*/
                        }else if(task[i]=='Tags'){
                            elem.val(val).trigger("change");
                        }else if(task[i]=='Priority'){
                            if(val==1) {
                                biuldSwicth('.make','Priority','#edit-modal-task','checked');
                            }else{
                                biuldSwicth('.make','Priority','#edit-modal-task','');
                            }
                        }else if(task[i]=='taskClosed'){
                            if(val==1) {
                                biuldSwicth('.taskClosed','taskClosed','#edit-modal-task','checked');
                            }else{
                                biuldSwicth('.taskClosed','taskClosed','#edit-modal-task','');
                            }
                        }else if(task[i]=='DueDate' || task[i]=='StartTime'){
                            if(val=='0000-00-00' || val=='00:00:00'){
                                elem.val('');
                            }
                        }
                    }
                }
                $('#edit-modal-task h4').text('Edit Task');
                $('#edit-modal-task').modal('show');
            });
// task edit end
// opp edit submit form start
        $('#edit-opportunity-form').submit(function (e) {
            e.preventDefault();
            var update_new_url = '';
            var formid = $(this).attr('id');
            var opportunityID = $('#' + formid).find('[name="OpportunityID"]').val();

            if (opportunityID) {
                update_new_url = baseurl + '/opportunity/' + opportunityID + '/update';
            } else {
                update_new_url = baseurl + '/opportunity/create';
            }
            $('#' + formid).find('[name="AccountID"]').prop('disabled', false);
            $('#' + formid).find('[name="UserID"]').prop('disabled', false);
            var formData = new FormData($('#' + formid)[0]);
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#edit-modal-opportunity').modal('hide');                       
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
					data_table.fnFilter('',0);
                    $("#opportunity-update").button('reset');                   
                  
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });
// opp edit submit form end

// task edit submit form start
 $('#edit-task-form').submit(function(e){
            e.preventDefault();
            var update_new_url = '';
            var formid = $(this).attr('id');
            var taskID = $('#'+formid).find('[name="TaskID"]').val();
            if(taskID){
                update_new_url = baseurl + '/task/'+taskID+'/update';
            }else{
                update_new_url = baseurl + '/task/create';
            }
            var formData = new FormData($('#'+formid)[0]);
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#edit-modal-task').modal('hide');
                        
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
					data_table1.fnFilter('',0);
                    $("#task-update").button('reset');     
                    //getOpportunities();
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });
// task edit submit form end



   			function biuldSwicth(container,formID,checked){
                var make = '<span class="make-switch switch-small">';
                make += '<input name="opportunityClosed" value="{{Opportunity::Close}}" '+checked+' type="checkbox">';
                make +='</span>';

                var container = $(formID).find(container);
                container.empty();
                container.html(make);
                container.find('.make-switch').bootstrapSwitch();
            }
});

function set_search_parameter(submit_form){
	 var $searchFilter = {};
	$searchFilter.UsersID = $(submit_form).find("[name='UsersID[]']").val();
    $searchFilter.CurrencyID = $(submit_form).find("[name='CurrencyID']").val();
}