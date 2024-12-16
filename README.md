# basicsecurity
Implementa las medidas de seguridad básicas en tu sitio WordPress, fácil y gratis

Con sólo instalarlo implementa las siguientes medidas de seguridad:



- Desactivar la API de WooCommerce: Bloquea todos los accesos a la API REST de WooCommerce, devolviendo un error 403.

- Restringir tipos de archivos permitidos en las subidas: Solo permite imágenes (JPEG, PNG, GIF), documentos PDF y videos MP4.

- Deshabilitar el archivo xmlrpc.php: Bloquea cualquier intento de acceso al archivo xmlrpc.php.

- Eliminar el archivo install.php: Si existe y es escribible, elimina este archivo para evitar su reutilización.

- Configurar permisos de archivos y carpetas: Establece permisos recomendados para archivos (0644) y carpetas (0755) en todo el sitio.

- Deshabilitar la edición de archivos desde el panel: Evita que se editen archivos de temas o plugins desde el administrador de WordPress.

- Proteger wp-config.php y .htaccess: Añade reglas al archivo .htaccess para denegar el acceso a estos archivos críticos.

- Limitar intentos de inicio de sesión: Restringe el número de intentos fallidos a 5 por dirección IP y bloquea por 15 minutos si se exceden.

- Deshabilitar el listado de directorios: Añade la directiva Options -Indexes para impedir que los directorios sin índice sean visibles.

- Restringir acceso a la API REST para usuarios no autenticados: Exige autenticación para acceder a la API REST de WordPress.

- Deshabilitar los comentarios: Elimina la compatibilidad con comentarios en todas las publicaciones y páginas del sitio.

- Forzar HTTPS: Redirige automáticamente todo el tráfico HTTP a HTTPS.

- Cambiar la ruta de acceso al panel de administración: Cambia la URL de /wp-admin y /wp-login.php a /acceder.

- Restringir acceso a archivos PHP en la carpeta de uploads: Bloquea la ejecución de archivos PHP dentro de la carpeta de subidas.

