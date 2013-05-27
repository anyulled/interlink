<?php

/**
 * Description of funcionalidad
 *
 * @author Anyul Rivas
 */
class funcionalidad extends db {

    const tabla = "funcionalidad";
    const tabla_grupo_funcionalidad = "rol_funcionalidad";

    public function actualizar($id, $data) {
        return $this->update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id) {
        return $this->delete(self::tabla, array("id" => $id));
    }

    public function insertar($data) {
        return $this->insert(self::tabla, $data);
    }

    public function listar() {
        return $this->select("*", self::tabla);
    }

    public function ver($id) {
        return $this->select("*", self::tabla, array("id" => $id));
    }

    public function funcionalidad_grupo($grupo) {
        return $this->dame_query("select funcionalidad.* from ".self::tabla_grupo_funcionalidad." 
            inner join ".self::tabla." on funcionalidad.id = funcionalidad_id
            where rol_id= $grupo order by orden");
    }

    public function guardar_funcionalidad($data) {
        $exito['delete'] = $this->delete(self::tabla_grupo_funcionalidad, array("rol_id" => $data['grupo_id']));
        $exito['funcionalidad'] = array();
        if ($exito) {
            foreach ($data['funcionalidad_id']as $funcionlidad) {
                $exito_f = $this->insert(self::tabla_grupo_funcionalidad, array(
                    "funcionalidad_id" => $funcionlidad,
                    "rol_id" => $data['grupo_id']
                        ));
                array_push($exito, $exito_f);
                if (!$exito) {
                    break;
                    $exito['suceed'] = FALSE;
                }
            }
            $exito['suceed'] = TRUE;
        }
        return $exito;
    }
}
?>
