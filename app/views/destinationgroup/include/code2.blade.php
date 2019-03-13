    
            <form id="table_filter1" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <div class="col-sm-3">
                    <label for=" field-1" class="control-label">Country</label>
                    {{Form::select('CountryID', $countries,'',array("class"=>"form-control"))}}
                </div>
                <div class="col-sm-3">
                    <label class="control-label">Code</label>
                    <input type="text" class="form-control" name="FilterCode">
                </div>
                <div class="col-sm-3">
                    <label class="control-label">Description</label>
                    <input type="text" class="form-control" name="FilterDescription">
                    <!--<select class="form-control" name="Type">
                        <option value="">All</option>
                        <option value="Mobile">Mobile</option>
                        <option value="Fixed">Fixed</option>
                    </select>-->
                </div>
                <div class="col-sm-3">
                    <label class="control-label">Show Applied Code</label>
                    <input class="icheck" name="Selected" type="checkbox" value="1" >
                </div>
                <input type="hidden" name="DestinationGroupID" value="{{$dgid}}" >
                <input type="hidden" name="DestinationGroupSetID" value="{{$dgsid}}">
                <div class="clearfix"></div>
                <br>
                <div class="col-sm-4">
                    
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
                </div>
        
    </form>
   
    <button  id="add-button-codes" class=" btn btn-primary btn-sm btn-icon icon-left pull-right " style="margin-left: 5px;" data-loading-text="Loading..."><i class="fa fa-floppy-o"></i>Add</button>
    <button  id="delete-button-codes" class=" btn btn-danger btn-sm btn-icon icon-left pull-right" data-loading-text="Loading..."><i class="entypo-trash"></i>Delete</button>

    <button  id="tfilter" class=" btn btn-info btn-sm btn-icon icon-left pull-right" style="margin-right: 5px;"><i class="fa fa-filter"></i>Filters</button>

    <div class="clearfix"></div><br>
    <form id="modal-form-codes" method="post">
    <table id="table-extra" class="table table-bordered">
                                                          <thead>
                                            <th width="10%">
                                                <div class="checkbox">
                                                    <input type="checkbox" name="RateID[]" class="selectall" id="selectall">
                                                </div>
                                            </th>
                                            <th width="30%">Code</th>
                                            <th width="30%">Description</th>
                                            <th width="30%">Applied</th>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                        
   <script type="text/javascript">
        /**
         * JQuery Plugin for dataTable
         * */

        var data_table_list;
        var update_new_url;
        var postdata;
        var edit_url_new = baseurl + "/destination_group/update/{{$dgid}}";
        var datagrid_extra_url = baseurl + "/destination_group_code/ajax_datagrid";
        var checked='';
        var $searchFilter = {};

        var loading_btn;

        jQuery(document).ready(function ($) {
            $("#table_filter1").hide();
            $("#tfilter").click(function(){
                $("#table_filter1").toggle(500);
                return false;
            });

            $('#filter-button-toggle').show();


            $searchFilter.Code = $("#table_filter1 [name='FilterCode']").val();
            $searchFilter.Description = $("#table_filter1 [name='FilterDescription']").val();
            $searchFilter.CountryID = $("#table_filter1 [name='CountryID']").val();
            $searchFilter.Type = $("#table_filter1 [name='Type']").val();
            $searchFilter.Selected = $("#table_filter1 input[name='Selected']").prop("checked");
            $searchFilter.DestinationGroupSetID = "{{$dgsid}}";
            $searchFilter.DestinationGroupID = "{{$dgid}}";
            console.log("{{$dgid}}"+" "+"{{$dgsid}}");

            $("#selectall").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#table-extra tbody tr').each(function(i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });
            // apply filter
            $("#table_filter1").submit(function(ev) {
                ev.preventDefault();
                $searchFilter.Code = $("#table_filter1 [name='FilterCode']").val();
                $searchFilter.Description = $("#table_filter1 [name='FilterDescription']").val();
                $searchFilter.CountryID = $("#table_filter1 [name='CountryID']").val();
                $searchFilter.Type = $("#table_filter1 [name='Type']").val();
                $searchFilter.Selected = $("#table_filter1 input[name='Selected']").prop("checked");
                data_table_edit.fnFilter('', 0);
                return false;
            });
            // save codes
            $("form#modal-form-codes").submit(function(e){
                e.preventDefault();
                loading_btn.button('loading');
                submit_ajaxbtn_show_modal(edit_url_new,$(this).serialize()+'&'+ $.param($searchFilter),'',loading_btn);
            });
            $('#add-button-codes').click(function(ev){
                ev.preventDefault();
                loading_btn = $(this);
                $searchFilter.Action = 'Insert';
                $("#modal-form-codes").trigger('submit');
                setTimeout(function(){
                  data_table_edit.fnFilter('', 0);
                },5000);
            });
            $('#delete-button-codes').click(function(ev){
                ev.preventDefault();
                loading_btn = $(this);
                $searchFilter.Action = 'Delete';
                $("#modal-form-codes").trigger('submit');
                setTimeout(function(){
                  data_table_edit.fnFilter('', 0);
                },5000);
                
            });
            
            //select all records
            $('#table-extra tbody').on('click', 'tr', function() {
                if (checked =='') {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            });

            data_table_edit = $("#table-extra").dataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing":true,
                "bServerSide": true,
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "fnServerParams": function(aoData) {
                    aoData.push(
                            {"name": "DestinationGroupSetID", "value": $searchFilter.DestinationGroupSetID},
                            {"name": "DestinationGroupID", "value":$searchFilter.DestinationGroupID},
                            {"name": "Code", "value":$searchFilter.Code},
                            {"name": "Description", "value":$searchFilter.Description},
                            {"name": "Selected", "value":$searchFilter.Selected},
                            {"name": "CountryID", "value":$searchFilter.CountryID},
                            {"name": "Type", "value":$searchFilter.Type}

                    );
                },
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcodecheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": datagrid_extra_url,
                "oTableTools": {
                    "aButtons": [

                    ]
                },
                "aoColumns": [
                    {"bSearchable":false,"bSortable": false, //RateID
                        mRender: function(id, type, full) {
                            /*if(full[3] > 0) {
                                return '<div class="checkbox "><input checked type="checkbox" name="RateID[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }else{
                                return '<div class="checkbox "><input type="checkbox" name="RateID[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }*/
                            return '<div class="checkbox "><input type="checkbox" name="RateID[]" value="' + id + '" class="rowcheckbox" ></div>';
                        }
                    },
                    {  "bSearchable":true,"bSortable": false },  // 0 Code
                    {  "bSearchable":true,"bSortable": false },  // 0 description
                    {  "bSearchable":true,"bSortable": false },  // 0 Applied
                ],

                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });


                    $('#table-extra tbody tr').each(function(i, el) {

                        if (checked!='') {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                            $('#selectallbutton').prop("checked", true);
                        } else if(!$(this).hasClass('donotremove')){
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);;
                            $(this).removeClass('selected');
                        }
                    });

                    $('#selectallbutton').click(function(ev) {
                        if($(this).is(':checked')){
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-extra tbody tr').each(function(i, el) {
                                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                    $(this).addClass('selected');
                                });
                            }
                        }else{
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-extra tbody tr').each(function(i, el) {
                                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                    $(this).removeClass('selected');
                                });
                            }
                        }
                    });
                }

            });
            $("#selectcodecheckbox").append('<input type="checkbox" id="selectallbutton" name="selectallcodes[]" class="" title="Select All Found Records" />');
        });


    </script>
