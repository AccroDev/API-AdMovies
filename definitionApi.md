openapi: 3.0.0
info:
  title: Authentification API
  version: 1.0.0
paths:
  /signin:
    post:
      summary: Inscription d'un nouvel utilisateur
      description: Cette API permet d'inscrire un nouvel utilisateur en fournissant les informations nécessaires.
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                name:
                  type: string
                  description: Le nom de l'utilisateur
                  example: "John Doe"
                email:
                  type: string
                  description: L'email de l'utilisateur
                  example: "johndoe@example.com"
                password:
                  type: string
                  description: Le mot de passe de l'utilisateur
                  example: "password123"
      responses:
        '201':
          description: Utilisateur enregistré avec succès
          content:
            application/json:
              schema:
                type: object
                properties:
                  statut:
                    type: boolean
                    example: true
                  message:
                    type: string
                    example: "User registered successfully. Please check your email for the confirmation code."
                  code:
                    type: integer
                    example: 201
                  data:
                    type: object
                    properties:
                      id:
                        type: integer
                        example: 1
                      name:
                        type: string
                        example: "John Doe"
                      email:
                        type: string
                        example: "johndoe@example.com"
                      accreditation:
                        type: integer
                        example: 0
                      avatar:
                        type: string
                        example: ""
                      pays:
                        type: string
                        example: ""
                      ville:
                        type: string
                        example: ""
                      devise:
                        type: string
                        example: ""
        '400':
          description: Champs requis manquants
          content:
            application/json:
              schema:
                type: object
                properties:
                  statut:
                    type: boolean
                    example: false
                  message:
                    type: string
                    example: "Missing required fields"
                  code:
                    type: integer
                    example: 400
        '409':
          description: Email déjà existant et confirmé
          content:
            application/json:
              schema:
                type: object
                properties:
                  statut:
                    type: boolean
                    example: false
                  message:
                    type: string
                    example: "Email already exists and confirmed"
                  code:
                    type: integer
                    example: 409
        '500':
          description: Erreur interne du serveur
          content:
            application/json:
              schema:
                type: object
                properties:
                  statut:
                    type: boolean
                    example: false
                  message:
                    type: string
                    example: "Failed to register user"
                  code:
                    type: integer
                    example: 500