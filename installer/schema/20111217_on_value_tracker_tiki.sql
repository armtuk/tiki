update `tiki_tracker_item_fields` ttif left join `tiki_tracker_fields` ttf on (ttif.`fieldId`=ttf.`fieldId`) set ttif.`value`='y' where ttf.`type`='c' and ttif.`value`='on';
update `tiki_tracker_item_fields` ttif left join `tiki_tracker_fields` ttf on (ttif.`fieldId`=ttf.`fieldId`) set ttif.`value`='n' where ttf.`type`='c' and ttif.`value`='off';