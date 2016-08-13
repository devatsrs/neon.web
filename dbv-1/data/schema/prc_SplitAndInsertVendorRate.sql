CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_SplitAndInsertVendorRate`(IN `TempVendorRateID` INT, IN `Code` VARCHAR(200))
BEGIN

	DECLARE v_First_ INT;
	DECLARE v_Last_ INT;	
		
	SELECT  REPLACE(SUBSTRING(SUBSTRING_INDEX(Code, '-', 1)
                 , LENGTH(SUBSTRING_INDEX(Code, '-', 0)) + 1)
                 , '-'
                 , '') INTO v_First_;
                 
	 SELECT REPLACE(SUBSTRING(SUBSTRING_INDEX(Code, '-', 2)
                 , LENGTH(SUBSTRING_INDEX(Code, '-', 1)) + 1)
                 , '-'
                 , '') INTO v_Last_;                 
	
	
   WHILE v_Last_ >= v_First_
	DO
   	 INSERT my_splits VALUES (TempVendorRateID,v_Last_);
	    SET v_Last_ = v_Last_ - 1;
  END WHILE;
	
END