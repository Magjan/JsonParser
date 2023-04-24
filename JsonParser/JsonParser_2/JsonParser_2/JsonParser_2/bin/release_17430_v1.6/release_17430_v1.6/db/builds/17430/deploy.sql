set serveroutput on

set define off

ALTER SESSION SET CURRENT_SCHEMA = AML_USER;

WHENEVER SQLERROR EXIT FAILURE

PROMPT Installation PROCESS_TXT_OPERATIONS.sql

@@db/builds/17430/sql/table/PROCESS_TXT_OPERATIONS.sql

PROMPT Installation PROCESS_TXT_MEMBERS.sql

@@db/builds/17430/sql/table/PROCESS_TXT_MEMBERS.sql

PROMPT Installation pkg_upload_proc_utils.sql

@@db/builds/17430/sql/package/pkg_upload_proc_utils.sql

PROMPT Installation pkg_upload_proc_utils.sql

@@db/builds/17430/sql/package_body/pkg_upload_proc_utils.sql

PROMPT Installation insert_TB_DICT_DATABASE.sql

@@db/builds/17430/sql/insert_TB_DICT_DATABASE.sql

PROMPT Installation pkg_upload_proc_utils.sql

@@db/builds/17430/sql/synonym/pkg_upload_proc_utils.sql

PROMPT Installation create_statment_1.sql

@@db/builds/17430/sql/create_statment_1.sql

PROMPT Installation create_statment_2.sql

@@db/builds/17430/sql/create_statment_2.sql

PROMPT Installation vw_tb_users.sql

@@db/builds/17430/sql/view/vw_tb_users.sql

PROMPT Installation vw_tb_users.sql

@@db/builds/17430/sql/synonym/vw_tb_users.sql

PROMPT Installation create_statment_3.sql

@@db/builds/17430/sql/create_statment_3.sql
