<?php
// Formulário de contato do site RazorByte (index.html).
require __DIR__ . '/mailer.php';

processarContato([
    'pagina'   => 'index.html',
    'marca'    => 'RazorByte',
    'tema'     => 'razorbyte',
    // mesmos valores do <select id="assunto"> do index.html
    'assuntos' => ['Parceria comercial', 'Projeto Laço', 'Novo projeto', 'Suporte Laço', 'Dúvidas', 'Outro'],
]);
