Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c C:\Development\php\matchoracle-be\run-scheduler.bat", 0, True
Set WshShell = Nothing