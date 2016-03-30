CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_RateDeleteFromCodedeck`(IN `p_CompanyId` INT, IN `p_CodeDeckId` INT, IN `p_RateID` LONGTEXT, IN `p_CountryId` INT, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(200))
BEGIN

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; 
   
	delete tblRate FROM tblRate INNER JOIN(
	  select r.RateID FROM `tblRate` r
		where (r.CompanyID = p_CompanyId)
		AND (r.CodeDeckId = p_CodeDeckId)
		and ( p_RateID ='' OR  FIND_IN_SET(r.RateID, p_RateID) )
		AND (p_CountryId = 0 OR r.CountryID = p_CountryId)
		AND (p_code = '' OR r.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_description = '' OR r.Description LIKE REPLACE(p_description, '*', '%')))
	  tr on tr.RateID=tblRate.RateID;				
	  
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END