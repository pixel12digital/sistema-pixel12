$body = @{
    url = "https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php"
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://212.85.11.238:3000/webhook/config" -Method POST -ContentType "application/json" -Body $body 