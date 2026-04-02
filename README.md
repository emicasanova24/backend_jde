# backend_jde

Aplicacion Node.js + Express con frontend estatico y PostgreSQL, preparada para desplegarse en Render.

## Deploy en Render

Este repositorio incluye [render.yaml](render.yaml) para crear:

- Un Web Service Node
- Una base Render Postgres

### Opcion recomendada: Blueprint

1. En Render, elegir New + Blueprint.
2. Conectar este repositorio de GitHub.
3. Confirmar la creacion del servicio y la base.
4. Completar las variables manuales del servicio:
   - ADMIN_EMAIL
   - ADMIN_PASSWORD
5. Esperar el primer deploy.

JWT_SECRET se genera automaticamente desde Render. Las variables de base DB_HOST, DB_PORT, DB_NAME, DB_USER y DB_PASSWORD se enlazan desde la base creada por el blueprint.

### Variables de entorno usadas por la app

- DB_HOST
- DB_PORT
- DB_NAME
- DB_USER
- DB_PASSWORD
- DB_SSL
- ADMIN_EMAIL
- ADMIN_PASSWORD
- JWT_SECRET
- PORT

### Scripts

- npm start: inicia el servidor en [src/app.js](src/app.js)

### Verificaciones despues del deploy

1. Abrir /health y verificar respuesta ok.
2. Abrir la home publica del sitio.
3. Probar login admin.
4. Revisar que equipos, jugadores, partidos y assets carguen sin errores.

### Desarrollo local

1. Instalar dependencias con npm install.
2. Configurar .env local.
3. Iniciar con npm start.