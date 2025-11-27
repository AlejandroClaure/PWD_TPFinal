<?php

class AbmUsuario
{

    /** CARGAR OBJETO SIN CLAVE (ALTA) */
    private function cargarObjetoSinClave($param)
    {
        $obj = null;

        if (
            isset($param['usnombre']) &&
            isset($param['uspass']) &&
            isset($param['usmail'])
        ) {

            $hash = password_hash($param['uspass'], PASSWORD_DEFAULT);

            $obj = new Usuario();
            $obj->setear(
                null,
                $param['usnombre'],
                $hash,
                $param['usmail'],
                null
            );
        }
        return $obj;
    }

    /** CARGAR OBJETO CON CLAVE (MODIFICAR) */
    private function cargarObjeto($param)
    {
        $obj = null;

        if (
            isset($param['idusuario']) &&
            isset($param['usnombre']) &&
            isset($param['usmail'])
        ) {

            $obj = new Usuario();

            // Si hay contraseña nueva → hash
            $pass = null;
            if (!empty($param['uspass'])) {
                $pass = password_hash($param['uspass'], PASSWORD_DEFAULT);
            } else {
                // Si la contraseña viene vacía, mantenemos la actual
                $obj->setIdUsuario($param['idusuario']);
                $obj->cargar();
                $pass = $obj->getUsPass();
            }

            $obj->setear(
                $param['idusuario'],
                $param['usnombre'],
                $pass,
                $param['usmail'],
                null
            );
        }

        return $obj;
    }

    private function seteadosCamposClaves($param)
    {
        return isset($param['idusuario']);
    }

    /** ALTA DESDE PANEL ADMIN */
    public function alta($param)
    {
        $obj = $this->cargarObjetoSinClave($param);

        if ($obj != null && $obj->insertar()) {
            return $obj->getIdUsuario();
        }
        return false;
    }

    /** REGISTRAR DESDE FORMULARIO PÚBLICO */
    public function registrar($param)
    {
        return $this->alta($param);
    }

    /** BAJA */
    public function baja($param)
    {
        $resp = false;

        if ($this->seteadosCamposClaves($param)) {
            $obj = new Usuario();
            $obj->setIdUsuario($param['idusuario']);

            if ($obj->eliminar()) {
                $resp = true;
            }
        }
        return $resp;
    }

    /** MODIFICACION */
    public function modificacion($param)
    {
        $resp = false;

        if ($this->seteadosCamposClaves($param)) {
            $obj = $this->cargarObjeto($param);

            if ($obj != null && $obj->modificar()) {
                $resp = true;
            }
        }

        return $resp;
    }

    /** BUSCAR */
    public function buscar($param)
    {
        $where = " true ";

        if ($param != null) {
            if (isset($param['idusuario']))
                $where .= " AND idusuario = " . (int)$param['idusuario'];
            if (isset($param['usnombre']))
                $where .= " AND usnombre = '" . $param['usnombre'] . "'";
            if (isset($param['usmail']))
                $where .= " AND usmail = '" . $param['usmail'] . "'";
        }

        $obj = new Usuario();
        return $obj->listar($where);
    }


    /*
 * cambia la visibilidad del propducto
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function crearUsuario()
    {
        $abm = new AbmUsuario();
        $param = [
            "usnombre" => $_POST["usnombre"],
            "usmail"   => $_POST["usmail"],
            "uspass"   => $_POST["uspass"]
        ];

        $abm->alta($param);

        // Redirige al panel
        header("Location: ../panelUsuarios.php?ok=1");
        exit;
    }


    /*
 * cambia la visibilidad del propducto
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function EditarUsuario($abm)
    {
        $abm = new AbmUsuario();

        $param = [
            "idusuario" => $_POST["idusuario"],
            "usnombre" => $_POST["usnombre"],
            "usmail"   => $_POST["usmail"]
        ];

        // Modifica SIN tocar contraseña
        $abm->modificacion($param);

        header("Location: ../panelUsuarios.php?edit=1");
        exit;
    }
}
