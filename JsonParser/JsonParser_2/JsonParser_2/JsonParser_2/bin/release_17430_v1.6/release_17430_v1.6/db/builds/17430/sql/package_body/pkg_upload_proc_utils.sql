create or replace package body pkg_upload_proc_utils is
-- Uploading datas from text files( operations )
procedure setIntegrationOperationid_load( in_p_issuedbid         varchar2,              -- бд источник
                                          in_p_bankoperationid   varchar2,              -- номер операции
                                          in_p_ordernumber       varchar2 default null, -- номер заявки в системе
                                          in_p_branch            varchar2 default null, -- код филиала
                                          in_p_currencycode      varchar2,              -- код валюты
                                          in_p_operationdatetime varchar2,              -- дата операции
                                          in_p_baseamount        number,                -- сумма (нац)
                                          in_p_currencyamount    number default null,   -- сумма (вал)
                                          in_p_eknpcode          varchar2 default null, -- кнп
                                          in_p_docnumber         varchar2 default null, -- № документа
                                          in_p_docdate           varchar2 default null, -- дата документа
                                          in_p_doccategory       varchar2 default null, -- категория документа
                                          in_p_doctype           varchar2 default null, -- тип документа
                                          in_p_docsuspic         varchar2 default null, -- тип подозрительности
                                          in_p_operationstatus   varchar2,              -- состояние операции
                                          in_p_operationreason   varchar2 default null, -- основание совершения
                                          in_p_property          varchar2 default null,
                                          in_p_propertynumber    varchar2 default null,
                                          in_p_suspic_comments   varchar2 default null,
                                          in_p_operation_type    varchar2 default null,
                                          in_product_code        varchar2 default null,
                                          in_p_product_code      varchar2 default null,
                                          in_id                  in out number          -- ид
                                         ) is
  pragma autonomous_transaction;
  /*************************************************************************************
  Описание: запись данных в таблицу PROCESS_TXT_OPERATIONS
  *************************************************************************************/
Begin
  --
  pr_IntegrOper$update(
                        in_p_issuedbid         => in_p_issuedbid,
                        in_p_bankoperationid   => in_p_bankoperationid,
                        in_p_ordernumber       => in_p_ordernumber,
                        in_p_branch            => in_p_branch,
                        in_p_currencycode      => get_currency_num( in_p_currencycode ),
                        in_p_operationdatetime => to_date( in_p_operationdatetime,'DD.MM.YYYY HH24:MI:SS' ),
                        in_p_baseamount        => in_p_baseamount,
                        in_p_currencyamount    => nvl( in_p_currencyamount, in_p_baseamount ),
                        in_p_eknpcode          => in_p_eknpcode,
                        in_p_docnumber         => in_p_docnumber,
                        in_p_docdate           => to_date( in_p_docdate, 'DD.MM.YYYY HH24:MI:SS' ),
                        in_p_doccategory       => to_number( nvl( in_p_doccategory, 0 ) ),
                        in_p_doctype           => in_p_doctype,
                        in_p_docsuspic         => to_number( in_p_docsuspic ),
                        in_p_operationstatus   => to_number( in_p_operationstatus ),
                        in_p_operationreason   => in_p_operationreason,
                        in_p_date_insert       => sysdate,
                        in_p_date_update       => sysdate,
                        in_p_property          => in_p_property,
                        in_p_propertynumber    => in_p_propertynumber,
                        in_p_suspic_comments   => in_p_suspic_comments,
                        in_p_operation_type    => in_p_operation_type,
                        in_product_code        => nvl( in_product_code, in_p_product_code ),
                        in_id                  => in_id
                       );
  --
  if ( sql%rowcount = 0 ) then
       pr_IntegrOper$insert(
                             in_p_issuedbid         => in_p_issuedbid,
                             in_p_bankoperationid   => in_p_bankoperationid,
                             in_p_ordernumber       => in_p_ordernumber,
                             in_p_branch            => in_p_branch,
                             in_p_currencycode      => get_currency_num( in_p_currencycode ),
                             in_p_operationdatetime => to_date( in_p_operationdatetime, 'DD.MM.YYYY HH24:MI:SS' ),
                             in_p_baseamount        => in_p_baseamount,
                             in_p_currencyamount    => nvl( in_p_currencyamount, in_p_baseamount ),
                             in_p_eknpcode          => in_p_eknpcode,
                             in_p_docnumber         => in_p_docnumber,
                             in_p_docdate           => to_date( in_p_docdate, 'DD.MM.YYYY HH24:MI:SS' ),
                             in_p_doccategory       => to_number( nvl( in_p_doccategory, 0 ) ),
                             in_p_doctype           => in_p_doctype,
                             in_p_docsuspic         => to_number( in_p_docsuspic ),
                             in_p_operationstatus   => to_number( in_p_operationstatus ),
                             in_p_operationreason   => in_p_operationreason,
                             in_p_date_insert       => sysdate,
                             in_p_date_update       => sysdate,
                             in_p_property          => in_p_property,
                             in_p_propertynumber    => in_p_propertynumber,
                             in_p_suspic_comments   => in_p_suspic_comments,
                             in_p_operation_type    => in_p_operation_type,
                             in_product_code        => nvl( in_product_code, in_p_product_code ),
                             in_id                  => in_id
                            );
  end if;
  --
  commit;
  --
exception
  when others then
       aml_user.dbg$insert( 'setIntegrationOperationid_load', substr( sqlerrm, 1, 4000 ) );
       aml_user.opers$insert(
                     'setIntegrationOperationid_load', in_p_bankoperationid,
                     'IN_P_ISSUEDBID' || ' = ' || in_p_issuedbid || chr( 10 ) ||
                     'IN_P_BANKOPERATIONID' || ' = ' || in_p_bankoperationid  || chr( 10 ) ||
                     'IN_P_ORDERNUMBER' || ' = ' || in_p_ordernumber || chr( 10 ) ||
                     'IN_P_BRANCH' || ' = ' || in_p_branch || chr( 10 )||
                     'IN_P_CURRENCYCODE' || ' = ' || in_p_currencycode || chr( 10 ) ||
                     'IN_P_OPERATIONDATETIME' || ' = ' || in_p_operationdatetime || chr( 10 ) ||
                     'IN_P_BASEAMOUNT' || ' = ' || in_p_baseamount || chr( 10 ) ||
                     'IN_P_CURRENCYAMOUNT' || ' = ' || in_p_currencyamount || chr( 10 ) ||
                     'IN_P_EKNPCODE' || ' = ' || in_p_eknpcode || chr( 10 ) ||
                     'IN_P_DOCNUMBER' || ' = ' || in_p_docnumber || chr( 10 ) ||
                     'IN_P_DOCDATE' || ' = ' || in_p_docdate || chr( 10 ) ||
                     'IN_P_DOCCATEGORY' || ' = ' || in_p_doccategory || chr( 10 )||
                     'IN_P_DOCTYPE' || ' = ' || in_p_doctype || chr( 10 ) ||
                     'IN_P_DOCSUSPIC' || ' = ' || in_p_docsuspic || chr( 10 ) ||
                     'IN_P_OPERATIONSTATUS' || ' = ' || in_p_operationstatus || chr( 10 )||
                     'IN_P_OPERATIONREASON' || ' = ' || substr( in_p_operationreason, 1, 200 ) || chr( 10 )||
                     'IN_P_PROPERTY' || ' = ' || in_p_property || chr( 10 ) ||
                     'IN_P_PROPERTYNUMBER' || ' = ' || in_p_propertynumber
                    );
       in_ID := Null;
       Raise;
End;
-- Uploading datas from text files( members )
procedure setIntegrationMembersis_load(
                                        in_operationid           varchar2,
                                        in_clientid              varchar2 default null,
                                        in_bsclientid            varchar2 default null,
                                        in_name                  varchar2 default null,  -- не должно быть пустым
                                        in_lastname              varchar2 default null,  -- не должно быть пустым
                                        in_firstname             varchar2 default null,
                                        in_middlename            varchar2 default null,  -- не должно быть пустым
                                        in_orgform               varchar2 default null,  -- не должно быть пустым
                                        in_bank_client           varchar2 default null,  -- не должно быть пустым
                                        in_regopendate           varchar2 default null,  -- не должно быть пустым,
                                        in_countrycode           varchar2 default null,  -- не должно быть пустым
                                        in_client_type           varchar2 default null,
                                        in_clientrole            varchar2 default null,  -- не должно быть пустым
                                        in_clientkind            varchar2 default null,  -- не должно быть пустым
                                        in_account               varchar2 default null,  -- не должно быть пустым
                                        in_bsaccount             varchar2 default null,  -- не должно быть пустым
                                        in_bank                  varchar2 default null,
                                        in_bankcountrycode       varchar2 default null,
                                        in_bankname              varchar2 default null,
                                        in_ipdl                  varchar2 default null,
                                        in_sdp                   varchar2 default null,
                                        in_bankcity              varchar2 default null,
                                        in_counterparty_bank     varchar2 default null,
                                        in_ordering_bank         varchar2 default null,
                                        in_source_code           varchar2 default null,
                                        in_remitter_bene         varchar2 default null,
                                        in_bank_details          varchar2 default null,
                                        in_client_add_info       varchar2 default null,
                                        in_bn_accno              varchar2 default null,
                                        in_bn_add_info           varchar2 default null,
                                        in_ult_beneficiary1      varchar2 default null, -- 2 member additional info
                                        in_ult_beneficiary2      varchar2 default null,
                                        in_ult_beneficiary3      varchar2 default null,
                                        in_ult_beneficiary4      varchar2 default null,
                                        in_ult_beneficiary5      varchar2 default null,
                                        in_by_order_of1          varchar2 default null, -- 1 member additional info
                                        in_by_order_of2          varchar2 default null,
                                        in_by_order_of3          varchar2 default null,
                                        in_by_order_of4          varchar2 default null,
                                        in_by_order_of5          varchar2 default null,
                                        in_ordering_institution1 varchar2 default null, -- 3 member bank
                                        in_ordering_institution2 varchar2 default null, -- 3 member bank name
                                        in_ordering_institution3 varchar2 default null, -- 3-4-5 - additional info
                                        in_ordering_institution4 varchar2 default null,
                                        in_ordering_institution5 varchar2 default null,
                                        in_cpty_ac_no            varchar2 default null,
                                        in_cpty_name             varchar2 default null,
                                        in_opdatetime            varchar2 default null
                                       ) is
  pragma autonomous_transaction;
   /*************************************************************************************
  Описание: запись данных в таблицу PROCESS_TXT_MEMBERS

  *************************************************************************************/
  in_id number;
  v_operation_id number;
  v_clientrole number;
Begin
  -- dbg$insert('TEST BSCLIENTID', IN_BSCLIENTID);
  begin
    select id
      into v_operation_id
      from aml_user.process_txt_operations m
     where m.p_bankoperationid = in_operationid;
  exception
    when others then
         v_operation_id := null;
  end;
  --
  pr_IntegrMembers$update(
                           in_operationid       => v_operation_id,
                           in_name              => nvl( upper( in_name ), '-' ),
                           in_account           => nvl( in_account, '-' ),
                           in_clientid          => in_clientid,
                           in_bsclientid        => in_bsclientid,
                           in_bank_client       => to_number( in_bank_client ),
                           in_regopendate       => to_date( in_regopendate, 'dd.mm.yyyy hh24:mi:ss' ),
                           in_countrycode       => nvl( get_country_num_code( in_countrycode ), 398 ),
                           in_client_type       => to_number( nvl( in_client_type, 1 ) ),
                           in_clientrole        => to_number( nvl( in_clientrole, 1 ) ),
                           in_clientkind        => to_number( nvl( in_clientkind, 55 ) ),
                           in_bsaccount         => in_bsaccount,
                           in_bank              => nvl( in_bank,' ' ),
                           in_bankcountrycode   => nvl( get_country_num_code( in_bankcountrycode ), 398 ),
                           in_bankname          => nvl( in_bankname, '-' ),
                           in_ipdl              => nvl( to_number( in_ipdl ), 1 ),
                           in_date_insert       => sysdate,
                           in_date_update       => sysdate,
                           in_username          => 'AML_USER',
                           in_lastname          => in_lastname,
                           in_firstname         => in_firstname,
                           in_middlename        => in_middlename,
                           in_sdp               => in_sdp,
                           in_orgform           => in_orgform,
                           in_bankcity          => in_bankcity,
                           in_counterparty_bank => in_counterparty_bank,
                           in_ordering_bank     => in_ordering_bank,
                           in_source_code       => in_source_code,
                           in_remitter_bene     => in_remitter_bene,
                           in_bank_details      => in_bank_details,
                           in_client_add_info   => in_bn_add_info,
                           in_id                => in_id
                          );
  --
  if ( sql%rowcount = 0 ) then
       pr_IntegrMembers$insert(
                                in_operationid       => in_operationid,
                                in_clientid          => in_clientid,
                                in_bsclientid        => in_bsclientid,
                                in_name              => nvl( in_name, '-' ),
                                in_bank_client       => to_number( in_bank_client ),
                                in_regopendate       => to_date( in_regopendate, 'dd.mm.yyyy hh24:mi:ss' ),
                                in_countrycode       => nvl( get_country_num_code( in_countrycode ), '398' ),
                                in_client_type       => to_number( nvl( in_client_type, 1 ) ),
                                in_clientrole        => to_number( nvl( in_clientrole, 1 ) ),
                                in_clientkind        => to_number( nvl( in_clientkind, 55 ) ),
                                in_account           => nvl( in_account, '-' ),
                                in_bsaccount         => in_bsaccount,
                                in_bank              => nvl( in_bank, ' ' ),
                                in_bankcountrycode   => nvl( get_country_num_code( in_bankcountrycode), '398' ),
                                in_bankname          => nvl( in_bankname, '-' ),
                                in_ipdl              => nvl( to_number( in_ipdl ), 1 ),
                                in_date_insert       => sysdate,
                                in_date_update       => sysdate,
                                in_username          => 'AML_USER',
                                in_lastname          => in_lastname,
                                in_firstname         => in_firstname,
                                in_middlename        => in_middlename,
                                in_sdp               => in_sdp,
                                in_orgform           => in_orgform,
                                in_bankcity          => in_bankcity,
                                in_counterparty_bank => in_counterparty_bank,
                                in_ordering_bank     => in_ordering_bank,
                                in_source_code       => in_source_code,
                                in_remitter_bene     => in_remitter_bene,
                                in_bank_details      => in_bank_details,
                                in_client_add_info   => in_bn_add_info,
                                in_id                => in_id
                               );
  end if;
  --
  commit;
  -- second member
  if    ( IN_CLIENTROLE = 1 ) then
          v_clientrole := 2;
  elsif ( IN_CLIENTROLE = 2 ) then
          v_clientrole := 1;
  else
          v_clientrole := 2;
  end if;
  --
  pr_IntegrMembers$update(
                           in_operationid       => v_operation_id,
                           in_name              => nvl( upper( in_remitter_bene ), '-' ),
                           in_account           => nvl( in_bn_accno, nvl( in_cpty_ac_no, '-' ) ),
                           in_clientid          => null,
                           in_bsclientid        => null,
                           in_bank_client       => 1,
                           in_regopendate       => null,
                           in_countrycode       => 398,
                           in_client_type       => 1,
                           in_clientrole        => v_clientrole,
                           in_clientkind        => 55,
                           in_bsaccount         => null,
                           in_bank              => nvl( in_bank_details,' ' ),
                           in_bankcountrycode   => 398,
                           in_bankname          => nvl( in_cpty_name, nvl( in_client_add_info, '-' ) ),
                           in_ipdl              => 1,
                           in_date_insert       => sysdate,
                           in_date_update       => sysdate,
                           in_username          => 'AML_USER',
                           in_lastname          => null,
                           in_firstname         => null,
                           in_middlename        => null,
                           in_sdp               => null,
                           in_orgform           => null,
                           in_bankcity          => null,
                           in_counterparty_bank => null,
                           in_ordering_bank     => null,
                           in_source_code       => in_source_code,
                           in_remitter_bene     => null,
                           in_bank_details      => in_bank_details,
                           in_client_add_info   => in_client_add_info,
                           in_id                => in_id
                          );
  if ( sql%rowcount = 0 ) then
       pr_IntegrMembers$insert(
                                in_operationid       => in_operationid,
                                in_name              => nvl( upper( in_remitter_bene ), '-' ),
                                in_account           => nvl( in_bn_accno, nvl( in_cpty_ac_no, '-' ) ),
                                in_clientid          => null,
                                in_bsclientid        => null,
                                in_bank_client       => 1,
                                in_regopendate       => null,
                                in_countrycode       => 398,
                                in_client_type       => 1,
                                in_clientrole        => v_clientrole,
                                in_clientkind        => 55,
                                in_bsaccount         => null,
                                in_bank              => nvl( in_bank_details,' ' ),
                                in_bankcountrycode   => 398,
                                in_bankname          => nvl( in_cpty_name, nvl( in_client_add_info, '-' ) ),
                                in_ipdl              => 1,
                                in_date_insert       => sysdate,
                                in_date_update       => sysdate,
                                in_username          => 'AML_USER',
                                in_lastname          => null,
                                in_firstname         => null,
                                in_middlename        => null,
                                in_sdp               => null,
                                in_orgform           => null,
                                in_bankcity          => null,
                                in_counterparty_bank => in_counterparty_bank,
                                in_ordering_bank     => in_ordering_bank,
                                in_source_code       => in_source_code,
                                in_remitter_bene     => in_remitter_bene,
                                in_bank_details      => in_bank_details,
                                in_client_add_info   => in_client_add_info,
                                in_id                => in_id
                             );
  end if;
  --
  commit;
  -- third member
  if ( ( v_clientrole = 2 ) and ( in_ordering_institution1 is not null ) ) then
         pr_IntegrMembers$update(
                                  in_operationid       => v_operation_id,
                                  in_name              => nvl( upper( in_remitter_bene ), '-' ),
                                  in_account           => nvl( in_bn_accno, nvl( in_cpty_ac_no, '-' ) ),
                                  in_clientid          => null,
                                  in_bsclientid        => null,
                                  in_bank_client       => 1,
                                  in_regopendate       => null,
                                  in_countrycode       => 398,
                                  in_client_type       => 1,
                                  in_clientrole        => v_clientrole,
                                  in_clientkind        => 55,
                                  in_bsaccount         => null,
                                  in_bank              => nvl( in_ordering_institution1, ' ' ),
                                  in_bankcountrycode   => 398,
                                  in_bankname          => nvl( in_ordering_institution2, '-' ),
                                  in_ipdl              => 1,
                                  in_date_insert       => sysdate,
                                  in_date_update       => sysdate,
                                  in_username          => 'AML_USER',
                                  in_lastname          => null,
                                  in_firstname         => null,
                                  in_middlename        => null,
                                  in_sdp               => null,
                                  in_orgform           => null,
                                  in_bankcity          => null,
                                  in_counterparty_bank => null,
                                  in_ordering_bank     => null,
                                  in_source_code       => in_source_code,
                                  in_remitter_bene     => null,
                                  in_bank_details      => null,
                                  in_client_add_info   => in_ordering_institution3 || chr( 10 ) || in_ordering_institution4 || chr( 10 ) || in_ordering_institution5,
                                  in_id                => in_id
                                 );
         --
         if ( sql%rowcount = 0 ) then
              pr_IntegrMembers$insert(
                                       in_operationid       => in_operationid,
                                       in_name              => nvl( upper( in_remitter_bene ), '-' ),
                                       in_account           => nvl( in_bn_accno, nvl( in_cpty_ac_no, '-' ) ),
                                       in_clientid          => null,
                                       in_bsclientid        => null,
                                       in_bank_client       => 1,
                                       in_regopendate       => null,
                                       in_countrycode       => 398,
                                       in_client_type       => 1,
                                       in_clientrole        => v_clientrole,
                                       in_clientkind        => 55,
                                       in_bsaccount         => null,
                                       in_bank              => nvl( in_ordering_institution1, ' ' ),
                                       in_bankcountrycode   => 398,
                                       in_bankname          => nvl( in_ordering_institution2, '-' ),
                                       in_ipdl              => 1,
                                       in_date_insert       => sysdate,
                                       in_date_update       => sysdate,
                                       in_username          => 'AML_USER',
                                       in_lastname          => null,
                                       in_firstname         => null,
                                       in_middlename        => null,
                                       in_sdp               => null,
                                       in_orgform           => null,
                                       in_bankcity          => null,
                                       in_counterparty_bank => null,
                                       in_ordering_bank     => null,
                                       in_source_code       => in_source_code,
                                       in_remitter_bene     => null,
                                       in_bank_details      => null,
                                       in_client_add_info   => in_ordering_institution3 || chr( 10 ) || in_ordering_institution4 || chr( 10 ) || in_ordering_institution5,
                                       in_id                => in_id
                                      );
         end if;
  end if;
  --
  commit;
  --
exception
  when others then
       aml_user.dbg$insert( 'SETINTEGRATIONMEMBERSIS_LOAD', substr( sqlerrm, 1, 4000 ) );
       in_id := null;
       raise;
end;
-- Inserting datas to Process_txt_operations
procedure pr_IntegrOper$insert(
                                in_p_issuedbid         varchar2,
                                in_p_bankoperationid   varchar2,
                                in_p_ordernumber       varchar2,
                                in_p_branch            varchar2,
                                in_p_currencycode      varchar2,
                                in_p_operationdatetime date,
                                in_p_baseamount        number,
                                in_p_currencyamount    number,
                                in_p_eknpcode          varchar2,
                                in_p_docnumber         varchar2,
                                in_p_docdate           date,
                                in_p_doccategory       number,
                                in_p_doctype           varchar2,
                                in_p_docsuspic         number,
                                in_p_operationstatus   number,
                                in_p_operationreason   varchar2,
                                in_p_date_insert       date,
                                in_p_date_update       date,
                                in_p_property          in varchar2,
                                in_p_propertynumber    in varchar2,
                                in_p_suspic_comments   in varchar2,
                                in_p_operation_type    in varchar2,
                                in_product_code        in varchar2,
                                in_id                  in out number
                               ) is
  res_sql number;
begin
  --
  aml_user.opers$insert(
                'pr_IntegrOper$insert', IN_P_BANKOPERATIONID,
                'IN_P_ISSUEDBID' || ' = ' || IN_P_ISSUEDBID || CHR( 10 ) ||
                'IN_P_BANKOPERATIONID' || ' = ' || IN_P_BANKOPERATIONID || CHR( 10 ) ||
                'IN_P_ORDERNUMBER' || ' = ' || IN_P_ORDERNUMBER || CHR( 10 ) ||
                'IN_P_BRANCH' || ' = ' || IN_P_BRANCH || CHR( 10 ) ||
                'IN_P_CURRENCYCODE' || ' = ' || IN_P_CURRENCYCODE || CHR( 10 ) ||
                'IN_P_OPERATIONDATETIME' || ' = ' || IN_P_OPERATIONDATETIME || CHR( 10 ) ||
                'IN_P_BASEAMOUNT' || ' = ' || IN_P_BASEAMOUNT || CHR( 10 ) ||
                'IN_P_CURRENCYAMOUNT' || ' = ' || IN_P_CURRENCYAMOUNT || CHR( 10 )||
                'IN_P_EKNPCODE' || ' = ' || IN_P_EKNPCODE || CHR( 10 ) ||
                'IN_P_DOCNUMBER' || ' = ' || IN_P_DOCNUMBER || CHR( 10 ) ||
                'IN_P_DOCDATE' || ' = ' || IN_P_DOCDATE || CHR( 10 ) ||
                'IN_P_DOCCATEGORY' || ' = ' || IN_P_DOCCATEGORY || CHR( 10 ) ||
                'IN_P_DOCTYPE' || ' = ' || IN_P_DOCTYPE || CHR( 10 ) ||
                'IN_P_DOCSUSPIC' || ' = ' || IN_P_DOCSUSPIC || CHR( 10 ) ||
                'IN_P_OPERATIONSTATUS' || ' = ' || IN_P_OPERATIONSTATUS || CHR( 10 ) ||
                'IN_P_OPERATIONREASON' || ' = ' || substr( IN_P_OPERATIONREASON, 1, 200 ) || CHR( 10 ) ||
                'IN_P_DATE_INSERT' || ' = ' || IN_P_DATE_INSERT || CHR( 10 ) ||
                'IN_P_DATE_UPDATE' || ' = ' || IN_P_DATE_UPDATE || CHR( 10 ) ||
                'IN_P_PROPERTY' || ' = ' || IN_P_PROPERTY || CHR( 10 ) ||
                'IN_P_PROPERTYNUMBER' || ' = ' || IN_P_PROPERTYNUMBER
               );
  --
  -- in_id := GetID();
  --
  select nvl( max( id ), 0 )
    into res_sql
    from aml_user.process_txt_operations
   where p_issuedbid = in_p_issuedbid
     and p_bankoperationid = in_p_bankoperationid;
  --
  if ( res_sql = 0 ) then
       --
       select nvl( max( id ), 0 ) + 1
         into in_id
         from aml_user.process_txt_operations;
       --
       insert into aml_user.process_txt_operations (
                                            id,
                                            p_issuedbid,
                                            p_bankoperationid,
                                            p_ordernumber,
                                            p_branch,
                                            p_currencycode,
                                            p_operationdatetime,
                                            p_baseamount,
                                            p_currencyamount,
                                            p_eknpcode,
                                            p_docnumber,
                                            p_docdate,
                                            p_doccategory,
                                            p_doctype,
                                            p_docsuspic,
                                            p_operationstatus,
                                            p_operationreason,
                                            p_date_insert,
                                            p_date_update,
                                            p_property,
                                            p_propertynumber,
                                            p_suspic_comments,
                                            p_operation_type,
                                            p_product_code
                                           )
       values (
                in_id,
                in_p_issuedbid,
                in_p_bankoperationid,
                in_p_ordernumber,
                in_p_branch,
                in_p_currencycode,
                in_p_operationdatetime,
                in_p_baseamount,
                in_p_currencyamount,
                in_p_eknpcode,
                in_p_docnumber,
                in_p_docdate,
                in_p_doccategory,
                in_p_doctype,
                in_p_docsuspic,
                in_p_operationstatus,
                in_p_operationreason,
                in_p_date_insert,
                in_p_date_update,
                in_p_property,
                in_p_propertynumber,
                in_p_suspic_comments,
                in_p_operation_type,
                in_product_code
               );
  end if;
  --
exception
  when others then
       aml_user.dbg$insert( 'pr_IntegrOper$insert', substr( sqlerrm, 1, 4000 ) );
       in_id := null;
       raise;
end;
-- Updating datas in Process_txt_operations
procedure pr_IntegrOper$update(
                                in_p_issuedbid         in varchar2,
                                in_p_bankoperationid   in varchar2,
                                in_p_ordernumber       in varchar2,
                                in_p_branch            in varchar2,
                                in_p_currencycode      in varchar2,
                                in_p_operationdatetime in date,
                                in_p_baseamount        in number,
                                in_p_currencyamount    in number,
                                in_p_eknpcode          in varchar2,
                                in_p_docnumber         in varchar2,
                                in_p_docdate           in date,
                                in_p_doccategory       in number,
                                in_p_doctype           in varchar2,
                                in_p_docsuspic         in number,
                                in_p_operationstatus   in number,
                                in_p_operationreason   in varchar2,
                                in_p_date_insert       in date,
                                in_p_date_update       in date,
                                in_p_property          in varchar2,
                                in_p_propertynumber    in varchar2,
                                in_p_suspic_comments   in varchar2,
                                in_p_operation_type    in varchar2,
                                in_product_code        in varchar2,
                                in_id                  out number
                               ) is
begin
  --
  aml_user.opers$insert(
                'pr_IntegrOper$update', in_p_bankoperationid,
                'IN_P_ISSUEDBID' || ' = ' || in_p_issuedbid || chr( 10 ) ||
                'IN_P_BANKOPERATIONID' || ' = ' || in_p_bankoperationid || chr( 10 )||
                'IN_P_ORDERNUMBER' || ' = ' || in_p_ordernumber || chr( 10 ) ||
                'IN_P_BRANCH' || ' = ' || in_p_branch || chr( 10 ) ||
                'IN_P_CURRENCYCODE' || ' = ' || in_p_currencycode || chr( 10 ) ||
                'IN_P_OPERATIONDATETIME' || ' = ' || in_p_operationdatetime || chr( 10 ) ||
                'IN_P_BASEAMOUNT' || ' = ' || in_p_baseamount || chr( 10 ) ||
                'IN_P_CURRENCYAMOUNT' || ' = ' || in_p_currencyamount || chr( 10 ) ||
                'IN_P_EKNPCODE' || ' = '|| in_p_eknpcode || chr( 10 ) ||
                'IN_P_DOCNUMBER' || ' = ' || in_p_docnumber || chr( 10 ) ||
                'IN_P_DOCDATE' || ' = ' || in_p_docdate || chr( 10 )||
                'IIN_P_DOCCATEGORY' || ' = ' || in_p_doccategory || chr( 10 ) ||
                'IN_P_DOCTYPE' || ' = ' || in_p_doctype || chr( 10 ) ||
                'IN_P_DOCSUSPIC' || ' = ' || in_p_docsuspic || chr( 10 ) ||
                'IN_P_OPERATIONSTATUS' || ' = ' || in_p_operationstatus || chr( 10 )||
                'IN_P_OPERATIONREASON' || ' = ' || substr( in_p_operationreason, 1, 200 ) || chr( 10 ) ||
                'IN_P_DATE_INSERT' || ' = ' || in_p_date_insert || chr( 10 ) ||
                'IN_P_DATE_UPDATE' || ' = ' || in_p_date_update || chr( 10 ) ||
                'IN_P_PROPERTY' || ' = ' || in_p_property || chr( 10 ) ||
                'IN_P_PROPERTYNUMBER' || ' = ' || in_p_propertynumber
               );
  --
  update aml_user.process_txt_operations set
         p_ordernumber       = in_p_ordernumber,
         p_branch            = in_p_branch,
         p_currencycode      = in_p_currencycode,
         p_operationdatetime = in_p_operationdatetime,
         p_baseamount        = in_p_baseamount,
         p_currencyamount    = in_p_currencyamount,
         p_eknpcode          = in_p_eknpcode,
         p_docnumber         = in_p_docnumber,
         p_docdate           = in_p_docdate,
         p_doccategory       = in_p_doccategory,
         p_doctype           = in_p_doctype,
         p_docsuspic         = in_p_docsuspic,
         p_operationstatus   = in_p_operationstatus,
         p_operationreason   = in_p_operationreason,
         p_date_insert       = in_p_date_insert,
         p_date_update       = nvl(in_p_date_update, sysdate),
         p_propertynumber    = in_p_propertynumber,
         p_property          = in_p_property,
         p_suspic_comments   = in_p_suspic_comments,
         p_operation_type    = in_p_operation_type,
         p_product_code      = in_product_code
   where p_issuedbid = in_p_issuedbid
     and p_bankoperationid = in_p_bankoperationid;
  --
  if ( sql%rowcount > 0 ) then
       select max( id )
         into in_id
         from aml_user.tb_offlineoperations
        where p_issuedbid = in_p_issuedbid
          and p_bankoperationid = in_p_bankoperationid;
  end if;
  --
exception
  when others then
       in_id := null;
       aml_user.dbg$insert( 'PR_OFFOPER$UPDATE', substr( sqlerrm, 1, 4000 ) );
       --
       raise;
end;
-- Updating datas in Process_txt_members
procedure pr_IntegrMembers$update(
                                   in_operationid       varchar2,
                                   in_name              varchar2,
                                   in_account           varchar2,
                                   in_clientid          varchar2,
                                   in_bsclientid        varchar2,
                                   in_bank_client       number,
                                   in_regopendate       date,
                                   in_countrycode       varchar2,
                                   in_client_type       number,
                                   in_clientrole        number,
                                   in_clientkind        number,
                                   in_bsaccount         varchar2,
                                   in_bank              varchar2,
                                   in_bankcountrycode   varchar2,
                                   in_bankname          varchar2,
                                   in_ipdl              number,
                                   in_date_insert       date,
                                   in_date_update       date,
                                   in_username          varchar2,
                                   in_lastname          varchar2,
                                   in_firstname         varchar2,
                                   in_middlename        varchar2,
                                   in_sdp               varchar2,
                                   in_orgform           varchar2,
                                   in_bankcity          varchar2,
                                   in_counterparty_bank varchar2 default null,
                                   in_ordering_bank     varchar2 default null,
                                   in_source_code       varchar2 default null,
                                   in_remitter_bene     varchar2 default null,
                                   in_bank_details      varchar2 default null,
                                   in_client_add_info   varchar2 default null,
                                   in_id                out number
                                  ) is
  v_oper_date date;
begin
  --
  begin
    select p_operationdatetime
      into v_oper_date
      from aml_user.process_txt_operations
     where id = in_operationid;
  exception
    when others then
         v_oper_date := null;
  end;
  --
  update aml_user.process_txt_members set
         p_clientid          = in_clientid,
         p_bsclientid        = in_bsclientid,
         p_bank_client       = in_bank_client,
         p_regopendate       = in_regopendate,
         p_countrycode       = in_countrycode,
         p_client_type       = in_client_type,
         p_clientrole        = in_clientrole,
         p_clientkind        = in_clientkind,
         p_bsaccount         = in_bsaccount,
         p_bank              = in_bank,
         p_bankcountrycode   = in_bankcountrycode,
         p_bankname          = in_bankname,
         p_ipdl              = in_ipdl,
         p_date_insert       = in_date_insert,
         p_date_update       = nvl( in_date_update, sysdate ),
         p_username          = in_username,
         p_lastname          = in_lastname,
         p_firstname         = in_firstname,
         p_middlename        = in_middlename,
         p_sdp               = in_sdp,
         p_orgform           = in_orgform,
         p_bankcity          = in_bankcity,
         p_counterparty_bank = in_counterparty_bank,
         p_ordering_bank     = in_ordering_bank,
         p_source_code       = in_source_code,
         p_remitter_bene     = in_remitter_bene,
         p_bank_details      = in_bank_details,
         p_client_add_info   = in_client_add_info,
         p_oper_date         = v_oper_date
   where p_operationid = in_operationid
     and upper( trim( p_name ) ) = upper( trim( in_name ) )
     and upper( p_account ) = upper( in_account )
  	 and p_clientrole = in_clientrole;
  --
  if ( sql%rowcount > 0 ) then
       select max( id )
         into in_id
         from aml_user.process_txt_members
        where p_operationid = in_operationid
          and upper( trim( p_name ) ) = upper( trim( in_name ) )
          and upper( p_account ) = upper( in_account );
  end if;
  --
exception
  when others then
       in_id := null;
       aml_user.dbg$insert( 'ERR_pr_IntegrMembers$update: [P_OPERATIONID] = ' || in_operationid, substr( sqlerrm, 1, 4000 ) );
       raise;
end;
-- Updating datas in Process_txt_members
Procedure pr_IntegrMembers$insert(
                                   in_operationid       varchar2,
                                   in_clientid          varchar2,
                                   in_bsclientid        varchar2,
                                   in_name              varchar2,
                                   in_bank_client       number,
                                   in_regopendate       date,
                                   in_countrycode       varchar2,
                                   in_client_type       number,
                                   in_clientrole        number,
                                   in_clientkind        number,
                                   in_account           varchar2,
                                   in_bsaccount         varchar2,
                                   in_bank              varchar2,
                                   in_bankcountrycode   varchar2,
                                   in_bankname          varchar2,
                                   in_ipdl              number,
                                   in_date_insert       date,
                                   in_date_update       date,
                                   in_username          varchar2,
                                   in_lastname          varchar2,
                                   in_firstname         varchar2,
                                   in_middlename        varchar2,
                                   in_sdp               varchar2,
                                   in_orgform           varchar2,
                                   in_bankcity          varchar2,
                                   in_counterparty_bank varchar2 default null,
                                   in_ordering_bank     varchar2 default null,
                                   in_source_code       varchar2 default null,
                                   in_remitter_bene     varchar2 default null,
                                   in_bank_details      varchar2 default null,
                                   in_client_add_info   varchar2 default null,
                                   in_id                in out number
                                  ) Is
  v_oper_date date;
  v_oper_id   number;
  -- res_sql number;
begin
  --
  begin
    select p_operationdatetime, id
      into v_oper_date, v_oper_id
      from aml_user.process_txt_operations
     where p_bankoperationid = in_operationid;
  exception
    when others then
         v_oper_date := null;
  end;
  --
  -- in_id := GetID();
  /*
  select nvl(max(id), 0)
    into res_sql
    from tb_off_members
   where p_operationid = in_operationid
     and p_name = in_name
     and p_account = in_account;

   if res_sql = 0 then
  */
  --
  select nvl( max( id ), 0 ) + 1
    into in_id
    from aml_user.process_txt_members;
  --
  insert into aml_user.process_txt_members (
                                    id,
                                    p_operationid,
                                    p_clientid,
                                    p_bsclientid,
                                    p_name,
                                    p_bank_client,
                                    p_regopendate,
                                    p_countrycode,
                                    p_client_type,
                                    p_clientrole,
                                    p_clientkind,
                                    p_account,
                                    p_bsaccount,
                                    p_bank,
                                    p_bankcountrycode,
                                    p_bankname,
                                    p_ipdl,
                                    p_date_insert,
                                    p_date_update,
                                    p_username,
                                    p_lastname,
                                    p_firstname,
                                    p_middlename,
                                    p_sdp,
                                    p_orgform,
                                    p_bankcity,
                                    p_oper_date,
                                    p_counterparty_bank,
                                    p_ordering_bank,
                                    p_source_code,
                                    p_remitter_bene,
                                    p_bank_details,
                                    p_client_add_info
                                   )
  values (
           in_id,
           v_oper_id,
           in_clientid,
           in_bsclientid,
           upper( trim( in_name ) ),
           in_bank_client,
           in_regopendate,
           in_countrycode,
           in_client_type,
           in_clientrole,
           in_clientkind,
           upper( in_account ),
           in_bsaccount,
           in_bank,
           in_bankcountrycode,
           in_bankname,
           in_ipdl,
           in_date_insert,
           in_date_update,
           in_username,
           in_lastname,
           in_firstname,
           in_middlename,
           in_sdp,
           in_orgform,
           decode( in_bank, 'CITIKZKA', 'Алматы', in_bankcity ),
           v_oper_date,
           in_counterparty_bank,
           in_ordering_bank,
           in_source_code,
           in_remitter_bene,
           in_bank_details,
           in_client_add_info
           );
--end if;
exception
  when others then
       aml_user.dbg$insert( 'ERR_pr_IntegrMembers$insert: [P_OPERATIONID] = ' || in_operationid || ',BSCLIENTID - ' || in_bsclientid, substr( sqlerrm, 1, 4000 ) );
       in_id := null;
       raise;
end;
-- Save results of uploading operations/members into integrity structures to log from service
procedure proc_operation_log(
                              in_count_oper             number,   -- общ кол-во
                              in_download_oper          number,   -- кол-во успешных
                              in_not_download_oper      number,   -- кол-во ошибок
                              in_error_oper             varchar2, -- причина ошибок
                              in_count_off_oper         number,
                              in_count_oper_n           number,
                              in_err_off                varchar2,
                              in_oper_file              varchar2,
                              in_count_members          number,
                              in_error_members          number,
                              in_count_download_members number,
                              in_member_file            varchar2,
                              in_member_err             varchar2
                             ) is
  cnt               number;
  cnt1              number;
  log_id            number;
  p_error_log       varchar2( 4000 );
  in_member_err_log varchar2( 4000 );
  --
  function getMaxIdForLog return number is
    ln_result number;
  begin
    --
    select nvl( max( id ), 0 ) + 1
      into ln_result
      from aml_user.tb_operation_log;
    --
    return ln_result;
    --
  end;
begin
  -- существует ли записи в логах за этот день?
  select count(*)
    into cnt
    from aml_user.tb_operation_log t
   where trunc( t.p_date, 'DDD' ) = trunc( sysdate, 'DDD' )
     and ( p_operfile = in_oper_file or p_memberfile = in_member_file );
  -- dbg$insert( 'log_oper_2', in_error_members || ' ' || in_member_err || ' ' || cnt );
  --
  if ( substr( in_oper_file, 1, 18 ) = 'AM800_KZR_AML_BrDl' ) then
       p_error_log := 'Общее количество строк -' || in_count_oper || chr( 10 ) ||
                      'Количество успешно загруженных строк в tb_parsedata - ' || in_download_oper || chr( 10 ) ||
                      'Количество незагруженных строк в tb_parsedata  - ' || in_not_download_oper || chr( 10 ) ||
                      'Количество успешно загруженных операции в базу - ' || in_count_off_oper || chr( 10 )||
                      'Количество незагруженных операции в базу - ' || in_count_oper_n || chr( 10 );
                      -- 'количество успешно загруженных строк( am800_kzr_aml_brdl ) - ' || in_download_oper || chr( 10 ) ||
                      -- 'количество незагруженных строк( am800_kzr_aml_brdl ) - ' || in_not_download_oper;

  else
       p_error_log := 'Общее количество строк( operations ) - ' || in_count_oper || chr( 10 ) ||
                      'Количество успешно загруженных строк( operations ) - ' || in_download_oper || chr( 10 ) ||
                      'Количество незагруженных строк( operations ) - ' || in_not_download_oper || chr( 10 ) ||
                      'Общее количество строк( members ) - ' || in_count_members || chr( 10 ) ||
                      'Количество успешно загруженных строк( members ) - ' || in_count_download_members || chr( 10 ) ||
                      'Количество незагруженных строк( members ) - ' || in_error_members || chr( 10 );
  end if;
  --
  if ( cnt = 0 ) then
       log_id := getMaxIdForLog();
       insert into aml_user.tb_operation_log c (
                                        id,
                                        p_date,
                                        p_operfile,
                                        p_memberfile,
                                        p_error
                                       )
       values (
                log_id,
                sysdate,
                in_oper_file,
                in_member_file,
                p_error_log
               );
       commit;
  else -- если есть то обновляем
       select min( id )
         into log_id
         from aml_user.tb_operation_log c
        where trunc( c.p_date, 'DDD' ) = trunc( sysdate, 'DDD' )
          and ( c.p_operfile = in_oper_file or c.p_memberfile = in_member_file );
       --
       update aml_user.tb_operation_log z set
              z.p_error = p_error_log,
              z.p_memberfile = in_member_file
        where z.id = log_id;
       commit;
  end if;
  --
  if ( ( in_not_download_oper > 0 ) and ( cnt - 1 <= in_not_download_oper ) ) then
         log_id := getMaxIdForLog();
         insert into aml_user.tb_operation_log c (
                                          id,
                                          p_date,
                                          p_operfile,
                                          p_memberfile,
                                          p_error
                                         )
         values (
                  log_id,
                  sysdate,
                  in_oper_file,
                  null,
                  in_error_oper
                 );
         commit;
  end if;
  --
  select count( * )
    into cnt1
    from aml_user.tb_operation_log t
   where trunc( t.p_date, 'DDD' ) = trunc( sysdate, 'DDD' )
     and ( p_memberfile = in_member_file );
  --
  if ( ( in_error_members > 0 ) and ( cnt1 - 1 <= in_error_members ) ) then
         log_id := getMaxIdForLog();
         insert into aml_user.tb_operation_log c (
                                          id,
                                          p_date,
                                          p_operfile,
                                          p_memberfile,
                                          p_error
                                         )
         values (
                  log_id,
                  sysdate,
                  null,
                  in_member_file,
                  in_member_err
                 );
         commit;
  end if;
  --
  if ( ( in_count_oper_n > 0 ) and ( cnt - 1 <= in_count_oper_n ) ) then
         log_id := getMaxIdForLog();
         insert into aml_user.tb_operation_log c (
                                          id,
                                          p_date,
                                          p_operfile,
                                          p_memberfile,
                                          p_error
                                         )
         values (
                  log_id,
                  sysdate,
                  in_oper_file,
                  null,
                  in_err_off
                 );
         commit;
  end if;
  --
end;
-- Find tb_suspiciousmembers.id by account
function getClientIdByAccount( in_account varchar2 ) return number is
  lv_clientBSId aml_user.tb_off_members.p_bsclientid%type;
  lv_account    aml_user.tb_off_members.p_account%type := trim( upper( in_account ) );
  ln_result     number;
begin
  if    ( length( lv_account ) = 20 ) then
          lv_clientBSId := substr( lv_account, 11, 7 );
  elsif ( length( lv_account ) = 10 ) then
          lv_clientBSId := substr( lv_account, 1, 7 );
  else
          ln_result := -1;
  end if;
  --
  if ( lv_clientBSId is not null ) then
       select nvl( max( m.id ), -1 )
         into ln_result
         from aml_user.tb_suspiciousmembers m
        where m.p_bsclientid = lv_clientBSId;
  else
       ln_result := -1;
  end if;
  --
  return ln_result;
exception
  when others then
       return -1;
end;
-- Find process_txt_operations.id by bankoperationid
function getOperationIdByBankOperId( in_bankoperationid in varchar2 ) return number is
  ln_result number := -1;
begin
  select nvl( max( id ), 0 )
    into ln_result
    from aml_user.process_txt_operations o
   where o.p_bankoperationid = in_bankoperationid;
  --
  if ( ln_result > 0 ) then
       return ln_result;
  else
       return -1;
  end if;
exception
  when others then
       return -1;
end;
-- Add additional info from JSON file
function addJSONDatas(
                       in_bankoperationid     in varchar2 default null, -- тег EnrichMsgId
                       in_OrigDbtrNm          in varchar2 default null, -- тег OrigDbtrNm
                       in_OrigDbtrCtryoR      in varchar2 default null, -- тег OrigDbtrCtryoR
                       in_OrigDbtrAccountIBAN in varchar2 default null, -- тег OrigDbtrAccountIBAN
                       in_OrigDbtrOrgIdBIC    in varchar2 default null, -- тег OrigDbtrOrgIdBIC
                       in_OrigCdtrNm          in varchar2 default null, -- тег OrigCdtrNm
                       in_OrigCdtrCtryoR      in varchar2 default null, -- тег OrigCdtrCtryoR
                       in_OrigCdtrAccountIBAN in varchar2 default null, -- тег OrigCdtrAccountIBAN
                       in_OrigCdtrOrgIdBIC    in varchar2 default null  -- тег OrigCdtrOrgIdBIC
                      ) return number is                                -- > 0 - SUCCESS, 0 - NOT FOUND, -1 - ERROR
  ln_operationid    aml_user. process_txt_operations.id%type := getOperationIdByBankOperId( in_bankoperationid );
  ln_result          number := 0;
  ln_cnt             number := 0;
  ln_payer_result    number := -1;
  ln_receiver_result number := -1;
begin
  --
  if    ( ln_operationid < 0 ) then
          ln_result := ln_operationid;
  elsif ( ln_operationid > 0 ) then
          --
          select count( * )
            into ln_cnt
            from aml_user.process_txt_members m
           where m.p_operationid = ln_operationid;
          --
          if ( ln_cnt > 0 ) then
               ln_result := ln_operationid;
               for i in ( select *
                            from aml_user.process_txt_members m
                           where m.p_operationid = ln_operationid
                         ) loop
                   if    ( i.p_clientrole = 1 ) then -- payer
                           begin
                             update aml_user.process_txt_members m set
                                    m.p_name_json        = in_OrigDbtrNm,
                                    m.p_countrycode_json = in_OrigDbtrCtryoR,
                                    m.p_bank_json        = in_OrigDbtrOrgIdBIC,
                                    m.p_account_json     = in_OrigDbtrAccountIBAN
                              where m.id = i.id;
                             --
                             ln_payer_result := 1;
                           exception
                             when others then
                                  ln_payer_result := -1;
                           end;
                   elsif ( i.p_clientrole = 2 ) then -- receiver
                           begin
                             update aml_user.process_txt_members m set
                                    m.p_name_json        = in_OrigCdtrNm,
                                    m.p_countrycode_json = in_OrigCdtrCtryoR,
                                    m.p_bank_json        = in_OrigCdtrOrgIdBIC,
                                    m.p_account_json     = in_OrigCdtrAccountIBAN
                              where m.id = i.id;
                              --
                              ln_receiver_result := 1;
                           exception
                             when others then
                                  ln_receiver_result := -1;
                           end;
                   end if;
               end loop;
               --
               if ( ( ln_payer_result > 0 ) and ( ln_receiver_result > 0 ) ) then
                      --
                      begin
                        update aml_user.process_txt_operations o set
                               o.p_issuedbid_json = 'DataLake'
                         where o.id = ln_operationid;
                        --
                        commit;
                      exception
                        when others then
                             ln_result := -1;
                             rollback;
                      end;
                      --
               else
                      ln_result := -1;
                      rollback;
               end if;
               --
          else
               ln_result := 0;
          end if;
  else
          ln_result := 0;
  end if;
  --
  return ln_result;
end;
-- Send all operations from integration tables to offline
function sendToOffline return number is
  ln_result        number := 1;
  ln_off_operation number;
  ln_off_members   number;
begin
  --
  for i in (
             select *
               from aml_user.process_txt_operations o
              where o.p_loaded = 0
            ) loop
      --
      uploadOperationToOffline( i.id, ln_off_operation );
      --
      if ( ln_off_operation > 0 ) then
           uploadOperMembersToOffline(
                                       in_process_txt_operations_id => i.id,
                                       in_id                        => ln_off_members
                                       );
      end if;
      --
      if ( ( ln_off_operation > 0 ) and ( ln_off_members > 0 ) ) then
             update aml_user.process_txt_operations o set
                    o.p_loaded = 1
              where o.id = i.id;
      end if;
      --
  end loop;
  --
  commit;
  --
  return ln_result;
exception
  when others then
       return -1;
end;
-- Upload operation from temporary table to offline
procedure uploadOperationToOffline(
                                    in_process_txt_operations_id in number,
                                    in_id                        in out number
                                   ) is
  ln_operation_id aml_user.process_txt_operations.id%type := in_process_txt_operations_id;
  ln_result       number;
  ln_cnt          number;
  rec_oper        process_txt_operations%rowtype;
begin
  --
  if ( nvl( in_process_txt_operations_id, -1 ) >= 0 ) then
       --
       select count( * )
         into ln_cnt
         from aml_user.process_txt_operations o
        where o.id = in_process_txt_operations_id;
       --
       if ( ln_cnt = 1 ) then
            select *
              into rec_oper
              from aml_user.process_txt_operations o
             where o.id = in_process_txt_operations_id;
            --
            aml_user.pr_offoper$update(
                               in_p_issuedbid         => nvl( rec_oper.p_issuedbid_json, nvl( rec_oper.p_issuedbid, 1 ) ),
                               in_p_bankoperationid   => rec_oper.p_bankoperationid,
                               in_p_ordernumber       => rec_oper.p_ordernumber,
                               in_p_branch            => rec_oper.p_ordernumber,
                               in_p_currencycode      => get_currency_num( rec_oper.p_currencycode ),
                               in_p_operationdatetime => to_date( rec_oper.p_operationdatetime,'DD.MM.YYYY HH24:MI:SS'),
                               in_p_baseamount        => rec_oper.p_baseamount,
                               in_p_currencyamount    => nvl( rec_oper.p_currencyamount, rec_oper.p_baseamount ),
                               in_p_eknpcode          => rec_oper.p_eknpcode,
                               in_p_docnumber         => rec_oper.p_docnumber,
                               in_p_docdate           => to_date( rec_oper.p_docdate, 'DD.MM.YYYY HH24:MI:SS' ),
                               in_p_doccategory       => to_number( nvl( rec_oper.p_doccategory, 0 ) ),
                               in_p_doctype           => rec_oper.p_doctype,
                               in_p_docsuspic         => to_number( rec_oper.p_docsuspic ),
                               in_p_operationstatus   => to_number( rec_oper.p_operationstatus ),
                               in_p_operationreason   => rec_oper.p_operationreason,
                               in_p_checked           => 0,
                               in_p_toextractbool     => 0,
                               in_p_date_insert       => rec_oper.p_date_insert,
                               in_p_date_update       => rec_oper.p_date_update,
                               in_p_history           => null,
                               in_p_property          => rec_oper.p_property,
                               in_p_propertynumber    => rec_oper.p_propertynumber,
                               in_p_suspic_comments   => rec_oper.p_suspic_comments,
                               in_p_operation_type    => rec_oper.p_operation_type,
                               in_product_code        => rec_oper.p_product_code,
                               in_id                  => in_id
                              );
            if ( sql%rowcount = 0 ) then
                 aml_user.pr_OffOper$insert(
                                    in_p_issuedbid         => nvl( rec_oper.p_issuedbid_json, nvl( rec_oper.p_issuedbid, 1 ) ),
                                    in_p_bankoperationid   => rec_oper.p_bankoperationid,
                                    in_p_ordernumber       => rec_oper.p_ordernumber,
                                    in_p_branch            => rec_oper.p_ordernumber,
                                    in_p_currencycode      => get_currency_num( rec_oper.p_currencycode ),
                                    in_p_operationdatetime => to_date( rec_oper.p_operationdatetime,'DD.MM.YYYY HH24:MI:SS'),
                                    in_p_baseamount        => rec_oper.p_baseamount,
                                    in_p_currencyamount    => nvl( rec_oper.p_currencyamount, rec_oper.p_baseamount ),
                                    in_p_eknpcode          => rec_oper.p_eknpcode,
                                    in_p_docnumber         => rec_oper.p_docnumber,
                                    in_p_docdate           => to_date( rec_oper.p_docdate, 'DD.MM.YYYY HH24:MI:SS' ),
                                    in_p_doccategory       => to_number( nvl( rec_oper.p_doccategory, 0 ) ),
                                    in_p_doctype           => rec_oper.p_doctype,
                                    in_p_docsuspic         => to_number( rec_oper.p_docsuspic ),
                                    in_p_operationstatus   => to_number( rec_oper.p_operationstatus ),
                                    in_p_operationreason   => rec_oper.p_operationreason,
                                    in_p_checked           => 0,
                                    in_p_toextractbool     => 0,
                                    in_p_date_insert       => rec_oper.p_date_insert,
                                    in_p_date_update       => rec_oper.p_date_update,
                                    in_p_history           => null,
                                    in_p_property          => rec_oper.p_property,
                                    in_p_propertynumber    => rec_oper.p_propertynumber,
                                    in_p_suspic_comments   => rec_oper.p_suspic_comments,
                                    in_p_operation_type    => rec_oper.p_operation_type,
                                    in_product_code        => rec_oper.p_product_code,
                                    in_id                  => in_id
                                   );
            end if;
       end if;
  end if;
  --
  if ( in_id > 0 ) then
       commit;
  end if;
  --
exception
  when others then
       aml_user.dbg$insert(

                   'UPLOADOPERATIONTOOFFLINE', substr( Sqlerrm, 1, 4000 ) );
                    aml_user.opers$insert( 'PROCESS_TXT_OPERATIONS', in_process_txt_operations_id, 'CANNOT ADD/UPDATE OPERATION process_txt_operations.id = ' || in_process_txt_operations_id || '!!!' );
       in_id := null;
       raise;
end;
-- Upload members from temporary table to offline
procedure uploadOperMembersToOffline(
                                      in_process_txt_operations_id in number,
                                      in_id                        in out number
                                     ) is
  /*************************************************************************************
  описание: запись данных в таблицу tb_off_members

  *************************************************************************************/
  ln_cnt           number;
  ln_errors        number := 0;
  ln_member_id     number := 0;
begin
  --
  select count( * )
    into ln_cnt
    from aml_user.process_txt_members m
   where m.p_operationid = in_process_txt_operations_id;
  --
  if ( ln_cnt > 0 ) then
       for i in (
                  select *
                    from aml_user.process_txt_members m
                   where m.p_operationid = in_process_txt_operations_id
                 ) loop
           aml_user.pr_offopermembers$update(
                                     in_operationid       => i.p_operationid,
                                     in_name              => nvl( i.p_name_json, i.p_name ),
                                     in_account           => nvl( i.p_account_json, i.p_account ),
                                     in_clientid          => i.p_clientid,
                                     in_bsclientid        => i.p_bsclientid,
                                     in_bank_client       => i.p_bank_client,
                                     in_regopendate       => i.p_regopendate,
                                     in_countrycode       => nvl( i.p_countrycode_json, i.p_countrycode ),
                                     in_client_type       => i.p_client_type,
                                     in_clientrole        => i.p_clientrole,
                                     in_clientkind        => i.p_clientkind,
                                     in_bsaccount         => i.p_bsaccount,
                                     in_bank              => nvl( i.p_bank_json, i.p_bank ),
                                     in_bankcountrycode   => i.p_bankcountrycode,
                                     in_bankname          => i.p_bankname,
                                     in_ipdl              => i.p_ipdl,
                                     in_date_insert       => i.p_date_insert,
                                     in_date_update       => i.p_date_update,
                                     in_username          => i.p_username,
                                     in_history           => null,
                                     in_lastname          => i.p_lastname,
                                     in_firstname         => i.p_firstname,
                                     in_middlename        => i.p_middlename,
                                     in_sdp               => i.p_sdp,
                                     in_orgform           => i.p_orgform,
                                     in_bankcity          => i.p_bankcity,
                                     in_counterparty_bank => i.p_counterparty_bank,
                                     in_ordering_bank     => i.p_ordering_bank,
                                     in_source_code       => i.p_source_code,
                                     in_remitter_bene     => i.p_remitter_bene,
                                     in_bank_details      => i.p_bank_details,
                                     in_client_add_info   => i.p_client_add_info,
                                     in_id                => ln_member_id
                                    );
           --
           if ( sql%rowcount = 0 ) then
                aml_user.pr_offopermembers$insert(
                                          in_operationid       => i.p_operationid,
                                          in_name              => nvl( i.p_name_json, i.p_name ),
                                          in_account           => nvl( i.p_account_json, i.p_account ),
                                          in_clientid          => i.p_clientid,
                                          in_bsclientid        => i.p_bsclientid,
                                          in_bank_client       => i.p_bank_client,
                                          in_regopendate       => i.p_regopendate,
                                          in_countrycode       => nvl( i.p_countrycode_json, i.p_countrycode ),
                                          in_client_type       => i.p_client_type,
                                          in_clientrole        => i.p_clientrole,
                                          in_clientkind        => i.p_clientkind,
                                          in_bsaccount         => i.p_bsaccount,
                                          in_bank              => nvl( i.p_bank_json, i.p_bank ),
                                          in_bankcountrycode   => i.p_bankcountrycode,
                                          in_bankname          => i.p_bankname,
                                          in_ipdl              => i.p_ipdl,
                                          in_date_insert       => i.p_date_insert,
                                          in_date_update       => i.p_date_update,
                                          in_username          => i.p_username,
                                          in_history           => null,
                                          in_lastname          => i.p_lastname,
                                          in_firstname         => i.p_firstname,
                                          in_middlename        => i.p_middlename,
                                          in_sdp               => i.p_sdp,
                                          in_orgform           => i.p_orgform,
                                          in_bankcity          => i.p_bankcity,
                                          in_counterparty_bank => i.p_counterparty_bank,
                                          in_ordering_bank     => i.p_ordering_bank,
                                          in_source_code       => i.p_source_code,
                                          in_remitter_bene     => i.p_remitter_bene,
                                          in_bank_details      => i.p_bank_details,
                                          in_client_add_info   => i.p_client_add_info,
                                          in_id                => ln_member_id
                                         );
           end if;
       end loop;
       --
  end if;
  --
exception
  when others then
       aml_user.dbg$insert( 'uploadOpermembersToOffline', substr( sqlerrm, 1, 4000 ) );
       in_id := -1;
       raise;
end;
/*
  -- Private type declarations
  type <TypeName> is <Datatype>;

  -- Private constant declarations
  <ConstantName> constant <Datatype> := <Value>;

  -- Private variable declarations
  <VariableName> <Datatype>;

  -- Function and procedure implementations
  function <FunctionName>(<Parameter> <Datatype>) return <Datatype> is
    <LocalVariable> <Datatype>;
  begin
    <Statement>;
    return(<Result>);
  end;

begin
  -- Initialization
  <Statement>;
*/
end pkg_upload_proc_utils;
/