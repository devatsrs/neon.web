<div class="row">
    <?php $selectd_val ='';
     $count = 1;
    ?>

        @if (isset($CompanyGateway) && count($CompanyGateway) > 0)
            <div class="col-md-12">
                <div class="form-group">
                <label for="drp_rateGenerators" class="control-label ">Gateway</label>
                    {{Form::select('CompanyGatewayID', $CompanyGateway, (isset($commandconfigval->CompanyGatewayID)?$commandconfigval->CompanyGatewayID:'') ,array("id"=>"drp_rateGenerators" ,"class"=>"select2 small form-control"))}}
                </div>
            </div>
        @endif

        @if (isset($emailTemplates) && count($emailTemplates) > 0 && isset($accounts) && count($accounts) > 0)
            <div class="col-md-6">
                <div class="form-group">
                <label class="control-label ">Email Template</label>
                    {{Form::SelectControl('email_template',1,(isset($commandconfigval->TemplateID)?$commandconfigval->TemplateID:''))}}
                    <!--{Form::select('TemplateID', $emailTemplates, (isset($commandconfigval->TemplateID)?$commandconfigval->TemplateID:'') ,array("class"=>"select2 small form-control"))}}-->
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                <label class="control-label ">Account</label>
                    {{Form::select('AccountID', $accounts, (isset($commandconfigval->AccountID)?$commandconfigval->AccountID:'') ,array("class"=>"select2 small form-control"))}}
                </div>
            </div>
        @elseif(isset($emailTemplates) && count($emailTemplates) > 0)
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label ">Email Template</label>
                    {{Form::SelectControl('email_template',1,(isset($commandconfigval->TemplateID)?$commandconfigval->TemplateID:''))}}
                    <!--{Form::select('TemplateID', $emailTemplates, (isset($commandconfigval->TemplateID)?$commandconfigval->TemplateID:'') ,array("class"=>"select2 small form-control"))}}-->
                </div>
            </div>
        @endif

        @if (isset($rateGenerators) && count($rateGenerators) > 0 && isset($rateTables) && count($rateTables) > 0 )
            <div class="col-md-6">
                <div class="form-group">
                <label for="drp_rateGenerators" class="control-label ">Rate Generator</label>
                    {{Form::select('rateGenerators', $rateGenerators, (isset($commandconfigval)?$commandconfigval->rateGeneratorID:'') ,array("id"=>"drp_rateGenerators" ,"class"=>"select2 small form-control"))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="drp_rateGenerators" class="control-label ">Rate Table</label>

                    {{Form::select('rateTables', $rateTables,(isset($commandconfigval)?$commandconfigval->rateTableID:'') ,array("id"=>"drp_rateGenerators" ,"class"=>"select2 small form-control"))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="drp_rateGenerators" class="control-label ">&nbsp;</label>
                    <div>
                        <label for="drp_rateGenerators" class="control-label">
                            <input type="checkbox" id="rd-1" name="replace_rate" value="1" @if (isset($commandconfigval->replace_rate) && ($commandconfigval->replace_rate==1) > 0) checked @endif > &nbsp;&nbsp;Replace all of the existing rates
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="drp_rateGenerators" class="control-label ">Effective Rate</label>

                    {{Form::select('EffectiveRate', array('now'=>'Current','effective'=>'Effective on selected effective date','future'=>'Future'),(isset($commandconfigval->EffectiveRate)?$commandconfigval->EffectiveRate:'now') ,array("id"=>"" ,"class"=>"select2 small form-control"))}}
                </div>
            </div>
        @endif
        @if (isset($customers) && count($customers) > 0)
            <div class="col-md-6">
                <div class="form-group">
                    <label for="drp_rateGenerators" class="control-label ">Customers</label>
                    {{Form::select('Setting[customers][]', $customers, (isset($commandconfigval->customers)?$commandconfigval->customers:'') ,array("id"=>"customers" ,"class"=>"select2",'multiple',"data-placeholder"=>"Select Customers"))}}
                </div>
            </div>
        @endif
        @if (isset($vendors) && count($vendors) > 0)
            <div class="col-md-6">
                <div class="form-group">
                    <label for="drp_rateGenerators" class="control-label ">Vendors</label>
                    {{Form::select('Setting[vendors][]', $vendors, (isset($commandconfigval->vendors)?$commandconfigval->vendors:'') ,array("id"=>"vendors" ,"class"=>"select2",'multiple',"data-placeholder"=>"Select Vendors"))}}
                </div>
            </div>
        @endif
        @if (isset($gateway) && count($gateway) > 0)
            <div class="col-md-6">
                <div class="form-group">
                    <label for="drp_rateGenerators" class="control-label ">Gateway</label>
                    {{Form::select('Setting[gateway]', $gateway, (isset($commandconfigval->gateway)?$commandconfigval->gateway:'') ,array("id"=>"gateway" ,"class"=>"select2"))}}
                </div>
            </div>
        @endif
@foreach((array)$commandconfig as  $config)
@foreach((array)$config as $configtitle)
    <?php
        $selectd_val = '';
        if(isset($commandconfigval->$configtitle['name'])){
            $selectd_val =$commandconfigval->$configtitle['name'];
        }
        $count++;

        if(empty($selectd_val) && isset($configtitle['value']) && !empty($configtitle['value'])){
            $selectd_val=$configtitle['value'];
        }
    ?>
    @if($count%2 == 0)
        <div class="clear"></div>
    @endif
     <div class="col-md-6">
        <div class="form-group">
            <label for="field-5" class="control-label @if(isset($configtitle['timepicker'])) starttime2 @endif">{{$configtitle['title']}}
            @if($configtitle['name'] == 'ThresholdTime') <span data-original-title="What is Threshold Time?" data-content="Threshold Time is maximum running time. if time is more than threshold time then email will be sent to Error Email" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span> @endif
            @if($configtitle['name'] == 'ImportDays') <span data-original-title="Import Payments Day" data-content="if blank then system will import payments from last 7 days." data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span> @endif
            @if($configtitle['name'] == 'AlertEmailInterval') <span data-original-title="What is Alert Active Email Time?" data-content="It is interval time to send Email If any cron job is running out of its threshold time" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span> @endif
            @if($configtitle['name'] == 'StartDate' && $StartDateMessage!='') <span data-original-title="Dates" data-content="{{$StartDateMessage}}" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span> @endif
            </label>
            @if($configtitle['type'] == 'select' && isset($configtitle['multiple']) &&  $configtitle['multiple'] == 'multiple')
            {{Form::select('Setting['.$configtitle['name'].'][]',$configtitle['value'],$selectd_val, array( "class"=>"select2",'multiple',"data-placeholder"=>$configtitle['placeholder']))}}
            @elseif($configtitle['type'] == 'select')
            {{Form::select('Setting['.$configtitle['name'].']',$configtitle['value'],$selectd_val,array( "class"=>"select2 small"))}}
            @elseif($configtitle['type'] == 'text' && isset($configtitle['timepicker']))

            <input type="{{$configtitle['type']}}"  name="Setting[{{$configtitle['name']}}]" value="{{$selectd_val}}" class="form-control timepicker starttime2" data-minute-step="5" data-show-meridian="true"  data-default-time="12:00:00 AM" data-show-seconds="true" data-template="dropdown">

            @else
            <input type="{{$configtitle['type']}}" name="Setting[{{$configtitle['name']}}]" value="{{$selectd_val}}" class="form-control @if(isset($configtitle['datepicker'])) datepicker  @endif" @if(isset($configtitle['datepicker'])) data-date-format="yyyy-mm-dd"  @endif  id="field-5" placeholder="" @if(isset($configtitle['datepicker']) && isset($configtitle['startdate']) && $configtitle['startdate'] == 'now') data-startdate="{{date('Y-m-d')}}" @endif>
            @endif
         </div>
    </div>
@endforeach
@endforeach
</div>

<script type="text/javascript" >
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
// Datepicker
        if ($.isFunction($.fn.datepicker))
        {
            $(".datepicker").each(function(i, el)
            {
                var $this = $(el),
                        opts = {
                    //format: attrDefault($this, 'format', 'dd/mm/yyyy'),
                    startDate: attrDefault($this, 'startdate', ''),
                    endDate: attrDefault($this, 'enddate', ''),
                    daysOfWeekDisabled: attrDefault($this, 'disableddays', ''),
                    startView: attrDefault($this, 'startview', 0),
                    rtl: rtl()
                },
                $n = $this.next(),
                        $p = $this.prev();

                $this.datepicker(opts);

                if ($n.is('.input-group-addon') && $n.has('a'))
                {
                    $n.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.datepicker('show');
                    });
                }

                if ($p.is('.input-group-addon') && $p.has('a'))
                {
                    $p.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.datepicker('show');
                    });
                }
            });
        }

        // Popovers and tooltips
                $('[data-toggle="popover"]').each(function(i, el)
                {
                    var $this = $(el),
                        placement = attrDefault($this, 'placement', 'right'),
                        trigger = attrDefault($this, 'trigger', 'click'),
                        popover_class = $this.hasClass('popover-secondary') ? 'popover-secondary' : ($this.hasClass('popover-primary') ? 'popover-primary' : ($this.hasClass('popover-default') ? 'popover-default' : ''));

                    $this.popover({
                        placement: placement,
                        trigger: trigger
                    });

                    $this.on('shown.bs.popover', function(ev)
                    {
                        var $popover = $this.next();

                        $popover.addClass(popover_class);
                    });
                });


</script>
