create or replace package pkg_upload_proc_utils is

  -- Author  : ANDREY
  -- Created : 03.10.2022 20:57:54
  -- Purpose : Utils for uploading datas from files
  
  -- Public type declarations
  /*
  type <TypeName> is <Datatype>;
  
  -- Public constant declarations
  <ConstantName> constant <Datatype> := <Value>;

  -- Public variable declarations
  <VariableName> <Datatype>;

  -- Public function and procedure declarations
  function <FunctionName>(<Parameter> <Datatype>) return <Datatype>;
  */
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
                                         );
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
                                       );                                         
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
                               );  
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
                               );
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
                                  );
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
                                  );                                  
-- Find tb_suspiciousmembers.id by account
function getClientIdByAccount( in_account varchar2 ) return number;
-- Find process_txt_operations.id by bankoperationid
function getOperationIdByBankOperId( in_bankoperationid in varchar2 ) return number;
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
                      ) return number;                                  -- > 0 - SUCCESS, 0 - NOT FOUND, -1 - ERROR
-- Send all operations from integration tables to offline
function sendToOffline return number;
-- Upload to offline from integration table
procedure uploadOperationToOffline( 
                                    in_process_txt_operations_id in number,
                                    in_id                        in out number
                                   );
-- Upload members from temporary table to offline
procedure uploadOperMembersToOffline( 
                                      in_process_txt_operations_id in number,
                                      in_id                        in out number
                                     );
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
                             );

end pkg_upload_proc_utils;
/