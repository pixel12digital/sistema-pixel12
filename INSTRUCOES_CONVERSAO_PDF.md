
# 📄 Instruções para Converter HTML em PDF

## Arquivo HTML Gerado
- **Arquivo:** documentacao_sistema_loja_virtual.html
- **Tamanho:** 26.69 KB
- **Data:** 01/08/2025 18:06:46

## Métodos de Conversão

### 1. 🌐 Navegador (Mais Fácil)
1. Abra o arquivo `documentacao_sistema_loja_virtual.html` no seu navegador
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

wkhtmltopdf --page-size A4 --margin-top 15 --margin-bottom 15 --margin-left 15 --margin-right 15 --encoding UTF-8 documentacao_sistema_loja_virtual.html documentacao_sistema_loja_virtual.pdf
```

#### Com Chrome headless:
```bash
chrome --headless --disable-gpu --print-to-pdf=documentacao_sistema_loja_virtual.pdf --print-to-pdf-no-header file://C:\xampp\htdocs\loja-virtual-revenda\documentacao_sistema_loja_virtual.html
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
- **Ambiente:** Produção
- **Banco:** Não configurado
- **VPS WhatsApp:** 212.85.11.238:3000

## Suporte
Se tiver problemas com a conversão:
- Email: suporte@pixel12digital.com.br
- GitHub: https://github.com/pixel12digital/revenda-sites
