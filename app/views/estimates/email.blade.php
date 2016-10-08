<div class="form-group">
    <label for="field-5" class="col-sm-2 control-label">To</label>
    <div class="col-sm-10">
        {{Form::text('Email',$Account->BillingEmail,array("class"=>"form-control"))}}
    </div>
 </div>
<div class="form-group">
    <label for="field-5" class="col-sm-2 control-label">Subject</label>
    <div class="col-sm-10">

       {{Form::text('Subject',$Subject,array("class"=>" form-control"))}}
    </div>
 </div>
<div class="form-group">
    <label for="field-5" class="col-sm-2 control-label">Message</label>
    <div class="col-sm-10">
        {{Form::textarea('Message',$Message,array("class"=>" form-control ","rows"=>5 ))}}
        <br>
        <a target="_blank" href="{{URL::to('/estimate/'.$Estimate->EstimateID.'/estimate_preview')}}">View Estimate</a>
        <br>
        <br>
        <br>
        Best Regards,<br><br>
        {{$CompanyName}}
    </div>
 </div>
{{Form::hidden('EstimateID',$Estimate->EstimateID)}}