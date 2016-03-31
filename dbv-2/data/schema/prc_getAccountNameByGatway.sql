CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountNameByGatway`(IN `p_company_id` INT, IN `p_gatewayid` INT
)
BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    
     SELECT DISTINCT
                    a.AccountID,
                    a.AccountName                    
                FROM Ratemanagement3.tblAccount a
                INNER JOIN tblGatewayAccount ga ON a.AccountiD = ga.AccountiD AND a.Status = 1
                WHERE GatewayAccountID IS NOT NULL                
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid 
					 order by a.AccountName ASC;
					 
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	             
END