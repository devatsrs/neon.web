<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $('#add-new-config').click(function(ev){
        ev.preventDefault();
        $('#add-new-config-form').trigger("reset");
        $("#add-new-config-form [name='CronJobID']").val('');
        $("#CronJobCommandID").select2().select2('val','');
        //$("#add-new-config-form [name='Setting[JobTime]']").val('');
        $("#add-new-config-form [name='Setting[JobTime]']").val('').trigger("change");
        $("#add-new-config-form [name='Setting[JobDay][]']").val('').trigger("change");
        $("#CronJobCommandID").trigger('change');
        $("#CronJobCommandID").prop("disabled", false);
        $('#add-new-modal-config h4').html('Add New Cron Job');
        $('#add-new-modal-config').modal('show');
    });

    $('table tbody').on('click','.delete-config',function(ev){
        result = confirm("Are you Sure?");
        if(result){
            submit_ajax(baseurl+'/cronjobs/delete/'+$(this).attr('data-id'));
        }
    });

    $('table tbody').on('click','.edit-config',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-config-form').trigger("reset");
        $("#config-update").button('reset');

        var prevrow = $(this).parent().find("div.hiddenRowData");
        $('#add-new-modal-config h4').html('Edit Cron Job');
        $('#add-new-modal-config').modal('show');
        $("#add-new-config-form [name='CronJobID']").val(prevrow.find("input[name='CronJobID']").val())
        $("#add-new-config-form [name='Title']").val(prevrow.find("input[name='Title']").val())
        $("#add-new-config-form [name='JobStartTime']").val(prevrow.find("input[name='JobStartTime']").val())
        $("#add-new-config-form [name='JobTitle']").val(prevrow.find("input[name='JobTitle']").val())
        var setting = prevrow.find("#cron_set").html()
        if (setting !== null && setting.trim() != ''){
            var json = JSON.parse(setting);
            if(json !== null && typeof  json['JobStartTime'] != 'undefined'){
                $("#add-new-config-form [name='Setting[JobStartTime]']").val(json['JobStartTime']);
            }
            if(json !== null && typeof  json['JobTime'] != 'undefined'){
                $("#add-new-config-form [name='Setting[JobTime]']").val(json['JobTime']).trigger("change");
            }
            if(json !== null && typeof  json['JobInterval'] != 'undefined'){
                $("#add-new-config-form [name='Setting[JobInterval]']").val(json['JobInterval']).trigger("change");
            }
            if(json !== null && typeof json['JobDay'] != 'undefined'){
                var str =json['JobDay'].toString().split(",");
                $("#add-new-config-form [name='Setting[JobDay][]']").val(str).trigger("change");
            }else{
                $("#add-new-config-form [name='Setting[JobDay][]']").val('').trigger("change");
            }
            if(json !== null && typeof  json['JobStartDay'] != 'undefined'){
                $("#add-new-config-form [name='Setting[JobStartDay]']").val(json['JobStartDay']).trigger("change");
            }

        }
        $("#CronJobCommandID").val(prevrow.find("input[name='CronJobCommandID']").val()).trigger("change");
        $("#CronJobCommandID").prop("disabled", true);

        if(prevrow.find("input[name='Status']").val() == 1 ){
            $('[name="Status_name"]').prop('checked',true)
        }else{
            $('[name="Status_name"]').prop('checked',false)
        }
    });

    $('[name="Status_name"]').change(function(e){
        if($(this).prop('checked')){
            $("#add-new-config-form [name='Status']").val(1);
        }else{
            $("#add-new-config-form [name='Status']").val(0);
        }

    });
    $('#add-new-config-form').submit(function(e){
        e.preventDefault();
        var CronJobID = $("#add-new-config-form [name='CronJobID']").val()
        if( typeof CronJobID != 'undefined' && CronJobID != ''){
            update_new_url = baseurl + '/cronjobs/update/'+CronJobID;
        }else{
            update_new_url = baseurl + '/cronjobs/create';
        }
        $.ajax({
            url: update_new_url,  //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if(response.status =='success'){
                    toastr.success(response.message, "Success", toastr_opts);
                    $('#add-new-modal-config').modal('hide');
                    data_table.fnFilter('', 0);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
                $("#config-update").button('reset');
            },
            // Form data
            data: $('#add-new-config-form').serialize(),
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false
        });
    });

    $('#CronJobCommandID').change(function(e){

        $('#ajax_config_html').html('Loading...<br>');

        if($(this).val() != ''){
            $("#CronJobCommandID_hide").val($(this).val());
            $.ajax({
                url: baseurl + "/cronjobs/ajax_load_cron_dropdown",
                type: 'POST',
                success: function(response) {
                    $('#ajax_config_html').html(response);
                    $("#ajax_config_html .select2").each(function(i, el)
                    {
                        var $this = $(el),
                                opts = {
                                    allowClear: attrDefault($this, 'allowClear', false)
                                };
                        if($this.hasClass('small')){
                            opts['minimumResultsForSearch'] = attrDefault($this, 'allowClear', Infinity);
                            opts['dropdownCssClass'] = attrDefault($this, 'allowClear', 'no-search')
                        }
                        $this.select2(opts);
                        if($this.hasClass('small')){
                            $this.select2('container').find('.select2-search').addClass ('hidden') ;
                        }
                        //$this.select2("open");
                    }).promise().done(function(){
                        $('.select2').css('visibility','visible');
                    });


                    if ($.isFunction($.fn.perfectScrollbar))
                    {
                        $(".select2-results").niceScroll({
                            cursorcolor: '#d4d4d4',
                            cursorborder: '1px solid #ccc',
                            railpadding: {right: 3}
                        });
                    }

                },
                // Form data
                data: "CronJobCommandID="+$(this).val()+'&CronJobID='+$("#add-new-config-form [name='CronJobID']").val(),
                cache: false
            });
        }else{
            $('#ajax_config_html').html('');
        }
    });


    $("#add-new-config-form [name='Setting[JobTime]']").change(function(){
        console.log("jobtype" + $(this).val());
        populateJonInterval($(this).val());

    });

    function populateJonInterval(jobtype){

        //console.log("in populateJonInterval ");
        $("#add-new-config-form [name='Setting[JobInterval]']").addClass('visible');
        var selectBox = $("#add-new-config-form [name='Setting[JobInterval]']");
        //var selectBoxStartDay = $("#add-new-config-form [name='Setting[JobStartDay]']").selectBoxIt().data("selectBox-selectBoxIt");
        $("#add-new-config-form .JobStartDay").hide();
        var starttime = $("#add-new-config-form .starttime");
        if(selectBox){
            selectBox.empty();
            options = [];
            // console.log("jobtype" + jobtype);
            if(jobtype == 'HOUR'){
                for(var i=1;i<'24';i++){
                    options.push(new Option(i+" Hour", i, true, true));
                }
                starttime.show();
            }else if(jobtype == 'MINUTE'){
                for(var i=1;i<60;i++){
                    options.push(new Option(i+" Minute", i, true, true));
                }
                starttime.hide();
                starttime.val('');
            }else if(jobtype == 'SECONDS'){
                options.push(new Option("10 Second", 10, true, true));
                options.push(new Option("20 Second", 20, true, true));
                options.push(new Option("30 Second", 30, true, true));
                starttime.hide();
                starttime.val('');
            }else if(jobtype == 'DAILY'){
                for(var i=1;i<'32';i++){
                    options.push(new Option(i+" Day", i, true, true));
                }
                //console.log("jobtype" + jobtype);
                starttime.show();
            }else if(jobtype == 'MONTHLY'){
                for(var i=1;i<13;i++){
                    options.push(new Option(i+" Month", i, true, true));
                }
                for(var i=1;i<'32';i++){
                    options.push(new Option(i+" Day", i, true, true));

                }
                $("#add-new-config-form .JobStartDay").show();
                starttime.show();
            }
            //options.sort();
            selectBox.append(options);
            var firstval = selectBox.find('option').first().val();
            selectBox.val(firstval).trigger('change');
            @if(isset($commandconfigval->JobInterval))
            selectBox.val('{{$commandconfigval->JobInterval}}').trigger("change");
            @endif
        }
    }

    // Timepicker
    if ($.isFunction($.fn.timepicker))
    {
        $(".timepicker").each(function(i, el)
        {
            var $this = $(el),
                    opts = {
                        template: attrDefault($this, 'template', false),
                        showSeconds: attrDefault($this, 'showSeconds', false),
                        defaultTime: attrDefault($this, 'defaultTime', 'current'),
                        showMeridian: attrDefault($this, 'showMeridian', true),
                        minuteStep: attrDefault($this, 'minuteStep', 15),
                        secondStep: attrDefault($this, 'secondStep', 15)
                    },
                    $n = $this.next(),
                    $p = $this.prev();

            $this.timepicker(opts);

            if ($n.is('.input-group-addon') && $n.has('a'))
            {
                $n.on('click', function(ev)
                {
                    ev.preventDefault();

                    $this.timepicker('showWidget');
                });
            }

            if ($p.is('.input-group-addon') && $p.has('a'))
            {
                $p.on('click', function(ev)
                {
                    ev.preventDefault();

                    $this.timepicker('showWidget');
                });
            }
        });
    }
});
</script>
<div class="modal fade" id="add-new-modal-config">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-config-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Cron Job</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Job Title</label>
                                <input type="text" name="JobTitle" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Cron Type</label>
                                {{ Form::select('CronJobCommandID', $commands, '', array("class"=>"select2",'id'=>'CronJobCommandID')) }}
                            </div>
                        </div>
                    </div>
                    <div id="ajax_config_html"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Job Time</label>
                                {{Form::select('Setting[JobTime]',array(""=>"Select run time","SECONDS"=>"Seconds","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily",'MONTHLY'=>'Monthly'),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Job Interval</label>
                                {{Form::select('Setting[JobInterval]',array(),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Job Day</label>
                                {{Form::select('Setting[JobDay][]',array("SUN"=>"Sunday","MON"=>"Monday","TUE"=>"Tuesday","WED"=>"Wednesday","THU"=>"Thursday","FRI"=>"Friday","SAT"=>"Saturday"),'',array( "class"=>"select2",'multiple',"data-placeholder"=>"Select day"))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Job Start Time</label>
                                <!--<input type="text"  name="Setting[JoStratTime]" value="" class="form-control timepicker starttime2" data-minute-step="5" data-show-meridian="true"  data-default-time="12:00:00 AM" data-show-seconds="true" data-template="dropdown">-->
                                <input type="text" data-template="dropdown" data-show-seconds="true" data-default-time="12:00:00 AM" data-show-meridian="true" data-minute-step="5" class="form-control timepicker starttime2" value="12:00:00 AM" name="Setting[JobStartTime]">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 JobStartDay">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Job Start Day</label>
                                {{Form::select('Setting[JobStartDay]',array(),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Active</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox" checked=""  name="Status_name" value="0">
                                    </p>
                                    <input type="hidden"  name="Status" value="0">
                                    <input type="hidden"  name="CronJobCommandID" id="CronJobCommandID_hide">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="CronJobID" value="">
                        <button type="submit" id="config-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
