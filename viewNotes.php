<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $RCSfile: common.php,v $
  Language:  PHP
  Date:      $Date$
  Version:   $Revision$

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
$noforcelogin = 1;
include("config.php");
include('login.php');
include("common.php");

@$buildid = $_GET["buildid"];
@$date = $_GET["date"];

include("config.php");
$db = mysql_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
mysql_select_db("$CDASH_DB_NAME",$db);
  
$build_array = mysql_fetch_array(mysql_query("SELECT projectid FROM build WHERE id='$buildid'"));  
$projectid = $build_array["projectid"];
checkUserPolicy(@$_SESSION['cdash']['loginid'],$projectid);

if(!isset($date) || strlen($date)==0)
    { 
    $currenttime = time();
    }
  else
    {
    $currenttime = mktime("23","59","0",substr($date,4,2),substr($date,6,2),substr($date,0,4));
    }
    
$project = mysql_query("SELECT * FROM project WHERE id='$projectid'");
if(mysql_num_rows($project)>0)
  {
  $project_array = mysql_fetch_array($project);  
  $projectname = $project_array["name"];  
  }

  $previousdate = date("Ymd",$currenttime-24*3600); 
  $nextdate = date("Ymd",$currenttime+24*3600);

$xml = '<?xml version="1.0"?><cdash>';
$xml .= "<title>CDash : ".$projectname."</title>";
$xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
$xml .= get_cdash_dashboard_xml_($projectid,$date);
  
  // Build
  $xml .= "<build>";
  $build = mysql_query("SELECT * FROM build WHERE id='$buildid'");
  $build_array = mysql_fetch_array($build); 
  $siteid = $build_array["siteid"];
  $site_array = mysql_fetch_array(mysql_query("SELECT name FROM site WHERE id='$siteid'"));
  $xml .= add_XML_value("site",$site_array["name"]);
  $xml .= add_XML_value("buildname",$build_array["name"]);
  $xml .= add_XML_value("buildid",$build_array["id"]);
  $xml .= "</build>";
  
  $xml .= "<note>";
  
  $note = mysql_query("SELECT * FROM note WHERE buildid='$buildid'");
  $note_array = mysql_fetch_array($note);
  
  $xml .= add_XML_value("name",$note_array["name"]);
  $xml .= add_XML_value("text",$note_array["text"]);
  $xml .= add_XML_value("time",$note_array["time"]);
  
  $xml .= "</note>";
  $xml .= "</cdash>";

// Now doing the xslt transition
generate_XSLT($xml,"viewNotes");
?>
