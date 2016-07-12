CREATE DEFINER=`root`@`localhost` FUNCTION `fngetLastInvoiceDate`(`p_AccountID` INT) RETURNS date
BEGIN
	
	DECLARE v_LastInvoiceDate_ DATE;
	
	SELECT 
		CASE WHEN LastInvoiceDate IS NOT NULL AND LastInvoiceDate <> '' 
		THEN 
			DATE_FORMAT(LastInvoiceDate,'%Y-%m-%d')
		ELSE 
			CASE WHEN BillingStartDate IS NOT NULL AND BillingStartDate <> ''
			THEN
				DATE_FORMAT(BillingStartDate,'%Y-%m-%d')
			ELSE DATE_FORMAT(created_at,'%Y-%m-%d')
			END 
		END
		INTO v_LastInvoiceDate_ 
	FROM NeonRMDev.tblAccount 
	WHERE AccountID = p_AccountID;
	
	RETURN v_LastInvoiceDate_;
	
END