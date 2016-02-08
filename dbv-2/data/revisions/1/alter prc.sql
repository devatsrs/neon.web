

DROP PROCEDURE IF EXISTS `prc_getDashboardinvoiceExpense`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardinvoiceExpense`(IN `p_CompanyID` INT, IN `p_CurrencyID` INT, IN `p_AccountID` INT)
BEGIN
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    
     CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalDue_(
			`Year` int,
			`Month` int,
			MonthName varchar(50),
			CurrencyID int,
			TotalAmount float
		);
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalReceived_(
			`Year` int,
			`Month` int,
			MonthName varchar(50),
			CurrencyID int,
			TotalAmount float
		);


	 INSERT INTO tmp_MonthlyTotalDue_
    SELECT YEAR(created_at) as Year, MONTH(created_at) as Month,MONTHNAME(created_at) as  MonthName, SUM(IFNULL(GrandTotal,0)) as TotalAmount,CurrencyID
    from tblInvoice
    where 
        CompanyID = p_CompanyID
        and CurrencyID = p_CurrencyID
        and created_at >= DATE_ADD(NOW(),INTERVAL -6 MONTH)
        and InvoiceType = 1 -- Invoice Out
        and InvoiceStatus != 'cancel'
        and (p_AccountID = 0 or AccountID = p_AccountID)

    GROUP BY YEAR(created_at), MONTH(created_at),CurrencyID
    ORDER BY Year, Month;

	 INSERT INTO tmp_MonthlyTotalReceived_
    SELECT YEAR(inv.created_at) as Year, MONTH(inv.created_at) as Month,MONTHNAME(inv.created_at) as  MonthName, SUM(IFNULL(p.Amount,0)) as TotalAmount,inv.CurrencyID
    from tblInvoice inv
    inner join Ratemanagement3.tblAccount ac on ac.AccountID = inv.AccountID
    left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
    left join tblPayment p on REPLACE(p.InvoiceNo,'-','') = CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID
    where 
        inv.CompanyID = p_CompanyID
        and inv.CurrencyID = p_CurrencyID
        and inv.created_at >= DATE_ADD(NOW(),INTERVAL -6 MONTH)
        and inv.InvoiceType = 1 
        and (p_AccountID = 0 or inv.AccountID = p_AccountID)
        and (
            inv.InvoiceStatus = 'paid' -- Paid Invoice
            OR inv.InvoiceStatus = 'partially_paid' 
            )
     
    GROUP BY YEAR(inv.created_at), MONTH(inv.created_at),inv.CurrencyID
    ORDER BY Year, Month;
 

    
    SELECT td.MonthName,td.Year,IFNULL(td.TotalAmount,0) TotalInvoice ,  IFNULL(tr.TotalAmount,0) PaymentReceived, IFNULL((IFNULL(td.TotalAmount,0) - IFNULL(tr.TotalAmount,0)),0) TotalOutstanding , td.CurrencyID CurrencyID from 
        tmp_MonthlyTotalDue_ td
        left join tmp_MonthlyTotalReceived_ tr on td.Month = tr.Month and td.Year = tr.Year and tr.CurrencyID = td.CurrencyID;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getInvoiceUsage`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getInvoiceUsage`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_ShowZeroCall` INT)
BEGIN
    
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	DECLARE v_CDRType_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	

	
	SELECT BillingTime INTO v_BillingTime_
	FROM Ratemanagement3.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_); 

	Select CDRType  INTO v_CDRType_ from  Ratemanagement3.tblAccount where AccountID = p_AccountID;


            
        
            
    IF( v_CDRType_ = 2) -- Summery
    Then

        SELECT
            area_prefix AS AreaPrefix,
            max(Trunk) as Trunk,
            (SELECT 
                Country
            FROM Ratemanagement3.tblRate r
            INNER JOIN Ratemanagement3.tblCustomerRate cr
                ON cr.RateID = r.RateID
            INNER JOIN Ratemanagement3.tblCustomerTrunk ct
                ON cr.CustomerID = ct.AccountID
                AND ct.TrunkID = cr.TrunkID
            INNER JOIN Ratemanagement3.tblTrunk t
                ON ct.TrunkID = t.TrunkID
            INNER JOIN Ratemanagement3.tblCountry c
                ON c.CountryID = r.CountryID
            WHERE t.Trunk = MAX(ud.trunk)
            AND ct.AccountID = ud.AccountID
            AND r.Code = ud.area_prefix limit 1)
            AS Country,
            (SELECT Description
            FROM Ratemanagement3.tblRate r
            INNER JOIN Ratemanagement3.tblCustomerRate cr
                ON cr.RateID = r.RateID
            INNER JOIN Ratemanagement3.tblCustomerTrunk ct
                ON cr.CustomerID = ct.AccountID
                AND ct.TrunkID = cr.TrunkID
            INNER JOIN Ratemanagement3.tblTrunk t
                ON ct.TrunkID = t.TrunkID
            WHERE t.Trunk = MAX(ud.trunk)
            AND ct.AccountID = ud.AccountID
            AND r.Code = ud.area_prefix limit 1 )
            AS Description,
            COUNT(UsageDetailID) AS NoOfCalls,
            Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
            SUM(cost) AS TotalCharges

        FROM tmp_tblUsageDetails_ ud
        GROUP BY ud.area_prefix,
                 ud.AccountID;

         
    ELSE
        
        
            select
            trunk,
            area_prefix,
            cli,
            cld,
            connect_time,
            disconnect_time,
            billed_duration,
            cost
            FROM tmp_tblUsageDetails_ ud
            WHERE
             ((p_ShowZeroCall =0 and ud.cost >0 )or (p_ShowZeroCall =1 and ud.cost >= 0));
            
    END IF;    
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getSOA`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getSOA`(IN `p_CompanyID` INT, IN `p_accountID` INT, IN `p_StartDate` datetime, IN `p_EndDate` datetime, IN `p_isExport` INT )
BEGIN
	
		
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_AOS_;
    CREATE TEMPORARY TABLE tmp_AOS_ (
        AccountName VARCHAR(50),
        InvoiceNo VARCHAR(50),
        PaymentType VARCHAR(15),
        PaymentDate LONGTEXT,
        PaymentMethod VARCHAR(20),
        Amount NUMERIC(18, 8),
        Currency VARCHAR(15),
        PeriodCover VARCHAR(30),
		  StartDate datetime,
  		  EndDate datetime,
        Type VARCHAR(10),
        InvoiceAmount NUMERIC(18, 8),
        CreatedDate VARCHAR(15),
        PaymentsID INT
    );
-- Invoice & Payment with Invoice number 
    INSERT INTO tmp_AOS_
        SELECT
						DISTINCT 
            tblAccount.AccountName,
            tblInvoice.InvoiceNumber,
            tblPayment.PaymentType,
            CASE
            WHEN p_isExport = 1
            THEN
                DATE_FORMAT(tblPayment.PaymentDate,'%d/%m/%Y')
            ELSE
                DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            END AS dates,
            tblPayment.PaymentMethod,
            tblPayment.Amount,
            tblPayment.Currency,
            CASE
            WHEN p.Name IS NOT NULL
            THEN
                p.Name
            ELSE
                CASE
                WHEN p_isExport = 1
                THEN
                    Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d/%m/%Y') ,' - ' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d/%m/%Y'))
                ELSE
	                 Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d-%m-%Y') , ' => ' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d-%m-%Y'))
                END
            END AS dates,
			tblInvoiceDetail.StartDate,
			tblInvoiceDetail.EndDate,
            tblInvoice.InvoiceType,
            tblInvoice.GrandTotal,
            tblInvoice.created_at,
            tblPayment.PaymentID
        FROM tblInvoice
        INNER JOIN tblInvoiceDetail
            ON tblInvoice.InvoiceID = tblInvoiceDetail.InvoiceID
        INNER JOIN Ratemanagement3.tblAccount
            ON tblInvoice.AccountID = tblAccount.AccountID
        LEFT JOIN tblPayment
            ON (tblInvoice.InvoiceNumber = tblPayment.InvoiceNo
            AND tblPayment.Status = 'Approved')
        LEFT JOIN tblProduct p
            ON p.ProductID = tblInvoiceDetail.ProductID
            AND p.CompanyId = p_CompanyID
        WHERE tblInvoice.CompanyID = p_CompanyID
        AND (p_accountID = 0
        OR tblInvoice.AccountID = p_accountID)
		AND 
		(
			(p_StartDate = '0000-00-00 00:00:00' OR  p_EndDate = '0000-00-00 00:00:00') OR ((p_StartDate != '0000-00-00 00:00:00' AND p_EndDate != '0000-00-00 00:00:00') AND str_to_date(tblInvoice.IssueDate,'%Y-%m-%d') between  str_to_date(p_StartDate,'%Y-%m-%d') and str_to_date(p_EndDate,'%Y-%m-%d') )
		)

			-- Payment without Invoice number 
        UNION ALL
        SELECT
						DISTINCT
            tblAccount.AccountName,
            tblPayment.InvoiceNo,
            tblPayment.PaymentType,
            CASE
            WHEN p_isExport = 1
            THEN
               DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            ELSE
               DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            END AS dates,
            tblPayment.PaymentMethod,
            tblPayment.Amount,
            tblPayment.Currency,
            '' AS dates,-- tblPayment.No... *** SQLINES FOR EVALUATION USE ONLY *** 
			tblPayment.PaymentDate,
			tblPayment.PaymentDate,
            CASE
			WHEN tblPayment.PaymentType = 'Payment In'
            THEN
                1
            ELSE
                2
            END AS InvoiceType,
            0 AS GrandTotal,
            tblPayment.created_at,
            tblPayment.PaymentID
        FROM tblPayment
        INNER JOIN Ratemanagement3.tblAccount
            ON tblPayment.AccountID = tblAccount.AccountID
        WHERE tblPayment.Status = 'Approved'
        AND tblPayment.AccountID = p_accountID
        AND tblPayment.CompanyID = p_CompanyID
        AND tblPayment.InvoiceNo = ''
		AND 
		(
			(p_StartDate = '0000-00-00 00:00:00' OR p_EndDate = '0000-00-00 00:00:00') OR ((p_StartDate != '0000-00-00 00:00:00' AND p_EndDate != '0000-00-00 00:00:00') AND str_to_date(tblPayment.PaymentDate,'%Y-%m-%d') between  str_to_date(p_StartDate,'%Y-%m-%d') and str_to_date(p_EndDate,'%Y-%m-%d') )
		);


    SELECT
				 
        IFNULL(InvoiceNo,'') as InvoiceNo,
        PeriodCover,
        InvoiceAmount,
        ' ' AS spacer,
        PaymentDate,
        Amount AS payment,
        NULL AS ballence,
        CASE
        WHEN p_isExport = 0
        THEN
            PaymentsID
        END PaymentID 
    FROM tmp_AOS_
    WHERE Type = 1
    ORDER BY StartDate desc;

    SELECT
				 
        IFNULL(InvoiceNo,'') as InvoiceNo,
        PeriodCover,
        InvoiceAmount,
        ' ' AS spacer,
        PaymentDate,
        Amount AS payment,
        NULL AS ballence,
        CASE
        WHEN p_isExport = 0
        THEN
            PaymentsID
        END PaymentID
    FROM tmp_AOS_
    WHERE Type = 2
    ORDER BY StartDate desc;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getSummaryReportByCountry`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getSummaryReportByCountry`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_Country` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
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
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,p_UserID,p_isAdmin,v_BillingTime_); 
  
    IF p_isExport = 0
    THEN
            SELECT
                MAX(AccountName) AS AccountName,
                MAX(c.Country) AS Country,
                COUNT(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
                SUM(cost) AS TotalCharges
            FROM tmp_tblUsageDetails_ uh
            LEFT JOIN Ratemanagement3.tblRate r
                ON r.Code = uh.area_prefix 
			LEFT JOIN Ratemanagement3.tblCountry c
                ON r.CountryID = c.CountryID
            WHERE (p_Country = '' OR c.CountryID = p_Country)
            group by 
            c.Country,uh.AccountID
				ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN MAX(AccountName)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN MAX(AccountName)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN MAX(c.Country)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN MAX(c.Country)
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


        SELECT count(*) as totalcount from (SELECT DISTINCT uh.AccountID ,c.Country 
        FROM tmp_tblUsageDetails_ uh
            LEFT JOIN Ratemanagement3.tblRate r
                ON r.Code = uh.area_prefix 
			LEFT JOIN Ratemanagement3.tblCountry c
                ON r.CountryID = c.CountryID
            WHERE (p_Country = '' OR c.CountryID = p_Country)
        ) As TBL;

    END IF;
    IF p_isExport = 1
    THEN
        
            SELECT
                MAX(AccountName) AS AccountName,
                MAX(c.Country) AS Country,
                COUNT(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
                SUM(cost) AS TotalCharges
            FROM tmp_tblUsageDetails_ uh
            LEFT JOIN Ratemanagement3.tblRate r
                ON r.Code = uh.area_prefix 
			LEFT JOIN Ratemanagement3.tblCountry c
                ON r.CountryID = c.CountryID
            WHERE (p_Country = '' OR c.CountryID = p_Country)
            group by 
            c.Country,uh.AccountID;
            

    END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getSummaryReportByCustomer`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getSummaryReportByCustomer`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
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
	
  CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,p_UserID,p_isAdmin,v_BillingTime_); 

    IF p_isExport = 0
    THEN
            SELECT
                MAX(AccountName) as AccountName,
                count(UsageDetailID) AS NoOfCalls,
				Concat( Format(SUM(duration ) / 60,0), ':' , SUM(duration ) % 60) AS Duration,
		    	Concat( Format(SUM(billed_duration ) / 60,0),':' , SUM(billed_duration ) % 60) AS BillDuration,
                SUM(cost) AS TotalCharges
            FROM tmp_tblUsageDetails_ uh
            group by 
            uh.AccountID
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN MAX(AccountName)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN MAX(AccountName)
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
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getSummaryReportByPrefix`;
DELIMITER //
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
	
  CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,p_UserID,p_isAdmin,v_BillingTime_); 

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

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getAccountInvoiceTotal`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountInvoiceTotal`(IN `p_AccountID` INT, IN `p_CompanyID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_checkDuplicate` INT, IN `p_InvoiceDetailID` INT)
BEGIN 
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	
	SELECT BillingTime INTO v_BillingTime_
	FROM Ratemanagement3.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_); 
	
	IF p_checkDuplicate = 1 THEN
	
		SELECT COUNT(inv.InvoiceID) INTO v_InvoiceCount_
		FROM tblInvoice inv
		LEFT JOIN tblInvoiceDetail invd ON invd.InvoiceID = inv.InvoiceID
		WHERE inv.CompanyID = p_CompanyID AND (p_InvoiceDetailID = 0 OR (p_InvoiceDetailID > 0 AND invd.InvoiceDetailID != p_InvoiceDetailID)) AND AccountID = p_AccountID AND 
		(
		 (p_StartDate BETWEEN invd.StartDate AND invd.EndDate) OR
		 (p_EndDate BETWEEN invd.StartDate AND invd.EndDate) OR
		 (invd.StartDate BETWEEN p_StartDate AND p_EndDate) 
		) AND inv.InvoiceStatus != 'cancel'; 
		
		IF v_InvoiceCount_ = 0 THEN
		
			SELECT IFNULL(CAST(SUM(cost) AS DECIMAL(18,8)), 0) AS TotalCharges
			FROM tmp_tblUsageDetails_ ud; 
		ELSE
			SELECT 0 AS TotalCharges; 
		END IF; 
	
	ELSE
		SELECT IFNULL(CAST(SUM(cost) AS DECIMAL(18,8)), 0) AS TotalCharges
		FROM tmp_tblUsageDetails_ ud; 
	END IF; 
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_InsertTempReRateCDR`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_InsertTempReRateCDR`(IN `p_company_id` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT, IN `p_ProcessID` VARCHAR(50), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	 DECLARE v_BillingTime_ INT; 
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SELECT BillingTime INTO v_BillingTime_
	 FROM Ratemanagement3.tblCompanyGateway cg
	 INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	 WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
	 LIMIT 1;

    SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
    
    CALL fnUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_); 
    
    set @stm1 = CONCAT('

    INSERT INTO RMCDR3.`' , p_tbltempusagedetail_name , '` (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,connect_time,disconnect_time,billed_duration,trunk,area_prefix,cli,cld,cost,ProcessID,duration)

    SELECT "',p_CompanyID,'","',p_CompanyGatewayID,'",AccountName ,AccountID,connect_time,disconnect_time,billed_duration,trunk,area_prefix,cli,cld,cost,"',p_ProcessID,'",duration
    FROM tmp_tblUsageDetails_');
    
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;