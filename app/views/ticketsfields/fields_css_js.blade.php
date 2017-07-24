<script>
	window.odometerOptions = {
  format: '(ddd).dd'
};
        var $searchFilter = {};
        var currentDrageable = '';
        var fixedHeader = false;
        $(document).ready(function ($) {
			
            var ticketfields = [
             'id', 
            'type', 
            'name', 
            'label',
            'dom_type', 
            'field_type',
            'label_in_portal', 
            'description', 
            'has_section',
            'position', 
            'active', 
            'required',
            'required_for_closure',
            'visible_in_portal',
            'editable_in_portal', 
            'required_in_portal', 
            'FieldStaticType', 
            'field_options', 
			'choices'
            ];
			
			getTicketsFields();			
       
            var readonly = ['Company','Phone','Email','Title','FirstName','LastName','Worth'];
            var board = $('#board-start');

            board.perfectScrollbar({minScrollbarLength: 20,handlers: ['click-rail','drag-scrollbar', 'keyboard', 'wheel', 'touch']});
            board.on('mouseenter',function(){
                board.perfectScrollbar('update');
            });

            $( window ).resize(function() {
                board.perfectScrollbar('update');
            });
			
			$('.add_new_field').click(function(e) {
                var field_type_add 		= 	$(this).attr('field_type');
				var FieldDomType_add 	= 	$(this).attr('FieldDomType');				
				$('#add-modal-ticketfield #field_type').val(field_type_add);
				$('#add-modal-ticketfield #type').val(FieldDomType_add);
				var position_add = $('#deals-dashboard li').length;				
				var position_add = 1;
				var getClass =   $('#deals-dashboard li.count-cards');
				getClass.each(function () {position_add++;});
				$('#add-modal-ticketfield #position').val(position_add);
				$('#add-modal-ticketfield').modal('show');
				setTimeout(function(){$("#add-modal-ticketfield" ).find('#label').focus()}, 1000);
				
            });
			
			$(document).on("submit","#add-ticketfields-form",function(e){
				e.stopPropagation();
			    e.preventDefault();	
				var field_type_submit = $(this).find('#field_type').val();	 	
				
				var url = baseurl + '/ticketsfields/save_single_field';
				var formData = new FormData($(this)[0]);
				
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
						if(response.status =='success'){
							toastr.success(response.message, "Success", toastr_opts);
							$('#add-modal-ticketfield').modal('hide');
							location.reload();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
						$('#field_model_btn_add').button('reset');
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
			
			
			$(document).on("keyup","#add-modal-ticketfield #label",function(){
				$('#add-modal-ticketfield #label_in_portal').val($(this).val());				
			});
			

     $(document).on('click','#board-start ul.sortable-list li button.edit-deal',function(e){
				
                e.stopPropagation();
                if($(this).is('a')){
                    var rowHidden = $(this).prev('div.hiddenRowData');
                }else {
                    var rowHidden = $(this).parents('.tile-stats').children('div.row-hidden');
                }
				var Currentfieldtype = $(this).parent().attr('field_type'); 
				var Currentlabel = rowHidden.find('input[name="label"]').val();
				var fieldtype = '';
                for(var i = 0 ; i< ticketfields.length; i++)
				{
                    var val = rowHidden.find('input[name="'+ticketfields[i]+'"]').val();					
                    var elem = $('#edit-ticketfields-form [name="'+ticketfields[i]+'"]');
					elem.val(val);
					if(ticketfields[i]=='FieldStaticType')
					{       
						if(val=={{Ticketfields::FIELD_TYPE_STATIC}}){ 
							$('#edit-modal-ticketfield').find('#label').attr('readonly','readonly');
						}else{
							$('#edit-modal-ticketfield').find('#label').removeAttr('readonly');
						}
					}
					else if(ticketfields[i]=='required')
					{	
						biuldSwicth2('#AgentReqSubmitSwitch','required','#edit-ticketfields-form',val);						
					}
					
					else if(ticketfields[i]=='visible_in_portal')
					{
						biuldSwicth2('#CustomerDisplaySwitch','visible_in_portal','#edit-ticketfields-form',val);	
					}
					
					else if(ticketfields[i]=='required_for_closure')
					{
						biuldSwicth2('#AgentReqCloseSwitch','required_for_closure','#edit-ticketfields-form',val);	
					}
					else if(ticketfields[i]=='type')
					{
						fieldtype = val;
					}
					
					
					else if(ticketfields[i]=='editable_in_portal')
					{
						biuldSwicth2('#CustomerEditSwitch','editable_in_portal','#edit-ticketfields-form',val);	
					}
					
					else if(ticketfields[i]=='required_in_portal')
					{
						biuldSwicth2('#CustomerReqSubmitSwitch','required_in_portal','#edit-ticketfields-form',val);	
					}
					else if(ticketfields[i]=='choices')
					{
						//if(Currentfieldtype=='default_ticket_type' || Currentfieldtype=='default_status')
						if(fieldtype=='dropdown')
						{
							if(Currentfieldtype!='default_agent' && Currentfieldtype!='default_group' && Currentfieldtype!='default_priority' ){
								call_field_choices(Currentfieldtype,val);
							}
						}						
					}
					
                   
                }
                $('#edit-modal-ticketfield h3').text('Edit '+Currentlabel+' Field');
                $('#edit-modal-ticketfield').modal('show');
				
				var checked_visible_in_portal = $('#edit-modal-ticketfield #visible_in_portal').prop("checked");
				//alert(checked_visible_in_portal);
				if(!checked_visible_in_portal){
					$('#edit-modal-ticketfield #CustomerEditSwitch .make-switch').bootstrapSwitch('setState', false); 
					$('#edit-modal-ticketfield #CustomerReqSubmitSwitch .make-switch').bootstrapSwitch('setState', false); 
					
					$('#edit-modal-ticketfield #editable_in_portal').attr('disabled','disabled');
					$('#edit-modal-ticketfield #editable_in_portal').parent().parent().addClass('deactivate');
					
					$('#edit-modal-ticketfield #required_in_portal').attr('disabled','disabled');
					$('#edit-modal-ticketfield #required_in_portal').parent().parent().addClass('deactivate');
					
				}else{
					$('#edit-modal-ticketfield #editable_in_portal').removeAttr('disabled');
					$('#edit-modal-ticketfield #editable_in_portal').parent().parent().removeClass('deactivate');
					
					$('#edit-modal-ticketfield #required_in_portal').removeAttr('disabled');
					$('#edit-modal-ticketfield #required_in_portal').parent().parent().removeClass('deactivate');			
				}
				
				
				empty_popup_default();
            });
			
			function empty_popup_default()
			{
				$('#edit-modal-ticketfield #choices_item').html('');
				$('#edit-modal-ticketfield #deleted_choices').val('');
				$('#edit-modal-ticketfield #modalfieldchoicesdata').val('');			
			}
			
			 $('#edit-modal-ticketfield').on('hidden.bs.modal', function(event){		
					 empty_popup_default();
              });		
			
			function call_field_choices(type,values){
				
			    var url = baseurl + '/ticketsfields/ajax_ticketsfields_choices';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) { 
						$('#choices_item').html(response);
						initSortableChoices(type);
                    },
                    // Form data
                    data: {type:type,values:values},
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false                   
                });
            
			}
            
			
            $('#tools .toggle').click(function(){
                        if($(this).hasClass('list')){
                            $(this).addClass('active');
                            $(this).siblings('.toggle').removeClass('active');
                            $('#board-start').addClass('hidden');
                            $('#opportunityGrid_wrapper,#opportunityGrid').removeClass('hidden');
                        }else{
                            $(this).addClass('active');
                            $(this).siblings('.toggle').removeClass('active');
                            $('#board-start').removeClass('hidden');
                            $('#opportunityGrid_wrapper,#opportunityGrid').addClass('hidden');
                        }
                    });

      
            $(document).on('mouseover','#attachments a',
                    function(){
                        var a = $(this).attr('alt');
                        $(this).html(a);
                    }
            );

            $(document).on('mouseout','#attachments a',function(){
                var a = $(this).attr('alt');
                if(a.length>8){
                    a  = a.substring(0,8)+"..";
                }
                $(this).html(a);
            });
		
		
			$(document).on("click",".delete_main_field",function(ee){
				var confirm_choice_delete = confirm("Are you sure to delete?");
				if(confirm_choice_delete){
					//deleted_choices
					var choices_id 			= $(this).attr('delete_main_field_id'); 
					 $(this).parent().remove();
					 
					var current_del =  $('#TicketfieldsDataFrom').find('#deleted_main_fields').val(); 
					if(current_del==''){
						$('#TicketfieldsDataFrom').find('#deleted_main_fields').val(choices_id);
					}else{
						$('#TicketfieldsDataFrom').find('#deleted_main_fields').val(current_del+','+choices_id);
					}
					saveOrder();
				}			
			});
			
			$(document).on("click",".feild_choice_delete",function(ee){
				var confirm_choice_delete = confirm("Are you sure to delete?");
				if(confirm_choice_delete){
					//deleted_choices
					var choices_id 			= $(this).attr('del_data_id');
					
					var current_del =  $('#edit-modal-ticketfield').find('#deleted_choices').val();
					if(current_del==''){
						$('#edit-modal-ticketfield').find('#deleted_choices').val(choices_id);
					}else{
						$('#edit-modal-ticketfield').find('#deleted_choices').val(current_del+','+choices_id);
					}
					var choice_field_type 	= $(this).attr('field_type'); 
					 $(this).parent().parent().parent().parent().parent().remove();
					saveOrderchoices(choice_field_type);
				}			
			});

            function initEnhancement(){
                board.find('.board-column-list').perfectScrollbar({minScrollbarLength: 20,handlers: ['click-rail','drag-scrollbar', 'keyboard', 'wheel', 'touch']});
                board.find('.board-column-list').on('mouseenter',function(){
                    $(this).perfectScrollbar('update');
                });

                $( window ).resize(function() {
                    board.find('.board-column-list').perfectScrollbar('update');
                });
            }
            function initSortable(){
                // Code using $ as usual goes here.
                $('#board-start .sortable-list').sortable({
                    connectWith: '.sortable-list',
                    placeholder: 'placeholder',
                    start: function() {
                        //setting current draggable item
                        currentDrageable = $('#board-start ul.sortable-list li.dragging');
                    },
                    stop: function(ev,ui) {
                        saveOrder();
                        //de-setting draggable item after submit order.
                        currentDrageable = '';
                    }
                });
            }
			
			function initSortableChoices(type){
                // Code using $ as usual goes here.
                $('#choices_item .sortable-list').sortable({
                    connectWith: '.sortable-list-choices',
                    placeholder: 'placeholder',
                    start: function() {
                        //setting current draggable item
                        currentDrageable = $('#choices_item ul.sortable-list li.dragging');
                    },
                    stop: function(ev,ui) {
						saveOrderchoices(type);
                        //de-setting draggable item after submit order.
                        currentDrageable = '';
                    }
                });
            }

            function initToolTip(){
                $('[data-toggle="tooltip"]').each(function(i, el)
                {
                    var $this = $(el),
                            placement = attrDefault($this, 'placement', 'top'),
                            trigger = attrDefault($this, 'trigger', 'hover'),
                            popover_class = $this.hasClass('tooltip-secondary') ? 'tooltip-secondary' : ($this.hasClass('tooltip-primary') ? 'tooltip-primary' : ($this.hasClass('tooltip-default') ? 'tooltip-default' : ''));

                    $this.tooltip({
                        placement: placement,
                        trigger: trigger
                    });
                    $this.on('shown.bs.tooltip', function(ev)
                    {
                        var $tooltip = $this.next();

                        $tooltip.addClass(popover_class);
                    });
                });
            }

            function autosizeUpdate(){
                $('.autogrow').trigger('autosize.resize');
            }

            function biuldSwicth(container,formID,checked){
                var make = '<span class="make-switch switch-small">';
                make += '<input name="opportunityClosed" value="{{Opportunity::Close}}" '+checked+' type="checkbox">';
                make +='</span>';

                var container = $(formID).find(container);
                container.empty();
                container.html(make);
                container.find('.make-switch').bootstrapSwitch();
            }
			
			function biuldSwicth2(container,name,formID,checked){
				checkedstr= '';
				if(checked==1){
					checkedstr= 'checked';
				}
				
				var make  = '<p class="make-switch  switch-small">';
                make	 += '<input id="'+name+'" name="'+name+'" type="checkbox" '+checkedstr+'  value="1">';
                make 	 += '</p>';
				
				/*var make = '<span class="make-switch switch-small">';
				make += '<input name="'+name+'" id="'+name+'" value="checked" '+checked+' type="checkbox">';
				make +='</span>';*/
	
				var container = $(formID).find(container);
				container.empty();
				container.html(make);
				container.find('.make-switch').bootstrapSwitch();
			} 
			
			$(document).on("change","#add-modal-ticketfield #visible_in_portal",function(){
				var checked_visible_in_portal = $(this).prop("checked");
				//alert(checked_visible_in_portal);
				if(!checked_visible_in_portal){
					$('#add-modal-ticketfield #CustomerEditSwitch .make-switch').bootstrapSwitch('setState', false); 
					$('#add-modal-ticketfield #CustomerReqSubmitSwitch .make-switch').bootstrapSwitch('setState', false); 
					
					$('#add-modal-ticketfield #editable_in_portal').attr('disabled','disabled');
					$('#add-modal-ticketfield #editable_in_portal').parent().parent().addClass('deactivate');
					
					$('#add-modal-ticketfield #required_in_portal').attr('disabled','disabled');
					$('#add-modal-ticketfield #required_in_portal').parent().parent().addClass('deactivate');
					
				}else{
					$('#add-modal-ticketfield #editable_in_portal').removeAttr('disabled');
					$('#add-modal-ticketfield #editable_in_portal').parent().parent().removeClass('deactivate');
					
					$('#add-modal-ticketfield #required_in_portal').removeAttr('disabled');
					$('#add-modal-ticketfield #required_in_portal').parent().parent().removeClass('deactivate');			
				}
				
				});


			$(document).on("change","#edit-modal-ticketfield #visible_in_portal",function(){
				var checked_visible_in_portal = $(this).prop("checked");
				//alert(checked_visible_in_portal);
				if(!checked_visible_in_portal){
					$('#edit-modal-ticketfield #CustomerEditSwitch .make-switch').bootstrapSwitch('setState', false); 
					$('#edit-modal-ticketfield #CustomerReqSubmitSwitch .make-switch').bootstrapSwitch('setState', false); 
					
					$('#edit-modal-ticketfield #editable_in_portal').attr('disabled','disabled');
					$('#edit-modal-ticketfield #editable_in_portal').parent().parent().addClass('deactivate');
					
					$('#edit-modal-ticketfield #required_in_portal').attr('disabled','disabled');
					$('#edit-modal-ticketfield #required_in_portal').parent().parent().addClass('deactivate');
					
				}else{
					$('#edit-modal-ticketfield #editable_in_portal').removeAttr('disabled');
					$('#edit-modal-ticketfield #editable_in_portal').parent().parent().removeClass('deactivate');
					
					$('#edit-modal-ticketfield #required_in_portal').removeAttr('disabled');
					$('#edit-modal-ticketfield #required_in_portal').parent().parent().removeClass('deactivate');			
				}
				
				});


            function getTicketsFields(){
                var url = baseurl + '/ticketsfields/ajax_ticketsfields';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) { 
                        board.html(response);
                        initEnhancement();
                        initSortable();
                        initToolTip();
                    },
                    // Form data
                    data: {s:1},
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function getComments(){
                $('#comment_processing').removeClass('hidden');
                var opportunityID = $('#add-opportunity-comments-form [name="OpportunityID"]').val();
                var url = baseurl +'/opportunitycomments/'+opportunityID+'/ajax_opportunitycomments';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#comment_processing').addClass('hidden');
                        if(response.status){
                            toastr.error(response.message, "Error", toastr_opts);
                        }else {
                            $('#allComments').html(response);
                            $('#allComments .perfect-scrollbar').perfectScrollbar({minScrollbarLength: 20,handlers: ['click-rail','drag-scrollbar', 'keyboard', 'wheel', 'touch']});
                            $('#allComments .perfect-scrollbar').on('mouseenter',function(){
                                $(this).perfectScrollbar('update');
                            });
                        }
                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function getOpportunityAttachment(){
                var opportunityID = $('#add-opportunity-comments-form [name="OpportunityID"]').val();
                var url = baseurl +'/opportunity/'+opportunityID+'/ajax_getattachments';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#attachments').html(response);
                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function fillColumns(){
                var url = baseurl + '/opportunityboardcolumn/'+BoardID+'/ajax_datacolumn';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $('#deals-dashboard').empty();
                        $(response).each(function(i,item){
                            $('#deals-dashboard').append(builditem(item));
                            initdrageable();
                        });
                    },
                    // Form data
                    //data: {},
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function postorder(elem){
                saveOrder(elem);
                url = baseurl + '/opportunity/'+BoardID+'/updateColumnOrder';
                var formData = new FormData($('#cardorder')[0]);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            getTicketsFields();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                            fillColumns();
                        }
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }
			
			function postorderChoices(){
			    saveOrderchoices(elem);
                url = baseurl + '/opportunity/'+BoardID+'/updateColumnOrder';
                var formData = new FormData($('#cardorder')[0]);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            getTicketsFields();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                            fillColumns();
                        }
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            
			}

            function saveOrder() {
				var Ticketfields_array   = 	new Array();				
				$('#deals-dashboard li').each(function(index, element) {					
					var TicketfieldsSortArray  =  {};
					TicketfieldsSortArray["data_id"] = $(element).attr('data-id');
					TicketfieldsSortArray["FieldOrder"] = index+1;  				
					 
                   	 Ticketfields_array.push(TicketfieldsSortArray); 					
                });	
				var data_sort_fields =  JSON.stringify(Ticketfields_array); 
				$('#main_fields_sort').val(data_sort_fields);
				$('#TicketfieldsDataFrom').submit();				
            }

			$('#TicketfieldsDataFrom').submit(function(e){
				e.stopPropagation();
			    e.preventDefault();	
				
				var formData = new FormData($(this)[0]);
				var url		 = baseurl + '/ticketsfields/update_fields_sorting';
				
				  $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
						if(response.status =='success'){
							toastr.success(response.message, "Success", toastr_opts);							
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
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
			
			$(document).on("submit","#edit-ticketfields-form",function(e){
				e.stopPropagation();
			    e.preventDefault();	
				var field_type_submit = $(this).find('#field_type').val();	 	
				saveOrderchoices(field_type_submit);
				
				var url = baseurl + '/ticketsfields/save_single_field';
				var formData = new FormData($(this)[0]);
				
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
						if(response.status =='success'){
							toastr.success(response.message, "Success", toastr_opts);
							$('#edit-modal-ticketfield').modal('hide');
							empty_popup_default();
							location.reload();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
						$('#field_model_btn').button('reset');
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
			
			$(document).on("click",".feild_choice_add",function(){
				var field_add_type = $(this).attr('field_type')
					if(field_add_type=='default_status'){
						$('.field_choices_ui').prepend($('.dropdown_fields_add_row_status').html());
					}else{
						$('.field_choices_ui').prepend($('.dropdown_fields_add_row').html());
					}
					$('.field_choices_ui li').eq(0).find('.feild_choice_delete').attr('field_type',field_add_type);
					
					saveOrderchoices(field_add_type);
					if(field_add_type=='default_status'){$('.field_choices_ui li').eq(0).find('.make-switch_sla').bootstrapSwitch();}
				});
			
			function saveOrderchoices(type) {
				
                var selectedCards 	= 	new Array();
				var fldli 			= 	$("#deals-dashboard").find("li [field_type='" + type + "']");							
				
				var choices_array   = 	new Array();
				 var choices_data	=   JSON.stringify( $('#edit-ticketfields-form').serializeArray() );
				
				var choices_order   = 	$('#choices_item ul.sortable-list li').each(function(index, element) {
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
				$('#modalfieldchoicesdata').val(JSON.stringify(choices_array));
            }

            function setcolor(elem,color){
                elem.colorpicker('destroy');
                elem.val(color);
                elem.colorpicker({color:color});
                elem.siblings('.input-group-addon').find('.color-preview').css('background-color', color);
            }

        });
    </script>
<style>
#deals-dashboard .board-column{width:100%;}
.count-cards{width:100% !important; min-width:100%; max-width:100%;}
.file-input-wrapper {
	height: 26px;
}
.margin-top {
	margin-top: 10px;
}
.margin-top-group {
	margin-top: 15px;
}
.paddingleft-0 {
	padding-left: 3px;
}
.paddingright-0 {
	padding-right: 0px;
}
#add-modal-opportunity .btn-xs {
	padding: 0px;
}
.resizevertical {
	resize: vertical;
}
.file-input-names span {
	cursor: pointer;
}
.WorthBox {
	display: none;
	max-width: 100%;
	padding-left: 15px;
}
.oppertunityworth {
	border-radius: 5px;
	border: 2px solid #ccc;
	background: #fff;
	padding: 0 6px;
	margin-bottom: 10px;
	font-weight: bold;
	width: 100%;
}
.currency_worth, .odometer {
	font-size: 21px;
}
.currency_worth {
	margin-left: 7px;
	vertical-align: middle;
}
.worth_add_box_ajax {
	margin-left: -2px;
}
#deals-dashboard li:hover {cursor:all-scroll; }
#choices_item .count-cards{min-height:50px;}
#deals-dashboard .count-cards{min-height:70px;}
.choices_field_li:hover {cursor:all-scroll; }
.choices_field_li{margin-bottom:0px !important; }
.count-cards .info{min-height:55px; padding:0 0 0 5px;}
.field_model_behavoiur .col-md-6{padding-left:1px !important;}
.field_model_behavoiur .col-md-6 .form-group{margin-top:5px;}
.field_model_behavoiur .col-md-6 .form-group label h3{margin-top:3px;}
.ps-scrollbar-x-rail{display:none !important; }
.ps-scrollbar-y-rail{display:none !important;}
.padding-left-0{padding-left:0px;}
.padding-right-0{padding-right:0px; width:14%;}
</style>
