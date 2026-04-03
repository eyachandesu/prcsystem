<?php

class RoleHelper
{
    // Available roles
    const ADMIN = 'Admin';
    const USER = 'User';


    // Function to check if the current role is admin
    public static function isAdmin($role): bool
    {
        return $role === self::ADMIN;
    }

    // Function to check if the current role is user
    public static function isUser($role): bool
    {
        return $role === self::USER;
    }

    // Function to check if the role is one of the allowed roles
    public static function hasRole($role, $allowedRoles): bool
    {
        return in_array($role, $allowedRoles);
    }
}