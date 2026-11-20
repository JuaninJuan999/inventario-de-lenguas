# Checklist servidor — inventario operativo (igual que local)

Tras `git pull`, seguir en orden según lo que falte en el server.

```bash
cd /ruta/del/proyecto
git pull
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan modulos:sync-matriz
```

### `APP_URL` y filtros de Inventario (ajuste crítico)

**URL de acceso al programa en el server:**

```text
http://192.168.20.205:8007/
```

En el `.env` **del server** (no el de Laragon local):

```env
APP_URL=http://192.168.20.205:8007
```

No uses `http://127.0.0.1:8000` ni la IP de otro PC (p. ej. `.11`). Si `APP_URL` apunta a localhost, al pulsar **Aplicar** / chips / Excel el navegador puede ir a una URL incorrecta y los filtros “no funcionan”.

Además, Inventario ya no depende de `route()` absoluto para **Aplicar** / chips / Excel / sync: usa `action=""` + paths `/inventario-de-lenguas...` y un submit JS same-origin (ver `docs/inventario/filtro-id-producto-server.md`).

Tras editar `.env`:

```bash
php artisan config:clear
php artisan view:clear
```

Comprobar: abrir `http://192.168.20.205:8007/inventario-de-lenguas`, filtrar por un ID producto y **Aplicar** — la barra de dirección debe seguir en `192.168.20.205:8007` con `?id_producto=...` y el listado acotado.

---

## 1. Baseline inicial (solo si aún no se hizo)

Vacía y parte del Excel de planta (766 códigos, corte 2026-11-19):

→ `docs/inventario/reiniciar-desde-excel.md`

```bash
php artisan inventario:reiniciar-desde-excel "docs/inventario/INV LENGUAS 19-11-2026.xlsx" --force --desde=2026-10-01 --fecha-corte=2026-11-19
# si faltan:
php artisan inventario:completar-baseline --desde=2026-10-01
```

---

## 2. Depurar vencidos (conservar -1 y -2)

→ `docs/inventario/depurar-vencidos.md`

```bash
php artisan inventario:depurar-vencidos --dry-run
php artisan inventario:depurar-vencidos --force
```

---

## 3. Sumar códigos del Excel v2 (sin borrar lo demás)

→ `docs/inventario/importar-excel-adicionales.md`

```bash
php artisan inventario:importar-excel "docs/inventario/inventario-v2.xlsx" --dry-run
php artisan inventario:importar-excel "docs/inventario/inventario-v2.xlsx" --force --desde=2026-10-01
```

Requiere SIRT reachable desde el server.

---

## 4. Aviso sonoro de despacho (auxiliar + super_admin)

→ `docs/aviso-despacho-auxiliar.md`

- Audio: `public/song/sound.mp3`
- Roles: `auxiliar`, `user`, `super_admin`
- Logístico: sin tono; vuelve al alistamiento

Tras pull + `view:clear` + `modulos:sync-matriz` basta. El auxiliar/super_admin debe tener la app abierta (un clic en la página para desbloquear audio).

---

## 5. Dato “total disponible” en Inventario

Es el conteo de lenguas **sin despachar** en la BD local (con filtros de la pantalla si los hay).
Recargar sin filtros si aparecen dos cifras distintas (sync vs momento anterior).

---

## Índice de docs

| Documento | Tema |
|-----------|------|
| `reiniciar-desde-excel.md` | Baseline / wipe + Excel inicial |
| `depurar-vencidos.md` | Borrar vencidos ≤ -3 |
| `importar-excel-adicionales.md` | Sumar Excel v2 sin duplicar |
| `../aviso-despacho-auxiliar.md` | Tono + toast |
| `../sincronizacion-inventario.md` | Sync habitual |
| `../roles-y-modulos.md` | Permisos |
