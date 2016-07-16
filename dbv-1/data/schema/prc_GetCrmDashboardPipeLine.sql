CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardPipeLine`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_Status` VARCHAR(50),
	IN `p_CurrencyID` INT


)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from NeonRMDev.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
		
 DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_Oppertunites_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_Oppertunites_(
	   Status int,
 	   Worth int	
);
  
		insert into tmp_Dashboard_Oppertunites_
SELECT 		
		o.`Status`,
 	   o.Worth
FROM tblOpportunity o
Inner JOIN tblAccount ac on ac.AccountID = o.AccountID
			AND ac.`Status` = 1
			AND (p_CurrencyID = '' OR ( p_CurrencyID != ''  and ac.CurrencyId = p_CurrencyID))
where
			 o.CompanyID = p_CompanyID
			AND (p_OwnerID = '' OR find_in_set(o.`UserID`,p_OwnerID))
			AND (p_Status  = '' OR find_in_set(o.`Status`,p_Status));

 SELECT		Status,	           
			sum(Worth) as TotalWorth,
			v_CurrencyCode_,
			count(*) as TotalOpportunites	
        FROM tmp_Dashboard_Oppertunites_ 
        group by `Status` asc;
END