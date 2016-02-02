<?
header( 'Content-type: text/html; charset=utf-8' );
error_reporting(E_ALL & ~E_NOTICE);


foreach (glob("*.mp4") as $filename) {

	$q = "ffmpeg -i \"{$filename}\" -vf blackdetect=d=0:pic_th=0.70:pix_th=0.10 -an -f null - 2> log.txt";
	system($q, $r);
	
	$myfile = fopen("log.txt", "r") or die("Unable to open file!");
	$read = fread($myfile,filesize("log.txt"));
	fclose($myfile);
	
	$blackStart =  getArray($read, "black_start:", "black_end:");
	$blackEnd =  getArray($read, "black_end:", "black_duration:");
	$videoDuration =  trim(getOne($read, "Duration:", ","));
	
	$siyahBolumler = Array();
	
	for  ($i=0; $i<count($blackStart)-1; $i++ ){

		$siyahBolumler[$i]['baslangic'] = $blackStart[$i];
		$siyahBolumler[$i]['bitis'] = $blackEnd[$i];
		$siyahBolumler[$i]['sure'] = $videoDuration;

	}
	
	if ( count($siyahBolumler) == 1 ){
		
		$basla = $siyahBolumler[0]['baslangic'];
		$bitis = $siyahBolumler[0]['bitis'];

		if ($basla == 0 ) {
			$basla = "00:00:00";
		} else {
			$exp = explode('.',$basla);
			$basla = "00:00:".sprintf("%02d", $exp[0]).".".$exp[1];
		}
			

		if ( bastami($basla) ){
			$q = "ffmpeg -i \"{$filename}\" -ss 00:00:00 -t {$basla} -async 1 converted/\"{$filename}\"";			
		}else {
			$q = "ffmpeg -i \"{$filename}\" -ss {$bitis} -t {$videoDuration} -async 1 converted/\"{$filename}\"";	
		}
		
		
		system($q);
		echo "{$filename} - Bu dosyada 1 siyah frame bulundu ve silindi. <hr>";		

		
	} elseif ( count($siyahBolumler) == 2) {
		
		
		$basla1 = $siyahBolumler[0]['baslangic'];
		$bitis1 = $siyahBolumler[0]['bitis'];	
		
		$basla2 = $siyahBolumler[1]['baslangic'];
		$bitis2 = $siyahBolumler[1]['bitis'];

		if ($basla1 == 0 ) {
			$basla1 = "00:00:00";
		} else {
			$exp = explode('.',$basla1);
			$basla1 = "00:00:".sprintf("%02d", $exp[0]).".".$exp[1];
		}
		
		$q = "ffmpeg -i \"{$filename}\" -ss {$bitis1} -t ".($basla2-$bitis1)." -async 1 converted/\"{$filename}\"";			
		system($q);
			
		echo "{$filename} - Bu dosyada 2 siyah frame bulundu ve silindi. <hr>";		


	} else {
		echo "{$filename} - Bu dosyada siyah frame bulunamadı. <hr>";

	}
	
	ob_flush();
	flush();	
		
}




function encodeURI($uri)
{
    return preg_replace_callback("{[^0-9a-z_.!~*'();,/?:@&=+$#-]}i", function ($m) {
        return sprintf('%%%02X', ord($m[0]));
    }, $uri);
}

function getArray($kaynak, $ref_bas, $ref_son){
	$cikti_bas=explode($ref_bas,$kaynak);
	
	for($a=1;$a<count($cikti_bas);$a++){
		$cikti_son[] = explode($ref_son,$cikti_bas[$a]);
	}

	for($b=0;$b<count($cikti_bas);$b++){
		$sonuc[$b] = $cikti_son[$b][0];
	}

	if(empty($sonuc)){
	die("Dizi Alınamadı.");
	}else{
	return $sonuc;
	}
}

function getOne($kaynak, $ref_bas, $ref_son){
	
	$cikti_bas = explode($ref_bas,$kaynak);
	$cikti_son = explode($ref_son,$cikti_bas[1]);
	
	return $cikti_son[0];
}


function bastami($time){
	$exp = explode(':',$time);
	
	if ( $exp[2] > 3){
		return true;
	} else{
		return false;
	}
}
