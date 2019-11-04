use `speakintelligentRM`;

INSERT INTO `tblJobType` (`JobTypeID`, `Code`, `Title`, `Description`, `CreatedDate`, `CreatedBy`, `ModifiedDate`, `ModifiedBy`) VALUES (38, 'TRM', 'Termination Rate Margin', 'Termination Rate Margin Difference for Awaiting Approval Rates', '2019-10-25 11:28:08', 'RateManagementSystem', NULL, NULL);

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
                        @row_num := IF(@prev_RateId = tmp.RateID AND @prev_OriginationRateID = tmp.OriginationRateID AND @prev_TimezonesID = tmp.TimezonesID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                        @prev_RateId := tmp.RateID,
                        @prev_OriginationRateID := tmp.OriginationRateID,
                        @prev_TimezonesID := tmp.TimezonesID,
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
                        order by vr1.RateID desc, vr1.OriginationRateID desc, vr1.TimezonesID desc, vr1.EffectiveDate desc
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
                      order by RateID desc, OriginationRateID desc, TimezonesID desc, EffectiveDate desc
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
	CREATE TEMPORARY TABLE tmp_split_RateTableRate_2 LIKE tmp_split_RateTableRate_;
	INSERT INTO tmp_split_RateTableRate_2 SELECT * FROM tmp_split_RateTableRate_;

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
		INDEX tmp_Code_OCode_Effective_DP_Timezon_Rate_TempRateID (`Code`,`OriginationCode`,`EffectiveDate`,`DialStringPrefix`,`TimezonesID`,`Rate`,`TempRateTableRateID`),
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
				AND vrcl.TimezonesID = tblTempRateTablePKGRate.TimezonesID
				AND vrcl.EffectiveDate = tblTempRateTablePKGRate.EffectiveDate
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



		-- complete file
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
			/*
				this one condition removed at 2019-09-06 as per SI Requirement
				if upload any timezones rate with paritial file upload then all old rates on all timezones for that prefix need to deleted.
			*/
		--	AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		SET tblRateTableRate.EndDate = NOW()
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND
			-- DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d')
			(
				( -- if future rates then delete same date existing records
					DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d') > CURDATE() AND
					DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d')
				)
				OR
				( -- if current rates then delete current or older records
					DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d') <= CURDATE() AND
					DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') <= DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d')
				)
			);

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
		INDEX tmp_Code_OCode_Effective_DP_Timezon_Rate_TempRateID (`Code`,`OriginationCode`,`EffectiveDate`,`DialStringPrefix`,`TimezonesID`,`Rate`,`TempRateTableRateID`),
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
	IN `p_RateTableIds` BIGINT,
	IN `p_TimezonesIDs` INT,
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
		(rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs) AND rtr.EffectiveDate <= NOW()) AND
		(rtr2.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr2.TimezonesID=p_TimezonesIDs) AND rtr2.EffectiveDate <= NOW()) AND
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
		Notes,
		VendorID
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
		concat('Ends Today rates @ ' , now() ) as `Notes`,
		VendorID
	FROM tblRateTableRate
	WHERE RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR TimezonesID=p_TimezonesIDs) AND EndDate <= NOW();


	DELETE  rtr
	FROM tblRateTableRate rtr
	inner join tblRateTableRateArchive rtra
		on rtr.RateTableRateID = rtra.RateTableRateID
	WHERE  rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs);


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableRateAA`(
	IN `p_RateTableIds` BIGINT,
	IN `p_TimezonesIDs` INT,
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
		(rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs) AND rtr.EffectiveDate <= NOW()) AND
		(rtr2.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr2.TimezonesID=p_TimezonesIDs) AND rtr2.EffectiveDate <= NOW()) AND
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
		Notes,
		VendorID
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
		concat('Ends Today rates @ ' , now() ) as `Notes`,
		VendorID
	FROM tblRateTableRateAA
	WHERE
		RateTableId=p_RateTableIds
		AND (p_TimezonesIDs IS NULL OR TimezonesID=p_TimezonesIDs)
		AND EndDate <= NOW()
		AND ApprovedStatus = 2; -- only rejected rates will be archive


	DELETE  rtr
	FROM tblRateTableRateAA rtr
	WHERE
		rtr.RateTableId=p_RateTableIds
		AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs)
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
	IN `p_lSortCol` VARCHAR(500),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
ThisSP:BEGIN
	DECLARE v_OffSet_ int;

	DECLARE v_Select_Count VARCHAR(500);
	DECLARE v_Select LONGTEXT;
	DECLARE v_common_query LONGTEXT;
	DECLARE v_Order_By LONGTEXT;
	DECLARE v_Sort_By LONGTEXT;
	DECLARE v_limit VARCHAR(50);

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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_Count_;
	CREATE TEMPORARY TABLE tmp_RateTableDIDRate_Count_ (
		`Count` INT
	);

	-- in order by we need to specify table name of columns to avoid error
	CASE
		WHEN p_lSortCol = 'Country' THEN SET v_Sort_By=CONCAT('tblCountry.Country ',p_SortOrder);
		WHEN p_lSortCol = 'OriginationCode' THEN SET v_Sort_By=CONCAT('OriginationRate.Code ',p_SortOrder);
		WHEN p_lSortCol = 'Code' THEN SET v_Sort_By=CONCAT('tblRate.Code ',p_SortOrder);
		WHEN p_lSortCol = 'TimezoneTitle' THEN SET v_Sort_By=CONCAT('tblTimezones.Title ',p_SortOrder);
		WHEN p_lSortCol = 'ModifiedBy' THEN SET v_Sort_By=CONCAT('tblRateTableDIDRate.ModifiedBy ',p_SortOrder,',tblRateTableDIDRate.updated_at ',p_SortOrder);
		WHEN p_lSortCol = 'ApprovedBy' THEN SET v_Sort_By=CONCAT('tblRateTableDIDRate.ApprovedBy ',p_SortOrder,',tblRateTableDIDRate.ApprovedDate ',p_SortOrder);
	ELSE
		SET v_Sort_By = 'tblRate.Code';
	END CASE;


	-- we need order by and limit only in grid, we don't need it in export
	IF(p_isExport = 0)
	THEN
		SET v_Order_By = CONCAT('ORDER BY ',v_Sort_By,',RateTableDIDRateID ASC');
		SET v_limit 	= CONCAT('LIMIT ',p_RowspPage,' OFFSET ',v_OffSet_);
	ELSE
		SET v_Order_By = '';
		SET v_limit 	= '';
	END IF;


	SET v_common_query = CONCAT('
		FROM
			tblRateTableDIDRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableDIDRate.RateID
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
		WHERE
			tblRateTableDIDRate.RateTableId = ',p_RateTableId,'
			AND (tblRate.CompanyID = ',p_companyid,')
			',IF(p_contryID IS NULL,'',CONCAT('AND tblRate.CountryID = ',p_contryID)),'
			',IF(p_origination_code IS NULL,'',CONCAT('AND OriginationRate.Code LIKE REPLACE("',p_origination_code,'", "*", "%")')),'
			',IF(p_code IS NULL,'',CONCAT('AND tblRate.Code LIKE REPLACE("',p_code,'", "*", "%")')),'
			',IF(p_City IS NULL,'',CONCAT('AND tblRateTableDIDRate.City = "',p_City,'"')),'
			',IF(p_Tariff IS NULL,'',CONCAT('AND tblRateTableDIDRate.Tariff = "',p_Tariff,'"')),'
			',IF(p_AccessType IS NULL,'',CONCAT('AND tblRateTableDIDRate.AccessType = "',p_AccessType,'"')),'
			',IF(p_ApprovedStatus IS NULL,'',CONCAT('AND tblRateTableDIDRate.ApprovedStatus = ',p_ApprovedStatus)),'
			',IF(p_TimezonesID IS NULL,'',CONCAT('AND tblRateTableDIDRate.TimezonesID = ',p_TimezonesID)),'
			AND TrunkID = ',p_trunkID,'
			AND (
				"',p_effective,'" = "All"
				OR ("',p_effective,'" = "Now" AND EffectiveDate <= NOW() )
				OR ("',p_effective,'" = "Future" AND EffectiveDate > NOW())
			)
	');

	SET v_Select = '
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
		IFNULL(tblOneOffCostCurrency.Symbol,"") AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,"") AS MonthlyCostCurrencySymbol,
		IFNULL(tblCostPerCallCurrency.Symbol,"") AS CostPerCallCurrencySymbol,
		IFNULL(tblCostPerMinuteCurrency.Symbol,"") AS CostPerMinuteCurrencySymbol,
		IFNULL(tblSurchargePerCallCurrency.Symbol,"") AS SurchargePerCallCurrencySymbol,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol,"") AS SurchargePerMinuteCurrencySymbol,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol,"") AS OutpaymentPerCallCurrencySymbol,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,"") AS OutpaymentPerMinuteCurrencySymbol,
		IFNULL(tblSurchargesCurrency.Symbol,"") AS SurchargesCurrencySymbol,
		IFNULL(tblChargebackCurrency.Symbol,"") AS ChargebackCurrencySymbol,
		IFNULL(tblCollectionCostAmountCurrency.Symbol,"") AS CollectionCostAmountCurrencySymbol,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,"") AS RegistrationCostPerNumberCurrencySymbol,
		tblRateTableDIDRate.TimezonesID
	';

	SET v_Select_Count = 'COUNT(tblRateTableDIDRate.RateTableDIDRateID)';


	-- final query from dynamic variables
	SET @stm_q_select = CONCAT('
		INSERT INTO tmp_RateTableDIDRate_
		SELECT ',v_Select,' ',v_common_query,' ',v_Order_By,' ',v_limit,' ;
	');
--	 select @stm_q_select; leave ThisSP;
	PREPARE stmt FROM @stm_q_select;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	-- count only need in view page, we don't need it when export
	IF(p_isExport = 0)
	THEN
		SET @stm_q_count = CONCAT('
			INSERT INTO tmp_RateTableDIDRate_Count_
			SELECT ',v_Select_Count,' ',v_common_query,';
		');

		-- select @stm_q_select,@stm_q_count; leave ThisSP;

		PREPARE stmt FROM @stm_q_count;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;


	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate4_ as (select * from tmp_RateTableDIDRate_);
		DELETE n1 FROM tmp_RateTableDIDRate_ n1, tmp_RateTableDIDRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID AND n1.City = n2.City AND n1.Tariff = n2.Tariff;
	END IF;


	-- if request for grid not export
   IF p_isExport = 0
   THEN
   	SELECT * FROM tmp_RateTableDIDRate_;
		SELECT `Count` AS totalcount FROM tmp_RateTableDIDRate_Count_;
   END IF;


	 -- export basic view
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

	 -- export advance view
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
	IN `p_Percentage` VARCHAR(500),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(500),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
ThisSP:BEGIN
	DECLARE v_OffSet_ INT;
	DECLARE v_Reseller_ INT;

	DECLARE v_Select_Count VARCHAR(500);
	DECLARE v_Select LONGTEXT;
	DECLARE v_common_query LONGTEXT;
	DECLARE v_Order_By LONGTEXT;
	DECLARE v_Sort_By LONGTEXT;
	DECLARE v_limit VARCHAR(50);
	DECLARE v_Where LONGTEXT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT Reseller INTO v_Reseller_ FROM tblRateTable WHERE CompanyID=p_companyid AND RateTableId=p_RateTableId;

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
		CurrentOneOffCost DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentMonthlyCost DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentCostPerCall DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentSurchargePerCall DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentSurchargePerMinute DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentOutpaymentPerCall DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentOutpaymentPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentSurcharges DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentChargeback DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentCollectionCostAmount DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentCollectionCostPercentage DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentRegistrationCostPerNumber DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentOneOffCostCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentMonthlyCostCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentCostPerCallCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentCostPerMinuteCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentSurchargePerCallCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentSurchargePerMinuteCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentOutpaymentPerCallCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentOutpaymentPerMinuteCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentSurchargesCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentChargebackCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentCollectionCostAmountCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentRegistrationCostPerNumberCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		OneOffCostMargin VARCHAR(255) NULL DEFAULT NULL,
		MonthlyCostMargin VARCHAR(255) NULL DEFAULT NULL,
		CostPerCallMargin VARCHAR(255) NULL DEFAULT NULL,
		CostPerMinuteMargin VARCHAR(255) NULL DEFAULT NULL,
		SurchargePerCallMargin VARCHAR(255) NULL DEFAULT NULL,
		SurchargePerMinuteMargin VARCHAR(255) NULL DEFAULT NULL,
		OutpaymentPerCallMargin VARCHAR(255) NULL DEFAULT NULL,
		OutpaymentPerMinuteMargin VARCHAR(255) NULL DEFAULT NULL,
		SurchargesMargin VARCHAR(255) NULL DEFAULT NULL,
		ChargebackMargin VARCHAR(255) NULL DEFAULT NULL,
		CollectionCostAmountMargin VARCHAR(255) NULL DEFAULT NULL,
		CollectionCostPercentageMargin VARCHAR(255) NULL DEFAULT NULL,
		RegistrationCostPerNumberMargin VARCHAR(255) NULL DEFAULT NULL,
		OneOffCostMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		MonthlyCostMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		CostPerCallMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		CostPerMinuteMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		SurchargePerCallMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		SurchargePerMinuteMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		OutpaymentPerCallMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		OutpaymentPerMinuteMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		SurchargesMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		ChargebackMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		CollectionCostAmountMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		CollectionCostPercentageMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		RegistrationCostPerNumberMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		INDEX `tmp_IX_DIDAA_RateID_ORateID_TimezonesID_EffDate` (`RateID`, `OriginationRateID`, `City`, `Tariff`, `TimezonesID`, `EffectiveDate`)
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_Count_;
	CREATE TEMPORARY TABLE tmp_RateTableDIDRate_Count_ (
		`Count` INT
	);

	-- in order by we need to specify table name of columns to avoid error
/*	CASE
		WHEN p_lSortCol = 'Country' THEN SET v_Sort_By=CONCAT('tblCountry.Country ',p_SortOrder);
		WHEN p_lSortCol = 'OriginationCode' THEN SET v_Sort_By=CONCAT('OriginationRate.Code ',p_SortOrder);
		WHEN p_lSortCol = 'Code' THEN SET v_Sort_By=CONCAT('tblRate.Code ',p_SortOrder);
		WHEN p_lSortCol = 'TimezoneTitle' THEN SET v_Sort_By=CONCAT('tblTimezones.Title ',p_SortOrder);
		WHEN p_lSortCol = 'ModifiedBy' THEN SET v_Sort_By=CONCAT('tblRateTableDIDRate.ModifiedBy ',p_SortOrder,',tblRateTableDIDRate.updated_at ',p_SortOrder);
		WHEN p_lSortCol = 'ApprovedBy' THEN SET v_Sort_By=CONCAT('tblRateTableDIDRate.ApprovedBy ',p_SortOrder,',tblRateTableDIDRate.ApprovedDate ',p_SortOrder);
	ELSE
		SET v_Sort_By = 'tblRate.Code';
	END CASE; */

	SET v_Sort_By = CONCAT(p_lSortCol,' ',p_SortOrder);

	IF(p_Percentage IS NOT NULL)
	THEN
		SET v_Where = CONCAT('WHERE OneOffCostMarginPercent ',p_Percentage,' OR MonthlyCostMarginPercent ',p_Percentage,' OR CostPerCallMarginPercent ',p_Percentage,' OR CostPerMinuteMarginPercent ',p_Percentage,' OR SurchargePerCallMarginPercent ',p_Percentage,' OR SurchargePerMinuteMarginPercent ',p_Percentage,' OR OutpaymentPerCallMarginPercent ',p_Percentage,' OR OutpaymentPerMinuteMarginPercent ',p_Percentage,' OR SurchargesMarginPercent ',p_Percentage,' OR ChargebackMarginPercent ',p_Percentage,' OR CollectionCostAmountMarginPercent ',p_Percentage,' OR CollectionCostPercentageMarginPercent ',p_Percentage,' OR RegistrationCostPerNumberMarginPercent ',p_Percentage);
	ELSE
		SET v_Where = '';
	END IF;


	-- we need order by and limit only in grid, we don't need it in export
	IF(p_isExport = 0)
	THEN
		SET v_Order_By = CONCAT('ORDER BY ',v_Sort_By);
		SET v_limit 	= CONCAT('LIMIT ',p_RowspPage,' OFFSET ',v_OffSet_);
	ELSE
		SET v_Order_By = '';
		SET v_limit 	= '';
	END IF;


	SET v_common_query = CONCAT('
		FROM
			tblRateTableDIDRateAA AS tblRateTableDIDRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableDIDRate.RateID
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
		WHERE
			tblRateTableDIDRate.RateTableId = ',p_RateTableId,'
			AND (tblRate.CompanyID = ',p_companyid,')
			',IF(p_contryID IS NULL,'',CONCAT('AND tblRate.CountryID = ',p_contryID)),'
			',IF(p_origination_code IS NULL,'',CONCAT('AND OriginationRate.Code LIKE REPLACE("',p_origination_code,'", "*", "%")')),'
			',IF(p_code IS NULL,'',CONCAT('AND tblRate.Code LIKE REPLACE("',p_code,'", "*", "%")')),'
			',IF(p_City IS NULL,'',CONCAT('AND tblRateTableDIDRate.City = "',p_City,'"')),'
			',IF(p_Tariff IS NULL,'',CONCAT('AND tblRateTableDIDRate.Tariff = "',p_Tariff,'"')),'
			',IF(p_AccessType IS NULL,'',CONCAT('AND tblRateTableDIDRate.AccessType = "',p_AccessType,'"')),'
			',IF(p_ApprovedStatus IS NULL,'',CONCAT('AND tblRateTableDIDRate.ApprovedStatus = ',p_ApprovedStatus)),'
			',IF(p_TimezonesID IS NULL,'',CONCAT('AND tblRateTableDIDRate.TimezonesID = ',p_TimezonesID)),'
			AND (
				"',p_effective,'" = "All"
				OR ("',p_effective,'" = "Now" AND EffectiveDate <= NOW() )
				OR ("',p_effective,'" = "Future" AND EffectiveDate > NOW())
			)
	');

	SET v_Select = '
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
		IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
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
		IFNULL(tblOneOffCostCurrency.Symbol,"") AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,"") AS MonthlyCostCurrencySymbol,
		IFNULL(tblCostPerCallCurrency.Symbol,"") AS CostPerCallCurrencySymbol,
		IFNULL(tblCostPerMinuteCurrency.Symbol,"") AS CostPerMinuteCurrencySymbol,
		IFNULL(tblSurchargePerCallCurrency.Symbol,"") AS SurchargePerCallCurrencySymbol,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol,"") AS SurchargePerMinuteCurrencySymbol,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol,"") AS OutpaymentPerCallCurrencySymbol,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,"") AS OutpaymentPerMinuteCurrencySymbol,
		IFNULL(tblSurchargesCurrency.Symbol,"") AS SurchargesCurrencySymbol,
		IFNULL(tblChargebackCurrency.Symbol,"") AS ChargebackCurrencySymbol,
		IFNULL(tblCollectionCostAmountCurrency.Symbol,"") AS CollectionCostAmountCurrencySymbol,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,"") AS RegistrationCostPerNumberCurrencySymbol,
		tblRateTableDIDRate.TimezonesID
	';

	SET v_Select_Count = 'COUNT(tblRateTableDIDRate.RateTableDIDRateAAID)';


	-- final query from dynamic variables
	SET @stm_q_select = CONCAT('
		INSERT INTO tmp_RateTableDIDRate_
		(
			ID,
			AccessType,
			Country,
			OriginationCode,
			Code,
			City,
			Tariff,
			TimezoneTitle,
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
			ModifiedBy,
			RateTableDIDRateID,
			OriginationRateID,
			RateID,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
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
			OneOffCostCurrencySymbol,
			MonthlyCostCurrencySymbol,
			CostPerCallCurrencySymbol,
			CostPerMinuteCurrencySymbol,
			SurchargePerCallCurrencySymbol,
			SurchargePerMinuteCurrencySymbol,
			OutpaymentPerCallCurrencySymbol,
			OutpaymentPerMinuteCurrencySymbol,
			SurchargesCurrencySymbol,
			ChargebackCurrencySymbol,
			CollectionCostAmountCurrencySymbol,
			RegistrationCostPerNumberCurrencySymbol,
			TimezonesID
		)
		SELECT ',v_Select,' ',v_common_query,' ;
	');	-- ,' ',v_Order_By,' ',v_limit
	-- select @stm_q_select; leave ThisSP;
	PREPARE stmt FROM @stm_q_select;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	-- count only need in view page, we don't need it when export
/*	IF(p_isExport = 0)
	THEN
		SET @stm_q_count = CONCAT('
			INSERT INTO tmp_RateTableDIDRate_Count_
			SELECT ',v_Select_Count,' ',v_common_query,';
		');

		-- select @stm_q_select,@stm_q_count; leave ThisSP;

		PREPARE stmt FROM @stm_q_count;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;	*/


	IF p_effective = 'Now'
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate_2 LIKE tmp_RateTableDIDRate_;
		INSERT INTO tmp_RateTableDIDRate_2 SELECT * FROM tmp_RateTableDIDRate_;

		DELETE n1 FROM tmp_RateTableDIDRate_ n1, tmp_RateTableDIDRate_2 n2 WHERE
		n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.City = n2.City AND n1.Tariff = n2.Tariff AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate < n2.EffectiveDate;
	END IF;


	-- update current rate, margin and margin percentage
	UPDATE
		tmp_RateTableDIDRate_ tr
	LEFT JOIN
		tblRateTableDIDRate rtr ON rtr.RateTableDIDRateID = ( SELECT tmp.RateTableDIDRateID FROM tblRateTableDIDRate tmp INNER JOIN tblRateTable tmp_r ON tmp.RateTableId=tmp_r.RateTableId WHERE tmp.RateID=tr.RateID AND tmp.OriginationRateID=tr.OriginationRateID AND tmp.City=tr.City AND tmp.Tariff=tr.Tariff AND tmp.TimezonesID = tr.TimezonesID AND ((tr.EffectiveDate < CURDATE() AND tmp.EffectiveDate <= CURDATE()) OR tmp.EffectiveDate<=tr.EffectiveDate) AND tmp_r.Reseller=v_Reseller_ ORDER BY EffectiveDate DESC,RateTableDIDRateID DESC LIMIT 1 )
	LEFT JOIN tblCurrency AS tblOneOffCostCurrency
		ON tblOneOffCostCurrency.CurrencyID = rtr.OneOffCostCurrency
	LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
		ON tblMonthlyCostCurrency.CurrencyID = rtr.MonthlyCostCurrency
	LEFT JOIN tblCurrency AS tblCostPerCallCurrency
		ON tblCostPerCallCurrency.CurrencyID = rtr.CostPerCallCurrency
	LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
		ON tblCostPerMinuteCurrency.CurrencyID = rtr.CostPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
		ON tblSurchargePerCallCurrency.CurrencyID = rtr.SurchargePerCallCurrency
	LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
		ON tblSurchargePerMinuteCurrency.CurrencyID = rtr.SurchargePerMinuteCurrency
	LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
		ON tblOutpaymentPerCallCurrency.CurrencyID = rtr.OutpaymentPerCallCurrency
	LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
		ON tblOutpaymentPerMinuteCurrency.CurrencyID = rtr.OutpaymentPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblSurchargesCurrency
		ON tblSurchargesCurrency.CurrencyID = rtr.SurchargesCurrency
	LEFT JOIN tblCurrency AS tblChargebackCurrency
		ON tblChargebackCurrency.CurrencyID = rtr.ChargebackCurrency
	LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
		ON tblCollectionCostAmountCurrency.CurrencyID = rtr.CollectionCostAmountCurrency
	LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
		ON tblRegistrationCostPerNumberCurrency.CurrencyID = rtr.RegistrationCostPerNumberCurrency
	SET
		CurrentOneOffCost = rtr.OneOffCost,
		CurrentMonthlyCost = rtr.MonthlyCost,
		CurrentCostPerCall = rtr.CostPerCall,
		CurrentCostPerMinute = rtr.CostPerMinute,
		CurrentSurchargePerCall = rtr.SurchargePerCall,
		CurrentSurchargePerMinute = rtr.SurchargePerMinute,
		CurrentOutpaymentPerCall = rtr.OutpaymentPerCall,
		CurrentOutpaymentPerMinute = rtr.OutpaymentPerMinute,
		CurrentSurcharges = rtr.Surcharges,
		CurrentChargeback = rtr.Chargeback,
		CurrentCollectionCostAmount = rtr.CollectionCostAmount,
		CurrentCollectionCostPercentage = rtr.CollectionCostPercentage,
		CurrentRegistrationCostPerNumber = rtr.RegistrationCostPerNumber,
		CurrentOneOffCostCurrencySymbol = tblOneOffCostCurrency.Symbol,
		CurrentMonthlyCostCurrencySymbol = tblMonthlyCostCurrency.Symbol,
		CurrentCostPerCallCurrencySymbol = tblCostPerCallCurrency.Symbol,
		CurrentCostPerMinuteCurrencySymbol = tblCostPerMinuteCurrency.Symbol,
		CurrentSurchargePerCallCurrencySymbol = tblSurchargePerCallCurrency.Symbol,
		CurrentSurchargePerMinuteCurrencySymbol = tblSurchargePerMinuteCurrency.Symbol,
		CurrentOutpaymentPerCallCurrencySymbol = tblOutpaymentPerCallCurrency.Symbol,
		CurrentOutpaymentPerMinuteCurrencySymbol = tblOutpaymentPerMinuteCurrency.Symbol,
		CurrentSurchargesCurrencySymbol = tblSurchargesCurrency.Symbol,
		CurrentChargebackCurrencySymbol = tblChargebackCurrency.Symbol,
		CurrentCollectionCostAmountCurrencySymbol = tblCollectionCostAmountCurrency.Symbol,
		CurrentRegistrationCostPerNumberCurrencySymbol = tblRegistrationCostPerNumberCurrency.Symbol,
		OneOffCostMargin = (tr.OneOffCost - rtr.OneOffCost),
		MonthlyCostMargin = (tr.MonthlyCost - rtr.MonthlyCost),
		CostPerCallMargin = (tr.CostPerCall - rtr.CostPerCall),
		CostPerMinuteMargin = (tr.CostPerMinute - rtr.CostPerMinute),
		SurchargePerCallMargin = (tr.SurchargePerCall - rtr.SurchargePerCall),
		SurchargePerMinuteMargin = (tr.SurchargePerMinute - rtr.SurchargePerMinute),
		OutpaymentPerCallMargin = (tr.OutpaymentPerCall - rtr.OutpaymentPerCall),
		OutpaymentPerMinuteMargin = (tr.OutpaymentPerMinute - rtr.OutpaymentPerMinute),
		SurchargesMargin = (tr.Surcharges - rtr.Surcharges),
		ChargebackMargin = (tr.Chargeback - rtr.Chargeback),
		CollectionCostAmountMargin = (tr.CollectionCostAmount - rtr.CollectionCostAmount),
		CollectionCostPercentageMargin = (tr.CollectionCostPercentage - rtr.CollectionCostPercentage),
		RegistrationCostPerNumberMargin = (tr.RegistrationCostPerNumber - rtr.RegistrationCostPerNumber),
		OneOffCostMarginPercent = ROUND(
            IF(
                (IFNULL(tr.OneOffCost,0) - IFNULL(rtr.OneOffCost,0)) <> 0,
                (((IFNULL(tr.OneOffCost,0) - IFNULL(rtr.OneOffCost,0)) * 100) / rtr.OneOffCost),
                NULL
            )
            ,2
        ),
		MonthlyCostMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.MonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) <> 0,
                (((IFNULL(tr.MonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) * 100) / rtr.MonthlyCost),
                NULL
		    )
		    ,2
        ),
		CostPerCallMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.CostPerCall,0) - IFNULL(rtr.CostPerCall,0)) <> 0,
                (((IFNULL(tr.CostPerCall,0) - IFNULL(rtr.CostPerCall,0)) * 100) / rtr.CostPerCall),
                NULL
		    )
		    ,2
        ),
		CostPerMinuteMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.CostPerMinute,0) - IFNULL(rtr.CostPerMinute,0)) <> 0,
                (((IFNULL(tr.CostPerMinute,0) - IFNULL(rtr.CostPerMinute,0)) * 100) / rtr.CostPerMinute),
                NULL
		    )
		    ,2
        ),
		SurchargePerCallMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.SurchargePerCall,0) - IFNULL(rtr.SurchargePerCall,0)) <> 0,
                (((IFNULL(tr.SurchargePerCall,0) - IFNULL(rtr.SurchargePerCall,0)) * 100) / rtr.SurchargePerCall),
                NULL
		    )
		    ,2
        ),
		SurchargePerMinuteMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.SurchargePerMinute,0) - IFNULL(rtr.SurchargePerMinute,0)) <> 0,
                (((IFNULL(tr.SurchargePerMinute,0) - IFNULL(rtr.SurchargePerMinute,0)) * 100) / rtr.SurchargePerMinute),
                NULL
		    )
		    ,2
        ),
		OutpaymentPerCallMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.OutpaymentPerCall,0) - IFNULL(rtr.OutpaymentPerCall,0)) <> 0,
                (((IFNULL(tr.OutpaymentPerCall,0) - IFNULL(rtr.OutpaymentPerCall,0)) * 100) / rtr.OutpaymentPerCall),
                NULL
		    )
		    ,2
        ),
		OutpaymentPerMinuteMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.OutpaymentPerMinute,0) - IFNULL(rtr.OutpaymentPerMinute,0)) <> 0,
                (((IFNULL(tr.OutpaymentPerMinute,0) - IFNULL(rtr.OutpaymentPerMinute,0)) * 100) / rtr.OutpaymentPerMinute),
                NULL
		    )
		    ,2
        ),
		SurchargesMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.Surcharges,0) - IFNULL(rtr.Surcharges,0)) <> 0,
                (((IFNULL(tr.Surcharges,0) - IFNULL(rtr.Surcharges,0)) * 100) / rtr.Surcharges),
                NULL
		    )
		    ,2
        ),
		ChargebackMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.Chargeback,0) - IFNULL(rtr.Chargeback,0)) <> 0,
                (((IFNULL(tr.Chargeback,0) - IFNULL(rtr.Chargeback,0)) * 100) / rtr.Chargeback),
                NULL
		    )
		    ,2
        ),
		CollectionCostAmountMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.CollectionCostAmount,0) - IFNULL(rtr.CollectionCostAmount,0)) <> 0,
                (((IFNULL(tr.CollectionCostAmount,0) - IFNULL(rtr.CollectionCostAmount,0)) * 100) / rtr.CollectionCostAmount),
                NULL
		    )
		    ,2
        ),
		CollectionCostPercentageMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.CollectionCostPercentage,0) - IFNULL(rtr.CollectionCostPercentage,0)) <> 0,
                (((IFNULL(tr.CollectionCostPercentage,0) - IFNULL(rtr.CollectionCostPercentage,0)) * 100) / rtr.CollectionCostPercentage),
                NULL
		    )
		    ,2
        ),
		RegistrationCostPerNumberMarginPercent = ROUND(
		    IF(
                (IFNULL(tr.RegistrationCostPerNumber,0) - IFNULL(rtr.RegistrationCostPerNumber,0)) <> 0,
                (((IFNULL(tr.RegistrationCostPerNumber,0) - IFNULL(rtr.RegistrationCostPerNumber,0)) * 100) / rtr.RegistrationCostPerNumber),
                NULL
		    )
		    ,2
        );


	-- if request for grid not export
    IF p_isExport = 0
    THEN
       	SET @stm_final_select = CONCAT('
				SELECT * FROM tmp_RateTableDIDRate_ ', v_Where,' ',v_Order_By,' ',v_limit,';
			');
			PREPARE stmt FROM @stm_final_select;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;

			SET @stm_final_select_count = CONCAT('
				SELECT count(*) AS totalcount FROM tmp_RateTableDIDRate_ ', v_Where,';
			');
			PREPARE stmt FROM @stm_final_select_count;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;

	--		SELECT `Count` AS totalcount FROM tmp_RateTableDIDRate_Count_;
    END IF;



	 -- export basic view
    IF p_isExport = 10
    THEN
        SELECT
			AccessType AS `Access Type`,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS `One-Off Cost`,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS `Monthly Cost`,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS `Cost Per Call`,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS `Cost Per Minute`,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS `Surcharge Per Call`,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS `Surcharge Per Minute`,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS `Outpayment Per Call`,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS `Outpayment Per Minute`,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS `Surcharges`,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS `Chargeback`,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS `Collection Cost Amount`,
			CollectionCostPercentage AS `Collection Cost Percentage`,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS `Registration Cost Per Number`,
			EffectiveDate AS `Effective Date`,
			ApprovedStatus AS `Approved Status`
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	 -- export advance view
    IF p_isExport = 11
    THEN
        SELECT
        	AccessType AS `Access Type`,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
--			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,

         CONCAT(IF(CurrentOneOffCost IS NULL,'',CurrentOneOffCostCurrencySymbol),IFNULL(CurrentOneOffCost,'-'),'\n',IF(OneOffCost IS NULL,'',OneOffCostCurrencySymbol),IFNULL(OneOffCost,'-')) AS `One-Off Cost`,
			IF(OneOffCostMargin IS NULL, '-',OneOffCostMargin) AS `One-Off Cost Diff.`,
			IF(OneOffCostMarginPercent IS NULL, '-',CONCAT(OneOffCostMarginPercent,'%')) AS `One-Off Cost Diff. %`,

			CONCAT(IF(CurrentMonthlyCost IS NULL,'',CurrentMonthlyCostCurrencySymbol),IFNULL(CurrentMonthlyCost,'-'),'\n',IF(MonthlyCost IS NULL,'',MonthlyCostCurrencySymbol),IFNULL(MonthlyCost,'-')) AS `Monthly Cost`,
			IF(MonthlyCostMargin IS NULL, '-',MonthlyCostMargin) AS `Monthly Cost Diff.`,
			IF(MonthlyCostMarginPercent IS NULL, '-',CONCAT(MonthlyCostMarginPercent,'%')) AS `Monthly Cost Diff. %`,

			CONCAT(IF(CurrentCostPerCall IS NULL,'',CurrentCostPerCallCurrencySymbol),IFNULL(CurrentCostPerCall,'-'),'\n',IF(CostPerCall IS NULL,'',CostPerCallCurrencySymbol),IFNULL(CostPerCall,'-')) AS `Cost Per Call`,
			IF(CostPerCallMargin IS NULL, '-',CostPerCallMargin) AS `Cost Per Call Diff.`,
			IF(CostPerCallMarginPercent IS NULL, '-',CONCAT(CostPerCallMarginPercent,'%')) AS `Cost Per Call Diff. %`,

			CONCAT(IF(CurrentCostPerMinute IS NULL,'',CurrentCostPerMinuteCurrencySymbol),IFNULL(CurrentCostPerMinute,'-'),'\n',IF(CostPerMinute IS NULL,'',CostPerMinuteCurrencySymbol),IFNULL(CostPerMinute,'-')) AS `Cost Per Minute`,
			IF(CostPerMinuteMargin IS NULL, '-',CostPerMinuteMargin) AS `Cost Per Minute Diff.`,
			IF(CostPerMinuteMarginPercent IS NULL, '-',CONCAT(CostPerMinuteMarginPercent,'%')) AS `Cost Per Minute Diff. %`,

			CONCAT(IF(CurrentSurchargePerCall IS NULL,'',CurrentSurchargePerCallCurrencySymbol),IFNULL(CurrentSurchargePerCall,'-'),'\n',IF(SurchargePerCall IS NULL,'',SurchargePerCallCurrencySymbol),IFNULL(SurchargePerCall,'-')) AS `Surcharge Per Call`,
			IF(SurchargePerCallMargin IS NULL, '-',SurchargePerCallMargin) AS `Surcharge Per Call Diff.`,
			IF(SurchargePerCallMarginPercent IS NULL, '-',CONCAT(SurchargePerCallMarginPercent,'%')) AS `Surcharge Per Call Diff. %`,

			CONCAT(IF(CurrentSurchargePerMinute IS NULL,'',CurrentSurchargePerMinuteCurrencySymbol),IFNULL(CurrentSurchargePerMinute,'-'),'\n',IF(SurchargePerMinute IS NULL,'',SurchargePerMinuteCurrencySymbol),IFNULL(SurchargePerMinute,'-')) AS `Surcharge Per Minute`,
			IF(SurchargePerMinuteMargin IS NULL, '-',SurchargePerMinuteMargin) AS `Surcharge Per Minute Diff.`,
			IF(SurchargePerMinuteMarginPercent IS NULL, '-',CONCAT(SurchargePerMinuteMarginPercent,'%')) AS `Surcharge Per Minute Diff. %`,

			CONCAT(IF(CurrentOutpaymentPerCall IS NULL,'',CurrentOutpaymentPerCallCurrencySymbol),IFNULL(CurrentOutpaymentPerCall,'-'),'\n',IF(OutpaymentPerCall IS NULL,'',OutpaymentPerCallCurrencySymbol),IFNULL(OutpaymentPerCall,'-')) AS `Outpayment Per Call`,
			IF(OutpaymentPerCallMargin IS NULL, '-',OutpaymentPerCallMargin) AS `Outpayment Per Call Diff.`,
			IF(OutpaymentPerCallMarginPercent IS NULL, '-',CONCAT(OutpaymentPerCallMarginPercent,'%')) AS `Outpayment Per Call Diff. %`,

			CONCAT(IF(CurrentOutpaymentPerMinute IS NULL,'',CurrentOutpaymentPerMinuteCurrencySymbol),IFNULL(CurrentOutpaymentPerMinute,'-'),'\n',IF(OutpaymentPerMinute IS NULL,'',OutpaymentPerMinuteCurrencySymbol),IFNULL(OutpaymentPerMinute,'-')) AS `Outpayment Per Minute`,
			IF(OutpaymentPerMinuteMargin IS NULL, '-',OutpaymentPerMinuteMargin) AS `Outpayment Per Minute Diff.`,
			IF(OutpaymentPerMinuteMarginPercent IS NULL, '-',CONCAT(OutpaymentPerMinuteMarginPercent,'%')) AS `Outpayment Per Minute Diff. %`,

			CONCAT(IF(CurrentSurcharges IS NULL,'',CurrentSurchargesCurrencySymbol),IFNULL(CurrentSurcharges,'-'),'\n',IF(Surcharges IS NULL,'',SurchargesCurrencySymbol),IFNULL(Surcharges,'-')) AS `Surcharges`,
			IF(SurchargesMargin IS NULL, '-',SurchargesMargin) AS `Surcharges Diff.`,
			IF(SurchargesMarginPercent IS NULL, '-',CONCAT(SurchargesMarginPercent,'%')) AS `Surcharges Diff. %`,

			CONCAT(IF(CurrentChargeback IS NULL,'',CurrentChargebackCurrencySymbol),IFNULL(CurrentChargeback,'-'),'\n',IF(Chargeback IS NULL,'',ChargebackCurrencySymbol),IFNULL(Chargeback,'-')) AS `Chargeback`,
			IF(ChargebackMargin IS NULL, '-',ChargebackMargin) AS `Chargeback Diff.`,
			IF(ChargebackMarginPercent IS NULL, '-',CONCAT(ChargebackMarginPercent,'%')) AS `Chargeback Diff. %`,

			CONCAT(IF(CurrentCollectionCostAmount IS NULL,'',CurrentCollectionCostAmountCurrencySymbol),IFNULL(CurrentCollectionCostAmount,'-'),'\n',IF(CollectionCostAmount IS NULL,'',CollectionCostAmountCurrencySymbol),IFNULL(CollectionCostAmount,'-')) AS `Collection Cost Amount`,
			IF(CollectionCostAmountMargin IS NULL, '-',CollectionCostAmountMargin) AS `Collection Cost Amount Diff.`,
			IF(CollectionCostAmountMarginPercent IS NULL, '-',CONCAT(CollectionCostAmountMarginPercent,'%')) AS `Collection Cost Amount Diff. %`,

			CONCAT(IFNULL(CurrentCollectionCostPercentage,'-'),'\n',IFNULL(CollectionCostPercentage,'-')) AS `Collection Cost Percentage`,
			IF(CollectionCostPercentageMargin IS NULL, '-',CollectionCostPercentageMargin) AS `Collection Cost Percentage Diff.`,
			IF(CollectionCostPercentageMarginPercent IS NULL, '-',CONCAT(CollectionCostPercentageMarginPercent,'%')) AS `Collection Cost Percentage Diff. %`,

			CONCAT(IF(CurrentRegistrationCostPerNumber IS NULL,'',CurrentRegistrationCostPerNumberCurrencySymbol),IFNULL(CurrentRegistrationCostPerNumber,'-'),'\n',IF(RegistrationCostPerNumber IS NULL,'',RegistrationCostPerNumberCurrencySymbol),IFNULL(RegistrationCostPerNumber,'-')) AS `Registration Cost Per Number`,
			IF(RegistrationCostPerNumberMargin IS NULL, '-',RegistrationCostPerNumberMargin) AS `Registration Cost Per Number Diff.`,
			IF(RegistrationCostPerNumberMarginPercent IS NULL, '-',CONCAT(RegistrationCostPerNumberMarginPercent,'%')) AS `Registration Cost Per Number Diff. %`,

			EffectiveDate AS `Effective Date`,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`,
			ApprovedStatus AS `Approved Status`
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
	IN `p_lSortCol` VARCHAR(500),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;

	DECLARE v_Select_Count VARCHAR(500);
	DECLARE v_Select LONGTEXT;
	DECLARE v_common_query LONGTEXT;
	DECLARE v_Order_By LONGTEXT;
	DECLARE v_Sort_By LONGTEXT;
	DECLARE v_limit VARCHAR(50);

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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_Count_;
	CREATE TEMPORARY TABLE tmp_RateTablePKGRate_Count_ (
		`Count` INT
	);

	-- in order by we need to specify table name of columns to avoid error
	CASE
		WHEN p_lSortCol = 'Code' THEN SET v_Sort_By=CONCAT('tblRate.Code ',p_SortOrder);
		WHEN p_lSortCol = 'TimezoneTitle' THEN SET v_Sort_By=CONCAT('tblTimezones.Title ',p_SortOrder);
		WHEN p_lSortCol = 'ModifiedBy' THEN SET v_Sort_By=CONCAT('tblRateTablePKGRate.ModifiedBy ',p_SortOrder,',tblRateTablePKGRate.updated_at ',p_SortOrder);
		WHEN p_lSortCol = 'ApprovedBy' THEN SET v_Sort_By=CONCAT('tblRateTablePKGRate.ApprovedBy ',p_SortOrder,',tblRateTablePKGRate.ApprovedDate ',p_SortOrder);
	ELSE
		SET v_Sort_By = 'tblRate.Code';
	END CASE;


	-- we need order by and limit only in grid, we don't need it in export
	IF(p_isExport = 0)
	THEN
		SET v_Order_By = CONCAT('ORDER BY ',v_Sort_By,',RateTablePKGRateID ASC');
		SET v_limit 	= CONCAT('LIMIT ',p_RowspPage,' OFFSET ',v_OffSet_);
	ELSE
		SET v_Order_By = '';
		SET v_limit 	= '';
	END IF;


	SET v_common_query = CONCAT('
		FROM
			tblRateTablePKGRate
		INNER JOIN
			tblRate ON tblRateTablePKGRate.RateID = tblRate.RateID
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
			tblRateTablePKGRate.RateTableId = ',p_RateTableId,'
			AND (tblRate.CompanyID = ',p_companyid,')
			',IF(p_code IS NULL,'',CONCAT('AND tblRate.Code LIKE REPLACE("',p_code,'", "*", "%")')),'
			',IF(p_ApprovedStatus IS NULL,'',CONCAT('AND tblRateTablePKGRate.ApprovedStatus = ',p_ApprovedStatus)),'
			',IF(p_TimezonesID IS NULL,'',CONCAT('AND tblRateTablePKGRate.TimezonesID = ',p_TimezonesID)),'
			AND (
				"',p_effective,'" = "All"
				OR ("',p_effective,'" = "Now" AND EffectiveDate <= NOW() )
				OR ("',p_effective,'" = "Future" AND EffectiveDate > NOW())
			)
	');


	SET v_Select = '
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
		IFNULL(tblOneOffCostCurrency.Symbol,"") AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,"") AS MonthlyCostCurrencySymbol,
		IFNULL(tblPackageCostPerMinuteCurrency.Symbol,"") AS PackageCostPerMinuteCurrencySymbol,
		IFNULL(tblRecordingCostPerMinuteCurrency.Symbol,"") AS RecordingCostPerMinuteCurrencySymbol,
		tblRateTablePKGRate.TimezonesID
	';

	SET v_Select_Count = 'COUNT(tblRateTablePKGRate.RateTablePKGRateID)';

	-- final query from dynamic variables
	SET @stm_q_select = CONCAT('
		INSERT INTO tmp_RateTablePKGRate_
		SELECT ',v_Select,' ',v_common_query,' ',v_Order_By,' ',v_limit,' ;
	');
	-- select @stm_q_select; leave ThisSP;
	PREPARE stmt FROM @stm_q_select;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	-- count only need in view page, we don't need it when export
	IF(p_isExport = 0)
	THEN
		SET @stm_q_count = CONCAT('
			INSERT INTO tmp_RateTablePKGRate_Count_
			SELECT ',v_Select_Count,' ',v_common_query,';
		');

		-- select @stm_q_select,@stm_q_count; leave ThisSP;

		PREPARE stmt FROM @stm_q_count;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;


	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate4_ AS (SELECT * FROM tmp_RateTablePKGRate_);
		DELETE n1 FROM tmp_RateTablePKGRate_ n1, tmp_RateTablePKGRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		AND  n1.RateID = n2.RateID AND n1.TimezonesID = n2.TimezonesID;
	END IF;


	-- if request for grid not export
	IF p_isExport = 0
	THEN
		SELECT * FROM tmp_RateTablePKGRate_;
		SELECT `Count` AS totalcount FROM tmp_RateTablePKGRate_Count_;
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
	IN `p_Percentage` VARCHAR(550),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(500),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
ThisSP:BEGIN
	DECLARE v_OffSet_ INT;
	DECLARE v_Reseller_ INT;

	DECLARE v_Select_Count VARCHAR(500);
	DECLARE v_Select LONGTEXT;
	DECLARE v_common_query LONGTEXT;
	DECLARE v_Order_By LONGTEXT;
	DECLARE v_Sort_By LONGTEXT;
	DECLARE v_limit VARCHAR(50);
	DECLARE v_Where LONGTEXT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT Reseller INTO v_Reseller_ FROM tblRateTable WHERE CompanyID=p_companyid AND RateTableId=p_RateTableId;

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
		CurrentOneOffCost DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentMonthlyCost DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentPackageCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentRecordingCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
		CurrentOneOffCostCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentMonthlyCostCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentPackageCostPerMinuteCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentRecordingCostPerMinuteCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		OneOffCostMargin DECIMAL(18,6) NULL DEFAULT NULL,
		MonthlyCostMargin DECIMAL(18,6) NULL DEFAULT NULL,
		PackageCostPerMinuteMargin DECIMAL(18,6) NULL DEFAULT NULL,
		RecordingCostPerMinuteMargin DECIMAL(18,6) NULL DEFAULT NULL,
		OneOffCostMarginPercent DECIMAL(18,2) NULL DEFAULT NULL,
		MonthlyCostMarginPercent DECIMAL(18,2) NULL DEFAULT NULL,
		PackageCostPerMinuteMarginPercent DECIMAL(18,2) NULL DEFAULT NULL,
		RecordingCostPerMinuteMarginPercent DECIMAL(18,2) NULL DEFAULT NULL,
		INDEX tmp_RateTablePKGRate_RateID (`RateID`),
		INDEX `tmp_IX_PKGAA_RateID_TimezonesID_EffDate` (`RateID`, `TimezonesID`, `EffectiveDate`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_Count_;
	CREATE TEMPORARY TABLE tmp_RateTablePKGRate_Count_ (
		`Count` INT
	);

	-- in order by we need to specify table name of columns to avoid error
/*	CASE
		WHEN p_lSortCol = 'Code' THEN SET v_Sort_By=CONCAT('tblRate.Code ',p_SortOrder);
		WHEN p_lSortCol = 'TimezoneTitle' THEN SET v_Sort_By=CONCAT('tblTimezones.Title ',p_SortOrder);
		WHEN p_lSortCol = 'ModifiedBy' THEN SET v_Sort_By=CONCAT('tblRateTablePKGRate.ModifiedBy ',p_SortOrder,',tblRateTablePKGRate.updated_at ',p_SortOrder);
		WHEN p_lSortCol = 'ApprovedBy' THEN SET v_Sort_By=CONCAT('tblRateTablePKGRate.ApprovedBy ',p_SortOrder,',tblRateTablePKGRate.ApprovedDate ',p_SortOrder);
	ELSE
		SET v_Sort_By = 'Code ASC';
	END CASE;*/

	SET v_Sort_By = CONCAT(p_lSortCol,' ',p_SortOrder);

	IF(p_Percentage IS NOT NULL)
	THEN
		SET v_Where = CONCAT('WHERE OneOffCostMarginPercent ',p_Percentage,' OR MonthlyCostMarginPercent ',p_Percentage,' OR PackageCostPerMinuteMarginPercent ',p_Percentage,' OR RecordingCostPerMinuteMarginPercent ',p_Percentage);
	ELSE
		SET v_Where = '';
	END IF;

	-- we need order by and limit only in grid, we don't need it in export
	IF(p_isExport = 0)
	THEN
		SET v_Order_By = CONCAT('ORDER BY ',v_Sort_By);
		SET v_limit 	= CONCAT('LIMIT ',p_RowspPage,' OFFSET ',v_OffSet_);
	ELSE
		SET v_Order_By = '';
		SET v_limit 	= '';
	END IF;


	SET v_common_query = CONCAT('
		FROM
			tblRateTablePKGRateAA AS tblRateTablePKGRate
		INNER JOIN
			tblRate ON tblRateTablePKGRate.RateID = tblRate.RateID
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
			tblRateTablePKGRate.RateTableId = ',p_RateTableId,'
			AND (tblRate.CompanyID = ',p_companyid,')
			',IF(p_code IS NULL,'',CONCAT('AND tblRate.Code LIKE REPLACE("',p_code,'", "*", "%")')),'
			',IF(p_ApprovedStatus IS NULL,'',CONCAT('AND tblRateTablePKGRate.ApprovedStatus = ',p_ApprovedStatus)),'
			',IF(p_TimezonesID IS NULL,'',CONCAT('AND tblRateTablePKGRate.TimezonesID = ',p_TimezonesID)),'
			AND (
				"',p_effective,'" = "All"
				OR ("',p_effective,'" = "Now" AND EffectiveDate <= NOW() )
				OR ("',p_effective,'" = "Future" AND EffectiveDate > NOW())
			)
	');


	SET v_Select = '
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
		IFNULL(tblOneOffCostCurrency.Symbol,"") AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,"") AS MonthlyCostCurrencySymbol,
		IFNULL(tblPackageCostPerMinuteCurrency.Symbol,"") AS PackageCostPerMinuteCurrencySymbol,
		IFNULL(tblRecordingCostPerMinuteCurrency.Symbol,"") AS RecordingCostPerMinuteCurrencySymbol,
		tblRateTablePKGRate.TimezonesID
	';

	SET v_Select_Count = 'COUNT(tblRateTablePKGRate.RateTablePKGRateAAID)';

	-- final query from dynamic variables
	SET @stm_q_select = CONCAT('
		INSERT INTO tmp_RateTablePKGRate_
		(
			ID,
			TimezoneTitle,
			Code,
			OneOffCost,
			MonthlyCost,
			PackageCostPerMinute,
			RecordingCostPerMinute,
			EffectiveDate,
			EndDate,
			updated_at,
			ModifiedBy,
			RateTablePKGRateID,
			RateID,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			OneOffCostCurrency,
			MonthlyCostCurrency,
			PackageCostPerMinuteCurrency,
			RecordingCostPerMinuteCurrency,
			OneOffCostCurrencySymbol,
			MonthlyCostCurrencySymbol,
			PackageCostPerMinuteCurrencySymbol,
			RecordingCostPerMinuteCurrencySymbol,
			TimezonesID
		)
		SELECT ',v_Select,' ',v_common_query,' ;
	');	-- ' ',v_Order_By,' ',v_limit,
	-- select @stm_q_select; leave ThisSP;
	PREPARE stmt FROM @stm_q_select;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	-- count only need in view page, we don't need it when export
/*	IF(p_isExport = 0)
	THEN
		SET @stm_q_count = CONCAT('
			INSERT INTO tmp_RateTablePKGRate_Count_
			SELECT ',v_Select_Count,' ',v_common_query,';
		');

		-- select @stm_q_select,@stm_q_count; leave ThisSP;

		PREPARE stmt FROM @stm_q_count;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;	*/


	IF p_effective = 'Now'
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate4_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate4_ LIKE tmp_RateTablePKGRate_;
		INSERT INTO tmp_RateTablePKGRate4_ SELECT * FROM tmp_RateTablePKGRate_;

		DELETE n1 FROM tmp_RateTablePKGRate_ n1, tmp_RateTablePKGRate4_ n2 WHERE n1.RateID = n2.RateID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate < n2.EffectiveDate;
	END IF;


	-- update current rate, margin and margin percentage
	UPDATE
		tmp_RateTablePKGRate_ tr
	LEFT JOIN
		tblRateTablePKGRate rtr ON rtr.RateTablePKGRateID = ( SELECT tmp.RateTablePKGRateID FROM tblRateTablePKGRate tmp INNER JOIN tblRateTable tmp_r ON tmp.RateTableId=tmp_r.RateTableId WHERE tmp.RateID=tr.RateID AND tmp.TimezonesID = tr.TimezonesID AND ((tr.EffectiveDate < CURDATE() AND tmp.EffectiveDate <= CURDATE()) OR tmp.EffectiveDate<=tr.EffectiveDate) AND tmp_r.Reseller=v_Reseller_ ORDER BY EffectiveDate DESC,RateTablePKGRateID DESC LIMIT 1 )
	LEFT JOIN
		tblCurrency OneOffCostCurrency ON OneOffCostCurrency.CurrencyID=rtr.OneOffCostCurrency
	LEFT JOIN
		tblCurrency MonthlyCostCurrency ON MonthlyCostCurrency.CurrencyID=rtr.MonthlyCostCurrency
	LEFT JOIN
		tblCurrency PackageCostPerMinuteCurrency ON PackageCostPerMinuteCurrency.CurrencyID=rtr.PackageCostPerMinuteCurrency
	LEFT JOIN
		tblCurrency RecordingCostPerMinuteCurrency ON RecordingCostPerMinuteCurrency.CurrencyID=rtr.RecordingCostPerMinuteCurrency
	SET
		CurrentOneOffCost = rtr.OneOffCost,
		CurrentMonthlyCost = rtr.MonthlyCost,
		CurrentPackageCostPerMinute = rtr.PackageCostPerMinute,
		CurrentRecordingCostPerMinute = rtr.RecordingCostPerMinute,
		CurrentOneOffCostCurrencySymbol = OneOffCostCurrency.Symbol,
		CurrentMonthlyCostCurrencySymbol = MonthlyCostCurrency.Symbol,
		CurrentPackageCostPerMinuteCurrencySymbol = PackageCostPerMinuteCurrency.Symbol,
		CurrentRecordingCostPerMinuteCurrencySymbol = RecordingCostPerMinuteCurrency.Symbol,
		OneOffCostMargin = (tr.OneOffCost - rtr.OneOffCost),
		MonthlyCostMargin = (tr.MonthlyCost - rtr.MonthlyCost),
		PackageCostPerMinuteMargin = (tr.PackageCostPerMinute - rtr.PackageCostPerMinute),
		RecordingCostPerMinuteMargin = (tr.RecordingCostPerMinute - rtr.RecordingCostPerMinute),
		OneOffCostMarginPercent = ROUND(
			IF(
				(IFNULL(tr.OneOffCost,0) - IFNULL(rtr.OneOffCost,0)) <> 0,
				(((IFNULL(tr.OneOffCost,0) - IFNULL(rtr.OneOffCost,0)) * 100) / rtr.OneOffCost),
				NULL
			)
			,2
		),
		MonthlyCostMarginPercent = ROUND(
			IF(
				(IFNULL(tr.MonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) <> 0,
				(((IFNULL(tr.MonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) * 100) / rtr.MonthlyCost),
				NULL
			)
			,2
		),
		PackageCostPerMinuteMarginPercent = ROUND(
			IF(
				(IFNULL(tr.PackageCostPerMinute,0) - IFNULL(rtr.PackageCostPerMinute,0)) <> 0,
				(((IFNULL(tr.PackageCostPerMinute,0) - IFNULL(rtr.PackageCostPerMinute,0)) * 100) / rtr.PackageCostPerMinute),
				NULL
			)
			,2
		),
		RecordingCostPerMinuteMarginPercent = ROUND(
			IF(
				(IFNULL(tr.RecordingCostPerMinute,0) - IFNULL(rtr.RecordingCostPerMinute,0)) <> 0,
				(((IFNULL(tr.RecordingCostPerMinute,0) - IFNULL(rtr.RecordingCostPerMinute,0)) * 100) / rtr.RecordingCostPerMinute),
				NULL
			)
			,2
		);

	-- if request for grid not export
	IF p_isExport = 0
	THEN
		SET @stm_final_select = CONCAT('
			SELECT * FROM tmp_RateTablePKGRate_ ', v_Where,' ',v_Order_By,' ',v_limit,';
		');
		PREPARE stmt FROM @stm_final_select;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		SET @stm_final_select_count = CONCAT('
			SELECT count(*) AS totalcount FROM tmp_RateTablePKGRate_ ', v_Where,';
		');
		PREPARE stmt FROM @stm_final_select_count;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

--		SELECT `Count` AS totalcount FROM tmp_RateTablePKGRate_Count_;
	END IF;

	-- export
	IF p_isExport = 1
	THEN
		SELECT
			TimezoneTitle AS `Time of Day`,
			Code AS `Package Name`,

			CONCAT(IF(CurrentOneOffCost IS NULL,'',CurrentOneOffCostCurrencySymbol),IFNULL(CurrentOneOffCost,'-'),'\n',IF(OneOffCost IS NULL,'',OneOffCostCurrencySymbol),IFNULL(OneOffCost,'-')) AS `One-Off Cost`,
			IF(OneOffCostMargin IS NULL, '-',OneOffCostMargin) AS `One-Off Cost Diff.`,
			IF(OneOffCostMarginPercent IS NULL, '-',CONCAT(OneOffCostMarginPercent,'%')) AS `One-Off Cost Diff. %`,

			CONCAT(IF(CurrentMonthlyCost IS NULL,'',CurrentMonthlyCostCurrencySymbol),IFNULL(CurrentMonthlyCost,'-'),'\n',IF(MonthlyCost IS NULL,'',MonthlyCostCurrencySymbol),IFNULL(MonthlyCost,'-')) AS `Monthly Cost`,
			IF(MonthlyCostMargin IS NULL, '-',MonthlyCostMargin) AS `Monthly Cost Diff.`,
			IF(MonthlyCostMarginPercent IS NULL, '-',CONCAT(MonthlyCostMarginPercent,'%')) AS `Monthly Cost Diff. %`,

			CONCAT(IF(CurrentPackageCostPerMinute IS NULL,'',CurrentPackageCostPerMinuteCurrencySymbol),IFNULL(CurrentPackageCostPerMinute,'-'),'\n',IF(PackageCostPerMinute IS NULL,'',PackageCostPerMinuteCurrencySymbol),IFNULL(PackageCostPerMinute,'-')) AS `Package Cost Per Minute`,
			IF(PackageCostPerMinuteMargin IS NULL, '-',PackageCostPerMinuteMargin) AS `Package Cost Per Minute Diff.`,
			IF(PackageCostPerMinuteMarginPercent IS NULL, '-',CONCAT(PackageCostPerMinuteMarginPercent,'%')) AS `Package Cost Per Minute Diff. %`,

			CONCAT(IF(CurrentRecordingCostPerMinute IS NULL,'',CurrentRecordingCostPerMinuteCurrencySymbol),IFNULL(CurrentRecordingCostPerMinute,'-'),'\n',IF(RecordingCostPerMinute IS NULL,'',RecordingCostPerMinuteCurrencySymbol),IFNULL(RecordingCostPerMinute,'-')) AS `Recording Cost Per Minute`,
			IF(RecordingCostPerMinuteMargin IS NULL, '-',RecordingCostPerMinuteMargin) AS `Recording Cost Per Minute Diff.`,
			IF(RecordingCostPerMinuteMarginPercent IS NULL, '-',CONCAT(RecordingCostPerMinuteMarginPercent,'%')) AS `Recording Cost Per Minute Diff. %`,

--			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			EffectiveDate AS `Effective Date`,
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
	IN `p_lSortCol` VARCHAR(500),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
ThisSP:BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_ROUTING_PROFILE_ INT;
	DECLARE v_RateApprovalProcess_ INT;
	DECLARE v_AppliedTo_ INT;

	DECLARE v_Select_Count VARCHAR(500);
	DECLARE v_Select LONGTEXT;
	DECLARE v_From LONGTEXT;
	DECLARE v_Join_Rate LONGTEXT;
	DECLARE v_Join_ORate LONGTEXT;
	DECLARE v_Join_Timezone LONGTEXT;
	DECLARE v_Join_RC LONGTEXT;
	DECLARE v_Where LONGTEXT;
	DECLARE v_Order_By LONGTEXT;
	DECLARE v_Group_By LONGTEXT;
	DECLARE v_Sort_By LONGTEXT;
	DECLARE v_limit VARCHAR(50);


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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_Count_;
	CREATE TEMPORARY TABLE tmp_RateTableRate_Count_ (
		`Count` INT
	);

	-- in order by we need to specify table name of columns to avoid error
	CASE
		WHEN p_lSortCol = 'DestinationType' THEN SET v_Sort_By=CONCAT('tblRate.Type',' ',p_SortOrder);
		WHEN p_lSortCol = 'TimezoneTitle' THEN SET v_Sort_By=CONCAT('tblTimezones.Title',' ',p_SortOrder);
		WHEN p_lSortCol = 'OriginationCode' THEN SET v_Sort_By=CONCAT('OriginationRate.Code',' ',p_SortOrder);
		WHEN p_lSortCol = 'OriginationDescription' THEN SET v_Sort_By=CONCAT('OriginationRate.Description',' ',p_SortOrder);
		WHEN p_lSortCol = 'Code' THEN SET v_Sort_By=CONCAT('tblRate.Code',' ',p_SortOrder);
		WHEN p_lSortCol = 'Description' THEN SET v_Sort_By=CONCAT('tblRate.Description',' ',p_SortOrder);
		WHEN p_lSortCol = 'EffectiveDate' THEN SET v_Sort_By=CONCAT('tblRateTableRate.EffectiveDate',' ',p_SortOrder);
		WHEN p_lSortCol = 'RoutingCategoryName' THEN SET v_Sort_By=CONCAT('RC.Name',' ',p_SortOrder);
		WHEN p_lSortCol = 'ModifiedBy' THEN SET v_Sort_By=CONCAT('tblRateTableRate.ModifiedBy ',p_SortOrder,',tblRateTableRate.updated_at ',p_SortOrder);
		WHEN p_lSortCol = 'ApprovedBy' THEN SET v_Sort_By=CONCAT('tblRateTableRate.ApprovedBy ',p_SortOrder,',tblRateTableRate.ApprovedDate ',p_SortOrder);
	ELSE
		SET v_Sort_By = 'tblRate.Code ASC';
	END CASE;

	-- we need order by and limit only in grid, we don't need it in export
	IF(p_isExport = 0)
	THEN
		SET v_Order_By = CONCAT('ORDER BY ',v_Sort_By);
		SET v_limit 	= CONCAT('LIMIT ',p_RowspPage,' OFFSET ',v_OffSet_);
	ELSE
		SET v_Order_By = '';
		SET v_limit 	= '';
	END IF;

	SET v_From = '
		FROM
			tblRateTableRate';

	SET v_Join_Rate = CONCAT('
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableRate.RateID AND tblRate.CompanyID = ',p_companyid);

	SET v_Join_ORate = CONCAT('
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.RateID = tblRateTableRate.OriginationRateID AND OriginationRate.CompanyID = ',p_companyid);

	SET v_Join_Timezone = '
		INNER JOIN
			tblTimezones ON tblTimezones.TimezonesID = tblRateTableRate.TimezonesID';

	SET v_Join_RC = '
		LEFT JOIN speakintelligentRouting.tblRoutingCategory AS RC
			ON RC.RoutingCategoryID = tblRateTableRate.RoutingCategoryID';

	SET v_Where = CONCAT('
		WHERE
			tblRateTableRate.RateTableId = ',p_RateTableId,'
			',IF(p_contryID IS NULL,'',CONCAT('AND tblRate.CountryID = ',p_contryID)),'
			',IF(p_origination_code IS NULL,'',CONCAT('AND OriginationRate.Code LIKE REPLACE("',p_origination_code,'", "*", "%")')),'
			',IF(p_origination_description IS NULL,'',CONCAT('AND OriginationRate.Description LIKE REPLACE("',p_origination_description,'", "*", "%")')),'
			',IF(p_code IS NULL,'',CONCAT('AND tblRate.Code LIKE REPLACE("',p_code,'", "*", "%")')),'
			',IF(p_description IS NULL,'',CONCAT('AND tblRate.Description LIKE REPLACE("',p_description,'", "*", "%")')),'
			',IF(p_RoutingCategoryID IS NULL,'',CONCAT('AND RC.RoutingCategoryID = ',p_RoutingCategoryID)),'
			',IF(p_Preference IS NULL,'',CONCAT('AND tblRateTableRate.Preference = ',p_Preference)),'
			',IF(p_Blocked IS NULL,'',CONCAT('AND tblRateTableRate.Blocked = ',p_Blocked)),'
			',IF(p_ApprovedStatus IS NULL,'',CONCAT('AND tblRateTableRate.ApprovedStatus = ',p_ApprovedStatus)),'
			',IF(p_TimezonesID IS NULL,'',CONCAT('AND tblRateTableRate.TimezonesID = ',p_TimezonesID)),'
			AND (
				"',p_effective,'" = "All"
				OR ("',p_effective,'" = "Now" AND EffectiveDate <= NOW() )
				OR ("',p_effective,'" = "Future" AND EffectiveDate > NOW())
			)
	');

	IF p_view = 1
	THEN
		-- if group by code view
		SET v_Select = '
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
			tblRateTableRate.RateCurrency AS RateCurrency,
			tblRateTableRate.ConnectionFeeCurrency AS ConnectionFeeCurrency,
			NULL AS RateCurrencySymbol,
			NULL AS ConnectionFeeCurrencySymbol,
			tblRateTableRate.TimezonesID
		';

		SET v_Group_By = '';

		SET v_Select_Count = 'COUNT(tblRateTableRate.RateTableRateID)';

	ELSE
		-- if group by description view
		-- this is not in working condition as SI is not using it, if they need we will need to implement this properly
		-- this is not implemented right now as of 2019-09-06 while rebuilding this whole procedure, as suggested sumera (not important)
		SET v_Select = '
			group_concat(RateTableRateID) AS ID,
			MAX(tblRate.Type) AS DestinationType,
			MAX(tblTimezones.Title) AS TimezoneTitle,
			group_concat(OriginationRate.Code) AS OriginationCode,
			OriginationRate.Description AS OriginationDescription,
			group_concat(tblRate.Code) AS Code,
			MAX(tblRate.Description) AS Description,
			IFNULL(tblRateTableRate.MinimumDuration,0) AS MinimumDuration,
			IFNULL(tblRateTableRate.Interval1,1) AS Interval1,
			IFNULL(tblRateTableRate.IntervalN,1) AS IntervalN,
			tblRateTableRate.ConnectionFee AS ConnectionFee,
			NULL AS PreviousRate,
			IFNULL(tblRateTableRate.Rate, 0) AS Rate,
			MAX(IFNULL(tblRateTableRate.RateN, 0)) AS RateN,
			IFNULL(tblRateTableRate.EffectiveDate, NOW()) AS EffectiveDate,
			MAX(tblRateTableRate.EndDate),
			MAX(tblRateTableRate.updated_at) AS updated_at,
			MAX(tblRateTableRate.ModifiedBy) AS ModifiedBy,
			group_concat(RateTableRateID) AS RateTableRateID,
			group_concat(OriginationRate.RateID) AS OriginationRateID,
			group_concat(tblRate.RateID) AS RateID,
			MAX(tblRateTableRate.RoutingCategoryID) AS RoutingCategoryID,
			MAX(RC.Name) AS RoutingCategoryName,
			MAX(tblRateTableRate.Preference) AS Preference,
			MAX(tblRateTableRate.Blocked) AS Blocked,
			ApprovedStatus,
			MAX(tblRateTableRate.ApprovedBy) AS ApprovedBy,
			MAX(tblRateTableRate.ApprovedDate) AS ApprovedDate,
			MAX(tblRateCurrency.CurrencyID) AS RateCurrency,
			MAX(tblConnectionFeeCurrency.CurrencyID) AS ConnectionFeeCurrency,
			MAX(IFNULL(tblRateCurrency.Symbol,"")) AS RateCurrencySymbol,
			MAX(IFNULL(tblConnectionFeeCurrency.Symbol,"")) AS ConnectionFeeCurrencySymbol,
			tblRateTableRate.TimezonesID AS TimezonesID
		';

		SET v_Group_By = 'GROUP BY tblRate.Description, OriginationRate.Description, tblRateTableRate.MinimumDuration, tblRateTableRate.Interval1, tblRateTableRate.IntervalN, tblRateTableRate.ConnectionFee, tblRateTableRate.Rate, tblRateTableRate.EffectiveDate, tblRateTableRate.ApprovedStatus, tblRateTableRate.TimezonesID';

		SET v_Select_Count = 'COUNT(tblRate.Description)';

	END IF;

	-- final query from dynamic variables
	SET @stm_q_select = CONCAT('
		INSERT INTO tmp_RateTableRate_
		(
			ID,
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
			ModifiedBy,
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
		)
		SELECT ',v_Select,' ',v_From,' ',v_Join_Rate,' ',v_Join_ORate,' ',v_Join_Timezone,' ',v_Join_RC,' ',v_Where,' ',v_Group_By,' ',v_Order_By,' ',v_limit,' ;
	');
	-- select @stm_q_select; leave ThisSP;
	PREPARE stmt FROM @stm_q_select;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	-- count only need in view page, we don't need it when export
	IF(p_isExport = 0)
	THEN
		SET @stm_q_count = CONCAT('
			INSERT INTO tmp_RateTableRate_Count_
			SELECT ',v_Select_Count,' ',v_From,' ',IF(p_code IS NULL AND p_description IS NULL,'',v_Join_Rate),' ',IF(p_origination_code IS NULL AND p_origination_description IS NULL,'',v_Join_ORate),' ',IF(p_TimezonesID IS NULL,'',v_Join_Timezone),' ',IF(p_RoutingCategoryID IS NULL,'',v_Join_RC),' ',v_Where,' ',v_Group_By,';
		');

		-- select @stm_q_select,@stm_q_count; leave ThisSP;

		PREPARE stmt FROM @stm_q_count;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;


	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
		DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
			AND n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID;
	END IF;

	-- update rate currency, connection fee currency symbols
	UPDATE
		tmp_RateTableRate_ tr
	INNER JOIN
		tblRateTableRate ON tblRateTableRate.RateTableRateID = tr.ID
	LEFT JOIN
		tblCurrency AS RateCurrency ON RateCurrency.CurrencyID = tblRateTableRate.RateCurrency
	LEFT JOIN
		tblCurrency AS ConnectionFeeCurrency ON ConnectionFeeCurrency.CurrencyID = tblRateTableRate.ConnectionFeeCurrency
	SET
		ConnectionFeeCurrencySymbol = IFNULL(ConnectionFeeCurrency.Symbol,""),
		RateCurrencySymbol = IFNULL(RateCurrency.Symbol,"");


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

	-- if request for grid not export
	IF p_isExport = 0
	THEN
		SELECT * FROM tmp_RateTableRate_;
		SELECT `Count` AS totalcount FROM tmp_RateTableRate_Count_;
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
	IN `p_lSortCol` VARCHAR(500),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
ThisSP:BEGIN
	DECLARE v_OffSet_ INT;
	DECLARE v_Reseller_ INT;

	DECLARE v_Select_Count VARCHAR(500);
	DECLARE v_Select LONGTEXT;
	DECLARE v_From LONGTEXT;
	DECLARE v_Join_Rate LONGTEXT;
	DECLARE v_Join_ORate LONGTEXT;
	DECLARE v_Join_Timezone LONGTEXT;
	DECLARE v_Where LONGTEXT;
	DECLARE v_Order_By LONGTEXT;
	DECLARE v_Sort_By LONGTEXT;
	DECLARE v_limit VARCHAR(50);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET SESSION GROUP_CONCAT_MAX_LEN = 1000000;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT Reseller INTO v_Reseller_ FROM tblRateTable WHERE CompanyID=p_companyid AND RateTableId=p_RateTableId;

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
		ConnectionFeeCurrency INT(11),
		RateCurrency INT(11),
		ConnectionFeeCurrencySymbol VARCHAR(255),
		RateCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		CurrentConnectionFee DECIMAL(18, 6) NULL DEFAULT NULL,
		CurrentRate DECIMAL(18, 6) NULL DEFAULT NULL,
		CurrentRateN DECIMAL(18, 6) NULL DEFAULT NULL,
		CurrentConnectionFeeCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		CurrentRateCurrencySymbol VARCHAR(255) NULL DEFAULT NULL,
		ConnectionFeeMargin VARCHAR(255) NULL DEFAULT NULL,
		RateMargin VARCHAR(255) NULL DEFAULT NULL,
		RateNMargin VARCHAR(255) NULL DEFAULT NULL,
		ConnectionFeeMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		RateMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		RateNMarginPercent VARCHAR(255) NULL DEFAULT NULL,
		INDEX `IX_tmpIAA_RateID_ORateID_TmzID_EffDate` (`RateID`, `OriginationRateID`, `TimezonesID`, `EffectiveDate`),
		INDEX `IX_tmpIAA_ID` (`ID`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_Count_;
	CREATE TEMPORARY TABLE tmp_RateTableRate_Count_ (
		`Count` INT
	);

	-- in order by we need to specify table name of columns to avoid error
	CASE
		WHEN p_lSortCol = 'DestinationType' THEN SET v_Sort_By=CONCAT('tblRate.Type',' ',p_SortOrder);
		WHEN p_lSortCol = 'TimezoneTitle' THEN SET v_Sort_By=CONCAT('tblTimezones.Title',' ',p_SortOrder);
		WHEN p_lSortCol = 'OriginationCode' THEN SET v_Sort_By=CONCAT('OriginationRate.Code',' ',p_SortOrder);
		WHEN p_lSortCol = 'OriginationDescription' THEN SET v_Sort_By=CONCAT('OriginationRate.Description',' ',p_SortOrder);
		WHEN p_lSortCol = 'Code' THEN SET v_Sort_By=CONCAT('tblRate.Code',' ',p_SortOrder);
		WHEN p_lSortCol = 'Description' THEN SET v_Sort_By=CONCAT('tblRate.Description',' ',p_SortOrder);
		WHEN p_lSortCol = 'EffectiveDate' THEN SET v_Sort_By=CONCAT('tblRateTableRate.EffectiveDate',' ',p_SortOrder);
		WHEN p_lSortCol = 'RoutingCategoryName' THEN SET v_Sort_By=CONCAT('RC.Name',' ',p_SortOrder);
		WHEN p_lSortCol = 'ModifiedBy' THEN SET v_Sort_By=CONCAT('tblRateTableRate.ModifiedBy ',p_SortOrder,',tblRateTableRate.updated_at ',p_SortOrder);
		WHEN p_lSortCol = 'ApprovedBy' THEN SET v_Sort_By=CONCAT('tblRateTableRate.ApprovedBy ',p_SortOrder,',tblRateTableRate.ApprovedDate ',p_SortOrder);
	ELSE
		SET v_Sort_By = 'tblRate.Code ASC';
	END CASE;

	-- we need order by and limit only in grid, we don't need it in export
	IF(p_isExport = 0)
	THEN
		SET v_Order_By = CONCAT('ORDER BY ',v_Sort_By);
		SET v_limit 	= CONCAT('LIMIT ',p_RowspPage,' OFFSET ',v_OffSet_);
	ELSE
		SET v_Order_By = '';
		SET v_limit 	= '';
	END IF;

	SET v_From = '
		FROM
			tblRateTableRateAA AS tblRateTableRate';

	SET v_Join_Rate = CONCAT('
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableRate.RateID AND tblRate.CompanyID = ',p_companyid);

	SET v_Join_ORate = CONCAT('
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.RateID = tblRateTableRate.OriginationRateID AND OriginationRate.CompanyID = ',p_companyid);

	SET v_Join_Timezone = '
		INNER JOIN
			tblTimezones ON tblTimezones.TimezonesID = tblRateTableRate.TimezonesID';

	SET v_Where = CONCAT('
		WHERE
			tblRateTableRate.RateTableId = ',p_RateTableId,'
			',IF(p_contryID IS NULL,'',CONCAT('AND tblRate.CountryID = ',p_contryID)),'
			',IF(p_origination_code IS NULL,'',CONCAT('AND OriginationRate.Code LIKE REPLACE("',p_origination_code,'", "*", "%")')),'
			',IF(p_origination_description IS NULL,'',CONCAT('AND OriginationRate.Description LIKE REPLACE("',p_origination_description,'", "*", "%")')),'
			',IF(p_code IS NULL,'',CONCAT('AND tblRate.Code LIKE REPLACE("',p_code,'", "*", "%")')),'
			',IF(p_description IS NULL,'',CONCAT('AND tblRate.Description LIKE REPLACE("',p_description,'", "*", "%")')),'
			',IF(p_ApprovedStatus IS NULL,'',CONCAT('AND tblRateTableRate.ApprovedStatus = ',p_ApprovedStatus)),'
			',IF(p_TimezonesID IS NULL,'',CONCAT('AND tblRateTableRate.TimezonesID = ',p_TimezonesID)),'
			AND (
				"',p_effective,'" = "All"
				OR ("',p_effective,'" = "Now" AND EffectiveDate <= NOW() )
				OR ("',p_effective,'" = "Future" AND EffectiveDate > NOW())
			)
	');


	-- if select columns
	SET v_Select = '
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
		RateTableRateAAID,
		IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
		tblRate.RateID,
		tblRateTableRate.RoutingCategoryID,
		"" AS RoutingCategoryName,
		tblRateTableRate.Preference,
		tblRateTableRate.Blocked,
		tblRateTableRate.ApprovedStatus,
		tblRateTableRate.ApprovedBy,
		tblRateTableRate.ApprovedDate,
		tblRateTableRate.ConnectionFeeCurrency AS ConnectionFeeCurrency,
		tblRateTableRate.RateCurrency AS RateCurrency,
		NULL AS ConnectionFeeCurrencySymbol,
		NULL AS RateCurrencySymbol,
		tblRateTableRate.TimezonesID
	';

	SET v_Select_Count = 'COUNT(tblRateTableRate.RateTableRateAAID)';

	-- final query from dynamic variables
	SET @stm_q_select = CONCAT('
		INSERT INTO tmp_RateTableRate_
		(
			ID,
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
			ModifiedBy,
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
			ConnectionFeeCurrency,
			RateCurrency,
			ConnectionFeeCurrencySymbol,
			RateCurrencySymbol,
			TimezonesID
		)
		SELECT ',v_Select,' ',v_From,' ',v_Join_Rate,' ',v_Join_ORate,' ',v_Join_Timezone,' ',v_Where,' ',v_Order_By,' ',v_limit,' ;
	');
	-- select @stm_q_select; leave ThisSP;
	PREPARE stmt FROM @stm_q_select;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	-- count only need in view page, we don't need it when export
	IF(p_isExport = 0)
	THEN
		-- count query from dynamic variables
		SET @stm_q_count = CONCAT('
			INSERT INTO tmp_RateTableRate_Count_
			SELECT ',v_Select_Count,' ',v_From,' ',IF(p_code IS NULL AND p_description IS NULL,'',v_Join_Rate),' ',IF(p_origination_code IS NULL AND p_origination_description IS NULL,'',v_Join_ORate),' ',IF(p_TimezonesID IS NULL,'',v_Join_Timezone),' ',v_Where,';
		');

		-- select @stm_q_select,@stm_q_count; leave ThisSP;

		PREPARE stmt FROM @stm_q_count;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;

	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
		DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2
		WHERE n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate < n2.EffectiveDate;
	END IF;


	-- update rate currency, connection fee currency symbols, timezone title
	UPDATE
		tmp_RateTableRate_ tr
	INNER JOIN
		tblRateTableRateAA AS tblRateTableRate ON tblRateTableRate.RateTableRateAAID = tr.ID
	LEFT JOIN
		tblCurrency AS RateCurrency ON RateCurrency.CurrencyID = tblRateTableRate.RateCurrency
	LEFT JOIN
		tblCurrency AS ConnectionFeeCurrency ON ConnectionFeeCurrency.CurrencyID = tblRateTableRate.ConnectionFeeCurrency
	SET
		ConnectionFeeCurrencySymbol = IFNULL(ConnectionFeeCurrency.Symbol,""),
		RateCurrencySymbol = IFNULL(RateCurrency.Symbol,"");


	-- update current rate, margin and margin percentage
	UPDATE
		tmp_RateTableRate_ tr
	LEFT JOIN
		tblRateTableRate rtr ON rtr.RateTableRateID = ( SELECT tmp.RateTableRateID FROM tblRateTableRate tmp INNER JOIN tblRateTable tmp_r ON tmp.RateTableId=tmp_r.RateTableId WHERE tmp_r.Reseller=v_Reseller_ AND tmp.TimezonesID = tr.TimezonesID AND tmp.RateID=tr.RateID AND tmp.OriginationRateID=tr.OriginationRateID AND ((tr.EffectiveDate < CURDATE() AND tmp.EffectiveDate <= CURDATE()) OR tmp.EffectiveDate<=tr.EffectiveDate) ORDER BY EffectiveDate DESC,RateTableRateID DESC LIMIT 1 )
	LEFT JOIN
		tblCurrency AS CurrentRateCurrency ON CurrentRateCurrency.CurrencyID=rtr.RateCurrency
	LEFT JOIN
		tblCurrency AS CurrentConnectionFeeCurrency ON CurrentConnectionFeeCurrency.CurrencyID=rtr.ConnectionFeeCurrency
	SET
		CurrentConnectionFee = rtr.ConnectionFee,
		CurrentRate = rtr.Rate,
		CurrentRateN = rtr.RateN,
		CurrentConnectionFeeCurrencySymbol = IFNULL(CurrentConnectionFeeCurrency.Symbol,""),
		CurrentRateCurrencySymbol = IFNULL(CurrentRateCurrency.Symbol,""),
		ConnectionFeeMargin = (tr.ConnectionFee - rtr.ConnectionFee),
		RateMargin = (tr.Rate - rtr.Rate),
		RateNMargin = (tr.RateN - rtr.RateN),
		ConnectionFeeMarginPercent = ROUND(
			IF(
				(IFNULL(tr.ConnectionFee,0) - IFNULL(rtr.ConnectionFee,0)) <> 0,
				(((IFNULL(tr.ConnectionFee,0) - IFNULL(rtr.ConnectionFee,0)) * 100) / rtr.ConnectionFee),
				NULL
			)
			,2
		),
		RateMarginPercent = ROUND(
			IF(
				(IFNULL(tr.Rate,0) - IFNULL(rtr.Rate,0)) <> 0,
				(((IFNULL(tr.Rate,0) - IFNULL(rtr.Rate,0)) * 100) / rtr.Rate),
				NULL
			)
			,2
		),
		RateNMarginPercent = ROUND(
			IF(
				(IFNULL(tr.RateN,0) - IFNULL(rtr.RateN,0)) <> 0,
				(((IFNULL(tr.RateN,0) - IFNULL(rtr.RateN,0)) * 100) / rtr.RateN),
				NULL
			)
			,2
		)
	/*WHERE
		rtr.RateTableID=p_RateTableId*/;


	-- if request for grid not export
	IF p_isExport = 0
	THEN
		SELECT * FROM tmp_RateTableRate_;
		SELECT `Count` AS totalcount FROM tmp_RateTableRate_Count_;
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
				CONCAT(Interval1,'/',IntervalN) AS `Interval1/N`
		";

		SET @stm3 = ", EffectiveDate AS `Effective Date`";

		-- advance view
		IF p_isExport = 11
		THEN
			SET @stm2 = ",
				CONCAT(IF(CurrentConnectionFee IS NULL,'',CurrentConnectionFeeCurrencySymbol),IFNULL(CurrentConnectionFee,'-'),'\n',IF(ConnectionFee IS NULL,'',ConnectionFeeCurrencySymbol),IFNULL(ConnectionFee,'-')) AS `Connection Fee`,
				IF(ConnectionFeeMargin IS NULL, '-',ConnectionFeeMargin) AS `Connection Fee Diff.`,
				IF(ConnectionFeeMarginPercent IS NULL, '-',CONCAT(ConnectionFeeMarginPercent,'%')) AS `Connection Fee Diff. %`,

				CONCAT(CurrentRateCurrencySymbol,IFNULL(CurrentRate,'-'),'\n',RateCurrencySymbol,IFNULL(Rate,'-')) AS `Rate`,
				IF(RateMargin IS NULL, '-',RateMargin) AS `Rate Diff.`,
				IF(RateMarginPercent IS NULL, '-',CONCAT(RateMarginPercent,'%')) AS `Rate Diff. %`,

				CONCAT(CurrentRateCurrencySymbol,IFNULL(CurrentRateN,'-'),'\n',RateCurrencySymbol,IFNULL(RateN,'-')) AS `RateN`,
				IF(RateNMargin IS NULL, '-',RateNMargin) AS `RateN Diff.`,
				IF(RateNMarginPercent IS NULL, '-',CONCAT(RateNMarginPercent,'%')) AS `RateN Diff. %`
			";
			-- SET @stm4 = ", PreviousRate AS `Previous Rate`, CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`";
			SET @stm4 = ", CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`";
			SET @stm4 = CONCAT(@stm4,", CONCAT(IFNULL(ApprovedBy,''),'\n',IFNULL(ApprovedDate,'')) AS `Approved By/Date`, ApprovedStatus");
		ELSE
			SET @stm2 = ",
				CONCAT(ConnectionFeeCurrencySymbol,ConnectionFee) AS `Connection Fee`,
				CONCAT(RateCurrencySymbol,Rate) AS Rate,
				CONCAT(RateCurrencySymbol,RateN) AS RateN
			";
		END IF;

		SET @stm = CONCAT(@stm1,@stm2,@stm3,@stm4,' FROM tmp_RateTableRate_;');

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
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeDESC') THEN ANY_VALUE(DestinationType)
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationTypeASC') THEN ANY_VALUE(DestinationType)
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN ANY_VALUE(TimezoneTitle)
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN ANY_VALUE(TimezoneTitle)
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
		INDEX tmp_RateTableRate_RateTableRateAAID (`RateTableRateAAID`),
		INDEX tmp_RateTableRate_RateTableRateID (`RateTableRateID`),
		INDEX tmp_RateTableRate_RateID (`RateTableId`,`RateID`,`OriginationRateID`,`TimezonesID`,`EffectiveDate`)
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
--		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_ AS (SELECT * FROM tmp_RateTableRate_);
		CREATE TEMPORARY TABLE tmp_RateTableRate2_ LIKE tmp_RateTableRate_;
		INSERT INTO tmp_RateTableRate2_ SELECT * FROM tmp_RateTableRate_;

		-- delete all duplicate records, keep only one - only last aa rate will be approved and all other will be ignored
		DELETE temp2
		FROM
			tmp_RateTableRate2_ temp2
		INNER JOIN
			tmp_RateTableRate_ temp1 ON temp1.RateTableId = temp2.RateTableId
			AND temp1.RateID = temp2.RateID
			AND temp1.OriginationRateID = temp2.OriginationRateID
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
			rtr.RateTableId = p_RateTableId AND
			(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID) AND
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
			rtr.RateTableId = p_RateTableId AND
			(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID) AND
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

		-- drop table to free up memory, now no need of it
		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate2_;

		-- delete from Awaiting Approval table after inserting into tblRateTableRate
		DELETE AA
		FROM
			tblRateTableRateAA AS AA
		INNER JOIN
			tmp_RateTableRate_ AS temp ON temp.RateTableRateAAID = AA.RateTableRateAAID;


	--	CALL prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');
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




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateApprove`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateApprove`(
	IN `p_RateTableId` INT,
	IN `p_RateTableDIDRateAAID` LONGTEXT,
	IN `p_ApprovedStatus` TINYINT,
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` VARCHAR(50),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_Critearia_Percentage` VARCHAR(500),
	IN `p_Critearea_City` VARCHAR(50),
	IN `p_Critearea_Tariff` VARCHAR(50),
	IN `p_Critearea_AccessType` VARCHAR(50),
	IN `p_ApprovedBy` VARCHAR(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	DECLARE v_StatusAwaitingApproval_ INT(11) DEFAULT 0;
	DECLARE v_StatusApproved_ INT(11) DEFAULT 1;
	DECLARE v_StatusRejected_ INT(11) DEFAULT 2;
	DECLARE v_StatusDelete_ INT(11) DEFAULT 3;
	DECLARE v_Reseller_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Reseller INTO v_Reseller_ FROM tblRateTable WHERE RateTableId=p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_RateTableDIDRate_ (
		`RateTableDIDRateAAID` BIGINT(20),
		`OriginationRateID` BIGINT(20),
		`RateID` INT(11),
		`RateTableId` BIGINT(20),
		`TimezonesID` BIGINT(20),
		`EffectiveDate` DATE,
		`EndDate` DATE,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` DECIMAL(18,6),
		`MonthlyCost` DECIMAL(18,6),
		`CostPerCall` DECIMAL(18,6),
		`CostPerMinute` DECIMAL(18,6),
		`SurchargePerCall` DECIMAL(18,6),
		`SurchargePerMinute` DECIMAL(18,6),
		`OutpaymentPerCall` DECIMAL(18,6),
		`OutpaymentPerMinute` DECIMAL(18,6),
		`Surcharges` DECIMAL(18,6),
		`Chargeback` DECIMAL(18,6),
		`CollectionCostAmount` DECIMAL(18,6),
		`CollectionCostPercentage` DECIMAL(18,6),
		`RegistrationCostPerNumber` DECIMAL(18,6),
		`OneOffCostCurrency` INT(11),
		`MonthlyCostCurrency` INT(11),
		`CostPerCallCurrency` INT(11),
		`CostPerMinuteCurrency` INT(11),
		`SurchargePerCallCurrency` INT(11),
		`SurchargePerMinuteCurrency` INT(11),
		`OutpaymentPerCallCurrency` INT(11),
		`OutpaymentPerMinuteCurrency` INT(11),
		`SurchargesCurrency` INT(11),
		`ChargebackCurrency` INT(11),
		`CollectionCostAmountCurrency` INT(11),
		`RegistrationCostPerNumberCurrency` INT(11),
		`created_at` DATETIME,
		`updated_at` DATETIME,
		`CreatedBy` VARCHAR(50),
		`ModifiedBy` VARCHAR(50),
		`ApprovedStatus` TINYINT(4),
		`ApprovedBy` VARCHAR(50),
		`ApprovedDate` DATETIME,
		`VendorID` INT(11),
		`RateTableDIDRateID` BIGINT(20),
		`OneOffCostMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`MonthlyCostMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`CostPerCallMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`CostPerMinuteMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`SurchargePerCallMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`SurchargePerMinuteMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`OutpaymentPerCallMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`OutpaymentPerMinuteMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`SurchargesMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`ChargebackMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`CollectionCostAmountMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`CollectionCostPercentageMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`RegistrationCostPerNumberMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		INDEX tmp_RateTableDIDRate_RateID (`RateID`,`OriginationRateID`,`TimezonesID`,`EffectiveDate`,`City`,`Tariff`,`AccessType`)
	);

	INSERT INTO	tmp_RateTableDIDRate_
	(
		`RateTableDIDRateAAID`,
		`OriginationRateID`,
		`RateID`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		`EndDate`,
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
		`created_at`,
		`updated_at`,
		`CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		`VendorID`,
		`RateTableDIDRateID`
	)
	SELECT
		rtr.RateTableDIDRateAAID,
		rtr.OriginationRateID,
		rtr.RateID,
		rtr.RateTableId,
		rtr.TimezonesID,
		IF(rtr.EffectiveDate < CURDATE(), CURDATE(), rtr.EffectiveDate) AS EffectiveDate,
		rtr.EndDate,
		rtr.City,
		rtr.Tariff,
		rtr.AccessType,
		rtr.OneOffCost,
		rtr.MonthlyCost,
		rtr.CostPerCall,
		rtr.CostPerMinute,
		rtr.SurchargePerCall,
		rtr.SurchargePerMinute,
		rtr.OutpaymentPerCall,
		rtr.OutpaymentPerMinute,
		rtr.Surcharges,
		rtr.Chargeback,
		rtr.CollectionCostAmount,
		rtr.CollectionCostPercentage,
		rtr.RegistrationCostPerNumber,
		rtr.OneOffCostCurrency,
		rtr.MonthlyCostCurrency,
		rtr.CostPerCallCurrency,
		rtr.CostPerMinuteCurrency,
		rtr.SurchargePerCallCurrency,
		rtr.SurchargePerMinuteCurrency,
		rtr.OutpaymentPerCallCurrency,
		rtr.OutpaymentPerMinuteCurrency,
		rtr.SurchargesCurrency,
		rtr.ChargebackCurrency,
		rtr.CollectionCostAmountCurrency,
		rtr.RegistrationCostPerNumberCurrency,
		rtr.created_at,
		rtr.updated_at,
		rtr.CreatedBy,
		rtr.ModifiedBy,
		rtr.ApprovedStatus AS ApprovedStatus,
		p_ApprovedBy AS ApprovedBy,
		NOW() AS ApprovedDate,
		rtr.VendorID,
		rtr.RateTableDIDRateID
	FROM
		tblRateTableDIDRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	WHERE
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableDIDRateAAID,p_RateTableDIDRateAAID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					(p_Critearea_City IS NULL OR rtr.City = p_Critearea_City) AND
					(p_Critearea_Tariff IS NULL OR rtr.Tariff = p_Critearea_Tariff) AND
					(p_Critearea_AccessType IS NULL OR rtr.AccessType = p_Critearea_AccessType) AND
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


	IF(p_Critearia_Percentage IS NOT NULL)
	THEN
		-- update current rate, margin and margin percentage
		UPDATE
			tmp_RateTableDIDRate_ tr
		LEFT JOIN
			tblRateTableDIDRate rtr ON rtr.RateTableDIDRateID = ( SELECT tmp.RateTableDIDRateID FROM tblRateTableDIDRate tmp INNER JOIN tblRateTable tmp_r ON tmp.RateTableId=tmp_r.RateTableId WHERE tmp.RateID=tr.RateID AND tmp.OriginationRateID=tr.OriginationRateID AND tmp.City=tr.City AND tmp.Tariff=tr.Tariff AND tmp.AccessType=tr.AccessType AND tmp.TimezonesID = tr.TimezonesID AND ((tr.EffectiveDate < CURDATE() AND tmp.EffectiveDate <= CURDATE()) OR tmp.EffectiveDate<=tr.EffectiveDate) AND tmp_r.Reseller=v_Reseller_ ORDER BY EffectiveDate DESC,RateTableDIDRateID DESC LIMIT 1 )
		SET
			OneOffCostMarginPercent = ROUND(
	            IF(
	                (IFNULL(tr.OneOffCost,0) - IFNULL(rtr.OneOffCost,0)) <> 0,
	                (((IFNULL(tr.OneOffCost,0) - IFNULL(rtr.OneOffCost,0)) * 100) / rtr.OneOffCost),
	                NULL
	            )
	            ,2
	        ),
			MonthlyCostMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.MonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) <> 0,
	                (((IFNULL(tr.MonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) * 100) / rtr.MonthlyCost),
	                NULL
			    )
			    ,2
	        ),
			CostPerCallMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.CostPerCall,0) - IFNULL(rtr.CostPerCall,0)) <> 0,
	                (((IFNULL(tr.CostPerCall,0) - IFNULL(rtr.CostPerCall,0)) * 100) / rtr.CostPerCall),
	                NULL
			    )
			    ,2
	        ),
			CostPerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.CostPerMinute,0) - IFNULL(rtr.CostPerMinute,0)) <> 0,
	                (((IFNULL(tr.CostPerMinute,0) - IFNULL(rtr.CostPerMinute,0)) * 100) / rtr.CostPerMinute),
	                NULL
			    )
			    ,2
	        ),
			SurchargePerCallMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.SurchargePerCall,0) - IFNULL(rtr.SurchargePerCall,0)) <> 0,
	                (((IFNULL(tr.SurchargePerCall,0) - IFNULL(rtr.SurchargePerCall,0)) * 100) / rtr.SurchargePerCall),
	                NULL
			    )
			    ,2
	        ),
			SurchargePerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.SurchargePerMinute,0) - IFNULL(rtr.SurchargePerMinute,0)) <> 0,
	                (((IFNULL(tr.SurchargePerMinute,0) - IFNULL(rtr.SurchargePerMinute,0)) * 100) / rtr.SurchargePerMinute),
	                NULL
			    )
			    ,2
	        ),
			OutpaymentPerCallMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OutpaymentPerCall,0) - IFNULL(rtr.OutpaymentPerCall,0)) <> 0,
	                (((IFNULL(tr.OutpaymentPerCall,0) - IFNULL(rtr.OutpaymentPerCall,0)) * 100) / rtr.OutpaymentPerCall),
	                NULL
			    )
			    ,2
	        ),
			OutpaymentPerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OutpaymentPerMinute,0) - IFNULL(rtr.OutpaymentPerMinute,0)) <> 0,
	                (((IFNULL(tr.OutpaymentPerMinute,0) - IFNULL(rtr.OutpaymentPerMinute,0)) * 100) / rtr.OutpaymentPerMinute),
	                NULL
			    )
			    ,2
	        ),
			SurchargesMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.Surcharges,0) - IFNULL(rtr.Surcharges,0)) <> 0,
	                (((IFNULL(tr.Surcharges,0) - IFNULL(rtr.Surcharges,0)) * 100) / rtr.Surcharges),
	                NULL
			    )
			    ,2
	        ),
			ChargebackMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.Chargeback,0) - IFNULL(rtr.Chargeback,0)) <> 0,
	                (((IFNULL(tr.Chargeback,0) - IFNULL(rtr.Chargeback,0)) * 100) / rtr.Chargeback),
	                NULL
			    )
			    ,2
	        ),
			CollectionCostAmountMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.CollectionCostAmount,0) - IFNULL(rtr.CollectionCostAmount,0)) <> 0,
	                (((IFNULL(tr.CollectionCostAmount,0) - IFNULL(rtr.CollectionCostAmount,0)) * 100) / rtr.CollectionCostAmount),
	                NULL
			    )
			    ,2
	        ),
			CollectionCostPercentageMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.CollectionCostPercentage,0) - IFNULL(rtr.CollectionCostPercentage,0)) <> 0,
	                (((IFNULL(tr.CollectionCostPercentage,0) - IFNULL(rtr.CollectionCostPercentage,0)) * 100) / rtr.CollectionCostPercentage),
	                NULL
			    )
			    ,2
	        ),
			RegistrationCostPerNumberMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.RegistrationCostPerNumber,0) - IFNULL(rtr.RegistrationCostPerNumber,0)) <> 0,
	                (((IFNULL(tr.RegistrationCostPerNumber,0) - IFNULL(rtr.RegistrationCostPerNumber,0)) * 100) / rtr.RegistrationCostPerNumber),
	                NULL
			    )
			    ,2
	        );

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate_1 LIKE tmp_RateTableDIDRate_;


		SET @v_Where = CONCAT('WHERE ApprovedStatus = ',v_StatusDelete_,' OR (OneOffCostMarginPercent ',p_Critearia_Percentage,' OR MonthlyCostMarginPercent ',p_Critearia_Percentage,' OR CostPerCallMarginPercent ',p_Critearia_Percentage,' OR CostPerMinuteMarginPercent ',p_Critearia_Percentage,' OR SurchargePerCallMarginPercent ',p_Critearia_Percentage,' OR SurchargePerMinuteMarginPercent ',p_Critearia_Percentage,' OR OutpaymentPerCallMarginPercent ',p_Critearia_Percentage,' OR OutpaymentPerMinuteMarginPercent ',p_Critearia_Percentage,' OR SurchargesMarginPercent ',p_Critearia_Percentage,' OR ChargebackMarginPercent ',p_Critearia_Percentage,' OR CollectionCostAmountMarginPercent ',p_Critearia_Percentage,' OR CollectionCostPercentageMarginPercent ',p_Critearia_Percentage,' OR RegistrationCostPerNumberMarginPercent ',p_Critearia_Percentage,')');
	 	SET @stm_filter_percent = CONCAT('
			INSERT INTO tmp_RateTableDIDRate_1 SELECT * FROM tmp_RateTableDIDRate_ ', @v_Where,';
		');

		PREPARE stmt FROM @stm_filter_percent;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_;
		ALTER TABLE tmp_RateTableDIDRate_1 RENAME tmp_RateTableDIDRate_;
--		RENAME TEMPORARY TABLE tmp_RateTablePKGRate_1 TO tmp_RateTablePKGRate_;

	END IF; -- /IF(p_Critearia_Percentage IS NOT NULL)



	IF p_ApprovedStatus = v_StatusApproved_ -- approve rates
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate2_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate2_ AS (SELECT * FROM tmp_RateTableDIDRate_);

		-- delete all duplicate records, keep only one - only last aa rate will be approved and all other will be ignored
		DELETE temp2
		FROM
			tmp_RateTableDIDRate2_ temp2
		INNER JOIN
			tmp_RateTableDIDRate_ temp1 ON temp1.OriginationRateID = temp2.OriginationRateID
			AND temp1.RateID = temp2.RateID
			AND temp1.RateTableId = temp2.RateTableId
			AND temp1.TimezonesID = temp2.TimezonesID
			AND temp1.City = temp2.City
			AND temp1.Tariff = temp2.Tariff
			AND temp1.AccessType = temp2.AccessType
			AND (
					temp1.EffectiveDate = temp2.EffectiveDate OR
					(temp1.EffectiveDate <= NOW() AND temp2.EffectiveDate <= NOW())
				)
		WHERE
			temp2.RateTableDIDRateAAID < temp1.RateTableDIDRateAAID;

		-- set EndDate to archive rates which needs to approve and exist with same effective date
		UPDATE
			tblRateTableDIDRate rtr
		INNER JOIN
			tmp_RateTableDIDRate2_ temp ON temp.RateId = rtr.RateId AND temp.OriginationRateID = rtr.OriginationRateID AND temp.TimezonesID = rtr.TimezonesID AND temp.EffectiveDate = rtr.EffectiveDate AND temp.City = rtr.City AND temp.Tariff = rtr.Tariff AND temp.AccessType = rtr.AccessType
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusAwaitingApproval_ AND
			rtr.RateTableId = p_RateTableId AND
			(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID);

		-- set EndDate to archive rates which needs to approve and exist with old effective date new rate is <=now() effective date
		UPDATE
			tblRateTableDIDRate rtr
		INNER JOIN
			tmp_RateTableDIDRate2_ temp ON temp.RateId = rtr.RateId AND
			temp.OriginationRateID = rtr.OriginationRateID AND
			temp.TimezonesID = rtr.TimezonesID AND
			(temp.EffectiveDate <= NOW() AND rtr.EffectiveDate <= temp.EffectiveDate) AND
			temp.City = rtr.City AND
			temp.Tariff = rtr.Tariff AND
			temp.AccessType = rtr.AccessType
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusAwaitingApproval_ AND
			rtr.RateTableId = p_RateTableId AND
			(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID);

		-- set EndDate to archive rates which rate's status is - awaiting approval delete
		UPDATE
			tblRateTableDIDRate rtr
		INNER JOIN
			tmp_RateTableDIDRate2_ temp ON temp.RateTableDIDRateID = rtr.RateTableDIDRateID
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusDelete_;

		--	archive rates
		CALL prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_ApprovedBy);

		-- insert approved rates to tblRateTableDIDRate
		INSERT INTO	tblRateTableDIDRate
		(
			OriginationRateID,
			RateID,
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
			VendorID
		)
		SELECT
			OriginationRateID,
			RateID,
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
			v_StatusApproved_ AS ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			VendorID
		FROM
			tmp_RateTableDIDRate2_
		WHERE
			ApprovedStatus = v_StatusAwaitingApproval_;

		-- delete from Awaiting Approval table after inserting into tblRateTableDIDRate
		DELETE AA
		FROM
			tblRateTableDIDRateAA AS AA
		INNER JOIN
			tmp_RateTableDIDRate_ AS temp ON temp.RateTableDIDRateAAID = AA.RateTableDIDRateAAID;

		CALL prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_ApprovedBy);

	ELSE -- reject/disapprove rates

		UPDATE
			tblRateTableDIDRateAA rtr
		INNER JOIN
			tmp_RateTableDIDRate_ temp ON temp.RateTableDIDRateAAID = rtr.RateTableDIDRateAAID
		SET
			rtr.ApprovedStatus = p_ApprovedStatus, rtr.ApprovedBy = temp.ApprovedBy, rtr.ApprovedDate = temp.ApprovedDate;

	END IF;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTablePKGRateApprove`;
DELIMITER //
CREATE PROCEDURE `prc_RateTablePKGRateApprove`(
	IN `p_RateTableId` INT,
	IN `p_RateTablePKGRateAAID` LONGTEXT,
	IN `p_ApprovedStatus` TINYINT,
	IN `p_Critearea_Code` VARCHAR(50),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_Critearia_Percentage` VARCHAR(500),
	IN `p_ApprovedBy` VARCHAR(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	DECLARE v_StatusAwaitingApproval_ INT(11) DEFAULT 0;
	DECLARE v_StatusApproved_ INT(11) DEFAULT 1;
	DECLARE v_StatusRejected_ INT(11) DEFAULT 2;
	DECLARE v_StatusDelete_ INT(11) DEFAULT 3;
	DECLARE v_Reseller_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Reseller INTO v_Reseller_ FROM tblRateTable WHERE RateTableId=p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_RateTablePKGRate_ (
		`RateTablePKGRateAAID` BIGINT(20),
		`RateID` INT(11),
		`RateTableId` BIGINT(20),
		`TimezonesID` BIGINT(20),
		`EffectiveDate` DATE,
		`EndDate` DATE,
		`OneOffCost` DECIMAL(18,6),
		`MonthlyCost` DECIMAL(18,6),
		`PackageCostPerMinute` DECIMAL(18,6),
		`RecordingCostPerMinute` DECIMAL(18,6),
		`OneOffCostCurrency` INT(11),
		`MonthlyCostCurrency` INT(11),
		`PackageCostPerMinuteCurrency` INT(11),
		`RecordingCostPerMinuteCurrency` INT(11),
		`created_at` DATETIME,
		`updated_at` DATETIME,
		`CreatedBy` VARCHAR(50),
		`ModifiedBy` VARCHAR(50),
		`ApprovedStatus` TINYINT(4),
		`ApprovedBy` VARCHAR(50),
		`ApprovedDate` DATETIME,
		`VendorID` INT(11),
		`RateTablePKGRateID` INT(11),
		`OneOffCostMarginPercent` decimal(18,6) NULL DEFAULT NULL,
		`MonthlyCostMarginPercent` decimal(18,6) NULL DEFAULT NULL,
		`PackageCostPerMinuteMarginPercent` decimal(18,6) NULL DEFAULT NULL,
		`RecordingCostPerMinuteMarginPercent` decimal(18,6) NULL DEFAULT NULL,
		INDEX tmp_RateTablePKGRate_RateID (`RateID`,`TimezonesID`,`EffectiveDate`)
	);

	INSERT INTO	tmp_RateTablePKGRate_
	(
		`RateTablePKGRateAAID`,
		`RateID`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		`EndDate`,
		`OneOffCost`,
		`MonthlyCost`,
		`PackageCostPerMinute`,
		`RecordingCostPerMinute`,
		`OneOffCostCurrency`,
		`MonthlyCostCurrency`,
		`PackageCostPerMinuteCurrency`,
		`RecordingCostPerMinuteCurrency`,
		`created_at`,
		`updated_at`,
		`CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		`VendorID`,
		`RateTablePKGRateID`
	)
	SELECT
		rtr.RateTablePKGRateAAID,
		rtr.RateID,
		rtr.RateTableId,
		rtr.TimezonesID,
		IF(rtr.EffectiveDate < CURDATE(), CURDATE(), rtr.EffectiveDate) AS EffectiveDate,
		rtr.EndDate,
		rtr.OneOffCost,
		rtr.MonthlyCost,
		rtr.PackageCostPerMinute,
		rtr.RecordingCostPerMinute,
		rtr.OneOffCostCurrency,
		rtr.MonthlyCostCurrency,
		rtr.PackageCostPerMinuteCurrency,
		rtr.RecordingCostPerMinuteCurrency,
		rtr.created_at,
		rtr.updated_at,
		rtr.CreatedBy,
		rtr.ModifiedBy,
		rtr.ApprovedStatus AS ApprovedStatus,
		p_ApprovedBy AS ApprovedBy,
		NOW() AS ApprovedDate,
		rtr.VendorID,
		rtr.RateTablePKGRateID
	FROM
		tblRateTablePKGRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	WHERE
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTablePKGRateAAID,p_RateTablePKGRateAAID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
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


	IF(p_Critearia_Percentage IS NOT NULL)
	THEN
		-- update current rate, margin and margin percentage
		UPDATE
			tmp_RateTablePKGRate_ tr
		LEFT JOIN
			tblRateTablePKGRate rtr ON rtr.RateTablePKGRateID = ( SELECT tmp.RateTablePKGRateID FROM tblRateTablePKGRate tmp INNER JOIN tblRateTable tmp_r ON tmp.RateTableId=tmp_r.RateTableId WHERE tmp.RateID=tr.RateID AND tmp.TimezonesID = tr.TimezonesID AND ((tr.EffectiveDate < CURDATE() AND tmp.EffectiveDate <= CURDATE()) OR tmp.EffectiveDate<=tr.EffectiveDate) AND tmp_r.Reseller=v_Reseller_ ORDER BY EffectiveDate DESC,RateTablePKGRateID DESC LIMIT 1 )
		SET
			OneOffCostMarginPercent = ROUND(
	            IF(
	                (IFNULL(tr.OneOffCost,0) - IFNULL(rtr.OneOffCost,0)) <> 0,
	                (((IFNULL(tr.OneOffCost,0) - IFNULL(rtr.OneOffCost,0)) * 100) / rtr.OneOffCost),
	                NULL
	            )
	            ,2
	        ),
			MonthlyCostMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.MonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) <> 0,
	                (((IFNULL(tr.MonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) * 100) / rtr.MonthlyCost),
	                NULL
			    )
			    ,2
	        ),
			PackageCostPerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.PackageCostPerMinute,0) - IFNULL(rtr.PackageCostPerMinute,0)) <> 0,
	                (((IFNULL(tr.PackageCostPerMinute,0) - IFNULL(rtr.PackageCostPerMinute,0)) * 100) / rtr.PackageCostPerMinute),
	                NULL
			    )
			    ,2
	        ),
			RecordingCostPerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.RecordingCostPerMinute,0) - IFNULL(rtr.RecordingCostPerMinute,0)) <> 0,
	                (((IFNULL(tr.RecordingCostPerMinute,0) - IFNULL(rtr.RecordingCostPerMinute,0)) * 100) / rtr.RecordingCostPerMinute),
	                NULL
			    )
			    ,2
	        );

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate_1 LIKE tmp_RateTablePKGRate_;


		SET @v_Where = CONCAT('WHERE ApprovedStatus = ',v_StatusDelete_,' OR (OneOffCostMarginPercent ',p_Critearia_Percentage,' OR MonthlyCostMarginPercent ',p_Critearia_Percentage,' OR PackageCostPerMinuteMarginPercent ',p_Critearia_Percentage,' OR RecordingCostPerMinuteMarginPercent ',p_Critearia_Percentage,')');
	 	SET @stm_filter_percent = CONCAT('
			INSERT INTO tmp_RateTablePKGRate_1 SELECT * FROM tmp_RateTablePKGRate_ ', @v_Where,';
		');

		PREPARE stmt FROM @stm_filter_percent;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_;
		ALTER TABLE tmp_RateTablePKGRate_1 RENAME tmp_RateTablePKGRate_;
--		RENAME TEMPORARY TABLE tmp_RateTablePKGRate_1 TO tmp_RateTablePKGRate_;

	END IF; -- /IF(p_Critearia_Percentage IS NOT NULL)


	IF p_ApprovedStatus = v_StatusApproved_ -- approve rates
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate2_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate2_ AS (SELECT * FROM tmp_RateTablePKGRate_);

		-- delete all duplicate records, keep only one - only last aa rate will be approved and all other will be ignored
		DELETE temp2
		FROM
			tmp_RateTablePKGRate2_ temp2
		INNER JOIN
			tmp_RateTablePKGRate_ temp1 ON temp1.RateID = temp2.RateID
			AND temp1.RateTableId = temp2.RateTableId
			AND temp1.TimezonesID = temp2.TimezonesID
			AND (
					temp1.EffectiveDate = temp2.EffectiveDate OR
					(temp1.EffectiveDate <= NOW() AND temp2.EffectiveDate <= NOW())
				)
		WHERE
			temp2.RateTablePKGRateAAID < temp1.RateTablePKGRateAAID;

		-- set EndDate to archive rates which needs to approve and exist with same effective date
		UPDATE
			tblRateTablePKGRate rtr
		INNER JOIN
			tmp_RateTablePKGRate2_ temp ON temp.RateId = rtr.RateId AND temp.TimezonesID = rtr.TimezonesID AND temp.EffectiveDate = rtr.EffectiveDate
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusAwaitingApproval_ AND
			rtr.RateTableId = p_RateTableId AND
			(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID);

		-- set EndDate to archive rates which needs to approve and exist with old effective date new rate is <=now() effective date
		UPDATE
			tblRateTablePKGRate rtr
		INNER JOIN
			tmp_RateTablePKGRate2_ temp ON temp.RateId = rtr.RateId AND
			temp.TimezonesID = rtr.TimezonesID AND
			(temp.EffectiveDate <= NOW() AND rtr.EffectiveDate <= temp.EffectiveDate)
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusAwaitingApproval_ AND
			rtr.RateTableId = p_RateTableId AND
			(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID);

		-- set EndDate to archive rates which rate's status is - awaiting approval delete
		UPDATE
			tblRateTablePKGRate rtr
		INNER JOIN
			tmp_RateTablePKGRate2_ temp ON temp.RateTablePKGRateID = rtr.RateTablePKGRateID
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusDelete_;

		--	archive rates
		CALL prc_ArchiveOldRateTablePKGRate(p_RateTableId, NULL,p_ApprovedBy);

		-- insert approved rates to tblRateTablePKGRate
		INSERT INTO	tblRateTablePKGRate
		(
			RateID,
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
			VendorID
		)
		SELECT
			RateID,
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
			v_StatusApproved_ AS ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			VendorID
		FROM
			tmp_RateTablePKGRate2_
		WHERE
			ApprovedStatus = v_StatusAwaitingApproval_;

		-- delete from Awaiting Approval table after inserting into tblRateTablePKGRate
		DELETE AA
		FROM
			tblRateTablePKGRateAA AS AA
		INNER JOIN
			tmp_RateTablePKGRate_ AS temp ON temp.RateTablePKGRateAAID = AA.RateTablePKGRateAAID;

--		CALL prc_ArchiveOldRateTablePKGRate(p_RateTableId, NULL,p_ApprovedBy);

	ELSE -- reject/disapprove rates

		UPDATE
			tblRateTablePKGRateAA rtr
		INNER JOIN
			tmp_RateTablePKGRate_ temp ON temp.RateTablePKGRateAAID = rtr.RateTablePKGRateAAID
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




DROP PROCEDURE IF EXISTS `UpdateRateTableInProductANDPackage`;
DELIMITER //
CREATE PROCEDURE `UpdateRateTableInProductANDPackage`()
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_RateANDDID;
	CREATE TEMPORARY TABLE tmp_RateANDDID  (
		RateTableId INT,
		CompanyId int,
		DIDCategoryID INT,
		City varchar(200),
		Tariff varchar(200),
		accessType varchar(200),
		prefixName varchar(200)

	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateANDTermination;
	CREATE TEMPORARY TABLE tmp_RateANDTermination  (
		CompanyId INT,
		RateTableId INT
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateANDPackage;
	CREATE TEMPORARY TABLE tmp_RateANDPackage  (
		RateTableId INT,
		CompanyId int,
		PackageCode varchar(50)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateANDPackagePartner;
	CREATE TEMPORARY TABLE tmp_RateANDPackagePartner  (
		RateTableId INT,
		ResellerID int,
		PackageCode varchar(50)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateANDPackagePartner1;
	CREATE TEMPORARY TABLE tmp_RateANDPackagePartner1  (
		ChildCompanyID int
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateANDDIDPartner;
	CREATE TEMPORARY TABLE tmp_RateANDDIDPartner  (
		RateTableId INT,
		ResellerID int,
		DIDCategoryID INT,
		City varchar(200),
		Tariff varchar(200),
		accessType varchar(200),
		prefixName varchar(200)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateANDDIDPartner1;
	CREATE TEMPORARY TABLE tmp_RateANDDIDPartner1  (
		ChildCompanyID int
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_SELECTedRateANDDID;
	CREATE TEMPORARY TABLE tmp_SELECTedRateANDDID  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		RateTableId INT,
		CompanyId int,
		DIDCategoryID INT,
		RateTableDIDRateID INT

	);

	SET @RateTableId:=0,@CompanyID:='',@DIDCategoryID:='',@ProductRateTableID:='',@OutboundRateTableID:=0;

	INSERT INTO tmp_RateANDPackage(RateTableId,CompanyId,PackageCode)
	SELECT MAX(rateTable.RateTableId), rateTable.CompanyId ,rate.Code
	FROM tblRateTablePKGRate pkgRate,tblRateTable rateTable,tblRate rate
	WHERE pkgRate.RateTableId = rateTable.RateTableId
		AND pkgRate.RateID = rate.RateID
		AND pkgRate.EffectiveDate <= date(sysdate())
		AND rateTable.`Type` = 3
		AND rateTable.AppliedTo = 1
		AND (SELECT COUNT(*) FROM tblRateTablePKGRateAA  WHERE RateTableId = pkgRate.RateTableId) =0
		AND rateTable.`Status` = 1
	GROUP BY rateTable.CompanyId,rate.Code;

	INSERT INTO tmp_RateANDPackagePartner(RateTableId,ResellerID,PackageCode)
	SELECT MAX(rateTable.RateTableId), rateTable.Reseller ,rate.Code
	FROM tblRateTablePKGRate pkgRate,tblRateTable rateTable,tblRate rate
	WHERE pkgRate.RateTableId = rateTable.RateTableId
		AND pkgRate.RateID = rate.RateID
		AND pkgRate.EffectiveDate <= date(sysdate())
		AND rateTable.`Type` = 3
		AND rateTable.AppliedTo = 3
		AND (SELECT COUNT(*) FROM tblRateTablePKGRateAA  WHERE RateTableId = pkgRate.RateTableId) =0
		AND rateTable.`Status` = 1
	GROUP BY rateTable.RateTableId,rateTable.Reseller,rate.Code;



	INSERT INTO tmp_RateANDPackage(RateTableId,CompanyId,PackageCode)
	SELECT RateTableId,ChildCompanyID,PackageCode FROM tblReseller,tmp_RateANDPackagePartner WHERE tblReseller.ResellerID = tmp_RateANDPackagePartner.ResellerID;

	SET @V_AllPackageCOUNT = (SELECT COUNT(*) FROM tmp_RateANDPackagePartner WHERE tmp_RateANDPackagePartner.ResellerID = -1);


	IF @V_AllPackageCOUNT > 0 THEN

		INSERT INTO tmp_RateANDPackagePartner1(ChildCompanyID)
		SELECT ChildCompanyID FROM tblReseller WHERE
		ResellerID not in (SELECT tmp_RateANDPackagePartner.ResellerID FROM tmp_RateANDPackagePartner WHERE tmp_RateANDPackagePartner.ResellerID <> -1);

		INSERT INTO tmp_RateANDPackage(RateTableId,CompanyId,PackageCode)
		SELECT RateTableId,ChildCompanyID,PackageCode FROM tmp_RateANDPackagePartner,tmp_RateANDPackagePartner1
		WHERE tmp_RateANDPackagePartner.ResellerID = -1;

	END IF;

	SELECT * FROM tmp_RateANDPackage;



	UPDATE tblPackage as package
	JOIN tmp_RateANDPackage ON package.CompanyID = tmp_RateANDPackage.CompanyID AND package.Name = tmp_RateANDPackage.PackageCode
		SET package.RateTableId = tmp_RateANDPackage.RateTableId
	WHERE package.Status = 1;


	-- get CompanyID and RateTableID list in tmp_RateANDTermination
	CALL getTerminationRateTableCompanyIDList();

	-- tmp_RateANDTermination table will populate from above procedure then update tblServiceTemplate in below query
	UPDATE
		tblServiceTemplate terminationTemplate
	JOIN
		tmp_RateANDTermination ON terminationTemplate.CompanyID = tmp_RateANDTermination.CompanyID
	SET
		terminationTemplate.OutboundRateTableId = tmp_RateANDTermination.RateTableId;


	INSERT INTO tmp_RateANDDID(RateTableId,CompanyId,DIDCategoryID,City,Tariff,accessType,prefixName)
	SELECT MAX(rateTable.RateTableId),rateTable.CompanyId,rateTable.DIDCategoryID,didRate.City,didRate.Tariff,didRate.AccessType,rate.Code
	FROM tblRateTableDIDRate didRate,tblRateTable rateTable,tblRate rate
	WHERE didRate.RateTableId = rateTable.RateTableId
		AND didRate.EffectiveDate <= date(NOW())
		AND rateTable.`Type` = 2
		AND rateTable.AppliedTo = 1
		AND (SELECT COUNT(*) FROM tblRateTableDIDRateAA  WHERE RateTableId = didRate.RateTableId) =0
		AND rateTable.`Status` = 1
		AND didRate.RateID = rate.RateID
	GROUP BY rateTable.CompanyId,
				rateTable.DIDCategoryID,didRate.City,didRate.Tariff,didRate.AccessType,rate.Code;

	INSERT INTO tmp_RateANDDIDPartner(RateTableId,ResellerID,DIDCategoryID,City,Tariff,accessType,prefixName)
	SELECT MAX(rateTable.RateTableId),rateTable.Reseller,rateTable.DIDCategoryID,didRate.City,didRate.Tariff,didRate.AccessType,rate.Code
	FROM tblRateTableDIDRate didRate,tblRateTable rateTable,tblRate rate
	WHERE didRate.RateTableId = rateTable.RateTableId
		AND didRate.EffectiveDate <= date(NOW())
		AND rateTable.`Type` = 2
		AND rateTable.AppliedTo = 3
		AND (SELECT COUNT(*) FROM tblRateTableDIDRateAA  WHERE RateTableId = didRate.RateTableId) =0
		AND rateTable.`Status` = 1
		AND didRate.RateID = rate.RateID
	GROUP BY rateTable.RateTableId,rateTable.Reseller,
				rateTable.DIDCategoryID,didRate.City,didRate.Tariff,didRate.AccessType,rate.Code;



	INSERT INTO tmp_RateANDDID(RateTableId,CompanyId,DIDCategoryID,City,Tariff,accessType,prefixName)
	SELECT RateTableId,ChildCompanyID,DIDCategoryID,City,Tariff,accessType,prefixName FROM tblReseller,tmp_RateANDDIDPartner WHERE tblReseller.ResellerID = tmp_RateANDDIDPartner.ResellerID;

	SET @V_AllDIDCOUNT = (SELECT COUNT(*) FROM tmp_RateANDDIDPartner WHERE tmp_RateANDDIDPartner.ResellerID = -1);

	IF @V_AllDIDCOUNT > 0 THEN

		-- SELECT RateTableId,DIDCategoryID INTO @V_AllDIDRateTable,@DIDCategoryID FROM tmp_RateANDDIDPartner WHERE tmp_RateANDDIDPartner.ResellerID = 0;
		INSERT INTO tmp_RateANDDIDPartner1(ChildCompanyID)
		SELECT ChildCompanyID FROM tblReseller WHERE
		ResellerID not in (SELECT tmp_RateANDDIDPartner.ResellerID FROM tmp_RateANDDIDPartner WHERE tmp_RateANDDIDPartner.ResellerID <> -1);

		INSERT INTO tmp_RateANDDID(RateTableId,CompanyId,DIDCategoryID,City,Tariff,accessType,prefixName)
		SELECT RateTableId,ChildCompanyID,DIDCategoryID,City,Tariff,accessType,prefixName FROM tmp_RateANDDIDPartner,tmp_RateANDDIDPartner1
		WHERE tmp_RateANDDIDPartner.ResellerID = -1;

	END IF;


	SELECT * FROM tmp_RateANDDID;


	UPDATE tblServiceTemapleInboundTariff AS ServiceTemapleInboundTariff
	INNER JOIN
	(
		SELECT
			serviceTemplate.ServiceTemplateId,r.DIDCategoryID,r.RateTableId
		FROM
			tblServiceTemplate serviceTemplate
		JOIN
			tmp_RateANDDID r ON r.CompanyId = serviceTemplate.CompanyID
			AND IFNULL(r.City,'') = IFNULL(serviceTemplate.City,'')
			AND IFNULL(r.Tariff,'') = IFNULL(serviceTemplate.Tariff,'')
			AND IFNULL(r.accessType,'') = IFNULL(serviceTemplate.accessType,'')
			AND r.prefixName =concat((SELECT Prefix FROM tblCountry WHERE Country = serviceTemplate.Country), TRIM(LEADING '0' FROM serviceTemplate.prefixName))
		INNER JOIN
			tblServiceTemapleInboundTariff tariff ON r.DIDCategoryID = tariff.DIDCategoryId
			AND serviceTemplate.ServiceTemplateId = tariff.ServiceTemplateID )	 serviceTemplate1
	SET
		ServiceTemapleInboundTariff.RateTableId = serviceTemplate1.RateTableId
	WHERE
		ServiceTemapleInboundTariff.ServiceTemplateID = serviceTemplate1.ServiceTemplateId
		AND ServiceTemapleInboundTariff.DIDCategoryId = serviceTemplate1.DIDCategoryID;


	INSERT INTO tblServiceTemapleInboundTariff
	(
		ServiceTemplateID,
		DIDCategoryId,
		RateTableId,
		CompanyID
	)
	SELECT
		serviceTemplate.ServiceTemplateId ,
		r.DIDCategoryID ,
		r.RateTableId ,
		serviceTemplate.CompanyID
	FROM
		tblServiceTemplate serviceTemplate
	JOIN
		tmp_RateANDDID r ON r.CompanyId = serviceTemplate.CompanyID
		AND IFNULL(r.City,'') = IFNULL(serviceTemplate.City,'')
		AND IFNULL(r.Tariff,'') = IFNULL(serviceTemplate.Tariff,'')
		AND IFNULL(r.accessType,'') = IFNULL(serviceTemplate.accessType,'')
		AND r.prefixName =concat((SELECT Prefix FROM tblCountry WHERE Country = serviceTemplate.Country), TRIM(LEADING '0' FROM serviceTemplate.prefixName))
	LEFT JOIN
		tblServiceTemapleInboundTariff tariff ON serviceTemplate.ServiceTemplateId = tariff.ServiceTemplateID
	WHERE
		tariff.ServiceTemapleInboundTariffId IS NULL;


END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `getTerminationRateTableCompanyIDList`;
DELIMITER //
CREATE PROCEDURE `getTerminationRateTableCompanyIDList`()
BEGIN

	-- get all company list
	INSERT INTO tmp_RateANDTermination (CompanyId)
	SELECT DISTINCT CompanyID from tblCompany;

	-- update the RateTableId if exist against that company
	UPDATE
		tmp_RateANDTermination temp
	JOIN
	(
		SELECT
			rateTable.CompanyId AS CompanyId, MAX(rateTable.RateTableId) AS RateTableId
		FROM
			tblRateTable rateTable
		INNER JOIN
			tblRateTableRate termRate ON termRate.RateTableId = rateTable.RateTableId
		LEFT JOIN
			tblRateTableRateAA AA ON AA.RateTableId = rateTable.RateTableId
		WHERE
			AA.RateTableRateAAID IS NULL
			AND termRate.EffectiveDate <= date(NOW())
			AND rateTable.`Type` = 1
			AND rateTable.AppliedTo != 2
			AND rateTable.`Status` = 1
		GROUP BY
			rateTable.CompanyId
	) temp2 ON temp.CompanyId = temp2.CompanyId
	SET
		temp.RateTableId = temp2.RateTableId
	WHERE
		temp.CompanyId = temp2.CompanyId;

	-- update rateTable if Partner = that partner
	UPDATE
		tmp_RateANDTermination temp
	JOIN
	(
		SELECT
			res.ChildCompanyID AS CompanyID, MAX(rateTable.RateTableId) AS RateTableId
		FROM
			tblRateTable rateTable
		INNER JOIN
			tblRateTableRate termRate ON termRate.RateTableId = rateTable.RateTableId
		LEFT JOIN
			tblRateTableRateAA AA ON AA.RateTableId = rateTable.RateTableId
		INNER JOIN
			tblReseller res ON res.ResellerID = rateTable.Reseller
		WHERE
			AA.RateTableRateAAID IS NULL
			AND termRate.EffectiveDate <= date(NOW())
			AND rateTable.`Type` = 1
			AND rateTable.AppliedTo = 3
			AND rateTable.`Status` = 1
		GROUP BY
			res.ChildCompanyID
	) temp2 ON temp.CompanyId = temp2.CompanyID
	SET
		temp.RateTableId = temp2.RateTableId
	WHERE
		temp.RateTableId IS NULL;

	-- If not found any rateTable against that company then get Partner=All RateTable and Update against that company
	UPDATE
		tmp_RateANDTermination temp
	JOIN
	(
		SELECT
			MAX(rateTable.RateTableId) AS RateTableId
		FROM
			tblRateTable rateTable
		INNER JOIN
			tblRateTableRate termRate ON termRate.RateTableId = rateTable.RateTableId
		LEFT JOIN
			tblRateTableRateAA AA ON AA.RateTableId = rateTable.RateTableId
		WHERE
			AA.RateTableRateAAID IS NULL
			AND termRate.EffectiveDate <= date(NOW())
			AND rateTable.`Type` = 1
			AND rateTable.AppliedTo = 3
			AND rateTable.`Status` = 1
			AND rateTable.`Reseller` = -1
		GROUP BY
			rateTable.CompanyID
	) temp2 ON temp.RateTableId IS NULL
	SET
		temp.RateTableId = temp2.RateTableId
	WHERE
		temp.RateTableId IS NULL;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableDIDRate`(
	IN `p_RateTableIds` BIGINT,
	IN `p_TimezonesIDs` INT,
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
		AND rtr2.AccessType = rtr.AccessType
	SET
		rtr.EndDate=NOW()
	WHERE
		(rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs) AND rtr.EffectiveDate <= NOW()) AND
		(rtr2.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr2.TimezonesID=p_TimezonesIDs) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableDIDRateID != rtr2.RateTableDIDRateID;


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
		Notes,
		VendorID
	)
	SELECT DISTINCT -- null ,
		`RateTableDIDRateID`,
		`OriginationRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
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
		now() as `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		concat('Ends Today rates @ ' , now() ) as `Notes`,
		VendorID
	FROM tblRateTableDIDRate
	WHERE RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR TimezonesID=p_TimezonesIDs) AND EndDate <= NOW();



	DELETE  rtr
	FROM tblRateTableDIDRate rtr
	inner join tblRateTableDIDRateArchive rtra
		on rtr.RateTableDIDRateID = rtra.RateTableDIDRateID
	WHERE  rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs);



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableDIDRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableDIDRateAA`(
	IN `p_RateTableIds` BIGINT,
	IN `p_TimezonesIDs` INT,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableDIDRateAA rtr
	INNER JOIN tblRateTableDIDRateAA rtr2
		ON rtr2.RateID = rtr.RateID
		AND rtr2.OriginationRateID = rtr.OriginationRateID
		AND rtr2.City = rtr.City
		AND rtr2.Tariff = rtr.Tariff
		AND rtr2.AccessType = rtr.AccessType
		AND rtr2.TimezonesID = rtr.TimezonesID
		AND rtr2.RateTableId = rtr.RateTableId
	SET
		rtr.EndDate=NOW()
	WHERE
		rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs AND rtr.EffectiveDate <= NOW()) AND
		rtr2.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr2.TimezonesID=p_TimezonesIDs AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate <= rtr2.EffectiveDate AND rtr.RateTableDIDRateAAID != rtr2.RateTableDIDRateAAID;


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
		Notes,
		VendorID
	)
	SELECT DISTINCT -- null ,
		`RateTableDIDRateAAID`,
		`OriginationRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
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
		now() as `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		concat('Ends Today rates @ ' , now() ) as `Notes`,
		VendorID
	FROM tblRateTableDIDRateAA
	WHERE
		RateTableId=p_RateTableIds
		AND (p_TimezonesIDs IS NULL OR TimezonesID=p_TimezonesIDs)
		AND EndDate <= NOW()
		AND ApprovedStatus = 2; -- only rejected rates will be archive



	DELETE  rtr
	FROM tblRateTableDIDRateAA rtr
	WHERE
		rtr.RateTableId=p_RateTableIds
		AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs)
		AND EndDate <= NOW();



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTablePKGRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTablePKGRate`(
	IN `p_RateTableIds` BIGINT,
	IN `p_TimezonesIDs` INT,
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
		(rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs) AND rtr.EffectiveDate <= NOW()) AND
		(rtr2.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr2.TimezonesID=p_TimezonesIDs) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTablePKGRateID != rtr2.RateTablePKGRateID;


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
		Notes,
			VendorID
	)
	SELECT DISTINCT -- null ,
		`RateTablePKGRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`OneOffCost`,
		`MonthlyCost`,
		`PackageCostPerMinute`,
		`RecordingCostPerMinute`,
		`OneOffCostCurrency`,
        `MonthlyCostCurrency`,
        `PackageCostPerMinuteCurrency`,
        `RecordingCostPerMinuteCurrency`,
        now() as `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		concat('Ends Today rates @ ' , now() ) as `Notes`,
			VendorID
	FROM tblRateTablePKGRate
	WHERE
		RateTableId=p_RateTableIds
		AND (p_TimezonesIDs IS NULL OR TimezonesID=p_TimezonesIDs)
		AND EndDate <= NOW()
		;



	DELETE  rtr
	FROM tblRateTablePKGRate rtr
	inner join tblRateTablePKGRateArchive rtra
		on rtr.RateTablePKGRateID = rtra.RateTablePKGRateID
	WHERE  rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs);



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTablePKGRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTablePKGRateAA`(
	IN `p_RateTableIds` BIGINT,
	IN `p_TimezonesIDs` INT,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTablePKGRateAA rtr
	INNER JOIN tblRateTablePKGRateAA rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND rtr2.TimezonesID = rtr.TimezonesID
	SET
		rtr.EndDate=NOW()
	WHERE
		(rtr.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs) AND rtr.EffectiveDate <= NOW()) AND
		(rtr2.RateTableId=p_RateTableIds AND (p_TimezonesIDs IS NULL OR rtr2.TimezonesID=p_TimezonesIDs) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate <= rtr2.EffectiveDate AND rtr.RateTablePKGRateAAID != rtr2.RateTablePKGRateAAID;


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
		Notes,
			VendorID
	)
	SELECT DISTINCT
		`RateTablePKGRateAAID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`OneOffCost`,
		`MonthlyCost`,
		`PackageCostPerMinute`,
		`RecordingCostPerMinute`,
		`OneOffCostCurrency`,
        `MonthlyCostCurrency`,
        `PackageCostPerMinuteCurrency`,
        `RecordingCostPerMinuteCurrency`,
        now() as `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		concat('Ends Today rates @ ' , now() ) as `Notes`,
			VendorID
	FROM tblRateTablePKGRateAA
	WHERE
		RateTableId=p_RateTableIds
		AND (p_TimezonesIDs IS NULL OR TimezonesID=p_TimezonesIDs)
		AND EndDate <= NOW()
		AND ApprovedStatus = 2;




	DELETE  rtr
	FROM tblRateTablePKGRateAA rtr
	WHERE
		rtr.RateTableId=p_RateTableIds
		AND (p_TimezonesIDs IS NULL OR rtr.TimezonesID=p_TimezonesIDs)
		AND EndDate <= NOW();



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateAAUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateAAUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTableDIDRateAAId` LONGTEXT,
	IN `p_OriginationRateID` INT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(200),
	IN `p_OneOffCost` VARCHAR(255),
	IN `p_MonthlyCost` VARCHAR(255),
	IN `p_CostPerCall` VARCHAR(255),
	IN `p_CostPerMinute` VARCHAR(255),
	IN `p_SurchargePerCall` VARCHAR(255),
	IN `p_SurchargePerMinute` VARCHAR(255),
	IN `p_OutpaymentPerCall` VARCHAR(255),
	IN `p_OutpaymentPerMinute` VARCHAR(255),
	IN `p_Surcharges` VARCHAR(255),
	IN `p_Chargeback` VARCHAR(255),
	IN `p_CollectionCostAmount` VARCHAR(255),
	IN `p_CollectionCostPercentage` VARCHAR(255),
	IN `p_RegistrationCostPerNumber` VARCHAR(255),
	IN `p_OneOffCostCurrency` DECIMAL(18,6),
	IN `p_MonthlyCostCurrency` DECIMAL(18,6),
	IN `p_CostPerCallCurrency` DECIMAL(18,6),
	IN `p_CostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_SurchargePerCallCurrency` DECIMAL(18,6),
	IN `p_SurchargePerMinuteCurrency` DECIMAL(18,6),
	IN `p_OutpaymentPerCallCurrency` DECIMAL(18,6),
	IN `p_OutpaymentPerMinuteCurrency` DECIMAL(18,6),
	IN `p_SurchargesCurrency` DECIMAL(18,6),
	IN `p_ChargebackCurrency` DECIMAL(18,6),
	IN `p_CollectionCostAmountCurrency` DECIMAL(18,6),
	IN `p_RegistrationCostPerNumberCurrency` DECIMAL(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_City` VARCHAR(50),
	IN `p_Critearea_Tariff` VARCHAR(50),
	IN `p_Critearea_AccessType` VARCHAR(50),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_Critearia_Percentage` VARCHAR(500),
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN
	DECLARE v_Reseller_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Reseller INTO v_Reseller_ FROM tblRateTable WHERE RateTableId=p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		`RateTableDIDRateAAId` int(11) NOT NULL,
		`OriginationRateID` int(11) NOT NULL DEFAULT '0',
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`City` VARCHAR(50) NOT NULL DEFAULT '',
		`Tariff` VARCHAR(50) NOT NULL DEFAULT '',
		`AccessType` VARCHAR(200) NOT NULL DEFAULT '',
		`OldOneOffCost` decimal(18,6) NULL DEFAULT NULL,
		`OldMonthlyCost` decimal(18,6) NULL DEFAULT NULL,
		`OldCostPerCall` decimal(18,6) NULL DEFAULT NULL,
		`OldCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OldSurchargePerCall` decimal(18,6) NULL DEFAULT NULL,
		`OldSurchargePerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OldOutpaymentPerCall` decimal(18,6) NULL DEFAULT NULL,
		`OldOutpaymentPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OldSurcharges` decimal(18,6) NULL DEFAULT NULL,
		`OldChargeback` decimal(18,6) NULL DEFAULT NULL,
		`OldCollectionCostAmount` decimal(18,6) NULL DEFAULT NULL,
		`OldCollectionCostPercentage` decimal(18,6) NULL DEFAULT NULL,
		`OldRegistrationCostPerNumber` decimal(18,6) NULL DEFAULT NULL,
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` datetime NOT NULL,
		`EndDate` datetime DEFAULT NULL,
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		`CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ApprovedStatus` tinyint,
		`ApprovedBy` varchar(50),
		`ApprovedDate` datetime,
		`OneOffCostMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`MonthlyCostMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`CostPerCallMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`CostPerMinuteMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`SurchargePerCallMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`SurchargePerMinuteMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`OutpaymentPerCallMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`OutpaymentPerMinuteMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`SurchargesMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`ChargebackMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`CollectionCostAmountMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`CollectionCostPercentageMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		`RegistrationCostPerNumberMarginPercent` VARCHAR(255) NULL DEFAULT NULL,
		INDEX `tmp_IX_DIDAA_RateTableDIDRateAAID` (`RateTableDIDRateAAID`),
		INDEX `tmp_IX_DIDAA_ApprovedStatus` (`ApprovedStatus`),
		INDEX `tmp_IX_DIDAA_RateTableId_RateID_ORateID_TimezonesID_EffDate` (`RateID`, `OriginationRateID`, `City`, `Tariff`, `AccessType`, `TimezonesID`, `RateTableId`, `EffectiveDate`),
		INDEX `tmp_IX_DIDAA_RateTableDIDRateAAID_Currency` (`RateTableDIDRateAAID`, `OneOffCostCurrency`, `MonthlyCostCurrency`, `CostPerCallCurrency`, `CostPerMinuteCurrency`, `SurchargePerCallCurrency`, `SurchargePerMinuteCurrency`, `OutpaymentPerCallCurrency`, `OutpaymentPerMinuteCurrency`, `SurchargesCurrency`, `ChargebackCurrency`, `CollectionCostAmountCurrency`, `RegistrationCostPerNumberCurrency`)
	);

	INSERT INTO tmp_TempRateTableDIDRate_
	(
		`RateTableDIDRateAAId`,
		`OriginationRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`City`,
		`Tariff`,
		`AccessType`,
		`OldOneOffCost`,
		`OldMonthlyCost`,
		`OldCostPerCall`,
		`OldCostPerMinute`,
		`OldSurchargePerCall`,
		`OldSurchargePerMinute`,
		`OldOutpaymentPerCall`,
		`OldOutpaymentPerMinute`,
		`OldSurcharges`,
		`OldChargeback`,
		`OldCollectionCostAmount`,
		`OldCollectionCostPercentage`,
		`OldRegistrationCostPerNumber`,
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
		`EffectiveDate`,
		`EndDate`,
		`created_at`,
		`updated_at`,
		`CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`
	)
	SELECT
		rtr.RateTableDIDRateAAId,
		IF(rtr.OriginationRateID=0,IFNULL(p_OriginationRateID,rtr.OriginationRateID),rtr.OriginationRateID) AS OriginationRateID,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IFNULL(p_City,rtr.City) AS City,
		IFNULL(p_Tariff,rtr.Tariff) AS Tariff,
		IFNULL(p_AccessType,rtr.AccessType) AS AccessType,
		rtr.OneOffCost,
		rtr.MonthlyCost,
		rtr.CostPerCall,
		rtr.CostPerMinute,
		rtr.SurchargePerCall,
		rtr.SurchargePerMinute,
		rtr.OutpaymentPerCall,
		rtr.OutpaymentPerMinute,
		rtr.Surcharges,
		rtr.Chargeback,
		rtr.CollectionCostAmount,
		rtr.CollectionCostPercentage,
		rtr.RegistrationCostPerNumber,
		IF(p_OneOffCost IS NOT NULL,IF(p_OneOffCost='NULL',NULL,p_OneOffCost),rtr.OneOffCost) AS OneOffCost,
		IF(p_MonthlyCost IS NOT NULL,IF(p_MonthlyCost='NULL',NULL,p_MonthlyCost),rtr.MonthlyCost) AS MonthlyCost,
		IF(p_CostPerCall IS NOT NULL,IF(p_CostPerCall='NULL',NULL,p_CostPerCall),rtr.CostPerCall) AS CostPerCall,
		IF(p_CostPerMinute IS NOT NULL,IF(p_CostPerMinute='NULL',NULL,p_CostPerMinute),rtr.CostPerMinute) AS CostPerMinute,
		IF(p_SurchargePerCall IS NOT NULL,IF(p_SurchargePerCall='NULL',NULL,p_SurchargePerCall),rtr.SurchargePerCall) AS SurchargePerCall,
		IF(p_SurchargePerMinute IS NOT NULL,IF(p_SurchargePerMinute='NULL',NULL,p_SurchargePerMinute),rtr.SurchargePerMinute) AS SurchargePerMinute,
		IF(p_OutpaymentPerCall IS NOT NULL,IF(p_OutpaymentPerCall='NULL',NULL,p_OutpaymentPerCall),rtr.OutpaymentPerCall) AS OutpaymentPerCall,
		IF(p_OutpaymentPerMinute IS NOT NULL,IF(p_OutpaymentPerMinute='NULL',NULL,p_OutpaymentPerMinute),rtr.OutpaymentPerMinute) AS OutpaymentPerMinute,
		IF(p_Surcharges IS NOT NULL,IF(p_Surcharges='NULL',NULL,p_Surcharges),rtr.Surcharges) AS Surcharges,
		IF(p_Chargeback IS NOT NULL,IF(p_Chargeback='NULL',NULL,p_Chargeback),rtr.Chargeback) AS Chargeback,
		IF(p_CollectionCostAmount IS NOT NULL,IF(p_CollectionCostAmount='NULL',NULL,p_CollectionCostAmount),rtr.CollectionCostAmount) AS CollectionCostAmount,
		IF(p_CollectionCostPercentage IS NOT NULL,IF(p_CollectionCostPercentage='NULL',NULL,p_CollectionCostPercentage),rtr.CollectionCostPercentage) AS CollectionCostPercentage,
		IF(p_RegistrationCostPerNumber IS NOT NULL,IF(p_RegistrationCostPerNumber='NULL',NULL,p_RegistrationCostPerNumber),rtr.RegistrationCostPerNumber) AS RegistrationCostPerNumber,
		IFNULL(p_OneOffCostCurrency,rtr.OneOffCostCurrency) AS OneOffCostCurrency,
		IFNULL(p_MonthlyCostCurrency,rtr.MonthlyCostCurrency) AS MonthlyCostCurrency,
		IFNULL(p_CostPerCallCurrency,rtr.CostPerCallCurrency) AS CostPerCallCurrency,
		IFNULL(p_CostPerMinuteCurrency,rtr.CostPerMinuteCurrency) AS CostPerMinuteCurrency,
		IFNULL(p_SurchargePerCallCurrency,rtr.SurchargePerCallCurrency) AS SurchargePerCallCurrency,
		IFNULL(p_SurchargePerMinuteCurrency,rtr.SurchargePerMinuteCurrency) AS SurchargePerMinuteCurrency,
		IFNULL(p_OutpaymentPerCallCurrency,rtr.OutpaymentPerCallCurrency) AS OutpaymentPerCallCurrency,
		IFNULL(p_OutpaymentPerMinuteCurrency,rtr.OutpaymentPerMinuteCurrency) AS OutpaymentPerMinuteCurrency,
		IFNULL(p_SurchargesCurrency,rtr.SurchargesCurrency) AS SurchargesCurrency,
		IFNULL(p_ChargebackCurrency,rtr.ChargebackCurrency) AS ChargebackCurrency,
		IFNULL(p_CollectionCostAmountCurrency,rtr.CollectionCostAmountCurrency) AS CollectionCostAmountCurrency,
		IFNULL(p_RegistrationCostPerNumberCurrency,rtr.RegistrationCostPerNumberCurrency) AS RegistrationCostPerNumberCurrency,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		rtr.ApprovedStatus,
		NULL AS ApprovedBy,
		NULL AS ApprovedDate
	FROM
		tblRateTableDIDRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
   INNER JOIN
		tblRateTable ON tblRateTable.RateTableId = rtr.RateTableId
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.OriginationRateID,rtr.TimezonesID) NOT IN (
						SELECT
							RateID,OriginationRateID,TimezonesID
						FROM
							tblRateTableDIDRateAA
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND City=p_City AND Tariff=p_Tariff AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTableDIDRateAAID,p_RateTableDIDRateAAID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableDIDRateAAID,p_RateTableDIDRateAAID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					(p_Critearea_City IS NULL OR rtr.City = p_Critearea_City) AND
					(p_Critearea_Tariff IS NULL OR rtr.Tariff = p_Critearea_Tariff) AND
					(p_Critearea_AccessType IS NULL OR rtr.AccessType = p_Critearea_AccessType) AND
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


	IF p_action = 1
	THEN

		-- remove rejected rates from temp table while updating so, it can't be update and delete
		DELETE n1 FROM tmp_TempRateTableDIDRate_ n1 WHERE ApprovedStatus = 2;

		IF p_EffectiveDate IS NOT NULL
		THEN
			DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_2;
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 LIKE tmp_TempRateTableDIDRate_;
			INSERT INTO tmp_TempRateTableDIDRate_2 SELECT * FROM tmp_TempRateTableDIDRate_;
			DELETE n1 FROM tmp_TempRateTableDIDRate_ n1, tmp_TempRateTableDIDRate_2 n2 WHERE n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.City=n2.City AND n1.Tariff=n2.Tariff AND n1.AccessType=n2.AccessType AND n1.TimezonesID = n2.TimezonesID AND n1.RateTableId = n2.RateTableId AND n1.RateTableDIDRateAAID < n2.RateTableDIDRateAAID;
		END IF;


		-- update current rate, margin and margin percentage
		UPDATE
			tmp_TempRateTableDIDRate_ tr
		LEFT JOIN
			tblRateTableDIDRate rtr ON rtr.RateTableDIDRateID = ( SELECT tmp.RateTableDIDRateID FROM tblRateTableDIDRate tmp INNER JOIN tblRateTable tmp_r ON tmp.RateTableId=tmp_r.RateTableId WHERE tmp.RateID=tr.RateID AND tmp.OriginationRateID=tr.OriginationRateID AND tmp.City=tr.City AND tmp.Tariff=tr.Tariff AND tmp.AccessType=tr.AccessType AND tmp.TimezonesID = tr.TimezonesID AND ((tr.EffectiveDate < CURDATE() AND tmp.EffectiveDate <= CURDATE()) OR tmp.EffectiveDate<=tr.EffectiveDate) AND tmp_r.Reseller=v_Reseller_ ORDER BY EffectiveDate DESC,RateTableDIDRateID DESC LIMIT 1 )
		SET
			OneOffCostMarginPercent = ROUND(
	            IF(
	                (IFNULL(tr.OldOneOffCost,0) - IFNULL(rtr.OneOffCost,0)) <> 0,
	                (((IFNULL(tr.OldOneOffCost,0) - IFNULL(rtr.OneOffCost,0)) * 100) / rtr.OneOffCost),
	                NULL
	            )
	            ,2
	        ),
			MonthlyCostMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldMonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) <> 0,
	                (((IFNULL(tr.OldMonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) * 100) / rtr.MonthlyCost),
	                NULL
			    )
			    ,2
	        ),
			CostPerCallMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldCostPerCall,0) - IFNULL(rtr.CostPerCall,0)) <> 0,
	                (((IFNULL(tr.OldCostPerCall,0) - IFNULL(rtr.CostPerCall,0)) * 100) / rtr.CostPerCall),
	                NULL
			    )
			    ,2
	        ),
			CostPerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldCostPerMinute,0) - IFNULL(rtr.CostPerMinute,0)) <> 0,
	                (((IFNULL(tr.OldCostPerMinute,0) - IFNULL(rtr.CostPerMinute,0)) * 100) / rtr.CostPerMinute),
	                NULL
			    )
			    ,2
	        ),
			SurchargePerCallMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldSurchargePerCall,0) - IFNULL(rtr.SurchargePerCall,0)) <> 0,
	                (((IFNULL(tr.OldSurchargePerCall,0) - IFNULL(rtr.SurchargePerCall,0)) * 100) / rtr.SurchargePerCall),
	                NULL
			    )
			    ,2
	        ),
			SurchargePerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldSurchargePerMinute,0) - IFNULL(rtr.SurchargePerMinute,0)) <> 0,
	                (((IFNULL(tr.OldSurchargePerMinute,0) - IFNULL(rtr.SurchargePerMinute,0)) * 100) / rtr.SurchargePerMinute),
	                NULL
			    )
			    ,2
	        ),
			OutpaymentPerCallMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldOutpaymentPerCall,0) - IFNULL(rtr.OutpaymentPerCall,0)) <> 0,
	                (((IFNULL(tr.OldOutpaymentPerCall,0) - IFNULL(rtr.OutpaymentPerCall,0)) * 100) / rtr.OutpaymentPerCall),
	                NULL
			    )
			    ,2
	        ),
			OutpaymentPerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldOutpaymentPerMinute,0) - IFNULL(rtr.OutpaymentPerMinute,0)) <> 0,
	                (((IFNULL(tr.OldOutpaymentPerMinute,0) - IFNULL(rtr.OutpaymentPerMinute,0)) * 100) / rtr.OutpaymentPerMinute),
	                NULL
			    )
			    ,2
	        ),
			SurchargesMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldSurcharges,0) - IFNULL(rtr.Surcharges,0)) <> 0,
	                (((IFNULL(tr.OldSurcharges,0) - IFNULL(rtr.Surcharges,0)) * 100) / rtr.Surcharges),
	                NULL
			    )
			    ,2
	        ),
			ChargebackMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldChargeback,0) - IFNULL(rtr.Chargeback,0)) <> 0,
	                (((IFNULL(tr.OldChargeback,0) - IFNULL(rtr.Chargeback,0)) * 100) / rtr.Chargeback),
	                NULL
			    )
			    ,2
	        ),
			CollectionCostAmountMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldCollectionCostAmount,0) - IFNULL(rtr.CollectionCostAmount,0)) <> 0,
	                (((IFNULL(tr.OldCollectionCostAmount,0) - IFNULL(rtr.CollectionCostAmount,0)) * 100) / rtr.CollectionCostAmount),
	                NULL
			    )
			    ,2
	        ),
			CollectionCostPercentageMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldCollectionCostPercentage,0) - IFNULL(rtr.CollectionCostPercentage,0)) <> 0,
	                (((IFNULL(tr.OldCollectionCostPercentage,0) - IFNULL(rtr.CollectionCostPercentage,0)) * 100) / rtr.CollectionCostPercentage),
	                NULL
			    )
			    ,2
	        ),
			RegistrationCostPerNumberMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldRegistrationCostPerNumber,0) - IFNULL(rtr.RegistrationCostPerNumber,0)) <> 0,
	                (((IFNULL(tr.OldRegistrationCostPerNumber,0) - IFNULL(rtr.RegistrationCostPerNumber,0)) * 100) / rtr.RegistrationCostPerNumber),
	                NULL
			    )
			    ,2
	        );

	      IF(p_Critearia_Percentage IS NOT NULL)
			THEN

				DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_Final;
				CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_Final LIKE tmp_TempRateTableDIDRate_;

				SET @v_Where = CONCAT('WHERE (OneOffCostMarginPercent ',p_Critearia_Percentage,' OR MonthlyCostMarginPercent ',p_Critearia_Percentage,' OR CostPerCallMarginPercent ',p_Critearia_Percentage,' OR CostPerMinuteMarginPercent ',p_Critearia_Percentage,' OR SurchargePerCallMarginPercent ',p_Critearia_Percentage,' OR SurchargePerMinuteMarginPercent ',p_Critearia_Percentage,' OR OutpaymentPerCallMarginPercent ',p_Critearia_Percentage,' OR OutpaymentPerMinuteMarginPercent ',p_Critearia_Percentage,' OR SurchargesMarginPercent ',p_Critearia_Percentage,' OR ChargebackMarginPercent ',p_Critearia_Percentage,' OR CollectionCostAmountMarginPercent ',p_Critearia_Percentage,' OR CollectionCostPercentageMarginPercent ',p_Critearia_Percentage,' OR RegistrationCostPerNumberMarginPercent ',p_Critearia_Percentage,')');
	       	SET @stm_filter_percent = CONCAT('
					INSERT INTO tmp_TempRateTableDIDRate_Final SELECT * FROM tmp_TempRateTableDIDRate_ ', @v_Where,';
				');

				PREPARE stmt FROM @stm_filter_percent;
				EXECUTE stmt;
				DEALLOCATE PREPARE stmt;

				DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
				ALTER TABLE tmp_TempRateTableDIDRate_Final RENAME tmp_TempRateTableDIDRate_;

			END IF;


		-- delete records which can be duplicates, we will not update them
--		DELETE n1.* FROM tmp_TempRateTableDIDRate_ n1, tblRateTableDIDRateAA n2 WHERE n1.RateTableDIDRateAAID <> n2.RateTableDIDRateAAID AND n1.RateTableID = n2.RateTableID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate = n2.EffectiveDate AND n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.City=n2.City AND n1.Tariff=n2.Tariff AND n2.RateTableID=p_RateTableId;

		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
/*		DELETE
			temp
		FROM
			tmp_TempRateTableDIDRate_ temp
		JOIN
			tblRateTableDIDRateAA rtr ON rtr.RateTableDIDRateAAID = temp.RateTableDIDRateAAID
		WHERE
			(rtr.OriginationRateID = temp.OriginationRateID) AND
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			(rtr.TimezonesID = temp.TimezonesID) AND
			(rtr.City = temp.City) AND
			(rtr.Tariff = temp.Tariff) AND
			((rtr.AccessType IS NULL && temp.AccessType IS NULL) || rtr.AccessType = temp.AccessType) AND
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
			((rtr.RegistrationCostPerNumber IS NULL && temp.RegistrationCostPerNumber IS NULL) || rtr.RegistrationCostPerNumber = temp.RegistrationCostPerNumber) AND
			((rtr.OneOffCostCurrency IS NULL && temp.OneOffCostCurrency IS NULL) || rtr.OneOffCostCurrency = temp.OneOffCostCurrency) AND
			((rtr.MonthlyCostCurrency IS NULL && temp.MonthlyCostCurrency IS NULL) || rtr.MonthlyCostCurrency = temp.MonthlyCostCurrency) AND
			((rtr.CostPerCallCurrency IS NULL && temp.CostPerCallCurrency IS NULL) || rtr.CostPerCallCurrency = temp.CostPerCallCurrency) AND
			((rtr.CostPerMinuteCurrency IS NULL && temp.CostPerMinuteCurrency IS NULL) || rtr.CostPerMinuteCurrency = temp.CostPerMinuteCurrency) AND
			((rtr.SurchargePerCallCurrency IS NULL && temp.SurchargePerCallCurrency IS NULL) || rtr.SurchargePerCallCurrency = temp.SurchargePerCallCurrency) AND
			((rtr.SurchargePerMinuteCurrency IS NULL && temp.SurchargePerMinuteCurrency IS NULL) || rtr.SurchargePerMinuteCurrency = temp.SurchargePerMinuteCurrency) AND
			((rtr.OutpaymentPerCallCurrency IS NULL && temp.OutpaymentPerCallCurrency IS NULL) || rtr.OutpaymentPerCallCurrency = temp.OutpaymentPerCallCurrency) AND
			((rtr.OutpaymentPerMinuteCurrency IS NULL && temp.OutpaymentPerMinuteCurrency IS NULL) || rtr.OutpaymentPerMinuteCurrency = temp.OutpaymentPerMinuteCurrency) AND
			((rtr.SurchargesCurrency IS NULL && temp.SurchargesCurrency IS NULL) || rtr.SurchargesCurrency = temp.SurchargesCurrency) AND
			((rtr.ChargebackCurrency IS NULL && temp.ChargebackCurrency IS NULL) || rtr.ChargebackCurrency = temp.ChargebackCurrency) AND
			((rtr.CollectionCostAmountCurrency IS NULL && temp.CollectionCostAmountCurrency IS NULL) || rtr.CollectionCostAmountCurrency = temp.CollectionCostAmountCurrency) AND
			((rtr.RegistrationCostPerNumberCurrency IS NULL && temp.RegistrationCostPerNumberCurrency IS NULL) || rtr.RegistrationCostPerNumberCurrency = temp.RegistrationCostPerNumberCurrency);
*/
	END IF;


	UPDATE
		tblRateTableDIDRateAA rtr
	INNER JOIN
		tmp_TempRateTableDIDRate_ temp ON temp.RateTableDIDRateAAID = rtr.RateTableDIDRateAAID
	SET
		rtr.EndDate = NOW()
	WHERE
		rtr.RateTableId = p_RateTableId;

	CALL prc_ArchiveOldRateTableDIDRateAA(p_RateTableId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN

		INSERT INTO tblRateTableDIDRateAA (
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
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
		SELECT
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
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
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTableDIDRate_
		WHERE
			ApprovedStatus = 0; -- only allow awaiting approval rates to be updated

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
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(200),
	IN `p_OneOffCost` VARCHAR(255),
	IN `p_MonthlyCost` VARCHAR(255),
	IN `p_CostPerCall` VARCHAR(255),
	IN `p_CostPerMinute` VARCHAR(255),
	IN `p_SurchargePerCall` VARCHAR(255),
	IN `p_SurchargePerMinute` VARCHAR(255),
	IN `p_OutpaymentPerCall` VARCHAR(255),
	IN `p_OutpaymentPerMinute` VARCHAR(255),
	IN `p_Surcharges` VARCHAR(255),
	IN `p_Chargeback` VARCHAR(255),
	IN `p_CollectionCostAmount` VARCHAR(255),
	IN `p_CollectionCostPercentage` VARCHAR(255),
	IN `p_RegistrationCostPerNumber` VARCHAR(255),
	IN `p_OneOffCostCurrency` DECIMAL(18,6),
	IN `p_MonthlyCostCurrency` DECIMAL(18,6),
	IN `p_CostPerCallCurrency` DECIMAL(18,6),
	IN `p_CostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_SurchargePerCallCurrency` DECIMAL(18,6),
	IN `p_SurchargePerMinuteCurrency` DECIMAL(18,6),
	IN `p_OutpaymentPerCallCurrency` DECIMAL(18,6),
	IN `p_OutpaymentPerMinuteCurrency` DECIMAL(18,6),
	IN `p_SurchargesCurrency` DECIMAL(18,6),
	IN `p_ChargebackCurrency` DECIMAL(18,6),
	IN `p_CollectionCostAmountCurrency` DECIMAL(18,6),
	IN `p_RegistrationCostPerNumberCurrency` DECIMAL(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_City` VARCHAR(50),
	IN `p_Critearea_Tariff` VARCHAR(50),
	IN `p_Critearea_AccessType` VARCHAR(50),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ModifiedBy` varchar(50),
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

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		`RateTableDIDRateId` int(11) NOT NULL,
		`OriginationRateID` int(11) NOT NULL DEFAULT '0',
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`City` VARCHAR(50) NOT NULL DEFAULT '',
		`Tariff` VARCHAR(50) NOT NULL DEFAULT '',
		`AccessType` VARCHAR(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
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
		IFNULL(p_City,rtr.City) AS City,
		IFNULL(p_Tariff,rtr.Tariff) AS Tariff,
		IFNULL(p_AccessType,rtr.AccessType) AS AccessType,
		IF(p_OneOffCost IS NOT NULL,IF(p_OneOffCost='NULL',NULL,p_OneOffCost),rtr.OneOffCost) AS OneOffCost,
		IF(p_MonthlyCost IS NOT NULL,IF(p_MonthlyCost='NULL',NULL,p_MonthlyCost),rtr.MonthlyCost) AS MonthlyCost,
		IF(p_CostPerCall IS NOT NULL,IF(p_CostPerCall='NULL',NULL,p_CostPerCall),rtr.CostPerCall) AS CostPerCall,
		IF(p_CostPerMinute IS NOT NULL,IF(p_CostPerMinute='NULL',NULL,p_CostPerMinute),rtr.CostPerMinute) AS CostPerMinute,
		IF(p_SurchargePerCall IS NOT NULL,IF(p_SurchargePerCall='NULL',NULL,p_SurchargePerCall),rtr.SurchargePerCall) AS SurchargePerCall,
		IF(p_SurchargePerMinute IS NOT NULL,IF(p_SurchargePerMinute='NULL',NULL,p_SurchargePerMinute),rtr.SurchargePerMinute) AS SurchargePerMinute,
		IF(p_OutpaymentPerCall IS NOT NULL,IF(p_OutpaymentPerCall='NULL',NULL,p_OutpaymentPerCall),rtr.OutpaymentPerCall) AS OutpaymentPerCall,
		IF(p_OutpaymentPerMinute IS NOT NULL,IF(p_OutpaymentPerMinute='NULL',NULL,p_OutpaymentPerMinute),rtr.OutpaymentPerMinute) AS OutpaymentPerMinute,
		IF(p_Surcharges IS NOT NULL,IF(p_Surcharges='NULL',NULL,p_Surcharges),rtr.Surcharges) AS Surcharges,
		IF(p_Chargeback IS NOT NULL,IF(p_Chargeback='NULL',NULL,p_Chargeback),rtr.Chargeback) AS Chargeback,
		IF(p_CollectionCostAmount IS NOT NULL,IF(p_CollectionCostAmount='NULL',NULL,p_CollectionCostAmount),rtr.CollectionCostAmount) AS CollectionCostAmount,
		IF(p_CollectionCostPercentage IS NOT NULL,IF(p_CollectionCostPercentage='NULL',NULL,p_CollectionCostPercentage),rtr.CollectionCostPercentage) AS CollectionCostPercentage,
		IF(p_RegistrationCostPerNumber IS NOT NULL,IF(p_RegistrationCostPerNumber='NULL',NULL,p_RegistrationCostPerNumber),rtr.RegistrationCostPerNumber) AS RegistrationCostPerNumber,
		IFNULL(p_OneOffCostCurrency,rtr.OneOffCostCurrency) AS OneOffCostCurrency,
		IFNULL(p_MonthlyCostCurrency,rtr.MonthlyCostCurrency) AS MonthlyCostCurrency,
		IFNULL(p_CostPerCallCurrency,rtr.CostPerCallCurrency) AS CostPerCallCurrency,
		IFNULL(p_CostPerMinuteCurrency,rtr.CostPerMinuteCurrency) AS CostPerMinuteCurrency,
		IFNULL(p_SurchargePerCallCurrency,rtr.SurchargePerCallCurrency) AS SurchargePerCallCurrency,
		IFNULL(p_SurchargePerMinuteCurrency,rtr.SurchargePerMinuteCurrency) AS SurchargePerMinuteCurrency,
		IFNULL(p_OutpaymentPerCallCurrency,rtr.OutpaymentPerCallCurrency) AS OutpaymentPerCallCurrency,
		IFNULL(p_OutpaymentPerMinuteCurrency,rtr.OutpaymentPerMinuteCurrency) AS OutpaymentPerMinuteCurrency,
		IFNULL(p_SurchargesCurrency,rtr.SurchargesCurrency) AS SurchargesCurrency,
		IFNULL(p_ChargebackCurrency,rtr.ChargebackCurrency) AS ChargebackCurrency,
		IFNULL(p_CollectionCostAmountCurrency,rtr.CollectionCostAmountCurrency) AS CollectionCostAmountCurrency,
		IFNULL(p_RegistrationCostPerNumberCurrency,rtr.RegistrationCostPerNumberCurrency) AS RegistrationCostPerNumberCurrency,
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
   INNER JOIN
		tblRateTable ON tblRateTable.RateTableId = rtr.RateTableId
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.OriginationRateID,rtr.TimezonesID,City,Tariff) NOT IN (
						SELECT
							RateID,OriginationRateID,TimezonesID,City,Tariff
						FROM
							tblRateTableDIDRate
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND
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
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					(p_Critearea_City IS NULL OR rtr.City = p_Critearea_City) AND
					(p_Critearea_Tariff IS NULL OR rtr.Tariff = p_Critearea_Tariff) AND
					(p_Critearea_AccessType IS NULL OR rtr.AccessType = p_Critearea_AccessType) AND
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


	IF p_action = 1
	THEN

		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 as (select * from tmp_TempRateTableDIDRate_);
			DELETE n1 FROM tmp_TempRateTableDIDRate_ n1, tmp_TempRateTableDIDRate_2 n2 WHERE n1.RateTableDIDRateID < n2.RateTableDIDRateID AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID AND n1.City=n2.City AND n1.Tariff=n2.Tariff AND n1.AccessType=n2.AccessType;
		END IF;

		-- delete records which can be duplicates, we will not update them
		DELETE n1.* FROM tmp_TempRateTableDIDRate_ n1, tblRateTableDIDRate n2 WHERE n1.RateTableDIDRateID <> n2.RateTableDIDRateID AND n1.RateTableID = n2.RateTableID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate = n2.EffectiveDate AND n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.City=n2.City AND n1.Tariff=n2.Tariff AND n1.AccessType=n2.AccessType AND n2.RateTableID=p_RateTableId;

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
			(rtr.TimezonesID = temp.TimezonesID) AND
			(rtr.City = temp.City) AND
			(rtr.Tariff = temp.Tariff) AND
			(rtr.AccessType = temp.AccessType) AND
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
			((rtr.RegistrationCostPerNumber IS NULL && temp.RegistrationCostPerNumber IS NULL) || rtr.RegistrationCostPerNumber = temp.RegistrationCostPerNumber) AND
			((rtr.OneOffCostCurrency IS NULL && temp.OneOffCostCurrency IS NULL) || rtr.OneOffCostCurrency = temp.OneOffCostCurrency) AND
			((rtr.MonthlyCostCurrency IS NULL && temp.MonthlyCostCurrency IS NULL) || rtr.MonthlyCostCurrency = temp.MonthlyCostCurrency) AND
			((rtr.CostPerCallCurrency IS NULL && temp.CostPerCallCurrency IS NULL) || rtr.CostPerCallCurrency = temp.CostPerCallCurrency) AND
			((rtr.CostPerMinuteCurrency IS NULL && temp.CostPerMinuteCurrency IS NULL) || rtr.CostPerMinuteCurrency = temp.CostPerMinuteCurrency) AND
			((rtr.SurchargePerCallCurrency IS NULL && temp.SurchargePerCallCurrency IS NULL) || rtr.SurchargePerCallCurrency = temp.SurchargePerCallCurrency) AND
			((rtr.SurchargePerMinuteCurrency IS NULL && temp.SurchargePerMinuteCurrency IS NULL) || rtr.SurchargePerMinuteCurrency = temp.SurchargePerMinuteCurrency) AND
			((rtr.OutpaymentPerCallCurrency IS NULL && temp.OutpaymentPerCallCurrency IS NULL) || rtr.OutpaymentPerCallCurrency = temp.OutpaymentPerCallCurrency) AND
			((rtr.OutpaymentPerMinuteCurrency IS NULL && temp.OutpaymentPerMinuteCurrency IS NULL) || rtr.OutpaymentPerMinuteCurrency = temp.OutpaymentPerMinuteCurrency) AND
			((rtr.SurchargesCurrency IS NULL && temp.SurchargesCurrency IS NULL) || rtr.SurchargesCurrency = temp.SurchargesCurrency) AND
			((rtr.ChargebackCurrency IS NULL && temp.ChargebackCurrency IS NULL) || rtr.ChargebackCurrency = temp.ChargebackCurrency) AND
			((rtr.CollectionCostAmountCurrency IS NULL && temp.CollectionCostAmountCurrency IS NULL) || rtr.CollectionCostAmountCurrency = temp.CollectionCostAmountCurrency) AND
			((rtr.RegistrationCostPerNumberCurrency IS NULL && temp.RegistrationCostPerNumberCurrency IS NULL) || rtr.RegistrationCostPerNumberCurrency = temp.RegistrationCostPerNumberCurrency);

	END IF;


	-- if rate table is not vendor rate table and rate approval process is on then set approval status to awaiting approval while updating
	IF v_RateTableAppliedTo_!=2 AND v_RateApprovalProcess_=1
	THEN
		UPDATE
			tmp_TempRateTableDIDRate_
		SET
			ApprovedStatus = v_StatusAwaitingApproval_,
			ApprovedBy = NULL,
			ApprovedDate = NULL;


		INSERT INTO tblRateTableDIDRateAA (
			RateTableDIDRateID,
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
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
		SELECT
			RateTableDIDRateId,
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
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
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			IF(p_action=1,v_StatusAwaitingApproval_,v_StatusDelete_) AS ApprovedStatus, -- if action=update then status=aa else status=aadelete
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTableDIDRate_;

		LEAVE ThisSP;

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
		SELECT
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
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
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTableDIDRate_;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTablePKGRateAAUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_RateTablePKGRateAAUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTablePKGRateAAId` LONGTEXT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_OneOffCost` VARCHAR(255),
	IN `p_MonthlyCost` VARCHAR(255),
	IN `p_PackageCostPerMinute` VARCHAR(255),
	IN `p_RecordingCostPerMinute` VARCHAR(255),
	IN `p_OneOffCostCurrency` DECIMAL(18,6),
	IN `p_MonthlyCostCurrency` DECIMAL(18,6),
	IN `p_PackageCostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_RecordingCostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_Critearia_Percentage` VARCHAR(500),
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN
	DECLARE v_Reseller_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Reseller INTO v_Reseller_ FROM tblRateTable WHERE RateTableId=p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTablePKGRate_ (
		`RateTablePKGRateAAId` int(11) NOT NULL,
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`OldOneOffCost` decimal(18,6) NULL DEFAULT NULL,
		`OldMonthlyCost` decimal(18,6) NULL DEFAULT NULL,
		`OldPackageCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OldRecordingCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) NULL DEFAULT NULL,
		`MonthlyCost` decimal(18,6) NULL DEFAULT NULL,
		`PackageCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`RecordingCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`PackageCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`RecordingCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` datetime NOT NULL,
		`EndDate` datetime DEFAULT NULL,
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		`CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ApprovedStatus` tinyint,
		`ApprovedBy` varchar(50),
		`ApprovedDate` datetime,
		`OneOffCostMarginPercent` decimal(18,6) NULL DEFAULT NULL,
		`MonthlyCostMarginPercent` decimal(18,6) NULL DEFAULT NULL,
		`PackageCostPerMinuteMarginPercent` decimal(18,6) NULL DEFAULT NULL,
		`RecordingCostPerMinuteMarginPercent` decimal(18,6) NULL DEFAULT NULL,
		INDEX `tmp_IX_PKGAA_RateTablePKGRateAAID` (`RateTablePKGRateAAID`),
		INDEX `tmp_IX_PKGAA_ApprovedStatus` (`ApprovedStatus`),
		INDEX `tmp_IX_PKGAA_RateTableId_RateID_TimezonesID_EffDate` (`RateID`, `TimezonesID`, `RateTableId`, `EffectiveDate`)
	);

	INSERT INTO tmp_TempRateTablePKGRate_
	(
		`RateTablePKGRateAAId`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`OldOneOffCost`,
		`OldMonthlyCost`,
		`OldPackageCostPerMinute`,
		`OldRecordingCostPerMinute`,
		`OneOffCost`,
		`MonthlyCost`,
		`PackageCostPerMinute`,
		`RecordingCostPerMinute`,
		`OneOffCostCurrency`,
		`MonthlyCostCurrency`,
		`PackageCostPerMinuteCurrency`,
		`RecordingCostPerMinuteCurrency`,
		`EffectiveDate`,
		`EndDate`,
		`created_at`,
		`updated_at`,
		`CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`
	)
	SELECT
		rtr.RateTablePKGRateAAId,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		rtr.OneOffCost,
		rtr.MonthlyCost,
		rtr.PackageCostPerMinute,
		rtr.RecordingCostPerMinute,
		IF(p_OneOffCost IS NOT NULL,IF(p_OneOffCost='NULL',NULL,p_OneOffCost),rtr.OneOffCost) AS OneOffCost,
		IF(p_MonthlyCost IS NOT NULL,IF(p_MonthlyCost='NULL',NULL,p_MonthlyCost),rtr.MonthlyCost) AS MonthlyCost,
		IF(p_PackageCostPerMinute IS NOT NULL,IF(p_PackageCostPerMinute='NULL',NULL,p_PackageCostPerMinute),rtr.PackageCostPerMinute) AS PackageCostPerMinute,
		IF(p_RecordingCostPerMinute IS NOT NULL,IF(p_RecordingCostPerMinute='NULL',NULL,p_RecordingCostPerMinute),rtr.RecordingCostPerMinute) AS RecordingCostPerMinute,
		IFNULL(p_OneOffCostCurrency,rtr.OneOffCostCurrency) AS OneOffCostCurrency,
		IFNULL(p_MonthlyCostCurrency,rtr.MonthlyCostCurrency) AS MonthlyCostCurrency,
		IFNULL(p_PackageCostPerMinuteCurrency,rtr.PackageCostPerMinuteCurrency) AS PackageCostPerMinuteCurrency,
		IFNULL(p_RecordingCostPerMinuteCurrency,rtr.RecordingCostPerMinuteCurrency) AS RecordingCostPerMinuteCurrency,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		rtr.ApprovedStatus,
		NULL AS ApprovedBy,
		NULL AS ApprovedDate
	FROM
		tblRateTablePKGRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.TimezonesID) NOT IN (
						SELECT
							RateID,TimezonesID
						FROM
							tblRateTablePKGRateAA
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTablePKGRateAAID,p_RateTablePKGRateAAID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTablePKGRateAAID,p_RateTablePKGRateAAID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
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

		-- remove rejected rates from temp table while updating so, it can't be update and delete
		DELETE n1 FROM tmp_TempRateTablePKGRate_ n1 WHERE ApprovedStatus = 2;

		IF p_EffectiveDate IS NOT NULL
		THEN
			DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_2;
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTablePKGRate_2 LIKE tmp_TempRateTablePKGRate_;
			INSERT INTO tmp_TempRateTablePKGRate_2 SELECT * FROM tmp_TempRateTablePKGRate_;

			DELETE n1 FROM tmp_TempRateTablePKGRate_ n1, tmp_TempRateTablePKGRate_2 n2 WHERE n1.RateID = n2.RateID AND n1.TimezonesID = n2.TimezonesID AND n1.RateTablePKGRateAAID < n2.RateTablePKGRateAAID;
		END IF;

		-- update current rate, margin and margin percentage
		UPDATE
			tmp_TempRateTablePKGRate_ tr
		LEFT JOIN
			tblRateTablePKGRate rtr ON rtr.RateTablePKGRateID = ( SELECT tmp.RateTablePKGRateID FROM tblRateTablePKGRate tmp INNER JOIN tblRateTable tmp_r ON tmp.RateTableId=tmp_r.RateTableId WHERE tmp.RateID=tr.RateID AND tmp.TimezonesID = tr.TimezonesID AND ((tr.EffectiveDate < CURDATE() AND tmp.EffectiveDate <= CURDATE()) OR tmp.EffectiveDate<=tr.EffectiveDate) AND tmp_r.Reseller=v_Reseller_ ORDER BY EffectiveDate DESC,RateTablePKGRateID DESC LIMIT 1 )
		SET
			OneOffCostMarginPercent = ROUND(
	            IF(
	                (IFNULL(tr.OldOneOffCost,0) - IFNULL(rtr.OneOffCost,0)) <> 0,
	                (((IFNULL(tr.OldOneOffCost,0) - IFNULL(rtr.OneOffCost,0)) * 100) / rtr.OneOffCost),
	                NULL
	            )
	            ,2
	        ),
			MonthlyCostMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldMonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) <> 0,
	                (((IFNULL(tr.OldMonthlyCost,0) - IFNULL(rtr.MonthlyCost,0)) * 100) / rtr.MonthlyCost),
	                NULL
			    )
			    ,2
	        ),
			PackageCostPerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldPackageCostPerMinute,0) - IFNULL(rtr.PackageCostPerMinute,0)) <> 0,
	                (((IFNULL(tr.OldPackageCostPerMinute,0) - IFNULL(rtr.PackageCostPerMinute,0)) * 100) / rtr.PackageCostPerMinute),
	                NULL
			    )
			    ,2
	        ),
			RecordingCostPerMinuteMarginPercent = ROUND(
			    IF(
	                (IFNULL(tr.OldRecordingCostPerMinute,0) - IFNULL(rtr.RecordingCostPerMinute,0)) <> 0,
	                (((IFNULL(tr.OldRecordingCostPerMinute,0) - IFNULL(rtr.RecordingCostPerMinute,0)) * 100) / rtr.RecordingCostPerMinute),
	                NULL
			    )
			    ,2
	        );

	      IF(p_Critearia_Percentage IS NOT NULL)
			THEN

				DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_Final;
				CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTablePKGRate_Final LIKE tmp_TempRateTablePKGRate_;

				SET @v_Where = CONCAT('WHERE (OneOffCostMarginPercent ',p_Critearia_Percentage,' OR MonthlyCostMarginPercent ',p_Critearia_Percentage,' OR PackageCostPerMinuteMarginPercent ',p_Critearia_Percentage,' OR RecordingCostPerMinuteMarginPercent ',p_Critearia_Percentage,')');
	       	SET @stm_filter_percent = CONCAT('
					INSERT INTO tmp_TempRateTablePKGRate_Final SELECT * FROM tmp_TempRateTablePKGRate_ ', @v_Where,';
				');

				PREPARE stmt FROM @stm_filter_percent;
				EXECUTE stmt;
				DEALLOCATE PREPARE stmt;

				DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_;
				ALTER TABLE tmp_TempRateTablePKGRate_Final RENAME tmp_TempRateTablePKGRate_;

			END IF;

		-- delete records which can be duplicates, we will not update them
--		DELETE n1.* FROM tmp_TempRateTablePKGRate_ n1, tblRateTablePKGRateAA n2 WHERE n1.RateTablePKGRateAAID <> n2.RateTablePKGRateAAID AND n1.RateTableID = n2.RateTableID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate = n2.EffectiveDate AND n1.RateID = n2.RateID AND n2.RateTableID=p_RateTableId;

		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
/*		DELETE
			temp
		FROM
			tmp_TempRateTablePKGRate_ temp
		JOIN
			tblRateTablePKGRateAA rtr ON rtr.RateTablePKGRateAAID = temp.RateTablePKGRateAAID
		WHERE
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			(rtr.TimezonesID = temp.TimezonesID) AND
			((rtr.OneOffCost IS NULL && temp.OneOffCost IS NULL) || rtr.OneOffCost = temp.OneOffCost) AND
			((rtr.MonthlyCost IS NULL && temp.MonthlyCost IS NULL) || rtr.MonthlyCost = temp.MonthlyCost) AND
			((rtr.PackageCostPerMinute IS NULL && temp.PackageCostPerMinute IS NULL) || rtr.PackageCostPerMinute = temp.PackageCostPerMinute) AND
			((rtr.RecordingCostPerMinute IS NULL && temp.RecordingCostPerMinute IS NULL) || rtr.RecordingCostPerMinute = temp.RecordingCostPerMinute) AND
			((rtr.OneOffCostCurrency IS NULL && temp.OneOffCostCurrency IS NULL) || rtr.OneOffCostCurrency = temp.OneOffCostCurrency) AND
			((rtr.MonthlyCostCurrency IS NULL && temp.MonthlyCostCurrency IS NULL) || rtr.MonthlyCostCurrency = temp.MonthlyCostCurrency) AND
			((rtr.PackageCostPerMinuteCurrency IS NULL && temp.PackageCostPerMinuteCurrency IS NULL) || rtr.PackageCostPerMinuteCurrency = temp.PackageCostPerMinuteCurrency) AND
			((rtr.RecordingCostPerMinuteCurrency IS NULL && temp.RecordingCostPerMinuteCurrency IS NULL) || rtr.RecordingCostPerMinuteCurrency = temp.RecordingCostPerMinuteCurrency);
*/
	END IF;


	UPDATE
		tblRateTablePKGRateAA rtr
	INNER JOIN
		tmp_TempRateTablePKGRate_ temp ON temp.RateTablePKGRateAAID = rtr.RateTablePKGRateAAID
	SET
		rtr.EndDate = NOW()
	WHERE
		temp.RateTablePKGRateAAID = rtr.RateTablePKGRateAAID;

	CALL prc_ArchiveOldRateTablePKGRateAA(p_RateTableId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN

		INSERT INTO tblRateTablePKGRateAA (
			RateId,
			RateTableId,
			TimezonesID,
			OneOffCost,
			MonthlyCost,
			PackageCostPerMinute,
			RecordingCostPerMinute,
			OneOffCostCurrency,
			MonthlyCostCurrency,
			PackageCostPerMinuteCurrency,
			RecordingCostPerMinuteCurrency,
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
		SELECT
			RateId,
			RateTableId,
			TimezonesID,
			OneOffCost,
			MonthlyCost,
			PackageCostPerMinute,
			RecordingCostPerMinute,
			OneOffCostCurrency,
			MonthlyCostCurrency,
			PackageCostPerMinuteCurrency,
			RecordingCostPerMinuteCurrency,
			EffectiveDate,
			EndDate,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTablePKGRate_
		WHERE
			ApprovedStatus = 0; -- only allow awaiting approval rates to be updated

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableDIDRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		TempRateTableDIDRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableDIDRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableDIDRate (
		RateTableDIDRateID INT,
		RateTableId INT,
		TimezonesID INT,
		OriginationRateID INT,
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		RateId INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		City varchar(50) NOT NULL DEFAULT '',
		Tariff varchar(50) NOT NULL DEFAULT '',
		AccessType varchar(200) NOT NULL DEFAULT '',
		OneOffCost decimal(18,6) DEFAULT NULL,
	  	MonthlyCost decimal(18,6) DEFAULT NULL,
	  	CostPerCall decimal(18,6) DEFAULT NULL,
	  	CostPerMinute decimal(18,6) DEFAULT NULL,
	  	SurchargePerCall decimal(18,6) DEFAULT NULL,
	  	SurchargePerMinute decimal(18,6) DEFAULT NULL,
	  	OutpaymentPerCall decimal(18,6) DEFAULT NULL,
	  	OutpaymentPerMinute decimal(18,6) DEFAULT NULL,
	  	Surcharges decimal(18,6) DEFAULT NULL,
	  	Chargeback decimal(18,6) DEFAULT NULL,
	  	CollectionCostAmount decimal(18,6) DEFAULT NULL,
	  	CollectionCostPercentage decimal(18,6) DEFAULT NULL,
	  	RegistrationCostPerNumber decimal(18,6) DEFAULT NULL,
		OneOffCostCurrency INT(11) NULL DEFAULT NULL,
		MonthlyCostCurrency INT(11) NULL DEFAULT NULL,
		CostPerCallCurrency INT(11) NULL DEFAULT NULL,
		CostPerMinuteCurrency INT(11) NULL DEFAULT NULL,
		SurchargePerCallCurrency INT(11) NULL DEFAULT NULL,
		SurchargePerMinuteCurrency INT(11) NULL DEFAULT NULL,
		OutpaymentPerCallCurrency INT(11) NULL DEFAULT NULL,
		OutpaymentPerMinuteCurrency INT(11) NULL DEFAULT NULL,
		SurchargesCurrency INT(11) NULL DEFAULT NULL,
		ChargebackCurrency INT(11) NULL DEFAULT NULL,
		CollectionCostAmountCurrency INT(11) NULL DEFAULT NULL,
		RegistrationCostPerNumberCurrency INT(11) NULL DEFAULT NULL,
		EffectiveDate DATETIME,
		EndDate Datetime ,
		deleted_at DATETIME,
		INDEX tmp_RateTableDIDRateDiscontinued_RateTableDIDRateID (`RateTableDIDRateID`)
	);

	CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableDIDRate_;

	IF newstringcode = 0
	THEN

		IF (SELECT count(*) FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN

			UPDATE
				tblRateTableDIDRate vr
			INNER JOIN tblRateTableDIDRateChangeLog  vrcl
			on vrcl.RateTableDIDRateID = vr.RateTableDIDRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';


			UPDATE tmp_TempRateTableDIDRate_ tblTempRateTableDIDRate
			JOIN tblRateTableDIDRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableDIDRate.Code
				AND vrcl.OriginationCode = tblTempRateTableDIDRate.OriginationCode
				AND vrcl.City = tblTempRateTableDIDRate.City
				AND vrcl.Tariff = tblTempRateTableDIDRate.Tariff
				AND vrcl.AccessType = tblTempRateTableDIDRate.AccessType
				AND vrcl.TimezonesID = tblTempRateTableDIDRate.TimezonesID
				AND vrcl.EffectiveDate = tblTempRateTableDIDRate.EffectiveDate
			SET
				tblTempRateTableDIDRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;


		END IF;


		IF  p_replaceAllRates = 1
		THEN

			UPDATE tblRateTableDIDRate
				SET tblRateTableDIDRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;

		END IF;



		-- complete file
		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableDIDRate(
				RateTableDIDRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
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
				EffectiveDate,
				EndDate,
				deleted_at
			)
			SELECT DISTINCT
				tblRateTableDIDRate.RateTableDIDRateID,
				p_RateTableId AS RateTableId,
				tblRateTableDIDRate.TimezonesID,
				tblRateTableDIDRate.OriginationRateID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRateTableDIDRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
            tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblRateTableDIDRate.EffectiveDate,
				IFNULL(tblRateTableDIDRate.EndDate,date(now())) ,
				now() AS deleted_at
			FROM tblRateTableDIDRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableDIDRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	/*JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableDIDRate.TimezonesID*/
			LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				ON tblTempRateTableDIDRate.Code = tblRate.Code
				AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
				AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
				AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
				AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
				AND  tblTempRateTableDIDRate.ProcessId = p_processId
				AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableDIDRate.RateTableId = p_RateTableId
				AND tblTempRateTableDIDRate.Code IS NULL
				AND ( tblRateTableDIDRate.EndDate is NULL OR tblRateTableDIDRate.EndDate <= date(now()) )
			ORDER BY RateTableDIDRateID ASC;


			UPDATE tblRateTableDIDRate
			JOIN tmp_Delete_RateTableDIDRate ON tblRateTableDIDRate.RateTableDIDRateID = tmp_Delete_RateTableDIDRate.RateTableDIDRateID
				SET tblRateTableDIDRate.EndDate = date(now())
			WHERE
				tblRateTableDIDRate.RateTableId = p_RateTableId;

		END IF;


		IF ( (SELECT count(*) FROM tblRateTableDIDRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN

			call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 AS (SELECT * FROM tmp_TempRateTableDIDRate_);

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
					tblTempRateTableDIDRate.Code,
					MAX(tblTempRateTableDIDRate.Description) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.Code
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
					tblTempRateTableDIDRate.OriginationCode AS Code,
					MAX(tblTempRateTableDIDRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.OriginationCode IS NOT NULL AND tblTempRateTableDIDRate.OriginationCode != ''
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.OriginationCode
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
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
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
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;


		UPDATE tblRateTableDIDRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableDIDRate.RateId
			AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
			AND OriginationRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
			AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
			AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
			AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
			AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
			AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTableDIDRate.EndDate = IFNULL(tblTempRateTableDIDRate.EndDate,date(now()))
		WHERE tblRateTableDIDRate.RateTableId = p_RateTableId;


		DELETE tblTempRateTableDIDRate
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
			AND IFNULL(tblTempRateTableDIDRate.OneOffCost,0) = IFNULL(tblRateTableDIDRate.OneOffCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) = IFNULL(tblRateTableDIDRate.MonthlyCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerCall,0) = IFNULL(tblRateTableDIDRate.CostPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) = IFNULL(tblRateTableDIDRate.CostPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) = IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) = IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.Surcharges,0) = IFNULL(tblRateTableDIDRate.Surcharges,0)
        	AND IFNULL(tblTempRateTableDIDRate.Chargeback,0) = IFNULL(tblRateTableDIDRate.Chargeback,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) = IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) = IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
        	AND IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) = IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
		WHERE
			tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');


		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		UPDATE tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			/*
				this one condition removed at 2019-09-06 as per SI Requirement
				if upload any timezones rate with paritial file upload then all old rates on all timezones for that prefix need to deleted.
			*/
		--	AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
		SET tblRateTableDIDRate.EndDate = NOW()
		WHERE
			tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND (
				tblTempRateTableDIDRate.City <> tblRateTableDIDRate.City
				OR tblTempRateTableDIDRate.Tariff <> tblRateTableDIDRate.Tariff
				OR tblTempRateTableDIDRate.AccessType <> tblRateTableDIDRate.AccessType
				OR tblTempRateTableDIDRate.OneOffCost <> tblRateTableDIDRate.OneOffCost
				OR tblTempRateTableDIDRate.MonthlyCost <> tblRateTableDIDRate.MonthlyCost
				OR tblTempRateTableDIDRate.CostPerCall <> tblRateTableDIDRate.CostPerCall
				OR tblTempRateTableDIDRate.CostPerMinute <> tblRateTableDIDRate.CostPerMinute
				OR tblTempRateTableDIDRate.SurchargePerCall <> tblRateTableDIDRate.SurchargePerCall
				OR tblTempRateTableDIDRate.SurchargePerMinute <> tblRateTableDIDRate.SurchargePerMinute
				OR tblTempRateTableDIDRate.OutpaymentPerCall <> tblRateTableDIDRate.OutpaymentPerCall
				OR tblTempRateTableDIDRate.OutpaymentPerMinute <> tblRateTableDIDRate.OutpaymentPerMinute
				OR tblTempRateTableDIDRate.Surcharges <> tblRateTableDIDRate.Surcharges
				OR tblTempRateTableDIDRate.Chargeback <> tblRateTableDIDRate.Chargeback
				OR tblTempRateTableDIDRate.CollectionCostAmount <> tblRateTableDIDRate.CollectionCostAmount
				OR tblTempRateTableDIDRate.CollectionCostPercentage <> tblRateTableDIDRate.CollectionCostPercentage
				OR tblTempRateTableDIDRate.RegistrationCostPerNumber <> tblRateTableDIDRate.RegistrationCostPerNumber
			)
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND
			-- DATE_FORMAT (tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
			(
				( -- if future rates then delete same date existing records
					DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d') > CURDATE() AND
					DATE_FORMAT (tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
				)
				OR
				( -- if current rates then delete current or older records
					DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d') <= CURDATE() AND
					DATE_FORMAT (tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') <= DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
				)
			);


		call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

		SET @stm1 = CONCAT('
			INSERT INTO tblRateTableDIDRate (
				RateTableId,
				TimezonesID,
				OriginationRateID,
				RateId,
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
				EffectiveDate,
				EndDate,
				ApprovedStatus
			)
			SELECT DISTINCT
				',p_RateTableId,' AS RateTableId,
				tblTempRateTableDIDRate.TimezonesID,
				IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
				tblRate.RateID,
				tblTempRateTableDIDRate.City,
				tblTempRateTableDIDRate.Tariff,
				tblTempRateTableDIDRate.AccessType,
		');

		SET @stm2 = '';
		IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
        THEN
			IF p_CurrencyID = v_CompanyCurrencyID_
            THEN
				SET @stm2 = CONCAT('
				    ( tblTempRateTableDIDRate.OneOffCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OneOffCost,
				    ( tblTempRateTableDIDRate.MonthlyCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS MonthlyCost,
				    ( tblTempRateTableDIDRate.CostPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerCall,
				    ( tblTempRateTableDIDRate.CostPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerMinute,
				    ( tblTempRateTableDIDRate.SurchargePerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerCall,
				    ( tblTempRateTableDIDRate.SurchargePerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerMinute,
				    ( tblTempRateTableDIDRate.OutpaymentPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerCall,
				    ( tblTempRateTableDIDRate.OutpaymentPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerMinute,
				    ( tblTempRateTableDIDRate.Surcharges  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Surcharges,
				    ( tblTempRateTableDIDRate.Chargeback  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Chargeback,
				    ( tblTempRateTableDIDRate.CollectionCostAmount  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostAmount,
				    ( tblTempRateTableDIDRate.CollectionCostPercentage  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostPercentage,
				    ( tblTempRateTableDIDRate.RegistrationCostPerNumber  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS RegistrationCostPerNumber,
				');
			ELSE
				SET @stm2 = CONCAT('
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OneOffCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS MonthlyCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Surcharges,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Chargeback,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostAmount,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostPercentage,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS RegistrationCostPerNumber,
				');
			END IF;
        ELSE
            SET @stm2 = CONCAT('
                    tblTempRateTableDIDRate.OneOffCost AS OneOffCost,
                    tblTempRateTableDIDRate.MonthlyCost AS MonthlyCost,
                    tblTempRateTableDIDRate.CostPerCall AS CostPerCall,
                    tblTempRateTableDIDRate.CostPerMinute AS CostPerMinute,
                    tblTempRateTableDIDRate.SurchargePerCall AS SurchargePerCall,
                    tblTempRateTableDIDRate.SurchargePerMinute AS SurchargePerMinute,
                    tblTempRateTableDIDRate.OutpaymentPerCall AS OutpaymentPerCall,
                    tblTempRateTableDIDRate.OutpaymentPerMinute AS OutpaymentPerMinute,
                    tblTempRateTableDIDRate.Surcharges AS Surcharges,
                    tblTempRateTableDIDRate.Chargeback AS Chargeback,
                    tblTempRateTableDIDRate.CollectionCostAmount AS CollectionCostAmount,
                    tblTempRateTableDIDRate.CollectionCostPercentage AS CollectionCostPercentage,
                    tblTempRateTableDIDRate.RegistrationCostPerNumber AS RegistrationCostPerNumber,
                ');
		END IF;

		SET @stm3 = CONCAT('
				tblTempRateTableDIDRate.OneOffCostCurrency,
				tblTempRateTableDIDRate.MonthlyCostCurrency,
				tblTempRateTableDIDRate.CostPerCallCurrency,
				tblTempRateTableDIDRate.CostPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargePerCallCurrency,
				tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
				tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
				tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargesCurrency,
				tblTempRateTableDIDRate.ChargebackCurrency,
				tblTempRateTableDIDRate.CollectionCostAmountCurrency,
				tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblTempRateTableDIDRate.EffectiveDate,
				tblTempRateTableDIDRate.EndDate,
				 -- if rate table is not vendor rate table and Rate Approval Process is on then rate will be upload as not approved
				IF(',v_RateTableAppliedTo_,' !=2,IF(',v_RateApprovalProcess_,'=1,0,1),1) AS ApprovedStatus
			FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTableDIDRate.Code
				AND tblRate.CompanyID = ',p_companyId,'
				AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
				AND OriginationRate.CompanyID = ',p_companyId,'
				AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			LEFT JOIN tblRateTableDIDRate
				ON tblRate.RateID = tblRateTableDIDRate.RateId
				AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
				AND tblRateTableDIDRate.RateTableId = ',p_RateTableId,'
				AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
				AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
				AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
				AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
				AND tblTempRateTableDIDRate.EffectiveDate = tblRateTableDIDRate.EffectiveDate
			WHERE tblRateTableDIDRate.RateTableDIDRateID IS NULL
				AND tblTempRateTableDIDRate.Change NOT IN ("Delete", "R", "D", "Blocked","Block")
				AND tblTempRateTableDIDRate.EffectiveDate >= DATE_FORMAT (NOW(), "%Y-%m-%d");
		');

		SET @stm4 = CONCAT(@stm1,@stm2,@stm3);

		PREPARE stm4 FROM @stm4;
		EXECUTE stm4;
		DEALLOCATE PREPARE stm4;

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
			from 	tblRateTableDIDRate
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

				UPDATE  tblRateTableDIDRate vr1
				inner join
				(
					select
						RateTableId,
						OriginationRateID,
						RateID,
						EffectiveDate,
						TimezonesID,
						City,
						Tariff,
						AccessType
					FROM tblRateTableDIDRate
					WHERE RateTableId = p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
					AND vr1.OriginationRateID = tmpvr.OriginationRateID
					AND vr1.RateID = tmpvr.RateID
					AND vr1.TimezonesID = tmpvr.TimezonesID
					AND vr1.City = tmpvr.City
					AND vr1.Tariff = tmpvr.Tariff
					AND vr1.AccessType = tmpvr.AccessType
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

	call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

	DELETE  FROM tblTempRateTableDIDRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableDIDRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableDIDRateAA`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		TempRateTableDIDRateID INT,
		RateTableDIDRateID INT DEFAULT 0,
		`CodeDeckId` INT ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`ApprovedStatus` TINYINT(4) DEFAULT 0,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableDIDRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableDIDRate (
		TempRateTableDIDRateID INT DEFAULT 0,
		RateTableDIDRateID INT,
		`CodeDeckId` INT ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`ApprovedStatus` TINYINT(4) DEFAULT 0,
		INDEX tmp_RateTableDIDRateDiscontinued_RateTableDIDRateID (`RateTableDIDRateID`)
	);

	CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableDIDRate_;

	IF newstringcode = 0
	THEN


		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableDIDRate(
				RateTableDIDRateID,
				CodeDeckId,
				TimezonesID,
				OriginationCode,
				OriginationDescription,
				Code,
				Description,
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
				EffectiveDate,
				EndDate,
				`Change`,
				ProcessId,
				DialStringPrefix,
				ApprovedStatus
			)
			SELECT DISTINCT
				tblRateTableDIDRate.RateTableDIDRateID,
				tblRateTable.CodeDeckId AS CodeDeckId,
				tblRateTableDIDRate.TimezonesID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRate.Code,
				tblRate.Description,
				tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
				tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblRateTableDIDRate.EffectiveDate,
				NULL AS EndDate,
				'Delete' AS `Change`,
				p_processId AS ProcessId,
				'' AS DialStringPrefix,
				3 AS ApprovedStatus
			FROM tblRateTableDIDRate
			JOIN tblRateTable
				ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
			JOIN tblRate
				ON tblRate.RateID = tblRateTableDIDRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	/*JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableDIDRate.TimezonesID*/
			LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				ON tblTempRateTableDIDRate.Code = tblRate.Code
				AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
				AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
				AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
				AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
				AND  tblTempRateTableDIDRate.ProcessId = p_processId
				AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableDIDRate.RateTableId = p_RateTableId
				AND tblTempRateTableDIDRate.Code IS NULL
				AND ( tblRateTableDIDRate.EndDate is NULL OR tblRateTableDIDRate.EndDate <= date(now()) )
			ORDER BY RateTableDIDRateID ASC;


		END IF;




		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 AS (SELECT * FROM tmp_TempRateTableDIDRate_);

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
					tblTempRateTableDIDRate.Code,
					MAX(tblTempRateTableDIDRate.Description) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.Code
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
					tblTempRateTableDIDRate.OriginationCode AS Code,
					MAX(tblTempRateTableDIDRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.OriginationCode IS NOT NULL AND tblTempRateTableDIDRate.OriginationCode != ''
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.OriginationCode
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
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
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
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;


		DELETE tblTempRateTableDIDRate
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRateAA AS tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
			AND IFNULL(tblTempRateTableDIDRate.OneOffCost,0) = IFNULL(tblRateTableDIDRate.OneOffCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) = IFNULL(tblRateTableDIDRate.MonthlyCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerCall,0) = IFNULL(tblRateTableDIDRate.CostPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) = IFNULL(tblRateTableDIDRate.CostPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) = IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) = IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.Surcharges,0) = IFNULL(tblRateTableDIDRate.Surcharges,0)
        	AND IFNULL(tblTempRateTableDIDRate.Chargeback,0) = IFNULL(tblRateTableDIDRate.Chargeback,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) = IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) = IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
        	AND IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) = IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
		WHERE
			tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');


		-- SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		UPDATE tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRateAA AS tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
		SET tblRateTableDIDRate.EndDate = NOW()
		WHERE
			tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND (
				tblTempRateTableDIDRate.City <> tblRateTableDIDRate.City
				OR tblTempRateTableDIDRate.Tariff <> tblRateTableDIDRate.Tariff
				OR tblTempRateTableDIDRate.AccessType <> tblRateTableDIDRate.AccessType
				OR tblTempRateTableDIDRate.OneOffCost <> tblRateTableDIDRate.OneOffCost
				OR tblTempRateTableDIDRate.MonthlyCost <> tblRateTableDIDRate.MonthlyCost
				OR tblTempRateTableDIDRate.CostPerCall <> tblRateTableDIDRate.CostPerCall
				OR tblTempRateTableDIDRate.CostPerMinute <> tblRateTableDIDRate.CostPerMinute
				OR tblTempRateTableDIDRate.SurchargePerCall <> tblRateTableDIDRate.SurchargePerCall
				OR tblTempRateTableDIDRate.SurchargePerMinute <> tblRateTableDIDRate.SurchargePerMinute
				OR tblTempRateTableDIDRate.OutpaymentPerCall <> tblRateTableDIDRate.OutpaymentPerCall
				OR tblTempRateTableDIDRate.OutpaymentPerMinute <> tblRateTableDIDRate.OutpaymentPerMinute
				OR tblTempRateTableDIDRate.Surcharges <> tblRateTableDIDRate.Surcharges
				OR tblTempRateTableDIDRate.Chargeback <> tblRateTableDIDRate.Chargeback
				OR tblTempRateTableDIDRate.CollectionCostAmount <> tblRateTableDIDRate.CollectionCostAmount
				OR tblTempRateTableDIDRate.CollectionCostPercentage <> tblRateTableDIDRate.CollectionCostPercentage
				OR tblTempRateTableDIDRate.RegistrationCostPerNumber <> tblRateTableDIDRate.RegistrationCostPerNumber
			)
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d');


		call prc_ArchiveOldRateTableDIDRateAA(p_RateTableId, NULL,p_UserName);

		SET @stm1 = CONCAT('
			INSERT INTO tblRateTableDIDRateAA (
				RateTableId,
				TimezonesID,
				OriginationRateID,
				RateId,
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
				EffectiveDate,
				EndDate,
				ApprovedStatus,
				RateTableDIDRateID
			)
			SELECT DISTINCT
				',p_RateTableId,' AS RateTableId,
				tblTempRateTableDIDRate.TimezonesID,
				IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
				tblRate.RateID,
				tblTempRateTableDIDRate.City,
				tblTempRateTableDIDRate.Tariff,
				tblTempRateTableDIDRate.AccessType,
		');

		SET @stm2 = '';
		IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
        THEN
			IF p_CurrencyID = v_CompanyCurrencyID_
            THEN
				SET @stm2 = CONCAT('
				    ( tblTempRateTableDIDRate.OneOffCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OneOffCost,
				    ( tblTempRateTableDIDRate.MonthlyCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS MonthlyCost,
				    ( tblTempRateTableDIDRate.CostPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerCall,
				    ( tblTempRateTableDIDRate.CostPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerMinute,
				    ( tblTempRateTableDIDRate.SurchargePerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerCall,
				    ( tblTempRateTableDIDRate.SurchargePerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerMinute,
				    ( tblTempRateTableDIDRate.OutpaymentPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerCall,
				    ( tblTempRateTableDIDRate.OutpaymentPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerMinute,
				    ( tblTempRateTableDIDRate.Surcharges  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Surcharges,
				    ( tblTempRateTableDIDRate.Chargeback  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Chargeback,
				    ( tblTempRateTableDIDRate.CollectionCostAmount  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostAmount,
				    ( tblTempRateTableDIDRate.CollectionCostPercentage  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostPercentage,
				    ( tblTempRateTableDIDRate.RegistrationCostPerNumber  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS RegistrationCostPerNumber,
				');
			ELSE
				SET @stm2 = CONCAT('
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OneOffCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS MonthlyCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Surcharges,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Chargeback,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostAmount,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostPercentage,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS RegistrationCostPerNumber,
				');
			END IF;
        ELSE
            SET @stm2 = CONCAT('
                    tblTempRateTableDIDRate.OneOffCost AS OneOffCost,
                    tblTempRateTableDIDRate.MonthlyCost AS MonthlyCost,
                    tblTempRateTableDIDRate.CostPerCall AS CostPerCall,
                    tblTempRateTableDIDRate.CostPerMinute AS CostPerMinute,
                    tblTempRateTableDIDRate.SurchargePerCall AS SurchargePerCall,
                    tblTempRateTableDIDRate.SurchargePerMinute AS SurchargePerMinute,
                    tblTempRateTableDIDRate.OutpaymentPerCall AS OutpaymentPerCall,
                    tblTempRateTableDIDRate.OutpaymentPerMinute AS OutpaymentPerMinute,
                    tblTempRateTableDIDRate.Surcharges AS Surcharges,
                    tblTempRateTableDIDRate.Chargeback AS Chargeback,
                    tblTempRateTableDIDRate.CollectionCostAmount AS CollectionCostAmount,
                    tblTempRateTableDIDRate.CollectionCostPercentage AS CollectionCostPercentage,
                    tblTempRateTableDIDRate.RegistrationCostPerNumber AS RegistrationCostPerNumber,
                ');
		END IF;

		SET @stm3 = CONCAT('
				tblTempRateTableDIDRate.OneOffCostCurrency,
				tblTempRateTableDIDRate.MonthlyCostCurrency,
				tblTempRateTableDIDRate.CostPerCallCurrency,
				tblTempRateTableDIDRate.CostPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargePerCallCurrency,
				tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
				tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
				tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargesCurrency,
				tblTempRateTableDIDRate.ChargebackCurrency,
				tblTempRateTableDIDRate.CollectionCostAmountCurrency,
				tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblTempRateTableDIDRate.EffectiveDate,
				tblTempRateTableDIDRate.EndDate,
				tblTempRateTableDIDRate.ApprovedStatus,
				tblTempRateTableDIDRate.RateTableDIDRateID
			FROM
			(
				SELECT * FROM tmp_TempRateTableDIDRate_
				WHERE tmp_TempRateTableDIDRate_.Change NOT IN ("Delete", "R", "D", "Blocked","Block")
				AND tmp_TempRateTableDIDRate_.EffectiveDate >= DATE_FORMAT (NOW(), "%Y-%m-%d")

				UNION

				SELECT * FROM tmp_Delete_RateTableDIDRate

			) as tblTempRateTableDIDRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTableDIDRate.Code
				AND tblRate.CompanyID = ',p_companyId,'
				AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
				AND OriginationRate.CompanyID = ',p_companyId,'
				AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId;
		');

		SET @stm4 = CONCAT(@stm1,@stm2,@stm3);

		PREPARE stm4 FROM @stm4;
		EXECUTE stm4;
		DEALLOCATE PREPARE stm4;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	call prc_ArchiveOldRateTableDIDRateAA(p_RateTableId, NULL,p_UserName);

	DELETE  FROM tblTempRateTableDIDRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateCheckDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateCheckDialstringAndDupliacteCode`(
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_2` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_3` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitRateTableDIDRate(p_processId,p_dialcodeSeparator,p_seperatecolumn);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_RateTableDIDRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_RateTableDIDRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_RateTableDIDRate_2 as (SELECT * FROM tmp_split_RateTableDIDRate_);

	-- delete duplicate records
	DELETE n1 FROM tmp_split_RateTableDIDRate_ n1
	INNER JOIN
	(
		SELECT MAX(TempRateTableDIDRateID) AS TempRateTableDIDRateID,EffectiveDate,OriginationCode,Code,DialStringPrefix,TimezonesID,City,Tariff,AccessType,
			OneOffCost, MonthlyCost, CostPerCall, CostPerMinute, SurchargePerCall, SurchargePerMinute, OutpaymentPerCall,
			OutpaymentPerMinute, Surcharges, Chargeback, CollectionCostAmount, CollectionCostPercentage, RegistrationCostPerNumber
		FROM tmp_split_RateTableDIDRate_2 WHERE ProcessId = p_processId
		GROUP BY
			OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType,
			OneOffCost, MonthlyCost, CostPerCall, CostPerMinute, SurchargePerCall, SurchargePerMinute, OutpaymentPerCall,
			OutpaymentPerMinute, Surcharges, Chargeback, CollectionCostAmount, CollectionCostPercentage, RegistrationCostPerNumber
		HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code
		AND ((n1.OriginationCode IS NULL AND n2.OriginationCode IS NULL) OR (n1.OriginationCode = n2.OriginationCode))
		AND n2.EffectiveDate = n1.EffectiveDate
		AND ((n2.DialStringPrefix IS NULL AND n1.DialStringPrefix IS NULL) OR (n2.DialStringPrefix = n1.DialStringPrefix))
		AND n2.TimezonesID = n1.TimezonesID
		AND ((n2.City IS NULL AND n1.City IS NULL) OR n2.City = n1.City)
		AND ((n2.Tariff IS NULL AND n1.Tariff IS NULL) OR n2.Tariff = n1.Tariff)
		AND ((n2.AccessType IS NULL AND n1.AccessType IS NULL) OR n2.AccessType = n1.AccessType)
		AND ((n2.OneOffCost IS NULL AND n1.OneOffCost IS NULL) OR n2.OneOffCost = n1.OneOffCost)
		AND ((n2.MonthlyCost IS NULL AND n1.MonthlyCost IS NULL) OR n2.MonthlyCost = n1.MonthlyCost)
		AND ((n2.CostPerCall IS NULL AND n1.CostPerCall IS NULL) OR n2.CostPerCall = n1.CostPerCall)
		AND ((n2.CostPerMinute IS NULL AND n1.CostPerMinute IS NULL) OR n2.CostPerMinute = n1.CostPerMinute)
		AND ((n2.SurchargePerCall IS NULL AND n1.SurchargePerCall IS NULL) OR n2.SurchargePerCall = n1.SurchargePerCall)
		AND ((n2.SurchargePerMinute IS NULL AND n1.SurchargePerMinute IS NULL) OR n2.SurchargePerMinute = n1.SurchargePerMinute)
		AND ((n2.OutpaymentPerCall IS NULL AND n1.OutpaymentPerCall IS NULL) OR n2.OutpaymentPerCall = n1.OutpaymentPerCall)
		AND ((n2.OutpaymentPerMinute IS NULL AND n1.OutpaymentPerMinute IS NULL) OR n2.OutpaymentPerMinute = n1.OutpaymentPerMinute)
		AND ((n2.Surcharges IS NULL AND n1.Surcharges IS NULL) OR n2.Surcharges = n1.Surcharges)
		AND ((n2.Chargeback IS NULL AND n1.Chargeback IS NULL) OR n2.Chargeback = n1.Chargeback)
		AND ((n2.CollectionCostAmount IS NULL AND n1.CollectionCostAmount IS NULL) OR n2.CollectionCostAmount = n1.CollectionCostAmount)
		AND ((n2.CollectionCostPercentage IS NULL AND n1.CollectionCostPercentage IS NULL) OR n2.CollectionCostPercentage = n1.CollectionCostPercentage)
		AND ((n2.RegistrationCostPerNumber IS NULL AND n1.RegistrationCostPerNumber IS NULL) OR n2.RegistrationCostPerNumber = n1.RegistrationCostPerNumber)
		AND n1.TempRateTableDIDRateID < n2.TempRateTableDIDRateID
	WHERE
		n1.ProcessId = p_processId;

	INSERT INTO tmp_TempRateTableDIDRate_
	(
		`TempRateTableDIDRateID`,
		CodeDeckId,
		TimezonesID,
		OriginationCode,
		OriginationDescription,
		Code,
		Description,
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
		EffectiveDate,
		EndDate,
		`Change`,
		ProcessId,
		DialStringPrefix
	)
	SELECT DISTINCT
		`TempRateTableDIDRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`OriginationCode`,
		`OriginationDescription`,
		`Code`,
		`Description`,
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
		`EffectiveDate`,
		`EndDate`,
		`Change`,
		`ProcessId`,
		`DialStringPrefix`
	FROM tmp_split_RateTableDIDRate_
	WHERE tmp_split_RateTableDIDRate_.ProcessId = p_processId;

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_TempRateTableDIDRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempRateTableDIDRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	SELECT COUNT(*) INTO totalduplicatecode FROM(
	SELECT COUNT(code) as c,code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType HAVING c>1) AS tbl;

	IF  totalduplicatecode > 0
	THEN

		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT OriginationCode,Code, 1 as a FROM(
		SELECT COUNT(TempRateTableDIDRateID) as c, OriginationCode, Code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
			CONCAT(IF(OriginationCode IS NOT NULL,CONCAT(OriginationCode,'-'),''), Code, ' DUPLICATE CODE')
		FROM(
			SELECT COUNT(TempRateTableDIDRateID) as c, OriginationCode, Code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType HAVING c>1) AS tbl;
	END IF;

	-- this code is no longer in use as we have removed dialstring mapping from did and pkg rate upload
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

			SELECT  COUNT(*) as COUNT INTO totaldialstringcode
			FROM tmp_TempRateTableDIDRate_ vr
			LEFT JOIN tmp_DialString_ ds
				ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
			WHERE vr.ProcessId = p_processId
				AND ds.DialStringID IS NULL
				AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			IF totaldialstringcode > 0
			THEN

				INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
				  SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
					FROM tmp_TempRateTableDIDRate_ vr
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

				SELECT  COUNT(*) as COUNT INTO totaldialstringcode
				FROM tmp_TempRateTableDIDRate_ vr
					LEFT JOIN tmp_DialString_ ds
						ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
					WHERE vr.ProcessId = p_processId
						AND ds.DialStringID IS NULL
						AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				INSERT INTO tmp_JobLog_ (Message)
					  SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
					  	FROM tmp_TempRateTableDIDRate_ vr
							LEFT JOIN tmp_DialString_ ds
								ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
							WHERE vr.ProcessId = p_processId
								AND ds.DialStringID IS NULL
								AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
			END IF;

			IF totaldialstringcode = 0
			THEN

				INSERT INTO tmp_RateTableDIDRateDialString_
				SELECT DISTINCT
					`TempRateTableDIDRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`DialString`,
					CASE WHEN ds.Description IS NULL OR ds.Description = ''
					THEN
						tblTempRateTableDIDRate.Description
					ELSE
						ds.Description
					END
					AS Description,
					`CityTariff`,
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
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					tblTempRateTableDIDRate.DialStringPrefix as DialStringPrefix
				FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				INNER JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableDIDRate.Code = ds.ChargeCode AND tblTempRateTableDIDRate.DialStringPrefix = '') OR (tblTempRateTableDIDRate.DialStringPrefix != '' AND tblTempRateTableDIDRate.DialStringPrefix =  ds.DialString AND tblTempRateTableDIDRate.Code = ds.ChargeCode  ))
				WHERE tblTempRateTableDIDRate.ProcessId = p_processId
					AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


				INSERT INTO tmp_RateTableDIDRateDialString_2
				SELECT *  FROM tmp_RateTableDIDRateDialString_ where DialStringPrefix!='';

				Delete From tmp_RateTableDIDRateDialString_
				Where DialStringPrefix = ''
				And Code IN (Select DialStringPrefix From tmp_RateTableDIDRateDialString_2);

				INSERT INTO tmp_RateTableDIDRateDialString_3
				SELECT * FROM tmp_RateTableDIDRateDialString_;


				DELETE  FROM tmp_TempRateTableDIDRate_ WHERE  ProcessId = p_processId;

				INSERT INTO tmp_TempRateTableDIDRate_(
					`TempRateTableDIDRateID`,
					CodeDeckId,
					TimezonesID,
					OriginationCode,
					OriginationDescription,
					Code,
					Description,
					CityTariff,
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
					EffectiveDate,
					EndDate,
					`Change`,
					ProcessId,
					DialStringPrefix
				)
				SELECT DISTINCT
					`TempRateTableDIDRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`Code`,
					`Description`,
					`CityTariff`,
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
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					DialStringPrefix
				FROM tmp_RateTableDIDRateDialString_3;

			END IF;

		END IF;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitRateTableDIDRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempRateTableDIDRateID_ INT;
	DECLARE v_OriginationCode_ TEXT;
	DECLARE v_OriginationCountryCode_ VARCHAR(500);
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN

		DROP TEMPORARY TABLE IF EXISTS `my_splits`;
		CREATE TEMPORARY TABLE `my_splits` (
			`TempRateTableDIDRateID` INT(11) NULL DEFAULT NULL,
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
				INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableDIDRateID , FnStringSplit(OriginationCode, p_dialcodeSeparator, i), OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableDIDRate
				WHERE FnStringSplit(OriginationCode, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			ELSE
				INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableDIDRateID , OriginationCode, OriginationCountryCode, FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempRateTableDIDRate
				WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			END IF;

			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;

		UPDATE my_splits SET OriginationCode = trim(OriginationCode), Code = trim(Code);



		INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
		SELECT TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableDIDRate
		WHERE
			(
				(p_seperatecolumn = 1 AND (OriginationCountryCode IS NOT NULL AND OriginationCountryCode <> '') AND (OriginationCode IS NULL OR OriginationCode = '')) OR
				(p_seperatecolumn = 2 AND (CountryCode IS NOT NULL AND CountryCode <> '') AND (Code IS NULL OR Code = ''))
			)
		AND ProcessId = p_processId;


		DROP TEMPORARY TABLE IF EXISTS tmp_newratetable_splite_;
		CREATE TEMPORARY TABLE tmp_newratetable_splite_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			TempRateTableDIDRateID INT(11) NULL DEFAULT NULL,
			OriginationCode VARCHAR(500) NULL DEFAULT NULL,
			OriginationCountryCode VARCHAR(500) NULL DEFAULT NULL,
			Code VARCHAR(500) NULL DEFAULT NULL,
			CountryCode VARCHAR(500) NULL DEFAULT NULL
		);

		INSERT INTO tmp_newratetable_splite_(TempRateTableDIDRateID,OriginationCode,OriginationCountryCode,Code,CountryCode)
		SELECT
			TempRateTableDIDRateID,
			OriginationCode,
			OriginationCountryCode,
			Code,
			CountryCode
		FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableDIDRateID IS NOT NULL;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newratetable_splite_);

		WHILE v_pointer_ <= v_rowCount_
		DO
			SET v_TempRateTableDIDRateID_ = (SELECT TempRateTableDIDRateID FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCode_ = (SELECT OriginationCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCountryCode_ = (SELECT OriginationCountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_Code_ = (SELECT Code FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);

			Call prc_SplitAndInsertRateTableDIDRate(v_TempRateTableDIDRateID_,p_seperatecolumn,v_OriginationCode_,v_OriginationCountryCode_,v_Code_,v_CountryCode_);

			SET v_pointer_ = v_pointer_ + 1;
		END WHILE;

		DELETE FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableDIDRateID IS NOT NULL;

		DELETE FROM my_splits
		WHERE (Code = '' OR Code IS NULL) AND (CountryCode = '' OR CountryCode IS NULL);

		INSERT INTO tmp_split_RateTableDIDRate_
		SELECT DISTINCT
			my_splits.TempRateTableDIDRateID as `TempRateTableDIDRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(my_splits.OriginationCountryCode,''),my_splits.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
			`Description`,
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
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`DialStringPrefix`
		FROM my_splits
		INNER JOIN tblTempRateTableDIDRate
			ON my_splits.TempRateTableDIDRateID = tblTempRateTableDIDRate.TempRateTableDIDRateID
		WHERE	tblTempRateTableDIDRate.ProcessId = p_processId;

	END IF;

	IF p_dialcodeSeparator = 'null'
	THEN

		INSERT INTO tmp_split_RateTableDIDRate_
		SELECT DISTINCT
			`TempRateTableDIDRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(tblTempRateTableDIDRate.OriginationCountryCode,''),tblTempRateTableDIDRate.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(tblTempRateTableDIDRate.CountryCode,''),tblTempRateTableDIDRate.Code) as Code,
			`Description`,
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
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`DialStringPrefix`
		FROM tblTempRateTableDIDRate
		WHERE ProcessId = p_processId;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableDIDRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
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

    DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
    CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
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
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	ALTER TABLE
		`tmp_TempRateTableDIDRate_`
	ADD Column `NewOneOffCost` decimal(18, 6),
	ADD Column `NewMonthlyCost` decimal(18, 6),
	ADD Column `NewCostPerCall` decimal(18, 6),
	ADD Column `NewCostPerMinute` decimal(18, 6),
	ADD Column `NewSurchargePerCall` decimal(18, 6),
	ADD Column `NewSurchargePerMinute` decimal(18, 6),
	ADD Column `NewOutpaymentPerCall` decimal(18, 6),
	ADD Column `NewOutpaymentPerMinute` decimal(18, 6),
	ADD Column `NewSurcharges` decimal(18, 6),
	ADD Column `NewChargeback` decimal(18, 6),
	ADD Column `NewCollectionCostAmount` decimal(18, 6),
	ADD Column `NewCollectionCostPercentage` decimal(18, 6),
	ADD Column `NewRegistrationCostPerNumber` decimal(18, 6) ;

    SELECT COUNT(*) AS COUNT INTO newstringcode FROM tmp_JobLog_;

    SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
    SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

	IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
	THEN
		IF p_CurrencyID = v_CompanyCurrencyID_
		THEN
			UPDATE
				tmp_TempRateTableDIDRate_
			SET
				NewOneOffCost = ( OneOffCost  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewMonthlyCost = ( MonthlyCost  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCostPerCall = ( CostPerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCostPerMinute = ( CostPerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurchargePerCall = ( SurchargePerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurchargePerMinute = ( SurchargePerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewOutpaymentPerCall = ( OutpaymentPerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewOutpaymentPerMinute = ( OutpaymentPerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurcharges = ( Surcharges  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewChargeback = ( Chargeback  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCollectionCostAmount = ( CollectionCostAmount  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCollectionCostPercentage = ( CollectionCostPercentage  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewRegistrationCostPerNumber = ( RegistrationCostPerNumber  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) )
			WHERE ProcessID=p_processId;
		ELSE
			UPDATE
				tmp_TempRateTableDIDRate_
			SET
				NewOneOffCost = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewMonthlyCost = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCostPerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCostPerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurchargePerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurchargePerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewOutpaymentPerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewOutpaymentPerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurcharges = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewChargeback = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCollectionCostAmount = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCollectionCostPercentage = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewRegistrationCostPerNumber = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId )))
			WHERE ProcessID=p_processId;
		END IF;
	ELSE
		UPDATE
			tmp_TempRateTableDIDRate_
		SET
			NewOneOffCost = OneOffCost,
			NewMonthlyCost = MonthlyCost,
			NewCostPerCall = CostPerCall,
			NewCostPerMinute = CostPerMinute,
			NewSurchargePerCall = SurchargePerCall,
			NewSurchargePerMinute = SurchargePerMinute,
			NewOutpaymentPerCall = OutpaymentPerCall,
			NewOutpaymentPerMinute = OutpaymentPerMinute,
			NewSurcharges = Surcharges,
			NewChargeback = Chargeback,
			NewCollectionCostAmount = CollectionCostAmount,
			NewCollectionCostPercentage = CollectionCostPercentage,
			NewRegistrationCostPerNumber = RegistrationCostPerNumber
		WHERE
			ProcessID = p_processId;
	END IF;

    IF newstringcode = 0
    THEN

		INSERT INTO tblRateTableDIDRateChangeLog(
            TempRateTableDIDRateID,
            RateTableDIDRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
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
            EffectiveDate,
            EndDate,
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTableDIDRate.TempRateTableDIDRateID,
			tblRateTableDIDRate.RateTableDIDRateID,
			p_RateTableId AS RateTableId,
			tblTempRateTableDIDRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
			tblTempRateTableDIDRate.OriginationCode,
			tblTempRateTableDIDRate.OriginationDescription,
			tblRate.RateId,
			tblTempRateTableDIDRate.Code,
			tblTempRateTableDIDRate.Description,
			tblTempRateTableDIDRate.City,
			tblTempRateTableDIDRate.Tariff,
			tblTempRateTableDIDRate.AccessType,
			tblTempRateTableDIDRate.NewOneOffCost,
			tblTempRateTableDIDRate.NewMonthlyCost,
			tblTempRateTableDIDRate.NewCostPerCall,
			tblTempRateTableDIDRate.NewCostPerMinute,
			tblTempRateTableDIDRate.NewSurchargePerCall,
			tblTempRateTableDIDRate.NewSurchargePerMinute,
			tblTempRateTableDIDRate.NewOutpaymentPerCall,
			tblTempRateTableDIDRate.NewOutpaymentPerMinute,
			tblTempRateTableDIDRate.NewSurcharges,
			tblTempRateTableDIDRate.NewChargeback,
			tblTempRateTableDIDRate.NewCollectionCostAmount,
			tblTempRateTableDIDRate.NewCollectionCostPercentage,
			tblTempRateTableDIDRate.NewRegistrationCostPerNumber,
			tblTempRateTableDIDRate.OneOffCostCurrency,
			tblTempRateTableDIDRate.MonthlyCostCurrency,
			tblTempRateTableDIDRate.CostPerCallCurrency,
			tblTempRateTableDIDRate.CostPerMinuteCurrency,
			tblTempRateTableDIDRate.SurchargePerCallCurrency,
			tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
			tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
			tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
			tblTempRateTableDIDRate.SurchargesCurrency,
			tblTempRateTableDIDRate.ChargebackCurrency,
			tblTempRateTableDIDRate.CollectionCostAmountCurrency,
			tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
			tblTempRateTableDIDRate.EffectiveDate,
			tblTempRateTableDIDRate.EndDate,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		LEFT JOIN tblRate
			ON tblTempRateTableDIDRate.Code = tblRate.Code AND tblTempRateTableDIDRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code AND tblTempRateTableDIDRate.CodeDeckId = OriginationRate.CodeDeckId  AND OriginationRate.CompanyID = p_companyId
		LEFT JOIN tblRateTableDIDRate
			ON tblRate.RateID = tblRateTableDIDRate.RateId AND
			((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID)) AND
			tblRateTableDIDRate.RateTableId = p_RateTableId AND
			tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID AND
			tblRateTableDIDRate.City = tblTempRateTableDIDRate.City AND
			tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff AND
			tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType AND
			tblRateTableDIDRate.EffectiveDate  <= date(now())
		WHERE tblTempRateTableDIDRate.ProcessID=p_processId AND tblRateTableDIDRate.RateTableDIDRateID IS NULL
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


        DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			EffectiveDate  Date,
			RowID int,
			INDEX (RowID)
		);
        INSERT INTO tmp_EffectiveDates_
        SELECT DISTINCT
            EffectiveDate,
            @row_num := @row_num+1 AS RowID
        FROM tmp_TempRateTableDIDRate_
            ,(SELECT @row_num := 0) x
        WHERE  ProcessID = p_processId

        group by EffectiveDate
        ORDER BY EffectiveDate asc;

        SET v_pointer_ = 1;
        SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

        IF v_rowCount_ > 0 THEN

            WHILE v_pointer_ <= v_rowCount_
            DO

                SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
                SET @row_num = 0;

                INSERT INTO tblRateTableDIDRateChangeLog(
					TempRateTableDIDRateID,
					RateTableDIDRateID,
					RateTableId,
					TimezonesID,
					OriginationRateID,
					OriginationCode,
					OriginationDescription,
					RateId,
					Code,
					Description,
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
					EffectiveDate,
					EndDate,
					`Action`,
					ProcessID,
					created_at
                )
                SELECT
					DISTINCT
					tblTempRateTableDIDRate.TempRateTableDIDRateID,
					RateTableDIDRate.RateTableDIDRateID,
					p_RateTableId AS RateTableId,
					tblTempRateTableDIDRate.TimezonesID,
					IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
					OriginationRate.Code AS OriginationCode,
					OriginationRate.Description AS OriginationDescription,
					tblRate.RateId,
					tblRate.Code,
					tblRate.Description,
					tblTempRateTableDIDRate.City,
					tblTempRateTableDIDRate.Tariff,
					tblTempRateTableDIDRate.AccessType,
					CONCAT(tblTempRateTableDIDRate.NewOneOffCost, IF(tblTempRateTableDIDRate.NewOneOffCost > RateTableDIDRate.OneOffCost, '<span style="color: green;" data-toggle="tooltip" data-title="OneOffCost Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOneOffCost < RateTableDIDRate.OneOffCost, '<span style="color: red;" data-toggle="tooltip" data-title="OneOffCost Decrease" data-placement="top">&#9660;</span>',''))) AS `OneOffCost`,
					CONCAT(tblTempRateTableDIDRate.NewMonthlyCost, IF(tblTempRateTableDIDRate.NewMonthlyCost > RateTableDIDRate.MonthlyCost, '<span style="color: green;" data-toggle="tooltip" data-title="MonthlyCost Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewMonthlyCost < RateTableDIDRate.MonthlyCost, '<span style="color: red;" data-toggle="tooltip" data-title="MonthlyCost Decrease" data-placement="top">&#9660;</span>',''))) AS `MonthlyCost`,
					CONCAT(tblTempRateTableDIDRate.NewCostPerCall, IF(tblTempRateTableDIDRate.NewCostPerCall > RateTableDIDRate.CostPerCall, '<span style="color: green;" data-toggle="tooltip" data-title="CostPerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCostPerCall < RateTableDIDRate.CostPerCall, '<span style="color: red;" data-toggle="tooltip" data-title="CostPerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `CostPerCall`,
					CONCAT(tblTempRateTableDIDRate.NewCostPerMinute, IF(tblTempRateTableDIDRate.NewCostPerMinute > RateTableDIDRate.CostPerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="CostPerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCostPerMinute < RateTableDIDRate.CostPerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="CostPerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `CostPerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewSurchargePerCall, IF(tblTempRateTableDIDRate.NewSurchargePerCall > RateTableDIDRate.SurchargePerCall, '<span style="color: green;" data-toggle="tooltip" data-title="SurchargePerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurchargePerCall < RateTableDIDRate.SurchargePerCall, '<span style="color: red;" data-toggle="tooltip" data-title="SurchargePerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `SurchargePerCall`,
					CONCAT(tblTempRateTableDIDRate.NewSurchargePerMinute, IF(tblTempRateTableDIDRate.NewSurchargePerMinute > RateTableDIDRate.SurchargePerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="SurchargePerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurchargePerMinute < RateTableDIDRate.SurchargePerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="SurchargePerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `SurchargePerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewOutpaymentPerCall, IF(tblTempRateTableDIDRate.NewOutpaymentPerCall > RateTableDIDRate.OutpaymentPerCall, '<span style="color: green;" data-toggle="tooltip" data-title="OutpaymentPerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOutpaymentPerCall < RateTableDIDRate.OutpaymentPerCall, '<span style="color: red;" data-toggle="tooltip" data-title="OutpaymentPerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `OutpaymentPerCall`,
					CONCAT(tblTempRateTableDIDRate.NewOutpaymentPerMinute, IF(tblTempRateTableDIDRate.NewOutpaymentPerMinute > RateTableDIDRate.OutpaymentPerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="OutpaymentPerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOutpaymentPerMinute < RateTableDIDRate.OutpaymentPerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="OutpaymentPerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `OutpaymentPerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewSurcharges, IF(tblTempRateTableDIDRate.NewSurcharges > RateTableDIDRate.Surcharges, '<span style="color: green;" data-toggle="tooltip" data-title="Surcharges Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurcharges < RateTableDIDRate.Surcharges, '<span style="color: red;" data-toggle="tooltip" data-title="Surcharges Decrease" data-placement="top">&#9660;</span>',''))) AS `Surcharges`,
					CONCAT(tblTempRateTableDIDRate.NewChargeback, IF(tblTempRateTableDIDRate.NewChargeback > RateTableDIDRate.Chargeback, '<span style="color: green;" data-toggle="tooltip" data-title="Chargeback Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewChargeback < RateTableDIDRate.Chargeback, '<span style="color: red;" data-toggle="tooltip" data-title="Chargeback Decrease" data-placement="top">&#9660;</span>',''))) AS `Chargeback`,
					CONCAT(tblTempRateTableDIDRate.NewCollectionCostAmount, IF(tblTempRateTableDIDRate.NewCollectionCostAmount > RateTableDIDRate.CollectionCostAmount, '<span style="color: green;" data-toggle="tooltip" data-title="CollectionCostAmount Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCollectionCostAmount < RateTableDIDRate.CollectionCostAmount, '<span style="color: red;" data-toggle="tooltip" data-title="CollectionCostAmount Decrease" data-placement="top">&#9660;</span>',''))) AS `CollectionCostAmount`,
					CONCAT(tblTempRateTableDIDRate.NewCollectionCostPercentage, IF(tblTempRateTableDIDRate.NewCollectionCostPercentage > RateTableDIDRate.CollectionCostPercentage, '<span style="color: green;" data-toggle="tooltip" data-title="CollectionCostPercentage Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCollectionCostPercentage < RateTableDIDRate.CollectionCostPercentage, '<span style="color: red;" data-toggle="tooltip" data-title="CollectionCostPercentage Decrease" data-placement="top">&#9660;</span>',''))) AS `CollectionCostPercentage`,
					CONCAT(tblTempRateTableDIDRate.NewRegistrationCostPerNumber, IF(tblTempRateTableDIDRate.NewRegistrationCostPerNumber > RateTableDIDRate.RegistrationCostPerNumber, '<span style="color: green;" data-toggle="tooltip" data-title="RegistrationCostPerNumber Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewRegistrationCostPerNumber < RateTableDIDRate.RegistrationCostPerNumber, '<span style="color: red;" data-toggle="tooltip" data-title="RegistrationCostPerNumber Decrease" data-placement="top">&#9660;</span>',''))) AS `RegistrationCostPerNumber`,
					tblTempRateTableDIDRate.OneOffCostCurrency,
					tblTempRateTableDIDRate.MonthlyCostCurrency,
					tblTempRateTableDIDRate.CostPerCallCurrency,
					tblTempRateTableDIDRate.CostPerMinuteCurrency,
					tblTempRateTableDIDRate.SurchargePerCallCurrency,
					tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
					tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
					tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
					tblTempRateTableDIDRate.SurchargesCurrency,
					tblTempRateTableDIDRate.ChargebackCurrency,
					tblTempRateTableDIDRate.CollectionCostAmountCurrency,
					tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
					tblTempRateTableDIDRate.EffectiveDate,
					tblTempRateTableDIDRate.EndDate ,
					'IncreasedDecreased' AS `Action`,
					p_processid AS ProcessID,
					now() AS created_at
                FROM
                (
                    SELECT DISTINCT tmp.* ,
                        @row_num := IF(@prev_RateId = tmp.RateID AND @prev_OriginationRateID = tmp.OriginationRateID AND @prev_TimezonesID = tmp.TimezonesID AND @prev_City = tmp.City AND @prev_Tariff = tmp.Tariff AND @prev_AccessType = tmp.AccessType AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                        @prev_RateId := tmp.RateID,
                        @prev_OriginationRateID := tmp.OriginationRateID,
                        @prev_TimezonesID := tmp.TimezonesID,
                        @prev_City := tmp.City,
                        @prev_Tariff := tmp.Tariff,
                        @prev_AccessType := tmp.AccessType,
                        @prev_EffectiveDate := tmp.EffectiveDate
                    FROM
                    (
                        SELECT DISTINCT vr1.*
                        FROM tblRateTableDIDRate vr1
                        LEFT OUTER JOIN tblRateTableDIDRate vr2
                            ON vr1.RateTableId = vr2.RateTableId
                            AND vr1.RateID = vr2.RateID
                            AND vr1.OriginationRateID = vr2.OriginationRateID
                            AND vr1.TimezonesID = vr2.TimezonesID
                            AND vr1.City = vr2.City
                            AND vr1.Tariff = vr2.Tariff
                            AND vr1.AccessType = vr2.AccessType
                            AND vr2.EffectiveDate  = @EffectiveDate
                        WHERE
                            vr1.RateTableId = p_RateTableId
                            AND vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate)
                        ORDER BY vr1.RateID DESC, vr1.OriginationRateID DESC, vr1.TimezonesID DESC, vr1.City DESC, vr1.Tariff DESC, vr1.AccessType DESC, vr1.EffectiveDate DESC
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_OriginationRateID := 0 , @prev_TimezonesID := 0 , @prev_City := '' , @prev_Tariff := '' , @prev_AccessType := '' , @prev_EffectiveDate := '' ) x
                      ORDER BY RateID DESC, OriginationRateID DESC, TimezonesID DESC, City DESC, Tariff DESC, AccessType DESC, EffectiveDate DESC
                ) RateTableDIDRate
                JOIN tblRate
                    ON tblRate.CompanyID = p_companyId
                    AND tblRate.RateID = RateTableDIDRate.RateId
                LEFT JOIN tblRate AS OriginationRate
                    ON OriginationRate.CompanyID = p_companyId
                    AND OriginationRate.RateID = RateTableDIDRate.OriginationRateID
                JOIN tmp_TempRateTableDIDRate_ tblTempRateTableDIDRate
                    ON tblTempRateTableDIDRate.Code = tblRate.Code
                    AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
                    AND tblTempRateTableDIDRate.TimezonesID = RateTableDIDRate.TimezonesID
                    AND tblTempRateTableDIDRate.City = RateTableDIDRate.City
                    AND tblTempRateTableDIDRate.Tariff = RateTableDIDRate.Tariff
                    AND tblTempRateTableDIDRate.AccessType = RateTableDIDRate.AccessType
                    AND tblTempRateTableDIDRate.ProcessID=p_processId
                    AND RateTableDIDRate.EffectiveDate <= tblTempRateTableDIDRate.EffectiveDate
                    AND tblTempRateTableDIDRate.EffectiveDate =  @EffectiveDate
                    AND RateTableDIDRate.RowID = 1
                WHERE
                    RateTableDIDRate.RateTableId = p_RateTableId
                    AND tblTempRateTableDIDRate.Code IS NOT NULL
                    AND tblTempRateTableDIDRate.ProcessID=p_processId
                    AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

                SET v_pointer_ = v_pointer_ + 1;

            END WHILE;

        END IF;


        IF p_list_option = 1
        THEN

            INSERT INTO tblRateTableDIDRateChangeLog(
				RateTableDIDRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
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
				EffectiveDate,
				EndDate,
				`Action`,
				ProcessID,
				created_at
            )
            SELECT DISTINCT
                tblRateTableDIDRate.RateTableDIDRateID,
                p_RateTableId AS RateTableId,
                tblRateTableDIDRate.TimezonesID,
                tblRateTableDIDRate.OriginationRateID,
                OriginationRate.Code,
                OriginationRate.Description,
                tblRateTableDIDRate.RateId,
                tblRate.Code,
                tblRate.Description,
                tblRateTableDIDRate.City,
					 tblRateTableDIDRate.Tariff,
                tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
                tblRateTableDIDRate.EffectiveDate,
                tblRateTableDIDRate.EndDate ,
                'Deleted' AS `Action`,
                p_processId AS ProcessID,
                now() AS deleted_at
            FROM tblRateTableDIDRate
            JOIN tblRate
                ON tblRate.RateID = tblRateTableDIDRate.RateId AND tblRate.CompanyID = p_companyId
        		LEFT JOIN tblRate AS OriginationRate
             	 ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
            LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
                ON tblTempRateTableDIDRate.Code = tblRate.Code
                AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
                AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
                AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
                AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
                AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
                AND tblTempRateTableDIDRate.ProcessID=p_processId
                AND (
                    ( tblTempRateTableDIDRate.EndDate is null AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
                    OR
                    ( tblTempRateTableDIDRate.EndDate is not null AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
                )
            WHERE tblRateTableDIDRate.RateTableId = p_RateTableId
                AND ( tblRateTableDIDRate.EndDate is null OR tblRateTableDIDRate.EndDate <= date(now()) )
                AND tblTempRateTableDIDRate.Code IS NULL
            ORDER BY RateTableDIDRateID ASC;

        END IF;


        INSERT INTO tblRateTableDIDRateChangeLog(
            RateTableDIDRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
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
            EffectiveDate,
            EndDate,
            `Action`,
            ProcessID,
            created_at
        )
        SELECT DISTINCT
            tblRateTableDIDRate.RateTableDIDRateID,
            p_RateTableId AS RateTableId,
            tblRateTableDIDRate.TimezonesID,
            tblRateTableDIDRate.OriginationRateID,
            OriginationRate.Code,
            OriginationRate.Description,
            tblRateTableDIDRate.RateId,
            tblRate.Code,
            tblRate.Description,
            tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
            tblRateTableDIDRate.AccessType,
			tblRateTableDIDRate.OneOffCost,
			tblRateTableDIDRate.MonthlyCost,
			tblRateTableDIDRate.CostPerCall,
			tblRateTableDIDRate.CostPerMinute,
			tblRateTableDIDRate.SurchargePerCall,
			tblRateTableDIDRate.SurchargePerMinute,
			tblRateTableDIDRate.OutpaymentPerCall,
			tblRateTableDIDRate.OutpaymentPerMinute,
			tblRateTableDIDRate.Surcharges,
			tblRateTableDIDRate.Chargeback,
			tblRateTableDIDRate.CollectionCostAmount,
			tblRateTableDIDRate.CollectionCostPercentage,
			tblRateTableDIDRate.RegistrationCostPerNumber,
			tblRateTableDIDRate.OneOffCostCurrency,
			tblRateTableDIDRate.MonthlyCostCurrency,
			tblRateTableDIDRate.CostPerCallCurrency,
			tblRateTableDIDRate.CostPerMinuteCurrency,
			tblRateTableDIDRate.SurchargePerCallCurrency,
			tblRateTableDIDRate.SurchargePerMinuteCurrency,
			tblRateTableDIDRate.OutpaymentPerCallCurrency,
			tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
			tblRateTableDIDRate.SurchargesCurrency,
			tblRateTableDIDRate.ChargebackCurrency,
			tblRateTableDIDRate.CollectionCostAmountCurrency,
			tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
            tblRateTableDIDRate.EffectiveDate,
            IFNULL(tblTempRateTableDIDRate.EndDate,tblRateTableDIDRate.EndDate) as  EndDate ,
            'Deleted' AS `Action`,
            p_processId AS ProcessID,
            now() AS deleted_at
        FROM tblRateTableDIDRate
        JOIN tblRate
            ON tblRate.RateID = tblRateTableDIDRate.RateId AND tblRate.CompanyID = p_companyId
        LEFT JOIN tblRate AS OriginationRate
             ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
        LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
            ON tblRate.Code = tblTempRateTableDIDRate.Code
            AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
            AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
            AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
            AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
            AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
            AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
            AND tblTempRateTableDIDRate.ProcessID=p_processId
        WHERE
			tblRateTableDIDRate.RateTableId = p_RateTableId AND
			tblTempRateTableDIDRate.Code IS NOT NULL
        ORDER BY
		RateTableDIDRateID ASC;

    END IF;

    SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTablePKGRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTablePKGRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
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

    DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTablePKGRate_;
    CREATE TEMPORARY TABLE tmp_split_RateTablePKGRate_ (
		`TempRateTablePKGRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`PackageCostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`RecordingCostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`PackageCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`RecordingCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTablePKGRate_ (
		`TempRateTablePKGRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`PackageCostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`RecordingCostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`PackageCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`RecordingCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    CALL  prc_RateTablePKGRateCheckDupliacteCode(p_companyId,p_processId,p_effectiveImmediately);

	ALTER TABLE
		`tmp_TempRateTablePKGRate_`
	ADD Column `NewOneOffCost` decimal(18, 6),
	ADD Column `NewMonthlyCost` decimal(18, 6),
	ADD Column `NewPackageCostPerMinute` decimal(18, 6),
	ADD Column `NewRecordingCostPerMinute` decimal(18, 6) ;

    SELECT COUNT(*) AS COUNT INTO newstringcode FROM tmp_JobLog_;

    SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
    SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

	IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
	THEN
		IF p_CurrencyID = v_CompanyCurrencyID_
		THEN
			UPDATE
				tmp_TempRateTablePKGRate_
			SET
				NewOneOffCost = ( OneOffCost  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewMonthlyCost = ( MonthlyCost  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewPackageCostPerMinute = ( PackageCostPerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewRecordingCostPerMinute = ( RecordingCostPerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) )
			WHERE ProcessID=p_processId;
		ELSE
			UPDATE
				tmp_TempRateTablePKGRate_
			SET
				NewOneOffCost = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewMonthlyCost = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewPackageCostPerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (PackageCostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewRecordingCostPerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (RecordingCostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId )))
			WHERE ProcessID=p_processId;
		END IF;
	ELSE
		UPDATE
			tmp_TempRateTablePKGRate_
		SET
			NewOneOffCost = OneOffCost,
			NewMonthlyCost = MonthlyCost,
			NewPackageCostPerMinute = PackageCostPerMinute,
			NewRecordingCostPerMinute = RecordingCostPerMinute
		WHERE
			ProcessID = p_processId;
	END IF;

    IF newstringcode = 0
    THEN

		INSERT INTO tblRateTablePKGRateChangeLog(
            TempRateTablePKGRateID,
            RateTablePKGRateID,
            RateTableId,
            TimezonesID,
            RateId,
            Code,
            Description,
            OneOffCost,
			MonthlyCost,
			PackageCostPerMinute,
			RecordingCostPerMinute,
			OneOffCostCurrency,
			MonthlyCostCurrency,
			PackageCostPerMinuteCurrency,
			RecordingCostPerMinuteCurrency,
			EffectiveDate,
            EndDate,
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTablePKGRate.TempRateTablePKGRateID,
			tblRateTablePKGRate.RateTablePKGRateID,
			p_RateTableId AS RateTableId,
			tblTempRateTablePKGRate.TimezonesID,
			tblRate.RateId,
			tblTempRateTablePKGRate.Code,
			tblTempRateTablePKGRate.Description,
			tblTempRateTablePKGRate.NewOneOffCost,
			tblTempRateTablePKGRate.NewMonthlyCost,
			tblTempRateTablePKGRate.NewPackageCostPerMinute,
			tblTempRateTablePKGRate.NewRecordingCostPerMinute,
			tblTempRateTablePKGRate.OneOffCostCurrency,
			tblTempRateTablePKGRate.MonthlyCostCurrency,
			tblTempRateTablePKGRate.PackageCostPerMinuteCurrency,
			tblTempRateTablePKGRate.RecordingCostPerMinuteCurrency,
			tblTempRateTablePKGRate.EffectiveDate,
			tblTempRateTablePKGRate.EndDate,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTablePKGRate_ as tblTempRateTablePKGRate
		LEFT JOIN tblRate
			ON tblTempRateTablePKGRate.Code = tblRate.Code AND tblTempRateTablePKGRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRateTablePKGRate
			ON tblRate.RateID = tblRateTablePKGRate.RateId AND
			tblRateTablePKGRate.RateTableId = p_RateTableId AND
			tblRateTablePKGRate.TimezonesID = tblTempRateTablePKGRate.TimezonesID AND
			tblRateTablePKGRate.EffectiveDate  <= date(now())
		WHERE tblTempRateTablePKGRate.ProcessID=p_processId AND tblRateTablePKGRate.RateTablePKGRateID IS NULL
			AND tblTempRateTablePKGRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


        DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			EffectiveDate  Date,
			RowID int,
			INDEX (RowID)
		);
        INSERT INTO tmp_EffectiveDates_
        SELECT DISTINCT
            EffectiveDate,
            @row_num := @row_num+1 AS RowID
        FROM tmp_TempRateTablePKGRate_
            ,(SELECT @row_num := 0) x
        WHERE  ProcessID = p_processId

        group by EffectiveDate
        ORDER BY EffectiveDate asc;

        SET v_pointer_ = 1;
        SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

        IF v_rowCount_ > 0 THEN

            WHILE v_pointer_ <= v_rowCount_
            DO

                SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
                SET @row_num = 0;

                INSERT INTO tblRateTablePKGRateChangeLog(
					TempRateTablePKGRateID,
					RateTablePKGRateID,
					RateTableId,
					TimezonesID,
					RateId,
					Code,
					Description,
					OneOffCost,
					MonthlyCost,
					PackageCostPerMinute,
					RecordingCostPerMinute,
					OneOffCostCurrency,
					MonthlyCostCurrency,
					PackageCostPerMinuteCurrency,
					RecordingCostPerMinuteCurrency,
					EffectiveDate,
					EndDate,
					`Action`,
					ProcessID,
					created_at
                )
                SELECT
					DISTINCT
					tblTempRateTablePKGRate.TempRateTablePKGRateID,
					RateTablePKGRate.RateTablePKGRateID,
					p_RateTableId AS RateTableId,
					tblTempRateTablePKGRate.TimezonesID,
					tblRate.RateId,
					tblRate.Code,
					tblRate.Description,
					CONCAT(tblTempRateTablePKGRate.NewOneOffCost, IF(tblTempRateTablePKGRate.NewOneOffCost > RateTablePKGRate.OneOffCost, '<span style="color: green;" data-toggle="tooltip" data-title="OneOffCost Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTablePKGRate.NewOneOffCost < RateTablePKGRate.OneOffCost, '<span style="color: red;" data-toggle="tooltip" data-title="OneOffCost Decrease" data-placement="top">&#9660;</span>',''))) AS `OneOffCost`,
					CONCAT(tblTempRateTablePKGRate.NewMonthlyCost, IF(tblTempRateTablePKGRate.NewMonthlyCost > RateTablePKGRate.MonthlyCost, '<span style="color: green;" data-toggle="tooltip" data-title="MonthlyCost Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTablePKGRate.NewMonthlyCost < RateTablePKGRate.MonthlyCost, '<span style="color: red;" data-toggle="tooltip" data-title="MonthlyCost Decrease" data-placement="top">&#9660;</span>',''))) AS `MonthlyCost`,
					CONCAT(tblTempRateTablePKGRate.NewPackageCostPerMinute, IF(tblTempRateTablePKGRate.NewPackageCostPerMinute > RateTablePKGRate.PackageCostPerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="PackageCostPerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTablePKGRate.NewPackageCostPerMinute < RateTablePKGRate.PackageCostPerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="PackageCostPerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `PackageCostPerMinute`,
					CONCAT(tblTempRateTablePKGRate.NewRecordingCostPerMinute, IF(tblTempRateTablePKGRate.NewRecordingCostPerMinute > RateTablePKGRate.RecordingCostPerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="RecordingCostPerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTablePKGRate.NewRecordingCostPerMinute < RateTablePKGRate.RecordingCostPerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="RecordingCostPerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `RecordingCostPerMinute`,
					tblTempRateTablePKGRate.OneOffCostCurrency,
					tblTempRateTablePKGRate.MonthlyCostCurrency,
					tblTempRateTablePKGRate.PackageCostPerMinuteCurrency,
					tblTempRateTablePKGRate.RecordingCostPerMinuteCurrency,
					tblTempRateTablePKGRate.EffectiveDate,
					tblTempRateTablePKGRate.EndDate ,
					'IncreasedDecreased' AS `Action`,
					p_processid AS ProcessID,
					now() AS created_at
                FROM
                (
                    SELECT DISTINCT tmp.* ,
                        @row_num := IF(@prev_RateId = tmp.RateID AND @prev_TimezonesID = tmp.TimezonesID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                        @prev_RateId := tmp.RateID,
                        @prev_TimezonesID := tmp.TimezonesID,
                        @prev_EffectiveDate := tmp.EffectiveDate
                    FROM
                    (
                        SELECT DISTINCT vr1.*
                        FROM tblRateTablePKGRate vr1
                        LEFT OUTER JOIN tblRateTablePKGRate vr2
                            ON vr1.RateTableId = vr2.RateTableId
                            AND vr1.RateID = vr2.RateID
                            AND vr1.TimezonesID = vr2.TimezonesID
                            AND vr2.EffectiveDate  = @EffectiveDate
                        WHERE
                            vr1.RateTableId = p_RateTableId
                            AND vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate)
                        ORDER BY vr1.RateID DESC, vr1.TimezonesID DESC, vr1.EffectiveDate DESC
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_TimezonesID := 0 , @prev_EffectiveDate := '' ) x
                      ORDER BY RateID DESC , TimezonesID DESC , EffectiveDate DESC
                ) RateTablePKGRate
                JOIN tblRate
                    ON tblRate.CompanyID = p_companyId
                    AND tblRate.RateID = RateTablePKGRate.RateId
                JOIN tmp_TempRateTablePKGRate_ tblTempRateTablePKGRate
                    ON tblTempRateTablePKGRate.Code = tblRate.Code
                    AND tblTempRateTablePKGRate.TimezonesID = RateTablePKGRate.TimezonesID
                    AND tblTempRateTablePKGRate.ProcessID=p_processId
                    AND RateTablePKGRate.EffectiveDate <= tblTempRateTablePKGRate.EffectiveDate
                    AND tblTempRateTablePKGRate.EffectiveDate =  @EffectiveDate
                    AND RateTablePKGRate.RowID = 1
                WHERE
                    RateTablePKGRate.RateTableId = p_RateTableId
                    AND tblTempRateTablePKGRate.Code IS NOT NULL
                    AND tblTempRateTablePKGRate.ProcessID=p_processId
                    AND tblTempRateTablePKGRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

                SET v_pointer_ = v_pointer_ + 1;

            END WHILE;

        END IF;


        IF p_list_option = 1
        THEN

            INSERT INTO tblRateTablePKGRateChangeLog(
				RateTablePKGRateID,
				RateTableId,
				TimezonesID,
				RateId,
				Code,
				Description,
				OneOffCost,
				MonthlyCost,
				PackageCostPerMinute,
				RecordingCostPerMinute,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				PackageCostPerMinuteCurrency,
				RecordingCostPerMinuteCurrency,
				EffectiveDate,
				EndDate,
				`Action`,
				ProcessID,
				created_at
            )
            SELECT DISTINCT
                tblRateTablePKGRate.RateTablePKGRateID,
                p_RateTableId AS RateTableId,
                tblRateTablePKGRate.TimezonesID,
                tblRateTablePKGRate.RateId,
                tblRate.Code,
                tblRate.Description,
                tblRateTablePKGRate.OneOffCost,
				tblRateTablePKGRate.MonthlyCost,
				tblRateTablePKGRate.PackageCostPerMinute,
				tblRateTablePKGRate.RecordingCostPerMinute,
				tblRateTablePKGRate.OneOffCostCurrency,
				tblRateTablePKGRate.MonthlyCostCurrency,
				tblRateTablePKGRate.PackageCostPerMinuteCurrency,
				tblRateTablePKGRate.RecordingCostPerMinuteCurrency,
				tblRateTablePKGRate.EffectiveDate,
                tblRateTablePKGRate.EndDate ,
                'Deleted' AS `Action`,
                p_processId AS ProcessID,
                now() AS deleted_at
            FROM tblRateTablePKGRate
            JOIN tblRate
                ON tblRate.RateID = tblRateTablePKGRate.RateId AND tblRate.CompanyID = p_companyId
            LEFT JOIN tmp_TempRateTablePKGRate_ as tblTempRateTablePKGRate
                ON tblTempRateTablePKGRate.Code = tblRate.Code
                AND tblTempRateTablePKGRate.TimezonesID = tblRateTablePKGRate.TimezonesID
                AND tblTempRateTablePKGRate.ProcessID=p_processId
                AND (
                    ( tblTempRateTablePKGRate.EndDate is null AND tblTempRateTablePKGRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
                    OR
                    ( tblTempRateTablePKGRate.EndDate is not null AND tblTempRateTablePKGRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
                )
            WHERE tblRateTablePKGRate.RateTableId = p_RateTableId
                AND ( tblRateTablePKGRate.EndDate is null OR tblRateTablePKGRate.EndDate <= date(now()) )
                AND tblTempRateTablePKGRate.Code IS NULL
            ORDER BY RateTablePKGRateID ASC;

        END IF;


        INSERT INTO tblRateTablePKGRateChangeLog(
            RateTablePKGRateID,
            RateTableId,
            TimezonesID,
            RateId,
            Code,
            Description,
            OneOffCost,
			MonthlyCost,
			PackageCostPerMinute,
			RecordingCostPerMinute,
			OneOffCostCurrency,
			MonthlyCostCurrency,
			PackageCostPerMinuteCurrency,
			RecordingCostPerMinuteCurrency,
			EffectiveDate,
            EndDate,
            `Action`,
            ProcessID,
            created_at
        )
        SELECT DISTINCT
            tblRateTablePKGRate.RateTablePKGRateID,
            p_RateTableId AS RateTableId,
            tblRateTablePKGRate.TimezonesID,
            tblRateTablePKGRate.RateId,
            tblRate.Code,
            tblRate.Description,
            tblRateTablePKGRate.OneOffCost,
			tblRateTablePKGRate.MonthlyCost,
			tblRateTablePKGRate.PackageCostPerMinute,
			tblRateTablePKGRate.RecordingCostPerMinute,
			tblRateTablePKGRate.OneOffCostCurrency,
			tblRateTablePKGRate.MonthlyCostCurrency,
			tblRateTablePKGRate.PackageCostPerMinuteCurrency,
			tblRateTablePKGRate.RecordingCostPerMinuteCurrency,
			tblRateTablePKGRate.EffectiveDate,
            IFNULL(tblTempRateTablePKGRate.EndDate,tblRateTablePKGRate.EndDate) as  EndDate ,
            'Deleted' AS `Action`,
            p_processId AS ProcessID,
            now() AS deleted_at
        FROM tblRateTablePKGRate
        JOIN tblRate
            ON tblRate.RateID = tblRateTablePKGRate.RateId AND tblRate.CompanyID = p_companyId
        LEFT JOIN tmp_TempRateTablePKGRate_ as tblTempRateTablePKGRate
            ON tblRate.Code = tblTempRateTablePKGRate.Code
            AND tblTempRateTablePKGRate.TimezonesID = tblRateTablePKGRate.TimezonesID
            AND tblTempRateTablePKGRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
            AND tblTempRateTablePKGRate.ProcessID=p_processId
        WHERE
			tblRateTablePKGRate.RateTableId = p_RateTableId AND
			tblTempRateTablePKGRate.Code IS NOT NULL
        ORDER BY
		RateTablePKGRateID ASC;

    END IF;

    SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTablePKGRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTablePKGRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTablePKGRate_ (
		`TempRateTablePKGRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`PackageCostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`RecordingCostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`PackageCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`RecordingCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTablePKGRate_ (
		TempRateTablePKGRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`PackageCostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`RecordingCostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`PackageCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`RecordingCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTablePKGRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTablePKGRate (
		RateTablePKGRateID INT,
		RateTableId INT,
		TimezonesID INT,
		RateId INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		OneOffCost decimal(18,6) DEFAULT NULL,
	  	MonthlyCost decimal(18,6) DEFAULT NULL,
	  	PackageCostPerMinute decimal(18,6) DEFAULT NULL,
	  	RecordingCostPerMinute decimal(18,6) DEFAULT NULL,
	  	OneOffCostCurrency INT(11) NULL DEFAULT NULL,
		MonthlyCostCurrency INT(11) NULL DEFAULT NULL,
		PackageCostPerMinuteCurrency INT(11) NULL DEFAULT NULL,
		RecordingCostPerMinuteCurrency INT(11) NULL DEFAULT NULL,
		EffectiveDate DATETIME,
		EndDate Datetime ,
		deleted_at DATETIME,
		INDEX tmp_RateTablePKGRateDiscontinued_RateTablePKGRateID (`RateTablePKGRateID`)
	);

	CALL  prc_RateTablePKGRateCheckDupliacteCode(p_companyId,p_processId,p_effectiveImmediately);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTablePKGRate_;

	IF newstringcode = 0
	THEN

		IF (SELECT count(*) FROM tblRateTablePKGRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN

			UPDATE
				tblRateTablePKGRate vr
			INNER JOIN tblRateTablePKGRateChangeLog  vrcl
			on vrcl.RateTablePKGRateID = vr.RateTablePKGRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';


			UPDATE tmp_TempRateTablePKGRate_ tblTempRateTablePKGRate
			JOIN tblRateTablePKGRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTablePKGRate.Code
				AND vrcl.TimezonesID = tblTempRateTablePKGRate.TimezonesID
				AND vrcl.EffectiveDate = tblTempRateTablePKGRate.EffectiveDate
			SET
				tblTempRateTablePKGRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;


		END IF;


		IF  p_replaceAllRates = 1
		THEN

			UPDATE tblRateTablePKGRate
				SET tblRateTablePKGRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;

		END IF;



		-- complete file
		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTablePKGRate(
				RateTablePKGRateID,
				RateTableId,
				TimezonesID,
				RateId,
				Code,
				Description,
				OneOffCost,
				MonthlyCost,
				PackageCostPerMinute,
				RecordingCostPerMinute,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				PackageCostPerMinuteCurrency,
				RecordingCostPerMinuteCurrency,
				EffectiveDate,
				EndDate,
				deleted_at
			)
			SELECT DISTINCT
				tblRateTablePKGRate.RateTablePKGRateID,
				p_RateTableId AS RateTableId,
				tblRateTablePKGRate.TimezonesID,
				tblRateTablePKGRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTablePKGRate.OneOffCost,
				tblRateTablePKGRate.MonthlyCost,
				tblRateTablePKGRate.PackageCostPerMinute,
				tblRateTablePKGRate.RecordingCostPerMinute,
				tblRateTablePKGRate.OneOffCostCurrency,
				tblRateTablePKGRate.MonthlyCostCurrency,
				tblRateTablePKGRate.PackageCostPerMinuteCurrency,
				tblRateTablePKGRate.RecordingCostPerMinuteCurrency,
				tblRateTablePKGRate.EffectiveDate,
				IFNULL(tblRateTablePKGRate.EndDate,date(now())) ,
				now() AS deleted_at
			FROM tblRateTablePKGRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTablePKGRate.RateId
				AND tblRate.CompanyID = p_companyId

			LEFT JOIN tmp_TempRateTablePKGRate_ as tblTempRateTablePKGRate
				ON tblTempRateTablePKGRate.Code = tblRate.Code
				AND tblTempRateTablePKGRate.TimezonesID = tblRateTablePKGRate.TimezonesID
				AND  tblTempRateTablePKGRate.ProcessId = p_processId
				AND tblTempRateTablePKGRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTablePKGRate.RateTableId = p_RateTableId
				AND tblTempRateTablePKGRate.Code IS NULL
				AND ( tblRateTablePKGRate.EndDate is NULL OR tblRateTablePKGRate.EndDate <= date(now()) )
			ORDER BY RateTablePKGRateID ASC;


			UPDATE tblRateTablePKGRate
			JOIN tmp_Delete_RateTablePKGRate ON tblRateTablePKGRate.RateTablePKGRateID = tmp_Delete_RateTablePKGRate.RateTablePKGRateID
				SET tblRateTablePKGRate.EndDate = date(now())
			WHERE
				tblRateTablePKGRate.RateTableId = p_RateTableId;

		END IF;


		IF ( (SELECT count(*) FROM tblRateTablePKGRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN

			call prc_ArchiveOldRateTablePKGRate(p_RateTableId, NULL,p_UserName);

		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTablePKGRate_2 AS (SELECT * FROM tmp_TempRateTablePKGRate_);

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
					tblTempRateTablePKGRate.Code,
					MAX(tblTempRateTablePKGRate.Description) AS Description,
					MAX(tblTempRateTablePKGRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTablePKGRate_  as tblTempRateTablePKGRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTablePKGRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTablePKGRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTablePKGRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTablePKGRate.Code
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
							tblTempRateTablePKGRate.Code,
							tblTempRateTablePKGRate.Description
						FROM tmp_TempRateTablePKGRate_  as tblTempRateTablePKGRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTablePKGRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTablePKGRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTablePKGRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
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
							tblTempRateTablePKGRate.Code,
							tblTempRateTablePKGRate.Description
						FROM tmp_TempRateTablePKGRate_  as tblTempRateTablePKGRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTablePKGRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTablePKGRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTablePKGRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;


		UPDATE tblRateTablePKGRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTablePKGRate.RateId
			AND tblRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTablePKGRate_ as tblTempRateTablePKGRate
			ON tblRate.Code = tblTempRateTablePKGRate.Code
			AND tblTempRateTablePKGRate.TimezonesID = tblRateTablePKGRate.TimezonesID
			AND tblTempRateTablePKGRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTablePKGRate.EndDate = IFNULL(tblTempRateTablePKGRate.EndDate,date(now()))
		WHERE tblRateTablePKGRate.RateTableId = p_RateTableId;


		DELETE tblTempRateTablePKGRate
		FROM tmp_TempRateTablePKGRate_ as tblTempRateTablePKGRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTablePKGRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTablePKGRate.CodeDeckId
		JOIN tblRateTablePKGRate
			ON tblRateTablePKGRate.RateId = tblRate.RateId
			AND tblRateTablePKGRate.RateTableId = p_RateTableId
			AND tblRateTablePKGRate.TimezonesID = tblTempRateTablePKGRate.TimezonesID
			AND IFNULL(tblTempRateTablePKGRate.OneOffCost,0) = IFNULL(tblRateTablePKGRate.OneOffCost,0)
        	AND IFNULL(tblTempRateTablePKGRate.MonthlyCost,0) = IFNULL(tblRateTablePKGRate.MonthlyCost,0)
        	AND IFNULL(tblTempRateTablePKGRate.PackageCostPerMinute,0) = IFNULL(tblRateTablePKGRate.PackageCostPerMinute,0)
        	AND IFNULL(tblTempRateTablePKGRate.RecordingCostPerMinute,0) = IFNULL(tblRateTablePKGRate.RecordingCostPerMinute,0)
		WHERE
			tblTempRateTablePKGRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');


		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		UPDATE tmp_TempRateTablePKGRate_ as tblTempRateTablePKGRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTablePKGRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTablePKGRate.CodeDeckId
		JOIN tblRateTablePKGRate
			ON tblRateTablePKGRate.RateId = tblRate.RateId
			AND tblRateTablePKGRate.RateTableId = p_RateTableId
		/*
			this one condition removed at 2019-09-06 as per SI Requirement
			if upload any timezones rate with paritial file upload then all old rates on all timezones for that prefix need to deleted.
		*/
		--	AND tblRateTablePKGRate.TimezonesID = tblTempRateTablePKGRate.TimezonesID
		SET tblRateTablePKGRate.EndDate = NOW()
		WHERE
			tblRateTablePKGRate.RateId = tblRate.RateId
			AND (
				tblTempRateTablePKGRate.OneOffCost <> tblRateTablePKGRate.OneOffCost
				OR tblTempRateTablePKGRate.MonthlyCost <> tblRateTablePKGRate.MonthlyCost
				OR tblTempRateTablePKGRate.PackageCostPerMinute <> tblRateTablePKGRate.PackageCostPerMinute
				OR tblTempRateTablePKGRate.RecordingCostPerMinute <> tblRateTablePKGRate.RecordingCostPerMinute
			)
			AND tblTempRateTablePKGRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND
			-- DATE_FORMAT (tblRateTablePKGRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTablePKGRate.EffectiveDate, '%Y-%m-%d')
			(
				( -- if future rates then delete same date existing records
					DATE_FORMAT (tblTempRateTablePKGRate.EffectiveDate, '%Y-%m-%d') > CURDATE() AND
					DATE_FORMAT (tblRateTablePKGRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTablePKGRate.EffectiveDate, '%Y-%m-%d')
				)
				OR
				( -- if current rates then delete current or older records
					DATE_FORMAT (tblTempRateTablePKGRate.EffectiveDate, '%Y-%m-%d') <= CURDATE() AND
					DATE_FORMAT (tblRateTablePKGRate.EffectiveDate, '%Y-%m-%d') <= DATE_FORMAT (tblTempRateTablePKGRate.EffectiveDate, '%Y-%m-%d')
				)
			);


		call prc_ArchiveOldRateTablePKGRate(p_RateTableId, NULL,p_UserName);

		SET @stm1 = CONCAT('
			INSERT INTO tblRateTablePKGRate (
				RateTableId,
				TimezonesID,
				RateId,
				OneOffCost,
				MonthlyCost,
				PackageCostPerMinute,
				RecordingCostPerMinute,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				PackageCostPerMinuteCurrency,
				RecordingCostPerMinuteCurrency,
				EffectiveDate,
				EndDate,
				ApprovedStatus
			)
			SELECT DISTINCT
				',p_RateTableId,' AS RateTableId,
				tblTempRateTablePKGRate.TimezonesID,
				tblRate.RateID,
		');

		SET @stm2 = '';
		IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
        THEN
			IF p_CurrencyID = v_CompanyCurrencyID_
            THEN
				SET @stm2 = CONCAT('
				    ( tblTempRateTablePKGRate.OneOffCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OneOffCost,
				    ( tblTempRateTablePKGRate.MonthlyCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS MonthlyCost,
				    ( tblTempRateTablePKGRate.PackageCostPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS PackageCostPerMinute,
				    ( tblTempRateTablePKGRate.RecordingCostPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS RecordingCostPerMinute,
				');
			ELSE
				SET @stm2 = CONCAT('
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTablePKGRate.OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OneOffCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTablePKGRate.MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS MonthlyCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTablePKGRate.PackageCostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS PackageCostPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTablePKGRate.RecordingCostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS RecordingCostPerMinute,
				');
			END IF;
        ELSE
            SET @stm2 = CONCAT('
                    tblTempRateTablePKGRate.OneOffCost AS OneOffCost,
                    tblTempRateTablePKGRate.MonthlyCost AS MonthlyCost,
                    tblTempRateTablePKGRate.PackageCostPerMinute AS PackageCostPerMinute,
                    tblTempRateTablePKGRate.RecordingCostPerMinute AS RecordingCostPerMinute,
                ');
		END IF;

		SET @stm3 = CONCAT('
				tblTempRateTablePKGRate.OneOffCostCurrency,
				tblTempRateTablePKGRate.MonthlyCostCurrency,
				tblTempRateTablePKGRate.PackageCostPerMinuteCurrency,
				tblTempRateTablePKGRate.RecordingCostPerMinuteCurrency,
				tblTempRateTablePKGRate.EffectiveDate,
				tblTempRateTablePKGRate.EndDate,
				 -- if rate table is not vendor rate table and Rate Approval Process is on then rate will be upload as not approved
				IF(',v_RateTableAppliedTo_,' !=2,IF(',v_RateApprovalProcess_,'=1,0,1),1) AS ApprovedStatus
			FROM tmp_TempRateTablePKGRate_ as tblTempRateTablePKGRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTablePKGRate.Code
				AND tblRate.CompanyID = ',p_companyId,'
				AND tblRate.CodeDeckId = tblTempRateTablePKGRate.CodeDeckId
			LEFT JOIN tblRateTablePKGRate
				ON tblRate.RateID = tblRateTablePKGRate.RateId
				AND tblRateTablePKGRate.RateTableId = ',p_RateTableId,'
				AND tblRateTablePKGRate.TimezonesID = tblTempRateTablePKGRate.TimezonesID
				AND tblTempRateTablePKGRate.EffectiveDate = tblRateTablePKGRate.EffectiveDate
			WHERE tblRateTablePKGRate.RateTablePKGRateID IS NULL
				AND tblTempRateTablePKGRate.Change NOT IN ("Delete", "R", "D", "Blocked","Block")
				AND tblTempRateTablePKGRate.EffectiveDate >= DATE_FORMAT (NOW(), "%Y-%m-%d");
		');

		SET @stm4 = CONCAT(@stm1,@stm2,@stm3);

		PREPARE stm4 FROM @stm4;
		EXECUTE stm4;
		DEALLOCATE PREPARE stm4;

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
			from 	tblRateTablePKGRate
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

				UPDATE  tblRateTablePKGRate vr1
				inner join
				(
					select
						RateTableId,
						RateID,
						EffectiveDate,
						TimezonesID
					FROM tblRateTablePKGRate
					WHERE RateTableId = p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
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

	call prc_ArchiveOldRateTablePKGRate(p_RateTableId, NULL,p_UserName);

	DELETE  FROM tblTempRateTablePKGRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTablePKGRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_CronJobAllPending`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobAllPending`(
	IN `p_CompanyID` INT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CDR'
		AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
	ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CDR'
		AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID,
	   TBL1.JobLoggedUserID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BI'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BI'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		tblCronJobCommand.Command,
		tblCronJob.CronJobID
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Status = 1
	AND tblCronJob.Active = 0;






	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BIS'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BIS'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'RCC'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'RCC'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'RCV'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'RCV'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;





	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'INU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'INU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
			AND j.Options like '%"Format":"Rate Sheet"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
			AND j.Options like '%"Format":"Rate Sheet"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BIR'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BIR'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BLE'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BLE'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BAE'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BAE'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VU'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


    SELECT
    	  "CodeDeckUpload",
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			   @prev_JobLoggedUserID  := j.JobLoggedUserID,
 			   @prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'CDU'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'CDU'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;

	SELECT
    	  "ImportTranslations",
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			   @prev_JobLoggedUserID  := j.JobLoggedUserID,
 			   @prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'ILT'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'ILT'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;


    SELECT
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
				@prev_JobLoggedUserID  := j.JobLoggedUserID,
				@prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'IR'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'IR'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'GRT'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'GRT'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'RTU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'RTU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'DRTU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'DRTU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'PRTU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'PRTU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


    SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VDR'
		AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
	ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VDR'
		AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'MGA'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'MGA'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'DSU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'DSU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'QIP'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'QIP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'ICU'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'ICU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'IU'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'IU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Mor"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Mor"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Mor"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Mor"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'XIP'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'XIP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"M2"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"M2"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"M2"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"M2"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'QPP'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'QPP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	SELECT
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
				@prev_JobLoggedUserID  := j.JobLoggedUserID,
				@prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'BDS'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'BDS'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;




    SELECT
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
				@prev_JobLoggedUserID  := j.JobLoggedUserID,
				@prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'DR'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'DR'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;

      SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BCS'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BCS'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'TRO'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.created_at,j.JobLoggedUserID ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			count(*) as COUNTER
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'TRO'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL2.COUNTER = 0
	WHERE TBL1.rowno = 1
	AND TBL2.COUNTER = 0
	limit 1;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'TRM'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.created_at,j.JobLoggedUserID ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			count(*) as COUNTER
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'TRM'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL2.COUNTER = 0
	WHERE TBL1.rowno = 1
	AND TBL2.COUNTER = 0
	limit 1;



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;