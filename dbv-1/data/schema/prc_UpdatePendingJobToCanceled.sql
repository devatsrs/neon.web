CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_UpdatePendingJobToCanceled`(IN `p_JobID` INT, IN `p_UserName` VARCHAR(500))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
 	
 	/*
	 This procedure used to Cancel a job.
	 */
 	
 	UPDATE tblJob 
 	SET 
		JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'C' ),
		ModifiedBy  = p_UserName,
		JobStatusMessage = concat(  p_UserName , ' has cancelled the job. ', '\n' , JobStatusMessage  ),
		updated_at = now()
 	WHERE 
 	JobID  = p_JobID
 	AND JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'P' );
	 
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END