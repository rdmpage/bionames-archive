<?php

$sha1 = $_GET['sha1'];

$template = <<<EOT
<!DOCTYPE html>
	<html>
        <head>
            <meta charset="utf-8"/>
            
			<script src="js/jquery.js" type="text/javascript" charset="utf-8"></script>
            
			
			<!-- documentcloud -->
			<!--[if (!IE)|(gte IE 8)]><!-->
				<link href="public/assets/viewer-datauri.css" media="screen" rel="stylesheet" type="text/css" />
				<link href="public/assets/plain-datauri.css" media="screen" rel="stylesheet" type="text/css" />
			<!--<![endif]-->
			<!--[if lte IE 7]>
			<link href="public/assets/viewer.css" media="screen" rel="stylesheet" type="text/css" />
			<link href="public/assets/plain.css" media="screen" rel="stylesheet" type="text/css" />
			<![endif]-->
 
			<script src="public/assets/viewer.js" type="text/javascript" charset="utf-8"></script>
			<script src="public/assets/templates.js" type="text/javascript" charset="utf-8"></script>			
		</head>
		
		<body>
			<div id="doc"></div>

		<script type="text/javascript">
				var windowWidth  = $(window).width();
				var windowHeight = $(window).height();
		
		
			var docUrl = '/bionames-archive/documentcloud/<ID>.json';
			DV.load(docUrl, {
				container: '#doc',
				width:windowWidth,
				height:windowHeight,
				sidebar: false
			});
		</script>
		
		<script>
			// http://stackoverflow.com/questions/6762564/setting-div-width-according-to-the-screen-size-of-user
			$(window).resize(function() { 
				var windowWidth = $(window).width();
				var windowHeight =$(window).height();
				$('#doc').css({'width':windowWidth ,'height':windowHeight });
			});
		
		</script>
		
		</body>
	</html>
EOT;

	
$template = str_replace('<ID>', $sha1, $template);
	

echo $template;

?>