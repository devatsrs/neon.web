CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardPipeLine`(IN `p_CompanyID` INT, IN `p_OwnerID` VARCHAR(500), IN `p_CurrencyID` INT)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_Round_ int;
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
		
 DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_Oppertunites_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_Oppertunites_(
 	   Worth decimal(18,8),
	   AssignedUserText varchar(100)
		
);  

insert into tmp_Dashboard_Oppertunites_
SELECT 		
 	   o.Worth,
	   concat(tu.FirstName,' ',tu.LastName) as AssignedUserText
FROM tblOpportunity o
Inner JOIN tblAccount ac on ac.AccountID = o.AccountID
inner join tblUser tu on o.UserID = tu.UserID
where
			ac.`Status` = 1
			AND (p_CurrencyID = '' OR ( p_CurrencyID !=''  and ac.CurrencyId = p_CurrencyID))
		   AND o.CompanyID = p_CompanyID
		   AND o.OpportunityClosed =0
		   and o.`Status` = 1
			AND (p_OwnerID = '' OR find_in_set(o.`UserID`,p_OwnerID));

 SELECT			           
			ROUND(sum(Worth),v_Round_) as TotalWorth,
			v_CurrencyCode_,
			v_Round_ as RoundVal,
			AssignedUserText,			
			count(*) as TotalOpportunites	
         FROM tmp_Dashboard_Oppertunites_ 
         group by `AssignedUserText` asc;
END