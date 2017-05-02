function sc_checkdate(datefield,dateinputstyle) {

    /*
        Accepted formats
        dd-mm-yyyy
        dd/mm/yyyy
        dd mm yyyy
        dd:mm:yyyy
        dd.mm.yyyy
        dd-mon-yyyy
        dd/mon/yyyy
        dd mon yyyy
        dd:mon:yyyy
        dd.mon.yyyy
        mon-dd-yyyy
        mon/dd/yyyy
        mon dd yyyy
        mon:dd:yyyy
        mon.dd.yyyy
    */

    if (datefield.value.trim() == '')
        return true;

    var status = true;
    str = datefield.value;
/*
    str = str.toLowerCase().replace(/[\s\d]st|nd|rd|th/,'');
*/
    strlower = str.toLowerCase();
    var n=strlower.indexOf("ad");
    if (n != -1)
    {
        adfound=true;
        var pattern = new RegExp('ad', 'gi');
        str = str.replace(pattern,"");
    }
    else
        adfound=false;
    var n=strlower.indexOf("bc");
    if (n != -1)
    {
        bcfound=true;
        var pattern = new RegExp('bc', 'gi');
        str = str.replace(pattern,"");
    }
    else
        bcfound=false;

    str = str.toLowerCase().replace(/([\d]+)(st|nd|rd|th)/,"$1");
    str = str.replace(/^\s*/,'');

/*
    var match = str.match(/[-/\s:.]/g);
*/

    /*
        Split on
        -
        /
        space
        :
        .

        + means to match any of those patterns one or more times

    */

    var parts = str.trim().split(/[-/\s:.]+/);


/*
    alert(parts.length);
*/
    if (isNaN(parts[0]) && parts.length==3)
    {

        /*
            Alpahabet first field
        */

        monthpart = parts[0];
        daypart = parts[1];
        yearpart = parts[2];
    }
    else if (isNaN(parts[1]) && parts.length==3)
    {

        /*
            Alpahabet second field
        */

        daypart = parts[0];
        monthpart = parts[1];
        yearpart = parts[2];
    }
    else if (parts.length == 1)
    {
       var daypart = '1';
       monthpart = 'january';
       yearpart = parts[0];
    }
    else
    {

        /*
            First two fields are numeric
        */

        if (dateinputstyle == 'MM/DD/YYYY')
        {
            monthpart = parts[0];
            daypart = parts[1];
            yearpart = parts[2];
        }
        else if (dateinputstyle == 'DD/MM/YYYY')
        {
            daypart = parts[0];
            monthpart = parts[1];
            yearpart = parts[2];
        }
        else
        {
            monthpart = parts[0];
            daypart = parts[1];
            yearpart = parts[2];
            if (yearpart.length == 2)
            {
                if (bcfound == false && adfound == false)
                {
                    if (yearpart.charAt(0) == '0' || yearpart.charAt(0) == '1')
                        yearparttest = '20' + yearpart;
                    else
                        yearparttest = '19' + yearpart;
                }
            }
            else
            {
                yearparttest = yearpart;
            }
            res = false;
            messagemdy = '';
            messagedmy = '';
            if (sc_isDate(daypart,monthpart,yearparttest))
            {
                monthpartdisp = sc_getMonthName(parseInt(monthpart));
                messagemdy = 'Confirm ' + daypart + ' ' + monthpartdisp + ' ' + yearpart + ' ?';
            }
            daypart = parts[0];
            monthpart = parts[1];
            yearpart = parts[2];
            if (sc_isDate(daypart,monthpart,yearparttest))
            {
                monthpartdisp = sc_getMonthName(parseInt(monthpart));
                messagedmy = 'Confirm ' + daypart + ' ' + monthpartdisp + ' ' + yearpart + ' ?';
            }
            if (messagemdy && messagedmy)
            {
                monthpart = parts[0];
                daypart = parts[1];
                yearpart = parts[2];
                res= confirm (messagemdy);
                if (res != true)
                {
                    daypart = parts[0];
                    monthpart = parts[1];
                    yearpart = parts[2];
                    res= confirm (messagedmy);
                }
            }
            else if (messagemdy)
            {
                monthpart = parts[0];
                daypart = parts[1];
                yearpart = parts[2];
                res = true;
            }
            else if (messagedmy)
            {
                daypart = parts[0];
                monthpart = parts[1];
                yearpart = parts[2];
                res = true;
            }
            if (res != true)
            {
                alert ('Invalid date entered');
                status = Boolean(false);
            }
        }
    }

    if (isNaN(daypart) || isNaN(yearpart))
    {
        alert ('Invalid date entered');
        status = Boolean(false);
    }

    if (status == true)
    {
        if (yearpart.length == 2)
        {
            if (bcfound == false && adfound == false)
            {
                if (yearpart.charAt(0) == '0' || yearpart.charAt(0) == '1')
                    yearpart = '20' + yearpart;
                else
                    yearpart = '19' + yearpart;
            }
        }
    }

    if (status == true)
    {
        if (bcfound == true)
        {
            yearpart = yearpart * -1;
        }
    }

    if (status == true)
    {
        if (isNaN(monthpart))
        {
            monthpart = sc_checkMonthName(monthpart);
            if (monthpart == -1)
            {
                alert ('Invalid month entered');
                status = Boolean(false);
            }
        }
        else
        {
            monthpart = sc_getMonthName(parseInt(monthpart));
            if (monthpart == -1)
            {
                alert ('Invalid month entered');
                status = Boolean(false);
            }
        }
    }
    if (status == true)
    {
        monthnumber = sc_retMonthNumber(monthpart);
        if (sc_isDate(daypart,monthnumber,yearpart))
        {
            if (bcfound == false)
                datefield.value = daypart + ' ' + monthpart + ' ' + yearpart;
            else
            {
                yearpart = yearpart * -1;
                datefield.value = daypart + ' ' + monthpart + ' ' + yearpart + ' bc';
            }
        }
        else
        {
            alert ('Invalid Date Entered');
            status = Boolean(false);
        }
    }

    if (status != true)
        datefield.value = '';
    return status;

}
function sc_checktime(timefield,timedisplaystyle)
{
    /*
        Accepted formats 
        hh-mm-ss
        hh mm ss
        hh:mm:ss
        hh.mm.ss
    */
    if (timefield.value.trim() == '')
        return true;

    var status = true;

    str = timefield.value;
    strlower = str.toLowerCase();
    var n=strlower.indexOf("am"); 
    if (n != -1)
    {
        amfound=true;
        var pattern = new RegExp('am', 'i');
        str = str.replace(pattern,"");
    }
    else
        amfound=false;
    var n=strlower.indexOf("pm"); 
    if (n != -1)
    {
        pmfound=true;
        var pattern = new RegExp('pm', 'gi');
        str = str.replace(pattern,"");
    }
    else
        pmfound=false;

    /*
        Split on
        -
        space
        :
        .
    */

    str = str.replace(/^\s+|\s+$/g,'');
    var parts = str.split(/[-\s:.]/);
/*
    alert (parts.length);
*/

    if (parts.length == 1 || parts.length == 2 || parts.length == 3) 
    {
        if (parts[1])
        {
            if (isNaN(parts[1]))
            {
                alert ('Invalid Time Format');
                status = false;
            }
            else
               minutes = parts[1];
        }
        else
            minutes = '00';
        if (parts[2])
        {
            if (isNaN(parts[2]))
            {
                alert ('Invalid Time Format');
                status = false;
            }
            else
                seconds = parts[2];
        }
        else
            seconds = '00';

        if (isNaN(parts[0]))
        {
            alert ('Invalid Time Format');
            status= false;
        }
        else if (pmfound == true)
        {
            if (parseInt(parts[0]) != 12)
                hours = parseInt(parts[0]) + 12;
            else
                hours = parts[0];
        }
        else if (amfound == true)
        {
            if (parseInt(parts[0]) == 12)
                hours = 0;
            else
                hours = parts[0];
        }
        else
            hours = parts[0];
    }
    else
    {
        alert ('Invalid Time Format');
        status = false;
    }

    if ((parseInt(hours) < 0 || parseInt(hours) > 23) || (parseInt(minutes) < 0 || parseInt(minutes) > 59) || (parseInt(seconds) < 0 || parseInt(seconds) > 59))
    {
        alert ('Invalid Time Format');
        status = false;
    }

    if (status == true)
    {
        if (timedisplaystyle == 'HH24:MI')
            timefield.value = hours + ':' + minutes;
        else
        {
            if (parseInt(hours) == 0)
            {
                hours = 12;
                ampm = 'am';
            }
            else if (parseInt(hours) == 12)
            {
                hours = 12;
                ampm = 'pm';
            }
            else if (hours > 12)
            {
    	        ampm = 'pm';
                hours = hours - 12;
            }
            else
            {
                ampm = 'am';
            }
            timefield.value  = hours + ':' + minutes + ' ' + ampm;
        }
/*
    if (hours.charAt(0) == '0')
        hours=hours.replace('0','');
*/
    }
	
/*`
     alert(noneurotimefield);
*/

    if (status != true)
        timefield.value = '';
    return status;

}
function sc_getMonthName(n) {
    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',  'Dec'];
    if (n >= 1 && n <= 12)
        return monthNames[n - 1];
    else
        return -1;
}
function sc_checkMonthName(name) {
    var monthNamesshort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',  'Dec'];
    var monthNameslong = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November',  'December'];
    for (i = 0; i <monthNamesshort.length; i++)
        if (name.toLowerCase() == monthNamesshort[i].toLowerCase())
            return monthNamesshort[i];
    for (i = 0; i <monthNameslong.length; i++)
    {
        if (name.toLowerCase() == monthNameslong[i].toLowerCase())
        {
            return monthNamesshort[i];
        }
    }
    return -1;
}
function sc_retMonthNumber (name) {
    var monthNamesshort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',  'Dec'];
    for (i = 0; i <monthNamesshort.length; i++)
        if (name.toLowerCase() == monthNamesshort[i].toLowerCase())
            return i+1;
    return -1;
}
function sc_isDate(d,m,y)
{
    d=d.replace(/^0+/, '');
    var date = new Date(y,m-1,d);
    if (parseInt(y) > 0 && parseInt(y) < 100)
    {
        testyear = parseInt(y)+1900;
        testyear = testyear.toString();
    }
    else
       testyear = y;
    var convertedDate = ""+date.getFullYear() + (date.getMonth()+1) + date.getDate();
    var givenDate = "" + testyear + parseInt(m) + parseInt(d);
    return ( givenDate == convertedDate);
}
function sc_checkdisplaytime(user)
{
   
    if (user == 1)
    { 
        if (document.getElementById('unknowntime'+user).checked)
        {
            if (document.getElementById('enter1time'))
                document.getElementById('enter1time').style.display = 'none';                
            document.getElementById('time1line').style.display = 'none';
        }
        else
        {
            if (document.getElementById('enter1time'))
                document.getElementById('enter1time').style.display = '';
            document.getElementById('time1line').style.display = '';                
        }
    }
    else
    {
        if (document.getElementById('unknowntime'+user).checked)
        {
            if (document.getElementById('secondpersonentry8'))
                document.getElementById('secondpersonentry8').style.display = 'none';                
            document.getElementById('secondpersonentry9').style.display = 'none';
        }
        else
        {
            if (document.getElementById('secondpersonentry8'))
                document.getElementById('secondpersonentry8').style.display = '';               
            document.getElementById('secondpersonentry9').style.display = '';
        }
    }           
           
}
