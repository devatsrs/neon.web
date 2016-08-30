CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkCDRIsLoadedOrNot`(IN `p_AccountID` INT, IN `p_CompanyID` INT, IN `p_UsageEndDate` DATETIME )
BEGIN

    DECLARE v_end_time_ DATE;
    DECLARE v_notInGateeway_ INT;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SELECT COUNT(*) INTO v_notInGateeway_ FROM tblGatewayAccount ga
	 INNER  JOIN  NeonRMDev.tblCompanyGateway cg ON cg.CompanyGatewayID  = ga.CompanyGatewayID AND cg.Status = 1 
	 WHERE ga.AccountID = p_AccountID and ga.CompanyID  = p_CompanyID;    


    IF v_notInGateeway_ > 0 
    THEN

        SELECT DATE_FORMAT(MIN(end_time), '%y-%m-%d') INTO v_end_time_  
        FROM  (
            SELECT  MAX(tmpusglog.end_time) AS end_time ,ga.CompanyGatewayID  
            FROM  tblGatewayAccount ga  
            INNER  JOIN  tblTempUsageDownloadLog tmpusglog on tmpusglog.CompanyGatewayID = ga.CompanyGatewayID
            INNER  JOIN  NeonRMDev.tblCompanyGateway cg ON cg.CompanyGatewayID  = ga.CompanyGatewayID AND cg.Status = 1 
            WHERE  ga.AccountID = p_AccountID and ga.CompanyID  = p_CompanyID
            GROUP BY ga.CompanyGatewayID
            )TBL;        

        IF p_UsageEndDate < v_end_time_ 
        THEN
            SELECT '1' AS isLoaded;
        ELSE
            SELECT '0' AS isLoaded;
        END IF;
    
    ELSE
        SELECT '1' AS isLoaded;
    END IF;

 	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END