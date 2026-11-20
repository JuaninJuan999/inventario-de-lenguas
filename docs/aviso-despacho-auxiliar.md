# Aviso de despacho nuevo (rol Auxiliar)

Cuando el logístico termina un despacho, el **auxiliar** recibe en vivo:

1. Tono `public/song/sound.mp3`
2. Toast informativo (~7 s) con consecutivo real, placa y enlace al PDF

El logístico **no** oye el tono ni ve el toast: cierra el despacho y vuelve al alistamiento de placas. Tampoco tiene el módulo Entrega de conformidad.

---

## Cómo funciona

| Pieza | Rol |
|-------|-----|
| `DespachoFinalizarInventarioController` | Al cerrar OK, guarda aviso en caché (`despacho.conformidad.aviso`) con `id`, `numero`, placa, PDF. |
| `GET entrega-de-conformidad/avisos` | Roles `auxiliar` / `user` / `super_admin`. Devuelve despachos nuevos desde `after_id`. |
| `partials/aviso-despacho-auxiliar.blade.php` | Poll cada 2 s + toast + audio. |
| `partials/logo-institucional.blade.php` | Incluye el partial → el aviso corre en **cualquier módulo** del auxiliar. |

`numero` = cantidad de despachos existentes con `id <=` el actual (consecutivo operativo). **No** es el autoincrement de MySQL.

---

## En el servidor (checklist)

```bash
cd /ruta/del/proyecto
git pull
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan modulos:sync-matriz
```

Comprobar que exista el audio:

```text
public/song/sound.mp3
```

Si el servidor usa `php artisan config:cache` / `route:cache` / `view:cache`, regenerarlos tras el pull.

### Validación rápida

1. Sesión **auxiliar** abierta en cualquier módulo (hacer un clic en la página para desbloquear audio del navegador).
2. En otra sesión, un logístico termina un despacho (o crear uno de prueba).
3. En ≤ 2–3 s: tono + toast “Despacho #N …” que desaparece solo.

### Notas de navegador

- Chrome/Edge bloquean audio hasta la **primera interacción** del usuario en la pestaña.
- El toast sigue apareciendo aunque el audio esté bloqueado.

---

## Roles

Ver `docs/roles-y-modulos.md`:

- `logistico` / `operador`: despacho, desposte, historial (sin `entrega`)
- `auxiliar` / `user` / `super_admin`: aviso sonoro + toast global al cerrarse un despacho

Tras cambiar `config/modulos_por_rol.php` en el server: `php artisan modulos:sync-matriz`.

---

## Código

| Archivo | Uso |
|---------|-----|
| `app/Http/Controllers/Despacho/DespachoFinalizarInventarioController.php` | Caché del aviso al finalizar |
| `app/Http/Controllers/EntregaConformidadController.php` | Endpoint `avisosNuevos` + `numero` |
| `routes/web.php` | `entrega.conformidad.avisos` |
| `resources/views/partials/aviso-despacho-auxiliar.blade.php` | UI + poll + sonido |
| `resources/views/partials/logo-institucional.blade.php` | Inclusión global |
| `public/song/sound.mp3` | Tono de notificación |
