@extends('layout.resellerpanel.main')

@section('content')

<style>
    .position_fixed{
        position: fixed;
    }
</style>


<ol class="breadcrumb bc-3">
    <li>
        <a href="#"><i class="entypo-home"></i>Profile</a>
    </li>
</ol>
<h3>View Account

    <div style="float: right; text-align: right " class="col-sm-4">
        <a href="{{ URL::to('reseller/profile/edit')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Edit</a>
    </div>


</h3>


@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">

</p>

<div class="row">
    <div class="col-md-12 form-horizontal">


            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Account Details
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Account Owner</label>
                        <div class="col-sm-4">
                            @if(count($account_owner))
                                {{$account_owner->FirstName}} {{$account_owner->LastName}}
                            @endif
                        </div>

                        <label class="col-sm-2 text-right">Ownership</label>
                        <div class="col-sm-4">
                            {{$account->Ownership}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">First Name</label>
                        <div class="col-sm-4">
                            {{$account->FirstName}}
                        </div>

                        <label class="col-sm-2 text-right">Last Name</label>
                        <div class="col-sm-4">
                            {{$account->LastName}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Account Number</label>
                        <div class="col-sm-4">
                            {{$account->Number}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Website</label>
                        <div class="col-sm-4">
                            {{$account->Website}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">*Account Name</label>
                        <div class="col-sm-4">
                            {{$account->AccountName}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Phone</label>
                        <div class="col-sm-4">
                            {{$account->Phone}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">Vendor</label>
                        <div class="col-sm-4">
                            @if($account->IsVendor == 1 ) Yes @else No @endif
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Fax</label>
                        <div class="col-sm-4">
                            {{$account->Fax}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">Customer</label>
                        <div class="col-sm-4">
                            @if($account->IsCustomer == 1 ) Yes @else No @endif
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Employee</label>
                        <div class="col-sm-4">
                            {{$account->Employee}}
                        </div>
                    </div>
                    <div class="form-group">
                        <!--<label for="field-1" class="col-sm-2 text-right">Rate Email</label>
                        <div class="col-sm-4">
                            {$account->RateEmail}
                        </div>-->

                        <label for="field-1" class="col-sm-2 text-right">Billing Email</label>
                        <div class="col-sm-4">
                            {{$account->BillingEmail}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">Status</label>
                        <div class="col-sm-4">
                                 @if($account->Status == 1 ) Active @else Inactive @endif
                         </div>

                        <label for="field-1" class="col-sm-2 text-right">VAT Number</label>
                        <div class="col-sm-4">
                            {{$account->VatNumber}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">Currency</label>
                        <div class="col-sm-4">
                                {{(Currency::getCurrency($account->CurrencyId))}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Timezone</label>
                        <div class="col-sm-4">
                            {{$account->TimeZone}}
                        </div>
                    </div>
                    @if(isset($account->Description) && $account->Description!='')
                    <div class="form-group">
                        <label class="col-sm-2 text-right">Description</label>
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
                        Billing
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Tax Rate</label>
                        <div class="col-sm-4">
                            {{ TaxRate::getTaxRate(AccountBilling::getTaxRate($account->AccountID))}}
                        </div>
                        <label for="field-1" class="col-sm-2 text-right">Billing Type*</label>
                        @if(isset($AccountBilling->BillingType))
                        <div class="col-sm-4">
                            {{AccountApproval::$billing_type[$AccountBilling->BillingType]}}
                        </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Billing Timezone*</label>
                        <div class="col-sm-4">
                            {{AccountBilling::getBillingKey($AccountBilling,'BillingTimezone')}}
                        </div>
                    </div>

                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Address Information
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Address Line 1</label>
                        <div class="col-sm-4">
                            {{$account->Address1}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">City</label>
                        <div class="col-sm-4">
                            {{$account->City}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Address Line 2</label>
                        <div class="col-sm-4">
                            {{$account->Address2}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Post/Zip Code</label>
                        <div class="col-sm-4">
                            {{$account->PostCode}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Address Line 3</label>
                        <div class="col-sm-4">
                            {{$account->Address3}}
                        </div>

                        <label for=" field-1" class="col-sm-2 text-right">*Country</label>
                        <div class="col-sm-4">
                            {{$account->Country}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        Payment Information
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 text-right">Payment Method</label>
                            <div class="col-sm-4">
                                {{$account->PaymentMethod}}
                            </div>

                            <label for=" field-1" class="col-sm-2 text-right">Payment Details</label>
                            <div class="col-sm-4">
                                {{$account->PaymentDetail}}
                            </div>
                        </div>

                 </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        Contacts
                    </div>



                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">

                    <table class="table table-bordered table-hover responsive">
                        <thead>
                            <tr>
                                <th>Contact Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($contacts)>0)
                            @foreach($contacts as $contact)
                            <tr class="odd gradeX">
                                <td>{{$contact->NamePrefix}} {{$contact->FirstName}} {{$contact->LastName}}</td>
                                <td>{{$contact->Phone}}</td>
                                <td>{{$contact->Email}}</td>
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