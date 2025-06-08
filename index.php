<?php
require_once 'config/database.php';
require_once 'includes/Utils.php';

session_start();

// Get base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host;

// API version
$apiVersion = "1.0.0";

// Check if developer is logged in
$isLoggedIn = isset($_SESSION['dev_id']);
$developerData = null;

if ($isLoggedIn) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM developers WHERE id = ?");
        $stmt->execute([$_SESSION['dev_id']]);
        $developerData = $stmt->fetch();
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Authentication API - Developer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2c3e50',
                        secondary: '#3498db',
                        accent: '#e74c3c',
                    }
                }
            }
        }
    </script>
    <style>
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #2c3e50;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }
        .back-to-top.visible {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col font-['Poppins']">
    <!-- Navigation -->
    <nav class="bg-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="#" class="text-white text-xl font-bold">User Auth API</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex justify-center items-center sm:space-x-8">
                        <a href="#documentation" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Documentation</a>
                        <?php if ($isLoggedIn): ?>
                        <a href="#dashboard" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <?php if ($isLoggedIn): ?>
                    <a href="dev/logout.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    <?php else: ?>
                    <a href="dev/login-page.php" class="ml-4 bg-white hover:bg-gray-100 text-primary px-4 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="dev/register-page.php" class="ml-4 bg-white hover:bg-gray-100 text-primary px-4 py-2 rounded-md text-sm font-medium">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary to-secondary text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold sm:text-5xl md:text-6xl">User Authentication API</h1>
                <p class="mt-3 max-w-md mx-auto text-xl text-gray-100 sm:text-2xl md:mt-5 md:max-w-3xl">
                    A secure, scalable API for managing user authentication in your applications.
                </p>
                <?php if (!$isLoggedIn): ?>
                <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                    <div class="rounded-md shadow">
                        <a href="/dev/register-page.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-primary bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                            Get Started
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if ($isLoggedIn && $developerData): ?>
        <!-- Developer Dashboard -->
        <section id="dashboard" class="mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Developer Dashboard</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Account Details</h3>
                        <div class="space-y-3">
                            <p class="text-gray-600"><span class="font-medium">Name:</span> <?php echo htmlspecialchars($developerData['name']); ?></p>
                            <p class="text-gray-600"><span class="font-medium">Email:</span> <?php echo htmlspecialchars($developerData['email']); ?></p>
                            <p class="text-gray-600"><span class="font-medium">Account Created:</span> <?php echo date('F j, Y', strtotime($developerData['created_at'])); ?></p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">API Key</h3>
                        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                            <code id="apiKey" class="text-sm font-mono text-gray-800"><?php echo htmlspecialchars($developerData['api_key']); ?></code>
                            <button onclick="copyApiKey()" class="ml-4 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Keep this key secure and never share it publicly.</p>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">API Calls</h3>
                        <p class="text-4xl font-bold text-primary">1,234</p>
                        <p class="text-sm text-gray-500">Last 30 days</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Active Users</h3>
                        <p class="text-4xl font-bold text-primary">56</p>
                        <p class="text-sm text-gray-500">Total registered users</p>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Documentation Section -->
        <section id="documentation" class="space-y-8">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Table of Contents -->
                <div class="md:w-1/4">
                    <div class="sticky top-4 bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Table of Contents</h3>
                        <nav class="space-y-2">
                            <a href="#overview" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">
                                <i class="fas fa-home w-5 mr-2"></i>Overview
                            </a>
                            <a href="#authentication" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">
                                <i class="fas fa-key w-5 mr-2"></i>Authentication
                            </a>
                            <a href="#api-usage" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">
                                <i class="fas fa-book w-5 mr-2"></i>Using the API
                            </a>
                            <a href="#error-responses" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">
                                <i class="fas fa-exclamation-circle w-5 mr-2"></i>Error Responses
                            </a>
                            <a href="#security" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">
                                <i class="fas fa-shield-alt w-5 mr-2"></i>Security Best Practices
                            </a>
                            <a href="#integration" class="block text-gray-600 hover:text-primary hover:bg-gray-50 px-3 py-2 rounded-md">
                                <i class="fas fa-cogs w-5 mr-2"></i>System Requirements
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Main Documentation Content -->
                <div class="md:w-3/4 space-y-8">
                    <!-- Overview Section -->
                    <div id="overview" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-home text-primary text-2xl mr-3"></i>
                            <h2 class="text-3xl font-bold text-gray-900">Overview</h2>
                        </div>
                        <p class="text-gray-600 mb-6">Welcome to the User Authentication API documentation. This system provides both a developer portal for managing API keys and a complete authentication API that can be integrated into any application.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Developer Portal Features</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-2">
                                    <li>Developer registration with email verification</li>
                                    <li>Secure login system</li>
                                    <li>API key management</li>
                                    <li>Interactive documentation</li>
                                    <li>Modern UI with Tailwind CSS</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">API Features</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-2">
                                    <li>User registration with email verification</li>
                                    <li>Secure token-based authentication</li>
                                    <li>Password reset functionality</li>
                                    <li>Password change functionality</li>
                                    <li>Secure token management</li>
                                    <li>Rate limiting and API key validation</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Authentication Section -->
                    <div id="authentication" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">    
                            <i class="fas fa-key text-primary text-2xl mr-3"></i>
                            <h2 class="text-3xl font-bold text-gray-900">Authentication</h2>
                        </div>
                        <p class="text-gray-600 mb-4">All API requests require authentication using your API key in the header:</p>
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="flex items-center justify-between">
                                <code class="text-sm font-mono text-gray-800">X-API-Key: your_api_key</code>
                                <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your_api_key" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Note:</strong> Keep your API key secure and never expose it in client-side code.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- API Usage Section -->
                    <div id="api-usage" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-code text-primary text-2xl mr-3"></i>
                            <h2 class="text-3xl font-bold text-gray-900">Using the API</h2>
                        </div>

                        <!-- Available Endpoints -->
                        <div class="space-y-8">
                            <!-- Register User -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/register.php</code>
                                    <span class="ml-2 text-gray-600">Register new user</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Request Body</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"email":"user@example.com","password":"SecurePass123"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "email": "user@example.com",
    "password": "SecurePass123"
}</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"User registered successfully","data":{"user_id":"user_id","email":"user@example.com","auth_token":"32_character_token","expires_at":"token_expiry_timestamp","is_verified":false,"otp":"verification_code"}}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user_id": "user_id",
        "email": "user@example.com",
        "auth_token": "32_character_token",
        "expires_at": "token_expiry_timestamp",
        "is_verified": false,
        "otp": "verification_code"
    }
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Request OTP -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/request-otp.php</code>
                                    <span class="ml-2 text-gray-600">Request OTP for verification</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Request Body</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"email":"user@example.com","purpose":"email-verification"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "email": "user@example.com",
    "purpose": "email-verification"
}</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"OTP generated successfully","data":{"user_id":"user_id","email":"user@example.com","purpose":"email-verification","otp":"6_digit_code","otp_expires_at":"otp_expiry_timestamp","auth_token":"32_character_token","token_expires_at":"token_expiry_timestamp"}}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "OTP generated successfully",
    "data": {
        "user_id": "user_id",
        "email": "user@example.com",
        "purpose": "email-verification",
        "otp": "6_digit_code",
        "otp_expires_at": "otp_expiry_timestamp",
        "auth_token": "32_character_token",
        "token_expires_at": "token_expiry_timestamp"
    }
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Verify Email -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/verify-email.php</code>
                                    <span class="ml-2 text-gray-600">Verify email with OTP</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Request Body</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"email":"user@example.com","otp":"123456"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "email": "user@example.com",
    "otp": "123456"
}</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"Email verified successfully","data":{"user_id":"user_id","email":"user@example.com","auth_token":"32_character_token","expires_at":"token_expiry_timestamp"}}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "Email verified successfully",
    "data": {
        "user_id": "user_id",
        "email": "user@example.com",
        "auth_token": "32_character_token",
        "expires_at": "token_expiry_timestamp"
    }
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- User Login -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/login.php</code>
                                    <span class="ml-2 text-gray-600">User login</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Request Body</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"email":"user@example.com","password":"SecurePass123"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "email": "user@example.com",
    "password": "SecurePass123"
}</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"Login successful","data":{"user_id":"user_id","email":"user@example.com","auth_token":"32_character_token","expires_at":"token_expiry_timestamp"}}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "Login successful",
    "data": {
        "user_id": "user_id",
        "email": "user@example.com",
        "auth_token": "32_character_token",
        "expires_at": "token_expiry_timestamp"
    }
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Logout -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/logout.php</code>
                                    <span class="ml-2 text-gray-600">Logout user</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nAuthorization: Bearer auth_token\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Authorization: Bearer auth_token
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"Successfully logged out"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "Successfully logged out"
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reset Password -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/reset-password.php</code>
                                    <span class="ml-2 text-gray-600">Reset user password</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nAuthorization: Bearer auth_token\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Authorization: Bearer auth_token
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Request Body</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"otp":"123456","new_password":"NewSecurePass123"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "otp": "123456",
    "new_password": "NewSecurePass123"
}</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"Password reset successfully","data":{"email":"user@example.com","token_expires_at":"2024-03-22 10:30:00"}}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "Password reset successfully",
    "data": {
        "email": "user@example.com",
        "token_expires_at": "2024-03-22 10:30:00"
    }
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Change Password -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/change-password.php</code>
                                    <span class="ml-2 text-gray-600">Change user password</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nAuthorization: Bearer auth_token\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Authorization: Bearer auth_token
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Request Body</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"old_password":"CurrentPass123","new_password":"NewSecurePass123","confirm_password":"NewSecurePass123"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "old_password": "CurrentPass123",
    "new_password": "NewSecurePass123",
    "confirm_password": "NewSecurePass123"
}</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"Password changed successfully"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "Password changed successfully"
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Refresh Token -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/refresh-token.php</code>
                                    <span class="ml-2 text-gray-600">Refresh authentication token</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nAuthorization: Bearer auth_token\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Authorization: Bearer auth_token
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"Token refreshed successfully","data":{"token":"new_32_character_token","expires_at":"2024-03-22 10:30:00"}}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "token": "new_32_character_token",
        "expires_at": "2024-03-22 10:30:00"
    }
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete User -->
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center mb-4">
                                    <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-medium mr-2">POST</span>
                                    <code class="text-sm font-mono text-gray-800 font-bold">/api/delete-user.php</code>
                                    <span class="ml-2 text-gray-600">Delete user account</span>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Headers</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text="X-API-Key: your-api-key\nContent-Type: application/json" class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">X-API-Key: your-api-key
Content-Type: application/json</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Request Body</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"email":"user@example.com"}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "email": "user@example.com"
}</pre>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-medium text-gray-900 mb-2">Response</h6>
                                        <div class="bg-gray-100 p-3 rounded-md relative group">
                                            <button onclick="copyToClipboard(this)" data-copy-text='{"success":true,"message":"User account deleted successfully","data":{"email":"user@example.com"}}' class="absolute top-2 right-2 p-1 text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <pre class="text-sm font-mono text-gray-800">{
    "success": true,
    "message": "User account deleted successfully",
    "data": {
        "email": "user@example.com"
    }
}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Responses Section -->
                    <div id="error-responses" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-exclamation-circle text-primary text-2xl mr-3"></i>
                            <h2 class="text-3xl font-bold text-gray-900">Error Responses</h2>
                        </div>
                        <p class="text-gray-600 mb-6">All API endpoints return consistent error responses in the following format:</p>

                        <div class="space-y-6">
                            <!-- General Error Format -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">General Error Format</h3>
                                <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"error_type"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"Human-readable error message"</span>
<span class="text-purple-600">}</span></pre>
                            </div>

                            <!-- Common Error Types -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Common Error Types</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Code</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">400</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Invalid request</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Missing or invalid parameters</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">401</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Unauthorized</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Invalid or missing API key</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">403</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Forbidden</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Invalid authentication token</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">404</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Not found</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Resource not found</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">429</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Too many requests</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Rate limit exceeded</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">500</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Server error</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">Internal server error</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Example Error Responses -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Example Error Responses</h3>
                                
                                <!-- Invalid Request -->
                                <div class="mb-6">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Invalid Request</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"Invalid request"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"No matching user found with the provided email and token"</span>
<span class="text-purple-600">}</span></pre>
                                    </div>
                                </div>

                                <!-- Token Expired -->
                                <div class="mb-6">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Token Expired</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"Token expired"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"The authentication token has expired"</span>,
    <span class="text-blue-600">"expired_at"</span>: <span class="text-green-600">"2024-03-21 12:34:56"</span>
<span class="text-purple-600">}</span></pre>
                                    </div>
                                </div>

                                <!-- Rate Limit Exceeded -->
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Rate Limit Exceeded</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <pre class="text-sm font-mono text-gray-800"><span class="text-purple-600">{</span>
    <span class="text-blue-600">"success"</span>: <span class="text-orange-600">false</span>,
    <span class="text-blue-600">"error"</span>: <span class="text-green-600">"Rate limit exceeded"</span>,
    <span class="text-blue-600">"message"</span>: <span class="text-green-600">"Too many requests. Please try again later."</span>,
    <span class="text-blue-600">"retry_after"</span>: <span class="text-orange-600">60</span>
<span class="text-purple-600">}</span></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div id="security" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-shield-alt text-primary text-2xl mr-3"></i>
                            <h2 class="text-3xl font-bold text-gray-900">Security Best Practices</h2>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">API Key Security</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-2">
                                    <li>Never expose your API key in client-side code</li>
                                    <li>Store API keys securely in environment variables</li>
                                    <li>Rotate API keys periodically</li>
                                    <li>Use HTTPS for all API requests</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Password Security</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-2">
                                    <li>Use strong passwords (minimum 8 characters, mix of letters, numbers, and symbols)</li>
                                    <li>Implement rate limiting for login attempts</li>
                                    <li>Use secure password reset flows with time-limited tokens</li>
                                    <li>Enable two-factor authentication when available</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Integration Guide Section -->
                    <div id="integration" class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-book text-primary text-2xl mr-3"></i>
                            <h3 class="text-2xl font-semibold text-gray-900">Getting Started</h3>
                        </div>
                        
                        <!-- Setup Requirements -->
                        <div class="mb-8">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">System Requirements</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                                    <li>PHP 7.4 or higher</li>
                                    <li>MySQL 5.7 or higher</li>
                                    <li>Apache/Nginx web server</li>
                                    <li>SSL certificate (recommended for production)</li>
                                    <li>Composer (PHP package manager)</li>
                                    <li>Write permissions for logs directory</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Installation Steps -->
                        <div class="mb-8">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Installation Steps</h4>
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">1. Clone and Setup</h5>
                                    <p class="text-gray-600 mb-2">Clone the repository and install dependencies:</p>
                                    <div class="bg-gray-100 p-3 rounded-md">
                                        <pre class="text-sm font-mono text-gray-800">git clone <repository-url>
cd user_auth_api
composer install</pre>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">2. Database Setup</h5>
                                    <p class="text-gray-600 mb-2">Create a new MySQL database and import the schema:</p>
                                    <div class="bg-gray-100 p-3 rounded-md">
                                        <pre class="text-sm font-mono text-gray-800">mysql -u root -p < database/schema.sql</pre>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">3. Environment Configuration</h5>
                                    <p class="text-gray-600 mb-2">Set up your environment variables:</p>
                                    <ol class="list-decimal pl-6 space-y-2 text-gray-600">
                                        <li>Copy <code class="text-sm font-mono bg-gray-200 px-1 py-0.5 rounded">.env.example</code> to <code class="text-sm font-mono bg-gray-200 px-1 py-0.5 rounded">.env</code></li>
                                        <li>Update database credentials</li>
                                        <li>Configure SMTP settings for email</li>
                                        <li>Set security keys</li>
                                    </ol>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">4. Web Server Configuration</h5>
                                    <p class="text-gray-600 mb-2">Configure your web server:</p>
                                    <ul class="list-disc pl-6 space-y-2 text-gray-600">
                                        <li>Point document root to the project directory</li>
                                        <li>Ensure PHP has write permissions for logs</li>
                                        <li>Enable URL rewriting (if using Apache)</li>
                                        <li>Configure SSL certificate (recommended)</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-2">5. Developer Registration</h5>
                                    <p class="text-gray-600 mb-2">To start using the API:</p>
                                    <ol class="list-decimal pl-6 space-y-2 text-gray-600">
                                        <li>Visit the registration page</li>
                                        <li>Fill in your details and system information</li>
                                        <li>Verify your email address</li>
                                        <li>Receive your API key</li>
                                        <li>Store the API key securely</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h5 class="font-medium text-gray-900 mb-2">Security Best Practices</h5>
                            <ul class="list-disc pl-6 space-y-2 text-gray-600">
                                <li>Always use HTTPS in production</li>
                                <li>Store API keys securely</li>
                                <li>Implement rate limiting</li>
                                <li>Validate all input data</li>
                                <li>Use secure password hashing</li>
                                <li>Implement proper error handling</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-primary text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h5 class="text-xl font-bold mb-2">User Authentication API</h5>
                    <p class="text-gray-300">Version <?php echo $apiVersion; ?></p>
                </div>
                <div class="text-right">
                    <p class="text-gray-300">Need help? <a href="mailto:support@example.com" class="text-white hover:text-gray-200">Contact Support</a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script>
        function copyApiKey() {
            const apiKey = document.getElementById('apiKey');
            navigator.clipboard.writeText(apiKey.textContent)
                .then(() => {
                    alert('API key copied to clipboard!');
                })
                .catch(err => {
                    console.error('Failed to copy API key:', err);
                });
        }

        function copyToClipboard(button) {
            const text = button.getAttribute('data-copy-text');
            navigator.clipboard.writeText(text).then(() => {
                const icon = button.querySelector('i');
                icon.classList.remove('fa-copy');
                icon.classList.add('fa-check');
                button.classList.remove('text-gray-500', 'hover:text-gray-700');
                button.classList.add('text-green-500');
                
                setTimeout(() => {
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-copy');
                    button.classList.remove('text-green-500');
                    button.classList.add('text-gray-500', 'hover:text-gray-700');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy text:', err);
                const icon = button.querySelector('i');
                icon.classList.remove('fa-copy');
                icon.classList.add('fa-times');
                button.classList.remove('text-gray-500', 'hover:text-gray-700');
                button.classList.add('text-red-500');
                
                setTimeout(() => {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-copy');
                    button.classList.remove('text-red-500');
                    button.classList.add('text-gray-500', 'hover:text-gray-700');
                }, 2000);
            });
        }

        // Back to top button functionality
        window.onscroll = function() {
            const button = document.querySelector('.back-to-top');
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                button.classList.add('visible');
            } else {
                button.classList.remove('visible');
            }
        };
    </script>
</body>
</html> 