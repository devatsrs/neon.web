USE Ratemanagement3;

update tblCronJob set Status = 0 where CronJobCommandID IN (SELECT CronJobCommandID FROM tblCronJobCommand where Command = 'createvendorsummary');
update tblCronJobCommand set Status = 0 where Command = 'createvendorsummary';
update tblCronJobCommand set Settings = '[[{"title":"Start Date","type":"text","datepicker":"","value":"","name":"StartDate"},{"title":"End Date","type":"text","value":"","datepicker":"","name":"EndDate"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"}]]' where Command = 'createsummary';