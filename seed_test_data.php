<?php
/**
 * Simple seeding script to populate test data
 * Run with: php seed_test_data.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/bootstrap.php';

use App\Entity\User;
use App\Entity\Club;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Get the entity manager and password hasher
$container = require_once __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.php';
$em = $container->get(EntityManagerInterface::class);
$passwordHasher = $container->get(UserPasswordHasherInterface::class);

try {
    // Create test users
    $students = [];
    for ($i = 1; $i <= 3; $i++) {
        $user = new User();
        $user->setEmail("student{$i}@test.com");
        $user->setFullName("Student {$i}");
        $user->setRole('ROLE_STUDENT');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setIsApproved(true);
        $user->setIsVerified(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        
        $em->persist($user);
        $students[] = $user;
    }
    
    // Create a test instructor/admin user
    $creator = new User();
    $creator->setEmail('instructor@test.com');
    $creator->setFullName('Test Instructor');
    $creator->setRole('ROLE_INSTRUCTOR');
    $creator->setPassword($passwordHasher->hashPassword($creator, 'password123'));
    $creator->setIsApproved(true);
    $creator->setIsVerified(true);
    $creator->setCreatedAt(new \DateTimeImmutable());
    
    $em->persist($creator);
    
    // Flush users to get IDs
    $em->flush();
    
    // Create test clubs
    $clubNames = [
        'Chess Club' => 'Learn and play chess with fellow enthusiasts. All skill levels welcome!',
        'Art & Design' => 'Explore digital and traditional art forms. Weekly workshops and exhibitions.',
        'Coding Club' => 'Learn programming, work on projects, and build cool applications together.',
        'Photography Club' => 'Share photography skills and go on photo walks together.',
        'Music Club' => 'For musicians of all levels. Jam sessions, workshops, and performances.'
    ];
    
    $clubCount = 1;
    foreach ($clubNames as $name => $description) {
        $club = new Club();
        $club->setName($name);
        $club->setDescription($description);
        $club->setStatus(Club::STATUS_APPROVED);
        $club->setCreator($creator);
        $club->setCreatedAt(new \DateTimeImmutable());
        
        // Add some students as members
        if ($clubCount % 2 === 0 && !empty($students)) {
            $club->addMember($students[0]);
        }
        
        $em->persist($club);
        $clubCount++;
    }
    
    // Flush all data
    $em->flush();
    
    echo "✅ Test data seeded successfully!\n";
    echo "   - Created 3 student users\n";
    echo "   - Created 1 instructor user\n";
    echo "   - Created 5 test clubs\n";
    echo "\nYou can now access:\n";
    echo "   - Student 1: student1@test.com / password123\n";
    echo "   - Instructor: instructor@test.com / password123\n";
    echo "   - Clubs available at: http://localhost:8000/student/clubs\n";
    
} catch (\Exception $e) {
    echo "❌ Error seeding data: " . $e->getMessage() . "\n";
    exit(1);
}
