<?php
define('SYSTEM_NAME', 'ORION GYM');
define('SYSTEM_ID', 2);
define('CLIENT_RTN', '16251998000746'); // For license validation

// URL de la API construida dinámicamente usando las constantes de arriba
define('API_LICENSE_URL', 'https://gestion.caesolutions.online/api/validate_license.php?rtn=' . CLIENT_RTN . '&id_sistema=' . SYSTEM_ID);

// Global Permission Check function
function hasPerm($code) {
    if (isset($_SESSION['permissions']) && in_array('admin', $_SESSION['permissions'])) return true;
    return isset($_SESSION['permissions']) && in_array($code, $_SESSION['permissions']);
}
?>
