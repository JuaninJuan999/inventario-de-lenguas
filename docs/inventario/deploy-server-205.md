# Despliegue rápido — server `192.168.20.205:8007`

Pasos para dejar el server igual que el entorno local (código + URL + filtros).

## 1. Traer código

En el server, dentro del proyecto:

```bash
cd /ruta/del/proyecto   # carpeta inventario-de-lenguas en 192.168.20.205
git pull origin main
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan modulos:sync-matriz
```

## 2. Configurar URL pública

Editar `.env` del **server**:

```env
APP_URL=http://192.168.20.205:8007
```

```bash
php artisan config:clear
php artisan view:clear
```

Entrar siempre por: `http://192.168.20.205:8007/`

## 3. Qué incluye el código reciente (main)

| Tema | Doc / comando |
|------|----------------|
| Filtros Inventario (rutas relativas) | Este ajuste + `APP_URL` correcto |
| Dashboard: ingresos últimos 5 días | Panel en dashboard (reemplaza “Importados sync”) |
| Baseline Excel / v2 / vencidos / aviso auxiliar | `docs/inventario/checklist-server.md` |

## 4. Validar filtros

1. `http://192.168.20.205:8007/inventario-de-lenguas`
2. ID producto → **Aplicar**
3. La URL debe ser `http://192.168.20.205:8007/inventario-de-lenguas?id_producto=...`
4. Si sigue yendo a `127.0.0.1`, falta el `git pull` o el `APP_URL` / `config:clear`

## Relacionado

Checklist completo: `docs/inventario/checklist-server.md`
