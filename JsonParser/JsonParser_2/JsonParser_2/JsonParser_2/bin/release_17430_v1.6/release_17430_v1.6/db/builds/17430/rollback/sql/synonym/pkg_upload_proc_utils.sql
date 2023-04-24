declare
  ln_cnt      number := 0;
begin
  --
  select count( * )
    into ln_cnt
    from all_objects o
   where o.owner = 'PUBLIC'
     and o.object_type = 'SYNONYM'
     and o.object_name = upper( 'pkg_upload_proc_utils' );
  --
  if ( ln_cnt > 0 ) then
       execute immediate 'drop public synonym PKG_UPLOAD_PROC_UTILS';
  end if;
end;
/