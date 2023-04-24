using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using NLog;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Security.Principal;
using System.Text;
using System.Threading.Tasks;

namespace JsonParser_2
{
    public class Program
    {

        private static Logger logger = LogManager.GetCurrentClassLogger();
        static void Main(string[] args)
        {


            string directory = ConfigurationManager.AppSettings["directory"];
            string directory_arch = ConfigurationManager.AppSettings["directory_arch"];

            logger.Info("directory = " + directory);

            string[] filePaths = Directory.GetFiles(directory);

            foreach (string filePath in filePaths)
            {

                logger.Info("filePath = " + filePath);

                using (StreamReader r = new StreamReader(filePath))
                {
                    string json = r.ReadToEnd();

                    string[] tokens = json.Split(new string[] { "\r\n", "\r", "\n" }, StringSplitOptions.None);

                    tokens = tokens.Where(x => !string.IsNullOrEmpty(x)).ToArray();
                    string lastjson = tokens[tokens.Length - 1];

                    try
                    {
                        JObject o = JObject.Parse(lastjson);

                        if (o.ContainsKey("recordCnt"))
                        {

                            string countOperationStr =  o["recordCnt"].ToString();
                            int countOperation = Int32.Parse(countOperationStr);

                            if (countOperation == 0) {
                                Console.WriteLine("JsonParser stops work  recordCnt = "+countOperation);
                                logger.Info("JsonParser stops work  recordCnt = " + countOperation);
                                return;
                            }


                        }

                    }
                    catch (Exception ex) {
                        logger.Info("cannot read last json from file  "+ lastjson);
                    }


                    for (int i = 0; i < tokens.Length; i++)
                    {

                        Data item = JsonConvert.DeserializeObject<Data>(tokens[i]);

                        if (item != null)
                        {

                            if (item.OrigBdHdrMsgNmTp == "pacs.008" || item.OrigBdHdrMsgNmTp == "pacs.009")
                            {


                                try
                                {
                                    StoredProcedures.addJSONDatas(item);

                                }
                                catch (Exception ex)
                                {
                                    logger.Error(ex.Message);

                                }

                            }
                        }

                    }

                    logger.Info("sendToOffline calling... ");
                    try
                    {
                        StoredProcedures.sendToOffline();

                    }
                    catch (Exception ex)
                    {
                        logger.Error(ex.Message);

                    }

                    logger.Info("sendToOffline finishes... ");

                }



            }



            String directoryName = directory_arch;
            DirectoryInfo dirInfo = new DirectoryInfo(directoryName);
            if (dirInfo.Exists == false)
                Directory.CreateDirectory(directoryName);

            List<String> MyFiles = Directory
                               .GetFiles(directory, "*.*", SearchOption.AllDirectories).ToList();

            foreach (string file in MyFiles)
            {
                FileInfo mFile = new FileInfo(file);

                if (new FileInfo(dirInfo + "\\" + mFile.Name).Exists == false)
                {
                    mFile.MoveTo(dirInfo + "\\" + mFile.Name);
                }
            }

            

        }
    }
}
