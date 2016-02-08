CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getCustomerCliRate`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_TrunkID` INT, IN `p_Duration` DECIMAL(18,6), IN `p_CustomerCLI` VARCHAR(50))
BEGIN
     
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	 CALL prc_GetCustomerRate (p_CompanyID,p_AccountID,p_TrunkID,NULL,p_CustomerCLI,NULL,'All',1,'','','','','',1);
   -- INSERT INTO tmp_customerrate_ (Code,Description,Interval1,IntervalN,RoutinePlanName,ConnectionFee,Rate,EffectiveDate,LastModifiedDate,LastModifiedBy)
--     CALL prc_GetCustomerRate (p_CompanyID,p_AccountID,p_TrunkID,NULL,p_CustomerCLI,NULL,'All',1,'','','','','',1);
    

    SELECT 
    CASE WHEN  p_Duration >= Interval1
    THEN
    (Rate/60.0)*Interval1+CEILING((p_Duration-Interval1)/IntervalN)*(Rate/60.0)*IntervalN
        ElSE 
        Rate
    END AS Rate
    FROM tmp_customerrate_   WHERE Code = p_CustomerCLI

    ORDER BY CHAR_LENGTH(RTRIM(Code)) DESC
	 LIMIT 1;
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END