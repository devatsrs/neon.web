CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUsageDetail`(IN `p_CompanyID` int , IN `p_AccountID` int , IN `p_GatewayID` int , IN `p_StartDate` datetime , IN `p_EndDate` datetime , IN `p_UserID` INT , IN `p_isAdmin` INT, IN `p_billing_time` INT   

)
BEGIN
		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetails_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetails_(
			AccountID int,
			AccountName varchar(50),
			trunk varchar(50),
			area_prefix varchar(50),
			pincode VARCHAR(50),
			UsageDetailID int,
			duration int,
			billed_duration int,
			cli varchar(100),
			cld varchar(100),
			cost decimal(18,6),
			connect_time datetime,
			disconnect_time datetime
	);
	INSERT INTO tmp_tblUsageDetails_
	SELECT
    *
	FROM (SELECT
		uh.AccountID,
		a.AccountName,
		trunk,
		area_prefix,
		pincode,
		UsageDetailID,
		duration,
		billed_duration,
		cli,
		cld,
		cost,
		connect_time,
		disconnect_time
	FROM RMCDR3.tblUsageDetails  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	LEFT JOIN Ratemanagement3.tblAccount a
		ON uh.AccountID = a.AccountID
	WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
	AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
	AND uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID)) 
	) tbl
	WHERE 
	
	(p_billing_time =1 and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR 
	(p_billing_time =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	AND billed_duration > 0;
END