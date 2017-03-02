USE `RMBilling3`;

ALTER TABLE `tblInvoice`
	ADD COLUMN `RecurringInvoiceID` INT(50) NULL DEFAULT NULL AFTER `EstimateID`,
	ADD COLUMN `ProcessID` VARCHAR(50) NULL DEFAULT NULL AFTER `RecurringInvoiceID`;


-- Dumping structure for function RMBilling3.FnGetInvoiceNumber
DROP FUNCTION IF EXISTS `FnGetInvoiceNumber`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `FnGetInvoiceNumber`(
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
END//
DELIMITER ;

-- Dumping structure for procedure RMBilling3.prc_Convert_Invoices_to_Estimates
DROP PROCEDURE IF EXISTS `prc_Convert_Invoices_to_Estimates`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_Convert_Invoices_to_Estimates`(
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
END//
DELIMITER ;
