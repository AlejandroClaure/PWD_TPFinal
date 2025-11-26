<?php include_once '../estructura/cabecera.php'; ?>

<body>

  <div class="wrapper d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="auth-content">
      <div class="card shadow-lg border-0" style="width: 400px;">
        <div class="card-body text-center p-4">
          <?php
            $abrirRegistro = isset($_GET['email_duplicado']) ? true : false;
          ?>
          <!-- Botón de cambio tipo toggle -->
          <div class="btn-group mb-4 w-100" role="group">
            <button id="btn-login" class="btn btn-outline-primary <?php echo !$abrirRegistro ? 'active' : ''; ?>">Login</button>
            <button id="btn-register" class="btn btn-outline-primary <?php echo $abrirRegistro ? 'active' : ''; ?>">Registrar</button>
          </div>

          <h5 class="mb-4 text-muted" id="form-title">Iniciar sesión</h5>

          <!-- MENSAJE DE REGISTRO EXITOSO AQUI -->
          <?php if (isset($_GET["ok"])) { ?>
            <div class="alert alert-success py-1">
              ✔ Registro exitoso. Ya podés iniciar sesión.
            </div>
          <?php } ?>

          <!-- FORMULARIO LOGIN -->
          <form id="login-form" class="text-start" action="accion/verificarLogin.php" method="post">
            <div class="mb-3">
              <label class="form-label">Nombre de usuario</label>
              <input type="text" class="form-control" name="usnombre" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" class="form-control" name="uspass" required>
            </div>

            <?php if (isset($_GET["error"])) { ?>
              <div class="alert alert-danger py-1">
                Nombre de usuario o contraseña incorrectos
              </div>
            <?php } ?>

            <button type="submit" class="btn btn-success w-100">Ingresar</button>
          </form>

          <!-- FORMULARIO REGISTRO -->
          <form id="register-form" class="text-start d-none" action="accion/registrarUsuario.php" method="post">
            <div class="mb-3">
              <label class="form-label">Nombre de usuario</label>
              <input type="text" class="form-control" name="usnombre" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Correo electrónico</label>
              <input type="email" class="form-control" name="usmail" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" class="form-control" name="uspass" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Crear cuenta</button>
            <?php if (isset($_GET["email_duplicado"])) { ?>
            <div class="alert alert-warning py-1">
                ⚠ El correo ya está registrado. Probá iniciar sesión.
            </div>
          <?php } ?>
          </form>

        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const btnLogin = document.getElementById("btn-login");
    const btnRegister = document.getElementById("btn-register");
    const loginForm = document.getElementById("login-form");
    const registerForm = document.getElementById("register-form");
    const title = document.getElementById("form-title");

    btnLogin.addEventListener("click", () => {
      btnLogin.classList.add("active");
      btnRegister.classList.remove("active");
      loginForm.classList.remove("d-none");
      registerForm.classList.add("d-none");
      title.textContent = "Iniciar sesión";
    });

    btnRegister.addEventListener("click", () => {
      btnRegister.classList.add("active");
      btnLogin.classList.remove("active");
      registerForm.classList.remove("d-none");
      loginForm.classList.add("d-none");
      title.textContent = "Registrar nuevo usuario";
    });
  </script>
  <script>
  // Si viene email_duplicado, abrir automáticamente el registro
    <?php if ($abrirRegistro) { ?>
      btnRegister.classList.add("active");
      btnLogin.classList.remove("active");
      registerForm.classList.remove("d-none");
      loginForm.classList.add("d-none");
      title.textContent = "Registrar nuevo usuario";
    <?php } ?>
  </script>
  <style>
    .btn-group .btn.active {
      background-color: #0d6efd;
      color: white;
      border-color: #0d6efd;
    }
  </style>

</body>
<?php include_once '../estructura/pie.php'; ?>