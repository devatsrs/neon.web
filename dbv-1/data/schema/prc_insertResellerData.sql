CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_insertResellerData`(
	IN `p_companyid` INT,
	IN `p_childcompanyid` INT,
	IN `p_accountname` VARCHAR(100),
	IN `p_firstname` VARCHAR(100),
	IN `p_lastname` VARCHAR(100),
	IN `p_accountid` INT,
	IN `p_email` VARCHAR(100),
	IN `p_password` TEXT,
	IN `p_is_product` INT,
	IN `p_product` TEXT,
	IN `p_is_subscription` INT,
	IN `p_subscription` TEXT,
	IN `p_is_trunk` INT,
	IN `p_trunk` TEXT,
	IN `p_allowwhitelabel` INT
)
BEGIN

	DECLARE companycodedeckid int;
	DECLARE resellercodedeckid int;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
		SELECT @p2 as Message;
		-- ROLLBACK;
	END;		

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_currency;
	CREATE TEMPORARY TABLE tmp_currency (
		`CompanyId` INT,
		`Code` VARCHAR(50),
		`CurrencyID` INT,
		`NewCurrencyID` INT
	) ENGINE=InnoDB;

	-- START TRANSACTION;

	INSERT INTO	tblUser(CompanyID,FirstName,LastName,EmailAddress,password,AdminUser,updated_at,created_at,created_by,Status,JobNotification)	
	SELECT p_childcompanyid as CompanyID,p_firstname as FirstName,p_lastname as LastName , p_email as EmailAddress,p_password as password, 1 as AdminUser, Now(),Now(),'system' as created_by, '1' as Status, '1' as JobNotification;

	INSERT INTO tblEmailTemplate (CompanyID,TemplateName,Subject,TemplateBody,created_at,CreatedBy,updated_at,`Type`,EmailFrom,StaticType,SystemType,Status,StatusDisabled,TicketTemplate)
	SELECT DISTINCT p_childcompanyid as `CompanyID`,TemplateName,Subject,TemplateBody,NOW(),'system' as CreatedBy,NOW(),`Type`, p_email as `EmailFrom`,StaticType,SystemType,Status,StatusDisabled,TicketTemplate	
	FROM tblEmailTemplate
	WHERE StaticType=1 AND CompanyID = p_companyid ;

	INSERT INTO tblCompanyConfiguration (`CompanyID`,`Key`,`Value`)
	SELECT DISTINCT p_childcompanyid as `CompanyID`,`Key`,`Value`	
	FROM tblCompanyConfiguration
	WHERE CompanyID = p_companyid;

	INSERT INTO tblCronJobCommand (`CompanyID`,GatewayID,Title,Command,Settings,Status,created_at,created_by)
	SELECT DISTINCT p_childcompanyid as `CompanyID`,GatewayID,Title,Command,Settings,Status,created_at,created_by	
	FROM tblCronJobCommand
	WHERE CompanyID = p_companyid;

	INSERT INTO tblTaxRate (CompanyId,Title,Amount,TaxType,FlatStatus,Status,created_at,updated_at)
	SELECT DISTINCT p_childcompanyid as `CompanyId`,Title,Amount,TaxType,FlatStatus,Status,NOW(),NOW()
	FROM tblTaxRate
	WHERE CompanyId = p_companyid;

	/*
	INSERT INTO tblCurrency (CompanyId,Code,Description,Status,created_at,updated_at,Symbol)
	SELECT DISTINCT p_childcompanyid as `CompanyId` ,Code,Description,Status,NOW(),NOW(),Symbol
	FROM tblCurrency
	WHERE CompanyId = p_companyid; */

	IF p_is_product =1
	THEN	

		INSERT INTO NeonBillingDev.tblProduct (CompanyId,Name,Code,Description,Amount,Active,Note,CreatedBy,ModifiedBy,created_at,updated_at)
		SELECT DISTINCT p_childcompanyid as `CompanyId`,Name,Code,Description,Amount,Active,Note,'system' as CreatedBy,'system' as ModifiedBy,NOW(),NOW()
		FROM NeonBillingDev.tblProduct
		WHERE CompanyId = p_companyid AND FIND_IN_SET(ProductID,p_product);

	END IF;

	IF p_is_subscription = 1
	THEN

		/*
		INSERT INTO	tmp_currency(CompanyId,Code,CurrencyID)	
		SELECT p_companyid as CompanyId,Code, CurrencyId FROM `tblCurrency` WHERE CompanyId	= p_companyid;	

		UPDATE tmp_currency tc LEFT JOIN tblCurrency c ON tc.Code=c.Code AND c.CompanyId = p_childcompanyid
		set NewCurrencyID = c.CurrencyId
		WHERE c.CurrencyId IS NOT NULL;

		*/

		INSERT INTO NeonBillingDev.tblBillingSubscription(`CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,created_at,updated_at,ModifiedBy,CreatedBy,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance)
		SELECT DISTINCT p_childcompanyid as `CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,created_at,updated_at,ModifiedBy,CreatedBy,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance
		FROM NeonBillingDev.tblBillingSubscription
		WHERE CompanyID = p_companyid AND FIND_IN_SET(SubscriptionID,p_subscription);

	END IF;

	/*
	IF p_is_trunk =1
	THEN

	INSERT INTO tblTrunk (Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status,created_at,updated_at)
	SELECT DISTINCT Trunk, p_childcompanyid as `CompanyId`,RatePrefix,AreaPrefix,`Prefix`,Status,NOW(),NOW()
	FROM tblTrunk
	WHERE CompanyId = p_companyid AND FIND_IN_SET(TrunkID,p_trunk);

	END IF;
	*/

	INSERT INTO tblReseller(ResellerName,CompanyID,ChildCompanyID,AccountID,FirstName,LastName,Email,Password,Status,AllowWhiteLabel,created_at,updated_at,created_by)
	SELECT p_accountname as ResellerName,p_companyid as CompanyID,p_childcompanyid as ChildCompanyID,p_accountid as AccountID,p_firstname as FirstName,p_lastname as LastName,p_email as Email,p_password as Password,'1' as Status,p_allowwhitelabel as AllowWhiteLabel,Now(),Now(),'system' as created_by;

	INSERT INTO tblCompanySetting(`CompanyID`,`Key`,`Value`)
	SELECT p_childcompanyid as `CompanyID`,`Key`,`Value` 
	FROM tblCompanySetting
	WHERE CompanyID = p_companyid AND `Key`='RoundChargesAmount';

	SELECT CodeDeckId INTO companycodedeckid  FROM tblCodeDeck WHERE CompanyId=p_companyid AND DefaultCodedeck=1;

	IF companycodedeckid > 0 THEN
		INSERT INTO tblCodeDeck (CompanyId, CodeDeckName, created_at, CreatedBy, updated_at, ModifiedBy, `Type`, DefaultCodedeck) 
		VALUES (p_childcompanyid, 'Default Codedeck', Now(), 'Dev', Now(), NULL, NULL, 1);

		SELECT CodeDeckId INTO resellercodedeckid  FROM tblCodeDeck WHERE CompanyId=p_childcompanyid AND DefaultCodedeck=1;

		INSERT INTO tblRate(CountryID,CompanyID,CodeDeckId,Code,Description,Interval1,IntervalN,created_at)
		SELECT CountryID,p_childcompanyid as CompanyID,resellercodedeckid as CodeDeckId,Code,Description,Interval1,IntervalN,Now() as created_at FROM tblRate where CodeDeckId=companycodedeckid;


	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END