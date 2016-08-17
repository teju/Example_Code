<?php
/**
 * template_start.php
 *
 * Author: pixelcave
 *
 * The first block of code used in every page of the template
 *
 */
?>
<!DOCTYPE html>
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if IE 9]>         <html class="no-js lt-ie10"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">

        <title><?php echo $template['title'] ?></title>

        <meta name="description" content="<?php echo $template['description'] ?>">
        <meta name="author" content="<?php echo $template['author'] ?>">
        <meta name="robots" content="<?php echo $template['robots'] ?>">

        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">

        <!-- Icons -->
        <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
        <link rel="shortcut icon" href="<?php echo BASEURL; ?>favicon.ico">
        <link rel="apple-touch-icon" href="<?php echo IMG_PATH; ?>logo.jpg" sizes="57x57">
        <link rel="apple-touch-icon" href="<?php echo BASEURL; ?>logo.jpg" sizes="72x72">
        <link rel="apple-touch-icon" href="<?php echo BASEURL; ?>logo.jpg" sizes="76x76">
        <link rel="apple-touch-icon" href="<?php echo BASEURL; ?>logo.jpg" sizes="114x114">
        <link rel="apple-touch-icon" href="<?php echo BASEURL; ?>logo.jpg" sizes="120x120">
        <link rel="apple-touch-icon" href="<?php echo BASEURL; ?>logo.jpg" sizes="144x144">
        <link rel="apple-touch-icon" href="<?php echo BASEURL; ?>logo.jpg" sizes="152x152">
        <link rel="apple-touch-icon" href="<?php echo BASEURL; ?>logo.jpg" sizes="180x180">
        <!-- END Icons -->

        <!-- Stylesheets -->
        <!-- Bootstrap is included in its original form, unaltered -->
        <link rel="stylesheet" href="<?php echo BASEURL; ?>css/bootstrap.min.css">

        <!-- Related styles of various icon packs and plugins -->
        <link rel="stylesheet" href="<?php echo BASEURL; ?>css/plugins.css">

        <!-- The main stylesheet of this template. All Bootstrap overwrites are defined in here -->
        <link rel="stylesheet" href="<?php echo BASEURL; ?>css/main.css">

        <!-- Include a specific file here from css/themes/ folder to alter the default theme of the template -->
        <?php if ($template['theme']) { ?><link id="theme-link" rel="stylesheet" href="<?php echo BASEURL; ?>css/themes/<?php echo $template['theme']; ?>.css"><?php } ?>

        <!-- NFC custom stylesheet -->
        <link rel="stylesheet" href="<?php echo BASEURL; ?>css/nfc.css">

        <!-- The themes stylesheet of this template (for using specific theme color in individual elements - must included last) -->
        <link rel="stylesheet" href="<?php echo BASEURL; ?>css/themes.css">
        <!-- END Stylesheets -->

        <!-- Modernizr (browser feature detection library) & Respond.js (Enable responsive CSS code on browsers that don't support it, eg IE8) -->
        <script src="<?php echo BASEURL; ?>js/vendor/modernizr-2.7.1-respond-1.4.2.min.js"></script>
        <script src="<?php echo BASEURL; ?>js/jquery.min.js"></script>
    </head>
    <body>