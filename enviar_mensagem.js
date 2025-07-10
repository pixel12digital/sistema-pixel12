const http = require('http');

const data = JSON.stringify({
  to: '5547996164699',
  message: 'Olá! Esperamos que esteja bem. Identificamos que há um pagamento pendente em seu nome em nosso sistema. Por favor, regularize sua situação para evitar a suspensão dos serviços. Caso já tenha efetuado o pagamento, desconsidere esta mensagem. Em caso de dúvidas, estamos à disposição para ajudar. Obrigado pela atenção!'
});

const options = {
  hostname: 'localhost',
  port: 3000,
  path: '/send',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(data, 'utf8')
  }
};

const req = http.request(options, (res) => {
  let response = '';
  res.on('data', (chunk) => {
    response += chunk;
  });
  res.on('end', () => {
    console.log('Resposta do robô:', response);
  });
});

req.on('error', (error) => {
  console.error('Erro ao enviar mensagem:', error);
});

req.write(data, 'utf8');
req.end(); 