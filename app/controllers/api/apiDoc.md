# API Documentation #


#### Login ###
* **URL**

    http://neon-soft.com/api/login

* **Method:**

    The request type

    POST

* **Parameters:**

    1. EmailAddress
    2. password

* **Output Format**

    JSON

* **Success Response:**
    
    When there is success reponse found api will return response in following json format.
    which will have 2 keys like "status" and "message".
    
          {
              "status": "Success",
              "message": "Login Success"
          }
    

* **Error Response:**
    
    Whenever there is an error it will return as follows.
    
        {
            "status": "failed",
            "message": "Not authorized. Please Login"
        }

#### Logout ###
* **URL**

    http://neon-soft.com/api/logout

* **Method:**

    The request type
    
    GET

* **Output Format**

    JSON

* **Success Response:**
    
    When there is success reponse found api will return response in following json format.
    which will have 2 keys like "status" and "message".
    
          {
              "status": "Success",
              "message": "Logout Success"
          }
    

#### Get Currency List ###

* **URL**

    http://neon-soft.com/api/currency/list

* **Method:**

    The request type
    
    GET

* **Output Format**

    JSON

* **Success Response:**

    When there is success reponse found api will return response in following json format.
    which will have 2 keys like "status" and "data" which will contain all following columns from neon database.
    
        {
            "status": "success",
            "data": [
              {
                  "CurrencyId": 2,
                  "Symbol": "£",
                  "Code": "GBP",
                  "Description": "Great Britain Pound"
              },
              {
                  "CurrencyId": 3,
                  "Symbol": "$",
                  "Code": "USD",
                  "Description": "United States Dollars"
              }
            ]
        }
    

#### Get Billing Type List ###
* **URL**

    http://neon-soft.com/api/billingType/list

* **Method:**

    The request type
    
    GET

* **Output Format**

    JSON

* **Success Response:**

  When there is success reponse found api will return response in following json format.
  which will have 2 keys like "status" and "data" which will contain all following columns from neon database.

      {
          "status": "success",
          "data": {
              "1": "Prepaid",
              "2": "Postpaid",
              "": "Select Billing Type"
          }
      }


#### Get Billing Cycle List ###
* **URL**

    http://neon-soft.com/api/billingCycle/list

* **Method:**

    The request type
    
    GET

* **Output Format**

    JSON

* **Success Response:**

  When there is success reponse found api will return response in following json format.
  which will have 2 keys like "status" and "data" which will contain all following columns from neon database.

      {
          "status": "success",
          "data": {
              "": "Select",
              "daily": "Daily",
              "fortnightly": "Fortnightly",
              "in_specific_days": "In Specific days",
              "manual": "Manual"
          }
      }


#### Get Billing Class List ###
* **URL**

    http://neon-soft.com/api/billingClass/list

* **Method:**

    The request type
    
    GET

* **Output Format**

    JSON

* **Success Response:**

  When there is success reponse found api will return response in following json format.
  which will have 2 keys like "status" and "data" which will contain all following columns from neon database.

      {
          "status": "success",
          "data": {
              "6": "Billing",
              "7": "Billing Payment",
              "8": "Default Billing Class"
          }
      }

#### Get Service List ###
* **URL**

    http://neon-soft.com/api/service/list

* **Method:**

    The request type
    
    GET

* **Output Format**

    JSON

* **Success Response:**

  When there is success reponse found api will return response in following json format.
  which will have 2 keys like "status" and "data" which will contain all following columns from neon database.

      {
          "status": "success",
          "data": [
              {
                  "ServiceID": 1,
                  "ServiceName": "Default Service",
                  "ServiceType": "voice",
                  "CompanyGatewayID": 0,
                  "Title": null
              }
          ]
      }


#### Get Discount List ###
* **URL**

    http://neon-soft.com/api/discount/list

* **Method:**

    The request type
    
    GET

* **Output Format**

    JSON

* **Success Response:**

  When there is success reponse found api will return response in following json format.
  which will have 2 keys like "status" and "data" which will contain all following columns from neon database.

      {
          "status": "success",
          "data": [
              {
                  "DiscountPlanID": 7,
                  "Name": "UK + PK + IND"
              }
          ]
      }


#### Get Subscription List ###
* **URL**

    http://neon-soft.com/api/subscription/list

* **Method:**

    The request type
    
    GET

* **Output Format**

    JSON

* **Success Response:**

  When there is success reponse found api will return response in following json format.
  which will have 2 keys like "status" and "data" which will contain all following columns from neon database.

      {
          "status": "success",
          "data": [
              {
                  "SubscriptionID": 1,
                  "Name": "test sub",
                  "CurrencyID": 3,
                  "AnnuallyFee": null,
                  "QuarterlyFee": null,
                  "MonthlyFee": "100.00",
                  "WeeklyFee": "23.33",
                  "DailyFee": "3.33",
                  "Advance": 1,
                  "ActivationFee": "1.00",
                  "InvoiceLineDescription": "Internet Plan line",
                  "Description": "TEST description",
                  "AppliedTo": 0
              }
          ]
      }



#### Get Subscription List ###
* **URL**

    http://neon-soft.com/api/inboundOutbound/list/{CurrencyID}

* **Method:**

    The request type
    
    GET
  
* **Parameters:**

    1. CurrencyID
  
* **Output Format**

    JSON

* **Success Response:**

  When there is success reponse found api will return response in following json format.
  which will have 2 keys like "status" and "data" which will contain all following columns from neon database.

      {
          "status": "success",
          "data": [
              {
                  "RateTableId": 19,
                  "RateTableName": "A-Z CLI 19\\/03\\/2015"
              }
          ]
      }


#### Get Payment List ###
* **URL**

    http://neon-soft.com/api/payment/list

* **Method:**

    The request type
    
    GET
  
* **Output Format**

    JSON

* **Success Response:**

  When there is success reponse found api will return response in following json format.
  which will have 2 keys like "status" and "data" which will contain all following columns from neon database.

      {
          "status": "success",
          "data": {
              "AuthorizeNet": "AuthorizeNet",
              "Stripe": "Stripe",
              "FideliPay": "FideliPay",
              "StripeACH": "StripeACH",
              "Paypal": "Paypal",
              "SagePay": "SagePay",
              "PeleCard": "PeleCard"
          }
      }
