CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardSales`(IN `p_CompanyID` INT, IN `p_OwnerID` VARCHAR(500), IN `p_Status` VARCHAR(50), IN `p_CurrencyID` INT, IN `p_Start` DATE, IN `p_End` DATE)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_Round_ int;
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
		
	 DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_Oppertunites_Sales_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_Oppertunites_Sales_(
		   `Status` int,
		   `Worth` decimal(18,8),
			`Year` int,
			`Month` int,		
			`AssignedUserText` varchar(100),
			`Opportunitescount`	int
	);
	  
			insert into tmp_Dashboard_Oppertunites_Sales_
	SELECT 		
			MAX(o.`Status`),
			SUM(o.Worth), 	   
			YEAR(o.ClosingDate) as Year,
			MONTH(o.ClosingDate) as Month,			   
			concat(tu.FirstName,' ',tu.LastName) as AssignedUserText,
			count(*) as Opportunitescount
	FROM tblOpportunity o
	INNER JOIN tblAccount ac on ac.AccountID = o.AccountID
			AND ac.`Status` = 1
	INNER join tblUser tu on o.UserID = tu.UserID
			and tu.`Status` = 1 					 
	WHERE
				o.CompanyID = p_CompanyID
			/*	AND o.OpportunityClosed=1*/
				AND (p_OwnerID = '' OR find_in_set(o.`UserID`,p_OwnerID))
				AND (p_Status = '' OR find_in_set(o.`Status`,p_Status))
				AND (ClosingDate between p_Start and p_End)
				AND (p_CurrencyID = '' OR ( p_CurrencyID != ''  and ac.CurrencyId = p_CurrencyID))	
	GROUP BY 
			YEAR(o.ClosingDate) 
			,MONTH(o.ClosingDate)
			,CONCAT(tu.FirstName,' ',tu.LastName);
			
	 SELECT	ROUND(td.Worth,v_Round_) as TotalWorth,
				v_CurrencyCode_,
				CONCAT(CONCAT(case when td.Month <10 then concat('0',td.Month) else td.Month End, '/'), td.Year) AS MonthName ,
				td.`Status` as `Status`,
				td.AssignedUserText,
				v_Round_	 as round_number,
				Opportunitescount		
	 FROM tmp_Dashboard_Oppertunites_Sales_  td;
END