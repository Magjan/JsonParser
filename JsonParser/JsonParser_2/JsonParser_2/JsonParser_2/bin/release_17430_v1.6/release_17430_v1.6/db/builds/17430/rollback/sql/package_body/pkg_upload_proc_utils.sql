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
     and o.object_type = 'PACKAGE BODY'
     and o.object_name = upper( 'PKG_UPLOAD_PROC_UTILS' );
  --
  if ( ln_cnt > 0 ) then
       lc_statment := ' drop package body pkg_upload_proc_utils';
       execute immediate lc_statment;
  end if;
end;
/