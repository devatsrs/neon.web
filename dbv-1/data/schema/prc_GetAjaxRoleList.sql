CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAjaxRoleList`(IN `p_CompanyID` INT, IN `p_UserID` LONGTEXT, IN `p_ResourceID` LONGTEXT, IN `p_action` int)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	/* get role from userid */
	IF p_action = 1
	THEN

	select distinct `tblRole`.`RoleID`, `tblRole`.`RoleName`, tblUserRole.RoleID as Checked,case when tblUserRole.RoleID>0
	 then
		1
     else
	    0
     end as check2
	 from tblRole 
	 left join tblUserRole on `tblUserRole`.`RoleID` = `tblRole`.`RoleID` and `tblRole`.`CompanyID` = p_CompanyID and  FIND_IN_SET(`tblUserRole`.`UserID`, p_UserID) != 0
	 order by
	 case when tblUserRole.RoleID>0
	 then
		1
     else
	    0
     end
	  desc,`tblRole`.`RoleName` asc;

	END IF;
	/* get role from ResourceCategoryID */
	IF p_action = 2
	THEN

	select distinct `tblRole`.`RoleID`, `tblRole`.`RoleName`, tblRolePermission.RoleID as Checked,case when tblRolePermission.RoleID>0
	 then
		1
     else
	    0
     end as check2
	 from tblRole 
	 left join tblRolePermission on `tblRolePermission`.`roleID` = `tblRole`.`RoleID` and `tblRolePermission`.`CompanyID` = p_CompanyID and FIND_IN_SET(`tblRolePermission`.`resourceID`,p_ResourceID) != 0
	  order by
	  case when tblRolePermission.RoleID>0
	 then
		1
     else
	    0
     end
	  desc,`tblRole`.`RoleName` asc;
	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END