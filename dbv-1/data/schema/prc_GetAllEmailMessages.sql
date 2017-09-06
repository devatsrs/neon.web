CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAllEmailMessages`(
	IN `p_companyid` INT,
	IN `p_UserID` INT,
	IN `p_isadmin` INT,
	IN `p_EmailCall` VARCHAR(50),
	IN `p_Search` VARCHAR(200),
	IN `p_Unread` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT


)
    DETERMINISTIC
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	IF p_EmailCall = 1 
	THEN
         SELECT
           	   ae.AccountEmailLogID,
           	   ae.EmailfromName,
           	   ae.Subject,           	   
           	   ae.AttachmentPaths,
           	   tblMessages.created_at,              
               ae.AccountID,
               tblMessages.HasRead              
         FROM
				tblMessages
			INNER JOIN
					 AccountEmailLog ae
			ON 
				ae.AccountEmailLogID = tblMessages.EmailID			
         WHERE
				tblMessages.CompanyID = p_companyid        
				AND ae.EmailCall = p_EmailCall
				AND (p_isadmin = 1 OR (p_isadmin = 0 AND tblMessages.MsgLoggedUserID = p_UserID)) 
				AND (p_Search = '' OR (ae.Subject like Concat('%',p_Search,'%') OR ae.Message like Concat('%',p_Search,'%')))
				AND (p_Unread = 0 OR (p_Unread = 1 AND tblMessages.HasRead=0))				
		   ORDER BY
				tblMessages.MsgID desc      
         LIMIT
				p_RowspPage OFFSET p_PageNumber;
          
		  	  
         SELECT
				COUNT(tblMessages.MsgID) as totalcount
		   FROM tblMessages
				INNER JOIN AccountEmailLog ae
				ON ae.AccountEmailLogID = tblMessages.EmailID
		   WHERE tblMessages.CompanyID = p_companyid
				AND ae.EmailCall = p_EmailCall
				AND  (p_isadmin = 1 OR (p_isadmin = 0 AND tblMessages.MsgLoggedUserID = p_UserID))
				AND (p_Search = '' OR (ae.Subject like Concat('%',p_Search,'%') OR ae.Message like Concat('%',p_Search,'%')))
				AND (p_Unread = 0 OR (p_Unread = 1 AND tblMessages.HasRead=0));
   	END IF;
	 	IF p_EmailCall = 0 
		THEN
		SELECT
           	   ae.AccountEmailLogID as AccountEmailLogID,
           	   ae.EmailfromName as EmailfromName,
           	   ae.Subject as Subject,           	   
           	   ae.AttachmentPaths as AttachmentPaths, 
           	   ae.created_at as created_at,
               ae.CreatedBy as CreatedBy,
               ae.AccountID as AccountID,
               ae.EmailTo as EmailTo
      FROM
				AccountEmailLog ae						
      WHERE
				ae.CompanyID = p_companyid        
				AND ae.EmailCall = p_EmailCall
				AND  (p_isadmin = 1 OR (p_isadmin = 0 AND ae.UserID = p_UserID))  
				AND (p_Search = '' OR (ae.Subject like Concat('%',p_Search,'%') OR ae.Message like Concat('%',p_Search,'%')))
		ORDER BY
				ae.AccountEmailLogID desc      
      LIMIT
				p_RowspPage OFFSET p_PageNumber;
          
		  	  
         SELECT
				COUNT(ae.AccountEmailLogID) as totalcount
		   FROM AccountEmailLog ae
		   WHERE ae.CompanyID = p_companyid
				AND ae.EmailCall = p_EmailCall
				AND  (p_isadmin = 1 OR (p_isadmin = 0 AND ae.UserID = p_UserID))  
				AND (p_Search = '' OR (ae.Subject like Concat('%',p_Search,'%') OR ae.Message like Concat('%',p_Search,'%')));
	  END IF;  
			IF p_EmailCall = 2 
		THEN
				
			  SELECT
           	   ae.AccountEmailLogID,
           	   ae.EmailTo,
           	   ae.Subject,           	   
           	   ae.AttachmentPaths,
           	   ae.created_at           
            FROM
				AccountEmailLog ae						
            WHERE
				 ae.CompanyID = p_companyid   
				AND ae.EmailCall = p_EmailCall
				AND  (p_isadmin = 1 OR (p_isadmin = 0 AND ae.UserID = p_UserID)) 
				AND (p_Search = '' OR (ae.Subject like Concat('%',p_Search,'%')) OR (ae.Message like Concat('%',p_Search,'%')))			
		    order by
				ae.AccountEmailLogID desc      
           LIMIT
				p_RowspPage OFFSET p_PageNumber;
				
				  
           SELECT
				COUNT(ae.AccountEmailLogID) as totalcount
		   FROM AccountEmailLog ae
		   WHERE ae.CompanyID = p_companyid
				AND ae.EmailCall = p_EmailCall
				AND  (p_isadmin = 1 OR (p_isadmin = 0 AND ae.UserID = p_UserID))  
				AND (p_Search = '' OR (ae.Subject like Concat('%',p_Search,'%')) OR (ae.Message like Concat('%',p_Search,'%')));
				
			END IF; 	 
			
			
          SELECT
				COUNT(tblMessages.MsgID) as totalcountInbox
		   FROM tblMessages
				INNER JOIN AccountEmailLog ae
				ON ae.AccountEmailLogID = tblMessages.EmailID
	       WHERE
				 tblMessages.CompanyID = p_companyid
				 AND ae.EmailCall = 1
				 AND  (p_isadmin = 1 OR (p_isadmin = 0 AND tblMessages.MsgLoggedUserID = p_UserID))
				 AND  tblMessages.HasRead=0;
				 
				 
			SELECT
				COUNT(ae.AccountEmailLogID) as TotalCountDraft
		   FROM AccountEmailLog ae			
	       WHERE				
				 ae.CompanyID = p_companyid
				 AND ae.EmailCall = 2
				 AND  (p_isadmin = 1 OR (p_isadmin = 0 AND ae.UserID = p_UserID));
			
			
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END