<?php

date_default_timezone_set('PRC');

define('SPRITE_Z', 128);/* size of each sprite */


/** 
 * generate sprite for corners and sides
 * @param $shape 图像类型 值范围：0-15
 * @param $R 红 值范围：0-255
 * @param $G 绿 值范围：0-255
 * @param $B 蓝 值范围：0-255
 * @param $rotation 初始旋转次数 值范围：0-3
 * @param $width_height 图像宽高 
 * @return resource
 */
function getsprite($shape,$R,$G,$B,$rotation,$width_height ) {
	$sprite=imagecreatetruecolor($width_height,$width_height);
	imageantialias($sprite,TRUE);
	$fg=imagecolorallocate($sprite,$R,$G,$B);
	$bg=imagecolorallocate($sprite,255,255,255);
	imagefilledrectangle($sprite,0,0,$width_height,$width_height,$bg);
	switch($shape) {
		case 0: // triangle 三角形
			$shape=array(
				0.5,1,
				1,0,
				1,1
			);
			break;
		case 1: // parallelogram 平行四边形
			$shape=array(
				0.5,0,
				1,0,
				0.5,1,
				0,1
			);
			break;
		case 2: // mouse ears 小鼠耳
			$shape=array(
				0.5,0,
				1,0,
				1,1,
				0.5,1,
				1,0.5
			);
			break;
		case 3: // ribbon 丝带
			$shape=array(
				0,0.5,
				0.5,0,
				1,0.5,
				0.5,1,
				0.5,0.5
			);
			break;
		case 4: // sails 帆
			$shape=array(
				0,0.5,
				1,0,
				1,1,
				0,1,
				1,0.5
			);
			break;
		case 5: // fins 鳍
			$shape=array(
				1,0,
				1,1,
				0.5,1,
				1,0.5,
				0.5,0.5
			);
			break;
		case 6: // beak 喙
			$shape=array(
				0,0,
				1,0,
				1,0.5,
				0,0,
				0.5,1,
				0,1
			);
			break;
		case 7: // chevron 雪佛龙
			$shape=array(
				0,0,
				0.5,0,
				1,0.5,
				0.5,1,
				0,1,
				0.5,0.5
			);
			break;
		case 8: // fish
			$shape=array(
				0.5,0,
				0.5,0.5,
				1,0.5,
				1,1,
				0.5,1,
				0.5,0.5,
				0,0.5
			);
			break;
		case 9: // kite 风筝
			$shape=array(
				0,0,
				1,0,
				0.5,0.5,
				1,0.5,
				0.5,1,
				0.5,0.5,
				0,1
			);
			break;
		case 10: // trough 水槽
			$shape=array(
				0,0.5,
				0.5,1,
				1,0.5,
				0.5,0,
				1,0,
				1,1,
				0,1
			);
			break;
		case 11: // rays 射线
			$shape=array(
				0.5,0,
				1,0,
				1,1,
				0.5,1,
				1,0.75,
				0.5,0.5,
				1,0.25
			);
			break;
		case 12: // double rhombus 双菱形
			$shape=array(
				0,0.5,
				0.5,0,
				0.5,0.5,
				1,0,
				1,0.5,
				0.5,1,
				0.5,0.5,
				0,1
			);
			break;
		case 13: // crown 王冠
			$shape=array(
				0,0,
				1,0,
				1,1,
				0,1,
				1,0.5,
				0.5,0.25,
				0.5,0.75,
				0,0.5,
				0.5,0.25
			);
			break;
		case 14: // radioactive 放射性的
			$shape=array(
				0,0.5,
				0.5,0.5,
				0.5,0,
				1,0,
				0.5,0.5,
				1,0.5,
				0.5,1,
				0.5,0.5,
				0,1
			);
			break;
		default: // tiles 瓷砖
			$shape=array(
				0,0,
				1,0,
				0.5,0.5,
				0.5,0,
				0,0.5,
				1,0.5,
				0.5,1,
				0.5,0.5,
				0,1
			);
			break;
	}
	/* apply ratios */
	for ($i=0;$i<count($shape);$i++)
		$shape[$i]=$shape[$i]*$width_height;
	imagefilledpolygon($sprite,$shape,count($shape)/2,$fg);
	/* rotate the sprite */
	for ($i=0;$i<$rotation;$i++)
		$sprite=imagerotate($sprite,90,$bg);
	return $sprite;
}

/**
 * generate sprite for center block
 * @param $shape 图像类型 值范围：0-7
 * @param $fR 红 值范围：0-255
 * @param $fG 绿 值范围：0-255
 * @param $fB 蓝 值范围：0-255
 * @param $bR 红 值范围：0-255
 * @param $bG 绿 值范围：0-255
 * @param $bB 蓝 值范围：0-255
 * @param $usebg 是否使用背景颜色
 * @param $width_height 图像宽高 
 * @return resource
 */
function getcenter($shape,$fR,$fG,$fB,$bR,$bG,$bB,$usebg,$width_height) {
	$sprite=imagecreatetruecolor($width_height,$width_height);
	imageantialias($sprite,TRUE);
	$fg=imagecolorallocate($sprite,$fR,$fG,$fB);
	/* make sure there's enough contrast before we use background color of side sprite */
	if ($usebg>0 && (abs($fR-$bR)>127 || abs($fG-$bG)>127 || abs($fB-$bB)>127)){
		$bg=imagecolorallocate($sprite,$bR,$bG,$bB);
	}else{
		$bg=imagecolorallocate($sprite,255,255,255);
	}
	imagefilledrectangle($sprite,0,0,$width_height,$width_height,$bg);
	switch($shape) {
		case 0: // empty
			$shape=array();
			break;
		case 1: // fill
			$shape=array(
				0,0,
				1,0,
				1,1,
				0,1
			);
			break;
		case 2: // diamond
			$shape=array(
				0.5,0,
				1,0.5,
				0.5,1,
				0,0.5
			);
			break;
		case 3: // reverse diamond
			$shape=array(
				0,0,
				1,0,
				1,1,
				0,1,
				0,0.5,
				0.5,1,
				1,0.5,
				0.5,0,
				0,0.5
			);
			break;
		case 4: // cross
			$shape=array(
				0.25,0,
				0.75,0,
				0.5,0.5,
				1,0.25,
				1,0.75,
				0.5,0.5,
				0.75,1,
				0.25,1,
				0.5,0.5,
				0,0.75,
				0,0.25,
				0.5,0.5
			);
			break;
		case 5: // morning star
			$shape=array(
				0,0,
				0.5,0.25,
				1,0,
				0.75,0.5,
				1,1,
				0.5,0.75,
				0,1,
				0.25,0.5
			);
			break;
		case 6: // small square
			$shape=array(
				0.33,0.33,
				0.67,0.33,
				0.67,0.67,
				0.33,0.67
			);
			break;
		case 7: // checkerboard
			$shape=array(
				0,0,
				0.33,0,
				0.33,0.33,
				0.66,0.33,
				0.67,0,
				1,0,
				1,0.33,
				0.67,0.33,
				0.67,0.67,
				1,0.67,
				1,1,
				0.67,1,
				0.67,0.67,
				0.33,0.67,
				0.33,1,
				0,1,
				0,0.67,
				0.33,0.67,
				0.33,0.33,
				0,0.33
			);
			break;
	}
	/* apply ratios */
	for ($i=0;$i<count($shape);$i++){
		$shape[$i]=$shape[$i]*$width_height;
	}
	if (count($shape)>0){
		imagefilledpolygon($sprite,$shape,count($shape)/2,$fg);
	}
	return $sprite;
}

function genIdenticon( $hash ,$size,$path=null ){
	/* parse hash string */

	$csh=hexdec(substr($hash,0,1)); // corner sprite shape
	$ssh=hexdec(substr($hash,1,1)); // side sprite shape
	$xsh=hexdec(substr($hash,2,1))&7; // center sprite shape

	$cro=hexdec(substr($hash,3,1))&3; // corner sprite rotation
	$sro=hexdec(substr($hash,4,1))&3; // side sprite rotation
	$xbg=hexdec(substr($hash,5,1))%2; // center sprite background

	/* corner sprite foreground color */
	$cfr=hexdec(substr($hash,6,2));
	$cfg=hexdec(substr($hash,8,2));
	$cfb=hexdec(substr($hash,10,2));

	/* side sprite foreground color */
	$sfr=hexdec(substr($hash,12,2));
	$sfg=hexdec(substr($hash,14,2));
	$sfb=hexdec(substr($hash,16,2));

	

	/* final angle of rotation */
	$angle=hexdec(substr($hash,18,2));

	/* start with blank 3x3 identicon */
	$identicon=imagecreatetruecolor(SPRITE_Z*3,SPRITE_Z*3);
	imageantialias($identicon,TRUE);

	/* assign white as background */
	$bg=imagecolorallocate($identicon,255,255,255);
	imagefilledrectangle($identicon,0,0,SPRITE_Z,SPRITE_Z,$bg);

	/* generate corner sprites */
	$corner=getsprite($csh,$cfr,$cfg,$cfb,$cro,SPRITE_Z);
	imagecopy($identicon,$corner,0,0,0,0,SPRITE_Z,SPRITE_Z);
	$corner=imagerotate($corner,90,$bg);
	imagecopy($identicon,$corner,0,SPRITE_Z*2,0,0,SPRITE_Z,SPRITE_Z);
	$corner=imagerotate($corner,90,$bg);
	imagecopy($identicon,$corner,SPRITE_Z*2,SPRITE_Z*2,0,0,SPRITE_Z,SPRITE_Z);
	$corner=imagerotate($corner,90,$bg);
	imagecopy($identicon,$corner,SPRITE_Z*2,0,0,0,SPRITE_Z,SPRITE_Z);

	/* generate side sprites */
	$side=getsprite($ssh,$sfr,$sfg,$sfb,$sro,SPRITE_Z);
	imagecopy($identicon,$side,SPRITE_Z,0,0,0,SPRITE_Z,SPRITE_Z);
	$side=imagerotate($side,90,$bg);
	imagecopy($identicon,$side,0,SPRITE_Z,0,0,SPRITE_Z,SPRITE_Z);
	$side=imagerotate($side,90,$bg);
	imagecopy($identicon,$side,SPRITE_Z,SPRITE_Z*2,0,0,SPRITE_Z,SPRITE_Z);
	$side=imagerotate($side,90,$bg);
	imagecopy($identicon,$side,SPRITE_Z*2,SPRITE_Z,0,0,SPRITE_Z,SPRITE_Z);

	/* generate center sprite */
	$center=getcenter($xsh,$cfr,$cfg,$cfb,$sfr,$sfg,$sfb,$xbg,SPRITE_Z);
	imagecopy($identicon,$center,SPRITE_Z,SPRITE_Z,0,0,SPRITE_Z,SPRITE_Z);

	/* make white transparent */
	imagecolortransparent($identicon,$bg);

	$identicon=imagerotate($identicon,$angle,$bg);

	/* create blank image according to specified dimensions */
	$resized=imagecreatetruecolor($size,$size);
	imageantialias($resized,TRUE);

	/* assign white as background */
	$bg=imagecolorallocate($resized,255,255,255);
	imagefilledrectangle($resized,0,0,$size,$size,$bg);

	/* resize identicon according to specification */
	imagecopyresampled($resized,$identicon,0,0,(imagesx($identicon)-SPRITE_Z*3)/2,(imagesx($identicon)-SPRITE_Z*3)/2,$size,$size,SPRITE_Z*3,SPRITE_Z*3);

	imagedestroy($identicon);
	
	/* make white transparent */
	imagecolortransparent($resized,$bg);
	//imagecolortransparent($resized,imagecolorallocate($resized,0,0,0));
	if( !empty($path) ){
		$result = imagepng($resized,$path);
	}else{
		$result = imagepng($resized);
	}
	imagedestroy($resized);
	return $result;
}
/**
* @param $hash max_len:160
* @param $size image size
*/
function genIdenticonMore( $hash,$size,$is_iteration=false ){
	$len = strlen( $hash );
	$rest_len = $len - 16;//四个角的颜色(3*2)+图案(1)+初始方向(1) + 中心的颜色(3*2)+图案(1)+是否使用背景色(1)
	$num = ceil($rest_len/8);

	$need_add = 8 - $rest_len%8;
	if( $need_add<8 ){//长度不足，补齐
		$len += $need_add;
		$hash = str_pad($hash, $len,'0');
	}

	$corner_side = [];
	$center = [];

	for($i=0;$i<$len;$i=$i+8){
		if( $i==0 && $num>0 ){
			$center = [
				's'=>hexdec( substr($hash,$i,1) ) & 7, //图案方式
				'r'=>hexdec( substr($hash,$i+1,2) ),  //红色
				'g'=>hexdec( substr($hash,$i+3,2) ),  //绿色
				'b'=>hexdec( substr($hash,$i+5,2) ),  //蓝色
				'd'=>hexdec( substr($hash,$i+7,1) ) & 1 // 是否使用背景色
			];
		}else{
			$corner_side[] = [
				's'=>hexdec( substr($hash,$i,1) ), //图案方式
				'r'=>hexdec( substr($hash,$i+1,2) ),  //红色
				'g'=>hexdec( substr($hash,$i+3,2) ),  //绿色
				'b'=>hexdec( substr($hash,$i+5,2) ),  //蓝色
				'd'=>hexdec( substr($hash,$i+7,1) ) & 3 // 初始方向
			];
		}
	}
	
	$identicon=imagecreatetruecolor(SPRITE_Z*($num+2),SPRITE_Z*($num+2));
	imageantialias($identicon,TRUE);
	/* assign white as background */
	$bg=imagecolorallocate($identicon,255,255,255);
	imagefilledrectangle($identicon,0,0,SPRITE_Z,SPRITE_Z,$bg);
	
	foreach( $corner_side as $key => $value ){
		/* generate corner sprites */
		$corner=getsprite($value['s'],$value['r'],$value['g'],$value['b'],$value['d'],SPRITE_Z);
		imagecopy($identicon,$corner,SPRITE_Z*$key,0,0,0,SPRITE_Z,SPRITE_Z);// 0 0 0 0 
		$corner=imagerotate($corner,90,$bg);
		imagecopy($identicon,$corner,0,SPRITE_Z*($num+1-$key),0,0,SPRITE_Z,SPRITE_Z);// 0 2 0 0
		$corner=imagerotate($corner,90,$bg);
		imagecopy($identicon,$corner,SPRITE_Z*($num+1-$key),SPRITE_Z*($num+1),0,0,SPRITE_Z,SPRITE_Z);//2 2 0 0
		$corner=imagerotate($corner,90,$bg);
		imagecopy($identicon,$corner,SPRITE_Z*($num+1),SPRITE_Z*$key,0,0,SPRITE_Z,SPRITE_Z);//2 0 0 0
		imagedestroy($corner);
	}
	
	$fbg = array_shift($corner_side);
	unset($corner_side);
	if($num-2>0){//填充中间部分
		$tmp = genIdenticonMore( substr($hash,0,$len-2*8),$size,true );
		imagecopy($identicon,$tmp,SPRITE_Z,SPRITE_Z,0,0,SPRITE_Z*$num,SPRITE_Z*$num);
		imagedestroy($tmp);
	}
	if( $is_iteration ){
		unset($center,$bg);
		return $identicon;
	}
	if($center){//设置中心图片
		$center=getcenter($center['s'],$center['r'],$center['g'],$center['b'],$fbg['r'],$fbg['g'],$fbg['b'],$center['d'],SPRITE_Z*($num%2==0?2:1));
		imagecopy($identicon,$center,SPRITE_Z*($num+2-($num%2==0?2:1))/2,SPRITE_Z*($num+2-($num%2==0?2:1))/2,0,0,SPRITE_Z*($num%2==0?2:1),SPRITE_Z*($num%2==0?2:1));
		imagedestroy($center);
	}
	/* make white transparent */
	imagecolortransparent($identicon,$bg);
	//$identicon=imagerotate($identicon,$angle,$bg);

	/* create blank image according to specified dimensions */
	$resized=imagecreatetruecolor($size,$size);
	imageantialias($resized,TRUE);

	/* assign white as background */
	$bg=imagecolorallocate($resized,255,255,255);
	imagefilledrectangle($resized,0,0,$size,$size,$bg);

	/* resize identicon according to specification */
	imagecopyresampled($resized,$identicon,0,0,0,0,$size,$size,SPRITE_Z*($num+2),SPRITE_Z*($num+2));

	imagedestroy($identicon);
	
	/* make white transparent */
	imagecolortransparent($resized,$bg);
	//imagecolortransparent($resized,imagecolorallocate($resized,0,0,0));
	if( !empty($path) ){
		$result = imagepng($resized,$path);
	}else{
		$result = imagepng($resized);
	}
	imagedestroy($resized);
	return $result;
	
}

function getCornerSide(){
	
}

///TEST CASE

/* and finally, send to standard output */
header("Content-Type: image/png");
//genIdenticon( $_GET["hash"] ,$_GET["size"]);
//genIdenticonMore('e4da3b7fbbce2345d7772b0674a318d5',128);
//genIdenticonMore('e4da3b7fbbce2345d7772b0674a318d5e4da3b7fbbce2345d7772b0674a318d5',128);
genIdenticonMore('e4da3b7fbbce2345d7772b0674a318d574a318d574a318d574a318d574a318d574a318d5e4da3b7fbbce2345d7772b0674a318d574a318d574a318d574a318d574a318d574a318d5e4da3b7fbbcefsde',128);
?>
