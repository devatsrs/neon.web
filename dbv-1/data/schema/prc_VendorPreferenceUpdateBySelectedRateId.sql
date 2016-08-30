CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorPreferenceUpdateBySelectedRateId`(IN `p_CompanyId` INT, IN `p_AccountId` LONGTEXT , IN `p_RateIDList` LONGTEXT , IN `p_TrunkId` VARCHAR(100) , IN `p_Preference` INT, IN `p_ModifiedBy` VARCHAR(50)
, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(50), IN `p_action` INT)
BEGIN


	DECLARE v_CodeDeckId_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	select CodeDeckId into v_CodeDeckId_  from tblVendorTrunk where AccountID = p_AccountID and TrunkID = p_trunkID;
	
	IF p_action = 0
	THEN	
			  DROP TEMPORARY TABLE IF EXISTS tblVendorRate_;
			  CREATE TEMPORARY TABLE tblVendorRate_  (
				RateID INT(11) 
			  );
			  
			  INSERT INTO tblVendorRate_
			  SELECT RateID from tblRate 
				where FIND_IN_SET(RateID,p_RateIDList);
			  
			  SELECT *FROM	tblVendorRate_;
	END IF; 	
	
	IF p_action = 1
	THEN	
			DROP TEMPORARY TABLE IF EXISTS tblVendorRate_;
			  CREATE TEMPORARY TABLE tblVendorRate_  (
				RateID INT(11) 
			  );
  
			INSERT INTO tblVendorRate_
			SELECT
				tblRate.RateID as RateID
			FROM tblVendorRate
			JOIN tblRate
				ON tblVendorRate.RateId = tblRate.RateId
			LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
			WHERE (p_contryID IS NULL OR CountryID = p_contryID)
			AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
			AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
			AND (tblRate.CompanyID = p_companyid)
			AND tblVendorRate.TrunkID = p_trunkID
			AND tblVendorRate.AccountID = p_AccountID
			AND CodeDeckId = v_CodeDeckId_;
	END IF;			
			
			UPDATE  tblVendorPreference v
		    INNER JOIN (
			select  tvp.VendorPreferenceID,tvp.RateId from tblVendorPreference tvp
			LEFT JOIN tblVendorRate_ vr on tvp.RateId = vr.RateID  			
			  where tvp.AccountId = p_AccountId
				and tvp.TrunkID = p_TrunkId
				and vr.RateID IS NOT NULL
		    )vp on vp.VendorPreferenceID = v.VendorPreferenceID
			SET Preference = p_Preference;
			
			INSERT  INTO tblVendorPreference
		   (  RateID ,
			AccountId ,
			  TrunkID ,
			  Preference,
			  CreatedBy,
			  created_at
		   )
		   
		   SELECT  DISTINCT
			 vr.RateID,
			 p_AccountId ,
			 p_TrunkId,
			 p_Preference ,
			 p_ModifiedBy,
			 NOW()
			 from tblVendorRate_ vr 
			 LEFT JOIN tblVendorPreference vp 
				ON vr.RateID = vp.RateID and vp.AccountId = p_AccountId and vp.TrunkID = p_TrunkId 
				where vp.RateID is null;
				
			IF p_Preference = 0
			THEN		
			delete tblVendorPreference FROM tblVendorPreference INNER JOIN(
				select vr.RateId FROM tblVendorRate_ vr)
				tr on tr.RateId=tblVendorPreference.RateID WHERE tblVendorPreference.AccountId = p_AccountId
	            AND tblVendorPreference.TrunkID = p_TrunkId;		
			END IF;	
	
			
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END