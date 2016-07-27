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
    <h1>NEON - 4.05</h1>


    <p>
        <br />
    <div class="btn btn-primary btn-lg">Current Version: <strong>v4.05</strong></div>
</p>
</div>

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
@stop