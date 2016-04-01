
DROP PROCEDURE IF EXISTS `prc_getCustomerCliRateByAccount`;

DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getCustomerCliRateByAccount`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_TrunkID` INT , IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    call prc_GetCustomerRate (p_CompanyID,p_AccountID,p_TrunkID,NULL,NULL,NULL,'All',1,0,0,0,'','',1);

    set @stm1 = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    SET cost = CASE WHEN  billed_duration >= Interval1
    THEN
    (Rate/60.0)*Interval1+CEILING((billed_duration-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+ifnull(ConnectionFee,0)
        ElSE 
        Rate+ifnull(ConnectionFee,0)
    END
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'" 
    AND trunk = (select Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" limit 1)
    AND cr.rate is not null') ;
    
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
    

    set @stm2 = CONCAT('UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    SET cost = 0.0
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND cr.rate is null ');
    
    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;

    set @stm3 = CONCAT('UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` SET cost = 0.0
    WHERE processid = "',p_processId,'" AND accountid is null');
    
    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    
    set @stm4 = CONCAT('SELECT DISTINCT ud.cld as area_prefix,ud.trunk,ud.GatewayAccountID,a.AccountName
    FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    LEFT JOIN tblAccount a ON a.AccountID = ud.AccountID
    WHERE processid ="',p_processId,'" 
    AND ud.accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND cr.rate is null');
    
    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_GetCustomerRate`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCustomerRate`(IN `p_companyid` INT, IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(50), IN `p_Effective` VARCHAR(50), IN `p_effectedRates` INT, IN `p_RoutinePlan` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT )
BEGIN
    DECLARE v_codedeckid_ INT;
    DECLARE v_ratetableid_ INT;
   DECLARE v_RateTableAssignDate_ DATETIME;
   DECLARE v_NewA2ZAssign_ INT;
   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

    SELECT
        CodeDeckId,
        RateTableID,
        RateTableAssignDate INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
    FROM tblCustomerTrunk
    WHERE CompanyID = p_companyid
    AND tblCustomerTrunk.TrunkID = p_trunkID
    AND tblCustomerTrunk.AccountID = p_AccountID
    AND tblCustomerTrunk.Status = 1;

    DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        RateTableRateID INT,
        TrunkID INT,
        RoutinePlan INT
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        RateID INT,
        Interval1 INT,
        IntervalN INT,
        Rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        RateTableRateID INT,
        TrunkID INT
    );

     

    INSERT INTO tmp_CustomerRates_

        SELECT
            RateID,
            Interval1,
            IntervalN,
            Rate,
            ConnectionFee,
            EffectiveDate,
            LastModifiedDate,
            LastModifiedBy,
            CustomerRateId,
            RateTableRateID,
            TrunkID,
            RoutinePlan

        FROM (
            SELECT
                     @row_num := IF(@prev_RateID=tblCustomerRate.RateID and @prev_effectivedate >= tblCustomerRate.effectivedate ,@row_num+1,1) AS RowID,
                tblCustomerRate.RateID,
                tblCustomerRate.Interval1,
                tblCustomerRate.IntervalN,
                tblCustomerRate.RoutinePlan,
                tblCustomerRate.Rate,
                tblCustomerRate.ConnectionFee,
                tblCustomerRate.EffectiveDate,
                tblCustomerRate.LastModifiedDate,
                tblCustomerRate.LastModifiedBy,
                tblCustomerRate.CustomerRateId,
                NULL AS RateTableRateID,
                p_trunkID as TrunkID,
                     @prev_RateID  := tblCustomerRate.RateID,
                     @prev_effectivedate  := tblCustomerRate.effectivedate
            FROM tblCustomerRate
            INNER JOIN tblRate
                ON tblCustomerRate.RateID = tblRate.RateID,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z
            WHERE (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
            AND (tblRate.CompanyID = p_companyid)
            AND tblRate.CodeDeckId = v_codedeckid_
            AND tblCustomerRate.TrunkID = p_trunkID
            AND (p_RoutinePlan = 0 or tblCustomerRate.RoutinePlan = p_RoutinePlan)
            AND CustomerID = p_AccountID
            AND (
                   (p_Effective = 'Now' AND EffectiveDate <= NOW() )
                OR (p_Effective = 'Future' AND EffectiveDate > NOW())
                OR  p_Effective = 'All'
               )
            ORDER BY
                tblCustomerRate.TrunkId, tblCustomerRate.CustomerId,tblCustomerRate.RateID,tblCustomerRate.effectivedate
            ) TBL
        WHERE  (
                   (p_Effective = 'Now' AND EffectiveDate <= NOW() AND RowID = 1)
                OR (p_Effective = 'Future' AND EffectiveDate > NOW())
                OR  p_Effective = 'All'
               );

    SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
        SELECT *,  
         @row_num := IF(@prev_RateID=tblRateTableRate.RateID and @prev_effectivedate >= tblRateTableRate.effectivedate ,@row_num+1,1) AS RowID,
         @prev_RateID  := tblRateTableRate.RateID,
         @prev_effectivedate  := tblRateTableRate.effectivedate
        FROM 
        tblRateTableRate,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z
        WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW() AND p_Effective = 'Now' 
        ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
     )tbl WHERE RowID =1;

    INSERT INTO tmp_RateTableRate_
        SELECT
            RateID,
            Interval1,
            IntervalN,
            Rate,
            ConnectionFee,
            EffectiveDate,
            LastModifiedDate,
            LastModifiedBy,
            CustomerRateId,
            RateTableRateID,
            TrunkID

        FROM (
            SELECT
                 @row_num := IF(@prev_RateID=tblRateTableRate.RateID and @prev_effectivedate >= tblRateTableRate.effectivedate ,@row_num+1,1) AS RowID, 
                tblRateTableRate.RateID,
                tblRateTableRate.Interval1,
                tblRateTableRate.IntervalN,
                tblRateTableRate.Rate,
                tblRateTableRate.ConnectionFee,
                 case when v_NewA2ZAssign_ = 1
                then
                    v_RateTableAssignDate_  
                else 
                    tblRateTableRate.EffectiveDate
                end  as EffectiveDate,
                NULL AS LastModifiedDate,
                NULL AS LastModifiedBy,
                NULL AS CustomerRateId,
                RateTableRateID,
                p_trunkID as TrunkID,
                @prev_RateID  := tblRateTableRate.RateID,
                     @prev_effectivedate  := tblRateTableRate.effectivedate
            FROM tblRateTableRate
            INNER JOIN tblRate
                ON tblRateTableRate.RateID = tblRate.RateID,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z
            WHERE (p_contryID IS NULL OR tblRate.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
            AND (tblRate.CompanyID = p_companyid)
            AND tblRate.CodeDeckId = v_codedeckid_
            AND RateTableID = v_ratetableid_
            AND (
                   (p_Effective = 'Now' AND EffectiveDate <= NOW() 
                     OR (v_NewA2ZAssign_ = 1 AND EffectiveDate >= NOW() )
                    )
                OR (p_Effective = 'Future' AND EffectiveDate > NOW())
                OR  p_Effective = 'All'
               )
                ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
            ) TBL
        WHERE (
                   (p_Effective = 'Now' AND EffectiveDate <= NOW() AND RowID = 1)
                OR (p_Effective = 'Future' AND EffectiveDate > NOW())
                OR  p_Effective = 'All'
              );



    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates2_ as (select * from tmp_CustomerRates_);
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates3_ as (select * from tmp_CustomerRates_);
    

    IF p_isExport = 0
    THEN





         SELECT
                r.RateID,
                r.Code,
                r.Description,
                case when allRates.Interval1 is null
                then 
                    r.Interval1
                else
                    allRates.Interval1
                end as Interval1,
                case when allRates.IntervalN is null
                then 
                    r.IntervalN
                else
                    allRates.IntervalN
                end as IntervalN,
                allRates.ConnectionFee,
                allRates.RoutinePlanName,
                allRates.Rate,
                allRates.EffectiveDate,
                allRates.LastModifiedDate,
                allRates.LastModifiedBy,
                allRates.CustomerRateId,
                p_trunkID as TrunkID,
                allRates.RateTableRateId
            FROM tblRate r
            LEFT JOIN (SELECT
                    CustomerRates.RateID,
                    CustomerRates.Interval1,
                    CustomerRates.IntervalN,
                    CustomerRates.ConnectionFee,
                    tblTrunk.Trunk as RoutinePlanName,
                    CustomerRates.Rate,
                    CustomerRates.EffectiveDate,
                    CustomerRates.LastModifiedDate,
                    CustomerRates.LastModifiedBy,
                    CustomerRates.CustomerRateId,
                    NULL AS RateTableRateID,
                    p_trunkID as TrunkID
                FROM tmp_CustomerRates_ CustomerRates
                left join tblTrunk on tblTrunk.TrunkID = CustomerRates.RoutinePlan

                UNION ALL

                SELECT
                DISTINCT
                    rtr.RateID,
                    rtr.Interval1,
                    rtr.IntervalN,
                    rtr.ConnectionFee,
                    NULL,
                    rtr.Rate,
                    rtr.EffectiveDate,
                    NULL,
                    NULL,
                    NULL AS CustomerRateId,
                    rtr.RateTableRateID,
                    p_trunkID as TrunkID
                FROM tmp_RateTableRate_ AS rtr
                LEFT JOIN tmp_CustomerRates2_ cr
                    ON cr.RateID = rtr.RateID
                WHERE (
                (
                    p_Effective = 'Now'
                    AND rtr.EffectiveDate <= NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (cr.RateID IS NOT NULL AND rtr.RateTableRateID IS NULL) -- Only CR Data
                        )

                )
                OR
                (
                    p_Effective = 'Future'
                    AND rtr.EffectiveDate > NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            ( cr.RateID IS NOT NULL AND rtr.EffectiveDate < (
                                                                            SELECT IFNULL(MIN(cr.EffectiveDate), rtr.EffectiveDate)
                                                                            FROM tmp_CustomerRates3_ cr
                                                                            WHERE cr.RateID = rtr.RateID
                                                                            )
                            )
                        )
                )
                OR p_Effective = 'All'

                )) allRates
                ON allRates.RateID = r.RateID

            WHERE (p_contryID IS NULL OR r.CountryID = p_contryID)
            AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
            AND (p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%'))
            AND (r.CompanyID = p_companyid)
            AND r.CodeDeckId = v_codedeckid_
            AND ((p_effectedRates = 1 AND Rate IS NOT NULL) OR  (p_effectedRates = 0))
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN r.Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN r.Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN allRates.Rate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN allRates.Rate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN allRates.Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN allRates.Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN allRates.IntervalN
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN allRates.IntervalN
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN allRates.ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN allRates.ConnectionFee
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN allRates.EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN allRates.EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedDateDESC') THEN allRates.LastModifiedDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedDateASC') THEN allRates.LastModifiedDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedByDESC') THEN allRates.LastModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastModifiedByASC') THEN allRates.LastModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CustomerRateIdDESC') THEN allRates.CustomerRateId
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CustomerRateIdASC') THEN allRates.CustomerRateId
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(r.RateID) AS totalcount
        FROM tblRate r
        LEFT JOIN (
            SELECT
                CustomerRates.RateID,
                CustomerRates.Rate,
                CustomerRates.EffectiveDate,
                CustomerRates.LastModifiedDate,
                CustomerRates.LastModifiedBy,
                CustomerRates.CustomerRateId,
                NULL AS RateTableRateID,
                p_trunkID as TrunkID
            FROM tmp_CustomerRates_ CustomerRates


            UNION ALL

            SELECT
            DISTINCT
                rtr.RateID,
                rtr.Rate,
                rtr.EffectiveDate,
                NULL,
                NULL,
                NULL AS CustomerRateId,
                rtr.RateTableRateID,
                p_trunkID as TrunkID
            FROM tmp_RateTableRate_ AS rtr
            LEFT JOIN tmp_CustomerRates2_ cr
                ON cr.RateID = rtr.RateID
            WHERE (
                (
                    p_Effective = 'Now' AND rtr.EffectiveDate <= NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (cr.RateID IS NOT NULL AND rtr.RateTableRateID IS NULL) -- Only CR Data
                        )

                )
                OR
                ( p_Effective = 'Future' AND rtr.EffectiveDate > NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (
                                cr.RateID IS NOT NULL AND rtr.EffectiveDate < (
                                                                                SELECT IFNULL(MIN(cr.EffectiveDate), rtr.EffectiveDate)
                                                                                FROM tmp_CustomerRates3_ cr
                                                                                WHERE cr.RateID = rtr.RateID
                                                                                )
                            )
                        )
                )
            OR p_Effective = 'All'

            )) allRates
            ON allRates.RateID = r.RateID
        WHERE (p_contryID IS NULL OR r.CountryID = p_contryID)
        AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
        AND (p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%'))
        AND (r.CompanyID = p_companyid)
        AND r.CodeDeckId = v_codedeckid_
        AND  ((p_effectedRates = 1 AND Rate IS NOT NULL) OR  (p_effectedRates = 0));

    END IF;

    IF p_isExport = 1
    THEN

        CREATE TEMPORARY TABLE IF NOT EXISTS  tmp_customerrate_ as (


        SELECT
            r.Code,
            r.Description,
            allRates.Interval1,
            allRates.IntervalN,
            allRates.RoutinePlanName,
            allRates.ConnectionFee,
            allRates.Rate,
            allRates.EffectiveDate,
            allRates.LastModifiedDate,
            allRates.LastModifiedBy
        FROM tblRate r
        LEFT JOIN (SELECT
                CustomerRates.RateID,
                CustomerRates.Interval1,
                CustomerRates.IntervalN,
                tblTrunk.Trunk as RoutinePlanName,
                CustomerRates.ConnectionFee,
                CustomerRates.Rate,
                CustomerRates.EffectiveDate,
                CustomerRates.LastModifiedDate,
                CustomerRates.LastModifiedBy,
                CustomerRates.CustomerRateId,
                NULL AS RateTableRateID,
                p_trunkID as TrunkID
            FROM tmp_CustomerRates_ CustomerRates
            left join tblTrunk on tblTrunk.TrunkID = CustomerRates.RoutinePlan


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
                NULL,
                NULL,
                NULL AS CustomerRateId,
                rtr.RateTableRateID,
                p_trunkID as TrunkID
            FROM tmp_RateTableRate_ AS rtr
            LEFT JOIN tmp_CustomerRates2_ as cr
                ON cr.RateID = rtr.RateID
            WHERE (
                (
                    p_Effective = 'Now' AND rtr.EffectiveDate <= NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
                            OR
                            (cr.RateID IS NOT NULL AND rtr.RateTableRateID IS NULL) -- Only CR Data
                        )

                )
                OR
                ( p_Effective = 'Future' AND rtr.EffectiveDate > NOW()
                    AND (
                            (cr.RateID IS NULL) -- no data in C... *** SQLINES FOR EVALUATION USE ONLY *** 
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
            OR p_Effective = 'All'

            )) allRates
            ON allRates.RateID = r.RateID
        WHERE (p_contryID IS NULL OR r.CountryID = p_contryID)
        AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
        AND (p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%'))
        AND (r.CompanyID = p_companyid)
        AND r.CodeDeckId = v_codedeckid_
        AND  ((p_effectedRates = 1 AND Rate IS NOT NULL) OR  (p_effectedRates = 0))
          );
          select * from tmp_customerrate_;

    END IF;
 
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `fnVendorSippySheet`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `fnVendorSippySheet`(IN `p_AccountID` int, IN `p_Trunks` longtext)
BEGIN
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
            `Activation Date` varchar(10),
            `Expiration Date` varchar(10),
            AccountID int,
            TrunkID int
    );
    

    call vwVendorCurrentRates(); 
    
        SELECT NULL AS RateID,
               'A' AS `Action [A|D|U|S|SA]`,
               '' AS id,
               Concat(tblTrunk.Prefix , vendorRate.Code)  AS PREFIX,
               vendorRate.Description AS COUNTRY,
               5 AS Preference,
               CASE
                   WHEN vendorRate.Description LIKE '%gambia%'
                        OR vendorRate.Description LIKE '%mexico%' THEN 60
                   ELSE 1
               END AS `Interval 1`,
               CASE
                   WHEN vendorRate.Description LIKE '%mexico%' THEN 60
                   ELSE 1
               END AS `Interval N`,
               vendorRate.Rate AS `Price 1`,
               vendorRate.Rate AS `Price N`,
               10 AS `1xx Timeout`,
               60 AS `2xx Timeout`,
               0 AS Huntstop,
               CASE WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0) OR (blockCountry.VendorBlockingId IS NOT NULL AND FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0 ) 
                THEN 1 
                ELSE 0 
                END AS Forbidden,
               'NOW' AS `Activation Date`,
               '' AS `Expiration Date`,
               tblAccount.AccountID,
               tblTrunk.TrunkID
        FROM tmp_VendorSippySheet_ AS vendorRate
        INNER JOIN tblAccount ON vendorRate.AccountId = tblAccount.AccountID
        LEFT OUTER JOIN tblVendorBlocking ON vendorRate.RateID = tblVendorBlocking.RateId
        AND tblAccount.AccountID = tblVendorBlocking.AccountId
        AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
        LEFT OUTER JOIN tblVendorBlocking AS blockCountry ON vendorRate.CountryID = blockCountry.CountryId
        AND tblAccount.AccountID = blockCountry.AccountId
        AND vendorRate.TrunkID = blockCountry.TrunkID
        INNER JOIN tblTrunk ON tblTrunk.TrunkID = vendorRate.TrunkID
        WHERE vendorRate.AccountId = p_AccountID
          AND FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0
          AND vendorRate.Rate > 0;


     
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_BlockVendorCodes`;

CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_BlockVendorCodes`(IN `p_companyid` INT , IN `p_AccountId` VARCHAR(500), IN `p_trunkID` INT, IN `p_CountryIDs` VARCHAR(500), IN `p_Codes` VARCHAR(500), IN `p_Username` VARCHAR(100), IN `p_action` VARCHAR(100), IN `p_isCountry` INT, IN `p_isAllCountry` INT)
BEGIN
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

  	IF p_isAllCountry = 1
	THEN
		SELECT CONCAT(CountryID) INTO p_CountryIDs  FROM tblCountry;
	END IF;

	IF p_isCountry = 0 AND p_action = 1 -- block Code
   THEN

		INSERT INTO tblVendorBlocking (AccountId,RateId,TrunkID,BlockedBy)
			SELECT DISTINCT tblVendorRate.AccountID,tblRate.RateID,tblVendorRate.TrunkID,p_Username
			FROM tblVendorRate
			INNER JOIN tblRate ON tblRate.RateID = tblVendorRate.RateId
				AND tblRate.CompanyID = p_companyid
			LEFT JOIN tblVendorBlocking ON tblVendorBlocking.AccountId = tblVendorRate.AccountId
				AND tblVendorBlocking.RateId = tblRate.RateID
				AND tblVendorBlocking.TrunkID = p_trunkID

			WHERE tblVendorBlocking.VendorBlockingId IS NULL AND FIND_IN_SET(tblRate.Code,p_Codes) != 0 AND tblVendorRate.TrunkID = p_trunkID AND FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0 ;
	END IF;

	IF p_isCountry = 0 AND p_action = 0 -- unblock Code
	THEN
		DELETE  tblVendorBlocking
	  	FROM      tblVendorBlocking
	  	INNER JOIN tblVendorRate ON tblVendorRate.RateID = tblVendorBlocking.RateId
			AND tblVendorRate.TrunkID =  p_trunkID
	  	INNER JOIN tblRate ON  tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyid
		WHERE FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0 AND FIND_IN_SET(tblRate.Code,p_Codes) != 0 ;
	END IF;


	IF p_isCountry = 1 AND p_action = 1 -- block Country
	THEN
		INSERT INTO tblVendorBlocking (AccountId,CountryId,TrunkID,BlockedBy)
		SELECT DISTINCT tblVendorRate.AccountID,tblRate.CountryID,p_trunkID,p_Username
		FROM tblVendorRate
		INNER JOIN tblRate ON tblVendorRate.RateId = tblRate.RateID
			AND tblRate.CompanyID = p_companyid
			AND  FIND_IN_SET(tblRate.CountryID,p_CountryIDs) != 0
		LEFT JOIN tblVendorBlocking ON tblVendorBlocking.AccountId = tblVendorRate.AccountID
			AND tblRate.CountryID = tblVendorBlocking.CountryId
			AND tblVendorBlocking.TrunkID = p_trunkID
		WHERE tblVendorBlocking.VendorBlockingId IS NULL AND FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0
			AND tblVendorRate.TrunkID = p_trunkID;

	END IF;


	IF p_isCountry = 1 AND p_action = 0 -- unblock Count
	THEN
		DELETE FROM tblVendorBlocking
		WHERE tblVendorBlocking.TrunkID = p_trunkID AND FIND_IN_SET (tblVendorBlocking.AccountId,p_AccountId) !=0 AND FIND_IN_SET(tblVendorBlocking.CountryID,p_CountryIDs) != 0;
	END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END