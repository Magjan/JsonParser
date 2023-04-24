begin
  delete from tb_report_parameters rp where rp.report_id = ( select r.id from tb_reports r where r.report_name = 'Действия над операциями от 10.2022' );
  delete from tb_reports r where r.report_name = 'Действия над операциями от 10.2022';
  commit;
end;
/  
