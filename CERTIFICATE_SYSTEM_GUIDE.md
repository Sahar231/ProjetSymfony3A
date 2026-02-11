# Certificate System Integration Guide

## Overview
The certificate system has been fully implemented to award badges and certificates to students who pass quizzes in formations.

## Components Created

### 1. **Certificate Entity** (`src/Entity/Certificate.php`)
Stores certificate records with:
- `user` - Student who earned the certificate
- `formation` - Formation the certificate is for
- `quiz` - Quiz that was passed (optional)
- `score` - Percentage score achieved
- `awardedAt` - Date certificate was awarded

### 2. **Certificate Service** (`src/Service/CertificateService.php`)
Helper service for certificate operations:
- `awardCertificate()` - Manually award a certificate
- `awardCertificateIfPassed()` - Award certificate if quiz score >= passing score
- `hasCertificate()` - Check if student already has certificate
- `getCertificate()` - Retrieve a student's certificate for a formation

### 3. **Certificate Controller** (`src/Controller/Student/CertificateController.php`)
Routes:
- `GET /student/certificates` - List all certificates
- `GET /student/certificates/{id}` - View certificate details
- `GET /student/certificates/{id}/download` - Download certificate as PDF

### 4. **Templates** (`templates/student/certificate/`)
- `index.html.twig` - Certificate gallery with badges
- `show.html.twig` - Detailed certificate view
- `pdf.html.twig` - Printable certificate

### 5. **Database Migration** 
Migration `Version20260210220000` creates the `certificate` table.

## Integration with Quiz System

### In Student\QuizController.php

When a quiz is submitted and graded, use the CertificateService to award certificates:

```php
<?php

namespace App\Controller\Student;

use App\Repository\QuizRepository;
use App\Service\CertificateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student/quiz')]
class QuizController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CertificateService $certificateService,
    ) {
    }

    #[Route('/{id}/submit', name: 'student_quiz_submit', methods: ['POST'])]
    public function submitQuiz(Quiz $quiz): Response
    {
        $user = $this->getUser();
        
        // Calculate score (implement your scoring logic here)
        $score = $this->calculateScore($quiz); // e.g., 85.5
        
        // Award certificate if passed
        $certificate = $this->certificateService->awardCertificateIfPassed(
            $user,
            $quiz->getFormation(),
            $quiz,
            $score
        );
        
        if ($certificate) {
            $this->addFlash('success', sprintf(
                'Congratulations! You passed with %.2f%% and earned a certificate for %s',
                $certificate->getScore(),
                $certificate->getFormation()->getTitle()
            ));
        } else if ($score >= $quiz->getPass_Score()) {
            $this->addFlash('info', 'You passed! Certificate already earned previously.');
        } else {
            $this->addFlash('warning', sprintf(
                'You scored %.2f%%. You need %.2f%% to pass.',
                $score,
                $quiz->getPass_Score()
            ));
        }
        
        return $this->redirectToRoute('student_formation_view', ['id' => $quiz->getFormation()->getId()]);
    }

    private function calculateScore(Quiz $quiz): float
    {
        // TODO: Implement your quiz scoring logic
        // This should calculate percentage scored on the quiz
        // Return a float between 0 and 100
        return 0;
    }
}
```

## Formation Level Field

The `Formation` entity now includes a `level` field:
- Can be set when creating/editing formations
- Examples: "Beginner", "Intermediate", "Advanced", "Professional"
- Displays on certificates to indicate difficulty level

### Adding Level to Formation Creation

In your formation creation templates, add a level field:

```html
<div class="form-group">
    <label for="level" class="form-label">Level</label>
    <select id="level" name="level" class="form-select">
        <option value="Beginner">Beginner</option>
        <option value="Intermediate">Intermediate</option>
        <option value="Advanced">Advanced</option>
        <option value="Professional">Professional</option>
    </select>
</div>
```

Then in the controller, set it:
```php
$formation->setLevel($request->request->get('level'));
```

## Features

✅ **Certificate Gallery** - Beautiful badge display with statistics
✅ **Certificate Details** - View full certificate with score and details
✅ **Printable Certificates** - Download certificate as PDF (uses browser print)
✅ **Share Achievements** - Social media sharing buttons (LinkedIn, Twitter)
✅ **Statistics Dashboard** - Show total certificates, average score, study hours
✅ **Duplicate Prevention** - Won't award multiple certificates for same formation
✅ **Protected Access** - Only the certificate owner can view their certificates

## Usage Example

### Award Certificate Manually
```php
$certificate = $certificateService->awardCertificate(
    user: $user,
    formation: $formation,
    score: 92.5,
    quiz: $quiz
);
```

### Check if Certificate Exists
```php
if ($certificateService->hasCertificate($user, $formation)) {
    echo "Student already has this certificate";
}
```

### Retrieve Certificate
```php
$certificate = $certificateService->getCertificate($user, $formation);
echo "Score: " . $certificate->getScore() . "%";
```

## Next Steps

1. **Add Level Field to Formation Forms** - Update instructor and admin formation creation templates
2. **Implement Quiz Submission Logic** - Update QuizController to call `awardCertificateIfPassed()`
3. **Create Quiz Result Tracking** - Create a QuizResult entity to track attempts and scores
4. **Generate PDF Certificates** - Integrate DOMPDF for actual PDF generation
5. **Email Notifications** - Send certificate notification emails
6. **Certificate Verification** - Create a public verification page to validate certificates

## Database Structure

```sql
CREATE TABLE certificate (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    formation_id INT NOT NULL,
    quiz_id INT NULL,
    score DOUBLE NOT NULL,
    awarded_at DATETIME NOT NULL,
    INDEX IDX_FEEDFDC6A76ED395 (user_id),
    INDEX IDX_FEEDFDC65200282E (formation_id),
    INDEX IDX_FEEDFDC6853CD175 (quiz_id),
    FOREIGN KEY (user_id) REFERENCES user (id),
    FOREIGN KEY (formation_id) REFERENCES formation (id),
    FOREIGN KEY (quiz_id) REFERENCES quiz (id)
)
```

## Testing

1. Navigate to `/student/certificates` to view empty certificate list
2. Award a test certificate through the service or controller
3. Verify it appears in the gallery with correct details
4. Click "View Certificate" to see full details
5. Test social media sharing buttons

---

**Status**: ✅ Complete and ready for integration with quiz submission logic
