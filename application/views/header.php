<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?=@$page_title?></title>

		<!-- Bootstrap -->
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
		<?/*<link rel="stylesheet" type="text/css" href="/css/font-awesome.min.css">*/?>
		<link rel="stylesheet" type="text/css" href="/css/style.css">

		<?=@$styles?>

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
</head>
<body>
<header>
	<div class="container">
		<div class="row">
			<div class="col-md-3 col-xs-12"><a href="/" id="logo"></a></div>
			<div class="col-md-3 col-xs-12 pull-right text-right" id="wellcome">
				<div>WELCOME,</div><div> <?=$this->session->userdata('firstName')?> <?=$this->session->userdata('lastName')?></div>
				<div class="lastlogin">LAST LOGIN <?=$this->session->userdata('lastlogin')?></div>
			</div>
		</div>
	</div>
	<div class="nav-wrapper">
		<div class="container">
			<div class="row">
				<div class="navbar-header">
			    	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
			        	<span class="sr-only">Toggle navigation</span>
			        	<span class="icon-bar"></span>
			        	<span class="icon-bar"></span>
			        	<span class="icon-bar"></span>
			    	</button>
			    </div>
				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li class="menu-item menu-item-1 first"><a href="/"><span class="fa fa-5x"></span><br />CLIENT<br/>DASHBOARD</a></li>
						<li class="menu-item menu-item-2"><a href="/resources"><span class="fa fa-5x"></span><br />RESOURCES</a></li>
						<li class="menu-item menu-item-3"><a href="/media"><span class="fa fa-5x"></span><br />MEDIA</a></li>
						<li class="menu-item menu-item-4"><a href="/contactanexpert"><span class="fa fa-5x"></span><br />CONTACT<br/>AN EXPERT</a></li>
						<li class="menu-item menu-item-5"><a href="/user"><span class="fa fa-5x"></span><br />USER SETTINGS</a></li>
						<li class="menu-item menu-item-6 last"><a href="/user/leave"><span class="fa fa-5x"></span><br />LOG OUT</a></li>
					</ul>
				</div>
			</div>
		</div>
		
	</div>
</header>
<div id="main">
	<div class="container-fluid">
		<div class="container-fluid maincontent">
			<div id="page-title"><h1 class="text-center"><?=@$page_title?></h1></div>
			<div class="text-center msgbox"><?=@$msg?></div>