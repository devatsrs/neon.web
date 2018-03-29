CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_SplitAndInsertRateTableRate`(
	IN `TempRateTableRateID` INT,
	IN `Code` VARCHAR(500),
	IN `p_countryCode` VARCHAR(50)
)
BEGIN

	DECLARE v_First_ BIGINT;
	DECLARE v_Last_ BIGINT;	

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
		INSERT my_splits (TempRateTableRateID,Code,CountryCode) VALUES (TempRateTableRateID,v_Last_,p_countryCode);
		SET v_Last_ = v_Last_ - 1;
	END WHILE;

END