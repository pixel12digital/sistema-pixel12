// Endpoint para logout/desconectar sessão
app.post('/api/logout', async (req, res) => {
    const { identificador } = req.body;
    if (!identificador || !sessions[identificador] || !sessions[identificador].sock) {
        return res.status(400).json({ success: false, error: 'Sessão não encontrada' });
    }
    try {
        await sessions[identificador].sock.logout();
        delete sessions[identificador];
        res.json({ success: true });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
}); 