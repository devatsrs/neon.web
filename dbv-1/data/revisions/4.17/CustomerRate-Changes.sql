use Ratemanagement3;


INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1333, 'CustomersRates.Create', 1, 3);

INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('CustomersRates.create', 'CustomersRatesController.create', 1, 'Vasim Seta', NULL, '2018-04-13 09:37:36.000', '2018-04-13 09:37:36.000', 1333);

ALTER TABLE `tblCustomerRate`
	ADD COLUMN `CreatedBy` VARCHAR(50) NULL DEFAULT NULL AFTER `CreatedDate`,
	ADD COLUMN `EndDate` DATE NULL DEFAULT NULL AFTER `EffectiveDate`;

ALTER TABLE `tblRateSheetDetails`
	ADD COLUMN `EndDate` DATETIME NULL AFTER `EffectiveDate`;

RENAME TABLE `tblCustomerRateArchive` TO `tblCustomerRateArchive_old`;
CREATE TABLE IF NOT EXISTS `tblCustomerRateArchive` (
  `CustomerRateArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerRateID` int(11) DEFAULT NULL,
  `AccountId` int(11) NOT NULL,
  `TrunkID` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` datetime NOT NULL,
  `EndDate` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `RoutinePlan` int(11) DEFAULT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CustomerRateArchiveID`),
  KEY `CustomerRateID` (`CustomerRateID`),
  KEY `AccountId` (`AccountId`),
  KEY `TrunkID` (`TrunkID`),
  KEY `RateId` (`RateId`),
  KEY `EffectiveDate` (`EffectiveDate`),
  KEY `EndDate` (`EndDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;








DROP PROCEDURE IF EXISTS `prc_ArchiveOldCustomerRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldCustomerRate`(
	IN `p_AccountIds` LONGTEXT,
	IN `p_TrunkIds` LONGTEXT,
	IN `p_DeletedBy` VARCHAR(50)
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
		tblCustomerRate cr
	INNER JOIN tblCustomerRate cr2
		ON cr2.CustomerID = cr.CustomerID
		AND cr2.TrunkID = cr.TrunkID
		AND cr2.RateID = cr.RateID
	SET
		cr.EndDate=NOW()
	WHERE
		FIND_IN_SET(cr.CustomerID,p_AccountIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 AND cr.EffectiveDate <= NOW() AND
		FIND_IN_SET(cr2.CustomerID,p_AccountIds) != 0 AND FIND_IN_SET(cr2.TrunkID,p_TrunkIds) != 0 AND cr2.EffectiveDate <= NOW() AND
		cr.EffectiveDate < cr2.EffectiveDate;


	/*1. Move Rates which EndDate <= now() */

	INSERT INTO tblCustomerRateArchive
	SELECT DISTINCT  null , -- Primary Key column
		`CustomerRateID`,
		`CustomerID`,
		`TrunkID`,
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
		FIND_IN_SET(CustomerID,p_AccountIds) != 0 AND FIND_IN_SET(TrunkID,p_TrunkIds) != 0 AND EndDate <= NOW();


	DELETE  cr
	FROM tblCustomerRate cr
	inner join tblCustomerRateArchive cra
	on cr.CustomerRateID = cra.CustomerRateID
	WHERE  FIND_IN_SET(cr.CustomerID,p_AccountIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END//
DELIMITER ;









DROP PROCEDURE IF EXISTS `prc_CustomerRateClear`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerRateClear`(
	IN `p_AccountIdList` LONGTEXT,
	IN `p_TrunkId` INT,
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
			( -- if single or selected rates delete
			  p_CustomerRateId IS NOT NULL AND
        FIND_IN_SET(c.CustomerRateId,p_CustomerRateId) != 0
      )
			OR
			( -- if bulk rates delete
				p_CustomerRateId IS NULL AND
				FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0  AND
				( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%'))) AND
				( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%'))) AND
				( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) )
			)
	) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID
	SET
		tblCustomerRate.EndDate=NOW();

	CALL prc_ArchiveOldCustomerRate(p_AccountIdList,p_TrunkId,p_ModifiedBy);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;







DROP PROCEDURE IF EXISTS `prc_CustomerRateUpdateBySelectedRateId`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerRateUpdateBySelectedRateId`(
	IN `p_CompanyId` INT,
	IN `p_AccountIdList` LONGTEXT ,
	IN `p_RateIDList` LONGTEXT ,
	IN `p_TrunkId` VARCHAR(100) ,
	IN `p_Rate` DECIMAL(18, 6) ,
	IN `p_ConnectionFee` DECIMAL(18, 6) ,
	IN `p_EffectiveDate` DATETIME ,
	IN `p_EndDate` DATETIME,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_RoutinePlan` INT,
	IN `p_ModifiedBy` VARCHAR(50)
)
ThisSP:BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	UPDATE  tblCustomerRate
	INNER JOIN (
		SELECT
			c.CustomerRateID,c.EffectiveDate,
			CASE WHEN ctr.TrunkID IS NOT NULL
			THEN p_RoutinePlan
			ELSE 0
			END AS RoutinePlan
		FROM
			tblCustomerRate c
		INNER JOIN tblCustomerTrunk ON tblCustomerTrunk.TrunkID = c.TrunkID
			AND tblCustomerTrunk.AccountID = c.CustomerID
			AND tblCustomerTrunk.Status = 1
			AND c.EffectiveDate = p_EffectiveDate
		LEFT JOIN tblCustomerTrunk ctr
			ON ctr.TrunkID = c.TrunkID
			AND ctr.AccountID = c.CustomerID
			AND ctr.RoutinePlanStatus = 1
	) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID and cr.EffectiveDate = p_EffectiveDate
	SET
		/*tblCustomerRate.PreviousRate = Rate ,
		tblCustomerRate.Rate = p_Rate ,
		tblCustomerRate.ConnectionFee = p_ConnectionFee,
		tblCustomerRate.EffectiveDate = p_EffectiveDate ,
		tblCustomerRate.Interval1 = p_Interval1,
		tblCustomerRate.IntervalN = p_IntervalN,
		tblCustomerRate.RoutinePlan = cr.RoutinePlan,*/
		tblCustomerRate.LastModifiedBy = p_ModifiedBy ,
		tblCustomerRate.LastModifiedDate = NOW(),
		tblCustomerRate.EndDate = NOW()
	WHERE tblCustomerRate.TrunkID = p_TrunkId
		AND FIND_IN_SET(tblCustomerRate.RateID,p_RateIDList) != 0
		AND FIND_IN_SET(tblCustomerRate.CustomerID,p_AccountIdList) != 0;

   CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId, p_ModifiedBy);

        INSERT  INTO tblCustomerRate
                ( RateID ,
                  CustomerID ,
                  TrunkID ,
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
                SELECT  r.RateID,
                        r.AccountId ,
                        p_TrunkId ,
                        p_Rate ,
								p_ConnectionFee,
                        p_EffectiveDate ,
                        p_EndDate,
                        p_Interval1,
					    		p_IntervalN,
								RoutinePlan,
                        NOW() ,
                        p_ModifiedBy ,
                        NOW()
                FROM    ( SELECT    tblRate.RateID ,
                                    a.AccountId,
                                    tblRate.CompanyID,
									RoutinePlan
                          FROM      tblRate ,
                                    ( SELECT  a.AccountId,
													CASE WHEN ctr.TrunkID IS NOT NULL
														THEN p_RoutinePlan
														ELSE 0
													END AS RoutinePlan
                                      FROM tblAccount a
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
                        LEFT JOIN ( SELECT DISTINCT
                                                    c.RateID,
                                                    c.CustomerID as AccountId ,
                                                    c.TrunkID,
                                                    c.EffectiveDate
                                          FROM      tblCustomerRate c
                                                    INNER JOIN tblCustomerTrunk ON tblCustomerTrunk.TrunkID = c.TrunkID
                                                              AND tblCustomerTrunk.AccountID = c.CustomerID
                                                              AND tblCustomerTrunk.Status = 1
                                          WHERE FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 AND c.TrunkID = p_TrunkId
                                        ) cr ON r.RateID = cr.RateID
                                                AND r.AccountId = cr.AccountId
                                                AND r.CompanyID = p_CompanyId
                                                and cr.EffectiveDate = p_EffectiveDate
                WHERE
                 r.CompanyID = p_CompanyId
                 and cr.RateID is NULL;

 --  	CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId, p_ModifiedBy);

      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;









DROP PROCEDURE IF EXISTS `prc_CustomerBulkRateUpdate`;
DELIMITER //
CREATE PROCEDURE `prc_CustomerBulkRateUpdate`(
	IN `p_AccountIdList` LONGTEXT ,
	IN `p_TrunkId` INT ,
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
   UPDATE  tblCustomerRate
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
				AND c.TrunkID = p_TrunkId
	         AND FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0
			) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID and cr.EffectiveDate = p_EffectiveDate
		 SET /*tblCustomerRate.PreviousRate = Rate ,
           tblCustomerRate.Rate = p_Rate ,
			  tblCustomerRate.ConnectionFee = p_ConnectionFee ,
           tblCustomerRate.EffectiveDate = p_EffectiveDate ,
           tblCustomerRate.Interval1 = p_Interval1,
			  tblCustomerRate.IntervalN = p_IntervalN,
			  tblCustomerRate.RoutinePlan = cr.RoutinePlan,*/
           tblCustomerRate.LastModifiedBy = p_ModifiedBy ,
           tblCustomerRate.LastModifiedDate = NOW(),
		   tblCustomerRate.EndDate = NOW();


     CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId, p_ModifiedBy);

        INSERT  INTO tblCustomerRate
                ( RateID ,
                  CustomerID ,
                  TrunkID ,
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
                SELECT  r.RateID ,
                        r.AccountId ,
                        p_TrunkId ,
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
                FROM    ( SELECT    RateID,Code,AccountId,CompanyID,CodeDeckId,Description,CountryID,RoutinePlan
                          FROM      tblRate ,
                                    (  SELECT   a.AccountId,
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
												WHERE FIND_IN_SET(a.AccountID,p_AccountIdList) != 0
                                    ) a
                        ) r
                        LEFT OUTER JOIN ( SELECT DISTINCT
                                                    RateID ,
                                                    c.CustomerID as AccountId ,
                                                    c.TrunkID,
                                                    c.EffectiveDate
                                          FROM      tblCustomerRate c
														INNER JOIN tblCustomerTrunk
															ON tblCustomerTrunk.TrunkID = c.TrunkID
                                          	AND tblCustomerTrunk.AccountID = c.CustomerID
                                             AND tblCustomerTrunk.Status = 1
                                          WHERE FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 AND c.TrunkID = p_TrunkId
                                        ) cr ON r.RateID = cr.RateID
                                                AND r.AccountId = cr.AccountId
                                                AND r.CompanyID = p_CompanyId
                                                and cr.EffectiveDate = p_EffectiveDate
                WHERE   r.CompanyID = p_CompanyId
						AND r.CodeDeckId=p_CodeDeckId
                  AND ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
						AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
						AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) )
      				AND cr.RateID IS NULL;

     CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId, p_ModifiedBy);

    	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;










DROP PROCEDURE IF EXISTS `prc_GetCustomerRate`;
DELIMITER //
CREATE PROCEDURE `prc_GetCustomerRate`(
	IN `p_companyid` INT,
	IN `p_AccountID` INT,
	IN `p_trunkID` INT,
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
BEGIN
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
            AND (p_RoutinePlan = 0 or tblCustomerRate.RoutinePlan = p_RoutinePlan)
            AND CustomerID = p_AccountID

            ORDER BY
                tblCustomerRate.TrunkId, tblCustomerRate.CustomerId,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;







	 	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates4_;
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);
			DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
			AND n1.TrunkID = n2.TrunkID
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
                p_trunkID as TrunkID
            FROM tblRateTableRate
            INNER JOIN tblRate
                ON tblRateTableRate.RateID = tblRate.RateID
            WHERE (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
            AND (tblRate.CompanyID = p_companyid)
            AND tblRate.CodeDeckId = v_codedeckid_
            AND RateTableID = v_ratetableid_
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









DROP PROCEDURE IF EXISTS `prc_GetCustomerRatesArchiveGrid`;
DELIMITER //
CREATE PROCEDURE `prc_GetCustomerRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
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
		FIND_IN_SET (r.Code, p_Codes) != 0
	ORDER BY
		cra.EffectiveDate DESC, cra.created_at DESC;
END//
DELIMITER ;










DROP PROCEDURE IF EXISTS `prc_getDiscontinuedCustomerRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedCustomerRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
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











DROP PROCEDURE IF EXISTS `vwCustomerRate`;
DELIMITER //
CREATE PROCEDURE `vwCustomerRate`(
	IN `p_CustomerID` INT,
	IN `p_Trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(20),
	IN `p_CustomDate` DATE
)
BEGIN

	DECLARE v_codedeckid_ INT;
    DECLARE v_ratetableid_ INT;
    DECLARE v_RateTableAssignDate_ DATETIME;
    DECLARE v_NewA2ZAssign_ INT;
    DECLARE v_companyid_ INT;
    DECLARE v_TrunkID_ INT;
    DECLARE v_pointer_ INT ;
    DECLARE v_rowCount_ INT ;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


    DROP TEMPORARY TABLE IF EXISTS tmp_customerrateall_;
    CREATE TEMPORARY TABLE tmp_customerrateall_ (
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

    DROP TEMPORARY TABLE IF EXISTS tmp_customerrateall_archive_;
    CREATE TEMPORARY TABLE tmp_customerrateall_archive_ (
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

    DROP TEMPORARY TABLE IF EXISTS tmp_trunks_;
    CREATE TEMPORARY TABLE tmp_trunks_  (
        TrunkID INT,
        RowNo INT
    );

    SELECT
        CompanyId INTO v_companyid_
    FROM tblAccount
    WHERE AccountID = p_CustomerID;

    INSERT INTO tmp_trunks_
    SELECT TrunkID,
        @row_num := @row_num+1 AS RowID
    FROM tblCustomerTrunk,(SELECT @row_num := 0) x
    WHERE  FIND_IN_SET(tblCustomerTrunk.TrunkID,p_Trunks)!= 0
        AND tblCustomerTrunk.AccountID = p_CustomerID;

    SET v_pointer_ = 1;
    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_trunks_);

    WHILE v_pointer_ <= v_rowCount_
    DO
        SET v_TrunkID_ = (SELECT TrunkID FROM tmp_trunks_ t WHERE t.RowNo = v_pointer_);

        CALL prc_GetCustomerRate(v_companyid_,p_CustomerID,v_TrunkID_,null,null,null,p_Effective,p_CustomDate,1,0,0,0,'','',-1);

        INSERT INTO tmp_customerrateall_
        SELECT * FROM tmp_customerrate_;

        CALL vwCustomerArchiveCurrentRates(v_companyid_,p_CustomerID,v_TrunkID_,p_Effective,p_CustomDate);

        INSERT INTO tmp_customerrateall_archive_
        SELECT * FROM tmp_customerrate_archive_;

        SET v_pointer_ = v_pointer_ + 1;
    END WHILE;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;













DROP PROCEDURE IF EXISTS `prc_WSGenerateSippySheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateSippySheet`(
	IN `p_CustomerID` INT ,
	IN `p_Trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN

	-- get customer rates
	CALL vwCustomerRate(p_CustomerID,p_Trunks,p_Effective,p_CustomDate);

	SELECT
		CASE WHEN EndDate IS NOT NULL THEN
			'SA'
		ELSE
			'A'
		END AS `Action [A|D|U|S|SA`,
		'' as id,
		Concat(IFNULL(Prefix,''), Code) as Prefix,
		Description as COUNTRY ,
		Interval1 as `Interval 1`,
		IntervalN as `Interval N`,
		Rate as `Price 1`,
		Rate as `Price N`,
		0  as Forbidden,
		0 as `Grace Period`,
		DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' ) AS `Activation Date`,
		DATE_FORMAT( EndDate, '%Y-%m-%d %H:%i:%s' )  AS `Expiration Date`
	FROM
		tmp_customerrateall_
	ORDER BY
		Prefix;

END//
DELIMITER ;











DROP PROCEDURE IF EXISTS `prc_WSGenerateVersion3VosSheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateVersion3VosSheet`(
	IN `p_CustomerID` INT ,
	IN `p_trunks` varchar(200) ,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_Format` VARCHAR(50)
)
BEGIN

	-- get customer rates
	CALL vwCustomerRate(p_CustomerID,p_Trunks,p_Effective,p_CustomDate);

	IF p_Effective = 'Now' OR p_Format = 'Vos 2.0'
	THEN

		SELECT distinct
			IFNULL(RatePrefix, '') as `Rate Prefix` ,
			Concat(IFNULL(AreaPrefix,''), Code) as `Area Prefix` ,
			'International' as `Rate Type` ,
			Description  as `Area Name`,
			Rate / 60  as `Billing Rate`,
			IntervalN as `Billing Cycle`,
			Rate as `Minute Cost` ,
			'No Lock'  as `Lock Type`,
			CASE WHEN Interval1 != IntervalN
			THEN
				Concat('0,', Rate, ',',Interval1)
			ELSE
				''
			END as `Section Rate`,
			0 AS `Billing Rate for Calling Card Prompt`,
			0  as `Billing Cycle for Calling Card Prompt`
		FROM   tmp_customerrateall_
		ORDER BY `Rate Prefix`;

	END IF;

	IF (p_Effective = 'Future' OR  p_Effective = 'All' OR p_Effective = 'CustomDate') AND p_Format = 'Vos 3.2'
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_customerrateall_2_ ;
		CREATE TEMPORARY TABLE tmp_customerrateall_2_ SELECT * FROM tmp_customerrateall_;

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
		FROM
		(
			SELECT distinct
				CONCAT(EffectiveDate,' 00:00') as `Time of timing replace`,
				'Append replace' as `Mode of timing replace`,
				IFNULL(RatePrefix, '') as `Rate Prefix` ,
				Concat(IFNULL(AreaPrefix,''), Code) as `Area Prefix` ,
				'International' as `Rate Type` ,
				Description  as `Area Name`,
				Rate / 60  as `Billing Rate`,
				IntervalN as `Billing Cycle`,
				Rate as `Minute Cost` ,
				'No Lock'  as `Lock Type`,
				CASE WHEN Interval1 != IntervalN
				THEN
					Concat('0,', Rate, ',',Interval1)
				ELSE
					''
				END as `Section Rate`,
				0 AS `Billing Rate for Calling Card Prompt`,
				0  as `Billing Cycle for Calling Card Prompt`
			FROM   tmp_customerrateall_2_
			-- ORDER BY `Rate Prefix`;

			UNION ALL

			SELECT distinct
				CONCAT(EffectiveDate,' 00:00') as `Time of timing replace`,
				'Delete' as `Mode of timing replace`,
				IFNULL(RatePrefix, '') as `Rate Prefix` ,
				Concat(IFNULL(AreaPrefix,''), Code) as `Area Prefix` ,
				'International' as `Rate Type` ,
				Description  as `Area Name`,
				Rate / 60  as `Billing Rate`,
				IntervalN as `Billing Cycle`,
				Rate as `Minute Cost` ,
				'No Lock'  as `Lock Type`,
				CASE WHEN Interval1 != IntervalN
				THEN
					Concat('0,', Rate, ',',Interval1)
				ELSE
					''
				END as `Section Rate`,
				0 AS `Billing Rate for Calling Card Prompt`,
				0  as `Billing Cycle for Calling Card Prompt`
			FROM   tmp_customerrateall_
			WHERE  EndDate IS NOT NULL

			UNION ALL

			SELECT distinct
				CONCAT(EffectiveDate,' 00:00') as `Time of timing replace`,
				'Delete' as `Mode of timing replace`,
				IFNULL(RatePrefix, '') as `Rate Prefix` ,
				Concat(IFNULL(AreaPrefix,''), Code) as `Area Prefix` ,
				'International' as `Rate Type` ,
				Description  as `Area Name`,
				Rate / 60  as `Billing Rate`,
				IntervalN as `Billing Cycle`,
				Rate as `Minute Cost` ,
				'No Lock'  as `Lock Type`,
				CASE WHEN Interval1 != IntervalN
				THEN
					Concat('0,', Rate, ',',Interval1)
				ELSE
					''
				END as `Section Rate`,
				0 AS `Billing Rate for Calling Card Prompt`,
				0  as `Billing Cycle for Calling Card Prompt`
			FROM   tmp_customerrateall_archive_
		) tmp
		ORDER BY `Rate Prefix`;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;












DROP PROCEDURE IF EXISTS `prc_CronJobGenerateMorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGenerateMorSheet`(
	IN `p_CustomerID` INT ,
	IN `p_trunks` VARCHAR(200) ,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN

	-- get customer rates
	CALL vwCustomerRate(p_CustomerID,p_Trunks,p_Effective,p_CustomDate);

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
        RoutinePlanName VARCHAR(50),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        TrunkID INT,
        RateTableRateId INT,
        IncludePrefix TINYINT,
        Prefix VARCHAR(50),
        RatePrefix VARCHAR(50),
        AreaPrefix VARCHAR(50),
        SubCode VARCHAR(50)
    );

    INSERT INTO tmp_morrateall_
     SELECT
		  tc.RateID,
	  	  c.Country,
		  c.ISO3,
		  tc.Code,
		  tc.Description,
		  tc.Interval1,
        tc.IntervalN,
        tc.ConnectionFee,
        tc.RoutinePlanName,
        tc.Rate,
        tc.EffectiveDate,
        tc.LastModifiedDate,
        tc.LastModifiedBy,
        tc.CustomerRateId,
        tc.TrunkID,
        tc.RateTableRateId,
        tc.IncludePrefix,
        tc.Prefix,
        tc.RatePrefix,
        tc.AreaPrefix,
        'FIX' as `SubCode`
	  	 FROM tmp_customerrateall_ tc
	  			 INNER JOIN tblRate r ON tc.RateID = r.RateID
				 LEFT JOIN tblCountry c ON r.CountryID = c.CountryID
					;

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













DROP PROCEDURE IF EXISTS `prc_CronJobGenerateM2Sheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGenerateM2Sheet`(
	IN `p_CustomerID` INT ,
	IN `p_trunks` VARCHAR(200) ,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN

	-- get customer rates
	CALL vwCustomerRate(p_CustomerID,p_Trunks,p_Effective,p_CustomDate);

		SELECT DISTINCT
	       Description  as `Destination`,
		   Code as `Prefix`,
		   Rate as `Rate(USD)`,
		   ConnectionFee as `Connection Fee(USD)`,
		   Interval1 as `Increment`,
		   IntervalN as `Minimal Time`,
		   '0:00:00 'as `Start Time`,
		   '23:59:59' as `End Time`,
		   '' as `Week Day`,
		   EffectiveDate  as `Effective from`,
			RoutinePlanName as `Routing through`
     FROM tmp_customerrateall_ ;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;











DROP PROCEDURE IF EXISTS `prc_WSGenerateRateSheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateSheet`(
	IN `p_CustomerID` INT,
	IN `p_Trunk` VARCHAR(100)
)
BEGIN
		DECLARE v_trunkDescription_ VARCHAR(50);
		DECLARE v_lastRateSheetID_ INT ;
		DECLARE v_IncludePrefix_ TINYINT;
		DECLARE v_Prefix_ VARCHAR(50);
		DECLARE v_codedeckid_  INT;
		DECLARE v_ratetableid_ INT;
		DECLARE v_RateTableAssignDate_  DATETIME;
		DECLARE v_NewA2ZAssign_ INT;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


		SELECT trunk INTO v_trunkDescription_
		FROM   tblTrunk
		WHERE  TrunkID = p_Trunk;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate_;
		CREATE TEMPORARY TABLE tmp_RateSheetRate_(
			RateID        INT,
			Destination   VARCHAR(200),
			Codes         VARCHAR(50),
			Interval1     INT,
			IntervalN     INT,
			Rate          DECIMAL(18, 6),
			`level`         VARCHAR(50),
			`change`        VARCHAR(50),
			EffectiveDate  DATE,
			EndDate  DATE,
			INDEX tmp_RateSheetRate_RateID (`RateID`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
		CREATE TEMPORARY TABLE tmp_CustomerRates_ (
			RateID INT,
			Interval1 INT,
			IntervalN  INT,
			Rate DECIMAL(18, 6),
			EffectiveDate DATE,
			EndDate DATE,
			LastModifiedDate DATETIME,
			INDEX tmp_CustomerRates_RateId (`RateID`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
		CREATE TEMPORARY TABLE tmp_RateTableRate_ (
			RateID INT,
			Interval1 INT,
			IntervalN  INT,
			Rate DECIMAL(18, 6),
			EffectiveDate DATE,
			EndDate DATE,
			updated_at DATETIME,
			INDEX tmp_RateTableRate_RateId (`RateID`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail_;
		CREATE TEMPORARY TABLE tmp_RateSheetDetail_ (
			ratesheetdetailsid int,
			RateID int ,
			RateSheetID int,
			Destination varchar(200),
			Code varchar(50),
			Rate DECIMAL(18, 6),
			`change` varchar(50),
			EffectiveDate Date,
			EndDate DATE,
			INDEX tmp_RateSheetDetail_RateId (`RateID`,`RateSheetID`)
		);
		SELECT RateSheetID INTO v_lastRateSheetID_
		FROM   tblRateSheet
		WHERE  CustomerID = p_CustomerID
					 AND level = v_trunkDescription_
		ORDER  BY RateSheetID DESC LIMIT 1 ;

		SELECT includeprefix INTO v_IncludePrefix_
		FROM   tblCustomerTrunk
		WHERE  AccountID = p_CustomerID
					 AND TrunkID = p_Trunk;

		SELECT prefix INTO v_Prefix_
		FROM   tblCustomerTrunk
		WHERE  AccountID = p_CustomerID
					 AND TrunkID = p_Trunk;


		SELECT
			CodeDeckId,
			RateTableID,
			RateTableAssignDate
		INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
		FROM tblCustomerTrunk
		WHERE tblCustomerTrunk.TrunkID = p_Trunk
					AND tblCustomerTrunk.AccountID = p_CustomerID
					AND tblCustomerTrunk.Status = 1;

		INSERT INTO tmp_CustomerRates_
			SELECT  tblCustomerRate.RateID,
				tblCustomerRate.Interval1,
				tblCustomerRate.IntervalN,
				tblCustomerRate.Rate,
				effectivedate,
				tblCustomerRate.EndDate,
				lastmodifieddate
			FROM   tblAccount
				JOIN tblCustomerRate
					ON tblAccount.AccountID = tblCustomerRate.CustomerID
				JOIN tblRate
					ON tblRate.RateId = tblCustomerRate.RateId
						 AND tblRate.CodeDeckId = v_codedeckid_
			WHERE  tblAccount.AccountID = p_CustomerID
						 AND tblCustomerRate.TrunkID = p_Trunk
			ORDER BY tblCustomerRate.CustomerID,tblCustomerRate.TrunkID,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;

		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates4_;
		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates2_;

		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);
		DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
																																			 AND  n1.RateId = n2.RateId;

		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates2_ as (select * from tmp_CustomerRates_);

		INSERT INTO tmp_RateTableRate_
			SELECT
				tblRateTableRate.RateID,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.Rate,
				tblRateTableRate.EffectiveDate,
				tblRateTableRate.EndDate,
				tblRateTableRate.updated_at
			FROM tblAccount
				JOIN tblCustomerTrunk
					ON tblCustomerTrunk.AccountID = tblAccount.AccountID
				JOIN tblRateTable
					ON tblCustomerTrunk.ratetableid = tblRateTable.ratetableid
				JOIN tblRateTableRate
					ON tblRateTableRate.ratetableid = tblRateTable.ratetableid
				LEFT JOIN tmp_CustomerRates_ trc1
					ON trc1.RateID = tblRateTableRate.RateID
			WHERE  tblAccount.AccountID = p_CustomerID

						 AND tblCustomerTrunk.TrunkID = p_Trunk
						 AND (( tblRateTableRate.EffectiveDate <= Now()
										AND ( ( trc1.RateID IS NULL )
													OR ( trc1.RateID IS NOT NULL
															 AND tblRateTableRate.ratetablerateid IS NULL )
										) )
									OR ( tblRateTableRate.EffectiveDate > Now()
											 AND ( ( trc1.RateID IS NULL )
														 OR ( trc1.RateID IS NOT NULL
																	AND tblRateTableRate.EffectiveDate < (SELECT
																																					IFNULL(MIN(crr.EffectiveDate),
																																								 tblRateTableRate.EffectiveDate)
																																				FROM   tmp_CustomerRates2_ crr
																																				WHERE  crr.RateID =
																																							 tblRateTableRate.RateID
			) ) ) ) )
			ORDER BY tblRateTableRate.RateID,tblRateTableRate.EffectiveDate desc;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate4_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
		DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
																																			 AND  n1.RateId = n2.RateId;

		INSERt INTO tmp_RateSheetDetail_
			SELECT ratesheetdetailsid,
				RateID,
				RateSheetID,
				Destination,
				Code,
				Rate,
				`change`,
				effectivedate,
				EndDate
			FROM   tblRateSheetDetails
			WHERE  RateSheetID = v_lastRateSheetID_
			ORDER BY RateID,effectivedate desc;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail4_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetDetail4_ as (select * from tmp_RateSheetDetail_);
		DELETE n1 FROM tmp_RateSheetDetail_ n1, tmp_RateSheetDetail4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
																																					 AND  n1.RateId = n2.RateId;


		DROP TABLE IF EXISTS tmp_CloneRateSheetDetail_ ;
		CREATE TEMPORARY TABLE tmp_CloneRateSheetDetail_ LIKE tmp_RateSheetDetail_;
		INSERT tmp_CloneRateSheetDetail_ SELECT * FROM tmp_RateSheetDetail_;


		INSERT INTO tmp_RateSheetRate_
			SELECT tbl.RateID,
				description,
				Code,
				tbl.Interval1,
				tbl.IntervalN,
				tbl.Rate,
				v_trunkDescription_,
				'NEW' as `change`,
				tbl.EffectiveDate,
				tbl.EndDate
			FROM   (
							 SELECT
								 rt.RateID,
								 rt.Interval1,
								 rt.IntervalN,
								 rt.Rate,
								 rt.EffectiveDate,
								 rt.EndDate
							 FROM   tmp_RateTableRate_ rt
								 LEFT JOIN tblRateSheet
									 ON tblRateSheet.RateSheetID =
											v_lastRateSheetID_
								 LEFT JOIN tmp_RateSheetDetail_ as  rsd
									 ON rsd.RateID = rt.RateID AND rsd.RateSheetID = v_lastRateSheetID_
							 WHERE  rsd.ratesheetdetailsid IS NULL

							 UNION

							 SELECT
								 trc2.RateID,
								 trc2.Interval1,
								 trc2.IntervalN,
								 trc2.Rate,
								 trc2.EffectiveDate,
								 trc2.EndDate
							 FROM   tmp_CustomerRates_ trc2
								 LEFT JOIN tblRateSheet
									 ON tblRateSheet.RateSheetID =
											v_lastRateSheetID_
								 LEFT JOIN tmp_CloneRateSheetDetail_ as  rsd2
									 ON rsd2.RateID = trc2.RateID AND rsd2.RateSheetID = v_lastRateSheetID_
							 WHERE  rsd2.ratesheetdetailsid IS NULL

						 ) AS tbl
				INNER JOIN tblRate
					ON tbl.RateID = tblRate.RateID;

		INSERT INTO tmp_RateSheetRate_
			SELECT tbl.RateID,
				description,
				Code,
				tbl.Interval1,
				tbl.IntervalN,
				tbl.Rate,
				v_trunkDescription_,
				tbl.`change`,
				tbl.EffectiveDate,
				tbl.EndDate
			FROM   (SELECT rt.RateID,
								description,
								tblRate.Code,
								rt.Interval1,
								rt.IntervalN,
								rt.Rate,
								rsd5.Rate AS rate2,
								rt.EffectiveDate,
								rt.EndDate,
								CASE
								WHEN rsd5.Rate > rt.Rate
										 AND rsd5.Destination !=
												 description THEN
									'NAME CHANGE & DECREASE'
								ELSE
									CASE
									WHEN rsd5.Rate > rt.Rate
											 AND rt.EffectiveDate <= Now() THEN
										'DECREASE'
									ELSE
										CASE
										WHEN ( rsd5.Rate >
													 rt.Rate
													 AND rt.EffectiveDate > Now()
										)
											THEN
												'PENDING DECREASE'
										ELSE
											CASE
											WHEN ( rsd5.Rate <
														 rt.Rate
														 AND rt.EffectiveDate <=
																 Now() )
												THEN
													'INCREASE'
											ELSE
												CASE
												WHEN ( rsd5.Rate
															 <
															 rt.Rate
															 AND rt.EffectiveDate >
																	 Now() )
													THEN
														'PENDING INCREASE'
												ELSE
													CASE
													WHEN
														rsd5.Destination !=
														description THEN
														'NAME CHANGE'
													ELSE 'NO CHANGE'
													END
												END
											END
										END
									END
								END as `Change`
							FROM   tblRate
								INNER JOIN tmp_RateTableRate_ rt
									ON rt.RateID = tblRate.RateID
								INNER JOIN tblRateSheet
									ON tblRateSheet.RateSheetID = v_lastRateSheetID_
								INNER JOIN tmp_RateSheetDetail_ as  rsd5
									ON rsd5.RateID = rt.RateID
										 AND rsd5.RateSheetID =
												 v_lastRateSheetID_
							UNION
							SELECT trc4.RateID,
								description,
								tblRate.Code,
								trc4.Interval1,
								trc4.IntervalN,
								trc4.Rate,
								rsd6.Rate AS rate2,
								trc4.EffectiveDate,
								trc4.EndDate,
								CASE
								WHEN rsd6.Rate > trc4.Rate
										 AND rsd6.Destination !=
												 description THEN
									'NAME CHANGE & DECREASE'
								ELSE
									CASE
									WHEN rsd6.Rate > trc4.Rate
											 AND trc4.EffectiveDate <= Now() THEN
										'DECREASE'
									ELSE
										CASE
										WHEN ( rsd6.Rate >
													 trc4.Rate
													 AND trc4.EffectiveDate > Now()
										)
											THEN
												'PENDING DECREASE'
										ELSE
											CASE
											WHEN ( rsd6.Rate <
														 trc4.Rate
														 AND trc4.EffectiveDate <=
																 Now() )
												THEN
													'INCREASE'
											ELSE
												CASE
												WHEN ( rsd6.Rate
															 <
															 trc4.Rate
															 AND trc4.EffectiveDate >
																	 Now() )
													THEN
														'PENDING INCREASE'
												ELSE
													CASE
													WHEN
														rsd6.Destination !=
														description THEN
														'NAME CHANGE'
													ELSE 'NO CHANGE'
													END
												END
											END
										END
									END
								END as  `Change`
							FROM   tblRate
								INNER JOIN tmp_CustomerRates_ trc4
									ON trc4.RateID = tblRate.RateID
								INNER JOIN tblRateSheet
									ON tblRateSheet.RateSheetID = v_lastRateSheetID_
								INNER JOIN tmp_CloneRateSheetDetail_ as rsd6
									ON rsd6.RateID = trc4.RateID
										 AND rsd6.RateSheetID =
												 v_lastRateSheetID_
						 ) AS tbl ;

		INSERT INTO tmp_RateSheetRate_
			SELECT tblRateSheetDetails.RateID,
				tblRateSheetDetails.Destination,
				tblRateSheetDetails.Code,
				tblRateSheetDetails.Interval1,
				tblRateSheetDetails.IntervalN,
				tblRateSheetDetails.Rate,
				v_trunkDescription_,
				'DELETE',
				tblRateSheetDetails.EffectiveDate,
				tblRateSheetDetails.EndDate
			FROM   tblRate
				INNER JOIN tblRateSheetDetails
					ON tblRate.RateID = tblRateSheetDetails.RateID
						 AND tblRateSheetDetails.RateSheetID = v_lastRateSheetID_
				LEFT JOIN (SELECT DISTINCT RateID,
										 effectivedate
									 FROM   tmp_RateTableRate_
									 UNION
									 SELECT DISTINCT RateID,
										 effectivedate
									 FROM tmp_CustomerRates_) AS TBL
					ON TBL.RateID = tblRateSheetDetails.RateID
			WHERE  `change` != 'DELETE'
						 AND TBL.RateID IS NULL ;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate4_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetRate4_ as (select * from tmp_RateSheetRate_);
		DELETE n1 FROM tmp_RateSheetRate_ n1, tmp_RateSheetRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
																																			 AND  n1.RateId = n2.RateId;

		IF v_IncludePrefix_ = 1
		THEN
			SELECT rsr.RateID AS rateid,
						 rsr.Interval1 AS interval1,
						 rsr.IntervalN AS intervaln,
						 rsr.Destination AS destination,
						 rsr.Codes AS codes,
						 v_Prefix_ AS `tech prefix`,
						 CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS `interval`,
						 FORMAT(rsr.Rate,6) AS `rate per minute (usd)`,
				rsr.`level`,
				rsr.`change`,
						 rsr.EffectiveDate AS `effective date`,
						 rsr.EndDate AS `end date`
			FROM   tmp_RateSheetRate_ rsr

			ORDER BY rsr.Destination,rsr.Codes desc;
		ELSE
			SELECT rsr.RateID AS rateid ,
						 rsr.Interval1 AS interval1,
						 rsr.IntervalN AS intervaln,
						 rsr.Destination AS destination,
						 rsr.Codes AS codes,
						 CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS  `interval`,
						 FORMAT(rsr.Rate, 6) AS `rate per minute (usd)`,
				rsr.`level`,
				rsr.`change`,
						 rsr.EffectiveDate AS `effective date`,
						 rsr.EndDate AS `end date`
			FROM   tmp_RateSheetRate_ rsr

			ORDER BY rsr.Destination,rsr.Codes DESC;
		END IF;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;














