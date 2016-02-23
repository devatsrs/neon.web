CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertPayments`(IN `p_CompanyID` INT, IN `p_ProcessID` VARCHAR(100), IN `p_UserID` INT)

	DECLARE v_UserName varchar(30);
 	
 	SELECT CONCAT(u.FirstName,CONCAT(' ',u.LastName)) as name into v_UserName from Ratemanagement3.tblUser u where u.UserID=p_UserID;
 	
 	INSERT INTO tblPayment (
	 		CompanyID,
	 		 AccountID,
			 InvoiceNo,
			 PaymentDate,
			 PaymentMethod,
			 PaymentType,
			 Notes,
			 Amount,
			 Currency,
			 Recall,
			 `Status`,
			 created_at,
			 updated_at,
			 CreatedBy,
			 ModifyBy,
			 RecallReasoan,
			 RecallBy)
 	select distinct tp.CompanyID,
	 		 tp.AccountID,
			 tp.InvoiceNo,
			 tp.PaymentDate,
			 tp.PaymentMethod,
			 tp.PaymentType,
			 tp.Notes,
			 tp.Amount,
			 (select `Code` 
					from Ratemanagement3.tblAccount ac 
					inner join Ratemanagement3.tblCurrency cr on ac.CurrencyId = cr.CurrencyId 
					where (ac.AccountID = tp.AccountID) limit 1 ) as Currency,
			 0 as Recall,
			 tp.Status,
			 Now() as created_at,
			 Now() as updated_at,
			 v_UserName as CreatedBy,
			 '' as ModifyBy,
			 '' as RecallReasoan,
			 '' as RecallBy
	from tblTempPayment tp
	where tp.ProcessID = p_ProcessID
			AND tp.CompanyID = p_CompanyID;	
END