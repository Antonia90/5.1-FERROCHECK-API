# FerroCheck API

FerroCheck is a RESTful API built with **Laravel** that helps users track their daily iron intake through recipes and ingredients.  
The app allows creating ingredients, building recipes from those ingredients, and running a **daily check** to compare consumed iron with recommended dietary requirements.

---

## Features

### **Authentication**

- User registration, login, and logout (token-based).
- Profile management.
- Role-based permissions (user, admin).

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

---

## Installation and Setup

### Prerequisites

- PHP >= 8.2
- Composer >= 2.5
- MySQL >= 8.0
- Laravel 12.x
- Git

### 1. Clone the repository

git clone <https://github.com/Antonia90/ferrocheck-api.git>
cd ferrocheck-api

### 2. Install dependencies

    composer install

### 3. Configure environment

    cp .env.example .env
    php artisan key:generate

### 4. Set your database credentials in .env and run migrations

    php artisan migrate --seed

### 5. Run the development server

    php artisan serve

### Testing

Run the test suite with Pest:

php artisan test

vendor/bin/pest

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
- POST /api/login → Login
- POST /api/logout → Logout
- GET /api/user → Get authenticated user profile

## Ingredients

- GET /api/ingredients → List ingredients
- GET /api/ingredients/{id} → Show ingredient
- POST /api/ingredients → Create ingredient
- PUT /api/ingredients/{id} → Update ingredient
- DELETE /api/ingredients/{id} → Delete ingredient

## Recipes

- GET /api/recipes → List recipes (supports ?diet_category=vegana)
- GET /api/recipes/{id} → Show recipe with ingredients
- POST /api/recipes → Create recipe (with ingredients)
- PUT /api/recipes/{id} → Update recipe and its ingredients
- DELETE /api/recipes/{id} → Delete recipe

## Daily Check

- POST /api/daily-check

5.**Troubleshooting**
    - `401 Unauthorized`: Make sure you included the correct token and updated the Passport client details in your `.env` file.
    - `422 Unprocessable Entity`: Check required fields and validation rules in the docs.

---

## Common Issues & Solutions

1. **Failed to listen on 127.0.0.1:8000**: Another process is using the port. Try `php artisan serve --port=8080`.

2. **PHP Version Compatibility**: This project requires PHP 8.2.x. If you have PHP 8.4.x installed, you may encounter issues.

### License

This project is licensed under the MIT License.
