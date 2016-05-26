CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDisputeDetail`(IN `p_CompanyID` INT, IN `p_DisputeID` INT)
BEGIN

 
 
 
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

 

				SELECT   
				ds.DisputeID,
				CASE WHEN ds.InvoiceType = 2 THEN 'Invoice Received' 
				     WHEN ds.InvoiceType = 1 THEN 'Invoice Sent' 
				     ELSE ''
				END as InvoiceType,
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				ds.Notes,
 			   CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Settled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at,
				ds.CreatedBy,
		 		ds.Attachment,
		 		ds.updated_at
            from tblDispute ds
            inner join LocalRatemanagement.tblAccount a on a.CompanyId = ds.CompanyID and a.AccountID = ds.AccountID
            where ds.CompanyID = p_CompanyID
            AND ds.DisputeID = p_DisputeID
				limit 1;
            
            

 

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
 

END