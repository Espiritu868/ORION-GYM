<?php
require_once 'C:/xampp/htdocs/Orion_gym/config/Database.php';

try {
    $db = (new Database())->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "
    -- 1. TABLA DE PLANES
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PLANES' AND xtype='U')
    CREATE TABLE PLANES (
        id_plan INT IDENTITY(1,1) PRIMARY KEY,
        nombre_plan VARCHAR(100) NOT NULL,
        descripcion VARCHAR(255),
        precio DECIMAL(10,2) NOT NULL,
        duracion_dias INT NOT NULL,
        estado INT DEFAULT 1
    );

    -- 2. TABLA DE PRODUCTOS (INVENTARIO)
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PRODUCTOS' AND xtype='U')
    CREATE TABLE PRODUCTOS (
        id_producto INT IDENTITY(1,1) PRIMARY KEY,
        codigo_barras VARCHAR(50),
        nombre_producto VARCHAR(150) NOT NULL,
        descripcion VARCHAR(255),
        precio_compra DECIMAL(10,2) DEFAULT 0,
        precio_venta DECIMAL(10,2) NOT NULL,
        stock INT DEFAULT 0,
        estado INT DEFAULT 1
    );

    -- 3. TABLA DE MEMBRESIAS
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MEMBRESIAS' AND xtype='U')
    CREATE TABLE MEMBRESIAS (
        id_membresia INT IDENTITY(1,1) PRIMARY KEY,
        id_cliente INT NOT NULL FOREIGN KEY REFERENCES CLIENTES(id_cliente),
        id_plan INT NOT NULL FOREIGN KEY REFERENCES PLANES(id_plan),
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE NOT NULL,
        estado_membresia VARCHAR(20) DEFAULT 'Activa' -- Activa, Vencida, Cancelada
    );

    -- 4. TABLA DE TRANSACCIONES DE CAJA
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='CAJA_TRANSACCIONES' AND xtype='U')
    CREATE TABLE CAJA_TRANSACCIONES (
        id_transaccion INT IDENTITY(1,1) PRIMARY KEY,
        tipo_transaccion VARCHAR(10) NOT NULL, -- INGRESO, EGRESO
        concepto VARCHAR(255) NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        fecha_transaccion DATETIME DEFAULT GETDATE(),
        id_usuario INT NOT NULL FOREIGN KEY REFERENCES USUARIOS(id_usuario),
        id_membresia INT NULL FOREIGN KEY REFERENCES MEMBRESIAS(id_membresia)
    );

    -- 5. TABLA DE VENTAS (CABECERA)
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='VENTAS' AND xtype='U')
    CREATE TABLE VENTAS (
        id_venta INT IDENTITY(1,1) PRIMARY KEY,
        id_cliente INT NULL FOREIGN KEY REFERENCES CLIENTES(id_cliente),
        id_usuario INT NOT NULL FOREIGN KEY REFERENCES USUARIOS(id_usuario),
        fecha_venta DATETIME DEFAULT GETDATE(),
        total DECIMAL(10,2) NOT NULL,
        estado INT DEFAULT 1
    );

    -- 6. TABLA DE DETALLE DE VENTA
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='VENTA_DETALLE' AND xtype='U')
    CREATE TABLE VENTA_DETALLE (
        id_detalle INT IDENTITY(1,1) PRIMARY KEY,
        id_venta INT NOT NULL FOREIGN KEY REFERENCES VENTAS(id_venta),
        id_producto INT NOT NULL FOREIGN KEY REFERENCES PRODUCTOS(id_producto),
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL
    );
    ";

    $db->exec($sql);
    echo "Base de datos finalizada con exito.\n";

    // Insertar permisos de administracion
    $nuevos_permisos = [
        ['administracion_view', 'Ver Administración (Planes y Productos)'],
        ['administracion_create', 'Crear en Administración'],
        ['administracion_edit', 'Editar en Administración'],
        ['administracion_delete', 'Eliminar en Administración']
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
    echo "Permisos de administracion agregados.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
