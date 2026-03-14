<?php
require_once __DIR__ . "/../vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class JwtHelper
{
    private static $secret_key;
    private static $algorithm = "HS256";

    private static function init()
    {
        try {
            $dotenv = Dotenv::createImmutable(__DIR__ . "/../");
            $dotenv->safeLoad();

            // Provide a hardcoded fallback ONLY for development if .env is missing
            self::$secret_key = $_ENV["SECRET_KEY"] ?? 'b3cc8029ea193a08c43c93f8f3b6dcb7c3a21458ac4e098fb32caad9cec194c0c59cd276afb278c5f9c5d0c4f9fe4591f719716eb058ad63d0d3d3619586f508';
        } catch (Exception $e) {
            error_log("JWT Init Warning: " . $e->getMessage());
            self::$secret_key = 'YOUR_SAFE_FALLBACK_KEY_123';
        }
    }

    public static function generateToken($payload)
    {
        self::init();
        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function verifyToken($token)
    {
        try {
            self::init();
            return JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
        } catch (Exception $e) {
            return null;
        }
    }
}