USE `speakintelligentRM`;

DROP PROCEDURE IF EXISTS `prc_FindApiInBoundPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_FindApiInBoundPrefix`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_cli` VARCHAR(200),
	IN `p_cld` VARCHAR(200),
	IN `p_City` VARCHAR(200),
	IN `p_Tariff` VARCHAR(50),
	IN `p_OriginType` VARCHAR(50),
	IN `p_OriginProvider` VARCHAR(50),
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Type` VARCHAR(50)
)
BEGIN

	DECLARE v_codedeckid_ INT;
	DECLARE v_ratetableid_ INT;
	DECLARE v_CompanyID_ INT;
	DECLARE v_Count_ INT;
	DECLARE v_Count1_ INT;
	DECLARE v_Count2_ INT;

		SELECT
			CodeDeckId,
			RateTableId
		INTO
			v_codedeckid_,
			v_ratetableid_
		FROM tblRateTable
		WHERE RateTableId = p_RateTableID;

	DROP TEMPORARY TABLE IF EXISTS tmp_codes;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_codes(
		RateID INT,
		Code varchar(50)
	);
	
	INSERT INTO tmp_codes
	SELECT RateID,
		Code
	FROM tblRate
	WHERE CodeDeckId = v_codedeckid_;
	

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate_(
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		AccessType varchar(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_(
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		AccessType varchar(50)
	);
	
	INSERT INTO tmp_RateTableRate_
	SELECT 
		RateTableDIDRateID,
		OriginationRateID,
		RateID,
		'Other' as OriginationCode,
		'Other' as DestincationCode,
		IFNULL(City,'') as City,
		IFNULL(Tariff,'') as Tariff,
		IFNULL(AccessType,'') as AccessType
	FROM tblRateTableDIDRate
	WHERE RateTableId = p_RateTableID
		AND TimezonesID = p_TimezonesID
		AND EffectiveDate <= NOW()
		AND ApprovedStatus =1
		;
		
		UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.RateID=c.RateID
	 SET DestincationCode = c.Code; 	
	 UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.OriginationRateID=c.RateID
	 SET OriginationCode = c.Code;

	/** Both mathc cld-> destination code , cli -> origination code */
	
	IF (p_OriginType != '' OR p_OriginProvider != '')
	THEN		
		
	INSERT INTO tmp_RateTableRate2_
	SELECT * FROM tmp_RateTableRate_
	WHERE p_cli REGEXP "^[0-9]+$"
			AND (OriginationCode like  CONCAT("%",p_OriginType,"%") && OriginationCode like CONCAT("%",p_OriginProvider,"%"))			
			AND p_cld REGEXP "^[0-9]+$"
			-- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = p_City
			AND Tariff = p_Tariff
			AND AccessType = p_Type
			;
	
	END IF;		
			
	SELECT COUNT(*) into v_Count_ from tmp_RateTableRate2_;


	/** if not found record above , we only match on cld->destincation code */
	
	IF v_Count_ = 0
	THEN 
	
		INSERT INTO tmp_RateTableRate2_
		SELECT * FROM tmp_RateTableRate_
		WHERE OriginationCode ='Other'
			AND p_cld REGEXP "^[0-9]+$"
		 -- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = p_City
			AND Tariff = p_Tariff
			AND AccessType = p_Type
				;
				
		SELECT COUNT(*) into v_Count1_ from tmp_RateTableRate2_;
		
	ELSE
	
		SET v_Count1_=v_Count_;
		
	END IF;
	
	/*
	
	IF v_Count1_ = 0
	THEN
	
		INSERT INTO tmp_RateTableRate2_
		SELECT * FROM tmp_RateTableRate_
		WHERE OriginationCode ='Other'
			AND p_cld REGEXP "^[0-9]+$"
			-- AND p_cld like  CONCAT(DestincationCode,"%")
			AND DestincationCode = p_AreaPrefix
			AND City = ''
			AND Tariff = ''
			;
				
		SELECT COUNT(*) into v_Count2_ from tmp_RateTableRate2_;
		SET v_Count1_=v_Count2_;
	
	
	END IF;

	*/
	
	IF v_Count1_ > 0
	THEN
		SELECT * FROM tmp_RateTableRate2_ ORDER BY LENGTH(DestincationCode) DESC LIMIT 1; 
	END IF;	

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_FindApiOutBoundPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_FindApiOutBoundPrefix`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_cli` VARCHAR(200),
	IN `p_cld` VARCHAR(200)
)
BEGIN

	DECLARE v_codedeckid_ INT;
	DECLARE v_ratetableid_ INT;
	DECLARE v_CompanyID_ INT;
	DECLARE v_Count_ INT;
	DECLARE v_Count1_ INT;

		SELECT
			CodeDeckId,
			RateTableId
		INTO
			v_codedeckid_,
			v_ratetableid_
		FROM tblRateTable
		WHERE RateTableId = p_RateTableID;

	DROP TEMPORARY TABLE IF EXISTS tmp_codes;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_codes(
		RateID int,
		Code varchar(50)
	);
	
	INSERT INTO tmp_codes
	SELECT RateID,
	Code
	FROM tblRate
	WHERE CodeDeckId = v_codedeckid_;
	

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate_(
		RateTableRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_(
		RateTableRateID INT,
		OriginationRateID INT,
		RateID INT,
		OriginationCode VARCHAR(50),
		DestincationCode VARCHAR(50)
	);
	
	INSERT INTO tmp_RateTableRate_
	SELECT 
		RateTableRateID,
		OriginationRateID,
		RateID,
		'Other' as OriginationCode,
		'Other' as DestincationCode
	FROM tblRateTableRate
	WHERE RateTableId = p_RateTableID
		AND TimezonesID = p_TimezonesID
		AND EffectiveDate <= NOW()
		AND ApprovedStatus=1
		;
		
	UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.RateID=c.RateID
	 SET DestincationCode = c.Code; 	
	 
	 UPDATE tmp_RateTableRate_ t INNER JOIN tmp_codes c ON t.OriginationRateID=c.RateID
	 SET OriginationCode = c.Code;
		
	/** Both mathc cld-> destination code , cli -> origination code */
		
	INSERT INTO tmp_RateTableRate2_
	select * from tmp_RateTableRate_
	where p_cli REGEXP "^[0-9]+$"
			AND p_cli like  CONCAT(OriginationCode,"%")			
			AND p_cld REGEXP "^[0-9]+$"
			AND p_cld like  CONCAT(DestincationCode,"%");
			
	SELECT COUNT(*) into v_Count_ from tmp_RateTableRate2_;
	
	/** if not found record above , we only match on cld->destincation code */
		
	IF v_Count_ = 0
	THEN 
	
		INSERT INTO tmp_RateTableRate2_
		select * from tmp_RateTableRate_
		where OriginationCode ='Other'
				AND p_cld REGEXP "^[0-9]+$"
				AND p_cld like  CONCAT(DestincationCode,"%");
				
		SELECT COUNT(*) into v_Count1_ from tmp_RateTableRate2_;
		
	ELSE
	
		SET v_Count1_=v_Count_;
		
	END IF;

	IF v_Count1_ > 0
	THEN
		SELECT * FROM tmp_RateTableRate2_ order by length(DestincationCode) desc limit 1; 
	END IF;
	
END//
DELIMITER ;

-- 13-09-2019 Prepaid Task Changes

ALTER TABLE `tblServiceBilling`
	ADD COLUMN `CLIRateTableID` INT(11) NOT NULL DEFAULT '0' AFTER `AccountSubscriptionID`;

DROP PROCEDURE IF EXISTS `prcGetAccountServiceNumberData`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prcGetAccountServiceNumberData`(
	IN `p_AccountServiceID` INT,
	IN `p_CLIRateTableID` INT
)
BEGIN

  DECLARE v_count INT DEFAULT 0;
  DECLARE v_count_1 INT DEFAULT 0;
  DECLARE v_count_2 INT  DEFAULT 0;
  DECLARE v_rowCount_ INT;
  DECLARE v_pointer_ INT;	
  DECLARE v_pkgcount INT DEFAULT 0;
  DECLARE v_pkgcount_1 INT DEFAULT 0;
  DECLARE v_pkgcount_2 INT DEFAULT 0;
  DECLARE v_SpecialRateTableID INT DEFAULT 0;
  DECLARE v_RateTableID INT DEFAULT 0;
  DECLARE v_AccountServicePackageID INT DEFAULT 0;
  DECLARE v_CountryID INT; 
  DECLARE v_NoType VARCHAR(200);
  DECLARE v_City VARCHAR(50);
  DECLARE v_Tariff VARCHAR(50);
  DECLARE v_Prefix VARCHAR(50);	
  DECLARE v_CLIRateTableID INT;
  DECLARE v_CLI VARCHAR(50);
  DECLARE v_pkgSpecialRateTableID INT DEFAULT 0;
  DECLARE v_pkgRateTableID INT DEFAULT 0;
  DECLARE v_packageName VARCHAR(50);
  DECLARE v_PackageId INT;
  DECLARE v_CompanyID INT;
  DECLARE v_AccountID INT;
  DECLARE v_CompanyCurrency INT;
  DECLARE v_AccountCurrency INT;
  
  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  SELECT ac.AccountID,a.CompanyId,IFNULL(a.CurrencyId,0),IFNULL(c.CurrencyId,0) INTO v_AccountID,v_CompanyID,v_AccountCurrency,v_CompanyCurrency
  FROM tblAccountService ac 
    INNER JOIN tblAccount a ON a.AccountID=ac.AccountID
    INNER JOIN tblCompany c ON c.CompanyID=a.CompanyId
  WHERE ac.AccountServiceID = p_AccountServiceID;

  DROP TEMPORARY TABLE IF EXISTS tmp_NumberServices_;
  CREATE TEMPORARY TABLE tmp_NumberServices_ (
	RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	CLIRateTableID INT,
	CLI VARCHAR(50),
	AccountServicePackageID INT,
	RateTableID INT,
	SpecialRateTableID INT,
	PackageRateTableID INT,
	SpecialPackageRateTableID INT,
	NoType VARCHAR(200),
	CountryID INT,
	City VARCHAR(50),
	Tariff VARCHAR(50),
	Prefix VARCHAR(50)
  );

  DROP TEMPORARY TABLE IF EXISTS tmp_clidata_;
  CREATE TEMPORARY TABLE tmp_clidata_ (
	RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	CLIRateTableID INT,
	CLI VARCHAR(50),
	AccountServicePackageID INT,
	OneOffCost DECIMAL(18,6),
	MonthlyCost DECIMAL(18,6),
	RegistrationCostPerNumber DECIMAL(18,6),
	OneOffCostCurrency INT,
	MonthlyCostCurrency INT,
    RegistrationCostPerNumberCurrency INT
  );

  DROP TEMPORARY TABLE IF EXISTS tmp_clidata_1;
  CREATE TEMPORARY TABLE tmp_clidata_1 (
	RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	RateTablePKGRateID INT,
	CLIRateTableID INT,
	CLI VARCHAR(50),
	AccountServicePackageID INT,
	OneOffCost DECIMAL(18,6),
	MonthlyCost DECIMAL(18,6),
	OneOffCostCurrency INT,
	MonthlyCostCurrency INT
  );

  DROP TEMPORARY TABLE IF EXISTS tmp_all;
  CREATE TEMPORARY TABLE tmp_all(
	CLIRateTableID INT,
	CLI VARCHAR(50),
	AccountServicePackageID INT,
	OneOffCost DECIMAL(18,6),
	MonthlyCost DECIMAL(18,6),
	RegistrationCostPerNumber DECIMAL(18,6),
	PKGOneOffCost DECIMAL(18,6),
	PKGMonthlyCost DECIMAL(18,6)
  );

  DROP TEMPORARY TABLE IF EXISTS tmp_data_1;
  CREATE TEMPORARY TABLE tmp_data_1(
	CLIRateTableID INT,
	CLI VARCHAR(50),
	AccountServicePackageID INT,
	PKGOneOffCost DECIMAL(18,6),
	PKGMonthlyCost DECIMAL(18,6)
  );

  DROP TEMPORARY TABLE IF EXISTS tmp_data_2;
  CREATE TEMPORARY TABLE tmp_data_2(
	CLIRateTableID INT,
	CLI VARCHAR(50),
	AccountServicePackageID INT,
	OneOffCost DECIMAL(18,6),
	MonthlyCost DECIMAL(18,6),
	RegistrationCostPerNumber DECIMAL(18,6)
  );	
    
  INSERT INTO tmp_NumberServices_(CLIRateTableID,CLI,AccountServicePackageID,RateTableID,SpecialRateTableID,PackageRateTableID,SpecialPackageRateTableID,NoType,CountryID,City,Tariff,Prefix)
  SELECT CLIRateTableID,CLI,AccountServicePackageID,RateTableID,SpecialRateTableID,0,0,NoType,CountryID,City,Tariff,Prefix
  FROM tblCLIRateTable 
  WHERE AccountServiceID=p_AccountServiceID AND CLIRateTableID = p_CLIRateTableID;
    
  SELECT COUNT(*) INTO v_count  from tmp_NumberServices_;
    
  IF(v_count > 0)
  THEN
    
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_NumberServices_);
	
	WHILE v_pointer_ <= v_rowCount_
	DO
	  SELECT CLIRateTableID,CLI,SpecialRateTableID,RateTableID,AccountServicePackageID,IFNULL(NoType,''),IFNULL(City,''),IFNULL(Tariff,''),Prefix,IFNULL(CountryID,0) 
	  INTO v_CLIRateTableID,v_CLI,v_SpecialRateTableID,v_RateTableID,v_AccountServicePackageID,v_NoType,v_City,v_Tariff,v_Prefix,v_CountryID 
	  FROM tmp_NumberServices_ t 
	  WHERE t.RowID = v_pointer_;
			
      INSERT INTO tmp_all(CLIRateTableID,CLI,AccountServicePackageID,MonthlyCost,OneOffCost,RegistrationCostPerNumber,PKGOneOffCost,PKGMonthlyCost)
	  VALUES(v_CLIRateTableID,v_CLI,v_AccountServicePackageID,0,0,0,0,0);
			
	  IF(v_SpecialRateTableID > 0)
	  THEN
		INSERT INTO tmp_clidata_(CLIRateTableID,CLI,AccountServicePackageID,OneOffCost,MonthlyCost,RegistrationCostPerNumber,OneOffCostCurrency,MonthlyCostCurrency,RegistrationCostPerNumberCurrency)
		SELECT v_CLIRateTableID,v_CLI,v_AccountServicePackageID,IFNULL(OneOffCost,0),IFNULL(MonthlyCost,0),IFNULL(RegistrationCostPerNumber,0),IFNULL(OneOffCostCurrency,0),IFNULL(MonthlyCostCurrency,0),IFNULL(RegistrationCostPerNumberCurrency,0)
		FROM tblRateTableDIDRate didRate
		  INNER JOIN tblRate rate ON rate.RateID = didRate.RateID
		  INNER JOIN tblTimezones ratetimeZone ON ratetimeZone.TimezonesID = didRate.TimezonesID AND rate.CountryID = v_CountryID
		WHERE RateTableID = v_SpecialRateTableID AND
		  IFNULL(rate.Code,'') = v_Prefix AND
		  IFNULL(didRate.AccessType,'')=v_NoType AND
		  IFNULL(didRate.City,'') = v_City AND
		  IFNULL(didRate.Tariff,'') = v_Tariff
		;
		  
		SELECT COUNT(*) INTO v_count_2 FROM tmp_clidata_ WHERE CLIRateTableID=v_CLIRateTableID ;
				
	  END IF; -- special rate table id over

	  IF(v_RateTableID > 0 && v_count_2 = 0)
      THEN				
		INSERT INTO tmp_clidata_(CLIRateTableID,CLI,AccountServicePackageID,OneOffCost,MonthlyCost,RegistrationCostPerNumber,OneOffCostCurrency,MonthlyCostCurrency,RegistrationCostPerNumberCurrency)
		SELECT v_CLIRateTableID,v_CLI,v_AccountServicePackageID,IFNULL(OneOffCost,0),IFNULL(MonthlyCost,0),IFNULL(RegistrationCostPerNumber,0),IFNULL(OneOffCostCurrency,0),IFNULL(MonthlyCostCurrency,0),IFNULL(RegistrationCostPerNumberCurrency,0)
		FROM tblRateTableDIDRate didRate
		  INNER JOIN tblRate rate ON rate.RateID = didRate.RateID
		  INNER JOIN tblTimezones ratetimeZone ON ratetimeZone.TimezonesID = didRate.TimezonesID AND rate.CountryID = v_CountryID
		WHERE RateTableID = v_RateTableID AND
		  IFNULL(rate.Code,'') = v_Prefix AND
		  IFNULL(didRate.AccessType,'')=v_NoType AND
		  IFNULL(didRate.City,'') = v_City AND
		  IFNULL(didRate.Tariff,'') = v_Tariff
		;			  
  	  END IF;	--  regular rate table id over	
			
	  SELECT COUNT(*) INTO v_count_1 FROM tmp_clidata_;
	
	  IF(v_count_1 > 0)
	  THEN	
	    /* Sum of all timezones of prefix */
		INSERT INTO tmp_data_2(CLIRateTableID,CLI,AccountServicePackageID,MonthlyCost,OneOffCost,RegistrationCostPerNumber)
		SELECT CLIRateTableID,CLI,AccountServicePackageID,SUM(MonthlyCost),SUM(OneOffCost),SUM(RegistrationCostPerNumber) 
		FROM(
		      SELECT 
			    RowID,
			    CLIRateTableID,
				CLI,
				AccountServicePackageID,
				`FnConvertCurrencyRate`(v_CompanyCurrency,v_AccountCurrency,MonthlyCostCurrency,MonthlyCost) as MonthlyCost,
				`FnConvertCurrencyRate`(v_CompanyCurrency,v_AccountCurrency,OneOffCostCurrency,OneOffCost) as OneOffCost,
				`FnConvertCurrencyRate`(v_CompanyCurrency,v_AccountCurrency,RegistrationCostPerNumberCurrency,RegistrationCostPerNumber) as RegistrationCostPerNumber
			  FROM tmp_clidata_ 
			  WHERE CLIRateTableID=v_CLIRateTableID
			) AS tbl 
		GROUP BY CLIRateTableID,CLI,AccountServicePackageID;
			
	  END IF;
			
	  -- Number data over
	  -- Package data start
			
	  IF(v_AccountServicePackageID > 0)
	  THEN
		SELECT SpecialPackageRateTableID,RateTableID,PackageId INTO v_pkgSpecialRateTableID,v_pkgRateTableID,v_PackageId 
		FROM tblAccountServicePackage 
		WHERE AccountServicePackageID=v_AccountServicePackageID;
		/* Special Package Rate Table Check */
		IF(v_pkgSpecialRateTableID > 0)
		THEN			
		  SELECT name INTO v_packageName FROM tblPackage WHERE PackageId = v_PackageId;
					
		  INSERT INTO tmp_clidata_1(RateTablePKGRateID,CLIRateTableID,CLI,AccountServicePackageID,OneOffCost,MonthlyCost,OneOffCostCurrency,MonthlyCostCurrency)
		  SELECT RateTablePKGRateID,v_CLIRateTableID,v_CLI,v_AccountServicePackageID,IFNULL(OneOffCost,0),IFNULL(MonthlyCost,0),IFNULL(OneOffCostCurrency,0),IFNULL(MonthlyCostCurrency,0)
		  FROM tblRateTablePKGRate pkgRate
		    INNER JOIN tblRate rate ON rate.RateID = pkgRate.RateID									 
		  WHERE rate.Code = v_packageName AND RateTableID = v_pkgSpecialRateTableID;
					
		  SELECT COUNT(*) INTO v_pkgcount FROM tmp_clidata_1 WHERE CLIRateTableID = v_CLIRateTableID;
				
		END IF; -- package special rate table
	
        /* If Special Package Rate Table Rate not found,it will check Default Rate Table */	
		IF(v_pkgRateTableID > 0 && v_pkgcount = 0)
		THEN		
		  SELECT name INTO v_packageName FROM tblPackage WHERE PackageId = v_PackageId;
			
			INSERT INTO tmp_clidata_1(RateTablePKGRateID,CLIRateTableID,CLI,AccountServicePackageID,OneOffCost,MonthlyCost,OneOffCostCurrency,MonthlyCostCurrency)
			SELECT RateTablePKGRateID,v_CLIRateTableID,v_CLI,v_AccountServicePackageID,IFNULL(OneOffCost,0),IFNULL(MonthlyCost,0),IFNULL(OneOffCostCurrency,0),IFNULL(MonthlyCostCurrency,0)
			FROM tblRateTablePKGRate pkgRate
			INNER JOIN tblRate rate on rate.RateID = pkgRate.RateID									 
			WHERE rate.Code = v_packageName and RateTableID = v_pkgRateTableID;
		
		END IF; -- Package DEFAULT Rate Table
				
		SELECT COUNT(*) INTO v_pkgcount_1 FROM tmp_clidata_1 WHERE CLIRateTableID = v_CLIRateTableID;
				
		IF(v_pkgcount_1 > 0)
		THEN 			
		  INSERT INTO tmp_data_1(CLIRateTableID,CLI,AccountServicePackageID,PKGMonthlyCost,PKGOneOffCost)
		  SELECT CLIRateTableID,CLI,AccountServicePackageID,SUM(MonthlyCost) AS PKGMonthlyCost,SUM(OneOffCost) AS PKGOneOffCost
		  FROM(
				SELECT 
				  RateTablePKGRateID,
  				  CLIRateTableID,
				  CLI,
				  AccountServicePackageID,
				  `FnConvertCurrencyRate`(v_CompanyCurrency,v_AccountCurrency,MonthlyCostCurrency,MonthlyCost) as MonthlyCost,
				  `FnConvertCurrencyRate`(v_CompanyCurrency,v_AccountCurrency,OneOffCostCurrency,OneOffCost) as OneOffCost
				FROM tmp_clidata_1 
				WHERE CLIRateTableID = v_CLIRateTableID
			  ) AS tbl
		  GROUP BY CLIRateTableID,CLI,AccountServicePackageID;
			
		END IF;
	  END IF; -- Account Service Package Over
						
	  SET v_pointer_ = v_pointer_ + 1;
			
	END WHILE; -- loop over
		
	UPDATE tmp_all t1 
	  INNER JOIN tmp_data_2 t2 ON t1.CLIRateTableID = t2.CLIRateTableID AND t1.CLI = t2.CLI AND t1.AccountServicePackageID = t2.AccountServicePackageID		
	  SET t1.MonthlyCost = t2.MonthlyCost,t1.OneOffCost = t2.OneOffCost,t1.RegistrationCostPerNumber = t2.RegistrationCostPerNumber
	;
		
	UPDATE tmp_all t1 
	  INNER JOIN tmp_data_1 t2 ON t1.CLIRateTableID = t2.CLIRateTableID AND t1.CLI = t2.CLI AND t1.AccountServicePackageID = t2.AccountServicePackageID		
	  SET t1.PKGMonthlyCost = t2.PKGMonthlyCost,t1.PKGOneOffCost = t2.PKGOneOffCost
	;
		
  END IF;	
  
  SELECT * FROM tmp_all;
	
  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getPrepaidUnbilledReport`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getPrepaidUnbilledReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Detail` INT,
	IN `p_Type` VARCHAR(50),
	IN `p_Description` VARCHAR(50)
)
BEGIN
	
	DECLARE v_Round_ INT;
	DECLARE v_AccountBalanceLogID_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	SELECT AccountBalanceLogID INTO v_AccountBalanceLogID_ FROM tblAccountBalanceLog WHERE AccountID=p_AccountID;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Account;
	CREATE TEMPORARY TABLE tmp_Account (
		AccountID INT,	
		`Type` VARCHAR(25),
		Description VARCHAR(255),
		Period VARCHAR(255),
		Amount NUMERIC(18, 8),
		IssueDate DATETIME,
		created_at DATETIME
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_TopUp;
	CREATE TEMPORARY TABLE tmp_TopUp(
		AccountID INT,
		InvoiceID INT,
		Description VARCHAR(255),
		InvoiceAmount NUMERIC(18, 8),
		TotalTax NUMERIC(18, 8),
		TotalAmount NUMERIC(18, 8),
		IssueDate DATETIME,
		created_at DATETIME		
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_OutPayment;
	CREATE TEMPORARY TABLE tmp_OutPayment(
		AccountID INT,
		InvoiceID INT,
		Description VARCHAR(255),
		InvoiceAmount NUMERIC(18, 8),
		TotalTax NUMERIC(18, 8),
		TotalAmount NUMERIC(18, 8),
		IssueDate DATETIME,
		created_at DATETIME		
	);
	
	INSERT INTO tmp_TopUp(AccountID,InvoiceID,Description,InvoiceAmount,TotalTax,TotalAmount,IssueDate,created_at)
	SELECT
		i.AccountID,
		i.InvoiceID,
		id.Description,
		id.LineTotal AS InvoiceAmount,
		IFNULL(SUM(r.TaxAmount),0) AS TotalTax,
		(id.LineTotal + (IFNULL(SUM(r.TaxAmount),0))) AS TotalAmount,
		i.IssueDate,
		i.created_at
	FROM speakintelligentBilling.tblInvoiceDetail id 
			INNER JOIN speakintelligentBilling.tblInvoice i ON id.InvoiceID=i.InvoiceID					
			INNER JOIN speakintelligentBilling.tblProduct p ON id.ProductID=p.ProductID AND p.Code='topup'
			LEFT JOIN speakintelligentBilling.tblInvoiceTaxRate r ON id.InvoiceDetailID = r.InvoiceDetailID
	WHERE i.AccountID = p_AccountID
		AND ( (i.InvoiceType = 2) OR ( i.InvoiceType = 1 AND i.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
		AND i.IssueDate BETWEEN p_StartDate AND p_EndDate
		GROUP BY id.InvoiceDetailID
		;
		
	INSERT INTO tmp_OutPayment(AccountID,InvoiceID,Description,InvoiceAmount,TotalTax,TotalAmount,IssueDate,created_at)
	SELECT
		i.AccountID,
		i.InvoiceID,
		IF(i.InvoiceStatus = "paid", 'Paid', 'Awaiting Approval'),
		id.LineTotal AS InvoiceAmount,
		IFNULL(SUM(r.TaxAmount),0) AS TotalTax,
		(id.LineTotal + (IFNULL(SUM(r.TaxAmount),0))) AS TotalAmount,
		i.IssueDate,
		i.created_at
	FROM speakintelligentBilling.tblInvoiceDetail id 
			INNER JOIN speakintelligentBilling.tblInvoice i ON id.InvoiceID=i.InvoiceID					
			INNER JOIN speakintelligentBilling.tblProduct p ON id.ProductID=p.ProductID AND p.Code='outpayment'
			LEFT JOIN speakintelligentBilling.tblInvoiceTaxRate r ON id.InvoiceDetailID = r.InvoiceDetailID
	WHERE i.AccountID = p_AccountID
		AND ( (i.InvoiceType = 2) OR ( i.InvoiceType = 1 AND i.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
		AND i.IssueDate BETWEEN p_StartDate AND p_EndDate
		GROUP BY id.InvoiceDetailID
		;				
	
	INSERT INTO tmp_Account(AccountID,TYPE,Description,Period,Amount,IssueDate,created_at)
	SELECT p_AccountID AS AccountID,
			'Usage' AS `Type`,
			'Usage' AS Description,
			CONCAT(DATE,' - ',DATE_FORMAT(DATE ,"%Y-%m-%d 23:59:59")) AS Period,
			TotalAmount AS Amount,
			DATE AS IssueDate,
			created_at
	FROM tblAccountBalanceUsageLog
	WHERE AccountBalanceLogID = v_AccountBalanceLogID_ 
			AND DATE BETWEEN p_StartDate AND p_EndDate;			
	
	INSERT INTO tmp_Account(AccountID,TYPE,Description,Period,Amount,IssueDate,created_at)
	SELECT p_AccountID AS AccountID,
			'Subscription' AS `Type`,
			Description,
			CONCAT(StartDate,' - ',EndDate) AS Period,
			TotalAmount AS Amount,
			IssueDate,
			created_at
	FROM tblAccountBalanceSubscriptionLog 
	WHERE AccountBalanceLogID = v_AccountBalanceLogID_ 
			AND IssueDate BETWEEN p_StartDate AND p_EndDate
			AND ProductType IN(3,8,11);	
	
	INSERT INTO tmp_Account(AccountID,TYPE,Description,Period,Amount,IssueDate,created_at)
	SELECT p_AccountID AS AccountID,
			'PRS Earnings',
			'Awaiting Approval' AS Description,
			CONCAT(`Date`,' - ',`Date`) AS Period,
			Amount,
			`Date` AS IssueDate,
			created_at
	FROM tblOutPaymentLog 
	WHERE AccountID = p_AccountID 
	AND `Date` BETWEEN p_StartDate AND p_EndDate;
	
	INSERT INTO tmp_Account(AccountID,TYPE,Description,Period,Amount,IssueDate,created_at)
	SELECT p_AccountID AS AccountID,
			'PRS Earnings',
			'Approved' AS Description,
			CONCAT(`StartDate`,' - ',`EndDate`) AS Period,
			Amount,
			`created_at` AS IssueDate,
			created_at
	FROM tblApprovedOutPaymentLog 
	WHERE AccountID = p_AccountID 
	AND `created_at` BETWEEN p_StartDate AND p_EndDate;
	
	INSERT INTO tmp_Account(AccountID,TYPE,Description,Period,Amount,IssueDate,created_at)
	SELECT p_AccountID AS AccountID,
			'Oneofcharge' AS `Type`,
			Description,
			CONCAT(StartDate,' - ',EndDate) AS Period,
			TotalAmount AS Amount,
			IssueDate,
			created_at
	FROM tblAccountBalanceSubscriptionLog 
	WHERE AccountBalanceLogID = v_AccountBalanceLogID_ 
			AND IssueDate BETWEEN p_StartDate AND p_EndDate
			AND ProductType IN(4,9,10,12);
			
	INSERT INTO tmp_Account(AccountID,TYPE,Description,Period,Amount,IssueDate,created_at)
	SELECT p_AccountID AS AccountID,
			'TopUp' AS `Type`,
			Description,
			CONCAT(IssueDate,' - ',IssueDate) AS Period,
			TotalAmount AS Amount,
			IssueDate,
			created_at		
	FROM	tmp_TopUp;	
	
	INSERT INTO tmp_Account(AccountID,TYPE,Description,Period,Amount,IssueDate,created_at)
	SELECT p_AccountID AS AccountID,
			'Out Payment' AS `Type`,
			Description,
			'' AS Period,
			TotalAmount AS Amount,
			IssueDate,
			created_at		
	FROM	tmp_OutPayment;
	
	SELECT * FROM tmp_Account
	WHERE
	(p_Type = '' OR `Type` = p_Type) AND
	(p_Description = '' OR Description LIKE CONCAT('%', p_Description , '%'))
	ORDER BY IssueDate DESC;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FnConvertCurrencyRate`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` FUNCTION `FnConvertCurrencyRate`(
  `p_CompanyCurrency` INT,
  `p_AccountCurrency` INT,
  `p_FileCurrency` INT,
  `p_Rate` DECIMAL(18,6)
) RETURNS decimal(18,6)
BEGIN

  DECLARE V_NewRate DECIMAL(18,6) DEFAULT 0;
  DECLARE V_ConversionRate DECIMAL(18,6) DEFAULT 0;
  DECLARE V_ACConversionRate DECIMAL(18,6) DEFAULT 0;
  DECLARE V_FCConversionRate DECIMAL(18,6) DEFAULT 0;

  IF(p_CompanyCurrency = 0 || p_AccountCurrency=0 || p_FileCurrency = 0 || p_Rate = 0)
  THEN
	RETURN p_Rate;
  END IF;

  IF(p_FileCurrency = p_AccountCurrency)
  THEN
	SET V_NewRate = p_Rate;	
  ELSEIF (p_FileCurrency = p_CompanyCurrency)	
  THEN
    SELECT Value INTO V_ConversionRate FROM tblCurrencyConversion WHERE CurrencyID = p_AccountCurrency;
	 
	IF FOUND_ROWS() = 0
	THEN
	  SET V_NewRate = 0;
	ELSE
	  SET V_NewRate = (p_Rate * V_ConversionRate);
	END IF;	
  ELSE
	SELECT Value INTO V_ACConversionRate FROM tblCurrencyConversion WHERE CurrencyID = p_AccountCurrency;
	IF FOUND_ROWS() > 0
	THEN	
	  SELECT Value INTO V_FCConversionRate FROM tblCurrencyConversion WHERE CurrencyID = p_FileCurrency;
	  IF FOUND_ROWS() > 0
	  THEN
		SET V_NewRate = (V_ACConversionRate) * (p_Rate /V_FCConversionRate );
	  END IF;			
	END IF;
  END IF;

  RETURN V_NewRate;

END//
DELIMITER ;

-- Outpayment tax task 16-09-2019

DROP PROCEDURE IF EXISTS `prc_ProcessActiveCallCost`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_ProcessActiveCallCost`(
	IN `p_ProcessID` VARCHAR(50)
)
PRC:BEGIN

	DECLARE V_Count_ INT DEFAULT 0;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	TRUNCATE TABLE tblTempActiveCall;
	INSERT tblTempActiveCall SELECT * FROM `speakintelligentRoutingEngine`.`tblActiveCall` WHERE EndCall=1;

	INSERT INTO `speakintelligentCDR`.`tblUsageHeader`(AccountID,CompanyID,CompanyGatewayID,GatewayAccountID,StartDate,ServiceID,AccountServiceID,GatewayAccountPKID,created_at,updated_at)
	SELECT 
		DISTINCT
		a.AccountID,
		a.CompanyID,
		a.CompanyGatewayID,
		a.ServiceNumber AS GatewayAccountID,
		DATE_FORMAT(a.ConnectTime, "%Y-%m-%d") AS StartDate,
		a.ServiceID,
		a.AccountServiceID,
		0 as GatewayAccountPKID, 
		NOW() AS created_at,
		NOW() AS updated_at 
	FROM tblTempActiveCall a 
	LEFT JOIN `speakintelligentCDR`.`tblUsageHeader` h 
		ON h.CompanyGatewayID=a.CompanyGatewayID 
			AND h.StartDate = DATE_FORMAT(a.ConnectTime, "%Y-%m-%d")
			AND h.AccountID = a.AccountID 
			AND h.AccountServiceID = a.AccountServiceID  
			AND h.GatewayAccountID=a.ServiceNumber
	WHERE h.UsageHeaderID IS NULL;
	
		INSERT INTO `speakintelligentCDR`.`tblUsageDetails`(
			UsageHeaderID,
			connect_time,
			disconnect_time,
			duration,
			billed_duration,
			billed_second,
			area_prefix,
			CLIPrefix,
			cli,
			cld,
			cost,
			ProcessID,
			ID,
			UUID,
			OutpaymentPerCall,
			OutpaymentPerMinute,
			Surcharges,
			Chargeback,
			CollectionCostAmount,
			CollectionCostPercentage,
			RecordingCostPerMinute,
			PackageCostPerMinute,
			AccountServicePackageID,
			CallRecording,CallRecordingStartTime,
			OriginType,
			OriginProvider,
			TimezonesID,
			PackageTimezonesID,
			City,
			Tariff,
			NoType,
			is_inbound,
			disposition,
			BlockReason,
			CostPerCall,
			CostPerMinute,
			SurchargePerCall,
			SurchargePerMinute,
			MinimumCallCharge,
			MinimumDuration
		)
		SELECT 
			DISTINCT 
			h.UsageHeaderID,
			a.ConnectTime,
			a.DisconnectTime,
			a.Duration,
			a.billed_duration,
			a.Duration,
			a.CLDPrefix,
			a.CLIPrefix,
			a.CLI,
			a.CLD,
			a.Cost,
			p_ProcessID AS ProcessID,
			0 AS ID,
			a.UUID,
			a.OutpaymentPerCall,
			a.OutpaymentPerMinute,
			a.Surcharges,
			a.Chargeback,
			a.CollectionCostAmount,
			a.CollectionCostPercentage,
			a.RecordingCostPerMinute,
			a.PackageCostPerMinute,
			a.AccountServicePackageID,
			a.CallRecording,
			a.CallRecordingStartTime,
			a.OriginType,
			a.OriginProvider,
			a.TimezonesID,
			a.PackageTimezonesID,
			a.City,
			a.Tariff,
			a.NoType,
			( CASE WHEN(a.CallType = 'Inbound') THEN 1 ELSE 0 END) AS is_inbound,
			( CASE WHEN(a.IsBlock = '1') THEN 'Blocked' ELSE '' END) AS disposition,
			a.BlockReason,
			a.CostPerCall,
			a.CostPerMinute,
			a.SurchargePerCall,
			a.SurchargePerMinute,
			a.MinimumCallCharge,
			a.MinimumDuration
		FROM tblTempActiveCall a 
			INNER JOIN `speakintelligentCDR`.`tblUsageHeader` h
				ON h.CompanyGatewayID=a.CompanyGatewayID 
					AND h.StartDate = DATE_FORMAT(a.ConnectTime, "%Y-%m-%d")
					AND h.AccountID = a.AccountID 
					AND h.AccountServiceID = a.AccountServiceID 
					AND h.GatewayAccountPKID=a.GatewayAccountPKID 
					AND h.GatewayAccountID=a.ServiceNumber
		;	
		
		
		UPDATE `speakintelligentCDR`.`tblUsageDetails`
			SET ID = UsageDetailID
		WHERE ProcessID = p_ProcessID;		
		
		
		SELECT COUNT(*) INTO V_Count_ FROM tblTempActiveCall WHERE VendorID > 0 AND CallType='Outbound';
		
		IF V_Count_ > 0
		THEN
		
			INSERT INTO `speakintelligentCDR`.`tblVendorCDRHeader`(
				AccountID,
				CompanyID,
				CompanyGatewayID,
				GatewayAccountID,
				StartDate,
				ServiceID,
				AccountServiceID,
				GatewayAccountPKID,
				created_at,
				updated_at
			)
			SELECT 
				DISTINCT
				a.VendorID,
				a.CompanyID,
				a.CompanyGatewayID,
				ta.AccountName AS GatewayAccountID,
				DATE_FORMAT(ConnectTime, "%Y-%m-%d") AS StartDate,
				0 as ServiceID,
				0 as AccountServiceID,
				0 as GatewayAccountPKID,
				NOW() AS created_at,
				NOW() AS updated_at
			FROM tblTempActiveCall a 
				INNER JOIN tblAccount ta ON ta.AccountID = a.VendorID
				LEFT JOIN `speakintelligentCDR`.`tblVendorCDRHeader` h 
					ON h.CompanyGatewayID=a.CompanyGatewayID 
						AND h.StartDate = DATE_FORMAT(a.ConnectTime, "%Y-%m-%d")
						AND h.AccountID = a.VendorID 
						AND h.GatewayAccountID=ta.AccountName
				WHERE a.VendorID > 0 AND a.CallType='Outbound' AND h.VendorCDRHeaderID IS NULL;
				
				INSERT INTO `speakintelligentCDR`.`tblVendorCDR`(
					VendorCDRHeaderID,
					connect_time,
					disconnect_time,
					duration,
					billed_duration,
					billed_second,
					area_prefix,
					CLIPrefix,
					cli,
					cld,
					selling_cost,
					buying_cost,
					ProcessID,
					ID,
					UUID,
					trunk
				)
				SELECT 
					DISTINCT
					h.VendorCDRHeaderID AS VendorCDRHeaderID,
					a.ConnectTime,
					a.DisconnectTime,
					a.Duration,
					a.billed_duration,
					a.Duration,
					a.VendorCLDPrefix,
					a.VendorCLIPrefix,
					a.CLI,
					a.CLD,
					a.Cost,
					(a.billed_duration * (a.VendorRate/60)) AS buying_cost,
					p_ProcessID AS ProcessID,
					0 as ID,
					a.UUID,
					a.VendorConnectionName AS trunk
				FROM tblTempActiveCall a 
					INNER JOIN tblAccount ta ON ta.AccountID = a.VendorID
					INNER JOIN `speakintelligentCDR`.`tblVendorCDRHeader` h 
						ON h.CompanyGatewayID=a.CompanyGatewayID 
							AND h.StartDate = DATE_FORMAT(a.ConnectTime, "%Y-%m-%d")
							AND h.AccountID = a.VendorID 
							AND h.GatewayAccountID=ta.AccountName
					WHERE a.VendorID > 0 AND a.CallType='Outbound' AND h.AccountServiceID = 0 AND h.GatewayAccountPKID = 0;
	
			UPDATE `speakintelligentCDR`.`tblVendorCDR` v INNER JOIN `speakintelligentCDR`.`tblUsageDetails` ud ON ud.UUID = v.UUID AND ud.ProcessID = p_ProcessID
			SET v.ID = ud.ID
			WHERE v.ProcessID = p_ProcessID;
		
		END IF;
			
		SET V_Count_ = 0;	
		SELECT COUNT(*) INTO V_Count_ FROM tblTempActiveCall WHERE OutPaymentVendorID > 0 AND CallType='Inbound';
		
		IF V_Count_ > 0
		THEN
		
			INSERT INTO tblOutPaymentLog(CompanyID,AccountID,VendorID,CLI,Date,Amount,Status,created_at)
			SELECT DISTINCT  a.CompanyID,a.AccountID,a.OutPaymentVendorID AS VendorID,a.ServiceNumber AS CLI,DATE_FORMAT(ConnectTime, "%Y-%m-%d") AS Date,0 AS Amount,0 AS Status,NOW()
			FROM tblTempActiveCall a 
				LEFT JOIN tblOutPaymentLog op 
					ON op.AccountID = a.AccountID 
						AND op.VendorID = a.OutPaymentVendorID 
						AND op.DATE = DATE_FORMAT(a.ConnectTime, "%Y-%m-%d") 
						AND op.CLI = a.ServiceNumber
			WHERE a.OutPaymentVendorID > 0 AND a.CallType='Inbound' AND op.OutPaymentLogID IS NULL;
			
			DROP TEMPORARY TABLE IF EXISTS `outpayment_table1`;
			CREATE TEMPORARY TABLE `outpayment_table1` (
			  CompanyID INT,
			  AccountID INT,
			  VendorID INT,
			  CLI VARCHAR(50),
			  Amount DECIMAL(18,6),
			  `Date` DATETIME,
			  TaxRateID TEXT,
			  NewAmount DECIMAL(18,6)
			);
			
			INSERT INTO outpayment_table1(CompanyID,AccountID,VendorID,CLI,Date,Amount,TaxRateID,NewAmount)
			SELECT CompanyID,AccountID,VendorID,CLI,Date,SUM(Amount),0 as TaxRateID, SUM(Amount) as NewAmount FROM (
						SELECT CompanyID,AccountID,OutPaymentVendorID AS VendorID,ServiceNumber AS CLI,DATE_FORMAT(ConnectTime, "%Y-%m-%d") AS Date,(OutpaymentPerCall + OutpaymentPerMinute) AS Amount
			FROM tblTempActiveCall WHERE OutPaymentVendorID > 0 AND CallType='Inbound'
			)TBL group by CompanyID,AccountID,VendorID,CLI,Date;
			
			UPDATE outpayment_table1 t1
			  INNER JOIN tblAccount a
			    ON a.AccountID = t1.AccountID AND a.Billing = 1
			SET t1.TaxRateID = a.TaxRateID,NewAmount = FnGetCostWithTaxes(Amount,a.TaxRateID);		    
			
			UPDATE tblOutPaymentLog op 
				INNER JOIN outpayment_table1 t 
					ON op.AccountID = t.AccountID 
						AND op.VendorID = t.VendorID 
						AND op.DATE = t.Date
						AND op.CLI = t.CLI
			SET op.Amount = (op.Amount + t.NewAmount);						
			
		END IF;
		
		DELETE a FROM `speakintelligentRoutingEngine`.`tblActiveCall` a INNER JOIN tblTempActiveCall ta ON ta.ActiveCallID = a.ActiveCallID;
		
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FnGetCostWithTaxes`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` FUNCTION `FnGetCostWithTaxes`(
	`p_Rate` DECIMAL(18,6),
	`p_TaxRateIDs` TEXT	
) RETURNS decimal(18,6)
BEGIN

	DECLARE V_NewRate DECIMAL(18,6) DEFAULT 0;
	DECLARE i INT;

	DROP TEMPORARY TABLE IF EXISTS `table1`;
	CREATE TEMPORARY TABLE `table1` (
	  `TaxRateID` INT NOT NULL,
	  `Amount` DECIMAL(18,6) DEFAULT NULL,
	  `TotalAmount` DECIMAL(18,6) DEFAULT NULL
	);

	SET i=1;
	REPEAT

		INSERT INTO table1(TaxRateID)
		SELECT FnStringSplit(p_TaxRateIDs, ',', i) WHERE FnStringSplit(p_TaxRateIDs, ',', i) IS NOT NULL LIMIT 1;
		SET i = i + 1;
		UNTIL ROW_COUNT() = 0

	END REPEAT;


	UPDATE table1 t INNER JOIN tblTaxRate tr ON t.TaxRateID = tr.TaxRateId
	SET t.Amount = tr.Amount, t.TotalAmount = IF(tr.FlatStatus=1,tr.Amount,((p_Rate*tr.Amount)/100))
	;

	SELECT SUM(IFNULL(TotalAmount,0)) INTO V_NewRate FROM table1;

	RETURN V_NewRate + p_Rate;

END//
DELIMITER ;