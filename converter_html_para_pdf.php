<?php
/**
 * Conversor HTML para PDF - Sistema Loja Virtual
 * Oferece múltiplas opções para converter a documentação em PDF
 */

echo "🔄 Conversor de Documentação HTML para PDF\n";
echo "==========================================\n\n";

// Verificar se o arquivo HTML foi gerado
$html_file = 'documentacao_sistema_loja_virtual.html';
if (!file_exists($html_file)) {
    echo "❌ Arquivo HTML não encontrado. Execute primeiro: php gerar_documentacao_pdf.php\n";
    exit;
}

echo "✅ Arquivo HTML encontrado: $html_file\n\n";

// Opção 1: Usar wkhtmltopdf (se disponível)
echo "📋 Opção 1: Conversão automática com wkhtmltopdf\n";
echo "------------------------------------------------\n";

$wkhtmltopdf_paths = [
    'wkhtmltopdf',
    'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe',
    '/usr/local/bin/wkhtmltopdf',
    '/usr/bin/wkhtmltopdf'
];

$wkhtmltopdf_found = false;
foreach ($wkhtmltopdf_paths as $path) {
    $output = shell_exec("which $path 2>/dev/null || where $path 2>/dev/null");
    if (!empty($output)) {
        $wkhtmltopdf_found = true;
        $wkhtmltopdf_path = trim($output);
        break;
    }
}

if ($wkhtmltopdf_found) {
    echo "✅ wkhtmltopdf encontrado em: $wkhtmltopdf_path\n";
    
    $pdf_file = 'documentacao_sistema_loja_virtual.pdf';
    $command = "$wkhtmltopdf_path --page-size A4 --margin-top 15 --margin-bottom 15 --margin-left 15 --margin-right 15 --encoding UTF-8 $html_file $pdf_file";
    
    echo "🔄 Convertendo...\n";
    $result = shell_exec($command . " 2>&1");
    
    if (file_exists($pdf_file)) {
        echo "✅ PDF gerado com sucesso: $pdf_file\n";
        echo "📁 Tamanho: " . number_format(filesize($pdf_file) / 1024, 2) . " KB\n";
    } else {
        echo "❌ Erro na conversão: $result\n";
    }
} else {
    echo "❌ wkhtmltopdf não encontrado\n";
}

echo "\n";

// Opção 2: Usar Chrome/Chromium headless
echo "📋 Opção 2: Conversão com Chrome/Chromium headless\n";
echo "--------------------------------------------------\n";

$chrome_paths = [
    'chrome',
    'chromium',
    'C:\Program Files\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium-browser'
];

$chrome_found = false;
foreach ($chrome_paths as $path) {
    $output = shell_exec("which $path 2>/dev/null || where $path 2>/dev/null");
    if (!empty($output)) {
        $chrome_found = true;
        $chrome_path = trim($output);
        break;
    }
}

if ($chrome_found) {
    echo "✅ Chrome/Chromium encontrado em: $chrome_path\n";
    
    $pdf_file = 'documentacao_sistema_loja_virtual_chrome.pdf';
    $html_url = 'file://' . realpath($html_file);
    $command = "$chrome_path --headless --disable-gpu --print-to-pdf=$pdf_file --print-to-pdf-no-header $html_url";
    
    echo "🔄 Convertendo...\n";
    $result = shell_exec($command . " 2>&1");
    
    if (file_exists($pdf_file)) {
        echo "✅ PDF gerado com sucesso: $pdf_file\n";
        echo "📁 Tamanho: " . number_format(filesize($pdf_file) / 1024, 2) . " KB\n";
    } else {
        echo "❌ Erro na conversão: $result\n";
    }
} else {
    echo "❌ Chrome/Chromium não encontrado\n";
}

echo "\n";

// Opção 3: Instruções manuais
echo "📋 Opção 3: Conversão manual\n";
echo "-----------------------------\n";
echo "Se as opções automáticas não funcionaram, você pode:\n\n";

echo "🌐 1. Abrir o arquivo HTML no navegador:\n";
echo "   - Abra: $html_file\n";
echo "   - Pressione Ctrl+P para imprimir\n";
echo "   - Selecione 'Salvar como PDF'\n\n";

echo "📱 2. Usar serviços online:\n";
echo "   - https://www.ilovepdf.com/html-to-pdf\n";
echo "   - https://smallpdf.com/html-to-pdf\n";
echo "   - https://pdfcrowd.com/html-to-pdf/\n\n";

echo "💻 3. Instalar ferramentas:\n";
echo "   - wkhtmltopdf: https://wkhtmltopdf.org/downloads.html\n";
echo "   - WeasyPrint: pip install weasyprint\n";
echo "   - Puppeteer: npm install puppeteer\n\n";

// Opção 4: Gerar arquivo de instruções
echo "📋 Opção 4: Gerando arquivo de instruções\n";
echo "----------------------------------------\n";

$instructions = '
# 📄 Instruções para Converter HTML em PDF

## Arquivo HTML Gerado
- **Arquivo:** ' . $html_file . '
- **Tamanho:** ' . number_format(filesize($html_file) / 1024, 2) . ' KB
- **Data:** ' . date('d/m/Y H:i:s') . '

## Métodos de Conversão

### 1. 🌐 Navegador (Mais Fácil)
1. Abra o arquivo `' . $html_file . '` no seu navegador
2. Pressione `Ctrl+P` (ou `Cmd+P` no Mac)
3. Selecione "Salvar como PDF"
4. Escolha as opções de página (A4, margens, etc.)
5. Clique em "Salvar"

### 2. 💻 Linha de Comando

#### Com wkhtmltopdf:
```bash
# Instalar wkhtmltopdf
# Windows: https://wkhtmltopdf.org/downloads.html
# Linux: sudo apt-get install wkhtmltopdf
# Mac: brew install wkhtmltopdf

wkhtmltopdf --page-size A4 --margin-top 15 --margin-bottom 15 --margin-left 15 --margin-right 15 --encoding UTF-8 ' . $html_file . ' documentacao_sistema_loja_virtual.pdf
```

#### Com Chrome headless:
```bash
chrome --headless --disable-gpu --print-to-pdf=documentacao_sistema_loja_virtual.pdf --print-to-pdf-no-header file://' . realpath($html_file) . '
```

### 3. 🌍 Serviços Online
- **ILovePDF:** https://www.ilovepdf.com/html-to-pdf
- **SmallPDF:** https://smallpdf.com/html-to-pdf
- **PDFCrowd:** https://pdfcrowd.com/html-to-pdf/

### 4. 📱 Aplicativos
- **Adobe Acrobat Reader DC**
- **Microsoft Edge** (tem conversor integrado)
- **Google Chrome** (Ctrl+P → Salvar como PDF)

## Estrutura do PDF Gerado

O PDF conterá:
- ✅ Índice completo com links
- ✅ Todas as seções da documentação
- ✅ Códigos formatados
- ✅ Tabelas organizadas
- ✅ Quebras de página automáticas
- ✅ Estilo profissional

## Informações do Sistema
- **Versão:** 2.0.0
- **Ambiente:** ' . (isset($is_local) && $is_local ? 'Desenvolvimento Local' : 'Produção') . '
- **Banco:** ' . (defined('DB_HOST') ? DB_HOST . '/' . DB_NAME : 'Não configurado') . '
- **VPS WhatsApp:** 212.85.11.238:3000

## Suporte
Se tiver problemas com a conversão:
- Email: suporte@pixel12digital.com.br
- GitHub: https://github.com/pixel12digital/revenda-sites
';

file_put_contents('INSTRUCOES_CONVERSAO_PDF.md', $instructions);
echo "✅ Arquivo de instruções gerado: INSTRUCOES_CONVERSAO_PDF.md\n";

echo "\n🎉 Processo concluído!\n";
echo "📁 Arquivos gerados:\n";
echo "   - $html_file (HTML formatado)\n";
echo "   - INSTRUCOES_CONVERSAO_PDF.md (Instruções)\n";

if (file_exists('documentacao_sistema_loja_virtual.pdf')) {
    echo "   - documentacao_sistema_loja_virtual.pdf (PDF via wkhtmltopdf)\n";
}
if (file_exists('documentacao_sistema_loja_virtual_chrome.pdf')) {
    echo "   - documentacao_sistema_loja_virtual_chrome.pdf (PDF via Chrome)\n";
}

echo "\n💡 Dica: O método mais fácil é abrir o HTML no navegador e usar Ctrl+P → Salvar como PDF\n";
?> 