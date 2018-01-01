<script>
    var checked = '';
    @if(!empty($report_settings['filter_settings']))
        var filter_settings = {{$report_settings['filter_settings']}};
    @else
        var filter_settings = {};
    @endif


    var dimesions = {{json_encode($dimensions)}};
    var measures = {{json_encode($measures)}};
    $( function() {

        // There's the Dimension and the Measures
        var $Dimension = $( "#Dimension" ),
                $Measures = $( "#Measures" ),
                $Columns = $( "#Columns_Drop" ),
                $Filter = $( "#Filter_Drop" ),
                $Row = $( "#Row_Drop" );



        // Let the Measures be droppable, accepting the Dimension items
        $Columns.droppable({
            accept:  function(d) {
                if(d.hasClass("dimension")|| d.hasClass("measures")){
                    return true;
                }
            },
            classes: {
                "ui-droppable-active": "ui-state-highlight"
            },
            out: function( event, ui ) {
                var drop_ele_val = $(ui.draggable).attr('data-val');
                if( $Dimension.find('[data-val="'+drop_ele_val+'"]').length == 1 || $Measures.find('[data-val="'+drop_ele_val+'"]').length) {
                    $Columns.find('[data-val="'+drop_ele_val+'"]').remove();
                    update_columns(ui.draggable,'remove',1);
                }
            },
            drop: function( event, ui ) {

                deleteImage( ui.draggable,$Columns );
                update_rows(ui.draggable,'remove',0);
                update_columns(ui.draggable,'add',1);
                remove_tooltip();

            }
        });

        // Let the Measures be droppable, accepting the Dimension items
        $Row.droppable({
            accept: function(d) {
                if(d.hasClass("dimension")){
                    return true;
                }
            },
            classes: {
                "ui-droppable-active": "ui-state-highlight"
            },
            out: function( event, ui ) {
                var drop_ele_val = $(ui.draggable).attr('data-val');
                if( $Dimension.find('[data-val="'+drop_ele_val+'"]').length == 1 || $Measures.find('[data-val="'+drop_ele_val+'"]').length) {
                    $Row.find('[data-val="'+drop_ele_val+'"]').remove();
                    update_rows(ui.draggable,'remove',1);
                }
            },
            drop: function( event, ui ) {
                deleteImage( ui.draggable,$Row );
                update_columns(ui.draggable,'remove',0);
                update_rows(ui.draggable,'add',1);
                remove_tooltip();

            }
        });

        // Let the Measures be droppable, accepting the Dimension items
        $Filter.droppable({
            accept: function(d) {
                if(d.hasClass("dimension")){
                    return true;
                }
            },
            classes: {
                "ui-droppable-active": "ui-state-highlight"
            },
            out: function( event, ui ) {
                var drop_ele_val = $(ui.draggable).attr('data-val');
                if( $Dimension.find('[data-val="'+drop_ele_val+'"]').length == 1 || $Measures.find('[data-val="'+drop_ele_val+'"]').length) {
                    $Filter.find('[data-val="'+drop_ele_val+'"]').remove();
                    update_filter(ui.draggable,'remove',1);

                }
            },
            drop: function( event, ui ) {
                deleteImage( ui.draggable,$Filter );
                update_filter(ui.draggable,'add',0);
                show_filter(ui.draggable);
                //update_rows(ui.draggable,'add',1);
                remove_tooltip();

            }
        });

        $($Filter).on('click', '.dimension', function(e) {
            show_filter($(this));
        });

        function deleteImage( $item, $droppable) {
            var element=$item.clone();
            var drop_ele_val = $(element).attr('data-val');
            if( $droppable.find('[data-val="'+drop_ele_val+'"]').length == 0){
                var $list = $( "ul", $droppable ).length ?
                        $( "ul", $droppable ) :
                        $( "<ul class=' select2-choices ui-helper-reset'/>" ).appendTo( $droppable );
                $(element).draggable({helper: 'clone'});
                $(element).appendTo( $list ).fadeIn();
            }
        }


        $('.save-report-data').on('click', function (e) {
            var data = baseurl +'/report/getdatagrid/0?'+$("#report-row-col").serialize()+'&'+$("#add-new-filter-form").serialize()+'&'+$("#add-new-report-form").serialize()+'&Export=1&Type='+$(this).attr('data-format');
            $(this).attr('href',data);
                /*$(".table_report_overflow").table2excel({
                    exclude: ".noExl",
                    name: "Reports",
                    filename: "Reports",
                    fileext: ".xls",

                });*/
        });
        function reload_table(){
            var data = $("#report-row-col").serialize()+'&'+$("#add-new-filter-form").serialize();
            loading_table('.table_report_overflow',1);
            $.ajax({
                url:baseurl +'/report/getdatagrid/0', //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    loading_table(".table_report_overflow",0);
                    $('.table_report_overflow').html(response);
                },
                data: data,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });
        }

        function  makedraggable(){
            // Let the Dimension items be draggable
            $( "li.ui-draggable", $Dimension ).draggable({
                helper: "clone",
                cursor: "move"
            });

            // Let the Measures items be draggable
            $( "li.ui-draggable", $Measures ).draggable({
                helper: "clone",
                cursor: "move"
            });
            // Let the Dimension items be draggable
            $( "li", $Columns ).draggable({
                helper: "clone"
            });

            // Let the Measures items be draggable
            $( "li", $Filter ).draggable({
                helper: "clone"
            });
            // Let the Dimension items be draggable
            $( "li", $Row ).draggable({
                helper: "clone"
            });

            $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
            $('.tree li.parent_li > span').on('click', function (e) {
                var children = $(this).parent('li.parent_li').find(' > ul > li');
                if (children.is(":visible")) {
                    children.hide('fast');
                    $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
                } else {
                    children.show('fast');
                    $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
                }
                e.stopPropagation();
            });
            $('.tree li.parent_li > span').trigger('click');
        }

        $("[name='Cube']").on('change', function () {
            var cube  = $(this).val();
            var current_dimesions = dimesions[cube];
            var current_measures = measures[cube];
            var dimesions_html = '';
            var measures_html = '';
            $.each(current_dimesions, function(index, value) {
                if(typeof value == 'object'){
                    dimesions_html += '<li><span><i class="fa fa-folder-open-o"></i> '+index+'</span><ul>';
                    $.each(value, function(index2, value2) {
                        dimesions_html += '<li class="dimension ui-draggable" data-cube="' + cube + '" data-val="' + index2 + '"><span><i class="fa fa-arrows"></i> ' + value2 + '</span></li>';
                    });
                    dimesions_html += '</ul></li>';
                }else{
                    if(index == 'ProductType'){
                        dimesions_html += '<li class="dimension ui-draggable tooltip-primary" data-trigger="hover" data-toggle="tooltip" data-original-title="Item / Subscriptions / Additional Charges / Invoice Received" data-cube="'+cube+'" data-val="'+index+'"><span><i class="fa fa-arrows"></i> ' + value + '</span></li>';
                    }else{
                        dimesions_html += '<li class="dimension ui-draggable" data-cube="'+cube+'" data-val="'+index+'"><span><i class="fa fa-arrows"></i> ' + value + '</span></li>';
                    }
                }

            });
            $.each(current_measures, function(index, value) {
                measures_html += '<li class="measures ui-draggable" data-cube="'+cube+'" data-val="'+index+'"><span><i class="fa fa-arrows"></i> ' + value + '</span></li>';
            });
            $("#Dimension").html(dimesions_html);
            $("#Measures").html(measures_html);
            makedraggable();
        });
        $("[name='Cube']").trigger('change');
        $("#hidden_row").on('change', function () {
            reload_table();
        });
        $("#hidden_columns").on('change', function () {
            reload_table();
        });
        function loading_table(table,bit){
            var panel = jQuery(table).closest('.loading');
            if(bit==1){
                blockUI(panel);
                panel.addClass('reloading');
            }else{
                unblockUI(panel);
                panel.removeClass('reloading');
            }
        }

        function update_rows($item,action,trigger) {
            var rows = [];
            var previous_val = $("#hidden_row").val();
            if($("#hidden_row").val() != '') {
                rows = $("#hidden_row").val().split(',');
            }
            //if(action == 'remove') {
            var index = rows.indexOf($item.attr('data-val'));
            if (index > -1) {
                rows.splice(index, 1);
            }
            //}
            if(action == 'add') {
                rows[rows.length] = $item.attr('data-val');
            }
            $("#hidden_row").val(rows.join(","));
            if($("#hidden_row").val() != previous_val && trigger == 1){
                $("#hidden_row").trigger('change');
            }

        }
        function update_columns($item,action,trigger) {
            var columns = [];
            var previous_val = $("#hidden_columns").val();
            if($("#hidden_columns").val() != '') {
                columns = $("#hidden_columns").val().split(',');
            }
            //if(action == 'remove') {
            var index = columns.indexOf($item.attr('data-val'));
            if (index > -1) {
                columns.splice(index, 1);
            }
            //}
            if(action == 'add') {
                columns[columns.length] = $item.attr('data-val');
            }
            $("#hidden_columns").val(columns.join(","));

            if($("#hidden_columns").val() != previous_val && trigger == 1){
                $("#hidden_columns").trigger('change');
            }

        }
        function show_filter($items){
            var col_val=  $items.attr('data-val');
            $('#hidden_filter_col').val(col_val);
            var data = $("#report-row-col").serialize();
            var date_fields = {{json_encode(Report::$date_fields)}};
            if(filter_settings[col_val]) {
                var filter_settings_array = filter_settings[col_val];
            }

            if($.inArray(col_val,date_fields) > -1){
                $(".filter_data_table").hide();
                $(".filter_data_wildcard").hide();
                $("li.date_filters a").trigger('click');
                $(".date_filters").show()
                if(typeof filter_settings_array != 'undefined') {
                    $("#date_filter [name='start_date']").val(filter_settings_array.start_date);
                    $("#date_filter [name='end_date']").val(filter_settings_array.end_date);
                }
            }else{
                $(".filter_data_table").show();
                $(".filter_data_wildcard").show();
                $("li.filter_data_table a").trigger('click');
                $(".date_filters").hide();
                if(typeof filter_settings_array != 'undefined') {
                    $("#wildcard [name='wildcard_match_val']").val(filter_settings_array.wildcard_match_val);
                }
                filter_data_table();
            }



            $('#add-new-modal-filter').modal('show');
        }

        function update_filter($item,action,trigger) {
            var rows = [];
            var previous_val = $("#hidden_filter").val();
            if($("#hidden_filter").val() != '') {
                rows = $("#hidden_filter").val().split(',');
            }
            //if(action == 'remove') {
            var index = rows.indexOf($item.attr('data-val'));
            if (index > -1) {
                rows.splice(index, 1);
            }
            if (filter_settings[$item.attr('data-val')]) {
                delete filter_settings[$item.attr('data-val')];
                $('#hidden_setting').val(JSON.stringify(filter_settings));
                $("#hidden_filter_col").val('');
            }
            //}
            if(action == 'add') {
                rows[rows.length] = $item.attr('data-val');
                $('#hidden_filter_col').val($item.attr('data-val'));
            }
            $("#hidden_filter").val(rows.join(","));
            if($("#hidden_filter").val() != previous_val && trigger == 1){
                $("#hidden_filter").trigger('change');
                $("#report-update").trigger('click');
            }

        }
        $('#report-update').click(function(e){
            if($("#hidden_filter_col").val() != '') {
                var result = { };
                var ser_array = $("#add-new-filter-form").serializeArray();
                $.each(ser_array,function(){
                    if(this.name.indexOf('[')  !== -1 ){
                        var actual_name  = this.name;
                        actual_name = actual_name.replace('[','');
                        actual_name = actual_name.replace(']','');
                        if(typeof result[actual_name]  == 'undefined'){
                            result[actual_name] = [];
                        }
                        result[actual_name].push(this.value);
                    }else{
                        result[this.name] = this.value;
                    }
                });
                filter_settings[$("#hidden_filter_col").val()] = result;
                $('#add-new-modal-filter').modal('hide');
            }
            $('#hidden_setting').val(JSON.stringify(filter_settings));
            e.preventDefault();
            reload_table();
        });

        $(document).on('click', '#table-filter-list tbody tr', function() {
            if (checked =='') {
                $(this).toggleClass('selected');
                if($(this).is('tr')) {
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            }
        });

    $("#add-new-report-form").submit(function(ev){
        ev.preventDefault();
        var save_report_url = '';
        var data3 = $("#report-row-col").serialize()+'&'+$("#add-new-filter-form").serialize()+'&'+$("#add-new-report-form").serialize();
        if($("#add-new-report-form [name='ReportID']").val() > 0){
            save_report_url = baseurl+'/report/update/'+$("#add-new-report-form [name='ReportID']").val()
        }else{
            save_report_url =  baseurl+'/report/store'
        }
        submit_ajax(save_report_url,data3);
    });

    $("#save_report").click(function(ev){
        $("#add-new-modal-report").modal('show');
    });
    $(".top_filter").click(function(ev)
    {
        if($(this).val() == 'none'){
            $('.top_filter_data').attr('disabled','disabled');
        }else{
            $('.top_filter_data').removeAttr('disabled');
        }
    });
    $(".top_filter_none").trigger('click');

    $(".condition_filter").click(function(ev)
    {
        if($(this).val() == 'none'){
            $('.condition_filter_data').attr('disabled','disabled');
        }else{
            $('.condition_filter_data').removeAttr('disabled');
        }
    });
    $(".condition_filter_none").trigger('click');
        @if(empty($report_settings))
        $("#hidden_filter").val('');
        $("#hidden_row").val('');
        $("#hidden_columns").val('');
        @else
                reload_table();
        @endif
    } );


    function filter_data_table(){
        data_table_filter = $("#table-filter-list").dataTable({
            "bDestroy": true,
            "bProcessing": false,
            "bServerSide": false,
            "bPaginate": false,
            "sAjaxSource": baseurl + "/report/getdatalist",
            "sDom": "<'row'<'col-xs-1 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-11 col-right'<'change-view'> f>r> t<'row'<'col-xs-12 col-left'i>>",
            "aaSorting": [[0, 'asc']],
            "fnServerParams": function(aoData) {
                aoData.push(
                        {"name":"filter_col_name","value":$("#hidden_filter_col").val()},
                        {"name":"Cube","value":$("#report-row-col [name='Cube']").val()}

                );
                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name":"filter_col_name","value":$("#hidden_filter_col").val()},
                        {"name":"Cube","value":$("#report-row-col [name='Cube']").val()},
                        {"name":"Export","value":1}
                );
            },
            "aoColumns":
                    [
                        {"bSortable": false,
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="'+$("#hidden_filter_col").val()+'[]" value="' + id.toString().replace(/"/g,"&quot;") + '" class="rowcheckbox" ></div>';
                            }
                        }, //0Checkbox
                        { "bSortable": true}
                    ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/currency/exports/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/currency/exports/csv",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            }
        });
        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });
        $("#table-filter-list tbody input[type=checkbox]").each(function (i, el) {
            var $this = $(el),
                    $p = $this.closest('tr');

            $(el).on('change', function () {
                var is_checked = $this.is(':checked');

                $p[is_checked ? 'addClass' : 'removeClass']('highlight');
            });
        });
        $("#selectall").click(function(ev) {
            var is_checked = $(this).is(':checked');
            $('#table-filter-list tbody tr').each(function(i, el) {
                if (is_checked) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                    $(this).addClass('selected');
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                    $(this).removeClass('selected');
                }
            });
        });
        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
        //select all record
        $('#selectallbutton').click(function(){
            if($('#selectallbutton').is(':checked')){
                checked = 'checked=checked disabled';
                $("#selectall").prop("checked", true).prop('disabled', true);
                $('#table-filter-list tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                    $(this).addClass('selected');
                });

            }else{
                checked = '';
                $("#selectall").prop("checked", false).prop('disabled', false);
                $('#table-filter-list tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                    $(this).removeClass('selected');
                });
            }
        });
    }
    function remove_tooltip(){
        $('body').find('[role="tooltip"]').remove();
    }

</script>