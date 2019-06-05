USE `speakintelligentBilling`;



DROP PROCEDURE IF EXISTS `prc_updateTempCDRTimeZones`;
DELIMITER //
CREATE PROCEDURE `prc_updateTempCDRTimeZones`(
	IN `p_tbltempusagedetail_name` TEXT
)
ThisSP:BEGIN

	DECLARE v_timezones_count_ int;
	DECLARE v_pointer_ int;
	DECLARE v_rowCount_ int;
	DECLARE v_TimezonesID_ int;

	DROP TEMPORARY TABLE IF EXISTS tmp_timezones;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_timezones (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TimezonesID INT(11),
		Title VARCHAR(50),
		FromTime VARCHAR(10),
		ToTime VARCHAR(10),
		DaysOfWeek VARCHAR(100),
		DaysOfMonth VARCHAR(100),
		Months VARCHAR(100),
		ApplyIF VARCHAR(100)
	);

	SELECT COUNT(*) INTO v_timezones_count_ FROM speakintelligentRM.tblTimezones WHERE `Status`=1;

	IF v_timezones_count_ > 1
	THEN

		INSERT INTO tmp_timezones (TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF)
		SELECT
			TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF
		FROM
			speakintelligentRM.tblTimezones
		WHERE
			`Status`=1
		ORDER BY
			TimezonesID DESC;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_timezones);

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_TimezonesID_ = (SELECT TimezonesID FROM tmp_timezones t WHERE t.RowID = v_pointer_);

			SET @stm = CONCAT("
				UPDATE
					speakintelligentCDR.",p_tbltempusagedetail_name," temp
				JOIN
					tmp_timezones t ON t.TimezonesID = ",v_TimezonesID_,"
				SET
					temp.TimezonesID = ",v_TimezonesID_,"
				WHERE
				(
					(temp.TimezonesID = '' OR temp.TimezonesID IS NULL)
					AND
					(
						(t.FromTime = '' AND t.ToTime = '')
						OR
						(
							(
								t.ApplyIF = 'start' AND
								CAST(DATE_FORMAT(connect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
							)
							OR
							(
								t.ApplyIF = 'end' AND
								CAST(DATE_FORMAT(disconnect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
							)
							OR
							(
								t.ApplyIF = 'both' AND
								CAST(DATE_FORMAT(connect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME) AND
								CAST(DATE_FORMAT(disconnect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
							)
						)
					)
					AND
					(
						t.Months = ''
						OR
						(
							(
								t.ApplyIF = 'start' AND
								FIND_IN_SET(MONTH(connect_time), Months) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND
								FIND_IN_SET(MONTH(disconnect_time), Months) != 0
							)
							OR
							(
								t.ApplyIF = 'both' AND
								FIND_IN_SET(MONTH(connect_time), Months) != 0 AND
								FIND_IN_SET(MONTH(disconnect_time), Months) != 0
							)
						)
					)
					AND
					(
						t.DaysOfMonth = ''
						OR
						(
							(
								t.ApplyIF = 'start' AND
								FIND_IN_SET(DAY(connect_time), DaysOfMonth) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND
								FIND_IN_SET(DAY(disconnect_time), DaysOfMonth) != 0
							)
							OR
							(
								t.ApplyIF = 'both' AND
								FIND_IN_SET(DAY(connect_time), DaysOfMonth) != 0 AND
								FIND_IN_SET(DAY(disconnect_time), DaysOfMonth) != 0
							)
						)
					)
					AND
					(
						t.DaysOfWeek = ''
						OR
						(
							(
								t.ApplyIF = 'start' AND
								FIND_IN_SET(DAYOFWEEK(connect_time), DaysOfWeek) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND
								FIND_IN_SET(DAYOFWEEK(disconnect_time), DaysOfWeek) != 0
							)
							OR
							(
								t.ApplyIF = 'both' AND
								FIND_IN_SET(DAYOFWEEK(connect_time), DaysOfWeek) != 0 AND
								FIND_IN_SET(DAYOFWEEK(disconnect_time), DaysOfWeek) != 0
							)
						)
					)
				)
			");

			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

	ELSE

		SET @stm = CONCAT("
			UPDATE speakintelligentCDR.",p_tbltempusagedetail_name," SET TimezonesID=1
		");

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

END//
DELIMITER ;