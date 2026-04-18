<?php


// Design Patter: Factory Pattern --> decouples the logic of class types from the main schdeule
abstract class SessionAccess {
    public abstract function getLabel();
}

class PremiumAccess extends SessionAccess {
    public function getLabel() {
        return "Members Only";
    }
}

class FreeAccess extends SessionAccess {
    public function getLabel() {
        return "Open to Public";
    }
}

class AccessFactory {
    public static function create($isFree) {
        return $isFree ? new FreeAccess() : new PremiumAccess();
    }
}


