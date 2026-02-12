# Courses & Chapters Routes Reference

## Admin Routes
```
Admin Course Management:
GET    /admin/course                      → admin_course_list        (List all courses)
GET    /admin/course/add                  → admin_course_add         (Create form)
POST   /admin/course/add                  → admin_course_add         (Create course)
GET    /admin/course/{id}                 → admin_course_show        (View details)
GET    /admin/course/{id}/edit            → admin_course_edit        (Edit form)
POST   /admin/course/{id}/edit            → admin_course_edit        (Update course)
POST   /admin/course/{id}/delete          → admin_course_delete      (Delete course)
POST   /admin/course/{id}/approve         → admin_course_approve     (Approve pending)
POST   /admin/course/{id}/refuse          → admin_course_refuse      (Refuse pending)
GET    /admin/course/approvals/all        → admin_course_approvals   (Approval page)
```

## Instructor Routes
```
Instructor Course Management:
GET    /instructor/course                 → instructor_course_list    (Own + Approved)
GET    /instructor/course/add             → instructor_course_add     (Create form)
POST   /instructor/course/add             → instructor_course_add     (Create course)
GET    /instructor/course/{id}            → instructor_course_show    (View course)
GET    /instructor/course/{id}/edit       → instructor_course_edit    (Edit form)
POST   /instructor/course/{id}/edit       → instructor_course_edit    (Update course)
POST   /instructor/course/{id}/delete     → instructor_course_delete  (Delete course)
```

## Student Routes
```
Student Course Browsing:
GET    /student/course                    → student_course_list       (All approved)
GET    /student/course/{id}               → student_course_show       (View details)
```

## Query Parameters
```
Approval page status filter:
?status=pending   → Show pending courses (awaiting approval)
?status=approved  → Show approved courses (visible to students)
?status=refused   → Show refused courses (hidden from students)
```

## URL Patterns with Examples

### Admin Creating Course
```
/admin/course/add
/admin/course/1
/admin/course/1/edit
/admin/course/1/delete
/admin/course/1/approve
/admin/course/1/refuse
/admin/course/approvals/all?status=pending
```

### Instructor Managing Course
```
/instructor/course
/instructor/course/add
/instructor/course/5
/instructor/course/5/edit
/instructor/course/5/delete
```

### Student Browsing Course
```
/student/course
/student/course/2
```

## Form Method Summary
- **GET**: Display forms and listing pages
- **POST**: Form submissions (create, edit, delete, approve, refuse)

## CSRF Token Requirements
All POST routes require CSRF token. Form templates use:
```twig
<input type="hidden" name="_token" value="{{ csrf_token('token_name') }}">
```

## Access Control
- **Admin Routes**: Require `ROLE_ADMIN`
- **Instructor Routes**: Require `ROLE_INSTRUCTOR`
- **Student Routes**: Require `ROLE_STUDENT`

## Breadcrumb Navigation
All pages include breadcrumb navigation:
- Admin: Home > Admin Dashboard > [Current Page]
- Instructor: Home > Instructor Dashboard > [Current Page]
- Student: Home > Student Dashboard > [Current Page]

## Quick Dashboard Links
**Admin Dashboard Sidebar**:
- All Courses → `/admin/course` 
- Create Course → `/admin/course/add`
- Courses Approval (Pending) → `/admin/course/approvals/all?status=pending`
- Courses Approval (Approved) → `/admin/course/approvals/all?status=approved`
- Courses Approval (Refused) → `/admin/course/approvals/all?status=refused`
