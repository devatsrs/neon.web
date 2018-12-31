use `speakintelligentRM`;

CREATE TABLE IF NOT EXISTS `tblDIDCategory` (
  `DIDCategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`DIDCategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `tblRateTable`
	ADD COLUMN `DIDCategoryID` INT(11) NOT NULL AFTER `RoundChargedAmount`,
	ADD COLUMN `Type` INT(11) NOT NULL DEFAULT '1' AFTER `DIDCategoryID`,
	ADD COLUMN `MinimumCallCharge` DECIMAL(18,6) NULL DEFAULT NULL AFTER `Type`,
	ADD COLUMN `AppliedTo` INT NOT NULL DEFAULT '1' AFTER `MinimumCallCharge`;

ALTER TABLE `tblRateTableRate`
	ADD COLUMN `OriginationRateID` INT(11) NULL DEFAULT NULL AFTER `RateTableRateID`,
	ADD COLUMN `RoutingCategoryID` INT(11) NULL DEFAULT NULL AFTER `ConnectionFee`,
	ADD COLUMN `Preference` INT(11) NULL DEFAULT NULL AFTER `RoutingCategoryID`,
	ADD COLUMN `Blocked` TINYINT NOT NULL DEFAULT '0' AFTER `Preference`,
	ADD COLUMN `ApprovedStatus` TINYINT(4) NOT NULL DEFAULT '1' AFTER `Blocked`,
	ADD COLUMN `ApprovedBy` VARCHAR(50) NULL DEFAULT NULL AFTER `ApprovedStatus`,
	ADD COLUMN `ApprovedDate` DATETIME NULL DEFAULT NULL AFTER `ApprovedBy`;

ALTER TABLE `tblRateTableRateArchive`
	ADD COLUMN `OriginationRateID` INT(11) NULL DEFAULT NULL AFTER `TimezonesID`,
	ADD COLUMN `RoutingCategoryID` INT(11) NULL DEFAULT NULL AFTER `ConnectionFee`,
	ADD COLUMN `Preference` INT(11) NULL DEFAULT NULL AFTER `RoutingCategoryID`,
	ADD COLUMN `Blocked` TINYINT NOT NULL DEFAULT '0' AFTER `Preference`
	ADD COLUMN `ApprovedStatus` TINYINT(4) NOT NULL DEFAULT '1' AFTER `Blocked`,
	ADD COLUMN `ApprovedBy` VARCHAR(50) NULL DEFAULT NULL AFTER `ApprovedStatus`,
	ADD COLUMN `ApprovedDate` DATETIME NULL DEFAULT NULL AFTER `ApprovedBy`;

ALTER TABLE `tblRateTableRate`
	DROP INDEX `IX_Unique_RateID_RateTableId_TimezonesID_EffectiveDate`,
	ADD UNIQUE INDEX `IX_Unique_RateID_ORateID_RateTableId_TimezonesID_EffectiveDate` (`RateID`, `OriginationRateID`, `RateTableId`, `TimezonesID`, `EffectiveDate`);

CREATE TABLE IF NOT EXISTS `tblRateTableDIDRate` (
  `RateTableDIDRateID` bigint(20) NOT NULL AUTO_INCREMENT,
  `OriginationRateID` bigint(20) NULL DEFAULT NULL,
  `RateID` int(11) NOT NULL,
  `RateTableId` bigint(20) NOT NULL,
  `TimezonesID` bigint(20) NOT NULL DEFAULT '1',
  `EffectiveDate` date NOT NULL,
  `EndDate` DATE NULL DEFAULT NULL,
  `OneOffCost` decimal(18,6) DEFAULT NULL,
  `MonthlyCost` decimal(18,6) DEFAULT NULL,
  `CostPerCall` decimal(18,6) DEFAULT NULL,
  `CostPerMinute` decimal(18,6) DEFAULT NULL,
  `SurchargePerCall` decimal(18,6) DEFAULT NULL,
  `SurchargePerMinute` decimal(18,6) DEFAULT NULL,
  `OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
  `OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
  `Surcharges` decimal(18,6) DEFAULT NULL,
  `Chargeback` decimal(18,6) DEFAULT NULL,
  `CollectionCostAmount` decimal(18,6) DEFAULT NULL,
  `CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
  `RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
	`ApprovedStatus` TINYINT(4) NOT NULL DEFAULT '1',
	`ApprovedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ApprovedDate` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`RateTableDIDRateID`),
  UNIQUE KEY `IX_Unique_RateID_ORateID_RateTableId_TimezonesID_EffectiveDate` (`RateID`,`OriginationRateID`,`RateTableId`,`TimezonesID`,`EffectiveDate`),
  KEY `RateTableIDEffectiveDate` (`RateTableId`,`EffectiveDate`,`RateID`),
  KEY `IX_RateTableId_RateID_EffectiveDate` (`RateTableId`,`RateID`,`EffectiveDate`),
  KEY `IX_RateTableId` (`RateTableId`),
  KEY `XI_RateID_RatetableID` (`RateID`,`RateTableDIDRateID`),
  CONSTRAINT `FK_tblRateTableDIDRate_tblRate` FOREIGN KEY (`RateID`) REFERENCES `tblRate` (`RateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tblRateTableDIDRateArchive` (
  `RateTableDIDRateArchiveID` bigint(20) NOT NULL AUTO_INCREMENT,
  `RateTableDIDRateID` bigint(20) NOT NULL,
  `OriginationRateID` bigint(20) NULL DEFAULT NULL,
  `RateID` int(11) NOT NULL,
  `RateTableId` bigint(20) NOT NULL,
  `TimezonesID` bigint(20) NOT NULL DEFAULT '1',
  `EffectiveDate` date NOT NULL,
  `EndDate` DATE NULL DEFAULT NULL,
  `OneOffCost` decimal(18,6) DEFAULT NULL,
  `MonthlyCost` decimal(18,6) DEFAULT NULL,
  `CostPerCall` decimal(18,6) DEFAULT NULL,
  `CostPerMinute` decimal(18,6) DEFAULT NULL,
  `SurchargePerCall` decimal(18,6) DEFAULT NULL,
  `SurchargePerMinute` decimal(18,6) DEFAULT NULL,
  `OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
  `OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
  `Surcharges` decimal(18,6) DEFAULT NULL,
  `Chargeback` decimal(18,6) DEFAULT NULL,
  `CollectionCostAmount` decimal(18,6) DEFAULT NULL,
  `CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
  `RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
	`ApprovedStatus` TINYINT(4) NOT NULL DEFAULT '1',
	`ApprovedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ApprovedDate` DATETIME NULL DEFAULT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateTableDIDRateArchiveID`),
  KEY `RateTableIDEffectiveDate` (`RateTableId`,`EffectiveDate`,`RateID`),
  KEY `IX_RateTableId_RateID_EffectiveDate` (`RateTableId`,`RateID`,`EffectiveDate`),
  KEY `IX_RateTableId` (`RateTableId`),
  KEY `XI_RateID_RatetableID` (`RateID`,`RateTableDIDRateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `tblTempRateTableRate`
	CHANGE COLUMN `TimezonesID` `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `CodeDeckId`,
	ADD COLUMN `OriginationCountryCode` VARCHAR(500) NULL DEFAULT NULL AFTER `TimezonesID`,
	ADD COLUMN `OriginationCode` TEXT NULL DEFAULT NULL AFTER `OriginationCountryCode`,
	ADD COLUMN `OriginationDescription` VARCHAR(200) NULL DEFAULT NULL AFTER `OriginationCode`,
	CHANGE COLUMN `CountryCode` `CountryCode` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `OriginationDescription`
	CHANGE COLUMN `Preference` `Preference` INT NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `ProcessId`,
	CHANGE COLUMN `Forbidden` `Blocked` TINYINT NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci' AFTER `IntervalN`,
	ADD COLUMN `RoutingCategoryID` INT NULL DEFAULT NULL AFTER `DialStringPrefix`;

ALTER TABLE `tblRateTableRateChangeLog`
	ADD COLUMN `OriginationRateID` INT(11) NULL DEFAULT NULL AFTER `TimezonesID`,
	ADD COLUMN `OriginationCode` VARCHAR(50) NULL DEFAULT NULL AFTER `OriginationRateID`,
	ADD COLUMN `OriginationDescription` VARCHAR(200) NULL DEFAULT NULL AFTER `OriginationCode`,
	ADD COLUMN `Preference` INT NULL DEFAULT NULL AFTER `ConnectionFee`,
	ADD COLUMN `Blocked` TINYINT NOT NULL DEFAULT '0' AFTER `Preference`,
	ADD COLUMN `RoutingCategoryID` INT NULL DEFAULT NULL AFTER `Blocked`;





DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableRate`(
	IN `p_RateTableIds` LONGTEXT,
	IN `p_TimezonesIDs` LONGTEXT,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableRate rtr
	INNER JOIN tblRateTableRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND ((rtr2.OriginationRateID IS NULL AND rtr.OriginationRateID IS NULL) OR (rtr2.OriginationRateID = rtr.OriginationRateID))
	SET
		rtr.EndDate=NOW()
	WHERE
		(FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0) AND rtr.EffectiveDate <= NOW()) AND
		(FIND_IN_SET(rtr2.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr2.TimezonesID,p_TimezonesIDs) != 0) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableRateID != rtr2.RateTableRateID;


	INSERT INTO tblRateTableRateArchive
	SELECT DISTINCT  null ,
		`RateTableRateID`,
		`RateTableId`,
		`TimezonesID`,
		`OriginationRateID`,
		`RateId`,
		`Rate`,
		`RateN`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`updated_at`,
		now() as `created_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		`RoutingCategoryID`,
		`Preference`,
		`Blocked`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM tblRateTableRate
	WHERE FIND_IN_SET(RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(TimezonesID,p_TimezonesIDs) != 0) AND EndDate <= NOW();


	DELETE  rtr
	FROM tblRateTableRate rtr
	inner join tblRateTableRateArchive rtra
		on rtr.RateTableRateID = rtra.RateTableRateID
	WHERE  FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0);


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableRateUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableRateUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTableRateId` LONGTEXT,
	IN `p_OriginationRateID` INT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Rate` DECIMAL(18,6),
	IN `p_RateN` DECIMAL(18,6),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_ConnectionFee` DECIMAL(18,6),
	IN `p_RoutingCategoryID` INT,
	IN `p_Preference` TEXT,
	IN `p_Blocked` TINYINT,
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` VARCHAR(50),
	IN `p_Critearea_Description` VARCHAR(200),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_OriginationDescription` VARCHAR(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_RoutingCategoryID` INT,
	IN `p_Critearea_Preference` TEXT,
	IN `p_Critearea_Blocked` TINYINT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		`RateTableRateId` int(11) NOT NULL,
		`OriginationRateID` int(11) NULL DEFAULT NULL,
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
		`RateN` decimal(18,6) NOT NULL DEFAULT '0.000000',
		`EffectiveDate` datetime NOT NULL,
		`EndDate` datetime DEFAULT NULL,
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		`CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`Interval1` int(11) DEFAULT NULL,
		`IntervalN` int(11) DEFAULT NULL,
		`ConnectionFee` decimal(18,6) DEFAULT NULL,
		`RoutingCategoryID` int(11) DEFAULT NULL,
		`Preference` int(11) DEFAULT NULL,
		`Blocked` tinyint NOT NULL DEFAULT 0,
		`ApprovedStatus` tinyint,
		`ApprovedBy` varchar(50),
		`ApprovedDate` datetime
	);

	INSERT INTO tmp_TempRateTableRate_
	SELECT
		rtr.RateTableRateId,
		IF(rtr.OriginationRateID=0,IFNULL(p_OriginationRateID,rtr.OriginationRateID),rtr.OriginationRateID) AS OriginationRateID,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IFNULL(p_Rate,rtr.Rate) AS Rate,
		IFNULL(p_RateN,rtr.RateN) AS RateN,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		IFNULL(p_Interval1,rtr.Interval1) AS Interval1,
		IFNULL(p_IntervalN,rtr.IntervalN) AS IntervalN,
		IFNULL(p_ConnectionFee,rtr.ConnectionFee) AS ConnectionFee,
		IF(p_RoutingCategoryID='',NULL,IFNULL(p_RoutingCategoryID,rtr.RoutingCategoryID)) AS RoutingCategoryID,
		IF(p_Preference='',NULL,IFNULL(p_Preference,rtr.Preference)) AS Preference,
		IFNULL(p_Blocked,rtr.Blocked) AS Blocked,
		rtr.ApprovedStatus AS ApprovedStatus,
		rtr.ApprovedBy,
		rtr.ApprovedDate
	FROM
		tblRateTableRate rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
   LEFT JOIN speakintelligentRouting.tblRoutingCategory AS RC
    	ON RC.RoutingCategoryID = rtr.RoutingCategoryID
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.OriginationRateID) NOT IN (
						SELECT
							RateID,OriginationRateID
						FROM
							tblRateTableRate
						WHERE
							EffectiveDate=p_EffectiveDate AND TimezonesID=p_TimezonesID AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTableRateID,p_RateTableRateID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableRateID,p_RateTableRateID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_OriginationDescription IS NULL) OR (p_Critearea_OriginationDescription IS NOT NULL AND r2.Description LIKE REPLACE(p_Critearea_OriginationDescription,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND
					(p_Critearea_RoutingCategoryID IS NULL OR RC.RoutingCategoryID = p_Critearea_RoutingCategoryID ) AND
					(p_Critearea_Preference IS NULL OR rtr.Preference = p_Critearea_Preference) AND
					(p_Critearea_Blocked IS NULL OR rtr.Blocked = p_Critearea_Blocked) AND
					(p_Critearea_ApprovedStatus IS NULL OR rtr.ApprovedStatus = p_Critearea_ApprovedStatus) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND rtr.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND rtr.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		rtr.RateTableId = p_RateTableId AND
		rtr.TimezonesID = p_TimezonesID;


	IF p_action = 1
	THEN
		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate_2 as (select * from tmp_TempRateTableRate_);
	      DELETE n1 FROM tmp_TempRateTableRate_ n1, tmp_TempRateTableRate_2 n2 WHERE n1.RateTableRateID < n2.RateTableRateID AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID;
      END IF;

		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
		DELETE
			temp
		FROM
			tmp_TempRateTableRate_ temp
		JOIN
			tblRateTableRate rtr ON rtr.RateTableRateID = temp.RateTableRateID
		WHERE
			(rtr.OriginationRateID = temp.OriginationRateID) AND
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			((rtr.Rate IS NULL && temp.Rate IS NULL) || rtr.Rate = temp.Rate) AND
			((rtr.RateN IS NULL && temp.RateN IS NULL) || rtr.RateN = temp.RateN) AND
			((rtr.ConnectionFee IS NULL && temp.ConnectionFee IS NULL) || rtr.ConnectionFee = temp.ConnectionFee) AND
			((rtr.Interval1 IS NULL && temp.Interval1 IS NULL) || rtr.Interval1 = temp.Interval1) AND
			((rtr.IntervalN IS NULL && temp.IntervalN IS NULL) || rtr.IntervalN = temp.IntervalN) AND
			((rtr.RoutingCategoryID IS NULL && temp.RoutingCategoryID IS NULL) || rtr.RoutingCategoryID = temp.RoutingCategoryID) AND
			((rtr.Preference IS NULL && temp.Preference IS NULL) || rtr.Preference = temp.Preference) AND
			((rtr.Blocked IS NULL && temp.Blocked IS NULL) || rtr.Blocked = temp.Blocked);

	END IF;




	UPDATE
		tblRateTableRate rtr
	INNER JOIN
		tmp_TempRateTableRate_ temp ON temp.RateTableRateID = rtr.RateTableRateID
	SET
		rtr.EndDate = NOW()
	WHERE
		temp.RateTableRateID = rtr.RateTableRateID;

	CALL prc_ArchiveOldRateTableRate(p_RateTableId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN

		INSERT INTO tblRateTableRate (
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			Rate,
			RateN,
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		)
		select
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			Rate,
			RateN,
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		from
			tmp_TempRateTableRate_;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTableDIDRateId` LONGTEXT,
	IN `p_OriginationRateID` INT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_OneOffCost` DECIMAL(18,6),
	IN `p_MonthlyCost` DECIMAL(18,6),
	IN `p_CostPerCall` DECIMAL(18,6),
	IN `p_CostPerMinute` DECIMAL(18,6),
	IN `p_SurchargePerCall` DECIMAL(18,6),
	IN `p_SurchargePerMinute` DECIMAL(18,6),
	IN `p_OutpaymentPerCall` DECIMAL(18,6),
	IN `p_OutpaymentPerMinute` DECIMAL(18,6),
	IN `p_Surcharges` DECIMAL(18,6),
	IN `p_Chargeback` DECIMAL(18,6),
	IN `p_CollectionCostAmount` DECIMAL(18,6),
	IN `p_CollectionCostPercentage` DECIMAL(18,6),
	IN `p_RegistrationCostPerNumber` DECIMAL(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Description` varchar(200),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_OriginationDescription` VARCHAR(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		`RateTableDIDRateId` int(11) NOT NULL,
		`OriginationRateID` int(11) NOT NULL,
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`OneOffCost` decimal(18,6) NULL DEFAULT NULL,
		`MonthlyCost` decimal(18,6) NULL DEFAULT NULL,
		`CostPerCall` decimal(18,6) NULL DEFAULT NULL,
		`CostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`SurchargePerCall` decimal(18,6) NULL DEFAULT NULL,
		`SurchargePerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OutpaymentPerCall` decimal(18,6) NULL DEFAULT NULL,
		`OutpaymentPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`Surcharges` decimal(18,6) NULL DEFAULT NULL,
		`Chargeback` decimal(18,6) NULL DEFAULT NULL,
		`CollectionCostAmount` decimal(18,6) NULL DEFAULT NULL,
		`CollectionCostPercentage` decimal(18,6) NULL DEFAULT NULL,
		`RegistrationCostPerNumber` decimal(18,6) NULL DEFAULT NULL,
		`EffectiveDate` datetime NOT NULL,
		`EndDate` datetime DEFAULT NULL,
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		`CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ApprovedStatus` tinyint,
		`ApprovedBy` varchar(50),
		`ApprovedDate` datetime
	);

	INSERT INTO tmp_TempRateTableDIDRate_
	SELECT
		rtr.RateTableDIDRateId,
		IF(rtr.OriginationRateID=0,IFNULL(p_OriginationRateID,rtr.OriginationRateID),rtr.OriginationRateID) AS OriginationRateID,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IFNULL(p_OneOffCost,rtr.OneOffCost) AS OneOffCost,
		IFNULL(p_MonthlyCost,rtr.MonthlyCost) AS MonthlyCost,
		IFNULL(p_CostPerCall,rtr.CostPerCall) AS CostPerCall,
		IFNULL(p_CostPerMinute,rtr.CostPerMinute) AS CostPerMinute,
		IFNULL(p_SurchargePerCall,rtr.SurchargePerCall) AS SurchargePerCall,
		IFNULL(p_SurchargePerMinute,rtr.SurchargePerMinute) AS SurchargePerMinute,
		IFNULL(p_OutpaymentPerCall,rtr.OutpaymentPerCall) AS OutpaymentPerCall,
		IFNULL(p_OutpaymentPerMinute,rtr.OutpaymentPerMinute) AS OutpaymentPerMinute,
		IFNULL(p_Surcharges,rtr.Surcharges) AS Surcharges,
		IFNULL(p_Chargeback,rtr.Chargeback) AS Chargeback,
		IFNULL(p_CollectionCostAmount,rtr.CollectionCostAmount) AS CollectionCostAmount,
		IFNULL(p_CollectionCostPercentage,rtr.CollectionCostPercentage) AS CollectionCostPercentage,
		IFNULL(p_RegistrationCostPerNumber,rtr.RegistrationCostPerNumber) AS RegistrationCostPerNumber,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		rtr.ApprovedStatus,
		rtr.ApprovedBy,
		rtr.ApprovedDate
	FROM
		tblRateTableDIDRate rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.OriginationRateID) NOT IN (
						SELECT
							RateID,OriginationRateID
						FROM
							tblRateTableDIDRate
						WHERE
							EffectiveDate=p_EffectiveDate AND TimezonesID=p_TimezonesID AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTableDIDRateID,p_RateTableDIDRateID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableDIDRateID,p_RateTableDIDRateID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_OriginationDescription IS NULL) OR (p_Critearea_OriginationDescription IS NOT NULL AND r2.Description LIKE REPLACE(p_Critearea_OriginationDescription,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND
					(p_Critearea_ApprovedStatus IS NULL OR rtr.ApprovedStatus = p_Critearea_ApprovedStatus) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND rtr.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND rtr.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		rtr.RateTableId = p_RateTableId AND
		rtr.TimezonesID = p_TimezonesID;


	IF p_action = 1
	THEN

		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 as (select * from tmp_TempRateTableDIDRate_);
			DELETE n1 FROM tmp_TempRateTableDIDRate_ n1, tmp_TempRateTableDIDRate_2 n2 WHERE n1.RateTableDIDRateID < n2.RateTableDIDRateID AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID;
		END IF;

		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
		DELETE
			temp
		FROM
			tmp_TempRateTableDIDRate_ temp
		JOIN
			tblRateTableDIDRate rtr ON rtr.RateTableDIDRateID = temp.RateTableDIDRateID
		WHERE
			(rtr.OriginationRateID = temp.OriginationRateID) AND
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			((rtr.OneOffCost IS NULL && temp.OneOffCost IS NULL) || rtr.OneOffCost = temp.OneOffCost) AND
			((rtr.MonthlyCost IS NULL && temp.MonthlyCost IS NULL) || rtr.MonthlyCost = temp.MonthlyCost) AND
			((rtr.CostPerCall IS NULL && temp.CostPerCall IS NULL) || rtr.CostPerCall = temp.CostPerCall) AND
			((rtr.CostPerMinute IS NULL && temp.CostPerMinute IS NULL) || rtr.CostPerMinute = temp.CostPerMinute) AND
			((rtr.SurchargePerCall IS NULL && temp.SurchargePerCall IS NULL) || rtr.SurchargePerCall = temp.SurchargePerCall) AND
			((rtr.SurchargePerMinute IS NULL && temp.SurchargePerMinute IS NULL) || rtr.SurchargePerMinute = temp.SurchargePerMinute) AND
			((rtr.OutpaymentPerCall IS NULL && temp.OutpaymentPerCall IS NULL) || rtr.OutpaymentPerCall = temp.OutpaymentPerCall) AND
			((rtr.OutpaymentPerMinute IS NULL && temp.OutpaymentPerMinute IS NULL) || rtr.OutpaymentPerMinute = temp.OutpaymentPerMinute) AND
			((rtr.Surcharges IS NULL && temp.Surcharges IS NULL) || rtr.Surcharges = temp.Surcharges) AND
			((rtr.Chargeback IS NULL && temp.Chargeback IS NULL) || rtr.Chargeback = temp.Chargeback) AND
			((rtr.CollectionCostAmount IS NULL && temp.CollectionCostAmount IS NULL) || rtr.CollectionCostAmount = temp.CollectionCostAmount) AND
			((rtr.CollectionCostPercentage IS NULL && temp.CollectionCostPercentage IS NULL) || rtr.CollectionCostPercentage = temp.CollectionCostPercentage) AND
			((rtr.RegistrationCostPerNumber IS NULL && temp.RegistrationCostPerNumber IS NULL) || rtr.RegistrationCostPerNumber = temp.RegistrationCostPerNumber);

	END IF;


	UPDATE
		tblRateTableDIDRate rtr
	INNER JOIN
		tmp_TempRateTableDIDRate_ temp ON temp.RateTableDIDRateID = rtr.RateTableDIDRateID
	SET
		rtr.EndDate = NOW()
	WHERE
		temp.RateTableDIDRateID = rtr.RateTableDIDRateID;

	CALL prc_ArchiveOldRateTableDIDRate(p_RateTableId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN

		INSERT INTO tblRateTableDIDRate (
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			OneOffCost,
			MonthlyCost,
			CostPerCall,
			CostPerMinute,
			SurchargePerCall,
			SurchargePerMinute,
			OutpaymentPerCall,
			OutpaymentPerMinute,
			Surcharges,
			Chargeback,
			CollectionCostAmount,
			CollectionCostPercentage,
			RegistrationCostPerNumber,
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		)
		select
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			OneOffCost,
			MonthlyCost,
			CostPerCall,
			CostPerMinute,
			SurchargePerCall,
			SurchargePerMinute,
			OutpaymentPerCall,
			OutpaymentPerMinute,
			Surcharges,
			Chargeback,
			CollectionCostAmount,
			CollectionCostPercentage,
			RegistrationCostPerNumber,
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		from
			tmp_TempRateTableDIDRate_;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableDIDRate`(
	IN `p_RateTableIds` LONGTEXT,
	IN `p_TimezonesIDs` LONGTEXT,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableDIDRate rtr
	INNER JOIN tblRateTableDIDRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND rtr2.OriginationRateID = rtr.OriginationRateID
	SET
		rtr.EndDate=NOW()
	WHERE
		(FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0) AND rtr.EffectiveDate <= NOW()) AND
		(FIND_IN_SET(rtr2.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr2.TimezonesID,p_TimezonesIDs) != 0) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableDIDRateID != rtr2.RateTableDIDRateID;


	INSERT INTO tblRateTableDIDRateArchive
	SELECT DISTINCT  null ,
		`RateTableDIDRateID`,
		`OriginationRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`OneOffCost`,
		`MonthlyCost`,
		`CostPerCall`,
		`CostPerMinute`,
		`SurchargePerCall`,
		`SurchargePerMinute`,
		`OutpaymentPerCall`,
		`OutpaymentPerMinute`,
		`Surcharges`,
		`Chargeback`,
		`CollectionCostAmount`,
		`CollectionCostPercentage`,
		`RegistrationCostPerNumber`,
		now() as `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM tblRateTableDIDRate
	WHERE FIND_IN_SET(RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(TimezonesID,p_TimezonesIDs) != 0) AND EndDate <= NOW();



	DELETE  rtr
	FROM tblRateTableDIDRate rtr
	inner join tblRateTableDIDRateArchive rtra
		on rtr.RateTableDIDRateID = rtra.RateTableDIDRateID
	WHERE  FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0);



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getDiscontinuedRateTableDIDRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedRateTableDIDRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_CountryID` INT,
	IN `p_origination_code` VARCHAR(50),
	IN `p_origination_description` VARCHAR(200),
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(200),
	IN `p_ApprovedStatus` TINYINT,
	IN `p_View` INT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_RateTableDIDRate_ (
		RateTableDIDRateID INT,
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		Code VARCHAR(50),
		Description VARCHAR(200),
		OneOffCost DECIMAL(18,6),
	  	MonthlyCost DECIMAL(18,6),
	  	CostPerCall DECIMAL(18,6),
	  	CostPerMinute DECIMAL(18,6),
	  	SurchargePerCall DECIMAL(18,6),
	  	SurchargePerMinute DECIMAL(18,6),
	  	OutpaymentPerCall DECIMAL(18,6),
	  	OutpaymentPerMinute DECIMAL(18,6),
	  	Surcharges DECIMAL(18,6),
	  	Chargeback DECIMAL(18,6),
	  	CollectionCostAmount DECIMAL(18,6),
	  	CollectionCostPercentage DECIMAL(18,6),
	  	RegistrationCostPerNumber DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		updated_by VARCHAR(50),
		OriginationRateID INT,
		RateID INT,
     	ApprovedStatus TINYINT,
     	ApprovedBy VARCHAR(50),
     	ApprovedDate DATETIME,
		INDEX tmp_RateTableDIDRate_RateID (`Code`)
	);

	INSERT INTO tmp_RateTableDIDRate_
	SELECT
		vra.RateTableDIDRateID,
		OriginationRate.Code AS OriginationCode,
		OriginationRate.Description AS OriginationDescription,
		r.Code,
		r.Description,
		vra.OneOffCost,
	  	vra.MonthlyCost,
	  	vra.CostPerCall,
	  	vra.CostPerMinute,
	 	vra.SurchargePerCall,
	  	vra.SurchargePerMinute,
	  	vra.OutpaymentPerCall,
	  	vra.OutpaymentPerMinute,
	  	vra.Surcharges,
	  	vra.Chargeback,
	  	vra.CollectionCostAmount,
	  	vra.CollectionCostPercentage,
	  	vra.RegistrationCostPerNumber,
		vra.EffectiveDate,
		vra.EndDate,
		vra.created_at AS updated_at,
		vra.CreatedBy AS updated_by,
		vra.OriginationRateID,
		vra.RateID,
      vra.ApprovedStatus,
      vra.ApprovedBy,
      vra.ApprovedDate
	FROM
		tblRateTableDIDRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
   LEFT JOIN
		tblRate AS OriginationRate ON OriginationRate.RateID = vra.OriginationRateID
	LEFT JOIN
		tblRateTableDIDRate vr ON vr.RateTableId = vra.RateTableId AND vr.RateId = vra.RateId AND vr.OriginationRateID = vra.OriginationRateID AND vr.TimezonesID = vra.TimezonesID
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		vra.TimezonesID = p_TimezonesID AND
		(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
		(p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%')) AND
		(p_origination_description is null OR OriginationRate.Description LIKE REPLACE(p_origination_description, '*', '%')) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
		(p_ApprovedStatus IS NULL OR vra.ApprovedStatus = p_ApprovedStatus) AND
		vr.RateTableDIDRateID is NULL;

	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate2_ as (select * from tmp_RateTableDIDRate_);
		DELETE
			n1
		FROM
			tmp_RateTableDIDRate_ n1, tmp_RateTableDIDRate2_ n2
		WHERE
			n1.Code = n2.Code AND (n1.OriginationCode = n2.OriginationCode OR (n1.OriginationCode IS NULL AND n2.OriginationCode IS NULL)) AND n1.RateTableDIDRateID < n2.RateTableDIDRateID;

		IF p_view = 1
		THEN
			SELECT
				RateTableDIDRateID AS ID,
				OriginationCode,
				OriginationDescription,
				Code,
				Description,
				OneOffCost,
			   MonthlyCost,
			   CostPerCall,
			   CostPerMinute,
			   SurchargePerCall,
			  	SurchargePerMinute,
			  	OutpaymentPerCall,
			  	OutpaymentPerMinute,
			  	Surcharges,
			  	Chargeback,
			  	CollectionCostAmount,
			  	CollectionCostPercentage,
			 	RegistrationCostPerNumber,
				EffectiveDate,
				EndDate,
				updated_at,
				updated_by,
        		RateTableDIDRateID,
				OriginationRateID,
				RateID,
		      ApprovedStatus,
		      ApprovedBy,
		      ApprovedDate
			FROM
				tmp_RateTableDIDRate_
			ORDER BY
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN OriginationDescription
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN OriginationDescription
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostDESC') THEN OneOffCost
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostASC') THEN OneOffCost
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostDESC') THEN MonthlyCost
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostASC') THEN MonthlyCost
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallDESC') THEN CostPerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallASC') THEN CostPerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteDESC') THEN CostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteASC') THEN CostPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallDESC') THEN SurchargePerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallASC') THEN SurchargePerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteDESC') THEN SurchargePerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteASC') THEN SurchargePerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallDESC') THEN OutpaymentPerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallASC') THEN OutpaymentPerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteDESC') THEN OutpaymentPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteASC') THEN OutpaymentPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesDESC') THEN Surcharges
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesASC') THEN Surcharges
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackDESC') THEN Chargeback
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackASC') THEN Chargeback
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountDESC') THEN CollectionCostAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountASC') THEN CollectionCostAmount
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageDESC') THEN CollectionCostPercentage
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageASC') THEN CollectionCostPercentage
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberDESC') THEN RegistrationCostPerNumber
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberASC') THEN RegistrationCostPerNumber
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN updated_by
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN updated_by
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableDIDRateIDDESC') THEN RateTableDIDRateID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableDIDRateIDASC') THEN RateTableDIDRateID
                END ASC
			LIMIT
				p_RowspPage
			OFFSET
				v_OffSet_;

			SELECT
				COUNT(code) AS totalcount
			FROM tmp_RateTableDIDRate_;

		ELSE

			SELECT
				GROUP_CONCAT(RateTableDIDRateID) AS ID,
				GROUP_CONCAT(OriginationCode) AS OriginationCode,
				OriginationDescription,
				GROUP_CONCAT(Code) AS Code,
				Description,
				MAX(OneOffCost),
				MAX(MonthlyCost),
				MAX(CostPerCall),
				MAX(CostPerMinute),
				MAX(SurchargePerCall),
				MAX(SurchargePerMinute),
				MAX(OutpaymentPerCall),
				MAX(OutpaymentPerMinute),
				MAX(Surcharges),
				MAX(Chargeback),
				MAX(CollectionCostAmount),
				MAX(CollectionCostPercentage),
				MAX(RegistrationCostPerNumber),
				EffectiveDate,
				EndDate,
				MAX(updated_at),
				MAX(updated_by),
				GROUP_CONCAT(RateTableDIDRateID) AS RateTableDIDRateID,
				GROUP_CONCAT(OriginationRateID) AS OriginationRateID,
				GROUP_CONCAT(RateID) AS RateID,
				ApprovedStatus,
				MAX(ApprovedBy) AS ApprovedBy,
				MAX(ApprovedDate) AS ApprovedDate
			FROM
				tmp_RateTableDIDRate_
			GROUP BY
				Description, OriginationDescription, EffectiveDate, EndDate, ApprovedStatus
			ORDER BY
       		 CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN MAX(OriginationCode)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN MAX(OriginationCode)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN MAX(OriginationDescription)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN MAX(OriginationDescription)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostDESC') THEN MAX(OneOffCost)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostASC') THEN MAX(OneOffCost)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostDESC') THEN MAX(MonthlyCost)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostASC') THEN MAX(MonthlyCost)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallDESC') THEN MAX(CostPerCall)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallASC') THEN MAX(CostPerCall)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteDESC') THEN MAX(CostPerMinute)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteASC') THEN MAX(CostPerMinute)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallDESC') THEN MAX(SurchargePerCall)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallASC') THEN MAX(SurchargePerCall)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteDESC') THEN MAX(SurchargePerMinute)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteASC') THEN MAX(SurchargePerMinute)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallDESC') THEN MAX(OutpaymentPerCall)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallASC') THEN MAX(OutpaymentPerCall)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteDESC') THEN MAX(OutpaymentPerMinute)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteASC') THEN MAX(OutpaymentPerMinute)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesDESC') THEN MAX(Surcharges)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesASC') THEN MAX(Surcharges)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackDESC') THEN MAX(Chargeback)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackASC') THEN MAX(Chargeback)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountDESC') THEN MAX(CollectionCostAmount)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountASC') THEN MAX(CollectionCostAmount)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageDESC') THEN MAX(CollectionCostPercentage)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageASC') THEN MAX(CollectionCostPercentage)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberDESC') THEN MAX(RegistrationCostPerNumber)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberASC') THEN MAX(RegistrationCostPerNumber)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN MAX(updated_at)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN MAX(updated_at)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN MAX(updated_by)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN MAX(updated_by)
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableDIDRateIDDESC') THEN MAX(RateTableDIDRateID)
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableDIDRateIDASC') THEN MAX(RateTableDIDRateID)
             END ASC
			LIMIT
				p_RowspPage
			OFFSET
				v_OffSet_;


			SELECT COUNT(*) AS `totalcount`
			FROM (
				SELECT
	            Description
	        	FROM tmp_RateTableDIDRate_
					GROUP BY Description, OriginationDescription, EffectiveDate, EndDate, ApprovedStatus
			) totalcount;

		END IF;

	END IF;

	-- basic view
	IF p_isExport = 10
	THEN
		SELECT
			OriginationCode,
			OriginationDescription,
			Code AS DestinationCode,
			Description AS DestinationDescription,
			OneOffCost,
			MonthlyCost,
			CostPerCall,
			CostPerMinute,
			SurchargePerCall,
			SurchargePerMinute,
			OutpaymentPerCall,
			OutpaymentPerMinute,
			Surcharges,
			Chargeback,
			CollectionCostAmount,
			CollectionCostPercentage,
			RegistrationCostPerNumber,
         ApprovedStatus
		FROM tmp_RateTableDIDRate_;
	END IF;

	-- advance view
	IF p_isExport = 10
	THEN
		SELECT
			OriginationCode,
			OriginationDescription,
			Code AS DestinationCode,
			Description AS DestinationDescription,
			OneOffCost,
			MonthlyCost,
			CostPerCall,
			CostPerMinute,
			SurchargePerCall,
			SurchargePerMinute,
			OutpaymentPerCall,
			OutpaymentPerMinute,
			Surcharges,
			Chargeback,
			CollectionCostAmount,
			CollectionCostPercentage,
			RegistrationCostPerNumber,
         CONCAT(updated_at,'\n',updated_by) AS `Modified Date/By`,
         CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
         ApprovedStatus
		FROM tmp_RateTableDIDRate_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getDiscontinuedRateTableRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedRateTableRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_CountryID` INT,
	IN `p_origination_code` VARCHAR(50),
	IN `p_origination_description` VARCHAR(200),
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(200),
	IN `p_RoutingCategoryID` INT,
	IN `p_Preference` TEXT,
	IN `p_Blocked` TINYINT,
	IN `p_ApprovedStatus` TINYINT,
	IN `p_View` INT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_RateTableRate_ (
		RateTableRateID INT,
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		Code VARCHAR(50),
		Description VARCHAR(200),
		Interval1 INT,
		IntervalN INT,
		ConnectionFee VARCHAR(50),
		PreviousRate DECIMAL(18, 6),
		Rate DECIMAL(18, 6),
		RateN DECIMAL(18, 6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		updated_by VARCHAR(50),
		OriginationRateID INT,
		RateID INT,
     	RoutingCategoryID INT,
     	RoutingCategoryName VARCHAR(50),
     	Preference INT,
     	Blocked TINYINT,
     	ApprovedStatus TINYINT,
     	ApprovedBy VARCHAR(50),
     	ApprovedDate DATETIME,
		INDEX tmp_RateTableRate_RateID (`Code`)
	);

	INSERT INTO tmp_RateTableRate_
	SELECT
		vra.RateTableRateID,
		OriginationRate.Code AS OriginationCode,
		OriginationRate.Description AS OriginationDescription,
		r.Code,
		r.Description,
		CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
		'' AS ConnectionFee,
		null AS PreviousRate,
		vra.Rate,
		vra.RateN,
		vra.EffectiveDate,
		vra.EndDate,
		vra.created_at AS updated_at,
		vra.created_by AS updated_by,
		vra.OriginationRateID,
		vra.RateID,
     	vra.RoutingCategoryID,
     	RC.Name AS RoutingCategoryName,
      vra.Preference,
      vra.Blocked,
      vra.ApprovedStatus,
      vra.ApprovedBy,
      vra.ApprovedDate
	FROM
		tblRateTableRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
   LEFT JOIN
		tblRate AS OriginationRate ON OriginationRate.RateID = vra.OriginationRateID
	LEFT JOIN
		tblRateTableRate vr ON vr.RateTableId = vra.RateTableId AND vr.RateId = vra.RateId AND vr.OriginationRateID = vra.OriginationRateID AND vr.TimezonesID = vra.TimezonesID
   LEFT JOIN speakintelligentRouting.tblRoutingCategory AS RC
    	ON RC.RoutingCategoryID = vra.RoutingCategoryID
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		vra.TimezonesID = p_TimezonesID AND
		(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
		(p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%')) AND
		(p_origination_description is null OR OriginationRate.Description LIKE REPLACE(p_origination_description, '*', '%')) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
		(p_RoutingCategoryID IS NULL OR RC.RoutingCategoryID = p_RoutingCategoryID ) AND
		(p_Preference IS NULL OR vra.Preference = p_Preference) AND
		(p_Blocked IS NULL OR vra.Blocked = p_Blocked) AND
		(p_ApprovedStatus IS NULL OR vra.ApprovedStatus = p_ApprovedStatus) AND
		vr.RateTableRateID is NULL;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_ as (select * from tmp_RateTableRate_);
	DELETE
		n1
	FROM
		tmp_RateTableRate_ n1, tmp_RateTableRate2_ n2
	WHERE
		n1.Code = n2.Code AND (n1.OriginationCode = n2.OriginationCode OR (n1.OriginationCode IS NULL AND n2.OriginationCode IS NULL)) AND n1.RateTableRateID < n2.RateTableRateID;

	IF p_isExport = 0
	THEN
		IF p_view = 1
		THEN
			SELECT
				RateTableRateID AS ID,
				OriginationCode,
				OriginationDescription,
				Code,
				Description,
				Interval1,
				IntervalN,
				ConnectionFee,
				PreviousRate,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
				updated_at,
				updated_by,
        		RateTableRateID,
				OriginationRateID,
				RateID,
				RoutingCategoryID,
				RoutingCategoryName,
				Preference,
				Blocked,
		      ApprovedStatus,
		      ApprovedBy,
		      ApprovedDate
			FROM
				tmp_RateTableRate_
			ORDER BY
				CASE
              	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
          	END DESC,
          	CASE
              	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
          	END ASC,
	        	CASE
	           	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN OriginationDescription
	        	END DESC,
	        	CASE
	           	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN OriginationDescription
	        	END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNDESC') THEN RateN
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNASC') THEN RateN
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN IntervalN
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN IntervalN
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN updated_by
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN updated_by
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN RateTableRateID
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN RateTableRateID
				END ASC
			LIMIT
				p_RowspPage
			OFFSET
				v_OffSet_;

			SELECT
				COUNT(code) AS totalcount
			FROM tmp_RateTableRate_;

		ELSE

			SELECT
				group_concat(RateTableRateID) AS ID,
				group_concat(OriginationCode) AS OriginationCode,
				OriginationDescription,
				group_concat(Code) AS Code,
				Description,
				ConnectionFee,
				Interval1,
				IntervalN,
				ANY_VALUE(PreviousRate),
				Rate,
				ANY_VALUE(RateN) AS RateN,
				EffectiveDate,
				EndDate,
				MAX(updated_at),
				MAX(updated_by),
				GROUP_CONCAT(RateTableRateID) AS RateTableRateID,
				GROUP_CONCAT(OriginationRateID) AS OriginationRateID,
				GROUP_CONCAT(RateID) AS RateID,
				MAX(RoutingCategoryID) AS RoutingCategoryID,
				MAX(RoutingCategoryName) AS RoutingCategoryName,
				MAX(Preference) AS Preference,
				MAX(Blocked) AS Blocked,
				ApprovedStatus,
				MAX(ApprovedBy) AS ApprovedBy,
				MAX(ApprovedDate) AS ApprovedDate
			FROM
				tmp_RateTableRate_
			GROUP BY
				Description, OriginationDescription, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate, ApprovedStatus
			ORDER BY
          	CASE
              	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN ANY_VALUE(OriginationCode)
          	END DESC,
          	CASE
              	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN ANY_VALUE(OriginationCode)
          	END ASC,
          	CASE
              	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN ANY_VALUE(OriginationDescription)
          	END DESC,
          	CASE
              	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN ANY_VALUE(OriginationDescription)
          	END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN ANY_VALUE(Description)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN ANY_VALUE(Description)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN ANY_VALUE(Rate)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN ANY_VALUE(Rate)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNDESC') THEN ANY_VALUE(RateN)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNASC') THEN ANY_VALUE(RateN)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ANY_VALUE(ConnectionFee)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ANY_VALUE(ConnectionFee)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN ANY_VALUE(Interval1)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN ANY_VALUE(Interval1)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN ANY_VALUE(IntervalN)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN ANY_VALUE(IntervalN)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN ANY_VALUE(EffectiveDate)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN ANY_VALUE(EffectiveDate)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN ANY_VALUE(EndDate)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN ANY_VALUE(EndDate)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN ANY_VALUE(updated_at)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN ANY_VALUE(updated_at)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ANY_VALUE(updated_by)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ANY_VALUE(updated_by)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN ANY_VALUE(RateTableRateID)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN ANY_VALUE(RateTableRateID)
				END ASC
			LIMIT
				p_RowspPage
			OFFSET
				v_OffSet_;


			SELECT COUNT(*) AS `totalcount`
			FROM (
				SELECT
	            Description
	        	FROM tmp_RateTableRate_
					GROUP BY Description, OriginationDescription, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate, ApprovedStatus
			) totalcount;

		END IF;

	END IF;

	-- basic view
   IF p_isExport = 10
   THEN
      SELECT
         OriginationCode,
         OriginationDescription,
         Code AS DestinationCode,
         Description AS DestinationDescription,
         CONCAT(Interval1,'/',IntervalN) AS `Interval1/N`,
         ConnectionFee,
         Rate,
         RateN,
         EffectiveDate,
         RoutingCategoryName,
         Preference,
         Blocked,
         ApprovedStatus
      FROM   tmp_RateTableRate_;
   END IF;

 	-- advance view
 	IF p_isExport = 11
 	THEN
      SELECT
         OriginationCode,
         OriginationDescription,
         Code AS DestinationCode,
         Description AS DestinationDescription,
         CONCAT(Interval1,'/',IntervalN) AS `Interval1/N`,
         ConnectionFee,
         PreviousRate,
         Rate,
         RateN,
         EffectiveDate,
         CONCAT(updated_at,'\n',updated_by) AS `Modified Date/By`,
         RoutingCategoryName,
         Preference,
         CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
         Blocked,
         ApprovedStatus
      FROM   tmp_RateTableRate_;
   END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableDIDRate`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_contryID` INT,
	IN `p_origination_code` VARCHAR(50),
	IN `p_origination_description` VARCHAR(50),
	IN `p_code` VARCHAR(50),
	IN `p_description` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
	IN `p_view` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET SESSION GROUP_CONCAT_MAX_LEN = 1000000;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_;
   CREATE TEMPORARY TABLE tmp_RateTableDIDRate_ (
        ID INT,
        OriginationCode VARCHAR(50),
        OriginationDescription VARCHAR(200),
        Code VARCHAR(50),
        Description VARCHAR(200),
        OneOffCost DECIMAL(18,6),
		  MonthlyCost DECIMAL(18,6),
		  CostPerCall DECIMAL(18,6),
		  CostPerMinute DECIMAL(18,6),
		  SurchargePerCall DECIMAL(18,6),
		  SurchargePerMinute DECIMAL(18,6),
		  OutpaymentPerCall DECIMAL(18,6),
		  OutpaymentPerMinute DECIMAL(18,6),
		  Surcharges DECIMAL(18,6),
		  Chargeback DECIMAL(18,6),
		  CollectionCostAmount DECIMAL(18,6),
		  CollectionCostPercentage DECIMAL(18,6),
		  RegistrationCostPerNumber DECIMAL(18,6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50),
        RateTableDIDRateID INT,
        OriginationRateID INT,
        RateID INT,
        ApprovedStatus TINYINT,
        ApprovedBy VARCHAR(50),
        ApprovedDate DATETIME,
        INDEX tmp_RateTableDIDRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTableDIDRate_
    SELECT
        RateTableDIDRateID AS ID,
        OriginationRate.Code AS OriginationCode,
        OriginationRate.Description AS OriginationDescription,
        tblRate.Code,
        tblRate.Description,
        OneOffCost,
		  MonthlyCost,
		  CostPerCall,
		  CostPerMinute,
		  SurchargePerCall,
		  SurchargePerMinute,
		  OutpaymentPerCall,
		  OutpaymentPerMinute,
		  Surcharges,
		  Chargeback,
		  CollectionCostAmount,
		  CollectionCostPercentage,
		  RegistrationCostPerNumber,
        IFNULL(tblRateTableDIDRate.EffectiveDate, NOW()) as EffectiveDate,
        tblRateTableDIDRate.EndDate,
        tblRateTableDIDRate.updated_at,
        tblRateTableDIDRate.ModifiedBy,
        RateTableDIDRateID,
        OriginationRate.RateID AS OriginationRateID,
        tblRate.RateID,
        tblRateTableDIDRate.ApprovedStatus,
        tblRateTableDIDRate.ApprovedBy,
        tblRateTableDIDRate.ApprovedDate
    FROM tblRate
    LEFT JOIN tblRateTableDIDRate
        ON tblRateTableDIDRate.RateID = tblRate.RateID
        AND tblRateTableDIDRate.RateTableId = p_RateTableId
    LEFT JOIN tblRate AS OriginationRate
    	  ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
    WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID is null OR tblRate.CountryID = p_contryID)
		AND (p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%'))
		AND (p_origination_description is null OR OriginationRate.Description LIKE REPLACE(p_origination_description, '*', '%'))
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_description is null OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
		AND (p_ApprovedStatus IS NULL OR tblRateTableDIDRate.ApprovedStatus = p_ApprovedStatus)
		AND TrunkID = p_trunkID
		AND tblRateTableDIDRate.TimezonesID = p_TimezonesID
		AND (
				p_effective = 'All'
				OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
				OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	  IF p_effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate4_ as (select * from tmp_RateTableDIDRate_);
         DELETE n1 FROM tmp_RateTableDIDRate_ n1, tmp_RateTableDIDRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID;
		END IF;


    IF p_isExport = 0
    THEN

		IF p_view = 1
		THEN
       	SELECT * FROM tmp_RateTableDIDRate_
					ORDER BY CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN OriginationDescription
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN OriginationDescription
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostDESC') THEN OneOffCost
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostASC') THEN OneOffCost
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostDESC') THEN MonthlyCost
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostASC') THEN MonthlyCost
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallDESC') THEN CostPerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallASC') THEN CostPerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteDESC') THEN CostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteASC') THEN CostPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallDESC') THEN SurchargePerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallASC') THEN SurchargePerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteDESC') THEN SurchargePerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteASC') THEN SurchargePerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallDESC') THEN OutpaymentPerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallASC') THEN OutpaymentPerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteDESC') THEN OutpaymentPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteASC') THEN OutpaymentPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesDESC') THEN Surcharges
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesASC') THEN Surcharges
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackDESC') THEN Chargeback
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackASC') THEN Chargeback
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountDESC') THEN CollectionCostAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountASC') THEN CollectionCostAmount
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageDESC') THEN CollectionCostPercentage
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageASC') THEN CollectionCostPercentage
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberDESC') THEN RegistrationCostPerNumber
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberASC') THEN RegistrationCostPerNumber
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableDIDRateIDDESC') THEN RateTableDIDRateID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableDIDRateIDASC') THEN RateTableDIDRateID
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTableDIDRate_;

		ELSE
			SELECT group_concat(ID) AS ID,group_concat(OriginationCode) AS OriginationCode,OriginationDescription,group_concat(Code) AS Code,Description,MAX(OneOffCost),MAX(MonthlyCost),MAX(CostPerCall),MAX(CostPerMinute),MAX(SurchargePerCall),MAX(SurchargePerMinute),MAX(OutpaymentPerCall),MAX(OutpaymentPerMinute),MAX(Surcharges),MAX(Chargeback),MAX(CollectionCostAmount),MAX(CollectionCostPercentage),MAX(RegistrationCostPerNumber),MAX(EffectiveDate),MAX(EndDate),MAX(updated_at) AS updated_at,MAX(ModifiedBy) AS ModifiedBy,group_concat(ID) AS RateTableDIDRateID,group_concat(OriginationRateID) AS OriginationRateID, group_concat(RateID) AS RateID, ApprovedStatus, MAX(ApprovedBy) AS ApprovedBy, MAX(ApprovedDate) AS ApprovedDate FROM tmp_RateTableDIDRate_
					GROUP BY Description, OriginationDescription, EffectiveDate, ApprovedStatus
					ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN MAX(OriginationCode)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN MAX(OriginationCode)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN MAX(OriginationDescription)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN MAX(OriginationDescription)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostDESC') THEN MAX(OneOffCost)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostASC') THEN MAX(OneOffCost)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostDESC') THEN MAX(MonthlyCost)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostASC') THEN MAX(MonthlyCost)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallDESC') THEN MAX(CostPerCall)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallASC') THEN MAX(CostPerCall)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteDESC') THEN MAX(CostPerMinute)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteASC') THEN MAX(CostPerMinute)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallDESC') THEN MAX(SurchargePerCall)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallASC') THEN MAX(SurchargePerCall)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteDESC') THEN MAX(SurchargePerMinute)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteASC') THEN MAX(SurchargePerMinute)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallDESC') THEN MAX(OutpaymentPerCall)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallASC') THEN MAX(OutpaymentPerCall)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteDESC') THEN MAX(OutpaymentPerMinute)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteASC') THEN MAX(OutpaymentPerMinute)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesDESC') THEN MAX(Surcharges)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesASC') THEN MAX(Surcharges)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackDESC') THEN MAX(Chargeback)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackASC') THEN MAX(Chargeback)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountDESC') THEN MAX(CollectionCostAmount)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountASC') THEN MAX(CollectionCostAmount)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageDESC') THEN MAX(CollectionCostPercentage)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageASC') THEN MAX(CollectionCostPercentage)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberDESC') THEN MAX(RegistrationCostPerNumber)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberASC') THEN MAX(RegistrationCostPerNumber)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN MAX(EndDate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN MAX(EndDate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN MAX(updated_at)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN MAX(updated_at)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN MAX(ModifiedBy)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN MAX(ModifiedBy)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableDIDRateIDDESC') THEN MAX(RateTableDIDRateID)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableDIDRateIDASC') THEN MAX(RateTableDIDRateID)
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT COUNT(*) AS `totalcount`
			FROM (
				SELECT
	            Description
	        	FROM tmp_RateTableDIDRate_
					GROUP BY Description, OriginationDescription, EffectiveDate, ApprovedStatus
			) totalcount;


		END IF;

    END IF;

	 -- basic view
    IF p_isExport = 10
    THEN
        SELECT
            OriginationCode,
            OriginationDescription,
            Code AS DestinationCode,
            Description AS DestinationDescription,
            OneOffCost,
				MonthlyCost,
				CostPerCall,
				CostPerMinute,
				SurchargePerCall,
				SurchargePerMinute,
				OutpaymentPerCall,
				OutpaymentPerMinute,
				Surcharges,
				Chargeback,
				CollectionCostAmount,
				CollectionCostPercentage,
				RegistrationCostPerNumber,
            EffectiveDate,
            ApprovedStatus
        FROM
		  		tmp_RateTableDIDRate_;
    END IF;

	 -- advance view
    IF p_isExport = 11
    THEN
        SELECT
            OriginationCode,
            OriginationDescription,
            Code AS DestinationCode,
            Description AS DestinationDescription,
            OneOffCost,
				MonthlyCost,
				CostPerCall,
				CostPerMinute,
				SurchargePerCall,
				SurchargePerMinute,
				OutpaymentPerCall,
				OutpaymentPerMinute,
				Surcharges,
				Chargeback,
				CollectionCostAmount,
				CollectionCostPercentage,
				RegistrationCostPerNumber,
            EffectiveDate,
            CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
            CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
            ApprovedStatus
        FROM
		  		tmp_RateTableDIDRate_;
    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableDIDRatesArchiveGrid`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableDIDRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_RateID` LONGTEXT,
	IN `p_OriginationRateID` LONGTEXT,
	IN `p_View` INT
)
BEGIN

	SET SESSION GROUP_CONCAT_MAX_LEN = 1000000;

	CALL prc_SplitAndInsertRateIDs(p_RateID,p_OriginationRateID);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        OriginationCode VARCHAR(50),
        OriginationDescription VARCHAR(200),
        Code VARCHAR(50),
        Description VARCHAR(200),
        OneOffCost DECIMAL(18,6),
		  MonthlyCost DECIMAL(18,6),
		  CostPerCall DECIMAL(18,6),
		  CostPerMinute DECIMAL(18,6),
		  SurchargePerCall DECIMAL(18,6),
		  SurchargePerMinute DECIMAL(18,6),
		  OutpaymentPerCall DECIMAL(18,6),
		  OutpaymentPerMinute DECIMAL(18,6),
		  Surcharges DECIMAL(18,6),
		  Chargeback DECIMAL(18,6),
		  CollectionCostAmount DECIMAL(18,6),
		  CollectionCostPercentage DECIMAL(18,6),
		  RegistrationCostPerNumber DECIMAL(18,6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50)
   );

	IF p_View = 1
	THEN
		INSERT INTO tmp_RateTableRate_ (
			OriginationCode,
		  	OriginationDescription,
			Code,
		  	Description,
		  	OneOffCost,
		   MonthlyCost,
		   CostPerCall,
		   CostPerMinute,
		   SurchargePerCall,
		  	SurchargePerMinute,
		  	OutpaymentPerCall,
		  	OutpaymentPerMinute,
		  	Surcharges,
		  	Chargeback,
		  	CollectionCostAmount,
		  	CollectionCostPercentage,
		 	RegistrationCostPerNumber,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy
		)
	   SELECT
			o_r.Code AS OriginationCode,
			o_r.Description AS OriginationDescription,
			r.Code,
			r.Description,
			vra.OneOffCost,
		  	vra.MonthlyCost,
		  	vra.CostPerCall,
		  	vra.CostPerMinute,
		 	vra.SurchargePerCall,
		  	vra.SurchargePerMinute,
		  	vra.OutpaymentPerCall,
		  	vra.OutpaymentPerMinute,
		  	vra.Surcharges,
		  	vra.Chargeback,
		  	vra.CollectionCostAmount,
		  	vra.CollectionCostPercentage,
		  	vra.RegistrationCostPerNumber,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(vra.created_at,'') AS ModifiedDate,
			IFNULL(vra.CreatedBy,'') AS ModifiedBy
		FROM
			tblRateTableDIDRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblRate o_r ON o_r.RateID=vra.OriginationRateID
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.RateTableId = p_RateTableID AND
			vra.TimezonesID = p_TimezonesID AND
			(
				(vra.RateID, vra.OriginationRateID) IN (
					SELECT RateID,OriginationRateID FROM temp_rateids_
				)
			)
		ORDER BY
			vra.EffectiveDate DESC, vra.created_at DESC;
	ELSE
		INSERT INTO tmp_RateTableRate_ (
			OriginationCode,
		  	OriginationDescription,
			Code,
		  	Description,
		  	OneOffCost,
		   MonthlyCost,
		   CostPerCall,
		   CostPerMinute,
		   SurchargePerCall,
		  	SurchargePerMinute,
		  	OutpaymentPerCall,
		  	OutpaymentPerMinute,
		  	Surcharges,
		  	Chargeback,
		  	CollectionCostAmount,
		  	CollectionCostPercentage,
		 	RegistrationCostPerNumber,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy
		)
	   SELECT
			GROUP_CONCAT(DISTINCT o_r.Code) AS OriginationCode,
			MAX(o_r.Description) AS OriginationDescription,
			GROUP_CONCAT(r.Code),
			r.Description,
			MAX(vra.OneOffCost),
		  	MAX(vra.MonthlyCost),
		  	MAX(vra.CostPerCall),
		  	MAX(vra.CostPerMinute),
		 	MAX(vra.SurchargePerCall),
		  	MAX(vra.SurchargePerMinute),
		  	MAX(vra.OutpaymentPerCall),
		  	MAX(vra.OutpaymentPerMinute),
		  	MAX(vra.Surcharges),
		  	MAX(vra.Chargeback),
		  	MAX(vra.CollectionCostAmount),
		  	MAX(vra.CollectionCostPercentage),
		  	MAX(vra.RegistrationCostPerNumber),
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(MAX(vra.created_at),'') AS ModifiedDate,
			IFNULL(MAX(vra.CreatedBy),'') AS ModifiedBy
		FROM
			tblRateTableDIDRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblRate o_r ON o_r.RateID=vra.OriginationRateID
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.RateTableId = p_RateTableID AND
			vra.TimezonesID = p_TimezonesID AND
			(
				(vra.RateID, vra.OriginationRateID) IN (
					SELECT RateID,OriginationRateID FROM temp_rateids_
				)
			)
		GROUP BY
			Description, EffectiveDate, EndDate
		ORDER BY
			vra.EffectiveDate DESC, MAX(vra.created_at) DESC;
	END IF;

	SELECT
		OriginationCode,
	  	OriginationDescription,
		Code,
		Description,
		OneOffCost,
	   MonthlyCost,
	   CostPerCall,
	   CostPerMinute,
	   SurchargePerCall,
	  	SurchargePerMinute,
	  	OutpaymentPerCall,
	  	OutpaymentPerMinute,
	  	Surcharges,
	  	Chargeback,
	  	CollectionCostAmount,
	  	CollectionCostPercentage,
	 	RegistrationCostPerNumber,
		EffectiveDate,
		EndDate,
		IFNULL(updated_at,'') AS ModifiedDate,
		IFNULL(ModifiedBy,'') AS ModifiedBy
	FROM tmp_RateTableRate_;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableRate`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_contryID` INT,
	IN `p_origination_code` VARCHAR(50),
	IN `p_origination_description` VARCHAR(50),
	IN `p_code` VARCHAR(50),
	IN `p_description` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_RoutingCategoryID` INT,
	IN `p_Preference` TEXT,
	IN `p_Blocked` TINYINT,
	IN `p_ApprovedStatus` TINYINT,
	IN `p_view` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET SESSION GROUP_CONCAT_MAX_LEN = 1000000;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        ID INT,
        OriginationCode VARCHAR(50),
        OriginationDescription VARCHAR(200),
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
		  ConnectionFee DECIMAL(18, 6),
        PreviousRate DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        RateN DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50),
        RateTableRateID INT,
        OriginationRateID INT,
        RateID INT,
        RoutingCategoryID INT,
        RoutingCategoryName VARCHAR(50),
        Preference INT,
        Blocked TINYINT,
        ApprovedStatus TINYINT,
        ApprovedBy VARCHAR(50),
        ApprovedDate DATETIME,
        INDEX tmp_RateTableRate_RateID (`RateID`)
    );



    INSERT INTO tmp_RateTableRate_
    SELECT
        RateTableRateID AS ID,
        OriginationRate.Code AS OriginationCode,
        OriginationRate.Description AS OriginationDescription,
        tblRate.Code,
        tblRate.Description,
        ifnull(tblRateTableRate.Interval1,1) as Interval1,
        ifnull(tblRateTableRate.IntervalN,1) as IntervalN,
		  tblRateTableRate.ConnectionFee,
        null as PreviousRate,
        IFNULL(tblRateTableRate.Rate, 0) as Rate,
        IFNULL(tblRateTableRate.RateN, 0) as RateN,
        IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
        tblRateTableRate.EndDate,
        tblRateTableRate.updated_at,
        tblRateTableRate.ModifiedBy,
        RateTableRateID,
        OriginationRate.RateID AS OriginationRateID,
        tblRate.RateID,
        tblRateTableRate.RoutingCategoryID,
        RC.Name AS RoutingCategoryName,
        tblRateTableRate.Preference,
        tblRateTableRate.Blocked,
        tblRateTableRate.ApprovedStatus,
        tblRateTableRate.ApprovedBy,
        tblRateTableRate.ApprovedDate
    FROM tblRate
    LEFT JOIN tblRateTableRate
        ON tblRateTableRate.RateID = tblRate.RateID
        AND tblRateTableRate.RateTableId = p_RateTableId
    LEFT JOIN tblRate AS OriginationRate
    	  ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
    LEFT JOIN speakintelligentRouting.tblRoutingCategory AS RC
    	  ON RC.RoutingCategoryID = tblRateTableRate.RoutingCategoryID
    WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
		AND (p_origination_code IS NULL OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%'))
		AND (p_origination_description IS NULL OR OriginationRate.Description LIKE REPLACE(p_origination_description, '*', '%'))
		AND (p_code IS NULL OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
		AND (p_RoutingCategoryID IS NULL OR RC.RoutingCategoryID = p_RoutingCategoryID)
		AND (p_Preference IS NULL OR tblRateTableRate.Preference = p_Preference)
		AND (p_Blocked IS NULL OR tblRateTableRate.Blocked = p_Blocked)
		AND (p_ApprovedStatus IS NULL OR tblRateTableRate.ApprovedStatus = p_ApprovedStatus)
		AND TrunkID = p_trunkID
		AND tblRateTableRate.TimezonesID = p_TimezonesID
		AND (
			p_effective = 'All'
		OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
		OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	  IF p_effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
         DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID;
		END IF;


	UPDATE
		tmp_RateTableRate_ tr
	SET
		PreviousRate = (SELECT Rate FROM tblRateTableRate WHERE RateTableID=p_RateTableId AND TimezonesID = p_TimezonesID AND RateID=tr.RateID AND OriginationRateID=tr.OriginationRateID AND Code=tr.Code AND EffectiveDate<tr.EffectiveDate ORDER BY EffectiveDate DESC,RateTableRateID DESC LIMIT 1);

	UPDATE
		tmp_RateTableRate_ tr
	SET
		PreviousRate = (SELECT Rate FROM tblRateTableRateArchive WHERE RateTableID=p_RateTableId AND TimezonesID = p_TimezonesID AND RateID=tr.RateID AND OriginationRateID=tr.OriginationRateID AND Code=tr.Code AND EffectiveDate<tr.EffectiveDate ORDER BY EffectiveDate DESC,RateTableRateID DESC LIMIT 1)
	WHERE
		PreviousRate is null;

    IF p_isExport = 0
    THEN

		IF p_view = 1
		THEN
       	SELECT * FROM tmp_RateTableRate_
					ORDER BY CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN OriginationDescription
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN OriginationDescription
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreviousRateDESC') THEN PreviousRate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreviousRateASC') THEN PreviousRate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNDESC') THEN RateN
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNASC') THEN RateN
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN RateTableRateID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN RateTableRateID
                END ASC,
				    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTableRate_;

		ELSE
			SELECT group_concat(ID) AS ID,group_concat(OriginationCode) AS OriginationCode,OriginationDescription,group_concat(Code) AS Code,MAX(Description),MAX(Interval1),MAX(Intervaln),MAX(ConnectionFee),MAX(PreviousRate),MAX(Rate),MAX(RateN),MAX(EffectiveDate),MAX(EndDate),MAX(updated_at) AS updated_at,MAX(ModifiedBy) AS ModifiedBy,group_concat(ID) AS RateTableRateID,group_concat(OriginationRateID) AS OriginationRateID,group_concat(RateID) AS RateID, MAX(RoutingCategoryID) AS RoutingCategoryID, MAX(RoutingCategoryName) AS RoutingCategoryName, MAX(Preference) AS Preference, MAX(Blocked) AS Blocked, ApprovedStatus, MAX(ApprovedBy) AS ApprovedBy, MAX(ApprovedDate) AS ApprovedDate FROM tmp_RateTableRate_
					GROUP BY Description, OriginationDescription, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, ApprovedStatus
					ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN ANY_VALUE(OriginationCode)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN ANY_VALUE(OriginationCode)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN ANY_VALUE(OriginationDescription)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN ANY_VALUE(OriginationDescription)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN ANY_VALUE(Description)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN ANY_VALUE(Description)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreviousRateDESC') THEN ANY_VALUE(PreviousRate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreviousRateASC') THEN ANY_VALUE(PreviousRate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN ANY_VALUE(Rate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN ANY_VALUE(Rate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNDESC') THEN ANY_VALUE(RateN)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNASC') THEN ANY_VALUE(RateN)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN ANY_VALUE(Interval1)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN ANY_VALUE(Interval1)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN ANY_VALUE(Interval1)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN ANY_VALUE(Interval1)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN ANY_VALUE(EffectiveDate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN ANY_VALUE(EffectiveDate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN ANY_VALUE(EndDate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN ANY_VALUE(EndDate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN ANY_VALUE(updated_at)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN ANY_VALUE(updated_at)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ANY_VALUE(ModifiedBy)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ANY_VALUE(ModifiedBy)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN ANY_VALUE(RateTableRateID)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN ANY_VALUE(RateTableRateID)
                END ASC,
				    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ANY_VALUE(ConnectionFee)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ANY_VALUE(ConnectionFee)
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT COUNT(*) AS `totalcount`
			FROM (
				SELECT
	            Description
	        	FROM tmp_RateTableRate_
					GROUP BY Description, OriginationDescription, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, ApprovedStatus
			) totalcount;


		END IF;

    END IF;

	 -- basic view
    IF p_isExport = 10
    THEN
        SELECT
            OriginationCode,
            OriginationDescription,
            Code AS DestinationCode,
            Description AS DestinationDescription,
            CONCAT(Interval1,'/',IntervalN) AS `Interval1/N`,
            ConnectionFee,
            Rate,
            RateN,
            EffectiveDate,
            RoutingCategoryName,
            Preference,
            Blocked,
            ApprovedStatus
        FROM   tmp_RateTableRate_;
    END IF;

	 -- advance view
    IF p_isExport = 11
    THEN
        SELECT
            OriginationCode,
            OriginationDescription,
            Code AS DestinationCode,
            Description AS DestinationDescription,
            CONCAT(Interval1,'/',IntervalN) AS `Interval1/N`,
            ConnectionFee,
            PreviousRate,
            Rate,
            RateN,
            EffectiveDate,
            CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
            RoutingCategoryName,
            Preference,
            CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
            Blocked,
            ApprovedStatus
        FROM   tmp_RateTableRate_;
    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableRatesArchiveGrid`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_RateID` LONGTEXT,
	IN `p_OriginationRateID` LONGTEXT,
	IN `p_View` INT
)
BEGIN

	SET SESSION GROUP_CONCAT_MAX_LEN = 1000000;

	CALL prc_SplitAndInsertRateIDs(p_RateID,p_OriginationRateID);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        OriginationCode VARCHAR(50),
        OriginationDescription VARCHAR(200),
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
		  ConnectionFee VARCHAR(50),
        PreviousRate DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        RateN DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50),
        RoutingCategoryName VARCHAR(50),
        Preference INT,
        Blocked TINYINT
   );

	IF p_View = 1
	THEN
		INSERT INTO tmp_RateTableRate_ (
			OriginationCode,
		  	OriginationDescription,
			Code,
		  	Description,
		  	Interval1,
		  	IntervalN,
		  	ConnectionFee,
		  	Rate,
		  	RateN,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy,
		  	RoutingCategoryName,
        	Preference,
        	Blocked
		)
	   SELECT
			o_r.Code AS OriginationCode,
			o_r.Description AS OriginationDescription,
			r.Code,
			r.Description,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
			vra.Rate,
			vra.RateN,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(vra.created_at,'') AS ModifiedDate,
			IFNULL(vra.created_by,'') AS ModifiedBy,
        	RC.Name AS RoutingCategoryName,
        	vra.Preference,
        	vra.Blocked
		FROM
			tblRateTableRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblRate o_r ON o_r.RateID=vra.OriginationRateID
    	LEFT JOIN speakintelligentRouting.tblRoutingCategory AS RC
    	  	ON RC.RoutingCategoryID = vra.RoutingCategoryID
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.RateTableId = p_RateTableID AND
			vra.TimezonesID = p_TimezonesID AND
			(
				(vra.RateID, vra.OriginationRateID) IN (
					SELECT RateID,OriginationRateID FROM temp_rateids_
				)
			)
		ORDER BY
			vra.EffectiveDate DESC, vra.created_at DESC;
	ELSE
		INSERT INTO tmp_RateTableRate_ (
			OriginationCode,
		  	OriginationDescription,
			Code,
		  	Description,
		  	Interval1,
		  	IntervalN,
		  	ConnectionFee,
		  	Rate,
		  	RateN,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy,
         RoutingCategoryName,
        	Preference,
        	Blocked
		)
	   SELECT
			GROUP_CONCAT(DISTINCT o_r.Code) AS OriginationCode,
			MAX(o_r.Description) AS OriginationDescription,
			GROUP_CONCAT(r.Code),
			r.Description,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
			vra.Rate,
		  	MAX(vra.RateN) AS RateN,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(MAX(vra.created_at),'') AS ModifiedDate,
			IFNULL(MAX(vra.created_by),'') AS ModifiedBy,
        	MAX(RC.Name) AS RoutingCategoryName,
        	MAX(vra.Preference) AS Preference,
        	MAX(vra.Blocked) AS Blocked
		FROM
			tblRateTableRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblRate o_r ON o_r.RateID=vra.OriginationRateID
    	LEFT JOIN speakintelligentRouting.tblRoutingCategory AS RC
    	  	ON RC.RoutingCategoryID = vra.RoutingCategoryID
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.RateTableId = p_RateTableID AND
			vra.TimezonesID = p_TimezonesID AND
			(
				(vra.RateID, vra.OriginationRateID) IN (
					SELECT RateID,OriginationRateID FROM temp_rateids_
				)
			)
		GROUP BY
			Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate
		ORDER BY
			vra.EffectiveDate DESC, MAX(vra.created_at) DESC;
	END IF;

	SELECT
		OriginationCode,
	  	OriginationDescription,
		Code,
		Description,
		Interval1,
		IntervalN,
		ConnectionFee,
		Rate,
		RateN,
		EffectiveDate,
		EndDate,
		IFNULL(updated_at,'') AS ModifiedDate,
		IFNULL(ModifiedBy,'') AS ModifiedBy,
		RoutingCategoryName,
      Preference,
      Blocked
	FROM tmp_RateTableRate_;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT
)
ThisSP:BEGIN


	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_split_RateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`DialStringPrefix` varchar(500) ,
		`RoutingCategoryID` int,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`DialStringPrefix` varchar(500) ,
		`RoutingCategoryID` int,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	ALTER TABLE `tmp_TempRateTableRate_`	ADD Column `NewRate` decimal(18, 6) ;

    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

    SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
    SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


	update tmp_TempRateTableRate_
	SET
	NewRate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
                    THEN
                        Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    Rate
                )
    WHERE ProcessID=p_processId;


    IF newstringcode = 0
    THEN

		INSERT INTO tblRateTableRateChangeLog(
            TempRateTableRateID,
            RateTableRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
            Rate,
            RateN,
            EffectiveDate,
            EndDate,
            Interval1,
            IntervalN,
            ConnectionFee,
            Preference,
            Blocked,
            RoutingCategoryID,
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTableRate.TempRateTableRateID,
			tblRateTableRate.RateTableRateID,
         p_RateTableId AS RateTableId,
         tblTempRateTableRate.TimezonesID,
         OriginationRate.RateId AS OriginationRateID,
         tblTempRateTableRate.OriginationCode,
         tblTempRateTableRate.OriginationDescription,
         tblRate.RateId,
         tblTempRateTableRate.Code,
         tblTempRateTableRate.Description,
         tblTempRateTableRate.Rate,
         tblTempRateTableRate.RateN,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate ,
			IFNULL(tblTempRateTableRate.Interval1,tblRate.Interval1 ) as Interval1,
			IFNULL(tblTempRateTableRate.IntervalN , tblRate.IntervalN ) as IntervalN,
			tblTempRateTableRate.ConnectionFee,
         tblTempRateTableRate.Preference,
         tblTempRateTableRate.Blocked,
         tblTempRateTableRate.RoutingCategoryID,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		LEFT JOIN tblRate
			ON tblTempRateTableRate.Code = tblRate.Code AND tblTempRateTableRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON tblTempRateTableRate.OriginationCode = OriginationRate.Code AND tblTempRateTableRate.CodeDeckId = OriginationRate.CodeDeckId  AND OriginationRate.CompanyID = p_companyId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId AND OriginationRate.RateID = tblRateTableRate.OriginationRateID AND tblRateTableRate.RateTableId = p_RateTableId AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblRateTableRate.EffectiveDate  <= date(now())
		WHERE tblTempRateTableRate.ProcessID=p_processId AND tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');



        DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			EffectiveDate  Date,
			RowID int,
			INDEX (RowID)
		);
        INSERT INTO tmp_EffectiveDates_
        SELECT distinct
            EffectiveDate,
            @row_num := @row_num+1 AS RowID
        FROM tmp_TempRateTableRate_
            ,(SELECT @row_num := 0) x
        WHERE  ProcessID = p_processId

        group by EffectiveDate
        order by EffectiveDate asc;

        SET v_pointer_ = 1;
        SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

        IF v_rowCount_ > 0 THEN

            WHILE v_pointer_ <= v_rowCount_
            DO

                SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
                SET @row_num = 0;



                INSERT INTO tblRateTableRateChangeLog(
                    TempRateTableRateID,
                    RateTableRateID,
                    RateTableId,
                    TimezonesID,
                    OriginationRateID,
                    OriginationCode,
                    OriginationDescription,
                    RateId,
                    Code,
                    Description,
                    Rate,
                    RateN,
                    EffectiveDate,
                    EndDate,
                    Interval1,
                    IntervalN,
                    ConnectionFee,
			           Preference,
			           Blocked,
			           RoutingCategoryID,
                    `Action`,
                    ProcessID,
                    created_at
                )
                SELECT
                    distinct
                    tblTempRateTableRate.TempRateTableRateID,
                    RateTableRate.RateTableRateID,
                    p_RateTableId AS RateTableId,
                    tblTempRateTableRate.TimezonesID,
                    OriginationRate.RateId AS OriginationRateID,
                    OriginationRate.Code AS OriginationCode,
                    OriginationRate.Description AS OriginationDescription,
                    tblRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblTempRateTableRate.Rate,
                    tblTempRateTableRate.RateN,
                    tblTempRateTableRate.EffectiveDate,
                    tblTempRateTableRate.EndDate ,
                    tblTempRateTableRate.Interval1,
                    tblTempRateTableRate.IntervalN,
                    tblTempRateTableRate.ConnectionFee,
				        tblTempRateTableRate.Preference,
				        tblTempRateTableRate.Blocked,
				        tblTempRateTableRate.RoutingCategoryID,
                    IF(tblTempRateTableRate.NewRate > RateTableRate.Rate, 'Increased', IF(tblTempRateTableRate.NewRate < RateTableRate.Rate, 'Decreased','')) AS `Action`,
                    p_processid AS ProcessID,
                    now() AS created_at
                FROM
                (

                    select distinct tmp.* ,
                        @row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                        @prev_RateId := tmp.RateID,
                        @prev_EffectiveDate := tmp.EffectiveDate
                    FROM
                    (
                        select distinct vr1.*
                        from tblRateTableRate vr1
                        LEFT outer join tblRateTableRate vr2
                            on vr1.RateTableId = vr2.RateTableId
                            and vr1.RateID = vr2.RateID
                            and vr1.OriginationRateID = vr2.OriginationRateID
                            AND vr1.TimezonesID = vr2.TimezonesID
                            AND vr2.EffectiveDate  = @EffectiveDate
                        where
                            vr1.RateTableId = p_RateTableId
                            and vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate)
                        order by vr1.RateID desc ,vr1.EffectiveDate desc
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
                      order by RateID desc , EffectiveDate desc
                ) RateTableRate
                JOIN tblRate
                    ON tblRate.CompanyID = p_companyId
                    AND tblRate.RateID = RateTableRate.RateId
                LEFT JOIN tblRate AS OriginationRate
                    ON OriginationRate.CompanyID = p_companyId
                    AND OriginationRate.RateID = RateTableRate.OriginationRateID
                JOIN tmp_TempRateTableRate_ tblTempRateTableRate
                    ON tblTempRateTableRate.Code = tblRate.Code
                    AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
                    AND tblTempRateTableRate.TimezonesID = RateTableRate.TimezonesID
                    AND tblTempRateTableRate.ProcessID=p_processId

                    AND  RateTableRate.EffectiveDate <= tblTempRateTableRate.EffectiveDate
                    AND tblTempRateTableRate.EffectiveDate =  @EffectiveDate
                    AND RateTableRate.RowID = 1
                WHERE
                    RateTableRate.RateTableId = p_RateTableId

                    AND tblTempRateTableRate.Code IS NOT NULL
                    AND tblTempRateTableRate.ProcessID=p_processId
                    AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

                SET v_pointer_ = v_pointer_ + 1;

            END WHILE;

        END IF;


        IF p_list_option = 1
        THEN

            INSERT INTO tblRateTableRateChangeLog(
                RateTableRateID,
                RateTableId,
                TimezonesID,
                OriginationRateID,
                OriginationCode,
                OriginationDescription,
                RateId,
                Code,
                Description,
                Rate,
                RateN,
                EffectiveDate,
                EndDate,
                Interval1,
                IntervalN,
                ConnectionFee,
		          Preference,
		          Blocked,
		          RoutingCategoryID,
                `Action`,
                ProcessID,
                created_at
            )
            SELECT DISTINCT
                tblRateTableRate.RateTableRateID,
                p_RateTableId AS RateTableId,
                tblRateTableRate.TimezonesID,
                tblRateTableRate.OriginationRateID,
                OriginationRate.Code,
                OriginationRate.Description,
                tblRateTableRate.RateId,
                tblRate.Code,
                tblRate.Description,
                tblRateTableRate.Rate,
                tblRateTableRate.RateN,
                tblRateTableRate.EffectiveDate,
                tblRateTableRate.EndDate ,
                tblRateTableRate.Interval1,
                tblRateTableRate.IntervalN,
                tblRateTableRate.ConnectionFee,
                tblRateTableRate.Preference,
                tblRateTableRate.Blocked,
                tblRateTableRate.RoutingCategoryID,
                'Deleted' AS `Action`,
                p_processId AS ProcessID,
                now() AS deleted_at
            FROM tblRateTableRate
            JOIN tblRate
                ON tblRate.RateID = tblRateTableRate.RateId AND tblRate.CompanyID = p_companyId
        		LEFT JOIN tblRate AS OriginationRate
             	 ON OriginationRate.RateID = tblRateTableRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
            LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
                ON tblTempRateTableRate.Code = tblRate.Code
                AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
                AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
                AND tblTempRateTableRate.ProcessID=p_processId
                AND (

                    ( tblTempRateTableRate.EndDate is null AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
                    OR

                    ( tblTempRateTableRate.EndDate is not null AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
                )
            WHERE tblRateTableRate.RateTableId = p_RateTableId
                AND ( tblRateTableRate.EndDate is null OR tblRateTableRate.EndDate <= date(now()) )
                AND tblTempRateTableRate.Code IS NULL
            ORDER BY RateTableRateID ASC;

        END IF;


        INSERT INTO tblRateTableRateChangeLog(
            RateTableRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
            Rate,
            RateN,
            EffectiveDate,
            EndDate,
            Interval1,
            IntervalN,
            ConnectionFee,
            Preference,
            Blocked,
            RoutingCategoryID,
            `Action`,
            ProcessID,
            created_at
        )
        SELECT DISTINCT
            tblRateTableRate.RateTableRateID,
            p_RateTableId AS RateTableId,
            tblRateTableRate.TimezonesID,
            tblRateTableRate.OriginationRateID,
            OriginationRate.Code,
            OriginationRate.Description,
            tblRateTableRate.RateId,
            tblRate.Code,
            tblRate.Description,
            tblRateTableRate.Rate,
            tblRateTableRate.RateN,
            tblRateTableRate.EffectiveDate,
            IFNULL(tblTempRateTableRate.EndDate,tblRateTableRate.EndDate) as  EndDate ,
            tblRateTableRate.Interval1,
            tblRateTableRate.IntervalN,
            tblRateTableRate.ConnectionFee,
            tblRateTableRate.Preference,
            tblRateTableRate.Blocked,
            tblRateTableRate.RoutingCategoryID,
            'Deleted' AS `Action`,
            p_processId AS ProcessID,
            now() AS deleted_at
        FROM tblRateTableRate
        JOIN tblRate
            ON tblRate.RateID = tblRateTableRate.RateId AND tblRate.CompanyID = p_companyId
        LEFT JOIN tblRate AS OriginationRate
             ON OriginationRate.RateID = tblRateTableRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
        LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
            ON tblRate.Code = tblTempRateTableRate.Code
            AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
            AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
            AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
            AND tblTempRateTableRate.ProcessID=p_processId


        WHERE tblRateTableRate.RateTableId = p_RateTableId

            AND tblTempRateTableRate.Code IS NOT NULL
        ORDER BY RateTableRateID ASC;


    END IF;

    SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableCheckDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableCheckDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_2` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_3` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`RoutingCategoryID` int,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitRateTableRate(p_processId,p_dialcodeSeparator,p_seperatecolumn);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_RateTableRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_RateTableRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_RateTableRate_2 as (SELECT * FROM tmp_split_RateTableRate_);

	DELETE n1 FROM tmp_split_RateTableRate_ n1
	INNER JOIN
	(
		SELECT MAX(TempRateTableRateID) AS TempRateTableRateID,EffectiveDate,OriginationCode,Code,DialStringPrefix,TimezonesID,Rate
		FROM tmp_split_RateTableRate_2 WHERE ProcessId = p_processId
		GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,Rate
		HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code
	AND ((n1.OriginationCode IS NULL AND n2.OriginationCode IS NULL) OR (n1.OriginationCode = n2.OriginationCode))
	AND n2.EffectiveDate = n1.EffectiveDate
	AND ((n2.DialStringPrefix IS NULL AND n1.DialStringPrefix IS NULL) OR (n2.DialStringPrefix = n1.DialStringPrefix))
	AND n2.TimezonesID = n1.TimezonesID
	AND n2.Rate = n1.Rate
	AND n1.TempRateTableRateID < n2.TempRateTableRateID
	WHERE n1.ProcessId = p_processId;

	INSERT INTO tmp_TempRateTableRate_
	SELECT DISTINCT
		`TempRateTableRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`OriginationCode`,
		`OriginationDescription`,
		`Code`,
		`Description`,
		`Rate`,
		`RateN`,
		`EffectiveDate`,
		`EndDate`,
		`Change`,
		`ProcessId`,
		`Preference`,
		`ConnectionFee`,
		`Interval1`,
		`IntervalN`,
		`Blocked`,
		`RoutingCategoryID`,
		`DialStringPrefix`
	FROM tmp_split_RateTableRate_
	WHERE tmp_split_RateTableRate_.ProcessId = p_processId;

	SELECT CodeDeckId INTO v_CodeDeckId_
	FROM tmp_TempRateTableRate_
	WHERE ProcessId = p_processId  LIMIT 1;

	UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
	LEFT JOIN tblRate
		ON tblRate.Code = tblTempRateTableRate.Code
		AND tblRate.CompanyID = p_companyId
		AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		AND tblRate.CodeDeckId =  v_CodeDeckId_
	SET
		tblTempRateTableRate.Interval1 = CASE WHEN tblTempRateTableRate.Interval1 is not null  and tblTempRateTableRate.Interval1 > 0
		THEN
			tblTempRateTableRate.Interval1
		ELSE
			CASE WHEN tblRate.Interval1 is not null
			THEN
				tblRate.Interval1
			ELSE
				1
			END
		END,
		tblTempRateTableRate.IntervalN = CASE WHEN tblTempRateTableRate.IntervalN is not null  and tblTempRateTableRate.IntervalN > 0
		THEN
			tblTempRateTableRate.IntervalN
		ELSE
			CASE WHEN tblRate.IntervalN is not null
			THEN
				tblRate.IntervalN
			ElSE
				1
			END
		END;

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_TempRateTableRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempRateTableRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	SELECT count(*) INTO totalduplicatecode FROM(
	SELECT count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;

	IF  totalduplicatecode > 0
	THEN

		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT OriginationCode,Code, 1 as a FROM(
		SELECT count(TempRateTableRateID) as c, OriginationCode, Code FROM tmp_TempRateTableRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
			CONCAT(OriginationCode, '-', Code, ' DUPLICATE CODE')
		FROM(
			SELECT count(TempRateTableRateID) as c, OriginationCode, Code FROM tmp_TempRateTableRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;
	END IF;

	IF	totalduplicatecode = 0
	THEN

		IF p_dialstringid >0
		THEN

			DROP TEMPORARY TABLE IF EXISTS tmp_DialString_;
			CREATE TEMPORARY TABLE tmp_DialString_ (
				`DialStringID` INT,
				`DialString` VARCHAR(250),
				`ChargeCode` VARCHAR(250),
				`Description` VARCHAR(250),
				`Forbidden` VARCHAR(50),
				INDEX tmp_DialStringID (`DialStringID`),
				INDEX tmp_DialStringID_ChargeCode (`DialStringID`,`ChargeCode`)
			);

			INSERT INTO tmp_DialString_
			SELECT DISTINCT
				`DialStringID`,
				`DialString`,
				`ChargeCode`,
				`Description`,
				`Forbidden`
			FROM tblDialStringCode
			WHERE DialStringID = p_dialstringid;

			SELECT  COUNT(*) as count INTO totaldialstringcode
			FROM tmp_TempRateTableRate_ vr
			LEFT JOIN tmp_DialString_ ds
				ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
			WHERE vr.ProcessId = p_processId
				AND ds.DialStringID IS NULL
				AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			IF totaldialstringcode > 0
			THEN




				INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
				  SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
					FROM tmp_TempRateTableRate_ vr
						LEFT JOIN tmp_DialString_ ds

							ON vr.DialStringPrefix = ds.DialString AND ds.DialStringID = p_dialStringId
						WHERE vr.ProcessId = p_processId
							AND ds.DialStringID IS NULL
							AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				TRUNCATE tmp_DialString_;
				INSERT INTO tmp_DialString_
					SELECT DISTINCT
						`DialStringID`,
						`DialString`,
						`ChargeCode`,
						`Description`,
						`Forbidden`
					FROM tblDialStringCode
						WHERE DialStringID = p_dialstringid;

				SELECT  COUNT(*) as count INTO totaldialstringcode
				FROM tmp_TempRateTableRate_ vr
					LEFT JOIN tmp_DialString_ ds
						ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))

					WHERE vr.ProcessId = p_processId
						AND ds.DialStringID IS NULL
						AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				INSERT INTO tmp_JobLog_ (Message)
					  SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
					  	FROM tmp_TempRateTableRate_ vr
							LEFT JOIN tmp_DialString_ ds

								ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
							WHERE vr.ProcessId = p_processId
								AND ds.DialStringID IS NULL
								AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			END IF;

			IF totaldialstringcode = 0
			THEN

				INSERT INTO tmp_RateTableRateDialString_
				SELECT DISTINCT
					`TempRateTableRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`DialString`,
					CASE WHEN ds.Description IS NULL OR ds.Description = ''
					THEN
						tblTempRateTableRate.Description
					ELSE
						ds.Description
					END
					AS Description,
					`Rate`,
					`RateN`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					`Preference`,
					`ConnectionFee`,
					`Interval1`,
					`IntervalN`,
					tblTempRateTableRate.Forbidden as Forbidden ,
					tblTempRateTableRate.DialStringPrefix as DialStringPrefix
				FROM tmp_TempRateTableRate_ as tblTempRateTableRate
				INNER JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode AND tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' AND tblTempRateTableRate.DialStringPrefix =  ds.DialString AND tblTempRateTableRate.Code = ds.ChargeCode  ))
				WHERE tblTempRateTableRate.ProcessId = p_processId
					AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');



				INSERT INTO tmp_RateTableRateDialString_2
				SELECT *  FROM tmp_RateTableRateDialString_ where DialStringPrefix!='';

				Delete From tmp_RateTableRateDialString_
				Where DialStringPrefix = ''
				And Code IN (Select DialStringPrefix From tmp_RateTableRateDialString_2);

				INSERT INTO tmp_RateTableRateDialString_3
				SELECT * FROM tmp_RateTableRateDialString_;



				DELETE  FROM tmp_TempRateTableRate_ WHERE  ProcessId = p_processId;

				INSERT INTO tmp_TempRateTableRate_(
					`TempRateTableRateID`,
					CodeDeckId,
					TimezonesID,
					Code,
					Description,
					Rate,
					RateN,
					EffectiveDate,
					EndDate,
					`Change`,
					ProcessId,
					Preference,
					ConnectionFee,
					Interval1,
					IntervalN,
					Forbidden,
					DialStringPrefix
				)
				SELECT DISTINCT
					`TempRateTableRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`Code`,
					`Description`,
					`Rate`,
					`RateN`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					`Preference`,
					`ConnectionFee`,
					`Interval1`,
					`IntervalN`,
					`Forbidden`,
					DialStringPrefix
				FROM tmp_RateTableRateDialString_3;

				UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
				JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode and tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' and tblTempRateTableRate.DialStringPrefix =  ds.DialString and tblTempRateTableRate.Code = ds.ChargeCode  ))
					AND tblTempRateTableRate.ProcessId = p_processId
					AND ds.Forbidden = 1
				SET tblTempRateTableRate.Forbidden = 'B';

				UPDATE tmp_TempRateTableRate_ as  tblTempRateTableRate
				JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode and tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' and tblTempRateTableRate.DialStringPrefix =  ds.DialString and tblTempRateTableRate.Code = ds.ChargeCode  ))
					AND tblTempRateTableRate.ProcessId = p_processId
					AND ds.Forbidden = 0
				SET tblTempRateTableRate.Forbidden = 'UB';

			END IF;

		END IF;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitRateTableRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempRateTableRateID_ INT;
	DECLARE v_OriginationCode_ TEXT;
	DECLARE v_OriginationCountryCode_ VARCHAR(500);
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN

		DROP TEMPORARY TABLE IF EXISTS `my_splits`;
		CREATE TEMPORARY TABLE `my_splits` (
			`TempRateTableRateID` INT(11) NULL DEFAULT NULL,
			`OriginationCode` Text NULL DEFAULT NULL,
			`OriginationCountryCode` Text NULL DEFAULT NULL,
			`Code` Text NULL DEFAULT NULL,
			`CountryCode` Text NULL DEFAULT NULL
		);

		SET i = 1;
		REPEAT
			/*
				p_seperatecolumn = 1 = Origination Code
				p_seperatecolumn = 2 = Destination Code
			*/
			IF(p_seperatecolumn = 1)
			THEN
				INSERT INTO my_splits (TempRateTableRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableRateID , FnStringSplit(OriginationCode, p_dialcodeSeparator, i), OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableRate
				WHERE FnStringSplit(OriginationCode, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			ELSE
				INSERT INTO my_splits (TempRateTableRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableRateID , OriginationCode, OriginationCountryCode, FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempRateTableRate
				WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			END IF;

			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;

		UPDATE my_splits SET OriginationCode = trim(OriginationCode), Code = trim(Code);



		INSERT INTO my_splits (TempRateTableRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
		SELECT TempRateTableRateID, OriginationCode, OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableRate
		WHERE
			(
				(p_seperatecolumn = 1 AND (OriginationCountryCode IS NOT NULL AND OriginationCountryCode <> '') AND (OriginationCode IS NULL OR OriginationCode = '')) OR
				(p_seperatecolumn = 2 AND (CountryCode IS NOT NULL AND CountryCode <> '') AND (Code IS NULL OR Code = ''))
			)
		AND ProcessId = p_processId;


		DROP TEMPORARY TABLE IF EXISTS tmp_newratetable_splite_;
		CREATE TEMPORARY TABLE tmp_newratetable_splite_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			TempRateTableRateID INT(11) NULL DEFAULT NULL,
			OriginationCode VARCHAR(500) NULL DEFAULT NULL,
			OriginationCountryCode VARCHAR(500) NULL DEFAULT NULL,
			Code VARCHAR(500) NULL DEFAULT NULL,
			CountryCode VARCHAR(500) NULL DEFAULT NULL
		);

		INSERT INTO tmp_newratetable_splite_(TempRateTableRateID,OriginationCode,OriginationCountryCode,Code,CountryCode)
		SELECT
			TempRateTableRateID,
			OriginationCode,
			OriginationCountryCode,
			Code,
			CountryCode
		FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableRateID IS NOT NULL;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newratetable_splite_);

		WHILE v_pointer_ <= v_rowCount_
		DO
			SET v_TempRateTableRateID_ = (SELECT TempRateTableRateID FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCode_ = (SELECT OriginationCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCountryCode_ = (SELECT OriginationCountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_Code_ = (SELECT Code FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);

			Call prc_SplitAndInsertRateTableRate(v_TempRateTableRateID_,p_seperatecolumn,v_OriginationCode_,v_OriginationCountryCode_,v_Code_,v_CountryCode_);

			SET v_pointer_ = v_pointer_ + 1;
		END WHILE;

		DELETE FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableRateID IS NOT NULL;

		DELETE FROM my_splits
		WHERE (Code = '' OR Code IS NULL) AND (CountryCode = '' OR CountryCode IS NULL);

		INSERT INTO tmp_split_RateTableRate_
		SELECT DISTINCT
			my_splits.TempRateTableRateID as `TempRateTableRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(my_splits.OriginationCountryCode,''),my_splits.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
			`Description`,
			`Rate`,
			`RateN`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Blocked`,
			`RoutingCategoryID`,
			`DialStringPrefix`
		FROM my_splits
		INNER JOIN tblTempRateTableRate
			ON my_splits.TempRateTableRateID = tblTempRateTableRate.TempRateTableRateID
		WHERE	tblTempRateTableRate.ProcessId = p_processId;

	END IF;

	IF p_dialcodeSeparator = 'null'
	THEN

		INSERT INTO tmp_split_RateTableRate_
		SELECT DISTINCT
			`TempRateTableRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(tblTempRateTableRate.OriginationCountryCode,''),tblTempRateTableRate.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(tblTempRateTableRate.CountryCode,''),tblTempRateTableRate.Code) as Code,
			`Description`,
			`Rate`,
			`RateN`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Blocked`,
			`RoutingCategoryID`,
			`DialStringPrefix`
		FROM tblTempRateTableRate
		WHERE ProcessId = p_processId;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitAndInsertRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitAndInsertRateTableRate`(
	IN `TempRateTableRateID` INT,
	IN `p_seperatecolumn` INT,
	IN `p_OriginationCode` VARCHAR(500),
	IN `p_OriginationCountryCode` VARCHAR(50),
	IN `p_Code` VARCHAR(500),
	IN `p_CountryCode` VARCHAR(50)
)
BEGIN

	DECLARE v_First_ VARCHAR(255);
	DECLARE v_Last_ VARCHAR(255);

	SELECT  REPLACE(SUBSTRING(SUBSTRING_INDEX(IF(p_seperatecolumn=1,p_OriginationCode,p_Code), '-', 1)
					, LENGTH(SUBSTRING_INDEX(IF(p_seperatecolumn=1,p_OriginationCode,p_Code), '-', 0)) + 1)
					, '-'
					, '') INTO v_First_;

	SELECT REPLACE(SUBSTRING(SUBSTRING_INDEX(IF(p_seperatecolumn=1,p_OriginationCode,p_Code), '-', 2)
					, LENGTH(SUBSTRING_INDEX(IF(p_seperatecolumn=1,p_OriginationCode,p_Code), '-', 1)) + 1)
					, '-'
					, '') INTO v_Last_;

	SET v_First_ = CONCAT(IF(p_seperatecolumn=1,p_OriginationCountryCode,p_CountryCode),v_First_);
	SET v_Last_ = CONCAT(IF(p_seperatecolumn=1,p_OriginationCountryCode,p_CountryCode),v_Last_);

	WHILE v_Last_ >= v_First_
	DO
		/*
			p_seperatecolumn = 1 = Origination Code
			p_seperatecolumn = 2 = Destination Code
		*/
		IF(p_seperatecolumn = 1)
		THEN
			INSERT my_splits (TempRateTableRateID,OriginationCode,OriginationCountryCode,Code,CountryCode) VALUES (TempRateTableRateID,v_Last_,'',p_Code,p_CountryCode);
		ELSE
			INSERT my_splits (TempRateTableRateID,OriginationCode,OriginationCountryCode,Code,CountryCode) VALUES (TempRateTableRateID,p_OriginationCode,p_OriginationCountryCode,v_Last_,'');
		END IF;
		SET v_Last_ = v_Last_ - 1;
	END WHILE;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getReviewRateTableRates`;
DELIMITER //
CREATE PROCEDURE `prc_getReviewRateTableRates`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_Action` VARCHAR(50),
	IN `p_Origination_Code` VARCHAR(50),
	IN `p_Origination_Description` VARCHAR(200),
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(200),
	IN `p_Timezone` INT,
	IN `p_RoutingCategoryID` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_isExport = 0
	THEN
		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		SELECT
			distinct
			IF(p_Action='Deleted',RateTableRateID,TempRateTableRateID) AS RateTableRateID,
			`OriginationCode`,`OriginationDescription`,`Code`,RTCL.`Description`,tz.Title,`Rate`,`RateN`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`,`Preference`,`Blocked`,RC.`Name` AS RoutingCategory
		FROM
			tblRateTableRateChangeLog AS RTCL
		JOIN
			tblTimezones tz ON RTCL.TimezonesID = tz.TimezonesID
		LEFT JOIN
			speakintelligentRouting.tblRoutingCategory RC ON RC.RoutingCategoryID = RTCL.RoutingCategoryID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action AND
			RTCL.TimezonesID = p_Timezone AND
			(p_Origination_Code IS NULL OR OriginationCode LIKE REPLACE(p_Origination_Code, '*', '%')) AND
			(p_Origination_Description IS NULL OR OriginationDescription LIKE REPLACE(p_Origination_Description, '*', '%')) AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%')) AND
			(p_Description IS NULL OR p_Description = '' OR RTCL.Description LIKE REPLACE(p_Description, '*', '%')) AND
			(p_RoutingCategoryID IS NULL OR RTCL.RoutingCategoryID = p_RoutingCategoryID)
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionDESC') THEN OriginationDescription
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationDescriptionASC') THEN OriginationDescription
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN RTCL.Description
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN RTCL.Description
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNDESC') THEN RateN
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateNASC') THEN RateN
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
		FROM
			tblRateTableRateChangeLog AS RTCL
		JOIN
			tblTimezones tz ON RTCL.TimezonesID = tz.TimezonesID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action AND
			RTCL.TimezonesID = p_Timezone AND
			(p_Origination_Code IS NULL OR OriginationCode LIKE REPLACE(p_Origination_Code, '*', '%')) AND
			(p_Origination_Description IS NULL OR OriginationDescription LIKE REPLACE(p_Origination_Description, '*', '%')) AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%')) AND
			(p_Description IS NULL OR p_Description = '' OR RTCL.Description LIKE REPLACE(p_Description, '*', '%')) AND
			(p_RoutingCategoryID IS NULL OR RTCL.RoutingCategoryID = p_RoutingCategoryID);
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			distinct
			`OriginationCode`,`OriginationDescription`,`Code`,RTCL.`Description`,tz.Title,`Rate`,`RateN`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`,`Preference`,`Blocked`,RC.Name AS `RoutingCategory`
		FROM
			tblRateTableRateChangeLog AS RTCL
		JOIN
			tblTimezones tz ON RTCL.TimezonesID = tz.TimezonesID
		LEFT JOIN
			speakintelligentRouting.tblRoutingCategory RC ON RC.RoutingCategoryID = RTCL.RoutingCategoryID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action AND
			RTCL.TimezonesID = p_Timezone AND
			(p_Origination_Code IS NULL OR OriginationCode LIKE REPLACE(p_Origination_Code, '*', '%')) AND
			(p_Origination_Description IS NULL OR OriginationDescription LIKE REPLACE(p_Origination_Description, '*', '%')) AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%')) AND
			(p_Description IS NULL OR p_Description = '' OR RTCL.Description LIKE REPLACE(p_Description, '*', '%')) AND
			(p_RoutingCategoryID IS NULL OR RTCL.RoutingCategoryID = p_RoutingCategoryID);
	END IF;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
)
ThisSP:BEGIN

	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;
	DECLARE v_RateApprovalProcess_ INT;

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = p_companyId AND `Key`='RateApprovalProcess';

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

    DROP TEMPORARY TABLE IF EXISTS tmp_TempTimezones_;
    CREATE TEMPORARY TABLE tmp_TempTimezones_ (
        TimezonesID INT
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`DialStringPrefix` varchar(500) ,
		`RoutingCategoryID` int,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		TempRateTableRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`DialStringPrefix` varchar(500) ,
		`RoutingCategoryID` int,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableRate (
		RateTableRateID INT,
		RateTableId INT,
		TimezonesID INT,
		OriginationRateID INT,
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		RateId INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		Rate DECIMAL(18, 6),
		RateN DECIMAL(18, 6),
		EffectiveDate DATETIME,
		EndDate Datetime ,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18, 6),
		Preference varchar(100) ,
		Blocked tinyint,
		RoutingCategoryID int,
		deleted_at DATETIME,
		INDEX tmp_RateTableRateDiscontinued_RateTableRateID (`RateTableRateID`)
	);


	CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;


 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableRate_;


	IF newstringcode = 0
	THEN




		IF (SELECT count(*) FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN

			UPDATE
				tblRateTableRate vr
			INNER JOIN tblRateTableRateChangeLog  vrcl
			on vrcl.RateTableRateID = vr.RateTableRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';


			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				AND vrcl.OriginationCode = tblTempRateTableRate.OriginationCode
			SET
				tblTempRateTableRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;


			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				AND vrcl.OriginationCode = tblTempRateTableRate.OriginationCode
			SET
				tblTempRateTableRate.Interval1 = vrcl.Interval1 ,
				tblTempRateTableRate.IntervalN = vrcl.IntervalN
			WHERE
				vrcl.`Action` = 'New'
				AND vrcl.Interval1 IS NOT NULL
				AND vrcl.IntervalN IS NOT NULL ;



		END IF;


		IF  p_replaceAllRates = 1
		THEN
			UPDATE tblRateTableRate
				SET tblRateTableRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;


		END IF;



		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableRate(
				RateTableRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
				Interval1,
				IntervalN,
				ConnectionFee,
				Preference,
				Blocked,
				RoutingCategoryID,
				deleted_at
			)
			SELECT DISTINCT
				tblRateTableRate.RateTableRateID,
				p_RateTableId AS RateTableId,
				tblRateTableRate.TimezonesID,
				tblRateTableRate.OriginationRateID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRateTableRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableRate.Rate,
				tblRateTableRate.RateN,
				tblRateTableRate.EffectiveDate,
				IFNULL(tblRateTableRate.EndDate,date(now())) ,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.ConnectionFee,
				tblRateTableRate.Preference,
				tblRateTableRate.Blocked,
				tblRateTableRate.RoutingCategoryID,
				now() AS deleted_at
			FROM tblRateTableRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableRate.TimezonesID
			LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
				ON tblTempRateTableRate.Code = tblRate.Code
				AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
				AND  tblTempRateTableRate.ProcessId = p_processId
				AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableRate.RateTableId = p_RateTableId
				AND tblTempRateTableRate.Code IS NULL
				AND ( tblRateTableRate.EndDate is NULL OR tblRateTableRate.EndDate <= date(now()) )
			ORDER BY RateTableRateID ASC;




			UPDATE tblRateTableRate
			JOIN tmp_Delete_RateTableRate ON tblRateTableRate.RateTableRateID = tmp_Delete_RateTableRate.RateTableRateID
				SET tblRateTableRate.EndDate = date(now())
			WHERE
				tblRateTableRate.RateTableId = p_RateTableId;

		END IF;



		IF ( (SELECT count(*) FROM tblRateTableRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN





			call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');


			call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);

		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate_2 AS (SELECT * FROM tmp_TempRateTableRate_);

		IF  p_addNewCodesToCodeDeck = 1
		THEN
			-- Destination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.Code,
					tblTempRateTableRate.Description,
					tblTempRateTableRate.CodeDeckId,
					tblTempRateTableRate.Interval1,
					tblTempRateTableRate.IntervalN
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			) vc;

			-- Origination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.OriginationCode AS Code,
					tblTempRateTableRate.OriginationDescription AS Description,
					tblTempRateTableRate.CodeDeckId,
					tblTempRateTableRate.Interval1,
					tblTempRateTableRate.IntervalN
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.OriginationCode IS NOT NULL AND tblTempRateTableRate.OriginationCode != ''
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			) vc;

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableRate.Code,
							tblTempRateTableRate.Description
						FROM tmp_TempRateTableRate_  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableRate.OriginationCode AS Code,
							tblTempRateTableRate.OriginationDescription AS Description
						FROM tmp_TempRateTableRate_2  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) c
			) as tbl GROUP BY a;

			IF errormessage IS NOT NULL
			THEN
				INSERT INTO tmp_JobLog_ (Message)
				SELECT DISTINCT
					CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableRate.Code,
							tblTempRateTableRate.Description
						FROM tmp_TempRateTableRate_  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableRate.OriginationCode AS Code,
							tblTempRateTableRate.OriginationDescription AS Description
						FROM tmp_TempRateTableRate_2  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;




		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
			AND OriginationRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
			AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
			AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTableRate.EndDate = IFNULL(tblTempRateTableRate.EndDate,date(now()))
		WHERE tblRateTableRate.RateTableId = p_RateTableId;



		UPDATE tblRate
		JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON 	  tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblTempRateTableRate.Code = tblRate.Code
			AND  tblTempRateTableRate.ProcessId = p_processId
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		SET
			tblRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRate.IntervalN = tblTempRateTableRate.IntervalN
		WHERE
			tblTempRateTableRate.Interval1 IS NOT NULL
			AND tblTempRateTableRate.IntervalN IS NOT NULL
			AND
			(
				tblRate.Interval1 != tblTempRateTableRate.Interval1
				OR
				tblRate.IntervalN != tblTempRateTableRate.IntervalN
			);




		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON tblRateTableRate.OriginationRateID = OriginationRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND OriginationRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
			AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblRateTableRate.RateId = tblRate.RateId
		SET
			tblRateTableRate.ConnectionFee = tblTempRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN
		WHERE
			tblRateTableRate.RateTableId = p_RateTableId;




		DELETE tblTempRateTableRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.Rate = tblRateTableRate.Rate
		WHERE
			tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');



		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);



		DROP TEMPORARY TABLE IF EXISTS tmp_PreviousRate;
		CREATE TEMPORARY TABLE `tmp_PreviousRate` (
			`OriginationRateId` int,
			`RateId` int,
			`PreviousRate` decimal(18, 6),
			`EffectiveDate` Datetime
		);

		UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		SET tblRateTableRate.EndDate = NOW()
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		INSERT INTO
			tmp_PreviousRate (OriginationRateId,RateId,PreviousRate,EffectiveDate)
		SELECT
			tblRateTableRate.OriginationRateID,tblRateTableRate.RateId,tblRateTableRate.Rate,tblTempRateTableRate.EffectiveDate
		FROM
			tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');


		call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);



		INSERT INTO tblRateTableRate (
			RateTableId,
			TimezonesID,
			OriginationRateID,
			RateId,
			Rate,
			RateN,
			EffectiveDate,
			EndDate,
			ConnectionFee,
			Interval1,
			IntervalN,
			Preference,
			Blocked,
			RoutingCategoryID,
			PreviousRate,
			ApprovedStatus
		)
		SELECT DISTINCT
			p_RateTableId,
			tblTempRateTableRate.TimezonesID,
			OriginationRate.RateID AS OriginationRateID,
			tblRate.RateID,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.Rate
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) AS Rate,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.RateN
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.RateN  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.RateN  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) AS RateN,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate,
			tblTempRateTableRate.ConnectionFee,
			tblTempRateTableRate.Interval1,
			tblTempRateTableRate.IntervalN,
			tblTempRateTableRate.Preference,
			tblTempRateTableRate.Blocked,
			tblTempRateTableRate.RoutingCategoryID,
			IFNULL(tmp_PreviousRate.PreviousRate,0) AS PreviousRate,
			IF(v_RateApprovalProcess_=1,0,1) AS ApprovedStatus
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.EffectiveDate = tblRateTableRate.EffectiveDate
		LEFT JOIN tmp_PreviousRate
			ON ((IFNULL(tmp_PreviousRate.OriginationRateID,0) = 0 AND OriginationRate.RateId IS NULL) OR (OriginationRate.RateId = tmp_PreviousRate.OriginationRateId))
			AND tblRate.RateId = tmp_PreviousRate.RateId AND tblTempRateTableRate.EffectiveDate = tmp_PreviousRate.EffectiveDate
		WHERE tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();



		DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			EffectiveDate  Date
		);
		INSERT INTO tmp_EffectiveDates_ (EffectiveDate)
		SELECT distinct
			EffectiveDate
		FROM
		(
			select distinct EffectiveDate
			from 	tblRateTableRate
			WHERE
				RateTableId = p_RateTableId
			Group By EffectiveDate
			order by EffectiveDate desc
		) tmp
		,(SELECT @row_num := 0) x;


		SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO
				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
				SET @row_num = 0;

				UPDATE  tblRateTableRate vr1
				inner join
				(
					select
						RateTableId,
						OriginationRateID,
						RateID,
						EffectiveDate,
						TimezonesID
					FROM tblRateTableRate
					WHERE RateTableId = p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
					AND vr1.OriginationRateID = tmpvr.OriginationRateID
					AND vr1.RateID = tmpvr.RateID
					AND vr1.TimezonesID = tmpvr.TimezonesID
					AND vr1.EffectiveDate < tmpvr.EffectiveDate
				SET
					vr1.EndDate = @EffectiveDate
				where
					vr1.RateTableId = p_RateTableId

					AND vr1.EndDate is null;


				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

		END IF;

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );


	call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');


	call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);


	DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableRateUpdatePreviousRate`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableRateUpdatePreviousRate`(
	IN `p_RateTableID` INT,
	IN `p_EffectiveDate` VARCHAR(50)
)
BEGIN

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_EffectiveDate != '' THEN

			SET  @EffectiveDate = STR_TO_DATE(p_EffectiveDate , '%Y-%m-%d');
			SET @row_num = 0;

			UPDATE tblRateTableRate rtr
			inner join
			(
				select distinct tmp.* ,
				@row_num := IF(@prev_RateId = tmp.RateID AND @prev_OriginationRateID = tmp.OriginationRateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
				@prev_OriginationRateID := tmp.OriginationRateID,
				@prev_RateId := tmp.RateID,
				@prev_EffectiveDate := tmp.EffectiveDate
				FROM
				(
					select distinct rt1.*
					from tblRateTableRate rt1
					inner join tblRateTableRate rt2
					on rt1.RateTableId = p_RateTableId and rt1.OriginationRateID = rt2.OriginationRateID and rt1.RateID = rt2.RateID AND rt1.TimezonesID = rt2.TimezonesID
					where
					rt1.RateTableID = p_RateTableId
					and rt1.EffectiveDate < rt2.EffectiveDate AND rt2.EffectiveDate  = @EffectiveDate
					order by rt1.OriginationRateID desc ,rt1.RateID desc ,rt1.EffectiveDate desc
				) tmp

			) old_rtr on  old_rtr.RateTableID = rtr.RateTableID AND old_rtr.OriginationRateID = rtr.OriginationRateID AND old_rtr.RateID = rtr.RateID AND plo_rtr.TimezonesID = rtr.TimezonesID
			and old_rtr.EffectiveDate < rtr.EffectiveDate AND rtr.EffectiveDate =  @EffectiveDate AND old_rtr.RowID = 1
			SET rtr.PreviousRate = old_rtr.Rate
			where
			rtr.RateTableID = p_RateTableId;

	ELSE

		DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
			CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
				EffectiveDate  Date,
				RowID int,
				INDEX (RowID)
			);

		INSERT INTO tmp_EffectiveDates_
		SELECT distinct
			EffectiveDate,
			@row_num := @row_num+1 AS RowID
		FROM tblRateTableRate a
			,(SELECT @row_num := 0) x
		WHERE  RateTableID = p_RateTableID
		group by EffectiveDate
		order by EffectiveDate asc;

		SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
				SET @row_num = 0;

				UPDATE tblRateTableRate rtr
				inner join
				(
					select distinct tmp.* ,
					@row_num := IF(@prev_OriginationRateID = tmp.OriginationRateID AND @prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
					@prev_OriginationRateID := tmp.OriginationRateID,
					@prev_RateId := tmp.RateID,
					@prev_EffectiveDate := tmp.EffectiveDate
					FROM
					(
						SELECT DISTINCT rt1.*
						FROM tblRateTableRate rt1
						INNER JOIN tblRateTableRate rt2
						ON rt1.RateTableId = p_RateTableId AND rt1.OriginationRateID = rt2.OriginationRateID AND rt1.RateID = rt2.RateID AND rt1.TimezonesID=rt2.TimezonesID
						WHERE
						rt1.RateTableID = p_RateTableId
						AND rt1.EffectiveDate < rt2.EffectiveDate AND rt2.EffectiveDate  = @EffectiveDate
						ORDER BY rt1.OriginationRateID DESC ,rt1.RateID DESC ,rt1.EffectiveDate DESC
					) tmp
				) old_rtr ON  old_rtr.RateTableID = rtr.RateTableID AND old_rtr.OriginationRateID = rtr.OriginationRateID AND old_rtr.RateID = rtr.RateID AND old_rtr.TimezonesID = rtr.TimezonesID AND old_rtr.EffectiveDate < rtr.EffectiveDate
				AND rtr.EffectiveDate =  @EffectiveDate  AND old_rtr.RowID = 1
				SET rtr.PreviousRate = old_rtr.Rate
				WHERE
				rtr.RateTableID = p_RateTableID;

				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

		END IF;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableRateApprove`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableRateApprove`(
	IN `p_RateTableId` INT,
	IN `p_RateTableRateId` LONGTEXT,
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` VARCHAR(50),
	IN `p_Critearea_Description` VARCHAR(200),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_OriginationDescription` VARCHAR(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_RoutingCategoryID` INT,
	IN `p_Critearea_Preference` TEXT,
	IN `p_Critearea_Blocked` TINYINT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ApprovedBy` VARCHAR(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableRate rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	LEFT JOIN speakintelligentRouting.tblRoutingCategory AS RC
    	ON RC.RoutingCategoryID = rtr.RoutingCategoryID
	SET
		rtr.ApprovedStatus = 1, rtr.ApprovedBy = p_ApprovedBy, rtr.ApprovedDate = NOW()
	WHERE
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableRateID,p_RateTableRateID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_OriginationDescription IS NULL) OR (p_Critearea_OriginationDescription IS NOT NULL AND r2.Description LIKE REPLACE(p_Critearea_OriginationDescription,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND
					(p_Critearea_RoutingCategoryID IS NULL OR RC.RoutingCategoryID = p_Critearea_RoutingCategoryID ) AND
					(p_Critearea_Preference IS NULL OR rtr.Preference = p_Critearea_Preference) AND
					(p_Critearea_Blocked IS NULL OR rtr.Blocked = p_Critearea_Blocked) AND
					(p_Critearea_ApprovedStatus IS NULL OR rtr.ApprovedStatus = p_Critearea_ApprovedStatus) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND rtr.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND rtr.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		rtr.RateTableId = p_RateTableId AND
		rtr.TimezonesID = p_TimezonesID AND
		rtr.ApprovedStatus = 0;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateApprove`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateApprove`(
	IN `p_RateTableId` INT,
	IN `p_RateTableDIDRateID` LONGTEXT,
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` VARCHAR(50),
	IN `p_Critearea_Description` VARCHAR(200),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_OriginationDescription` VARCHAR(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ApprovedBy` VARCHAR(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableDIDRate rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	SET
		rtr.ApprovedStatus = 1, rtr.ApprovedBy = p_ApprovedBy, rtr.ApprovedDate = NOW()
	WHERE
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableDIDRateID,p_RateTableDIDRateID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_OriginationDescription IS NULL) OR (p_Critearea_OriginationDescription IS NOT NULL AND r2.Description LIKE REPLACE(p_Critearea_OriginationDescription,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND
					(p_Critearea_ApprovedStatus IS NULL OR rtr.ApprovedStatus = p_Critearea_ApprovedStatus) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND rtr.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND rtr.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		rtr.RateTableId = p_RateTableId AND
		rtr.TimezonesID = p_TimezonesID AND
		rtr.ApprovedStatus = 0;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;