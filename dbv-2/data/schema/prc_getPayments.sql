CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getPayments`(IN `p_CompanyID` INT, IN `p_accountID` INT, IN `p_InvoiceNo` varchar(30), IN `p_Status` varchar(20), IN `p_PaymentType` varchar(20), IN `p_PaymentMethod` varchar(20), IN `p_RecallOnOff` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isCustomer` INT , IN `p_paymentStartDate` DATETIME, IN `p_paymentEndDate` DATETIME, IN `p_isExport` INT )
BEGIN
		
    	DECLARE v_OffSet_ int;
    	DECLARE v_Round_ int;
    	
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
     
		SELECT cs.Value INTO v_Round_ from LocalRatemanagement.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;

	    
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	IF p_isExport = 0
    THEN
    select
            tblPayment.PaymentID,
            tblAccount.AccountName,
            tblPayment.AccountID,
            ROUND(tblPayment.Amount,v_Round_) AS Amount,
            CASE WHEN p_isCustomer = 1 THEN
              CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
              END
            ELSE  PaymentType
            END as PaymentType,
            tblPayment.CurrencyID,
            tblPayment.PaymentDate,
            tblPayment.Status,
            tblPayment.CreatedBy,
            tblPayment.PaymentProof,
            tblPayment.InvoiceNo,
            tblPayment.PaymentMethod,
            tblPayment.Notes,
            tblPayment.Recall,
            tblPayment.RecallReasoan,
            tblPayment.RecallBy,
            CONCAT(IFNULL(tblCurrency.Symbol,''),ROUND(tblPayment.Amount,v_Round_)) as AmountWithSymbol
            from tblPayment
            left join LocalRatemanagement.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
            LEFT JOIN LocalRatemanagement.tblCurrency  ON tblPayment.CurrencyID =   tblCurrency.CurrencyId
            where tblPayment.CompanyID = p_CompanyID
            AND(tblPayment.Recall = p_RecallOnOff)
            AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
            AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo = p_InvoiceNo))
            AND((p_Status IS NULL OR tblPayment.Status = p_Status))
            AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
            AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
            AND (p_paymentStartDate is null OR ( p_paymentStartDate != '' AND tblPayment.PaymentDate >= p_paymentStartDate))
			   AND (p_paymentEndDate  is null OR ( p_paymentEndDate != '' AND tblPayment.PaymentDate <= p_paymentEndDate))
            ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN tblAccount.AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN tblAccount.AccountName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNoDESC') THEN InvoiceNo
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNoASC') THEN InvoiceNo
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountDESC') THEN Amount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountASC') THEN Amount
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentTypeDESC') THEN PaymentType
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentTypeASC') THEN PaymentType
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentDateDESC') THEN PaymentDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentDateASC') THEN PaymentDate
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN tblPayment.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN tblPayment.Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN tblPayment.CreatedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN tblPayment.CreatedBy
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

            SELECT
            COUNT(tblPayment.PaymentID) AS totalcount,CONCAT(IFNULL(tblCurrency.Symbol,''),ROUND(sum(tblPayment.Amount),v_Round_)) as total_grand
            from tblPayment
            left join LocalRatemanagement.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
            LEFT JOIN LocalRatemanagement.tblCurrency  ON tblPayment.CurrencyID =   tblCurrency.CurrencyId
            where tblPayment.CompanyID = p_CompanyID
            AND(tblPayment.Recall = p_RecallOnOff)
            AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
            AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo = p_InvoiceNo))
            AND((p_Status IS NULL OR tblPayment.Status = p_Status))
            AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
            AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
            AND (p_paymentStartDate is null OR ( p_paymentStartDate != '' AND tblPayment.PaymentDate >= p_paymentStartDate))
			   AND (p_paymentEndDate  is null OR ( p_paymentEndDate != '' AND tblPayment.PaymentDate <= p_paymentEndDate));

	END IF;
	IF p_isExport = 1
    THEN

		SELECT 
            AccountName,
            CONCAT(IFNULL(tblCurrency.Symbol,''),ROUND(tblPayment.Amount,v_Round_)) as Amount,
            CASE WHEN p_isCustomer = 1 THEN
              CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
              END
            ELSE  PaymentType
            END as PaymentType,
            PaymentDate,
            tblPayment.Status,
            tblPayment.CreatedBy,
            InvoiceNo,
            tblPayment.PaymentMethod,
            Notes 
			from tblPayment
            left join LocalRatemanagement.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
            LEFT JOIN LocalRatemanagement.tblCurrency  ON tblPayment.CurrencyID =   tblCurrency.CurrencyId
            where tblPayment.CompanyID = p_CompanyID
            AND(tblPayment.Recall = p_RecallOnOff)
            AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
            AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo = p_InvoiceNo))
            AND((p_Status IS NULL OR tblPayment.Status = p_Status))
            AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
            AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
            AND (p_paymentStartDate is null OR ( p_paymentStartDate != '' AND tblPayment.PaymentDate >= p_paymentStartDate))
			AND (p_paymentEndDate  is null OR ( p_paymentEndDate != '' AND tblPayment.PaymentDate <= p_paymentEndDate));
	END IF;
	
	
	-- export data for customer panel
	IF p_isExport = 2
    THEN

		SELECT 
            AccountName,
            CONCAT(IFNULL(tblCurrency.Symbol,''),ROUND(tblPayment.Amount,v_Round_)) as Amount,
            CASE WHEN p_isCustomer = 1 THEN
              CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
              END
            ELSE  PaymentType
            END as PaymentType,
            PaymentDate,
            tblPayment.Status,
            InvoiceNo,
            tblPayment.PaymentMethod,
            Notes 
			from tblPayment
            left join LocalRatemanagement.tblAccount ON tblPayment.AccountID = tblAccount.AccountID
            LEFT JOIN LocalRatemanagement.tblCurrency  ON tblPayment.CurrencyID =   tblCurrency.CurrencyId
            where tblPayment.CompanyID = p_CompanyID
            AND(tblPayment.Recall = p_RecallOnOff)
            AND(p_accountID = 0 OR tblPayment.AccountID = p_accountID)
            AND((p_InvoiceNo IS NULL OR tblPayment.InvoiceNo = p_InvoiceNo))
            AND((p_Status IS NULL OR tblPayment.Status = p_Status))
            AND((p_PaymentType IS NULL OR tblPayment.PaymentType = p_PaymentType))
            AND((p_PaymentMethod IS NULL OR tblPayment.PaymentMethod = p_PaymentMethod))
            AND (p_paymentStartDate is null OR ( p_paymentStartDate != '' AND tblPayment.PaymentDate >= p_paymentStartDate))
			AND (p_paymentEndDate  is null OR ( p_paymentEndDate != '' AND tblPayment.PaymentDate <= p_paymentEndDate));
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END