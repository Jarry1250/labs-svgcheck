<?php
	/*
	Stable version of SVGCheck © 2011 Harry Burt <jarry1250@gmail.com>

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

	$regexpart = '/^[a-zA-Z0-9 ;()_]+$/';
	$regexfull = '/^[a-zA-Z0-9 ;()_]+[.]svg$/';
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
		$target_path = strtolower( "permatemp/" . $name );
		if( $_FILES['uploadedfile']['error'] != 0 ){
			die( "There was an unknown upload error." );
		}
		$shortname = substr( $name, 0, -4 );
		if( preg_match( $regexfull, $name ) === false ){
			die ( "For security reasons, we require that you use a simpler name." );
		}
		if( move_uploaded_file( $_FILES['uploadedfile']['tmp_name'], $target_path ) ){
			exec( "rsvg-convert '" . $target_path . "' -o 'permatemp/" . $shortname . ".png'" );
			list( $width, $height ) = getimagesize( "permatemp/" . $shortname . ".png" );
			?>
			<h3>What it will render like</h3>
			<p>This image was rendered using rsvg version 2.35.2; as of time of writing, Wikimedia wikis use a slightly
				different version (2.36.1). Note also that this is a temporary rendering and will soon be deleted.</p>
			<p>
			<div
				style="background: url(transcheck.png) repeat; width:<?php echo $width; ?>px; height:<?php echo $height; ?>px;">
				<img width="<?php echo $width; ?>px" height="<?php echo $height; ?>px"
					 src="permatemp/<?php echo $shortname; ?>.png"/></div></p>
			<?php
			if( $diagnose ){
				$f = fopen( $target_path, 'r' ) or die( "There was an error reading the file for error diagnosis purposes." );
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
			?>
			<form action="index.php" method="post">
				<input type="hidden" name="old" value="<?php echo $shortname; ?>"/>
				<input type="submit" value="Hold on, I have another to check"/>
			</form>
		<?php
		} else {
			echo "There was an error uploading the file, please try again!";
			print_r( $_FILES );
		}
	} else {
		if( isset( $_POST["old"] ) ){
			$old = $_POST["old"];
			if( preg_match( $regexpart, $old ) ){
				unlink( "permatemp/" . strtolower( $old ) . ".svg" );
				if( file_exists( "permatemp/$old" . "op.svg" ) ){
					unlink( "permatemp/$old" . "op.svg" );
				}
				if( file_exists( "permatemp/$old" . "op2.svg" ) ){
					unlink( "permatemp/$old" . "op2.svg" );
				}
				if( file_exists( "permatemp/$old" . "op2.png" ) ){
					unlink( "permatemp/$old" . "op2.png" );
				}
				unlink( "permatemp/$old.png" );
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
			security risk and will be blocked, I'm sure you understand. Queries can be directed to me on-wiki or to my
			username at Gmail (it stands for Google email, you know) dot com.</p>
		<h3>Form</h3>
		<form action="index.php" enctype="multipart/form-data" method="POST">
			<input name="uploadedfile" type="file"/>&nbsp;&nbsp;&nbsp;<input type="checkbox" value="diagnose"
																			 name="options[]" checked="checked"/>Check
			this file for errors&nbsp;&nbsp;&nbsp;<input type="submit" value="Check this SVG"/>
		</form>
	<?php
	}
	echo get_html( 'footer' );
?>