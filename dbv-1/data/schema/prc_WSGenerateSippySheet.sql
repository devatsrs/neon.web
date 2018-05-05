CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_WSGenerateSippySheet`(
	IN `p_CustomerID` INT ,
	IN `p_Trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN

	-- get customer rates
	CALL vwCustomerRate(p_CustomerID,p_Trunks,p_Effective,p_CustomDate);

	SELECT 
		CASE WHEN EndDate IS NOT NULL THEN
			'SA'
		ELSE 
			'A' 
		END AS `Action [A|D|U|S|SA`,
		'' as id,
		Concat(IFNULL(Prefix,''), Code) as Prefix,
		Description as COUNTRY ,
		Interval1 as `Interval 1`,
		IntervalN as `Interval N`,
		Rate as `Price 1`,
		Rate as `Price N`,
		0  as Forbidden,
		0 as `Grace Period`,
		DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' ) AS `Activation Date`,
		DATE_FORMAT( EndDate, '%Y-%m-%d %H:%i:%s' )  AS `Expiration Date`
	FROM 
		tmp_customerrateall_
	ORDER BY 
		Prefix;

END