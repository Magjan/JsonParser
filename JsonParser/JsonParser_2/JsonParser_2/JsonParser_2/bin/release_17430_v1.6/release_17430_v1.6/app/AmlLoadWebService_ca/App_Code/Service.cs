using System;
using System.Web;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.Xml.Linq;
using System.Text;
using System.IO;
using System.Collections.Generic;
using System.Xml;
using System.Timers;
using System.Diagnostics;
using Oracle.ManagedDataAccess.Client;
using AmlLoadWebService;
using System.Data;
using System.Linq;
using System.Configuration;
using NPOI.SS.UserModel;
using NPOI.HSSF.UserModel;
using NPOI.HSSF.Model;
using NPOI.XSSF.UserModel;
using NPOI.XSSF.Model;
using NPOI.POIFS.FileSystem;
using NLog;
using System.Threading;
using System.Security;

[WebService(Namespace = "http://primesource.kz/")]
[WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
public class Service : System.Web.Services.WebService
{
    [WebMethod]
    public string StartAmlLoader()
    {
        OracleConnection conn;
        string path = @"D:\logs\AMLCyberArkIntegration\" + DateTime.Today.ToString("dd.MM.yyyy") + "_ca.txt";
        string log_msg;
        /*try
        {*/
            CyberArkConnection capass2a = new CyberArkConnection(System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"]);
            //Console.WriteLine(capass2._readyConnectionSting);
            string conStr = capass2a._readyConnectionSting;
            conn = new OracleConnection(conStr);
        /*}
        catch (Exception exc)
        {
            
            log_msg = DateTime.Now.ToString() + " - " + exc.ToString() + "\n\n";
            if (!File.Exists(path))
            {
                using (StreamWriter sw = File.CreateText(path))
                {
                    sw.WriteLine(log_msg);
                }
            }
            else
            {
                using (StreamWriter sw = File.AppendText(path))
                {
                    sw.WriteLine(log_msg);
                }
            }
        }*/

        //string directoryLoad,  string directoryError, string directoryArchive
        XElement answerex = new XElement("OPERATIONS");
        try
        {
            #region парсинг и сохранение файлов
            OracleConnectionClass oracle = new OracleConnectionClass();
            string directoryLoad;

            string directoryError;
            string directoryArchive;

            //string directory1 = System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"];
           // CyberArkConnection capass12 = new CyberArkConnection(System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"]);

            // заполнение переменных из файла app.config 
            // string directory = directoryLoad; //ConfigurationManager.AppSettings["directoryLoad"];
            string directory = System.Configuration.ConfigurationSettings.AppSettings["directoryLoad"];
            directoryError = System.Configuration.ConfigurationSettings.AppSettings["directoryError"];
            directoryArchive = System.Configuration.ConfigurationSettings.AppSettings["directoryArchive"];
			 Logger Logger = LogManager.GetCurrentClassLogger();
            if (!Directory.Exists(directory))
            {
                Directory.CreateDirectory(directory);
            }
            string fileExtension = "*.txt";
            string[] filePaths = Directory.GetFiles(directory, fileExtension).OrderByDescending(c => c).ToArray();

            string[] filePaths2 = Directory.GetFiles(directory, "*.xlsx").OrderByDescending(c => c).ToArray();

            //DirectoryInfo direct = new DirectoryInfo(directory);
           // FileInfo[] filesInDir = direct.GetFiles("*.xls");

            
            //int count = 1;
               int count_str_oper=0; //для строк операции
			   int count_str_members=0; // для строк участников
			   int count_str_oper_excel=0; // для строк эксель файла
			
			int count_all_opertion=0; // общее количество  операции 
			int err_oper = 0;   // количество ошибок загрузки операции
			int download_oper = 0; // количество загруженных операции
			string ex_oper_log;  //причина ошибки загрузки операции
			
			int count_all_members=0; // общее количество  участников
			int count_download_members=0; //количество загруженных участников
			int count_err_members=0; // количество ошибок загрузки учасников
			string err_member; // причина ошибки загрузки операции
			
			int count_all_operation_excel=0; //общее количество операции в  эксель файле
			int err_oper_excel=0; // количество ошибок загрузки операции в эксель файле
			int download_oper_excel=0; // количество загруженных операции в эксель файле
			string ex_oper_log_excel; // причина ошибки загрузки в эксель файла

            int count_err_off=0;
            int count_off=0;
            string error_off="";			
			
			
			string oper_file;
			string member_file;
			oper_file = "";
			member_file = "";
			err_member = "";
			ex_oper_log = ""; 
            foreach (string filePath in filePaths)
            {
				Logger.Info(filePath);

                int error = 0;
                if (File.Exists(filePath))
                {
                    //count++;

                    // считывание входящего файла 
                    IEnumerable<string> allLines = File.ReadLines(filePath);
                    List<Operation> operationList = new List<Operation>();

                    #region парсинг операций

                    try
                    {

                        IEnumerable<string> operationHeader = allLines.Where(d => d.StartsWith("AML reference", StringComparison.CurrentCultureIgnoreCase));
                        if (operationHeader.IsNotNull())
                        {
                            string[] header = operationHeader.ElementAt(0).Split('\t');

                            IEnumerable<string> operationLines = allLines.Where(d => !d.StartsWith("AML reference", StringComparison.CurrentCultureIgnoreCase));
							count_all_opertion=operationLines.Count();
							count_str_oper=1;
							oper_file = filePath;
							err_oper = 0;
							download_oper = 0;
							
                            foreach (string line in operationLines)
                            {
                                string errorBankNumber = "";
								string k="";
                                 count_str_oper++;
                                try
                                {
									Operation operation = new Operation();
									ex_oper_log="";
									

                                    // делитель - табуляция 
                                    string[] values = line.Split('\t');

                                    for (int i = 0; i < header.Length; i++)
                                    {
                                        if (header[i] == "AML reference")
										{
                                            operation.BankOperationId = values[i];
											
									errorBankNumber = operation.BankOperationId;
									k=count_str_oper.ToString();
										}
                                        else if (header[i] == "Operation ID")
										{
                                            operation.BankOperationId = values[i];
											
									errorBankNumber = operation.BankOperationId;
									 k=count_str_oper.ToString();
										}
                                        else if (header[i] == "Operation Module")
                                            operation.IssueDbId = values[i];
                                        else if (header[i] == "Branch")
                                            operation.Branch = values[i];
                                        else if (header[i] == "Currency Code")
                                            operation.CurrencyCode = values[i];
                                        else if (header[i] == "Date and time of operation")
                                            operation.OperationDateTime = values[i];
                                        else if (header[i] == "LCY amount")
                                            operation.BaseAmount = values[i];
                                        else if (header[i] == "FCY amount")
                                            operation.CurrencyAmount = values[i];
                                        else if (header[i] == "КНП")
                                            operation.EknpCode = values[i];
                                        else if (header[i] == "AML document number")
                                            operation.DocNumber = values[i];
                                        else if (header[i] == "AML document date")
                                            operation.DocDate = values[i];
                                        else if (header[i] == "AML document type")
                                            operation.DocCategory = values[i];
                                        else if (header[i] == "AML operation status")
                                            operation.OperationStatus = values[i];
                                        else if (header[i] == "Payment details")
                                            operation.OperationReason = values[i];
                                        else if (header[i] == "Document Type")
                                            operation.DocType = values[i];
                                        else if (header[i] == "Property Type")
                                            operation.Property = values[i];
                                        else if (header[i] == "Property Number")
                                            operation.PropertyNumber = values[i];
                                        else if (header[i] == "Suspition Type")
                                            operation.DocSuspic = values[i];
                                        else if (header[i] == "Suspition comments")
                                            operation.DocSuspicComments = values[i];
                                        else if (header[i] == "Operation Type")
                                            operation.OperationType = values[i];
                                        else if (header[i] == "PRODUCT_CODE")
                                            operation.Productcode = values[i];
                                    }
                                    operationList.Add(operation);
									//int k=i;
									errorBankNumber = operation.BankOperationId;

                                    oracle.insertLog(conn, filePath, operation.BankOperationId, "Разбор операции - успешно" + operation.BankOperationId, String.Empty);

                                    
									//oper_file = filePath;

                                    StoredProcOperation insertProcedure = new StoredProcOperation();
                                    insertProcedure.InsertOperation(
                                        conn,
                                        operation.BankOperationId,
                                        operation.IssueDbId,
                                        //operation.OrderNumber,
                                        operation.Branch,
                                        operation.CurrencyCode,
                                        operation.OperationDateTime,
                                        operation.BaseAmount,
                                        operation.CurrencyAmount,
                                        operation.EknpCode,
                                        operation.DocNumber,
                                        operation.DocDate,
                                        operation.DocCategory,
                                        operation.DocType,
                                        operation.DocSuspic,
                                        operation.OperationStatus,
                                        operation.OperationReason,
                                        operation.Property,
                                        operation.PropertyNumber,
                                        operation.DocSuspicComments,
                                        operation.OperationType,
                                        operation.Productcode,
                                        filePath
                                        );
										download_oper++;
                                }
                                catch (Exception ex)
                                {
																			
									err_oper++;
									ex_oper_log = "Operation - Ошибка данных при считывании из файла: " + ex.Message; 
									ex_oper_log = ex_oper_log + "Номер банковской операции: " + errorBankNumber+"  ";
									ex_oper_log = ex_oper_log+k+"строка";
                                    oracle.insertLog(conn, filePath, "Operation - Ошибка данных при считывании из файла: " + ex.Message, "Номер банковской операции: " + errorBankNumber, String.Empty);
                                }
								//logirovanie
								StoredProclog insertProclog = new StoredProclog();
                                insertProclog.log_operation(
                                    conn,
								count_all_opertion,
								download_oper,
								err_oper,
								ex_oper_log,
								oper_file,
								count_err_off,
								count_off,
								error_off,
								count_all_members,
								count_download_members,
								count_err_members,
								err_member,
								member_file
								);
                            }
                        }
                    }
                    catch (Exception e)
                    {
                        if (!Directory.Exists(directoryError))
                        {
                            Directory.CreateDirectory(directoryError);
                        }
                        string fileTo = string.Format(@"{0}\{1}", directoryError, Path.GetFileName(filePath));

                        File.Move(filePath, fileTo);

                        oracle.insertLog(conn, filePath, "Ошибка считывания файла операций", e.Message, String.Empty);

                        XDocument answer = new XDocument(
                                 new XDeclaration("1.0", Encoding.GetEncoding("UTF-16").HeaderName, "yes"),
                                    new XElement("Root",
                                        new XElement("Result", "-1"),
                                        new XElement("Error", e.Message)
                                    )
                              );
                    }
                    #endregion парсинг и сохранение операций


                    #region парсинг участников
                    List<Member> memberList = new List<Member>();
                    
                    try
                    {
                        IEnumerable<string> memberHeader = allLines.Where(d => d.StartsWith("Operation ID", StringComparison.CurrentCultureIgnoreCase));
						string g;
                        if (memberHeader.IsNotNull())
                        {
                            string[] header = memberHeader.ElementAt(0).Split('\t');
                            IEnumerable<string> memberLines = allLines.Where(d => !d.StartsWith("Operation ID", StringComparison.CurrentCultureIgnoreCase));
							count_all_members=memberLines.Count();
							count_download_members=0; 
						    count_err_members=0;
							count_str_members=1;
                            foreach (string line in memberLines)
                            {
                                string errorMember = "";
                                string errorOperation = "";
								string j="";
								count_str_members++;

                                try
                                {

                                    Member member = new Member();
                                    err_member="";
                                    // делитель - табуляция 
                                    string[] values = line.Split('\t');

                                    for (int i = 0; i < header.Length; i++)
                                    {
                                        if (header[i] == "Operation ID")
										{
                                            member.OperationID = values[i];
											errorOperation = member.OperationID;
											j=count_str_members.ToString();
										}
                                        else if (header[i] == "Client IIN")
                                            member.ClientId = values[i];
                                        else if (header[i] == "Client ID")
										{
                                            member.BsClientId = values[i];
											errorMember = member.BsClientId;
											j=count_str_members.ToString();
										}
                                        else if (header[i] == "Customer name")
                                            member.Name = values[i];
                                        else if (header[i] == "Bank Client")
                                            member.BankClient = values[i];
                                        else if (header[i] == "Customer Registration date")
                                            member.RegOpenDate = values[i];
                                        else if (header[i] == "Country Code")
                                            member.CountryCode = values[i];
                                        else if (header[i] == "Client Type")
                                            member.ClientType = values[i];
                                        else if (header[i] == "Client Role")
                                            member.ClientRole = values[i];
                                        else if (header[i] == "Client Kind")
                                            member.ClientKind = values[i];
                                        else if (header[i] == "Customer account")
                                            member.Account = values[i];
                                        else if (header[i] == "Customer balance account")
                                            member.BsAccount = values[i];
                                        else if (header[i] == "Bank code")
                                            member.Bank = values[i];
                                        else if (header[i] == "Bank Country Code")
                                            member.BankCountryCode = values[i];
                                        else if (header[i] == "Bank Name")
                                            member.BankName = values[i];
                                        else if (header[i] == "Publicity")
                                            member.Ipdl = values[i];
                                        else if (header[i] == "Last Name")
                                            member.LastName = values[i];
                                        else if (header[i] == "First Name")
                                            member.FirstName = values[i];
                                        else if (header[i] == "Middle Name")
                                            member.MiddleName = values[i];
                                        else if (header[i] == "SDP")
                                            member.Sdp = values[i];
                                        else if (header[i] == "Bank City")
                                            member.BankCity = values[i];
                                        //new
                                        else if (header[i] == "Counterparty Bank Name")
                                            member.CounterpartyBank = values[i];
                                        else if (header[i] == "Ordering Bank")
                                            member.OrderingBank = values[i];

                                        else if (header[i] == "Source Code")
                                            member.SourceCode = values[i];

                                        else if (header[i] == "Remitter/Bene Details")
                                            member.RemitterBene = values[i];

                                        else if (header[i] == "Bank Details")
                                            member.BankDetails = values[i];


                                        else if (header[i] == "Operation Type")
                                            member.OperationType = values[i];

                                        //new fields from 27.10.2015
                                        else if (header[i] == "ULT_BENEFICIARY1")
                                            member.Ult_Beneficiary1 = values[i];
                                        else if (header[i] == "ULT_BENEFICIARY2")
                                            member.Ult_Beneficiary2 += values[i];
                                        else if (header[i] == "ULT_BENEFICIARY3")
                                            member.Ult_Beneficiary3 += values[i];
                                        else if (header[i] == "ULT_BENEFICIARY4")
                                            member.Ult_Beneficiary4 += values[i];
                                        else if (header[i] == "ULT_BENEFICIARY5")
                                            member.Ult_Beneficiary5 += values[i];

                                        else if (header[i] == "BY_ORDER_OF1")
                                            member.By_Order_Of1 += values[i];
                                        else if (header[i] == "BY_ORDER_OF2")
                                            member.By_Order_Of2 += values[i];
                                        else if (header[i] == "BY_ORDER_OF3")
                                            member.By_Order_Of3 += values[i];
                                        else if (header[i] == "BY_ORDER_OF4")
                                            member.By_Order_Of4 += values[i];
                                        else if (header[i] == "BY_ORDER_OF5")
                                            member.By_Order_Of5 += values[i];

                                        // new fields from 04.11.2015
                                        else if (header[i] == "BEN_AC_NO")
                                            member.BenAcNo = values[i];

                                        else if (header[i] == "ORDERING_INSTITUTION1")
                                            member.OrderingInstitution1 += values[i];
                                        else if (header[i] == "ORDERING_INSTITUTION2")
                                            member.OrderingInstitution2 += values[i];
                                        else if (header[i] == "ORDERING_INSTITUTION3")
                                            member.OrderingInstitution3 += values[i];
                                        else if (header[i] == "ORDERING_INSTITUTION4")
                                            member.OrderingInstitution4 += values[i];
                                        else if (header[i] == "ORDERING_INSTITUTION5")
                                            member.OrderingInstitution5 += values[i];

                                        else if (header[i] == "Date and time of operation")
                                            member.DateTimeOperation = values[i];

                                        else if (header[i] == "CPTY_AC_NO")
                                            member.Cpty_Ac_No = values[i];

                                        else if (header[i] == "CPTY_NAME")
                                            member.Cpty_Name = values[i];


                                    }
                                    memberList.Add(member);

                                    //errorMember = member.BsClientId;
                                    //errorOperation = member.OperationID;

                                    oracle.insertLog(conn, filePath, member.ClientId, "Разбор участника - успешно", String.Empty);
									member_file = filePath;

                                    StoredProcMember insertProcedure = new StoredProcMember();
                                    insertProcedure.InsertMembers(
                                        conn,
                                        member.OperationID,
                                        member.ClientId,
                                        member.BsClientId,
                                        member.Name,
                                        member.LastName,
                                        member.FirstName,
                                        member.MiddleName,
                                        member.OrgForm,
                                        member.BankClient,
                                        member.RegOpenDate,
                                        member.CountryCode,
                                        member.ClientType,
                                        member.ClientRole,
                                        member.ClientKind,
                                        member.Account,
                                        member.BsAccount,
                                        member.BankName,
                                        member.BankCountryCode,
                                        member.Bank,
                                        member.BankCity,
                                        member.Ipdl,
                                        member.Sdp,
                                        //new
                                        member.CounterpartyBank,
                                        member.OrderingBank,
                                        member.SourceCode,
                                        member.RemitterBene,
                                        member.BankDetails,
                                        member.OperationType,
                                        member.Ult_Beneficiary1,
                                        member.Ult_Beneficiary2,
                                        member.Ult_Beneficiary3,
                                        member.Ult_Beneficiary4,
                                        member.Ult_Beneficiary5,
                                        member.By_Order_Of1,
                                        member.By_Order_Of2,
                                        member.By_Order_Of3,
                                        member.By_Order_Of4,
                                        member.By_Order_Of5,
                                        member.OrderingInstitution1,
                                        member.OrderingInstitution2,
                                        member.OrderingInstitution3,
                                        member.OrderingInstitution4,
                                        member.OrderingInstitution5,
                                        member.BenAcNo,
                                        member.bn_add_info,
                                        member.Cpty_Ac_No,
                                        member.Cpty_Name,

                                        member.DateTimeOperation,

                                        filePath
                                        );
										count_download_members++;

                                }
                                catch (Exception ex)
                                {
									count_err_members++;
									err_member = "Member - Ошибка данных при считывании из файла: " + ex.Message;
									err_member =err_member+ "Номер участника: " + errorMember + " Номер операции: " + errorOperation+" ";
                                    err_member = err_member + j + "строка";
                                    oracle.insertLog(conn, filePath, "Member - Ошибка данных при считывании из файла: " + ex.Message, "Номер участника: " + errorMember + " Номер операции: " + errorOperation, String.Empty);
                                }
								//logirovanie
								 StoredProclog insertProclog = new StoredProclog();
                                insertProclog.log_operation(
                                    conn,
								count_all_opertion,
								download_oper,
								err_oper,
								ex_oper_log,
								oper_file,
								count_err_off,
								count_off,
								error_off,
								count_all_members,
								count_download_members,
								count_err_members,
								err_member,
								member_file
								);

                            }
                        }
                    }
                    catch (Exception e)
                    {
                        if (!Directory.Exists(directoryError))
                        {
                            Directory.CreateDirectory(directoryError);
                        }
                        string fileTo = string.Format(@"{0}\{1}", directoryError, Path.GetFileName(filePath));

                        File.Move(filePath, fileTo);

                        oracle.insertLog(conn, filePath, "Ошибка разбора файла участников " + e.Message, e.Message, String.Empty);

                        XDocument answer = new XDocument(
                                 new XDeclaration("1.0", Encoding.GetEncoding("UTF-16").HeaderName, "yes"),
                                    new XElement("Root",
                                        new XElement("Result", "-1"),
                                        new XElement("Error", e.Message)
                                    )
                              );
                    }
                    #endregion парсинг и сохранение  участников
                }

                // перенос файла 
                if (!Directory.Exists(directoryArchive))
                {
                    Directory.CreateDirectory(directoryArchive);
                }
                string fileToArchive = string.Format(@"{0}\{1}", directoryArchive, Path.GetFileName(filePath));
                File.Move(filePath, fileToArchive);
                oracle.insertLog(conn, filePath, "Обработка файла успешна " + filePath, "Файл перенесен в архив", String.Empty);
            }

           /* OracleConnectionClass ora = new OracleConnectionClass();
            ora.LoadScenarios();*/
            try
            {
                foreach (string filepath in filePaths2)
                {

                    string documentdate = "";
					ex_oper_log_excel="";
					string filename = Path.GetFileNameWithoutExtension(filepath);
					string extension = Path.GetExtension(filepath);
                    if (File.Exists(filepath))
                    {
                        
                        count_all_operation_excel=0;
						err_oper_excel=0; 
			            download_oper_excel=0;
						count_str_oper_excel=1;

                        for (int i = 0; i < filename.Length - 7; i++)
                        {
                            if (Char.IsDigit(filename[i]) && Char.IsDigit(filename[i + 1]) && Char.IsDigit(filename[i + 2]) && Char.IsDigit(filename[i + 3]) && Char.IsDigit(filename[i + 4]) && Char.IsDigit(filename[i + 5]) && Char.IsDigit(filename[i + 6]) && Char.IsDigit(filename[i + 7]))
                            {
                                documentdate = filename.Substring(i + 6, 2) + "/" + filename.Substring(i + 4, 2) + "/" + filename.Substring(i, 4);
                            }
                        }
                        try
                        {

                            XSSFWorkbook xssfwb = null;
                            HSSFWorkbook hssfwb = null;
                            if (extension == ".xlsx")
                            {
                                using (FileStream file = new FileStream(filepath, FileMode.Open, FileAccess.Read))
                                {
                                    xssfwb = new XSSFWorkbook(file);

                                }


                            }
                            else
                            {

                                using (FileStream file = new FileStream(filepath, FileMode.Open, FileAccess.Read))
                                {
                                    hssfwb = new HSSFWorkbook(file);

                                }


                            }


                            ISheet sheet;

                            if (extension == ".xlsx")
                            {
                                sheet = xssfwb.GetSheetAt(0);
                            }
                            else
                                sheet = hssfwb.GetSheetAt(0);
							
						   //count_all_operation_excel=sheet.PhysicalNumberOfRows-4;
							
 
							//count_all_operation_excel=sheet.Columns.Count;
                            int row = 5;
                            int col = 2;
                            

                            col--;
                            row--;
                            if (!String.IsNullOrEmpty(documentdate))
                            {
                                /*CyberArkConnection capass23 = new CyberArkConnection(System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"]);
                                string connectionString = capass23._readyConnectionSting;
                                //string connectionString = System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"];
                                //ConfigurationManager.ConnectionStrings["OracleConnstring"].ConnectionString;*/
                                /*using (OracleConnection connection = new OracleConnection(connectionString))
                                {*/
                                    try
                                    {

                                        //connection.Open();
                                        OracleCommand cmdInsert = conn.CreateCommand();// connection.CreateCommand();
                                        cmdInsert.CommandText = "BEGIN " +
                                                                 "remove_datafromexcel( " +
                                                                "operationdate => :document_date " +
                                                                 " ); " +
                                                                 " COMMIT; " +
                                                                     "END;";





                                        cmdInsert.Parameters.Add(new OracleParameter("document_date", documentdate));
                                        cmdInsert.ExecuteNonQuery();
                                        cmdInsert.Dispose();
                                    }
                                    catch (Exception ex)
                                    {
                                        oracle.insertLog(conn, filepath, documentdate, String.Empty, "Ошибка операции - " + ex.Message);
                                       // answerex.Add(new XElement("Result", ex.ToString()));
                                    }
                                    finally
                                    {
                                        int i = 1;// connection.Close();
                                    }
                                //}

                                
                                while (sheet.GetRow(row) != null && sheet.GetRow(row).GetCell(col, MissingCellPolicy.RETURN_NULL_AND_BLANK) != null) 
                                {
                                    string errorBankNumber2 = "";
									count_all_operation_excel++;
									
                                    if (String.IsNullOrEmpty(System.Convert.ToString(sheet.GetRow(row).GetCell(col))))
                                    {
                                        throw new System.ArgumentException("Parameter cannot be empty", "BankoperationID");
                                    }
                                    try
                                    {


                                        string str1 = "";
                                        string str2 = "";
                                        string str3 = "";
                                        string str4 = "";
                                        string str5 = "";
                                        double str6 = 0.0;
                                        double str7 = 0.0;
                                        string str8 = "";
                                        string str9 = "";
                                        string str10 = "";
                                        string str11 = "";
                                        string str12 = "";
                                        string str13 = "";
                                        string str14 = "";
                                        string str15 = "";
                                        string str16 = "";
                                        string str17 = "";
                                        string str18 = "";
                                        string str19 = "";
                                        string str20 = "";
                                        string str21 = "";
                                        string str22 = "";
                                        string str23 = "";

                                        if (filename.Length > 26 && filename.Length <= 28)
                                        {
                                            str1 = System.Convert.ToString(sheet.GetRow(row).GetCell(col));
                                            errorBankNumber2 = str1;
                                            str2 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 1));
                                            str3 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 2));
                                            str4 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 3));
                                            str5 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 4));
                                            str6 = double.Parse((System.Convert.ToString(sheet.GetRow(row).GetCell(col + 5))));
                                            str7 = double.Parse((System.Convert.ToString(sheet.GetRow(row).GetCell(col + 6))));
                                            str8 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 7));
                                            str9 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 8));
                                            str10 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 9));
                                            str11 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 10));
                                            str12 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 11));
                                            str13 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 12));
                                            str14 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 13));
                                            str15 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 14));
                                            str16 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 15));
                                            str17 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 16));
                                            str18 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 17));
                                            str19 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 18));
                                            str20 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 19));
                                            str21 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 20));
                                            str22 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 21));
                                            str23 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 22));
                                        }

                                        else if (filename.Length > 31 && filename.Length <= 33)
                                        {
                                            str1 = System.Convert.ToString(sheet.GetRow(row).GetCell(col));
                                            errorBankNumber2 = str1;
                                            str2 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 1));
                                            str3 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 2));
                                            str4 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 3));
                                            str5 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 4));
                                            str6 = double.Parse((System.Convert.ToString(sheet.GetRow(row).GetCell(col + 6))));
                                            str7 = double.Parse((System.Convert.ToString(sheet.GetRow(row).GetCell(col + 5))));
                                            str8 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 15));
                                            str9 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 16));
                                            str10 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 17));
                                            str11 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 18));
                                            str12 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 19));
                                            str13 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 20));
                                            str14 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 21));
                                            str15 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 22));
                                            str16 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 7));
                                            str17 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 8));
                                            str18 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 9));
                                            str19 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 10));
                                            str20 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 11));
                                            str21 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 12));
                                            str22 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 13));
                                            str23 = System.Convert.ToString(sheet.GetRow(row).GetCell(col + 14));

                                        }

                                        if (!String.IsNullOrEmpty(str1))
                                        {
                                            oracle.insertLog(conn, filepath, str1, "Разбор операции - успешно", String.Empty);

                                            InsertProcess(conn, filepath,
                                                                    documentdate,
                                                                    str1,
                                                                    str2,
                                                                    str3,
                                                                    str4,
                                                                    str5,
                                                                    str6,
                                                                    str7,
                                                                    str8,
                                                                    str9,
                                                                    str10,
                                                                    str11,
                                                                    str12,
                                                                    str13,
                                                                    str14,
                                                                    str15,
                                                                    str16,
                                                                    str17,
                                                                    str18,
                                                                    str19,
                                                                    str20,
                                                                    str21,
                                                                    str22,
                                                                    str23);
											download_oper_excel++;

                                        }

                                        else
                                        {
                                            throw new System.ArgumentException("Parameter cannot be null", "BankoperationID");
                                        }

                                    }
                                    catch (Exception ex)
                                    {
										err_oper_excel++;
										ex_oper_log_excel = "Operation - Ошибка данных при считывании из файла: " + ex.Message; 
									    ex_oper_log_excel = ex_oper_log_excel + "Номер банковской операции: " + errorBankNumber2+"  ";
										ex_oper_log_excel = ex_oper_log_excel+"строка";
                                        oracle.insertLog(conn,filepath, "Operation - Ошибка данных при считывании из файла: " + ex.Message, "Номер банковской операции: " + errorBankNumber2, String.Empty);
                                        answerex.Add(new XElement("Result", ex.ToString()));
                                    }
                                    
                                    row++;
									count_all_members=0;
									count_download_members=0;
									count_err_members=0;
									err_member="";
									member_file="";
									StoredProclog insertProclog = new StoredProclog();
									/*insertProclog.log_operation(
									count_all_operation_excel,
									download_oper_excel,
									err_oper_excel,
									ex_oper_log_excel,
									filename,
									count_err_off,
									count_off,
									error_off,
									count_all_members,
									count_download_members,
									count_err_members,
									err_member,
									member_file
									);*/
                               }
                            }

                            
                        }

            #endregion


                        catch (Exception ex)
                        {
                            if (!Directory.Exists(directoryError))
                            {
                                Directory.CreateDirectory(directoryError);
                            }
                            string fileTo = string.Format(@"{0}\{1}", directoryError, Path.GetFileName(filepath));

                            
                            answerex.Add(new XElement("Result", ex.ToString()));
                            oracle.insertLog(conn, filepath, "Ошибка считывания файла операций", ex.Message, String.Empty);
							
							StoredProclog insertProclog = new StoredProclog();
									insertProclog.log_operation(
                                        conn,
									0,
									0,
									0,
									"Parameter cannot be null - BankoperationID",
								    filename,
								    0,
								    0,
								    "",
								    0,
								    0,
								    0,
							     	"",
							     	""
								);							
                            File.Move(filepath, fileTo);

                        }

                    }

                    if (!Directory.Exists(directoryArchive))
                    {
                        Directory.CreateDirectory(directoryArchive);
                    }
                    string fileToArchive = string.Format(@"{0}\{1}", directoryArchive, Path.GetFileName(filepath));

                    oracle.insertLog(conn, filepath, "Обработка файла успешна " + filepath, "Файл перенесен в архив", String.Empty);
                    File.Move(filepath, fileToArchive);
                    Insert_operation(conn, documentdate, 
									filename, 
									count_all_operation_excel,
									download_oper_excel,
									err_oper_excel,
									ex_oper_log_excel
									);
                    /* OracleConnectionClass oraex = new OracleConnectionClass();
                     oraex.LoadScenarioForExcel();*/
                    
                }
             
            }

            catch (Exception e)
            {
               /* OracleConnectionClass oraex = new OracleConnectionClass();
                oraex.LoadScenarioForExcel();*/
               // answerex.Add(new XElement("Result", e.ToString()));        
                XDocument answer2 = new XDocument(
                             new XDeclaration("1.0", Encoding.GetEncoding("UTF-16").HeaderName, "yes"),
                                new XElement("Root",
                                    new XElement("Result", "-1"),
                                    new XElement("Error", e.ToString(), answerex)
                    // answerex
                                )
                          );
                //return answer2.ToString();
            }

            XDocument answer1 = new XDocument(
                            new XDeclaration("1.0", Encoding.GetEncoding("UTF-16").HeaderName, "yes"),
                               new XElement("Root",
                                   new XElement("Result", "0"),
                                   new XElement("Error", ""),
                                   answerex
                               )
                         );




            return answer1.ToString();

            
        }

        catch (Exception e)
        {
            #region Обработка исключений
           /* OracleConnectionClass ora = new OracleConnectionClass();
            ora.LoadScenarios();*/
           // answerex.Add(new XElement("Result", e.ToString()));
            XDocument answer1 = new XDocument(
                             new XDeclaration("1.0", Encoding.GetEncoding("UTF-16").HeaderName, "yes"),
                                new XElement("Root",
                                    new XElement("Result", "-1"),
                                    new XElement("Error", e.Message)
                                   // answerex
                                )
                          );
            return answer1.ToString();
            #endregion Обработка исключений
        }finally{
			
			 Thread t1 = new Thread(new ThreadStart(RunJsonParser));
			 t1.Start();
				
				
		}


    }
	
	
	
	
	private void RunJsonParser()
    {
			ProcessStartInfo info = new ProcessStartInfo();
            //info.FileName = @"C:\LOADERS\JsonParserOperation\JsonParser.exe";
			Logger Logger = LogManager.GetCurrentClassLogger();
			Logger.Info(System.Configuration.ConfigurationSettings.AppSettings["RunLoaderPath"]);
			info.FileName = System.Configuration.ConfigurationSettings.AppSettings["RunLoaderPath"];
            info.Arguments = "";
            info.WindowStyle = ProcessWindowStyle.Normal;
            Process pro = Process.Start(info);
            pro.WaitForExit();
			
    } 
	
	
	
    public  void InsertProcess(OracleConnection connection, string filepath, string docdate, string str1, string str2, string str3, string str4, string str5, double str6, double str7, string str8, string str9, string str10, string str11, string str12, string str13,
        string str14, string str15, string str16, string str17, string str18, string str19, string str20, string str21, string str22, string str23)
    {

        
        OracleConnectionClass oracle = new OracleConnectionClass();
        //Console.WriteLine(System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"]);
        /*CyberArkConnection capass20 = new CyberArkConnection(System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"]);
        //Console.WriteLine(capass2._readyConnectionSting);
        string connectionString = capass20._readyConnectionSting;*/
        //string connectionString = System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"];
        //ConfigurationManager.ConnectionStrings["OracleConnstring"].ConnectionString;
        /*using (OracleConnection connection = new OracleConnection(connectionString))
        {*/
            try
            {
                if (connection.State != ConnectionState.Open) {
                     connection.Open();
                }
               
                OracleCommand cmdInsert = connection.CreateCommand();
                cmdInsert.CommandText = "BEGIN " +
                                         "set_datafromexcel( " +
                              "docdate          => :document_date, " +
                              "operation_number       => :operation_number, " +
                              "additional_information => :additional_information, " +
                              "operationdatetime      => :operationdatetime, " +
                              "eknp_code              => :eknp_code, " +
                              "op_currencycode        => :op_currencycode, " +
                              "op_baseamount          => :op_baseamount, " +
                              "op_currencyamount      => :op_currencyamount, " +
                              "payer_partyname        => :payer_partyname, " +
                              "isclient1              => :isclient1, " +
                              "client_number1         => :client_number1, " +
                              "BIN_number1            => :BIN_number1, " +
                              "payer_accnumber        => :payer_accnumber, " +
                              "payer_bankcode         => :payer_bankcode, " +
                              "payer_bankname         => :payer_bankname, " +
                              "payer_banklocatcountry => :payer_banklocatcountry, " +
                              "receiv_partyname       => :receiv_partyname, " +
                              "isclient2              => :isclient2, " +
                              "client_number2         => :client_number2, " +
                              "BIN_number2            => :BIN_number2, " +
                              "receiv_accnumber       => :receiv_accnumber, " +
                              "receiv_bankcode        => :receiv_bankcode, " +
                              "receiv_bankname        => :receiv_bankname, " +
                              "receiv_banklocatcountry => :receiv_banklocatcountry); " +
                                " COMMIT; " +
                                  "END;";





                cmdInsert.Parameters.Add(new OracleParameter("document_date", docdate));
                cmdInsert.Parameters.Add(new OracleParameter("operation_number", str1));
                cmdInsert.Parameters.Add(new OracleParameter("additional_information", str2));
                cmdInsert.Parameters.Add(new OracleParameter("operationdatetime", str3));
                cmdInsert.Parameters.Add(new OracleParameter("eknp_code", str4));
                cmdInsert.Parameters.Add(new OracleParameter("op_currencycode", str5));
                cmdInsert.Parameters.Add(new OracleParameter("op_baseamount", str6));
                cmdInsert.Parameters.Add(new OracleParameter("op_currencyamount", str7));
                cmdInsert.Parameters.Add(new OracleParameter("payer_partyname", str8));
                cmdInsert.Parameters.Add(new OracleParameter("isclient1", str9));
                cmdInsert.Parameters.Add(new OracleParameter("client_number1", str10));
                cmdInsert.Parameters.Add(new OracleParameter("BIN_number1", str11));
                cmdInsert.Parameters.Add(new OracleParameter("payer_accnumber", str12));
                cmdInsert.Parameters.Add(new OracleParameter("payer_bankcode", str13));
                cmdInsert.Parameters.Add(new OracleParameter("payer_bankname", str14));
                cmdInsert.Parameters.Add(new OracleParameter("payer_banklocatcountry", str15));
                cmdInsert.Parameters.Add(new OracleParameter("receiv_partyname", str16));
                cmdInsert.Parameters.Add(new OracleParameter("isclient2", str17));
                cmdInsert.Parameters.Add(new OracleParameter("client_number2", str18));
                cmdInsert.Parameters.Add(new OracleParameter("BIN_number2", str19));
                cmdInsert.Parameters.Add(new OracleParameter("receiv_accnumber", str20));
                cmdInsert.Parameters.Add(new OracleParameter("receiv_bankcode", str21));
                cmdInsert.Parameters.Add(new OracleParameter("receiv_bankname", str22));
                cmdInsert.Parameters.Add(new OracleParameter("receiv_banklocatcountry", str23));
                cmdInsert.ExecuteNonQuery();
                cmdInsert.Dispose();





               oracle.insertLog(connection, filepath, str1, String.Empty, "Сохранение операции в базу - успешно");


            }
            catch (Exception ex)
            {


                oracle.insertLog(connection, filepath, str1, String.Empty, "Ошибка cохранения операции в базу - " + ex.Message.Substring(0, 3500));
            }
            finally
            {
                int i = 1;//connection.Close();
            }
            
            
        //}

    }
    public void Insert_operation(OracleConnection conn, string operationdate, string filename,  int count_all_oper_excel, int down_oper_excel,int er_oper_excel,  string log_excel)
    {
        OracleConnectionClass oracle = new OracleConnectionClass();
        XElement answer = new XElement("OPERATIONS");

        //Console.WriteLine(System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"]);
        /*CyberArkConnection capass2 = new CyberArkConnection(System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"]);
        //Console.WriteLine(capass2._readyConnectionSting);
        string connectionString = capass2._readyConnectionSting;*/
        //string connectionString = System.Configuration.ConfigurationSettings.AppSettings["OracleConnstring"];
		int count_oper_op=0;
		int count_oper_not_download=0;
		string ex_oper_log_excel=" ";
        /*using (OracleConnection conn = new OracleConnection(connectionString))
        {*/
            try
            {

                if (conn.State != ConnectionState.Open)
                {
                    conn.Open();
                }
				OracleCommand cmd = new OracleCommand("insertparseddata_to", conn);
                cmd.CommandType = CommandType.StoredProcedure;
                cmd.BindByName = true;
				
				OracleParameter operationdate1 = new OracleParameter("operationdate", OracleDbType.Varchar2);
                operationdate1.Direction = ParameterDirection.Input;
                operationdate1.Value = operationdate;
                cmd.Parameters.Add(operationdate1);
				
				OracleParameter count_oper1 = new OracleParameter("count_oper", OracleDbType.Decimal);
                count_oper1.Direction = ParameterDirection.Output;
                cmd.Parameters.Add(count_oper1);
				
				OracleParameter count_oper_not_download1 = new OracleParameter("count_not_download_oper", OracleDbType.Decimal);
                count_oper_not_download1.Direction = ParameterDirection.Output;
                cmd.Parameters.Add(count_oper_not_download1);
				
				OracleParameter ex_oper_log_excel1 = new OracleParameter("err", OracleDbType.Varchar2);
                ex_oper_log_excel1.Direction = ParameterDirection.Output;
                cmd.Parameters.Add(ex_oper_log_excel1);
				cmd.Parameters["err"].Size = 4000;

                /*racleCommand cmdInsert = conn.CreateCommand();

                cmdInsert.CommandText = "BEGIN " +
                                        "insertparseddata_to( " +
                            " operationdate  =>  :operationdate); " +
                               " COMMIT; " +
                                 "END;";


                cmdInsert.Parameters.Add(new OracleParameter("operationdate", operationdate));*/
                cmd.ExecuteNonQuery();
				count_oper_op=Convert.ToInt32(cmd.Parameters["count_oper"].Value.ToString());
				count_oper_not_download=Convert.ToInt32(cmd.Parameters["count_not_download_oper"].Value.ToString());
				ex_oper_log_excel=cmd.Parameters["err"].Value.ToString();
                cmd.Dispose();


            }
            catch (Exception ex)
            {
				
				ex_oper_log_excel="Operation - Ошибка данных при считывании из базы: " + ex.Message;
                answer.Add(new XElement("Result", ex.ToString()));
                oracle.insertLog(conn, operationdate, "Operation - Ошибка данных при считывании из базы: " + ex.Message, "", String.Empty);
            }
			//count_all_members=0;
			
			StoredProclog insertProclog = new StoredProclog();
                                insertProclog.log_operation(
                                    conn,
								count_all_oper_excel,
								down_oper_excel,
								er_oper_excel,
								log_excel,
								filename,
								count_oper_op,
								count_oper_not_download,
								ex_oper_log_excel,
								0,
								0,
								0,
								"",
								""
								);
			//conn.Close();
            /*finally
            {
                conn.Close();
                
            }*/
            
        //}
    }
}



