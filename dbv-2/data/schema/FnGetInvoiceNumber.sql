CREATE DEFINER=`root`@`localhost` FUNCTION `FnGetInvoiceNumber`(`p_account_id` INT) RETURNS int(11)
    NO SQL
    DETERMINISTIC
    COMMENT 'Return Next Invoice Number'
BEGIN
DECLARE lastin VARCHAR(50);
DECLARE found_val INT(11);

set lastin = (select LastInvoiceNumber from tblInvoiceTemplate where InvoiceTemplateID = (select InvoiceTemplateID from NeonRMDev.tblAccountBilling where AccountID = p_account_id));

set found_val = (select count(*) as total_res from tblInvoice where FnGetIntegerString(InvoiceNumber)=lastin);
IF found_val>=1 then
WHILE found_val>0 DO
	set lastin = lastin+1;
	set found_val = (select count(*) as total_res from tblInvoice where FnGetIntegerString(InvoiceNumber)=lastin);
END WHILE;
END IF;

return lastin;
END