CREATE DEFINER=`root`@`localhost` FUNCTION `FnGetInvoiceNumber`(
	`p_CompanyID` INT,
	`p_AccountID` INT,
	`p_BillingClassID` INT
) RETURNS int(11)
    NO SQL
    DETERMINISTIC
    COMMENT 'Return Next Invoice Number'
BEGIN
	DECLARE v_LastInv VARCHAR(50);
	DECLARE v_FoundVal INT(11);
	DECLARE v_InvoiceTemplateID INT(11);

	SET v_InvoiceTemplateID =
	CASE WHEN p_BillingClassID=0
	THEN (
		SELECT 
			b.InvoiceTemplateID 
		FROM Ratemanagement3.tblAccountBilling ab
		INNER JOIN Ratemanagement3.tblBillingClass b
			ON b.BillingClassID = ab.BillingClassID
		WHERE AccountID = p_AccountID AND ServiceID = 0
	)
	ELSE (
		SELECT b.InvoiceTemplateID
		FROM  Ratemanagement3.tblBillingClass b
		WHERE b.BillingClassID = p_BillingClassID
	) END;

	SELECT IF(LastInvoiceNumber=0,InvoiceStartNumber,LastInvoiceNumber) INTO v_LastInv FROM tblInvoiceTemplate WHERE InvoiceTemplateID =v_InvoiceTemplateID;

	-- select count(*) as total_res from tblInvoice where FnGetIntegerString(InvoiceNumber) = '64123';

	SET v_FoundVal = (SELECT COUNT(*) AS total_res FROM tblInvoice WHERE CompanyID = p_CompanyID AND InvoiceNumber=v_LastInv);
	IF v_FoundVal>=1 THEN
	WHILE v_FoundVal>0 DO
		SET v_LastInv = v_LastInv+1;
		SET v_FoundVal = (SELECT COUNT(*) AS total_res FROM tblInvoice WHERE CompanyID = p_CompanyID AND InvoiceNumber=v_LastInv);
	END WHILE;
	END IF;

RETURN v_LastInv;
END