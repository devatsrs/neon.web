@extends('layout.main')

@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}/">
                <i class="entypo-home"></i>Home
            </a>
        </li>
        <li>
            <a href="{{URL::to('notification')}}">Notification</a>
        </li>
        <li class="active">
            <strong>Create Notification</strong>
        </li>
    </ol>
    <p class="text-right">
        <button type="submit" href="{{URL::to('notification/store')}}" id="save_notification" class="btn save btn-primary btn-icon btn-sm icon-left hidden-print" data-loading-text="Loading...">
            Save
            <i class="entypo-floppy"></i>
        </button>
        <a href="{{URL::to('notification')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    <div class="row">
        <div class="col-md-12">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Notification Details
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <form id="notification_datail" class="form-horizontal form-groups-bordered" role="form">
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">Type</label>
                            <div class="col-sm-4">
                                {{Form::select('NotificationType',$notificationType,'',array("class"=>"select2"))}}
                            </div>
                            <label class="col-sm-2 control-label" for="field-2">Email Addresses </label>
                            <div class="col-sm-4">
                                <input type="text" name="EmailAddresses" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-5" class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-4">
                                <div class="make-switch switch-small">
                                    <input type="checkbox" checked="checked" name="Status" value="1">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php $tabs = 'tabs'; ?>
    @include('notification.paymentreminder')




    <script src="{{ URL::asset('assets/js/jquery.multi-select.js') }}"></script>
    <script src="{{ URL::asset('assets/js/jquery.quicksearch.js') }}"></script>
    <script src="{{ URL::asset('assets/js/notification.js') }}"></script>
@stop
