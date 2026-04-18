<?php
require_once('../user_management/Database.php');

abstract class BaseModel {
    // Change 'protected' to 'public' so other classes can use the connection
    public static function db() {
        return getDBConnection();
    }
}