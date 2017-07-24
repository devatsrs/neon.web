USE `RMBilling3`;

-- SAGEPAY Payment Gateway
ALTER TABLE `tblTransactionLog`
	CHANGE COLUMN `Reposnse` `Response` LONGTEXT NULL COLLATE 'utf8_unicode_ci' AFTER `CreatedBy`;


-- Payment Import from MOR
CREATE TABLE IF NOT EXISTS `tblTempPaymentImportExport` (
	`PaymentID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL,
	`ProcessID` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`AccountID` INT(11) NOT NULL,
	`AccountNumber` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`PaymentDate` DATETIME NOT NULL,
	`PaymentMethod` VARCHAR(15) NOT NULL COLLATE 'utf8_unicode_ci',
	`PaymentType` VARCHAR(15) NOT NULL COLLATE 'utf8_unicode_ci',
	`Notes` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Amount` DECIMAL(18,8) NOT NULL,
	`Status` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`TransactionID` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`PaymentID`),
	INDEX `IX_CompanyID_ProcessID_TransactionID` (`ProcessID`, `CompanyID`, `TransactionID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
ROW_FORMAT=COMPACT;

	ALTER TABLE `tblPayment`
	ADD COLUMN `TransactionID` VARCHAR(20) NULL AFTER `InvoiceID`;


	ALTER TABLE `tblPayment`
	CHANGE COLUMN `updated_at` `updated_at` DATETIME NULL AFTER `created_at`,
	CHANGE COLUMN `ModifyBy` `ModifyBy` VARCHAR(50) NULL COLLATE 'utf8_unicode_ci' AFTER `updated_at`,
	CHANGE COLUMN `RecallReasoan` `RecallReasoan` VARCHAR(500) NULL COLLATE 'utf8_unicode_ci' AFTER `Recall`,
	CHANGE COLUMN `RecallBy` `RecallBy` VARCHAR(30) NULL COLLATE 'utf8_unicode_ci' AFTER `RecallReasoan`;

  ALTER TABLE `tblPayment`	ADD INDEX `IX_CompanyID_TransactionID` (`CompanyID`, `TransactionID`);




DELIMITER |
DROP PROCEDURE IF EXISTS `prc_importFromTempPaymentImportExport`;
CREATE PROCEDURE `prc_importFromTempPaymentImportExport`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_CurrentDate` DATETIME
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN


 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


 -- delete payments which exists in Neon

 DELETE tmpp
 FROM tblTempPaymentImportExport tmpp
 INNER JOIN tblPayment p on
 p.CompanyID = tmpp.CompanyID and
 p.TransactionID = tmpp.TransactionID
 where tmpp.ProcessID= p_ProcessID ;


 -- insert payments which are not exist -- also match AccountNumber

 insert into tblPayment (CompanyID,AccountID,CurrencyID,Amount,PaymentDate,PaymentType,TransactionID,`Status`,PaymentMethod,created_at,CreatedBy)
 select a.CompanyID,a.AccountID,a.CurrencyId,tmpp.Amount,tmpp.PaymentDate,	IF(tmpp.Amount >= 0 , 'Payment in', 'Payment out' ) as PaymentType ,tmpp.TransactionID,'Approved' as `Status`,'Cash' as PaymentMethod,p_CurrentDate as created_at, 'System Imported' as CreatedBy
 FROM tblTempPaymentImportExport tmpp
 INNER JOIN Ratemanagement3.tblAccount a on
	a.CompanyID = tmpp.CompanyID and
	(tmpp.AccountNumber = a.Number or tmpp.AccountNumber = a.AccountName) and a.CurrencyId > 0
 where tmpp.ProcessID= p_ProcessID ;


SELECT AccountNumber as `Account Number` ,Amount,PaymentDate as `Payment Date` ,PaymentType as `Payment Type`,TransactionID as `Transaction ID`,`Action`
FROM
(
		-- inserted records
		SELECT tmpp.* , 'Imported' as `Action`
		 FROM tblTempPaymentImportExport tmpp
		 INNER JOIN Ratemanagement3.tblAccount a on
			a.CompanyID = tmpp.CompanyID and
			(tmpp.AccountNumber = a.Number or tmpp.AccountNumber = a.AccountName)  and a.CurrencyId is not null
		 where tmpp.ProcessID= p_ProcessID

		 UNION

		  -- return skipped records
		 SELECT tmpp.* , 'Skipped' as `Action`
		 FROM tblTempPaymentImportExport tmpp
		 LEFT JOIN Ratemanagement3.tblAccount a on
			a.CompanyID = tmpp.CompanyID and
			(tmpp.AccountNumber = a.Number or tmpp.AccountNumber = a.AccountName)
		 where tmpp.ProcessID= p_ProcessID and a.AccountID is null

) tmp;

 --  SELECT tblNeonTempPaymentImportExport,*,'Skipped' as `Action` from tblNeonTempPaymentImportExport  where ProcessID= p_ProcessID ;

  delete from tblTempPaymentImportExport where ProcessID= p_ProcessID;


 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END|
DELIMITER ;