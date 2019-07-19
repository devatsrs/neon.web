USE `speakintelligentRoutingEngine`;





DROP PROCEDURE IF EXISTS `prc_getTestDialPlan`;
DELIMITER //
CREATE PROCEDURE `prc_getTestDialPlan`(
	IN `v_AccountCurrencyID_` INT,
	IN `p_OriginationNo` VARCHAR(50),
	IN `p_DestinationNo` VARCHAR(50),
	IN `p_TimeZone` VARCHAR(50),
	IN `p_profileIDs` VARCHAR(50),
	IN `p_Location` VARCHAR(200),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_APICountry` VARCHAR(200),
	IN `p_DateAndTime` DATETIME
)
BEGIN
	DECLARE v_OffSet_ INT;
	DECLARE v_RowCount_ INT;
	DECLARE v_CaseID INT;
	DECLARE v_AccountCurrency_ VARCHAR(50);
	DECLARE v_DistinctVendorConnection_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET @@session.collation_connection='utf8_unicode_ci';
	SET @@session.character_set_results='utf8';
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRates;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRates (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		VendorID INT(11),
		Prefix VARCHAR(500),
		VendorName VARCHAR(255),
		VendorConnectionName VARCHAR(255),
		SipHeader VARCHAR(255),
		IP VARCHAR(255),
		Port VARCHAR(255),
		Username VARCHAR(255),
		Password text,
		AuthenticationMode VARCHAR(255),
		AccountCurrency VARCHAR(50),
		Rate DECIMAL(18,6),
		RoutingCategoryID INT,
		CategoryName VARCHAR(100),
		Preference INT,
		RoutingCategoryOrder INT,
		OriginationCode VARCHAR(100),
		DestinationCode VARCHAR(100),
		TimezonesID INT,
		VendorConnectionID INT(11)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRatesCount;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRatesCount (
		VendorConnectionCount VARCHAR(255)
	);

	SELECT Code INTO v_AccountCurrency_ FROM tblCurrency WHERE CurrencyId = v_AccountCurrencyID_;
	SET @rank:=0;
	SET @VendorConnectionName = '';
	SET @v_RateTypeID = 1; -- voicecall/termination
	SET @v_Vendor = 2; -- Vendor
	SET @v_Active = 1;
	SET v_DistinctVendorConnection_ = 0;

	IF p_profileIDs > 0
	THEN

		-- check against p_OriginationNo AND p_DestinationNo
		INSERT INTO tmp_VendorRates (
			Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,AccountCurrency,Rate,RoutingCategoryID,CategoryName,Preference,RoutingCategoryOrder,OriginationCode,DestinationCode,TimezonesID,VendorConnectionID
		)
		SELECT
			Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,v_AccountCurrency_ AS AccountCurrency,ROUND(Rate,6) AS Rate,RoutingCategoryID,CategoryName,Preference,RoutingCategoryOrder,OriginationCode,DestinationCode,TimezonesID,VendorConnectionID
		FROM
		(
			SELECT
				CONCAT(IFNULL(vc.CallPrefix ,""),rtr.DestinationCode) AS Prefix,  -- CallPrefix = TrunkPrefix
				IFNULL(vc.CallPrefix ,"") AS TrunkPrefix,
				IFNULL(rtr.OriginationCode ,"") AS VendorCLIPrefix,
				rtr.DestinationCode AS VendorCLDPrefix,
				vc.AccountId AS VendorID,
				a.AccountName AS VendorName,
				vc.Name AS VendorConnectionName, vc.SipHeader, vc.IP, vc.Port, vc.Username, vc.Password, vc.AuthenticationMode,rtr.TimezonesID,
				CASE WHEN IFNULL(rtr.RateCurrency,rt.CurrencyID) = @v_AccountCurrencyID_
				THEN
					rtr.Rate
				ELSE
				(
					-- Convert to base currrncy AND x by RateGenerator Exhange
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ )
					* ( rtr.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = IFNULL(rtr.RateCurrency,rt.CurrencyID) ))
				)
				END AS Rate,
				rtr.Preference,
				rpc.RoutingCategoryID,
				rpc.Order AS RoutingCategoryOrder,
				-- rp.RoutingProfileId as RoutingProfileID,
				rc.Name AS CategoryName,
				rtr.DestinationCode,
				rtr.OriginationCode,
				vc.VendorConnectionID
			FROM
				tblRoutingProfile rp
			JOIN tblRoutingProfileCategory rpc ON rp.RoutingProfileID = rpc.RoutingProfileID AND rp.RoutingProfileId = p_profileIDs -- customer routing profile id
			JOIN tblRateTableRate rtr ON rpc.RoutingCategoryID =  rtr.RoutingCategoryID
			JOIN tblRoutingCategory rc ON rpc.RoutingCategoryID =  rc.RoutingCategoryID
			JOIN tblRateTable rt ON  rtr.RateTableID = rt.RateTableId AND rt.CompanyId = rp.CompanyID
			JOIN tblVendorConnection vc ON rt.RateTableId = vc.RateTableID AND vc.CompanyId = rt.CompanyID
			JOIN tblAccount a ON vc.AccountId = a.AccountId AND a.CompanyId = vc.CompanyID
			WHERE
				rp.Status = 1 AND vc.Active = 1 AND a.Status = 1 AND a.IsVendor = 1 -- AND a.CurrencyId IS NOT NULL
				AND vc.RateTypeID = @v_RateTypeID -- voicecall/termination
				AND rt.Type = @v_RateTypeID -- voicecall/termination
				AND rt.AppliedTo = @v_Vendor -- vendor
				AND rtr.EffectiveDate <= NOW()
				AND ( rtr.EndDate IS NULL OR  rtr.EndDate > NOW() )
				AND rtr.Blocked = 0
				-- @TODO Vendor wise timzeone in future
				AND ( p_OriginationNo LIKE  CONCAT(rtr.OriginationCode,"%") OR rtr.OriginationCode IS NULL)
				AND p_DestinationNo LIKE  CONCAT(rtr.DestinationCode,"%")
				AND (p_Location = '' OR vc.Location = p_Location)
				AND ( rtr.TimezonesID = fn_updateTempCDRTimeZones(p_DateAndTime,vc.AccountId,1,a.Country,p_APICountry) OR rtr.TimezonesID = 1 )
			ORDER BY
				IFNULL(rtr.Preference ,5) DESC, Rate ASC, rpc.`Order` DESC, rtr.DestinationCode DESC, rtr.OriginationCode DESC, vc.Name
		) vendorTable;


	ELSE -- IF p_profileIDs > 0 THEN ELSE

		SET @rank:=0,@VendorConnectionName:='';
		INSERT INTO tmp_VendorRates(
			Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,AccountCurrency,Rate,RoutingCategoryID,CategoryName,Preference,RoutingCategoryOrder,OriginationCode,DestinationCode,TimezonesID,VendorConnectionID
		)
		SELECT
			Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,v_AccountCurrency_ AS AccountCurrency,ROUND(Rate,6) AS Rate,RoutingCategoryID,CategoryName,Preference,RoutingCategoryOrder,OriginationCode,DestinationCode,TimezonesID,VendorConnectionID
		FROM
		(
			SELECT
				CONCAT(IFNULL(vc.CallPrefix ,""),rtr.DestinationCode) AS Prefix,IFNULL(vc.CallPrefix ,"") AS TrunkPrefix,rtr.DestinationCode AS VendorCLDPrefix,IFNULL(rtr.OriginationCode ,"") AS VendorCLIPrefix,vc.AccountId AS VendorID,v.AccountName AS VendorName,vc.Name AS VendorConnectionName,vc.SipHeader AS SipHeader,vc.IP AS IP,vc.Port AS Port,vc.Username AS Username,vc.Password AS Password,
				vc.AuthenticationMode AS AuthenticationMode,rtr.TimezonesID, @v_AccountCurrency_ as AccountCurrency,
				CASE WHEN IFNULL(rtr.RateCurrency,rt.CurrencyID) = @v_AccountCurrencyID_
				THEN
					rtr.Rate
				ELSE
				(
					-- Convert to base currrncy AND x by RateGenerator Exhange
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ )
					* ( rtr.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = IFNULL(rtr.RateCurrency,rt.CurrencyID) ))
				)
				END AS Rate,
				rtr.Preference,
				rc.RoutingCategoryID,
				rc.Order AS RoutingCategoryOrder,
				-- rp.RoutingProfileId as RoutingProfileID,
				rc.Name AS CategoryName,
				rtr.DestinationCode,
				rtr.OriginationCode,
				vc.VendorConnectionID
			FROM
				tblVendorConnection vc
			JOIN tblAccount v ON vc.AccountId = v.AccountId
			JOIN tblRateTable rt ON vc.RateTableID = rt.RateTableId AND v.CompanyId = rt.CompanyId
			JOIN tblRateTableRate rtr ON  rtr.RateTableID = rt.RateTableId
			LEFT JOIN tblRoutingCategory rc ON rtr.RoutingCategoryID =  rc.RoutingCategoryID
			WHERE
				vc.Active = 1
				AND vc.RateTypeID = 1
				AND v.Status = 1
				AND v.IsVendor = 1
				AND v.CurrencyId IS NOT NULL
				AND EffectiveDate <= NOW()
				AND ( rtr.EndDate IS NULL OR rtr.EndDate > NOW() )
				AND rtr.Blocked = 0
				AND rt.`Type` = 1 AND rt.AppliedTo = 2
				AND (p_OriginationNo LIKE  CONCAT(rtr.OriginationCode,"%") OR rtr.OriginationCode IS NULL)
				AND p_DestinationNo LIKE  CONCAT(rtr.DestinationCode,"%")
				AND ( rtr.TimezonesID = fn_updateTempCDRTimeZones(p_DateAndTime,vc.AccountId,1,v.Country,p_APICountry) OR rtr.TimezonesID = 1 )
				AND fnGetColumnValue(p_Location,vc.Location,2) = fnGetColumnValue(p_Location,vc.Location,1)
			ORDER BY
				IFNULL(rtr.Preference ,5) DESC, Rate, rtr.DestinationCode DESC, rtr.OriginationCode DESC, vc.Name
		) vendorTable;


	END IF; -- /IF p_profileIDs > 0



	-- SELECT * FROM tmp_VendorRates;

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRates2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRates2 AS (SELECT * FROM tmp_VendorRates);
	-- delete default timezone rates if custom timezone applies to vendor connection and rate exist against that custom timezone (here custom means peak, off-peak etc)
	DELETE
		t1
	FROM
		tmp_VendorRates t1
	INNER JOIN
		tmp_VendorRates2 t2
	WHERE
		t1.VendorID = t2.VendorID AND t1.VendorConnectionID = t2.VendorConnectionID AND
		t1.DestinationCode = t2.DestinationCode AND
		((t1.OriginationCode IS NULL AND t2.OriginationCode IS NULL) OR t1.OriginationCode = t2.OriginationCode) AND
		t1.TimezonesID < t2.TimezonesID;

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRates3;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRates3 AS (SELECT * FROM tmp_VendorRates);
	-- keep only maximum matched Prefix of the same vendor connection, delete other rates
	DELETE
		t1
	FROM
		tmp_VendorRates t1
	INNER JOIN
		tmp_VendorRates3 t2
	WHERE
		t1.VendorID = t2.VendorID AND t1.VendorConnectionID = t2.VendorConnectionID
		AND (
			t1.Prefix < t2.Prefix OR
			(
				t1.Prefix = t2.Prefix AND
				(
					t1.OriginationCode < t2.OriginationCode OR (t1.OriginationCode IS NULL AND t2.OriginationCode IS NOT NULL)
				)
			)
		);


	IF p_profileIDs > 0
	THEN

		SELECT
			@rank:=@rank+1 AS Position,OriginationCode,Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,
			AuthenticationMode,v_AccountCurrency_ AS AccountCurrency,ROUND(Rate,6) AS Rate,RoutingCategoryID,CategoryName,Preference,RoutingCategoryOrder
		FROM
			tmp_VendorRates
		ORDER BY
			RoutingCategoryOrder ASC, IFNULL(Preference ,5) DESC, Rate ASC, DestinationCode DESC, OriginationCode DESC, VendorConnectionName;

	ELSE -- IF p_profileIDs > 0

		SELECT
			@rank:=@rank+1 AS Position,OriginationCode,Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,
			AuthenticationMode,v_AccountCurrency_ AS AccountCurrency,ROUND(Rate,6) AS Rate,RoutingCategoryID,CategoryName,Preference,RoutingCategoryOrder
		FROM
			tmp_VendorRates
		ORDER BY
			IFNULL(Preference ,5) DESC, Rate ASC, DestinationCode DESC, OriginationCode DESC, VendorConnectionName;

	END IF; -- /IF p_profileIDs > 0

	SELECT FOUND_ROWS() AS `totalcount`;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RoutingByAccOriDestLoc`;
DELIMITER //
CREATE PROCEDURE `prc_RoutingByAccOriDestLoc`(
	IN `p_OriginationNo` VARCHAR(200),
	IN `p_DestinationNo` VARCHAR(200),
	IN `p_DateAndTime` DATETIME,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountID` INT,
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_Location` VARCHAR(200),
	IN `p_APICountry` VARCHAR(200)
)
PRC:BEGIN

	DECLARE v_RowCount_ INT;
	DECLARE v_DistinctVendorConnection_ INT;
	DECLARE v_AccountCurrency_ VARCHAR(10);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
	CREATE TEMPORARY TABLE tmp_Error_ (
		Message LONGTEXT
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRates;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRates (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		VendorID INT(11),
		Prefix VARCHAR(500),
		VendorName VARCHAR(255),
		VendorConnectionName VARCHAR(255),
		VendorCLIPrefix VARCHAR(255),
		VendorCLDPrefix VARCHAR(255),
		TrunkPrefix VARCHAR(255),
		SipHeader VARCHAR(255),
		IP VARCHAR(255),
		Port VARCHAR(255),
		Username VARCHAR(255),
		Password TEXT,
		AuthenticationMode VARCHAR(255),
		Currency VARCHAR(50),
		Rate DECIMAL(18,6),
		RoutingCategoryID INT,
		RoutingCategoryName VARCHAR(100),
		Preference INT,
		RoutingCategoryOrder INT,
		OriginationCode VARCHAR(100),
		DestinationCode VARCHAR(100),
		TimezonesID INT,
		VendorConnectionID INT(11)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRatesCount;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRatesCount (
		VendorConnectionCount VARCHAR(255)
	);
	-- find account id
	SET @v_AccountID = 0;
	SET @v_CompanyID = 0;
	SET @v_RoutingProfileID = 0;
	SET @v_TimezoneID = 0;

	IF p_AccountID > 0 THEN

		SELECT AccountID , CompanyID INTO @v_AccountID, @v_CompanyID FROM tblAccount WHERE AccountID = p_AccountID AND Status = 1 LIMIT 1 ;

	ELSEIF p_AccountNo != '' THEN

		SELECT AccountID , CompanyID INTO @v_AccountID, @v_CompanyID FROM tblAccount WHERE `Number` = p_AccountNo AND Status = 1 LIMIT 1 ;

	ELSEIF p_AccountDynamicField != '' AND p_AccountDynamicFieldValue != '' THEN

		SELECT AccountID , CompanyID INTO @v_AccountID, @v_CompanyID FROM tblAccount WHERE CustomerID = p_AccountDynamicFieldValue AND Status = 1 LIMIT 1 ;

	END IF;




	-- Check if account id found yet
	IF (@v_AccountID IS NULL OR @v_AccountID = 0 )
	THEN

		INSERT INTO tmp_Error_ (Message) VALUES ('AccountID not found');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;

	END IF;



	-- Now find routing profile assigned to customer.

	-- First match on SELECTion code
	SELECT RoutingProfileId INTO @v_RoutingProfileID FROM tblRoutingProfile
	WHERE CompanyID = @v_CompanyID AND p_DestinationNo LIKE CONCAT(SELECTionCode,"%") AND SELECTionCode != ''
	ORDER BY LENGTH(SELECTionCode) DESC LIMIT 1;




	IF @v_RoutingProfileID = 0 THEN

		-- SELECT "2";

		/*
		-- Nothing found. Match on Trunk -

		SELECT RoutingProfileID INTO @v_RoutingProfileID
		FROM tblRoutingProfileToCustomer rpc
		INNER JOIN (

		SELECT AccountID, TrunkID FROM speakintelligentRM.tblCustomerTrunk

		WHERE CompanyID = @v_CompanyID AND UseInBilling = 1 AND `Prefix` != '' AND p_DestinationNo LIKE CONCAT(`Prefix`,"%")

		ORDER BY length(`Prefix`) DESC LIMIT 1

		) ct on rpc.AccountID = ct.AccountID AND rpc.TrunkID = ct.TrunkID

		LIMIT 1;


		*/


		-- IF FOUND_ROWS() = 0 THEN

		-- Nothing found. Match on CLI -

		SELECT RoutingProfileID INTO @v_RoutingProfileID
		FROM tblRoutingProfileToCustomer rpc
		INNER JOIN (

			SELECT AccountID, ServiceID FROM tblCLIRateTable
			WHERE CompanyID = @v_CompanyID AND  CLI = p_OriginationNo
			AND tblCLIRateTable.`Status` = 1 AND NumberEndDate >= Now() AND NumberStartDate <= Now() -- time should come FROM PHP code
			LIMIT 1

		) ct ON rpc.AccountID = ct.AccountID /* AND rpc.TrunkID = ct.TrunkID */ AND rpc.ServiceID = ct.ServiceID

		LIMIT 1;

		-- END IF;

		IF @v_RoutingProfileID = 0 THEN

			--	SELECT "3";
			-- if still not found then find RoutingProfileID FROM AccountID  tblRoutingProfileToCustomer

			SELECT RoutingProfileID INTO @v_RoutingProfileID
			FROM tblRoutingProfileToCustomer WHERE AccountID = @v_AccountID
			AND TrunkID = 0 AND ServiceID = 0
			LIMIT 1;

		END IF;


		IF @v_RoutingProfileID = 0 THEN

			-- SELECT "4";
			-- if still not found then find RoutingProfileID FROM reseller AccountID

			SELECT RoutingProfileID INTO @v_RoutingProfileID
			FROM tblRoutingProfileToCustomer rpc
			INNER JOIN  tblReseller rs ON rs.AccountID = rpc.AccountID AND rs.ChildCompanyID = @v_CompanyID
			WHERE rpc.TrunkID = 0 AND rpc.ServiceID = 0
			LIMIT 1;

		END IF;


	END IF;


	--	SELECT @v_RoutingProfileID;


	-- @TODO: move to RoutingEngine
	-- find timezoneID
	--	INSERT INTO tblgetTimezone (connect_time,disconnect_time) values (p_DateAndTime  , p_DateAndTime);
	--	CALL prc_updateTempCDRTimeZones();
	--	SELECT @v_TimezoneID , @v_getTimezoneID;
	--	SELECT TimezonesID , getTimezoneID INTO @v_TimezoneID , @v_getTimezoneID FROM tblgetTimezone WHERE connect_time = p_DateAndTime AND disconnect_time = p_DateAndTime;

	--	delete FROM tblgetTimezone WHERE getTimezoneID = @v_getTimezoneID;



	-- ############################################### ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ #################################################################################

	-- rounting records
	-- taken  FROM prc_getRoutingRecords

	-- now find vendor rates by
	-- @v_RoutingProfileID customer routing profile.
	-- @v_TimezoneID
	-- @v_AccountID


	-- customer currency ID
	SELECT Code,CurrencyID  INTO @v_AccountCurrency_ , @v_AccountCurrencyID_
	FROM tblCurrency
	WHERE CurrencyId =  (SELECT CurrencyID FROM  `tblAccount` WHERE AccountID = @v_AccountID);


	SET @v_RateTypeID = 1; -- voicecall/termination
	SET @v_Vendor = 2; -- Vendor
	SET @v_Active = 1;

	SET @rank:=0;
	SET @VendorConnectionName = '';

	-- SELECT @v_RoutingProfileID,@v_CompanyID;

	IF @v_RoutingProfileID > 0
	THEN

		-- check against p_OriginationNo AND p_DestinationNo
		INSERT INTO tmp_VendorRates(
			Prefix,VendorCLIPrefix,VendorCLDPrefix,TrunkPrefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,Currency,Rate,RoutingCategoryOrder,RoutingCategoryName,OriginationCode,DestinationCode,TimezonesID,VendorConnectionID
		)
		SELECT
			Prefix,VendorCLIPrefix,VendorCLDPrefix,TrunkPrefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,@v_AccountCurrency_ as Currency,ROUND(Rate,6) as Rate,RoutingCategoryOrder,RoutingCategoryName,OriginationCode,DestinationCode,TimezonesID,VendorConnectionID
		FROM
		(
			SELECT
				CONCAT(IFNULL(vc.CallPrefix ,""),rtr.DestinationCode) AS Prefix,  -- CallPrefix = TrunkPrefix
				IFNULL(vc.CallPrefix ,"") AS TrunkPrefix,
				IFNULL(rtr.OriginationCode ,"") AS VendorCLIPrefix,
				rtr.DestinationCode AS VendorCLDPrefix,
				vc.AccountId AS VendorID,
				a.AccountName AS VendorName,
				vc.Name AS VendorConnectionName, vc.SipHeader, vc.IP, vc.Port, vc.Username, vc.Password, vc.AuthenticationMode,rtr.TimezonesID,
				CASE WHEN IFNULL(rtr.RateCurrency,rt.CurrencyID) = @v_AccountCurrencyID_
				THEN
					rtr.Rate
				ELSE
				(
					-- Convert to base currrncy AND x by RateGenerator Exhange
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = @v_AccountCurrencyID_ )
					* ( rtr.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = IFNULL(rtr.RateCurrency,rt.CurrencyID) ))
				)
				END as Rate,
				-- rtr.Preference,
				rpc.Order as RoutingCategoryOrder,
				-- rp.RoutingProfileId as RoutingProfileID,
				rc.Name as RoutingCategoryName,rtr.DestinationCode,
				rtr.OriginationCode,
				vc.VendorConnectionID
			FROM
				tblRoutingProfile rp
			JOIN tblRoutingProfileCategory rpc ON rp.RoutingProfileID = rpc.RoutingProfileID AND rp.RoutingProfileId = @v_RoutingProfileID -- customer routing profile id
			JOIN tblRateTableRate rtr ON rpc.RoutingCategoryID =  rtr.RoutingCategoryID
			JOIN tblRoutingCategory rc ON rpc.RoutingCategoryID =  rc.RoutingCategoryID
			JOIN tblRateTable rt ON  rtr.RateTableID = rt.RateTableId AND rt.CompanyId = rp.CompanyID
			JOIN tblVendorConnection vc ON rt.RateTableId = vc.RateTableID AND vc.CompanyId = rt.CompanyID
			JOIN tblAccount a ON vc.AccountId = a.AccountId AND a.CompanyId = vc.CompanyID
			WHERE
				rp.Status = 1 AND vc.Active = 1 AND a.Status = 1 AND a.IsVendor = 1 -- AND a.CurrencyId IS NOT NULL
				AND vc.RateTypeID = @v_RateTypeID -- voicecall/termination
				AND rt.Type = @v_RateTypeID -- voicecall/termination
				AND rt.AppliedTo = @v_Vendor -- vendor
				AND rtr.EffectiveDate <= NOW()
				AND ( rtr.EndDate IS NULL OR  rtr.EndDate > NOW() )
				AND rtr.Blocked = 0
				-- @TODO Vendor wise timzeone in future
				AND ( p_OriginationNo LIKE  CONCAT(rtr.OriginationCode,"%") OR rtr.OriginationCode IS NULL)
				AND p_DestinationNo LIKE  CONCAT(rtr.DestinationCode,"%")
				AND (p_Location = '' OR vc.Location = p_Location)
				AND ( rtr.TimezonesID = fn_updateTempCDRTimeZones(p_DateAndTime,vc.AccountId,1,a.Country,p_APICountry) OR rtr.TimezonesID = 1 )
			ORDER BY
				IFNULL(rtr.Preference ,5) DESC, Rate ASC, rpc.`Order` DESC, rtr.DestinationCode DESC, rtr.OriginationCode DESC, vc.Name
		) vendorTable;

	ELSE -- IF @v_RoutingProfileID > 0 ELSE

		SET @rank:=0,@VendorConnectionName:='';
		INSERT INTO tmp_VendorRates(
			Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,Currency,Rate,VendorCLIPrefix,VendorCLDPrefix,TrunkPrefix,OriginationCode,DestinationCode,TimezonesID,VendorConnectionID
		)
		SELECT
			Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,Currency as Currency,ROUND(Rate,6) as Rate,VendorCLIPrefix,VendorCLDPrefix,TrunkPrefix,OriginationCode,DestinationCode,TimezonesID,VendorConnectionID
		FROM
		(
			SELECT
				CONCAT(IFNULL(vc.CallPrefix ,""),rtr.DestinationCode) as Prefix,IFNULL(vc.CallPrefix ,"") as TrunkPrefix,rtr.DestinationCode as VendorCLDPrefix,IFNULL(rtr.OriginationCode ,"") as VendorCLIPrefix,vc.AccountId as VendorID,v.AccountName as VendorName,vc.Name as VendorConnectionName,vc.SipHeader as SipHeader,vc.IP as IP,vc.Port as Port,vc.Username as Username,vc.Password as Password,
				vc.AuthenticationMode as AuthenticationMode,rtr.TimezonesID, @v_AccountCurrency_ as Currency,
				CASE WHEN IFNULL(rtr.RateCurrency,rt.CurrencyID) = @v_AccountCurrencyID_
				THEN
					rtr.Rate
				ELSE
				(
					-- Convert to base currrncy AND x by RateGenerator Exhange
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = @v_AccountCurrencyID_ )
					* ( rtr.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = IFNULL(rtr.RateCurrency,rt.CurrencyID) ))
				)
				END as Rate,rtr.Preference,rc.Order ,rtr.DestinationCode,
				rtr.OriginationCode,
				vc.VendorConnectionID
			FROM
				tblVendorConnection vc
			JOIN tblAccount v ON vc.AccountId = v.AccountId AND v.CompanyId = vc.CompanyID
			JOIN tblRateTable rt ON  vc.RateTableID = rt.RateTableId
			AND v.CompanyId = rt.CompanyId
			JOIN tblRateTableRate rtr ON  rtr.RateTableID = rt.RateTableId
			LEFT JOIN tblRoutingCategory rc ON rtr.RoutingCategoryID =  rc.RoutingCategoryID
			WHERE
				vc.Active = 1
				AND vc.RateTypeID = 1
				AND v.Status = 1
				AND v.IsVendor = 1
				AND v.CurrencyId is not NULL
				AND EffectiveDate <= now()
				AND ( rtr.EndDate IS NULL OR  rtr.EndDate > Now() )
				AND rtr.Blocked = 0
				AND rt.`Type` = 1 AND rt.AppliedTo = 2
				AND (p_OriginationNo LIKE CONCAT(rtr.OriginationCode,"%") OR rtr.OriginationCode IS NULL)
				AND p_DestinationNo LIKE CONCAT(rtr.DestinationCode,"%")
				AND ( rtr.TimezonesID = fn_updateTempCDRTimeZones(p_DateAndTime,vc.AccountId,1,v.Country,p_APICountry) OR rtr.TimezonesID = 1 )
				AND fnGetColumnValue(p_Location,vc.Location,2) = fnGetColumnValue(p_Location,vc.Location,1)
			ORDER BY
				IFNULL(rtr.Preference ,5) DESC, Rate, rtr.DestinationCode DESC, rtr.OriginationCode DESC, vc.Name
		) vendorTable;

	END IF; -- /IF @v_RoutingProfileID > 0



	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRates2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRates2 AS (SELECT * FROM tmp_VendorRates);
	-- delete default timezone rates if custom timezone applies to vendor connection and rate exist against that custom timezone (here custom means peak, off-peak etc)
	DELETE
		t1
	FROM
		tmp_VendorRates t1
	INNER JOIN
		tmp_VendorRates2 t2
	WHERE
		t1.VendorID = t2.VendorID AND t1.VendorConnectionID = t2.VendorConnectionID AND
		t1.DestinationCode = t2.DestinationCode AND
		((t1.OriginationCode IS NULL AND t2.OriginationCode IS NULL) OR t1.OriginationCode = t2.OriginationCode) AND
		t1.TimezonesID < t2.TimezonesID;

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRates3;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRates3 AS (SELECT * FROM tmp_VendorRates);
	-- keep only maximum matched Prefix of the same vendor connection, delete other rates
	DELETE
		t1
	FROM
		tmp_VendorRates t1
	INNER JOIN
		tmp_VendorRates3 t2
	WHERE
		t1.VendorID = t2.VendorID AND t1.VendorConnectionID = t2.VendorConnectionID
		AND (
			t1.Prefix < t2.Prefix OR
			(
				t1.Prefix = t2.Prefix AND
				(
					t1.OriginationCode < t2.OriginationCode OR (t1.OriginationCode IS NULL AND t2.OriginationCode IS NOT NULL)
				)
			)
		);


	IF @v_RoutingProfileID > 0
	THEN

		SELECT
			@rank:=@rank+1 AS Position,Prefix,VendorCLIPrefix,VendorCLDPrefix,TrunkPrefix,VendorID,VendorName,VendorConnectionName,
			SipHeader,IP,Port,Username,Password,AuthenticationMode,Currency,Rate,RoutingCategoryOrder,RoutingCategoryName
		FROM
			tmp_VendorRates
		ORDER BY
			RoutingCategoryOrder ASC, IFNULL(Preference ,5) DESC, Rate ASC, DestinationCode DESC, OriginationCode DESC, VendorConnectionName;

	ELSE

		SELECT
			@rank:=@rank+1 AS Position,Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,
			AuthenticationMode,Currency,ROUND(Rate,6) AS Rate,VendorCLIPrefix,VendorCLDPrefix,TrunkPrefix
		FROM
			tmp_VendorRates
		ORDER BY
			IFNULL(Preference ,5) DESC, Rate,DestinationCode DESC, OriginationCode DESC,VendorConnectionName;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP FUNCTION IF EXISTS `fn_updateTempCDRTimeZones`;
DELIMITER //
CREATE FUNCTION `fn_updateTempCDRTimeZones`(
	`p_DateAndTime` DATETIME,
	`p_VendorID` INT,
	`p_RateType` INT,
	`p_CountryName` VARCHAR(50),
	`p_APICountry` VARCHAR(200)
) RETURNS int(11)
ThisSP:BEGIN

	DECLARE v_timezones_count_ INT;
	DECLARE v_TimezonesID_ INT;
	DECLARE v_VendorTimeZone_ VARCHAR(200);
	DECLARE v_countryTimeZone_ VARCHAR(200);
	DECLARE v_CompanyTimeZone_ VARCHAR(200);
	DECLARE v_DateAndTimeVendorToCountry DATETIME;
	DECLARE v_DateAndTimeCompanyToCountry DATETIME;
	DECLARE v_APICountryID INT;

	DROP TEMPORARY TABLE IF EXISTS tmp_timezones;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_timezones (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT(11),
		TimezonesID INT(11),
		Title VARCHAR(50),
		FromTime VARCHAR(10),
		ToTime VARCHAR(10),
		DaysOfWeek VARCHAR(100),
		DaysOfMonth VARCHAR(100),
		Months VARCHAR(100),
		ApplyIF VARCHAR(100)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_tblgetTimezone;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblgetTimezone (
		`getTimezoneID` INT(11) NOT NULL AUTO_INCREMENT,
		`AccountID` INT(11),
		`connect_time` DATETIME NULL DEFAULT NULL,
		`disconnect_time` DATETIME NULL DEFAULT NULL,
		`TimezonesID` INT(11) NULL DEFAULT NULL,
		PRIMARY KEY (`getTimezoneID`)
	);

	-- get company timezone
	SET v_CompanyTimeZone_ = "+00:00"; -- set default value
	SELECT IFNULL(TimeZoneValue,"+00:00") INTO v_CompanyTimeZone_ FROM tbl_Neon_TimeZone WHERE TimeZoneName = (SELECT TimeZone FROM tblCompany WHERE CompanyID = 1);

	-- get api vendor timezone
	SET v_VendorTimeZone_ = "+00:00"; -- set default value
	SELECT IFNULL(TimeZoneValue,"+00:00") INTO v_VendorTimeZone_ FROM tbl_Neon_TimeZone WHERE TimeZoneName = (SELECT TimeZone FROM tblAccount WHERE AccountID = p_VendorID);

	-- get api country timezone
	SET v_countryTimeZone_ = "+00:00"; -- set default value
	SELECT CountryID,IFNULL(TimeZone,"+00:00") INTO v_APICountryID,v_countryTimeZone_ FROM tblCountry WHERE Country = p_APICountry;

	-- convert time from company timezone to api country timezone
	SELECT CONVERT_TZ(p_DateAndTime,v_CompanyTimeZone_,v_countryTimeZone_) INTO v_DateAndTimeCompanyToCountry;

	-- convert time from vendor timezone to api country timezone
	SELECT CONVERT_TZ(p_DateAndTime,v_VendorTimeZone_,v_countryTimeZone_) INTO v_DateAndTimeVendorToCountry;

	-- insert vendor timezones where timezone country = api country or timezones country = all
	INSERT INTO tmp_timezones (
		AccountID,TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF
	)
	SELECT
		VendorID AS AccountID,TimezoneID AS TimezonesID,'' AS Title,
		CASE WHEN INSTR(FromTime,':') = 2 THEN CONCAT('0',FromTime) ELSE FromTime END AS FromTime,
		CASE WHEN INSTR(ToTime,':') = 2  THEN CONCAT('0',ToTime) ELSE ToTime END AS ToTime,
		DaysOfWeek,DaysOfMonth,Months,ApplyIF
	FROM
		tblVendorTimezone
	WHERE
		Status=1 AND Type = p_RateType AND VendorID = p_VendorID AND (Country = v_APICountryID OR Country IS NULL)
	ORDER BY
		Country DESC; -- order by => first matched country timezones then All country timezones

	-- insert all timezones from main company timezone table
	INSERT INTO tmp_timezones (
		AccountID,TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF
	)
	SELECT
		0,TimezonesID AS TimezonesID,'' AS Title,
		CASE WHEN INSTR(FromTime,':') = 2 THEN CONCAT('0',FromTime) ELSE FromTime END AS FromTime,
		CASE WHEN INSTR(ToTime,':') = 2  THEN CONCAT('0',ToTime) ELSE ToTime END AS ToTime,
		DaysOfWeek,DaysOfMonth,Months,ApplyIF
	FROM
		tblTimezones
	WHERE
		Status=1
	ORDER BY
		TimezonesID DESC;


	SELECT COUNT(*) INTO v_timezones_count_ FROM tmp_timezones;

	IF v_timezones_count_ >= 1
	THEN

		INSERT INTO tmp_tblgetTimezone (AccountID,connect_time,disconnect_time) VALUES (p_VendorID,v_DateAndTimeVendorToCountry, v_DateAndTimeVendorToCountry);
		INSERT INTO tmp_tblgetTimezone (AccountID,connect_time,disconnect_time) VALUES (0,v_DateAndTimeCompanyToCountry, v_DateAndTimeCompanyToCountry);

		UPDATE
			tmp_tblgetTimezone temp
		JOIN
			tmp_timezones t ON t.AccountID = temp.AccountID
		SET
			temp.TimezonesID = t.TimezonesID
		WHERE
		(
			(temp.TimezonesID = '' OR temp.TimezonesID IS NULL)
			AND
			(
				(t.FromTime = '' AND t.ToTime = '')
				OR
				(
					(
						t.ApplyIF = 'start' AND
						-- DATE_FORMAT(connect_time, '%H:%i:%s') BETWEEN CONCAT(FromTime,':00') AND CONCAT(ToTime,':00')
						-- (TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) >= TIME(FromTime) OR TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) <= TIME(ToTime))
						(
							TIME(FromTime) > TIME(ToTime) AND
							(
								-- this condition works when date change for ex: FromTime=18:00 and ToTime=08:00
								-- if you see that day changes between 18:00 to 08:00, 18:00 to 00:00 then 00:00 to 08:00
								TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) >= TIME(FromTime) OR TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) <= TIME(ToTime)
							)
							OR
							TIME(FromTime) <= TIME(ToTime) AND
							(
								-- this condition works when same date for ex: FromTime=09:00 and ToTime=12:00
								TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) >= TIME(FromTime) AND TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) <= TIME(ToTime)
							)
						)
					)
					OR
					(
						t.ApplyIF = 'end' AND
						-- DATE_FORMAT(disconnect_time, '%H:%i:%s') BETWEEN CONCAT(FromTime,':00') AND CONCAT(ToTime,':00')
						(
							TIME(FromTime) > TIME(ToTime) AND
							(
								TIME(DATE_FORMAT(disconnect_time, '%H:%i:%s')) >= TIME(FromTime) OR TIME(DATE_FORMAT(disconnect_time, '%H:%i:%s')) <= TIME(ToTime)
							)
							OR
							TIME(FromTime) <= TIME(ToTime) AND
							(
								TIME(DATE_FORMAT(disconnect_time, '%H:%i:%s')) >= TIME(FromTime) AND TIME(DATE_FORMAT(disconnect_time, '%H:%i:%s')) <= TIME(ToTime)
							)
						)
					)
					OR
					(
						t.ApplyIF = 'both' AND
						-- DATE_FORMAT(connect_time, '%H:%i:%s') BETWEEN CONCAT(FromTime,':00') AND CONCAT(ToTime,':00') AND
						-- DATE_FORMAT(disconnect_time, '%H:%i:%s') BETWEEN CONCAT(FromTime,':00') AND CONCAT(ToTime,':00')
						(
							TIME(FromTime) > TIME(ToTime) AND
							(
								TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) >= TIME(FromTime) OR TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) <= TIME(ToTime)
							)
							OR
							TIME(FromTime) <= TIME(ToTime) AND
							(
								TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) >= TIME(FromTime) AND TIME(DATE_FORMAT(connect_time, '%H:%i:%s')) <= TIME(ToTime)
							)
						)
						AND
						(
							TIME(FromTime) > TIME(ToTime) AND
							(
								TIME(DATE_FORMAT(disconnect_time, '%H:%i:%s')) >= TIME(FromTime) OR TIME(DATE_FORMAT(disconnect_time, '%H:%i:%s')) <= TIME(ToTime)
							)
							OR
							TIME(FromTime) <= TIME(ToTime) AND
							(
								TIME(DATE_FORMAT(disconnect_time, '%H:%i:%s')) >= TIME(FromTime) AND TIME(DATE_FORMAT(disconnect_time, '%H:%i:%s')) <= TIME(ToTime)
							)
						)
					)
				)
			)
			AND
			(
				t.Months = ''
				OR
				(
					(
						t.ApplyIF = 'start' AND
						FIND_IN_SET(MONTH(connect_time), Months) != 0
					)
					OR
					(
						t.ApplyIF = 'end' AND
						FIND_IN_SET(MONTH(disconnect_time), Months) != 0
					)
					OR
					(
						t.ApplyIF = 'both' AND
						FIND_IN_SET(MONTH(connect_time), Months) != 0 AND
						FIND_IN_SET(MONTH(disconnect_time), Months) != 0
					)
				)
			)
			AND
			(
				t.DaysOfMonth = ''
				OR
				(
					(
						t.ApplyIF = 'start' AND
						FIND_IN_SET(DAY(connect_time), DaysOfMonth) != 0
					)
					OR
					(
						t.ApplyIF = 'end' AND
						FIND_IN_SET(DAY(disconnect_time), DaysOfMonth) != 0
					)
					OR
					(
						t.ApplyIF = 'both' AND
						FIND_IN_SET(DAY(connect_time), DaysOfMonth) != 0 AND
						FIND_IN_SET(DAY(disconnect_time), DaysOfMonth) != 0
					)
				)
			)
			AND
			(
				t.DaysOfWeek = ''
				OR
				(
					(
						t.ApplyIF = 'start' AND
						FIND_IN_SET(DAYOFWEEK(connect_time), DaysOfWeek) != 0
					)
					OR
					(
						t.ApplyIF = 'end' AND
						FIND_IN_SET(DAYOFWEEK(disconnect_time), DaysOfWeek) != 0
					)
					OR
					(
						t.ApplyIF = 'both' AND
						FIND_IN_SET(DAYOFWEEK(connect_time), DaysOfWeek) != 0 AND
						FIND_IN_SET(DAYOFWEEK(disconnect_time), DaysOfWeek) != 0
					)
				)
			)
		);

		-- if vendor timezone match
		SELECT TimezonesID INTO v_TimezonesID_ FROM tmp_tblgetTimezone WHERE AccountID = p_VendorID AND TimezonesID IS NOT NULL;

		IF v_TimezonesID_ IS NULL
		THEN
			-- if vendor timezone not match then check if company timezone match
			SELECT TimezonesID INTO v_TimezonesID_ FROM tmp_tblgetTimezone WHERE AccountID = 0 AND TimezonesID IS NOT NULL;
		END IF;

		RETURN IFNULL(v_TimezonesID_,1);-- return match timezone, if not match any then return default timezone which is 1

	ELSE

		RETURN 1; -- return default timezone

	END IF;

END//
DELIMITER ;