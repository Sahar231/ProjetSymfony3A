<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $manager->persist($admin);

        // Student
        $student = new User();
        $student->setEmail('student@test.com');
        $student->setRoles(['ROLE_USER']); // Default role
        $student->setPassword($this->userPasswordHasher->hashPassword($student, 'password'));
        $student->setFirstName('Student');
        $student->setLastName('User');
        $manager->persist($student);

        // Teacher (treated as another user who can create clubs)
        $teacher = new User();
        $teacher->setEmail('teacher@test.com');
        $teacher->setRoles(['ROLE_USER']);
        $teacher->setPassword($this->userPasswordHasher->hashPassword($teacher, 'password'));
        $teacher->setFirstName('Teacher');
        $teacher->setLastName('User');
        $manager->persist($teacher);

        // Club 1 (Approved)
        $club1 = new Club();
        $club1->setName('Chess Club');
        $club1->setDescription('For chess enthusiasts.');
        $club1->setCreatedAt(new \DateTimeImmutable());
        $club1->setStatus('APPROVED');
        $club1->setCreator($student);
        $club1->addMember($student);
        $manager->persist($club1);

        // Club 2 (Pending)
        $club2 = new Club();
        $club2->setName('Robotics Club');
        $club2->setDescription('Building robots.');
        $club2->setCreatedAt(new \DateTimeImmutable());
        $club2->setStatus('PENDING');
        $club2->setCreator($teacher);
        $manager->persist($club2);

        $manager->flush();
    }
}
