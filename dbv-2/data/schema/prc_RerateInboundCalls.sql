CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_RerateInboundCalls`(
	IN `p_CompanyID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_InboundTableID` INT
)
BEGIN

	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_ServiceID_ INT;
	DECLARE v_cld_ VARCHAR(500);
	DECLARE v_CustomerIDs_ TEXT DEFAULT '';
	DECLARE v_CustomerIDs_Count_ INT DEFAULT 0;
	DECLARE v_InboundTableID_ INT;

	SELECT GROUP_CONCAT(AccountID) INTO v_CustomerIDs_ FROM tmp_Customers_ GROUP BY CompanyGatewayID;
	SELECT COUNT(*) INTO v_CustomerIDs_Count_ FROM tmp_Customers_;

	IF p_RateCDR = 1
	THEN

	
		IF ( SELECT COUNT(*) FROM tmp_Service_ ) > 0
		THEN


			DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
			CREATE TEMPORARY TABLE tmp_Account_  (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				AccountID INT,
				ServiceID INT,
				cld VARCHAR(500) NULL DEFAULT NULL
			);
			SET @stm = CONCAT('
			INSERT INTO tmp_Account_(AccountID,ServiceID,cld)
			SELECT DISTINCT AccountID,ServiceID,"" FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 1;
			');

			PREPARE stm FROM @stm;
			EXECUTE stm;
			DEALLOCATE PREPARE stm;

		ELSE


			DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
			CREATE TEMPORARY TABLE tmp_Account_  (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				AccountID INT,
				ServiceID INT,
				cld VARCHAR(500) NULL DEFAULT NULL
			);
			SET @stm = CONCAT('
			INSERT INTO tmp_Account_(AccountID,ServiceID,cld)
			SELECT DISTINCT AccountID,ServiceID,"" FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 1;
			');

			PREPARE stm FROM @stm;
			EXECUTE stm;
			DEALLOCATE PREPARE stm;

		END IF;
		
		IF (SELECT COUNT(*) FROM NeonRMDev.tblCLIRateTable WHERE CompanyID = p_CompanyID AND RateTableID > 0) > 0
		THEN

			SET @stm = CONCAT('
			INSERT INTO tmp_Account_(AccountID,ServiceID,cld)
			SELECT DISTINCT ud.AccountID,ud.ServiceID,cld FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud 
			INNER JOIN NeonRMDev.tblCLIRateTable ON ud.cld = tblCLIRateTable.CLI
				AND ud.AccountID = tblCLIRateTable.AccountID
				AND ud.ServiceID = tblCLIRateTable.ServiceID
			WHERE ProcessID="' , p_processId , '" AND ud.AccountID IS NOT NULL AND ud.is_inbound = 1;
			');

			PREPARE stm FROM @stm;
			EXECUTE stm;
			DEALLOCATE PREPARE stm;
			
		END IF;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_Account_);

		IF p_InboundTableID > 0
		THEN

			CALL NeonRMDev.prc_getCustomerInboundRate(v_AccountID_,p_RateCDR,p_RateMethod,p_SpecifyRate,v_cld_,p_InboundTableID);
		END IF;

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_AccountID_ = (SELECT AccountID FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
			SET v_ServiceID_ = (SELECT ServiceID FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
			SET v_cld_ = (SELECT cld FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
			
			
			IF (v_CustomerIDs_Count_=0 OR (v_CustomerIDs_Count_>0 AND FIND_IN_SET(v_AccountID_,v_CustomerIDs_)>0))
			THEN
				IF p_InboundTableID =  0
				THEN

					SET v_InboundTableID_ = (SELECT RateTableID FROM NeonRMDev.tblAccountTariff  WHERE AccountID = v_AccountID_ AND ServiceID = v_ServiceID_ AND Type = 2 LIMIT 1);
					SET v_InboundTableID_ = IFNULL(v_InboundTableID_,0);

					CALL NeonRMDev.prc_getCustomerInboundRate(v_AccountID_,p_RateCDR,p_RateMethod,p_SpecifyRate,v_cld_,v_InboundTableID_);
					
					SET v_InboundTableID_ = 0;
					
				END IF;
				
				IF v_cld_ != ''
				THEN
			
					SET @stm = CONCAT('
					UPDATE NeonCDRDev.`' , p_tbltempusagedetail_name , '` 
					SET area_prefix = "Other"
					WHERE ProcessID="' , p_processId , '" AND AccountID = "' , v_AccountID_ , '" AND ServiceID = "' , v_ServiceID_ , '" AND cld = "' , v_cld_ , '"  AND is_inbound = 1;
					');
			
					PREPARE stm FROM @stm;
					EXECUTE stm;
					DEALLOCATE PREPARE stm;
					
				END IF;


				CALL prc_updateInboundPrefix(v_AccountID_, p_processId, p_tbltempusagedetail_name,v_cld_,v_ServiceID_);


				CALL prc_updateInboundRate(v_AccountID_, p_processId, p_tbltempusagedetail_name,v_cld_,v_ServiceID_,p_RateMethod,p_SpecifyRate);
			END IF;

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

	END IF;

END