<?php
class AbmUsuarioRol
{
    /**
 * Verifica si el usuario tiene rol de administrador
 * Compatible con ambos casos: objeto Rol o string
 */
public function esAdmin($idusuario)
{
    // Opción 1: Usar obtenerRoles() → devuelve array de strings
    $roles = $this->rolesDeUsuario($idusuario); // ← este método ya tenés y devuelve ['admin', 'cliente', ...]

    foreach ($roles as $rol) {
        if (is_string($rol) && strtolower(trim($rol)) === 'admin') {
            return true;
        }
        // Por si acaso también devuelve objetos (doble seguridad)
        if (is_object($rol) && method_exists($rol, 'getRolDescripcion')) {
            if (strtolower($rol->getRolDescripcion()) === 'admin') {
                return true;
            }
        }
    }

    // Opción extra: verificar por ID del rol (más seguro aún)
    $rolesConID = $this->rolesDeUsuarioConID($idusuario);
    foreach ($rolesConID as $rol) {
        if (isset($rol['idrol']) && $rol['idrol'] == 1) { // 1 = admin (ajustá si es otro)
            return true;
        }
    }

    return false;
}

    /**
     * Asigna un rol a un usuario
     */
    public function asignarRol($idusuario, $idrol)
    {
        $obj = new UsuarioRol();
        $obj->setObjUsuario((new Usuario())->buscar($idusuario)[0] ?? null);
        $obj->setObjRol((new Rol())->buscar(['idrol' => $idrol])[0] ?? null);

        return $obj->insertar();
    }

    /**
     * Quita un rol a un usuario
     */
    public function quitarRol($idusuario, $idrol)
    {
        $param = ['idusuario' => $idusuario, 'idrol' => $idrol];
        $lista = (new UsuarioRol())->buscar($param);
        if (!empty($lista)) {
            return $lista[0]->eliminar();
        }
        return false;
    }

    /**
     * Devuelve array de objetos Rol que tiene el usuario
     */
    public function rolesDeUsuario($idusuario)
    {
        $obj = new UsuarioRol();
        $obj->setObjUsuario((new Usuario())->buscar(['idusuario' => $idusuario])[0] ?? null);
        return $obj->obtenerRoles();
    }

    /**
     * Devuelve array con los IDs de los roles del usuario
     */
    public function rolesDeUsuarioConID($idusuario)
    {
        $obj = new UsuarioRol();
        $obj->setObjUsuario((new Usuario())->buscar(['idusuario' => $idusuario])[0] ?? null);
        return $obj->obtenerRolesConID();
    }

    /**
     * Devuelve un array con las descripciones de los roles (ej: ["admin", "cliente"])
     */
    public function rolesDescripcion($idusuario)
    {
        $roles = $this->rolesDeUsuario($idusuario);
        $desc = [];
        foreach ($roles as $rol) {
            $desc[] = strtolower($rol->getRolDescripcion());
        }
        return $desc;
    }
}