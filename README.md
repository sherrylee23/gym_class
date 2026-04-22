#  Gym Class Booking and Member Management System
A secure, integrative web application built for **Anytime Fitness**. This repository focuses specifically on the **Payment and Membership Management Module**, demonstrating advanced software design patterns, robust security implementations, and RESTful web service integration.

## ✨ Key Features

### 📐 Software Design Patterns
* **Strategy Pattern:** Implemented in `PaymentController.php` to dynamically handle multiple payment algorithms (Credit Card, Online Banking/FPX, and E-Wallet) without hardcoding conditional logic, ensuring the system is highly extensible.

### 🛡️ Software Security
* **SQL Injection Prevention:** 100% of database queries utilize **PDO Prepared Statements** and parameterized queries to safely handle user input.
* **IDOR (Insecure Direct Object Reference) Prevention:** Enforced strict **Session-Based Authorization**. The system relies on server-side session data (`$_SESSION['user_id']`) rather than easily manipulated URL parameters to process checkouts and view payment histories.

### 🔌 Web Services (Integrative Programming)
Designed using RESTful (JSON-based) architecture for cross-module communication:
* **Service Exposure (Provider):** The `history_api.php` endpoint serves a member's payment history to other modules (like Customer Service or Reporting). Features strict API gatekeeping requiring a unique `requestID` and outputting mandatory `status` and `timeStamp` fields.
* **Service Consumption (Consumer):** The checkout process (`viewPlans.php`) acts as an API consumer. It uses `file_get_contents()` to query the User Management module's API, verifying user validity before allowing financial transactions.

## 🛠️ Tech Stack
* **Backend:** PHP 8+
* **Database:** MySQL
* **Integration:** REST APIs, JSON, cURL
* **Frontend:** HTML5, CSS3, Bootstrap 5

## 🚀 Getting Started

### Prerequisites
* XAMPP, WAMP, or any standard LAMP stack environment.
* PHP 8.0 or higher.
* MySQL.
