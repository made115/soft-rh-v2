<?php

class Empleado
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll(): array
    {
        $sql = "
            SELECT
                e.id_empleado,
                e.nombre_empleado,
                e.apellido_pat_empleado,
                e.apellido_mat_empleado,
                e.estado_laboral,
                p.nombre_puesto,
                d.nombre_departamento
            FROM empleados e
            INNER JOIN puestos p ON p.id_puesto = e.id_puesto
            INNER JOIN departamentos d ON d.id_departamento = p.id_departamento
            ORDER BY e.id_empleado ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function existsByField(string $field, string $value): bool
    {
        $allowedFields = [
            'curp',
            'rfc',
            'nss',
            'numero_preafiliacion_imss',
            'correo'
        ];

        if (!in_array($field, $allowedFields, true)) {
            return false;
        }

        $sql = "
            SELECT id_empleado
            FROM empleados
            WHERE $field = :value
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':value', $value);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    public function create(array $data, int $id_usuario_accion): int
    {
        $this->db->beginTransaction();

        try {
            $sql = "
                INSERT INTO empleados (
                    nombre_empleado,
                    apellido_pat_empleado,
                    apellido_mat_empleado,
                    sexo,
                    curp,
                    rfc,
                    nss,
                    numero_preafiliacion_imss,
                    id_puesto,
                    fecha_ingreso,
                    telefono,
                    correo,
                    estado_laboral
                ) VALUES (
                    :nombre_empleado,
                    :apellido_pat_empleado,
                    :apellido_mat_empleado,
                    :sexo,
                    :curp,
                    :rfc,
                    :nss,
                    :numero_preafiliacion_imss,
                    :id_puesto,
                    :fecha_ingreso,
                    :telefono,
                    :correo,
                    'activo'
                )
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':nombre_empleado', $data['nombre_empleado']);
            $stmt->bindValue(':apellido_pat_empleado', $data['apellido_pat_empleado']);
            $stmt->bindValue(':apellido_mat_empleado', $data['apellido_mat_empleado']);
            $stmt->bindValue(':sexo', $data['sexo']);
            $stmt->bindValue(':curp', $data['curp']);
            $stmt->bindValue(':rfc', $data['rfc']);
            $stmt->bindValue(':nss', $data['nss']);
            $stmt->bindValue(':numero_preafiliacion_imss', $data['numero_preafiliacion_imss']);
            $stmt->bindValue(':id_puesto', $data['id_puesto'], PDO::PARAM_INT);
            $stmt->bindValue(':fecha_ingreso', $data['fecha_ingreso']);
            $stmt->bindValue(':telefono', $data['telefono']);
            $stmt->bindValue(':correo', $data['correo']);

            $stmt->execute();

            $id_empleado = (int) $this->db->lastInsertId();

            $stmtHistorial = $this->db->prepare("
                INSERT INTO historial_laboral_empleados (
                    id_empleado,
                    fecha_alta,
                    tipo_alta,
                    estado_periodo,
                    id_usuario_alta
                ) VALUES (
                    :id_empleado,
                    :fecha_alta,
                    'registro',
                    'activo',
                    :id_usuario_alta
                )
            ");

            $stmtHistorial->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmtHistorial->bindValue(':fecha_alta', $data['fecha_ingreso']);
            $stmtHistorial->bindValue(':id_usuario_alta', $id_usuario_accion, PDO::PARAM_INT);
            $stmtHistorial->execute();

            $stmtBitacora = $this->db->prepare("
                INSERT INTO bitacora_empleados (
                    id_empleado,
                    id_usuario,
                    accion
                ) VALUES (
                    :id_empleado,
                    :id_usuario,
                    'registro'
                )
            ");

            $stmtBitacora->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmtBitacora->bindValue(':id_usuario', $id_usuario_accion, PDO::PARAM_INT);
            $stmtBitacora->execute();

            $this->db->commit();

            return $id_empleado;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getDetalleById(int $id_empleado): ?array
    {
        $sql = "
            SELECT
                e.id_empleado,
                e.nombre_empleado,
                e.apellido_pat_empleado,
                e.apellido_mat_empleado,
                e.sexo,
                e.curp,
                e.rfc,
                e.nss,
                e.numero_preafiliacion_imss,
                e.fecha_ingreso,
                e.fecha_baja,
                e.motivo_baja,
                e.telefono,
                e.correo,
                e.estado_laboral,
                e.fecha_registro,
                e.fecha_actualizacion,
                p.id_puesto,
                p.nombre_puesto,
                d.id_departamento,
                d.nombre_departamento
            FROM empleados e
            INNER JOIN puestos p ON p.id_puesto = e.id_puesto
            INNER JOIN departamentos d ON d.id_departamento = p.id_departamento
            WHERE e.id_empleado = :id_empleado
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();

        $empleado = $stmt->fetch();

        return $empleado ?: null;
    }

    public function existsByFieldExceptId(string $field, string $value, int $id_empleado): bool
    {
        $allowedFields = [
            'curp',
            'rfc',
            'nss',
            'numero_preafiliacion_imss',
            'correo'
        ];

        if (!in_array($field, $allowedFields, true)) {
            return false;
        }

        $sql = "
            SELECT id_empleado
            FROM empleados
            WHERE $field = :value
            AND id_empleado != :id_empleado
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    public function update(array $data, int $id_usuario_accion): bool
    {
        $this->db->beginTransaction();

        try {
            $sql = "
                UPDATE empleados
                SET
                    nombre_empleado = :nombre_empleado,
                    apellido_pat_empleado = :apellido_pat_empleado,
                    apellido_mat_empleado = :apellido_mat_empleado,
                    sexo = :sexo,
                    curp = :curp,
                    rfc = :rfc,
                    nss = :nss,
                    numero_preafiliacion_imss = :numero_preafiliacion_imss,
                    id_puesto = :id_puesto,
                    telefono = :telefono,
                    correo = :correo
                WHERE id_empleado = :id_empleado
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':nombre_empleado', $data['nombre_empleado']);
            $stmt->bindValue(':apellido_pat_empleado', $data['apellido_pat_empleado']);
            $stmt->bindValue(':apellido_mat_empleado', $data['apellido_mat_empleado']);
            $stmt->bindValue(':sexo', $data['sexo']);
            $stmt->bindValue(':curp', $data['curp']);
            $stmt->bindValue(':rfc', $data['rfc']);
            $stmt->bindValue(':nss', $data['nss']);
            $stmt->bindValue(':numero_preafiliacion_imss', $data['numero_preafiliacion_imss']);
            $stmt->bindValue(':id_puesto', $data['id_puesto'], PDO::PARAM_INT);
            $stmt->bindValue(':telefono', $data['telefono']);
            $stmt->bindValue(':correo', $data['correo']);
            $stmt->bindValue(':id_empleado', $data['id_empleado'], PDO::PARAM_INT);

            $stmt->execute();

            $stmtBitacora = $this->db->prepare("
                INSERT INTO bitacora_empleados (
                    id_empleado,
                    id_usuario,
                    accion
                ) VALUES (
                    :id_empleado,
                    :id_usuario,
                    'edicion'
                )
            ");

            $stmtBitacora->bindValue(':id_empleado', $data['id_empleado'], PDO::PARAM_INT);
            $stmtBitacora->bindValue(':id_usuario', $id_usuario_accion, PDO::PARAM_INT);
            $stmtBitacora->execute();

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function inactivar(int $id_empleado, string $motivo_baja, int $id_usuario_accion): bool
    {
        $this->db->beginTransaction();

        try {
            $fecha_baja = date('Y-m-d');

            $sql = "
                UPDATE empleados
                SET
                    estado_laboral = 'inactivo',
                    fecha_baja = :fecha_baja,
                    motivo_baja = :motivo_baja
                WHERE id_empleado = :id_empleado
                AND estado_laboral = 'activo'
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':fecha_baja', $fecha_baja);
            $stmt->bindValue(':motivo_baja', $motivo_baja);
            $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException('No se pudo inactivar el empleado porque no se encuentra activo.');
            }

            $stmtHistorial = $this->db->prepare("
                UPDATE historial_laboral_empleados
                SET
                    fecha_baja = :fecha_baja,
                    motivo_baja = :motivo_baja,
                    estado_periodo = 'cerrado',
                    id_usuario_baja = :id_usuario_baja,
                    fecha_registro_baja = NOW()
                WHERE id_empleado = :id_empleado
                AND estado_periodo = 'activo'
                LIMIT 1
            ");

            $stmtHistorial->bindValue(':fecha_baja', $fecha_baja);
            $stmtHistorial->bindValue(':motivo_baja', $motivo_baja);
            $stmtHistorial->bindValue(':id_usuario_baja', $id_usuario_accion, PDO::PARAM_INT);
            $stmtHistorial->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmtHistorial->execute();

            if ($stmtHistorial->rowCount() === 0) {
                throw new RuntimeException('No se encontró un periodo laboral activo para cerrar.');
            }

            $stmtBitacora = $this->db->prepare("
                INSERT INTO bitacora_empleados (
                    id_empleado,
                    id_usuario,
                    accion
                ) VALUES (
                    :id_empleado,
                    :id_usuario,
                    'inactivacion'
                )
            ");

            $stmtBitacora->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmtBitacora->bindValue(':id_usuario', $id_usuario_accion, PDO::PARAM_INT);
            $stmtBitacora->execute();

            $stmtContratoVigente = $this->db->prepare("
                SELECT id_contrato
                FROM contratos
                WHERE id_empleado = :id_empleado
                AND estado_contrato = 'vigente'
                ORDER BY fecha_inicio DESC, id_contrato DESC
                LIMIT 1
                FOR UPDATE
            ");

            $stmtContratoVigente->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmtContratoVigente->execute();

            $contratoVigente = $stmtContratoVigente->fetch();

            if ($contratoVigente) {
                $id_contrato = (int) $contratoVigente['id_contrato'];

                $stmtTerminarContrato = $this->db->prepare("
                    UPDATE contratos
                    SET estado_contrato = 'terminado'
                    WHERE id_contrato = :id_contrato
                    AND estado_contrato = 'vigente'
                    LIMIT 1
                ");

                $stmtTerminarContrato->bindValue(':id_contrato', $id_contrato, PDO::PARAM_INT);
                $stmtTerminarContrato->execute();

                $stmtBitacoraContrato = $this->db->prepare("
                    INSERT INTO bitacora_contratos (
                        id_contrato,
                        id_usuario,
                        accion
                    ) VALUES (
                        :id_contrato,
                        :id_usuario,
                        'terminacion_por_baja'
                    )
                ");

                $stmtBitacoraContrato->bindValue(':id_contrato', $id_contrato, PDO::PARAM_INT);
                $stmtBitacoraContrato->bindValue(':id_usuario', $id_usuario_accion, PDO::PARAM_INT);
                $stmtBitacoraContrato->execute();
            }

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function reactivar(int $id_empleado, int $id_usuario_accion): bool
    {
        $this->db->beginTransaction();

        try {
            $fecha_reingreso = date('Y-m-d');

            $sql = "
                UPDATE empleados
                SET
                    estado_laboral = 'activo',
                    fecha_ingreso = :fecha_ingreso,
                    fecha_baja = NULL,
                    motivo_baja = NULL
                WHERE id_empleado = :id_empleado
                AND estado_laboral = 'inactivo'
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':fecha_ingreso', $fecha_reingreso);
            $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException('No se pudo reactivar el empleado porque no se encuentra inactivo.');
            }

            $stmtHistorialActivo = $this->db->prepare("
                SELECT id_historial_laboral
                FROM historial_laboral_empleados
                WHERE id_empleado = :id_empleado
                AND estado_periodo = 'activo'
                LIMIT 1
            ");

            $stmtHistorialActivo->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmtHistorialActivo->execute();

            if ($stmtHistorialActivo->fetch()) {
                throw new RuntimeException('El empleado ya tiene un periodo laboral activo en el historial.');
            }

            $stmtHistorial = $this->db->prepare("
                INSERT INTO historial_laboral_empleados (
                    id_empleado,
                    fecha_alta,
                    tipo_alta,
                    estado_periodo,
                    id_usuario_alta
                ) VALUES (
                    :id_empleado,
                    :fecha_alta,
                    'reactivacion',
                    'activo',
                    :id_usuario_alta
                )
            ");

            $stmtHistorial->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmtHistorial->bindValue(':fecha_alta', $fecha_reingreso);
            $stmtHistorial->bindValue(':id_usuario_alta', $id_usuario_accion, PDO::PARAM_INT);
            $stmtHistorial->execute();

            $stmtBitacora = $this->db->prepare("
                INSERT INTO bitacora_empleados (
                    id_empleado,
                    id_usuario,
                    accion
                ) VALUES (
                    :id_empleado,
                    :id_usuario,
                    'reactivacion'
                )
            ");

            $stmtBitacora->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmtBitacora->bindValue(':id_usuario', $id_usuario_accion, PDO::PARAM_INT);
            $stmtBitacora->execute();

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getHistorialLaboralByEmpleado(int $id_empleado): array
    {
        $sql = "
            SELECT
                h.id_historial_laboral,
                h.id_empleado,
                h.fecha_alta,
                h.fecha_baja,
                h.motivo_baja,
                h.tipo_alta,
                h.estado_periodo,
                h.fecha_registro_alta,
                h.fecha_registro_baja,
                ua.nombre AS usuario_alta,
                ub.nombre AS usuario_baja
            FROM historial_laboral_empleados h
            LEFT JOIN usuarios ua ON ua.id_usuario = h.id_usuario_alta
            LEFT JOIN usuarios ub ON ub.id_usuario = h.id_usuario_baja
            WHERE h.id_empleado = :id_empleado
            ORDER BY h.id_historial_laboral ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getUltimoMovimientoByEmpleado(int $id_empleado): ?array
    {
        $sql = "
            SELECT
                b.id_bitacora_empleado,
                b.accion,
                b.fecha_accion,
                u.nombre AS usuario_accion
            FROM bitacora_empleados b
            INNER JOIN usuarios u ON u.id_usuario = b.id_usuario
            WHERE b.id_empleado = :id_empleado
            ORDER BY b.fecha_accion DESC, b.id_bitacora_empleado DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();

        $movimiento = $stmt->fetch();

        return $movimiento ?: null;
    }
}