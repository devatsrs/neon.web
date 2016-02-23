@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        {{customer_dropbox($id,["IsVendor"=>1])}}
    </li>
    <li class="active">
        <strong>Customer Rate Sheet Upload/Download History</strong>
    </li>
</ol>
<h3>Vendor Rate Sheet Upload/Download History</h3>

@include('accounts.errormessage');
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
<li>
    <a href="{{ URL::to('vendor_rates/'.$id) }}" >
        <span class="hidden-xs">Vendor Rate</span>
    </a>
</li>
@if(User::checkCategoryPermission('VendorRates','Upload'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/upload') }}" >
        <span class="hidden-xs">Vendor Rate Upload</span>
    </a>
</li>
@endif
@if(User::checkCategoryPermission('VendorRates','Download'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/download') }}" >
        <span class="hidden-xs">Vendor Rate Download</span>
    </a>
</li>
@endif
@if(User::checkCategoryPermission('VendorRates','Settings'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/settings') }}" >
        <span class="hidden-xs">Settings</span>
    </a>
</li>
@endif
@if(User::checkCategoryPermission('VendorRates','Blocking'))
<li >
    <a href="{{ URL::to('vendor_blocking/'.$id) }}" >
        <span class="hidden-xs">Blocking</span>
    </a>
</li>
@endif
@if(User::checkCategoryPermission('VendorRates','Preference'))
<li >
    <a href="{{ URL::to('/vendor_rates/vendor_preference/'.$id) }}" >
        <span class="hidden-xs">Preference</span>
    </a>
</li>
@endif
<li class="active">
    <a href="{{ URL::to('/vendor_rates/'.$id.'/history') }}" >
        <span class="hidden-xs">Vendor Rate History</span>
    </a>
</li>
</ul>

<table class="table table-bordered datatable" id="table-4">
    <thead>
        <tr>
            <th>Title</th>
            <th>Created Date</th>
            <th>Created by</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>



<script type="text/javascript">

    jQuery(document).ready(function($) {

        data_table = $("#table-4").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/vendor_rates/{{$id}}/history_ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[2, 'desc']],
            "aoColumns":
                    [
                        {},
                        {},
                        {},
                        {
                            mRender: function(id, type, full) {
                                var action, show_;
                                show_ = "/vendor_rates/{{$id}}/history/{id}/view";
                                show_ = show_.replace('{id}', id);
                                var download_upload_type = full[4] ;
                                var jobID = full[5] ;
                                var download_ = "";

                                action = '<a  onclick=" return showAjaxModal(\''+show_+'\',\'modal-customer-rate-history\');" href="javascript:;"   class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View </a>';
                                if( jobID != null ){
                                    /*Customer Upload*/
                                    if(download_upload_type == 'VU' && full[5]!= ''){
                                        download_ = baseurl +   "/jobs/"+jobID+"/download_excel";
                                        download_ = download_.replace('{id}', jobID);

                                        action += ' <a  href="'+  download_ +'" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>';
                                    }
                                    /*Customer Download*/
                                     if(download_upload_type == 'VD' && full[6] != '' &&  full[6] != null && full[6] != 'No data found!' ){
                                        download_= baseurl +"/jobs/"+jobID+"/downloaoutputfile";
                                        action += ' <a  href="'+  download_ +'" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>';
                                    }
                                }
                                return action;
                            }
                        },
                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "Export Data",
                                "sUrl": baseurl + "/vendor_rates/{{$id}}/history_exports",
                                sButtonClass: "save-collection",
                            }
                        ]
                    },
        });

        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });
    });

</script>
@stop

@section('footer_ext')
@parent
<!-- Job Modal  (Ajax Modal)-->
<div class="modal fade" id="modal-customer-rate-history">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Detail</h4>
            </div>
            <div class="modal-body">
                Content is loading...
            </div>
        </div>
    </div>
</div>
@stop