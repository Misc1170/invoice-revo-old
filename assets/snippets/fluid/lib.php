<?php
$GLOBALS['companyIP']['94.159.54.30'] = 'fl';
$GLOBALS['companyIP']['62.105.33.210'] = 'fl';
$GLOBALS['companyIP']['62.117.119.177'] = 'fl';
$GLOBALS['companyIP']['62.117.119.176'] = 'fl';
$GLOBALS['companyIP']['62.117.119.142'] = 'fl';
$GLOBALS['companyIP']['185.212.88.178'] = 'fl';
$GLOBALS['companyIP']['77.108.67.26'] = 'fl';
$GLOBALS['companyIP']['62.105.43.74'] = 'fl';
$GLOBALS['companyIP']['62.105.43.75'] = 'fl';
$GLOBALS['companyIP']['62.105.43.76'] = 'fl';
$GLOBALS['companyIP']['62.105.43.77'] = 'fl';
$GLOBALS['companyIP']['62.105.43.78'] = 'fl';
$GLOBALS['companyIP']['127.0.0.1'] = 'fl';
$GLOBALS['companyIP']['192.168.0.197'] = 'fl';
$GLOBALS['companyIP']['192.168.0.156'] = 'fl';
$GLOBALS['companyIP']['192.168.0.185'] = 'fl';
$GLOBALS['companyIP']['178.140.155.146'] = 'm';

/* позволяет менять параметры url
 * пример: sgp($_SERVER['REQUEST_URI'], 'page', '21') - вернет ссылку установит параметр page равным 21
 */
function sgp($url, $varname, $value) // substitute get parameter
{
     if (is_array($varname)) {
         foreach ($varname as $i => $n) {
            $v = (is_array($value))
                  ? ( isset($value[$i]) ? $value[$i] : NULL )
                  : $value;
            $url = sgp($url, $n, $v);
         }
         return $url;
     }
     
    preg_match('/^([^?]+)(\?.*?)?(#.*)?$/', $url, $matches);
    $gp = (isset($matches[2])) ? $matches[2] : ''; // GET-parameters
    if (!$gp) return $url .'?'.$varname.'='.$value;
   
    $pattern = "/([?&])$varname=.*?(?=&|#|\z)/";
    if (preg_match($pattern, $gp)) {
        $substitution = ($value !== '') ? "\${1}$varname=" . preg_quote($value) : '';
        $newgp = preg_replace($pattern, $substitution, $gp); // new GET-parameters
        $newgp = preg_replace('/^&/', '?', $newgp);
        $newgp = str_replace('\\','', $newgp); // убираем \ из значения переменной
    }
    else    {
        $s = ($gp) ? '&' : '?';
        $newgp = $gp.$s.$varname.'='.$value;
    }
   
    $anchor = (isset($matches[3])) ? $matches[3] : '';
    $newurl = $matches[1].$newgp.$anchor;
    return $newurl;
}

/**
 * @param num $type
 *	1 - информационное
 *	2 - предупреждение (warning) + запись в логи
 *	3 - ошибка + запись в логи
 * @param string $userText
 * @param string $logText
 * @return string
 */
function errorDetect($type, $userText, $logText = false){
	global $modx;
	if(empty($type) || empty($userText))
		return;
	if(empty($logText))
		$logText = $userText;
	
	//выводим на странице сообщение для менеджера
	if(isManager())
		$userText = $logText;
	
	switch($type){
		case 2: echo '<div style="padding: 10px; margin: 5px; border: 1px solid red; font-weight: bold; font-size: 13px;">'.$userText.'</div>'; break;
		case 3: echo '<div style="padding: 10px; margin:5px; border: 1px solid red; font-weight: bold; font-size: 13px;">'.$userText.'</div>'; break;
		default: echo '<div style="padding: 10px; margin: 5px; border: 1px solid orange; font-weight: bold; font-size: 13px;">'.$userText.'</div>'; break;
	}
	
	if ($logText && ($type == 2 || $type == 3) )
	{
		$logTitle = 'Ошибка <b>'.$modx->getSnippetName().'</b> '. $modx->documentObject["id"].' | '. $modx->documentObject["pagetitle"];
        $logText .='<hr/>
        <b>URL</b>: '.$_SERVER['REQUEST_URI'].'<br>
        <b>Пользователь</b>:'. $_SERVER['HTTP_USER_AGENT'] .'<br/>
        <b>IP</b>: '.$_SERVER['REMOTE_ADDR'];

		$modx->logEvent(0, $type, $logText, $logTitle);
	}
	
	//прекращаем выволнение сниппета (КАК?)
}

function isManager(){
	if (isset($GLOBALS['admin']))
		return $GLOBALS['admin'];
	if ($_SESSION['usertype'] == 'manager'){
		$GLOBALS['admin'] = true;
	} else {
		if(isset($GLOBALS['companyIP'][$_SERVER[REMOTE_ADDR]]))
			$GLOBALS['admin'] = true;
		else
			$GLOBALS['admin'] = false;
	}
	return $GLOBALS['admin'];
}

function DetectSearchEngine(){
    $engines = array(
        array('Aport', 'Aport'),
        array('Google', 'Google'),
        array('msnbot', 'MSN'),
        array('Rambler', 'Rambler'),
        array('Yahoo', 'Yahoo'),
        array('Yandex', 'Yandex'),
        array('Aport', 'Aport robot'),
        array('Google', 'Google'),
        array('msnbot', 'MSN'),
        array('Rambler', 'Rambler'),
        array('Yahoo', 'Yahoo'),
        array('AbachoBOT', 'AbachoBOT'),
        array('accoona', 'Accoona'),
        array('AcoiRobot', 'AcoiRobot'),
        array('ASPSeek', 'ASPSeek'),
        array('CrocCrawler', 'CrocCrawler'),
        array('Dumbot', 'Dumbot'),
        array('FAST-WebCrawler', 'FAST-WebCrawler'),
        array('GeonaBot', 'GeonaBot'),
        array('Gigabot', 'Gigabot'),
        array('Lycos', 'Lycos spider'),
        array('MSRBOT', 'MSRBOT'),
        array('Scooter', 'Altavista robot'),
        array('AltaVista', 'Altavista robot'),
        array('WebAlta', 'WebAlta'),
        array('IDBot', 'ID-Search Bot'),
        array('eStyle', 'eStyle Bot'),
        array('Mail.Ru', 'Mail.Ru Bot'),
        array('Scrubby', 'Scrubby robot'),
        array('DotBot', 'DotBot'),
        array('AhrefsBot', 'AhrefsBot'),
        array('Bot', 'Bot'),
        array('bot', 'bot'),
        array('Java', 'Java'),
        array('spider', 'spider'),
        array('bingbot', 'bingbot')
    );

    foreach ($engines as $engine)
    {
        if (stristr($_SERVER['HTTP_USER_AGENT'], $engine[0]))
        {
            return($engine[1]);
        }
    }

    if( empty($_SERVER['HTTP_USER_AGENT']) )
        return 'Пустой USER_AGENT';
    if( $_SERVER['HTTP_USER_AGENT'] == 'compatible' )
        return 'compatible';
    if( $_SERVER['HTTP_USER_AGENT'] == 'ia_archiver' )
        return 'archiver';
    if( $_SERVER['HTTP_USER_AGENT'] == 'Windows NT 6.1' )
        return 'Windows NT 6.1';
    if( $_SERVER['HTTP_USER_AGENT'] == 'Windows NT 5.1' )
        return 'Windows NT 5.1';
    if( $_SERVER['HTTP_USER_AGENT'] == 'Windows NT 6.3' )
        return 'Windows NT 6.3';
    if( strpos($_SERVER['HTTP_USER_AGENT'], 'Java') === 0 )
        return 'Java<..>';
    if( strpos($_SERVER['HTTP_USER_AGENT'], 'Python') === 0 )
        return 'Python<..>';

    return (false);
}

//используем рекурсию, чтобы вытащить ближайших родителей детей(без дедушек и продедушек)
function getNearestParents($parent, $parents = ''){
	global $modx;
	
	if(strpos($parent, ',')){
		$parentArr = explode(',', $parent);
	}else{
		$parentArr[0] = $parent;
	}
	
	foreach( $parentArr as $val => $parentNum ){
		$res = $modx->db->query('SELECT `id` FROM `modx_site_content` WHERE `parent` = '. $parentNum .' AND `isfolder` = 1 LIMIT 50');
		if($modx->db->getRecordCount($res) != 0){
			//проходимся по всем веткам родителя
			while( $row = $modx->db->getRow($res) ){
				//проверяем наличие подпапок
				$hasParents = $modx->db->query('SELECT 1 FROM `modx_site_content` WHERE `parent` = '. $row[id] .' AND `isfolder` = 1 LIMIT 1');
				if( $modx->db->getRecordCount($hasParents) == 0 ){
					$parents .= $row['id'] .',';
				}else{
					$parentsWithParents .= $row['id'] .',';
				}
			}
		}else{
			$parents .= $parent .',';
		}
	}
	
	$parentsWithParents = rtrim($parentsWithParents, ',');
	
	if(empty($parentsWithParents)){
		return rtrim($parents, ',');
	}else{
		return getNearestParents($parentsWithParents, $parents);
	}
}

//используем рекурсию, чтобы вытащить все страницы(опубликованные) у родителя
function getAllPages($parent, $parents = ''){
    global $modx;
    if(empty($parents))
        $parents = $parent.',';

    if(strpos($parent, ',')){
        $parentArr = explode(',', $parent);
    }else{
        $parentArr[0] = $parent;
    }

    foreach( $parentArr as $val => $parentNum ){
        $res = $modx->db->query('SELECT `id` FROM `modx_site_content` WHERE `parent` = '. $parentNum .' AND `isfolder` = 1 AND `template` NOT IN(16,91,104) LIMIT 50');
        if($modx->db->getRecordCount($res) != 0){
            //проходимся по всем веткам родителя
            while( $row = $modx->db->getRow($res) ){
                //проверяем наличие подпапок
                $hasParents = $modx->db->query('SELECT 1 FROM `modx_site_content` WHERE `parent` = '. $row[id] .' AND `isfolder` = 1 LIMIT 1');
                if( $modx->db->getRecordCount($hasParents) == 0 ){
                    $parents .= $row['id'] .',';
                }else{
                    $parents .= $row['id'] .',';
                    $parentsWithParents .= $row['id'] .',';
                }
            }
        }else{
            $parents .= $parent .',';
        }
    }

    $parentsWithParents = rtrim($parentsWithParents, ',');

    if(empty($parentsWithParents)){
        return rtrim($parents, ',');
    }else{
        return getAllPages($parentsWithParents, $parents);
    }
}

//записываем данные в файл, указывая максимальное количество строк
function saveToFile($filename, $dataline, $maxrecords = 60){
    if(!file_exists($filename))
        file_put_contents($filename,'');

    $file = file($filename);

    while(count($file) > $maxrecords)
        array_shift($file);
    $file[] = "\n".$dataline;
    file_put_contents($filename, $file);
}

//ищет картинку для ID товара
function getProductImage($id){
    global $modx;
    $plug = 'https://www.fluid-line.ru/images/icons/Image_Capture_26240(1).png';

    //узнаем внешний это документ или нет
    $query = $modx->db->query('SELECT `foreignTable` FROM `modx_site_content` WHERE `id` = ' . $id);
    $row = $modx->db->getRow($query);
    if ($row['foreignTable'] == 1)
        $table = 'product_tmplvar_contentvalues';
    else
        $table = 'modx_site_tmplvar_contentvalues';
    $query = $modx->db->query('SELECT `value` FROM `' . $table . '` WHERE `contentid` = ' . $id . ' AND `tmplvarid` = 6');
    if ($modx->db->getRecordCount($query) != 1){
        //ищем кратинку у родителей
        $query = $modx->db->query('SELECT `parent` FROM `modx_site_content` WHERE `id` = '. $id);
        if ($modx->db->getRecordCount($query) != 1)
            return $plug;
        $parent = current($modx->db->getRow($query));
        $query = $modx->db->query('SELECT `value` FROM `modx_site_tmplvar_contentvalues` WHERE `contentid` = '.$parent.' AND `tmplvarid` = 6');
        $image = $modx->db->getRow($query);
        if(!empty($image))
            return $image['value'];

        //ищем кратинку у деда
        $query = $modx->db->query('SELECT `parent` FROM `modx_site_content` WHERE `id` = '. $parent);
        if ($modx->db->getRecordCount($query) != 1)
            return $plug;
        $grandfather  = current($modx->db->getRow($query));
        $query = $modx->db->query('SELECT `value` FROM `modx_site_tmplvar_contentvalues` WHERE `contentid` = '.$grandfather.' AND `tmplvarid` = 6');
        $image = $modx->db->getRow($query);
        if(!empty($image))
            return $image['value'];

        //ищем кратинку у прадеда
        $query = $modx->db->query('SELECT `parent` FROM `modx_site_content` WHERE `id` = '. $grandfather);
        if ($modx->db->getRecordCount($query) != 1)
            return $plug;
        $greatGrandfather  = current($modx->db->getRow($query));
        $query = $modx->db->query('SELECT `value` FROM `modx_site_tmplvar_contentvalues` WHERE `contentid` = '. $greatGrandfather.' AND `tmplvarid` = 6');
        $image = $modx->db->getRow($query);
        if(!empty($image))
            return $image['value'];
        return $plug;
    }else{
        $imageArr = $modx->db->getRow($query);
        $image = $imageArr['value'];


        //если таблица внешняя, мы выбрали не значение а ссылку на него. Получ знач.
        if($row['foreignTable'] == 1){
            $query = $modx->db->query('SELECT `value` FROM `product_tmplvar_data` WHERE `id` = '.(int)$image);
            $row = $modx->db->getRow($query);
            $image = $row['value'];
            if(empty($image))
                $image = $plug;
        }
    }

    return $image;
}
