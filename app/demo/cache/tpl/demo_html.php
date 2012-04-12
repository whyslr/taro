<?php
if(!empty($_obj['DEMO']))
{
if(!is_array($_obj['DEMO']))
{
$_obj['DEMO']=array(array('DEMO'=>$_obj['DEMO']));
}
$_tmp_arr_keys=array_keys($_obj['DEMO']);
if ($_tmp_arr_keys[0]!='0')
{
$_obj['DEMO']=array(0=>$_obj['DEMO']);
}
$_stack[$_stack_cnt++]=$_obj;
foreach ($_obj['DEMO'] as $rowcnt=>$DEMO)
{
$_obj=&$DEMO;
?>
<li>ID:<?php echo $_obj['id'];?> <a href="<?php echo $_obj['href'];?>">NAME:<?php echo $_obj['name'];?></a></li>
<?php
}
$_obj=$_stack[--$_stack_cnt];}
?>d