@if (isset($DynamicFields) && $DynamicFields['totalfields'] > 0)
    <?php
        $cnt=0;
    ?>
    <hr/>
    <link rel="stylesheet" href="<?php echo URL::to('/'); ?>/assets/css/bootstrap-datetimepicker.css">
    <link rel="stylesheet" href="<?php echo URL::to('/'); ?>/assets/css/bootstrap-datetimepicker.min.css">
    <div class="row margin-top">
        <div class="col-md-12">
            <div class="form-group">
                @foreach($DynamicFields['fields'] as $field)
                    @if($field->Status == 1)
                        <?php
                        $DynamicFieldValue=$field->DefaultValue;
                         if(isset($data) && $data['ProductID'] > 0){
                             $DynamicFieldsValues = DynamicFieldsValue::getDynamicColumnValuesByProductID($field->DynamicFieldsID,$data['ProductID']);
                             $FieldName = $field->FieldName;
                             if($DynamicFieldsValues->count() > 0){
                                 foreach ($DynamicFieldsValues as $DynamicFieldsValue) {
                                     $DynamicFieldValue = $DynamicFieldsValue->FieldValue;
                                     $DynamicFieldsValueID=$DynamicFieldsValue->DynamicFieldsValueID;
                                 }
                             } else {
                                 $DynamicFieldValue = "";
                                 $DynamicFieldsValueID=0;
                             }
                         }
                        ?>

                <?php
                    if($cnt!=0 && $cnt%2==0){
                ?>
                        </div>
                    </div>
                </div>
                <div class="row margin-top">
                    <div class="col-md-12">
                        <div class="form-group">
                <?php
                }
                ?>

                    <label for="field-5" class="control-label col-sm-2">{{ $field->FieldName }}</label>
                    <div class="col-sm-4">
                        @if($field->FieldDomType == 'string' || $field->FieldDomType == 'numeric')
                            {{Form::text('DynamicFields['.$field->DynamicFieldsID.']', $DynamicFieldValue,array("class"=>"form-control"))}}
                        @elseif($field->FieldDomType == 'textarea')
                            {{ Form::textarea('DynamicFields['.$field->DynamicFieldsID.']', $DynamicFieldValue,array('rows' => 2, "class"=>"form-control")) }}
                        @elseif($field->FieldDomType == 'select')
                            <?php
                                $result=array();
                                $arr=explode(',',$field->SelectVal);
                                foreach($arr as $i =>$val){
                                    $result[$val]=$val;
                                }
                                $result=[''=>'Select']+$result;
                            ?>
                            {{Form::select('DynamicFields['.$field->DynamicFieldsID.']',$result,$DynamicFieldValue,array("class"=>"form-control"))}}
                    @elseif($field->FieldDomType == 'file')
                        {{-- Form::file('DynamicFields['.$field->DynamicFieldsID.']',array('rows' => 2, "class"=>"form-control")) --}}
                        <input name="DynamicFields[<?php echo $field->DynamicFieldsID; ?>]" type="file" accept=".png" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;Browse" />
                        @if(isset($DynamicFieldValue) && $DynamicFieldValue != '')
                            <?php
                                $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$field->CompanyID)."/";
                                $fileUrl=$field->DynamicFieldsID."/dynamicfields/";
                                $url="products/dynamicfield/".$DynamicFieldsValueID."/download";
                            ?>
                            <a href="{{URL::to($url)}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>

                        @endif
                    @elseif($field->FieldDomType == 'datetime')
                        <div class='input-group date' id='datetimepicker1'>
                            <input type='text' class="form-control datetimepicker" value="{{ $DynamicFieldValue  }}" name="DynamicFields[<?php echo $field->DynamicFieldsID; ?>]"/>
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                        </div>
                    @elseif($field->FieldDomType == 'boolean')
                            <p class="make-switch switch-small">
                                <input id="DynamicFields[<?php echo $field->DynamicFieldsID; ?>]" name="hDynamicFields[<?php echo $field->DynamicFieldsID; ?>]" class="boolean_field" type="checkbox" value="1" checked >
                                <input type="hidden" name="DynamicFields[<?php echo $field->DynamicFieldsID; ?>]" id="hDynamicFields[<?php echo $field->DynamicFieldsID; ?>]" value="<?php echo $DynamicFieldValue; ?>">
                            </p>
                            <?php
                                if($DynamicFieldValue=='' || $DynamicFieldValue=='0'){
                                    ?>
                                    <script>
                                        $(document).ready(function() {
                                            console.log("555");
                                            $('[name="hDynamicFields[<?php echo $field->DynamicFieldsID; ?>]').prop("checked", false).trigger('change');
                                        });
                                    </script>
                                <?php
                                }else{
                                    ?>
                                    <script>
                                        $(document).ready(function() {
                                            console.log("666");
                                        $('[name="hDynamicFields[<?php echo $field->DynamicFieldsID; ?>]').prop("checked", true).trigger('change');
                                        });
                                    </script>
                                    <?php
                                }
                            ?>
                    @endif

            </div>
            <?php $cnt++; ?>
        @endif
    @endforeach
@endif

<script type="text/javascript" src="<?php echo URL::to('/'); ?>/assets/js/bootstrap-datetimepicker.js" ></script>
<script type="text/javascript" src="<?php echo URL::to('/'); ?>/assets/js/bootstrap-datetimepicker.min.js" ></script>
<script>
    $(document).ready(function(){
        $('.datetimepicker').datetimepicker({
            format:'yyyy-mm-dd H:i:00'
        });
        // Replaced File Input
        $("input.file2[type=file]").not("#Image").each(function(i, el)
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

        $(".boolean_field").on('change',function(){
            var name=$(this).attr('id');
           if($(this).prop('checked') == true){
              $("[name='"+name+"']").val('1');
           }else{
               $("[name='"+name+"']").val(0);
           }
        });

    });
</script>