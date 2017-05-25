
<?php 
/*
This code was scraped together by Nick G and Wade S during the GeoHuntsville Hackathon.  Need to still address:
1) web directory to local directory mapping
2) GeoJSON format for images embedded
3) Modificaitons to the GeoJSON import in GeoQ
4) Comments and attribution (esp for GetGPps function and gps2Num function)
5) Still need to figure out the right places to put the data in the GeoJSON to expose it as an image (thumbnail) and clickable URL in GeoQ
*/
// Set this variable to the direcoty that serves the images
$webdirectory = 'http://rcal.ist.psu.edu/campusconstruction/';
function getGps($exifCoord, $hemi) {
    $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;
    $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;
    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
}
function gps2Num($coordPart) {
    $parts = explode('/', $coordPart);
    if (count($parts) <= 0)
        return 0;
    if (count($parts) == 1)
        return $parts[0];
    return floatval($parts[0]) / floatval($parts[1]);
}
function reportGps ($exif) {
    $lon = getGps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
    $lat = getGps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
    $datetime = $exif["DateTimeOriginal"];
    return array ($lat,$lon,$datetime);
}
// Main
/*
MR: Added GeoQ parameters below:
*/
function head()
{
    $heads = <<<EOS
    <?xml version="1.0" encoding="UTF-8"?>
    <kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>
EOS;
return $heads;        
}

function footer()
{
    $foots = <<<foot
    </Document>
    </kml>
foot;
return $foots;
}

function Placemark($link,$lat,$lon,$datetime)
{
    $place = <<<EOS
        <Placemark>
        <name>Photo $datetime</name>
        <description> <![CDATA[ <img src = ".$link." width = 300 height = 240>]]> </description>
        <Point>            
        <coordinates>$lon,$lat,0</coordinates>
        </Point>
        </Placemark>
EOS;
return $place;
}


header('Content-Type: application/kml');
print head();

$directory = './';
$scanned_directory = array_diff(scandir($directory), array('..', '.', '.images.php.swp', 'images.php','.php'));
foreach ($scanned_directory as $filename) {
    
    if ((strpos($filename, '.jpg') !== false ) OR (strpos($filename, '.JPG') !== false )) {
        $exifforfile = exif_read_data($filename);
        list ($lat,$lon,$datetime) = reportGps ($exifforfile);
        $link = $webdirectory.$filename;
        print Placemark($link,$lat,$lon,$datetime);
        }
}


print footer();
 ?>
