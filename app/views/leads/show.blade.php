@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

        <a href="{{URL::to('/leads')}}">leads</a>
    </li>
    <li class="active">
        <strong>View Lead</strong>
    </li>
</ol>
<h3>View Lead

    <div style="float: right; text-align: right; padding-right:0px; " class="col-sm-6">
        @if(User::checkCategoryPermission('Leads','Convert'))
        <a href="{{ URL::to('leads/'.$lead->AccountID.'/convert')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Convert to Account</a>
        @endif
        @if(User::checkCategoryPermission('Leads','Edit'))
        <a href="{{ URL::to('leads/'.$lead->AccountID.'/clone')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-users"></i>Clone</a>

        <a href="{{ URL::to('leads/'.$lead->AccountID.'/edit')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Edit</a>
        @endif
        <a href="{{URL::to('/leads')}}" class="btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Close</a>

    </div>

</h3>

@include('includes.errors')
@include('includes.success')



<div class="row">
    <div class="col-md-12 form-horizontal">
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
                        <label for="field-1" class="col-sm-2 text-right">Lead Owner</label>
                        <div class="col-sm-4">
                            @if(isset($lead_owner->FirstName)){{$lead_owner->FirstName}} {{$lead_owner->LastName}}@endif
                        </div>
                        <label class="col-sm-2 text-right">*Company</label>
                        <div class="col-sm-4">
                            {{$lead->AccountName}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">First Name</label>
                        <div class="col-sm-4">
                            <div class="input-group" style="width: 100%;">
                                {{$lead->FirstName}}
                            </div>
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">*Last Name</label>
                        <div class="col-sm-4">
                            {{$lead->LastName}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Title</label>
                        <div class="col-sm-4">
                            {{$lead->Title}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Email</label>
                        <div class="col-sm-4">
                            {{$lead->Email}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Phone</label>
                        <div class="col-sm-4">
                            <i class="entypo-phone"></i>
                            {{$lead->Phone}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Fax</label>
                        <div class="col-sm-4">
                            {{$lead->Fax}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Mobile</label>
                        <div class="col-sm-4">
                            <i class="entypo-phone"></i>
                            {{$lead->Mobile}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Website</label>
                        <div class="col-sm-4">
                            {{$lead->Website}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Lead Source</label>
                        <div class="col-sm-4">
                             {{$lead->LeadSource}}
                        </div>

                        <label class="col-sm-2 text-right">Lead Status</label>
                        <div class="col-sm-4">
                            {{$lead->LeadStatus}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">Rating</label>
                        <div class="col-sm-4">
                            {{$lead->Rating}}
                        </div>

                        <label class="col-sm-2 text-right">No. Of Employees</label>
                        <div class="col-sm-4">
                            {{$lead->Employee}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-2 text-right">Email Opt Out</label>
                        <div class="col-sm-4">
                                @if( $lead->EmailOptOut == 1 ) Yes @else No @endif
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Skype ID</label>
                        <div class="col-sm-4">
                            {{$lead->Skype}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-2 text-right">Secondary Email</label>
                        <div class="col-sm-4">
                            {{$lead->SecondaryEmail}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right ">Twitter</label>
                        <div class="col-sm-4 text-left ">
                                @ {{$lead->Twitter}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 text-right">Status</label>
                        <div class="col-sm-4">
                                 @if($lead->Status == 1 ) Active @else Inactive  @endif
                         </div>

                        <label for="field-1" class="col-sm-2 text-right">VAT Number</label>
                        <div class="col-sm-4">
                            {{$lead->VatNumber}}
                        </div>
                    </div>

                    <div class="panel-title  clear">
                        Description
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            {{$lead->Description}}
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
                            {{$lead->Address1}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">City</label>
                        <div class="col-sm-4">
                            {{$lead->City}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Address Line 2</label>
                        <div class="col-sm-4">
                            {{$lead->Address2}}
                        </div>

                        <label for="field-1" class="col-sm-2 text-right">Post/Zip Code</label>
                        <div class="col-sm-4">
                            {{$lead->PostCode}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 text-right">Address Line 3</label>
                        <div class="col-sm-4">
                            {{$lead->Address3}}
                        </div>

                        <label for=" field-1" class="col-sm-2 text-right">Country</label>
                        <div class="col-sm-4">
                            {{$lead->Country}}
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
                    @if(User::checkCategoryPermission('Leads','Add'))
                    <div class="form-group">
                        <form role="form" id="notes-from" method="post" action="{{URL::to('leads/'.$lead->AccountID.'/store_note/')}}" class="form-horizontal form-groups-bordered">
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
                                @if(User::checkCategoryPermission('Leads','Edit'))
                                    <a href="{{URL::to('leads/'.$lead->AccountID.'/store_note/')}}" class="btn-danger btn-sm deleteNote entypo-cancel" id="{{$note->NoteID}}"></a>
                                    <a href="{{URL::to('leads/'.$lead->AccountID.'/delete_note/')}}" class="btn-default btn-sm editNote entypo-pencil" id="{{$note->NoteID}}"></a>
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
                    <p style="text-align: left;">
                        @if(User::checkCategoryPermission('Contacts','Add'))
                        <a href="{{URL::action('contacts_create',array("AccountID"=>$lead->AccountID))}}" class="btn btn-primary ">
                            <i class="entypo-plus"></i>
                            Add New
                        </a>
                        @endif
                    </p>
                </div>
            </div>

    </div>

    @include('includes.submit_note_script',array("controller"=>"leads"))

    @stop