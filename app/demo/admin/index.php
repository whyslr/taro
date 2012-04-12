<?php
echo '<Pre>', 'in admin index.php', '<br />', var_dump ($_GET );
$back = taro::url (array ('fgf' => 'dddd', 'abcdef' => 'gfgf'));
taro::tpl()->assign('back', $back)->display('admin_index.html');
?>