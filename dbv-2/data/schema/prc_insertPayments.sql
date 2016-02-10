CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertPayments`(IN `p_CompanyID` INT, IN `p_ProcessID` VARCHAR(50))
BEGIN

DECLARE v_already_existPayments int DEFAULT 0;
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
DROP TEMPORARY TABLE IF EXISTS tmp_tbltemppayment_;
	
CREATE TEMPORARY TABLE tmp_tbltemppayment_ (
	`AccountName` VARCHAR(15),
	`InvoiceNo` VARCHAR(30),
	`PaymentDate` DATETIME,
	`PaymentMethod` VARCHAR(15),
	`PaymentType` VARCHAR(15),
	`Notes` VARCHAR(50),
	`Amount` DECIMAL(18,8) NOT NULL
);

	
insert into tmp_tblTempPayment_
select ac.AccountName,pt.InvoiceNo,pt.PaymentDate,pt.PaymentMethod,pt.PaymentType,pt.Notes,pt.Amount from tblTempPayment pt
INNER JOIN Ratemanagement3.tblAccount ac on ac.AccountID = pt.AccountID
INNER JOIN tblPayment p on p.CompanyID = pt.CompanyID 
	AND p.AccountID = pt.AccountID
	AND p.PaymentDate = pt.PaymentDate
	AND p.Amount = pt.Amount
where pt.CompanyID = p_CompanyID
	AND pt.ProcessID = p_ProcessID;
	
	select count(tmp_tblTempPayment_.AccountName) as exist into v_already_existPayments from tmp_tblTempPayment_;
	
	IF v_already_existPayments > 0
	THEN
	
		select * from tmp_tblTempPayment_;
		
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END