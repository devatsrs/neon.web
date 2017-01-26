CREATE DEFINER=`neon-user-umer`@`122.129.78.153` PROCEDURE `prc_GetCrmDashboardSalesManager`(
	IN `p_CompanyID` INT,
	IN `p_OwnerID` VARCHAR(500),
	IN `p_CurrencyID` INT,
	IN `p_ListType` VARCHAR(50),
	IN `p_Start` DATETIME,
	IN `p_End` DATETIME
)
BEGIN
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_Round_ int;
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
		
 DROP TEMPORARY TABLE IF EXISTS tmp_Dashboard_invoices_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dashboard_invoices_( 	   
	   AssignedUserText varchar(100),
	   AssignedUserID int,
	   Revenue decimal(18,8),
		`Year` int,
		`Month` int,
		`Week` int		
);  

insert into tmp_Dashboard_invoices_  
SELECT 		
	CONCAT(tu.FirstName,' ',tu.LastName) AS `AssignedUserText`,
	 tu.UserID AS  AssignedUserID,
	sum(`inv`.`GrandTotal`) AS `Revenue`,
	YEAR(inv.IssueDate) as Year,
	MONTH(inv.IssueDate) as Month,
	WEEK(inv.IssueDate)	 as `Week`
FROM NeonBillingDev.tblInvoice inv
Inner JOIN tblAccount ac on ac.AccountID = inv.AccountID 
INNER join tblUser tu on tu.UserID = ac.Owner
			
where
			ac.`Status` = 1
			and tu.`Status` = 1 		
			AND (p_CurrencyID = '' OR ( p_CurrencyID !=''  and ac.CurrencyId = p_CurrencyID))
		   AND ac.CompanyID = p_CompanyID
			AND (p_OwnerID = '' OR   find_in_set(tu.UserID,p_OwnerID))
			AND `inv`.`InvoiceType` = 1
			AND `inv`.GrandTotal != 0
			AND (inv.IssueDate between p_Start and p_End)
GROUP BY 
			YEAR(inv.IssueDate) 
			,MONTH(inv.IssueDate)
			,WEEK(inv.IssueDate)
			,CONCAT(tu.FirstName,' ',tu.LastName)
			,tu.UserID;
			
IF p_ListType = 'Monthly'
THEN
  SELECT	
  	  	  td.AssignedUserText as AssignedUserText,
  	  	  td.AssignedUserID AS AssignedUserID,
		  ROUND(sum(td.Revenue),v_Round_) as Revenue,
		  v_CurrencyCode_,
		  CONCAT(CONCAT(case when td.Month <10 then concat('0',td.Month) else td.Month End, '/'), td.Year) AS MonthName,							
			v_Round_	 as round_number						
	 FROM tmp_Dashboard_invoices_  td group by td.Month,td.Year,td.AssignedUserID,td.AssignedUserText;
END IF;

IF p_ListType = 'Weekly'
THEN
 SELECT	
  	  	  td.AssignedUserText AS AssignedUserText,
  	  	  td.AssignedUserID,
		  ROUND(sum(td.Revenue),v_Round_) AS Revenue,
		  v_CurrencyCode_,
		  CONCAT(td.`Week`,'-',MAX( td.Year)) AS `Week`,
		  MAX( td.Year) AS `Year`,
		  td.`Week` AS `Weekday`,
		  v_Round_	 AS round_number						
	 FROM 
	 		tmp_Dashboard_invoices_  td 
	 GROUP BY
	  		td.`Week`
			,td.AssignedUserText
			,td.AssignedUserID
	 ORDER BY
	 	 `Year`
		  ,`Weekday` ASC;
END IF;
END