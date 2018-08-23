Use RMBilling3;

CREATE TABLE `tblTempPBXPaymentDetail` (
	`TempPBXPaymentDetailID` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`ProcessID` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`CompanyID` INT(11) NULL DEFAULT NULL,
	`Note` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`PaymentDate` DATETIME NULL DEFAULT NULL,
	`Amount` DECIMAL(20,5) NULL DEFAULT NULL,
	`GatewayAccountID` VARCHAR(255) NULL DEFAULT NULL COMMENT 'AccountNumber' COLLATE 'utf8_unicode_ci',
	`AccountID` INT(11) NULL DEFAULT NULL,
	`CurrencyID` INT(11) NULL DEFAULT NULL,
	`TransactionID` VARCHAR(155) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`TempPBXPaymentDetailID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;


DELIMITER //
CREATE PROCEDURE `prc_importPBXPaymentAccounting`(
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
	FROM tblTempPBXPaymentDetail tmpp
	INNER JOIN tblPayment p
	ON p.TransactionID = tmpp.TransactionID
	WHERE tmpp.ProcessID= p_ProcessID ;

	-- update temp table AccountID and other detail with tblAccount number matching

	Update tblTempPBXPaymentDetail ta LEFT JOIN NeonRMDev.tblAccount ac ON ac.Number = ta.GatewayAccountID
		SET ta.AccountID = ac.AccountID, ta.CurrencyID = ac.CurrencyID, ta.CompanyID = ac.CompanyID
		WHERE ta.ProcessID= p_ProcessID;


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
		Note,
		Now() AS created_at,
		'System Imported' AS CreatedBy
	FROM tblTempPBXPaymentDetail
		WHERE ProcessID= p_ProcessID AND AccountID IS NOT NULL AND CurrencyID IS NOT NULL;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT CONCAT(' Invalid Tenants Code : ',IFNULL(GatewayAccountID,''))
		FROM tblTempPBXPaymentDetail
		WHERE ProcessID = p_ProcessID AND AccountID IS NULL AND CurrencyID IS NULL;


		    INSERT INTO tmp_JobLog_ (Message)
	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Imported \n\r ' );

		SELECT * FROM tmp_JobLog_;

		DELETE FROM tblTempPBXPaymentDetail WHERE ProcessID = p_ProcessID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;