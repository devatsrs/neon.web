@extends('layout.main')

@section('content')
	<ol class="breadcrumb bc-3">
		<li>
			<a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
		</li>
		<li class="active">
			<a href="{{URL::to('invoice_template')}}">  Invoice Template</a>
		</li>
		<li>
			<a><span>{{invoicetemplate_dropbox($InvoiceTemplate->InvoiceTemplateID)}}</span></a>
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
			<h3>Report Heading</h3>

			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<div class="col-md-2">&nbsp;</div>
						<div class="col-md-2"><h4><strong>Heading</strong></h4></div>
						<div class="col-md-2">&nbsp;</div>
						<div class="col-md-2"><h4><strong>Customize Name</strong></h4></div>
						<div class="col-md-2">&nbsp;</div>
						<div class="col-md-2"><h4><strong></strong></h4></div>
					</div>
				</div>
			</div>
			<!-- start -->
			<ul class="sortable-list sortable-list-choices   field_choices_ui board-column-list list-unstyled ui-sortable margin-top report_manager" data-name="closedwon">
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
												<input type="checkbox" value="1" name="Status"  @if($valuesData['Status'] == 1 )checked=""@endif autocomplete="off">
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
		</div>
		<input type="hidden" name="reportchoicesdata" id="reportchoicesdata" value="">
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

			$('#choices_item .report_manager').sortable({
				connectWith: '.sortable-list-choices',
				placeholder: 'placeholder',
				start: function() {
					currentDrageable = $('#choices_item ul.report_manager li.dragging');
				},
				stop: function(ev,ui) {
					saveOrderchoices();
					currentDrageable = '';
				}
			});

			$('#invoice_template-save').on("click",function(e){
				$("#edit-usagefields-form").submit();
			});

			$("#edit-usagefields-form").on("submit",function(e){
				e.stopPropagation();
				e.preventDefault();
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
				var choices_array   = 	new Array();
				var check_count = 0;
				var choices_order   = 	$('#choices_item ul.report_manager li').each(function(index, element) {
					var attributeArray  =  {};
					$(element).find('input').each(function(index, element) {
						var name = $(element).attr('name');
						var attributetype = $(element).attr('type');
						if(attributetype =='checkbox'){
							if($(element).prop("checked")){
								check_count++;
							}
							attributeArray[name] = $(element).prop("checked");
						}else{
							attributeArray[name] = $(element).val();
						}
					});
					attributeArray["FieldOrder"] = index+1;
					choices_array.push(attributeArray);

				});
				if(check_count>0) {
					$('#reportchoicesdata').val(JSON.stringify(choices_array));
				}else{
					$('#reportchoicesdata').val('');
				}

			}

			$('#invoice_template-print').click(function() {
				document.getElementById("invoice_iframe").contentDocument.location.reload(true);
				$('#print-modal-invoice_template').modal('show');
			});

			$('#drp_invoicetemplate_jump').on('change',function(){
				var val = $(this).val();
				if(val!="") {
					var InvoiceTemplateID = '{{$InvoiceTemplate->InvoiceTemplateID}}';
					var url ='/invoice_template/'+ val + '/view?Type=2';
					window.location.href = baseurl + url;
				}
			});

		});
	</script>
	<style>
		#drp_invoicetemplate_jump{
			border: 0px solid #fff;
			background-color: rgba(255,255,255,0);
			padding: 0px;
		}
		#drp_invoicetemplate_jump option{
			-webkit-appearance: none;
			-moz-appearance: none;
			border: 0px;
		}

	</style>
@stop
@section('footer_ext')
	@parent
	<div class="modal fade custom-width" id="print-modal-invoice_template">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form id="add-new-invoice_template-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">                     <a href="{{URL::to('invoice_template/'.$InvoiceTemplate->InvoiceTemplateID.'/pdf_download?Type='.Input::get('Type'))}}" type="button" class="btn btn-primary print btn-sm btn-icon icon-left" >
								<i class="entypo-print"></i>
								Print
							</a>
						</h4>
					</div>
					<div class="modal-body">

						<iframe  id="invoice_iframe"   frameborder="0" scrolling="no" style="position: relative; height: 1050px; width: 100%;overflow-y: auto; overflow-x: hidden;" width="100%" height="100%" src="{{ URL::to('/invoice_template/'.$InvoiceTemplate->InvoiceTemplateID .'/print?Type='.Input::get('Type')); }}"></iframe>

					</div>
					<div class="modal-footer">
						<a href="{{URL::to('invoice_template/'.$InvoiceTemplate->InvoiceTemplateID.'/pdf_download?Type='.Input::get('Type'))}}" type="button" class="btn btn-primary print btn-sm btn-icon icon-left" >
							<i class="entypo-print"></i>
							Print
						</a>
						<button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
							<i class="entypo-cancel"></i>
							Close
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@stop