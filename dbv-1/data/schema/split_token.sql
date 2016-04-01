CREATE DEFINER=`root`@`localhost` FUNCTION `split_token`(txt TEXT CHARSET utf8, delimiter_text VARCHAR(255) CHARSET utf8, token_index INT UNSIGNED) RETURNS text CHARSET utf8
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'Return substring by index in delimited text'
begin
  if CHAR_LENGTH(delimiter_text) = '' then
    return SUBSTRING(txt, token_index, 1);
  else
    return SUBSTRING_INDEX(SUBSTRING_INDEX(txt, delimiter_text, token_index), delimiter_text, -1);
  end if;
end