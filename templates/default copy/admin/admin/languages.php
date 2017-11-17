<?php print $render; ?>

<center><button type="button" onclick="document.location.href='<?php print $this->admin_url; ?>?m=admin&a=languages_check_tables'; return false;" class="btn btn-primary btn-large">Перепроверить базу, создать недостающие поля переводов</button></center>