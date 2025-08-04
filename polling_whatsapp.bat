@echo off
cd /d "C:\xampp\htdocs\loja-virtual-revenda"
"C:\xampp\php\php.exe" polling_mensagens_whatsapp.php >> polling.log 2>&1
