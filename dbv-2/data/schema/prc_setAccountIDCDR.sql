CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_setAccountIDCDR`(IN `p_companyid` int, IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	set @stm1 = CONCAT(' UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
	INNER JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = uh.GatewayAccountID
		AND ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ' ,  p_companyid , '
	AND uh.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END