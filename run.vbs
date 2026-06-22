Dim sh, dir
Set sh = CreateObject("WScript.Shell")
dir = Left(WScript.ScriptFullName, InStrRev(WScript.ScriptFullName, "\"))
sh.Run "powershell.exe -ExecutionPolicy Bypass -File """ & dir & "launch.ps1""", 0, False
Set sh = Nothing
