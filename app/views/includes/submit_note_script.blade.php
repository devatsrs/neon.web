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
        FnSubmitNoteSuccess = function(response){
            $(".save.btn").button('reset');
            if (response.status == 'success') {
                ShowToastr("success",response.message);
                var output = "";
                if(response.update != undefined && response.update == true ){
                    output = '<p>'+ response.Note.Note+'</p>';
                    $("#Note"+response.NoteID).html(output).fadeIn('slow');
                }else{
                    output = '<tr><td><a href="#" class="btn-danger btn-sm deleteNote entypo-cancel" id="'+ response.Note.NoteID + '"></a><a href="#" id="'+ response.Note.NoteID +'" class="btn-default btn-sm editNote entypo-pencil"></a></td><td ><div id="Note'+ response.NoteID+'"><span class="badge badge-secondary badge-roundless">New</span><p>'+ response.Note.Note+'</p></div><h5><a href="#">'+ response.Note.created_by+'</a> &nbsp; '+ response.Note.created_at+'</h5></td></tr>';
                    $(".notes_body").prepend(output).fadeIn('slow'); // Show new record
                    $(".editNote").click(FnEditNote); // Edit Note
                    $(".deleteNote").click(FnDeleteNote);// Delete Note
                }
            } else {
                ShowToastr("error",response.message);
            }
        }
        //Note Form Submit
        $("#notes-from").submit(function () {
            var formData = new FormData($('#notes-from')[0]);
            showAjaxScript( $("#notes-from").attr("action") ,formData,FnSubmitNoteSuccess );
            return false;
        });
    });
</script>