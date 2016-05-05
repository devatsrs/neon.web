CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDisputes`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceNumber` VARCHAR(100), IN `p_Status` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50), IN `p_Export` INT)
BEGIN

     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    			SELECT   
		 		a.AccountName,
				iv.InvoiceNumber,
				iv.GrandTotal,
		 		ds.DisputeTotal,
		 		ds.DisputeDifference,
		 		ds.DisputeDifferencePer,
		 		
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Setteled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `Created Date`,
				ds.CreatedBy,
				CASE WHEN LENGTH(ds.Notes) > 100 THEN CONCAT(SUBSTRING(ds.Notes, 1, 100) , '...')
						 ELSE  ds.Notes 
						 END as ShortNotes ,
		 		ds.DisputeID,
		 		ds.DisputeMinutes,
		 		ds.MinutesDifference,
		 		ds.MinutesDifferencePer,
				 a.AccountID,
		 		iv.InvoiceID,
		 		ds.Notes
		 		

            from tblDispute ds
            inner join tblInvoice iv on ds.InvoiceID = iv.InvoiceID
            inner join LocalRatemanagement.tblAccount a on a.AccountID = iv.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
            AND(p_InvoiceNumber is NULL OR iv.InvoiceNumber like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR iv.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
            AND(p_StartDate is NULL OR ds.created_at >= p_StartDate)
            AND(p_EndDate is NULL OR ds.created_at <= p_EndDate) 
            
            
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberDESC') THEN InvoiceNumber
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberASC') THEN InvoiceNumber
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeTotalDESC') THEN DisputeTotal
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeTotalASC') THEN DisputeTotal
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeDifferenceDESC') THEN DisputeDifference
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeDifferenceASC') THEN DisputeDifference
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeDifferencePerDESC') THEN DisputeDifferencePer
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeDifferencePerASC') THEN DisputeDifferencePer
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN ds.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN ds.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDDESC') THEN DisputeID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeIDASC') THEN DisputeID
                END ASC 			 
					 					 					    	             
                
            LIMIT p_RowspPage OFFSET v_OffSet_;

		 

				 SELECT   
		 		COUNT(ds.DisputeID) AS totalcount,
		 		sum(iv.GrandTotal) as TotalGrandTotal,
		 		sum(ds.DisputeTotal) as TotalDisputeTotal,		 		
		 		sum(ds.DisputeDifference) as TotalDisputeDifference
		 		
            from tblDispute ds
            inner join tblInvoice iv on ds.InvoiceID = iv.InvoiceID
            inner join LocalRatemanagement.tblAccount a on a.AccountID = iv.AccountID
				where ds.CompanyID = p_CompanyID
				
                        AND(p_InvoiceNumber is NULL OR iv.InvoiceNumber like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR iv.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
            AND(p_StartDate is NULL OR ds.created_at >= p_StartDate)
            AND(p_EndDate is NULL OR ds.created_at <= p_EndDate);
            
            
	ELSE

				SELECT   
		 		a.AccountName,
				iv.InvoiceNumber,
				iv.GrandTotal,
		 		ds.DisputeTotal,
		 		ds.DisputeDifference,
		 		ds.DisputeDifferencePer,
		 		
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Setteled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `Created Date`,
				ds.CreatedBy,
				ds.Notes ,
		 		ds.DisputeMinutes,
		 		ds.MinutesDifference,
		 		ds.MinutesDifferencePer
				

            from tblDispute ds
            inner join tblInvoice iv on ds.InvoiceID = iv.InvoiceID
            inner join LocalRatemanagement.tblAccount a on a.AccountID = iv.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
                       AND(p_InvoiceNumber is NULL OR iv.InvoiceNumber like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR iv.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
            AND(p_StartDate is NULL OR ds.created_at >= p_StartDate)
            AND(p_EndDate is NULL OR ds.created_at <= p_EndDate);

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END