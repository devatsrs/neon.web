CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCronJobHistory`(IN `p_CronJobID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   
       
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
    IF p_isExport = 0
    THEN
          
            SELECT
                tblCronJobCommand.Title,
                tblCronJobLog.CronJobStatus,
                tblCronJobLog.Message,
                tblCronJobLog.created_at
            FROM tblCronJob
            INNER JOIN tblCronJobLog 
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            WHERE tblCronJobLog.CronJobID = p_CronJobID
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblCronJobCommand.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblCronJobCommand.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CronJobStatusDESC') THEN tblCronJobLog.CronJobStatus
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CronJobStatusASC') THEN tblCronJobLog.CronJobStatus
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageDESC') THEN tblCronJobLog.Message
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageASC') THEN tblCronJobLog.Message
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblCronJobLog.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblCronJobLog.created_at
                END ASC
           LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(tblCronJobLog.CronJobID) as totalcount
        FROM tblCronJob
            INNER JOIN tblCronJobLog 
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            WHERE tblCronJobLog.CronJobID = p_CronJobID;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
            tblCronJobCommand.Title,
            tblCronJobLog.CronJobStatus,
            tblCronJobLog.Message,
            tblCronJobLog.created_at
        FROM tblCronJob
        INNER JOIN tblCronJobLog 
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
        INNER JOIN tblCronjobCommand
            ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
        WHERE tblCronJobLog.CronJobID = p_CronJobID;
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END