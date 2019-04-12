/*
SQLyog Ultimate v11.42 (64 bit)
MySQL - 5.7.25 : Database - speakintelligentRM
*********************************************************************
*/
USE `speakintelligentRM`;

/* Trigger structure for table `tblAccount` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblAccount_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblAccount_insert` AFTER INSERT ON `tblAccount` FOR EACH ROW BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
		
		IF (SELECT count(*) FROM eng_tblTempAccount WHERE AccountID=NEW.AccountID) = 1
		THEN
		UPDATE eng_tblTempAccount SET IsVendor=NEW.IsVendor,Status=NEW.Status WHERE AccountID=NEW.AccountID;
		ELSE
		INSERT INTO eng_tblTempAccount(AccountID, AccountType, CompanyId, CurrencyId, Number, AccountName, IsVendor, IsCustomer, IsReseller, STATUS, created_at,Country,CustomerID) 
		SELECT AccountID, AccountType, CompanyId, CurrencyId, Number, AccountName, IsVendor, IsCustomer, IsReseller, STATUS, created_at,Country,CustomerID FROM tblAccount WHERE AccountID=NEW.AccountID;
		END IF;
		
			SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
			
    END */$$


DELIMITER ;

/* Trigger structure for table `tblAccount` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblAccount_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblAccount_update` AFTER UPDATE ON `tblAccount` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	if NEW.Status <> OLD.Status OR NEW.IsVendor <> OLD.IsVendor THEN
		IF (SELECT count(*) FROM eng_tblTempAccount WHERE AccountID=NEW.AccountID) = 1
		THEN
		UPDATE eng_tblTempAccount SET IsVendor=NEW.IsVendor,Status=NEW.Status WHERE AccountID=NEW.AccountID;
		ELSE
		INSERT INTO eng_tblTempAccount(AccountID, AccountType, CompanyId, CurrencyId, Number, AccountName, IsVendor, IsCustomer, IsReseller, STATUS, created_at,Country,CustomerID) 
SELECT AccountID, AccountType, CompanyId, CurrencyId, Number, AccountName, IsVendor, IsCustomer, IsReseller, STATUS, created_at,Country,CustomerID FROM tblAccount WHERE AccountID=NEW.AccountID;
		END IF;
	END IF;
	
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
    END */$$


DELIMITER ;

/* Trigger structure for table `tblAccount` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblAccount_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'78.129.239.99' */ /*!50003 TRIGGER `trig_tblAccount_delete` BEFORE DELETE ON `tblAccount` FOR EACH ROW BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
		
	DELETE FROM eng_tblTempAccount WHERE AccountID=OLD.AccountID;
	INSERT INTO eng_tblTempAccount(AccountID, AccountType, CompanyId, CurrencyId, Number, AccountName, IsVendor, IsCustomer, IsReseller, STATUS, created_at,Country,CustomerID) 
	SELECT AccountID, AccountType, CompanyId, CurrencyId, Number, AccountName, IsVendor, IsCustomer, IsReseller, STATUS, created_at,Country,CustomerID FROM tblAccount WHERE AccountID=OLD.AccountID;
	UPDATE eng_tblTempRateTable SET `Action`='del' WHERE AccountID=OLD.AccountID;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END */$$


DELIMITER ;

/* Trigger structure for table `tblCLIRateTable` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCLIRateTable_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCLIRateTable_insert` AFTER INSERT ON `tblCLIRateTable` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCLIRateTable WHERE CLIRateTableID=NEW.CLIRateTableID;
	INSERT INTO eng_tblTempCLIRateTable(CLIRateTableID, CompanyID, AccountID, CLI, RateTableID, ServiceID, AccountServiceID, City,Tariff,PackageID, PackageRateTableID, STATUS, Prefix,DIDCategoryID,VendorID,NoType,ContractID,AccessDiscountPlanID,TerminationRateTableID,TerminationDiscountPlanID,CountryID,NumberStartDate,NumberEndDate) 
	SELECT CLIRateTableID, CompanyID, AccountID, CLI, RateTableID, ServiceID, AccountServiceID, City,Tariff,PackageID, PackageRateTableID, STATUS, Prefix,DIDCategoryID,VendorID,NoType,ContractID,AccessDiscountPlanID,TerminationRateTableID,TerminationDiscountPlanID,CountryID,NumberStartDate,NumberEndDate FROM tblCLIRateTable WHERE CLIRateTableID=NEW.CLIRateTableID;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
   
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblCLIRateTable` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCLIRateTable_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCLIRateTable_update` AFTER UPDATE ON `tblCLIRateTable` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCLIRateTable WHERE CLIRateTableID=OLD.CLIRateTableID;
	INSERT INTO eng_tblTempCLIRateTable(CLIRateTableID, CompanyID, AccountID, CLI, RateTableID, ServiceID, AccountServiceID, City,Tariff,PackageID, PackageRateTableID, STATUS, Prefix,DIDCategoryID,VendorID,NoType,ContractID,AccessDiscountPlanID,TerminationRateTableID,TerminationDiscountPlanID,CountryID,NumberStartDate,NumberEndDate) 
	SELECT CLIRateTableID, CompanyID, AccountID, CLI, RateTableID, ServiceID, AccountServiceID, City,Tariff,PackageID, PackageRateTableID, STATUS, Prefix,DIDCategoryID,VendorID,NoType,ContractID,AccessDiscountPlanID,TerminationRateTableID,TerminationDiscountPlanID,CountryID,NumberStartDate,NumberEndDate FROM tblCLIRateTable WHERE CLIRateTableID=OLD.CLIRateTableID;
   
   
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblCLIRateTable` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCLIRateTable_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCLIRateTable_delete` BEFORE DELETE ON `tblCLIRateTable` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCLIRateTable WHERE CLIRateTableID=OLD.CLIRateTableID;
	INSERT INTO eng_tblTempCLIRateTable(CLIRateTableID, CompanyID, AccountID, CLI, RateTableID, ServiceID, AccountServiceID, City,Tariff,PackageID, PackageRateTableID, STATUS, Prefix,DIDCategoryID,VendorID,NoType,ContractID,AccessDiscountPlanID,TerminationRateTableID,TerminationDiscountPlanID,CountryID,NumberStartDate,NumberEndDate) 
	SELECT CLIRateTableID, CompanyID, AccountID, CLI, RateTableID, ServiceID, AccountServiceID, City,Tariff,PackageID, PackageRateTableID, STATUS, Prefix,DIDCategoryID,VendorID,NoType,ContractID,AccessDiscountPlanID,TerminationRateTableID,TerminationDiscountPlanID,CountryID,NumberStartDate,NumberEndDate FROM tblCLIRateTable WHERE CLIRateTableID=OLD.CLIRateTableID;
	UPDATE eng_tblTempCLIRateTable SET `Action`='del' WHERE CLIRateTableID=OLD.CLIRateTableID;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblCurrency` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCurrency_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCurrency_insert` AFTER INSERT ON `tblCurrency` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCurrency WHERE CurrencyId=NEW.CurrencyId;
	INSERT INTO eng_tblTempCurrency(CurrencyId, CompanyId, CODE, Symbol, Description, STATUS, created_at, CreatedBy,updated_at, ModifiedBy) 
	SELECT CurrencyId, CompanyId, CODE, Symbol, Description, STATUS, created_at, CreatedBy,updated_at, ModifiedBy FROM tblCurrency WHERE CurrencyId=NEW.CurrencyId;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
   
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblCurrency` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCurrency_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCurrency_update` AFTER UPDATE ON `tblCurrency` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCurrency WHERE CurrencyId=OLD.CurrencyId;
	INSERT INTO eng_tblTempCurrency(CurrencyId, CompanyId, CODE, Symbol, Description, STATUS, created_at, CreatedBy,updated_at, ModifiedBy) 
	SELECT CurrencyId, CompanyId, CODE, Symbol, Description, STATUS, created_at, CreatedBy,updated_at, ModifiedBy FROM tblCurrency WHERE CurrencyId=OLD.CurrencyId;
   
   
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblCurrency` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCurrency_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCurrency_delete` BEFORE DELETE ON `tblCurrency` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCurrency WHERE CurrencyId=OLD.CurrencyId;
	INSERT INTO eng_tblTempCurrency(CurrencyId, CompanyId, CODE, Symbol, Description, STATUS, created_at, CreatedBy,updated_at, ModifiedBy) 
	SELECT CurrencyId, CompanyId, CODE, Symbol, Description, STATUS, created_at, CreatedBy,updated_at, ModifiedBy FROM tblCurrency WHERE CurrencyId=OLD.CurrencyId;
	UPDATE eng_tblTempCurrency SET `Action`='del' WHERE CurrencyId=OLD.CurrencyId;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblCurrencyConversion` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCurrencyConversion_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCurrencyConversion_insert` AFTER INSERT ON `tblCurrencyConversion` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCurrencyConversion WHERE ConversionID=NEW.ConversionID;
	INSERT INTO eng_tblTempCurrencyConversion(ConversionID, CompanyID, CurrencyID, VALUE,  created_at, CreatedBy,updated_at, ModifiedBy,EffectiveDate) 
	SELECT ConversionID, CompanyID, CurrencyID, VALUE,  created_at, CreatedBy,updated_at, ModifiedBy,EffectiveDate FROM tblCurrencyConversion WHERE ConversionID=NEW.ConversionID;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
   
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblCurrencyConversion` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCurrencyConversion_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCurrencyConversion_update` AFTER UPDATE ON `tblCurrencyConversion` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCurrencyConversion WHERE ConversionID=OLD.ConversionID;
	INSERT INTO eng_tblTempCurrencyConversion(ConversionID, CompanyID, CurrencyID, VALUE,  created_at, CreatedBy,updated_at, ModifiedBy,EffectiveDate) 
	SELECT ConversionID, CompanyID, CurrencyID, VALUE,  created_at, CreatedBy,updated_at, ModifiedBy,EffectiveDate FROM tblCurrencyConversion WHERE ConversionID=OLD.ConversionID;
   
   
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblCurrencyConversion` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblCurrencyConversion_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblCurrencyConversion_delete` BEFORE DELETE ON `tblCurrencyConversion` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempCurrencyConversion WHERE ConversionID=OLD.ConversionID;
	INSERT INTO eng_tblTempCurrencyConversion(ConversionID, CompanyID, CurrencyID, VALUE,  created_at, CreatedBy,updated_at, ModifiedBy,EffectiveDate) 
	SELECT ConversionID, CompanyID, CurrencyID, VALUE,  created_at, CreatedBy,updated_at, ModifiedBy,EffectiveDate FROM tblCurrencyConversion WHERE ConversionID=OLD.ConversionID;
	UPDATE eng_tblTempCurrencyConversion SET `Action`='del' WHERE ConversionID=OLD.ConversionID;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblDynamicFieldsValue` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblDynamicFieldsValue_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblDynamicFieldsValue_insert` AFTER INSERT ON `tblDynamicFieldsValue` FOR EACH ROW BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
		
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
			
    END */$$


DELIMITER ;

/* Trigger structure for table `tblDynamicFieldsValue` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblDynamicFieldsValue_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblDynamicFieldsValue_update` AFTER UPDATE ON `tblDynamicFieldsValue` FOR EACH ROW BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
    END */$$


DELIMITER ;

/* Trigger structure for table `tblDynamicFieldsValue` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblDynamicFieldsValue_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'78.129.239.99' */ /*!50003 TRIGGER `trig_tblDynamicFieldsValue_delete` BEFORE DELETE ON `tblDynamicFieldsValue` FOR EACH ROW BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
		
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END */$$


DELIMITER ;

/* Trigger structure for table `tblRateTable` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblRateTable_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblRateTable_insert` AFTER INSERT ON `tblRateTable` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempRateTable WHERE RateTableId=NEW.RateTableId;
	INSERT INTO eng_tblTempRateTable(RateTableId, CompanyId, CodeDeckId, RateTableName, RateGeneratorID, TrunkID, STATUS, created_at, CreatedBy, updated_at, ModifiedBy, CurrencyID, RoundChargedAmount, DIDCategoryID, TYPE, MinimumCallCharge, AppliedTo)
	SELECT RateTableId, CompanyId, CodeDeckId, RateTableName, RateGeneratorID, TrunkID, STATUS, created_at, CreatedBy, updated_at, ModifiedBy, CurrencyID, RoundChargedAmount, DIDCategoryID, TYPE, MinimumCallCharge, AppliedTo FROM tblRateTable
	  WHERE RateTableId=NEW.RateTableId  and Type = 1 and AppliedTo = 2;
	
		 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		 
    END */$$


DELIMITER ;

/* Trigger structure for table `tblRateTable` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblRateTable_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblRateTable_update` AFTER UPDATE ON `tblRateTable` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempRateTable WHERE RateTableId=OLD.RateTableId;
	INSERT INTO eng_tblTempRateTable(RateTableId, CompanyId, CodeDeckId, RateTableName, RateGeneratorID, TrunkID, STATUS, created_at, CreatedBy, updated_at, ModifiedBy, CurrencyID, RoundChargedAmount, DIDCategoryID, TYPE, MinimumCallCharge, AppliedTo)
	SELECT RateTableId, CompanyId, CodeDeckId, RateTableName, RateGeneratorID, TrunkID, STATUS, created_at, CreatedBy, updated_at, ModifiedBy, CurrencyID, RoundChargedAmount, DIDCategoryID, TYPE, MinimumCallCharge, AppliedTo
	 FROM tblRateTable  WHERE RateTableId=OLD.RateTableId  and Type = 1 and AppliedTo = 2;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblRateTable` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblRateTable_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblRateTable_delete` BEFORE DELETE ON `tblRateTable` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempRateTable WHERE RateTableId=OLD.RateTableId;
	INSERT INTO eng_tblTempRateTable(RateTableId, CompanyId, CodeDeckId, RateTableName, RateGeneratorID, TrunkID, STATUS, created_at, CreatedBy, updated_at, ModifiedBy, CurrencyID, RoundChargedAmount, DIDCategoryID, TYPE, MinimumCallCharge, AppliedTo)
	SELECT RateTableId, CompanyId, CodeDeckId, RateTableName, RateGeneratorID, TrunkID, STATUS, created_at, CreatedBy, updated_at, ModifiedBy, CurrencyID, RoundChargedAmount, DIDCategoryID, TYPE, MinimumCallCharge, AppliedTo
	 FROM tblRateTable  WHERE RateTableId=OLD.RateTableId and Type = 1 and AppliedTo = 2;
	UPDATE eng_tblTempRateTable SET `Action`='del' WHERE RateTableId=OLD.RateTableId;
	
		 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END */$$


DELIMITER ;

/* Trigger structure for table `tblRateTableRate` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblRateTableRate_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblRateTableRate_insert` AFTER INSERT ON `tblRateTableRate` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempRateTableRate WHERE RateTableRateID=NEW.RateTableRateID;
	INSERT INTO eng_tblTempRateTableRate(RateTableRateID, OriginationRateID, RateID, RateTableId, TimezonesID, Rate, RateN, EffectiveDate, EndDate, created_at, updated_at, CreatedBy, ModifiedBy, PreviousRate, Interval1, IntervalN, ConnectionFee, RoutingCategoryID, Preference, Blocked, ApprovedStatus, ApprovedBy, ApprovedDate,OriginationCode,DestinationCode) 
SELECT RateTableRateID, OriginationRateID, rtr.RateID, rtr.RateTableId, TimezonesID, rtr.Rate, RateN, EffectiveDate, EndDate, rtr.created_at, rtr.updated_at, rtr.CreatedBy, rtr.ModifiedBy, rtr.PreviousRate, rtr.Interval1, rtr.IntervalN, rtr.ConnectionFee, rtr.RoutingCategoryID, rtr.Preference, rtr.Blocked, rtr.ApprovedStatus, 
ApprovedBy, ApprovedDate,
origRate.Code AS `OriginationCode` ,destRate.Code AS `DestinationCode`
 
FROM tblRateTableRate  rtr
JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
join tblRateTable rt on rtr.RateTableID = rt.RateTableId
LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
WHERE RateTableRateID=NEW.RateTableRateID and rt.`Type` = 1 and rt.AppliedTo = 2;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END */$$


DELIMITER ;

/* Trigger structure for table `tblRateTableRate` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblRateTableRate_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblRateTableRate_update` AFTER UPDATE ON `tblRateTableRate` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempRateTableRate WHERE RateTableRateID=OLD.RateTableRateID;
	INSERT INTO eng_tblTempRateTableRate(RateTableRateID, OriginationRateID, RateID, RateTableId, TimezonesID, Rate, RateN, EffectiveDate, EndDate, created_at, updated_at, CreatedBy, ModifiedBy, PreviousRate, Interval1, IntervalN, ConnectionFee, RoutingCategoryID, Preference, Blocked, ApprovedStatus, ApprovedBy, ApprovedDate,OriginationCode,DestinationCode) 
SELECT RateTableRateID, OriginationRateID, rtr.RateID, rtr.RateTableId, TimezonesID, rtr.Rate, RateN, EffectiveDate, EndDate, rtr.created_at, rtr.updated_at, rtr.CreatedBy, rtr.ModifiedBy, rtr.PreviousRate, rtr.Interval1, rtr.IntervalN, rtr.ConnectionFee, rtr.RoutingCategoryID, rtr.Preference, rtr.Blocked, rtr.ApprovedStatus, 
ApprovedBy, ApprovedDate,
origRate.Code AS `OriginationCode` ,destRate.Code AS `DestinationCode`
 
FROM tblRateTableRate  rtr
JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
join tblRateTable rt on rtr.RateTableID = rt.RateTableId
LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
WHERE RateTableRateID=OLD.RateTableRateID and rt.`Type` = 1 and rt.AppliedTo = 2;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END */$$


DELIMITER ;

/* Trigger structure for table `tblRateTableRate` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblRateTableRate_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_tblRateTableRate_delete` BEFORE DELETE ON `tblRateTableRate` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempRateTableRate WHERE RateTableRateID=OLD.RateTableRateID;
	INSERT INTO eng_tblTempRateTableRate(RateTableRateID, OriginationRateID, RateID, RateTableId, TimezonesID, Rate, RateN, EffectiveDate, EndDate, created_at, updated_at, CreatedBy, ModifiedBy, PreviousRate, Interval1, IntervalN, ConnectionFee, RoutingCategoryID, Preference, Blocked, ApprovedStatus, ApprovedBy, ApprovedDate,OriginationCode,DestinationCode) 
SELECT RateTableRateID, OriginationRateID, rtr.RateID, rtr.RateTableId, TimezonesID, rtr.Rate, RateN, EffectiveDate, EndDate, rtr.created_at, rtr.updated_at, rtr.CreatedBy, rtr.ModifiedBy, rtr.PreviousRate, rtr.Interval1, rtr.IntervalN, rtr.ConnectionFee, rtr.RoutingCategoryID, rtr.Preference, rtr.Blocked, rtr.ApprovedStatus, 
ApprovedBy, ApprovedDate,
origRate.Code AS `OriginationCode` ,destRate.Code AS `DestinationCode`
 
FROM tblRateTableRate  rtr
JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
join tblRateTable rt on rtr.RateTableID = rt.RateTableId
LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
WHERE RateTableRateID=OLD.RateTableRateID and rt.`Type` = 1 and rt.AppliedTo = 2;
UPDATE eng_tblTempRateTableRate SET `Action`='del' WHERE RateTableRateID=OLD.RateTableRateID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
    END */$$


DELIMITER ;

/* Trigger structure for table `tblReseller` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblReseller_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblReseller_insert` AFTER INSERT ON `tblReseller` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempReseller WHERE ResellerID=NEW.ResellerID;
	INSERT INTO eng_tblTempReseller(ResellerID, ResellerName, CompanyID, ChildCompanyID, AccountID, FirstName, LastName, Email,PASSWORD, STATUS, AllowWhiteLabel, created_at,created_by,updated_at,updated_by) 
	SELECT ResellerID, ResellerName, CompanyID, ChildCompanyID, AccountID, FirstName, LastName, Email,PASSWORD, STATUS, AllowWhiteLabel, created_at,created_by,updated_at,updated_by FROM tblReseller WHERE ResellerID=NEW.ResellerID;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
   
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblReseller` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblReseller_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblReseller_update` AFTER UPDATE ON `tblReseller` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempReseller WHERE ResellerID=OLD.ResellerID;
	INSERT INTO eng_tblTempReseller(ResellerID, ResellerName, CompanyID, ChildCompanyID, AccountID, FirstName, LastName, Email,PASSWORD, STATUS, AllowWhiteLabel, created_at,created_by,updated_at,updated_by) 
	SELECT ResellerID, ResellerName, CompanyID, ChildCompanyID, AccountID, FirstName, LastName, Email,PASSWORD, STATUS, AllowWhiteLabel, created_at,created_by,updated_at,updated_by FROM tblReseller WHERE ResellerID=OLD.ResellerID;
   
   
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblReseller` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblReseller_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblReseller_delete` BEFORE DELETE ON `tblReseller` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempReseller WHERE ResellerID=OLD.ResellerID;
	INSERT INTO eng_tblTempReseller(ResellerID, ResellerName, CompanyID, ChildCompanyID, AccountID, FirstName, LastName, Email,PASSWORD, STATUS, AllowWhiteLabel, created_at,created_by,updated_at,updated_by) 
	SELECT ResellerID, ResellerName, CompanyID, ChildCompanyID, AccountID, FirstName, LastName, Email,PASSWORD, STATUS, AllowWhiteLabel, created_at,created_by,updated_at,updated_by FROM tblReseller WHERE ResellerID=OLD.ResellerID;
	UPDATE eng_tblTempReseller SET `Action`='del' WHERE ResellerID=OLD.ResellerID;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblServiceTemapleInboundTariff` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_ServiceTemapleInboundTariff_Delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'110.39.187.46' */ /*!50003 TRIGGER `trig_ServiceTemapleInboundTariff_Delete` AFTER DELETE ON `tblServiceTemapleInboundTariff` FOR EACH ROW BEGIN

 update tblServiceTemplate set InboundTariffExists = 0 where ServiceTemplateId = OLD.ServiceTemplateID;
END */$$


DELIMITER ;

/* Trigger structure for table `tblVendorConnection` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblVendorConnection_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblVendorConnection_insert` AFTER INSERT ON `tblVendorConnection` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempVendorConnection WHERE VendorConnectionID=NEW.VendorConnectionID;
	INSERT INTO eng_tblTempVendorConnection(VendorConnectionID, AccountId, RateTypeID, CompanyID, NAME, DIDCategoryID, Active, RateTableID, TrunkID, CLIRule, CLDRule, CallPrefix, IP, PORT, Username, PASSWORD, created_at, updated_at, created_by, updated_by, PrefixCDR, SipHeader, AuthenticationMode, Location) 
SELECT VendorConnectionID, AccountId, RateTypeID, CompanyID, NAME, DIDCategoryID, Active, RateTableID, TrunkID, CLIRule, CLDRule, CallPrefix, IP, PORT, Username, PASSWORD, created_at, updated_at, created_by, updated_by, PrefixCDR, SipHeader, AuthenticationMode, Location FROM tblVendorConnection WHERE VendorConnectionID=NEW.VendorConnectionID;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
   
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblVendorConnection` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblVendorConnection_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblVendorConnection_update` AFTER UPDATE ON `tblVendorConnection` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempVendorConnection WHERE VendorConnectionID=OLD.VendorConnectionID;
	INSERT INTO eng_tblTempVendorConnection(VendorConnectionID, AccountId, RateTypeID, CompanyID, NAME, DIDCategoryID, Active, RateTableID, TrunkID, CLIRule, CLDRule, CallPrefix, IP, PORT, Username, PASSWORD, created_at, updated_at, created_by, updated_by, PrefixCDR, SipHeader, AuthenticationMode, Location) 
SELECT VendorConnectionID, AccountId, RateTypeID, CompanyID, NAME, DIDCategoryID, Active, RateTableID, TrunkID, CLIRule, CLDRule, CallPrefix, IP, PORT, Username, PASSWORD, created_at, updated_at, created_by, updated_by, PrefixCDR, SipHeader, AuthenticationMode, Location FROM tblVendorConnection WHERE VendorConnectionID=OLD.VendorConnectionID;
   
   
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblVendorConnection` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblVendorConnection_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblVendorConnection_delete` BEFORE DELETE ON `tblVendorConnection` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTempVendorConnection WHERE VendorConnectionID=OLD.VendorConnectionID;
	INSERT INTO eng_tblTempVendorConnection(VendorConnectionID, AccountId, RateTypeID, CompanyID, NAME, DIDCategoryID, Active, RateTableID, TrunkID, CLIRule, CLDRule, CallPrefix, IP, PORT, Username, PASSWORD, created_at, updated_at, created_by, updated_by, PrefixCDR, SipHeader, AuthenticationMode, Location) 
SELECT VendorConnectionID, AccountId, RateTypeID, CompanyID, NAME, DIDCategoryID, Active, RateTableID, TrunkID, CLIRule, CLDRule, CallPrefix, IP, PORT, Username, PASSWORD, created_at, updated_at, created_by, updated_by, PrefixCDR, SipHeader, AuthenticationMode, Location FROM tblVendorConnection WHERE VendorConnectionID=OLD.VendorConnectionID;
	UPDATE eng_tblTempVendorConnection SET `Action`='del' WHERE VendorConnectionID=OLD.VendorConnectionID;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblVendorTimezone` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblVendorTimezone_insert` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblVendorTimezone_insert` AFTER INSERT ON `tblVendorTimezone` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempVendorTimezone WHERE VendorTimezoneID=NEW.VendorTimezoneID;
	INSERT INTO eng_tblTempVendorTimezone(VendorTimezoneID, TYPE, Country, TimeZoneID, VendorID, FromTime, ToTime, DaysOfWeek,DaysOfMonth, Months, ApplyIF, STATUS, created_at, created_by,updated_at,updated_by) 
	SELECT VendorTimezoneID, TYPE, Country, TimeZoneID, VendorID, FromTime, ToTime, DaysOfWeek,DaysOfMonth, Months, ApplyIF, STATUS, created_at, created_by,updated_at,updated_by FROM tblVendorTimezone WHERE VendorTimezoneID=NEW.VendorTimezoneID;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
   
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblVendorTimezone` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblVendorTimezone_update` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblVendorTimezone_update` AFTER UPDATE ON `tblVendorTimezone` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempVendorTimezone WHERE VendorTimezoneID=OLD.VendorTimezoneID;
	INSERT INTO eng_tblTempVendorTimezone(VendorTimezoneID, TYPE, Country, TimeZoneID, VendorID, FromTime, ToTime, DaysOfWeek,DaysOfMonth, Months, ApplyIF, STATUS, created_at, created_by,updated_at,updated_by) 
	SELECT VendorTimezoneID, TYPE, Country, TimeZoneID, VendorID, FromTime, ToTime, DaysOfWeek,DaysOfMonth, Months, ApplyIF, STATUS, created_at, created_by,updated_at,updated_by FROM tblVendorTimezone WHERE VendorTimezoneID=OLD.VendorTimezoneID;
      
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
	 END */$$


DELIMITER ;

/* Trigger structure for table `tblVendorTimezone` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trig_tblVendorTimezone_delete` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'neon-user'@'localhost' */ /*!50003 TRIGGER `trig_tblVendorTimezone_delete` BEFORE DELETE ON `tblVendorTimezone` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DELETE FROM eng_tblTempVendorTimezone WHERE VendorTimezoneID=OLD.VendorTimezoneID;
	INSERT INTO eng_tblTempVendorTimezone(VendorTimezoneID, TYPE, Country, TimeZoneID, VendorID, FromTime, ToTime, DaysOfWeek,DaysOfMonth, Months, ApplyIF, STATUS, created_at, created_by,updated_at,updated_by) 
SELECT VendorTimezoneID, TYPE, Country, TimeZoneID, VendorID, FromTime, ToTime, DaysOfWeek,DaysOfMonth, Months, ApplyIF, STATUS, created_at, created_by,updated_at,updated_by FROM tblVendorTimezone WHERE VendorTimezoneID=OLD.VendorTimezoneID;
	UPDATE eng_tblTempVendorTimezone SET `Action`='del' WHERE VendorTimezoneID=OLD.VendorTimezoneID;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
	 END */$$


DELIMITER ;
