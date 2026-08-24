<?php
define('SYSTEM_NAME', 'ORION GYM');
define('SYSTEM_ID', 2);
define('BASE_URL', 'http://localhost/Orion_gym/'); // Update accordingly
define('CLIENT_RTN', '16251998000746'); // For license validation

// URL de la API construida dinámicamente usando las constantes de arriba
define('API_LICENSE_URL', 'http://localhost/CAESOLUTION/api/validate_license.php?rtn=' . CLIENT_RTN . '&id_sistema=' . SYSTEM_ID);
?>
