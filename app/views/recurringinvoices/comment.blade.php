<div class="col-md-12">
    <div class="row">
        @if(!empty($EstimateComments) && count($EstimateComments)>0)
        <div class="form-group">
            <label for="field-5" class="col-sm-2 control-label">Comments:</label>
            <div class="col-sm-10">
                @foreach($EstimateComments as $EstimateComment)
                    {{$EstimateComment->Note}}<br>
                    {{$EstimateComment->created_at}}<br><br>
                @endforeach
            </div>
        </div>
        @endif
        <div class="form-group">
            <label for="field-5" class="col-sm-2 control-label">New Comment</label>
            <div class="col-sm-10">
                {{Form::textarea('Comment',$Comment,array("class"=>" form-control ","rows"=>5 ))}}
            </div>
         </div>
    </div>
</div>
{{Form::hidden('EstimateID',$Estimate->EstimateID)}}