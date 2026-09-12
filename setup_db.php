<?php
require_once 'app/config/Database.php';
$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    try {
        $sql = "
        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='USUARIOS' and xtype='U')
        CREATE TABLE USUARIOS (
            id_usuario INT IDENTITY(1,1) PRIMARY KEY,
            nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            estado_usuario INT DEFAULT 1
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PERMISOS' and xtype='U')
        CREATE TABLE PERMISOS (
            codigo_permiso VARCHAR(50) PRIMARY KEY,
            nombre_permiso VARCHAR(100) NOT NULL,
            descripcion TEXT
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PERMISOS_USUARIO' and xtype='U')
        CREATE TABLE PERMISOS_USUARIO (
            id_usuario INT FOREIGN KEY REFERENCES USUARIOS(id_usuario),
            codigo_permiso VARCHAR(50) FOREIGN KEY REFERENCES PERMISOS(codigo_permiso),
            PRIMARY KEY (id_usuario, codigo_permiso)
        );

        IF NOT EXISTS (SELECT * FROM PERMISOS WHERE codigo_permiso='admin')
        INSERT INTO PERMISOS (codigo_permiso, nombre_permiso) VALUES ('admin', 'Administrador Total');
        
        IF NOT EXISTS (SELECT * FROM PERMISOS WHERE codigo_permiso='recepcion')
        INSERT INTO PERMISOS (codigo_permiso, nombre_permiso) VALUES ('recepcion', 'Recepción');

        IF NOT EXISTS (SELECT * FROM USUARIOS WHERE nombre_usuario='admin')
        BEGIN
            INSERT INTO USUARIOS (nombre_usuario, password_hash, estado_usuario) 
            VALUES ('admin', '" . hash('sha256', 'admin123') . "', 1);
            
            DECLARE @id_admin INT;
            SELECT @id_admin = id_usuario FROM USUARIOS WHERE nombre_usuario = 'admin';
            
            INSERT INTO PERMISOS_USUARIO (id_usuario, codigo_permiso) VALUES (@id_admin, 'admin');
        END
        ";

        $conn->exec($sql);
        echo "Base de datos inicializada correctamente.\n";
    } catch (Exception $e) {
        echo "Error creando tablas: " . $e->getMessage() . "\n";
    }
}
?>
