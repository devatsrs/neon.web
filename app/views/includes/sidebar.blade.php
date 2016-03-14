<?php
$LicenceApiResponse = Session::get('LicenceApiResponse','');

?>
<div class="sidebar-menu">
    <header class="logo-env">

        <!-- logo -->
        <div class="logo"><!-- Added by Abubakar -->
            <a href="{{Url::to('/process_redirect')}}">
                <img src="<?php echo URL::to('/'); ?>/assets/images/logo@2x.png" width="120" alt="" />
            </a>
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
        @if( User::can('DashboardController.home')  || User::can('DashboardController.salesdashboard')||User::can('DashboardController.billingdashboard'))
            <li class="">
                <a href="#">
                    <i class="entypo-gauge"></i>
                    <span>Dashboard</span>
                </a>
                <ul>
                    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_RM || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
                        @if(User::can('DashboardController.home'))
                        <li>
                            <a href="{{action('dashboard')}}">
                                <i class="entypo-pencil"></i>
                                <span>RM Dashboard</span>
                            </a>
                        </li>
                        @endif
                    @endif
                    @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
                        @if(User::can('DashboardController.salesdashboard'))
                            <li>
                                <a href="{{action('salesdashboard')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Sales Dashboard</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('DashboardController.billingdashboard'))
                            <li>
                                <a href="{{Url::to('/billingdashboard')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Billing Dashboard</span>
                                </a>
                            </li>
                        @endif
                    @endif
                </ul>
            </li>
        @endif
        @if(User::can('LeadsController.index'))
            <li>
                <a href="{{Url::to('/leads')}}">
                    <i class="entypo-layout"></i>
                    <span>Leads</span>
                </a>
            </li>
        @endif
        @if( User::can('ContactsController.index'))
            <li>
                <a href="{{Url::to('/contacts')}}">
                    <i class="entypo-layout"></i>
                    <span>Contacts</span>
                </a>
            </li>
        @endif
        @if( User::can('AccountsController.index'))
            <li>
                <a href="{{URL::to('/accounts')}}">
                    <i class="entypo-layout"></i>
                    <span>Accounts</span>
                </a>
            </li>
        @endif
        @if( User::can('EmailTemplateController.index'))
            <li>
                <a href="#">
                    <i class="entypo-layout"></i>
                    <span>Template Management</span>
                </a>
                <ul>
                    <li>
                        <a href="{{URL::to('/email_template')}}">
                            <i class="entypo-pencil"></i>
                            <span>Email Templates</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_RM || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
                @if( User::can('RateTablesController.index') || User::can('LCRController.*') || User::can('LCRController.index') ||
                    User::can('RateGeneratorsController.index') || User::can('VendorProfilingController.index') || User::can('VendorProfilingController.*'))
                <li>
                    <a href="#">
                        <i class="entypo-layout"></i>
                        <span>Rate Management</span>
                    </a>
                    <ul>
                        @if(User::can('RateTablesController.index'))
                            <li>
                                <a href="{{URL::to('/rate_tables')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Rate Tables</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('LCRController.*') || User::can('LCRController.index'))
                            <li>
                                <a href="{{URL::to('/lcr')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>LCR List</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('RateGeneratorsController.index'))
                            <li>
                                <a href="{{URL::to('/rategenerators')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Rate Generator</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('VendorProfilingController.index') || User::can('VendorProfilingController.*'))
                            <li>
                                <a href="{{URL::to('/vendor_profiling')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Vendor Profiling</span>
                                </a>
                            </li>
                        @endif
                    </ul>
            </li>
            @endif
        @endif

        @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type']== Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
            @if( User::can('SummaryController.*') || User::can('SummaryController.index') )
                <li >
                    <a href="#">
                        <i class="entypo-layout"></i>
                        <span>Summary Reports</span>
                    </a>
                    <ul>

                            <li>
                                <a href="{{URL::to('/summaryreport')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Summary reports  by prefix </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{URL::to('/summaryreport/summrybycountry')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Summary reports  by country </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{URL::to('/summaryreport/summrybycustomer')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Summary reports  by customer </span>
                                </a>
                            </li>

                    </ul>
                </li>
            @endif
        @endif
        @if(!empty($LicenceApiResponse['Type']) && $LicenceApiResponse['Type'] == Company::LICENCE_BILLING || $LicenceApiResponse['Type'] == Company::LICENCE_ALL)
            @if(User::can('InvoicesController.index')  || User::can('BillingSubscriptionController.index') ||
                User::can('PaymentsController.index') || User::can('AccountStatementController.*') || User::can('AccountStatementController.index') ||
                User::can('ProductsController.index') || User::can('InvoiceTemplatesController.index') ||
                User::can('TaxRatesController.index') || User::can('CDRController.index') || User::can('CDRController.show') )
                <li>
                    <a href="#">
                        <i class="entypo-layout"></i>
                        <span>Billing</span>
                    </a>
                    <ul>
                        @if(User::can('InvoicesController.index'))
                            <li>
                                <a href="{{URL::to('/invoice')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Invoices</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('BillingSubscriptionController.index'))
                            <li>
                                <a href="{{URL::to('/billing_subscription')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Subscription</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('PaymentsController.index'))
                            <li>
                                <a href="{{URL::to('/payments')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Payments</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('AccountStatementController.*') || User::can('AccountStatementController.index'))
                            <li>
                                <a href="{{URL::to('/account_statement')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Account Statement</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('ProductsController.index'))
                            <li>
                                <a href="{{URL::to('products')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Items</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('InvoiceTemplatesController.index'))
                            <li>
                                <a href="{{URL::to('/invoice_template')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Invoice Template</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('TaxRatesController.index'))
                            <li>
                                <a href="{{URL::to('/taxrate')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>Tax Rate</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('CDRController.index'))
                            <li>
                                <a href="{{URL::to('/cdr_upload')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>CDR Upload</span>
                                </a>
                            </li>
                        @endif
                        @if(User::can('CDRController.show'))
                            <li>
                                <a href="{{URL::to('/cdr_show')}}">
                                    <i class="entypo-pencil"></i>
                                    <span>CDR</span>
                                </a>
                            </li>
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
        @if(User::can('UsersController.edit_profile') || User::can('UsersController.*') ||
            User::can('TrunkController.index') || User::can('GatewayController.index') ||
            User::can('CurrenciesController.index') || User::can('CurrencyConversionController.index') ||
            User::can('CodeDecksController.basecodedeck'))
            <li>
                <a href="#">
                    <i class="entypo-layout"></i>
                    <span>Settings</span>
                </a>
                <ul>
                    @if(User::can('UsersController.edit_profile') || User::can('UsersController.*') )
                        <li>
                            <a href="{{URL::to('users/edit_profile/'. User::get_userID() )}}">
                                <i class="entypo-pencil"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                    @endif
                    @if( User::can('TrunkController.index') )
                        <li>
                            <a href="{{Url::to('/trunks')}}">
                                <i class="entypo-pencil"></i>
                                <span>Trunks</span>
                            </a>
                        </li>
                    @endif
                    @if( User::can('CodeDecksController.basecodedeck') )
                        <li>
                            <a href="{{Url::to('/codedecks')}}">
                                <i class="entypo-pencil"></i>
                                <span>Code Decks</span>
                            </a>
                        </li>
                    @endif
                    @if(User::can('GatewayController.index'))
                        <li>
                            <a href="{{Url::to('/gateway')}}">
                                <i class="entypo-pencil"></i>
                                <span>Gateway</span>
                            </a>
                        </li>
                    @endif
                    @if(User::can('CurrenciesController.index'))
                        <li>
                            <a href="{{Url::to('/currency')}}">
                                <i class="entypo-pencil"></i>
                                <span>Currency</span>
                            </a>
                        </li>
                    @endif
                    @if(User::can('CurrencyConversionController.index'))
                        <li>
                            <a href="{{Url::to('/currency_conversion')}}">
                                <i class="entypo-pencil"></i>
                                <span>Exchange Rate</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif
        @if( User::can('UsersController.index') || User::can('AccountApprovalController.index') ||
            User::can('CronJobController.index') || User::can('VendorFileUploadTemplateController.index'))
            <li>
                <a href="#">
                    <i class="entypo-layout"></i>
                    <span>Admin</span>
                </a>
                <ul>
                    @if( User::can('UsersController.index'))
                        <li>
                            <a href="{{Url::to('users')}}">
                                <i class="entypo-pencil"></i>
                                <span>Users</span>
                            </a>
                        </li>
                    @endif
                    @if(User::is_admin())
                        <li>
                            <a href="{{Url::to('roles')}}">
                                <i class="entypo-pencil"></i>
                                <span>User Roles</span>
                            </a>
                        </li>
                    @endif
                    @if(User::can('AccountApprovalController.index'))
                        <li>
                            <a href="{{Url::to('accountapproval')}}">
                                <i class="entypo-pencil"></i>
                                <span>Account Checklist</span>
                            </a>
                        </li>
                    @endif
                    @if(User::can('CronJobController.index'))
                        <li>
                            <a href="{{URL::to('/cronjobs')}}">
                                <i class="entypo-pencil"></i>
                                <span>Cron Jobs</span>
                            </a>
                        </li>
                    @endif
                    @if(User::can('VendorFileUploadTemplateController.index'))
                        <li>
                            <a href="{{URL::to('/uploadtemplate')}}">
                                <i class="entypo-pencil"></i>
                                <span>Upload File Template</span>
                            </a>
                        </li>
                    @endif
                    @if(User::can('JobsController.index'))
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
        @if( User::can('JobsController.index'))
            <li>
                <a href="{{Url::to('jobs')}}">
                    <i class="entypo-layout"></i>
                    <span>Jobs</span>
                </a>
            </li>
        @endif

        @if( User::can('CompaniesController.edit'))
            <li>
                <a href="{{Url::to('company')}}">
                    <i class="entypo-layout"></i>
                    <span>Company</span>
                </a>
            </li>
        @endif
        @if( User::can('PagesController.about'))
            <li>
                <a href="{{Url::to('/about')}}">
                    <i class="entypo-layout"></i>
                    <span>About</span>
                </a>
            </li>
        @endif
    </ul>

</div>