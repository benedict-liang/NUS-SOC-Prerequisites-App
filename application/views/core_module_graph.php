<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Benedict - Module Graph</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- CSS -->
<link type="text/css" href="<?php echo base_url() ?>static/css/bootstrap.min.css" rel="stylesheet" media="screen" />
<link type="text/css" href="<?php echo base_url() ?>static/css/module_graph_main.css" rel="stylesheet" media="screen" />
<link href='http://fonts.googleapis.com/css?family=The+Girl+Next+Door|Fredoka+One' rel='stylesheet' type='text/css'>
</head>

<body>
 
<div class="header_strip">
	<a href="<?php echo base_url().'module_pages/index' ?>">
		<button class="btn btn-inverse home_button">Modules Prerequisites Home</button>
	</a>

</div>

<div class="container">
	<div class="row">
		<div id="header">
			<div class="header_title">
				<h1>Module Prerequisites App (NUS SOC)</h1>
				<h2>by Benedict</h2>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="span12 guide_title">
			<h1>Guide</h1>
			<h3>Modules away from the arrow head, corresponds to the prerequisites of the module at the 
				arrow head.</h3>
			<h4>Example: A ---> B implies Module A is a prerequisite of module B</h4>
			<br/>
			<h3>Arrows with the same color represent an &ldquo;or&rdquo; between the corresponding prerequisites.</h3>
			<br/>	
			<h3>Extra: You can drag the bubbles in the graph around!</h3>
				
		</div>
	</div>

	<?php echo $page_content ?>

	</div>
	<!-- Footer -->
</div>
<!-- Scripts -->
<script src="<?php echo base_url(); ?>static/js/jquery1.7.2.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>static/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>static/js/dracula_graph/raphael-min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>static/js/dracula_graph/dracula_graffle.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>static/js/dracula_graph/dracula_graph.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>static/js/dracula_graph/dracula_algorithms.js"></script>

<script src="<?php echo base_url(); ?>static/js/module_graph_main.js" type="text/javascript"></script>
<?php echo $additional_scripts ?>
	
</script>
</body>



</html>