CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashBoardPinCodes`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE, IN `p_AccountID` INT, IN `p_Type` INT, IN `p_Limit` INT, IN `p_PinExt` VARCHAR(50), IN `p_CurrencyID` INT)
    COMMENT 'Top 10 pincodes p_Type = 1 = cost,p_Type =2 =Duration'
BEGIN
	DECLARE v_Round_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SELECT cs.Value INTO v_Round_ from Ratemanagement3.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	CALL fnUsageDetail(p_CompanyID,p_AccountID,0,p_StartDate,p_EndDate,0,1,1,'');
	
	IF p_Type =1 AND  p_PinExt = 'pincode' /* Top Pincodes by cost*/
	THEN 
	
		SELECT ROUND(SUM(ud.cost),v_Round_) as PincodeValue,IFNULL(ud.pincode,'n/a') as Pincode  FROM tmp_tblUsageDetails_ ud 
		INNER JOIN Ratemanagement3.tblAccount a on ud.AccountID = a.AccountID 
		WHERE  ud.cost>0 AND ud.pincode IS NOT NULL AND ud.pincode != ''
			AND a.CurrencyId = p_CurrencyID
		GROUP BY ud.pincode
		ORDER BY PincodeValue DESC
		LIMIT p_Limit;
	
	END IF;
	
	IF p_Type =2 AND  p_PinExt = 'pincode' /* Top Pincodes by Duration*/
	THEN 
	
		SELECT SUM(ud.billed_duration) as PincodeValue,IFNULL(ud.pincode,'n/a') as Pincode FROM tmp_tblUsageDetails_ ud
		INNER JOIN Ratemanagement3.tblAccount a on ud.AccountID = a.AccountID  
		WHERE  ud.cost>0 AND ud.pincode IS NOT NULL AND ud.pincode != ''
			AND a.CurrencyId = p_CurrencyID
		GROUP BY ud.pincode
		ORDER BY PincodeValue DESC
		LIMIT p_Limit;
	
	END IF;
	
	IF p_Type =1 AND  p_PinExt = 'extension' /* Top Extension by cost*/
	THEN 
	
		SELECT ROUND(SUM(ud.cost),v_Round_) as PincodeValue,IFNULL(ud.extension,'n/a') as Pincode  FROM tmp_tblUsageDetails_ ud 
		INNER JOIN Ratemanagement3.tblAccount a on ud.AccountID = a.AccountID 
		WHERE  ud.cost>0 AND ud.extension IS NOT NULL AND ud.extension != ''
			AND a.CurrencyId = p_CurrencyID 
		GROUP BY ud.extension
		ORDER BY PincodeValue DESC
		LIMIT p_Limit;
	
	END IF;
	
	IF p_Type =2 AND  p_PinExt = 'extension' /* Top Extension by Duration*/
	THEN 
	
		SELECT SUM(ud.billed_duration) as PincodeValue,IFNULL(ud.extension,'n/a') as Pincode FROM tmp_tblUsageDetails_ ud
		INNER JOIN Ratemanagement3.tblAccount a on ud.AccountID = a.AccountID 
		WHERE  ud.cost>0 AND ud.extension IS NOT NULL AND ud.extension != ''
			AND a.CurrencyId = p_CurrencyID
		GROUP BY ud.extension
		ORDER BY PincodeValue DESC
		LIMIT p_Limit;
	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END