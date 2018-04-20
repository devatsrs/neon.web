@extends('layout.main')



@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a>Auto Import</a>
        </li>
        <li class="active">
            <strong>Account Settings </strong>
        </li>
    </ol>
    <h3>AutoImport Inbox Setting</h3>

    <br><br>
    <div class="cler row">
        <div class="col-md-12">
            <form id="add-new-form" method="post">


                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                        Inbox Settings
                    </div>
                    <div class="panel-body">


                            <div class="col-md-3">
                                <input type="hidden" name="CompanyID" value="{{$autoimportSetting[0]->CompanyID}}">
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Host</label>
                                        <input type="text" name="host" class="form-control" value="{{$autoimportSetting[0]->host}}" />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Port</label>
                                        <input type="text" name="port" class="form-control" value="{{$autoimportSetting[0]->port}}" />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Encryption</label>
                                        <input type="text" name="encryption" class="form-control" value="{{$autoimportSetting[0]->encryption}}" />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Validate_cert</label>
                                        <input type="text" name="validate_cert" class="form-control" value="{{$autoimportSetting[0]->validate_cert}}" />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="{{$autoimportSetting[0]->username}}" />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Password</label>
                                        <input type="text" name="password" class="form-control" value="{{$autoimportSetting[0]->password}}" />
                                    </div>
                                </div>


                            </div>


                    </div>
                </div>
                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-heading">
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                        Error Notification
                    </div>
                    <div class="panel-body">
                        <div class="col-md-3">
                            <input type="hidden" name="CompanyID" value="{{$autoimportSetting[0]->CompanyID}}">
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">Notification Email</label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" {{$autoimportSetting["SuccessNotify"]}} name="emailNotification" value="S">Success</label>
                                    <label class="radio-inline"><input type="radio" {{$autoimportSetting["FailNotify"]}} name="emailNotification" value="F">Fail</label>
                                </div>

                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Send a copy to Account Owner </label>
                                    <div class="clear">
                                        <p class="make-switch switch-small">
                                            <input type="checkbox" {{$autoimportSetting["copyNotification"]}}  name="SendCopyToAccount" >
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-12">
                            <button type="button" id="autoImportInboxSetting-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                <i class="entypo-floppy"></i>
                                Save
                            </button>
                            <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                                <i class="entypo-cancel"></i>
                                Close
                            </button>
                        </div>

                    </div>


                </div>


            </form>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {

            $("#autoImportInboxSetting-update").click(function (){
                  //  e.preventDefault();
                var formInbox = $( "#add-new-form" ).serialize();
                console.log(formInbox);
                update_new_url = baseurl + '/auto_rate_import/storeAndUpdate';
                $.ajax({
                    url: update_new_url,
                    type: 'POST',
                    data: formInbox,
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                            data_table.fnFilter('', 0);
                        }
                    },
                });

            });
        });

    </script>
    @include('includes.errors')
    @include('includes.success')
@stop
@section('footer_ext')

@stop