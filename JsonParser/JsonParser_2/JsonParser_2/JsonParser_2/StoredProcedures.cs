using NLog;
using Oracle.ManagedDataAccess.Client;
using System;
using System.Collections.Generic;
using System.Data;
using System.Text;


namespace JsonParser_2
{
    public class StoredProcedures
    {

        private static Logger logger = LogManager.GetCurrentClassLogger();

        public static void addJSONDatas(Data item)
        {




            string connectionString = System.Configuration.ConfigurationManager.AppSettings["ConnectionString"];

            logger.Info("connectionString = " + connectionString);

            CyberArkConnection capass2a = new CyberArkConnection(connectionString);

            string conStr = capass2a._readyConnectionSting;

            logger.Info("conStr = " + conStr);

            using (OracleConnection con = new OracleConnection(System.Configuration.ConfigurationManager.AppSettings["ConnectionString"]))
            {


                try
                {



                    logger.Info("Connection to database open...");
                    con.Open();

                    logger.Info("pkg_upload_proc_utils.addJSONDatas");

                    OracleCommand cmdGetOperationStatus = new OracleCommand("pkg_upload_proc_utils.addJSONDatas", con);
                    cmdGetOperationStatus.CommandType = CommandType.StoredProcedure;
                    cmdGetOperationStatus.BindByName = true;


                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_bankoperationid", OracleDbType.Varchar2, item.EnrichMsgId));
                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_origdbtrnm", OracleDbType.Varchar2, item.OrigDbtrNm));
                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_origdbtrctryor", OracleDbType.Varchar2, item.OrigDbtrCtryoR));
                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_origdbtraccountiban", OracleDbType.Varchar2, item.OrigDbtrAccountIBAN));
                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_origdbtrorgidbic", OracleDbType.Varchar2, item.OrigDbtrOrgIdBIC));
                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_origcdtrnm", OracleDbType.Varchar2, item.OrigCdtrNm));
                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_origcdtrctryor", OracleDbType.Varchar2, item.OrigCdtrCtryoR));
                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_origcdtraccountiban", OracleDbType.Varchar2, item.OrigCdtrAccountIBAN));
                    cmdGetOperationStatus.Parameters.Add(CreateOracleParameter("in_origcdtrorgidbic", OracleDbType.Varchar2, item.OrigCdtrOrgIdBIC));




                    OracleParameter result = new OracleParameter("result", OracleDbType.Decimal);
                    result.Direction = ParameterDirection.ReturnValue;
                    cmdGetOperationStatus.Parameters.Add(result);



                    cmdGetOperationStatus.ExecuteNonQuery();





                    /* if (Int16.Parse(p_result_code.Value.ToString()) == -1)
                     {
                         logger.Error("p_operation_id =  " + p_operation_id.Value + "\n" +
                                             "p_result_code = " + p_result_code.Value + "\n" +
                                             "p_result_message = " + p_result_message.Value);
                     }
                     else
                     {

                         logger.Debug("p_operation_id =  " + p_operation_id.Value + "\n" +
                                             "p_result_code = " + p_result_code.Value + "\n" +
                                             "p_result_message = " + p_result_message.Value);
                     }*/

                }
                catch (Exception ex)
                {

                    logger.Error(ex.Message);

                    //   total.RESULT = "-1";
                    //  total.COMMENT = ex.Message;

                }
                finally
                {
                    con.Close();

                }

            }





        }



        public static void sendToOffline()
        {




            string connectionString = System.Configuration.ConfigurationManager.AppSettings["ConnectionString"];

           logger.Info("connectionString = " + connectionString);

            CyberArkConnection capass2a = new CyberArkConnection(connectionString);

            string conStr = capass2a._readyConnectionSting;

            logger.Info("conStr = " + conStr);

            using (OracleConnection con = new OracleConnection(System.Configuration.ConfigurationManager.AppSettings["ConnectionString"]))
            {


                try
                {



                    logger.Info("Connection to database open...");

                    con.Open();

                    logger.Info("pkg_upload_proc_utils.sendToOffline");

                    OracleCommand cmdGetOperationStatus = new OracleCommand("pkg_upload_proc_utils.sendToOffline", con);
                    cmdGetOperationStatus.CommandType = CommandType.StoredProcedure;
                    cmdGetOperationStatus.BindByName = true;


                    OracleParameter result = new OracleParameter("result", OracleDbType.Decimal);
                    result.Direction = ParameterDirection.ReturnValue;
                    cmdGetOperationStatus.Parameters.Add(result);



                    cmdGetOperationStatus.ExecuteNonQuery();



                }
                catch (Exception ex)
                {

                    logger.Error(ex.Message);

                }
                finally
                {
                    con.Close();

                }

            }

        }







        private static OracleParameter CreateOracleParameter(string parameterName, OracleDbType type, string value)
        {
            OracleParameter parameter = new OracleParameter(parameterName, type);
            parameter.Direction = ParameterDirection.Input;

            if (type == OracleDbType.Decimal)
            {
                if (!String.IsNullOrEmpty(value)) parameter.Value = Convert.ToInt32(value);
            }
            else if (type == OracleDbType.Date)
            {
                if (!String.IsNullOrEmpty(value)) parameter.Value = Convert.ToDateTime(value);
            }
            else if (type == OracleDbType.Double)
            {
                if (!String.IsNullOrEmpty(value))
                {
                    double d;
                    if (double.TryParse(value, out d))
                    {
                        parameter.Value = d;
                    }
                    else
                    {
                        parameter.Value = value;
                    }
                }
            }
            else
            {
                parameter.Value = value;
            }

            return parameter;
        }
    }
}
