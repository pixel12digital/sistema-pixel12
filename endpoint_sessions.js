// Endpoint para listar sessões
app.get('/sessions', (req, res) => {
    const sessions = Object.keys(whatsappClients).map(sessionName => ({
        name: sessionName,
        status: clientStatus[sessionName] || { status: 'unknown' }
    }));
    
    res.json({
        success: true,
        total: sessions.length,
        sessions: sessions
    });
}); 