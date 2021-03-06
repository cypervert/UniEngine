<script>
var JSLang = {'qTip_Edit': '{qTip_Edit}', 'qTip_Delete': '{qTip_Delete}', 'qTip_Remove': '{qTip_Remove}', 'qTip_Accept': '{qTip_Accept}', 'qTip_Refuse': '{qTip_Refuse}'};
</script>
<script src="js/buddy_list-2.0.0.1.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="css/buddy_list-2.0.0.1.min.css" />
<br/>
<table style="width: 800px;">
	<tr>
		<td class="c center" colspan="8">{Title}</td>
	</tr>
	<tr>
		<td class="c pad2 TH_1">{Headers_Nick}</td>
		<td class="c pad2 TH_2">{Headers_StatPoints}</td>
		<td class="c pad2 TH_3">{Headers_StatPosition}</td>
		<td class="c pad2 TH_4">{Headers_Ally}</td>
		<td class="c pad2 TH_5">{Headers_GalaxyPosition}</td>
		<td class="c pad2 TH_6">{Headers_Date}</td>
		<td class="c pad2 TH_7">{Headers_State}</td>
		<td class="c pad2 TH_8">{Headers_Actions}</td>
	</tr>
	{Insert_AwaitingList}
	<tr class="small {Insert_HideSeparator}">
		<th colspan="8">&nbsp;</th>
	</tr>
	{Insert_BuddyList}
	<tr class="{Insert_HideWithBuddyList}">
		<th class="pad5 orange" colspan="8">{Info_NoBuddy}</th>
	</tr>
	<tr class="{Insert_HidePagination}">
		<th class="pad7" colspan="8">{Insert_Pagination}</th>
	</tr>
	<tbody class="{Insert_MsgBoxHide}">
		<tr class="inv">
			<th>&nbsp;</th>
		</tr>
		<tr>
			<th class="pad5 {Insert_MsgBoxColor}" colspan="8">{Insert_MsgBoxText}</th>
		</tr>
	</tbody>
</table>