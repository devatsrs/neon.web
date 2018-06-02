USE `Ratemanagement3`;

CREATE TABLE `tblTimezones` (
	`TimezonesID` INT(11) NOT NULL AUTO_INCREMENT,
	`Title` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`FromTime` VARCHAR(10) NOT NULL COLLATE 'utf8_unicode_ci',
	`ToTime` VARCHAR(10) NOT NULL COLLATE 'utf8_unicode_ci',
	`DaysOfWeek` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`DaysOfMonth` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`Months` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`ApplyIF` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`Status` TINYINT(4) NOT NULL DEFAULT '1',
	`created_at` DATETIME NOT NULL,
	`created_by` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`updated_at` DATETIME NOT NULL,
	`updated_by` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`TimezonesID`),
	UNIQUE INDEX `IX_tblTimezones_Title` (`Title`)
) COLLATE='utf8_unicode_ci' ENGINE=InnoDB;

INSERT INTO `tblTimezones` (`TimezonesID`, `Title`, `FromTime`, `ToTime`, `DaysOfWeek`, `DaysOfMonth`, `Months`, `ApplyIF`, `Status`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES (1, 'Default', '0:00', '23:59', '', '', '', 'start', 1, '2018-05-22 11:46:21', 'System', '2018-05-29 11:41:57', 'System');

INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1344, 'Timezones.Delete', 1, 8);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1343, 'Timezones.Edit', 1, 8);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1342, 'Timezones.Add', 1, 8);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1341, 'Timezones.View', 1, 8);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1340, 'Timezones.All', 1, 8);


-- need to get tblResource queries from local database when this task is finished
/*INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Timezones.Delete', 'TimezonesController.delete', 1, 'Vasim Seta', NULL, '2018-05-21 14:51:44.000', '2018-05-21 14:51:44.000', 1344);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Timezones.Update', 'TimezonesController.update', 1, 'Vasim Seta', NULL, '2018-05-21 14:51:44.000', '2018-05-21 14:51:44.000', 1343);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Timezones.Edit', 'TimezonesController.edit', 1, 'Vasim Seta', NULL, '2018-05-21 14:51:44.000', '2018-05-21 14:51:44.000', 1343);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Timezones.Store', 'TimezonesController.store', 1, 'Vasim Seta', NULL, '2018-05-21 14:51:44.000', '2018-05-21 14:51:44.000', 1342);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Timezones.Add', 'TimezonesController.create', 1, 'Vasim Seta', NULL, '2018-05-21 14:51:44.000', '2018-05-21 14:51:44.000', 1342);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Timezones.ajax_datagrid', 'TimezonesController.ajax_datagrid', 1, 'Vasim Seta', NULL, '2018-05-21 14:51:44.000', '2018-05-21 14:51:44.000', 1341);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Timezones.index', 'TimezonesController.index', 1, 'Vasim Seta', NULL, '2018-05-21 14:51:44.000', '2018-05-21 14:51:44.000', 1341);*/


ALTER TABLE `tblVendorRate`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `TrunkID`,
	DROP INDEX `IXUnique_AccountId_TrunkId_RateId_EffectiveDate`,
	ADD UNIQUE INDEX `IXUnique_AccountId_TrunkId_RateId_EffectiveDate` (`AccountId`, `TrunkID`, `TimezonesID`, `RateId`, `EffectiveDate`);

ALTER TABLE `tblRateTableRate`
	ADD COLUMN `TimezonesID` BIGINT(20) NOT NULL DEFAULT '1' AFTER `RateTableId`,
	DROP INDEX `IX_Unique_RateID_RateTableId_EffectiveDate`,
	ADD UNIQUE INDEX `IX_Unique_RateID_RateTableId_TimezonesID_EffectiveDate` (`RateID`, `RateTableId`, `TimezonesID`, `EffectiveDate`);

ALTER TABLE `tblCustomerRate`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `TrunkID`,
	DROP INDEX `IXUnique_RateId_CustomerId_TrunkId_EffectiveDate`,
	ADD UNIQUE INDEX `IXUnique_RateId_CustomerId_TrunkId_TimezonesID_EffectiveDate` (`RateID`, `CustomerID`, `TrunkID`, `TimezonesID`, `EffectiveDate`);

ALTER TABLE `tblVendorRateArchive`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `TrunkID`;

ALTER TABLE `tblRateTableRateArchive`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `RateTableId`;

ALTER TABLE `tblCustomerRateArchive`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `TrunkID`;

ALTER TABLE `tblTempVendorRate`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `CountryCode`;

ALTER TABLE `tblTempRateTableRate`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `CountryCode`;

ALTER TABLE `tblVendorRateChangeLog`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `TrunkID`;

ALTER TABLE `tblRateTableRateChangeLog`
	ADD COLUMN `TimezonesID` INT(11) NOT NULL DEFAULT '1' AFTER `RateTableId`;




DROP PROCEDURE IF EXISTS `prc_GetTimezones`;
DELIMITER //
CREATE PROCEDURE `prc_GetTimezones`(
	IN `p_Title` varchar(100),
	IN `p_PageNumber` INT ,
	IN `p_RowspPage` INT ,
	IN `p_lSortCol` VARCHAR(50) ,
	IN `p_SortOrder` VARCHAR(5) ,
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_Timezones_;
	CREATE TEMPORARY TABLE tmp_Timezones_ (
		`TimezonesID` int(11) NOT NULL,
		`Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
		`FromTime` varchar(10) NOT NULL,
		`ToTime` varchar(10) NOT NULL,
		`DaysOfWeek` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
		`DaysOfMonth` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
		`Months` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
		`ApplyIF` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
		`Status` TINYINT(4) NOT NULL,
		`created_at` datetime NOT NULL,
		`created_by` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
		`updated_at` datetime NOT NULL,
		`updated_by` varchar(50) COLLATE utf8_unicode_ci NOT NULL
	);

	INSERT INTO tmp_Timezones_
	SELECT
		`TimezonesID`,
		`Title`,
		`FromTime`,
		`ToTime`,
		`DaysOfWeek`,
		`DaysOfMonth`,
		`Months`,
		`ApplyIF`,
		`Status`,
		`created_at`,
		`created_by`,
		`updated_at`,
		`updated_by`
	FROM
		tblTimezones
	WHERE
		p_Title IS NULL OR Title LIKE REPLACE(p_Title, '*', '%');

	IF p_isExport = 0
	THEN
		SELECT
			`Title`,
			`FromTime`,
			`ToTime`,
			`DaysOfWeek`,
			`DaysOfMonth`,
			`Months`,
			`ApplyIF`,
			`created_at`,
			`created_by`,
			`TimezonesID`,
			`Status`
		FROM
			tmp_Timezones_
		ORDER BY
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN Title
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN Title
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'FromTimeDESC') THEN FromTime
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'FromTimeASC') THEN FromTime
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ToTimeDESC') THEN ToTime
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ToTimeASC') THEN ToTime
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DaysOfWeekDESC') THEN DaysOfWeek
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DaysOfWeekASC') THEN DaysOfWeek
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DaysOfMonthDESC') THEN DaysOfMonth
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DaysOfMonthASC') THEN DaysOfMonth
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthsDESC') THEN Months
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthsASC') THEN Months
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApplyIFDESC') THEN ApplyIF
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApplyIFASC') THEN ApplyIF
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN created_at
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN created_at
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_byDESC') THEN created_by
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_byASC') THEN created_by
			END ASC
		LIMIT
			p_RowspPage OFFSET v_OffSet_;

		SELECT
		COUNT(code) AS totalcount
		FROM tmp_Timezones_;

	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			`Title`,
			`FromTime`,
			`ToTime`,
			`DaysOfWeek`,
			`DaysOfMonth`,
			`Months`,
			`ApplyIF`,
			`created_at`,
			`created_by`
		FROM
			tmp_Timezones_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetVendorRates`;
DELIMITER //
CREATE PROCEDURE `prc_GetVendorRates`(
	IN `p_companyid` INT ,
	IN `p_AccountID` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_contryID` INT ,
	IN `p_code` VARCHAR(50) ,
	IN `p_description` VARCHAR(50) ,
	IN `p_effective` varchar(100),
	IN `p_PageNumber` INT ,
	IN `p_RowspPage` INT ,
	IN `p_lSortCol` VARCHAR(50) ,
	IN `p_SortOrder` VARCHAR(5) ,
	IN `p_isExport` INT
)
BEGIN


	DECLARE v_CodeDeckId_ int;
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


		select CodeDeckId into v_CodeDeckId_  from tblVendorTrunk where AccountID = p_AccountID and TrunkID = p_trunkID;


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
	   CREATE TEMPORARY TABLE tmp_VendorRate_ (
	        VendorRateID INT,
	        Code VARCHAR(50),
	        Description VARCHAR(200),
			  ConnectionFee DECIMAL(18, 6),
	        Interval1 INT,
	        IntervalN INT,
	        Rate DECIMAL(18, 6),
	        EffectiveDate DATE,
	        EndDate DATE,
	        updated_at DATETIME,
	        updated_by VARCHAR(50),
	        INDEX tmp_VendorRate_RateID (`Code`)
	    );

	    INSERT INTO tmp_VendorRate_
		 SELECT
					VendorRateID,
					Code,
					tblRate.Description,
					tblVendorRate.ConnectionFee,
					CASE WHEN tblVendorRate.Interval1 IS NOT NULL
					THEN tblVendorRate.Interval1
					ELSE tblRate.Interval1
					END AS Interval1,
					CASE WHEN tblVendorRate.IntervalN IS NOT NULL
					THEN tblVendorRate.IntervalN
					ELSE tblRate.IntervalN
					END AS IntervalN ,
					Rate,
					EffectiveDate,
					EndDate,
					tblVendorRate.updated_at,
					tblVendorRate.updated_by
				FROM tblVendorRate
				JOIN tblRate
					ON tblVendorRate.RateId = tblRate.RateId
				WHERE (p_contryID IS NULL
				OR CountryID = p_contryID)
				AND (p_code IS NULL
				OR Code LIKE REPLACE(p_code, '*', '%'))
				AND (p_description IS NULL
				OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
				AND (tblRate.CompanyID = p_companyid)
				AND TrunkID = p_trunkID
				AND TimezonesID = p_TimezonesID
				AND tblVendorRate.AccountID = p_AccountID
				AND CodeDeckId = v_CodeDeckId_
				AND
					(
					(p_effective = 'Now' AND EffectiveDate <= NOW() )
					OR
					(p_effective = 'Future' AND EffectiveDate > NOW())
					OR
					p_effective = 'All'
					);
		IF p_effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);
         DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.Code = n2.Code;
		END IF;

		IF p_effective = 'All'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);
         DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate2_ n2 WHERE n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW() AND n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.Code = n2.Code;
		END IF;


		   IF p_isExport = 0
			THEN
		 		SELECT
					VendorRateID,
					Code,
					Description,
					ConnectionFee,
					Interval1,
					IntervalN,
					Rate,
					EffectiveDate,
					EndDate,
					updated_at,
					updated_by

				FROM  tmp_VendorRate_
				ORDER BY CASE
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
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDDESC') THEN VendorRateID
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDASC') THEN VendorRateID
					END ASC
				LIMIT p_RowspPage OFFSET v_OffSet_;



				SELECT
					COUNT(code) AS totalcount
				FROM tmp_VendorRate_;


			END IF;

       IF p_isExport = 1
		THEN

			SELECT
				Code,
				Description,
				Rate,
				EffectiveDate,
				EndDate,
				updated_at AS `Modified Date`,
				updated_by AS `Modified By`

			FROM tmp_VendorRate_;
		END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getDiscontinuedVendorRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedVendorRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_CountryID` INT,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
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

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
   CREATE TEMPORARY TABLE tmp_VendorRate_ (
        VendorRateID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
		  ConnectionFee VARCHAR(50),
        Interval1 INT,
        IntervalN INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        updated_by VARCHAR(50),
        INDEX tmp_VendorRate_RateID (`Code`)
   );

   INSERT INTO tmp_VendorRate_
		SELECT
			vra.VendorRateID,
			r.Code,
			r.Description,
			'' AS ConnectionFee,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			vra.Rate,
			vra.EffectiveDate,
			vra.EndDate,
			vra.created_at AS updated_at,
			vra.created_by AS updated_by
		FROM
			tblVendorRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblVendorRate vr ON vr.AccountId = vra.AccountId AND vr.TrunkID = vra.TrunkID AND vr.RateId = vra.RateId AND vr.TimezonesID = vra.TimezonesID
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.TrunkID = p_TrunkID AND
			vra.TimezonesID = p_TimezonesID AND
			vra.AccountId = p_AccountID AND
			(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
			(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
			(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
			vr.VendorRateID is NULL;

	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);
		DELETE
			n1
		FROM
			tmp_VendorRate_ n1, tmp_VendorRate2_ n2
		WHERE
			n1.Code = n2.Code AND n1.VendorRateID < n2.VendorRateID;

 		SELECT
			VendorRateID,
			Code,
			Description,
			ConnectionFee,
			Interval1,
			IntervalN,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			updated_by
		FROM
			tmp_VendorRate_
		ORDER BY
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDDESC') THEN VendorRateID
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDASC') THEN VendorRateID
			END ASC
		LIMIT
			p_RowspPage
		OFFSET
			v_OffSet_;

		SELECT
			COUNT(code) AS totalcount
		FROM tmp_VendorRate_;

	END IF;

   IF p_isExport = 1
	THEN
		SELECT
			Code,
			Description,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at AS `Modified Date`,
			updated_by AS `Modified By`

		FROM tmp_VendorRate_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetVendorRatesArchiveGrid`;
DELIMITER //
CREATE PROCEDURE `prc_GetVendorRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_Codes` LONGTEXT
)
BEGIN
	SELECT
	--	vra.VendorRateArchiveID,
	--	vra.VendorRateID,
	--	vra.AccountID,
		r.Code,
		r.Description,
		IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
		CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
		vra.Rate,
		vra.EffectiveDate,
		IFNULL(vra.EndDate,'') AS EndDate,
		IFNULL(vra.created_at,'') AS ModifiedDate,
		IFNULL(vra.created_by,'') AS ModifiedBy
	FROM
		tblVendorRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.AccountId = p_AccountID AND
		vra.TrunkID = p_TrunkID AND
		vra.TimezonesID = p_TimezonesID AND
		FIND_IN_SET (r.Code, p_Codes) != 0
	ORDER BY
		vra.EffectiveDate DESC, vra.created_at DESC;
END//
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_VendorRateUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_VendorRateUpdateDelete`(
	IN `p_CompanyId` INT,
	IN `p_AccountId` INT,
	IN `p_VendorRateId` LONGTEXT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Rate` decimal(18,6),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_ConnectionFee` decimal(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Description` varchar(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TrunkId` INT,
	IN `p_TimezonesID` INT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	--	p_action = 1 = update rates
	--	p_action = 2 = delete rates

	DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
	CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
		`VendorRateId` int(11) NOT NULL,
		`RateId` int(11) NOT NULL,
		`AccountId` int(11) NOT NULL,
		`TrunkID` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
		`EffectiveDate` datetime NOT NULL,
		`EndDate` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		`created_at` datetime DEFAULT NULL,
		`created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`Interval1` int(11) DEFAULT NULL,
		`IntervalN` int(11) DEFAULT NULL,
		`ConnectionFee` decimal(18,6) DEFAULT NULL,
		`MinimumCost` decimal(18,6) DEFAULT NULL
	);

	INSERT INTO tmp_TempVendorRate_
	SELECT
		v.VendorRateId,
		v.RateId,
		v.AccountId,
		v.TrunkID,
		v.TimezonesID,
		IFNULL(p_Rate,v.Rate) AS Rate,
		IFNULL(p_EffectiveDate,v.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,v.EndDate) AS EndDate,
		NOW() AS updated_at,
		v.created_at,
		v.created_by,
		p_ModifiedBy AS updated_by,
		IFNULL(p_Interval1,v.Interval1) AS Interval1,
		IFNULL(p_IntervalN,v.IntervalN) AS IntervalN,
		IFNULL(p_ConnectionFee,v.ConnectionFee) AS ConnectionFee,
		v.MinimumCost
	FROM
		tblVendorRate v
	INNER JOIN
		tblRate r ON r.RateID = v.RateId
	INNER JOIN
		tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
	WHERE
		(
			p_EffectiveDate IS NULL OR v.RateID NOT IN (
				SELECT
					RateID
				FROM
					tblVendorRate
				WHERE
					EffectiveDate=p_EffectiveDate AND
					((p_Critearea = 0 AND (FIND_IN_SET(VendorRateID,p_VendorRateID) = 0 )) OR p_Critearea = 1) AND
					AccountId = p_AccountId
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(v.VendorRateID,p_VendorRateID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND v.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND v.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		v.AccountId = p_AccountId AND
		v.TrunkID = p_TrunkId AND
		v.TimezonesID = p_TimezonesID;

--	select * from tmp_TempVendorRate_;LEAVE ThisSP;

	-- if Effective Date needs to change then remove duplicate codes
	IF p_action = 1 AND p_EffectiveDate IS NOT NULL
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorRate_2 as (select * from tmp_TempVendorRate_);

		DELETE n1 FROM tmp_TempVendorRate_ n1, tmp_TempVendorRate_2 n2 WHERE n1.VendorRateID < n2.VendorRateID AND  n1.RateID = n2.RateID;
	END IF;

	-- archive and delete rates if action is 2 and also delete rates if action is 1 and rates are updating
	UPDATE
		tblVendorRate v
	INNER JOIN
		tmp_TempVendorRate_ temp ON temp.VendorRateID = v.VendorRateID
	SET
		v.EndDate = NOW()
	WHERE
		temp.VendorRateID = v.VendorRateID;

	CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN

		INSERT INTO tblVendorRate (
			RateId,
			AccountId,
			TrunkID,
			TimezonesID,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			created_at,
			created_by,
			updated_by,
			Interval1,
			IntervalN,
			ConnectionFee,
			MinimumCost
		)
		select
			RateId,
			AccountId,
			TrunkID,
			TimezonesID,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			created_at,
			created_by,
			updated_by,
			Interval1,
			IntervalN,
			ConnectionFee,
		MinimumCost
		from
			tmp_TempVendorRate_;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldVendorRate`(
	IN `p_AccountIds` LONGTEXT,
	IN `p_TrunkIds` LONGTEXT,
	IN `p_TimezonesIDs` LONGTEXT,
	IN `p_DeletedBy` TEXT
)
BEGIN
 	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	 /*1. Move Rates which EndDate <= now() */


	INSERT INTO tblVendorRateArchive
   SELECT DISTINCT  null , -- Primary Key column
							`VendorRateID`,
							`AccountId`,
							`TrunkID`,
							`TimezonesID`,
							`RateId`,
							`Rate`,
							`EffectiveDate`,
							IFNULL(`EndDate`,date(now())) as EndDate,
							`updated_at`,
							now() as `created_at`,
							p_DeletedBy AS `created_by`,
							`updated_by`,
							`Interval1`,
							`IntervalN`,
							`ConnectionFee`,
							`MinimumCost`,
	  concat('Ends Today rates @ ' , now() ) as `Notes`
      FROM tblVendorRate
      WHERE  FIND_IN_SET(AccountId,p_AccountIds) != 0 AND FIND_IN_SET(TrunkID,p_TrunkIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(TimezonesID,p_TimezonesIDs) != 0) AND EndDate <= NOW();


/*
     IF (FOUND_ROWS() > 0) THEN
	 	select concat(FOUND_ROWS() ," Ends Today rates" ) ;
	  END IF;
*/



	DELETE  vr
	FROM tblVendorRate vr
   inner join tblVendorRateArchive vra
   on vr.VendorRateID = vra.VendorRateID
	WHERE  FIND_IN_SET(vr.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr.TrunkID,p_TrunkIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(vr.TimezonesID,p_TimezonesIDs) != 0);


	/*  IF (FOUND_ROWS() > 0) THEN
		 select concat(FOUND_ROWS() ," sane rate " ) ;
	 END IF;

	*/

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
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
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
		Code VARCHAR(50),
		Description VARCHAR(200),
		Interval1 INT,
		IntervalN INT,
		ConnectionFee VARCHAR(50),
		PreviousRate DECIMAL(18, 6),
		Rate DECIMAL(18, 6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		updated_by VARCHAR(50),
		INDEX tmp_RateTableRate_RateID (`Code`)
	);

	INSERT INTO tmp_RateTableRate_
	SELECT
		vra.RateTableRateID,
		r.Code,
		r.Description,
		CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
		'' AS ConnectionFee,
		null AS PreviousRate,
		vra.Rate,
		vra.EffectiveDate,
		vra.EndDate,
		vra.created_at AS updated_at,
		vra.created_by AS updated_by
	FROM
		tblRateTableRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
	LEFT JOIN
		tblRateTableRate vr ON vr.RateTableId = vra.RateTableId AND vr.RateId = vra.RateId
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		vra.TimezonesID = p_TimezonesID AND
		(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
		vr.RateTableRateID is NULL;

	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_ as (select * from tmp_RateTableRate_);
		DELETE
			n1
		FROM
			tmp_RateTableRate_ n1, tmp_RateTableRate2_ n2
		WHERE
			n1.Code = n2.Code AND n1.RateTableRateID < n2.RateTableRateID;

		IF p_view = 1
		THEN
			SELECT
				RateTableRateID,
				Code,
				Description,
				Interval1,
				IntervalN,
				ConnectionFee,
				PreviousRate,
				Rate,
				EffectiveDate,
				EndDate,
				updated_at,
				updated_by
			FROM
				tmp_RateTableRate_
			ORDER BY
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
				group_concat(RateTableRateID) AS RateTableRateID,
				group_concat(Code),
				Description,
				ConnectionFee,
				Interval1,
				IntervalN,
				ANY_VALUE(PreviousRate),
				Rate,
				EffectiveDate,
				EndDate,
				MAX(updated_at),
				MAX(updated_by)
			FROM
				tmp_RateTableRate_
			GROUP BY
				Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate
			ORDER BY
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
					GROUP BY Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate
			) totalcount;

		END IF;

	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			Code,
			Description,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at AS `Modified Date`,
			updated_by AS `Modified By`
		FROM tmp_RateTableRate_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
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
	IN `p_code` VARCHAR(50),
	IN `p_description` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
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
	SET SESSION GROUP_CONCAT_MAX_LEN = 1000000; -- group_concat limit bydefault is 1024, so we have increase it
--	SET sql_mode = '';
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        ID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
		  ConnectionFee DECIMAL(18, 6),
        PreviousRate DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50),
        RateTableRateID INT,
        RateID INT,
        INDEX tmp_RateTableRate_RateID (`RateID`)
    );



    INSERT INTO tmp_RateTableRate_
    SELECT
        RateTableRateID AS ID,
        Code,
        Description,
        ifnull(tblRateTableRate.Interval1,1) as Interval1,
        ifnull(tblRateTableRate.IntervalN,1) as IntervalN,
		  tblRateTableRate.ConnectionFee,
        null as PreviousRate,
        IFNULL(tblRateTableRate.Rate, 0) as Rate,
        IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
        tblRateTableRate.EndDate,
        tblRateTableRate.updated_at,
        tblRateTableRate.ModifiedBy,
        RateTableRateID,
        tblRate.RateID
    FROM tblRate
    LEFT JOIN tblRateTableRate
        ON tblRateTableRate.RateID = tblRate.RateID
        AND tblRateTableRate.RateTableId = p_RateTableId
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
    WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID is null OR CountryID = p_contryID)
		AND (p_code is null OR Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_description is null OR Description LIKE REPLACE(p_description, '*', '%'))
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
		   AND  n1.RateID = n2.RateID;
		END IF;

	-- update Previous Rates
	UPDATE
		tmp_RateTableRate_ tr
	SET
		PreviousRate = (SELECT Rate FROM tblRateTableRate WHERE RateTableID=p_RateTableId AND TimezonesID = p_TimezonesID AND RateID=tr.RateID AND Code=tr.Code AND EffectiveDate<tr.EffectiveDate ORDER BY EffectiveDate DESC,RateTableRateID DESC LIMIT 1);

	UPDATE
		tmp_RateTableRate_ tr
	SET
		PreviousRate = (SELECT Rate FROM tblRateTableRateArchive WHERE RateTableID=p_RateTableId AND TimezonesID = p_TimezonesID AND RateID=tr.RateID AND Code=tr.Code AND EffectiveDate<tr.EffectiveDate ORDER BY EffectiveDate DESC,RateTableRateID DESC LIMIT 1)
	WHERE
		PreviousRate is null;

    IF p_isExport = 0
    THEN

		IF p_view = 1
		THEN
       	SELECT * FROM tmp_RateTableRate_
					ORDER BY CASE
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
			SELECT group_concat(ID) AS ID, group_concat(Code) AS Code,ANY_VALUE(Description),ANY_VALUE(Interval1),ANY_VALUE(Intervaln),ANY_VALUE(ConnectionFee),ANY_VALUE(PreviousRate),ANY_VALUE(Rate),ANY_VALUE(EffectiveDate),ANY_VALUE(EndDate),MAX(updated_at) AS updated_at,MAX(ModifiedBy) AS ModifiedBy,group_concat(ID) AS RateTableRateID,group_concat(RateID) AS RateID FROM tmp_RateTableRate_
					GROUP BY Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate
					ORDER BY
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
					GROUP BY Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate
			) totalcount;


		END IF;

    END IF;

    IF p_isExport = 1
    THEN

        SELECT
            Code,
            Description,
            Interval1,
            IntervalN,
            ConnectionFee,
            PreviousRate,
            Rate,
            EffectiveDate,
            updated_at,
            ModifiedBy

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
	IN `p_Codes` LONGTEXT,
	IN `p_View` INT
)
BEGIN

	SET SESSION GROUP_CONCAT_MAX_LEN = 1000000; -- group_concat limit bydefault is 1024, so we have increase it

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
		  ConnectionFee VARCHAR(50),
        PreviousRate DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50)
   );

	IF p_View = 1
	THEN
		INSERT INTO tmp_RateTableRate_ (
			Code,
		  	Description,
		  	Interval1,
		  	IntervalN,
		  	ConnectionFee,
--		  	PreviousRate,
		  	Rate,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy
		)
	   SELECT
			r.Code,
			r.Description,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
			vra.Rate,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(vra.created_at,'') AS ModifiedDate,
			IFNULL(vra.created_by,'') AS ModifiedBy
		FROM
			tblRateTableRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.RateTableId = p_RateTableID AND
			vra.TimezonesID = p_TimezonesID AND
			FIND_IN_SET (r.Code, p_Codes) != 0
		ORDER BY
			vra.EffectiveDate DESC, vra.created_at DESC;
	ELSE
		INSERT INTO tmp_RateTableRate_ (
			Code,
		  	Description,
		  	Interval1,
		  	IntervalN,
		  	ConnectionFee,
--		  	PreviousRate,
		  	Rate,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy
		)
	   SELECT
			GROUP_CONCAT(r.Code),
			r.Description,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
			vra.Rate,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(MAX(vra.created_at),'') AS ModifiedDate,
			IFNULL(MAX(vra.created_by),'') AS ModifiedBy
		FROM
			tblRateTableRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.RateTableId = p_RateTableID AND
			vra.TimezonesID = p_TimezonesID AND
			FIND_IN_SET (r.Code, p_Codes) != 0
		GROUP BY
			Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate
		ORDER BY
			vra.EffectiveDate DESC, MAX(vra.created_at) DESC;
	END IF;

	SELECT
		Code,
		Description,
		Interval1,
		IntervalN,
		ConnectionFee,
		Rate,
		EffectiveDate,
		EndDate,
		IFNULL(updated_at,'') AS ModifiedDate,
		IFNULL(ModifiedBy,'') AS ModifiedBy
	FROM tmp_RateTableRate_;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableRateUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableRateUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTableRateId` LONGTEXT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Rate` decimal(18,6),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_ConnectionFee` decimal(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Description` varchar(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	--	p_action = 1 = update rates
	--	p_action = 2 = delete rates

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		`RateTableRateId` int(11) NOT NULL,
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
		`EffectiveDate` datetime NOT NULL,
		`EndDate` datetime DEFAULT NULL,
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		`CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`Interval1` int(11) DEFAULT NULL,
		`IntervalN` int(11) DEFAULT NULL,
		`ConnectionFee` decimal(18,6) DEFAULT NULL
	);

	INSERT INTO tmp_TempRateTableRate_
	SELECT
		rtr.RateTableRateId,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IFNULL(p_Rate,rtr.Rate) AS Rate,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		IFNULL(p_Interval1,rtr.Interval1) AS Interval1,
		IFNULL(p_IntervalN,rtr.IntervalN) AS IntervalN,
		IFNULL(p_ConnectionFee,rtr.ConnectionFee) AS ConnectionFee
	FROM
		tblRateTableRate rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	WHERE
		(
			p_EffectiveDate IS NULL OR rtr.RateID NOT IN (
				SELECT
					RateID
				FROM
					tblRateTableRate
				WHERE
					EffectiveDate=p_EffectiveDate AND
					((p_Critearea = 0 AND (FIND_IN_SET(RateTableRateID,p_RateTableRateID) = 0 )) OR p_Critearea = 1) AND
					RateTableId = p_RateTableId
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableRateID,p_RateTableRateID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND
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

	-- if Effective Date needs to change then remove duplicate codes
	IF p_action = 1 AND p_EffectiveDate IS NOT NULL
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate_2 as (select * from tmp_TempRateTableRate_);

      DELETE n1 FROM tmp_TempRateTableRate_ n1, tmp_TempRateTableRate_2 n2 WHERE n1.RateTableRateID < n2.RateTableRateID AND  n1.RateID = n2.RateID;
	END IF;

	-- select * from tmp_TempRateTableRate_;leave ThisSP;
	-- archive and delete rates if action is 2 and also delete rates if action is 1 and rates are updating

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
			RateId,
			RateTableId,
			TimezonesID,
			Rate,
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			Interval1,
			IntervalN,
			ConnectionFee
		)
		select
			RateId,
			RateTableId,
			TimezonesID,
			Rate,
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			Interval1,
			IntervalN,
			ConnectionFee
		from
			tmp_TempRateTableRate_;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableRate`(
	IN `p_RateTableIds` LONGTEXT,
	IN `p_TimezonesIDs` LONGTEXT,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/* SET EndDate of current time to older rates */
	-- for example there are 3 rates, today's date is 2018-04-11
	-- 1. Code 	Rate 	EffectiveDate
	-- 1. 91 	0.1 	2018-04-09
	-- 2. 91 	0.2 	2018-04-10
	-- 3. 91 	0.3 	2018-04-11
	/* Then it will delete 2018-04-09 and 2018-04-10 date's rate */
	UPDATE
		tblRateTableRate rtr
	INNER JOIN tblRateTableRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
	SET
		rtr.EndDate=NOW()
	WHERE
		(FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0) AND rtr.EffectiveDate <= NOW()) AND
		(FIND_IN_SET(rtr2.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr2.TimezonesID,p_TimezonesIDs) != 0) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableRateID != rtr2.RateTableRateID;

	/*1. Move Rates which EndDate <= now() */

	INSERT INTO tblRateTableRateArchive
	SELECT DISTINCT  null , -- Primary Key column
		`RateTableRateID`,
		`RateTableId`,
		`TimezonesID`,
		`RateId`,
		`Rate`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`updated_at`,
		now() as `created_at`,
		p_DeletedBy AS `created_by`,
		`ModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM tblRateTableRate
	WHERE FIND_IN_SET(RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(TimezonesID,p_TimezonesIDs) != 0) AND EndDate <= NOW();

	/*
	IF (FOUND_ROWS() > 0) THEN
	select concat(FOUND_ROWS() ," Ends Today rates" ) ;
	END IF;
	*/

	DELETE  rtr
	FROM tblRateTableRate rtr
	inner join tblRateTableRateArchive rtra
		on rtr.RateTableRateID = rtra.RateTableRateID
	WHERE  FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0);

	/*  IF (FOUND_ROWS() > 0) THEN
	select concat(FOUND_ROWS() ," sane rate " ) ;
	END IF;
	*/

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetCustomerRate`;
DELIMITER //
CREATE PROCEDURE `prc_GetCustomerRate`(
	IN `p_companyid` INT,
	IN `p_AccountID` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_contryID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_description` VARCHAR(50),
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_effectedRates` INT,
	IN `p_RoutinePlan` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
ThisSP:BEGIN
   DECLARE v_codedeckid_ INT;
   DECLARE v_ratetableid_ INT;
   DECLARE v_RateTableAssignDate_ DATETIME;
   DECLARE v_NewA2ZAssign_ INT;
   DECLARE v_OffSet_ int;
   DECLARE v_IncludePrefix_ INT;
   DECLARE v_Prefix_ VARCHAR(50);
   DECLARE v_RatePrefix_ VARCHAR(50);
   DECLARE v_AreaPrefix_ VARCHAR(50);

   -- set custome date = current date if custom date is past date
   IF(p_CustomDate < DATE(NOW()))
	THEN
		SET p_CustomDate=DATE(NOW());
	END IF;

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

    SELECT
        CodeDeckId,
        RateTableID,
        RateTableAssignDate,IncludePrefix INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_,v_IncludePrefix_
    FROM tblCustomerTrunk
    WHERE CompanyID = p_companyid
    AND tblCustomerTrunk.TrunkID = p_trunkID
    AND tblCustomerTrunk.AccountID = p_AccountID
    AND tblCustomerTrunk.Status = 1;

    SELECT
        Prefix,RatePrefix,AreaPrefix INTO v_Prefix_,v_RatePrefix_,v_AreaPrefix_
    FROM tblTrunk
    WHERE CompanyID = p_companyid
    AND tblTrunk.TrunkID = p_trunkID
    AND tblTrunk.Status = 1;



    DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        RateTableRateID INT,
        TrunkID INT,
        TimezonesID INT,
        RoutinePlan INT,
        INDEX tmp_CustomerRates__RateID (`RateID`),
        INDEX tmp_CustomerRates__TrunkID (`TrunkID`),
        INDEX tmp_CustomerRates__EffectiveDate (`EffectiveDate`)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        RateID INT,
        Interval1 INT,
        IntervalN INT,
        Rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        RateTableRateID INT,
        TrunkID INT,
        TimezonesID INT,
        INDEX tmp_RateTableRate__RateID (`RateID`),
        INDEX tmp_RateTableRate__TrunkID (`TrunkID`),
        INDEX tmp_RateTableRate__EffectiveDate (`EffectiveDate`)

    );

    DROP TEMPORARY TABLE IF EXISTS tmp_customerrate_;
    CREATE TEMPORARY TABLE tmp_customerrate_ (
        RateID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        RoutinePlanName VARCHAR(50),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        TrunkID INT,
        RateTableRateId INT,
        IncludePrefix TINYINT,
        Prefix VARCHAR(50),
        RatePrefix VARCHAR(50),
        AreaPrefix VARCHAR(50)
    );


    INSERT INTO tmp_CustomerRates_

            SELECT
                tblCustomerRate.RateID,
                tblCustomerRate.Interval1,
                tblCustomerRate.IntervalN,

                tblCustomerRate.Rate,
                tblCustomerRate.ConnectionFee,
                tblCustomerRate.EffectiveDate,
                tblCustomerRate.EndDate,
                tblCustomerRate.LastModifiedDate,
                tblCustomerRate.LastModifiedBy,
                tblCustomerRate.CustomerRateId,
                NULL AS RateTableRateID,
                p_trunkID as TrunkID,
                p_TimezonesID as TimezonesID,
                tblCustomerRate.RoutinePlan

            FROM tblCustomerRate
            INNER JOIN tblRate
                ON tblCustomerRate.RateID = tblRate.RateID
            WHERE (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
            AND (tblRate.CompanyID = p_companyid)
            AND tblRate.CodeDeckId = v_codedeckid_
            AND tblCustomerRate.TrunkID = p_trunkID
            AND (p_TimezonesID IS NULL OR tblCustomerRate.TimezonesID = p_TimezonesID)
            AND (p_RoutinePlan = 0 or tblCustomerRate.RoutinePlan = p_RoutinePlan)
            AND CustomerID = p_AccountID

            ORDER BY
                tblCustomerRate.TrunkId, tblCustomerRate.CustomerId,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;







	 	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates4_;
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);
			DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
			AND n1.TrunkID = n2.TrunkID
         AND (p_TimezonesID IS NULL OR n1.TimezonesID = n2.TimezonesID)
			AND  n1.RateID = n2.RateID
			AND
			(
				(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
				OR
				(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
			);

	 	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates2_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates2_ as (select * from tmp_CustomerRates_);
		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates3_;
	   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates3_ as (select * from tmp_CustomerRates_);
	   DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates5_;
	   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates5_ as (select * from tmp_CustomerRates_);

	   ALTER TABLE tmp_CustomerRates2_ ADD  INDEX tmp_CustomerRatesRateID (`RateID`);
	   ALTER TABLE tmp_CustomerRates3_ ADD  INDEX tmp_CustomerRatesRateID (`RateID`);
	   ALTER TABLE tmp_CustomerRates5_ ADD  INDEX tmp_CustomerRatesRateID (`RateID`);

	   ALTER TABLE tmp_CustomerRates2_ ADD  INDEX tmp_CustomerRatesEffectiveDate (`EffectiveDate`);
	   ALTER TABLE tmp_CustomerRates3_ ADD  INDEX tmp_CustomerRatesEffectiveDate (`EffectiveDate`);
	   ALTER TABLE tmp_CustomerRates5_ ADD  INDEX tmp_CustomerRatesEffectiveDate (`EffectiveDate`);

    INSERT INTO tmp_RateTableRate_
            SELECT
                tblRateTableRate.RateID,
                tblRateTableRate.Interval1,
                tblRateTableRate.IntervalN,
                tblRateTableRate.Rate,
                tblRateTableRate.ConnectionFee,

      			 tblRateTableRate.EffectiveDate,
      			 tblRateTableRate.EndDate,
                NULL AS LastModifiedDate,
                NULL AS LastModifiedBy,
                NULL AS CustomerRateId,
                tblRateTableRate.RateTableRateID,
                p_trunkID as TrunkID,
                p_TimezonesID as TimezonesID
            FROM tblRateTableRate
            INNER JOIN tblRate
                ON tblRateTableRate.RateID = tblRate.RateID
            WHERE (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
            AND (tblRate.CompanyID = p_companyid)
            AND tblRate.CodeDeckId = v_codedeckid_
            AND RateTableID = v_ratetableid_
            AND (p_TimezonesID IS NULL OR tblRateTableRate.TimezonesID = p_TimezonesID)
            AND (
						(
							(SELECT count(*) from tmp_CustomerRates2_ cr where cr.RateID = tblRateTableRate.RateID) >0
							AND tblRateTableRate.EffectiveDate <
								( SELECT MIN(cr.EffectiveDate)
                          FROM tmp_CustomerRates_ as cr
                          WHERE cr.RateID = tblRateTableRate.RateID
								)
							AND (SELECT count(*) from tmp_CustomerRates5_ cr where cr.RateID = tblRateTableRate.RateID AND cr.EffectiveDate <= NOW() ) = 0
						)
						or  (  SELECT count(*) from tmp_CustomerRates3_ cr where cr.RateID = tblRateTableRate.RateID ) = 0
					)

                ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC;




		  DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate4_;
		  CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
        DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
	 	  AND n1.TrunkID = n2.TrunkID
		  AND  n1.RateID = n2.RateID
		  AND (p_TimezonesID IS NULL OR n1.TimezonesID = n2.TimezonesID)
		  AND
			(
				(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
				OR
				(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
			);





		  INSERT INTO tmp_customerrate_
        SELECT
        			 r.RateID,
                r.Code,
                r.Description,
                CASE WHEN allRates.Interval1 IS NULL
                THEN
                    r.Interval1
                ELSE
                    allRates.Interval1
                END as Interval1,
                CASE WHEN allRates.IntervalN IS NULL
                THEN
                    r.IntervalN
                ELSE
                    allRates.IntervalN
                END  IntervalN,
                allRates.ConnectionFee,
                allRates.RoutinePlanName,
                allRates.Rate,
                allRates.EffectiveDate,
                allRates.EndDate,
                allRates.LastModifiedDate,
                allRates.LastModifiedBy,
                allRates.CustomerRateId,
                p_trunkID as TrunkID,
                allRates.RateTableRateId,
					v_IncludePrefix_ as IncludePrefix ,
   	         CASE  WHEN tblTrunk.TrunkID is not null
               THEN
               	tblTrunk.Prefix
               ELSE
               	v_Prefix_
					END AS Prefix,
					CASE  WHEN tblTrunk.TrunkID is not null
               THEN
               	tblTrunk.RatePrefix
               ELSE
               	v_RatePrefix_
					END AS RatePrefix,
					CASE  WHEN tblTrunk.TrunkID is not null
               THEN
               	tblTrunk.AreaPrefix
               ELSE
               	v_AreaPrefix_
					END AS AreaPrefix
        FROM tblRate r
        LEFT JOIN (SELECT
                CustomerRates.RateID,
                CustomerRates.Interval1,
                CustomerRates.IntervalN,
                tblTrunk.Trunk as RoutinePlanName,
                CustomerRates.ConnectionFee,
                CustomerRates.Rate,
                CustomerRates.EffectiveDate,
                CustomerRates.EndDate,
                CustomerRates.LastModifiedDate,
                CustomerRates.LastModifiedBy,
                CustomerRates.CustomerRateId,
                NULL AS RateTableRateID,
                p_trunkID as TrunkID,
                RoutinePlan
            FROM tmp_CustomerRates_ CustomerRates
            LEFT JOIN tblTrunk on tblTrunk.TrunkID = CustomerRates.RoutinePlan
            WHERE
                (
					 	( p_Effective = 'Now' AND CustomerRates.EffectiveDate <= NOW() )
					 	OR
					 	( p_Effective = 'Future' AND CustomerRates.EffectiveDate > NOW() )
						OR
						( p_Effective = 'CustomDate' AND CustomerRates.EffectiveDate <= p_CustomDate AND (CustomerRates.EndDate IS NULL OR CustomerRates.EndDate > p_CustomDate) )
					 	OR
						p_Effective = 'All'
					 )


            UNION ALL

            SELECT
            DISTINCT
                rtr.RateID,
                rtr.Interval1,
                rtr.IntervalN,
                NULL,
                rtr.ConnectionFee,
                rtr.Rate,
                rtr.EffectiveDate,
                rtr.EndDate,
                NULL,
                NULL,
                NULL AS CustomerRateId,
                rtr.RateTableRateID,
                p_trunkID as TrunkID,
                NULL AS RoutinePlan
            FROM tmp_RateTableRate_ AS rtr
            LEFT JOIN tmp_CustomerRates2_ as cr
                ON cr.RateID = rtr.RateID AND
						 (
						 	( p_Effective = 'Now' AND cr.EffectiveDate <= NOW() )
						 	OR
						 	( p_Effective = 'Future' AND cr.EffectiveDate > NOW())
						 	OR
							( p_Effective = 'CustomDate' AND cr.EffectiveDate <= p_CustomDate AND (cr.EndDate IS NULL OR cr.EndDate > p_CustomDate) )
						 	OR
							 p_Effective = 'All'
						 )
            WHERE (
                (
                    p_Effective = 'Now' AND rtr.EffectiveDate <= NOW()
                    AND (
                            (cr.RateID IS NULL)
                            OR
                            (cr.RateID IS NOT NULL AND rtr.RateTableRateID IS NULL)
                        )

                )
                OR
                ( p_Effective = 'Future' AND rtr.EffectiveDate > NOW()
                    AND (
                            (cr.RateID IS NULL)
                            OR
                            (
                                cr.RateID IS NOT NULL AND rtr.EffectiveDate < (
                                                                                SELECT IFNULL(MIN(crr.EffectiveDate), rtr.EffectiveDate)
                                                                                FROM tmp_CustomerRates3_ as crr
                                                                                WHERE crr.RateID = rtr.RateID
                                                                                )
                            )
                        )
                )
				OR
				(
					p_Effective = 'CustomDate' AND rtr.EffectiveDate <= p_CustomDate AND (rtr.EndDate IS NULL OR rtr.EndDate > p_CustomDate)
					AND (
                            (cr.RateID IS NULL)
                            OR
                            (cr.RateID IS NOT NULL AND rtr.RateTableRateID IS NULL)
                        )
				)
            OR p_Effective = 'All'

            )

				) allRates
            ON allRates.RateID = r.RateID
         LEFT JOIN tblTrunk on tblTrunk.TrunkID = RoutinePlan
        WHERE (p_contryID IS NULL OR r.CountryID = p_contryID)
        AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
        AND (p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%'))
        AND (r.CompanyID = p_companyid)
        AND r.CodeDeckId = v_codedeckid_
        AND  ((p_effectedRates = 1 AND Rate IS NOT NULL) OR  (p_effectedRates = 0));




    IF p_isExport = 0
    THEN


         SELECT
                RateID,
                Code,
                Description,
                Interval1,
                IntervalN,
                ConnectionFee,
                RoutinePlanName,
                Rate,
                EffectiveDate,
                EndDate,
                LastModifiedDate,
                LastModifiedBy,
                CustomerRateId,
                TrunkID,
                RateTableRateId
            FROM tmp_customerrate_
            ORDER BY
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedDateDESC') THEN LastModifiedDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedDateASC') THEN LastModifiedDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedByDESC') THEN LastModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedByASC') THEN LastModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CustomerRateIdDESC') THEN CustomerRateId
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CustomerRateIdASC') THEN CustomerRateId
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(RateID) AS totalcount
        FROM tmp_customerrate_;

    END IF;

    IF p_isExport = 1
    THEN

          select
            Code,
            Description,
            Interval1,
            IntervalN,
            ConnectionFee,
            Rate,
            EffectiveDate,
            LastModifiedDate,
            LastModifiedBy from tmp_customerrate_;

    END IF;


	IF p_isExport = 2
    THEN

          select
            Code,
            Description,
            Interval1,
            IntervalN,
            ConnectionFee,
            Rate,
            EffectiveDate from tmp_customerrate_;

    END IF;


    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getDiscontinuedCustomerRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedCustomerRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_CountryID` INT,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
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

	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRate_;
	CREATE TEMPORARY TABLE tmp_CustomerRate_ (
		RateID INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		Interval1 INT,
		IntervalN INT,
		ConnectionFee VARCHAR(50),
		RoutinePlanName VARCHAR(50),
		Rate DECIMAL(18, 6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		updated_by VARCHAR(50),
		CustomerRateID INT,
		TrunkID INT,
        RateTableRateId INT,
		INDEX tmp_CustomerRate_RateID (`Code`)
	);

	INSERT INTO tmp_CustomerRate_
	SELECT
		cra.RateId,
		r.Code,
		r.Description,
		CASE WHEN cra.Interval1 IS NOT NULL THEN cra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN cra.IntervalN IS NOT NULL THEN cra.IntervalN ELSE r.IntervalN END AS IntervalN,
		'' AS ConnectionFee,
		cra.RoutinePlan AS RoutinePlanName,
		cra.Rate,
		cra.EffectiveDate,
		cra.EndDate,
		cra.created_at AS updated_at,
		cra.created_by AS updated_by,
		cra.CustomerRateID,
		p_trunkID AS TrunkID,
		NULL AS RateTableRateID
	FROM
		tblCustomerRateArchive cra
	JOIN
		tblRate r ON r.RateID=cra.RateId
	LEFT JOIN
		tblCustomerRate cr ON cr.CustomerID = cra.AccountId AND cr.TrunkID = cra.TrunkID AND cr.RateId = cra.RateId
	WHERE
		r.CompanyID = p_CompanyID AND
		cra.TrunkID = p_TrunkID AND
		cra.TimezonesID = p_TimezonesID AND
		cra.AccountId = p_AccountID AND
		(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
		cr.CustomerRateID is NULL;

	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRate2_ as (select * from tmp_CustomerRate_);
		DELETE
			n1
		FROM
			tmp_CustomerRate_ n1, tmp_CustomerRate2_ n2
		WHERE
			n1.Code = n2.Code AND n1.CustomerRateID < n2.CustomerRateID;

		SELECT
			RateID,
			Code,
			Description,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutinePlanName,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			updated_by,
			CustomerRateId,
			TrunkID,
			RateTableRateId
		FROM
			tmp_CustomerRate_
		ORDER BY
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CustomerRateIDDESC') THEN CustomerRateID
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CustomerRateIDASC') THEN CustomerRateID
			END ASC
		LIMIT
			p_RowspPage
			OFFSET
			v_OffSet_;

		SELECT
			COUNT(code) AS totalcount
		FROM tmp_CustomerRate_;

	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			Code,
			Description,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at AS `Modified Date`,
			updated_by AS `Modified By`
		FROM tmp_CustomerRate_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetCustomerRatesArchiveGrid`;
DELIMITER //
CREATE PROCEDURE `prc_GetCustomerRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_TimezonesID` INT,
	IN `p_Codes` LONGTEXT
)
BEGIN
	SELECT
	--	cra.CustomerRateArchiveID,
	--	cra.CustomerRateID,
	--	cra.AccountID,
		r.Code,
		r.Description,
		IFNULL(cra.ConnectionFee,'') AS ConnectionFee,
		CASE WHEN cra.Interval1 IS NOT NULL THEN cra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN cra.IntervalN IS NOT NULL THEN cra.IntervalN ELSE r.IntervalN END AS IntervalN,
		cra.Rate,
		cra.EffectiveDate,
		IFNULL(cra.EndDate,'') AS EndDate,
		IFNULL(cra.created_at,'') AS ModifiedDate,
		IFNULL(cra.created_by,'') AS ModifiedBy
	FROM
		tblCustomerRateArchive cra
	JOIN
		tblRate r ON r.RateID=cra.RateId
	WHERE
		r.CompanyID = p_CompanyID AND
		cra.AccountId = p_AccountID AND
		cra.TimezonesID = p_TimezonesID AND
		FIND_IN_SET (r.Code, p_Codes) != 0
	ORDER BY
		cra.EffectiveDate DESC, cra.created_at DESC;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_CustomerRateUpdate`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerRateUpdate`(
	IN `p_AccountIdList` LONGTEXT ,
	IN `p_TrunkId` VARCHAR(100) ,
	IN `p_TimezonesID` INT,
	IN `p_CustomerRateIDList` LONGTEXT,
	IN `p_Rate` DECIMAL(18, 6) ,
	IN `p_ConnectionFee` DECIMAL(18, 6) ,
	IN `p_EffectiveDate` DATETIME ,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_RoutinePlan` INT,
	IN `p_ModifiedBy` VARCHAR(50)
)
ThisSP:BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        PreviousRate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        CreatedDate DATETIME,
        CreatedBy VARCHAR(50),
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        TrunkID INT,
        TimezonesID INT,
        RoutinePlan INT,
        INDEX tmp_CustomerRates__CustomerRateID (`CustomerRateID`)/*,
        INDEX tmp_CustomerRates__RateID (`RateID`),
        INDEX tmp_CustomerRates__TrunkID (`TrunkID`),
        INDEX tmp_CustomerRates__EffectiveDate (`EffectiveDate`)*/
    );

	-- if p_EffectiveDate null means multiple rates update
	-- and if multiple rates update then we don't allow to change EffectiveDate
	-- we only allow EffectiveDate change when single edit

	-- insert rates in temp table which needs to update
	INSERT INTO tmp_CustomerRates_
	(
		CustomerRateID,
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		TimezonesID,
		RoutinePlan
	)
	SELECT
		cr.CustomerRateID,
		cr.RateID,
		cr.CustomerID,
		p_Interval1 AS Interval1,
		p_IntervalN AS IntervalN,
		p_Rate AS Rate,
		cr.PreviousRate,
		p_ConnectionFee AS ConnectionFee,
		IFNULL(p_EffectiveDate,cr.EffectiveDate) AS EffectiveDate, -- if p_EffectiveDate null take exiting EffectiveDate
		cr.EndDate,
		cr.CreatedDate,
		cr.CreatedBy,
		NOW() AS LastModifiedDate,
		p_ModifiedBy AS LastModifiedBy,
		cr.TrunkID,
		cr.TimezonesID,
		CASE WHEN ctr.TrunkID IS NOT NULL
		THEN p_RoutinePlan
		ELSE NULL
		END AS RoutinePlan
	FROM
		tblCustomerRate cr
	LEFT JOIN tblCustomerTrunk ctr
		ON ctr.TrunkID = cr.TrunkID
		AND ctr.AccountID = cr.CustomerID
		AND ctr.RoutinePlanStatus = 1
	LEFT JOIN
	(
		SELECT
			RateID,CustomerID
		FROM
			tblCustomerRate
		WHERE
			EffectiveDate = p_EffectiveDate AND
			FIND_IN_SET(tblCustomerRate.CustomerRateID,p_CustomerRateIDList) = 0
	) crc ON crc.RateID=cr.RateID AND crc.CustomerID=cr.CustomerID
	WHERE
		 cr.TimezonesID = p_TimezonesID AND FIND_IN_SET(cr.CustomerRateID,p_CustomerRateIDList) != 0 AND crc.RateID IS NULL;

	-- update EndDate to Archive rates which needs to update
	UPDATE
		tblCustomerRate cr
	JOIN
		tmp_CustomerRates_ temp ON cr.CustomerRateID=temp.CustomerRateID
	SET
		cr.EndDate = NOW();

	-- archive rates which rates' EndDate < NOW()
	CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId, p_TimezonesID, p_ModifiedBy);

	-- insert rates in tblCustomerRate with updated values
	INSERT INTO tblCustomerRate
	(
		CustomerRateID,
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		TimezonesID,
		RoutinePlan
	)
	SELECT
		NULL, -- primary key
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		TimezonesID,
		RoutinePlan
	FROM
		tmp_CustomerRates_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldCustomerRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldCustomerRate`(
	IN `p_AccountIds` LONGTEXT,
	IN `p_TrunkIds` LONGTEXT,
	IN `p_TimezonesIDs` LONGTEXT,
	IN `p_DeletedBy` VARCHAR(50)
)
ThisSP:BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/* SET EndDate of current time to older rates */
	-- for example there are 3 rates, today's date is 2018-04-11
	-- 1. Code 	Rate 	EffectiveDate
	-- 1. 91 	0.1 	2018-04-09
	-- 2. 91 	0.2 	2018-04-10
	-- 3. 91 	0.3 	2018-04-11
	/* Then it will delete 2018-04-09 and 2018-04-10 date's rate */
	UPDATE
		tblCustomerRate cr
	INNER JOIN tblCustomerRate cr2
		ON cr2.CustomerID = cr.CustomerID
		AND cr2.TrunkID = cr.TrunkID
		AND cr2.TimezonesID = cr.TimezonesID
		AND cr2.RateID = cr.RateID
	SET
		cr.EndDate=NOW()
	WHERE
		(FIND_IN_SET(cr.CustomerID,p_AccountIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(cr.TimezonesID,p_TimezonesIDs) != 0) AND cr.EffectiveDate <= NOW()) AND
		(FIND_IN_SET(cr2.CustomerID,p_AccountIds) != 0 AND FIND_IN_SET(cr2.TrunkID,p_TrunkIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(cr2.TimezonesID,p_TimezonesIDs) != 0) AND cr2.EffectiveDate <= NOW()) AND
		cr.EffectiveDate < cr2.EffectiveDate AND cr.CustomerRateID != cr2.CustomerRateID;

	-- leave ThisSP;
	/*1. Move Rates which EndDate <= now() */

	INSERT INTO tblCustomerRateArchive
	SELECT DISTINCT  null , -- Primary Key column
		`CustomerRateID`,
		`CustomerID`,
		`TrunkID`,
		`TimezonesID`,
		`RateId`,
		`Rate`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		now() as `created_at`,
		p_DeletedBy AS `created_by`,
		`LastModifiedDate`,
		`LastModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		`RoutinePlan`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM
		tblCustomerRate
	WHERE
		FIND_IN_SET(CustomerID,p_AccountIds) != 0 AND FIND_IN_SET(TrunkID,p_TrunkIds) != 0 AND
		(p_TimezonesIDs IS NULL OR FIND_IN_SET(TimezonesID,p_TimezonesIDs) != 0) AND EndDate <= NOW();


	DELETE  cr
	FROM tblCustomerRate cr
	inner join tblCustomerRateArchive cra
	on cr.CustomerRateID = cra.CustomerRateID
	WHERE  FIND_IN_SET(cr.CustomerID,p_AccountIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(cr.TimezonesID,p_TimezonesIDs) != 0);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_CustomerRateInsert`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerRateInsert`(
	IN `p_CompanyId` INT,
	IN `p_AccountIdList` LONGTEXT ,
	IN `p_TrunkId` VARCHAR(100) ,
	IN `p_TimezonesID` INT,
	IN `p_RateIDList` LONGTEXT,
	IN `p_Rate` DECIMAL(18, 6) ,
	IN `p_ConnectionFee` DECIMAL(18, 6) ,
	IN `p_EffectiveDate` DATETIME ,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_RoutinePlan` INT,
	IN `p_ModifiedBy` VARCHAR(50)
)
ThisSP:BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	INSERT  INTO tblCustomerRate
	(
		RateID ,
		CustomerID ,
		TrunkID ,
		TimezonesID,
		Rate ,
		ConnectionFee,
		EffectiveDate ,
		EndDate,
		Interval1,
		IntervalN ,
		RoutinePlan,
		CreatedDate ,
		LastModifiedBy ,
		LastModifiedDate
	)
	SELECT
		r.RateID,
		r.AccountId ,
		p_TrunkId ,
		p_TimezonesID,
		p_Rate ,
		p_ConnectionFee,
		p_EffectiveDate ,
		NULL AS EndDate,
		p_Interval1,
		p_IntervalN,
		RoutinePlan,
		NOW() ,
		p_ModifiedBy ,
		NOW()
	FROM
	(
		SELECT
			tblRate.RateID ,
			a.AccountId,
			tblRate.CompanyID,
			RoutinePlan
		FROM
			tblRate ,
			(
				SELECT
					a.AccountId,
					CASE WHEN ctr.TrunkID IS NOT NULL
					THEN p_RoutinePlan
					ELSE 0
					END AS RoutinePlan
				FROM
					tblAccount a
				INNER JOIN tblCustomerTrunk ON TrunkID = p_TrunkId
					AND a.AccountId = tblCustomerTrunk.AccountID
					AND tblCustomerTrunk.Status = 1
				LEFT JOIN tblCustomerTrunk ctr
					ON ctr.TrunkID = p_TrunkId
					AND ctr.AccountID = a.AccountID
					AND ctr.RoutinePlanStatus = 1
				WHERE  FIND_IN_SET(a.AccountID,p_AccountIdList) != 0
			) a
		WHERE FIND_IN_SET(tblRate.RateID,p_RateIDList) != 0
	) r
	LEFT JOIN
	(
		SELECT DISTINCT
			c.RateID,
			c.CustomerID as AccountId ,
			c.TrunkID,
			c.EffectiveDate
		FROM
			tblCustomerRate c
		INNER JOIN tblCustomerTrunk ON tblCustomerTrunk.TrunkID = c.TrunkID
			AND tblCustomerTrunk.AccountID = c.CustomerID
			AND tblCustomerTrunk.Status = 1
		WHERE FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 AND c.TrunkID = p_TrunkId AND c.TimezonesID = p_TimezonesID
	) cr ON r.RateID = cr.RateID
		AND r.AccountId = cr.AccountId
		AND r.CompanyID = p_CompanyId
		and cr.EffectiveDate = p_EffectiveDate
	WHERE
		r.CompanyID = p_CompanyId
		and cr.RateID is NULL;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_CustomerBulkRateUpdate`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerBulkRateUpdate`(
	IN `p_AccountIdList` LONGTEXT ,
	IN `p_TrunkId` INT ,
	IN `p_TimezonesID` INT,
	IN `p_CodeDeckId` int,
	IN `p_code` VARCHAR(50) ,
	IN `p_description` VARCHAR(200) ,
	IN `p_CountryId` INT ,
	IN `p_CompanyId` INT ,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_Rate` DECIMAL(18, 6) ,
	IN `p_ConnectionFee` DECIMAL(18, 6) ,
	IN `p_EffectiveDate` DATETIME ,
	IN `p_EndDate` DATETIME ,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_RoutinePlan` INT,
	IN `p_ModifiedBy` VARCHAR(50)
)
BEGIN

 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        PreviousRate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        CreatedDate DATETIME,
        CreatedBy VARCHAR(50),
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        TrunkID INT,
        TimezonesID INT,
        RoutinePlan INT,
        INDEX tmp_CustomerRates__CustomerRateID (`CustomerRateID`)/*,
        INDEX tmp_CustomerRates__RateID (`RateID`),
        INDEX tmp_CustomerRates__TrunkID (`TrunkID`),
        INDEX tmp_CustomerRates__EffectiveDate (`EffectiveDate`)*/
    );

	-- insert rates in temp table which needs to update (based on grid filter)
	INSERT INTO tmp_CustomerRates_
	(
		CustomerRateID,
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		TimezonesID,
		RoutinePlan
	)
	SELECT
		tblCustomerRate.CustomerRateID,
		tblCustomerRate.RateID,
		tblCustomerRate.CustomerID,
		p_Interval1 AS Interval1,
		p_IntervalN AS IntervalN,
		p_Rate AS Rate,
		tblCustomerRate.PreviousRate,
		p_ConnectionFee AS ConnectionFee,
		tblCustomerRate.EffectiveDate,
		tblCustomerRate.EndDate,
		tblCustomerRate.CreatedDate,
		tblCustomerRate.CreatedBy,
		NOW() AS LastModifiedDate,
		p_ModifiedBy AS LastModifiedBy,
		tblCustomerRate.TrunkID,
		tblCustomerRate.TimezonesID,
		tblCustomerRate.RoutinePlan
	FROM
		tblCustomerRate
	INNER JOIN (
		SELECT c.CustomerRateID,
			c.EffectiveDate,
			CASE WHEN ctr.TrunkID IS NOT NULL
			THEN p_RoutinePlan
			ELSE NULL
			END AS RoutinePlan
		FROM   tblCustomerRate c
		INNER JOIN tblCustomerTrunk
			ON tblCustomerTrunk.TrunkID = c.TrunkID
			AND tblCustomerTrunk.AccountID = c.CustomerID
			AND tblCustomerTrunk.Status = 1
		INNER JOIN tblRate r
			ON c.RateID = r.RateID and r.CodeDeckId=p_CodeDeckId
		LEFT JOIN tblCustomerTrunk ctr
			ON ctr.TrunkID = c.TrunkID
			AND ctr.AccountID = c.CustomerID
			AND ctr.RoutinePlanStatus = 1
		WHERE  ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
			AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
			AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) )
			AND
			(
				( p_Effective = 'Now' AND c.EffectiveDate <= NOW() )
				OR
				( p_Effective = 'Future' AND c.EffectiveDate > NOW() )
				OR
				( p_Effective = 'CustomDate' AND c.EffectiveDate <= p_CustomDate AND (c.EndDate IS NULL OR c.EndDate > p_CustomDate) )
				OR
				p_Effective = 'All'
			)
			AND c.TrunkID = p_TrunkId
			AND c.TimezonesID = p_TimezonesID
			AND FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0
	) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID; -- and cr.EffectiveDate = p_EffectiveDate;

	-- if custom date then remove duplicate rates of earlier date
	-- for examle custom date is 2018-05-03 today's date is 2018-05-01 and there are 2 rates available for 1 code
	-- Code	Date
	-- 1204	2018-05-01
	-- 1204	2018-05-03
	-- then it will delete 2018-05-01 from temp table and keeps only 2018-05-03 rate to update
	IF p_Effective = 'CustomDate'
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_2_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates_2_ AS (SELECT * FROM tmp_CustomerRates_);

		DELETE
			n1
		FROM
			tmp_CustomerRates_ n1,
			tmp_CustomerRates_2_ n2
		WHERE
			n1.EffectiveDate < n2.EffectiveDate AND
			n1.RateID = n2.RateID AND
			n1.CustomerID = n2.CustomerID AND
			n1.TrunkID = n2.TrunkId AND
			n1.TimezonesID = n2.TimezonesID AND
			(
				(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
				OR
				(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
			);
	END IF;

	-- update EndDate to Archive rates which needs to update
	UPDATE
		tblCustomerRate cr
	JOIN
		tmp_CustomerRates_ temp ON cr.CustomerRateID=temp.CustomerRateID
	SET
		cr.EndDate = NOW();

	-- archive rates which rates' EndDate < NOW()
	CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId, p_TimezonesID, p_ModifiedBy);

	-- insert rates in tblCustomerRate with updated values
	INSERT INTO tblCustomerRate
	(
		CustomerRateID,
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		TimezonesID,
		RoutinePlan
	)
	SELECT
		NULL, -- primary key
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		TimezonesID,
		RoutinePlan
	FROM
		tmp_CustomerRates_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_CustomerBulkRateInsert`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerBulkRateInsert`(
	IN `p_AccountIdList` LONGTEXT ,
	IN `p_TrunkId` INT ,
	IN `p_TimezonesID` INT,
	IN `p_CodeDeckId` int,
	IN `p_code` VARCHAR(50) ,
	IN `p_description` VARCHAR(200) ,
	IN `p_CountryId` INT ,
	IN `p_CompanyId` INT ,
	IN `p_Rate` DECIMAL(18, 6) ,
	IN `p_ConnectionFee` DECIMAL(18, 6) ,
	IN `p_EffectiveDate` DATETIME ,
	IN `p_EndDate` DATETIME ,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_RoutinePlan` INT,
	IN `p_ModifiedBy` VARCHAR(50)
)
BEGIN

 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	INSERT  INTO tblCustomerRate
	(
		RateID ,
		CustomerID ,
		TrunkID ,
		TimezonesID,
		Rate ,
		ConnectionFee,
		EffectiveDate ,
		EndDate ,
		Interval1,
		IntervalN ,
		RoutinePlan,
		CreatedDate ,
		LastModifiedBy ,
		LastModifiedDate
	)
	SELECT
		r.RateID ,
		r.AccountId ,
		p_TrunkId ,
		p_TimezonesID,
		p_Rate ,
		p_ConnectionFee,
		p_EffectiveDate ,
		p_EndDate ,
		p_Interval1,
		p_IntervalN,
		RoutinePlan,
		NOW() ,
		p_ModifiedBy ,
		NOW()
	FROM
	(
		SELECT
			RateID,Code,AccountId,CompanyID,CodeDeckId,Description,CountryID,RoutinePlan
		FROM
			tblRate ,
			(
				SELECT
					a.AccountId,
					CASE WHEN ctr.TrunkID IS NOT NULL
					THEN p_RoutinePlan
					ELSE NULL
					END AS RoutinePlan
				FROM tblAccount a
				INNER JOIN tblCustomerTrunk
					ON TrunkID = p_TrunkId
					AND a.AccountID    = tblCustomerTrunk.AccountID
					AND tblCustomerTrunk.Status = 1
				LEFT JOIN tblCustomerTrunk ctr
					ON ctr.TrunkID = p_TrunkId
					AND ctr.AccountID = a.AccountID
					AND ctr.RoutinePlanStatus = 1
				WHERE
					FIND_IN_SET(a.AccountID,p_AccountIdList) != 0
			) a
	) r
	LEFT OUTER JOIN
	(
		SELECT DISTINCT
			RateID ,
			c.CustomerID as AccountId ,
			c.TrunkID,
			c.EffectiveDate
		FROM
			tblCustomerRate c
		INNER JOIN tblCustomerTrunk
			ON tblCustomerTrunk.TrunkID = c.TrunkID
			AND tblCustomerTrunk.AccountID = c.CustomerID
			AND tblCustomerTrunk.Status = 1
		WHERE
			FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 AND c.TrunkID = p_TrunkId AND c.TimezonesID = p_TimezonesID
	) cr ON r.RateID = cr.RateID
		AND r.AccountId = cr.AccountId
		AND r.CompanyID = p_CompanyId
		and cr.EffectiveDate = p_EffectiveDate
	WHERE
		r.CompanyID = p_CompanyId
		AND r.CodeDeckId=p_CodeDeckId
		AND ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
		AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
		AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) )
		AND cr.RateID IS NULL;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_CustomerRateClear`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerRateClear`(
	IN `p_AccountIdList` LONGTEXT,
	IN `p_TrunkId` INT,
	IN `p_TimezonesID` INT,
	IN `p_CodeDeckId` int,
	IN `p_CustomerRateId` LONGTEXT,
	IN `p_code` VARCHAR(50),
	IN `p_description` VARCHAR(200),
	IN `p_CountryId` INT,
	IN `p_CompanyId` INT,
	IN `p_ModifiedBy` VARCHAR(50)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/*delete tblCustomerRate
	from tblCustomerRate
	INNER JOIN (
		SELECT c.CustomerRateID
		FROM   tblCustomerRate c
		INNER JOIN tblCustomerTrunk
			ON tblCustomerTrunk.TrunkID = c.TrunkID
			AND tblCustomerTrunk.AccountID = c.CustomerID
			AND tblCustomerTrunk.Status = 1
		INNER JOIN tblRate r
			ON c.RateID = r.RateID and r.CodeDeckId=p_CodeDeckId
		WHERE c.TrunkID = p_TrunkId
			AND FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0
			AND ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
			AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
			AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) )
	) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID;*/

	UPDATE
		tblCustomerRate
	INNER JOIN (
		SELECT
			c.CustomerRateID
		FROM
			tblCustomerRate c
		INNER JOIN tblCustomerTrunk
			ON tblCustomerTrunk.TrunkID = c.TrunkID
			AND tblCustomerTrunk.AccountID = c.CustomerID
			AND tblCustomerTrunk.Status = 1
		INNER JOIN tblRate r
			ON c.RateID = r.RateID and r.CodeDeckId=p_CodeDeckId
		WHERE
			c.TrunkID = p_TrunkId AND
			c.TimezonesID = p_TimezonesID AND
			(
				( -- if single or selected rates delete
					p_CustomerRateId IS NOT NULL AND FIND_IN_SET(c.CustomerRateId,p_CustomerRateId) != 0
				)
				OR
				( -- if bulk rates delete
					p_CustomerRateId IS NULL AND
					FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0  AND
					( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%'))) AND
					( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%'))) AND
					( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) )
				)
			)
	) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID
	SET
		tblCustomerRate.EndDate=NOW();

	CALL prc_ArchiveOldCustomerRate(p_AccountIdList,p_TrunkId,p_TimezonesID,p_ModifiedBy);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ChangeCodeDeckRateTable`;
DELIMITER //
CREATE PROCEDURE `prc_ChangeCodeDeckRateTable`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_RateTableID` INT,
	IN `p_DeletedBy` VARCHAR(50),
	IN `p_Action` INT
)
ThisSP:BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	-- p_Action = 1 = change codedeck = when change codedeck then archive both customer and ratetable rates
	-- p_Action = 2 = change ratetable = when change ratetable then archive only ratetable rates

	IF p_Action = 1
	THEN
		-- set ratetableid = 0, no rate table assign to trunk
		UPDATE
			tblCustomerTrunk
		SET
			RateTableID = 0
		WHERE
			AccountID = p_AccountID AND TrunkID = p_TrunkID;

		-- archive all customer rate against this account and trunk
		UPDATE
			tblCustomerRate
		SET
			EndDate = DATE(NOW())
		WHERE
			CustomerID = p_AccountID AND TrunkID = p_TrunkID;

		-- archive Customer Rates
		call prc_ArchiveOldCustomerRate (p_AccountID,p_TrunkID, NULL,p_DeletedBy);
	END IF;

	-- archive RateTable Rates
	INSERT INTO tblCustomerRateArchive
	(
		`AccountId`,
		`TrunkID`,
		`RateId`,
		`Rate`,
		`EffectiveDate`,
		`EndDate`,
		`created_at`,
		`created_by`,
		`updated_at`,
		`updated_by`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		`Notes`
	)
	SELECT
		p_AccountID AS `AccountId`,
		p_TrunkID AS `TrunkID`,
		`RateID`,
		`Rate`,
		`EffectiveDate`,
		DATE(NOW()) AS `EndDate`,
		DATE(NOW()) AS `created_at`,
		p_DeletedBy AS `created_by`,
		`updated_at`,
		`ModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM
		tblRateTableRate
	WHERE
		RateTableID = p_RateTableID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetLCR`;
DELIMITER //
CREATE PROCEDURE `prc_GetLCR`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_vendor_block` INT,
	IN `p_groupby` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_ShowAllVendorCodes` INT,
	IN `p_isExport` INT
)
ThisSP:BEGIN

		DECLARE v_OffSet_ int;

		DECLARE v_Code VARCHAR(50) ;
		DECLARE v_pointer_ int;
		DECLARE v_rowCount_ int;
		DECLARE v_p_code VARCHAR(50);
		DECLARE v_Codlen_ int;
		DECLARE v_position int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_has_null_position int ;
		DECLARE v_next_position1 VARCHAR(200) ;
		DECLARE v_CompanyCurrencyID_ INT;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_results='utf8';

		-- just for taking codes -

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50),
			FinalRankNumber int
		)
		;

		-- Loop codes

		DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
		CREATE TEMPORARY TABLE tmp_search_code_ (
			Code  varchar(50),
			INDEX Index1 (Code)
		);

		-- searched codes.

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index2 (Code)
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
		CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			rankname INT,
			INDEX IX_Code (Code,rankname)
		)
		;

		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;

		-- Search code based on p_code
		IF (p_ShowAllVendorCodes = 1) THEN

	          insert into tmp_search_code_
	          SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode FROM (
						  SELECT @RowNo  := @RowNo + 1 as RowNo
						  FROM mysql.help_category
						  ,(SELECT @RowNo := 0 ) x
						  limit 15
	          ) x
	         -- INNER JOIN tblRate AS f          ON f.CompanyID = p_companyid  AND f.CodeDeckId = p_codedeckID
			  INNER JOIN (
						  	SELECT distinct Code , Description from tblRate
						  	WHERE CompanyID = p_companyid
							 	AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
	          					AND ( p_Description = ''  OR Description LIKE REPLACE(p_Description,'*', '%') )
			  ) f
	          ON x.RowNo   <= LENGTH(f.Code)
	          order by loopCode   desc;


		ELSE

		insert into tmp_search_code_
			SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode FROM (
					SELECT @RowNo  := @RowNo + 1 as RowNo
					FROM mysql.help_category
						,(SELECT @RowNo := 0 ) x
					limit 15
				) x
				INNER JOIN tblRate AS f
					ON f.CompanyID = p_companyid  AND f.CodeDeckId = p_codedeckID
						 AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR f.Code LIKE REPLACE(p_code,'*', '%') )
						 AND ( p_Description = ''  OR f.Description LIKE REPLACE(p_Description,'*', '%') )
						 AND x.RowNo   <= LENGTH(f.Code)
			order by loopCode   desc;

		END IF;
		-- distinct vendor rates

		### change v 4.17
		IF p_ShowAllVendorCodes = 1 THEN

			INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (
							 SELECT distinct tblVendorRate.AccountId,
							    IFNULL(blockCode.VendorBlockingId, 0) AS BlockingId,
							    IFNULL(blockCountry.CountryId, 0)  as BlockingCountryId,
								 tblAccount.AccountName,
								 tblRate.Code,
								 tblRate.Description,
								 CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
									 THEN
										 tblVendorRate.Rate
								 WHEN  v_CompanyCurrencyID_ = p_CurrencyID
									 THEN
										 (
											 ( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
										 )
								 ELSE
									 (
										 -- Convert to base currrncy and x by RateGenerator Exhange

										 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
										 * (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
									 )
								 END
								as  Rate,
								 ConnectionFee,
								 DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference
							 FROM      tblVendorRate
								 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID

								 INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = p_companyid     AND    tblVendorRate.RateId = tblRate.RateID   AND vt.CodeDeckId = tblRate.CodeDeckId

								 LEFT JOIN tblVendorPreference vp
									 ON vp.AccountId = tblVendorRate.AccountId
											AND vp.TrunkID = tblVendorRate.TrunkID
											AND vp.RateId = tblVendorRate.RateId
								 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																		 AND tblVendorRate.AccountId = blockCode.AccountId
																																		 AND tblVendorRate.TrunkID = blockCode.TrunkID
								 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																				 AND tblVendorRate.AccountId = blockCountry.AccountId
																																				 AND tblVendorRate.TrunkID = blockCountry.TrunkID
							 WHERE
								  ( CHAR_LENGTH(RTRIM(p_code)) = 0 OR tblRate.Code LIKE REPLACE(p_code,'*', '%') )
								 AND (p_Description='' OR tblRate.Description LIKE REPLACE(p_Description,'*','%'))
								 AND ( EffectiveDate <= DATE(p_SelectedEffectiveDate) )
								 AND ( tblVendorRate.EndDate IS NULL OR  tblVendorRate.EndDate > Now() )   -- rate should not end Today
								 AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
								 AND tblAccount.IsVendor = 1
								 AND tblAccount.Status = 1
								 AND tblAccount.CurrencyId is not NULL
								 AND tblVendorRate.TrunkID = p_trunkID
								 AND tblVendorRate.TimezonesID = p_TimezonesID
								  AND
							        (
							           p_vendor_block = 1 OR
							          (
							             p_vendor_block = 0 AND   (
							                 blockCode.RateId IS NULL  AND blockCountry.CountryId IS NULL
							             )
							         )
							       )
								 -- AND blockCode.RateId IS NULL
								 -- AND blockCountry.CountryId IS NULL

						 ) tbl
				order by Code asc;

		ELSE

			INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (
							 SELECT distinct tblVendorRate.AccountId,
							    IFNULL(blockCode.VendorBlockingId, 0) AS BlockingId,
							    IFNULL(blockCountry.CountryId, 0)  as BlockingCountryId,
								 tblAccount.AccountName,
								 tblRate.Code,
								 tblRate.Description,
								 CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
									 THEN
										 tblVendorRate.Rate
								 WHEN  v_CompanyCurrencyID_ = p_CurrencyID
									 THEN
										 (
											 ( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
										 )
								 ELSE
									 (
										 -- Convert to base currrncy and x by RateGenerator Exhange

										 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
										 * (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
									 )
								 END
																																			 as  Rate,
								 ConnectionFee,
								 DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference
							 FROM      tblVendorRate
								 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID

								 INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = p_companyid     AND    tblVendorRate.RateId = tblRate.RateID   AND vt.CodeDeckId = tblRate.CodeDeckId

								 INNER JOIN tmp_search_code_  SplitCode   on tblRate.Code = SplitCode.Code

								 LEFT JOIN tblVendorPreference vp
									 ON vp.AccountId = tblVendorRate.AccountId
											AND vp.TrunkID = tblVendorRate.TrunkID
											AND vp.RateId = tblVendorRate.RateId
								 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																		 AND tblVendorRate.AccountId = blockCode.AccountId
																																		 AND tblVendorRate.TrunkID = blockCode.TrunkID
								 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																				 AND tblVendorRate.AccountId = blockCountry.AccountId
																																				 AND tblVendorRate.TrunkID = blockCountry.TrunkID
							 WHERE
								 ( EffectiveDate <= DATE(p_SelectedEffectiveDate) )
								 AND ( tblVendorRate.EndDate IS NULL OR  tblVendorRate.EndDate > Now() )   -- rate should not end Today
								 AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
								 AND tblAccount.IsVendor = 1
								 AND tblAccount.Status = 1
								 AND tblAccount.CurrencyId is not NULL
								 AND tblVendorRate.TrunkID = p_trunkID
								 AND tblVendorRate.TimezonesID = p_TimezonesID
								  AND
							        (
							           p_vendor_block = 1 OR
							          (
							             p_vendor_block = 0 AND   (
							                 blockCode.RateId IS NULL  AND blockCountry.CountryId IS NULL
							             )
							         )
							       )
								 -- AND blockCode.RateId IS NULL
								 -- AND blockCountry.CountryId IS NULL

						 ) tbl
				order by Code asc;
		END IF;

		-- filter by Effective Dates

		IF p_groupby = 'description' THEN

		INSERT INTO tmp_VendorCurrentRates_
			Select AccountId,max(BlockingId),max(BlockingCountryId) ,max(AccountName),max(Code),Description, MAX(Rate),max(ConnectionFee),max(EffectiveDate),max(TrunkID),max(CountryID),max(RateID),max(Preference)
			FROM (
						 SELECT * ,
							 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_Description = Description AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := AccountID,
							 @prev_TrunkID := TrunkID,
							 @prev_Description := Description,
							 @prev_EffectiveDate := EffectiveDate
						 FROM tmp_VendorCurrentRates1_
							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
						 ORDER BY AccountId, TrunkID, Description, EffectiveDate DESC
					 ) tbl
			WHERE RowID = 1
			group BY AccountId, TrunkID, Description
			order by Description asc;

		ELSE

		INSERT INTO tmp_VendorCurrentRates_
			Select AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT * ,
							 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := AccountID,
							 @prev_TrunkID := TrunkID,
							 @prev_RateId := RateID,
							 @prev_EffectiveDate := EffectiveDate
						 FROM tmp_VendorCurrentRates1_
							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
						 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
					 ) tbl
			WHERE RowID = 1
			order by Code asc;

		END IF;
		-- Collect Codes pressent in vendor Rates from above query.
		/*
               9372     9372    1
               9372     937     2
               9372     93      3
               9372     9       4

    */

 		-- ### change
 		IF p_ShowAllVendorCodes = 1 THEN

 				insert into tmp_all_code_ (RowCode,Code,RowNo)
				select RowCode , loopCode,RowNo
				from (
					 select   RowCode , loopCode,
					 	@RowNo := ( CASE WHEN ( @prev_Code = tbl1.RowCode  ) THEN @RowNo + 1
											 ELSE 1
								END

					 			)      as RowNo,
						 @prev_Code := tbl1.RowCode
				 		from (
						SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode FROM (
								SELECT @RowNo  := @RowNo + 1 as RowNo
								FROM mysql.help_category
									,(SELECT @RowNo := 0 ) x
								limit 15
							) x
							INNER JOIN tmp_search_code_ AS f
								ON  x.RowNo   <= LENGTH(f.Code)
										AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
							INNER JOIN tblRate as tr on f.Code=tr.Code -- AND tr.CodeDeckId=p_codedeckID
								order by RowCode desc,  LENGTH(loopCode) DESC
							) tbl1
						, ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;


 		ELSE

			insert into tmp_all_code_ (RowCode,Code,RowNo)
				select RowCode , loopCode,RowNo
				from (
					 select   RowCode , loopCode,
					 	@RowNo := ( CASE WHEN ( @prev_Code = tbl1.RowCode  ) THEN @RowNo + 1
											 ELSE 1
								END

					 			)      as RowNo,
						 @prev_Code := tbl1.RowCode
				 		from (
						SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode FROM (
								SELECT @RowNo  := @RowNo + 1 as RowNo
								FROM mysql.help_category
									,(SELECT @RowNo := 0 ) x
								limit 15
							) x
							INNER JOIN tmp_search_code_ AS f
								ON  x.RowNo   <= LENGTH(f.Code)
										AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
							INNER JOIN tblRate as tr on f.Code=tr.Code AND tr.CodeDeckId=p_codedeckID
								order by RowCode desc,  LENGTH(loopCode) DESC
							) tbl1
						, ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;

		END IF;


		/*IF (p_isExport = 0)
		THEN

			insert into tmp_code_
				select * from tmp_all_code_
				order by RowCode	LIMIT p_RowspPage OFFSET v_OffSet_ ;

		ELSE

			insert into tmp_code_
				select * from tmp_all_code_
				order by RowCode	  ;

		END IF;
		*/


		IF p_Preference = 1 THEN

			-- Sort by Preference

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					preference_rank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
									@preference_rank := CASE WHEN (@prev_Description     = Description AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																		WHEN (@prev_Description     = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1
																END
								ELSE
									@preference_rank := CASE WHEN (@prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																		WHEN (@prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1
																		END
								END AS preference_rank,

								@prev_Code := Code,
								@prev_Description := Description,
								@prev_Preference := IFNULL(Preference, 5),
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS preference,
								(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Description := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							ORDER BY
									CASE WHEN p_groupby = 'description' THEN
										preference.Description
									ELSE
										 preference.Code
									END ASC ,
								  preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC
						 ) tbl
				WHERE p_isExport = 1 OR (p_isExport = 0 AND preference_rank <= p_Position)
				ORDER BY Code, preference_rank;

		ELSE

			-- Sort by Rate

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					RateRank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
								@rank := CASE WHEN (@prev_Description    = Description AND @prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Description    = Description AND @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END
								ELSE
								@rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END
								END
									AS RateRank,
								@prev_Code := Code,
								@prev_Description := Description,
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS rank,
								(SELECT @rank := 0 , @prev_Code := '' ,  @prev_Description := '' , @prev_Rate := 0) f
							ORDER BY
								CASE WHEN p_groupby = 'description' THEN
									rank.Description
								ELSE
									 rank.Code
								END ,
								rank.Rate,rank.AccountId

							) tbl
				WHERE p_isExport = 1 OR (p_isExport = 0 AND RateRank <= p_Position)
				ORDER BY Code, RateRank;

		END IF;

		-- --------- Split Logic ----------
		/* DESC             MaxMatchRank 1  MaxMatchRank 2
    923 Pakistan :       *923 V1          92 V1
    923 Pakistan :       *92 V2            -

    now take only where  MaxMatchRank =  1
    */


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);

		-- ### change v 4.17
		IF p_ShowAllVendorCodes = 1 THEN

			 insert ignore into tmp_VendorRate_stage_1 (
		   	     RowCode,
		     	Description ,
		     	AccountId ,
		     	AccountName ,
		     	Code ,
		     	Rate ,
		     	ConnectionFee,
		     	EffectiveDate ,
		     	Preference
				)
		         SELECT
		          distinct
		   		 RowCode,
		     	Description ,
		     	AccountId ,
		     	AccountName ,
		     	Code ,
		     	Rate ,
		     	ConnectionFee,
		     	EffectiveDate ,
		     	Preference

		     	from (
				     	select
							CASE WHEN (tr.Code is not null OR tr.Code like concat(v.Code,'%')) THEN
									tr.Code
							ELSE
									v.Code
							END 	as RowCode,
							CASE WHEN (tr.Code is not null OR tr.Code like concat(v.Code,'%')) THEN
									tr.Description
							ELSE
								concat(v.Description,'*')
							END
						 	as Description,
					     	v.AccountId ,
					     	v.AccountName ,
					     	v.Code ,
					     	v.Rate ,
					     	v.ConnectionFee,
					     	v.EffectiveDate ,
					     	v.Preference
				          FROM tmp_VendorRateByRank_ v
				          left join  tmp_all_code_ 		SplitCode   on v.Code = SplitCode.Code

				          LEFT JOIN (	select Code,Description from tblRate where CodeDeckId=p_codedeckID AND
								   	       ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
							          AND ( p_Description = ''  OR Description LIKE REPLACE(p_Description,'*', '%') )
									) tr on tr.Code=SplitCode.Code
		       			  where  SplitCode.Code is not null and (p_isExport = 1 OR (p_isExport = 0 AND rankname <= p_Position))

		  		      ) tmp
					order by AccountID,RowCode desc ,LENGTH(RowCode), Code desc, LENGTH(Code)  desc;

		ELSE

		insert ignore into tmp_VendorRate_stage_1 (
			RowCode,
			AccountId ,
			BlockingId,
			BlockingCountryId,
			AccountName ,
			Code ,
			Rate ,
			ConnectionFee,
			EffectiveDate ,
			Description ,
			Preference
		)
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.BlockingId,
				v.BlockingCountryId,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				tr.Description,
				-- (select Description from tblRate where tblRate.Code =RowCode AND  tblRate.CodeDeckId=p_codedeckID ) as Description ,
				v.Preference
			FROM tmp_VendorRateByRank_ v
				left join  tmp_all_code_ SplitCode   on v.Code = SplitCode.Code
				inner join tblRate tr  on  RowCode = tr.Code AND  tr.CodeDeckId=p_codedeckID
			where  SplitCode.Code is not null and (p_isExport = 1 OR (p_isExport = 0 AND rankname <= p_Position))
			order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;

		END IF;

		insert ignore into tmp_VendorRate_stage_
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.BlockingId,
				v.BlockingCountryId,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference,
				@rank := ( CASE WHEN( @prev_AccountID = v.AccountId  and @prev_RowCode     = RowCode   )
					THEN  @rank + 1
									 ELSE 1
									 END
				) AS MaxMatchRank,
				@prev_RowCode := RowCode	 ,
				@prev_AccountID := v.AccountId
			FROM tmp_VendorRate_stage_1 v
				, (SELECT  @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by AccountID,RowCode desc ;



		IF p_groupby = 'description' THEN

			insert ignore into tmp_VendorRate_
				select
				distinct
				AccountId ,
				max(BlockingId) ,
				max(BlockingCountryId),
				max(AccountName) ,
				max(Code) ,
				max(Rate) ,
				max(ConnectionFee),
				max(EffectiveDate) ,
				Description ,
				max(Preference),
				max(RowCode)
			from tmp_VendorRate_stage_
			where MaxMatchRank = 1
			group by AccountId,Description
			order by AccountId,Description asc;

		ELSE

			insert ignore into tmp_VendorRate_
				select
					distinct
					AccountId ,
					BlockingId ,
					BlockingCountryId,
					AccountName ,
					Code ,
					Rate ,
					ConnectionFee,
					EffectiveDate ,
					Description ,
					Preference,
					RowCode
				from tmp_VendorRate_stage_
				where MaxMatchRank = 1
				order by RowCode desc;
		END IF;






		IF( p_Preference = 0 )
		THEN

			IF p_groupby = 'description' THEN
				/* group by description when preference off */

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN (@prev_Description    = Description AND  @prev_Rate <  Rate ) THEN @rank+1
											 WHEN (@prev_Description    = Description AND  @prev_Rate = Rate ) THEN @rank
											 ELSE
												 1
											 END
								AS FinalRankNumber,
								@prev_Description  := Description,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_Description := '' , @prev_Rate := 0 ) x
							order by Description,Rate,AccountId ASC

						) tbl1
					where
						p_isExport = 1 OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
			ELSE
					/* group by code when preference off */

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN ( @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
										 WHEN ( @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank
										 ELSE
											 1
										 END
								AS FinalRankNumber,
								@prev_RowCode  := RowCode,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 ) x
							order by RowCode,Rate,AccountId ASC

						) tbl1
					where
						p_isExport = 1 OR (p_isExport = 0 AND FinalRankNumber <= p_Position);

			END IF;

		ELSE

			IF p_groupby = 'description' THEN
				/* group by description when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=AccountId AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_Description     = Description AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Description     = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_Description := Description,
								@prev_Preference := Preference,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Description := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by Description ASC ,Preference DESC ,Rate ASC ,AccountId ASC

						) tbl1
					where
						p_isExport = 1 OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
			ELSE
					/* group by code when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_Code := RowCode,
								@prev_Preference := Preference,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC

						) tbl1
					where
						p_isExport = 1 OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
			END IF;

		END IF;


		SET @stm_columns = "";

		-- if not export then columns must be max 10
		IF p_isExport = 0 AND p_Position > 10 THEN
			SET p_Position = 10;
		END IF;

		-- if export then all columns
		IF p_isExport = 1 THEN
			SELECT MAX(FinalRankNumber) INTO p_Position FROM tmp_final_VendorRate_;
		END IF;

		-- columns loop 5,10,50,...
		SET v_pointer_=1;
		WHILE v_pointer_ <= p_Position
		DO

			IF (p_isExport = 0)
			THEN
				SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Description), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL)) AS `POSITION ",v_pointer_,"`,");
			ELSE
				SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Description), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y')), NULL))  AS `POSITION ",v_pointer_,"`,");
			END IF;

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

		SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);

		/* @stm_columns output
		GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
		GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
		GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
		GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
		GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
		*/

		IF (p_isExport = 0)
		THEN
			IF p_groupby = 'description' THEN

				SET @stm_query = CONCAT("SELECT CONCAT(max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.Description ORDER BY t.Description ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

			ELSE

				SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,", @stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

			END IF;

			SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_
				WHERE  ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') ) ;

		END IF;

		IF p_isExport = 1
		THEN

			SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC;");

		END IF;

		PREPARE stm_query FROM @stm_query;
		EXECUTE stm_query;
		DEALLOCATE PREPARE stm_query;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetLCRwithPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_GetLCRwithPrefix`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_vendor_block` INT,
	IN `p_groupby` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_ShowAllVendorCodes` INT,
	IN `p_isExport` INT
)
BEGIN

		DECLARE v_OffSet_ int;
		DECLARE v_Code VARCHAR(50) ;
		DECLARE v_pointer_ int;
		DECLARE v_rowCount_ int;
		DECLARE v_p_code VARCHAR(50);
		DECLARE v_Codlen_ int;
		DECLARE v_position int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_has_null_position int ;
		DECLARE v_next_position1 VARCHAR(200) ;
		DECLARE v_CompanyCurrencyID_ INT;


		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_results='utf8';

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50),
			FinalRankNumber int
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
		CREATE TEMPORARY TABLE tmp_search_code_ (
			Code  varchar(50),
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50),
			Code  varchar(50),

			INDEX Index2 (Code)
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
		CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			rankname INT,
			INDEX IX_Code (Code,rankname)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_block0;
		CREATE TEMPORARY TABLE tmp_block0(
			AccountId INT,
			AccountName VARCHAR(200),
			des VARCHAR(200),
			RateId INT
		);

		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;


		### change v 4.17
		IF p_ShowAllVendorCodes = 1 THEN

				INSERT INTO tmp_VendorCurrentRates1_

				Select DISTINCT AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (

						 SELECT distinct tblVendorRate.AccountId,
						 		IFNULL(blockCode.VendorBlockingId, 0) AS BlockingId,
						 		IFNULL(blockCountry.CountryId, 0)  as BlockingCountryId,
								tblAccount.AccountName,
								tblRate.Code,
								tblRate.Description,
								CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
									THEN
										tblVendorRate.Rate
								WHEN  v_CompanyCurrencyID_ = p_CurrencyID
									THEN
										(
											( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
										)
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
										* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
									)
								END
								as  Rate,
							 ConnectionFee,
																																				DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
							 tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference
						 FROM      tblVendorRate
							 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID

							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID
							 INNER JOIN tblRate ON tblRate.CompanyID = p_companyid   AND    tblVendorRate.RateId = tblRate.RateID  AND vt.CodeDeckId = tblRate.CodeDeckId

							 LEFT JOIN tblVendorPreference vp
								 ON vp.AccountId = tblVendorRate.AccountId
										AND vp.TrunkID = tblVendorRate.TrunkID
										AND vp.RateId = tblVendorRate.RateId
							 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																	 AND tblVendorRate.AccountId = blockCode.AccountId
																																	 AND tblVendorRate.TrunkID = blockCode.TrunkID
							 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																			 AND tblVendorRate.AccountId = blockCountry.AccountId
																																			 AND tblVendorRate.TrunkID = blockCountry.TrunkID
						 WHERE
							 ( CHAR_LENGTH(RTRIM(p_code)) = 0 OR tblRate.Code LIKE REPLACE(p_code,'*', '%') )
							 AND (p_Description='' OR tblRate.Description LIKE REPLACE(p_Description,'*','%'))
							 AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
							-- AND EffectiveDate <= NOW()
							 AND EffectiveDate <= DATE(p_SelectedEffectiveDate)
							 AND (tblVendorRate.EndDate is NULL OR tblVendorRate.EndDate > now() )    -- rate should not end Today
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = p_trunkID
							 AND tblVendorRate.TimezonesID = p_TimezonesID
							 AND
						        (
						           p_vendor_block = 1 OR
						          (
						             p_vendor_block = 0 AND   (
						                 blockCode.RateId IS NULL  AND blockCountry.CountryId IS NULL
						             )
						         )
						       )
							 -- AND blockCode.RateId IS NULL
							-- AND blockCountry.CountryId IS NULL
					 ) tbl
					order by Code asc;

		ELSE

			INSERT INTO tmp_VendorCurrentRates1_

				Select DISTINCT AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (

						 SELECT distinct tblVendorRate.AccountId,
						 		IFNULL(blockCode.VendorBlockingId, 0) AS BlockingId,
						 		IFNULL(blockCountry.CountryId, 0)  as BlockingCountryId,
								tblAccount.AccountName, tblRate.Code, tmpselectedcd.Description,
								CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
									THEN
										tblVendorRate.Rate
								WHEN  v_CompanyCurrencyID_ = p_CurrencyID
									THEN
										(
											( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
										)
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
										* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
									)
								END
								as  Rate,
							 ConnectionFee,
																																				DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
							 tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference
						 FROM      tblVendorRate
							 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID

							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID
							 INNER JOIN tblRate ON tblRate.CompanyID = p_companyid   AND    tblVendorRate.RateId = tblRate.RateID  AND vt.CodeDeckId = tblRate.CodeDeckId


						    INNER JOIN 	(select Code,Description from tblRate where CodeDeckId=p_codedeckID ) tmpselectedcd on tmpselectedcd.Code=tblRate.Code

							 LEFT JOIN tblVendorPreference vp
								 ON vp.AccountId = tblVendorRate.AccountId
										AND vp.TrunkID = tblVendorRate.TrunkID
										AND vp.RateId = tblVendorRate.RateId
							 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																	 AND tblVendorRate.AccountId = blockCode.AccountId
																																	 AND tblVendorRate.TrunkID = blockCode.TrunkID
							 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																			 AND tblVendorRate.AccountId = blockCountry.AccountId
																																			 AND tblVendorRate.TrunkID = blockCountry.TrunkID
						 WHERE
							 ( CHAR_LENGTH(RTRIM(p_code)) = 0 OR tblRate.Code LIKE REPLACE(p_code,'*', '%') )
							 AND (p_Description='' OR tblRate.Description LIKE REPLACE(p_Description,'*','%'))
							 AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
							-- AND EffectiveDate <= NOW()
							 AND EffectiveDate <= DATE(p_SelectedEffectiveDate)
							 AND (tblVendorRate.EndDate is NULL OR tblVendorRate.EndDate > now() )    -- rate should not end Today
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = p_trunkID
							 AND tblVendorRate.TimezonesID = p_TimezonesID
							 AND
						        (
						           p_vendor_block = 1 OR
						          (
						             p_vendor_block = 0 AND   (
						                 blockCode.RateId IS NULL  AND blockCountry.CountryId IS NULL
						             )
						         )
						       )
							 -- AND blockCode.RateId IS NULL
							-- AND blockCountry.CountryId IS NULL
					 ) tbl
					order by Code asc;

			END IF ;


/* for grooup by description  			*/

			IF p_groupby = 'description' THEN

				INSERT INTO tmp_VendorCurrentRates_
				Select AccountId,max(BlockingId),max(BlockingCountryId) ,max(AccountName),max(Code),Description, MAX(Rate),max(ConnectionFee),max(EffectiveDate),max(TrunkID),max(CountryID),max(RateID),max(Preference)
				FROM (

							 SELECT * ,
								 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_Description = Description AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_Description := Description,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

							 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
						 ) tbl
				WHERE RowID = 1
				group BY AccountId, TrunkID, Description
				order by Description asc;



			Else

/* for grooup by code  */

		INSERT INTO tmp_VendorCurrentRates_
				Select AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (
							 SELECT * ,
								 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_RateId := RateID,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
							 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
						 ) tbl
				WHERE RowID = 1
				order by Code asc;

      END IF;



		IF p_Preference = 1 THEN

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					preference_rank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								RateID,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
									@preference_rank := CASE WHEN (@prev_Description     = Description AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																		WHEN (@prev_Description     = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1
																END
								ELSE
									@preference_rank := CASE WHEN (@prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																		WHEN (@prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1
																		END
								END AS preference_rank,

								@prev_Code := Code,
								@prev_Description := Description,
								@prev_Preference := IFNULL(Preference, 5),
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS preference,
								(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Description := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							ORDER BY
									CASE WHEN p_groupby = 'description' THEN
										preference.Description
									ELSE
										 preference.Code
									END ASC ,
								  preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC

						 ) tbl
				WHERE p_isExport = 1 OR (p_isExport = 0 AND preference_rank <= p_Position)
				ORDER BY Code, preference_rank;

		ELSE

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					RateRank
				FROM (
				SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								RateID,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
								@rank := CASE WHEN (@prev_Description    = Description AND @prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Description    = Description AND @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END
								ELSE
								@rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END
								END
									AS RateRank,
								@prev_Code := Code,
								@prev_Description := Description,
								@prev_Rate := Rate
								FROM tmp_VendorCurrentRates_ AS rank,
								(SELECT @rank := 0 , @prev_Code := '' ,  @prev_Description := '' , @prev_Rate := 0) f
								ORDER BY
									CASE WHEN p_groupby = 'description' THEN
										rank.Description
									ELSE
										 rank.Code
									END ,
									rank.Rate,rank.AccountId

							) tbl
				WHERE p_isExport = 1 OR (p_isExport = 0 AND RateRank <= p_Position)
				ORDER BY Code, RateRank;

		END IF;


		-- ### change v 4.17
		IF p_ShowAllVendorCodes = 1 THEN

				insert ignore into tmp_VendorRate_
				select
					distinct
					AccountId ,
					BlockingId ,
					BlockingCountryId,
					AccountName ,
					v.Code ,
					Rate ,
					RateID,
					ConnectionFee,
					EffectiveDate ,
					CASE WHEN (tr.Code is not null) THEN
						tr.Description
					ELSE
						concat(v.Description,'*')
					END
					as Description,
					Preference,
					v.Code as RowCode
				from tmp_VendorRateByRank_ v
				LEFT JOIN (
							select Code,Description from tblRate
							where CodeDeckId=p_codedeckID AND
					   				( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
									AND ( p_Description = ''  OR Description LIKE REPLACE(p_Description,'*', '%') )
					) tr on tr.Code=v.Code

				order by RowCode desc;

		ELSE

			insert ignore into tmp_VendorRate_
				select
					distinct
					AccountId ,
					BlockingId ,
					BlockingCountryId,
					AccountName ,
					Code ,
					Rate ,
					RateID,
					ConnectionFee,
					EffectiveDate ,
					Description ,
					Preference,
					Code as RowCode
				from tmp_VendorRateByRank_
				order by RowCode desc;

		END IF;

		IF( p_Preference = 0 )
		THEN

			 /* if group by description preference off */
			IF p_groupby = 'description' THEN


				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId,
								-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where AccountId=tmp_VendorRate_.AccountId AND Description=tmp_VendorRate_.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
								(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=AccountId AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
								BlockingCountryId,
						      AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN (@prev_Description    = Description AND  @prev_Rate <  Rate ) THEN @rank+1
												 WHEN (@prev_Description    = Description AND  @prev_Rate = Rate ) THEN @rank
												 ELSE
													 1
												 END
								AS FinalRankNumber,
								@prev_Rate  := Rate,
								@prev_Description := Description,
								@prev_RateID  := RateID

							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_Description := '' , @prev_Rate := 0 ) x
							order by Description,Rate,AccountId ASC

						) tbl1
					where
						p_isExport = 1 OR (p_isExport = 0 AND FinalRankNumber <= p_Position);

			ELSE
					/* if group by code start preference off */
					insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN ( @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
												 WHEN ( @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank
												 ELSE
													 1
												 END
								AS FinalRankNumber,
								@prev_RowCode  := RowCode,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 ) x
							order by RowCode,Rate,AccountId ASC

						) tbl1
					where
						p_isExport = 1 OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
					/* if group by code end  preference off*/
			END IF;

		ELSE

			IF p_groupby = 'description' THEN
				/* group by descrion when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId = AccountId  AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_Description    = Description AND @prev_Preference > Preference  )   THEN @preference_rank + 1
											WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
											WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
											ELSE 1 END
									AS FinalRankNumber,
								@prev_Preference := Preference,
								@prev_Description := Description,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Preference := 5, @prev_Description := '',  @prev_Rate := 0) x
							order by Description ASC ,Preference DESC ,Rate ASC ,AccountId ASC

						) tbl1
					where
						p_isExport = 1 OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
				ELSE

						/* group by code when preference on start*/
						insert into tmp_final_VendorRate_
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								FinalRankNumber
							from
								(
									SELECT
										AccountId ,
										BlockingId ,
										BlockingCountryId,
										AccountName ,
										Code ,
										Rate ,
										RateID,
										ConnectionFee,
										EffectiveDate ,
										Description ,
										Preference,
										RowCode,
										@preference_rank := CASE WHEN (@prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1
													WHEN (@prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
													WHEN (@prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
													ELSE 1 END
										AS FinalRankNumber,
										@prev_Code := RowCode,
										@prev_Preference := Preference,
										@prev_Rate := Rate
									from tmp_VendorRate_
										,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
									order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC

								) tbl1
							where
								p_isExport = 1 OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
						/* group by code when preference on end */

				END IF;
		END IF;


		SET @stm_columns = "";

		-- if not export then columns must be max 10
		IF p_isExport = 0 AND p_Position > 10 THEN
			SET p_Position = 10;
		END IF;

		-- if export then all columns
		IF p_isExport = 1 THEN
			SELECT MAX(FinalRankNumber) INTO p_Position FROM tmp_final_VendorRate_;
		END IF;

		-- columns loop 5,10,50,...
		SET v_pointer_=1;
		WHILE v_pointer_ <= p_Position
		DO

			IF (p_isExport = 0)
			THEN
				SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION ",v_pointer_,"`,");
			ELSE
				SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y') ), NULL))AS `POSITION ",v_pointer_,"`,");
			END IF;

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

		SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);

		IF (p_isExport = 0)
		THEN

		   IF p_groupby = 'description' THEN

				SET @stm_query = CONCAT("SELECT	CONCAT(max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.Description ORDER BY t.Description ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

					/*SELECT
					   CONCAT(max(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
					FROM tmp_final_VendorRate_  t
					GROUP BY  t.Description
					ORDER BY t.Description ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;*/

			ELSE

				SET @stm_query = CONCAT("SELECT	CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

					/*SELECT
					   CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
					FROM tmp_final_VendorRate_  t
					GROUP BY  RowCode
					ORDER BY RowCode ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;*/

			END IF;


			SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ where ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') );

		ELSE

			IF p_groupby = 'description' THEN

				SET @stm_query = CONCAT("SELECT CONCAT(max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.Description ORDER BY t.Description ASC;");

				/*SELECT
					CONCAT(max(t.Description)) as Destination,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 5`
				FROM tmp_final_VendorRate_  t
				GROUP BY  t.Description
				ORDER BY t.Description ASC;*/

			ELSE

				SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC;");

				/*SELECT
					CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 5`
				FROM tmp_final_VendorRate_  t
				GROUP BY  RowCode
				ORDER BY RowCode ASC;*/

			END IF;

		END IF;

		PREPARE stm_query FROM @stm_query;
		EXECUTE stm_query;
		DEALLOCATE PREPARE stm_query;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewVendorRate`(
	IN `p_accountId` INT,
	IN `p_trunkId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT
)
ThisSP:BEGIN


    -- @TODO: code cleanup
     DECLARE newstringcode INT(11) DEFAULT 0;
     DECLARE v_pointer_ INT;
     DECLARE v_rowCount_ INT;


	  DECLARE v_AccountCurrencyID_ INT;
	  DECLARE v_CompanyCurrencyID_ INT;


     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_;
    CREATE TEMPORARY TABLE tmp_split_VendorRate_ (
    		`TempVendorRateID` int,
			  `CodeDeckId` int ,
			  `TimezonesID` INT,
			  `Code` varchar(50) ,
			  `Description` varchar(200) ,
			  `Rate` decimal(18, 6) ,
			  `EffectiveDate` Datetime ,
			  `EndDate` Datetime ,
			  `Change` varchar(100) ,
			  `ProcessId` varchar(200) ,
			  `Preference` varchar(100) ,
			  `ConnectionFee` decimal(18, 6),
			  `Interval1` int,
			  `IntervalN` int,
			  `Forbidden` varchar(100) ,
			  `DialStringPrefix` varchar(500) ,
			  INDEX tmp_EffectiveDate (`EffectiveDate`),
			  INDEX tmp_Code (`Code`),
        INDEX tmp_CC (`Code`,`Change`),
			  INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
    CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
    		`TempVendorRateID` int,
			  `CodeDeckId` int ,
			  `TimezonesID` INT,
			  `Code` varchar(50) ,
			  `Description` varchar(200) ,
			  `Rate` decimal(18, 6) ,
			  `EffectiveDate` Datetime ,
			  `EndDate` Datetime ,
			  `Change` varchar(100) ,
			  `ProcessId` varchar(200) ,
			  `Preference` varchar(100) ,
			  `ConnectionFee` decimal(18, 6),
			  `Interval1` int,
			  `IntervalN` int,
			  `Forbidden` varchar(100) ,
			  `DialStringPrefix` varchar(500) ,
			  INDEX tmp_EffectiveDate (`EffectiveDate`),
			  INDEX tmp_Code (`Code`),
        INDEX tmp_CC (`Code`,`Change`),
			  INDEX tmp_Change (`Change`)
    );

    CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);


	-- archive vendor rate code
--	CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId);


	ALTER TABLE `tmp_TempVendorRate_`	ADD Column `NewRate` decimal(18, 6) ;



    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;


	   SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
	   SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


	-- update all rate on newrate with currency conversion.
	update tmp_TempVendorRate_
	SET
	NewRate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    Rate
                )
   WHERE ProcessID=p_processId;


		-- if no error
    IF newstringcode = 0
    THEN
			-- if rates is not in our database (new rates from file) than insert it into ChangeLog
			INSERT INTO tblVendorRateChangeLog(
				TempVendorRateID,
				VendorRateID,
		   	AccountId,
		   	TrunkID,
			   TimezonesID,
				RateId,
		   	Code,
		   	Description,
		   	Rate,
		   	EffectiveDate,
		   	EndDate,
		   	Interval1,
		   	IntervalN,
		   	ConnectionFee,
		   	`Action`,
		   	ProcessID,
		   	created_at
			)
			SELECT
				tblTempVendorRate.TempVendorRateID,
				tblVendorRate.VendorRateID,
			   p_accountId AS AccountId,
			   p_trunkId AS TrunkID,
			   tblTempVendorRate.TimezonesID,
			   tblRate.RateId,
			   tblTempVendorRate.Code,
			   tblTempVendorRate.Description,
			   tblTempVendorRate.Rate,
			  	tblTempVendorRate.EffectiveDate,
				tblTempVendorRate.EndDate ,
			  	IFNULL(tblTempVendorRate.Interval1,tblRate.Interval1 ) as Interval1,		-- take interval from file and update in tblRate if not changed in service
			  	IFNULL(tblTempVendorRate.IntervalN , tblRate.IntervalN ) as IntervalN,
			   tblTempVendorRate.ConnectionFee,
			   'New' AS `Action`,
			   p_processId AS ProcessID,
			   now() AS created_at
			FROM tmp_TempVendorRate_ as tblTempVendorRate
			LEFT JOIN tblRate
			   ON tblTempVendorRate.Code = tblRate.Code AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblVendorRate
				ON tblRate.RateID = tblVendorRate.RateId AND tblVendorRate.AccountId = p_accountId   AND tblVendorRate.TrunkId = p_trunkId AND tblVendorRate.TimezonesID = tblTempVendorRate.TimezonesID
				AND tblVendorRate.EffectiveDate  <= date(now())
		   WHERE tblTempVendorRate.ProcessID=p_processId AND tblVendorRate.VendorRateID IS NULL
              AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
				 -- AND tblTempVendorRate.EffectiveDate != '0000-00-00 00:00:00';


   		-- loop through effective date
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
      FROM tmp_TempVendorRate_
        ,(SELECT @row_num := 0) x
      WHERE  ProcessID = p_processId
     -- AND EffectiveDate <> '0000-00-00 00:00:00'
      group by EffectiveDate
      order by EffectiveDate asc;


    SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
				SET @row_num = 0;

	         -- update  previous rate with all latest recent entriy of previous effective date

                       INSERT INTO tblVendorRateChangeLog(
                           TempVendorRateID,
                           VendorRateID,
                           AccountId,
                           TrunkID,
                           TimezonesID,
                           RateId,
                           Code,
                           Description,
                           Rate,
                           EffectiveDate,
                           EndDate,
                           Interval1,
                           IntervalN,
                           ConnectionFee,
                           `Action`,
                           ProcessID,
                           created_at
                       )
               			  SELECT
               			  distinct
                       tblTempVendorRate.TempVendorRateID,
                       VendorRate.VendorRateID,
                       p_accountId AS AccountId,
                       p_trunkId AS TrunkID,
                       tblTempVendorRate.TimezonesID,
                       VendorRate.RateId,
                       tblRate.Code,
                       tblRate.Description,
                       tblTempVendorRate.Rate,
                       tblTempVendorRate.EffectiveDate,
                       tblTempVendorRate.EndDate ,
                       tblTempVendorRate.Interval1,
                       tblTempVendorRate.IntervalN,
                       tblTempVendorRate.ConnectionFee,
                       IF(tblTempVendorRate.NewRate > VendorRate.Rate, 'Increased', IF(tblTempVendorRate.NewRate < VendorRate.Rate, 'Decreased','')) AS `Action`,
                       p_processid AS ProcessID,
                       now() AS created_at
                       FROM
                         (
                         -- get all rates RowID = 1 to remove old to old effective date

                         select distinct tmp.* ,
                         @row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                         @prev_RateId := tmp.RateID,
                         @prev_EffectiveDate := tmp.EffectiveDate
                         FROM
                         (


                         				select distinct vr1.*
	                         	     from tblVendorRate vr1
			                          LEFT outer join tblVendorRate vr2
												on vr1.AccountID = vr2.AccountID
												and vr1.TrunkID = vr2.TrunkID
												and vr1.RateID = vr2.RateID
												AND vr1.TimezonesID = vr2.TimezonesID
												AND vr2.EffectiveDate  = @EffectiveDate
			                          where
			                          vr1.AccountID = p_accountId AND vr1.TrunkID = p_trunkId
			                          and vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate)   -- <= because if same day rate change need to log
			                          order by vr1.RateID desc ,vr1.EffectiveDate desc


                         ) tmp ,
								 ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
								  order by RateID desc , EffectiveDate desc


                         ) VendorRate
                      JOIN tblRate
                         ON tblRate.CompanyID = p_companyId
                         AND tblRate.RateID = VendorRate.RateId
                      JOIN tmp_TempVendorRate_ tblTempVendorRate
                         ON tblTempVendorRate.Code = tblRate.Code
                    			AND tblTempVendorRate.TimezonesID = VendorRate.TimezonesID
								 	AND tblTempVendorRate.ProcessID=p_processId
                         --	AND  tblTempVendorRate.EffectiveDate <> '0000-00-00 00:00:00'
								 AND  VendorRate.EffectiveDate <= tblTempVendorRate.EffectiveDate -- <= because if same day rate change need to log
               				  AND tblTempVendorRate.EffectiveDate =  @EffectiveDate

               				   AND VendorRate.RowID = 1

                       WHERE
                         VendorRate.AccountId = p_accountId
                         AND VendorRate.TrunkId = p_trunkId
                         -- AND tblTempVendorRate.EffectiveDate <> '0000-00-00 00:00:00'
                         AND tblTempVendorRate.Code IS NOT NULL
                         AND tblTempVendorRate.ProcessID=p_processId
                         AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;


    		IF p_list_option = 1 -- p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
    		THEN

    			-- get rates which is not in file and insert it into ChangeLog
         	          INSERT INTO tblVendorRateChangeLog(
				VendorRateID,
			   	AccountId,
			   	TrunkID,
			   	TimezonesID,
				RateId,
			   	Code,
			   	Description,
			   	Rate,
			   	EffectiveDate,
			   	EndDate,
			   	Interval1,
			   	IntervalN,
			   	ConnectionFee,
			   	`Action`,
			   	ProcessID,
			   	created_at
				)
				SELECT DISTINCT
                    tblVendorRate.VendorRateID,
                    p_accountId AS AccountId,
                    p_trunkId AS TrunkID,
                    tblVendorRate.TimezonesID,
                    tblVendorRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblVendorRate.Rate,
                    tblVendorRate.EffectiveDate,
                    tblVendorRate.EndDate ,
                    tblVendorRate.Interval1,
                    tblVendorRate.IntervalN,
                    tblVendorRate.ConnectionFee,
                    'Deleted' AS `Action`,
			   			p_processId AS ProcessID,
                    now() AS deleted_at
                    FROM tblVendorRate
                    JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyId
                    LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
                    ON tblTempVendorRate.Code = tblRate.Code
						  AND tblTempVendorRate.TimezonesID = tblVendorRate.TimezonesID
						  AND tblTempVendorRate.ProcessID=p_processId
						  AND (
						  			-- normal condition
								  ( tblTempVendorRate.EndDate is null AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
							  	OR
							  		-- skip records just to avoid duplicate records in tblVendorRateChangeLog tabke - when EndDate is given with delete
								  ( tblTempVendorRate.EndDate is not null AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
							  )
                    WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
                    AND ( tblVendorRate.EndDate is null OR tblVendorRate.EndDate <= date(now()) )
                    AND tblTempVendorRate.Code IS NULL
                    ORDER BY VendorRateID ASC;

    		END IF;


            INSERT INTO tblVendorRateChangeLog(
				VendorRateID,
			   	AccountId,
			   	TrunkID,
			   	TimezonesID,
				RateId,
			   	Code,
			   	Description,
			   	Rate,
			   	EffectiveDate,
			   	EndDate,
			   	Interval1,
			   	IntervalN,
			   	ConnectionFee,
			   	`Action`,
			   	ProcessID,
			   	created_at
				)
				SELECT DISTINCT
                    tblVendorRate.VendorRateID,
                    p_accountId AS AccountId,
                    p_trunkId AS TrunkID,
                    tblVendorRate.TimezonesID,
                    tblVendorRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblVendorRate.Rate,
                    tblVendorRate.EffectiveDate,
                    IFNULL(tblTempVendorRate.EndDate,tblVendorRate.EndDate) as  EndDate ,
                    tblVendorRate.Interval1,
                    tblVendorRate.IntervalN,
                    tblVendorRate.ConnectionFee,
                    'Deleted' AS `Action`,
			   			p_processId AS ProcessID,
                    now() AS deleted_at
                    FROM tblVendorRate
                    JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyId
                    LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
	                    ON tblRate.Code = tblTempVendorRate.Code
	                    AND tblTempVendorRate.TimezonesID = tblVendorRate.TimezonesID
							  AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
							   AND tblTempVendorRate.ProcessID=p_processId
                    -- AND tblTempVendorRate.EndDate <= date(now())
         	           -- AND tblTempVendorRate.ProcessID=p_processId
                    WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
               	     -- AND tblVendorRate.EndDate <= date(now())
            	        AND tblTempVendorRate.Code IS NOT NULL
                    ORDER BY VendorRateID ASC;



    END IF;

    SELECT * FROM tmp_JobLog_;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_checkDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_checkDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE     v_CodeDeckId_ INT ;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;


	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_VendorRateDialString_` (
		`TempVendorRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_VendorRateDialString_2` (
		`TempVendorRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_VendorRateDialString_3` (
		`TempVendorRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitVendorRate(p_processId,p_dialcodeSeparator);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_VendorRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_VendorRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_VendorRate_2 as (SELECT * FROM tmp_split_VendorRate_);

	/*DELETE n1 FROM tmp_split_VendorRate_ n1
	INNER JOIN
	(
	SELECT MAX(TempVendorRateID) AS TempVendorRateID,EffectiveDate,Code
	FROM tmp_split_VendorRate_2 WHERE ProcessId = p_processId
	GROUP BY Code,EffectiveDate
	HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code
	AND n2.EffectiveDate = n1.EffectiveDate AND n1.TempVendorRateID < n2.TempVendorRateID
	WHERE n1.ProcessId = p_processId;*/

	-- v4.16
	INSERT INTO tmp_TempVendorRate_
	SELECT DISTINCT
		`TempVendorRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`Code`,
		`Description`,
		`Rate`,
		`EffectiveDate`,
		`EndDate`,
		`Change`,
		`ProcessId`,
		`Preference`,
		`ConnectionFee`,
		`Interval1`,
		`IntervalN`,
		`Forbidden`,
		`DialStringPrefix`
	FROM tmp_split_VendorRate_
	WHERE tmp_split_VendorRate_.ProcessId = p_processId;

	SELECT CodeDeckId INTO v_CodeDeckId_
		FROM tmp_TempVendorRate_
	WHERE ProcessId = p_processId  LIMIT 1;

	UPDATE tmp_TempVendorRate_ as tblTempVendorRate
		LEFT JOIN tblRate
		ON tblRate.Code = tblTempVendorRate.Code
		AND tblRate.CompanyID = p_companyId
		AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
		AND tblRate.CodeDeckId =  v_CodeDeckId_
	SET
		tblTempVendorRate.Interval1 = CASE WHEN tblTempVendorRate.Interval1 is not null  and tblTempVendorRate.Interval1 > 0
									THEN
										tblTempVendorRate.Interval1
									ELSE
									CASE WHEN tblRate.Interval1 is not null
									THEN
										tblRate.Interval1
									ELSE
										1
									END
									END,
		tblTempVendorRate.IntervalN = CASE WHEN tblTempVendorRate.IntervalN is not null  and tblTempVendorRate.IntervalN > 0
									THEN
										tblTempVendorRate.IntervalN
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
		UPDATE tmp_TempVendorRate_
			SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
			WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempVendorRate_
			SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

	END IF;


	SELECT count(*) INTO totalduplicatecode FROM(
	SELECT count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;


	IF  totalduplicatecode > 0
	THEN


		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT code, 1 as a FROM(
		SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
		CONCAT(code , ' DUPLICATE CODE')
		FROM(
		SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;

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
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds
		ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))

		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

		IF totaldialstringcode > 0
		THEN

		/*INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds

		ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');*/

		INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
		SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds

		ON vr.DialStringPrefix = ds.DialString AND ds.DialStringID = p_dialStringId
		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		AND (vr.DialStringPrefix is not null AND vr.DialStringPrefix != '')
		AND (Code is not null AND Code != '');

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
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds
		ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))

		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds

		ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

		END IF;

		IF totaldialstringcode = 0
		THEN

			INSERT INTO tmp_VendorRateDialString_
				SELECT DISTINCT
				`TempVendorRateID`,
				`CodeDeckId`,
				`TimezonesID`,
				`DialString`,
				CASE WHEN ds.Description IS NULL OR ds.Description = ''
				THEN
				tblTempVendorRate.Description
				ELSE
				ds.Description
				END
				AS Description,
				`Rate`,
				`EffectiveDate`,
				`EndDate`,
				`Change`,
				`ProcessId`,
				`Preference`,
				`ConnectionFee`,
				`Interval1`,
				`IntervalN`,
				tblTempVendorRate.Forbidden as Forbidden ,
				tblTempVendorRate.DialStringPrefix as DialStringPrefix
			FROM tmp_TempVendorRate_ as tblTempVendorRate
			INNER JOIN tmp_DialString_ ds

			ON ( (tblTempVendorRate.Code = ds.ChargeCode AND tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' AND tblTempVendorRate.DialStringPrefix =  ds.DialString AND tblTempVendorRate.Code = ds.ChargeCode  ))

			WHERE tblTempVendorRate.ProcessId = p_processId
			AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


			/*				INSERT INTO tmp_VendorRateDialString_2
			SELECT * FROM tmp_VendorRateDialString_; */

			INSERT INTO tmp_VendorRateDialString_2
			SELECT *  FROM tmp_VendorRateDialString_ where DialStringPrefix!='';

			Delete From tmp_VendorRateDialString_
			Where DialStringPrefix = ''
			And Code IN (Select DialStringPrefix From tmp_VendorRateDialString_2);

			INSERT INTO tmp_VendorRateDialString_3
			SELECT * FROM tmp_VendorRateDialString_;

			/*

			INSERT INTO tmp_VendorRateDialString_3
			SELECT vrs1.* from tmp_VendorRateDialString_2 vrs1
			LEFT JOIN tmp_VendorRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId
			AND vrs1.EffectiveDate=vrs2.EffectiveDate
			AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
			WHERE ( (vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL)); */

			DELETE  FROM tmp_TempVendorRate_ WHERE  ProcessId = p_processId;

			INSERT INTO tmp_TempVendorRate_(
				`TempVendorRateID`,
				CodeDeckId,
				TimezonesID,
				Code,
				Description,
				Rate,
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
				`TempVendorRateID`,
				`CodeDeckId`,
				`TimezonesID`,
				`Code`,
				`Description`,
				`Rate`,
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
			FROM tmp_VendorRateDialString_3;

			UPDATE tmp_TempVendorRate_ as tblTempVendorRate
			JOIN tmp_DialString_ ds

			ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  ))
			AND tblTempVendorRate.ProcessId = p_processId
			AND ds.Forbidden = 1
			SET tblTempVendorRate.Forbidden = 'B';

			UPDATE tmp_TempVendorRate_ as  tblTempVendorRate
			JOIN tmp_DialString_ ds

			ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  ))
			AND tblTempVendorRate.ProcessId = p_processId
			AND ds.Forbidden = 0
			SET tblTempVendorRate.Forbidden = 'UB';

			END IF;

		END IF;

	END IF;


END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitVendorRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempVendorRateID_ INT;
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN





	DROP TEMPORARY TABLE IF EXISTS `my_splits`;
	CREATE TEMPORARY TABLE `my_splits` (
		`TempVendorRateID` INT(11) NULL DEFAULT NULL,
		`Code` Text NULL DEFAULT NULL,
		`CountryCode` Text NULL DEFAULT NULL
	);

  SET i = 1;
  REPEAT
    INSERT INTO my_splits (TempVendorRateID, Code, CountryCode)
      SELECT TempVendorRateID , FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempVendorRate
      WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
			 AND ProcessId = p_processId;
    SET i = i + 1;
    UNTIL ROW_COUNT() = 0
  END REPEAT;

  UPDATE my_splits SET Code = trim(Code);

	INSERT INTO my_splits (TempVendorRateID, Code, CountryCode)
	SELECT TempVendorRateID , Code, CountryCode  FROM tblTempVendorRate
	WHERE (CountryCode IS NOT NULL AND CountryCode <> '') AND (Code IS NULL OR Code = '')
	AND ProcessId = p_processId;



  DROP TEMPORARY TABLE IF EXISTS tmp_newvendor_splite_;
	CREATE TEMPORARY TABLE tmp_newvendor_splite_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempVendorRateID INT(11) NULL DEFAULT NULL,
		Code VARCHAR(500) NULL DEFAULT NULL,
		CountryCode VARCHAR(500) NULL DEFAULT NULL
	);

	INSERT INTO tmp_newvendor_splite_(TempVendorRateID,Code,CountryCode)
	SELECT
		TempVendorRateID,
		Code,
		CountryCode
	FROM my_splits
	WHERE Code like '%-%'
		AND TempVendorRateID IS NOT NULL;



	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newvendor_splite_);

	WHILE v_pointer_ <= v_rowCount_
	DO
		SET v_TempVendorRateID_ = (SELECT TempVendorRateID FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);
		SET v_Code_ = (SELECT Code FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);
		SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);

		Call prc_SplitAndInsertVendorRate(v_TempVendorRateID_,v_Code_,v_CountryCode_);

	SET v_pointer_ = v_pointer_ + 1;
	END WHILE;


	DELETE FROM my_splits
		WHERE Code like '%-%'
			AND TempVendorRateID IS NOT NULL;

	DELETE FROM my_splits
		WHERE (Code = '' OR Code IS NULL) AND (CountryCode = '' OR CountryCode IS NULL);

	 INSERT INTO tmp_split_VendorRate_
	SELECT DISTINCT
		   my_splits.TempVendorRateID as `TempVendorRateID`,
		   `CodeDeckId`,
		   `TimezonesID`,
		   CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
		   `Description`,
			`Rate`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Forbidden`,
			`DialStringPrefix`
		 FROM my_splits
		   INNER JOIN tblTempVendorRate
				ON my_splits.TempVendorRateID = tblTempVendorRate.TempVendorRateID
		  WHERE	tblTempVendorRate.ProcessId = p_processId;

	END IF;




	IF p_dialcodeSeparator = 'null'
	THEN

		INSERT INTO tmp_split_VendorRate_
		SELECT DISTINCT
			  `TempVendorRateID`,
			  `CodeDeckId`,
			  `TimezonesID`,
			   CONCAT(IFNULL(tblTempVendorRate.CountryCode,''),tblTempVendorRate.Code) as Code,
			   `Description`,
				`Rate`,
				`EffectiveDate`,
				`EndDate`,
				`Change`,
				`ProcessId`,
				`Preference`,
				`ConnectionFee`,
				`Interval1`,
				`IntervalN`,
				`Forbidden`,
				`DialStringPrefix`
			 FROM tblTempVendorRate
			  WHERE ProcessId = p_processId;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessVendorRate`(
	IN `p_accountId` INT,
	IN `p_trunkId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
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
		DECLARE v_AccountCurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;


		DECLARE v_pointer_ INT;
		DECLARE v_rowCount_ INT;


	  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_;
    CREATE TEMPORARY TABLE tmp_split_VendorRate_ (
    		`TempVendorRateID` int,
			  `CodeDeckId` int ,
			  `TimezonesID` INT,
			  `Code` varchar(50) ,
			  `Description` varchar(200) ,
			  `Rate` decimal(18, 6) ,
			  `EffectiveDate` Datetime ,
			  `EndDate` Datetime ,
			  `Change` varchar(100) ,
			  `ProcessId` varchar(200) ,
			  `Preference` varchar(100) ,
			  `ConnectionFee` decimal(18, 6),
			  `Interval1` int,
			  `IntervalN` int,
			  `Forbidden` varchar(100) ,
			  `DialStringPrefix` varchar(500) ,
			  INDEX tmp_EffectiveDate (`EffectiveDate`),
			  INDEX tmp_Code (`Code`),
        INDEX tmp_CC (`Code`,`Change`),
			  INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
    CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
		    TempVendorRateID int,
			  `CodeDeckId` int ,
			  `TimezonesID` INT,
			  `Code` varchar(50) ,
			  `Description` varchar(200) ,
			  `Rate` decimal(18, 6) ,
			  `EffectiveDate` Datetime ,
			  `EndDate` Datetime ,
			  `Change` varchar(100) ,
			  `ProcessId` varchar(200) ,
			  `Preference` varchar(100) ,
			  `ConnectionFee` decimal(18, 6),
			  `Interval1` int,
			  `IntervalN` int,
			  `Forbidden` varchar(100) ,
			  `DialStringPrefix` varchar(500) ,
			  INDEX tmp_EffectiveDate (`EffectiveDate`),
			  INDEX tmp_Code (`Code`),
        INDEX tmp_CC (`Code`,`Change`),
			  INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_Delete_VendorRate;
    CREATE TEMPORARY TABLE tmp_Delete_VendorRate (
        VendorRateID INT,
        AccountId INT,
        TrunkID INT,
		  TimezonesID INT,
        RateId INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
		EndDate Datetime ,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        deleted_at DATETIME,
        INDEX tmp_VendorRateDiscontinued_VendorRateID (`VendorRateID`)
    );


	/*  1.  Check duplicate code, dial string   */
    CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 -- LEAVE ThisSP;


	-- if no error
    IF newstringcode = 0
    THEN


		/*  2.  Send Today EndDate to rates which are marked deleted in review screen  */
		/*  3.  Update interval in temp table */

		-- if review
		IF (SELECT count(*) FROM tblVendorRateChangeLog WHERE ProcessID = p_processId ) > 0 THEN

			-- v4.16 update end date given from tblVendorRateChangeLog for deleted rates.
			UPDATE
			tblVendorRate vr
			INNER JOIN tblVendorRateChangeLog  vrcl
                    on vrcl.VendorRateID = vr.VendorRateID
			SET
			vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
			AND vrcl.`Action`  ='Deleted';

			-- update end date on temp table
			 UPDATE tmp_TempVendorRate_ tblTempVendorRate
          JOIN tblVendorRateChangeLog vrcl
          		 ON  vrcl.ProcessId = p_processId
          		 AND vrcl.Code = tblTempVendorRate.Code
        			 -- AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
        	SET
			   tblTempVendorRate.EndDate = vrcl.EndDate
		     WHERE
		     vrcl.`Action` = 'Deleted'
        	  AND vrcl.EndDate IS NOT NULL ;


			-- update intervals.
		   UPDATE tmp_TempVendorRate_ tblTempVendorRate
          JOIN tblVendorRateChangeLog vrcl
          		 ON  vrcl.ProcessId = p_processId
          		 AND vrcl.Code = tblTempVendorRate.Code
        			 -- AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
        	SET
			   tblTempVendorRate.Interval1 = vrcl.Interval1 ,
				tblTempVendorRate.IntervalN = vrcl.IntervalN
		     WHERE
		     vrcl.`Action` = 'New'
        	  AND vrcl.Interval1 IS NOT NULL
			  AND vrcl.IntervalN IS NOT NULL ;



			/*IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated End Date of Deleted Records. ' );
			END IF;
			*/


		END IF;

		/*  4.  Update EndDate to Today if Replace All existing */

		IF  p_replaceAllRates = 1
		THEN

          /*
				DELETE FROM tblVendorRate
				WHERE AccountId = p_accountId
				AND TrunkID = p_trunkId;
          */

			UPDATE tblVendorRate
			SET tblVendorRate.EndDate = date(now())
			WHERE AccountId = p_accountId
			AND TrunkID = p_trunkId;




			/*
			IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' Records Removed.   ' );
			END IF;
			*/

		END IF;

		/* 5. If Complete File, remove rates not exists in file  */

		IF p_list_option = 1    -- v4.16 p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
		THEN


			-- v4.16 get rates which is not in file and insert it into temp table
			INSERT INTO tmp_Delete_VendorRate(
							VendorRateID ,
							AccountId,
							TrunkID ,
							TimezonesID,
							RateId,
							Code ,
							Description ,
							Rate ,
							EffectiveDate ,
							EndDate ,
							Interval1 ,
							IntervalN ,
							ConnectionFee ,
							deleted_at
			)
			SELECT DISTINCT
                    tblVendorRate.VendorRateID,
                    p_accountId AS AccountId,
                    p_trunkId AS TrunkID,
                    tblVendorRate.TimezonesID,
                    tblVendorRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblVendorRate.Rate,
                    tblVendorRate.EffectiveDate,
                    IFNULL(tblVendorRate.EndDate,date(now())) ,
                    tblVendorRate.Interval1,
                    tblVendorRate.IntervalN,
                    tblVendorRate.ConnectionFee,
                    now() AS deleted_at
                    FROM tblVendorRate
	                    JOIN tblRate
	                   		 ON tblRate.RateID = tblVendorRate.RateId
									  	AND tblRate.CompanyID = p_companyId
	                    LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
	                   		 ON tblTempVendorRate.Code = tblRate.Code
	                   		 	 AND tblTempVendorRate.TimezonesID = tblVendorRate.TimezonesID
	                   			 AND  tblTempVendorRate.ProcessId = p_processId
	                   			 AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
	                    WHERE tblVendorRate.AccountId = p_accountId
	                   		 AND tblVendorRate.TrunkId = p_trunkId
	                   		 AND tblTempVendorRate.Code IS NULL
	                   		 AND ( tblVendorRate.EndDate is NULL OR tblVendorRate.EndDate <= date(now()) )

                    ORDER BY VendorRateID ASC;


							/*IF (FOUND_ROWS() > 0) THEN
								INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as Not exists in File' );
							END IF;*/

			-- set end date will remove at bottom in archive proc
			UPDATE tblVendorRate
				JOIN tmp_Delete_VendorRate ON tblVendorRate.VendorRateID = tmp_Delete_VendorRate.VendorRateID
				SET tblVendorRate.EndDate = date(now())
			WHERE
				tblVendorRate.AccountId = p_accountId
		      AND tblVendorRate.TrunkId = p_trunkId;

		-- 	CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId);


		END IF;

		/* 6. Move Rates to archive which has EndDate <= now()  */
			-- move to archive if EndDate is <= now()
		IF ( (SELECT count(*) FROM tblVendorRate WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW() )  > 0  ) THEN

				-- move to archive
				INSERT INTO tblVendorRateArchive
				SELECT DISTINCT  null , -- Primary Key column
				`VendorRateID`,
				`AccountId`,
				`TrunkID`,
				`TimezonesID`,
				`RateId`,
				`Rate`,
				`EffectiveDate`,
				IFNULL(`EndDate`,date(now())) as EndDate,
				`updated_at`,
				`created_at`,
				`created_by`,
				`updated_by`,
				`Interval1`,
				`IntervalN`,
				`ConnectionFee`,
				`MinimumCost`,
				  concat('Ends Today rates @ ' , now() ) as `Notes`
			      FROM tblVendorRate
			      WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW();

			      delete from tblVendorRate
			      WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW();


		END IF;

		/* 7. Add New code in codedeck  */

		IF  p_addNewCodesToCodeDeck = 1
            THEN
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
                        tblTempVendorRate.Code,
                        tblTempVendorRate.Description,
                        tblTempVendorRate.CodeDeckId,
                        tblTempVendorRate.Interval1,
                        tblTempVendorRate.IntervalN
                    FROM tmp_TempVendorRate_  as tblTempVendorRate
                    LEFT JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                    WHERE tblRate.RateID IS NULL
                    AND tblTempVendorRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                ) vc;


						/*IF (FOUND_ROWS() > 0) THEN
								INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Code Inserted into Codedeck ' );
						END IF;*/


						/*
               	SELECT GROUP_CONCAT(Code) into errormessage FROM(
                    SELECT DISTINCT
                        tblTempVendorRate.Code as Code, 1 as a
                    FROM tmp_TempVendorRate_  as tblTempVendorRate
                    INNER JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							      WHERE tblRate.CountryID IS NULL
                    AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                ) as tbl GROUP BY a;

                IF errormessage IS NOT NULL
                THEN
                    INSERT INTO tmp_JobLog_ (Message)
                    	  SELECT DISTINCT
                          CONCAT(tblTempVendorRate.Code , ' INVALID CODE - COUNTRY NOT FOUND')
                        FROM tmp_TempVendorRate_  as tblTempVendorRate
                        INNER JOIN tblRate
                        ON tblRate.Code = tblTempVendorRate.Code
                          AND tblRate.CompanyID = p_companyId
                          AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        WHERE tblRate.CountryID IS NULL
                          AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
					 	    END IF; */
            ELSE
                SELECT GROUP_CONCAT(code) into errormessage FROM(
                    SELECT DISTINCT
                        c.Code as code, 1 as a
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description
                        FROM tmp_TempVendorRate_  as tblTempVendorRate
                        LEFT JOIN tblRate
				                ON tblRate.Code = tblTempVendorRate.Code
                          AND tblRate.CompanyID = p_companyId
                          AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        WHERE tblRate.RateID IS NULL
                          AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
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
                                tblTempVendorRate.Code,
                                tblTempVendorRate.Description
                            FROM tmp_TempVendorRate_  as tblTempVendorRate
                            LEFT JOIN tblRate
                            ON tblRate.Code = tblTempVendorRate.Code
                              AND tblRate.CompanyID = p_companyId
                              AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                            WHERE tblRate.RateID IS NULL
                              AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                        ) as tbl;
					 	    END IF;
            END IF;

			/* 8. delete rates which will be map as deleted */

				-- delete rates which will be map as deleted
            UPDATE tblVendorRate
                    INNER JOIN tblRate
                        ON tblRate.RateID = tblVendorRate.RateId
                            AND tblRate.CompanyID = p_companyId
                    INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
                        ON tblRate.Code = tblTempVendorRate.Code
                        AND tblTempVendorRate.TimezonesID = tblVendorRate.TimezonesID
                        AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                     SET tblVendorRate.EndDate = IFNULL(tblTempVendorRate.EndDate,date(now()))
                     WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId ;


						/*IF (FOUND_ROWS() > 0) THEN
								INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as mapped in File ' );
						END IF;*/


			-- need to get vendor rates with latest records ....
			-- and then need to use that table to insert update records in vendor rate.


			-- ------

			  	  -- CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId);

			/* 9. Update Interval in tblRate */

			-- Update Interval Changed for Action = "New"
			-- update Intervals which are not maching with tblTempVendorRate
			-- so as if intervals will not mapped next time it will be same as last file.
    				UPDATE tblRate
                 JOIN tmp_TempVendorRate_ as tblTempVendorRate
						ON 	  tblRate.CompanyID = p_companyId
							 AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							 AND tblTempVendorRate.Code = tblRate.Code
							AND  tblTempVendorRate.ProcessId = p_processId
							AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
	         		 SET
                    tblRate.Interval1 = tblTempVendorRate.Interval1,
                    tblRate.IntervalN = tblTempVendorRate.IntervalN
				     WHERE
                		     tblTempVendorRate.Interval1 IS NOT NULL
							 AND tblTempVendorRate.IntervalN IS NOT NULL
                		 AND
							  (
								  tblRate.Interval1 != tblTempVendorRate.Interval1
							  OR
								  tblRate.IntervalN != tblTempVendorRate.IntervalN
							  );




			/* 10. Update INTERVAL, ConnectionFee,  */

            UPDATE tblVendorRate
                INNER JOIN tblRate
                    ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
                INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblTempVendorRate.TimezonesID = tblVendorRate.TimezonesID
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        AND tblVendorRate.RateId = tblRate.RateId
                SET tblVendorRate.ConnectionFee = tblTempVendorRate.ConnectionFee,
                    tblVendorRate.Interval1 = tblTempVendorRate.Interval1,
                    tblVendorRate.IntervalN = tblTempVendorRate.IntervalN
                  --  tblVendorRate.EndDate = tblTempVendorRate.EndDate
                WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId ;


						/*IF (FOUND_ROWS() > 0) THEN
								INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated Existing Records' );
						END IF;*/

			/* 11. Update VendorBlocking  */

            IF  p_forbidden = 1 OR p_dialstringid > 0
				    THEN
                INSERT INTO tblVendorBlocking
                (
                    `AccountId`,
                    `RateId`,
                    `TrunkID`,
                    `BlockedBy`
                )
                SELECT distinct
                    p_accountId as AccountId,
                    tblRate.RateID as RateId,
                    p_trunkId as TrunkID,
                    'RMService' as BlockedBy
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                INNER JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                LEFT JOIN tblVendorBlocking vb
                    ON vb.AccountId=p_accountId
                        AND vb.RateId = tblRate.RateID
                        AND vb.TrunkID = p_trunkId
                WHERE tblTempVendorRate.Forbidden IN('B')
                    AND vb.VendorBlockingId is null;

            DELETE tblVendorBlocking
                FROM tblVendorBlocking
                INNER JOIN(
                    select VendorBlockingId
                    FROM `tblVendorBlocking` tv
                    INNER JOIN(
                        SELECT
                            tblRate.RateId as RateId
                        FROM tmp_TempVendorRate_ as tblTempVendorRate
                        INNER JOIN tblRate
                            ON tblRate.Code = tblTempVendorRate.Code
                                AND tblRate.CompanyID = p_companyId
                                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        WHERE tblTempVendorRate.Forbidden IN('UB')
                    )tv1 on  tv.AccountId=p_accountId
                    AND tv.TrunkID=p_trunkId
                    AND tv.RateId = tv1.RateID
                )vb2 on vb2.VendorBlockingId = tblVendorBlocking.VendorBlockingId;
				END IF;

		/* 11. Update VendorPreference  */

		IF  p_preference = 1
		THEN
            INSERT INTO tblVendorPreference
            (
                 `AccountId`
                 ,`Preference`
                 ,`RateId`
                 ,`TrunkID`
                 ,`CreatedBy`
                 ,`created_at`
            )
            SELECT
                 p_accountId AS AccountId,
                 tblTempVendorRate.Preference as Preference,
                 tblRate.RateID AS RateId,
                  p_trunkId AS TrunkID,
                  'RMService' AS CreatedBy,
                  NOW() AS created_at
            FROM tmp_TempVendorRate_ as tblTempVendorRate
            INNER JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
            LEFT JOIN tblVendorPreference vp
                ON vp.RateId=tblRate.RateID
                    AND vp.AccountId = p_accountId
                    AND vp.TrunkID = p_trunkId
            WHERE  tblTempVendorRate.Preference IS NOT NULL
                AND  tblTempVendorRate.Preference > 0
                AND  vp.VendorPreferenceID IS NULL;

					  UPDATE tblVendorPreference
                INNER JOIN tblRate
                    ON tblVendorPreference.RateId=tblRate.RateID
                INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
                    ON tblTempVendorRate.Code = tblRate.Code
                        AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId
                        AND tblRate.CompanyID = p_companyId
                SET tblVendorPreference.Preference = tblTempVendorRate.Preference
                WHERE tblVendorPreference.AccountId = p_accountId
                    AND tblVendorPreference.TrunkID = p_trunkId
                    AND  tblTempVendorRate.Preference IS NOT NULL
                    AND  tblTempVendorRate.Preference > 0
                    AND tblVendorPreference.VendorPreferenceID IS NOT NULL;

						DELETE tblVendorPreference
							  from	tblVendorPreference
					 	INNER JOIN tblRate
					 		  ON tblVendorPreference.RateId=tblRate.RateID
            INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
							  ON tblTempVendorRate.Code = tblRate.Code
				            AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId
				            AND tblRate.CompanyID = p_companyId
            WHERE tblVendorPreference.AccountId = p_accountId
							  AND tblVendorPreference.TrunkID = p_trunkId
							  AND  tblTempVendorRate.Preference IS NOT NULL
							  AND  tblTempVendorRate.Preference = ''
							  AND tblVendorPreference.VendorPreferenceID IS NOT NULL;

				END IF;


		/* 12. Delete rates which are same in file   */

			-- delete rates which are not increase/decreased  (rates = rates)
        DELETE tblTempVendorRate
            FROM tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
            JOIN tblVendorRate
                ON tblVendorRate.RateId = tblRate.RateId
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
                    AND tblVendorRate.TimezonesID = tblTempVendorRate.TimezonesID
                    AND tblTempVendorRate.Rate = tblVendorRate.Rate
                    AND (
                        tblVendorRate.EffectiveDate = tblTempVendorRate.EffectiveDate
                        OR
                        (
                            DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d')
                        )
                        OR 1 = (CASE
                            WHEN tblTempVendorRate.EffectiveDate > NOW() THEN 1
                            ELSE 0
                        END)
                    )
            WHERE  tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');

				/*IF (FOUND_ROWS() > 0) THEN
					INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Discarded no change records' );
				END IF;*/



            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

            SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
            SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

		/* 13. update currency   */

            /*UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
            JOIN tblVendorRate
                ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
				    SET tblVendorRate.Rate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       tblTempVendorRate.Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( tblTempVendorRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (tblTempVendorRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    tblTempVendorRate.Rate
                )
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');

 				SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();*/

            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
            JOIN tblVendorRate
                ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
                    AND tblVendorRate.TimezonesID = tblTempVendorRate.TimezonesID
				    SET tblVendorRate.EndDate = NOW()
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');

				-- archive rates which has EndDate <= today
				call prc_ArchiveOldVendorRate(p_accountId,p_trunkId,NULL,p_UserName);


		/* 13. insert new rates   */

            INSERT INTO tblVendorRate (
                AccountId,
                TrunkID,
                TimezonesID,
                RateId,
                Rate,
                EffectiveDate,
                EndDate,
                ConnectionFee,
                Interval1,
                IntervalN
            )
            SELECT DISTINCT
                p_accountId,
                p_trunkId,
                tblTempVendorRate.TimezonesID,
                tblRate.RateID,
                IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       tblTempVendorRate.Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( tblTempVendorRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (tblTempVendorRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    tblTempVendorRate.Rate
                ) ,
                tblTempVendorRate.EffectiveDate,
                tblTempVendorRate.EndDate,
                tblTempVendorRate.ConnectionFee,
                tblTempVendorRate.Interval1,
                tblTempVendorRate.IntervalN
            FROM tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
            LEFT JOIN tblVendorRate
                ON tblRate.RateID = tblVendorRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.trunkid = p_trunkId
                    AND tblVendorRate.TimezonesID = tblTempVendorRate.TimezonesID
                    AND tblTempVendorRate.EffectiveDate = tblVendorRate.EffectiveDate
            WHERE tblVendorRate.VendorRateID IS NULL
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

					SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

				/*IF (FOUND_ROWS() > 0) THEN
					INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Records Inserted.' );
				END IF;
				*/

			/* 13. update enddate in old rates */


			-- loop through effective date to update end date
			DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
			CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				EffectiveDate  Date
			);
			INSERT INTO tmp_EffectiveDates_ (EffectiveDate)
				SELECT distinct
					EffectiveDate
				FROM
					(	select distinct EffectiveDate
								from 	tblVendorRate
								WHERE
								AccountId = p_accountId
								AND TrunkId = p_trunkId
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

				UPDATE  tblVendorRate vr1
	         	inner join
	         	(
						select
			         	AccountID,
			         	RateID,
			         	TrunkID,
			         	TimezonesID,
	   		      	EffectiveDate
	      	   	FROM tblVendorRate
		                    WHERE AccountId = p_accountId
		                   		 AND TrunkId = p_trunkId
		            				AND EffectiveDate =   @EffectiveDate
		         	order by EffectiveDate desc

	         	) tmpvr
	         	on
	         	vr1.AccountID = tmpvr.AccountID
	         	AND vr1.TrunkID  	=       	tmpvr.TrunkID
	         	AND vr1.TimezonesID = tmpvr.TimezonesID
	         	AND vr1.RateID  	=        	tmpvr.RateID
	         	AND vr1.EffectiveDate 	< tmpvr.EffectiveDate
	         	SET
	         	vr1.EndDate = @EffectiveDate
	         	where
	         		vr1.AccountId = p_accountId
						AND vr1.TrunkID = p_trunkId
					--	AND vr1.EffectiveDate < @EffectiveDate
						AND vr1.EndDate is null;


					SET v_pointer_ = v_pointer_ + 1;


				END WHILE;

			END IF;


		END IF;

   INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	-- archive rates which has EndDate <= today
	call prc_ArchiveOldVendorRate(p_accountId,p_trunkId,NULL,p_UserName);


 	 SELECT * FROM tmp_JobLog_;
   DELETE  FROM tblTempVendorRate WHERE  ProcessId = p_processId;
   DELETE  FROM tblVendorRateChangeLog WHERE ProcessID = p_processId;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getReviewVendorRates`;
DELIMITER //
CREATE PROCEDURE `prc_getReviewVendorRates`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_Action` VARCHAR(50),
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
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
			IF(p_Action='Deleted',VendorRateID,TempVendorRateID) AS VendorRateID,
			`Code`,`Description`,tz.Title,`Rate`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`
		FROM
			tblVendorRateChangeLog
		JOIN
			tblTimezones tz ON tblVendorRateChangeLog.TimezonesID = tz.TimezonesID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
			END DESC,
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
			tblVendorRateChangeLog
		JOIN
			tblTimezones tz ON tblVendorRateChangeLog.TimezonesID = tz.TimezonesID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'));
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			distinct
			`Code`,`Description`,tz.Title,`Rate`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`
		FROM
			tblVendorRateChangeLog
		JOIN
			tblTimezones tz ON tblVendorRateChangeLog.TimezonesID = tz.TimezonesID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'));
	END IF;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
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
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT
)
ThisSP:BEGIN

    -- @TODO: code cleanup
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
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

	ALTER TABLE `tmp_TempRateTableRate_`	ADD Column `NewRate` decimal(18, 6) ;

    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

    SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
    SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

	  -- update all rate on newrate with currency conversion.
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

		-- if no error
    IF newstringcode = 0
    THEN
		-- if rates is not in our database (new rates from file) than insert it into ChangeLog
		INSERT INTO tblRateTableRateChangeLog(
            TempRateTableRateID,
            RateTableRateID,
            RateTableId,
            TimezonesID,
            RateId,
            Code,
            Description,
            Rate,
            EffectiveDate,
            EndDate,
            Interval1,
            IntervalN,
            ConnectionFee,
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTableRate.TempRateTableRateID,
			tblRateTableRate.RateTableRateID,
            p_RateTableId AS RateTableId,
            tblTempRateTableRate.TimezonesID,
            tblRate.RateId,
            tblTempRateTableRate.Code,
            tblTempRateTableRate.Description,
            tblTempRateTableRate.Rate,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate ,
			IFNULL(tblTempRateTableRate.Interval1,tblRate.Interval1 ) as Interval1,		-- take interval from file and update in tblRate if not changed in service
			IFNULL(tblTempRateTableRate.IntervalN , tblRate.IntervalN ) as IntervalN,
			tblTempRateTableRate.ConnectionFee,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		LEFT JOIN tblRate
			ON tblTempRateTableRate.Code = tblRate.Code AND tblTempRateTableRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId AND tblRateTableRate.RateTableId = p_RateTableId AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblRateTableRate.EffectiveDate  <= date(now())
		WHERE tblTempRateTableRate.ProcessID=p_processId AND tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
			-- AND tblTempRateTableRate.EffectiveDate != '0000-00-00 00:00:00';

   		  -- loop through effective date
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
         -- AND EffectiveDate <> '0000-00-00 00:00:00'
        group by EffectiveDate
        order by EffectiveDate asc;

        SET v_pointer_ = 1;
        SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

        IF v_rowCount_ > 0 THEN

            WHILE v_pointer_ <= v_rowCount_
            DO

                SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
                SET @row_num = 0;

                -- update  previous rate with all latest recent entriy of previous effective date

                INSERT INTO tblRateTableRateChangeLog(
                    TempRateTableRateID,
                    RateTableRateID,
                    RateTableId,
                    TimezonesID,
                    RateId,
                    Code,
                    Description,
                    Rate,
                    EffectiveDate,
                    EndDate,
                    Interval1,
                    IntervalN,
                    ConnectionFee,
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
                    RateTableRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblTempRateTableRate.Rate,
                    tblTempRateTableRate.EffectiveDate,
                    tblTempRateTableRate.EndDate ,
                    tblTempRateTableRate.Interval1,
                    tblTempRateTableRate.IntervalN,
                    tblTempRateTableRate.ConnectionFee,
                    IF(tblTempRateTableRate.NewRate > RateTableRate.Rate, 'Increased', IF(tblTempRateTableRate.NewRate < RateTableRate.Rate, 'Decreased','')) AS `Action`,
                    p_processid AS ProcessID,
                    now() AS created_at
                FROM
                (
                    -- get all rates RowID = 1 to remove old to old effective date
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
                            AND vr1.TimezonesID = vr2.TimezonesID
                            AND vr2.EffectiveDate  = @EffectiveDate
                        where
                            vr1.RateTableId = p_RateTableId
                            and vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate) -- <= because if same day rate change need to log
                        order by vr1.RateID desc ,vr1.EffectiveDate desc
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
                      order by RateID desc , EffectiveDate desc
                ) RateTableRate
                JOIN tblRate
                    ON tblRate.CompanyID = p_companyId
                    AND tblRate.RateID = RateTableRate.RateId
                JOIN tmp_TempRateTableRate_ tblTempRateTableRate
                    ON tblTempRateTableRate.Code = tblRate.Code
                    AND tblTempRateTableRate.TimezonesID = RateTableRate.TimezonesID
                    AND tblTempRateTableRate.ProcessID=p_processId
                    --	AND  tblTempRateTableRate.EffectiveDate <> '0000-00-00 00:00:00'
                    AND  RateTableRate.EffectiveDate <= tblTempRateTableRate.EffectiveDate -- <= because if same day rate change need to log
                    AND tblTempRateTableRate.EffectiveDate =  @EffectiveDate
                    AND RateTableRate.RowID = 1
                WHERE
                    RateTableRate.RateTableId = p_RateTableId
                    -- AND tblTempRateTableRate.EffectiveDate <> '0000-00-00 00:00:00'
                    AND tblTempRateTableRate.Code IS NOT NULL
                    AND tblTempRateTableRate.ProcessID=p_processId
                    AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

                SET v_pointer_ = v_pointer_ + 1;

            END WHILE;

        END IF;


        IF p_list_option = 1 -- p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
        THEN
            -- get rates which is not in file and insert it into ChangeLog
            INSERT INTO tblRateTableRateChangeLog(
                RateTableRateID,
                RateTableId,
                TimezonesID,
                RateId,
                Code,
                Description,
                Rate,
                EffectiveDate,
                EndDate,
                Interval1,
                IntervalN,
                ConnectionFee,
                `Action`,
                ProcessID,
                created_at
            )
            SELECT DISTINCT
                tblRateTableRate.RateTableRateID,
                p_RateTableId AS RateTableId,
                tblRateTableRate.TimezonesID,
                tblRateTableRate.RateId,
                tblRate.Code,
                tblRate.Description,
                tblRateTableRate.Rate,
                tblRateTableRate.EffectiveDate,
                tblRateTableRate.EndDate ,
                tblRateTableRate.Interval1,
                tblRateTableRate.IntervalN,
                tblRateTableRate.ConnectionFee,
                'Deleted' AS `Action`,
                p_processId AS ProcessID,
                now() AS deleted_at
            FROM tblRateTableRate
            JOIN tblRate
                ON tblRate.RateID = tblRateTableRate.RateId AND tblRate.CompanyID = p_companyId
            LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
                ON tblTempRateTableRate.Code = tblRate.Code
                AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
                AND tblTempRateTableRate.ProcessID=p_processId
                AND (
                    -- normal condition
                    ( tblTempRateTableRate.EndDate is null AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
                    OR
                    -- skip records just to avoid duplicate records in tblRateTableRateChangeLog tabke - when EndDate is given with delete
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
            RateId,
            Code,
            Description,
            Rate,
            EffectiveDate,
            EndDate,
            Interval1,
            IntervalN,
            ConnectionFee,
            `Action`,
            ProcessID,
            created_at
        )
        SELECT DISTINCT
            tblRateTableRate.RateTableRateID,
            p_RateTableId AS RateTableId,
            tblRateTableRate.TimezonesID,
            tblRateTableRate.RateId,
            tblRate.Code,
            tblRate.Description,
            tblRateTableRate.Rate,
            tblRateTableRate.EffectiveDate,
            IFNULL(tblTempRateTableRate.EndDate,tblRateTableRate.EndDate) as  EndDate ,
            tblRateTableRate.Interval1,
            tblRateTableRate.IntervalN,
            tblRateTableRate.ConnectionFee,
            'Deleted' AS `Action`,
            p_processId AS ProcessID,
            now() AS deleted_at
        FROM tblRateTableRate
        JOIN tblRate
            ON tblRate.RateID = tblRateTableRate.RateId AND tblRate.CompanyID = p_companyId
        LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
            ON tblRate.Code = tblTempRateTableRate.Code
            AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
            AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
            AND tblTempRateTableRate.ProcessID=p_processId
            -- AND tblTempRateTableRate.EndDate <= date(now())
            -- AND tblTempRateTableRate.ProcessID=p_processId
        WHERE tblRateTableRate.RateTableId = p_RateTableId
            -- AND tblRateTableRate.EndDate <= date(now())
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
	IN `p_dialcodeSeparator` VARCHAR(50)
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
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
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
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
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
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitRateTableRate(p_processId,p_dialcodeSeparator);

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

	INSERT INTO tmp_TempRateTableRate_
	SELECT DISTINCT
		`TempRateTableRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`Code`,
		`Description`,
		`Rate`,
		`EffectiveDate`,
		`EndDate`,
		`Change`,
		`ProcessId`,
		`Preference`,
		`ConnectionFee`,
		`Interval1`,
		`IntervalN`,
		`Forbidden`,
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
	SELECT count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;

	IF  totalduplicatecode > 0
	THEN

		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT code, 1 as a FROM(
		SELECT   count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
			CONCAT(code , ' DUPLICATE CODE')
		FROM(
			SELECT   count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;
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

				/*INSERT INTO tmp_JobLog_ (Message)
				SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
				FROM tmp_TempRateTableRate_ vr
				LEFT JOIN tmp_DialString_ ds
					ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
				WHERE vr.ProcessId = p_processId
					AND ds.DialStringID IS NULL
					AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');*/

				-- Insert new dialstring if not exist
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

				/*INSERT INTO tmp_RateTableRateDialString_2
				SELECT * FROM tmp_RateTableRateDialString_;*/

				INSERT INTO tmp_VendorRateDialString_2
				SELECT *  FROM tmp_VendorRateDialString_ where DialStringPrefix!='';

				Delete From tmp_VendorRateDialString_
				Where DialStringPrefix = ''
				And Code IN (Select DialStringPrefix From tmp_VendorRateDialString_2);

				INSERT INTO tmp_VendorRateDialString_3
				SELECT * FROM tmp_VendorRateDialString_;

				/*INSERT INTO tmp_RateTableRateDialString_3
				SELECT vrs1.* from tmp_RateTableRateDialString_2 vrs1
				LEFT JOIN tmp_RateTableRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId AND vrs1.Description=vrs2.Description AND vrs1.EffectiveDate=vrs2.EffectiveDate AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
				WHERE ( (vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL));*/

				DELETE  FROM tmp_TempRateTableRate_ WHERE  ProcessId = p_processId;

				INSERT INTO tmp_TempRateTableRate_(
					`TempRateTableRateID`,
					CodeDeckId,
					TimezonesID,
					Code,
					Description,
					Rate,
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
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempRateTableRateID_ INT;
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN

		DROP TEMPORARY TABLE IF EXISTS `my_splits`;
		CREATE TEMPORARY TABLE `my_splits` (
			`TempRateTableRateID` INT(11) NULL DEFAULT NULL,
			`Code` Text NULL DEFAULT NULL,
			`CountryCode` Text NULL DEFAULT NULL
		);

		SET i = 1;
		REPEAT
			INSERT INTO my_splits (TempRateTableRateID, Code, CountryCode)
			SELECT TempRateTableRateID , FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempRateTableRate
			WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
				AND ProcessId = p_processId;

			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;

		UPDATE my_splits SET Code = trim(Code);


		INSERT INTO my_splits (TempVendorRateID, Code, CountryCode)
		SELECT TempVendorRateID , Code, CountryCode  FROM tblTempVendorRate
		WHERE (CountryCode IS NOT NULL AND CountryCode <> '') AND (Code IS NULL OR Code = '')
		AND ProcessId = p_processId;


		DROP TEMPORARY TABLE IF EXISTS tmp_newratetable_splite_;
		CREATE TEMPORARY TABLE tmp_newratetable_splite_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			TempRateTableRateID INT(11) NULL DEFAULT NULL,
			Code VARCHAR(500) NULL DEFAULT NULL,
			CountryCode VARCHAR(500) NULL DEFAULT NULL
		);

		INSERT INTO tmp_newratetable_splite_(TempRateTableRateID,Code,CountryCode)
		SELECT
			TempRateTableRateID,
			Code,
			CountryCode
		FROM my_splits
		WHERE Code like '%-%'
			AND TempRateTableRateID IS NOT NULL;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newratetable_splite_);

		WHILE v_pointer_ <= v_rowCount_
		DO
			SET v_TempRateTableRateID_ = (SELECT TempRateTableRateID FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_Code_ = (SELECT Code FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);

			Call prc_SplitAndInsertRateTableRate(v_TempRateTableRateID_,v_Code_,v_CountryCode_);

			SET v_pointer_ = v_pointer_ + 1;
		END WHILE;

		DELETE FROM my_splits
		WHERE Code like '%-%'
			AND TempRateTableRateID IS NOT NULL;

		DELETE FROM my_splits
		WHERE (Code = '' OR Code IS NULL) AND (CountryCode = '' OR CountryCode IS NULL);

		INSERT INTO tmp_split_RateTableRate_
		SELECT DISTINCT
			my_splits.TempRateTableRateID as `TempRateTableRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
			`Description`,
			`Rate`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Forbidden`,
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
			CONCAT(IFNULL(tblTempRateTableRate.CountryCode,''),tblTempRateTableRate.Code) as Code,
			`Description`,
			`Rate`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Forbidden`,
			`DialStringPrefix`
		FROM tblTempRateTableRate
		WHERE ProcessId = p_processId;

	END IF;

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

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

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
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		TempRateTableRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableRate (
		RateTableRateID INT,
		RateTableId INT,
		TimezonesID INT,
		RateId INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		Rate DECIMAL(18, 6),
		EffectiveDate DATETIME,
		EndDate Datetime ,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18, 6),
		deleted_at DATETIME,
		INDEX tmp_RateTableRateDiscontinued_RateTableRateID (`RateTableRateID`)
	);

	/*  1.  Check duplicate code, dial string   */
	CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

	-- if no error
	IF newstringcode = 0
	THEN
		/*  2.  Send Today EndDate to rates which are marked deleted in review screen  */
		/*  3.  Update interval in temp table */

		-- if review
		IF (SELECT count(*) FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN
			-- update end date given from tblRateTableRateChangeLog for deleted rates.
			UPDATE
				tblRateTableRate vr
			INNER JOIN tblRateTableRateChangeLog  vrcl
			on vrcl.RateTableRateID = vr.RateTableRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';

			-- update end date on temp table
			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				-- AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			SET
				tblTempRateTableRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;

			-- update intervals.
			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				-- AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			SET
				tblTempRateTableRate.Interval1 = vrcl.Interval1 ,
				tblTempRateTableRate.IntervalN = vrcl.IntervalN
			WHERE
				vrcl.`Action` = 'New'
				AND vrcl.Interval1 IS NOT NULL
				AND vrcl.IntervalN IS NOT NULL ;

			/*IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated End Date of Deleted Records. ' );
			END IF;
			*/

		END IF;

		/*  4.  Update EndDate to Today if Replace All existing */
		IF  p_replaceAllRates = 1
		THEN
			UPDATE tblRateTableRate
				SET tblRateTableRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;

			/*
			IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' Records Removed.   ' );
			END IF;
			*/
		END IF;

		/* 5. If Complete File, remove rates not exists in file  */

		IF p_list_option = 1    -- v4.16 p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
		THEN
			-- v4.16 get rates which is not in file and insert it into temp table
			INSERT INTO tmp_Delete_RateTableRate(
				RateTableRateID ,
				RateTableId,
				TimezonesID,
				RateId,
				Code ,
				Description ,
				Rate ,
				EffectiveDate ,
				EndDate ,
				Interval1 ,
				IntervalN ,
				ConnectionFee ,
				deleted_at
			)
			SELECT DISTINCT
				tblRateTableRate.RateTableRateID,
				p_RateTableId AS RateTableId,
				tblRateTableRate.TimezonesID,
				tblRateTableRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableRate.Rate,
				tblRateTableRate.EffectiveDate,
				IFNULL(tblRateTableRate.EndDate,date(now())) ,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.ConnectionFee,
				now() AS deleted_at
			FROM tblRateTableRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
				ON tblTempRateTableRate.Code = tblRate.Code
				AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
				AND  tblTempRateTableRate.ProcessId = p_processId
				AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableRate.RateTableId = p_RateTableId
				AND tblTempRateTableRate.Code IS NULL
				AND ( tblRateTableRate.EndDate is NULL OR tblRateTableRate.EndDate <= date(now()) )
			ORDER BY RateTableRateID ASC;

			/*IF (FOUND_ROWS() > 0) THEN
			INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as Not exists in File' );
			END IF;*/

			-- set end date will remove at bottom in archive proc
			UPDATE tblRateTableRate
			JOIN tmp_Delete_RateTableRate ON tblRateTableRate.RateTableRateID = tmp_Delete_RateTableRate.RateTableRateID
				SET tblRateTableRate.EndDate = date(now())
			WHERE
				tblRateTableRate.RateTableId = p_RateTableId;

		END IF;

		/* 6. Move Rates to archive which has EndDate <= now()  */
		-- move to archive if EndDate is <= now()
		IF ( (SELECT count(*) FROM tblRateTableRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN

			-- move to archive
			/*INSERT INTO tblRateTableRateArchive
			SELECT DISTINCT  null , -- Primary Key column
				`RateTableRateID`,
				`RateTableId`,
				`RateId`,
				`Rate`,
				`EffectiveDate`,
				IFNULL(`EndDate`,date(now())) as EndDate,
				`updated_at`,
				`created_at`,
				`created_by`,
				`ModifiedBy`,
				`Interval1`,
				`IntervalN`,
				`ConnectionFee`,
				concat('Ends Today rates @ ' , now() ) as `Notes`
			FROM tblRateTableRate
			WHERE  RateTableId = p_RateTableId AND EndDate <= NOW();

			delete from tblRateTableRate
			WHERE  RateTableId = p_RateTableId AND EndDate <= NOW();*/

			-- Update previous rate before archive
			call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');

			-- Archive Rates
			call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);

		END IF;

		/* 7. Add New code in codedeck  */

		IF  p_addNewCodesToCodeDeck = 1
		THEN
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

			/*IF (FOUND_ROWS() > 0) THEN
					INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Code Inserted into Codedeck ' );
			END IF;*/

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
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
						tblTempRateTableRate.Code,
						tblTempRateTableRate.Description
					FROM tmp_TempRateTableRate_  as tblTempRateTableRate
					LEFT JOIN tblRate
						ON tblRate.Code = tblTempRateTableRate.Code
						AND tblRate.CompanyID = p_companyId
						AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
					WHERE tblRate.RateID IS NULL
						AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				) as tbl;
			END IF;
		END IF;

		/* 8. delete rates which will be map as deleted */

		-- delete rates which will be map as deleted
		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND tblRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
			AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTableRate.EndDate = IFNULL(tblTempRateTableRate.EndDate,date(now()))
		WHERE tblRateTableRate.RateTableId = p_RateTableId;

		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as mapped in File ' );
		END IF;*/


		-- need to get ratetable rates with latest records ....
		-- and then need to use that table to insert update records in ratetable rate.


		-- ------

		/* 9. Update Interval in tblRate */

		-- Update Interval Changed for Action = "New"
		-- update Intervals which are not maching with tblTempRateTableRate
		-- so as if intervals will not mapped next time it will be same as last file.
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


		/* 10. Update INTERVAL, ConnectionFee,  */

		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblRateTableRate.RateId = tblRate.RateId
		SET tblRateTableRate.ConnectionFee = tblTempRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN
			--  tblRateTableRate.EndDate = tblTempRateTableRate.EndDate
		WHERE tblRateTableRate.RateTableId = p_RateTableId;


		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated Existing Records' );
		END IF;*/


		/* 12. Delete rates which are same in file   */

		-- delete rates which are not increase/decreased  (rates = rates)
		DELETE tblTempRateTableRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.Rate = tblRateTableRate.Rate
			AND (
				tblRateTableRate.EffectiveDate = tblTempRateTableRate.EffectiveDate
				OR
				(
					DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d')
				)
				OR 1 = (CASE
							WHEN tblTempRateTableRate.EffectiveDate > NOW() THEN 1
							ELSE 0
						END)
			)
		WHERE  tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');

		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Discarded no change records' );
		END IF;*/

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

		/* 13. update currency   */

		/*UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
		SET tblRateTableRate.Rate = IF (
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
		)
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();*/

		/* 13. archive same date's rate   */
		DROP TEMPORARY TABLE IF EXISTS tmp_PreviousRate;
		CREATE TEMPORARY TABLE `tmp_PreviousRate` (
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
			tmp_PreviousRate (RateId,PreviousRate,EffectiveDate)
		SELECT
			tblRateTableRate.RateId,tblRateTableRate.Rate,tblTempRateTableRate.EffectiveDate
		FROM
			tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		-- archive rates which has EndDate <= today
		call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);

		/* 13. insert new rates   */

		INSERT INTO tblRateTableRate (
			RateTableId,
			TimezonesID,
			RateId,
			Rate,
			EffectiveDate,
			EndDate,
			ConnectionFee,
			Interval1,
			IntervalN,
			PreviousRate
		)
		SELECT DISTINCT
			p_RateTableId,
			tblTempRateTableRate.TimezonesID,
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
			) ,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate,
			tblTempRateTableRate.ConnectionFee,
			tblTempRateTableRate.Interval1,
			tblTempRateTableRate.IntervalN,
			IFNULL(tmp_PreviousRate.PreviousRate,0) AS PreviousRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.EffectiveDate = tblRateTableRate.EffectiveDate
		LEFT JOIN tmp_PreviousRate
			ON tblRate.RateId = tmp_PreviousRate.RateId AND tblTempRateTableRate.EffectiveDate = tmp_PreviousRate.EffectiveDate
		WHERE tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Records Inserted.' );
		END IF;
		*/

		/* 13. update enddate in old rates */

		-- loop through effective date to update end date
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
					AND vr1.RateID  	=        	tmpvr.RateID
					AND vr1.TimezonesID = tmpvr.TimezonesID
					AND vr1.EffectiveDate 	< tmpvr.EffectiveDate
				SET
					vr1.EndDate = @EffectiveDate
				where
					vr1.RateTableId = p_RateTableId
					--	AND vr1.EffectiveDate < @EffectiveDate
					AND vr1.EndDate is null;


				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

		END IF;

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	-- Update previous rate before archive
	call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');

	-- archive rates which has EndDate <= today
	call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);


	DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getReviewRateTableRates`;
DELIMITER //
CREATE PROCEDURE `prc_getReviewRateTableRates`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_Action` VARCHAR(50),
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
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
			`Code`,`Description`,tz.Title,`Rate`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`
		FROM
			tblRateTableRateChangeLog
		JOIN
			tblTimezones tz ON tblRateTableRateChangeLog.TimezonesID = tz.TimezonesID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
			END DESC,
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
			tblRateTableRateChangeLog
		JOIN
			tblTimezones tz ON tblRateTableRateChangeLog.TimezonesID = tz.TimezonesID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'));
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			distinct
			`Code`,`Description`,tz.Title,`Rate`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`
		FROM
			tblRateTableRateChangeLog
		JOIN
			tblTimezones tz ON tblRateTableRateChangeLog.TimezonesID = tz.TimezonesID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'));
	END IF;


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

			-- front end update , tmp_Update_RateTable_ table required

			SET  @EffectiveDate = STR_TO_DATE(p_EffectiveDate , '%Y-%m-%d');


			SET @row_num = 0;

			-- update  previous rate with all latest recent entriy of previous effective date
			UPDATE tblRateTableRate rtr
			inner join
			(
				-- get all rates RowID = 1 to remove old to old effective date
				select distinct tmp.* ,
				@row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
				@prev_RateId := tmp.RateID,
				@prev_EffectiveDate := tmp.EffectiveDate
				FROM
				(
					select distinct rt1.*
					from tblRateTableRate rt1
					inner join tblRateTableRate rt2
					on rt1.RateTableId = p_RateTableId and rt1.RateID = rt2.RateID AND rt1.TimezonesID = rt2.TimezonesID
					where
					rt1.RateTableID = p_RateTableId
					and rt1.EffectiveDate < rt2.EffectiveDate AND rt2.EffectiveDate  = @EffectiveDate
					order by rt1.RateID desc ,rt1.EffectiveDate desc
				) tmp

			) old_rtr on  old_rtr.RateTableID = rtr.RateTableID  and old_rtr.RateID = rtr.RateID AND plo_rtr.TimezonesID = rtr.TimezonesID
			and old_rtr.EffectiveDate < rtr.EffectiveDate AND rtr.EffectiveDate =  @EffectiveDate AND old_rtr.RowID = 1
			SET rtr.PreviousRate = old_rtr.Rate
			where
			rtr.RateTableID = p_RateTableId;


	ELSE

		-- update for job

		DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
			CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
				EffectiveDate  Date,
				RowID int,
				INDEX (RowID)
			);



		-- loop through effective date to update previous rate

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

	         -- update  previous rate with all latest recent entriy of previous effective date
				UPDATE tblRateTableRate rtr
				inner join
				(
					-- get all rates RowID = 1 to remove old to old effective date

					select distinct tmp.* ,
					@row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
					@prev_RateId := tmp.RateID,
					@prev_EffectiveDate := tmp.EffectiveDate
					FROM
					(
						select distinct rt1.*
						from tblRateTableRate rt1
						inner join tblRateTableRate rt2
						on rt1.RateTableId = p_RateTableId and rt1.RateID = rt2.RateID AND rt1.TimezonesID=rt2.TimezonesID
						where
						rt1.RateTableID = p_RateTableId
						and rt1.EffectiveDate < rt2.EffectiveDate AND rt2.EffectiveDate  = @EffectiveDate
						order by rt1.RateID desc ,rt1.EffectiveDate desc
					) tmp


				) old_rtr on  old_rtr.RateTableID = rtr.RateTableID  and old_rtr.RateID = rtr.RateID AND old_rtr.TimezonesID = rtr.TimezonesID and old_rtr.EffectiveDate < rtr.EffectiveDate
				AND rtr.EffectiveDate =  @EffectiveDate  AND old_rtr.RowID = 1
				SET rtr.PreviousRate = old_rtr.Rate
				where
				rtr.RateTableID = p_RateTableID;


				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;

		-- Previous rate update


	END IF;


	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END//
DELIMITER ;