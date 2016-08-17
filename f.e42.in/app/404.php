<!DOCTYPE html>
<html>
<head>
<?php if (!defined('BASEURL')) define('BASEURL','http://shopnix.in'); ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>404 Page Not found - Shopnix - eCommerce website builder</title>
	<meta name="description" content="404 Page Not Found - Something has gone wrong and we cannot find the page you are looking for.">
	<meta name="robots" content="NOODP">

	<link rel="icon" href="<?php echo BASEURL; ?>/favicon.ico">

	<?php include 'common_includes.php'; ?>
</head>

<body class="four-column">
	<!-- TOP BAR -->


	<!-- TOP MENU -->
	<?php include 'topbar.php'; ?>
	<!-- END TOPBAR-->

    <div id="main-wrapper" class="public">
         <div id="main" style="min-height:630px" class="default"> 
            <div id="container" class="clearfix public">
				<div id="detailed-content" style="padding-bottom: 30px">

					<div class="content-header clearfix" style="margin-top:20px;">
						<h1 class="tertiary-content-heading" style="font-size: 32px;">PAGE NOT FOUND</h1>
					</div>
					
					<div class="content-body clearfix" style="background-color:white !important">
							<div class="content-main-tab" style="">								
								<div class="list-section first">
									<div class="section-body pg-features" style="margin-top:5px">
										<div class="section-body" style="text-align:center; margin-top:30px; margin-bottom: 30px" >
											<a href="<?php echo BASEURL; ?>/"><img src="<?php echo BASEURL; ?>/images/m-logo.png" /></a>
											<h2 style="margin-top:30px; text-align:center; font-size: 20px;" class="header-text">
												Oops! Something has gone wrong and the page<br/>you were looking for could not be found!<br>Try visiting our <a href="<?php echo BASEURL; ?>/">home page</a>.
											</h2>											
										</div>

										<div style="width:280px; margin-left: auto; margin-right: auto; border-top: 1px dotted #dddddd; padding-bottom: 15px; text-align: center">
											<a></a>
										</div>
										
										<p style="width: 50%; text-align:left; padding-left:65px; margin-top:40px; margin-bottom: 10px; float: left" class="section-body">
											<br>
											Start selling your products online.<br>
											Shopnix lets you create an online store within minutes<br>
											Get started now!<br>										
										</p>

										<div style="float: left; position: relative; top: 60px; text-align:center" onClick="_gaq.push(['_trackEvent', 'SignUps', 'SignUpClick', '404 Page']);">
											<a href="#" class="button-secondary QuickStoreLauncher">
												<div style="display:inline-block; width:55px; height:35px; vertical-align:middle; background:url('<?php echo BASEURL; ?>/images/btn-cart.png') no-repeat;"></div>
												<span style="height:35px; vertical-align:middle; font-size: 25px;">Create your FREE store now!</span>
											</a>
										</div>
									</div>
								</div>
							</div>
					</div>
				</div>                
            </div>            
		</div>
	</div> 
    
	<!-- START FOOTER -->
	<?php include 'footer.php'; ?>
	<?php include 'jQBanner.php'; ?>

	<!-- END FOOTER -->
	</body>
</html>
