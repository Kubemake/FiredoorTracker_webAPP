<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login page</title>

    <!-- Bootstrap -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="site-wrapper">
    	<div class="site-wrapper-inner text-center">
    		<div class="container">
	    		<div class="row">
	    			<div class="col-md-12"><a href="/"><img id="login" width="443" height="128" src="/images/logo.png" alt="FireDoor tracker" alt="FireDoor tracker"></a></div>
	    		</div>
	    		<div class="row">
					<div class="col-xs-4 col-xs-push-4">
						<form name="loginform" id="loginform" method="POST" enctype="multipart/form-data" class="text-left">
							<?php echo validation_errors(); ?>
							<div class="form-group">
									<input type="text" class="form-control input-lg" name="username" id="username" placeholder="Login" value="<?php echo set_value('username'); ?>" autofocus required />
							</div>
							<div class="form-group">
									<input type="password" class="form-control input-lg" name="password" id="password" placeholder="Password" value="<?php echo set_value('password'); ?>" required />
							</div>
							<div class="form-group row">
								<div class="checkbox col-xs-6 rememberme">
									<label><input name="rememberme" type="checkbox"><span>Remember me</span></label>
								</div>
								<div class="col-xs-6 text-right restore">
									<a href="/user/recovery/" class="">Forgot password?</a>
								</div>
							</div>
							<div class="form-group text-center">
								<button type="submit" class="btn btn-primary btn-lg btn-block">LOGIN</button>
							</div>
						</form>
					</div>
				</div>
			</div>
    	</div>
    </div>


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/js/bootstrap.min.js"></script>
  </body>
</html>