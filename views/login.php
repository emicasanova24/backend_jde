<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
  </style>
</head>
<body class="font-body bg-surface text-on-surface antialiased">
  <div class="relative flex min-h-screen w-full flex-col items-center justify-center overflow-x-hidden p-4">
    <div class="absolute inset-0 z-0 overflow-hidden">
      <div class="absolute -top-[10%] -left-[10%] h-[40%] w-[40%] rounded-full bg-primary/10 blur-[120px]"></div>
      <div class="absolute -bottom-[10%] -right-[10%] h-[40%] w-[40%] rounded-full bg-primary/5 blur-[120px]"></div>
    </div>
    <div class="z-10 w-full max-w-md">
      <div class="flex flex-col items-center mb-6 sm:mb-8">
        <div class="mb-4 sm:mb-6 h-auto w-[140px] sm:w-[180px] md:w-[200px] overflow-hidden rounded-xl">
          <img alt="Juego de Equipo official logo" class="h-full w-full object-contain" src="/assets/logo.png"/>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-on-surface text-center font-headline">Bienvenido de nuevo</h1>
        <p class="mt-1 sm:mt-2 text-sm text-on-surface-variant">Portal de administración y gestión</p>
      </div>
      <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4 sm:p-6 md:p-8 shadow-xl backdrop-blur-sm">
        <form id="loginForm" class="space-y-6" method="POST">
          <div class="flex flex-col gap-2">
            <label for="email" class="text-sm font-medium leading-none text-on-surface-variant font-headline">Correo del Administrador</label>
            <input id="email" name="email" type="email" placeholder="admin@juegodeequipo.com" required class="flex h-10 sm:h-12 w-full rounded-xl border border-outline-variant/20 bg-surface-container py-2 px-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"/>
          </div>
          <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
              <label for="password" class="text-sm font-medium leading-none text-on-surface-variant font-headline">Contraseña</label>
              <a href="#" class="text-xs font-medium text-primary hover:underline">¿Olvidaste tu contraseña?</a>
            </div>
            <input id="password" name="password" type="password" placeholder="********" required class="flex h-10 sm:h-12 w-full rounded-xl border border-outline-variant/20 bg-surface-container py-2 px-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"/>
          </div>
          <div class="flex items-center space-x-2">
            <input id="remember" type="checkbox" class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary"/>
            <label for="remember" class="text-sm font-medium text-on-surface-variant">Mantener sesión iniciada por 30 días</label>
          </div>
          <p id="loginMessage" class="hidden rounded-xl border px-4 py-3 text-sm"></p>
          <button type="submit" class="flex h-10 sm:h-12 w-full items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-bold text-on-primary shadow-lg shadow-primary/20 hover:bg-primary-dim focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 active:scale-[0.98] transition-all">Iniciar sesión</button>
        </form>
      </div>
      <div class="mt-6 sm:mt-8 flex flex-wrap justify-center gap-4 sm:gap-6 text-xs font-medium text-on-surface-variant/50">
        <a href="#" class="hover:text-primary transition-colors">Política de Seguridad</a>
        <a href="#" class="hover:text-primary transition-colors">Centro de Ayuda</a>
        <a href="#" class="hover:text-primary transition-colors">Estado de la Plataforma</a>
      </div>
    </div>
  </div>
  <script>
    const loginForm = document.getElementById("loginForm");
    const loginMessage = document.getElementById("loginMessage");
    const submitButton = loginForm?.querySelector('button[type="submit"]');

    const showMessage = (message, type = "error") => {
      if (!loginMessage) return;
      loginMessage.textContent = message;
      loginMessage.className = "rounded-xl border px-4 py-3 text-sm";
      loginMessage.classList.remove("hidden");
      if (type === "success") {
        loginMessage.classList.add("border-emerald-500/30", "bg-emerald-500/10", "text-emerald-300");
        return;
      }
      loginMessage.classList.add("border-error/30", "bg-error/10", "text-error");
    };

    const hideMessage = () => {
      if (!loginMessage) return;
      loginMessage.textContent = "";
      loginMessage.className = "hidden rounded-xl border px-4 py-3 text-sm";
    };

    const setSubmitting = (isSubmitting) => {
      if (!submitButton) return;
      submitButton.disabled = isSubmitting;
      submitButton.textContent = isSubmitting ? "Ingresando..." : "Iniciar sesión";
      submitButton.classList.toggle("opacity-70", isSubmitting);
      submitButton.classList.toggle("cursor-not-allowed", isSubmitting);
    };

    const validateExistingSession = async () => {
      const token = localStorage.getItem("token");
      if (!token) return;
      try {
        const response = await fetch("/api/test-protegido", {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (response.ok) {
          window.location.href = "/dashboard";
          return;
        }
      } catch (error) {
        console.warn("No se pudo validar la sesión existente", error);
      }
      localStorage.removeItem("token");
    };

    validateExistingSession();

    if (loginForm) {
      loginForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;
        hideMessage();

        if (!email || !password) {
          showMessage("Email y contraseña son requeridos");
          return;
        }

        try {
          setSubmitting(true);
          localStorage.removeItem("token");

          const response = await fetch("/api/auth/login", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, password }),
          });

          let result;
          try { result = await response.json(); } catch (e) { result = null; }

          if (response.ok && result.success && result.token) {
            localStorage.setItem("token", result.token);
            if (result.account) localStorage.setItem("accountProfile", JSON.stringify(result.account));
            showMessage("Login correcto. Redirigiendo...", "success");
            window.location.href = "/dashboard";
          } else {
            const fallbackMessage = response.status >= 500
              ? "El servidor respondió con un error. Intentá nuevamente en unos minutos."
              : "Credenciales inválidas";
            showMessage(result?.message || fallbackMessage);
          }
        } catch (error) {
          console.error("Login error", error);
          showMessage("Error de conexión. Verificá tu conexión e intentá nuevamente.");
        } finally {
          setSubmitting(false);
        }
      });
    }
  </script>
</body>
</html>
