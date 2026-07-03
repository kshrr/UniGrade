# Student Grade & Transcript Web Application
## Overview

This project is a PHP and MySQL-based web application developed for a Web Security course. The project consists of an Insecure Version and a Secure Version to demonstrate common web application vulnerabilities and their mitigations.

## Prerequisites

Before running the project, ensure the following software is installed:

XAMPP (Apache & MySQL)
Git
Visual Studio Code (recommended)
## Installation Guide
### Step 1: Clone the Repository

Open Git Bash or your preferred terminal and navigate to your XAMPP htdocs directory.

cd C:\xampp\htdocs

Clone the repository:

git clone https://github.com/kshrr/UniGrade.git   

### Step 2: Start XAMPP

Open the XAMPP Control Panel and start:

Apache
MySQL

Ensure both services are running before continuing.

### Step 3: Create the Database
Open your browser.
Navigate to:
http://localhost/phpmyadmin
Click New.
Create a database named:
usim_grades_insecure
usim_grades_secure
### Step 4: Import the Database
Select the newly created database.
Click the Import tab.
Choose the SQL file included in the project
Click Go.
### Step 5: Configure Database Connection

Locate the database connection file db_insecure.php and db_secure.php.

Update the database configuration if necessary.

Example:

$host = "localhost";
$username = "root";
$password = "";
$database = "usim_grades_insecure";
$port = 3306; (update this to your port number)

If your MySQL server uses a different port or password, update the configuration accordingly.

### Step 6: Run the Project

Open your browser and visit:

http://localhost/UniGrade/usim_portal/insecure/login_insecure.php

or

http://localhost/UniGrade/usim_portal/secure/login_secure.php
