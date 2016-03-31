CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAllDashboardData`(IN `p_companyId` INT, IN `p_userId` INT, IN `p_isadmin` INT)
BEGIN
  DECLARE v_isAccountManager_ int;
   
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 

SELECT count(*) as TotalDueCustomer,DAYSDIFF from (
			SELECT distinct  a.AccountName,a.AccountID,cr.TrunkID,cr.EffectiveDate,TIMESTAMPDIFF(DAY,cr.EffectiveDate, NOW()) as DAYSDIFF
			FROM tblCustomerRate cr
			INNER JOIN tblRate  r on r.RateID =cr.RateID
			INNER JOIN tblCountry c on c.CountryID = r.CountryID
			INNER JOIN tblAccount a on a.AccountID = cr.CustomerID
			WHERE a.CompanyId =@companyId
			AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, DATE_FORMAT (NOW(),120)) BETWEEN -1 AND 1

		) as tbl
		LEFT JOIN tblJob ON tblJob.AccountID = tbl.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='CD'))
					AND FIND_IN_SET (tbl.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0

					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)

		where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')
		group by DAYSDIFF
		order by DAYSDIFF desc;

 
        

        SELECT count(*) as TotalDueVendor ,DAYSDIFF from (
				SELECT  DISTINCT a.AccountName,a.AccountID,vr.TrunkID,vr.EffectiveDate,TIMESTAMPDIFF(DAY,vr.EffectiveDate, NOW()) as DAYSDIFF
			FROM tblVendorRate vr
			INNER JOIN tblRate  r on r.RateID =vr.RateID
			INNER JOIN tblCountry c on c.CountryID = r.CountryID
			INNER JOIN tblAccount a on a.AccountID = vr.AccountId
			WHERE a.CompanyId = @companyId
			AND TIMESTAMPDIFF(DAY,vr.EffectiveDate, NOW()) BETWEEN -1 AND 1
	   ) as tbl
		LEFT JOIN tblJob ON tblJob.AccountID = tbl.AccountID
					AND tblJob.JobID IN(select JobID from tblJob where JobTypeID = (select JobTypeID from tblJobType where Code='VD'))
					AND FIND_IN_SET (tbl.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)

		where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')
		group by DAYSDIFF
		order by DAYSDIFF desc;
  
   
     select `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` as Status, `tblJob`.`HasRead`, `tblJob`.`CreatedBy`, `tblJob`.`created_at` 
     from tblJob inner join tblJobStatus on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` inner join tblJobType on `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID` 
     where `tblJob`.`CompanyID` = p_companyId AND ( p_isadmin = 1 OR (p_isadmin = 0 and tblJob.JobLoggedUserID = p_userId) )
     order by `tblJob`.`ShowInCounter` desc, `tblJob`.`updated_at` desc,tblJob.JobID desc
	  limit 10;
     
    
 
    select count(*) as aggregate from tblJob inner join tblJobStatus on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` 
    where `tblJobStatus`.`Title` = 'Pending' and `tblJob`.`CompanyID` = p_companyId AND 
     ( p_isadmin = 1 OR ( p_isadmin = 0 and tblJob.JobLoggedUserID = p_userId) );
   
  
 
      select `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` as Status, `tblJob`.`CreatedBy`, `tblJob`.`created_at` from tblJob inner join tblJobStatus on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` inner join tblJobType on `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID` 
        where ( tblJobStatus.Title = 'Success' OR tblJobStatus.Title = 'Completed' ) and `tblJob`.`CompanyID` = p_companyId and
        ( p_isadmin = 1 OR ( p_isadmin = 0 and `tblJob`.`JobLoggedUserID` = p_userId))  and `tblJob`.`updated_at` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
		  limit 10; 
        
	  
        select AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount where (`AccountType` = '0' and `CompanyID` = p_companyId and `Status` = '1') order by `tblAccount`.`AccountID` desc limit 10;
    
 
    select AccountID,AccountName,Phone,Email,created_by,created_at from tblAccount 
          where 
          `AccountType` = '1' and CompanyID = p_companyId and `Status` = '1' 
          AND (  v_isAccountManager_ = 0 OR (v_isAccountManager_ = 1 AND tblAccount.Owner = p_userId ) )
    order by `tblAccount`.`AccountID` desc
	 limit 10;
  
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
 
END