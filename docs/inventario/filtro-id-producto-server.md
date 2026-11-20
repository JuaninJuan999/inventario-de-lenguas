# Filtro Id producto en Inventario (server)

## Síntoma

En `http://192.168.20.205:8007/inventario-de-lenguas` se escribe un código en **Id producto**, se pulsa **Aplicar**, la página recarga y **no filtra** (sigue el listado completo; el input vuelve vacío o sin efecto).

## Causa

El formulario usaba `action="{{ route('inventario.lenguas') }}"` (URL absoluta). Si en el server el `.env` tiene un `APP_URL` distinto del host real — p. ej. `http://127.0.0.1:8000`, `http://127.0.0.1:8007` o la IP de otro PC — el navegador envía el GET a ese host incorrecto. Desde otro equipo parece un “reload” sin filtro; la barra de dirección **no** queda en `192.168.20.205:8007?...`.

## Arreglo en código (tras `git pull`)

1. Formulario con `action=""` (mismo origen / path actual).
2. JS de submit que navega a `/inventario-de-lenguas?id_producto=...` en el host actual.
3. Chips / Excel / sync con rutas root-relativas (`/inventario-de-lenguas/...`), sin `APP_URL`.
4. Búsqueda de código con variantes (con/sin guion), alineada al historial de despacho.

## Qué hacer en el server

```bash
cd /ruta/del/proyecto
git pull origin main
php artisan view:clear
php artisan config:clear
```

Recomendado en `.env` del server:

```env
APP_URL=http://192.168.20.205:8007
```

## Cómo validar

1. Abrir `http://192.168.20.205:8007/inventario-de-lenguas`
2. Escribir un Id producto que exista y **Aplicar**
3. La URL debe ser exactamente:
   `http://192.168.20.205:8007/inventario-de-lenguas?id_producto=CODIGO`
4. El resumen debe mostrar `total disponible: 1` (o el conteo filtrado), no el stock completo

Si la URL sigue yendo a `127.0.0.1` u otra IP: falta el `git pull` / `view:clear`, o hay caché de vistas antigua.

## Relacionado

- `docs/inventario/deploy-server-205.md`
- `docs/inventario/checklist-server.md` (sección `APP_URL`)
