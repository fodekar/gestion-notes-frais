<?php

namespace App\UI\Controller;

use App\Application\Service\ExpenseNoteService;
use App\Domain\Entity\User;
use App\UI\DTO\ExpenseNoteDTO;
use App\UI\Request\UpdateExpenseRequest;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

#[Route('/api/expenses')]
class ExpenseNoteController extends AbstractController
{
    private ExpenseNoteService $expenseNoteService;
    private Security $security; // Injection de Security pour récupérer l'utilisateur connecté

    public function __construct(ExpenseNoteService $expenseNoteService, Security $security)
    {
        $this->expenseNoteService = $expenseNoteService;
        $this->security = $security;
    }

    /**
     * @OA\Get(
     *     path="/api/expenses",
     *     summary="Récupère toutes les notes de frais de l'utilisateur",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste des notes de frais")
     * )
     */
    #[Route('', methods: ['GET'])]
    public function getAllExpenses(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $expenses = $this->expenseNoteService->getExpensesForUser($user);
        $expenseDTOs = array_map(fn($expense) => new ExpenseNoteDTO($expense), $expenses);

        return $this->json($expenseDTOs);
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/{id}",
     *     summary="Récupérer une note de frais spécifique",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la note de frais",
     *         @OA\Schema(type="string", format="uuid", example="e971f96b-e292-4a32-889e-2c14a7b2bd4f")
     *     ),
     *     @OA\Response(response=200, description="Détails de la note de frais"),
     *     @OA\Response(response=403, description="Non autorisé"),
     *     @OA\Response(response=404, description="Note de frais non trouvée")
     * )
     */
    #[Route('/{id}', methods: ['GET'])]
    public function getExpense(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return new JsonResponse(['error' => 'Invalid UUID format.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $expense = $this->expenseNoteService->getExpenseNoteByIdAndUser(Uuid::fromString($id), $user);

        if (!$expense) {
            return new JsonResponse(['error' => 'Expense not found or not authorized.'], JsonResponse::HTTP_FORBIDDEN);
        }

        return $this->json(new ExpenseNoteDTO($expense));
    }


    /**
     * @OA\Post(
     *     path="/api/expenses",
     *     summary="Créer une nouvelle note de frais",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"companyId", "date", "amount", "type"},
     *             @OA\Property(property="companyId", type="string", format="uuid", example="94eca325-5f1a-4a7c-89f4-ca22102ce624"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-03-12"),
     *             @OA\Property(property="amount", type="number", example=75.50),
     *             @OA\Property(property="type", type="string", example="meal")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Note de frais créée"),
     *     @OA\Response(response=400, description="Requête invalide")
     * )
     */
    #[Route('', methods: ['POST'])]
    public function createExpense(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], JsonResponse::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);

        $companyId = Uuid::fromString($data['companyId']);
        $date = new DateTimeImmutable($data['date']);
        $amount = $data['amount'];
        $type = $data['type'];

        $expense = $this->expenseNoteService->createExpenseNote(
            $user,
            $companyId,
            $date,
            $amount,
            $type
        );

        return $this->json(new ExpenseNoteDTO($expense), JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/expenses/{id}",
     *     summary="Modifier une note de frais",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la note de frais",
     *         @OA\Schema(type="string", format="uuid", example="e971f96b-e292-4a32-889e-2c14a7b2bd4f")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date", "amount", "type"},
     *             @OA\Property(property="date", type="string", format="date", example="2024-03-15"),
     *             @OA\Property(property="amount", type="number", example=90.00),
     *             @OA\Property(property="type", type="string", example="conference")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Note de frais mise à jour"),
     *     @OA\Response(response=403, description="Non autorisé"),
     *     @OA\Response(response=404, description="Note de frais non trouvée")
     * )
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function updateExpense(string $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return new JsonResponse(['error' => 'Invalid UUID format.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $updateRequest = new UpdateExpenseRequest($data);

        $errors = $validator->validate($updateRequest);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string)$errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        $expense = $this->expenseNoteService->updateExpenseNote(
            Uuid::fromString($id),
            new \DateTimeImmutable($updateRequest->date),
            $updateRequest->amount,
            $updateRequest->type,
            $user // Vérification du propriétaire
        );

        if (!$expense) {
            return new JsonResponse(['error' => 'Expense not found or not authorized.'], JsonResponse::HTTP_FORBIDDEN);
        }

        return new JsonResponse(new ExpenseNoteDTO($expense), JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/expenses/{id}",
     *     summary="Supprimer une note de frais",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la note de frais",
     *         @OA\Schema(type="string", format="uuid", example="e971f96b-e292-4a32-889e-2c14a7b2bd4f")
     *     ),
     *     @OA\Response(response=204, description="Note de frais supprimée"),
     *     @OA\Response(response=403, description="Non autorisé"),
     *     @OA\Response(response=404, description="Note de frais non trouvée")
     * )
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteExpense(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return new JsonResponse(['error' => 'Invalid UUID format.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $result = $this->expenseNoteService->deleteExpenseNote(Uuid::fromString($id), $user);

        if (!$result) {
            return new JsonResponse(['error' => 'Expense not found or not authorized.'], JsonResponse::HTTP_FORBIDDEN);
        }

        return new JsonResponse(['message' => 'Expense deleted successfully.'], JsonResponse::HTTP_NO_CONTENT);
    }
}
