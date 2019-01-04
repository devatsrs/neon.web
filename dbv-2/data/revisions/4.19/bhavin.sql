Use `RMBilling3`;

DROP PROCEDURE IF EXISTS `prc_GetCDR`;
DELIMITER //
CREATE PROCEDURE `prc_GetCDR`(
	IN `p_company_id` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_start_date` DATETIME,
	IN `p_end_date` DATETIME,
	IN `p_AccountID` INT ,
	IN `p_ResellerID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(100),
	IN `p_extension` VARCHAR(50)
)
BEGIN

	DECLARE v_OffSet_ INT;
	DECLARE v_BillingTime_ INT;
	DECLARE v_Round_ INT;
	DECLARE v_raccountids TEXT;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	SET v_raccountids = '';

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_company_id) INTO v_Round_;

	SELECT cr.Symbol INTO v_CurrencyCode_ FROM Ratemanagement3.tblCurrency cr WHERE cr.CurrencyId =p_CurrencyID;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	Call fnUsageDetail(p_company_id,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CDRType,p_CLI,p_CLD,p_zerovaluecost);
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM Ratemanagement3.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM Ratemanagement3.tblReseller WHERE ResellerID=p_ResellerID;
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;

	IF p_isExport = 0
	THEN
		SELECT
			uh.UsageDetailID,
			uh.AccountName,
			uh.connect_time,
			uh.disconnect_time,
			uh.billed_duration,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(uh.cost)+0) AS cost,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(ROUND((uh.cost/uh.billed_duration)*60.0,6))+0) AS rate,
			uh.cli,
			uh.cld,
			uh.area_prefix,
			uh.trunk,
			s.ServiceName,
			uh.AccountID,
			p_CompanyGatewayID AS CompanyGatewayID,
			p_start_date AS StartDate,
			p_end_date AS EndDate,
			uh.is_inbound AS CDRType,
			uh.extension
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		LEFT JOIN Ratemanagement3.tblService s
			ON uh.ServiceID = s.ServiceID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND (p_extension = '' OR extension = p_extension)
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount,
			fnFormateDuration(sum(billed_duration)) AS total_duration,
			sum(cost) AS total_cost,
			v_CurrencyCode_ AS CurrencyCode
		FROM  tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND (p_extension = '' OR extension = p_extension);

	END IF;

	IF p_isExport = 1
	THEN

		SELECT
			uh.AccountName AS 'Account Name',
			uh.connect_time AS 'Connect Time',
			uh.disconnect_time AS 'Disconnect Time',
			uh.billed_duration AS 'Billed Duration (sec)' ,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(uh.cost)+0) AS 'Cost',
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(ROUND((uh.cost/uh.billed_duration)*60.0,6))+0) AS 'Avg. Rate/Min',
			uh.cli AS 'CLI',
			uh.cld AS 'CLD',
			uh.area_prefix AS 'Prefix',
			uh.trunk AS 'Trunk',
			IF(uh.is_inbound = 0 , 'OutBound','InBound') AS 'Type',
			uh.extension AS 'Extension'
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND (p_extension = '' OR extension = p_extension);
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_CreateInvoiceFromRecurringInvoice`;
DELIMITER //
CREATE PROCEDURE `prc_CreateInvoiceFromRecurringInvoice`(
	IN `p_CompanyID` INT,
	IN `p_InvoiceIDs` VARCHAR(200),
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_LogStatus` INT,
	IN `p_ProsessID` VARCHAR(50),
	IN `p_CurrentDate` DATETIME
)
    COMMENT 'test'
BEGIN
	DECLARE v_Note VARCHAR(100);
	DECLARE v_Check int;
	DECLARE v_SkippedWIthDate VARCHAR(200);
	DECLARE v_SkippedWIthOccurence VARCHAR(200);
	DECLARE v_Message VARCHAR(200);
	DECLARE v_InvoiceID int;
	DECLARE cnt_stock int;
	DECLARE v_StockErrorMessage VARCHAR(200);
	
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_Note = CONCAT('Recurring Invoice Generated by ',p_ModifiedBy,' ');
	SET v_StockErrorMessage = '';
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		CompanyID int,
		Title varchar(50),
		AccountID int,
		Address varchar(500),
		InvoiceNumber varchar(30),
		IssueDate datetime,
		CurrencyID int,
		PONumber varchar(30),
		InvoiceType int,
		SubTotal decimal(18,6),
		TotalDiscount decimal(18,6),
		TaxRateID int,
		TotalTax decimal(18,6),
		RecurringInvoiceTotal decimal(18,6),
		GrandTotal decimal(18,6),
		Description varchar(100),
		Attachment varchar(200),
		Note  longtext,
		Terms longtext,
		InvoiceStatus varchar(50),
		PDF varchar(500),
		UsagePath varchar(500),
		PreviousBalance decimal(18,6),
		TotalDue decimal(18,6),
		Payment decimal(18,6),
		CreatedBy varchar(50),
		ModifiedBy varchar(50),
		created_at datetime,
		updated_at datetime,
		ItemInvoice tinyint(3),
		FooterTerm longtext,
		RecurringInvoiceID int,
		ProsessID varchar(50),
		NextInvoiceDate datetime,
		Occurrence int,
		BillingClassID int
	);

	INSERT INTO tmp_Invoices_ 
 	SELECT rinv.CompanyID,
 				rinv.Title,
	 		 rinv.AccountID,
			 rinv.Address,
			 null as InvoiceNumber,
			 DATE(p_CurrentDate) as IssueDate,
			 rinv.CurrencyID,
			 '' as PONumber,
			 1 as InvoiceType,
			 rinv.SubTotal,
			 rinv.TotalDiscount,
			 rinv.TaxRateID,
			 rinv.TotalTax,
			 rinv.RecurringInvoiceTotal,
			 rinv.GrandTotal,
			 rinv.Description,
			 rinv.Attachment,
			 rinv.Note,
			 rinv.Terms,
			 'awaiting' as InvoiceStatus,
			 rinv.PDF,
			 '' as UsagePath,
			 0 as PreviousBalance,
			 0 as TotalDue,
			 0 as Payment,
			 rinv.CreatedBy,
			 '' as ModifiedBy,
			p_CurrentDate as created_at,
			p_CurrentDate as updated_at,
			1 as ItemInvoice,
			rinv.FooterTerm,
			rinv.RecurringInvoiceID,
			p_ProsessID,
			rinv.NextInvoiceDate,
			rinv.Occurrence,
			rinv.BillingClassID
		FROM tblRecurringInvoice rinv
		WHERE rinv.CompanyID = p_CompanyID
		AND rinv.RecurringInvoiceID=p_InvoiceIDs;

		
     SELECT GROUP_CONCAT(CONCAT(temp.Title,': Skipped with INVOICE DATE ',DATE(temp.NextInvoiceDate)) separator '\n\r') INTO v_SkippedWIthDate
	  FROM tmp_Invoices_ temp
	  WHERE (DATE(temp.NextInvoiceDate) > DATE(p_CurrentDate));

	  
	  SELECT GROUP_CONCAT(CONCAT(temp.Title,': Skipped with exceding limit Occurrence ',(SELECT COUNT(InvoiceID) FROM tblInvoice WHERE InvoiceStatus!='cancel' AND RecurringInvoiceID=temp.RecurringInvoiceID)) separator '\n\r') INTO v_SkippedWIthOccurence
	  FROM tmp_Invoices_ temp
	  	WHERE (temp.Occurrence > 0
		  	AND (SELECT COUNT(InvoiceID) FROM tblInvoice WHERE InvoiceStatus!='cancel' AND RecurringInvoiceID=temp.RecurringInvoiceID) >= temp.Occurrence);

     
     SELECT CASE
    								
	  				WHEN ((v_SkippedWIthDate IS NOT NULL) OR (v_SkippedWIthOccurence IS NOT NULL))
					THEN CONCAT(IFNULL(v_SkippedWIthDate,''),'\n\r',IFNULL(v_SkippedWIthOccurence,'')) 
							
				ELSE ''
				END as message INTO v_Message;
				

	IF(v_Message="") THEN
        

		INSERT INTO tblInvoice (`CompanyID`, `AccountID`, `Address`, `InvoiceNumber`, `IssueDate`, `CurrencyID`, `PONumber`, `InvoiceType`, `SubTotal`, `TotalDiscount`, `TaxRateID`, `TotalTax`, `InvoiceTotal`, `GrandTotal`, `Description`, `Attachment`, `Note`, `Terms`, `InvoiceStatus`, `PDF`, `UsagePath`, `PreviousBalance`, `TotalDue`, `Payment`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ItemInvoice`, `FooterTerm`,`RecurringInvoiceID`,`ProcessID`,`BillingClassID`)
	 	SELECT
		 rinv.CompanyID,
		 rinv.AccountID,
		 rinv.Address,
		 FNGetInvoiceNumber(p_CompanyID,rinv.AccountID,rinv.BillingClassID) as InvoiceNumber,
		 DATE(p_CurrentDate) as IssueDate,
		 rinv.CurrencyID,
		 '' as PONumber,
		 1 as InvoiceType,
		 rinv.SubTotal,
		 rinv.TotalDiscount,
		 rinv.TaxRateID,
		 rinv.TotalTax,
		 rinv.RecurringInvoiceTotal,
		 rinv.GrandTotal,
		 rinv.Description,
		 rinv.Attachment,
		 rinv.Note,
		 rinv.Terms,
		 'awaiting' as InvoiceStatus,
		 rinv.PDF,
		 '' as UsagePath,
		 0 as PreviousBalance,
		 0 as TotalDue,
		 0 as Payment,
		 rinv.CreatedBy,
		 '' as ModifiedBy,
		p_CurrentDate as created_at,
		p_CurrentDate as updated_at,
		1 as ItemInvoice,
		rinv.FooterTerm,
		rinv.RecurringInvoiceID,
		p_ProsessID,
		rinv.BillingClassID
		FROM tmp_Invoices_ rinv;

		SET v_InvoiceID = LAST_INSERT_ID();

		INSERT INTO tblInvoiceDetail ( `InvoiceID`, `ProductID`, `Description`, `StartDate`, `EndDate`, `Price`, `Qty`, `Discount`, `TaxRateID`,`TaxRateID2`, `TaxAmount`, `LineTotal`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ProductType`,`DiscountAmount`,`DiscountType`,`DiscountLineAmount`)
			SELECT
				inv.InvoiceID,
				rinvd.ProductID,
				rinvd.Description,
				null as StartDate,
				null as EndDate,
				rinvd.Price,
				rinvd.Qty,
				rinvd.Discount,
				rinvd.TaxRateID,
				rinvd.TaxRateID2,
				rinvd.TaxAmount,
				rinvd.LineTotal,
				rinvd.CreatedBy,
				rinvd.ModifiedBy,
				rinvd.created_at,
				p_CurrentDate as updated_at,
				rinvd.ProductType,
				rinvd.DiscountAmount,
				rinvd.DiscountType,
				rinvd.DiscountLineAmount
				FROM tblRecurringInvoiceDetail rinvd
				INNER JOIN tblInvoice inv ON  inv.RecurringInvoiceID = rinvd.RecurringInvoiceID
				INNER JOIN tblRecurringInvoice rinv ON  rinv.RecurringInvoiceID = rinvd.RecurringInvoiceID
				WHERE rinv.CompanyID = p_CompanyID
				AND inv.InvoiceID = v_InvoiceID;

		INSERT INTO tblInvoiceTaxRate ( `InvoiceID`,`InvoiceDetailID`, `TaxRateID`, `TaxAmount`,`InvoiceTaxType`,`Title`, `CreatedBy`,`ModifiedBy`)
		SELECT
			inv.InvoiceID,
			rinvt.RecurringInvoiceDetailID,
			rinvt.TaxRateID,
			rinvt.TaxAmount,
			rinvt.RecurringInvoiceTaxType,
			rinvt.Title,
			rinvt.CreatedBy,
			rinvt.ModifiedBy
		FROM tblRecurringInvoiceTaxRate rinvt
		INNER JOIN tblInvoice inv ON  inv.RecurringInvoiceID = rinvt.RecurringInvoiceID
		INNER JOIN tblRecurringInvoice rinv ON  rinv.RecurringInvoiceID = rinvt.RecurringInvoiceID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;

		INSERT INTO tblInvoiceLog (InvoiceID,Note,InvoiceLogStatus,created_at)
		SELECT inv.InvoiceID,CONCAT(v_Note, CONCAT(LTRIM(RTRIM(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.InvoiceNumber)))) as Note,1 as InvoiceLogStatus,p_CurrentDate as created_at
		FROM tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;

			
		INSERT INTO tblRecurringInvoiceLog (RecurringInvoiceID,Note,RecurringInvoiceLogStatus,created_at)
		SELECT inv.RecurringInvoiceID,CONCAT(v_Note, CONCAT(LTRIM(RTRIM(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.InvoiceNumber)))) as Note,p_LogStatus as InvoiceLogStatus,p_CurrentDate as created_at
		FROM tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;


		
		UPDATE tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		SET FullInvoiceNumber = IF(inv.InvoiceType=1,CONCAT(ltrim(rtrim(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber))),ltrim(rtrim(inv.InvoiceNumber)))
		WHERE inv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;
		
		
		DROP TEMPORARY TABLE IF EXISTS tmp_TaxRateDetail_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TaxRateDetail_(
			InvoiceTaxRateID int,
			InvoiceDetailID int
		);
	
		INSERT INTO tmp_TaxRateDetail_
		SELECT 
		tblInvoiceTaxRate.InvoiceTaxRateID,
		tblInvoiceDetail.InvoiceDetailID
		FROM tblInvoiceTaxRate
		JOIN tblRecurringInvoiceDetail on tblRecurringInvoiceDetail.RecurringInvoiceDetailID=tblInvoiceTaxRate.InvoiceDetailID 
		JOIN tblInvoiceDetail on tblInvoiceDetail.InvoiceID=v_InvoiceID AND tblInvoiceDetail.ProductID=tblRecurringInvoiceDetail.ProductID AND tblInvoiceDetail.Price=tblRecurringInvoiceDetail.Price
		WHERE tblInvoiceTaxRate.InvoiceID = v_InvoiceID AND tblInvoiceTaxRate.InvoiceDetailID !=0
		GROUP BY tblInvoiceTaxRate.InvoiceTaxRateID,tblInvoiceDetail.InvoiceDetailID
		;
		
		UPDATE tblInvoiceTaxRate a
		INNER JOIN tmp_TaxRateDetail_ b ON a.InvoiceTaxRateID=b.InvoiceTaxRateID
		SET a.InvoiceDetailID=b.InvoiceDetailID
		WHERE a.InvoiceTaxRateID=b.InvoiceTaxRateID;
		
		CALL prc_StockManageRecurringInvoice(p_CompanyID,p_InvoiceIDs,v_InvoiceID,p_ModifiedBy);
		
			
	END IF;

	SELECT v_Message as Message, IFNULL(v_InvoiceID,0) as InvoiceID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;