<?php

namespace App\Tests\UI\Controller;

use App\Domain\Entity\Company;
use App\Domain\Entity\ExpenseNote;
use App\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExpenseNoteControllerTest extends WebTestCase
{
    private $client;
    private static string $token;
    private static string $otherUserToken;
    private static string $userId;
    private static string $companyId;
    private static string $expenseIdUser1;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // 🔹 Effectuer un login pour récupérer le token
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'john.doe@example.com',
            'password' => 'securepassword'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::$token = $data['token'] ?? '';

        if (!isset(self::$token)) {
            throw new \Exception("❌ Erreur : Impossible de récupérer le token pour John.");
        }

        // 🔹 Récupérer un User ID
        $entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'john.doe@example.com']);
        if ($user) {
            self::$userId = $user->getId()->toString();
        } else {
            throw new \Exception("❌ Aucun utilisateur trouvé en base.");
        }

        // 🔹 Récupérer une Company ID
        $company = $entityManager->getRepository(Company::class)->findOneBy([]);
        if ($company) {
            self::$companyId = $company->getId()->toString();
        } else {
            throw new \Exception("❌ Aucune entreprise trouvée en base.");
        }

        // 🔹 Récupérer une note de frais existante de John Doe
        $expense = $entityManager->getRepository(ExpenseNote::class)->findOneBy(['user' => $user]);
        if ($expense) {
            self::$expenseIdUser1 = $expense->getId()->toString();
        } else {
            throw new \Exception("❌ Aucune note de frais trouvée pour John.");
        }
    }

    public function testCreateExpense(): void
    {
        $this->client->request('POST', '/api/expenses', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'companyId' => self::$companyId,
            'date' => '2024-03-12',
            'amount' => 75.50,
            'type' => 'meal'
        ]));

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
    }

    public function testGetExpenses(): void
    {
        $this->client->request('GET', '/api/expenses', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::$token
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testAccessForbiddenForOtherUserExpense(): void
    {
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'jane.doe@example.com',
            'password' => 'securepassword'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $token = $data['token'] ?? '';

        if (!isset($data['token'])) {
            throw new \Exception("❌ Erreur : Impossible de récupérer le token pour Jane.");
        }

        // 🔹 Essayer d'accéder à une note de frais de John avec le token de Jane
        $this->client->request('GET', '/api/expenses/' . self::$expenseIdUser1, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(403);
        $this->assertJson($this->client->getResponse()->getContent());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("Expense not found or not authorized.", $response['error']);
    }

    public function testUpdateExpense(): void
    {
        // 🔹 Mise à jour de la note de frais de John Doe
        $this->client->request('PUT', '/api/expenses/' . self::$expenseIdUser1, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'date' => '2024-03-15',
            'amount' => 90.00,
            'type' => 'conference' // ✅ Confirmer que ce type est maintenant autorisé
        ]));

        // ✅ Vérifier que la mise à jour réussit (200 OK)
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(90.00, $data['amount']);
        $this->assertEquals('conference', $data['type']); // 🔥 Vérifier que la mise à jour fonctionne
    }

    public function testForbiddenUpdateForOtherUserExpense(): void
    {
        // 🔹 Connexion de Jane Doe
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'jane.doe@example.com',
            'password' => 'securepassword'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $tokenJane = $data['token'] ?? '';

        if (!isset($data['token'])) {
            throw new \Exception("❌ Erreur : Impossible de récupérer le token pour Jane.");
        }

        // 🔹 Essayer de modifier la note de John avec le token de Jane
        $this->client->request('PUT', '/api/expenses/' . self::$expenseIdUser1, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $tokenJane,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'date' => '2024-03-20',
            'amount' => 150.00,
            'type' => 'conference' // ✅ Vérifier avec un type valide
        ]));

        // ✅ Vérifier que l'accès est interdit (403 Forbidden)
        $this->assertResponseStatusCodeSame(403);
        $this->assertJson($this->client->getResponse()->getContent());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("Expense not found or not authorized.", $response['error']);
    }

    public function testDeleteExpense(): void
    {
        // 🔹 Créer une note de frais avant de la supprimer
        $this->client->request('POST', '/api/expenses', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'companyId' => self::$companyId,
            'date' => '2024-03-12',
            'amount' => 50.00,
            'type' => 'meal'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $expenseId = $data['id'];

        // 🔹 Supprimer cette note de frais
        $this->client->request('DELETE', "/api/expenses/$expenseId", [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::$token
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
