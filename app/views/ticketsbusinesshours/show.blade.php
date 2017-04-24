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

        <a href="{{URL::to('/contacts')}}">Contacts</a>
    </li>
    <li class="active">
        <strong>View Contact</strong>
    </li>
</ol>
<h3>View Contact

    <div style="float: right; text-align: right " class="col-sm-3">
        @if(User::checkCategoryPermission('Contacts','Edit'))
        <a href="{{ URL::to('contacts/'.$contact->ContactID.'/edit')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Edit</a>
        @endif
        <a href="{{URL::to('/contacts')}}" class="btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Close</a>
    </div>


</h3>

@include('includes.errors')
@include('includes.success')

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
            <label for="field-1" class="col-sm-2 text-right">First Name</label>
            <div class="col-sm-4">
                     {{$contact->NamePrefix}} {{$contact->FirstName}}
             </div>
            <label for="field-1" class="col-sm-2 text-right">Last Name</label>
            <div class="col-sm-4">
                {{$contact->LastName}}
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 text-right">Contact Owner</label>
            <div class="col-sm-4">
                @if(isset($contact_owner->AccountType))
                @if( $contact_owner->AccountType == 0)
                {{$contact_owner->FirstName}} {{$contact_owner->LastName}}
                @else
                {{$contact_owner->AccountName}}
                @endif
                @endif
            </div>

            <label for="field-1" class="col-sm-2 text-right">Job Title</label>
            <div class="col-sm-4">
                {{$contact->Title}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 text-right">Email</label>
            <div class="col-sm-4">
                {{$contact->Email}}
            </div>

            <label class="col-sm-2 text-right">Department</label>
            <div class="col-sm-4">
                {{$contact->Department}}
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 text-right">Phone</label>
            <div class="col-sm-4">
                {{$contact->Phone}}
            </div>

            <label for="field-1" class="col-sm-2 text-right">Home Phone</label>
            <div class="col-sm-4">
                {{$contact->HomePhone}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 text-right">Other Phone</label>
            <div class="col-sm-4">
                {{$contact->OtherPhone}}
            </div>

            <label for="field-1" class="col-sm-2 text-right">Fax</label>
            <div class="col-sm-4">
                {{$contact->Fax}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 text-right">Mobile</label>
            <div class="col-sm-4">
                {{$contact->Mobile}}
            </div>
            <label for="field-1" class="col-sm-2 text-right">Date of Birth</label>
            <div class="col-sm-4">
                {{date("d-m-Y",strtotime($contact->DateOfBirth))}}
            </div>
        </div>
        <div class="form-group">
            <label class=" col-sm-2 text-right no-padding-top">Email Opt Out</label>
            <div class="col-sm-4">
                    @if( $contact->EmailOptOut == 1 ) Yes @else No @endif
            </div>

            <label for="field-1" class="col-sm-2 text-right">Skype ID</label>
            <div class="col-sm-4">
                {{$contact->Skype}}
            </div>
        </div>
        <div class="form-group">
            <label class=" col-sm-2 text-right">Secondary Email</label>
            <div class="col-sm-4">
                {{$contact->SecondaryEmail}}
            </div>

            <label for="field-1" class="col-sm-2 text-right">Twitter</label>
            <div class="col-sm-4">
                     @ {{$contact->Twitter}}
             </div>
        </div>

        <div class="panel-title  clear">
            Description
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                {{$contact->Description}}
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
                {{$contact->Address1}}
            </div>

            <label for="field-1" class="col-sm-2 text-right">City</label>
            <div class="col-sm-4">
                {{$contact->City}}
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 text-right">Address Line 2</label>
            <div class="col-sm-4">
                {{$contact->Address2}}
            </div>

            <label for="field-1" class="col-sm-2 text-right">Post/Zip Code</label>
            <div class="col-sm-4">
                {{$contact->PostCode}}
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 text-right">Address Line 3</label>
            <div class="col-sm-4">
                {{$contact->Address3}}
            </div>

            <label for=" field-1" class="col-sm-2 text-right">Country</label>
            <div class="col-sm-4">
                {{$contact->Country}}
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
        @if(User::checkCategoryPermission('Contacts','Add'))
        <div class="form-group">
            <form role="form" id="notes-from" method="post" action="{{URL::to('contacts/'.$contact->ContactID.'/store_note/')}}" class="form-horizontal form-groups-bordered">
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
                    @if(User::checkCategoryPermission('Contacts','Delete'))
                    <a href="{{URL::to('contacts/'.$contact->ContactID.'/store_note/')}}" class="btn-danger btn-sm deleteNote entypo-cancel" id="{{$note->NoteID}}"></a>
                    @endif
                    @if(User::checkCategoryPermission('Contacts','Edit'))
                    <a href="{{URL::to('contacts/'.$contact->ContactID.'/delete_note/')}}" class="btn-default btn-sm editNote entypo-pencil" id="{{$note->NoteID}}"></a>
                    @endif

                </td>
                <td>
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
</div>
</div>

@include('includes.submit_note_script',array("controller"=>"contacts"))

@stop