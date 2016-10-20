CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUsageDetailbyUsageHeaderID`(
	p_UsageHeaderID INT

)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailswithHeader_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetailswithHeader_(
			CompanyID int,
			CompanyGatewayID int,
			AccountID int,
			trunk varchar(50),			
			area_prefix varchar(50),			
			UsageDetailID int,
			duration int,
			billed_duration int,
			cli varchar(100),
			cld varchar(100),
			cost float,
			connect_time datetime,
			disconnect_time datetime
	);
	INSERT INTO tmp_tblUsageDetailswithHeader_
	 SELECT
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.AccountID,
		trunk,
		area_prefix,
		UsageDetailID,
		duration,
		billed_duration,
		cli,
		cld,
		cost,
		connect_time,
		disconnect_time
	FROM NeonCDRDev.tblUsageDetails ud
	INNER JOIN NeonCDRDev.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.AccountID is not null
	AND  uh.UsageHeaderID = p_UsageHeaderID;

END