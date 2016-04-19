<script type="text/javascript">
    jQuery(document).ready(function ($) {
        /*
         * Note Add/Edit/Delete Script
         * */
        //After Delete done
        FnDeleteNoteSuccess = function(response){
            if (response.status == 'success') {
                $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                ShowToastr("success",response.message);
            }else{
                ShowToastr("error",response.message);
            }
        }
        //onDelete Click
        FnDeleteNote = function(){
            result = confirm("Are you Sure?");
            if(result){
                var id  = $(this).attr("id");
                showAjaxScript( baseurl + "/{{$controller}}/"+id+"/delete_note" ,"",FnDeleteNoteSuccess );
            }
            return false;
        }
        FnEditNote = function(){
            var id  = $(this).attr("id");
            Txtnote = $(this).parent().next().find("#Note"+id + " p").html().nl2br();
            $("#txtNote").val(Txtnote).focus();
            $("input[name=NoteID]").val(id);

            return false;
        }
        $("#notes-from .btn.btn-danger").click(function () {
            $("input[name=NoteID]").val("");
        });
        $(".editNote").click(FnEditNote); // Edit Note
        $(".deleteNote").click(FnDeleteNote); // Delete Note

        //After Note Save.
        FnAddNoteSuccess = function(response){
			  $(".save.btn").button('reset');
            if (response.message) {
				 ShowToastr("error",response.message);
            } else {
				per_scroll = count;
                ShowToastr("success","Note Successfully Created");                         
                $('#timeline-ul li:eq(0)').before(response);
				document.getElementById('notes-from').reset();
            }
      			
			
			}
		
		//After Email Save.
        FnAddEmailSuccess = function(response){
			alert(response);
            $(".save.btn").button('reset');
            if (response.AccountEmailLogID) {
                ShowToastr("success",response.message);
                var getClass = $(".count-li");
                var count = 0;
                getClass.each(function () {
                    count++;
                });
                var addCount = count + 1;
                var noteContent = $("#note-content").val();
                var html = '<li id="timeline-' + addCount + '" class="count-li"><time class="cbp_tmtime" datetime="2014-03-27T03:45"><span>Now</span></time><div class="cbp_tmicon bg-success"><i class="entypo-mail"></i></div><div class="cbp_tmlabel"><h2 onclick=emailDialog()>You <span>sent an email to </span>' + response.EmailTo + '</h2><a id="show-more-' + addCount + '" onclick="expandTimeLine(' + addCount + ')" class="pull-right show-less">Show More<i class="entypo-down-open"></i></a><div id="hidden-timeline-' + addCount + '" class="details no-display"><p>CC: &nbsp; '+response.EmailTo+' </p><p>BCC: &nbsp; '+response.EmailTo+'</p><p>Subject: &nbsp; '+response.Subject+'</p><p>Email: &nbsp; '+response.EmailTo+'</p><a class="pull-right show-less" onclick="hideDetail(' + addCount + ')">Show Less<i class="entypo-up-open"></i></a></div></div></li>';
                $('#timeline-ul li:eq(0)').before(html);

            } else {
                ShowToastr("error",response.message);
            }
        }
        //Note Form Submit
        $("#notes-from").submit(function () {			
           /* var formData = new FormData($('#notes-from')[0]);
            showAjaxScript( $("#notes-from").attr("action") ,formData,FnSubmitNoteSuccess );
            return false;*/
        });
    });
</script>