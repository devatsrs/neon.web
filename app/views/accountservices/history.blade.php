<style>
    .modal-ku {
        width: 750px;
        margin: auto;
    }
    .display{
        display:none;
    }
</style>
@section('footer_ext')
    @parent
    <div class="modal fade" id="history-modal">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title">Contract History</h3>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="col-md-2 control-label">Date</label>
                                @if(isset($AccountServiceHistory->Date))
                                    {{$AccountServiceHistory->Date}}
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="col-md-2 control-label">Action</label>
                                @if(isset($AccountServiceHistory->Action))
                                    {{$AccountServiceHistory->Action}}
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="col-md-2 control-label">Action By</label>
                                @if(isset($AccountServiceHistory->ActionBy))
                                    {{$AccountServiceHistory->ActionBy}}
                                @endif
                            </div>
                        </div>
                    </div>

<div class="modal-footer">
    <button  type="button" class="btn  btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
        <i class="entypo-cancel"></i>
        Close
    </button>
</div>
</div>
</div>
</div>
{{--<div class="modal fade" id="history-modal">--}}
        {{--<div class="modal-dialog  modal-lg">--}}
            {{--<div class="modal-content">--}}
                    {{--<div class="modal-header">--}}
                        {{--<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>--}}
                        {{--<h3 class="modal-title">Cancellation Contract</h3>--}}
                    {{--</div>--}}
                {{--<div class="row">--}}
                    {{--<div class="modal-body">--}}
                        {{--<div class="col-md-12">--}}
                            {{--<label class="col-md-2 control-label">DATE</label>--}}
                            {{--@if(isset($AccountServiceHistory->Date))--}}
                                {{--{{$AccountServiceHistory->Date}}--}}
                            {{--@endif--}}
                        {{--</div>--}}

                        {{--<div class="col-md-12">--}}
                            {{--<label class="col-md-2 control-label">DATE</label>--}}
                            {{--@if(isset($AccountServiceHistory->Date))--}}
                                {{--{{$AccountServiceHistory->Date}}--}}
                            {{--@endif--}}
                        {{--</div>--}}
                {{--</div>--}}


                    {{--<div class="modal-footer">--}}
                        {{--<button type="submit" id="currency-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">--}}
                            {{--<i class="entypo-floppy"></i>--}}
                            {{--Save--}}
                        {{--</button>--}}
                        {{--<button  type="button" class="btn  btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">--}}
                            {{--<i class="entypo-cancel"></i>--}}
                            {{--Close--}}
                        {{--</button>--}}
                    {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
        {{--</div>--}}

    {{--<div class="modal fade" id="history-modal">--}}
        {{--<div class="modal-dialog  modal-lg">--}}
            {{--<div class="modal-content">--}}
                {{--<div class="modal-header">--}}
                    {{--<h3 class="modal-title">Contract History</h3>--}}
                {{--</div>--}}
                {{--<div class="modal-body">--}}
                    {{--<div class="col-md-12">--}}
                        {{--<label class="col-md-2 control-label">DATE</label>--}}
                        {{--@if(isset($AccountServiceHistory->Date))--}}
                            {{--{{$AccountServiceHistory->Date}}--}}
                        {{--@endif--}}
                    {{--</div>--}}

                    {{--<div class="col-md-12">--}}
                        {{--<label class="col-md-2 control-label">DATE</label>--}}
                        {{--@if(isset($AccountServiceHistory->Date))--}}
                            {{--{{$AccountServiceHistory->Date}}--}}
                        {{--@endif--}}
                    {{--</div>--}}

                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
@stop

