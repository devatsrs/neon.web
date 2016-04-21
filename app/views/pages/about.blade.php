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
    <h1>NEON - 4.02</h1>


    <p>
        <br />
    <div class="btn btn-primary btn-lg">Current Version: <strong>v4.02</strong></div>
</p>
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