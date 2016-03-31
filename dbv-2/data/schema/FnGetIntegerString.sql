CREATE DEFINER=`neon-user`@`122.129.78.153` FUNCTION `FnGetIntegerString`(`str` VARCHAR(50)) RETURNS char(32) CHARSET utf8 COLLATE utf8_unicode_ci
    NO SQL
BEGIN
DECLARE i, len SMALLINT DEFAULT 1;
  DECLARE ret CHAR(32) DEFAULT '';
  DECLARE c CHAR(1);

  IF str IS NULL
  THEN 
    RETURN "";
  END IF; 

  SET len = CHAR_LENGTH( str );
  REPEAT
    BEGIN
      SET c = MID( str, i, 1 );
      IF c BETWEEN '0' AND '9' THEN 
        SET ret=CONCAT(ret,c);
      END IF;
      SET i = i + 1;
    END;
  UNTIL i > len END REPEAT;
  RETURN ret;
END