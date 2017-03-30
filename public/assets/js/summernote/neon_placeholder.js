/**
 * Created by deven on 30/03/2017.
 */

var neon_summernote_options = {
    "leadoptions": true,
    "TicketsSingle": false,
    "invoiceoptions": false,
    "Cronjobs": false,
    "estimateoptions": false,
    "ratesheetoptions": false,
    "opportunities": false,
    "tasks": false,
    "Crm": true,
    "Tickets": false,
    "dropdown_text" : ''
};

var dropdown_text = neon_summernote_options.dropdown_text = {

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
            AccountName:"AccountName",
            Signature:"Signature",
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
};

!function($, wysi) {
    "use strict";

    var tpl = {
        "leadoptions": function () {
            return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
                "<li><a data-value='{{FirstName}}'>" + neon_summernote_options.dropdown_textleadoptions.FirstName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textleadoptions.LastName + "</a></li>" +
                "<li><a data-value='{{Email}}'>" + neon_summernote_options.dropdown_textleadoptions.Email + "</a></li>" +
                "<li><a data-value='{{Address1}}'>" + neon_summernote_options.dropdown_textleadoptions.Address1 + "</a></li>" +
                "<li><a data-value='{{Address2}}'>" + neon_summernote_options.dropdown_textleadoptions.Address2 + "</a></li>" +
                "<li><a data-value='{{Address3}}'>" + neon_summernote_options.dropdown_textleadoptions.Address3 + "</a></li>" +
                "<li><a data-value='{{City}}'>" + neon_summernote_options.dropdown_textleadoptions.City + "</a></li>" +
                "<li><a data-value='{{State}}'>" + neon_summernote_options.dropdown_textleadoptions.State + "</a></li>" +
                "<li><a data-value='{{PostCode}}'>" + neon_summernote_options.dropdown_textleadoptions.PostCode + "</a></li>" +
                "<li><a data-value='{{Country}}'>" + neon_summernote_options.dropdown_textleadoptions.Country + "</a></li>" +
                "<li><a data-value='{{Currency}}'>" + neon_summernote_options.dropdown_textleadoptions.Currency + "</a></li>" +
                "<li><a data-value='{{OutstandingExcludeUnbilledAmount}}'>" + neon_summernote_options.dropdown_textleadoptions.OutstandingExcludeUnbilledAmount + "</a></li>" +
                "<li><a data-value='{{OutstandingIncludeUnbilledAmount}}'>" + neon_summernote_options.dropdown_textleadoptions.OutstandingIncludeUnbilledAmount + "</a></li>" +
                "<li><a data-value='{{BalanceThreshold}}'>" + neon_summernote_options.dropdown_textleadoptions.BalanceThreshold + "</a></li>" +
                "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textCompany.CompanyName + "</a></li>" +
                "<li><a data-value='{{CompanyVAT}}'>" + neon_summernote_options.dropdown_textCompany.Vat + "</a></li>" +
                "<li><a data-value='{{CompanyAddress1}}'>" + neon_summernote_options.dropdown_textCompany.Address1 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress2}}'>" + neon_summernote_options.dropdown_textCompany.Address2 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress3}}'>" + neon_summernote_options.dropdown_textCompany.Address3 + "</a></li>" +
                "<li><a data-value='{{CompanyCity}}'>" + neon_summernote_options.dropdown_textCompany.City + "</a></li>" +
                "<li><a data-value='{{CompanyPostCode}}'>" + neon_summernote_options.dropdown_textCompany.PostCode + "</a></li>" +
                "<li><a data-value='{{CompanyCountry}}'>" + neon_summernote_options.dropdown_textCompany.Country + "</a></li>" +
                "<li><a data-value='{{Logo}}'>" + neon_summernote_options.dropdown_textCompany.Logo + "</a></li>" +
                "<li class='unclick'><a ><b>Others</b></a></li>" +
                "<li><a data-value='{{Signature}}'>" + neon_summernote_options.dropdown_textleadoptions.Signature + "</a></li>";
        },
        "invoiceoptions": function () {
            
            return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
                "<li><a data-value='{{AccountName}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.AccountName + "</a></li>" +
                "<li><a data-value='{{FirstName}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.FirstName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.LastName + "</a></li>" +
                "<li><a data-value='{{Email}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.Email + "</a></li>" +
                "<li><a data-value='{{Address1}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.Address1 + "</a></li>" +
                "<li><a data-value='{{Address2}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.Address2 + "</a></li>" +
                "<li><a data-value='{{Address3}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.Address3 + "</a></li>" +
                "<li><a data-value='{{City}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.City + "</a></li>" +
                "<li><a data-value='{{State}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.State + "</a></li>" +
                "<li><a data-value='{{PostCode}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.PostCode + "</a></li>" +
                "<li><a data-value='{{Country}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.Country + "</a></li>" +
                "<li><a data-value='{{Currency}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.Currency + "</a></li>" +
                "<li class='unclick'><a ><b>Invoice Fields</b></a></li>" +
                "<li><a data-value='{{InvoiceNumber}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.InvoiceNumber + "</a></li>" +
                "<li><a data-value='{{InvoiceGrandTotal}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.InvoiceGrandTotal + "</a></li>" +
                "<li><a data-value='{{InvoiceOutstanding}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.InvoiceOutstanding + "</a></li>" +
                "<li><a data-value='{{InvoiceLink}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.InvoiceLink + "</a></li>" +
                    /* "<li><a data-value='{{OutstandingExcludeUnbilledAmount}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.OutstandingExcludeUnbilledAmount + "</a></li>" +
                     "<li><a data-value='{{OutstandingIncludeUnbilledAmount}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.OutstandingIncludeUnbilledAmount + "</a></li>" +
                     "<li><a data-value='{{BalanceThreshold}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.BalanceThreshold + "</a></li>" +*/
                "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textCompany.CompanyName + "</a></li>" +
                "<li><a data-value='{{CompanyVAT}}'>" + neon_summernote_options.dropdown_textCompany.Vat + "</a></li>" +
                "<li><a data-value='{{CompanyAddress1}}'>" + neon_summernote_options.dropdown_textCompany.Address1 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress2}}'>" + neon_summernote_options.dropdown_textCompany.Address2 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress3}}'>" + neon_summernote_options.dropdown_textCompany.Address3 + "</a></li>" +
                "<li><a data-value='{{CompanyCity}}'>" + neon_summernote_options.dropdown_textCompany.City + "</a></li>" +
                "<li><a data-value='{{CompanyPostCode}}'>" + neon_summernote_options.dropdown_textCompany.PostCode + "</a></li>" +
                "<li><a data-value='{{CompanyCountry}}'>" + neon_summernote_options.dropdown_textCompany.Country + "</a></li>" +
                "<li><a data-value='{{Logo}}'>" + neon_summernote_options.dropdown_textCompany.Logo + "</a></li>" +
                "<li class='unclick'><a ><b>Others</b></a></li>" +
                "<li><a data-value='{{Signature}}'>" + neon_summernote_options.dropdown_textinvoiceoptions.Signature + "</a></li>" ;
        },
        "tasks": function () {
            return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
                "<li><a data-value='{{FirstName}}'>" + neon_summernote_options.dropdown_textleadoptions.FirstName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textleadoptions.LastName + "</a></li>" +
                "<li><a data-value='{{Email}}'>" + neon_summernote_options.dropdown_textleadoptions.Email + "</a></li>" +
                "<li><a data-value='{{Address1}}'>" + neon_summernote_options.dropdown_textleadoptions.Address1 + "</a></li>" +
                "<li><a data-value='{{Address2}}'>" + neon_summernote_options.dropdown_textleadoptions.Address2 + "</a></li>" +
                "<li><a data-value='{{Address3}}'>" + neon_summernote_options.dropdown_textleadoptions.Address3 + "</a></li>" +
                "<li><a data-value='{{City}}'>" + neon_summernote_options.dropdown_textleadoptions.City + "</a></li>" +
                "<li><a data-value='{{State}}'>" + neon_summernote_options.dropdown_textleadoptions.State + "</a></li>" +
                "<li><a data-value='{{PostCode}}'>" + neon_summernote_options.dropdown_textleadoptions.PostCode + "</a></li>" +
                "<li><a data-value='{{Country}}'>" + neon_summernote_options.dropdown_textleadoptions.Country + "</a></li>" +
                "<li class='unclick'><a ><b>Tasks Fields</b></a></li>" +
                "<li><a data-value='{{subject}}'>" + neon_summernote_options.dropdown_texttasks.Subject + "</a></li>" +
                "<li><a data-value='{{User}}'>" + neon_summernote_options.dropdown_texttasks.User + "</a></li>" +
                "<li><a data-value='{{Comment}}'>" + neon_summernote_options.dropdown_texttasks.Comment + "</a></li>" +
                "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textCompany.CompanyName + "</a></li>" +
                "<li><a data-value='{{CompanyVAT}}'>" + neon_summernote_options.dropdown_textCompany.Vat + "</a></li>" +
                "<li><a data-value='{{CompanyAddress1}}'>" + neon_summernote_options.dropdown_textCompany.Address1 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress2}}'>" + neon_summernote_options.dropdown_textCompany.Address2 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress3}}'>" + neon_summernote_options.dropdown_textCompany.Address3 + "</a></li>" +
                "<li><a data-value='{{CompanyCity}}'>" + neon_summernote_options.dropdown_textCompany.City + "</a></li>" +
                "<li><a data-value='{{CompanyPostCode}}'>" + neon_summernote_options.dropdown_textCompany.PostCode + "</a></li>" +
                "<li><a data-value='{{CompanyCountry}}'>" + neon_summernote_options.dropdown_textCompany.Country + "</a></li>" +
                "<li><a data-value='{{Logo}}'>" + neon_summernote_options.dropdown_textCompany.Logo + "</a></li>" ;
         },
        "opportunities": function () {
            return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
                "<li><a data-value='{{FirstName}}'>" + neon_summernote_options.dropdown_textleadoptions.FirstName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textleadoptions.LastName + "</a></li>" +
                "<li><a data-value='{{Email}}'>" + neon_summernote_options.dropdown_textleadoptions.Email + "</a></li>" +
                "<li><a data-value='{{Address1}}'>" + neon_summernote_options.dropdown_textleadoptions.Address1 + "</a></li>" +
                "<li><a data-value='{{Address2}}'>" + neon_summernote_options.dropdown_textleadoptions.Address2 + "</a></li>" +
                "<li><a data-value='{{Address3}}'>" + neon_summernote_options.dropdown_textleadoptions.Address3 + "</a></li>" +
                "<li><a data-value='{{City}}'>" + neon_summernote_options.dropdown_textleadoptions.City + "</a></li>" +
                "<li><a data-value='{{State}}'>" + neon_summernote_options.dropdown_textleadoptions.State + "</a></li>" +
                "<li><a data-value='{{PostCode}}'>" + neon_summernote_options.dropdown_textleadoptions.PostCode + "</a></li>" +
                "<li><a data-value='{{Country}}'>" + neon_summernote_options.dropdown_textleadoptions.Country + "</a></li>" +
                "<li class='unclick'><a ><b>Opportunity Fields</b></a></li>" +
                "<li><a data-value='{{subject}}'>" + neon_summernote_options.dropdown_textopportunities.Subject + "</a></li>" +
                "<li><a data-value='{{User}}'>" + neon_summernote_options.dropdown_textopportunities.User + "</a></li>" +
                "<li><a data-value='{{Comment}}'>" + neon_summernote_options.dropdown_textopportunities.Comment + "</a></li>" +
                "<li><a data-value='{{Logo}}'>" + neon_summernote_options.dropdown_textopportunities.Logo + "</a></li>" +
                "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textCompany.CompanyName + "</a></li>" +
                "<li><a data-value='{{CompanyVAT}}'>" + neon_summernote_options.dropdown_textCompany.Vat + "</a></li>" +
                "<li><a data-value='{{CompanyAddress1}}'>" + neon_summernote_options.dropdown_textCompany.Address1 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress2}}'>" + neon_summernote_options.dropdown_textCompany.Address2 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress3}}'>" + neon_summernote_options.dropdown_textCompany.Address3 + "</a></li>" +
                "<li><a data-value='{{CompanyCity}}'>" + neon_summernote_options.dropdown_textCompany.City + "</a></li>" +
                "<li><a data-value='{{CompanyPostCode}}'>" + neon_summernote_options.dropdown_textCompany.PostCode + "</a></li>" +
                "<li><a data-value='{{CompanyCountry}}'>" + neon_summernote_options.dropdown_textCompany.Country + "</a></li>" +
                "<li><a data-value='{{Logo}}'>" + neon_summernote_options.dropdown_textCompany.Logo + "</a></li>" ;
        },
        "ratesheetoptions": function () {
            return  "<li><a data-value='{{FirstName}}'>" + neon_summernote_options.dropdown_textratesheetoptions.FirstName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textratesheetoptions.LastName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textratesheetoptions.RateGeneratorName + "</a></li>" +
                "<li><a data-value='{{RateTableName}}'>" + neon_summernote_options.dropdown_textratesheetoptions.RateTableName + "</a></li>" +
                "<li><a data-value='{{EffectiveDate}}'>" + neon_summernote_options.dropdown_textratesheetoptions.EffectiveDate + "</a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textratesheetoptions.CompanyName + "</a></li>" ;
        },
        "estimateoptions": function () {
                return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
                "<li><a data-value='{{AccountName}}'>" + neon_summernote_options.dropdown_textestimateoptions.AccountName + "</a></li>" +
                "<li><a data-value='{{FirstName}}'>" + neon_summernote_options.dropdown_textestimateoptions.FirstName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textestimateoptions.LastName + "</a></li>" +
                "<li><a data-value='{{Email}}'>" + neon_summernote_options.dropdown_textestimateoptions.Email + "</a></li>" +
                "<li><a data-value='{{Address1}}'>" + neon_summernote_options.dropdown_textestimateoptions.Address1 + "</a></li>" +
                "<li><a data-value='{{Address2}}'>" + neon_summernote_options.dropdown_textestimateoptions.Address2 + "</a></li>" +
                "<li><a data-value='{{Address3}}'>" + neon_summernote_options.dropdown_textestimateoptions.Address3 + "</a></li>" +
                "<li><a data-value='{{City}}'>" + neon_summernote_options.dropdown_textestimateoptions.City + "</a></li>" +
                "<li><a data-value='{{State}}'>" + neon_summernote_options.dropdown_textestimateoptions.State + "</a></li>" +
                "<li><a data-value='{{PostCode}}'>" + neon_summernote_options.dropdown_textestimateoptions.PostCode + "</a></li>" +
                "<li><a data-value='{{Country}}'>" + neon_summernote_options.dropdown_textestimateoptions.Country + "</a></li>" +
                "<li><a data-value='{{Currency}}'>" + neon_summernote_options.dropdown_textestimateoptions.Currency + "</a></li>" +
                "<li class='unclick'><a ><b>Estimate Fields</b></a></li>" +
                "<li><a data-value='{{EstimateNumber}}'>" + neon_summernote_options.dropdown_textestimateoptions.EstimateNumber + "</a></li>" + "<li><a data-value='{{EstimateGrandTotal}}'>" + neon_summernote_options.dropdown_textestimateoptions.EstimateGrandTotal + "</a></li>" +
                "<li><a data-value='{{EstimateLink}}'>" + neon_summernote_options.dropdown_textestimateoptions.EstimateLink + "</a></li>" +
                "<li><a data-value='{{Comment}}'>" + neon_summernote_options.dropdown_textestimateoptions.Comment + "</a></li>" +
                "<li><a data-value='{{User}}'>" + neon_summernote_options.dropdown_textestimateoptions.User + "</a></li>" +

                "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textCompany.CompanyName + "</a></li>" +
                "<li><a data-value='{{CompanyVAT}}'>" + neon_summernote_options.dropdown_textCompany.Vat + "</a></li>" +
                "<li><a data-value='{{CompanyAddress1}}'>" + neon_summernote_options.dropdown_textCompany.Address1 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress2}}'>" + neon_summernote_options.dropdown_textCompany.Address2 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress3}}'>" + neon_summernote_options.dropdown_textCompany.Address3 + "</a></li>" +
                "<li><a data-value='{{CompanyCity}}'>" + neon_summernote_options.dropdown_textCompany.City + "</a></li>" +
                "<li><a data-value='{{CompanyPostCode}}'>" + neon_summernote_options.dropdown_textCompany.PostCode + "</a></li>" +
                "<li><a data-value='{{CompanyCountry}}'>" + neon_summernote_options.dropdown_textCompany.Country + "</a></li>" +
                "<li><a data-value='{{Logo}}'>" + neon_summernote_options.dropdown_textCompany.Logo + "</a></li>" +
                "<li class='unclick'><a ><b>Others</b></a></li>" +
                "<li><a data-value='{{Signature}}'>" + neon_summernote_options.dropdown_textestimateoptions.Signature + "</a></li>" ;

        },
         "Crm": function () {
                return "<li><a data-value='{{FirstName}}'>" + neon_summernote_options.dropdown_textCrm.FirstName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textCrm.LastName + "</a></li>" +
                "<li><a data-value='{{Email}}'>" + neon_summernote_options.dropdown_textCrm.Email + "</a></li>" +
                "<li><a data-value='{{Address1}}'>" + neon_summernote_options.dropdown_textCrm.Address1 + "</a></li>" +
                "<li><a data-value='{{Address2}}'>" + neon_summernote_options.dropdown_textCrm.Address2 + "</a></li>" +
                "<li><a data-value='{{Address3}}'>" + neon_summernote_options.dropdown_textCrm.Address3 + "</a></li>" +
                "<li><a data-value='{{City}}'>" + neon_summernote_options.dropdown_textCrm.City + "</a></li>" +
                "<li><a data-value='{{State}}'>" + neon_summernote_options.dropdown_textCrm.State + "</a></li>" +
                "<li><a data-value='{{PostCode}}'>" + neon_summernote_options.dropdown_textCrm.PostCode + "</a></li>" +
                "<li><a data-value='{{Country}}'>" + neon_summernote_options.dropdown_textCrm.Country + "</a></li>" +
                "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textCompany.CompanyName + "</a></li>" +
                "<li><a data-value='{{CompanyVAT}}'>" + neon_summernote_options.dropdown_textCompany.Vat + "</a></li>" +
                "<li><a data-value='{{CompanyAddress1}}'>" + neon_summernote_options.dropdown_textCompany.Address1 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress2}}'>" + neon_summernote_options.dropdown_textCompany.Address2 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress3}}'>" + neon_summernote_options.dropdown_textCompany.Address3 + "</a></li>" +
                "<li><a data-value='{{CompanyCity}}'>" + neon_summernote_options.dropdown_textCompany.City + "</a></li>" +
                "<li><a data-value='{{CompanyPostCode}}'>" + neon_summernote_options.dropdown_textCompany.PostCode + "</a></li>" +
                "<li><a data-value='{{CompanyCountry}}'>" + neon_summernote_options.dropdown_textCompany.Country + "</a></li>" +
                "<li><a data-value='{{Logo}}'>" + neon_summernote_options.dropdown_textCompany.Logo + "</a></li>" +
                "<li class='unclick'><a ><b>Others</b></a></li>" +
                "<li><a data-value='{{Signature}}'>" + neon_summernote_options.dropdown_textCrm.Signature + "</a></li>";
        },
        "TicketsSingle": function () {
                return "<li class='unclick'><a ><b>Ticket Fields</b></a></li>" +
                "<li><a data-value='{{Subject}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Subject + "</a></li>" +
                "<li><a data-value='{{TicketID}}'>" + neon_summernote_options.dropdown_textTicketsSingle.TicketID + "</a></li>" +
                "<li><a data-value='{{Requester}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Requester + "</a></li>" +
                "<li><a data-value='{{RequesterName}}'>" + neon_summernote_options.dropdown_textTicketsSingle.RequesterName + "</a></li>" +
                "<li><a data-value='{{Status}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Status + "</a></li>" +
                "<li><a data-value='{{Priority}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Priority + "</a></li>" +
                "<li><a data-value='{{Description}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Description + "</a></li>" +
                "<li><a data-value='{{Group}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Group + "</a></li>" +
                "<li><a data-value='{{Type}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Type + "</a></li>" +
                "<li><a data-value='{{Date}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Date + "</a></li>" +
                "<li><a data-value='{{AgentName}}'>" + neon_summernote_options.dropdown_textTicketsSingle.AgentName + "</a></li>" +
                "<li><a data-value='{{AgentEmail}}'>" + neon_summernote_options.dropdown_textTicketsSingle.AgentEmail + "</a></li>" +
                "<li><a data-value='{{TicketUrl}}'>" + neon_summernote_options.dropdown_textTicketsSingle.TicketUrl + "</a></li>" +
                "<li><a data-value='{{TicketCustomerUrl}}'>" + neon_summernote_options.dropdown_textTicketsSingle.TicketCustomerUrl + "</a></li>" +
                "<li><a data-value='{{Comment}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Comment + "</a></li>" +
                "<li><a data-value='{{Notebody}}'>" + neon_summernote_options.dropdown_textTicketsSingle.Notebody + "</a></li>" +
                "<li><a data-value='{{NoteUser}}'>" + neon_summernote_options.dropdown_textTicketsSingle.NoteUser + "</a></li>" +
                "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textCompany.CompanyName + "</a></li>" +
                "<li><a data-value='{{CompanyVAT}}'>" + neon_summernote_options.dropdown_textCompany.Vat + "</a></li>" +
                "<li><a data-value='{{CompanyAddress1}}'>" + neon_summernote_options.dropdown_textCompany.Address1 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress2}}'>" + neon_summernote_options.dropdown_textCompany.Address2 + "</a></li>" +
                "<li><a data-value='{{CompanyAddress3}}'>" + neon_summernote_options.dropdown_textCompany.Address3 + "</a></li>" +
                "<li><a data-value='{{CompanyCity}}'>" + neon_summernote_options.dropdown_textCompany.City + "</a></li>" +
                "<li><a data-value='{{CompanyPostCode}}'>" + neon_summernote_options.dropdown_textCompany.PostCode + "</a></li>" +
                "<li><a data-value='{{CompanyCountry}}'>" + neon_summernote_options.dropdown_textCompany.Country + "</a></li>" +
                "<li><a data-value='{{Logo}}'>" + neon_summernote_options.dropdown_textCompany.Logo + "</a></li>"   ;
        },
        "Cronjobs": function () {

                return "<li><a data-value='{{KillCommand}}'>" + neon_summernote_options.dropdown_textCronjobs.KillCommand + "</a></li>" +
                "<li><a data-value='{{ReturnStatus}}'>" + neon_summernote_options.dropdown_textCronjobs.ReturnStatus + "</a></li>" +
                "<li><a data-value='{{DetailOutput}}'>" + neon_summernote_options.dropdown_textCronjobs.DetailOutput + "</a></li>" +
                "<li><a data-value='{{Minute}}'>" + neon_summernote_options.dropdown_textCronjobs.Minute + "</a></li>" +
                "<li><a data-value='{{JobTitle}}'>" + neon_summernote_options.dropdown_textCronjobs.JobTitle + "</a></li>" +
                "<li><a data-value='{{PID}}'>" + neon_summernote_options.dropdown_textCronjobs.PID + "</a></li>" +
                "<li><a data-value='{{CompanyName}}'>" + neon_summernote_options.dropdown_textCronjobs.CompanyName + "</a></li>" +
                "<li><a data-value='{{Url}}'>" + neon_summernote_options.dropdown_textCronjobs.Url + "</a></li>";
        },
        "Tickets": function () {
            var tickets_data = '';
            if (tickets_enable == 1) {
                tickets_data = "<li class='unclick'><a ><b>Ticket Fields</b></a></li>" + "<li><a data-value='{{Subject}}'>" + neon_summernote_options.dropdown_textTickets.Subject + "</a></li>" +
                    "<li><a data-value='{{TicketID}}'>" + neon_summernote_options.dropdown_textTickets.TicketID + "</a></li>" +
                    "<li><a data-value='{{Requester}}'>" + neon_summernote_options.dropdown_textTickets.Requester + "</a></li>" +
                    "<li><a data-value='{{RequesterName}}'>" + neon_summernote_options.dropdown_textTickets.RequesterName + "</a></li>" +
                    "<li><a data-value='{{Status}}'>" + neon_summernote_options.dropdown_textTickets.Status + "</a></li>" +
                    "<li><a data-value='{{Priority}}'>" + neon_summernote_options.dropdown_textTickets.Priority + "</a></li>" +
                    "<li><a data-value='{{Description}}'>" + neon_summernote_options.dropdown_textTickets.Description + "</a></li>" +
                    "<li><a data-value='{{Group}}'>" + neon_summernote_options.dropdown_textTickets.Group + "</a></li>" +
                    "<li><a data-value='{{Type}}'>" + neon_summernote_options.dropdown_textTickets.Type + "</a></li>" +
                    "<li><a data-value='{{Date}}'>" + neon_summernote_options.dropdown_textTickets.Date + "</a></li>" +
                    "<li><a data-value='{{AgentName}}'>" + neon_summernote_options.dropdown_textTickets.AgentName + "</a></li>" +
                    "<li><a data-value='{{AgentEmail}}'>" + neon_summernote_options.dropdown_textTickets.AgentEmail + "</a></li>";

            }

            return  tickets_data +
                "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
                "<li><a data-value='{{FirstName}}'>" + neon_summernote_options.dropdown_textTickets.FirstName + "</a></li>" +
                "<li><a data-value='{{LastName}}'>" + neon_summernote_options.dropdown_textTickets.LastName + "</a></li>" +
                "<li><a data-value='{{Email}}'>" + neon_summernote_options.dropdown_textTickets.Email + "</a></li>" +
                "<li><a data-value='{{Address1}}'>" + neon_summernote_options.dropdown_textTickets.Address1 + "</a></li>" +
                "<li><a data-value='{{Address2}}'>" + neon_summernote_options.dropdown_textTickets.Address2 + "</a></li>" +
                "<li><a data-value='{{Address3}}'>" + neon_summernote_options.dropdown_textTickets.Address3 + "</a></li>" +
                "<li><a data-value='{{City}}'>" + neon_summernote_options.dropdown_textTickets.City + "</a></li>" +
                "<li><a data-value='{{State}}'>" + neon_summernote_options.dropdown_textTickets.State + "</a></li>" +
                "<li><a data-value='{{PostCode}}'>" + neon_summernote_options.dropdown_textTickets.PostCode + "</a></li>" +
                "<li><a data-value='{{Country}}'>" + neon_summernote_options.dropdown_textTickets.Country + "</a></li>" +
                "<li><a data-value='{{Currency}}'>" + neon_summernote_options.dropdown_textTickets.Currency + "</a></li>" +
                "<li><a data-value='{{OutstandingExcludeUnbilledAmount}}'>" + neon_summernote_options.dropdown_textTickets.OutstandingExcludeUnbilledAmount + "</a></li>" +
                "<li><a data-value='{{OutstandingIncludeUnbilledAmount}}'>" + neon_summernote_options.dropdown_textTickets.OutstandingIncludeUnbilledAmount + "</a></li>" +
                "<li><a data-value='{{BalanceThreshold}}'>" + neon_summernote_options.dropdown_textTickets.BalanceThreshold + "</a></li>" +
                "<li class='unclick'><a ><b>Invoice Fields</b></a></li>" +
                "<li><a data-value='{{InvoiceNumber}}'>" + neon_summernote_options.dropdown_textTickets.InvoiceNumber + "</a></li>" +
                "<li><a data-value='{{InvoiceGrandTotal}}'>" + neon_summernote_options.dropdown_textTickets.InvoiceGrandTotal + "</a></li>" +
                "<li><a data-value='{{InvoiceOutstanding}}'>" + neon_summernote_options.dropdown_textTickets.InvoiceOutstanding + "</a></li>" + "<li class='unclick'><a ><b>Others</b></a></li>" +
                "<li><a data-value='{{Signature}}'>" + neon_summernote_options.dropdown_textTickets.Signature + "</a></li>";

        },
    }
}(window.jQuery, window.neon_summernote_options);


