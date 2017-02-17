<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-4" class="control-label">From</label>
      {{Form::select('email_from',TicketGroups::GetGroupsFrom(),$from,array("class"=>"select22","style"=>"display:block;"))}} </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-4" class="control-label">To</label>
      {{Form::text('Email',$Account->BillingEmail,array("class"=>"form-control"))}} </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-4" class="control-label">Subject</label>
      {{Form::text('Subject',$Subject,array("class"=>" form-control"))}} </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-4" class="control-label">Message</label>
      {{Form::textarea('Message',$Message,array("class"=>"form-control","id"=>"InvoiceMessage","rows"=>8 ))}} <br>      
      <span style="display:none;">
      <a target="_blank" href="{{URL::to('/invoice/'.$Invoice->InvoiceID.'/invoice_preview')}}">View Invoice</a> <br>
       <br>
      <br>
      Best Regards,<br>
      <br>
      {{$CompanyName}}</span> </div>
  </div>
</div>
{{Form::hidden('InvoiceID',$Invoice->InvoiceID)}}
<script>
jQuery(document).ready(function ($) {
	 $("#send-modal-invoice").find(".select22").select2();
	 
	  $('#InvoiceMessage').wysihtml5({
				   "font-styles": true,				
				  	"leadoptions":false,	
				    "invoiceoptions":false,	
				    "estimateoptions":false,
					"TicketsSingle":false,					
				    "Tickets":false,
					"Crm":false,			
					"emphasis": true,
					"lists": true,
					"html": true,
					"link": true,
					"image": true,
					"color": false,
				parser: function(html) {
		        	return html;
    			}
		});	 
});
</script>