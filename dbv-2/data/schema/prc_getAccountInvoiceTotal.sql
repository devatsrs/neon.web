CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountInvoiceTotal`(IN `p_AccountID` INT, IN `p_CompanyID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_checkDuplicate` INT, IN `p_InvoiceDetailID` INT)
BEGIN 
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	
	SELECT BillingTime INTO v_BillingTime_
	FROM Ratemanagement3.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_); 
	
	IF p_checkDuplicate = 1 THEN
	
		SELECT COUNT(inv.InvoiceID) INTO v_InvoiceCount_
		FROM tblInvoice inv
		LEFT JOIN tblInvoiceDetail invd ON invd.InvoiceID = inv.InvoiceID
		WHERE inv.CompanyID = p_CompanyID AND (p_InvoiceDetailID = 0 OR (p_InvoiceDetailID > 0 AND invd.InvoiceDetailID != p_InvoiceDetailID)) AND AccountID = p_AccountID AND 
		(
		 (p_StartDate BETWEEN invd.StartDate AND invd.EndDate) OR
		 (p_EndDate BETWEEN invd.StartDate AND invd.EndDate) OR
		 (invd.StartDate BETWEEN p_StartDate AND p_EndDate) 
		) AND inv.InvoiceStatus != 'cancel'; 
		
		IF v_InvoiceCount_ = 0 THEN
		
			SELECT IFNULL(CAST(SUM(cost) AS DECIMAL(18,8)), 0) AS TotalCharges
			FROM tmp_tblUsageDetails_ ud; 
		ELSE
			SELECT 0 AS TotalCharges; 
		END IF; 
	
	ELSE
		SELECT IFNULL(CAST(SUM(cost) AS DECIMAL(18,8)), 0) AS TotalCharges
		FROM tmp_tblUsageDetails_ ud; 
	END IF; 
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END