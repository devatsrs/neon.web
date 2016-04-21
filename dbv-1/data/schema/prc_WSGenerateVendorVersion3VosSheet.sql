CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateVendorVersion3VosSheet`(IN `p_VendorID` INT , IN `p_Trunks` VARCHAR(200), IN `p_Effective` VARCHAR(50))
BEGIN
         SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
         
        call vwVendorVersion3VosSheet(p_VendorID,p_Trunks,p_Effective);
        SELECT  `Rate Prefix` ,
                `Area Prefix` ,
                `Rate Type` ,
                `Area Name` ,
                `Billing Rate` ,
                `Billing Cycle`,
                `Minute Cost` ,
                `Lock Type` ,
                `Section Rate` ,
                `Billing Rate for Calling Card Prompt` ,
                `Billing Cycle for Calling Card Prompt`
        FROM    tmp_VendorVersion3VosSheet_
        WHERE   AccountID = p_VendorID
        AND  FIND_IN_SET(TrunkId,p_Trunks)!= 0
        ORDER BY `Rate Prefix`;
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END