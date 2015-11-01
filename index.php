<?php
	/*
	Stable version of SVGCheck © 2011-2013 Harry Burt <jarry1250@gmail.com>
	There is an associated crontab for this tool

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

	ini_set( "display_errors", 1 );
	error_reporting( E_NOTICE );
	require_once( 'common.php' );
	require_once( '/data/project/jarry-common/public_html/global.php' );

	// If changing $permatempPath, note the hardcoding of the 'external' version below
	$permatempPath = '/data/project/svgcheck/public_html/permatemp/';

	$charset = '/[^a-zA-Z0-9 ;()_.]/';
	$diagnose = ( isset( $_POST['options'] ) && in_array( 'diagnose', $_POST['options'] ) ) ? true : false;

	echo get_html( 'header', 'SVG Check' );

	if( isset( $_FILES['uploadedfile'] ) ){
		if( $_FILES['uploadedfile']['type'] !== "image/svg+xml" ){
			$text = "That file doesn't appear to be an SVG file.";
			if( $_FILES['uploadedfile']['type'] === "application/x-octetstream" ){
				$text .= ' On Firefox and convinced it is? This is a known bug with a <a href="http://www.harryburt.co.uk/blog/2012/04/01/svg-files-as-applicationx-octetstream/">simple fix</a>.';
			}
			die( $text );
		}
		$name = basename( $_FILES['uploadedfile']['name'] );
		$name = preg_replace( $charset, '', $name );
		if( strlen( $name ) == 4 ){
			// All characters stripped
			$name = 'Test.svg';
		}
		if( strtolower( substr( $name, -4 ) ) != '.svg' ){
			die( "All SVG uploads should end in .svg. Please try again." );
		}
		if( $_FILES['uploadedfile']['error'] != 0 ){
			die( "There was an unknown upload error." );
		}
		$svgPath = $permatempPath . strtolower( $name );
		$pngPath = substr( $svgPath, 0, -4 ) . '.png';
		if( move_uploaded_file( $_FILES['uploadedfile']['tmp_name'], $svgPath ) ){
			exec( "/usr/bin/rsvg-convert '" . $svgPath . "' -o '" . $pngPath . "'" );
			exec( "chmod 0605 '$pngPath'" );
			list( $width, $height ) = getimagesize(  $pngPath  );
			?>
			<h3>What it will render like</h3>
			<p>This image was rendered using <?= exec( '/usr/bin/rsvg-convert -v' )?>, almost certainly the same version as in use on Wikimedia wikis. Note also that this is a temporary rendering and will soon be deleted.</p>
			<p>
			<div
				style="background: url(transcheck.png) repeat; width:<?php echo $width; ?>px; height:<?php echo $height; ?>px;">
				<img width="<?php echo $width; ?>px" height="<?php echo $height; ?>px"
					 src="permatemp/<?php echo substr( strtolower( $name ), 0, -4 ) . '.png'; ?>"/></div></p>
			<?php
			if( $diagnose ){
				$f = fopen( $svgPath, 'r' ) or die( "There was an error reading the file for error diagnosis purposes." );
				echo '<h3>Debugging information</h3>' . "\n<pre>Starting to debug...\n";
				$ln = 1;
				$noerrorsfound = true;
				while( !feof( $f ) ){
					$line = fgets( $f );
					$errors = isproblematic( $line );
					if( $errors !== false ){
						foreach( $errors as $error ){
							printf( "Line %3d: " . htmlspecialchars( $error ) . "\n", $ln );
						}
						$noerrorsfound = false;
					}
					$ln++;
				}
				if( $noerrorsfound ){
					echo "No issues found.\n";
				}
				fclose( $f );
				echo "\n</pre>";
			}
			unlink( $svgPath );
			?>
			<form action="index.php" method="post">
				<input type="hidden" name="old" value="<?php echo $name; ?>"/>
				<input type="submit" value="Hold on, I have another to check"/>
			</form>
		<?php
		} else {
			echo "<br /><br />As you can see, there was an error uploading the file, please try again!";
		}
	} else {
		if( isset( $_POST["old"] ) ){
			$old = $_POST["old"];
			$old = preg_replace( $charset, '', $old );
			if( strtolower( substr( $old, -4 ) ) == '.svg' ){
				unlink( $permatempPath . strtolower( $old ) );
				unlink( $permatempPath . substr( strtolower( $old ), 0, -4 ) . ".png" );
			}
		}
		?>
		<h3>Instructions</h3>
		<p>Upload your SVG file to begin. A temporary (you have been warned!) PNG rendering of your SVG will be
			generated, at 1:1 size, and exactly how the Wikimedia servers would render it, warts and all.</p>
		<p>Additionally, if you have ticked the 'Check this file for errors' box, then an output showing any
			automatically detected (possible) errors will be generated. This is designed for files known to be causing
			errors; it is therefore inclusive and will give more output than is normally necessary in order to ensure
			that the problem is found. Some errors are fixable using a GUI like Inkscape; others will require you to
			edit the file using a text editor like Wordpad. </p>
		<p>Please <strong>be patient</strong>, the process can take some time. Some filenames represent a possible
			security risk and will be blocked. Queries can be directed to me on-wiki or to my
			username at Gmail (it stands for Google email, you know) dot com.</p>
		<h3>Form</h3>
		<form action="index.php" enctype="multipart/form-data" method="POST">
			<input name="uploadedfile" type="file"/>&nbsp;&nbsp;&nbsp;
			<input type="checkbox" value="diagnose" name="options[]" checked="checked"/>Check this file for errors
			&nbsp;&nbsp;&nbsp;<input type="submit" value="Check this SVG"/>
		</form>
	<?php
	}
	echo get_html( 'footer' );