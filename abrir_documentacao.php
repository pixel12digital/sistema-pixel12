<?php
/**
 * Abrir Documentação HTML no Navegador
 * Sistema Loja Virtual
 */

echo "🌐 Abrindo Documentação no Navegador\n";
echo "===================================\n\n";

$html_file = 'documentacao_sistema_loja_virtual.html';

if (!file_exists($html_file)) {
    echo "❌ Arquivo HTML não encontrado!\n";
    echo "Execute primeiro: php gerar_documentacao_pdf.php\n";
    exit;
}

echo "✅ Arquivo encontrado: $html_file\n";
echo "📁 Tamanho: " . number_format(filesize($html_file) / 1024, 2) . " KB\n\n";

$full_path = realpath($html_file);
echo "🔄 Abrindo no navegador...\n";

// Detectar sistema operacional e abrir no navegador
$os = strtoupper(substr(PHP_OS, 0, 3));

if ($os === 'WIN') {
    // Windows
    $command = "start $full_path";
    echo "💻 Comando Windows: $command\n";
    shell_exec($command);
} elseif ($os === 'DAR') {
    // macOS
    $command = "open $full_path";
    echo "🍎 Comando macOS: $command\n";
    shell_exec($command);
} else {
    // Linux
    $command = "xdg-open $full_path";
    echo "🐧 Comando Linux: $command\n";
    shell_exec($command);
}

echo "\n✅ Documentação aberta no navegador!\n\n";

echo "📋 Instruções para gerar PDF:\n";
echo "============================\n\n";

echo "1. 🌐 No navegador aberto:\n";
echo "   - Pressione Ctrl+P (ou Cmd+P no Mac)\n";
echo "   - Selecione 'Salvar como PDF'\n";
echo "   - Escolha as opções de página (A4, margens)\n";
echo "   - Clique em 'Salvar'\n\n";

echo "2. 📱 Serviços online (alternativa):\n";
echo "   - ILovePDF: https://www.ilovepdf.com/html-to-pdf\n";
echo "   - SmallPDF: https://smallpdf.com/html-to-pdf\n";
echo "   - PDFCrowd: https://pdfcrowd.com/html-to-pdf/\n\n";

echo "3. 💻 Linha de comando:\n";
echo "   - Instale wkhtmltopdf: https://wkhtmltopdf.org/downloads.html\n";
echo "   - Execute: wkhtmltopdf --page-size A4 $html_file documentacao.pdf\n\n";

echo "📄 Arquivos disponíveis:\n";
echo "=======================\n";
echo "   - $html_file (HTML formatado)\n";
echo "   - INSTRUCOES_CONVERSAO_PDF.md (Instruções detalhadas)\n";
echo "   - gerar_documentacao_pdf.php (Gerador principal)\n";
echo "   - converter_html_para_pdf.php (Conversor automático)\n\n";

echo "🎉 Documentação completa gerada com sucesso!\n";
echo "📧 Suporte: suporte@pixel12digital.com.br\n";
?> 