CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_InsertDiscontinuedVendorRate`(IN `p_AccountId` INT, IN `p_TrunkId` INT)
BEGIN
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		--  tmp_Delete_VendorRate table created in prc_VendorBulkRateDelete if vendor delete from front end
		-- tmp_Delete_VendorRate table created in prc_WSProcessVendorRate if vendor delete from service or vendor upload process
	 	
	 	
	 	-- insert vendor rate in tblVendorRateDiscontinued before delete
	 	INSERT INTO tblVendorRateDiscontinued(
					VendorRateID,
			   	AccountId,
			   	TrunkID,
					RateId,
			   	Code,
			   	Description,
			   	Rate,
			   	EffectiveDate,
			   	Interval1,
			   	IntervalN,
			   	ConnectionFee,
			   	deleted_at
					)
		SELECT DISTINCT
			   	VendorRateID,
			   	AccountId,
			   	TrunkID,
					RateId,
			   	Code,
			   	Description,
			   	Rate,
			   	EffectiveDate,
			   	Interval1,
			   	IntervalN,
			   	ConnectionFee,
			   	deleted_at
		FROM tmp_Delete_VendorRate
			ORDER BY VendorRateID ASC
			; 
		
		-- delete old code or duplicate code from tblVendorRateDiscontinued
		
		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDiscontinued_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDiscontinued_ as (select * from tblVendorRateDiscontinued);

		  DELETE n1 FROM tblVendorRateDiscontinued n1, tmp_VendorRateDiscontinued_ n2 WHERE n1.DiscontinuedID < n2.DiscontinuedID
		  	 AND  n1.RateId = n2.RateId
		  	 AND  n1.AccountId = n2.AccountId
			 AND  n1.AccountId = p_AccountId
			 AND  n2.AccountId = p_AccountId;
	
		-- delete vendor rate from tblVendorRate
	
		DELETE tblVendorRate
			FROM tblVendorRate
				INNER JOIN(	SELECT dv.VendorRateID FROM tmp_Delete_VendorRate dv) tmdv
					ON tmdv.VendorRateID = tblVendorRate.VendorRateID			
			WHERE tblVendorRate.AccountId = p_AccountId;
									
		CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId);
		
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END