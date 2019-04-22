/* Vos5000 Integration */
USE RMCDR3;
DROP TABLE IF EXISTS `tblActiveCall`;
CREATE TABLE IF NOT EXISTS `tblActiveCall` (
  `ActiveCallID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `CompanyID` int(11) NOT NULL,
  `UUID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ConnectTime` datetime NOT NULL,
  `DisconnectTime` datetime DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  `CLI` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLD` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLIPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLDPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Cost` decimal(18,6) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT '0',
  `CallType` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CallRecordingStartTime` datetime DEFAULT NULL,
  `CallRecordingEndTime` datetime DEFAULT NULL,
  `CallRecording` tinyint(4) DEFAULT '0',
  `AccountServicePackageID` int(11) DEFAULT NULL,
  `RecordingCostPerMinute` decimal(18,6) DEFAULT NULL,
  `PackageCostPerMinute` decimal(18,6) DEFAULT NULL,
  `CallRecordingDuration` int(11) DEFAULT '0',
  `Surcharges` decimal(18,6) DEFAULT NULL,
  `Chargeback` decimal(18,6) DEFAULT NULL,
  `CollectionCostAmount` decimal(18,6) DEFAULT '0.000000',
  `CollectionCostPercentage` decimal(18,6) DEFAULT '0.000000',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TrunkID` int(11) DEFAULT NULL,
  `VendorID` int(11) DEFAULT NULL,
  `AccountServiceID` int(11) DEFAULT '0',
  `IsBlock` tinyint(4) DEFAULT '0',
  `BlockReason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountPKID` int(11) DEFAULT NULL,
  `RateTableID` int(11) DEFAULT '0',
  `RateTableRateID` int(11) DEFAULT '0',
  `TimezonesID` int(11) DEFAULT '0',
  `billed_duration` int(11) DEFAULT '0',
  `RateTableDIDRateID` int(11) DEFAULT '0',
  `CostPerCall` decimal(18,6) DEFAULT '0.000000',
  `CostPerMinute` decimal(18,6) DEFAULT '0.000000',
  `SurchargePerCall` decimal(18,6) DEFAULT '0.000000',
  `SurchargePerMinute` decimal(18,6) DEFAULT '0.000000',
  `OutpaymentPerCall` decimal(18,6) DEFAULT '0.000000',
  `OutpaymentPerMinute` decimal(18,6) DEFAULT '0.000000',
  `VendorConnectionName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `OriginType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `OriginProvider` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RateTablePKGRateID` bigint(20) DEFAULT '0',
  `VendorRate` decimal(18,6) DEFAULT '0.000000',
  `VendorCLIPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VendorCLDPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ActiveCallID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `tblActiveCall`
	ADD COLUMN `CallerPDD` BIGINT(20) NULL DEFAULT NULL AFTER `VendorCLDPrefix`,
	ADD COLUMN `CalleePDD` BIGINT(20) NULL DEFAULT NULL AFTER `CallerPDD`,
	ADD COLUMN `MappingGateway` VARCHAR(255) NULL DEFAULT NULL AFTER `CalleePDD`,
	ADD COLUMN `RoutingGateway` VARCHAR(255) NULL DEFAULT NULL AFTER `MappingGateway`,
	ADD COLUMN `CustomerIP` VARCHAR(255) NULL DEFAULT NULL AFTER `RoutingGateway`,
	ADD COLUMN `SupplierIPRTP` VARCHAR(255) NULL DEFAULT NULL AFTER `CustomerIP`,
	ADD COLUMN `Codec` VARCHAR(255) NULL DEFAULT NULL AFTER `SupplierIPRTP`;




DROP PROCEDURE IF EXISTS `prc_getActiveCalls`;
DELIMITER //
CREATE PROCEDURE `prc_getActiveCalls`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_CLI` VARCHAR(255),
	IN `p_CLD` VARCHAR(255),
	IN `p_CLDPrefix` VARCHAR(255),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CallType` INT,
	IN `p_TrunkID` INT,
	IN `p_ServiceID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_Export` INT


)
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    SELECT   
			acct.AccountName,
			ac.CLI,
			ac.CLD,
			ac.CLDPrefix,
			ac.ConnectTime,
			ac.Cost,
			t.Trunk,
			s.ServiceName,
			case when ac.CallType = '0' then 'Inbound' ELSE 'Outbound' END as CallType
    FROM tblActiveCall ac
			INNER JOIN Ratemanagement3.tblAccount acct
				ON acct.AccountID=ac.AccountID
			LEFT JOIN Ratemanagement3.tblTrunk t
				ON t.TrunkID=ac.TrunkID	
			LEFT JOIN Ratemanagement3.tblService s
				ON s.ServiceID=ac.ServiceID		
         where ac.CompanyID = p_CompanyID
         AND((p_AccountID = 0 OR ac.AccountID = p_AccountID))
			AND(p_CLI ='' OR ac.CLI like Concat('%',p_CLI,'%'))
			AND(p_CLD ='' OR ac.CLD like Concat('%',p_CLD,'%'))
			AND(p_CLDPrefix ='' OR ac.CLDPrefix like Concat(p_CLDPrefix,'%'))
			AND((p_CallType = -1 OR ac.CallType = p_CallType))
			AND((p_TrunkID = 0 OR ac.TrunkID = p_TrunkID))
			AND((p_CompanyGatewayID = 0 OR ac.CompanyGatewayID = p_CompanyGatewayID))
			AND((p_ServiceID = 0 OR ac.ServiceID = p_ServiceID))
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN acct.AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN acct.AccountName
                END ASC,
				
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CLIDESC') THEN ac.CLI
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CLIASC') THEN ac.CLI
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CLDDESC') THEN ac.CLD
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CLDASC') THEN ac.CLD
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectTimeDESC') THEN ac.ConnectTime
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectTimeASC') THEN ac.ConnectTime
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(ac.ActiveCallID) AS totalcount
         FROM tblActiveCall ac
			INNER JOIN Ratemanagement3.tblAccount acct
				ON acct.AccountID=ac.AccountID
			LEFT JOIN Ratemanagement3.tblTrunk t
				ON t.TrunkID=ac.TrunkID	
			LEFT JOIN Ratemanagement3.tblService s
				ON s.ServiceID=ac.ServiceID		
         where ac.CompanyID = p_CompanyID
         AND((p_AccountID = 0 OR ac.AccountID = p_AccountID))
			AND(p_CLI ='' OR ac.CLI like Concat('%',p_CLI,'%'))
			AND(p_CLD ='' OR ac.CLD like Concat('%',p_CLD,'%'))
			AND(p_CLDPrefix ='' OR ac.CLDPrefix like Concat(p_CLDPrefix,'%'))
			AND((p_CallType = -1 OR ac.CallType = p_CallType))
			AND((p_TrunkID = 0 OR ac.TrunkID = p_TrunkID))
			AND((p_CompanyGatewayID = 0 OR ac.CompanyGatewayID = p_CompanyGatewayID))
			AND((p_ServiceID = 0 OR ac.ServiceID = p_ServiceID));

	ELSE

			SELECT
				acct.AccountName,
				ac.CLI,
				ac.CLD,
				ac.CLDPrefix,
				ac.ConnectTime,
				ac.Cost,
				t.Trunk,
				s.ServiceName,
				case when ac.CallType = '1' then 'Inbound' ELSE 'Outbound' END as CallType
         FROM tblActiveCall ac
			INNER JOIN Ratemanagement3.tblAccount acct
				ON acct.AccountID=ac.AccountID
			LEFT JOIN Ratemanagement3.tblTrunk t
				ON t.TrunkID=ac.TrunkID	
			LEFT JOIN Ratemanagement3.tblService s
				ON s.ServiceID=ac.ServiceID		
         where ac.CompanyID = p_CompanyID
         AND((p_AccountID = 0 OR ac.AccountID = p_AccountID))
			AND(p_CLI ='' OR ac.CLI like Concat('%',p_CLI,'%'))
			AND(p_CLD ='' OR ac.CLD like Concat('%',p_CLD,'%'))
			AND(p_CLDPrefix ='' OR ac.CLDPrefix like Concat(p_CLDPrefix,'%'))
			AND((p_CallType = -1 OR ac.CallType = p_CallType))
			AND((p_TrunkID = 0 OR ac.TrunkID = p_TrunkID))
			AND((p_CompanyGatewayID = 0 OR ac.CompanyGatewayID = p_CompanyGatewayID))
			AND((p_ServiceID = 0 OR ac.ServiceID = p_ServiceID));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;



CREATE TABLE IF NOT EXISTS `tblVOSVendorActiveCall` (
  `VOSVendorActiveCallID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `GatewayName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CallPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCurrentCalls` int(11) DEFAULT NULL,
  `Asr` decimal(18,6) DEFAULT NULL,
  `Acd` decimal(18,6) DEFAULT NULL,
  `RemoteIP` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VOSVendorActiveCallID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




DROP PROCEDURE IF EXISTS `prc_getVOSVendorActiveCall`;
DELIMITER //
CREATE PROCEDURE `prc_getVOSVendorActiveCall`(
	IN `p_CompanyID` INT,
	IN `p_GatewayName` VARCHAR(255),
	IN `p_CallPrefix` VARCHAR(255),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_CompanyGatewayID` INT,
	IN `p_Export` INT



)
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    SELECT   
			vac.GatewayName,
			vac.CallPrefix,
			vac.TotalCurrentCalls,
			vac.Asr,
			vac.Acd,
			vac.RemoteIP
			
    FROM tblVOSVendorActiveCall vac			
        WHERE vac.CompanyID = p_CompanyID
		AND(p_GatewayName ='' OR vac.GatewayName like Concat('%',p_GatewayName,'%'))
		AND(p_CompanyGatewayID = 0 OR vac.CompanyGatewayID = p_CompanyGatewayID)
		AND(p_CallPrefix ='' OR vac.CallPrefix like Concat(p_CallPrefix,'%'))
			
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GatewayNameDESC') THEN vac.GatewayName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GatewayNameASC') THEN vac.GatewayName
                END ASC,
				
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallPrefixDESC') THEN vac.CallPrefix
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallPrefixASC') THEN vac.CallPrefix
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RemoteIPDESC') THEN vac.RemoteIP
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RemoteIPASC') THEN vac.RemoteIP
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
				COUNT(vac.VOSVendorActiveCallID) AS totalcount
			FROM tblVOSVendorActiveCall vac
			WHERE vac.CompanyID = p_CompanyID
			AND(p_GatewayName ='' OR vac.GatewayName like Concat('%',p_GatewayName,'%'))
			AND(p_CompanyGatewayID = 0 OR vac.CompanyGatewayID = p_CompanyGatewayID)
			AND(p_CallPrefix ='' OR vac.CallPrefix like Concat(p_CallPrefix,'%'));

	ELSE

			SELECT
				vac.GatewayName,
				vac.CallPrefix,
				vac.TotalCurrentCalls,
				vac.Asr,
				vac.Acd,
				vac.RemoteIP
         FROM tblVOSVendorActiveCall vac
			WHERE vac.CompanyID = p_CompanyID
			AND(p_GatewayName ='' OR vac.GatewayName like Concat('%',p_GatewayName,'%'))
			AND(p_CompanyGatewayID = 0 OR vac.CompanyGatewayID = p_CompanyGatewayID)
			AND(p_CallPrefix ='' OR vac.CallPrefix like Concat(p_CallPrefix,'%'));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getVOSActiveCalls`;
DELIMITER //
CREATE PROCEDURE `prc_getVOSActiveCalls`(
	IN `p_CompanyID` INT,
	IN `p_CLI` VARCHAR(255),
	IN `p_CLD` VARCHAR(255),
	IN `p_MappingGateway` VARCHAR(255),
	IN `p_RoutingGateway` VARCHAR(255),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT

)
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    SELECT   
			vac.CLI,
			vac.CLD,
			vac.MappingGateway,
			vac.RoutingGateway,
			vac.CallerPDD,
			vac.CalleePDD,
			vac.ConnectTime,
			vac.Duration,
			vac.Codec,
			vac.CustomerIP,
			vac.SupplierIPRTP
			
    FROM tblActiveCall vac			
        WHERE vac.CompanyID = p_CompanyID
		AND(p_CLI ='' OR vac.CLI like Concat('%',p_CLI,'%'))
		AND(p_CLD ='' OR vac.CLD like Concat('%',p_CLD,'%'))
		AND(p_MappingGateway ='' OR vac.MappingGateway like Concat('%',p_MappingGateway,'%'))
		AND(p_RoutingGateway ='' OR vac.RoutingGateway like Concat('%',p_RoutingGateway,'%'))
		
			
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CLIDESC') THEN vac.CLI
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CLIASC') THEN vac.CLI
                END ASC,
				
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CLDDESC') THEN vac.CLD
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CLDASC') THEN vac.CLD
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MappingGatewayDESC') THEN vac.MappingGateway
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MappingGatewayASC') THEN vac.MappingGateway
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingGatewayDESC') THEN vac.RoutingGateway
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingGatewayASC') THEN vac.RoutingGateway
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectTimeDESC') THEN vac.ConnectTime
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectTimeASC') THEN vac.ConnectTime
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
				COUNT(vac.ActiveCallID) AS totalcount
			FROM tblActiveCall vac			
			WHERE vac.CompanyID = p_CompanyID
			AND(p_CLI ='' OR vac.CLI like Concat('%',p_CLI,'%'))
			AND(p_CLD ='' OR vac.CLD like Concat('%',p_CLD,'%'))
			AND(p_MappingGateway ='' OR vac.MappingGateway like Concat('%',p_MappingGateway,'%'))
			AND(p_RoutingGateway ='' OR vac.RoutingGateway like Concat('%',p_RoutingGateway,'%'));

	ELSE

			SELECT
				vac.CLI,
				vac.CLD,
				vac.MappingGateway,
				vac.RoutingGateway,
				vac.CallerPDD,
				vac.CalleePDD,
				vac.ConnectTime,
				vac.Duration,
				vac.Codec,
				vac.CustomerIP,
				vac.SupplierIPRTP
			FROM tblActiveCall vac			
				WHERE vac.CompanyID = p_CompanyID
				AND(p_CLI ='' OR vac.CLI like Concat('%',p_CLI,'%'))
				AND(p_CLD ='' OR vac.CLD like Concat('%',p_CLD,'%'))
				AND(p_MappingGateway ='' OR vac.MappingGateway like Concat('%',p_MappingGateway,'%'))
				AND(p_RoutingGateway ='' OR vac.RoutingGateway like Concat('%',p_RoutingGateway,'%'));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;




USE Ratemanagement3;

/* Lock wait timeout - 1wordtec and other env */

DROP PROCEDURE IF EXISTS `prc_UpdateMysqlPID`;
DELIMITER //
CREATE PROCEDURE `prc_UpdateMysqlPID`(
	IN `p_processId` VARCHAR(200)
)
BEGIN
	DECLARE MysqlPID VARCHAR(200);
--	  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
		
		SELECT CONNECTION_ID() into MysqlPID;
		
		UPDATE tblCronJob
			SET MysqlPID=MysqlPID
		WHERE ProcessID=p_processId;
		
		COMMIT;

--	  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

/* END- Lock wait timeout - 1wordtec and other env */

INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'SIDEBAR_ACTIVECALL_MENU', '1');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'VENDOR_ACTIVECALL_BTN_LOADACTIVECALL', '1');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'VENDOR_ACTIVECALL_MENU', '1');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'VOS_ACTIVECALL_MENU', '1');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'VOSACTIVECALL_BTN_LOADACTIVECALL', '1');



INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (14, 'API URL', 'APIURL', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);



INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1373, 'ActiveCall.All', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1374, 'ActiveCall.View', 1, 7);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ActiveCall.ajax_datagrid', 'ActiveCallController.ajax_datagrid', 1, 'Sumera Saeed', NULL, '2019-02-07 17:57:49.000', '2019-02-07 17:57:49.000', 1374);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ActiveCall.*', 'ActiveCallController.*', 1, 'Sumera Saeed', NULL, '2019-02-07 17:57:49.000', '2019-02-07 17:57:49.000', 1373);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('ActiveCall.index', 'ActiveCallController.index', 1, 'Sumera Saeed', NULL, '2019-02-07 17:57:49.000', '2019-02-07 17:57:49.000', 1374);


INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1375, 'VendorActiveCall.View', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1376, 'VendorActiveCall.All', 1, 7);


INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2713, 'VendorActiveCall.ajax_datagrid', 'VendorActiveCallController.ajax_datagrid', 1, 'Sumera Saeed', NULL, '2019-02-12 12:24:45.000', '2019-02-12 12:24:45.000', 1375);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2712, 'VendorActiveCall.*', 'VendorActiveCallController.*', 1, 'Sumera Saeed', NULL, '2019-02-12 12:24:45.000', '2019-02-12 12:24:45.000', 1376);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2711, 'VendorActiveCall.index', 'VendorActiveCallController.index', 1, 'Sumera Saeed', NULL, '2019-02-12 12:24:45.000', '2019-02-12 12:24:45.000', 1375);


INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1378, 'VOSActiveCall.View', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1377, 'VOSActiveCall.All', 1, 7);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('VOSActiveCall.ajax_datagrid', 'VOSActiveCallController.ajax_datagrid', 1, 'Sumera Saeed', NULL, '2019-02-12 19:08:27.000', '2019-02-12 19:08:27.000', 1378);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('VOSActiveCall.*', 'VOSActiveCallController.*', 1, 'Sumera Saeed', NULL, '2019-02-12 19:08:27.000', '2019-02-12 19:08:27.000', 1377);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('VOSActiveCall.index', 'VOSActiveCallController.index', 1, 'Sumera Saeed', NULL, '2019-02-12 19:08:27.000', '2019-02-12 19:08:27.000', 1378);




DROP TABLE IF EXISTS `tblAccountsIP`;
CREATE TABLE IF NOT EXISTS `tblAccountsIP` (
  `AccountsIP` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CustomerIP` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `VendorIP` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  `AccountServiceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`AccountsIP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `tblVOSAccountBalance`;
CREATE TABLE IF NOT EXISTS `tblVOSAccountBalance` (
  `VOSAccountBalanceID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `AccountName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountBalance` decimal(18,10) DEFAULT NULL,
  `AccountNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VOSAccountBalanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `tblVosAccount`;
CREATE TABLE IF NOT EXISTS `tblVosAccount` (
  `VOSAccountBalanceID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `AccountName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountBalance` decimal(18,10) DEFAULT NULL,
  `AccountNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address` text COLLATE utf8_unicode_ci,
  `PostCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Fax` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LimitMoney` decimal(18,6) DEFAULT NULL,
  `AccountType` smallint(6) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VOSAccountBalanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 14, 'Import VOS Account Balance', 'importvosaccountbalance', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2019-02-18 13:45:49', 'RateManagementSystem');


INSERT INTO `tblCronJob` (`CompanyID`, `CronJobCommandID`, `Settings`, `Status`, `LastRunTime`, `NextRunTime`, `created_at`, `created_by`, `updated_at`, `updated_by`, `Active`, `JobTitle`, `DownloadActive`, `PID`, `EmailSendTime`, `CdrBehindEmailSendTime`, `CdrBehindDuration`, `ProcessID`, `MysqlPID`) VALUES (1, 603, '{"ThresholdTime":"","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobStartTime":"12:00:00 AM","CompanyGatewayID":"31"}', 1, '2019-02-19 11:47:00', '2019-02-19 11:48:00', '2019-02-18 00:00:00', 'Sumera Saeed', '2019-02-19 11:47:17', NULL, 0, 'Get VOS Account Balance', 0, '', NULL, NULL, NULL, '', '');


INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 14, 'Import VOS Accounts', 'importvosaccounts', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2019-02-18 13:45:49', 'RateManagementSystem');

INSERT INTO `tblCronJob` (`CompanyID`, `CronJobCommandID`, `Settings`, `Status`, `LastRunTime`, `NextRunTime`, `created_at`, `created_by`, `updated_at`, `updated_by`, `Active`, `JobTitle`, `DownloadActive`, `PID`, `EmailSendTime`, `CdrBehindEmailSendTime`, `CdrBehindDuration`, `ProcessID`, `MysqlPID`) VALUES (1, 604, '{"ThresholdTime":"","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["DAILY","SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":"31"}', 1, NULL, '2019-02-20 14:06:00', '2019-02-20 00:00:00', 'Sumera Saeed', '2019-02-20 14:05:22', NULL, 0, 'Import VOS Account', 0, NULL, NULL, NULL, NULL, NULL, NULL);


DELIMITER //
CREATE PROCEDURE `prc_getVOSAccountBalance`(
	IN `p_CompanyID` INT,
	IN `p_AccountName` VARCHAR(255),
	IN `p_AccountNumber` VARCHAR(255),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT

)
BEGIN
     DECLARE v_OffSet_ int;
     DECLARE v_Round_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	

	if p_Export = 0
	THEN

		SELECT   
			vab.AccountName,
			vab.AccountNumber,
			ROUND(COALESCE(vab.AccountBalance,0),v_Round_) as AccountBalance
			
						
		FROM tblVOSAccountBalance vab		
        WHERE vab.CompanyID = p_CompanyID
        AND(p_AccountName ='' OR (vab.AccountName like Concat(p_AccountName,'%') ))
		  AND(p_AccountNumber ='' OR (vab.AccountNumber like Concat(p_AccountNumber,'%') ))				
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN vab.AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN vab.AccountName
                END ASC,
				
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNumberDESC') THEN vab.AccountNumber
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNumberASC') THEN vab.AccountNumber
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountBalanceDESC') THEN vab.AccountBalance
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountBalanceASC') THEN vab.AccountBalance
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
				COUNT(vab.VOSAccountBalanceID) AS totalcount
			FROM tblVOSAccountBalance vab		
			WHERE vab.CompanyID = p_CompanyID
			AND(p_AccountName ='' OR (vab.AccountName like Concat(p_AccountName,'%') ))
		  AND(p_AccountNumber ='' OR (vab.AccountNumber like Concat(p_AccountNumber,'%') ));

	ELSE

			SELECT   
			vab.AccountName,
			vab.AccountNumber,
			ROUND(COALESCE(vab.AccountBalance,0),v_Round_) as AccountBalance
						
		FROM tblVOSAccountBalance vab		
        WHERE vab.CompanyID = p_CompanyID
		  AND(p_AccountName ='' OR (vab.AccountName like Concat(p_AccountName,'%') ))
		  AND(p_AccountNumber ='' OR (vab.AccountNumber like Concat(p_AccountNumber,'%') ));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;



CREATE TABLE IF NOT EXISTS `tblVosIP` (
  `VOSIPID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `AccountName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RemoteIps` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IPType` tinyint(1) DEFAULT NULL,
  `LocalIP` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VOSIPID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



CREATE TABLE IF NOT EXISTS `tblVOSIPAgainstAccount` (
  `VOSIPAgainstAccountID` int(11) NOT NULL AUTO_INCREMENT,
  `VOSIPID` int(11) NOT NULL,
  `AccountName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IPType` tinyint(4) DEFAULT NULL,
  `IP` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VOSIPAgainstAccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `tblAccount`
	CHANGE COLUMN `Address1` `Address1` TEXT NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `Description`;


DROP PROCEDURE IF EXISTS `prc_VOSCreateAccountOnNeonIfNotExist`;
DELIMITER //
CREATE PROCEDURE `prc_VOSCreateAccountOnNeonIfNotExist`(
	IN `p_CompanyID` INT	

)
BEGIN

    DECLARE v_CustomerType int;
    DECLARE v_VendorType int;
	 DECLARE v_cnt int;
	     
    SET v_CustomerType=0;
    SET v_VendorType = 2;
    SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
     
	  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	  
	  DROP TEMPORARY TABLE IF EXISTS tmp_vos_account;
				CREATE TEMPORARY TABLE tmp_vos_account (
					AccountName VARCHAR(255),
					AccountType INT,
					AccountID INT default null
				);

     			
			DROP TEMPORARY TABLE IF EXISTS tmp_VOS_account_detail;
				CREATE TEMPORARY TABLE tmp_VOS_account_detail (
					VOSAccountBalanceID INT ,
					AccountName VARCHAR(255),
					AccountType INT,
					isBothcustVendor INT
				);
				
			DROP TEMPORARY TABLE IF EXISTS tmp_account_auth;
				CREATE TEMPORARY TABLE tmp_account_auth (
					AccountID INT,
					CustomerAuthRule VARCHAR(255),
					CustomerAuthValue TEXT,
					VendorAuthRule VARCHAR(255),
					VendorAuthValue TEXT
					
				);			
					
			DROP TEMPORARY TABLE IF EXISTS tmp_account_authenticate;
				CREATE TEMPORARY TABLE tmp_account_authenticate (
					AccountName VARCHAR(255),
					IP TEXT
				);	
				
			DROP TEMPORARY TABLE IF EXISTS tmp_create_account;
				CREATE TEMPORARY TABLE tmp_create_account (
					AccountName VARCHAR(255)
				);		
		/* Process		
		/* Process of exists Account in Neon */	
		
		
		INSERT INTO tmp_vos_account
		SELECT 
		 	DISTINCT
			AccountName,
			AccountType,
			null
		FROM tblVosAccount;
		
		
		UPDATE tmp_vos_account tva1
		INNER JOIN tblAccount a1
		ON tva1.AccountName=a1.AccountName
		SET tva1.AccountID=a1.AccountID
		WHERE a1.AccountType=1;
		
		
		select count(*) INTO v_cnt from tmp_vos_account where AccountID is null;
		
		IF v_cnt > 0 THEN
			
			/* Check Customer Authentication with Account Name */
			UPDATE
				tmp_vos_account va2
			INNER JOIN tblAccountAuthenticate au1
			ON va2.AccountName = au1.CustomerAuthValue
			INNER JOIN tblAccount a2
			ON a2.AccountID=au1.AccountID
			SET va2.AccountID = a2.AccountID 
			WHERE 
			va2.AccountID is null
			AND a2.AccountType=1 AND au1.CustomerAuthRule='other';
		
		END IF;
		
		select count(*) INTO v_cnt from tmp_vos_account where AccountID is null;
	--	select v_cnt;
		
		IF v_cnt > 0 THEN
		
		/* Check Vendor Authentication with Account Name */
			UPDATE
				tmp_vos_account va3
			INNER JOIN tblAccountAuthenticate au2
			ON va3.AccountName = au2.VendorAuthValue
			INNER JOIN tblAccount a2
			ON a2.AccountID=au2.AccountID
			SET va3.AccountID = a2.AccountID 
			WHERE 
			va3.AccountID is null
			AND a2.AccountType=1 AND au2.VendorAuthRule='other';
		END IF;
		
		select count(*) INTO v_cnt from tmp_vos_account where AccountID is null;
	--	select v_cnt;
		
		IF v_cnt > 0 THEN
			-- select * from tmp_vos_account;
			
			INSERT INTO tmp_account_auth
			SELECT
			AccountID,
			CustomerAuthRule,
			CustomerAuthValue,
			VendorAuthRule,
			VendorAuthValue
			FROM tblAccountAuthenticate
			WHERE (CustomerAuthRule='IP' AND CustomerAuthValue!='') OR (VendorAuthRule='IP' AND VendorAuthValue!='');
			
			INSERT INTO tmp_account_authenticate
			SELECT
				DISTINCT
				b.AccountName,
				b.IP
			FROM tmp_vos_account a 
			join tblVOSIPAgainstAccount b on a.AccountName=b.AccountName 
			join tmp_account_auth c on FIND_IN_SET(b.IP,c.CustomerAuthValue) > 0
			WHERE 
				a.AccountID is null 
			AND b.AccountName!=''
			AND b.IP!='';
			
			-- check Customer Auth Rule Value IP
			
			UPDATE 
				tmp_vos_account va4 
			join tmp_account_authenticate b 
			on va4.AccountName=b.AccountName 
			join tmp_account_auth c 
			on FIND_IN_SET(b.IP,c.CustomerAuthValue) > 0 
			INNER JOIN tblAccount a3
			on a3.AccountID=c.AccountID
			SET va4.AccountID=c.AccountID
			WHERE va4.AccountID is null
			AND a3.AccountType=1
			;
						
		END IF;
		
		select count(*) INTO v_cnt from tmp_vos_account where AccountID is null;
	--	select v_cnt;
		
		IF v_cnt > 0 THEN
		-- check Vendor Auth Rule Value IP
		
			truncate table tmp_account_authenticate;
			
			INSERT INTO tmp_account_authenticate
			SELECT
				DISTINCT
				b.AccountName,
				b.IP
			FROM tmp_vos_account a 
			join tblVOSIPAgainstAccount b on a.AccountName=b.AccountName 
			join tmp_account_auth c on FIND_IN_SET(b.IP,c.VendorAuthValue) > 0
			WHERE 
				a.AccountID is null 
			AND b.AccountName!=''
			AND b.IP!='';
		
			UPDATE 
				tmp_vos_account a 
			join tmp_account_authenticate b 
			on a.AccountName=b.AccountName 
			join tmp_account_auth c 
			on FIND_IN_SET(b.IP,c.VendorAuthValue) > 0
			INNER JOIN tblAccount a4
			on a4.AccountID=c.AccountID
			
			SET a.AccountID=c.AccountID
			
			WHERE a.AccountID is null;
			
		END IF;
		
		select count(*) INTO v_cnt from tmp_vos_account where AccountID is null;
		-- select v_cnt;
		
		/*Update Customer & Vendor Accounts Flag*/
		
		/* Check Customer */
		
		UPDATE
			tblAccount acct
		INNER JOIN tmp_vos_account acct2
		ON acct2.AccountID=acct.AccountID
		SET acct.IsCustomer=1
		WHERE 
		acct2.AccountID is not null
		AND (acct.IsCustomer is null || acct.IsCustomer !=1) 
		AND acct2.AccountType=v_CustomerType;
		
		/* Check Vendor */
		
		UPDATE
			tblAccount acct
		INNER JOIN tmp_vos_account acct2
		ON acct2.AccountID=acct.AccountID
		SET acct.IsVendor=1
		WHERE 
		acct2.AccountID is not null
		AND (acct.IsVendor is null || acct.IsVendor !=1) 
		AND acct2.AccountType=v_VendorType;
		
		
		IF v_cnt > 0 THEN
			/* Create Account */
			INSERT INTO tmp_create_account
			SELECT AccountName
			FROM 
			tmp_vos_account 
			WHERE AccountID is null group by AccountName;
			
		
			INSERT INTO tblAccount
				(AccountType,CompanyId,Number,AccountName,Email,IsVendor,IsCustomer,Phone,Fax,Address1,PostCode,BillingEmail,`Status`,created_at,created_by)
				
					SELECT
						1 as AccountType, 
						-- b.CompanyID as CompanyId,
						p_CompanyID as CompanyId,
						b.AccountNumber as Number,
						b.AccountName as AccountName,
						b.Email as Email,
						
						CASE WHEN
							
							 (SELECT count(*) FROM tblVosAccount d WHERE d.AccountType=v_VendorType AND d.AccountName=a.AccountName) > 0
						THEN 1 
						ELSE 0
						END as IsVendor,
						
						CASE WHEN
							
							 (SELECT count(*) FROM tblVosAccount c WHERE c.AccountType=v_CustomerType AND c.AccountName=a.AccountName) > 0
						THEN 1 
						ELSE 0
						END as IsCustomer,
						
						b.Phone as Phone,
						b.Fax as Fax,
						b.Address as Address1,
						b.PostCode as PostCode,
						b.Email as BillingEmail,
						1 as `Status`,
						NOW() as created_at,
						'VOSAPI' as created_by
						
					FROM tmp_create_account a 
					JOIN tblVosAccount b ON a.AccountName = b.AccountName 
					GROUP BY b.AccountName;
								
		
		END IF;		
		
		SELECT count(AccountName) as TotalAccountCreate
			FROM 
			tmp_create_account 
		;
		

		

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getVOSAccountIP`;
DELIMITER //
CREATE PROCEDURE `prc_getVOSAccountIP`(
	IN `p_CompanyID` INT,
	IN `p_AccountName` VARCHAR(255),
	IN `p_RemoteIps` VARCHAR(255),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT


)
BEGIN
     DECLARE v_OffSet_ int;
     DECLARE v_Round_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_VOSIPCount;
				CREATE TEMPORARY TABLE tmp_VOSIPCount (
					VosIPID int
				);
	

	if p_Export = 0
	THEN

		SELECT   
			
			vi.AccountName,
			vi.RemoteIps,
			CASE WHEN vi.IPType=1 THEN 'Vendor' ELSE 'Customer' END as IPType
									
		FROM tblVosIP vi		
        WHERE vi.CompanyID = p_CompanyID
        AND vi.AccountName!=''
        AND(p_AccountName ='' OR (vi.AccountName like Concat(p_AccountName,'%')))
        AND(p_RemoteIps ='' OR (vi.RemoteIps like Concat(p_RemoteIps,'%')))
		  group by  vi.AccountName,vi.RemoteIps,vi.IPType	
		  
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN vi.AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN vi.AccountName
                END ASC,
				
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IPTypeDESC') THEN vi.IPType
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IPTypeASC') THEN vi.IPType
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;
            
            INSERT INTO tmp_VOSIPCount
            	SELECT
						vi.VOSIPID
            	FROM tblVosIP vi		
			WHERE vi.CompanyID = p_CompanyID
			AND(p_AccountName ='' OR (vi.AccountName like Concat(p_AccountName,'%')))
        AND(p_RemoteIps ='' OR (vi.RemoteIps like Concat(p_RemoteIps,'%')))
		  GROUP BY  vi.AccountName,vi.RemoteIps,vi.IPType;
            

			SELECT
				COUNT(*) AS totalcount
			FROM tmp_VOSIPCount;

	ELSE

			SELECT   
				vi.AccountName,
				vi.RemoteIps,
				CASE WHEN vi.IPType=1 THEN 'Vendor' ELSE 'Customer' END as IPType
									
			FROM tblVosIP vi		
			WHERE vi.CompanyID = p_CompanyID
			AND(p_AccountName ='' OR (vi.AccountName like Concat(p_AccountName,'%')))
        AND(p_RemoteIps ='' OR (vi.RemoteIps like Concat(p_RemoteIps,'%')))
		  GROUP BY  vi.AccountName,vi.RemoteIps,vi.IPType;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;




/*
----- Add in tblCompanyConfiguration in Key "BILLING_DASHBOARD" ----

BillingDashboardVOSAccountBalanceWidget
BillingDashboardVOSAccountIPWidget

*/

INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1379, 'BillingDashboardVOSAccountIPWidget.View', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1380, 'BillingDashboardVOSAccountBalanceWidget.View', 1, 7);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'BillingDashboard@VOSAccountBalance_ajax_datagrid', 'BillingDashboard.VOSAccountBalance_ajax_datagrid', 1, 'Sumera Saeed', NULL, '2019-02-26 16:00:13.000', '2019-02-26 16:00:13.000', 1380);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'BillingDashboard@VOSIP_ajax_datagrid', 'BillingDashboard.VOSIP_ajax_datagrid', 1, 'Sumera Saeed', NULL, '2019-02-26 16:00:13.000', '2019-02-26 16:00:13.000', 1379);



/* Above Done ON LIVE */

INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1382, 'VOSAccountIP.View', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1381, 'VOSAccountBalance.View', 1, 7);


INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2726, 'VOSAccountIP.ajax_datagrid', 'VOSAccountIPController.ajax_datagrid', 1, 'Sumera Saeed', NULL, '2019-03-05 12:01:58.000', '2019-03-05 12:01:58.000', 1382);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2725, 'VOSAccountIP.*', 'VOSAccountIPController.*', 1, 'Sumera Saeed', NULL, '2019-03-05 12:01:58.000', '2019-03-05 12:01:58.000', 1382);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2724, 'VOSAccountIP.index', 'VOSAccountIPController.index', 1, 'Sumera Saeed', NULL, '2019-03-05 12:01:58.000', '2019-03-05 12:01:58.000', 1382);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2723, 'VOSAccountBalance.ajax_datagrid', 'VOSAccountBalanceController.ajax_datagrid', 1, 'Sumera Saeed', NULL, '2019-03-05 12:01:58.000', '2019-03-05 12:01:58.000', 1381);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2722, 'VOSAccountBalance.*', 'VOSAccountBalanceController.*', 1, 'Sumera Saeed', NULL, '2019-03-05 12:01:58.000', '2019-03-05 12:01:58.000', 1381);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2721, 'VOSAccountBalance.index', 'VOSAccountBalanceController.index', 1, 'Sumera Saeed', NULL, '2019-03-05 12:01:58.000', '2019-03-05 12:01:58.000', 1381);


INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 14, 'Import VOS Customer Rate', 'getvoscustomerrate', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2019-02-18 13:45:49', 'RateManagementSystem');

INSERT INTO `tblCronJob` (`CompanyID`, `CronJobCommandID`, `Settings`, `Status`, `LastRunTime`, `NextRunTime`, `created_at`, `created_by`, `updated_at`, `updated_by`, `Active`, `JobTitle`, `DownloadActive`, `PID`, `EmailSendTime`, `CdrBehindEmailSendTime`, `CdrBehindDuration`, `ProcessID`, `MysqlPID`) VALUES (1, 605, '{"ThresholdTime":"","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["DAILY","SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":"31"}', 1, '2019-03-08 10:57:00', '2019-03-09 00:00:00', '2019-03-06 00:00:00', 'Sumera Saeed', '2019-03-08 10:57:56', NULL, 0, 'Import VOS Customer Rate', 0, '', NULL, NULL, NULL, '', '');

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 14, 'Import VOS Vendor Rate', 'getvosvendorrate', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2019-02-18 13:45:49', 'RateManagementSystem');

INSERT INTO `tblCronJob` (`CompanyID`, `CronJobCommandID`, `Settings`, `Status`, `LastRunTime`, `NextRunTime`, `created_at`, `created_by`, `updated_at`, `updated_by`, `Active`, `JobTitle`, `DownloadActive`, `PID`, `EmailSendTime`, `CdrBehindEmailSendTime`, `CdrBehindDuration`, `ProcessID`, `MysqlPID`) VALUES (1, 606, '{"ThresholdTime":"","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":"31"}', 1, NULL, '2019-03-09 00:00:00', '2019-03-08 00:00:00', 'Sumera Saeed', '2019-03-08 11:23:30', NULL, 0, 'Import VOS Vendor Rate', 0, '', NULL, NULL, NULL, NULL, NULL);



DROP TABLE IF EXISTS `tblVOSCustomerFeeRateGroup`;
CREATE TABLE IF NOT EXISTS `tblVOSCustomerFeeRateGroup` (
  `VOSCustomerFeeRateGroupID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `FeePrefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaCode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Fee` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Period` int(11) DEFAULT NULL,
  `Type` int(11) DEFAULT NULL,
  `IvrFee` int(11) DEFAULT NULL,
  `IvrPeriod` int(11) DEFAULT NULL,
  `FeeRateGroup` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VOSCustomerFeeRateGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



DROP TABLE IF EXISTS `tblVOSVendorFeeRateGroup`;
CREATE TABLE IF NOT EXISTS `tblVOSVendorFeeRateGroup` (
  `VOSVendorFeeRateGroupID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `FeePrefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaCode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Fee` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Period` int(11) DEFAULT NULL,
  `Type` int(11) DEFAULT NULL,
  `IvrFee` int(11) DEFAULT NULL,
  `IvrPeriod` int(11) DEFAULT NULL,
  `FeeRateGroup` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VOSVendorFeeRateGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




DROP PROCEDURE IF EXISTS `prc_VOSImportCustomerFeeRate`;
DELIMITER //
CREATE PROCEDURE `prc_VOSImportCustomerFeeRate`(
	IN `p_CompanyID` INT	


)
sp:BEGIN
	DECLARE v_AccountIds LONGTEXT;
	DECLARE v_TrunkIds LONGTEXT; 
	DECLARE v_TimezoneIds LONGTEXT;
	  
    
    SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));
             
	  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	  
	  
	  DROP TEMPORARY TABLE IF EXISTS tmp_customer_VOSData;
				CREATE TEMPORARY TABLE tmp_customer_VOSData (
					`VOSCustomerFeeRateGroupID` int(11),
					`FeePrefix` varchar(255) DEFAULT NULL,
					`AreaCode` varchar(50) DEFAULT NULL,
					`AreaName` varchar(255) DEFAULT NULL,
					`Fee` varchar(255) DEFAULT NULL,
					`Period` int(11) DEFAULT NULL,
					`Type` int(11) DEFAULT NULL,
					`FeeRateGroup` VARCHAR(255) DEFAULT NULL	,
					`TrunkID` INT(11),
					`CodeDeckId` INT(11),
					`AccountID` INT(11),
					INDEX IX_AreaCode (AreaCode),
					INDEX IX_FeeRateGroup (FeeRateGroup)
					
				);
				
				DROP TEMPORARY TABLE IF EXISTS tmp_customer_feerate_group;
				CREATE TEMPORARY TABLE tmp_customer_feerate_group (
					`VOSCustomerFeeRateGroupID` int(11),
					`FeePrefix` varchar(255) DEFAULT NULL,
					`AreaCode` varchar(50) DEFAULT NULL,
					`AreaName` varchar(255) DEFAULT NULL,
					`Fee` varchar(255) DEFAULT NULL,
					`Period` int(11) DEFAULT NULL,
					`Type` int(11) DEFAULT NULL,
					`FeeRateGroup` VARCHAR(255) DEFAULT NULL	,
					`TrunkID` INT(11),
					`CodeDeckId` INT(11),
					`RateID` INT(11),
					`AccountID` INT(11),
					INDEX IX_AreaCode (AreaCode),
					INDEX IX_FeeRateGroup (FeeRateGroup)
					
				);
				
					-- Load VOS Data in tempTable
					
					INSERT INTO tmp_customer_VOSData
						SELECT 
						cfg.VOSCustomerFeeRateGroupID,
						cfg.FeePrefix,
						cfg.AreaCode,
						cfg.AreaName,
						cast(cfg.Fee as decimal(18,6)) as Fee,
						cfg.Period,
						cfg.Type,
						cfg.FeeRateGroup,
						ct.TrunkID,
						ct.CodeDeckId,
						ct.AccountID
					FROM tblVOSCustomerFeeRateGroup cfg
					inner join tblCustomerTrunk ct 
						on cfg.FeeRateGroup = ct.Prefix 
				--	inner join tblRate r 
				--		on r.CompanyID = ct.CompanyID  
				--		and r.CodedeckID = ct.CodedeckID 
				--		and r.Code = cfg.AreaCode
					WHERE ct.CompanyID=p_CompanyID
					GROUP BY cfg.AreaCode,cfg.FeeRateGroup 
					ORDER BY cfg.VOSCustomerFeeRateGroupID
					;	
					
				-- Not Found AreaCode imported	
				INSERT INTO tblRate
				(
					CompanyID,
					CodeDeckId,
					Code,
					Description,
					updated_at,
					created_at,
					CreatedBy,
					Interval1,
					IntervalN
				)
				SELECT
				DISTINCT 
					p_CompanyID,
					tbl1.CodeDeckId,
					tbl1.AreaCode,
					tbl1.AreaName,
					NOW(),
					NOW(),
					'VOS-System',
					tbl1.Period,
					tbl1.Period
					
				FROM (
						SELECT 
							a.AreaCode,
							a.CodeDeckId,
							a.Period,
							a.AreaName 
						from tmp_customer_VOSData a 
						left join tblRate b 
						on b.CompanyID=p_CompanyID
						and b.Code=a.AreaCode 
						and b.CodeDeckId=a.CodeDeckId 
						WHERE 
							b.RateID is NULL
						group by a.CodeDeckId,a.AreaCode
					) tbl1 ;
					
					
				INSERT INTO tmp_customer_feerate_group
				SELECT 
						cfg.VOSCustomerFeeRateGroupID,
						cfg.FeePrefix,
						cfg.AreaCode,
						cfg.AreaName,
						cfg.Fee,
						cfg.Period,
						cfg.Type,
						cfg.FeeRateGroup,
						cfg.TrunkID,
						cfg.CodeDeckId,
						r.RateID,
						cfg.AccountID
				FROM tmp_customer_VOSData cfg
				inner join tblRate r 
						on r.CompanyID = p_CompanyID 
						and r.CodedeckID = cfg.CodedeckID 
						and r.Code = cfg.AreaCode;		
				
			
		--	SELECT * FROM tmp_customer_feerate_group;
			

			CALL prc_LoadVOSCustomerRates(p_CompanyID);
			
						
			-- SELECT * FROM tblCustomerRate a JOIN tmp_customer_rates_id b ON a.CustomerRateID=b.CustomerRateID JOIN tblRate c ON c.RateID=a.RateID;
		
					
			/*select *
			FROM 
			tmp_customer_feerate_group a 
			INNER join tblRate b 
				ON a.AreaCode=b.Code  
				AND b.CompanyID = p_CompanyID
				AND b.CodeDeckId = a.CodeDeckId
			join tblCustomerRate c ON c.RateID=b.RateID AND c.Rate=a.Fee AND c.RatePrefix=a.FeePrefix
			join tmp_customer_rates_id d ON d.CustomerRateID=c.CustomerRateID;*/
		
			--  Delete from vos table which rates and Code matches 
			DELETE a
			FROM 
			tmp_customer_feerate_group a 
			INNER join tblRate b 
				ON a.AreaCode=b.Code  
				AND b.CompanyID = p_CompanyID
				AND b.CodeDeckId = a.CodeDeckId
			join tblCustomerRate c ON c.RateID=b.RateID AND c.Rate=a.Fee AND c.RatePrefix=a.FeePrefix
			join tmp_customer_rates_id d ON d.CustomerRateID=c.CustomerRateID;
			
			-- Load TempTable
			CALL prc_LoadVOSCustomerRates(p_CompanyID);
			
			
			
			SELECT GROUP_CONCAT(distinct a.CustomerID) INTO v_AccountIds FROM tblCustomerRate a JOIN tmp_customer_rates_id b ON a.CustomerRateID=b.CustomerRateID; -- GROUP BY a.CustomerID;
			SELECT GROUP_CONCAT(distinct a.TrunkID) INTO v_TrunkIds FROM tblCustomerRate a JOIN tmp_customer_rates_id b ON a.CustomerRateID=b.CustomerRateID; -- GROUP BY a.TrunkID;
			SELECT GROUP_CONCAT(distinct a.TimezonesID) INTO v_TimezoneIds FROM tblCustomerRate a JOIN tmp_customer_rates_id b ON a.CustomerRateID=b.CustomerRateID; -- GROUP BY a.TimezonesID;	
			
					
			UPDATE tblCustomerRate a INNER JOIN tmp_customer_rates_id b ON a.CustomerRateID=b.CustomerRateID set a.EndDate=NOW();
			
			select count(*) As TotalArchivedRates from tblCustomerRate a INNER JOIN tmp_customer_rates_id b ON a.CustomerRateID=b.CustomerRateID;
		
		--	select v_AccountIds;
		--	select v_TrunkIds;
			
			IF v_AccountIds != '' AND v_TrunkIds != '' THEN
				
				CALL prc_ArchiveOldCustomerRate(v_AccountIds,v_TrunkIds,v_TimezoneIds,'System-VOS');
				
			END IF;
			
			--  Import Rates 
			--	select * from tmp_customer_feerate_group a INNER JOIN tblCustomerTrunk b on a.FeeRateGroup=b.Prefix;		
			-- select * from tmp_customer_feerate_group a INNER JOIN tblCustomerTrunk b on a.FeeRateGroup=b.Prefix INNER JOIN tblRate c on c.CodeDeckId=b.CodeDeckId;
			
			-- select * from tmp_customer_feerate_group;
			
			INSERT INTO tblCustomerRate
			(
				RateID,
				CustomerID,
				LastModifiedDate,
				LastModifiedBy,
				CreatedDate,
				CreatedBy,
				TrunkID,
				TimezonesID,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
				PreviousRate,
				Interval1,
				IntervalN,
				RoutinePlan,
				ConnectionFee,
				RatePrefix
			)
				SELECT 
					DISTINCT
					RateID,
					AccountID,
					CURDATE(),
					'VOS-System',
					NOW(),
					'VOS-System',
					TrunkID,
					1 as TimezonesID,
					Fee,
					Fee,
					CURDATE() as EffectiveDate,
					NULL,
					'0.000000',
					Period,
					Period,
					NULL,
					NULL,
					FeePrefix
					
				FROM 
				tmp_customer_feerate_group
				group by RateID,AccountID,TrunkID,TimezonesID,EffectiveDate,FeePrefix
				;
				
				select FOUND_ROWS() as TotalCustomerRatesImport;
				 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_LoadVOSCustomerRates`;
DELIMITER //
CREATE PROCEDURE `prc_LoadVOSCustomerRates`(
	IN `p_CompanyID` INT	


)
BEGIN

    SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));
            
	  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	  
				
			DROP TEMPORARY TABLE IF EXISTS tmp_customer_trunk;
			CREATE TEMPORARY TABLE tmp_customer_trunk (
				`CodeDeckId` int(11),
				`AccountID` int(11),
				`TrunkID` int(11),
				`Prefix` VARCHAR(255)						
			);
				
			DROP TEMPORARY TABLE IF EXISTS tmp_customer_rates_id;
			CREATE TEMPORARY TABLE tmp_customer_rates_id (
				`CustomerRateID` int(11)			
			);
				
			
			/*INSERT INTO tmp_customer_trunk
			 SELECT a.CodeDeckId,a.AccountID,a.TrunkID,a.Prefix 
			FROM tblCustomerTrunk a 
			JOIN tmp_customer_feerate_group b 
			ON a.Prefix=b.FeeRateGroup 
			GROUP BY a.CustomerTrunkID;*/
			
			INSERT INTO tmp_customer_trunk
			SELECT CodeDeckId,AccountID,TrunkID,FeeRateGroup
			FROM
			tmp_customer_feerate_group group by TrunkID;
			
			INSERT INTO tmp_customer_rates_id
				SELECT a.CustomerRateID 
				FROM tblCustomerRate a 
				JOIN tmp_customer_trunk b 
					ON a.CustomerID=b.AccountID 
					AND a.TrunkID=b.TrunkID
					;
			
			
		--	SELECT * FROM tblCustomerRate a JOIN tmp_customer_rates_id b ON a.CustomerRateID=b.CustomerRateID;
			
			 			 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;






ALTER TABLE `tblCustomerRate`
	ADD COLUMN `RatePrefix` VARCHAR(255) NULL DEFAULT NULL AFTER `ConnectionFee`;

ALTER TABLE `tblVendorRate`
	ADD COLUMN `RatePrefix` VARCHAR(255) NULL DEFAULT NULL AFTER `MinimumCost`;	
	


DROP PROCEDURE IF EXISTS `prc_VOSImportVendorFeeRate`;
DELIMITER //
CREATE PROCEDURE `prc_VOSImportVendorFeeRate`(
	IN `p_CompanyID` INT	

)
sp:BEGIN
	DECLARE v_AccountIds LONGTEXT;
	DECLARE v_TrunkIds LONGTEXT; 
	DECLARE v_TimezoneIds LONGTEXT;
	  
    SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));
    
             
	  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	  
	  DROP TEMPORARY TABLE IF EXISTS tmp_vendor_VOSData;
				CREATE TEMPORARY TABLE tmp_vendor_VOSData (
					`VOSVendorFeeRateGroupID` int(11),
					`FeePrefix` varchar(255) DEFAULT NULL,
					`AreaCode` varchar(50) DEFAULT NULL,
					`AreaName` varchar(255) DEFAULT NULL,
					`Fee` varchar(255) DEFAULT NULL,
					`Period` int(11) DEFAULT NULL,
					`Type` int(11) DEFAULT NULL,
					`FeeRateGroup` VARCHAR(255) DEFAULT NULL	,
					`TrunkID` INT(11),
					`CodeDeckId` INT(11),
					`AccountID` INT(11),
					INDEX IX_AreaCode (AreaCode),
					INDEX IX_FeeRateGroup (FeeRateGroup)
					
				);
				
				DROP TEMPORARY TABLE IF EXISTS tmp_vendor_feerate_group;
				CREATE TEMPORARY TABLE tmp_vendor_feerate_group (
					`VOSVendorFeeRateGroupID` int(11),
					`FeePrefix` varchar(255) DEFAULT NULL,
					`AreaCode` varchar(50) DEFAULT NULL,
					`AreaName` varchar(255) DEFAULT NULL,
					`Fee` varchar(255) DEFAULT NULL,
					`Period` int(11) DEFAULT NULL,
					`Type` int(11) DEFAULT NULL,
					`FeeRateGroup` VARCHAR(255) DEFAULT NULL	,
					`TrunkID` INT(11),
					`CodeDeckId` INT(11),
					`RateID` INT(11),
					`AccountID` INT(11),
					INDEX IX_AreaCode (AreaCode),
					INDEX IX_FeeRateGroup (FeeRateGroup)
					
				);
			
					-- Load VOS Data in tempTable
					INSERT INTO tmp_vendor_VOSData
						SELECT 
						vfg.VOSVendorFeeRateGroupID,
						vfg.FeePrefix,
						vfg.AreaCode,
						vfg.AreaName,
						cast(vfg.Fee as decimal(18,6)) as Fee,
						vfg.Period,
						vfg.Type,
						vfg.FeeRateGroup,
						ct.TrunkID,
						ct.CodeDeckId,
						ct.AccountID
					FROM tblVOSVendorFeeRateGroup vfg
					inner join tblVendorTrunk ct 
						on vfg.FeeRateGroup = ct.Prefix 
				--	inner join tblRate r 
				--		on r.CompanyID = ct.CompanyID  
				--		and r.CodedeckID = ct.CodedeckID 
				--		and r.Code = cfg.AreaCode
					WHERE ct.CompanyID=p_CompanyID
					GROUP BY vfg.AreaCode,vfg.FeeRateGroup 
					ORDER BY vfg.VOSVendorFeeRateGroupID
					;
				
				
				-- Not Found AreaCode imported	
				INSERT INTO tblRate
				(
					CompanyID,
					CodeDeckId,
					Code,
					Description,
					updated_at,
					created_at,
					CreatedBy,
					Interval1,
					IntervalN
				)
				SELECT
				DISTINCT 
					p_CompanyID,
					tbl1.CodeDeckId,
					tbl1.AreaCode,
					tbl1.AreaName,
					NOW(),
					NOW(),
					'VOS-System',
					tbl1.Period,
					tbl1.Period
					
				FROM (
						SELECT 
							a.AreaCode,
							a.CodeDeckId,
							a.Period,
							a.AreaName 
						from tmp_vendor_VOSData a 
						left join tblRate b 
						on b.CompanyID=p_CompanyID
						and b.Code=a.AreaCode 
						and b.CodeDeckId=a.CodeDeckId 
						WHERE 
							b.RateID is NULL
							group by a.CodeDeckId,a.AreaCode
					) tbl1;
				
				
					INSERT INTO tmp_vendor_feerate_group
						SELECT 
						cfg.VOSVendorFeeRateGroupID,
						cfg.FeePrefix,
						cfg.AreaCode,
						cfg.AreaName,
						cast(cfg.Fee as decimal(18,6)) as Fee,
						cfg.Period,
						cfg.Type,
						cfg.FeeRateGroup,
						ct.TrunkID,
						ct.CodeDeckId,
						r.RateID,
						ct.AccountID
					FROM tblVOSVendorFeeRateGroup cfg
					inner join tblVendorTrunk ct 
						on cfg.FeeRateGroup = ct.Prefix 
					inner join tblRate r 
						on r.CompanyID = ct.CompanyID  
						and r.CodedeckID = ct.CodeDeckId 
						and r.Code = cfg.AreaCode
					WHERE ct.CompanyID=p_CompanyID
				--	GROUP BY cfg.AreaCode 
					ORDER BY cfg.VOSVendorFeeRateGroupID;	
				
					
		--	SELECT * FROM tmp_vendor_feerate_group;
		
			
			CALL prc_LoadVOSVendorRates(p_CompanyID);
			
			 -- SELECT * FROM tblVendorRate a JOIN tmp_vendor_rates_id b ON a.VendorRateID=b.VendorRateID JOIN tblRate c ON c.RateID=a.RateID;
					
			--  Delete from vos table which rates and Code matches 
			DELETE a
			FROM 
			tmp_vendor_feerate_group a 
			INNER join tblRate b 
				ON a.AreaCode=b.Code  
				AND b.CompanyID = p_CompanyID
				AND b.CodeDeckId = a.CodeDeckId
			join tblVendorRate c ON c.RateID=b.RateID AND c.Rate=a.Fee AND c.RatePrefix=a.FeePrefix
			join tmp_vendor_rates_id d ON d.VendorRateID=c.VendorRateID;
			
			-- Load TempTable 
				
			
			CALL prc_LoadVOSVendorRates(p_CompanyID);
			
						
			SELECT GROUP_CONCAT( distinct a.AccountId) INTO v_AccountIds FROM tblVendorRate a JOIN tmp_vendor_rates_id b ON a.VendorRateID=b.VendorRateID; -- GROUP BY a.AccountId;
			SELECT GROUP_CONCAT(distinct a.TrunkID) INTO v_TrunkIds FROM tblVendorRate a JOIN tmp_vendor_rates_id b ON a.VendorRateID=b.VendorRateID; -- GROUP BY a.TrunkID;
			SELECT GROUP_CONCAT(distinct a.TimezonesID) INTO v_TimezoneIds FROM tblVendorRate a JOIN tmp_vendor_rates_id b ON a.VendorRateID=b.VendorRateID; -- GROUP BY a.TimezonesID;	
			
			
			UPDATE tblVendorRate a INNER JOIN tmp_vendor_rates_id b ON a.VendorRateID=b.VendorRateID set a.EndDate=NOW();
			
			select count(*) As TotalArchivedRates from tblVendorRate a INNER JOIN tmp_vendor_rates_id b ON a.VendorRateID=b.VendorRateID;
		
		--	select v_AccountIds;
		--	select v_TrunkIds;
		
			
			IF v_AccountIds != '' AND v_TrunkIds != '' THEN
				
				CALL prc_ArchiveOldVendorRate(v_AccountIds,v_TrunkIds,v_TimezoneIds,'System-VOS');
				
			END IF;
			
			--  Import Rates 
			--	select * from tmp_vendor_feerate_group a INNER JOIN tblCustomerTrunk b on a.FeeRateGroup=b.Prefix;		
			-- select * from tmp_vendor_feerate_group a INNER JOIN tblCustomerTrunk b on a.FeeRateGroup=b.Prefix INNER JOIN tblRate c on c.CodeDeckId=b.CodeDeckId;
			
			INSERT INTO tblVendorRate
			(
				AccountId,
				TrunkID,
				TimezonesID,
				RateId,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
				updated_at,
				created_at,
				created_by,
				updated_by,
				Interval1,
				IntervalN,
				ConnectionFee,
				MinimumCost,
				RatePrefix
			)
				SELECT 
					AccountID,
					TrunkID,
					1 as TimezonesID,
					RateID,
					Fee,
					Fee,
					NOW() as EffectiveDate,
					NULL,
					NOW(),
					NOW(),
					'VOS-System',
					'VOS-System',
					Period,
					NULL,
					NULL,
					NULL,
					FeePrefix
					
				FROM 
				tmp_vendor_feerate_group
				group by AccountID,TrunkID,RateID,EffectiveDate,TimezonesID,FeePrefix
				;
				
				select FOUND_ROWS() as TotalVendorRatesImport;
				 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_LoadVOSVendorRates`;
DELIMITER //
CREATE PROCEDURE `prc_LoadVOSVendorRates`(
	IN `p_CompanyID` INT	

)
BEGIN

    SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));
            
	  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	  
				
			DROP TEMPORARY TABLE IF EXISTS tmp_vendor_trunk;
			CREATE TEMPORARY TABLE tmp_vendor_trunk (
				`CodeDeckId` int(11),
				`AccountID` int(11),
				`TrunkID` int(11),
				`Prefix` VARCHAR(255)						
			);
				
			DROP TEMPORARY TABLE IF EXISTS tmp_vendor_rates_id;
			CREATE TEMPORARY TABLE tmp_vendor_rates_id (
				`VendorRateID` int(11)			
			);
				
						
			INSERT INTO tmp_vendor_trunk
			SELECT CodeDeckId,AccountID,TrunkID,FeeRateGroup
			FROM
			tmp_vendor_feerate_group group by TrunkID;
			
			
			INSERT INTO tmp_vendor_rates_id
			SELECT a.VendorRateID 
			FROM tblVendorRate a 
			JOIN tmp_vendor_trunk b 
				ON a.AccountId=b.AccountID 
				AND a.TrunkID=b.TrunkID
					;
			
			
		--	SELECT * FROM tblVendorRate a JOIN tmp_vendor_rates_id b ON a.VendorRateID=b.VendorRateID;
			
			 			 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;




DROP TABLE IF EXISTS `tblVOSVendorFeeRateGroup`;
CREATE TABLE IF NOT EXISTS `tblVOSVendorFeeRateGroup` (
  `VOSVendorFeeRateGroupID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `FeePrefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaCode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Fee` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Period` int(11) DEFAULT NULL,
  `Type` int(11) DEFAULT NULL,
  `IvrFee` int(11) DEFAULT NULL,
  `IvrPeriod` int(11) DEFAULT NULL,
  `FeeRateGroup` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VOSVendorFeeRateGroupID`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	

	ALTER TABLE `tblCustomerRate`
	DROP INDEX `RatePrefix`,
	DROP INDEX `IXUnique_RateId_CustomerId_TrunkId_TimezonesID_EffectiveDate`,
	ADD UNIQUE INDEX `IXUnique_RateId_CustomerId_TrunkId_TimezonesID_EffectiveDate` (`RateID`, `CustomerID`, `TrunkID`, `TimezonesID`, `EffectiveDate`, `RatePrefix`);

	
	ALTER TABLE `tblVendorRate`
	DROP INDEX `IXUnique_AccountId_TrunkId_TimezonesID_RateId_EffectiveDate`,
	ADD UNIQUE INDEX `IXUnique_AccountId_TrunkId_TimezonesID_RateId_EffectiveDate` (`AccountId`, `TrunkID`, `TimezonesID`, `RateId`, `EffectiveDate`, `RatePrefix`);

	
/* Tickets Changes */
	
use Ratemanagement3;	

DELIMITER //
CREATE PROCEDURE `prc_GetSystemTicket`(
	IN `p_CompanyID` int,
	IN `p_Search` VARCHAR(100),
	IN `P_Status` VARCHAR(100),
	IN `P_Priority` VARCHAR(100),
	IN `P_Group` VARCHAR(100),
	IN `P_Agent` VARCHAR(100),
	IN `P_DueBy` VARCHAR(50),
	IN `P_CurrentDate` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_is_StartDate` INT,
	IN `p_is_EndDate` INT,
	IN `p_Requester` VARCHAR(255),
	IN `p_LastReply` INT

)
BEGIN

	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_Groups_ varchar(200);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT
			T.TicketID,
			T.Subject,


  CASE
 	 WHEN T.AccountID>0   THEN     (SELECT CONCAT(IFNULL(TAA.AccountName,''),' (',T.Requester,')') FROM tblAccount TAA WHERE TAA.AccountID = T.AccountID  )
  	 WHEN T.ContactID>0   THEN     (select CONCAT(IFNULL(TCCC.FirstName,''),' ',IFNULL(TCCC.LastName,''),' (',T.Requester,')') FROM tblContact TCCC WHERE TCCC.ContactID = T.ContactID)
     WHEN T.UserID>0      THEN  	 (select CONCAT(IFNULL(TUU.FirstName,''),' ',IFNULL(TUU.LastName,''),' (',T.Requester,')') FROM tblUser TUU WHERE TUU.UserID = T.UserID )
    ELSE CONCAT(T.RequesterName,' (',T.Requester,')')
  END AS Requester,
			T.Requester as RequesterEmail,
			TFV.FieldValueAgent  as TicketStatus,
			TP.PriorityValue,
			concat(TU.FirstName,' ',TU.LastName) as Agent,
			TG.GroupName,
			T.created_at,

			(select tc.created_at from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =1 and tc.EmailParent>0 order by tc.AccountEmailLogID desc limit 1) as CustomerResponse,
		    (select tc.created_at from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =0  order by tc.AccountEmailLogID desc limit 1) as AgentResponse,
			(select TAC.AccountID from tblAccount TAC where 	TAC.Email = T.Requester or TAC.BillingEmail =T.Requester limit 1) as ACCOUNTID,
			T.`Read` as `Read`,
			T.TicketSlaID,
			T.DueDate,
			T.updated_at,
         T.Status,
         T.AgentRepliedDate,
         T.CustomerRepliedDate
		FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`

		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_Requester = '' OR find_in_set(T.`Requester`,p_Requester))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND (
					P_DueBy = '' OR
					(  P_DueBy != '' AND
						(
							   (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate))
							OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
							OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
							OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate )
						)
					)
				)
				
			AND 
			(
				p_LastReply = 0 OR
				
				CASE 
					WHEN T.AgentRepliedDate is not null AND T.CustomerRepliedDate is not null				   
				THEN
				
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate > T.CustomerRepliedDate THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate < T.CustomerRepliedDate THEN 
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END
				
			 WHEN T.AgentRepliedDate is null OR T.CustomerRepliedDate is null					   
			  THEN	
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.CustomerRepliedDate is null AND (T.AgentRepliedDate is not null) THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate is null AND (T.CustomerRepliedDate is not null) THEN  
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END	
			
				END 
				
			)	

			ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectASC') THEN T.Subject
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectDESC') THEN T.Subject
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN TicketStatus
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN TicketStatus
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AgentASC') THEN TU.FirstName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AgentDESC') THEN TU.FirstName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN T.created_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN T.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN T.updated_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN T.updated_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RequesterASC') THEN T.Requester
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RequesterDESC') THEN T.Requester
			END DESC
			LIMIT
				p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
				FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_Requester = '' OR find_in_set(T.`Requester`,p_Requester))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate))
			
			AND 
			(
				p_LastReply = 0 OR
				
				CASE 
					WHEN T.AgentRepliedDate is not null AND T.CustomerRepliedDate is not null				   
				THEN
				
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate > T.CustomerRepliedDate THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate < T.CustomerRepliedDate THEN 
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END
				
			 WHEN T.AgentRepliedDate is null OR T.CustomerRepliedDate is null					   
			  THEN	
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.CustomerRepliedDate is null AND (T.AgentRepliedDate is not null) THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate is null AND (T.CustomerRepliedDate is not null) THEN  
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END	
			
				END 
				
			);

	SELECT
			DISTINCT(TG.GroupID),
			TG.GroupName
	FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_Requester = '' OR find_in_set(T.`Requester`,p_Requester))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ) )
			
			AND 
			(
				p_LastReply = 0 OR
				
				CASE 
					WHEN T.AgentRepliedDate is not null AND T.CustomerRepliedDate is not null				   
				THEN
				
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate > T.CustomerRepliedDate THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate < T.CustomerRepliedDate THEN 
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END
				
			 WHEN T.AgentRepliedDate is null OR T.CustomerRepliedDate is null					   
			  THEN	
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.CustomerRepliedDate is null AND (T.AgentRepliedDate is not null) THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate is null AND (T.CustomerRepliedDate is not null) THEN  
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END	
			
				END
			);

	END IF;
	IF p_isExport = 1
	THEN
	SELECT
			T.TicketID,
			T.Subject,
			T.Requester,
			T.RequesterCC as 'CC',
			TFV.FieldValueAgent  as 'Status',
			TP.PriorityValue as 'Priority',
			concat(TU.FirstName,' ',TU.LastName) as Agent,
			T.created_at as 'Date Created',
			TG.GroupName as 'Group'
		FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		LEFT JOIN tblContact TCC
			ON TCC.Email = T.`Requester`

		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_Requester = '' OR find_in_set(T.`Requester`,p_Requester))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ))
			
			AND 
			(
				p_LastReply = 0 OR
				
				CASE 
					WHEN T.AgentRepliedDate is not null AND T.CustomerRepliedDate is not null				   
				THEN
				
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate > T.CustomerRepliedDate THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate < T.CustomerRepliedDate THEN 
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END
				
			 WHEN T.AgentRepliedDate is null OR T.CustomerRepliedDate is null					   
			  THEN	
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.CustomerRepliedDate is null AND (T.AgentRepliedDate is not null) THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate is null AND (T.CustomerRepliedDate is not null) THEN  
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END	
			
				END	
			);
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;





	