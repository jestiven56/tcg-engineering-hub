# Notas de Arquitectura

## Decisiones principales

### 1. Separación de responsabilidades

Las reglas de negocio (Gates) viven exclusivamente en servicios
de dominio, nunca en controladores:

- `ArtifactGateService` → maneja los 4 gates del framework
- `ModuleValidationService` → valida los requisitos de un módulo
- `AuditService` → centraliza el registro de eventos

Los controladores solo coordinan: reciben el request,
llaman al servicio, devuelven la respuesta.

### 2. RBAC con Policies

Se usaron Laravel Policies en lugar de checks manuales en
controladores. Cada modelo tiene su policy:

- `ProjectPolicy` → admin, pm
- `ArtifactPolicy` → admin, pm
- `ModulePolicy` → admin, pm, engineer

Esto permite que el sistema de autorización sea extensible
sin tocar los controladores.

### 3. Audit Trail con diffs

Cada evento de auditoría guarda `before_json` y `after_json`
para mostrar exactamente qué cambió. Esto fue una decisión
deliberada sobre guardar solo el estado final, ya que
permite reconstruir la historia completa de cualquier entidad.

### 4. Artefactos auto-creados

Al crear un proyecto se crean automáticamente los 7 artefactos
con status `not_started`. Esto garantiza que el framework
siempre esté completo y los Gates puedan evaluarse desde
el primer momento.

### 5. content_json flexible

El contenido de los artefactos se almacena como JSON
estructurado. Esto permite evolucionar los campos sin
nuevas migraciones, manteniendo validación en el backend
y formularios específicos por tipo en el frontend.

### 6. Enums en PHP 8.1

Se usaron enums nativos de PHP 8.1 para status y tipos,
lo que elimina strings hardcodeados y da autocompletado
en el IDE.

---

## Lo que mejoraría en una siguiente iteración

### Corto plazo
- **Notificaciones**: avisar al owner de un artefacto cuando
  cambia su estado

### Mediano plazo
- **Dashboard**: métricas globales (proyectos por estado,
  tiempo promedio por fase, etc.)

---

## Estructura final del repositorio

Te recomiendo esta estructura para el repo:
```
tcg-engineering-hub/
├── backend/               ← Laravel
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── tests/
│   └── ...
├── frontend/              ← Vue 3
│   ├── src/
│   │   ├── api/
│   │   ├── components/
│   │   ├── router/
│   │   ├── stores/
│   │   └── views/
│   └── ...
├── README.md
└── ARCHITECTURE.md
```
