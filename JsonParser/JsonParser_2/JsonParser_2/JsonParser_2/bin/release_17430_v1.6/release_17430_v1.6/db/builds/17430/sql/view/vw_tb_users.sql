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
       return;
  else 
       lc_statment := q'[ create or replace view vw_tb_users as
                           select u.id, u.p_username as p_code, u.p_username as p_longname
                             from tb_users u
                       ]';
       execute immediate lc_statment;
       -- execute immediate replace( 'create public synonym VW_TB_USERS for #schema#.VW_TB_USERS', '#schema#', lv_schema );
  end if;
end;
/