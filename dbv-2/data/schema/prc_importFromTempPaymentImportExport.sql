CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_importFromTempPaymentImportExport`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_CurrentDate` DATETIME
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE 
		tmpp
	FROM tblTempPaymentImportExport tmpp
	INNER JOIN tblPayment p 
	ON p.CompanyID = tmpp.CompanyID 
	AND p.TransactionID = tmpp.TransactionID
	WHERE tmpp.ProcessID= p_ProcessID ;

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
		a.CompanyID,
		a.AccountID,
		a.CurrencyId,
		tmpp.Amount,
		tmpp.PaymentDate,
		IF(tmpp.Amount >= 0 , 'Payment in', 'Payment out' ) AS PaymentType,
		tmpp.TransactionID,
		'Approved' AS `Status`,
		'Cash' AS PaymentMethod,
		Notes,
		p_CurrentDate AS created_at,
		'System Imported' AS CreatedBy
	FROM tblTempPaymentImportExport tmpp
	INNER JOIN NeonRMDev.tblAccount a 
	ON a.CompanyID = tmpp.CompanyID
	AND (tmpp.AccountNumber = a.Number OR tmpp.AccountNumber = a.AccountName) AND a.CurrencyId > 0
	WHERE tmpp.ProcessID= p_ProcessID ;

	SELECT AccountNumber AS `Account Number` ,Amount,PaymentDate AS `Payment Date` ,PaymentType AS `Payment Type`,TransactionID AS `Transaction ID`,`Action`
	FROM
	(

		SELECT 
			tmpp.* ,
			'Imported' AS `Action`
		FROM tblTempPaymentImportExport tmpp
		INNER JOIN NeonRMDev.tblAccount a 
		ON a.CompanyID = tmpp.CompanyID 
		AND (tmpp.AccountNumber = a.Number OR tmpp.AccountNumber = a.AccountName) AND a.CurrencyId IS NOT NULL
		WHERE tmpp.ProcessID= p_ProcessID

		UNION

		SELECT
			tmpp.* ,
			CONCAT('Skipped (',IF(a.AccountID,"Currency is not setup against Account" ,"Account doesn't exists in System" ),' )') as `Action`
		FROM tblTempPaymentImportExport tmpp
		LEFT JOIN NeonRMDev.tblAccount a 
		ON a.CompanyID = tmpp.CompanyID
		AND (tmpp.AccountNumber = a.Number OR tmpp.AccountNumber = a.AccountName)
		WHERE tmpp.ProcessID= p_ProcessID AND ( a.AccountID IS NULL OR a.CurrencyId IS NULL)

	) tmp;

	DELETE FROM tblTempPaymentImportExport WHERE ProcessID = p_ProcessID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END