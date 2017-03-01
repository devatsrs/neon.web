@extends('layout.main')

@section('content')

<style>
    .position_fixed{
        position: fixed;
    }
</style>


<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        <a><span>{{customer_dropbox($account->AccountID)}}</span></a>
    </li>
    <li class="active">
        <strong>View Account</strong>
    </li>
</ol>
<h3>View Account

    <div style="float: right; text-align: right " class="col-sm-4">
        @if(User::checkCategoryPermission('Account','Edit'))
        <a href="{{ URL::to('accounts/'.$account->AccountID.'/edit')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Edit</a>
        @endif
        <a href="{{URL::to('/accounts')}}" class="btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Close</a>

    </div>


</h3>


@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">

</p>
<?php $Account = $account;?>
@include('accounts.errormessage')
@if($outstanding>0)
<div  class=" toast-container-fix toast-top-full-width">
        <div class="toast toast-error" style="">
        <div class="toast-title">Outstanding</div>
        <div class="toast-message">
        Outstanding Amount - {{$currency}}{{$outstanding}}
        </div>
    </div>
</div>
@endif

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
                        <label for="field-1" class="col-sm-2 text-right">Email</label>
                        <div class="col-sm-4">
                            {{$account->Email}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Secondry Email</label>
                        <div class="col-sm-4">
                            {{$account->SecondaryEmail}}
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
                    <div class="panel-title clear">
                        Description
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            {{$account->Description}}
                        </div>
                    </div>
                </div>
            </div>
            @if(User::checkCategoryPermission('Account','Add'))
                @include('accountactivity.index')
            @endif
            @if(User::checkCategoryPermission('Account','Email'))
                @include('accountemaillog.index')
            @endif

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
                    <div class="form-group clear">
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
                    <div class="form-group clear">
                        <label for="field-1" class="col-sm-2 text-right">Address Line 2</label>
                        <div class="col-sm-4">
                            {{$account->Address2}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Post/Zip Code</label>
                        <div class="col-sm-4">
                            {{$account->PostCode}}
                        </div>
                    </div>
                    <div class="form-group clear">
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
                        Notes
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    @if(User::checkCategoryPermission('Account','Add'))
                    <div class="form-group">
                            <form role="form" id="notes-from" method="post" action="{{URL::to('accounts/'.$account->AccountID.'/store_note/')}}" class="form-horizontal form-groups-bordered">
                                <div class="  col-sm-12">
                                    <textarea class="form-control" name="Note" id="txtNote" rows="5" placeholder="Add Note..."></textarea>
                                    <div class="col-padding-1 no-padding-left">
                                        <button class="save btn btn-primary btn-sm btn-icon icon-left" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
                                        <button class="btn btn-danger btn-sm btn-icon icon-left" type="reset" ><i class="entypo-cancel"></i>Reset</button>
                                        <input type="hidden" name="NoteID" id="NoteID" value="">
                                    </div>
                                </div>
                            </form>
                     </div>
                    @endif
                    <hr/>
                    <table class="table table-bordered table-hover responsive">
                        <thead>
                        <tr>
                            <th>Action</th>
                            <th>Note</th>
                        </tr>
                        </thead>
                        <tbody class="notes_body">

                        @if(count($notes)>0)
                        @foreach($notes as $note)
                        <tr>
                            <td>
                                @if(User::checkCategoryPermission('Account','Edit'))
                                    <a href="{{URL::to('accounts/'.$account->AccountID.'/store_note/')}}" class="btn-danger btn-sm deleteNote entypo-cancel" id="{{$note->NoteID}}"></a>
                                    <a href="{{URL::to('accounts/'.$account->AccountID.'/delete_note/')}}" class="btn-default btn-sm editNote entypo-pencil" id="{{$note->NoteID}}"></a>
                                @endif
                            </td>
                            <td >
                                <div id="Note{{$note->NoteID}}">
                                    <p>{{$note->Note}}</p>
                                </div>
                                <h5><a href="#">{{$note->created_by}}</a> &nbsp; {{date( "d/m/Y H:i A", strtotime($note->created_at))}}</h5>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        </tbody>
                    </table>
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
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($contacts)>0)
                            @foreach($contacts as $contact)
                            <tr class="odd gradeX">
                                <td>{{$contact->NamePrefix}} {{$contact->FirstName}} {{$contact->LastName}}</td>
                                <td>{{$contact->Phone}}</td>
                                <td>{{$contact->Email}}</td>
                                <td class="center">
                                    @if(User::checkCategoryPermission('Contacts','Edit'))
                                    <a href="{{ URL::to('contacts/'.$contact->ContactID.'/edit')}}"
                                       class="btn btn-default btn-sm btn-icon icon-left">
                                        <i class="entypo-pencil"></i>
                                        Edit
                                    </a>
                                    @endif
                                    @if(User::checkCategoryPermission('Contacts','View'))
                                    <a href="{{ URL::to('contacts/'.$contact->ContactID.'/show')}}"
                                       class="btn btn-default btn-sm btn-icon icon-left">
                                        <i class="entypo-pencil"></i>
                                        View
                                    </a>
                                    @endif

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
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                    @if(User::checkCategoryPermission('Contacts','Add'))
                    <p style="text-align: left;">
                         <a href="{{URL::action('contacts_create',array("AccountID"=>$account->AccountID))}}" class="btn btn-primary ">
                            <i class="entypo-plus"></i>
                            Add New
                        </a>
                    </p>
                    @endif
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