use `speakintelligentBilling`;

CREATE TABLE `tblExactInvoiceLog` (
	`ExactInvoiceLogID` INT(11) NOT NULL AUTO_INCREMENT,
	`InvoiceID` INT(11) NOT NULL,
	`ExactResponse` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`PaymentStatus` ENUM('0','1') NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`ExactInvoiceLogID`),
	INDEX `IX_InvoiceID` (`InvoiceID`)
) COLLATE='utf8_unicode_ci' ENGINE=InnoDB;





DROP PROCEDURE IF EXISTS `prc_getPendingInvoiceListForExact`;
DELIMITER //
CREATE PROCEDURE `prc_getPendingInvoiceListForExact`()
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT
		i.InvoiceID,a.AccountName,a.Number,a.AccountID,i.InvoiceNumber,i.IssueDate,b.PaymentDueInDays
	FROM
		tblInvoice i
	INNER JOIN
		speakintelligentRM.tblAccount a ON a.AccountID=i.AccountID
	INNER JOIN
		speakintelligentRM.tblAccountBilling ab ON ab.AccountID=a.AccountID
	INNER JOIN
		speakintelligentRM.tblBillingClass b ON b.BillingClassID=ab.BillingClassID
	LEFT JOIN
		tblExactInvoiceLog ei ON i.InvoiceID=ei.InvoiceID
	WHERE
		i.InvoiceStatus='awaiting' AND ei.InvoiceID IS NULL AND i.InvoiceID=777;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getExactPendingInvoicePayment`;
DELIMITER //
CREATE PROCEDURE `prc_getExactPendingInvoicePayment`()
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/* PaymentStatus=0=Pending
	 * PaymentStatus=1=Paid
	 */

	SELECT
		eil.InvoiceID,i.InvoiceNumber,a.Number,a.AccountName,TRIM('"' FROM JSON_EXTRACT(eil.ExactResponse, "$.Invoice.EntryID")) AS ExactInvoiceID
	FROM
		tblExactInvoiceLog eil
	JOIN
		tblInvoice i ON i.InvoiceID = eil.InvoiceID
	JOIN
		speakintelligentRM.tblAccount a ON a.AccountID = i.AccountID
	WHERE
		PaymentStatus=0; -- Pending

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;