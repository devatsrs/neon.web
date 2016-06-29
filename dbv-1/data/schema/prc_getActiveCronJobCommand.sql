CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getActiveCronJobCommand`(IN `p_CompanyID` INT, IN `p_CronJobID` INT)
BEGIN

	SELECT
		tblCronJobCommand.Command,
		tblCronJob.CronJobID
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CronJobID = p_CronJobID and tblCronJob.CompanyID = p_CompanyID 
	AND tblCronJob.Status = 1;
	
END