CREATE DEFINER=`root`@`localhost` FUNCTION `fnFormateDuration`(`p_Duration` INT) RETURNS varchar(50) CHARSET utf8 COLLATE utf8_unicode_ci
BEGIN

DECLARE v_FormateDuration_ varchar(50);

select Concat( FLOOR(SUM(p_Duration ) / 60),':' , SUM(p_Duration ) % 60) into v_FormateDuration_;

RETURN v_FormateDuration_;
END