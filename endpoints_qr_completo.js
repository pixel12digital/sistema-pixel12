// Endpoint específico para QR Code
app.get('/qr', (req, res) => {
    const sessionName = req.query.session || 'default';
    
    if (!whatsappClients[sessionName]) {
        return res.status(404).json({
            success: false,
            message: `Sessão ${sessionName} não encontrada`,
            suggestion: 'Inicie uma sessão primeiro usando POST /session/start/default'
        });
    }
    
    const status = clientStatus[sessionName];
    
    if (!status) {
        return res.status(503).json({
            success: false,
            message: 'Status da sessão não disponível'
        });
    }
    
    if (status.status === 'connected') {
        return res.json({
            success: true,
            qr: null,
            ready: true,
            message: 'WhatsApp já está conectado',
            status: 'connected'
        });
    }
    
    if (status.status === 'qr_ready' && status.qr) {
        return res.json({
            success: true,
            qr: status.qr,
            ready: false,
            message: 'QR Code disponível para escaneamento',
            status: 'qr_ready'
        });
    }
    
    return res.status(503).json({
        success: false,
        qr: null,
        ready: false,
        message: 'QR Code não disponível no momento',
        status: status.status,
        suggestion: 'Aguarde alguns segundos e tente novamente'
    });
});

// Endpoint para QR Code da sessão default (compatibilidade)
app.get('/qr/default', (req, res) => {
    // Redirecionar para o endpoint principal
    res.redirect('/qr?session=default');
});

// Endpoint para QR Code da sessão específica
app.get('/qr/:sessionName', (req, res) => {
    const { sessionName } = req.params;
    res.redirect(`/qr?session=${sessionName}`);
}); 