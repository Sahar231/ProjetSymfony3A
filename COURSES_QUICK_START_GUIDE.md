# Courses & Chapters System - Quick Start Guide

## 🚀 Getting Started

The Courses & Chapters system is now live and operational. Here's how to use it:

## 👤 Admin Guide

### Accessing Course Management
1. Log in as an **Admin** user
2. Go to **Admin Dashboard**
3. In the **Quick Actions** sidebar, click **All Courses**

### Creating a Course
1. Click **Create New Course** button
2. Fill in the form:
   - **Title**: Must start with uppercase, 3-255 characters
   - **Description**: Must start with uppercase, 10-5000 characters
   - **Category**: e.g., "Programming", "Design", "Management"
3. **Add Chapters**: 
   - Click **Add Chapter** button
   - Enter chapter title and content
   - Click **Remove Chapter** to delete chapters
4. Click **Create Course** to save
   - Admin-created courses are **automatically approved**
   - They become visible to students immediately

### Approving/Refusing Courses
1. Click **Courses Approval** in sidebar
2. Filter by status:
   - **Pending** - Courses awaiting approval (from instructors)
   - **Approved** - Already approved courses
   - **Refused** - Rejected courses
3. Click **View Details** to review course
4. Click **Approve** or **Refuse** button
   - Approved courses appear to students
   - Refused courses are hidden from students

### Managing Courses
1. Click **All Courses** to see all courses (all statuses)
2. Use filters to find courses:
   - Pending Approvals
   - Approved Courses
   - Refused Courses
3. Click action buttons:
   - **Eye icon** - View course details
   - **Edit icon** - Edit course and chapters
   - **Trash icon** - Delete course

## 👨‍🏫 Instructor Guide

### Accessing Your Courses
1. Log in as an **Instructor** user
2. Go to **Instructor Dashboard**
3. Click **My Courses** in the sidebar

### Creating a Course
1. Click **Create New Course** button
2. Fill in the form same as admin
3. Click **Create Course**
   - Your course will be marked as **PENDING**
   - It won't be visible to students until admin approves it
   - You'll see this in your course list

### Managing Your Courses
1. Your courses section shows all your courses (any status)
2. For each course, you can:
   - **View** - See course details and chapters
   - **Edit** - Modify course and chapters
   - **Delete** - Remove the course

### Viewing Approved Courses
1. Below your courses is **"Approved Courses from Other Instructors"** section
2. Shows courses created by other instructors that are approved
3. These are **read-only** - you cannot edit them
4. Click **View Details** to see the course

### Approval Status
- **Pending** badge - Waiting for admin approval
- **Approved** badge - Admin has approved (visible to students)
- **Refused** badge - Admin rejected (not visible to students)

## 👨‍🎓 Student Guide

### Accessing Courses
1. Log in as a **Student** user
2. Go to **Student Dashboard**
3. Click **Available Courses** in the sidebar

### Browsing Courses
1. See card view of all **APPROVED** courses
2. Each card shows:
   - Course title
   - Instructor name
   - Category
   - Brief description
   - Number of chapters
3. Click **View Course** to see full details

### Reading Course Content
1. On course details page, see:
   - Full course description
   - All chapters listed
   - Total number of chapters
2. Click on any chapter to expand and read content
3. Click again to collapse

### What You Can't Do
- Create courses (instructors/admin only)
- Edit courses
- Delete courses
- See PENDING or REFUSED courses (not approved)

## 📊 Course Status Explained

### PENDING Status
- Instructor just created the course
- **Not visible to students**
- Needs admin approval
- Admin sees in Courses Approval section

### APPROVED Status
- ✅ Visible to students
- ✅ Visible to all instructors
- ✅ Students can view and read chapters
- Can be from either admin or instructor

### REFUSED Status
- ❌ Not visible to students
- Admin rejected the course
- Can be re-reviewed and potentially approved later
- Only visible to admin and the course creator

## ✅ Form Rules & Validation

All course fields follow these rules:

**Title Field**
- Must start with uppercase letter (A-Z)
- Must be 3-255 characters
- Examples: ✅ "PHP Fundamentals", ❌ "php basics"

**Description Field**
- Must start with uppercase letter (A-Z)
- Must be 10-5000 characters
- Can contain detailed course overview
- Examples: ✅ "Learn the basics...", ❌ "learn the basics..."

**Category Field**
- Free text field
- Examples: "Programming", "Web Development", "Design"

**Chapter Title**
- Must start with uppercase letter (A-Z)
- Must be 3-255 characters

**Chapter Content**
- Must be at least 10 characters
- Can be as long as needed
- Can contain detailed chapter content

## 🔍 Understanding Error Messages

When you see a **red bold error message**:
1. Read the error carefully
2. Fix the issue mentioned
3. Re-submit the form

Common errors:
- "Must start with uppercase letter (A-Z)" → Change first character to uppercase
- "Cannot exceed 255 characters" → Shorten the text
- "At least 10 characters" → Add more content

**Gray helper text** under each field explains what's required before you submit.

## 🔐 Permission Summary

| Action | Admin | Instructor | Student |
|--------|:-----:|:----------:|:-------:|
| View All Courses | ✓ | Own only | Approved only |
| Create Course | ✓ | ✓ | ✗ |
| Edit Course | ✓ (all) | ✓ (own) | ✗ |
| Delete Course | ✓ (all) | ✓ (own) | ✗ |
| Approve/Refuse | ✓ | ✗ | ✗ |
| View Chapter Content | ✓ (all) | ✓ (own/approved) | ✓ (approved only) |

## 🆘 Troubleshooting

**"I can't see approve/refuse buttons"**
- Make sure you're an admin user
- Make sure course status is PENDING
- Refresh the page

**"My course doesn't show to students"**
- Check its status (should be APPROVED)
- If PENDING, wait for admin approval
- If REFUSED, contact admin to review

**"I can't edit another instructor's course"**
- This is normal! Instructors can only edit their own courses
- Contact the original instructor or ask admin

**"Validation errors keep appearing"**
- Check that first letter of title/description is uppercase
- Check word/character count matches requirements
- Look at gray helper text for details

## 📞 Need Help?

If you encounter issues:
1. Check the breadcrumb navigation to get back to dashboard
2. Click back buttons to return to previous pages
3. Try refreshing the page
4. Contact an administrator

## 🎯 Key URLs

| Page | Admin | Instructor | Student |
|------|:-----:|:----------:|:-------:|
| Course List | /admin/course | /instructor/course | /student/course |
| Create Course | /admin/course/add | /instructor/course/add | N/A |
| View Course | /admin/course/{id} | /instructor/course/{id} | /student/course/{id} |
| Approvals | /admin/course/approvals/all | N/A | N/A |

## 💡 Tips & Best Practices

1. **Before Creating**: Plan your course structure and chapter titles
2. **Use Clear Titles**: Make titles descriptive (e.g., "Introduction to Web Development" vs "Chapter 1")
3. **Detailed Content**: Add substantial content to chapters (not just a few words)
4. **Category Consistency**: Use consistent category names for organization
5. **Proofread**: Review course description and chapter content for typos
6. **Check Status**: Always verify course status in your list after creating

## 🎓 Example Course Structure

```
Course: "Web Development Basics"
  - Chapter 1: "HTML Fundamentals"
    Content: Introduction to HTML tags, structure, semantics...
  
  - Chapter 2: "CSS Styling"
    Content: Introduction to CSS selectors, properties, layout...
  
  - Chapter 3: "JavaScript Basics"
    Content: Variables, functions, DOM manipulation...
```

---

**The Courses & Chapters system is ready to use!** 🚀

For more detailed documentation, see:
- COURSES_CHAPTERS_IMPLEMENTATION.md
- COURSES_ROUTES_REFERENCE.md
