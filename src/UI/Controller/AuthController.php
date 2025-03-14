<?php

namespace App\UI\Controller;

use App\Domain\Entity\Company;
use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;
use OpenApi\Annotations as OA;

#[Route('/api')]
class AuthController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Créer un nouvel utilisateur",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firstName", "lastName", "email", "password", "birthDate"},
     *             @OA\Property(property="firstName", type="string", example="John"),
     *             @OA\Property(property="lastName", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="securepassword"),
     *             @OA\Property(property="birthDate", type="string", format="date", example="1990-01-01")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Utilisateur créé avec succès"),
     *     @OA\Response(response=400, description="Requête invalide"),
     *     @OA\Response(response=409, description="L'email est déjà utilisé")
     * )
     */
    #[Route('/register', methods: ['POST'])]
    public function register(
        Request                     $request,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['firstName'], $data['lastName'], $data['email'], $data['password'], $data['birthDate'])) {
            return new JsonResponse(['error' => 'All fields are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {

            $email = new Email($data['email']);

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email->getValue()]);
            if ($existingUser) {
                return new JsonResponse(['error' => 'Email already in use'], JsonResponse::HTTP_CONFLICT);
            }

            $hashedPassword = $passwordHasher->hashPassword(new User(), $data['password']);

            $user = (new User())->initialize(
                $data['firstName'],
                $data['lastName'],
                $email->getValue(),
                new DateTimeImmutable($data['birthDate']),
                $hashedPassword
            );

            $company = $entityManager->getRepository(Company::class)->findOneBy(['name' => 'Default Company']);
            if (!$company) {
                $company = new Company('Default Company');
                $entityManager->persist($company);
            }
            $user->addCompany($company);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->logger->info('Nouvel utilisateur enregistré', ['email' => $email->getValue()]);

            return new JsonResponse(['message' => 'User registered successfully'], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'inscription', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => 'An error occurred during registration'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Connexion de l'utilisateur et récupération du token JWT",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="securepassword")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Connexion réussie, retourne un token JWT"),
     *     @OA\Response(response=400, description="Requête invalide"),
     *     @OA\Response(response=401, description="Échec d'authentification")
     * )
     */
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return new JsonResponse(['error' => 'Unexpected error'], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
