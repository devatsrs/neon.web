@extends('layout.main')



@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('/auto_rate_import/autoimport')}}">Auto Import</a>
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

                <div class="float-right">
                    <button type="button" id="autoImportInboxSetting-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <a href="{{URL::to('/auto_rate_import/autoimport')}}" class="btn btn-danger btn-sm btn-icon icon-left">Close
                        <i class="entypo-cancel"></i>
                    </a>
                </div><br><br>

                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                        Inbox Settings
                    </div>
                    <div class="panel-body">



                            <div class="col-md-12">

                                <input type="hidden" name="CompanyID" value="{{$autoimportSetting[0]->CompanyID}}">
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Imap/Pop3</label>
                                        <input type="text" name="host" class="form-control" value="{{$autoimportSetting[0]->host}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Port</label>
                                        <input type="text" name="port" class="form-control" value="{{$autoimportSetting[0]->port}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="{{$autoimportSetting[0]->username}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Password" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="field-5" class="control-label">Enable SSL</label>
                                        <div class="clear">
                                            <p class="make-switch switch-small">
                                                <input type="checkbox" {{$autoimportSetting["IsSSL"]}}  name="IsSSL" >
                                            </p>
                                        </div>
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
                        Notification
                    </div>
                    <div class="panel-body">
                        <div class="col-md-12">
                            <input type="hidden" name="CompanyID" value="{{$autoimportSetting[0]->CompanyID}}">
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">On Success</label>
                                    <br>
                                    <input type="text" name="emailNotificationOnSuccess" class="form-control" value="{{$autoimportSetting[0]->emailNotificationOnSuccess}}" />
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">On Failure</label>
                                    <br>
                                    <input type="text" name="emailNotificationOnFail" class="form-control" value="{{$autoimportSetting[0]->emailNotificationOnFail}}" />
                                </div>

                            </div>
                            <div class="col-md-6">
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