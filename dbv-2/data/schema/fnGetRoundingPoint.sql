CREATE DEFINER=`root`@`localhost` FUNCTION `fnGetRoundingPoint`(`p_CompanyID` INT) RETURNS int(11)
BEGIN

DECLARE v_Round_ int;

SELECT cs.Value INTO v_Round_ from LocalRatemanagement.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;

RETURN v_Round_;
END