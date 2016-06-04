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
        <li>
            <a href="{{Url::to('customer/monitor')}}">
                <i class="entypo-monitor"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="{{check_uri('Customer_billing')}}">
            <a href="#">
                <i class="fa fa-credit-card"></i>
                <span>Billing</span>
            </a>

            <ul>
                <li>
                    <a href="{{URL::to('customer/dashboard')}}">
                        <span>Analysis</span>
                    </a>
                </li>
                <li>
                    <a href="{{Url::to('customer/invoice')}}">
                        <span>Invoices</span>
                    </a>
                </li>
                <li>
                    <a href="{{URL::to('customer/payments')}}">
                        <span>Payments</span>
                    </a>
                </li>

                <li>
                    <a href="{{URL::to('customer/account_statement')}}">
                        <span>Account Statement</span>
                    </a>
                </li>
                @if (is_authorize())
                <li>
                    <a href="{{URL::to('customer/PaymentMethodProfiles')}}">
                        <span>Payment Method Profiles</span>
                    </a>
                </li>
                @endif
                <li>
                    <a href="{{URL::to('customer/cdr')}}">
                        <span>CDR</span>
                    </a>
                </li>
            </ul>
        </li>
        @if(getenv('CUSTOMER_COMMERCIAL_DISPLAY') == 1)
        <li>
            <a href="{{URL::to('customer/customers_rates')}}">
                <i class="fa fa-table"></i>
                <span>Commercial</span>
            </a>
        </li>
        @endif
        <li>
            <a href="{{(Customer::get_currentUser()->IsVendor == 1 && Customer::get_currentUser()->IsCustomer == 0 ) ? Url::to('customer/vendor_analysis') : Url::to('customer/analysis')}}">
                <i class="fa fa-bar-chart"></i>
                <span>Analysis</span>
            </a>
        </li>
        <li>
            <a href="{{URL::to('customer/profile')}}">
                <i class="glyphicon glyphicon-user"></i>
                <span>Profile</span>
            </a>
        </li>
    </ul>

</div>