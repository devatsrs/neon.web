CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_applyRateTableTomultipleAccByTrunk`(
	IN `p_companyid` INT,
	IN `p_AccountIds` TEXT,
	IN `p_ratetableId` INT,
	IN `p_CreatedBy` VARCHAR(100)





,
	IN `p_checkedAll` VARCHAR(1),
	IN `p_select_trunkId` INT,
	IN `p_select_currency` INT,
	IN `p_select_ratetableid` INT,
	IN `p_select_accounts` TEXT





)
ThisSP:BEGIN

	DECLARE v_codeDeckId int;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;	
	DECLARE v_AccountID_ INT;
	DECLARE v_TrunkID_ INT;
	DECLARE v_RateTableID_ INT;
	DECLARE v_Old_CodeDeckID_ INT;
	DECLARE v_Action_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT CodeDeckId INTO v_codeDeckId FROM  tblRateTable WHERE RateTableId = p_ratetableId;

	DROP TEMPORARY TABLE IF EXISTS tmp;
		CREATE TEMPORARY TABLE tmp (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			CustomerTrunkID INT ,
			CompanyID INT ,
			AccountID INT,
			TrunkID INT ,
			RateTableID INT,
			CodeDeckID INT,
			CreatedBy VARCHAR(100)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_insert;
		CREATE TEMPORARY TABLE tmp_insert (
			CompanyID INT ,
			AccountID INT,
			TrunkID INT ,
			RateTableID INT,
			CreatedBy VARCHAR(100)
	);
		
			
         IF ( p_checkedAll = 'Y')  THEN
         
         	
				
					DROP TEMPORARY TABLE IF EXISTS tmp_allaccountByfilter;
						CREATE TEMPORARY TABLE tmp_allaccountByfilter (
							AccountID INT,
							RateTableID INT,
							TrunkID INT 
					);
         
					/* Get All Acount by filter (p_select_trunkId,p_select_currency,p_select_ratetableid,p_select_accounts)  */
				
						insert into tmp_allaccountByfilter (AccountID,RateTableID,TrunkID)					
						select distinct a.AccountID,r.RateTableId,t.TrunkID from tblAccount a
									join tblTrunk t
									left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
									left join tblRateTable r on ct.RateTableID=r.RateTableId
									where 
									CASE 
										WHEN (p_select_ratetableid > 0 AND p_select_trunkId > 0) THEN
										    r.TrunkID=p_select_trunkId AND r.RateTableId=p_select_ratetableid AND a.CompanyId=p_companyid  
										    
										WHEN (p_select_trunkId > 0) THEN
											 t.TrunkID=p_select_trunkId AND a.CompanyId=p_companyid 
										 
									   WHEN (p_select_ratetableid > 0) THEN
										    r.RateTableId=p_select_ratetableid AND a.CompanyId=p_companyid 
									   ELSE						
											 a.CompanyId=p_companyid				
										
									END
									AND (p_select_accounts='' OR FIND_IN_SET(a.AccountID,p_select_accounts) != 0 )
									AND a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1
									AND a.CurrencyId=p_select_currency AND t.`Status`=1;								
								
					-- select * from tmp_allaccountByfilter;
					
					insert into tmp (CustomerTrunkID,AccountID,TrunkID,RateTableID,CodeDeckID)
					select distinct ct.CustomerTrunkID,a.AccountID,taf.TrunkID,ct.RateTableID,r.CodeDeckId
					from tblAccount a
					join tblTrunk t
					left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
					left join tblRateTable r on ct.RateTableID=r.RateTableId
					right join tmp_allaccountByfilter taf on a.AccountID=taf.AccountID
					where a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1 AND t.TrunkID=taf.TrunkID;
					
					select * from  tmp;
					 
					select * from  tmp where CustomerTrunkID IS NULL;
					select * from  tmp where CustomerTrunkID IS NOT NULL;
												
			
					
					
			ELSE
			
				
							/* Start the selected Account ratetable apply */
			
			
			
							DROP TEMPORARY TABLE IF EXISTS temp_acc_trunk;
								CREATE TEMPORARY TABLE temp_acc_trunk (
									Account_Trunk text,
									AccountID INT,
									TrunkID INT
							);
							DROP TEMPORARY TABLE IF EXISTS tmp_seprateAccountandTrunk;
								CREATE TEMPORARY TABLE tmp_seprateAccountandTrunk (
									Account_Trunk text,
									AccountID INT,
									TrunkID INT
							);
							
							/* Insert all comma seprate account with trunk in Account_Trunk field from Parameter "p_AccountIds" like 
							( Account_Trunk  AccountID   TrunkID
							  5009_7           NULL      NULL
							  5009_3           NULL      NULL
							  5009_5           NULL      NULL
							) */							
							set @sql = concat("insert into temp_acc_trunk (Account_Trunk) values ('", replace(( select distinct p_AccountIds as data), ",", "'),('"),"');");
							prepare stmt1 from @sql;
							execute stmt1;	
							
							
			
							/* Spit(_) Account with trunk field in AccountID and TrunkID like and insert into table (tmp_seprateAccountandTrunk)
								Account_Trunk  AccountID   TrunkID
							   5009_7           5009      7
							   5009_3           5009      3
							   5009_5           5009      5
							*/							
							insert into tmp_seprateAccountandTrunk (Account_Trunk,AccountID,TrunkID)
							SELECT   Account_Trunk , SUBSTRING_INDEX(Account_Trunk,'_',1) AS AccountID , SUBSTRING_INDEX(Account_Trunk,'_',-1) AS TrunkID FROM temp_acc_trunk;
							
							
							insert into tmp (CustomerTrunkID,AccountID,TrunkID,RateTableID,CodeDeckID)
							select distinct ct.CustomerTrunkID,a.AccountID,taf.TrunkID,ct.RateTableID,r.CodeDeckId
							from tblAccount a
							join tblTrunk t
							left join tblCustomerTrunk ct on a.AccountID=ct.AccountID AND t.TrunkID=ct.TrunkID								
							left join tblRateTable r on ct.RateTableID=r.RateTableId
							right join tmp_seprateAccountandTrunk taf on a.AccountID=taf.AccountID
							where a.IsCustomer=1 AND a.`Status`=1 AND a.AccountType=1 AND t.TrunkID=taf.TrunkID;
							
															
				
							
			END IF;
			

			-- archive rate table rate by vasim seta starts -- V-4.17
			SET v_pointer_ = 1;
			SET v_rowCount_ = (SELECT COUNT(*) FROM tmp);
			
			WHILE v_pointer_ <= v_rowCount_
			DO
				SELECT AccountID,TrunkID,RateTableID,CodeDeckID INTO v_AccountID_,v_TrunkID_,v_RateTableID_,v_Old_CodeDeckID_ FROM tmp t WHERE t.RowID = v_pointer_;
				
				IF(v_codeDeckId = v_Old_CodeDeckID_)
				THEN
					SET v_Action_ = 2; -- change ratetable - will archive only ratetable rate
				ELSE
					SET v_Action_ = 1; -- change codedeck - will archive both ratetable rate and customer rate
				END IF;
				
				CALL prc_ChangeCodeDeckRateTable(v_AccountID_,v_TrunkID_,v_RateTableID_,p_CreatedBy, v_Action_);
				
				SET v_pointer_ = v_pointer_ + 1;
			END WHILE;			
			-- archive rate table rate by vasim seta ends -- V-4.17
			
			
			/*DELETE ct FROM tblCustomerRate ct
			inner join tmp on ct.TrunkID = tmp.TrunkID AND ct.CustomerID=tmp.AccountID;*/
			
		
			
			insert into tblCustomerTrunk (AccountID,RateTableID,CodeDeckId,CompanyID,TrunkID,IncludePrefix,RoutinePlanStatus,UseInBilling,Status,CreatedBy)
			select 
			sep_acc.AccountID,
			p_ratetableId as RateTableID,
			v_codeDeckId as CodeDeckId,
			p_companyid as CompanyID,
			sep_acc.TrunkID as TrunkID,
			0 as IncludePrefix,
			0 as RoutinePlanStatus,
			0 as UseInBilling,
			1 as Status,			
			p_CreatedBy as CreatedBy
			from tmp sep_acc
			left join tblCustomerTrunk ct on sep_acc.TrunkID = ct.TrunkID AND sep_acc.AccountID = ct.AccountID
			where sep_acc.CustomerTrunkID IS NULL;
			
			
			
			update tblCustomerTrunk 
			right join tmp sep_tmp on tblCustomerTrunk.AccountID=sep_tmp.AccountID AND tblCustomerTrunk.TrunkID=sep_tmp.TrunkID
			set tblCustomerTrunk.RateTableID=p_ratetableId,tblCustomerTrunk.CodeDeckId=v_codeDeckId,tblCustomerTrunk.`Status`=1, ModifiedBy=p_CreatedBy
			where  tblCustomerTrunk.CustomerTrunkID IS NOT NULL;	
								
		  
		    

						

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END