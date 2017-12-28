<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Company*</label>
    <div class="col-sm-4">
        {{Form::select('selection[AccountName]', $columns,(isset($attrselection->AccountName)?$attrselection->AccountName:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Title</label>
    <div class="col-sm-4">
        {{Form::select('selection[NamePrefix]', $columns,(isset($attrselection->NamePrefix)?$attrselection->NamePrefix:''),array("class"=>"select2 small"))}}
        <input type="hidden" class="form-control" name="AccountType" value="0" />
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">First Name*</label>
    <div class="col-sm-4">
        {{Form::select('selection[FirstName]', $columns,(isset($attrselection->FirstName)?$attrselection->FirstName:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Last Name*</label>
    <div class="col-sm-4">
        {{Form::select('selection[LastName]', $columns,(isset($attrselection->LastName)?$attrselection->LastName:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Email</label>
    <div class="col-sm-4">
        {{Form::select('selection[Email]', $columns,(isset($attrselection->Email)?$attrselection->Email:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Phone</label>
    <div class="col-sm-4">
        {{Form::select('selection[Phone]', $columns,(isset($attrselection->Phone)?$attrselection->Phone:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Mobile</label>
    <div class="col-sm-4">
        {{Form::select('selection[Mobile]', $columns,(isset($attrselection->Mobile)?$attrselection->Mobile:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Fax</label>
    <div class="col-sm-4">
        {{Form::select('selection[Fax]', $columns,(isset($attrselection->Fax)?$attrselection->Fax:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Address1</label>
    <div class="col-sm-4">
        {{Form::select('selection[Address1]', $columns,(isset($attrselection->Address1)?$attrselection->Address1:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Address2</label>
    <div class="col-sm-4">
        {{Form::select('selection[Address2]', $columns,(isset($attrselection->Address2)?$attrselection->Address2:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Address3</label>
    <div class="col-sm-4">
        {{Form::select('selection[Address3]', $columns,(isset($attrselection->Address3)?$attrselection->Address3:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">City</label>
    <div class="col-sm-4">
        {{Form::select('selection[City]', $columns,(isset($attrselection->City)?$attrselection->City:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Post Code</label>
    <div class="col-sm-4">
        {{Form::select('selection[Pincode]', $columns,(isset($attrselection->Pincode)?$attrselection->Pincode:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Country</label>
    <div class="col-sm-4">
        {{Form::select('selection[Country]', $columns,(isset($attrselection->Country)?$attrselection->Country:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Website</label>
    <div class="col-sm-4">
        {{Form::select('selection[Website]', $columns,(isset($attrselection->Website)?$attrselection->Website:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Employee</label>
    <div class="col-sm-4">
        {{Form::select('selection[Employee]', $columns,(isset($attrselection->Employee)?$attrselection->Employee:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Skype</label>
    <div class="col-sm-4">
        {{Form::select('selection[Skype]', $columns,(isset($attrselection->Skype)?$attrselection->Skype:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Twitter</label>
    <div class="col-sm-4">
        {{Form::select('selection[Twitter]', $columns,(isset($attrselection->Twitter)?$attrselection->Twitter:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Description</label>
    <div class="col-sm-4">
        {{Form::select('selection[Description]', $columns,(isset($attrselection->Description)?$attrselection->Description:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">VatNumber</label>
    <div class="col-sm-4">
        {{Form::select('selection[VatNumber]', $columns,(isset($attrselection->VatNumber)?$attrselection->VatNumber:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Tags</label>
    <div class="col-sm-4">
        {{Form::select('selection[tags]', $columns,(isset($attrselection->tags)?$attrselection->tags:''),array("class"=>"select2 small"))}}
    </div>
</div>