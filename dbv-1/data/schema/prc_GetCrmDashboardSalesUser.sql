CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardSalesUser`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_CurrencyID` INT,
	IN `p_ListType` VARCHAR(50),
	IN `p_Start` DATETIME,
	IN `p_End` VARCHAR(50),
	IN `p_WeekOrMonth` INT,
	IN `p_Year` INT
)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_Round_ int;
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
		
 DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_user_invoices_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_user_invoices_( 	   
	   AssignedUserText varchar(100),
	   Revenue decimal(18,8)	
);  

INSERT INTO tmp_Dashboard_user_invoices_  
SELECT 		
	ac.AccountName  AS `AssignedUserText`,
	SUM(`inv`.`GrandTotal`) AS `Revenue`

FROM 
	NeonBillingDev.tblInvoice inv
INNER JOIN 
	tblAccount ac on ac.AccountID = inv.AccountID 
INNER join
	tblUser tu on tu.UserID = ac.Owner			
WHERE
			ac.`Status` = 1
			AND tu.`Status` = 1 		
			AND (p_CurrencyID = '' OR ( p_CurrencyID !=''  and ac.CurrencyId = p_CurrencyID))
		   AND ac.CompanyID = p_CompanyID
			AND (p_OwnerID = '' OR   find_in_set(tu.UserID,p_OwnerID))
			AND `inv`.`InvoiceType` = 1
			AND (inv.IssueDate between p_Start and p_End)
			AND (( p_ListType = 'Weekly' and WEEK(inv.IssueDate) = p_WeekOrMonth and year(inv.IssueDate) = p_Year) or( p_ListType = 'Monthly' and month(inv.IssueDate) = p_WeekOrMonth and year(inv.IssueDate) = p_Year))
GROUP BY 		
			ac.AccountName;
					
  SELECT	
  	  	  td.AssignedUserText,
		  ROUND(td.Revenue,v_Round_) as Revenue,
		  v_CurrencyCode_ as CurrencyCode,
		  v_Round_	 AS round_number
	FROM 
			tmp_Dashboard_user_invoices_  td;
END