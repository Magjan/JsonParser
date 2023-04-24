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
  if ( ln_cnt = 0 ) then
       lc_statment := q'[ 
create table PROCESS_TXT_OPERATIONS
(
  id                  NUMBER not null,
  p_issuedbid         VARCHAR2(10) not null,
  p_bankoperationid   VARCHAR2(500) not null,
  p_ordernumber       VARCHAR2(50),
  p_branch            VARCHAR2(50),
  p_currencycode      VARCHAR2(3) not null,
  p_operationdatetime DATE not null,
  p_baseamount        NUMBER(20,5) not null,
  p_currencyamount    NUMBER(20,5),
  p_eknpcode          VARCHAR2(5),
  p_docnumber         VARCHAR2(100),
  p_docdate           DATE,
  p_doccategory       NUMBER not null,
  p_operationstatus   NUMBER(1) default 1 not null,
  p_operationreason   VARCHAR2(4000),
  p_date_insert       DATE default SYSDATE not null,
  p_date_update       DATE,
  changedate          DATE default SYSDATE not null,
  p_doctype           NUMBER,
  p_docsuspic         NUMBER,
  p_property          VARCHAR2(1000),
  p_propertynumber    VARCHAR2(100),
  p_suspic_comments   VARCHAR2(4000),
  p_operation_type    VARCHAR2(100),
  p_product_code      VARCHAR2(100),
  p_loaded            NUMBER default 0,
  p_issuedbid_json    VARCHAR2(10)
)
]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
-- Add comments to the table 
comment on table PROCESS_TXT_OPERATIONS
  is 'Интеграционная таблица для хранения данных по операциям из txt файлов'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
-- Add comments to the columns 
comment on column PROCESS_TXT_OPERATIONS.id
  is 'ИД'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_issuedbid
  is 'БД ИСТОЧНИК'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_ordernumber
  is 'НОМЕР ЗАЯВКИ В СИСТЕМЕ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_branch
  is 'КОД ФИЛИАЛА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_currencycode
  is 'КОД ВАЛЮТЫ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_operationdatetime
  is 'ДАТА ОПЕРАЦИИ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_baseamount
  is 'СУММА (НАЦ)'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_currencyamount
  is 'СУММА (ВАЛ)'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_eknpcode
  is 'КНП'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_docnumber
  is '№ ДОКУМЕНТА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_docdate
  is 'ДАТА ДОКУМЕНТА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_doccategory
  is 'КАТЕГОРИЯ ДОКУМЕНТА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_operationstatus
  is 'СОСТОЯНИЕ ОПЕРАЦИИ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_operationreason
  is 'ОСНОВАНИЕ СОВЕРШЕНИЯ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_date_insert
  is 'ДАТА ВСТАВКИ ЗАПИСИ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_date_update
  is 'ДАТА ОБНОВЛЕНИЯ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.changedate
  is 'ДАТА ИЗМЕНЕНИЯ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_doctype
  is 'ТИП ДОКУМЕНТА'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_docsuspic
  is 'ТИП ПОДОЗРИТЕЛЬНОСТИ'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_loaded
  is 'Признак загрузки в tb_offlineoperations (0-no/1-yes)'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
comment on column PROCESS_TXT_OPERATIONS.p_issuedbid_json
  is 'БД ИСТОЧНИК из json'
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
-- Create/Recreate indexes 
create index PROCESS_TXT_OPERATIONS_IDX0 on PROCESS_TXT_OPERATIONS (P_BANKOPERATIONID)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_OPERATIONS_IDX1 on PROCESS_TXT_OPERATIONS (P_BASEAMOUNT)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_OPERATIONS_IDX2 on PROCESS_TXT_OPERATIONS (P_DOCCATEGORY)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
create index PROCESS_TXT_OPERATIONS_IDX3 on PROCESS_TXT_OPERATIONS (P_OPERATIONDATETIME)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
-- Create/Recreate primary, unique and foreign key constraints 
alter table PROCESS_TXT_OPERATIONS
  add constraint PROCESS_TXT_OPERATIONS_PK primary key (ID)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       lc_statment := q'[
alter table PROCESS_TXT_OPERATIONS
  add constraint PROCESS_TXT_OPERATIONS_UK unique (P_ISSUEDBID, P_BANKOPERATIONID)
  ]';
       -- lc_statment := replace( lc_statment, '#schema#', lv_schema );
       -- execute immediate replace( lc_statment, '#schema#', lv_schema );
       execute immediate lc_statment;
       
  end if;

end;
/