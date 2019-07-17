::Sourcetree export file
::工具->选项->自定义操作 参数：$REPO $FILE
@SETLOCAL ENABLEDELAYEDEXPANSION
@CHCP 65001
@SET export_dir=C:\Users\Administrator\Desktop\www.zip\www
@SET source_dir=%1
@SET source_files=%*
@RMDIR  /s/q %export_dir%
@mkdir %export_dir%
@for %%i in (%source_files%) do @(
	@SET TMDA=%%i
	@SET KVS=!TMDA:/=\!
	@IF not "%%i" =="%source_dir%" @(
		@MD "%export_dir%\!KVS!"
		@RMDIR "%export_dir%\!KVS!" /s/q
		@COPY "%source_dir%\!KVS!" "%export_dir%\!KVS!"
		@ECHO "%%i ==> !KVS! is copied"
	)
)

@explorer %export_dir%
@EXIT 0
