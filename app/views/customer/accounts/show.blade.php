@extends('layout.customer.main')

@section('content')

<style>
    .position_fixed{
        position: fixed;
    }
</style>


<ol class="breadcrumb bc-3">
    <li>
        <a href="#"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_PAGE_PROFILE_TITLE')</a>
    </li>
</ol>

<h3 class="text-left">@lang('routes.CUST_PANEL_PAGE_PROFILE_HEADING_VIEW_ACCOUNT')</h3>
<div class="text-right">
    <a href="{{ URL::to('customer/profile/edit')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>@lang('routes.BUTTON_EDIT_CAPTION')</a>
</div>


@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">

</p>

<div class="row">
    <div class="col-md-12 form-horizontal">


            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                            @lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_TITLE')
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_ACCOUNT_OWNER')</label>
                        <div class="col-sm-4">
                            @if(count($account_owner))
                                {{$account_owner->FirstName}} {{$account_owner->LastName}}
                            @endif
                        </div>

                        <label class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_OWNERSHIP')</label>
                        <div class="col-sm-4">
                            {{$account->Ownership}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_FIRST_NAME')</label>
                        <div class="col-sm-4">
                            {{$account->FirstName}}
                        </div>

                        <label class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_LAST_NAME')</label>
                        <div class="col-sm-4">
                            {{$account->LastName}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_AC_NUMBER')</label>
                        <div class="col-sm-4">
                            {{$account->Number}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_WEBSITE')</label>
                        <div class="col-sm-4">
                            {{$account->Website}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_ACCOUNT_NAME')</label>
                        <div class="col-sm-4">
                            {{$account->AccountName}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_PHONE')</label>
                        <div class="col-sm-4">
                            {{$account->Phone}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_VENDOR')</label>
                        <div class="col-sm-4">
                            @if($account->IsVendor == 1 ) @lang('routes.BUTTON_YES_CAPTION') @else @lang('routes.BUTTON_NO_CAPTION') @endif
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_FAX')</label>
                        <div class="col-sm-4">
                            {{$account->Fax}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_CUSTOMER')</label>
                        <div class="col-sm-4">
                            @if($account->IsCustomer == 1 ) @lang('routes.BUTTON_YES_CAPTION') @else @lang('routes.BUTTON_NO_CAPTION') @endif
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_EMPLOYEE')</label>
                        <div class="col-sm-4">
                            {{$account->Employee}}
                        </div>
                    </div>
                    <div class="form-group">
                        <!--<label for="field-1" class="col-sm-2 text-right">Rate Email</label>
                        <div class="col-sm-4">
                            {$account->RateEmail}
                        </div>-->

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_BILLING_EMAIL')</label>
                        <div class="col-sm-4">
                            {{$account->BillingEmail}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_STATUS')</label>
                        <div class="col-sm-4">
                                 @if($account->Status == 1 ) @lang('routes.BUTTON_ACTIVE_CAPTION') @else @lang('routes.BUTTON_INACTIVE_CAPTION') @endif
                         </div>

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_VAT_NUMBER')</label>
                        <div class="col-sm-4">
                            {{$account->VatNumber}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_CURRENCY')</label>
                        <div class="col-sm-4">
                                {{(Currency::getCurrency($account->CurrencyId))}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_TIMEZONE')</label>
                        <div class="col-sm-4">
                            {{$account->TimeZone}}
                        </div>
                    </div>
                    @if(isset($account->Description) && $account->Description!='')
                    <div class="form-group">
                        <label class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_AC_DETAILS_LBL_DESCRIPTION')</label>
                        <div class="col-sm-4">
                            {{$account->Description}}
                        </div>
                    </div>
                    <!--<div class="panel-title clear">
                        Description
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            {{$account->Description}}
                        </div>
                    </div>-->
                    @endif
                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        @lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_BILLING_TITLE')
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_BILLING_LBL_TAX_RATE')</label>
                        <div class="col-sm-4">
                            {{ TaxRate::getTaxRate(AccountBilling::getTaxRate($account->AccountID))}}
                        </div>
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_BILLING_LBL_BILLING_TYPE')</label>
                        @if(isset($AccountBilling->BillingType))
                        <div class="col-sm-4">
                            {{AccountApproval::$billing_type[$AccountBilling->BillingType]}}
                        </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_BILLING_LBL_BILLING_TIMEZONE')</label>
                        <div class="col-sm-4">
                            {{AccountBilling::getBillingKey($AccountBilling,'BillingTimezone')}}
                        </div>
                    </div>

                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        @lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_ADDRESS_INFORMATION_TITLE')
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_ADDRESS_INFORMATION_LBL_ADDRESS_LINE_1')</label>
                        <div class="col-sm-4">
                            {{$account->Address1}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_ADDRESS_INFORMATION_LBL_CITY')</label>
                        <div class="col-sm-4">
                            {{$account->City}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_ADDRESS_INFORMATION_LBL_ADDRESS_LINE_2')</label>
                        <div class="col-sm-4">
                            {{$account->Address2}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_ADDRESS_INFORMATION_LBL_POST_ZIP_CODE')</label>
                        <div class="col-sm-4">
                            {{$account->PostCode}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_ADDRESS_INFORMATION_LBL_ADDRESS_LINE_3')</label>
                        <div class="col-sm-4">
                            {{$account->Address3}}
                        </div>

                        <label for=" field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_ADDRESS_INFORMATION_LBL_COUNTRY')</label>
                        <div class="col-sm-4">
                            {{$account->Country}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        @lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_PAYMENT_INFORMATION_TITLE')
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_PAYMENT_INFORMATION_LBL_PAYMENT_METHOD')</label>
                            <div class="col-sm-4">
                                {{$account->PaymentMethod}}
                            </div>

                            <label for=" field-1" class="col-sm-2 text-right">@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_PAYMENT_INFORMATION_LBL_PAYMENT_DETAILS')</label>
                            <div class="col-sm-4">
                                {{$account->PaymentDetail}}
                            </div>
                        </div>

                 </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        @lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_CONTACTS_TITLE')
                    </div>



                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">

                    <table class="table table-bordered table-hover responsive">
                        <thead>
                            <tr>
                                <th>@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_CONTACTS_TBL_CONTACT_NAME')</th>
                                <th>@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_CONTACTS_TBL_PHONE')</th>
                                <th>@lang('routes.CUST_PANEL_PAGE_PROFILE_TAB_CONTACTS_TBL_EMAIL')</th>
                                <!--<th>Actions</th>-->
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($contacts)>0)
                            @foreach($contacts as $contact)
                            <tr class="odd gradeX">
                                <td>{{$contact->NamePrefix}} {{$contact->FirstName}} {{$contact->LastName}}</td>
                                <td>{{$contact->Phone}}</td>
                                <td>{{$contact->Email}}</td>
                                <!--<td class="center">
                                    <a href="{{ URL::to('contacts/'.$contact->ContactID.'/edit')}}"
                                       class="btn btn-default btn-sm btn-icon icon-left">
                                        <i class="entypo-pencil"></i>
                                        Edit
                                    </a>

                                    <a href="{{ URL::to('contacts/'.$contact->ContactID.'/show')}}"
                                       class="btn btn-default btn-sm btn-icon icon-left">
                                        <i class="entypo-pencil"></i>
                                        View
                                    </a>

                                    @if($contact->IsVendor)
                                    <a href="#" class="btn btn-info btn-sm btn-icon icon-left">
                                        <i class="entypo-cancel"></i>
                                        Vendor
                                    </a>
                                    @endif
                                    @if($contact->IsCustomer)
                                    <a href="#" class="btn btn-warning btn-sm btn-icon icon-left">
                                        <i class="entypo-cancel"></i>
                                        Customer
                                    </a>
                                    @endif
                                </td>-->
                            </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                </div>
            </div>
    </div>
</div>

@include('includes.submit_note_script',array("controller"=>"accounts"))
<script type="text/javascript">
    jQuery(document).ready(function ($) {

    // When Lead is converted to account.
    @if(Session::get('is_converted'))

        var toastr_opts = {
            "closeButton": true,
            "debug": false,
            "positionClass": "toast-top-right",
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        toastr.success('{{Session::get('is_converted')}}', "Success", toastr_opts);

    @endif

    });

</script>
@stop