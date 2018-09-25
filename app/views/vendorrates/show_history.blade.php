@if(isset($history) && !empty($history) )
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 text-left bold">Title:</label>
            <div class="col-sm-12">{{$history->Title}}</div>
        </div>
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Description</label>
            <div class="col-sm-12">{{$history->Description}}</div>
        </div>

        @if( isset($history->AccountID) && !empty($history->AccountID))
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Account Name</label>
            <div class="col-sm-12">{{Account::getCompanyNameByID($history->AccountID)}}</div>
        </div>
        @endif

         @if( $history->Type == 'VU')
            <?php $Options = JobFile::where(["JobID"=>$history->JobID])->pluck('Options');  ?>
            @if(!empty($Options) )
                <?php $Options = json_decode($Options);
// stdClass Object
//(
//    [Trunk] => CLI
//    [radio_options] => radio_replace_all
//    [checkbox_rates_with_effected_from] => 1
//    [checkbox_skip_rates_with_same_date] => 1
//    [checkbox_add_new_codes_to_code_decks] => 1
//    [full_path] => I:\bk\www\projects\aamir\rm\laravel\uploads\A-Z Company2\eFfJgcAPYzhoLK39.xlsx
//    [AccountID] => 18
//    [CompanyID] => 1
//)
                ?>
                    @if (isset($Options->Trunk))
                        <?php $Trunks = $Options->Trunk;?>
                        <div class="form-group">
                        <label for="field-1" class="control-label col-sm-12 bold">Trunk</label>
                        <div class="col-sm-12">{{Trunk::getTrunkName($Trunks)}}</div>
                        </div>

                        <div class="form-group">
                        <label for="field-1" class="control-label col-sm-12 bold">Settings</label>


                            @if( isset($Options->checkbox_replace_all) && $Options->checkbox_replace_all =='1')
                                <div class="col-sm-12">Replace all of the existing rates with the rates from the file</div>
                            @endif
                            @if(isset($Options->checkbox_rates_with_effected_from) )
                                <div class="col-sm-12">Rates with 'effective from' date in the past should be uploaded as effective immediately</div>
                            @endif
                            @if(isset($Options->checkbox_add_new_codes_to_code_decks) && $Options->checkbox_add_new_codes_to_code_decks == 1)
                                <div class="col-sm-12">Add new codes from the file to code decks</div>
                            @endif
                            @if(isset($Options->checkbox_review_rates) && $Options->checkbox_review_rates == 1)
                                <div class="col-sm-12">Review Rates</div>
                            @endif
                            @if(isset($Options->radio_list_option) && $Options->radio_list_option == 1)
                                <div class="col-sm-12">Complete File</div>
                            @else
                                <div class="col-sm-12">Partial File</div>
                            @endif
                            <?php $Options = json_decode($Options->Options); ?>
                            @if(isset($Options->skipRows))
                                @if(isset($Options->skipRows->start_row) && (int) $Options->skipRows->start_row > 0)
                                    <div class="col-sm-12">Skips rows from Start - {{$Options->skipRows->start_row}}</div>
                                @endif
                                @if(isset($Options->skipRows->end_row) && (int) $Options->skipRows->end_row > 0)
                                    <div class="col-sm-12">Skips rows from Bottom - {{$Options->skipRows->end_row}}</div>
                                @endif
                            @endif
                            @if(!empty($Options->Sheet))
                                <div class="col-sm-12">Sheet Name : {{$Options->Sheet}}</div>
                            @endif
                        </div>
                     @endif
            @endif
         @endif

        @if( isset($history->Options) && !empty($history->Options))
        <?php $Options = json_decode($history->Options); ?>
        @if(isset($Options->Format) && !empty($Options->Format))
        <?php $Format = $Options->Format; ?>
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Output format</label>
            <div class="col-sm-12">{{$Format}}</div>
        </div>
        @endif
        @endif
        @if( isset($history->Options) && !empty($history->Options))
        <?php
        $Options = json_decode($history->Options);
        if (isset($Options->Trunks)) {
            $Trunks = $Options->Trunks;
            $trunkname = '';
            if (is_array($Trunks)) {
                foreach($Trunks as $Trunk){
                    $trunktemp = Trunk::getTrunkName($Trunk);
                    if(!empty($trunktemp)){
                        $trunkname .= $trunktemp.',';
                    }
                }
                $trunkname = substr($trunkname,0,-1);
            }else{
                $trunkname = Trunk::getTrunkName($Trunks);
            }
        }
        ?>
        @if(isset($Trunks) && !empty($Trunks))
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Trunks</label>
            <div class="col-sm-12">{{$trunkname}}</div>
        </div>
        @endif
        <?php
        if (isset($Options->Timezones)) {
            $Timezones = $Options->Timezones;
            $TimezonesName = '';
            $TimezonesName = Timezones::getTimezonesName($Timezones);
        }
        ?>
        @if(isset($Timezones) && !empty($Timezones))
            <div class="form-group">
                <label class="control-label col-sm-12 bold">Timezones</label>
                <div class="col-sm-12">{{$TimezonesName}}</div>
            </div>
        @endif

        @endif

        @if( isset($history_file->FilePath) && !empty($history_file->FilePath))
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">File Location</label>
            <div class="col-sm-12">{{$history_file->FilePath}}</div>
        </div>
        @endif
        @if( isset($history->OutputFilePath) && !empty($history->OutputFilePath) && $history->OutputFilePath != 'No data found!')
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Generated File Path</label>
            <div class="col-sm-12">
            <a href="{{URL::to('/jobs/'.$history->JobID.'/downloaoutputfile')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>
            </div>
        </div>
        @endif
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Date Created</label>
            <div class="col-sm-12">{{$history->created}}</div>
        </div>


    </div>

</div>
@endif