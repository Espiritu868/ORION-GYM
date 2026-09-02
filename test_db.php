<?php
require_once 'config/Database.php';

try {
    $db = (new Database())->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='KARDEX_INVENTARIO' AND xtype='U')
    CREATE TABLE KARDEX_INVENTARIO (
        id_kardex INT IDENTITY(1,1) PRIMARY KEY,
        id_producto INT NOT NULL FOREIGN KEY REFERENCES PRODUCTOS(id_producto),
        tipo_movimiento VARCHAR(20) NOT NULL, -- ENTRADA, SALIDA, VENTA
        cantidad INT NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        descripcion VARCHAR(255),
        fecha_movimiento DATETIME DEFAULT GETDATE(),
        id_usuario INT NULL FOREIGN KEY REFERENCES USUARIOS(id_usuario)
    );
    ";
    
    $db->exec($sql);
    echo "Tabla KARDEX_INVENTARIO creada exitosamente.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
