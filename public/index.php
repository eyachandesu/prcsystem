<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./dist/output.css" rel="stylesheet">
</head>
<body>
 <div class="bg-blue-500 text-white p-4">Welcome to the PRCS System</div>
 <div class="bg-blue-500 text-white p-4">
  <p class = "text-2xl font-bold">
    Login
  </p>
  <form action="/controllers/login_handler.php" method="POST">
    <input type="text" id="username" name="username" placeholder="Username">
    <input type="password" id="password" name="password" placeholder="Password">
    <button type="submit">Login</button>
  </form>
 </div>
</body>
</html>