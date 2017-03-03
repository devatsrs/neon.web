USE `RMBilling3`;

ALTER TABLE `tblInvoice`
	ADD COLUMN `RecurringInvoiceID` INT(50) NULL DEFAULT NULL AFTER `EstimateID`,
	ADD COLUMN `ProcessID` VARCHAR(50) NULL DEFAULT NULL AFTER `RecurringInvoiceID`;

CREATE TABLE `tblRecurringInvoice` (
  `RecurringInvoiceID` int(11) NOT NULL auto_increment,
  `CompanyID` int(11) NULL,
  `Title` varchar(100) NULL,
  `AccountID` int(11) NULL,
  `Address` varchar(200) NULL,
  `BillingClassID` int(11) NULL,
  `BillingCycleType` varchar(50) NULL,
  `BillingCycleValue` varchar(50) NULL,
  `Occurrence` int(11) NULL DEFAULT '0',
  `PONumber` varchar(50) NULL,
  `Status` int(11) NULL,
  `LastInvoicedDate` datetime NULL,
  `NextInvoiceDate` datetime NULL,
  `CurrencyID` int(11) NULL,
  `InvoiceType` int(11) NULL,
  `SubTotal` decimal(18,6) NULL,
  `TotalDiscount` decimal(18,2) NULL,
  `TaxRateID` int(11) NULL,
  `TotalTax` decimal(10,6) NULL,
  `RecurringInvoiceTotal` decimal(10,6) NULL,
  `GrandTotal` decimal(10,6) NULL,
  `Description` varchar(50) NULL,
  `Attachment` varchar(50) NULL,
  `Note` longtext NULL,
  `Terms` longtext NULL,
  `ItemInvoice` tinyint(3) NULL,
  `FooterTerm` longtext NULL,
  `PDF` varchar(500) NULL,
  `UsagePath` varchar(500) NULL,
  `CreatedBy` varchar(50) NULL,
  `ModifiedBy` varchar(50) NULL,
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`RecurringInvoiceID`)
) ENGINE=InnoDB;

CREATE TABLE `tblRecurringInvoiceDetail` (
  `RecurringInvoiceDetailID` int(11) NOT NULL auto_increment,
  `RecurringInvoiceID` int(11) NOT NULL,
  `ProductID` int(11) NULL,
  `Description` varchar(250) NOT NULL,
  `Price` decimal(18,6) NOT NULL,
  `Qty` int(11) NULL,
  `Discount` decimal(18,2) NULL,
  `TaxRateID` int(11) NULL,
  `TaxRateID2` int(11) NULL,
  `TaxAmount` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `LineTotal` decimal(18,6) NOT NULL,
  `CreatedBy` varchar(50) NULL,
  `ModifiedBy` varchar(50) NULL,
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  `ProductType` int(11) NULL,
  PRIMARY KEY (`RecurringInvoiceDetailID`)
) ENGINE=InnoDB;

CREATE TABLE `tblRecurringInvoiceLog` (
  `RecurringInvoicesLogID` int(11) NOT NULL auto_increment,
  `RecurringInvoiceID` int(11) NULL,
  `Note` longtext NULL,
  `RecurringInvoiceLogStatus` int(11) NULL,
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`RecurringInvoicesLogID`)
) ENGINE=InnoDB;

CREATE TABLE `tblRecurringInvoiceTaxRate` (
  `RecurringInvoiceTaxRateID` int(11) NOT NULL auto_increment,
  `RecurringInvoiceID` int(11) NOT NULL,
  `TaxRateID` int(11) NOT NULL,
  `TaxAmount` decimal(18,6) NOT NULL,
  `Title` varchar(500) NOT NULL,
  `RecurringInvoiceTaxType` tinyint(4) NOT NULL DEFAULT 0,
  `CreatedBy` varchar(50) NULL,
  `ModifiedBy` varchar(50) NULL,
  `created_at` datetime NULL DEFAULT 'CURRENT_TIMESTAMP',
  `updated_at` datetime NULL DEFAULT 'CURRENT_TIMESTAMP' on update CURRENT_TIMESTAMP,
  PRIMARY KEY (`RecurringInvoiceTaxRateID`),
  UNIQUE KEY `RecurringInvoiceTaxRateUnique`(`RecurringInvoiceID`,`TaxRateID`,`RecurringInvoiceTaxType`)
) ENGINE=InnoDB;

DROP FUNCTION `FnGetInvoiceNumber`;

DELIMITER |
CREATE FUNCTION `FnGetInvoiceNumber`(
	`p_account_id` INT,
	`p_BillingClassID` INT



) RETURNS int(11)
    NO SQL
    DETERMINISTIC
    COMMENT 'Return Next Invoice Number'
BEGIN
DECLARE v_LastInv VARCHAR(50);
DECLARE v_FoundVal INT(11);
DECLARE v_InvoiceTemplateID INT(11);

SET v_InvoiceTemplateID = CASE WHEN p_BillingClassID=0 THEN (SELECT b.InvoiceTemplateID FROM Ratemanagement3.tblAccountBilling ab INNER JOIN Ratemanagement3.tblBillingClass b ON b.BillingClassID = ab.BillingClassID WHERE AccountID = p_account_id) ELSE (SELECT b.InvoiceTemplateID FROM  Ratemanagement3.tblBillingClass b WHERE b.BillingClassID = p_BillingClassID) END;

SELECT LastInvoiceNumber INTO v_LastInv FROM tblInvoiceTemplate WHERE InvoiceTemplateID =v_InvoiceTemplateID;

set v_FoundVal = (select count(*) as total_res from tblInvoice where FnGetIntegerString(InvoiceNumber)=v_LastInv);
IF v_FoundVal>=1 then
WHILE v_FoundVal>0 DO
	set v_LastInv = v_LastInv+1;
	set v_FoundVal = (select count(*) as total_res from tblInvoice where FnGetIntegerString(InvoiceNumber)=v_LastInv);
END WHILE;
END IF;

return v_LastInv;
END|
DELIMITER ;

DROP PROCEDURE `prc_Convert_Invoices_to_Estimates`;

DELIMITER |
CREATE PROCEDURE `prc_Convert_Invoices_to_Estimates`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` VARCHAR(50),
	IN `p_EstimateNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_EstimateStatus` VARCHAR(50),
	IN `p_EstimateID` VARCHAR(50),
	IN `p_convert_all` INT


)
    COMMENT 'test'
BEGIN
	DECLARE estimate_ids int;
	DECLARE note_text varchar(50);
 	SET sql_mode = 'ALLOW_INVALID_DATES';
	set note_text = 'Created From Estimate: ';

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
update tblInvoice  set EstimateID = '';
INSERT INTO tblInvoice (`CompanyID`, `AccountID`, `Address`, `InvoiceNumber`, `IssueDate`, `CurrencyID`, `PONumber`, `InvoiceType`, `SubTotal`, `TotalDiscount`, `TaxRateID`, `TotalTax`, `InvoiceTotal`, `GrandTotal`, `Description`, `Attachment`, `Note`, `Terms`, `InvoiceStatus`, `PDF`, `UsagePath`, `PreviousBalance`, `TotalDue`, `Payment`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ItemInvoice`, `FooterTerm`,EstimateID)
 	select te.CompanyID,
	 		 te.AccountID,
			 te.Address,
			 FNGetInvoiceNumber(te.AccountID,0) as InvoiceNumber,
			 DATE(NOW()) as IssueDate,
			 te.CurrencyID,
			 te.PONumber,
			 1 as InvoiceType,
			 te.SubTotal,
			 te.TotalDiscount,
			 te.TaxRateID,
			 te.TotalTax,
			 te.EstimateTotal,
			 te.GrandTotal,
			 te.Description,
			 te.Attachment,
			 te.Note,
			 te.Terms,
			 'awaiting' as InvoiceStatus,
			 te.PDF,
			 '' as UsagePath,
			 0 as PreviousBalance,
			 0 as TotalDue,
			 0 as Payment,
			 te.CreatedBy,
			 '' as ModifiedBy,
			NOW() as created_at,
			NOW() as updated_at,
			1 as ItemInvoice,
			te.FooterTerm,
			te.EstimateID
			from tblEstimate te
			where
			(p_convert_all=0 and te.EstimateID = p_EstimateID)
			OR
			(p_EstimateID = '' and p_convert_all =1 and (te.CompanyID = p_CompanyID)
			AND (p_AccountID = '' OR ( p_AccountID != '' AND te.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND te.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND te.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND te.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND te.EstimateStatus = p_EstimateStatus)) );


 select 	InvoiceID from tblInvoice inv
INNER JOIN tblEstimate ti ON  inv.EstimateID =  ti.EstimateID
where (p_convert_all=0 and ti.EstimateID = p_EstimateID)
		OR
		(p_EstimateID = '' and p_convert_all =1 and (ti.CompanyID = p_CompanyID)
			AND (p_AccountID = '' OR ( p_AccountID != '' AND ti.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND ti.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND ti.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND ti.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND ti.EstimateStatus = p_EstimateStatus)) );

		INSERT INTO tblInvoiceDetail ( `InvoiceID`, `ProductID`, `Description`, `StartDate`, `EndDate`, `Price`, `Qty`, `Discount`, `TaxRateID`,`TaxRateID2`, `TaxAmount`, `LineTotal`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ProductType`)
			select
				inv.InvoiceID,
				ted.ProductID,
				ted.Description,
				'' as StartDate,
				'' as EndDate,
				ted.Price,
				ted.Qty,
				ted.Discount,
				ted.TaxRateID,
				ted.TaxRateID2,
				ted.TaxAmount,
				ted.LineTotal,
				ted.CreatedBy,
				ted.ModifiedBy,
				ted.created_at,
				NOW() as updated_at,
				ted.ProductType
from tblEstimateDetail ted
INNER JOIN tblInvoice inv ON  inv.EstimateID = ted.EstimateID
INNER JOIN tblEstimate ti ON  ti.EstimateID = ted.EstimateID
where
		 (p_convert_all=0 and ti.EstimateID = p_EstimateID)
		OR	(p_EstimateID = '' and p_convert_all =1 and (ti.CompanyID = p_CompanyID)
			AND (p_AccountID = '' OR ( p_AccountID != '' AND ti.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND ti.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND ti.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND ti.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND ti.EstimateStatus = p_EstimateStatus)));

	INSERT INTO tblInvoiceTaxRate ( `InvoiceID`, `TaxRateID`, `TaxAmount`,`InvoiceTaxType`,`Title`, `CreatedBy`,`ModifiedBy`)
	SELECT
		inv.InvoiceID,
		ted.TaxRateID,
		ted.TaxAmount,
		ted.EstimateTaxType,
		ted.Title,
		ted.CreatedBy,
		ted.ModifiedBy
	FROM tblEstimateTaxRate ted
	INNER JOIN tblInvoice inv ON  inv.EstimateID = ted.EstimateID
	INNER JOIN tblEstimate ti ON  ti.EstimateID = ted.EstimateID
	WHERE	(p_convert_all=0 and ti.EstimateID = p_EstimateID)
		OR	(
			p_EstimateID = '' and p_convert_all =1 and (ti.CompanyID = p_CompanyID)
			AND (p_AccountID = '' OR ( p_AccountID != '' AND ti.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND ti.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND ti.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND ti.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND ti.EstimateStatus = p_EstimateStatus))
		);

insert into tblInvoiceLog (InvoiceID,Note,InvoiceLogStatus,created_at)
select inv.InvoiceID,concat(note_text, CONCAT(LTRIM(RTRIM(IFNULL(it.EstimateNumberPrefix,''))), LTRIM(RTRIM(ti.EstimateNumber)))) as Note,1 as InvoiceLogStatus,NOW() as created_at  from tblInvoice inv
INNER JOIN tblEstimate ti ON  inv.EstimateID =  ti.EstimateID
INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
INNER JOIN Ratemanagement3.tblAccountBilling ab ON ab.AccountID = ac.AccountID
INNER JOIN Ratemanagement3.tblBillingClass b ON ab.BillingClassID = b.BillingClassID
LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
where
			(p_convert_all=0 and ti.EstimateID = p_EstimateID)
		OR	(p_EstimateID = '' and p_convert_all =1 and (ti.CompanyID = p_CompanyID)
			AND (p_AccountID = '' OR ( p_AccountID != '' AND ti.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND ti.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND ti.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND ti.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND ti.EstimateStatus = p_EstimateStatus)));


update tblEstimate te set te.EstimateStatus='accepted'
where
			(p_convert_all=0 and te.EstimateID = p_EstimateID)
			OR
			(p_EstimateID = '' and p_convert_all =1 and (te.CompanyID = p_CompanyID)
			AND (p_AccountID = '' OR ( p_AccountID != '' AND te.AccountID = p_AccountID))
			AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND te.EstimateNumber = p_EstimateNumber))
			AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND te.IssueDate >= p_IssueDateStart))
			AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND te.IssueDate <= p_IssueDateEnd))
			AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND te.EstimateStatus = p_EstimateStatus)));

	UPDATE tblInvoice
	INNER JOIN tblEstimate ON  tblInvoice.EstimateID =  tblEstimate.EstimateID
	INNER JOIN Ratemanagement3.tblAccount ON tblAccount.AccountID = tblInvoice.AccountID
	INNER JOIN Ratemanagement3.tblAccountBilling ON tblAccount.AccountID = tblAccountBilling.AccountID
	INNER JOIN Ratemanagement3.tblBillingClass ON tblAccountBilling.BillingClassID = tblBillingClass.BillingClassID
	INNER JOIN tblInvoiceTemplate ON tblBillingClass.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
	SET FullInvoiceNumber = IF(InvoiceType=1,CONCAT(ltrim(rtrim(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), ltrim(rtrim(tblInvoice.InvoiceNumber))),ltrim(rtrim(tblInvoice.InvoiceNumber)))
	WHERE FullInvoiceNumber IS NULL AND tblInvoice.CompanyID = p_CompanyID AND tblInvoice.InvoiceType = 1;

				SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DELIMITER |
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

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_Note = CONCAT('Recurring Invoice Generated by ',p_ModifiedBy,' ');

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

	INSERT INTO tmp_Invoices_ /*insert invoices in temp table on the bases of filter*/
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

		/*Fill error message for recurring invoices with date check*/
     SELECT GROUP_CONCAT(CONCAT(temp.Title,': Skipped with INVOICE DATE ',DATE(temp.NextInvoiceDate)) separator '\n\r') INTO v_SkippedWIthDate
	  FROM tmp_Invoices_ temp
	  WHERE (DATE(temp.NextInvoiceDate) > DATE(p_CurrentDate));

	  /*Fill error message for recurring invoices with occurrence check*/
	  SELECT GROUP_CONCAT(CONCAT(temp.Title,': Skipped with exceding limit Occurrence ',(SELECT COUNT(InvoiceID) FROM tblInvoice WHERE InvoiceStatus!='cancel' AND RecurringInvoiceID=temp.RecurringInvoiceID)) separator '\n\r') INTO v_SkippedWIthOccurence
	  FROM tmp_Invoices_ temp
	  	WHERE (temp.Occurrence > 0
		  	AND (SELECT COUNT(InvoiceID) FROM tblInvoice WHERE InvoiceStatus!='cancel' AND RecurringInvoiceID=temp.RecurringInvoiceID) >= temp.Occurrence);

     /*return message either fill or empty*/
     SELECT CASE
	  				WHEN ((v_SkippedWIthDate IS NOT NULL) OR (v_SkippedWIthOccurence IS NOT NULL))
					THEN CONCAT(IFNULL(v_SkippedWIthDate,''),'\n\r',IFNULL(v_SkippedWIthOccurence,'')) ELSE ''
				END as message INTO v_Message;

	IF(v_Message="") THEN
        /*insert new invoices and its related detail, texes and updating logs.*/

		INSERT INTO tblInvoice (`CompanyID`, `AccountID`, `Address`, `InvoiceNumber`, `IssueDate`, `CurrencyID`, `PONumber`, `InvoiceType`, `SubTotal`, `TotalDiscount`, `TaxRateID`, `TotalTax`, `InvoiceTotal`, `GrandTotal`, `Description`, `Attachment`, `Note`, `Terms`, `InvoiceStatus`, `PDF`, `UsagePath`, `PreviousBalance`, `TotalDue`, `Payment`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ItemInvoice`, `FooterTerm`,RecurringInvoiceID,ProcessID)
	 	SELECT
		 rinv.CompanyID,
		 rinv.AccountID,
		 rinv.Address,
		 FNGetInvoiceNumber(rinv.AccountID,rinv.BillingClassID) as InvoiceNumber,
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
		p_ProsessID
		FROM tmp_Invoices_ rinv;

		SET v_InvoiceID = LAST_INSERT_ID();

		INSERT INTO tblInvoiceDetail ( `InvoiceID`, `ProductID`, `Description`, `StartDate`, `EndDate`, `Price`, `Qty`, `Discount`, `TaxRateID`,`TaxRateID2`, `TaxAmount`, `LineTotal`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `ProductType`)
			select
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
				rinvd.ProductType
				FROM tblRecurringInvoiceDetail rinvd
				INNER JOIN tblInvoice inv ON  inv.RecurringInvoiceID = rinvd.RecurringInvoiceID
				INNER JOIN tblRecurringInvoice rinv ON  rinv.RecurringInvoiceID = rinvd.RecurringInvoiceID
				WHERE rinv.CompanyID = p_CompanyID
				AND inv.InvoiceID = v_InvoiceID;

		INSERT INTO tblInvoiceTaxRate ( `InvoiceID`, `TaxRateID`, `TaxAmount`,`InvoiceTaxType`,`Title`, `CreatedBy`,`ModifiedBy`)
		SELECT
			inv.InvoiceID,
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

			/*add log for recurring invoice*/
		INSERT INTO tblRecurringInvoiceLog (RecurringInvoiceID,Note,RecurringInvoiceLogStatus,created_at)
		SELECT inv.RecurringInvoiceID,CONCAT(v_Note, CONCAT(LTRIM(RTRIM(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.InvoiceNumber)))) as Note,p_LogStatus as InvoiceLogStatus,p_CurrentDate as created_at
		FROM tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		WHERE rinv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;


		/*update full invoice number related to curring processID*/
		UPDATE tblInvoice inv
		INNER JOIN tblRecurringInvoice rinv ON  inv.RecurringInvoiceID =  rinv.RecurringInvoiceID
		INNER JOIN Ratemanagement3.tblBillingClass b ON rinv.BillingClassID = b.BillingClassID
		INNER JOIN tblInvoiceTemplate ON b.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
		SET FullInvoiceNumber = IF(inv.InvoiceType=1,CONCAT(ltrim(rtrim(IFNULL(tblInvoiceTemplate.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber))),ltrim(rtrim(inv.InvoiceNumber)))
		WHERE inv.CompanyID = p_CompanyID
		AND inv.InvoiceID = v_InvoiceID;

	END IF;

	SELECT v_Message as Message, IFNULL(v_InvoiceID,0) as InvoiceID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DELIMITER |
CREATE PROCEDURE `prc_DeleteRecurringInvoices`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_RecurringInvoiceStatus` INT,
	IN `p_InvoiceIDs` VARCHAR(200)
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DELETE invd FROM tblRecurringInvoiceDetail invd
	INNER JOIN tblRecurringInvoice inv ON invd.RecurringInvoiceID = inv.RecurringInvoiceID
	WHERE inv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs=''
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID)
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs))
		 );

	DELETE invlg FROM tblRecurringInvoiceLog invlg
	INNER JOIN tblRecurringInvoice inv ON invlg.RecurringInvoiceID = inv.RecurringInvoiceID
	WHERE inv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs=''
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID)
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs))
		 );

	DELETE invtr FROM tblRecurringInvoiceTaxRate invtr
	INNER JOIN tblRecurringInvoice inv ON invtr.RecurringInvoiceID = inv.RecurringInvoiceID
	WHERE inv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs=''
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID)
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs))
		 );

	UPDATE tblInvoice inv
	INNER JOIN tblRecurringInvoice rinv ON inv.RecurringInvoiceID = rinv.RecurringInvoiceID
	AND rinv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs=''
			AND (p_AccountID = 0 OR rinv.AccountID=p_AccountID)
			AND (p_RecurringInvoiceStatus=2 OR rinv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(rinv.RecurringInvoiceID ,p_InvoiceIDs))
		 )
	SET inv.RecurringInvoiceID=0;

	DELETE inv FROM tblRecurringInvoice inv
	WHERE inv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs=''
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID)
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs))
		 );

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DELIMITER |
CREATE PROCEDURE `prc_GetRecurringInvoiceLog`(
	IN `p_CompanyID` INT,
	IN `p_RecurringInvoiceID` INT,
	IN `p_Status` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_isExport` INT

)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
   IF p_isExport = 0
    THEN
      SELECT
          rinvlg.Note,
          rinvlg.RecurringInvoiceLogStatus,
          rinvlg.created_at,
          inv.RecurringInvoiceID

      FROM tblRecurringInvoice inv
      INNER JOIN Ratemanagement3.tblAccount ac
          ON ac.AccountID = inv.AccountID
      INNER JOIN tblRecurringInvoiceLog rinvlg
          ON rinvlg.RecurringInvoiceID = inv.RecurringInvoiceID
      WHERE ac.CompanyID = p_CompanyID
      AND (inv.RecurringInvoiceID = p_RecurringInvoiceID)
      AND (p_Status=0 OR rinvlg.RecurringInvoiceLogStatus=p_Status)
       ORDER BY
          CASE
              WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecurringInvoiceLogStatusDESC') THEN rinvlg.RecurringInvoiceLogStatus
          END DESC,
          CASE
              WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecurringInvoiceLogStatusASC') THEN rinvlg.RecurringInvoiceLogStatus
          END ASC,
          CASE
              WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN rinvlg.created_at
          END DESC,
          CASE
              WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN rinvlg.created_at
          END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

     SELECT
         COUNT(*) AS totalcount
     FROM tblRecurringInvoice inv
      INNER JOIN Ratemanagement3.tblAccount ac
          ON ac.AccountID = inv.AccountID
      INNER JOIN tblRecurringInvoiceLog rinvlg
          ON rinvlg.RecurringInvoiceID = inv.RecurringInvoiceID
      WHERE ac.CompanyID = p_CompanyID
      AND (inv.RecurringInvoiceID = p_RecurringInvoiceID)
      AND (p_Status=0 OR rinvlg.RecurringInvoiceLogStatus=p_Status);
    END IF;
    IF p_isExport = 1
    THEN
     SELECT
         rinvlg.Note,
         rinvlg.created_at,
         rinvlg.InvoiceLogStatus,
         inv.InvoiceNumber
     FROM tblRecurringInvoice inv
      INNER JOIN Ratemanagement3.tblAccount ac
          ON ac.AccountID = inv.AccountID
      INNER JOIN tblRecurringInvoiceLog rinvlg
          ON rinvlg.RecurringInvoiceID = inv.RecurringInvoiceID
      WHERE ac.CompanyID = p_CompanyID
      AND (inv.RecurringInvoiceID = p_RecurringInvoiceID)
      AND (p_Status=0 OR rinvlg.RecurringInvoiceLogStatus=p_Status);
    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

DELIMITER |
CREATE PROCEDURE `prc_getRecurringInvoices`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_Status` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ INT;
	DECLARE v_Round_ INT;
	SET sql_mode = 'ALLOW_INVALID_DATES';
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	IF p_isExport = 0
	THEN
	  SELECT
	  rinv.RecurringInvoiceID,
	  rinv.Title,
	  ac.AccountName,
	  DATE(rinv.LastInvoicedDate),
	  DATE(rinv.NextInvoiceDate),
	  CONCAT(IFNULL(cr.Symbol,''),ROUND(rinv.GrandTotal,v_Round_)) AS GrandTotal2,
	  rinv.`Status`,
	  rinv.Occurrence,
	  (SELECT COUNT(InvoiceID) FROM tblInvoice WHERE (InvoiceStatus!='awaiting' AND InvoiceStatus!='cancel' ) AND RecurringInvoiceID = rinv.RecurringInvoiceID) as Sent,
	  rinv.BillingCycleType,
	  rinv.BillingCycleValue,
	  rinv.AccountID,
	  ROUND(rinv.GrandTotal,v_Round_) AS GrandTotal
	  FROM tblRecurringInvoice rinv
	  INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = rinv.AccountID
	  LEFT JOIN Ratemanagement3.tblCurrency cr ON rinv.CurrencyID   = cr.CurrencyId
	  WHERE ac.CompanyID = p_CompanyID
	  AND (p_AccountID = 0 OR rinv.AccountID = p_AccountID)
	  AND (p_Status =2 OR rinv.`Status` = p_Status)
	  ORDER BY
	  		CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN rinv.Title
	      END DESC,
	          CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN rinv.Title
	      END ASC,
	   	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
	      END DESC,
	          CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
	      END ASC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastInvoicedDateDESC') THEN rinv.LastInvoicedDate
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastInvoicedDateASC') THEN rinv.LastInvoicedDate
	      END ASC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NextInvoiceDateDESC') THEN rinv.NextInvoiceDate
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NextInvoiceDateASC') THEN rinv.NextInvoiceDate
	      END ASC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecurringInvoiceStatusDESC') THEN rinv.`Status`
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecurringInvoiceStatusASC') THEN rinv.`Status`
	      END ASC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN rinv.GrandTotal
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN rinv.GrandTotal
	      END ASC

	  LIMIT p_RowspPage OFFSET v_OffSet_;


	  SELECT
	      COUNT(*) AS totalcount,  ROUND(SUM(rinv.GrandTotal),v_Round_) AS total_gran
	  FROM tblRecurringInvoice rinv
	  INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = rinv.AccountID
	  LEFT JOIN Ratemanagement3.tblCurrency cr ON rinv.CurrencyID   = cr.CurrencyId
	  WHERE ac.CompanyID = p_CompanyID
	  AND (p_AccountID = 0 OR rinv.AccountID = p_AccountID)
	  AND (p_Status =2 OR rinv.`Status` = p_Status);
	END IF;
	IF p_isExport = 1
	THEN
	  SELECT
	  ac.AccountName,
	  rinv.LastInvoiceNumber,
	  rinv.LastInvoicedDate,
	  rinv.Description,
	  IFNULL(ac.BillingEmail,'') AS BillingEmail,
	  ROUND(rinv.GrandTotal,v_Round_) AS GrandTotal
	  FROM tblRecurringInvoice rinv
	  INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = rinv.AccountID
	  LEFT JOIN Ratemanagement3.tblCurrency cr ON rinv.CurrencyID   = cr.CurrencyId
	  WHERE ac.CompanyID = p_CompanyID
	  AND (p_AccountID = 0 OR rinv.AccountID = p_AccountID)
	  AND (p_Status =2 OR rinv.`Status` = p_Status);
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;

