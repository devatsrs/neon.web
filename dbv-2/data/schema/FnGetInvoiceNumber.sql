CREATE DEFINER=`root`@`localhost` FUNCTION `FnGetInvoiceNumber`(
	`p_account_id` INT,
	`p_BillingClassID` INT
) RETURNS int(11)
    NO SQL
    DETERMINISTIC
    COMMENT 'Return Next Invoice Number'
BEGIN
DECLARE v_LastInv VARCHAR(50);
DECLARE v_FoundVal INT(11);
DECLARE v_InvoiceTemplateID INT(11);

SET v_InvoiceTemplateID = CASE WHEN p_BillingClassID=0 THEN (SELECT b.InvoiceTemplateID FROM NeonRMDev.tblAccountBilling ab INNER JOIN NeonRMDev.tblBillingClass b ON b.BillingClassID = ab.BillingClassID WHERE AccountID = p_account_id AND ServiceID = 0) ELSE (SELECT b.InvoiceTemplateID FROM  NeonRMDev.tblBillingClass b WHERE b.BillingClassID = p_BillingClassID) END;

SELECT LastInvoiceNumber INTO v_LastInv FROM tblInvoiceTemplate WHERE InvoiceTemplateID =v_InvoiceTemplateID;

-- select count(*) as total_res from tblInvoice where FnGetIntegerString(InvoiceNumber) = '64123';

set v_FoundVal = (select count(*) as total_res from tblInvoice where InvoiceNumber=v_LastInv);
IF v_FoundVal>=1 then
WHILE v_FoundVal>0 DO
	set v_LastInv = v_LastInv+1;
	set v_FoundVal = (select count(*) as total_res from tblInvoice where InvoiceNumber=v_LastInv);
END WHILE;
END IF;

return v_LastInv;
END