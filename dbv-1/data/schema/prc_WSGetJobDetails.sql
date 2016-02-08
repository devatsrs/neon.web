CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGetJobDetails`(IN `p_jobId` int)
BEGIN
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
  SELECT tblJob.CompanyID 
  , IFNULL(tblUser.EmailAddress,'')  as EmailAddress
  , tblJob.Title as JobTitle
  , IFNULL(tblJob.JobStatusMessage,'') as JobStatusMessage
  , tblJobStatus.Title as StatusTitle
  ,OutputFilePath
  ,AccountID 
  FROM tblJob   
    JOIN tblJobStatus 
      ON tblJob.JobStatusID = tblJobStatus.JobStatusID    
    JOIN tblUser
      ON tblUser.UserID = tblJob.JobLoggedUserID
  WHERE tblJob.JobID = p_jobId;   
  
  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END