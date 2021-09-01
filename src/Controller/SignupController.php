<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Form\Signup\SignupFormType;
use App\Service\EmailSender;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/signup')]
class SignupController extends AbstractController
{
    /**
     * Display & process form to request a password reset.
     */
    #[Route('', name: 'app_signup')]
    public function request(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, EmailSender $emailSender, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(SignupFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($em->getRepository(Company::class)->findOneBy(['name' => $data['company']])) {
                $this->addFlash(
                    'signup_error',
                    $translator->trans('signup.error.company_exist')
                );

                return $this->render('signup/request.html.twig', [
                    'requestForm' => $form->createView(),
                ]);
            }

            if ($em->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
                $this->addFlash(
                    'signup_error',
                    $translator->trans('signup.error.email_exist')
                );

                return $this->render('signup/request.html.twig', [
                    'requestForm' => $form->createView(),
                ]);
            }

            $company = new Company();
            $company->setName($data['company']);

            $user = new User();
            $user->setEmail($data['email']);
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $user->setCompany($company);
            $user->setPassword(
                $passwordEncoder->encodePassword($user, Uuid::uuid4()->toString())
            );
            $user->setRoles(['ROLE_USER', 'ROLE_MANAGER']);

            $em->persist($company);
            $em->persist($user);
            $em->flush();

            $emailSender->sendAccountCreatedEmail($user->getEmail());

            return $this->render('signup/success.html.twig', [
                'requestForm' => $form->createView(),
            ]);
        }

        return $this->render('signup/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }
}
