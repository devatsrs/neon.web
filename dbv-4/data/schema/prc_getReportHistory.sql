CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getReportHistory`(
	IN `p_CompanyID` INT,
	IN `p_ReportID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_SearchText` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT
			tblReport.Name,
			tblReportLog.send_at,
			AccountEmailLog.Subject,
			AccountEmailLog.Message
		FROM tblReport
		INNER JOIN tblReportLog 
			ON tblReport.ReportID = tblReportLog.ReportID
		INNER JOIN NeonRMDev.AccountEmailLog 
			ON tblReportLog.AccountEmailLogID = AccountEmailLog.AccountEmailLogID
		WHERE tblReport.CompanyID = p_CompanyID
			AND (p_ReportID = 0 OR tblReport.ReportID = p_ReportID)
			AND tblReportLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblReport.Name LIKE CONCAT('%',p_SearchText,'%'))
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageDESC') THEN tblReport.Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageASC') THEN tblReport.Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblReportLog.send_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblReportLog.send_at
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(tblReportLog.ReportLogID) as totalcount
		FROM tblReport
		INNER JOIN tblReportLog 
			ON tblReport.ReportID = tblReportLog.ReportID
		WHERE CompanyID = p_CompanyID
			AND (p_ReportID = 0 OR tblReport.ReportID = p_ReportID)
			AND tblReportLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblReport.Name LIKE CONCAT('%',p_SearchText,'%'));
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			tblReport.Name,
			tblReportLog.send_at
		FROM tblReport
		INNER JOIN tblReportLog 
			ON tblReport.ReportID = tblReportLog.ReportID
		WHERE CompanyID = p_CompanyID
			AND (p_ReportID = 0 OR tblReport.ReportID = p_ReportID)
			AND tblReportLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblReport.Name LIKE CONCAT('%',p_SearchText,'%'));
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END