<?php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    try {
        $sql = "
        IF EXISTS (SELECT * FROM sysobjects WHERE name='RUTINAS_CLIENTE' and xtype='U')
        DROP TABLE RUTINAS_CLIENTE;

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='CATALOGO_EJERCICIOS' and xtype='U')
        CREATE TABLE CATALOGO_EJERCICIOS (
            id_ejercicio INT IDENTITY(1,1) PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            grupo_muscular VARCHAR(50) NOT NULL,
            imagen_url VARCHAR(255)
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='RUTINA_DIAS' and xtype='U')
        CREATE TABLE RUTINA_DIAS (
            id_rutina_dia INT IDENTITY(1,1) PRIMARY KEY,
            id_cliente INT FOREIGN KEY REFERENCES CLIENTES(id_cliente),
            dia_semana INT NOT NULL
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='RUTINA_EJERCICIOS' and xtype='U')
        CREATE TABLE RUTINA_EJERCICIOS (
            id_rutina_ej INT IDENTITY(1,1) PRIMARY KEY,
            id_rutina_dia INT FOREIGN KEY REFERENCES RUTINA_DIAS(id_rutina_dia) ON DELETE CASCADE,
            id_ejercicio INT FOREIGN KEY REFERENCES CATALOGO_EJERCICIOS(id_ejercicio),
            orden INT DEFAULT 0,
            descanso_segundos INT DEFAULT 120
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='RUTINA_SERIES' and xtype='U')
        CREATE TABLE RUTINA_SERIES (
            id_serie INT IDENTITY(1,1) PRIMARY KEY,
            id_rutina_ej INT FOREIGN KEY REFERENCES RUTINA_EJERCICIOS(id_rutina_ej) ON DELETE CASCADE,
            numero_serie INT NOT NULL,
            lbs_obj DECIMAL(6,2),
            reps_obj INT,
            rpe_obj DECIMAL(3,1)
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='ENTRENAMIENTO_LOG' and xtype='U')
        CREATE TABLE ENTRENAMIENTO_LOG (
            id_entrenamiento INT IDENTITY(1,1) PRIMARY KEY,
            id_cliente INT FOREIGN KEY REFERENCES CLIENTES(id_cliente),
            dia_semana INT NOT NULL,
            fecha DATETIME DEFAULT GETDATE(),
            duracion_segundos INT DEFAULT 0,
            finalizado INT DEFAULT 0
        );

        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='ENTRENAMIENTO_SERIES_LOG' and xtype='U')
        CREATE TABLE ENTRENAMIENTO_SERIES_LOG (
            id_log_serie INT IDENTITY(1,1) PRIMARY KEY,
            id_entrenamiento INT FOREIGN KEY REFERENCES ENTRENAMIENTO_LOG(id_entrenamiento) ON DELETE CASCADE,
            id_ejercicio INT FOREIGN KEY REFERENCES CATALOGO_EJERCICIOS(id_ejercicio),
            numero_serie INT NOT NULL,
            lbs_real DECIMAL(6,2),
            reps_real INT,
            rpe_real DECIMAL(3,1),
            completada INT DEFAULT 0
        );
        ";

        $conn->exec($sql);
        echo "Esquema relacional avanzado creado.\n";

        // Inject default exercises if catalog is empty
        $check = $conn->query("SELECT COUNT(*) FROM CATALOGO_EJERCICIOS");
        $count = $check->fetchColumn();
        if ($count == 0) {
            $ejercicios = [
                ['Press de Banca (Barra)', 'Pecho'],
                ['Press de Banca Inclinado (Mancuernas)', 'Pecho'],
                ['Aperturas (Pec Deck)', 'Pecho'],
                ['Dominadas', 'Espalda'],
                ['Remo con Barra', 'Espalda'],
                ['Jalón al Pecho', 'Espalda'],
                ['Sentadilla Libre', 'Pierna'],
                ['Prensa de Piernas', 'Pierna'],
                ['Extensiones de Cuádriceps', 'Pierna'],
                ['Curl Femoral Acostado', 'Pierna'],
                ['Peso Muerto Rumano', 'Pierna'],
                ['Elevación de Talones (Pantorrilla)', 'Pierna'],
                ['Press Militar (Mancuernas)', 'Hombro'],
                ['Elevaciones Laterales (Mancuernas)', 'Hombro'],
                ['Pájaros (Hombro Posterior)', 'Hombro'],
                ['Curl de Bíceps (Barra)', 'Bíceps'],
                ['Curl Martillo (Mancuernas)', 'Bíceps'],
                ['Extensiones de Tríceps (Polea)', 'Tríceps'],
                ['Press Francés (Barra Z)', 'Tríceps'],
                ['Crunch Abdominal', 'Core'],
                ['Plancha', 'Core']
            ];

            $stmt = $conn->prepare("INSERT INTO CATALOGO_EJERCICIOS (nombre, grupo_muscular) VALUES (?, ?)");
            foreach ($ejercicios as $ej) {
                $stmt->execute($ej);
            }
            echo "Catálogo de 21 ejercicios inyectado.\n";
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No connection.\n";
}
?>
