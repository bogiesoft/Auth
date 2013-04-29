<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Auth admin panel</title>
<link rel="stylesheet" type="text/css" href="/a/public/admin/css/admin.css" />

<!-- Admin template
<link rel="stylesheet" type="text/css" href="/auth/css/theme.css" />
<link rel="stylesheet" type="text/css" href="/auth/css/style.css" />
<link rel="stylesheet" type="text/css" href="/auth/css/blue.css" /> -->
<!-- jquery & jquery-ui -->
<script language="javascript" src="/a/public/admin/js/jquery-1.8.3.min.js"></script>
<script language="javascript" src="/a/public/admin/js/jquery-ui-1.9.1.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="/a/public/admin/css/ui-lightness/jquery-ui-1.9.1.custom.min.css" />
<!-- jquery.treeview -->
<script language="javascript" src="/a/public/admin/js/jquery.treeview/jquery.treeview.js"></script>
<link rel="stylesheet" type="text/css" href="/a/public/admin/js/jquery.treeview/jquery.treeview.css" />

<!--[if IE]>
<link rel="stylesheet" type="text/css" href="css/ie-sucks.css" />
<![endif]-->

<!-- Bootstrap -->
<!-- Requires jQuery to be loaded already -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="/a/public/admin/js/bootstrap/css/bootstrap.min.css" />
<script language="javascript" src="/a/public/admin/js/bootstrap/js/bootstrap.min.js"></script>

<style type="text/css">
	body {
		background: #fafafa;
	}
	div.page {
		background: white;
		padding: 40px 0 40px 0;
	}
	#sidetreecontrol {
		margin-top: 20px;
	}
	ul#tree.treeview ul {
		background: none;
	}
</style>
</head>
<body>
	<div class="container" style="margin-top: 20px;">
		<div class="row">
			<div class="span12">
				<div class="navbar">
				  <div class="navbar-inner" style="text-align: center;">
				    <a class="brand" href="#" style="margin-left: 0;">Auth</a>
				    <a href="<?php echo web::constructURL(	'Page',		'logout', array(''), 'html'); ?>" class="btn btn-primary pull-right">Log Out</a>
				  </div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<ul class="nav nav-tabs">
					<li<?php if($data['current'] == "Dashboard")	{ echo " class=\"active\""; } ?>><a href="<?php echo web::constructURL('Page',		'view', array(''), 'html'); ?>">Dashboard</a></li>
					<li<?php if($data['current'] == "Accounts")		{ echo " class=\"active\""; } ?>><a href="<?php echo web::constructURL('Account',	'view', array(''), 'html'); ?>">Accounts</a></li>
					<li<?php if($data['current'] == "Utility")		{ echo " class=\"active\""; } ?>><a href="<?php echo web::constructURL('Utility',	'view', array(''), 'html'); ?>">Utilities</a></li>
				</ul>
			</div>
		</div>
		<?php include(dirname(__FILE__)."/".$template. ".inc"); ?>
	</div>
</body>
</html>