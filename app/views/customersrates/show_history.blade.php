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