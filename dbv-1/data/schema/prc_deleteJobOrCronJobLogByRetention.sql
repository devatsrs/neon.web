CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_deleteJobOrCronJobLogByRetention`(IN `p_CompanyID` INT, IN `p_DeleteDate` DATETIME, IN `p_ActionName` VARCHAR(200))
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	-- cron job log delete before delete date
	IF p_ActionName = 'Cronjob'
	THEN
		
		DELETE
			 tblCronJobLog
		FROM tblCronJobLog
		INNER JOIN tblCronJob
		 	 ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
		WHERE tblCronJob.CompanyID = p_CompanyID
			 AND tblCronJobLog.created_at < p_DeleteDate;
	
	END IF;	

	-- job log delete before delete date
	IF p_ActionName = 'Job'
	THEN
		-- job file delete		
		DELETE 
			tblJobFile
		FROM tblJobFile
		INNER JOIN  tblJob
			ON tblJob.JobID = tblJobFile.JobID
	  	INNER JOIN tblJobType
			 ON tblJob.JobTypeID = tblJobType.JobTypeID
		WHERE tblJobType.`Code` NOT IN('CD','VD','VU') 
			AND tblJob.CompanyID = p_CompanyID 
			AND tblJob.created_at < p_DeleteDate;

		-- job delete
			
		DELETE
			tblJob
		FROM tblJob
		INNER JOIN tblJobType
			ON tblJob.JobTypeID = tblJobType.JobTypeID
		WHERE tblJobType.`Code` NOT IN('CD','VD','VU')
			AND tblJob.CompanyID = p_CompanyID 
	  		AND tblJob.created_at < p_DeleteDate;
	
	END IF;	
	
	-- job log delete before delete date
	IF p_ActionName = 'CustomerRateSheet'
	THEN
		-- job delete of customer rate sheet download
			
		DELETE
			 tblJob
		FROM tblJob
		INNER JOIN tblJobType
			ON tblJob.JobTypeID = tblJobType.JobTypeID
		WHERE tblJobType.`Code` IN('CD')
			AND tblJob.CompanyID = p_CompanyID 
	  		AND tblJob.created_at < p_DeleteDate;
	
	END IF;	
	
	-- job log delete before delete date
	IF p_ActionName = 'VendorRateSheet'
	THEN
		-- job delete of vendor rate sheet download and upload
		-- job file delete		
		DELETE 
			tblJobFile
		FROM tblJobFile
		INNER JOIN  tblJob
			ON tblJob.JobID = tblJobFile.JobID
	  	INNER JOIN tblJobType
			 ON tblJob.JobTypeID = tblJobType.JobTypeID
		WHERE tblJobType.`Code` IN('VD','VU') 
			AND tblJob.CompanyID = p_CompanyID 
			AND tblJob.created_at < p_DeleteDate;	
			
		DELETE
			 tblJob
		FROM tblJob
		INNER JOIN tblJobType
			ON tblJob.JobTypeID = tblJobType.JobTypeID
		WHERE tblJobType.`Code` IN('VD','VU')
			AND tblJob.CompanyID = p_CompanyID 
	  		AND tblJob.created_at < p_DeleteDate;
	
	END IF;	
		
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END