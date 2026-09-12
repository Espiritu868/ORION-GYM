git add .
git commit -m "Feature: Implementacion completa de Membresias, Configuracion e Inventario (Kardex)" -m "Se completaron las siguientes funcionalidades:
1. Modulo de Administracion:
   - Backend y Frontend conectados a la BD para CRUD de Planes de Mensualidad.
   - Backend y Frontend para gestion de Inventario de Productos.
   - Nueva pestana de 'Control de Inactividad' para desactivar masivamente miembros inactivos (meses sin membresia).
2. Kardex de Inventario:
   - Creacion de tabla KARDEX_INVENTARIO en la BD.
   - Panel modal para registrar Ajustes manuales (Entradas/Salidas) afectando el stock general automaticamente.
   - Visualizacion cronologica del historial de movimientos.
3. Modulo de Membresias:
   - Dinamismo real (sin datos estaticos) para mostrar membresias activas, vencidas o canceladas.
   - Modal de asignacion: Busqueda dinamica de clientes existentes y seleccion de planes creados.
   - Sistema de calculo de dias restantes y estados dinamicos en base a fechas (Activa, Por Vencer, Vencida).
   - Acciones de Renovar membresia y Cancelar membresia (con registro de fecha_cancelacion).
4. Mejoras UI/UX:
   - Implementacion de Menu Hamburguesa lateral para expandir el ancho del contenido principal.
   - Modales centrados a pantalla completa y rediseno de altura para el Kardex."
git push origin Chamba-OG
