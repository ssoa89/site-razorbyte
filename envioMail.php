<?php
// Formulário de contato do site RazorByte (index.html).
require __DIR__ . '/mailer.php';

processarContato([
    'pagina' => 'index.html',
    'marca'  => 'RazorByte',
]);
