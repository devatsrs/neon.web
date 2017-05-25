@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

        <a href="{{URL::to('contacts')}}">Contacts</a>
    </li>
    <li>
        <a><span>{{contacts_dropbox($contact->ContactID)}}</span></a>
    </li>
    <li class="active">
        <strong>Edit Contact</strong>
    </li>
</ol>
<h3>Edit Contact</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">

    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('/contacts')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
    @if(User::checkCategoryPermission('Contacts','Delete'))
    <a href="{{URL::to('contacts/'.$contact->ContactID.'/delete')}}" class="btn delete btn-danger btn-sm btn-icon icon-left" >
        <i class="entypo-trash"></i>
        Delete
    </a>
    @endif
</p>
<br>
<div class="row">
    <div class="col-md-12">

        <form role="form" id="contact-from" method="post" action="{{URL::to('contacts/update/'.$contact->AccountID)}}" class="form-horizontal form-groups-bordered">
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Contact Information
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">*First Name</label>
                    <div class="col-sm-4">
                        <div class="input-group" style="width: 100%;">
                            <div class="input-group-addon" style="padding: 0px; width: 85px;">
                                <?php $NamePrefix_array = array( ""=>"-None-" ,"Mr"=>"Mr", "Miss"=>"Miss" , "Mrs"=>"Mrs" ); ?>
                                {{Form::select('NamePrefix', $NamePrefix_array, $contact->NamePrefix ,array("class"=>"select2 small"))}}
                            </div>
                            <input type="text" name="FirstName" class="form-control" value="{{$contact->FirstName}}"/>
                        </div>
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">*Last Name</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="LastName" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder=""  value="{{$contact->LastName}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Contact Owner</label>
                    <div class="col-sm-4">
                    <select name="Owner" class="select2" data-allow-clear="true"
                                    data-placeholder="Select Account Owner...">
                                <option></option>
                                <optgroup label="Leads">
                                    @if( count($lead_owners))
                                    @foreach($lead_owners as $lead_owner)
                                    @if(!empty($lead_owner->AccountName) && $lead_owner->Status == 1)
                                    <option value="{{$lead_owner->AccountID}}" @if($contact->Owner == $lead_owner->AccountID) {{"selected"}} @endif >
                                    {{$lead_owner->AccountName}}
                                    </option>
                                    @endif
                                    @endforeach
                                    @endif
                                </optgroup>
                                <optgroup label="Accounts">
                                    @if( count($account_owners))
                                    @foreach($account_owners as $account_owner)
                                    @if(!empty($account_owner->AccountName) && $account_owner->Status == 1)
                                    <option value="{{$account_owner->AccountID}}" @if($contact->Owner == $account_owner->AccountID) {{"selected"}} @endif >
                                    {{$account_owner->AccountName}}
                                    </option>
                                    @endif
                                    @endforeach
                                    @endif
                                </optgroup>
                            </select>
                     </div>
                    <label for="field-1" class="col-sm-2 control-label">Job Title</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder="" name="Title"  value="{{$contact->Title}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder="" name="Email"  value="{{$contact->Email}}" />
                    </div>

                    <label class="col-sm-2 control-label">Department</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder=""  name="Department" value="{{$contact->Department}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Phone</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder="" name="Phone"  value="{{$contact->Phone}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Home Phone</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder=""  name="HomePhone" value="{{$contact->HomePhone}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Other Phone</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder="" name="OtherPhone"  value="{{$contact->OtherPhone}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Fax</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder="" name="Fax"  value="{{$contact->Fax}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Mobile</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder="" name="Mobile"  value="{{$contact->Mobile}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Date of Birth</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control datepicker" data-start-date="" data-date-format="dd-mm-yyyy" data-end-date="+1w" data-start-view="2" name="DateOfBirth" value="@if($contact->DateOfBirth != '1900-01-01'){{date("d-m-Y",strtotime($contact->DateOfBirth))}}@endif" />
                    </div>
                </div>
                <div class="form-group">
                    <label class=" col-sm-2 control-label no-padding-top">Email Opt Out</label>
                    <div class="col-sm-4">
                        <div class="make-switch switch-small ">
                        <input type="checkbox" name="EmailOptOut"  @if( $contact->EmailOptOut == 1 ) checked="" @endif value="1" />
                        </div>
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Skype ID</label>
                    <div class="col-sm-4">
                        <input type="text" name="Skype" class="form-control" id="field-1" placeholder="" value="{{$contact->Skype}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class=" col-sm-2 control-label">Secondary Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="field-1" placeholder="" name="SecondaryEmail"  value="{{$contact->SecondaryEmail}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Twitter</label>
                    <div class="col-sm-4">
                        <div class="input-group minimal">
                            <span class="input-group-addon">@</span>
                            <input type="text" name="Twitter" class="form-control" id="field-1" placeholder="" value="{{$contact->Twitter}}" />
                        </div>
                    </div>
                </div>

                <div class="panel-title desc clear">
                    Description
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <textarea class="form-control" name="Description" id="events_log" rows="5" placeholder="Description">{{$contact->Description}}</textarea>
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
                        <input type="text" name="Address1" class="form-control" id="field-1" placeholder="" value="{{$contact->Address1}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">City</label>
                    <div class="col-sm-4">
                        <input type="text" name="City" class="form-control" id="field-1" placeholder="" value="{{$contact->City}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Address Line 2</label>
                    <div class="col-sm-4">
                        <input type="text" name="Address2" class="form-control" id="field-1" placeholder="" value="{{$contact->Address2}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Post/Zip Code</label>
                    <div class="col-sm-4">
                        <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="" value="{{$contact->PostCode}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Address Line 3</label>
                    <div class="col-sm-4">
                        <input type="text" name="Address3" class="form-control" id="field-1" placeholder="" value="{{$contact->Address3}}" />
                    </div>

                    <label for=" field-1" class="col-sm-2 control-label">Country</label>
                    <div class="col-sm-4">

                        {{Form::select('Country', $countries, $contact->Country ,array("class"=>"select2 small"))}}
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
            $("#contact-from").submit();
            $(this).button('Loading');
        });
    });

</script>
@include('includes.ajax_submit_script', array('formID'=>'contact-from' , 'url' => ('contacts/update/'.$contact->ContactID)))

@stop