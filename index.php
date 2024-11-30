<?PHP
ini_set("pcre.backtrack_limit", "1000000");
require("sql.php");

function get_control($myget, $key, $default) {
    $mydefault = $default;

    if(isset($myget[$key]))
        $mydefault = $myget[$key];

    return $mydefault;
}

$mode = get_control($_REQUEST, "mode", NULL);
$year = get_control($_REQUEST, "year", NULL);

$name = get_control($_REQUEST, "name", NULL);
$date = get_control($_REQUEST, "date", NULL);
$value = get_control($_REQUEST, "value", NULL);
$event = get_control($_REQUEST, "event", NULL);
$hidden = get_control($_REQUEST, "hidden", NULL);

$multientry = get_control($_REQUEST, "multientry", NULL);

function add_replace_param($array, $newdata) {
    $params           = array_merge( $array, $newdata );
    $new_query_string = http_build_query( $params );

    return $new_query_string;
}

$msg = "no upload.";
$uploadOk = 0;

if("store_entry" == $mode) {
    $msg = "";

    $imageFileType = strtolower(pathinfo(basename($_FILES["myimage"]["name"]),PATHINFO_EXTENSION));
    
    $msg .= "File is not an image.";

    if($check !== false) {
        $msg = "File is an image - " . $imageFileType . ".";
        $uploadOk = 1;
        $msg .= $result;
    }
    
    if(1 == $uploadOk) {
        $sql = sql_store_entry($name, $date, eur_to_int($value), $event, $hidden);
        $result = query($conn, $sql);

        if ($result === TRUE) {
            $last_id = $conn->insert_id;
            
            $target_dir = "uploads/";
            $target_file = $target_dir . $last_id . "." . $imageFileType;
            
            move_uploaded_file($_FILES["myimage"]["tmp_name"], $target_file);
            $msg .= "New record created successfully. Last inserted ID is: " . $last_id;
        } else {
            $msg .= "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    else
        $msg .= "upload not ok.";
}

if("" == $date)
    $date = date("Y-m-d");
        
$header = <<<EOD
    
<html>
<head>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <link href="main.css" rel="stylesheet">
    <script language="JavaScript">
    <!--
    function initform()
    {
        document.getElementById('value').focus();
        document.getElementById('date').value = "$date";
    }
    
    function deactivateSubmitButton() {
        var button = document.getElementById('mysubmit');
        button.setAttribute("disabled", "");
    }
    //-->
    </script>
    <style>
        * {
        box-sizing: border-box;
        }
        
        .zoom {
        padding: 1px;
        //background-color: green;
        //transition: transform .2s;
        width: 10px;
        height: 10px;
        margin-left:1px;
        margin-top:1px;
        }
        
        .zoom:hover {
        -ms-transform: scale(30); /* IE 9 */
        -webkit-transform: scale(30); /* Safari 3-8 */
        transform: scale(30);
        margin-left:145px;
        margin-top:145px;
        }
    </style>
</head>
<body onload="initform()">
<h1>Sasem Dusem Kasse</h1>
EOD;


$footer = "</body></html>";


# navigation:

$navigation = "<a href='?'>start</a>";

$clean = get_control($_GET, "clean", "true");

$navigation .= " | Kaffeekasse: ";
$out = "<a id='top' href='?" . add_replace_param($_GET, array( 'clean' => 'true')) . "'>mit</a>";
if("true" == $clean)
    $out ="<a href='?" . add_replace_param($_GET, array( 'clean' => 'false')) . "'>ohne</a>";

$navigation .= $out;


$detail = get_control($_GET, "detail", "true");

$navigation .= " | Details: ";
$out = "<a href='?" . add_replace_param($_GET, array( 'detail' => 'true')) . "'>ohne</a>";
if("true" == $detail)
    $out ="<a href='?" . add_replace_param($_GET, array( 'detail' => 'false')) . "'>mit</a>";

$navigation .= $out;


$pdf = get_control($_GET, "pdf", "false");

$navigation .= " | PDF: ";
$out = "<a href='?" . add_replace_param($_GET, array( 'pdf' => 'true')) . "'>pdf</a>";

$navigation .= $out;


$debug = get_control($_GET, "debug", "false");

$navigation .= " | Debug: ";
$out = "<a href='?" . add_replace_param($_GET, array( 'debug' => 'true')) . "'>ohne</a>";
if("true" == $debug)
    $out ="<a href='?" . add_replace_param($_GET, array( 'debug' => 'false')) . "'>mit</a>";

$navigation .= $out;


$navigation .= "<br><br>Auswertung nach Veranstaltung: ";
$params = array_merge(array( 'mode' => 'event', 'detail' => $detail, 'clean' => $clean));

$sql = "SELECT * FROM account_event";
$result = query($conn, $sql);
while ($row = $result->fetch_assoc()) {
    $navigation .= "<a href='?" . add_replace_param($params, array( 'id' => $row["id"])) . "'>" . $row["name"]. "</a> | ";
}


$navigation .= "<br><br>Auswertung nach Jahr: ";
$params = array_merge(array( 'mode' => 'year', 'detail' => $detail, 'clean' => $clean));

$sql = "SELECT DATE_FORMAT(date, '%Y') AS year FROM account_entry GROUP BY DATE_FORMAT(date, '%Y')";
$result = query($conn, $sql);
while ($row = $result->fetch_assoc()) {
    $navigation .= "<a href='?" . add_replace_param($params, array( 'year' => $row["year"])) . "'>" . $row["year"]. "</a> | ";
}

$navigation .= "<br><br>Neuer Eintrag: <a href='?mode=add_entry'>Neue Buchung</a><br>######################################<br>";

$file = file_get_contents('./main.css', FILE_USE_INCLUDE_PATH);

$settings = <<<EOD

<style>
$file
</style>

Clean: $clean<br/>
Mit Details: $detail <br/>
Mode: $mode<br/>

EOD;

# control

$sql = "SELECT sum(value) as asset FROM account_account";
$result = query($conn, $sql);
$asset = 0;
while ($row = $result->fetch_assoc()) {
    $asset = $asset + $row["asset"];
}

$mode = get_control($_GET, "mode", NULL);
$multientry_checked = "";
if("checked" == $multientry) {
    $mode = "add_entry";
    $multientry_checked = " checked";
}

if("add_entry" == $mode) {
    $sql = "SELECT * FROM account_event";
    $result = query($conn, $sql);
    $myevents = "<option value='0'>n.a.</option>";
    while ($row = $result->fetch_assoc()) {
        $myevents .= "<option value='" . $row["id"] . "'";
        if($event == $row["id"])
            $myevents .= " selected";
        $myevents .= ">" . $row["name"]. "</option>";
    }
    
    $content = <<<EOD

<p>
<form enctype="multipart/form-data" id="input_entry" action="?" method="post" onsubmit="deactivateSubmitButton()">
    <label>mehrere Eintraege
        <input type="checkbox" id="multientry" name="multientry" value="checked" class="myinput"$multientry_checked>
    </label>
    <br>
    <br>
    <label>Betrag<br>
        <input type="number" step="0.01" id="value" name="value" class="myinput">
    </label>
    <br>
    <label>Datum<br>
        <input type="date" id="date" name="date" class="myinput">
    </label>
    <br>
    <label>Bezeichnung<br>
        <input type="text" id="name" name="name" data-clear-btn="true" class="myinput">
    </label>
    <br>
    <label>Kaffeekasse
        <input type="checkbox" id="hidden" name="hidden" value="checked" class="myinput">
    </label>
    <br><br>
    <label>Veranstaltung<br>
        <select name="event" id="event" class="myinput">
        $myevents
        </select>
    </label>
    <br>
    <label>Bild<br>
        <input type="file" accept="image/*" capture id="myimage" name="myimage" class="myinput">
    </label>
    <br>
    <input id="mysubmit" type="submit" value="speichern" class="myinput">
    <input type="hidden" value="store_entry" name="mode">
</form>
<form enctype="multipart/form-data" id="input_entry" action="?" method="post">
    <input type="submit" value="abbrechen" class="myinput">
</form>
</p>

EOD;
}
else if("event" == $mode) {

    $event = get_control($_GET, "id", 1);

    $sql = "select * from account_event where id = $event";
    $result = query($conn, $sql);
    while ($row = $result->fetch_assoc()) {
        $subtotal = intval($row["subtotal"]);
        $settings =  $row["name"] . "<br/><br/>" . $settings;
    }

    $data = array();

    $sum_before = 0;
    $sql = before_subtotal($event, $clean);
    $result = query($conn, $sql);
    while ($row = $result->fetch_assoc()) {
        $sum_before = $sum_before + $row["value"];
        $detail_content1 .= "<tr><td>" . $row["name"] . "</td><td>" . int_to_eur($row["value"]) . "</td></tr>";
        $data[] = $row;
    }

    $start = $subtotal - $sum_before;

    $sum_after = 0;
    $sql = after_subtotal($event, $clean);

    $result = query($conn, $sql);
    while ($row = $result->fetch_assoc()) {
        $sum_after = $sum_after + $row["value"];
        $flag = "";
        if ("1" == $row["cash"] and "1" == $row["before_subtotal"] and "0" == $row["bill_available"])
            $flag = "<td>ausgenommen</td>";
        $detail_content2 .= "<tr><td>" . $row["name"] . "</td><td>" . int_to_eur($row["value"]) . "</td>" . $flag . "</tr>";
        $data[] = $row;
    }

    $result = $subtotal + $sum_after;

    $content = "<table>";
    $content .= "<tr><th><b>Umsatz             </b></th><th><b>" . int_to_eur($start) . "</b></th></tr>";
    $content .= "<tr><th><b>Bewegung bar       </b></th><th><b>" . int_to_eur($sum_before) . "</b></th></tr>";
    if("true" == $detail)
        $content .= $detail_content1;
    $content .= "<tr><th><b>Zwischensumme      </b></th><th><b>" . int_to_eur($subtotal) . "</b></th></tr>";
    $content .= "<tr><th><b>Bewegung konto     </b></th><th><b>" . int_to_eur($sum_after) . "</b></th></tr>";
    if("true" == $detail)
        $content .= $detail_content2;
    $content .= "<tr><th><b>Ergebnis           </b></th><th><b>" . int_to_eur($result) . "</b></th></tr>";
    $content .= "</table>";

}
else {

    $year = get_control($_GET, "year", NULL);
    $settings =  $year . "<br/><br/>" . $settings;
    
    $sql = "select sum(subtotal) as subtotal from account_event";
    if("year" == $mode)
        $sql .= " where DATE_FORMAT(date, '%Y') = '$year'";
    $result = query($conn, $sql);
    while ($row = $result->fetch_assoc()) {
        $subtotal = intval($row["subtotal"]);
    }

    $sql = "select sum(value) as mysum from account_entry where before_subtotal = '1'";
    if("true" == $clean)
        $sql .= " and bill_available > 0";
    if("year" == $mode)
        $sql .= " and DATE_FORMAT(date, '%Y') = '$year'";
    
    $msg .= $sql;
    
    $result = query($conn, $sql);
    while ($row = $result->fetch_assoc()) {
        $income = intval($row["mysum"]);
    }
    $msg .= $income;
    $data = array();

    $sum_before = 0;
    $sql = before_subtotal(NULL, $clean);
    $result = query($conn, $sql);
    while ($row = $result->fetch_assoc()) {
        $year_db = date( 'Y', strtotime($row["date"]) );
        if("year" != $mode or ("year" == $mode and $year_db == $year))
            $sum_before = $sum_before + $row["value"];
        $data[] = $row;
    }

    $start = 0 - $sum_before;
    $sum = $start;

    $content = "<table>";
    $content .= "<tr><th><b>" . $year . "-01-01</b></th><th><b>Einnahmen Events</b></th><th><b>" . int_to_eur($start) . "</b></th></tr>";

    $sql = "select * from account_entry WHERE cash = 0 or (cash = 1 and bill_available > 0) order by date,id";
    if("false" == $clean)
        $sql = "select * from account_entry order by date,id";
    $result = query($conn, $sql);
    $i = 0;
    while ($row = $result->fetch_assoc()) {
        $year_db = date( 'Y', strtotime($row["date"]) );
        if("year" != $mode or ("year" == $mode and $year_db == $year)) {
            $sum = $sum + $row["value"];
            $flag = "";
            if ("1" == $row["cash"] and "1" == $row["before_subtotal"] and "0" == $row["bill_available"])
                $flag = "<td>ausgenommen</td>";

            $content .= "<tr><td>" . $row["date"];
            
            $filename = "uploads/" . $row["id"] . ".png";
            if(file_exists($filename)) {
                $content .= "<div class=\"zoom\"><img src=\"" . $filename . "\" height=\"10\"/></div>";
            }

            $filename = "uploads/" . $row["id"] . ".jpg";
            if(file_exists($filename)) {
                $content .= "<div class=\"zoom\"><img src=\"" . $filename . "\" height=\"10\"/></div>";
            }

            $content .= "</td><td>" . $row["name"] . "</td><td>" . int_to_eur($row["value"]) . "</td>" . $flag . "</tr>\r\n";
        }
    }

    if("year" == $mode) {
        $content .= "<tr><th><b>" . $year . "-12-31</b></th><th><b>Ergebnis</b></th><th><b>" . int_to_eur($sum) . "</b></th></tr>";
    }

    else {
        $content .= "<tr><th><b>Errechneter Kontostand</b></th><th><b>" . int_to_eur($sum) . "</b></th></tr>";
        $content .= "<tr><th><b>Kontostand</b></th><th><b>" . int_to_eur($asset) . "</b></th></tr>";
    }
    $content .= "</table>";

}

if("false" == $pdf) {
    echo $header;
    if("add_entry" != $mode)
        echo $navigation;
    
    if("true" == $debug)
        echo $msg;
    
    echo $content;
    if("add_entry" != $mode)
        echo "<br><a href='#top'>top</a>";
    echo $footer;
} else {
    require_once('tcpdf/tcpdf.php');
    
    $rechnungs_nummer = "743";
    $rechnungs_datum = date("d.m.Y");
    $lieferdatum = date("d.m.Y");
    $pdfAuthor = "PHP-Einfach.de";
    $pdfName = "Rechnung_".$rechnungs_nummer.".pdf";
 
    // Erstellung des PDF Dokuments
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Dokumenteninformationen
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($pdfAuthor);
    $pdf->SetTitle('Rechnung '.$rechnungs_nummer);
    $pdf->SetSubject('Rechnung '.$rechnungs_nummer);
    
    
    // Header und Footer Informationen
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Auswahl des Font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Auswahl der MArgins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Automatisches Autobreak der Seiten
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Image Scale 
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Schriftart
    $pdf->SetFont('dejavusans', '', 10);
    
    // Neue Seite
    $pdf->AddPage();
    //echo $settings;
    $pdf->writeHTML($settings, true, false, true, false, '');

    $pdf->AddPage();
    
    // Fügt den HTML Code in das PDF Dokument ein
    //echo $content;
    $pdf->writeHTML($content, true, false, true, false, '');
    
    //Ausgabe der PDF
    
    //Variante 1: PDF direkt an den Benutzer senden:
    $pdf->Output($pdfName, 'I');
}

?>