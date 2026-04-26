# backend_jde

Proyecto servido con frontend estatico + API PHP sobre MySQL.

## Estado actual

- La salida del proyecto es PHP + MySQL.
- El backend PHP vive en [api/index.php](c:\Users\Emi\Desktop\backend_jde\api\index.php).
- La entrada web local y de desarrollo vive en [index.php](c:\Users\Emi\Desktop\backend_jde\index.php).
- El frontend se sirve de forma estatica desde [frontend](c:\Users\Emi\Desktop\backend_jde\frontend).

## Hosting objetivo

La configuracion actual queda preparada para hosting PHP clasico con Apache o equivalente, usando:

- reescrituras en [\.htaccess](c:\Users\Emi\Desktop\backend_jde\.htaccess)
- entrada API en [api/index.php](c:\Users\Emi\Desktop\backend_jde\api\index.php)
- reglas internas en [api/.htaccess](c:\Users\Emi\Desktop\backend_jde\api\.htaccess)

## Variables de entorno usadas por la API PHP

- DB_HOST
- DB_PORT
- DB_NAME
- DB_USER
- DB_PASSWORD
- DB_SSL
- ADMIN_EMAIL
- ADMIN_PASSWORD
- JWT_SECRET

## Validacion local PHP

1. Tener PHP 8.1+ con extensiones mysqli, pdo_mysql, openssl y mbstring.
2. Configurar el archivo .env.
3. Levantar el servidor con el front controller actual:

```powershell
php -c api/php.ini -S localhost:8000 index.php
```

4. Verificar:
   - /api/health
   - /
   - /login
   - login admin
   - generacion de semanario

## Scripts legados

La carpeta [scripts](c:\Users\Emi\Desktop\backend_jde\scripts) se conserva solo como referencia operativa temporal. No forma parte del servidor ni del despliegue del proyecto, y varios scripts siguen pendientes de adaptación a PHP + MySQL.