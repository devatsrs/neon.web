<?php $LicenceApiResponse = Session::get('LicenceApiResponse','');  ?>
<div class="sidebar-menu">
  <header class="logo-env">   
    <!-- logo -->
    <div class="logo"> @if(Session::get('user_site_configrations.Logo')!='')<a href="#"> <img src="{{Session::get('user_site_configrations.Logo')}}" width="120" alt="" /> </a> @endif
      @if(strtolower(getenv('APP_ENV'))!='production') <br/>
      <br/>
      <div class="text-center">
        <button class="text-center  btn btn-danger btn-sm" type="submit">STAGING</button>
      </div>
      @endif </div>
    
    <!-- logo collapse icon -->
    <div class="sidebar-collapse"> <a href="#" class="sidebar-collapse-icon with-animation"> 
      <!-- add class "with-animation" if you want sidebar to have animation during expanding/collapsing transition --> 
      <i class="entypo-menu"></i> </a> </div>
    
    <!-- open/close menu icon (do not remove if you want to enable menu on mobile devices) -->
    <div class="sidebar-mobile-menu visible-xs"> <a href="#" class="with-animation"> 
      <!-- add class "with-animation" to support animation --> 
      <i class="entypo-menu"></i> </a> </div>
  </header>
  <ul id="main-menu" class="">
    <li class="{{check_uri('Customer_billing')}}"> <a href="#"> <i class="entypo-monitor"></i> <span>Reseller Panel</span> </a>
      <ul>
        <li> <a href="{{Url::to('reseller/monitor')}}"><span>Dashboard</span> </a> </li>
        <li> <a href="{{URL::to('reseller/dashboard')}}"> <span>Analysis</span> </a> </li>
        <li> <a href="{{Url::to('reseller/invoice')}}"> <span>Invoices</span> </a> </li>
        <li> <a href="{{URL::to('reseller/payments')}}"> <span>Payments</span> </a> </li>
        <li> <a href="{{URL::to('reseller/account_statement')}}"> <span>Account Statement</span> </a> </li>
        @if (is_authorize())
        <li> <a href="{{URL::to('reseller/PaymentMethodProfiles')}}"> <span>Payment Method Profiles</span> </a> </li>
        @endif
        <li> <a href="{{URL::to('reseller/cdr')}}"> <span>CDR</span> </a> </li>
        @if(getenv('CUSTOMER_COMMERCIAL_DISPLAY') == 1)
        <li> <a href="{{URL::to('reseller/customers_rates')}}"> <i class="fa fa-table"></i> <span>Commercial</span> </a> </li>
        @endif
        <li> <a href="{{(Reseller::get_currentUser()->IsVendor == 1 && Reseller::get_currentUser()->IsReseller == 0 ) ? Url::to('reseller/vendor_analysis') : Url::to('reseller/analysis')}}"> <!--<i class="fa fa-bar-chart"></i>--> <span>Analysis</span> </a> </li>
        <li> <a href="{{URL::to('reseller/subscriptions')}}"> <!--<i class="entypo-info"></i>--> <span>Subscriptions</span> </a> </li>
        <li> <a href="{{URL::to('reseller/profile')}}"> <!--<i class="glyphicon glyphicon-user"></i>--> <span>Profile</span> </a> </li>
      </ul>
    </li>
        @if( Reseller::checkResellerPermission('RmDashboard','All')  || Reseller::checkResellerPermission('SalesDashboard','All')||Reseller::checkResellerPermission('BillingDashboard','All'))
    <li class=""><a href="{{Url::to('/monitor')}}"><i class="entypo-monitor"></i><span>Monitor Dashboard</span></a>
    </li>
    @endif
    @if(Reseller::checkResellerPermission('Leads','View'))
    <li> <a href="{{Url::to('/leads')}}"> <i class="fa fa-building" aria-hidden="true"></i> <span>&nbsp;Leads</span> </a> </li>
    @endif
    @if( Reseller::checkResellerPermission('Contacts','View'))
    <li> <a href="{{Url::to('/contacts')}}"> <i class="entypo-users"></i><span>Contacts</span></a></li>
    @endif
    @if( Reseller::checkResellerPermission('Account','View'))
    <li> <a href="{{URL::to('/accounts')}}"> <i class="fa fa-users"></i> <span>&nbsp;Accounts</span> </a> </li>
    @endif
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_RM || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if( Reseller::checkResellerPermission('RateTables','View') || Reseller::checkResellerPermission('LCR','All') ||
    Reseller::checkResellerPermission('RateGenerator','View') || Reseller::checkResellerPermission('VendorProfiling','All'))
    <li class="{{check_uri('Rates')}}"> <a href="#"> <i class="fa fa-table"></i> <span>&nbsp;Rate Management</span> </a>
      <ul>
        @if(Reseller::checkResellerPermission('RateTables','View'))
        <li> <a href="{{URL::to('/rate_tables')}}">  <span>Rate Tables</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('LCR','All'))
        <li> <a href="{{URL::to('/lcr')}}">  <span>LCR List</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('RateGenerator','View'))
        <li> <a href="{{URL::to('/rategenerators')}}">  <span>Rate Generator</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('VendorProfiling','All'))
        <li> <a href="{{URL::to('/vendor_profiling')}}">  <span>Vendor Profiling</span> </a> </li>
        @endif
      </ul>
    </li>
    @endif
    @endif
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if( Reseller::checkResellerPermission('SummaryReports','All'))
        {{--<li > <a href="#"> <i class="entypo-layout"></i> <span>Summary Reports</span> </a>
          <ul>
            <li> <a href="{{URL::to('/summaryreport')}}"> <i class="entypo-pencil"></i> <span>Summary reports  by prefix </span> </a> </li>
            <li> <a href="{{URL::to('/summaryreport/summrybycountry')}}"> <i class="entypo-pencil"></i> <span>Summary reports  by country </span> </a> </li>
            <li> <a href="{{URL::to('/summaryreport/summrybycustomer')}}"> <i class="entypo-pencil"></i> <span>Summary reports  by customer </span> </a> </li>
          </ul>
        </li>--}}
    @endif
    <li><a href="#"><i class="glyphicon glyphicon-th"></i><span>&nbsp;&nbsp;CRM</span></a>
        <ul>
            @if(Reseller::checkResellerPermission('OpportunityBoard','View'))
                <li><a href="{{URL::to('/opportunityboards')}}"><span>Opportunities</span></a></li>
            @endif
            @if(Reseller::checkResellerPermission('Task','View'))
                <li><a href="{{URL::to('/task')}}"><span>Tasks</span></a></li>
            @endif
        </ul>
    </li>
    @endif
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type'] == Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if(Reseller::checkResellerPermission('Invoice','View')  || Reseller::checkResellerPermission('BillingSubscription','View') ||
    Reseller::checkResellerPermission('Payments','View') || Reseller::checkResellerPermission('AccountStatement','All') ||
    Reseller::checkResellerPermission('Products','View') || Reseller::checkResellerPermission('InvoiceTemplates','View') ||
    Reseller::checkResellerPermission('TaxRates','View') || Reseller::checkResellerPermission('CDR','Upload') || Reseller::checkResellerPermission('CDR','View') )
    <li class="{{check_uri('Billing')}}"> <a href="#"> <i class="fa fa-credit-card" ></i> <span>Billing</span> </a>
      <ul>
        @if(Reseller::checkResellerPermission('BillingDashboard','All'))
          <li> <a href="{{Url::to('/billingdashboard')}}"><span>Analysis</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('Invoice','View'))
        <li> <a href="{{URL::to('/estimates')}}">  <span>Estimates</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('Invoice','View'))
        <li> <a href="{{URL::to('/invoice')}}">  <span>Invoices</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('Disputes','View'))
        <li> <a href="{{URL::to('/disputes')}}">  <span>Disputes</span> </a> </li>
        @endif

        @if(Reseller::checkResellerPermission('BillingSubscription','View'))
        <li> <a href="{{URL::to('/billing_subscription')}}">  <span>Subscription</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('Payments','View'))
        <li> <a href="{{URL::to('/payments')}}">  <span>Payments</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('AccountStatement','All'))
        <li> <a href="{{URL::to('/account_statement')}}">  <span>Account Statement</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('Products','View'))
        <li> <a href="{{URL::to('products')}}">  <span>Items</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('InvoiceTemplates','View'))
        <li> <a href="{{URL::to('/invoice_template')}}">  <span>Invoice Template</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('TaxRates','View'))
        <li> <a href="{{URL::to('/taxrate')}}">  <span>Tax Rate</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('CDR','Upload'))
        <li> <a href="{{URL::to('/cdr_upload')}}">  <span>CDR Upload</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('CDR','View'))
        <li> <a href="{{URL::to('/cdr_show')}}">  <span>CDR</span> </a> </li>
        @endif 
      </ul>
    </li>
    @endif
    @endif
    @if( Reseller::checkResellerPermission('Analysis','All'))
      <li> <a href="{{Url::to('/analysis')}}"> <i class="fa fa-bar-chart"></i> <span>Analysis</span> </a> </li>
    @endif
    @if(Reseller::checkResellerPermission('MyProfile','All') || Reseller::checkResellerPermission('Users','All') ||
    Reseller::checkResellerPermission('Trunk','View') || Reseller::checkResellerPermission('Gateway','View') ||
    Reseller::checkResellerPermission('Currency','View') || Reseller::checkResellerPermission('ExchangeRate','View') ||
    Reseller::checkResellerPermission('CodeDecks','View'))
    <li class="{{check_uri('Settings')}}"> <a href="#"> <i class="fa fa-cogs"></i> <span>Settings</span> </a>
      <ul>
        @if(Reseller::checkResellerPermission('MyProfile','All') || Reseller::checkResellerPermission('Users','All') )
        <li> <a href="{{URL::to('users/edit_profile/'. User::get_userID() )}}">  <span>My Profile</span> </a> </li>
        @endif
        @if( Reseller::checkResellerPermission('Trunk','View') )
        <li> <a href="{{Url::to('/trunks')}}">  <span>Trunks</span> </a> </li>
        @endif
        @if( Reseller::checkResellerPermission('CodeDecks','View') )
        <li> <a href="{{Url::to('/codedecks')}}">  <span>Code Decks</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('Gateway','View'))
        <li> <a href="{{Url::to('/gateway')}}">  <span>Gateway</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('Currency','View'))
        <li> <a href="{{Url::to('/currency')}}">  <span>Currency</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('ExchangeRate','View'))
        <li> <a href="{{Url::to('/currency_conversion')}}">  <span>Exchange Rate</span> </a> </li>
        @endif        
      </ul>
    </li>
    @endif
    @if( Reseller::checkResellerPermission('Users','View') || Reseller::checkResellerPermission('AccountChecklist','View') ||
    Reseller::checkResellerPermission('CronJob','View') || Reseller::checkResellerPermission('UploadFileTemplate','View'))
    <li class="{{check_uri('Admin')}}"> <a href="#"> <i class="fa fa-lock"></i> <span>&nbsp;&nbsp;&nbsp;Admin</span> </a>
      <ul>
        @if( Reseller::checkResellerPermission('Users','View'))
        <li> <a href="{{Url::to('users')}}">  <span>Users</span> </a> </li>
        @endif
        @if(User::is_admin())
        <li> <a href="{{Url::to('roles')}}">  <span>User Roles</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('themes','View'))
        <li> <a href="{{Url::to('/themes')}}">  <span>Themes</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('AccountChecklist','View'))
        <li> <a href="{{Url::to('accountapproval')}}">  <span>Account Checklist</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('CronJob','View'))
        <li> <a href="{{URL::to('/cronjobs')}}">  <span>Cron Jobs</span> </a> </li>
        @endif
        @if(Reseller::checkResellerPermission('UploadFileTemplate','view'))
        <li> <a href="{{URL::to('/uploadtemplate')}}">  <span>Vendor Template</span> </a> </li>
        @endif
         @if( Reseller::checkResellerPermission('EmailTemplate','View'))
        <li> <a href="{{URL::to('/email_template')}}">  <span>Email Templates</span> </a> </li>
    	@endif
      </ul>
    </li>
    @endif
    @if( Reseller::checkResellerPermission('Jobs','View'))
    <li> <a href="{{Url::to('jobs')}}"> <i class="glyphicon glyphicon-time"></i> <span>&nbsp;Jobs</span> </a> </li>
    @endif
    
    @if( Reseller::checkResellerPermission('Company','View'))
    <li> <a href="{{Url::to('company')}}"> <i class="glyphicon glyphicon-home"></i> <span>&nbsp;Company</span> </a> </li>
    @endif
    @if( Reseller::checkResellerPermission('Pages','About'))
    <li> <a href="{{Url::to('/about')}}"> <i class="glyphicon glyphicon-info-sign"></i> <span>&nbsp;About</span> </a> </li>
    @endif
  </ul>
</div>