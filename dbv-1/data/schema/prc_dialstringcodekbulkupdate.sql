CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_dialstringcodekbulkupdate`(IN `p_dialstringid` int, IN `p_up_chargecode` int, IN `p_up_description` int, IN `p_up_forbidden` int, IN `p_critearea` int, IN `p_dialstringcode` varchar(500), IN `p_critearea_dialstring` varchar(250), IN `p_critearea_chargecode` varchar(250), IN `p_critearea_description` varchar(250), IN `p_chargecode` varchar(250), IN `p_description` varchar(250), IN `p_forbidden` varchar(250), IN `p_isAction` int )
BEGIN

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; 
   
   DROP TEMPORARY TABLE IF EXISTS tmp_dialstringcode;
    CREATE TEMPORARY TABLE tmp_dialstringcode (
      DialStringCodeID INT
    ); 
    
    IF p_critearea = 0
    THEN
    	
    		INSERT INTO tmp_dialstringcode
			  SELECT DialStringCodeID from tblDialStringCode
				where FIND_IN_SET(DialStringCodeID,p_dialstringcode)
						AND DialStringID = p_dialstringid;
    	
    END IF;
    
    IF p_critearea = 1
    THEN
    	
    		INSERT INTO tmp_dialstringcode
			  SELECT DialStringCodeID from tblDialStringCode
				where DialStringID = p_dialstringid
						AND (p_critearea_dialstring IS NULL OR DialString LIKE REPLACE(p_critearea_dialstring, '*', '%'))
		            AND (p_critearea_chargecode IS NULL OR ChargeCode LIKE REPLACE(p_critearea_chargecode, '*', '%'))
      		      AND (p_critearea_description IS NULL OR Description LIKE REPLACE(p_critearea_description, '*', '%'));
    	
    END IF;
    
    IF p_isAction = 0
    THEN
    
    	IF p_up_chargecode = 1
    	THEN
			    	
    		UPDATE tblDialStringCode
    		 INNER JOIN tmp_dialstringcode dc 
    		 	ON dc.DialStringCodeID = tblDialStringCode.DialStringCodeID
    		 SET ChargeCode = p_chargecode
			 WHERE tblDialStringCode.DialStringID = p_dialstringid;	
    	
    	END IF;
    	
    	IF p_up_description = 1
    	THEN

    		UPDATE tblDialStringCode
    		 INNER JOIN tmp_dialstringcode dc 
    		 	ON dc.DialStringCodeID = tblDialStringCode.DialStringCodeID
    		 SET Description = p_description
			 WHERE tblDialStringCode.DialStringID = p_dialstringid;	
    	
    	END IF;
    	
    	IF p_up_forbidden = 1
    	THEN
    	
    		UPDATE tblDialStringCode
    		 INNER JOIN tmp_dialstringcode dc 
    		 	ON dc.DialStringCodeID = tblDialStringCode.DialStringCodeID
    		 SET Forbidden = p_forbidden
			 WHERE tblDialStringCode.DialStringID = p_dialstringid;	
    	
    	END IF;
    
    END IF;
    
    IF p_isAction = 1
    THEN
    	
    	DELETE tblDialStringCode
    		FROM tblDialStringCode
    		 INNER JOIN( SELECT dc.DialStringCodeID FROM tmp_dialstringcode dc) dpc 
    		 	ON dpc.DialStringCodeID = tblDialStringCode.DialStringCodeID
			 WHERE tblDialStringCode.DialStringID = p_dialstringid;	
    	
    END IF;
    
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END