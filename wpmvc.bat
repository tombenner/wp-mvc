@echo off
rem usage : wpmvc.bat generate plugin MyPlugin
rem Make sure the PHP directory is set in the PATH environement variable
for %%F in ("%0") do set mvc_core_path=%%~dpF
set "mvc_core_path=%mvc_core_path%core\"
php -q "%mvc_core_path%wpmvc.php" %*