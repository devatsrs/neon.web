CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAccountBalanceHistory`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	
    IF p_isExport = 0
    THEN
         
		SELECT
			abh.PermanentCredit,
			abh.BalanceThreshold,
			abh.CreatedBy,
			abh.created_at
		FROM tblAccountBalanceHistory abh
      WHERE abh.AccountID = p_AccountID
      ORDER BY                
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PermanentCreditDESC') THEN abh.PermanentCredit
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PermanentCreditASC') THEN abh.PermanentCredit
			END ASC,				
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ThresholdASC') THEN abh.BalanceThreshold
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ThresholdDESC') THEN abh.BalanceThreshold
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN abh.created_at
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN abh.created_at
			END DESC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN abh.CreatedBy
			END ASC,
			CASE
			  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN abh.CreatedBy
			END DESC
		LIMIT p_RowspPage OFFSET v_OffSet_;


     SELECT
         COUNT(abh.AccountBalanceHistoryID) as totalcount
     FROM tblAccountBalanceHistory abh
         WHERE abh.AccountID = p_AccountID;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
			abh.PermanentCredit,
			abh.BalanceThreshold,
			abh.CreatedBy,
			abh.created_at
		FROM tblAccountBalanceHistory abh
      WHERE abh.AccountID = p_AccountID;
    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END