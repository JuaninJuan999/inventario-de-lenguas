# Inventario de Lenguas

<p align="center">
  <strong>Colbeef®</strong> · control operativo de lenguas<br/>
  Réplica local · despacho · decomisos · conformidad · usabilidad
</p>

<p align="center">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" />
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat-square&logo=php&logoColor=white" />
  <img alt="Stack" src="https://img.shields.io/badge/Stack-SIRT%20%2F%20Colbeef-7CE8AD?style=flat-square" />
  <img alt="UI" src="https://img.shields.io/badge/UI-verde%20%2B%20rosa-F9DFF8?style=flat-square" />
</p>

---

Sistema web para el **inventario operativo de lenguas**: sincroniza desde SIRT/Colbeef, alista despachos por placa, registra decomisos, genera actas de conformidad y deja a superadmin un **Observatorio** de uso real.

> Desarrollado por **Juan Carreño** — Auxiliar TIC · 2026 · Colbeef®

---

## Qué resuelve

| Necesidad | Cómo lo cubre |
|-----------|----------------|
| Stock fiable en planta | Réplica local + sync programada / manual desde SIRT |
| Despacho ordenado | Checklist por placa Colbeef + validación en inventario |
| Alertas de vida útil | Vencen en **4 días** desde fecha de registro · rojo / naranja en listado |
| Trazabilidad de salidas | Historial, entrega de conformidad (PDF) y manifiestos |
| Control de acceso | Roles (`super`, `admin`, `gerencia`, `logistico`, `auxiliar`, `planillaje`, `calidad`); aliases `super_admin`/`operador`/`user` |
| Usabilidad (superadmin) | Módulo **Observatorio**: quién entra, a qué módulo y gráficas en el tiempo |

```text
  ┌─────────────┐     sync      ┌──────────────────┐
  │  SIRT /     │ ────────────► │  Réplica local   │
  │  Colbeef    │               │  (inventario)    │
  └─────────────┘               └────────┬─────────┘
                                         │
          ┌──────────────────────────────┼──────────────────────────────┐
          ▼                              ▼                              ▼
   Inventario / Destinos          Despacho / Desposte            Decomisos / Acta
   Dashboard KPIs                 Checklist placas               Conformidad PDF
                                         │
                                         ▼
                                  Observatorio (uso)
```

---

## Módulos

| Módulo | Para qué |
|--------|----------|
| **Dashboard** | KPIs de stock, vencidas, por vencer, despachos y decomisos |
| **Inventario** | Listado, Excel, sync, filtros *hoy / todos / por vencer / vencidas* |
| **Cambios de destinos** | Edición masiva + marca **Adicional** |
| **Ingresos** | Consulta en vivo a SIRT (sin guardar en local) |
| **Despacho** | Placas pendientes → checklist → terminar despacho |
| **Desposte** | Salida a planta de desposte |
| **Historial** | Despachos y movimientos registrados |
| **Entrega de conformidad** | Acta PDF y manifiestos de destino |
| **Decomisos** | Manuales + consulta SIRT |
| **Gestión de usuarios** | Solo **super_admin** |
| **Observatorio** | Solo **super_admin** · usabilidad y gráficas |

La matriz rol → módulo vive en [`config/modulos_por_rol.php`](config/modulos_por_rol.php). Tras cambiarla en un servidor:

```bash
php artisan modulos:sync-matriz
```

---

## Arranque rápido (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configura DB_* y, si aplica, POSTGRES_* / COLBEEF_*
php artisan migrate --force
php artisan modulos:sync-matriz
php artisan db:seed   # si usas seeders de usuarios / placas
php artisan serve
```

Abre el menú, inicia sesión y verifica módulos según el rol.

### Windows / Laragon — sync en segundo plano

```bat
scripts\start-schedule.bat
```

O manualmente:

```bash
php artisan schedule:list
php artisan inventario:sync-lenguas
php artisan schedule:work
```

Documentación de sync para equipo de servidor:  
**[docs/sincronizacion-inventario.md](docs/sincronizacion-inventario.md)**

---

## Despliegue / `git pull` en servidor

Después de traer código nuevo:

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan modulos:sync-matriz
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

> Si no corres **`modulos:sync-matriz`**, módulos nuevos (p. ej. **Observatorio**) no aparecen en el menú: el menú lee la BD local, no solo el repo.

---

## Operación que conviene conocer

### Vida útil
- Vencimiento = `fecha_registro` **+ 4 días** (zona de operación).
- **Por vencer:** 0–1 día restante (naranja).
- **Vencidas:** &lt; 0 días (rojo).
- Botones y KPIs del dashboard llevan directo a esos filtros.

### Despacho — día operativo
- Por defecto solo lista Colbeef del **día de operación**.
- Hasta las **05:00** (configurable) el día operativo sigue siendo el **calendario anterior**, para no vaciar el listado a medianoche.
- Variable: `DESPACHO_PPV_DIA_OPERACION_CORTE=05:00`  
  Ver más en [`.env.example`](.env.example).

### Observatorio
- Registra entradas a módulos (con throttle) y muestra KPIs, heatmap, top usuarios, feed y **gráficas en el tiempo**.

---

## Stack

- **PHP 8.3+** · **Laravel 13**
- BD app: MySQL / PostgreSQL / SQLite (según `.env`)
- Origen corporativo: PostgreSQL SIRT / Colbeef (`POSTGRES_*`, `COLBEEF_*`)
- PDF: Dompdf · Excel: writer propio del proyecto

---

## Estructura útil

```text
app/
  Http/Controllers/     # inventario, despacho, decomisos, observatorio…
  Services/             # sync inventario, Colbeef PPV, matriz de módulos
  Support/              # vencimiento, matching códigos, parsers
config/
  modulos_por_rol.php   # matriz de permisos
  despacho.php          # filtros PPV / día operativo
docs/
  sincronizacion-inventario.md
resources/views/        # UI verde / rosa del sistema
scripts/
  start-schedule.bat
```

---

## Créditos

**Colbeef®** · 2026  
Desarrollado por **Juan Carreño** — Auxiliar TIC

---

<p align="center">
  <sub>Hecho para planta · pensado para turno · listo para servidor</sub>
</p>
