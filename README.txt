Project Setup Documentation
Category
Details
Project Title
Wellmeadows Hospital Management System
System Requirements
• PHP 8.2 or higher • Laravel 12 • PostgreSQL 15 or higher • Composer • Node.js and npm • XAMPP or local server environment • Visual Studio Code Visual Studio Code • pgAdmin pgAdmin 4
Installation Steps
1. Clone or download the project folder from GitHub. 2. Open the project folder in Visual Studio Code. 3. Open the terminal and install Laravel dependencies using: composer install 4. Install frontend dependencies using: npm install 5. Copy the environment configuration file: copy .env.example .env 6. Generate the Laravel application key: php artisan key:generate
Database Setup
1. Open pgAdmin and create a database named wellmeadows_hospital. 2. Configure the database connection in the .env file: DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=5432 DB_DATABASE=wellmeadows_hospital DB_USERNAME=postgres DB_PASSWORD=yourpassword 3. Run database migrations using: php artisan migrate 4. If seeders are available, run: php artisan db:seed
Run Instructions
1. Start the Laravel development server: php artisan serve 2. Start the Vite frontend server: npm run dev 3. Open the browser and access: http://127.0.0.1:8000 4. Test the modules such as login, patient management, staff management, appointments, and billing.
Test Accounts
Admin Account Username: admin@wellmeadows.com Password: admin123 Staff Account Username: staff@wellmeadows.com Password: staff123
Known Limitations
• Some modules are still under development. • Real-time notifications are not yet implemented. • Email verification and password recovery may be limited. • Mobile responsiveness may vary on smaller devices. • Some advanced reporting and analytics features are not included in the current version.
127.0.0.1
127.0.0.1
Aiya
Project Setup Documentation
Project Title
Wellmeadows Hospital Management System

System Requirements
PHP 8.2 or higher
Laravel 12
PostgreSQL 15 or higher
Composer
Node.js and npm
XAMPP or local server environment
Visual Studio Code Visual Studio Code
pgAdmin pgAdmin 4

Installation Steps
Clone or download the project folder from GitHub.
Open the project folder in Visual Studio Code.
Open the terminal and install Laravel dependencies:
composer install

Install frontend dependencies:
npm install

Copy the environment configuration file:
copy .env.example .env

Generate the Laravel application key:
php artisan key:generate


Database Setup
Open pgAdmin and create a database named:
wellmeadows_hospital

Configure the database connection in the .env file:
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=wellmeadows_hospital
DB_USERNAME=postgres
DB_PASSWORD=yourpassword

Run the database migrations:
php artisan migrate

If seeders are available, run:
php artisan db:seed


Run Instructions
Start the Laravel development server:
php artisan serve

Start the Vite frontend server:
npm run dev

Open the browser and access:
http://127.0.0.1:8000

Test the application modules such as:
Login and Registration
Patient Management
Staff Management
Appointments
Billing System

Test Accounts
Admin Account
Username: medicaldirector@wellmeadows.com
Password: 12345678
Staff Account
Username: staff@wellmeadows.com
Password: 12345678

Known Limitations
Some modules are still under development.
Real-time notifications are not yet implemented.
Email verification and password recovery features may be limited.
Mobile responsiveness may vary on smaller devices.
Some advanced reports and analytics are not included in the current version.