# Sistema de gestion academica - Desarrollo web


### Descripcion


Este sistema fue creado utilizando Laravel, SQLite, Tailwind CSS e Inertia con Vue. 


### Ejecucion

Para ejecutar el sistema, primero debes clonar el repositorio y luego instalar las dependencias necesarias. Asegúrate de tener instalado Composer y Node.js en tu máquina.

```bash
  composer install
  npm install
```

Tambien debemos crear un usuario por defecto.
Para ello, ejecutamos el siguiente comando:
```bash
  php artisan db:seed --class= CreateUser
```

Ahora podemos ejecutar el servidor de desarrollo de Laravel y el servidor de desarrollo de Node.js:

```bash
  php artisan serve
  npm run dev
```

Si accedemos a la URL `http://localhost:8000`, deberíamos ver la página de inicio del sistema de gestión académica.
