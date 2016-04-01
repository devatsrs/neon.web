CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUsageDetailbyProcessID`(IN `p_ProcessID` VARCHAR(200)

)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailsProcess_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetailsProcess_(
			CompanyID int,
			CompanyGatewayID int,
			AccountID int,
			trunk varchar(50),			
			area_prefix varchar(50),			
			pincode varchar(50),
			extension VARCHAR(50),
			UsageDetailID int,
			duration int,
			billed_duration int,
			cli varchar(100),
			cld varchar(100),
			cost float,
			connect_time datetime,
			disconnect_time datetime
	);
	INSERT INTO tmp_tblUsageDetailsProcess_
	 SELECT
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.AccountID,
		trunk,
		area_prefix,
		pincode,
		extension,
		UsageDetailID,
		duration,
		billed_duration,
		cli,
		cld,
		cost,
		connect_time,
		disconnect_time
	FROM RMCDR3.tblUsageDetails ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.AccountID is not null
	AND  ud.ProcessID = p_ProcessID;

END