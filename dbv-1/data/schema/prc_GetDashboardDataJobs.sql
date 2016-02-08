CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardDataJobs`(IN `p_companyId` int , IN `p_userId` int , IN `p_isadmin` int)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	/*  Get top 10 Jobs */
	SELECT `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` AS `Status`, `tblJob`.`HasRead`, `tblJob`.`CreatedBy`, `tblJob`.`created_at`
	FROM tblJob INNER JOIN tblJobStatus ON `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` INNER JOIN tblJobType ON `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID`
	WHERE `tblJob`.`CompanyID` = p_companyId AND ( p_isadmin = 1 OR (p_isadmin = 0 AND tblJob.JobLoggedUserID = p_userId) )
	ORDER BY `tblJob`.`ShowInCounter` DESC, `tblJob`.`updated_at` DESC,tblJob.JobID DESC
	LIMIT 10;


	/*  Get Total Pending Jobs Count */
	SELECT count(*) AS totalpending FROM tblJob INNER JOIN tblJobStatus ON `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID`
	WHERE `tblJobStatus`.`Title` = 'Pending' AND `tblJob`.`CompanyID` = p_companyId AND
	( p_isadmin = 1 OR ( p_isadmin = 0 AND tblJob.JobLoggedUserID = p_userId) );
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END