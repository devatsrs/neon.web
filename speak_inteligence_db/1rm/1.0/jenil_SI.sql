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

INSERT INTO `tblRateType` (`RateTypeID`, `Slug`, `Title`, `Description`, `Active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
	(1, 'voicecall', 'Voice Call', NULL, 1, '2018-12-27 15:14:39', '2018-12-27 15:14:44', 'jenil', NULL),
	(2, 'did', 'DID', NULL, 1, '2018-12-27 15:14:39', '2018-12-27 15:14:44', 'jenil', NULL);



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
			vc.Location,
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
			vc.Location,
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
	

	
CREATE TABLE IF NOT EXISTS `tblAccountPaymentAutomation` (
  `AccountPaymentAutomationID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `AutoTopup` tinyint(1) DEFAULT '0',
  `MinThreshold` decimal(18,2) DEFAULT NULL,
  `TopupAmount` decimal(18,2) DEFAULT NULL,
  `AutoOutpayment` tinyint(1) DEFAULT '0',
  `OutPaymentThreshold` decimal(18,2) DEFAULT NULL,
  `OutPaymentAmount` decimal(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountPaymentAutomationID`),
  UNIQUE KEY `AccountID` (`AccountID`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


	
ALTER TABLE `tblPayment`
	ADD COLUMN `IsOutPayment` TINYINT(1) NULL DEFAULT '0' AFTER `UsageEndDate`;
	

USE speakintelligentBilling;	
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


-- Dumping structure for table speakIntelligentRoutingEngine.tblActiveCall
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
  `Cost` decimal(18,6) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  `CallType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CallRecordingStartTime` datetime DEFAULT NULL,
  `CallRecording` tinyint(4) DEFAULT NULL,
  `PackageSubscriptionID` int(11) DEFAULT NULL,
  `RecordingCost` decimal(18,6) DEFAULT NULL,
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
  `TrunkID` int(11) DEFAULT NULL,
  `VendorID` int(11) DEFAULT NULL,
  `AccountServiceID` int(11) DEFAULT NULL,
  `IsBlock` tinyint(4) DEFAULT '0',
  `BlockReason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ActiveCallID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


use speakintelligentRM;

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, NULL, 'ActiveCall Balance Alert', 'activecallbalancealert', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Api URL","type":"text","value":"","name":"APIURL"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2018-12-29 14:07:52', 'RateManagementSystem');


USE speakIntelligentRoutingEngine;
-- Dumping structure for procedure speakIntelligentRoutingEngine.prc_getBlockCall
DROP PROCEDURE IF EXISTS `prc_getBlockCall`;
DELIMITER //
CREATE PROCEDURE `prc_getBlockCall`(
	IN `p_AccountId` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		IF p_AccountId > 0 THEN
			SELECT
				ud.*,
				uh.StartDate,
				uh.GatewayAccountID
			FROM speakintelligentCDR.tblUsageDetails  ud
			INNER JOIN speakintelligentCDR.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN speakintelligentRM.tblAccount a
				ON uh.AccountID = a.AccountID
			WHERE
			(p_StartDate ='0000-00-00' OR ( p_StartDate != '0000-00-00' AND DATE(uh.StartDate) >= p_StartDate))
			AND (p_EndDate ='0000-00-00' OR ( p_EndDate != '0000-00-00' AND DATE(uh.StartDate) <= p_EndDate))
			AND uh.AccountID = p_AccountID
			AND ud.disposition='Blocked';
			
		ELSE 
		
			SELECT
				ud.*,
				uh.StartDate,
				uh.GatewayAccountID
			FROM speakintelligentCDR.tblUsageDetails  ud
			INNER JOIN speakintelligentCDR.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			WHERE
			(p_StartDate ='0000-00-00' OR ( p_StartDate != '0000-00-00' AND DATE(uh.StartDate) >= p_StartDate))
			AND (p_EndDate ='0000-00-00' OR ( p_EndDate != '0000-00-00' AND DATE(uh.StartDate) <= p_EndDate))
			AND ud.disposition='Blocked';
			
		END IF;
		
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


/* Above done on staging */


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
  
  
  
/* Api Latest Change - Local*/ 
INSERT INTO `tbldynamicfields` (`CompanyID`, `Type`, `FieldDomType`, `FieldName`, `FieldSlug`, `FieldDescription`, `FieldOrder`, `Status`, `created_at`, `created_by`, `updated_at`, `updated_by`, `ItemTypeID`, `Minimum`, `Maximum`, `DefaultValue`, `SelectVal`) VALUES (1, 'account', 'text', 'SI AccountReference', 'SIAccountID', 'SI AccountReference', 0, 1, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL);
 
  
/*Vendor RateChanges - Download vendor Rate*/


DROP PROCEDURE IF EXISTS `vwVendorCurrentRates`;
DELIMITER //
CREATE PROCEDURE `vwVendorCurrentRates`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
		AccountId int,
		Code varchar(50),
		Description varchar(200),
		Rate float,
		RateN float,
		EffectiveDate date,
		TrunkID int,
		TimezonesID int,
		CountryID int,
		RateID int,
		Preference int,
		Blocked int,
		Interval1 INT,
		IntervalN varchar(100),
		ConnectionFee float,
		EndDate date
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
        TimezonesID INT,
	 	  RateId INT,
	 	  Preference int,
		  Blocked int,
        Rate DECIMAL(18,6),
        RateN DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        EndDate date,
        INDEX tmp_RateTable_RateId (`RateId`)
    );
    
       /* INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `TimezonesID`, `RateId`, `Rate`, `RateN`, `EffectiveDate`, `Interval1`, `IntervalN`, `ConnectionFee` , tblVendorRate.EndDate
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_Trunks) != 0
								AND tblVendorRate.TimezonesID = p_TimezonesID
                        AND
								(
					  				(p_Effective = 'Now' AND EffectiveDate <= NOW() AND (EndDate IS NULL OR EndDate > NOW() ))
								  	OR
								  	(p_Effective = 'Future' AND EffectiveDate > NOW() AND ( EndDate IS NULL OR EndDate > NOW() ))
								  	OR
								  	(p_Effective = 'CustomDate' AND EffectiveDate <= p_CustomDate AND (EndDate IS NULL OR EndDate > p_CustomDate))
								  	OR
								  	(p_Effective = 'All'  )
								);*/
								
		INSERT INTO tmp_VendorRate_
		SELECT  vc.`TrunkID`, rtr.`TimezonesID`, rtr.`RateId`, rtr.Preference, rtr.Blocked,rtr.`Rate`, rtr.`RateN`, DATE_FORMAT (rtr.`EffectiveDate`, '%Y-%m-%d'), rtr.`Interval1`, rtr.`IntervalN`, rtr.`ConnectionFee` , rtr.EndDate
		FROM tblRateTableRate rtr
		INNER JOIN tblVendorConnection vc ON vc.RateTableID=rtr.RateTableId
		INNER JOIN tblRateTable rt ON rtr.RateTableId=vc.RateTableID
		WHERE vc.AccountId =  p_AccountID
			AND vc.RateTypeID = p_VoiceCallTypeID
			AND rt.AppliedTo  = p_AppliedToVendorID
			AND FIND_IN_SET(vc.TrunkId,p_Trunks) != 0
			AND rtr.TimezonesID = p_TimezonesID
			AND
			(
				(p_Effective = 'Now' AND rtr.EffectiveDate <= NOW() AND (rtr.EndDate IS NULL OR EndDate > NOW() ))
				OR
				(p_Effective = 'Future' AND rtr.EffectiveDate > NOW() AND (rtr.EndDate IS NULL OR EndDate > NOW() ))
				OR
				(p_Effective = 'CustomDate' AND rtr.EffectiveDate <= p_CustomDate AND (rtr.EndDate IS NULL OR rtr.EndDate > p_CustomDate))
				OR
				(p_Effective = 'All'  )
			);
			
			
    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
 	   AND n1.TimezonesID = n2.TimezonesID
	   AND  n1.RateId = n2.RateId
		AND
	   (
			(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
		  	OR
		  	(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
		);


    INSERT INTO tmp_VendorCurrentRates_
    SELECT DISTINCT
    p_AccountID,
    r.Code,
    r.Description,
    v_1.Rate,
    v_1.RateN,
    DATE_FORMAT (v_1.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
    v_1.TrunkID,
    v_1.TimezonesID,
    r.CountryID,
    r.RateID,
    v_1.Preference,
    v_1.Blocked,
   	CASE WHEN v_1.Interval1 is not null
   		THEN v_1.Interval1
    	ELSE r.Interval1
    END as  Interval1,
    CASE WHEN v_1.IntervalN is not null
    	THEN v_1.IntervalN
        ELSE r.IntervalN
    END IntervalN,
    v_1.ConnectionFee,
    v_1.EndDate
    FROM tmp_VendorRate_ AS v_1
	INNER JOIN tblRate AS r
    	ON r.RateID = v_1.RateId;
    	

END//
DELIMITER ;



 
DROP PROCEDURE IF EXISTS `vwVendorSippySheet`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `vwVendorSippySheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE


)
BEGIN

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorArhiveSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArhiveSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		call vwVendorCurrentRates(p_AccountID,p_Trunks,p_TimezonesID,p_Effective,p_CustomDate);

		INSERT INTO tmp_VendorSippySheet_
			SELECT
				NULL AS RateID,
				CASE WHEN EndDate IS NOT NULL THEN
					'SA'
				ELSE
					'A'
				END AS `Action [A|D|U|S|SA`,
				'' AS id,
				Concat('' , tblTrunk.Prefix ,vendorRate.Code) AS Prefix,
				vendorRate.Description AS COUNTRY,
				IFNULL(vendorRate.Preference,5) as Preference,
				vendorRate.Interval1 as `Interval 1`,
				vendorRate.IntervalN as `Interval N`,
				vendorRate.Rate AS `Price 1`,
				vendorRate.RateN AS `Price N`,
				10 AS `1xx Timeout`,
				60 AS `2xx Timeout`,
				0 AS Huntstop,
				CASE
				WHEN (
					vendorRate.Blocked IS NOT NULL AND vendorRate.Blocked !=0	
				) THEN 1
				ELSE 0
				END  AS Forbidden,
				CASE WHEN EffectiveDate < NOW()  THEN
					'NOW'
				ELSE
					DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' )
				END AS `Activation Date`,
				DATE_FORMAT( EndDate, '%Y-%m-%d %H:%i:%s' )  AS `Expiration Date`,

				tblAccount.AccountID,
				tblTrunk.TrunkID
			FROM tmp_VendorCurrentRates_ AS vendorRate
				INNER JOIN tblAccount
					ON vendorRate.AccountId = tblAccount.AccountID				
				INNER JOIN tblTrunk
					ON tblTrunk.TrunkID = vendorRate.TrunkID
			WHERE (vendorRate.Rate > 0);


	END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_CronJobGenerateM2VendorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGenerateM2VendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_m2rateall_;
    CREATE TEMPORARY TABLE tmp_m2rateall_ (
        RateID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE
    );

    
    call vwVendorCurrentRates(p_AccountID,p_trunks,p_TimezonesID,p_Effective,p_CustomDate,p_AppliedToVendorID,p_VoiceCallTypeID);
         
     INSERT INTO tmp_m2rateall_
     SELECT Distinct
			vendorRate.RateID as `RateID`,
			vendorRate.Code as `Code`,
			vendorRate.Description as `Description` ,
			vendorRate.Interval1 as `Interval1` ,
			vendorRate.IntervalN as `IntervalN` ,			
			vendorRate.ConnectionFee as `ConnectionFee`,
			Abs(vendorRate.Rate) as `Rate`,
			vendorRate.EffectiveDate as `EffectiveDate`
        FROM  tmp_VendorCurrentRates_ as vendorRate;



		SELECT DISTINCT
			CONCAT(IF(tblCountry.Country IS NULL,'',CONCAT(tblCountry.Country,' - ')),tmpRate.Description) as `Destination`,
			tmpRate.Code as `Prefix`,
			tmpRate.Rate as `Rate(USD)`,
			tmpRate.ConnectionFee as `Connection Fee(USD)`,
			tmpRate.Interval1 as `Increment`,
			tmpRate.IntervalN as `Minimal Time`,
			'0:00:00 'as `Start Time`,
			'23:59:59' as `End Time`,
			'' as `Week Day`,
			tmpRate.EffectiveDate  as `Effective from`,
			TRUNCATE(tmpRate.Rate + ((tmpRate.Rate * 2.041)/100),6) as `Rate(USD) with Tax`
		FROM
			tmp_m2rateall_ AS tmpRate
		JOIN
			tblRate ON tblRate.RateID = tmpRate.RateID
		LEFT JOIN
			tblCountry ON tblCountry.CountryID = tblRate.CountryID;

      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `prc_CronJobGenerateMorVendorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGenerateMorVendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
		
		call vwVendorCurrentRates(p_AccountID,p_trunks,p_TimezonesID,p_Effective,p_CustomDate,p_AppliedToVendorID,p_VoiceCallTypeID);

		DROP TEMPORARY TABLE IF EXISTS tmp_morrateall_;
    CREATE TEMPORARY TABLE tmp_morrateall_ (
        RateID INT,
        Country VARCHAR(155),
        CountryCode VARCHAR(50),
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        SubCode VARCHAR(50)
    );

     INSERT INTO tmp_morrateall_
     SELECT Distinct
          vendorRate.RateID as `RateID`,
			  c.Country as `Country`,
			  c.ISO3 as `CountryCode`,
			  vendorRate.Code as `Code`,
           vendorRate.Description as `Description` ,
           vendorRate.Interval1 as `Interval1` ,
			  vendorRate.IntervalN as `IntervalN` ,
           vendorRate.ConnectionFee as `ConnectionFee`,
           Abs(vendorRate.Rate) as `Rate`,
           'FIX' as `SubCode`

       FROM    tmp_VendorCurrentRates_ as vendorRate
       JOIN tblRate on vendorRate.RateID =tblRate.RateID
       LEFT JOIN tblCountry as c ON tblRate.CountryID = c.CountryID;

		UPDATE tmp_morrateall_
	  			SET SubCode='MOB'
	  			WHERE Description LIKE '%Mobile%';


		SELECT DISTINCT
	      Country as `Direction` ,
	      Description  as `Destination`,
		   Code as `Prefix`,
		   SubCode as `Subcode`,
		   CountryCode as `Country code`,
		   Rate as `Rate(EUR)`,
		   ConnectionFee as `Connection Fee(EUR)`,
		   Interval1 as `Increment`,
		   IntervalN as `Minimal Time`,
		   '0:00:00 'as `Start Time`,
		   '23:59:59' as `End Time`,
		   '' as `Week Day`
     FROM tmp_morrateall_;

      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `vwVendorArchiveCurrentRates`;
DELIMITER //
CREATE PROCEDURE `vwVendorArchiveCurrentRates`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorArchiveCurrentRates_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArchiveCurrentRates_(
		AccountId int,
		Code varchar(50),
		Description varchar(200),
		Rate float,
		RateN float,
		EffectiveDate date,
		TrunkID int,
		CountryID int,
		RateID int,
		Interval1 INT,
		IntervalN varchar(100),
		ConnectionFee float,
		EndDate date
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateArchive_;
    CREATE TEMPORARY TABLE tmp_VendorRateArchive_ (
        TrunkId INT,
        TimezonesID INT,
	 	  RateId INT,
        Rate DECIMAL(18,6),
        RateN DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        EndDate date,
        INDEX tmp_RateTable_RateId (`RateId`)
    );
    
        INSERT INTO tmp_VendorRateArchive_
        SELECT   vc.`TrunkID`, vra.`TimezonesID`, vra.`RateId`, vra.`Rate`, vra.`RateN`, vra.`EffectiveDate`, vra.`Interval1`, vra.`IntervalN`, vra.`ConnectionFee` , vra.EndDate
		  FROM tblRateTableRateArchive vra
		  INNER JOIN tblVendorConnection vc ON vc.RateTableID=vra.RateTableId
		  INNER JOIN tblRateTable rt ON rt.RateTableId=vc.RateTableID
		  
		  WHERE 
		  vc.AccountId =  p_AccountID
			AND vc.RateTypeID = p_VoiceCallTypeID
			AND rt.AppliedTo  = p_AppliedToVendorID
			AND FIND_IN_SET(vc.TrunkId,p_Trunks) != 0
			AND vra.TimezonesID = p_TimezonesID
         AND
         (
					
				vra.EffectiveDate <= NOW() AND date(vra.EndDate) = date(NOW())
			)
								;

    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateArchive4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateArchive4_ as (select * from tmp_VendorRateArchive_);
      DELETE n1 FROM tmp_VendorRateArchive_ n1, tmp_VendorRateArchive4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
 	   AND n1.TimezonesID = n2.TimezonesID
	   AND  n1.RateId = n2.RateId
		AND n1.EffectiveDate <= NOW()
		AND n2.EffectiveDate <= NOW();


    INSERT INTO tmp_VendorArchiveCurrentRates_
    SELECT DISTINCT
    p_AccountID,
    r.Code,
    r.Description,
    v_1.Rate,
    v_1.RateN,
    DATE_FORMAT (v_1.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
    v_1.TrunkID,
    r.CountryID,
    r.RateID,
   	CASE WHEN v_1.Interval1 is not null
   		THEN v_1.Interval1
    	ELSE r.Interval1
    END as  Interval1,
    CASE WHEN v_1.IntervalN is not null
    	THEN v_1.IntervalN
        ELSE r.IntervalN
    END IntervalN,
    v_1.ConnectionFee,
    v_1.EndDate
    FROM tmp_VendorRateArchive_ AS v_1
	INNER JOIN tblRate AS r
    	ON r.RateID = v_1.RateId;

END//
DELIMITER ;

	

DROP PROCEDURE IF EXISTS `prc_CronJobGeneratePortaVendorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGeneratePortaVendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	    
   call vwVendorCurrentRates(p_AccountID,p_trunks,p_TimezonesID,p_Effective,p_CustomDate,p_AppliedToVendorID,p_VoiceCallTypeID);


	DROP TEMPORARY TABLE IF EXISTS tmp_VendorArchiveCurrentRates_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArchiveCurrentRates_(
		AccountId int,
		Code varchar(50),
		Description varchar(200),
		Rate float,
		RateN float,
		EffectiveDate date,
		TrunkID int,
		CountryID int,
		RateID int,
		Interval1 INT,
		IntervalN varchar(100),
		ConnectionFee float,
		EndDate date
    );

	IF p_Effective = 'Now' || p_Effective = 'All' THEN

  	 	call vwVendorArchiveCurrentRates(p_AccountID,p_Trunks,p_TimezonesID,p_Effective,p_AppliedToVendorID,p_VoiceCallTypeID);

	END IF;

       SELECT Distinct  
		 			vendorRate.Code as `Destination`,
               vendorRate.Description as `Description` ,
               vendorRate.Interval1 as `First Interval`,
               vendorRate.IntervalN as `Next Interval`,
               Abs(vendorRate.Rate) as `First Price`,
               Abs(vendorRate.RateN) as `Next Price`,
               DATE_FORMAT (vendorRate.EffectiveDate, '%Y-%m-%d')  as `Effective From` ,
               IFNULL(vendorRate.Preference,5) as `Preference`,
               CASE
                   WHEN (vendorRate.Blocked IS NOT NULL AND vendorRate.Blocked != 0)
                        THEN 'Y'
                   ELSE 'N'
               END AS `Forbidden`,
               CASE WHEN vendorRate.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
               CASE WHEN vendorRate.ConnectionFee > 0 THEN
						CONCAT('SEQ=',vendorRate.ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`,
					'N' AS `Discontinued`
       FROM tmp_VendorCurrentRates_ as vendorRate
               
       UNION ALL



		SELECT
					Distinct
			    	vrd.Code AS `Destination`,
			 		vrd.Description AS `Description` ,
			 		vrd.Interval1 AS `First Interval`,
               vrd.IntervalN AS `Next Interval`,
			 		Abs(vrd.Rate) AS `First Price`,
			 		Abs(vrd.RateN) AS `Next Price`,
			 		DATE_FORMAT (vrd.EffectiveDate, '%Y-%m-%d') AS `Effective From`,
			 		'' AS `Preference`,
			 		'' AS `Forbidden`,
			 		CASE WHEN vrd.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
			 		CASE WHEN vrd.ConnectionFee > 0 THEN
						CONCAT('SEQ=',vrd.ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`,
			 		'Y' AS `Discontinued`
			FROM tmp_VendorArchiveCurrentRates_ AS vrd
	 		JOIN tblRate on vrd.RateId = tblRate.RateID
			WHERE
				FIND_IN_SET(vrd.TrunkID,p_trunks) != 0
			AND vrd.AccountId = p_AccountID
			AND vrd.Rate > 0;
			
			
			
			/*LEFT JOIN tblVendorRate vr
						ON vrd.AccountId = vr.AccountId
							AND vrd.TrunkID = vr.TrunkID
							AND vrd.RateId = vr.RateId
					WHERE FIND_IN_SET(vrd.TrunkID,p_trunks) != 0
						AND vrd.AccountId = p_AccountID
						AND vr.VendorRateID IS NULL
						AND vrd.Rate > 0;*/



      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

	

	
DROP PROCEDURE IF EXISTS `prc_WSGenerateVendorSippySheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateVendorSippySheet`(
	IN `p_VendorID` INT  ,
	IN `p_Trunks` varchar(200),
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		call vwVendorSippySheet(p_VendorID,p_Trunks,p_TimezonesID,p_Effective,p_CustomDate,p_AppliedToVendorID,p_VoiceCallTypeID);

		SELECT
			`Action [A|D|U|S|SA`,
			id ,
			vendorRate.Prefix,
			COUNTRY,
			Preference ,
			`Interval 1` ,
			`Interval N` ,
			`Price 1` ,
			`Price N` ,
			`1xx Timeout` ,
			`2xx Timeout` ,
			`Huntstop` ,
			Forbidden ,
			`Activation Date` ,
			`Expiration Date`
		FROM    tmp_VendorSippySheet_ vendorRate
		WHERE   vendorRate.AccountId = p_VendorID
						And  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0;

		

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `vwVendorSippySheet`;
DELIMITER //
CREATE PROCEDURE `vwVendorSippySheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorArhiveSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArhiveSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		call vwVendorCurrentRates(p_AccountID,p_Trunks,p_TimezonesID,p_Effective,p_CustomDate,p_AppliedToVendorID,p_VoiceCallTypeID);

		INSERT INTO tmp_VendorSippySheet_
			SELECT
				NULL AS RateID,
				CASE WHEN EndDate IS NOT NULL THEN
					'SA'
				ELSE
					'A'
				END AS `Action [A|D|U|S|SA`,
				'' AS id,
				Concat('' , tblTrunk.Prefix ,vendorRate.Code) AS Prefix,
				vendorRate.Description AS COUNTRY,
				IFNULL(vendorRate.Preference,5) as Preference,
				vendorRate.Interval1 as `Interval 1`,
				vendorRate.IntervalN as `Interval N`,
				vendorRate.Rate AS `Price 1`,
				vendorRate.RateN AS `Price N`,
				10 AS `1xx Timeout`,
				60 AS `2xx Timeout`,
				0 AS Huntstop,
				CASE
				WHEN (
					vendorRate.Blocked IS NOT NULL AND vendorRate.Blocked !=0	
				) THEN 1
				ELSE 0
				END  AS Forbidden,
				CASE WHEN EffectiveDate < NOW()  THEN
					'NOW'
				ELSE
					DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' )
				END AS `Activation Date`,
				DATE_FORMAT( EndDate, '%Y-%m-%d %H:%i:%s' )  AS `Expiration Date`,

				tblAccount.AccountID,
				tblTrunk.TrunkID
			FROM tmp_VendorCurrentRates_ AS vendorRate
				INNER JOIN tblAccount
					ON vendorRate.AccountId = tblAccount.AccountID				
				INNER JOIN tblTrunk
					ON tblTrunk.TrunkID = vendorRate.TrunkID
			WHERE (vendorRate.Rate > 0);


	END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_WSGenerateVendorVersion3VosSheet`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateVendorVersion3VosSheet`(
	IN `p_VendorID` INT ,
	IN `p_Trunks` varchar(200) ,
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_Format` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN
         SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

        call vwVendorVersion3VosSheet(p_VendorID,p_Trunks,p_TimezonesID,p_Effective,p_CustomDate,p_AppliedToVendorID,p_VoiceCallTypeID);

        IF p_Effective = 'Now' OR p_Format = 'Vos 2.0'
		  THEN

	        SELECT  `Rate Prefix` ,
	                `Area Prefix` ,
	                `Rate Type` ,
	                `Area Name` ,
	                `Billing Rate` ,
	                `Billing Cycle`,
	                `Minute Cost` ,
	                `Lock Type` ,
	                `Section Rate` ,
	                `Billing Rate for Calling Card Prompt` ,
	                `Billing Cycle for Calling Card Prompt`
	        FROM    tmp_VendorVersion3VosSheet_
	       
	       
	        ORDER BY `Rate Prefix`;

        END IF;

        IF ( (p_Effective = 'Future' OR p_Effective = 'All' OR p_Effective = 'CustomDate') AND p_Format = 'Vos 3.2'  )
		  THEN

				DROP TEMPORARY TABLE IF EXISTS tmp_VendorVersion3VosSheet2_ ;
				CREATE TEMPORARY TABLE tmp_VendorVersion3VosSheet2_ SELECT * FROM tmp_VendorVersion3VosSheet_;

				SELECT
					 	 `Time of timing replace`,
						 `Mode of timing replace`,
			  			 `Rate Prefix` ,
	                `Area Prefix` ,
	                `Rate Type` ,
	                `Area Name` ,
	                `Billing Rate` ,
	                `Billing Cycle`,
	                `Minute Cost` ,
	                `Lock Type` ,
	                `Section Rate` ,
	                `Billing Rate for Calling Card Prompt` ,
	                `Billing Cycle for Calling Card Prompt`
	        FROM (
					  SELECT  CONCAT(EffectiveDate,' 00:00') as `Time of timing replace`,
								 'Append replace' as `Mode of timing replace`,
					  			 `Rate Prefix` ,
			                `Area Prefix` ,
			                `Rate Type` ,
			                `Area Name` ,
			                `Billing Rate` ,
			                `Billing Cycle`,
			                `Minute Cost` ,
			                `Lock Type` ,
			                `Section Rate` ,
			                `Billing Rate for Calling Card Prompt` ,
			                `Billing Cycle for Calling Card Prompt`
			        FROM    tmp_VendorVersion3VosSheet2_
			        
			       
			       

			   	
					  
			      
			      

			   	UNION ALL

			        
			        SELECT
			        		distinct
					  	  CONCAT(EndDate,' 00:00') as `Time of timing replace`,
						 	'Delete' as `Mode of timing replace`,
					  		`Rate Prefix` ,
			                `Area Prefix` ,
			                `Rate Type` ,
			                `Area Name` ,
			                `Billing Rate` ,
			                `Billing Cycle`,
			                `Minute Cost` ,
			                `Lock Type` ,
			                `Section Rate` ,
			                `Billing Rate for Calling Card Prompt` ,
			                `Billing Cycle for Calling Card Prompt`
			        FROM    tmp_VendorArhiveVersion3VosSheet_

					  
			      


	      ) tmp
	      ORDER BY `Rate Prefix`;



     END IF;





        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `vwVendorVersion3VosSheet`;
DELIMITER //
CREATE PROCEDURE `vwVendorVersion3VosSheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE




,
	IN `p_AppliedToVendorID` INT,
	IN `p_VoiceCallTypeID` INT
)
BEGIN



	DROP TEMPORARY TABLE IF EXISTS tmp_VendorVersion3VosSheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorVersion3VosSheet_(
			RateID int,
			`Rate Prefix` varchar(50),
			`Area Prefix` varchar(50),
			`Rate Type` varchar(50),
			`Area Name` varchar(200),
			`Billing Rate` float,
			`Billing Cycle` int,
			`Minute Cost` float,
			`Lock Type` varchar(50),
			`Section Rate` varchar(50),
			`Billing Rate for Calling Card Prompt` float,
			`Billing Cycle for Calling Card Prompt` INT,
			AccountID int,
			TrunkID int,
			EffectiveDate date,
			EndDate date
	);


	DROP TEMPORARY TABLE IF EXISTS tmp_VendorArhiveVersion3VosSheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArhiveVersion3VosSheet_(
			RateID int,
			`Rate Prefix` varchar(50),
			`Area Prefix` varchar(50),
			`Rate Type` varchar(50),
			`Area Name` varchar(200),
			`Billing Rate` float,
			`Billing Cycle` int,
			`Minute Cost` float,
			`Lock Type` varchar(50),
			`Section Rate` varchar(50),
			`Billing Rate for Calling Card Prompt` float,
			`Billing Cycle for Calling Card Prompt` INT,
			AccountID int,
			TrunkID int,
			EffectiveDate date,
			EndDate date
	);


	 Call vwVendorCurrentRates(p_AccountID,p_Trunks,p_TimezonesID,p_Effective,p_CustomDate,p_AppliedToVendorID,p_VoiceCallTypeID);


INSERT INTO tmp_VendorVersion3VosSheet_
SELECT


    vendorRate.RateID AS RateID,
    IFNULL(tblTrunk.RatePrefix, '') AS `Rate Prefix`,
    Concat('' , IFNULL(tblTrunk.AreaPrefix, '') , vendorRate.Code) AS `Area Prefix`,
    'International' AS `Rate Type`,
    vendorRate.Description AS `Area Name`,
    vendorRate.Rate / 60 AS `Billing Rate`,
    vendorRate.IntervalN AS `Billing Cycle`,
    CAST(vendorRate.Rate AS DECIMAL(18, 5)) AS `Minute Cost`,
    CASE
        WHEN (vendorRate.Blocked IS NOT NULL AND vendorRate.Blocked!=0) 
		  THEN 
		  		'No Lock'
        ELSE 
		  		'No Lock'
    END
    AS `Lock Type`,
   CASE WHEN vendorRate.Interval1 != vendorRate.IntervalN
                                      THEN
                    Concat('0,', vendorRate.Rate, ',',vendorRate.Interval1)
                                      ELSE ''
                                 END as `Section Rate`,
    0 AS `Billing Rate for Calling Card Prompt`,
    0 AS `Billing Cycle for Calling Card Prompt`,
    tblAccount.AccountID,
    vendorRate.TrunkId,
    vendorRate.EffectiveDate,
    vendorRate.EndDate
FROM tmp_VendorCurrentRates_ AS vendorRate
INNER JOIN tblAccount
    ON vendorRate.AccountId = tblAccount.AccountID
INNER JOIN tblTrunk
    ON tblTrunk.TrunkID = vendorRate.TrunkId
WHERE (vendorRate.Rate > 0);


	 
	 IF p_Effective != 'Now' THEN

		 	call vwVendorArchiveCurrentRates(p_AccountID,p_Trunks,p_TimezonesID,p_Effective,p_AppliedToVendorID,p_VoiceCallTypeID);

			INSERT INTO tmp_VendorArhiveVersion3VosSheet_
			SELECT


			    NULL AS RateID,
			    IFNULL(tblTrunk.RatePrefix, '') AS `Rate Prefix`,
			    Concat('' , IFNULL(tblTrunk.AreaPrefix, '') , vendorArchiveRate.Code) AS `Area Prefix`,
			    'International' AS `Rate Type`,
			    vendorArchiveRate.Description AS `Area Name`,
			    vendorArchiveRate.Rate / 60 AS `Billing Rate`,
			    vendorArchiveRate.IntervalN AS `Billing Cycle`,
			    CAST(vendorArchiveRate.Rate AS DECIMAL(18, 5)) AS `Minute Cost`,
			    'No Lock'   AS `Lock Type`,
			     CASE WHEN vendorArchiveRate.Interval1 != vendorArchiveRate.IntervalN THEN
				           Concat('0,', vendorArchiveRate.Rate, ',',vendorArchiveRate.Interval1)
			   	ELSE ''
			    END as `Section Rate`,
			    0 AS `Billing Rate for Calling Card Prompt`,
			    0 AS `Billing Cycle for Calling Card Prompt`,
			    tblAccount.AccountID,
			    vendorArchiveRate.TrunkId,
			    vendorArchiveRate.EffectiveDate,
			    vendorArchiveRate.EndDate
			FROM tmp_VendorArchiveCurrentRates_ AS vendorArchiveRate
			Left join tmp_VendorVersion3VosSheet_ vendorRate
				 ON vendorArchiveRate.AccountId = vendorRate.AccountID
				 AND vendorArchiveRate.TrunkID = vendorRate.TrunkID
 				 AND vendorArchiveRate.RateID = vendorRate.RateID

			INNER JOIN tblAccount
			    ON vendorArchiveRate.AccountId = tblAccount.AccountID
			INNER JOIN tblTrunk
			    ON tblTrunk.TrunkID = vendorArchiveRate.TrunkId
			WHERE vendorRate.RateID is Null AND 
			(vendorArchiveRate.Rate > 0);

	 END IF;

END//
DELIMITER ;


