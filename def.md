# Authentification API

## Inscription d'un nouvel utilisateur

### POST /signin

Cette API permet d'inscrire un nouvel utilisateur en fournissant les informations nécessaires.

#### Request Body

- **name** (string): Le nom de l'utilisateur
  - Exemple: "John Doe"
- **email** (string): L'email de l'utilisateur
  - Exemple: "johndoe@example.com"
- **password** (string): Le mot de passe de l'utilisateur
  - Exemple: "password123"

#### Responses

- **201**: Utilisateur enregistré avec succès
  - **Content-Type**: application/json
  - **Body**:
    ```json
    {
      "statut": true,
      "message": "User registered successfully. Please check your email for the confirmation code.",
      "code": 201,
      "data": {
        "id": 1,
        "name": "John Doe",
        "email": "johndoe@example.com",
        "accreditation": 0,
        "avatar": "",
        "pays": "",
        "ville": "",
        "devise": ""
      }
    }
    ```

- **400**: Champs requis manquants
  - **Content-Type**: application/json
  - **Body**:
    ```json
    {
      "statut": false,
      "message": "Missing required fields",
      "code": 400
    }
    ```

- **409**: Email déjà existant et confirmé
  - **Content-Type**: application/json
  - **Body**:
    ```json
    {
      "statut": false,
      "message": "Email already exists and confirmed",
      "code": 409
    }
    ```

- **500**: Erreur interne du serveur
  - **Content-Type**: application/json
  - **Body**:
    ```json
    {
      "statut": false,
      "message": "Failed to register user",
      "code": 500
    }
    ```