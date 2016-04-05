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
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        RateTableRateID INT,
        TrunkID INT,
        INDEX tmp_RateTableRate__RateID (`RateID`),
        INDEX tmp_RateTableRate__TrunkID (`TrunkID`),
        INDEX tmp_RateTableRate__EffectiveDate (`EffectiveDate`)

    );

     

    INSERT INTO tmp_CustomerRates_

            SELECT
                tblCustomerRate.RateID,
                tblCustomerRate.Interval1,
                tblCustomerRate.IntervalN,
                
                tblCustomerRate.Rate,
                tblCustomerRate.ConnectionFee,
                tblCustomerRate.EffectiveDate,
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
            AND (
                   (p_Effective = 'Now' AND EffectiveDate <= NOW() )
                OR (p_Effective = 'Future' AND EffectiveDate > NOW())
                OR  p_Effective = 'All'
               )
            ORDER BY
                tblCustomerRate.TrunkId, tblCustomerRate.CustomerId,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;
         
        
         
        
                
            
        SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
        SELECT MAX(EffectiveDate) as EffectiveDate
        FROM 
        tblRateTableRate
        WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW() 
        ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
        )tbl;

    INSERT INTO tmp_RateTableRate_
            SELECT
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
                   (p_Effective = 'Now' AND EffectiveDate <= NOW() 
                     OR (v_NewA2ZAssign_ = 1 AND EffectiveDate >= NOW() )
                    )
                OR (p_Effective = 'Future' AND EffectiveDate > NOW())
                OR  p_Effective = 'All'
               )
                ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC;
            
        IF p_Effective = 'Now'
        THEN
            CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);         
            DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
            AND n1.TrunkId = n2.TrunkId
            AND  n1.RateID = n2.RateID;
          
          CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);           
        DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
          AND n1.TrunkId = n2.TrunkId
          AND  n1.RateID = n2.RateID;
        END IF;

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
    
		    
		  DROP TEMPORARY TABLE IF EXISTS tmp_customerrate_;

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
END