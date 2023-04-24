declare
  ln_cnt number;
begin
  select count( * )
    into ln_cnt
    from tb_dict_database d 
   where d.p_code = 'DataLake';
  --
  if ( ln_cnt = 0 ) then
       insert into tb_dict_database ( id, p_code, p_longname, p_id )
       values ( ( select nvl( max( id ), 0 ) + 1 from aml_user.tb_dict_database ), 'DataLake', 'Данные из JSON', 'DataLake');
       commit;
  end if;     
end;
/  
