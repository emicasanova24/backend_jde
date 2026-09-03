<?php
$pageTitle = 'Política de privacidad — Quién paga el asado';
$pageDescription = 'Política de privacidad del bot de WhatsApp Quién paga el asado, operado por Juego de Equipo.';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="robots" content="index, follow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      color-scheme: dark;
      --background: #120d1a;
      --surface: #1a1225;
      --surface-soft: #21172e;
      --primary: #8c52ff;
      --primary-light: #b896ff;
      --text: #f8f5ff;
      --muted: #b9afc8;
      --border: rgba(255, 255, 255, .1);
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      background:
        radial-gradient(circle at 85% -10%, rgba(140, 82, 255, .22), transparent 32rem),
        var(--background);
      color: var(--text);
      font-family: "Public Sans", system-ui, sans-serif;
      font-size: 16px;
      line-height: 1.65;
    }
    a { color: var(--primary-light); }
    a:hover { color: #d3c1ff; }
    .site-header {
      border-bottom: 1px solid var(--border);
      background: rgba(18, 13, 26, .9);
      backdrop-filter: blur(12px);
    }
    .site-header__inner,
    main,
    .site-footer__inner {
      width: min(100% - 2rem, 880px);
      margin-inline: auto;
    }
    .site-header__inner {
      min-height: 76px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }
    .logo { display: inline-flex; align-items: center; }
    .logo img { display: block; width: auto; height: 42px; }
    .back-link { font-size: .9rem; font-weight: 700; text-decoration: none; }
    main { padding-block: 4.5rem; }
    .eyebrow {
      margin: 0 0 .6rem;
      color: var(--primary-light);
      font-size: .78rem;
      font-weight: 800;
      letter-spacing: .13em;
      text-transform: uppercase;
    }
    h1 {
      max-width: 760px;
      margin: 0;
      font-size: clamp(2.25rem, 7vw, 4.25rem);
      line-height: 1.02;
      letter-spacing: -.045em;
    }
    .updated { margin: 1rem 0 2.5rem; color: var(--muted); font-size: .9rem; }
    .intro {
      margin-bottom: 2rem;
      padding: 1.5rem;
      border: 1px solid rgba(140, 82, 255, .35);
      border-radius: 18px;
      background: linear-gradient(135deg, rgba(140, 82, 255, .14), rgba(255, 255, 255, .035));
      font-size: 1.08rem;
    }
    section {
      margin-top: 1rem;
      padding: 1.7rem;
      border: 1px solid var(--border);
      border-radius: 18px;
      background: rgba(26, 18, 37, .78);
    }
    h2 { margin: 0 0 .75rem; font-size: 1.25rem; line-height: 1.25; }
    p { margin: .65rem 0 0; color: #ddd6e8; }
    ul { margin: .75rem 0 0; padding-left: 1.2rem; color: #ddd6e8; }
    li + li { margin-top: .45rem; }
    code {
      padding: .12rem .4rem;
      border: 1px solid var(--border);
      border-radius: 6px;
      background: var(--surface-soft);
      color: #fff;
      font-family: inherit;
      font-weight: 700;
    }
    .site-footer { border-top: 1px solid var(--border); color: var(--muted); }
    .site-footer__inner {
      padding-block: 1.6rem;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: .6rem 1.25rem;
      font-size: .82rem;
    }
    .site-footer p { margin: 0; color: inherit; }
    @media (max-width: 560px) {
      main { padding-block: 3rem; }
      section, .intro { padding: 1.25rem; border-radius: 15px; }
      .back-link { font-size: 0; }
      .back-link::after { content: "Volver"; font-size: .9rem; }
    }
  </style>
</head>
<body>
<?php require $root . '/views/partials/gtm-body.php'; ?>
  <header class="site-header">
    <div class="site-header__inner">
      <a class="logo" href="/" aria-label="Ir al inicio de Juego de Equipo">
        <img src="/assets/logo.png" alt="Juego de Equipo">
      </a>
      <a class="back-link" href="/">Volver a Juego de Equipo</a>
    </div>
  </header>

  <main>
    <p class="eyebrow">Quién paga el asado</p>
    <h1>Política de privacidad</h1>
    <p class="updated">Última actualización: 3 de septiembre de 2026</p>

    <div class="intro">
      <strong>Quién paga el asado</strong> es un bot de WhatsApp que permite registrar compras, dividir gastos y calcular saldos entre las personas que voluntariamente participan de una juntada. El servicio es operado por Juego de Equipo.
    </div>

    <section>
      <h2>1. Datos que tratamos</h2>
      <p>Para prestar el servicio podemos recibir y guardar:</p>
      <ul>
        <li>el identificador o número asociado a tu cuenta de WhatsApp;</li>
        <li>tu nombre de perfil de WhatsApp y el nombre que elijas usar dentro de una juntada;</li>
        <li>los códigos de las juntadas a las que te unas;</li>
        <li>las compras que cargues, incluyendo importe, descripción, persona que pagó y participantes entre quienes se divide;</li>
        <li>identificadores técnicos de los mensajes enviados al bot, utilizados temporalmente para evitar procesarlos más de una vez.</li>
      </ul>
      <p>No solicitamos datos bancarios, contraseñas ni acceso al contenido de otros chats de WhatsApp.</p>
    </section>

    <section>
      <h2>2. Para qué usamos los datos</h2>
      <p>Usamos esta información exclusivamente para crear y administrar juntadas, registrar gastos, calcular cuánto debe o debe cobrar cada participante, sugerir transferencias y responder los comandos que le envíes al bot.</p>
    </section>

    <section>
      <h2>3. Cómo obtenemos la información</h2>
      <p>Recibimos los datos cuando iniciás una conversación con el bot, enviás un comando o te unís voluntariamente a una juntada mediante su código. No importamos tu agenda de contactos ni leemos conversaciones ajenas al bot.</p>
    </section>

    <section>
      <h2>4. Proveedores y transferencias</h2>
      <p>El funcionamiento del servicio requiere la Plataforma de WhatsApp Business de Meta y servicios de alojamiento e infraestructura. Estos proveedores pueden procesar información técnica únicamente en la medida necesaria para transmitir mensajes y mantener el servicio. No vendemos datos personales ni los utilizamos para publicidad.</p>
    </section>

    <section>
      <h2>5. Conservación y seguridad</h2>
      <p>Conservamos los datos personales mientras sean necesarios para mantener las cuentas de las juntadas y prestar el servicio. Aplicamos medidas técnicas razonables, incluida la validación de las solicitudes que llegan desde Meta. Cuando pedís la eliminación, tu número y tu nombre se anonimizan; los importes pueden conservarse sin identidad para no alterar los saldos compartidos de otras personas.</p>
    </section>

    <section id="eliminacion">
      <h2>6. Acceso, corrección y eliminación</h2>
      <p>Podés consultar los datos de una juntada mediante sus resúmenes, corregir el nombre que mostrás con el comando <code>nombre Tu nombre</code> y solicitar la eliminación escribiendo <code>eliminar mis datos</code> al bot.</p>
      <p>También podés consultar las <a href="/eliminacion-de-datos">instrucciones completas para eliminar tus datos</a>.</p>
    </section>

    <section>
      <h2>7. Servicios de Meta</h2>
      <p>El uso de WhatsApp también está sujeto a las políticas de sus proveedores. Podés consultar la <a href="https://www.whatsapp.com/legal/privacy-policy" target="_blank" rel="noopener noreferrer">Política de privacidad de WhatsApp</a> y la <a href="https://www.facebook.com/privacy/policy" target="_blank" rel="noopener noreferrer">Política de privacidad de Meta</a>.</p>
    </section>

    <section>
      <h2>8. Cambios y contacto</h2>
      <p>Podemos actualizar esta política si cambia el funcionamiento del bot o sus obligaciones de privacidad. La fecha de la versión vigente siempre aparecerá al comienzo de esta página.</p>
      <p>Para consultas sobre privacidad o para ejercer tus derechos, escribinos a <a href="mailto:contacto@juegodeequipo.com">contacto@juegodeequipo.com</a>.</p>
    </section>
  </main>

  <footer class="site-footer">
    <div class="site-footer__inner">
      <p>© 2026 Juego de Equipo.</p>
      <p><a href="/eliminacion-de-datos">Eliminación de datos</a></p>
    </div>
  </footer>
</body>
</html>
