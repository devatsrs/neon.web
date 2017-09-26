@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="codedesk_filter">
                <div class="form-group">
                    <label class="control-label hide_country" for="field-1">Country</label>
                    <div class="hide_country">
                        {{ Form::select('ft_country', $countries, Input::get('Country') , array("class"=>"select2")) }}
                        <input name="ft_codedeckid" value="{{$id}}" type="hidden" >
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Code</label>
                    <input type="text" name="ft_code" class="form-control">
                </div>
                <div class="form-group">
                    <label class="control-label">Description</label>
                    <input type="text" name="ft_description" class="form-control">
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
            <a href="{{URL::to('codedecks')}}">Code Decks</a>
    </li>
    <li>
        <a><span>{{basecodedeck_dropbox($id)}}</span></a>
    </li>
    <li class="active">
        <strong>{{$CodeDeckName}}</strong>
    </li>
</ol>
<h3>Code Decks</h3>

@include('includes.errors')
@include('includes.success')


<!--<script src="{{URL::to('/')}}/assets/js/neon-fileupload.js" type="text/javascript"></script>-->

<div style="text-align: right;padding:10px 0 ">

    @if( User::checkCategoryPermission('CodeDecks','Upload') )
        <a href="javascript:;" id="upload-codedeck" class="btn btn-sm upload btn-primary ">
            <i class="entypo-upload"></i>
            Upload
        </a>
    @endif
    @if(User::checkCategoryPermission('CodeDecks','Add') )
        <a href="javascript:;" id="add-new-code" class="btn btn-sm btn-primary ">
            <i class="entypo-plus"></i>
            Add New
        </a>
    @endif

    @if( User::checkCategoryPermission('CodeDecks','Edit'))
    <a href="javascript:;"  id="changeSelectedCodedeck" class="btn btn-primary btn-sm btn-icon icon-left" onclick="jQuery('#modal-6').modal('show', {backdrop: 'static'});" href="javascript:;">
        <i class="entypo-floppy"></i>
        Change Selected
    </a>
    @endif
    @if( User::checkCategoryPermission('CodeDecks','Delete'))
    <button type="submit" id="delete-bulk-codedeck-selected" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-trash"></i>
        Delete Selected
    </button>
    <!--<button type="submit" id="delete-bulk-codedeck-all" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Delete All Codedeck
    </button>-->
    @endif

</div>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="codedeck[]" class="" />
            <!--<button type="button" id="selectallbutton"  class="btn btn-primary btn-xs" title="Select All Codedeck" alt="Select All Codedeck"><i class="entypo-check"></i></button>-->
        </th>
        <th width="10%">ISO Code</th>
        <th width="15%" class="hide_country">Country</th>
        <th width="15%">Code</th>
        <th width="20%">Description</th>
        <th width="10%">Interval 1</th>
        <th width="10%">Interval N</th>
        <th width="15%">Actions</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var checked='';
var postdata;
    jQuery(document).ready(function ($) {

        $('#filter-button-toggle').show();

        public_vars.$body = $("body");
        //show_loading_bar(40);
        $("#codedesk_filter").submit(function(e) {
        e.preventDefault();

        $searchFilter.ft_country = $("#codedesk_filter [name='ft_country']").val();
        $searchFilter.ft_code = $("#codedesk_filter [name='ft_code']").val();
        $searchFilter.ft_description = $("#codedesk_filter [name='ft_description']").val();
        $searchFilter.ft_codedeckid = $("#codedesk_filter [name='ft_codedeckid']").val();

        if($searchFilter.ft_codedeckid == ''){
            ShowToastr("error",'Please Select Codedeck');
            return false;
        }


        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/codedecks/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "fnServerParams": function(aoData) {
                aoData.push({"name":"ft_country","value":$searchFilter.ft_country},{"name":"ft_code","value":$searchFilter.ft_code},{"name":"ft_description","value":$searchFilter.ft_description},{"name":"ft_codedeckid","value":$searchFilter.ft_codedeckid});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"ft_country","value":$searchFilter.ft_country},{"name":"ft_code","value":$searchFilter.ft_code},{"name":"ft_description","value":$searchFilter.ft_description},{"name":"ft_codedeckid","value":$searchFilter.ft_codedeckid});
            },
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[3, 'asc']],
            "aoColumns":
            [
                {"bSortable": false, //RateID
                    mRender: function(id, type, full) {
                        return '<div class="checkbox "><input type="checkbox" name="codedeck[]" value="' + id + '" class="rowcheckbox" ></div>';
                    }
                },
                {  "bSortable": true }, //ISO Code
                { "bSortable": true }, //Country
                { "bSortable": true }, //Code
                { "bSortable": true }, //Description
                { "bSortable": true }, //Interval1
                { "bSortable": true }, //IntervalN
                {
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                        edit_ = "{{ URL::to('codedecks/{id}/edit')}}";

                        edit_ = edit_.replace( '{id}', full[0] );

                        RateID = full[0];
                        ISO_CODE = full[1];
                        country = full[2];
                        code = full[3];
                        description = full[4];
                        Interval1 = ( full[5] == null )? 1:full[5];
                        IntervalN = ( full[6] == null )? 1:full[6];
                        action = '<div class = "hiddenRowData" >';
                        action += '<input type = "hidden"  name = "RateID" value = "' + RateID + '" / >';
                        action += '<input type = "hidden"  name = "Country" value = "' + country + '" / >';
                        action += '<input type = "hidden"  name = "Code" value = "' + code + '" / >';
                        action += '<input type = "hidden"  name = "Description" value = "' + description + '" / >';
                        action += '<input type = "hidden"  name = "Interval1" value = "' +  Interval1 + '" / >' ;
                        action += '<input type = "hidden"  name = "IntervalN" value = "' +  IntervalN + '" / >' ;
                        action += '</div>';

                        <?php if(User::checkCategoryPermission('CodeDecks','Edit')){ ?>
                            action += ' <a href="javascript:;" title="Edit" class="edit-codedeck btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('CodeDecks','Delete') ){ ?>
                            action += ' <a data-id="'+ RateID +'" title="Delete" class="delete-codedecks btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
                        <?php } ?>

                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/codedecks/exports/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/codedecks/exports/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteCodeDecksSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteCodeDecks = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/codedecks/"+id+"/delete" ,"",FnDeleteCodeDecksSuccess );
                       }
                       return false;
                   }
                   $(".delete-codedecks").click(FnDeleteCodeDecks); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });

                   $('#table-4 tbody tr').each(function(i, el) {
                       if (checked!='') {
                           $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                           $(this).addClass('selected');
                           $('#selectallbutton').prop("checked", true);
                       } else {
                           $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);;
                           $(this).removeClass('selected');
                       }
                   });
                   $('#selectallbutton').click(function(ev) {
                       if($(this).is(':checked')){
                           checked = 'checked=checked disabled';
                           $("#selectall").prop("checked", true).prop('disabled', true);
                           if(!$('#changeSelectedInvoice').hasClass('hidden')){
                               $('#table-4 tbody tr').each(function(i, el) {
                                   $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                   $(this).addClass('selected');
                               });
                           }
                       }else{
                           checked = '';
                           $("#selectall").prop("checked", false).prop('disabled', false);
                           if(!$('#changeSelectedInvoice').hasClass('hidden')){
                               $('#table-4 tbody tr').each(function(i, el) {
                                   $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                   $(this).removeClass('selected');
                               });
                           }
                       }
                   });
           }

        });
        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
    });


        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Highlighted rows
        $("#table-2 tbody input[type=checkbox]").each(function (i, el) {
            var $this = $(el),
                $p = $this.closest('tr');

            $(el).on('change', function () {
                var is_checked = $this.is(':checked');

                $p[is_checked ? 'addClass' : 'removeClass']('highlight');
            });
        });

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $("#form-upload").submit(function () {
           // return false;
            var formData = new FormData($('#form-upload')[0]);
            show_loading_bar(100);
            $.ajax({
                url: baseurl + '/codedecks/upload',  //Server script to process data
                type: 'POST',
                dataType: 'json',
               /* xhr: function() {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    if(myXhr.upload){ // Check if upload property exists
                        myXhr.upload.addEventListener('progress',function(e){
                            if (e.lengthComputable) {
                                //$('progress').attr({value:e.loaded,max:e.total});
                            }
                        }, false); // For handling the progress of the upload
                    }
                    return myXhr;
                },*/
                //Ajax events
                beforeSend: function(){
                    $('.btn.upload').button('loading');
                },
                afterSend: function(){
                    console.log("Afer Send");
                },
                success: function (response) {

                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#upload-modal-codedeck').modal('hide');
                        reloadJobsDrodown(0);

                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $('.btn.upload').button('reset');
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
    $("#selectall").click(function(ev) {
        var is_checked = $(this).is(':checked');
        $('#table-4 tbody tr').each(function(i, el) {
            if (is_checked) {
                $(this).find('.rowcheckbox').prop("checked", true);
                $(this).addClass('selected');
            } else {
                $(this).find('.rowcheckbox').prop("checked", false);
                $(this).removeClass('selected');
            }
        });
    });
    $('#table-4 tbody').on('click', 'tr', function() {
        if (checked =='') {
            $(this).toggleClass('selected');
            if ($(this).hasClass('selected')) {
                $(this).find('.rowcheckbox').prop("checked", true);
            } else {
                $(this).find('.rowcheckbox').prop("checked", false);
            }
        }
    });
    $("#changeSelectedCodedeck").click(function(ev) {
        var Codedecks = [];
        var i = 0;
        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
            console.log($(this).val());
            Codedeck = $(this).val();
            Codedecks[i++] = Codedeck;
        });
        if(Codedecks.length){
            $("#bulk-edit-codedeck-form [name='CountryID']").select2().select2('val','');
            $('#bulk-edit-codedeck-form').trigger("reset");
            $('#modal-Codedeck').modal('show', {backdrop: 'static'});
        }

    });
    $("#delete-bulk-codedeck-selected").click(function(ev) {
        var criteria = '';
        var Codedecks = [];
        var i = 0;
        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
            console.log($(this).val());
            Codedeck = $(this).val();
            Codedecks[i++] = Codedeck;
        });
        if($('#selectallbutton').is(':checked')){
            update_new_url = baseurl + '/codedecks/delete_all';
            postdata = $.param($searchFilter);
            if(postdata != ''){
                result = confirm("Are you Sure?");
                if(result){
                    bulk_update(update_new_url,postdata);
                }
            }
        }else {
            if(Codedecks.length){
                result = confirm("Are you Sure?")
                if(result) {
                    update_new_url = baseurl + '/codedecks/delete_selected';
                    bulk_update(update_new_url, 'CodeDecks=' + Codedecks+'&CodeDeckID='+$searchFilter.ft_codedeckid);
                }
            }
        }

    });
    $("#delete-bulk-codedeck-all").click(function(ev) {
        update_new_url = baseurl + '/codedecks/delete_all';
        postdata = $.param($searchFilter);
        if(postdata != ''){
        result = confirm("Are you Sure?");
            if(result){
                bulk_update(update_new_url,postdata);
            }
        }
    });
    $("#bulk-edit-codedeck-form").submit(function(e) {
        e.preventDefault();
        update_new_url = baseurl + '/codedecks/delete_all';
        var criteria = '';
        var Codedecks = [];
        if($('#selectallbutton').is(':checked')){
            criteria = JSON.stringify($searchFilter);
        }else{
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                console.log($(this).val());
                Codedeck = $(this).val();
                Codedecks[i++] = Codedeck;
            });
        }
        if(Codedecks.length!='' || criteria!=''){
                update_new_url = baseurl + '/codedecks/update_selected';
                bulk_update(update_new_url,'CodeDecks='+Codedecks+'&criteria='+criteria+'&'+$('#bulk-edit-codedeck-form').serialize());
        }

        return false;
    });

    $('#upload-codedeck').click(function(ev){
        ev.preventDefault();
        $('#upload-modal-codedeck').modal('show');
    });
    $('#add-new-code').click(function(ev){
        ev.preventDefault();
        $('#add-new-codedeck-form').trigger("reset");
        $("#add-new-codedeck-form [name='CountryID']").select2().select2('val','');
        $("#add-new-codedeck-form [name='RateID']").val('');
        $('#add-new-modal').modal('show');
    });
    $('table tbody').on('click','.edit-codedeck',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        var prev_raw = $(this).prev("div.hiddenRowData");
        $('#add-new-codedeck-form').trigger("reset");
        $("#add-new-codedeck-form [name='RateID']").val(prev_raw.find("input[name='RateID']").val());
        $("#add-new-codedeck-form [name='Description']").val(prev_raw.find("input[name='Description']").val());
        $("#add-new-codedeck-form [name='Interval1']").val(prev_raw.find("input[name='Interval1']").val());
        $("#add-new-codedeck-form [name='IntervalN']").val(prev_raw.find("input[name='IntervalN']").val());
        var countryid = $('select[name="ft_country"] > option:contains("'+prev_raw.find("input[name='Country']").val()+'")').val()
        $("#add-new-codedeck-form [name='CountryID']").select2().select2('val',countryid);
        $("#add-new-codedeck-form [name='Code']").val(prev_raw.find("input[name='Code']").val());

        $('#add-new-modal').modal('show');
    });
    $("#add-new-codedeck-form").submit(function(e) {
        e.preventDefault();
        var rateid = $("#add-new-codedeck-form [name='RateID']").val()
        if( typeof rateid != 'undefined' && rateid != ''){
            update_new_url = baseurl + '/codedecks/update/'+rateid;
        }else{
            update_new_url = baseurl + '/codedecks/store';
        }

        bulk_update(update_new_url,$("#add-new-codedeck-form").serialize());

    });

        $(".numbercheck").keypress(function (e) {
            //if the letter is not digit then display error and don't type anything
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                //display error message
                return false;
            }
        });


    });

function bulk_update(fullurl,data){
//alert(data)
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#codedeck-update").button('reset');
            $(".btn").button('reset');

            if (response.status == 'success') {
                $('#modal-Codedeck').modal('hide');
                $('#add-new-modal').modal('hide');
                toastr.success(response.message, "Success", toastr_opts);
                if( typeof data_table !=  'undefined'){
                    data_table.fnFilter('', 0);
                }
            } else {
                toastr.error(response.message, "Error", toastr_opts);
            }
        },
        data: data,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false
    });
}

</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
#selectcheckbox{
    padding: 15px 10px;
}
</style>
@stop


@section('footer_ext')
@parent
<div class="modal fade" id="modal-fileformat">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Code Decks File Format</h4>
            </div>



            <div class="modal-body">
            <p>All columns are mandatory and the first line should have the column headings.</p>
                        <table class="table responsive">
                            <thead>
                                <tr>
                                    <th class="hide_country">Country(Optional)</th>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                    <th>Interval1(Opt.)</th>
                                    <th>IntervalN(Opt.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="hide_country">Afghanistan</td>
                                    <td>9379 </td>
                                    <td>Afghanistan Cellular-Others</td>
                                    <td>I <span data-original-title="Insert" data-content="When action is set to 'I', It will insert new CodeDeck" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                    <td>1</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td class="hide_country">Afghanistan</td>
                                    <td>9377 </td>
                                    <td>Afghanistan Cellular-Areeba</td>
                                    <td>U <span data-original-title="Insert" data-content="When action is set to 'U',It will replace existing CodeDeck" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                    <td>1</td>
                                   <td>1</td>
                                </tr>
                                <tr>
                                    <td class="hide_country">Afghanistan</td>
                                    <td> 9378 </td>
                                    <td>Afghanistan Cellular-Etisalat</td>
                                    <td>D <span data-original-title="Insert" data-content="When action is set to 'D',It will delete existing CodeDeck" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                    <td>1</td>
                                    <td>1</td>
                                </tr>
                            </tbody>
                        </table>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-Codedeck">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="bulk-edit-codedeck-form" method="post">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Codedeck</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group hide_country">
                                <input type="checkbox" name="updateCountryID" class="" />
                                <label for="field-4" class="control-label">Country</label>
                                {{ Form::select('CountryID', $countries, '', array("class"=>"select2")) }}
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <input type="checkbox" name="updateDescription" class="" />
                                <label for="field-5" class="control-label">Description</label>

                                <input type="text" name="Description" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>




                    </div>
                     <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateInterval1" class="" />
                                <label for="field-5" class="control-label">Interval 1</label>
                                <input type="text" value="1" name="Interval1" class="form-control numbercheck" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateIntervalN" class="" />
                                <label for="field-4" class="control-label">Interval N</label>
                                <input type="text" name="IntervalN"  class="form-control numbercheck" value="1" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
<div class="modal fade" id="add-new-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-codedeck-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Code Deck Detail</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Code</label>
                                <input type="text" name="Code" class="form-control"  placeholder="Code">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Description</label>
                                <input type="text" name="Description" class="form-control" id="field-1" placeholder="Description">
                            </div>
                        </div>
                        <div class="col-md-6 clear">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6 hide_country">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Country</label>
                                {{Form::select('CountryID', $countries, Input::old('Country') ,array("class"=>"form-control select2"))}}
                                <input name="codedeckid" value="{{$id}}" type="hidden" >
                                <input name="RateID" value="" type="hidden" >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
<div class="modal fade" id="upload-modal-codedeck" >
    <div class="modal-dialog">
        <div class="modal-content">
        <form role="form" id="form-upload" method="post" action="{{URL::to('codedecks/upload')}}"
              class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Upload Code Decks</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label">File Select</label>
                    <div class="col-sm-5">
                        <input type="file" id="excel" type="file" name="excel" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                        <input name="codedeckid" value="{{$id}}" type="hidden" >
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Note</label>
                    <div class="col-sm-5">
                        <p>Allowed Extension .xls, .xlxs, .csv</p>
						<p>Please upload the file in given <span style="cursor: pointer" onclick="jQuery('#modal-fileformat').modal('show');jQuery('#modal-fileformat').css('z-index',1999)" class="label label-info">Format</span></p>
						<p>Sample File <a class="btn btn-success btn-sm btn-icon icon-left" href="{{URL::to('codedecks/download_sample_excel_file')}}"><i class="entypo-down"></i>Download</a></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="codedeck-update"  class="btn upload btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                    <i class="entypo-upload"></i>
                     Upload
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
