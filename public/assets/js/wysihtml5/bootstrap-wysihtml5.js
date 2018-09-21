!function($, wysi) {
    "use strict";
	var option = '<img width="100" src="http://placehold.it/50/B9E4FB/260b50" class="span1">';
	var image = "<tbody><tr><td><a data-wysihtml5-command-value='"+option+"'>"+option+"</a></td><td><a data-wysihtml5-command-value='"+option+"'>"+option+"</a></td></tr></tbody>";
    var tpl = {
        "leadoptions": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown leadoptions'>" +
                "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.leadoptions.FirstName + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
                "<ul class='dropdown-menu'>" +
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Account Fields</b></a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{FirstName}}'>" + locale.leadoptions.FirstName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.leadoptions.LastName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Email}}'>" + locale.leadoptions.Email + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address1}}'>" + locale.leadoptions.Address1 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address2}}'>" + locale.leadoptions.Address2 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address3}}'>" + locale.leadoptions.Address3 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{City}}'>" + locale.leadoptions.City + "</a></li>" +                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PostCode}}'>" + locale.leadoptions.PostCode + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Country}}'>" + locale.leadoptions.Country + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Currency}}'>" + locale.leadoptions.Currency+ "</a></li>" +
                //"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{OutstandingExcludeUnbilledAmount}}'>" + locale.leadoptions.OutstandingExcludeUnbilledAmount + "</a></li>" +
                //"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{OutstandingIncludeUnbilledAmount}}'>" + locale.leadoptions.OutstandingIncludeUnbilledAmount + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{BalanceThreshold}}'>" + locale.leadoptions.BalanceThreshold + "</a></li>" +
               		"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Company Fields</b></a></li>" +			
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.Company.CompanyName + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyVAT}}'>" + locale.Company.Vat + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress1}}'>" + locale.Company.Address1 + "</a></li>" +
 		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress2}}'>" + locale.Company.Address2 + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress3}}'>" + locale.Company.Address3+ "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCity}}'>" + locale.Company.City + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyPostCode}}'>" + locale.Company.PostCode + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCountry}}'>" + locale.Company.Country + "</a></li>" +		 
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Logo}}'>" + locale.Company.Logo + "</a></li>"+		   	
	 	 "<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Others</b></a></li>" +
         "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Signature}}'>" + locale.leadoptions.Signature + "</a></li>" +
         "</ul>" +
         "</li>";
        },
        "invoiceoptions": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown invoiceoptions'>" +
                "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.invoiceoptions.FirstName + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
                "<ul class='dropdown-menu'>" +
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Account Fields</b></a></li>" +
			   "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{AccountName}}'>"+locale.invoiceoptions.AccountName +"</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{FirstName}}'>"+locale.invoiceoptions.FirstName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.invoiceoptions.LastName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Email}}'>" + locale.invoiceoptions.Email + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address1}}'>" + locale.invoiceoptions.Address1 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address2}}'>" + locale.invoiceoptions.Address2 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address3}}'>" + locale.invoiceoptions.Address3 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{City}}'>" + locale.invoiceoptions.City + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PostCode}}'>" + locale.invoiceoptions.PostCode + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Country}}'>" + locale.invoiceoptions.Country + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Currency}}'>" + locale.invoiceoptions.Currency + "</a></li>" +
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Invoice Fields</b></a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{InvoiceNumber}}'>" + locale.invoiceoptions.InvoiceNumber + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{InvoiceGrandTotal}}'>" + locale.invoiceoptions.InvoiceGrandTotal + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{InvoiceOutstanding}}'>" + locale.invoiceoptions.InvoiceOutstanding + "</a></li>" +
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{InvoiceLink}}'>" + locale.invoiceoptions.InvoiceLink + "</a></li>" +	
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PeriodFrom}}'>" + locale.invoiceoptions.PeriodFrom + "</a></li>" +
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PeriodTo}}'>" + locale.invoiceoptions.PeriodTo + "</a></li>" +
               /* "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{OutstandingExcludeUnbilledAmount}}'>" + locale.invoiceoptions.OutstandingExcludeUnbilledAmount + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{OutstandingIncludeUnbilledAmount}}'>" + locale.invoiceoptions.OutstandingIncludeUnbilledAmount + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{BalanceThreshold}}'>" + locale.invoiceoptions.BalanceThreshold + "</a></li>" +*/
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Company Fields</b></a></li>" +			
			 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.Company.CompanyName + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyVAT}}'>" + locale.Company.Vat + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress1}}'>" + locale.Company.Address1 + "</a></li>" +
 		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress2}}'>" + locale.Company.Address2 + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress3}}'>" + locale.Company.Address3+ "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCity}}'>" + locale.Company.City + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyPostCode}}'>" + locale.Company.PostCode + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCountry}}'>" + locale.Company.Country + "</a></li>" +
		    "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Logo}}'>" + locale.Company.Logo + "</a></li>"+		   		 	
				 	"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Others</b></a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Signature}}'>" + locale.invoiceoptions.Signature + "</a></li>" +
				"</ul>" +
                "</li>";
        }, 
		"tasks": function(locale, options) {			
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown opportunity'>" +
                "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.tasks.Subject + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
                "<ul class='dropdown-menu'>" +
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Account Fields</b></a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{FirstName}}'>" + locale.leadoptions.FirstName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.leadoptions.LastName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Email}}'>" + locale.leadoptions.Email + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address1}}'>" + locale.leadoptions.Address1 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address2}}'>" + locale.leadoptions.Address2 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address3}}'>" + locale.leadoptions.Address3 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{City}}'>" + locale.leadoptions.City + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PostCode}}'>" + locale.leadoptions.PostCode + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Country}}'>" + locale.leadoptions.Country + "</a></li>" +
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Tasks Fields</b></a></li>" +            
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{subject}}'>" + locale.tasks.Subject + "</a></li>" +
				   "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{User}}'>" + locale.tasks.User + "</a></li>"+
				   "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Comment}}'>" + locale.tasks.Comment + "</a></li>"+			 
						"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Company Fields</b></a></li>" +			
			 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.Company.CompanyName + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyVAT}}'>" + locale.Company.Vat + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress1}}'>" + locale.Company.Address1 + "</a></li>" +
 		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress2}}'>" + locale.Company.Address2 + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress3}}'>" + locale.Company.Address3+ "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCity}}'>" + locale.Company.City + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyPostCode}}'>" + locale.Company.PostCode + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCountry}}'>" + locale.Company.Country + "</a></li>" +
		   "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Logo}}'>" + locale.Company.Logo + "</a></li>"+		   
		        "</ul>" +
                "</li>";
        },
			"opportunities": function(locale, options) {			
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown opportunity'>" +
                "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.opportunities.Subject + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
                "<ul class='dropdown-menu'>" +
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Account Fields</b></a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{FirstName}}'>" + locale.leadoptions.FirstName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.leadoptions.LastName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Email}}'>" + locale.leadoptions.Email + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address1}}'>" + locale.leadoptions.Address1 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address2}}'>" + locale.leadoptions.Address2 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address3}}'>" + locale.leadoptions.Address3 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{City}}'>" + locale.leadoptions.City + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PostCode}}'>" + locale.leadoptions.PostCode + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Country}}'>" + locale.leadoptions.Country + "</a></li>" +
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Opportunity Fields</b></a></li>" +            
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{subject}}'>" + locale.opportunities.Subject + "</a></li>" +
				   "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{User}}'>" + locale.opportunities.User + "</a></li>"+
				   "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Comment}}'>" + locale.opportunities.Comment + "</a></li>"+
				   "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Logo}}'>" + locale.opportunities.Logo + "</a></li>"+
						"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Company Fields</b></a></li>" +			
			 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.Company.CompanyName + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyVAT}}'>" + locale.Company.Vat + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress1}}'>" + locale.Company.Address1 + "</a></li>" +
 		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress2}}'>" + locale.Company.Address2 + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress3}}'>" + locale.Company.Address3+ "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCity}}'>" + locale.Company.City + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyPostCode}}'>" + locale.Company.PostCode + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCountry}}'>" + locale.Company.Country + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Logo}}'>" + locale.Company.Logo + "</a></li>"+		   	   
		        "</ul>" +
                "</li>";
        },
		  "ratesheetoptions":function(locale, options){		   
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown estimateoptions'>" +
                "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.ratesheetoptions.FirstName + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
                "<ul class='dropdown-menu'>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{FirstName}}'>" + locale.ratesheetoptions.FirstName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.ratesheetoptions.LastName + "</a></li>" +
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.ratesheetoptions.RateGeneratorName + "</a></li>" +				
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{RateTableName}}'>" + locale.ratesheetoptions.RateTableName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{EffectiveDate}}'>" + locale.ratesheetoptions.EffectiveDate + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.ratesheetoptions.CompanyName + "</a></li>" +		 "</ul></li>";  
		 
        
		   },
		  "estimateoptions": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown estimateoptions'>" +
                "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.estimateoptions.FirstName + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
                "<ul class='dropdown-menu'>" +
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Account Fields</b></a></li>" +
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{AccountName}}'>" + locale.estimateoptions.AccountName + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{FirstName}}'>" + locale.estimateoptions.FirstName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.estimateoptions.LastName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Email}}'>" + locale.estimateoptions.Email + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address1}}'>" + locale.estimateoptions.Address1 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address2}}'>" + locale.estimateoptions.Address2 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address3}}'>" + locale.estimateoptions.Address3 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{City}}'>" + locale.estimateoptions.City + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PostCode}}'>" + locale.estimateoptions.PostCode + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Country}}'>" + locale.estimateoptions.Country + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Currency}}'>" + locale.estimateoptions.Currency + "</a></li>" +
              "<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Estimate Fields</b></a></li>" +
			    "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{EstimateNumber}}'>" + locale.estimateoptions.EstimateNumber+ "</a></li>"+	   "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{EstimateGrandTotal}}'>" + locale.estimateoptions.EstimateGrandTotal+ "</a></li>"+ 
		"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{EstimateLink}}'>" + locale.estimateoptions.EstimateLink + "</a></li>"+
		"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Comment}}'>" + locale.estimateoptions.Comment + "</a></li>"+
		"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{User}}'>" + locale.estimateoptions.User + "</a></li>"+		 		
		
		"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Company Fields</b></a></li>" +			
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.Company.CompanyName + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyVAT}}'>" + locale.Company.Vat + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress1}}'>" + locale.Company.Address1 + "</a></li>" +
 		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress2}}'>" + locale.Company.Address2 + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress3}}'>" + locale.Company.Address3+ "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCity}}'>" + locale.Company.City + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyPostCode}}'>" + locale.Company.PostCode + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCountry}}'>" + locale.Company.Country + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Logo}}'>" + locale.Company.Logo + "</a></li>"+		   		 	
		 "<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Others</b></a></li>" +
		  "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Signature}}'>" + locale.estimateoptions.Signature + "</a></li>" +
		 "</ul></li>";  
		 
        },
        "font-styles": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown font-styles'>" +
              "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
              "<i class='icon-font'></i>&nbsp;<span class='current-font'>" + locale.font_styles.normal + "</span>&nbsp;<b class='caret'></b>" +
              "</a>" +
              "<ul class='dropdown-menu'>" +
                "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='div' tabindex='-1'>" + locale.font_styles.normal + "</a></li>" +
                "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='h1' tabindex='-1'>" + locale.font_styles.h1 + "</a></li>" +
                "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='h2' tabindex='-1'>" + locale.font_styles.h2 + "</a></li>" +
                "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='h3' tabindex='-1'>" + locale.font_styles.h3 + "</a></li>" +
                "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='h4'>" + locale.font_styles.h4 + "</a></li>" +
                "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='h5'>" + locale.font_styles.h5 + "</a></li>" +
                "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='h6'>" + locale.font_styles.h6 + "</a></li>" +
              "</ul>" +
            "</li>";
        },
		  "Crm": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown Crm'>" +
              "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.Crm.FirstName + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
              "<ul class='dropdown-menu'>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{FirstName}}'>" + locale.Crm.FirstName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.Crm.LastName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Email}}'>" + locale.Crm.Email + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address1}}'>" + locale.Crm.Address1 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address2}}'>" + locale.Crm.Address2 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address3}}'>" + locale.Crm.Address3 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{City}}'>" + locale.Crm.City + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PostCode}}'>" + locale.Crm.PostCode + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Country}}'>" + locale.Crm.Country + "</a></li>" +  
               	"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Company Fields</b></a></li>" +			
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.Company.CompanyName + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyVAT}}'>" + locale.Company.Vat + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress1}}'>" + locale.Company.Address1 + "</a></li>" +
 		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress2}}'>" + locale.Company.Address2 + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress3}}'>" + locale.Company.Address3+ "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCity}}'>" + locale.Company.City + "</a></li>" +
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyPostCode}}'>" + locale.Company.PostCode + "</a></li>" +	
		 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCountry}}'>" + locale.Company.Country + "</a></li>" +		
		    "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Logo}}'>" + locale.Company.Logo + "</a></li>"+		    	
		 "<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Others</b></a></li>" +
		  "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Signature}}'>" + locale.Crm.Signature + "</a></li>" +
              "</ul>" +
            "</li>";
        },
		"TicketsSingle":function(locale, options){
			
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown TicketsSingle'>" +
              "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.TicketsSingle.Subject + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
              "<ul class='dropdown-menu'>" +
			  "<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Ticket Fields</b></a></li>" +
                 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Subject}}'>" + locale.TicketsSingle.Subject + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{TicketID}}'>" + locale.TicketsSingle.TicketID + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Requester}}'>" + locale.TicketsSingle.Requester + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{RequesterName}}'>" + locale.TicketsSingle.RequesterName + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Status}}'>" + locale.TicketsSingle.Status + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Priority}}'>" + locale.TicketsSingle.Priority + "</a></li>"+				
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Description}}'>" + locale.TicketsSingle.Description + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Group}}'>" + locale.TicketsSingle.Group + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Type}}'>" + locale.TicketsSingle.Type + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Date}}'>" + locale.TicketsSingle.Date + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{AgentName}}'>" + locale.TicketsSingle.AgentName + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{AgentEmail}}'>" + locale.TicketsSingle.AgentEmail+ "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{TicketUrl}}'>" + locale.TicketsSingle.TicketUrl+ "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{TicketCustomerUrl}}'>" + locale.TicketsSingle.TicketCustomerUrl+ "</a></li>"+	
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Comment}}'>" + locale.TicketsSingle.Comment+ "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Notebody}}'>" + locale.TicketsSingle.Notebody+ "</a></li>"+				
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{NoteUser}}'>" + locale.TicketsSingle.NoteUser+ "</a></li>"+				
               	"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Company Fields</b></a></li>" +			
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.Company.CompanyName + "</a></li>" +
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyVAT}}'>" + locale.Company.Vat + "</a></li>" +	
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress1}}'>" + locale.Company.Address1 + "</a></li>" +
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress2}}'>" + locale.Company.Address2 + "</a></li>" +
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyAddress3}}'>" + locale.Company.Address3+ "</a></li>" +	
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCity}}'>" + locale.Company.City + "</a></li>" +
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyPostCode}}'>" + locale.Company.PostCode + "</a></li>" +	
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyCountry}}'>" + locale.Company.Country + "</a></li>" +	
			    "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Logo}}'>" + locale.Company.Logo + "</a></li>"+		   	              "</ul>" +
            "</li>";        
		},
		"Cronjobs":function(locale, options){
			
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown Cronjobs'>" +
              "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.Cronjobs.KillCommand + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
              "<ul class='dropdown-menu'>" +
                 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{KillCommand}}'>" + locale.Cronjobs.KillCommand + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{ReturnStatus}}'>" + locale.Cronjobs.ReturnStatus + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{DetailOutput}}'>" + locale.Cronjobs.DetailOutput + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Minute}}'>" + locale.Cronjobs.Minute + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{JobTitle}}'>" + locale.Cronjobs.JobTitle + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PID}}'>" + locale.Cronjobs.PID + "</a></li>"+				
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{CompanyName}}'>" + locale.Cronjobs.CompanyName + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Url}}'>" + locale.Cronjobs.Url + "</a></li>"+			
              "</ul>" +
            "</li>";        
		},
		"Tickets":function(locale, options){			
            var size = (options && options.size) ? ' btn-'+options.size : '';			var tickets_data  = '';
			if(tickets_enable == 1){
				tickets_data  = "<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Ticket Fields</b></a></li>"+			                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Subject}}'>" + locale.Tickets.Subject + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{TicketID}}'>" + locale.Tickets.TicketID + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Requester}}'>" + locale.Tickets.Requester + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{RequesterName}}'>" + locale.Tickets.RequesterName + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Status}}'>" + locale.Tickets.Status + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Priority}}'>" + locale.Tickets.Priority + "</a></li>"+				
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Description}}'>" + locale.Tickets.Description + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Group}}'>" + locale.Tickets.Group + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Type}}'>" + locale.Tickets.Type + "</a></li>"+
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Date}}'>" + locale.Tickets.Date + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{AgentName}}'>" + locale.Tickets.AgentName + "</a></li>"+
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{AgentEmail}}'>" + locale.Tickets.AgentEmail+ "</a></li>"				
				;
			}

            return "<li class='dropdown Tickets'>" +
              "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#'>" +
                "<i class='icon-font'></i>&nbsp;<span class='current-option'>" + locale.Tickets.Subject + "</span>&nbsp;<b class='caret'></b>" +
                "</a>" +
              "<ul class='TicketsScroll dropdown-menu'>" +tickets_data+			  
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Account Fields</b></a></li>" +
				 "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{FirstName}}'>" + locale.Tickets.FirstName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{LastName}}'>" + locale.Tickets.LastName + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Email}}'>" + locale.Tickets.Email + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address1}}'>" + locale.Tickets.Address1 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address2}}'>" + locale.Tickets.Address2 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Address3}}'>" + locale.Tickets.Address3 + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{City}}'>" + locale.Tickets.City + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{PostCode}}'>" + locale.Tickets.PostCode + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Country}}'>" + locale.Tickets.Country + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Currency}}'>" + locale.Tickets.Currency+ "</a></li>" +
                //"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{OutstandingExcludeUnbilledAmount}}'>" + locale.Tickets.OutstandingExcludeUnbilledAmount + "</a></li>" +
                //"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{OutstandingIncludeUnbilledAmount}}'>" + locale.Tickets.OutstandingIncludeUnbilledAmount + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{BalanceThreshold}}'>" + locale.Tickets.BalanceThreshold + "</a></li>" +              
				"<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Invoice Fields</b></a></li>" +
	                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{InvoiceNumber}}'>" + locale.Tickets.InvoiceNumber + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{InvoiceGrandTotal}}'>" + locale.Tickets.InvoiceGrandTotal + "</a></li>" +
                "<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{InvoiceOutstanding}}'>" + locale.Tickets.InvoiceOutstanding + "</a></li>" +   "<li class='unclick'><a data-wysihtml5-command='' data-wysihtml5-command-value=''><b>Others</b></a></li>" +           
				"<li><a data-wysihtml5-command='insertHTML' data-wysihtml5-command-value='{{Signature}}'>" + locale.Tickets.Signature + "</a></li>" +
              "</ul>" +
            "</li>";
        
		},
        "emphasis": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li>" +
              "<div class='btn-group'>" +
                "<a class='btn btn-white" + size + "' data-wysihtml5-command='bold' title='CTRL+B' tabindex='-1'>" + locale.emphasis.bold + "</a>" +
                "<a class='btn btn-white" + size + "' data-wysihtml5-command='italic' title='CTRL+I' tabindex='-1'>" + locale.emphasis.italic + "</a>" +
                "<a class='btn btn-white" + size + "' data-wysihtml5-command='underline' title='CTRL+U' tabindex='-1'>" + locale.emphasis.underline + "</a>" +
              "</div>" +
            "</li>";
        },

        "lists": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li>" +
              "<div class='btn-group'>" +
                "<a class='btn btn-white" + size + "' data-wysihtml5-command='insertUnorderedList' title='" + locale.lists.unordered + "' tabindex='-1'><i class='glyphicon glyphicon-list'></i></a>" +
                "<a class='btn btn-white" + size + "' data-wysihtml5-command='insertOrderedList' title='" + locale.lists.ordered + "' tabindex='-1'><i class='glyphicon glyphicon-th-list'></i></a>" +
                "<a class='btn btn-white" + size + "' data-wysihtml5-command='Outdent' title='" + locale.lists.outdent + "' tabindex='-1'><i class='glyphicon glyphicon-indent-right'></i></a>" +
                "<a class='btn btn-white" + size + "' data-wysihtml5-command='Indent' title='" + locale.lists.indent + "' tabindex='-1'><i class='glyphicon glyphicon-indent-left'></i></a>" +
              "</div>" +
            "</li>";
        },

        "link": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li>" +
              "<div class='bootstrap-wysihtml5-insert-link-modal modal fade'>" +
               "<div class='modal-dialog'><div class='modal-content'>" +
                "<div class='modal-header'>" +
                  "<a class='close' data-dismiss='modal'>&times;</a>" +
                  "<h4 class='modal-title'>" + locale.link.insert + "</h4>" +
                "</div>" +
                "<div class='modal-body'>" +
                  "<input value='http://' class='bootstrap-wysihtml5-insert-link-url form-control'>" +
                  "<label class='checkbox'><input type='checkbox' class='bootstrap-wysihtml5-insert-link-target' checked>" + locale.link.target + "</label>" +
                "</div>" +
                "<div class='modal-footer'>" +
                  "<a href='#' class='btn btn-default' data-dismiss='modal'>" + locale.link.cancel + "</a>" +
                  "<a href='#' class='btn btn-primary' data-dismiss='modal'>" + locale.link.insert + "</a>" +
                "</div>" +
               "</div></div>" +
              "</div>" +
              "<a class='btn btn-white" + size + "' data-wysihtml5-command='createLink' title='" + locale.link.insert + "' tabindex='-1'><i class='entypo-link'></i></a>" +

            "</li>";
        },

        "image": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li>" +
              "<div class='bootstrap-wysihtml5-insert-image-modal modal fade'>" +
               "<div class='modal-dialog'><div class='modal-content'>" +
                "<div class='modal-header'>" +
                  "<a class='close' data-dismiss='modal'>&times;</a>" +
                  "<h3>" + locale.image.insert + "</h3>" +
                "</div>" +
                "<div class='modal-body'>" +
                  //"<input value='http://' class='bootstrap-wysihtml5-insert-image-url form-control'>" +
					'<table class="table table-bordered datatable bootstrap-wysihtml5-insert-image-url hidden">'+
					'<thead>'+
                    '   <tr>'+
                    '    	<th>File</th>'+
                    '   </tr>'+
                    '</thead>'+
					'<tbody>'+
						//image +
					'</tbody>'+
					'</table>'+
					'<br>'+
					'<div class="row">'+
					'	<form role="form" id="bootstrap-wysihtml5-file-upload" name="bootstrap-wysihtml5-file-upload" method="post" action="" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">' +
					'		<input name="file" type="file">' +
					'	</form>' +
					"</div>"+
                "</div>" +
                "<div class='modal-footer'>" +
                  "<a href='#' class='btn btn-default' data-dismiss='modal'>" + locale.image.cancel + "</a>" +
                  "<a href='#' class='btn btn-primary' data-dismiss='modal'>" + locale.image.insert + "</a>" +
                "</div>" +
                "</div></div>" +
              "</div>" +
              "<a class='btn btn-white" + size + "' data-wysihtml5-command='insertImage' title='" + locale.image.insert + "' tabindex='-1'><i class='entypo-picture'></i></a>" +
            "</li>";
        },

        "html": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='pull-right'>" +
              "<div class='btn-group'>" +
                "<a class='btn btn-white" + size + "' data-wysihtml5-action='change_view' title='" + locale.html.edit + "' tabindex='-1'><i class='entypo-code'></i></a>" +
              "</div>" +
            "</li>";
        },

        "color": function(locale, options) {
            var size = (options && options.size) ? ' btn-'+options.size : '';
            return "<li class='dropdown'>" +
              "<a class='btn btn-white dropdown-toggle" + size + "' data-toggle='dropdown' href='#' tabindex='-1'>" +
                "<span class='current-color'>" + locale.colours.black + "</span>&nbsp;<b class='caret'></b>" +
              "</a>" +
              "<ul class='dropdown-menu'>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='black'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='black'>" + locale.colours.black + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='silver'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='silver'>" + locale.colours.silver + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='gray'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='gray'>" + locale.colours.gray + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='maroon'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='maroon'>" + locale.colours.maroon + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='red'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='red'>" + locale.colours.red + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='purple'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='purple'>" + locale.colours.purple + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='green'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='green'>" + locale.colours.green + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='olive'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='olive'>" + locale.colours.olive + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='navy'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='navy'>" + locale.colours.navy + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='blue'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='blue'>" + locale.colours.blue + "</a></li>" +
                "<li><div class='wysihtml5-colors' data-wysihtml5-command-value='orange'></div><a class='wysihtml5-colors-title' data-wysihtml5-command='foreColor' data-wysihtml5-command-value='orange'>" + locale.colours.orange + "</a></li>" +
              "</ul>" +
            "</li>";
        }
    };

    var templates = function(key, locale, options) {
        return tpl[key](locale, options);
    };


    var Wysihtml5 = function(el, options) {
        this.el = el;
        var toolbarOpts = options || defaultOptions;
        for(var t in toolbarOpts.customTemplates) {
          tpl[t] = toolbarOpts.customTemplates[t];
        }
        this.toolbar = this.createToolbar(el, toolbarOpts);
        this.editor =  this.createEditor(options);

        window.editor = this.editor;

        $('iframe.wysihtml5-sandbox').each(function(i, el){
            $(el.contentWindow).off('focus.wysihtml5').on({
                'focus.wysihtml5' : function(){
                    $('li.dropdown').removeClass('open');
                }
            });
        });
    };

    Wysihtml5.prototype = {

        constructor: Wysihtml5,

        createEditor: function(options) {
            options = options || {};

            // Add the toolbar to a clone of the options object so multiple instances
            // of the WYISYWG don't break because "toolbar" is already defined
            options = $.extend(true, {}, options);
            options.toolbar = this.toolbar[0];

            var editor = new wysi.Editor(this.el[0], options);

            if(options && options.events) {
                for(var eventName in options.events) {
                    editor.on(eventName, options.events[eventName]);
                }
            }
            return editor;
        },

        createToolbar: function(el, options) {
            var self = this;
            var toolbar = $("<ul/>", {
                'class' : "wysihtml5-toolbar",
                'style': "display:none"
            });
            var culture = options.locale || defaultOptions.locale || "en";
            for(var key in defaultOptions) {
                var value = false;

                if(options[key] !== undefined) {
                    if(options[key] === true) {
                        value = true;
                    }
                } else {
                    value = defaultOptions[key];
                }

                if(value === true) {
                    toolbar.append(templates(key, locale[culture], options));

                    if(key === "html") {
                        this.initHtml(toolbar);
                    }

                    if(key === "link") {
                        this.initInsertLink(toolbar);
                    }

                    if(key === "image") {
                        this.initInsertImage(toolbar);
                    }
                }
            }

            if(options.toolbar) {
                for(key in options.toolbar) {
                    toolbar.append(options.toolbar[key]);
                }
            }

            toolbar.find("a[data-wysihtml5-command='formatBlock']").click(function(e) {
                var target = e.target || e.srcElement;
                var el = $(target);
                self.toolbar.find('.current-font').text(el.html());
            });

            toolbar.find("a[data-wysihtml5-command='insertHTML']").click(function(e) {
                var target = e.target || e.srcElement;
                var el = $(target);
                self.toolbar.find('.current-option').text(el.html());
            });

            toolbar.find("a[data-wysihtml5-command='foreColor']").click(function(e) {
                var target = e.target || e.srcElement;
                var el = $(target);
                self.toolbar.find('.current-color').text(el.html());
            });

            this.el.before(toolbar);

            return toolbar;
        },

        initHtml: function(toolbar) {
            var changeViewSelector = "a[data-wysihtml5-action='change_view']";
            toolbar.find(changeViewSelector).click(function(e) {
                toolbar.find('a.btn').not(changeViewSelector).toggleClass('disabled');
            });
        },

        initInsertImage: function(toolbar) {
            var self = this;
            var insertImageModal = toolbar.find('.bootstrap-wysihtml5-insert-image-modal');
            var urlInput = insertImageModal.find('.bootstrap-wysihtml5-insert-image-url');
            var insertButton = insertImageModal.find('a.btn-primary');
            var initialValue = urlInput.val();
            var caretBookmark;

            /*var insertImage = function() {
                var url = urlInput.val();
                urlInput.val(initialValue);
                self.editor.currentView.element.focus();
                if (caretBookmark) {
                  self.editor.composer.selection.setBookmark(caretBookmark);
                  caretBookmark = null;
                }
                self.editor.composer.commands.exec("insertImage", url);
            };*/
			
			insertImageModal.find('[name="file"]').change(function() {
				var formData = new FormData($('#bootstrap-wysihtml5-file-upload')[0]);
				var url = baseurl + '/Wysihtml5/file_upload';
				  $.ajax({
						url:  url,  //Server script to process data
						type: 'POST',
						dataType: 'json',
						beforeSend: function(){
							$('.btn.upload').button('loading');
						},
						afterSend: function(){
							console.log("Afer Send");
						},
						success: function (response) {
							if (response.status == 'success') {
								toastr.success(response.message, "Success", toastr_opts);
								getfiles();
							} else {
								toastr.error(response.message, "Error", toastr_opts);
							}
							//alert(response.message);
						},
						// Form data
						data: formData,
						//Options to tell jQuery not to process data or worry about content-type.
						cache: false,
						contentType: false,
						processData: false
					});
			});
			
			var getfiles = function(){
				var data_table_new = $('.bootstrap-wysihtml5-insert-image-url').dataTable({
					"bDestroy": true, // Destroy when resubmit form
					"sDom": "<'row'<'col-xs-12 border_left'p><'col-xs-12 border_left'f>r><'imagegrid'>t",
					"bProcessing":true,
					"bServerSide":true,
					"sPaginationType": "bootstrap",
					"sAjaxSource": baseurl + '/Wysihtml5/getfiles',
					"iDisplayLength": '10',
					"aoColumns":
							[
								{"bSortable": false, //Code
									mRender: function(url, type, full) {
										var img = '<img src="'+url+'">';
										return "<a data-wysihtml5-command-value='"+img+"'>"+img+"</a>";
									}
								},
							],

					"fnDrawCallback": function() {
						
						var imagegrid = '<ul class="thumbnails image_picker_selector">';
										
						urlInput.find('td').each(function(){
							imagegrid += '<li>';
							imagegrid += '	<div class="thumbnail">';
							imagegrid += $(this).html();
							imagegrid += '	</div>';
							imagegrid += '</li>';
						});
						imagegrid += '</ul>';
						insertImageModal.find('.imagegrid').html(imagegrid);
						insertImageModal.find('.image_picker_selector li').on('click',function(){
							$(this).siblings('li').children('.thumbnail').removeClass('selected');
							$(this).children('.thumbnail').addClass('selected');
						})
						
						$(".dataTables_wrapper select").select2({
							minimumResultsForSearch: -1
						});
					}

				});
				insertImageModal.find('.dataTables_filter').remove();
			}
			
			var insertImage = function(){
				var selected= insertImageModal.find('.image_picker_selector div.selected');
				var selectedimage = selected.children('a[data-wysihtml5-command-value]').attr('data-wysihtml5-command-value');
				self.editor.currentView.element.focus();
				if (caretBookmark) {
                  self.editor.composer.selection.setBookmark(caretBookmark);
                  caretBookmark = null;
                }
				if(selectedimage){
					self.editor.composer.commands.exec("insertHTML", selectedimage);
				}
			}

            /*urlInput.keypress(function(e) {
                if(e.which == 13) {
                    insertImage();
                    insertImageModal.modal('hide');
                }
            });*/

            insertButton.click(insertImage);

            insertImageModal.on('shown.bs.modal', function() {
				getfiles();
                urlInput.focus();
            });

            insertImageModal.on('hide', function() {
                self.editor.currentView.element.focus();
            });

            toolbar.find('a[data-wysihtml5-command=insertImage]').click(function() {
				var offset = $(this).offset();
                var activeButton = $(this).hasClass("wysihtml5-command-active");

                if (!activeButton) {
                    self.editor.currentView.element.focus(false);
                    caretBookmark = self.editor.composer.selection.getBookmark();
                    insertImageModal.appendTo('body').modal('show');
					insertImageModal.css({ top: offset.top-200+'px' });
                    insertImageModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
                        e.stopPropagation();
                    });
                    return false;
                }
                else {
                    return true;
                }
            });
        },

        initInsertLink: function(toolbar) {
            var self = this;
            var insertLinkModal = toolbar.find('.bootstrap-wysihtml5-insert-link-modal');
            var urlInput = insertLinkModal.find('.bootstrap-wysihtml5-insert-link-url');
            var targetInput = insertLinkModal.find('.bootstrap-wysihtml5-insert-link-target');
            var insertButton = insertLinkModal.find('a.btn-primary');
            var initialValue = urlInput.val();
            var caretBookmark;

            var insertLink = function() {
                var url = urlInput.val();
                urlInput.val(initialValue);
                self.editor.currentView.element.focus();
                if (caretBookmark) {
                  self.editor.composer.selection.setBookmark(caretBookmark);
                  caretBookmark = null;
                }

                var newWindow = targetInput.prop("checked");
                self.editor.composer.commands.exec("createLink", {
                    'href' : url,
                    'target' : (newWindow ? '_blank' : '_self'),
                    'rel' : (newWindow ? 'nofollow' : '')
                });
            };
            var pressedEnter = false;

            urlInput.keypress(function(e) {
                if(e.which == 13) {
                    insertLink();
                    insertLinkModal.modal('hide');
                }
            });

            insertButton.click(insertLink);

            insertLinkModal.on('shown', function() {
                urlInput.focus();
            });

            insertLinkModal.on('hide', function() {
                self.editor.currentView.element.focus();
            });

            toolbar.find('a[data-wysihtml5-command=createLink]').click(function() {
				var offset = $(this).offset();
                var activeButton = $(this).hasClass("wysihtml5-command-active");

                if (!activeButton) {
                    self.editor.currentView.element.focus(false);
                    caretBookmark = self.editor.composer.selection.getBookmark();
                    insertLinkModal.appendTo('body').modal('show');
					insertLinkModal.css({ top: offset.top-200+'px' });
                    insertLinkModal.on('click.dismiss.modal', '[data-dismiss="modal"]', function(e) {
                        e.stopPropagation();
                    });
                    return false;
                }
                else {
                    return true;
                }
            });
        }
    };

    // these define our public api
    var methods = {
        resetDefaults: function() {
            $.fn.wysihtml5.defaultOptions = $.extend(true, {}, $.fn.wysihtml5.defaultOptionsCache);
        },
        bypassDefaults: function(options) {
            return this.each(function () {
                var $this = $(this);
                $this.data('wysihtml5', new Wysihtml5($this, options));
            });
        },
        shaltExtend: function (options) {
            var settings = $.extend({}, $.fn.wysihtml5.defaultOptions, options || {}, $(this).data());
            var that = this;
            return methods.bypassDefaults.apply(that, [settings]);
        },
        deepExtend: function(options) {
            var settings = $.extend(true, {}, $.fn.wysihtml5.defaultOptions, options || {});
            var that = this;
            return methods.bypassDefaults.apply(that, [settings]);
        },
        init: function(options) {
            var that = this;
            return methods.shaltExtend.apply(that, [options]);
        }
    };

    $.fn.wysihtml5 = function ( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.wysihtml5' );
        }
    };

    $.fn.wysihtml5.Constructor = Wysihtml5;

    var defaultOptions = $.fn.wysihtml5.defaultOptions = {
        "leadoptions":true ,
		"TicketsSingle":false,
        "invoiceoptions":false ,
		"Cronjobs":false,
		"estimateoptions":false,
		"ratesheetoptions":false,
		"opportunities":false,
		"tasks":false,
		"Crm":true,
		"Tickets":false,
        "font-styles": true,
        "color": false,
        "emphasis": true,
        "lists": true,
        "html": true,
        "link": true,
        "image": true,		
        events: {},
        parserRules: {
            classes: {
                // (path_to_project/lib/css/wysiwyg-color.css)
                "wysiwyg-color-silver" : 1,
                "wysiwyg-color-gray" : 1,
                "wysiwyg-color-white" : 1,
                "wysiwyg-color-maroon" : 1,
                "wysiwyg-color-red" : 1,
                "wysiwyg-color-purple" : 1,
                "wysiwyg-color-fuchsia" : 1,
                "wysiwyg-color-green" : 1,
                "wysiwyg-color-lime" : 1,
                "wysiwyg-color-olive" : 1,
                "wysiwyg-color-yellow" : 1,
                "wysiwyg-color-navy" : 1,
                "wysiwyg-color-blue" : 1,
                "wysiwyg-color-teal" : 1,
                "wysiwyg-color-aqua" : 1,
                "wysiwyg-color-orange" : 1
            },
            "tags": {
				"tr": {
					"add_class": {
						"align": "align_text"
					},
					"check_attributes":{
						"style":""
					}
				},
				"strike": {
					"remove": 1
				},
				"form": {
					"rename_tag": "div"
				},
				"rt": {
					"rename_tag": "span"
				},
				"code": {},
				"acronym": {
					"rename_tag": "span"
				},
				"br": {
					"add_class": {
						"clear": "clear_br"
					}
				},
				"details": {
					"rename_tag": "div"
				},
				"h4": {
					"add_class": {
						"align": "align_text"
					}
				},
				"em": {},
				"title": {
					"remove": 1
				},
				"multicol": {
					"rename_tag": "div"
				},
				"figure": {
					"rename_tag": "div"
				},
				"xmp": {
					"rename_tag": "span"
				},
				"small": {
					"rename_tag": "span",
					"set_class": "wysiwyg-font-size-smaller"
				},
				"area": {
					"remove": 1
				},
				"time": {
					"rename_tag": "span"
				},
				"dir": {
					"rename_tag": "ul"
				},
				"bdi": {
					"rename_tag": "span"
				},
				"command": {
					"remove": 1
				},
				"ul": {},
				"progress": {
					"rename_tag": "span"
				},
				"dfn": {
					"rename_tag": "span"
				},
				"iframe": {
					"remove": 1
				},
				"figcaption": {
					"rename_tag": "div"
				},
				"a": {
					"check_attributes": {
						"href": "url" // if you compiled master manually then change this from 'url' to 'href'
					},
					"set_attributes": {
						"rel": "nofollow",
						"target": "_blank"
					}
				},
				"img": {
					"check_attributes": {
						"width": "numbers",
						"alt": "alt",
						"src": "url", // if you compiled master manually then change this from 'url' to 'src'
						"height": "numbers"
					},
					"add_class": {
						"align": "align_img"
					}
				},
				"rb": {
					"rename_tag": "span"
				},
				"footer": {
					"rename_tag": "div"
				},
				"noframes": {
					"remove": 1
				},
				"abbr": {
					"rename_tag": "span"
				},
				"u": {},
				"bgsound": {
					"remove": 1
				},
				"sup": {
					"rename_tag": "span"
				},
				"address": {
					"rename_tag": "div"
				},
				"basefont": {
					"remove": 1
				},
				"nav": {
					"rename_tag": "div"
				},
				"h1": {
					"add_class": {
						"align": "align_text"
					}
				},
				"head": {
					"remove": 1
				},
				"tbody": {
					"add_class": {
						"align": "align_text"
					}
				},
				"dd": {
					"rename_tag": "div"
				},
				"s": {
					"rename_tag": "span"
				},
				"li": {},
				"td": {
					"check_attributes": {
						"rowspan": "numbers",
						"colspan": "numbers"
					},
					"add_class": {
						"align": "align_text"
					}
				},
				"object": {
					"remove": 1
				},
				"div": {
					"add_class": {
						"align": "align_text"
					}
				},
				"option": {
					"rename_tag": "span"
				},
				"select": {
					"rename_tag": "span"
				},
				"i": {},
				"track": {
					"remove": 1
				},
				"wbr": {
					"remove": 1
				},
				"fieldset": {
					"rename_tag": "div"
				},
				"big": {
					"rename_tag": "span",
					"set_class": "wysiwyg-font-size-larger"
				},
				"button": {
					"rename_tag": "span"
				},
				"noscript": {
					"remove": 1
				},
				"svg": {
					"remove": 1
				},
				"input": {
					"remove": 1
				},
				"table": {},
				"keygen": {
					"remove": 1
				},
				"h5": {
					"add_class": {
						"align": "align_text"
					}
				},
				"meta": {
					"remove": 1
				},
				"map": {
					"rename_tag": "div"
				},
				"isindex": {
					"remove": 1
				},
				"mark": {
					"rename_tag": "span"
				},
				"caption": {
					"add_class": {
						"align": "align_text"
					}
				},
				"tfoot": {
					"add_class": {
						"align": "align_text"
					}
				},
				"base": {
					"remove": 1
				},
				"video": {
					"remove": 1
				},
				"strong": {},
				"canvas": {
					"remove": 1
				},
				"output": {
					"rename_tag": "span"
				},
				"marquee": {
					"rename_tag": "span"
				},
				"b": {},
				"q": {
					"check_attributes": {
						"cite": "url"
					}
				},
				"applet": {
					"remove": 1
				},
				"span": {},
				"rp": {
					"rename_tag": "span"
				},
				"spacer": {
					"remove": 1
				},
				"source": {
					"remove": 1
				},
				"aside": {
					"rename_tag": "div"
				},
				"frame": {
					"remove": 1
				},
				"section": {
					"rename_tag": "div"
				},
				"body": {
					"rename_tag": "div"
				},
				"ol": {},
				"nobr": {
					"rename_tag": "span"
				},
				"html": {
					"rename_tag": "div"
				},
				"summary": {
					"rename_tag": "span"
				},
				"var": {
					"rename_tag": "span"
				},
				"del": {
					"remove": 1
				},
				"blockquote": {
					"check_attributes": {
						"cite": "url"
					}
				},
				"style": {
					"remove": 1
				},
				"device": {
					"remove": 1
				},
				"meter": {
					"rename_tag": "span"
				},
				"h3": {
					"add_class": {
						"align": "align_text"
					}
				},
				"textarea": {
					"rename_tag": "span"
				},
				"embed": {
					"remove": 1
				},
				"hgroup": {
					"rename_tag": "div"
				},
				"font": {
					"rename_tag": "span",
					"add_class": {
						"size": "size_font"
					}
				},
				"tt": {
					"rename_tag": "span"
				},
				"noembed": {
					"remove": 1
				},
				"thead": {
					"add_class": {
						"align": "align_text"
					}
				},
				"blink": {
					"rename_tag": "span"
				},
				"plaintext": {
					"rename_tag": "span"
				},
				"xml": {
					"remove": 1
				},
				"h6": {
					"add_class": {
						"align": "align_text"
					}
				},
				"param": {
					"remove": 1
				},
				"th": {
					"check_attributes": {
						"rowspan": "numbers",
						"colspan": "numbers"
					},
					"add_class": {
						"align": "align_text"
					}
				},
				"legend": {
					"rename_tag": "span"
				},
				"hr": {},
				"label": {
					"rename_tag": "span"
				},
				"dl": {
					"rename_tag": "div"
				},
				"kbd": {
					"rename_tag": "span"
				},
				"listing": {
					"rename_tag": "div"
				},
				"dt": {
					"rename_tag": "span"
				},
				"nextid": {
					"remove": 1
				},
				"pre": {},
				"center": {
					"rename_tag": "div",
					"set_class": "wysiwyg-text-align-center"
				},
				"audio": {
					"remove": 1
				},
				"datalist": {
					"rename_tag": "span"
				},
				"samp": {
					"rename_tag": "span"
				},
				"col": {
					"remove": 1
				},
				"article": {
					"rename_tag": "div"
				},
				"cite": {},
				"link": {
					"remove": 1
				},
				"script": {
					"remove": 1
				},
				"bdo": {
					"rename_tag": "span"
				},
				"menu": {
					"rename_tag": "ul"
				},
				"colgroup": {
					"remove": 1
				},
				"ruby": {
					"rename_tag": "span"
				},
				"h2": {
					"add_class": {
						"align": "align_text"
					}
				},
				"ins": {
					"rename_tag": "span"
				},
				"p": {
					"add_class": {
						"align": "align_text"
					},
					"check_attributes":{
						"style":""
					}
				},
				"sub": {
					"rename_tag": "span"
				},
				"comment": {
					"remove": 1
				},
				"frameset": {
					"remove": 1
				},
				"optgroup": {
					"rename_tag": "span"
				},
				"header": {
					"rename_tag": "div"
				}
			}
        },
        stylesheets: baseurl+'/assets/js/wysihtml5/bootstrap-wysihtml5.css',//["./lib/css/wysiwyg-color.css"], // (path_to_project/lib/css/wysiwyg-color.css)
        locale: "en"
    };

    if (typeof $.fn.wysihtml5.defaultOptionsCache === 'undefined') {
        $.fn.wysihtml5.defaultOptionsCache = $.extend(true, {}, $.fn.wysihtml5.defaultOptions);
    }

    var locale = $.fn.wysihtml5.locale = {
        en: {
            leadoptions:{
                FirstName:"FirstName",
                LastName:"LastName",
                Email:"Email",
                Address1:"Address1",
                Address2:"Address2",
                Address3:"Address3",
                City:"City",
                State:"State",
                PostCode:"PostCode",
                Country:"Country",
                Currency:"Currency",
                Signature:"Signature",
                OutstandingExcludeUnbilledAmount:"OutstandingExcludeUnbilledAmount",
                OutstandingIncludeUnbilledAmount:"OutstandingIncludeUnbilledAmount",
                BalanceThreshold:"BalanceThreshold"

            },
			ratesheetoptions:{
	            FirstName:"FirstName",
                LastName:"LastName",
				RateTableName:"RateTableName",
				EffectiveDate:"EffectiveDate",
				RateGeneratorName:"RateGeneratorName",
				CompanyName:"CompanyName",
			},
			opportunities:{
				Subject:"Subject",
				User:"User",
				Comment:"Comment",
				Logo:"Logo"
			},
			tasks:{
				Subject:"Subject",
				User:"User",
				Comment:"Comment",				
			},
            invoiceoptions:{
                FirstName:"FirstName",
                LastName:"LastName",
                Email:"Email",
                Address1:"Address1",
                Address2:"Address2",
                Address3:"Address3",
                City:"City",
                State:"State",
                PostCode:"PostCode",
                Country:"Country",
                Currency:"Currency",
                InvoiceNumber:"InvoiceNumber",
                InvoiceGrandTotal:"InvoiceGrandTotal",
                InvoiceOutstanding:"InvoiceOutstanding",
                OutstandingExcludeUnbilledAmount:"OutstandingExcludeUnbilledAmount",
                OutstandingIncludeUnbilledAmount:"OutstandingIncludeUnbilledAmount",
                BalanceThreshold:"BalanceThreshold",
                Signature:"Signature",
				CompanyName:"CompanyName",
				InvoiceLink:"InvoiceLink",
                PeriodFrom:"PeriodFrom",
                PeriodTo:"PeriodTo",
				AccountName:"AccountName",
            }, 
			estimateoptions:{
                FirstName:"FirstName",
                LastName:"LastName",
                Email:"Email",
                Address1:"Address1",
                Address2:"Address2",
                Address3:"Address3",
                City:"City",
                State:"State",
                PostCode:"PostCode",
                Country:"Country",
                Currency:"Currency",
			 	EstimateNumber:"EstimateNumber",
				EstimateGrandTotal:"EstimateGrandTotal",
				CompanyName:"CompanyName",
				EstimateLink:"EstimateLink",
				AccountName:"AccountName",
				Comment:"Comment",
				Message:"Message",
				Signature:"Signature",
				User:"User"								
			},
			Crm:{				
                FirstName:"FirstName",
                LastName:"LastName",
                Email:"Email",
                Address1:"Address1",
                Address2:"Address2",
                Address3:"Address3",
                City:"City",
                State:"State",
                PostCode:"PostCode",
                Country:"Country",
                Signature:"Signature"				
				},
		Cronjobs:{
				KillCommand:"KillCommand",
				ReturnStatus:"ReturnStatus",
				DetailOutput:"DetailOutput",
				Minute:"Minute",
				JobTitle:"JobTitle",
				PID:"PID",
				CompanyName:"CompanyName",
				Url:"Url"
				},
		TicketsSingle:{
				Subject:"Subject",
				TicketID:"TicketID",
				Requester:"Requester",
				Account:"Account",
				RequesterName:"RequesterName",
				Status:"Status",
				Priority:'Priority',
				Description:"Description",
				Group:"Group",
				Type:"Type",
				Date:"Date",
				AgentName:"AgentName",
				AgentEmail:"AgentEmail",
				Comment:"Comment",
				TicketCustomerUrl:"TicketCustomerUrl",
				TicketUrl:"TicketUrl",
				Notebody:"Notebody",
				NoteUser:"NoteUser"
				},
			Company:{
				CompanyName:"CompanyName",
				Vat:"Vat",
				FirstName:"FirstName",
                LastName:"LastName",
                Email:"Email",
                Address1:"Address1",
                Address2:"Address2",
                Address3:"Address3",
                City:"City",
                State:"State",
                PostCode:"PostCode",
                Country:"Country",
				Logo:"Logo"
				},
			Tickets:{
				Subject:"Subject",
				TicketID:"TicketID",
				Requester:"Requester",
				Account:"Account",
				RequesterName:"RequesterName",
				Status:"Status",
				Priority:'Priority',
				Description:"Description",
				Group:"Group",
				Type:"Type",
				Date:"Date",
				AgentName:"AgentName",
				AgentEmail:"AgentEmail",
				FirstName:"FirstName",				
				LastName:"LastName",
				Email:"Email",
				Address1:"Address1",
				Address2:"Address2",
				Address3:"Address3",
				City:"City",
				State:"State",
				PostCode:"PostCode",
				Country:"Country",
				Currency:"Currency",
				OutstandingExcludeUnbilledAmount:"OutstandingExcludeUnbilledAmount",
				OutstandingIncludeUnbilledAmount:"OutstandingIncludeUnbilledAmount",
				BalanceThreshold:"BalanceThreshold",
				Signature:"Signature",
				InvoiceNumber:"InvoiceNumber",
				InvoiceGrandTotal:"InvoiceGrandTotal",
				InvoiceOutstanding:"InvoiceOutstanding",			
			},	
            font_styles: {
                normal: "Normal text",
                h1: "Heading 1",
                h2: "Heading 2",
                h3: "Heading 3",
                h4: "Heading 4",
                h5: "Heading 5",
                h6: "Heading 6"
            },
            emphasis: {
                bold: "Bold",
                italic: "Italic",
                underline: "Underline"
            },
            lists: {
                unordered: "Unordered list",
                ordered: "Ordered list",
                outdent: "Outdent",
                indent: "Indent"
            },
            link: {
                insert: "Insert link",
                cancel: "Cancel",
                target: "Open link in new window"
            },
            image: {
                insert: "Insert image",
                cancel: "Cancel"
            },
            html: {
                edit: "Edit HTML"
            },
            colours: {
                black: "Black",
                silver: "Silver",
                gray: "Grey",
                maroon: "Maroon",
                red: "Red",
                purple: "Purple",
                green: "Green",
                olive: "Olive",
                navy: "Navy",
                blue: "Blue",
                orange: "Orange"
            }
        }
    };

}(window.jQuery, window.wysihtml5);