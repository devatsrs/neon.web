CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_linkCDR`(
	IN `p_ProcessID` INT,
	IN `p_UniqueID` VARCHAR(50)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;

	SET @stmt = CONCAT('
	INSERT INTO   tblTempCallDetail_1_',p_UniqueID,' (
		GCID1,
		CID,
		UsageHeaderID,
		CompanyGatewayID1,
		GatewayAccountPKID,
		AccountID,
		FailCall,
		ProcessID
	)
	SELECT 
		ID,
		UsageDetailID,
		tblUsageDetails.UsageHeaderID,
		CompanyGatewayID,
		GatewayAccountPKID,
		AccountID,
		1,
		ProcessID
	FROM tblUsageDetails
	INNER JOIN tblUsageHeader
		ON  tblUsageDetails.UsageHeaderID = tblUsageHeader.UsageHeaderID
	WHERE ProcessID = "' , p_ProcessID , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	INSERT INTO   tblTempCallDetail_1_',p_UniqueID,' (
		GCID1,
		CID,
		UsageHeaderID,
		CompanyGatewayID1,
		GatewayAccountPKID,
		AccountID,
		FailCall,
		ProcessID
	)
	SELECT 
		ID,
		UsageDetailFailedCallID,
		tblUsageDetailFailedCall.UsageHeaderID,
		CompanyGatewayID,
		GatewayAccountPKID,
		AccountID,
		2,
		ProcessID
	FROM tblUsageDetailFailedCall
	INNER JOIN tblUsageHeader
		ON  tblUsageDetailFailedCall.UsageHeaderID = tblUsageHeader.UsageHeaderID
	WHERE ProcessID = "' , p_ProcessID , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	INSERT INTO   tblTempCallDetail_2_',p_UniqueID,' (
		GCID2,
		VCID,
		VendorCDRHeaderID,
		CompanyGatewayID2,
		GatewayVAccountPKID,
		VAccountID,
		FailCallV,
		ProcessID
	)
	SELECT 
		ID,
		VendorCDRID,
		tblVendorCDR.VendorCDRHeaderID,
		CompanyGatewayID,
		GatewayAccountPKID,
		AccountID,
		1,
		ProcessID
	FROM tblVendorCDR
	INNER JOIN tblVendorCDRHeader
		ON  tblVendorCDR.VendorCDRHeaderID = tblVendorCDRHeader.VendorCDRHeaderID
	WHERE ProcessID = "' , p_ProcessID , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	INSERT INTO   tblTempCallDetail_2_',p_UniqueID,' (
		GCID2,
		VCID,
		VendorCDRHeaderID,
		CompanyGatewayID2,
		GatewayVAccountPKID,
		VAccountID,
		FailCallV,
		ProcessID
	)
	SELECT 
		ID,
		VendorCDRFailedID,
		tblVendorCDRFailed.VendorCDRHeaderID,
		CompanyGatewayID,
		GatewayAccountPKID,
		AccountID,
		2,
		ProcessID
	FROM tblVendorCDRFailed
	INNER JOIN tblVendorCDRHeader
		ON  tblVendorCDRFailed.VendorCDRHeaderID = tblVendorCDRHeader.VendorCDRHeaderID
	WHERE ProcessID = "' , p_ProcessID , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	INSERT INTO   tblCallDetail (
		GCID,
		CID,
		VCID,
		UsageHeaderID,
		VendorCDRHeaderID,
		CompanyGatewayID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		FailCall,
		FailCallV
	)
	SELECT 
		c.GCID1,
		CID,
		VCID,
		UsageHeaderID,
		VendorCDRHeaderID,
		c.CompanyGatewayID1,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		FailCall,
		FailCallV
	FROM tblTempCallDetail_1_',p_UniqueID,' c
	INNER JOIN tblTempCallDetail_2_',p_UniqueID,' v
		ON c.GCID1 = v.GCID2
		AND c.CompanyGatewayID1 = v.CompanyGatewayID2
		AND c.ProcessID = v.ProcessID
	WHERE c.ProcessID = "' , p_ProcessID , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	INSERT INTO   tblCallDetail (
		GCID,
		CID,
		VCID,
		UsageHeaderID,
		VendorCDRHeaderID,
		CompanyGatewayID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		FailCall,
		FailCallV
	)
	SELECT 
		c.GCID1,
		CID,
		VCID,
		UsageHeaderID,
		VendorCDRHeaderID,
		c.CompanyGatewayID1,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		FailCall,
		FailCallV
	FROM tblTempCallDetail_1_',p_UniqueID,' c
	LEFT JOIN tblTempCallDetail_2_',p_UniqueID,' v
		ON c.GCID1 = v.GCID2
		AND c.CompanyGatewayID1 = v.CompanyGatewayID2
		AND c.ProcessID = v.ProcessID
	WHERE c.ProcessID = "' , p_ProcessID , '"
		AND v.VCID IS NULL;
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	INSERT INTO   tblCallDetail (
		GCID,
		CID,
		VCID,
		UsageHeaderID,
		VendorCDRHeaderID,
		CompanyGatewayID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		FailCall,
		FailCallV
	)
	SELECT 
		v.GCID2,
		CID,
		VCID,
		UsageHeaderID,
		VendorCDRHeaderID,
		v.CompanyGatewayID2,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		FailCall,
		FailCallV
	FROM tblTempCallDetail_2_',p_UniqueID,' v
	LEFT JOIN tblTempCallDetail_1_',p_UniqueID,' c
		ON c.GCID1 = v.GCID2
		AND c.CompanyGatewayID1 = v.CompanyGatewayID2
		AND c.ProcessID = v.ProcessID
	WHERE v.ProcessID = "' , p_ProcessID , '"
		AND c.CID IS NULL;
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END