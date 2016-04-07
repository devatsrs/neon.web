CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getPincodesGrid`(IN `p_CompanyID` INT, IN `p_Pincode` VARCHAR(50), IN `p_PinExt` VARCHAR(50), IN `p_StartDate` DATE, IN `p_EndDate` DATE, IN `p_AccountID` INT, IN `p_CurrencyID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50), IN `p_isExport` INT)
    COMMENT 'Pincodes Grid For DashBorad'
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT cs.Value INTO v_Round_ from LocalRatemanagement.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	CALL fnUsageDetail(p_CompanyID,p_AccountID,0,p_StartDate,p_EndDate,0,1,1,'');
	
	
	IF p_isExport = 0
	THEN
		SELECT
      	cld as DestinationNumber,
         CONCAT(IFNULL(c.Symbol,''),ROUND(SUM(cost),v_Round_)) AS TotalCharges,
 			COUNT(UsageDetailID) AS NoOfCalls
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN LocalRatemanagement.tblAccount a ON a.AccountID = uh.AccountID
		LEFT  JOIN LocalRatemanagement.tblCurrency c ON c.CurrencyId = a.CurrencyId
      WHERE ((p_PinExt = 'pincode' AND uh.pincode = p_Pincode ) OR (p_PinExt = 'extension' AND uh.extension = p_Pincode )) AND uh.cost>0 AND a.CurrencyId = p_CurrencyID
		GROUP BY uh.cld
		ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationNumberDESC') THEN DestinationNumber
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DestinationNumberASC') THEN DestinationNumber
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsDESC') THEN COUNT(UsageDetailID) 
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NoOfCallsASC') THEN COUNT(UsageDetailID) 
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesDESC') THEN SUM(cost)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalChargesASC') THEN SUM(cost)
                END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT COUNT(*) as totalcount FROM(SELECT
      	DISTINCT cld 
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN LocalRatemanagement.tblAccount a on a.AccountID = uh.AccountID
      WHERE ((p_PinExt = 'pincode' AND uh.pincode = p_Pincode ) OR (p_PinExt = 'extension' AND uh.extension = p_Pincode )) AND uh.cost>0 AND a.CurrencyId = p_CurrencyID
		GROUP BY uh.cld) tbl;
		
	END IF;
	
	IF p_isExport = 1
	THEN
		SELECT
      	cld as `Destination Number`,
         CONCAT(IFNULL(c.Symbol,''),ROUND(SUM(cost),v_Round_)) AS `Total Cost`,
 			COUNT(UsageDetailID) AS `Number of Times Dialed`
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN LocalRatemanagement.tblAccount a on a.AccountID = uh.AccountID
      WHERE ((p_PinExt = 'pincode' AND uh.pincode = p_Pincode ) OR (p_PinExt = 'extension' AND uh.extension = p_Pincode )) AND uh.cost>0 AND a.CurrencyId = p_CurrencyID
		GROUP BY uh.cld;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END