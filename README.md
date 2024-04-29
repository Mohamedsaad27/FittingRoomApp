# FittingRoomApp RESTful API

This repository contains the codebase for the RESTful API backend of the FittingRoomApp, developed using Laravel framework. The API serves as the communication bridge between the Android application and the server, facilitating various operations such as user authentication, product management, and order processing.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Endpoints](#endpoints)
- [Contributing](#contributing)
- [License](#license)

## Features

- **User Authentication:** Secure user authentication system with JWT tokens.
- **Register and Login:** User registration and login functionality with encrypted passwords.
- **Personal Information Editing:** Allow users to edit their personal information such as name, email, and address.
- **Category Management:** CRUD operations for managing product categories.
- **Product Management:** CRUD operations for managing products.
- **Home Page:** Display categories and popular products based on sold count.
- **Product Page:** Display products filtered by specific categories.
- **Product Details Page:** Display detailed information about a specific product.
- **Search Engine:** Search functionality to find products by name or category.
- **Chat Bot:** Integration of a chat bot for assisting users with inquiries or recommendations.

## Installation

1. **Clone the repository:**
   ```
   git clone https://github.com/Mohamedsaad27/FittingRoomApp
   ```

2. **Install dependencies:**
   ```
   composer install
   ```

3. **Configure Environment:**
   - Copy the `.env.example` file to `.env` and configure your environment variables, including database settings and JWT secret key.

4. **Database Migration:**
   - Run the database migrations to create the necessary tables:
   ```
   php artisan migrate
   ```

5. **Generate Application Key:**
   ```
   php artisan key:generate
   ```

6. **Start the Development Server:**
   ```
   php artisan serve
   ```

## Usage

Once the installation is complete, you can start using the API endpoints to interact with the FittingRoomApp. Make sure to authenticate your requests using JWT tokens.

## Endpoints

For detailed information about available endpoints and their usage, please refer to the API documentation. You can access the documentation by navigating to 
This link  => https://documenter.getpostman.com/view/29332012/2sA3BuW95d
endpoint on your local server.

