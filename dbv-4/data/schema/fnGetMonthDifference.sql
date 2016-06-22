CREATE DEFINER=`root`@`localhost` FUNCTION `fnGetMonthDifference`(`p_Date1` DATE, `p_Date2` DATE) RETURNS int(11)
BEGIN

DECLARE v_Month INT;

SELECT 12 * (YEAR(p_Date2) - YEAR(p_Date1)) + MONTH(p_Date2) -  MONTH(p_Date1) INTO v_Month;

RETURN v_Month;

END