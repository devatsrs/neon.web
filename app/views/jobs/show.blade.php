@if(isset($job) && !empty($job) )
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label text-left bold">Title:</label>
            <div>{{$job->Title}}</div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label bold">Description</label>
            <div>{{$job->Description}}</div>
        </div>
    </div>
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
<div class="row">
    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-1" class="control-label  bold">Rate Generator Name</label>
                            <div >{{$RateGenerator->RateGeneratorName}}</div>
                        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-1" class="control-label  bold">Trunk</label>
                            <div >{{$trunkname}}</div>
                        </div>
    </div>
</div>
                        <?php
                        if (isset($RateGenerator->RateTableId) && !empty($RateGenerator->RateTableId)) {
                            $RateTable = RateTable::find($RateGenerator->RateTableId);
                            if(!empty($RateTable)){
                            ?>
<div class="row">
    <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-1" class="control-label  bold">Rate Table Name</label>
                                <div >{{$RateTable->RateTableName}}</div>
                            </div>
    </div>
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
            <div class="row">
                <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Account Name</label>
            <div >{{Account::getCompanyNameByID($job->AccountID)}}</div>
        </div>
                </div>
            </div>
        @endif
        
        @if( isset($job->Options) && !empty($job->Options))
            <?php $Options = json_decode($job->Options); ?>
        @if(isset($Options->Format) && !empty($Options->Format))
        <?php $Format = $Options->Format; ?>
        <div class="row">
            <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Output format</label>
            <div >{{$Format}}</div>
        </div>
            </div>
        </div>
        @endif
                @if(isset($Options->Effective) && !empty($Options->Effective))
                    <?php $Effective = $Options->Effective; ?>
                    <div class="row">
                        <div class="col-md-12">
                    <div class="form-group">
                        <label for="field-1" class="control-label  bold">Effective</label>
                        <div >
                            @if($Options->Effective == 'CustomDate' && isset($Options->CustomDate) && !empty($Options->CustomDate))
                                {{$Options->CustomDate}}
                            @else
                                {{$Effective}}
                            @endif
                        </div>
                    </div>
                        </div>
                    </div>
                @endif
        <?php $Accountname = array();?>
        @if(isset($Options->AccountID) && is_array($Options->AccountID))
        @foreach($Options->AccountID as $row=>$AccountID)
        <?php if((int)$AccountID){$Accountname[] = Account::getCompanyNameByID((int)$AccountID);} ?>
        @endforeach
        <div class="row">
            <div class="col-md-12">
        <div class="form-group" style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
            <label for="field-1" class="control-label  bold">Account Names</label>
            <div >{{implode(',<br>',$Accountname)}}</div>
        </div>
            </div>
        </div>
        @endif
        @if(isset($Options->StartDate))
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Start Date</label>
            <div >{{$Options->StartDate}}</div>
        </div>
        @endif
        @if(isset($Options->EndDate))
            <div class="row">
                <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">End Date</label>
            <div >{{$Options->EndDate}}</div>
        </div>
                </div>
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
            <div class="row">
                <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Trunks</label>
            <div >{{$trunkname}}</div>
        </div>
                </div>
            </div>
        @endif
        @endif
        <?php
            if (isset($Options->Timezones)) {
                $Timezones = $Options->Timezones;
                $TimezonesName = '';
                $TimezonesName = Timezones::getTimezonesName($Timezones);
            }
        ?>
        @if(isset($Timezones) && !empty($Timezones))
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="field-1" class="control-label  bold">Timezones</label>
                        <div >{{$TimezonesName}}</div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($Options->token))
            <div class="form-group">
                <label class="control-label  bold">Job Details</label>
                <div>
                    @if(isset($Options->token))
                        Token   - {{$Options->token}} <br/>
                    @endif
                    @if(isset($Options->url))
                        URL     - {{$Options->url}} <br/>
                    @endif
                    @if(isset($Options->status))
                        Status  - {{$Options->status}} <br/>
                    @endif
                    @if(isset($Options->CompanyGatewayID))
                        Gateway - {{CompanyGateway::getCompanyGatewayName($Options->CompanyGatewayID)}} <br/>
                    @endif
                </div>
            </div>
        @endif
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Mail Status</label>
            <div>
            @if(isset($job->JobStatusID) && ( $job->JobStatusID != 1 || $job->JobStatusID != 2 ) && $job->EmailSentStatus == 0 && $job->EmailSentStatusMessage == '')
                Failed to send email
            @elseif(isset($job->JobStatusID) && $job->EmailSentStatus == 1 && $job->EmailSentStatusMessage == '')
                Email sent successfully
            @else
                {{nl2br($job->EmailSentStatusMessage)}}
            @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Job Status Message</label>
            <div  style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">{{str_replace('\n\r','<br>',nl2br($job->JobStatusMessage))}}</div>
        </div>
    </div>
</div>
        @if($job->Type == 'Vendor Rate Upload')
            @if(isset($job_file->Options))
            <?php $Options = json_decode($job_file->Options);?>
            @if (isset($Options->Trunk))
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-1" class="control-label  bold">Trunk</label>
                            <div >{{Trunk::getTrunkName($Options->Trunk)}}</div>
                        </div>
                    </div>
                </div>
            @endif
            @if (isset($Options->TimezonesID))
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-1" class="control-label  bold">Timezones</label>
                            <div >{{!empty($Options->TimezonesID) ? Timezones::getTimezonesName($Options->TimezonesID) : 'Timezone column is in the File'}}</div>
                        </div>
                    </div>
                </div>
            @endif

            @endif
        @endif
        @if($job->Type == 'Vendor Rate Upload' || $job->Type == 'Rate Table Upload')
            @if(isset($job_file->Options))
                <?php $Options = json_decode($job_file->Options);?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-1" class="control-label  bold">Settings</label>
                            @if( isset($Options->checkbox_replace_all) && $Options->checkbox_replace_all =='1')
                                <div>Replace all of the existing rates with the rates from the file</div>
                            @endif
                            @if(isset($Options->checkbox_rates_with_effected_from) )
                                <div>Rates with 'effective from' date in the past should be uploaded as effective immediately</div>
                            @endif
                            @if(isset($Options->checkbox_add_new_codes_to_code_decks) && $Options->checkbox_add_new_codes_to_code_decks == 1)
                            <div>Add new codes from the file to code decks</div>
                            @endif
                            @if(isset($Options->checkbox_review_rates) && $Options->checkbox_review_rates == 1)
                            <div>Review Rates</div>
                            @endif
                            @if(isset($Options->radio_list_option) && $Options->radio_list_option == 1)
                                <div>Complete File</div>
                            @else
                                <div>Partial File</div>
                            @endif
                            <?php $Options = json_decode($Options->Options); ?>
                            @if(isset($Options->skipRows->start_row) && (int) $Options->skipRows->start_row > 0)
                                <div>Skips rows from Start - {{$Options->skipRows->start_row}}</div>
                            @endif
                            @if(isset($Options->skipRows->end_row) && (int) $Options->skipRows->end_row > 0)
                                <div>Skips rows from Bottom - {{$Options->skipRows->end_row}}</div>
                            @endif
                            @if(!empty($Options->Sheet))
                                <div>Sheet Name : {{$Options->Sheet}}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif
        @if( isset($job_file->FilePath) && !empty($job_file->FilePath))
            <div class="row">
                <div class="col-md-12">
         <div class="form-group">
            <label for="field-1" class="control-label  bold">Download File</label>
            <div ><a href="{{URL::to('/jobs/'.$job_file->JobID.'/download_excel')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a></div>
        </div>
                </div>
            </div>
        @elseif( isset($job->OutputFilePath) && !empty($job->OutputFilePath) && $job->OutputFilePath != 'No data found!')
            <div class="row">
                <div class="col-md-12">
         <div class="form-group">
            <label for="field-1" class="control-label  bold">{{$file_title}}</label>
            <div>
            <a href="{{URL::to('/jobs/'.$job->JobID.'/downloaoutputfile')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>
            </div>
        </div>
                </div>
            </div>
        @elseif(!empty($job->OutputFilePath))
            <div class="row">
                <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label bold">{{$file_title}}</label>
            <div >
            No data found!
            </div>
        </div>
                </div>
            </div>
        @endif
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Date Created</label>
            <div >{{$job->created_at}}</div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Created By</label>
            <div >{{$job->CreatedBy}}</div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="field-1" class="control-label  bold">Processed Date</label>
            <div >{{$job->updated_at}}</div>
        </div>
    </div>
</div>
        <!--
        <div class="form-group">
        <label for="field-1" class="control-label  bold"><strong>Modified By</strong></label>
        <div >{{$job->ModifiedBy}}</div>
        </div>
        -->
@endif