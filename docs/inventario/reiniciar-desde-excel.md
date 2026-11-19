# Reiniciar inventario local desde Excel (baseline)

Partir de la lista fija de códigos del Excel de planta y seguir alimentando **solo hacia adelante**
desde un REF. TURNO de corte (en este corte: **2026-11-19**).

No toca SIRT ni usuarios/roles. Solo la BD local de la app.

Estado esperado tras el procedimiento (igual que en desarrollo):

| Concepto | Valor |
|----------|--------|
| Códigos en Excel | 769 líneas |
| Únicos normalizados / stock local | **766** |
| Fecha de corte (REF. TURNO) | **2026-11-19** |
| Baseline persistido | `storage/app/inventario/baseline.json` |

---

## Archivos de referencia (van en el repo)

| Archivo | Uso |
|---------|-----|
| `docs/inventario/INV LENGUAS 19-11-2026.xlsx` | Lista oficial (columna **Codigo**, p. ej. `2610-08191-6000`) |
| `docs/inventario/INV-LENGUAS-19-11-2026-codigos.txt` | Misma lista en texto (un código por línea), útil si el Excel falla en el server |

Los códigos del Excel suelen traer sufijo `-6000`; en inventario local coinciden también con la forma normalizada (`2610-08191`).

---

## Qué hace el reinicio

```bash
php artisan inventario:reiniciar-desde-excel "docs/inventario/INV LENGUAS 19-11-2026.xlsx" --force --fecha-corte=2026-11-19
```

1. Vacía datos operativos locales (`ingreso_lenguas_locales`, y por defecto también despachos, despostes, decomisos, placas, accesos).
2. Guarda el **baseline** (`baseline.json`) con los 766 códigos y la fecha de corte.
3. Reimporta desde SIRT por tramos mensuales (`--desde` → `--hasta`; default desde `2026-10-01` hasta el día de operación).
4. Solo deja en la réplica códigos de la lista del Excel (purga el resto).
5. Si faltan códigos, los recupera uno a uno por `id_producto` (sin tope artificial).

Mientras exista `baseline.json`, la sync normal (`inventario:sync-lenguas` / polling):

- **sí** crea/actualiza códigos del Excel;
- **sí** crea lenguas con `fecha_turno_referencia` **≥ 2026-11-19**;
- **no** reabre histórico de SIRT anterior al corte que no estaba en el Excel
  (evita que el total salte de ~766 a miles al abrir Inventario o el dashboard).

---

## En el servidor (checklist para quedar igual que local)

### 1. Traer código y limpiar cachés

```bash
cd /ruta/del/proyecto
git pull
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### 2. Matriz de roles (incluye Historial para logístico)

```bash
php artisan modulos:sync-matriz
```

### 3. Tope SIRT temporal (recomendado)

En `.env`, durante el reinicio:

```env
INGRESOS_LENGUAS_CONSULTA_LIMIT=5000
```

```bash
php artisan config:clear
```

### 4. Reiniciar inventario desde el Excel

```bash
php artisan inventario:reiniciar-desde-excel "docs/inventario/INV LENGUAS 19-11-2026.xlsx" --force --desde=2026-10-01 --fecha-corte=2026-11-19
```

Alternativa con el `.txt` (si el xlsx no está o falla):

```bash
php artisan inventario:reiniciar-desde-excel "docs/inventario/INV-LENGUAS-19-11-2026-codigos.txt" --force --desde=2026-10-01 --fecha-corte=2026-11-19
```

Puede tardar varios minutos (tramos + recuperación individual).

### 5. Si el total no llega a 766

```bash
php artisan inventario:completar-baseline --desde=2026-10-01
```

### 6. Verificar

- Dashboard / Inventario: **total disponible ≈ 766**
- Existe `storage/app/inventario/baseline.json` con `fecha_corte: "2026-11-19"`
- Tras abrir Inventario o `php artisan inventario:sync-lenguas`, el total **no** debe dispararse a miles

### 7. Opcional

Volver `INGRESOS_LENGUAS_CONSULTA_LIMIT` al valor habitual (p. ej. `2000`) y `php artisan config:clear`.

---

## Opciones del comando

| Opción | Efecto |
|--------|--------|
| `--force` | Sin confirmación interactiva (usar en server) |
| `--desde=YYYY-MM-DD` | Inicio del rango SIRT (default `2026-10-01`) |
| `--hasta=YYYY-MM-DD` | Fin del rango (default: día de operación) |
| `--fecha-corte=YYYY-MM-DD` | Desde qué REF. TURNO se alimenta lo nuevo (default: `--hasta`) |
| `--solo-inventario` | No borra despachos/despostes/decomisos/placas/accesos |
| `--sin-sync` | Solo vacía + guarda baseline; no consulta SIRT |
| `--columna=Codigo` | Nombre del encabezado en el Excel |

---

## Código relacionado

| Pieza | Ruta |
|-------|------|
| Reinicio | `app/Console/Commands/ReiniciarInventarioDesdeExcelCommand.php` |
| Completar faltantes | `app/Console/Commands/CompletarBaselineExcelCommand.php` |
| Gate de altas | `app/Services/Inventario/InventarioBaseline.php` |
| Sync (respeta baseline) | `app/Services/Inventario/InventarioLenguasSynchronizer.php` |
| Lector xlsx | `app/Support/SimpleXlsxReader.php` |
| Sync habitual | `docs/sincronizacion-inventario.md` |
| Roles / módulos | `docs/roles-y-modulos.md` |
