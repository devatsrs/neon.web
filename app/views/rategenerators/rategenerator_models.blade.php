<style>
    .radio{
        margin-top:0 !important;
    }
    .radio label{
        min-height:16px !important;
    }
</style>
<div class="modal fade" id="modal-update-rate" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="update-rate-generator-form" method="post" >
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Update Rate Table</h4>
        </div>
        <div class="modal-body">
          <div class="row" id="RateTableIDid">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-4" class="control-label">Select Rate Table</label>
                <div id="DropdownRateTableID"> </div>
              </div>
            </div>
          </div>
          <div class="row" id="RateTableNameid">
            <div class="col-md-12">
              <div class="form-group" >
                <label for="field-4" class="control-label">Rate Table Name</label>
                <input type="text" name="RateTableName" class="form-control"  value="" />
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group" >
                <label for="field-4" class="control-label">Effective Date</label>
                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}"  data-date-format="yyyy-mm-dd" value="" />
              </div>
            </div>
          </div>
            <div class="row when_update_rate_generator">
                <div class="col-md-12">
                    <div class="form-group" >
                        <label for="field-4" class="control-label">Effective Date (Where Rate Increases) </label>
                        <input type="text" name="IncreaseEffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}"  data-date-format="yyyy-mm-dd" value="" />
                    </div>
                </div>
            </div>
            <div class="row when_update_rate_generator">
                <div class="col-md-12">
                    <div class="form-group" >
                        <label for="field-4" class="control-label">Effective Date (Where Rate Decreases) </label>
                        <input type="text" name="DecreaseEffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}"  data-date-format="yyyy-mm-dd" value="" />
                    </div>
                </div>
            </div>
            <div class="row" id="RateTableReplaceRate">
                <div class="col-md-12">
                    <div class="form-group" >
                        <label class="control-label">
                            <input type="checkbox" id="rd-1" name="checkbox_replace_all" value="1" > &nbsp;&nbsp;Replace all of the existing rates
                        </label>
                    </div>
                </div>
            </div>
            <div class="row" id="RateTableEffectiveRate">
                <div class="col-md-12">
                    <div class="form-group" >
                        <div class="">
                            <label for="field-4" class="control-label pull-left">Rate :&nbsp;&nbsp;&nbsp;</label>
                            <div class="radio radio-replace color-primary pull-left checked" id="defaultradiorate">
                                <input class="icheck-11 timeline_filter" type="radio" id="minimal-radio-1" name="EffectiveRate" value="now">
                                <label for="minimal-radio-1">Current</label>
                                &nbsp;&nbsp;</div>
                            <div class="radio radio-replace color-green pull-left">
                                <input class="icheck-11 timeline_filter" type="radio" id="minimal-radio-2" name="EffectiveRate" value="effective">
                                <label for="minimal-radio-2">Effective on selected effective date</label>
                                &nbsp;&nbsp;</div>
                            <div class="radio radio-replace color-blue pull-left">
                                <input class="icheck-11 timeline_filter" type="radio" id="minimal-radio-3" name="EffectiveRate" value="future">
                                <label for="minimal-radio-3">Future</label>
                                &nbsp;&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- modal body div over-->
        <div class="modal-footer">
          <input type="hidden" name="RateGeneratorID" value="">
          <button type="submit"  class="save TrunkSelect btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Ok </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="modal-delete-rategenerator" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="delete-rate-generator-form" method="post" >
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Delete Rate Generator cron job</h4>
        </div>
        <div class="modal-body">
          <div class="container col-md-12"></div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="RateGeneratorID" value="">
          <button id="rategenerator-select"  class="save TrunkSelect btn btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-trash"></i> Delete </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php if(!isset($id)){$id=0;} ?>
<script type="text/javascript">
    jQuery(document).ready(function($) {

        el_effective_date =$('#update-rate-generator-form input[name="EffectiveDate"]');
        el_inc_effective_date =$('#update-rate-generator-form input[name="IncreaseEffectiveDate"]');
        el_dec_effective_date =$('#update-rate-generator-form input[name="DecreaseEffectiveDate"]');

        el_effective_date.change(function(e) {

            if(el_effective_date.val().trim() != '' /*&& el_inc_effective_date.val().trim() == '' && el_dec_effective_date.val().trim() == '' */ ) {

                var EffectiveDate = el_effective_date.val();


                var EffectiveDate_Date = new Date(EffectiveDate);
                var EffectiveDate_7Days = new Date(new Date(EffectiveDate_Date).setDate(EffectiveDate_Date.getDate()+7));
                var EffectiveDate_7Days_str = EffectiveDate_7Days.getFullYear() + '-' + (EffectiveDate_7Days.getMonth()+1) + '-' +  ('0'+ EffectiveDate_7Days.getDate()).slice(-2);

                el_dec_effective_date.val(EffectiveDate);
                el_inc_effective_date.val(EffectiveDate_7Days_str);

            }


        });
    });

</script>
