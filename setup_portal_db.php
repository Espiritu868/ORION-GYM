<?php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    try {
        $sql = "
        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MEDIDAS_CLIENTE' and xtype='U')
        CREATE TABLE MEDIDAS_CLIENTE (
            id_medida INT IDENTITY(1,1) PRIMARY KEY,
            id_cliente INT FOREIGN KEY REFERENCES CLIENTES(id_cliente),
            fecha_evaluacion DATE DEFAULT GETDATE(),
            peso DECIMAL(5,2),
            pecho DECIMAL(5,2),
            brazo DECIMAL(5,2),
            cintura DECIMAL(5,2),
            cadera DECIMAL(5,2),
            pierna DECIMAL(5,2),
            pantorrilla DECIMAL(5,2)
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='RUTINAS_CLIENTE' and xtype='U')
        CREATE TABLE RUTINAS_CLIENTE (
            id_rutina INT IDENTITY(1,1) PRIMARY KEY,
            id_cliente INT FOREIGN KEY REFERENCES CLIENTES(id_cliente),
            dia_semana INT NOT NULL,
            descripcion_rutina TEXT NOT NULL
        );
        ";

        $conn->exec($sql);
        echo "Tablas de medidas y rutinas creadas correctamente.\n";
    } catch (Exception $e) {
        echo "Error creando tablas: " . $e->getMessage() . "\n";
    }
} else {
    echo "No se pudo conectar a la base de datos.\n";
}
?>
