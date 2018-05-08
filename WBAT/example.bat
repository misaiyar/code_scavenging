
#composer.bat
@php "%~dp0composer.phar" %*

#tool.bat
@D:\phpdev\bin\php\php7.0.4\php.exe "%~dp0Tool.php" %*

#timex.bat
@IF "%*" NEQ "" ( D:\phpdev\bin\php\php7.0.4\php -r "echo strtotime('%1');" ) else ( echo Input your express! )

#md5.bat
@if exist "%1" (D:\phpdev\bin\php\php7.0.4\php -r "echo hash_file('md5','%1');") else (D:\phpdev\bin\php\php7.0.4\php -r "echo md5('%1');")
