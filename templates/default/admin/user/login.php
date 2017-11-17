<div class="well" style="width:650px; margin:0 auto; margin-top:100px;">
  <?php
if(isset($this->login_error)){
  ?><div class="alert alert-error">
<?php print preg_replace("#</?p[^>]*>#is","",$this->login_error); ?>
</div><?php
}
?>

<form class="form-horizontal" method="post">
  <div class="control-group">
    <label class="control-label" for="inputEmail">Логин:</label>
    <div class="controls">
      <input type="text" id="inputEmail" name="username" placeholder="Логин" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="inputPassword">Пароль:</label>
    <div class="controls">
      <input type="password" id="inputPassword" name="password" placeholder="Пароль" />
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <button type="submit" class="btn">Войти</button>
    </div>
  </div>
  <input type="hidden" name="admin_login_sm" value="1" />
</form>
</div>