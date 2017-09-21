USE `RMBilling3`;

DROP FUNCTION IF EXISTS `fnGetAutoAddIP`;
DELIMITER |
CREATE FUNCTION `fnGetAutoAddIP`(
	`p_CompanyGatewayID` INT
) RETURNS int(11)
BEGIN

	DECLARE v_AutoAddIP_ INT;

	SELECT 
		CASE WHEN REPLACE(JSON_EXTRACT(cg.Settings, '$.AutoAddIP'),'"','') > 0
		THEN
			CAST(REPLACE(JSON_EXTRACT(cg.Settings, '$.AutoAddIP'),'"','') AS UNSIGNED INTEGER)
		ELSE
			NULL
		END
	INTO v_AutoAddIP_
	FROM Ratemanagement3.tblCompanyGateway cg
	WHERE cg.CompanyGatewayID = p_CompanyGatewayID
	LIMIT 1;
	
	SET v_AutoAddIP_ = IFNULL(v_AutoAddIP_,0);

	RETURN v_AutoAddIP_;
END|
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_autoAddIP`;
DELIMITER |
CREATE PROCEDURE `prc_autoAddIP`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT
)
BEGIN
	DECLARE AutoAddIP INT;
	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL
	);
	SELECT fnGetAutoAddIP(p_CompanyGatewayID) INTO AutoAddIP;
	IF AutoAddIP = 1
	THEN
		INSERT IGNORE INTO tmp_tblTempRateLog_ (
			CompanyID,
			CompanyGatewayID,
			MessageType,
			Message,
			RateDate
		)
		SELECT 
			ga.CompanyID,
			ga.CompanyGatewayID,
			4,
			CONCAT('Account: ',ga.AccountName,' - IP: ',GROUP_CONCAT(ga.AccountIP)),
			DATE(NOW())
		FROM tblGatewayAccount ga
		INNER JOIN Ratemanagement3.tblAccount a 
			ON a.AccountName = ga.AccountName
			AND a.CompanyId = p_CompanyID
			AND a.AccountType = 1
			AND a.`Status` = 1
		WHERE  ga.CompanyID = p_CompanyID 
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.AccountID IS NULL 
			AND ga.AccountName <> ''
			AND ga.AccountIP <> ''
			AND ga.IsVendor IS NULL
		GROUP BY ga.CompanyID,ga.CompanyGatewayID,ga.AccountID,ga.AccountName,ga.ServiceID;
		
		INSERT INTO Ratemanagement3.tblTempRateLog (
			CompanyID,
			CompanyGatewayID,
			MessageType,
			Message,
			RateDate,
			SentStatus,
			created_at
		)
		SELECT
			CompanyID,
			CompanyGatewayID,
			MessageType,
			Message,
			RateDate,
			0,
			NOW()
		FROM tmp_tblTempRateLog_;
	
		/* update customer ips */
		UPDATE Ratemanagement3.tblAccountAuthenticate aa
		INNER JOIN (
			SELECT 
				ga.CompanyID,
				a.AccountID,
				CONCAT(IFNULL(MAX(aa.CustomerAuthValue),''),IF(MAX(aa.CustomerAuthValue) IS NULL,'',','),GROUP_CONCAT(ga.AccountIP)) AS CustomerAuthValue 
			FROM tblGatewayAccount ga
			INNER JOIN Ratemanagement3.tblAccount a 
				ON a.AccountName = ga.AccountName
				AND a.CompanyId = p_CompanyID
				AND a.AccountType = 1
				AND a.`Status` = 1
			INNER JOIN Ratemanagement3.tblAccountAuthenticate aa 
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
		UPDATE Ratemanagement3.tblAccountAuthenticate aa
		INNER JOIN (
			SELECT
				ga.CompanyID,
				a.AccountID,
				CONCAT(IFNULL(MAX(aa.VendorAuthValue),''),IF(MAX(aa.VendorAuthValue) IS NULL,'',','),GROUP_CONCAT(ga.AccountIP)) AS VendorAuthValue 
			FROM tblGatewayAccount ga
			INNER JOIN Ratemanagement3.tblAccount a 
				ON a.AccountName = ga.AccountName
				AND a.CompanyId = p_CompanyID
				AND a.AccountType = 1
				AND a.`Status` = 1
			INNER JOIN Ratemanagement3.tblAccountAuthenticate aa 
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
		INSERT IGNORE INTO Ratemanagement3.tblAccountAuthenticate (
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
		FROM tblGatewayAccount ga
		INNER JOIN Ratemanagement3.tblAccount a 
			ON a.AccountName = ga.AccountName
			AND a.CompanyId = p_CompanyID
			AND a.AccountType = 1
			AND a.`Status` = 1
		LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa 
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
		INSERT IGNORE INTO Ratemanagement3.tblAccountAuthenticate (
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
		FROM tblGatewayAccount ga
		INNER JOIN Ratemanagement3.tblAccount a 
			ON a.AccountName = ga.AccountName
			AND a.CompanyId = p_CompanyID
			AND a.AccountType = 1
			AND a.`Status` = 1
		LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa 
			ON a.AccountID = aa.AccountID
		WHERE  ga.CompanyID = p_CompanyID 
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.AccountID IS NULL 
			AND ga.AccountName <> ''
			AND ga.AccountIP <> ''
			AND ga.IsVendor = 1
			AND aa.AccountID IS NULL
		GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID;

	END IF;

END|
DELIMITER ;
