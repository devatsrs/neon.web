<div class="sidebar-menu">
    <header class="logo-env">

        <!-- logo -->
        <div class="logo">
            <a href="#">
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
        <li>
            <a href="{{Url::to('customer/monitor')}}">
                <i class="entypo-monitor"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="entypo-layout"></i>
                <span>Billing</span>
            </a>

            <ul>
                <li>
                    <a href="{{Url::to('customer/invoice')}}">
                        <i class="entypo-pencil"></i>
                        <span>Invoices</span>
                    </a>
                </li>
                <li>
                    <a href="{{URL::to('customer/payments')}}">
                        <i class="entypo-pencil"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li>
                    <a href="{{URL::to('customer/dashboard')}}">
                        <i class="fa fa-bar-chart"></i>
                        <span>Billing Analysis</span>
                    </a>
                </li>
                <li>
                    <a href="{{URL::to('customer/account_statement')}}">
                        <i class="entypo-pencil"></i>
                        <span>Account Statement</span>
                    </a>
                </li>
                @if (is_authorize())
                <li>
                    <a href="{{URL::to('customer/PaymentMethodProfiles')}}">
                        <i class="entypo-pencil"></i>
                        <span>Payment Method Profiles</span>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        <li>
            <a href="{{(Customer::get_currentUser()->IsVendor == 1 && Customer::get_currentUser()->IsCustomer == 0 ) ? Url::to('customer/vendor_analysis') : Url::to('customer/analysis')}}">
                <i class="fa fa-bar-chart"></i>
                <span>Analysis</span>
            </a>
        </li>
        <li>
            <a href="{{URL::to('customer/profile')}}">
                <i class="entypo-layout"></i>
                <span>Profile</span>
            </a>
        </li>
    </ul>

</div>