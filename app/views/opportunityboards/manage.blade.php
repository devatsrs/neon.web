@extends('layout.main')

@section('content')
<style>

    .file-input-wrapper{
        height: 26px;
    }

    .margin-top{
        margin-top:10px;
    }
    .margin-top-group{
        margin-top:15px;
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
    .resizevertical{
        resize:vertical;
    }

    .file-input-names span{
        cursor: pointer;
    }

    .WorthBox{display:none;  max-width: 100%; padding-left:15px;}
    .oppertunityworth{
        border-radius:5px;
        border:2px solid #ccc;
        background:#fff;
        padding:0 6px;
        margin-bottom:10px;
        font-weight:bold;
        width:100%;
    }
.currency_worth,.odometer{font-size:21px;}
.currency_worth{ margin-left:7px; vertical-align:middle;}
.worth_add_box_ajax{margin-left:-2px;}
</style>
<div id="content">
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('opportunityboards')}}">Opportunity Board</a>
        </li>
         <li>
        {{opportunites_dropbox($id)}}
    </li>
        <!--<li class="active">
            <strong>{{$Board->BoardName}}</strong>
        </li>-->
    </ol>
    <h3>Opportunity</h3>
        <div class="row">
            <div class="col-md-12 clearfix">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form id="search-opportunity-filter" method="get"  action="" class="form-horizontal form-groups-bordered validate" novalidate>
                    <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Filter
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-1 control-label">Name</label>
                                <div class="col-sm-2">
                                    <input class="form-control" name="opportunityName"  type="text" >
                                </div>
                                @if(User::is_admin())
                                    <label for="field-1" class="col-sm-1 control-label">Account Owner</label>
                                    <div class="col-sm-2">
                                        {{Form::select('AccountOwner',$account_owners,Input::get('account_owners'),array("class"=>"select2"))}}
                                    </div>
                                @endif
                                <label for="field-1" class="col-sm-1 control-label">Company</label>
                                <div class="col-sm-2">
                                    {{Form::select('AccountID',$leadOrAccount,Input::get('AccountID'),array("class"=>"select2"))}}
                                </div>
                                <label for="field-1" class="col-sm-1 control-label">Tags</label>
                                <div class="col-sm-2">
                                    <input class="form-control opportunitytags" name="Tags" type="text" >
                                </div>

                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-1 control-label">Status</label>
                                <div class="col-sm-4">
                                    {{Form::select('Status[]', Opportunity::$status, Opportunity::$defaultSelectedStatus ,array("class"=>"select2","multiple"=>"multiple"))}}
                                </div>

                                <label for="field-1" class="col-sm-1 control-label">Currency</label>
                                <div class="col-sm-2"> {{ Form::select('CurrencyID',$currency,$DefaultCurrencyID,array("class"=>"select2")) }}</div>

                                <label class="col-sm-1 control-label">Close</label>
                                <div class="col-sm-1">
                                    <p class="make-switch switch-small">
                                        <input name="opportunityClosed" type="checkbox" value="{{Opportunity::Close}}">
                                    </p>
                                </div>
                            </div>
                            <p style="text-align: right;">
                                <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <p id="tools">
            <a class="btn btn-primary toggle grid active" title="Grid View" href="javascript:void(0)"><i class="entypo-book-open"></i></a>
            <a class="btn btn-primary toggle list" title="List View" href="javascript:void(0)"><i class="entypo-list"></i></a>
            @if(User::checkCategoryPermission('Opportunity','Add'))
                <a href="javascript:void(0)" class="btn btn-primary pull-right opportunity">
                    <i class="entypo-plus"></i>
                    Add
                </a>
            @endif
            <a><strong><span class="currency_worth"></span>  <span class="odometer worth_add_box_ajax">0.00</span></strong></a>
        </p>

        <section class="deals-board" >
            

            <table class="table table-bordered datatable hidden" id="opportunityGrid">
                <thead>
                <tr>
                    <th width="25%" >Name</th>
                    <th width="5%" >Status</th>
                    <th width="20%">Assigned To</th>
                    <th width="20%">Related To</th>
                    <th width="10%" >Expected Close Date</th>
                    <th width="5%" >Value</th>
                    <th width="5%" >Rating</th>
                    <th width="10%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
                <div id="board-start" class="board" style="height: 600px;" >
                </div>

            <form id="cardorder" method="POST" />
                <input type="hidden" name="cardorder" />
                <input type="hidden" name="BoardColumnID" />
            </form>
        </section>
    <script>
	window.odometerOptions = {
  format: '(ddd).dd'
};
        var $searchFilter = {};
        var currentDrageable = '';
        var fixedHeader = false;
        $(document).ready(function ($) {
            var opportunitystatus = JSON.parse('{{json_encode(Opportunity::$status)}}');
            var opportunity = [
                'BoardColumnID',
                'BoardColumnName',
                'OpportunityID',
                'OpportunityName',
                'BackGroundColour',
                'TextColour',
                'Company',
                'Title',
                'FirstName',
                'LastName',
                'Owner',
                'UserID',
                'Phone',
                'Email',
                'BoardID',
                'AccountID',
                'Tags',
                'Rating',
                'TaggedUsers',
                'Status',
                'Worth',
                'OpportunityClosed',
                'ClosingDate',
                'ExpectedClosing'
            ];

            @if(empty($message)){
                var allow_extensions  =   '{{$response_extensions}}';
            }@else {
                var allow_extensions  =  '';
                toastr.error({{'"'.$message.'"'}}, "Error", toastr_opts);
            }
            @endif;
            var readonly = ['Company','Phone','Email','Title','FirstName','LastName','Worth'];
            var BoardID = "{{$BoardID}}";
            var board = $('#board-start');
            var emailFileList     =    new Array();
            var token               =   '{{$token}}';
            var max_file_size_txt   =   '{{$max_file_size}}';
            var max_file_size       =   '{{str_replace("M","",$max_file_size)}}';

            board.perfectScrollbar({minScrollbarLength: 20,handlers: ['click-rail','drag-scrollbar', 'keyboard', 'wheel', 'touch']});
            board.on('mouseenter',function(){
                board.perfectScrollbar('update');
            });

            $( window ).resize(function() {
                board.perfectScrollbar('update');
            });

            data_table = $("#opportunityGrid").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/opportunity/"+BoardID+"/ajax_opportunity_grid",
                "fnServerParams": function (aoData) {
                    aoData.push(
                            {"name": "opportunityName", "value": $searchFilter.opportunityName},
                            {"name": "AccountOwner","value": $searchFilter.AccountOwner},
                            {"name": "AccountID","value": $searchFilter.AccountID},
                            {"name": "Tags","value": $searchFilter.Tags},
                            {"name": "Status","value": $searchFilter.Status},
                            {"name": "opportunityClosed","value": $searchFilter.opportunityClosed},
                            {"name": "CurrencyID","value": $searchFilter.CurrencyID}
                    );
                },
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'desc']],
                "aoColumns": [
                    {
                        "bSortable": true, //Opportunity Name
                        mRender: function (id, type, full) {
                            return full[3];
                            //return '<div class="'+(full[13] == "1"?'priority':'normal')+' inlinetable">&nbsp;</div>'+'<div class="inlinetable">'+full[5]+'</div>';
                        }
                    },
                    {
                        "bSortable": true, //Status
                        mRender: function (id, type, full) {
                            return opportunitystatus[full[19]];
                        }
                    },
                    {
                        "bSortable": true, //Assign To
                        mRender: function (id, type, full) {
                            return full[10];
                        }
                    },
                    {
                        "bSortable": true, //Related To
                        mRender: function (id, type, full) {
                            return full[6];
                        }
                    },
                    {
                        "bSortable": true, //Expected Closing
                        mRender: function (id, type, full) {
                            return full[23];
                        }
                    },
                    {
                        "bSortable": true, //Value
                        mRender: function (id, type, full) {
                            return full[20];
                        }
                    },
                    {
                        "bSortable": true, //Rating
                        mRender: function (id, type, full) {
                            return '<input type="text" class="knob" data-min="0" data-max="5" data-width="40" data-height="40" name="Rating" value="'+full[17]+'" />';
                        }
                    },
                    {
                        "bSortable": false, //action
                        mRender: function (id, type, full) {
                            action = '<div class = "hiddenRowData" >';
                            for(var i = 0 ; i< opportunity.length; i++){
                                action += '<input type = "hidden"  name = "' + opportunity[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                            }
                            action += '</div>';
                            @if(User::checkCategoryPermission('Task','Edit'))
                            action += ' <a data-id="' + full[2] + '" title="Edit" class="edit-deal btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                            @endif
                            return action;
                        }
                    }
                ],
                "oTableTools": {
                    "aButtons": [
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $(this)
                    if($('#tools .active').hasClass('grid')){
                        $('#opportunityGrid_wrapper').addClass('hidden');
                        $('#opportunityGrid').addClass('hidden');
                    }else{
                        $('#opportunityGrid_wrapper').removeClass('hidden');
                        $('#opportunityGrid').removeClass('hidden');
                    }
                    $('#opportunityGrid .knob').knob({"readOnly":true});
                    $('#opportunityGrid .knob').each(function(){
                        var self = $(this);
                        self.css('position','relative');
                        self.css('margin-top',self.css('margin-left'));
                    });
                }

            });


            getRecord();

            $('#search-opportunity-filter').submit(function(e){
                e.preventDefault();
                getRecord();
            });


            @if(User::checkCategoryPermission('Opportunity','Edit'))
            $(document).on('click','#board-start ul.sortable-list li button.edit-deal,#opportunityGrid .edit-deal',function(e){
				
                e.stopPropagation();
                if($(this).is('a')){
                    var rowHidden = $(this).prev('div.hiddenRowData');
                }else {
                    var rowHidden = $(this).parents('.tile-stats').children('div.row-hidden');
                }
                var select = ['UserID','BoardID','TaggedUsers','Title','Status'];
                var color = ['BackGroundColour','TextColour'];
                var OpportunityClosed = 0;
                //$('.closedDate').addClass('hidden');
                //$('#closedDate').text('');
				$('#ClosingDate').val('');
                for(var i = 0 ; i< opportunity.length; i++){
                    var val = rowHidden.find('input[name="'+opportunity[i]+'"]').val();					
                    var elem = $('#edit-opportunity-form [name="'+opportunity[i]+'"]');
                    //console.log(opportunity[i]+' '+val);
                    if(select.indexOf(opportunity[i])!=-1){
                        if(opportunity[i]=='TaggedUsers'){
                            var taggedUsers = rowHidden.find('[name="TaggedUsers"]').val();
                            $('#edit-opportunity-form [name="TaggedUsers[]"]').select2('val', taggedUsers.split(','));
                        }else {
                            elem.val(val).trigger("change");
                        }
                    } else{
                        elem.val(val);
                        if(color.indexOf(opportunity[i])!=-1){
                            setcolor(elem,val);
                        }else if(opportunity[i]=='Rating'){
                            elem.val(val).trigger('change');
                        }else if(opportunity[i]=='Tags'){
                            elem.val(val).trigger("change");
                        }else if(opportunity[i]=='OpportunityClosed'){
                            if(val==1){
                                OpportunityClosed = 1;
                                biuldSwicth('.make','#edit-opportunity-form','checked');
                            }else{
                                biuldSwicth('.make','#edit-opportunity-form','');
                            }
                        }else if(opportunity[i]=='ClosingDate'){
                            if(OpportunityClosed==1){
                                //$('.closedDate').removeClass('hidden');
                                //$('#closedDate').text(val);
								$('#ClosingDate').val(val);
                            }
                        }
                        else if(opportunity[i]=='ExpectedClosing'){
                            if(val=='0000-00-00'){
                                elem.val('');
                            }
                        }
                    }
                }
                $('#edit-modal-opportunity h4').text('Edit Opportunity');
                $('#edit-modal-opportunity').modal('show');
            });
            @endif
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
            @if(User::checkCategoryPermission('OpportunityComment','View'))
            $(document).on('click','#board-start ul.sortable-list li',function(){
                $('#add-opportunity-comments-form').trigger("reset");
                $('.sendmail').removeClass('hidden');
                var rowHidden = $(this).children('div.row-hidden');
                $('#allComments,#attachments').empty();
                var opportunityID = rowHidden.find('[name="OpportunityID"]').val();
                var accountID = rowHidden.find('[name="AccountID"]').val();
                var opportunityName = rowHidden.find('[name="OpportunityName"]').val();
                if(!accountID){
                    $('.sendmail').addClass('hidden');
                }
                $('#add-opportunity-comments-form [name="OpportunityID"]').val(opportunityID);
                $('#add-opportunity-attachment-form [name="OpportunityID"]').val(opportunityID);
                $('#add-opportunity-attachment-form [name="AccountID"]').val(accountID);
                $('#add-opportunity-comments-form [name="AccountID"]').val(accountID);
                $('#add-view-modal-opportunity-comments h4.modal-title').text(opportunityName);
                getComments();
                getOpportunityAttachment();
                autosizeUpdate();
                $('#add-view-modal-opportunity-comments').modal('show');
            });

            @endif
            $('#add-opportunity-comments-form').submit(function(e){
                e.preventDefault();
                var formData = new FormData($('#add-opportunity-comments-form')[0]);
                var url = baseurl + '/opportunitycomment/create';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            emailFileList = [];
                            $(".file-input-names").empty();
                            $('#add-opportunity-comments-form').trigger("reset");
                            $('#commentadd').siblings('.file-input-name').empty();
                            $('#card-features-details').find('[name="attachmentsinfo"]').val('');
                            toastr.success(response.message, "Success", toastr_opts);
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#commentadd").button('reset');
                        autosizeUpdate();
                        getComments();
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
            @if(User::checkCategoryPermission('OpportunityAttachment','Add'))
            $(document).on('change','#add-opportunity-attachment-form input[type="file"]',function(){
                var opportunityID = $('#add-opportunity-attachment-form [name="OpportunityID"]').val();
                var formData = new FormData($('#add-opportunity-attachment-form')[0]);
                var url = baseurl + '/opportunity/'+opportunityID+'/saveattachment';
                var top = $(this).offset().top;
                top = top-300;
                $('#attachment_processing').css('top',top);
                $('#attachment_processing').removeClass('hidden');
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-opportunity-attachment-form').trigger("reset");
                            $('#attachment_processing').addClass('hidden');
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $('#add-opportunity-attachment-form').trigger("reset");
                        $('#addattachmentop .file-input-name').empty();
                        getOpportunityAttachment();
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
            @endif
            @if(User::checkCategoryPermission('OpportunityAttachment','Delete'))
            $(document).on('click','#attachments i.delete-file',function(){
                var con = confirm('Are you sure you want to delete this attachments?');
                if(!con){
                    return true;
                }
                var opportunityID = $('#add-opportunity-attachment-form [name="OpportunityID"]').val();
                var attachmentID = $(this).attr('data-id');
                var url = baseurl + '/opportunity/'+opportunityID+'/deleteattachment/'+attachmentID;
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
                        getComments();
                        getOpportunityAttachment();
                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
            @endif
            $(document).on('click','#addTtachment',function(){
                $('#filecontrole').click();
            });

            $(document).on('click','.viewattachments',function(){
                $(this).siblings('.comment-attachment').toggleClass('hidden');
            });


            $(document).on('change','#filecontrole',function(e){
                e.stopImmediatePropagation();
                e.preventDefault();
                var files     = e.target.files;
                var fileText    = new Array();
                var file_check   = 1;
                var local_array   =  new Array();
                var filesArr = Array.prototype.slice.call(files);
                filesArr.forEach(function(f) {
                    var ext_current_file  = f.name.split('.').pop();
                    if(allow_extensions.indexOf(ext_current_file.toLowerCase()) > -1 ) {
                        var name_file = f.name;
                        var index_file = emailFileList.indexOf(f.name);
                        if(index_file >-1 ) {
                            ShowToastr("error",f.name+" file already selected.");
                        } else if(bytesToSize(f.size)) {
                            ShowToastr("error",f.name+" file size exceeds then upload limit ("+max_file_size_txt+"). Please select files again.");
                            file_check = 0;
                            return false;
                        }else {
                            //emailFileList.push(f.name);
                            local_array.push(f.name);
                        }
                    } else {
                        ShowToastr("error",ext_current_file+" file type not allowed.");
                    }
                });
                if(local_array.length>0 && file_check==1) {
                    emailFileList = emailFileList.concat(local_array);

                    var formData = new FormData($('#add-opportunity-comments-form')[0]);
                    var url = baseurl + '/opportunity/uploadfile';
                    $.ajax({
                        url: url,  //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if(response.status =='success'){
                                $('#card-features-details').find('.file-input-names').html(response.data.text);
                                $('#card-features-details').find('[name="attachmentsinfo"]').val(JSON.stringify(response.data.attachmentsinfo));

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
                }
            });

            $(document).on("click",".del_attachment",function(ee){
                var url  =  baseurl + '/opportunity/deleteattachmentfile';
                var fileName   =  $(this).attr('del_file_name');
                var attachmentsinfo = $('#card-features-details').find('[name="attachmentsinfo"]').val();
                if(!attachmentsinfo){
                    return true;
                }
                attachmentsinfo = jQuery.parseJSON(attachmentsinfo);
                $(this).parent().remove();
                var fileIndex = emailFileList.indexOf(fileName);
                var fileinfo = attachmentsinfo[fileIndex];
                emailFileList.splice(fileIndex, 1);
                attachmentsinfo.splice(fileIndex, 1);
                $('#card-features-details').find('[name="attachmentsinfo"]').val(JSON.stringify(attachmentsinfo));
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data:{file:fileinfo},
                    async :false,
                    success: function(response) {
                        if(response.status =='success'){

                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }
                });
            });

            $('#add-view-modal-opportunity-comments').on('shown.bs.modal', function(event){
                emailFileList = [];
                $(".file-input-names").empty();
                $('#add-opportunity-comments-form').trigger("reset");
                $('#commentadd').siblings('.file-input-name').empty();
                $('#card-features-details').find('[name="attachmentsinfo"]').val('');
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
                        postorder(ui.item);
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

            function getRecord(){
                $searchFilter.opportunityName = $("#search-opportunity-filter [name='opportunityName']").val();
                $searchFilter.AccountOwner = $("#search-opportunity-filter [name='AccountOwner']").val();
                $searchFilter.AccountID = $("#search-opportunity-filter [name='AccountID']").val();
                $searchFilter.Tags = $("#search-opportunity-filter [name='Tags']").val();
                $searchFilter.Status = $("#search-opportunity-filter [name='Status[]']").val();
                $searchFilter.opportunityClosed = $("#search-opportunity-filter [name='opportunityClosed']").prop("checked");
                $searchFilter.CurrencyID = $("#search-opportunity-filter select[name='CurrencyID']").val();
                getOpportunities();
                data_table.fnFilter('',0);
            }

            function getOpportunities(){
                var formData = new FormData($('#search-opportunity-filter')[0]);
                var url = baseurl + '/opportunity/'+BoardID+'/ajax_opportunity';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        board.html(response);
                        var worth_hidden = $('#Worth_hidden').val();
						var Currency_hidden = $('#Currency_hidden').val();
                        $('.odometer').html(0);
						$('.currency_worth').html('');
						$('.worth_add_box_ajax').html(worth_hidden); 
						$('.currency_worth').html(Currency_hidden);
						
						//$('.WorthBox').show();
                        initEnhancement();
                        initSortable();
                        initToolTip();
                    },
                    // Form data
                    data: formData,
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
                var url = baseurl + '/opportunityboardcolumn/{{$BoardID}}/ajax_datacolumn';
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
                            getOpportunities();
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

            function saveOrder(elem) {
                var selectedCards = new Array();
                var currentColumn = elem.parents('li.board-column');
                var BoardColumnID = currentColumn.attr('data-id');
                currentColumn.find('ul.board-column-list li.count-cards').each(function() {
                    selectedCards.push($(this).attr("data-id"));
                });
                $('#cardorder [name="cardorder"]').val(selectedCards);
                $('#cardorder [name="BoardColumnID"]').val(BoardColumnID);
            }

            function setcolor(elem,color){
                elem.colorpicker('destroy');
                elem.val(color);
                elem.colorpicker({color:color});
                elem.siblings('.input-group-addon').find('.color-preview').css('background-color', color);
            }

            function bytesToSize(filesize) {
                var sizeInMB = (filesize / (1024*1024)).toFixed(2);
                if(sizeInMB>max_file_size)
                {return 1;}else{return 0;}
            }
        });
    </script>
</div>
@include('opportunityboards.opportunitymodal')
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="edit-modal-opportunity">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="edit-opportunity-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Opportunity</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-12 text-left">
                                <label for="field-5" class="control-label col-sm-2">Tag User</label>
                                <div class="col-sm-10">
                                    <?php unset($account_owners['']); ?>
                                    {{Form::select('TaggedUsers[]',$account_owners,[],array("class"=>"select2","multiple"=>"multiple"))}}
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Account Owner *</label>
                                    <div class="col-sm-8">
                                        {{Form::select('UserID',$account_owners,'',array("class"=>"select2"))}}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 margin-top pull-right">
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

                            <div class="col-md-6 margin-top-group pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">First Name*</label>
                                    <div class="col-sm-8">
                                        <div class="input-group" style="width: 100%;">
                                            <div class="input-group-addon" style="padding: 0px; width: 85px;">
                                                <?php $NamePrefix_array = array( ""=>"-None-" ,"Mr"=>"Mr", "Miss"=>"Miss" , "Mrs"=>"Mrs" ); ?>
                                                {{Form::select('Title', $NamePrefix_array, '' ,array("class"=>"select2 small"))}}
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
                                    <label for="field-5" class="control-label col-sm-4">Phone Number</label>
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

                            <div class="col-md-6 margin-top-group pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Status</label>
                                    <div class="col-sm-8 input-group">
                                        {{Form::select('Status', Opportunity::$status, '' ,array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Select Board*</label>
                                    <div class="col-sm-8">
                                        {{Form::select('BoardID',$boards,'',array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Tags</label>
                                    <div class="col-sm-8 input-group">
                                        <input class="form-control opportunitytags" name="Tags" type="text" >
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top-group pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Value</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" value="0" name="Worth" type="number" step="any" min=”0″>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Expected Close Date</label>
                                    <div class="col-sm-8">
                                        <input autocomplete="off" type="text" name="ExpectedClosing" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top-group pull-left">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Close</label>
                                    <div class="col-sm-3 make">
                                        <p class="make-switch switch-small">
                                            <input name="opportunityClosed" type="checkbox" value="{{Opportunity::Close}}">
                                        </p>
                                    </div>
                                    <label class="col-sm-2 control-label closedDate hidden">Closed Date</label>
                                    <div class="col-sm-3">
                                        <span id="closedDate"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Actual Close Date</label>
                                    <div class="col-sm-8">
                                        <input autocomplete="off" id="ClosingDate" type="text" name="ClosingDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                                    </div>
                                </div>
                            </div>

                            <!--<div class="col-md-6 margin-top-group pull-left">
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
                            </div>-->

                            <!--<div class="col-md-6 margin-top-group pull-left">
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
                                    </div>
                                </div>
                            </div>-->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="OpportunityID">
                        <button type="submit" id="opportunity-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

    <div class="modal fade" id="add-view-modal-opportunity-comments" data-backdrop="static">
        <div id="card-features-details" class="modal-dialog">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Opportunity Name</h4>
                    </div>
                    <div class="modal-body">
                        @if(User::checkCategoryPermission('OpportunityComment','Add'))
                        <form id="add-opportunity-comments-form" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-12 text-left">
                                    <h4>Add Comment</h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea class="form-control autogrow resizevertical" name="CommentText" placeholder="Write a comment."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 pull-left end-buttons sendmail" style="text-align: left;">
                                    <label for="field-5" class="control-label">Send Mail To Customer:</label>
                                    <span id="label-switch" class="make-switch switch-small">
                                        <input name="PrivateComment" value="1" type="checkbox">
                                    </span>
                                </div>
                                <div class="col-md-6 pull-right end-buttons" style="text-align: right;">
                                    <p class="comment-box-options">
                                        <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)">
                                            <i class="entypo-attach"></i>
                                        </a>
                                    </p>
                                    <input type="hidden" name="OpportunityID" >
                                    <input type="hidden" name="AccountID" >
                                    <button data-loading-text="Loading..." id="commentadd" class="add btn btn-primary btn-sm btn-icon icon-left" type="submit" style="visibility: visible;">
                                        <i class="entypo-floppy"></i>
                                        Add Comment
                                    </button>
                                    <div class="file_attachment">
                                        <div class="file-input-names"></div>
                                        <input id="filecontrole" type="file" name="commentattachment[]" class="hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />&nbsp;
                                        <input  type="hidden" name="token_attachment" value="{{$token}}" />
                                        <input type="hidden" name="attachmentsinfo" >
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                <div id="comment_processing" class="dataTables_processing hidden">Processing...</div>
                                <div id="allComments"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="attachments" class="form-group"></div>
                            </div>
                        </div>
                        <div id="attachment_processing" class="dataTables_processing hidden">Processing...</div>
                        <form id="add-opportunity-attachment-form" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8"></div>
                                <div class="col-md-4" id="addattachmentop" style="text-align: right;">
                                    <input type="file" name="opportunityattachment[]" data-loading-text="Loading..." class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" multiple data-label="<i class='entypo-attach'></i>Add Attachments" />
                                    <input type="hidden" name="OpportunityID" >
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
            </div>
        </div>
    </div>

@stop
