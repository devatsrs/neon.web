use `speakintelligentRM`;

CREATE TABLE IF NOT EXISTS `tblRate_new` (
  `RateID` int(11) NOT NULL AUTO_INCREMENT,
  `CountryID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `CodeDeckId` int(11) NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country__tobe_delete` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT '1',
  `IntervalN` int(11) DEFAULT '1',
  `MinimumDuration` int(11) DEFAULT '0',
  PRIMARY KEY (`RateID`),
  UNIQUE KEY `IXUnique_CompanyID_Code_CodedeckID` (`CompanyID`,`Code`,`CodeDeckId`),
  KEY `IX_tblRate_CountryId` (`CountryID`),
  KEY `IX_tblRate_companyId_codedeckid` (`CompanyID`,`CodeDeckId`,`RateID`,`CountryID`,`Code`,`Description`),
  KEY `IX_country_company_codedeck` (`CountryID`,`CompanyID`,`CodeDeckId`),
  KEY `IX_tblrate_desc` (`Description`),
  KEY `IX_tblrate_code` (`Code`),
  KEY `IX_tblrate_CodeDescription` (`RateID`,`CompanyID`,`CountryID`,`Code`,`Description`),
  KEY `IX_tblRate_CompanyCodeDeckIdCode` (`CompanyID`,`CodeDeckId`,`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `tblRate_new`
(
	`RateID`,
	`CountryID`,
	`CompanyID`,
	`CodeDeckId`,
	`Code`,
	`Description`,
	`Type`,
	`Country__tobe_delete`,
	`updated_at`,
	`created_at`,
	`UpdatedBy`,
	`CreatedBy`,
	`Interval1`,
	`IntervalN`
)
SELECT
	`RateID`,
	`CountryID`,
	`CompanyID`,
	`CodeDeckId`,
	`Code`,
	`Description`,
	`Type`,
	`Country__tobe_delete`,
	`updated_at`,
	`created_at`,
	`UpdatedBy`,
	`CreatedBy`,
	`Interval1`,
	`IntervalN`
FROM
	`tblRate`;

-- do this manuaally
-- SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'speakintelligentRM' AND TABLE_NAME = 'tblRate';
-- ALTER TABLE speakintelligentRM.tblRate_new AUTO_INCREMENT=AUTO_INCREMENT;

RENAME TABLE `tblRate` TO `tblRate_old_20190418`;
RENAME TABLE `tblRate_new` TO `tblRate`;

ALTER TABLE `tblTempCodeDeck`
	ADD COLUMN `MinimumDuration` INT(11) NULL DEFAULT NULL AFTER `IntervalN`;

ALTER TABLE `tblTempRateTableRate`
	ADD COLUMN `MinimumDuration` VARCHAR(5) NULL DEFAULT NULL AFTER `IntervalN`;

ALTER TABLE `tblRateTableRateChangeLog`
	ADD COLUMN `MinimumDuration` INT(11) NULL DEFAULT NULL AFTER `IntervalN`;

ALTER TABLE `tblRateTableRate`
	ADD COLUMN `MinimumDuration` INT(11) NULL DEFAULT NULL AFTER `IntervalN`;

ALTER TABLE `tblRateTableRateAA`
	ADD COLUMN `MinimumDuration` INT(11) NULL DEFAULT NULL AFTER `IntervalN`;

ALTER TABLE `tblRateTableRateArchive`
	ADD COLUMN `MinimumDuration` INT(11) NULL DEFAULT NULL AFTER `IntervalN`;

ALTER TABLE `tblCountry`
	ADD INDEX `index_Prefix` (`Prefix`),
	ADD INDEX `index_Country` (`Country`),
	ADD INDEX `index_Prefix_Country` (`Prefix`, `Country`);








DROP PROCEDURE IF EXISTS `prc_GetCodeDeck`;
DELIMITER //
CREATE PROCEDURE `prc_GetCodeDeck`(
	IN `p_companyid` int,
	IN `p_codedeckid` int,
	IN `p_contryid` int,
	IN `p_code` varchar(50),
	IN `p_description` varchar(50),
	IN `p_Type` VARCHAR(50),
	IN `p_PageNumber` int,
	IN `p_RowspPage` int,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` VARCHAR(50)
)
BEGIN

    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	  SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

    IF p_isExport = 0
    THEN

        SELECT
            RateID,
            tblCountry.ISO2,
            tblCountry.Country,
            Code,
            Description,
            `Type`,
            Interval1,
            IntervalN,
            MinimumDuration
        FROM tblRate
        LEFT JOIN tblCountry
            ON tblCountry.CountryID = tblRate.CountryID
        WHERE   (p_contryid = 0 OR tblCountry.CountryID = p_contryid)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))
            AND (CompanyID = p_companyid AND CodeDeckId = p_codedeckid)
            AND (p_Type IS NULL OR `Type` = p_Type)
        ORDER BY
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeDESC') THEN `Type`
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeASC') THEN `Type`
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ISO2DESC') THEN ISO2
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ISO2ASC') THEN ISO2
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
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationDESC') THEN MinimumDuration
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationASC') THEN MinimumDuration
            END ASC
        LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(RateID) AS totalcount
        FROM tblRate
        LEFT JOIN tblCountry
            ON tblCountry.CountryID = tblRate.CountryID
        WHERE   (p_contryid = 0 OR tblCountry.CountryID = p_contryid)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))
            AND (p_Type IS NULL OR `Type` = p_Type)
            AND (CompanyID = p_companyid AND CodeDeckId = p_codedeckid);

    END IF;

    IF p_isExport = 1
    THEN

        SELECT
            tblCountry.Country,
            Code,
            tblCountry.ISO2 as 'ISO Code',
            Description,
            `Type`,
            Interval1,
            IntervalN,
            MinimumDuration
        FROM tblRate
        LEFT JOIN tblCountry
            ON tblCountry.CountryID = tblRate.CountryID
        WHERE   (p_contryid  = 0 OR tblCountry.CountryID = p_contryid)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))
            AND (p_Type IS NULL OR `Type` = p_Type)
            AND (CompanyID = p_companyid AND CodeDeckId = p_codedeckid);

    END IF;
    IF p_isExport = 2
    THEN

        SELECT
	        RateID,
	         tblCountry.ISO2,
            tblCountry.Country,
            Code,
            Description,
            `Type`,
            Interval1,
            IntervalN,
            MinimumDuration
        FROM tblRate
        LEFT JOIN tblCountry
            ON tblCountry.CountryID = tblRate.CountryID
        WHERE   (p_contryid  = 0 OR tblCountry.CountryID = p_contryid)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))
            AND (p_Type IS NULL OR `Type` = p_Type)
            AND (CompanyID = p_companyid AND CodeDeckId = p_codedeckid);

    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessCodeDeck`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessCodeDeck`(
	IN `p_processId` VARCHAR(200),
	IN `p_companyId` INT
)
BEGIN

	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE   v_CodeDeckId_ INT;
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE countrycount INT DEFAULT 0;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_  (
		Message longtext
	);

    SELECT CodeDeckId INTO v_CodeDeckId_ FROM tblTempCodeDeck WHERE ProcessId = p_processId AND CompanyId = p_companyId LIMIT 1;

    DELETE n1
	 FROM tblTempCodeDeck n1
	 INNER JOIN (
	 	SELECT MAX(TempCodeDeckRateID) as TempCodeDeckRateID,Code FROM tblTempCodeDeck WHERE ProcessId = p_processId
		GROUP BY Code
		HAVING COUNT(*)>1
	) n2
	 	ON n1.Code = n2.Code AND n1.TempCodeDeckRateID < n2.TempCodeDeckRateID
	WHERE n1.ProcessId = p_processId;


	 SELECT COUNT(*) INTO countrycount FROM tblTempCodeDeck WHERE ProcessId = p_processId AND Country !='';


    UPDATE tblTempCodeDeck
    SET
        tblTempCodeDeck.Interval1 = CASE WHEN tblTempCodeDeck.Interval1 is not null  and tblTempCodeDeck.Interval1 > 0
                                    THEN
                                        tblTempCodeDeck.Interval1
                                    ELSE
                                    	1
                                    END,
        tblTempCodeDeck.IntervalN = CASE WHEN tblTempCodeDeck.IntervalN is not null  and tblTempCodeDeck.IntervalN > 0
                                    THEN
                                        tblTempCodeDeck.IntervalN
                                    ELSE
                                        1
                                    END,
        tblTempCodeDeck.MinimumDuration = CASE WHEN tblTempCodeDeck.MinimumDuration IS NOT NULL AND tblTempCodeDeck.MinimumDuration >= 0
                                    THEN
                                        tblTempCodeDeck.MinimumDuration
                                    ELSE
                                        0
                                    END
    WHERE tblTempCodeDeck.ProcessId = p_processId;

    UPDATE tblTempCodeDeck t
	    SET t.CountryId = fnGetCountryIdByCodeAndCountry (t.Code ,t.Country)
	 WHERE t.ProcessId = p_processId ;

   IF countrycount > 0
   THEN
	  	UPDATE tblTempCodeDeck t
		    SET t.CountryId = fnGetCountryIdByCodeAndCountry (t.Code ,t.Description)
		 WHERE t.ProcessId = p_processId AND  t.CountryId IS NULL;
	END IF;

 IF ( SELECT COUNT(*)
                 FROM   tblTempCodeDeck
                 WHERE  tblTempCodeDeck.ProcessId = p_processId
                        AND tblTempCodeDeck.Action = 'D'
               ) > 0
            THEN
      DELETE  tblRate
            FROM    tblRate
                    INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
                                                  AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
                    LEFT OUTER JOIN tblCustomerRate ON tblRate.RateID = tblCustomerRate.RateID
                    LEFT OUTER JOIN tblRateTableRate ON tblRate.RateID = tblRateTableRate.RateID
                    LEFT OUTER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId
            WHERE   tblTempCodeDeck.Action = 'D'
          AND tblTempCodeDeck.CompanyID = p_companyId
          AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_
          AND tblTempCodeDeck.ProcessId = p_processId
                    AND tblCustomerRate.CustomerRateID IS NULL
                    AND tblRateTableRate.RateTableRateID IS NULL
                    AND tblVendorRate.VendorRateID IS NULL ;
		END IF;
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


  		SELECT GROUP_CONCAT(Code) into errormessage FROM(
	      SELECT distinct tblRate.Code as Code,1 as a
	      FROM    tblRate
	                    INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
	      	    AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
	          WHERE   tblTempCodeDeck.Action = 'D'
	          AND tblTempCodeDeck.ProcessId = p_processId
	          AND tblTempCodeDeck.CompanyID = p_companyId AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_)as tbl GROUP BY a;

	   IF errormessage IS NOT NULL
          THEN
                    INSERT INTO tmp_JobLog_ (Message)
                    SELECT distinct
						  CONCAT(tblRate.Code , ' FAILED TO DELETE - CODE IS IN USE')
					      FROM   tblRate
					              INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
					      	    AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
					          WHERE   tblTempCodeDeck.Action = 'D'
					          AND tblTempCodeDeck.ProcessId = p_processId
					          AND tblTempCodeDeck.CompanyID = p_companyId AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_;
	 	END IF;

      UPDATE  tblRate
      JOIN tblTempCodeDeck ON tblRate.Code = tblTempCodeDeck.Code
            AND tblTempCodeDeck.ProcessId = p_processId
            AND tblRate.CompanyID = p_companyId
            AND tblRate.CodeDeckId = v_CodeDeckId_
            AND tblTempCodeDeck.Action != 'D'
		SET   tblRate.Description = tblTempCodeDeck.Description,
		      tblRate.`Type` = tblTempCodeDeck.`Type`,
            tblRate.Interval1 = tblTempCodeDeck.Interval1,
            tblRate.IntervalN = tblTempCodeDeck.IntervalN,
            tblRate.MinimumDuration = tblTempCodeDeck.MinimumDuration;

  		IF countrycount > 0
  		THEN

	  		UPDATE  tblRate
	      JOIN tblTempCodeDeck ON tblRate.Code = tblTempCodeDeck.Code
	            AND tblTempCodeDeck.ProcessId = p_processId
	            AND tblRate.CompanyID = p_companyId
	            AND tblRate.CodeDeckId = v_CodeDeckId_
	            AND tblTempCodeDeck.Action != 'D'
			SET   tblRate.CountryID = tblTempCodeDeck.CountryId;

		END IF;

      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

            INSERT  INTO tblRate
                    ( CountryID ,
                      CompanyID ,
                      CodeDeckId,
                      Code ,
                      Description,
                      `Type`,
                      Interval1,
                      IntervalN,
					  MinimumDuration
                    )
                    SELECT  DISTINCT
              tblTempCodeDeck.CountryId ,
                            tblTempCodeDeck.CompanyId ,
                            tblTempCodeDeck.CodeDeckId,
                            tblTempCodeDeck.Code ,
                            tblTempCodeDeck.Description,
                            tblTempCodeDeck.`Type`,
                            tblTempCodeDeck.Interval1,
                            tblTempCodeDeck.IntervalN,
							tblTempCodeDeck.MinimumDuration
                    FROM    tblTempCodeDeck left join tblRate on(tblRate.CompanyID = p_companyId AND  tblRate.CodeDeckId = v_CodeDeckId_ AND tblTempCodeDeck.Code=tblRate.Code)
                    WHERE  tblRate.RateID is null
                            AND tblTempCodeDeck.ProcessId = p_processId
              AND tblTempCodeDeck.CompanyID = p_companyId
              AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_
                            AND tblTempCodeDeck.Action != 'D';

      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );

	DELETE  FROM tblTempCodeDeck WHERE   tblTempCodeDeck.ProcessId = p_processId;
 	 SELECT * from tmp_JobLog_;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    SELECT * from tmp_JobLog_ limit 0 , 20;
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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
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
            MinimumDuration,
            ConnectionFee,
            Preference,
            Blocked,
            RoutingCategoryID,
            RateCurrency,
            ConnectionFeeCurrency,
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTableRate.TempRateTableRateID,
			tblRateTableRate.RateTableRateID,
			p_RateTableId AS RateTableId,
			tblTempRateTableRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
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
			IFNULL(tblTempRateTableRate.MinimumDuration , tblRate.MinimumDuration ) as MinimumDuration,
			tblTempRateTableRate.ConnectionFee,
			tblTempRateTableRate.Preference,
			tblTempRateTableRate.Blocked,
			tblTempRateTableRate.RoutingCategoryID,
			tblTempRateTableRate.RateCurrency,
			tblTempRateTableRate.ConnectionFeeCurrency,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		LEFT JOIN tblRate
			ON tblTempRateTableRate.Code = tblRate.Code AND tblTempRateTableRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON tblTempRateTableRate.OriginationCode = OriginationRate.Code AND tblTempRateTableRate.CodeDeckId = OriginationRate.CodeDeckId  AND OriginationRate.CompanyID = p_companyId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId AND
			((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID)) AND
			tblRateTableRate.RateTableId = p_RateTableId AND
			tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
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
					MinimumDuration,
					ConnectionFee,
					Preference,
					Blocked,
					RoutingCategoryID,
					RateCurrency,
					ConnectionFeeCurrency,
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
					IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
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
					tblTempRateTableRate.MinimumDuration,
					tblTempRateTableRate.ConnectionFee,
					tblTempRateTableRate.Preference,
					tblTempRateTableRate.Blocked,
					tblTempRateTableRate.RoutingCategoryID,
					tblTempRateTableRate.RateCurrency,
					tblTempRateTableRate.ConnectionFeeCurrency,
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
				MinimumDuration,
				ConnectionFee,
				Preference,
				Blocked,
				RoutingCategoryID,
				RateCurrency,
				ConnectionFeeCurrency,
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
				tblRateTableRate.MinimumDuration,
				tblRateTableRate.ConnectionFee,
				tblRateTableRate.Preference,
				tblRateTableRate.Blocked,
				tblRateTableRate.RoutingCategoryID,
				tblRateTableRate.RateCurrency,
				tblRateTableRate.ConnectionFeeCurrency,
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
            MinimumDuration,
            ConnectionFee,
            Preference,
            Blocked,
            RoutingCategoryID,
            RateCurrency,
            ConnectionFeeCurrency,
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
            tblRateTableRate.MinimumDuration,
            tblRateTableRate.ConnectionFee,
            tblRateTableRate.Preference,
            tblRateTableRate.Blocked,
            tblRateTableRate.RoutingCategoryID,
			tblRateTableRate.RateCurrency,
			tblRateTableRate.ConnectionFeeCurrency,
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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Forbidden` varchar(100) ,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
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
		`Type`,
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
		`MinimumDuration`,
		`Blocked`,
		`RoutingCategoryID`,
		`RateCurrency`,
		`ConnectionFeeCurrency`,
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
		tblTempRateTableRate.Interval1 = CASE WHEN tblTempRateTableRate.Interval1 IS NOT NULL AND tblTempRateTableRate.Interval1 > 0
		THEN
			tblTempRateTableRate.Interval1
		ELSE
			CASE WHEN tblRate.Interval1 IS NOT NULL
			THEN
				tblRate.Interval1
			ELSE
				1
			END
		END,
		tblTempRateTableRate.IntervalN = CASE WHEN tblTempRateTableRate.IntervalN IS NOT NULL AND tblTempRateTableRate.IntervalN > 0
		THEN
			tblTempRateTableRate.IntervalN
		ELSE
			CASE WHEN tblRate.IntervalN IS NOT NULL
			THEN
				tblRate.IntervalN
			ElSE
				1
			END
		END,
		tblTempRateTableRate.MinimumDuration = CASE WHEN tblTempRateTableRate.MinimumDuration IS NOT NULL AND tblTempRateTableRate.MinimumDuration >= 0
		THEN
			tblTempRateTableRate.MinimumDuration
		ELSE
			CASE WHEN tblRate.MinimumDuration IS NOT NULL
			THEN
				tblRate.MinimumDuration
			ElSE
				0
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
			CONCAT(IF(OriginationCode IS NOT NULL,CONCAT(OriginationCode,'-'),''), Code, ' DUPLICATE CODE')
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
					`OriginationCode`,
					`OriginationDescription`,
					`DialString`,
					CASE WHEN ds.Description IS NULL OR ds.Description = ''
					THEN
						tblTempRateTableRate.Description
					ELSE
						ds.Description
					END
					AS Description,
					`Type`,
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
					`MinimumDuration`,
					tblTempRateTableRate.Forbidden as Forbidden,
					`RoutingCategoryID`,
					`RateCurrency`,
					`ConnectionFeeCurrency`,
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
					OriginationCode,
					OriginationDescription,
					Code,
					Description,
					`Type`,
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
					MinimumDuration,
					Forbidden,
					RoutingCategoryID,
					RateCurrency,
					ConnectionFeeCurrency,
					DialStringPrefix
				)
				SELECT DISTINCT
					`TempRateTableRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`Code`,
					`Description`,
					`Type`,
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
					`MinimumDuration`,
					`Forbidden`,
					`RoutingCategoryID`,
					`RateCurrency`,
					`ConnectionFeeCurrency`,
					`DialStringPrefix`
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
			`Type`,
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
			`MinimumDuration`,
			`Blocked`,
			`RoutingCategoryID`,
			`RateCurrency`,
			`ConnectionFeeCurrency`,
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
			`Type`,
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
			`MinimumDuration`,
			`Blocked`,
			`RoutingCategoryID`,
			`RateCurrency`,
			`ConnectionFeeCurrency`,
			`DialStringPrefix`
		FROM tblTempRateTableRate
		WHERE ProcessId = p_processId;

	END IF;

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
		--	distinct
			IF(p_Action='Deleted',RateTableRateID,TempRateTableRateID) AS RateTableRateID,
			`OriginationCode`,
			`OriginationDescription`,
			RTCL.`Code`,
			RTCL.`Description`,
			tz.Title,
			CONCAT(IFNULL(tblRateCurrency.Symbol,''), IFNULL(Rate,'')) AS Rate,
			CONCAT(IFNULL(tblRateCurrency.Symbol,''), IFNULL(RateN,'')) AS RateN,
			`EffectiveDate`,
			`EndDate`,
			CONCAT(IFNULL(tblConnectionFeeCurrency.Symbol,''), IFNULL(ConnectionFee,'')) AS ConnectionFee,
			`Interval1`,
			`IntervalN`,
			`MinimumDuration`,
			`Preference`,
			`Blocked`,
			RC.`Name` AS RoutingCategory
		FROM
			tblRateTableRateChangeLog AS RTCL
		JOIN
			tblTimezones tz ON RTCL.TimezonesID = tz.TimezonesID
		LEFT JOIN tblCurrency AS tblRateCurrency
			ON tblRateCurrency.CurrencyID = RTCL.RateCurrency
		LEFT JOIN tblCurrency AS tblConnectionFeeCurrency
			ON tblConnectionFeeCurrency.CurrencyID = RTCL.ConnectionFeeCurrency
		LEFT JOIN
			speakintelligentRouting.tblRoutingCategory RC ON RC.RoutingCategoryID = RTCL.RoutingCategoryID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action AND
			RTCL.TimezonesID = p_Timezone AND
			(p_Origination_Code IS NULL OR OriginationCode LIKE REPLACE(p_Origination_Code, '*', '%')) AND
			(p_Origination_Description IS NULL OR OriginationDescription LIKE REPLACE(p_Origination_Description, '*', '%')) AND
			(p_Code IS NULL OR p_Code = '' OR RTCL.Code LIKE REPLACE(p_Code, '*', '%')) AND
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN RTCL.Code
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN RTCL.Code
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationDESC') THEN MinimumDuration
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationASC') THEN MinimumDuration
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
			(p_Code IS NULL OR p_Code = '' OR RTCL.Code LIKE REPLACE(p_Code, '*', '%')) AND
			(p_Description IS NULL OR p_Description = '' OR RTCL.Description LIKE REPLACE(p_Description, '*', '%')) AND
			(p_RoutingCategoryID IS NULL OR RTCL.RoutingCategoryID = p_RoutingCategoryID);
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
		--	distinct
			`OriginationCode`,
			`OriginationDescription`,
			RTCL.`Code`,
			RTCL.`Description`,
			tz.Title,
			CONCAT(IFNULL(tblRateCurrency.Symbol,''), IFNULL(Rate,'')) AS Rate,
			CONCAT(IFNULL(tblRateCurrency.Symbol,''), IFNULL(RateN,'')) AS RateN,
			`EffectiveDate`,
			`EndDate`,
			CONCAT(IFNULL(tblConnectionFeeCurrency.Symbol,''), IFNULL(ConnectionFee,'')) AS ConnectionFee,
			`Interval1`,
			`IntervalN`,
			`MinimumDuration`,
			`Preference`,
			`Blocked`,
			RC.Name AS `RoutingCategory`
		FROM
			tblRateTableRateChangeLog AS RTCL
		JOIN
			tblTimezones tz ON RTCL.TimezonesID = tz.TimezonesID
		LEFT JOIN tblCurrency AS tblRateCurrency
			ON tblRateCurrency.CurrencyID = RTCL.RateCurrency
		LEFT JOIN tblCurrency AS tblConnectionFeeCurrency
			ON tblConnectionFeeCurrency.CurrencyID = RTCL.ConnectionFeeCurrency
		LEFT JOIN
			speakintelligentRouting.tblRoutingCategory RC ON RC.RoutingCategoryID = RTCL.RoutingCategoryID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action AND
			RTCL.TimezonesID = p_Timezone AND
			(p_Origination_Code IS NULL OR OriginationCode LIKE REPLACE(p_Origination_Code, '*', '%')) AND
			(p_Origination_Description IS NULL OR OriginationDescription LIKE REPLACE(p_Origination_Description, '*', '%')) AND
			(p_Code IS NULL OR p_Code = '' OR RTCL.Code LIKE REPLACE(p_Code, '*', '%')) AND
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
	DECLARE v_RateTableAppliedTo_ INT;

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = p_companyId AND `Key`='RateApprovalProcess';
	SELECT AppliedTo INTO v_RateTableAppliedTo_ FROM tblRateTable WHERE RateTableID = p_RateTableId;

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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
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
		`Type` varchar(50) NULL DEFAULT NULL,
		Rate DECIMAL(18, 6),
		RateN DECIMAL(18, 6),
		EffectiveDate DATETIME,
		EndDate Datetime ,
		Interval1 INT,
		IntervalN INT,
		MinimumDuration INT,
		ConnectionFee DECIMAL(18, 6),
		Preference varchar(100) ,
		Blocked tinyint,
		RoutingCategoryID int,
		RateCurrency INT(11) NULL DEFAULT NULL,
		ConnectionFeeCurrency INT(11) NULL DEFAULT NULL,
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
				tblTempRateTableRate.Interval1 = vrcl.Interval1,
				tblTempRateTableRate.IntervalN = vrcl.IntervalN,
				tblTempRateTableRate.MinimumDuration = vrcl.MinimumDuration
			WHERE
				vrcl.`Action` = 'New'
				AND vrcl.Interval1 IS NOT NULL
				AND vrcl.IntervalN IS NOT NULL
				AND vrcl.MinimumDuration IS NOT NULL;



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
				MinimumDuration,
				ConnectionFee,
				Preference,
				Blocked,
				RoutingCategoryID,
				RateCurrency,
				ConnectionFeeCurrency,
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
				tblRateTableRate.MinimumDuration,
				tblRateTableRate.ConnectionFee,
				tblRateTableRate.Preference,
				tblRateTableRate.Blocked,
				tblRateTableRate.RoutingCategoryID,
				tblRateTableRate.RateCurrency,
				tblRateTableRate.ConnectionFeeCurrency,
				now() AS deleted_at
			FROM tblRateTableRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	/*JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableRate.TimezonesID*/
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

			CALL prc_UpdateCountryIDRateTableRate('tmp_TempRateTableRate_');

			-- Destination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				`Type`,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN,
				MinimumDuration
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				vc.`Type`,
				'RMService',
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN,
				MinimumDuration
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.Code,
					MAX(tblTempRateTableRate.Description) AS Description,
					MAX(tblTempRateTableRate.`Type`) AS `Type`,
					MAX(tblTempRateTableRate.CodeDeckId) AS CodeDeckId,
					MAX(tblTempRateTableRate.Interval1) AS Interval1,
					MAX(tblTempRateTableRate.IntervalN) AS IntervalN,
					MAX(tblTempRateTableRate.MinimumDuration) AS MinimumDuration,
					MAX(tmp_Prefix.CountryID) AS CountryID
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				LEFT JOIN
					tmp_Prefix ON tmp_Prefix.Prefix = tblTempRateTableRate.Code
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableRate.Code
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
				IntervalN,
				MinimumDuration
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN,
				MinimumDuration
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.OriginationCode AS Code,
					MAX(tblTempRateTableRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableRate.CodeDeckId) AS CodeDeckId,
					MAX(tblTempRateTableRate.Interval1) AS Interval1,
					MAX(tblTempRateTableRate.IntervalN) AS IntervalN,
					MAX(tblTempRateTableRate.MinimumDuration) AS MinimumDuration,
					MAX(tmp_Prefix.CountryID) AS CountryID
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				LEFT JOIN
					tmp_Prefix ON tmp_Prefix.Prefix = tblTempRateTableRate.Code
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.OriginationCode IS NOT NULL AND tblTempRateTableRate.OriginationCode != ''
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableRate.OriginationCode
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
		WHERE tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID));



		UPDATE tblRate
		JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON 	  tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblTempRateTableRate.Code = tblRate.Code
			AND  tblTempRateTableRate.ProcessId = p_processId
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		SET
			tblRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRate.IntervalN = tblTempRateTableRate.IntervalN,
			tblRate.MinimumDuration = tblTempRateTableRate.MinimumDuration
		WHERE
			tblTempRateTableRate.Interval1 IS NOT NULL
			AND tblTempRateTableRate.IntervalN IS NOT NULL
			AND tblTempRateTableRate.MinimumDuration IS NOT NULL
			AND
			(
				tblRate.Interval1 != tblTempRateTableRate.Interval1 OR
				tblRate.IntervalN != tblTempRateTableRate.IntervalN OR
				tblRate.MinimumDuration != tblTempRateTableRate.MinimumDuration
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
			tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN,
			tblRateTableRate.MinimumDuration = tblTempRateTableRate.MinimumDuration
		WHERE
			tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID));




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
			tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID));



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
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
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
			MinimumDuration,
			Preference,
			Blocked,
			RoutingCategoryID,
			PreviousRate,
			ApprovedStatus,
			RateCurrency,
			ConnectionFeeCurrency
		)
		SELECT DISTINCT
			p_RateTableId,
			tblTempRateTableRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
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
			tblTempRateTableRate.MinimumDuration,
			tblTempRateTableRate.Preference,
			tblTempRateTableRate.Blocked,
			tblTempRateTableRate.RoutingCategoryID,
			IFNULL(tmp_PreviousRate.PreviousRate,0) AS PreviousRate,
			 -- if rate table is not vendor rate table and Rate Approval Process is on then rate will be upload as not approved
			IF(v_RateTableAppliedTo_!=2,IF(v_RateApprovalProcess_=1,0,1),1) AS ApprovedStatus,
			tblTempRateTableRate.RateCurrency,
			tblTempRateTableRate.ConnectionFeeCurrency
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




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableRateAA`(
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

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
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
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableRate (
		RateTableRateID INT,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
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
		`MinimumDuration` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
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

			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				AND vrcl.OriginationCode = tblTempRateTableRate.OriginationCode
			SET
				tblTempRateTableRate.Interval1 = vrcl.Interval1,
				tblTempRateTableRate.IntervalN = vrcl.IntervalN,
				tblTempRateTableRate.MinimumDuration = vrcl.MinimumDuration
			WHERE
				vrcl.`Action` = 'New'
				AND vrcl.Interval1 IS NOT NULL
				AND vrcl.IntervalN IS NOT NULL
				AND vrcl.MinimumDuration IS NOT NULL;

		END IF;


		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableRate(
				RateTableRateID,
				CodeDeckId,
				TimezonesID,
				OriginationCode,
				OriginationDescription,
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
				MinimumDuration,
				Blocked,
				RoutingCategoryID,
				RateCurrency,
				ConnectionFeeCurrency,
				DialStringPrefix
			)
			SELECT DISTINCT
				tblRateTableRate.RateTableRateID,
				tblRateTable.CodeDeckId,
				tblRateTableRate.TimezonesID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRate.Code,
				tblRate.Description,
				tblRateTableRate.Rate,
				tblRateTableRate.RateN,
				tblRateTableRate.EffectiveDate,
				NULL AS EndDate,
				'Delete' AS `Change`,
				p_processId AS ProcessId,
				tblRateTableRate.Preference,
				tblRateTableRate.ConnectionFee,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.MinimumDuration,
				tblRateTableRate.Blocked,
				tblRateTableRate.RoutingCategoryID,
				tblRateTableRate.RateCurrency,
				tblRateTableRate.ConnectionFeeCurrency,
				'' AS DialStringPrefix
			FROM tblRateTableRate
			JOIN tblRateTable
				ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
			JOIN tblRate
				ON tblRate.RateID = tblRateTableRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	/*JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableRate.TimezonesID*/
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



		END IF;



		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate_2 AS (SELECT * FROM tmp_TempRateTableRate_);

		IF  p_addNewCodesToCodeDeck = 1
		THEN

			CALL prc_UpdateCountryIDRateTableRate('tmp_TempRateTableRate_');

			-- Destination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				`Type`,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN,
				MinimumDuration
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				vc.`Type`,
				'RMService',
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN,
				MinimumDuration
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.Code,
					MAX(tblTempRateTableRate.Description) AS Description,
					MAX(tblTempRateTableRate.`Type`) AS `Type`,
					MAX(tblTempRateTableRate.CodeDeckId) AS CodeDeckId,
					MAX(tblTempRateTableRate.Interval1) AS Interval1,
					MAX(tblTempRateTableRate.IntervalN) AS IntervalN,
					MAX(tblTempRateTableRate.MinimumDuration) AS MinimumDuration,
					MAX(tmp_Prefix.CountryID) AS CountryID
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				LEFT JOIN
					tmp_Prefix ON tmp_Prefix.Prefix = tblTempRateTableRate.Code
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableRate.Code
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
				IntervalN,
				MinimumDuration
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN,
				MinimumDuration
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.OriginationCode AS Code,
					MAX(tblTempRateTableRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableRate.CodeDeckId) AS CodeDeckId,
					MAX(tblTempRateTableRate.Interval1) AS Interval1,
					MAX(tblTempRateTableRate.IntervalN) AS IntervalN,
					MAX(tblTempRateTableRate.MinimumDuration) AS MinimumDuration,
					MAX(tmp_Prefix.CountryID) AS CountryID
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				LEFT JOIN
					tmp_Prefix ON tmp_Prefix.Prefix = tblTempRateTableRate.Code
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.OriginationCode IS NOT NULL AND tblTempRateTableRate.OriginationCode != ''
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableRate.OriginationCode
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



		UPDATE tblRate
		JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON 	  tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblTempRateTableRate.Code = tblRate.Code
			AND  tblTempRateTableRate.ProcessId = p_processId
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		SET
			tblRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRate.IntervalN = tblTempRateTableRate.IntervalN,
			tblRate.MinimumDuration = tblTempRateTableRate.MinimumDuration
		WHERE
			tblTempRateTableRate.Interval1 IS NOT NULL
			AND tblTempRateTableRate.IntervalN IS NOT NULL
			AND
			(
				tblRate.Interval1 != tblTempRateTableRate.Interval1 OR
				tblRate.IntervalN != tblTempRateTableRate.IntervalN OR
				tblRate.MinimumDuration != tblTempRateTableRate.MinimumDuration
			);


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
		JOIN tblRateTableRateAA AS tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.Rate = tblRateTableRate.Rate
		WHERE
			tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID));



	--	SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		-- delete from live table if code is already exist but rate is different
		/*UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRateAA AS tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		SET tblRateTableRate.EndDate = NOW()
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');*/


		-- call prc_ArchiveOldRateTableRateAA(p_RateTableId, NULL,p_UserName);



		INSERT INTO tblRateTableRateAA (
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
			MinimumDuration,
			Preference,
			Blocked,
			RoutingCategoryID,
			PreviousRate,
			ApprovedStatus,
			RateCurrency,
			ConnectionFeeCurrency
		)
		SELECT DISTINCT
			p_RateTableId,
			tblTempRateTableRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
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
			tblTempRateTableRate.MinimumDuration,
			tblTempRateTableRate.Preference,
			tblTempRateTableRate.Blocked,
			tblTempRateTableRate.RoutingCategoryID,
			0 AS PreviousRate,
			0 AS ApprovedStatus,
			tblTempRateTableRate.RateCurrency,
			tblTempRateTableRate.ConnectionFeeCurrency
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		/*LEFT JOIN tblRateTableRateAA AS tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.EffectiveDate = tblRateTableRate.EffectiveDate*/
		WHERE /*tblRateTableRate.RateTableRateAAID IS NULL
			AND*/ tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


		IF((SELECT COUNT(*) FROM tmp_Delete_RateTableRate) > 0)
		THEN
			-- rates which needs to deleted
			INSERT INTO tblRateTableRateAA (
				RateTableRateID,
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
				MinimumDuration,
				Preference,
				Blocked,
				RoutingCategoryID,
				PreviousRate,
				ApprovedStatus,
				RateCurrency,
				ConnectionFeeCurrency
			)
			SELECT DISTINCT
				tblTempRateTableRate.RateTableRateID,
				p_RateTableId,
				tblTempRateTableRate.TimezonesID,
				IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
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
				tblTempRateTableRate.MinimumDuration,
				tblTempRateTableRate.Preference,
				tblTempRateTableRate.Blocked,
				tblTempRateTableRate.RoutingCategoryID,
				0 AS PreviousRate,
				3 AS ApprovedStatus, -- delete status
				tblTempRateTableRate.RateCurrency,
				tblTempRateTableRate.ConnectionFeeCurrency
			FROM tmp_Delete_RateTableRate as tblTempRateTableRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTableRate.Code
				AND tblRate.CompanyID = p_companyId
				AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
				AND OriginationRate.CompanyID = p_companyId
				AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId;


			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		END IF; -- END IF((SELECT COUNT(*) FROM tmp_Delete_RateTableRate) > 0)

	END IF; -- IF newstringcode = 0

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Affected ' );

	call prc_ArchiveOldRateTableRateAA(p_RateTableId, NULL,p_UserName);


	DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

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

	UPDATE
		tblRateTableRate rtr
	INNER JOIN tblRateTableRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND rtr2.OriginationRateID = rtr.OriginationRateID
		AND rtr2.TimezonesID = rtr.TimezonesID
	SET
		rtr.EndDate=NOW()
	WHERE
		(FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0) AND rtr.EffectiveDate <= NOW()) AND
		(FIND_IN_SET(rtr2.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr2.TimezonesID,p_TimezonesIDs) != 0) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableRateID != rtr2.RateTableRateID;


	INSERT INTO tblRateTableRateArchive
	(
		RateTableRateID,
		RateTableId,
		TimezonesID,
		OriginationRateID,
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
		MinimumDuration,
		ConnectionFee,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		RateCurrency,
		ConnectionFeeCurrency,
		Notes
	)
	SELECT DISTINCT -- null ,
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
		`MinimumDuration`,
		`ConnectionFee`,
		`RoutingCategoryID`,
		`Preference`,
		`Blocked`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		`RateCurrency`,
		`ConnectionFeeCurrency`,
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




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableRateAA`(
	IN `p_RateTableIds` LONGTEXT,
	IN `p_TimezonesIDs` LONGTEXT,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableRateAA rtr
	INNER JOIN tblRateTableRateAA rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND rtr2.OriginationRateID = rtr.OriginationRateID
		AND rtr2.TimezonesID = rtr.TimezonesID
	SET
		rtr.EndDate=NOW()
	WHERE
		(FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0) AND rtr.EffectiveDate <= NOW()) AND
		(FIND_IN_SET(rtr2.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr2.TimezonesID,p_TimezonesIDs) != 0) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableRateAAID != rtr2.RateTableRateAAID;


	INSERT INTO tblRateTableRateArchive
	(
		RateTableRateID,
		RateTableId,
		TimezonesID,
		OriginationRateID,
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
		MinimumDuration,
		ConnectionFee,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		RateCurrency,
		ConnectionFeeCurrency,
		Notes
	)
	SELECT DISTINCT -- null ,
		`RateTableRateAAID`,
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
		`MinimumDuration`,
		`ConnectionFee`,
		`RoutingCategoryID`,
		`Preference`,
		`Blocked`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		`RateCurrency`,
		`ConnectionFeeCurrency`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM tblRateTableRateAA
	WHERE
		FIND_IN_SET(RateTableId,p_RateTableIds) != 0
		AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(TimezonesID,p_TimezonesIDs) != 0)
		AND EndDate <= NOW()
		AND ApprovedStatus = 2; -- only rejected rates will be archive


	DELETE  rtr
	FROM tblRateTableRateAA rtr
	WHERE
		FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0
		AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0)
		AND EndDate <= NOW();


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
	IN `p_code` VARCHAR(50),
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
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
		ID INT,
		AccessType varchar(200),
		Country VARCHAR(200),
		OriginationCode VARCHAR(50),
		Code VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		TimezoneTitle VARCHAR(50),
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
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		CostPerCallCurrency INT(11),
		CostPerMinuteCurrency INT(11),
		SurchargePerCallCurrency INT(11),
		SurchargePerMinuteCurrency INT(11),
		OutpaymentPerCallCurrency INT(11),
		OutpaymentPerMinuteCurrency INT(11),
		SurchargesCurrency INT(11),
		ChargebackCurrency INT(11),
		CollectionCostAmountCurrency INT(11),
		RegistrationCostPerNumberCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		CostPerCallCurrencySymbol VARCHAR(255),
		CostPerMinuteCurrencySymbol VARCHAR(255),
		SurchargePerCallCurrencySymbol VARCHAR(255),
		SurchargePerMinuteCurrencySymbol VARCHAR(255),
		OutpaymentPerCallCurrencySymbol VARCHAR(255),
		OutpaymentPerMinuteCurrencySymbol VARCHAR(255),
		SurchargesCurrencySymbol VARCHAR(255),
		ChargebackCurrencySymbol VARCHAR(255),
		CollectionCostAmountCurrencySymbol VARCHAR(255),
		RegistrationCostPerNumberCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableDIDRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTableDIDRate_
    SELECT
		RateTableDIDRateID AS ID,
		AccessType,
		tblCountry.Country,
		OriginationRate.Code AS OriginationCode,
		tblRate.Code,
		City,
		Tariff,
		tblTimezones.Title AS TimezoneTitle,
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
		tblRateTableDIDRate.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblCostPerCallCurrency.CurrencyID AS CostPerCallCurrency,
		tblCostPerMinuteCurrency.CurrencyID AS CostPerMinuteCurrency,
		tblSurchargePerCallCurrency.CurrencyID AS SurchargePerCallCurrency,
		tblSurchargePerMinuteCurrency.CurrencyID AS SurchargePerMinuteCurrency,
		tblOutpaymentPerCallCurrency.CurrencyID AS OutpaymentPerCallCurrency,
		tblOutpaymentPerMinuteCurrency.CurrencyID AS OutpaymentPerMinuteCurrency,
		tblSurchargesCurrency.CurrencyID AS SurchargesCurrency,
		tblChargebackCurrency.CurrencyID AS ChargebackCurrency,
		tblCollectionCostAmountCurrency.CurrencyID AS CollectionCostAmountCurrency,
		tblRegistrationCostPerNumberCurrency.CurrencyID AS RegistrationCostPerNumberCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblCostPerCallCurrency.Symbol,'') AS CostPerCallCurrencySymbol,
		IFNULL(tblCostPerMinuteCurrency.Symbol,'') AS CostPerMinuteCurrencySymbol,
		IFNULL(tblSurchargePerCallCurrency.Symbol,'') AS SurchargePerCallCurrencySymbol,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol,'') AS SurchargePerMinuteCurrencySymbol,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol,'') AS OutpaymentPerCallCurrencySymbol,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,'') AS OutpaymentPerMinuteCurrencySymbol,
		IFNULL(tblSurchargesCurrency.Symbol,'') AS SurchargesCurrencySymbol,
		IFNULL(tblChargebackCurrency.Symbol,'') AS ChargebackCurrencySymbol,
		IFNULL(tblCollectionCostAmountCurrency.Symbol,'') AS CollectionCostAmountCurrencySymbol,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,'') AS RegistrationCostPerNumberCurrencySymbol,
		tblRateTableDIDRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTableDIDRate
        ON tblRateTableDIDRate.RateID = tblRate.RateID
        AND tblRateTableDIDRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTableDIDRate.TimezonesID
    LEFT JOIN tblRate AS OriginationRate
    	  ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
    LEFT JOIN tblCountry
    		ON tblCountry.CountryID = tblRate.CountryID
    LEFT JOIN tblCurrency AS tblOneOffCostCurrency
        ON tblOneOffCostCurrency.CurrencyID = tblRateTableDIDRate.OneOffCostCurrency
    LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
        ON tblMonthlyCostCurrency.CurrencyID = tblRateTableDIDRate.MonthlyCostCurrency
    LEFT JOIN tblCurrency AS tblCostPerCallCurrency
        ON tblCostPerCallCurrency.CurrencyID = tblRateTableDIDRate.CostPerCallCurrency
    LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
        ON tblCostPerMinuteCurrency.CurrencyID = tblRateTableDIDRate.CostPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
        ON tblSurchargePerCallCurrency.CurrencyID = tblRateTableDIDRate.SurchargePerCallCurrency
    LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
        ON tblSurchargePerMinuteCurrency.CurrencyID = tblRateTableDIDRate.SurchargePerMinuteCurrency
    LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
        ON tblOutpaymentPerCallCurrency.CurrencyID = tblRateTableDIDRate.OutpaymentPerCallCurrency
    LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
        ON tblOutpaymentPerMinuteCurrency.CurrencyID = tblRateTableDIDRate.OutpaymentPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblSurchargesCurrency
        ON tblSurchargesCurrency.CurrencyID = tblRateTableDIDRate.SurchargesCurrency
    LEFT JOIN tblCurrency AS tblChargebackCurrency
        ON tblChargebackCurrency.CurrencyID = tblRateTableDIDRate.ChargebackCurrency
    LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
        ON tblCollectionCostAmountCurrency.CurrencyID = tblRateTableDIDRate.CollectionCostAmountCurrency
    LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
        ON tblRegistrationCostPerNumberCurrency.CurrencyID = tblRateTableDIDRate.RegistrationCostPerNumberCurrency
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
    LEFT JOIN tblCurrency AS tblRateTableCurrency
    	  ON tblRateTableCurrency.CurrencyId = tblRateTable.CurrencyID
    WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID is null OR tblRate.CountryID = p_contryID)
		AND (p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%'))
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%')) 
		AND (p_City IS NULL OR tblRateTableDIDRate.City = p_City)
		AND (p_Tariff IS NULL OR tblRateTableDIDRate.Tariff = p_Tariff)
		AND (p_AccessType IS NULL OR tblRateTableDIDRate.AccessType = p_AccessType)
		AND (p_ApprovedStatus IS NULL OR tblRateTableDIDRate.ApprovedStatus = p_ApprovedStatus)
		AND TrunkID = p_trunkID
		AND (p_TimezonesID IS NULL OR tblRateTableDIDRate.TimezonesID = p_TimezonesID)
		AND (
				p_effective = 'All'
				OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
				OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	  IF p_effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate4_ as (select * from tmp_RateTableDIDRate_);
         DELETE n1 FROM tmp_RateTableDIDRate_ n1, tmp_RateTableDIDRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID AND n1.City = n2.City AND n1.Tariff = n2.Tariff;
		END IF;


    IF p_isExport = 0
    THEN

       	SELECT * FROM tmp_RateTableDIDRate_
					ORDER BY 
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityDESC') THEN City
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityASC') THEN City
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffDESC') THEN Tariff
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffASC') THEN Tariff
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeDESC') THEN AccessType
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeASC') THEN AccessType
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTableDIDRate_;

    END IF;

	 -- basic view
    IF p_isExport = 10
    THEN
        SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			EffectiveDate,
			ApprovedStatus
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	 -- advance view
    IF p_isExport = 11
    THEN
        SELECT
        	AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`,
			ApprovedStatus
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableDIDRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableDIDRateAA`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_contryID` INT,
	IN `p_origination_code` VARCHAR(50),
	IN `p_code` VARCHAR(50),
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
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
		ID INT,
		AccessType varchar(200),
		Country VARCHAR(200),
		OriginationCode VARCHAR(50),
		Code VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		TimezoneTitle VARCHAR(50),
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
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		CostPerCallCurrency INT(11),
		CostPerMinuteCurrency INT(11),
		SurchargePerCallCurrency INT(11),
		SurchargePerMinuteCurrency INT(11),
		OutpaymentPerCallCurrency INT(11),
		OutpaymentPerMinuteCurrency INT(11),
		SurchargesCurrency INT(11),
		ChargebackCurrency INT(11),
		CollectionCostAmountCurrency INT(11),
		RegistrationCostPerNumberCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		CostPerCallCurrencySymbol VARCHAR(255),
		CostPerMinuteCurrencySymbol VARCHAR(255),
		SurchargePerCallCurrencySymbol VARCHAR(255),
		SurchargePerMinuteCurrencySymbol VARCHAR(255),
		OutpaymentPerCallCurrencySymbol VARCHAR(255),
		OutpaymentPerMinuteCurrencySymbol VARCHAR(255),
		SurchargesCurrencySymbol VARCHAR(255),
		ChargebackCurrencySymbol VARCHAR(255),
		CollectionCostAmountCurrencySymbol VARCHAR(255),
		RegistrationCostPerNumberCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableDIDRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTableDIDRate_
    SELECT
		RateTableDIDRateAAID AS ID,
		AccessType,
		tblCountry.Country,
		OriginationRate.Code AS OriginationCode,
		tblRate.Code,
		City,
		Tariff,
		tblTimezones.Title AS TimezoneTitle,
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
		RateTableDIDRateAAID,
		OriginationRate.RateID AS OriginationRateID,
		tblRate.RateID,
		tblRateTableDIDRate.ApprovedStatus,
		tblRateTableDIDRate.ApprovedBy,
		tblRateTableDIDRate.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblCostPerCallCurrency.CurrencyID AS CostPerCallCurrency,
		tblCostPerMinuteCurrency.CurrencyID AS CostPerMinuteCurrency,
		tblSurchargePerCallCurrency.CurrencyID AS SurchargePerCallCurrency,
		tblSurchargePerMinuteCurrency.CurrencyID AS SurchargePerMinuteCurrency,
		tblOutpaymentPerCallCurrency.CurrencyID AS OutpaymentPerCallCurrency,
		tblOutpaymentPerMinuteCurrency.CurrencyID AS OutpaymentPerMinuteCurrency,
		tblSurchargesCurrency.CurrencyID AS SurchargesCurrency,
		tblChargebackCurrency.CurrencyID AS ChargebackCurrency,
		tblCollectionCostAmountCurrency.CurrencyID AS CollectionCostAmountCurrency,
		tblRegistrationCostPerNumberCurrency.CurrencyID AS RegistrationCostPerNumberCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblCostPerCallCurrency.Symbol,'') AS CostPerCallCurrencySymbol,
		IFNULL(tblCostPerMinuteCurrency.Symbol,'') AS CostPerMinuteCurrencySymbol,
		IFNULL(tblSurchargePerCallCurrency.Symbol,'') AS SurchargePerCallCurrencySymbol,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol,'') AS SurchargePerMinuteCurrencySymbol,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol,'') AS OutpaymentPerCallCurrencySymbol,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,'') AS OutpaymentPerMinuteCurrencySymbol,
		IFNULL(tblSurchargesCurrency.Symbol,'') AS SurchargesCurrencySymbol,
		IFNULL(tblChargebackCurrency.Symbol,'') AS ChargebackCurrencySymbol,
		IFNULL(tblCollectionCostAmountCurrency.Symbol,'') AS CollectionCostAmountCurrencySymbol,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,'') AS RegistrationCostPerNumberCurrencySymbol,
		tblRateTableDIDRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTableDIDRateAA AS tblRateTableDIDRate
        ON tblRateTableDIDRate.RateID = tblRate.RateID
        AND tblRateTableDIDRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTableDIDRate.TimezonesID
    LEFT JOIN tblRate AS OriginationRate
    	  ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
    LEFT JOIN tblCountry
    		ON tblCountry.CountryID = tblRate.CountryID
    LEFT JOIN tblCurrency AS tblOneOffCostCurrency
        ON tblOneOffCostCurrency.CurrencyID = tblRateTableDIDRate.OneOffCostCurrency
    LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
        ON tblMonthlyCostCurrency.CurrencyID = tblRateTableDIDRate.MonthlyCostCurrency
    LEFT JOIN tblCurrency AS tblCostPerCallCurrency
        ON tblCostPerCallCurrency.CurrencyID = tblRateTableDIDRate.CostPerCallCurrency
    LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
        ON tblCostPerMinuteCurrency.CurrencyID = tblRateTableDIDRate.CostPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
        ON tblSurchargePerCallCurrency.CurrencyID = tblRateTableDIDRate.SurchargePerCallCurrency
    LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
        ON tblSurchargePerMinuteCurrency.CurrencyID = tblRateTableDIDRate.SurchargePerMinuteCurrency
    LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
        ON tblOutpaymentPerCallCurrency.CurrencyID = tblRateTableDIDRate.OutpaymentPerCallCurrency
    LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
        ON tblOutpaymentPerMinuteCurrency.CurrencyID = tblRateTableDIDRate.OutpaymentPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblSurchargesCurrency
        ON tblSurchargesCurrency.CurrencyID = tblRateTableDIDRate.SurchargesCurrency
    LEFT JOIN tblCurrency AS tblChargebackCurrency
        ON tblChargebackCurrency.CurrencyID = tblRateTableDIDRate.ChargebackCurrency
    LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
        ON tblCollectionCostAmountCurrency.CurrencyID = tblRateTableDIDRate.CollectionCostAmountCurrency
    LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
        ON tblRegistrationCostPerNumberCurrency.CurrencyID = tblRateTableDIDRate.RegistrationCostPerNumberCurrency
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
    LEFT JOIN tblCurrency AS tblRateTableCurrency
    	  ON tblRateTableCurrency.CurrencyId = tblRateTable.CurrencyID
    WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID is null OR tblRate.CountryID = p_contryID)
		AND (p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%'))
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_City IS NULL OR tblRateTableDIDRate.City = p_City)
		AND (p_Tariff IS NULL OR tblRateTableDIDRate.Tariff = p_Tariff)
		AND (p_AccessType IS NULL OR tblRateTableDIDRate.AccessType = p_AccessType)
		AND (p_ApprovedStatus IS NULL OR tblRateTableDIDRate.ApprovedStatus = p_ApprovedStatus)
		AND TrunkID = p_trunkID
		AND (p_TimezonesID IS NULL OR tblRateTableDIDRate.TimezonesID = p_TimezonesID)
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

       	SELECT * FROM tmp_RateTableDIDRate_
					ORDER BY 
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityDESC') THEN City
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityASC') THEN City
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffDESC') THEN Tariff
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffASC') THEN Tariff
                END ASC,
	             CASE
	                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeDESC') THEN AccessType
	             END DESC,
	             CASE
	                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeASC') THEN AccessType
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTableDIDRate_;

    END IF;

	 -- basic view
    IF p_isExport = 10
    THEN
        SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			EffectiveDate,
			ApprovedStatus
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	 -- advance view
    IF p_isExport = 11
    THEN
        SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`,
			ApprovedStatus
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTablePKGRate`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTablePKGRate`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_TimezonesID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_RateTablePKGRate_ (
		ID INT,
		TimezoneTitle VARCHAR(50),
		Code VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		PackageCostPerMinute DECIMAL(18,6),
		RecordingCostPerMinute DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		ModifiedBy VARCHAR(50),
		RateTablePKGRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		PackageCostPerMinuteCurrency INT(11),
		RecordingCostPerMinuteCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		PackageCostPerMinuteCurrencySymbol VARCHAR(255),
		RecordingCostPerMinuteCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTablePKGRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTablePKGRate_
    SELECT
		RateTablePKGRateID AS ID,
		tblTimezones.Title AS TimezoneTitle,
		tblRate.Code,
		OneOffCost,
		MonthlyCost,
		PackageCostPerMinute,
		RecordingCostPerMinute,
		IFNULL(tblRateTablePKGRate.EffectiveDate, NOW()) as EffectiveDate,
		tblRateTablePKGRate.EndDate,
		tblRateTablePKGRate.updated_at,
		tblRateTablePKGRate.ModifiedBy,
		RateTablePKGRateID,
		tblRate.RateID,
		tblRateTablePKGRate.ApprovedStatus,
		tblRateTablePKGRate.ApprovedBy,
		tblRateTablePKGRate.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblPackageCostPerMinuteCurrency.CurrencyID AS PackageCostPerMinuteCurrency,
		tblRecordingCostPerMinuteCurrency.CurrencyID AS RecordingCostPerMinuteCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblPackageCostPerMinuteCurrency.Symbol,'') AS PackageCostPerMinuteCurrencySymbol,
		IFNULL(tblRecordingCostPerMinuteCurrency.Symbol,'') AS RecordingCostPerMinuteCurrencySymbol,
		tblRateTablePKGRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTablePKGRate
        ON tblRateTablePKGRate.RateID = tblRate.RateID
        AND tblRateTablePKGRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTablePKGRate.TimezonesID
    LEFT JOIN tblCurrency AS tblOneOffCostCurrency
        ON tblOneOffCostCurrency.CurrencyID = tblRateTablePKGRate.OneOffCostCurrency
    LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
        ON tblMonthlyCostCurrency.CurrencyID = tblRateTablePKGRate.MonthlyCostCurrency
    LEFT JOIN tblCurrency AS tblPackageCostPerMinuteCurrency
        ON tblPackageCostPerMinuteCurrency.CurrencyID = tblRateTablePKGRate.PackageCostPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblRecordingCostPerMinuteCurrency
        ON tblRecordingCostPerMinuteCurrency.CurrencyID = tblRateTablePKGRate.RecordingCostPerMinuteCurrency
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTablePKGRate.RateTableId
    WHERE
		(tblRate.CompanyID = p_companyid)
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_ApprovedStatus IS NULL OR tblRateTablePKGRate.ApprovedStatus = p_ApprovedStatus)
		AND (p_TimezonesID IS NULL OR tblRateTablePKGRate.TimezonesID = p_TimezonesID)
		AND (
				p_effective = 'All'
				OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
				OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate4_ as (select * from tmp_RateTablePKGRate_);
		DELETE n1 FROM tmp_RateTablePKGRate_ n1, tmp_RateTablePKGRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
			AND  n1.RateID = n2.RateID AND n1.TimezonesID = n2.TimezonesID;
	END IF;


    IF p_isExport = 0
    THEN

       	SELECT * FROM tmp_RateTablePKGRate_
			ORDER BY
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteDESC') THEN PackageCostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteASC') THEN PackageCostPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteDESC') THEN RecordingCostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteASC') THEN RecordingCostPerMinute
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTablePKGRate_;

    END IF;

    IF p_isExport = 1
    THEN
        SELECT
        	TimezoneTitle AS `Time of Day`,
			Code AS PackageName,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(PackageCostPerMinuteCurrencySymbol,PackageCostPerMinute) AS PackageCostPerMinute,
			CONCAT(RecordingCostPerMinuteCurrencySymbol,RecordingCostPerMinute) AS RecordingCostPerMinute,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`,
			ApprovedStatus
        FROM
		  		tmp_RateTablePKGRate_;
    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTablePKGRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTablePKGRateAA`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_TimezonesID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_RateTablePKGRate_ (
		ID INT,
		TimezoneTitle VARCHAR(50),
		Code VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		PackageCostPerMinute DECIMAL(18,6),
		RecordingCostPerMinute DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		ModifiedBy VARCHAR(50),
		RateTablePKGRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		PackageCostPerMinuteCurrency INT(11),
		RecordingCostPerMinuteCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		PackageCostPerMinuteCurrencySymbol VARCHAR(255),
		RecordingCostPerMinuteCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTablePKGRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTablePKGRate_
    SELECT
		RateTablePKGRateAAID AS ID,
		tblTimezones.Title AS TimezoneTitle,
		tblRate.Code,
		OneOffCost,
		MonthlyCost,
		PackageCostPerMinute,
		RecordingCostPerMinute,
		IFNULL(tblRateTablePKGRate.EffectiveDate, NOW()) as EffectiveDate,
		tblRateTablePKGRate.EndDate,
		tblRateTablePKGRate.updated_at,
		tblRateTablePKGRate.ModifiedBy,
		RateTablePKGRateAAID,
		tblRate.RateID,
		tblRateTablePKGRate.ApprovedStatus,
		tblRateTablePKGRate.ApprovedBy,
		tblRateTablePKGRate.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblPackageCostPerMinuteCurrency.CurrencyID AS PackageCostPerMinuteCurrency,
		tblRecordingCostPerMinuteCurrency.CurrencyID AS RecordingCostPerMinuteCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblPackageCostPerMinuteCurrency.Symbol,'') AS PackageCostPerMinuteCurrencySymbol,
		IFNULL(tblRecordingCostPerMinuteCurrency.Symbol,'') AS RecordingCostPerMinuteCurrencySymbol,
		tblRateTablePKGRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTablePKGRateAA AS tblRateTablePKGRate
        ON tblRateTablePKGRate.RateID = tblRate.RateID
        AND tblRateTablePKGRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTablePKGRate.TimezonesID
    LEFT JOIN tblCurrency AS tblOneOffCostCurrency
        ON tblOneOffCostCurrency.CurrencyID = tblRateTablePKGRate.OneOffCostCurrency
    LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
        ON tblMonthlyCostCurrency.CurrencyID = tblRateTablePKGRate.MonthlyCostCurrency
    LEFT JOIN tblCurrency AS tblPackageCostPerMinuteCurrency
        ON tblPackageCostPerMinuteCurrency.CurrencyID = tblRateTablePKGRate.PackageCostPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblRecordingCostPerMinuteCurrency
        ON tblRecordingCostPerMinuteCurrency.CurrencyID = tblRateTablePKGRate.RecordingCostPerMinuteCurrency
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTablePKGRate.RateTableId
    WHERE
		(tblRate.CompanyID = p_companyid)
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_ApprovedStatus IS NULL OR tblRateTablePKGRate.ApprovedStatus = p_ApprovedStatus)
		AND (p_TimezonesID IS NULL OR tblRateTablePKGRate.TimezonesID = p_TimezonesID)
		AND (
				p_effective = 'All'
				OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
				OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate4_ as (select * from tmp_RateTablePKGRate_);
		DELETE n1 FROM tmp_RateTablePKGRate_ n1, tmp_RateTablePKGRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
			AND  n1.RateID = n2.RateID;
	END IF;


    IF p_isExport = 0
    THEN

       	SELECT * FROM tmp_RateTablePKGRate_
			ORDER BY
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteDESC') THEN PackageCostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteASC') THEN PackageCostPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteDESC') THEN RecordingCostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteASC') THEN RecordingCostPerMinute
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTablePKGRate_;

    END IF;

    IF p_isExport = 1
    THEN
        SELECT
        	TimezoneTitle AS `Time of Day`,
			Code AS PackageName,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(PackageCostPerMinuteCurrencySymbol,PackageCostPerMinute) AS PackageCostPerMinute,
			CONCAT(RecordingCostPerMinuteCurrencySymbol,RecordingCostPerMinute) AS RecordingCostPerMinute,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`,
			ApprovedStatus
        FROM
		  		tmp_RateTablePKGRate_;
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
	IN `p_TimezonesID` VARCHAR(50),
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
	DECLARE v_ROUTING_PROFILE_ INT;
	DECLARE v_RateApprovalProcess_ INT;
	DECLARE v_AppliedTo_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET SESSION GROUP_CONCAT_MAX_LEN = 1000000;

	SELECT Value INTO v_ROUTING_PROFILE_ FROM tblCompanyConfiguration WHERE CompanyID=p_companyid AND `Key`='ROUTING_PROFILE';
	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID=p_companyid AND `Key`='RateApprovalProcess';
	SELECT AppliedTo INTO v_AppliedTo_ FROM tblRateTable WHERE CompanyID=p_companyid AND RateTableId=p_RateTableId;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
		ID INT,
		DestinationType VARCHAR(50),
		TimezoneTitle VARCHAR(50),
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		Code VARCHAR(50),
		Description VARCHAR(200),
		MinimumDuration INT,
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
		RateCurrency INT(11),
		ConnectionFeeCurrency INT(11),
		RateCurrencySymbol VARCHAR(255),
		ConnectionFeeCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableRate_RateID (`RateID`)
    );



    INSERT INTO tmp_RateTableRate_
    SELECT
		RateTableRateID AS ID,
		tblRate.Type AS DestinationType,
		tblTimezones.Title AS TimezoneTitle,
		OriginationRate.Code AS OriginationCode,
		OriginationRate.Description AS OriginationDescription,
		tblRate.Code,
		tblRate.Description,
		IFNULL(tblRateTableRate.MinimumDuration,0) AS MinimumDuration,
		IFNULL(tblRateTableRate.Interval1,1) AS Interval1,
		IFNULL(tblRateTableRate.IntervalN,1) AS IntervalN,
		tblRateTableRate.ConnectionFee,
		NULL AS PreviousRate,
		IFNULL(tblRateTableRate.Rate, 0) AS Rate,
		IFNULL(tblRateTableRate.RateN, 0) AS RateN,
		IFNULL(tblRateTableRate.EffectiveDate, NOW()) AS EffectiveDate,
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
		tblRateTableRate.ApprovedDate,
		tblRateCurrency.CurrencyID AS RateCurrency,
		tblConnectionFeeCurrency.CurrencyID AS ConnectionFeeCurrency,
		IFNULL(tblRateCurrency.Symbol,'') AS RateCurrencySymbol,
		IFNULL(tblConnectionFeeCurrency.Symbol,'') AS ConnectionFeeCurrencySymbol,
		tblRateTableRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTableRate
        ON tblRateTableRate.RateID = tblRate.RateID
        AND tblRateTableRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTableRate.TimezonesID
    LEFT JOIN tblCurrency AS tblRateCurrency
        ON tblRateCurrency.CurrencyID = tblRateTableRate.RateCurrency
    LEFT JOIN tblCurrency AS tblConnectionFeeCurrency
        ON tblConnectionFeeCurrency.CurrencyID = tblRateTableRate.ConnectionFeeCurrency
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
		AND (p_TimezonesID IS NULL OR tblRateTableRate.TimezonesID = p_TimezonesID)
		AND (
			p_effective = 'All'
		OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
		OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	  IF p_effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
         DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID;
		END IF;


	UPDATE
		tmp_RateTableRate_ tr
	SET
		PreviousRate = (SELECT Rate FROM tblRateTableRate WHERE RateTableID=p_RateTableId AND TimezonesID = tr.TimezonesID AND RateID=tr.RateID AND OriginationRateID=tr.OriginationRateID AND Code=tr.Code AND EffectiveDate<tr.EffectiveDate ORDER BY EffectiveDate DESC,RateTableRateID DESC LIMIT 1);

	UPDATE
		tmp_RateTableRate_ tr
	SET
		PreviousRate = (SELECT Rate FROM tblRateTableRateArchive WHERE RateTableID=p_RateTableId AND TimezonesID = tr.TimezonesID AND RateID=tr.RateID AND OriginationRateID=tr.OriginationRateID AND Code=tr.Code AND EffectiveDate<tr.EffectiveDate ORDER BY EffectiveDate DESC,RateTableRateID DESC LIMIT 1)
	WHERE
		PreviousRate is null;

    IF p_isExport = 0
    THEN

		IF p_view = 1
		THEN
       	SELECT * FROM tmp_RateTableRate_
					ORDER BY 
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeDESC') THEN DestinationType
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeASC') THEN DestinationType
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationDESC') THEN MinimumDuration
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationASC') THEN MinimumDuration
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
                END ASC,
				    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameDESC') THEN RoutingCategoryName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameASC') THEN RoutingCategoryName
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceDESC') THEN Preference
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceASC') THEN Preference
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTableRate_;

		ELSE
			SELECT group_concat(ID) AS ID, MAX(DestinationType) AS DestinationType,MAX(TimezoneTitle) AS TimezoneTitle,group_concat(OriginationCode) AS OriginationCode,OriginationDescription,group_concat(Code) AS Code,MAX(Description),MinimumDuration,Interval1,IntervalN,ConnectionFee,MAX(PreviousRate),Rate,MAX(RateN),EffectiveDate,MAX(EndDate),MAX(updated_at) AS updated_at,MAX(ModifiedBy) AS ModifiedBy,group_concat(ID) AS RateTableRateID,group_concat(OriginationRateID) AS OriginationRateID,group_concat(RateID) AS RateID, MAX(RoutingCategoryID) AS RoutingCategoryID, MAX(RoutingCategoryName) AS RoutingCategoryName, MAX(Preference) AS Preference, MAX(Blocked) AS Blocked, ApprovedStatus, MAX(ApprovedBy) AS ApprovedBy, MAX(ApprovedDate) AS ApprovedDate, MAX(RateCurrency) AS RateCurrency, MAX(ConnectionFeeCurrency) AS ConnectionFeeCurrency, MAX(RateCurrencySymbol) AS RateCurrencySymbol, MAX(ConnectionFeeCurrencySymbol) AS ConnectionFeeCurrencySymbol,TimezonesID FROM tmp_RateTableRate_
					GROUP BY Description, OriginationDescription, MinimumDuration, Interval1, IntervalN, ConnectionFee, Rate, EffectiveDate, ApprovedStatus, TimezonesID
					ORDER BY
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeDESC') THEN DestinationType
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeASC') THEN DestinationType
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationDESC') THEN MinimumDuration
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationASC') THEN MinimumDuration
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ANY_VALUE(ModifiedBy)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ANY_VALUE(ModifiedBy)
                END ASC,
				    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ANY_VALUE(ConnectionFee)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ANY_VALUE(ConnectionFee)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameDESC') THEN RoutingCategoryName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameASC') THEN RoutingCategoryName
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceDESC') THEN Preference
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceASC') THEN Preference
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT COUNT(*) AS `totalcount`
			FROM (
				SELECT
	            Description
	        	FROM tmp_RateTableRate_
					GROUP BY Description, OriginationDescription, MinimumDuration, Interval1, IntervalN, ConnectionFee, Rate, EffectiveDate, ApprovedStatus, TimezonesID
			) totalcount;


		END IF;

    END IF;


	-- export
	IF p_isExport <> 0
	THEN
		SET @stm1='',@stm2='',@stm3='',@stm4='';

		SET @stm1 = "
			SELECT
        		DestinationType AS `Dest. Type`,
        		TimezoneTitle AS `Time of Day`,
				OriginationCode AS `Orig. Code`,
				OriginationDescription AS `Orig. Description`,
				Code AS `Destination Code`,
				Description AS `Destination Description`,
				MinimumDuration AS `Min. Duration`,
				CONCAT(Interval1,'/',IntervalN) AS `Interval1/N`,
				CONCAT(ConnectionFeeCurrencySymbol,ConnectionFee) AS `Connection Fee`,
				CONCAT(RateCurrencySymbol,Rate) AS Rate,
				CONCAT(RateCurrencySymbol,RateN) AS RateN,
				EffectiveDate AS `Effective Date`
		";

	   IF(v_ROUTING_PROFILE_ = 1)
		THEN
			SET @stm3 = ', RoutingCategoryName AS `Routing Category Name`';
		END IF;

		-- if vendor rate table
		IF(v_AppliedTo_ = 2)
		THEN
		   SET @stm4 = ', Preference, Blocked';
	   END IF;

	   -- advance view
		IF p_isExport = 11
		THEN
	   	SET @stm2 = ", PreviousRate AS `Previous Rate`, CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`";

	   	-- rate approval process is on and rate table is vendor rate table
			IF(v_RateApprovalProcess_ = 1 && v_AppliedTo_ <> 2)
			THEN
	   		SET @stm2 = CONCAT(@stm2,", CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`, ApprovedStatus");
	   	END IF;

	   END IF;

	   SET @stm = CONCAT(@stm1,@stm2,@stm3,@stm4,' FROM tmp_RateTableRate_;');

	   PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableRateAA`(
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
		DestinationType VARCHAR(50),
		TimezoneTitle VARCHAR(50),
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		Code VARCHAR(50),
		Description VARCHAR(200),
		MinimumDuration INT,
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
		RateCurrency INT(11),
		ConnectionFeeCurrency INT(11),
		RateCurrencySymbol VARCHAR(255),
		ConnectionFeeCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableRate_RateID (`RateID`)
	);



	INSERT INTO tmp_RateTableRate_
	SELECT
		RateTableRateAAID AS ID,
		tblRate.Type AS DestinationType,
		tblTimezones.Title AS TimezoneTitle,
		OriginationRate.Code AS OriginationCode,
		OriginationRate.Description AS OriginationDescription,
		tblRate.Code,
		tblRate.Description,
		IFNULL(tblRateTableRate.MinimumDuration,0) AS MinimumDuration,
		IFNULL(tblRateTableRate.Interval1,1) AS Interval1,
		IFNULL(tblRateTableRate.IntervalN,1) AS IntervalN,
		tblRateTableRate.ConnectionFee,
		NULL AS PreviousRate,
		IFNULL(tblRateTableRate.Rate, 0) AS Rate,
		IFNULL(tblRateTableRate.RateN, 0) AS RateN,
		IFNULL(tblRateTableRate.EffectiveDate, NOW()) AS EffectiveDate,
		tblRateTableRate.EndDate,
		tblRateTableRate.updated_at,
		tblRateTableRate.ModifiedBy,
		RateTableRateAAID AS RateTableRateID,
		OriginationRate.RateID AS OriginationRateID,
		tblRate.RateID,
		tblRateTableRate.RoutingCategoryID,
		'' AS RoutingCategoryName,
		tblRateTableRate.Preference,
		tblRateTableRate.Blocked,
		tblRateTableRate.ApprovedStatus,
		tblRateTableRate.ApprovedBy,
		tblRateTableRate.ApprovedDate,
		tblRateCurrency.CurrencyID AS RateCurrency,
		tblConnectionFeeCurrency.CurrencyID AS ConnectionFeeCurrency,
		IFNULL(tblRateCurrency.Symbol,'') AS RateCurrencySymbol,
		IFNULL(tblConnectionFeeCurrency.Symbol,'') AS ConnectionFeeCurrencySymbol,
		tblRateTableRate.TimezonesID
	FROM tblRate
	LEFT JOIN tblRateTableRateAA AS tblRateTableRate
		ON tblRateTableRate.RateID = tblRate.RateID
		AND tblRateTableRate.RateTableId = p_RateTableId
   INNER JOIN tblTimezones
    	ON tblTimezones.TimezonesID = tblRateTableRate.TimezonesID
	LEFT JOIN tblCurrency AS tblRateCurrency
		ON tblRateCurrency.CurrencyID = tblRateTableRate.RateCurrency
	LEFT JOIN tblCurrency AS tblConnectionFeeCurrency
		ON tblConnectionFeeCurrency.CurrencyID = tblRateTableRate.ConnectionFeeCurrency
	LEFT JOIN tblRate AS OriginationRate
		ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
	INNER JOIN tblRateTable
		ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
	WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
		AND (p_origination_code IS NULL OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%'))
		AND (p_origination_description IS NULL OR OriginationRate.Description LIKE REPLACE(p_origination_description, '*', '%'))
		AND (p_code IS NULL OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
		AND (p_ApprovedStatus IS NULL OR tblRateTableRate.ApprovedStatus = p_ApprovedStatus)
		AND TrunkID = p_trunkID
		AND (p_TimezonesID IS NULL OR tblRateTableRate.TimezonesID = p_TimezonesID)
		AND (
			p_effective = 'All'
			OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
			OR (p_effective = 'Future' AND EffectiveDate > NOW())
		);

	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
		DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID;
	END IF;


	IF p_isExport = 0
	THEN

		SELECT * FROM tmp_RateTableRate_
		ORDER BY 
			CASE
	     		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeDESC') THEN DestinationType
	     	END DESC,
	     	CASE
	        	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeASC') THEN DestinationType
	     	END ASC,
			CASE
            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
         END DESC,
         CASE
            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
         END ASC,
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
           	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationDESC') THEN MinimumDuration
       	END DESC,
       	CASE
           	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationASC') THEN MinimumDuration
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
			END ASC,
         CASE
            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
         END DESC,
         CASE
            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
         END ASC,
         CASE
            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameDESC') THEN RoutingCategoryName
         END DESC,
         CASE
            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameASC') THEN RoutingCategoryName
         END ASC,
         CASE
            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceDESC') THEN Preference
         END DESC,
         CASE
            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceASC') THEN Preference
         END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(RateID) AS totalcount
		FROM tmp_RateTableRate_;

	END IF;


	-- export
	IF p_isExport <> 0
	THEN
		SET @stm1='',@stm2='';

		SET @stm1 = "
			SELECT
        		DestinationType AS `Dest. Type`,
        		TimezoneTitle AS `Time of Day`,
				OriginationCode AS `Orig. Code`,
				OriginationDescription AS `Orig. Description`,
				Code AS `Destination Code`,
				Description AS `Destination Description`,
				MinimumDuration AS `Min. Duration`,
				CONCAT(Interval1,'/',IntervalN) AS `Interval1/N`,
				CONCAT(ConnectionFeeCurrencySymbol,ConnectionFee) AS `Connection Fee`,
				CONCAT(RateCurrencySymbol,Rate) AS Rate,
				CONCAT(RateCurrencySymbol,RateN) AS RateN,
				EffectiveDate AS `Effective Date`
		";

		-- advance view
		IF p_isExport = 11
		THEN
			SET @stm2 = ", PreviousRate AS `Previous Rate`, CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`";
			SET @stm2 = CONCAT(@stm2,", CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`, ApprovedStatus");
		END IF;

		SET @stm = CONCAT(@stm1,@stm2,' FROM tmp_RateTableRate_;');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

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
		MinimumDuration INT,
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
		ApprovedStatus tinyint(4),
		ApprovedDate DATETIME,
		ApprovedBy VARCHAR(50),
		RoutingCategoryName VARCHAR(50),
		Preference INT,
		Blocked TINYINT,
		RateCurrency VARCHAR(255),
		ConnectionFeeCurrency VARCHAR(255)
	);

	IF p_View = 1
	THEN
		INSERT INTO tmp_RateTableRate_ (
			OriginationCode,
		  	OriginationDescription,
			Code,
		  	Description,
		  	MinimumDuration,
		  	Interval1,
		  	IntervalN,
		  	ConnectionFee,
		  	Rate,
		  	RateN,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy,
			ApprovedStatus,
			ApprovedDate,
			ApprovedBy,
		  	RoutingCategoryName,
        	Preference,
        	Blocked,
			RateCurrency,
			ConnectionFeeCurrency
		)
	   SELECT
			o_r.Code AS OriginationCode,
			o_r.Description AS OriginationDescription,
			r.Code,
			r.Description,
			CASE WHEN vra.MinimumDuration IS NOT NULL THEN vra.MinimumDuration ELSE r.MinimumDuration END AS MinimumDuration,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
			vra.Rate,
			vra.RateN,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(vra.created_at,'') AS ModifiedDate,
			IFNULL(vra.created_by,'') AS ModifiedBy,
			vra.ApprovedStatus,
			vra.ApprovedDate,
			vra.ApprovedBy,
        	RC.Name AS RoutingCategoryName,
        	vra.Preference,
        	vra.Blocked,
			tblRateCurrency.Symbol AS RateCurrency,
			tblConnectionFeeCurrency.Symbol AS ConnectionFeeCurrency
		FROM
			tblRateTableRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblRate o_r ON o_r.RateID=vra.OriginationRateID
		LEFT JOIN tblCurrency AS tblRateCurrency
			ON tblRateCurrency.CurrencyID = vra.RateCurrency
		LEFT JOIN tblCurrency AS tblConnectionFeeCurrency
			ON tblConnectionFeeCurrency.CurrencyID = vra.ConnectionFeeCurrency
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
		  	MinimumDuration,
		  	Interval1,
		  	IntervalN,
		  	ConnectionFee,
		  	Rate,
		  	RateN,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy,
			ApprovedStatus,
			ApprovedDate,
			ApprovedBy,
			RoutingCategoryName,
        	Preference,
        	Blocked,
			RateCurrency,
			ConnectionFeeCurrency
		)
	   SELECT
			GROUP_CONCAT(DISTINCT o_r.Code) AS OriginationCode,
			MAX(o_r.Description) AS OriginationDescription,
			GROUP_CONCAT(r.Code),
			r.Description,
			CASE WHEN vra.MinimumDuration IS NOT NULL THEN vra.MinimumDuration ELSE r.MinimumDuration END AS MinimumDuration,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
			vra.Rate,
		  	MAX(vra.RateN) AS RateN,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(MAX(vra.created_at),'') AS ModifiedDate,
			IFNULL(MAX(vra.created_by),'') AS ModifiedBy,
			vra.ApprovedStatus,
			MAX(vra.ApprovedDate) AS ApprovedDate,
			MAX(vra.ApprovedBy) AS ApprovedBy,
        	MAX(RC.Name) AS RoutingCategoryName,
        	MAX(vra.Preference) AS Preference,
        	MAX(vra.Blocked) AS Blocked,
			MAX(tblRateCurrency.Symbol) AS RateCurrency,
			MAX(tblConnectionFeeCurrency.Symbol) AS ConnectionFeeCurrency
		FROM
			tblRateTableRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblRate o_r ON o_r.RateID=vra.OriginationRateID
		LEFT JOIN tblCurrency AS tblRateCurrency
			ON tblRateCurrency.CurrencyID = vra.RateCurrency
		LEFT JOIN tblCurrency AS tblConnectionFeeCurrency
			ON tblConnectionFeeCurrency.CurrencyID = vra.ConnectionFeeCurrency
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
			Description, MinimumDuration, Interval1, IntervalN, ConnectionFee, Rate, EffectiveDate, EndDate, ApprovedStatus
		ORDER BY
			vra.EffectiveDate DESC, MAX(vra.created_at) DESC;
	END IF;

	SELECT
		OriginationCode,
	  	OriginationDescription,
		Code,
		Description,
		MinimumDuration,
		Interval1,
		IntervalN,
		CONCAT(IFNULL(ConnectionFeeCurrency,''), ConnectionFee) AS ConnectionFee,
		CONCAT(IFNULL(RateCurrency,''), Rate) AS Rate,
		CONCAT(IFNULL(RateCurrency,''), RateN) AS RateN,
		EffectiveDate,
		EndDate,
		IFNULL(updated_at,'') AS ModifiedDate,
		IFNULL(ModifiedBy,'') AS ModifiedBy,
		ApprovedStatus,
		ApprovedDate,
		ApprovedBy,
		RoutingCategoryName,
		Preference,
		Blocked
	FROM tmp_RateTableRate_;
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
	IN `p_Code` VARCHAR(50),
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
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
		ID INT,
		AccessType varchar(200),
		Country VARCHAR(200),
		OriginationCode VARCHAR(50),
		Code VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		TimezoneTitle VARCHAR(50),
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
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		CostPerCallCurrency INT(11),
		CostPerMinuteCurrency INT(11),
		SurchargePerCallCurrency INT(11),
		SurchargePerMinuteCurrency INT(11),
		OutpaymentPerCallCurrency INT(11),
		OutpaymentPerMinuteCurrency INT(11),
		SurchargesCurrency INT(11),
		ChargebackCurrency INT(11),
		CollectionCostAmountCurrency INT(11),
		RegistrationCostPerNumberCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		CostPerCallCurrencySymbol VARCHAR(255),
		CostPerMinuteCurrencySymbol VARCHAR(255),
		SurchargePerCallCurrencySymbol VARCHAR(255),
		SurchargePerMinuteCurrencySymbol VARCHAR(255),
		OutpaymentPerCallCurrencySymbol VARCHAR(255),
		OutpaymentPerMinuteCurrencySymbol VARCHAR(255),
		SurchargesCurrencySymbol VARCHAR(255),
		ChargebackCurrencySymbol VARCHAR(255),
		CollectionCostAmountCurrencySymbol VARCHAR(255),
		RegistrationCostPerNumberCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableDIDRate_RateID (`Code`)
	);

	INSERT INTO tmp_RateTableDIDRate_
	SELECT
		vra.RateTableDIDRateID,
		vra.AccessType,
		tblCountry.Country,
		OriginationRate.Code AS OriginationCode,
		r.Code,
		vra.City,
		vra.Tariff,
		tblTimezones.Title AS TimezoneTitle,
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
		vra.RateTableDIDRateID,
		vra.OriginationRateID,
		vra.RateID,
		vra.ApprovedStatus,
		vra.ApprovedBy,
		vra.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblCostPerCallCurrency.CurrencyID AS CostPerCallCurrency,
		tblCostPerMinuteCurrency.CurrencyID AS CostPerMinuteCurrency,
		tblSurchargePerCallCurrency.CurrencyID AS SurchargePerCallCurrency,
		tblSurchargePerMinuteCurrency.CurrencyID AS SurchargePerMinuteCurrency,
		tblOutpaymentPerCallCurrency.CurrencyID AS OutpaymentPerCallCurrency,
		tblOutpaymentPerMinuteCurrency.CurrencyID AS OutpaymentPerMinuteCurrency,
		tblSurchargesCurrency.CurrencyID AS SurchargesCurrency,
		tblChargebackCurrency.CurrencyID AS ChargebackCurrency,
		tblCollectionCostAmountCurrency.CurrencyID AS CollectionCostAmountCurrency,
		tblRegistrationCostPerNumberCurrency.CurrencyID AS RegistrationCostPerNumberCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblCostPerCallCurrency.Symbol,'') AS CostPerCallCurrencySymbol,
		IFNULL(tblCostPerMinuteCurrency.Symbol,'') AS CostPerMinuteCurrencySymbol,
		IFNULL(tblSurchargePerCallCurrency.Symbol,'') AS SurchargePerCallCurrencySymbol,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol,'') AS SurchargePerMinuteCurrencySymbol,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol,'') AS OutpaymentPerCallCurrencySymbol,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,'') AS OutpaymentPerMinuteCurrencySymbol,
		IFNULL(tblSurchargesCurrency.Symbol,'') AS SurchargesCurrencySymbol,
		IFNULL(tblChargebackCurrency.Symbol,'') AS ChargebackCurrencySymbol,
		IFNULL(tblCollectionCostAmountCurrency.Symbol,'') AS CollectionCostAmountCurrencySymbol,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,'') AS RegistrationCostPerNumberCurrencySymbol,
		vra.TimezonesID
	FROM
		tblRateTableDIDRateArchive vra
   INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = vra.TimezonesID
	JOIN
		tblRate r ON r.RateID=vra.RateId
	LEFT JOIN
		tblCountry ON tblCountry.CountryID = r.CountryID
   LEFT JOIN
		tblRate AS OriginationRate ON OriginationRate.RateID = vra.OriginationRateID
	LEFT JOIN
		tblRateTableDIDRate vr ON vr.RateTableId = vra.RateTableId AND vr.RateId = vra.RateId AND vr.OriginationRateID = vra.OriginationRateID AND vr.TimezonesID = vra.TimezonesID
	LEFT JOIN tblCurrency AS tblOneOffCostCurrency
		ON tblOneOffCostCurrency.CurrencyID = vra.OneOffCostCurrency
	LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
		ON tblMonthlyCostCurrency.CurrencyID = vra.MonthlyCostCurrency
	LEFT JOIN tblCurrency AS tblCostPerCallCurrency
		ON tblCostPerCallCurrency.CurrencyID = vra.CostPerCallCurrency
	LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
		ON tblCostPerMinuteCurrency.CurrencyID = vra.CostPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
		ON tblSurchargePerCallCurrency.CurrencyID = vra.SurchargePerCallCurrency
	LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
		ON tblSurchargePerMinuteCurrency.CurrencyID = vra.SurchargePerMinuteCurrency
	LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
		ON tblOutpaymentPerCallCurrency.CurrencyID = vra.OutpaymentPerCallCurrency
	LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
		ON tblOutpaymentPerMinuteCurrency.CurrencyID = vra.OutpaymentPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblSurchargesCurrency
		ON tblSurchargesCurrency.CurrencyID = vra.SurchargesCurrency
	LEFT JOIN tblCurrency AS tblChargebackCurrency
		ON tblChargebackCurrency.CurrencyID = vra.ChargebackCurrency
	LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
		ON tblCollectionCostAmountCurrency.CurrencyID = vra.CollectionCostAmountCurrency
	LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
		ON tblRegistrationCostPerNumberCurrency.CurrencyID = vra.RegistrationCostPerNumberCurrency
	INNER JOIN tblRateTable
		ON tblRateTable.RateTableId = vra.RateTableId
	LEFT JOIN tblCurrency AS tblRateTableCurrency
		ON tblRateTableCurrency.CurrencyId = tblRateTable.CurrencyID
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		(p_TimezonesID IS NULL OR vra.TimezonesID = p_TimezonesID) AND
		(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
		(p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%')) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_City IS NULL OR vra.City LIKE REPLACE(p_City, '*', '%')) AND
		(p_Tariff IS NULL OR vra.Tariff LIKE REPLACE(p_Tariff, '*', '%')) AND
		(p_AccessType IS NULL OR vra.AccessType LIKE REPLACE(p_AccessType, '*', '%')) AND
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
			n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID AND n1.City = n2.City AND n1.Tariff = n2.Tariff AND n1.RateTableDIDRateID < n2.RateTableDIDRateID;

		SELECT * FROM tmp_RateTableDIDRate_
		ORDER BY
				 CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
             END ASC,
				 CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
             END ASC,
				 CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
             END ASC,
             CASE
					  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityDESC') THEN City
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityASC') THEN City
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffDESC') THEN Tariff
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffASC') THEN Tariff
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeDESC') THEN AccessType
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeASC') THEN AccessType
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
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN updated_by
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN updated_by
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
             END ASC
		LIMIT
			p_RowspPage
		OFFSET
			v_OffSet_;

		SELECT
			COUNT(code) AS totalcount
		FROM tmp_RateTableDIDRate_;

	END IF;

	-- basic view
	IF p_isExport = 10
	THEN
		SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			ApprovedStatus
		FROM tmp_RateTableDIDRate_;
	END IF;

	-- advance view
	IF p_isExport = 11
	THEN
		SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			CONCAT(updated_at,'\n',updated_by) AS `Modified Date/By`,
			CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`,
			ApprovedStatus
		FROM tmp_RateTableDIDRate_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getDiscontinuedRateTablePKGRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedRateTablePKGRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_Code` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_RateTablePKGRate_ (
		ID INT,
		TimezoneTitle VARCHAR(50),
		Code VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		PackageCostPerMinute DECIMAL(18,6),
		RecordingCostPerMinute DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		ModifiedBy VARCHAR(50),
		RateTablePKGRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		PackageCostPerMinuteCurrency INT(11),
		RecordingCostPerMinuteCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		PackageCostPerMinuteCurrencySymbol VARCHAR(255),
		RecordingCostPerMinuteCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTablePKGRate_RateID (`Code`)
	);

	INSERT INTO tmp_RateTablePKGRate_
	SELECT
		vra.RateTablePKGRateID,
		tblTimezones.Title AS TimezoneTitle,
		r.Code,
		vra.OneOffCost,
		vra.MonthlyCost,
		vra.PackageCostPerMinute,
		vra.RecordingCostPerMinute,
		vra.EffectiveDate,
		vra.EndDate,
		vra.created_at AS updated_at,
		vra.CreatedBy AS ModifiedBy,
		vra.RateTablePKGRateID,
		vra.RateID,
		vra.ApprovedStatus,
		vra.ApprovedBy,
		vra.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblPackageCostPerMinuteCurrency.CurrencyID AS PackageCostPerMinuteCurrency,
		tblRecordingCostPerMinuteCurrency.CurrencyID AS RecordingCostPerMinuteCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblPackageCostPerMinuteCurrency.Symbol,'') AS PackageCostPerMinuteCurrencySymbol,
		IFNULL(tblRecordingCostPerMinuteCurrency.Symbol,'') AS RecordingCostPerMinuteCurrencySymbol,
		vra.TimezonesID
	FROM
		tblRateTablePKGRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
   INNER JOIN tblTimezones
    	ON tblTimezones.TimezonesID = vra.TimezonesID
	LEFT JOIN
		tblRateTablePKGRate vr ON vr.RateTableId = vra.RateTableId AND vr.RateId = vra.RateId AND vr.TimezonesID = vra.TimezonesID
	LEFT JOIN tblCurrency AS tblOneOffCostCurrency
		ON tblOneOffCostCurrency.CurrencyID = vra.OneOffCostCurrency
	LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
		ON tblMonthlyCostCurrency.CurrencyID = vra.MonthlyCostCurrency
	LEFT JOIN tblCurrency AS tblPackageCostPerMinuteCurrency
		ON tblPackageCostPerMinuteCurrency.CurrencyID = vra.PackageCostPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblRecordingCostPerMinuteCurrency
		ON tblRecordingCostPerMinuteCurrency.CurrencyID = vra.RecordingCostPerMinuteCurrency
	INNER JOIN tblRateTable
		ON tblRateTable.RateTableId = vra.RateTableId
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		(p_TimezonesID IS NULL OR vra.TimezonesID = p_TimezonesID) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_ApprovedStatus IS NULL OR vra.ApprovedStatus = p_ApprovedStatus) AND
		vr.RateTablePKGRateID IS NULL;

	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate2_ as (select * from tmp_RateTablePKGRate_);
		DELETE
			n1
		FROM
			tmp_RateTablePKGRate_ n1, tmp_RateTablePKGRate2_ n2
		WHERE
			n1.RateID = n2.RateID AND n1.TimezonesID = n2.TimezonesID AND n1.RateTablePKGRateID < n2.RateTablePKGRateID;

		SELECT * FROM tmp_RateTablePKGRate_
		ORDER BY
			CASE
	        	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
	     	END DESC,
	     	CASE
	        	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
	     	END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteDESC') THEN PackageCostPerMinute
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteASC') THEN PackageCostPerMinute
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteDESC') THEN RecordingCostPerMinute
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteASC') THEN RecordingCostPerMinute
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
           	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
       	END DESC,
       	CASE
           	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
       	END ASC,
       	CASE
           	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
       	END DESC,
       	CASE
           	WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
       	END ASC
		LIMIT
			p_RowspPage
		OFFSET
			v_OffSet_;

		SELECT
			COUNT(code) AS totalcount
		FROM tmp_RateTablePKGRate_;

	END IF;

	IF p_isExport = 1
	THEN
		SELECT
        	TimezoneTitle AS `Time of Day`,
			Code AS DestinationCode,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(PackageCostPerMinuteCurrencySymbol,PackageCostPerMinute) AS PackageCostPerMinute,
			CONCAT(RecordingCostPerMinuteCurrencySymbol,RecordingCostPerMinute) AS RecordingCostPerMinute,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`,
			ApprovedStatus
		FROM tmp_RateTablePKGRate_;
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
	DECLARE v_ROUTING_PROFILE_ INT;
	DECLARE v_RateApprovalProcess_ INT;
	DECLARE v_AppliedTo_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Value INTO v_ROUTING_PROFILE_ FROM tblCompanyConfiguration WHERE CompanyID=p_companyid AND `Key`='ROUTING_PROFILE';
	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID=p_companyid AND `Key`='RateApprovalProcess';
	SELECT AppliedTo INTO v_AppliedTo_ FROM tblRateTable WHERE CompanyID=p_companyid AND RateTableId=p_RateTableId;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_RateTableRate_ (
		RateTableRateID INT,
		DestinationType VARCHAR(50),
		TimezoneTitle VARCHAR(50),
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		Code VARCHAR(50),
		Description VARCHAR(200),
		MinimumDuration INT,
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
     	ApprovedDate DATE,
		RateCurrency INT(11),
		ConnectionFeeCurrency INT(11),
		RateCurrencySymbol VARCHAR(255),
		ConnectionFeeCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableRate_RateID (`Code`)
	);

	INSERT INTO tmp_RateTableRate_
	SELECT
		vra.RateTableRateID,
		r.Type AS DestinationType,
		tblTimezones.Title AS TimezoneTitle,
		OriginationRate.Code AS OriginationCode,
		OriginationRate.Description AS OriginationDescription,
		r.Code,
		r.Description,
		CASE WHEN vra.MinimumDuration IS NOT NULL THEN vra.MinimumDuration ELSE r.MinimumDuration END AS MinimumDuration,
		CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
		'' AS ConnectionFee,
		NULL AS PreviousRate,
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
		vra.ApprovedDate,
		tblRateCurrency.CurrencyID AS RateCurrency,
		tblConnectionFeeCurrency.CurrencyID AS ConnectionFeeCurrency,
		IFNULL(tblRateCurrency.Symbol,'') AS RateCurrencySymbol,
		IFNULL(tblConnectionFeeCurrency.Symbol,'') AS ConnectionFeeCurrencySymbol,
		vra.TimezonesID
	FROM
		tblRateTableRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
   INNER JOIN tblTimezones
    	ON tblTimezones.TimezonesID = vra.TimezonesID
	LEFT JOIN
		tblRate AS OriginationRate ON OriginationRate.RateID = vra.OriginationRateID
   LEFT JOIN tblCurrency AS tblRateCurrency
      ON tblRateCurrency.CurrencyID = vra.RateCurrency
   LEFT JOIN tblCurrency AS tblConnectionFeeCurrency
      ON tblConnectionFeeCurrency.CurrencyID = vra.ConnectionFeeCurrency
	LEFT JOIN
		tblRateTableRate vr ON vr.RateTableId = vra.RateTableId AND vr.RateId = vra.RateId AND vr.OriginationRateID = vra.OriginationRateID AND vr.TimezonesID = vra.TimezonesID
	LEFT JOIN speakintelligentRouting.tblRoutingCategory AS RC
    	ON RC.RoutingCategoryID = vra.RoutingCategoryID
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		(p_TimezonesID IS NULL OR 	vra.TimezonesID = p_TimezonesID)AND
		(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
		(p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%')) AND
		(p_origination_description is null OR OriginationRate.Description LIKE REPLACE(p_origination_description, '*', '%')) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
		(p_RoutingCategoryID IS NULL OR RC.RoutingCategoryID = p_RoutingCategoryID ) AND
		(p_Preference IS NULL OR vra.Preference = p_Preference) AND
		(p_Blocked IS NULL OR vra.Blocked = p_Blocked) AND
		-- (p_ApprovedStatus IS NULL OR vra.ApprovedStatus = p_ApprovedStatus) AND
		vr.RateTableRateID is NULL;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_ as (select * from tmp_RateTableRate_);
	DELETE
		n1
	FROM
		tmp_RateTableRate_ n1, tmp_RateTableRate2_ n2
	WHERE
		n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID AND n1.RateTableRateID < n2.RateTableRateID;

	IF p_isExport = 0
	THEN
		IF p_view = 1
		THEN
			SELECT
				RateTableRateID AS ID,
				DestinationType,
        		TimezoneTitle,
				OriginationCode,
				OriginationDescription,
				Code,
				Description,
				MinimumDuration,
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
				ApprovedDate,
				RateCurrency,
				ConnectionFeeCurrency,
				RateCurrencySymbol,
				ConnectionFeeCurrencySymbol,
				TimezonesID
			FROM
				tmp_RateTableRate_
			ORDER BY
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeDESC') THEN DestinationType
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeASC') THEN DestinationType
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
            END ASC,
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
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationDESC') THEN MinimumDuration
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationASC') THEN MinimumDuration
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
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN updated_by
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN updated_by
				END ASC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
            END ASC,
	         CASE
	            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameDESC') THEN RoutingCategoryName
	         END DESC,
	         CASE
	            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameASC') THEN RoutingCategoryName
	         END ASC,
	         CASE
	            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceDESC') THEN Preference
	         END DESC,
	         CASE
	            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceASC') THEN Preference
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
				MAX(DestinationType) AS DestinationType,
				MAX(TimezoneTitle) AS TimezoneTitle,
				group_concat(OriginationCode) AS OriginationCode,
				OriginationDescription,
				group_concat(Code) AS Code,
				Description,
				ConnectionFee,
				MinimumDuration,
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
				MAX(ApprovedDate) AS ApprovedDate,
				MAX(RateCurrency) AS RateCurrency,
				MAX(ConnectionFeeCurrency) AS ConnectionFeeCurrency,
				MAX(RateCurrencySymbol) AS RateCurrencySymbol,
				MAX(ConnectionFeeCurrencySymbol) AS ConnectionFeeCurrencySymbol,
				TimezonesID
			FROM
				tmp_RateTableRate_
			GROUP BY
				Description, OriginationDescription, MinimumDuration, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate, ApprovedStatus, TimezonesID
			ORDER BY
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeDESC') THEN DestinationType
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeASC') THEN DestinationType
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
            END ASC,
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
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationDESC') THEN MinimumDuration
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MinimumDurationASC') THEN MinimumDuration
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
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ANY_VALUE(updated_by)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ANY_VALUE(updated_by)
				END ASC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ANY_VALUE(ApprovedBy)
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ANY_VALUE(ApprovedBy)
            END ASC,
	         CASE
	            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameDESC') THEN ANY_VALUE(RoutingCategoryName)
	         END DESC,
	         CASE
	            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RoutingCategoryNameASC') THEN ANY_VALUE(RoutingCategoryName)
	         END ASC,
	         CASE
	            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceDESC') THEN ANY_VALUE(Preference)
	         END DESC,
	         CASE
	            WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreferenceASC') THEN ANY_VALUE(Preference)
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
					GROUP BY Description, OriginationDescription, MinimumDuration, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate, ApprovedStatus, TimezonesID
			) totalcount;

		END IF;

	END IF;


	-- export
	IF p_isExport <> 0
	THEN
		SET @stm1='',@stm2='',@stm3='',@stm4='';

		SET @stm1 = "
			SELECT
        		DestinationType AS `Dest. Type`,
        		TimezoneTitle AS `Time of Day`,
				OriginationCode AS `Orig. Code`,
				OriginationDescription AS `Orig. Description`,
				Code AS `Destination Code`,
				Description AS `Destination Description`,
				MinimumDuration AS `Min. Duration`,
				CONCAT(Interval1,'/',IntervalN) AS `Interval1/N`,
				CONCAT(ConnectionFeeCurrencySymbol,ConnectionFee) AS `Connection Fee`,
				CONCAT(RateCurrencySymbol,Rate) AS Rate,
				CONCAT(RateCurrencySymbol,RateN) AS RateN,
				EffectiveDate AS `Effective Date`
		";

	   IF(v_ROUTING_PROFILE_ = 1)
		THEN
			SET @stm3 = ', RoutingCategoryName AS `Routing Category Name`';
		END IF;

		-- if vendor rate table
		IF(v_AppliedTo_ = 2)
		THEN
		   SET @stm4 = ', Preference, Blocked';
	   END IF;

	   -- advance view
		IF p_isExport = 11
	   THEN
	   	SET @stm2 = ", PreviousRate AS `Previous Rate`, CONCAT(updated_by,'\n',updated_at) AS `Modified By/Date`";

	   	-- rate approval process is on and rate table is vendor rate table
			IF(v_RateApprovalProcess_ = 1 && v_AppliedTo_ <> 2)
			THEN
	   		SET @stm2 = CONCAT(@stm2,", CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`, ApprovedStatus AS `Approved Status`");
	   	END IF;

	   END IF;

	   SET @stm = CONCAT(@stm1,@stm2,@stm3,@stm4,' FROM tmp_RateTableRate_;');

	   PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	-- basic view
   /*IF p_isExport = 10
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
   END IF;*/

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableRateAAUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableRateAAUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTableRateAAId` LONGTEXT,
	IN `p_OriginationRateID` INT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Rate` DECIMAL(18,6),
	IN `p_RateN` VARCHAR(255),
	IN `p_MinimumDuration` INT,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_ConnectionFee` VARCHAR(255),
	IN `p_RateCurrency` DECIMAL(18,6),
	IN `p_ConnectionFeeCurrency` DECIMAL(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` VARCHAR(50),
	IN `p_Critearea_Description` VARCHAR(200),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_OriginationDescription` VARCHAR(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		`RateTableRateAAId` int(11) NOT NULL,
		`OriginationRateID` int(11) NOT NULL DEFAULT '0',
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
		`MinimumDuration` int(11) DEFAULT NULL,
		`Interval1` int(11) DEFAULT NULL,
		`IntervalN` int(11) DEFAULT NULL,
		`ConnectionFee` decimal(18,6) DEFAULT NULL,
		`RoutingCategoryID` int(11) DEFAULT NULL,
		`Preference` int(11) DEFAULT NULL,
		`Blocked` tinyint NOT NULL DEFAULT 0,
		`ApprovedStatus` tinyint,
		`ApprovedBy` varchar(50),
		`ApprovedDate` datetime,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL
	);

	INSERT INTO tmp_TempRateTableRate_
	SELECT
		rtr.RateTableRateAAId,
		IF(rtr.OriginationRateID=0,IFNULL(p_OriginationRateID,rtr.OriginationRateID),rtr.OriginationRateID) AS OriginationRateID,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IF(p_Rate=0,0,IFNULL(p_Rate,rtr.Rate)) AS Rate,
		IF(p_RateN IS NOT NULL,IF(p_RateN='NULL',NULL,p_RateN),rtr.RateN) AS RateN,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		IFNULL(p_MinimumDuration,rtr.MinimumDuration) AS MinimumDuration,
		IFNULL(p_Interval1,rtr.Interval1) AS Interval1,
		IFNULL(p_IntervalN,rtr.IntervalN) AS IntervalN,
		IF(p_ConnectionFee IS NOT NULL,IF(p_ConnectionFee='NULL',NULL,p_ConnectionFee),rtr.ConnectionFee) AS ConnectionFee,
		rtr.RoutingCategoryID AS RoutingCategoryID,
		rtr.Preference AS Preference,
		rtr.Blocked AS Blocked,
		rtr.ApprovedStatus,
		NULL AS ApprovedBy,
		NULL AS ApprovedDate,
		IFNULL(p_RateCurrency,rtr.RateCurrency) AS RateCurrency,
		IFNULL(p_ConnectionFeeCurrency,rtr.ConnectionFeeCurrency) AS ConnectionFeeCurrency
	FROM
		tblRateTableRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.OriginationRateID,rtr.TimezonesID) NOT IN (
						SELECT
							RateID,OriginationRateID,TimezonesID
						FROM
							tblRateTableRateAA
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTableRateAAID,p_RateTableRateAAID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableRateAAID,p_RateTableRateAAID) != 0 )) OR
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
		(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID);
	
	IF p_action = 1
	THEN
		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate_2 as (select * from tmp_TempRateTableRate_);
			DELETE n1 FROM tmp_TempRateTableRate_ n1, tmp_TempRateTableRate_2 n2 WHERE n1.RateTableRateAAID < n2.RateTableRateAAID AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID;
		END IF;

		-- remove rejected rates from temp table while updating so, it can't be update and delete
		DELETE n1 FROM tmp_TempRateTableRate_ n1 WHERE ApprovedStatus = 2;

		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
		DELETE
			temp
		FROM
			tmp_TempRateTableRate_ temp
		JOIN
			tblRateTableRateAA rtr ON rtr.RateTableRateAAID = temp.RateTableRateAAID
		WHERE
			(rtr.OriginationRateID = temp.OriginationRateID) AND
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			(rtr.TimezonesID = temp.TimezonesID) AND
			((rtr.Rate IS NULL && temp.Rate IS NULL) || rtr.Rate = temp.Rate) AND
			((rtr.RateN IS NULL && temp.RateN IS NULL) || rtr.RateN = temp.RateN) AND
			((rtr.ConnectionFee IS NULL && temp.ConnectionFee IS NULL) || rtr.ConnectionFee = temp.ConnectionFee) AND
			((rtr.MinimumDuration IS NULL && temp.MinimumDuration IS NULL) || rtr.MinimumDuration = temp.MinimumDuration) AND
			((rtr.Interval1 IS NULL && temp.Interval1 IS NULL) || rtr.Interval1 = temp.Interval1) AND
			((rtr.IntervalN IS NULL && temp.IntervalN IS NULL) || rtr.IntervalN = temp.IntervalN) AND
			((rtr.RoutingCategoryID IS NULL && temp.RoutingCategoryID IS NULL) || rtr.RoutingCategoryID = temp.RoutingCategoryID) AND
			((rtr.Preference IS NULL && temp.Preference IS NULL) || rtr.Preference = temp.Preference) AND
			((rtr.Blocked IS NULL && temp.Blocked IS NULL) || rtr.Blocked = temp.Blocked) AND
			((rtr.RateCurrency IS NULL && temp.RateCurrency IS NULL) || rtr.RateCurrency = temp.RateCurrency) AND
			((rtr.ConnectionFeeCurrency IS NULL && temp.ConnectionFeeCurrency IS NULL) || rtr.ConnectionFeeCurrency = temp.ConnectionFeeCurrency);

	END IF;




	UPDATE
		tblRateTableRateAA rtr
	INNER JOIN
		tmp_TempRateTableRate_ temp ON temp.RateTableRateAAID = rtr.RateTableRateAAID
	SET
		rtr.EndDate = NOW()
	WHERE
		temp.RateTableRateAAID = rtr.RateTableRateAAID;

	CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN

		INSERT INTO tblRateTableRateAA (
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
			MinimumDuration,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			RateCurrency,
			ConnectionFeeCurrency
		)
		SELECT
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
			MinimumDuration,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			RateCurrency,
			ConnectionFeeCurrency
		FROM
			tmp_TempRateTableRate_
		WHERE
			ApprovedStatus = 0; -- only allow awaiting approval rates to be updated

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableRateApprove`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableRateApprove`(
	IN `p_RateTableId` INT,
	IN `p_RateTableRateAAID` LONGTEXT,
	IN `p_ApprovedStatus` TINYINT,
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

	DECLARE v_StatusAwaitingApproval_ INT(11) DEFAULT 0;
	DECLARE v_StatusApproved_ INT(11) DEFAULT 1;
	DECLARE v_StatusRejected_ INT(11) DEFAULT 2;
	DECLARE v_StatusDelete_ INT(11) DEFAULT 3;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_RateTableRate_ (
		`RateTableRateAAID` INT,
		`OriginationRateID` BIGINT(20),
		`RateID` INT(11),
		`RateTableId` BIGINT(20),
		`TimezonesID` INT(11),
		`Rate` DECIMAL(18,6),
		`RateN` DECIMAL(18,6),
		`EffectiveDate` DATE,
		`EndDate` DATE,
		`created_at` DATETIME,
		`updated_at` DATETIME,
		`CreatedBy` VARCHAR(100),
		`ModifiedBy` VARCHAR(50),
		`PreviousRate` DECIMAL(18,6),
		`MinimumDuration` INT(11),
		`Interval1` INT(11),
		`IntervalN` INT(11),
		`ConnectionFee` DECIMAL(18,6),
		`RoutingCategoryID` INT(11),
		`Preference` INT(11),
		`Blocked` TINYINT(4),
		`ApprovedStatus` TINYINT(4),
		`ApprovedBy` VARCHAR(50),
		`ApprovedDate` DATETIME,
		`RateCurrency` INT(11),
		`ConnectionFeeCurrency` INT(11),
		`VendorID` INT(11),
		`RateTableRateID` INT(11),
		INDEX tmp_RateTableRate_RateID (`RateID`,`OriginationRateID`,`TimezonesID`,`EffectiveDate`)
	);
	
	INSERT INTO	tmp_RateTableRate_
	SELECT
		rtr.RateTableRateAAID,
		rtr.OriginationRateID,
		rtr.RateID,
		rtr.RateTableId,
		rtr.TimezonesID,
		rtr.Rate,
		rtr.RateN,
		IF(rtr.EffectiveDate < CURDATE(), CURDATE(), rtr.EffectiveDate) AS EffectiveDate,
		rtr.EndDate,
		rtr.created_at,
		rtr.updated_at,
		rtr.CreatedBy,
		rtr.ModifiedBy,
		rtr.PreviousRate,
		rtr.MinimumDuration,
		rtr.Interval1,
		rtr.IntervalN,
		rtr.ConnectionFee,
		rtr.RoutingCategoryID,
		rtr.Preference,
		rtr.Blocked,
		rtr.ApprovedStatus AS ApprovedStatus,
		p_ApprovedBy AS ApprovedBy,
		NOW() AS ApprovedDate,
		rtr.RateCurrency,
		rtr.ConnectionFeeCurrency,
		rtr.VendorID,
		rtr.RateTableRateID
	FROM
		tblRateTableRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	WHERE
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableRateAAID,p_RateTableRateAAID) != 0 )) OR
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
		(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID) AND
		rtr.ApprovedStatus IN (v_StatusAwaitingApproval_,v_StatusDelete_); -- only awaitng approval and awaitng approval delete rates



	IF p_ApprovedStatus = v_StatusApproved_ -- approve rates
	THEN
		
		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate2_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_ AS (SELECT * FROM tmp_RateTableRate_);
		
		-- delete all duplicate records, keep only one - only last aa rate will be approved and all other will be ignored
		DELETE temp2
		FROM
			tmp_RateTableRate2_ temp2
		INNER JOIN
			tmp_RateTableRate_ temp1 ON temp1.OriginationRateID = temp2.OriginationRateID
			AND temp1.RateID = temp2.RateID
			AND temp1.RateTableId = temp2.RateTableId
			AND temp1.TimezonesID = temp2.TimezonesID
			AND (
					temp1.EffectiveDate = temp2.EffectiveDate OR 
					(temp1.EffectiveDate <= NOW() AND temp2.EffectiveDate <= NOW())
				)
		WHERE
			temp2.RateTableRateAAID < temp1.RateTableRateAAID;

		-- set EndDate to archive rates which needs to approve and exist with same effective date
		UPDATE
			tblRateTableRate rtr
		INNER JOIN
			tmp_RateTableRate2_ temp ON temp.RateId = rtr.RateId AND temp.OriginationRateID = rtr.OriginationRateID AND temp.TimezonesID = rtr.TimezonesID AND temp.EffectiveDate = rtr.EffectiveDate
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusAwaitingApproval_;

		-- set EndDate to archive rates which needs to approve and exist with old effective date new rate is <=now() effective date
		UPDATE
			tblRateTableRate rtr
		INNER JOIN
			tmp_RateTableRate2_ temp ON temp.RateId = rtr.RateId AND 
			temp.OriginationRateID = rtr.OriginationRateID AND 
			temp.TimezonesID = rtr.TimezonesID AND 
			(temp.EffectiveDate <= NOW() AND rtr.EffectiveDate <= temp.EffectiveDate)
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusAwaitingApproval_;
		
		-- set EndDate to archive rates which rate's status is - awaiting approval delete
		UPDATE
			tblRateTableRate rtr
		INNER JOIN
			tmp_RateTableRate2_ temp ON temp.RateTableRateID = rtr.RateTableRateID
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusDelete_;
				
		--	archive rates
		CALL prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_ApprovedBy);

		-- insert approved rates to tblRateTableRate
		INSERT INTO	tblRateTableRate
		(
			OriginationRateID,
			RateID,
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
			PreviousRate,
			MinimumDuration,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			RateCurrency,
			ConnectionFeeCurrency,
			VendorID
		)
		SELECT 
			OriginationRateID,
			RateID,
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
			PreviousRate,
			MinimumDuration,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			v_StatusApproved_ AS ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			RateCurrency,
			ConnectionFeeCurrency,
			VendorID
		FROM
			tmp_RateTableRate2_
		WHERE
			ApprovedStatus = v_StatusAwaitingApproval_;
		
		-- delete from Awaiting Approval table after inserting into tblRateTableRate
		DELETE AA
		FROM
			tblRateTableRateAA AS AA
		INNER JOIN
			tmp_RateTableRate_ AS temp ON temp.RateTableRateAAID = AA.RateTableRateAAID;
			
		
		CALL prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');
		CALL prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_ApprovedBy);
			
	ELSE -- reject/disapprove rates
		
		UPDATE
			tblRateTableRateAA rtr
		INNER JOIN
			tmp_RateTableRate_ temp ON temp.RateTableRateAAID = rtr.RateTableRateAAID		
		SET
			rtr.ApprovedStatus = p_ApprovedStatus, rtr.ApprovedBy = temp.ApprovedBy, rtr.ApprovedDate = temp.ApprovedDate;

	END IF;


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
	IN `p_RateN` VARCHAR(255),
	IN `p_MinimumDuration` INT,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_ConnectionFee` VARCHAR(255),
	IN `p_RoutingCategoryID` INT,
	IN `p_Preference` TEXT,
	IN `p_Blocked` TINYINT,
	IN `p_RateCurrency` DECIMAL(18,6),
	IN `p_ConnectionFeeCurrency` DECIMAL(18,6),
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

	DECLARE v_RateApprovalProcess_ INT;
	DECLARE v_RateTableAppliedTo_ INT;

	DECLARE v_StatusAwaitingApproval_ INT(11) DEFAULT 0;
	DECLARE v_StatusApproved_ INT(11) DEFAULT 1;
	DECLARE v_StatusRejected_ INT(11) DEFAULT 2;
	DECLARE v_StatusDelete_ INT(11) DEFAULT 3;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = (SELECT CompanyId FROM tblRateTable WHERE RateTableID = p_RateTableId) AND `Key`='RateApprovalProcess';
	SELECT AppliedTo INTO v_RateTableAppliedTo_ FROM tblRateTable WHERE RateTableID = p_RateTableId;


	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		`RateTableRateId` int(11) NOT NULL,
		`OriginationRateID` int(11) NOT NULL DEFAULT '0',
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
		`MinimumDuration` int(11) DEFAULT NULL,
		`Interval1` int(11) DEFAULT NULL,
		`IntervalN` int(11) DEFAULT NULL,
		`ConnectionFee` decimal(18,6) DEFAULT NULL,
		`RoutingCategoryID` int(11) DEFAULT NULL,
		`Preference` int(11) DEFAULT NULL,
		`Blocked` tinyint NOT NULL DEFAULT 0,
		`ApprovedStatus` tinyint,
		`ApprovedBy` varchar(50),
		`ApprovedDate` datetime,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL
	);

	INSERT INTO tmp_TempRateTableRate_
	SELECT
		rtr.RateTableRateId,
		IF(rtr.OriginationRateID=0,IFNULL(p_OriginationRateID,rtr.OriginationRateID),rtr.OriginationRateID) AS OriginationRateID,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IF(p_Rate=0,0,IFNULL(p_Rate,rtr.Rate)) AS Rate,
		IF(p_RateN IS NOT NULL,IF(p_RateN='NULL',NULL,p_RateN),rtr.RateN) AS RateN,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		IFNULL(p_MinimumDuration,rtr.MinimumDuration) AS MinimumDuration,
		IFNULL(p_Interval1,rtr.Interval1) AS Interval1,
		IFNULL(p_IntervalN,rtr.IntervalN) AS IntervalN,
		IF(p_ConnectionFee IS NOT NULL,IF(p_ConnectionFee='NULL',NULL,p_ConnectionFee),rtr.ConnectionFee) AS ConnectionFee,
		IF(p_RoutingCategoryID='',NULL,IFNULL(p_RoutingCategoryID,rtr.RoutingCategoryID)) AS RoutingCategoryID,
		IF(p_Preference='',NULL,IFNULL(p_Preference,rtr.Preference)) AS Preference,
		IFNULL(p_Blocked,rtr.Blocked) AS Blocked,
		rtr.ApprovedStatus AS ApprovedStatus,
		rtr.ApprovedBy,
		rtr.ApprovedDate,
		IFNULL(p_RateCurrency,rtr.RateCurrency) AS RateCurrency,
		IFNULL(p_ConnectionFeeCurrency,rtr.ConnectionFeeCurrency) AS ConnectionFeeCurrency
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
				(rtr.RateID,rtr.OriginationRateID,rtr.TimezonesID) NOT IN (
						SELECT
							RateID,OriginationRateID,TimezonesID
						FROM
							tblRateTableRate
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND
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
		(p_TimezonesID IS NULL OR 	rtr.TimezonesID = p_TimezonesID);
	
	IF p_action = 1 -- update
	THEN
		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate_2 as (select * from tmp_TempRateTableRate_);
	      DELETE n1 FROM tmp_TempRateTableRate_ n1, tmp_TempRateTableRate_2 n2 WHERE n1.RateTableRateID < n2.RateTableRateID AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID;
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
			(rtr.TimezonesID = temp.TimezonesID) AND
			((rtr.Rate IS NULL && temp.Rate IS NULL) || rtr.Rate = temp.Rate) AND
			((rtr.RateN IS NULL && temp.RateN IS NULL) || rtr.RateN = temp.RateN) AND
			((rtr.ConnectionFee IS NULL && temp.ConnectionFee IS NULL) || rtr.ConnectionFee = temp.ConnectionFee) AND
			((rtr.MinimumDuration IS NULL && temp.MinimumDuration IS NULL) || rtr.MinimumDuration = temp.MinimumDuration) AND
			((rtr.Interval1 IS NULL && temp.Interval1 IS NULL) || rtr.Interval1 = temp.Interval1) AND
			((rtr.IntervalN IS NULL && temp.IntervalN IS NULL) || rtr.IntervalN = temp.IntervalN) AND
			((rtr.RoutingCategoryID IS NULL && temp.RoutingCategoryID IS NULL) || rtr.RoutingCategoryID = temp.RoutingCategoryID) AND
			((rtr.Preference IS NULL && temp.Preference IS NULL) || rtr.Preference = temp.Preference) AND
			((rtr.Blocked IS NULL && temp.Blocked IS NULL) || rtr.Blocked = temp.Blocked) AND
			((rtr.RateCurrency IS NULL && temp.RateCurrency IS NULL) || rtr.RateCurrency = temp.RateCurrency) AND
			((rtr.ConnectionFeeCurrency IS NULL && temp.ConnectionFeeCurrency IS NULL) || rtr.ConnectionFeeCurrency = temp.ConnectionFeeCurrency);

	END IF;


	-- if rate table is not vendor rate table and rate approval process is on then set approval status to awaiting approval while updating
	IF v_RateTableAppliedTo_!=2 AND v_RateApprovalProcess_=1
	THEN
		UPDATE
			tmp_TempRateTableRate_
		SET
			ApprovedStatus = 0,
			ApprovedBy = NULL,
			ApprovedDate = NULL;
			
			
		INSERT INTO tblRateTableRateAA (
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
			MinimumDuration,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			RateCurrency,
			ConnectionFeeCurrency,
			RateTableRateID
		)
		SELECT
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
			MinimumDuration,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			IF(p_action=1,v_StatusAwaitingApproval_,v_StatusDelete_) AS ApprovedStatus, -- if action=update then status=aa else status=aadelete
			ApprovedBy,
			ApprovedDate,
			RateCurrency,
			ConnectionFeeCurrency,
			RateTableRateID
		FROM
			tmp_TempRateTableRate_;
		
		LEAVE ThisSP;
		
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
			MinimumDuration,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			RateCurrency,
			ConnectionFeeCurrency
		)
		SELECT
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
			MinimumDuration,
			Interval1,
			IntervalN,
			ConnectionFee,
			RoutingCategoryID,
			Preference,
			Blocked,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			RateCurrency,
			ConnectionFeeCurrency
		FROM
			tmp_TempRateTableRate_;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSCronJobDeleteOldRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSCronJobDeleteOldRateTableDIDRate`(
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
		AND rtr2.TimezonesID = rtr.TimezonesID
		AND rtr2.City = rtr.City
		AND rtr2.Tariff = rtr.Tariff
	SET
		rtr.EndDate=NOW()
	WHERE
		rtr.EffectiveDate <= NOW() AND
		rtr2.EffectiveDate <= NOW() AND
		rtr.EffectiveDate < rtr2.EffectiveDate;


	INSERT INTO tblRateTableDIDRateArchive
	(
		RateTableDIDRateID,
		OriginationRateID,
		RateId,
		RateTableId,
		TimezonesID,
		EffectiveDate,
		EndDate,
		City,
		Tariff,
		AccessType,
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
		OneOffCostCurrency,
		MonthlyCostCurrency,
		CostPerCallCurrency,
		CostPerMinuteCurrency,
		SurchargePerCallCurrency,
		SurchargePerMinuteCurrency,
		OutpaymentPerCallCurrency,
		OutpaymentPerMinuteCurrency,
		SurchargesCurrency,
		ChargebackCurrency,
		CollectionCostAmountCurrency,
		RegistrationCostPerNumberCurrency,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		Notes
	)
	SELECT DISTINCT -- null ,
		`RateTableDIDRateID`,
		`OriginationRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,DATE(NOW())) AS EndDate,
		`City`,
		`Tariff`,
		`AccessType`,
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
		`OneOffCostCurrency`,
        `MonthlyCostCurrency`,
        `CostPerCallCurrency`,
        `CostPerMinuteCurrency`,
        `SurchargePerCallCurrency`,
        `SurchargePerMinuteCurrency`,
        `OutpaymentPerCallCurrency`,
        `OutpaymentPerMinuteCurrency`,
        `SurchargesCurrency`,
        `ChargebackCurrency`,
        `CollectionCostAmountCurrency`,
        `RegistrationCostPerNumberCurrency`,
		NOW() AS `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		CONCAT('Ends Today rates @ ' , NOW() ) AS `Notes`
	FROM 
		tblRateTableDIDRate
	WHERE 
		EndDate <= NOW();



	DELETE rtr
	FROM tblRateTableDIDRate rtr
	INNER JOIN tblRateTableDIDRateArchive rtra
	ON rtr.RateTableDIDRateID = rtra.RateTableDIDRateID;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSCronJobDeleteOldRateTablePKGRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSCronJobDeleteOldRateTablePKGRate`(
	IN `p_DeletedBy` TEXT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTablePKGRate rtr
	INNER JOIN tblRateTablePKGRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND rtr2.TimezonesID = rtr.TimezonesID
	SET
		rtr.EndDate=NOW()
	WHERE
		rtr.EffectiveDate <= NOW() AND
		rtr2.EffectiveDate <= NOW() AND
		rtr.EffectiveDate < rtr2.EffectiveDate;


	INSERT INTO tblRateTablePKGRateArchive
	(
		RateTablePKGRateID,
		RateId,
		RateTableId,
		TimezonesID,
		EffectiveDate,
		EndDate,
		OneOffCost,
		MonthlyCost,
		PackageCostPerMinute,
		RecordingCostPerMinute,
		OneOffCostCurrency,
		MonthlyCostCurrency,
		PackageCostPerMinuteCurrency,
		RecordingCostPerMinuteCurrency,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		Notes
	)
	SELECT DISTINCT -- null ,
		`RateTablePKGRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,DATE(NOW())) AS EndDate,
		`OneOffCost`,
		`MonthlyCost`,
		`PackageCostPerMinute`,
		`RecordingCostPerMinute`,
		`OneOffCostCurrency`,
        `MonthlyCostCurrency`,
        `PackageCostPerMinuteCurrency`,
        `RecordingCostPerMinuteCurrency`,
        NOW() AS `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		CONCAT('Ends Today rates @ ' , NOW() ) AS `Notes`
	FROM 
		tblRateTablePKGRate
	WHERE 
		EndDate <= NOW();
		

	DELETE rtr
	FROM tblRateTablePKGRate rtr
	INNER JOIN tblRateTablePKGRateArchive rtra
	ON rtr.RateTablePKGRateID = rtra.RateTablePKGRateID;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSCronJobDeleteOldRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSCronJobDeleteOldRateTableRate`(
	IN `p_DeletedBy` TEXT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableRate rtr
	INNER JOIN tblRateTableRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND rtr2.OriginationRateID = rtr.OriginationRateID
		AND rtr2.TimezonesID = rtr.TimezonesID
	SET
		rtr.EndDate=NOW()
	WHERE
		rtr.EffectiveDate <= NOW() AND
		rtr2.EffectiveDate <= NOW() AND
		rtr.EffectiveDate < rtr2.EffectiveDate;

	INSERT INTO tblRateTableRateArchive
	(
		RateTableRateID,
		RateTableId,
		TimezonesID,
		OriginationRateID,
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
		MinimumDuration,
		ConnectionFee,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		RateCurrency,
		ConnectionFeeCurrency,
		Notes
	)
	SELECT DISTINCT  -- null , 
		`RateTableRateID`,
		`RateTableId`,
		`TimezonesID`,
		`OriginationRateID`,
		`RateId`,
		`Rate`,
		`RateN`,
		`EffectiveDate`,
		IFNULL(`EndDate`,DATE(NOW())) AS EndDate,
		`updated_at`,
		NOW() AS `created_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`MinimumDuration`,
		`ConnectionFee`,
		`RoutingCategoryID`,
		`Preference`,
		`Blocked`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		`RateCurrency`,
		`ConnectionFeeCurrency`,
		CONCAT('Ends Today rates @ ' , NOW() ) AS `Notes`
	FROM
		tblRateTableRate
	WHERE
		EndDate <= NOW();


	DELETE rtr
	FROM tblRateTableRate rtr
	INNER JOIN tblRateTableRateArchive rtra
	ON rtr.RateTableRateID = rtra.RateTableRateID;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSMapCountryRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSMapCountryRateTableDIDRate`(
	IN `p_ProcessID` TEXT,
	IN `p_CountryMapping` INT,
	IN `p_OriginationCountryMapping` INT
)
ThisSP:BEGIN

	DECLARE v_Country_Error_ INT DEFAULT 0;
	DECLARE v_OCountry_Error_ INT DEFAULT 0;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

	IF p_CountryMapping = 1
	THEN
		SELECT
			COUNT(*) INTO v_Country_Error_
		FROM
			tblTempRateTableDIDRate temp
		LEFT JOIN
			tblCountry c ON (c.Country=temp.CountryCode OR FIND_IN_SET(temp.CountryCode,c.Keywords) != 0)
		WHERE
			temp.ProcessID = p_ProcessID AND
			temp.CountryCode IS NOT NULL AND
			temp.CountryCode != '' AND
			c.CountryID IS NULL;

		IF v_Country_Error_ = 0
		THEN
			UPDATE
				tblTempRateTableDIDRate temp
			LEFT JOIN
				tblCountry c ON (c.Country=temp.CountryCode OR FIND_IN_SET(temp.CountryCode,c.Keywords) != 0)
			SET
				temp.CountryCode = c.Prefix
			WHERE
				temp.ProcessID = p_ProcessID AND
				temp.CountryCode IS NOT NULL AND
				temp.CountryCode != '' AND
				c.CountryID IS NOT NULL;
		ELSE
			INSERT INTO tmp_JobLog_ (Message)
			SELECT DISTINCT
				CONCAT(temp.CountryCode , ' Country NOT FOUND IN DATABASE')
			FROM
				tblTempRateTableDIDRate temp
			LEFT JOIN
				tblCountry c ON (c.Country=temp.CountryCode OR FIND_IN_SET(temp.CountryCode,c.Keywords) != 0)
			WHERE
				temp.ProcessID = p_ProcessID AND
				temp.CountryCode IS NOT NULL AND
				temp.CountryCode != '' AND
				c.CountryID IS NULL;
		END IF;
	END IF;

	IF p_OriginationCountryMapping = 1
	THEN
		SELECT
			COUNT(*) INTO v_OCountry_Error_
		FROM
			tblTempRateTableDIDRate temp
		LEFT JOIN
			tblCountry c ON (c.Country=temp.OriginationCountryCode OR FIND_IN_SET(temp.OriginationCountryCode,c.Keywords) != 0)
		WHERE
			temp.ProcessID = p_ProcessID AND
			temp.OriginationCountryCode IS NOT NULL AND
			temp.OriginationCountryCode != '' AND
			c.CountryID IS NULL;

		IF v_Country_Error_ = 0
		THEN
			UPDATE
				tblTempRateTableDIDRate temp
			LEFT JOIN
				tblCountry c ON (c.Country=temp.OriginationCountryCode OR FIND_IN_SET(temp.OriginationCountryCode,c.Keywords) != 0)
			SET
				temp.OriginationCountryCode = c.Prefix
			WHERE
				temp.ProcessID = p_ProcessID AND
				temp.OriginationCountryCode IS NOT NULL AND
				temp.OriginationCountryCode != '' AND
				c.CountryID IS NULL;
		ELSE
			INSERT INTO tmp_JobLog_ (Message)
			SELECT DISTINCT
				CONCAT(temp.OriginationCountryCode , ' Origination Country NOT FOUND IN DATABASE')
			FROM
				tblTempRateTableDIDRate temp
			LEFT JOIN
				tblCountry c ON (c.Country=temp.OriginationCountryCode OR FIND_IN_SET(temp.OriginationCountryCode,c.Keywords) != 0)
			WHERE
				temp.ProcessID = p_ProcessID AND
				temp.OriginationCountryCode IS NOT NULL AND
				temp.OriginationCountryCode != '' AND
				c.CountryID IS NOT NULL;
		END IF;
	END IF;

	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_UpdateCountryIDRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_UpdateCountryIDRateTableRate`(
	IN `p_table_name` TEXT
)
BEGIN

	DECLARE v_countryId int;
	DECLARE v_countryCount int;
	DECLARE v_rowCount_ INT;
	DECLARE i INTEGER;

	DROP TEMPORARY TABLE IF EXISTS tmp_Prefix;
	CREATE TEMPORARY TABLE tmp_Prefix (
		ID INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		Prefix varchar(500) ,
		CountryID int,
		INDEX `index_Prefix` (`Prefix`)
	);

	SET @stm = CONCAT("DROP TEMPORARY TABLE IF EXISTS ",p_table_name,"_2_");

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT("CREATE TEMPORARY TABLE IF NOT EXISTS ",p_table_name,"_2_ AS (SELECT * FROM ",p_table_name,")");

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT("
		INSERT INTO tmp_Prefix
		(
			Prefix,
			CountryID
		)
		SELECT
			DISTINCT
			tblTempRateTableRate.Code,
			tblCountry.CountryID
		FROM
			",p_table_name," AS tblTempRateTableRate
		LEFT JOIN
			tblCountry ON tblTempRateTableRate.Code LIKE CONCAT(tblCountry.Prefix, '%')
		LEFT OUTER JOIN
		(
			SELECT Prefix FROM tblCountry GROUP BY Prefix HAVING COUNT(*) > 1
		) d ON tblCountry.Prefix = d.Prefix
		WHERE
			(tblTempRateTableRate.Code LIKE CONCAT(tblCountry.Prefix, '%') AND d.Prefix IS NULL)
			OR (
				tblTempRateTableRate.Code LIKE CONCAT(tblCountry.Prefix, '%') AND
				d.Prefix IS NOT NULL AND
				tblTempRateTableRate.Description LIKE CONCAT('%', tblCountry.Country, '%')
			)

		UNION

		SELECT
			DISTINCT
			tblTempRateTableRate.OriginationCode,
			tblCountry.CountryID
		FROM
			",p_table_name,"_2_ AS tblTempRateTableRate
		LEFT JOIN
			tblCountry ON tblTempRateTableRate.OriginationCode LIKE CONCAT(tblCountry.Prefix, '%')
		LEFT OUTER JOIN
		(
			SELECT Prefix FROM tblCountry GROUP BY Prefix HAVING COUNT(*) > 1
		) d ON tblCountry.Prefix = d.Prefix
		WHERE
			(tblTempRateTableRate.OriginationCode LIKE CONCAT(tblCountry.Prefix, '%') AND d.Prefix IS NULL)
			OR (
				tblTempRateTableRate.OriginationCode LIKE CONCAT(tblCountry.Prefix, '%') AND
				d.Prefix IS NOT NULL AND
				tblTempRateTableRate.OriginationDescription LIKE CONCAT('%', tblCountry.Country, '%')
			)
	");

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	DROP TEMPORARY TABLE IF EXISTS `tmp_CountryKeywords`;
	CREATE TEMPORARY TABLE `tmp_CountryKeywords` (
		`Prefix`varchar(500) NULL DEFAULT NULL,
		`Keywords` Text NULL DEFAULT NULL,
		`CountryID` int NULL DEFAULT NULL
	);


	SET i = 1;
	REPEAT
		INSERT INTO tmp_CountryKeywords ( Prefix,Keywords,CountryID)
      SELECT
			Prefix,FnStringSplit(Keywords,',' , i) as Keywords, CountryID
		FROM
			tblCountry
      WHERE
			FnStringSplit(Keywords, ',' , i) IS NOT NULL;

		SET i = i + 1;
		UNTIL ROW_COUNT() = 0
	END REPEAT;

	DROP TEMPORARY TABLE IF EXISTS tmp_CountryKeywords_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CountryKeywords_2 AS (SELECT * FROM tmp_CountryKeywords);

	SET @stm = CONCAT("
		INSERT IGNORE INTO tmp_Prefix
		(
			Prefix,
			CountryID
		)
		SELECT
			DISTINCT
			tblTempRateTableRate.Code,
			tmp_CountryKeywords.CountryID
		FROM
			",p_table_name," AS tblTempRateTableRate
		LEFT JOIN
			tmp_CountryKeywords ON tblTempRateTableRate.Code LIKE CONCAT(tmp_CountryKeywords.Prefix, '%')
		WHERE
			tblTempRateTableRate.Code LIKE CONCAT(tmp_CountryKeywords.Prefix, '%') AND
			tblTempRateTableRate.Description LIKE CONCAT('%', tmp_CountryKeywords.Keywords, '%')

		UNION

		SELECT
			DISTINCT
			tblTempRateTableRate.OriginationCode,
			tmp_CountryKeywords.CountryID
		FROM
			",p_table_name,"_2_ AS tblTempRateTableRate
		LEFT JOIN
			tmp_CountryKeywords_2 AS tmp_CountryKeywords ON tblTempRateTableRate.OriginationCode LIKE CONCAT(tmp_CountryKeywords.Prefix, '%')
		WHERE
			tblTempRateTableRate.OriginationCode LIKE CONCAT(tmp_CountryKeywords.Prefix, '%') AND
			tblTempRateTableRate.OriginationDescription LIKE CONCAT('%', tmp_CountryKeywords.Keywords, '%')
	");

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	DROP TEMPORARY TABLE IF EXISTS tmp_Prefix_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Prefix_2 AS (SELECT * FROM tmp_Prefix);

	DELETE
		n1
	FROM
		tmp_Prefix n1
	LEFT JOIN
		tmp_Prefix_2 n2 ON n1.Prefix = n2.Prefix AND n1.CountryID = n2.CountryID
	WHERE
		n1.ID < n2.ID;

END//
DELIMITER ;