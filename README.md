# RazorByte

Sites institucionais da **RazorByte**, estúdio de software, e do **Laço**, app que conecta famílias a cuidadores de idosos.

| Página | Arquivo | Estilo |
|---|---|---|
| Site da RazorByte | `index.html` | `style.css` |
| Site do Laço | `index2.html` | `style2.css` |
| Política de privacidade do Laço | `privacidade.html` | `style2.css` |

## Como rodar localmente

Requer PHP 8+ (o do XAMPP serve).

```bash
php -S 127.0.0.1:8765
```

Depois abra <http://127.0.0.1:8765/index.html>. Com o XAMPP, basta deixar a pasta em `htdocs/` e acessar `http://localhost/razorbyte-main/`.

## Formulários de contato

Os dois sites enviam o formulário por e-mail via Gmail (SMTP), usando o PHPMailer da pasta `PHPMailer/`.

| Arquivo | Papel |
|---|---|
| `envioMail.php` | Recebe o formulário do site RazorByte |
| `enviarSac.php` | Recebe o formulário do site Laço (exige consentimento LGPD) |
| `mailer.php` | Lógica comum: validação, anti-spam (honeypot), limite de 1 envio/min por IP |

### Configuração (obrigatória)

As credenciais **não** ficam no repositório.

1. Copie `config.example.php` para `config.php`.
2. Gere uma senha de app em <https://myaccount.google.com/apppasswords> e coloque em `smtp_pass`.

`config.php` está no `.gitignore` e nunca deve ser versionado.

## Estrutura

```
imagi/      imagens do site RazorByte (WebP otimizado)
imgLaco/    imagens do site Laço (WebP otimizado)
PHPMailer/  biblioteca de envio de e-mail
```

### Imagens

Todas as imagens são WebP redimensionadas para cerca de 2× o tamanho em que aparecem. Ao adicionar uma imagem nova:

- exporte em WebP (qualidade 75–85) na largura em que ela será exibida, vezes 2;
- informe `width` e `height` na tag `<img>`;
- use `loading="lazy"` em tudo que não aparece na primeira tela.

As imagens de compartilhamento em redes sociais (`og-*.jpg`, 1200×630) ficam em JPEG por compatibilidade.
