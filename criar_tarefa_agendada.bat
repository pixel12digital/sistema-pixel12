@echo off
echo Configurando polling automatico do WhatsApp...

REM Criar tarefa agendada
schtasks /create /tn "WhatsApp_Polling" /tr "C:\xampp\htdocs\loja-virtual-revenda\polling_whatsapp.bat" /sc minute /mo 1 /ru SYSTEM /f

IF %ERRORLEVEL% EQU 0 (
    echo ✅ Tarefa criada com sucesso!
    echo Verificando se a tarefa foi criada...
    schtasks /query /tn "WhatsApp_Polling"
) ELSE (
    echo ❌ Erro ao criar tarefa. Tentando com usuario atual...
    schtasks /create /tn "WhatsApp_Polling" /tr "C:\xampp\htdocs\loja-virtual-revenda\polling_whatsapp.bat" /sc minute /mo 1 /f
)

echo.
echo Polling configurado! Agora o sistema vai verificar mensagens a cada 1 minuto.
echo Para parar o polling, execute: schtasks /delete /tn "WhatsApp_Polling" /f
echo.
pause 