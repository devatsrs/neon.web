CREATE DEFINER=`root`@`localhost` FUNCTION `FnGetAccountNumber`(`p_companyId` INT, `p_number` INT) RETURNS varchar(50) CHARSET utf8 COLLATE utf8_unicode_ci
    NO SQL
    DETERMINISTIC
    COMMENT 'Return Next Account Number'
BEGIN
DECLARE lastnumber VARCHAR(50);
DECLARE found_val INT(11);

set lastnumber = (select cs.`Value` FROM tblCompanySetting cs where cs.`Key` = 'LastAccountNo' AND cs.CompanyID = p_companyId);

IF p_number =0
THEN
 set found_val = (select count(*) as total_res from tblAccount where Number=lastnumber);
END IF;

IF p_number != 0
THEN
 set found_val = (select count(*) as total_res from tblAccount where Number=p_number);
 IF found_val=0 THEN
 	SET lastnumber= p_number;
 END IF;	
END IF;


IF found_val>0 then
 SET lastnumber = NULL;
#WHILE found_val>0 DO
#	set lastnumber = lastnumber+1;
#	set found_val = (select count(*) as total_res from tblAccount where Number=lastnumber);
#END WHILE;
END IF;

 return lastnumber;
END