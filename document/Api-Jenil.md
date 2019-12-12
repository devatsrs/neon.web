# API

### POST checkBalance

http://speakintelligence.neon-soft.com/api/account/checkBalance

Params:

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

http://speakintelligence.neon-soft.com/api/account/getPayments

Params:

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

Params:

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

Params:

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

Params:

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


Params:

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

Params:

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

Params:

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

http://speakintelligence.neon-soft.com/api/requestFund

Params:

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

http://speakintelligence.neon-soft.com/api/depositFund

Params:

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


### POST startCall

http://speakintelligence.neon-soft.com/api/startCall

Params:

	"AccountID"/"AccountNo"
	ConnectTime
	CLI
	CLD
	CallType
	UUID
	VendorID

Return

    Example:
        Request:

              AccountID :6727
              ConnectTime:2018-12-26 15:23:06
              CLI : 971562600839
              CLD : 123456987456
              CallType: Inbound
              UUID : 1155544
              VendorID : 111              

        Response:

            {
                  "status": "failed",
                  "message": "Account has not sufficient balance."
            }


### POST endcall

http://speakintelligence.neon-soft.com/api/endcall

Params:

"AccountID"/"AccountNo"
UUID
DisconnectTime


Return

    Example:
        Request:

              AccountID :6728
              UUID:54564564
              DisconnectTime : 2018-12-26 15:24:06            


        Response:
           {
               "status": "success",
               "message": "Record Updated Successfully",
               "data": {
                   "duration": 60
               }
           }


### POST startRecording

http://speakintelligence.neon-soft.com/api/startRecording

Params:

"AccountID"/"AccountNo"
UUID

Return

    Example:
        Request:

              AccountID :6727
              UUID:1155545

        Response:

          {
              "status": "success",
              "message": "Recording Start Successfully."
          }



### POST blockCall

http://speakintelligence.neon-soft.com/api/blockCall

Params:

"AccountID"/"AccountNo"
UUID
DisconnectTime
BlockReason

Return

    Example:
        Request:

        AccountID: "6727"
        UUID   : 1155545
        BlockReason : LowBalance

        Response:

            {
                "status": "success",
                "message": "Call Blocked Successfully",
                "data": {
                    "duration": 120
                }
            }


### POST getBlockCalls

http://speakintelligence.neon-soft.com/api/getBlockCalls

Params:

"AccountID"(optional)
StartDate
EndDate

Return

   Example:
           Request:

           StartDate: 2018-01-01
           EndDate   : 2018-08-01
           AccountID :

           Response:

               {
                   "status": "success",
                   "data": [
                       {
                           "status": "success",
                           "data": [
                               {
                                   "UsageDetailID": 1,
                                   "UsageHeaderID": 1,
                                   "connect_time": "2018-06-10 23:53:58",
                                   "disconnect_time": "2018-06-10 23:54:32",
                                   "billed_duration": 34,
                                   "area_prefix": "qukn8801",
                                   "pincode": null,
                                   "extension": null,
                                   "cli": "971562600839",
                                   "cld": "11118801855498036",
                                   "cost": "0.010370",
                                   "remote_ip": "203.90.232.81",
                                   "duration": 34,
                                   "trunk": "Other",
                                   "ProcessID": "30254",
                                   "ID": 38018081,
                                   "is_inbound": 0,
                                   "billed_second": 34,
                                   "disposition": "Blocked",
                                   "userfield": null,
                                   "StartDate": "2018-06-10 00:00:00",
                                   "GatewayAccountID": "QUICKCOM"
                               },
                               {
                                   "UsageDetailID": 2,
                                   "UsageHeaderID": 1,
                                   "connect_time": "2018-06-10 23:44:15",
                                   "disconnect_time": "2018-06-10 23:54:52",
                                   "billed_duration": 637,
                                   "area_prefix": "qukn8801",
                                   "pincode": null,
                                   "extension": null,
                                   "cli": "971567884733",
                                   "cld": "11118801720932251",
                                   "cost": "0.194285",
                                   "remote_ip": "203.90.232.81",
                                   "duration": 637,
                                   "trunk": "Other",
                                   "ProcessID": "30254",
                                   "ID": 38018164,
                                   "is_inbound": 0,
                                   "billed_second": 637,
                                   "disposition": "Blocked",
                                   "userfield": null,
                                   "StartDate": "2018-06-10 00:00:00",
                                   "GatewayAccountID": "QUICKCOM"
                               }
                           ]
                       }
                   ]
               }



### GET EmailTemplate List

http://speakintelligence.neon-soft.com/api/emailTemplate/list

Return

    Example:

        Response:

            {
                "status": "success",
                "data": [
                    {
                        "TemplateID": 2,
                        "LanguageID": 43,
                        "TemplateName": "Push List Template",
                        "Subject": "Push List",
                        "TemplateBody": "Dear&nbsp;{{FirstName}}&nbsp;{{LastName}}<br><br>{{PostCode}} &nbsp;{{CompanyName}}{{CompanyAddress1}}{{CompanyAddress2}}{{CompanyAddress3}}{{CompanyCity}}{{CompanyPostCode}}<br><br>Our latest push list&nbsp;<br><br><table>\r\n <tbody><tr>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n </tr>\r\n <tr>\r\n  <td></td>\r\n  <td>Pakistan&nbsp;</td>\r\n  <td>0.0656</td>\r\n  <td></td>\r\n  <td></td>\r\n </tr>\r\n <tr>\r\n  <td></td>\r\n  <td>India</td>\r\n  <td>0.66161</td>\r\n  <td></td>\r\n  <td></td>\r\n </tr>\r\n <tr>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n </tr>\r\n <tr>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n </tr>\r\n <tr>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n  <td></td>\r\n </tr></tbody></table><br>Regards<br><br>Sales<br><br><img alt=\"\" src=\"http://wave-tel.com/images/logo2.png\"><br>"
                    },
                    {
                        "TemplateID": 13,
                        "LanguageID": 43,
                        "TemplateName": "Rate Notification",
                        "Subject": "Rate Notification from Wave Tel Limited",
                        "TemplateBody": "<span>Dear PARTNER &nbsp;\r\n&nbsp;<br>\r\n<br>\r\nOn behalf of &nbsp;WaveTel , please find attached rate updates.&nbsp;Kindly note that the rates/codes specified in the attached rate sheets shall be\r\nthe only effective ones offered by Wavetel for CORE ROUTES. All previous rates/codes offered would be considered as null. <br><br><u>TECH PREFIX</u> - Please open the ratesheet to know the TECH PREFIX.&nbsp;<br></span><br><p><u>Note:</u> Kindly confirm receipt of the rate\r\nnotification by replying to this email. Failure to confirm receipt will not\r\nimpair effectiveness of this rate notice.&nbsp;</p><p><br></p><p><br></p><p><b>Best Regards</b></p><p><b><span><br>\r\nKhaza Mizan</span></b></p><p><b>Wavetel\r\nLimited</b></p><p><i>88 -90&nbsp;Goodmayes Road, Essex, IG3 9UU, London, UK</i><i><span>​​<br>\r\n</span></i><i><span><br>\r\n</span></i><b>T</b>&nbsp;+44(0) 0203 5000 975&nbsp;<b>M&nbsp;</b>+(44)\r\n(0)7410168732&nbsp;</p><p><b>E</b>&nbsp;<a rel=\"nofollow\" target=\"_blank\">khaza.mizan@wave-tel.com</a>&nbsp;<b>W</b><b>&nbsp;</b><span><a rel=\"nofollow\" target=\"_blank\" href=\"http://www.zamirtelecom.com/\" title=\"Link: http://www.zamirtelecom.com/\">www.</a><a rel=\"nofollow\" target=\"_blank\" href=\"http://wave-tel.com/\">wave-tel.com</a></span><span><br>\r\n</span><b>SKYPE</b><b>&nbsp;</b>live:mizankm&nbsp;<b>Whats&nbsp;App\r\n&amp; Viber</b><b>&nbsp;</b>+447970973057​</p><p><br></p><p>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n</p><p><span>This\r\nmessage is confidential and is intended solely for the use of the individual to\r\nwhom it is addressed. Any views and opinions expressed are solely that of the\r\nauthor and not necessarily that of \"\" or any of its subsidiary\r\ncompanies or partner institutions. It may contain legally privileged, sensitive\r\ndata and be protected by legal rules. If you have received this email by error,\r\nyou must let the sender know and delete it immediately. The organisation\r\naccepts no liability for any damage caused by any virus transmitted by this\r\nemail, which may not be secure.</span></p>\r\n\r\n<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>"
                    },
                    {
                        "TemplateID": 14,
                        "LanguageID": 43,
                        "TemplateName": "URGENT RATE INCREASE",
                        "Subject": "Urgent Rate Increase Notification",
                        "TemplateBody": "<span>Dear PARTNER &nbsp; &nbsp;<br><br>On behalf of &nbsp;WaveTel , please find attached rate updates.&nbsp;Kindly note that the rates/codes specified in the attached rate sheets shall be the only effective ones offered by Wavetel for CORE ROUTES.&nbsp;All previous rates/codes offered would be considered as null.&nbsp;<br><br><u>TECH PREFIX</u>&nbsp;- Please open the ratesheet to know the TECH PREFIX.&nbsp;<br></span><br><p><u>Note:</u>&nbsp;Kindly confirm receipt of the rate notification by replying to this email. Failure to confirm receipt will not impair effectiveness of this rate notice.&nbsp;</p><p><br></p><p><br></p><p><b>Best Regards</b></p><p><b><br>Khaza Mizan</b></p><p><b>Wavetel Limited</b></p><p><i>88 -90&nbsp;Goodmayes Road, Essex, IG3 9UU, London, UK</i><i>​​<br></i><i><br></i><b>T</b>&nbsp;+44(0) 0203 5000 975&nbsp;<b>M&nbsp;</b>+(44) (0)7410168732&nbsp;</p><p><b>E</b>&nbsp;<a rel=\"nofollow\" target=\"_blank\">khaza.mizan@wave-tel.com</a>&nbsp;<b>W</b><b>&nbsp;</b><a rel=\"nofollow\" target=\"_blank\" href=\"http://www.zamirtelecom.com/\">www.</a><a rel=\"nofollow\" target=\"_blank\" href=\"http://wave-tel.com/\">wave-tel.com</a><br><b>SKYPE</b><b>&nbsp;</b>live:mizankm&nbsp;<b>Whats&nbsp;App &amp; Viber</b><b>&nbsp;</b>+447970973057​</p><p><br></p><p></p><p>This message is confidential and is intended solely for the use of the individual to whom it is addressed. Any views and opinions expressed are solely that of the author and not necessarily that of \"\" or any of its subsidiary companies or partner institutions. It may contain legally privileged, sensitive data and be protected by legal rules. If you have received this email by error, you must let the sender know and delete it immediately. The organisation accepts no liability for any damage caused by any virus transmitted by this email, which may not be secure.</p>"
                    },
                    {
                        "TemplateID": 15,
                        "LanguageID": 43,
                        "TemplateName": "SamTemplate",
                        "Subject": "Account General",
                        "TemplateBody": "{{FirstName}} &nbsp;{{LastName}}"
                    },
                    {
                        "TemplateID": 16,
                        "LanguageID": 43,
                        "TemplateName": "SS Template",
                        "Subject": "SS 08",
                        "TemplateBody": "<p>{{FirstName}}&nbsp;{{LastName}}&nbsp;<br><br>{{Signature}}<br><br><img src=\"https://s3-us-west-2.amazonaws.com/ratemanagement.staging/1/Wysihtml5fileupload/2016/04/08/Wysihtml5_C047D66E-C5DD-8EB3-A024-3545BD6F4A5D.png\" title=\"Image: https://s3-us-west-2.amazonaws.com/ratemanagement.staging/1/Wysihtml5fileupload/2016/04/08/Wysihtml5_C047D66E-C5DD-8EB3-A024-3545BD6F4A5D.png\"><br></p><p><br></p><p style=\"text-align: right; \">וְאָהַבְתָּ אֵת יְיָ | אֱלֹהֶיךָ, בְּכָל-לְבָֽבְךָ, וּבְכָל-נַפְשְׁךָ, וּבְכָל-מְאֹדֶֽךָ. וְהָיוּ הַדְּבָרִים הָאֵלֶּה, אֲשֶׁר | אָֽנֹכִי מְצַוְּךָ הַיּוֹם, עַל-לְבָבֶֽךָ: וְשִׁנַּנְתָּם לְבָנֶיךָ, וְדִבַּרְתָּ בָּם בְּשִׁבְתְּךָ בְּבֵיתֶךָ, וּבְלֶכְתְּךָ בַדֶּרֶךְ וּֽבְשָׁכְבְּךָ, וּבְקוּמֶֽךָ. וּקְשַׁרְתָּם לְאוֹת | עַל-יָדֶךָ, וְהָיוּ לְטֹטָפֹת בֵּין | עֵינֶֽיךָ, וּכְתַבְתָּם | עַל מְזֻזֹת בֵּיתֶךָ וּבִשְׁעָרֶֽיךָ:</p><p style=\"text-align: right; \">וְהָיָה אִם-שָׁמֹעַ תִּשְׁמְעוּ אֶל-מִצְוֹתַי, אֲשֶׁר | אָנֹכִי מְצַוֶּה | אֶתְכֶם הַיּוֹם, לְאַהֲבָה אֶת יְיָ | אֱלֹֽהֵיכֶם, וּלְעָבְדוֹ בְּכָל-לְבַבְכֶם וּבְכָל נַפְשְׁכֶם. וְנָֽתַתִּי מְטַֽר-אַרְצְכֶם בְּעִתּוֹ, יוֹרֶה וּמַלְקוֹשׁ, וְאָֽסַפְתָּ דְגָנֶךָ וְתִירֽשְׁךָ וְיִצְהָרֶֽךָ. וְנָֽתַתִּי | עֵשֶׂב | בְּשָֽׂדְךָ לִבְהֶמְתֶּךָ, וְאָֽכַלְתָּ וְשָׂבָֽעְתָּ. הִשָּֽׁמְרוּ לָכֶם פֶּן-יִפְתֶּה לְבַבְכֶם, וְסַרְתֶּם וַעֲבַדְתֶּם | אֱלֹהִים | אֲחֵרִים וְהִשְׁתַּחֲוִיתֶם לָהֶם. וְחָרָה | אַף-יְיָ בָּכֶם, וְעָצַר | אֶת-הַשָּׁמַיִם וְלֹא-יִהְיֶה מָטָר, וְהָאֲדָמָה לֹא תִתֵּן אֶת-יְבוּלָהּ וַאֲבַדְתֶּם | מְהֵרָה מֵעַל הָאָרֶץ הַטֹּבָה | אֲשֶׁר | יְיָ נֹתֵן לָכֶם: וְשַׂמְתֶּם | אֶת דְּבָרַי | אֵלֶּה עַל-לְבַבְכֶם וְעַל-נַפְשְׁכֶם וּקְשַׁרְתֶּם | אֹתָם לְאוֹת | עַל-יֶדְכֶם, וְהָיוּ לְטוֹטָפֹת בֵּין | עֵינֵיכֶם: וְלִמַּדְתֶּם | אֹתָם | אֶת-בְּנֵיכֶם, לְדַבֵּר בָּם, בְּשִׁבְתְּךָ בְּבֵיתֶךָ, וּבְלֶכְתְּךָ בַדֶּרֶךְ, וּבְשָׁכְבְּךָ וּבְקוּמֶֽךָ: וּכְתַבְתָּם | עַל-מְזוּזוֹת בֵּיתֶךָ וּבִשְׁעָרֶֽיךָ: לְמַעַן | יִרְבּוּ | יְמֵיכֶם וִימֵי בְנֵיכֶם עַל הָֽאֲדָמָה | אֲשֶׁר נִשְׁבַּע | יְיָ לַאֲבֹֽתֵיכֶם לָתֵת לָהֶם, כִּימֵי הַשָּׁמַיִם | עַל-הָאָֽרֶץ:</p><div style=\"text-align: right; \"><br></div>"
                    },
                    {
                        "TemplateID": 17,
                        "LanguageID": 43,
                        "TemplateName": "Low Balance Reminders",
                        "Subject": "Low Balance Reminders",
                        "TemplateBody": "<p>You reached call credit on account&nbsp;is {{Currency}}{{OutstandingIncludeUnbilledAmount}} which is below the warning level set of {{Currency}} {{BalanceThreshold}}</p><p>Please make a payment to your account soon to ensure your services are not limited.</p><p>As your balance is below {{Currency}}{{BalanceThreshold}}.</p><p><br></p><p>Exclude : {{OutstandingExcludeUnbilledAmount}}</p><p>Include: {{OutstandingIncludeUnbilledAmount}}</p>&nbsp;<br><br>{{BalanceThreshold}}<br>"
                    }
                ]
            }


### GET UsersList

http://speakintelligence.neon-soft.com/api/users/list

Return

    Example:

        Response:

           {
               "status": "success",
               "data": [
                   {
                       "UserID": 1,
                       "FirstName": "Sumera",
                       "LastName": "Khan",
                       "EmailAddress": "saeedsumera@hotmail.com"
                   },
                   {
                       "UserID": 91,
                       "FirstName": "Onno",
                       "LastName": "Westra",
                       "EmailAddress": "onno.westra@speakintelligence.com"
                   }
               ]
           }



### GET CurrencyList

http://speakintelligence.neon-soft.com/api/currency/list

Return

    Example:

        Response:

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
                  },
                  {
                      "CurrencyId": 9,
                      "Symbol": "€",
                      "Code": "EUR",
                      "Description": "EURO"
                  }
              ]
          }



### GET ServiceList

http://speakintelligence.neon-soft.com/api/service/list

Return

    Example:

        Response:

          {
              "status": "success",
              "data": [
                  {
                      "ServiceID": 1,
                      "ServiceName": "Default Service",
                      "ServiceType": "voice",
                      "CompanyGatewayID": 0,
                      "Title": null
                  },
                  {
                      "ServiceID": 2,
                      "ServiceName": "Broadband plus PSTN Line",
                      "ServiceType": "voice",
                      "CompanyGatewayID": 0,
                      "Title": null
                  },
                  {
                      "ServiceID": 3,
                      "ServiceName": "PSTN Line",
                      "ServiceType": "voice",
                      "CompanyGatewayID": 0,
                      "Title": null
                  },
                  {
                      "ServiceID": 4,
                      "ServiceName": "IP Centrex",
                      "ServiceType": "voice",
                      "CompanyGatewayID": 0,
                      "Title": null
                  },
                  {
                      "ServiceID": 5,
                      "ServiceName": "ISDN",
                      "ServiceType": "voice",
                      "CompanyGatewayID": 0,
                      "Title": null
                  },
                  {
                      "ServiceID": 6,
                      "ServiceName": "GIRISH TEST",
                      "ServiceType": "voice",
                      "CompanyGatewayID": 0,
                      "Title": null
                  }
              ]
          }



### GET DiscountPlan

http://speakintelligence.neon-soft.com/api/discount/list

Return

    Example:

        Response:

         {
             "status": "success",
             "data": [
                 {
                     "DiscountPlanID": 1,
                     "Name": "UK + PK + IND",
                     "CurrencyID": 3
                 },
                 {
                     "DiscountPlanID": 2,
                     "Name": "All ",
                     "CurrencyID": 3
                 },
                 {
                     "DiscountPlanID": 3,
                     "Name": "PK ONLY",
                     "CurrencyID": 3
                 },
                 {
                     "DiscountPlanID": 4,
                     "Name": "NEW INDIA",
                     "CurrencyID": 3
                 },
                 {
                     "DiscountPlanID": 5,
                     "Name": "NEW INDIA 2",
                     "CurrencyID": 3
                 },
                 {
                     "DiscountPlanID": 7,
                     "Name": "sw",
                     "CurrencyID": 2
                 }
             ]
         }



### GET RateTableList

http://speakintelligence.neon-soft.com/api/inboundOutbound/list/{CurrencyID}

Return

 Example:
    http://speakintelligence.neon-soft.com/api/inboundOutbound/list/2
     Response:

     {
         "status": "success",
         "data": [
             {
                 "RateTableId": 263,
                 "RateTableName": "Ziggo - Customer"
             },
             {
                 "RateTableId": 261,
                 "RateTableName": "Access GBP"
             }
         ]
     }



### GET billingClassList

http://speakintelligence.neon-soft.com/api/billingClass/list

Return

    Example:

        Response:

         {
             "status": "success",
             "data": [
                 {
                     "Name": "Wholesale",
                     "BillingClassID": 1,
                     "TaxRateID": "1"
                 },
                 {
                     "Name": "Wt Services",
                     "BillingClassID": 2,
                     "TaxRateID": "2,3,8"
                 },
                 {
                     "Name": "WT business",
                     "BillingClassID": 3,
                     "TaxRateID": "2"
                 },
                 {
                     "Name": "Porta",
                     "BillingClassID": 4,
                     "TaxRateID": "2"
                 },
                 {
                     "Name": "Default Billing Class",
                     "BillingClassID": 5,
                     "TaxRateID": "2,3,4,5,6,7,8,9"
                 },
                 {
                     "Name": "bug test",
                     "BillingClassID": 7,
                     "TaxRateID": ""
                 },
                 {
                     "Name": "test billing class",
                     "BillingClassID": 8,
                     "TaxRateID": ""
                 },
                 {
                     "Name": "test billing Class account",
                     "BillingClassID": 9,
                     "TaxRateID": "1,6"
                 },
                 {
                     "Name": "template number test with recuring",
                     "BillingClassID": 10,
                     "TaxRateID": "2"
                 },
                 {
                     "Name": "0300101",
                     "BillingClassID": 11,
                     "TaxRateID": ""
                 },
                 {
                     "Name": "vasim billing",
                     "BillingClassID": 12,
                     "TaxRateID": "1"
                 },
                 {
                     "Name": "GIRISH",
                     "BillingClassID": 13,
                     "TaxRateID": ""
                 },
                 {
                     "Name": "Reseller billing",
                     "BillingClassID": 14,
                     "TaxRateID": "11"
                 },
                 {
                     "Name": "Round Charged CDR Test",
                     "BillingClassID": 15,
                     "TaxRateID": ""
                 },
                 {
                     "Name": "SSResller Class",
                     "BillingClassID": 16,
                     "TaxRateID": ""
                 },
                 {
                     "Name": "PAYG",
                     "BillingClassID": 17,
                     "TaxRateID": ""
                 },
                 {
                     "Name": "test",
                     "BillingClassID": 18,
                     "TaxRateID": ""
                 }
             ]
         }








### POST Add Service Purchased

http://speakintelligence.neon-soft.com/api/account/createAccount
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
	PaymentMethod
	DynamicFields
	AutoTopup
	MinThreshold
	TopupAmount
	AutoOutpayment
	OutPaymentThreshold
	OutPaymentAmount

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
		PaymentMethod:AuthorizeNetEcheck
		DynamicFields:[

                                      {
                                          "Name": "SIAccountID",
                                          "Value": "745"
                                      }
                                  ]
        AutoTopup:1
        MinThreshold:40
        TopupAmount:40
        AutoOutpayment:1
        OutPaymentThreshold:30
        OutPaymentAmount:40


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
        	"ContractDuration":"35",
            "ContractType":"4",
            "AutoRenewal":"1",
            "ContractFeeValue":"35",
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

	AccountNumber
	AccountID
	ServiceTemaplate
	NumberPurchased
	InboundTariffCategoryId
    ServiceStartDate
    ServiceEndDate
    ContractDuration
    ContractType
    ContractFeeValue
    AutoRenewal
    DynamicFields
    PackageSubscription

Return

    Example:
        Request:
            AccountNumber:6746[Mandatory if AccountID/Dynamic field is mandatory]
        	AccountID:[Mandatory if AccountNumber/Dynamic field is mandatory]
        	ServiceTemaplate: {"Name": "SI Product Ref","Value": "RFP"}[Mandatory]
        	NumberPurchased:08004570 [Mandatory]
        	InboundTariffCategoryId:
        	ServiceStartDate:2018-12-23
            ServiceEndDate:2018-12-23
            ContractDuration:30
            ContractType:1 [Possible Values are 1=Fixed Fee,2=Remaining Term Of Contract,3=Remaining Term Of Contract (%),4=Remaining Term Of Contract]
            ContractFeeValue:20
            AutoRenewal:1
            DynamicFields:[

                                          {
                                              "Name": "SIAccountID",
                                              "Value": "745"
                                          }
                                      ] [Mandatory if AccountNumber/AccountID field is mandatory]
            PackageSubscription:Pro

        Response:

           {
               "status": "success",
               "message": "Account Service Successfully Added"
           }

http://speakintelligence.neon-soft.com/api/routing/list

Params:

	OriginationNo
	DestinationNo
	AccountNumber
	AccountID
	DataAndTime
    Location

Return

    Example:
        Request:
            OriginationNo:442085950856[Mandatory]
        	DestinationNo: 44208589657[Mandatory]
        	AccountNumber:08004570 [AccountNumber OR AccountID is mandatory]
        	AccountID:[AccountNumber OR AccountID is mandatory]
        	DataAndTime:2018-11-30 10:10:10
        	Location:Switzerland

        Response:

           {
               "status": "success",
               "Positions": [
                   {
                       "Position": "1",
                       "Prefix": "11144",
                       "VendorID": "6748",
                       "VendorName": "BICS",
                       "VendorConnectionName": "BICS2",
                       "SipHeader": "",
                       "IP": "",
                       "Port": "",
                       "Username": "onno.westra@speakintelligence.com",
                       "Password": "test987",
                       "AuthenticationMode": "",
                       "Currency": "EUR",
                       "Rate": "0.002300"
                   },
                   {
                       "Position": "2",
                       "Prefix": "12344",
                       "VendorID": "6747",
                       "VendorName": "TATA",
                       "VendorConnectionName": "TATA",
                       "SipHeader": "",
                       "IP": "",
                       "Port": "",
                       "Username": "",
                       "Password": "",
                       "AuthenticationMode": "",
                       "Currency": "EUR",
                       "Rate": "0.002300"
                   },
                   {
                       "Position": "3",
                       "Prefix": "22244",
                       "VendorID": "6748",
                       "VendorName": "BICS",
                       "VendorConnectionName": "BICS1",
                       "SipHeader": "",
                       "IP": "",
                       "Port": "",
                       "Username": "",
                       "Password": "",
                       "AuthenticationMode": "",
                       "Currency": "EUR",
                       "Rate": "0.006750"
                   }
               ]
           }

### POST Get Account Payment Methods

http://speakintelligence.neon-soft.com/api/account/paymentMethod

Params:




Return

    Example:
        Request:


        Response:

           {
               "status": "success",
               "PaymentMethod": [
                   "AuthorizeNet",
                   "AuthorizeNetEcheck",
                   "FideliPay",
                   "Paypal",
                   "PeleCard",
                   "SagePay",
                   "SagePayDirectDebit",
                   "Stripe",
                   "StripeACH",
                   "FastPay",
                   "MerchantWarrior",
                   "Wire Transfer",
                   "Other"
               ]
           }

### POST Get Account
http://speakintelligence.neon-soft.com/api/account/GetAccount

Params:

	AccountNumber
	AccountID
	DynamicFields


Return

    Example:
        Request:
        AccountNumber: [One of AccountNumber,AccountID,DynamicFields is mandatory]
        AccountID:
        DynamicFields:[

                                      {
                                          "Name": "SIAccountID",
                                          "Value": "745"
                                      }
                                  ]
        Response:



                                  {
                                      "status": "success",
                                      "AccountID": 6773,
                                      "AccountNumber": "dev-0557"
                                  }