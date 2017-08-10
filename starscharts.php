<?php
/*
Plugin Name: Stars Charts
Plugin URI: http://starswebservice.com
Description: Plugin to display an Astrology Chart
Version: 2.2
History
2.2
Copyright link moved to front page
2.1
Planetary Points and Aspects Displayed in more concise way.
Planetary Points and Aspects graphic replaced with table in non Wordpress version.

Author: David Klugmann	
Author URI: http://www.starswebservice.com
*/

session_start();
define("IMPLEMENTATION", "WORDPRESS");
define("WALESID",1000);
define("SCOTLANDID",2000);
define("ENGLANDID",3000);
define("NIRELANDID",4000);
header('Content-Type: text/html; charset=utf-8');

if (IMPLEMENTATION == "STANDARD")
{
    printf("<link rel=\"stylesheet\" type=\"text/css\" href=\"sc_stars.css?id=%d\">",time());
    printf("<script type=\"text/javascript\" src=\"sc_stars.js?id=%d\" language=\"javascript1.2\"></script>",time());
}
if (IMPLEMENTATION == "WORDPRESS")
    add_action('wp_enqueue_scripts','sc_include',999);

function sc_include() 
{
    $url = sprintf("sc_stars.css?id=%d",time());
    wp_enqueue_style( 'sc-stars-style', plugins_url($url, __FILE__) );
    $url = sprintf("sc_stars.js?id=%d",time());
    wp_enqueue_script( 'sc-stars-js', plugins_url( $url, __FILE__ ) );
}

if (IMPLEMENTATION == "WORDPRESS")
    include( plugin_dir_path( __FILE__ ) . 'sc_stars_config.php');
if (IMPLEMENTATION == "STANDARD")
    include ('sc_stars_config.php');

function sc_display_input_form() 
{

    global $apikey;
    global $enginepath;
    global $enginename;

    global $townmatch1;
    global $towncount1;
    global $failednoname1;
    global $failednotown1;
    global $failednocountry1;
    global $failednodob1;
    global $failednotime1;
    global $failedzerotown1;
    global $foundmultipletown1;
    global $failedmultipletown1;
    global $foundvaguetown1;
    global $failednoemail;

    $name1 = $_POST['name1'];
    $dob1 = $_POST['dob1'];
    $unknowntime1 = $_POST['unknowntime1'];
    if (isset($unknowntime1))
       $unknowntime1 = 'Y';
    else
        $unknowntime1 = 'N';
    $time1 = $_POST['time1'];
    $town1 = $_POST['town1'];
    $townselect1 = $_POST['townselect1'];
    $email =  $_POST['email'];
    $usebirth = $_POST['usebirth'];
    $starspath = "http://www.myastrologycharts.com/astroservice/";
    $timedisplaystyle = 'HH:MI:SS AM/PM';
    $dateinputstyle = 'CHOOSE';
    $nousstates = 52;
    if ( !isset( $_POST['submitted'] ) ) 
         $countryid1 = -1;
    else
        $countryid1 = $_POST['countryid1'];

    if ( !isset( $_POST['submitted'] ) ) 
    {
        $email = $_SESSION['session_email'];

        $name1 =  $_SESSION['session_name1'];
        $dob1 = $_SESSION['session_dob1'];
        $time1 = $_SESSION['session_time1'];
        $unknowntime1 = $_SESSION['session_unknowntime1'];
        $town1 = $_SESSION['session_town1'];
        if ($_SESSION['session_countryid1'])
            $countryid1 = $_SESSION['session_countryid1'];
    }
    printf ("<html><body>");
    echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';

    printf ("<table class=\"entrytable\" style=\"max-width:500px; width:100%%\">");
    printf ("<tr><td colspan=2 class=\"persontitle\">Enter Person's Details</td></tr>");
    if ($failednoname1)
        printf ("<tr><td colspan=2 class=\"errormessage\">*Please enter a Name</td></tr>");
    printf ("<tr><td class=\"entrytitle\">Name</td><td class=\"entrystandard\"><input type=\"text\" class=\"entryinput\" name=\"name1\" value=\"%s\"></input></td></tr>",$name1);

    if ($failednodob1)
        printf ("<tr><td colspan=2 class=\"errormessage\">*Please enter a Date of Birth</td></tr>");
    printf ("<tr><td class=\"entrytitle\">Date of Birth</td><td class=\"entrystandard\"><input type=\"text\" class=\"entryinput\" onchange=\"sc_checkdate(this,'%s');\" id=\"dob1\" name=\"dob1\" value=\"%s\"></input></td></tr>",$dateinputstyle,$dob1);
    if ($failednotime1)
        printf ("<tr id=\"enter1time\"><td colspan=2 class=\"errormessage\">*Please enter a Time of Birth</td></tr>");
    printf ("<tr id=\"time1line\"><td class=\"entrytitle\">Time of Birth</td><td class=\"entrystandard\"><input type=\"text\" class=\"entryinput\" onchange=\"sc_checktime(this,'%s');\" id=\"time1\" name=\"time1\" value=\"%s\"></input></td></tr>",$timedisplaystyle,$time1);
    printf ("<tr><td class=\"entrytitle\">Unknown Time</td>");
    printf ("<td class=\"entrystandard\"><input style=\"padding:0px; margin:0px; border:0px;\" name=\"unknowntime1\" id=\"unknowntime1\" type=\"checkbox\" onchange=\"sc_checkdisplaytime(1);\"");
    if ($unknowntime1 == 'Y')
        printf (" checked");
    printf ("></input></td></tr>");
    if ($failednotown1)
        printf ("<tr><td colspan=2 class=\"errormessage\">*Please enter a City</td></tr>");
    elseif ($foundvaguetown1)
    {
        printf ("<tr><td colspan=2 class=\"errormessage\">*Closest Town Listed / Please proceed to Confirm</td></tr>");
        $town1 = $townmatch1[0]->town;
    }
    elseif ($failedzerotown1)
        printf ("<tr><td colspan=2 class=\"errormessage\">*Failed to find that city in the US State / Country</td></tr>");
    if (!$foundmultipletown1)
    {
        printf ("<tr><td class=\"entrytitle\">Enter City Only</td><td class=\"entrystandard\"><input type=\"text\" class=\"entryinput\" type=text id=\"town1\" name=\"town1\" value=\"%s\"></input></td></tr>",$town1);
        if ($failednocountry1)
            printf ("<tr><td colspan=2 class=\"errormessage\">*Please select a US State / Country</td></tr>");
        printf ("<tr>");
        printf ("<td class=\"entrytitle\">US State / Country</td>");
        printf ("<td class=\"entrystandard\">");
        $url = $starspath . "/listcountries.php";
		$returnxmlstring = sc_loadXML($url);
        printf ("<select class=\"entryinput\" name=\"countryid1\" id=\"countryid1\" size=1>");
        printf ("<option value=-1></option>");
        $displaywales1 = FALSE;
        $displayscotland1 = FALSE;
        for ($count = $nousstates; $count <$returnxmlstring->rowsreturned; $count++)
        {
            if ((strcasecmp($returnxmlstring->country[$count],"Wales") > 0) && $displaywales1 == FALSE)
            {
                printf ("<option value=%d", WALESID);
                if ($countryid1 == WALESID)
                    printf (" selected=selected");
                printf (">%s</option>","Wales");
                $displaywales1 = TRUE;
            }
            if ((strcasecmp($returnxmlstring->country[$count],"Scotland") > 0) && $displayscotland1 == FALSE)
            {
                printf ("<option value=%d", SCOTLANDID);
                if ($countryid1 == SCOTLANDID)
                    printf (" selected=selected");
                printf (">%s</option>","Scotland");
                $displayscotland1 = TRUE;
            }
            if ((strcasecmp($returnxmlstring->country[$count],"England") > 0) && $displayengland1 == FALSE)
            {
                printf ("<option value=%d", ENGLANDID);
                if ($countryid1 == ENGLANDID)
                    printf (" selected=selected");
                printf (">%s</option>","England");
                $displayengland1 = TRUE;
            }
            if ((strcasecmp($returnxmlstring->country[$count],"Northern Ireland") > 0) && $displaynireland1 == FALSE)
            {
                printf ("<option value=%d", NIRELANDID);
                if ($countryid1 == NIRELANDID)
                    printf (" selected=selected");
                printf (">%s</option>","Northern Ireland");
                $displaynireland1 = TRUE;
            }
            printf ("<option value=%d", $count);
            if ($count == $countryid1)
                printf (" selected=selected");
            printf (">%s</option>",$returnxmlstring->country[$count]);
        }
        for ($count = 0; $count <$nousstates; $count++)
        {
            printf ("<option value=%d", $count);
            if ($count == $countryid1)
                printf (" selected=selected");
            printf (">%s</option>",$returnxmlstring->country[$count]);
        }
        printf ("</select>");
        printf ("</td></tr>");

    }
    else
    {
        printf ("<input type=\"hidden\" name=town1 value=\"%s\">",$town1);
        printf ("<input type=\"hidden\" name=countryid1 value=\"%d\">",$countryid1);
        printf ("<tr><td colspan=2 class=\"errormessage\">Multiple cities found. Please choose one.</td></tr>");
        printf ("<tr><td colspan=2 class=\"entrylong\"><select class=\"entryinput\" name=\"townselect1\" size=5>");
        for ($count = 0; $count <$towncount1; $count++)
        {
            $townidentifier = sprintf("%s#%s#%s#%d#%d#%d#%d", $townmatch1[$count]->town,$townmatch1[$count]->county,$townmatch1[$count]->country,$townmatch1[$count]->latitude,$townmatch1[$count]->longitude,$townmatch1[$count]->typetable,$townmatch1[$count]->zonetable);
            printf ("<option");
            if ($townidentifier == $townselect1)
                 printf (" selected");
            printf (" value=\"%s\">%s %s %s Lat (%.2lf) Long (%.2lf)</option>",$townidentifier,ucwords($townmatch1[$count]->town), ucwords($townmatch1[$count]->county), ucwords($townmatch1[$count]->country),($townmatch1[$count]->latitude/3600), ($townmatch1[$count]->longitude/3600)*-1);
        }
        printf ("</select></td></tr></select>");
    }
    printf ("<tr><td colspan=2 style=\"text-align:center; padding-top:25px;\">");
    printf ("<input type=\"submit\" name=\"submitted\" value=\"Generate Chart\" onclick=\"res=sc_checkdatesok(document.getElementById('dob1'), document.getElementById('time1'),'%s', '%s'); return(res);\">",$dateinputstyle,$timedisplaystyle);
    printf ("</td></tr>");
    printf ("<tr><td colspan=2 style=\"padding-top:30px; font-size:75%%\">&copy %s <a class=\"copyrightlink\" href=\"http://www.seeingwithstars.net\">Seeingwithstars</a> & <a class=\"copyrightlink\" href=\"http://www.myastrologycharts.com\">Myastrologycharts</a></td></tr>",date("Y"));
    printf ("</table>");
    printf ("</form>");
    printf ("</body>");
    printf ("</html>");
    printf ("<script>");
    printf ("sc_checkdisplaytime(1);");
    printf ("</script>");
}
function sc_validate_page()
{
    global $townmatch1;
    global $towncount1;
    global $failednoname1;
    global $failednotown1;
    global $failednocountry1;
    global $failednodob1;
    global $failednotime1;
    global $failedzerotown1;
    global $foundmultipletown1;
    global $failedmultipletown1;
    global $foundvaguetown1;

    $retstatus = 1;
    $dob1 = $_POST['dob1'];
    $time1 = $_POST['time1'];
    $unknowntime1 = $_POST['unknowntime1'];
    $name1 = $_POST['name1'];
    $town1 =  $_POST["town1"];
    $countryid1 = $_POST['countryid1'];
    $townselect1 = $_POST['townselect1'];
    if (isset($unknowntime1))
       $unknowntime1 = 'Y';
    else
        $unknowntime1 = 'N';
    if (!$name1)
        $failednoname1 = TRUE;
    if (!$town1)
        $failednotown1 = TRUE;
    if ($countryid1 < 0)
        $failednocountry1 = TRUE;
    if (!$dob1)
        $failednodob1 = TRUE;
    if (!$time1 && $unknowntime1 != 'Y')
        $failednotime1 = TRUE;
    if (!$email)
        $failednoemail = TRUE;
    $searchcountryid1 = $countryid1;

    $starspath = "http://www.myastrologycharts.com/astroservice/";
    if (!$failednotown1 && !$failednocountry1)
    {
        $url = $starspath . "/listcountries.php";
		$returnxmlstring = sc_loadXML($url);
        for ($count = 0; $count <$returnxmlstring->rowsreturned; $count++)
        {
            if (!strcasecmp($returnxmlstring->country[$count],"United Kingdom"))
            {
                $ukid = $count;
                break;
            }
        }
        if ($countryid1 == WALESID || $countryid1 == SCOTLANDID || $countryid1 == ENGLANDID || $countryid1 == NIRELANDID)
            $searchcountryid1 = $ukid;
        else
            $searchcountryid1 = $countryid1;

        $url = sprintf("%s/listtowns.php?countryid=%d&town=%s",$starspath,$searchcountryid1,urlencode($town1));
		$returnxmlstring = sc_loadXML($url);
        $towncount1 = $returnxmlstring->rowsreturned;
        for ($count = 0; $count < $towncount1; $count++)
        {

            if ($countryid1 == WALESID)
                $country1 = 'Wales';
            elseif ($countryid1 == SCOTLANDID)
                $country1 = 'Scotland';
            elseif ($countryid1 == ENGLANDID)
                $country1 = 'England';
            elseif ($countryid1 == NIRELANDID)
                $country1 = 'Northern Ireland';
            else
                $country1 = $returnxmlstring->country[$count];

            $townmatch1[$count] = new StdClass;
            $townmatch1[$count]->town=$returnxmlstring->town[$count];
            $townmatch1[$count]->county=$returnxmlstring->county[$count];
            $townmatch1[$count]->country=$country1;
            $townmatch1[$count]->latitude=$returnxmlstring->latitude[$count];
            $townmatch1[$count]->longitude=$returnxmlstring->longitude[$count];
            $townmatch1[$count]->typetable=$returnxmlstring->typetable[$count];
            $townmatch1[$count]->zonetable=$returnxmlstring->zonetable[$count];
            $townmatch1[$count]->vague=$returnxmlstring->vague[$count];

        }
        if ($towncount1 == 0)
        {
            $failedzerotown1 = true;
        }
        else if ($townmatch1[0]->vague == 'TRUE')
        {
            $foundvaguetown1 = TRUE;
        }
        else if ($towncount1 > 1)
        {
            $foundmultipletown1 = true;
            if (!$townselect1)
                $failedmultipletown1 = TRUE;
        }
    }
    if (!$email)
        $failednoemail = TRUE;
    if ($failednodob1 || $failednotime1 || $failednoname1 || $failednotown1 || $failednocountry1 || $failedzerotown1 || $failedmultipletown1 || $foundvaguetown1)
        $retstatus = 0;
    return ($retstatus);

}
 
function sc_loadXML($url)
{
    if (ini_get('allow_url_fopen') == true)
    {
         return sc_load_xml_fopen($url);
    } 
    else if (function_exists('curl_init'))
    {
         return sc_load_xml_curl($url);
    }
    else
    {
         throw new Exception("Can't load data.");
    }
}
 
function sc_load_xml_fopen($url)
{
    return simplexml_load_file($url);
}
 
function sc_load_xml_curl($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return simplexml_load_string($result);
}

function sc_loadContents($url)
{
    if (ini_get('allow_url_fopen') == true)
    {
         return sc_load_contents_get($url);
    }
    else if (function_exists('curl_init'))
    {
         return sc_load_contents_curl($url);
    }
    else
    {
         throw new Exception("Can't xload data.");
    }
}
 
function sc_load_contents_get($url)
{
    return file_get_contents($url);
}
 
function sc_load_contents_curl($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
function sc_initiate() 
{
    if (IMPLEMENTATION == "WORDPRESS")
        ob_start();
    $processed = sc_process_report();
    if (!$processed)
        sc_display_input_form();
    if (IMPLEMENTATION == "WORDPRESS")
        return ob_get_clean();
}

function sc_process_report()
{
    global $starspath;
    global $enginepath;
    global $enginename;
    global $imagepath;
      
    global $townmatch1;
    global $townmatch2;
    global $townmatchreturn;
    global $apikey;
    $processed = FALSE;
    $monthnames = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    if ( isset( $_POST['submitted'] ) ) 
    {
        $result = sc_validate_page();
        if ($result)
        {

            $dob1 = $_POST['dob1'];
            $time1 = $_POST['time1'];
            $unknowntime1 = $_POST['unknowntime1'];
            $name1 = $_POST['name1'];
            $town1 =  $_POST["town1"];
            $countryid1 = $_POST['countryid1'];
            $townselect1 = $_POST['townselect1'];
            if (isset($unknowntime1))
               $unknowntime1 = 'Y';
            else
                $unknowntime1 = 'N';

            if ($townselect1)
            {
                $splittownselect1 = explode("#",$townselect1);
                $town1 = $splittownselect1[0];
                $county1 = $splittownselect1[1];
                $country1 = $splittownselect1[2];
                $latitude1 = $splittownselect1[3];
                $longitude1 = $splittownselect1[4];
                $typetable1 = $splittownselect1[5];
                $zonetable1 = $splittownselect1[6];
            }
            else
            {
                $town1 = $townmatch1[0]->town;
                $county1= $townmatch1[0]->county;
                $country1= $townmatch1[0]->country;
                $latitude1 = $townmatch1[0]->latitude;
                $longitude1 = $townmatch1[0]->longitude;
                $typetable1 = $townmatch1[0]->typetable;
                $zonetable1 = $townmatch1[0]->zonetable;
            }
	    $latitude1 = $latitude1 / 3600;
            $longitude1 = ($longitude1 / 3600) * -1;

            /*
                First Persons details
            */

            $_SESSION['session_dob1'] = $dob1;
            list($day1, $monthname1, $year1, $bc1) = sscanf($dob1, "%d %s %d %s");
            $month1 = array_search($monthname1, $monthnames) + 1;
            $dob1 = sprintf("%02d-%02d-%d %s",$day1,$month1,$year1,$bc1);

            $_SESSION['session_name1'] = $name1;
            $_SESSION['session_time1'] = $time1;
            $_SESSION['session_unknowntime1'] = $unknowntime1;
            $_SESSION['session_town1'] = (string) $town1;
            $_SESSION['session_countryid1'] = $countryid1;

            $processstyle = 'SYNCHRONOUS';
            $reporttype = 'CHART';
            $params = sprintf('<?xml version="1.0" encoding="UTF-8" ?>
            <astroRequest responseFormat="xml" processStyle="%s">
                <reports>
                  <report index="0" type="%s" level="gold" userId="0" />
                </reports>
                <users>
                  <user id="0" firstname="%s" lastname="" dob="%s" time="%s" unknowntime="%s"
                      latitude="%lf" longitude="%lf" country="%s" city="%s" typetable="%d" zonetable="%d"/>
                </users>
                <auth apiKey="%s" />
                <pref lang="EN" houseSystem="%s" />
              </astroRequest>',$processstyle,$reporttype,$name1,$dob1,$time1,$unknowntime1,$latitude1,$longitude1,$country1,addslashes($town1),$typetable1,$zonetable1,$apikey,$housecode);

            $url = sprintf ("%s%s?requestxml=%s",$enginepath,$enginename,urlencode($params));
            $returnxmlstring = sc_loadXML($url);
            $serviceid = $returnxmlstring->service["id"];
            $report = $returnxmlstring->reports[0]->report;
            $chartid1 = $report->charts[0]->chart[0]["path"];
            $keyid1 = str_replace("Chart","Key",$chartid1);
            $lchartid1 = str_replace("Chart","lChart",$chartid1);
            printf ("<html><body>");
            printf ("<table cellpadding=0 cellspacing=0 style=\"margin:0px; padding:0px; margin-left:auto; margin-right:auto; max-width:550px; width:100%%\" class=\"displaytable\">");
            printf ("<tr><td><a href=\"%s\"><img class=\"responsive\" style=\"border-style:none\" src=\"%s\"></img></a></td></tr>",$lchartid1,$lchartid1);

            printf ("</table>");
            if (IMPLEMENTATION == "WORDPRESS" || IMPLEMENTATION == "STANDARD")
            {

                printf ("<table cellpadding=0 cellspacing=0 style=\"max-width:550px; width:100%%; padding:0px; margin-left:auto; margin-right:auto;\" class=\"planetpointsindextable\">");
                printf ("<tr><td style=\"padding:0px; margin:0px; padding-bottom:20px; font-size:115%%\" class=\"planetpointsindextitle\" colspan=7>Planetary Points</td></tr>");
                printf ("<tr>");
                printf ("<td class=\"planetpointsindexcolheader\" style=\"padding:3px; padding-bottom:15px; font-size:100%%\">Planet</td>");

                printf ("<td class=\"planetpointsindexcolheader\" style=\"padding:3px; padding-bottom:15px; font-size:100%%\">Pos</td>");
                printf ("<td class=\"planetpointsindexcolheader\" style=\"padding:3px; padding-bottom:15px; font-size:100%%\">Sign</td>");

                printf ("<td class=\"planetpointsindexcolheader\" style=\"padding:3px; padding-bottom:15px; font-size:100%%\">Ho</td>");
                printf ("<td class=\"planetpointsindexcolheader\" style=\"padding:3px; padding-bottom:15px; font-size:100%%\">Dir</td>");
                printf ("</tr>");
                foreach ($report->charts->chart[0]->planets->planet as $planet)
                {
                    printf ("<tr>");
                    printf ("<td style=\"padding:3px;\"><img style=\"margin:0px;\" src=\"%s/%s.png\"></img> %s</td>", $imagepath, strtolower($planet["name"]), ucwords(strtolower($planet["name"])));

                    printf ("<td style=\"padding:3px;\">%s</td>", ucwords(strtolower($planet["degmin"])));
                    printf ("<td style=\"padding:3px;\"><img style=\"margin:0px;\" src=\"%s/%s.png\"></img> %s</td>", $imagepath, strtolower($planet["signname"]),ucwords(strtolower($planet["signname"])));

                    printf ("<td style=\"padding:3px;\">%s</td>", ucwords(strtolower($planet["house"])));
                    printf ("<td style=\"padding:3px;\">%s</td>", ucwords(strtolower($planet["direction"])));
                    printf ("</tr>");
                }
                printf ("</table>");
                printf ("<div style=\"padding-top:15px\"></div>");
                printf ("<table cellpadding=0 cellspacing=0 style=\"max-width:550px; width:100%%; margin-left:auto; margin-right:auto;\" class=\"planetaspectsindextable\">");
                printf ("<tr><td style=\"padding-bottom:20px; font-size:115%%\" class=\"planetaspectsindextitle\"  colspan=7>Planetary Aspects</td></tr>");
                printf ("<tr>");
                printf ("<td class=\"planetaspectsindexcolheader\" style=\"padding:3px; padding-bottom:15px; font-size:100%%\">Planet</td>");

                printf ("<td class=\"planetaspectsindexcolheader\" style=\"padding:3px; padding-bottom:15px; font-size:100%%\">Aspect</td>");

                printf ("<td class=\"planetaspectsindexcolheader\" style=\"padding:3px; padding-bottom:15px; font-size:100%%\">Planet</td>");

                printf ("</tr>");
                foreach ($report->charts->chart[0]->aspects->aspect as $aspect)
                {
                    printf ("<tr>");
                    printf ("<td style=\"padding:3px;\"><img style=\"margin:0px;\" src=\"%s/%s.png\"></img> %s</td>", $imagepath, strtolower($aspect["planeta"]),ucwords(strtolower($aspect["planeta"])));

                    printf ("<td style=\"padding:3px;\"><img style=\"margin:0px;\" src=\"%s/%s.png\"></img> %s</td>", $imagepath, strtolower($aspect["name"]),ucwords(strtolower($aspect["name"])));

                    printf ("<td style=\"padding:3px;\"><img style=\"margin:0px;\" src=\"%s/%s.png\"</img> %s</td>", $imagepath, strtolower($aspect["planetb"]),ucwords(strtolower($aspect["planetb"])));

                    printf ("</tr>");
                }
                printf ("</table>");

            }
            printf ("<table style=\"max-width:550px; width:100%%\" class=\"buttonstable\">");
            printf ("<tr><td style=\"text-align:center;\"><a href=\"\">Another Person</a>");
            $paramname = 'REPORTENTRY_LINK';
            $url = $starspath . "/getserviceparam.php?apikey=" . urlencode($apikey) . "&paramname=" . urlencode($paramname);
                    $returnxmlstring = sc_loadXML($url);
            if ((int) $returnxmlstring->rowsreturned)
            {
                $reportentry_link = $returnxmlstring->paramvalue[0];
                printf ("<span style=\"padding-right:20px\"></span>");
                printf ("<a href=\"%s\">Full Report</a></td>",$reportentry_link);
            }
            printf ("</tr>");
            printf ("</table>");
            printf ("</body>");
            printf ("</html>");

            $processed = TRUE;
        }
    }
    return $processed;
}
 



if (IMPLEMENTATION == "WORDPRESS")
    add_shortcode( 'starscharts', 'sc_initiate' );

if (IMPLEMENTATION == "STANDARD")
    sc_initiate();

?>
