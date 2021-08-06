<?php

declare(strict_types=1);

namespace App\Command\demo;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateDemoUserCommand extends Command
{
    public const COMPANY_NAME = 'Demo Company';

    protected static $defaultName = 'demo:user:create';

    private string $demoMode;
    private string $demoPassword;
    private string $demoUserdemo;
    private string $demoAdmindemo;

    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(string $name = null, string $demoMode, string $demoAdmindemo, string $demoUserdemo, string $demoPassword, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($name);
        $this->demoMode = $demoMode;
        $this->demoAdmindemo = $demoAdmindemo;
        $this->demoUserdemo = $demoUserdemo;
        $this->demoPassword = $demoPassword;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create demo user in databases - Use environment variable')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->demoMode) {
            $output->writeln('<comment>Demo mode must be activate</comment>');

            return Command::FAILURE;
        }

        if (!$this->demoAdmindemo || !$this->demoPassword || !$this->demoUserdemo) {
            $output->writeln('<comment>Variables `DEMO_PASSWORD`, `DEMO_USER` and `DEMO_ADMIN` cannot be null or empty</comment>');

            return Command::FAILURE;
        }

        $company = (new Company())
            ->setName(self::COMPANY_NAME)
        ;

        $admin = (new User())
            ->setEmail($this->demoAdmindemo)
            ->setActive(true)
            ->setLastname('Demo')
            ->setFirstname('Admin')
            ->setCompany($company)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
        ;

        $admin->setPassword(
            $this->passwordEncoder->encodePassword($admin, $this->demoPassword)
        );

        $user = (new User())
            ->setEmail($this->demoUserdemo)
            ->setActive(true)
            ->setLastname('Demo')
            ->setFirstname('User')
            ->setCompany($company)
            ->setRoles(['ROLE_USER'])
        ;

        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $this->demoPassword)
        );

        try {
            $this->entityManager->persist($company);
            $this->entityManager->persist($admin);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $output->writeln('<info>Users are created</info>');
        } catch (Exception $exception) {
            $output->writeln('<error>Issue during user creation</error>');
        }

        return Command::SUCCESS;
    }
}
