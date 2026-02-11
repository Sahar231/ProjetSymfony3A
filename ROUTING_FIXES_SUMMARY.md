# Routing Fixes Summary

## Issues Fixed

### 1. **Instructor Formation Routes - Route Order Issue**
**Problem:** The parameterized route `/{id}` was matching before the specific `/create` route, causing "create" to be treated as an ID parameter.

**Error:** 
```
App\Controller\Instructor\FormationController::show(): 
Argument #1 ($id) must be of type int, string given
```

**Solution:** Reordered routes in [Instructor FormationController](src/Controller/Instructor/FormationController.php):
- Moved `/create` route BEFORE `/{id}` route
- Ensured correct Symfony route precedence

**Current Correct Order:**
1. `#[Route('', ...)]` → list all formations
2. `#[Route('/create', ...)]` → create form (most specific)
3. `#[Route('/{id}', ...)]` → show single formation (parameterized)
4. `#[Route('/{id}/edit', ...)]` → edit formation
5. `#[Route('/{id}/archive', ...)]` → archive formation
6. `#[Route('/{id}/unarchive', ...)]` → unarchive formation

### 2. **Admin Formation Routes - Duplicate/Incorrect Route**
**Problem:** Had duplicate route `/admin/create/formations` that was incorrect.

**Solution:** Removed the duplicate route in [Admin FormationController](src/Controller/Admin/FormationController.php):
- Kept only `#[Route('/create', ...)]` 
- Route resolves to `/admin/formations/create` (correct)

### 3. **Template Route Reference Fix**
**Problem:** [templates/main/about.html.twig](templates/main/about.html.twig) referenced non-existent route `instructor_formation`

**Solution:** Updated to use correct route name:
```twig
<!-- Before -->
{{ path('instructor_formation') }}

<!-- After -->
{{ path('instructor_formation_list') }}
```

## Verified Routes

### Instructor Formation Routes
```
instructor_formation_list       GET      /instructor/formations
instructor_formation_create     GET|POST /instructor/formations/create
instructor_formation_show       GET      /instructor/formations/{id}
instructor_formation_edit       GET|POST /instructor/formations/{id}/edit
instructor_formation_archive    POST     /instructor/formations/{id}/archive
instructor_formation_unarchive  POST     /instructor/formations/{id}/unarchive
```

### Admin Formation Routes
```
admin_formation_list       GET      /admin/formations
admin_formation_create     GET|POST /admin/formations/create
admin_formation_show       GET      /admin/formations/{id}/show
admin_formation_edit       GET|POST /admin/formations/{id}/edit
admin_formation_delete     POST     /admin/formations/{id}/delete
admin_formation_archive    POST     /admin/formations/{id}/archive
admin_formation_unarchive  POST     /admin/formations/{id}/unarchive
```

### Student Formation Routes
```
student_formations        GET /student/formations
student_formation_view    GET /student/formations/{id}
```

## Access Control

All routes are protected with appropriate role checks:

- **Instructor Routes:** `#[IsGranted('ROLE_INSTRUCTOR')]`
  - Instructors can only:
    - Create their own formations
    - View/edit/archive their own formations
    
- **Admin Routes:** `#[IsGranted('ROLE_ADMIN')]`
  - Admins can:
    - Create formations (auto-approved)
    - View/edit/delete/archive all formations

- **Student Routes:** `#[IsGranted('ROLE_STUDENT')]`
  - Students can:
    - View list of approved formations they're enrolled in and available formations
    - View formation details

## Files Modified

1. [src/Controller/Instructor/FormationController.php](src/Controller/Instructor/FormationController.php)
   - Reordered methods to fix route precedence
   - Added `list()` and `show()` methods in correct position

2. [src/Controller/Admin/FormationController.php](src/Controller/Admin/FormationController.php)
   - Removed duplicate `/admin/create/formations` route

3. [templates/main/about.html.twig](templates/main/about.html.twig)
   - Fixed broken route reference from `instructor_formation` to `instructor_formation_list`

## Testing

The following routes should now work correctly:

✅ `/instructor/formations/create` - Create new formation
✅ `/instructor/formations/{id}` - View formation details
✅ `/instructor/formations/{id}/edit` - Edit formation
✅ `/admin/formations/create` - Admin create formation
✅ `/admin/formations/{id}/edit` - Admin edit formation
✅ `/student/formations` - View available formations

## Key Takeaway

**Symfony Route Precedence Rule:** More specific routes must be defined BEFORE less specific (parameterized) routes. The router matches routes in the order they're encountered in the code, so:

```
Good Order:
#[Route('/create', ...)]     ← More specific
#[Route('/{id}', ...)]       ← Less specific

Bad Order:
#[Route('/{id}', ...)]       ← Matches '/create' as {id}!
#[Route('/create', ...)]     ← Never reached
```
