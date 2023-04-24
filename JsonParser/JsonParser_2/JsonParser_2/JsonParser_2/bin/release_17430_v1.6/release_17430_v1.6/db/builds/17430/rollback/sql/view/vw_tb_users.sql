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
     and o.object_type = 'VIEW'
     and o.object_name = upper( 'vw_tb_users' );
  --
  if ( ln_cnt > 0 ) then
       lc_statment := ' drop view vw_tb_users';
       execute immediate lc_statment;
  else
       return;
  end if;
end;
/