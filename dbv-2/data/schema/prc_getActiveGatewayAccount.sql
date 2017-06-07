CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getActiveGatewayAccount`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
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
		AccountName varchar(100),
		AccountNumber varchar(100),
		AccountCLI varchar(100),
		AccountIP varchar(100),
		AccountID INT,
		ServiceID INT
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_AuthenticateRules_;
	CREATE TEMPORARY TABLE tmp_AuthenticateRules_ (
		RowNo INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AuthRule VARCHAR(50)
	);

	/* gateway level authentication rule */
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
		AND CompanyGatewayID = p_CompanyGatewayID
		LIMIT 1;

	END IF;

	IF p_NameFormat != ''
	THEN
		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT p_NameFormat;

	END IF;
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_Service_);
	/* service level authentication rule */ 
	IF v_rowCount_ > 0
	THEN

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_ServiceID_ = (SELECT ServiceID FROM tmp_Service_ t WHERE t.RowID = v_pointer_);

			INSERT INTO tmp_AuthenticateRules_  (AuthRule)
			SELECT DISTINCT CustomerAuthRule FROM NeonRMDev.tblAccountAuthenticate aa WHERE CompanyID = p_CompanyID AND CustomerAuthRule IS NOT NULL AND ServiceID = v_ServiceID_
			UNION
			SELECT DISTINCT VendorAuthRule FROM NeonRMDev.tblAccountAuthenticate aa WHERE CompanyID = p_CompanyID AND VendorAuthRule IS NOT NULL AND ServiceID = v_ServiceID_;

			CALL prc_ApplyAuthRule(p_CompanyID,p_CompanyGatewayID,v_ServiceID_);

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

	END IF;

	/* account level authentication rule */
	IF (SELECT COUNT(*) FROM NeonRMDev.tblAccountAuthenticate WHERE CompanyID = p_CompanyID AND ServiceID = 0) > 0
	THEN


		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT DISTINCT CustomerAuthRule FROM NeonRMDev.tblAccountAuthenticate aa WHERE CompanyID = p_CompanyID AND CustomerAuthRule IS NOT NULL AND ServiceID = 0
		UNION
		SELECT DISTINCT VendorAuthRule FROM NeonRMDev.tblAccountAuthenticate aa WHERE CompanyID = p_CompanyID AND VendorAuthRule IS NOT NULL AND ServiceID = 0;

		CALL prc_ApplyAuthRule(p_CompanyID,p_CompanyGatewayID,0);

	END IF;

	CALL prc_ApplyAuthRule(p_CompanyID,p_CompanyGatewayID,0);

	UPDATE tblGatewayAccount
	INNER JOIN tmp_ActiveAccount a
		ON a.AccountName = tblGatewayAccount.AccountName
		AND a.AccountNumber = tblGatewayAccount.AccountNumber
		AND a.AccountCLI = tblGatewayAccount.AccountCLI
		AND a.AccountIP = tblGatewayAccount.AccountIP
		AND tblGatewayAccount.CompanyGatewayID = p_CompanyGatewayID
		AND tblGatewayAccount.ServiceID = a.ServiceID
	SET tblGatewayAccount.AccountID = a.AccountID
	WHERE tblGatewayAccount.AccountID IS NULL;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END