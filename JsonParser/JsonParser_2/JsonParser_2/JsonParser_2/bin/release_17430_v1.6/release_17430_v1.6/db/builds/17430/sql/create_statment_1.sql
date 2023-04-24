declare
  rec_report   tb_reports%rowtype; 
  rec_param    tb_report_parameters%rowtype;
  ln_count     number;
begin
  -- 
  select count( * )
    into ln_count 
    from tb_reports r
   where r.report_name = 'Отчет 1а. Операции, отправленные в КФМ от 10.2022';
  -- 
  if ( ln_count = 0 ) then
       select nvl( max( id ), 0 ) + 1
         into rec_report.id
         from aml_user.tb_reports r;
       --
       rec_report.report_name  := 'Отчет 1а. Операции, отправленные в КФМ от 10.2022';
       rec_report.view_name    := 'Отчет 1а. Операции, отправленные в КФМ от 10.2022';
       rec_report.description  := 'Отчет 1а. Операции, отправленные в КФМ от 10.2022';
       rec_report.order_number := 1;
       rec_report.xml_report   := q'[<?xml version="1.0"?>
<report>
  <queries>

<query id="1">
select max( db.p_code || ' - ' || db.p_longname ) as p_dbid,
       max( s.p_bankoperationid ) as p_bankoperationid,
       max( to_char( s.p_operationdatetime, 'dd.mm.yyyy hh24:mi:ss' ) ) as p_operationdatetime,
       max( stype.p_code || ' - ' || stype.p_longname ) as p_type,
       max( os.p_longname ) as p_oper_status,
       max( fmtype.p_code || ' - ' || fmtype.p_longname ) as p_fmtype,
       max( susptype.p_code || ' - ' || susptype.p_longname ) as p_susptype,
       max( cur.p_code || ' - ' || cur.p_longname ) as p_currencycode,
       max( s.p_currencyamount ) as p_currencyamount,
       max( s.p_baseamount ) as p_baseamount,
       max( s.p_eknpcode ) as p_eknpcode,
       sumstr( case when sm.p_clientrole = 1 then nvl( sm.p_name, '' ) || chr( 10 ) end ) as mem_dt_name,
       sumstr( case when sm.p_clientrole = 1 then decode( nvl( sm.p_bank_client, 2 ), 2, 'Не является', 'Является' ) || chr( 10 ) end ) as mem_dt_is_subject,
       sumstr( case when sm.p_clientrole = 1 then nvl( sm.p_clientid, '' ) || chr( 10 ) end ) as mem_dt_clientid,
       sumstr( case when sm.p_clientrole = 1 then nvl( mrol.p_longname, '' ) || chr( 10 ) end ) as mem_dt_role,
       sumstr( case when sm.p_clientrole = 1 then nvl( sm.p_countrycode, '' ) || chr( 10 ) end ) as mem_dt_residency,
       sumstr( case when sm.p_clientrole = 1 then nvl( sm.p_account, '' ) || chr( 10 ) end ) as mem_dt_account,
       sumstr( case when sm.p_clientrole = 1 then nvl( sm.p_bankname, '' ) || chr( 10 ) end ) as mem_dt_bank_name,
       sumstr( case when sm.p_clientrole = 1 then nvl( sm.p_bank, '' ) || chr( 10 ) end ) as mem_dt_bank_code,
       sumstr( case when sm.p_clientrole = 1 then nvl( sm.p_bankcountrycode, '' ) || chr( 10 ) end ) as mem_dt_bank_country,
       sumstr( case when sm.p_clientrole = 2 then case when nvl( sm.p_bankcountrycode, 'XXX' ) = '398' then 'Резидент' || chr( 10 ) else 'Нерезидент' || chr( 10 ) end  end ) as mem_dt_bank_residency,
       --
       sumstr( case when sm.p_clientrole = 2 then nvl( sm.p_name, '' ) || chr( 10 ) end ) as mem_ct_name,
       sumstr( case when sm.p_clientrole = 2 then decode( nvl( sm.p_bank_client, 2 ), 2, 'Не является', 'Является' ) || chr( 10 ) end ) as mem_ct_is_subject,
       sumstr( case when sm.p_clientrole = 2 then nvl( sm.p_clientid, '' ) || chr( 10 ) end ) as mem_ct_clientid,
       sumstr( case when sm.p_clientrole = 2 then nvl( mrol.p_longname, '' ) || chr( 10 ) end ) as mem_ct_role,
       sumstr( case when sm.p_clientrole = 2 then nvl( sm.p_countrycode, '' ) || chr( 10 ) end ) as mem_ct_residency,
       sumstr( case when sm.p_clientrole = 2 then nvl( sm.p_account, '' ) || chr( 10 ) end ) as mem_ct_account,
       sumstr( case when sm.p_clientrole = 2 then nvl( sm.p_bankname, '' ) || chr( 10 ) end ) as mem_ct_bank_name,
       sumstr( case when sm.p_clientrole = 2 then nvl( sm.p_bank, '' ) || chr( 10 ) end ) as mem_ct_bank_code,
       sumstr( case when sm.p_clientrole = 2 then nvl( sm.p_bankcountrycode, '' ) || chr( 10 ) end ) as mem_ct_bank_country,
       sumstr( case when sm.p_clientrole = 2 then case when nvl( sm.p_bankcountrycode, 'XXX' ) = '398' then 'Резидент' || chr( 10 ) else 'Нерезидент' || chr( 10 ) end end ) as mem_ct_bank_residency,

       -- sumstr( CASE when sm.p_clientrole not in ( 1, 2 ) then mrol.p_code || ' - ' || mrol.p_longname || ' - ' || nvl( sm.p_account, '' ) || ' - ' || sm.p_name || ' - ' || sm.p_bank || ' - ' || sm.p_bankname || chr( 10 ) end ) as MEM_OTH,
       max( s.p_operationreason ) as p_operationreason,
       max( '' ) as p_status,
       max( to_char( s.p_date_insert, 'dd.mm.yyyy hh24:mi:ss' ) ) as p_date_insert,
       -- max( s.p_username ) as p_username,
       max( s.p_mess_number ) as p_mess_number,
       max( s.p_mess_date ) as p_mess_date,
       max( s.p_criteriadifficulties ) as p_comment,
       max( s.p_operationextrainfo ) as p_extractinfo,
       max( s.p_docnumber ) as doc_number,
       max( s.p_docdate ) as doc_date,
       max( rkfm.p_receivedate ) as receive_date
  from tb_suspiciousoperations s,
       tb_dict_currency        cur,
       tb_susp_members         sm,
       tb_dict_suspic_type     stype,
       tb_dict_opercode        fmtype,
       tb_dict_member_type     mrol,
       tb_dict_suspicious      susptype,
       tb_dict_database        db,
       tb_receive_from_kfm     rkfm,
       tb_dict_operstatus      os
 where s.p_sendtokfmbool in ( 7, 1 )
   and s.p_currencycode = cur.p_code( + )
   and s.id = sm.p_suspiciousoperationid( + )
   and s.p_operationstatus = stype.p_code
   and s.p_suspicioustypecode = fmtype.p_code
   and sm.p_clientrole = mrol.p_code( + )
   and s.p_criteriafirst = susptype.p_code( + )
   and s.p_issuedbid = db.p_code( + )
   and s.id = rkfm.p_operationid( + )
   and s.p_operationstatus = to_number( os.p_code( + ) )
   and ( trunc( s.p_mess_date ) between :p1 and :p2 )
 group by s.id
 order by p_type, p_fmtype, p_mess_date







</query>

  </queries>
  <report_parameters>
     <param>
      <name>p1</name>
      <title>Начало периода</title>
      <datatype>DATE</datatype>
      <required>1</required>
     </param>
     <param>
      <name>p2</name>
      <title>Конец периода</title>
      <datatype>DATE</datatype>
      <required>1</required>
     </param>
  </report_parameters>

</report>

 ]';
       rec_report.xsl_text := q'[<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
  <xsl:template match="/">
    <xsl:processing-instruction name="mso-application">
      <xsl:text>progid="Excel.Sheet"</xsl:text>
    </xsl:processing-instruction>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Windows User</Author>
  <LastAuthor>Windows User</LastAuthor>
  <LastPrinted>2010-07-19T11:35:00Z</LastPrinted>
  <Created>2010-07-19T09:39:45Z</Created>
  <LastSaved>2010-07-20T04:34:00Z</LastSaved>
  <Version>12.00</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>8010</WindowHeight>
  <WindowWidth>19920</WindowWidth>
  <WindowTopX>360</WindowTopX>
  <WindowTopY>120</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s62">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s63">
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s64">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s65">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000" ss:Bold="1"/>
   <NumberFormat ss:Format="Short Date"/>
  </Style>
  <Style ss:ID="s67">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s68">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s69">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s70">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s71">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s72">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s73">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
    <NumberFormat ss:Format="Fixed"/>
  </Style>
  <Style ss:ID="s74">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s75">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s76">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="General Date"/>
  </Style>
  <Style ss:ID="s78">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s79">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s80">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s81">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s82">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="General Date"/>
  </Style>
  <Style ss:ID="s84">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s85">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s86">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s87">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="Fixed"/>
  </Style>
  <Style ss:ID="s88">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="Fixed"/>
  </Style>
  <Style ss:ID="s89">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s90">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s91">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="Fixed"/>
  </Style>
  <Style ss:ID="s92">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="Fixed"/>
  </Style>
  <Style ss:ID="s93">
   <Alignment ss:Vertical="Bottom"/>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000" ss:Bold="1"/>
   <NumberFormat ss:Format="Short Date"/>
  </Style>
  <Style ss:ID="s94">
   <Alignment ss:Vertical="Bottom"/>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s95">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Size="12" ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s96">
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s97">
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="General Date"/>
  </Style>
  <Style ss:ID="s99">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s100">
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="Fixed"/>
  </Style>
  <Style ss:ID="s101">
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
   <NumberFormat ss:Format="Fixed"/>
  </Style>
  <Style ss:ID="s102">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s103">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s104">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Times New Roman" x:CharSet="204" x:Family="Roman" ss:Color="#000000"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Отчет">
  <Table x:FullColumns="1" x:FullRows="1" ss:StyleID="s63">
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="100" ss:Span="1"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="120"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="130"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="130"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="100"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="40"/>
   <!-- <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/> -->
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <!-- <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/> -->
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/> 
   <!-- <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/> -->
   <!-- <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/> -->
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/>
   <!-- <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="100"/> -->
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="80"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="100"/>
   <!-- <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="80"/> -->
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="80"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="60"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="80"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/>
   
   <Row ss:Height="15.75">
    <Cell ss:MergeAcross="11" ss:StyleID="s95"><Data ss:Type="String">Отчет 1а. Операции, отправленные в КФМ</Data></Cell>
   </Row>
   
   <Row ss:Height="13.5"/>

    <xsl:for-each select="reportdata/report_parameters/parameter">
    <Row>
      <Cell><Data ss:Type="String"><xsl:value-of select="name"/></Data></Cell>
      <Cell ss:StyleID="s93"><Data ss:Type="String"><xsl:value-of select="value"/></Data></Cell>
    <Cell ss:StyleID="s63"/>
   </Row>
   </xsl:for-each> 
   
   <Row ss:Height="13.5"/>
   <Row ss:Height="13.5">
    <Cell ss:StyleID="s67"><Data ss:Type="String">№ операции</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Дата операции</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">База источник</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Тип отчетности</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Состояние операции</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Код вида операции</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Критерий подозрительности</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Валюта</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Сумма (вал.)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Сумма (тенге)</Data></Cell>    
    <Cell ss:StyleID="s68"><Data ss:Type="String">КНП</Data></Cell>        
    <!-- <Cell ss:StyleID="s68"><Data ss:Type="String">Участник ДТ</Data></Cell>       -->
    <Cell ss:StyleID="s68"><Data ss:Type="String">Поле Наименование для плательщика</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Клиент субъекта ФМ (Плательщик)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">ИИН/БИН Участника (Плательщик)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Тип участника операции (Плательщик)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Резидентство (Плательщик)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Номер счета (Плательщик)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Наименование банка (Плательщик)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Резидентство банка (Плательщик)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Код банка (Плательщик)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Страна местонахождения Банка (Плательщик)</Data></Cell>
    <!-- <Cell ss:StyleID="s68"><Data ss:Type="String">Участник КТ</Data></Cell>        -->
    <Cell ss:StyleID="s68"><Data ss:Type="String">Получатель по операции</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Клиент субъекта ФМ (Получатель)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">ИИН/БИН Участника (Получатель)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Тип участника операции (Получатель)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Резидентство (Получатель)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Номер счета (Получатель)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Наименование банка (Получатель)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Резидентство банка (Получатель)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Код банка (Получатель)</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Страна местонахождения Банка (Получатель)</Data></Cell>
    <!-- <Cell ss:StyleID="s68"><Data ss:Type="String">Иные участники</Data></Cell>        -->
    <!-- <Cell ss:StyleID="s68"><Data ss:Type="String">Назначение платежа</Data></Cell>             -->
    <Cell ss:StyleID="s68"><Data ss:Type="String">Основание совершения операции</Data></Cell>
    <!-- <Cell ss:StyleID="s68"><Data ss:Type="String">Выявлено</Data></Cell>    -->
    <Cell ss:StyleID="s67"><Data ss:Type="String">Номер документа</Data></Cell>
    <Cell ss:StyleID="s67"><Data ss:Type="String">Дата документа</Data></Cell>
    <!-- <Cell ss:StyleID="s68"><Data ss:Type="String">Пользователь</Data></Cell>        -->
    <Cell ss:StyleID="s68"><Data ss:Type="String">№ ФМ-1</Data></Cell>        
    <Cell ss:StyleID="s68"><Data ss:Type="String">Отправлено</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Получено</Data></Cell>
    <Cell ss:StyleID="s69"><Data ss:Type="String">Описание затруднений</Data></Cell>    
    <Cell ss:StyleID="s68"><Data ss:Type="String">Дополнительная информация</Data></Cell>             
   </Row>
   
 <xsl:for-each select="reportdata/resultset[@id=1]/row">
   
   <Row>
    <Cell ss:StyleID="s96"><Data ss:Type="String" x:Ticked="1"><xsl:value-of select="P_BANKOPERATIONID"/></Data></Cell>
    <Cell ss:StyleID="s97"><Data ss:Type="String" x:Ticked="1"><xsl:value-of select="P_OPERATIONDATETIME"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_DBID"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_TYPE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_OPER_STATUS"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_FMTYPE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_SUSPTYPE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_CURRENCYCODE"/></Data></Cell>
    <Cell ss:StyleID="s100"><Data ss:Type="Number"><xsl:value-of select="P_CURRENCYAMOUNT"/></Data></Cell>
    <Cell ss:StyleID="s100"><Data ss:Type="Number"><xsl:value-of select="P_BASEAMOUNT"/></Data></Cell>
    <Cell ss:StyleID="s99"><Data ss:Type="String"><xsl:value-of select="P_EKNPCODE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_NAME"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_IS_SUBJECT"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_CLIENTID"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_ROLE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_RESIDENCY"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_ACCOUNT"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_BANK_NAME"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_BANK_RESIDENCY"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_BANK_CODE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT_BANK_COUNTRY"/></Data></Cell>
    <!-- <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_DT"/></Data></Cell> -->
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_NAME"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_IS_SUBJECT"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_CLIENTID"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_ROLE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_RESIDENCY"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_ACCOUNT"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_BANK_NAME"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_BANK_RESIDENCY"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_BANK_CODE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT_BANK_COUNTRY"/></Data></Cell>
    <!-- <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_CT"/></Data></Cell> -->
    <!-- <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="MEM_OTH"/></Data></Cell> -->
    <!-- <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_OPERATIONREASON"/></Data></Cell> -->
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_OPERATIONREASON"/></Data></Cell>
    <!-- <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_DATE_INSERT"/></Data></Cell> -->
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="DOC_NUMBER"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="DOC_DATE"/></Data></Cell>
    <!-- <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_USERNAME"/></Data></Cell> -->
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_MESS_NUMBER"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_MESS_DATE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="RECEIVE_DATE"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_COMMENT"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="P_EXTRACTINFO"/></Data></Cell>    
    
   </Row> 
  
  </xsl:for-each> 
   
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Layout x:Orientation="Landscape"/>
    <Header x:Margin="0"/>
    <Footer x:Margin="0"/>
    <PageMargins x:Bottom="0.39370078740157483" x:Left="0.39370078740157483" x:Right="0.39370078740157483" x:Top="0.39370078740157483"/>
   </PageSetup>
   <Print>
    <ValidPrinterInfo/>
    <Scale>80</Scale>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>600</VerticalResolution>
   </Print>
   <Selected/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>13</ActiveRow>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
  </xsl:template>
</xsl:stylesheet>
]'; 
       --
       insert into tb_reports ( id, report_name, view_name, description, order_number, xml_report, xsl_text )
       values ( rec_report.id, rec_report.report_name, rec_report.view_name, rec_report.description, rec_report.order_number, rec_report.xml_report, rec_report.xsl_text );
       commit;
       --
       select nvl( max( id ), 0 ) + 1
         into rec_param.id
         from tb_report_parameters p;
       --
       rec_param.report_id  := rec_report.id;
       rec_param.name       := 'P1';
       rec_param.param_type := 'D';
       rec_param.param_name := 'Начало периода';
       rec_param.required   := 1;
       --
       insert into tb_report_parameters ( id, report_id, name, param_type, param_name, required )
       values ( rec_param.id, rec_param.report_id, rec_param.name, rec_param.param_type, rec_param.param_name, rec_param.required );
       commit;
       --
       select nvl( max( id ), 0 ) + 1
         into rec_param.id
         from tb_report_parameters p;
       --
       rec_param.report_id  := rec_report.id;
       rec_param.name       := 'P2';
       rec_param.param_type := 'D';
       rec_param.param_name := 'Конец периода';
       rec_param.required   := 1;
       --
       insert into tb_report_parameters ( id, report_id, name, param_type, param_name, required )
       values ( rec_param.id, rec_param.report_id, rec_param.name, rec_param.param_type, rec_param.param_name, rec_param.required );
       commit;
       
  end if;  
end;
/