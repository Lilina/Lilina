<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php template_sitename();?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
template_synd_header();
?>
<?php
//Add templates code here
?>
<link rel="stylesheet" type="text/css" href="styles/style_default.css" media="screen"/>
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
  	<a href="<?php template_sitelink();?>">
	<img src="i/logo.jpg" alt="<?php template_sitename();?>" title="<?php template_sitename();?>" />
	</a>
  	<?php template_synd_links(); ?>
	&nbsp;&nbsp;
	|
    <a href="javascript:visible_mode(true);">
    <img src="i/arrow_out.png" alt="Expand" /> expand</a>
    <a href="javascript:visible_mode(false);">
    <img src="i/arrow_in.png" alt="Collapse" /> collapse</a>
	|
    <?php
	if(template_opml()){
		echo '|';
	}
	?>
    <a href="#sources">SOURCES</a>
	
</div><div style="text-align: right;" id="times">
		<ul>
		<?php
		//Params equal tag each side of link
		template_times('<li>','</li>');
		?>
		</ul>
    </div>
<div id="main">
<?php template_output(); ?>
</div>

<div id="sources">
    <?php template_source_list(); ?>
</div>
<div id="c1">&nbsp;powered by</div>
<div id="c2">&nbsp;lilina.</div>
<div id="footer">
<?php template_footer(); ?>
</div>
</body>
</html>