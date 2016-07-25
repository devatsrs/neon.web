CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCrmDashboardForecast`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_Status` INT,
	IN `p_CurrencyID` INT,
	IN `p_Start` DATE,
	IN `p_End` DATE
)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_Round_ int;
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT cs.Value INTO v_Round_ from tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
		
	 DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_Oppertunites_Forecast_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_Oppertunites_Forecast_(
		   `Status` int,
		   `Worth` decimal(18,8),
			`Year` int,
			`Month` int,
		-- 	MonthName varchar(50),
			`AssignedUserText` varchar(100),
			`Opportunitescount`	int
	);
	  
		insert into tmp_Dashboard_Oppertunites_Forecast_
		SELECT 		
			   MAX(o.`Status`),
			   SUM(o.Worth),
			   YEAR(IFNULL(o.ExpectedClosing,now())) as Year,
			   MONTH(IFNULL(o.ExpectedClosing,now())) as Month,
				-- MONTHNAME(IFNULL(o.ExpectedClosing,now())) as  MonthName, 	
			   CONCAT(tu.FirstName,' ',tu.LastName) as AssignedUserText,
			   COUNT(*) as Opportunitescount
		FROM tblOpportunity o
		INNER JOIN tblAccount ac on ac.AccountID = o.AccountID
					AND ac.`Status` = 1
		INNER join tblUser tu on o.UserID = tu.UserID
						AND tu.`Status` = 1						
		WHERE
					 o.CompanyID = p_CompanyID
					AND o.OpportunityClosed=0
					AND (p_OwnerID = '' OR find_in_set(o.`UserID`,p_OwnerID))
					AND (p_Status = '' OR o.`Status` = p_Status)		
					AND (IFNULL(o.ExpectedClosing,now()) between p_Start and p_End)
					AND (p_CurrencyID = '' OR ( p_CurrencyID != ''  and ac.CurrencyId = p_CurrencyID))
		GROUP BY 
					YEAR(IFNULL(o.ExpectedClosing,now()))
					,MONTH(IFNULL(o.ExpectedClosing,now()))			
					,CONCAT(tu.FirstName,' ',tu.LastName);
			
		SELECT	ROUND(td.Worth,v_Round_) as TotalWorth,
				v_CurrencyCode_ as CurrencyCode,			
				CONCAT(CONCAT(case when td.Month <10 then concat('0',td.Month) else td.Month End, '/'), td.Year) AS MonthName ,
				td.`Status` as `Status`,
				td.AssignedUserText,
				v_Round_ as round_number,
				Opportunitescount	
	    FROM tmp_Dashboard_Oppertunites_Forecast_ td;
END