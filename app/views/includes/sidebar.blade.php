<?php $LicenceApiResponse = Session::get('LicenceApiResponse',''); ?>
<div class="sidebar-menu">
  <header class="logo-env"> 
    <!-- logo -->
    <div class="logo"><!-- Added by Abubakar --> 
      @if(Session::get('user_site_configrations.Logo')!='')<a href="{{Url::to('/process_redirect')}}"> <img src="{{Session::get('user_site_configrations.Logo')}}" width="120" alt="" /> </a>
      @endif
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
    <!-- add class "multiple-expanded" to allow multiple submenus to open --> 
    <!-- class "auto-inherit-active-class" will automatically add "active" class for parent elements who are marked already with class "active" --> 
    <!-- Search Bar --> 
    <!--        <li id="search">
                    <form method="get" action="">
                        <input type="text" name="q" class="search-input" placeholder="Search something..." />
                        <button type="submit">
                            <i class="entypo-search"></i>
                        </button>
                        </button>
                    </form>
                </li>--> 
    @if( User::checkCategoryPermission('RmDashboard','All')  || User::checkCategoryPermission('SalesDashboard','All')||User::checkCategoryPermission('BillingDashboard','All'))
    <li class=""> <a href="#"> <i class="entypo-gauge"></i> <span>Dashboard</span> </a>
      <ul>
        @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_RM || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
        {{--@if(User::checkCategoryPermission('RmDashboard','All'))
        <li> <a href="{{action('dashboard')}}"> <i class="entypo-pencil"></i> <span>RM Dashboard</span> </a> </li>
        @endif--}}
        @if(User::checkCategoryPermission('MonitorDashboard','All'))
          <li> <a href="{{Url::to('/monitor')}}"> <i class="entypo-monitor"></i> <span>Monitor Dashboard</span> </a> </li>
        @endif
        @endif
        {{--@if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
        @if(User::checkCategoryPermission('SalesDashboard','All'))
        <li> <a href="{{action('salesdashboard')}}"> <i class="entypo-pencil"></i> <span>Sales Dashboard</span> </a> </li>
        @endif
        @endif--}}
      </ul>
    </li>
    @endif
    @if(User::checkCategoryPermission('Leads','View'))
    <li> <a href="{{Url::to('/leads')}}"> <i class="entypo-layout"></i> <span>Leads</span> </a> </li>
    @endif
    @if( User::checkCategoryPermission('Contacts','View'))
    <li> <a href="{{Url::to('/contacts')}}"> <i class="entypo-layout"></i> <span>Contacts</span> </a> </li>
    @endif
    @if( User::checkCategoryPermission('Account','View'))
    <li> <a href="{{URL::to('/accounts')}}"> <i class="entypo-layout"></i> <span>Accounts</span> </a> </li>
    @endif
    @if( User::checkCategoryPermission('EmailTemplate','View'))
    <li> <a href="#"> <i class="entypo-layout"></i> <span>Template Management</span> </a>
      <ul>
        <li> <a href="{{URL::to('/email_template')}}"> <i class="entypo-pencil"></i> <span>Email Templates</span> </a> </li>
      </ul>
    </li>
    @endif
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_RM || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if( User::checkCategoryPermission('RateTables','View') || User::checkCategoryPermission('LCR','All') ||
    User::checkCategoryPermission('RateGenerator','View') || User::checkCategoryPermission('VendorProfiling','All'))
    <li> <a href="#"> <i class="entypo-layout"></i> <span>Rate Management</span> </a>
      <ul>
        @if(User::checkCategoryPermission('RateTables','View'))
        <li> <a href="{{URL::to('/rate_tables')}}"> <i class="entypo-pencil"></i> <span>Rate Tables</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('LCR','All'))
        <li> <a href="{{URL::to('/lcr')}}"> <i class="entypo-pencil"></i> <span>LCR List</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('RateGenerator','View'))
        <li> <a href="{{URL::to('/rategenerators')}}"> <i class="entypo-pencil"></i> <span>Rate Generator</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('VendorProfiling','All'))
        <li> <a href="{{URL::to('/vendor_profiling')}}"> <i class="entypo-pencil"></i> <span>Vendor Profiling</span> </a> </li>
        @endif
      </ul>
    </li>
    @endif
    @endif
    @if( User::checkCategoryPermission('Analysis','All'))
    <li> <a href="{{Url::to('/analysis')}}"> <i class="fa fa-bar-chart"></i> <span>Analysis</span> </a> </li>
    @endif
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if( User::checkCategoryPermission('SummaryReports','All'))
        {{--<li > <a href="#"> <i class="entypo-layout"></i> <span>Summary Reports</span> </a>
          <ul>
            <li> <a href="{{URL::to('/summaryreport')}}"> <i class="entypo-pencil"></i> <span>Summary reports  by prefix </span> </a> </li>
            <li> <a href="{{URL::to('/summaryreport/summrybycountry')}}"> <i class="entypo-pencil"></i> <span>Summary reports  by country </span> </a> </li>
            <li> <a href="{{URL::to('/summaryreport/summrybycustomer')}}"> <i class="entypo-pencil"></i> <span>Summary reports  by customer </span> </a> </li>
          </ul>
        </li>--}}
    @endif
    @endif
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type'] == Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if(User::checkCategoryPermission('Invoice','View')  || User::checkCategoryPermission('BillingSubscription','View') ||
    User::checkCategoryPermission('Payments','View') || User::checkCategoryPermission('AccountStatement','All') ||
    User::checkCategoryPermission('Products','View') || User::checkCategoryPermission('InvoiceTemplates','View') ||
    User::checkCategoryPermission('TaxRates','View') || User::checkCategoryPermission('CDR','Upload') || User::checkCategoryPermission('CDR','View') )
    <li> <a href="#"> <i class="entypo-layout"></i> <span>Billing</span> </a>
      <ul>
        @if(User::checkCategoryPermission('Invoice','View'))
        <li> <a href="{{URL::to('/estimates')}}"> <i class="entypo-pencil"></i> <span>Estimates</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('Invoice','View'))
        <li> <a href="{{URL::to('/invoice')}}"> <i class="entypo-pencil"></i> <span>Invoices</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('BillingDashboard','All'))
            <li> <a href="{{Url::to('/billingdashboard')}}"> <i class="fa fa-bar-chart"></i> <span>Billing Analysis</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('BillingSubscription','View'))
        <li> <a href="{{URL::to('/billing_subscription')}}"> <i class="entypo-pencil"></i> <span>Subscription</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('Payments','View'))
        <li> <a href="{{URL::to('/payments')}}"> <i class="entypo-pencil"></i> <span>Payments</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('AccountStatement','All'))
        <li> <a href="{{URL::to('/account_statement')}}"> <i class="entypo-pencil"></i> <span>Account Statement</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('Products','View'))
        <li> <a href="{{URL::to('products')}}"> <i class="entypo-pencil"></i> <span>Items</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('InvoiceTemplates','View'))
        <li> <a href="{{URL::to('/invoice_template')}}"> <i class="entypo-pencil"></i> <span>Invoice Template</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('TaxRates','View'))
        <li> <a href="{{URL::to('/taxrate')}}"> <i class="entypo-pencil"></i> <span>Tax Rate</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('CDR','Upload'))
        <li> <a href="{{URL::to('/cdr_upload')}}"> <i class="entypo-pencil"></i> <span>CDR Upload</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('CDR','View'))
        <li> <a href="{{URL::to('/cdr_show')}}"> <i class="entypo-pencil"></i> <span>CDR</span> </a> </li>
        @endif 
        <!--<li>
<a href="{{URL::to('/cdr_recal')}}">
  <i class="entypo-pencil"></i>
  <span>CDR Recalculate</span>
</a>
</li>
<li>
<a href="{{URL::to('/cdr_upload/delete')}}">
  <i class="entypo-pencil"></i>
  <span>CDR Delete</span>
</a>
</li>-->
      </ul>
    </li>
    @endif
    @endif
    @if(User::checkCategoryPermission('MyProfile','All') || User::checkCategoryPermission('Users','All') ||
    User::checkCategoryPermission('Trunk','View') || User::checkCategoryPermission('Gateway','View') ||
    User::checkCategoryPermission('Currency','View') || User::checkCategoryPermission('ExchangeRate','View') ||
    User::checkCategoryPermission('CodeDecks','View'))
    <li> <a href="#"> <i class="entypo-layout"></i> <span>Settings</span> </a>
      <ul>
        @if(User::checkCategoryPermission('MyProfile','All') || User::checkCategoryPermission('Users','All') )
        <li> <a href="{{URL::to('users/edit_profile/'. User::get_userID() )}}"> <i class="entypo-pencil"></i> <span>My Profile</span> </a> </li>
        @endif
        @if( User::checkCategoryPermission('Trunk','View') )
        <li> <a href="{{Url::to('/trunks')}}"> <i class="entypo-pencil"></i> <span>Trunks</span> </a> </li>
        @endif
        @if( User::checkCategoryPermission('CodeDecks','View') )
        <li> <a href="{{Url::to('/codedecks')}}"> <i class="entypo-pencil"></i> <span>Code Decks</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('Gateway','View'))
        <li> <a href="{{Url::to('/gateway')}}"> <i class="entypo-pencil"></i> <span>Gateway</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('Currency','View'))
        <li> <a href="{{Url::to('/currency')}}"> <i class="entypo-pencil"></i> <span>Currency</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('ExchangeRate','View'))
        <li> <a href="{{Url::to('/currency_conversion')}}"> <i class="entypo-pencil"></i> <span>Exchange Rate</span> </a> </li>
        @endif        
      </ul>
    </li>
    @endif
    @if( User::checkCategoryPermission('Users','View') || User::checkCategoryPermission('AccountChecklist','View') ||
    User::checkCategoryPermission('CronJob','View') || User::checkCategoryPermission('UploadFileTemplate','View'))
    <li> <a href="#"> <i class="entypo-layout"></i> <span>Admin</span> </a>
      <ul>
        @if( User::checkCategoryPermission('Users','View'))
        <li> <a href="{{Url::to('users')}}"> <i class="entypo-pencil"></i> <span>Users</span> </a> </li>
        @endif
        @if(User::is_admin())
        <li> <a href="{{Url::to('roles')}}"> <i class="entypo-pencil"></i> <span>User Roles</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('themes','View'))
        <li> <a href="{{Url::to('/themes')}}"> <i class="entypo-pencil"></i> <span>Themes</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('AccountChecklist','View'))
        <li> <a href="{{Url::to('accountapproval')}}"> <i class="entypo-pencil"></i> <span>Account Checklist</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('CronJob','View'))
        <li> <a href="{{URL::to('/cronjobs')}}"> <i class="entypo-pencil"></i> <span>Cron Jobs</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('UploadFileTemplate','view'))
        <li> <a href="{{URL::to('/uploadtemplate')}}"> <i class="entypo-pencil"></i> <span>Upload File Template</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('Jobs','view')) 
        <!-- <li>
                            <a href="{URL::to('/activejob')}">
                                <i class="entypo-pencil"></i>
                                <span>Active Jobs</span>
                            </a>
                        </li>--> 
        @endif
      </ul>
    </li>
    @endif
    @if( User::checkCategoryPermission('Jobs','View'))
    <li> <a href="{{Url::to('jobs')}}"> <i class="entypo-layout"></i> <span>Jobs</span> </a> </li>
    @endif
    
    @if( User::checkCategoryPermission('Company','View'))
    <li> <a href="{{Url::to('company')}}"> <i class="entypo-layout"></i> <span>Company</span> </a> </li>
    @endif
    @if( User::checkCategoryPermission('Pages','About'))
    <li> <a href="{{Url::to('/about')}}"> <i class="entypo-layout"></i> <span>About</span> </a> </li>
    @endif
  </ul>
</div>
