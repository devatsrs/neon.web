USE `speakIntelligentRoutingEngine`;

DROP PROCEDURE IF EXISTS `prc_blockApiCall`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_blockApiCall`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_UUID` TEXT,
	IN `p_BlockReason` VARCHAR(255)
)
PRC:BEGIN

	DECLARE V_ActiveCallID INT DEFAULT 0;
	DECLARE v_AccountID INT;
	DECLARE v_BillingType INT;
	DECLARE V_Balance DECIMAL(18,6);
	DECLARE V_connect_time DATETIME;
	DECLARE V_Duration INT;
	DECLARE i INT;
	DECLARE v_pointer_ INT ;
	DECLARE v_rowCount_ INT ;
	DECLARE V_UUID VARCHAR(255);
	DECLARE p_DisconnectTime DATETIME;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	SET p_DisconnectTime = NOW();	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
		CREATE TEMPORARY TABLE tmp_Error_ (
			ErrorMessage longtext
		);
	
		
	IF p_AccountID > 0 THEN
		
			SELECT AccountID INTO v_AccountID FROM tblAccount WHERE AccountID = p_AccountID AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountNo != '' THEN
		
			SELECT AccountID INTO v_AccountID FROM tblAccount WHERE `Number` = p_AccountNo AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountDynamicField = 'CustomerID' AND p_AccountDynamicFieldValue != '' THEN
		
			SELECT AccountID INTO v_AccountID FROM tblAccount WHERE `CustomerID` = p_AccountDynamicFieldValue AND Status = 1 limit 1 ;
			
		END IF;	
		
	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
		
	END IF;	

	
	DROP TEMPORARY TABLE IF EXISTS `tempuuid`; 
		CREATE TEMPORARY TABLE `tempuuid` (
		  `splitted_column` varchar(45) NOT NULL,
		  RowNo INT
		);
	
	SET i = 1;
	REPEAT
			INSERT INTO tempuuid
			SELECT FnStringSplit(p_UUID, ',', i),i WHERE FnStringSplit(p_UUID, ',', i) IS NOT NULL LIMIT 1;
			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tempuuid);

	WHILE v_pointer_ <= v_rowCount_
	DO
		SET V_UUID = ( SELECT splitted_column FROM tempuuid  WHERE RowNo = v_pointer_ );		
		
		SELECT ActiveCallID,ConnectTime INTO V_ActiveCallID,V_connect_time FROM tblActiveCall WHERE UUID = V_UUID AND AccountID = p_AccountID AND EndCall=0  limit 1;

		IF (V_ActiveCallID > 0 )
		THEN
		
			CALL prc_insertActiveCallCost(V_ActiveCallID,2,p_DisconnectTime,p_BlockReason);
		
		END IF;			
		
	SET v_pointer_ = v_pointer_ + 1;

	END WHILE;
	
	SELECT 0 AS duration;
	
	/*	
	
	
	SELECT TIMESTAMPDIFF(SECOND,V_connect_time,p_DisconnectTime) INTO V_Duration;
	
	-- DELETE FROM tblActiveCall WHERE ActiveCallID = V_ActiveCallID;
	
	SELECT V_Duration AS duration;
	
	*/
		
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_endApiCall`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_endApiCall`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_UUID` VARCHAR(250)
)
PRC:BEGIN

	DECLARE V_ActiveCallID INT DEFAULT 0;
	DECLARE v_AccountID INT;
	DECLARE v_BillingType INT;
	DECLARE V_Balance DECIMAL(18,6);
	DECLARE V_connect_time DATETIME;
	DECLARE V_Duration INT;
	DECLARE p_DisconnectTime DATETIME;
	DECLARE V_Cost DECIMAL(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;	
		
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
		CREATE TEMPORARY TABLE tmp_Error_ (
			ErrorMessage longtext
		);
	
		
	IF p_AccountID > 0 THEN
		
			SELECT AccountID INTO v_AccountID from tblAccount where AccountID = p_AccountID AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountNo != '' THEN
		
			SELECT AccountID INTO v_AccountID from tblAccount where `Number` = p_AccountNo AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountDynamicField = 'CustomerID'  AND p_AccountDynamicFieldValue != '' 
		THEN
		
			SELECT AccountID INTO v_AccountID FROM tblAccount WHERE `CustomerID` = p_AccountDynamicFieldValue AND Status = 1 limit 1 ;
			
		END IF;	
		
	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
		
	END IF;	
		
	SELECT ActiveCallID,ConnectTime INTO V_ActiveCallID,V_connect_time FROM tblActiveCall WHERE UUID = p_UUID AND AccountID = v_AccountID AND EndCall=0 limit 1;
	IF (V_ActiveCallID = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Record Not Found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;			
	
	SET p_DisconnectTime = NOW();

	CALL prc_insertActiveCallCost(V_ActiveCallID,1,p_DisconnectTime,'');
	
	SELECT TIMESTAMPDIFF(SECOND,V_connect_time,p_DisconnectTime) INTO V_Duration;
	
	SELECT Cost INTO V_Cost FROM tblActiveCall where ActiveCallID=V_ActiveCallID;
	
	SELECT V_Duration AS duration,V_Cost AS cost;
	
		
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
	IN `p_UUID` VARCHAR(250)
)
PRC:BEGIN

	DECLARE V_UUID_Duplicate INT;
	DECLARE v_AccountID INT;
	DECLARE v_BillingType INT;
	DECLARE V_Balance DECIMAL(18,6);
	DECLARE p_CallRecordingStartTime DATETIME;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
		CREATE TEMPORARY TABLE tmp_Error_ (
			ErrorMessage longtext
		);
	
		
	IF p_AccountID > 0 THEN
		
			SELECT AccountID INTO v_AccountID FROM tblAccount WHERE AccountID = p_AccountID AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountNo != '' THEN
		
			SELECT AccountID INTO v_AccountID FROM tblAccount WHERE `Number` = p_AccountNo AND Status = 1 limit 1 ;
		
		ELSEIF p_AccountDynamicField = 'CustomerID' AND p_AccountDynamicFieldValue != '' THEN
		
			SELECT AccountID INTO v_AccountID FROM tblAccount WHERE `CustomerID` = p_AccountDynamicFieldValue AND Status = 1 limit 1 ;
			
		END IF;	
		
	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
		
	END IF;	
		
	SELECT COUNT(*) INTO V_UUID_Duplicate FROM tblActiveCall WHERE UUID = p_UUID AND AccountID = p_AccountID AND EndCall=0;
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

	SET p_CallRecordingStartTime = NOW();
	
	UPDATE tblActiveCall
	SET CallRecordingStartTime = p_CallRecordingStartTime,CallRecording = 1,updated_by = 'API',updated_at = NOW()
	WHERE UUID = p_UUID AND AccountID = p_AccountID;
		
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_startCall`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_startCall`(
	IN `p_AccountID` INT,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_UUID` VARCHAR(250),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_ServiceNumber` INT,
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
DECLARE V_AccountBalance DECIMAL(18,6) DEFAULT 0;
DECLARE V_ActiveCallCost DECIMAL(18,6) DEFAULT 0;
DECLARE p_ConnectTime DATETIME;

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
		SELECT AccountID,CompanyID INTO v_AccountID,v_CompanyID FROM tblAccount WHERE AccountID = p_AccountID AND Status = 1 limit 1 ;
	ELSEIF p_AccountNo != ''
	THEN
		SELECT AccountID,CompanyID INTO v_AccountID,v_CompanyID FROM tblAccount WHERE `Number` = p_AccountNo AND Status = 1 limit 1 ;
	ELSEIF p_AccountDynamicField = 'CustomerID'  AND p_AccountDynamicFieldValue != ''
	THEN
		SELECT AccountID,CompanyID INTO v_AccountID,v_CompanyID FROM tblAccount WHERE `CustomerID` = p_AccountDynamicFieldValue AND Status = 1 limit 1 ;
	END IF;	

	IF (v_AccountID IS NULL OR v_AccountID = 0 )
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('AccountID not found');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	END IF;	
	
	IF (p_VendorID > 0 )
	THEN
		SELECT COUNT(*) INTO v_Check_Vendor FROM tblAccount WHERE AccountID = p_VendorID;
		IF (v_Check_Vendor = 0 )
		THEN
			INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Vendor Account Not Found.');
			SELECT * FROM tmp_Error_;
			LEAVE PRC;
		END IF;	
	END IF;
	
	
	/** Check Account exits or not - end **/
	
	/** Check Account Balance is sufficent or not -start **/
	
	SELECT IFNULL(BalanceAmount,0) INTO V_AccountBalance FROM tblAPIAccountBalance WHERE AccountID=v_AccountID;
	SELECT IFNULL(SUM(Cost),0) INTO V_ActiveCallCost FROM tblActiveCall WHERE AccountID=v_AccountID;
	
	SET V_Balance = V_AccountBalance - V_ActiveCallCost;
			
	IF(V_Balance <= 0)
	THEN
		INSERT INTO tmp_Error_ (ErrorMessage) VALUES ('Account has not sufficient balance.');
		SELECT * FROM tmp_Error_;
		LEAVE PRC;
	
	END IF;
		
	/** Check Account Balance is sufficent or not -end **/	
	
	SET p_ConnectTime = NOW();
	
	INSERT INTO tblActiveCall(AccountID,CompanyID,ConnectTime,CLI,CLD,ServiceNumber,CallType,UUID,VendorID,VendorConnectionName,OriginType,OriginProvider,VendorRate,VendorCLIPrefix,VendorCLDPrefix,CallRecording,Cost,Duration,billed_duration,EndCall,created_by,created_at,updated_at)
	VALUES(v_AccountID,v_CompanyID,p_ConnectTime,p_CLI,p_CLD,p_ServiceNumber,p_CallType,p_UUID,p_VendorID,p_VendorConnectionName,REPLACE(p_OriginType,'-',''),REPLACE(p_OriginProvider,'-',''),p_VendorRate,p_VendorCLIPrefix,p_VendorCLDPrefix,0,0,0,0,0,'API',NOW(),NOW());
	
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