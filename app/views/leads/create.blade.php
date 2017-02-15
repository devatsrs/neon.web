@extends('layout.main')
@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

        <a href="{{URL::to('leads')}}">Leads</a>
    </li>
    <li class="active">
        <strong>New Lead</strong>
    </li>
</ol>
<h3> New Lead</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('/leads')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>

<div class="row">
<div class="col-md-12">
<form role="form" id="lead-from" method="post" action="{{URL::to('leads/store')}}" class="form-horizontal form-groups-bordered">
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Lead Information
        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body">

        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">*Lead Owner</label>
            <div class="col-sm-4">
            {{Form::select('Owner',$account_owners,User::get_userID(),array("class"=>"select2"))}}
            </div>
            <label class="col-sm-2 control-label">*Company</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="AccountName" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder="" value="{{Input::old('AccountName')}}" />
            </div>
        </div>

        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">*First Name</label>
            <div class="col-sm-4">
                <div class="input-group" style="width: 100%;">
                    <div class="input-group-addon" style="padding: 0px; width: 85px;">
                        <?php $NamePrefix_array = array( ""=>"-None-" ,"Mr"=>"Mr", "Miss"=>"Miss" , "Mrs"=>"Mrs" ); ?>
                        {{Form::select('Title', $NamePrefix_array, Input::old('Title') ,array("class"=>"select2 small"))}}
                    </div>
                    <input type="text" name="FirstName" class="form-control" value="@if(Input::old('FirstName')!=''){{Input::old('FirstName')}}@elseif(Input::get('name')!=''){{Input::get('name')}}@endif"/>
                </div>
            </div>

            <label for="field-1" class="col-sm-2 control-label">*Last Name</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="LastName" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder="" value="{{Input::old('LastName')}}" />
            </div>
        </div>
        <div class="form-group">
           <!-- <label for="field-1" class="col-sm-2 control-label">Title</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="Title" id="field-1" placeholder=""  value="{{Input::old('Title')}}"/>
            </div>-->

            <label for="field-1" class="col-sm-2 control-label">Email</label>
            <div class="col-sm-4">
                <input type="text" name="Email" class="form-control" id="field-1" placeholder="" value="@if(Input::old('Email')!=''){{Input::old('Email')}}@elseif(Input::get('email')!=''){{Input::get('email')}}@endif"/>
            </div>

        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Phone</label>
            <div class="col-sm-4">
                <input type="text"  name="Phone" class="form-control" id="field-1" placeholder="" value="{{Input::old('Phone')}}"/>
            </div>

            <label for="field-1" class="col-sm-2 control-label">Fax</label>
            <div class="col-sm-4">
                <input type="text" name="Fax" class="form-control" id="field-1" placeholder="" value="{{Input::old('Fax')}}"/>
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Mobile</label>
            <div class="col-sm-4">
                <input type="text"  name="Mobile" class="form-control" id="field-1" placeholder="" value="{{Input::old('Mobile')}}"/>
            </div>

            <label for="field-1" class="col-sm-2 control-label">Website</label>
            <div class="col-sm-4">
                <input type="text" name="Website" class="form-control" id="field-1" placeholder="" value="{{Input::old('Website')}}" />
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Lead Source</label>
            <div class="col-sm-4">
                <?php $leadsource_array = array( "Advertisement"=>"Advertisement", "Cold Call"=>"Cold Call" , "Employee Referral"=>"Employee Referral","Online Store"=>"Online Store","Employee Referral"=>"Employee Referral","Partner"=>"Partner","Public Relations"=>"Public Relations","Sales Mail Alias"=>"Sales Mail Alias","Seminar Partner"=>"Seminar Partner","Trade Show"=>"Trade Show","Web Download"=>"Web Download","Web Research"=>"Web Research","Chat"=>"Chat" ); ?>
                {{Form::select('LeadSource', $leadsource_array, Input::old('LeadSource') ,array("class"=>"select2 small"))}}
            </div>

            <label class="col-sm-2 control-label">Lead Status</label>
            <div class="col-sm-4">
                <?php $leadstatus_array = array( ""=>"-none-", "Attempted to Contact"=>"Attempted to Contact" , "Contact in Future"=>"Contact in Future","Contacted"=>"Contacted", "Junk Lead"=>"Junk Lead","Not Contacted"=>"Not Contacted", "Pre Qualified"=>"Pre Qualified" ); ?>
                {{Form::select('LeadStatus', $leadstatus_array, Input::old('LeadStatus') ,array("class"=>"select2 small"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Rating</label>
            <div class="col-sm-4">
                <?php $rating_array = array( ""=>"-none-", "Acquired"=>"Acquired" , "Active"=>"Active","Market Failed"=>"Market Failed", "Project Cancelled"=>"Project Cancelled","Shutdown"=>"Shutdown"); ?>
                {{Form::select('Rating', $rating_array, Input::old('Rating') ,array("class"=>"select2 small"))}}
            </div>

            <label class="col-sm-2 control-label">No. Of Employees</label>
            <div class="col-sm-4">
                <input type="text" name="Employee" class="form-control" id="field-1" placeholder="" value="{{Input::old('Employee')}}" />
            </div>
        </div>
        <div class="form-group">
            <label class=" col-sm-2 control-label no-padding-top">Email Opt Out</label>
            <div class="col-sm-4">
                <div class="make-switch switch-small">
                    <input type="checkbox" name="EmailOptOut"  @if( Input::old('EmailOptOut') == 1 ) checked="" @endif value="1" />
                </div>
            </div>

            <label for="field-1" class="col-sm-2 control-label">Skype ID</label>
            <div class="col-sm-4">
                <input type="text" name="Skype" class="form-control" id="field-1" placeholder="" value="{{Input::old('Skype')}}" />
            </div>
        </div>
        <div class="form-group">
            <label class=" col-sm-2 control-label">Secondary Email</label>
            <div class="col-sm-4">
                <input type="text" name="SecondaryEmail" class="form-control" id="field-1" placeholder="" value="{{Input::old('SecondaryEmail')}}" />
            </div>

            <label for="field-1" class="col-sm-2 control-label">Twitter</label>
            <div class="col-sm-4">
                <div class="input-group minimal">
                    <span class="input-group-addon">@</span>
                    <input type="text" name="Twitter" class="form-control" id="field-1" placeholder="" value="{{Input::old('Twitter')}}" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Status</label>
            <div class="col-sm-4">
                <div class="make-switch switch-small">
                    <input type="checkbox" name="Status"  checked="" value="1">
                </div>
            </div>

            <label for="field-1" class="col-sm-2 control-label">VAT Number</label>
            <div class="col-sm-4">
                <input type="text" class="form-control"  name="VatNumber" id="field-1" placeholder="" value="{{Input::old('VatNumber')}}" />
            </div>
        </div>

        <div class="panel-title desc clear">
            Description
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <textarea class="form-control" name="Description" id="events_log" rows="5" placeholder="Description">{{Input::old('Description')}}</textarea>
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
            <label for="field-1" class="col-sm-2 control-label">Address Line 1</label>
            <div class="col-sm-4">
                <input type="text" name="Address1" class="form-control" id="field-1" placeholder="" value="{{Input::old('Address1')}}" />
            </div>

            <label for="field-1" class="col-sm-2 control-label">City</label>
            <div class="col-sm-4">
                <input type="text" name="City" class="form-control" id="field-1" placeholder="" value="{{Input::old('City')}}" />
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Address Line 2</label>
            <div class="col-sm-4">
                <input type="text" name="Address2" class="form-control" id="field-1" placeholder="" value="{{Input::old('Address2')}}" />
            </div>

            <label for="field-1" class="col-sm-2 control-label">Post/Zip Code</label>
            <div class="col-sm-4">
                <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="" value="{{Input::old('PostCode')}}" />
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Address Line 3</label>
            <div class="col-sm-4">
                <input type="text" name="Address3" class="form-control" id="field-1" placeholder="" value="{{Input::old('Address3')}}" />
            </div>

            <label for=" field-1" class="col-sm-2 control-label">Country</label>
            <div class="col-sm-4">

                {{Form::select('Country', $countries, Input::old('Country') ,array("class"=>"select2 small"))}}
            </div>
        </div>
    </div>

</div>
</form>
</div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $(".save.btn").click(function (ev) {
            $("#lead-from").submit();
        });

    });

    function ajax_form_success(response){
        if(typeof response.redirect != 'undefined' && response.redirect != ''){
            window.location = response.redirect;
        }
    }

</script>
@include('includes.ajax_submit_script', array('formID'=>'lead-from' , 'url' => ('leads/store'),'update_url'=>'leads/update/{id}'))
@stop