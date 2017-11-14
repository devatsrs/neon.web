USE `RMBilling3`;

ALTER TABLE `tblPayment`
	CHANGE COLUMN `TransactionID` `TransactionID` VARCHAR(155) NULL COLLATE 'utf8_unicode_ci';
	
CREATE TABLE IF NOT EXISTS `tblTempPaymentAccounting` (
  `PaymentID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `AccountName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDate` datetime NOT NULL,
  `PaymentMethod` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentType` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Amount` decimal(18,8) NOT NULL,
  `Status` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TransactionID` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  `InvoiceID` int(11) DEFAULT NULL,
  `InvoiceNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`PaymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;	

DROP PROCEDURE IF EXISTS `prc_importPaymentAccounting`;
DELIMITER //
CREATE PROCEDURE `prc_importPaymentAccounting`(
	IN `p_ProcessID` VARCHAR(50)
)
BEGIN
		DECLARE v_AffectedRecords_ INT DEFAULT 0;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	
	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_(
		`Message` VARCHAR(500)
	);	

	-- Delete Already Done Payment

	DELETE 
		tmpp
	FROM tblTempPaymentAccounting tmpp
	INNER JOIN tblPayment p 
	ON p.CompanyID = tmpp.CompanyID 
	AND p.TransactionID = tmpp.TransactionID
	WHERE tmpp.ProcessID= p_ProcessID ;
	
	-- update temp table invoiceid and other detail with invoice number matching

	Update tblTempPaymentAccounting ta LEFT JOIN tblInvoice inv ON inv.FullInvoiceNumber = ta.InvoiceNumber
		SET ta.InvoiceID = inv.InvoiceID, ta.AccountID = inv.AccountID, ta.CurrencyID = inv.CurrencyID
		WHERE ta.ProcessID= p_ProcessID AND inv.InvoiceID IS NOT NULL ;
	 

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
		CompanyID,
		AccountID,
		CurrencyId,
		Amount,
		PaymentDate,
		IF(Amount >= 0 , 'Payment in', 'Payment out' ) AS PaymentType,
		TransactionID,
		'Approved' AS `Status`,
		'Cash' AS PaymentMethod,
		Notes,
		Now() AS created_at,
		'System Imported' AS CreatedBy
	FROM tblTempPaymentAccounting 
		WHERE ProcessID= p_ProcessID AND InvoiceID IS NOT NULL;
		
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();		
		
	INSERT INTO tmp_JobLog_ (Message)	
		SELECT DISTINCT CONCAT(' Invalid InvoiceNumber : ',IFNULL(InvoiceNumber,''))
		FROM tblTempPaymentAccounting
		WHERE ProcessID = p_ProcessID AND InvoiceID IS NULL;
		
		
		    INSERT INTO tmp_JobLog_ (Message)
	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Imported \n\r ' );	

		SELECT * FROM tmp_JobLog_;
		
		DELETE FROM tblTempPaymentAccounting WHERE ProcessID = p_ProcessID;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;