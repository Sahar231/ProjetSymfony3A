# 🗺️ Complete Routes Documentation

**Last Updated:** February 10, 2025  
**Total Routes:** 60+  
**Framework:** Symfony 6.4 (Attribute-based Routing)

---

## 📋 Quick Navigation

1. [Admin Routes](#1-admin-routes)
2. [Instructor Routes](#2-instructor-routes)
3. [Student Routes](#3-student-routes)
4. [Home Routes](#4-home-routes)
5. [Other Routes](#5-other-routes)

---

## 1️⃣ ADMIN ROUTES

### Admin Courses (`/admin/cours`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/admin/cours` | `admin_cours_index` | List all courses |
| GET/POST | `/admin/cours/create` | `admin_cours_create` | Create new course |
| GET/POST | `/admin/cours/{id}/edit` | `admin_cours_edit` | Edit course |
| GET | `/admin/cours/{id}` | `admin_cours_show` | View course details |
| POST | `/admin/cours/{id}/delete` | `admin_cours_delete` | Delete course |
| POST | `/admin/cours/{id}/approve` | `admin_cours_approve` | Approve course (PENDING → APPROVED) |
| POST | `/admin/cours/{id}/refuse` | `admin_cours_refuse` | Refuse course (PENDING → REFUSED) |

### Admin Chapters (`/admin/chapitre`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/admin/chapitre/cours/{coursId}` | `admin_chapitre_index` | List chapters for a course |
| GET/POST | `/admin/chapitre/create/{coursId}` | `admin_chapitre_create` | Create new chapter |
| GET/POST | `/admin/chapitre/{id}/edit` | `admin_chapitre_edit` | Edit chapter |
| GET | `/admin/chapitre/{id}` | `admin_chapitre_show` | View chapter details |
| POST | `/admin/chapitre/{id}/delete` | `admin_chapitre_delete` | Delete chapter |

### Admin Dashboard

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/admin/dashboard` | `admin_dashboard` | Main admin dashboard |
| GET | `/admin/courses` | `admin_courses_list` | All courses management |

---

## 2️⃣ INSTRUCTOR ROUTES

### Instructor Main Routes (`/instructor`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/instructor/dashboard` | `instructor_dashboard` | Instructor dashboard |
| GET | `/instructor/list` | `instructor_list` | Instructors list |
| GET | `/instructor/create-course` | `instructor_create_course` | Create course page |
| GET | `/instructor/manage-courses` | `instructor_manage_courses` | Manage courses |
| GET | `/instructor/quiz` | `instructor_quiz` | Quiz management |
| GET | `/instructor/reviews` | `instructor_reviews` | View reviews |
| GET | `/instructor/earnings` | `instructor_earnings` | Earnings tracking |
| GET | `/instructor/payout` | `instructor_payout` | Payout info |
| GET | `/instructor/orders` | `instructor_orders` | Orders list |
| GET | `/instructor/students` | `instructor_students` | Students list |
| GET | `/instructor/edit-profile` | `instructor_edit_profile` | Edit profile |
| GET | `/instructor/settings` | `instructor_settings` | Settings |
| GET | `/instructor/delete-account` | `instructor_delete_account` | Delete account |
| GET | `/instructor/{id}` | `instructor_detail` | Instructor profile detail |

### Instructor Courses (`/instructor/cours`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/instructor/cours` | `instructor_cours_index` | List my courses + approved others |
| GET/POST | `/instructor/cours/create` | `instructor_cours_create` | Create new course |
| GET/POST | `/instructor/cours/{id}/edit` | `instructor_cours_edit` | Edit course |
| GET | `/instructor/cours/{id}` | `instructor_cours_show` | View course details |
| POST | `/instructor/cours/{id}/delete` | `instructor_cours_delete` | Delete course |

### Instructor Chapters (`/instructor/chapitre`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/instructor/chapitre/cours/{coursId}` | `instructor_chapitre_index` | List chapters for course |
| GET/POST | `/instructor/chapitre/create/{coursId}` | `instructor_chapitre_create` | Create new chapter |
| GET/POST | `/instructor/chapitre/{id}/edit` | `instructor_chapitre_edit` | Edit chapter |
| GET | `/instructor/chapitre/{id}` | `instructor_chapitre_show` | View chapter |
| POST | `/instructor/chapitre/{id}/delete` | `instructor_chapitre_delete` | Delete chapter |

### Instructor Quiz (`/instructor/quiz`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/instructor/quiz` | `instructor_quiz_list` | List quizzes |
| GET/POST | `/instructor/quiz/create` | `instructor_quiz_create` | Create quiz |

### Instructor Formations (`/instructor/formations`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/instructor/formations` | `instructor_formation_list` | List formations |
| GET/POST | `/instructor/formations/create` | `instructor_formation_create` | Create formation |

### Instructor Clubs (`/instructor/clubs`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/instructor/clubs` | `instructor_club_list` | List clubs |
| GET/POST | `/instructor/clubs/create` | `instructor_club_create` | Create club |

---

## 3️⃣ STUDENT ROUTES

### Student Main Routes (`/student`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/student/dashboard` | `student_dashboard` | Student dashboard |
| GET | `/student/courses` | `student_courses` | My courses |
| GET | `/student/course-resume/{id}` | `student_course_resume` | Course resume |
| GET | `/student/quiz` | `student_quiz` | My quizzes |
| GET | `/student/bookmarks` | `student_bookmarks` | Bookmarks |
| GET | `/student/subscription` | `student_subscription` | Subscription info |
| GET | `/student/payment-info` | `student_payment_info` | Payment information |

### Student Courses (`/student/cours`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/student/cours` | `student_cours_index` | List approved courses |
| GET | `/student/cours/{id}` | `student_cours_show` | View course details |

### Student Chapters (`/student/chapitre`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/student/chapitre/cours/{coursId}` | `student_chapitre_index` | List chapters for course |
| GET | `/student/chapitre/{id}` | `student_chapitre_show` | View chapter |

### Student Quiz (`/student/quiz`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/student/quiz` | `student_quiz_list` | List quizzes |
| GET/POST | `/student/quiz/start` | `student_quiz_start` | Start quiz |

### Student Formations (`/student/formations`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/student/formations` | `student_formation_list` | List formations |
| GET/POST | `/student/formations/enroll` | `student_formation_enroll` | Enroll in formation |

### Student Clubs (`/student/clubs`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/student/clubs` | `student_club_list` | List clubs |
| GET/POST | `/student/clubs/join` | `student_club_join` | Join club |

---

## 4️⃣ HOME ROUTES

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/` | `app_home` | Home page |
| GET | `/about` | `app_about` | About page |
| GET | `/contact` | `app_contact` | Contact page |
| GET | `/faq` | `app_faq` | FAQ page |
| GET | `/pricing` | `app_pricing` | Pricing page |

---

## 5️⃣ OTHER ROUTES

### Quiz Routes (`/quiz`)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| GET | `/quiz/` | `quiz_list` | List all quizzes |
| GET | `/quiz/detail/{id}` | `quiz_detail` | Quiz details |
| GET | `/quiz/added` | `quiz_added` | Added quizzes |

---

## 📝 Route Usage Examples in Templates

### Twig Path Function
```twig
{# Generate URL for a route #}
<a href="{{ path('admin_cours_index') }}">List Courses</a>
<a href="{{ path('admin_cours_show', {id: cours.id}) }}">View</a>
<a href="{{ path('instructor_chapitre_create', {coursId: cours.id}) }}">Add Chapter</a>
```

### PHP Controller Usage
```php
// Generate route URL in controller
$url = $this->generateUrl('admin_cours_index');

// Redirect to route
return $this->redirectToRoute('instructor_cours_index');

// Forward to route
return $this->forward('App\Controller\Admin\AdminCoursController::index');
```

---

## 🔒 CSRF Protection

Routes with `POST` method require CSRF token:
```twig
<form method="POST" action="{{ path('admin_cours_delete', {id: cours.id}) }}">
    <input type="hidden" name="_token" value="{{ csrf_token('delete' ~ cours.id) }}">
    <button type="submit">Delete</button>
</form>
```

---

## 📊 Route Statistics

| Category | Count |
|----------|-------|
| Admin Routes | 12 |
| Instructor Routes | 35 |
| Student Routes | 18 |
| Home Routes | 5 |
| Other Routes | 3 |
| **Total** | **73** |

---

## 🔄 Access Control

### By Role

| Role | Main Prefix | Base URL |
|------|------------|----------|
| Admin | `/admin/` | `/admin/dashboard` |
| Instructor | `/instructor/` | `/instructor/dashboard` |
| Student | `/student/` | `/student/dashboard` |
| Public | `/` | `/` |

---

## ✅ Status Methods

### Course Status Workflow
```
Creation (PENDING)
    ↓
Admin Review: /admin/cours/{id}/approve → APPROVED
         or: /admin/cours/{id}/refuse → REFUSED
```

---

## 🎯 Most Used Routes

1. `admin_cours_index` - Admin course management hub
2. `instructor_cours_index` - Instructor dashboard
3. `student_cours_index` - Student learning hub
4. `student_chapitre_index` - View course chapters
5. `instructor_chapitre_create` - Create course content

---

## 📞 Questions?

Refer to:
- **Controllers Location:** `src/Controller/`
- **Templates Location:** `templates/`
- **Config:** `config/routes.yaml` (auto-discovery enabled)
- **Entity Definitions:** `src/Entity/`

---

**Generated:** February 10, 2025  
**Framework Version:** Symfony 6.4  
**Routing Style:** Attribute-based (Modern Symfony)
