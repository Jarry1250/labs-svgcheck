<?php
	/*
	common.php © 2010 Harry Burt <jarry1250@gmail.com>

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
	*/

	// Remember to change both!
	$permatempPath = '/data/project/svgcheck/public_html/permatemp/';
	$permatempWeb = 'permatemp/';

	function isproblematic( $line ) {
		$errors = array();
		if( strpos( $line, "http://" ) !== false && !preg_match( '/(([a-z]+)(:[a-z]+)?="|[<]![^>]*)http:/i', $line ) ){
			$errors[] = "*Warning* http:// external reference found.\n\t These may be innocuous document declarations (particularly early on, and no problem)\n\t but if they form file references, they will not work (and may be blocked) by the Wikimedia software.\n\t All required elements need to be included in the SVG directly.";
		}
		if( strpos( $line, "<image" ) !== false ){
			$errors[] = "*ERROR* image tag found.\n\t Since image tags refer to external references, they will not work (and may be blocked) by the Wikimedia software.\n\t All required elements need to be included in the SVG directly.";
		}
		if( strpos( $line, "C:\\" ) !== false ){
			$errors[] = "*ERROR* Local file reference found.\n\t These will not work (and may be blocked) by the Wikimedia software.\n\t All required elements need to be included in the SVG directly.";
		}
		if( strpos( $line, "<flow" ) !== false ){
			$errors[] = "*ERROR* Flow element found.\n\t These will not render properly.\n\t If you're not actually trying to bend the text, try opening with a text editor and removing the elements.\n\t If you are actually trying to bend it, use 'Convert to path' (Inkscape) or similar.";
		}
		if( strpos( $line, "url(&quot;" ) !== false ){
			$errors[] = "*Warning* Your editor has accidentally added &quot; marks inside an url(), which you will need to remove by hand.";
		}
		if( preg_match( '/<[^ ]+:pgf/', $line ) ){
			$errors[] = "*Warning* It appears you have a pgf metadata attribute. While in rare cases this can be helpful, it usually just adds to filesize, and can cause Inkscape and other editors to choke.";
		}
		if( preg_match( '/= *"[^"]+ "/', $line ) ){
			$errors[] = "*Warning* It appears you have a trailing space in one of your attributes. With certain attributes, this can cause a rendering problem.";
		}
		if( preg_match( '/font-size *: *[0-9.]+%/', $line ) ){
			$errors[] = "*Warning* The renderer used to struggle with percentage-based font size specifications, although this issue seems to have been resolved recently.";
		}
		if( preg_match( '/font-family *: */', $line ) && !preg_match( '/font-family *: *("? *(Arrunta|Bandal|Bangwool|Bitstream Charter|Bitstream Vera Sansà¦…à¦¨à¦¿  Dvf|aakar|Abyssinica SIL|Aksharyogini|Ani|AnjaliOldLipi|AR PL UKai CN|AR PL UKai HK|AR PL UKai TW|AR PL UKai TW MBE|AR PL UMing CN|AR PL UMing HK|AR PL UMing TW|AR PL UMing TW MBE|Arrunta|Bandal|Bangwool|Bitstream Charter|Bitstream Vera Sans|Bitstream Vera Sans Mono|Bitstream Vera Serif|Century Schoolbook L|Chandas|Charter|Clean|ClearlyU|ClearlyU Alternate Glyphs|ClearlyU PUA|Courier|Courier 10 Pitch|DejaVu Sans|DejaVu Sans Condensed|DejaVu Sans Light|DejaVu Sans Mono|DejaVu Serif|DejaVu Serif Condensed|Dingbats|Eunjin|EunjinNakseo|Ezra SIL|Ezra SIL SR|Fixed|fxd|gargi|Garuda|goth_p|gothic|Guseul|Helvetica|hlv|hlvw|Homa|Jamrul|KacstArt|KacstBook|KacstDecorative|KacstDigital|KacstFarsi|KacstOne|KacstOneFixed|KacstPoster|KacstQurn|KacstTitle|KacstTitleL|Kalimati|Kedage|Khmer OS|Khmer OS Battambang|Khmer OS Bokor|Khmer OS Content|Khmer OS Fasthand|Khmer OS Freehand|Khmer OS Metal Chrieng|Khmer OS Muol|Khmer OS Muol Light|Khmer OS Muol Pali|Khmer OS Siemreap|Khmer OS System|Kochi Gothic|Kochi Mincho|Liberation Mono|Liberation Sans|Liberation Serif|Likhan|Lohit Bengali|Lohit Kannada|Lohit Oriya|Lohit Telugu|Loma|Lucida|LucidaBright|LucidaTypewriter|Mallige|Manchučejné|medium|MgOpen Canonica|MgOpen Cosmetica|MgOpen Modata|MgOpen Moderna|Mitra Mono|mry_KacstQurn|Nafees|Nafees Web Naskh|Nakula|Navadno|Nazli|New Century Schoolbook|Newspaper|Nimbus Mono L|Nimbus Roman No9 L|Nimbus Sans L|Norasi|Normaali|Normál|Normale|Normálne|Normalny|Padauk|padmaa|padmaa-Bold.1.1|padmmaa|padmmaa.1.1|Phetsarath OT|Pothana20002000|Purisa|qub|Rachana_w01|Rekha|Saab|Sahadeva|Samanata|Sarai|Sawasdee|Scheherazade|SIL Yi|Standaard|Standard|Standard Symbols L|sys|TAMu_Kadambri|TAMu_Kalyani|TAMu_Maduram|Terminal|Tibetan Machine Uni|Times|Titr|Tlwg Typist|TlwgMono|TlwgTypewriter|TSCu_Comic|TSCu_Paranar|TSCu_Times|UnBom|UnGraphic|UnGungseo|UnJamoBatang|UnJamoDotum|UnJamoNovel|UnJamoSora|UnPen|UnPenheulim|UnPilgi|UnShinmun|UnTaza|UnYetgul|URW Bookman L|URW Chancery L|URW Gothic L|URW Palladio L|Utopia|VL Gothic|VL PGothic|VL Pゴシック|VL ゴシック|Waree|WenQuanYi Bitmap Song|WenQuanYi Zen Hei|Κανονικά|Обычный|नालिमाटीकालिमाटी|구슬|반달|방울|은 궁서|은 그래픽|은 봄|은 신문|은 옛글|은 자모 노벨|은 자모 돋움|은 자모 바탕|은 자모 소라|은 타자|은 펜|은 펜흘림|은 필기|은진|은진낙서|文泉驛正黑|文泉驿正黑中等|東風ゴシック標準|東風明朝標準|serif|sans-serif|cursive|fantasy|monospace) *"? *,?)+ *([;"]|[^A-Za-z]*[}])/', $line ) ){
			$errors[] = "*Warning* You appear to have specified a font that does not exist on Wikimedia wikis.";
		}
		if( preg_match( '/font-family *: */', $line ) && !preg_match( '/font-family *:[^";]*([^;"]*"[^"]+"[^;"]*)*[^";](serif|sans-serif|cursive|fantasy|monospace) *[;"]/', $line ) ){
			$errors[] = "*Warning* You should define a fallback font type, which should not be placed in quote marks and should be in lower case.\n\tAllowable types are serif, sans-serif, cursive, fantasy and monospace.";
		}
		if( strpos( $line, 'xmlns="&' ) || strpos( $line, 'xmlns:xlink="&' ) ){
			$errors[] = "*ERROR* Your editor has attempted to use entity references in its <svg> tag.\n\tThe easiest solution is to open the file in a text editor and replace the offending xmlns and/pr xmlns:xlink parameters with a straightforward URL, e.g.\n\t" . '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ' . "\n\tfollowed by the existing height, width and other attributes.";
		}
		if( count( $errors ) > 0 ){
			return $errors;
		}
		return false;
	}

?>
