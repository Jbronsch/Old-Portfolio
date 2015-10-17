<?php 
require 'vendor/autoload.php';
date_default_timezone_set('America/Vancouver');

$app = new \Slim\Slim( array(
	'view' => new \Slim\Views\Twig()
	));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->get('/', function() use($app){
	$app->render('index.twig');
})->name("home");

$app->get('/projects', function() use($app){
	$app->render('projects.twig');
})->name("projects");

$app->get('/contact', function() use($app){
	$app->render('contact.twig');
})->name("contact");

$app->post('/contact', function() use($app){
	$name = $app->request->post("name");
	$email = $app->request->post("email");
	$message = $app->request->post("message");

	if(!empty($name) && !empty($email) && !empty($message)){
		$cleanName = filter_var($name, FILTER_SANITIZE_STRING);
		$cleanEmail = filter_var($email, FILTER_SANITIZE_STRING);
		$cleanMessage = filter_var($message, FILTER_SANITIZE_STRING);
	} else {
		//message user problem
		$app->redirect("contact");
	}

// Swift_SmtpTransport::newInstance('smtp.gmail.com' , 465, 'ssl')
// 		->setUsername("jbronsch@gmail.com")
// 		->setPassword('DeadButDreaming99');

	$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
	$mailer = \Swift_Mailer::newInstance($transport);
	$swMessage = \Swift_Message::newInstance();
	$swMessage->setFrom(array($cleanEmail => $cleanName ));
	$swMessage->setTo(array('jbronsch@gmail.com'));
	$swMessage->setBody($cleanEmail, $cleanMessage);

	$result = $mailer->send($swMessage);

	if($result > 0){
		//send thank you
		$app->redirect('/');
	} else {
		//send user message failed
		//log error
		$app->redirect('contact');
	}

});

$app->run();
?>