CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_GetAccountSubscriptions`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_ServiceID` VARCHAR(50),
	IN `p_SubscriptionName` VARCHAR(50),
	IN `p_Status` INT,
	IN `p_Date` DATE,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT

)
BEGIN 
	DECLARE v_OffSet_ INT;
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	IF p_isExport = 0
	THEN 
		SELECT
			sa.SequenceNo,
			a.AccountName,
			s.ServiceName,
			sb.Name,
			sa.InvoiceDescription,
			sa.Qty,
			sa.StartDate,
			IF(sa.EndDate = '0000-00-00','',sa.EndDate) as EndDate,
			sa.ActivationFee,
			sa.DailyFee,
			sa.WeeklyFee,
			sa.MonthlyFee,
			sa.QuarterlyFee,
			sa.AnnuallyFee,
			sa.AccountSubscriptionID,
			sa.SubscriptionID,	
			sa.ExemptTax,
			a.AccountID,
			s.ServiceID,
			sa.`Status`
		FROM tblAccountSubscription sa
			INNER JOIN tblBillingSubscription sb
				ON sb.SubscriptionID = sa.SubscriptionID
			INNER JOIN NeonRMDev.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN NeonRMDev.tblService s
				ON sa.ServiceID = s.ServiceID
		WHERE 	(p_AccountID = 0 OR a.AccountID = p_AccountID)
			AND (p_SubscriptionName is null OR sb.Name LIKE concat('%',p_SubscriptionName,'%'))
			AND (p_Status = sa.`Status`)
			AND (p_ServiceID = 0 OR s.ServiceID = p_ServiceID)
		ORDER BY
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SequenceNoASC') THEN sa.SequenceNo
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SequenceNoDESC') THEN sa.SequenceNo
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN a.AccountName
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN a.AccountName
			END DESC,			
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameASC') THEN s.ServiceName
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameDESC') THEN s.ServiceName
			END DESC,			
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN sb.Name
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN sb.Name
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QtyASC') THEN sa.Qty
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QtyDESC') THEN sa.Qty
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StartDateASC') THEN sa.StartDate
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StartDateDESC') THEN sa.StartDate
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN sa.EndDate
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN sa.EndDate
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActivationFeeASC') THEN sa.ActivationFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActivationFeeDESC') THEN sa.ActivationFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DailyFeeASC') THEN sa.DailyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DailyFeeDESC') THEN sa.DailyFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'WeeklyFeeASC') THEN sa.WeeklyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'WeeklyFeeDESC') THEN sa.WeeklyFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyFeeASC') THEN sa.MonthlyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyFeeDESC') THEN sa.MonthlyFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuarterlyFeeASC') THEN sa.QuarterlyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuarterlyFeeDESC') THEN sa.QuarterlyFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AnnuallyFeeASC') THEN sa.AnnuallyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AnnuallyFeeDESC') THEN sa.AnnuallyFee
			END DESC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
		FROM tblAccountSubscription sa
			INNER JOIN tblBillingSubscription sb
				ON sb.SubscriptionID = sa.SubscriptionID
			INNER JOIN NeonRMDev.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN NeonRMDev.tblService s
				ON sa.ServiceID = s.ServiceID
		WHERE 	(p_AccountID = 0 OR a.AccountID = p_AccountID)
			AND (p_SubscriptionName is null OR sb.Name LIKE concat('%',p_SubscriptionName,'%'))
			AND (p_Status = sa.`Status`)
			AND (p_ServiceID = 0 OR s.ServiceID = p_ServiceID);
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			sa.SequenceNo,
			a.AccountName,
			s.ServiceName,
			sb.Name,
			sa.InvoiceDescription,
			sa.Qty,
			sa.StartDate,
			IF(sa.EndDate = '0000-00-00','',sa.EndDate) as EndDate,
			sa.ActivationFee,
			sa.DailyFee,
			sa.WeeklyFee,
			sa.MonthlyFee,
			sa.QuarterlyFee,
			sa.AnnuallyFee,
			sa.AccountSubscriptionID,
			sa.SubscriptionID,	
			sa.ExemptTax,
			sa.`Status`
		FROM tblAccountSubscription sa
			INNER JOIN tblBillingSubscription sb
				ON sb.SubscriptionID = sa.SubscriptionID
			INNER JOIN NeonRMDev.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN NeonRMDev.tblService s
				ON sa.ServiceID = s.ServiceID
		WHERE 	(p_AccountID = 0 OR a.AccountID = p_AccountID)
			AND (p_SubscriptionName is null OR sb.Name LIKE concat('%',p_SubscriptionName,'%'))
			AND (p_Status = sa.`Status`)
			AND (p_ServiceID =0 OR s.ServiceID = p_ServiceID);
	END IF;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END