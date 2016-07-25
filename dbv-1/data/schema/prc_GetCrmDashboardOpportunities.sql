CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardOpportunities`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_CurrencyID` INT


)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from NeonRMDev.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
		
 DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_Oppertunites_Grid_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_Oppertunites_Grid_(	 
		BoardID int,
		OpportunityName varchar(50),
		AssignedText varchar (100),
 	   Worth int,
 	   ClosingDate date
);
  
	insert into tmp_Dashboard_Oppertunites_Grid_
SELECT 		
		o.BoardID,
		o.OpportunityName,
		concat(o.Title,' ', o.FirstName,' ',o.LastName) as AssignedUserText,
 	   o.Worth,
	   o.ClosingDate as ClosingDate
FROM tblOpportunity o
Inner JOIN tblAccount ac on ac.AccountID = o.AccountID		
where
			ac.`Status` = 1
			and  o.CompanyID = p_CompanyID
			AND o.OpportunityClosed=0
			AND (p_CurrencyID = '' OR ( p_CurrencyID != ''  and ac.CurrencyId = p_CurrencyID))		
			AND (p_OwnerID = '' OR find_in_set(o.`UserID`,p_OwnerID));

 SELECT	      
			v_CurrencyCode_,
			dod.*	
        FROM tmp_Dashboard_Oppertunites_Grid_ dod
        order by BoardID desc;
END