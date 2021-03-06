$(document).ready(function()
{
	var qTipStyles = {style: {classes: 'tiptip_content'}, position: {my: 'top center', at: 'bottom center', adjust: {y: 4}}};
	$('.rmv').qtip($.extend({'content': JSLang['qTip_Remove']}, qTipStyles));
	$('.edit').qtip($.extend({'content': JSLang['qTip_Edit']}, qTipStyles));
	$('.del').qtip($.extend({'content': JSLang['qTip_Delete']}, qTipStyles));
	$('.accept').qtip($.extend({'content': JSLang['qTip_Accept']}, qTipStyles));
	$('.refuse').qtip($.extend({'content': JSLang['qTip_Refuse']}, qTipStyles));
});