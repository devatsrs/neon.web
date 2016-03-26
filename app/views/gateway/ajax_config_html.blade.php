<?php $count = 1;?>
<div class="row">
@foreach($gatewayconfig as $configkey => $configtitle)
    <?php $selectd_val ='';
        if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey)){
            $selectd_val =$gatewayconfigval->$configkey;
        }
        $count++;
        $NameFormat =  GatewayConfig::$NameFormat;
        if($GatewayName == 'Porta'){
            unset($NameFormat['IP']);
        }
    ?>
    @if($count%2 == 0)
            <div class="clear"></div>
    @endif
     <div class="col-md-6 " @if($configkey == 'RateFormat') id="rate_dropdown" @endif>
        <div class="form-group">
            <label for="field-5" class="control-label @if($configkey == 'RateCDR') col-md-13 @endif">{{$configtitle}}</label>
            @if($configkey == 'NameFormat')
                {{Form::select($configkey,$NameFormat,$selectd_val,array( "class"=>"selectboxit"))}}
            @elseif($configkey == 'CallType')
                {{Form::select($configkey,GatewayConfig::$CallType,$selectd_val,array( "class"=>"selectboxit"))}}
            @elseif($configkey == 'BillingTime')
                {{Form::select($configkey,Company::$billing_time,$selectd_val,array( "class"=>"selectboxit"))}}
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
                $options = array_merge(array( "class"=>"selectboxit"),$disable);
                ?>
                <input type="hidden" name="RateFormat" value="{{$selectd_val}}">
                {{Form::select($configkey,Company::$rerate_format,$selectd_val,$options)}}
            @elseif($configkey == 'AllowAccountImport')
                <div class="clear col-md-13">
                    <p class="make-switch switch-small">
                        <input id="AllowAccountImport"  type="checkbox"   @if($selectd_val == 1) checked=""  @endif name="AllowAccountImport" value="1">
                    </p>
                </div>
            @else
                <input @if($configkey == 'password') type="password" @else type="text" @endif  value="@if(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey) && $configkey == 'password'){{Crypt::decrypt($gatewayconfigval->$configkey)}}@elseif(isset($gatewayconfigval) && isset($gatewayconfigval->$configkey)){{$gatewayconfigval->$configkey}}@endif" name="{{$configkey}}" class="form-control" id="field-5" placeholder="">
            @endif
         </div>
    </div>
@endforeach
</div>

<script>
if ($.isFunction($.fn.selectBoxIt))
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
}
</script>