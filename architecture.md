# 📐 Architecture et Choix Techniques

## 1️⃣ Introduction

L'application **Gestion des Notes de Frais** est une API construite avec **Symfony 6** qui permet aux commerciaux de gérer leurs notes de frais et d'obtenir un remboursement de leurs dépenses professionnelles.

Ce document explique **l'architecture**, **les choix techniques** et **les bonnes pratiques** suivies dans ce projet.

---

## 2️⃣ Architecture Générale

L’application suit une **architecture hexagonale**, également connue sous le nom de **ports et adaptateurs**. Ce choix permet :

- ✅ **Séparation des responsabilités** : Le domaine métier est découplé des infrastructures.
- ✅ **Testabilité** : Possibilité de tester indépendamment les composants.
- ✅ **Évolutivité** : Facilité à modifier ou remplacer des dépendances sans impacter le cœur du projet.

### 📂 **Structure du projet**

📦 gestion-notes-frais
┣ 📂 src
┃ ┣ 📂 Application (Services)
┃ ┣ 📂 Domain (Entités, Value Objects)
┃ ┣ 📂 Infrastructure (Repositories, Controllers)
┃ ┗ 📂 UI (DTOs, Requests)
┣ 📂 config
┣ 📂 tests


### 🏗 **Détail des Couches**

#### 1️⃣ **Domaine (`Domain/`)**

- Contient **les entités**, **les objets de valeur**, **les interfaces des repositories**.
- Ne dépend **d'aucune technologie**.

#### 2️⃣ **Application (`Application/`)**

- Contient **les services** qui appliquent la logique métier.
- Utilise les **interfaces des repositories** définies dans le domaine.

#### 3️⃣ **Infrastructure (`Infrastructure/`)**

- Implémente les repositories avec Doctrine.
- Gère l'interaction avec la base de données.

#### 4️⃣ **Interface Utilisateur (`UI/`)**

- Contient les **contrôleurs**, **DTOs**, **validations des requêtes**.
- Gère la communication entre l’extérieur et l'application.

---

## 3️⃣ Technologies et Outils

| Technologie                            | Usage                           |
| -------------------------------------- | ------------------------------- |
| **Symfony 6**                          | Framework PHP principal         |
| **Doctrine ORM**                       | Gestion de la base de données   |
| **JWT (LexikJWTAuthenticationBundle)** | Authentification utilisateur    |
| **NelmioApiDoc**                       | Documentation API avec Swagger  |
| **PHPUnit**                            | Tests unitaires et fonctionnels |
| **Docker & Docker Compose**            | Containerisation du projet      |

---

## 4️⃣ Workflow de l'API

📌 **Exemple : Processus de création d’une note de frais**

1. 🔑 L’utilisateur s'authentifie avec JWT.
2. 📨 Il envoie une requête `POST /api/expenses` avec les données nécessaires.
3. 🎯 **Le contrôleur** reçoit la requête et valide les données.
4. 🏗 **Le service** applique la logique métier.
5. 💾 **Le repository** sauvegarde les informations en base de données.
6. ✅ Une réponse est renvoyée à l’utilisateur.

---

## 5️⃣ Bonnes Pratiques et Commentaires PHPDoc

### ✏ **Exemple de Documentation du Code**

#### 📌 **Service `ExpenseNoteService`**

```php
/**
 * Service de gestion des notes de frais.
 * Gère la création, la modification, la récupération et la suppression des notes de frais.
 */
class ExpenseNoteService {

    /**
     * Récupère toutes les notes de frais d'un utilisateur donné.
     *
     * @param User $user L'utilisateur dont on veut les notes de frais
     * @return ExpenseNote[] Liste des notes de frais
     */
    public function getExpensesForUser(User $user): array {
        return $this->expenseNoteRepository->findBy(['user' => $user]);
    }
}

📌 Contrôleur ExpenseNoteController
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

✅ Tests Unitaires et Fonctionnels
- 📌 Les tests sont basés sur PHPUnit.
- 📌 Les fixtures sont rechargées avant chaque exécution des tests.

🎯 Conclusion
Cette architecture offre modularité, maintenabilité et sécurité.
Elle facilite également l’évolutivité et garantit une séparation claire des responsabilités. 🚀
