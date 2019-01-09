-- --------------------------------------------------------
-- Host:                         188.227.186.98
-- Server version:               5.7.18 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for procedure speakIntelligentRoutingEngine.prc_getRoutingRecords
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getRoutingRecords`(
	IN `p_AccountID` INT




,
	IN `p_OriginationCode` VARCHAR(50),
	IN `p_DestinationCode` VARCHAR(50),
	IN `p_TimeZone` VARCHAR(50)









,
	IN `p_profileIDs` VARCHAR(50)




































)
BEGIN

	DECLARE v_AccountCurrencyID_ INT;
	DECLARE v_RowCount_ INT;
	DECLARE v_CaseID INT;
	DECLARE v_AccountCurrency_ VARCHAR(50);


	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @@session.collation_connection='utf8_unicode_ci';
	SET @@session.character_set_results='utf8';
	
SELECT CurrencyID INTO v_AccountCurrencyID_ FROM  `speakintelligentRM`.`tblAccount` WHERE AccountID = p_AccountID;
select Code INTO v_AccountCurrency_ from speakintelligentRM.tblCurrency where CurrencyId = v_AccountCurrencyID_;


IF p_profileIDs != '' THEN
SET @rank:=0,@VendorConnectionName:='';
		select @rank:=@rank+1 as Position,Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,accountCurrency as Currency,ROUND(Rate,6) as Rate from
				(
				select 
				   @VendorConnectionName := VendorConnectionName,
					
					concat(IFNULL(TrunkPrefix ,""),DestinationCode) as Prefix,VendorID,VendorName,SipHeader,IP,Port,Username,Password,AuthenticationMode,TimezoneId, v_AccountCurrency_ as accountCurrency,
					CASE WHEN  Currency = v_AccountCurrencyID_
							THEN
								Rate
							ELSE
							(
								-- Convert to base currrncy and x by RateGenerator Exhange
								(Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ )
								* (Rate  / (Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ ))							
							)
							END as Rate,VendorConnectionName,Preference,RoutingCategoryOrder  from tblRoutingProfileRate
							
							 where RoutingProfileId = p_profileIDs
							  and (TimezoneId = p_TimeZone OR (TimezoneId <> p_TimeZone and TimezoneId=1))
							  and p_OriginationCode like  CONCAT(OriginationCode,"%")
							  and p_DestinationCode like  CONCAT(DestinationCode,"%")
							  and @VendorConnectionName <> VendorConnectionName
								 and @VendorConnectionName <> VendorConnectionName
								  order by Rate,RoutingCategoryOrder  desc,Preference desc,CONCAT(DestinationCode,"%") desc
								  
								  
								  ) vendorTable; 
								  
								  

select FOUND_ROWS() into v_RowCount_;

	IF v_RowCount_ = 0 THEN
	SET @rank:=0,@VendorConnectionName:='';
	select @rank:=@rank+1 as Position,Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,accountCurrency as Currency,ROUND(Rate,6) as Rate from
			(select 
					@VendorConnectionName := VendorConnectionName,
					concat(IFNULL(TrunkPrefix ,""),DestinationCode) as Prefix,
					VendorID,VendorName,SipHeader,IP,Port,Username,Password,AuthenticationMode,TimezoneId, v_AccountCurrency_ as accountCurrency,
					CASE WHEN  Currency = v_AccountCurrencyID_
								THEN
									Rate
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ )
									* (Rate  / (Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ ))							
								)
								END as Rate,VendorConnectionName,Preference,RoutingCategoryOrder  from tblRoutingProfileRate
								 where RoutingProfileId = p_profileIDs
								   and (TimezoneId = p_TimeZone OR (TimezoneId <> p_TimeZone and TimezoneId=1))
								   and p_DestinationCode like  CONCAT(DestinationCode,"%")
								   and @VendorConnectionName <> VendorConnectionName
									 order by Rate,RoutingCategoryOrder  desc,Preference desc,CONCAT(DestinationCode,"%") desc) vendorTable;
select FOUND_ROWS() into v_RowCount_;
								
	END IF;
ELSE

	SET @rank:=0,@VendorConnectionName:='';
	select @rank:=@rank+1 as Position,Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,accountCurrency as Currency,ROUND(Rate,6) as Rate from
	(select 
				@VendorConnectionName := VendorConnectionName,
				concat(IFNULL(TrunkPrefix ,""),DestinationCode) as Prefix,VendorID,VendorName,SipHeader,IP,Port,Username,Password,AuthenticationMode,TimezoneId,v_AccountCurrency_ as accountCurrency,
				CASE WHEN  Currency = v_AccountCurrency_
						THEN
							Rate
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ )
							* (Rate  / (Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ ))							
						)
						END as Rate,
						VendorConnectionName,Preference  from tblVendorRate
						 where (TimezoneId = p_TimeZone OR (TimezoneId <> p_TimeZone and TimezoneId=1))
						  and p_OriginationCode like  CONCAT(OriginationCode,"%")
						 and p_DestinationCode like  CONCAT(DestinationCode,"%") 
						 and @VendorConnectionName <> VendorConnectionName 
						 
						   order by Rate,Preference desc,CONCAT(DestinationCode,"%") desc) vendorTable;

select FOUND_ROWS() into v_RowCount_;

	IF v_RowCount_ = 0 THEN
	SET @rank:=0,@VendorConnectionName:='';
	select @rank:=@rank+1 as Position,Prefix,VendorID,VendorName,VendorConnectionName,SipHeader,IP,Port,Username,Password,AuthenticationMode,accountCurrency as Currency,ROUND(Rate,6) as Rate from
	(select 
		@VendorConnectionName := VendorConnectionName,

				concat(IFNULL(TrunkPrefix ,""),DestinationCode) as Prefix,VendorID,VendorName,SipHeader,IP,Port,Username,Password,AuthenticationMode,TimezoneId,v_AccountCurrency_ as accountCurrency,
				CASE WHEN  Currency = v_AccountCurrency_
						THEN
							Rate
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ )
							* (Rate  / (Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ ))							
						)
						END as Rate,VendorConnectionName,Preference  from tblVendorRate
						
						 where (TimezoneId = p_TimeZone OR (TimezoneId <> p_TimeZone and TimezoneId=1))
						  and p_DestinationCode like  CONCAT(DestinationCode,"%")  
						 and @VendorConnectionName <> VendorConnectionName
						  order by Rate,Preference desc,CONCAT(DestinationCode,"%") desc) vendorTable;


						
						
END IF;
END IF;


END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

-- --------------------------------------------------------
-- Host:                         188.227.186.98
-- Server version:               5.7.18 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table speakintelligentRM.tblServiceTemapleInboundTariff
CREATE TABLE IF NOT EXISTS `tblServiceTemapleInboundTariff` (
  `ServiceTemapleInboundTariffId` int(11) NOT NULL AUTO_INCREMENT,
  `ServiceTemplateID` int(11) DEFAULT NULL,
  `DIDCategoryId` int(11) DEFAULT NULL,
  `RateTableId` bigint(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ServiceTemapleInboundTariffId`),
  KEY `tblServiceTemapleInboundTariff_ibfk_1_idx_idx` (`ServiceTemplateID`),
  KEY `tblServiceTemapleInboundTariff_ibfk_2_idx_idx` (`DIDCategoryId`),
  KEY `tblServiceTemapleInboundTariff_ibfk_3_idx_idx` (`RateTableId`),
  CONSTRAINT `tblServiceTemapleInboundTariff_ibfk_1_idx` FOREIGN KEY (`ServiceTemplateID`) REFERENCES `tblServiceTemplate` (`ServiceTemplateId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `tblServiceTemapleInboundTariff_ibfk_2_idx` FOREIGN KEY (`DIDCategoryId`) REFERENCES `tblDIDCategory` (`DIDCategoryID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `tblServiceTemapleInboundTariff_ibfk_3_idx` FOREIGN KEY (`RateTableId`) REFERENCES `tblRateTable` (`RateTableId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table speakintelligentRM.tblServiceTemapleSubscription
CREATE TABLE IF NOT EXISTS `tblServiceTemapleSubscription` (
  `ServiceTemplateSubscriptionId` int(11) NOT NULL AUTO_INCREMENT,
  `ServiceTemplateID` int(11) DEFAULT NULL,
  `SubscriptionId` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ServiceTemplateSubscriptionId`)
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table speakintelligentRM.tblServiceTemplate
CREATE TABLE IF NOT EXISTS `tblServiceTemplate` (
  `ServiceTemplateId` int(11) NOT NULL AUTO_INCREMENT,
  `ServiceId` int(11) DEFAULT NULL,
  `Name` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InboundDiscountPlanId` int(11) DEFAULT NULL,
  `OutboundDiscountPlanId` int(11) DEFAULT NULL,
  `OutboundRateTableId` bigint(20) DEFAULT NULL,
  `CurrencyId` int(11) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ServiceTemplateId`),
  UNIQUE KEY `tbltblServiceTemplate_ibfk_6` (`Name`),
  KEY `tbltblServiceTemplate_ibfk_1_idx` (`ServiceId`),
  KEY `tbltblServiceTemplate_ibfk_2_idx` (`InboundDiscountPlanId`),
  KEY `tbltblServiceTemplate_ibfk_3_idx` (`OutboundDiscountPlanId`),
  KEY `tbltblServiceTemplate_ibfk_4_idx` (`OutboundRateTableId`),
  KEY `tbltblServiceTemplate_ibfk_5` (`CurrencyId`),
  CONSTRAINT `tbltblServiceTemplate_ibfk_1` FOREIGN KEY (`ServiceId`) REFERENCES `tblService` (`ServiceID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `tbltblServiceTemplate_ibfk_2` FOREIGN KEY (`InboundDiscountPlanId`) REFERENCES `tblDiscountPlan` (`DiscountPlanID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `tbltblServiceTemplate_ibfk_3` FOREIGN KEY (`OutboundDiscountPlanId`) REFERENCES `tblDiscountPlan` (`DiscountPlanID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `tbltblServiceTemplate_ibfk_4` FOREIGN KEY (`OutboundRateTableId`) REFERENCES `tblRateTable` (`RateTableId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `tbltblServiceTemplate_ibfk_5` FOREIGN KEY (`CurrencyId`) REFERENCES `tblCurrency` (`CurrencyId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

-- --------------------------------------------------------
-- Host:                         188.227.186.98
-- Server version:               5.7.18 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table speakintelligentCDR.tblgetTimezone
CREATE TABLE IF NOT EXISTS `tblgetTimezone` (
  `getTimezoneID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `connect_time` datetime DEFAULT NULL,
  `disconnect_time` datetime DEFAULT NULL,
  `TimezonesID` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`getTimezoneID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
