# Despliegue

Mantén los archivos de acceso público separados del código de la aplicación para garantizar una instalación segura.

**Pasos recomendados**

1. Copia los archivos públicos a la raíz de documentos de tu servidor web (ejemplo: `/var/www/html` o la raíz de un subdominio):

```bash
cp -R public/* /var/www/html/
```

*Nota: Asegúrate de copiar también el archivo oculto `.htaccess`. En algunos sistemas operativos o configuraciones de terminal, las copias con comodines como `public/*` pueden omitir archivos ocultos. Puedes copiarlo explícitamente:*

```bash
cp public/.htaccess /var/www/html/
```

2. Copia el resto de los archivos del proyecto fuera del sitio público (ejemplo: `/var/www/parina`):

```bash
mkdir -p /var/www/parina
# Desde la raíz del repositorio del proyecto; excluye la carpeta public
rsync -a --exclude='public' ./ /var/www/parina/
```

3. Instala las dependencias de PHP con Composer en la raíz del proyecto. Asegúrate de que Composer esté instalado y ejecuta el comando como el propietario del proyecto (no como `root`). Para producción, utiliza las opciones recomendadas que se muestran a continuación para omitir paquetes de desarrollo y optimizar el cargador automático.

```bash
# cambia a la raíz del proyecto (donde se encuentra composer.json)
cd /var/www/parina
# desarrollo: instala todas las dependencias
composer install
# producción: omite paquetes de desarrollo y optimiza el cargador automático
composer install --no-dev --optimize-autoloader
```

4. Otorga al grupo de Apache (servidor web) la propiedad de la carpeta de la base de datos para que el servidor pueda leer/escribir según sea necesario (ejemplo para Debian/Ubuntu):

```bash
sudo chown -R www-data:www-data /var/www/parina/src/Db
```

Notas:
- Si lo prefieres, en lugar de copiar archivos puedes apuntar el DocumentRoot de tu host virtual a la carpeta `public` dentro de tu proyecto (por ejemplo, `/var/www/parina/public`).
- Ajusta los comandos y los nombres de usuario/grupo para que coincidan con tu distribución y configuración de hosting.
- Protege cualquier archivo de entorno o configuración (no los expongas dentro del directorio raíz web público).

¡Saludos!
