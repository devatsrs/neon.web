CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getSummaryReportByCustomer`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
     
    DECLARE v_BillingTime_ INT; 
    DECLARE v_OffSet_ int;
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;
	
  CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,p_UserID,p_isAdmin,v_BillingTime_,'','','',0); 

    IF p_isExport = 0
    THEN
            SELECT
                MAX(uh.AccountName) as AccountName,
                count(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
                CONCAT(IFNULL(cc.Symbol,''),SUM(cost)) AS TotalCharges
            FROM tmp_tblUsageDetails_ uh
            INNER JOIN NeonRMDev.tblAccount a
         	ON a.AccountID = uh.AccountID
         LEFT JOIN NeonRMDev.tblCurrency cc
         	ON cc.CurrencyId = a.CurrencyId
            group by 
            uh.AccountID
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN MAX(uh.AccountName)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN MAX(uh.AccountName)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsDESC') THEN count(UsageDetailID)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsASC') THEN count(UsageDetailID)
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


        SELECT count(*) as totalcount from (SELECT DISTINCT AccountID  
        FROM tmp_tblUsageDetails_
        ) As TBL;
             

    END IF;
    IF p_isExport = 1
    THEN
        
            SELECT
                MAX(AccountName) as AccountName,
                count(UsageDetailID) AS NoOfCalls,
                Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,	
                SUM(cost) AS TotalCharges

            FROM tmp_tblUsageDetails_
            group by 
            AccountID;

    END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END