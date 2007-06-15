<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		index.skin.php
Purpose:	Default Template
Notes:		
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php template_sitename();?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
template_synd_header();
?>
<link rel="stylesheet" type="text/css" href="<?php template_path(); ?>/style.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script language="JavaScript" type="text/javascript"><!--
	var showDetails = <?php echo ( ($_COOKIE['showDetails']=="true") ? "true" : "false"); ?>;
	var markID = '<?php echo $_COOKIE['mark']; ?>' ;
//-->
</script>
<script language="JavaScript" type="text/javascript" src="js/engine.js"></script>
<?php
//Just extra stuff that a plugin may have added
template_header();
?>
</head>
<body onload="visible_mode(showDetails)">
<div id="navigation">
  	<a href="<?php template_siteurl();?>">
	<img src="i/logo.jpg" alt="<?php template_sitename();?>" title="<?php template_sitename();?>" />
	</a>
  	<?php template_synd_links(); ?>
	&nbsp;&nbsp;
	|
    <a href="javascript:visible_mode(true);">
	<img src="i/arrow_out.png" alt="Show All Items" /> Expand</a>
    <a href="javascript:visible_mode(false);">
	<img src="i/arrow_in.png" alt="Hide All Items" /> Collapse</a>
	|
    <?php
	if(template_opml()){
		echo ' |';
	}
	?>
    <a href="#sources">SOURCES</a>
	
</div>
<div style="text-align: right; padding-top: 2em; padding-right: 2em;" id="times">
		<ul>
		<?php
		template_times();
		?>
		</ul>
    </div>
<div id="main">
<?php global $out; echo $out; ?>
</div>

<div id="sources">
	<strong>Sources:</strong>
	<ul>
		<?php template_source_list('echo', $data); ?>
	</ul>
	<?php template_end_errors(); ?>
</div>
<div id="c1">&nbsp;powered by</div>
<div id="c2">&nbsp;lilina.</div>
<div id="footer">
<?php template_footer(); ?><br />
<img src="<?php template_path(); ?>/magpie.png" alt="Uses MagpieRSS" /><img src="<?php template_path(); ?>/oss.png" alt="Licensed under the GNU General Public License" /><img src="<?php template_path(); ?>/php.png" alt="Powered by PHP: Hypertext Processor" />
</div>
</body>
</html>