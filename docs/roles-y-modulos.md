# Roles y módulos (guía operativa)

Fuente de verdad: [`config/modulos_por_rol.php`](../config/modulos_por_rol.php).  
El menú y el middleware `module:*` leen la BD (`modulos` + `role_modulo`). Tras editar la config:

```bash
php artisan modulos:sync-matriz
```

---

## Catálogo de módulos (claves)

| Clave | Nombre en menú | Notas |
|-------|----------------|-------|
| `dashboard` | Dashboard | KPIs |
| `inventario` | Inventario de Lenguas | Réplica local; filtros Adicionales/Salidas con badge |
| `cambios_destinos` | **Destinos y etiquetas** | Destino logístico + marcas **Adicional (ADI.)** y **Salida (SAL.)**. La clave interna no cambió (`cambios_destinos`) para no romper rutas/permisos. |
| `ingresos` | Ingresos de Lenguas | Solo consulta SIRT |
| `despacho` | Despacho de Lenguas | Placas / checklist |
| `desposte` | Lenguas Desposte | Movimiento a planta |
| `historial` | Historial de despacho | Incluye filtro por código de producto |
| `entrega` | Entrega de conformidad | PDF / manifiestos |
| `decomisos` | Decomisos | Algunos roles solo lectura (`decomisos_solo_lectura`) |
| `gestion_usuarios` | Gestión de usuarios | Solo `super_admin` |
| `observatorio` | Observatorio | Solo `super_admin` |

Campos locales en `ingreso_lenguas_locales` (independientes de SIRT):

| Campo | Uso |
|-------|-----|
| `es_adicional` / `es_salida` | Etiquetas ADI. / SAL. La sync **no** las borra. |
| `destino` + `destino_local` | Si se edita el destino en **Destinos y etiquetas**, `destino_local=true` y la sync **no** lo sobrescribe con el destino de SIRT. |

**Ingresos de Lenguas** sigue mostrando el destino de SIRT en vivo (no usa la réplica local). Inventario, despacho, desposte, historial y entrega sí leen el destino local.

---

## Roles asignables

| Rol | Perfil | Módulos (resumen) |
|-----|--------|-------------------|
| `super_admin` | Control total | Todos, incl. usuarios y observatorio; aviso sonoro + toast de despacho nuevo (como auxiliar) |
| `admin` | Administración operativa | Todo lo operativo (sin usuarios/observatorio); decomisos con escritura |
| `supervisor` | Responsable de turno / planta | Operación de piso (inventario, ingresos, despacho, desposte, historial, entrega); **sin** Destinos y etiquetas; **decomisos solo lectura** |
| `gerencia` | Consulta gerencial | Dashboard, inventario, ingresos, historial, decomisos (lectura) — **sin** destinos/etiquetas ni despacho |
| `logistico` / `operador` | Operación de salida | Inventario, despacho, desposte, historial |
| `auxiliar` / `user` | Apoyo | Inventario, historial, entrega, decomisos (lectura). Recibe aviso sonoro + toast al cerrarse un despacho (ver `docs/aviso-despacho-auxiliar.md`) |
| `planillaje` | Planillas | Inventario + decomisos (lectura) |
| `calidad` | Calidad | Inventario + decomisos (escritura) |

### Por qué `supervisor` queda así

Un supervisor de turno necesita **ver stock**, **despachar/despostar**, **consultar ingresos e historial** y **revisar actas**, sin administrar usuarios, observatorio ni **Destinos y etiquetas** (esa edición queda en admin u otros roles operativos). Los decomisos manuales quedan en **solo lectura** para que no se confunda con el rol de calidad (que sí registra).

Si en planta el supervisor también debe registrar decomisos manuales, quítelo de `decomisos_solo_lectura` en la config y vuelva a sincronizar la matriz.

---

## Checklist servidor tras cambiar roles

```bash
git pull
php artisan modulos:sync-matriz
php artisan config:clear
php artisan view:clear
```
