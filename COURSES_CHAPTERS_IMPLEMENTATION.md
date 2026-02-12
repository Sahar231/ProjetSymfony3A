# Courses & Chapters System Implementation

## Overview
Complete course and chapter management system with role-based access control (Admin, Instructor, Student).

## Entities Created

### Cours (Course)
**Location**: `src/Entity/Cours.php`
- **Properties**:
  - `id` (Primary Key)
  - `title` (255 chars, required, uppercase first letter)
  - `description` (TEXT, required, uppercase first letter, 10-5000 chars)
  - `category` (100 chars, optional)
  - `status` (PENDING/APPROVED/REFUSED, default PENDING)
  - `createdAt` (DATETIME_IMMUTABLE)
  - `updatedAt` (DATETIME_IMMUTABLE)
  
- **Relationships**:
  - ManyToOne: `creator` (User who created the course)
  - OneToMany: `chapitres` (Collection of chapters with cascade delete)

- **Status Constants**:
  - `STATUS_PENDING = 'pending'`
  - `STATUS_APPROVED = 'approved'`
  - `STATUS_REFUSED = 'refused'`

- **Validators**:
  - Title: NotBlank, Length(3-255), Regex(/^[A-Z]/)
  - Description: NotBlank, Length(10-5000), Regex(/^[A-Z]/)
  - Category: NotBlank

- **Lifecycle Callbacks**:
  - `__construct()`: Initializes timestamps, status = PENDING, chapitres collection
  - `PreUpdate`: Updates `updatedAt` timestamp

- **Helper Methods**:
  - `isApproved()`, `isPending()`, `isRefused()`
  - `approve()`, `refuse()` (change status)
  - `addChapitre()`, `removeChapitre()` (collection management)

### Chapitre (Chapter)
**Location**: `src/Entity/Chapitre.php`
- **Properties**:
  - `id` (Primary Key)
  - `title` (255 chars, required, uppercase first letter)
  - `content` (TEXT, required, min 10 chars)
  - `createdAt` (DATETIME_IMMUTABLE)
  - `updatedAt` (DATETIME_IMMUTABLE)

- **Relationships**:
  - ManyToOne: `cours` (Parent course, required)
  - ManyToOne: `creator` (User who created the chapter)

- **Validators**:
  - Title: NotBlank, Length(3-255), Regex(/^[A-Z]/)
  - Content: NotBlank, Length(10-50000), Regex(/^[A-Z]/)

- **Lifecycle Callbacks**:
  - `__construct()`: Initializes timestamps
  - `PreUpdate`: Updates `updatedAt` timestamp

## Repositories

### CoursRepository (`src/Repository/CoursRepository.php`)
Methods:
- `findApproved()` - Get all approved courses
- `findPending()` - Get all pending courses
- `findRefused()` - Get all refused courses
- `findByCreator(User $creator)` - Get courses created by user
- `findApprovedExcludingCreator(User $creator)` - Get approved courses not by user
- `countPending()`, `countApproved()`, `countRefused()` - Count courses by status

### ChapitreRepository (`src/Repository/ChapitreRepository.php`)
Methods:
- `findByCours(Cours $cours)` - Get chapters for a course
- `countByCours(Cours $cours)` - Count chapters in a course
- `findOneByIdAndCours(int $id, Cours $cours)` - Get chapter by ID and course

## Form Types

### CoursType (`src/Form/CoursType.php`)
Fields:
- `title` (TextType) - with placeholder "(start with uppercase)"
- `description` (TextareaType) - 5 rows
- `category` (TextType) - optional
- `chapitres` (CollectionType) - ChapitreType, allow_add, allow_delete

### ChapitreType (`src/Form/ChapitreType.php`)
Fields:
- `title` (TextType) - with placeholder "(start with uppercase)"
- `content` (TextareaType) - 8 rows

## Controllers

### Admin Course Controller (`src/Controller/Admin/CoursController.php`)
Routes: `/admin/course`
- `list()` - GET `/` - Show all courses with filters
- `add()` - GET/POST `/add` - Create new course (auto-approved)
- `show(Cours $cours)` - GET `/{id}` - Course details with chapters
- `edit(Request $request, Cours $cours)` - GET/POST `/{id}/edit` - Edit course
- `delete(Request $request, Cours $cours)` - POST `/{id}/delete` - Delete course
- `approve(Request $request, Cours $cours)` - POST `/{id}/approve` - Approve pending
- `refuse(Request $request, Cours $cours)` - POST `/{id}/refuse` - Refuse pending
- `approvals(Request $request)` - GET `/approvals/all` - Approval management page

**Access Control**: `#[IsGranted('ROLE_ADMIN')]` - Only admins

### Instructor Course Controller (`src/Controller/Instructor/CoursController.php`)
Routes: `/instructor/course`
- `list()` - GET `/` - Own courses (all statuses) + Approved from others (read-only)
- `add()` - GET/POST `/add` - Create course (status = PENDING)
- `show(Cours $cours)` - GET `/{id}` - View own or approved courses
- `edit(Request $request, Cours $cours)` - GET/POST `/{id}/edit` - Edit own only
- `delete(Request $request, Cours $cours)` - POST `/{id}/delete` - Delete own only

**Access Control**: `#[IsGranted('ROLE_INSTRUCTOR')]` - Only instructors
**Authorization**: Check `$cours->getCreator() === $this->getUser()` for edit/delete

### Student Course Controller (`src/Controller/Student/CoursController.php`)
Routes: `/student/course`
- `list()` - GET `/` - All approved courses (card view)
- `show(Cours $cours)` - GET `/{id}` - Approved courses only (read-only)

**Access Control**: `#[IsGranted('ROLE_STUDENT')]` - Only students
**Authorization**: Check `$cours->isApproved()` for access

## Templates

### Admin Templates
- `admin/course/list.html.twig` - Course listing with filters (All/Pending/Approved/Refused)
- `admin/course/add.html.twig` - Create course form
- `admin/course/edit.html.twig` - Edit course form
- `admin/course/show.html.twig` - Course details with chapters and approval buttons
- `admin/course/approvals.html.twig` - Approval management page (filter by status)

### Shared Form Template
- `course/_form.html.twig` - Course form with:
  - Gray helper text under every field explaining constraints
  - Red bold error messages for validation failures
  - Chapter collection management with add/remove buttons
  - Info icons for constraints

### Instructor Templates
- `instructor/course/list.html.twig` - Own courses + Approved others (read-only)
- `instructor/course/add.html.twig` - Create course form (status = PENDING)
- `instructor/course/edit.html.twig` - Edit course form
- `instructor/course/show.html.twig` - Course details (edit/delete for own only)

### Student Templates
- `student/course/list.html.twig` - Card view of all approved courses
- `student/course/show.html.twig` - Course details with expandable chapters accordion

## Database Migration

**File**: `migrations/Version20260211000003.php`

Creates two tables:
1. **cours** table:
   - Columns: id, creator_id, title (255), description (TEXT), category (100), status (50), created_at, updated_at
   - Indexes: status, creator_id, created_at
   - Foreign key: creator_id → user.id

2. **chapitre** table:
   - Columns: id, cours_id, creator_id, title (255), content (TEXT), created_at, updated_at
   - Indexes: cours_id, creator_id
   - Foreign keys: cours_id → cours.id (CASCADE), creator_id → user.id

## Admin Dashboard Integration

**Updated Files**:
- `src/Controller/Admin/DashboardController.php` - Added course data fetching
- `templates/admin/dashboard.html.twig` - Added course quick actions and approval section

**New Variables Passed**:
- `pendingCourses`, `approvedCourses`, `refusedCourses`
- `pendingCoursesCount`, `approvedCoursesCount`, `refusedCoursesCount`

**Quick Actions**:
- All Courses → `admin_course_list`
- Create Course → `admin_course_add`

**Approval Buttons** with badge counts:
- Pending → `admin_course_approvals?status=pending`
- Approved → `admin_course_approvals?status=approved`
- Refused → `admin_course_approvals?status=refused`

## Workflow

### Admin Workflow
1. View all courses (all statuses)
2. Create new course (auto-approved, visible to students immediately)
3. Approve pending courses from instructors
4. Refuse courses (sets status to REFUSED)
5. Edit/Delete any course
6. View course details with chapter previews

### Instructor Workflow
1. Create course (status = PENDING, awaiting admin approval)
2. View own courses in any status
3. View approved courses from other instructors (read-only)
4. Edit own courses
5. Delete own courses
6. Once approved by admin, course becomes visible to students

### Student Workflow
1. View only APPROVED courses
2. Browse courses in card view with descriptions
3. View course details with expandable chapters
4. Read-only access (no editing)

## Status Lifecycle

```
PENDING (Instructor creates)
    ↓
    ├→ APPROVED (Admin approves) → Visible to Students
    └→ REFUSED (Admin refuses) → Hidden from Students
```

## Validation Rules

All fields use same pattern as Quiz module:

### Course Validation
- **Title**: 3-255 chars, must start with uppercase letter (A-Z)
- **Description**: 10-5000 chars, must start with uppercase letter (A-Z)
- **Category**: Required, free text

### Chapter Validation
- **Title**: 3-255 chars, must start with uppercase letter (A-Z)
- **Content**: 10-50000 chars (can be any content)

## Helper Text Display

Form fields include helper text matching Quiz module pattern:
- Gray helper text below each field describing constraints
- Red bold error messages with warning icon when validation fails
- Info icons (`<i class="fas fa-info-circle"></i>`) for additional context

## Key Features

✅ Role-based access control (Admin, Instructor, Student)
✅ Approval workflow for instructor-created courses
✅ Auto-approved courses for admin-created courses
✅ Chapter management within courses
✅ Status-based visibility (only APPROVED visible to students)
✅ Creator-based ownership verification
✅ Comprehensive validation with user-friendly error messages
✅ Admin dashboard integration with quick actions and approval section
✅ Reusable form template following Quiz module pattern
✅ Breadcrumb navigation on all pages

## Testing Checklist

- [ ] Admin can create, edit, delete any course
- [ ] Admin-created courses are auto-approved
- [ ] Admin can approve/refuse pending courses
- [ ] Instructor can create courses (status = PENDING)
- [ ] Instructor can only edit/delete own courses
- [ ] Instructor can view approved courses from others (read-only)
- [ ] Student can only see APPROVED courses
- [ ] Student cannot edit/delete courses
- [ ] Validation error messages appear in red
- [ ] Helper text displays in gray
- [ ] Chapters can be added/removed in forms
- [ ] Breadcrumb navigation works correctly
- [ ] Database migration runs without errors
- [ ] Admin dashboard shows course counts and links
