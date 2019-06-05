USE `speakIntelligentRoutingEngine`;

ALTER TABLE `tblActiveCall`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `PackageTimezonesID`;
	
UPDATE tblActiveCall SET City='' WHERE City IS NULL;

ALTER TABLE `tblActiveCall`
	CHANGE COLUMN `City` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `PackageTimezonesID`;	
	
ALTER TABLE `tblActiveCall`
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`;


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