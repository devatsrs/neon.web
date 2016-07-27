CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getSOA`(IN `p_CompanyID` INT, IN `p_accountID` INT, IN `p_StartDate` datetime, IN `p_EndDate` datetime, IN `p_isExport` INT )
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN


    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
     SET SESSION sql_mode='';


	 DROP TEMPORARY TABLE IF EXISTS tmp_Invoices;
    CREATE TEMPORARY TABLE tmp_Invoices (
        InvoiceNo VARCHAR(50),
        PeriodCover VARCHAR(50),
        Amount NUMERIC(18, 8),
        InvoiceType int
       -- DisputeAmount NUMERIC(18, 8),
        -- DisputeID INT
    );



    DROP TEMPORARY TABLE IF EXISTS tmp_Payments;
    CREATE TEMPORARY TABLE tmp_Payments (
        InvoiceNo VARCHAR(50),
        PeriodCover VARCHAR(50),
        Amount NUMERIC(18, 8),
        PaymentID INT,
        PaymentType VARCHAR(50)
    );


    DROP TEMPORARY TABLE IF EXISTS tmp_Disputes;
    CREATE TEMPORARY TABLE tmp_Disputes (
        InvoiceNo VARCHAR(50),
        created_at datetime,
        DisputeAmount NUMERIC(18, 8),
        InvoiceType VARCHAR(50),
        DisputeID INT

    );

    /*
	    New Logic

	    -- 1 Invoice Sent
	    -- 2 Payment against Invoice Sent

	    -- 3 Invoice Received
	    -- 4 Payment against Invoice Received

    */

     -- 1 Invoices
    INSERT into tmp_Invoices
    SELECT
			DISTINCT
         tblInvoice.InvoiceNumber,
         CASE
         	WHEN (tblInvoice.ItemInvoice = 1 ) THEN
                DATE_FORMAT(tblInvoice.IssueDate,'%d-%m-%Y')
				WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 0 ) THEN
					Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d/%m/%Y') ,'<br>' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d/%m/%Y'))
				WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 1) THEN
					(select Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d-%m-%Y') ,'-' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d-%m-%Y')) from tblInvoiceDetail where tblInvoiceDetail.InvoiceID= tblInvoice.InvoiceID order by InvoiceDetailID  limit 1)

       END AS PeriodCover,
         tblInvoice.GrandTotal,
         tblInvoice.InvoiceType
        FROM tblInvoice
        LEFT JOIN tblInvoiceDetail         ON tblInvoice.InvoiceID = tblInvoiceDetail.InvoiceID
        WHERE tblInvoice.CompanyID = p_CompanyID
        AND tblInvoice.AccountID = p_accountID
        AND ( (tblInvoice.InvoiceType = 2) OR ( tblInvoice.InvoiceType = 1 AND tblInvoice.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
  		  AND (p_StartDate = '0000-00-00' OR  (p_StartDate != '0000-00-00' AND  DATE_FORMAT(tblInvoice.IssueDate,'%Y-%m-%d') >= p_StartDate ) )
 		  AND (p_EndDate   = '0000-00-00' OR  (p_EndDate   != '0000-00-00' AND  DATE_FORMAT(tblInvoice.IssueDate,'%Y-%m-%d') <= p_EndDate ) )
		  Order by tblInvoice.IssueDate desc
		;


     -- 2 Payments
     INSERT into tmp_Payments
	     SELECT
				DISTINCT
            tblPayment.InvoiceNo,
            DATE_FORMAT(tblPayment.PaymentDate, '%d-%m-%Y') AS PaymentDate,
            tblPayment.Amount,
            tblPayment.PaymentID,
            tblPayment.PaymentType
        FROM tblPayment
        WHERE
  		      tblPayment.CompanyID = p_CompanyID
		  AND tblPayment.AccountID = p_accountID
		  AND tblPayment.Status = 'Approved'
        AND tblPayment.Recall = 0
		  AND (p_StartDate = '0000-00-00' OR  (p_StartDate != '0000-00-00' AND  DATE_FORMAT(tblPayment.PaymentDate,'%Y-%m-%d') >= p_StartDate ) )
 		  AND (p_EndDate   = '0000-00-00' OR  (p_EndDate   != '0000-00-00' AND  DATE_FORMAT(tblPayment.PaymentDate,'%Y-%m-%d') <= p_EndDate ) )
		Order by tblPayment.PaymentDate desc;


     -- 3 Disputes
     INSERT INTO tmp_Disputes
    	 SELECT
            InvoiceNo,
				created_at,
            DisputeAmount,
            InvoiceType,
            DisputeID
		  FROM tblDispute
        WHERE
  		      CompanyID = p_CompanyID
		  AND AccountID = p_accountID
		  AND Status = 0
		  AND (p_StartDate = '0000-00-00' OR  (p_StartDate != '0000-00-00' AND  DATE_FORMAT(created_at,'%Y-%m-%d') >= p_StartDate ) )
 		  AND (p_EndDate   = '0000-00-00' OR  (p_EndDate   != '0000-00-00' AND  DATE_FORMAT(created_at,'%Y-%m-%d') <= p_EndDate ) )
			order by created_at
			;
      	###################################################
    	
		 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_dup AS (SELECT * FROM tmp_Invoices);
		 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Disputes_dup AS (SELECT * FROM tmp_Disputes);
		     			
     -- Invoice Sent with Disputes	
     	SELECT  
		InvoiceNo as InvoiceOut_InvoiceNo,
		PeriodCover as InvoiceOut_PeriodCover,
		Amount as InvoiceOut_Amount,
		ifnull(DisputeAmount,0) as InvoiceOut_DisputeAmount,
		DisputeID as InvoiceOut_DisputeID
		FROM
	   (
     SELECT
				DISTINCT
            iv.InvoiceNo,
            iv.PeriodCover,
            iv.Amount,
            ds.DisputeAmount,
            ds.DisputeID
            
        FROM tmp_Invoices iv
		  LEFT JOIN tmp_Disputes ds on ds.InvoiceNo = iv.InvoiceNo AND ds.InvoiceType = iv.InvoiceType  AND ds.InvoiceNo is not null
        WHERE 
        iv.InvoiceType = 1          -- Only Invoice In Disputes
       
		
		 UNION ALL
		
		 SELECT
				DISTINCT
            ds.InvoiceNo,
            DATE_FORMAT(ds.created_at, '%d-%m-%Y') as PeriodCover,
            0 as Amount,
            ds.DisputeAmount,
            ds.DisputeID
		  FROM tmp_Disputes_dup ds 
		  LEFT JOIN  tmp_Invoices_dup iv on ds.InvoiceNo = iv.InvoiceNo and iv.InvoiceType = ds.InvoiceType 
        WHERE 
        ds.InvoiceType = 1  -- Only Invoice Sent Disputes
        AND iv.InvoiceNo is null
        
	 
		) tbl  ;
	 	
		
		-- Payment Received
		select 
		 InvoiceNo as PaymentIn_InvoiceNo,
        PeriodCover as PaymentIn_PeriodCover,
        Amount as PaymentIn_Amount,
        PaymentID as PaymentIn_PaymentID
		 from tmp_Payments 
		where PaymentType = 'Payment In'  ;
		
		
		-- Invoice Received with Disputes 
		SELECT 
		InvoiceNo as InvoiceIn_InvoiceNo,
		PeriodCover as InvoiceIn_PeriodCover,
		Amount as InvoiceIn_Amount,
		ifnull(DisputeAmount,0) as InvoiceIn_DisputeAmount,
		DisputeID as InvoiceIn_DisputeID
		FROM
	   (  SELECT
				DISTINCT
            iv.InvoiceNo,
            iv.PeriodCover,
            iv.Amount,
            ds.DisputeAmount,
            ds.DisputeID
        FROM tmp_Invoices iv
		  LEFT JOIN tmp_Disputes ds  on  ds.InvoiceNo = iv.InvoiceNo   AND ds.InvoiceType = iv.InvoiceType AND ds.InvoiceNo is not null
        WHERE 
        iv.InvoiceType = 2          -- Only Invoice In Disputes
        
		
		 UNION ALL
		
		 SELECT
				DISTINCT
            ds.InvoiceNo,
            DATE_FORMAT(ds.created_at, '%d-%m-%Y') as PeriodCover,
            0 as Amount,
            ds.DisputeAmount,
            ds.DisputeID
		  From tmp_Disputes_dup ds 
        LEFT JOIN tmp_Invoices_dup iv on ds.InvoiceNo = iv.InvoiceNo AND iv.InvoiceType = ds.InvoiceType 
        WHERE  ds.InvoiceType = 2  -- Only Invoice In Disputes
        AND iv.InvoiceNo is null
        
		)tbl;
    	
      -- Payment Sent
		select 
		  InvoiceNo as PaymentOut_InvoiceNo,
        PeriodCover as PaymentOut_PeriodCover,
        Amount as PaymentOut_Amount,
        PaymentID as PaymentOut_PaymentID
		   from tmp_Payments 
		where PaymentType = 'Payment Out' ;
		 
   	##########################
   	-- Counts 
   	
   	-- Total Invoice Sent Amount
   	select sum(Amount) as InvoiceOutAmountTotal
   	from tmp_Invoices where InvoiceType = 1 ;-- Invoice Sent
   	

   	-- Total Invoice Sent Amount
   	select sum(DisputeAmount) as InvoiceOutDisputeAmountTotal
   	from tmp_Disputes where InvoiceType = 1; -- Invoice Sent

   	-- Total Payment  IN Amount
   	select sum(Amount) as PaymentInAmountTotal
   	from tmp_Payments where PaymentType = 'Payment In' ; 
   	

   	-- Total Invoice IN Amount
   	select sum(Amount) as InvoiceInAmountTotal
   	from tmp_Invoices where InvoiceType = 2 ;-- Invoice Sent
   	
   	
   	-- Total Invoice IN Amount
   	select sum(DisputeAmount) as InvoiceInDisputeAmountTotal
   	from tmp_Disputes where InvoiceType = 2; -- Invoice Sent


   	-- Total Payment Out Amount
   	select sum(Amount) as PaymentOutAmountTotal
   	from tmp_Payments where PaymentType = 'Payment Out' ; 
   	
     
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END