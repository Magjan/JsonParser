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
     and o.object_name = upper( 'PROCESS_TXT_MEMBERS' );
  --
  dbms_output.put_line( ln_cnt );
  --
  if ( ln_cnt = 0 ) then
       lc_statment := q'[ 
create table PROCESS_TXT_MEMBERS
(
  id                  NUMBER not null,
  p_operationid       NUMBER not null,
  p_clientid          VARCHAR2(50),
  p_bsclientid        VARCHAR2(30),
  p_name              VARCHAR2(2000),
  p_bank_client       NUMBER(1) not null,
  p_regopendate       DATE,
  p_countrycode       VARCHAR2(20) not null,
  p_client_type       NUMBER(1) not null,
  p_clientrole        NUMBER(2) not null,
  p_clientkind        NUMBER not null,
  p_account           VARCHAR2(200) not null,
  p_bsaccount         VARCHAR2(50),
  p_bank              VARCHAR2(100),
  p_bankcountrycode   VARCHAR2(5),
  p_bankname          VARCHAR2(1024),
  p_ipdl              NUMBER(3) default 1,
  p_date_insert       DATE default sysdate not null,
  p_date_update       DATE,
  p_username          VARCHAR2(30),
  changedate          DATE default sysdate not null,
  p_lastname          VARCHAR2(300),
  p_firstname         VARCHAR2(300),
  p_middlename        VARCHAR2(300),
  p_sdp               VARCHAR2(512),
  p_orgform           VARCHAR2(50),
  p_bankcity          VARCHAR2(150),
  p_oper_date         DATE,
  p_counterparty_bank VARCHAR2(100),
  p_ordering_bank     VARCHAR2(50),
  p_source_code       VARCHAR2(100),
  p_remitter_bene     VARCHAR2(1000),
  p_bank_details      VARCHAR2(2000),
  p_client_add_info   VARCHAR2(4000),
  p_name_json         VARCHAR2(2000),
  p_countrycode_json  VARCHAR2(20),
  p_bank_json         VARCHAR2(100),
  p_account_json      VARCHAR2(200)
)
]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
-- Add comments to the table 
comment on table PROCESS_TXT_MEMBERS
  is 'Интеграционная таблица для хранения информации участникам операций из txt файлов'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
-- Add comments to the columns 
comment on column PROCESS_TXT_MEMBERS.id
  is 'ИД'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_operationid
  is 'ID ОПЕРАЦИИ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_clientid
  is '№ КЛИЕНТА (СКВОЗНОЙ)'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_bsclientid
  is '№ КЛИЕНТА (ДЛЯ СИСТЕМЫ)'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_name
  is 'НАИМЕНОВАНИЕ УЧАСТНИКА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_bank_client
  is 'КЛИЕНТ СУБЪЕКТА ФИНАНСОВОГО МОНИТОРИНГА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_regopendate
  is 'ДАТА РЕГИСТРАЦИИ УЧАСТНИКА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_countrycode
  is 'СТРАН РЕГИСТРАЦИИ УЧАСТНИКА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_client_type
  is 'ТИП КЛИЕНТА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_clientrole
  is 'ВИД УЧАСТНИКА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_clientkind
  is 'ВИД УЧАСТНИКА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_account
  is 'СЧЕТ КЛИЕНТА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_bsaccount
  is 'БАЛАНСОВЫЙ СЧЕТ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_bank
  is 'КОД БАНКА КЛИЕНТА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_bankcountrycode
  is 'СТРАНА БАНКА УЧАСТНИКА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_bankname
  is 'НАИМЕНОВАНИЕ БАНКА УЧАСТНИКА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_ipdl
  is 'ПРИНАДЛЕЖНОСТЬ К ИПДЛ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_date_insert
  is 'ДАТА СОЗДАНИЯ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_date_update
  is 'ДАТА ИЗМЕНЕНИЯ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_username
  is 'ПОЛЬЗОВАТЕЛЬ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.changedate
  is 'ДАТА ИЗМЕНЕНИЯ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_lastname
  is 'Фамилия'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_firstname
  is 'Имя'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_middlename
  is 'Отчество'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_sdp
  is 'Наименование СДП'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_orgform
  is 'Коды организационных форм'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_bankcity
  is 'Город банка (только для резидентов)'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_oper_date
  is 'Дата операции'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_name_json
  is 'НАИМЕНОВАНИЕ УЧАСТНИКА (теги json: OrigDbtrNm (payer)/OrigCdtrNm(receiver))'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_countrycode_json
  is 'СТРАН РЕГИСТРАЦИИ УЧАСТНИКА (теги json: OrigDbtrCtryoR(payer)/)'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_bank_json
  is 'КОД БАНКА КЛИЕНТА (теги json: OrigDbtrOrgIdBIC(payer)/OrigCdtrOrgIdBIC(receiver))'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_MEMBERS.p_account_json
  is 'СЧЕТ КЛИЕНТА (теги json: OrigDbtrAccountIBAN(payer)/OrigCdtrAccountIBAN(receiver))'
                             ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
-- Create/Recreate indexes 
create index PROCESS_TXT_MEMBERS_IDX0 on PROCESS_TXT_MEMBERS (P_NAME, P_ACCOUNT, P_CLIENTROLE, P_OPERATIONID, P_CLIENTKIND, P_BANK_CLIENT, P_CLIENTID, P_BSCLIENTID, P_BANK)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_MEMBERS_IDX1 on PROCESS_TXT_MEMBERS (P_OPERATIONID)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_MEMBERS_IDX2 on PROCESS_TXT_MEMBERS (P_NAME)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_MEMBERS_IDX3 on PROCESS_TXT_MEMBERS (P_ACCOUNT)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_MEMBERS_IDX4 on PROCESS_TXT_MEMBERS (P_CLIENTID, P_OPER_DATE)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_MEMBERS_IDX5 on PROCESS_TXT_MEMBERS (P_ACCOUNT, P_OPER_DATE)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_MEMBERS_IDX6 on PROCESS_TXT_MEMBERS (P_CLIENTID, P_BSCLIENTID)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
-- Create/Recreate primary, unique and foreign key constraints 
alter table PROCESS_TXT_MEMBERS
  add constraint PROCESS_TXT_MEMBERS_FK foreign key (P_OPERATIONID)
  references PROCESS_TXT_OPERATIONS (ID)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       
  end if;
end;
/