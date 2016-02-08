CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAjaxResourceList`(IN `p_CompanyID` INT, IN `p_userid` LONGTEXT, IN `p_roleids` LONGTEXT, IN `p_action` int)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	/*  get Resource categories from user id */
	IF p_action = 1
    THEN

        select distinct tblResourceCategories.ResourceCategoryID,tblResourceCategories.ResourceCategoryName,usrper.AddRemove,rolres.Checked
			from tblResourceCategories
			LEFT OUTER JOIN(
			select distinct rs.ResourceID, rs.ResourceName,usrper.AddRemove
			from tblResource rs
			inner join tblUserPermission usrper on usrper.resourceID = rs.ResourceID and FIND_IN_SET(usrper.UserID ,p_userid) != 0 ) usrper
			on usrper.ResourceID = tblResourceCategories.ResourceCategoryID
			LEFT OUTER JOIN(
			select distinct res.ResourceID, res.ResourceName,'true' as Checked
			from `tblResource` res
			inner join `tblRolePermission` rolper on rolper.resourceID = res.ResourceID and FIND_IN_SET(rolper.roleID ,p_roleids) != 0) rolres
			on rolres.ResourceID = tblResourceCategories.ResourceCategoryID
			where tblResourceCategories.CompanyID= p_CompanyID order by rolres.Checked desc,usrper.AddRemove desc,tblResourceCategories.ResourceCategoryName;
    
	
	END IF;
	/*  get Resource categories from RoleID */
	IF p_action = 2
	THEN

		select  distinct `tblResourceCategories`.`ResourceCategoryID`, `tblResourceCategories`.`ResourceCategoryName`, tblRolePermission.resourceID as Checked ,case when tblRolePermission.resourceID>0
	 then
		1
     else
	    0
     end as check2
		from tblResourceCategories left join tblRolePermission on `tblRolePermission`.`resourceID` = `tblResourceCategories`.`ResourceCategoryID` 
		and `tblRolePermission`.`CompanyID` = p_CompanyID and FIND_IN_SET(`tblRolePermission`.`roleID`,p_roleids) != 0
		order by
		case when tblRolePermission.resourceID>0
	 then
		1
     else
	    0
     end
	  desc,`tblResourceCategories`.`ResourceCategoryName` asc;
	
	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END