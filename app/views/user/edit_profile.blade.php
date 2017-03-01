@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Edit Profile</strong>
    </li>
</ol>
<h3>Edit Profile</h3>

<div class="panel-title">
    @include('includes.errors')
    @include('includes.success')
</div>

<div class="float-right">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <!----<a href="{{URL::to('/')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>-->
</div>
<br>
<br>
<div class="row">
    <div class="col-md-12">
        <form role="form" id="form-user-add"  method="post" action="{{URL::current()}}"  class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        User Detail
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">


                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">First Name</label>

                        <div class="col-sm-6">
                            <input type="text" name='FirstName' class="form-control" id="Text1" placeholder="First Name" value="{{$user->FirstName}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Last Name</label>

                        <div class="col-sm-6">
                            <input type="text" name='LastName' class="form-control" id="Text2" placeholder="Last Name" value="{{$user->LastName}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Email</label>

                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="entypo-mail"></i></span>
                                <input name='EmailAddress' type="text" class="form-control" placeholder="Email" value="{{$user->EmailAddress}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Password</label>

                        <div class="col-sm-6">
                            <input name="password" type="password" class="form-control" id="Text4" placeholder="Password" value="{{Input::old('password')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Confirm Password</label>

                        <div class="col-sm-6">
                            <input type="password" name="password_confirmation" class="form-control" id="Text5" placeholder="Confirm Password" value="{{Input::old('password_confirmation')}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Job Notification</label>
                        <div class="col-sm-6">
                            <div class="make-switch switch-small popover-primary" title="Job Notification" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If enabled, system will notify you by email about Job status." data-original-title="Notification">
                                <input type="checkbox" name="JobNotification"  @if($user->JobNotification == 1 )checked=""@endif value="0">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Personal Information
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Picture</label>
                        <div class="col-sm-4">
                            <input id="picture" type="file" name="Picture" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            <img src="{{ UserProfile::get_user_picture_url(User::get_userID()) }}" alt="" class="img-circle" width="44" />

                        </div>
                        <label for="field-1" class="col-sm-2 control-label">UTC</label>
                        <div class="col-sm-4">
                            {{Form::select('Utc', $timezones, $user_profile->Utc ,array("class"=>"form-control select2"))}}
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
                            <input type="text" name="Address1" class="form-control" id="field-1" placeholder="" value="{{$user_profile->Address1 or ''}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">City</label>
                        <div class="col-sm-4">
                            <input type="text" name="City" class="form-control" id="field-1" placeholder="" value="{{$user_profile->City or ''}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Address Line 2</label>
                        <div class="col-sm-4">
                            <input type="text" name="Address2" class="form-control" id="field-1" placeholder="" value="{{$user_profile->Address2 or ''}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Post/Zip Code</label>
                        <div class="col-sm-4">
                            <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="" value="{{$user_profile->PostCode or ''}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Address Line 3</label>
                        <div class="col-sm-4">
                            <input type="text" name="Address3" class="form-control" id="field-1" placeholder="" value="{{$user_profile->Address3 or ''}}" />
                        </div>
                        <label for=" field-1" class="col-sm-2 control-label  ">Country</label>
                        <div class="col-sm-4">
                            {{Form::select('Country', $countries, $user_profile->Country,array("class"=>"form-control select2"))}}
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function($) {

        // Replace Checboxes
        $(".save.btn").click(function(ev) {
            $(this).button('loading');
            $('#form-user-add').submit();
        });
    });

</script>
@include('includes.ajax_submit_script', array('formID'=>'form-user-add' , 'url' => 'users/update_profile/'.$user->UserID ))
@stop