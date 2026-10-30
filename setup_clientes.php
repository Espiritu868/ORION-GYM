<?php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    try {
        $sql = "
        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='CLIENTES' and xtype='U')
        CREATE TABLE CLIENTES (
            id_cliente INT IDENTITY(1,1) PRIMARY KEY,
            identidad VARCHAR(50),
            nombre_completo VARCHAR(100) NOT NULL,
            telefono VARCHAR(20),
            email VARCHAR(100),
            direccion TEXT,
            fotografia VARCHAR(MAX),
            fecha_ingreso DATE DEFAULT GETDATE(),
            estado INT DEFAULT 1
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TEMP_PHOTOS' and xtype='U')
        CREATE TABLE TEMP_PHOTOS (
            token VARCHAR(50) PRIMARY KEY,
            foto VARCHAR(MAX) NOT NULL,
            creado_en DATETIME DEFAULT GETDATE()
        );
        ";

        $conn->exec($sql);
        echo "Tabla CLIENTES y TEMP_PHOTOS creadas correctamente.\n";
    } catch (Exception $e) {
        echo "Error creando tablas: " . $e->getMessage() . "\n";
    }
}
?>
