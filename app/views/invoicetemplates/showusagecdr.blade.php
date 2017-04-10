@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <a href="{{URL::to('invoice_template')}}">  Invoice Template</a>
    </li>
    <li class="active">
        <strong>Edit {{$InvoiceTemplate->Name}}</strong>
    </li>
</ol>
<h3>Edit {{$InvoiceTemplate->Name}}</h3>

@include('includes.errors')
@include('includes.success')
<p style="text-align: right;">
    <a href="{{URL::to('/invoice_template')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
    @if(User::checkCategoryPermission('InvoiceTemplates','Edit') )
    <button type="submit" id="invoice_template-save"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>
    @endif
	<!--
    <a  href="Javascript:void(0);" id="invoice_template-print"  class="btn btn-danger btn-sm btn-icon icon-left" >
        <i class="entypo-print"></i>
        Preview Template
    </a>-->

</p>
<br>

<form id="edit-usagefields-form" method="post">

	<div id="choices_item" class="choices_item margin-top">
	
	<!-- Detail CDR start -->
			<h3>Detail CDR</h3>
	
			<div class="row">
			  <div class="col-md-12">
				<div class="form-group">
				  <div class="col-md-2">&nbsp;</div>
				  <div class="col-md-2"><h4><strong>Column Name</strong></h4></div>
				  <div class="col-md-2">&nbsp;</div>
				  <div class="col-md-2"><h4><strong>Customize Name</strong></h4></div>
				  <div class="col-md-2">&nbsp;</div>
				  <div class="col-md-2"><h4><strong>Active/DeActive</strong></h4></div>
				</div>
			  </div>
			</div>
			<!-- start -->
			<ul class="sortable-list sortable-list-choices1   field_choices_ui board-column-list list-unstyled ui-sortable margin-top detailsummary" data-name="closedwon">
		  @foreach($detail_values as $key => $valuesData) 
		  @if(!empty($valuesData))  
		  <li class="tile-stats sortable-item count-cards choices_field_li choices_field_li_data_{{$valuesData['ValuesID']}}"   data-id="{{$valuesData['ValuesID']}}">
			<div class="row">
			  <div class="col-md-12">
				<div class="form-group">          
				  <div class="col-md-5">
					<input type="text" name="Title" class="form-control" readonly value="{{$valuesData['Title']}}">
					<input type="hidden"  name="ValuesID" class="form-control"  value="{{$valuesData['ValuesID']}}">
				  </div>
				  <div class="col-md-4">
					<input type="text" name="UsageName" class="form-control" value="{{$valuesData['UsageName']}}">
				  </div>
				  <div class="col-md-1">&nbsp;</div>
				  <div class="col-md-2">
					<div class="make-switch switch-small">
					  <input type="checkbox" value="1" name="Status"  @if($valuesData['Status'] == 1 )checked=""@endif>
					  </div>
				  </div>
				</div>
			  </div>
			</div>
		  </li>
		  @endif
		  @endforeach
		</ul>
		<!-- Detail CDR end -->

		<!--Summary	CDR Start -->
		
		<h3>Summary CDR</h3>
		
		<div class="row">
		  <div class="col-md-12">
			<div class="form-group">
			  <div class="col-md-2">&nbsp;</div>
			  <div class="col-md-2"><h4><strong>Column Name</strong></h4></div>
			  <div class="col-md-2">&nbsp;</div>
			  <div class="col-md-2"><h4><strong>Customize Name</strong></h4></div>
			  <div class="col-md-2">&nbsp;</div>
			  <div class="col-md-2"><h4><strong>Active/DeActive</strong></h4></div>
			</div>
		  </div>
		</div>
		<!-- start -->
		<ul class="sortable-list sortable-list-choices   field_choices_ui board-column-list list-unstyled ui-sortable margin-top usagesummary" data-name="closedwon">
		  @foreach($summary_values as $key => $valuesData) 
		  @if(!empty($valuesData))  
		  <li class="tile-stats sortable-item count-cards choices_field_li choices_field_li_data_{{$valuesData['ValuesID']}}"   data-id="{{$valuesData['ValuesID']}}">
			<div class="row">
			  <div class="col-md-12">
				<div class="form-group">          
				  <div class="col-md-5">
					<input type="text" name="Title" class="form-control" readonly value="{{$valuesData['Title']}}">
					<input type="hidden"  name="ValuesID" class="form-control"  value="{{$valuesData['ValuesID']}}">
				  </div>
				  <div class="col-md-4">
					<input type="text" name="UsageName" class="form-control" value="{{$valuesData['UsageName']}}">
				  </div>
				  <div class="col-md-1">&nbsp;</div>
				  <div class="col-md-2">
					<div class="make-switch switch-small">
					  <input type="checkbox" value="1" name="Status"  @if($valuesData['Status'] == 1 )checked=""@endif>
					  </div>
				  </div>
				</div>
			  </div>
			</div>
		  </li>
		  @endif
		  @endforeach
		</ul>
		<!--Summary	CDR Start -->
	</div>
	<input type="hidden" name="summarychoices" id="summarychoicesdata" value="">
	<input type="hidden" name="detailchoices" id="detailchoicesdata" value="">
	<input type="hidden" name="InvoiceTemplateID" value="{{$InvoiceTemplate->InvoiceTemplateID}}">
</form>
	<style>
		#deals-dashboard .board-column{width:100%;}
		.count-cards{width:100% !important; min-width:100%; max-width:100%;}
		#deals-dashboard li:hover {cursor:all-scroll; }
		#choices_item .count-cards{min-height:50px;}
		#deals-dashboard .count-cards{min-height:70px;}
		.choices_field_li:hover {cursor:all-scroll; }
		.choices_field_li{margin-bottom:0px !important; }
		.count-cards .info{min-height:55px; padding:0 0 0 5px;}
		.field_model_behavoiur .col-md-6{padding-left:1px !important;}
		.field_model_behavoiur .col-md-6 .form-group{margin-top:5px;}
		.field_model_behavoiur .col-md-6 .form-group label h3{margin-top:3px;}
		.phpdebugbar{display:none;}
	</style>
	
	<script type="text/javascript">
	$(document).ready(function() {
		
		$('#choices_item .usagesummary').sortable({
                    connectWith: '.sortable-list-choices',
                    placeholder: 'placeholder',
                    start: function() {
                        //setting current draggable item
                        currentDrageable = $('#choices_item ul.usagesummary li.dragging');
                    },
                    stop: function(ev,ui) {
						saveOrderchoices();
                        //de-setting draggable item after submit order.
                        currentDrageable = '';
                    }
        });
		
		$('#choices_item .detailsummary').sortable({
                    connectWith: '.sortable-list-choices1',
                    placeholder: 'placeholder',
                    start: function() {
                        //setting current draggable item
                        currentDrageable = $('#choices_item ul.detailsummary li.dragging');
                    },
                    stop: function(ev,ui) {
						saveOrderchoices();
                        //de-setting draggable item after submit order.
                        currentDrageable = '';
                    }
        });
		
		//$( "#choices_item .usagesummary, #choices_item .detailsummary" ).disableSelection();
				
		$('#invoice_template-save').on("click",function(e){
			//alert('hi');
				$("#edit-usagefields-form").submit();
		});
				
		$("#edit-usagefields-form").on("submit",function(e){
				e.stopPropagation();
			    e.preventDefault();	
				
				//var field_type_submit = $(this).find('#field_type').val();	 	
				//saveOrderchoices(field_type_submit);
				saveOrderchoices();
				
				var url = baseurl + '/invoice_template/save_single_field';
				var formData = new FormData($(this)[0]);
				$('#invoice_template-save').button('loading');
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
						if(response.status =='success'){
							toastr.success(response.message, "Success", toastr_opts);
							//$('#add-modal-ticketfield').modal('hide');
							//location.reload();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
						$('#invoice_template-save').button('reset');
					},
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
					contentType: false,
                    processData: false              
                });						
				return false;
			});		
			
			function saveOrderchoices() {
				
                //var selectedCards 	= 	new Array();
				//var fldli 			= 	$("#deals-dashboard").find("li [field_type='" + type + "']");							
				
				var choices_array   = 	new Array();
				var choices_array1   = 	new Array();
				 //var choices_data	=   JSON.stringify( $('#edit-ticketfields-form').serializeArray() );
				
				var choices_order   = 	$('#choices_item ul.usagesummary li').each(function(index, element) {
					var attributeArray  =  {};
					
					$(element).find('input').each(function(index, element) {
						var name = $(element).attr('name');
						var attributetype = $(element).attr('type');
						if(attributetype =='checkbox'){
							attributeArray[name] = $(element).prop("checked");
						}else{						
                      	  attributeArray[name] = $(element).val();
						}
                    });
					 attributeArray["FieldOrder"] = index+1;  
                   	 choices_array.push(attributeArray); 					
                });
					
				//$(fldli).find('.row-hidden').find('[name="choices"]').val( JSON.stringify(choices_array));
				$('#summarychoicesdata').val(JSON.stringify(choices_array));
				
				var choices_order1   = 	$('#choices_item ul.detailsummary li').each(function(index, element) {
					var attributeArray  =  {};
					
					$(element).find('input').each(function(index, element) {
						var name = $(element).attr('name');
						var attributetype = $(element).attr('type');
						if(attributetype =='checkbox'){
							attributeArray[name] = $(element).prop("checked");
						}else{						
                      	  attributeArray[name] = $(element).val();
						}
                    });
					 attributeArray["FieldOrder"] = index+1;  
                   	 choices_array1.push(attributeArray); 					
                });
					 	 
				//$(fldli).find('.row-hidden').find('[name="choices"]').val( JSON.stringify(choices_array));
				$('#detailchoicesdata').val(JSON.stringify(choices_array1));
            }

    });
	</script>
@stop