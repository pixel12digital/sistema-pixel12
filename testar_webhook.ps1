$body = @{
    test = "true"
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://212.85.11.238:3000/webhook/test" -Method POST -ContentType "application/json" -Body $body 