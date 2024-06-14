# Project Laravel Payment Module

## Setup

1. **Clone Repository:**
git clone <repository-url>
cd payment-module

2. **Environment Setup:**
- Copy `.env.example` to `.env`:
  ```
  cp .env.example .env
  ```
- Edit `.env` and set your database credentials:
  ```dotenv
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=your_database_name
  DB_USERNAME=your_database_username
  DB_PASSWORD=your_database_password
  ```

3. **Install Dependencies:**
composer install

4. **Passport Installation:**
php artisan passport

5. **Horizon Installation:**
php artisan horizon
6. **Queue Setup:**
php artisan queue:table
php artisan migrate
php artisan queue

7. **Create Personal Access Client:**
php artisan passport
--personal

Follow the prompts to assign the client to a user and provide a name.

8. **Run Migrations:**
php artisan migrate

9. **Serve the Application:**
php artisan serve
php artisan queue:work (different terminal)

Access the application at `http://localhost:8000`.

## Testing

To run tests:
php artisan test

## Additional Notes

- Ensure `.env` configuration matches your development environment.
- For production, adjust caching, queue configurations, and environment variables accordingly.