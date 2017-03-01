<?php $count = 1;?>
<div class="row">
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
        }
    ?>
    @if($count%2 == 0)
            <div class="clear"></div>
    @endif
        @if($configkey == 'AllowAccountImport')
            <input id="AllowAccountImport"  type="hidden" name="AllowAccountImport" value="1">
        @else

     <div class="col-md-6 " @if($configkey == 'RateFormat') id="rate_dropdown" @endif>
        <div class="form-group">
            @if($configkey != 'AllowAccountImport')
            <label for="field-5" class="control-label @if($configkey == 'RateCDR') col-md-13 @endif">{{$configtitle}}</label>
            @endif

            @if($configkey == 'NameFormat')
                {{Form::select($configkey,$NameFormat,$selectd_val,array( "class"=>"select2 small"))}}
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

            @else

                <input @if($configkey == 'password') type="password" @else type="text" @endif  value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey) && !empty($gatewayconfigval->$configkey) && $configkey == 'password'){{''}}@elseif(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey)){{$gatewayconfigval->$configkey}}@endif" name="{{$configkey}}" class="form-control" id="field-5" placeholder="">

                    @if($configkey == 'password')<input type="hidden" disabled value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey) && !empty($gatewayconfigval->$configkey) && $configkey == 'password'   ){{''}}@endif" name="{{$configkey}}_disabled" class="form-control" id="field-5" placeholder="">@endif

                @endif
         </div>
    </div>
        @endif
@endforeach
</div>

<script>
/*if ($.isFunction($.fn.selectBoxIt))
{
    $("select.selectboxit").each(function(i, el)
    {
        var $this = $(el),
                opts = {
            showFirstOption: attrDefault($this, 'first-option', true),
            'native': attrDefault($this, 'native', false),
            defaultText: attrDefault($this, 'text', '')
        };

        $this.addClass('visible');
        $this.selectBoxIt(opts);
    });
}*/
</script>