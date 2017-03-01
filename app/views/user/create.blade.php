@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{action('users')}}">Users</a>
    </li>
    <li class="active">
        <strong>New User</strong>
    </li>
</ol>
<h3>New User</h3>


<div class="panel-title">
    @include('includes.errors')
    @include('includes.success')

</div>

<p style="text-align: right;">
    <button type='button' class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{action('users')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>
<br>
<div class="row">
    <div class="col-md-12">

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

                <form role="form" id="form-user-add" method="post" action="{{URL::to('users/create')}}"
                      class="form-horizontal form-groups-bordered">

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">First Name</label>

                        <div class="col-sm-6">
                            <input type="text" name='FirstName' class="form-control" id="Text1" placeholder="First Name"
                                   value="{{Input::old('FirstName')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Last Name</label>

                        <div class="col-sm-6">
                            <input type="text" name='LastName' class="form-control" id="Text2" placeholder="Last Name"
                                   value="{{Input::old('LastName')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Email</label>

                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="entypo-mail"></i></span>
                                <input name='EmailAddress' type="text" class="form-control" placeholder="Email"
                                       value="{{Input::old('EmailAddress')}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Password</label>

                        <div class="col-sm-6">
                            <input name="password" type="password" class="form-control" id="Text4"
                                   placeholder="Password" value="{{Input::old('password')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Confirm Password</label>

                        <div class="col-sm-6">
                            <input type="password" name="password_confirmation" class="form-control" id="Text5"
                                   placeholder="Confirm Password" value="{{Input::old('password_confirmation')}}">
                        </div>
                    </div>

                    <div class="form-group tobehide">
                        <label for="field-1" class="col-sm-3 control-label">Roles</label>

                        <div class="col-sm-6">
                            {{ Form::select('Roles[]', $roles, explode(',',Input::get('Roles')) , array(  "multiple"=>"multiple", "class"=>"select2")) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Job Notification</label>
                        <div class="col-sm-6">
                            <div class="make-switch switch-small">
                                <input type="checkbox" name="JobNotification" checked="" value="1">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Active</label>

                        <div class="col-sm-6">
                            <div class="make-switch switch-small">
                                <input type="checkbox" name="Status"  @if(Input::old('Status') =='' )checked="" @else  @if( ( Input::old('Status') !='' ) && Input::old('Status') == 1 ) checked=""  @endif @endif value="1">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Admin User</label>

                        <div class="col-sm-6">
                            <div class="make-switch switch-small">
                                <input id="admin" type="checkbox" name="AdminUser"  @if(Input::old('AdminUser') =='' ) @else  @if( ( Input::old('AdminUser') !='' ) && Input::old('AdminUser') == 1 ) checked=""  @endif @endif value="1">
                            </div>
                        </div>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#admin').change(function(){
            if($(this).prop('checked')){
                $('.tobehide').addClass('hidden');
            }else{
                $('.tobehide').removeClass('hidden');
            }
        });
 //            $("#Roles").select2({
//                minimumResultsForSearch: -1,
//                tags: ["Admin", "CRM", "Account Manager"],
//                placeholder: "Insert Role",
//                allowClear: true,
//                minimumInputLength: 1,
//                maximumSelectionSize: -1,
//                minimumInputLength: -1,
//                multiple : true,
//                tokenSeparators: [",", " "]});
//        });

        // Replace Checboxes
        $(".save.btn").click(function(ev) {
            $(this).button('loading');
            $('#form-user-add').submit();
        });

    });
</script>
@include('includes.ajax_submit_script', array('formID'=>'form-user-add' , 'url' => 'users/store','update_url'=>'users/update/{id}' ))
@stop