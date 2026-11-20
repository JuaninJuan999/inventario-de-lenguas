# Depurar lenguas vencidas del inventario local

Alinea la réplica local con existencias físicas: **conserva** vencidos recientes
(**-1** y **-2** días) y **elimina** los más antiguos (**-3** o menos).

No toca SIRT. Solo borra filas disponibles (`despachado_at` null) en `ingreso_lenguas_locales`.

Vencimiento = fecha de registro + 4 días calendario (zona de operación), igual que en Dashboard / Inventario.

---

## Comando

```bash
# Ver qué se borraría (sin eliminar)
php artisan inventario:depurar-vencidos --dry-run

# Ejecutar (pide confirmación)
php artisan inventario:depurar-vencidos

# En servidor / scripts
php artisan inventario:depurar-vencidos --force
```

Por defecto `--min-dias=-2`: se eliminan registros con días hasta vencimiento **&lt; -2**.

| Días (UI) | Acción |
|-----------|--------|
| ≥ 0 | Se conservan |
| -1, -2 | Se conservan |
| ≤ -3 | Se eliminan |

---

## En el servidor

```bash
git pull
php artisan config:clear
php artisan inventario:depurar-vencidos --dry-run
php artisan inventario:depurar-vencidos --force
```

Luego abrir Inventario / Dashboard y verificar totales vs. físico.

---

## Código

`app/Console/Commands/DepurarVencidosInventarioCommand.php`
