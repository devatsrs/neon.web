USE `speakIntelligentRoutingEngine`;

ALTER TABLE `tblActiveCall`
	ADD COLUMN `MinimumDuration` TINYINT(4) NULL DEFAULT '0' AFTER `MinimumCallCharge`;

USE `speakintelligentCDR`;	
	
ALTER TABLE `tblUsageDetails`
	ADD COLUMN `MinimumDuration` TINYINT(4) NULL DEFAULT '0' AFTER `MinimumCallCharge`;	
	
ALTER TABLE `tblUsageDetailFailedCall`
	ADD COLUMN `MinimumDuration` TINYINT(4) NULL DEFAULT '0' AFTER `MinimumCallCharge`;
	
USE `speakintelligentRM`;
DROP PROCEDURE IF EXISTS `prc_FindApiInBoundPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_FindApiInBoundPrefix`(
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_cli` VARCHAR(200),
	IN `p_cld` VARCHAR(200),
	IN `p_City` VARCHAR(200),
	IN `p_Tariff` VARCHAR(50),
	IN `p_OriginType` VARCHAR(50),
	IN `p_OriginProvider` VARCHAR(50),
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Type` VARCHAR(50)
)
BEGIN

	DECLARE v_codedeckid_ INT;
	DECLARE v_ratetableid_ INT;
	DECLARE v_CompanyID_ INT;
	DECLARE v_Count_ INT;
	DECLARE v_Count1_ INT;
	DECLARE v_Count2_ INT;

		SELECT
			CodeDeckId,
			RateTableId,
			CompanyId
		INTO
			v_codedeckid_,
			v_ratetableid_,
			v_CompanyID_
		FROM tblRateTable
		WHERE RateTableId = p_RateTableID;

	DROP TEMPORARY TABLE IF EXISTS tmp_codes;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_codes(
		RateID INT,
		Code varchar(50)
	);
	
	INSERT INTO tmp_codes
	SELECT RateID,
		Code
	FROM tblRate
	WHERE CodeDeckId = v_codedeckid_ AND CompanyID = v_CompanyID_;
	

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate_(
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		AccessType varchar(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_(
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		AccessType varchar(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate3_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate3_(
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		AccessType varchar(50)
	);	
	
	INSERT INTO tmp_RateTableRate_
	SELECT 
		RateTableDIDRateID,
		OriginationRateID,
		RateID,
		'Other' as OriginationCode,
		'Other' as DestincationCode,
		IFNULL(City,'') as City,
		IFNULL(Tariff,'') as Tariff,
		IFNULL(AccessType,'') as AccessType
	FROM tblRateTableDIDRate
	WHERE RateTableId = p_RateTableID
		AND TimezonesID = p_TimezonesID
		AND EffectiveDate <= NOW()
		AND ApprovedStatus =1
		;
		
		UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.RateID=c.RateID
	 SET DestincationCode = c.Code; 	
	 UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.OriginationRateID=c.RateID
	 SET OriginationCode = c.Code;

	/** Both mathc cld-> destination code , cli -> origination code */
	
	IF (p_OriginType != '' OR p_OriginProvider != '')
	THEN		
		
	INSERT INTO tmp_RateTableRate2_
	SELECT * FROM tmp_RateTableRate_
	WHERE p_cli REGEXP "^[0-9]+$"
			AND (OriginationCode like  CONCAT("%",p_OriginType,"%") && OriginationCode like CONCAT("%",p_OriginProvider,"%"))			
			AND p_cld REGEXP "^[0-9]+$"
			-- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = p_City
			AND Tariff = p_Tariff
			AND AccessType = p_Type
			;
	
	END IF;		
			
	SELECT COUNT(*) into v_Count_ from tmp_RateTableRate2_;


	/** if not found record above , we only match on cld->destincation code */
	
	IF v_Count_ = 0
	THEN 
	
		INSERT INTO tmp_RateTableRate2_
		SELECT * FROM tmp_RateTableRate_
		WHERE OriginationCode ='Other'
			AND p_cld REGEXP "^[0-9]+$"
		 -- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = p_City
			AND Tariff = p_Tariff
			AND AccessType = p_Type
				;
				
		SELECT COUNT(*) into v_Count1_ from tmp_RateTableRate2_;
		
	ELSE
	
		SET v_Count1_=v_Count_;
		
	END IF;
	
	/*
	
	IF v_Count1_ = 0
	THEN
	
		INSERT INTO tmp_RateTableRate2_
		SELECT * FROM tmp_RateTableRate_
		WHERE OriginationCode ='Other'
			AND p_cld REGEXP "^[0-9]+$"
			-- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = ''
			AND Tariff = ''
			;
				
		SELECT COUNT(*) into v_Count2_ from tmp_RateTableRate2_;
		SET v_Count1_=v_Count2_;
	
	
	END IF;

	*/
	
	IF v_Count1_ > 0
	THEN
		INSERT INTO tmp_RateTableRate3_
		SELECT * FROM tmp_RateTableRate2_ ORDER BY LENGTH(DestincationCode) DESC LIMIT 1; 
	END IF;	

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_FindApiOutBoundPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_FindApiOutBoundPrefix`(
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_cli` VARCHAR(200),
	IN `p_cld` VARCHAR(200)
)
BEGIN

	DECLARE v_codedeckid_ INT;
	DECLARE v_ratetableid_ INT;
	DECLARE v_CompanyID_ INT;
	DECLARE v_Count_ INT;
	DECLARE v_Count1_ INT;

		SELECT
			CodeDeckId,
			RateTableId,
			CompanyId
		INTO
			v_codedeckid_,
			v_ratetableid_,
			v_CompanyID_
		FROM tblRateTable
		WHERE RateTableId = p_RateTableID;

	DROP TEMPORARY TABLE IF EXISTS tmp_codes;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_codes(
		RateID int,
		Code varchar(50)
	);
	
	INSERT INTO tmp_codes
	SELECT RateID,
	Code
	FROM tblRate
	WHERE CodeDeckId = v_codedeckid_ AND CompanyID = v_CompanyID_;
	

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate_(
		RateTableRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_(
		RateTableRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate3_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate3_(
		RateTableRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50)
	);
	
	INSERT INTO tmp_RateTableRate_
	SELECT 
		RateTableRateID,
		OriginationRateID,
		RateID,
		'Other' as OriginationCode,
		'Other' as DestincationCode
	FROM tblRateTableRate
	WHERE RateTableId = p_RateTableID
		AND TimezonesID = p_TimezonesID
		AND EffectiveDate <= NOW()
		AND ApprovedStatus=1
		;
		
	UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.RateID=c.RateID
	 SET DestincationCode = c.Code; 	
	 
	 UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.OriginationRateID=c.RateID
	 SET OriginationCode = c.Code;
		
	/** Both mathc cld-> destination code , cli -> origination code */
		
	INSERT INTO tmp_RateTableRate2_
	select * from tmp_RateTableRate_
	where p_cli REGEXP "^[0-9]+$"
			AND p_cli like  CONCAT(OriginationCode,"%")			
			AND p_cld REGEXP "^[0-9]+$"
			AND p_cld like  CONCAT(DestincationCode,"%");
			
	SELECT COUNT(*) INTO v_Count_ FROM tmp_RateTableRate2_;
	
	/** if not found record above , we only match on cld->destincation code */
		
	IF v_Count_ = 0
	THEN 
	
		INSERT INTO tmp_RateTableRate2_
		select * from tmp_RateTableRate_
		where OriginationCode ='Other'
				AND p_cld REGEXP "^[0-9]+$"
				AND p_cld like  CONCAT(DestincationCode,"%");
				
		SELECT COUNT(*) INTO v_Count1_ FROM tmp_RateTableRate2_;
		
	ELSE
	
		SET v_Count1_=v_Count_;
		
	END IF;

	IF v_Count1_ > 0
	THEN
		INSERT INTO tmp_RateTableRate3_
		SELECT * FROM tmp_RateTableRate2_ ORDER BY length(DestincationCode) desc limit 1; 
	END IF;
	
END//
DELIMITER ;

USE `speakIntelligentRoutingEngine`;

DROP FUNCTION IF EXISTS `FnConvertCurrencyRate`;
DELIMITER //
CREATE FUNCTION `FnConvertCurrencyRate`(
	`p_CompanyCurrency` INT,
	`p_AccountCurrency` INT,
	`p_FileCurrency` INT,
	`p_Rate` DECIMAL(18,6)


) RETURNS decimal(18,6)
BEGIN

DECLARE V_NewRate DECIMAL(18,6) DEFAULT 0;
DECLARE V_ConversionRate DECIMAL(18,6) DEFAULT 0;
DECLARE V_ACConversionRate DECIMAL(18,6) DEFAULT 0;
DECLARE V_FCConversionRate DECIMAL(18,6) DEFAULT 0;

IF(p_FileCurrency = p_AccountCurrency)
THEN
	SET V_NewRate = p_Rate;
	
ELSEIF (p_FileCurrency = p_CompanyCurrency)	
THEN

	 SELECT Value INTO V_ConversionRate FROM `speakintelligentRM`.`tblCurrencyConversion` WHERE CurrencyID = p_AccountCurrency;
	 
	IF FOUND_ROWS() = 0
	THEN
		SET V_NewRate = 0;
	ELSE
		SET V_NewRate = (p_Rate * V_ConversionRate);
	END IF;
	
ELSE

	SELECT Value INTO V_ACConversionRate FROM `speakintelligentRM`.`tblCurrencyConversion` WHERE CurrencyID = p_AccountCurrency;
	IF FOUND_ROWS() > 0
	THEN
	
		SELECT Value INTO V_FCConversionRate FROM `speakintelligentRM`.`tblCurrencyConversion` WHERE CurrencyID = p_FileCurrency;
		IF FOUND_ROWS() > 0
		THEN
			SET V_NewRate = (V_ACConversionRate) * (p_Rate /V_FCConversionRate );
		END IF;	
		
	END IF;

END IF;


RETURN V_NewRate;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FnGetCostWithTaxes`;
DELIMITER //
CREATE FUNCTION `FnGetCostWithTaxes`(
	`p_Rate` DECIMAL(18,6),
	`p_TaxRateIDs` TEXT	

) RETURNS decimal(18,6)
BEGIN

DECLARE V_NewRate DECIMAL(18,6) DEFAULT 0;
DECLARE i INT;

DROP TEMPORARY TABLE IF EXISTS `table1`;
CREATE TEMPORARY TABLE `table1` (
  `TaxRateID` INT NOT NULL,
  `Amount` DECIMAL(18,6) DEFAULT NULL,
  `TotalAmount` DECIMAL(18,6) DEFAULT NULL
);

SET i=1;
REPEAT

	INSERT INTO table1(TaxRateID)
	SELECT `speakintelligentRM`.`FnStringSplit`(p_TaxRateIDs, ',', i) WHERE `speakintelligentRM`.`FnStringSplit`(p_TaxRateIDs, ',', i) IS NOT NULL LIMIT 1;
	SET i = i + 1;
	UNTIL ROW_COUNT() = 0

END REPEAT;


UPDATE table1 t INNER JOIN `speakintelligentRM`.`tblTaxRate` tr ON t.TaxRateID = tr.TaxRateId
SET t.Amount = tr.Amount, t.TotalAmount = IF(tr.FlatStatus=1,tr.Amount,((p_Rate*tr.Amount)/100))
;

SELECT SUM(IFNULL(TotalAmount,0)) INTO V_NewRate FROM table1;

RETURN V_NewRate + p_Rate;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_blockApiCall`;
DELIMITER //
CREATE PROCEDURE `prc_blockApiCall`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_UUID` VARCHAR(250),
	IN `p_DisconnectTime` DATETIME,
	IN `p_BlockReason` VARCHAR(255)
)
PRC:BEGIN

	DECLARE V_ActiveCallID INT DEFAULT 0;
	DECLARE v_AccountID INT;
	DECLARE v_BillingType INT;
	DECLARE V_Balance DECIMAL(18,6);
	DECLARE V_connect_time DATETIME;
	DECLARE V_Duration INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
		CREATE TEMPORARY TABLE tmp_Error_ (
			ErrorMessage longtext
		);
	
		
	IF p_AccountID > 0 THEN
		
			SELECT AccountID INTO v_AccountID from speakintelligentRM.tblAccount where AccountID = p_AccountID AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountNo != '' THEN
		
			SELECT AccountID INTO v_AccountID from speakintelligentRM.tblAccount where `Number` = p_AccountNo AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountDynamicField != '' AND p_AccountDynamicFieldValue != '' THEN
		
			SELECT DISTINCT dfv.ParentID INTO v_AccountID  FROM speakintelligentRM.tblDynamicFields df 						
			INNER JOIN speakintelligentRM.tblDynamicFieldsValue dfv ON dfv.DynamicFieldsID = df.DynamicFieldsID
			WHERE    df.`Type` = 'account' AND df.`Status` = 1 AND df.FieldSlug = p_AccountDynamicField AND dfv.FieldValue = p_AccountDynamicFieldValue;
			
		END IF;	
		
	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
		
	END IF;	
		
	SELECT ActiveCallID,ConnectTime INTO V_ActiveCallID,V_connect_time FROM tblActiveCall WHERE UUID = p_UUID AND AccountID = p_AccountID limit 1;
	IF (V_ActiveCallID = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Record Not Found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;			

	CALL `prc_insertActiveCallCost`(V_ActiveCallID,2,p_DisconnectTime,p_BlockReason);
	
	SELECT TIMESTAMPDIFF(SECOND,V_connect_time,p_DisconnectTime) INTO V_Duration;
	
	DELETE FROM tblActiveCall WHERE ActiveCallID = V_ActiveCallID;
	
	SELECT V_Duration AS duration;
		
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_endApiCall`;
DELIMITER //
CREATE PROCEDURE `prc_endApiCall`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_UUID` VARCHAR(250),
	IN `p_DisconnectTime` DATETIME
)
PRC:BEGIN

	DECLARE V_ActiveCallID INT DEFAULT 0;
	DECLARE v_AccountID INT;
	DECLARE v_BillingType INT;
	DECLARE V_Balance DECIMAL(18,6);
	DECLARE V_connect_time DATETIME;
	DECLARE V_Duration INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
		CREATE TEMPORARY TABLE tmp_Error_ (
			ErrorMessage longtext
		);
	

	IF p_AccountID > 0 THEN
		
			SELECT AccountID INTO v_AccountID from speakintelligentRM.tblAccount where AccountID = p_AccountID AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountNo != '' THEN
		
			SELECT AccountID INTO v_AccountID from speakintelligentRM.tblAccount where `Number` = p_AccountNo AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountDynamicField != '' AND p_AccountDynamicFieldValue != '' THEN
		
			SELECT DISTINCT dfv.ParentID INTO v_AccountID  FROM speakintelligentRM.tblDynamicFields df 						
			INNER JOIN speakintelligentRM.tblDynamicFieldsValue dfv ON dfv.DynamicFieldsID = df.DynamicFieldsID
			WHERE    df.`Type` = 'account' AND df.`Status` = 1 AND df.FieldSlug = p_AccountDynamicField AND dfv.FieldValue = p_AccountDynamicFieldValue;
		
		END IF;	
		
	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
		
	END IF;	
		
	SELECT ActiveCallID,ConnectTime INTO V_ActiveCallID,V_connect_time FROM tblActiveCall WHERE UUID = p_UUID AND AccountID = p_AccountID limit 1;
	IF (V_ActiveCallID = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Record Not Found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;			

	CALL `prc_insertActiveCallCost`(V_ActiveCallID,1,p_DisconnectTime,'');
	
	SELECT TIMESTAMPDIFF(SECOND,V_connect_time,p_DisconnectTime) INTO V_Duration;
	
	DELETE FROM tblActiveCall WHERE ActiveCallID = V_ActiveCallID;
	
	SELECT V_Duration AS duration;
	
		
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getAccountBalance`;
DELIMITER //
CREATE PROCEDURE `prc_getAccountBalance`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200)
)
PRC:BEGIN

	DECLARE v_AccountID INT;
	DECLARE v_BillingType INT;
	DECLARE V_Balance DECIMAL(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
		CREATE TEMPORARY TABLE tmp_Error_ (
			ErrorMessage longtext
		);
		
		
	IF p_AccountID > 0 THEN
		
			SELECT AccountID INTO v_AccountID from speakintelligentRM.tblAccount where AccountID = p_AccountID AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountNo != '' THEN
		
			SELECT AccountID INTO v_AccountID from speakintelligentRM.tblAccount where `Number` = p_AccountNo AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountDynamicField != '' AND p_AccountDynamicFieldValue != '' THEN
		
			SELECT DISTINCT dfv.ParentID INTO v_AccountID  FROM speakintelligentRM.tblDynamicFields df 						
			INNER JOIN speakintelligentRM.tblDynamicFieldsValue dfv ON dfv.DynamicFieldsID = df.DynamicFieldsID
			WHERE    df.`Type` = 'account' AND df.`Status` = 1 AND df.FieldSlug = p_AccountDynamicField AND dfv.FieldValue = p_AccountDynamicFieldValue;
			
		END IF;	
		
	IF (v_AccountID IS NULL OR v_AccountID = 0 )
		THEN
			
			INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found');
			SELECT * FROM tmp_Error_;
			LEAVE PRC;
			
		END IF;	


	SELECT BillingType INTO v_BillingType FROM speakintelligentRM.tblAccountBilling WHERE AccountID=v_AccountID AND AccountServiceID=0;
	
	/* Account is prepaid*/	
	IF(v_BillingType=1)
	THEN
	
		SELECT IFNULL(BalanceAmount,0) INTO V_Balance FROM speakintelligentRM.tblAccountBalanceLog WHERE AccountID=v_AccountID;
		
	ELSE
	/* Account is postpaid*/		
	
		SELECT IFNULL(BalanceAmount,0) INTO V_Balance FROM speakintelligentRM.tblAccountBalance WHERE AccountID=v_AccountID;	
	
	END IF;
		
		
		
		
	SELECT IF( V_Balance > 0 , 1 ,0 ) as has_balance , V_Balance AS BalanceAmount;
	
	
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getBlockCall`;
DELIMITER //
CREATE PROCEDURE `prc_getBlockCall`(
	IN `p_AccountId` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		IF p_AccountId > 0 THEN
				SELECT
				ud.*,
				uh.StartDate,
				uh.GatewayAccountID
			FROM speakintelligentCDR.tblUsageDetails  ud
			INNER JOIN speakintelligentCDR.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN speakintelligentRM.tblAccount a
				ON uh.AccountID = a.AccountID
			WHERE
			(p_StartDate ='0000-00-00' OR ( p_StartDate != '0000-00-00' AND DATE(uh.StartDate) >= p_StartDate))
			AND (p_EndDate ='0000-00-00' OR ( p_EndDate != '0000-00-00' AND DATE(uh.StartDate) <= p_EndDate))
			AND uh.AccountID = p_AccountID
			AND ud.disposition='Blocked';
			
		ELSE 
		
			SELECT
				ud.*,
				uh.StartDate,
				uh.GatewayAccountID
			FROM speakintelligentCDR.tblUsageDetails  ud
			INNER JOIN speakintelligentCDR.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			WHERE
			(p_StartDate ='0000-00-00' OR ( p_StartDate != '0000-00-00' AND DATE(uh.StartDate) >= p_StartDate))
			AND (p_EndDate ='0000-00-00' OR ( p_EndDate != '0000-00-00' AND DATE(uh.StartDate) <= p_EndDate))
			AND ud.disposition='Blocked';
			
		END IF;
		
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_insertActiveCallCost`;
DELIMITER //
CREATE PROCEDURE `prc_insertActiveCallCost`(
	IN `p_ActiveCallID` INT,
	IN `p_Type` INT,
	IN `p_DisconnectTime` DATETIME,
	IN `p_BlockReason` VARCHAR(255)
)
PRC:BEGIN
    
	DECLARE V_connect_time DATETIME;
	DECLARE V_disconnect_time DATETIME;
	DECLARE V_CallRecording INT DEFAULT 0;
	DECLARE V_CallRecordingStartTime DATETIME DEFAULT NULL;
	
	DECLARE V_AccountID INT;
	DECLARE V_CompanyID INT;
	DECLARE V_VendorID INT DEFAULT 0;
	DECLARE V_OutPaymentVendorID INT DEFAULT 0;
	DECLARE V_CallType VARCHAR(50); 	
	DECLARE V_Cost DECIMAL(18,6) DEFAULT 0;	
	DECLARE V_Duration INT DEFAULT 0;
	DECLARE V_CallRecordingDuration INT DEFAULT 0;
	DECLARE V_IsBlock TINYINT(4) DEFAULT 0;
	
	DECLARE V_VendorConnectionName VARCHAR(50);	
	DECLARE V_VendorRate DECIMAL(18,6);
	DECLARE V_buying_cost DECIMAL(18,6);
	DECLARE V_Trunk VARCHAR(50);
	DECLARE V_vendorAccountName VARCHAR(255);
	
	DECLARE V_CompanyGatewayID INT;	
	DECLARE V_GatewayAccountPKID INT;	
	DECLARE V_GatewayAccountID VARCHAR(100);	
	DECLARE V_UsageHeaderID INT;
	DECLARE V_VendorCDRHeaderID INT;
	DECLARE V_UsageDetailID BIGINT(20);
	DECLARE V_VendorCDRID BIGINT(20);
	
	DECLARE V_is_inbound INT DEFAULT 0;
	DECLARE V_disposition VARCHAR(100) DEFAULT '';
	DECLARE V_Count INT DEFAULT 0;	
	DECLARE V_billed_duration INT DEFAULT 0;
	
	DECLARE V_OutpaymentPerCall DECIMAL(18,6) DEFAULT 0;
	DECLARE V_OutpaymentPerMinute DECIMAL(18,6) DEFAULT 0;
	DECLARE V_OutPaymentAmount DECIMAL(18,6) DEFAULT 0;
	DECLARE V_OutPaymentLogID INT DEFAULT 0;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	SELECT AccountID,CompanyID,ConnectTime,CallRecording,CallType,CallRecordingStartTime,VendorID,OutPaymentVendorID,VendorConnectionName,VendorRate,CompanyGatewayID
	INTO V_AccountID,V_CompanyID,V_connect_time,V_CallRecording,V_CallType,V_CallRecordingStartTime,V_VendorID,V_OutPaymentVendorID,V_VendorConnectionName,V_VendorRate,V_CompanyGatewayID
	FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID;	
		
	-- END CALL API
	IF p_Type = 1
	THEN
	
		SELECT TIMESTAMPDIFF(SECOND,V_connect_time,p_DisconnectTime) INTO V_Duration;
		IF(V_CallRecording = 1)
		THEN
			SELECT TIMESTAMPDIFF(SECOND,V_CallRecordingStartTime,p_DisconnectTime) INTO V_CallRecordingDuration;
		END IF;
	
	END IF;
	
	-- BLOCK CALL API
	IF p_Type = 2
	THEN
		SELECT TIMESTAMPDIFF(SECOND,V_connect_time,p_DisconnectTime) INTO V_Duration;
		IF(V_CallRecording = 1)
		THEN
			SELECT TIMESTAMPDIFF(SECOND,V_CallRecordingStartTime,p_DisconnectTime) INTO V_CallRecordingDuration;
		END IF;
		
		SET V_IsBlock = 1;
		SET V_disposition = 'Blocked';
	
	END IF;
	
	IF V_CallType = 'Inbound'
	THEN
		SET V_is_inbound = 1;	
	END IF;
	
	
	
	UPDATE tblActiveCall
	SET Duration = V_Duration,DisconnectTime = p_DisconnectTime,CallRecordingEndTime = p_DisconnectTime,CallRecordingDuration = V_CallRecordingDuration,BlockReason = p_BlockReason
	WHERE ActiveCallID = p_ActiveCallID;
	

	
	CALL prc_updateActiveCallCost(p_ActiveCallID);
				
		
	/* Customer cdr insert */
	
	SELECT ga.GatewayAccountID INTO V_GatewayAccountID FROM `speakintelligentBilling`.`tblGatewayAccount` ga INNER JOIN `tblActiveCall` ac ON ac.GatewayAccountPKID = ga.GatewayAccountPKID WHERE ac.ActiveCallID = p_ActiveCallID limit 1;
	
	SELECT h.UsageHeaderID INTO V_UsageHeaderID FROM `speakintelligentCDR`.`tblUsageHeader` h
	INNER JOIN `tblActiveCall` ac ON ac.CompanyGatewayID = h.CompanyGatewayID AND h.GatewayAccountID = V_GatewayAccountID AND h.StartDate = DATE_FORMAT(ac.ConnectTime, "%Y-%m-%d") AND ac.AccountID = h.AccountID AND ac.AccountServiceID = h.AccountServiceID AND h.GatewayAccountPKID = ac.GatewayAccountPKID
	WHERE ac.ActiveCallID = p_ActiveCallID LIMIT 1;
	
	IF FOUND_ROWS() = 0
	THEN
		INSERT INTO `speakintelligentCDR`.`tblUsageHeader`(AccountID,CompanyID,CompanyGatewayID,GatewayAccountID,StartDate,ServiceID,AccountServiceID,GatewayAccountPKID,created_at,updated_at)
		SELECT AccountID,CompanyID,CompanyGatewayID,V_GatewayAccountID AS GatewayAccountID,DATE_FORMAT(ConnectTime, "%Y-%m-%d") AS StartDate,ServiceID,AccountServiceID,GatewayAccountPKID, NOW() AS created_at,NOW() AS updated_at
		FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID;
		
		SET V_UsageHeaderID = LAST_INSERT_ID();	
	END IF;
	
	
	
	INSERT INTO `speakintelligentCDR`.`tblUsageDetails`(UsageHeaderID,connect_time,disconnect_time,duration,billed_duration,billed_second,area_prefix,CLIPrefix,cli,cld,cost,ProcessID,ID,UUID,
	OutpaymentPerCall,OutpaymentPerMinute,Surcharges,CollectionCostAmount,CollectionCostPercentage,RecordingCostPerMinute,PackageCostPerMinute,AccountServicePackageID,CallRecording,CallRecordingStartTime,OriginType,OriginProvider,TimezonesID,
	PackageTimezonesID,City,Tariff,NoType,is_inbound,disposition,BlockReason,CostPerCall,CostPerMinute,SurchargePerCall,SurchargePerMinute,MinimumCallCharge,MinimumDuration
	)
	SELECT V_UsageHeaderID AS UsageHeaderID,ConnectTime,DisconnectTime,Duration,billed_duration,Duration,CLDPrefix,CLIPrefix,CLI,CLD,Cost,'' AS ProcessID,0 AS ID,UUID,
	OutpaymentPerCall,OutpaymentPerMinute,Surcharges,CollectionCostAmount,CollectionCostPercentage,RecordingCostPerMinute,PackageCostPerMinute,AccountServicePackageID,CallRecording,CallRecordingStartTime,OriginType,OriginProvider,TimezonesID,
	PackageTimezonesID,City,Tariff,NoType,V_is_inbound AS is_inbound,V_disposition AS disposition,BlockReason,CostPerCall,CostPerMinute,SurchargePerCall,SurchargePerMinute,MinimumCallCharge,MinimumDuration
	FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID limit 1; 
	
	SET V_UsageDetailID = LAST_INSERT_ID();	
	
	UPDATE `speakintelligentCDR`.`tblUsageDetails`
	SET ID = V_UsageDetailID
	WHERE UsageDetailID = V_UsageDetailID;
	
	/* Customer cdr insert */	
	/* Vendor cdr start */
	
	IF(V_VendorID > 0 &&  V_CallType = 'Outbound') 
	THEN
	
	
	SELECT AccountName INTO V_vendorAccountName FROM `speakintelligentRM`.`tblAccount` WHERE AccountID = V_VendorID;
	SELECT billed_duration INTO V_billed_duration FROM `tblActiveCall` WHERE ActiveCallID = p_ActiveCallID;
	
	SET V_buying_cost = V_billed_duration * (V_VendorRate/60);
	
	SELECT t.Trunk INTO V_Trunk from `speakintelligentRM`.`tblVendorConnection` vc INNER JOIN `speakintelligentRM`.`tblTrunk` t ON t.TrunkID = vc.TrunkID WHERE vc.Name = V_VendorConnectionName AND vc.AccountId = V_VendorID; 
	IF FOUND_ROWS() = 0
	THEN
		SET V_Trunk = 'Other';
	END IF;
	
	SELECT GatewayAccountPKID INTO V_GatewayAccountPKID FROM `speakintelligentBilling`.`tblGatewayAccount` 
	WHERE CompanyID = V_CompanyID AND CompanyGatewayID = V_CompanyGatewayID AND GatewayAccountID = V_vendorAccountName AND AccountName = V_vendorAccountName AND AccountID = V_VendorID AND IsVendor = 1 AND AccountServiceID = 0 AND ServiceID = 0;
	
	IF FOUND_ROWS() = 0
	THEN
		INSERT INTO `speakintelligentBilling`.`tblGatewayAccount`(CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,AccountName,ServiceID,AccountServiceID,IsVendor)
		VALUES(V_CompanyID,V_CompanyGatewayID,V_vendorAccountName,V_VendorID,V_vendorAccountName,0,0,1);
		
		SET V_GatewayAccountPKID = LAST_INSERT_ID();
	END IF;
	

	
	SELECT h.VendorCDRHeaderID INTO V_VendorCDRHeaderID FROM `speakintelligentCDR`.`tblVendorCDRHeader` h
	INNER JOIN `tblActiveCall` ac ON ac.CompanyGatewayID = h.CompanyGatewayID
		AND h.GatewayAccountID = V_vendorAccountName AND h.StartDate = DATE_FORMAT(ac.ConnectTime, "%Y-%m-%d") AND ac.AccountID = h.AccountID AND h.AccountServiceID = 0
		AND h.GatewayAccountPKID = V_GatewayAccountPKID
	WHERE ac.ActiveCallID = p_ActiveCallID LIMIT 1;
	
	IF FOUND_ROWS() = 0
	THEN
		INSERT INTO `speakintelligentCDR`.`tblVendorCDRHeader`(AccountID,CompanyID,CompanyGatewayID,GatewayAccountID,StartDate,ServiceID,AccountServiceID,GatewayAccountPKID,created_at,updated_at)
		SELECT AccountID,CompanyID,CompanyGatewayID,V_vendorAccountName AS GatewayAccountID,DATE_FORMAT(ConnectTime, "%Y-%m-%d") AS StartDate,0 as ServiceID,0 as AccountServiceID,V_GatewayAccountPKID as GatewayAccountPKID, NOW() AS created_at,NOW() AS updated_at
		FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID;
		
		SET V_VendorCDRHeaderID = LAST_INSERT_ID();	
	END IF;
	
	INSERT INTO `speakintelligentCDR`.`tblVendorCDR`(VendorCDRHeaderID,connect_time,disconnect_time,duration,billed_duration,billed_second,area_prefix,CLIPrefix,cli,cld,selling_cost,buying_cost,ProcessID,ID,UUID,trunk)
	SELECT V_VendorCDRHeaderID AS VendorCDRHeaderID,ConnectTime,DisconnectTime,Duration,billed_duration,Duration,VendorCLDPrefix,VendorCLIPrefix,CLI,CLD,Cost,V_buying_cost AS buying_cost,'' AS ProcessID,V_UsageDetailID AS ID,UUID, V_Trunk AS trunk
	FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID; 
	
	END IF;
	
	/* Vendor cdr start */
	/* Outpayment Log start */
	
	IF(V_OutPaymentVendorID > 0 && V_CallType = 'Inbound')
	THEN
	
		SELECT OutpaymentPerCall,OutpaymentPerMinute
		INTO V_OutpaymentPerCall,V_OutpaymentPerMinute
		FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID;
		
		SET V_Count = V_OutpaymentPerCall + V_OutpaymentPerMinute;
		IF(V_Count <> 0)
		THEN
		
						
			SELECT h.OutPaymentLogID,h.Amount INTO V_OutPaymentLogID,V_OutPaymentAmount FROM `speakintelligentRM`.`tblOutPaymentLog` h
			INNER JOIN `tblActiveCall` ac ON ac.AccountID = h.AccountID
				AND h.VendorID = V_OutPaymentVendorID AND h.DATE = DATE_FORMAT(ac.ConnectTime, "%Y-%m-%d") AND h.CLI = ac.CLI
			WHERE ac.ActiveCallID = p_ActiveCallID LIMIT 1;
			
			IF FOUND_ROWS() > 0
			THEN
			
				UPDATE `speakintelligentRM`.`tblOutPaymentLog`
				SET Amount = (V_OutPaymentAmount + V_Count)
				WHERE OutPaymentLogID = V_OutPaymentLogID;
			
			ELSE
			
				INSERT INTO `speakintelligentRM`.`tblOutPaymentLog`(CompanyID,AccountID,VendorID,CLI,Date,Amount,Status,created_at)
				SELECT CompanyID,AccountID,V_OutPaymentVendorID AS VendorID,CLI,DATE_FORMAT(ConnectTime, "%Y-%m-%d") AS Date,V_Count,0 AS Status,NOW()
				FROM `tblActiveCall` WHERE ActiveCallID = p_ActiveCallID LIMIT 1;
			
			END IF;
			
		END IF;
	
	END IF;
	
	/* Outpayment Log start */
		
				
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_insertPostpaidApiCall`;
DELIMITER //
CREATE PROCEDURE `prc_insertPostpaidApiCall`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_UUID` VARCHAR(250),
	IN `p_ConnectTime` DATETIME,
	IN `p_DisconnectTime` DATETIME,
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_CallType` VARCHAR(50),
	IN `p_VendorID` INT,
	IN `p_VendorConnectionName` VARCHAR(50),
	IN `p_OriginType` VARCHAR(50),
	IN `p_OriginProvider` VARCHAR(50),
	IN `p_VendorRate` DECIMAL(18,6),
	IN `p_VendorCLIPrefix` VARCHAR(50),
	IN `p_VendorCLDPrefix` VARCHAR(50),
	IN `p_CallRecording` INT,
	IN `p_CallRecordingStartTime` DATETIME	
)
PRC:BEGIN

DECLARE V_UUID_Duplicate INT DEFAULT 0;
DECLARE v_Check_Vendor INT;
DECLARE v_AccountID INT;
DECLARE v_CompanyID INT;
DECLARE v_BillingType INT;
DECLARE V_Balance DECIMAL(18,6);
DECLARE v_ActiveCallID INT DEFAULT 0;
DECLARE V_Duration INT DEFAULT 0;


	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
	CREATE TEMPORARY TABLE tmp_Error_ (
		ErrorMessage longtext
	);
	
	SELECT COUNT(*) INTO V_UUID_Duplicate FROM tblActiveCall WHERE UUID = p_UUID;
	IF (V_UUID_Duplicate > 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Call with this UUID already exists.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;	

	/** Check Account exits or not - start **/	
	
	IF p_AccountID > 0
	THEN
		SELECT AccountID,CompanyID INTO v_AccountID,v_CompanyID FROM speakintelligentRM.tblAccount WHERE AccountID = p_AccountID AND Status = 1 limit 1 ;
	ELSEIF p_AccountNo != ''
	THEN
		SELECT AccountID,CompanyID INTO v_AccountID,v_CompanyID FROM speakintelligentRM.tblAccount WHERE `Number` = p_AccountNo AND Status = 1 limit 1 ;
	ELSEIF p_AccountDynamicField != '' AND p_AccountDynamicFieldValue != ''
	THEN
		SELECT DISTINCT dfv.ParentID,df.CompanyID INTO v_AccountID,v_CompanyID  
		FROM speakintelligentRM.tblDynamicFields df 						
			INNER JOIN speakintelligentRM.tblDynamicFieldsValue dfv 
			ON dfv.DynamicFieldsID = df.DynamicFieldsID
		WHERE df.`Type` = 'account' AND df.`Status` = 1 AND df.FieldSlug = p_AccountDynamicField AND dfv.FieldValue = p_AccountDynamicFieldValue;
	END IF;	

	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;	
	
	IF (p_VendorID > 0 )
	THEN
		SELECT COUNT(*) INTO v_Check_Vendor FROM speakintelligentRM.tblAccount WHERE AccountID = p_VendorID;
		IF (v_Check_Vendor = 0 )
		THEN
			INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Vendor Account Not Found.');
			SELECT * FROM tmp_Error_;
			LEAVE PRC;
		END IF;	
	END IF;
	
	
	INSERT INTO tblActiveCall(AccountID,CompanyID,ConnectTime,CLI,CLD,CallType,UUID,VendorID,VendorConnectionName,OriginType,OriginProvider,VendorRate,VendorCLIPrefix,VendorCLDPrefix,CallRecording,Cost,Duration,billed_duration,created_by,created_at,updated_at)
	VALUES(v_AccountID,v_CompanyID,p_ConnectTime,p_CLI,p_CLD,p_CallType,p_UUID,p_VendorID,p_VendorConnectionName,REPLACE(p_OriginType,'-',''),REPLACE(p_OriginProvider,'-',''),p_VendorRate,p_VendorCLIPrefix,p_VendorCLDPrefix,p_CallRecording,0,0,0,'API',NOW(),NOW());
	
	SET v_ActiveCallID = LAST_INSERT_ID();
	
	IF (v_ActiveCallID = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Problem Inserting Call.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;
			
	IF(p_CallRecording = 1)
	THEN
		UPDATE tblActiveCall
			SET CallRecordingStartTime = p_CallRecordingStartTime, DisconnectTime = p_DisconnectTime
		WHERE ActiveCallID = v_ActiveCallID;
		
	END IF;
	
	CALL prc_updatestartCall(v_ActiveCallID,2);
	
	CALL `prc_insertActiveCallCost`(v_ActiveCallID,1,p_DisconnectTime,'');
	
	SELECT TIMESTAMPDIFF(SECOND,p_ConnectTime,p_DisconnectTime) INTO V_Duration;
	
	SELECT V_Duration AS duration;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_startApiCallRecording`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_startApiCallRecording`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_UUID` VARCHAR(250),
	IN `p_CallRecordingStartTime` DATETIME
)
PRC:BEGIN

	DECLARE V_UUID_Duplicate INT;
	DECLARE v_AccountID INT;
	DECLARE v_BillingType INT;
	DECLARE V_Balance DECIMAL(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
		CREATE TEMPORARY TABLE tmp_Error_ (
			ErrorMessage longtext
		);
	
		
	IF p_AccountID > 0 THEN
		
			SELECT AccountID INTO v_AccountID from speakintelligentRM.tblAccount where AccountID = p_AccountID AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountNo != '' THEN
		
			SELECT AccountID INTO v_AccountID from speakintelligentRM.tblAccount where `Number` = p_AccountNo AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountDynamicField != '' AND p_AccountDynamicFieldValue != '' THEN
		
			SELECT DISTINCT dfv.ParentID INTO v_AccountID  FROM speakintelligentRM.tblDynamicFields df 						
			INNER JOIN speakintelligentRM.tblDynamicFieldsValue dfv ON dfv.DynamicFieldsID = df.DynamicFieldsID
			WHERE    df.`Type` = 'account' AND df.`Status` = 1 AND df.FieldSlug = p_AccountDynamicField AND dfv.FieldValue = p_AccountDynamicFieldValue;
			
		END IF;	
		
	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
		
	END IF;	
		
	SELECT COUNT(*) INTO V_UUID_Duplicate FROM tblActiveCall WHERE UUID = p_UUID AND AccountID = p_AccountID;
	IF (V_UUID_Duplicate = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Record Not Found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;			

	SET V_UUID_Duplicate = 0;		
	
	SELECT COUNT(*) INTO V_UUID_Duplicate FROM tblActiveCall WHERE UUID = p_UUID AND CallRecording = 1 AND AccountID = p_AccountID;
	IF (V_UUID_Duplicate > 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Recording Already Started.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;			


	UPDATE `tblActiveCall`
	SET CallRecordingStartTime = p_CallRecordingStartTime,CallRecording = 1,updated_by = 'API',updated_at = NOW()
	WHERE UUID = p_UUID AND AccountID = p_AccountID;
		
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_startCall`;
DELIMITER //
CREATE PROCEDURE `prc_startCall`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_UUID` VARCHAR(250),
	IN `p_ConnectTime` DATETIME,
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_CallType` VARCHAR(50),
	IN `p_VendorID` INT,
	IN `p_VendorConnectionName` VARCHAR(50),
	IN `p_OriginType` VARCHAR(50),
	IN `p_OriginProvider` VARCHAR(50),
	IN `p_VendorRate` DECIMAL(18,6),
	IN `p_VendorCLIPrefix` VARCHAR(50),
	IN `p_VendorCLDPrefix` VARCHAR(50)
)
PRC:BEGIN

DECLARE v_UUID_Duplicate INT;
DECLARE v_Check_Vendor INT;
DECLARE v_AccountID INT;
DECLARE v_CompanyID INT;
DECLARE v_BillingType INT DEFAULT 0;
DECLARE V_Balance DECIMAL(18,6) DEFAULT 0;
DECLARE v_ActiveCallID INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
	CREATE TEMPORARY TABLE tmp_Error_ (
		ErrorMessage longtext
	);
	
	SELECT COUNT(*) INTO v_UUID_Duplicate FROM tblActiveCall WHERE UUID = p_UUID;
	IF (v_UUID_Duplicate > 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Call with this UUID already exists.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;	

	/** Check Account exits or not - start **/	
	
	IF p_AccountID > 0
	THEN
		SELECT AccountID,CompanyID INTO v_AccountID,v_CompanyID FROM speakintelligentRM.tblAccount WHERE AccountID = p_AccountID AND Status = 1 limit 1 ;
	ELSEIF p_AccountNo != ''
	THEN
		SELECT AccountID,CompanyID INTO v_AccountID,v_CompanyID FROM speakintelligentRM.tblAccount WHERE `Number` = p_AccountNo AND Status = 1 limit 1 ;
	ELSEIF p_AccountDynamicField != '' AND p_AccountDynamicFieldValue != ''
	THEN
		SELECT DISTINCT dfv.ParentID,df.CompanyID INTO v_AccountID,v_CompanyID  
		FROM speakintelligentRM.tblDynamicFields df 						
			INNER JOIN speakintelligentRM.tblDynamicFieldsValue dfv 
			ON dfv.DynamicFieldsID = df.DynamicFieldsID
		WHERE df.`Type` = 'account' AND df.`Status` = 1 AND df.FieldSlug = p_AccountDynamicField AND dfv.FieldValue = p_AccountDynamicFieldValue;
	END IF;	

	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;	
	
	IF (p_VendorID > 0 )
	THEN
		SELECT COUNT(*) INTO v_Check_Vendor FROM speakintelligentRM.tblAccount WHERE AccountID = p_VendorID;
		IF (v_Check_Vendor = 0 )
		THEN
			INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Vendor Account Not Found.');
			SELECT * FROM tmp_Error_;
			LEAVE PRC;
		END IF;	
	END IF;
	
	
	/** Check Account exits or not - end **/
	
	/** Check Account Balance is sufficent or not -start **/
	
	SELECT BillingType INTO v_BillingType FROM speakintelligentRM.tblAccountBilling WHERE AccountID=v_AccountID AND AccountServiceID=0;
	/* Account is prepaid*/	
	IF(v_BillingType=1)
	THEN	
		SELECT IFNULL(BalanceAmount,0) INTO V_Balance FROM speakintelligentRM.tblAccountBalanceLog WHERE AccountID=v_AccountID;		
	ELSE
	/* Account is postpaid*/				
		SELECT IFNULL(BalanceAmount,0) INTO V_Balance FROM speakintelligentRM.tblAccountBalance WHERE AccountID=v_AccountID;		
	END IF;
		
	IF(V_Balance <= 0)
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Account has not sufficient balance.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	
	END IF;
		
	/** Check Account Balance is sufficent or not -end **/	
	
	INSERT INTO tblActiveCall(AccountID,CompanyID,ConnectTime,CLI,CLD,CallType,UUID,VendorID,VendorConnectionName,OriginType,OriginProvider,VendorRate,VendorCLIPrefix,VendorCLDPrefix,CallRecording,Cost,Duration,billed_duration,created_by,created_at,updated_at)
	VALUES(v_AccountID,v_CompanyID,p_ConnectTime,p_CLI,p_CLD,p_CallType,p_UUID,p_VendorID,p_VendorConnectionName,REPLACE(p_OriginType,'-',''),REPLACE(p_OriginProvider,'-',''),p_VendorRate,p_VendorCLIPrefix,p_VendorCLDPrefix,0,0,0,0,'API',NOW(),NOW());
	
	SET v_ActiveCallID = LAST_INSERT_ID();
	
	IF (v_ActiveCallID = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Problem Creating Active Call.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;
	
	CALL prc_updatestartCall(v_ActiveCallID,1);
	
	SELECT v_ActiveCallID as ActiveCallID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_updateActiveCallCost`;
DELIMITER //
CREATE PROCEDURE `prc_updateActiveCallCost`(
	IN `p_ActiveCallID` INT
)
PRC:BEGIN

	DECLARE V_AccountID INT DEFAULT 0;
	DECLARE V_CompanyID INT DEFAULT 0;
	
	DECLARE V_CompanyCurrency INT DEFAULT 0;
	DECLARE V_AccountCurrency INT DEFAULT 0;
	DECLARE V_CallType VARCHAR(50) DEFAULT ''; 
	DECLARE V_CallRecording INT DEFAULT 0;
	
	DECLARE V_Cost DECIMAL(18,6) DEFAULT 0;	
	DECLARE V_Duration INT DEFAULT 0;
	DECLARE V_BilledDuration INT DEFAULT 0;
	DECLARE V_CallRecordingDuration INT DEFAULT 0;
	DECLARE V_TaxRateIDs VARCHAR(50) DEFAULT '';
	
	DECLARE V_OutBoundRateTableID INT DEFAULT 0;
	DECLARE V_OutBoundRateTableRateID INT DEFAULT 0;
	
	DECLARE V_RateTablePKGRateID INT DEFAULT 0;
	
	DECLARE V_InboundRateTableDIDRateID INT DEFAULT 0;
	
	DECLARE V_PackageCostPerMinute DECIMAL(18,6) DEFAULT 0; 
	DECLARE V_PackageCostPerMinuteCurrency DECIMAL(18,6) DEFAULT 0; 
	DECLARE V_RecordingCostPerMinute DECIMAL(18,6) DEFAULT 0; 
	DECLARE V_RecordingCostPerMinuteCurrency DECIMAL(18,6) DEFAULT 0; 
	
	DECLARE V_CostPerMinute DECIMAL(18,6) DEFAULT 0; 
	DECLARE V_CostPerMinuteCurrency INT DEFAULT 0; 
	DECLARE V_CostPerCall DECIMAL(18,6) DEFAULT 0; 
	DECLARE V_CostPerCallCurrency INT DEFAULT 0;
	DECLARE V_SurchargePerCall DECIMAL(18,6) DEFAULT 0;
	DECLARE V_SurchargePerCallCurrency INT DEFAULT 0;
    DECLARE V_SurchargePerMinute DECIMAL(18,6) DEFAULT 0;
	DECLARE V_SurchargePerMinuteCurrency INT DEFAULT 0;
    DECLARE V_OutpaymentPerCall DECIMAL(18,6) DEFAULT 0;
	DECLARE V_OutpaymentPerCallCurrency INT DEFAULT 0;
    DECLARE V_OutpaymentPerMinute DECIMAL(18,6) DEFAULT 0;
	DECLARE V_OutpaymentPerMinuteCurrency INT DEFAULT 0;
    DECLARE V_Surcharges DECIMAL(18,6) DEFAULT 0;
	DECLARE V_SurchargesCurrency INT DEFAULT 0;
    DECLARE V_CollectionCostAmount DECIMAL(18,6) DEFAULT 0;
	DECLARE V_CollectionCostAmountCurrency INT DEFAULT 0; 	
	DECLARE V_CollectionCostPercentage DECIMAL(18,6) DEFAULT 0;
	
	DECLARE V_ConnectionFee DECIMAL(18,6) DEFAULT 0;
	DECLARE V_ConnectionFeeCurrency INT DEFAULT 0;
	DECLARE V_Interval1 INT DEFAULT 0;
	DECLARE V_IntervalN INT DEFAULT 0;
	DECLARE V_Rate DECIMAL(18,6) DEFAULT 0;
	DECLARE V_RateN DECIMAL(18,6) DEFAULT 0;
	DECLARE V_RateCurrency INT DEFAULT 0;
	DECLARE V_MinimumDuration INT DEFAULT 0;
	DECLARE V_IsMinimumDuration INT DEFAULT 0;
	DECLARE V_MinimumCallCharge DECIMAL(18,6) DEFAULT 0; 
    DECLARE V_IsMinimumCallCharge INT DEFAULT 0;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
		
	SELECT AccountID,CompanyID,CallType,IFNULL(Duration,0),IFNULL(CallRecordingDuration,0),TaxRateIDs,RateTablePKGRateID,RateTableRateID,IFNULL(RateTableID,0),RateTableDIDRateID,CallRecording
	INTO V_AccountID,V_CompanyID,V_CallType,V_Duration,V_CallRecordingDuration,V_TaxRateIDs,V_RateTablePKGRateID,V_OutBoundRateTableRateID,V_OutBoundRateTableID,V_InboundRateTableDIDRateID,V_CallRecording
	FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID;	
	
	SELECT CurrencyId INTO V_CompanyCurrency FROM `speakintelligentRM`.`tblCompany` WHERE CompanyID = V_CompanyID;
	SELECT CurrencyId INTO V_AccountCurrency FROM `speakintelligentRM`.`tblAccount` WHERE AccountID = V_AccountID;
	
	IF(V_CallRecording = 1 && V_RateTablePKGRateID > 0)
	THEN		
		-- calculate package cost and recording per minute start			
		SELECT PackageCostPerMinute,IFNULL(PackageCostPerMinuteCurrency,0),RecordingCostPerMinute,IFNULL(RecordingCostPerMinuteCurrency,0) INTO V_PackageCostPerMinute,V_PackageCostPerMinuteCurrency,V_RecordingCostPerMinute,V_RecordingCostPerMinuteCurrency FROM `speakintelligentRM`.`tblRateTablePKGRate` WHERE RateTablePKGRateID = V_RateTablePKGRateID;			
		IF(FOUND_ROWS() >0)			
		THEN
			IF(V_PackageCostPerMinute IS NOT NULL)
			THEN
				IF(V_PackageCostPerMinuteCurrency > 0)
				THEN						
					SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_PackageCostPerMinuteCurrency,V_PackageCostPerMinute) INTO V_PackageCostPerMinute;
				END IF;					
				SET V_PackageCostPerMinute = ((V_PackageCostPerMinute/60) * V_CallRecordingDuration);
			END IF;	

			IF(V_RecordingCostPerMinute IS NOT NULL)
			THEN
				IF(V_RecordingCostPerMinuteCurrency > 0)
				THEN						
					SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_RecordingCostPerMinuteCurrency,V_RecordingCostPerMinute) INTO V_RecordingCostPerMinute;
				END IF;					
				SET V_RecordingCostPerMinute = ((V_RecordingCostPerMinute/60) * V_CallRecordingDuration);
			END IF;	
		END IF;		
		-- calculate package cost and recording per minute end
	END IF;
	
	-- calculation outbound cost start
	
	IF(V_CallType = 'Outbound')
	THEN
		SELECT IFNULL(ConnectionFee,0),IFNULL(ConnectionFeeCurrency,0),Interval1,IntervalN,Rate,RateN,IFNULL(RateCurrency,0),IFNULL(MinimumDuration,0)
			INTO V_ConnectionFee,V_ConnectionFeeCurrency,V_Interval1,V_IntervalN,V_Rate,V_RateN,V_RateCurrency,V_MinimumDuration
		FROM `speakintelligentRM`.`tblRateTableRate` WHERE RateTableRateID = V_OutBoundRateTableRateID;
				
		IF(V_ConnectionFee > 0 && V_ConnectionFeeCurrency > 0)
		THEN
			SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_ConnectionFeeCurrency,V_ConnectionFee) INTO V_ConnectionFee;
		END IF;
		
		IF(V_MinimumDuration > V_Duration)
		THEN
			SET V_Duration = V_MinimumDuration;
			SET V_IsMinimumDuration = 1;
		END IF;
				
		IF(V_RateCurrency > 0)
		THEN
			IF(V_Rate IS NOT NULL)
			THEN
				SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_RateCurrency,V_Rate) INTO V_Rate;
			ELSE
				SET V_Rate = 0;	
			END IF;
			
			IF(V_RateN IS NOT NULL)
			THEN
				SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_RateCurrency,V_RateN) INTO V_RateN;
			ELSE
				SET V_RateN = 0;	
			END IF;
		END IF;
	
		-- COST UPDATE START
		
		IF(V_Duration >= V_Interval1) 
		THEN
			SET V_Cost = (V_Rate/60.0) * V_Interval1 + CEILING((V_Duration - V_Interval1)/V_IntervalN) * (V_RateN/60.0) * V_IntervalN + V_ConnectionFee;
			SET V_CostPerMinute = (V_Rate/60.0) * V_Interval1 + CEILING((V_Duration - V_Interval1)/V_IntervalN) * (V_RateN/60.0) * V_IntervalN;
			SET V_CostPerCall = V_ConnectionFee;
		ELSEIF(V_Duration > 0)
		THEN
			SET V_Cost = V_Rate + V_ConnectionFee;
			SET V_CostPerMinute = V_Rate;
			SET V_CostPerCall = V_ConnectionFee;
        ELSE
			SET V_Cost = 0;
			SET V_CostPerMinute = 0;
			SET V_CostPerCall = 0;			
		END IF;
		
		IF(V_Duration >= V_Interval1) 
		THEN
			SET V_BilledDuration = V_Interval1+CEILING((V_Duration-V_Interval1)/V_IntervalN)*V_IntervalN;
		ELSEIF(V_Duration > 0)
		THEN
			SET V_BilledDuration = V_Interval1;
        ELSE
			SET V_BilledDuration = 0;
		END IF;		
		
		-- BILLED DURATION UPDATE
		
		SET V_Cost = V_Cost + V_PackageCostPerMinute + V_RecordingCostPerMinute;
		
		IF(V_OutBoundRateTableID > 0)
		THEN
			SELECT MinimumCallCharge,IFNULL(CurrencyID,0) INTO V_MinimumCallCharge,V_RateCurrency FROM `speakintelligentRM`.`tblRateTable` WHERE RateTableId = V_OutBoundRateTableID;
			IF(V_MinimumCallCharge IS NOT NULL)
			THEN
				
				IF(V_RateCurrency > 0)
				THEN
					SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_RateCurrency,V_MinimumCallCharge) INTO V_MinimumCallCharge;
				END IF;
				
				IF(V_MinimumCallCharge > V_Cost)
				THEN
					SET V_Cost = V_MinimumCallCharge;
					SET V_IsMinimumCallCharge = 1;
				END IF;
			
			END IF;
		END IF;
		
		-- UPDATE COST AND DURATION
		
		UPDATE tblActiveCall 
		SET duration = V_Duration,		
		   billed_duration = V_BilledDuration,
			Cost = IFNULL(V_Cost,0),
			CostPerCall = IFNULL(V_CostPerCall,0),
			CostPerMinute = IFNULL(V_CostPerMinute,0),
			MinimumCallCharge = IFNULL(V_IsMinimumCallCharge,0),
			MinimumDuration = IFNULL(V_IsMinimumDuration,0),
			PackageCostPerMinute = IFNULL(V_PackageCostPerMinute,0),
			RecordingCostPerMinute = IFNULL(V_RecordingCostPerMinute,0),
			updated_at = NOW()
		WHERE ActiveCallID = p_ActiveCallID; 
		
	END IF;
	
	-- calculation outbound cost end		
	
	-- inbound cost start
	
	IF(V_CallType = 'Inbound')
	THEN
		IF(V_InboundRateTableDIDRateID > 0)
		THEN
			IF(V_Duration > 0)
			THEN
				SELECT CostPerCall,IFNULL(CostPerCallCurrency,0),CostPerMinute,IFNULL(CostPerMinuteCurrency,0),SurchargePerCall,IFNULL(SurchargePerCallCurrency,0),SurchargePerMinute,IFNULL(SurchargePerMinuteCurrency,0),OutpaymentPerCall,IFNULL(OutpaymentPerCallCurrency,0),OutpaymentPerMinute,IFNULL(OutpaymentPerMinuteCurrency,0),Surcharges,IFNULL(SurchargesCurrency,0),CollectionCostAmount,IFNULL(CollectionCostAmountCurrency,0),CollectionCostPercentage
					INTO V_CostPerCall,V_CostPerCallCurrency,V_CostPerMinute,V_CostPerMinuteCurrency,V_SurchargePerCall,V_SurchargePerCallCurrency,V_SurchargePerMinute,V_SurchargePerMinuteCurrency,V_OutpaymentPerCall,V_OutpaymentPerCallCurrency,V_OutpaymentPerMinute,V_OutpaymentPerMinuteCurrency,V_Surcharges,V_SurchargesCurrency,V_CollectionCostAmount,V_CollectionCostAmountCurrency,V_CollectionCostPercentage
				FROM `speakintelligentRM`.`tblRateTableDIDRate` WHERE RateTableDIDRateID = V_InboundRateTableDIDRateID;
				
				IF(V_CostPerCall IS NOT NULL)
				THEN
					IF(V_CostPerCallCurrency > 0)
					THEN						
						SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_CostPerCallCurrency,V_CostPerCall) INTO V_CostPerCall;
					END IF;			
				END IF;
				
				IF(V_CostPerMinute IS NOT NULL)
				THEN
					IF(V_CostPerMinuteCurrency > 0)
					THEN						
						SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_CostPerMinuteCurrency,V_CostPerMinute) INTO V_CostPerMinute;
					END IF;					
					SET V_CostPerMinute = ((V_CostPerMinute/60) * V_Duration);
				END IF;
				
				
				IF(V_SurchargePerCall IS NOT NULL)
				THEN
					IF(V_SurchargePerCallCurrency > 0)
					THEN						
						SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_SurchargePerCallCurrency,V_SurchargePerCall) INTO V_SurchargePerCall;
					END IF;		
				END IF;
								
				IF(V_SurchargePerMinute IS NOT NULL)
				THEN
					IF(V_SurchargePerMinuteCurrency > 0)
					THEN						
						SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_SurchargePerMinuteCurrency,V_SurchargePerMinute) INTO V_SurchargePerMinute;
					END IF;					
					SET V_SurchargePerMinute = ((V_SurchargePerMinute/60) * V_Duration);
				END IF;
				
				
				IF(V_OutpaymentPerCall IS NOT NULL)
				THEN
					IF(V_OutpaymentPerCallCurrency > 0)
					THEN						
						SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_OutpaymentPerCallCurrency,V_OutpaymentPerCall) INTO V_OutpaymentPerCall;
					END IF;					
				END IF;
				
				IF(V_OutpaymentPerMinute IS NOT NULL)
				THEN
					IF(V_OutpaymentPerMinuteCurrency > 0)
					THEN						
						SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_OutpaymentPerMinuteCurrency,V_OutpaymentPerMinute) INTO V_OutpaymentPerMinute;
					END IF;					
					SET V_OutpaymentPerMinute = ((V_OutpaymentPerMinute/60) * V_Duration);
				END IF;
				
				
				IF(V_Surcharges IS NOT NULL)
				THEN
					IF(V_SurchargesCurrency > 0)
					THEN						
						SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_SurchargesCurrency,V_Surcharges) INTO V_Surcharges;
					END IF;					
					SET V_Surcharges = ((V_Surcharges/60) * V_Duration);
				END IF;				
				
				IF(V_CollectionCostAmount IS NOT NULL)
				THEN
					IF(V_CollectionCostAmountCurrency > 0)
					THEN						
						SELECT FnConvertCurrencyRate(V_CompanyCurrency,V_AccountCurrency,V_CollectionCostAmountCurrency,V_CollectionCostAmount) INTO V_CollectionCostAmount;
					END IF;			
				END IF;
				
				SET V_Cost = IFNULL(V_PackageCostPerMinute,0) + IFNULL(V_RecordingCostPerMinute,0) + IFNULL(V_CostPerCall,0) + IFNULL(V_CostPerMinute,0) + IFNULL(V_SurchargePerCall,0) + IFNULL(V_SurchargePerMinute,0) + IFNULL(V_Surcharges,0) + IFNULL(V_CollectionCostAmount,0);
				
				
				IF(V_CollectionCostPercentage IS NOT NULL)
				THEN
					IF(V_TaxRateIDs IS NOT NULL && V_TaxRateIDs !='')
					THEN
                     SELECT FnGetCostWithTaxes(V_Cost,V_TaxRateIDs) INTO V_Cost;
               END IF;
					
					SET V_CollectionCostPercentage = V_Cost * (V_CollectionCostPercentage/100);
					SET V_Cost = V_Cost + V_CollectionCostPercentage;
				END IF;
				
			END IF;
		END IF;

		UPDATE tblActiveCall 
		SET billed_duration = V_Duration,
            Cost = IFNULL(V_Cost,0),
            CostPerCall = IFNULL(V_CostPerCall,0),
            CostPerMinute = IFNULL(V_CostPerMinute,0),
            SurchargePerCall = IFNULL(V_SurchargePerCall,0),
            SurchargePerMinute = IFNULL(V_SurchargePerMinute,0),
            OutpaymentPerCall = IFNULL(V_OutpaymentPerCall,0),
            OutpaymentPerMinute = IFNULL(V_OutpaymentPerMinute,0),
            Surcharges = IFNULL(V_Surcharges,0),
            CollectionCostAmount = IFNULL(V_CollectionCostAmount,0),
            CollectionCostPercentage = IFNULL(V_CollectionCostPercentage,0),
            PackageCostPerMinute = IFNULL(V_PackageCostPerMinute,0),
            RecordingCostPerMinute = IFNULL(V_RecordingCostPerMinute,0),
				updated_at = NOW()
		WHERE ActiveCallID = p_ActiveCallID;
					
	END IF;
	-- inbound cost end

				
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_updateActiveCallTimeZones`;
DELIMITER //
CREATE PROCEDURE `prc_updateActiveCallTimeZones`(
	IN `p_connect_time` DATETIME,
	IN `p_disconnect_time` DATETIME,
	IN `p_Type` INT
)
ThisSP:BEGIN

	DECLARE v_timezones_count_ int;
	DECLARE v_pointer_ int;
	DECLARE v_rowCount_ int;
	DECLARE v_TimezonesID_ int;

	DROP TEMPORARY TABLE IF EXISTS tmp_timezones;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_timezones (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TimezonesID INT(11),
		Title VARCHAR(50),
		FromTime VARCHAR(10),
		ToTime VARCHAR(10),
		DaysOfWeek VARCHAR(100),
		DaysOfMonth VARCHAR(100),
		Months VARCHAR(100),
		ApplyIF VARCHAR(100)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Gettimezones;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Gettimezones (
		RowID INT(11),
		TimezonesID INT(11)
	);
	
	INSERT INTO tmp_Gettimezones(RowID,TimezonesID) VALUES(1,0);
	

	IF(p_Type = 1 )
	THEN

	SELECT COUNT(*) INTO v_timezones_count_ FROM speakintelligentRM.tblTimezones WHERE `Status`=1 AND ApplyIF = 'start';


	IF v_timezones_count_ > 1
	THEN

		INSERT INTO tmp_timezones (TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF)
		SELECT
			TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF
		FROM
			speakintelligentRM.tblTimezones
		WHERE
			`Status`=1 AND ApplyIF = 'start';

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_timezones );

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_TimezonesID_ = (SELECT TimezonesID FROM tmp_timezones t WHERE t.RowID = v_pointer_);
			
				UPDATE
					tmp_Gettimezones temp
				JOIN
					tmp_timezones t ON t.TimezonesID = v_TimezonesID_
				SET
					temp.TimezonesID = v_TimezonesID_
				WHERE
				(
					(temp.TimezonesID = 0 OR temp.TimezonesID IS NULL)
					AND
					(
						(t.FromTime = '' AND t.ToTime = '')
						OR
						(
							(
								t.ApplyIF = 'start' AND
								CAST(DATE_FORMAT(p_connect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
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
								FIND_IN_SET(MONTH(p_connect_time), Months) != 0
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
								FIND_IN_SET(DAY(p_connect_time), DaysOfMonth) != 0
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
								FIND_IN_SET(DAYOFWEEK(p_connect_time), DaysOfWeek) != 0
							)
						)
					)
				);
			
			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

	ELSE
		UPDATE tmp_Gettimezones SET TimezonesID=1;
	END IF;
	
	ELSE
	
		SELECT COUNT(*) INTO v_timezones_count_ FROM speakintelligentRM.tblTimezones WHERE `Status`=1;

	IF v_timezones_count_ > 1
	THEN

		INSERT INTO tmp_timezones (TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF)
		SELECT
			TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF
		FROM
			speakintelligentRM.tblTimezones
		WHERE
			`Status`=1;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_timezones);

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_TimezonesID_ = (SELECT TimezonesID FROM tmp_timezones t WHERE t.RowID = v_pointer_);


				UPDATE
					tmp_Gettimezones temp
				JOIN
					tmp_timezones t ON t.TimezonesID = v_TimezonesID_
				SET
					temp.TimezonesID = v_TimezonesID_
				WHERE
				(
					(temp.TimezonesID = 0 OR temp.TimezonesID IS NULL)
					AND
					(
						(t.FromTime = '' AND t.ToTime = '')
						OR
						(
							(
								t.ApplyIF = 'start' AND
								CAST(DATE_FORMAT(p_connect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
							)
							OR
							(
								t.ApplyIF = 'end' AND
								CAST(DATE_FORMAT(p_disconnect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
							)
							OR
							(
								t.ApplyIF = 'both' AND
								CAST(DATE_FORMAT(p_connect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME) AND
								CAST(DATE_FORMAT(p_disconnect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
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
								FIND_IN_SET(MONTH(p_connect_time), Months) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND
								FIND_IN_SET(MONTH(p_disconnect_time), Months) != 0
							)
							OR
							(
								t.ApplyIF = 'both' AND
								FIND_IN_SET(MONTH(p_connect_time), Months) != 0 AND
								FIND_IN_SET(MONTH(p_disconnect_time), Months) != 0
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
								FIND_IN_SET(DAY(p_connect_time), DaysOfMonth) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND
								FIND_IN_SET(DAY(p_disconnect_time), DaysOfMonth) != 0
							)
							OR
							(
								t.ApplyIF = 'both' AND
								FIND_IN_SET(DAY(p_connect_time), DaysOfMonth) != 0 AND
								FIND_IN_SET(DAY(p_disconnect_time), DaysOfMonth) != 0
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
								FIND_IN_SET(DAYOFWEEK(p_connect_time), DaysOfWeek) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND
								FIND_IN_SET(DAYOFWEEK(p_disconnect_time), DaysOfWeek) != 0
							)
							OR
							(
								t.ApplyIF = 'both' AND
								FIND_IN_SET(DAYOFWEEK(p_connect_time), DaysOfWeek) != 0 AND
								FIND_IN_SET(DAYOFWEEK(p_disconnect_time), DaysOfWeek) != 0
							)
						)
					)
				);

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

	ELSE
		UPDATE tmp_Gettimezones SET TimezonesID=1;
	END IF;
		
	END IF;
	
	UPDATE tmp_Gettimezones SET TimezonesID=1 WHERE TimezonesID=0 AND RowID=1;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_updatestartCall`;
DELIMITER //
CREATE PROCEDURE `prc_updatestartCall`(
	IN `p_ActiveCallID` INT,
	IN `p_Type` INT
)
PRC:BEGIN

	DECLARE V_CompanyGatewayID INT DEFAULT 0;
	DECLARE V_CLIRateTableID INT DEFAULT 0;
	DECLARE V_AccountID INT DEFAULT 0;
	DECLARE V_ServiceID INT DEFAULT 0;
	DECLARE V_AccountServiceID INT DEFAULT 0;
	DECLARE V_GatewayAccountPKID INT DEFAULT 0;
	DECLARE V_TimezonesID INT DEFAULT 0;
	DECLARE V_SpecialTimezonesID INT DEFAULT 0;
	DECLARE V_PackageTimezonesID INT DEFAULT 0;
	DECLARE V_SpecialPackageTimezonesID INT DEFAULT 0;
	DECLARE V_ConnectTime DATE;
	
	DECLARE V_PackageId INT DEFAULT 0;
	DECLARE V_PackageRateTableID INT DEFAULT 0;
	DECLARE V_SpecialPackageRateTableID INT DEFAULT 0;
	DECLARE V_Count INT DEFAULT 0;
	DECLARE V_RateTablePKGRateID INT DEFAULT 0;
	
	DECLARE V_TaxRateIDs VARCHAR(50) DEFAULT '';
	DECLARE V_CallType VARCHAR(50) DEFAULT ''; 
	
	DECLARE V_OutBoundRateTableID INT DEFAULT 0;
	DECLARE V_SpecialOutBoundRateTableID INT DEFAULT 0;
	DECLARE V_OutBoundRateTableRateID INT DEFAULT 0;
	
	DECLARE V_InBoundRateTableID INT DEFAULT 0;
	DECLARE V_SpecialInBoundRateTableID INT DEFAULT 0;

	DECLARE V_OriginType VARCHAR(50) DEFAULT '';
	DECLARE V_OriginProvider VARCHAR(50) DEFAULT '';
	DECLARE V_AreaPrefix VARCHAR(50) DEFAULT '';
	DECLARE V_CLIPrefix VARCHAR(50) DEFAULT '';
	DECLARE V_CLDPrefix VARCHAR(50) DEFAULT '';
	
	DECLARE V_CLI VARCHAR(50) DEFAULT '';
	DECLARE V_CLD VARCHAR(50) DEFAULT '';
	
	DECLARE V_CallRecordingDuration INT DEFAULT 0;
	DECLARE V_Duration INT DEFAULT 0;
	DECLARE V_Cost DECIMAL(18,6) DEFAULT 0;
	
	DECLARE V_City VARCHAR(50) DEFAULT '';
	DECLARE V_Tariff VARCHAR(50) DEFAULT '';
	DECLARE V_NoType VARCHAR(50) DEFAULT '';
	DECLARE V_MinimumCallCharge INT DEFAULT 0;
	DECLARE V_MinimumDuration INT DEFAULT 0;
	DECLARE V_OutPaymentVendorID INT DEFAULT 0;
	
	DECLARE V_InboundRateTableDIDRateID INT DEFAULT 0;
	DECLARE V_SpecialTable INT DEFAULT 0;
	
	DECLARE V_connect_time DATETIME;
	DECLARE V_disconnect_time DATETIME;

	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
		
		/** find gateway **/
		
		SELECT IFNULL(cg.CompanyGatewayID,0) INTO V_CompanyGatewayID  FROM speakintelligentRM.tblCompanyGateway cg INNER JOIN speakintelligentRM.tblGateway g ON cg.GatewayID=g.GatewayID WHERE g.Name='ManualCDR' limit 1;
		
		/** FIND Authentication */
	
		SELECT IFNULL(cl.CLIRateTableID,0),IFNULL(cl.AccountServiceID,0),IFNULL(cl.CLI,'') INTO V_CLIRateTableID,V_AccountServiceID,V_CLI FROM speakintelligentRM.tblCLIRateTable cl INNER JOIN tblActiveCall a ON a.CLD = cl.CLI WHERE cl.Status=1 AND NumberStartDate <= DATE_FORMAT(ConnectTime, "%Y-%m-%d") AND NumberEndDate >= DATE_FORMAT(ConnectTime, "%Y-%m-%d") LIMIT 1;
		
		IF(V_AccountServiceID = 0)
		THEN
			INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('CLI not found.');
			SELECT * FROM tmp_Error_;
			LEAVE PRC;		
		END IF;

		SELECT ServiceID,AccountID INTO V_ServiceID,V_AccountID FROM `speakintelligentRM`.`tblAccountService` WHERE AccountServiceID = V_AccountServiceID;
		
		SELECT DATE_FORMAT(ConnectTime, "%Y-%m-%d"),ConnectTime,DisconnectTime INTO V_ConnectTime,V_connect_time,V_disconnect_time FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID;		 
		 
		SELECT IFNULL(GatewayAccountPKID,0) INTO V_GatewayAccountPKID FROM speakintelligentBilling.tblGatewayAccount
		WHERE CompanyGatewayID = V_CompanyGatewayID AND AccountCLI = V_CLI AND GatewayAccountID = V_CLI AND AccountID = V_AccountID AND AccountServiceID = V_AccountServiceID;

		IF FOUND_ROWS() = 0
		THEN

			INSERT INTO speakintelligentBilling.tblGatewayAccount(CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,AccountCLI,ServiceID,AccountServiceID)
			SELECT CompanyID,V_CompanyGatewayID AS CompanyGatewayID,CLI,AccountID,CLI,V_ServiceID AS ServiceID,AccountServiceID FROM speakintelligentRM.tblCLIRateTable
			WHERE CLIRateTableID = V_CLIRateTableID;

			SET V_GatewayAccountPKID = LAST_INSERT_ID();
		
		END IF;
		
		
		/** Find account service package **/		

		SELECT IFNULL(PackageId,0),IFNULL(RateTableID,0),IFNULL(SpecialPackageRateTableID,0) INTO V_PackageId,V_PackageRateTableID,V_SpecialPackageRateTableID FROM `speakintelligentRM`.`tblAccountServicePackage`
		WHERE AccountID = V_AccountID AND AccountServiceID = V_AccountServiceID AND Status = 1 AND PackageStartDate <= V_ConnectTime AND PackageEndDate >= V_ConnectTime LIMIT 1;
		
		/** NEED TO SETUP TIMEZONE GET PROCEDURE **/
		call prc_updateActiveCallTimeZones(V_ConnectTime,V_disconnect_time,p_Type);
		
		SELECT IFNULL(TimezonesID,1) INTO V_TimezonesID  FROM tmp_Gettimezones LIMIT 1;
		
		SET V_SpecialTimezonesID = V_TimezonesID;
		SET V_SpecialPackageTimezonesID = V_TimezonesID;
		 
		IF(V_PackageId > 0) -- Package find start
		THEN		
			IF(V_SpecialPackageRateTableID > 0) -- check special package rate table start
			THEN		
		
				SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.`tblRateTablePKGRate` WHERE RateTableId = V_SpecialPackageRateTableID AND TimezonesID = V_SpecialPackageTimezonesID;
		
				IF(V_Count = 0)
				THEN
					SET V_SpecialPackageTimezonesID = 1;			
				END IF;
		
				SET V_Count = 0;
		
				SELECT IFNULL(r.RateID,0) INTO V_Count
				FROM `speakintelligentRM`.`tblRate` r
					INNER JOIN `speakintelligentRM`.`tblRateTable` rt ON rt.CodeDeckId = r.CodeDeckId
					INNER JOIN `speakintelligentRM`.`tblPackage` p ON p.Name = r.Code
				WHERE rt.RateTableId = V_SpecialPackageRateTableID AND p.PackageId = V_PackageId;
		
				IF(V_Count > 0)
				THEN
					SELECT IFNULL(RateTablePKGRateID,0) INTO V_RateTablePKGRateID FROM `speakintelligentRM`.`tblRateTablePKGRate`
					WHERE RateTableId = V_SpecialPackageRateTableID
					AND TimezonesID = V_SpecialPackageTimezonesID AND RateID = V_Count
					AND ApprovedStatus =1 AND EffectiveDate <= NOW() LIMIT 1;
				END IF;
		
			END IF; -- SPECIAL PACKAGE RATE TABLE CHECK
		
			IF(V_RateTablePKGRateID = 0) -- check package rate table start
			THEN
		
				/** CHECK GLOBAL TIME ZONE **/
				SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.`tblRateTablePKGRate` WHERE RateTableId = V_PackageRateTableID AND TimezonesID = V_TimezonesID;
		
				IF(V_Count = 0)
				THEN
					SET V_TimezonesID = 1;			
				END IF;
		
				SET V_Count = 0;
				SELECT IFNULL(r.RateID,0) INTO V_Count FROM `speakintelligentRM`.`tblRate` r INNER JOIN `speakintelligentRM`.`tblRateTable` rt ON rt.CodeDeckId = r.CodeDeckId INNER JOIN `speakintelligentRM`.`tblPackage` p ON p.Name = r.Code
				WHERE rt.RateTableId = V_PackageRateTableID AND p.PackageId = V_PackageId;
		
				IF(V_Count > 0)
				THEN
					SELECT IFNULL(RateTablePKGRateID,0) INTO V_RateTablePKGRateID FROM `speakintelligentRM`.`tblRateTablePKGRate` WHERE RateTableId = V_PackageRateTableID AND TimezonesID = V_TimezonesID AND RateID = V_Count
					AND ApprovedStatus =1 AND EffectiveDate <= NOW() LIMIT 1;
				END IF;
		
		
			END IF;-- check  package rate table start
		
		END IF;
		IF(V_RateTablePKGRateID > 0) -- set package value start
		THEN				
			SELECT TimezonesID INTO V_PackageTimezonesID FROM `speakintelligentRM`.`tblRateTablePKGRate` WHERE RateTablePKGRateID = V_RateTablePKGRateID;
		ELSE
			SET V_PackageTimezonesID = 0;
			SET V_RateTablePKGRateID = 0;
		END IF; -- set package value start
		
		/** Find account service package end **/
		
		SELECT IFNULL(TaxRateID,'') INTO V_TaxRateIDs FROM `speakintelligentRM`.`tblAccount` WHERE AccountID = V_AccountID AND Billing=1;
	
		
		/** Get All Parameter from request **/
		SELECT CallType,IFNULL(CLI,''),IFNULL(CLD,''),Cost,IFNULL(Duration,0),IFNULL(CallRecordingDuration,0),OriginType,OriginProvider
  	    INTO V_CallType,V_CLI,V_CLD, V_Cost,V_Duration,V_CallRecordingDuration,V_OriginType,V_OriginProvider
		FROM tblActiveCall WHERE ActiveCallID = p_ActiveCallID;
		
		/** Get parameter from cliratetabel authentication **/
		
		SELECT IFNULL(TerminationRateTableID,0),IFNULL(SpecialTerminationRateTableID,0),IFNULL(RateTableID,0),IFNULL(SpecialRateTableID,0),IFNULL(City,''),IFNULL(Tariff,''),IFNULL(Prefix,''),IFNULL(NoType,''),IFNULL(VendorID,0)
		INTO V_OutBoundRateTableID,V_SpecialOutBoundRateTableID,V_InBoundRateTableID,V_SpecialInBoundRateTableID,V_City,V_Tariff,V_AreaPrefix,V_NoType,V_OutPaymentVendorID
		FROM `speakintelligentRM`.`tblCLIRateTable` WHERE CLIRateTableID = V_CLIRateTableID;
		
		SET V_SpecialTable = 0;
		IF(V_CallType = 'Outbound') -- OUTBOUND CALL START
		THEN
			 
			 IF(V_OutBoundRateTableID=0 && V_SpecialOutBoundRateTableID = 0)
			 THEN
				INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Outbound Rate Table not found.');
				SELECT * FROM tmp_Error_;
				LEAVE PRC;		
			 END IF;
			 
			 IF(V_SpecialOutBoundRateTableID > 0) -- Find prefix from special rate table for outbound call start
			 THEN
					SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.`tblRateTableRate` WHERE RateTableId = V_SpecialOutBoundRateTableID AND TimezonesID = V_SpecialTimezonesID;
					
					IF(V_Count = 0)
					THEN
						SET V_SpecialTimezonesID = 1;			
					END IF;
				
					CALL  `speakintelligentRM`.prc_FindApiOutBoundPrefix(V_SpecialOutBoundRateTableID,V_SpecialTimezonesID,V_CLI,V_CLD);
					SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.tmp_RateTableRate3_;
					
					IF(V_Count >0)
					THEN
						SET V_OutBoundRateTableID = V_SpecialOutBoundRateTableID;
						SELECT RateTableRateID,OriginationCode,DestincationCode INTO V_OutBoundRateTableRateID,V_CLIPrefix,V_CLDPrefix FROM `speakintelligentRM`.tmp_RateTableRate3_ LIMIT 1;	
						SET V_SpecialTable = 1;			
					END IF;
					
					
			 END IF; -- Find prefix from special rate table for outbound call end
			 
			 IF(V_SpecialTable = 0) -- Find prefix if special rate table no prefix found start
			 THEN
				SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.`tblRateTableRate` WHERE RateTableId = V_OutBoundRateTableID AND TimezonesID = V_TimezonesID;

						IF(V_Count = 0)
						THEN
							SET V_TimezonesID = 1;			
						END IF;
					
						CALL  `speakintelligentRM`.prc_FindApiOutBoundPrefix(V_OutBoundRateTableID,V_TimezonesID,V_CLI,V_CLD);
						SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.tmp_RateTableRate3_;
						
						IF(V_Count >0)
						THEN						
							SELECT RateTableRateID,OriginationCode,DestincationCode INTO V_OutBoundRateTableRateID,V_CLIPrefix,V_CLDPrefix FROM `speakintelligentRM`.tmp_RateTableRate3_ LIMIT 1;			
						ELSE
							INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Outbound Rate not found.');
							SELECT * FROM tmp_Error_;
							LEAVE PRC;		
						END IF;
			 
			 END IF; -- Find prefix if special rate table no prefix found end
			
			-- update outbound call;
			 
			UPDATE tblActiveCall
			SET CompanyGatewayID = V_CompanyGatewayID,
				 GatewayAccountPKID = V_GatewayAccountPKID,
				 AccountServiceID = V_AccountServiceID,
				 ServiceID = V_ServiceID,
				 CLIPrefix = V_CLIPrefix,
				 CLDPrefix = V_CLDPrefix,
				 RateTableID = V_OutBoundRateTableID,
				 RateTableRateID = V_OutBoundRateTableRateID,
				 RateTableDIDRateID = 0,
				 TimezonesID = V_TimezonesID,
				 Duration = V_Duration,
				 billed_duration = V_Duration,
				 Cost = V_Cost,
				 RateTablePKGRateID = V_RateTablePKGRateID,
				 CallRecordingDuration = V_CallRecordingDuration,
				 AccountServicePackageID = V_PackageId,
				 TaxRateIDs = V_TaxRateIDs,
				 PackageTimezonesID = V_PackageTimezonesID,
				 City = V_City,
				 Tariff = V_Tariff,
				 NoType = V_NoType,
				 MinimumCallCharge = V_MinimumCallCharge,
				 MinimumDuration = V_MinimumDuration,
				 OutPaymentVendorID = V_OutPaymentVendorID,
				 updated_at = NOW()		 
			WHERE ActiveCallID = p_ActiveCallID;			
			 
		END IF; -- OUTBOUND CALL END

		SET V_SpecialTable = 0;
		IF(V_CallType = 'Inbound') -- INBOUND CALL START
		THEN

			IF(V_AreaPrefix = '')
			THEN
				INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('InBound Rate not found.');
				SELECT * FROM tmp_Error_;
				LEAVE PRC;		
			END IF;

			IF(V_SpecialInBoundRateTableID > 0) -- Find prefix from special rate table for inbound call start
			THEN

				SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.`tblRateTableRate` WHERE RateTableId = V_SpecialInBoundRateTableID AND TimezonesID = V_SpecialTimezonesID;

				IF(V_Count = 0)
				THEN
					SET V_SpecialTimezonesID = 1;			
				END IF;

				CALL  `speakintelligentRM`.prc_FindApiInBoundPrefix(V_SpecialInBoundRateTableID,V_SpecialTimezonesID,V_CLI,V_CLD,V_City,V_Tariff,V_OriginType,V_OriginProvider,V_AreaPrefix,V_NoType);
				SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.tmp_RateTableRate3_;

				IF(V_Count >0)
				THEN
					SET V_InBoundRateTableID = V_SpecialInBoundRateTableID;			
					SET V_TimezonesID = V_SpecialTimezonesID;
					
					SELECT RateTableDIDRateID,OriginationCode,DestincationCode INTO V_InboundRateTableDIDRateID,V_CLIPrefix,V_CLDPrefix FROM `speakintelligentRM`.tmp_RateTableRate3_ LIMIT 1;	
					
					SET V_SpecialTable=1;

				END IF;
				
			END IF;	-- Find prefix from special rate table for inbound call end

			IF(V_SpecialTable = 0) -- Find prefix if special rate table no prefix found start
			THEN
				SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.`tblRateTableRate` WHERE RateTableId = V_InBoundRateTableID AND TimezonesID = V_TimezonesID;

				IF(V_Count = 0)
				THEN
					SET V_TimezonesID = 1;			
				END IF;

				CALL  `speakintelligentRM`.prc_FindApiInBoundPrefix(V_InBoundRateTableID,V_TimezonesID,V_CLI,V_CLD,V_City,V_Tariff,V_OriginType,V_OriginProvider,V_AreaPrefix,V_NoType);
				
				SELECT COUNT(*) INTO V_Count FROM `speakintelligentRM`.tmp_RateTableRate3_;

				IF(V_Count >0)
				THEN						
					SELECT RateTableDIDRateID,OriginationCode,DestincationCode INTO V_InboundRateTableDIDRateID,V_CLIPrefix,V_CLDPrefix FROM `speakintelligentRM`.tmp_RateTableRate3_ LIMIT 1;			
				ELSE
					INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('InBound Rate not found.');
					SELECT * FROM tmp_Error_;
					LEAVE PRC;		
				END IF;	
			END IF; -- Find prefix if special rate table no prefix found end
			
			-- update inbound call; 
					 
			UPDATE tblActiveCall
			SET CompanyGatewayID = V_CompanyGatewayID,
				 GatewayAccountPKID = V_GatewayAccountPKID,
				 AccountServiceID = V_AccountServiceID,
				 ServiceID = V_ServiceID,
				 CLIPrefix = V_CLIPrefix,
				 CLDPrefix = V_CLDPrefix,
				 RateTableID = V_InBoundRateTableID,		 
				 RateTableRateID = 0,
				 RateTableDIDRateID = V_InboundRateTableDIDRateID,
				 TimezonesID = V_TimezonesID,
				 Duration = V_Duration,
				 billed_duration = V_Duration,
				 Cost = V_Cost,
				 RateTablePKGRateID = V_RateTablePKGRateID,
				 CallRecordingDuration = V_CallRecordingDuration,
				 AccountServicePackageID = V_PackageId,
				 TaxRateIDs = V_TaxRateIDs,
				 PackageTimezonesID = V_PackageTimezonesID,
				 City = V_City,
				 Tariff = V_Tariff,
				 NoType = V_NoType,
				 MinimumCallCharge = V_MinimumCallCharge,
				 MinimumDuration = V_MinimumDuration,
				 OutPaymentVendorID = V_OutPaymentVendorID,
				 updated_at = NOW()
			WHERE ActiveCallID = p_ActiveCallID;
			
		END IF; -- INBOUND CALL END
				
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;