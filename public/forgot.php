<?php
// public/forgot.php
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Config.php';

$userModel = new User();

$notice = '';
$linkForDev = null;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL);
    if(!$email) {
        $notice = 'Correo inválido.';
    } else {
        $user = $userModel->findByEmail($email);
        if(!$user) {
            $notice = 'Si el correo existe, se ha enviado un enlace de recuperación.';
        } else {
            $token = bin2hex(random_bytes(24));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hora
            $userModel->createPasswordResetToken($user['id'],$token,$expires);

            // Genera enlace (en producción envía por email)
            $link = Config::$BASE_URL . '/reset.php?token=' . urlencode($token);
            // Intento de enviar correo (configura mailer de producción)
            // mail($email, "Recuperar contraseña", "Usa este enlace: $link");
            // Para desarrollo, mostramos el link en pantalla (elimina esta línea en producción)
            $linkForDev = $link;
            $notice = 'Se generó un enlace de recuperación. Revisa tu correo (o usa el enlace de desarrollo).';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Recuperar contraseña</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center">
  <div class="w-full max-w-md p-6">
    <div class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-4">Recuperar contraseña</h2>
      <?php if($notice): ?>
        <div class="bg-yellow-50 p-3 rounded mb-4 text-yellow-800"><?=$notice?></div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <div>
          <label class="block text-sm mb-1">Correo</label>
          <input name="email" type="email" required class="w-full border px-3 py-2 rounded" />
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">Solicitar enlace</button>
      </form>

      <?php if($linkForDev): ?>
        <div class="mt-4 p-3 bg-slate-100 rounded text-sm">
          <strong>Enlace de desarrollo (copiar/pegar en navegador):</strong>
          <div class="break-all"><a class="text-indigo-600" href="<?=$linkForDev?>"><?=$linkForDev?></a></div>
        </div>
      <?php endif; ?>

      <div class="mt-4 text-sm"><a href="index.php" class="text-indigo-600 hover:underline">Volver al login</a></div>
    </div>
  </div>
</body>
</html>
