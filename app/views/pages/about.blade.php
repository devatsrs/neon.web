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

<div class="jumbotron">
    <h1>&nbsp;4.14</h1>


    <p>
        <br />
    <div class="btn btn-primary btn-lg">Current Version: <strong>v4.14</strong></div>
</p>
</div>

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