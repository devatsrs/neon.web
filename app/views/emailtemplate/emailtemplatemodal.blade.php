<?php $emailfrom  = array(); if(isset($email_from)){$emailfrom = $email_from;}else{$emailfrom = TicketGroups::GetGroupsFrom();} ?>
<script>
    $(document).ready(function ($) {

		$('#add-new-template-form').submit(function(e){
            e.preventDefault();
            var templateID = $("#add-new-template-form [name='TemplateID']").val();
            if( typeof templateID != 'undefined' && templateID != ''){
                update_new_url = baseurl + '/email_template/'+templateID+'/update';
            }else{
                update_new_url = baseurl + '/email_template/store';
            }
            var formdata = new FormData(($('#add-new-template-form')[0]));
            formdata.append('LanguageID', $("#add-new-template-form [name='LanguageID']").val());
            formdata.append('SystemType', $("#add-new-template-form [name='SystemType']").val());

            showAjaxScript(update_new_url, formdata, function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-template').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    $('select[data-type="email_template"]').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.TemplateName, response.newcreated.TemplateID, true, true);
                        }else{
                            var newState = new Option(response.newcreated.TemplateName, response.newcreated.TemplateID, false, false);
                        }
                        $(el).append(newState).trigger('change');
                        $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                            return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                        }));
                    });
                    $('#template_filter').submit();
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });

        });

        $('#add-new-modal-template').on('shown.bs.modal', function(event){
			var modal = $(this);  
			if(typeof popup_type  == "undefined" ) {popup_type = 0;}
			if(popup_type == {{EmailTemplate::ACCOUNT_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"leadoptions":true});

			}else if(popup_type == {{EmailTemplate::INVOICE_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"invoiceoptions":true});

            }else if(popup_type == {{EmailTemplate::AUTO_PAYMENT_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"autopaymentoptions":true});

			} else if(popup_type == {{EmailTemplate::RATESHEET_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"ratetemplateoptions":true});

			} else if(popup_type == {{EmailTemplate::TICKET_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"TicketsSingle":true});

			}  else if(popup_type == {{EmailTemplate::ESTIMATE_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"estimateoptions":true});

			} else if(popup_type == {{EmailTemplate::CONTACT_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"leadoptions":true});

			} else if(popup_type == {{EmailTemplate::CRONJOB_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"Cronjobs":true});

			}
			else if(popup_type == {{EmailTemplate::TASK_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"tasks":true});

			}
			else if(popup_type == {{EmailTemplate::OPPORTUNITY_TEMPLATE}}){

				show_summernote(modal.find('.message'),{"opportunities":true});

			}
			else{

				show_summernote(modal.find('.message'),{"leadoptions":true});

			}
        });

        $('#add-new-modal-template').on('hidden.bs.modal', function(event){
            var modal = $(this);
            modal.find('.message').show();
			popup_type = 0;
		$("#add-new-template-form #email_from").val('').trigger('change');
        });
		
		$('.template_type').change(function(e) {
		     var template_type_val_change =  $(this).val();
			 console.log("old:"+template_type_val);
			console.log("new:"+template_type_val_change);		
			 var modal_change = $('#add-new-modal-template');
			if(template_type_val_change){
				if(template_type_val_change == {{EmailTemplate::TICKET_TEMPLATE}})
				{		console.log("Ticket");

					show_summernote(modal_change.find('.message'),{"Tickets":true});

				$(".TicketsScroll").perfectScrollbar();		 
				}else{ console.log("others");				
					if(template_type_val == {{EmailTemplate::TICKET_TEMPLATE}})
					{

						console.log("others added");
						show_summernote(modal_change.find('.message'),{"Tickets":true});


					}
				}
				
			}
			template_type_val = template_type_val_change; 
        });
		
	 $('#add-new-modal-template').on('hidden.bs.modal', function(event){				 	
				var modal = $(this);
				modal.find('.email_from').addClass('hidden');
				modal.find('#TemplateName').removeAttr('readonly');
				modal.find('#SystemType').select2('enable', true);
				modal.find('[name=LanguageID]').select2('enable', true);
	  });
		
    });
</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-template">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="add-new-template-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Template</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group">
                                <label for="field-1" class="control-label col-sm-2">Language</label>
                                <div class="col-sm-4">
                                    {{ddl_language("", "LanguageID", Translation::$default_lang_id,"", "id", "yes")}}
                                </div>

                                <label for="field-1" class="control-label col-sm-2">Type</label>
                                <div class="col-sm-4">
                                    {{ Form::select('SystemType', EmailTemplate::getSystemTypeArray(), '', array("class"=>"select2", "id"=>"SystemType")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <br />
                                <label for="field-1" class="control-label col-sm-2">Template Name</label>
                                <div class="col-sm-4">
                                    <input type="text" name="TemplateName" class="form-control" id="TemplateName" placeholder="">
                                    <input type="hidden" name="TemplateID" />
                                </div>
                            </div>
                        </div>
                       <!-- <div class="row">
                            <div class="form-group">
                                <br />
                                <label for="field-1" class="control-label col-sm-2">Template Type</label>
                                <div class="col-sm-4">
                                    {{Form::select('Type',$type,'',array("class"=>"select2 template_type small"))}}
                                </div>
                            </div>
                        </div>-->
                        <div class="row hidden email_from">
                            <div class="form-group">
                                <br />
                                <label for="email_from" class="control-label col-sm-2">From</label>
                                <div class="col-sm-4">
                                  {{Form::select('email_from',$emailfrom,'',array("class"=>"select2","id"=>"email_from"))}} 
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <br />
                                <label for="field-2" class="control-label col-sm-2">Subject</label>
                                <div class="col-sm-8">
                                    <input type="text" name="Subject" class="form-control" id="field-2" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-Group">
                                    <br />
                                    <label for="field-3" class="control-label">Email Template Body</label>
                                    <textarea class="form-control message" rows="18" id="field-3" name="TemplateBody"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br/>
                                <label class="col-sm-2 control-label">Email Template Privacy</label>
                                <div class="col-sm-4">
                                    {{Form::select('Email_template_privacy',$privacy,'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br/>
                                <label class="col-sm-2 control-label">Status</label>
                                <div class="col-sm-4">
                                 <p class="status_switch make-switch switch-small">
                                   <input type="checkbox" checked=""  name="Status" id="StatusEditAdd" value="1">
                                   </p>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
 					<input type="hidden"   name="Type" value="0">
                        <button type="submit" id="template-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
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