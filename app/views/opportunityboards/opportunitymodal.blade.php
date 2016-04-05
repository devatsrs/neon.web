<style>

        .file-input-wrapper{
            height: 26px;
        }

        .margin-top{
            margin-top:10px;
        }
        .paddingleft-0{
            padding-left: 3px;
        }
        .paddingright-0{
            padding-right: 0px;
        }
        #add-modal-opportunity .btn-xs{
            padding:0px;
        }

    </style>
<script>
    $(document).ready(function ($) {
        var opportunity = [
            'BoardColumnID',
            'BoardColumnName',
            'OpportunityID',
            'OpportunityName',
            'BackGroundColour',
            'TextColour',
            'Company',
            'ContactName',
            'Owner',
            'UserID',
            'Phone',
            'Email',
            'BoardID',
            'AccountID',
            'Tags',
            'Rating',
            'TaggedUser'
        ];
        var readonly = ['Company','Phone','Email','Title','FirstName','LastName'];
        var ajax_complete = false;
        var BoardID = '';
        var leadOrAccountID = '';
        @if(isset($BoardID))
            BoardID = "{{$BoardID}}";
            <?php $disabled='';$leadOrAccountExist = 'No';$leadOrAccountID = '';$leadOrAccountCheck='' ?>
        @else
         <?php $leads = [];$disabled = 'disabled';$leadOrAccountExist = 'Yes'?>
        leadOrAccountID = '{{$leadOrAccountID}}';
        @endif
        var usetId = "{{User::get_userID()}}";
        $('#add-opportunity-form [name="Rating"]').knob();
        //getOpportunities();
        $(document).on('click','.opportunity',function(){
            $('#add-opportunity-form').trigger("reset");
            var select = ['UserID','AccountID','BoardID'];
            var accountID = '';
            for(var i = 0 ; i< opportunity.length; i++){
                var elem = $('#add-opportunity-form [name="'+opportunity[i]+'"]');
                if(select.indexOf(opportunity[i])!=-1){
                    elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                    if(opportunity[i]=='UserID'){
                        $('#add-opportunity-form [name="UserID"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(usetId);
                        if(BoardID) {
                            $('#add-opportunity-form [name="leadcheck"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption('No');
                        }else{
                            $('#add-modal-opportunity .leads').removeClass('hidden');
                            $('#add-modal-opportunity .toHidden').addClass('hidden');
                        }
                    }else if(opportunity[i]=='AccountID'){
                        if(leadOrAccountID) {
                            elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption(leadOrAccountID);
                        }
                    }
                } else{
                    elem.val('');
                    if(opportunity[i]=='Tags'){
                        elem.val('').trigger("change");
                    }
                }
            }
            $('#add-modal-opportunity [name="BoardID"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(BoardID);

            setcolor($('#add-modal-opportunity [name="BackGroundColour"]'),'#303641');
            setcolor($('#add-modal-opportunity [name="TextColour"]'),'#ffffff');
            $('#add-opportunity-form [name="Rating"]').val(0);
            $('#add-opportunity-form [name="Rating"]').trigger('change');
            $('#add-modal-opportunity h4').text('Add Opportunity');
            if(!BoardID){
                accountID =$(this).attr('data-id');
                $('#add-opportunity-form [name="AccountID"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(accountID);
            }
            $('#add-modal-opportunity').modal('show');
        });

        $('#add-opportunity-form [name="leadcheck"]').change(function(){
            var lead = $('#add-modal-opportunity').find('.leads');
            if ($(this).val() == 'Yes') {
                lead.removeClass('hidden');
                setunset('');
            } else {
                lead.addClass('hidden');
                setunset('');
            }
        });

        $('#add-opportunity-form [name="AccountID"]').change(function(){
            var AccountID = $(this).val();
            getLeadorAccountInstance(AccountID);
        });

        $('#add-opportunity-form [name="UserID"]').change(function(){
            if(!BoardID){
                return true;
            }
            check = 1;
            if($('#add-opportunity-form [name="leadOrAccount"]').val()=='Lead'){
                $('#leadlable').text('Existing lead');
                $('.leads label').text('Lead');
            }else{
                $('#leadlable').text('Existing Account');
                $('.leads label').text('Account');
                check = 2;
            }
            var url = baseurl + '/opportunity/'+check+'/getDropdownLeadAccount';
            getLeadOrAccount(url);
        });

        $('#taggedUser [name="taggedUser[]"]').change(function(){
            var formData = new FormData($('#taggedUser')[0]);
            var opportunityID = $('#add-opportunity-comments-form [name="OpportunityID"]').val();
            var url = baseurl + '/opportunity/'+opportunityID+'/updatetaggeduser';
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
                    $("#opportunity-update").button('reset');
                    //getOpportunities();
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $(document).on('change','#add-opportunity-form [name="leadOrAccount"]',function(){
            changelableanddropdown();
        });

        $('#add-opportunity-form,#edit-opportunity-form').submit(function(e){
            e.preventDefault();
            var update_new_url = '';
            var formid = $(this).attr('id');
            var opportunityID = $('#'+formid).find('[name="OpportunityID"]').val();

            if(opportunityID){
                update_new_url = baseurl + '/opportunity/'+opportunityID+'/update';
            }else{
                update_new_url = baseurl + '/opportunity/create';
            }
            var formData = new FormData($('#'+formid)[0]);
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#add-modal-opportunity').modal('hide');
                        if(BoardID){
                            $('#search-opportunity-filter').submit();
                        }
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $("#opportunity-add").button('reset');
                    $("#opportunity-update").button('reset');
                    //getOpportunities();
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $('#add-modal-opportunity .reset').click(function(){
            var colorPicker = $(this).parents('.form-group').find('[type="text"].colorpicker');
            var color = $(this).attr('data-color');
            setcolor(colorPicker,color);
        });

        $(".opportunitytags").select2({
            tags:{{$opportunitytags}}
        });

        function getLeadOrAccount(url){
            var formData = new FormData($('#add-opportunity-form')[0]);
            $.ajax({
                url: url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    var elem = $('#add-opportunity-form [name="AccountID"]');
                    //elem.select2('destroy');
                    elem.empty();
                    if(Object.prototype.toString.call( response.result ) === '[object Object]') {
                        $.each(response.result, function (i, item) {
                            elem.append('<option value="' + i + '">' + item + '</option>');
                        });

                    }else{
                        elem.append('<option value="">Not Found</option>');
                    }
                    elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                    //opts = {allowClear: attrDefault(elem, 'allowClear', false)};
                    //elem.select2(opts);
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        }

        function setunset(data){
            for(var i = 0 ; i< readonly.length; i++){
                $('#add-opportunity-form [name="'+readonly[i]+'"]').val('');
                //$('#add-opportunity-form [name="'+readonly[i]+'"]').prop('readonly', status);
                if(data){
                    if(readonly[i]=='Title'){
                        $('#add-opportunity-form [name="'+readonly[i]+'"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(data[readonly[i]]);
                    }else {
                        $('#add-opportunity-form [name="' + readonly[i] + '"]').val(data[readonly[i]]);
                    }
                }
            }
        }

        function setcolor(elem,color){
            elem.colorpicker('destroy');
            elem.val(color);
            elem.colorpicker({color:color});
            elem.siblings('.input-group-addon').find('.color-preview').css('background-color', color);
        }

        function getLeadorAccountInstance(AccountID){
            if(AccountID) {
                var url = baseurl + '/opportunity/' + AccountID + '/getlead';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        setunset(response);
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }else{
                setunset('');
            }
        }

        function changelableanddropdown(){
            var check=1;
            if($('#add-opportunity-form [name="leadOrAccount"]').val()=='Lead'){
                $('#leadlable').text('Existing lead');
                $('.leads label').text('Lead');
            }else{
                $('#leadlable').text('Existing Account');
                $('.leads label').text('Account');
                check = 2;
            }
            var url = baseurl + '/opportunity/'+check+'/getDropdownLeadAccount';
            getLeadOrAccount(url);
            setunset('');
        }
    });
</script>

@section('footer_ext')
    @parent
<div class="modal fade" id="add-modal-opportunity">
    <div class="modal-dialog" style="width: 70%;">
        <div class="modal-content">
            <form id="add-opportunity-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Opportunity</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 pull-left">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Account Owner *</label>
                                <div class="col-sm-8">
                                    {{Form::select('UserID',$account_owners,'',array("class"=>"select2",$disabled))}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 pull-right">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Opportunity Name *</label>
                                <div class="col-sm-8">
                                    <input type="text" name="OpportunityName" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top pull-right">
                            <div class="form-group">
                                <label for="input-1" class="control-label col-sm-4">Rate This</label>
                                <div class="col-sm-8">
                                    <input type="text" class="knob" data-min="0" data-max="5" data-width="85" data-height="85" name="Rating" value="0" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 margin-top pull-left toHidden">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Lead/Account</label>
                                <div class="col-sm-8">
                                    <?php $leadaccount = ['Lead'=>'Lead','Account'=>'Account']; ?>
                                    {{Form::select('leadOrAccount',$leadaccount,$leadOrAccountCheck,array("class"=>"selectboxit"))}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 margin-top pull-left toHidden">
                            <div class="form-group">
                                <label id="leadlable" for="field-5" class="control-label col-sm-4">Existing Lead</label>
                                <div class="col-sm-8">
                                    <?php $leadcheck = ['No'=>'No','Yes'=>'Yes']; ?>
                                    {{Form::select('leadcheck',$leadcheck,$leadOrAccountExist,array("class"=>"selectboxit"))}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 margin-top pull-left leads hidden">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Leads</label>
                                <div class="col-sm-8">
                                    {{Form::select('AccountID',$leadOrAccount,$leadOrAccountID,array("class"=>"select2",$disabled))}}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top-group pull-left">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">First Name*</label>
                                <div class="col-sm-8">
                                    <div class="input-group" style="width: 100%;">
                                        <div class="input-group-addon" style="padding: 0px; width: 85px;">
                                            <?php $NamePrefix_array = array( ""=>"-None-" ,"Mr"=>"Mr", "Miss"=>"Miss" , "Mrs"=>"Mrs" ); ?>
                                            {{Form::select('Title', $NamePrefix_array, '' ,array("class"=>"selectboxit"))}}
                                        </div>
                                        <input type="text" name="FirstName" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top pull-right">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Last Name*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="LastName" class="form-control" id="field-5">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top pull-left">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Company*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="Company" class="form-control" id="field-5">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top pull-right">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Phone Number*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="Phone" class="form-control" id="field-5">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 margin-top pull-left">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Email Address*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="Email" class="form-control" id="field-5">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top pull-right">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Select Board*</label>
                                <div class="col-sm-8">
                                    {{Form::select('BoardID',$boards,'',array("class"=>"selectboxit"))}}
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6 margin-top-group pull-left">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Select Background</label>
                                <div class="col-sm-7 input-group paddingright-0">
                                    <input name="BackGroundColour" type="text" class="form-control colorpicker" value="" />
                                    <div class="input-group-addon">
                                        <i class="color-preview"></i>
                                    </div>
                                </div>
                                <div class="col-sm-1 paddingleft-0">
                                    <a class="btn btn-primary btn-xs reset" data-color="#303641" href="javascript:void(0)">
                                        <i class="entypo-ccw"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top-group pull-right">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Tags</label>
                                <div class="col-sm-8 input-group">
                                    <input class="form-control opportunitytags" name="Tags" type="text" >
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top-group pull-left">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Text Color</label>
                                <div class="col-sm-7 input-group paddingright-0">
                                    <input name="TextColour" type="text" class="form-control colorpicker" value="" />
                                    <div class="input-group-addon">
                                        <i class="color-preview"></i>
                                    </div>
                                </div>
                                <div class="col-sm-1 paddingleft-0">
                                    <a class="btn btn-primary btn-xs reset" data-color="#ffffff" href="javascript:void(0)">
                                        <i class="entypo-ccw"></i>
                                    </a>
                                    <!--<button class="btn btn-xs btn-danger reset" data-color="#ffffff" type="button">Reset</button>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="OpportunityID">
                    <button type="submit" id="opportunity-add"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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