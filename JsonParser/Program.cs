using System;
using log4net;
using System.Configuration;
using System.IO;
using Newtonsoft.Json;
using System.Collections.Generic;
using System.Linq;

namespace JsonParser
{
    internal class Program
    {

        private static readonly ILog logger = LogManager.GetLogger(typeof(Program));
        static void Main(string[] args)
        {
            log4net.Config.XmlConfigurator.Configure();

            string directory = ConfigurationManager.AppSettings["directory"];
            string directory_arch = ConfigurationManager.AppSettings["directory_arch"];
            
            logger.Info("directory = " + directory);

            string[] filePaths = Directory.GetFiles(directory);

            foreach (string filePath in filePaths) {

                logger.Info("filePath = " + filePath);

                using (StreamReader r = new StreamReader(filePath))
            {
                string json = r.ReadToEnd();

                string[] tokens = json.Split(new string[] { "\r\n", "\r", "\n" },StringSplitOptions.None);



                for (int i =0; i< tokens.Length; i++ ) {

                    logger.Info("json = " + tokens[i]);

                    Data item = JsonConvert.DeserializeObject<Data>(tokens[i]);

                    if (item!=null) {

                        if (item.OrigBdHdrMsgNmTp == "pacs.008" || item.OrigBdHdrMsgNmTp == "pacs.009") {


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
