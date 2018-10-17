@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">

        <strong>About</strong>
    </li>
</ol>
<!--
<div class="jumbotron">
    <h1>&nbsp;4.14</h1>
    <p>
        <br />
    <div class="btn btn-primary btn-lg">Current Version: <strong>v4.14</strong></div>
</p>
</div>-->
<br>
<div class="btn btn-primary btn-lg">Current Version: <strong>v4.18</strong></div>
<br>
<h2>
    <span class="label label-success">4.18</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>28-09-2018</h4>
                    01. Added Integration with Quickbook Desktop.<br>
                    02. Added option to post invoices and payments to Quickbook and XERO.<br>
                    03. Added Integration with Merchant Warrior.<br>
                    04. Added New Invoice Format(Template 2) In InvoiceTemplate. With two columns on the first page.<br>
                    05. Improved Reseller section.<br>
                    06. Added option to display invoice total on invoice PDF in multi-currency.<br>
                    07. Added option to Digitally Sign Invoice.<br>
                    08. Added new feature "Inventory Management".<br>
                    09. Added option to email disputes.<br>
                    10. Added new dimensions and measures in reports: Avg Rate,Invoice Number,Invoice Date,Invoice Due Date,Account Billing Options etc.<br>
                    11. Added option to Import and Export payments from PBX.<br>
                    12. Added Integration with Voip.ms gateway.<br>
                    13. Added option of vendor cdr re-rating.<br>
                    14. Added option to create Credit Notes.<br>
                    15. Added new cron job for Sippy to export Customer Rates.<br>
                    16. Added new option to create and Upload rates against Time zones. <br>
                    17. Added new field ‘Round Charged Amount’ against Rate Table.<br>
                    18. Added new field RateN against Customer, Vendor and Rate Table rates. <br>
                    19. Fixed few issues related to Tickets.<br>
                    20. Added option to select Discount plans at account level.<br>
                    21. Added option to set Accounts/Extensions under accounts subscriptions and specify discount plans against them.<br>
                    22. Added new notification ‘Account Balance’.<br>
                    23. Added   ‘Account Balance Warning’ under Billing class to send reminders about account balance.<br>
                    24. Added option to Block/Unblock account in PBX from Account screen.<br>
                    25. Added new Retention options: Failed Calls, Tickets and Archived Rates.<br>
                    26. Added option to send invoices as an attachment.<br>
                    27. Added new column BillDurationMinutes,Country,CallType,Description in Usage Column of Invoice Template.<br>
                    28. Added new Payment Integration with Authorize.Net eCheck.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>
<h2>
    <span class="label label-success">4.17</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>07-05-2018</h4>
                    01. Added option to sort rules in rate generator.<br>
                    02. Added option to set Charge Date and Next Invoice Date against accounts.<br>
                    03. Added Account Exposure widget in customer panel.<br>
                    04. Improved Reseller section.<br>
                    05. Fixed issue with Auto Payment Capture job to list all issues against each account.<br>
                    06. Added Account Name - Authentication Rule in Sippy Gateway.<br>
                    07. Added new billing gateway – FTP.<br>
                    08. Added option to collect CDRs from Sippy based on Setup time.<br>
                    09. Added option in Reports to filter on measures.<br>
                    10. Moved Upload Rates option under Rate Management. Now you can upload Vendor Rates OR Rate Tables from same page.<br>
                    11. Added Integration with pele card.<br>
                    12. Added option to download switch formatted rates against specific date ‘Custom Date’.<br>
                    13. Added option in billing class to setup CDRs cost rounding for invoices.<br>
                    14. Added option to Group by description in Rate Generator.<br>
                    15. Added option to Allow comma(,) And Pipe(|) in rate upload code separator.<br>
                    16. LCR List - Added group by description option.<br>
                    17. LCR List - Added option to Block/unblock Vendors and view blocked vendors as well.<br>
                    18. LCR list - Added option to view customer rates offered against codes and margins.<br>
                    19. Added option to bulk apply rate tables to multiple customers and trunks.<br>
                    20. Added option to digitally Sign Invoices.<br>
                    21. Added Top Up option in customer panel.<br>
                    22. Added Multilingual option for Customer Panel.<br>
                    23. Added History button against vendor, customer and rate table rates which will show you full history on codes.<br>
                    24. Added ‘Auto Rate Import’ option via email for both Vendor and Rate Tables.<br>
                    25. Added option to upload dial codes from the upload rates page well.<br>
                    26. Modified QuickBook and XERO Journal postings to post based on Payment Date instead of Invoice Date.<br>
                    27. Modified Avg Rate calculation in invoices for CDR format  ‘Summary CDR’ to show actual rate instead of average call rate.<br>
                    28. Customer Rate - Modified Rate Update options under Customer Rate. Added ‘New Offer’ and ‘Bulk New Offer’.<br>
                    29. Added placeholders option under Invoice Template > Footer and Terms boxes.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>
<h2>
    <span class="label label-success">4.16</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>09-01-2017</h4>
                    01. Added Resellers Billing.<br>
                    02. Added option to Review Vendor Rates before uploading.<br>
                    03. Added integration with Voip Now.<br>
                    04. Added option to export reports under ‘Reports’.<br>
                    05. Added option to schedule reports under ‘Reports’.<br>
                    06. Added new dimensions and measures under ‘Reports’.<br>
                    07. Added Management Reports option under Invoice Template.<br>
                    08. Added new gateway type ‘VOS5000’.<br>
                    09. Added invoice period from  and to placeholders under Email Templates.<br>
                    10. Added Account First and Last Name placeholders under Invoice Template.<br>
                    11. Added option to re run previous days summary.<br>
                    12. Improved Ticketing section.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.15</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>20-11-2017</h4>
                    01. Added Integration with XERO.<br>
                    02. Added Integration with M2.<br>
                    03. Added M2 and MOR format in ratesheet download.<br>
                    04. Improved Rate Table section to show previous rate and trend (rate increase or decrease).<br>
                    05. Added stats by call type ( Outbound and inbound ).<br>
                    06. Added Margin under Analysis.<br>
                    07. Improved Sales By Revenue chart to include unbilled amount.<br>
                    08. Auto rate import from MOR.<br>
                    09. Auto rate import from Loctorious.<br>
                    10. Added option to convert vendor rates to base currency when uploading rates.<br>
                    11. Added option to rate CDRs for selected accounts.<br>
                    12. Fixed issue with new ticket creation with same subject.<br>
                    13. Improved Ticket section.<br>
                    14. Added option in rate generator to set rate Increase and Decrease Date.<br>
                    15. Storing Logs of deleted tickets.<br>
                    16. Added ‘All’ option for Vos 3.2 format.<br>
                    17. Fixed ‘Country Code Not Found’ error when uploading rate sheets.<br>
                    18. Added option to import accounts from ‘Sippy’.<br>
                    19. Improved Prefix Translation to check prefix setup against customer/vendor trunk.<br>
                    20. Added Account Manager Reports under Analysis.<br>
                    21. Added Account Owner filter in Analysis .<br>
                    22. Added margin and revenue measures in reporting.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.14</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>05-10-2017</h4>
                    01. Added Integration with SagePay Direct Debit(.co.za).<br>
                    02. Added Integration with FideliPay.<br>
                    03. Added Rate Analysis option under Rate Management.<br>
                    04. Added option to Setup rules by description in Rate Generator.<br>
                    05. Fixed issues in Ticket section.<br>
                    06. Added Group By option in Rate Table.<br>
                    07. Added Account Log.<br>
                    08. Added option to delete or Edit roles.<br>
                    09. Redesigned Filter Section <br>
                    10. Added option to Bulk Download Invoices.<br>
                    11. Added option to Skip no of header and Footer lines when uploading files.<br>
                    12. Added Bulk Pickup option under Ticket.<br>
                    13. Added option to Auto Add IP against Accounts if information is provided in CDRs also added Notification for Auto Add IP. <br>
                    14. Added Integration with Fusion PBX.<br>
                    15. Added Report module. Drag and Drop and create reports.<br>
                    16. Added option under Company to setup your Customer Rate Sheet Templates.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.13</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>22-08-2017</h4>
                    01. Added Integration with Stripe ACH.<br>
                    02. Added Auto Payment Capture for authorize(.net) and stripe.<br>
                    03. Added STREAMCO Gateway Integration.<br>
                    04. Added Locutorios Gateway Integration.<br>
                    05. Added Movement Report in customer panel for MOR.<br>
                    06. Added Barcode scanning option on OneOff Invoice.<br>
                    07. Added New field under Account Service 'Details' where you can specify e.g. Location for that service or any other information.<br>
                    08. Added Account Exposure column in Account Grid.<br>
                    09. Added Vendors,Description and Position filter on LCR page.Now you can view upto 10 positions in LCR.<br>
                    10. Added Trunk Prefix Placeholder in Ratesheet Email Template.<br>
                    11. Added option to upload your own rate sheet templates.<br>
                    12. Added option to skip no of header and footer lines when uploading Vendor Rate Sheets.<br>
                    13. Fixed invoice period for Prepaid and Postpaid customers.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.12</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>22-06-2017</h4>
                    01. Added new output format Vos 2.0 for older versions of Vos.<br>
                    02. Fixed issues in ticketing.<br>
                    03. Added currency sign placeholder in email templates.<br>
                    04. Added option to bulk update accounts.<br>
                    05. Added option to import Account Ips.<br>
                    06. Added Notice Board to display warnings or messages to customers in Customer Portal.<br>
                    07. Added Prefix Translation rule.<br>
                    08. Added option of Manually Billing customers.<br>
                    09. Added option to import Payments from MOR.<br>
                    10. Added Integration with SagePay(.co.za).<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.11</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>23-05-2017</h4>
                    01. Added system email templates under Email Templates which you can ON/OFF and Modify.<br>
                    02. Added Invoice Paid notification under Admin -> Notifications.<br>
                    03. Added Quarterly and Yearly price in subscription.<br>
                    04. Added option to select Subscriptions in Estimate.<br>
                    05. Improved Estimate PDF to show One off and recurring fees separately<br>
                    06. Added options for Service Based billing.<br>
                    07. Improved HTML editor control.<br>
                    08. Improved Invoice Templates. Added option to select Usage Columns and Account Info.<br>
                    09. Added new widgets under Billing -> Analysis. Profit/Loss and Account Receivable and Payable.<br>
                    10. Implemented integration with MOR kolmisoft.<br>
                    11. Fixed issues with logging and session time out.<br>
                    12. Added Ticketing module.<br>
                    13. Removed hash keys from Customer/Vendor File names.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.09</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>02-03-2017</h4>
                    01. Added Recurring option under Billing.<br>
                    02. Moved Sage Export button under action on invoice page.<br>
                    03. Added option to delete missing gateway accounts.<br>
                    04. Added Unbilled Amount widget in customer panel.<br>
                    05. Added option to select Current, Future or specific date rates when generating rate table from Rate Generator.<br>
                    06. Revised Code Deck filter on LCR page.<br>
                    07. Added balance brought forward in Statement Of Account.<br>
                    08. Added option to re-rate CDRs based on Origination no.<br>
                    09. Added Yearly option in billing cycle.<br>
                    10. Added option to schedule cron jobs in seconds.<br>
                    11. Improved Email Templates. (User can select From email when sending emails)<br>
                    12. Fixed issue with Stripe errors.<br>
                    13. Removed Unique Prefix check against Customer trunk.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.08</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>17-01-2017</h4>
                    01. Improved Stripe integration.<br>
                    02. Added IP/CLI filter on account page.<br>
                    03. Added Traffic by region map.<br>
                    04. Added vendor hourly stats notification.<br>
                    05. Added Top 10 Most Dialled Numbers, Longest Duration Calls and Most Expensive Calls under Analysis and Monitor Dashboard.<br>
                    06. Added average rate per minute on cdr page and invoice.<br>
                    07. Added Job Notification tick box on user page so user can switch ON and OFF job notifcations.<br>
                    08. Improved filters on Rate Generator page.<br>
                    09. Fixed issue with logo on forgot password page.<br>
                    10. Fixed Next run time issue with Cron Jobs.<br>
                    11. Added option to enter rate when re-rating CDRs.<br>
                    12. Improved QoS notifications.<br>
                    13. Improved Hourly Analysis chart under Analysis.<br>
                    14. Added alerts widget on monitor dashboard.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.07</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>11-11-2016</h4>
                    01. Added Integration with QuickBook , Stripe and PayPal.<br>
                    02. Added Accept/Reject/Comments button on Estimate so that customer can accept/reject estimate or add comments against it.<br>
                    03. Added QOS alert (ACD/ASR).<br>
                    04. Added Call monitoring alert (Blacklisted Destinations/Longest Calls/Expensive Calls/Calls outside business hours).<br>
                    05. Added Analysis by Account under Monitor dashboard and Analysis.<br>
                    06. Added Translation rule at Gateway level.<br>
                    07. Added Base code deck option.<br>
                    08. Added option to specify Tax1 and Tax2 in Invoices/Estimates and Additional Charges.<br>
                    09. Added payments view option under Invoice log. From invoice page you can see all payments against the invoice.<br>
                    10. Improved Billing dashboard.<br>
                    11. Improved responsiveness.<br>
                    12. Fixed Invoice No filter on Invoice and Payments page. Now you will be able to do wild card search.<br>
                    13. Changed Sorting under Rate Generator -> Margin to Min Rate ASC.<br>
                    14. Added option to switch ON and OFF Job notifications.<br>
                    15. Fixed issue with Code Deck country update.<br>
                    16. Fixed issue with Previous balance on the invoice.<br>
                    17. Added Vendor Unbilled Usage and Account Exposure under Credit Control.<br>
                    18. Improved Account Card.<br>
                    19. Added Email Tracking and Mailbox.<br>
                    20. Added Payment Reminders.<br>
                    21. Added Low Balance Reminders.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>
<h2>
    <span class="label label-success">4.06</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>06-09-2016</h4>
                    01 .Added new feature ‘Discount Plans’.<br>
                    02 .Added ISO code to Code deck.<br>
                    03. Added Billing Enable option on Accounts.<br>
                    04. Added Overdue invoices filter under Invoices.<br>
                    05. Added Drill down option on Analysis and Monitor dashboard.<br>
                    06. Added new functionality to setup Data and File Retention.<br>
                    07. Added Integration section to setup third party integrations.<br>
                    08. Integration with Fresh desk.<br>
                    09. Integration with MS Exchange calendar.<br>
                    10. Added Recent accounts widget on CRM dashboard.<br>
                    11. Added Notification option under Admin to setup email notifications.<br>
                    12. Added option to setup multiple servers under Server Monitor.<br>
                    13. Added new column in porta sheet ‘Discontinued’.<br>
                    14. Added No against subscription under account. Subscriptions will be displayed in ‘No’ order on the invoice.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>
<h2>
    <span class="label label-success">4.05</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>27-07-2016</h4>
                    1. Added an option to bulk edit Rate Table.<br>
                    2. Status of paid and partially paid invoices won't be changed to send when click on Send.<br>
                    3. Added credit control option against account.<br>
                    4. Improved Cron Job monitoring.<br>
                    5. Added an option to restart failed jobs and terminate In Progress jobs.<br>
                    6. Added new CRM Dashboard.<br>
                    7. Fixed issue with cancel invoices, they will be excluded from total now.<br>
                    8. Regenerate invoice will ignore cancel invoices.<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.04</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>29-06-2016</h4>
                    1. Added Active filter on Rate Generator and Cron job Page<br>
                    2. Added an option to Recall Multiple Payments<br>
                    3. Added an option to Delete CDR<br>
                    4. Added an option to setup charge code to prefix mapping (Dial String)<br>
                    5. Resolved Concurrent Jobs Problem<br>
                    6. Added grid totals on Analysis page<br>
                    7. Added an option to overwrite subscription charges at account level<br>
                    8. Added an option to Delete Cron job<br>
                    9. Added an option to add multiple CLIs and IPs against account<br>
                    10. Added Invoice Period in the grid on Invoice page<br>
                    11. Added Server Monitor page under Admin<br>
                    12. Revised CDR process logic to make it faster<br>
                    13. Changed Generate New Invoice button on invoice page to generate all pending invoices against accounts<br>
                    14. Added 'Test' mail settings button under Company to test smtp settings<br>
                    15. Made phone no optional against Opportunity<br>
                    16. Added an option to delete/edit tasks and notes in activity timeline against account and lead<br>
                    17. Added an option to send email to user on Task Assignment or when tagged in Opportunity or Task<br>
                    18. Added back 'Convert to Account' button under lead<br>
                    19. Added default issue date filters on invoice page to show last 1 month invoices<br>
                    20. Added Account Activity chart against account<br>
                    21. Exclude Cancel invoices when re-generating invoices<br>
                    22. Added CLI and CLD translation rule under CDR upload<br>
                    23. Recalculate Billed Duration when re-rating cdrs depending on rates assigned<br>
                </div>
            </li>
        </ul>
    </div>

</div>
<div class="clear"></div>

<h2>
    <span class="label label-success">4.03</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>06-06-2016</h4>
                    1.  Added an option to create Disputes<br>
                    2.  Added an option to Import Accounts from Mirta/Porta/CSV/Excel<br>
                    3.  Added an option to import Leads<br>
                    4.  Added LCR policy (LCR/LCR+Prefix) option against Rate Generator and LCR<br>
                    5.  Added CRM module (Opportunities and Tasks)<br>
                    6.  Added Monitor Dashboard<br>
                    7.  Added Customer/Vendor Analysis by Destination,Prefix,Trunk and Gateway<br>
                    8.  Added Forbidden and Preference options under Vendor Rate Upload<br>
                    9.  Added an option to activate/deactivate multiple accounts<br>
                    10. Added grid totals on Payments and CDR pages<br>
                    11. Fixed issue with xls file import<br>
                    12. Improved Account/Lead view section<br>
                    13. improved menu icons<br>
                    14. Added following menu options to Customer Panel: Monitor Dashboard,Commercials and CDR<br>
                </div>
            </li>
        </ul>
    </div>

</div>

<div class="clear"></div>

<h2>
    <span class="label label-success">4.02</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
        <ul class="version-highlights full-width">
            <li>
                <div class="notes full-width ">
                    <h3>ChangeLog</h3>
                    <h4>21-04-2016</h4>
                    1.  Added an option to create Estimates<br>
                    2.  Added an option to create Themes<br>
                    3.  Added filter by CLI , CLD and Zero Cost under CDR<br>
                    4.  Added Payment date filter on Payments page.<br>
                    5.  Add totals in Invoice section<br>
                    6.  Added option to setup Quarterly and Fortnightly invoices<br>
                    7.  Added Payment Method ‘Direct Debit’<br>
                    8.  Added an option to download Current, Future and All rates<br>
                    9.  Added an option to download files in Excel and CSV<br>
                    10. Added an option to export grids in Excel and CSV<br>
                    11. Added  new logic to calculate LCR and implemented same logic in Rate Generator<br>
                    12. Added an option to specify which field to change in Code deck bulk edit.<br>
                    13. Fixed issues with CDR re rating<br>
                    14. Changed Account/Lead cards layout<br>
                    15. Fixed issues with Vendor Blocking By Code under Vendor profiling<br>
                    16. Fixed rate table page as it was bit slow<br>
                    17. Added an option to add currency symbol against currency<br>
                    18. Displaying currency symbol instead of currency name<br>

                </div>
            </li>
        </ul>
    </div>

</div>

<div class="clear"></div>

<h2>
    <span class="label label-success">4.01</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
<ul class="version-highlights full-width">
    <li>
        <div class="notes full-width ">
                <h3>ChangeLog</h3>
                1. Payment Recall<br>
                2. Payment Bulk Upload<br>
                3. Dashboard Report Top Pin Used<br>
        </div>
    </li>
</ul>
 </div>

 </div>

<div class="clear"></div>

<h2>
    <span class="label label-success">4.00</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
<ul class="version-highlights full-width">
    <li>
        <div class="notes full-width ">
            <h3>ChangeLog</h3>
                1. Vendor Profiling<br>
                2. Invoice Sage Export<br>
                3. Exchange Rates<br>
                4. Account Activities, Log and Reminder Email<br>
                5. Dynamic User Roles & Permissions<br>
                6. Rate Table Upload<br>
                7. Linux-Mysql Compatibility<br>
                8. CDR Upload<br>
                9. CDR Re-Rating<br>
                10. CLI Verification<br>
                11. Customer RateSheet Bulk Email<br>
                12. Bulk Email Template<br>
        </div>
    </li>
</ul>
 </div>

 </div>

<div class="clear"></div>

<h2>
    <span class="label label-success">3.03</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
<ul class="version-highlights full-width">
    <li>
        <div class="notes full-width ">
            <h3>ChangeLog</h3>
                1. Billing<br>
                2. Invoices Send/Receive<br>
                3. Bulk Invoices Generation<br>
                4. Payments<br>
                5. Invoice Items<br>
                6. Invoice Template<br>
                7. CDR Upload<br>
                8. Summery Reports<br>
        </div>
    </li>
</ul>
 </div>
 </div>

<div class="clear"></div>

<h2>
    <span class="label label-success">3.02</span> &nbsp; Version Highlights
</h2>

<div class="col-md-12">
    <div class="row">
<ul class="version-highlights full-width">
    <li>
        <div class="notes full-width ">
            <h3>ChangeLog</h3>
                1. Sales Dashboard<br>
                2. Sippy Customer CDR Download<br>
                3. Various Summary Report<br>
                4. Job Process By User<br>
                5. Cron Job Management <br>
                6. Gateway Management <br>
                7. Amazon S3 Integration <br>
                8. Quick Jump from one account to another account <br>
                9. Routing Plan added <br>
                10. More operation rates like "bulk clear" and more filter available <br>
                11. Interval in RateSheet and RateTable- default from Coddedeck <br>
        </div>
    </li>
</ul>
 </div>
 </div>

<div class="clear"></div>

<h2>
    <span class="label label-success">3.01</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
<ul class="version-highlights full-width">
    <li>
        <div class="notes full-width ">
            <h3>ChangeLog</h3>
                1. Tax Management<br>
                2. Account Approval Process<br>
                3. Account Number Auto Generated<br>
                4. Currency and Time Zone For Company and Account<br>
                5. Interval added in Rate Management<br>
            
        </div>
    </li>
</ul>
 </div>
 </div>
<div class="clear"></div>

<h2>
    <span class="label label-success">3.00</span> &nbsp; Version Highlights
</h2>
<div class="col-md-12">
    <div class="row">
<ul class="version-highlights full-width">

    <li>
        <div class="notes full-width ">
            <h3>Rate</h3>

                1. Customers,Vendors and User Management<br>
                2. Rates Notification and export XLS<br>
                3. Rates Scheduling (effective / end dates)<br>
                4. A 2 Z ​Rate Generator <br>



        </div>
    </li>
    <li>
        <div class="notes full-width ">
            <h3>CRM</h3>

                Manage Leads and Contacts.



        </div>
    </li>
 
    <li>
        <div class="notes full-width ">
            <h3>Routing</h3>

                Vendor Code blocking <br>
                Vendor rate sheet import \ export
.



        </div>
    </li>
 
</ul>
 </div>
 </div>
<div class="clear"></div>
@stop