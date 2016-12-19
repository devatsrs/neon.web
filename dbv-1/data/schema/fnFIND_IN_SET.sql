CREATE DEFINER=`root`@`localhost` FUNCTION `fnFIND_IN_SET`(
	`p_String1` LONGTEXT,
	`p_String2` LONGTEXT



) RETURNS int(11)
BEGIN
	DECLARE i int;
	DECLARE counter int;
	
	DROP TEMPORARY TABLE IF EXISTS `table1`;
	CREATE TEMPORARY TABLE `table1` (
	  `splitted_column` varchar(45) NOT NULL
	);
	
	DROP TEMPORARY TABLE IF EXISTS `table2`;
	CREATE TEMPORARY TABLE `table2` (
	  `splitted_column` varchar(45) NOT NULL
	);


	SET i = 1;
	REPEAT
		INSERT INTO table1
		SELECT NeonRMDev.FnStringSplit(p_String1, ',', i) WHERE NeonRMDev.FnStringSplit(p_String1, ',', i) IS NOT NULL LIMIT 1;
		SET i = i + 1;
		UNTIL ROW_COUNT() = 0
	END REPEAT;
	
	SET i = 1;
	REPEAT
		INSERT INTO table2
		SELECT NeonRMDev.FnStringSplit(p_String2, ',', i) WHERE NeonRMDev.FnStringSplit(p_String2, ',', i) IS NOT NULL LIMIT 1;
		SET i = i + 1;
		UNTIL ROW_COUNT() = 0
	END REPEAT;
	
	SELECT COUNT(*) INTO counter
	FROM table1 
	INNER JOIN table2 ON table1.splitted_column = table2.splitted_column;
	
	RETURN counter;

END