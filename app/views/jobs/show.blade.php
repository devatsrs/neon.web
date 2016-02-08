@if(isset($job) && !empty($job) )
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 text-left bold">Title:</label>
            <div class="col-sm-12">{{$job->Title}}</div>
        </div>
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Description</label>
            <div class="col-sm-12">{{$job->Description}}</div>
        </div>
        <?php
        if ($job->Type == 'Generate Rate Table') {

            if (isset($job->Options) && !empty($job->Options)) {  //{"RateGeneratorId":"13","action":"create"}
                $Options = json_decode($job->Options);
                if (isset($Options->RateGeneratorId) && !empty($Options->RateGeneratorId)) {
                    $RateGenerator = RateGenerator::find($Options->RateGeneratorId);
                    if (!empty($RateGenerator)) {
                        $trunkname = Trunk::getTrunkName($RateGenerator->TrunkID);
                        ?>
                        <div class="form-group">
                            <label for="field-1" class="control-label col-sm-12 bold">Rate Generator Name</label>
                            <div class="col-sm-12">{{$RateGenerator->RateGeneratorName}}</div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="control-label col-sm-12 bold">Trunk</label>
                            <div class="col-sm-12">{{$trunkname}}</div>
                        </div>
                        <?php
                        if (isset($RateGenerator->RateTableId) && !empty($RateGenerator->RateTableId)) {
                            $RateTable = RateTable::find($RateGenerator->RateTableId);
                            if(!empty($RateTable)){
                            ?>
                            <div class="form-group">
                                <label for="field-1" class="control-label col-sm-12 bold">Rate Table Name</label>
                                <div class="col-sm-12">{{$RateTable->RateTableName}}</div>
                            </div>
                            <?php
                            }
                        }
                    }
                }
            }
        }
        $file_title = "Generated File Path";
        if($job->Type == 'Bulk Leads mail send'){
            $file_title = "Attached file path";
        }
        ?>
        @if( isset($job->AccountID) && !empty($job->AccountID))
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Account Name</label>
            <div class="col-sm-12">{{Account::getCompanyNameByID($job->AccountID)}}</div>
        </div>
        @endif
        
        @if( isset($job->Options) && !empty($job->Options))
            <?php $Options = json_decode($job->Options); ?>
        @if(isset($Options->Format) && !empty($Options->Format))
        <?php $Format = $Options->Format; ?>
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Output format</label>
            <div class="col-sm-12">{{$Format}}</div>
        </div>
        @endif
        <?php $Accountname = array();?>
        @if(isset($Options->AccountID) && is_array($Options->AccountID))
        @foreach($Options->AccountID as $row=>$AccountID)
        <?php if((int)$AccountID){$Accountname[] = Account::getCompanyNameByID((int)$AccountID);} ?>
        @endforeach
        <div class="form-group" style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
            <label for="field-1" class="control-label col-sm-12 bold">Account Names</label>
            <div class="col-sm-12">{{implode(',<br>',$Accountname)}}</div>
        </div>
        @endif
        @if(isset($Options->StartDate))
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Start Date</label>
            <div class="col-sm-12">{{$Options->StartDate}}</div>
        </div>
        @endif
        @if(isset($Options->EndDate))
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">End Date</label>
            <div class="col-sm-12">{{$Options->EndDate}}</div>
        </div>
        @endif
        <?php
        if (isset($Options->Trunks)) {
            $Trunks = $Options->Trunks;
            $trunkname = '';
            if (is_array($Trunks)) {
                foreach($Trunks as $Trunk){
                $trunktemp =Trunk::getTrunkName($Trunk);
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
        @endif
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Mail Status</label>
            <div class="col-sm-12">
            @if(isset($job->JobStatusID) && ( $job->JobStatusID != 1 || $job->JobStatusID != 2 ) && $job->EmailSentStatus == 0 && $job->EmailSentStatusMessage == '')
                Failed to send email
            @elseif(isset($job->JobStatusID) && $job->EmailSentStatus == 1 && $job->EmailSentStatusMessage == '')
                Email sent successfully
            @else
                {{$job->EmailSentStatusMessage}}
            @endif
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Job Status Message</label>
            <div class="col-sm-12" style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">{{str_replace('\n\r','<br>',$job->JobStatusMessage)}}</div>
        </div>
        @if($job->Type == 'Vendor Rate Upload')
            @if(isset($job_file->Options))
            <?php $Options = json_decode($job_file->Options);?>
            @if (isset($Options->Trunk))
            <div class="form-group">
                <label for="field-1" class="control-label col-sm-12 bold">Trunk</label>
                <div class="col-sm-12">{{Trunk::getTrunkName($Options->Trunk)}}</div>
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
            </div>
            @endif

            @endif
        @endif
        @if( isset($job_file->FilePath) && !empty($job_file->FilePath))
         <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Download File</label>
            <div class="col-sm-12"><a href="{{URL::to('/jobs/'.$job_file->JobID.'/download_excel')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a></div>
        </div>
        @elseif( isset($job->OutputFilePath) && !empty($job->OutputFilePath) && $job->OutputFilePath != 'No data found!')
         <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">{{$file_title}}</label>
            <div class="col-sm-12">
            <a href="{{URL::to('/jobs/'.$job->JobID.'/downloaoutputfile')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>
            </div>
        </div>
        @elseif(!empty($job->OutputFilePath))
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">{{$file_title}}</label>
            <div class="col-sm-12">
            No data found!
            </div>
        </div>
        @endif
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Date Created</label>
            <div class="col-sm-12">{{$job->created_at}}</div>
        </div>
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Created By</label>
            <div class="col-sm-12">{{$job->CreatedBy}}</div>
        </div>
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Processed Date</label>
            <div class="col-sm-12">{{$job->updated_at}}</div>
        </div>
        <!--
        <div class="form-group">
        <label for="field-1" class="control-label col-sm-12 bold"><strong>Modified By</strong></label>
        <div class="col-sm-12">{{$job->ModifiedBy}}</div>
        </div>
        -->

    </div>

</div>
@endif