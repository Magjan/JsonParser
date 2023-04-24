using System;
using System.Threading;
using CyberArk.AIM.NetPasswordSDK;
using CyberArk.AIM.NetPasswordSDK.Exceptions;
using NLog;

namespace JsonParser_2
{
    public class CyberArkConnection
    {
        private static Logger logger = LogManager.GetCurrentClassLogger();
        public string _readyConnectionSting;
        private string _passFromCyber;
        private string _cyberUser;

        public CyberArkConnection(string password_from_app)
        {

            /*string cUser = System.Configuration.ConfigurationSettings.AppSettings["cyber_user"];
            string cAppId = System.Configuration.ConfigurationSettings.AppSettings["cyber_appId"];
            string cRequest = System.Configuration.ConfigurationSettings.AppSettings["cyber_req"];*/

            string cUser = System.Configuration.ConfigurationManager.AppSettings["cyber_user"];
            string cAppId = System.Configuration.ConfigurationManager.AppSettings["cyber_appId"];
            string cRequest = System.Configuration.ConfigurationManager.AppSettings["cyber_req"];

            logger.Info("cUser = "+ cUser);
            logger.Info("cAppId = "+ cAppId);
            logger.Info("cRequest = "+ cRequest);

            string passFromCyberArk = "";

            if (String.IsNullOrEmpty(cUser) || String.IsNullOrEmpty(cAppId) || String.IsNullOrEmpty(cRequest))
            {
                _passFromCyber = "";
                _cyberUser = "";
                _readyConnectionSting = "CYBER_USER OR CYBER_REQUEST IS NULL";
                return;
            }

            try
            {

                PSDKPasswordRequest passRequest = new PSDKPasswordRequest();
                PSDKPassword password = null;
                passRequest.SetAttribute("AppDescs.AppID", cAppId);
                passRequest.SetAttribute("Query", cRequest);

                int retryInterval = 100;

                for (int i = 0; i < 5; i++)
                {
                    try
                    {
                        password = PasswordSDK.GetPassword(passRequest);
                        break;
                    }
                    catch (PSDKRequestFailedOnPasswordChange ex)
                    {

                        passFromCyberArk = "CyberArk did not return the password, sorry...";
                        Thread.Sleep(retryInterval);
                    }
                }
                Thread.Sleep(retryInterval / 10);

                /*while (password == null)
                {
                    try
                    {
                        password = PasswordSDK.GetPassword(passRequest);
                        Thread.Sleep(retryIntervals);
                    }
                    catch (PSDKRequestFailedOnPasswordChange ex)
                    {

                        passFromCyberArk = "CyberArk did not return the password, sorry...";
                    }
                }*/

                passFromCyberArk = password.Content;
                _passFromCyber = passFromCyberArk;
                _cyberUser = cUser;
                password_from_app = password_from_app.Replace("User Id=****", "User Id=" + _cyberUser);//меняем на нужного пользователя
                password_from_app = password_from_app.Replace("Password=****", "Password=" + _passFromCyber);//меняем на пароль из CyberARK
                _readyConnectionSting = password_from_app;//передаем в переменную для чтения
            }
            catch (PSDKException ex)
            {

                passFromCyberArk = ex.ToString();/*"EXCEPTION" + "\n" +
                                //"DateTime = " + (DateTime.Now).ToString() + "\n" +
                                "Message = " + ex.Message + "\n" +
                                "StackTrace = " + ex.StackTrace.ToString() + "\n" +
                                "InnerException = " + ex.InnerException + "\n";*/
                _readyConnectionSting = passFromCyberArk;
            }

        }
    }
}
