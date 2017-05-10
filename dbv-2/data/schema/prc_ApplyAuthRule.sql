CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ApplyAuthRule`(
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
				GatewayAccountID,
				a.AccountID,
				ga.ServiceID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId
				AND CONCAT(a.AccountName , '-' , a.Number) = ga.AccountName
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND a.CompanyId = p_CompanyID
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
				GatewayAccountID,
				a.AccountID,
				ga.ServiceID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId
				AND CONCAT(a.Number, '-' , a.AccountName) = ga.AccountName
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND a.CompanyId = p_CompanyID
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
				GatewayAccountID,
				a.AccountID,
				ga.ServiceID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId
				AND a.Number = ga.AccountName
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID	
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND a.CompanyId = p_CompanyID
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
				GatewayAccountID,
				a.AccountID,
				aa.ServiceID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'IP' OR aa.VendorAuthRule ='IP')
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId 
				AND ga.ServiceID = p_ServiceID AND aa.ServiceID = ga.ServiceID 
				AND ( FIND_IN_SET(ga.AccountName,aa.CustomerAuthValue) != 0 OR FIND_IN_SET(ga.AccountName,aa.VendorAuthValue) != 0 )
			WHERE a.CompanyId = p_CompanyID
			AND a.`Status` = 1
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID;

		END IF;

		IF p_NameFormat = 'CLI'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				aa.ServiceID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId 
			INNER JOIN NeonRMDev.tblCLIRateTable aa
				ON a.AccountID = aa.AccountID
				AND ga.ServiceID = p_ServiceID AND aa.ServiceID = ga.ServiceID 
				AND ga.AccountName = aa.CLI
			WHERE a.CompanyId = p_CompanyID
			AND a.`Status` = 1
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID;

		END IF;

		IF p_NameFormat = '' OR p_NameFormat IS NULL OR p_NameFormat = 'NAME'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				ga.ServiceID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId 
				AND a.AccountName = ga.AccountName
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID 
				AND aa.ServiceID = ga.ServiceID 
			WHERE a.CompanyId = p_CompanyID
			AND a.`Status` = 1			
			AND ga.ServiceID = p_ServiceID 
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAME' OR aa.VendorAuthRule ='NAME' )) OR
					aa.AccountID IS NULL
				);

		END IF;

		IF p_NameFormat = 'Other'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				aa.ServiceID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'Other' OR aa.VendorAuthRule ='Other')
			INNER JOIN tblGatewayAccount ga
				ON ga.CompanyID = a.CompanyId
				AND ga.ServiceID = aa.ServiceID 
				AND ( aa.VendorAuthValue = ga.AccountName OR aa.CustomerAuthValue = ga.AccountName )
			WHERE a.CompanyId = p_CompanyID
			AND a.`Status` = 1
			AND GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.ServiceID = p_ServiceID;

		END IF;

		SET v_pointer_ = v_pointer_ + 1;

	END WHILE;

END