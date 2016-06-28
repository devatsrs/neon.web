CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetActiveCronJob`(IN `p_companyid` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT )
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	
    IF p_isExport = 0
    THEN
         
		SELECT
			tblCronJob.Active,
			tblCronJob.PID,
			tblCronJob.JobTitle,                
			CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,NOW()),' Hours, ',TIMESTAMPDIFF(minute,LastRunTime,now())%60,' Minutes, ',TIMESTAMPDIFF(second,LastRunTime,now())%60 , ' Seconds' ) AS RunningTime,
			tblCronJob.CronJobID,
 			tblCronJob.LastRunTime,
			tblCronJob.Status,	                 	
							  
          tblCronJob.CronJobCommandID,
          tblCronJob.Settings	
			 						  						
		FROM tblCronJob
      WHERE tblCronJob.CompanyID = p_companyid -- AND tblCronJob.Active=1
       
      ORDER BY                
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleDESC') THEN tblCronJob.JobTitle
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleASC') THEN tblCronJob.JobTitle
			END ASC,				
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PIDDESC') THEN tblCronJob.PID
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PIDASC') THEN tblCronJob.PID
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastRunTimeASC') THEN tblCronJob.LastRunTime
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastRunTimeDESC') THEN tblCronJob.LastRunTime
			END DESC
		
		LIMIT p_RowspPage OFFSET v_OffSet_;


     SELECT
         COUNT(tblCronJob.CronJobID) as totalcount
     FROM tblCronJob
         WHERE tblCronJob.CompanyID = p_companyid ; -- AND tblCronJob.Active=1;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
			tblCronJob.JobTitle,                
			tblCronJob.PID,
			tblCronJob.CronJobID,
			tblCronJob.LastRunTime  
        FROM tblCronJob
        WHERE tblCronJob.CompanyID = p_companyid; -- AND tblCronJob.Active=1;
    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END