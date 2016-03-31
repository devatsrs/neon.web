CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetRecentDueSheet`(IN `p_companyId` INT , IN `p_isAdmin` INT , IN `p_AccountType` INT, IN `p_DueDate` VARCHAR(10), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT 
)
BEGIN
	
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


    DROP TEMPORARY TABLE IF EXISTS tmp_DueSheets_;
    CREATE TEMPORARY TABLE tmp_DueSheets_ (
        AccountID INT,
        AccountName VARCHAR(100),
        Trunk VARCHAR(50),
        EffectiveDate DATE
    );


    IF p_AccountType = 1
    THEN
	  INSERT INTO tmp_DueSheets_
            SELECT DISTINCT
                TBL.AccountID,
                TBL.AccountName,
                TBL.Trunk,
                TBL.EffectiveDate
            FROM (SELECT DISTINCT
                    a.AccountID,
                    a.AccountName,
                    t.Trunk,
					t.TrunkID,
                    vr.EffectiveDate,
					TIMESTAMPDIFF(DAY, vr.EffectiveDate, DATE_FORMAT(NOW(),'%Y-%m-%d')) AS DAYSDIFF
                FROM tblVendorRate vr
				inner join tblVendorTrunk vt on vt.AccountID = vr.AccountId and vt.TrunkID = vr.TrunkID and vt.Status =1
                INNER JOIN tblRate r
                    ON r.RateID = vr.RateID
                INNER JOIN tblCountry c
                    ON c.CountryID = r.CountryID
                INNER JOIN tblAccount a
                    ON a.AccountID = vr.AccountId
                INNER JOIN tblTrunk t
                    ON t.TrunkID = vr.TrunkID
                WHERE a.CompanyId = p_companyId

				AND (
						(p_DueDate = 'Today' AND TIMESTAMPDIFF(DAY, vr.EffectiveDate, NOW()) = 0)
							OR
						(p_DueDate = 'Tomorrow' AND TIMESTAMPDIFF(DAY, vr.EffectiveDate, NOW()) = -1)
							OR
						(p_DueDate = 'Yesterday' AND TIMESTAMPDIFF(DAY, vr.EffectiveDate, NOW()) = 1)
					)
				AND  ((p_isAdmin <> 0 AND a.Owner = p_isAdmin) OR  (p_isAdmin = 0))

				) AS TBL

				Left JOIN tblJob ON tblJob.AccountID = TBL.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='VD'))
					AND FIND_IN_SET (TBL.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND (
					(p_DueDate = 'Today' AND TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = 0)
					OR
					(p_DueDate = 'Yesterday' AND TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)
					)
					where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S');
    END IF;

    IF p_AccountType = 2
    THEN
	  INSERT INTO tmp_DueSheets_
            SELECT  DISTINCT
                TBL.AccountID,
                TBL.AccountName,
                TBL.Trunk,
                TBL.EffectiveDate
            FROM (SELECT DISTINCT
                a.AccountID,
                a.AccountName,
                t.Trunk,
				t.TrunkID,
                cr.EffectiveDate,
				TIMESTAMPDIFF(DAY, cr.EffectiveDate, DATE_FORMAT(NOW(),'%Y-%m-%d')) AS DAYSDIFF
            FROM tblCustomerRate cr
			inner join tblCustomerTrunk ct on ct.AccountID = cr.CustomerID and ct.TrunkID = cr.TrunkID and ct.Status =1
            INNER JOIN tblRate r
                ON r.RateID = cr.RateID
            INNER JOIN tblCountry c
                ON c.CountryID = r.CountryID
            INNER JOIN tblAccount a
                ON a.AccountID = cr.CustomerID
            INNER JOIN tblTrunk t
                ON t.TrunkID = cr.TrunkID
            WHERE a.CompanyId = p_companyId
				AND (
						(p_DueDate = 'Today' AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, NOW()) = 0)
							OR
						(p_DueDate = 'Tomorrow' AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, NOW()) = -1)
							OR
						(p_DueDate = 'Yesterday' AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, NOW()) = 1)
					)
					AND  ((p_isAdmin <> 0 AND a.Owner = p_isAdmin) OR  (p_isAdmin = 0))
			) AS TBL

			LEFT JOIN tblJob ON tblJob.AccountID = TBL.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='CD'))
					AND FIND_IN_SET (TBL.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND (
					(p_DueDate = 'Today' AND TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = 0)
					OR
					(p_DueDate = 'Yesterday' AND TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)
					)
						where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S');

    END IF;

    IF p_isExport = 0
    THEN
        SELECT
            AccountName,
            Trunk,
            EffectiveDate,
            AccountID
        FROM tmp_DueSheets_ AS DueSheets
        ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkDESC') THEN Trunk
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkASC') THEN Trunk
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;


		SELECT count(AccountName) as totalcount
      FROM tmp_DueSheets_ AS DueSheets;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
            AccountName,
            Trunk,
            EffectiveDate
        FROM tmp_DueSheets_ TBL;
    END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END