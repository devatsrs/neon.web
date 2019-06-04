USE `speakintelligentRM`;
INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES ( 1, NULL, 'API Balance Update', 'apibalanceupdate', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2019-01-09 03:35:53', 'System');

USE `speakIntelligentRoutingEngine`;

DROP PROCEDURE IF EXISTS `prc_CreateAPIAccountBalance`;
DELIMITER //
CREATE PROCEDURE `prc_CreateAPIAccountBalance`()
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS `temp_tblAPIAccountBalance`;
	CREATE TEMPORARY TABLE `temp_tblAPIAccountBalance` (
		`AccountBalanceID` INT(11) NOT NULL AUTO_INCREMENT,		
		`AccountID` INT(11) NOT NULL,
		`BillingType` INT NOT NULL,
		`BalanceAmount` DECIMAL(18,6) NOT NULL,
		PRIMARY KEY (`AccountBalanceID`),
		UNIQUE INDEX `AccountID` (`AccountID`)
	)
	;
	
	INSERT INTO temp_tblAPIAccountBalance(AccountID,BillingType,BalanceAmount)	
	SELECT a.AccountID,IFNULL(ab.BillingType,2) AS BillingType,0 AS BalanceAmount
	FROM `speakintelligentRM`.`tblAccount`	a
		LEFT JOIN `speakintelligentRM`.`tblAccountBilling` ab
			ON ab.AccountID  = a.AccountID  AND ab.AccountServiceID = 0 	
	where a.Status = 1 and a.AccountType =1;
	
	/* postpaid */
	UPDATE temp_tblAPIAccountBalance ta INNER JOIN `speakintelligentRM`.`tblAccountBalance` ab ON ta.AccountID = ab.AccountID
	SET ta.BalanceAmount = ab.BalanceAmount
	WHERE ta.BillingType = 2; 
	
	/* prepaid */
	UPDATE temp_tblAPIAccountBalance ta INNER JOIN `speakintelligentRM`.`tblAccountBalanceLog` ab ON ta.AccountID = ab.AccountID
	SET ta.BalanceAmount = ab.BalanceAmount
	WHERE ta.BillingType = 1;
	
	DROP TABLE IF EXISTS `tblAPIAccountBalance`;
	CREATE TABLE `tblAPIAccountBalance` (
		`AccountBalanceID` INT(11) NOT NULL AUTO_INCREMENT,		
		`AccountID` INT(11) NOT NULL,
		`BillingType` INT NOT NULL,
		`BalanceAmount` DECIMAL(18,6) NOT NULL,
		PRIMARY KEY (`AccountBalanceID`),
		UNIQUE INDEX `AccountID` (`AccountID`)
	)	;
	
	INSERT INTO tblAPIAccountBalance
	SELECT * FROM temp_tblAPIAccountBalance;
	
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
			
			INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found');
			SELECT * FROM tmp_Error_;
			LEAVE PRC;
			
	END IF;	
		
	
	SELECT IFNULL(BalanceAmount,0) INTO V_Balance FROM tblAPIAccountBalance WHERE AccountID=v_AccountID;	
	SELECT IF( V_Balance > 0 , 1 ,0 ) as has_balance , V_Balance AS BalanceAmount;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;