CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_UpdateInProgressJobStatusToFail`(IN `p_JobID` INT, IN `p_JobStatusID` INT, IN `p_JobStatusMessage` LONGTEXT, IN `p_UserName` VARCHAR(250))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
 	
 	/*
	 This procedure used to restart and terminate job.
	 */
 	

 	IF (SELECT count(*) from tblJob WHERE JobID  = p_JobID 	AND JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'I' ) ) = 1
	 THEN

		 	UPDATE tblJob
		 	SET

				JobStatusID = p_JobStatusID,
				PID = NULL,
				ModifiedBy  = p_UserName,
				updated_at = now()

		 	WHERE
		 	JobID  = p_JobID
			AND JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'I' );

		 	IF p_JobStatusMessage != '' THEN

				UPDATE tblJob
				SET
					JobStatusMessage = concat(JobStatusMessage ,'\n' ,p_JobStatusMessage)
				WHERE
				JobID  = p_JobID;

		 	END IF;


   		SELECT '1' as result;
	
	ELSE
	
		SELECT '0' as result;	
		
	END IF;
	
 	
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END