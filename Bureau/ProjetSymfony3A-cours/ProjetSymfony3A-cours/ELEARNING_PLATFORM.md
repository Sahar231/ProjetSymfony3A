# E-Learning Platform - Implementation Guide

## Overview

This is a complete Symfony 6.4-based e-learning platform with role-based access control for **Admin**, **Instructor**, and **Student** users. The system supports course and chapter management with an approval workflow.

## Database Setup

### Database Configuration
- **Database**: `eudverse` (MySQL)
- **Location**: `.env` file
- **Connection String**: `mysql://root:@127.0.0.1:3306/eudverse?serverVersion=8.0.32&charset=utf8mb4`

### Tables
1. **cours** - Course management
   - Columns: id, title, description, category, status, createdAt, updatedAt, createdBy, approvedBy, approvedAt

2. **chapitre** - Chapter management
   - Columns: id, title, content (JSON), createdAt, updatedAt, cours_id

## Entities

### Cours Entity
- **File**: `src/Entity/Cours.php`
- **Key Fields**:
  - `status`: PENDING, APPROVED, REFUSED
  - `createdBy`: Instructor identifier
  - `approvedBy`: Admin identifier
  - `approvedAt`: Approval timestamp
- **Relations**: OneToMany with Chapitre

### Chapitre Entity
- **File**: `src/Entity/Chapitre.php`
- **Key Fields**:
  - `content`: JSON array from Editor.js
- **Method**: `getContentAsHtml()` - Converts Editor.js JSON to HTML
- **Relations**: ManyToOne with Cours

## Controllers

### Admin Controllers
#### AdminCoursController (`src/Controller/Admin/AdminCoursController.php`)
- **Routes**:
  - `GET /admin/cours` - List all courses (index)
  - `GET|POST /admin/cours/create` - Create new course
  - `GET|POST /admin/cours/{id}/edit` - Edit course
  - `GET /admin/cours/{id}` - View course details
  - `POST /admin/cours/{id}/delete` - Delete course
  - `POST /admin/cours/{id}/approve` - Approve course
  - `POST /admin/cours/{id}/refuse` - Refuse course

- **Features**:
  - See ALL courses (PENDING, APPROVED, REFUSED)
  - Edit/Delete any course
  - Approve or refuse courses with one click
  - Automatic status change propagation

#### AdminChapitreController (`src/Controller/Admin/AdminChapitreController.php`)
- **Routes**:
  - `GET /admin/chapitre/cours/{coursId}` - List chapters of a course
  - `GET|POST /admin/chapitre/create/{coursId}` - Create chapter
  - `GET|POST /admin/chapitre/{id}/edit` - Edit chapter
  - `GET /admin/chapitre/{id}` - View chapter
  - `POST /admin/chapitre/{id}/delete` - Delete chapter

### Instructor Controllers
#### InstructorCoursController (`src/Controller/Instructor/InstructorCoursController.php`)
- **Routes**:
  - `GET /instructor/cours` - Dashboard (own + approved courses)
  - `GET|POST /instructor/cours/create` - Create course (auto-PENDING)
  - `GET|POST /instructor/cours/{id}/edit` - Edit own course
  - `GET /instructor/cours/{id}` - View course
  - `POST /instructor/cours/{id}/delete` - Delete own course

- **Features**:
  - See own courses (all statuses)
  - See other approved courses (read-only)
  - Create courses → auto-set to PENDING
  - Edit/Delete only own courses
  - Authorization checks for own courses

#### InstructorChapitreController
- **Same chapter management as Admin**, but with authorization checks
- Can only manage chapters of own courses
- Cannot create chapters for other instructors' courses

### Student Controllers
#### StudentCoursController (`src/Controller/Student/StudentCoursController.php`)
- **Routes**:
  - `GET /student/cours` - List approved courses only
  - `GET /student/cours/{id}` - View approved course (read-only)

- **Features**:
  - Read-only access
  - Only see APPROVED courses
  - No create/edit/delete permissions

#### StudentChapitreController
- **Routes**:
  - `GET /student/chapitre/cours/{coursId}` - List chapters of approved course
  - `GET /student/chapitre/{id}` - View chapter content (read-only)

## Templates

### Admin Templates
- `templates/admin/cours/index.html.twig` - Course list with actions (Approve, Refuse, Edit, Delete)
- `templates/admin/cours/form.html.twig` - Create/Edit course form
- `templates/admin/cours/show.html.twig` - View course details
- `templates/admin/chapitre/index.html.twig` - Chapter list for course
- `templates/admin/chapitre/form.html.twig` - Create/Edit chapter with Editor.js
- `templates/admin/chapitre/show.html.twig` - View chapter with styled content

### Instructor Templates
- `templates/instructor/cours/index.html.twig` - Dashboard with two sections: My Courses + Other Approved
- `templates/instructor/cours/form.html.twig` - Create/Edit course form
- `templates/instructor/cours/show.html.twig` - View course details
- `templates/instructor/chapitre/index.html.twig` - Chapter list with conditional edit/delete
- `templates/instructor/chapitre/form.html.twig` - Create/Edit chapter with Editor.js
- `templates/instructor/chapitre/show.html.twig` - View chapter content

### Student Templates
- `templates/student/cours/index.html.twig` - Card-based list of approved courses
- `templates/student/cours/show.html.twig` - View course details (read-only)
- `templates/student/chapitre/index.html.twig` - Chapter list (read-only)
- `templates/student/chapitre/show.html.twig` - View chapter content (read-only)

## Editor.js Integration

### Features
- Rich text editing for chapter content
- Supports: Paragraph, Heading, List, Code, Image
- Content saved as JSON in database
- Rendered as HTML when displayed to students

### Implementation
1. **Form Template** (`admin/chapitre/form.html.twig`):
   - Editor.js CDN included
   - Textarea with hidden `content` field stores JSON
   - Form submission saves Editor.js data

2. **Display Template** (`admin/chapitre/show.html.twig`):
   - Uses `chapitre.contentAsHtml` to render stored JSON
   - CSS styling for readable content

### Example Workflow
```javascript
// Editor saves data as:
{
  "blocks": [
    { "type": "paragraph", "data": { "text": "Sample text" } },
    { "type": "heading", "data": { "level": 2, "text": "Title" } },
    { "type": "list", "data": { "style": "unordered", "items": ["Item 1"] } }
  ]
}

// PHP renders as:
<p>Sample text</p>
<h2>Title</h2>
<ul><li>Item 1</li></ul>
```

## Role-Based Access Control

### Current Implementation
- Uses placeholder instructor names: `instructor_user1`, etc.
- Ready for integration with Symfony Security (User entity)

### Permissions Matrix

| Action | Admin | Instructor | Student |
|--------|-------|-----------|---------|
| See all courses | ✓ | ✗ | ✗ |
| See own courses | ✓ | ✓ | ✗ |
| See approved courses | ✓ | ✓ (other) | ✓ |
| Create course | ✓ | ✓ | ✗ |
| Edit own course | ✓ | ✓ | ✗ |
| Edit other course | ✓ | ✗ | ✗ |
| Delete own course | ✓ | ✓ | ✗ |
| Delete other course | ✓ | ✗ | ✗ |
| Approve/Refuse | ✓ | ✗ | ✗ |
| Create chapter | ✓ | ✓ (own) | ✗ |
| Edit chapter | ✓ | ✓ (own) | ✗ |
| Delete chapter | ✓ | ✓ (own) | ✗ |
| View chapter | ✓ | ✓ (if approved or own) | ✓ (if approved) |

## Course Status Workflow

```
PENDING → [Admin reviews] → APPROVED
       ↓                      (Visible to Students & Instructors)
       → REFUSED
           (Not visible to Students)
```

### Automatic Status Changes
1. **Instructor creates course** → Status = PENDING
2. **Admin approves course** → Status = APPROVED, `approvedBy` set, `approvedAt` set
3. **Admin refuses course** → Status = REFUSED, `approvedBy` set, `approvedAt` set

## Form Fields & Actions

### Course Form Fields
- Title (required, string)
- Description (optional, textarea)
- Category (optional, string)
- Status (dropdown: PENDING, APPROVED, REFUSED - Admin only)

### Chapter Form
- Title (required, string)
- Content (Editor.js - required)

### Action Buttons
- **Ajouter** (Add) - Create new item
- **Modifier** (Edit) - Update existing
- **Supprimer** (Delete) - Remove with confirmation
- **Voir en détail** (View) - See full details
- **Approuver** (Approve) - Admin only
- **Refuser** (Refuse) - Admin only

## Migration & Database

### Migration File
- **Location**: `migrations/Version20260210171149.php`
- **Status**: Already executed
- **Tables Created**:
  - `cours` (49 columns)
  - `chapitre` (6 columns)

### Commands
```bash
# Create database
php bin/console doctrine:database:create --if-not-exists

# Generate migrations
php bin/console make:migration

# Execute migrations
php bin/console doctrine:migrations:migrate --no-interaction
```

## Integration With Existing Templates

- All pages extend `base.html.twig`
- Reuse existing Bootstrap styling
- All UI uses existing CSS classes from `assets/css/style.css`
- Bootstrap Icons for action buttons

## Future Enhancements

1. **Symfony Security Integration**
   - Replace placeholder instructor names with actual User entity
   - Use `@IsGranted` annotations
   - Password-based authentication

2. **Advanced Features**
   - Chapter ordering
   - Course prerequisites
   - Student progress tracking
   - Comments/Discussion on chapters
   - File uploads for chapters
   - Quiz assessment

3. **Admin Dashboard**
   - Statistics: total courses, pending approvals, etc.
   - Recent activity log
   - User management

## Testing the Platform

### Admin Access
- Visit: `/admin/cours`
- See all courses with approve/refuse buttons
- Create new courses with status selection

### Instructor Access
- Visit: `/instructor/cours`
- See own courses + approved courses from others
- Create courses (auto-PENDING)
- Manage only own courses

### Student Access
- Visit: `/student/cours`
- See only APPROVED courses
- Read-only access to courses and chapters
- View chapter content with Editor.js rendering

## Key Classes & Methods

### Repositories
- `CoursRepository::findApproved()` - Get approved courses
- `CoursRepository::findByInstructor()` - Get instructor's courses
- `CoursRepository::findInstructorVisibleCourses()` - Get instructor dashboard courses
- `ChapitreRepository::findByCours()` - Get chapters for a course

### Entity Methods
- `Cours::isApproved()` - Check if approved
- `Cours::isPending()` - Check if pending
- `Chapitre::getContentAsHtml()` - Convert JSON to HTML

## Security Notes

- CSRF tokens used on all POST requests
- Authorization checks in controllers
- Edit/Delete operations check ownership before allowing

## Notes

- Placeholder instructor identifier: `instructor_user1` (replace with actual user ID when integrating with Symfony Security)
- Admin identifier: `admin` (replace with actual user ID)
- Course approval timestamp automatically set on approval/refusal
