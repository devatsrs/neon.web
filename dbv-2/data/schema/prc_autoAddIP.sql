CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_autoAddIP`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT
)
BEGIN

	INSERT IGNORE INTO NeonRMDev.tblTempRateLog (
		CompanyID,
		CompanyGatewayID,
		MessageType,
		Message,
		RateDate,
		created_at
	)
	SELECT 
		ga.CompanyID,
		ga.CompanyGatewayID,
		4,
		CONCAT('Account: ',ga.AccountName,' - IP: ',GROUP_CONCAT(ga.AccountIP)),
		DATE(NOW()),
		NOW() 
	FROM NeonBillingDev.tblGatewayAccount ga
	INNER JOIN NeonRMDev.tblAccount a 
		ON a.AccountName = ga.AccountName
	WHERE  ga.CompanyID = p_CompanyID 
		AND ga.CompanyGatewayID = p_CompanyGatewayID
		AND ga.AccountID IS NULL 
		AND ga.AccountName <> ''
		AND ga.AccountIP <> ''
		AND ga.IsVendor IS NULL
	GROUP BY ga.CompanyID,ga.CompanyGatewayID,ga.AccountID,ga.AccountName,ga.ServiceID;

	/* update customer ips */
	UPDATE NeonRMDev.tblAccountAuthenticate aa
	INNER JOIN (
		SELECT 
			ga.CompanyID,
			a.AccountID,
			CONCAT(IFNULL(MAX(aa.CustomerAuthValue),''),IF(MAX(aa.CustomerAuthValue) IS NULL,'',','),GROUP_CONCAT(ga.AccountIP)) AS CustomerAuthValue 
		FROM NeonBillingDev.tblGatewayAccount ga
		INNER JOIN NeonRMDev.tblAccount a 
			ON a.AccountName = ga.AccountName
		INNER JOIN NeonRMDev.tblAccountAuthenticate aa 
			ON a.AccountID = aa.AccountID
		WHERE  ga.CompanyID = p_CompanyID 
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.AccountID IS NULL 
			AND ga.AccountName <> ''
			AND ga.AccountIP <> ''
			AND ga.IsVendor IS NULL
			AND ( 
					 ( FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) IS NULL OR FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) = 0)
				AND ( FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) IS NULL OR FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) = 0)
				 )
		GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID
	) TBl
	ON TBl.AccountID = aa.AccountID
	SET aa.CustomerAuthValue = TBl.CustomerAuthValue;

	/* update vendor ips */
	UPDATE NeonRMDev.tblAccountAuthenticate aa
	INNER JOIN (
		SELECT
			ga.CompanyID,
			a.AccountID,
			CONCAT(IFNULL(MAX(aa.VendorAuthValue),''),IF(MAX(aa.VendorAuthValue) IS NULL,'',','),GROUP_CONCAT(ga.AccountIP)) AS VendorAuthValue 
		FROM NeonBillingDev.tblGatewayAccount ga
		INNER JOIN NeonRMDev.tblAccount a 
			ON a.AccountName = ga.AccountName
		INNER JOIN NeonRMDev.tblAccountAuthenticate aa 
			ON a.AccountID = aa.AccountID
		WHERE  ga.CompanyID = p_CompanyID 
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.AccountID IS NULL 
			AND ga.AccountName <> ''
			AND ga.AccountIP <> ''
			AND ga.IsVendor = 1
			AND ( 
					 ( FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) IS NULL OR FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) = 0)
				AND ( FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) IS NULL OR FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) = 0)
				 )
		GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID
	) TBl
	ON TBl.AccountID = aa.AccountID
	SET aa.VendorAuthValue = TBl.VendorAuthValue;

	/* insert customer ips */
	INSERT IGNORE INTO NeonRMDev.tblAccountAuthenticate (
		CompanyID,
		AccountID,
		CustomerAuthRule,
		CustomerAuthValue,
		ServiceID
	)
	SELECT 
		ga.CompanyID,
		a.AccountID,
		'IP',
		GROUP_CONCAT(ga.AccountIP),
		ga.ServiceID
	FROM NeonBillingDev.tblGatewayAccount ga
	INNER JOIN NeonRMDev.tblAccount a 
		ON a.AccountName = ga.AccountName
	LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
		ON a.AccountID = aa.AccountID
	WHERE  ga.CompanyID = p_CompanyID 
		AND ga.CompanyGatewayID = p_CompanyGatewayID
		AND ga.AccountID IS NULL 
		AND ga.AccountName <> ''
		AND ga.AccountIP <> ''
		AND ga.IsVendor IS NULL
		AND aa.AccountID IS NULL
	GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID;

	/* insert vendor ips */
	INSERT IGNORE INTO NeonRMDev.tblAccountAuthenticate (
		CompanyID,
		AccountID,
		VendorAuthRule,
		VendorAuthValue,
		ServiceID
	)
	SELECT 
		ga.CompanyID,
		a.AccountID,
		'IP',
		GROUP_CONCAT(ga.AccountIP),
		ga.ServiceID
	FROM NeonBillingDev.tblGatewayAccount ga
	INNER JOIN NeonRMDev.tblAccount a 
		ON a.AccountName = ga.AccountName
	LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
		ON a.AccountID = aa.AccountID
	WHERE  ga.CompanyID = p_CompanyID 
		AND ga.CompanyGatewayID = p_CompanyGatewayID
		AND ga.AccountID IS NULL 
		AND ga.AccountName <> ''
		AND ga.AccountIP <> ''
		AND ga.IsVendor = 1
		AND aa.AccountID IS NULL
	GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID;

END