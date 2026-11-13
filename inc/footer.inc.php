<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */

// Check if this is a valid include
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}

// Users online
if (defined('SHOW_ONLINE'))
{
    hesk_printOnline();
}

/*******************************************************************************
The code below handles HESK licensing and must be included in the template.

Removing this code is a direct violation of the HESK End User License Agreement,
will void all support and may result in unexpected behavior.

To purchase a HESK license and support future HESK development please visit:
https://www.hesk.com/buy.php
*******************************************************************************/
$hesk_settings['hesk_license']('Qo8Zm9vdGVyIGNsYXNzPSJmb290ZXIiPg0KICAgIDxwIGNsY
XNzPSJ0ZXh0LWNlbnRlciI+UG93ZXJlZCBieSA8YSBocmVmPSJodHRwczovL3d3dy5oZXNrLmNvbSIgY
2xhc3M9ImxpbmsiPkhlbHAgRGVzayBTb2Z0d2FyZTwvYT4gPHNwYW4gY2xhc3M9ImZvbnQtd2VpZ2h0L
WJvbGQiPkhFU0s8L3NwYW4+PGJyPk1vcmUgSVQgZmlyZXBvd2VyPyBUcnkgPGEgaHJlZj0iaHR0cHM6L
y93d3cuc3lzYWlkLmNvbS8/dXRtX3NvdXJjZT1IZXNrJmFtcDt1dG1fbWVkaXVtPWNwYyZhbXA7dXRtX
2NhbXBhaWduPUhlc2tQcm9kdWN0X1RvX0hQIiBjbGFzcz0ibGluayI+U3lzQWlkPC9hPjwvcD4NCjwvZ
m9vdGVyPg0K',"\104", "a809404e0adf9823405ee0b536e5701fb7d3c969");
/*******************************************************************************
END LICENSE CODE
*******************************************************************************/
?><p class="text-center">Powered by <a href="https://rafihportal.com/" class="link">Rafih Portal</a> &copy; Copyright 2026 </p>
</main> <!-- End main -->
<?php
if (isset($login_wrapper)) {
    echo '</div> <!-- End wrapper login -->';
}
?>
</div> <!-- End wrapper -->
<input type="hidden" name="HESK_PATH" value="<?php echo HESK_PATH; ?>">
<!-- Add Items -->
<script type="text/javascript">
var room = 1;	
function education_fields() {
 
    room++;
    var objTo = document.getElementById('education_fields')
    var divtest = document.createElement("div");
	divtest.setAttribute("class", "form-group removeclass"+room);
	var rdiv = 'removeclass'+room;
    divtest.innerHTML = '<div class="row"><div class="col-sm-8"><div class="row"><div class="col-sm-6 nopadding"><div class="form-group"><input type="text" class="form-control" id="description" name="description[]" value="" placeholder="Description"></div></div><div class="col-sm-6 nopadding"><div class="form-group"><input type="number" class="form-control" id="budget" name="budget[]" value="" placeholder="Budget"></div></div></div><div class="row"><div class="col-sm-6 nopadding"><div class="form-group"><input type="number" class="form-control" id="quantity" name="quantity[]" value="" placeholder="Quantity"></div></div><div class="col-sm-6 nopadding"><div class="form-group"><div class="input-group"><select class="form-control" name="unit[]"><option value="Box">Box</option><option value="Peice">Peice</option></select></div></div></div></div></div><div class="col-sm-4"><div class="row"><div class="col-sm-11 nopadding"><div class="form-group"><div class="input-group"><textarea style="height:inherit" class="form-control" id="remarks" name="remarks[]" rows="5" cols="30" placeholder="Remarks"></textarea><div class="input-group-btn"><button class="btn btn-danger" type="button"  onclick="remove_education_fields('+ room +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span> </button></div></div></div></div></div></div></div><divclass="clear';
	
<!-- divtest.innerHTML = '<div class="col-sm-3 nopadding"><div class="form-group"> <input type="text" class="form-control" id="description" name="description[]" value="" placeholder="Description"></div></div><div class="col-sm-3 nopadding"><div class="form-group"> <input type="text" class="form-control" id="quantity" name="quantity[]" value="" placeholder="Quantity"></div></div><div class="col-sm-3 nopadding"><div class="form-group"> <input type="text" class="form-control" id="budget" name="budget[]" value="" placeholder="Budget"></div></div><div class="col-sm-3 nopadding"><div class="form-group"><select class="form-control" id="unit" name="unit[]"><option value="">Unit</option><option value="box">Box</option><option value="peice">Peice</option></select></div></div><div class="col-sm-3 nopadding"><div class="form-group"><div class="input-group"><textarea style="height: inherit" class="form-control" id="remarks" name="remarks[]" rows="5" cols="30" placeholder="Remarks"></textarea><div class="input-group-btn"><button class="btn btn-danger" type="button"  onclick="remove_education_fields('+ room +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span> </button></div></div></div></div><div class="clear"></div>';-->	
    
    objTo.appendChild(divtest)
}
	
function education_fields2() {
 
    room++;
    var objTo = document.getElementById('education_fields2')
    var divtest = document.createElement("div");
	divtest.setAttribute("class", "form-group business-card-row removeclass"+room);
	var rdiv = 'removeclass'+room;
    divtest.innerHTML = '<div class="col-sm-3 nopadding"><div class="form-group"> <input type="text" class="form-control" id="card_name" name="card_name[]" value="" placeholder="First Name"></div></div><div class="col-sm-3 nopadding"><div class="form-group"> <input type="text" class="form-control" id="lastname" name="lastname[]" value="" placeholder="Last Name"></div></div><div class="col-sm-3 nopadding"><div class="form-group"> <input type="text" class="form-control" id="card_title" name="card_title[]" value="" placeholder="Title"></div></div><div class="col-sm-3 nopadding"><div class="form-group"> <input type="text" class="form-control" id="business_phone" name="business_phone[]" value="" placeholder="Business Phone"></div></div><div class="col-sm-3 nopadding"><div class="form-group"><input type="text" class="form-control" id="card_email" name="card_email[]" value="" placeholder="Email"></div></div><div class="col-sm-3 nopadding"><div class="form-group"><input type="text" class="form-control" id="extension" name="extension[]" value="" placeholder="Extension"></div></div><div class="col-sm-3 nopadding"><div class="form-group"><div class="input-group"> <input type="text" class="form-control" id="card_cell" name="card_cell[]" value="" placeholder="Cell"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_education_fields2('+ room +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span> </button></div></div></div></div><div class="clear"></div>';
    
    objTo.appendChild(divtest)
}	
	
   function remove_education_fields(rid) {
	   $('.removeclass'+rid).remove();
   }
	
	 function remove_education_fields2(rid) {
	   $('.removeclass'+rid).remove();
   }
var newroom = 0;
	function education_fields_item_one() {
 
    newroom++;
    var objTo = document.getElementById('education_fields_item_one')
    var divtest = document.createElement("div");
    divtest.setAttribute("class", "form-group removeclass"+newroom);
    var rdiv = 'removeclass'+newroom;
    divtest.innerHTML = '<div class="row"> <div class="col-sm-8"> <div class="row"> <div class="col-sm-3 nopadding" style="width:23%"> <div class="form-group"> <select class="form-control" name="computer_type['+ newroom +']" id="computer_type" style=" height: 40px; background-color: #f1f9ff; font-size:12px; border: solid 1px #0c783c;"> <option value="">Computer Type</option> <option value="Laptop">Laptop</option> <option value="Desktop">Desktop</option> <option value="Workstation">Workstation</option> </select> </div> </div>  <div class="col-sm-3 nopadding" style="width:23%;"> <div class="form-group"> <select class="form-control" name="computer_operating_system['+ newroom +']" id="computer_operating_system" style=" height: 40px; background-color: #f1f9ff; font-size:12px; border: solid 1px #0c783c;"> <option value="">Operating Sys</option> <option value="Windows">Windows</option> <option value="Linux">Linux</option> <option value="Mac">Mac</option> </select> </div> </div>  <div class="col-sm-3 nopadding" style="width:23%"> <div class="form-group"> <input type="text" class="form-control" id="computer_processor" name="computer_processor['+ newroom +']" value="" placeholder="Processor Type"> </div> </div>  <div class="col-sm-3 nopadding" style="width:23%"> <div class="form-group"> <input type="number" class="form-control" id="computer_ram" name="computer_ram['+ newroom +']" value="" placeholder="RAM"> </div> </div> </div> <div class="row"> <div class="col-sm-3 nopadding" style="width:23%"> <div class="form-group"> <input type="text" class="form-control" id="computer_storage" name="computer_storage['+ newroom +']" value="" placeholder="Storage Capacity"> </div> </div>  <div class="col-sm-3 nopadding" style="width:23%"> <div class="form-group"> <input type="text" class="form-control" id="computer_graphics" name="computer_graphics['+ newroom +']" value="" placeholder="Graphics Card"> </div> </div>  <div class="col-sm-3 nopadding" style="width:23%"> <div class="form-group"> <input type="text" class="form-control" id="computer_screen" name="computer_screen['+ newroom +']" value="" placeholder="Screen Size"> </div> </div> <div class="col-sm-3 nopadding" style="width:23%"> <div class="form-group"> <input type="number" class="form-control" id="computer_budget" name="computer_budget['+ newroom +']" value="" placeholder="Budget"> </div> </div>  </div> <div class="row"> <div class="col-sm-4 nopadding" style="width:23%"> <div class="form-group">Keyboard: <br> <input type="radio" name="computer_keyboard['+ newroom +']" id="computer_keyboard'+ newroom +'" value="Yes" style="margin:0px 10px 3px 0px">Yes <input type="radio" name="computer_keyboard['+ newroom +']" id="computer_keyboard'+ newroom +'" value="No" style="margin:0px 10px 3px 5px">No </div> </div>  <div class="col-sm-4 nopadding" style="width:23%"> <div class="form-group">Mouse: <br> <input type="radio" name="computer_mouse['+ newroom +']" value="Yes" style="margin:0px 10px 3px 0px">Yes <input type="radio" name="computer_mouse['+ newroom +']" value="No" style="margin:0px 10px 3px 5px">No </div> </div>  <div class="col-sm-4 nopadding" style="width:23%"> <div class="form-group">Monitor: <br> <input type="radio" name="computer_monitor['+ newroom +']" value="Yes" style="margin:0px 10px 3px 0px">Yes <input type="radio" name="computer_monitor['+ newroom +']" value="No" style="margin:0px 10px 3px 5px">No </div> </div> </div> </div>  <div class="col-sm-4"> <div class="row"> <div class="col-sm-12 nopadding" style="width:90%"> <div class="form-group"> <div class="input-group"> <textarea class="form-control" id="computer_comments" name="computer_comments['+ newroom +']" value="" placeholder="Comments" style=" height: 150px; background-color: #f1f9ff; font-size:12px; border: solid 1px #0c783c;"> Comments...</textarea> <div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_education_fields_item_one('+ room +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span> </button></div> </div> </div> </div> </div> <div class="clear"></div> </div></div> </div>';
    
    objTo.appendChild(divtest)
    }
    function remove_education_fields_item_one(rid) {
        $('.removeclass'+rid).remove();
    }

    function education_fields_item_two() {
 
    room++;
    var objTo = document.getElementById('education_fields_item_two')
    var divtest = document.createElement("div");
    divtest.setAttribute("class", "form-group removeclass"+room);
    var rdiv = 'removeclass'+room;
		
		
    divtest.innerHTML = '<div class="row"><div class="col-sm-12"><div class="row"><div class="col-sm-3 nopadding"><div class="form-group"><input type="text" class="form-control" id="printer_make" name="printer_make[]" value="" placeholder="Make"></div></div><div class="col-sm-3 nopadding"><div class="form-group"><input type="text" class="form-control" id="printer_model" name="printer_model[]" value="" placeholder="Model"></div></div><div class="col-sm-2 nopadding"><div class="form-group"><input type="text" class="form-control" id="printer_used_by" name="printer_used_by[]" value="" placeholder="Used BY"></div></div><div class="col-sm-2 nopadding"><div class="form-group"><input type="text" class="form-control" id="printer_catridge_make" name="printer_catridge_make[]" value="" placeholder="Catridge Make"></div></div><div class="col-sm-2 nopadding"><div class="form-group"><input type="text" class="form-control" id="printer_catridge_model" name="printer_catridge_model[]" value="" placeholder="Catridge Model"></div></div></div><div class="row"><div class="col-sm-3 nopadding"><div class="form-group"><input type="number" class="form-control" id="printer_quantity" name="printer_quantity[]" value="" placeholder="Quantity"></div></div><div class="col-sm-3 nopadding"><div class="form-group"><input type="number" class="form-control" id="printer_budget" name="printer_budget[]" value="" placeholder="Budget"></div></div><div class="col-sm-6"><div class="row"><div class="col-sm-11 nopadding"><div class="form-group"><div class="input-group"><textarea class="form-control" id="printer_comments" name="printer_comments[]" value="" placeholder="Comments" style="height:60px"></textarea><div class="input-group-btn"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_education_fields_item_two('+ room +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span> </button></div></div></div></div></div></div></div></div></div></div><div class="clear"></div>';
		
<!--    divtest.innerHTML = '<div class="col-sm-3 nopadding"><div class="form-group"> <input type="text" class="form-control" id="printer_make" name="printer_make[]" value="" placeholder="Make"> </div> </div><div class="col-sm-3 nopadding"> <div class="form-group"> <input type="text" class="form-control" id="printer_model" name="printer_model[]" value="" placeholder="Model"> </div> </div> <div class="col-sm-3 nopadding"> <div class="form-group"> <input type="text" class="form-control" id="printer_used_by" name="printer_used_by[]" value="" placeholder="Used BY"> </div> </div> <div class="col-sm-3 nopadding"> <div class="form-group"> <input type="text" class="form-control" id="printer_catridge_make" name="printer_catridge_make[]" value="" placeholder="Toner Catridge Make"> </div> </div> <div class="col-sm-3 nopadding"> <div class="form-group"> <input type="text" class="form-control" id="printer_catridge_model" name="printer_catridge_model[]" value="" placeholder="Toner Catridge Model"> </div> </div> <div class="col-sm-3 nopadding"> <div class="form-group"> <input type="text" class="form-control" id="printer_quantity" name="printer_quantity[]" value="" placeholder="Quantity"> </div> </div> <div class="col-sm-3 nopadding"> <div class="form-group"> <input type="text" class="form-control" id="printer_budget" name="printer_budget[]" value="" placeholder="Budget"> </div> </div><div class="col-sm-3 nopadding"><div class="form-group"><div class="input-group"> <input type="text" class="form-control" id="printer_comments" name="printer_comments[]" value="" placeholder="Comments"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_education_fields_item_two('+ room +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span> </button></div></div></div></div><div class="clear"></div>';-->
		
		
    
    objTo.appendChild(divtest)
    }
    function remove_education_fields_item_two(rid) {
        $('.removeclass'+rid).remove();
    }

    function education_fields_item_three() {
 
    room++;
    var objTo = document.getElementById('education_fields_item_three')
    var divtest = document.createElement("div");
    divtest.setAttribute("class", "form-group removeclass"+room);
    var rdiv = 'removeclass'+room;
    divtest.innerHTML = '<div class="col-sm-3 nopadding"> <div class="form-group"> <input type="text" class="form-control" id="stationary_item" name="stationary_item[]" value="" placeholder="Item Name"> </div> </div> <div class="col-sm-3 nopadding"> <div class="form-group"> <input type="number" class="form-control" id="stationary_quantity" name="stationary_quantity[]" value="" placeholder="Quantity"> </div> </div> <div class="col-sm-3 nopadding"> <div class="form-group"> <input type="number" class="form-control" id="stationary_budget" name="stationary_budget[]" value="" placeholder="Budget"> </div> </div><div class="col-sm-3 nopadding"><div class="form-group"><div class="input-group"> <input type="text" class="form-control" id="stationary_remarks" name="stationary_remarks[]" value="" placeholder="Remarks"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_education_fields_item_three('+ room +');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span> </button></div></div></div></div><div class="clear"></div>';
    
    objTo.appendChild(divtest)
    }
    function remove_education_fields_item_three(rid) {
        $('.removeclass'+rid).remove();
    }
	
   $('#exampleModal').on('shown.bs.modal', function () {
  $('#myInput').trigger('focus')
})
</script>
<script type="text/javascript">
					function set_vals(track,id,desc){
						//alert(track+' , '+id);
						//$("#sidepopup").modal('show');
						document.getElementById("ticket_trackid").value = track;
						document.getElementById("item_id").value = id;
						document.getElementById("item_desc").innerHTML = desc;
					}
					</script>
<!-- END -->
<script src="<?php echo HESK_PATH; ?>js/svg4everybody.min.js"></script>
<?php
// Do we need the calendar functions?
if (defined('CALENDAR'))
{
?>
<script src="<?php echo HESK_PATH; ?>js/datepicker.min.js"></script>
<script type="text/javascript">
(function ($) { $.fn.datepicker.language['en'] = {
    days: ['<?php echo $hesklang['d0']; ?>', '<?php echo $hesklang['d1']; ?>', '<?php echo $hesklang['d2']; ?>', '<?php echo $hesklang['d3']; ?>', '<?php echo $hesklang['d4']; ?>', '<?php echo $hesklang['d5']; ?>', '<?php echo $hesklang['d6']; ?>'],
    daysShort: ['<?php echo $hesklang['sun']; ?>', '<?php echo $hesklang['mon']; ?>', '<?php echo $hesklang['tue']; ?>', '<?php echo $hesklang['wed']; ?>', '<?php echo $hesklang['thu']; ?>', '<?php echo $hesklang['fri']; ?>', '<?php echo $hesklang['sat']; ?>'],
    daysMin: ['<?php echo $hesklang['su']; ?>', '<?php echo $hesklang['mo']; ?>', '<?php echo $hesklang['tu']; ?>', '<?php echo $hesklang['we']; ?>', '<?php echo $hesklang['th']; ?>', '<?php echo $hesklang['fr']; ?>', '<?php echo $hesklang['sa']; ?>'],
    months: ['<?php echo $hesklang['m1']; ?>','<?php echo $hesklang['m2']; ?>','<?php echo $hesklang['m3']; ?>','<?php echo $hesklang['m4']; ?>','<?php echo $hesklang['m5']; ?>','<?php echo $hesklang['m6']; ?>', '<?php echo $hesklang['m7']; ?>','<?php echo $hesklang['m8']; ?>','<?php echo $hesklang['m9']; ?>','<?php echo $hesklang['m10']; ?>','<?php echo $hesklang['m11']; ?>','<?php echo $hesklang['m12']; ?>'],
    monthsShort: ['<?php echo $hesklang['ms01']; ?>','<?php echo $hesklang['ms02']; ?>','<?php echo $hesklang['ms03']; ?>','<?php echo $hesklang['ms04']; ?>','<?php echo $hesklang['ms05']; ?>','<?php echo $hesklang['ms06']; ?>', '<?php echo $hesklang['ms07']; ?>','<?php echo $hesklang['ms08']; ?>','<?php echo $hesklang['ms09']; ?>','<?php echo $hesklang['ms10']; ?>','<?php echo $hesklang['ms11']; ?>','<?php echo $hesklang['ms12']; ?>'],
    today: '<?php echo hesk_slashJS($hesklang['r1']); ?>',
    clear: '<?php echo hesk_slashJS($hesklang['clear']); ?>',
    dateFormat: '<?php echo hesk_slashJS($hesk_settings['format_datepicker_js']); ?>',
    timeFormat: '<?php echo hesk_slashJS($hesk_settings['format_time']); ?>',
    firstDay: <?php echo $hesklang['first_day_of_week']; ?>
}; })(jQuery);
</script>
<?php
}
?>

<script type="text/javascript" src="<?php echo HESK_PATH; ?>js/app<?php echo $hesk_settings['debug_mode'] ? '' : '.min'; ?>.js?<?php echo $hesk_settings['hesk_version']; ?>"></script>

<?php
// Any adjustments to datepicker?
if (isset($hesk_settings['datepicker'])):
    ?>
    <script>

    function convertDateToUTC(date) { return new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds()); }

    $(document).ready(function () {
        const myDP = {};
        <?php
        foreach ($hesk_settings['datepicker'] as $selector => $data) {
            echo "
                myDP['{$selector}'] = $('{$selector}').datepicker(".((isset($data['position']) && is_string($data['position'])) ? "{position: '{$data['position']}'}" : "").");
            ";
            if (isset($data['timestamp']) && ($ts = intval($data['timestamp']))) {
                if ( ! empty($hesk_settings['datepicker'][$selector]['fromDB'])) {
                    echo "myDP['{$selector}'].data('datepicker').selectDate(convertDateToUTC(new Date({$ts} * 1000)));";
                } else {
                    echo "myDP['{$selector}'].data('datepicker').selectDate(new Date({$ts} * 1000));";
                }
            }
        }
        ?>
        $('.showme').click(function (e) {
            $(this).addClass('active');
            $(this).parent()
                .find('.datepicker')
                .data("datepicker")
                .show();
        });
    });
    </script>
    <?php
endif;

// Auto-select first empty or error field on non-staff pages?
if (defined('AUTOFOCUS'))
{
?>
<script language="javascript">
(function(){
	var forms = document.forms || [];
	for(var i = 0; i < forms.length; i++)
    {
		for(var j = 0; j < forms[i].length; j++)
        {
			if(
				!forms[i][j].readonly != undefined &&
				forms[i][j].type != "hidden" &&
				forms[i][j].disabled != true &&
				forms[i][j].style.display != 'none' &&
				(forms[i][j].className == 'isError' || forms[i][j].className == 'isNotice' || forms[i][j].value == '')
			)
	        {
				forms[i][j].focus();
				return;
			}
		}
	}
})();
</script>
<?php
}

// Apply status coloring to drop-down box; needs to be called after app.js
if (isset($hesk_settings['print_status_select_box_jquery']))
{
    hesk_print_status_select_box_jquery();
}

echo '
</body>
</html>
';

$hesk_settings['security_cleanup']('exit');
