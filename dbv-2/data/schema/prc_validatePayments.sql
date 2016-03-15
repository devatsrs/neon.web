CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_validatePayments`(IN `p_CompanyID` INT, IN `p_ProcessID` VARCHAR(50))
BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
SET SESSION group_concat_max_len=5000;


	DROP TEMPORARY TABLE IF EXISTS tmp_error_;
	CREATE TEMPORARY TABLE tmp_error_(
		`ErrorMessage` VARCHAR(500)
	);

	INSERT INTO tmp_error_
	SELECT distinct CONCAT('Duplicate payment in file - Account: ',IFNULL(ac.AccountName,''),' Action: ' ,IFNULL(pt.PaymentType,''),' Payment Date: ',IFNULL(pt.PaymentDate,''),' Amount: ',pt.Amount) as ErrorMessage  FROM tblTempPayment pt
	INNER JOIN Ratemanagement3.tblAccount ac on ac.AccountID = pt.AccountID
	INNER JOIN tblTempPayment tp on tp.ProcessID = pt.ProcessID
		AND tp.AccountID = pt.AccountID
		AND tp.PaymentDate = pt.PaymentDate
		AND tp.Amount = pt.Amount
		AND tp.PaymentType = pt.PaymentType
		AND tp.PaymentMethod = pt.PaymentMethod
	WHERE pt.CompanyID = p_CompanyID
		AND pt.ProcessID = p_ProcessID
 	GROUP BY pt.InvoiceNo,pt.AccountID,pt.PaymentDate,pt.PaymentType,pt.PaymentMethod,pt.Amount having count(*)>=2;
 	
	INSERT INTO tmp_error_	
	SELECT DISTINCT
		CONCAT('Duplicate payment in system - Account: ',IFNULL(ac.AccountName,''),' Action: ' ,IFNULL(pt.PaymentType,''),' Payment Date: ',IFNULL(pt.PaymentDate,''),' Amount: ',pt.Amount) as ErrorMessage
	FROM tblTempPayment pt
	INNER JOIN Ratemanagement3.tblAccount ac on ac.AccountID = pt.AccountID
	INNER JOIN tblPayment p on p.CompanyID = pt.CompanyID 
		AND p.AccountID = pt.AccountID
		AND p.PaymentDate = pt.PaymentDate
		AND p.Amount = pt.Amount
		AND p.PaymentType = pt.PaymentType
		AND p.Recall = 0
	WHERE pt.CompanyID = p_CompanyID
		AND pt.ProcessID = p_ProcessID;
	
	INSERT INTO tmp_error_	
	SELECT DISTINCT
		CONCAT('Future payment in Account: ',IFNULL(ac.AccountName,''),' Action: ' ,IFNULL(pt.PaymentType,''),' Payment Date: ',IFNULL(pt.PaymentDate,''),' Amount: ',pt.Amount) as ErrorMessage
	FROM tblTempPayment pt
	INNER JOIN Ratemanagement3.tblAccount ac on ac.AccountID = pt.AccountID
	WHERE pt.CompanyID = p_CompanyID
		AND pt.PaymentDate > NOW()
		AND pt.ProcessID = p_ProcessID;
	
	
	IF (SELECT COUNT(*) FROM tmp_error_) > 0
	THEN
	SELECT GROUP_CONCAT(ErrorMessage SEPARATOR "\r\n" ) as ErrorMessage FROM	tmp_error_;
		
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END