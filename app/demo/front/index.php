<?php
$DEMO = array (
	array('id' => 1, 'name' => 'simple-1'),
	array('id' => 2, 'name' => 'simple-2'),
	array('id' => 3, 'name' => 'simple-3'),
	array('id' => 4, 'name' => 'simple-4'),
	array('id' => 5, 'name' => 'simple-5'),
);
foreach ($DEMO as $k => $v ) {
	$param = array(
		'block'	=>	'admin',
		'file'	=>	'simple',
		array(
			'id'		=>	$v['id'],
			'tttt'		=>	'abcdf',
		)
	);
	$DEMO[ $k ][ 'href' ] = taro::url ($param );
}
echo '<Pre>', var_dump ($_GET), '<br />';
taro::tpl()->assign ('DEMO', $DEMO)->display ('demo.html' );
?>