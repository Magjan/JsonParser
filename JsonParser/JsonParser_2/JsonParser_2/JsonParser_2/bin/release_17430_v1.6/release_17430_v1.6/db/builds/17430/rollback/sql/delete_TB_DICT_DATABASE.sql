begin
  delete from TB_DICT_DATABASE d
   where d.p_code = 'DataLake';
  commit;
end;
/