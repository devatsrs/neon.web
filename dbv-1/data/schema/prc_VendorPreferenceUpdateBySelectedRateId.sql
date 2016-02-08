CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorPreferenceUpdateBySelectedRateId`(IN `p_CompanyId` INT, IN `p_AccountId` LONGTEXT , IN `p_RateIDList` LONGTEXT , IN `p_TrunkId` VARCHAR(100) , IN `p_Preference` INT, IN `p_ModifiedBy` VARCHAR(50)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	     
    UPDATE  tblVendorPreference v
   INNER JOIN (
    select  VendorPreferenceID,RateId from tblVendorPreference
      where AccountId = p_AccountId and TrunkID = p_TrunkId and   FIND_IN_SET (RateId,p_RateIDList) != 0 
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
    SELECT  
            r.RateID,
         p_AccountId ,
         p_TrunkId,
         p_Preference ,
         p_ModifiedBy,
         NOW()
      FROM    ( 
            SELECT    
                distinct tblRate.RateID ,
                     a.AccountId,
                     tblRate.CompanyID
         FROM tblRate ,
         ( select AccountId from tblAccount
            where AccountId = p_AccountId 
         ) a
         WHERE   FIND_IN_SET (tblRate.RateID,p_RateIDList) != 0    
         ) r
      LEFT JOIN (  
            select  VendorPreferenceID,RateId,AccountId from tblVendorPreference
         where AccountId = p_AccountId 
                    and TrunkID = p_TrunkId 
        ) cr ON r.RateID = cr.RateID
        AND r.AccountId = cr.AccountId
         AND r.CompanyID = p_CompanyId   
      WHERE r.CompanyID = p_CompanyId       
            and cr.RateID is NULL;
  
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END