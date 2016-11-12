CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAlertHistory`(IN `p_CompanyID` INT, IN `p_AlertID` INT, IN `p_AlertType` VARCHAR(50), IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_SearchText` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN

	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT
			tblAlert.Name,
			tblAlert.AlertType,
			tblAlertLog.send_at,
			AccountEmailLog.Subject,
			AccountEmailLog.Message
		FROM tblAlert
		INNER JOIN tblAlertLog 
			ON tblAlert.AlertID = tblAlertLog.AlertID
		INNER JOIN AccountEmailLog 
			ON tblAlertLog.AccountEmailLogID = AccountEmailLog.AccountEmailLogID
		WHERE tblAlert.CompanyID = p_CompanyID
			AND (p_AlertID = 0 OR tblAlert.AlertID = p_AlertID)
			AND (p_AlertType = '' OR tblAlert.AlertType = p_AlertType)
			AND tblAlertLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblAlert.Name LIKE CONCAT('%',p_SearchText,'%'))
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageDESC') THEN tblAlert.Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageASC') THEN tblAlert.Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AlertTypeDESC') THEN tblAlert.AlertType
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AlertTypeASC') THEN tblAlert.AlertType
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'send_atDESC') THEN tblAlertLog.send_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'send_atASC') THEN tblAlertLog.send_at
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(tblAlertLog.AlertLogID) as totalcount
		FROM tblAlert
		INNER JOIN tblAlertLog 
			ON tblAlert.AlertID = tblAlertLog.AlertID
		WHERE CompanyID = p_CompanyID
			AND (p_AlertID = 0 OR tblAlert.AlertID = p_AlertID)
			AND (p_AlertType = '' OR tblAlert.AlertType = p_AlertType)
			AND tblAlertLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblAlert.Name LIKE CONCAT('%',p_SearchText,'%'));
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			tblAlert.Name,
			tblAlert.AlertType,
			tblAlertLog.send_at
		FROM tblAlert
		INNER JOIN tblAlertLog 
			ON tblAlert.AlertID = tblAlertLog.AlertID
		WHERE CompanyID = p_CompanyID
			AND (p_AlertID = 0 OR tblAlert.AlertID = p_AlertID)
			AND (p_AlertType = '' OR tblAlert.AlertType = p_AlertType)
			AND tblAlertLog.send_at BETWEEN p_StartDate and p_EndDate
			AND (p_SearchText='' OR tblAlert.Name LIKE CONCAT('%',p_SearchText,'%'));
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END