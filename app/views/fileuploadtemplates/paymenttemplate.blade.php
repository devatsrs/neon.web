<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Account Name*</label>
    <div class="col-sm-4"> {{Form::select('selection[AccountName]', $columns,(isset($attrselection->AccountName)?$attrselection->AccountName:''),array("class"=>"select2 small"))}} </div>
    <label for="field-1" class="col-sm-2 control-label">Payment Date*</label>
    <div class="col-sm-4"> {{Form::select('selection[PaymentDate]', $columns,(isset($attrselection->PaymentDate)?$attrselection->PaymentDate:''),array("class"=>"select2 small"))}} </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Payment Method*</label>
    <div class="col-sm-4"> {{Form::select('selection[PaymentMethod]', $columns,(isset($attrselection->PaymentMethod)?$attrselection->PaymentMethod:''),array("class"=>"select2 small"))}} </div>
    <label for="field-1" class="col-sm-2 control-label">Action*</label>
    <div class="col-sm-4"> {{Form::select('selection[PaymentType]', $columns,(isset($attrselection->PaymentType)?$attrselection->PaymentType:''),array("class"=>"select2 small"))}} </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Amount*</label>
    <div class="col-sm-4"> {{Form::select('selection[Amount]', $columns,(isset($attrselection->Amount)?$attrselection->Amount:''),array("class"=>"select2 small"))}} </div>
    <label for="field-1" class="col-sm-2 control-label">Invoice</label>
    <div class="col-sm-4"> {{Form::select('selection[InvoiceNo]', $columns,(isset($attrselection->InvoiceNo)?$attrselection->InvoiceNo:''),array("class"=>"select2 small"))}} </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Note</label>
    <div class="col-sm-4"> {{Form::select('selection[Notes]', $columns,(isset($attrselection->Notes)?$attrselection->Notes:''),array("class"=>"select2 small"))}} </div>
    <label for=" field-1" class="col-sm-2 control-label">Date Format</label>
    <div class="col-sm-4"> {{Form::select('selection[DateFormat]',Company::$date_format ,(isset($attrselection->DateFormat)?$attrselection->DateFormat:''),array("class"=>"select2 small"))}} </div>
</div>