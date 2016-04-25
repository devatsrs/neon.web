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
    #add-modal-task .btn-xs{
        padding:0px;
    }
    .resizevertical{
        resize:vertical;
    }

</style>
<div id="content">
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('taskboards')}}">Task</a>
        </li>
        <li class="active">
            <strong>{{$Board[0]->BoardName}}</strong>
        </li>
    </ol>

    <div class="row">
        <div class="col-md-12 clearfix">
        </div>
    </div>
    <p style="text-align: right;">
        <a href="{{URL::to('task/'.$Board[0]->BoardID.'/configure')}}" class="btn btn-primary">
            <i class="entypo-cog"></i>
            Configure Board
        </a>
    </p>

    <div class="row">
            <div class="col-md-12">
                <form id="search-task-filter" method="get"  action="" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
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
                                    <input class="form-control" name="taskName"  type="text" >
                                </div>
                                @if(User::is_admin())
                                    <label for="field-1" class="col-sm-1 control-label">Assign To</label>
                                    <div class="col-sm-2">
                                        {{Form::select('AccountOwner',$account_owners,User::get_userID(),array("class"=>"select2"))}}
                                    </div>
                                @endif
                                <label for="field-1" class="col-sm-1 control-label">Priority</label>
                                <div class="col-sm-2">
                                    <?php $priority = array(""=> "Select a Priority")+$priority; ?>
                                    {{Form::select('Priority',$priority,Input::get('AccountID'),array("class"=>"selectboxit"))}}
                                </div>
                                <label for="field-1" class="col-sm-1 control-label">Status</label>
                                <div class="col-sm-2">
                                    {{Form::select('TaskStatus',[''=>'Select a Status']+$taskStatus,'',array("class"=>"selectboxit"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-1 control-label">Due Date</label>
                                <div class="col-sm-2">
                                    {{Form::select('DueDateFilter',Task::$tasks,'',array("class"=>"selectboxit"))}}
                                </div>
                                <div class="col-sm-2">
                                    <input autocomplete="off" type="text" name="DueDate" class="form-control datepicker hidden"  data-date-format="yyyy-mm-dd" value="" />
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
            <a class="btn btn-primary toggle list active" href="javascript:void(0)"><i class="entypo-list"></i></a>
            <a class="btn btn-primary toggle grid" href="javascript:void(0)"><i class="entypo-book-open"></i></a>
            <a href="javascript:void(0)" class="btn btn-primary pull-right task">
                <i class="entypo-plus"></i>
                Add Task
            </a>
        </p>

        <section class="deals-board">
            <table class="table table-bordered datatable" id="taskGrid">
                <thead>
                <tr>
                    <th width="10%" >Subject</th>
                    <th width="15%" >Due Date</th>
                    <th width="15%" >Status</th>
                    <th width="10%">Priority</th>
                    <th width="20%">Assigned To</th>
                    <th width="20%">Related To</th>
                    <th width="10%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="board-start" class="board scroller hidden" style="height: 500px;overflow: auto !important;">
            </div>
            <form id="cardorder" method="POST" />
                <input type="hidden" name="cardorder" />
                <input type="hidden" name="BoardColumnID" />
            </form>
        </section>
    <?php $BoardID = $Board[0]->BoardID; ?>
    <script>
        var $searchFilter = {};
        var currentDrageable = '';
        var fixedHeader = false;
        $(document).ready(function(){

            $searchFilter.taskName = $("#search-task-filter [name='taskName']").val();
            $searchFilter.AccountOwner = $("#search-task-filter [name='AccountOwner']").val();
            $searchFilter.Priority = $("#search-task-filter [name='Priority']").val();
            $searchFilter.DueDateFilter = $("#search-task-filter [name='DueDateFilter']").val();
            $searchFilter.DueDate = $("#search-task-filter [name='DueDate']").val();
            $searchFilter.TaskStatus = $("#search-task-filter [name='TaskStatus']").val();
            var task = [
                'BoardColumnID',
                'BoardColumnName',
                'TaskID',
                'UsersIDs',
                'Users',
                'AccountIDs',
                'company',
                'Subject',
                'Description',
                'DueDate',
                'TaskStatus',
                'Priority',
                'PriorityText',
                'TaggedUser',
                'BoardID'
            ];
            var Priority = ['<i style="color:red;font-size:15px;" class="edit-deal entypo-up-bold"></i>',
                            '<i style="color:#FAD839;font-size:15px;" class="edit-deal entypo-record"></i>',
                            '<i style="color:green;font-size:15px;" class="edit-deal entypo-down-bold"></i>'];
            var BoardID = '{{$Board[0]->BoardID}}';
            var board = $('#board-start');
            var nicescroll_default = {cursorcolor:'#d4d4d4',
                cursoropacitymax:0.7,
                oneaxismousemode:false,
                cursorcolor: '#d4d4d4',
                cursorborder: '1px solid #ccc',
                railpadding: {right: 3},
                cursorborderradius: 1,
                autohidemode: true,
                sensitiverail: true};



            $('#board-start').niceScroll(nicescroll_default);
            getTask();
            data_table = $("#taskGrid").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/task/"+BoardID+"/ajax_task_grid",
                "fnServerParams": function (aoData) {
                    aoData.push(
                        {"name": "taskName", "value": $searchFilter.taskName},
                        {"name": "AccountOwner","value": $searchFilter.AccountOwner},
                        {"name": "Priority","value": $searchFilter.Priority},
                        {"name": "DueDateFilter","value": $searchFilter.DueDateFilter},
                        {"name": "DueDate","value": $searchFilter.DueDate},
                        {"name": "TaskStatus","value": $searchFilter.TaskStatus}
                    );
                },
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[1, 'desc']],
                "aoColumns": [
                    {
                        "bSortable": true, //Subject
                        mRender: function (id, type, full) {
                            return full[7];
                        }
                    },
                    {
                        "bSortable": true, //Due Date
                        mRender: function (id, type, full) {
                            return full[9];
                        }
                    },
                    {
                        "bSortable": true, //Status
                        mRender: function (id, type, full) {
                            return full[1];
                        }
                    },
                    {
                        "bSortable": true, //Priority
                        mRender: function (id, type, full) {

                            return Priority[full[11]-1]+full[12];
                        }
                    },
                    {
                        "bSortable": true, //Assign To
                        mRender: function (id, type, full) {
                            return full[4];
                        }
                    },
                    {
                        "bSortable": true, //Related To
                        mRender: function (id, type, full) {
                            return full[6];
                        }
                    },
                    {
                        "bSortable": false, //action
                        mRender: function (id, type, full) {
                            action = '<div class = "hiddenRowData" >';
                            for(var i = 0 ; i< task.length; i++){
                                action += '<input type = "hidden"  name = "' + task[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                            }
                            action += '</div>';
                            action += ' <a data-id="' + full[2] + '" class="edit-deal btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
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

                    if($('#tools .active').text()=='Grid'){
                        $('#taskGrid_wrapper').addClass('hidden');
                    }else{
                        $('#taskGrid').removeClass('hidden');
                    }
                }

            });

            $('#search-task-filter').submit(function(e){
                e.preventDefault();
                $searchFilter.taskName = $("#search-task-filter [name='taskName']").val();
                $searchFilter.AccountOwner = $("#search-task-filter [name='AccountOwner']").val();
                $searchFilter.Priority = $("#search-task-filter [name='Priority']").val();
                $searchFilter.DueDateFilter = $("#search-task-filter [name='DueDateFilter']").val();
                $searchFilter.DueDate = $("#search-task-filter [name='DueDate']").val();
                $searchFilter.TaskStatus = $("#search-task-filter [name='TaskStatus']").val();
                getTask();
                data_table.fnFilter('',0);
            });

            $(document).on('click','#board-start ul.sortable-list li i.edit-deal,#taskGrid .edit-deal',function(e){
                e.stopPropagation();
                if($(this).is('a')){
                    var rowHidden = $(this).prev('div.hiddenRowData');
                }else {
                    var rowHidden = $(this).parents('.tile-stats').children('div.row-hidden');
                }
                var select = ['UsersIDs','AccountIDs','TaskStatus','Priority','TaggedUser'];
                var color = ['BackGroundColour','TextColour'];
                for(var i = 0 ; i< task.length; i++){
                    var val = rowHidden.find('input[name="'+task[i]+'"]').val();
                    var elem = $('#edit-task-form [name="'+task[i]+'"]');
                    //console.log(task[i]+' '+val);
                    if(select.indexOf(task[i])!=-1){
                        if(task[i]=='TaggedUser' || task[i]=='AccountIDs') {
                            $('#edit-task-form [name="' + task[i] + '[]"]').select2('val', val.split(','));
                        } else {
                            elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption(val);
                        }
                    } else{
                        elem.val(val);
                        if(color.indexOf(task[i])!=-1){
                            /*setcolor(elem,val);*/
                        }else if(task[i]=='Tags'){
                            elem.val(val).trigger("change");
                        }
                    }
                }
                $('#edit-modal-task h4').text('Edit Task');
                $('#edit-modal-task').modal('show');
            });

            $('#tools .toggle').click(function(){
                if($(this).hasClass('list')){
                    $(this).addClass('active');
                    $(this).siblings('.toggle').removeClass('active');
                    $('#board-start').addClass('hidden');
                    $('#taskGrid_wrapper,#taskGrid').removeClass('hidden');
                }else{
                    $(this).addClass('active');
                    $(this).siblings('.toggle').removeClass('active');
                    $('#board-start').removeClass('hidden');
                    $('#taskGrid_wrapper,#taskGrid').addClass('hidden');
                }
            });

            $(document).on('click','#board-start ul.sortable-list li',function(){
                $('#add-task-comments-form').trigger("reset");
                var rowHidden = $(this).children('div.row-hidden');
                $('#allComments,#attachments').empty();
                var taskID = rowHidden.find('[name="TaskID"]').val();
                var accountID = rowHidden.find('[name="AccountID"]').val();
                var taskName = rowHidden.find('[name="taskName"]').val();
                $('#add-task-comments-form [name="TaskID"]').val(taskID);
                $('#add-task-attachment-form [name="TaskID"]').val(taskID);
                $('#add-task-attachment-form [name="AccountID"]').val(accountID);
                $('#add-task-comments-form [name="AccountID"]').val(accountID);
                $('#add-view-modal-task-comments h4.modal-title').text(taskName);
                getComments();
                getTaskAttachment();
                $('#add-view-modal-task-comments').modal('show');
            });

            $('#add-task-comments-form').submit(function(e){
                e.preventDefault();
                var formData = new FormData($('#add-task-comments-form')[0]);
                var url = baseurl + '/taskcomment/create';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-task-comments-form').trigger("reset");
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#commentadd").button('reset');
                        $('#add-task-comments-form').trigger("reset");
                        $('#commentadd').siblings('.file-input-name').empty();
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

            $(document).on('change','#add-task-attachment-form input[type="file"]',function(){
                var taskID = $('#add-task-attachment-form [name="TaskID"]').val();
                var formData = new FormData($('#add-task-attachment-form')[0]);
                var url = baseurl + '/task/'+taskID+'/saveattachment';
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
                            $('#add-task-attachment-form').trigger("reset");
                            $('#attachment_processing').addClass('hidden');
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $('#add-task-attachment-form').trigger("reset");
                        $('#addattachmentop .file-input-name').empty();
                        getTaskAttachment();
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $(document).on('click','#attachments i.delete-file',function(){
                var con = confirm('Do you delete current attachment?');
                if(!con){
                    return true;
                }
                var taskID = $('#add-task-attachment-form [name="TaskID"]').val();
                var attachmentID = $(this).attr('data-id');
                var url = baseurl + '/task/'+taskID+'/deleteattachment/'+attachmentID;
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
                        getTaskAttachment();
                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $('#addTtachment').click(function(){
                $('#filecontrole').click();
            });

            $(document).on('change','#filecontrole',function(e){
                var files = e.target.files;
                var fileText = '';
                for(i=0;i<files.length;i++){
                    fileText+=files[i].name+'<br>';
                }
                $('#commentadd').siblings('.file-input-name').html(fileText);
            });
            $(document).on('click','.viewattachments',function(){
                $(this).siblings('.comment-attachment').toggleClass('hidden');
            });

            $('#search-task-filter [name="DueDateFilter"]').change(function(){
                if($(this).val()==4){
                    var datefliter = $('#search-task-filter [name="DueDate"]');
                    datefliter.removeClass('hidden');
                }else{
                    var datefliter = $('#search-task-filter [name="DueDate"]');
                    datefliter.addClass('hidden');
                }
            });

            function initEnhancement(){
                var nicescroll_defaults = {
                    cursorcolor: '#d4d4d4',
                    cursorborder: '1px solid #ccc',
                    railpadding: {right: 3},
                    cursorborderradius: 1,
                    autohidemode: true,
                    sensitiverail: false
                };

                board.find('.board-column-list').niceScroll(nicescroll_defaults);
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

            function getTask(){
                var formData = new FormData($('#search-task-filter')[0]);
                var url = baseurl + '/task/'+BoardID+'/ajax_task_board';
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
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function getComments(){
                var taskID = $('#add-task-comments-form [name="TaskID"]').val();
                var url = baseurl +'/taskcomments/'+taskID+'/ajax_taskcomments';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#allComments').html(response);
                        var nicescroll_defaults = {
                            cursorcolor: '#d4d4d4',
                            cursorborder: '1px solid #ccc',
                            railpadding: {right: 3},
                            cursorborderradius: 1,
                            autohidemode: true,
                            sensitiverail: false
                        };
                        $('#allComments .fancyscroll').niceScroll(nicescroll_defaults);

                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function getTaskAttachment(){
                var taskID = $('#add-task-comments-form [name="TaskID"]').val();
                var url = baseurl +'/task/'+taskID+'/ajax_getattachments';
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
                var url = baseurl + '/taskboardcolumn/{{$BoardID}}/ajax_datacolumn';
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
                url = baseurl + '/task/'+BoardID+'/updateColumnOrder';
                var formData = new FormData($('#cardorder')[0]);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            getTask();
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
                console.log(BoardColumnID);
                $('#cardorder [name="cardorder"]').val(selectedCards);
                $('#cardorder [name="BoardColumnID"]').val(BoardColumnID);
            }

            function setcolor(elem,color){
                elem.colorpicker('destroy');
                elem.val(color);
                elem.colorpicker({color:color});
                elem.siblings('.input-group-addon').find('.color-preview').css('background-color', color);
            }
        });
    </script>
</div>
@include('taskboards.taskmodal')
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="edit-modal-task">
        <div class="modal-dialog" style="width: 70%;">
            <div class="modal-content">
                <form id="edit-task-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Task</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-12 text-left">
                                <label for="field-5" class="control-label col-sm-2">Tag User</label>
                                <div class="col-sm-10" style="padding: 0px 10px;">
                                    <?php unset($account_owners['']); ?>
                                    {{Form::select('TaggedUser[]',$account_owners,[],array("class"=>"select2","multiple"=>"multiple"))}}
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Task Status *</label>
                                    <div class="col-sm-8">
                                        {{Form::select('TaskStatus',$taskStatus,'',array("class"=>"selectboxit"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Assign To*</label>
                                    <div class="col-sm-8">
                                        {{Form::select('UsersIDs',$account_owners,'',array("class"=>"select2"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Task Subject *</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="Subject" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Due Date</label>
                                    <div class="col-sm-8">
                                        <input autocomplete="off" type="text" name="DueDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Company</label>
                                    <div class="col-sm-8">
                                        {{Form::select('AccountIDs',$leadOrAccount,'',array("class"=>"select2","multiple"=>"multiple"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Priority*</label>
                                    <div class="col-sm-8">
                                        {{Form::select('Priority',$priority,'',array("class"=>"selectboxit"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Description</label>
                                    <div class="col-sm-10">
                                        <textarea name="Description" class="form-control description autogrow resizevertical"> </textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="TaskID">
                        <button type="submit" id="task-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

    <div class="modal fade" id="add-view-modal-task-comments" data-backdrop="static">
        <div id="card-features-details" class="modal-dialog">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Task Name</h4>
                    </div>
                    <div class="modal-body">
                        <form id="add-task-comments-form" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <div class="col-md-12 text-left">
                                    <h4>Add Comment</h4>
                                </div>
                                <div class="col-md-12">
                                    <textarea class="form-control resizevertical" name="CommentText" placeholder="Write a comment."></textarea>
                                    <p class="comment-box-options">
                                        <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)">
                                            <i class="entypo-attach"></i>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-sm-6 pull-left end-buttons" style="text-align: left;">
                                    <label for="field-5" class="control-label">Send Mail To Customer:</label>
                                    <span id="label-switch" class="make-switch switch-mini">
                                        <input name="PrivateComment" value="1" type="checkbox">
                                    </span>
                                </div>
                                <div class="col-sm-6 pull-right end-buttons" style="text-align: right;">
                                    <input type="hidden" name="TaskID" >
                                    <input type="hidden" name="AccountID" >
                                    <button data-loading-text="Loading..." id="commentadd" class="add btn btn-primary btn-sm btn-icon icon-left" type="submit" style="visibility: visible;">
                                        <i class="entypo-floppy"></i>
                                        Add Comment
                                    </button>
                                    <br>
                                    <input id="filecontrole" type="file" name="commentattachment[]" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple="1" data-label="<i class='entypo-attach'></i>Attachments" />&nbsp;
                                </div>
                            </div>
                        </form>
                        <br>
                        <div id="allComments" class="form-group">

                        </div>
                        <div id="attachments" class="form-group">
                        </div>
                        <div id="attachment_processing" class="dataTables_processing hidden">Processing...</div>
                        <form id="add-task-attachment-form" method="post" enctype="multipart/form-data">
                            <div class="col-md-12" id="addattachmentop" style="text-align: right;">
                                <input type="file" name="taskattachment[]" data-loading-text="Loading..." class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" multiple="1" data-label="<i class='entypo-attach'></i>Add Attachments" />
                                <input type="hidden" name="TaskID" >
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
