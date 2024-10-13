<?
//----------------------------------------------------------------------------------
//   iDateToNumeric()
//
//   This function converts a date to an integer numeric date value. The value
//   returned by this function is the number of months since 0 AD.
//
//  PARAMETERS:
//    Date dtDate    - date to be converted
//
//  RETURN: numeric date value
//-----------------------------------------------------------------------------------
function iDateToNumeric($dtDate)
{
    return($dtDate->format("Y")*12 + $dtDate->format("n") - 1);
} 


//----------------------------------------------------------------------------------
//   dtNumericToDate()
//
//   This function convets a numeric date value back to a standard date.
//
//  PARAMETERS:
//    Date iNumeric  - numeric date to be converted
//
//  RETURN: date value
//-----------------------------------------------------------------------------------
function dtNumericToDate($iNumeric)
{
    return(new DateTime(($iNumeric%12) + 1 . "/1/" . floor($iNumeric/12)));
} 


//----------------------------------------------------------------------------------
//   iGetQuarterFromMonth()
//
//   This function returns the quarter (zero-based) that a given month belongs to
//
//  PARAMETERS:
//    Int iMonth    - Month
//
//  RETURN: The quarter containing iMonth
//-----------------------------------------------------------------------------------
function iGetQuarterFromMonth($iMonth)
{
    switch ($iMonth)
    {
        case 1:
        case 2:
        case 3:
            return(0);
            break;
        case 4:
        case 5:
        case 6:
            return(1);
            break;
        case 7:
        case 8:
        case 9:
            return(2);
            break;
        case 10:
        case 11:
        case 12:
            return(3);
            break;
    } 
} 


//----------------------------------------------------------------------------------
//  PreviousQuarter()
//
//  This function returns the quarter/year of the quarter previous to given
//  quarter and year
//
//  PARAMETERS:
//    quarter   - reference quarter (zero-based)
//    year      - reference year
//    pquarter  - quarter value of previous quarter will be placed here (zero-based)
//    pyear     - year value of previous quarter will be placed here
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function PreviousQuarter($quarter, $year, &$pquarter, &$pyear)
{
    $pquarter = ($quarter==0) ? 3 : $quarter-1;
    $pyear = ($quarter==0) ? $year-1 : $year;
}


//----------------------------------------------------------------------------------
//   FirstOfWeek()
//
//   This function takes a date and returns the date of the first day of the week
//   (Sunday) containing the date.
//
//   PARAMETERS:
//     $dtDate    - date to convert
//
//   RETURN: (DateTime) date of the first day of the week
//-----------------------------------------------------------------------------------
function FirstOfWeek($dtDate)
{
    return(AddDays($dtDate, -1 * $dtDate->format("w")));
}


//----------------------------------------------------------------------------------
//   MondayOfWeek()
//
//   This function takes a date and returns the date of Monday in the week
//   containing the date.
//
//   PARAMETERS:
//     $dtDate    - date to convert
//
//   RETURN: (DateTime) date of Monday in the specified week
//-----------------------------------------------------------------------------------
function MondayOfWeek($dtDate)
{
    return(AddDays($dtDate, -1 * ($dtDate->format("N")-1) ));
}


//----------------------------------------------------------------------------------
//   FirstOfMonth()
//
//   This function takes a date and returns the date of the first day of the month.
//   For example 11/12/2008 will be converted to 11/1/2008.
//
//   PARAMETERS:
//     $dtDate    - date to convert
//
//   RETURN: (DateTime) date of the first day of the month
//-----------------------------------------------------------------------------------
function FirstOfMonth($dtDate)
{
    return(new DateTime($dtDate->format("n/1/Y")));
}


//----------------------------------------------------------------------------------
//   LastOfMonth()
//
//   This function takes a date and returns the date of the last day of the month.
//   For example 11/12/2008 will be converted to 11/30/2008.
//
//   PARAMETERS:
//     $dtDate    - date to convert
//
//   RETURN: (DateTime) date of the last day of the month
//-----------------------------------------------------------------------------------
function LastOfMonth($dtDate)
{
    $lastDay = iDaysInMonth($dtDate->format("n"), $dtDate->format("Y"));
    return(new DateTime($dtDate->format("n/$lastDay/Y")));
}


//----------------------------------------------------------------------------------
//   FirstOfYear()
//
//   This function takes a date and returns the date of the first day of the year.
//   For example 11/12/2008 will be converted to 1/1/2008.
//
//   PARAMETERS:
//     $dtDate    - date to convert
//
//   RETURN: (DateTime) date of the first day of the year
//-----------------------------------------------------------------------------------
function FirstOfYear($dtDate)
{
    return(new DateTime($dtDate->format("1/1/Y")));
}


//----------------------------------------------------------------------------------
//   AddDays()
//
//   This function adds a specificed number of days to a given date
//
//   PARAMETERS:
//     $date    - starting date
//     $count   - number of days to add (may be negative)
//
//   RETURN: (DateTime) date + count days
//-----------------------------------------------------------------------------------
function AddDays($date,$count)
{
    return(date_create($date->format("n/j/Y") . " +$count days"));
}


//----------------------------------------------------------------------------------
//   AddMonths()
//
//   This function adds the given number of months to $dtDate. If the resulting
//   date is beyond the end of the resulting month (ex: 1/31/2000 + 1 month = 2/31/2001
//   which is invalid), the resulting date is rolled back to the last day of the
//   month (ex: 1/31/2000 + 1 month = 2/28/2001)
//
//   PARAMETERS:
//     $dtDate    - starting date
//     $months    - number of months to add
//
//   RETURN: (DateTime) $dtDate + $months
//-----------------------------------------------------------------------------------
function AddMonths($dtDate, $months)
{
    $numericDate = iDateToNumeric($dtDate) + $months;    
    $month = ($numericDate % 12) + 1;
    $year = (floor($numericDate/12));
    $day = min($dtDate->format("j"), iDaysInMonth($month, $year));
    return(new DateTime("$month/$day/$year"));
}


//----------------------------------------------------------------------------------
//   iDaysInMonth()
//
//   This function determines the number of days in a given month, year
//
//  PARAMETERS:
//    $month    - The month
//    $year     - The year
//
//  RETURN: Number of days in given month/year
//-----------------------------------------------------------------------------------
Function iDaysInMonth($month, $year)
{
    switch($month) {
        case 4:
        case 6:
        case 9:
        case 11:
            return(30);
            break;
        case 2:
        // --- February - need to check for leapyear
            if(IsLeapYear($year))
            {
                return(29);
            }
            else
            {
                return(28);
            }
            break;
        default:
            return(31);
            break;
    }
}


//----------------------------------------------------------------------------------
//   IsLeapYear()
//
//   This function determines if the specified year is a leap year
//
//  PARAMETERS:
//    $year     - the year    
//
//  RETURN: True of $year is a leap year
//-----------------------------------------------------------------------------------
Function IsLeapYear($year)
{
    if ($year % 4 != 0) 
    { 
        return false;
    } 
    else 
    { 
        if ($year % 100 != 0) 
        { 
            return true;
        } 
        else 
        { 
            if ($year % 400 != 0) 
            { 
                return false;
            } 
            else 
            { 
                return true;
            } 
        } 
    }
}
?>