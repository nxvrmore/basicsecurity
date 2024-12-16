<?php
/*
Plugin Name: Basic Security
Plugin URI: https://github.com/nxvrmore
Description: Desactiva la API de WooCommerce, restringe los tipos de archivos permitidos, deshabilita xmlrpc.php, elimina install.php y configura los permisos de archivos.
Version: 5.4
Author: Nxvrmore
Author URI: https://github.com/nxvrmore
License: GPL2
*/

// 1. Desactivar la API de WooCommerce
add_filter('woocommerce_rest_check_permissions', function($permission, $context, $object_id, $post) {
    return new WP_Error('woocommerce_api_disabled', 'La API REST de WooCommerce está desactivada.', ['status' => 403]);
}, 10, 4);

// 2. Restringir los tipos de archivos permitidos y validar antes de guardar
add_filter('upload_mimes', function($mimes) {
    return [
        'jpg|jpeg' => 'image/jpeg',
        'png'      => 'image/png',
        'gif'      => 'image/gif',
        'pdf'      => 'application/pdf',
        'mp4'      => 'video/mp4',
    ];
});

add_filter('wp_handle_upload_prefilter', function($file) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp4'];
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension'] ?? '');

    if (!in_array($extension, $allowed_extensions, true)) {
        $file['error'] = 'Tipo de archivo no permitido. Solo se aceptan imágenes, PDFs y videos MP4.';
    }

    return $file;
});

// 3. Deshabilitar xmlrpc.php
add_action('init', function() {
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        wp_die('xmlrpc.php está deshabilitado en este sitio.', '', ['response' => 403]);
    }
});

// 4. Eliminar el archivo install.php si existe
add_action('init', function() {
    $install_file = ABSPATH . 'wp-admin/install.php';
    if (file_exists($install_file) && is_writable($install_file)) {
        @unlink($install_file);
    }
});

// 5. Configurar permisos de archivos y directorios correctamente
add_action('admin_init', function() {
    if (!current_user_can('administrator')) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(ABSPATH, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            @chmod($item, 0755);
        } else {
            @chmod($item, 0644);
        }
    }
});

// 6. Deshabilitar la edición de archivos desde el panel
add_action('init', function() {
    if (!defined('DISALLOW_FILE_EDIT')) {
        define('DISALLOW_FILE_EDIT', true);
    }
});

// 7. Proteger wp-config.php y .htaccess
add_action('init', function() {
    $htaccess_rules = "
<Files wp-config.php>
    Order allow,deny
    Deny from all
</Files>

<Files .htaccess>
    Order allow,deny
    Deny from all
</Files>";
    $htaccess_file = ABSPATH . '.htaccess';

    if (file_exists($htaccess_file) && is_writable($htaccess_file)) {
        $current_content = file_get_contents($htaccess_file);
        if (strpos($current_content, '<Files wp-config.php>') === false) {
            file_put_contents($htaccess_file, $htaccess_rules, FILE_APPEND | LOCK_EX);
        }
    }
});

// 8. Limitar intentos de inicio de sesión
add_action('wp_login_failed', function($username) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $limit = 5;
    $lock_time = 15 * MINUTE_IN_SECONDS;

    $failed_logins = get_transient('failed_logins_' . $ip) ?: 0;

    if ($failed_logins >= $limit) {
        wp_die('Has excedido el número máximo de intentos. Inténtalo de nuevo más tarde.', '', ['response' => 403]);
    }

    set_transient('failed_logins_' . $ip, $failed_logins + 1, $lock_time);
});

add_action('wp_login', function($username) {
    $ip = $_SERVER['REMOTE_ADDR'];
    delete_transient('failed_logins_' . $ip);
});

// 9. Deshabilitar el listado de directorios
add_action('init', function() {
    $htaccess_rules = "
Options -Indexes
";
    $htaccess_file = ABSPATH . '.htaccess';

    if (file_exists($htaccess_file) && is_writable($htaccess_file)) {
        $current_content = file_get_contents($htaccess_file);
        if (strpos($current_content, 'Options -Indexes') === false) {
            file_put_contents($htaccess_file, $htaccess_rules, FILE_APPEND | LOCK_EX);
        }
    }
});

// 10. Restringir acceso a la API REST para usuarios no autenticados
add_filter('rest_authentication_errors', function($result) {
    if (!is_user_logged_in()) {
        return new WP_Error('rest_not_logged_in', 'Debes estar autenticado para acceder a la API REST.', ['status' => 401]);
    }
    return $result;
});

// 11. Deshabilitar comentarios
add_action('init', function() {
    remove_post_type_support('post', 'comments');
    remove_post_type_support('page', 'comments');
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
        }
    }
});

// 12. Forzar HTTPS
add_action('template_redirect', function() {
    if (!is_ssl()) {
        wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
        exit;
    }
});

// 13. Cambiar /wp-admin a /acceder
add_action('init', function() {
    global $pagenow;

    if ($pagenow === 'wp-login.php' && !isset($_GET['acceder'])) {
        wp_redirect(home_url('/acceder'));
        exit;
    }
});

add_action('template_redirect', function() {
    if ($_SERVER['REQUEST_URI'] === '/acceder') {
        require_once ABSPATH . 'wp-login.php';
        exit;
    }
});

add_action('admin_init', function() {
    if (!is_user_logged_in() && is_admin()) {
        wp_redirect(home_url('/acceder'));
        exit;
    }
});

// 14. Restringir acceso a archivos PHP en la carpeta de uploads
add_action('init', function() {
    $htaccess_rules = "
<Files *.php>
    deny from all
</Files>";
    $uploads_dir = wp_upload_dir()['basedir'] . '/.htaccess';

    if (!file_exists($uploads_dir)) {
        file_put_contents($uploads_dir, $htaccess_rules, LOCK_EX);
    }
});
