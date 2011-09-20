<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <script language=javascript>
    function updateSavings()
    {
        // get values from form
        var employees = toInt(document.getElementById('employees').value);
        var participation = toInt(document.getElementById('participation').value);
        var salary = toInt(document.getElementById('salary').value);
        var healthCare = toInt(document.getElementById('health-care').value);
        var internal = toInt(document.getElementById('internal').value);
        var external  = toInt(document.getElementById('external').value);
        var parkingType = toInt(document.getElementById('park-type').value);
        var parkCost = toInt(document.getElementById('park-cost').value);
        var paricipatingEmployees = employees * (participation/100.0);

        // calculate individual savings metrics
        var healthSavings = .16 * healthCare * paricipatingEmployees;
        var productivitySavings = .10 * salary * paricipatingEmployees;
        var taxSavings = 20 * paricipatingEmployees;
        var turnoverSavings = employees * 0.01 * salary * ((internal+external)/100.0);
        var absenteeismSavings = paricipatingEmployees * salary * (2.0/225.0);
      
        // calculate parking savings metric
        var parkDescription = '';
        var parkLabel = '';
        var parkingSavings = 0;
        switch(parkingType)
        {
            case 1:
                parkDescription = "$650/spot maintenance";
                showParkingInput(false);
                parkCost = 650/12;
                break;
            case 2:
                parkDescription = formatValue(parkCost*12) + " lease savings per spot";
                parkLabel = "Lease Cost/Spot (per month)";
                showParkingInput(true);
                break;
            case 3:
                parkDescription = formatValue(parkCost*12) + " subsidy savings";
                parkLabel = "Subsidy (per month)";
                showParkingInput(true);
                break;
            default:
                showParkingInput(false);
                parkDescription = "(no parking)";
                parkCost = 0;
                break;
        }
        parkingSavings = (paricipatingEmployees * .9) * parkCost * 12;

        // calculate total savings
        var totalSavings = healthSavings + productivitySavings + taxSavings + turnoverSavings + absenteeismSavings + parkingSavings;
              
        // display result on page
        document.getElementById('health-savings').innerHTML = formatValue(healthSavings);
        document.getElementById('productivity-savings').innerHTML = formatValue(productivitySavings);
        document.getElementById('tax-savings').innerHTML = formatValue(taxSavings);
        document.getElementById('turnover-savings').innerHTML = formatValue(turnoverSavings);
        document.getElementById('absenteeism-savings').innerHTML = formatValue(absenteeismSavings);
        document.getElementById('park-savings').innerHTML = formatValue(parkingSavings);
        document.getElementById('total-savings').innerHTML = formatValue(totalSavings);
        document.getElementById('park-description').innerHTML = parkDescription;
        document.getElementById('park-label').innerHTML = parkLabel;
    }
    
    function showParkingInput(state)
    {
        
        document.getElementById('park-label').style.display = (state) ? '' : 'none';
        document.getElementById('park-cost').style.display = (state) ? '' : 'none';
    }
    
    function toInt(value)
    {
        var intval = parseInt(value);
        return(isNaN(intval) ? 0 : intval);
    }
    
    function formatValue(value)
    {
        return(value ? "$" + addCommas(value.toFixed(0)) : '$0');
    }

    function addCommas(nStr)
    {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }

  </script>
  
</head>

<body style="font:14px arial, 'helvetica neue', sans-serif" onload='updateSavings()'>
  Number of Employees <input id='employees' onkeyup='updateSavings()'> Participation Rate <input id='participation' onkeyup='updateSavings()'>%
  <br>
  Per Employee Costs:
  Average Salary $<input id='salary' onkeyup='updateSavings()'>  Average Health Care $<input id='health-care' onkeyup='updateSavings()'><br>
  <br>
  Hiring Costs:
  Internal <input id='internal' onkeyup='updateSavings()'>% of Employee Salary<br>
  External <input id='external' onkeyup='updateSavings()'>% of Employee Salary<br>
  <br>
  Parking Costs:<br>
  Type of Parking Provided
  <select id='park-type' onkeyup='updateSavings()' onchange='updateSavings()'>
    <option value=0>None</option>
    <option value=1>Owned</option>
    <option value=2>Leased</option>
    <option value=3>Subsidized</option>
  </select>
  <span id='park-label'>Lease Cost per Spot</span> <input id='park-cost' onkeyup='updateSavings()'><br><br>
  Annual Savings
  <hr>
  <table>
    <tr>
      <td>Health Care Costs</td>
      <td>16% reduction for participants</td>
      <td id='health-savings'></td>
    <tr>
    <tr>
      <td style="padding-right:40px">Productivity Improvements</td>
      <td style="padding-right:40px">10% increase for participants</td>
      <td id='productivity-savings'></td>
    <tr>
    <tr>
      <td style="padding-right:40px">Payroll Taxes</td>
      <td style="padding-right:40px">$20/month deduction</td>
      <td id='tax-savings'></td>
    <tr>
    <tr>
      <td style="padding-right:40px">Hiring & Retention</td>
      <td style="padding-right:40px">reduced turnover</td>
      <td id='turnover-savings'></td>
    <tr>
    <tr>
      <td style="padding-right:40px">Absenteeism Reduction</td>
      <td style="padding-right:40px">2 days/year</td>
      <td id='absenteeism-savings'></td>
    <tr>
    <tr>
      <td style="padding-right:40px">Parking Savings</td>
      <td style="padding-right:40px" id='park-description'></td>
      <td id='park-savings'></td>
    <tr>
  </table>
  <hr>
  Total Annual Savings: <span id='total-savings'></span>
</body>

