@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Active Jobs</strong>
    </li>
</ol>
<h3>Active Jobs</h3>
@include('jobs.activejob')

@include('cronjob.activecronjob')

<script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#jobs tbody,#cronjobs tbody').on('click', '.delete-config', function (ev) {
                var id = $(this).parents('table').attr('id');
                var JobID = $(this).attr('data-id');
                var PID = $(this).attr('data-pid');
                var form = $('#Terminate-form');
                form.find('[name="JobStatusID"]').val('').trigger("change");
                form.find('[name="message"]').val('');
                form.find('[name="JobID"]').val(JobID);
                form.find('[name="PID"]').val(PID);
                if (id == 'jobs') {
                    form.find('[name="jobtype"]').val('job');
                    $('#modal-Terminate').find('.modal-title').text('Terminate Job');
                    $('#modal-Terminate').modal('show');
                } else {
                    form.find('[name="jobtype"]').val('cronjob');
                    result = confirm("Are you Sure Terminate Proccess?");
                    if(result){
                        $('#Terminate-form').submit();
                    }
                }
            });

            $('#Terminate-form').submit(function (e) { //handel for both jobs and cron jobs termination. =>(views.cronjon.activecronjob)
                e.preventDefault();
                var jobtype = $('#Terminate-form').find('[name="jobtype"]').val();
                var url = '';
                if (jobtype == 'job') {
                    url = baseurl + '/jobs/activeprocessdelete';
                } else {
                    url = baseurl + '/cronjobs/activeprocessdelete';
                }
                var formData = new FormData($('#Terminate-form')[0]);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            $(".save").button('reset');
                            $('#modal-Terminate').modal('hide');
                            if (jobtype == 'job') {
                                data_table_jobs.fnFilter('', 0);
                            }else {
                                data_table_cronjob.fnFilter('', 0);
                            }
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                            $(".save").button('reset');
                            if (jobtype == 'job') {
                                data_table_jobs.fnFilter('', 0);
                            }else {
                                data_table_cronjob.fnFilter('', 0);
                            }
                        }
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });

            });
        });
    </script>

@stop

@section('footer_ext')
    <div class="modal fade" id="modal-Terminate">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="Terminate-form" method="post" action="">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Terminate Job</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <label class="col-sm-3 control-label">Change Status To</label>
                                <div class="col-sm-9">
                                    {{Form::select('JobStatusID',$JobStatus,'',array("class"=>"select2 small"))}}
                                    <input type="hidden" name="JobID" />
                                    <input type="hidden" name="PID" />
                                    <input type="hidden" name="jobtype" />
                                </div>
                            </div>
                            <div class="form-Group">
                                <br />
                                <br />
                                <br />
                                <label class="col-sm-3 control-label">Message</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control message" rows="4" name="message"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Terminate
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

