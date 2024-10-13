<?

class DBConnection extends mysqli
{

    //----------------------------------------------------------------------------------
    //  DBConnection()
    //
    //   Constructor. Opens the MySQL database connection and checks for errors
    //
    //   NOTE: This connection will automatically be closed when the script that
    //   created it ends
    //
    //  PARAMETERS:
    //    host      - IP address / name of database server
    //    user      - MySQL database username
    //    pw        - MySQL database password
    //    dbname    - name of database
    //
    //  RETURN: none
    //-----------------------------------------------------------------------------------
    function __construct($host, $user, $pw, $dbname)
    {
        // call base class constructor
        parent::__construct($host, $user, $pw, $dbname);
        // check for errors
        if(mysqli_connect_error())
        {
            exit("<br>Error connecting to data.");
        }
    }


    //----------------------------------------------------------------------------------
    //  query()
    //
    //  This function is a wrapper around the mysqli query() function. It calls the mysqli
    //  query function and then checks for and reports mysql errors
    //
    //  The MySQL error message will be sent to the user's browser and/or PHP error log
    //  depending on settings in php.ini. For security reasons, we should *NEVER* send
    //  MySQL error messages to the user's browser on a production server. By using this
    //  function to check and report all MySQL errors we have a consistent interface that
    //  allows us to send MySQL messages to the browser on the development servers and
    //  send MySQL messages to the PHP error log on production servers
    //
    //  PARAMETERS:
    //    sql         - SQL statement
    //    resultmode  - needed to match signature of mysqli::query
    //
    //  RETURN: TRUE on success or FALSE on failure. For SELECT, SHOW, DESCRIBE or
    //          EXPLAIN mysqli_query() will return a mysqli result object.
    //-----------------------------------------------------------------------------------
    function query($sql, $resultmode = NULL)
    {
        $result = parent::query($sql, $resultmode);    // call base class query function
        if($this->errno)
        {
            error_log("SQL Error [" . $this->errno . "] " . $this->error);
            error_log("Full SQL: $sql");
            $e = new \Exception; // trick to get stacktrace https://stackoverflow.com/a/7039409/935514
            error_log("Stack trace:\n" . $e->getTraceAsString());
        }
        return($result);
    }


    //----------------------------------------------------------------------------------
    //  GetRecords()
    //
    //  Execute a query and return the results as an array of associative array
    //
    //  PARAMETERS:
    //    sql   - SQL statement
    //
    //  RETURN: Results of the query as an associative array
    //-----------------------------------------------------------------------------------
    function GetRecords($sql)
    {
        $records = Array();
        $rs = $this->query($sql);
        while($record = $rs->fetch_assoc())
        {
          $records[] = $record;
        }
        $rs->free();
        return($records);
    }


    //----------------------------------------------------------------------------------
    //  GetRecord()
    //
    //  Execute a query and return the first record as an associative array
    //
    //  PARAMETERS:
    //    sql   - SQL statement
    //
    //  RETURN: Results of the query as an associative array
    //-----------------------------------------------------------------------------------
    function GetRecord($sql)
    {
        $records = $this->GetRecords($sql);
        return($records[0]);
    }


    //----------------------------------------------------------------------------------
    //  DBLookup()
    //
    //  Looks up a value in a table.
    //
    //  PARAMETERS:
    //    field   - field to lookup
    //    table   - table to lookup in
    //    where   - where clause (without the WHERE) to filter results
    //    default - value to return if matching row is not found
    //
    //  RETURN: value of "field" in row matching "where" or value of default parameter if
    //          no row matched
    //-----------------------------------------------------------------------------------
    function DBLookup($field, $table, $where, $default="")
    {
        $rs = $this->query("SELECT $field FROM $table WHERE $where");
        if(($record=$rs->fetch_array())==false)
        {
            return($default);
        }
        else
        {
            $rs->free();
            return($record[$field]);
        }
    }


    //----------------------------------------------------------------------------------
    //  DBCount()
    //
    //  Count records in a table.
    //
    //  PARAMETERS:
    //    table   - table to count records
    //    where   - where clause (without the WHERE) to select which records to count
    //
    //  RETURN: count of records
    //-----------------------------------------------------------------------------------
    function DBCount($table, $where)
    {
        $rs = $this->query("SELECT COUNT(*) AS Count FROM $table WHERE $where");
        if(($record=$rs->fetch_array())==false)
        {
            return(0);
        }
        else
        {
            $rs->free();
            return($record['Count']);
        }
    }



    //----------------------------------------------------------------------------------
    //  DumpToJSArray()
    //
    //  This is used to build javascript arrays used for Ext.ComboBox lookup. The
    //  columns of the recordset will be put into a n x m javascript array
    //  The first column should be the key, the second column should be the string which
    //  will appear in the combo box. Any additional columns are optional. The output
    //  array looks like this:
    //
    //   [[1, 'Apples'],
    //    [2, 'Oranges'],
    //    [3, 'Peaches'],
    //    [4, 'Plums']];
    //
    //  PARAMETERS:
    //    String sql  - SQL statement that will produce an m-column dataset
    //
    //  RETURN: none (output will be echoed directly to HTML)
    //-----------------------------------------------------------------------------------
    function DumpToJSArray($sql)
    {
        $rs = $this->query($sql);
        $result = "[";
    // --- Loop through rows
        while($row = $rs->fetch_row())
        {
        // --- Loop through columns in the row and add each column
            $result .= "[";
            foreach($row as $col)
            {
            // --- Add column value. Surround strings with quotes, leave numbers as is
                if(is_numeric($col))
                {
                    $result .= "$col, ";
                }
                else
                {
                    $col = str_replace("\"", "'", $col);                // replace double quotes with single quotes
                    $col = preg_replace("/(\r\n|[\r\n])/", " ", $col);  // change linebreaks to spaces
                    $result .= "\"$col\", ";                            // surround string with double quotes
                }
            }
            $result = substr($result, 0, -2);   // trim last ", " from string
            $result .= "],\n";
        }
        if(strlen($result) > 1)
        {
            $result = substr($result, 0, -2);   // trim last ",\n" from string
        }
        $result .= "];\n";
        $rs->free();
        Echo $result;
    }


    //----------------------------------------------------------------------------------
    //  RecordActivity()
    //
    //  This function records activity into the activity table.
    //
    //  PARAMETERS:
    //    description   - description of activity (up to 150 characters)
    //    referenceID   - optional reference ID (ex: ID of patient record that was modified)
    //    siteID        - ID of Hospital, Plant, etc logged in.
    //
    //  RETURN: none
    //-----------------------------------------------------------------------------------
    function RecordActivity($description, $referenceID = 0, $siteID = 0)
    {
        if(strlen($description) > 150)
        {
        // limit description to 150 characters. The description field in the activity table is a varchar 150
        // If description is longer than 150 characters MySQL will throw a 'Data too long for column' error.
            $description = substr($description, 0, 150);
        }
        $loginID = isset($_SESSION['loginTableID']) ? $_SESSION['loginTableID'] : 0;
        $this->query("INSERT INTO activity (Date, LoginTableID, SiteID, Description, ReferenceID, IPAddress) VALUES (
                      NOW(), $loginID, $siteID, \"$description\", $referenceID, \"" . $_SERVER['REMOTE_ADDR'] . "\")");
    }


    //----------------------------------------------------------------------------------
    //  RecordActivityIfOK()
    //
    //  This function records activity into the activity table if there are no database
    //  errors (i.e. the last database operation did not have errors)
    //
    //  PARAMETERS:
    //    description   - description of activity (up to 150 characters)
    //    referenceID   - optional reference ID (ex: ID of patient record that was modified)
    //    siteID        - ID of Hospital, Plant, etc logged in.
    //
    //  RETURN: none
    //-----------------------------------------------------------------------------------
    function RecordActivityIfOK($description, $referenceID = 0, $siteID = 0)
    {
        if($this->errno==0)
        {
            $this->RecordActivity($description, $referenceID, $siteID);
        }
    }

}


//----------------------------------------------------------------------------------
//   PrepareDateForSQL()
//
//   This function takes a date and prepares it to be used in a SQL statement.
//   Date is reformatted as YYYY-MM-DD and surrounded in double-quotes
//   If the date is empty or invalid then NULL is returned.
//
//  PARAMETERS:
//     date   - date value to be fixed up for use in a SQL statement
//
//  RETURN: fixed up date value
//-----------------------------------------------------------------------------------
function PrepareDateForSQL($date)
{
    if(strtoupper($date)=="NULL" || $date=="")
    {
        return("NULL");
    }
    else
    {
        if(strtotime($date)==false)
        {
            return("NULL");
        }
        else
        {
            return(chr(34) . date("Y-m-d", strtotime($date)) . chr(34));
        }
    }
}


//----------------------------------------------------------------------------------
//   PrepareStringForSQL()
//
//   This function takes a string and prepares it to be used in a SQL statement.
//   Reserved characters are escaped with backslashes and the string is surrounded
//   in double-quotes. If the string is empty then NULL is returned.
//
//   If a database connection is provided in oDB, the string is escaped using a
//   database library function that's more thorough than addslashes(). This is
//   needed to fully protect against SQL injection attacks.
//
//  PARAMETERS:
//     str   - string value to be fixed up for use in a SQL statement
//     oDB   - optional database connection.
//
//  RETURN: fixed up string value
//-----------------------------------------------------------------------------------
function PrepareStringForSQL($str, $oDB=null)
{
    if(strtoupper($str)=="NULL" || $str=="")
    {
        return("NULL");
    }
    else
    {
    // --- add backslash prefix to all characters such as single quote, double quote,
    // --- and backslash that need to be escaped in mysql queries
        $str = ($oDB) ? $oDB->escape_string($str) : addslashes($str);
    // --- delimit string with double quotes. (FYI Double quotes don't work for MS Access)
        return(chr(34) . trim($str) . chr(34));
    }
}
?>