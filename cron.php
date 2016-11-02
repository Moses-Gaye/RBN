<meta charset="utf-8" />
<?php

// PHPMailer
//require_once 'email/phpmailer/class.phpmailer.php';

// Afficher les erreurs
showError();
date_default_timezone_set("Europe/Paris");
$no_updates = array();
$cdata ="<![CDATA[";
$close_cdata ="]]>";
$lundi = array();
$mardi = array();
$mercredi = array();
$jeudi = array();
$vendredi = array();
$samedi = array();
$dimanche = array();
$program_datas = array();
$nb_files_loaded = 0;
$sort_ready = false;
$sunday_loaded = false;
class MyDB extends SQLite3
{
    function __construct($db_name)
    {
        $this->open($db_name);
    }
}

$datee= new DateTime();
echo $datee->getTimestamp();
$insert_date = $datee->getTimestamp();
//d($insert_date);
$day = $datee->format('l');
$day = strtolower($day);
//d('$day : ' . $day);
/*,
	*/
//$fontToAdd = isset($_GET['font']) ? htmlspecialchars($_GET['font']) : '';
//$mode = isset($_GET['mode']) ? htmlspecialchars($_GET['mode']) : 'add';
$files = array('Lundi.xml','Mardi.xml','Mercredi.xml','Jeudi.xml','Vendredi.xml','Samedi.xml','Dimanche.xml');

$time = date('Ymd');
//d($time);

$filename = 'Lundi.xml';
$last_update = date('timestamp');
d($last_update);

if (!file_exists('rbn_db.db')){
	$db = new SQLite3('rbn_db.db');
	
	$db->exec('CREATE TABLE times (Lundi_last_time INT(30), Mardi_last_time INT(30), Mercredi_last_time INT(30), Jeudi_last_time INT(30), Vendredi_last_time INT(30), Samedi_last_time INT(30), Dimanche_last_time INT(30) , previous_day VARCHAR(30))');
	$db->exec('CREATE TABLE lundi_datas (title VARCHAR(10000000), artist VARCHAR(10000000), startTime VARCHAR(10000000), endTime VARCHAR(10000000), type VARCHAR(10000000), info VARCHAR(10000000))');
	$db->exec('CREATE TABLE mardi_datas (title VARCHAR(10000000), artist VARCHAR(10000000), startTime VARCHAR(10000000), endTime VARCHAR(10000000), type VARCHAR(10000000), info VARCHAR(10000000))');
	$db->exec('CREATE TABLE mercredi_datas (title VARCHAR(10000000), artist VARCHAR(10000000), startTime VARCHAR(10000000), endTime VARCHAR(10000000), type VARCHAR(10000000), info VARCHAR(10000000))');
	$db->exec('CREATE TABLE jeudi_datas (title VARCHAR(10000000), artist VARCHAR(10000000), startTime VARCHAR(10000000), endTime VARCHAR(10000000), type VARCHAR(10000000), info VARCHAR(10000000))');
	$db->exec('CREATE TABLE vendredi_datas (title VARCHAR(10000000), artist VARCHAR(10000000), startTime VARCHAR(10000000), endTime VARCHAR(10000000), type VARCHAR(10000000), info VARCHAR(10000000))');
	$db->exec('CREATE TABLE samedi_datas (title VARCHAR(10000000), artist VARCHAR(10000000), startTime VARCHAR(10000000), endTime VARCHAR(10000000), type VARCHAR(10000000), info VARCHAR(10000000))');
	$db->exec('CREATE TABLE dimanche_datas (title VARCHAR(10000000), artist VARCHAR(10000000), startTime VARCHAR(10000000), endTime VARCHAR(10000000), type VARCHAR(10000000), info VARCHAR(10000000))');
	$db->exec('INSERT INTO times (previous_day) VALUES ("'.$day.'")');
	for ($cpt = 0; $cpt < count($files); $cpt ++){

		$column = str_replace('.xml', '', $files[$cpt]).'_last_time';
		$data_col = strtolower(str_replace('.xml', '', $files[$cpt]));
		d('data_col : '.$data_col);
		$db->exec('INSERT INTO times ('. $column .') VALUES (0123456789)');
		$db->exec('INSERT INTO '.$data_col.'_datas (title) VALUES ("new title entry")');
		$db->exec('INSERT INTO '.$data_col.'_datas (artist) VALUES ("new artist entry")');
		$db->exec('INSERT INTO '.$data_col.'_datas (startTime) VALUES ("new starting time entry")');
		$db->exec('INSERT INTO '.$data_col.'_datas (endTime) VALUES ("new endTime entry")');
		$db->exec('INSERT INTO '.$data_col.'_datas (type) VALUES ("new type entry")');
		$db->exec('INSERT INTO '.$data_col.'_datas (info) VALUES ("new info entry")');

	}
	//on renseigne le jour

}

for ($xmlcount = 0; $xmlcount < count($files); $xmlcount++){
	check_update($files[$xmlcount]);
	//d('xml count : '.$xmlcount);
	if ($xmlcount >= (count($files) -1)) {
		//d('TOASTY !!!!!!!!!!!! ');
		$sort_ready = true;
		sort_datas();
	}
}

//$dom = new DomDocument;

function check_update($xmlfile){
	global $db;
	global $no_updates;
	global $day;
	$filetime = filemtime($xmlfile);
	$column_name = str_replace('.xml', '', $xmlfile).'_last_time';
	//d('colum name : '. $column_name);


	$db = new MyDB('rbn_db.db');
	//insert_new_timestamp($db, 'test_modif', $column_name);
	$result = $db->query('SELECT '. $column_name .' FROM times');
	$results = $result->fetchArray();
	$time_to_compare = $results[$column_name];
	//d('TIME TO COMPARE WITh : '.$time_to_compare);
	//d("checking for program updates ... ". ($filetime == $time_to_compare));
	if ($filetime != $time_to_compare){
		//d('updates found for '.$xmlfile);
		insert_new_data_into_column($db, 'times', $filetime, $column_name);
		load_datas($xmlfile);
	} else {
		//d('no update found');
		array_push($no_updates, strtolower(str_replace('.xml', '', $xmlfile)));
	}
	
	if (count($no_updates)>=  7){
		d('count($no_updates)>=  7 : vrai');
		$prev_day = query_data_from_db($db, 'rbn_db.db', 'previous_day', 'times');
		d('prev_day from db = ' .$prev_day);
		if ($day != $prev_day) // si on change de jour alors on met à jour le programme.xml aussi ainsi que la base
		{
			d('changement de jour');
			insert_new_data_into_column($db, 'times', $day, 'previous_day');
			sort_datas();
		}
		//
		//
	}
	/*if ($sort_ready){
		sort_datas();
	}*/
	

}


/*$filetime = filemtime($filename);
//d('file time : '.$filetime);
//d('last update : '.$last_update);*/
//$column_name = str_replace('.xml', '', $filename).'_last_time';
//$db_last_update = '<![CDATA['.$last_upda';te.']]>';

function query_data_from_db($database, $db_file_name, $db_column_name, $db_table_name){
	$database = new MyDB($db_file_name);
	//insert_new_timestamp($db, 'test_modif', $column_name);
	$result = $database->query('SELECT '. $db_column_name .' FROM '.$db_table_name);
	$results = $result->fetchArray();
	$request_data = $results[$db_column_name];
	//d('request_data : ' .$request_data);
	//$request_data = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $request_data);
	return $request_data;
}

function insert_new_data_into_column($database, $tablename, $value, $columnname){

	$query = 'UPDATE '. $tablename . ' SET '. $columnname .' = "'. $value .'"';//.' WHERE ID=1'
	$database->exec($query);
	d('updating db...'.$tablename.', '.$value.', '.$columnname);
}

function load_datas($xmlfile){
	//global $day;
	global $db;
	global $lundi;
	global $mardi;
	global $mercredi;
	global $jeudi;
	global $vendredi;
	global $samedi;
	global $dimanche;
	global $cdata;
	global $close_cdata;
	global $nb_files_loaded;
	global $sunday_loaded;
	global $sort_ready;

	//d('sort ready : ' + $sort_ready);

	$dom = new DomDocument();
	$dom->load($xmlfile);

	$infos = $dom->getElementsByTagName('info');
	$startime = $dom->getElementsByTagName('start_time');
	$artist = $dom->getElementsByTagName('artist');
	$titre = $dom->getElementsByTagName('title');
	$category = $dom->getElementsByTagName('type');
	$duration = $dom->getElementsByTagName('duration');


	$keepinfos = array();
	$keepartists = array();
	$keepstartimes = array();
	$keeptitres = array();
	$keepcategories = array();
	$keepdurations = array();

	foreach ($infos as $node) {
		$keepinfos[] = $node->nodeValue;
	}

	foreach ($artist as $node) {
		$keepartists[] = $node->nodeValue;
	}

	foreach ($startime as $node) {
		$keepstartimes[] = heure_to_secondes($node->nodeValue);
	}

	foreach ($duration as $node) {
		$keepdurations[] = heure_to_secondes($node->nodeValue);
	}

	foreach ($category as $node) {
		$keepcategories[] = $node->nodeValue;
	}

	foreach ($titre as $node) {
		$keeptitres[] = $node->nodeValue;
	}
	
//Fonction de tri
	$tracks = ['startTime'=>array(),'artist'=>array(),'title'=>array(),'category'=>array(),'endTime'=>array()];
	$hour=3600;
	$endday=$hour*24;
	$length = count ($keepstartimes);
	$flag=0;
	$overflow = false;
	$decalage=0;
	$hourflag=false;
	$daypassed=false;
	for ($t=0; $t < $length; $t++) { 
// $track correspond à la piste qui vaut garder que si elle va être jouer
		$track = ['startTime'=>$keepstartimes[$t]+$decalage, 'artist'=>$keepartists[$t], 'title'=>$keeptitres[$t], 'category'=>$keepcategories[$t], 'endTime'=>$keepstartimes[$t]+$decalage+$keepdurations[$t]];
		
/* On vérifie si l'heure de départ de la chanson est en-dessous de l'heure courante
$overflow nous signale si nous avons dépassé l'heure suivante dans l'heure courante
*/
				if ($track['startTime']<$hour and !$overflow and $track['startTime']>($hour-7200)) {
// cela permet de ne pas afficher des pistes sans réelle valeur pour nos auditeurs
					if ($keepcategories[$t]!='0' and !$daypassed) 
						{
						array_push($tracks['startTime'], secondes_to_heure($track['startTime']));
						array_push($tracks['artist'], $track['artist']);
						array_push($tracks['title'], $track['title']);
						array_push($tracks['category'], $track['category']);
						array_push($tracks['endTime'], secondes_to_heure($track['endTime']));
						d('piste');
						d($t);
						}
// Si la fin de la piste est après le début de l'heure suivante on le signale
					if ($track['endTime']>$hour) {	
						$overflow = true;
						$decalage=$track['endTime']-$hour;
						d("nouveau decalage");
						d(secondes_to_heure($decalage));
					}
				}
/*				d('Winmedia : Start time');
				d(secondes_to_heure($keepstartimes[$t]));
				d('real : Start time');
				d(secondes_to_heure($track['startTime']));
				d('Current hour');
				d(secondes_to_heure($hour));
*/
// On vérifie si on a atteind l'heure suivante
				if ($track['startTime']==($hour+$decalage)) {
					if ($keepcategories[$t]!='0' and !$daypassed)
						{
						array_push($tracks['startTime'], secondes_to_heure($track['startTime']));
						array_push($tracks['artist'], $track['artist']);
						array_push($tracks['title'], $track['title']);
						array_push($tracks['category'], $track['category']);
						array_push($tracks['endTime'], secondes_to_heure($track['endTime']));
						d('piste');
						d($t);}
// Si la piste commençait à l'heure de changement et n'avait pas allumé l'$overflow	
					if ($overflow = false) {$overflow=true;}
//c'est la fin de l'$overflow : on est de nouveau à une heure fixe
					else{
						$overflow=false;
						$hour=$hour+3600;
						if ($decalage>=3600) {$decalage=(($decalage-$decalage%3600)/3600-1)*3600+$decalage%3600;}
/*						
						d("nouveau decalage");
						d(secondes_to_heure($decalage));
*/
						$hourflag=true;
						}
					
				}
				
									
			
	}
	
	//var_dump($keeptitres);

	switch (strtolower($xmlfile)) {
		case 'lundi.xml':
			array_push($lundi, $tracks['title'], $tracks['artist'], $tracks['category'], $tracks['startTime'], $tracks['endTime'], $keepinfos, 'lundi');
			//.
			insert_new_data_into_column($db, 'lundi_datas', htmlspecialchars(serialize($tracks['artist'])), 'artist');
			insert_new_data_into_column($db, 'lundi_datas', htmlspecialchars(serialize($tracks['title'])), 'title');
			insert_new_data_into_column($db, 'lundi_datas', htmlspecialchars(serialize($tracks['startTime'])), 'startTime');
			insert_new_data_into_column($db, 'lundi_datas', htmlspecialchars(serialize($tracks['endTime'])), 'endTime');
			insert_new_data_into_column($db, 'lundi_datas', 'lundi', 'info');
			insert_new_data_into_column($db, 'lundi_datas', htmlspecialchars(serialize($tracks['category'])), 'type');
			break;
		case 'mardi.xml':
			array_push($mardi, $tracks['title'], $tracks['artist'], $tracks['category'], $tracks['startTime'], $tracks['endTime'], $keepinfos, 'mardi');
			insert_new_data_into_column($db, 'mardi_datas', htmlspecialchars(serialize($tracks['artist'])), 'artist');
			insert_new_data_into_column($db, 'mardi_datas', htmlspecialchars(serialize($tracks['title'])), 'title');
			insert_new_data_into_column($db, 'mardi_datas', htmlspecialchars(serialize($tracks['startTime'])), 'startTime');
			insert_new_data_into_column($db, 'mardi_datas', htmlspecialchars(serialize($tracks['endTime'])), 'endTime');
			insert_new_data_into_column($db, 'mardi_datas', 'mardi', 'info');
			insert_new_data_into_column($db, 'mardi_datas', htmlspecialchars(serialize($tracks['category'])), 'type');
			break;
		case 'mercredi.xml':
			array_push($mercredi, $tracks['title'], $tracks['artist'], $tracks['category'], $tracks['startTime'], $tracks['endTime'], $keepinfos, 'mercredi');
			insert_new_data_into_column($db, 'mercredi_datas', htmlspecialchars(serialize($tracks['artist'])), 'artist');
			insert_new_data_into_column($db, 'mercredi_datas', htmlspecialchars(serialize($tracks['title'])), 'title');
			insert_new_data_into_column($db, 'mercredi_datas', htmlspecialchars(serialize($tracks['startTime'])), 'startTime');
			insert_new_data_into_column($db, 'mercredi_datas', htmlspecialchars(serialize($tracks['endTime'])), 'endTime');
			insert_new_data_into_column($db, 'mercredi_datas', 'dmercredi', 'info');
			insert_new_data_into_column($db, 'mercredi_datas', htmlspecialchars(serialize($tracks['category'])), 'type');
			break;
		case 'jeudi.xml':
			array_push($jeudi, $tracks['title'], $tracks['artist'], $tracks['category'], $tracks['startTime'], $tracks['endTime'], $keepinfos, 'jeudi');
			insert_new_data_into_column($db, 'jeudi_datas', htmlspecialchars(serialize($tracks['artist'])), 'artist');
			insert_new_data_into_column($db, 'jeudi_datas', htmlspecialchars(serialize($tracks['title'])), 'title');
			insert_new_data_into_column($db, 'jeudi_datas', htmlspecialchars(serialize($tracks['startTime'])), 'startTime');
			insert_new_data_into_column($db, 'jeudi_datas', htmlspecialchars(serialize($tracks['endTime'])), 'endTime');
			insert_new_data_into_column($db, 'jeudi_datas', 'jeudi', 'info');
			insert_new_data_into_column($db, 'jeudi_datas', htmlspecialchars(serialize($tracks['category'])), 'type');
			break;
		case 'vendredi.xml':
			array_push($vendredi, $tracks['title'], $tracks['artist'], $tracks['category'], $tracks['startTime'], $tracks['endTime'], $keepinfos, 'vendredi');
			insert_new_data_into_column($db, 'vendredi_datas', htmlspecialchars(serialize($tracks['artist'])), 'artist');
			insert_new_data_into_column($db, 'vendredi_datas', htmlspecialchars(serialize($tracks['title'])), 'title');
			insert_new_data_into_column($db, 'vendredi_datas', htmlspecialchars(serialize($tracks['startTime'])), 'startTime');
			insert_new_data_into_column($db, 'vendredi_datas', htmlspecialchars(serialize($tracks['endTime'])), 'endTime');
			insert_new_data_into_column($db, 'vendredi_datas', 'vendredi', 'info');
			insert_new_data_into_column($db, 'vendredi_datas', htmlspecialchars(serialize($tracks['category'])), 'type');
			break;
		case 'samedi.xml':
			array_push($samedi, $tracks['title'], $tracks['artist'], $tracks['category'], $tracks['startTime'], $tracks['endTime'], $keepinfos, 'samedi');
			insert_new_data_into_column($db, 'samedi_datas', htmlspecialchars(serialize($tracks['artist'])), 'artist');
			insert_new_data_into_column($db, 'samedi_datas', htmlspecialchars(serialize($tracks['title'])), 'title');
			insert_new_data_into_column($db, 'samedi_datas', htmlspecialchars(serialize($tracks['startTime'])), 'startTime');
			insert_new_data_into_column($db, 'samedi_datas', htmlspecialchars(serialize($tracks['endTime'])), 'endTime');
			insert_new_data_into_column($db, 'samedi_datas', 'samedi', 'info');
			insert_new_data_into_column($db, 'samedi_datas', htmlspecialchars(serialize($tracks['category'])), 'type');
			break;
		case 'dimanche.xml':
			array_push($dimanche, $tracks['title'], $tracks['artist'], $tracks['category'], $tracks['startTime'], $tracks['endTime'], $keepinfos, 'dimanche');
			insert_new_data_into_column($db, 'dimanche_datas', htmlspecialchars(serialize($tracks['artist'])), 'artist');
			insert_new_data_into_column($db, 'dimanche_datas', htmlspecialchars(serialize($tracks['title'])), 'title');
			insert_new_data_into_column($db, 'dimanche_datas', htmlspecialchars(serialize($tracks['startTime'])), 'startTime');
			insert_new_data_into_column($db, 'dimanche_datas', htmlspecialchars(serialize($tracks['endTime'])), 'endTime');
			insert_new_data_into_column($db, 'dimanche_datas', 'dimanche', 'info');
			insert_new_data_into_column($db, 'dimanche_datas', htmlspecialchars(serialize($tracks['category'])), 'type');
			//d('dimanche : \n '.$dimanche);
			$sunday_loaded = true;
			break;
		
		default:
			//d('error xml switch xml name data split');
			break;
	}

	
	
}

function to_array($db_data){
	echo ('echo 0' . $db_data.'\n-------------------------------------\n');
	//echo ('speciallchars : '.htmlspecialchars($db_data));
	//$pattern = '/(\\".*?\\")/';
	$matches = array();
	preg_match_all("/(\&quot;.*?\&quot;)/", $db_data, $matches);
	//d('Resultat to_array :');
	//var_dump($matches);
	/*if(count($matches[0])>0){
		for ($cpt = 0; $cpt<count($matches[0]); $cpt++){
			//d('before str_replace, cpt : '.$cpt.' $matches[0][$cpt] : '. $matches[0][$cpt]);
			str_replace("&quot;", "", $matches[0][$cpt]);
			str_replace('"', '', $matches[0][$cpt]);
			//d('after str_replace, cpt : '.$cpt.' $matches[0][$cpt] : '. $matches[0][$cpt]);
		}		
	}
	//print_r($matches[0]);*/
	return $matches[0];
}

function sort_datas(){
	global $day;
	global $lundi;
	global $mardi;
	global $mercredi;
	global $jeudi;
	global $vendredi;
	global $samedi;
	global $dimanche;
	global $no_updates;
	global $db;
	// on verifie les fichiers qui ont changé si ce n'est pas le cas on va récupérer les anciennes valeurs dans la bdd
	if (count($no_updates)>0){ // si il y a eu au moins un fichier qui n'a pas été modifié
		for ($nb_files=0; $nb_files<count($no_updates); $nb_files++){
			switch ($no_updates[$nb_files]){
				case 'lundi':
					//d('aucune modif sur le fichier lundi.xml retour de la requete : '.query_data_from_db($db,'rbn_db.db','artist', 'lundi_datas'));
					//$lundi = to_array(query_data_from_db($db,'rbn_db.db','artist', 'lundi_datas'));
					array_push($lundi, 
							to_array(query_data_from_db($db,'rbn_db.db','title', 'lundi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','artist', 'lundi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','type', 'lundi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','startTime', 'lundi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','endTime', 'lundi_datas')),
							to_array(query_data_from_db($db,'rbn_db.db','info', 'lundi_datas')),
							'lundi'
						);
				break;
				case 'mardi':
					array_push($mardi, 
							to_array(query_data_from_db($db,'rbn_db.db','title', 'mardi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','artist', 'mardi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','type', 'mardi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','startTime', 'mardi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','endTime', 'mardi_datas')),
							to_array(query_data_from_db($db,'rbn_db.db','info', 'mardi_datas')),
							'mardi'
						);
				break;
				case 'mercredi':
					array_push($mercredi, 
							to_array(query_data_from_db($db,'rbn_db.db','title', 'mercredi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','artist', 'mercredi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','type', 'mercredi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','startTime', 'mercredi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','endTime', 'mercredi_datas')),
							to_array(query_data_from_db($db,'rbn_db.db','info', 'mercredi_datas')),
							'mercredi'
						);
				break;
				case 'jeudi':
					array_push($jeudi, 
							to_array(query_data_from_db($db,'rbn_db.db','title', 'jeudi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','artist', 'jeudi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','type', 'jeudi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','startTime', 'jeudi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','endTime', 'jeudi_datas')),
							to_array(query_data_from_db($db,'rbn_db.db','info', 'jeudi_datas')),
							'jeudi'
						);
				break;
				case 'vendredi':
					array_push($vendredi, 
							to_array(query_data_from_db($db,'rbn_db.db','title', 'vendredi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','artist', 'vendredi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','type', 'vendredi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','startTime', 'vendredi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','endTime', 'vendredi_datas')),
							to_array(query_data_from_db($db,'rbn_db.db','info', 'vendredi_datas')),
							'vendredi'
						);
				break;
				case 'samedi':
					array_push($samedi, 
							to_array(query_data_from_db($db,'rbn_db.db','title', 'samedi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','artist', 'samedi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','type', 'samedi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','startTime', 'samedi_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','endTime', 'samedi_datas')),
							to_array(query_data_from_db($db,'rbn_db.db','info', 'samedi_datas')),
							'samedi'
						);
				break;
				case 'dimanche':
					array_push($dimanche, 
							to_array(query_data_from_db($db,'rbn_db.db','title', 'dimanche_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','artist', 'dimanche_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','type', 'dimanche_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','startTime', 'dimanche_datas')), 
							to_array(query_data_from_db($db,'rbn_db.db','endTime', 'dimanche_datas')),
							to_array(query_data_from_db($db,'rbn_db.db','info', 'dimanche_datas')),
							'dimanche'
						);
				break;
			}
		}
	}

	$program_datas = array();

	switch (strtolower($day)){
		case 'monday':
			array_push($program_datas, $lundi, $mardi, $mercredi, $jeudi, $vendredi, $samedi, $dimanche);
			break;
		case 'tuesday':
			array_push($program_datas, $mardi, $mercredi, $jeudi, $vendredi, $samedi, $dimanche, $lundi);
			break;
		case 'wednesday':
			array_push($program_datas, $mercredi, $jeudi, $vendredi, $samedi, $dimanche, $lundi, $mardi);
			break;
		case 'thursday':
			array_push($program_datas, $jeudi, $vendredi, $samedi, $dimanche, $lundi, $mardi, $mercredi);
			break;
		case 'friday':
			array_push($program_datas, $vendredi, $samedi, $dimanche, $lundi, $mardi, $mercredi, $jeudi);
			break;
		case 'saturday':
			array_push($program_datas, $samedi, $dimanche, $lundi, $mardi, $mercredi, $jeudi, $vendredi);
			break;
		case 'sunday':
			array_push($program_datas, $dimanche, $lundi, $mardi, $mercredi, $jeudi, $vendredi, $samedi);
			break;
		
		default:
			//d('no valid day from datetime default value : Monday case');
			array_push($program_datas, $lundi, $mardi, $mercredi, $jeudi, $vendredi, $samedi, $dimanche);
			break;
	}
	//d('PROGRAMS DATAS FINISH : ');
	var_dump($program_datas);
	createXML($program_datas);
}


// ----------------------------------------


function createXML($array) {
	global $filePath, $filename;

	echo 'creating xml';

	$xml = new DomDocument;
	$xml->preserveWhiteSpace = true;
	$xml->formatOutput = true;



	$root = $xml->createElement('root');
	$baliseInfo = $xml->createElement('info');
	//$root->appendChil//d( $baliseInfo );

	for ($i = 0; $i< count($array); $i++) {
		$baliseInfo = $xml->createElement('info');
		$baliseday = $xml->createElement('day');
		$baliseday->appendChild($xml->createCDATASection($array[$i][6]));
		$baliseInfo->appendChild($baliseday);
		for ($j = 0; $j< count($array[$i][1]); $j++){
			$balisetrack = $xml->createElement('track');
			/*$balisedate = $xml->createElement('date');
			$balisedate->appendChil//d($xml->createCDATASection(str_replace('&quot;','',$array[$i][5][0])));*/
			$balisestart = $xml->createElement('starttime');
			$balisestart->appendChild($xml->createCDATASection((str_replace('&quot;','',$array[$i][3][$j]))));
			$baliseend = $xml->createElement('endtime');
			$baliseend->appendChild($xml->createCDATASection((str_replace('&quot;','',$array[$i][4][$j]))));
			$balisetitre = $xml->createElement('titre');
			$balisetitre->appendChild($xml->createCDATASection(str_replace('&quot;','',$array[$i][0][$j])));
			$baliseartist = $xml->createElement('artiste');
			$baliseartist->appendChild($xml->createCDATASection(str_replace('&quot;','',$array[$i][1][$j])));
			$balisecategory = $xml->createElement('category');
			$balisecategory->appendChild($xml->createCDATASection(str_replace('&quot;','',$array[$i][2][$j])));
			$baliselink = $xml->createElement('link');
			$baliselink->appendChild($xml->createCDATASection(clean_url_name(str_replace('&quot;','',$array[$i][2][$j])).'/'.clean_url_name(str_replace('&quot;','',$array[$i][1][$j])).'_-_'.clean_url_name(str_replace('&quot;','',$array[$i][0][$j])).'.mp3'));
			//$balisetrack->appendChil//d($balisedate);'http://www.v2-rbn.fr/tmp/'.

			$balisetrack->appendChild($balisestart);
			$balisetrack->appendChild($baliseend);
			$balisetrack->appendChild($balisetitre);
			$balisetrack->appendChild($baliseartist);
			$balisetrack->appendChild($balisecategory);
			$balisetrack->appendChild($baliselink);
			$baliseInfo->appendChild($balisetrack);
		}
		$root->appendChild( $baliseInfo);
	}

	//$baliseFont = $xml->createElement('font', 'tataalaplage');
	//$root->appendChil//d( $baliseFont );
	$xml->appendChild($root);

	// //d($root);'pouet.xml'

	////print $xml->saveXML();
	unlink('../programme.xml');
	$xml->save('programme_test.xml');
	$xml->save('../programme.xml');
	//exec('chmod 777 ' . 'programme_test.xml');
}

function clean_url_name($stringValue){
	$stringValue = str_replace(' ', '_', $stringValue);
	$stringValue = str_replace('.', '_', $stringValue);
	$stringValue = str_replace(':', '_', $stringValue);
	$stringValue = str_replace(';', '_', $stringValue);
	$stringValue = str_replace("'", '_', $stringValue);
	$stringValue = str_replace('"', '_', $stringValue);
	$stringValue = str_replace('!', '_', $stringValue);
	$stringValue = str_replace('?', '_', $stringValue);
	$stringValue = str_replace('(', '_', $stringValue);
	$stringValue = str_replace(')', '_', $stringValue);
	$stringValue = str_replace(',', '_', $stringValue);
	$stringValue = str_replace('´', '_', $stringValue);
	$stringValue = str_replace('…', '_', $stringValue);
	$stringValue = str_replace('/', '_', $stringValue);
	$stringValue = str_replace('°', '_', $stringValue);
    $stringValue = preg_replace('#Ç#', 'C', $stringValue);
    $stringValue = preg_replace('#ç#', 'c', $stringValue);
    $stringValue = preg_replace('#è|é|ê|ë#', 'e', $stringValue);
    $stringValue = preg_replace('#È|É|Ê|Ë#', 'E', $stringValue);
    $stringValue = preg_replace('#à|á|â|ã|ä|å#', 'a', $stringValue);
    $stringValue = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $stringValue);
    $stringValue = preg_replace('#ì|í|î|ï#', 'i', $stringValue);
    $stringValue = preg_replace('#Ì|Í|Î|Ï#', 'I', $stringValue);
    $stringValue = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $stringValue);
    $stringValue = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $stringValue);
    $stringValue = preg_replace('#ù|ú|û|ü#', 'u', $stringValue);
    $stringValue = preg_replace('#Ù|Ú|Û|Ü#', 'U', $stringValue);
    $stringValue = preg_replace('#ý|ÿ#', 'y', $stringValue);
    $stringValue = preg_replace('#Ý#', 'Y', $stringValue);

	return $stringValue;
}


function get_end_time($heure1,$heure2){
	$secondes1=heure_to_secondes($heure1);
	$secondes2=heure_to_secondes($heure2);
	$somme=$secondes1+$secondes2;
	//transfo en h:i:s
	$s=$somme % 60; //reste de la division en minutes => secondes
	$m1=($somme-$s) / 60; //minutes totales
	$m=$m1 % 60;//reste de la division en heures => minutes
	$h=($m1-$m) / 60; //heures
	if ($h>=24) {
		$h = $h % 24;
	}
	$resultat=convert_time_unity($h).":".convert_time_unity($m).":".convert_time_unity($s);
	return $resultat;
}

function heure_to_secondes($heure){
	$array_heure=explode(":",$heure);
	$secondes=3600*$array_heure[0]+60*$array_heure[1]+$array_heure[2];
	return $secondes;
}
function secondes_to_heure($heure1){
	//transfo en h:i:s
	$s=$heure1 % 60; //reste de la division en minutes => secondes
	$m1=($heure1-$s) / 60; //minutes totales
	$m=$m1 % 60;//reste de la division en heures => minutes
	$h=($m1-$m) / 60; //heures
	if ($h>=24) {
		$h = $h % 24;
	}
	$resultat=convert_time_unity($h).":".convert_time_unity($m).":".convert_time_unity($s);
	return $resultat;
}
function convert_time_unity($value){
	if ($value <10)
	{
		$returned_value = '0'.$value;
	} else {
		$returned_value = $value;

	}

	return $returned_value;
}

function d($var) {
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}

function dd($var) {
	d($var);
	exit;
}

function showError() {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}