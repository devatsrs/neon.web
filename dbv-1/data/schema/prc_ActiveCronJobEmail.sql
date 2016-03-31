CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ActiveCronJobEmail`(IN `p_CompanyID` INT)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT
		tblCronJobCommand.*,
		tblCronJob.*
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Active = 1
	AND tblCronJobCommand.Command != 'activecronjobemail';
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END