CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_ApplyAuthRule`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ServiceID` INT
)
BEGIN
	DECLARE p_NameFormat VARCHAR(10);
	DECLARE v_pointer_ INT ;
	DECLARE v_rowCount_ INT ;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AuthenticateRules_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET p_NameFormat = ( SELECT AuthRule FROM tmp_AuthenticateRules_  WHERE RowNo = v_pointer_ );

		IF  p_NameFormat = 'NAMENUB'
		THEN
	
			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				ga.AccountName,
				ga.AccountNumber,
				ga.AccountCLI,
				ga.AccountIP,
				a.AccountID,
				ga.ServiceID,
				a.CompanyId
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON CONCAT(a.AccountName , '-' , a.Number) = ga.AccountName
				-- AND ga.CompanyID = a.CompanyId 				
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
		--	AND a.CompanyId = p_CompanyID
			AND a.Status = 1
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID
			AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAMENUB' OR aa.VendorAuthRule ='NAMENUB' )) OR
              aa.AccountID IS NULL
          );
	
		END IF;
	
		IF p_NameFormat = 'NUBNAME'
		THEN
	
			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				ga.AccountName,
				ga.AccountNumber,
				ga.AccountCLI,
				ga.AccountIP,
				a.AccountID,
				ga.ServiceID,
				a.CompanyId
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON CONCAT(a.Number, '-' , a.AccountName) = ga.AccountName 
				-- AND ga.CompanyID = a.CompanyId
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
		--	AND a.CompanyId = p_CompanyID
			AND a.Status = 1
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID
			AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NUBNAME' OR aa.VendorAuthRule ='NUBNAME' )) OR
              aa.AccountID IS NULL
          );
	
		END IF;
	
		IF p_NameFormat = 'NUB'
		THEN
	
			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				ga.AccountName,
				ga.AccountNumber,
				ga.AccountCLI,
				ga.AccountIP,
				a.AccountID,
				ga.ServiceID,
				a.CompanyId
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON a.Number = ga.AccountNumber 
				-- AND ga.CompanyID = a.CompanyId
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID	
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			-- AND a.CompanyId = p_CompanyID
			AND a.Status = 1
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID
			AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NUB' OR aa.VendorAuthRule ='NUB' )) OR
              aa.AccountID IS NULL
          );
	
		END IF;
	
		IF p_NameFormat = 'IP'
		THEN
	
			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				ga.AccountName,
				ga.AccountNumber,
				ga.AccountCLI,
				ga.AccountIP,
				a.AccountID,
				aa.ServiceID,
				a.CompanyId
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'IP' OR aa.VendorAuthRule ='IP')
			INNER JOIN tblGatewayAccount ga
				ON  ga.ServiceID = p_ServiceID
				-- AND ga.CompanyID = a.CompanyId 
				AND aa.ServiceID = ga.ServiceID 
				AND ( (aa.CustomerAuthRule = 'IP' AND FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) != 0) OR (aa.VendorAuthRule ='IP' AND FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) != 0) )
			WHERE a.`Status` = 1
			-- AND a.CompanyId = p_CompanyID 			 			
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID;
	
		END IF;
	
		IF p_NameFormat = 'CLI'
		THEN
	
			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				ga.AccountName,
				ga.AccountNumber,
				ga.AccountCLI,
				ga.AccountIP,
				a.AccountID,
				aa.ServiceID,
				a.CompanyId
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.ServiceID = p_ServiceID
				-- AND ga.CompanyID = a.CompanyId 				 				
			INNER JOIN NeonRMDev.tblCLIRateTable aa
				ON a.AccountID = aa.AccountID
				AND aa.ServiceID = ga.ServiceID 
				AND ga.AccountCLI = aa.CLI
			WHERE a.`Status` = 1
			-- AND a.CompanyId = p_CompanyID			 			
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID;
	
		END IF;
	
		IF p_NameFormat = '' OR p_NameFormat IS NULL OR p_NameFormat = 'NAME'
		THEN
	
			-- IF sippy add sippy gateway too
			select count(*) into @IsSippy from NeonRMDev.tblGateway g inner join NeonRMDev.tblCompanyGateway cg
			on cg.GatewayID = g.GatewayID
			AND cg.`Status` = 1
			AND cg.CompanyGatewayID = p_CompanyGatewayID
			AND g.Name = 'SippySFTP';

			IF (@IsSippy > 0 ) THEN

		
					INSERT INTO tmp_ActiveAccount
					SELECT DISTINCT
						ga.AccountName,
						ga.AccountNumber,
						ga.AccountCLI,
						ga.AccountIP,
						sa.AccountID,
						ga.ServiceID,
						a.CompanyId
					FROM NeonRMDev.tblAccount  a
					LEFT JOIN NeonRMDev.tblAccountAuthenticate aa
						ON a.AccountID = aa.AccountID 
					INNER JOIN tblGatewayAccount ga
						ON aa.ServiceID = ga.ServiceID  
					--	AND  ga.CompanyID = a.CompanyId
					--	AND a.AccountName = ga.AccountName -- already comment by someone
					INNER JOIN NeonRMDev.tblAccountSippy sa
						ON ( (a.IsCustomer = 1	AND ga.AccountNumber = sa.i_account)	OR	( a.IsVendor = 1	AND ga.AccountNumber = sa.i_vendor ) )
						-- sa.CompanyID = a.CompanyId AND						 	
					WHERE a.`Status` = 1 
					-- AND a.CompanyId = p_CompanyID			
					AND ga.ServiceID = p_ServiceID 
					AND GatewayAccountID IS NOT NULL
					AND ga.AccountID IS NULL
					AND ga.CompanyGatewayID = p_CompanyGatewayID
					AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAME' OR aa.VendorAuthRule ='NAME' )) OR
		              aa.AccountID IS NULL
		          );
		 
			ELSE 
			
			
					INSERT INTO tmp_ActiveAccount
					SELECT DISTINCT
						ga.AccountName,
						ga.AccountNumber,
						ga.AccountCLI,
						ga.AccountIP,
						a.AccountID,
						ga.ServiceID,
						a.CompanyId
					FROM NeonRMDev.tblAccount  a
					INNER JOIN tblGatewayAccount ga
						ON a.AccountName = ga.AccountName  
						-- AND ga.CompanyID = a.CompanyId
					LEFT JOIN NeonRMDev.tblAccountAuthenticate aa
						ON a.AccountID = aa.AccountID 
						AND aa.ServiceID = ga.ServiceID 
					WHERE a.`Status` = 1 
					-- AND a.CompanyId = p_CompanyID			
					AND ga.ServiceID = p_ServiceID 
					AND GatewayAccountID IS NOT NULL
					AND ga.AccountID IS NULL
					AND ga.CompanyGatewayID = p_CompanyGatewayID
					AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAME' OR aa.VendorAuthRule ='NAME' )) OR
		              aa.AccountID IS NULL
		          );
			
				
			END IF;
	 
	
		END IF;
		
		IF p_NameFormat = 'Other'
		THEN
	
			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				ga.AccountName,
				ga.AccountNumber,
				ga.AccountCLI,
				ga.AccountIP,
				a.AccountID,
				aa.ServiceID,
				a.CompanyId
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'Other' OR aa.VendorAuthRule ='Other')
			INNER JOIN tblGatewayAccount ga
				 ON ga.ServiceID = aa.ServiceID 
				--  ga.CompanyID = a.CompanyId AND
				AND ( (aa.VendorAuthRule ='Other' AND aa.VendorAuthValue = ga.AccountName) OR (aa.CustomerAuthRule = 'Other' AND aa.CustomerAuthValue = ga.AccountName) )
			WHERE a.`Status` = 1 
			-- AND a.CompanyId = p_CompanyID			
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID;
	
		END IF;

		SET v_pointer_ = v_pointer_ + 1;

	END WHILE;

END