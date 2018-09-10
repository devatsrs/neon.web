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


CREATE TABLE IF NOT EXISTS `tblSubscriptionDiscountPlan` (
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


CREATE TABLE IF NOT EXISTS `tblRegistarionApiLog` (
  `RegistarionApiLogID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `AccountName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RequestUrl` text COLLATE utf8_unicode_ci,
  `ApiJson` longtext COLLATE utf8_unicode_ci,
  `PaymentGateway` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentAmount` float DEFAULT NULL,
  `PaymentStatus` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentResponse` longtext COLLATE utf8_unicode_ci,
  `NeonAccountStatus` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoiceStatus` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoiceID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FinalApiResponse` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`RegistarionApiLogID`)
) ENGINE=InnoDB COLLATE=utf8_unicode_ci;



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

DROP PROCEDURE IF EXISTS `prc_GetAccounts`;
DELIMITER //
CREATE PROCEDURE `prc_GetAccounts`(
	IN `p_CompanyID` int,
	IN `p_userID` int ,
	IN `p_IsVendor` int ,
	IN `p_isCustomer` int ,
	IN `p_isReseller` INT,
	IN `p_ResellerID` INT,
	IN `p_activeStatus` int,
	IN `p_VerificationStatus` int,
	IN `p_AccountNo` VARCHAR(100),
	IN `p_ContactName` VARCHAR(50),
	IN `p_AccountName` VARCHAR(50),
	IN `p_tags` VARCHAR(50),
	IN `p_IPCLI` VARCHAR(50),
	IN `p_low_balance` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_raccountids TEXT;
	DECLARE v_resellercompanyid int;
	SET v_raccountids = '';
	SET v_resellercompanyid = 0;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM tblReseller WHERE ResellerID=p_ResellerID;
		
		SELECT ChildCompanyID INTO v_resellercompanyid FROM tblReseller WHERE ResellerID=p_ResellerID;		
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;

	IF p_isExport = 0
	THEN

		SELECT
			tblAccount.AccountID,
			tblAccount.Number,
			tblAccount.AccountName,
			CONCAT(tblAccount.FirstName,' ',tblAccount.LastName) as Ownername,
			tblAccount.Phone,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.SOAOffset,0),v_Round_)) as OutStandingAmount,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_) - ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_)) as UnbilledAmount,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.PermanentCredit,0),v_Round_)) as PermanentCredit,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)) as AccountExposure,
			tblAccount.Email,
			tblAccount.IsCustomer,
			tblAccount.IsVendor,
			tblAccount.VerificationStatus,
			tblAccount.Address1,
			tblAccount.Address2,
			tblAccount.Address3,
			tblAccount.City,
			tblAccount.Country,
			tblAccount.PostCode,
			tblAccount.Picture,
	--	IF ( (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount AND abc.BalanceThreshold <> 0 ,1,0) as BalanceWarning,
	     IF ( (
				CASE WHEN abc.BalanceThreshold LIKE '%p' 
					THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit 
						ELSE abc.BalanceThreshold END
				) > CASE WHEN abg.BillingType = 1 THEN (CASE WHEN abc.BalanceAmount <0 THEN ABS(abc.BalanceAmount) ELSE (abc.BalanceAmount * -1) END) ELSE abc.BalanceAmount END  AND abc.BalanceThreshold <> 0 ,1,0) as BalanceWarning, 
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_)) as CUA,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_)) as VUA,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)) as AE,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,IF(ROUND(COALESCE(abc.PermanentCredit,0),v_Round_) - ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)<0,0,ROUND(COALESCE(abc.PermanentCredit,0),v_Round_) - ROUND(COALESCE(abc.BalanceAmount,0),v_Round_))) as ACL,
			abc.BalanceThreshold,
			tblAccount.Blocked
		FROM tblAccount
		LEFT JOIN tblAccountBilling abg 
		ON tblAccount.AccountID = abg.AccountID
		LEFT JOIN tblAccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE  
			 tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
			AND (p_ResellerID = 0 OR tblAccount.CompanyID = v_resellercompanyid)
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
	 --	AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) )
		AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND  (
				CASE WHEN abc.BalanceThreshold LIKE '%p' 
					THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit 
						ELSE abc.BalanceThreshold END
				) > CASE WHEN abg.BillingType = 1 THEN (CASE WHEN abc.BalanceAmount <0 THEN ABS(abc.BalanceAmount) ELSE (abc.BalanceAmount * -1) END) ELSE abc.BalanceAmount END)) 
		GROUP BY tblAccount.AccountID,abg.BillingType
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN tblAccount.AccountName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN tblAccount.AccountName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NumberDESC') THEN tblAccount.Number
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NumberASC') THEN tblAccount.Number
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OwnernameDESC') THEN tblUser.FirstName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OwnernameASC') THEN tblUser.FirstName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PhoneDESC') THEN tblAccount.Phone
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PhoneASC') THEN tblAccount.Phone
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutStandingAmountDESC') THEN abc.SOAOffset
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutStandingAmountASC') THEN abc.SOAOffset
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PermanentCreditDESC') THEN abc.PermanentCredit
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PermanentCreditASC') THEN abc.PermanentCredit
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UnbilledAmountDESC') THEN (ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_) - ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_))
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UnbilledAmountASC') THEN (ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_) - ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_))
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailDESC') THEN tblAccount.Email
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailASC') THEN tblAccount.Email
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(DISTINCT tblAccount.AccountID) AS totalcount
		FROM tblAccount
		LEFT JOIN tblAccountBilling abg 
		 ON tblAccount.AccountID = abg.AccountID
		LEFT JOIN tblAccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE 
			 tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
			AND (p_ResellerID = 0 OR tblAccount.CompanyID = v_resellercompanyid)
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
		--	AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) );
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND  (
				CASE WHEN abc.BalanceThreshold LIKE '%p' 
					THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit 
						ELSE abc.BalanceThreshold END
				) > CASE WHEN abg.BillingType = 1 THEN (CASE WHEN abc.BalanceAmount <0 THEN ABS(abc.BalanceAmount) ELSE (abc.BalanceAmount * -1) END) ELSE abc.BalanceAmount END)) ;

	END IF;
	IF p_isExport = 1
	THEN
		SELECT
			tblAccount.Number as NO,
			tblAccount.AccountName,
			CONCAT(tblAccount.FirstName,' ',tblAccount.LastName) as Name,
			tblAccount.Phone,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.SOAOffset,0),v_Round_)) as 'OutStanding',
			tblAccount.Email,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_)  - ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_)) as 'Unbilled Amount',
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.PermanentCredit,0),v_Round_)) as 'Credit Limit',
			CONCAT(tblUser.FirstName,' ',tblUser.LastName) as 'Account Owner',
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)) as AccountExposure
		FROM tblAccount
			LEFT JOIN tblAccountBilling abg 
		ON tblAccount.AccountID = abg.AccountID
		LEFT JOIN tblAccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE   tblAccount.CompanyID = p_CompanyID
			AND tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
			AND (p_ResellerID = 0 OR tblAccount.CompanyID = v_resellercompanyid)
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
		--	AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) )
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND  (
				CASE WHEN abc.BalanceThreshold LIKE '%p' 
					THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit 
						ELSE abc.BalanceThreshold END
				) > CASE WHEN abg.BillingType = 1 THEN (CASE WHEN abc.BalanceAmount <0 THEN ABS(abc.BalanceAmount) ELSE (abc.BalanceAmount * -1) END) ELSE abc.BalanceAmount END)) 
		GROUP BY tblAccount.AccountID,abg.BillingType;
	END IF;
	IF p_isExport = 2
	THEN
		SELECT
			tblAccount.AccountID,
			tblAccount.AccountName
		FROM tblAccount
			LEFT JOIN tblAccountBilling abg 
		ON tblAccount.AccountID = abg.AccountID
		LEFT JOIN tblAccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE   tblAccount.CompanyID = p_CompanyID
			AND tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
			AND (p_ResellerID = 0 OR tblAccount.CompanyID = v_resellercompanyid)
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
			-- AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) )
				AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND  (
				CASE WHEN abc.BalanceThreshold LIKE '%p' 
					THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit 
						ELSE abc.BalanceThreshold END
				) > CASE WHEN abg.BillingType = 1 THEN (CASE WHEN abc.BalanceAmount <0 THEN ABS(abc.BalanceAmount) ELSE (abc.BalanceAmount * -1) END) ELSE abc.BalanceAmount END)) 
		GROUP BY tblAccount.AccountID,abg.BillingType;
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_LowBalanceReminder`;
DELIMITER //
CREATE PROCEDURE `prc_LowBalanceReminder`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_BillingClassID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL RMBilling3.prc_updateSOAOffSet(p_CompanyID,p_AccountID);
	
	
	SELECT
			DISTINCT
			IF ( (
					CASE WHEN ab.BalanceThreshold LIKE '%p' 
						THEN REPLACE(ab.BalanceThreshold, 'p', '')/ 100 * ab.PermanentCredit 
							ELSE ab.BalanceThreshold END
					) > CASE WHEN abg.BillingType = 1 THEN 
					        (CASE WHEN ab.BalanceAmount <0 THEN ABS(ab.BalanceAmount) ELSE (ab.BalanceAmount * -1) END) ELSE ab.BalanceAmount END 
							  	 AND ab.BalanceThreshold <> 0 ,1,0) as BalanceWarning,
			a.AccountID
		FROM tblAccountBalance ab 
		INNER JOIN tblAccount a 
			ON a.AccountID = ab.AccountID
		INNER JOIN tblAccountBilling abg 
			ON abg.AccountID  = a.AccountID
		INNER JOIN tblBillingClass b
			ON b.BillingClassID = abg.BillingClassID
		WHERE a.CompanyId = p_CompanyID
		AND (p_AccountID = 0 OR  a.AccountID = p_AccountID)
		AND (p_BillingClassID = 0 OR  b.BillingClassID = p_BillingClassID)
		AND ab.PermanentCredit IS NOT NULL
		AND ab.BalanceThreshold IS NOT NULL
		AND a.`Status` = 1;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;