DROP PROCEDURE IF EXISTS `prc_getReportHistory`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getReportHistory`(
	IN `p_CompanyID` INT,
	IN `p_ReportScheduleID` INT,
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
			tblReportSchedule.Name,
			tblReportScheduleLog.send_at,
			AccountEmailLog.Subject,
			AccountEmailLog.Message,
			AccountEmailLog.AttachmentPaths,
			AccountEmailLog.AccountEmailLogID
		FROM tblReportSchedule
		INNER JOIN tblReportScheduleLog 
			ON tblReportSchedule.ReportScheduleID = tblReportScheduleLog.ReportScheduleID
		INNER JOIN Ratemanagement3.AccountEmailLog 
			ON tblReportScheduleLog.AccountEmailLogID = AccountEmailLog.AccountEmailLogID
		WHERE tblReportSchedule.CompanyID = p_CompanyID
			AND (p_ReportScheduleID = 0 OR tblReportSchedule.ReportScheduleID = p_ReportScheduleID)
			AND (p_ReportID = 0 OR tblReportSchedule.ReportID = p_ReportID)
			AND tblReportScheduleLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblReportSchedule.Name LIKE CONCAT('%',p_SearchText,'%'))
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageDESC') THEN tblReportSchedule.Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageASC') THEN tblReportSchedule.Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblReportScheduleLog.send_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblReportScheduleLog.send_at
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(tblReportScheduleLog.ReportScheduleLogID) as totalcount
		FROM tblReportSchedule
		INNER JOIN tblReportScheduleLog 
			ON tblReportSchedule.ReportScheduleID = tblReportScheduleLog.ReportScheduleID
		WHERE CompanyID = p_CompanyID
			AND (p_ReportScheduleID = 0 OR tblReportSchedule.ReportScheduleID = p_ReportScheduleID)
			AND (p_ReportID = 0 OR tblReportSchedule.ReportID = p_ReportID)
			AND tblReportScheduleLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblReportSchedule.Name LIKE CONCAT('%',p_SearchText,'%'));
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			tblReportSchedule.Name,
			tblReportScheduleLog.send_at
		FROM tblReportSchedule
		INNER JOIN tblReportScheduleLog 
			ON tblReportSchedule.ReportScheduleID = tblReportScheduleLog.ReportScheduleID
		WHERE CompanyID = p_CompanyID
			AND (p_ReportScheduleID = 0 OR tblReportSchedule.ReportScheduleID = p_ReportScheduleID)
			AND (p_ReportID = 0 OR tblReportSchedule.ReportID = p_ReportID)
			AND tblReportScheduleLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblReportSchedule.Name LIKE CONCAT('%',p_SearchText,'%'));
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;