CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getActiveGatewayAccount`(
	IN `p_company_id` INT,
	IN `p_gatewayid` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_NameFormat` VARCHAR(50)
)
BEGIN

	DECLARE v_NameFormat_ VARCHAR(10);
	DECLARE v_RTR_ INT;
	DECLARE v_pointer_ INT ;
	DECLARE v_rowCount_ INT ;
	DECLARE v_ServiceID_ INT ;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_ActiveAccount;
	CREATE TEMPORARY TABLE tmp_ActiveAccount (
		GatewayAccountID varchar(100),
		AccountID INT,
		AccountName varchar(100)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_AuthenticateRules_;
	CREATE TEMPORARY TABLE tmp_AuthenticateRules_ (
		RowNo INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AuthRule VARCHAR(50)
	);
	
	SELECT ServiceID INTO v_ServiceID_ FROM tmp_AccountsService_ LIMIT 1;
	
	SET v_ServiceID_ = IFNULL(v_ServiceID_,0); 

	IF p_NameFormat = ''
	THEN

		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT
			CASE WHEN Settings LIKE '%"NameFormat":"NAMENUB"%'
			THEN 'NAMENUB'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"NUBNAME"%'
			THEN 'NUBNAME'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"NUB"%'
			THEN 'NUB'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"IP"%'
			THEN 'IP'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"CLI"%'
			THEN 'CLI'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"NAME"%'
			THEN 'NAME'
			ELSE 'NAME' END END END END END END   AS  NameFormat
		FROM NeonRMDev.tblCompanyGateway
		WHERE Settings LIKE '%NameFormat%' 
		AND CompanyGatewayID = p_gatewayid
		AND Settings LIKE CONCAT('%"ServiceID":"',v_ServiceID_,'"%')
		LIMIT 1;

	END IF;

	IF p_NameFormat != ''
	THEN

		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT p_NameFormat;

	END IF;

	INSERT INTO tmp_AuthenticateRules_  (AuthRule)
	SELECT DISTINCT CustomerAuthRule FROM NeonRMDev.tblAccountAuthenticate aa WHERE CustomerAuthRule IS NOT NULL AND ServiceID = v_ServiceID_
	UNION
	SELECT DISTINCT VendorAuthRule FROM NeonRMDev.tblAccountAuthenticate aa WHERE VendorAuthRule IS NOT NULL AND ServiceID = v_ServiceID_;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AuthenticateRules_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_NameFormat_ = ( SELECT AuthRule FROM tmp_AuthenticateRules_  WHERE RowNo = v_pointer_ );

		IF  v_NameFormat_ = 'NAMENUB'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON concat(a.AccountName , '-' , a.Number) = ga.AccountName
				AND a.Status = 1
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ga.ServiceID = v_ServiceID_;

		END IF;

		IF v_NameFormat_ = 'NUBNAME'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON concat(a.Number, '-' , a.AccountName) = ga.AccountName
				AND a.Status = 1
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ga.ServiceID = v_ServiceID_;

		END IF;

		IF v_NameFormat_ = 'NUB'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON a.Number = ga.AccountName
				AND a.Status = 1
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ga.ServiceID = v_ServiceID_;

		END IF;

		IF v_NameFormat_ = 'IP'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'IP' OR aa.VendorAuthRule ='IP')
			INNER JOIN tblGatewayAccount ga
				ON   a.Status = 1
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ga.ServiceID = v_ServiceID_
			AND ( FIND_IN_SET(ga.AccountName,aa.CustomerAuthValue) != 0 OR FIND_IN_SET(ga.AccountName,aa.VendorAuthValue) != 0 );

		END IF;


		IF v_NameFormat_ = 'CLI'
		THEN
			

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblCLIRateTable aa
				ON a.AccountID = aa.AccountID
			INNER JOIN tblGatewayAccount ga
				ON   a.Status = 1
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ga.ServiceID = v_ServiceID_
			AND ga.AccountName = aa.CLI;

		END IF;


		IF v_NameFormat_ = '' OR v_NameFormat_ IS NULL OR v_NameFormat_ = 'NAME'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'Other' OR aa.VendorAuthRule ='Other')
			INNER JOIN tblGatewayAccount ga
				ON    a.Status = 1
			WHERE GatewayAccountID IS NOT NULL
			AND ga.AccountID IS NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ga.ServiceID = v_ServiceID_
			AND ((aa.AccountAuthenticateID IS NOT NULL AND (aa.VendorAuthValue = ga.AccountName OR aa.CustomerAuthValue = ga.AccountName  )) OR (aa.AccountAuthenticateID IS NULL AND a.AccountName = ga.AccountName));

		END IF;

		SET v_pointer_ = v_pointer_ + 1;

	END WHILE;

	UPDATE tblGatewayAccount
	INNER JOIN tmp_ActiveAccount a
		ON a.GatewayAccountID = tblGatewayAccount.GatewayAccountID
		AND tblGatewayAccount.CompanyGatewayID = p_gatewayid
		AND tblGatewayAccount.ServiceID = v_ServiceID_
	SET tblGatewayAccount.AccountID = a.AccountID
	WHERE tblGatewayAccount.AccountID is null;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END