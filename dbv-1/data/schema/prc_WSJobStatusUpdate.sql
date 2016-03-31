CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSJobStatusUpdate`(IN `p_JobId` int , IN `p_Status` varchar(5) , IN `p_JobStatusMessage` longtext , IN `p_OutputFilePath` longtext
)
BEGIN
  DECLARE v_JobStatusId_ int;
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED; 

  SELECT  JobStatusId INTO v_JobStatusId_ FROM tblJobStatus WHERE Code = p_Status;

  UPDATE tblJob
  SET JobStatusID = v_JobStatusId_
  , JobStatusMessage = p_JobStatusMessage
  , OutputFilePath = p_OutputFilePath
  , updated_at = NOW()
  , ModifiedBy = 'WindowsService'
  WHERE JobID = p_JobId;
  
  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END