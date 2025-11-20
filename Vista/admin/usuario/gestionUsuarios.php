<?php
// gestionUsuarios.php
require_once __DIR__ . '/../../../configuracion.php';

$session = new Session();
if (!$session->activa()) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
    exit;
}

if (!tieneRol('admin')) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/paginaSegura.php");
    exit;
}

$abmUsuario = new AbmUsuario();
$listaUsuarios = $abmUsuario->buscar(null);

include_once __DIR__ . '/../../estructura/cabecera.php';
?>

<main class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gestión de Usuarios</h2>
    </div>

    <!-- alerts container -->
    <div id="alerts"></div>

    <div class="table-responsive">
        <table class="table table-striped table-hover" id="tablaUsuarios">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th style="width:220px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaUsuarios as $u): ?>
                    <tr data-id="<?= htmlspecialchars($u->getIdUsuario()); ?>">
                        <td><?= htmlspecialchars($u->getIdUsuario()); ?></td>
                        <td><?= htmlspecialchars($u->getUsNombre()); ?></td>
                        <td><?= htmlspecialchars($u->getUsMail()); ?></td>
                        <td>
                            <?php if ($u->getUsDeshabilitado()): ?>
                                <span class="badge bg-danger">Deshabilitado</span>
                            <?php else: ?>
                                <span class="badge bg-success">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-editar">Editar</button>

                            <?php if ($u->getUsDeshabilitado()): ?>
                                <button class="btn btn-sm btn-success btn-habilitar">Habilitar</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-danger btn-deshabilitar">Deshabilitar</button>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Bootstrap para editar (incluye cambio de contraseña) -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formEditarUsuario" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_idusuario" name="idusuario">

        <div class="mb-3">
          <label for="edit_usnombre" class="form-label">Nombre</label>
          <input type="text" id="edit_usnombre" name="usnombre" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="edit_usmail" class="form-label">Email</label>
          <input type="email" id="edit_usmail" name="usmail" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="edit_uspass" class="form-label">Nueva contraseña <small class="text-muted">(dejar vacío para mantener)</small></label>
          <input type="password" id="edit_uspass" name="uspass" class="form-control" placeholder="Nueva contraseña (opcional)">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  const base = 'accion/accionUsuarios.php';
  const alerts = (html, timeout = 3500) => {
    document.getElementById('alerts').innerHTML = html;
    if (timeout) setTimeout(()=> document.getElementById('alerts').innerHTML = '', timeout);
  };

  // Helper para parsear JSON y mostrar error crudo si no es JSON
  async function fetchJson(url, opts) {
    const res = await fetch(url, opts);
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      throw new Error("Respuesta inválida del servidor: " + text.substring(0, 300));
    }
  }

  // EDITAR: abrir modal y cargar datos
  document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const tr = e.target.closest('tr');
      const id = tr.dataset.id;
      try {
        const json = await fetchJson(`${base}?accion=obtener&id=${encodeURIComponent(id)}`);
        if (!json.ok) {
          alerts(`<div class="alert alert-danger">Error: ${json.error || 'No encontrado'}</div>`);
          return;
        }
        const u = json.usuario;
        document.getElementById('edit_idusuario').value = u.idusuario;
        document.getElementById('edit_usnombre').value = u.usnombre;
        document.getElementById('edit_usmail').value = u.usmail;
        document.getElementById('edit_uspass').value = '';
        // mostrar modal (Bootstrap)
        const modalEl = document.getElementById('modalEditarUsuario');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      } catch (err) {
        alerts(`<div class="alert alert-danger">Error al obtener usuario: ${err.message}</div>`, 6000);
      }
    });
  });

  // SUBMIT editar (incluye contraseña opcional)
  document.getElementById('formEditarUsuario').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', 'editar');

    try {
      const json = await fetchJson(base, { method: 'POST', body: fd });
      if (json.ok) {
        alerts(`<div class="alert alert-success">Usuario actualizado correctamente.</div>`);
        setTimeout(()=> location.reload(), 700);
      } else {
        alerts(`<div class="alert alert-danger">Error: ${json.error || 'No se pudo actualizar'}</div>`, 6000);
      }
    } catch (err) {
      alerts(`<div class="alert alert-danger">Error de red: ${err.message}</div>`, 6000);
    }
  });

  // DESHABILITAR (pone fecha y hora)
  document.querySelectorAll('.btn-deshabilitar').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const id = e.target.closest('tr').dataset.id;
      if (!confirm('¿Deshabilitar este usuario?')) return;
      try {
        const json = await fetchJson(base, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `accion=deshabilitar&id=${encodeURIComponent(id)}`
        });
        if (json.ok) {
          alerts(`<div class="alert alert-success">Usuario deshabilitado.</div>`);
          setTimeout(()=> location.reload(), 700);
        } else {
          alerts(`<div class="alert alert-danger">Error: ${json.error || 'No se pudo deshabilitar'}</div>`, 6000);
        }
      } catch (err) {
        alerts(`<div class="alert alert-danger">${err.message}</div>`, 6000);
      }
    });
  });

  // HABILITAR (setea NULL)
  document.querySelectorAll('.btn-habilitar').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const id = e.target.closest('tr').dataset.id;
      if (!confirm('¿Habilitar este usuario?')) return;
      try {
        const json = await fetchJson(base, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `accion=habilitar&id=${encodeURIComponent(id)}`
        });
        if (json.ok) {
          alerts(`<div class="alert alert-success">Usuario habilitado.</div>`);
          setTimeout(()=> location.reload(), 700);
        } else {
          alerts(`<div class="alert alert-danger">Error: ${json.error || 'No se pudo habilitar'}</div>`, 6000);
        }
      } catch (err) {
        alerts(`<div class="alert alert-danger">${err.message}</div>`, 6000);
      }
    });
  });

})();
</script>

<?php include_once __DIR__ . '/../../estructura/pie.php'; ?>
