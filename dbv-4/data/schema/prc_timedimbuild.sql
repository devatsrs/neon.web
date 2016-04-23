CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_timedimbuild`()
BEGIN
    DECLARE v_full_date DATETIME;

    DELETE FROM tblDimTtime;

    SET v_full_date = '2009-03-01 00:00:00';
    WHILE v_full_date < '2009-03-02 00:00:00' DO

        INSERT INTO tblDimTtime (
            fulltime ,
            hour ,
            minute ,
            second ,
            ampm
        ) VALUES (
            TIME(v_full_date),
            HOUR(v_full_date),
            MINUTE(v_full_date),
            SECOND(v_full_date),
            DATE_FORMAT(v_full_date,'%p')
        );

        SET v_full_date = DATE_ADD(v_full_date, INTERVAL 1 SECOND);
    END WHILE;
END