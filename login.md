# Authentification API

## Connexion d'un utilisateur

### POST /login

Cette API permet de connecter un utilisateur en fournissant les informations nécessaires.

#### Request Body

- **email** (string): L'email de l'utilisateur
  - Exemple: "johndoe@example.com"
- **password** (string): Le mot de passe de l'utilisateur
  - Exemple: "password123"

#### Responses

- **200**: Utilisateur trouvé
  - **Content-Type**: application/json
  - **Body**:
    ```json
    {
      "statut": true,
      "message": "User found",
      "code": 200,
      "data": {
        "id": 1,
        "name": "John Doe",
        "postnom": "Doe",
        "numero": "123456789",
        "date": "2025-02-18",
        "email": "johndoe@example.com",
        "accreditation": 0,
        "avatar": "",
        "pays": "France",
        "ville": "Paris",
        "devise": "EUR"
      }
    }
    ```

- **400**: Email ou mot de passe manquant
  - **Content-Type**: application/json
  - **Body**:
    ```json
    {
      "statut": false,
      "message": "Missing email or password",
      "code": 400
    }
    ```

- **404**: Utilisateur non trouvé
  - **Content-Type**: application/json
  - **Body**:
    ```json
    {
      "statut": false,
      "message": "User not found",
      "code": 404
    }
    ```