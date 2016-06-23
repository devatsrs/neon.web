CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAjaxActionList`(IN `p_CompanyID` INT, IN `p_ResourceCategoryID` LONGTEXT, IN `p_action` int)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	/*  get Resource categories from user id */
	IF p_action = 1
    THEN
	select distinct r.ResourceID,r.ResourceName,
		case when rcm.ResourceID>0
		then
		1
		else
		null
		end As Checked 
	From `tblResource` r left join  `tblResourceCategoryMapping` rcm on r.ResourceID=rcm.ResourceID and (FIND_IN_SET(rcm.ResourceCategoryID,p_ResourceCategoryID) != 0 ) 
		where r.CompanyID=p_CompanyID
	order by
		 case when rcm.ResourceID>0
	 then
		1
     else
	    null
     end
	  desc, ResourceName asc;
	
	 END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END