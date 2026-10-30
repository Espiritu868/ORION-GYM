<?php
require_once 'C:/xampp/htdocs/Orion_gym/config/Database.php';

try {
    $db = (new Database())->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "
    -- TABLA DE ASISTENCIA
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='ASISTENCIA' AND xtype='U')
    CREATE TABLE ASISTENCIA (
        id_asistencia INT IDENTITY(1,1) PRIMARY KEY,
        id_cliente INT NOT NULL FOREIGN KEY REFERENCES CLIENTES(id_cliente),
        fecha_hora DATETIME DEFAULT GETDATE()
    );
    ";

    $db->exec($sql);
    echo "Tabla ASISTENCIA creada con exito.\n";

    // Insertar permisos de asistencia y reportes
    $nuevos_permisos = [
        ['asistencia_view', 'Ver Asistencia y Accesos'],
        ['asistencia_create', 'Registrar Acceso (Check-In)'],
        ['reportes_view', 'Ver Reportes Administrativos']
    ];

    $stmtInsert = $db->prepare("
        IF NOT EXISTS (SELECT 1 FROM PERMISOS WHERE codigo_permiso = ?)
        BEGIN
            INSERT INTO PERMISOS (codigo_permiso, nombre_permiso) VALUES (?, ?)
        END
    ");

    foreach ($nuevos_permisos as $perm) {
        $stmtInsert->execute([$perm[0], $perm[0], $perm[1]]);
    }
    echo "Permisos de asistencia y reportes agregados.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
