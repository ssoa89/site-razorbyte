<?php
// Formulário de contato do site Laço (index2.html).
require __DIR__ . '/mailer.php';

processarContato([
    'pagina'        => 'index2.html',
    'marca'         => 'Laço',
    'tema'          => 'laco',
    // mesmos valores do <select id="assunto"> do index2.html
    'assuntos'      => ['Quero ser cuidador', 'Parceria comercial', 'Problemas com profissionais',
                        'Problemas com o app Laço', 'Dúvidas', 'Outro'],
    'consentimento' => true,
]);
