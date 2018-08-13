USE `Ratemanagement3`;

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`)
VALUES (1, NULL, 'Process Call Charges', 'processcallcharges', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, NULL, NULL);

UPDATE tblCronJobCommand SET Title='PBX Account Block' WHERE Title='Mirta Account Block';

INSERT INTO `tblEmailTemplate` (`CompanyID`, `LanguageID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (1, 43, 'PBX Account Block Email', '{{AccountName}} - PBX Account Status Changed', '<p>Hi<br></p><p>Account&nbsp; Current Status is {{AccountBlocked}}.</p><p>Regards,</p><p>{{CompanyName}}<br></p>', '2018-05-22 16:42:31', 'System', '2018-05-28 15:38:59', 'System', NULL, 0, '', 1, 'PBXAccountBlockEmail', 1, 0, 0);
INSERT INTO `tblEmailTemplate` (`CompanyID`, `LanguageID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (1, 43, 'PBX Account UnBlock Email', '{{AccountName}} - PBX Account Status Changed', '<p>Hi<br></p><p>Account&nbsp; Current Status is {{AccountBlocked}}.</p><p>Regards,</p><p>{{CompanyName}}<br></p>', '2018-05-22 16:42:31', 'System', '2018-05-28 15:38:59', 'System', NULL, 0, '', 1, 'PBXAccountUnBlockEmail', 1, 0, 0);

ALTER TABLE `tblAccountDiscountPlan`
	ADD COLUMN `AccountSubscriptionID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`,
	ADD COLUMN `AccountName` VARCHAR(255) NULL DEFAULT NULL AFTER `AccountSubscriptionID`,
	ADD COLUMN `AccountCLI` VARCHAR(255) NULL DEFAULT NULL AFTER `AccountName`,
	ADD COLUMN `SubscriptionDiscountPlanID` INT NULL DEFAULT '0' AFTER `AccountCLI`;
	
ALTER TABLE `tblAccountDiscountPlanHistory`
	ADD COLUMN `AccountSubscriptionID` INT NULL DEFAULT '0' AFTER `ServiceID`,
	ADD COLUMN `AccountName` VARCHAR(255) NULL DEFAULT NULL AFTER `AccountSubscriptionID`,
	ADD COLUMN `AccountCLI` VARCHAR(50) NULL DEFAULT NULL AFTER `AccountName`,
	ADD COLUMN `SubscriptionDiscountPlanID` INT NULL DEFAULT '0' AFTER `AccountCLI`;	
	
ALTER TABLE `tblAccountDiscountPlan`
	DROP INDEX `AccountID`,
	Add UNIQUE INDEX `AccountID` (`Type`, `AccountID`, `ServiceID`, `AccountSubscriptionID`, `AccountName`, `AccountCLI`, `SubscriptionDiscountPlanID`);	
	
DROP TABLE IF EXISTS `tblSubscriptionDiscountPlan`;	
CREATE TABLE `tblSubscriptionDiscountPlan` (
	`SubscriptionDiscountPlanID` INT(11) NOT NULL AUTO_INCREMENT,
	`AccountID` INT(11) NULL DEFAULT '0',
	`ServiceID` INT(11) NULL DEFAULT '0',
	`AccountSubscriptionID` INT(11) NULL DEFAULT '0',
	`AccountName` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`AccountCLI` VARCHAR(300) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`InboundDiscountPlans` INT(11) NULL DEFAULT '0',
	`OutboundDiscountPlans` INT(11) NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`SubscriptionDiscountPlanID`),
	UNIQUE INDEX `IX_UNIQUE_ACCOUNTCLI` (`AccountCLI`),
	UNIQUE INDEX `IX_UNIQUE_ACCOUNTNAME` (`AccountName`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;	

DROP PROCEDURE IF EXISTS `prc_setAccountDiscountPlan`;
DELIMITER //
CREATE PROCEDURE `prc_setAccountDiscountPlan`(
	IN `p_AccountID` INT,
	IN `p_DiscountPlanID` INT,
	IN `p_Type` INT,
	IN `p_BillingDays` INT,
	IN `p_DayDiff` INT,
	IN `p_CreatedBy` VARCHAR(50),
	IN `p_Today` DATETIME,
	IN `p_ServiceID` INT,
	IN `p_AccountSubscriptionID` INT,
	IN `p_AccountName` VARCHAR(255),
	IN `p_AccountCLI` VARCHAR(255),
	IN `p_SubscriptionDiscountPlanID` INT
)
BEGIN
	
	DECLARE v_AccountDiscountPlanID INT;
	DECLARE v_StartDate DATE;
	DECLARE v_EndDate DATE;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF (SELECT COUNT(*) FROM tblAccountBilling WHERE AccountID = p_AccountID AND ServiceID = p_ServiceID) > 0
	THEN
		SELECT StartDate,EndDate INTO v_StartDate,v_EndDate FROM tblAccountBillingPeriod WHERE AccountID = p_AccountID AND ServiceID = p_ServiceID AND StartDate <= DATE(p_Today) AND EndDate > DATE(p_Today);
	ELSE
		SELECT StartDate,EndDate INTO v_StartDate,v_EndDate FROM tblAccountBillingPeriod WHERE AccountID = p_AccountID AND ServiceID = 0 AND StartDate <= DATE(p_Today) AND EndDate > DATE(p_Today);
	END IF;
	

	INSERT INTO tblAccountDiscountPlanHistory(AccountID,AccountDiscountPlanID,DiscountPlanID,Type,CreatedBy,Applied,Changed,StartDate,EndDate,ServiceID,AccountSubscriptionID,AccountName,AccountCLI,SubscriptionDiscountPlanID)
	SELECT AccountID,AccountDiscountPlanID,DiscountPlanID,Type,CreatedBy,created_at,p_Today,StartDate,EndDate,ServiceID,AccountSubscriptionID,AccountName,AccountCLI,SubscriptionDiscountPlanID 
		FROM tblAccountDiscountPlan
	WHERE AccountID = p_AccountID 
			AND ServiceID = p_ServiceID
			AND Type = p_Type
			AND AccountSubscriptionID=p_AccountSubscriptionID
			AND AccountName=p_AccountName
			AND AccountCLI=p_AccountCLI
			AND SubscriptionDiscountPlanID=p_SubscriptionDiscountPlanID;
	

	INSERT INTO tblAccountDiscountSchemeHistory (AccountDiscountSchemeID,AccountDiscountPlanID,DiscountID,Threshold,Discount,Unlimited,SecondsUsed)
	SELECT ads.AccountDiscountSchemeID,ads.AccountDiscountPlanID,ads.DiscountID,ads.Threshold,ads.Discount,ads.Unlimited,ads.SecondsUsed 
	FROM tblAccountDiscountScheme ads
	INNER JOIN tblAccountDiscountPlan adp
		ON adp.AccountDiscountPlanID = ads.AccountDiscountPlanID
	WHERE AccountID = p_AccountID 
		AND adp.ServiceID = p_ServiceID
		AND Type = p_Type
		AND AccountSubscriptionID=p_AccountSubscriptionID
		AND AccountName=p_AccountName
		AND AccountCLI=p_AccountCLI
		AND SubscriptionDiscountPlanID=p_SubscriptionDiscountPlanID;
	
	DELETE ads FROM tblAccountDiscountScheme ads
	INNER JOIN tblAccountDiscountPlan adp
		ON adp.AccountDiscountPlanID = ads.AccountDiscountPlanID
	WHERE AccountID = p_AccountID 
	   AND adp.ServiceID = p_ServiceID
		AND Type = p_Type
		AND AccountSubscriptionID=p_AccountSubscriptionID
		AND AccountName=p_AccountName
		AND AccountCLI=p_AccountCLI
		AND SubscriptionDiscountPlanID=p_SubscriptionDiscountPlanID;
		
	DELETE FROM tblAccountDiscountPlan
	WHERE AccountID = p_AccountID
			AND ServiceID = p_ServiceID
			AND Type = p_Type
			AND AccountSubscriptionID=p_AccountSubscriptionID
			AND AccountName=p_AccountName
			AND AccountCLI=p_AccountCLI
			AND SubscriptionDiscountPlanID=p_SubscriptionDiscountPlanID; 
	
	IF p_DiscountPlanID > 0
	THEN
	 
		INSERT INTO tblAccountDiscountPlan (AccountID,DiscountPlanID,Type,CreatedBy,created_at,StartDate,EndDate,ServiceID,AccountSubscriptionID,AccountName,AccountCLI,SubscriptionDiscountPlanID)
		VALUES (p_AccountID,p_DiscountPlanID,p_Type,p_CreatedBy,p_Today,v_StartDate,v_EndDate,p_ServiceID,p_AccountSubscriptionID,p_AccountName,p_AccountCLI,p_SubscriptionDiscountPlanID);
		
		SET v_AccountDiscountPlanID = LAST_INSERT_ID(); 
		
		INSERT INTO tblAccountDiscountScheme(AccountDiscountPlanID,DiscountID,Threshold,Discount,Unlimited)
		SELECT v_AccountDiscountPlanID,d.DiscountID,Threshold*(p_DayDiff/p_BillingDays),Discount,Unlimited
		FROM tblDiscountPlan dp
		INNER JOIN tblDiscount d 
			ON d.DiscountPlanID = dp.DiscountPlanID
		INNER JOIN tblDiscountScheme ds
			ON ds.DiscountID = d.DiscountID
		WHERE dp.DiscountPlanID = p_DiscountPlanID;
	
	END IF;
  
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_ProcessDiscountPlan`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_ProcessDiscountPlan`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN
	
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;	
	DECLARE v_AccountID_ INT;
	DECLARE v_ServiceID_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
		
	DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
	CREATE TEMPORARY TABLE tmp_Accounts_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		ServiceID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Accounts_(AccountID,ServiceID)
	SELECT DISTINCT ud.AccountID,ud.ServiceID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
	INNER JOIN tblAccountDiscountPlan adp
		ON ud.AccountID = adp.AccountID 
		AND ud.ServiceID = adp.ServiceID
		AND Type = 1
	WHERE ProcessID="' , p_processId , '" AND ud.is_inbound = 0;
	');
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	

	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_Accounts_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_AccountID_ = (SELECT AccountID FROM tmp_Accounts_ t WHERE t.RowID = v_pointer_);
		SET v_ServiceID_ = (SELECT ServiceID FROM tmp_Accounts_ t WHERE t.RowID = v_pointer_);
			
		 CALL prc_putDiscountPlan(v_AccountID_,p_tbltempusagedetail_name,p_processId,0,v_ServiceID_);
		
		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;
	
	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
	CREATE TEMPORARY TABLE tmp_Accounts_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		ServiceID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Accounts_(AccountID,ServiceID)
	SELECT DISTINCT ud.AccountID,ud.ServiceID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
	INNER JOIN tblAccountDiscountPlan adp
		ON ud.AccountID = adp.AccountID 
		AND ud.ServiceID = adp.ServiceID
		AND Type = 2
	WHERE ProcessID="' , p_processId , '" AND ud.is_inbound = 1;
	');
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
		
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_Accounts_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_AccountID_ = (SELECT AccountID FROM tmp_Accounts_ t WHERE t.RowID = v_pointer_);
		SET v_ServiceID_ = (SELECT ServiceID FROM tmp_Accounts_ t WHERE t.RowID = v_pointer_);
		
		 CALL prc_putDiscountPlan(v_AccountID_,p_tbltempusagedetail_name,p_processId,1,v_ServiceID_);
		
		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_putDiscountPlan`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_putDiscountPlan`(
	IN `p_AccountID` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_processId` INT,
	IN `p_inbound` INT,
	IN `p_ServiceID` INT
)
ThisSP:BEGIN

	DECLARE v_pointer_ INT;
	DECLARE v_pointer_AccountName_ INT;
	DECLARE v_pointer_AccountCLI_ INT;
	DECLARE v_rowCount_AccountName_ INT;
	DECLARE v_rowCount_AccountCLI_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_ServiceID_ INT;
	DECLARE v_AccountDiscountPlanID_ INT;
	

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_Discount_AccountName;
	CREATE TEMPORARY TABLE tmp_Discount_AccountName  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		AccountDiscountPlanID INT,
		ServiceID INT
	);
	
		INSERT INTO tmp_Discount_AccountName(AccountID,AccountDiscountPlanID,ServiceID)
		SELECT AccountID,AccountDiscountPlanID,ServiceID
		FROM tblAccountDiscountPlan
		WHERE AccountID=p_AccountID
				AND ( (p_inbound = 0 AND Type = 1) OR  (p_inbound = 1 AND Type = 2 ) )
		      AND ServiceID=p_ServiceID
				AND AccountName!='';
		
		SET v_pointer_AccountName_ = 1;
		SET v_rowCount_AccountName_ = (SELECT COUNT(*)FROM tmp_Discount_AccountName);

		WHILE v_pointer_AccountName_ <= v_rowCount_AccountName_
		DO

			SET v_AccountID_ = (SELECT AccountID FROM tmp_Discount_AccountName t WHERE t.RowID = v_pointer_AccountName_);
			SET v_ServiceID_ = (SELECT ServiceID FROM tmp_Discount_AccountName t WHERE t.RowID = v_pointer_AccountName_);
			SET v_AccountDiscountPlanID_ = (SELECT AccountDiscountPlanID FROM tmp_Discount_AccountName t WHERE t.RowID = v_pointer_AccountName_);

			 CALL prc_applyAccountDiscountPlan(v_AccountID_, p_tbltempusagedetail_name, p_processId, p_inbound, v_ServiceID_, v_AccountDiscountPlanID_, 1, 0);
			
			SET v_pointer_AccountName_ = v_pointer_AccountName_ + 1;
		END WHILE;


	DROP TEMPORARY TABLE IF EXISTS tmp_Discount_AccountCLI;
	CREATE TEMPORARY TABLE tmp_Discount_AccountCLI  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		AccountDiscountPlanID INT,
		ServiceID INT
	);

	INSERT INTO tmp_Discount_AccountCLI(AccountID,AccountDiscountPlanID,ServiceID)
	SELECT AccountID,AccountDiscountPlanID,ServiceID FROM tblAccountDiscountPlan where AccountID=p_AccountID AND ( (p_inbound = 0 AND Type = 1) OR  (p_inbound = 1 AND Type = 2 ) ) AND ServiceID=p_ServiceID AND AccountCLI!='';
	
	SET v_pointer_AccountCLI_ = 1;
	SET v_rowCount_AccountCLI_ = (SELECT COUNT(*)FROM tmp_Discount_AccountCLI);

	WHILE v_pointer_AccountCLI_ <= v_rowCount_AccountCLI_
	DO

		SET v_AccountID_ = (SELECT AccountID FROM tmp_Discount_AccountCLI t WHERE t.RowID = v_pointer_AccountCLI_);
		SET v_ServiceID_ = (SELECT ServiceID FROM tmp_Discount_AccountCLI t WHERE t.RowID = v_pointer_AccountCLI_);
		SET v_AccountDiscountPlanID_ = (SELECT AccountDiscountPlanID FROM tmp_Discount_AccountCLI t WHERE t.RowID = v_pointer_AccountName_);
		
		 CALL prc_applyAccountDiscountPlan(v_AccountID_,p_tbltempusagedetail_name,p_processId,p_inbound,v_ServiceID_,v_AccountDiscountPlanID_,0,1);
		
		SET v_pointer_AccountCLI_ = v_pointer_AccountCLI_ + 1;
	END WHILE;
	
	SET v_AccountDiscountPlanID_ = 0;
	
		/* get discount plan id*/
	SELECT 
		AccountDiscountPlanID
	INTO  
		v_AccountDiscountPlanID_
	FROM tblAccountDiscountPlan 
	WHERE AccountID = p_AccountID 
	AND  ServiceID = p_ServiceID
	AND  (AccountSubscriptionID = 0)
	AND  ( (p_inbound = 0 AND Type = 1) OR  (p_inbound = 1 AND Type = 2 ) );
	

	IF (v_AccountDiscountPlanID_ > 0)
	THEN 				
			CALL prc_applyAccountDiscountPlan(p_AccountID,p_tbltempusagedetail_name,p_processId,p_inbound,p_ServiceID,v_AccountDiscountPlanID_,0,0); 
	
	END IF; 
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_applyAccountDiscountPlan`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_applyAccountDiscountPlan`(
	IN `p_AccountID` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_processId` INT,
	IN `p_inbound` INT,
	IN `p_ServiceID` INT,
	IN `p_AccountDiscountPlanID` INT,
	IN `p_accountname` INT,
	IN `p_accountcli` INT
)
ThisSP:BEGIN
	
	DECLARE v_DiscountPlanID_ INT;
	DECLARE v_AccountDiscountPlanID_ INT;
	DECLARE v_AccountName_ VARCHAR(255);
	DECLARE v_AccountCLI_ VARCHAR(255);
	DECLARE v_StartDate DATE;
	DECLARE v_EndDate DATE;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
	CREATE TEMPORARY TABLE tmp_codes_ (
		RateID INT,
		Code VARCHAR(50),
		DiscountID INT
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_discountsecons_;
	CREATE TEMPORARY TABLE tmp_discountsecons_ (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempUsageDetailID INT,
		TotalSecond INT,
		AccountDiscountPlanID INT,
		DiscountID INT,
		RemainingSecond INT,
		Discount INT,
		ThresholdReached INT DEFAULT 0,
		Unlimited INT
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_discountsecons2_;
	CREATE TEMPORARY TABLE tmp_discountsecons2_ (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempUsageDetailID INT,
		TotalSecond INT,
		AccountDiscountPlanID INT,
		DiscountID INT,
		RemainingSecond INT,
		Discount INT,
		ThresholdReached INT DEFAULT 0,
		Unlimited INT
	);

	/* get discount plan id*/
	SELECT 
		AccountDiscountPlanID,
		DiscountPlanID,
		StartDate,
		EndDate,
		IFNULL(AccountName,''),
		IFNULL(AccountCLI,'')		
	INTO  
		v_AccountDiscountPlanID_,
		v_DiscountPlanID_,
		v_StartDate,
		v_EndDate,
		v_AccountName_,
		v_AccountCLI_
	FROM tblAccountDiscountPlan 
	WHERE AccountID = p_AccountID 
	AND  ServiceID = p_ServiceID
	And  AccountDiscountPlanID = p_AccountDiscountPlanID
	AND  ( (p_inbound = 0 AND Type = 1) OR  (p_inbound = 1 AND Type = 2 ) );
	
		
	IF v_DiscountPlanID_ > 0
	THEN 
		
				/* get codes from discount destination group*/

		INSERT INTO tmp_codes_
		SELECT 
			r.RateID,
			r.Code,
			d.DiscountID
		FROM tblDiscountPlan dp
		INNER JOIN tblDiscount d ON d.DiscountPlanID = dp.DiscountPlanID
		INNER JOIN tblDestinationGroupCode dgc ON dgc.DestinationGroupID = d.DestinationGroupID
		INNER JOIN tblRate r ON r.RateID = dgc.RateID
		WHERE dp.DiscountPlanID = v_DiscountPlanID_;
		
		
				/* get minutes total in cdr table by disconnect time*/

		SET @stm = CONCAT('
		INSERT INTO tmp_discountsecons_ (TempUsageDetailID,TotalSecond,DiscountID)
		SELECT 
			d.TempUsageDetailID,
			@t := IF(@pre_DiscountID = d.DiscountID, @t + TotalSecond,TotalSecond) as TotalSecond,
			@pre_DiscountID := d.DiscountID
		FROM
		(
			SELECT 
				billed_duration as TotalSecond,
				TempUsageDetailID,
				area_prefix,
				DiscountID,
				AccountID
			FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
			INNER JOIN tmp_codes_ c
				ON ud.ProcessID = ' , p_processId , '
				AND ud.is_inbound = ',p_inbound,' 
				AND ud.AccountID = ' , p_AccountID , '
				AND ud.ServiceID = ' , p_ServiceID , '
				AND area_prefix =  c.Code				
				AND (("',p_accountname,'" = 0) OR  ("',p_accountname,'" = 1 AND ud.extension= "',v_AccountName_,'"))
				AND (("',p_accountcli,'" = 0) OR  ("',p_accountname,'" = 1 AND ud.AccountCLI= "',v_AccountCLI_,'"))
				AND DATE(ud.disconnect_time) >= "', v_StartDate ,'"
				AND DATE(ud.disconnect_time) < "',v_EndDate, '"
			ORDER BY c.DiscountID asc , disconnect_time asc
		) d
		CROSS JOIN (SELECT @t := 0) i
		CROSS JOIN (SELECT @pre_DiscountID := 0) j
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
							
						/* update account discount plan id*/

		UPDATE tmp_discountsecons_ SET AccountDiscountPlanID  = v_AccountDiscountPlanID_;

				/* update remaining minutes and discount */
		UPDATE tmp_discountsecons_ d
		INNER JOIN tblAccountDiscountPlan adp 
	 		ON adp.AccountID = p_AccountID AND adp.ServiceID = p_ServiceID
		INNER JOIN tblAccountDiscountScheme adc 
			ON adc.AccountDiscountPlanID =  adp.AccountDiscountPlanID 
			AND adc.DiscountID = d.DiscountID 
			AND adp.AccountDiscountPlanID = d.AccountDiscountPlanID
		SET d.RemainingSecond = (adc.Threshold - adc.SecondsUsed),d.Discount=adc.Discount,d.Unlimited = adc.Unlimited;

				/* remove call which cross the threshold */

		UPDATE  tmp_discountsecons_ SET ThresholdReached=1   WHERE Unlimited = 0 AND TotalSecond > RemainingSecond;

				 
		INSERT INTO tmp_discountsecons2_
		SELECT * FROM tmp_discountsecons_;

				/* update call cost which are under threshold */

		SET @stm = CONCAT('
		UPDATE RMCDR3.' , p_tbltempusagedetail_name , ' ud  INNER JOIN
		tmp_discountsecons_ d ON d.TempUsageDetailID = ud.TempUsageDetailID 
		SET cost = (cost - d.Discount*cost/100)
		WHERE ThresholdReached = 0;
		');

		PREPARE stmt FROM @stm;
	 	EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	

				/* update remaining minutes in account discount */

		UPDATE tblAccountDiscountPlan adp 
		INNER JOIN tblAccountDiscountScheme adc 
			ON adc.AccountDiscountPlanID =  adp.AccountDiscountPlanID 
		INNER JOIN(
			SELECT 
				MAX(TotalSecond) as SecondsUsed,
				DiscountID,
				AccountDiscountPlanID 
			FROM tmp_discountsecons_
			WHERE ThresholdReached = 0
			GROUP BY DiscountID,AccountDiscountPlanID
		)d 
		ON adc.DiscountID = d.DiscountID
		SET adc.SecondsUsed = adc.SecondsUsed+d.SecondsUsed
		WHERE adp.AccountID = p_AccountID 
		AND adp.ServiceID = p_ServiceID
		AND adp.AccountDiscountPlanID = d.AccountDiscountPlanID;

		/* update call cost which reach threshold and update seconds also*/
		
		SET @stm =CONCAT('
		UPDATE tmp_discountsecons_ d
		INNER JOIN( 
			SELECT MIN(RowID) as RowID  FROM tmp_discountsecons2_ WHERE ThresholdReached = 1
		GROUP BY DiscountID
		) tbl ON tbl.RowID = d.RowID
		INNER JOIN RMCDR3.' , p_tbltempusagedetail_name , ' ud
			ON ud.TempUsageDetailID = d.TempUsageDetailID
		INNER JOIN tblAccountDiscountPlan adp 
		 		ON adp.AccountID = ',p_AccountID,' AND adp.ServiceID = ', p_ServiceID ,'
		INNER JOIN tblAccountDiscountScheme adc 
				ON adc.AccountDiscountPlanID =  adp.AccountDiscountPlanID 
				AND adc.DiscountID = d.DiscountID AND d.AccountDiscountPlanID = adp.AccountDiscountPlanID
		SET ud.cost = cost*(TotalSecond - RemainingSecond)/billed_duration,adc.SecondsUsed = adc.SecondsUsed + billed_duration - (TotalSecond - RemainingSecond);
		');

		PREPARE stmt FROM @stm;
	 	EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getAccountDiscountPlan`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getAccountDiscountPlan`(
	IN `p_AccountID` INT,
	IN `p_Type` INT,
	IN `p_ServiceID` INT,
	IN `p_AccountSubscriptionID` INT,
	IN `p_SubscriptionDiscountPlanID` INT
)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT 
		dg.Name,
		ROUND(adc.Threshold/60,0) as Threshold,
		IF (adc.Unlimited=1,'Unlimited','') as Unlimited,
		ROUND(adc.SecondsUsed/60,0) as MinutesUsed,
		StartDate,
		EndDate,
		adp.created_at,
		adp.CreatedBy
	FROM tblAccountDiscountPlan adp
	INNER JOIN tblAccountDiscountScheme adc
		ON adc.AccountDiscountPlanID = adp.AccountDiscountPlanID
	INNER JOIN tblDiscount d
		ON d.DiscountID = adc.DiscountID
	INNER JOIN tblDestinationGroup dg
		ON dg.DestinationGroupID = d.DestinationGroupID
	WHERE AccountID = p_AccountID 
	   AND adp.ServiceID = p_ServiceID
	   AND adp.AccountSubscriptionID = p_AccountSubscriptionID
	   AND adp.SubscriptionDiscountPlanID = p_SubscriptionDiscountPlanID
		AND Type = p_Type;
	

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getBillingAccounts`;
DELIMITER //
CREATE PROCEDURE `prc_getBillingAccounts`(
	IN `p_CompanyID` INT,
	IN `p_Today` DATE,
	IN `p_skip_accounts` TEXT,
	IN `p_singleinvoice_accounts` TEXT
)
BEGIN
   
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

IF (p_singleinvoice_accounts = 0)
THEN
	SELECT 
		DISTINCT
		tblAccount.AccountID, 
		tblAccountBilling.NextInvoiceDate,
		AccountName, 
		tblAccountBilling.ServiceID
	FROM tblAccount 
	LEFT JOIN tblAccountService 
		ON tblAccountService.AccountID = tblAccount.AccountID
	LEFT JOIN tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccount.AccountID
		AND (( tblAccountBilling.ServiceID = 0  ) OR ( tblAccountService.ServiceID > 0 AND tblAccountBilling.ServiceID = tblAccountService.ServiceID AND tblAccountService.Status = 1)  ) 
	WHERE tblAccount.CompanyId = p_CompanyID 
	AND tblAccount.Status = 1 
	AND AccountType = 1 
	AND Billing = 1	
	AND tblAccountBilling.NextInvoiceDate <= p_Today
	AND (tblAccountBilling.BillingCycleType IS NOT NULL AND tblAccountBilling.BillingCycleType <> 'manual') 
	AND FIND_IN_SET(tblAccount.AccountID,p_skip_accounts) = 0	
	ORDER BY tblAccount.AccountID ASC;
	
ELSE

	SELECT 
		DISTINCT
		tblAccount.AccountID, 
		tblAccountBilling.NextInvoiceDate,
		AccountName, 
		tblAccountBilling.ServiceID
	FROM tblAccount 
	LEFT JOIN tblAccountService 
		ON tblAccountService.AccountID = tblAccount.AccountID
	LEFT JOIN tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccount.AccountID
		AND (( tblAccountBilling.ServiceID = 0  ) OR ( tblAccountService.ServiceID > 0 AND tblAccountBilling.ServiceID = tblAccountService.ServiceID AND tblAccountService.Status = 1)  ) 
	WHERE tblAccount.CompanyId = p_CompanyID 
	AND tblAccount.Status = 1 
	AND AccountType = 1 
	AND Billing = 1	
	AND tblAccountBilling.NextInvoiceDate <= p_Today
	AND (tblAccountBilling.BillingCycleType IS NOT NULL AND tblAccountBilling.BillingCycleType <> 'manual') 
	AND FIND_IN_SET(tblAccount.AccountID,p_singleinvoice_accounts) != 0	
	AND FIND_IN_SET(tblAccount.AccountID,p_skip_accounts) = 0	
	ORDER BY tblAccount.AccountID ASC;

END IF;	

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;