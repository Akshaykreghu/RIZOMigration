<h2>Error Connection</h2>
<p><?= h($error->getMessage()); ?></p>
<?php
date_default_timezone_set('Asia/Kolkata');
	App::uses('HttpSocket','Network/Http');
	App::uses('Xml','Utility');
	$HttpSocket = new HttpSocket();
	$data = array('errorr'=>h($error->getMessage()));
	$urls = 'https://login.mypayrollmaster.com/sendfcmmessage.php';
	$response = $HttpSocket->get($urls,$data);
	$request = $HttpSocket->request; 
	$xmlString = $response['body'];
    $file_name = 'request.txt';
                                    //$handle = fopen($file_name, 'w') or die('Cannot open file:  '.$file_name);
                                    $data = 'This is the data';
									file_put_contents($file_name, "Response given @ " . date('Y-m-d h:i:s') . PHP_EOL, FILE_APPEND);
									file_put_contents($file_name, $data.PHP_EOL, FILE_APPEND);
	?>

		<?php
$file_name = 'request.txt';
		//file_put_contents($file_name, "Response given @ " . date('Y-m-d h:i:s') . PHP_EOL, FILE_APPEND);
								file_put_contents($file_name, __d('cake_dev', 'Error')." ".PHP_EOL, FILE_APPEND);
									file_put_contents($file_name, h($error->getMessage()).PHP_EOL, FILE_APPEND);
									?>

<?php if (!empty($error->queryString)) : ?>
        
			<?php
$file_name = 'request.txt';
		//file_put_contents($file_name, "Response given @ " . date('Y-m-d h:i:s') . PHP_EOL, FILE_APPEND);
								file_put_contents($file_name, __d('cake_dev', 'SQL Query')." ".PHP_EOL, FILE_APPEND);
									file_put_contents($file_name, $error->queryString."\r\n".PHP_EOL, FILE_APPEND);
									?>
      
<?php 

$data = array();	
$data['id'] = 'dsdfdf';
$data['name'] = 'SQL ERROR';
$data['Date'] = date('Y-m-d H:i:s');
$data['Type'] = 'Error';
$data['Types'] = 'M';
$data['Error']['causs'] = h($error->getMessage());
$data['Error']['hoem'] = 'SQL Query';
$data['Error']['QueryS'] = $error->queryString;


$inp = file_get_contents('results.json');
$tempArray = json_decode($inp,true);
echo "<pre>";
print_r($tempArray);
print_r($data);
//array_push($tempArray,	 $data);
$tempArray['contacts'][] = $data;
$jsonData = json_encode($tempArray);
file_put_contents('results.json', $jsonData);
endif; ?>

