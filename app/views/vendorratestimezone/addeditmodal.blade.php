<div class="modal fade" id="add-edit-modal-timezones">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add New Time Of Day</h4>
            </div>

            <form id="add-edit-timezones-form" method="post">
                <input type="hidden" name="VendorID" value="{{ $VendorID }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Type *</label>
                                {{Form::select('Type',$AllTypes,'',array("class"=>"form-control select2 small"))}}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Country*</label>
                                {{Form::select('Country',$CountryList,'',array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Time Of Day*</label>
                                {{Form::select('TimeZoneID',$TimeZones,'',array("class"=>"form-control select2 small"))}}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">From Time </label>
                                {{ Form::text('FromTime', '', array('class' => 'form-control timepicker', 'data-minute-step' => '5', 'data-show-meridian' => 'false', 'data-default-time' => '00:00', 'data-show-seconds' => 'false', 'data-template' => 'dropdown')); }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">To Time </label>
                                {{ Form::text('ToTime', '', array('class' => 'form-control timepicker', 'data-minute-step' => '5', 'data-show-meridian' => 'false', 'data-default-time' => '00:00', 'data-show-seconds' => 'false', 'data-template' => 'dropdown')); }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Days Of Week </label>
                                {{ Form::select('DaysOfWeek[]', Timezones::$DaysOfWeek, '', array('class' => 'form-control select2', 'multiple' => 'multiple')) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Days Of Month </label>
                                <?php $DaysOfMonth = array_combine($r = range(1,31), $r); //range 1 to 31 days for months ?>
                                {{ Form::select('DaysOfMonth[]', $DaysOfMonth, '', array('class' => 'form-control select2', 'multiple' => 'multiple')) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Months </label>
                                {{ Form::select('Months[]', Timezones::$Months, '', array('class' => 'form-control select2', 'multiple' => 'multiple')) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">This Time Of Day rates is used if *</label><br>
                                <div class="radio">
                                    <?php
                                        $Active = true;
                                        foreach (Timezones::$ApplyIF as $key => $value) {
                                    ?>
                                            <label>{{ Form::radio('ApplyIF', $key, $Active, array('class' => '')) }} {{$value}}</label><br>
                                    <?php
                                            $Active = false;
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Status</label><br>
                                <p class="make-switch switch-small">
                                    {{Form::checkbox('Status', '1', true, array("id"=>"Status"))}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{ Form::hidden('VendorTimeZoneID', '') }}
                    <button type="submit" id="product-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
