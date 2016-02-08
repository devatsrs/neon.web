CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getActiveGatewayAccount`(IN `p_company_id` INT, IN `p_gatewayid` INT, IN `p_UserID` INT, IN `p_isAdmin` INT)
BEGIN

    DECLARE v_NameFormat_ VARCHAR(10);
    DECLARE v_RTR_ INT;
    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    
    
    DROP TEMPORARY TABLE IF EXISTS tmp_ActiveAccount;
    CREATE TEMPORARY TABLE tmp_ActiveAccount (
        GatewayAccountID varchar(100),
        AccountID INT,
        AccountName varchar(100),
        CDRType INT
    );
  
    
	  	 SELECT  case when Settings like '%"NameFormat":"NAMENUB"%'
			  then 'NAMENUB'
			  else
			  case when Settings like '%"NameFormat":"NUBNAME"%'
			  then 'NUBNAME'
			  else 
			  case when Settings like '%"NameFormat":"NUB"%'
			  then 'NUB'
			  else 
			  case when Settings like '%"NameFormat":"IP"%'
			  then 'IP'
			  else 
			  case when Settings like '%"NameFormat":"CLI"%'
			  then 'CLI'
			  else 
			  case when Settings like '%"NameFormat":"NAME"%'
			  then 'NAME'
			   
			else 'NAME' end end end end end end   as  NameFormat into v_NameFormat_
        FROM Ratemanagement3.tblCompanyGateway
        WHERE Settings LIKE '%NameFormat%' AND
        CompanyGatewayID = p_gatewayid
        limit 1;


 

         
 

        IF  v_NameFormat_ = 'NAMENUB'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM Ratemanagement3.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON concat(a.AccountName , '-' , a.Number) = ga.AccountName
                    AND a.Status = 1 
                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;

        IF v_NameFormat_ = 'NUBNAME'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM Ratemanagement3.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON concat(a.Number, '-' , a.AccountName) = ga.AccountName
                    AND a.Status = 1 

                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;

        IF v_NameFormat_ = 'NUB'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM Ratemanagement3.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON a.Number = ga.AccountName
                    AND a.Status = 1 

                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;

         IF v_NameFormat_ = 'IP'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM Ratemanagement3.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON  FIND_IN_SET(a.AccountIP,ga.AccountName) != 0
	                AND a.Status = 1 	
                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;
 
 


      	IF v_NameFormat_ = 'CLI'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM Ratemanagement3.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON FIND_IN_SET(a.CustomerCLI,ga.AccountName) != 0
                AND a.Status = 1  
                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;


    IF v_NameFormat_ = ''
            OR v_NameFormat_ IS NULL OR v_NameFormat_ = 'NAME'
        THEN
            INSERT INTO tmp_ActiveAccount
                SELECT DISTINCT
                    GatewayAccountID,
                    a.AccountID,
                    a.AccountName,
                    a.CDRType
                FROM Ratemanagement3.tblAccount  a
                INNER JOIN tblGatewayAccount ga
                    ON a.AccountName = ga.AccountName
                    AND a.Status = 1 

                WHERE GatewayAccountID IS NOT NULL
                AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
                AND a.CompanyId = p_company_id
                AND ga.CompanyGatewayID = p_gatewayid;
        END IF;
        
    SELECT DISTINCT
        GatewayAccountID,AccountID,AccountName,CDRType
    FROM tmp_ActiveAccount;


    UPDATE tblGatewayAccount
    INNER JOIN tmp_ActiveAccount a
        ON a.GatewayAccountID = tblGatewayAccount.GatewayAccountID
        AND tblGatewayAccount.CompanyGatewayID = p_gatewayid
    SET tblGatewayAccount.AccountID = a.AccountID
    where tblGatewayAccount.AccountID is null;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
END