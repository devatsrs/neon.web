CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CustomerBulkRateClear`(IN `p_AccountIdList` LONGTEXT , IN `p_TrunkId` INT , IN `p_CodeDeckId` int, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(200) , IN `p_CountryId` INT , IN `p_CompanyId` INT)
BEGIN

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; 
	delete tblCustomerRate
	from tblCustomerRate
   INNER JOIN ( 
		SELECT c.CustomerRateID
      FROM   tblCustomerRate c
      INNER JOIN tblCustomerTrunk
          ON tblCustomerTrunk.TrunkID = c.TrunkID
          AND tblCustomerTrunk.AccountID = c.CustomerID
          AND tblCustomerTrunk.Status = 1
      INNER JOIN tblRate r
          ON c.RateID = r.RateID and r.CodeDeckId=p_CodeDeckId
		WHERE c.TrunkID = p_TrunkId
			   AND FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 
				AND ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
				AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
				AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) ) 
		) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END