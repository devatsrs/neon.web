CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAllJobs`(IN `p_companyid` INT, IN `p_Status` INT, IN `p_Type` INT, IN `p_AccountID` INT, IN `p_UserID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
    

    IF p_isExport = 0
    THEN
          
            SELECT
                tblJob.Title,
                tblJobType.Title as Type,
                tblJobStatus.Title as Status,
                tblJob.created_at,
                tblJob.CreatedBy as CreatedBy,
                tblJob.JobID,
                tblJob.ShowInCounter,
                tblJob.updated_at
            FROM tblJob
           JOIN tblJobStatus 
                ON tblJob.JobStatusID = tblJobStatus.JobStatusID
            JOIN tblJobType 
                ON tblJob.JobTypeID = tblJobType.JobTypeID
            WHERE tblJob.CompanyID = p_companyid
            AND ( p_UserID = 0  OR ( p_UserID > 0 AND  tblJob.JobLoggedUserID = p_UserID )  )
            AND ( p_Status = ''  OR ( p_Status > 0 AND  tblJob.JobStatusID = p_Status )  )
            AND ( p_Type = ''  OR ( p_Type > 0 AND  tblJob.JobTypeID = p_Type )  )
            AND ( p_AccountID = ''  OR ( p_AccountID > 0 AND  tblJob.AccountID = p_AccountID )  )
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblJob.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblJob.Title
                END ASC,
                 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeDESC') THEN tblJobType.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeASC') THEN tblJobType.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN tblJobStatus.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN tblJobStatus.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblJob.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblJob.created_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN tblJob.CreatedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN tblJob.CreatedBy
                END ASC

           LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(tblJob.JobID) as totalcount
        FROM tblJob
           JOIN tblJobStatus 
                ON tblJob.JobStatusID = tblJobStatus.JobStatusID
            JOIN tblJobType 
                ON tblJob.JobTypeID = tblJobType.JobTypeID
            WHERE tblJob.CompanyID = p_companyid
            AND ( p_UserID = 0  OR ( p_UserID > 0 AND  tblJob.JobLoggedUserID = p_UserID )  )
            AND ( p_Status = ''  OR ( p_Status > 0 AND  tblJob.JobStatusID = p_Status )  )
            AND ( p_Type = ''  OR ( p_Type > 0 AND  tblJob.JobTypeID = p_Type )  )
            AND ( p_AccountID = ''  OR ( p_AccountID > 0 AND  tblJob.AccountID = p_AccountID )  );


    END IF;

    IF p_isExport = 1
    THEN
        SELECT
            tblJob.Title,
                tblJobType.Title as Type,
                tblJobStatus.Title as Status,
                tblJob.created_at,
                tblJob.CreatedBy as CreatedBy
        FROM tblJob
           JOIN tblJobStatus 
                ON tblJob.JobStatusID = tblJobStatus.JobStatusID
            JOIN tblJobType 
                ON tblJob.JobTypeID = tblJobType.JobTypeID
            WHERE tblJob.CompanyID = p_companyid
            AND ( p_UserID = 0  OR ( p_UserID > 0 AND  tblJob.JobLoggedUserID = p_UserID )  )
            AND ( p_Status = ''  OR ( p_Status > 0 AND  tblJob.JobStatusID = p_Status )  )
            AND ( p_Type = ''  OR ( p_Type > 0 AND  tblJob.JobTypeID = p_Type )  )
            AND ( p_AccountID = ''  OR ( p_AccountID > 0 AND  tblJob.AccountID = p_AccountID )  );
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END