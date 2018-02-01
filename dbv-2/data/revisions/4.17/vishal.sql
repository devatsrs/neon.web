USE `RMBilling3`;

-- Dumping structure for procedure NeonBillingDev.prc_CustomerPanel_getInvoice
DROP PROCEDURE IF EXISTS `prc_CustomerPanel_getInvoice`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerPanel_getInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_InvoiceType` INT,
	IN `p_IsOverdue` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_zerovalueinvoice` INT


)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_TotalCount int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	SELECT cr.Symbol INTO v_CurrencyCode_ from NeonRMDev.tblCurrency cr INNER JOIN tblInvoice inv where  inv.CurrencyID   = cr.CurrencyId  and inv.AccountID=p_AccountID;

	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		InvoiceType tinyint(1),
		AccountName varchar(100),
		InvoiceNumber varchar(100),
		IssueDate datetime,
		InvoicePeriod varchar(100),
		CurrencySymbol varchar(5),
		GrandTotal decimal(18,6),
		TotalPayment decimal(18,6),
		PendingAmount decimal(18,6),
		InvoiceStatus varchar(50),
		InvoiceID int,
		Description varchar(500),
		Attachment varchar(255),
		AccountID int,
		ItemInvoice tinyint(1),
		BillingEmail varchar(255),
		AccountNumber varchar(100),
		PaymentDueInDays int,
		PaymentDate datetime,
		SubTotal decimal(18,6),
		TotalTax decimal(18,6),
		NominalAnalysisNominalAccountNumber varchar(100)
	);

		INSERT INTO tmp_Invoices_
		SELECT inv.InvoiceType ,
			ac.AccountName,
			FullInvoiceNumber as InvoiceNumber,
			inv.IssueDate,
			IF(invd.StartDate IS NULL ,'',CONCAT('From ',date(invd.StartDate) ,'<br> To ',date(invd.EndDate))) as InvoicePeriod,
			IFNULL(cr.Symbol,'') as CurrencySymbol,
			inv.GrandTotal as GrandTotal,
			(SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) as TotalPayment,
			(inv.GrandTotal -  (SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
			inv.InvoiceStatus,
			inv.InvoiceID,
			inv.Description,
			inv.Attachment,
			inv.AccountID,
			inv.ItemInvoice,
			IFNULL(ac.BillingEmail,'') as BillingEmail,
			ac.Number,
			(SELECT IFNULL(b.PaymentDueInDays,0) FROM NeonRMDev.tblAccountBilling ab INNER JOIN NeonRMDev.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID LIMIT 1) as PaymentDueInDays,
			(SELECT PaymentDate FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID ORDER BY PaymentID DESC LIMIT 1) AS PaymentDate,
			inv.SubTotal,
			inv.TotalTax,
			ac.NominalAnalysisNominalAccountNumber
			FROM tblInvoice inv
			INNER JOIN NeonRMDev.tblAccount ac on ac.AccountID = inv.AccountID
			LEFT JOIN tblInvoiceDetail invd on invd.InvoiceID = inv.InvoiceID AND invd.ProductType = 2
			LEFT JOIN NeonRMDev.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId
			WHERE ac.CompanyID = p_CompanyID
					AND (inv.AccountID = p_AccountID)
					AND ( (IFNULL(inv.InvoiceStatus,'') = '') OR  inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting'))
					AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
					AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
					AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
					AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
					AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));

	IF p_isExport = 0
	THEN
		SELECT
			InvoiceType ,
			AccountName,
			InvoiceNumber,
			IssueDate,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `PendingAmount`,
			InvoiceStatus,
			InvoiceID,
			Description,
			Attachment,
			AccountID,
			PendingAmount as OutstandingAmount,
			ItemInvoice,
			BillingEmail,
			GrandTotal
		FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0
							OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
									AND(PendingAmount>0)
								)
						)
		ORDER BY
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeDESC') THEN InvoiceType
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeASC') THEN InvoiceType
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusDESC') THEN InvoiceStatus
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusASC') THEN InvoiceStatus
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberASC') THEN InvoiceNumber
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberDESC') THEN InvoiceNumber
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN IssueDate
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN IssueDate
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN GrandTotal
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN GrandTotal
			END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDDESC') THEN InvoiceID
			END DESC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDASC') THEN InvoiceID
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;


		SELECT COUNT(*) into v_TotalCount FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0
							OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
									AND(PendingAmount>0)
								)
						);

		SELECT
			v_TotalCount AS totalcount,
			ROUND(sum(GrandTotal),v_Round_) as total_grand,
			ROUND(sum(TotalPayment),v_Round_) as `TotalPayment`,
			ROUND(sum(PendingAmount),v_Round_) as `TotalPendingAmount`,
			v_CurrencyCode_ as currency_symbol
		FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0
							OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
									AND(PendingAmount>0)
								)
						);
	END IF;
	IF p_isExport = 1
	THEN
		SELECT
			AccountName,
			InvoiceNumber,
			IssueDate,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
			InvoiceStatus,
			InvoiceType,
			ItemInvoice
		FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0
						OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
								AND(PendingAmount>0)
							)
					);
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;