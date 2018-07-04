<?php $count = 1;?>
<div class="row">
    <div class="col-md-12 cdrrerateaccountsbox">
        <div class="form-group">
            <label class="control-label">CDR Rerate Accounts <span type="button" class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-original-title="ReRate Accounts" data-content="If blank all accounts CDRs will be rated. Otherwise only selected accounts CDRs will be rated.">?</span></label>
            {{Form::select('Accounts[]', $Accounts, isset($gatewayconfigval->Accounts) ? $gatewayconfigval->Accounts : [] ,array("class"=>"form-control select2", "multiple"=>"multiple"))}}
        </div>
    </div>
@foreach($gatewayconfig as $configkey => $configtitle)
    <?php $selectd_val ='';
        if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey)){
            $selectd_val =$gatewayconfigval->$configkey;
        }
        if($configkey != 'AllowAccountImport'){
            $count++;
        }
        $NameFormat =  GatewayConfig::$NameFormat;
        if($GatewayName == 'Porta'){
            $NameFormat = GatewayConfig::$Porta_NameFormat;
        }else if($GatewayName == 'VOS'){
            $NameFormat = GatewayConfig::$Vos_NameFormat;
        }else if($GatewayName == 'SippySFTP'){
            $NameFormat = GatewayConfig::$Sippy_NameFormat;
        }else if($GatewayName == 'PBX'){
            $NameFormat = GatewayConfig::$Mirta_NameFormat;
        }else if($GatewayName == 'MOR'){
            $NameFormat = GatewayConfig::$MOR_NameFormat;
        }else if($GatewayName == 'CallShop'){
            $NameFormat = GatewayConfig::$CallShop_NameFormat;
        }else if($GatewayName == 'Streamco'){
            $NameFormat = GatewayConfig::$Streamco_NameFormat;
        }else if($GatewayName == 'FusionPBX'){
            $NameFormat = GatewayConfig::$FusionPBX_NameFormat;
        }else if($GatewayName == 'M2'){
            $NameFormat = GatewayConfig::$M2_NameFormat;
        }else if($GatewayName == 'VoipNow'){
            $NameFormat = GatewayConfig::$VoipNow_NameFormat;
        }else if($GatewayName == 'SippySQL'){
            $NameFormat = GatewayConfig::$SippySQL_NameFormat;
        }else if($GatewayName == 'VoipMS'){
            $NameFormat = GatewayConfig::$VoipMS_NameFormat;
        }
    ?>


    @if($count%2 == 0 && $configkey != 'AllowAccountImport')
            <div class="clear"></div>
    @endif

        @if($configkey == 'AllowAccountIPImport')
            <input id="AllowAccountIPImport"  type="hidden" name="AllowAccountIPImport" value="1">
        @endif
        @if($configkey == 'AllowAccountImport')
            <input id="AllowAccountImport"  type="hidden" name="AllowAccountImport" value="1">
        @else

     <div class="col-md-6 " @if($configkey == 'RateFormat') id="rate_dropdown" @endif>
        <div class="form-group" id="{{$configkey}}Box">
            @if($configkey != 'AllowAccountImport' && $configkey != 'AllowAccountIPImport')
            <label  class="control-label @if($configkey == 'RateCDR') col-md-13 @endif">{{$configtitle}} @if($configkey=='AutoAddIP') <span type="button" class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-original-title="Auto Add IP" data-content="IP will be automatically added to the account if account name matches to the switch account name. Turn ON Auto Add IP notification from Admin > Notifications.">?</span> @endif
           </label>
            @endif

            @if($configkey == 'NameFormat')
                {{Form::select($configkey,$NameFormat,$selectd_val,array( "class"=>"select2 small","id"=>$configkey))}}
            @elseif($configkey == 'CallType')
                {{Form::select($configkey,GatewayConfig::$CallType,$selectd_val,array( "class"=>"select2 small"))}}
            @elseif($configkey == 'BillingTime')
                {{Form::select($configkey,Company::$billing_time,$selectd_val,array( "class"=>"select2 small"))}}
            @elseif($configkey == 'RateCDR')
                <div class="clear col-md-13">
                <p class="make-switch switch-small">
                    <input id="RateCDR"  type="checkbox"   @if($selectd_val == 1) checked=""  @endif name="RateCDR" value="1">
                </p>
                </div>
            @elseif($configkey == 'RateMethod')
                {{Form::select($configkey,UsageDetail::$RateMethod,$selectd_val,array( "class"=>"select2 small ReRateOptions","id"=>$configkey))}}
            @elseif($configkey == 'SpecifyRate')
                 <input id="SpecifyRate"  type="text"  class="form-control ReRateOptions"  value="{{$selectd_val}}"  name="SpecifyRate" >
            @elseif($configkey == 'AutoAddIP')
                <div class="clear col-md-13">
                    <p class="make-switch switch-small">
                        <input id="AutoAddIP"  type="checkbox"   @if($selectd_val == 1) checked=""  @endif name="AutoAddIP" value="1">
                    </p>
                </div>
            @elseif($configkey == 'RateFormat')
                <?php
                $disable = array();
                if(!empty($selectd_val)){
                    $disable = array('disabled'=>'disabled');
                }
                $options = array_merge(array( "class"=>"select2 small"),$disable);
                ?>
                <input type="hidden" name="RateFormat" value="{{$selectd_val}}">
                {{Form::select($configkey,Company::$rerate_format,$selectd_val,$options)}}

            @elseif($configkey == 'key')
                    <br/>
                    <input type="hidden" name="oldkey" value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey)){{$gatewayconfigval->$configkey}}@endif">
                    <input  name="{{$configkey}}" type="file"
                           class="form-control file2 inline btn btn-primary"
                           data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse"/>
                 <span class="file-input-name">
                        @if(isset($gatewayconfigval->$configkey))
                            {{basename($gatewayconfigval->$configkey)}}
                        @endif
                 </span>

            @elseif($configkey == 'protocol_type')
                    {{Form::select($configkey,Gateway::$protocol_type,$selectd_val,array( "class"=>"select2 small","id"=>$configkey))}}
            @elseif($configkey == 'passive_mode')
                <div class="clear col-md-13">
                    <p class="make-switch switch-small">
                        <input id="{{$configkey}}"  type="checkbox"   @if($selectd_val == 1) checked=""  @endif name="{{$configkey}}" value="1">
                    </p>
                </div>

            @elseif($configkey == 'ssl')
                <div class="clear col-md-13">
                    <p class="make-switch switch-small">
                        <input id="{{$configkey}}"  type="checkbox"   @if($selectd_val == 1) checked=""  @endif name="{{$configkey}}" value="1">
                    </p>
                </div>
            @else
                @if($configkey != 'AllowAccountIPImport')
                    <input @if($configkey == 'password' || $configkey == 'dbpassword' || $configkey == 'sshpassword' || $configkey == 'api_password') type="password" @else type="text" @endif  value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey) && !empty($gatewayconfigval->$configkey) && ($configkey == 'password' || $configkey == 'dbpassword' || $configkey == 'sshpassword' || $configkey == 'api_password')){{''}}@elseif(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey)){{$gatewayconfigval->$configkey}}@endif" name="{{$configkey}}" class="form-control"  placeholder="">
                @endif

                @if($configkey == 'password')<input type="hidden" disabled value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey) && !empty($gatewayconfigval->$configkey) && $configkey == 'password'   ){{''}}@endif" name="{{$configkey}}_disabled" class="form-control"  placeholder="">@endif
                @if($configkey == 'dbpassword')<input type="hidden" disabled value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey) && !empty($gatewayconfigval->$configkey) && $configkey == 'dbpassword'   ){{''}}@endif" name="{{$configkey}}_disabled" class="form-control"  placeholder="">@endif
                @if($configkey == 'sshpassword')<input type="hidden" disabled value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey) && !empty($gatewayconfigval->$configkey) && $configkey == 'sshpassword'   ){{''}}@endif" name="{{$configkey}}_disabled" class="form-control"  placeholder="">@endif
                @if($configkey == 'api_password')<input type="hidden" disabled value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey) && !empty($gatewayconfigval->$configkey) && $configkey == 'api_password'   ){{''}}@endif" name="{{$configkey}}_disabled" class="form-control"  placeholder="">@endif

            @endif
         </div>
    </div>
        @endif
@endforeach
</div>

<script>


    $(document).ready(function() {
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

        $(document).on('change', 'select[name="RateMethod"]', function() {
            $('#SpecifyRateBox').parent().addClass('hidden');
            if ($(this).val() == 'SpecifyRate' || $(this).val() == 'ValueAgainstCost') {
                $('#SpecifyRateBox').parent().removeClass('hidden');
            }
        });

        $(document).on('change', 'select[name="protocol_type"]', function() {

            if ($(this).val() == '{{Gateway::SSH_FILE_TRANSFER}}' ){

                $("input[name='port']").val("").parents(".form-group").parent().addClass("hidden");
                $("input[name='ssl']").parents(".form-group").parent().addClass("hidden");
                $("input[name='passive_mode']").parents(".form-group").parent().addClass("hidden");

            }else {

                $("input[name='port']").parents(".form-group").parent().removeClass("hidden");
                $("input[name='ssl']").parents(".form-group").parent().removeClass("hidden");
                $("input[name='passive_mode']").parents(".form-group").parent().removeClass("hidden");

            }
        });
        $('select[name="protocol_type"]').trigger('change');


        $(document).on('change', 'select[name="NameFormat"]', function() {

        });

        // Replaced File Input
        $("input.file2[type=file]").each(function(i, el)
        {
            var $this = $(el),
                    label = attrDefault($this, 'label', 'Browse');

            $this.bootstrapFileInput(label);
        });

        // Jasny Bootstrap | Fileinput
        if ($.isFunction($.fn.fileinput))
        {
            $(".fileinput").fileinput()
        }

    });

</script>