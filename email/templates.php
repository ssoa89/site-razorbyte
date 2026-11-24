<?php
/**
 * Modelos HTML dos e-mails do formulário de contato.
 *
 * Layout em tabelas + estilos inline: é o que Gmail, Outlook e apps de celular
 * exibem de forma consistente. Todo texto vindo do visitante passa por esc().
 */

/** Identidade visual de cada site. */
function temaEmail(string $tema): array
{
    $temas = [
        'razorbyte' => [
            'marca'      => 'RazorByte',
            'com'        => 'com a RazorByte',
            'do'         => 'da RazorByte',
            'logo'       => 'logo-razorbyte.png',
            'logo_w'     => 145,
            'logo_h'     => 64,
            'topo_bg'    => '#070B14',   // faixa do logo (logo metálico pede fundo escuro)
            'acento'     => '#2F6FED',
            'acento_bg'  => '#EEF3FF',
            'fundo'      => '#F3F6FC',
            'texto'      => '#17150F',
            'suave'      => '#5F5B50',
            'borda'      => '#E4E7EF',
            'titulo'     => "Arial, Helvetica, sans-serif",
            'assinatura' => 'Equipe RazorByte',
            'privacidade'=> false,
        ],
        'laco' => [
            'marca'      => 'Laço',
            'com'        => 'com o Laço',
            'do'         => 'do Laço',
            'logo'       => 'logo-laco.png',
            'logo_w'     => 65,
            'logo_h'     => 56,
            'topo_bg'    => '#FFFFFF',
            'acento'     => '#8E3F83',
            'acento_bg'  => '#F7E9F1',
            'fundo'      => '#FBF7F2',
            'texto'      => '#2B1730',
            'suave'      => '#625066',
            'borda'      => '#EADFE6',
            'titulo'     => "Georgia, 'Times New Roman', serif",
            'assinatura' => 'Equipe Laço',
            'privacidade'=> true,
        ],
    ];
    return $temas[$tema] ?? $temas['razorbyte'];
}

function esc(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Primeiro nome "seguro" para a saudação: sem links, curto. Vazio se suspeito. */
function primeiroNome(string $nome): string
{
    $p = trim(preg_split('/\s+/u', trim($nome))[0] ?? '');
    if ($p === '' || preg_match('~(https?:|www\.|\.[a-z]{2,}/|[<>@])~i', $p)) return '';
    return mb_substr($p, 0, 30);
}

/** Moldura comum: fundo, cartão branco, faixa do logo e rodapé. */
function molduraEmail(array $t, string $logoSrc, string $preheader, string $conteudo, string $rodape): string
{
    $topoBorda = $t['topo_bg'] === '#FFFFFF' ? "border-bottom:3px solid {$t['acento']};" : '';
    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<title>{$t['marca']}</title>
</head>
<body style="margin:0;padding:0;background:{$t['fundo']};">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{$preheader}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{$t['fundo']};">
  <tr><td align="center" style="padding:32px 16px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;border:1px solid {$t['borda']};border-radius:16px;overflow:hidden;">
      <tr><td align="left" style="background:{$t['topo_bg']};padding:22px 32px;{$topoBorda}">
        <img src="{$logoSrc}" width="{$t['logo_w']}" height="{$t['logo_h']}" alt="{$t['marca']}" style="display:block;border:0;">
      </td></tr>
      <tr><td style="padding:32px;font-family:Arial,Helvetica,sans-serif;color:{$t['texto']};font-size:16px;line-height:1.6;">
        {$conteudo}
      </td></tr>
    </table>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">
      <tr><td style="padding:18px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12.5px;line-height:1.6;color:{$t['suave']};text-align:center;">
        {$rodape}
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
}

/** E-mail que chega para a equipe com a mensagem do visitante. */
function emailEquipe(string $tema, array $d, string $logoSrc): string
{
    $t = temaEmail($tema);
    $nome = esc($d['nome']);
    $email = esc($d['email']);
    $assunto = esc($d['assunto']);
    $mensagem = nl2br(esc($d['mensagem']), false);
    $quando = esc($d['quando']);
    $primeiro = esc(primeiroNome($d['nome']) ?: $d['nome']);
    $mailto = esc('mailto:' . $d['email'] . '?subject=' . rawurlencode('Re: ' . $d['assunto']));

    $linha = fn($rotulo, $valor) => <<<HTML
<tr>
  <td style="padding:10px 0;border-bottom:1px solid {$t['borda']};font-size:13px;color:{$t['suave']};width:110px;vertical-align:top;">{$rotulo}</td>
  <td style="padding:10px 0;border-bottom:1px solid {$t['borda']};font-size:15px;color:{$t['texto']};font-weight:bold;">{$valor}</td>
</tr>
HTML;

    $conteudo =
        "<span style=\"display:inline-block;background:{$t['acento_bg']};color:{$t['acento']};font-size:12px;font-weight:bold;letter-spacing:.06em;text-transform:uppercase;padding:5px 12px;border-radius:99px;\">Nova mensagem pelo site</span>" .
        "<h1 style=\"margin:16px 0 4px;font-family:{$t['titulo']};font-size:26px;line-height:1.25;color:{$t['texto']};\">{$assunto}</h1>" .
        "<p style=\"margin:0 0 20px;color:{$t['suave']};font-size:14px;\">Recebida em {$quando}</p>" .
        '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">' .
            $linha('Nome', $nome) .
            $linha('E-mail', "<a href=\"mailto:{$email}\" style=\"color:{$t['acento']};text-decoration:none;\">{$email}</a>") .
            $linha('Assunto', $assunto) .
        '</table>' .
        "<p style=\"margin:0 0 8px;font-size:13px;color:{$t['suave']};text-transform:uppercase;letter-spacing:.06em;font-weight:bold;\">Mensagem</p>" .
        "<div style=\"background:{$t['fundo']};border-left:4px solid {$t['acento']};border-radius:8px;padding:16px 18px;font-size:15.5px;line-height:1.65;color:{$t['texto']};\">{$mensagem}</div>" .
        '<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:28px;"><tr>' .
        "<td style=\"background:{$t['acento']};border-radius:99px;\"><a href=\"{$mailto}\" style=\"display:inline-block;padding:14px 28px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;color:#FFFFFF;text-decoration:none;\">Responder a {$primeiro}</a></td>" .
        '</tr></table>';

    $rodape = "Enviado pelo formulário de contato do site {$t['do']}.<br>Responder este e-mail fala direto com {$nome}.";
    return molduraEmail($t, $logoSrc, "{$nome} escreveu sobre: {$assunto}", $conteudo, $rodape);
}

/** Confirmação automática para quem enviou. Não repete a mensagem digitada (evita uso para spam). */
function emailConfirmacao(string $tema, array $d, string $logoSrc, string $siteUrl = ''): string
{
    $t = temaEmail($tema);
    $primeiro = esc(primeiroNome($d['nome']));
    $saudacao = $primeiro !== '' ? "Olá, {$primeiro}!" : 'Olá!';
    $assunto = esc($d['assunto']);
    $marca = $t['marca'];

    $conteudo =
        "<h1 style=\"margin:0 0 12px;font-family:{$t['titulo']};font-size:28px;line-height:1.25;color:{$t['texto']};\">{$saudacao}<br>Recebemos sua mensagem.</h1>" .
        "<p style=\"margin:0 0 20px;font-size:16px;color:{$t['texto']};\">Obrigado por falar {$t['com']}. Nossa equipe vai ler com atenção e responder por este mesmo e-mail.</p>" .
        "<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:{$t['acento_bg']};border-radius:12px;margin-bottom:22px;\"><tr><td style=\"padding:16px 20px;\">" .
            "<p style=\"margin:0;font-size:12px;color:{$t['acento']};text-transform:uppercase;letter-spacing:.06em;font-weight:bold;\">Assunto</p>" .
            "<p style=\"margin:4px 0 0;font-size:17px;font-weight:bold;color:{$t['texto']};\">{$assunto}</p>" .
        '</td></tr></table>' .
        "<p style=\"margin:0 0 6px;font-size:15px;color:{$t['suave']};\">Se quiser complementar alguma informação, é só responder este e-mail.</p>";

    if ($t['privacidade']) {
        $conteudo .= "<p style=\"margin:14px 0 0;font-size:14px;color:{$t['suave']};\">Por segurança, não envie dados de saúde por e-mail.</p>";
    }
    $conteudo .= "<p style=\"margin:24px 0 0;font-size:16px;color:{$t['texto']};\">Um abraço,<br><strong>{$t['assinatura']}</strong></p>";

    $links = '';
    if ($siteUrl !== '') {
        $u = esc(rtrim($siteUrl, '/'));
        $links = "<br><a href=\"{$u}\" style=\"color:{$t['suave']};\">Visitar o site</a>";
        if ($t['privacidade']) {
            $links .= " · <a href=\"{$u}/privacidade.html\" style=\"color:{$t['suave']};\">Política de privacidade</a>";
        }
    }
    $rodape = "Você recebeu este e-mail porque enviou uma mensagem pelo site {$t['do']}.<br>Se não foi você, pode ignorá-lo.{$links}";
    return molduraEmail($t, $logoSrc, "Recebemos sua mensagem sobre: {$assunto}", $conteudo, $rodape);
}
