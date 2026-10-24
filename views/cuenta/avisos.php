<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= $fvEsc($error) ?></div><?php endif; ?>
<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success" role="status"><?= $fvEsc($_SESSION['success']) ?></div><?php unset($_SESSION['success']); endif; ?>
