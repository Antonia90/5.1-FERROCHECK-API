# FerroCheck API

FerroCheck is a RESTful API built with **Laravel** that helps users track their daily iron intake through recipes and ingredients.  
The app allows creating ingredients, building recipes from those ingredients, and running a **daily check** to compare consumed iron with recommended dietary requirements.

---

## Features

### **Authentication**

- User registration, login, and logout (token-based).
- Profile management using Personal Access Token.
- Authenticated profile endpoint api/user.

### **Roles and Permissions**

- Role management via Spatie Laravel Permission (spatie/laravel-permission: ^6.21).
- Two default roles:
 -Admin: full access to all resources.
 -User: can only manage their own ingredients and recipes.
- Access control enforced through Laravel Policies (IngredientPolicy, RecipePolicy).

### **Ingredients**

- Full CRUD for ingredients.
- Each ingredient stores type, name, and iron content per 100g.
- Users can only manage their own ingredients; admins can manage all.

### **Recipes**

- Full CRUD for recipes.
- Recipes are composed of multiple ingredients with units and quantities.
- Support for diet categories: `vegan`, `vegetarian`, `omnivorous`.
- Filters by diet category.
- Users can only edit/delete their own recipes; admins can manage all.

### **Daily Check**

- Select up to **8 recipes** consumed in a day.
- Specify number of servings per recipe.
- Compare total iron intake against a chosen category (e.g. `woman_premenopausal`, `woman_postmenopausal`, `man_adult`, `pregnant`).
- Response includes:
        -   `total_iron_mg`
        -   `required_mg`
        -   `status` (`sufficient` / `insufficient`)
        -   `difference_mg`
        -   A friendly message.

---

## Tech Stack

- [Laravel 12](https://laravel.com) – PHP framework
- [MySQL](https://www.mysql.com/) – Database
- [Pest](https://pestphp.com/) – Testing framework
- Token Authentication (Laravel Passport)
- Spatie Laravel Permission – Role management

---

## Installation and Setup

### Prerequisites

- PHP >= 8.2
- Composer >= 2.5
- MySQL >= 8.0
- Laravel 12.x
- Git

### 1. Clone the repository

    git clone <https://github.com/Antonia90/5.1-FERROCHECK-API.git>
    cd 5.1-FERROCHECK-API

### 2. Install dependencies

    composer install

### 3. Configure environment

    cp .env.example .env
    php artisan key:generate

 Set your .env file with MySQL credentials and update.

### 4. Run Passport Installation

Passport handles API authentication and token management.

If you’re setting up the project for the very first time (on a clean machine with no existing /database/migrations/oauth_* files), run:

    php artisan passport:install

⚠️ Note:
    This project already includes Passport’s migration files (create_oauth_*).
    If you can see these files inside your /database/migrations folder, you don’t need to run passport:install again.
    Simply skip this step and continue below.

If you skipped the previous command, make sure to create a personal access client manually:

    php artisan migrate
    php artisan passport:keys --force
    php artisan passport:client --personal

This creates the client credentials required for token generation.

### 5. Set your database credentials in .env and run migrations

    php artisan migrate:fresh --seed

### 6. Reset Spatie permission cache

    php artisan permission:cache-reset

### 7. Run the development server

    php artisan serve

## Testing

### 1. Prepare testing environment

### Run test migrations

    php artisan migrate:fresh --seed --env=testing

Run the test suite with Pest:

    php artisan test

Testing uses SQLite (ferrocheck_api_testing) and seeds its own Passport client via PassportTestingSeeder.

## Data Generation

This project uses both **seeders** and **factories**:

- **Factories** generate fake data dynamically during automated tests.
- **Seeders** populate the database with predefined demo data (users, ingredients, roles, Passport client).

## How to Test the API with Postman

1. **Import the Collection**

    - Download from [http://localhost:8000/docs.postman](http://localhost:8000/docs.postman)
    - In Postman, click "Import" and select the downloaded file.

2. **Set the Base URL**

    - Make sure the `baseUrl` variable in Postman is set to `http://localhost:8000` (or your server address).

3. **Authentication Flow**
    - Register a user via `POST /api/register` (or use demo credentials).
    - Log in via `POST /api/login` to obtain an `access_token`.
    - For all protected endpoints, add this header:

 ```Authorization: Bearer {access_token}```

4.**Try the Endpoints**

## Authentication

- POST /api/register → Register a new user
- Body: { "name": "User", "email": <user@example.com>, "password": "password", "password_confirmation": "password" }
- POST /api/login → Login
- Body: { "email": <user@example.com>, "password": "password" }
- POST /api/logout → Logout
- GET /api/user → Get authenticated user profile

## Ingredients

- GET /api/ingredients → List ingredients
- GET /api/ingredients/{id} → Show ingredient
- POST /api/ingredients → Create ingredient
- PUT /api/ingredients/{id} → Update ingredient
- DELETE /api/ingredients/{id} → Delete ingredient
- Body:
{
  "ingredient_type": "proteina",
  "name": "Lentils",
  "iron_mg_per_100g": 3.3
}

## Recipes

- GET /api/recipes → List recipes (supports ?diet_category=vegana)
- GET /api/recipes/{id} → Show recipe with ingredients
- POST /api/recipes → Create recipe (with ingredients)
- PUT /api/recipes/{id} → Update recipe and its ingredients
- DELETE /api/recipes/{id} → Delete recipe

## Daily Check

- POST /api/daily-check
- Body:

{
  "recipes": [
    { "recipe_id": 1, "servings": 2 },
    { "recipe_id": 3, "servings": 1 }
  ],
  "user_category": "woman_premenopausal"
}

5.**Troubleshooting**

- `401 Unauthorized`: Make sure you included the correct token and updated the Passport client details in your `.env` file.

- `422 Unprocessable Entity`: Check required fields and validation rules in the docs

- `500 Internal Server Error` Passport misconfiguration Ensure provider='users' in oauth_clients

---

## Common Issues & Solutions

1. **Failed to listen on 127.0.0.1:8000**: Another process is using the port. Try `php artisan serve --port=8080`.

2. **PHP Version Compatibility**: This project requires PHP 8.2.x. If you have PHP 8.4.x installed, you may encounter issues.

### License

This project is licensed under the MIT License.
