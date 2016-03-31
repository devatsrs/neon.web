CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardDataRecentDueRateSheet`(IN `p_companyId` int)
BEGIN
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
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END