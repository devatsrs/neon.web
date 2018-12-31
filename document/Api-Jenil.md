# API

### POST checkBalance

http://speakintelligence.neon-soft.com/api/account/checkBalance

Perams:

"AccountID"
"AccountNo"

Return

    Example:
        Request:
        AccountID: "6727"

        Response:
            {
                "status": "success",
                "data": {
                    "has_balance": 0,
                    "amount": "0.00"
                }
            }



### POST getTransactions

http://speakintelligence.neon-soft.com/api/account/getTransactions

Perams:

"AccountID"
"AccountNo"
"StartDate"
"EndDate"

Return

    Example:
        Request:
        AccountID: "6728"
        StartDate : "2018-12-24"
        EndDate : "2018-12-24"

        Response:

            {
                "status": "success",
                "data": [
                    {
                        "PaymentID": 5540,
                        "AccountID": 6728,
                        "Amount": "2.20000000",
                        "PaymentType": "Payment In",
                        "CurrencyID": 9,
                        "PaymentDate": "2018-12-24 00:00:00",
                        "CreatedBy": "API",
                        "PaymentProof": null,
                        "InvoiceNo": null,
                        "PaymentMethod": "CREDIT CARD",
                        "Notes": "Stripe transaction_id ch_1DkmM9CLEhHAk25KmjYPpui0",
                        "Recall": 0,
                        "RecallReasoan": "",
                        "RecallBy": ""
                    },
                    {
                        "PaymentID": 5543,
                        "AccountID": 6728,
                        "Amount": "10.00000000",
                        "PaymentType": "Payment In",
                        "CurrencyID": 9,
                        "PaymentDate": "2018-12-24 00:00:00",
                        "CreatedBy": "API",
                        "PaymentProof": null,
                        "InvoiceNo": null,
                        "PaymentMethod": "CREDIT CARD",
                        "Notes": "AuthorizeNet transaction_id 60114201500",
                        "Recall": 0,
                        "RecallReasoan": "",
                        "RecallBy": ""
                    },
                    {
                        "PaymentID": 5544,
                        "AccountID": 6728,
                        "Amount": "5.00000000",
                        "PaymentType": "Payment In",
                        "CurrencyID": 9,
                        "PaymentDate": "2018-12-24 00:00:00",
                        "CreatedBy": "API",
                        "PaymentProof": null,
                        "InvoiceNo": null,
                        "PaymentMethod": "CREDIT CARD",
                        "Notes": "Stripe transaction_id ch_1DkrKfCLEhHAk25KKlNe03Ou",
                        "Recall": 0,
                        "RecallReasoan": "",
                        "RecallBy": ""
                    },
                    {
                        "PaymentID": 5545,
                        "AccountID": 6728,
                        "Amount": "15.00000000",
                        "PaymentType": "Payment In",
                        "CurrencyID": 9,
                        "PaymentDate": "2018-12-24 00:00:00",
                        "CreatedBy": "API",
                        "PaymentProof": null,
                        "InvoiceNo": null,
                        "PaymentMethod": "CREDIT CARD",
                        "Notes": "AuthorizeNet transaction_id 60114201619",
                        "Recall": 0,
                        "RecallReasoan": "",
                        "RecallBy": ""
                    },
                    {
                        "PaymentID": 5541,
                        "AccountID": 6728,
                        "Amount": "500.00000000",
                        "PaymentType": "Payment Out",
                        "CurrencyID": 0,
                        "PaymentDate": "2018-12-24 00:00:00",
                        "CreatedBy": "API",
                        "PaymentProof": null,
                        "InvoiceNo": null,
                        "PaymentMethod": "",
                        "Notes": null,
                        "Recall": 0,
                        "RecallReasoan": "",
                        "RecallBy": ""
                    },
                    {
                        "PaymentID": 5542,
                        "AccountID": 6728,
                        "Amount": "500.00000000",
                        "PaymentType": "Payment Out",
                        "CurrencyID": 0,
                        "PaymentDate": "2018-12-24 00:00:00",
                        "CreatedBy": "API",
                        "PaymentProof": null,
                        "InvoiceNo": null,
                        "PaymentMethod": "",
                        "Notes": null,
                        "Recall": 0,
                        "RecallReasoan": "",
                        "RecallBy": ""
                    }
                ]
            }



### POST getAutoDepositSettings

http://speakintelligence.neon-soft.com/api/getAutoDepositSettings

Perams:

"AccountID"
"AccountNo"

Return

    Example:
        Request:
        AccountID: "6725"


        Response:

           {
               "status": "success",
               "data": [
                   {
                       "AutoTopup": 1,
                       "MinThreshold": 40,
                       "TopupAmount": "560.00"
                   }
               ]
           }



### POST setAutoDepositSettings

http://speakintelligence.neon-soft.com/api/setAutoDepositSettings

Perams:

"AccountID"/"AccountNo"
"AutoTopup"
"MinThreshold"
"TopupAmount"

Description:
    If AccountID(AccountID) not found in table then will create New Entry in table(tblAccountPaymentAutomation).

Return

    Example:
        Request:
        AccountID: "6725"
        AutoTopup : 1
        MinThreshold :40
        TopupAmount : 560


        Response:

           {
               "status": "success",
               "message": "Auto Deposit Settings Updated Successfully."
           }



### POST getAutoOutPaymentSettings

http://speakintelligence.neon-soft.com/api/getAutoOutPaymentSettings

Perams:

"AccountID"
"AccountNo"

Return

    Example:
        Request:
        AccountID: "6728"


        Response:

            {
                "status": "success",
                "data": [
                    {
                        "AutoOutpayment": 1,
                        "OutPaymentThreshold": 5,
                        "OutPaymentAmount": "180.00"
                    }
                ]
            }



### POST setAutoOutPaymentSettings

http://speakintelligence.neon-soft.com/api/setAutoOutPaymentSettings

Description:
    If AccountID(AccountID) not found then will create New Entry in table(tblAccountPaymentAutomation).


Perams:

"AccountID"/"AccountNo"
"AutoTopup"
"MinThreshold"
"TopupAmount"


Return

    Example:
        Request:
        AccountID: "6728"
        AutoOutpayment : 1
        OutPaymentThreshold :5
        OutPaymentAmount : 180


        Response:

          {
              "status": "success",
              "message": "Auto Out Deposit Settings Updated Successfully."
          }


### POST setLowBalanceNotification

http://speakintelligence.neon-soft.com/api/setLowBalanceNotification

Perams:

Request:

       AccountID /AccountNo
       Status
       Email
       Period
       Interval
       StartTime
       EmailTemplateID
       Day
       SendCopyToAccountOwner
       BalanceThreshold

Return

    Example:
        Request:

        AccountID: 6728
        Status :1
        Email : test123@gmail.com
        Period : DAILY
        Interval : 2
        StartTime : 9:00:00 AM
        EmailTemplateID : 2
        Day : ["SUN","MON","TUE","WED","THU","FRI","SAT"]
        SendCopyToAccountOwner : 1
        BalanceThreshold : 10

        Response:

          {
              "status": "success",
              "message": "Updated Successfully."
          }


### POST getLowBalanceNotification

http://speakintelligence.neon-soft.com/api/getLowBalanceNotification

Perams:

"AccountID"
"AccountNo"

Return

    Example:
        Request:
        AccountID: "6728"

        Response:

            {
                "status": "success",
                "data": "{\"ReminderEmail\":\"bhavin@code-desk.com\",\"TemplateID\":\"17\",\"Time\":\"DAILY\",\"Interval\":\"1\",\"Day\":[\"SUN\",\"MON\",\"TUE\",\"WED\",\"THU\",\"FRI\",\"SAT\"],\"StartTime\":\"8:00:00 AM\",\"LastRunTime\":\"2016-12-07 08:01:00\",\"NextRunTime\":\"2016-12-08 08:00:00\"}"
            }


### POST requestFund

http://speakintelligence.neon-soft.com/api/account/requestFund

Perams:

"AccountID"/"AccountNo"
Amount

Return

    Example:
        Request:
        AccountID: "6728"
        Amount   : 500


        Response:

            {
                "status": "success",
                "data": {
                    "RequestFundID": 5548
                }
            }


### POST depositFund

http://speakintelligence.neon-soft.com/api/account/depositFund

Perams:

"AccountID"/"AccountNo"
Amount
BillingClassID

Return

    Example:
        Request:
        AccountID: "6725"/AccountNo : "dev-0514"
        Amount   : 5

        Response:

            {
                "status": "success",
                "PaymentResponse": {
                    "PaymentMethod": "CREDIT CARD",
                    "transaction_notes": "AuthorizeNet transaction_id 60114209662",
                    "transaction_id": "60114209662"
                },
                "InvoiceResponse": {
                    "status": "success",
                    "message": "Invoice Successfully Created.",
                    "LastInvoiceID": 122046
                }
            }




### POST Create Account

http://speakintelligence.neon-soft.com/api/account/create

Params:

	Number
	AccountName
	FirstName
	LastName
	Phone
	Address1
	Address2
	City
	Email
	BillingEmail
	OwnerID
	IsVendor
	IsCustomer
	IsReseller
	Currency
	Country
	CustomerPanelPassword
	VatNumber
	Language
	BillingType
	BillingClass
	BillingCycleType
	BillingCycleValue
	BillingStartDate
	NextInvoiceDate
	ResellerEmail
	ResellerPassword
	ReSellerAllowWhiteLabel
	ReSellerDomainUrl

Return

    Example:
        Request:
        Number:[Mandatrory if not provided system will generate as agreed formulla]
        AccountName:20181314[Mandatory]
        FirstName:Miss
        LastName:Summera
        Phone:080012345
        Address1:land
        Address2:netherland
        City: City
        Email:work@gmail.com
        BillingEmail:work@gmail.com
        OwnerID:72[Mandatory]
        IsVendor:0
        IsCustomer:0
        IsReseller:1
        Currency:USD[Mandatory]
        Country:Netherlands[Mandatory]
        CustomerPanelPassword:Hello
        VatNumber:12345[Mandatory]
        Language:English
        BillingType:Prepaid [Mandatory if billing needs to be add with account]
        BillingClass:GIRISH [Mandatory if billing needs to be add with account]
        BillingCycleType:weekly [Mandatory if billing needs to be add with account]
        BillingCycleValue:monday
        BillingStartDate
        NextInvoiceDate
        ResellerEmail:reseller@lda.com [Mandatory if reseller needs to be add with account]
        ResellerPassword:reseller [Mandatory if reseller needs to be add with account]
        ReSellerAllowWhiteLabel:1
        ReSellerDomainUrl:http://speakintelligence.neon-soft.com/accounts/

        Response:

            {
                "status": "success",
                "message": "Account Successfully Created",
                "Account ID": 6743,
                "redirect": "http://speakintelligence.neon-soft.com/accounts/6743/edit"
            }
### POST Add Service Template

http://speakintelligence.neon-soft.com/api/account/add_servicetemaplate

Params:

JSON Request

Return

    Example:
        Request:
        {
            "Name": "APITempalte 11", [Mandatory]
        	"Currency": "GBP", [Mandatory]
        	"ServiceId": "1", [Mandatory]
        	"OutboundDiscountPlanId": "",
        	"InboundDiscountPlanId": "",
        	"OutboundRateTableId": "",
        	"DynamicFields": [

                {
                    "Name": "SI Product Ref",
                    "Value": "745"
                }
            ]
        }

        Response:

            {
                "status": "success",
                "message": "Service Template Successfully Created",
                "newcreated": {
                    "Name": "APITempalte 12",
                    "CurrencyId": 2,
                    "updated_at": "2018-12-26 12:33:05",
                    "created_at": "2018-12-26 12:33:05",
                    "ServiceTemplateId": 97
                }
            }
### POST Add Service Purchased

http://speakintelligence.neon-soft.com/api/account/createAccount

Params:

	Number
	ServiceTemaplate
	NumberPurchased
	InboundTariffCategory


Return

    Example:
        Request:
            Number:6746[Mandatory]
        	ServiceTemaplate: {"Name": "SI Product Ref","Value": "RFP"}[Mandatory]
        	NumberPurchased:08004570 [Mandatory]
        	InboundTariffCategory

        Response:

           {
               "status": "success",
               "message": "Account Service Successfully Added"
           }

cy
	Country
	CustomerPanelPassword
	VatNumber
	Language
	BillingType
	BillingClass
	BillingCycleType
	BillingCycleValue
	BillingStartDate
	NextInvoiceDate
	ResellerEmail
	ResellerPassword
	ReSellerAllowWhiteLabel
	ReSellerDomainUrl

Return

    Example:
        Request:
        Number:[Mandatrory if not provided system will generate as agreed formulla]
        AccountName:20181314[Mandatory]
        FirstName:Miss
        LastName:Summera
        Phone:080012345
        Address1:land
        Address2:netherland
        City: City
        Email:work@gmail.com
        BillingEmail:work@gmail.com
        OwnerID:72[Mandatory]
        IsVendor:0
        IsCustomer:0
        IsReseller:1
        Currency:USD[Mandatory]
        Country:Netherlands[Mandatory]
        CustomerPanelPassword:Hello
        VatNumber:12345[Mandatory]
        Language:English
        BillingType:Prepaid [Mandatory if billing needs to be add with account]
        BillingClass:GIRISH [Mandatory if billing needs to be add with account]
        BillingCycleType:weekly [Mandatory if billing needs to be add with account]
        BillingCycleValue:monday
        BillingStartDate
        NextInvoiceDate
        ResellerEmail:reseller@lda.com [Mandatory if reseller needs to be add with account]
        ResellerPassword:reseller [Mandatory if reseller needs to be add with account]
        ReSellerAllowWhiteLabel:1
        ReSellerDomainUrl:http://speakintelligence.neon-soft.com/accounts/

        Response:

            {
                "status": "success",
                "message": "Account Successfully Created",
                "Account ID": 6743,
                "redirect": "http://speakintelligence.neon-soft.com/accounts/6743/edit"
            }
### POST Add Service Template

http://speakintelligence.neon-soft.com/api/serviceTemplate/createServiceTemplate

Params:

JSON Request

Return

    Example:
        Request:
        {
            "Name": "APITempalte 11", [Mandatory]
        	"Currency": "GBP", [Mandatory]
        	"ServiceId": "1", [Mandatory]
        	"OutboundDiscountPlanId": "",
        	"InboundDiscountPlanId": "",
        	"OutboundRateTableId": "",
        	"DynamicFields": [

                {
                    "Name": "SI Product Ref",
                    "Value": "745"
                }
            ]
        }

        Response:

            {
                "status": "success",
                "message": "Service Template Successfully Created",
                "newcreated": {
                    "Name": "APITempalte 12",
                    "CurrencyId": 2,
                    "updated_at": "2018-12-26 12:33:05",
                    "created_at": "2018-12-26 12:33:05",
                    "ServiceTemplateId": 97
                }
            }
### POST Add Service Purchased

http://speakintelligence.neon-soft.com/api/account/createService

Params:

	Number
	ServiceTemaplate
	NumberPurchased
	InboundTariffCategoryId


Return

    Example:
        Request:
            Number:6746[Mandatory]
        	ServiceTemaplate: {"Name": "SI Product Ref","Value": "RFP"}[Mandatory]
        	NumberPurchased:08004570 [Mandatory]
        	InboundTariffCategoryId

        Response:

           {
               "status": "success",
               "message": "Account Service Successfully Added"
           }

