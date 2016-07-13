CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_UpdateFailedJobToPending`(IN `p_JobID` INT)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
 	
 	/*
	 This procedure used to restart job.
	 */
 	
 	UPDATE tblJob 
 	SET 
 	
		JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'P' ),
		PID = NULL,
		updated_at = now()
 	WHERE 
 	JobID  = p_JobID;
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END