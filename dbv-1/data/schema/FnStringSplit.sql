CREATE DEFINER=`root`@`localhost` FUNCTION `FnStringSplit`(`x` TEXT, `delim` VARCHAR(500), `pos` INTEGER) RETURNS varchar(500) CHARSET utf8 COLLATE utf8_unicode_ci
BEGIN
  DECLARE output VARCHAR(500);
  SET output = REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos)
                 , LENGTH(SUBSTRING_INDEX(x, delim, pos - 1)) + 1)
                 , delim
                 , '');
  IF output = '' THEN SET output = null; END IF;
  RETURN output;
END