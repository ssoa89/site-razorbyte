<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/email/templates.php';

/** Conexão SMTP do Gmail já configurada. */
function novoMailer(array $config): PHPMailer
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';
    return $mail;
}

/**
 * Processa o formulário de contato e redireciona de volta para a página.
 *
 * $opcoes:
 *   pagina         página de volta (ex.: "index.html")
 *   marca          nome usado no assunto/remetente (ex.: "RazorByte")
 *   tema           visual do e-mail: "razorbyte" ou "laco"
 *   assuntos       lista de assuntos aceitos (os mesmos do <select> do formulário)
 *   consentimento  exige o checkbox "consentimento" (LGPD)
 */
function processarContato(array $opcoes): void
{
    $pagina = $opcoes['pagina'];
    $marca  = $opcoes['marca'];
    $tema   = $opcoes['tema'] ?? 'razorbyte';

    $voltar = function (array $query) use ($pagina) {
        header('Location: ' . $pagina . '?' . http_build_query($query) . '#contato');
        exit();
    };

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . $pagina);
        exit();
    }

    // Honeypot: humanos não veem esse campo. Robô preencheu → finge sucesso e descarta.
    if (trim($_POST['website'] ?? '') !== '') {
        $voltar(['enviado' => 1]);
    }

    $limpar = fn($campo, $max) => mb_substr(trim(strip_tags($_POST[$campo] ?? '')), 0, $max);
    $nome     = $limpar('nome', 100);
    $email    = $limpar('email', 150);
    $assunto  = $limpar('assunto', 100);
    $mensagem = $limpar('mensagem', 2000);

    $erros = [];
    if ($nome === '')     $erros[] = 'O campo nome é obrigatório.';
    if ($email === '')    $erros[] = 'O campo e-mail é obrigatório.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if ($assunto === '')  $erros[] = 'O campo assunto é obrigatório.';
    elseif (!empty($opcoes['assuntos']) && !in_array($assunto, $opcoes['assuntos'], true)) {
        $assunto = 'Outro';   // só aceita os assuntos do formulário
    }
    if ($mensagem === '') $erros[] = 'O campo mensagem é obrigatório.';
    if (!empty($opcoes['consentimento']) && empty($_POST['consentimento'])) {
        $erros[] = 'É preciso concordar com o uso dos dados (LGPD).';
    }

    if ($erros) {
        $voltar(['erros' => implode('|', $erros)]);
    }

    // Limite simples: 1 envio por minuto por IP.
    $ip    = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    $marca_arquivo = sys_get_temp_dir() . '/contato_' . md5($ip);
    if (is_file($marca_arquivo) && time() - filemtime($marca_arquivo) < 60) {
        $voltar(['erros' => 'Aguarde um minuto antes de enviar outra mensagem.']);
    }

    $configArquivo = __DIR__ . '/config.php';
    if (!is_file($configArquivo)) {
        error_log('Contato: config.php não encontrado (copie config.example.php).');
        $voltar(['enviado' => 0]);
    }
    $config = require $configArquivo;

    $quando = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('d/m/Y \à\s H:i');
    $dados  = compact('nome', 'email', 'assunto', 'mensagem', 'quando');
    $logo   = __DIR__ . '/email/' . temaEmail($tema)['logo'];

    // 1) Mensagem para a equipe (se falhar, avisa o visitante)
    $mail = novoMailer($config);
    try {
        $mail->setFrom($config['smtp_user'], "Site $marca");
        $mail->addAddress($config['destino'], $marca);
        $mail->addReplyTo($email, $nome);
        $mail->addEmbeddedImage($logo, 'logo', 'logo.png');
        $mail->isHTML(true);
        $mail->Subject = "[$marca] $assunto — $nome";
        $mail->Body    = emailEquipe($tema, $dados, 'cid:logo');
        $mail->AltBody =
            "Nova mensagem pelo site $marca ($quando)\n\n" .
            "Nome: $nome\nE-mail: $email\nAssunto: $assunto\n\nMensagem:\n$mensagem";
        $mail->send();
        touch($marca_arquivo);
    } catch (Exception $e) {
        // Detalhe técnico vai só para o log do servidor, nunca para a URL.
        error_log('Contato: falha no envio - ' . $mail->ErrorInfo);
        $voltar(['enviado' => 0]);
    }

    // 2) Confirmação para o visitante (se falhar, a mensagem já chegou: só registra)
    $conf = novoMailer($config);
    try {
        $conf->setFrom($config['smtp_user'], $marca);
        $conf->addAddress($email, $nome);
        $conf->addReplyTo($config['destino'], $marca);
        $conf->addEmbeddedImage($logo, 'logo', 'logo.png');
        $conf->isHTML(true);
        $conf->Subject = "Recebemos sua mensagem — $marca";
        $conf->Body    = emailConfirmacao($tema, $dados, 'cid:logo', $config['site_url'] ?? '');
        $primeiro      = primeiroNome($nome);
        $conf->AltBody =
            ($primeiro !== '' ? "Olá, $primeiro!" : 'Olá!') . " Recebemos sua mensagem.\n\n" .
            "Obrigado por falar " . temaEmail($tema)['com'] . ". Nossa equipe vai responder por este mesmo e-mail.\n" .
            "Assunto: $assunto\n\nUm abraço,\n" . temaEmail($tema)['assinatura'];
        $conf->send();
    } catch (Exception $e) {
        error_log('Contato: confirmação não enviada - ' . $conf->ErrorInfo);
    }

    $voltar(['enviado' => 1]);
}
