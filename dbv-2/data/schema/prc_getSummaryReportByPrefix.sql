CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getSummaryReportByPrefix`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_Prefix` VARCHAR(50), IN `p_Country` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
     
    DECLARE v_BillingTime_ INT; 
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
     

	SELECT BillingTime INTO v_BillingTime_
	FROM Ratemanagement3.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	AND (p_AccountID = '' OR ga.AccountID = p_AccountID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1);
	
  CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,p_UserID,p_isAdmin,v_BillingTime_,''); 

    IF p_isExport = 0
    THEN
         
            SELECT
                MAX(AccountName) AS AccountName,
                area_prefix as AreaPrefix,
                MAX(c.Country) AS Country,
                MAX(r.Description) AS Description,
                count(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
                SUM(cost) AS TotalCharges
                
            FROM tmp_tblUsageDetails_ uh
            LEFT JOIN Ratemanagement3.tblRate r
                ON r.Code = uh.area_prefix 
            LEFT JOIN Ratemanagement3.tblCountry c
                ON r.CountryID = c.CountryID
            WHERE 
			(p_Prefix='' OR uh.area_prefix like REPLACE(p_Prefix, '*', '%'))
			AND (p_Country=0 OR r.CountryID=p_Country)
			GROUP BY uh.area_prefix,
                     uh.AccountID
                     ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN MAX(AccountName)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN MAX(AccountName)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixDESC') THEN area_prefix
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixASC') THEN area_prefix
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN MAX(c.Country)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN MAX(c.Country)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN MAX(r.Description)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN MAX(r.Description)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsDESC') THEN COUNT(UsageDetailID)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsASC') THEN COUNT(UsageDetailID)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalDurationDESC') THEN SUM(billed_duration)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalDurationASC') THEN SUM(billed_duration)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesDESC') THEN SUM(cost)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesASC') THEN SUM(cost)
                END ASC
        LIMIT p_RowspPage OFFSET v_OffSet_;


        SELECT
            COUNT(*) AS totalcount
        FROM (
            SELECT DISTINCT
                area_prefix as AreaPrefix,
                uh.AccountID
            FROM tmp_tblUsageDetails_ uh
            LEFT JOIN Ratemanagement3.tblRate r
                ON r.Code = uh.area_prefix 
            LEFT JOIN Ratemanagement3.tblCountry c
                ON r.CountryID = c.CountryID
            WHERE 
			(p_Prefix='' OR uh.area_prefix like REPLACE(p_Prefix, '*', '%'))
			AND (p_Country=0 OR r.CountryID=p_Country)
        ) AS TBL;


    END IF;
    IF p_isExport = 1
    THEN

        SELECT
            MAX(AccountName) AS AccountName,
            area_prefix as AreaPrefix,
            MAX(c.Country) AS Country,
            MAX(r.Description) AS Description,
            COUNT(UsageDetailID) AS NoOfCalls,
  			Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
            SUM(cost) AS TotalCharges
        FROM tmp_tblUsageDetails_ uh
		
		LEFT JOIN Ratemanagement3.tblRate r
                ON r.Code = uh.area_prefix 
        LEFT JOIN Ratemanagement3.tblCountry c
                ON r.CountryID = c.CountryID
        WHERE 
			(p_Prefix='' OR uh.area_prefix like REPLACE(p_Prefix, '*', '%'))
			AND (p_Country=0 OR r.CountryID=p_Country)
        GROUP BY uh.area_prefix,
                 uh.AccountID;

    END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END