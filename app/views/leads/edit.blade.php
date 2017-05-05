@extends('layout.main')
@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('leads')}}">Leads</a>
    </li>
    <li>
        <a><span>{{customer_dropbox($lead->AccountID,["AccountType"=>0])}}</span></a>
    </li>
    <li class="active">
        <strong>{{$text}}</strong>
    </li>
</ol>
<h3>{{$text}}</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
    @if(User::checkCategoryPermission('Opportunity','Add'))
    <a href="javascript:void(0)" class="btn btn-primary btn-sm btn-icon icon-left opportunity">
        <i class="fa fa-line-chart"></i>
        Add Opportunity
    </a>
    @endif

     @if(User::checkCategoryPermission('Leads','Convert'))
        <a href="{{ URL::to('leads/'.$lead->AccountID.'/convert')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Convert to Account</a>
     @endif

    <button type="button" id="leadsave"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
        <form role="form" id="lead-from" method="post" action="{{$url}}" class="form-horizontal form-groups-bordered">
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
                        {{Form::select('Owner',$account_owners,$lead->Owner,array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Lead Owner..."))}}
                        </div>
                        <label class="col-sm-2 control-label">*Company</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="AccountName" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder="" value="{{$lead->AccountName}}" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">*First Name</label>
                        <div class="col-sm-4">
                            <div class="input-group" style="width: 100%;">
                                <div class="input-group-addon" style="padding: 0px; width: 85px;">
                                    <?php $NamePrefix_array = array( ""=>"-None-" ,"Mr"=>"Mr", "Miss"=>"Miss" , "Mrs"=>"Mrs" ); ?>
                                    {{Form::select('Title', $NamePrefix_array, $lead->Title ,array("class"=>"select2 small"))}}
                                </div>
                                <input type="text" name="FirstName" class="form-control" value="{{$lead->FirstName}}"/>
                            </div>
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">*Last Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="LastName" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder="" value="{{$lead->LastName}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <!--<label for="field-1" class="col-sm-2 control-label">Title</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="Title" id="field-1" placeholder=""  value="{$lead->Title}"/>
                        </div>-->

                        <label for="field-1" class="col-sm-2 control-label">Email</label>
                        <div class="col-sm-4">
                            <input type="text" name="Email" class="form-control" id="field-1" placeholder="" value="{{$lead->Email}}"/>
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Phone</label>
                        <div class="col-sm-4">
                            <input type="text"  name="Phone" class="form-control" id="field-1" placeholder="" value="{{$lead->Phone}}"/>
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Fax</label>
                        <div class="col-sm-4">
                            <input type="text" name="Fax" class="form-control" id="field-1" placeholder="" value="{{$lead->Fax}}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Mobile</label>
                        <div class="col-sm-4">
                            <input type="text"  name="Mobile" class="form-control" id="field-1" placeholder="" value="{{$lead->Mobile}}"/>
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Website</label>
                        <div class="col-sm-4">
                            <input type="text" name="Website" class="form-control" id="field-1" placeholder="" value="{{$lead->Website}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Lead Source</label>
                        <div class="col-sm-4">
                            <?php $leadsource_array = array( "Advertisement"=>"Advertisement", "Cold Call"=>"Cold Call" , "Employee Referral"=>"Employee Referral","Online Store"=>"Online Store","Employee Referral"=>"Employee Referral","Partner"=>"Partner","Public Relations"=>"Public Relations","Sales Mail Alias"=>"Sales Mail Alias","Seminar Partner"=>"Seminar Partner","Trade Show"=>"Trade Show","Web Download"=>"Web Download","Web Research"=>"Web Research","Chat"=>"Chat" ); ?>
                            {{Form::select('LeadSource', $leadsource_array, $lead->LeadSource ,array("class"=>"select2 small"))}}
                        </div>

                        <label class="col-sm-2 control-label">Lead Status</label>
                        <div class="col-sm-4">
                            <?php $leadstatus_array = array( ""=>"-none-", "Attempted to Contact"=>"Attempted to Contact" , "Contact in Future"=>"Contact in Future","Contacted"=>"Contacted", "Junk Lead"=>"Junk Lead","Not Contacted"=>"Not Contacted", "Pre Qualified"=>"Pre Qualified" ); ?>
                            {{Form::select('LeadStatus', $leadstatus_array, $lead->LeadStatus ,array("class"=>"select2 small"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Rating</label>
                        <div class="col-sm-4">
                            <?php $rating_array = array( ""=>"-none-", "Acquired"=>"Acquired" , "Active"=>"Active","Market Failed"=>"Market Failed", "Project Cancelled"=>"Project Cancelled","Shutdown"=>"Shutdown"); ?>
                            {{Form::select('Rating', $rating_array, $lead->Rating ,array("class"=>"select2 small"))}}
                        </div>

                        <label class="col-sm-2 control-label">No. Of Employees</label>
                        <div class="col-sm-4">
                            <input type="text" name="Employee" class="form-control" id="field-1" placeholder="" value="{{$lead->Employee}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-2 control-label no-padding-top">Email Opt Out</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                <input type="checkbox" name="EmailOptOut"  @if( $lead->EmailOptOut == 1 ) checked="" @endif value="1" />
                            </div>
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Skype ID</label>
                        <div class="col-sm-4">
                            <input type="text" name="Skype" class="form-control" id="field-1" placeholder="" value="{{$lead->Skype}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-2 control-label">Secondary Email</label>
                        <div class="col-sm-4">
                            <input type="text" name="SecondaryEmail" class="form-control" id="field-1" placeholder="" value="{{$lead->SecondaryEmail}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Twitter</label>
                        <div class="col-sm-4">
                            <div class="input-group minimal">
                                <span class="input-group-addon">@</span>
                                <input type="text" name="Twitter" class="form-control" id="field-1" placeholder="" value="{{$lead->Twitter}}" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Status</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                <input type="checkbox" name="Status"  @if($lead->Status == 1 )checked=""@endif value="1">
                            </div>
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">VAT Number</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control"  name="VatNumber" id="field-1" placeholder="" value="{{$lead->VatNumber}}" />
                        </div>
                    </div>
                   <div class="form-group">
                       <label for="field-1" class="col-sm-2 control-label">Leads Tags</label>
                       <div class="col-sm-4">
                           <input type="text" class="form-control" id="tags" name="tags" value="{{$lead->tags}}" />
                       </div>
                   </div>
                    <div class="panel-title desc clear">
                        Description
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <textarea class="form-control" name="Description" id="events_log" rows="5" placeholder="Description">{{$lead->Description}}</textarea>
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
                            <input type="text" name="Address1" class="form-control" id="field-1" placeholder="" value="{{$lead->Address1}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">City</label>
                        <div class="col-sm-4">
                            <input type="text" name="City" class="form-control" id="field-1" placeholder="" value="{{$lead->City}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Address Line 2</label>
                        <div class="col-sm-4">
                            <input type="text" name="Address2" class="form-control" id="field-1" placeholder="" value="{{$lead->Address2}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Post/Zip Code</label>
                        <div class="col-sm-4">
                            <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="" value="{{$lead->PostCode}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Address Line 3</label>
                        <div class="col-sm-4">
                            <input type="text" name="Address3" class="form-control" id="field-1" placeholder="" value="{{$lead->Address3}}" />
                        </div>

                        <label for=" field-1" class="col-sm-2 control-label">Country</label>
                        <div class="col-sm-4">

                            {{Form::select('Country', $countries, $lead->Country ,array("class"=>"select2 small"))}}
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    var readonly = ['Company','Phone','Email','ContactName'];
    var accountID = '{{$lead->AccountID}}';
    jQuery(document).ready(function ($) {
        $("#leadsave").click(function (ev) {
            $("#lead-from").submit();
        });
        //$('.tags').select2();

        $("#tags").select2({
            tags:{{$tags}}
        });

        $('#createopportunity').on('click',function(){
            $('#add-edit-opportunity-form').trigger('reset');
            $('#add-edit-opportunity-form [name="AccountID"]').val(accountID).trigger("change");
            if(accountID) {
                var url = baseurl + '/opportunity/' + accountID + '/getlead';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        setunsetreadonly(response[0],true);
                        $('#add-edit-modal-opportunity').modal('show');
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }
        });

        $('#add-edit-modal-opportunity .reset').click(function(){
            var colorPicker = $(this).parents('.form-group').find('[type="text"].colorpicker');
            var color = $(this).attr('data-color');
            setcolor(colorPicker,color);
        });

        $(document).on('mouseover','#rating i',function(){
            var currentrateid = $(this).attr('rate-id');
            setrating(currentrateid);
        });
        $(document).on('click','#rating i',function(){
            var currentrateid = $(this).attr('rate-id');
            $('#rating input[name="Rating"]').val(currentrateid);
            setrating(currentrateid);
        });
        $(document).on('mouseleave','#rating',function(){
            var defultrateid = $('#rating input[name="Rating"]').val();
            setrating(defultrateid);
        });

        $('#add-edit-opportunity-form').submit(function(e){
            e.preventDefault();
            var url = baseurl + '/accounts/'+accountID+'/createOpportunity';
            var formData = new FormData($('#add-edit-opportunity-form')[0]);
            $.ajax({
                url: url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#add-edit-modal-opportunity').modal('hide');
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $("#opportunity-update").button('reset');
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $('.opportunityTags').select2({
            tags:{{$opportunityTags}}
        });

        function setunsetreadonly(data,status){
            for(var i = 0 ; i< readonly.length; i++){
                $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').val('');
                $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').prop('readonly', status);
                if(data){
                    $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').val(data[readonly[i]]);
                }
            }
        }

        function setcolor(elem,color){
            elem.colorpicker('destroy');
            elem.val(color);
            elem.colorpicker({color:color});
            elem.siblings('.input-group-addon').find('.color-preview').css('background-color', color);
        }

        function setrating(currentrateid){
            $('#rating i').css('color','black');
            $('#rating i').each(function(){
                var rateid = $(this).attr('rate-id');
                if(currentrateid<rateid){
                    return false;
                }
                $(this).css('color','#e9dc3c');
            });
        }

    });

</script>

@include('includes.ajax_submit_script', array('formID'=>'lead-from' , 'url' => ($url2)))
@include('opportunityboards.opportunitymodal',array('leadOrAccountID'=>$leadOrAccountID))
@stop

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-modal-opportunity">
        <div class="modal-dialog" style="width: 70%;">
            <div class="modal-content">
                <form id="add-edit-opportunity-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Opportunity</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Account Owner *</label>
                                    <div class="col-sm-4">
                                        {{Form::select('UserID',$account_owners,User::get_userID(),array("class"=>"select2 small",'disabled'))}}
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Opportunity Name *</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="OpportunityName" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="leads">
                                        <label for="field-5" class="control-label col-sm-2">Leads</label>
                                        <div class="col-sm-4">
                                            {{Form::select('AccountID',$leads,'',array("class"=>"select2 small",'disabled'))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Company</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="Company" class="form-control" id="field-5">
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Contact Name</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="ContactName" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Phone Number</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="Phone" class="form-control" id="field-5">
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Email Address</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="Email" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Select Board</label>
                                    <div class="col-sm-4">
                                        {{Form::select('BoardID',$boards,'',array("class"=>"select2 small"))}}
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Select Background</label>
                                    <div class="col-sm-3 input-group">
                                        <input name="BackGroundColour" type="text" class="form-control colorpicker" value="#303641" />
                                        <div class="input-group-addon">
                                            <i class="color-preview"></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <button class="btn btn-xs btn-danger reset" data-color="#303641" type="button">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Text Color</label>
                                    <div class="col-sm-3 input-group">
                                        <input name="TextColour" type="text" class="form-control colorpicker" value="#ffffff" />
                                        <div class="input-group-addon">
                                            <i class="color-preview"></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <button class="btn btn-xs btn-danger reset" data-color="#ffffff" type="button">Reset</button>
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Tags</label>
                                    <div class="col-sm-4 input-group">
                                        <input class="form-control opportunityTags" name="Tags" type="text" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="input-1" class="control-label col-sm-2">Rate This</label>
                                    <div id="rating" class="col-sm-4">
                                        <i rate-id="1" class="entypo-star"></i>
                                        <i rate-id="2" class="entypo-star"></i>
                                        <i rate-id="3" class="entypo-star"></i>
                                        <i rate-id="4" class="entypo-star"></i>
                                        <i rate-id="5" class="entypo-star"></i>
                                        <input type="hidden" name="Rating" value="1" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="OpportunityID">
                        <button type="submit" id="opportunity-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop