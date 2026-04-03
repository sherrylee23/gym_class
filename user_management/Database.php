<?php

// Implementation of Singleton Design Pattern within your preferred style
function getDBConnection() {
    static $conn = null; // Use a static variable to ensure only ONE connection exists

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "anytime_fitness"; // Updated for your Gym System 

    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }
    return $conn; // Return the single connection object [cite: 32]
}
