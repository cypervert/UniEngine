// Insert Overlib
// Insert jQuery
// Insert jQuery.tipTip
// Insert jQuery.qTip

// GlobaScript.js (by mdziekon)
// Google Analytics code goes here

function createCookie(name, value, days)
{
	var expires;
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime() + (days * 86400000));
		expires = "; expires=" + date.toGMTString();
	}
	else
	{
		expires = "";
	}
	document.cookie = name + "=" + value + expires + "; path=/";
}
createCookie('var_1124', screen.width + '_' + screen.height + '_' + screen.colorDepth, 1);

// Global.js (by mdziekon)
$(document).ready(function ()
{
	$('#clockDiv').qtip(
	{
		content: PHPVar['ServerTimeTxt'],
		style: {
			classes: 'tiptip_content'
		},
		position: {
			my: 'top center',
			at: 'bottom center',
			adjust: {
				y: 4
			}
		}
	});
	var TopMenuServerClientDifference = (PHPVar['ServerTimestamp'] * 1000) - new Date().getTime();
	setInterval(function ()
	{
		Now = new Date(new Date().getTime() + TopMenuServerClientDifference);
		Hour = Now.getHours();
		Min = Now.getMinutes();
		Sec = Now.getSeconds();
		Day = Now.getDate();
		Month = Now.getMonth() + 1;
		Year = Now.getFullYear();
		if (Hour < 10)
		{
			Hour = '0' + Hour
		}
		if (Min < 10)
		{
			Min = '0' + Min
		}
		if (Sec < 10)
		{
			Sec = '0' + Sec
		}
		if (Day < 10)
		{
			Day = '0' + Day
		}
		if (Month < 10)
		{
			Month = '0' + Month
		}
		$('#clock').html(Day + '.' + Month + '.' + Year + ' <b>' + Hour + ':' + Min + ':' + Sec + '</b>')
	}, 250)
});