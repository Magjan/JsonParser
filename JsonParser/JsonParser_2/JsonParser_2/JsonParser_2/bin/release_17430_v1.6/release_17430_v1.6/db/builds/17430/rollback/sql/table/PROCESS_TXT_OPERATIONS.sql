declare
  ln_cnt      number := 0;
  lc_statment clob;
  lv_schema   varchar2( 512 ) := 'AML_USER';
begin
  --
  select count( * )
    into ln_cnt
    from all_objects o
   where o.owner = lv_schema
     and o.object_type = 'TABLE'
     and o.object_name = upper( 'PROCESS_TXT_OPERATIONS' );
  --
  if ( ln_cnt > 0 ) then
       lc_statment := ' drop table PROCESS_TXT_OPERATIONS';
       execute immediate lc_statment;
  end if;

end;
/