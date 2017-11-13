@extends('layout.main')

@section('content')


    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Translate</strong>
        </li>
    </ol>
    <h3>Translate</h3>

    <div class="row" style="margin-top: 5px;">
        <div class="col-md-12">
            <form role="form" id="language-search" method="get"  action="{{URL::to('translate/search')}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-body">
                        <label class="col-sm-1 control-label">Language</label>
                        <div class="col-sm-3">
                            <select class="select2" name="language" id="language">
                                @foreach($all_langs as $value)
                                    <option value="{{$value->ISOCode}}" >{{$value->Language}}</option>
                                @endforeach
                            </select>
                        </div>

                       {{-- <div style="text-align: right;padding:10px 0 ">
                            <a class="btn btn-primary btn-sm btn-icon icon-left" id="bulk_set_vendor_rate" href="javascript:;">
                                <i class="entypo-floppy"></i>
                                Add New
                            </a>
                            <a class="btn btn-danger btn-sm btn-icon icon-left" id="changeSelectedVendorRates" href="javascript:;">
                                <i class="entypo-trash"></i>
                                Delete
                            </a>
                        </div>--}}
                    </div>
                </div>
            </form>
        </div>
    </div>


    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th>System Name</th>
            <th>Keyword</th>
            <th>Translation</th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>

    <script type="text/javascript">
        var language;
        var $searchFilter = {};
        var checked='';
        var list_fields  = ['VendorRateID','language','Description','ConnectionFee','Interval1','IntervalN','Rate','EffectiveDate','updated_at','updated_by'];
        var table="";
        jQuery(document).ready(function($) {
            languageTableBind();

            $("#language").change(function(){
                table.fnDestroy();
                languageTableBind();
            });


        });
        function languageTableBind(){
            $searchFilter.language = language = $("#language").val();
            table =$("#table-4").dataTable( {
                "bDestroy": true, // Destroy when resubmit form
                "bAutoWidth": false,
                "bProcessing": true,
                "sAjaxSource": baseurl + "/translate/search_ajax_datagrid",
                "fnServerParams": function(aoData) {
                    aoData.push({"name": "Language", "value": language});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push( {"name": "Language", "value": language});
                },
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aoColumns":
                        [
                            {}, //1 system name
                            {}, //2 keyword
                            {}, //3 Translation
                        ],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/translate/"+language+"/exports/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/translate/"+language+"/exports/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {
                    $(".text_language").blur(function(){
                        updateLanguageData(this);
                    });
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }

            } );

        }
        function updateLanguageData(ele){

            var label=$(ele).parent().find("label");
            var text_val=$(ele).val();
            var language=$(ele).attr("data-languages");
            if(label.html() != text_val){
                label.html(text_val);
                if(language=="en"){
                    label.parents("tr").find("td:eq(1)").html(text_val);
                }

                var post_data = { "language" : language, "system_name" : label.attr("data-system-name"), "value" : text_val};
                $.ajax({
                    url: baseurl + "/translate/single_update",
                    type: 'POST',
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    data: post_data
                });
            }
        }
    </script>

    <style>
        #table-4_filter label{
            display: block !important;
            margin-right:110px;
        }
        .dataTables_wrapper .export-data{
             right: 30px !important;
         }
    </style>
@stop

@section('footer_ext')
    @parent
@stop

