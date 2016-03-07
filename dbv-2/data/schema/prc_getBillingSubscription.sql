CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getBillingSubscription`(IN `p_CompanyID` INT, IN `p_Advance` INT, IN `p_Name` VARCHAR(50), IN `p_CurrencyID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_Export` INT)
BEGIN
	
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	   
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	   
   	IF p_Export = 0
	THEN
		SELECT   
			tblBillingSubscription.Name,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.MonthlyFee) AS MonthlyFeeWithSymbol,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.WeeklyFee) AS WeeklyFeeWithSymbol,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.DailyFee) AS DailyFeeWithSymbol,
			tblBillingSubscription.SubscriptionID,
			tblBillingSubscription.ActivationFee,
			tblBillingSubscription.CurrencyID,
			tblBillingSubscription.InvoiceLineDescription,
			tblBillingSubscription.Description,
			tblBillingSubscription.Advance,
			tblBillingSubscription.MonthlyFee,
			tblBillingSubscription.WeeklyFee,
			tblBillingSubscription.DailyFee
    	FROM tblBillingSubscription
    	LEFT JOIN Ratemanagement3.tblCurrency on tblBillingSubscription.CurrencyID =tblCurrency.CurrencyId 
    	WHERE tblBillingSubscription.CompanyID = p_CompanyID
	        AND(p_CurrencyID =0  OR tblBillingSubscription.CurrencyID   = p_CurrencyID)
			AND(p_Advance is null OR tblBillingSubscription.Advance = p_Advance)
			AND(p_Name ='' OR tblBillingSubscription.Name like Concat('%',p_Name,'%'))
		ORDER BY
			CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
            END DESC,
            CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
           	END ASC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyFeeDESC') THEN MonthlyFee
           	END DESC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyFeeASC') THEN MonthlyFee
           	END ASC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'WeeklyFeeDESC') THEN WeeklyFee
           	END DESC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'WeeklyFeeASC') THEN WeeklyFee
           	END ASC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DailyFeeDESC') THEN DailyFee
           	END DESC,
           	CASE
           		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DailyFeeASC') THEN DailyFee
           	END ASC
         LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
        	COUNT(tblBillingSubscription.SubscriptionID) AS totalcount
     	FROM tblBillingSubscription
     	WHERE tblBillingSubscription.CompanyID = p_CompanyID
			AND(p_CurrencyID =0  OR tblBillingSubscription.CurrencyID   = p_CurrencyID)
			AND(p_Advance = 0 OR tblBillingSubscription.Advance = p_Advance)
			AND(p_Name ='' OR tblBillingSubscription.Name like Concat('%',p_Name,'%'));
 	END IF;
      
      
    IF p_Export = 1
	THEN
		
		SELECT   
			tblBillingSubscription.Name,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.MonthlyFee) AS MonthlyFee,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.WeeklyFee) AS WeeklyFee,
			CONCAT(IFNULL(tblCurrency.Symbol,''),tblBillingSubscription.DailyFee) AS DailyFee,
			tblBillingSubscription.ActivationFee,
			tblBillingSubscription.InvoiceLineDescription,
			tblBillingSubscription.Description,
			tblBillingSubscription.Advance
        FROM tblBillingSubscription
        LEFT JOIN Ratemanagement3.tblCurrency on tblBillingSubscription.CurrencyID =tblCurrency.CurrencyId 
        WHERE tblBillingSubscription.CompanyID = p_CompanyID
	    	AND(p_CurrencyID =0  OR tblBillingSubscription.CurrencyID   = p_CurrencyID)
			AND(p_Advance is null OR tblBillingSubscription.Advance = p_Advance)
			AND(p_Name ='' OR tblBillingSubscription.Name like Concat('%',p_Name,'%'));
				
	END IF;       
END