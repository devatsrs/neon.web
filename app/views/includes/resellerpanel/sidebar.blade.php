<div class="sidebar-menu">
    <header class="logo-env">

        <!-- logo -->
        <div class="logo">
            @if(Session::get('user_site_configrations.Logo')!='')<a href="#"> <img src="{{Session::get('user_site_configrations.Logo')}}" width="120" alt="" /> </a>
            @endif
            @if(strtolower(getenv('APP_ENV'))!='production')
                <br/>
                <br/>
                <div class="text-center"><button class="text-center  btn btn-danger btn-sm" type="submit">STAGING</button></div>
            @endif
        </div>

        <!-- logo collapse icon -->
        <div class="sidebar-collapse">
            <a href="#" class="sidebar-collapse-icon with-animation">
                <!-- add class "with-animation" if you want sidebar to have animation during expanding/collapsing transition -->
                <i class="entypo-menu"></i>
            </a>
        </div>

        <!-- open/close menu icon (do not remove if you want to enable menu on mobile devices) -->
        <div class="sidebar-mobile-menu visible-xs">
            <a href="#" class="with-animation">
                <!-- add class "with-animation" to support animation -->
                <i class="entypo-menu"></i>
            </a>
        </div>

    </header>

    <ul id="main-menu" class="">
        <li class="two-links"> <a href="{{URL::to('/accounts')}}" class="first"> <i class="fa fa-users"></i> <span>&nbsp;Accounts</span> </a> <a href="{{URL::to('accounts/create')}}" class="last"><i class="fa fa-plus-circle" style="color: #fff;"></i></a> </li>
        <!--<li> <a href="{{URL::to('/reseller')}}"><i class="entypo-users"></i>  <span>Reseller</span> </a> </li>-->
        <li class="{{check_uri('Rates')}}"> <a href="#"> <i class="fa fa-table"></i> <span>&nbsp;Rate Management</span> </a>
            <ul>
                <li> <a href="{{URL::to('/rate_tables')}}">  <span>Rate Tables</span> </a> </li>
                <li> <a href="{{URL::to('/lcr')}}">  <span>LCR List</span> </a> </li>
                <li> <a href="{{URL::to('/rategenerators')}}">  <span>Rate Generator</span> </a> </li>
                <li> <a href="{{URL::to('/rate_compare')}}">  <span>Rate Analysis</span> </a> </li>
                <li> <a href="{{URL::to('/vendor_profiling')}}">  <span>Vendor Profiling</span> </a> </li>
            </ul>
        </li>
        <li class="{{check_uri('Billing')}}"> <a href="#"> <i class="fa fa-credit-card" ></i> <span>Billing</span> </a>
            <ul>
                <li> <a href="{{URL::to('/estimates')}}">  <span>Estimates</span> </a> </li>
                <li> <a href="{{URL::to('/invoice')}}">  <span>Invoices</span> </a> </li>
                <li> <a href="{{URL::to('/payments')}}">  <span>Payments</span> </a> </li>
                <li> <a href="{{URL::to('/account_statement')}}">  <span>Account Statement</span> </a> </li>
                <li> <a href="{{URL::to('/invoice_template')}}">  <span>Invoice Template</span> </a> </li>
                <li> <a href="{{URL::to('/taxrate')}}">  <span>Tax Rate</span> </a> </li>
                <li> <a href="{{URL::to('/billing_subscription')}}">  <span>Subscription</span> </a> </li>
                <li> <a href="{{URL::to('products')}}">  <span>Items</span> </a> </li>
                <li> <a href="{{URL::to('/billing_class')}}">  <span>Billing Class</span> </a> </li>
                <li> <a href="{{URL::to('/cdr_upload')}}">  <span>CDR Upload</span> </a> </li>
                <li> <a href="{{URL::to('/cdr_show')}}">  <span>CDR</span> </a> </li>
            </ul>
        </li>
        <li class="two-links"> <a href="{{Url::to('/report')}}" class="first"> <i class="fa fa-line-chart"></i><span>Reports</span></a> <a href="{{URL::to('report/create')}}" class="last"><i class="fa fa-plus-circle" style="color: #fff;"></i></a> </li>
        <li class="{{check_uri('Settings')}}"> <a href="#"> <i class="fa fa-cogs"></i> <span>Settings</span> </a>
            <ul>
                <!--<li> <a href="{{Url::to('/trunks')}}">  <span>Trunks</span> </a> </li>-->
                <li> <a href="{{Url::to('/codedecks')}}">  <span>Code Decks</span> </a> </li>
                <li> <a href="{{URL::to('/dialstrings')}}">  <span>Dial String</span> </a> </li>
                <!--<li> <a href="{{Url::to('/currency')}}">  <span>Currency</span> </a> </li>-->
                <li> <a href="{{Url::to('/currency_conversion')}}">  <span>Exchange Rate</span> </a> </li>
                <li><a href="{{URL::to('/destination_group_set')}}"><span>Destination Group</span></a></li>
            </ul>
        </li>
        <li> <a href="{{URL::to('/integration')}}"><i class="fa fa-codepen"></i>   <span>Integration</span> </a> </li>
        <li class="{{check_uri('Admin')}}"> <a href="#"> <i class="fa fa-lock"></i> <span>&nbsp;&nbsp;&nbsp;Admin</span> </a>
            <ul>
                <li> <a href="{{Url::to('/notification')}}">  <span>Notifications</span> </a> </li>
                @if(Reseller::is_AllowWhiteLabel())
                    <li> <a href="{{Url::to('/themes')}}">  <span>Themes</span> </a> </li>
                @endif
                <li> <a href="{{URL::to('/email_template')}}">  <span>Email Templates</span> </a> </li>
                <li> <a href="{{URL::to('/noticeboard')}}">  <span>Notice Board</span> </a> </li>
            </ul>
        </li>
        <li> <a href="{{Url::to('cronjob_monitor')}}"> <i class="glyphicon glyphicon-time"></i> <span>&nbsp;Cron Jobs</span> </a> </li>
        <li> <a href="{{Url::to('company')}}"> <i class="glyphicon glyphicon-home"></i> <span>&nbsp;Company</span> </a> </li>
    </ul>


    <ul id="main-menu" class=""><span style="color: #ffffff;font-size: 13px;">&nbsp;&nbsp;Personal Menu</span>
        <li class="{{check_uri('Customer_billing')}}"><a href="#"><i class="fa fa-credit-card"></i><span>Billing</span></a>
            <ul>
                <li><a href="{{Url::to('customer/invoice')}}"><span>Invoices</span></a></li>
                <li><a href="{{URL::to('customer/payments')}}"><span>Payments</span></a></li>
                <li><a href="{{URL::to('customer/account_statement')}}"><span>Account Statement</span></a></li>
                <li><a href="{{URL::to('customer/PaymentMethodProfiles')}}"><span>Payment Method Profiles</span></a></li>
                <li><a href="{{URL::to('customer/cdr')}}"><span>CDR</span></a></li>
            </ul>
        </li>
        <li><a href="{{URL::to('customer/customers_rates')}}"><i class="fa fa-table"></i><span>Commercial</span></a></li>
        <li><a href="{{URL::to('reseller/profile')}}"><i class="glyphicon glyphicon-user"></i><span>Profile</span></a></li>
    </ul>

</div>