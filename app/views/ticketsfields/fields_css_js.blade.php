<link rel="stylesheet" type="text/css" media="screen" href="{{ URL::asset('assets/formbuilder/css/formbuilder.css')}}">
<link rel="stylesheet" type="text/css" media="screen" href="{{ URL::asset('assets/formbuilder/css/form-builder.min.css')}}">
<link rel="stylesheet" type="text/css" media="screen" href="{{ URL::asset('assets/formbuilder/css/form-render.min.css')}}">
<style>
 #draggablePanelList .panel-heading {
        cursor: move;
    }
 .graylabel{color:#CCCCCC;}	
 #draggableValuesList li{
	 list-style:none;	 
 }
 
  #draggableValuesList >  .panel{
	box-shadow:none !important;
}
 
  #draggableValuesList > li > div:first-child{
	/* margin-bottom: 10px;*/
 }
 
 #draggableValuesList > li > .panel-head:hover{
	 cursor:move;
 }
 
  #draggableValuesList > li > .panel-divbody > .col-md-12{
	 border:none !important;	 
	 
 }


 
</style>
<script>
$(document).ready(function(e) {
	
	 var Checkboxfields = {{$Checkboxfields}};
    $('#frmb-control-box li').click(function(e) {
        var current_model = $(this).attr('modal_link');
		if(current_model){
			var prepend_body = $('.default-row').html(); 
			$('#'+current_model).find('.before_body').html(prepend_body);
			
			//$('.switch-small').addClass('make-switch');
			/*$('input.icheck1').iCheck({
					checkboxClass: 'icheckbox_minimal',
				});*/
			$('#'+current_model).modal('show');
			
			//$('#'+current_model).find('.make-switch').bootstrapSwitch();
		}		
    });
	
	$(document).on("keyup",'.AgentLabel' ,function(e) {
		var modal_label = $(this).attr('modal_label');
        $('#'+modal_label).find('.CustomerLabel').val($(this).val());
		$('#'+modal_label).find('.fldtype').html($(this).val())
    });
	
	$(document).on("click",'.icheck1' ,function(e) {
        alert($(this).val());
    });
	

	
$(document).on("click",'.iCheck-helper' ,function(e) {
    var parent = $(this).parent().get(0);
    var checkboxId = parent .getElementsByTagName('input')[0].id;
    alert(checkboxId);
});


$(document).on('click','.icon-pencil',function(){
	var id_fld		   	  = 	$(this).attr('id'); 
	var linkid		   	  = 	$(this).attr('linkid'); 
	var linkidtxt		  = 	linkid+'-data';
	var linkmultipletxt	  = 	linkid+'-data_multiple';	
	var datafld		 	  =		$('#'+linkidtxt).val();
	var datamultiplefld	  =		$('#'+linkmultipletxt).val();
	var current_model  	  = 	$(this).attr('modal_link'); 
	var datafldjson		  = 	JSON.parse(datafld);	
	var multiplesjson	  =		JSON.parse(datamultiplefld);
	
	if(current_model){
		var prepend_body = $('.default-row').html(); 
		$('#'+current_model).find('.before_body').html(prepend_body);
		
		//$('.switch-small').addClass('make-switch');
		/*$('input.icheck1').iCheck({
				checkboxClass: 'icheckbox_minimal',
			});*/
			//alert(id_fld);	
				$.each( datafldjson, function( key, value ) {
				  console.log( key + ": " + value );
				  var index = Checkboxfields.indexOf(key);
				  if(index!=-1){
					//$('#'+current_model).find('#'+index).val();
						  console.log("-"+ index + ": " + value );
						if(value==0){
							$('#'+current_model).find('#'+key).parent().hide();													
						}
						if(value==1){
							$('#'+current_model).find('#'+key).attr('disabled','disabled');							
							$('#'+current_model).find("label[for='"+key+"']").addClass('graylabel');
						}
						if(value==2){
							$('#'+current_model).find('#'+key).attr('checked','checked');
							//$('#'+current_model).find('#'+key).parent().addClass('checked');
							$('#'+current_model).find('#'+key).attr('disabled','disabled');						
							$('#'+current_model).find("label[for='"+key+"']").addClass('graylabel');
						}
						if(value==3){
							$('#'+current_model).find('#'+key).removeAttr('checked','checked');
							$('#'+current_model).find('#'+key).removeAttr('disabled','disabled');				
							$('#'+current_model).find("label[for='"+key+"']").removeClass('graylabel');
						}
						if(value==4){	
							$('#'+current_model).find('#'+key).attr('checked','checked');
						//	$('#'+current_model).find('#'+key).parent().addClass('checked');
							$('#'+current_model).find('#'+key).removeAttr('disabled','disabled');												
							$('#'+current_model).find("label[for='"+key+"']").removeClass('graylabel');
						}
				  }  
				  if(key=='AgentLabel'){
				  	$('#'+current_model).find('#'+key).val(value);
				  }
				  if(key=='CustomerLabel'){
				  	$('#'+current_model).find('#'+key).val(value);
				  }
				  if(key=='FieldName'){				  
					  $('.fldtype').html(value);
				  }
				});
				
				if(multiplesjson.length>0){
					 var panelList = $('#draggableValuesList');

					panelList.sortable({
						// Only make the .panel-heading child elements support dragging.
						// Omit this to make then entire <li>...</li> draggable.
						handle: '.panel-head', 
						update: function() {
							$('.panel', panelList).each(function(index, elem) {
								 var $listItem = $(elem),
									 newIndex = $listItem.index();
			
								 // Persist the new indices.
							});
						}
					});
				}
			
					
		$('#'+current_model).modal('show');
	}	    
});
	
	 var panelList = $('#draggablePanelList');

        panelList.sortable({
            // Only make the .panel-heading child elements support dragging.
            // Omit this to make then entire <li>...</li> draggable.
            handle: '.panel-heading', 
            update: function() {
                $('.panel', panelList).each(function(index, elem) {
                     var $listItem = $(elem),
                         newIndex = $listItem.index();

                     // Persist the new indices.
                });
            }
        });
});
</script>