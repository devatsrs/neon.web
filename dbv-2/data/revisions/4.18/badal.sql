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
	`TaxAmount` DECIMAL(18,6) NOT NULL,
	`Title` VARCHAR(500) NOT NULL COLLATE 'utf8_unicode_ci',
	`CreditNotesTaxType` TINYINT(4) NOT NULL DEFAULT '0',
	`CreatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ModifiedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`CreditNotesTaxRateID`),
	UNIQUE INDEX `IX_EstimateTaxRateUnique` (`CreditNotesID`, `TaxRateID`, `CreditNotesTaxType`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;

/*procedure for list credit notes */
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
        CONCAT(LTRIM(RTRIM(inv.CreditNotesNumber))) AS CreditNotesNumber,
        inv.IssueDate,
        CONCAT(IFNULL(cr.Symbol,''),ROUND(inv.GrandTotal,v_Round_)) AS GrandTotal2,		
        inv.CreditNotesStatus,
        inv.CreditNotesID,
        inv.Description,
        inv.Attachment,
        inv.AccountID,		  
		  IFNULL(ac.BillingEmail,'') AS BillingEmail,
		  ROUND(inv.GrandTotal,v_Round_) AS GrandTotal
		  
        FROM tblCreditNotes inv
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON inv.BillingClassID = b.BillingClassID	
		LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        LEFT JOIN Ratemanagement3.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND inv.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND inv.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID))
        ORDER BY
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
            END DESC,
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesStatusDESC') THEN inv.CreditNotesStatus
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesStatusASC') THEN inv.CreditNotesStatus
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesNumberASC') THEN inv.CreditNotesNumber
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesNumberDESC') THEN inv.CreditNotesNumber
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN inv.IssueDate
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN inv.IssueDate
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN inv.GrandTotal
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN inv.GrandTotal
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesIDDESC') THEN inv.CreditNotesID
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreditNotesIDASC') THEN inv.CreditNotesID
            END ASC
        
        LIMIT p_RowspPage OFFSET v_OffSet_;
        
        
        SELECT
            COUNT(*) AS totalcount,  ROUND(SUM(inv.GrandTotal),v_Round_) AS total_grand,v_CurrencyCode_ as currency_symbol
        FROM
        tblCreditNotes inv
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON inv.BillingClassID = b.BillingClassID
		LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND inv.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND inv.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF;
    IF p_isExport = 1
    THEN
        SELECT ac.AccountName ,
        ( CONCAT(LTRIM(RTRIM(IFNULL(it.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.CreditNotesNumber)))) AS CreditNotesNumber,
        inv.IssueDate,
        ROUND(inv.GrandTotal,v_Round_) AS GrandTotal,
        inv.CreditNotesStatus
        FROM tblCreditNotes inv
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
        
		INNER JOIN Ratemanagement3.tblBillingClass b ON inv.BillingClassID = b.BillingClassID
	    LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND inv.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND inv.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF;
     IF p_isExport = 2
    THEN
        SELECT ac.AccountID ,
        ac.AccountName,
        ( CONCAT(LTRIM(RTRIM(IFNULL(it.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.CreditNotesNumber)))) AS CreditNotesNumber,
        inv.IssueDate,
		  ROUND(inv.GrandTotal,v_Round_) AS GrandTotal,
        inv.CreditNotesStatus,
        inv.CreditNotesID
        FROM tblCreditNotes inv
        INNER JOIN Ratemanagement3.tblAccount ac ON ac.AccountID = inv.AccountID
       
		INNER JOIN Ratemanagement3.tblBillingClass b ON inv.BillingClassID = b.BillingClassID
		  LEFT JOIN tblInvoiceTemplate it on b.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_CreditNotesNumber = '' OR ( p_CreditNotesNumber != '' AND inv.CreditNotesNumber = p_CreditNotesNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_CreditNotesStatus = '' OR ( p_CreditNotesStatus != '' AND inv.CreditNotesStatus = p_CreditNotesStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF; 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;

/*procedure for list credit notes logs*/

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






