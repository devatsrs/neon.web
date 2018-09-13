/**
 * Created by deven on 07/04/2017.
 */
/**
 *  custom dropdown
 *
*/


if( typeof neon_summernote_options == 'undefined') {
    var neon_summernote_options = {
        "leadoptions": true,
        "Crm": true,
        "TicketsSingle": false,
        "invoiceoptions": false,
        "autopaymentoptions": false,
        "Cronjobs": false,
        "estimateoptions": false,
        "ratesheetoptions": false,
        "opportunities": false,
        "tasks": false,
        "Tickets": false,
        "ratetemplateoptions":false
    };
} else {

}
var dropdown_text =  {

    leadoptions:{
        FirstName:"FirstName",
        AccountName:"AccountName",
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
        CurrencySign:"CurrencySign",
        Signature:"Signature",
        OutstandingExcludeUnbilledAmount:"OutstandingExcludeUnbilledAmount",
        OutstandingIncludeUnbilledAmount:"OutstandingIncludeUnbilledAmount",
        BalanceThreshold:"BalanceThreshold",
        AccountBlocked:"AccountBlocked",
        DisputeAmount:"DisputeAmount",
        InvoiceNumber:"InvoiceNumber"


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
        CurrencySign:"CurrencySign",
        InvoiceNumber:"InvoiceNumber",
        InvoiceGrandTotal:"InvoiceGrandTotal",
        InvoiceOutstanding:"InvoiceOutstanding",
        PeriodFrom:"PeriodFrom",
        PeriodTo:"PeriodTo",
        OutstandingExcludeUnbilledAmount:"OutstandingExcludeUnbilledAmount",
        OutstandingIncludeUnbilledAmount:"OutstandingIncludeUnbilledAmount",
        BalanceThreshold:"BalanceThreshold",
        Signature:"Signature",
        CompanyName:"CompanyName",
        InvoiceLink:"InvoiceLink",
        AccountName:"AccountName",
        Signature:"Signature",
    },
    autopaymentoptions:{
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
        PaidAmount:"PaidAmount",
        PaidStatus:"PaidStatus",
        PaymentMethod:"PaymentMethod",
        PaymentNotes:"PaymentNotes",
        CurrencySign:"CurrencySign",
        InvoiceNumber:"InvoiceNumber",
        InvoiceGrandTotal:"InvoiceGrandTotal",
        InvoiceOutstanding:"InvoiceOutstanding",
        OutstandingExcludeUnbilledAmount:"OutstandingExcludeUnbilledAmount",
        OutstandingIncludeUnbilledAmount:"OutstandingIncludeUnbilledAmount",
        BalanceThreshold:"BalanceThreshold",
        Signature:"Signature",
        CompanyName:"CompanyName",
        InvoiceLink:"InvoiceLink",
        AccountName:"AccountName"
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
        CurrencySign:"CurrencySign",
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
        AccountName:"AccountName",
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
        CurrencySign:"CurrencySign",
        OutstandingExcludeUnbilledAmount:"OutstandingExcludeUnbilledAmount",
        OutstandingIncludeUnbilledAmount:"OutstandingIncludeUnbilledAmount",
        BalanceThreshold:"BalanceThreshold",
        Signature:"Signature",
        InvoiceNumber:"InvoiceNumber",
        InvoiceGrandTotal:"InvoiceGrandTotal",
        InvoiceOutstanding:"InvoiceOutstanding",
    },
    ratetemplateoptions:{
        AccountName:"AccountName",
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
        CurrencySign:"CurrencySign",
        Signature:"Signature",
        OutstandingExcludeUnbilledAmount:"OutstandingExcludeUnbilledAmount",
        OutstandingIncludeUnbilledAmount:"OutstandingIncludeUnbilledAmount",
        BalanceThreshold:"BalanceThreshold",
        TrunkPrefix:"TrunkPrefix",
        TrunkName:"TrunkName"

    },
    accountbalanceemailreminder:{
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
        CurrencySign:"CurrencySign",
        Signature:"Signature",
        AccountBalance:"AccountBalance",
        AccountExposure:"AccountExposure",
        CompanyName:"CompanyName",
        AccountName:"AccountName",
    },
};


var neon_summernote_dropdown_tpl = {
    "leadoptions": function () {
        return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
            "<li><a data-value='{{AccountName}}'>" + dropdown_text.leadoptions.AccountName + "</a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.leadoptions.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.leadoptions.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.leadoptions.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.leadoptions.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.leadoptions.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.leadoptions.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.leadoptions.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.leadoptions.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.leadoptions.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.leadoptions.Country + "</a></li>" +
            "<li><a data-value='{{Currency}}'>" + dropdown_text.leadoptions.Currency + "</a></li>" +
            "<li><a data-value='{{CurrencySign}}'>" + dropdown_text.leadoptions.CurrencySign + "</a></li>" +
            //"<li><a data-value='{{OutstandingExcludeUnbilledAmount}}'>" + dropdown_text.leadoptions.OutstandingExcludeUnbilledAmount + "</a></li>" +
            //"<li><a data-value='{{OutstandingIncludeUnbilledAmount}}'>" + dropdown_text.leadoptions.OutstandingIncludeUnbilledAmount + "</a></li>" +
            "<li><a data-value='{{BalanceThreshold}}'>" + dropdown_text.leadoptions.BalanceThreshold + "</a></li>" +
            "<li><a data-value='{{AccountBalance}}'>" + dropdown_text.accountbalanceemailreminder.AccountBalance + "</a></li>" +
            "<li><a data-value='{{AccountExposure}}'>" + dropdown_text.accountbalanceemailreminder.AccountExposure + "</a></li>" +
            "<li><a data-value='{{AccountBlocked}}'>" + dropdown_text.leadoptions.AccountBlocked + "</a></li>" +
            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>" +
            "<li class='unclick'><a ><b>Others</b></a></li>" +
            "<li><a data-value='{{Signature}}'>" + dropdown_text.leadoptions.Signature + "</a></li>" +
            "<li class='unclick'><a ><b>Disputes</b></a></li>" +
            "<li><a data-value='{{DisputeAmount}}'>" + dropdown_text.leadoptions.DisputeAmount + "</a></li>" +
            "<li><a data-value='{{InvoiceNumber}}'>" + dropdown_text.leadoptions.InvoiceNumber + "</a></li>"
            ;
    },
    "ratetemplateoptions": function () {
        return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
            "<li><a data-value='{{AccountName}}'>" + dropdown_text.ratetemplateoptions.AccountName + "</a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.ratetemplateoptions.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.ratetemplateoptions.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.ratetemplateoptions.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.ratetemplateoptions.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.ratetemplateoptions.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.ratetemplateoptions.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.ratetemplateoptions.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.ratetemplateoptions.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.ratetemplateoptions.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.ratetemplateoptions.Country + "</a></li>" +
            "<li><a data-value='{{Currency}}'>" + dropdown_text.ratetemplateoptions.Currency + "</a></li>" +
            "<li><a data-value='{{CurrencySign}}'>" + dropdown_text.ratetemplateoptions.CurrencySign + "</a></li>" +
            //"<li><a data-value='{{OutstandingExcludeUnbilledAmount}}'>" + dropdown_text.ratetemplateoptions.OutstandingExcludeUnbilledAmount + "</a></li>" +
            //"<li><a data-value='{{OutstandingIncludeUnbilledAmount}}'>" + dropdown_text.ratetemplateoptions.OutstandingIncludeUnbilledAmount + "</a></li>" +
            "<li><a data-value='{{BalanceThreshold}}'>" + dropdown_text.ratetemplateoptions.BalanceThreshold + "</a></li>" +
            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>" +
            "<li class='unclick'><a ><b>RateSheet Fields</b></a></li>" +
            "<li><a data-value='{{TrunkPrefix}}'>" + dropdown_text.ratetemplateoptions.TrunkPrefix + "</a></li>"+
            "<li><a data-value='{{TrunkName}}'>" + dropdown_text.ratetemplateoptions.TrunkName + "</a></li>"+
            "<li class='unclick'><a ><b>Others</b></a></li>" +
            "<li><a data-value='{{Signature}}'>" + dropdown_text.ratetemplateoptions.Signature + "</a></li>";
    },
    "invoiceoptions": function () {

        return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
            "<li><a data-value='{{AccountName}}'>" + dropdown_text.invoiceoptions.AccountName + "</a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.invoiceoptions.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.invoiceoptions.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.invoiceoptions.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.invoiceoptions.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.invoiceoptions.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.invoiceoptions.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.invoiceoptions.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.invoiceoptions.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.invoiceoptions.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.invoiceoptions.Country + "</a></li>" +
            "<li><a data-value='{{Currency}}'>" + dropdown_text.invoiceoptions.Currency + "</a></li>" +
            "<li><a data-value='{{CurrencySign}}'>" + dropdown_text.invoiceoptions.CurrencySign + "</a></li>" +
            "<li class='unclick'><a ><b>Invoice Fields</b></a></li>" +
            "<li><a data-value='{{InvoiceNumber}}'>" + dropdown_text.invoiceoptions.InvoiceNumber + "</a></li>" +
            "<li><a data-value='{{InvoiceGrandTotal}}'>" + dropdown_text.invoiceoptions.InvoiceGrandTotal + "</a></li>" +
            "<li><a data-value='{{InvoiceOutstanding}}'>" + dropdown_text.invoiceoptions.InvoiceOutstanding + "</a></li>" +
            "<li><a data-value='{{InvoiceLink}}'>" + dropdown_text.invoiceoptions.InvoiceLink + "</a></li>" +
            "<li><a data-value='{{PeriodFrom}}'>" + dropdown_text.invoiceoptions.PeriodFrom + "</a></li>" +
            "<li><a data-value='{{PeriodTo}}'>" + dropdown_text.invoiceoptions.PeriodTo + "</a></li>" +
                /* "<li><a data-value='{{OutstandingExcludeUnbilledAmount}}'>" + dropdown_text.invoiceoptions.OutstandingExcludeUnbilledAmount + "</a></li>" +
                 "<li><a data-value='{{OutstandingIncludeUnbilledAmount}}'>" + dropdown_text.invoiceoptions.OutstandingIncludeUnbilledAmount + "</a></li>" +
                 "<li><a data-value='{{BalanceThreshold}}'>" + dropdown_text.invoiceoptions.BalanceThreshold + "</a></li>" +*/
            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>" +
            "<li class='unclick'><a ><b>Others</b></a></li>" +
            "<li><a data-value='{{Signature}}'>" + dropdown_text.invoiceoptions.Signature + "</a></li>" ;
    },
    "autopaymentoptions": function () {

        return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
            "<li><a data-value='{{AccountName}}'>" + dropdown_text.autopaymentoptions.AccountName + "</a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.autopaymentoptions.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.autopaymentoptions.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.autopaymentoptions.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.autopaymentoptions.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.autopaymentoptions.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.autopaymentoptions.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.autopaymentoptions.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.autopaymentoptions.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.autopaymentoptions.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.autopaymentoptions.Country + "</a></li>" +
            "<li><a data-value='{{Currency}}'>" + dropdown_text.autopaymentoptions.Currency + "</a></li>" +
            "<li><a data-value='{{CurrencySign}}'>" + dropdown_text.autopaymentoptions.CurrencySign + "</a></li>" +
            "<li class='unclick'><a ><b>Auto Payment Fields</b></a></li>" +
            "<li><a data-value='{{PaidAmount}}'>" + dropdown_text.autopaymentoptions.PaidAmount + "</a></li>" +
            "<li><a data-value='{{PaidStatus}}'>" + dropdown_text.autopaymentoptions.PaidStatus + "</a></li>" +
            "<li><a data-value='{{PaymentMethod}}'>" + dropdown_text.autopaymentoptions.PaymentMethod + "</a></li>" +
            "<li><a data-value='{{PaymentNotes}}'>" + dropdown_text.autopaymentoptions.PaymentNotes + "</a></li>" +
            "<li class='unclick'><a ><b>Invoice Fields</b></a></li>" +
            "<li><a data-value='{{InvoiceNumber}}'>" + dropdown_text.autopaymentoptions.InvoiceNumber + "</a></li>" +
            "<li><a data-value='{{InvoiceGrandTotal}}'>" + dropdown_text.autopaymentoptions.InvoiceGrandTotal + "</a></li>" +
            "<li><a data-value='{{InvoiceOutstanding}}'>" + dropdown_text.autopaymentoptions.InvoiceOutstanding + "</a></li>" +
            "<li><a data-value='{{InvoiceLink}}'>" + dropdown_text.autopaymentoptions.InvoiceLink + "</a></li>" +
            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>" +
            "<li class='unclick'><a ><b>Others</b></a></li>" +
            "<li><a data-value='{{Signature}}'>" + dropdown_text.autopaymentoptions.Signature + "</a></li>" ;
    },
    "tasks": function () {
        return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.leadoptions.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.leadoptions.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.leadoptions.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.leadoptions.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.leadoptions.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.leadoptions.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.leadoptions.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.leadoptions.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.leadoptions.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.leadoptions.Country + "</a></li>" +
            "<li class='unclick'><a ><b>Tasks Fields</b></a></li>" +
            "<li><a data-value='{{subject}}'>" + dropdown_text.tasks.Subject + "</a></li>" +
            "<li><a data-value='{{User}}'>" + dropdown_text.tasks.User + "</a></li>" +
            "<li><a data-value='{{Comment}}'>" + dropdown_text.tasks.Comment + "</a></li>" +
            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>" ;
    },
    "opportunities": function () {
        return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.leadoptions.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.leadoptions.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.leadoptions.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.leadoptions.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.leadoptions.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.leadoptions.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.leadoptions.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.leadoptions.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.leadoptions.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.leadoptions.Country + "</a></li>" +
            "<li class='unclick'><a ><b>Opportunity Fields</b></a></li>" +
            "<li><a data-value='{{subject}}'>" + dropdown_text.opportunities.Subject + "</a></li>" +
            "<li><a data-value='{{User}}'>" + dropdown_text.opportunities.User + "</a></li>" +
            "<li><a data-value='{{Comment}}'>" + dropdown_text.opportunities.Comment + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.opportunities.Logo + "</a></li>" +
            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>" ;
    },
    "ratesheetoptions": function () {
        return  "<li><a data-value='{{FirstName}}'>" + dropdown_text.ratesheetoptions.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.ratesheetoptions.LastName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.ratesheetoptions.RateGeneratorName + "</a></li>" +
            "<li><a data-value='{{RateTableName}}'>" + dropdown_text.ratesheetoptions.RateTableName + "</a></li>" +
            "<li><a data-value='{{EffectiveDate}}'>" + dropdown_text.ratesheetoptions.EffectiveDate + "</a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.ratesheetoptions.CompanyName + "</a></li>" ;
    },
    "estimateoptions": function () {
        return "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
            "<li><a data-value='{{AccountName}}'>" + dropdown_text.estimateoptions.AccountName + "</a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.estimateoptions.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.estimateoptions.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.estimateoptions.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.estimateoptions.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.estimateoptions.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.estimateoptions.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.estimateoptions.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.estimateoptions.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.estimateoptions.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.estimateoptions.Country + "</a></li>" +
            "<li><a data-value='{{Currency}}'>" + dropdown_text.estimateoptions.Currency + "</a></li>" +
            "<li><a data-value='{{CurrencySign}}'>" + dropdown_text.estimateoptions.CurrencySign + "</a></li>" +
            "<li class='unclick'><a ><b>Estimate Fields</b></a></li>" +
            "<li><a data-value='{{EstimateNumber}}'>" + dropdown_text.estimateoptions.EstimateNumber + "</a></li>" + "<li><a data-value='{{EstimateGrandTotal}}'>" + dropdown_text.estimateoptions.EstimateGrandTotal + "</a></li>" +
            "<li><a data-value='{{EstimateLink}}'>" + dropdown_text.estimateoptions.EstimateLink + "</a></li>" +
            "<li><a data-value='{{Comment}}'>" + dropdown_text.estimateoptions.Comment + "</a></li>" +
            "<li><a data-value='{{User}}'>" + dropdown_text.estimateoptions.User + "</a></li>" +

            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>" +
            "<li class='unclick'><a ><b>Others</b></a></li>" +
            "<li><a data-value='{{Signature}}'>" + dropdown_text.estimateoptions.Signature + "</a></li>" ;

    },
    "Crm": function () {
        return "<li><a data-value='{{AccountName}}'>" + dropdown_text.Crm.AccountName + "</a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.Crm.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.Crm.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.Crm.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.Crm.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.Crm.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.Crm.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.Crm.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.Crm.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.Crm.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.Crm.Country + "</a></li>" +
            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>" +
            "<li class='unclick'><a ><b>Others</b></a></li>" +
            "<li><a data-value='{{Signature}}'>" + dropdown_text.Crm.Signature + "</a></li>";
    },
    "TicketsSingle": function () {
        return "<li class='unclick'><a ><b>Ticket Fields</b></a></li>" +
            "<li><a data-value='{{Subject}}'>" + dropdown_text.TicketsSingle.Subject + "</a></li>" +
            "<li><a data-value='{{TicketID}}'>" + dropdown_text.TicketsSingle.TicketID + "</a></li>" +
            "<li><a data-value='{{Requester}}'>" + dropdown_text.TicketsSingle.Requester + "</a></li>" +
            "<li><a data-value='{{RequesterName}}'>" + dropdown_text.TicketsSingle.RequesterName + "</a></li>" +
            "<li><a data-value='{{Status}}'>" + dropdown_text.TicketsSingle.Status + "</a></li>" +
            "<li><a data-value='{{Priority}}'>" + dropdown_text.TicketsSingle.Priority + "</a></li>" +
            "<li><a data-value='{{Description}}'>" + dropdown_text.TicketsSingle.Description + "</a></li>" +
            "<li><a data-value='{{Group}}'>" + dropdown_text.TicketsSingle.Group + "</a></li>" +
            "<li><a data-value='{{Type}}'>" + dropdown_text.TicketsSingle.Type + "</a></li>" +
            "<li><a data-value='{{Date}}'>" + dropdown_text.TicketsSingle.Date + "</a></li>" +
            "<li><a data-value='{{AgentName}}'>" + dropdown_text.TicketsSingle.AgentName + "</a></li>" +
            "<li><a data-value='{{AgentEmail}}'>" + dropdown_text.TicketsSingle.AgentEmail + "</a></li>" +
            "<li><a data-value='{{TicketUrl}}'>" + dropdown_text.TicketsSingle.TicketUrl + "</a></li>" +
            "<li><a data-value='{{TicketCustomerUrl}}'>" + dropdown_text.TicketsSingle.TicketCustomerUrl + "</a></li>" +
            "<li><a data-value='{{Comment}}'>" + dropdown_text.TicketsSingle.Comment + "</a></li>" +
            "<li><a data-value='{{Notebody}}'>" + dropdown_text.TicketsSingle.Notebody + "</a></li>" +
            "<li><a data-value='{{NoteUser}}'>" + dropdown_text.TicketsSingle.NoteUser + "</a></li>" +
            "<li class='unclick'><a ><b>Company Fields</b></a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Company.CompanyName + "</a></li>" +
            "<li><a data-value='{{CompanyVAT}}'>" + dropdown_text.Company.Vat + "</a></li>" +
            "<li><a data-value='{{CompanyAddress1}}'>" + dropdown_text.Company.Address1 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress2}}'>" + dropdown_text.Company.Address2 + "</a></li>" +
            "<li><a data-value='{{CompanyAddress3}}'>" + dropdown_text.Company.Address3 + "</a></li>" +
            "<li><a data-value='{{CompanyCity}}'>" + dropdown_text.Company.City + "</a></li>" +
            "<li><a data-value='{{CompanyPostCode}}'>" + dropdown_text.Company.PostCode + "</a></li>" +
            "<li><a data-value='{{CompanyCountry}}'>" + dropdown_text.Company.Country + "</a></li>" +
            "<li><a data-value='{{Logo}}'>" + dropdown_text.Company.Logo + "</a></li>"   ;
    },
    "Cronjobs": function () {

        return "<li><a data-value='{{KillCommand}}'>" + dropdown_text.Cronjobs.KillCommand + "</a></li>" +
            "<li><a data-value='{{ReturnStatus}}'>" + dropdown_text.Cronjobs.ReturnStatus + "</a></li>" +
            "<li><a data-value='{{DetailOutput}}'>" + dropdown_text.Cronjobs.DetailOutput + "</a></li>" +
            "<li><a data-value='{{Minute}}'>" + dropdown_text.Cronjobs.Minute + "</a></li>" +
            "<li><a data-value='{{JobTitle}}'>" + dropdown_text.Cronjobs.JobTitle + "</a></li>" +
            "<li><a data-value='{{PID}}'>" + dropdown_text.Cronjobs.PID + "</a></li>" +
            "<li><a data-value='{{CompanyName}}'>" + dropdown_text.Cronjobs.CompanyName + "</a></li>" +
            "<li><a data-value='{{Url}}'>" + dropdown_text.Cronjobs.Url + "</a></li>";
    },
    "Tickets": function () {
        var tickets_data = '';
        if (tickets_enable == 1) {
            tickets_data = "<li class='unclick'><a ><b>Ticket Fields</b></a></li>" + "<li><a data-value='{{Subject}}'>" + dropdown_text.Tickets.Subject + "</a></li>" +
                "<li><a data-value='{{TicketID}}'>" + dropdown_text.Tickets.TicketID + "</a></li>" +
                "<li><a data-value='{{Requester}}'>" + dropdown_text.Tickets.Requester + "</a></li>" +
                "<li><a data-value='{{RequesterName}}'>" + dropdown_text.Tickets.RequesterName + "</a></li>" +
                "<li><a data-value='{{Status}}'>" + dropdown_text.Tickets.Status + "</a></li>" +
                "<li><a data-value='{{Priority}}'>" + dropdown_text.Tickets.Priority + "</a></li>" +
                "<li><a data-value='{{Description}}'>" + dropdown_text.Tickets.Description + "</a></li>" +
                "<li><a data-value='{{Group}}'>" + dropdown_text.Tickets.Group + "</a></li>" +
                "<li><a data-value='{{Type}}'>" + dropdown_text.Tickets.Type + "</a></li>" +
                "<li><a data-value='{{Date}}'>" + dropdown_text.Tickets.Date + "</a></li>" +
                "<li><a data-value='{{AgentName}}'>" + dropdown_text.Tickets.AgentName + "</a></li>" +
                "<li><a data-value='{{AgentEmail}}'>" + dropdown_text.Tickets.AgentEmail + "</a></li>";

        }

        return  tickets_data +
            "<li class='unclick'><a ><b>Account Fields</b></a></li>" +
            "<li><a data-value='{{FirstName}}'>" + dropdown_text.Tickets.FirstName + "</a></li>" +
            "<li><a data-value='{{LastName}}'>" + dropdown_text.Tickets.LastName + "</a></li>" +
            "<li><a data-value='{{Email}}'>" + dropdown_text.Tickets.Email + "</a></li>" +
            "<li><a data-value='{{Address1}}'>" + dropdown_text.Tickets.Address1 + "</a></li>" +
            "<li><a data-value='{{Address2}}'>" + dropdown_text.Tickets.Address2 + "</a></li>" +
            "<li><a data-value='{{Address3}}'>" + dropdown_text.Tickets.Address3 + "</a></li>" +
            "<li><a data-value='{{City}}'>" + dropdown_text.Tickets.City + "</a></li>" +
            "<li><a data-value='{{State}}'>" + dropdown_text.Tickets.State + "</a></li>" +
            "<li><a data-value='{{PostCode}}'>" + dropdown_text.Tickets.PostCode + "</a></li>" +
            "<li><a data-value='{{Country}}'>" + dropdown_text.Tickets.Country + "</a></li>" +
            "<li><a data-value='{{Currency}}'>" + dropdown_text.Tickets.Currency + "</a></li>" +
            "<li><a data-value='{{CurrencySign}}'>" + dropdown_text.Tickets.CurrencySign + "</a></li>" +
            //"<li><a data-value='{{OutstandingExcludeUnbilledAmount}}'>" + dropdown_text.Tickets.OutstandingExcludeUnbilledAmount + "</a></li>" +
            //"<li><a data-value='{{OutstandingIncludeUnbilledAmount}}'>" + dropdown_text.Tickets.OutstandingIncludeUnbilledAmount + "</a></li>" +
            "<li><a data-value='{{BalanceThreshold}}'>" + dropdown_text.Tickets.BalanceThreshold + "</a></li>" +
            "<li class='unclick'><a ><b>Invoice Fields</b></a></li>" +
            "<li><a data-value='{{InvoiceNumber}}'>" + dropdown_text.Tickets.InvoiceNumber + "</a></li>" +
            "<li><a data-value='{{InvoiceGrandTotal}}'>" + dropdown_text.Tickets.InvoiceGrandTotal + "</a></li>" +
            "<li><a data-value='{{InvoiceOutstanding}}'>" + dropdown_text.Tickets.InvoiceOutstanding + "</a></li>" + "<li class='unclick'><a ><b>Others</b></a></li>" +
            "<li><a data-value='{{Signature}}'>" + dropdown_text.Tickets.Signature + "</a></li>";

    },
};
(function (factory) {
    /* global define */
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function ($) {
    // Extends lang for print plugin.
    $.extend(true, $.summernote.lang, {
        'en-US': {
            neon_placeholders: {
                neon_placeholders: 'Placeholder'
            }
        }
    });

// Extends plugins for print plugin.
    $.extend($.summernote.plugins, {

        neon_placeholders: function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var options = context.options;
            var lang = options.langInfo;

            var defaultOptionsCache = {
                "leadoptions":false ,
                "TicketsSingle":false,
                "invoiceoptions":false ,
                "Cronjobs":false,
                "estimateoptions":false,
                "ratesheetoptions":false,
                "opportunities":false,
                "tasks":false,
                "Crm":false,
                "Tickets":false,
            };

            if(typeof context.options.defaultOptions == 'undefined'){
                 return false;
            }

            self.initialize = function () {

                if(typeof context.options.defaultOptions == 'undefined'){
                    return false;
                }
                defaultOptionsCache = $.extend(true, defaultOptionsCache, context.options.defaultOptions);
            },

            context.memo('button.neon_placeholders', function () {

                var defaultOptionsCache_ = $.extend(true, defaultOptionsCache, context.options.defaultOptions);

                var list = '';
                for(var key in defaultOptionsCache_) {
                    if (neon_summernote_dropdown_tpl[key] !== undefined) {
                        if (defaultOptionsCache_[key]) {
                            list = list + neon_summernote_dropdown_tpl[key]();
                        }
                    }
                }

                if (list == '') {
                    return false;
                }

                var $neonDropdown = ui.buttonGroup([

                    ui.button({
                        className: 'dropdown-toggle',
                        contents: lang.neon_placeholders.neon_placeholders + ' ' + ui.icon(options.icons.caret, 'span'),
                        tooltip: lang.neon_placeholders.neon_placeholders,
                        data: {
                            toggle: 'dropdown'
                        },
                        click: function() {
                            // Cursor position must be saved because is lost when dropdown is opened.
                            context.invoke('editor.saveRange');
                        }

                    }),
                    ui.dropdown({
                        className: 'dropdown-style drop-default class_neon_placeholders',
                        contents: list,
                        callback: function ($dropdown) {
                            $dropdown.find('li').each(function () {

                                $(this).click(function (e) {

                                    var placeholder_text = $(this).find("a").attr("data-value");

                                    context.invoke('editor.restoreRange');
                                    context.invoke('editor.focus');
                                    context.invoke("editor.insertText", placeholder_text);
                                    e.preventDefault();

                                });
                            });
                        }
                    })
                ]).render();
                return $neonDropdown;

            });

            //return false;
        }
    });
}));
