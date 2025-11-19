<?php

class AbmUsuarioRol {

    public function asignarRol($idusuario, $idrol) {
        $obj = new UsuarioRol();
        $obj->getObjUsuario()->setIdUsuario($idusuario);
        $obj->getObjRol()->setIdRol($idrol);

        return $obj->insertar();
    }

    public function quitarRol($idusuario, $idrol) {
        $obj = new UsuarioRol();
        $obj->getObjUsuario()->setIdUsuario($idusuario);
        $obj->getObjRol()->setIdRol($idrol);

        return $obj->eliminar();
    }

    public function rolesDeUsuario($idusuario) {
        $obj = new UsuarioRol();
        $obj->getObjUsuario()->setIdUsuario($idusuario);
        return $obj->obtenerRoles();
    }
}
