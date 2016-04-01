CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertGatewayAccount`(IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    set @stm = CONCAT('	 	  INSERT INTO tblGatewayAccount (CompanyID, CompanyGatewayID, GatewayAccountID, AccountName)
        SELECT
            distinct
            ud.CompanyID,
            ud.CompanyGatewayID,
            ud.GatewayAccountID,
            ud.GatewayAccountID
        FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
        LEFT JOIN tblGatewayAccount ga
            ON ga.GatewayAccountID = ud.GatewayAccountID
            AND ga.CompanyGatewayID = ud.CompanyGatewayID
            AND ga.CompanyID = ud.CompanyID
        WHERE processid =  "' , p_processId , '"
        AND ga.GatewayAccountID IS NULL
        AND ud.GatewayAccountID IS NOT NULL;
   ');
   
   PREPARE stmt FROM @stm;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
   
END