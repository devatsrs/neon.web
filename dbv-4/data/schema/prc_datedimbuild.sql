CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_datedimbuild`(IN `p_start_date` DATE, IN `p_end_date` DATE)
BEGIN
    DECLARE v_full_date DATE;

     DELETE FROM tblDimDate;

    SET v_full_date = p_start_date;
    WHILE v_full_date < p_end_date DO

        INSERT INTO tblDimDate (
            	`date`,
					`day`,
					`day_abbrev`,
					`day_of_week`,
					`day_of_month`,
					`day_of_quarter`,
					`day_of_semester`,
					`day_of_year`,
					`weekend`,
					`week_of_month`,
					`week_of_quarter`,
					`week_of_semester`,
					`week_of_year`,
					`month`,
					`month_abbrev`,
					`month_of_quarter`,
					`month_of_year`,
					`quarter_of_year`,
					`previous_day`,
					`next_day`,
					`start_week`,
					`end_week`,
					`start_month`,
					`end_month`,
					`start_quarter`,
					`end_quarter`,
					`year`,
					`week_month_name`,
					`week_quarter_name`,
					`week_year_name`,
					`month_quarter_name`,
					`month_year_name`,
					`quarter_year_name`
        ) VALUES (
        			v_full_date,
					DATE_FORMAT( v_full_date , "%W" ),
					DATE_FORMAT( v_full_date , "%a"),
					DAYOFWEEK(v_full_date),
					DAYOFMONTH(v_full_date),
					NULL,
					NULL,
					DAYOFYEAR(v_full_date),
					IF( DATE_FORMAT( v_full_date, "%W" ) IN ('Saturday','Sunday'), 'Weekend', 'Weekday'),
					WEEK(v_full_date) - WEEK(DATE_ADD(v_full_date,INTERVAL -DAY(v_full_date)+1 DAY)),
					NULL,
					NULL,
					WEEKOFYEAR(v_full_date),
					DATE_FORMAT( v_full_date , "%M"),
					DATE_FORMAT( v_full_date , "%b"),
					MOD(MONTH('2016-02-20')-1,3)+1,
					MONTH(v_full_date),
					QUARTER(v_full_date),
					DATE_ADD(v_full_date, INTERVAL -1 DAY),
					DATE_ADD(v_full_date, INTERVAL +1 DAY),
					v_full_date,
					v_full_date,
					v_full_date,
					v_full_date,
					v_full_date,
					v_full_date,
					YEAR(v_full_date),
					CONCAT(WEEK(v_full_date) - WEEK(DATE_ADD(v_full_date,INTERVAL -DAY(v_full_date)+1 DAY)),'-',DATE_FORMAT( v_full_date , "%M")),
					NULL,
					NULL,
					NULL,
					NULL,
					NULL
        );

        SET v_full_date = DATE_ADD(v_full_date, INTERVAL 1 DAY);
    END WHILE;
END