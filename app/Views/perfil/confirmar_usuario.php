<?= view('partials/_head', ['title' => 'Confirmar usuario - Gastito']) ?>
<div class="container py-5"><div class="row justify-content-center"><div class="col-md-5">
<div class="card shadow-sm"><div class="card-body p-4">
<h2 class="h4 mb-2">Eleg&iacute; tu nombre de usuario</h2>
<p class="text-muted">Lo vas a usar para iniciar sesi&oacute;n y para que tus amigos te encuentren.</p>
<?= view('partials/_feedback') ?>
<form method="post" action="<?= base_url('perfil/confirmar-usuario') ?>"><?= csrf_field() ?>
<label for="username" class="form-label">Nombre de usuario</label>
<div class="input-group mb-2"><span class="input-group-text">@</span><input id="username" name="username" class="form-control" required minlength="3" maxlength="30" pattern="[a-z0-9][a-z0-9._]{1,28}[a-z0-9]" value="<?= esc(old('username', $user['username'] ?? '')) ?>" autofocus autocomplete="username"></div>
<div class="form-text mb-3">De 3 a 30 caracteres: letras min&uacute;sculas, n&uacute;meros, punto o guion bajo.</div>
<button class="btn btn-primary w-100">Confirmar y continuar</button></form>
<a href="<?= base_url('logout') ?>" class="btn btn-link w-100 mt-2">Cerrar sesi&oacute;n</a>
</div></div></div></div></div>
<?= view('partials/_footer') ?>
