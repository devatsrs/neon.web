CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardForecast`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_Status` VARCHAR(50),
	IN `p_CurrencyID` INT,
	IN `p_Start` DATE,
	IN `p_End` DATE





)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from NeonRMDev.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
		
 DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_Oppertunites_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_Oppertunites_(
	   Status int,
 	   Worth int,
		ClosingDate date	
);
  
		insert into tmp_Dashboard_Oppertunites_
SELECT 		
		o.`Status`,
 	   o.Worth,
 	   o.ClosingDate
FROM tblOpportunity o
Inner JOIN tblAccount ac on ac.AccountID = o.AccountID
			AND ac.`Status` = 1
			AND (p_CurrencyID = '' OR ( p_CurrencyID != ''  and ac.CurrencyId = p_CurrencyID))
where
			 o.CompanyID = p_CompanyID
			AND (p_OwnerID = '' OR find_in_set(o.`UserID`,p_OwnerID))
			AND (p_Status = '' OR find_in_set(o.`Status`,p_Status))
			AND (ClosingDate between p_Start and p_End);
        
 SELECT	Worth as TotalWorth,
			v_CurrencyCode_,
			ClosingDate,
			`Status` as StatusSum	
         FROM tmp_Dashboard_Oppertunites_ order by `ClosingDate` asc;
END