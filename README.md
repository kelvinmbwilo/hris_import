hris_import
===========

A console command module for importing data to record table from ecxcel to be used in hrhis project

How To use
**********
 create a folder call it Command in RecordsBundle
 
 then save the ExcelCommand.php file in Command folder.
 
 change the path of excel file to match the file in your computer on line 54
 
 $phpExcelObject = $this->getContainer()->get('phpexcel')->createPHPExcelObject('your excel path here');
 
 run the command  php app/console demo:fill
