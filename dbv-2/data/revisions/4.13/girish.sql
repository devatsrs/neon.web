USE `RMBilling3`;

INSERT INTO tblInvoiceDetail (InvoiceID,ProductID,Description,StartDate,EndDate,Price,Qty,Discount,TaxAmount,LineTotal,CreatedBy,created_at,ProductType,ServiceID)
SELECT InvoiceID,0,'Invoice Period',StartDate,EndDate,0,0,0,0,0,CreatedBy,created_at,5,ServiceID FROM tblInvoiceDetail WHERE ProductType = 2;


DROP PROCEDURE IF EXISTS `prc_updateDefaultPrefix`;
DELIMITER |
CREATE PROCEDURE `prc_updateDefaultPrefix`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN
	DECLARE v_pointer_ INT;
	DECLARE v_partition_limit_ INT;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID(`TempUsageDetailID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID2(`TempUsageDetailID`)
	);

	/* find prefix from default codes and accounts doen't have active trunks */

	SET v_pointer_ = 0;
	SET v_partition_limit_ = 1000;
	SET @stm = CONCAT('
		SET @rowCount = (SELECT   COUNT(*)   FROM RMCDR3.' , p_tbltempusagedetail_name , '  ud LEFT JOIN tmp_Accounts_ a ON a.AccountID = ud.AccountID WHERE a.AccountID IS NULL AND area_prefix = "Other" AND ProcessID = "' , p_processId , '"  );
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	WHILE v_pointer_ <= @rowCount
	DO

		DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetailPart_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetailPart_(
			TempUsageDetailID int,
			cld varchar(500),
			INDEX IX_TempUsageDetailID(`TempUsageDetailID`),
			INDEX IX_cld(`cld`)
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetailPart_
		SELECT 
			TempUsageDetailID,
			cld
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
		LEFT JOIN tmp_Accounts_ a
			ON a.AccountID = ud.AccountID
		WHERE a.AccountID IS NULL 
			AND ProcessID = "' , p_processId , '"
			AND area_prefix = "Other"  
		LIMIT ',v_partition_limit_,' OFFSET ',v_pointer_,';
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM tmp_TempUsageDetailPart_ ud
		INNER JOIN Ratemanagement3.tmp_codes_ c 
			ON  cld like  CONCAT(c.Code,"%");
			
		SET v_pointer_ = v_pointer_ + v_partition_limit_;
		
	END WHILE;

	INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempUsageDetailID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempUsageDetailID;

	SET @stm = CONCAT('UPDATE RMCDR3.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END|
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_updateDefaultVendorPrefix`;
DELIMITER |
CREATE PROCEDURE `prc_updateDefaultVendorPrefix`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN
	DECLARE v_pointer_ INT;
	DECLARE v_partition_limit_ INT;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		TempVendorCDRID int,
		prefix varchar(50),
		INDEX IX_TempVendorCDRID(`TempVendorCDRID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
		TempVendorCDRID int,
		prefix varchar(50),
		INDEX IX_TempVendorCDRID2(`TempVendorCDRID`)
	);

	/* find prefix from default codes and accounts doen't have active trunks */	

	SET v_pointer_ = 0;
	SET v_partition_limit_ = 1000;
	SET @stm = CONCAT('
		SET @rowCount = (SELECT   COUNT(*)   FROM RMCDR3.' , p_tbltempusagedetail_name , '  ud LEFT JOIN tmp_Accounts_ a ON a.AccountID = ud.AccountID WHERE a.AccountID IS NULL AND area_prefix = "Other" AND ProcessID = "' , p_processId , '"  );
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	WHILE v_pointer_ <= @rowCount
	DO

		DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetailPart_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetailPart_(
			TempVendorCDRID int,
			cld varchar(500),
			INDEX IX_TempVendorCDRID(`TempVendorCDRID`),
			INDEX IX_cld(`cld`)
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetailPart_
		SELECT 
			TempVendorCDRID,
			cld
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
		LEFT JOIN tmp_Accounts_ a
			ON a.AccountID = ud.AccountID
		WHERE a.AccountID IS NULL 
			AND ProcessID = "' , p_processId , '"
			AND area_prefix = "Other"  
		LIMIT ',v_partition_limit_,' OFFSET ',v_pointer_,';
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempVendorCDRID,
			c.code AS prefix
		FROM tmp_TempUsageDetailPart_ ud
		INNER JOIN Ratemanagement3.tmp_codes_ c 
			ON  cld like  CONCAT(c.Code,"%");
			
		SET v_pointer_ = v_pointer_ + v_partition_limit_;

	END WHILE;

	INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempVendorCDRID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempVendorCDRID;

	SET @stm = CONCAT('UPDATE RMCDR3.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempVendorCDRID = tbl.TempVendorCDRID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END|
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_updatePrefix`;
DELIMITER |
CREATE PROCEDURE `prc_updatePrefix`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_ServiceID` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID(`TempUsageDetailID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID2(`TempUsageDetailID`)
	);

	IF p_TrunkID > 0
	THEN

		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
		INNER JOIN Ratemanagement3.tmp_codes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 0 
			AND ud.AccountID = ' , p_AccountID , '
			AND ud.TrunkID = ' , p_TrunkID , '
			AND ud.UseInBilling = 0
			AND ud.area_prefix = "Other"
			AND ( extension <> cld or extension IS NULL)
			AND cld REGEXP "^[0-9]+$"
			AND cld like  CONCAT(c.Code,"%");
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
		INNER JOIN Ratemanagement3.tmp_codes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 0
			AND ud.AccountID = ' , p_AccountID , '
			AND ud.TrunkID = ' , p_TrunkID , '
			AND ud.UseInBilling = 1 
			AND ud.area_prefix = "Other"
			AND ( extension <> cld or extension IS NULL)
			AND REPLACE(cld,ud.TrunkPrefix,"") REGEXP "^[0-9]+$"
			AND cld like  CONCAT(ud.TrunkPrefix,c.Code,"%");
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	ELSE

		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
		INNER JOIN Ratemanagement3.tmp_codes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 0 
			AND ud.AccountID = ' , p_AccountID , '
			AND ud.ServiceID = ' , p_ServiceID , '
			AND ud.area_prefix = "Other"
			AND ( extension <> cld or extension IS NULL)
			AND cld REGEXP "^[0-9]+$"
			AND cld like  CONCAT(c.Code,"%");
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempUsageDetailID,MAX(tbl.prefix)
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempUsageDetailID;

	SET @stm = CONCAT('UPDATE RMCDR3.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_importFromTempPaymentImportExport`;
DELIMITER |
CREATE PROCEDURE `prc_importFromTempPaymentImportExport`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_CurrentDate` DATETIME
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE
		tmpp
	FROM tblTempPaymentImportExport tmpp
	INNER JOIN tblPayment p
	ON p.CompanyID = tmpp.CompanyID
	AND p.TransactionID = tmpp.TransactionID
	WHERE tmpp.ProcessID= p_ProcessID ;

	INSERT INTO tblPayment (
			CompanyID,
			AccountID,
			CurrencyID,
			Amount,
			PaymentDate,
			PaymentType,
			TransactionID,
			`Status`,
			PaymentMethod,
			Notes,
			created_at,
			CreatedBy
		)
	SELECT
		a.CompanyID,
		a.AccountID,
		a.CurrencyId,
		tmpp.Amount,
		tmpp.PaymentDate,
		IF(tmpp.Amount >= 0 , 'Payment in', 'Payment out' ) AS PaymentType,
		tmpp.TransactionID,
		'Approved' AS `Status`,
		'Cash' AS PaymentMethod,
		Notes,
		p_CurrentDate AS created_at,
		'System Imported' AS CreatedBy
	FROM tblTempPaymentImportExport tmpp
	INNER JOIN Ratemanagement3.tblAccount a
	ON a.CompanyID = tmpp.CompanyID
	AND (tmpp.AccountNumber = a.Number OR tmpp.AccountNumber = a.AccountName) AND a.CurrencyId > 0
	WHERE tmpp.ProcessID= p_ProcessID ;

	SELECT AccountNumber AS `Account Number` ,Amount,PaymentDate AS `Payment Date` ,PaymentType AS `Payment Type`,TransactionID AS `Transaction ID`,`Action`
	FROM
	(

		SELECT
			tmpp.* ,
			'Imported' AS `Action`
		FROM tblTempPaymentImportExport tmpp
		INNER JOIN Ratemanagement3.tblAccount a
		ON a.CompanyID = tmpp.CompanyID
		AND (tmpp.AccountNumber = a.Number OR tmpp.AccountNumber = a.AccountName) AND a.CurrencyId IS NOT NULL
		WHERE tmpp.ProcessID= p_ProcessID

		UNION

		SELECT
			tmpp.* ,
			CONCAT('Skipped (',IF(a.AccountID,"Currency is not setup against Account" ,"Account doesn't exists in System" ),' )') as `Action`
		FROM tblTempPaymentImportExport tmpp
		LEFT JOIN Ratemanagement3.tblAccount a
		ON a.CompanyID = tmpp.CompanyID
		AND (tmpp.AccountNumber = a.Number OR tmpp.AccountNumber = a.AccountName)
		WHERE tmpp.ProcessID= p_ProcessID AND ( a.AccountID IS NULL OR a.CurrencyId IS NULL)

	) tmp;

	DELETE FROM tblTempPaymentImportExport WHERE ProcessID = p_ProcessID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getInvoice`;
DELIMITER |
CREATE PROCEDURE `prc_getInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_InvoiceType` INT,
	IN `p_InvoiceStatus` VARCHAR(50),
	IN `p_IsOverdue` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CurrencyID` INT,
	IN `p_isExport` INT,
	IN `p_sageExport` INT,
	IN `p_zerovalueinvoice` INT,
	IN `p_InvoiceID` LONGTEXT
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_TotalCount int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT cr.Symbol INTO v_CurrencyCode_ from Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

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
			(SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblAccountBilling ab INNER JOIN Ratemanagement3.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID LIMIT 1) as PaymentDueInDays,
			(SELECT PaymentDate FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID ORDER BY PaymentID DESC LIMIT 1) AS PaymentDate,
			inv.SubTotal,
			inv.TotalTax,
			ac.NominalAnalysisNominalAccountNumber
			FROM tblInvoice inv
			INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
			LEFT JOIN tblInvoiceDetail invd ON invd.InvoiceID = inv.InvoiceID AND (invd.ProductType = 5 OR inv.InvoiceType = 2)
			LEFT JOIN Ratemanagement3.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId
			WHERE ac.CompanyID = p_CompanyID
			AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
			AND (p_InvoiceNumber = '' OR (inv.FullInvoiceNumber like Concat('%',p_InvoiceNumber,'%')))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
			AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
			AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND FIND_IN_SET(inv.InvoiceStatus,p_InvoiceStatus) ))
			AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal != 0))
			AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ))
			AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));

	IF p_isExport = 0 and p_sageExport = 0
	THEN

		SELECT
			InvoiceType ,
			AccountName,
			InvoiceNumber,
			IssueDate,
			InvoicePeriod,
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
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
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
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoicePeriodASC') THEN InvoicePeriod
				END ASC,
			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoicePeriodDESC') THEN InvoicePeriod
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

		SELECT COUNT(*) INTO v_TotalCount
		FROM tmp_Invoices_
		WHERE (p_IsOverdue = 0
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
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
			WHERE ((InvoiceStatus IS NULL) OR (InvoiceStatus NOT IN('draft','Cancel')))
			AND (p_IsOverdue = 0
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);

	END IF;
	IF p_isExport = 1
	THEN

		SELECT
			AccountName ,
			InvoiceNumber,
			IssueDate,
			REPLACE(InvoicePeriod, '<br>', '') as InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
			InvoiceStatus,
			InvoiceType,
			ItemInvoice
		FROM tmp_Invoices_
		WHERE
				(p_IsOverdue = 0
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);
		END IF;
	IF p_isExport = 2
	THEN

		

		SELECT
			AccountName ,
			InvoiceNumber,
			IssueDate,
			REPLACE(InvoicePeriod, '<br>', '') as InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
			InvoiceStatus,
			InvoiceType,
			ItemInvoice,
			InvoiceID
		FROM tmp_Invoices_
		WHERE
				(p_IsOverdue = 0
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);

	END IF;

	IF p_sageExport =1 OR p_sageExport =2
	THEN
			

		IF p_sageExport = 2
		THEN
			UPDATE tblInvoice  inv
			INNER JOIN Ratemanagement3.tblAccount ac
				ON ac.AccountID = inv.AccountID
			INNER JOIN Ratemanagement3.tblAccountBilling ab
				ON ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID
			INNER JOIN Ratemanagement3.tblBillingClass b
				ON ab.BillingClassID = b.BillingClassID
			INNER JOIN Ratemanagement3.tblCurrency c
				ON c.CurrencyId = ac.CurrencyId
			SET InvoiceStatus = 'paid'
			WHERE ac.CompanyID = p_CompanyID
				AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
				AND (p_InvoiceNumber = '' OR (inv.FullInvoiceNumber like Concat('%',p_InvoiceNumber,'%')))
				AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
				AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
				AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
				AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND FIND_IN_SET(inv.InvoiceStatus,p_InvoiceStatus) ))
				AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal != 0))
				AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ))
				AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID))
				AND (p_IsOverdue = 0
					OR ((To_days(NOW()) - To_days(IssueDate)) > IFNULL(b.PaymentDueInDays,0)
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) )>0)
						)
				);
		END IF;
		SELECT
			AccountNumber,
			DATE_FORMAT(DATE_ADD(IssueDate,INTERVAL PaymentDueInDays DAY), '%Y-%m-%d') AS DueDate,
			GrandTotal AS GoodsValueInAccountCurrency,
			GrandTotal AS SalControlValueInBaseCurrency,
			1 AS DocumentToBaseCurrencyRate,
			1 AS DocumentToAccountCurrencyRate,
			DATE_FORMAT(IssueDate, '%Y-%m-%d') AS PostedDate,
			InvoiceNumber AS TransactionReference,
			'' AS SecondReference,
			'' AS Source,
			4 AS SYSTraderTranType, 
			DATE_FORMAT(PaymentDate ,'%Y-%m-%d') AS TransactionDate,
			TotalTax AS TaxValue,
			SubTotal AS `NominalAnalysisTransactionValue/1`,
			NominalAnalysisNominalAccountNumber AS `NominalAnalysisNominalAccountNumber/1`,
			'NEON' AS `NominalAnalysisNominalAnalysisNarrative/1`,
			'' AS `NominalAnalysisTransactionAnalysisCode/1`,
			1 AS `TaxAnalysisTaxRate/1`,
			SubTotal AS `TaxAnalysisGoodsValueBeforeDiscount/1`,
			TotalTax as   `TaxAnalysisTaxOnGoodsValue/1`
		FROM tmp_Invoices_
		WHERE
				(p_IsOverdue = 0
					OR ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting','draft','Cancel'))
							AND(PendingAmount>0)
						)
				);

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;
