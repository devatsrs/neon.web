CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_salesDashboard`(IN `p_CompanyID` INT, IN `p_gatewayid` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_PrevStartDate` DATETIME, IN `p_PrevEndDate` DATETIME, IN `p_Executive` INT 
)
BEGIN
   DECLARE v_Round_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SELECT cs.Value INTO v_Round_ from Ratemanagement3.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount';
	SELECT
        ROUND(ifNull(CAST(SUM(TotalCharges) as DECIMAL(16,5)),0),v_Round_) AS TotalCharges
    FROM tblUsageDaily ud
    LEFT JOIN Ratemanagement3.tblAccount a
        ON ud.AccountID = a.AccountID 
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID;
    
    



    SELECT
        COUNT( DISTINCT ud.AccountID ) AS totalactive
    FROM tblUsageDaily ud
    LEFT JOIN Ratemanagement3.tblAccount a
        ON ud.AccountID = a.AccountID
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID;
    


    SELECT
        COUNT( DISTINCT ga.GatewayAccountID) AS totalinactive
    FROM tblGatewayAccount ga
    left join tblUsageDaily ud on ud.AccountID = ga.AccountID AND  ud.DailyDate >= p_StartDate
        AND ud.DailyDate <= p_EndDate 
        and ud.CompanyID = ga.CompanyID
         LEFT JOIN Ratemanagement3.tblAccount a
        ON ga.AccountID = a.AccountID
    where ud.AccountID is null 
    and ga.AccountID is not null
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ga.CompanyID = p_CompanyID
    AND AccountDetailInfo like '%"blocked":false%'
    AND ga.AccountID is not null;
    

    SELECT
        ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5)),v_Round_) AS TotalCharges,
        ud.DailyDate AS sales_date
    FROM tblUsageDaily ud
    LEFT JOIN Ratemanagement3.tblAccount a
        ON ud.AccountID = a.AccountID 
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID
    GROUP BY ud.DailyDate
    ORDER BY ud.DailyDate;

    SELECT
        ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),v_Round_) AS TotalCharges,
        max(a.AccountName) as AccountName
    FROM tblUsageDaily ud
    LEFT JOIN Ratemanagement3.tblAccount a
        ON ud.AccountID = a.AccountID  
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID

    GROUP BY ud.AccountID
    order by SUM(TotalCharges) desc;



    SELECT  
        Max(ifnull(c.Country,'OTHER')) as Country,
        ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5)),v_Round_) AS TotalCharges
    FROM tblUsageDaily ud
    LEFT JOIN Ratemanagement3.tblAccount a
        ON ud.AccountID = a.AccountID  
    LEFT JOIN Ratemanagement3.tblRate r
        ON r.Code = ud.AreaPrefix AND r.CodeDeckId = 1
    LEFT JOIN Ratemanagement3.tblCountry c
        ON r.CountryID = c.CountryID
    WHERE ud.DailyDate >= p_StartDate
    AND ud.DailyDate <= p_EndDate
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID
    GROUP BY r.CountryID
    ORDER BY SUM(TotalCharges) DESC limit 5;

    SELECT
       DISTINCT ga.GatewayAccountID,
            ga.AccountName,
            ga.AccountID
    FROM tblGatewayAccount ga
    left join tblUsageDaily ud on ud.AccountID = ga.AccountID AND  ud.DailyDate >= p_StartDate
        AND ud.DailyDate <= p_EndDate 
        and ud.CompanyID = ga.CompanyID
         LEFT JOIN Ratemanagement3.tblAccount a
        ON ga.AccountID = a.AccountID
    where ud.AccountID is null 
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    and ga.AccountID is not null
    AND ga.CompanyID = p_CompanyID
    AND AccountDetailInfo like '%"blocked":false%'
    AND ga.AccountID is not null
    order by AccountName;

    IF p_Executive = 1
    THEN
        SELECT
            ROUND(ifNull(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),0),v_Round_) AS TotalCharges,a.Owner,(concat(max(u.FirstName),' ',max(u.LastName))) as FullName
        FROM tblUsageDaily ud
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ud.AccountID = a.AccountID 
        LEFT JOIN Ratemanagement3.tblUser u
            ON u.UserID = a.Owner
        WHERE ud.DailyDate >= p_StartDate
        AND ud.DailyDate <= p_EndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND a.AccountID is not null
        AND ud.CompanyID = p_CompanyID
        group by a.Owner;

        IF p_PrevStartDate != ''
        THEN
        SELECT
            ROUND(IFNull(CAST(SUM(TotalCharges) as DECIMAL(16,5)),0),v_Round_) AS TotalCharges,a.Owner,(concat(max(u.FirstName),' ',max(u.LastName))) as FullName
        FROM tblUsageDaily ud
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ud.AccountID = a.AccountID 
        LEFT JOIN Ratemanagement3.tblUser u
            ON u.UserID = a.Owner
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID
        group by a.Owner;

        else 
            select 1 as FullName,0 as TotalCharges;
        end if;

    else
        select 1 as FullName,0 as TotalCharges;
        select 1 as FullName,0 as TotalCharges;
    end if;

    IF p_PrevStartDate != ''
    THEN
        SELECT
            ROUND(IFNull(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),0),v_Round_)   AS PrevTotalCharges
        FROM tblUsageDaily ud
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ud.AccountID = a.AccountID  
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID;
        


        SELECT
            COUNT( DISTINCT ud.AccountID ) AS prevtotalactive
        FROM tblUsageDaily ud
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ud.AccountID = a.AccountID  
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID;
        



        SELECT
            COUNT( DISTINCT ga.GatewayAccountID ) AS prevtotalinactive
        FROM tblGatewayAccount ga
        left join tblUsageDaily ud on ud.AccountID = ga.AccountID AND
    			ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate 
            and ud.CompanyID = ga.CompanyID
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ga.AccountID = a.AccountID  
    where ud.AccountID is null 
    and ga.AccountID is not null
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    AND ud.CompanyID = p_CompanyID
    AND AccountDetailInfo like '%"blocked":false%'
    AND ga.AccountID is not null;

        SELECT
            ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),v_Round_) AS prevTotalCharges,
            ud.DailyDate
        FROM tblUsageDaily ud
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ud.AccountID = a.AccountID  
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID
        GROUP BY ud.DailyDate
        ORDER BY ud.DailyDate;

        SELECT
            ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),v_Round_) AS prevTotalCharges,
            max(a.AccountName) as AccountName
        FROM tblUsageDaily ud
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ud.AccountID = a.AccountID  
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID
        GROUP BY ud.AccountID
        order by SUM(TotalCharges) desc;



        SELECT
            Max(ifnull(c.Country,'OTHER')) as Country,
            ROUND(CAST(SUM(TotalCharges) as DECIMAL(16,5) ),v_Round_) AS prevTotalCharges
        FROM tblUsageDaily ud
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ud.AccountID = a.AccountID  
        LEFT JOIN Ratemanagement3.tblRate r
            ON r.Code = ud.AreaPrefix AND r.CodeDeckId = 1
        LEFT JOIN Ratemanagement3.tblCountry c
            ON r.CountryID = c.CountryID
        WHERE ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate
        AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
        AND ud.CompanyID = p_CompanyID
        GROUP BY r.CountryID
        ORDER BY SUM(TotalCharges) DESC limit 5;
        

         SELECT
            DISTINCT ga.GatewayAccountID,
            ga.AccountName,
            ga.AccountID
        FROM tblGatewayAccount ga
        left join tblUsageDaily ud on ud.AccountID = ga.AccountID  AND
    ud.DailyDate >= p_PrevStartDate
        AND ud.DailyDate <= p_PrevEndDate 
            and ud.CompanyID = ga.CompanyID
        LEFT JOIN Ratemanagement3.tblAccount a
            ON ga.AccountID = a.AccountID  
    where ud.AccountID is null 
    AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
    and ga.AccountID is not null
    AND ga.CompanyID = p_CompanyID
    AND AccountDetailInfo like '%"blocked":false%'
    AND ga.AccountID is not null
    order by AccountName;

    END IF;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END