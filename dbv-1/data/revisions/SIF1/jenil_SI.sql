use speakintelligentRM;
CREATE TABLE `tblDIDCategory` (
	`DIDCategoryID` INT(11) NOT NULL AUTO_INCREMENT,
	`CategoryName` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`CompanyID` INT(11) NOT NULL,
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`DIDCategoryID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=7
;


DROP TABLE IF EXISTS `tblRateType`;
CREATE TABLE IF NOT EXISTS `tblRateType` (
  `RateTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `Slug` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Active` tinyint(4) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateTypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table speakintelligentRM.tblRateType: ~2 rows (approximately)
/*!40000 ALTER TABLE `tblRateType` DISABLE KEYS */;
INSERT INTO `tblRateType` (`RateTypeID`, `CompanyID`, `Slug`, `Title`, `Description`, `Active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
	(1, 1, 'voicecall', 'Voice Call', NULL, 1, '2018-12-27 15:14:39', '2018-12-27 15:14:44', 'jenil', NULL),
	(2, 1, 'did', 'DID', NULL, 1, '2018-12-27 15:14:39', '2018-12-27 15:14:44', 'jenil', NULL);




DROP PROCEDURE IF EXISTS `prc_getDIDCategory`;
DELIMITER //
CREATE PROCEDURE `prc_getDIDCategory`(
	IN `p_CompanyID` INT,
	IN `p_CategoryName` VARCHAR(50),
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
			DIDCategoryID,
			CategoryName,
			updated_at
            from tblDIDCategory
            where CompanyID = p_CompanyID
			AND(p_CategoryName ='' OR CategoryName like Concat('%',p_CategoryName,'%'))
           
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CategoryNameDESC') THEN CategoryName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CategoryNameASC') THEN CategoryName
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(DIDCategoryID) AS totalcount
            from tblDIDCategory
            where CompanyID = p_CompanyID
			AND(p_CategoryName ='' OR CategoryName like Concat('%',p_CategoryName,'%'));

	ELSE

			SELECT
			DIDCategoryID,
			CategoryName,
			updated_at
            from tblDIDCategory
            where CompanyID = p_CompanyID
			AND(p_CategoryName ='' OR CategoryName like Concat('%',p_CategoryName,'%'));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


/* Permissions */

INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1375, 'DIDCategory.Add', 1, 8);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1374, 'DIDCategory.Edit', 1, 8);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1373, 'DIDCategory.Delete', 1, 8);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1372, 'DIDCategory.View', 1, 8);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1371, 'DIDCategory.All', 1, 8);

INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2663, 'DIDCategory.index', 'DIDCategoryController.index', 1, 'System', NULL, '2018-12-04 13:56:00.000', '2018-12-04 13:56:00.000', 1372);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2662, 'DIDCategory.*', 'DIDCategoryController.*', 1, 'System', NULL, '2018-12-04 13:56:00.000', '2018-12-04 13:56:00.000', 1371);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2661, 'DIDCategory.ajax_datagrid', 'DIDCategoryController.ajax_datagrid', 1, 'System', NULL, '2018-12-04 13:56:00.000', '2018-12-04 13:56:00.000', 1372);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2660, 'DIDCategory.create', 'DIDCategoryController.create', 1, 'System', NULL, '2018-12-04 13:56:00.000', '2018-12-04 13:56:00.000', 1375);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2659, 'DIDCategory.update', 'DIDCategoryController.update', 1, 'System', NULL, '2018-12-04 13:56:00.000', '2018-12-04 13:56:00.000', 1374);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2658, 'DIDCategory.delete', 'DIDCategoryController.delete', 1, 'System', NULL, '2018-12-04 13:56:00.000', '2018-12-04 13:56:00.000', 1373);



/* For Vendor Rate Changes - SI1-52 */

DROP TABLE IF EXISTS `tblVendorConnection`;
CREATE TABLE IF NOT EXISTS `tblVendorConnection` (
  `VendorConnectionID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountId` int(11) NOT NULL,
  `ConnectionType` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DIDCategoryID` int(11) DEFAULT NULL,
  `Active` tinyint(1) DEFAULT '0',
  `Tariff` int(11) DEFAULT NULL,
  `TrunkID` int(11) DEFAULT NULL,
  `CLIRule` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLDRule` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CallPrefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IP` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Port` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Password` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PrefixCDR` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`VendorConnectionID`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Dumping structure for procedure speakintelligentRM.prc_getVendorConnection
DROP PROCEDURE IF EXISTS `prc_getVendorConnection`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorConnection`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_IP` VARCHAR(255),
	IN `p_ConnectionType` INT,
	IN `p_DIDCategoryID` INT,
	IN `p_Name` VARCHAR(255),
	IN `p_Active` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT

)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	
	IF p_isExport = 0
	THEN
		
 		SELECT
			vc.VendorConnectionID,
			vc.Name,
			rt.Title as RateTypeTitle,
			vc.IP,
			vc.Active,
			t.Trunk as TrunkName,
			c.CategoryName as CategoryName,
			vc.created_at,
			vc.DIDCategoryID,
			vc.RateTableID,
			vc.TrunkID,
			vc.CLIRule,
			vc.CLDRule,
			vc.CallPrefix,
			vc.Port,
			vc.Username,
			vc.PrefixCDR,
			vc.SipHeader,
			vc.AuthenticationMode,
			vc.RateTypeID
			
		FROM
			tblVendorConnection vc
		LEFT JOIN tblDIDCategory c
			ON c.DIDCategoryID=vc.DIDCategoryID
		LEFT JOIN tblTrunk t
			ON t.TrunkID=vc.TrunkID 
		LEFT JOIN tblRateType rt
			ON rt.RateTypeID=vc.RateTypeID		
		WHERE vc.CompanyID = p_CompanyID
		AND vc.AccountId=p_AccountID
		AND(p_TrunkID = 0 OR vc.TrunkID = p_TrunkID)
		AND(p_IP = '' OR vc.IP like Concat(p_IP,'%'))
		AND(p_Name = '' OR vc.Name like Concat(p_Name,'%'))
		AND(p_ConnectionType = -1 OR vc.RateTypeID = p_ConnectionType)
		AND(p_DIDCategoryID = 0 OR vc.DIDCategoryID = p_DIDCategoryID)
		AND(p_Active=-1 OR vc.Active = p_Active)
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN vc.Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN vc.Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN vc.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN vc.created_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionTypeDESC') THEN vc.RateTypeID
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionTypeASC') THEN vc.RateTypeID
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IPDESC') THEN vc.IP
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IPASC') THEN vc.IP
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveDESC') THEN vc.Active
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActiveASC') THEN vc.Active
			END ASC
		LIMIT
			p_RowspPage
		OFFSET
			v_OffSet_;

		SELECT
			COUNT(vc.VendorConnectionID) AS totalcount
		FROM
			tblVendorConnection vc
		LEFT JOIN tblDIDCategory c
			ON c.DIDCategoryID=vc.DIDCategoryID
		LEFT JOIN tblTrunk t
			ON t.TrunkID=vc.TrunkID 
		LEFT JOIN tblRateType rt
			ON rt.RateTypeID=vc.RateTypeID		
		WHERE vc.CompanyID = p_CompanyID
		AND vc.AccountId=p_AccountID
		AND(p_TrunkID = 0 OR vc.TrunkID = p_TrunkID)
		AND(p_IP = '' OR vc.IP like Concat(p_IP,'%'))
		AND(p_Name = '' OR vc.Name like Concat(p_Name,'%'))
		AND(p_ConnectionType = -1 OR vc.RateTypeID = p_ConnectionType)
		AND(p_DIDCategoryID = 0 OR vc.DIDCategoryID = p_DIDCategoryID)
		AND(p_Active=-1 OR vc.Active = p_Active);

	END IF;

   IF p_isExport = 1
	THEN
		SELECT
			vc.VendorConnectionID,
			vc.Name,
			rt.Title as RateTypeTitle,
			vc.IP,
			vc.Active,
			t.Trunk as TrunkName,
			c.CategoryName as CategoryName,
			vc.created_at
		
		FROM
			tblVendorConnection vc
		LEFT JOIN tblDIDCategory c
			ON c.DIDCategoryID=vc.DIDCategoryID
		LEFT JOIN tblTrunk t
			ON t.TrunkID=vc.TrunkID 
		LEFT JOIN tblRateType rt
			ON rt.RateTypeID=vc.RateTypeID		
		WHERE vc.CompanyID = p_CompanyID
		AND vc.AccountId=p_AccountID
		AND(p_TrunkID = 0 OR vc.TrunkID = p_TrunkID)
		AND(p_IP = '' OR vc.IP like Concat(p_IP,'%'))
		AND(p_Name = '' OR vc.Name like Concat(p_Name,'%'))
		AND(p_ConnectionType = -1 OR vc.RateTypeID = p_ConnectionType)
		AND(p_DIDCategoryID = 0 OR vc.DIDCategoryID = p_DIDCategoryID)
		AND(p_Active=-1 OR vc.Active = p_Active);
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_VendorConnectionUpdateBySelectedConnectionId`;
DELIMITER //
CREATE PROCEDURE `prc_VendorConnectionUpdateBySelectedConnectionId`(
	IN `p_CompanyId` INT,
	IN `p_AccountId` INT,
	IN `p_ConnectionID` TEXT,
	IN `p_Active` INT,
	IN `p_TrunkID` INT(11),
	IN `p_IP` VARCHAR(255),
	IN `p_ConnectionType` VARCHAR(100),
	IN `p_Name` VARCHAR(255),
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_action` INT,
	IN `p_isDelete` INT
)
BEGIN


	DECLARE v_ConnectionIDs_ TEXT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	
	IF p_action = 0
	THEN

		IF p_isDelete=1 THEN
			/* Delete */
			
			DELETE 
			FROM 
				tblVendorConnection
			WHERE 
				FIND_IN_SET(VendorConnectionID,p_ConnectionID);
			
		ELSE
		
			/* Update */
			UPDATE 
				tblVendorConnection
			SET Active=p_Active,updated_by=p_ModifiedBy
			WHERE 
				FIND_IN_SET(VendorConnectionID,p_ConnectionID);
		
		END IF;
				
	END IF;


	IF p_action = 1
	THEN
		
		SELECT 
			GROUP_CONCAT(VendorConnectionID) INTO v_ConnectionIDs_
		FROM
			tblVendorConnection
		WHERE 
			CompanyID = p_CompanyID
			AND AccountId=p_AccountID
			AND(p_TrunkID = 0 OR TrunkID = p_TrunkID)
			AND(p_IP = '' OR IP like Concat(p_IP,'%'))
			AND(p_ConnectionType = '' OR ConnectionType like p_ConnectionType);
		
		select v_ConnectionIDs_;
		
		IF p_isDelete=1 THEN
		
			DELETE
			FROM 
				tblVendorConnection
			WHERE 
				FIND_IN_SET(VendorConnectionID,v_ConnectionIDs_);
		ELSE
						
			UPDATE 
				tblVendorConnection
			SET Active=p_Active,updated_by=p_ModifiedBy
			WHERE 
				FIND_IN_SET(VendorConnectionID,v_ConnectionIDs_);
		
		END IF;
		
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2702, 'Connection.bulk_update_connection', 'ConnectionController.bulk_update_connection', 1, 'Sumera Khan', NULL, '2018-12-13 11:08:30.000', '2018-12-13 11:08:30.000', 1376);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2701, 'Connection.updatestatus', 'ConnectionController.updatestatus', 1, 'Sumera Khan', NULL, '2018-12-13 11:08:30.000', '2018-12-13 11:08:30.000', 1376);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2700, 'Connection.delete', 'ConnectionController.delete', 1, 'Sumera Khan', NULL, '2018-12-13 11:08:30.000', '2018-12-13 11:08:30.000', 1376);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2699, 'Connection.update', 'ConnectionController.update', 1, 'Sumera Khan', NULL, '2018-12-13 11:08:30.000', '2018-12-13 11:08:30.000', 1376);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2698, 'Connection.create', 'ConnectionController.create', 1, 'Sumera Khan', NULL, '2018-12-13 11:08:30.000', '2018-12-13 11:08:30.000', 1376);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2697, 'Connection.*', 'ConnectionController.*', 1, 'Sumera Khan', NULL, '2018-12-13 11:08:30.000', '2018-12-13 11:08:30.000', 1376);
INSERT INTO `tblResource` (`ResourceID`, `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES (2696, 'Connection.index', 'ConnectionController.index', 1, 'Sumera Khan', NULL, '2018-12-13 11:08:30.000', '2018-12-13 11:08:30.000', 1376);


INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1376, 'VendorRates.Connection', 1, 3);

ALTER TABLE `tblVendorConnection`
	CHANGE COLUMN `Tariff` `RateTableID` INT(11) NULL DEFAULT NULL AFTER `Active`;

/*  Balance Flow APIs - Stage 1 - SI1-62 */

ALTER TABLE `tblAccount`
	ADD COLUMN `CustomerRef` VARCHAR(50) NULL DEFAULT NULL AFTER `DisplayRates`;
	

DROP TABLE IF EXISTS `tblAccountPaymentAutomation`;
CREATE TABLE IF NOT EXISTS `tblAccountPaymentAutomation` (
  `AccountPaymentAutomationID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `AutoTopup` tinyint(1) DEFAULT '0',
  `MinThreshold` int(11) DEFAULT NULL,
  `TopupAmount` decimal(18,2) DEFAULT NULL,
  `AutoOutpayment` tinyint(1) DEFAULT '0',
  `OutPaymentThreshold` int(11) DEFAULT NULL,
  `OutPaymentAmount` decimal(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountPaymentAutomationID`),
  UNIQUE KEY `AccountID` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	
ALTER TABLE `tblPayment`
	ADD COLUMN `IsOutPayment` TINYINT(1) NULL DEFAULT '0' AFTER `UsageEndDate`;
	
/* Above done on staging */

DROP PROCEDURE IF EXISTS `prc_getTransactionHistory`;
DELIMITER //
CREATE PROCEDURE `prc_getTransactionHistory`(
	IN `p_CompanyId` INT,
	IN `p_AccountId` INT,
	IN `p_paymentStartDate` DATE,
	IN `p_paymentEndDate` DATE

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SELECT
			tblPayment.PaymentID,
			tblPayment.AccountID,
			tblPayment.Amount AS Amount,
			tblPayment.PaymentType,
			tblPayment.CurrencyID,
			tblPayment.PaymentDate,
			-- CASE WHEN p_RecallOnOff = -1 AND tblPayment.Recall=1  THEN 'Recalled' ELSE tblPayment.Status END as `Status`,
			tblPayment.CreatedBy,
			tblPayment.PaymentProof,
			tblPayment.InvoiceNo,
			tblPayment.PaymentMethod,
			tblPayment.Notes,
			tblPayment.Recall,
			tblPayment.RecallReasoan,
			tblPayment.RecallBy
		FROM tblPayment
		 WHERE tblPayment.CompanyID = p_CompanyId
			 AND(p_AccountId = 0 OR tblPayment.AccountID = p_AccountId)
			  AND (p_paymentStartDate ='0000-00-00' OR ( p_paymentStartDate != '0000-00-00' AND tblPayment.PaymentDate >= p_paymentStartDate))
			  AND (p_paymentEndDate ='0000-00-00' OR ( p_paymentEndDate != '0000-00-00' AND tblPayment.PaymentDate <= p_paymentEndDate));
		
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


 /* Balance Flow APIs - Stage 2 - SI1-63 */

USE speakIntelligentRoutingEngine;

DROP TABLE IF EXISTS `tblActiveCall`;
CREATE TABLE IF NOT EXISTS `tblActiveCall` (
  `ActiveCallID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `CompanyId` int(11) NOT NULL,
  `UUID` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ConnectTime` datetime DEFAULT NULL,
  `DisconnectTime` datetime DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  `CLI` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLD` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLIPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLDPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cost` decimal(18,6) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  `CallType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CallRecordingStartTime` datetime DEFAULT NULL,
  `CallRecording` tinyint(4) DEFAULT '0',
  `PackageSubscriptionID` int(11) DEFAULT NULL,
  `RecodingCost` decimal(18,6) DEFAULT NULL,
  `CallRecordingDuration` int(11) DEFAULT NULL,
  `CallRecordingEndTime` datetime DEFAULT NULL,
  `OutpaymentCost` decimal(18,6) DEFAULT NULL,
  `Surcharges` decimal(18,6) DEFAULT NULL,
  `Chargeback` decimal(18,6) DEFAULT NULL,
  `CollectionAmount` decimal(18,6) DEFAULT NULL,
  `CollectionPercent` decimal(18,6) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ActiveCallID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




USE speakintelligentCDR;

ALTER TABLE tblUsageDetails
  ADD COLUMN `CLIPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD COLUMN `PackageSubscriptionID` int(11),
  ADD COLUMN `RecodingCost` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CallRecording` int(11) NOT NULL DEFAULT '0',
  ADD COLUMN `CallRecordingDuration` int(11) DEFAULT NULL,
  ADD COLUMN `CallRecordingStartTime` datetime DEFAULT NULL,
  ADD COLUMN `CallRecordingEndTime` datetime DEFAULT NULL,
  ADD COLUMN `OutpaymentCost` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `Surcharges` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `Chargeback` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CollectionAmount` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CollectionPercent` decimal(18,6) DEFAULT NULL;
  

ALTER TABLE tblUsageDetailFailedCall
  ADD COLUMN `CLIPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD COLUMN `PackageSubscriptionID` int(11),
  ADD COLUMN `RecodingCost` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CallRecording` int(11) NOT NULL DEFAULT '0',
  ADD COLUMN `CallRecordingDuration` int(11) DEFAULT NULL,
  ADD COLUMN `CallRecordingStartTime` datetime DEFAULT NULL,
  ADD COLUMN `CallRecordingEndTime` datetime DEFAULT NULL,
  ADD COLUMN `OutpaymentCost` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `Surcharges` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `Chargeback` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CollectionAmount` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CollectionPercent` decimal(18,6) DEFAULT NULL;  
  

ALTER TABLE tblVendorCDR
  ADD COLUMN `CLIPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD COLUMN `PackageSubscriptionID` int(11),
  ADD COLUMN `RecodingCost` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CallRecording` int(11) NOT NULL DEFAULT '0',
  ADD COLUMN `CallRecordingDuration` int(11) DEFAULT NULL,
  ADD COLUMN `CallRecordingStartTime` datetime DEFAULT NULL,
  ADD COLUMN `CallRecordingEndTime` datetime DEFAULT NULL,
  ADD COLUMN `OutpaymentCost` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `Surcharges` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `Chargeback` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CollectionAmount` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CollectionPercent` decimal(18,6) DEFAULT NULL;


ALTER TABLE tblVendorCDRFailed
  ADD COLUMN `CLIPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD COLUMN `PackageSubscriptionID` int(11),
  ADD COLUMN `RecodingCost` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CallRecording` int(11) NOT NULL DEFAULT '0',
  ADD COLUMN `CallRecordingDuration` int(11) DEFAULT NULL,
  ADD COLUMN `CallRecordingStartTime` datetime DEFAULT NULL,
  ADD COLUMN `CallRecordingEndTime` datetime DEFAULT NULL,
  ADD COLUMN `OutpaymentCost` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `Surcharges` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `Chargeback` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CollectionAmount` decimal(18,6) DEFAULT NULL,
  ADD COLUMN `CollectionPercent` decimal(18,6) DEFAULT NULL;  
  
  