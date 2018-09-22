<?php $LicenceApiResponse = Session::get('LicenceApiResponse','');  ?>
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
    @if(User::checkCategoryPermission('MonitorDashboard','All'))
    <li class=""><a href="{{Url::to('/monitor')}}"><i class="entypo-monitor"></i><span>Monitor Dashboard</span></a>
    </li>
    @endif
    @if(User::checkCategoryPermission('Leads','View'))
      @if(User::checkCategoryPermission('Leads','Add'))
        <li class="two-links"> <a href="{{Url::to('/leads')}}" class="first"> <i class="fa fa-building" aria-hidden="true"></i> <span>&nbsp;Leads</span> </a> <a href="{{URL::to('leads/create')}}" class="last"><i class="fa fa-plus-circle" style="color: #fff;"></i></a> </li>
      @else
        <li> <a href="{{Url::to('/leads')}}"> <i class="fa fa-building" aria-hidden="true"></i> <span>&nbsp;Leads</span> </a></li>
      @endif
    @endif
    @if( User::checkCategoryPermission('Contacts','View'))
      @if( User::checkCategoryPermission('Contacts','Add'))
        <li class="two-links"> <a href="{{Url::to('/contacts')}}" class="first"> <i class="entypo-users"></i><span>Contacts</span></a> <a href="{{URL::to('contacts/create')}}" class="last"><i class="fa fa-plus-circle" style="color: #fff;"></i></a> </li>
      @else
        <li> <a href="{{Url::to('/contacts')}}"> <i class="entypo-users"></i><span>Contacts</span></a></li>
      @endif
    @endif
    @if( User::checkCategoryPermission('Account','View'))
      @if( User::checkCategoryPermission('Account','Add'))
        <li class="two-links"> <a href="{{URL::to('/accounts')}}" class="first"> <i class="fa fa-users"></i> <span>&nbsp;Accounts</span> </a> <a href="{{URL::to('accounts/create')}}" class="last"><i class="fa fa-plus-circle" style="color: #fff;"></i></a> </li>
      @else
        <li> <a href="{{URL::to('/accounts')}}" class="first"> <i class="fa fa-users"></i> <span>&nbsp;Accounts</span> </a></li>
      @endif
    @endif
    @if(User::checkCategoryPermission('Reseller','View'))
    <li> <a href="{{URL::to('/reseller')}}">  <i class="entypo-users"></i><span>Reseller</span> </a> </li>
    @endif
         <!--tickets start -->
    @if(Tickets::CheckTicketLicense() && User::checkCategoryPermission('Tickets','View'))
    <li class="{{check_uri('tickets')}} {{ Tickets::CheckTicketLicense() && User::checkCategoryPermission('Tickets','Add') ? "two-links" : "" }}"><a href="#" class="first"><i class="fa fa-ticket"></i><span>Ticket Management</span></a>
      @if(Tickets::CheckTicketLicense() && User::checkCategoryPermission('Tickets','Add'))
        <span onclick="location.href=$(this).attr('href');" href="{{URL::to('tickets/add')}}" class="last"><i class="fa fa-plus-circle" style="color: #fff;"></i></span>
      @endif
      <ul>
          @if(User::checkCategoryPermission('TicketDashboard','View'))
              <li> <a href="{{URL::to('/ticketdashboard')}}">  <span>Dashboard</span> </a> </li>
          @endif
        @if(User::checkCategoryPermission('Tickets','View'))
        <li> <a href="{{URL::to('/tickets')}}">  <span>Tickets</span> </a> </li>
        @endif
         @if(User::checkCategoryPermission('TicketsFields','Edit'))
        <li> <a href="{{URL::to('/ticketsfields')}}">  <span>Tickets Fields</span></a></li>
        @endif
        @if(User::checkCategoryPermission('TicketsGroups','View'))
        <li> <a href="{{URL::to('/ticketgroups')}}">  <span>Groups</span></a> </li>
        @endif
         @if(User::checkCategoryPermission('TicketsSla','View'))
        <li> <a href="{{URL::to('/tickets/sla_policies')}}">  <span>SLA Policies</span></a> </li>
        @endif
        @if(User::checkCategoryPermission('BusinessHours','View'))
        <li> <a href="{{URL::to('/businesshours')}}">  <span>Business Hours</span></a> </li>
        @endif
         @if(User::checkCategoryPermission('TicketImportRules','View'))
        <li> <a href="{{URL::to('/tickets/importrules')}}">  <span>Import Rules</span></a> </li>
        @endif
      </ul>
    </li>
    @endif
    <!--tickets end --> 
	
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_RM || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if( User::checkCategoryPermission('RateTables','View') || User::checkCategoryPermission('LCR','All') ||
    User::checkCategoryPermission('RateGenerator','View') || User::checkCategoryPermission('VendorProfiling','All'))
    <li class="{{check_uri('Rates')}}"> <a href="#"> <i class="fa fa-table"></i> <span>&nbsp;Rate Management</span> </a>
      <ul>
        @if(User::checkCategoryPermission('RateUpload','All'))
          <li> <a href="{{URL::to('/rate_upload')}}">  <span>Upload Rates</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('RateTables','View'))
        <li> <a href="{{URL::to('/rate_tables')}}">  <span>Rate Tables</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('LCR','All'))
        <li> <a href="{{URL::to('/lcr')}}">  <span>LCR List</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('RateGenerator','View'))
        <li> <a href="{{URL::to('/rategenerators')}}">  <span>Rate Generator</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('RateCompare','All'))
          <li> <a href="{{URL::to('/rate_compare')}}">  <span>Rate Analysis</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('VendorProfiling','All'))
        <li> <a href="{{URL::to('/vendor_profiling')}}">  <span>Vendor Profiling</span> </a> </li>
        @endif

        {{--  for the Auto import link  --}}
        @if(User::checkCategoryPermission('AutoImport','View'))
            <li> <a href="{{URL::to('/auto_rate_import/autoimport')}}">  <span>Rate Import</span> </a> </li>

            <ul >
              @if(User::checkCategoryPermission('AutoRateImport','View'))
                <li> <a href="{{URL::to('/auto_rate_import/import_inbox_setting')}}">  <span>Import Inbox Settings</span> </a> </li>
              @endif
              @if(User::checkCategoryPermission('AutoRateImport','View'))
                <li> <a href="{{URL::to('/auto_rate_import/account_setting')}}">  <span>Account Settings</span> </a> </li>
              @endif
              @if(User::checkCategoryPermission('AutoRateImport','View'))
                <li> <a href="{{URL::to('/auto_rate_import/ratetable_setting')}}">  <span>Rate Table Settings </span> </a> </li>
              @endif
            </ul>
          </li>
        @endif
        {{--  for the Auto import link  --}}

      </ul>
    </li>
    @endif
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
    @if( User::checkCategoryPermission('CrmDashboard','View') || User::checkCategoryPermission('OpportunityBoard','View') ||
    User::checkCategoryPermission('Task','View'))
    <li class="{{check_uri('Crm')}}"><a href="#"><i class="glyphicon glyphicon-th"></i><span>&nbsp;&nbsp;CRM</span></a>
        <ul>
         @if(User::checkCategoryPermission('CrmDashboard','View'))
                <li><a href="{{URL::to('/crmdashboard')}}"><span>Dashboard</span></a></li>
            @endif 
            @if(User::checkCategoryPermission('OpportunityBoard','View'))
                <li><a href="{{URL::to('/opportunityboards')}}"><span>Opportunities</span></a></li>
            @endif
            @if(User::checkCategoryPermission('Task','View'))
                <li><a href="{{URL::to('/task')}}"><span>Tasks</span></a></li>
            @endif
        </ul>
    </li>
    @endif
    @endif
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type'] == Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if(User::checkCategoryPermission('Invoice','View')  || User::checkCategoryPermission('BillingSubscription','View') ||
    User::checkCategoryPermission('Payments','View') || User::checkCategoryPermission('AccountStatement','All') ||
    User::checkCategoryPermission('Products','View') || User::checkCategoryPermission('InvoiceTemplates','View') ||
    User::checkCategoryPermission('TaxRates','View') || User::checkCategoryPermission('CDR','Upload') || User::checkCategoryPermission('CDR','View')  ||
    User::checkCategoryPermission('Disputes','View') || User::checkCategoryPermission('Service','View') || User::checkCategoryPermission('BillingDashboard','All') ||
    User::checkCategoryPermission('BillingClass','View') || User::checkCategoryPermission('DiscountPlan','View'))
    <li class="{{check_uri('Billing')}}"> <a href="#"> <i class="fa fa-credit-card" ></i> <span>Billing</span> </a>
      <ul>
        @if(User::checkCategoryPermission('BillingDashboard','All'))
          <li> <a href="{{Url::to('/billingdashboard')}}"><span>Analysis</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('Estimate','View'))
        <li> <a href="{{URL::to('/estimates')}}">  <span>Estimates</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('Invoice','View'))
        <li> <a href="{{URL::to('/invoice')}}">  <span>Invoices</span> </a> </li>
        @endif
        <li> <a href="{{URL::to('/creditnotes')}}">  <span>Credit Notes</span> </a> </li>
      @if(User::checkCategoryPermission('Payments','View'))
        <li> <a href="{{URL::to('/payments')}}">  <span>Payments</span> </a> </li>
      @endif
        @if(User::checkCategoryPermission('Disputes','View'))
        <li> <a href="{{URL::to('/disputes')}}">  <span>Disputes</span> </a> </li>
        @endif
          @if(User::checkCategoryPermission('AccountStatement','All'))
            <li> <a href="{{URL::to('/account_statement')}}">  <span>Account Statement</span> </a> </li>
          @endif
          @if(User::checkCategoryPermission('Service','View'))
            <li> <a href="{{URL::to('/services')}}">  <span>Services</span> </a> </li>
          @endif
          @if(User::checkCategoryPermission('BillingSubscription','View'))
            <li> <a href="{{URL::to('/billing_subscription')}}">  <span>Subscription</span> </a> </li>
          @endif
          @if(User::checkCategoryPermission('DiscountPlan','View'))
            <li><a href="{{URL::to('/discount_plan')}}"><span>Discount Plan</span></a></li>
          @endif
          @if(User::checkCategoryPermission('Products','View'))
            <li> <a href="{{URL::to('products')}}">  <span>Items</span> </a> </li>
          @endif
          @if(User::checkCategoryPermission('InvoiceTemplates','View'))
            <li> <a href="{{URL::to('/invoice_template')}}">  <span>Invoice Template</span> </a> </li>
          @endif
          @if(User::checkCategoryPermission('TaxRates','View'))
            <li> <a href="{{URL::to('/taxrate')}}">  <span>Tax Rate</span> </a> </li>
          @endif
          @if( User::checkCategoryPermission('BillingClass','View'))
            <li> <a href="{{URL::to('/billing_class')}}">  <span>Billing Class</span> </a> </li>
          @endif
        @if(User::checkCategoryPermission('CDR','Upload'))
        <li> <a href="{{URL::to('/cdr_upload')}}">  <span>CDR Upload</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('CDR','View'))
        <li> <a href="{{URL::to('/cdr_show')}}">  <span>CDR</span> </a> </li>
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
    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type'] == Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
    @if( User::checkCategoryPermission('Analysis','All') || User::checkCategoryPermission('Analysis','Customer')  || User::checkCategoryPermission('Analysis','Vendor')  || User::checkCategoryPermission('Analysis','AccountManager') )
      <?php
          $analysis_url = Url::to('/analysis');
          if(User::checkCategoryPermission('Analysis','All') || User::checkCategoryPermission('Analysis','Customer'))
            $analysis_url = Url::to('/analysis');
          else if(User::checkCategoryPermission('Analysis','Vendor'))
            $analysis_url = Url::to('/vendor_analysis');
          else if(User::checkCategoryPermission('Analysis','AccountManager'))
            $analysis_url = Url::to('/analysis_manager');
      ?>
      <li> <a href="{{$analysis_url}}"> <i class="fa fa-bar-chart"></i> <span>Analysis</span> </a> </li>
    @endif
    @endif

    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type'] == Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
        @if( User::checkCategoryPermission('Report','Add'))
          <li class="two-links"> <a href="{{Url::to('/report')}}" class="first"> <i class="fa fa-line-chart"></i><span>Reports</span></a> <a href="{{URL::to('report/create')}}" class="last"><i class="fa fa-plus-circle" style="color: #fff;"></i></a> </li>
        @elseif( User::checkCategoryPermission('Report','All'))
          <li> <a href="{{Url::to('/report')}}"> <i class="fa fa-line-chart"></i><span>Reports</span></a></li>
        @endif
    @endif
    @if(User::checkCategoryPermission('Users','All') || User::checkCategoryPermission('Trunk','View') ||
    User::checkCategoryPermission('Currency','View') || User::checkCategoryPermission('ExchangeRate','View') ||
    User::checkCategoryPermission('CodeDecks','View')  || User::checkCategoryPermission('DialStrings','View'))
    <li class="{{check_uri('Settings')}}"> <a href="#"> <i class="fa fa-cogs"></i> <span>Settings</span> </a>
      <ul>      
        @if( User::checkCategoryPermission('Trunk','View') )
        <li> <a href="{{Url::to('/trunks')}}">  <span>Trunks</span> </a> </li>
        @endif
        @if( User::checkCategoryPermission('CodeDecks','View') )
        <li> <a href="{{Url::to('/codedecks')}}">  <span>Code Decks</span> </a> </li>
        @endif
          @if(User::checkCategoryPermission('DialStrings','View'))
            <li> <a href="{{URL::to('/dialstrings')}}">  <span>Dial String</span> </a> </li>
        @endif       
        @if(User::checkCategoryPermission('Currency','View'))
        <li> <a href="{{Url::to('/currency')}}">  <span>Currency</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('ExchangeRate','View'))
        <li> <a href="{{Url::to('/currency_conversion')}}">  <span>Exchange Rate</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('DestinationGroup','View'))
          <li><a href="{{URL::to('/destination_group_set')}}"><span>Destination Group</span></a></li>
        @endif
        @if(User::checkCategoryPermission('Timezones','View'))
          <li><a href="{{URL::to('/timezones')}}"><span>Timezones</span></a></li>
        @endif
      </ul>
    </li>
    @endif
     @if( User::checkCategoryPermission('Integration','View'))
       <li> <a href="{{URL::to('/integration')}}"><i class="fa fa-codepen"></i>   <span>Integration</span> </a> </li>
    @endif
    @if(User::checkCategoryPermission('AccountChecklist','View') ||
    User::checkCategoryPermission('CronJob','View') || User::checkCategoryPermission('Retention','View') ||
    User::checkCategoryPermission('UploadFileTemplate','View')||User::checkCategoryPermission('Notification','View')||
    User::checkCategoryPermission('ServerInfo','View') ||
    User::checkCategoryPermission('EmailTemplate','View')
    )
    <li class="{{check_uri('Admin')}}"> <a href="#"> <i class="fa fa-lock"></i> <span>&nbsp;&nbsp;&nbsp;Admin</span> </a>
      <ul>       
        @if(User::checkCategoryPermission('Notification','View'))
            <li> <a href="{{Url::to('/notification')}}">  <span>Notifications</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('themes','View'))
        <li> <a href="{{Url::to('/themes')}}">  <span>Themes</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('AccountChecklist','View'))
        <li> <a href="{{Url::to('accountapproval')}}">  <span>Account Checklist</span> </a> </li>
        @endif
         @if(User::checkCategoryPermission('Retention','View'))
         <li> <a href="{{URL::to('/retention')}}">  <span>Retention</span> </a> </li>
        @endif
        @if(User::checkCategoryPermission('UploadFileTemplate','view'))
        <li> <a href="{{URL::to('/uploadtemplate')}}">  <span>Upload Template</span> </a> </li>
        @endif
         @if( User::checkCategoryPermission('EmailTemplate','View'))
        <li> <a href="{{URL::to('/email_template')}}">  <span>Email Templates</span> </a> </li>
    	@endif
          @if( User::checkCategoryPermission('NoticeBoardPost','View'))
            <li> <a href="{{URL::to('/noticeboard')}}">  <span>Notice Board</span> </a> </li>
          @endif
         @if( User::checkCategoryPermission('ServerInfo','View'))
        <li> <a href="{{URL::to('/serverinfo')}}">  <span>Server Monitor</span> </a> </li>
    	@endif
          @if( User::checkCategoryPermission('Translate','View'))
          <li> <a href="{{URL::to('/translate')}}">  <span>Translation</span> </a> </li>
          @endif
          @if( User::checkCategoryPermission('Dynamiclink','View'))
            <li> <a href="{{Url::to('/dynamiclink')}}"> <span>&nbsp;Dynamic Link</span> </a> </li>
          @endif
      </ul>
    </li>
    @endif
    @if( User::checkCategoryPermission('CronJob','View'))
    <li> <a href="{{Url::to('cronjob_monitor')}}"> <i class="glyphicon glyphicon-time"></i> <span>&nbsp;Cron Jobs</span> </a> </li>
    @endif
    @if( User::checkCategoryPermission('Company','View'))
    <li> <a href="{{Url::to('company')}}"> <i class="glyphicon glyphicon-home"></i> <span>&nbsp;Company</span> </a> </li>
    @endif
    @if( User::checkCategoryPermission('Pages','About'))
    <li> <a href="{{Url::to('/about')}}"> <i class="glyphicon glyphicon-info-sign"></i> <span>&nbsp;About</span> </a> </li>
    @endif
  </ul>
</div>