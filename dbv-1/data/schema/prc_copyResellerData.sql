CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_copyResellerData`(
	IN `p_companyid` INT,
	IN `p_resellerids` TEXT,
	IN `p_is_product` INT,
	IN `p_product` TEXT,
	IN `p_is_subscription` INT,
	IN `p_subscription` TEXT,
	IN `p_is_trunk` INT,
	IN `p_trunk` TEXT


)
BEGIN
	DECLARE v_resellerId_ INT; 
	DECLARE v_pointer_ INT ;
	DECLARE v_rowCount_ INT ; 	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		
	END;		

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
		DROP TEMPORARY TABLE IF EXISTS tmp_currency;
		CREATE TEMPORARY TABLE tmp_currency (
			`ResellerCompanyID` INT,
			`CompanyId` INT,
			`Code` VARCHAR(50),
			`CurrencyID` INT,
			`NewCurrencyID` INT
		) ENGINE=InnoDB;	
				
		DROP TEMPORARY TABLE IF EXISTS tmp_product;
		CREATE TEMPORARY TABLE tmp_product (
			`ResellerCompanyID` INT,
			`CompanyId` INT,
			`Name` VARCHAR(50),
			`Code` VARCHAR(50),
			`Description` LONGTEXT,
			`Amount` DECIMAL(18,2),
			`Active` TINYINT(3) UNSIGNED,
			`Note` LONGTEXT,
			INDEX tmp_product_ResellerCompanyID (`ResellerCompanyID`),
			INDEX tmp_product_Code (`Code`)
	  	);			
				
		DROP TEMPORARY TABLE IF EXISTS tmp_BillingSubscription;
		CREATE TEMPORARY TABLE tmp_BillingSubscription (
				`ResellerCompanyID` INT,
				`CompanyID` INT(11),
				`Name` VARCHAR(50),
				`Description` LONGTEXT,
				`InvoiceLineDescription` VARCHAR(250),
				`ActivationFee` DECIMAL(18,2),						
				`CurrencyID` INT(11),
				`AnnuallyFee` DECIMAL(18,2),
				`QuarterlyFee` DECIMAL(18,2),
				`MonthlyFee` DECIMAL(18,2),
				`WeeklyFee` DECIMAL(18,2),
				`DailyFee` DECIMAL(18,2),
				`Advance` TINYINT(3) UNSIGNED,
				INDEX tmp_BillingSubscription_ResellerCompanyID (`ResellerCompanyID`),
				INDEX tmp_BillingSubscription_Name (`Name`)
		);	

		DROP TEMPORARY TABLE IF EXISTS tmp_Trunk;
		CREATE TEMPORARY TABLE tmp_Trunk (
				`ResellerCompanyID` INT,
				`Trunk` VARCHAR(50),
				`CompanyId` INT(11),
				`RatePrefix` VARCHAR(50),
				`AreaPrefix` VARCHAR(50),
				`Prefix` VARCHAR(50),
				`Status` TINYINT(1),
				INDEX tmp_Trunk_ResellerCompanyID (`ResellerCompanyID`),
				INDEX tmp_Trunk_TrunkName (`Trunk`)
			);	
			
			
		DROP TEMPORARY TABLE IF EXISTS tmp_resellers;
		CREATE TEMPORARY TABLE tmp_resellers (
			`CompanyID` INT,
			`ResellerID` INT,
			`ResellerCompanyID` INT,
			`AccountID` INT,
			`RowNo` INT,
			INDEX tmp_resellers_ResellerID (`ResellerID`),
			INDEX tmp_resellers_ResellerCompanyID (`ResellerCompanyID`),
			INDEX tmp_resellers_RowNo (`RowNo`)
		);			
				
				INSERT INTO tmp_resellers
				SELECT
					CompanyID,
					ResellerID,
					ChildCompanyID as ResellerCompanyID,
					AccountID,
					@row_num := @row_num+1 AS RowID
				FROM tblReseller,(SELECT @row_num := 0) x
				WHERE CompanyID = p_companyid
					  AND FIND_IN_SET(ResellerID,p_resellerids);
				
					
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(distinct ResellerCompanyID ) FROM tmp_resellers);
					
		WHILE v_pointer_ <= v_rowCount_
		DO
					
					SET v_resellerId_ = (SELECT ResellerCompanyID FROM tmp_resellers rr WHERE rr.RowNo = v_pointer_);			
					
							INSERT INTO	tmp_currency(ResellerCompanyID,CompanyId,Code,CurrencyID)	
							SELECT v_resellerId_ as ResellerCompanyID,p_companyid as CompanyId,Code, CurrencyId FROM `tblCurrency` WHERE CompanyId	= p_companyid;	
							
							UPDATE tmp_currency tc LEFT JOIN tblCurrency c ON tc.Code=c.Code AND tc.ResellerCompanyID = v_resellerId_ AND c.CompanyId = v_resellerId_
									set NewCurrencyID = c.CurrencyId
							WHERE c.CurrencyId IS NOT NULL;		
					
					IF p_is_product =1
					THEN	
					
						INSERT INTO tmp_product(ResellerCompanyID,CompanyId,Name,Code,Description,Amount,Active,Note)
						SELECT DISTINCT v_resellerId_ as ResellerCompanyID,p_companyid as `CompanyId`,Name,Code,Description,Amount,Active,Note
							FROM NeonBillingDev.tblProduct
						WHERE CompanyId = p_companyid AND FIND_IN_SET(ProductID,p_product);
					
					END IF;
					
					IF p_is_subscription = 1
					THEN
					
						INSERT INTO tmp_BillingSubscription(`ResellerCompanyID`,`CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance)
						SELECT DISTINCT v_resellerId_ as ResellerCompanyID, `CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance
						FROM NeonBillingDev.tblBillingSubscription
						WHERE CompanyID = p_companyid AND FIND_IN_SET(SubscriptionID,p_subscription);
					
					END IF;

					IF p_is_trunk = 1
					THEN
					
					INSERT INTO tmp_Trunk(ResellerCompanyID,Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status)
							SELECT DISTINCT v_resellerId_ as ResellerCompanyID,Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status
								FROM tblTrunk
							WHERE CompanyId = p_companyid AND FIND_IN_SET(TrunkID,p_trunk);
							
					END IF;		
					
					 SET v_pointer_ = v_pointer_ + 1;			 
			
		END WHILE;
			
		
		IF p_is_product =1
		THEN	
					INSERT INTO NeonBillingDev.tblProduct (CompanyId,Name,Code,Description,Amount,Active,Note,CreatedBy,ModifiedBy,created_at,updated_at)
					SELECT DISTINCT tp.ResellerCompanyID as `CompanyId`,tp.Name,tp.Code,tp.Description,tp.Amount,tp.Active,tp.Note,'system' as CreatedBy,'system' as ModifiedBy,NOW(),NOW()
						FROM tmp_product tp 
							LEFT JOIN NeonBillingDev.tblProduct p
							ON tp.ResellerCompanyID = p.CompanyId
							AND tp.Code=p.Code
					WHERE p.ProductID IS NULL;		
		
		END IF;
		

		
		IF p_is_subscription = 1
		THEN
				
				INSERT INTO NeonBillingDev.tblBillingSubscription(`CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance,created_at,updated_at,ModifiedBy,CreatedBy)
				SELECT DISTINCT tb.ResellerCompanyID as `CompanyID`,tb.Name,tb.Description,tb.InvoiceLineDescription,tb.ActivationFee,(SELECT NewCurrencyID FROM tmp_currency tc WHERE tc.CurrencyID= tb.CurrencyID AND tc.ResellerCompanyID = tb.ResellerCompanyID) as CurrencyID,tb.AnnuallyFee,tb.QuarterlyFee,tb.MonthlyFee,tb.WeeklyFee,tb.DailyFee,tb.Advance,Now(),Now(),'system' as ModifiedBy,'system' as CreatedBy 
					FROM tmp_BillingSubscription tb 
						LEFT JOIN NeonBillingDev.tblBillingSubscription b
						ON tb.ResellerCompanyID = b.CompanyID
						AND tb.Name = b.Name
				WHERE b.SubscriptionID IS NULL;
		
		END IF;
		
		IF p_is_trunk =1
		THEN

				INSERT INTO tblTrunk (Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status,created_at,updated_at)
				SELECT DISTINCT tt.Trunk, tt.ResellerCompanyID as `CompanyId`,tt.RatePrefix,tt.AreaPrefix,tt.`Prefix`,tt.Status,Now(),Now()
				FROM tmp_Trunk tt
					LEFT JOIN tblTrunk tr ON tt.ResellerCompanyID = tr.CompanyId AND tt.Trunk = tr.Trunk
				WHERE tr.TrunkID IS NULL;
		
		END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END