CREATE DEFINER=`root`@`localhost` FUNCTION `fnGetCountryIdByCodeAndCountry`(`p_code` TEXT, `p_country` TEXT) RETURNS int(11)
BEGIN

DECLARE v_countryId int;

DROP TEMPORARY TABLE IF EXISTS tmp_Prefix;    
CREATE TEMPORARY TABLE tmp_Prefix (Prefix varchar(500) , CountryID int);

INSERT INTO tmp_Prefix 
SELECT DISTINCT
 tblCountry.Prefix,
 tblCountry.CountryID
FROM tblCountry
LEFT OUTER JOIN
 (
	SELECT
	 Prefix
	FROM tblCountry
	GROUP BY Prefix
	HAVING COUNT(*) > 1) d 
	ON tblCountry.Prefix = d.Prefix
Where (p_code LIKE CONCAT(tblCountry.Prefix, '%') AND d.Prefix IS NULL)
	 OR (p_code LIKE CONCAT(tblCountry.Prefix, '%') AND d.Prefix IS NOT NULL 
 		AND (p_country LIKE CONCAT('%', tblCountry.Country, '%'))) ;


DROP TEMPORARY TABLE IF EXISTS tmp_PrefixCopy;  
CREATE TEMPORARY TABLE tmp_PrefixCopy (SELECT * FROM tmp_Prefix);

SELECT DISTINCT p.CountryID   INTO v_countryId
FROM tmp_Prefix  p 
JOIN (
		SELECT MAX(Prefix) AS  Prefix 
		 FROM  tmp_PrefixCopy 
	  ) AS MaxPrefix
	 ON MaxPrefix.Prefix = p.Prefix Limit 1;
	 

RETURN v_countryId;
END