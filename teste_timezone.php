<?php
// Teste de timezone PHP

date_default_timezone_set('America/Sao_Paulo');
echo '<b>Timezone:</b> ' . date_default_timezone_get() . '<br>';
echo '<b>Data/Hora (São Paulo):</b> ' . date('d/m/Y H:i:s') . '<br>';

date_default_timezone_set('UTC');
echo '<b>Data/Hora (UTC):</b> ' . date('d/m/Y H:i:s') . '<br>';
?> 