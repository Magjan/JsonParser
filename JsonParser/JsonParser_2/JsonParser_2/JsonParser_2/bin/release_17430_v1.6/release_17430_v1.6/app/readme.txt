Заявка: 17430
Патч: release_17430_v1.1
Дата: 04.01.2023
Автор: Сабазбеков Макжан
Описание: Установка сервис и загрузчик для парса жсон

1 Необходимо заменить файл Amlloadwebservice\App_Code\Service.cs
2 Необходимо заменить файл Amlloadwebservice\web.config
3 Необходимо устанвить загрузчик JsonParser в папку LOADERS 
4 Необходимо заменить файл officer\system\application\controller\page.php


в файле Amlloadwebservice\web.config
в родителськом теге appSettings необходимо изменить тег app с аттрибутом RunLoaderPath (если тег нету то необходимо создать внутри тега appSettings 
						Пример "<add key ="RunLoaderPath" value="" />") 
необходимо изменить значение на D:\inetpub\wwwroot\LOADERS\JsonParser\JsonParser_2.exe
 