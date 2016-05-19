CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_invoice_in_reconcile`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `StartTime` DATETIME, IN `EndTime` DATETIME)
BEGIN


		SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
		
		select ifnull(sum(vc.buying_cost),0) as DisputeTotal , ifnull(sum(billed_duration),0) as DisputeMinutes 
		from tblVendorCDR vc
		inner join tblVendorCDRHeader vh on vh.VendorCDRHeaderID = vc.VendorCDRHeaderID
		where vh.CompanyID = p_CompanyID and vh.AccountID = p_AccountID 
		and connect_time >= StartTime and connect_time <= EndTime;
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END