<div class="modal fade" id="add-schedule-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="billing-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Schedule</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Name</label>
                                <input type="text" name="Name" class="form-control" id="field-1" placeholder="" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label  >Report</label>
                                {{ Form::select('ReportID[]',$reports,array(), array("class"=>"select2",'multiple',"data-placeholder"=>"Select")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Active</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox" checked=""  name="Status" value="0">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Send Email To</label>
                                <input type="text" name="Report[NotificationEmail]" class="form-control" id="field-1" placeholder="" value="" />
                                <div class="field-options">
                                    <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replycc').parent().removeClass('hidden'); $('#replycc').focus();">CC</a>
                                    <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replybcc').parent().removeClass('hidden'); $('#replybcc').focus();">BCC</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group hidden">
                                <label for="cc">CC</label>
                                <input type="text" name="Report[cc]"  class="form-control tags"  id="replycc" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group hidden">
                                <label for="bcc">BCC</label>
                                <input type="text" name="Report[bcc]"  class="form-control tags"  id="replybcc" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Period</label>
                                {{Form::select('Report[Time]',array(""=>"Select",'HOUR'=>'Hour',"DAILY"=>"Daily",'WEEKLY'=>'Weekly','MONTHLY'=>'Monthly',"YEARLY"=>"Yearly"),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Interval</label>
                                {{Form::select('Report[Interval]',array(),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>

                        <div class="clear"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Day</label>
                                {{Form::select('Report[Day][]',array("SUN"=>"Sunday","MON"=>"Monday","TUE"=>"Tuesday","WED"=>"Wednesday","THU"=>"Thursday","FRI"=>"Friday","SAT"=>"Saturday"),array('SUN','MON','TUE','WED','THU','FRI','SAT'),array( "class"=>"select2",'multiple',"data-placeholder"=>"Select day"))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Start Time</label>
                                <!--<input type="text"  name="Setting[JoStratTime]" value="" class="form-control timepicker starttime2" data-minute-step="5" data-show-meridian="true"  data-default-time="12:00:00 AM" data-show-seconds="true" data-template="dropdown">-->
                                <input name="Report[StartTime]" type="text" data-template="dropdown" data-show-seconds="true" data-default-time="12:00:00 AM" data-show-meridian="true" data-minute-step="5" class="form-control timepicker starttime2" value="12:00:00 AM" >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Format</label>
                                {{Form::select('Report[Format]',array(""=>"Select",Report::XLS=>"Excel",Report::PDF=>'PDF',Report::PNG=>'PNG'),Report::XLS,array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="report-scheduale-update"  class="save btn btn-success btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $( function() {
    $('body').on('click', '.schedule_report', function (ev) {
        ev.preventDefault();
        $('#billing-form').trigger("reset");
        var edit_url  = $(this).attr("href");
        $('#billing-form').attr("action",edit_url);
        if($(this).attr("id") == 'add-report-schedule'){
            $('#add-schedule-modal h4').html('Add Schedule');
        }else{
            $('#add-schedule-modal h4').html('Edit Schedule');
        }
        $('#billing-form select').select2("val", "");
        $(this).parent().children("div.hiddenRowData").find('input').each(function(i, el){
            var ele_name = $(el).attr('name');
            var ele_val = $(el).val();

            $("#billing-form [name='"+ele_name+"']").val(ele_val);
            if(ele_name =='Time' || ele_name == 'StartTime'){
                var selectBox = $("#billing-form [name='Report["+ele_name+"]']");
                selectBox.val(ele_val).trigger("change");
            }else if(ele_name == 'ReportID') {
                $("#billing-form [name='"+ele_name+"[]']").val(ele_val.split(',')).trigger('change');
            }else if(ele_name == 'Day') {
                $("#billing-form [name='Report["+ele_name+"][]']").val(ele_val.split(',')).trigger('change');
            }else if(ele_name == 'Interval' || ele_name == 'Format'){
                setTimeout(function(){
                    $("#billing-form [name='Report["+ele_name+"]']").val(ele_val).trigger('change');
                },5);
            }else if(ele_name == 'Status') {
                if (ele_val == 1) {
                    $("#billing-form [name='"+ele_name+"']").prop('checked', true)
                } else {
                    $("#billing-form [name='"+ele_name+"']").prop('checked', false)
                }
            }else{
                $("#billing-form [name='Report["+ele_name+"]']").val(ele_val);
            }
        });
        setTimeout(function () {
            if($("#billing-form [name='Report[Format]']").val() =='') {
                $("#billing-form [name='Report[Format]']").val('{{Report::XLS}}').trigger('change');
            }
        }, 10);

        $('#add-schedule-modal').modal('show');
    });

    $("#billing-form").submit(function(e){
        e.preventDefault();
        var _url  = $(this).attr("action");
        submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
    });

    });

</script>
<script src="{{ URL::asset('assets/js/billing_class.js') }}"></script>