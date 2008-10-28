<?php
$graphfilterdir = rtrim(dirname(__FILE__), '/\\');
require_once("$graphfilterdir/../../assessment/mathphp.php");
// ASCIIsvgIMG.php
// (c) 2006-2008 David Lippman   http://www.pierce.ctc.edu/dlippman
// Generates an image based on an ASCIIsvg script
// as a backup for ASCIIsvg.js SVG generator script
//
// Revised 3/08 to add angle to text
//
// Based on ASCIIsvg.js (c) Peter Jipsen
// http://www.chapman.edu/~jipsen/svg/asciisvg.html
//
// Recognized commands:
//	setBorder(border) or setBorder(left,bottom,right,top)
//	initPicture(xmin,xmax,{ymin,ymax})
//	axes(xtick,ytick,{"labels",xgrid,ygrid,dox,doy})
//	plot("func",{xmin,xmax})	//plot a function, i.e.
//					//"sin(x)" or "[cos(t),sin(t)]"
//	line([x1,y1],[x2,y2])
//	path([[x1,y1],...,[xn,yn]])
//	circle([x1,y1],rad)
//	ellipse([x1,y1],xrad,yrad)
//	rect([x1,y1],[x2,y2])
//	text([x1,y1],"string",{pos,angle});  	//(pos: left,right,above,below,aboveleft,...)
//	dot([x1,y1],{type,label,pos});	//(type: open, closed)
//	stroke = "color"		//line color
//	fill = "color"			//fill color
//	strokewidth=width		//line thickness
//	strokedasharray=array		//dash array, ie "5 3" for 5 pixel color, 3 white
//	marker = marker			//"dot" or "arrow" or "arrowdot" or "none"
//					//marker for lines or paths
//	
// Use:
//	$AS = new AStoIMG(width,height);
//	$AS->processScript($ASscriptstring);
//		or
//	$AS->processShortScript($ASsscrString);
//	$AS->outputimage({filename});	//if no filename, outputed to stream

class AStoIMG 
{

var $usegd2, $usettf; 
var $xmin = -5; 
var $xmax = 5; 
var $ymin = -5; 
var $ymax = 5; 
var $border = array(5,5,5,5);
var $origin = array(0,0);
var $width;
var $height;
var $img;
var $winxmax,$winxmin,$winymin,$winymax;
var $white,$black,$red,$orange,$yellow,$green,$blue,$cyan,$purple,$gray;
var $stroke = 'black', $fill = 'none', $curdash='', $isdashed=false, $marker='none';
var $markerfill = 'green', $gridcolor = 'gray', $axescolor = 'black';
var $strokewidth = 1, $xunitlength, $yunitlength, $dotradius=8, $ticklength=4;
var $fontsize = 12, $fontfile, $fontfill='';

var $AScom; 
function AStoIMG($w=200, $h=200) {
	$this->xmin = -5; $this->xmax = 5; $this->ymin = -5; $this->ymax = 5; $this->border = array(5,5,5,5);
	$this->stroke = 'black'; $this->fill = 'none'; $this->curdash=''; $this->isdashed=false; $this->marker='none';
	$this->markerfill = 'green'; $this->gridcolor = 'gray'; $this->axescolor = 'black';
	$this->strokewidth = 1; $this->dotradius=8; $this->ticklength=4;
	$this->fontsize = 12; $this->fontfill='';
	
	if ($w<=0) {$w=200;}
	if ($h<=0) {$h=200;}
	$this->img = imagecreate($w,$h);
	$this->usegd2 = function_exists('imagesetthickness');
	$this->usettf = function_exists('imagettftext');
	$this->fontfile =  $GLOBALS['graphfilterdir'].'/FreeSerifItalic.ttf';
	$this->width = $w;
	$this->height = $h;
	$this->xunitlength = $w/10;
	$this->yunitlength = $h/10;
	$this->origin = array(round($w/2),round($h/2));
	$this->white = imagecolorallocate($this->img, 255,255,255);
	$this->black = imagecolorallocate($this->img, 0,0,0);
	$this->gray = imagecolorallocate($this->img, 200,200,200);
	$this->red = imagecolorallocate($this->img, 255,0,0);
	$this->orange = imagecolorallocate($this->img, 255,165,0);
	$this->yellow = imagecolorallocate($this->img, 255,255,0);
	$this->green = imagecolorallocate($this->img, 0,255,0);
	$this->blue = imagecolorallocate($this->img, 0,0,255);
	$this->cyan = imagecolorallocate($this->img, 0,255,255);
	$this->purple = imagecolorallocate($this->img, 128,0,128);
	imagefill($this->img,0,0,$this->white);
}

function processShortScript($script) {
	//$xmin = -5; $xmax = 5; $ymin = -5; $ymax = 5; $border = 5;
	//$stroke = 'black'; $fill = 'none'; $curdash=''; $isdashed=false; $marker='none';
        //$markerfill = 'green'; $gridcolor = 'gray'; $axescolor = 'black';
	//$strokewidth = 1; $dotradius=8; $ticklength=4; $fontsize = 12;
	
	$sa = explode(',',$script);
	if (count($sa)>10) {
		$this->border = 5;
		$this->AStoIMG($sa[9],$sa[10]);
		$this->ASinitPicture(array_slice($sa,0,4));//$sa[0] .','. $sa[1] .','. $sa[2] .','. $sa[3]);
		$this->ASaxes(array_slice($sa,4,5));//$sa[4] .','. $sa[5] .','. $sa[6] .','. $sa[7] .','. $sa[8]);
		$inx = 11;
		while (count($sa) > $inx+9) {
			$this->stroke = $sa[$inx+7];
			$this->strokewidth = $sa[$inx+8];
			if ($this->usegd2) {
				imagesetthickness($this->img,$this->strokewidth);
			}
			if ($sa[$inx+9] != "") {
				$this->ASsetdash($sa[$inx+9]);
			} else {
				$this->ASsetdash('none');
			}
			if ($sa[$inx]=='slope') {
				$this->ASslopefield(array($sa[$inx+1],$sa[$inx+2],$sa[$inx+2]));
			} else {
				if ($sa[$inx]=='func') {
					$eqn = $sa[$inx+1];
				} else if ($sa[$inx]=='polar') {
					$eqn = '[cos(t)*('.$sa[$inx+1].'),sin(t)*('.$sa[$inx+1].')]';
				} else if ($sa[$inx]=='param') {
					$eqn = '['.$sa[$inx+1].','.$sa[$inx+2].']';
				}
				if (is_numeric($sa[$inx+5])) {	
					$this->ASplot(array($eqn,$sa[$inx+5],$sa[$inx+6],null,null,$sa[$inx+3],$sa[$inx+4]));
				} else {
					$this->ASplot(array($eqn,null,null,null,null,$sa[$inx+3],$sa[$inx+4]));
				}
			}
			$inx += 10;
		}
	}
}
	
function processScript($script) {
	//$xmin = -5; $xmax = 5; $ymin = -5; $ymax = 5; $border = 5;
	//$stroke = 'black'; $fill = 'none'; $curdash=''; $isdashed=false; $marker='none';
        //$markerfill = 'green'; $gridcolor = 'gray'; $axescolor = 'black';
	//$strokewidth = 1; $dotradius=8; $ticklength=4; $fontfill = ''; $fontsize = 12;
	$this->AScom =  explode(';',$script);
	foreach ($this->AScom as $com) {
		if (preg_match('/\s*(\w+)\s*=(.+)/',$com,$matches)) { //is assignment operator
			$matches[2] = str_replace(array('"','\''),'',$matches[2]);
				
			switch($matches[1]) {
				case 'border':
				case 'xmin':
				case 'xmax':
				case 'ymin':
				case 'ymax':
				case 'fill':
				case 'marker':
				case 'fontfill':
					$this->$matches[1] = $matches[2];
					break;
				case 'stroke':
					$this->stroke = $matches[2];
					if ($this->isdashed) {
						$this->ASsetdash();
					}
					break;
				case 'strokedasharray':
					$this->ASsetdash($matches[2]);
					break;
				case 'strokewidth':
					$this->strokewidth = $matches[2];
					if ($this->usegd2) {
						imagesetthickness($this->img,$this->strokewidth);
					}
					break;
			}
		}
		if (preg_match('/\s*(\w+)\((.*)\)\s*$/',$com,$matches)) { //is function
			$argarr = $this->parseargs($matches[2]);
			switch($matches[1]) {
				case 'initPicture':
					$this->ASinitPicture($argarr);
					break;
				case 'setBorder':
					$this->border = $argarr;
					break;
				case 'axes':
					$this->ASaxes($argarr);
					break;
				case 'line':
					$this->ASline($argarr);
					break;
				case 'path':
					$this->ASpath($argarr);
					break;
				case 'circle':
					$this->AScircle($argarr);
					break;
				case 'ellipse':
					$this->ASellipse($argarr);
					break;
				case 'rect':
					$this->ASrect($argarr);
					break;
				case 'text':
					$this->AStext($argarr);
					break;
				case 'textabs':
					$this->AStextAbs($argarr);
					break;
				case 'dot':
					$this->ASdot2($argarr);
					break;
				case 'plot':
					$this->ASplot($argarr);
					break;
				case 'slopefield':
					$this->ASslopefield($argarr);
					break;
			}	
		}
	}
}
	
function ASsetdash() {
	if (func_num_args()>0) {
		$dash = func_get_arg(0);
		$this->curdash = $dash;
	} else {
		$dash = $this->curdash;
	}
	if ($dash=='none' || !preg_match('/\d/',$dash)) {
		$this->isdashed = false;
	} else {
		$dash = preg_replace('/\s+/',',',$dash);
		$darr = explode(',',$dash);
		$style = array();
		$alt = 0;
		$doagain = count($darr)%2;  //do twice if odd number
		while ($doagain>-1) {
			for ($i=0;$i<count($darr);$i++) {
				if ($alt==0) {
					$color = $this->stroke;
				} else {
					$color = 'white';
				}
				$style = array_pad($style,count($style)+$darr[$i],$this->$color);
				$alt = 1-$alt;
			}
			$doagain--;
		} 
		imagesetstyle($this->img,$style);
		$this->isdashed = true;
	}
}
function AStext($arg) {
	$pos = '';  $angle = 0;
	if (func_num_args()>1) {
		$p = $this->pt2arr($arg);
		$st = func_get_arg(1);
		if (func_num_args()>2) {
			$pos = func_get_arg(2);
		}
		if (func_num_args()>3) {
			$angle = func_get_arg(3);
		}
	} else {
		$p = $this->pt2arr($arg[0]);
		$st = $arg[1];
		if (isset($arg[2])) {
			$pos = $arg[2];
		}
		if (isset($arg[3])) {
			$angle = $arg[3];
		}		
	}
	$this->AStextInternal($p,$st,$pos,$angle);
}
function AStextAbs($arg) {
	$pos = '';  $angle = 0;
	if (func_num_args()>1) {
		$pt = $arg;
		$st = func_get_arg(1);
		if (func_num_args()>2) {
			$pos = func_get_arg(2);
		}
		if (func_num_args()>3) {
			$angle = func_get_arg(3);
		}
	} else {
		$pt = $arg[0];
		$st = $arg[1];
		if (isset($arg[2])) {
			$pos = $arg[2];
		}
		if (isset($arg[3])) {
			$angle = $arg[3];
		}		
	}
	$pt = str_replace(array('[',']'),'',$pt);
	$pt = explode(',',$pt);
	$pt[1] = $this->height - $pt[1];
	$this->AStextInternal($pt,$st,$pos,$angle);
}
function AStextInternal($p,$st,$pos,$angle) {
	
	/*if (func_num_args()>1) {
		$p = $this->pt2arr($arg);
		$st = func_get_arg(1);
		if (func_num_args()>2) {
			$pos = func_get_arg(2);
		}
		if (func_num_args()>3) {
			$angle = func_get_arg(3);
		}
	} else {
		$p = $this->pt2arr($arg[0]);
		$st = $arg[1];
		if (isset($arg[2])) {
			$pos = $arg[2];
		}
		if (isset($arg[3])) {
			$angle = $arg[3];
		}		
	}*/
	/*else {
		if (preg_match('/\s*\[(.*?)\]\s*,\s*[\'"](.*?)[\'"]\s*,([^,]*)/',$arg,$m)) {
			$p = $this->pt2arr($m[1]);
			$st = $m[2];
			$pos = trim(str_replace(array('"',"'"),'',$m[3]));
		} else {
			$arg = explode(',',$arg);
			$p = $this->pt2arr($arg[0].','.$arg[1]);
			$st = str_replace(array('"',"'"),'',$arg[2]);
		}
	}*/
	if ($this->usettf) {
		$bb = imagettfbbox($this->fontsize,$angle,$this->fontfile,$st);
		$bbw = $bb[4]-$bb[0];
		$bbh = -1*($bb[5]-$bb[1]);
		
		$p[0] = $p[0] - .5*($bbw);
		$p[1] = $p[1] + .5*($bbh);
		if ($pos=='above' || $pos=='aboveright' || $pos=='aboveleft') {
			$p[1] = $p[1] - .5*(abs($bbh)) - $this->fontsize/2;
		}			
		if ($pos=='below' || $pos=='belowright' || $pos=='belowleft') {
			$p[1] = $p[1] + .5*(abs($bbh)) + $this->fontsize/2;
		}
		if ($pos=='left' || $pos=='aboveleft' || $pos=='belowleft') {
			$p[0] = $p[0] - .5*(abs($bbw)) -$this->fontsize/2;
		}
		if ($pos=='right' || $pos=='aboveright' || $pos=='belowright') {
			$p[0] = $p[0] + .5*(abs($bbw)) +$this->fontsize/2;
		}
		if ($this->fontfill != '') {
			$color = $this->fontfill;
		} else {
			$color = $this->stroke;
		}
		imagettftext($this->img,$this->fontsize,$angle,$p[0],$p[1],$this->$color,$this->fontfile,$st);
	} else {
		if ($this->fontsize<9) {
			$fs = 1;
		} else if ($this->fontsize<13) {
			$fs = 2;
		} else {
			$fs = 4;
		}
		if ($angle==90 || $angle==270) {
			$bb = array(imagefontheight($fs),imagefontwidth($fs)*strlen($st));
		} else {
			$bb = array(imagefontwidth($fs)*strlen($st),imagefontheight($fs));
		}
		$p[0] = $p[0] - .5*$bb[0];
		if ($angle==90 || $angle==270) {
			$p[1] = $p[1] + .5*$bb[1];
		} else {
			$p[1] = $p[1] - .5*$bb[1];
		}
		if ($pos=='above' || $pos=='aboveright' || $pos=='aboveleft') {
			$p[1] = $p[1] - .5*$bb[1] - $fs*2;
		}			
		if ($pos=='below' || $pos=='belowright' || $pos=='belowleft') {
			$p[1] = $p[1] + .5*$bb[1] + $fs*2;
		}
		if ($pos=='left' || $pos=='aboveleft' || $pos=='belowleft') {
			$p[0] = $p[0] - .5*$bb[0] - $fs*2;
		}
		if ($pos=='right' || $pos=='aboveright' || $pos=='belowright') {
			$p[0] = $p[0] + .5*$bb[0] + $fs*2;
		}
		if ($this->fontfill != '') {
			$color = $this->fontfill;
		} else {
			$color = $this->stroke;
		}
		if ($angle==90 || $angle==270) {
			imagestringup($this->img,$fs,$p[0],$p[1],$st,$this->$color);
		} else {
			imagestring($this->img,$fs,$p[0],$p[1],$st,$this->$color);
		}
	}
}

function ASinitPicture($arg) {
	
	//$arg = explode(',',$arg);
	if (isset($arg[0]) && $arg[0]!='') { $this->xmin = $this->evalifneeded($arg[0]);}
	if (isset($arg[1])) { $this->xmax = $this->evalifneeded($arg[1]);}
	if (isset($arg[2])) { $this->ymin = $this->evalifneeded($arg[2]);}
	if (isset($arg[3])) { $this->ymax = $this->evalifneeded($arg[3]);}
	
	if ($this->xmin == $this->xmax) {
		$this->xmax = $this->xmin + .000001;
	}
	if (!is_array($this->border)) {
		$this->border = array($this->border,$this->border,$this->border,$this->border);
	} else if (count($this->border<4)) {
		for ($i=count($this->border);$i<5;$i++) {
			if ($i==1) {
				$this->border[$i] = $this->border[0];
			} else {
				$this->border[$i] = $this->border[$i-2];
			} 	
		}
	}
	$this->xunitlength = ($this->width - $this->border[0] - $this->border[2])/($this->xmax - $this->xmin);
	$this->yunitlength = ($this->height - $this->border[1] - $this->border[3])/($this->ymax - $this->ymin);
	if ($this->xunitlength<=0) {
		$this->xunitlength = 1;
	}
	if ($this->yunitlength<=0) {
		$this->yunitlength = 1;
	}
	$this->origin[0] = -$this->xmin*$this->xunitlength + $this->border[0];
	$this->origin[1] = -$this->ymin*$this->yunitlength + $this->border[1];
	
	$this->winxmin = max($this->border[0] - 5,0);
	$this->winxmax = min($this->width - $this->border[2] + 5, $this->width);
	$this->winymin = max($this->border[3] -5,0);
	$this->winymax = min($this->height - $this->border[1] + 5 , $this->height);
}
function ASaxes($arg) {
	//$arg = explode(',',$arg);
	$xscl = 0; $yscl = 0; $xgrid = 0; $ygrid = 0; $dolabels = false; $dogrid = false;
	$dox = true;
	$doy = true;
	if (is_numeric($arg[0])) {
		$xscl = $this->evalifneeded($arg[0]);
	} else {
		$dolabels = true;	
	}
	if (count($arg)>1) {
		if (is_numeric($arg[1])) {
			$yscl = $this->evalifneeded($arg[1]);
		} else {
			$dogrid = true;
		}
	}
	if (count($arg)>2) {
		if ($arg[2]=='0' || $arg[2]=='null' || $arg[2]=='"null"') {
			$dolabels = false;
		} else {
			$dolabels = true;
		}
	}
	if (count($arg)>3) {
		if ($arg[3]=='0' || $arg[3]=='null' || $arg[3]=='"null"') {
			$dogrid = false;
		} else {
			$xgrid = $this->evalifneeded($arg[3]);
			$dogrid = true;
		}
	}
	if (count($arg)>4) {
		$ygrid = $this->evalifneeded($arg[4]);
	}
	if (count($arg)>5) {
		if ($arg[5]=='off' || $arg[5]=='0') {
			$dox = false;
		}
	}
	if (count($arg)>6) {
		if ($arg[6]=='off' || $arg[6]=='0') {
			$doy = false;
		}
	}
	if ($xscl<0) {
		$xscl *= -1;
	}
	if ($yscl<0) {
		$yscl *= -1;
	}
	if ($xgrid<0) {
		$xgrid *= -1;
	} 
	if ($ygrid<0) {
		$ygrid *= -1;
	}
	if ($xscl==0) {
		$xscl = $this->xunitlength;
	} else {
		$xscl *= $this->xunitlength;
	}
	if ($yscl==0) {
		$yscl = $this->yunitlength;
	} else {
		$yscl *= $this->yunitlength;
	}
	$this->fontsize = min($xscl/2,$yscl/2,12);
	$this->ticklength = $this->fontsize/4;
	if ($this->usegd2) {
		imagesetthickness($this->img,1);
	}
	if ($dogrid) {
		if ($xgrid==0) {
			$xgrid = $this->xunitlength;
		} else {
			$xgrid *= $this->xunitlength;
		}
		if ($ygrid==0) {
			$ygrid = $this->yunitlength;
		} else {
			$ygrid *= $this->yunitlength;
		}
		$gc = $this->gridcolor;
		if ($dox) {
			for ($x=$this->origin[0]+($doy?$xgrid:0); $x<=$this->winxmax; $x += $xgrid) {
				if ($x>=$this->winxmin) {
					imageline($this->img,$x,$this->winymin,$x,$this->winymax,$this->$gc);
				}
			}
			for ($x=$this->origin[0]-$xgrid; $x>=$this->winxmin; $x -= $xgrid) {
				if ($x<=$this->winxmax) {
					imageline($this->img,$x,$this->winymin,$x,$this->winymax,$this->$gc);
				}
			}
		}
		if ($doy) {
			for ($y=$this->height - $this->origin[1]+($dox?$ygrid:0); $y<=$this->winymax; $y += $ygrid) {
				if ($y>=$this->winymin) {
					imageline($this->img,$this->winxmin,$y,$this->winxmax,$y,$this->$gc);
				}
			}
			for ($y=$this->height - $this->origin[1]-$ygrid; $y>$this->winymin; $y -= $ygrid) {
				if ($y<=$this->winymax) {
					imageline($this->img,$this->winxmin,$y,$this->winxmax,$y,$this->$gc);
				}
			}
		}
	}
	
	$ac = $this->axescolor;
	if ($doy) {
		if ($this->origin[0]>=$this->winxmin && $this->origin[0]<=$this->winxmax) {
			imageline($this->img,$this->origin[0],$this->winymin,$this->origin[0],$this->winymax,$this->$ac);
			//ticks
			for ($y=$this->height - $this->origin[1]; $y<=$this->winymax; $y += $yscl) {
				if ($y>=$this->winymin) {
					imageline($this->img,$this->origin[0]-$this->ticklength,$y,$this->origin[0]+$this->ticklength,$y,$this->$ac);
				}
			}
			for ($y=$this->height - $this->origin[1]-$yscl; $y>=$this->winymin; $y -= $yscl) {
				if ($y<=$this->winymax) {
					imageline($this->img,$this->origin[0]-$this->ticklength,$y,$this->origin[0]+$this->ticklength,$y,$this->$ac);
				}
			}
		}
	}
	if ($dox) {
		if ($this->origin[1]>=$this->winymin && $this->origin[1]<=$this->winymax) {
			imageline($this->img,$this->winxmin,$this->height-$this->origin[1],$this->winxmax,$this->height-$this->origin[1],$this->$ac);
			//ticks
			for ($x=$this->origin[0]; $x<=$this->winxmax; $x += $xscl) {
				if ($x>=$this->winxmin) {
						imageline($this->img,$x,$this->height- $this->origin[1] -$this->ticklength,$x,$this->height- $this->origin[1] +$this->ticklength,$this->$ac);
				}
			}
			for ($x=$this->origin[0]-$xscl; $x>=$this->winxmin; $x -= $xscl) {
				if ($x<=$this->winxmax) {
					imageline($this->img,$x,$this->height-$this->origin[1]-$this->ticklength,$x,$this->height-$this->origin[1]+$this->ticklength,$this->$ac);
				}
			}
		}
	}
	
	if ($dolabels) {
		$ldx = $xscl/$this->xunitlength;
		$ldy = $yscl/$this->yunitlength;
		if ($this->xmin>0 || $this->xmax<0) {
			$lx = $this->xmin;
			$lyp = 'right';
		} else {
			$lx = 0;
			$lyp = 'left';
		}
		if ($this->ymin>0 || $this->ymax<0) {
			$ly = $this->ymin;
			$lxp = 'above';
		} else {
			$ly = 0;
			$lxp = 'below';
		}
		
		$backupstroke = $this->stroke;
		$this->stroke = 'black';
		if ($dox) {
			for ($x=($doy?$ldx:0);$x<=$this->xmax; $x += $ldx) {
				if ($x>=$this->xmin) {
					$this->AStext("[$x,$ly]",$x,$lxp);
				}
			}
			for ($x=-$ldx;$this->xmin<=$x; $x -= $ldx) {
				if ($x<=$this->xmax) {
					$this->AStext("[$x,$ly]",$x,$lxp);
				}
			}
		}
		if ($doy) {
			for ($y=($dox?$ldy:0);$y<=$this->ymax; $y += $ldy) {
				if ($y>=$this->ymin) {
					$this->AStext("[$lx,$y]",$y,$lyp);
				}
			}
			
			for ($y=-$ldy;$this->ymin<=$y; $y -= $ldy) {
				if ($y<=$this->ymax) {
					$this->AStext("[$lx,$y]",$y,$lyp);
				}
			}
		}
		$this->stroke = $backupstroke;
	}
	if ($this->usegd2) {
		imagesetthickness($this->img,$this->strokewidth);
	}
}

function ASline($arg) {
	//$arg = explode('],[',$arg);
	if (count($arg)<2) { return;}
	$p = $this->pt2arr($arg[0]);
	$q = $this->pt2arr($arg[1]);
	if ($this->isdashed) {
		imageline($this->img,$p[0],$p[1],$q[0],$q[1],IMG_COLOR_STYLED);
	} else {
		$color = $this->stroke;
		imageline($this->img,$p[0],$p[1],$q[0],$q[1],$this->$color);
	}
	if ($this->marker=='dot' || $this->marker=='arrowdot') {
		$this->ASdot($p,8);
		$this->ASdot($q,8);
	}
	if ($this->marker=='arrow' || $this->marker=='arrowdot') {
		$this->ASarrowhead($p,$q);
	}
}
function ASpath($arg) {
	$arg = str_replace(array('[',']'),'',$arg[0]);
	$arg = explode(',',$arg);
	if (count($arg)<4) { return;}
	
	if (count($arg)>5 && $this->fill != 'none') {
		$pt = array();
		for ($i=0;$i<count($arg);$i++) {
			if ($i%2==0) { //x coord
				$pt[$i] = $arg[$i]*$this->xunitlength + $this->origin[0];
			} else {
				$pt[$i] = $this->height - $arg[$i]*$this->yunitlength - $this->origin[1];
			}
		}
		$color = $this->fill;
		imagefilledpolygon($this->img,$pt,count($pt)/2,$this->$color);
	}
	for ($i=0; $i<count($arg)-2; $i += 2) {
		//$this->ASline("[{$arg[$i]},{$arg[$i+1]}],[{$arg[$i+2]},{$arg[$i+3]}]");
		$this->ASline(array("[{$arg[$i]},{$arg[$i+1]}]","[{$arg[$i+2]},{$arg[$i+3]}]"));
	}
}
function AScircle($arg) {
	//$arg = explode(',',$arg);
	//$this->ASellipse("[{$arg[0]},{$arg[1]}],{$arg[2]},{$arg[2]}");
	$this->ASellipse(array($arg[0],$arg[1],$arg[1]));
}
function ASellipse($arg) {
	//$arg = explode(',',$arg);
	//$p = $this->pt2arr($arg[0].','.$arg[1]);
	$p = $this->pt2arr($arg[0]);
	$arg[1] *= $this->xunitlength;
	$arg[2] *= $this->yunitlength;
	if ($this->fill != 'none') {
		$color = $this->fill;
		if ($this->usegd2) {
			imagefilledellipse($this->img,$p[0],$p[1],$arg[1]*2,$arg[2]*2,$this->$color);
		}
	}
	if ($this->isdashed) {
		imageellipse($this->img,$p[0],$p[1],$arg[1]*2,$arg[2]*2,IMG_COLOR_STYLED);
	} else {
		$color = $this->stroke;
		imageellipse($this->img,$p[0],$p[1],$arg[1]*2,$arg[2]*2,$this->$color);
	}
}
function ASrect($arg) {
	//$arg = explode(',',$arg);
	$p = $this->pt2arr($arg[0]);
	$q = $this->pt2arr($arg[1]);
	$sx = min($p[0],$q[0]); $bx = max($p[0],$q[0]);
	$sy = min($p[1],$q[1]); $by = max($p[1],$q[1]);
	if ($this->fill != 'none') {
		$color = $this->fill;
		imagefilledrectangle($this->img,$sx,$sy,$bx,$by,$this->$color);
	}
	
	if ($this->isdashed) {
		imagerectangle($this->img,$sx,$sy,$bx,$by,IMG_COLOR_STYLED);
	} else {
		$color = $this->stroke;
		imagerectangle($this->img,$sx,$sy,$bx,$by,$this->$color);
	}
}

function ASdot($pt,$r) {
	if ($this->markerfill!='none') {
		$color = $this->markerfill;
		if ($this->usegd2) {
			imagefilledellipse($this->img,$pt[0],$pt[1],$r,$r,$this->$color);
		} else {
			imagefilledpolygon($this->img,array($pt[0]-$r,$pt[1],$pt[0],$pt[1]+$r,$pt[0]+$r,$pt[1],$pt[0],$pt[1]-$r),4,$this->$color);
		}
	}
	$color = $this->stroke;
	imageellipse($this->img,$pt[0],$pt[1],$r,$r,$this->$color);
}
function ASdot2($arg) {
	$pt = $this->pt2arr($arg[0]);
	$color = $this->stroke;
	if (isset($arg[1]) && $arg[1]=='closed') {
		if ($this->usegd2) {
			imagefilledellipse($this->img,$pt[0],$pt[1],$this->dotradius,$this->dotradius,$this->$color);
		} else {
			$r = $this->dotradius;
			imagefilledpolygon($this->img,array($pt[0]-$r,$pt[1],$pt[0],$pt[1]+$r,$pt[0]+$r,$pt[1],$pt[0],$pt[1]-$r),4,$this->$color);
		}
	} else {
		if ($this->usegd2) {
			imagefilledellipse($this->img,$pt[0],$pt[1],$this->dotradius,$this->dotradius,$this->white);
		} else {
			$r = $this->dotradius;
			imagefilledpolygon($this->img,array($pt[0]-$r,$pt[1],$pt[0],$pt[1]+$r,$pt[0]+$r,$pt[1],$pt[0],$pt[1]-$r),4,$this->white);
		}
		imageellipse($this->img,$pt[0],$pt[1],$this->dotradius,$this->dotradius,$this->$color);
	}
	if (isset($arg[2])) {
		if (isset($arg[3])) {
			$this->AStext(array($arg[0],$arg[2],$arg[3]));
		} else {
			$this->AStext(array($arg[0],$arg[2]));
		}
	}
	/*
	if (preg_match('/\s*\[(.*?)\]\s*,\s*[\'"](.*?)[\'"]\s*(.*)/',$arg,$m)) {
		$pt = $this->pt2arr($m[1]);
		if ($m[2]=='closed') {
			imagefilledellipse($this->img,$pt[0],$pt[1],$this->dotradius,$this->dotradius,$this->$color);		
		} else {
			imageellipse($this->img,$pt[0],$pt[1],$this->dotradius,$this->dotradius,$this->$color);
		}
		
		if (strlen($m[3])>0) {
			$this->AStext('['.$m[1].']'.$m[3]);
		}
	} else if (preg_match('/\s*\[(.*?)\]\s*,\s*,\s*(.*)/',$arg,$m)) {
		$pt = $this->pt2arr($m[1]);
		imageellipse($this->img,$pt[0],$pt[1],$this->dotradius,$this->dotradius,$this->$color);
		if (strlen($m[3])>0) {
			$this->AStext('['.$m[1].']'.$m[2]);
		}
	} else {
		$pt = $this->pt2arr($arg);
		imageellipse($this->img,$pt[0],$pt[1],$this->dotradius,$this->dotradius,$this->$color);
	}
	*/	
}
function ASarrowhead($v,$w) {
	$u = array($w[0]-$v[0],$w[1]-$v[1]);
	$d = sqrt($u[0]*$u[0]+$u[1]*$u[1]);
	if ($d > 0.00000001) {
		$u = array($u[0]/$d, $u[1]/$d);
		$up = array(-$u[1],$u[0]);
		$arr = array(($w[0]-15*$u[0]-4*$up[0]),($w[1]-15*$u[1]-4*$up[1]),($w[0]-3*$u[0]),($w[1]-3*$u[1]),($w[0]-15*$u[0]+4*$up[0]),($w[1]-15*$u[1]+4*$up[1]));
		$color = $this->stroke;
		imagefilledpolygon($this->img,$arr,count($arr)/2,$this->$color);
	}
}
function ASslopefield($arg) {
	$func = $arg[0];
	if (count($arg)>1) {
		$dx = $arg[1];
	} else {
		$dx = 1;
	}
	if (count($arg)>2) {
		$dy = $arg[2];
	} else {
		$dy = 1;
	}
	preg_match_all('/[a-zA-Z]+/',$func,$matches,PREG_PATTERN_ORDER);
	$okfunc = array('sin','cos','tan','sec','csc','cot','arcsin','arccos','arctan','x','y','log','ln','e','pi','abs','sqrt','safepow');
	foreach ($matches[0] as $m) {
		if (!in_array($m,$okfunc)) { echo "$m"; return;}
	}
	$func = mathphp($func,"x|y");
	$func = str_replace(array('x','y'),array('$x','$y'),$func);
	$efunc = create_function('$x,$y','return ('.$func.');');
	$dz = sqrt($dx*$dx + $dy*$dy)/6;
	$x_min = ceil($this->xmin/$dx);
	$y_min = ceil($this->ymin/$dy);
	for ($x = $x_min; $x<= $this->xmax; $x+= $dx) {
		for ($y = $y_min; $y<= $this->ymax; $y+= $dy) {
			$gxy = @$efunc($x,$y);
			if (!is_nan($gxy)) {
				if ($gxy===false) {
					$u = 0; $v = $dz;
				} else {
					$u = $dz/sqrt(1+$gxy*$gxy);
					$v = $gxy*$u;
				}
				$this->ASline(array("[$x-1*$u,$y-1*$v]","[$x+$u,$y+$v]"));
			}
		}
	}
}

function ASplot($function) {
	$funcstr = implode(',',$function);
	preg_match_all('/[a-zA-Z]+/',$funcstr,$matches,PREG_PATTERN_ORDER);
	$okfunc = array('sin','cos','tan','sec','csc','cot','arcsin','arccos','arctan','x','t','log','ln','e','pi','abs','sqrt','safepow');
	foreach ($matches[0] as $m) {
		if (!in_array($m,$okfunc)) { echo "$m"; return;}
	} //do safety check, as this will be eval'ed
	//$function = explode(',',str_replace(array('"','\'',';'),'',$function));
	$function = str_replace(array('"','\'',';'),'',$function);
	if (strpos($function[0],'[')===0) {
		$funcp = explode(',',$function[0]);
		$isparametric = true;
		$xfunc = str_replace("[","",$funcp[0]);
		$xfunc = mathphp($xfunc,"t");
		$xfunc = str_replace("t",'$t',$xfunc);
		$exfunc = create_function('$t','return ('.$xfunc.');');
		$yfunc = str_replace("]","",$funcp[1]);
		$yfunc = mathphp($yfunc,"t");
		$yfunc = str_replace("t",'$t',$yfunc);
		$eyfunc = create_function('$t','return ('.$yfunc.');');
	} else {
		$isparametric = false;
		$func = mathphp($function[0],"x");
		$func = str_replace("x",'$x',$func);
		$efunc = create_function('$x','return ('.$func.');');
	}
	$avoid = array();
	if (isset($function[1]) && $function[1]!='' && $function[1]!='null') {
		$xmin = $function[1];
	} else {
		$xmin = $this->xmin - min($this->border[0],5)/$this->xunitlength;
	}
	if (isset($function[2]) && $function[2]!='' && $function[2]!='null') {
		$xmaxarr = explode('!',$function[2]);
		$xmax = $xmaxarr[0];
		$avoid = array_slice($xmaxarr,1);
	} else {
		$xmax = $this->xmax + min($this->border[2],5)/$this->xunitlength;
	}
	$xmin += ($xmax - $xmin)/100000; //avoid divide by zero errors
	if (isset($function[3]) && $function[3]!='' && $function[3]!='null') {
		$dx = ($xmax - $xmin)/($function[3]-1);
		$stopat = $function[3];
	} else {
		$dx = ($xmax - $xmin)/100;
		$stopat = 101;
	}
	
	$px = null;
	$py = null;
	$lasty = 0;
	$lastl = 0;
	
	for ($i = 0; $i<$stopat;$i++) {
			
		if ($isparametric) {
			$t = $xmin + $dx*$i;
			if (in_array($t,$avoid)) { continue;}
			$x = $exfunc($t);
			$y = $eyfunc($t);
			if (is_nan($x) || is_nan($y)) { continue; }
		} else {
			$x = $xmin + $dx*$i;
			if (in_array($x,$avoid)) { continue;}
			$y = $efunc($x);
			if (is_nan($y)) { continue;}
		}
		if ($i<2 || $i==$stopat-2) {
			$fx[$i] = $x;
			$fy[$i] = $y;
		}
		$lastx = $x;
		/*if (abs($y-$lasty) > ($this->ymax-$this->ymin)) {
			if ($lastl > 1) { $lastl = 0; }//break path
			$lasty = $y;
		} else {
			
			$lasty = $y;
			if ($lastl > 0) {
				$this->ASline(array("[$px,$py]","[$x,$y]"));
			}
			$px = $x;
			$py = $y;
			
			$lastl++;
		}*/
		if ($py==null) { //starting line

		} else if ($y>$this->ymax || $y<$this->ymin) { //going or still out of bounds
			if ($py<=$this->ymax && $py>=$this->ymin) { //going out
				if ($y>$this->ymax) { //going up	
					$iy = $this->ymax + min($this->border[3],5)/$this->yunitlength;
				} else { //going down
					$iy = $this->ymin - min($this->border[1],5)/$this->yunitlength;
				}
				$ix = ($x-$px)*($iy - $py)/($y-$py) + $px;
				$this->ASline(array("[$px,$py]","[$ix,$iy]"));
			} else { //still out

			}
		} else if ($py>$this->ymax || $py<$this->ymin) { //coming or staying in bounds
			if ($y<=$this->ymax && $y>=$this->ymin) { //comin in
				if ($py>$this->ymax) { //comin from top	
					$iy = $this->ymax + min($this->border[3],5)/$this->yunitlength;
				} else { //coming from bottom
					$iy = $this->ymin - min($this->border[1],5)/$this->yunitlength;
				}
				$ix = ($x-$px)*($iy - $py)/($y-$py) + $px;
				$this->ASline(array("[$ix,$iy]","[$x,$y]"));
			} else { //still out
				
			}
		} else { //all in
			$this->ASline(array("[$px,$py]","[$x,$y]"));
		}
		$px = $x;
		$py = $y;
	}
	if (isset($function[5]) && $function[5]!='' && $function[5]!='null') {
		if ($function[5]==1) {
			//need pt2arr for xunit adjust
			$this->ASarrowhead($this->pt2arr("{$fx[1]},{$fy[1]}"),$this->pt2arr("{$fx[0]},{$fy[0]}"));
		} else if ($function[5]==2) {
			$this->ASdot2(array("[{$fx[0]},{$fy[0]}]","open"));
		} else if ($function[5]==3) {
			$this->ASdot2(array("[{$fx[0]},{$fy[0]}]","closed"));
		}
	}
	if (isset($function[6]) && $function[6]!='' && $function[6]!='null') {
		if ($function[6]==1) {
			$this->ASarrowhead($this->pt2arr("{$fx[$stopat-2]},{$fy[$stopat-2]}"),$this->pt2arr("$x,$y"));
		} else if ($function[6]==2) {
			$this->ASdot2(array("[$x,$y]","open"));
		} else if ($function[6]==3) {
			$this->ASdot2(array("[$x,$y]","closed"));
		}
	}
}
function pt2arr($pt) {
	$pt = str_replace(array('[',']'),'',$pt);
	$pt = explode(',',$pt);
	$pt[0] = round($this->evalifneeded($pt[0])*$this->xunitlength + $this->origin[0]);
	$pt[1] = round($this->height - $this->evalifneeded($pt[1])*$this->yunitlength - $this->origin[1]);
	return $pt;
}
function parseargs($str) {
	$lp = 0; $qd = 0; $bd=0; $args = array();
	for($i=0; $i<strlen($str); $i++) {
		if ($str{$i}=='[' && $qd==0) { $bd++;}
		if ($str{$i}==']' && $qd==0) { $bd--;}
		if ($str{$i}=='"' || $str{$i}=='\'') {
			$qd = 1-$qd;
		}
		if ($str{$i}==',' && $qd==0 && $bd==0) {
			if ($i>$lp) {
				$args[] = substr($str,$lp,$i-$lp);
			} else {
				$args[] = '';
			}
			$lp = $i+1;
		}
	}
	$args[] = substr($str,$lp);
	for ($i=0;$i<count($args);$i++) {
		$args[$i] = str_replace(array('"','\''),'',$args[$i]);
	}
	return $args;
}
function outputimage() {
	if (func_num_args()>0) {
		$filename = func_get_arg(0);
		imagepng($this->img,$filename,8);
	} else {
		imagepng($this->img,null,8);
	}
}
function evalifneeded($str) {
	if (is_numeric($str)) {
		return $str;
	} else if (preg_match('/[^\d+\-\/\*\.]/',$str)) {
		return $str;
	} else {
		eval("\$ret = $str;");
		return $ret;
	}
}
} //end AStoIMG class

?>
