CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateVendorSippySheet`(IN `p_VendorID` INT  , IN `p_Trunks` varchar(200))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
         
        call vwVendorSippySheet(p_VendorID,p_Trunks); 
        SELECT 
				 `Action [A|D|U|S|SA`,
                id ,
                vendorRate.Prefix,
                COUNTRY,
                Preference ,
                `Interval 1` ,
                `Interval N` ,
                `Price 1` ,
                `Price N` ,
                `1xx Timeout` ,
                `2xx Timeout` ,
                `Huntstop` ,
                Forbidden ,
                `Activation Date` ,
                `Expiration Date`
        FROM    tmp_VendorSippySheet_ vendorRate     
        WHERE   vendorRate.AccountId = p_VendorID
        And  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0;
        
      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END