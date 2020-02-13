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
CREATE PROCEDURE `prc_getPendingInvoiceListForExact`(
	IN `p_CompanyID` INT,
	IN `p_InvoiceID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_InvoiceID IS NULL -- get pending invoice list
	THEN

		SELECT
			i.InvoiceID,i.InvoiceNumber,i.IssueDate,i.ItemInvoice,i.PDF,i.UblInvoice,i.UsagePath,
			a.AccountName,a.Number,a.AccountID,a.PaymentMethod,b.PaymentDueInDays,ab.BillingType
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
			i.CompanyID = p_CompanyID
			AND (i.InvoiceStatus='send' OR i.InvoiceStatus='paid') -- only send and paid status invoice
			AND i.InvoiceType=1 -- only cusomer invoices (sent invoices)
			AND ei.InvoiceID IS NULL; -- only invoice which is not posted to exact

	ELSE	-- get components of given InvoiceID

		SELECT ItemInvoice INTO @ItemInvoice FROM tblInvoice WHERE InvoiceID=p_InvoiceID;

		IF @ItemInvoice = 1 -- item invoice
		THEN
			SELECT
				p.Code AS Component,id.LineTotal AS TotalCost,id.TaxRateID,tr.Title AS NeonVATCode,IFNULL(tr.VATCode,'') AS ExactVATCode, i.IssueDate AS StartDate
			FROM
				tblInvoice i
			INNER JOIN
				tblInvoiceDetail id ON id.InvoiceID = i.InvoiceID
			INNER JOIN
				tblProduct p ON p.ProductID=id.ProductID
			LEFT JOIN
				speakintelligentRM.tblTaxRate tr ON tr.TaxRateId = id.TaxRateID
			WHERE
				i.InvoiceID=p_InvoiceID;
		ELSE -- usage invoice
			SELECT
				icd.CLI,icd.Component,icd.TotalCost,icd.Type,id.StartDate,id.TaxRateID,tr.Title AS NeonVATCode,icd.ProductType,icd.AccountServiceID,IFNULL(tr.VATCode,'') AS ExactVATCode
			FROM
				tblInvoiceDetail id
			INNER JOIN
				tblInvoiceComponentDetail icd ON icd.InvoiceDetailID=id.InvoiceDetailID
			LEFT JOIN
				speakintelligentRM.tblTaxRate tr ON tr.TaxRateId = id.TaxRateID
			WHERE
				id.InvoiceID=p_InvoiceID;
		END IF;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getExactPendingInvoicePayment`;
DELIMITER //
CREATE PROCEDURE `prc_getExactPendingInvoicePayment`(
	IN `p_CompanyID` INT
)
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
		i.CompanyID = p_CompanyID AND
		PaymentStatus=0; -- Pending

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;