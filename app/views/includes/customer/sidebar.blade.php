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
        @if(CompanyConfiguration::get('CUSTOMER_DASHBOARD_DISPLAY') == 1)
        <li>
            <a href="{{Url::to('customer/monitor')}}">
                <i class="entypo-monitor"></i>
                <span>@lang('routes.CUST_PANEL_SIDENAV_MENU_DASHBOARD')</span>
            </a>
        </li>
        @endif
        @if(CompanyConfiguration::get('CUSTOMER_NOTICEBOARD_DISPLAY') == 1)
        <li>
            <a href="{{Url::to('customer/noticeboard')}}">
                <i class="entypo-gauge"></i>
                <span>@lang('routes.CUST_PANEL_SIDENAV_MENU_NOTICE_BOARD')</span>
            </a>
        </li>
        @endif
        	   <!--tickets start -->    
    @if(Tickets::CheckTicketLicense() && CompanyConfiguration::get('CUSTOMER_TICKET_DISPLAY') == 1)
    <li class="{{check_uri('tickets')}}"> <a href="#"> <i class="fa fa-ticket"></i> <span>&nbsp;@lang("routes.CUST_PANEL_SIDENAV_MENU_TICKET_MANAGEMENT")</span> </a>
      <ul>
        <li> <a href="{{URL::to('customer/tickets')}}">  <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_TICKET_MANAGEMENT__TICKET")</span> </a> </li>
      </ul>
    </li>
    @endif
    <!--tickets end -->
        @if(CompanyConfiguration::get('CUSTOMER_BILLING_DISPLAY') == 1)
        <li class="{{check_uri('Customer_billing')}}">
            <a href="#">
                <i class="fa fa-credit-card"></i>
                <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_BILLING")</span>
            </a>

            <ul>
                @if(CompanyConfiguration::get('CUSTOMER_BANALYSIS_DISPLAY') == 1)
                <li>
                    <a href="{{URL::to('customer/dashboard')}}">
                        <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_BILLING__ANALYSIS")</span>
                    </a>
                </li>
                @endif
                @if(CompanyConfiguration::get('CUSTOMER_INVOICE_DISPLAY') == 1)
                <li>
                    <a href="{{Url::to('customer/invoice')}}">
                        <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_BILLING__INVOICES")</span>
                    </a>
                </li>
                @endif
                <li>
                    <a href="{{Url::to('customer/creditnotes')}}">
                        <span>Credit Notes</span>
                    </a>
                </li>
                @if(CompanyConfiguration::get('CUSTOMER_PAYMENT_DISPLAY') == 1)
                <li>
                    <a href="{{URL::to('customer/payments')}}">
                        <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_BILLING__PAYMENTS")</span>
                    </a>
                </li>

                @endif
                @if(CompanyConfiguration::get('CUSTOMER_STATEMENT_DISPLAY') == 1)
                <li>
                    <a href="{{URL::to('customer/account_statement')}}">
                        <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_BILLING__ACCOUNT_STATEMENT")</span>
                    </a>
                </li>
                @endif
                @if (is_PayNowInvoice(Customer::get_companyID()) && CompanyConfiguration::get('CUSTOMER_PAYMENT_PROFILE_DISPLAY') == 1)
                <li>
                    <a href="{{URL::to('customer/PaymentMethodProfiles')}}">
                        <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_BILLING__PAYMENT_METHOD_PROFILES")</span>
                    </a>
                </li>
                @endif
                @if(CompanyConfiguration::get('CUSTOMER_CDR_DISPLAY') == 1)
                <li>
                    <a href="{{URL::to('customer/cdr')}}">
                        <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_BILLING__CRD")</span>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if(CompanyConfiguration::get('CUSTOMER_MOVEMENT_REPORT_DISPLAY') == 1)
            <li>
                <a href="{{URL::to('customer/daily_report/0')}}">
                    <i class="fa fa-calendar-plus-o"></i>
                    <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_MOVEMENT_REPORT")</span>
                </a>
            </li>
        @endif
        @if(CompanyConfiguration::get('CUSTOMER_COMMERCIAL_DISPLAY') == 1 && Customer::get_currentUser()->DisplayRates == 1)
        <li>
            <a href="{{URL::to('customer/customers_rates')}}">
                <i class="fa fa-table"></i>
                <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_COMMERCIAL")</span>
            </a>
        </li>
        @endif
        @if(CompanyConfiguration::get('CUSTOMER_RATE_DISPLAY') == 1 && Customer::get_currentUser()->DisplayRates == 1)
            <li>
                <a href="{{URL::to('customer/rates')}}">
                    <i class="fa fa-table"></i>
                    <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_RATES")</span>
                </a>
            </li>
        @endif
        @if(CompanyConfiguration::get('CUSTOMER_ANALYSIS_DISPLAY') == 1)
        <li>
            <a href="{{(Customer::get_currentUser()->IsVendor == 1 && Customer::get_currentUser()->IsCustomer == 0 ) ? Url::to('customer/vendor_analysis') : Url::to('customer/analysis')}}">
                <i class="fa fa-bar-chart"></i>
                <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_ANALYSIS")</span>
            </a>
        </li>
        @endif
        @if(CompanyConfiguration::get('CUSTOMER_NOTIFICATION_DISPLAY') == 1)
        <li>
            <a href="{{URL::to('customer/notification')}}">
                <i class="fa fa-bullhorn"></i>
                <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_NOTIFICATIONS")</span>
            </a>
        </li>
        @endif
        @if(CompanyConfiguration::get('CUSTOMER_PROFILE_DISPLAY') == 1)
        <li>
            <a href="{{URL::to('customer/profile')}}">
                <i class="glyphicon glyphicon-user"></i>
                <span>@lang("routes.CUST_PANEL_SIDENAV_MENU_PROFILE")</span>
            </a>
        </li>
        @endif

        <!-- Dynamic Links -->
        <?php
        $getDynamicLinks=Dynamiclink::getDynamicLinks();
        foreach($getDynamicLinks as $linkdata){
        ?>
            <li>
                <a href="{{$linkdata['link']}}" target="_blank">
                    <i class="glyphicon glyphicon-link"></i>
                    <span>{{$linkdata['name']}}</span>
                </a>
            </li>
        <?php
        }

        ?>
        <!-- End Dynamic Links -->

    </ul>

</div>