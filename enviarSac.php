<?php
// Formulário de contato do site Laço (index2.html).
require __DIR__ . '/mailer.php';

processarContato([
    'pagina'        => 'index2.html',
    'marca'         => 'Laço',
    'consentimento' => true,
]);
