CREATE DEFINER=`root`@`localhost` FUNCTION `fnGetRoundingPoint`(
	`p_CompanyID` INT
) RETURNS int(11)
BEGIN

DECLARE v_Round_ int;

SELECT cs.Value INTO v_Round_ from tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID AND cs.Value <> '';

SET v_Round_ = IFNULL(v_Round_,2);

RETURN v_Round_;
END