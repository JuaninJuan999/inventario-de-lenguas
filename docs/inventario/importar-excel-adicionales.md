# Importar códigos adicionales desde Excel (sin borrar)

Añade a la réplica local los códigos de un Excel **que aún no existan**.
No vacía inventario, no elimina despachos ni toca roles.

Útil cuando planta entrega un segundo listado (p. ej. `inventario-v2.xlsx`) y hay que
sumarlo al stock ya depurado / baseline.

---

## Archivo

| Archivo | Uso |
|---------|-----|
| `docs/inventario/inventario-v2.xlsx` | Columna **Codigo** (p. ej. `2611-07641-6000`) |

---

## Comando

```bash
# Ver cuántos faltarían
php artisan inventario:importar-excel "docs/inventario/inventario-v2.xlsx" --dry-run

# Importar desde SIRT (sin confirmación)
php artisan inventario:importar-excel "docs/inventario/inventario-v2.xlsx" --force --desde=2026-10-01
```

Qué hace:

1. Lee códigos únicos del Excel.
2. Compara con `ingreso_lenguas_locales` (normaliza `-6000`).
3. Actualiza `storage/app/inventario/baseline.json` para incluir los nuevos.
4. Trae desde SIRT solo los faltantes (reintento amplio `2026-01-01` si hace falta).

Requisitos: conexión SIRT OK en `.env` (`POSTGRES_*` / `colbeef`).

---

## En el servidor

```bash
git pull
php artisan config:clear
php artisan view:clear
# comprobar SIRT (Inventario o Ingresos deben poder consultar)
php artisan inventario:importar-excel "docs/inventario/inventario-v2.xlsx" --dry-run
php artisan inventario:importar-excel "docs/inventario/inventario-v2.xlsx" --force --desde=2026-10-01
```

Verificar en **Inventario de Lenguas** el **total disponible** (lenguas sin despachar).

---

## Código

`app/Console/Commands/ImportarExcelInventarioCommand.php`
