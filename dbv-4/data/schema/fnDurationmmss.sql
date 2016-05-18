CREATE DEFINER=`root`@`localhost` FUNCTION `fnDurationmmss`(`p_Duration` INT) RETURNS varchar(50) CHARSET utf8 COLLATE utf8_unicode_ci
BEGIN

DECLARE v_FormattedDuration_ VARCHAR(50);

SELECT CONCAT(FLOOR(p_Duration/60), ':' ,p_Duration % 60) INTO v_FormattedDuration_;

RETURN v_FormattedDuration_;
END