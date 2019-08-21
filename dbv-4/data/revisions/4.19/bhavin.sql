Use `StagingReport`;

ALTER TABLE `tmp_UsageSummaryLive`
	ADD COLUMN `extension` VARCHAR(50) NULL DEFAULT NULL AFTER `TotalCost`,
	ADD COLUMN `pincode` VARCHAR(50) NULL DEFAULT NULL AFTER `extension`;	
	
ALTER TABLE `tblUsageSummaryDayLive`
	ADD COLUMN `extension` VARCHAR(50) NULL DEFAULT NULL AFTER `TotalCost`,
	ADD COLUMN `pincode` VARCHAR(50) NULL DEFAULT NULL AFTER `extension`;
	
ALTER TABLE `tblUsageSummaryHourLive`
	ADD COLUMN `extension` VARCHAR(50) NULL DEFAULT NULL AFTER `TotalCost`,
	ADD COLUMN `pincode` VARCHAR(50) NULL DEFAULT NULL AFTER `extension`;
	
ALTER TABLE `tmp_UsageSummary`
	ADD COLUMN `extension` VARCHAR(50) NULL DEFAULT NULL AFTER `TotalCost`,
	ADD COLUMN `pincode` VARCHAR(50) NULL DEFAULT NULL AFTER `extension`;
	
ALTER TABLE `tblUsageSummaryDay`
	ADD COLUMN `extension` VARCHAR(50) NULL DEFAULT NULL AFTER `TotalCost`,
	ADD COLUMN `pincode` VARCHAR(50) NULL DEFAULT NULL AFTER `extension`;
	
ALTER TABLE `tblUsageSummaryHour`
	ADD COLUMN `extension` VARCHAR(50) NULL DEFAULT NULL AFTER `TotalCost`,
	ADD COLUMN `pincode` VARCHAR(50) NULL DEFAULT NULL AFTER `extension`;

	
DROP PROCEDURE IF EXISTS `prc_generateSummary`;
DELIMITER //
CREATE PROCEDURE `prc_generateSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)
)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_UniqueID);
	
	CALL fngetDefaultCodes(p_CompanyID); 
	
	
	CALL fnUpdateCustomerLink(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);

	DELETE FROM tmp_UsageSummary WHERE CompanyID = p_CompanyID;

	SET @stmt = CONCAT('
	INSERT INTO tmp_UsageSummary(
		DateID,
		TimeID,
		CompanyID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		d.DateID,
		t.TimeID,
		a.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ud.GatewayAccountPKID,
		ud.GatewayVAccountPKID,
		ud.AccountID,
		ud.VAccountID,
		ud.trunk,
		ud.area_prefix,
		ud.userfield,
		ud.extension,
		ud.pincode,
		COALESCE(SUM(ud.cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCost ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblUsageDetailsReport_',p_UniqueID,' ud  
	INNER JOIN Ratemanagement3.tblAccount a
		ON a.AccountID = ud.AccountID
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	WHERE a.CompanyID = ',p_CompanyID,'
		AND ud.AccountID IS NOT NULL
	GROUP BY d.DateID,t.TimeID,ud.CompanyID,ud.CompanyGatewayID,ud.ServiceID,ud.GatewayAccountPKID,ud.GatewayVAccountPKID,ud.AccountID,ud.VAccountID,ud.area_prefix,ud.trunk,ud.userfield,ud.extension,ud.pincode;
	');


	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	UPDATE tmp_UsageSummary 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_UsageSummary.CountryID =code.CountryID
	WHERE tmp_UsageSummary.CompanyID = p_CompanyID AND code.CountryID > 0;

	START TRANSACTION;
	
	DELETE us FROM tblUsageSummaryDay us 
	INNER JOIN tblHeader sh ON us.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;
	
	DELETE usd FROM tblUsageSummaryHour usd
	INNER JOIN tblHeader sh ON usd.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;
	
	DELETE h FROM tblHeader h 
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummary)u
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;
	
	INSERT INTO tblHeader (
		DateID,
		CompanyID,
		AccountID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		DateID,
		CompanyID,
		AccountID,
		SUM(TotalCharges) as TotalCharges,
		SUM(TotalCost) as TotalCost,
		SUM(TotalBilledDuration) as TotalBilledDuration,
		SUM(TotalDuration) as TotalDuration,
		SUM(NoOfCalls) as NoOfCalls,
		SUM(NoOfFailCalls) as NoOfFailCalls
	FROM tmp_UsageSummary 
	WHERE CompanyID = p_CompanyID
	GROUP BY DateID,CompanyID,AccountID;
	
	DELETE FROM tmp_SummaryHeader WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryHeader (HeaderID,DateID,CompanyID,AccountID)
	SELECT 
		sh.HeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID
	FROM tblHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummary)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	INSERT INTO tblUsageSummaryDay (
		HeaderID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		CountryID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT
		sh.HeaderID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		CountryID,
		SUM(us.TotalCharges),
		SUM(us.TotalCost),
		SUM(us.TotalBilledDuration),
		SUM(us.TotalDuration),
		SUM(us.NoOfCalls),
		SUM(us.NoOfFailCalls)
	FROM tmp_SummaryHeader sh
	INNER JOIN tmp_UsageSummary us FORCE INDEX (Unique_key)	 
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.AccountID = sh.AccountID
	WHERE us.CompanyID = p_CompanyID
	GROUP BY us.DateID,us.CompanyID,us.CompanyGatewayID,us.ServiceID,us.GatewayAccountPKID,us.GatewayVAccountPKID,us.AccountID,us.VAccountID,us.AreaPrefix,us.Trunk,us.CountryID,sh.HeaderID,us.userfield,us.extension,us.pincode;
	
	INSERT INTO tblUsageSummaryHour (
		HeaderID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		CountryID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls	
	)
	SELECT 
		sh.HeaderID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		CountryID,
		us.TotalCharges,
		us.TotalCost,
		us.TotalBilledDuration,
		us.TotalDuration,
		us.NoOfCalls,
		us.NoOfFailCalls
	FROM tmp_SummaryHeader sh
	INNER JOIN tmp_UsageSummary us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.AccountID = sh.AccountID
	WHERE us.CompanyID = p_CompanyID;
	
	CALL fnDistinctList(p_CompanyID);

	COMMIT;
	
 	DELETE FROM tmp_UsageSummary WHERE CompanyID = p_CompanyID;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_generateSummaryLive`;
DELIMITER //
CREATE PROCEDURE `prc_generateSummaryLive`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)
)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_UniqueID);
	
	CALL fngetDefaultCodes(p_CompanyID); 
	CALL fnGetUsageForSummary(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);
	CALL fnGetVendorUsageForSummary(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);
	CALL fnUpdateCustomerLink(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);
	CALL fnUpdateVendorLink(p_CompanyID,p_StartDate,p_EndDate,p_UniqueID);

	DELETE FROM tmp_UsageSummaryLive WHERE CompanyID = p_CompanyID;

	SET @stmt = CONCAT('
	INSERT INTO tmp_UsageSummaryLive(
		DateID,
		TimeID,
		CompanyID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		AccountID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		d.DateID,
		t.TimeID,
		a.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ud.GatewayAccountPKID,
		ud.GatewayVAccountPKID,
		ud.AccountID,
		ud.VAccountID,
		ud.trunk,
		ud.area_prefix,
		ud.userfield,
		ud.extension,
		ud.pincode,
		COALESCE(SUM(ud.cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCost ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblUsageDetailsReport_',p_UniqueID,' ud  
	INNER JOIN Ratemanagement3.tblAccount a
		ON a.AccountID = ud.AccountID	
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	WHERE a.CompanyID = ',p_CompanyID,'
		AND ud.AccountID IS NOT NULL
	GROUP BY d.DateID,t.TimeID,ud.CompanyID,ud.CompanyGatewayID,ud.ServiceID,ud.GatewayAccountPKID,ud.GatewayVAccountPKID,ud.AccountID,ud.VAccountID,ud.area_prefix,ud.trunk,ud.userfield,ud.extension,ud.pincode;
	');


	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	UPDATE tmp_UsageSummaryLive
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_UsageSummaryLive.CountryID =code.CountryID
	WHERE tmp_UsageSummaryLive.CompanyID = p_CompanyID AND code.CountryID > 0;

	START TRANSACTION;

	DELETE us FROM tblUsageSummaryDayLive us 
	INNER JOIN tblHeader sh ON us.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;

	DELETE usd FROM tblUsageSummaryHourLive usd
	INNER JOIN tblHeader sh ON usd.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;

	DELETE h FROM tblHeader h 
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummaryLive)u
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;

	INSERT INTO tblHeader (
		DateID,
		CompanyID,
		AccountID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		DateID,
		CompanyID,
		AccountID,
		SUM(TotalCharges) as TotalCharges,
		SUM(TotalCost) as TotalCost,
		SUM(TotalBilledDuration) as TotalBilledDuration,
		SUM(TotalDuration) as TotalDuration,
		SUM(NoOfCalls) as NoOfCalls,
		SUM(NoOfFailCalls) as NoOfFailCalls
	FROM tmp_UsageSummaryLive 
	WHERE CompanyID = p_CompanyID
	GROUP BY DateID,CompanyID,AccountID;

	DELETE FROM tmp_SummaryHeaderLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryHeaderLive (HeaderID,DateID,CompanyID,AccountID)
	SELECT 
		sh.HeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID
	FROM tblHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	INSERT INTO tblUsageSummaryDayLive (
		HeaderID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		CountryID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT
		sh.HeaderID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		CountryID,
		SUM(us.TotalCharges),
		SUM(us.TotalCost),
		SUM(us.TotalBilledDuration),
		SUM(us.TotalDuration),
		SUM(us.NoOfCalls),
		SUM(us.NoOfFailCalls)
	FROM tmp_SummaryHeaderLive sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.AccountID = sh.AccountID
	WHERE us.CompanyID = p_CompanyID
	GROUP BY us.DateID,us.CompanyID,us.CompanyGatewayID,us.ServiceID,us.GatewayAccountPKID,us.GatewayVAccountPKID,us.AccountID,us.VAccountID,us.AreaPrefix,us.Trunk,us.CountryID,sh.HeaderID,us.userfield,us.extension,us.pincode;

	INSERT INTO tblUsageSummaryHourLive (
		HeaderID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		CountryID,
		TotalCharges,
		TotalCost,
		TotalBilledDuration,
		TotalDuration,
		NoOfCalls,
		NoOfFailCalls
	)
	SELECT 
		sh.HeaderID,
		TimeID,
		CompanyGatewayID,
		ServiceID,
		GatewayAccountPKID,
		GatewayVAccountPKID,
		VAccountID,
		Trunk,
		AreaPrefix,
		userfield,
		extension,
		pincode,
		CountryID,
		us.TotalCharges,
		us.TotalCost,
		us.TotalBilledDuration,
		us.TotalDuration,
		us.NoOfCalls,
		us.NoOfFailCalls
	FROM tmp_SummaryHeaderLive sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
		ON  us.DateID = sh.DateID
		AND us.CompanyID = sh.CompanyID
		AND us.AccountID = sh.AccountID
	WHERE us.CompanyID = p_CompanyID;

	COMMIT;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `fnUsageSummary`;
DELIMITER //
CREATE PROCEDURE `fnUsageSummary`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT ,
	IN `p_isAdmin` INT,
	IN `p_Detail` INT,
	IN `p_ResellerID` INT,
	IN `p_tag` VARCHAR(250)
)
BEGIN
	DECLARE v_TimeId_ INT;
	DECLARE v_raccountids TEXT;
	SET v_raccountids ='';
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM Ratemanagement3.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM Ratemanagement3.tblReseller WHERE ResellerID=p_ResellerID;
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;
	

	IF DATEDIFF(p_EndDate,p_StartDate) > 31 AND p_Detail = 2
	THEN
		SET p_Detail = 1;
	END IF;

	IF p_Detail = 1 
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`CompanyID` INT(11) NOT NULL,
				`AccountID` INT(11) NOT NULL,
				`CompanyGatewayID` INT(11) NOT NULL,
				`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`userfield` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',	
				`extension` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`pincode` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',			
				`CountryID` INT(11) NULL DEFAULT NULL,
				`TotalCharges` DOUBLE NULL DEFAULT NULL,
				`TotalCost` DOUBLE NULL DEFAULT NULL,
				`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
				`TotalDuration` INT(11) NULL DEFAULT NULL,
				`NoOfCalls` INT(11) NULL DEFAULT NULL,
				`NoOfFailCalls` INT(11) NULL DEFAULT NULL,
				`AccountName` varchar(100),
				INDEX `tblUsageSummary_dim_date` (`DateID`)
		);
		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.AccountID,
			us.CompanyGatewayID,
			us.Trunk,
			us.AreaPrefix,
			us.userfield,
			us.extension,
			us.pincode,
			us.CountryID,
			us.TotalCharges,
			us.TotalCost,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryDay  us
			ON us.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR us.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
		AND (p_CDRType = '' OR us.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));
		

		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.AccountID,
			us.CompanyGatewayID,
			us.Trunk,
			us.AreaPrefix,
			us.userfield,
			us.extension,
			us.pincode,
			us.CountryID,
			us.TotalCharges,
			us.TotalCost,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryDayLive  us
			ON us.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR us.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
		AND (p_CDRType = '' OR us.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

	END IF;

	IF p_Detail = 2
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`TimeID` INT(11) NOT NULL,
				`CompanyID` INT(11) NOT NULL,
				`AccountID` INT(11) NOT NULL,
				`CompanyGatewayID` INT(11) NOT NULL,
				`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`userfield` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',	
				`extension` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`pincode` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',						
				`CountryID` INT(11) NULL DEFAULT NULL,
				`TotalCharges` DOUBLE NULL DEFAULT NULL,
				`TotalCost` DOUBLE NULL DEFAULT NULL,
				`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
				`TotalDuration` INT(11) NULL DEFAULT NULL,
				`NoOfCalls` INT(11) NULL DEFAULT NULL,
				`NoOfFailCalls` INT(11) NULL DEFAULT NULL,
				`AccountName` varchar(100),
				INDEX `tblUsageSummary_dim_date` (`DateID`)
		);

		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
			sh.CompanyID,
			sh.AccountID,
			usd.CompanyGatewayID,
			usd.Trunk,
			usd.AreaPrefix,
			usd.userfield,
			usd.extension,
			usd.pincode,
			usd.CountryID,
			usd.TotalCharges,
			usd.TotalCost,
			usd.TotalBilledDuration,
			usd.TotalDuration,
			usd.NoOfCalls,
			usd.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryHour  usd
			ON usd.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN tblDimTime dt
			ON dt.TimeID = usd.TimeID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR usd.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR usd.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR usd.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR usd.CountryID = p_CountryID)
		AND (p_CDRType = '' OR usd.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
			sh.CompanyID,
			sh.AccountID,
			usd.CompanyGatewayID,
			usd.Trunk,
			usd.AreaPrefix,
			usd.userfield,
			usd.extension,
			usd.pincode,
			usd.CountryID,
			usd.TotalCharges,
			usd.TotalCost,
			usd.TotalBilledDuration,
			usd.TotalDuration,
			usd.NoOfCalls,
			usd.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryHourLive  usd
			ON usd.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN tblDimTime dt
			ON dt.TimeID = usd.TimeID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR usd.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR usd.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR usd.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR usd.CountryID = p_CountryID)
		AND (p_CDRType = '' OR usd.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0)
		AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));

	END IF;

END//
DELIMITER ;	

DROP PROCEDURE IF EXISTS `prc_getExtensionReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getExtensionReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(250)
)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID,p_tag);
	
	IF p_isExport = 0
	THEN
		
	SELECT extension ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,	
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageSummary_ us
	WHERE extension IS NOT NULL
	GROUP BY extension   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'extensionDESC') THEN extension
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'extensionASC') THEN extension
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT extension ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE extension IS NOT NULL
		GROUP BY extension
	)tbl;

	
	END IF;
	
	
	IF p_isExport = 1
	THEN
		SELECT
			extension,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		WHERE extension IS NOT NULL
		GROUP BY extension;
	END IF;
	
	
	
	IF p_isExport = 2
	THEN
	
			
			
		SELECT extension as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.extension IS NOT NULL
		GROUP BY extension HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
			
			
		SELECT extension as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.extension IS NOT NULL
		GROUP BY extension HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
			
			
		SELECT extension as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.extension IS NOT NULL
		GROUP BY extension HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;