# GUÍA DE INSTALACIÓN Y PUESTA EN MARCHA DEL BIOMÉTRICO (ZKTECO ZK3969)

---

## 1. ¿Cómo funciona la arquitectura?

```
[ Lector ZK3969 ] (En la entrada / Red Local)
       │
       │ (Red LAN - Puerto 4370)
       ▼
[ PC Recepción ] -> Ejecuta: OrionGym_Biometrico.exe (o INICIAR_AGENTE.bat)
       │
       │ (Petición Web HTTPS por Internet)
       ▼
[ Tu Servidor Web / SQL Server ] (Orion Gym en la Nube)
  - Valida si el miembro pagó su membresía
  - Registra la asistencia en tiempo real
```

---

## 2. Cómo Enrolar Huellas en el Gimnasio (Solo toma 15 segundos)

1. En el sistema web vas a **Miembros**. 
   * Verás la columna **ID Bio** con una insignia negra (por ejemplo: `#15`).
2. En la pantalla del **ZK3969**:
   * Presiona **M/OK** durante 3 segundos para abrir el menú.
   * Entra a **Usuarios** -> **Nuevo Usuario**.
   * En **ID de Usuario**, escribe el número: `15`.
   * Selecciona **Huella** (o **Rostro**).
   * Pide al cliente poner el dedo 3 veces hasta el pitido verde.
   * Presiona **ESC** para guardar.

¡Listo! A partir de ese segundo, cada vez que el cliente ponga la huella:
* Si tiene membresía activa: Salta en verde en recepción y abre la puerta.
* Si está vencido: Salta en rojo con alerta de membresía vencida.

---

## 3. Configuración para cuando lo lleves donde tu cliente (`config.json`)

En la carpeta donde esté el ejecutable solo editas el archivo `config.json`:

```json
{
  "zk_ip": "192.168.1.200",
  "zk_port": 4370,
  "api_url": "https://oriongym.tudominio.com/api/biometrico.php",
  "api_token": "ORION_GYM_ZK_SECURE_TOKEN_2026",
  "comm_key": 0
}
```

* **`zk_ip`**: La IP fija que le configures al ZKTeco en el router del cliente.
* **`api_url`**: La URL donde tengas subido tu sistema web.

---

## 4. Convertir a Ejecutable (.EXE) para no necesitar instalar Python donde el cliente

1. En tu máquina de desarrollo, haz doble clic en **`COMPILAR_A_EXE.bat`**.
2. Te generará la carpeta:
   `biometrico_agent\dist\OrionGym_Biometrico\`
3. Esa carpeta la copias en una memoria USB y la pegas en la computadora del cliente.
4. **Para que arranque solo al encender la PC:**
   * Presiona `Windows + R`, escribe `shell:startup` y presiona Enter.
   * Pega ahí un acceso directo de `OrionGym_Biometrico.exe`.
