CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessImportAccount`(IN `p_processId` VARCHAR(200) , IN `p_companyId` INT, IN `p_companygatewayid` INT, IN `p_tempaccountid` TEXT, IN `p_option` INT)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;         
	SET sql_mode = '';	    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SET SESSION sql_mode='';
    
	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  ( 
        Message VARCHAR(200)     
    );
    
   IF p_option = 0 /* Csv Import */
   THEN
   
	DELETE n1 FROM tblTempAccount n1, tblTempAccount n2 WHERE n1.tblTempAccountID < n2.tblTempAccountID 	 	
		AND  n1.CompanyId = n2.CompanyId		   
		AND  n1.AccountName = n2.AccountName		
		AND  n1.ProcessID = n2.ProcessID
 		AND  n1.ProcessID = p_processId and n2.ProcessID = p_processId;      

			INSERT  INTO tblAccount
				  (	AccountType ,
					CompanyId ,
					Title,
					Owner ,
					`Number`,
					AccountName,
					NamePrefix,
					FirstName,
					LastName,
					LeadStatus,
					LeadSource,
					Email,
					Phone,
					Address1,
					Address2,
					Address3,
					City,
					PostCode,
					Country,
					Status,
					tags,
					Website,
					Mobile,
					Fax,
					Skype,
					Twitter,
					Employee,
					Description,
					BillingEmail,
					CurrencyId,
					VatNumber,
					created_at,
					created_by
                   )            
				   
				   SELECT  DISTINCT
					ta.AccountType,
					ta.CompanyId,
					ta.Title,
					ta.Owner,
					FnGetAccountNumber(p_companyId,IFNULL(ta.Number,0)) as Number,
					ta.AccountName,
					ta.NamePrefix,
					ta.FirstName,
					ta.LastName,
					ta.LeadStatus,
					ta.LeadSource,
					ta.Email,
					ta.Phone,
					ta.Address1,
					ta.Address2,
					ta.Address3,
					ta.City,
					ta.PostCode,
					ta.Country,
					ta.Status,
					ta.tags,
					ta.Website,
					ta.Mobile,
					ta.Fax,
					ta.Skype,
					ta.Twitter,
					ta.Employee,
					ta.Description,
					ta.BillingEmail,
					ta.Currency as CurrencyId,
					ta.VatNumber,
					ta.created_at,
					ta.created_by
					from tblTempAccount ta
						left join tblAccount a on ta.AccountName = a.AccountName
						where ta.ProcessID = p_processId
							AND a.AccountID is null
							AND ta.CompanyID = p_companyId;
			
  
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
    
	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );
	 
	 DELETE  FROM tblTempAccount WHERE   tblTempAccount.ProcessID = p_processId;
	
	END IF;
	
	IF p_option = 1 /* Manual Import by Comapanygatewayid */	
   THEN
			
			INSERT  INTO tblAccount
				  (	AccountType ,
					CompanyId ,
					Title,
					Owner ,
					AccountName,
					NamePrefix,
					FirstName,
					LastName,
					LeadStatus,
					LeadSource,
					Email,
					Phone,
					Address1,
					Address2,
					Address3,
					City,
					PostCode,
					Country,
					Status,
					tags,
					Mobile,
					Fax,
					created_at,
					created_by
                   )
			SELECT  DISTINCT
					ta.AccountType,
					ta.CompanyId,
					ta.Title,
					ta.Owner,
					ta.AccountName,
					ta.NamePrefix,
					ta.FirstName,
					ta.LastName,
					ta.LeadStatus,
					ta.LeadSource,
					ta.Email,
					ta.Phone,
					ta.Address1,
					ta.Address2,
					ta.Address3,
					ta.City,
					ta.PostCode,
					ta.Country,
					ta.Status,
					ta.tags,
					ta.Mobile,
					ta.Fax,
					ta.created_at,
					ta.created_by
				from tblTempAccount ta
				left join tblAccount a on ta.AccountName=a.AccountName AND ta.CompanyId = a.CompanyId
				where ta.CompanyID = p_companyId 
				AND a.AccountID is null			
				AND ta.CompanyGatewayID = p_companygatewayid
				group by ta.AccountName;

			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();	
			
			INSERT INTO tmp_JobLog_ (Message)
			SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );
			
			DELETE  FROM tblTempAccount WHERE CompanyGatewayID = p_companygatewayid;
					 
   END IF;
	
	IF p_option = 2 /* Manual Import by tempaccountid */	
   THEN
			
			INSERT  INTO tblAccount
				  (	AccountType ,
					CompanyId ,
					Title,
					Owner ,
					AccountName,
					NamePrefix,
					FirstName,
					LastName,
					LeadStatus,
					LeadSource,
					Email,
					Phone,
					Address1,
					Address2,
					Address3,
					City,
					PostCode,
					Country,
					Status,
					tags,
					Mobile,
					Fax,
					BillingTimezone,
					created_at,
					created_by
                   )
			SELECT  DISTINCT
					ta.AccountType,
					ta.CompanyId,
					ta.Title,
					ta.Owner,
					ta.AccountName,
					ta.NamePrefix,
					ta.FirstName,
					ta.LastName,
					ta.LeadStatus,
					ta.LeadSource,
					ta.Email,
					ta.Phone,
					ta.Address1,
					ta.Address2,
					ta.Address3,
					ta.City,
					ta.PostCode,
					ta.Country,
					ta.Status,
					ta.tags,
					ta.Mobile,
					ta.Fax,
					ta.BillingTimezone,
					ta.created_at,
					ta.created_by
				from tblTempAccount ta
				left join tblAccount a on ta.AccountName=a.AccountName AND ta.CompanyId = a.CompanyId
				where ta.CompanyID = p_companyId 
				AND a.AccountID is null			
				AND FIND_IN_SET(ta.tblTempAccountID,p_tempaccountid)
				group by ta.AccountName;

			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();	

			INSERT INTO tmp_JobLog_ (Message)
			SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );	
			
			DELETE  FROM tblTempAccount WHERE FIND_IN_SET(tblTempAccountID,p_tempaccountid);
   
   END IF;   
   
 	 SELECT * from tmp_JobLog_;	      
	      
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END