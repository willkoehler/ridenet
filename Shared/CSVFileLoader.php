<?
//----------------------------------------------------------------------------------
//   ImportCSVToArray()
//
//   This function reads a CSV file and parses it into array of records. Each row in
//   the CSV file becomes a record. The first row defines the field names which
//   become the array keys for indexing into each record.
//
//   UPDATE: This function has been modified to import tab-delimited files as well
//   as comma-delimited. The function automatically detects whether the file is
//   comma-delimited or tab-delimited.
//
//  PARAMETERS:
//     file   - full path and filename to read
//
//  RETURN: array of records parsed from CSV file or false on failure
//-----------------------------------------------------------------------------------
function ImportCSVToArray($filename)
{
    if(($handle = fopen($filename, "r"))!=FALSE)
    {
    // --- Read first row to determine if this file is tab-delimited or comma delimited
        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, "\t") == FALSE) ? "," : "\t";
        fseek($handle, 0);      // rewind back to beginning of the file
    // --- Read first row which contains the field names
        $fieldNames = fgetcsv($handle, 1000, $delimiter);
        $rowCount = 0;
    // --- remove leading/trailing spaces from field names
        for($i=0; $i < count($fieldNames); $i++)
        {
            $fieldNames[$i] = trim($fieldNames[$i]);
        }
    // --- Read each data row
        while($row = fgetcsv($handle, 1000, $delimiter))
        {
        // --- Copy row into final data array. Convert index from numbers to field names
            $fieldIndex = 0;
            foreach($fieldNames as $fieldName)
            {
            // --- Need to check array_key_exists to work around strange excel behavior where sometimes
            // --- the last field is omitted if it is blank.
                $data[$rowCount][$fieldName] = array_key_exists($fieldIndex, $row) ? $row[$fieldIndex] : "";
                $fieldIndex++;
            }
            $rowCount++;
        }
    }
    else
    {
        trigger_error("Failed to open file \"$filename\"", E_USER_ERROR);
        $data = false;
    }
    return $data;
}
?>