<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class JwtHelper
{
    private static $secret_key = null;
    private static $algorithm = "HS256";

    /**
     * Load environment variables once
     */
    private static function init()
    {
        // Only load if the key hasn't been set yet
        if (self::$secret_key !== null) {
            return;
        }

        try {
            // Load .env from the root directory
            $dotenv = Dotenv::createImmutable(__DIR__ . "/../");
            $dotenv->safeLoad();

            // Try $_ENV first, then getenv() as a fallback
            self::$secret_key = $_ENV["SECRET_KEY"] ?? getenv("SECRET_KEY");

            if (empty(self::$secret_key)) {
                throw new Exception("SECRET_KEY is not defined in the environment.");
            }
        } catch (Exception $e) {
            // Log the error and stop execution or handle it gracefully
            error_log("JWT Init Error: " . $e->getMessage());
            throw new Exception("Internal Server Error: Security configuration missing.");
        }
    }

    /**
     * @param array $payload Data to encode
     * @return string
     */
    public static function generateToken(array $payload)
    {
        self::init();

        // Optional: Add standard claims if not present
        if (!isset($payload['iat']))
            $payload['iat'] = time();
        if (!isset($payload['exp']))
            $payload['exp'] = time() + (60 * 60); // Default 1 hour

        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    /**
     * @param string $token
     * @return object|null
     */
    public static function verifyToken(string $token)
    {
        try {
            self::init();
            return JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
        } catch (Exception $e) {
            // This will catch expired tokens, signature mismatches, etc.
            error_log("JWT Verification Failed: " . $e->getMessage());
            return null;
        }
    }
}