<?php

class Job extends \Eloquent {

    protected $fillable = ['PID'];
    protected $table = "tblJob";
    protected $primaryKey = "JobID";
    public $timestamps = false; // no created_at and updated_at

    public static function logJob($JobType, $options = "") {
        switch ($JobType) {
            case 'VU':
                /*
                 *  Vendor Upload  Job Log
                 */
                $rules = array(
                    'CompanyID' => 'required',
                    'JobTypeID' => 'required',
                    'JobStatusID' => 'required',
                    'JobLoggedUserID' => 'required',
                    'Title' => 'required',
                    'CreatedBy' => 'required',
                );

                $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

                /* [CompanyID] [int] NOT NULL,
                  [JobTypeID] [tinyint] NOT NULL,
                  [JobStatusID] [tinyint] NOT NULL,
                  [JobLoggedUserID] [int] NOT NULL,
                  [TemplateID] [int] NULL,
                  [Title] [nvarchar](50) NOT NULL,
                  [Description] [nvarchar](200) NULL,
                  [JobStatusMessage] [nvarchar](max) NULL,
                  [created_at] [datetime] NOT NULL,
                  [CreatedBy] [nvarchar](100) NULL,
                  [updated_at] [datetime] NULL,
                  [ModifiedBy] [nvarchar](100) NULL, */

                $CompanyID = User::get_companyID();
                $options["CompanyID"] = $CompanyID;
                $data["CompanyID"] = $CompanyID;
                $data["AccountID"] = $options["AccountID"];
                $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $data["JobLoggedUserID"] = User::get_userID();
                $data["Title"] = Account::getCompanyNameByID($data["AccountID"]) ;
                $data["Description"] = Account::getCompanyNameByID($data["AccountID"]) . ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $data["CreatedBy"] = User::get_user_full_name();
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["created_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return validator_response($validator);
                }

                /*
                 * Double check if file is uploaded correctly before loging job
                 * */

                $rules = array(
                    'FileName' => 'required',
                    'FilePath' => 'required',
                    'HttpPath' => 'required',
                    'Options' => 'required',
                    'CreatedBy' => 'required',
                );

                $JobFile_data_  = array();
                $JobFile_data_["FileName"] = basename($options["full_path"]);
                $JobFile_data_["FilePath"] = $options["full_path"];
                $JobFile_data_["HttpPath"] = 0;
                $JobFile_data_["Options"] =  json_encode(self::removeUnnecesorryOptions($JobType,$options) );
                $JobFile_data_["CreatedBy"] = User::get_user_full_name();
                $JobFile_data_["created_at"] = date('Y-m-d H:i:s');
                $JobFile_data_["updated_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($JobFile_data_, $rules);
                if ($validator->fails()) {
                    return validator_response($validator);
                }
                /*
                  Job Insers Here
                 */

                if ($JobID = Job::insertGetId($data)) {
                    RateSheetHistory::AddtoHistory($JobID,'VU',$data);

                    /*
                     * JobFile Insers here 
                     */
                    $rules = array(
                        'JobID' => 'required',
                        'FileName' => 'required',
                        'FilePath' => 'required',
                        'HttpPath' => 'required',
                        'Options' => 'required',
                        'CreatedBy' => 'required',
                    );

                    /* [JobID] [int] NOT NULL,
                      [FileName] [nvarchar](255) NOT NULL,
                      [FilePath] [nvarchar](max) NULL,
                      [HttpPath] [bit] NOT NULL,
                      [Options] [nvarchar](max) NULL,
                      [CreatedBy] [nvarchar](100)   NULL,
                      [ModifiedBy] [nvarchar](100) NULL,
                     */

                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];
                    $data["HttpPath"] = 0;
                    $data["Options"] =  json_encode(self::removeUnnecesorryOptions($JobType,$options) );
                    $data["CreatedBy"] = User::get_user_full_name();
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');
                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return validator_response($validator);
                    }
                    if ($JobFileID = JobFile::insertGetId($data)) {
                        return array("status" => "success", "message" => "Job Logged Successfully");
                    } else {
                        return array("status" => "failed", "message" => "JobFile Insertion Error");
                    }
                } else {
                    return array("status" => "failed", "message" => "Problem Inserting Job.");
                }

                break;
            case 'CDU':
                /*
                 *  CodeDecks Upload  Job Log
                 */
                $rules = array(
                    'CompanyID' => 'required',
                    'JobTypeID' => 'required',
                    'JobStatusID' => 'required',
                    'JobLoggedUserID' => 'required',
                    'Title' => 'required',
                    'CreatedBy' => 'required',
                );

                $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

                /* [CompanyID] [int] NOT NULL,
                  [JobTypeID] [tinyint] NOT NULL,
                  [JobStatusID] [tinyint] NOT NULL,
                  [JobLoggedUserID] [int] NOT NULL,
                  [TemplateID] [int] NULL,
                  [Title] [nvarchar](50) NOT NULL,
                  [Description] [nvarchar](200) NULL,
                  [JobStatusMessage] [nvarchar](max) NULL,
                  [created_at] [datetime] NOT NULL,
                  [CreatedBy] [nvarchar](100) NULL,
                  [updated_at] [datetime] NULL,
                  [ModifiedBy] [nvarchar](100) NULL, */
                $CompanyID = User::get_companyID();
                $options["CompanyID"] = $CompanyID;
                $data["CompanyID"] = $CompanyID;
                $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $data["JobLoggedUserID"] = User::get_userID();
                if(!empty($options['codedeckname'])){
                    $codedeckname = $options['codedeckname'];
                }else{
                    $codedeckname = '';
                }
                $data["Title"] =  $codedeckname;
                $data["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $data["CreatedBy"] = User::get_user_full_name();
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["created_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return validator_response($validator);
                }
                /*
                 * Check File is uploaded before job log.
                 * */
                $rules_ = array(
                    'FileName' => 'required',
                    'FilePath' => 'required',
                    'HttpPath' => 'required',
                    'Options' => 'required',
                    'CreatedBy' => 'required',
                );
                $JobFile_data_ = array();
                $JobFile_data_["FileName"] = basename($options["full_path"]);
                $JobFile_data_["FilePath"] = $options["full_path"] ; //(is_file($options["full_path"]) && file_exists($options["full_path"]))?$options["full_path"]:'';
                $JobFile_data_["HttpPath"] = 0;
                $JobFile_data_["Options"] =  json_encode(self::removeUnnecesorryOptions($jobType,$options) );
                $JobFile_data_["CreatedBy"] = User::get_user_full_name();
                $JobFile_data_["created_at"] = date('Y-m-d H:i:s');
                $JobFile_data_["updated_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($JobFile_data_, $rules_);
                if ($validator->fails()) {
                    return validator_response($validator);
                }


                /*
                  Job Insers Here
                 */

                if ($JobID = Job::insertGetId($data)) {


                    /*
                     * JobFile Insers here 
                     */
                    $rules = array(
                        'JobID' => 'required',
                        'FileName' => 'required',
                        'FilePath' => 'required',
                        'HttpPath' => 'required',
                        'Options' => 'required',
                        'CreatedBy' => 'required',
                    );

                    /* [JobID] [int] NOT NULL,
                      [FileName] [nvarchar](255) NOT NULL,
                      [FilePath] [nvarchar](max) NULL,
                      [HttpPath] [bit] NOT NULL,
                      [Options] [nvarchar](max) NULL,
                      [CreatedBy] [nvarchar](100)   NULL,
                      [ModifiedBy] [nvarchar](100) NULL,
                     */

                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];//(is_file($options["full_path"]) && file_exists($options["full_path"]))?$options["full_path"]:'';
                    $data["HttpPath"] = 0;
                    $data["Options"] =  json_encode(self::removeUnnecesorryOptions($jobType,$options) );
                    $data["CreatedBy"] = User::get_user_full_name();
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');

                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return validator_response($validator);
                    }
                    if ($JobFileID = JobFile::insertGetId($data)) {
                        return array("status" => "success", "message" => "Job Logged Successfully");
                    } else {
                        return array("status" => "failed", "message" => "JobFile Insertion Error");
                    }
                } else {
                    return array("status" => "failed", "message" => "Problem Inserting Job.");
                }

                break;
            case 'PU':
                /*
                 *  Payment Upload  Job Log
                 */
                $rules = array(
                    'CompanyID' => 'required',
                    'JobTypeID' => 'required',
                    'JobStatusID' => 'required',
                    'JobLoggedUserID' => 'required',
                    'Title' => 'required',
                    'CreatedBy' => 'required',
                );

                $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

                $CompanyID = User::get_companyID();
                $options["CompanyID"] = $CompanyID;
                $data["CompanyID"] = $CompanyID;
                $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $data["JobLoggedUserID"] = User::get_userID();
                $data["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
                $data["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $data["CreatedBy"] = User::get_user_full_name();
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["created_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return validator_response($validator);
                }
                /*
                 * Check File is uploaded before job log.
                 * */
                $rules_ = array(
                    'FileName' => 'required',
                    'FilePath' => 'required',
                    'HttpPath' => 'required',
                    'CreatedBy' => 'required',
                );
                $JobFile_data_ = array();
                $JobFile_data_["FileName"] = basename($options["full_path"]);
                $JobFile_data_["FilePath"] = $options["full_path"] ; //(is_file($options["full_path"]) && file_exists($options["full_path"]))?$options["full_path"]:'';
                $JobFile_data_["HttpPath"] = 0;
                $JobFile_data_["Options"] =  '';
                $JobFile_data_["CreatedBy"] = User::get_user_full_name();
                $JobFile_data_["created_at"] = date('Y-m-d H:i:s');
                $JobFile_data_["updated_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($JobFile_data_, $rules_);
                if ($validator->fails()) {
                    return validator_response($validator);
                }


                /*
                  Job Insers Here
                 */

                if ($JobID = Job::insertGetId($data)) {


                    /*
                     * JobFile Insers here
                     */
                    $rules = array(
                        'JobID' => 'required',
                        'FileName' => 'required',
                        'FilePath' => 'required',
                        'HttpPath' => 'required',
                        'CreatedBy' => 'required',
                    );

                    /* [JobID] [int] NOT NULL,
                      [FileName] [nvarchar](255) NOT NULL,
                      [FilePath] [nvarchar](max) NULL,
                      [HttpPath] [bit] NOT NULL,
                      [Options] [nvarchar](max) NULL,
                      [CreatedBy] [nvarchar](100)   NULL,
                      [ModifiedBy] [nvarchar](100) NULL,
                     */

                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];//(is_file($options["full_path"]) && file_exists($options["full_path"]))?$options["full_path"]:'';
                    $data["HttpPath"] = 0;
                    $data["Options"] =  '';
                    $data["CreatedBy"] = User::get_user_full_name();
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');

                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return validator_response($validator);
                    }
                    if ($JobFileID = JobFile::insertGetId($data)) {
                        return array("status" => "success", "message" => "Job Logged Successfully");
                    } else {
                        return array("status" => "failed", "message" => "JobFile Insertion Error");
                    }
                } else {
                    return array("status" => "failed", "message" => "Problem Inserting Job.");
                }

                break;
            case 'RTU':
                /*
                 *  Rate Table Upload  Job Log
                 */
                $rules = array(
                    'CompanyID' => 'required',
                    'JobTypeID' => 'required',
                    'JobStatusID' => 'required',
                    'JobLoggedUserID' => 'required',
                    'Title' => 'required',
                    'CreatedBy' => 'required',
                );

                $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

                /* [CompanyID] [int] NOT NULL,
                  [JobTypeID] [tinyint] NOT NULL,
                  [JobStatusID] [tinyint] NOT NULL,
                  [JobLoggedUserID] [int] NOT NULL,
                  [TemplateID] [int] NULL,
                  [Title] [nvarchar](50) NOT NULL,
                  [Description] [nvarchar](200) NULL,
                  [JobStatusMessage] [nvarchar](max) NULL,
                  [created_at] [datetime] NOT NULL,
                  [CreatedBy] [nvarchar](100) NULL,
                  [updated_at] [datetime] NULL,
                  [ModifiedBy] [nvarchar](100) NULL, */

                $CompanyID = User::get_companyID();
                $options["CompanyID"] = $CompanyID;
                $data["CompanyID"] = $CompanyID;
                $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $data["JobLoggedUserID"] = User::get_userID();
                if(!empty($options['ratetablename'])){
                    $ratetablename = $options['ratetablename'];
                }else{
                    $ratetablename = '';
                }
                $data["Title"] = $ratetablename;
                $data["Description"] = ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $data["CreatedBy"] = User::get_user_full_name();
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["created_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return validator_response($validator);
                }

                /*
                 * Double check if file is uploaded correctly before loging job
                 * */

                $rules = array(
                    'FileName' => 'required',
                    'FilePath' => 'required',
                    'HttpPath' => 'required',
                    'Options' => 'required',
                    'CreatedBy' => 'required',
                );

                $JobFile_data_  = array();
                $JobFile_data_["FileName"] = basename($options["full_path"]);
                $JobFile_data_["FilePath"] = $options["full_path"];
                $JobFile_data_["HttpPath"] = 0;
                $JobFile_data_["Options"] =  json_encode(self::removeUnnecesorryOptions($JobType,$options) );
                $JobFile_data_["CreatedBy"] = User::get_user_full_name();
                $JobFile_data_["created_at"] = date('Y-m-d H:i:s');
                $JobFile_data_["updated_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($JobFile_data_, $rules);
                if ($validator->fails()) {
                    return validator_response($validator);
                }
                /*
                  Job Insers Here
                 */
                if ($JobID = Job::insertGetId($data)) {
                    RateSheetHistory::AddtoHistory($JobID,'RTU',$data);

                    /*
                     * JobFile Insers here
                     */
                    $rules = array(
                        'JobID' => 'required',
                        'FileName' => 'required',
                        'FilePath' => 'required',
                        'HttpPath' => 'required',
                        'Options' => 'required',
                        'CreatedBy' => 'required',
                    );

                    /* [JobID] [int] NOT NULL,
                      [FileName] [nvarchar](255) NOT NULL,
                      [FilePath] [nvarchar](max) NULL,
                      [HttpPath] [bit] NOT NULL,
                      [Options] [nvarchar](max) NULL,
                      [CreatedBy] [nvarchar](100)   NULL,
                      [ModifiedBy] [nvarchar](100) NULL,
                     */

                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];
                    $data["HttpPath"] = 0;
                    $data["Options"] =  json_encode(self::removeUnnecesorryOptions($JobType,$options) );
                    $data["CreatedBy"] = User::get_user_full_name();
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');
                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return validator_response($validator);
                    }
                    if ($JobFileID = JobFile::insertGetId($data)) {
                        return array("status" => "success", "message" => "Job Logged Successfully");
                    } else {
                        return array("status" => "failed", "message" => "JobFile Insertion Error");
                    }
                } else {
                    return array("status" => "failed", "message" => "Problem Inserting Job.");
                }

                break;

            case 'DSU':
                /*
                 *  DialString Upload  Job Log
                 */
                $rules = array(
                    'CompanyID' => 'required',
                    'JobTypeID' => 'required',
                    'JobStatusID' => 'required',
                    'JobLoggedUserID' => 'required',
                    'Title' => 'required',
                    'CreatedBy' => 'required',
                );

                $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

                $CompanyID = User::get_companyID();
                $options["CompanyID"] = $CompanyID;
                $data["CompanyID"] = $CompanyID;
                $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $data["JobLoggedUserID"] = User::get_userID();
                if(!empty($options['dialstringname'])){
                    $dialstringname = $options['dialstringname'];
                }else{
                    $dialstringname = '';
                }
                $data["Title"] = $dialstringname;
                $data["Description"] = ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $data["CreatedBy"] = User::get_user_full_name();
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["created_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return validator_response($validator);
                }

                /*
                 * Double check if file is uploaded correctly before loging job
                 * */

                $rules = array(
                    'FileName' => 'required',
                    'FilePath' => 'required',
                    'HttpPath' => 'required',
                    'Options' => 'required',
                    'CreatedBy' => 'required',
                );

                $JobFile_data_  = array();
                $JobFile_data_["FileName"] = basename($options["full_path"]);
                $JobFile_data_["FilePath"] = $options["full_path"];
                $JobFile_data_["HttpPath"] = 0;
                $JobFile_data_["Options"] =  json_encode(self::removeUnnecesorryOptions($JobType,$options) );
                $JobFile_data_["CreatedBy"] = User::get_user_full_name();
                $JobFile_data_["created_at"] = date('Y-m-d H:i:s');
                $JobFile_data_["updated_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($JobFile_data_, $rules);
                if ($validator->fails()) {
                    return validator_response($validator);
                }
                /*
                  Job Insers Here
                 */

                if ($JobID = Job::insertGetId($data)) {


                    /*
                     * JobFile Insers here
                     */
                    $rules = array(
                        'JobID' => 'required',
                        'FileName' => 'required',
                        'FilePath' => 'required',
                        'HttpPath' => 'required',
                        'Options' => 'required',
                        'CreatedBy' => 'required',
                    );

                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];//(is_file($options["full_path"]) && file_exists($options["full_path"]))?$options["full_path"]:'';
                    $data["HttpPath"] = 0;
                    $data["Options"] =  json_encode(self::removeUnnecesorryOptions($jobType,$options) );
                    $data["CreatedBy"] = User::get_user_full_name();
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');

                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return validator_response($validator);
                    }
                    if ($JobFileID = JobFile::insertGetId($data)) {
                        return array("status" => "success", "message" => "Job Logged Successfully");
                    } else {
                        return array("status" => "failed", "message" => "JobFile Insertion Error");
                    }
                } else {
                    return array("status" => "failed", "message" => "Problem Inserting Job.");
                }

                break;
            case 'ICU':
                /*
                 *  Ip Upload  Job Log
                 */
                $rules = array(
                    'CompanyID' => 'required',
                    'JobTypeID' => 'required',
                    'JobStatusID' => 'required',
                    'JobLoggedUserID' => 'required',
                    'Title' => 'required',
                    'CreatedBy' => 'required',
                );

                $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

                $CompanyID = User::get_companyID();
                $options["CompanyID"] = $CompanyID;
                $data["CompanyID"] = $CompanyID;
                $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $data["JobLoggedUserID"] = User::get_userID();
                $data["Title"] = 'IP Upload';
                $data["Description"] = ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $data["CreatedBy"] = User::get_user_full_name();
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["created_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return validator_response($validator);
                }

                /*
                 * Double check if file is uploaded correctly before loging job
                 * */

                $rules = array(
                    'FileName' => 'required',
                    'FilePath' => 'required',
                    'HttpPath' => 'required',
                    'Options' => 'required',
                    'CreatedBy' => 'required',
                );

                $JobFile_data_  = array();
                $JobFile_data_["FileName"] = basename($options["full_path"]);
                $JobFile_data_["FilePath"] = $options["full_path"];
                $JobFile_data_["HttpPath"] = 0;
                $JobFile_data_["Options"] =  json_encode(self::removeUnnecesorryOptions($JobType,$options) );
                $JobFile_data_["CreatedBy"] = User::get_user_full_name();
                $JobFile_data_["created_at"] = date('Y-m-d H:i:s');
                $JobFile_data_["updated_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($JobFile_data_, $rules);
                if ($validator->fails()) {
                    return validator_response($validator);
                }
                /*
                  Job Insers Here
                 */

                if ($JobID = Job::insertGetId($data)) {


                    /*
                     * JobFile Insers here
                     */
                    $rules = array(
                        'JobID' => 'required',
                        'FileName' => 'required',
                        'FilePath' => 'required',
                        'HttpPath' => 'required',
                        'Options' => 'required',
                        'CreatedBy' => 'required',
                    );

                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];//(is_file($options["full_path"]) && file_exists($options["full_path"]))?$options["full_path"]:'';
                    $data["HttpPath"] = 0;
                    $data["Options"] =  json_encode(self::removeUnnecesorryOptions($jobType,$options) );
                    $data["CreatedBy"] = User::get_user_full_name();
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');

                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return validator_response($validator);
                    }
                    if ($JobFileID = JobFile::insertGetId($data)) {
                        return array("status" => "success", "message" => "Job Logged Successfully");
                    } else {
                        return array("status" => "failed", "message" => "JobFile Insertion Error");
                    }
                } else {
                    return array("status" => "failed", "message" => "Problem Inserting Job.");
                }

                break;
            case 'MGA':
                /*
                 *  Import/upload account  Job Log
                 */
                $rules = array(
                    'CompanyID' => 'required',
                    'JobTypeID' => 'required',
                    'JobStatusID' => 'required',
                    'JobLoggedUserID' => 'required',
                    'Title' => 'required',
                    'CreatedBy' => 'required',
                );

                $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

                /* [CompanyID] [int] NOT NULL,
                  [JobTypeID] [tinyint] NOT NULL,
                  [JobStatusID] [tinyint] NOT NULL,
                  [JobLoggedUserID] [int] NOT NULL,
                  [TemplateID] [int] NULL,
                  [Title] [nvarchar](50) NOT NULL,
                  [Description] [nvarchar](200) NULL,
                  [JobStatusMessage] [nvarchar](max) NULL,
                  [created_at] [datetime] NOT NULL,
                  [CreatedBy] [nvarchar](100) NULL,
                  [updated_at] [datetime] NULL,
                  [ModifiedBy] [nvarchar](100) NULL, */

                $CompanyID = User::get_companyID();
                $options["CompanyID"] = $CompanyID;
                $data["CompanyID"] = $CompanyID;
                $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $data["JobLoggedUserID"] = User::get_userID();
                $AccountType = $options["AccountType"];
                if($AccountType==0){
                    $data["Title"] = 'Import Leads ';
                }elseif($AccountType==1){
                    $data["Title"] = 'Import Accounts ';
                }else{
                    $data["Title"] = (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
                }
                $data["Description"] = ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $data["CreatedBy"] = User::get_user_full_name();
                $data["updated_at"] = date('Y-m-d H:i:s');
                $data["created_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return validator_response($validator);
                }

                /*
                 * Double check if file is uploaded correctly before loging job
                 * */

                $rules = array(
                    'FileName' => 'required',
                    'FilePath' => 'required',
                    'HttpPath' => 'required',
                    'Options' => 'required',
                    'CreatedBy' => 'required',
                );

                $JobFile_data_  = array();
                $JobFile_data_["FileName"] = basename($options["full_path"]);
                $JobFile_data_["FilePath"] = $options["full_path"];
                $JobFile_data_["HttpPath"] = 0;
                $JobFile_data_["Options"] =  json_encode(self::removeUnnecesorryOptions($JobType,$options) );
                $JobFile_data_["CreatedBy"] = User::get_user_full_name();
                $JobFile_data_["created_at"] = date('Y-m-d H:i:s');
                $JobFile_data_["updated_at"] = date('Y-m-d H:i:s');

                $validator = Validator::make($JobFile_data_, $rules);
                if ($validator->fails()) {
                    return validator_response($validator);
                }
                /*
                  Job Insers Here
                 */

                if ($JobID = Job::insertGetId($data)) {


                    /*
                     * JobFile Insers here
                     */
                    $rules = array(
                        'JobID' => 'required',
                        'FileName' => 'required',
                        'FilePath' => 'required',
                        'HttpPath' => 'required',
                        'Options' => 'required',
                        'CreatedBy' => 'required',
                    );

                    /* [JobID] [int] NOT NULL,
                      [FileName] [nvarchar](255) NOT NULL,
                      [FilePath] [nvarchar](max) NULL,
                      [HttpPath] [bit] NOT NULL,
                      [Options] [nvarchar](max) NULL,
                      [CreatedBy] [nvarchar](100)   NULL,
                      [ModifiedBy] [nvarchar](100) NULL,
                     */

                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];//(is_file($options["full_path"]) && file_exists($options["full_path"]))?$options["full_path"]:'';
                    $data["HttpPath"] = 0;
                    $data["Options"] =  json_encode(self::removeUnnecesorryOptions($jobType,$options) );
                    $data["CreatedBy"] = User::get_user_full_name();
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');

                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return validator_response($validator);
                    }
                    if ($JobFileID = JobFile::insertGetId($data)) {
                        return array("status" => "success", "message" => "Job Logged Successfully");
                    } else {
                        return array("status" => "failed", "message" => "JobFile Insertion Error");
                    }
                } else {
                    return array("status" => "failed", "message" => "Problem Inserting Job.");
                }

                break;

            case 'CD': // Customer Rate Sheet Download

                return self::JobLogCustomerVendorDownload($JobType, $options);

                break;

            case 'VD': //Vendoer Rate Sheet Download

                return self::JobLogCustomerVendorDownload($JobType, $options);

                break;

            case 'INU': // Invoice Usage File Create

                return self::GenerateInvoiceUsageFile($JobType, $options);
            case 'GRT': // Generate Rate Table

                return self::GenerateRateTable($JobType, $options);

        }
    }

    public static function GenerateRateTable($JobType, $options = "") {
        /*
         *  Generate Rate Table Log
         */
        $rules = array(
            'CompanyID' => 'required',
            'JobTypeID' => 'required',
            'JobStatusID' => 'required',
            'JobLoggedUserID' => 'required',
            'Title' => 'required',
            'CreatedBy' => 'required',
            'Options' => 'required',
        );

        $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
        $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

        /* [CompanyID] [int] NOT NULL,
          [JobTypeID] [tinyint] NOT NULL,
          [JobStatusID] [tinyint] NOT NULL,
          [JobLoggedUserID] [int] NOT NULL,
          [TemplateID] [int] NULL,
          [Title] [nvarchar](50) NOT NULL,
          [Description] [nvarchar](200) NULL,
          [JobStatusMessage] [nvarchar](max) NULL,
          [created_at] [datetime] NOT NULL,
          [CreatedBy] [nvarchar](100) NULL,
          [updated_at] [datetime] NULL,
          [ModifiedBy] [nvarchar](100) NULL, */
        $CompanyID = User::get_companyID();
        //$options["CompanyID"] = User::get_companyID();
        //$data["AccountID"] = $options["AccountID"];
        $data["CompanyID"] = $CompanyID;
        $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
        $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
        $data["JobLoggedUserID"] = User::get_userID();
        $data["CreatedBy"] = User::get_user_full_name();

        if(!empty($options['ratetablename'])){
            $ratetablename = $options['ratetablename'];
        }else{
            $ratetablename = '';
        }
        $data["Title"] =   $ratetablename;
        $data["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
        $data["Options"] =  json_encode(self::removeUnnecesorryOptions($jobType,$options) );
        $data["updated_at"] = date('Y-m-d H:i:s');
        $data["created_at"] = date('Y-m-d H:i:s');

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return validator_response($validator);
        }

        if ($JobID = Job::insertGetId($data)) {
            return array("status" => "success", "message" => "Job Logged Successfully");
        } else {
            return array("status" => "failed", "message" => "Problem Inserting Job.");
        }
    }

    public static function JobLogCustomerVendorDownload($JobType, $options = "") {
        /*
         *  Customer Download  Job Log
         */
        $rules = array(
            'CompanyID' => 'required',
            'JobTypeID' => 'required',
            'JobStatusID' => 'required',
            'JobLoggedUserID' => 'required',
            'Title' => 'required',
            'CreatedBy' => 'required',
            'Options' => 'required',
        );

        $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
        $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);

        /* [CompanyID] [int] NOT NULL,
          [JobTypeID] [tinyint] NOT NULL,
          [JobStatusID] [tinyint] NOT NULL,
          [JobLoggedUserID] [int] NOT NULL,
          [TemplateID] [int] NULL,
          [Title] [nvarchar](50) NOT NULL,
          [Description] [nvarchar](200) NULL,
          [JobStatusMessage] [nvarchar](max) NULL,
          [created_at] [datetime] NOT NULL,
          [CreatedBy] [nvarchar](100) NULL,
          [updated_at] [datetime] NULL,
          [ModifiedBy] [nvarchar](100) NULL, */
        $CompanyID = User::get_companyID();
        $options["CompanyID"] = $CompanyID;

        $data["updated_at"] = date('Y-m-d H:i:s');
        $data["created_at"] = date('Y-m-d H:i:s');

        if($options['Effective'] == "CustomDate") {
            $Effective = $options['CustomDate'];
        } else {
            $Effective = $options['Effective'];
        }

        if (isset($options['isMerge']) && $options['isMerge'] == 1) {

            $data["AccountID"] = $options["AccountID"];
            $data["CompanyID"] = $CompanyID;
            $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
            $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
            $data["JobLoggedUserID"] = User::get_userID();
            $data["CreatedBy"] = User::get_user_full_name();

            if(!empty($options['Format'])){
                $format = "(".$options['Format'].")";
            }else{
                $format = "";
            }

            $Title = Account::getCompanyNameByID($data["AccountID"]) . ' ' . $format.' ('.$Effective.')';
            $data["Description"] = Account::getCompanyNameByID($data["AccountID"]) . ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';

            $timezones = $options['Timezones'];
            $i = 0;
            foreach ((array) $timezones as $timezone) {

                $options['Timezones'] = $timezone;
                $data["Options"] = json_encode(self::removeUnnecesorryOptions($jobType, $options));

                $TimezoneTitle = Timezones::getTimezonesName($timezone);
                $data["Title"] = $Title . ' (' . $TimezoneTitle . ')';

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return validator_response($validator);
                }

                $JobID = Job::insertGetId($data);
                RateSheetHistory::AddtoHistory($JobID, $JobType, $data);
                $i++;
            }

            if ($i == count($timezones)) {
                return array("status" => "success", "message" => "Job Logged Successfully");
            } else {
                return array("status" => "failed", "message" => "Problem Inserting Job.");
            }
        } else {
            $data["AccountID"] = $options["AccountID"];
            $data["CompanyID"] = $CompanyID;
            $data["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
            $data["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
            $data["JobLoggedUserID"] = User::get_userID();
            $data["CreatedBy"] = User::get_user_full_name();

            if(!empty($options['Format'])){
                $format = "(".$options['Format'].")";
            }else{
                $format = "";
            }

            $Title = Account::getCompanyNameByID($data["AccountID"]) . ' ' . $format.' ('.$Effective.') ';
            $data["Description"] = Account::getCompanyNameByID($data["AccountID"]) . ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';

            $trunks = $options['Trunks'];
            $timezones = $options['Timezones'];
            //$options_ = $options; // Duplicate
            unset($options['Trunks']);
            $options['isMerge'] = '0';
            $i = 0;
            foreach ((array) $trunks as $trunk) {
                $options['Trunks'] = $trunk;
                foreach ((array) $timezones as $timezone) {

                    $options['Timezones'] = $timezone;
                    $data["Options"] = json_encode(self::removeUnnecesorryOptions($jobType, $options));

                    $TimezoneTitle = Timezones::getTimezonesName($timezone);
                    $data["Title"] = $Title . ' (' . $TimezoneTitle . ')';

                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return validator_response($validator);
                    }

                    $JobID = Job::insertGetId($data);
                    RateSheetHistory::AddtoHistory($JobID, $JobType, $data);
                }
                $i++;
            }

            if ($i == count($trunks)) {
                return array("status" => "success", "message" => "Job Logged Successfully");
            } else {
                return array("status" => "failed", "message" => "Problem Inserting Job.");
            }
        }
    }

    /*
      Get All Jobs to show in Header Dropdown
      ###Moved to Stored Procedure
     */
    public static function getAllHeaderJobs($limit = 10) {

        $CompanyID = User::get_companyID();
        //When admin show all jobs by all user.
        if (User::is_admin()) {

            $jobs = DB::table('tblJob')->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                    ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                    ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.HasRead','tblJob.CreatedBy', 'tblJob.created_at')
                    ->where("tblJob.CompanyID", $CompanyID)
                    ->orderBy("tblJob.ShowInCounter", "desc")
                    ->orderBy("tblJob.updated_at", "desc")
                    ->take($limit)
                    ->get();
            /*$queries = DB::getQueryLog();
            $last_query = end($queries);
            print_r($last_query);*/

            return $jobs;
        } else {
            $userID = User::get_userID();

            $jobs = DB::table('tblJob')->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                    ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                    ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status','tblJob.HasRead', 'tblJob.CreatedBy', 'tblJob.created_at')
                    //->where("tblJobStatus.Title" , '!=' , "Completed")
                    ->where("tblJob.CompanyID", $CompanyID)
                    ->where("tblJob.JobLoggedUserID", $userID)
                    ->orderBy("tblJob.updated_at", "desc")
                    ->orderBy("tblJob.ShowInCounter", "asc")
                    ->take($limit)
                    ->get();
            return $jobs;
        }
    }

    /*
      Get All Jobs
     */

    public static function getAllJobs($limit = 10) {
        $CompanyID = User::get_companyID();
        //When admin show all jobs by all user.
        if (User::is_admin()) {

            $jobs = DB::table('tblJob')->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                    ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                    ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.CreatedBy', 'tblJob.created_at')
                    ->where("tblJobStatus.Title", '!=', "Completed")
                    ->where("tblJob.CompanyID", $CompanyID)
                    ->orderBy("tblJob.JobID", "desc")
                    ->take($limit)
                    ->get();
            return $jobs;
        } else {
            $userID = User::get_userID();

            $jobs = DB::table('tblJob')->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                    ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                    ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.CreatedBy', 'tblJob.created_at')
                    ->where("tblJobStatus.Title", '!=', "Completed")
                    ->where("tblJob.CompanyID", $CompanyID)
                    ->where("tblJob.JobLoggedUserID", $userID)
                    ->orderBy("tblJob.JobID", "desc")
                    ->take($limit)
                    ->get();
            return $jobs;
        }
    }

    /*
      Get Total UnCompleted Jobs Count
        ###Moved to Stored Procedure
     */

    public static function getTotalPendingJobs() {
        $CompanyID = User::get_companyID();
        //When admin show all jobs by all user.
        if (User::is_admin()) {

            $totalJobs = DB::table('tblJob')
                    ->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                    ->where("tblJobStatus.Title", "Pending")
                    ->where("tblJob.CompanyID", $CompanyID)
                    ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.CreatedBy', 'tblJob.created_at')
                    ->count();
            return $totalJobs;
        } else {
            $userID = User::get_userID();
            $totalJobs = DB::table('tblJob')
                    ->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                    ->where("tblJobStatus.Title" , "Pending")
                    ->where("tblJob.CompanyID", $CompanyID)
                    //->where("tblJobStatus.Title", '!=', "Completed")
                    ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.CreatedBy', 'tblJob.created_at')
                    ->where("tblJob.JobLoggedUserID", $userID)
                    ->count();
            return $totalJobs;
        }
    }

    /*
      Get Last 7 Days Processed Files
     */

    public static function getLast7DaysProcessedFiles($limit = 10) {

        $CompanyID = User::get_companyID();
        //When admin show all jobs by all user.
        if (Session::get('isAdmin')) {

            $jobs = DB::table('tblJob')->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                ->join('tblJobFile', 'tblJobFile.JobID', '=', 'tblJob.JobID')
                ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.CreatedBy', 'tblJob.created_at')
                ->where("tblJob.CompanyID", $CompanyID)
                ->whereRaw( "( tblJobStatus.Title = 'Success' OR  tblJobStatus.Title = 'Completed' )")
                ->where("tblJob.updated_at" , ">=" , \Carbon\Carbon::now()->subDays(7) )
                ->orderBy("tblJob.JobID", "desc")
                ->take($limit)
                ->get();
            return $jobs;
        } else {
            $userID = User::get_userID();

            $jobs = DB::table('tblJob')->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.CreatedBy', 'tblJob.created_at')
                ->whereRaw( "( tblJobStatus.Title = 'Success' OR  tblJobStatus.Title = 'Completed' )")
                ->where("tblJob.CompanyID", $CompanyID)
                ->where("tblJob.JobLoggedUserID", $userID)
                ->where("tblJob.updated_at" , ">=" , \Carbon\Carbon::now()->subDays(7) )
                ->orderBy("tblJob.JobID", "desc")
                ->take($limit)
                ->get();
            return $jobs;
        }
    }
    /*
     * Show Alert Counter for Job
     * ###Moved to Stored Procedure
     * */
    public static function getAlertCounterNonVisitedJobs() {
        $CompanyID = User::get_companyID();
        //When admin show all jobs by all user.
        if (User::is_admin()) {

            $totalJobs = DB::table('tblJob')
                ->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                ->where("tblJobStatus.Title", "Pending")
                ->where("tblJob.CompanyID", $CompanyID)
                ->where("tblJob.ShowInCounter", 1)
                ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.CreatedBy', 'tblJob.created_at')
                ->count();
            return $totalJobs;
        } else {
            $userID = User::get_userID();
            $totalJobs = DB::table('tblJob')
                ->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                ->where("tblJobStatus.Title" , "Pending")
                ->where("tblJob.CompanyID", $CompanyID)
                ->where("tblJob.ShowInCounter", 1)
                //->where("tblJobStatus.Title", '!=', "Completed")
                ->select('tblJob.JobID', 'tblJob.Title', 'tblJobStatus.Title as Status', 'tblJob.CreatedBy', 'tblJob.created_at')
                ->where("tblJob.JobLoggedUserID", $userID)
                ->count();
            return $totalJobs;
        }
    }

    /*
     * Set tblJob.ShowInCounter = 0 to not show Job Alert Counter in Dashboard Top
     * */
    public static function resetShowInCounter(){
        $CompanyID = User::get_companyID();
        //When admin show all jobs by all user.
        if (User::is_admin()) {

            Job::where("CompanyID", $CompanyID)
                ->where("ShowInCounter", 1)
                ->update(array("ShowInCounter"=>'0'));

        } else {
            $userID = User::get_userID();
            Job::where("CompanyID", $CompanyID)
                ->where("ShowInCounter", 1)
                ->where("JobLoggedUserID", $userID)
                ->update(array("ShowInCounter"=>'0'));
        }

    }

    /*
     * Set tblJob.HasRead = 0 to show This notification is visited
     * */
    public static function jobRead($JobId){
        $CompanyID = User::get_companyID();
        //When admin show all jobs by all user.
        if (User::is_admin()) {
            Job::where("JobId", $JobId)
                ->where("CompanyID", $CompanyID)
                ->where("HasRead", 0)
                ->update(array("HasRead"=>1));

        } else {
            $userID = User::get_userID();
            Job::where("JobId", $JobId)
                ->where("CompanyID", $CompanyID)
                ->where("HasRead", 0)
                ->where("JobLoggedUserID", $userID)
                ->update(array("HasRead"=>1));
        }

    }

    public static function removeUnnecesorryOptions( $type , $options = array()){
        if(!empty($options)){
            $unset_array = array("AccountID","CompanyID","full_path");
            foreach($unset_array as $unset_keys){
                if(isset($options[$unset_keys])){
                    unset($options[$unset_keys]);
                }
            }
        }
        return $options;

    }

    public static function getJobsDropDown($reset = 0){
        $companyID = User::get_companyID();
        $userID = User::get_userID();
        //$isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $isAdmin 					= 	(User::is_admin())?1:0;
        $query = "Call prc_getJobDropdown (".$companyID.",".$userID.",".$isAdmin .",".$reset.")" ;
        $dropdownData = DataTableSql::of($query)->getProcResult(array('jobs','totalNonVisitedJobs','totalPendingJobs'));
        return $dropdownData;

    }

    /** Not in use 
     * @param $JobType
     * @param string $options
     * @return array
     */
    public static function  GenerateInvoiceUsageFileJob($JobType, $options = ""){

        $CompanyID = $options["CompanyID"];
        $InvoiceID = $options["InvoiceID"];
        $Invoice = Invoice::find($InvoiceID);
        $AccountID = $Invoice->AccountID;
        if(isset($Invoice->AccountID)) {
            $AccountName = Account::getCompanyNameByID($AccountID);

            $jobType = JobType::where(["Code" => $JobType])->get(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
            $jobdata["CompanyID"] = $CompanyID;
            $jobdata["AccountID"] = $AccountID;
            $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
            $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] = (isset($jobType[0]->Title) ? $jobType[0]->Title : '') . ' For ' . $AccountName;
            $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '' .  ' For ' . $AccountName;
            $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($options);
            $jobdata["created_at"] = date('Y-m-d H:i:s');
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);

            if ($JobID > 0) {
                return array("status" => "success", "message" => "Job Logged Successfully");
            } else {
                return array("status" => "failed", "message" => "Problem Inserting Job.");
            }
        }

    }
	
	public static function CreateAutoImportJob($CompanyID,$job_type,$options){

        switch ($job_type) {

            case 'VU':

                $UserID = User::where("CompanyID", $CompanyID)->where(["AdminUser" => 1, "Status" => 1])->min("UserID");
                $jobType = JobType::where(["Code" => $job_type])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
                $jobdata = array();
                $jobdata["CompanyID"] = $CompanyID;
                $jobdata["AccountID"] = $options["AccountID"];
                $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $jobdata["JobLoggedUserID"] = $UserID;
                $AccountName = Account::where(["AccountID" => $options["AccountID"]])->pluck('AccountName');
                $jobdata["Title"] = $AccountName;
                $jobdata["Description"] = $AccountName . ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $jobdata["CreatedBy"] = "System";
                $jobdata["created_at"] = date('Y-m-d H:i:s');
                $jobdata["updated_at"] = date('Y-m-d H:i:s');
                $JobID = Job::insertGetId($jobdata);

                /* Job Insert Here */
                if ($JobID) {
                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];
                    $data["HttpPath"] = 0;
                    $data["Options"] =  json_encode(self::removeUnnecesorryOptions($jobType,$options) );
                    $data["CreatedBy"] = 'System';
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');
                    if ($JobFileID = JobFile::insertGetId($data)) {
                        // return $JobFileID;
                        return $JobID;
                    } else {
                        // error code
                    }
                }

            case 'RTU':

                $UserID = User::where("CompanyID", $CompanyID)->where(["AdminUser" => 1, "Status" => 1])->min("UserID");
                $jobType = JobType::where(["Code" => $job_type])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
                $jobdata = array();
                $jobdata["CompanyID"] = $CompanyID;
                $options["CompanyID"] = $CompanyID;
                $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $jobdata["JobLoggedUserID"] = $UserID;
                $jobdata["Title"] =  $options['ratetablename']; // New check this
                $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $jobdata["CreatedBy"] = "System";
                // $jobdata["Options"] = json_encode($options);
                $jobdata["created_at"] = date('Y-m-d H:i:s');
                $jobdata["updated_at"] = date('Y-m-d H:i:s');
                $JobID = Job::insertGetId($jobdata);

                /* Job Insert Here */
                if ($JobID) {
                    $data = array();
                    $data["JobID"] = $JobID;
                    $data["FileName"] = basename($options["full_path"]);
                    $data["FilePath"] = $options["full_path"];
                    $data["HttpPath"] = 0;
                    $data["Options"] =  json_encode(self::removeUnnecesorryOptions($jobType,$options) );
                    //$data["Options"] = '';
                    $data["CreatedBy"] = 'System';
                    $data["created_at"] = date('Y-m-d H:i:s');
                    $data["updated_at"] = date('Y-m-d H:i:s');
                    if ($JobFileID = JobFile::insertGetId($data)) {
                       // return $JobFileID;
                        return $JobID;
                    } else {
                        // error code
                    }
                }

        }


    }
}