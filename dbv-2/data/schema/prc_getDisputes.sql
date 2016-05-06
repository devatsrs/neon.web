CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDisputes`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceNumber` VARCHAR(100), IN `p_Status` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50), IN `p_Export` INT)
BEGIN

     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    			SELECT   
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Setteled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
				CASE WHEN LENGTH(ds.Notes) > 100 THEN CONCAT(SUBSTRING(ds.Notes, 1, 100) , '...')
						 ELSE  ds.Notes 
						 END as ShortNotes ,
		 		ds.DisputeID,
		 	   a.AccountID,
		 		ds.Notes
		 		
            from tblDispute ds
            inner join LocalRatemanagement.tblAccount a on a.AccountID = ds.AccountID

				where ds.CompanyID = p_CompanyID
            
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) 
            
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNoDESC') THEN InvoiceNo
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNoASC') THEN InvoiceNo
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountDESC') THEN DisputeAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DisputeAmountASC') THEN DisputeAmount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN ds.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN ds.created_at
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
		 		sum(ds.DisputeAmount) as TotalDisputeAmount
            from tblDispute ds
            inner join LocalRatemanagement.tblAccount a on a.AccountID = ds.AccountID
				where ds.CompanyID = p_CompanyID
				
            AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
            AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;
            
            
	ELSE

				SELECT   
		 		a.AccountName,
				ds.InvoiceNo,
				ds.DisputeAmount,
				 CASE WHEN ds.`Status`= 0 THEN
				 		'Pending' 
				WHEN ds.`Status`= 1 THEN
					'Setteled' 
				WHEN ds.`Status`= 2 THEN
					'Cancel' 
				END as `Status`,
				ds.created_at as `CreatedDate`,
				ds.CreatedBy,
				CASE WHEN LENGTH(ds.Notes) > 100 THEN CONCAT(SUBSTRING(ds.Notes, 1, 100) , '...')
						 ELSE  ds.Notes 
						 END as ShortNotes ,
		 		ds.DisputeID,
		 	   a.AccountID,
		 		ds.Notes
				

            from tblDispute ds
            inner join LocalRatemanagement.tblAccount a on a.AccountID = ds.AccountID
            
            
				where ds.CompanyID = p_CompanyID
            
                       AND(p_InvoiceNumber is NULL OR ds.InvoiceNo like Concat('%',p_InvoiceNumber,'%'))
            AND(p_AccountID is NULL OR ds.AccountID = p_AccountID)
            AND(p_Status is NULL OR ds.`Status` = p_Status)
           AND(p_StartDate is NULL OR cast(ds.created_at as Date) >= p_StartDate)
            AND(p_EndDate is NULL OR cast(ds.created_at as Date) <= p_EndDate) ;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END