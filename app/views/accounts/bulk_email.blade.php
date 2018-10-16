<?php   $templateoption = ['' => 'Select', 1 => 'Create new', 2 => 'Update existing']; ?>

<div class="modal fade" id="modal-BulkMail">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="BulkMail-form" method="post" action="" enctype="multipart/form-data">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Bulk Send Email</h4>
        </div>
        <div class="modal-body"> @if(isset($trunks) && (isset($bulk_type) && $bulk_type == 'accounts'))
          <div class="row CD">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-1" class="col-sm-2 control-label">Trunk</label>
                @foreach ($trunks as $index=>$trunk)
                @if(!empty($trunk) && !empty($index))
                <div class="col-sm-2">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="Trunks[]" value="{{$index}}" >
                      {{$trunk}} </label>
                  </div>
                </div>
                @endif
                @endforeach </div>
            </div>
          </div>
          <div class="row CD">
            <div class="col-md-12">
              <div class="form-group"> <br />
                <label for="field-1" class="control-label">Merge Output file By Trunk</label>
                <div class="make-switch switch-small" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>" data-animated="false">
                  <input type="hidden" name="isMerge" value="0">
                  <input type="checkbox" name="isMerge" value="1">
                  <input type="hidden" name="sendMail" value="1">
                  <input type="hidden" name="Format" value="{{RateSheetFormate::RATESHEET_FORMAT_RATESHEET}}">
                  <input type="hidden" name="Type" value="1">
                </div>
              </div>
            </div>
          </div>
          @endif
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-1" class="control-label">Show Template</label>
                {{Form::select('email_template_privacy',EmailTemplate::$privacy,'',array("class"=>"select2 small"))}} </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-3" class="control-label">Email Template</label>
                {{Form::select('email_template',$emailTemplates,'',array("class"=>"select2 small"))}} </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-4" class="control-label">From</label>
                {{Form::select('email_from',TicketGroups::GetGroupsFrom(),'',array("class"=>"select2"))}} </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-4" class="control-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" />
                @if(isset($bulk_type) && $bulk_type == 'accounts')
                <input type="hidden" name="SelectedIDs" />
                <input type="hidden" name="criteria" />
                <input type="hidden" name="Type" value="{{EmailTemplate::ACCOUNT_TEMPLATE}}" />
                <input type="hidden" name="type" value="BAE" />
                <input type="hidden" name="ratesheetmail" value="0" />
                <input type="hidden" name="test" value="0" />
                <input type="hidden" name="testEmail" value="" />
                @elseif(isset($bulk_type) && $bulk_type == 'invoices')
                <input type="hidden" name="SelectedIDs"/>
                <input type="hidden" name="criteria"/>
                <input type="hidden" name="Type" value="{{EmailTemplate::INVOICE_TEMPLATE}}"/>
                <input type="hidden" name="type" value="IR"/>
                <input type="hidden" name="test" value="0"/>
                <input type="hidden" name="testEmail" value=""/>
                <input type="hidden" name="email_template_privacy" value="0">
                @elseif(isset($bulk_type) && $bulk_type == 'disputes')
                  <input type="hidden" name="SelectedIDs"/>
                  <input type="hidden" name="criteria"/>
                  <input type="hidden" name="Type" value="0"/>
                  <input type="hidden" name="type" value="DR"/>
                  <input type="hidden" name="test" value="0"/>
                  <input type="hidden" name="testEmail" value=""/>
                  <input type="hidden" name="email_template_privacy" value="0">
                @elseif(isset($bulk_type) && $bulk_type == 'customers_rates')
                <input type="hidden" name="SelectedIDs" />
                <input type="hidden" name="test" value="0" />
                <input type="hidden" name="testEmail" value="" />
                @endif </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Message</label>
                <textarea class="form-control message" rows="18" name="message"></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-6" class="control-label">Attchament</label>
                <input type="file" id="attachment"  name="attachment" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-7" class="control-label">Template Option</label>
                {{Form::select('template_option',$templateoption,'',array("class"=>"select2 small"))}} </div>
            </div>
            <div id="templatename" class="col-md-6 hidden">
              <div class="form-group">
                <label for="field-7" class="control-label">New Template Name</label>
                <input type="text" name="template_name" class="form-control" id="field-5" placeholder="">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button  type="submit" id="mail-send"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Send </button>
          <button id="test"  class="savetest btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Send Test mail </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="modal-TestMail">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="TestMail-form" method="post" action="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Test Mail Options</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="form-Group"> <br />
              <label for="field-1" class="col-sm-3 control-label">Email Address</label>
              <div class="col-sm-4">
                <input type="text" class="form-control" name="EmailAddress" />
              </div>
            </div>
          </div>
          @if(isset($bulk_type) && $bulk_type == 'accounts')
          <div class="row">
            <div class="form-Group"> <br />
              <label for="field-1" class="col-sm-3 control-label">Sample Account</label>
              <div class="col-sm-4"> {{Form::select('accountID',$accounts,'',array("class"=>"select2 small"))}} </div>
            </div>
          </div>
          @endif </div>
        <div class="modal-footer">
          <button type="submit"  class=" @if(isset($bulk_type) && ($bulk_type == 'customers_rates' || $bulk_type == 'accounts')) lead @elseif(isset($bulk_type) &&  ($bulk_type == 'invoices' || $bulk_type == 'disputes')) alerta @endif btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Send </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<style>
.alert{
margin-bottom:inherit !important;
padding:inherit !important;
}
</style>