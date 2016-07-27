<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">Invoice Type: </label>
            <br>{{$Dispute['InvoiceType']}}

        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">Account Name: </label>
            <br>{{$Dispute['AccountName']}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">Invoice Number: </label>
            <br>{{$Dispute['InvoiceNo']}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">Dispute Amount: </label>
            <br>{{$Dispute['DisputeAmount']}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">Notes: </label>
            <br>{{nl2br($Dispute['Notes'])}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">Status: </label>
            <br>{{$Dispute['Status']}}
        </div>
    </div>
    <div class="col-md-12">
        @if(!empty($Dispute['Attachment']))
        <div class="form-group">
            <label for="Attachment" class="control-label">Attachment: </label>
            <div class="clear clearfix"></div>
            <a href="{{URL::to('/disputes/'.$Dispute['DisputeID'].'/download_attachment')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>
        </div>
        @endif
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">Created Date: </label>
            <br>{{$Dispute['created_at']}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">Created By: </label>
            <br>{{$Dispute['CreatedBy']}}
        </div>
    </div>
</div>
