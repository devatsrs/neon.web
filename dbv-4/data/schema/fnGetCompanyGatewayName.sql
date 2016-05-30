CREATE DEFINER=`root`@`localhost` FUNCTION `fnGetCompanyGatewayName`(`p_CompanyGatewayID` INT) RETURNS varchar(200) CHARSET utf8 COLLATE utf8_unicode_ci
BEGIN
DECLARE v_Title_ VARCHAR(200);

SELECT cg.Title INTO v_Title_ from LocalRatemanagement.tblCompanyGateway cg where cg.CompanyGatewayID = p_CompanyGatewayID;

RETURN v_Title_;
END