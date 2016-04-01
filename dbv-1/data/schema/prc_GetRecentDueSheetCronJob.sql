CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetRecentDueSheetCronJob`(IN `p_companyId` INT)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

            SELECT  DISTINCT
                TBL.AccountID,
                TBL.AccountName,
                TBL.Trunk,
                TBL.EffectiveDate,
				TBL.Owner,
				DAYSDIFF,
				'vender' as type
            FROM (SELECT DISTINCT
                    a.AccountID,
                    a.AccountName,
                    t.Trunk,
					t.TrunkID,
                    vr.EffectiveDate,
					a.Owner,
					TIMESTAMPDIFF(DAY,vr.EffectiveDate, NOW()) as DAYSDIFF
                FROM tblVendorRate vr
				inner join tblVendorTrunk vt on vt.AccountID = vr.AccountId and vt.TrunkID = vr.TrunkID and vt.Status =1
                INNER JOIN tblRate r
                    ON r.RateID = vr.RateID
                INNER JOIN tblCountry c
                    ON c.CountryID = r.CountryID
                INNER JOIN tblAccount a
                    ON a.AccountID = vr.AccountId
                INNER JOIN tblTrunk t
                    ON t.TrunkID = vr.TrunkID
                WHERE a.CompanyId = p_companyId

				AND TIMESTAMPDIFF(DAY, vr.EffectiveDate, DATE_FORMAT (NOW(),'%Y-%m-%d')) BETWEEN -1 AND 1

				) AS TBL

				Left JOIN tblJob ON tblJob.AccountID = TBL.AccountID
					AND tblJob.JobTypeID = (select JobTypeID from tblJobType where Code='VD')
					AND FIND_IN_SET (TBL.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)
					where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')

						UNION ALL

            SELECT  DISTINCT
                TBL.AccountID,
                TBL.AccountName,
                TBL.Trunk,
                TBL.EffectiveDate,
				tbl.Owner,
				DAYSDIFF,
				'customer' as type
            FROM (SELECT DISTINCT
                a.AccountID,
                a.AccountName,
				a.Owner,
                t.Trunk,
				t.TrunkID,
                cr.EffectiveDate,
				TIMESTAMPDIFF(DAY,cr.EffectiveDate, NOW()) as DAYSDIFF
            FROM tblCustomerRate cr
			inner join tblCustomerTrunk ct on ct.AccountID = cr.CustomerID and ct.TrunkID = cr.TrunkID and ct.Status =1
            INNER JOIN tblRate r
                ON r.RateID = cr.RateID
            INNER JOIN tblCountry c
                ON c.CountryID = r.CountryID
            INNER JOIN tblAccount a
                ON a.AccountID = cr.CustomerID
            INNER JOIN tblTrunk t
                ON t.TrunkID = cr.TrunkID
            WHERE a.CompanyId = p_companyId
				AND TIMESTAMPDIFF(DAY, cr.EffectiveDate, DATE_FORMAT (NOW(),'%Y-%m-%d')) BETWEEN -1 AND 1
			) AS TBL

			LEFT JOIN tblJob ON tblJob.AccountID = TBL.AccountID
					AND tblJob.JobTypeID = (select JobTypeID from tblJobType where Code='CD')
					AND FIND_IN_SET (TBL.TrunkID,JSON_EXTRACT(tblJob.Options, '$.Trunks')) != 0
					AND ( TIMESTAMPDIFF(DAY, tblJob.created_at, NOW()) = DAYSDIFF or TIMESTAMPDIFF(DAY, tblJob.created_at, EffectiveDate)  BETWEEN -1 AND 0)

						where JobStatusID is null or JobStatusID <> (select JobStatusID from tblJobStatus where Code='S')
						order by Owner,DAYSDIFF,type ASC;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END