@extends('layout.main')

@section('content')


<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
    </li>
		<li>
        <a><span>{{rategenerators_dropbox($rategenerators->RateGeneratorId)}}</span></a>
    </li>
   <!-- <li class="active">
        <strong>{{!empty($rategenerator)?$rategenerator->RateGeneratorName:''}}</strong>
    </li>-->
    <li class="active">
        <strong>Edit Rate Generator</strong>
    </li>
    
</ol>
<h3> Update Rate Generator</h3>
<div class="float-right" >
@if($rategenerators->Status==1)
<button title="Deactivate" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/change_status/0" class="btn btn-default change_status btn-danger btn-sm" data-loading-text="Loading..."><i class="entypo-minus-circled"></i></button>    
@elseif($rategenerators->Status==0)   
<button title="Activate" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/change_status/1" class="btn btn-default change_status btn-success btn-sm" data-loading-text="Loading..."><i class="entypo-check"></i></button>
@endif
<a title="Delete" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/delete" data-redirect="{{URL::to('/rategenerators')}}" data-id="{{$rategenerators->RateGeneratorId}}" class="btn btn-default btn-sm delete_rate btn-danger"><i class="entypo-trash"></i></a>
@if($rategenerators->Status==1)
<div class="btn-group">
<button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Generate Rate Table <span class="caret"></span></button>
 <ul class="dropdown-menu dropdown-green" role="menu">
    <li><a href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/generate_rate_table/create" class="generate_rate create" >Create New Rate Table</a></li>
    <li><a href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/generate_rate_table/update" class="generate_rate update" data-trunk="{{$rategenerators->TrunkID}}" data-codedeck="{{$rategenerators->CodeDeckId}}" data-currency="{{$rategenerators->CurrencyID}}">Update Existing Rate Table</a></li>
  </ul>
  </div>
  @endif
<button type="button"  class="update_form btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>
    <a href="{{URL::to('/rategenerators')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</div>
<br>
<br>


<div class="clear  row">
    <div class="col-md-12">
        <form role="form" id="rategenerator-from" method="post" action="{{URL::to('rategenerators/'.$rategenerators->RateGeneratorId.'/update')}}" class="form-horizontal form-groups-bordered">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Detail
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">

                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="RateGeneratorName" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{$rategenerators->RateGeneratorName}}" />
                        </div>

                        <label class="col-sm-2 control-label">Rate Position</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="RatePosition" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{$rategenerators->RatePosition}}" />

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Use Average</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                {{Form::checkbox('UseAverage', 1,  $rategenerators->UseAverage );}}
                            </div>
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Trunk</label>
                        <div class="col-sm-4">
                            {{ Form::select('TrunkID', $trunks, $rategenerators->TrunkID , array("class"=>"select2")) }}
                        </div>


                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">CodeDeck</label>
                        <div class="col-sm-4">
                                {{ Form::select('codedeckid', $codedecklist,  $rategenerators->CodeDeckId, array_merge( array("class"=>"select2"),$array_op)) }}
                            @if(isset($array_op['disabled']) && $array_op['disabled'] == 'disabled')
                                <input type="hidden" name="codedeckid" readonly  value="{{$rategenerators->CodeDeckId}}">
                            @endif
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Use Preference</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                {{Form::checkbox('UsePreference', 1,  $rategenerators->UsePreference );}}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Currency</label>
                        <div class="col-sm-4">
                        <?php if($rategenerators->CurrencyID == ''){
                            unset($array_op['disabled']);
                        }
                        ?>

                                <!--{ Form::select('CurrencyID', $currencylist,  $rategenerators->CurrencyID, array_merge( array("class"=>"select2"),$array_op)) }}-->
                            {{Form::SelectControl('currency',0,$rategenerators->CurrencyID,($rategenerators->CurrencyID==''?0:1))}}
                            @if($rategenerators->CurrencyID !='')
                                <input type="hidden" name="CurrencyID" readonly  value="{{$rategenerators->CurrencyID}}">
                            @endif
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Policy</label>
                        <div class="col-sm-4">
                            {{ Form::select('Policy', LCR::$policy, $rategenerators->Policy , array("class"=>"select2")) }}
                        </div>
                    </div>
                    {{--<input type="hidden" name="GroupBy" value="Code">--}}

                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Group By</label>
                        <div class="col-sm-4">
                            {{ Form::select('GroupBy', array('Code'=>'Code','Desc'=>'Description'), $rategenerators->GroupBy , array("class"=>"select2")) }}
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Timezones</label>
                        <div class="col-sm-4">
                            {{ Form::select('Timezones[]', $Timezones, explode(',',$rategenerators->Timezones) , array("class"=>"select2 multiselect", "multiple"=>"multiple")) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Merge Rate By Timezones</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                {{Form::checkbox('IsMerge', 1,  $rategenerators->IsMerge, array('id' => 'IsMerge') );}}
                            </div>
                        </div>
                        <label class="col-sm-2 control-label IsMerge">Take Price</label>
                        <div class="col-sm-4 IsMerge">
                            {{ Form::select('TakePrice', array(RateGenerator::HIGHEST_PRICE=>'Highest Price',RateGenerator::LOWEST_PRICE=>'Lowest Price'), $rategenerators->TakePrice , array("class"=>"select2")) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label IsMerge">Merge Into</label>
                        <div class="col-sm-4 IsMerge">
                            {{ Form::select('MergeInto', $Timezones, $rategenerators->MergeInto , array("class"=>"select2")) }}
                        </div>
                    </div>

                </div>
            </div>

        </form>

        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Rules
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">

                            <div class="pull-right">

                                <a  href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/rule/add" class="btn addnew btn-primary btn-sm btn-icon icon-left" >
                                    <i class="entypo-floppy"></i>
                                    Add New
                                </a>
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Rules will be applied in the order they are setup. Keep * at the top." data-original-title="Add New Rule">?</span>
                                <br><br>
                            </div>

                    @if($rategenerator_rules)
                    <form id="RateRulesDataFrom" method="POST" />
                    <?php /*
                     <table class="table table-bordered datatable" id="table-4">
                        <thead>
                        <tr>
                            <th>Rate Filter</th>
                            <th>Sources</th>
                            <th>Margins</th>
                            <th>Action</th>
                        </tr>
                        </thead>

                         <tbody id="sortable">
                                @foreach($rategenerator_rules as $rategenerator_rule)
                                <tr class="odd gradeX" data-id="{{$rategenerator_rule->RateRuleId}}">
                                        <td>
                                            {{$rategenerator_rule->Code}}@if(!empty($rategenerator_rule->Code)) <br/> @endif
                        {{$rategenerator_rule->Description}}
                                </td>
                                <td>
                                    @if(count($rategenerator_rule['RateRuleSource']))
                        @foreach($rategenerator_rule['RateRuleSource'] as $rateruleSource )
                        {{Account::getCompanyNameByID($rateruleSource->AccountId);}}<br>
                                            @endforeach
                        @endif

                                </td>
                                <td>

                                    @if(count($rategenerator_rule['RateRuleMargin']))
                        @foreach($rategenerator_rule['RateRuleMargin'] as $index=>$materulemargin )
                        @if($materulemargin->Type == 1)
                        <?php $tmptype = 'Rate'; ?>
                        @else
                        <?php $tmptype = 'Connection Fee'; ?>
                        @endif
                        {{$tmptype}} | {{$materulemargin->MinRate}} {{$index!=0?'<':'<='}}  rate <= {{$materulemargin->MaxRate}} {{$materulemargin->AddMargin}} {{$materulemargin->FixedValue}} <br>

                                                @endforeach
                        @endif



                                </td>
                                <td>
                                    <a href="{{URL::to('/rategenerators/'.$id. '/rule/' . $rategenerator_rule->RateRuleId .'/edit' )}}" id="add-new-margin" class="update btn btn-primary btn-sm">
                                                <i class="entypo-pencil"></i>
                                            </a>

                                            <a href="{{URL::to('/rategenerators/'.$id. '/rule/' . $rategenerator_rule->RateRuleId .'/clone_rule' )}}" data-rate-generator-id="{{$id}}" id="clone-rule" class="clone_rule btn btn-default  btn-sm" data-original-title="Clone" title="" data-placement="top" data-toggle="tooltip" data-loading-text="...">
                                                <i class="fa fa-clone"></i>
                                            </a>

                                            <a href="{{URL::to('/rategenerators/'.$id. '/rule/' . $rategenerator_rule->RateRuleId .'/delete' )}}" class="btn delete btn-danger btn-sm" data-redirect="{{Request::url()}}">
                                                <i class="entypo-trash"></i>
                                            </a>
                                        </td>
                                    </tr>


                                @endforeach
                                </tbody>
                    </table> -->
                    */
                    ?>
                        <div class="dataTables_wrapper clear-both">
                            <table class="table table-bordered datatable" id="table-raterule">
                                <thead>
                                    <tr>
                                        <th class="hidden"></th>
                                        <th>Rate Filter</th>
                                        <th>Sources</th>
                                        <th>Margins</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable">
                                </tbody>


                            </table>
                        </div>
                    <input type="hidden" name="main_fields_sort" id="main_fields_sort" value="">
                    </form>
                    @endif

                </div>
            </div>
        </div>

    </div>
<style>
    #sortable tr:hover {
        cursor: all-scroll;
    }
    .IsMerge {
        display: none;
    }
</style>
{{--<script src="http://mpryvkin.github.io/jquery-datatables-row-reordering/1.2.3/jquery.dataTables.rowReordering.js"></script>--}}

<script type="text/javascript">
    jQuery(document).ready(function($) {
        /*$(".btn.addnew").click(function(ev) {
            jQuery('#modal-rate-generator-rule').modal('show', {backdrop: 'static'});
        });*/
       // $( "#sortable" ).sortable();

        /* ---------- DataTable Start ---------- */

        var service_edit_url = baseurl + "/rategenerators/{{$id}}/rule/{id}/edit";
        var service_delete_url = baseurl + "/rategenerators/{{$id}}/rule/{id}/delete";
        var service_datagrid_url = baseurl + "/rategenerators/ajax_rule_datagrid";
        var service_clone_url = baseurl + "/rategenerators/{{$id}}/rule/{id}/clone_rule";

        var RateGeneratorId='{{$id}}';

        data_table_raterule = $("#table-raterule").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide": true,
            "sAjaxSource": service_datagrid_url,
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                //console.log(aData[0]);
                $(nRow).attr('data-id', aData[0]);
            },
            "fnServerParams": function (aoData) {
                aoData.push({"name": "RateGeneratorId", "value": RateGeneratorId}
                );

                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name": "RateGeneratorId", "value": RateGeneratorId},
                        {"name":"Export","value":1});

            },
            "iDisplayLength": 10,
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
           // "aaSorting": [[0, 'desc']],
            "aoColumns": [
                {  "bSortable": false,"bVisible":false },
                {
                    "bSortable": false, //Account
                    mRender: function (id, type, full) {
                        var filter_ =  full[1];
                        var filter='';

                        if(typeof(filter_)!='undefined' && filter_!=null && filter_!=''){
                            filter= filter_.split('{br}').join('<br>');
                        }

                        return filter;
                    },

                }, //1   RateFilter
                {  "bSortable": true,

                    mRender: function (id, type, full) {
                        var source_ =  full[2];
                        var source='';
                        if(typeof(source_)!='undefined' && source_!=null && source_!=''){
                            var source= source_.split('{br}').join('<br>');
                        }

                        return source;
                    }


                },  // 1 Sources

                {  "bSortable": false,
                    mRender: function (id, type, full) {
                        var source_ =  full[3];
                        var source='';
                        if(typeof(source_)!='undefined' && source_!=null && source_!=''){
                            var source= source_.split('{br}').join('<br>');
                        }

                        return source;
                    }
                },  // 2 margins
                {                        // 3 Action
                    "bSortable": false,
                    mRender: function ( id, type, full ) {

                        action = '';
                        <?php if(User::checkCategoryPermission('AccountService','Edit')) { ?>

                        action += ' <a href="' + service_edit_url.replace("{id}",full[0]) +'" id="add-new-margin" class="update btn btn-primary btn-sm"><i class="entypo-pencil"></i></a>';
                        <?php } ?>
                                action += ' <a href="' + service_clone_url.replace("{id}",full[0]) +'"  data-rate-generator-id="{{$id}}" id="clone-rule" class="clone_rule btn btn-default  btn-sm" data-original-title="Clone" title="" data-placement="top" data-toggle="tooltip" data-loading-text="..."><i class="fa fa-clone"></i></a>';
                        <?php if(User::checkCategoryPermission('AccountService','Delete')) { ?>
                                action += ' <a href="' + service_delete_url.replace("{id}",full[0]) +'" class="btn delete btn-danger btn-sm" data-redirect="{{Request::url()}}"><i class="entypo-trash"></i></a>';
                        <?php } ?>
                                return action;
                    }
                }
            ],

            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                $("#table-service tbody input[type=checkbox]").each(function (i, el) {
                    var $this = $(el),
                            $p = $this.closest('tr');

                    $(el).on('change', function () {
                        var is_checked = $this.is(':checked');

                        $p[is_checked ? 'addClass' : 'removeClass']('selected');
                    });
                });


                $('#selectall').removeClass('hidden');

                //select all record
                $('#selectallbutton').click(function(){
                    if($('#selectallbutton').is(':checked')){
                        checked = 'checked=checked disabled';
                        $("#selectall").prop("checked", true).prop('disabled', true);
                        $('#table-service tbody tr').each(function (i, el) {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                        });
                    }else{
                        checked = '';
                        $("#selectall").prop("checked", false).prop('disabled', false);
                        $('#table-service tbody tr').each(function (i, el) {
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                            $(this).removeClass('selected');
                        });
                    }
                });

               /* $(".clone-service").click(function(e){
                    e.preventDefault();
                    var self = $(this);
                    var serviceid = self.attr('data-id');

                    $('#cloneservices-form').trigger("reset");
                    $('#modal-clone-services h4').html('Clone Services');
                    $('#cloneservicetable').find('tbody tr').each(function (i, el) {
                        var self = $(this);
                        if (self.hasClass('selected')) {
                            $(this).find('input[type="checkbox"]').prop("checked", false);
                            $(this).removeClass('selected');
                        }
                    });
                    $("#cloneservices-form").find("input[name='criteria']").val('');
                    $("#cloneservices-form").find("input[name='CloneID']").val(serviceid);
                    $('#modal-clone-services').modal('show');

                    initCustomerGrid();

                }); */


                $(".btn.clone_rule").click(function (e) {

                    e.preventDefault();
                    $(this).button('loading');
                    var url = $(this).attr('href');
                    var rate_generator_id = $(this).attr('data-rate-generator-id');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {

                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                var new_rule_url = baseurl + '/rategenerators/' + rate_generator_id + '/rule/' + response.RateRuleID + '/edit';

                                setTimeout( function() {  window.location = new_rule_url } ,1000 );
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $(".btn.clone_rule").button('reset');


                        },

                        // Form data
                        //data: {},
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    return false;

                });



            }



        });


       // data_table_raterule.rowReordering();


        /* ---------- DataTable End ------------ */



        function initSortable(){
            // Code using $ as usual goes here.
            $('#sortable').sortable({
                connectWith: '#sortable',
                placeholder: 'placeholder',
                start: function() {
                    //setting current draggable item
                    currentDrageable = $('#sortable');
                },
                stop: function(ev,ui) {
                    saveOrder();
                    //de-setting draggable item after submit order.
                    currentDrageable = '';
                }
            });
        }
        function saveOrder() {

            var Ticketfields_array   = 	new Array();
            $('#sortable tr').each(function(index, element) {
                var TicketfieldsSortArray  =  {};
                TicketfieldsSortArray["data_id"] = $(element).attr('data-id');
                TicketfieldsSortArray["Order"] = index+1;

                Ticketfields_array.push(TicketfieldsSortArray);
            });
            var data_sort_fields =  JSON.stringify(Ticketfields_array);
            $('#main_fields_sort').val(data_sort_fields);
            $('#RateRulesDataFrom').submit();
        }

        $('#RateRulesDataFrom').submit(function(e){
            e.stopPropagation();
            e.preventDefault();

            var formData = new FormData($(this)[0]);
            var url		 = baseurl + '/rategenerators/update_fields_sorting';

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

        initSortable();

        $(".update_form.btn").click(function(ev) {
            $("#rategenerator-from").submit();
        });

		        $(".btn.change_status").click(function (e) {
                        //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                        $(this).button('loading');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $(this).button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    location.reload();
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },

                            // Form data
                            //data: {},
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                        return false;
                    });
					
					$(".generate_rate.create").click(function (e) {
                        e.preventDefault();
                        $('#update-rate-generator-form').trigger("reset");
                        $('#modal-update-rate').modal('show', {backdrop: 'static'});
                        $('.radio-replace').removeClass('checked');
                        $('#defaultradiorate').addClass('checked');
                        $('#RateTableIDid').hide();
                        $('#RateTableNameid').show();
                        $('#RateTableReplaceRate').hide();
                        $('#RateTableEffectiveRate').show();
                        $('#modal-update-rate h4').html('Generate Rate Table');
                        update_rate_table_url = $(this).attr("href");

                        return false;

                    });
					
		$('body').on('click', '.generate_rate.update', function (e) {
            e.preventDefault();
            $('#modal-update-rate').modal('show', {backdrop: 'static'});
            $('#update-rate-generator-form').trigger("reset");
            var trunkID = $(this).attr("data-trunk");
            var codeDeckId = $(this).attr("data-codedeck");
            var CurrencyID = $(this).attr("data-currency");
            $.ajax({
                url: baseurl + "/rategenerators/ajax_load_rate_table_dropdown",
                type: 'GET',
                dataType: 'text',
                success: function(response) {

                    $("#modal-update-rate #DropdownRateTableID").html('');
                    $("#modal-update-rate #DropdownRateTableID").html(response);
                    $("#modal-update-rate #DropdownRateTableID select.select2").addClass('visible');
                    $("#modal-update-rate #DropdownRateTableID select.select2").select2();

                },
                // Form data
                data: "TrunkID="+trunkID+'&CodeDeckId='+codeDeckId+'&CurrencyID='+CurrencyID ,
                cache: false,
                contentType: false,
                processData: false
            });
            /*
            * Submit and Generate Joblog
            * */
            update_rate_table_url = $(this).attr("href");

            $('.radio-replace').removeClass('checked');
            $('#defaultradiorate').addClass('checked');

            $('#RateTableIDid').show();
            $('#RateTableReplaceRate').show();
            $('#RateTableEffectiveRate').show();
            $('#RateTableNameid').hide();
            $('#modal-update-rate h4').html('Update Rate Table');
        });
		
		$('#update-rate-generator-form').submit(function (e) {
            e.preventDefault();
            if( typeof update_rate_table_url != 'undefined' && update_rate_table_url != '' ){
                $.ajax({
                    url: update_rate_table_url,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $(".btn.generate").button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            reloadJobsDrodown(0);
                            $('#modal-update-rate').modal('hide');
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".btn.generate").button('reset');
                        $(".save.TrunkSelect").button('reset');

                    },
                    // Form data
                    data: $('#update-rate-generator-form').serialize(),
                    cache: false

                });
            }else{
                $(".btn").button('reset');
                $('#modal-update-rate').modal('hide');
                toastr.info('Nothing Changed. Try again', "info", toastr_opts);
            }
        });
		
		
		$(".btn.delete_rate").click(function (e) {
                        e.preventDefault();
                        var id = $(this).attr('data-id');
                        var url = baseurl + '/rategenerators/'+id+'/ajax_existing_rategenerator_cronjob';
                        $('#delete-rate-generator-form [name="RateGeneratorID"]').val(id);
                        if(confirm('Are you sure you want to delete selected rate generator?')) {
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'html',
                                success: function (response) {
                                    $(".btn.delete_rate").button('reset');
                                    if (response) {
                                        $('#modal-delete-rategenerator .container').html(response);
                                        $('#modal-delete-rategenerator').modal('show');
                                    }else{
                                        $('#delete-rate-generator-form').submit();
                                    }
                                },

                                // Form data
                                //data: {},
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }
                        return false;

                    });
        /*$(".btn.clone_rule").click(function (e) {

            e.preventDefault();
            $(this).button('loading');
            var url = $(this).attr('href');
            var rate_generator_id = $(this).attr('data-rate-generator-id');

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {

                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                             var new_rule_url = baseurl + '/rategenerators/' + rate_generator_id + '/rule/' + response.RateRuleID + '/edit';

                            setTimeout( function() {  window.location = new_rule_url } ,1000 );
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".btn.clone_rule").button('reset');


                    },

                    // Form data
                    //data: {},
                    cache: false,
                    contentType: false,
                    processData: false
                });
            return false;

        });*/

					
        $('#delete-rate-generator-form').submit(function (e) {
            e.preventDefault();
            if($('#modal-delete-rategenerator .container').is(':empty')) {
                var RateGeneratorID = $(this).find('[name="RateGeneratorID"]').val();
                var url = baseurl + '/rategenerators/' + RateGeneratorID + '/delete';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);                          
                            $('#modal-delete-rategenerator').modal('hide');
							window.location = "{{URL::to('/rategenerators')}}";
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".save.TrunkSelect").button('reset');

                    },
                    // Form data
                    data: $('#update-rate-generator-form').serialize(),
                    cache: false

                });
            }else{
                var SelectedIDs = getselectedIDs("cronjob-table");
                if (SelectedIDs.length == 0) {
                    alert('No cron job selected.');
                    $("#rategenerator-select").button('reset');
                    return false;
                }else{
                    var deleteid = SelectedIDs.join(",");
                    cronjobsdelete(deleteid);
                }
            }
        });

        function cronjobsdelete(deleteid){
            if(confirm('Are you sure you want to delete selected cron job?')){
                var rateGeneratorID = $('#delete-rate-generator-form [name="RateGeneratorID"]').val();
                var url = baseurl + "/rategenerators/"+rateGeneratorID+"/deletecronjob";
                var cronjobs = deleteid;
                $('#modal-delete-rategenerator .container').html('');
                $('#modal-delete-rategenerator').modal('hide');
                $.ajax({
                    url: url,
                    type:'POST',
                    data:{cronjobs:cronjobs},
                    datatype:'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            toastr.success(response.message,'Success', toastr_opts);
                            var url = baseurl + '/rategenerators/'+rateGeneratorID+'/ajax_existing_rategenerator_cronjob';
                            $('#delete-rate-generator-form [name="RateGeneratorID"]').val(rateGeneratorID);
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'html',
                                success: function (response) {
                                    $(".btn.delete_rate").button('reset');
                                    if (response) {
                                        $('#modal-delete-rategenerator .container').html(response);
                                        $('#modal-delete-rategenerator').modal('show');
                                    }else{
                                        $('#delete-rate-generator-form').submit();
                                    }
                                },

                                // Form data
                                //data: {},
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }

                });
            }
        }
		
		   function getselectedIDs(table){
            var SelectedIDs = [];
            $('#'+table+' tr .rowcheckbox:checked').each(function (i, el) {
                var cronjob = $(this).val();
                SelectedIDs[i++] = cronjob;
            });
            return SelectedIDs;
        }
		
		  $(document).on('click','#selectall',function(){
            if($(this).is(':checked')){
                checked = 'checked=checked';
                $(this).prop("checked", true);
                $(this).parents('table').find('tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                    $(this).addClass('selected');
                });
            }else{
                checked = '';
                $(this).prop("checked", false);
                $(this).parents('table').find('tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", false);
                    $(this).removeClass('selected');
                });
            }
        });

        // when page load check if IsMerge checked or not
        if($('#IsMerge').is(':checked')) {
            $('.IsMerge').show();
        } else {
            $('.IsMerge').hide();
        }
        $('#IsMerge').on('change', function(e, s) {
            if($('#IsMerge').is(':checked')) {
                $('.IsMerge').show();
            } else {
                $('.IsMerge').hide();
            }
        });
    });
</script>
@include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('rategenerators/'.$rategenerators->RateGeneratorId.'/update')))

@include('includes.errors')
@include('includes.success')
@include('currencies.currencymodal')
@stop
@section('footer_ext') @parent
@include('rategenerators.rategenerator_models')
@stop