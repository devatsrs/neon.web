Use RMBilling3;

ALTER TABLE `tblInvoiceTemplate` ADD COLUMN `IgnoreCallCharge` TINYINT(1) NULL DEFAULT '0' AFTER `ManagementReport`;
ALTER TABLE `tblInvoiceTemplate` ADD COLUMN `ShowPaymentWidgetInvoice` TINYINT(1) NULL DEFAULT '0' AFTER `IgnoreCallCharge`;

CREATE TABLE `tblCreditNotes` (
	`CreditNotesID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NULL DEFAULT NULL,
	`AccountID` INT(11) NULL DEFAULT NULL,
	`Address` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`CreditNotesNumber` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`IssueDate` DATETIME NULL DEFAULT NULL,
	`CurrencyID` INT(11) NULL DEFAULT NULL,
	`CreditNotesType` INT(11) NULL DEFAULT NULL,
	`SubTotal` DECIMAL(18,6) NULL DEFAULT NULL,
	`TotalDiscount` DECIMAL(18,2) NULL DEFAULT '0.00',
	`TaxRateID` INT(11) NULL DEFAULT NULL,
	`TotalTax` DECIMAL(18,6) NULL DEFAULT '0.000000',
	`CreditNotesTotal` DECIMAL(18,6) NULL DEFAULT NULL,
	`GrandTotal` DECIMAL(18,6) NULL DEFAULT NULL,
	`Description` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Attachment` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Note` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`Terms` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`CreditNotesStatus` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`PDF` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`UsagePath` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`PreviousBalance` DECIMAL(18,6) NULL DEFAULT NULL,
	`TotalDue` DECIMAL(18,6) NULL DEFAULT NULL,
	`Payment` DECIMAL(18,6) NULL DEFAULT NULL,
	`CreatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ModifiedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	`FooterTerm` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`RecurringInvoiceID` INT(11) NULL DEFAULT '0',
	`ProcessID` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`FullCreditNotesNumber` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ServiceID` INT(11) NULL DEFAULT '0',
	`BillingClassID` INT(11) NULL DEFAULT NULL,
	`PaidAmount` DECIMAL(18,6) NULL DEFAULT '0.000000',
	PRIMARY KEY (`CreditNotesID`),
	INDEX `IX_AccountID_Status_CompanyID` (`AccountID`, `CreditNotesStatus`, `CompanyID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;


CREATE TABLE `tblCreditNotesDetail` (
	`CreditNotesDetailID` INT(11) NOT NULL AUTO_INCREMENT,
	`CreditNotesID` INT(11) NOT NULL,
	`ProductID` INT(11) NULL DEFAULT NULL,
	`Description` VARCHAR(250) NOT NULL COLLATE 'utf8_unicode_ci',
	`Price` DECIMAL(18,6) NOT NULL,
	`Qty` INT(11) NULL DEFAULT NULL,
	`Discount` DECIMAL(18,2) NULL DEFAULT NULL,
	`TaxRateID` INT(11) NULL DEFAULT NULL,
	`TaxRateID2` INT(11) NULL DEFAULT NULL,
	`TaxAmount` DECIMAL(18,6) NOT NULL DEFAULT '0.000000',
	`LineTotal` DECIMAL(18,6) NOT NULL,
	`CreatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ModifiedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	`ProductType` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`CreditNotesDetailID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE `tblCreditNotesLog` (
	`CreditNotesLogID` INT(11) NOT NULL AUTO_INCREMENT,
	`CreditNotesID` INT(11) NULL DEFAULT NULL,
	`Note` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`CreditNotesLogStatus` INT(11) NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`CreditNotesLogID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

CREATE TABLE `tblCreditNotesTaxRate` (
	`CreditNotesTaxRateID` INT(11) NOT NULL AUTO_INCREMENT,
	`CreditNotesID` INT(11) NOT NULL,
	`TaxRateID` INT(11) NOT NULL,
	`CreditNotesDetailID` INT(11) NOT NULL DEFAULT '0',
	`TaxAmount` DECIMAL(18,6) NOT NULL,
	`Title` VARCHAR(500) NOT NULL COLLATE 'utf8_unicode_ci',
	`CreditNotesTaxType` TINYINT(4) NOT NULL DEFAULT '0',
	`CreatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ModifiedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`CreditNotesTaxRateID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

/*procedure for list credit notes */
DROP PROCEDURE IF EXISTS `prc_getCreditNotes`;
DELIMITER //
CREATE PROCEDURE `prc_getCreditNotes`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_CreditNotesNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_CreditNotesStatus` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CurrencyID` INT,
	IN `p_isExport` INT
)
BEGIN
    
    DECLARE v_OffSet_ INT;
    DECLARE v_Round_ INT;    
    DECLARE v_CurrencyCode_ VARCHAR(50);
 	 SET sql_mode = 'ALLOW_INVALID_DATES';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	        
 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
 	 SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	 SELECT cr.Symbol INTO v_CurrencyCode_ from Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
    IF p_isExport = 0
    THEN
        SELECT 
        ac.AccountName,
        CONCAT(LTRIM(RTRIM(cn.CreditNotesNumber))) AS CreditNotesNumber,
        cn.IssueDate,
        CONCAT(IFNULL(cr.Symbol,''),ROUND(cn.GrandTotal,v_Round_)) AS GrandTotal2,		
        cn.CreditNotesStatus,
        cn.CreditNotesID,
        cn.Description,
        cn.Attachment,
        cn.AccountID,		  
		  IFNULL(ac.BillingEmail,'') AS BillingEmail,
		  ROUND(cn.GrandTotal,v_Round_) AS GrandTotal,
		  IFNULL(cn.GrandTotal - cn.PaidAmount,0) AS CreditNoteAmount
		  
        FROM tblCreditNotes cn
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = cn.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON cn.BillingClassID = b.BillingClassID	
		LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        LEFT JOIN Ratemanagement3.tblCurrency cr ON cn.CurrencyID   = cr.CurrencyId 
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND cn.AccountID = p_AccountID))
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND cn.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND cn.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND cn.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND cn.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND cn.CurrencyID = p_CurrencyID))
        ORDER BY
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
            END DESC,
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesStatusDESC') THEN cn.CreditNotesStatus
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesStatusASC') THEN cn.CreditNotesStatus
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesNumberASC') THEN cn.CreditNotesNumber
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesNumberDESC') THEN cn.CreditNotesNumber
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN cn.IssueDate
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN cn.IssueDate
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN cn.GrandTotal
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN cn.GrandTotal
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesIDDESC') THEN cn.CreditNotesID
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesIDASC') THEN cn.CreditNotesID
            END ASC
        
        LIMIT p_RowspPage OFFSET v_OffSet_;
        
        
        SELECT
            COUNT(*) AS totalcount,  ROUND(SUM(cn.GrandTotal),v_Round_) AS total_grand,v_CurrencyCode_ as currency_symbol
        FROM
        tblCreditNotes cn
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = cn.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON cn.BillingClassID = b.BillingClassID
		LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND cn.AccountID = p_AccountID))
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND cn.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND cn.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND cn.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND cn.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND cn.CurrencyID = p_CurrencyID));
    END IF;
    IF p_isExport = 1
    THEN
        SELECT ac.AccountName ,
        ( CONCAT(LTRIM(RTRIM(IFNULL(it.InvoiceNumberPrefix,''))), LTRIM(RTRIM(cn.CreditNotesNumber)))) AS CreditNotesNumber,
        cn.IssueDate,
        ROUND(cn.GrandTotal,v_Round_) AS GrandTotal,        
        cn.CreditNotesStatus
        FROM tblCreditNotes cn
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = cn.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON cn.BillingClassID = b.BillingClassID
	    LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND cn.AccountID = p_AccountID))
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND cn.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND cn.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND cn.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND cn.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND cn.CurrencyID = p_CurrencyID));
    END IF;
     IF p_isExport = 2
    THEN
        SELECT ac.AccountID ,
        ac.AccountName,
        ( CONCAT(LTRIM(RTRIM(IFNULL(it.InvoiceNumberPrefix,''))), LTRIM(RTRIM(cn.CreditNotesNumber)))) AS CreditNotesNumber,
        cn.IssueDate,
		  ROUND(cn.GrandTotal,v_Round_) AS GrandTotal,		  
        cn.CreditNotesStatus,
        cn.CreditNotesID
        FROM tblCreditNotes cn
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = cn.AccountID
       
		INNER JOIN Ratemanagement3.tblBillingClass b ON cn.BillingClassID = b.BillingClassID
		  LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND cn.AccountID = p_AccountID))
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND cn.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND cn.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND cn.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND cn.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND cn.CurrencyID = p_CurrencyID));
    END IF; 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;

/*procedure for list credit notes logs*/

DROP PROCEDURE IF EXISTS `prc_GetCreditNotesLog`;
DELIMITER //
CREATE PROCEDURE `prc_GetCreditNotesLog`(IN `p_CompanyID` INT, IN `p_CreditNotesID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50), IN `p_isExport` INT)
BEGIN

	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    IF p_isExport = 0
    THEN

       
            SELECT
                el.Note,
                el.CreditNotesLogStatus,
                el.created_at,                
                es.CreditNotesID                
            FROM tblCreditNotes es
            INNER JOIN Ratemanagement3.tblAccount ac
                ON ac.AccountID = es.AccountID
            INNER JOIN tblCreditNotesLog el
                ON el.CreditNotesID = es.CreditNotesID
            WHERE ac.CompanyID = p_CompanyID
            AND (p_CreditNotesID = '' 
            OR (p_CreditNotesID != ''
            AND es.CreditNotesID = p_CreditNotesID))
             ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesLogStatusDESC') THEN el.CreditNotesLogStatus
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesLogStatusASC') THEN el.CreditNotesLogStatus
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN el.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN el.created_at
                END ASC
					LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(*) AS totalcount
        FROM tblCreditNotes es
        INNER JOIN Ratemanagement3.tblAccount ac
            ON ac.AccountID = es.AccountID
        INNER JOIN tblCreditNotesLog el
            ON el.CreditNotesID = es.CreditNotesID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_CreditNotesID = ''
        OR (p_CreditNotesID != ''
        AND es.CreditNotesID = p_CreditNotesID));

    END IF;
    IF p_isExport = 1
    THEN

        SELECT
            el.Note,
            el.created_at,
            el.CreditNotesLogStatus,
            es.CreditNotesNumber
        FROM tblCreditNotes es
        INNER JOIN Ratemanagement3.tblAccount ac
            ON ac.AccountID = es.AccountID
        INNER JOIN tblCreditNotesLog el
            ON el.CreditNotesID = es.CreditNotesID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_CreditNotesID = ''
        OR (p_CreditNotesID != ''
        AND es.CreditNotesID = p_CreditNotesID));


    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


ALTER TABLE `tblPayment`
	ADD COLUMN `CreditNotesID` INT(11) NULL DEFAULT '0' AFTER `TransactionID`;
	

/* Update Invoice status on bulk upload - change procedure prc_insertPayments*/	
DROP PROCEDURE IF EXISTS `prc_insertPayments`;
DELIMITER //
CREATE PROCEDURE `prc_insertPayments`(
	IN `p_CompanyID` INT,
	IN `p_ProcessID` VARCHAR(100),
	IN `p_UserID` INT
)
BEGIN

	
	DECLARE v_UserName varchar(30);
 	
 	SELECT CONCAT(u.FirstName,CONCAT(' ',u.LastName)) as name into v_UserName from Ratemanagement3.tblUser u where u.UserID=p_UserID;
 	
 	INSERT INTO tblPayment (
	 		CompanyID,
	 		 AccountID,
			 InvoiceNo,
			 PaymentDate,
			 PaymentMethod,
			 PaymentType,
			 Notes,
			 Amount,
			 CurrencyID,
			 Recall,
			 `Status`,
			 created_at,
			 updated_at,
			 CreatedBy,
			 ModifyBy,
			 RecallReasoan,
			 RecallBy,
			 BulkUpload,
			 InvoiceID
			 )
 	select tp.CompanyID,
	 		 tp.AccountID,
			 COALESCE(tp.InvoiceNo,''),
			 tp.PaymentDate,
			 tp.PaymentMethod,
			 tp.PaymentType,
			 tp.Notes,
			 tp.Amount,
			 ac.CurrencyId,
			 0 as Recall,
			 tp.Status,
			 Now() as created_at,
			 Now() as updated_at,
			 v_UserName as CreatedBy,
			 '' as ModifyBy,
			 '' as RecallReasoan,
			 '' as RecallBy,
			 1 as BulkUpload,
			 InvoiceID
	from tblTempPayment tp
	INNER JOIN Ratemanagement3.tblAccount ac 
		ON  ac.AccountID = tp.AccountID  and ac.AccountType = 1 and ac.CurrencyId IS NOT NULL
	where tp.ProcessID = p_ProcessID
			AND tp.PaymentDate <= NOW()
			AND tp.CompanyID = p_CompanyID;
			
	DROP TEMPORARY TABLE IF EXISTS tmp_UpdateInvoiceStatus_;
	CREATE TEMPORARY TABLE tmp_UpdateInvoiceStatus_  (
		InvoiceID INT,
		AccountID INT,
		Status Varchar(255),
		Note TEXT
	);
	
	INSERT INTO tmp_UpdateInvoiceStatus_ (
	 		InvoiceID,
	 		 AccountID,
			 Status,
			 Note			 
			 )
 	select 			 
			 tp.InvoiceID,
			 tp.AccountID,			 
		CASE WHEN (tp.Amount>= (inv.GrandTotal -  (SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ))
 		then 
 		'paid' 
				ELSE 'partially_paid' 
			END
			as status,
			'' as Note
	from tblTempPayment tp
	INNER JOIN Ratemanagement3.tblAccount ac 
		ON  ac.AccountID = tp.AccountID  and ac.AccountType = 1 and ac.CurrencyId IS NOT NULL
		INNER JOIN tblInvoice inv ON tp.InvoiceID=inv.InvoiceID
	where tp.ProcessID = p_ProcessID
			AND tp.PaymentDate <= NOW()
			AND tp.CompanyID = p_CompanyID;
			
	 Update tblInvoice i INNER JOIN tmp_UpdateInvoiceStatus_ tmp ON i.InvoiceID=tmp.InvoiceID 
	 SET i.InvoiceStatus = tmp.Status;
	 
	INSERT INTO tblInvoiceLog (
	 		InvoiceID,			 
			 Note,
			 created_at
			 )
 	select 			 
			 tp.InvoiceID,			 	
			'Paid By System' as Note,
			 Now() as created_at,
			 Now() as updated_at	
	from tblTempPayment tp
	INNER JOIN Ratemanagement3.tblAccount ac 
		ON  ac.AccountID = tp.AccountID  and ac.AccountType = 1 and ac.CurrencyId IS NOT NULL
		INNER JOIN tblInvoice inv ON tp.InvoiceID=inv.InvoiceID
	where tp.ProcessID = p_ProcessID
			AND tp.PaymentDate <= NOW()
			AND tp.CompanyID = p_CompanyID;
			
	 delete from tblTempPayment where CompanyID = p_CompanyID and ProcessID = p_ProcessID;
	 
			
END//
DELIMITER ;

/*add creditnotes number fields*/

ALTER TABLE `tblInvoiceTemplate`
	ADD COLUMN `CreditNotesNumberPrefix` VARCHAR(50) NULL DEFAULT NULL AFTER `LastEstimateNumber`,
	ADD COLUMN `CreditNotesStartNumber` VARCHAR(50) NULL DEFAULT NULL AFTER `CreditNotesNumberPrefix`,
	ADD COLUMN `LastCreditNotesNumber` BIGINT(20) NULL DEFAULT NULL AFTER `CreditNotesStartNumber`;
	
	
/* procedure changes for display creditnotes amount in invoice listing and exporting */ 	
DROP PROCEDURE IF EXISTS `prc_getInvoice`;
DELIMITER //
CREATE PROCEDURE `prc_getInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_InvoiceType` INT,
	IN `p_InvoiceStatus` LONGTEXT,
	IN `p_IsOverdue` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CurrencyID` INT,
	IN `p_isExport` INT,
	IN `p_sageExport` INT,
	IN `p_zerovalueinvoice` INT,
	IN `p_InvoiceID` LONGTEXT,
	IN `p_userID` INT
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
		Currency varchar(50),
		GrandTotal decimal(18,6),
		TotalPayment decimal(18,6),
		PendingAmount decimal(18,6),
		InvoiceStatus varchar(50),
		CreditNoteAmount decimal(18,6),
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
		NominalAnalysisNominalAccountNumber varchar(100),
		TotalMinutes BIGINT(20)
	);

		INSERT INTO tmp_Invoices_
		SELECT inv.InvoiceType ,
			ac.AccountName,
			FullInvoiceNumber as InvoiceNumber,
			inv.IssueDate,
			IF(invd.StartDate IS NULL ,'',CONCAT('From ',date(invd.StartDate) ,'<br> To ',date(invd.EndDate))) as InvoicePeriod,
			IFNULL(cr.Symbol,'') as CurrencySymbol,
			cr.Code AS Currency,
			inv.GrandTotal as GrandTotal,
			(SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) as TotalPayment,
			(inv.GrandTotal -  (SELECT IFNULL(sum(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
			inv.InvoiceStatus,
			(SELECT IFNULL(sum(GrandTotal) - sum(PaidAmount),0)  FROM tblCreditNotes c WHERE c.AccountID = inv.AccountID and c.CreditNotesStatus='open' and inv.InvoiceType=1) as CreditNoteAmount,
			inv.InvoiceID,
			inv.Description,
			inv.Attachment,
			inv.AccountID,
			inv.ItemInvoice,
			IFNULL(ac.BillingEmail,'') as BillingEmail,
			ac.Number,
			if (inv.BillingClassID > 0,
				 (SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblBillingClass b where  b.BillingClassID =inv.BillingClassID),
				 (SELECT IFNULL(b.PaymentDueInDays,0) FROM Ratemanagement3.tblAccountBilling ab INNER JOIN Ratemanagement3.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID AND ab.ServiceID = inv.ServiceID LIMIT 1)
			) as PaymentDueInDays,
			(SELECT PaymentDate FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID ORDER BY PaymentID DESC LIMIT 1) AS PaymentDate,
			inv.SubTotal,
			inv.TotalTax,
			ac.NominalAnalysisNominalAccountNumber,
			IFNULL((SELECT SUM(IFNULL(TotalMinutes,0)) FROM tblInvoiceDetail tid WHERE tid.InvoiceID=inv.InvoiceID GROUP BY tid.InvoiceID),0) AS TotalMinutes
			FROM tblInvoice inv
			INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
			LEFT JOIN tblInvoiceDetail invd ON invd.InvoiceID = inv.InvoiceID AND (invd.ProductType = 5 OR inv.InvoiceType = 2)
			LEFT JOIN Ratemanagement3.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId
			WHERE ac.CompanyID = p_CompanyID
			AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
			AND (p_userID = 0 OR ac.Owner = p_userID)
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
			DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY)) AS DueDate,
			IF(InvoiceStatus IN ('send','awaiting'), IF(DATEDIFF(CURDATE(),DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY))) > 0,DATEDIFF(CURDATE(),DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY))),''), '') AS DueDays,
			InvoiceID,
			Description,
			Attachment,
			AccountID,
			PendingAmount as OutstandingAmount,
			ItemInvoice,
			BillingEmail,
			GrandTotal,
			CreditNoteAmount
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
			CreditNoteAmount,
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
			CreditNoteAmount,
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
	
	IF p_isExport = 3 -- api
	THEN

		SELECT
			InvoiceID,
			AccountName,
			InvoiceNumber,
			IssueDate,
			InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `PendingAmount`,
			InvoiceStatus,
			DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY)) AS DueDate,
			IF(InvoiceStatus IN ('send','awaiting'), IF(DATEDIFF(CURDATE(),DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY))) > 0,DATEDIFF(CURDATE(),DATE(DATE_ADD(IssueDate, INTERVAL IFNULL(PaymentDueInDays,0) DAY))),''), '') AS DueDays,
			Currency,
			InvoiceID,
			Description,
			Attachment,
			AccountID,
			PendingAmount as OutstandingAmount,
			ItemInvoice,
			BillingEmail,
			GrandTotal,
			TotalMinutes
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
				AND (p_userID = 0 OR ac.Owner = p_userID)
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
END//
DELIMITER ;



/* Add Procedure for get invoices list for specific credit notes*/
DROP PROCEDURE IF EXISTS `prc_getCreditNoteInvoices`;
DELIMITER //
CREATE PROCEDURE `prc_getCreditNoteInvoices`(
	IN `p_AccountID` INT,
	IN `p_InvoiceNumber` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT
)
BEGIN
DECLARE v_OffSet_ int;

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

DROP TEMPORARY TABLE IF EXISTS tmp_CreditNotes_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CreditNotes_(
		InvoiceID int,		
		FullInvoiceNumber varchar(100),
		IssueDate datetime,	
		GrandTotal decimal(18,6),
		TotalPayment decimal(18,6),
		AccountID int
	);

		INSERT INTO tmp_CreditNotes_
		select 
			`tblInvoice`.`InvoiceID`,
 			IFNULL(`tblInvoice`.`FullInvoiceNumber`,'') AS FullInvoiceNumber,
  			`tblInvoice`.`IssueDate`,
   		`tblInvoice`.`GrandTotal`,
			(select IFNULL(SUM(Amount),0) from tblPayment where tblPayment.InvoiceID=tblInvoice.InvoiceID and tblPayment.Recall=0) as TotalPayment,
			p_AccountID as AccountID
			from `tblInvoice` 
			where `tblInvoice`.`AccountID` = p_AccountID 
			and `tblInvoice`.`GrandTotal` <> 0 
		  	and (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND `tblInvoice`.`FullInvoiceNumber` = p_InvoiceNumber))
			and `tblInvoice`.`InvoiceStatus` in ('partially_paid','send','awaiting');
			
			
			select 	*
			from `tmp_CreditNotes_`
			where `tmp_CreditNotes_`.`GrandTotal` > `tmp_CreditNotes_`.`TotalPayment` 
			
			 LIMIT p_RowspPage OFFSET v_OffSet_;		
			 
		 SELECT
            COUNT(*) AS totalcount
        FROM
        tmp_CreditNotes_ cn
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = cn.AccountID
        where cn.GrandTotal > cn.TotalPayment;
			
			SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_CustomerPanel_getCreditNotes`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerPanel_getCreditNotes`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_CreditNotesNumber` VARCHAR(50),
	IN `p_IssueDateStart` DATETIME,
	IN `p_IssueDateEnd` DATETIME,
	IN `p_CreditNotesStatus` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CurrencyID` INT,
	IN `p_isExport` INT

)
BEGIN
    
    DECLARE v_OffSet_ INT;
    DECLARE v_Round_ INT;    
    DECLARE v_CurrencyCode_ VARCHAR(50);
 	 SET sql_mode = 'ALLOW_INVALID_DATES';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	        
 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
 	 SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	 SELECT cr.Symbol INTO v_CurrencyCode_ from Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
    IF p_isExport = 0
    THEN
        SELECT         
        cn.FullCreditNotesNumber AS CreditNotesNumber,
        cn.IssueDate,
        CONCAT(IFNULL(cr.Symbol,''),ROUND(cn.GrandTotal,v_Round_)) AS GrandTotal2,		
        cn.CreditNotesStatus,
        cn.CreditNotesID,
        cn.Description,
        cn.Attachment,
        cn.AccountID,		  
		  IFNULL(ac.BillingEmail,'') AS BillingEmail,
		  ROUND(cn.GrandTotal,v_Round_) AS GrandTotal,
		  IFNULL(cn.GrandTotal - cn.PaidAmount,0) AS CreditNoteAmount
		  
        FROM tblCreditNotes cn
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = cn.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON cn.BillingClassID = b.BillingClassID	
		LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        LEFT JOIN Ratemanagement3.tblCurrency cr ON cn.CurrencyID   = cr.CurrencyId 
        WHERE ac.CompanyID = p_CompanyID
        	AND (cn.AccountID = p_AccountID)
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND cn.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND cn.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND cn.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND cn.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND cn.CurrencyID = p_CurrencyID))
        ORDER BY
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
            END DESC,
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesStatusDESC') THEN cn.CreditNotesStatus
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesStatusASC') THEN cn.CreditNotesStatus
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesNumberASC') THEN cn.CreditNotesNumber
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesNumberDESC') THEN cn.CreditNotesNumber
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN cn.IssueDate
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN cn.IssueDate
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN cn.GrandTotal
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN cn.GrandTotal
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesIDDESC') THEN cn.CreditNotesID
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesIDASC') THEN cn.CreditNotesID
            END ASC
        
        LIMIT p_RowspPage OFFSET v_OffSet_;
        
        
        SELECT
            COUNT(*) AS totalcount,  ROUND(SUM(cn.GrandTotal),v_Round_) AS total_grand,v_CurrencyCode_ as currency_symbol
        FROM
        tblCreditNotes cn
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = cn.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON cn.BillingClassID = b.BillingClassID
		LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (cn.AccountID = p_AccountID)
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND cn.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND cn.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND cn.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND cn.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND cn.CurrencyID = p_CurrencyID));
    END IF;
    IF p_isExport = 1
    THEN
        SELECT 
        cn.FullCreditNotesNumber AS CreditNotesNumber,
        cn.IssueDate,
        ROUND(cn.GrandTotal,v_Round_) AS GrandTotal,
		IFNULL(cn.GrandTotal - cn.PaidAmount,0) AS AvailableBalance,        
        cn.CreditNotesStatus AS Status
        FROM tblCreditNotes cn
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = cn.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON cn.BillingClassID = b.BillingClassID
	    LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (cn.AccountID = p_AccountID)
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND cn.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND cn.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND cn.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND cn.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND cn.CurrencyID = p_CurrencyID));
    END IF;
    
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;

ALTER TABLE `tblAccountSubscription`
	ADD COLUMN `DiscountAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `Status`;
ALTER TABLE `tblAccountSubscription`
	ADD COLUMN `DiscountType` VARCHAR(100) NULL DEFAULT NULL AFTER `DiscountAmount`;

ALTER TABLE `tblAccountOneOffCharge`
	ADD COLUMN `DiscountAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `ServiceID`;
ALTER TABLE `tblAccountOneOffCharge`
	ADD COLUMN `DiscountType` VARCHAR(100) NULL DEFAULT NULL AFTER `DiscountAmount`;

ALTER TABLE `tblInvoiceDetail`
  ADD COLUMN `AccountOneOffChargeID` INT(11) NULL DEFAULT '0' AFTER `AccountSubscriptionID`;
ALTER TABLE `tblInvoiceDetail`
	ADD COLUMN `DiscountAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `AccountOneOffChargeID`;
ALTER TABLE `tblInvoiceDetail`
	ADD COLUMN `DiscountType` VARCHAR(100) NULL DEFAULT NULL AFTER `DiscountAmount`;
ALTER TABLE `tblInvoiceDetail`
	ADD COLUMN `DiscountLineAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `DiscountType`;

ALTER TABLE `tblRecurringInvoiceDetail`
	ADD COLUMN `DiscountAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `ProductType`;
ALTER TABLE `tblRecurringInvoiceDetail`
	ADD COLUMN `DiscountType` VARCHAR(100) NULL DEFAULT NULL AFTER `DiscountAmount`;
ALTER TABLE `tblRecurringInvoiceDetail`
	ADD COLUMN `DiscountLineAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `DiscountType`;

ALTER TABLE `tblEstimateDetail`
	ADD COLUMN `DiscountAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `ProductType`;
ALTER TABLE `tblEstimateDetail`
	ADD COLUMN `DiscountType` VARCHAR(100) NULL DEFAULT NULL AFTER `DiscountAmount`;
ALTER TABLE `tblEstimateDetail`
	ADD COLUMN `DiscountLineAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `DiscountType`;

ALTER TABLE `tblCreditNotesDetail`
	ADD COLUMN `DiscountAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `ProductType`;
ALTER TABLE `tblCreditNotesDetail`
	ADD COLUMN `DiscountType` VARCHAR(100) NULL DEFAULT NULL AFTER `DiscountAmount`;
ALTER TABLE `tblCreditNotesDetail`
	ADD COLUMN `DiscountLineAmount` DECIMAL(18,6) NULL DEFAULT '0.000000' AFTER `DiscountType`;

/* procedure changes for retrieve update discount amount & type through procedure*/
DROP PROCEDURE IF EXISTS `prc_GetAccountSubscriptions`;
CREATE PROCEDURE `prc_GetAccountSubscriptions`(
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
			sa.AccountSubscriptionID as AID,
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
			sa.`Status`,
			sa.DiscountAmount,
			sa.DiscountType
		FROM tblAccountSubscription sa
			INNER JOIN tblBillingSubscription sb
				ON sb.SubscriptionID = sa.SubscriptionID
			INNER JOIN Ratemanagement3.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN Ratemanagement3.tblService s
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
			INNER JOIN Ratemanagement3.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN Ratemanagement3.tblService s
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
			INNER JOIN Ratemanagement3.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN Ratemanagement3.tblService s
				ON sa.ServiceID = s.ServiceID
		WHERE 	(p_AccountID = 0 OR a.AccountID = p_AccountID)
			AND (p_SubscriptionName is null OR sb.Name LIKE concat('%',p_SubscriptionName,'%'))
			AND (p_Status = sa.`Status`)
			AND (p_ServiceID =0 OR s.ServiceID = p_ServiceID);
	END IF;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

