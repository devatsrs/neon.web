CREATE DEFINER=`neon-user`@`117.247.87.156` FUNCTION `fnGetRoundingPoint`(
	`p_CompanyID` INT
) RETURNS int(11)
BEGIN

DECLARE v_Round_ int;

SELECT cs.Value INTO v_Round_ from NeonRMDev.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;

SET v_Round_ = IFNULL(v_Round_,2);

RETURN v_Round_;
END