CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetActiveCronJob`(IN `p_companyid` INT, IN `p_Title` VARCHAR(500), IN `p_Status` INT, IN `p_Active` INT, IN `p_Type` INT, IN `p_CurrentDateTime` DATETIME, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT
)
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


    IF p_isExport = 0
    THEN

		SELECT
			jb.Active,
			jb.PID,
			jb.JobTitle,
			CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,p_CurrentDateTime),' Hours, ',TIMESTAMPDIFF(minute,LastRunTime,p_CurrentDateTime)%60,' Minutes, ',TIMESTAMPDIFF(second,LastRunTime,p_CurrentDateTime)%60 , ' Seconds' ) AS RunningTime,
			-- CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,p_CurrentDateTime),' Hours, ',TIMESTAMPDIFF(minute,LastRunTime,p_CurrentDateTime)%60,' Minutes, ',TIMESTAMPDIFF(second,LastRunTime,p_CurrentDateTime)%60 , ' Seconds' ) AS RunningTime,
 			jb.LastRunTime,
			jb.NextRunTime,
			jb.CronJobID,
			jb.Status,
          jb.CronJobCommandID,
          jb.Settings	,
 		   (select CronJobStatus from tblCronJobLog where tblCronJobLog.CronJobID = jb.CronJobID order by  CronJobLogID desc limit 1)  as  CronJobStatus

		FROM tblCronJob jb
		INNER JOIN tblCronJobCommand cm ON cm.CronJobCommandID = jb.CronJobCommandID
      WHERE jb.CompanyID = p_companyid
		AND (p_Title = '' OR (p_Title != '' AND jb.JobTitle like concat('%', p_Title , '%') ) )
		AND  (p_Status = -1 OR (p_Status != -1 and jb.Status = p_Status ) )
		 AND (p_Active = -1 OR (p_Active != -1 and  jb.Active = p_Active ) )
		 AND (p_Type = 0 OR (p_Type != 0 and  jb.CronJobCommandID = p_Type ) )
      ORDER BY
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleDESC') THEN jb.JobTitle
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleASC') THEN jb.JobTitle
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PIDDESC') THEN jb.PID
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PIDASC') THEN jb.PID
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastRunTimeASC') THEN jb.LastRunTime
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastRunTimeDESC') THEN jb.LastRunTime
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NextRunTimeASC') THEN jb.NextRunTime
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NextRunTimeDESC') THEN jb.NextRunTime
			END DESC

		LIMIT p_RowspPage OFFSET v_OffSet_;


     SELECT
         COUNT(jb.CronJobID) as totalcount
     FROM tblCronJob jb
		INNER JOIN tblCronJobCommand cm ON cm.CronJobCommandID = jb.CronJobCommandID
      WHERE jb.CompanyID = p_companyid
		AND (p_Title = '' OR (p_Title != '' AND jb.JobTitle like concat('%', p_Title , '%') ) )
		AND  (p_Status = -1 OR (p_Status != -1 and jb.Status = p_Status ) )
		AND (p_Active = -1 OR (p_Active != -1 and  jb.Active = p_Active ) )
		AND (p_Type = 0 OR (p_Type != 0 and  jb.CronJobCommandID = p_Type ) );

    END IF;

    IF p_isExport = 1
    THEN
        SELECT
			jb.Active,
			jb.PID,
			jb.JobTitle,
			CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,p_CurrentDateTime),' Hours, ',TIMESTAMPDIFF(minute,LastRunTime,p_CurrentDateTime)%60,' Minutes, ',TIMESTAMPDIFF(second,LastRunTime,p_CurrentDateTime)%60 , ' Seconds' ) AS RunningTime,
 			jb.LastRunTime,
			jb.NextRunTime
        FROM tblCronJob jb
			INNER JOIN tblCronJobCommand cm ON cm.CronJobCommandID = jb.CronJobCommandID
	      WHERE jb.CompanyID = p_companyid
			AND (p_Title = '' OR (p_Title != '' AND jb.JobTitle like concat('%', p_Title , '%') ) )
			AND  (p_Status = -1 OR (p_Status != -1 and jb.Status = p_Status ) )
			AND (p_Active = -1 OR (p_Active != -1 and  jb.Active = p_Active ) )
			AND (p_Type = 0 OR (p_Type != 0 and  jb.CronJobCommandID = p_Type ) );
		 	
		 	
    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END