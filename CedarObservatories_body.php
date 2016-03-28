<?php
class CedarObservatories extends SpecialPage
{
    var $dbuser, $dbpwd ;

    function CedarObservatories() {
	SpecialPage::SpecialPage("CedarObservatories");
	#wfLoadExtensionMessages( 'CedarObservatories' ) ;

	$this->dbuser = "madrigal" ;
	$this->dbpwd = "shrot-kash-iv-po" ;
    }
    
    function execute( $par ) {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$this->setHeaders();

	CedarNote::addScripts() ;

	$sort_param = $wgRequest->getText('sort');
	$action = $wgRequest->getText('action');
	$obs = $wgRequest->getText('obs');
	$sort_by = "ALPHA_CODE" ;
	if( $action == "detail" )
	{
	    $this->observatoryDetail( $obs ) ;
	    return ;
	}
	else if( $action == "create" )
	{
	    $this->observatoryEdit( $obs, 1, $action ) ;
	    return ;
	}
	else if( $action == "edit" )
	{
	    $this->observatoryEdit( $obs, 0, $action ) ;
	    return ;
	}
	else if( $action == "delete" )
	{
	    $this->observatoryDelete( $obs ) ;
	    return ;
	}
	else if( $action == "update" )
	{
	    $this->observatoryUpdate( $obs ) ;
	    return ;
	}
	else if( $action == "newnote" )
	{
	    $is_successful = CedarNote::newNote( "tbl_observatory", "ID", $obs ) ;
	    if( $is_successful )
	    {
		$this->observatoryDetail( $obs ) ;
	    }
	    return ;
	}
	else if( $action == "delete_note" )
	{
	    $is_successful = CedarNote::deleteNote( "CedarObservatories", "obs", $obs, "tbl_observatory", "ID" ) ;
	    if( $is_successful )
	    {
		$this->observatoryDetail( $obs ) ;
	    }
	    return ;
	}
	else if( $action == "edit_note" )
	{
	    $is_successful = CedarNote::editNoteForm( "CedarObservatories", "obs", $obs ) ;
	    if( !$is_successful )
	    {
		$this->observatoryDetail( $obs ) ;
	    }
	    return ;
	}
	else if( $action == "update_note" )
	{
	    $is_successful = CedarNote::updateNote( ) ;
	    if( $is_successful )
	    {
		$this->observatoryDetail( $obs ) ;
	    }
	    return ;
	}
	else if( $action == "sort" )
	{
	    if( $sort_param == "name" )
	    {
		$sort_by = "LONG_NAME" ;
	    }
	}

	$this->displayObservatories( $sort_by ) ;
    }

    private function displayObservatories( $sort_by )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer ;

	$wgOut->addHTML( "    <TABLE ALIGN=\"CENTER\" BORDER=\"0\" WIDTH=\"800\" CELLPADDING=\"0\" CELLSPACING=\"0\">" ) ;
	$wgOut->addHTML( "	<TR>" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"100%\" ALIGN=\"LEFT\">" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarObservatories?action=create'>Create a New Observatory</A></SPAN>" ) ;
	$wgOut->addHTML( "	    </TD>" ) ;
	$wgOut->addHTML( "	</TR>" ) ;
	$wgOut->addHTML( "    </TABLE>" ) ;
	$wgOut->addHTML( "    <BR/>" ) ;

	// Get the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database" ) ;
	}
	else
	{
	    // sort_by variable is created within this script, no need
	    // to clean it.
	    $res = $dbh->query( "select ID, ALPHA_CODE, LONG_NAME from tbl_observatory ORDER BY $sort_by" ) ;
	    if( !$res )
	    {
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />" ) ;
	    }
	    else
	    {
		$wgOut->addHTML( "    <TABLE ALIGN=\"CENTER\" BORDER=\"1\" WIDTH=\"800\" CELLPADDING=\"0\" CELLSPACING=\"0\">" ) ;
		$wgOut->addHTML( "	<TR style=\"background-color:gainsboro;\">\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "	        &nbsp;" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"20%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarObservatories?action=sort&sort=code'>Code</A></SPAN>" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"70%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarObservatories?action=sort&sort=name'>Name</A></SPAN>" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	</TR>" ) ;
		$rowcolor="white" ;
		while( ( $obj = $dbh->fetchObject( $res ) ) )
		{
		    $obs = intval( $obj->ID ) ;
		    $code = $obj->ALPHA_CODE ;
		    $name = $obj->LONG_NAME ;
		    if( $obs == 0 )
			continue ;
		    $wgOut->addHTML( "	<TR style=\"background-color:$rowcolor;\">\n" ) ;
		    if( $rowcolor == "white" ) $rowcolor = "gainsboro" ;
		    else $rowcolor = "white" ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">" ) ;
		    $wgOut->addHTML( "		<A HREF='$wgServer/wiki/index.php/Special:CedarObservatories?action=detail&obs=$obs'><IMG SRC='$wgServer/wiki/icons/detail.png' ALT='detail' TITLE='Detail'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarObservatories?action=edit&obs=$obs'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarObservatories?action=delete&obs=$obs'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"20%\" ALIGN=\"CENTER\">" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$code</SPAN>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"70%\" ALIGN=\"CENTER\">" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$name</SPAN>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	</TR>" ) ;
		}
		$wgOut->addHTML( "</TABLE>" ) ;
	    }
	    $dbh->close() ;
	}
    }

    private function observatoryDetail( $obs )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	$wgOut->addHTML( "<SPAN STYLE=\"font-size:12pt;\">Return to the <A HREF=\"$wgServer/wiki/index.php/Special:CedarObservatories\">Observatory List</A></SPAN><BR /><BR />\n" ) ;

	// Get the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database\n" ) ;
	    return ;
	}

	$obs = $dbh->strencode( $obs ) ;
	$res = $dbh->query( "select ALPHA_CODE, LONG_NAME, DUTY_CYCLE, OPERATIONAL_HOURS, DESCRIPTION, REF_URL, NOTE_ID from tbl_observatory WHERE ID = $obs" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}
	else
	{
	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$alpha_code = $obj->ALPHA_CODE ;
		$long_name = $obj->LONG_NAME ;
		$duty_cycle = $obj->DUTY_CYCLE ;
		$operational_hours = $obj->OPERATIONAL_HOURS ;
		$description = $obj->DESCRIPTION ;
		$ref_url = $obj->REF_URL ;
		$note_id = intval( $obj->NOTE_ID ) ;

		$wgOut->addHTML( "    <TABLE ALIGN=\"LEFT\" BORDER=\"1\" WIDTH=\"800\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
		$wgOut->addHTML( "        <TR>\n" ) ;
		$wgOut->addHTML( "            <TD ALIGN='CENTER' HEIGHT='30px' BGCOLOR='Aqua'>\n" ) ;
		if( $allowed )
		{
		    $wgOut->addHTML( "                <A HREF='$wgServer/wiki/index.php/Special:CedarObservatories?action=edit&obs=$obs'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarObservatories?action=delete&obs=$obs'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>&nbsp;&nbsp;\n" ) ;
		}
		$wgOut->addHTML( "                <SPAN STYLE='font-weight:bold;font-size:14pt;'>$alpha_code - $long_name</SPAN>\n" ) ;
		$wgOut->addHTML( "            </TD>\n" ) ;
		$wgOut->addHTML( "        </TR>\n" ) ;
		$wgOut->addHTML( "        <TR>\n" ) ;
		$wgOut->addHTML( "            <TD BGCOLOR='White'>\n" ) ;
		$wgOut->addHTML( "                <DIV STYLE='line-height:2.0;font-weight:normal;font-size:10pt;'>\n" ) ;
		$instrument_info = $this->observatoryInstruments( $obs, $dbh) ;
		$wgOut->addHTML( "                    Instruments: $instrument_info<BR />\n" ) ;
		$wgOut->addHTML( "                    Duty Cycle: $duty_cycle<BR />\n" ) ;
		$wgOut->addHTML( "                    Operational Hours: $operational_hours<BR />\n" ) ;
		$wgOut->addHTML( "                    Reference: $ref_url<BR />\n" ) ;
		$wgOut->addHTML( "                    Description:<BR /><SPAN STYLE=\"line-height:1.0;\">$description</SPAN><BR /><BR />\n" ) ;
		$wgOut->addHTML( "                </DIV>\n" ) ;
		$wgOut->addHTML( "            </TD>\n" ) ;
		$wgOut->addHTML( "        </TR>\n" ) ;
		$wgOut->addHTML( "        <TR>\n" ) ;
		$wgOut->addHTML( "            <TD BGCOLOR='White'>\n" ) ;
		$wgOut->addHTML( "                <DIV STYLE='font-weight:normal;font-size:10pt;'>\n" ) ;
		$wgOut->addHTML( "                    Notes:<BR />\n" ) ;
		$last_note_id = CedarNote::displayNote( $note_id, "CedarObservatories", "obs", $obs, 0, $dbh ) ;
		CedarNote::newNoteForm( $last_note_id, "CedarObservatories", "obs", $obs ) ;
		$wgOut->addHTML( "                </DIV>\n" ) ;
		$wgOut->addHTML( "            </TD>\n" ) ;
		$wgOut->addHTML( "        </TR>\n" ) ;
		$wgOut->addHTML( "    </TABLE>\n" ) ;
	    }
	    else
	    {
		$wgOut->addHTML( "There is no observatory with the given id: $obs<BR />\n" ) ;
	    }
	}

	$dbh->close() ;
    }

    private function observatoryInstruments( $obs, $dbh )
    {
	global $wgDBserver, $wgServer ;

	$instrument_info = "" ;

	// obs variable is already cleaned
	$res = $dbh->query( "select KINST, PREFIX, INST_NAME from tbl_instrument WHERE OBSERVATORY = $obs" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return $instrument_info ;
	}
	$first = true ;
	while( ( $obj = $dbh->fetchObject( $res ) ) )
	{
	    $kinst = intval( $obj->KINST ) ;
	    $prefix = $obj->PREFIX ;
	    $inst_name = $obj->INST_NAME ;
	    if( !$first )
		$instrument_info .= ", " ;
	    $instrument_info .= "<A HREF=\"$wgServer/wiki/index.php/Special:Cedar_Instruments?action=detail&kinst=$kinst\">$kinst - $prefix - $inst_name</A>" ;
	    $first = false ;
	}

	return $instrument_info ;
    }

    private function observatoryEdit( $obs, $isnew, $action )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit observatory information</SPAN><BR />\n" ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$obs = $dbh->strencode( $obs ) ;
	$alpha_code = "" ;
	$long_name = "" ;
	$duty_cycle = "" ;
	$operational_hours = "" ;
	$description = "" ;
	$ref_url = "" ;
	if( $isnew == 0 && $action == "edit" )
	{
	    $res = $dbh->query( "select ALPHA_CODE, LONG_NAME, DUTY_CYCLE, OPERATIONAL_HOURS, DESCRIPTION, REF_URL from tbl_observatory WHERE ID = $obs" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    if( $res->numRows() != 1 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "Unable to edit the observatory $obs, does not exist<BR />\n" ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( !$obj )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $alpha_code = $obj->ALPHA_CODE  ;
	    $long_name = $obj->LONG_NAME  ;
	    $duty_cycle = $obj->DUTY_CYCLE  ;
	    $operational_hours = $obj->OPERATIONAL_HOURS  ;
	    $description = $obj->DESCRIPTION  ;
	    $ref_url = $obj->REF_URL  ;
	}
	else if( $action == "update" )
	{
	    $alpha_code = $dbh->strnecode( $wgRequest->getText( 'alpha_code' ) ) ;
	    $long_name = $dbh->strnecode( $wgRequest->getText( 'long_name' ) ) ;
	    $duty_cycle = $dbh->strnecode( $wgRequest->getText( 'duty_cycle' ) ) ;
	    $operational_hours = $dbh->strnecode( $wgRequest->getText( 'operational_hours' ) ) ;
	    $description = $dbh->strnecode( $wgRequest->getText( 'description' ) ) ;
	    $ref_url = $dbh->strnecode( $wgRequest->getText( 'ref_url' ) ) ;
	}

	// now display the information in the form
	$wgOut->addHTML( "<FORM name=\"observatory_edit\" action=\"$wgServer/wiki/index.php/Special:CedarObservatories\" method=\"POST\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"action\" value=\"update\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"obs\" value=\"$obs\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"isnew\" value=\"$isnew\">\n" ) ;
	$wgOut->addHTML( "  <TABLE WIDTH=\"800\" CELLPADDING=\"2\" CELLSPACING=\"0\" BORDER=\"0\">\n" ) ;

	// observatory alpha_code text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Alpha Code:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"alpha_code\" size=\"30\" value=\"$alpha_code\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// observatory long_name text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Long Name:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"long_name\" size=\"50\" value=\"$long_name\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// observatory duty_cycle text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Duty Cycle:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"duty_cycle\" size=\"30\" value=\"$duty_cycle\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// observatory operational_hours text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Operational Hours:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"operational_hours\" size=\"30\" value=\"$operational_hours\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// observatory ref_url text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Reference URL:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"ref_url\" size=\"30\" value=\"$ref_url\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// description text area
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Description:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <TEXTAREA STYLE=\"width:75%;border-color:black;border-style:solid;border-width:thin;\" ID=\"description\" NAME=\"description\" rows=\"10\" cols=\"20\">$description</TEXTAREA><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// submit, cancel and reset buttons
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Submit\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;&nbsp;<INPUT TYPE=\"RESET\" VALUE=\"Reset\">\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;
	$wgOut->addHTML( "  </TABLE>\n" ) ;

	$wgOut->addHTML( "</FORM>\n" ) ;

	$dbh->close() ;
    }

    private function observatoryUpdate( $obs )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit observatory information</SPAN><BR />\n" ) ;
	    return ;
	}

	// if the cancel button was pressed then go to observatory detail
	$submit = $wgRequest->getText( 'submit' ) ;
	if( $submit == "Cancel" )
	{
	    if( $obs == 0 )
		$this->displayObservatories( "ID" ) ;
	    else
		$this->observatoryDetail( $obs ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$isnew = $wgRequest->getInt( 'isnew' ) ;
	$alpha_code = $dbh->strencode( $wgRequest->getText( 'alpha_code' ) ) ;
	$long_name = $dbh->strencode( $wgRequest->getText( 'long_name' ) ) ;
	$duty_cycle = $dbh->strencode( $wgRequest->getText( 'duty_cycle' ) ) ;
	$operational_hours = $dbh->strencode( $wgRequest->getText( 'operational_hours' ) ) ;
	$ref_url = $dbh->strencode( $wgRequest->getText( 'ref_url' ) ) ;
	$description = $dbh->strencode( $wgRequest->getText( 'description' ) ) ;

	// if isnew then insert the new instrument
	// if not new, kinst != 0, then update kinst (remember to use new_kinst)
	if( $isnew == 1 )
	{
	    $insert_success = $dbh->insert( 'tbl_observatory',
		    array(
			    'ALPHA_CODE' => $alpha_code,
			    'LONG_NAME' => $long_name,
			    'DUTY_CYCLE' => $duty_cycle,
			    'OPERATIONAL_HOURS' => $operational_hours,
			    'REF_URL' => $ref_url,
			    'DESCRIPTION' => $description
		    ),
		    __METHOD__
	    ) ;

	    if( $insert_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to insert new observatory $alpha_code<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obs = $dbh->insertId() ;
	}
	else if( $isnew == 0 )
	{
	    $update_success = $dbh->update( 'tbl_observatory',
		    array(
			    'ALPHA_CODE' => $alpha_code,
			    'LONG_NAME' => $long_name,
			    'DUTY_CYCLE' => $duty_cycle,
			    'OPERATIONAL_HOURS' => $operational_hours,
			    'REF_URL' => $ref_url,
			    'DESCRIPTION' => $description
		    ),
		    array(
			    'ID' => $obs
		    ),
		    __METHOD__
	    ) ;

	    if( $update_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to update observatory $alpha_code<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}

	$dbh->close() ;

	$this->observatoryDetail( $obs ) ;
    }

    private function observatoryDelete( $obs )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to delete observatories</SPAN><BR />\n" ) ;
	    return ;
	}

	// grab the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$obs = $dbh->strencode( $obs ) ;

	// first make sure the observatory exists
	$res = $dbh->query( "select ID from tbl_observatory WHERE ID = $obs" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	if( $res->numRows() != 1 )
	{
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to delete the Observatory, does not exist<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	// make sure there are no instruments referencing this observatory
	$res = $dbh->query( "select KINST, PREFIX, INST_NAME from tbl_instrument WHERE OBSERVATORY = $obs" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	if( $res->numRows() != 0 )
	{
	    $wgOut->addHTML( "Unable to delete the Observatory, still referenced by instruments " ) ;
	    $first = true ;
	    while( ( $obj = $dbh->fetchObject( $res ) ) )
	    {
		$kinst = intval( $obj->KINST ) ;
		$prefix = $obj->PREFIX ;
		$inst_name = $obj->INST_NAME ;
		if( !$first )
		    $wgOut->addHTML( ", " ) ;
		$wgOut->addHTML( "<A HREF=\"$wgServer/wiki/index.php/Special:Cedar_Instruments?action=detail&kinst=$kinst\">$kinst - $prefix - $inst_name</A>" ) ;
		$first = false ;
	    }
	    $dbh->close() ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}


	// ask for confirmation
	$confirm = $wgRequest->getText( 'confirm' ) ;

	if( !$confirm )
	{
	    $dbh->close() ;
	    $wgOut->addHTML( "Are you sure you want to delete the observatory with id$obs?\n" ) ;
	    $wgOut->addHTML( "(<A HREF=\"$wgServer/wiki/index.php/Special:CedarObservatories?action=delete&confirm=yes&obs=$obs\">Yes</A>" ) ;
	    $wgOut->addHTML( " | <A HREF=\"$wgServer/wiki/index.php/Special:CedarObservatories?action=delete&confirm=no&obs=$obs\">No</A>)" ) ;
	    return ;
	}

	if( $confirm && $confirm == "no" )
	{
	    $dbh->close() ;
	    $this->observatoryDetail( $obs ) ;
	    return ;
	}

	// delete the observatory and all of its notes
	if( $confirm && $confirm == "yes" )
	{
	    // need to delete all of the associated notes as well
	    $res = $dbh->query( "select NOTE_ID from tbl_observatory WHERE ID = $obs" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$note_id = intval( $obj->NOTE_ID ) ;
		if( $note_id != 0 )
		{
		    CedarNote::deleteNotes( $note_id, $dbh ) ;
		}
	    }

	    // delete the observatory
	    $delete_success = $dbh->delete( 'tbl_observatory', array( 'ID' => $obs ) ) ;

	    if( $delete_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to delete observatory $obs:<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}

	$dbh->close() ;

	$this->displayObservatories( "ID" ) ;

	return ;
    }
}

?>
