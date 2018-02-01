<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_INVOICE_TYPE'): </label>
            <br>{{$Dispute['InvoiceType']}}

        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_AC_NAME'):</label>
            <br>{{$Dispute['AccountName']}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_INVOICE_NUMBER'): </label>
            <br>{{$Dispute['InvoiceNo']}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_DISPUTE_AMOUNT'): </label>
            <br>{{$Dispute['DisputeAmount']}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_NOTES'): </label>
            <br>{{nl2br($Dispute['Notes'])}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_STATUS'): </label>
            <br>{{$Dispute['Status']}}
        </div>
    </div>
    <div class="col-md-12">
        @if(!empty($Dispute['Attachment']))
        <div class="form-group">
            <label for="Attachment" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_ATTACHMENT'): </label>
            <div class="clear clearfix"></div>
            <a href="{{URL::to('/disputes/'.$Dispute['DisputeID'].'/download_attachment')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>@lang('routes.BUTTON_DOWNLOAD_CAPTION')</a>
        </div>
        @endif
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_CREATED_DATE'): </label>
            <br>{{$Dispute['created_at']}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_FIELD_CREATED_BY'): </label>
            <br>{{$Dispute['CreatedBy']}}
        </div>
    </div>
</div>
