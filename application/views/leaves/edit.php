<?php 
/*
 * This file is part of lms.
 *
 * lms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * lms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 */


CI_Controller::get_instance()->load->helper('language');
$this->lang->load('leaves', $language);
$this->lang->load('status', $language);
$this->lang->load('global', $language);?>

<h2><?php echo lang('leaves_edit_title');?><?php echo $leave['id']; ?> &nbsp;
<a href="<?php echo lang('global_link_doc_page_request_leave');?>" title="<?php echo lang('global_link_tooltip_documentation');?>" target="_blank" rel="nofollow"><i class="icon-question-sign"></i></a>
</h2>

<div class="row-fluid">
    <div class="span8">

<?php echo validation_errors(); ?>

<?php if (isset($_GET['source'])) {
    echo form_open('leaves/edit/' . $id . '?source=' . $_GET['source']);
} else {
    echo form_open('leaves/edit/' . $id);
} ?>

    <label for="viz_startdate" required><?php echo lang('leaves_edit_field_start');?></label>
    <input type="input" name="viz_startdate" id="viz_startdate" value="<?php $date = new DateTime($leave['startdate']); echo $date->format(lang('global_date_format'));?>" />
    <input type="hidden" name="startdate" id="startdate" value="<?php echo $leave['startdate'];?>" />
    <select name="startdatetype" id="startdatetype">
        <option value="Morning" <?php if ($leave['startdatetype'] == "Morning") {echo "selected";}?>><?php echo lang('leaves_date_type_morning');?></option>
        <option value="Afternoon" <?php if ($leave['startdatetype'] == "Afternoon") {echo "selected";}?>><?php echo lang('leaves_date_type_afternoon');?></option>
    </select><br />
    
    <label for="viz_enddate" required><?php echo lang('leaves_edit_field_end');?></label>
    <input type="input" name="viz_enddate" id="viz_enddate" value="<?php $date = new DateTime($leave['enddate']); echo $date->format(lang('global_date_format'));?>" />
    <input type="hidden" name="enddate" id="enddate" value="<?php echo $leave['startdate'];?>" />
    <select name="enddatetype" id="enddatetype">
        <option value="Morning" <?php if ($leave['enddatetype'] == "Morning") {echo "selected";}?>><?php echo lang('leaves_date_type_morning');?></option>
        <option value="Afternoon" <?php if ($leave['enddatetype'] == "Afternoon") {echo "selected";}?>><?php echo lang('leaves_date_type_afternoon');?></option>
    </select><br />
    
    <label for="type" required><?php echo lang('leaves_edit_field_type');?></label>
    <select name="type" id="type">
    <?php foreach ($types as $types_item): ?>
        <option value="<?php echo $types_item['id'] ?>" <?php if ($types_item['id'] == 1) echo "selected" ?>><?php echo $types_item['name'] ?></option>
    <?php endforeach ?>    
    </select><br />
    
    <label for="duration" required><?php echo lang('leaves_edit_field_duration');?></label>
    <input type="input" name="duration" id="duration" value="<?php echo $leave['duration']; ?>" />
    
    <div class="alert hide alert-error" id="lblCreditAlert">
        <button type="button" class="close">&times;</button>
        <?php echo lang('leaves_edit_field_duration_message');?>
    </div>
    
    <label for="cause"><?php echo lang('leaves_edit_field_cause');?></label>
    <textarea name="cause"><?php echo $leave['cause']; ?></textarea>
    
    <label for="status" required><?php echo lang('leaves_edit_field_status');?></label>
    <select name="status">
        <option value="1" <?php if ($leave['status'] == 1) echo 'selected'; ?>><?php echo lang('Planned');?></option>
        <option value="2" <?php if ($leave['status'] == 2) echo 'selected'; ?>><?php echo lang('Requested');?></option>
        <?php if ($is_hr) {?>
        <option value="3" <?php if ($leave['status'] == 3) echo 'selected'; ?>><?php echo lang('Accepted');?></option>
        <option value="4" <?php if ($leave['status'] == 4) echo 'selected'; ?>><?php echo lang('Rejected');?></option>        
        <?php } ?>
    </select><br />

    <button type="submit" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;<?php echo lang('leaves_edit_button_update');?></button>
    &nbsp;
    <?php if (isset($_GET['source'])) {?>
        <a href="<?php echo base_url() . $_GET['source']; ?>" class="btn btn-danger"><i class="icon-remove icon-white"></i>&nbsp;<?php echo lang('leaves_edit_button_cancel');?></a>
    <?php } else {?>
        <a href="<?php echo base_url(); ?>leaves" class="btn btn-danger"><i class="icon-remove icon-white"></i>&nbsp;<?php echo lang('leaves_edit_button_cancel');?></a>
    <?php } ?>
    
</form>

    </div>
    <div class="span4">
        <span id="spnDayOff">&nbsp;</span>
    </div>
</div>

<link rel="stylesheet" href="<?php echo base_url();?>assets/css/flick/jquery-ui-1.10.4.custom.min.css">
<script src="<?php echo base_url();?>assets/js/jquery-ui-1.10.4.custom.min.js"></script>
<?php //Prevent HTTP-404 when localization isn't needed
if ($language_code != 'en') { ?>
<script src="<?php echo base_url();?>assets/js/i18n/jquery.ui.datepicker-<?php echo $language_code;?>.js"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/moment-with-langs.min.js" type="text/javascript"></script>
<script type="text/javascript">
    
    var last_startDate = moment("Jan 1, 1970");
    var last_endDate = moment("Jan 1, 1970");
    var duration = 0;
    var last_duration = 0;
    var addDays = 0;
    var last_type = 0;
    
    //Try to calculate the length of the leave
    function getLeaveLength() {
        var start = moment($('#startdate').val());
        var end = moment($('#enddate').val());
        var startType = $('#startdatetype option:selected').val();
        var endType = $('#enddatetype option:selected').val();      
        
        if (start.isValid() && end.isValid()) {
            if (start.isSame(end)) {
                addDays = 0;
                if (startType == "Morning" && endType == "Morning") {
                    duration = 0.5;
                    $("#spnDayOff").html("<img src='<?php echo base_url();?>assets/images/leave_1d_MM.png' />");
                }
                if (startType == "Afternoon" && endType == "Afternoon") {
                    duration = 0.5;
                    $("#spnDayOff").html("<img src='<?php echo base_url();?>assets/images/leave_1d_AA.png' />");
                }
                if (startType == "Morning" && endType == "Afternoon") {
                    duration = 1;
                    $("#spnDayOff").html("<img src='<?php echo base_url();?>assets/images/leave_1d_MA.png' />");
                }
                if (startType == "Afternoon" && endType == "Morning") {
                    //Error
                    $("#spnDayOff").html("<img src='<?php echo base_url();?>assets/images/date_error.png' />");
                }
                $('#duration').val(duration + addDays);
                checkDuration();
            } else {
                 if (start.isBefore(end)) {
                    if (startType == "Morning" && endType == "Morning") {
                        $("#spnDayOff").html("<img src='<?php echo base_url();?>assets/images/leave_2d_MM.png' />");
                        addDays = 0.5;
                    }
                    if (startType == "Afternoon" && endType == "Afternoon") {
                        $("#spnDayOff").html("<img src='<?php echo base_url();?>assets/images/leave_2d_AA.png' />");
                        addDays = 0.5;
                    }
                    if (startType == "Morning" && endType == "Afternoon") {
                        $("#spnDayOff").html("<img src='<?php echo base_url();?>assets/images/leave_2d_MA.png' />");
                        addDays = 1;
                    }
                    if (startType == "Afternoon" && endType == "Morning") {
                        $("#spnDayOff").html("<img src='<?php echo base_url();?>assets/images/leave_2d_AM.png' />");
                        addDays = 0;
                    }
                    //Prevent multiple triggers by UI components
                    if (!start.isSame(last_startDate) || !end.isSame(last_endDate)) {
                        $.ajax({
                            type: "POST",
                            url: "<?php echo base_url();?>leaves/length",
                            data: {
                                user_id: <?php echo $user_id; ?>,
                                start: $('#startdate').val(),
                                end: $('#enddate').val()
                                }
                            })
                            .done(function(msg) {
                                duration = parseFloat(msg);
                                $('#duration').val(duration + addDays);
                                checkDuration();
                            });
                            
                    }
                    else {
                        $('#duration').val(duration + addDays);
                        checkDuration();
                    }
                    last_startDate = start;
                    last_endDate = end;
                 } else {
                    //Error
                 }
            }
        }   //start and end dates are valid
    }
    
    //Check the entered duration of the leave
    function checkDuration() {
        //Prevent multiple triggers by UI components
        if ((duration != last_duration) || (last_type != $("#type option:selected").val())) {
            if ($("#duration").val() != "") {
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url();?>leaves/credit",
                    data: { id: <?php echo $user_id; ?>, type: $("#type option:selected").text() }
                    })
                    .done(function(msg) {
                        var credit = parseInt(msg);
                        var duration = parseFloat($("#duration").val());
                        if (duration > credit) {
                            $("#lblCreditAlert").show();
                        } else {
                            $("#lblCreditAlert").hide();
                        }
                    });
             }
         }
         last_duration = duration;
         last_type = $("#type option:selected").val();
    }
    
    $(function () {
        $("#viz_startdate").datepicker({
            changeMonth: true,
            changeYear: true,
            altFormat: "yy-mm-dd",
            altField: "#startdate",
            numberOfMonths: 1,
                  onClose: function( selectedDate ) {
                    $( "#viz_enddate" ).datepicker( "option", "minDate", selectedDate );
                  }
        }, $.datepicker.regional['<?php echo $language_code;?>']);
        $("#viz_enddate").datepicker({
            changeMonth: true,
            changeYear: true,
            altFormat: "yy-mm-dd",
            altField: "#enddate",
            numberOfMonths: 1,
                  onClose: function( selectedDate ) {
                    $( "#viz_startdate" ).datepicker( "option", "maxDate", selectedDate );
                  }
        }, $.datepicker.regional['<?php echo $language_code;?>']);
        
        //Force decimal separator whatever the locale is
        $( "#days" ).keyup(function() {
            var value = $("#days").val();
            value = value.replace(",", ".");
            $("#days").val(value);
        });
        
        $('#viz_startdate').change(function() {getLeaveLength();});
        $('#viz_enddate').change(function() {getLeaveLength();});
        $('#startdatetype').change(function() {getLeaveLength();});
        $('#enddatetype').change(function() {getLeaveLength();});
        $('#type').change(function() {checkDuration();});
        
        //Check if the user has not exceed the number of entitled days
        $("#duration").keyup(function() {
            checkDuration();
        });
    });
</script>