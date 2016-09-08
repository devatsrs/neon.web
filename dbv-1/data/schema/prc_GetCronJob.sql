CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCronJob`(
	IN `p_companyid` INT,
	IN `p_Status` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT

)
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
          
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

    IF p_isExport = 0
    THEN
          
            SELECT
				tblCronJob.JobTitle,
                tblCronJobCommand.Title,
                tblCronJob.Status,
                tblCronJob.CronJobID,
                tblCronJob.CronJobCommandID,
                tblCronJob.Settings
            FROM tblCronJob
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            WHERE tblCronJob.CompanyID = p_companyid
            AND (p_Status =2 OR tblCronJob.`Status`=p_Status)
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblCronJobCommand.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblCronJobCommand.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN tblCronJob.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN tblCronJob.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleDESC') THEN tblCronJob.JobTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'JobTitleASC') THEN tblCronJob.JobTitle
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(tblCronJob.CronJobID) as totalcount
        FROM tblCronJob
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            WHERE tblCronJob.CompanyID = p_companyid
            AND (p_Status =2 OR tblCronJob.`Status`=p_Status);
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
			tblCronJob.JobTitle,
            tblCronJobCommand.Title,
            tblCronJob.Status
        FROM tblCronJob
        INNER JOIN tblCronjobCommand
            ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
        WHERE tblCronJob.CompanyID = p_companyid
		  AND (p_Status =2 OR tblCronJob.`Status`=p_Status);
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END