declare
  rec_report   tb_reports%rowtype; 
  rec_param    tb_report_parameters%rowtype;
  ln_count     number;
begin
  -- 
  select count( * )
    into ln_count 
    from tb_reports r
   where r.report_name = 'Отчет 5. Пользователи и их роли от 10.2022';
  -- 
  if ( ln_count = 0 ) then
       select nvl( max( id ), 0 ) + 1
         into rec_report.id
         from tb_reports r;
       --
       rec_report.report_name  := 'Отчет 5. Пользователи и их роли от 10.2022';
       rec_report.view_name    := 'Отчет 5. Пользователи и их роли от 10.2022';
       rec_report.description  := 'Отчет 5. Пользователи и их роли от 10.2022';
       rec_report.order_number := 16;
       rec_report.xml_report   := q'[<?xml version="1.0"?>
<report>
  <queries>

<query id="1">
SELECT TU.P_USERNAME,
       TR.p_Rolename,
       rg.p_name as group_name,
       r.changedate
  FROM TB_USERS TU, TB_USER_ROLES r, TB_ROLES TR, tb_role_groups rg, tb_group_roles gr
 where TR.ID = R.P_ROLE_ID
   AND R.P_USER_ID = TU.ID
   and gr.id_role = tr.id
   and rg.id = gr.id_group
   and r.changedate between :p1 and :p2
   and tu.p_username = :p3
   
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
     <param>
      <name>p3</name>
      <title>Пользователь</title>
      <datatype>LIST</datatype>
      <directory>VW_TB_USERS</directory>
      <required>1</required>
     </param>
  </report_parameters>


</report>]';
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
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="250"/>
   <Column ss:StyleID="s63" ss:AutoFitWidth="0" ss:Width="70"/>

   <Row ss:Height="15.75">
    <Cell ss:MergeAcross="11" ss:StyleID="s95"><Data ss:Type="String">Отчет: "Пользователи и их роли"</Data></Cell>
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
    <Cell ss:StyleID="s67"><Data ss:Type="String">Пользователь</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Роль</Data></Cell>
    <Cell ss:StyleID="s68"><Data ss:Type="String">Группа</Data></Cell>
   </Row>

 <xsl:for-each select="reportdata/resultset[@id=1]/row">

   <Row>
    <Cell ss:StyleID="s96"><Data ss:Type="String" x:Ticked="1"><xsl:value-of select="P_USERNAME"/></Data></Cell>
    <Cell ss:StyleID="s97"><Data ss:Type="String" x:Ticked="1"><xsl:value-of select="P_ROLENAME"/></Data></Cell>
    <Cell ss:StyleID="s103"><Data ss:Type="String"><xsl:value-of select="GROUP_NAME"/></Data></Cell>
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
       --
       select nvl( max( id ), 0 ) + 1
         into rec_param.id
         from tb_report_parameters p;
       --
       rec_param.report_id      := rec_report.id;
       rec_param.name           := 'P3';
       rec_param.param_type     := 'L';
       rec_param.param_name     := 'Пользователь';
       rec_param.required       := 1;
       rec_param.directory_list := 'VW_TB_USERS';
       --
       insert into tb_report_parameters ( id, report_id, name, param_type, param_name, required, directory_list )
       values ( rec_param.id, rec_param.report_id, rec_param.name, rec_param.param_type, rec_param.param_name, rec_param.required, rec_param.directory_list );
       commit;
       --
  end if;  
end;
/