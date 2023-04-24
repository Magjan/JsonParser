declare
  ln_cnt      number := 0;
  lc_statment clob;
  lv_schema   varchar2( 512 ) := 'AML_USER';
begin
  --
  select count( * )
    into ln_cnt
    from all_objects o
   where o.owner = 'PUBLIC'
     and o.object_type = 'SYNONYM'
     and o.object_name = upper( 'pkg_upload_proc_utils' );
  --
  if ( ln_cnt = 0 ) then
       -- execute immediate replace( 'create public synonym PKG_UPLOAD_PROC_UTILS for #schema#.PKG_UPLOAD_PROC_UTILS', '#schema#', lv_schema );
       execute immediate 'create public synonym PKG_UPLOAD_PROC_UTILS for PKG_UPLOAD_PROC_UTILS';
  end if;
end;
/