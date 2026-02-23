# E-Learning Platform - Quick Start Guide

## System Overview

| Role | Dashboard | Can Do |
|------|-----------|--------|
| **Admin** | `/admin/cours` | See ALL courses, Approve/Refuse, Edit/Delete ANY course |
| **Instructor** | `/instructor/cours` | See own courses + approved courses from others, Create courses (auto-PENDING) |
| **Student** | `/student/cours` | See only APPROVED courses (read-only) |

## Setup & Installation

### 1. Database Configuration
The `.env` file is already configured:
```
DATABASE_URL="mysql://root:@127.0.0.1:3306/eudverse?serverVersion=8.0.32&charset=utf8mb4"
```

### 2. Database Tables
Tables are already created via migrations. If needed to reset:
```bash
# Drop and recreate database
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### 3. Start Development Server
```bash
# Using Symfony CLI (if installed)
symfony server:start

# Or using PHP built-in server
php -S localhost:8000 -t public
```

## Accessing the Platform

### URLs
- **Admin Dashboard**: `http://localhost:8000/admin/cours`
- **Instructor Dashboard**: `http://localhost:8000/instructor/cours`
- **Student Dashboard**: `http://localhost:8000/student/cours`

## Admin Workflow

### Create a Course
1. Go to `/admin/cours`
2. Click "Ajouter un Cours" button
3. Fill in:
   - Title (required)
   - Description
   - Category
   - Status (PENDING, APPROVED, or REFUSED)
4. Click "Créer"

### Create a Chapter
1. From `/admin/cours`, click the course (or book icon)
2. Click "Ajouter un Chapitre"
3. Fill in:
   - Title (required)
   - Content using Editor.js (Paragraph, Heading, List, Code, Image)
4. Click "Créer"

### Approve/Refuse a Course
1. Go to `/admin/cours`
2. Find course with PENDING status
3. Click green **✓** (Approve) or red **✗** (Refuse) button
4. Course becomes visible to Students if approved

### Edit/Delete Course
1. Click **✏️** (Edit) to modify course details
2. Click **🗑️** (Delete) to remove course
3. Deleting course also deletes its chapters

## Instructor Workflow

### Create a Course
1. Go to `/instructor/cours`
2. Click "Créer un Cours"
3. Fill in Title, Description, Category (Status is auto-PENDING)
4. Click "Créer"
5. **Wait for Admin approval** to be visible to Students

### Create Chapters
1. From `/instructor/cours`, click your course
2. Click "Gérer les Chapitres"
3. Click "Ajouter un Chapitre"
4. Use Editor.js to write chapter content
5. Click "Créer"

### View Other Courses
- **My Courses**: See all your courses (PENDING/APPROVED/REFUSED)
- **Other Approved Courses**: See courses from other instructors that are APPROVED
- Click course to view, click book icon to see chapters (read-only)

## Student Workflow

### Browse Courses
1. Go to `/student/cours`
2. See card-based list of APPROVED courses only
3. Click "Voir les détails" for course info
4. Click "Consulter les chapitres" to see chapters

### Read Chapter Content
1. Click "Consulter les chapitres" on course
2. See list of chapters
3. Click "Lire" to open chapter content
4. Content rendered from Editor.js JSON (paragraphs, headings, lists, code blocks, images)
5. No edit capability - read-only access

## Editor.js Usage

### Supported Tools
1. **Paragraph**: Regular text content
2. **Heading**: H1-H6 headers (level 2-4 commonly used)
3. **List**: Unordered (bullets) or ordered (numbered) lists
4. **Code**: Code blocks with monospace font
5. **Image**: Image embedding with URL

### Example Chapter Content
```json
{
  "blocks": [
    {
      "type": "heading",
      "data": { "level": 2, "text": "Introduction to Symfony" }
    },
    {
      "type": "paragraph",
      "data": { "text": "Symfony is a PHP framework..." }
    },
    {
      "type": "heading",
      "data": { "level": 3, "text": "Key Features" }
    },
    {
      "type": "list",
      "data": {
        "style": "unordered",
        "items": ["MVC Architecture", "Security Component", "ORM Support"]
      }
    },
    {
      "type": "code",
      "data": { "code": "symfony server:start" }
    }
  ]
}
```

## Database Structure

### COURS Table
```
id (PRIMARY)
title (VARCHAR 255) - Course name
description (TEXT) - Full description
category (VARCHAR 100) - Topic/Subject
status (VARCHAR 50) - PENDING, APPROVED, REFUSED
createdBy (VARCHAR 255) - Instructor identifier
approvedBy (VARCHAR 255) - Admin identifier (NULL if not reviewed)
approvedAt (DATETIME) - When approved/refused (NULL if pending)
createdAt (DATETIME) - Automatically set on creation
updatedAt (DATETIME) - Updated automatically on modification
```

### CHAPITRE Table
```
id (PRIMARY)
title (VARCHAR 255) - Chapter name
content (JSON) - Editor.js data (blocks array)
cours_id (FOREIGN KEY) - Reference to cours
createdAt (DATETIME) - Automatically set
updatedAt (DATETIME) - Automatically updated
```

## API Examples

### Create Course (Admin)
```
POST /admin/cours/create
Parameters:
  - title: "Python Fundamentals"
  - description: "Learn Python basics"
  - category: "Programming"
  - status: "PENDING"
```

### Create Chapter with Editor.js Content
```
POST /admin/chapitre/create/{coursId}
Parameters:
  - title: "Chapter 1: Getting Started"
  - content: {"blocks": [...]}  (JSON format)
```

### Approve Course
```
POST /admin/cours/{id}/approve
```

### Refuse Course
```
POST /admin/cours/{id}/refuse
```

## Authorization & Access Control

### Implemented Checks
✓ Instructors cannot edit other instructors' courses
✓ Instructors cannot create chapters for other teachers' courses
✓ Students cannot access admin/instructor areas
✓ Courses must be APPROVED to be visible to students
✓ CSRF tokens required for POST/DELETE operations

### Ready for Enhancement
- Integrate with Symfony Security (`@IsGranted` annotation)
- Replace placeholder instructor IDs with actual User entity
- Add role-based route protection

## Common Issues & Solutions

### Course not showing to students?
- Check course status is "APPROVED" in `/admin/cours`
- Refresh browser cache
- Verify course date range (future implementation)

### Can't edit instructor's chapter?
- Only the instructor who created the course can edit its chapters
- Check `createdBy` field matches current user
- Try with course created by login user

### Editor.js content not rendering?
- Check content is valid JSON format
- Verify `getContentAsHtml()` method in Chapitre entity
- Check Twig template uses `chapitre.contentAsHtml|raw`

## Testing Checklist

- [ ] Create course as admin (all statuses)
- [ ] Create course as instructor (auto-PENDING)
- [ ] Approve course - verify student sees it
- [ ] Refuse course - verify student doesn't see it
- [ ] Create chapter with text, heading, list, code
- [ ] View chapter - verify HTML rendering
- [ ] Edit own course as instructor
- [ ] Try edit other course as instructor (should fail)
- [ ] Delete course as admin
- [ ] Student reads chapter (read-only)
- [ ] Student cannot create/edit/delete

## File Structure

```
src/
├── Controller/
│   ├── Admin/
│   │   ├── AdminCoursController.php
│   │   └── AdminChapitreController.php
│   ├── Instructor/
│   │   ├── InstructorCoursController.php
│   │   └── InstructorChapitreController.php
│   └── Student/
│       ├── StudentCoursController.php
│       └── StudentChapitreController.php
├── Entity/
│   ├── Cours.php
│   └── Chapitre.php
└── Repository/
    ├── CoursRepository.php
    └── ChapitreRepository.php

templates/
├── admin/
│   ├── cours/
│   │   ├── index.html.twig
│   │   ├── form.html.twig
│   │   └── show.html.twig
│   └── chapitre/
│       ├── index.html.twig
│       ├── form.html.twig
│       └── show.html.twig
├── instructor/
│   ├── cours/
│   │   ├── index.html.twig
│   │   ├── form.html.twig
│   │   └── show.html.twig
│   └── chapitre/
│       ├── index.html.twig
│       ├── form.html.twig
│       └── show.html.twig
└── student/
    ├── cours/
    │   ├── index.html.twig
    │   └── show.html.twig
    └── chapitre/
        ├── index.html.twig
        └── show.html.twig
```

## Next Steps (Optional Enhancements)

1. **User Integration**
   - Create User entity with roles
   - Replace placeholder instructor IDs with `$this->getUser()`
   - Add login/authentication

2. **Advanced Features**
   - Chapter ordering/sequencing
   - Student progress tracking
   - Quiz/Assessment modules
   - Discussion forums
   - File attachments

3. **Admin Enhancements**
   - Dashboard with statistics
   - Bulk operations
   - Activity logs
   - User management

## Support & Documentation

See `ELEARNING_PLATFORM.md` for complete technical documentation.
