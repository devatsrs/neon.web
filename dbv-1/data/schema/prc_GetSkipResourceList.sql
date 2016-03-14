CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetSkipResourceList`(IN `p_CompanyID` INT)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	/*  get skip allow permission list */
	select distinct r.ResourceID,r.ResourceValue,
		case when cs.CompanyID>0
		then
		1
		else
		null
		end As Checked 
	From `tblResource` r left join  `tblCompanySetting` cs on (FIND_IN_SET(r.ResourceID,cs.`Value`) != 0 ) and  r.CompanyID=cs.CompanyID and cs.`Key`='SkipPermissionAction'
		where r.CompanyID=p_CompanyID 
	order by
		 case when cs.CompanyID>0
	 then
		1
     else
	    null
     end
	  desc, ResourceName asc;	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END