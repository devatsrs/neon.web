USE `Ratemanagement3`;

-- Sage pay Integration
INSERT INTO `tblIntegration` (`CompanyId`, `Title`, `Slug`, `ParentID`) VALUES ('1', 'SagePay', 'sagepay', '4');


Delimiter ;;
CREATE PROCEDURE `prc_ManualImportAccount`(
	IN `p_ProcessID` VARCHAR(50)




)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	Declare v_IsVendor  int ;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_Accounts;
	CREATE TEMPORARY TABLE tmp_Accounts Ratemanagement3(
		AccountID int,
		AccountName varchar(100),
		IP varchar(250)
	);



	SET v_IsVendor = 1;

	-- select accounts from tblTempAccount which Account Name match with tblAccount
	insert into tmp_Accounts (AccountID,AccountName,IP)
	select a.AccountID, a.AccountName, tmp.IP
	from tblTempAccount tmp
	inner join tblAccount a on tmp.AccountName like concat (a.AccountName ,'%')
	where  tmp.ProcessID = p_ProcessID
			and a.CompanyID=1
			and tmp.IP is not null
			and a.AccountID is not null
	group by a.AccountID,tmp.IP;


	if (v_IsVendor = 1) THEN

			-- account match with comma seperated IPs , ip not exists
			/*select tmpa.AccountID, tmpa.AccountName, GROUP_CONCAT(tmpa.IP) as IPs
			from tmp_Accounts tmpa
			left join tblAccountAuthenticate auth on auth.CustomerAuthRule='IP' and FIND_IN_SET(tmpa.IP,auth.CustomerAuthValue)>0
			where auth.AccountID is null
			group by tmpa.AccountID,tmpa.AccountName;
			*/

			Update tblAccountAuthenticate autha
			inner join
			(
				select tmpa.AccountID, GROUP_CONCAT(tmpa.IP) as IPs
				from tmp_Accounts tmpa
				left join tblAccountAuthenticate auth on auth.CustomerAuthRule='IP' and FIND_IN_SET(tmpa.IP,auth.CustomerAuthValue)>0
				where auth.AccountID is null
				group by tmpa.AccountID
			) tmp on autha.AccountID = tmp.AccountID
			SET
			VendorAuthRule = 'IP',
			VendorAuthValue = tmp.IPs
			;

			-- Updat Account Vendor = 1
			update tblAccount a
			inner join
			(
				select tmpa.AccountID, GROUP_CONCAT(tmpa.IP) as IPs
				from tmp_Accounts tmpa
				left join tblAccountAuthenticate auth on auth.CustomerAuthRule='IP' and FIND_IN_SET(tmpa.IP,auth.CustomerAuthValue)>0
				where auth.AccountID is null
				group by tmpa.AccountID
			) tmp on a.AccountID = tmp.AccountID
			SET
			IsVendor = 1;



	END IF;


		-- select accounts which IPs already exists
			select tmpa.AccountID, tmpa.AccountName, GROUP_CONCAT(tmpa.IP) as IPs, 'IP Already Exists' as Skipped
			from tmp_Accounts tmpa
			left join tblAccountAuthenticate auth on auth.CustomerAuthRule='IP' and FIND_IN_SET(tmpa.IP,auth.CustomerAuthValue)>0
			where auth.AccountID is not null
			group by tmpa.AccountID,tmpa.AccountName;











END;;
Delimiter ;