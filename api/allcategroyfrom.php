/* http://localhost:8100/dashboard/tickets/new/2 */
<div class="main__content categories">
                    <h2 class="select__title">Select request category</h2>
            <div class="nav">
                                    <a href="new_ticket.php?a=add&amp;category=2" class="navlink ">
                        <div class="icon-in-circle">
                            <svg class="icon icon-chevron-right">
                                <use xlink:href="../img/sprite.svg#icon-chevron-right"></use>
                            </svg>
                        </div>
                        <div>
                            <h5 class="navlink__title"><!--[if IE]> &raquo; <![endif]-->New Purchase Requistion</h5>

                            <label>This is for all purchases except Capital Expenditures (over $1000).  For these items us the Capital Expenditure</label>

                        </div>
                    </a>
                                    <a href="new_ticket.php?a=add&amp;category=3" class="navlink ">
                        <div class="icon-in-circle">
                            <svg class="icon icon-chevron-right">
                                <use xlink:href="../img/sprite.svg#icon-chevron-right"></use>
                            </svg>
                        </div>
                        <div>
                            <h5 class="navlink__title"><!--[if IE]> &raquo; <![endif]-->Capital Expenditure</h5>

                            <label>Use the capital expenditure form for long-term asset purchases. Examples include property, equipment, software, patents, and intangible assets.</label>

                        </div>
                    </a>
                                    <a href="new_ticket.php?a=add&amp;category=4" class="navlink ">
                        <div class="icon-in-circle">
                            <svg class="icon icon-chevron-right">
                                <use xlink:href="../img/sprite.svg#icon-chevron-right"></use>
                            </svg>
                        </div>
                        <div>
                            <h5 class="navlink__title"><!--[if IE]> &raquo; <![endif]-->Business Card Request</h5>

                            <label>To request a business card, employees need to fill out a form with their name, job title, and contact information.</label>

                        </div>
                    </a>
                                    <a href="new_ticket.php?a=add&amp;category=6" class="navlink ">
                        <div class="icon-in-circle">
                            <svg class="icon icon-chevron-right">
                                <use xlink:href="../img/sprite.svg#icon-chevron-right"></use>
                            </svg>
                        </div>
                        <div>
                            <h5 class="navlink__title"><!--[if IE]> &raquo; <![endif]-->HR Requisition</h5>

                            <label>Use this form to formally request approval to hire a new employee within their department or team.</label>

                        </div>
                    </a>
                                    <a href="new_ticket.php?a=add&amp;category=7" class="navlink ">
                        <div class="icon-in-circle">
                            <svg class="icon icon-chevron-right">
                                <use xlink:href="../img/sprite.svg#icon-chevron-right"></use>
                            </svg>
                        </div>
                        <div>
                            <h5 class="navlink__title"><!--[if IE]> &raquo; <![endif]-->Exit Interview</h5>

                            <label>This is description for this category.</label>

                        </div>
                    </a>
                                    <a href="new_ticket.php?a=add&amp;category=8" class="navlink ">
                        <div class="icon-in-circle">
                            <svg class="icon icon-chevron-right">
                                <use xlink:href="../img/sprite.svg#icon-chevron-right"></use>
                            </svg>
                        </div>
                        <div>
                            <h5 class="navlink__title"><!--[if IE]> &raquo; <![endif]-->Name Tag Request</h5>

                            <label>Use this from to submit name and other relevant details</label>

                        </div>
                    </a>
                            </div>
            </div>  
/*   http://localhost:8100/dashboard/tickets/new/2  */

/*   http://localhost:8100/dashboard/tickets/new/3  */
<div class="main__content categories ticket-create">


    <div class="table-wrap">

            <div class="main__content notice-flash ">
        <div class="notification blue">
            <b> </b> Use the capital expenditure form for long-term asset purchases. Examples include property, equipment, software, patents, and intangible assets.        </div>
    </div>
	       <!--  <div class="notification red"> 
  <b>Note:</b> Deanna is on vacation from June 20 – July 7, 2025. She will not approve any requests until she returns.
</div>  -->
        <h3 style="font-size: 1.3rem; margin-top: 10px">Insert a new request</h3>
        <h4>Required fields are marked with <span class="important">*</span></h4>

        <form method="post" class="form was-validated  " action="admin_submit_ticket.php" name="form1" id="submit-ticket" enctype="multipart/form-data">

                            <div class="form-group" style="margin-bottom: 0px;">
                    <label for="create_name" style="display: inline;">
                        Category:
                    </label>
                    &nbsp; 
                    <button type="button" class="btn btn--blue-border change_category" name="cc-btn" id="cc-btn" title="Click to change request category" fdprocessedid="llbdjg">Capital Expenditure                        &nbsp;
                        <svg class="icon icon-edit">
                            <use xlink:href="../img/sprite.svg#icon-edit"></use>
                        </svg>
                    </button>
                    <input type="hidden" name="change_category" id="change_category" value="3">
                    <script>
                        $("#cc-btn").click(function() {
                            $("#change_category").val(1);
                            $("#submit-ticket").submit();
                        });
                    </script>
                </div>
                                        <div class="main-group">
                    <div class="form-group">

                        <label for="create_name">
                            Name: <span class="important">*</span>
                        </label>
                        <input type="text" id="create_name" name="name" class="form-control " maxlength="50" value="Admin" readonly="" fdprocessedid="y3cq8">


                        <!--<label for="create_dealership">
                    Dealership: <span class="important">*</span>
                </label>
                <input type="text" id="create_dealership" name="dealership" class="form-control " maxlength="50" value="">
			</div>-->
                    </div>
                                        <div class="form-group">
                        <label for="email">
                            Email: <span class="important">*</span>                        </label>
                        <input type="email" class="form-control " name="email" id="email" readonly="" maxlength="1000" value="asharma@blackrockdigital.agency" onblur="Javascript:hesk_suggestEmail('email', 'email_suggestions', 1, 1)" fdprocessedid="g6ekib">
                    </div>

                    <div id="email_suggestions"></div>
                </div>
                <div class="main-group">
                    <div class="form-group">
                        <label class="priority ">Priority: </label>
                        <div class="dropdown-select center out-close priority" data-value="3">
                            <select name="priority">
                                                                <option value="3" selected="">Low</option>
                                <option value="2">Medium</option>
                                <option value="1">High</option>
                              
                            </select>
                        <div class="label"><span>Low</span><svg class="icon icon-chevron-down"><use xlink:href="../img/sprite.svg#icon-chevron-down"></use></svg></div><ul class="dropdown-list"><li data-option="3" class="selected">Low</li><li data-option="2">Medium</li><li data-option="1">High</li></ul></div>
                    </div>
                    <div class="form-group ts left-alig" style="margin-left: 60px !important;" id="ticket-status-div">
                        <label>Status:</label>
                        <div class="dropdown-select center out-close" data-value="0">
                            <select id="status-select" name="status" onchange="do {
                                                                            hesk_update_status_color
                                                                                } while (
                                                                                do {
                                                                                    condition
                                                                                } while (condition);
                                                                                );(this.value)">
                                <option value="0" selected="">New</option><option value="1">Waiting reply</option><option value="5">On Hold</option><option value="6">Canceled</option><option value="7">Approved</option><option value="3">Resolved</option>                            </select>
                        <div class="label"><span class="open">New</span><svg class="icon icon-chevron-down"><use xlink:href="../img/sprite.svg#icon-chevron-down"></use></svg></div><ul class="dropdown-list"><li data-option="0" class="selected open">New</li><li data-option="1" class="waitingreply">Waiting reply</li><li data-option="5" class="onhold">On Hold</li><li data-option="6" style="color: rgb(0, 0, 0);">Canceled</li><li data-option="7" style="color: rgb(95, 20, 255);">Approved</li><li data-option="3" class="resolved">Resolved</li></ul></div>
                    </div>
                    					  
                </div>
                
                       
               
           
            <!-- START CUSTOM BEFORE -->
            			
            			
            <div class="form-group" style="width:50%">
                                    <label>Dealership: <span class="important">*</span></label>
                                    <input readonly="" class="form-control " type="text" name="custom1" size="40" maxlength="255" value="" fdprocessedid="gt7jem">
                                </div><div class="form-group" style="width:50%">
                                    <label>Department: <span class="important">*</span></label>
                                    <input readonly="" class="form-control " type="text" name="custom2" size="40" maxlength="255" value="" fdprocessedid="1b51a">
                                </div>
                                <section class="param calendar">
                                    <label class="">Tentative Required Date: <span class="important">*</span></label>
                                    <div class="calendar--button">
                                        <button type="button" fdprocessedid="nbqex6">
                                            <svg class="icon icon-calendar">
                                                <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                                            </svg>
                                        </button>
                                        <input name="custom3" id="custom3" value="" type="text" class="datepicker">
                                    </div>
                                    <div class="calendar--value">
                                        <span class=""><i></i></span>
                                        <i class="close">
                                            <svg class="icon icon-close">
                                                <use xlink:href="../img/sprite.svg#icon-close"></use>
                                            </svg>
                                        </i>
                                    </div>
                                </section><div class="form-group" style="width:50%">
                                    <label>Department Manager: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom7" size="40" maxlength="255" value="" fdprocessedid="63fp7">
                                </div>
                                <div class="form-group ">
                                    <label>Replacement Equipment: <span class="important">*</span></label>
                                    <div class="radio-list">
                                            <div class="radio-custom" style="margin-bottom: 5px">
                                                <input type="radio" onclick="show_attachment('ReplacementEquipment','Yes')" id="edit_custom80" name="custom8" value="Yes" checked="">
                                                <label for="edit_custom80">Yes</label>
                                            </div>
                                            <div class="radio-custom" style="margin-bottom: 5px">
                                                <input type="radio" onclick="show_attachment('ReplacementEquipment','No')" id="edit_custom81" name="custom8" value="No">
                                                <label for="edit_custom81">No</label>
                                            </div><div id="ReplacementEquipment">
							<input type="file" name="file_ReplacementEquipment" id="">
							</div></div>
                                </div>
                                <div class="form-group ">
                                    <label>Revenue Generating: <span class="important">*</span></label>
                                    <div class="radio-list">
                                            <div class="radio-custom" style="margin-bottom: 5px">
                                                <input type="radio" onclick="show_attachment('RevenueGenerating','Yes')" id="edit_custom90" name="custom9" value="Yes" checked="">
                                                <label for="edit_custom90">Yes</label>
                                            </div>
                                            <div class="radio-custom" style="margin-bottom: 5px">
                                                <input type="radio" onclick="show_attachment('RevenueGenerating','No')" id="edit_custom91" name="custom9" value="No">
                                                <label for="edit_custom91">No</label>
                                            </div><div id="RevenueGenerating">
							<input type="file" name="file_RevenueGenerating" id="">
							</div></div>
                                </div>
                                <div class="form-group ">
                                    <label>If recommended for lease rather than purchase: <span class="important">*</span></label>
                                    <div class="radio-list">
                                            <div class="radio-custom" style="margin-bottom: 5px">
                                                <input type="radio" onclick="show_attachment('Ifrecommendedforleaseratherthanpurchase','Yes')" id="edit_custom100" name="custom10" value="Yes" checked="">
                                                <label for="edit_custom100">Yes</label>
                                            </div>
                                            <div class="radio-custom" style="margin-bottom: 5px">
                                                <input type="radio" onclick="show_attachment('Ifrecommendedforleaseratherthanpurchase','No')" id="edit_custom101" name="custom10" value="No">
                                                <label for="edit_custom101">No</label>
                                            </div><div id="Ifrecommendedforleaseratherthanpurchase">
							<input type="file" name="file_Ifrecommendedforleaseratherthanpurchase" id="">
							</div></div>
                                </div><div class="form-group" style="width:50%">
                                    <label>BRIEF PROJECT DESCRIPTION AND JUSTIFICATION: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom11" size="40" maxlength="255" value="" fdprocessedid="6befln">
                                </div><div class="form-group" style="width:50%">
                                    <label>EFFECT OF OPERATIONS: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom12" size="40" maxlength="255" value="" fdprocessedid="w7wqi8">
                                </div><div class="form-group" style="width:50%">
                                    <label>Product: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom13" size="40" maxlength="255" value="" fdprocessedid="00vkjl">
                                </div><div class="form-group" style="width:50%">
                                    <label>Vendor Name: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom14" size="40" maxlength="255" value="" fdprocessedid="sglbk8">
                                </div><div class="form-group" style="width:50%">
                                    <label>Model(#): <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom15" size="40" maxlength="255" value="" fdprocessedid="8sf7zk">
                                </div><div class="form-group" style="width:50%">
                                    <label>Price(Incl HST): <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom16" size="40" maxlength="255" value="" fdprocessedid="r3vadd">
                                </div>            <!-- END CUSTOM BEFORE -->
			    <div class="form-group">
        <label for="capex_person"><b>Person this needs to go to:</b></label>
        <input type="text" name="capex_person" class="form-control" value="" fdprocessedid="do78m6">
    </div>
    
    <div class="form-group">
        <label><b>Is this a replacement?</b></label>
        <div class="radio-list">
            <div class="radio-custom">
                <input type="radio" name="is_replacement" id="replacement_yes" value="1">
                <label for="replacement_yes">Yes</label>
            </div>
            <div class="radio-custom">
                <input type="radio" name="is_replacement" id="replacement_no" value="0" checked="">
                <label for="replacement_no">No</label>
            </div>
        </div>
    </div>
    
    <div id="replacement_fields" style="display:none;">
        <div class="form-group">
            <label for="replacement_person"><b>Person assigned to this equipment:</b></label>
            <input type="text" name="replacement_person" class="form-control" value="">
        </div>
        
        <div class="form-group">
            <label for="asset_tag"><b>Asset Tag Number:</b></label>
            <input type="text" name="asset_tag" class="form-control" value="">
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const replacementRadios = document.querySelectorAll('input[name="is_replacement"]');
        
        replacementRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('replacement_fields').style.display = 
                    this.value === '1' ? 'block' : 'none';
            });
        });
    });
    </script>
            <!--
                        <div class="form-group">
                <label><a href="manage_ticket_templates.php" class="link">Manage request templates</a></label>
            </div>
        
        
        

        <!--<div class="form-group">
                <label>Subject: <span class="important">*</span></label>
                <span id="HeskSub"><input class="form-control " type="text" name="subject" id="subject" maxlength="70" value=""></span>
            </div>-->
        <div class="form-group">
                            <label>Message: </label>
                        <span id="HeskMsg">
                <textarea style="height: inherit" class="form-control " name="message" id="message" rows="12" cols="60"></textarea>
            </span>
        </div>
                <!-- END CUSTOM AFTER -->

                    <div class="attachments">
                <div class="block--attach">
                    <svg class="icon icon-attach">
                        <use xlink:href="../img/sprite.svg#icon-attach"></use>
                    </svg>
                    <div>
                                                    Attachments:
                                            </div>
                </div>
                <div class="dropzone dz-click-filedrop dz-clickable" id="filedrop">
        
    <div class="dz-default dz-message"><button class="dz-button" type="button" fdprocessedid="msrljs">Drag files here or click the 'Add File' button below to select files to upload.</button></div></div>
    <div class="btn btn-full fileinput-button filedropbutton-filedrop dz-click-filedrop dz-clickable">Add file</div><a class="link" href="../file_limits.php" target="_blank" onclick="Javascript:hesk_window('../file_limits.php',250,500);return false;">File upload limits</a><div id="attachment-holder-filedrop" class="hide"></div>
    <div id="previews" style="display:none">
        <div id="template" class="file-row">
            <!-- This is used as the file preview template -->
            <div class="attachment-row">
                <div class="name-size-delete">
                    <div class="name-size">
                        <div class="name">
                            <p class="name" data-dz-name=""></p>
                        </div>
                        <div class="size">
                            (<span data-dz-size=""></span>)
                        </div>
                    </div>
                    <div class="delete-button">
                        <svg class="icon icon-delete" data-dz-remove="">
                            <use xlink:href="../img/sprite.svg#icon-delete"></use>
                        </svg>
                    </div>
                </div>
                <div class="upload-progress">
                    <div style="border: 1px solid #d4d6e3; width: 100%; height: 19px">
                        <div style="font-size: 1px; height: 17px; width: 0px; border: none; background-color: green" data-dz-uploadprogress="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="error">
                <strong class="error text-danger" data-dz-errormessage=""></strong>
            </div>
        </div>
    </div><script>const HeskWithout = (list, rejectedItem) => list.filter((item) => item !== rejectedItem).map((item) => item);</script>
    <script>
    Dropzone.autoDiscover = false;
    var dropzonefiledrop = new Dropzone('#filedrop', {
        paramName: 'attachment',
        url: '../upload_attachment.php',
        parallelUploads: 20,
        maxFiles: 20,
        acceptedFiles: ".gif,.jpg,.png,.zip,.rar,.csv,.doc,.docx,.xls,.xlsx,.txt,.pdf",
        maxFilesize: 115, // MB
        dictDefaultMessage: "Drag files here or click the 'Add File' button below to select files to upload.",
        dictFallbackMessage: '',
        dictInvalidFileType: "Sorry, but the file type you tried to upload is not allowed.",
        dictResponseError: "An error occurred when trying to upload. Please try again later.",
        dictFileTooBig: "This attachment is larger than the max filesize permitted.",
        dictCancelUpload: "Cancel",
        dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
        dictRemoveFile: "Remove attachment",
        dictMaxFilesExceeded: "This file will not be uploaded becuase you have already uploaded the maximum number of files allowed.",
        previewTemplate: $('#previews').html(),
        clickable: '.dz-click-filedrop',
        uploadMultiple: false
    });
    
    dropzonefiledrop.on('success', function(file, response) {
        var jsonResponse = JSON.parse(response);
        // console.log(JSON.stringify(jsonResponse, null, 4));

        if(jsonResponse.hasOwnProperty('status') && jsonResponse['status'] == 'failure'){
            // Upload was OK, but something failed on the server-side
            dropzonefiledrop.emit('uploadprogress', file, 0);
            dropzonefiledrop.files = HeskWithout(dropzonefiledrop.files, file);
            dropzonefiledrop.options.error(file, jsonResponse['message']);
        } else {
            // The response will only be a JSON object holding the saved and real name
            outputAttachmentIdHolder(jsonResponse['file_key'], 'filedrop');

            // Add the database id to the file
            file['databaseResponse'] = jsonResponse['file_key'];
        }
    });
    dropzonefiledrop.on('addedfile', function() {
        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }

        $('.filedropbutton-filedrop').attr('disabled', disabled);
    });
    dropzonefiledrop.on('removedfile', function(file) {
        if (file.beingRetried) {
            return;
        }
    
        // Remove the attachment from the database and the filesystem.
        removeAttachment(filedrop, file['databaseResponse'], true);

        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }
        $('.filedropbutton-filedrop').attr('disabled', disabled);
        
        dropzonefiledrop.getRejectedFiles().forEach(function(file) {
            file.beingRetried = true;
            dropzonefiledrop.removeFile(file);
            file.status = undefined;
            file.accepted = undefined;
            file.beingRetried = false;
            dropzonefiledrop.addFile(file);
        });
    });
    dropzonefiledrop.on('queuecomplete', function() {
        $('input[type="submit"]').attr('disabled', false);
    });
    dropzonefiledrop.on('processing', function() {
        $('input[type="submit"]').attr('disabled', true);
    });
    dropzonefiledrop.on('uploadprogress', function(file, percentage) {
        $(file.previewTemplate).find('#percentage').text(percentage + '%');
    });
    dropzonefiledrop.on('error', function(file, message) {
        $(file.previewTemplate).addClass('alert-danger');
        
        var actualMessage = message.title + ': ' + message.message;
        if (!message.message) {
            actualMessage = message;
        }
        
        $(file.previewElement).addClass('dz-error').find('[data-dz-errormessage]').text(actualMessage);
    });
    </script>
                </div>
        
                    <!--<section class="param calendar">
                                <label>Due date:</label>
                <div class="calendar--button">
                    <button type="button">
                        <svg class="icon icon-calendar">
                            <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                        </svg>
                    </button>
                    <input name="due_date" id="due_date"
                           value=""
                           type="text" class="datepicker">
                </div>
                <div class="calendar--value" style="">
                <span></span>
                <i class="close">
                    <svg class="icon icon-close">
                        <use xlink:href="../img/sprite.svg#icon-close"></use>
                    </svg>
                </i>
                </div>
            </section>-->
            <br>
                                    <!-- <div class="form-group">
                    <label>Assign this request to:</label>
                        <select name="owner" id="owner-select" >
                            <option value="-1"> &gt; Unassigned &lt; </option>
                            <option value="321">Adam Still</option><option value="1">Admin</option><option value="213">Adnan Abouzeeni (Leamington GM)</option><option value="440">Afran Ahmed</option><option value="135">Ahmad</option><option value="922">Alban Burani</option><option value="341">Aleks Petrovic</option><option value="141">ALI</option><option value="942">Ali Zibai</option><option value="143">Allyssa</option><option value="133">Amber</option><option value="132">AMITESH</option><option value="103">Andrea</option><option value="107">ANDREA JIGA</option><option value="174">Andrew</option><option value="214">Anees Haq (WHITEOAK FORD &amp; LINCLON)</option><option value="191">Anthony</option><option value="512">Basem Jamil</option><option value="544">Beth</option><option value="148">BILL ECHLIN</option><option value="888">Billy Henninger</option><option value="115">BOB KNIGHT</option><option value="928">Bowen Tong</option><option value="97">Brad Hicks</option><option value="203">Brandon</option><option value="198">BRIAN</option><option value="528">Brian Berg</option><option value="750">Brittney Wilson</option><option value="351">Brooke Armstrong</option><option value="368">Calvin Damian</option><option value="296">Che Shaydak (Porsche Motor City)</option><option value="53">Christopher</option><option value="165">Cody</option><option value="63">Colleen Williams</option><option value="118">CYNTHIA</option><option value="179">Dan</option><option value="92">Dan Pellizzari</option><option value="215">Dan Spasojevic (Mississauga Chrysler)</option><option value="35">Daniel Dasilva (Midtown Honda)</option><option value="952">Darren</option><option value="112">dave</option><option value="69">David</option><option value="78">David</option><option value="96">David Lunetta</option><option value="235">Deanna Muir (Purchase)</option><option value="160">Dee</option><option value="114">DEREK GUILBEAULT</option><option value="342">Devon Kailer</option><option value="525">Diego Vitello</option><option value="332">Dilpreet Singh</option><option value="490">Dion</option><option value="481">Don Ottosen</option><option value="151">Doug</option><option value="91">Douglas Hilton</option><option value="109">DOYLE BRAZNELL</option><option value="946">Doyle Braznell</option><option value="82">Dwayne</option><option value="219">Elie Daher (Audi Rochesterhills)</option><option value="671">Elliot Brown’s</option><option value="308">Elvin</option><option value="367">Emily Sybert</option><option value="331">EMIR BALA</option><option value="286">Emp - Audi Rochestorhills</option><option value="270">Emp - Countryside Chrysler</option><option value="271">Emp - Eastway Toyota</option><option value="273">Emp - Leamington GM</option><option value="288">Emp - Lincoln of Windsor</option><option value="284">Emp - Management</option><option value="287">Emp - Masrati of Cleveland</option><option value="285">Emp - Mazda of Windsor</option><option value="280">Emp - Mercedes Benz north Olmsted</option><option value="268">Emp - Midtown Honda</option><option value="274">Emp - Mini Motor City</option><option value="282">Emp - Oakridge Ford</option><option value="276">Emp - Overseas Mercedes benz</option><option value="275">Emp - Overseas Motors BMW</option><option value="281">Emp - Performance Ford</option><option value="279">Emp - Porsche Cleveland</option><option value="277">Emp - Porsche Motor City</option><option value="283">Emp - Whiteoak Ford</option><option value="369">ERIN</option><option value="105">Ethan</option><option value="220">Frank Steffen (Cleveland Porsche)</option><option value="197">fred</option><option value="934">George Bakic</option><option value="364">George Zawany</option><option value="465">Gerardo Diaz</option><option value="221">Ghassan Gus (Overseas BMW)</option><option value="232">Hamoody Rafih</option><option value="910">Hanif Jiwani</option><option value="65">HANNAH</option><option value="111">Heather</option><option value="152">Heather</option><option value="155">Heaven</option><option value="326">Helena Rapiz</option><option value="188">Jacob</option><option value="463">JAILYN</option><option value="222">James Godfrey (Eastway Toyota)</option><option value="153">Jamie</option><option value="157">Jason</option><option value="715">Jawad Al-Hakim</option><option value="93">Jean-Paul chouinard</option><option value="399">Jean-Paul Chouinard</option><option value="189">Jeff</option><option value="250">Jennifer Howe</option><option value="945">Jerome B Hartford</option><option value="932">Jesse</option><option value="144">JESSE NASR</option><option value="164">Jessica</option><option value="940">JIHAD</option><option value="162">Jim</option><option value="455">Joe Kailer</option><option value="374">Joesph Claiborne</option><option value="209">John</option><option value="650">JOHN COCCA (JJ)</option><option value="394">Jonathon Welch</option><option value="113">JORDAN LESANSKY</option><option value="347">Joseph Gill</option><option value="130">Josh</option><option value="183">Justin Petrozzi</option><option value="122">KIRK HILLMAN</option><option value="159">Kristin Ott</option><option value="81">Kristy Clark</option><option value="948">LAURA HEBERT</option><option value="319">Lindsey Grego</option><option value="180">Lisa Warnes</option><option value="95">Lorraine Williams</option><option value="608">Lotisha Thompson-Ellis</option><option value="624">Louie Lisi</option><option value="766">Louis Palmeri</option><option value="195">Lucas</option><option value="46">Mackenzie</option><option value="190">MARCEL</option><option value="334">MARK</option><option value="182">Mark Jennings</option><option value="224">Mark Stevens (Oakridge Ford)</option><option value="116">MARTHIE RESENDES</option><option value="645">Mat Wajda</option><option value="186">Matt</option><option value="156">Matt Kutsner</option><option value="629">Matt Roeder</option><option value="240">Matt Youssef</option><option value="451">Max Careau</option><option value="669">MEIXIA  SONG</option><option value="517">Melani Joseph</option><option value="359">Melissa Rapiz</option><option value="131">Michael</option><option value="357">Michael Moncrieffe</option><option value="208">Michael Moore</option><option value="322">Mike Sinainos</option><option value="194">Moe</option><option value="196">MOE HAZIME</option><option value="125">Mounika</option><option value="149">Mounir</option><option value="234">Munawwar Ali</option><option value="100">Najib</option><option value="861">Nehad Syed</option><option value="617">NEXHAT   REKA</option><option value="227">Nicole langlois</option><option value="67">Oscar</option><option value="885">Pat Cubellis</option><option value="102">Patricia Ciampini</option><option value="94">Paul Cunningham</option><option value="301">Paul Twigg</option><option value="72">Pranali</option><option value="931">Reception</option><option value="163">RENEE</option><option value="207">Richard</option><option value="371">Robert W. Lucas</option><option value="632">Royston Peter</option><option value="911">Royston Peter</option><option value="106">russ</option><option value="376">Ryan Cooper</option><option value="790">RYAN KASPERCZAK</option><option value="919">Ryan Suche</option><option value="51">Sam</option><option value="50">Samantha</option><option value="774">Sara Dawson</option><option value="841">SAYF</option><option value="340">Sean Barbarotta</option><option value="48">Shauna Lines</option><option value="236">Sherri Holmes</option><option value="134">shila</option><option value="104">Shirley</option><option value="350">Stephen Pfaendtner</option><option value="618">Steve Cowell</option><option value="591">Steve Reid</option><option value="120">STEVE TOUPIN</option><option value="228">Surinder Singh (Windsor Mazda)</option><option value="123">TANYA FREDERICK</option><option value="297">Thomas Jeon</option><option value="206">Tiffany</option><option value="136">Tim</option><option value="943">Tim Hanna</option><option value="137">Timothy</option><option value="500">Tina Galarza</option><option value="193">Tom</option><option value="858">Tommy Tran</option><option value="894">Tyler</option><option value="746">Wes McGinnis</option><option value="128">yasmine</option><option value="233">Zeyad Rafih</option>                        </select>
                        <script>
                            $('#owner-select').selectize();
                        </script>
                </div>-->
        
        <!--<div class="form-group" style="margin-top: 20px">
                <label>:</label>
                <div class="checkbox-list">
                    <div class="checkbox-custom">
                        <input type="checkbox" id="create_notify1" name="notify" value="1" >
                        <label for="create_notify1"></label>
                    </div>
                                        <div class="checkbox-custom">
                        <input type="checkbox" id="create_show1" name="show" value="1" >
                        <label for="create_show1"></label>
                    </div>
                                    </div>
            </div>-->

                    <button type="submit" class="btn btn-full" fdprocessedid="da1mnf">Submit Request</button>
                <input type="hidden" name="token" value="a5e1fa86e3f0555c50bcc5414b48ca915b7b71b8">
        <input type="hidden" name="category" value="3">
        </form>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
    </div>
</div>
/*   http://localhost:8100/dashboard/tickets/new/3  */

/*  http://localhost:8100/dashboard/tickets/new/4 */


<div class="main__content categories ticket-create">


    <div class="table-wrap">

            <div class="main__content notice-flash ">
        <div class="notification blue">
            <b> </b> To request a business card, employees need to fill out a form with their name, job title, and contact information.        </div>
    </div>
	       <!--  <div class="notification red"> 
  <b>Note:</b> Deanna is on vacation from June 20 – July 7, 2025. She will not approve any requests until she returns.
</div>  -->
        <h3 style="font-size: 1.3rem; margin-top: 10px">Insert a new request</h3>
        <h4>Required fields are marked with <span class="important">*</span></h4>

        <form method="post" class="form was-validated  " action="admin_submit_ticket.php" name="form1" id="submit-ticket" enctype="multipart/form-data">

                            <div class="form-group" style="margin-bottom: 0px;">
                    <label for="create_name" style="display: inline;">
                        Category:
                    </label>
                    &nbsp; 
                    <button type="button" class="btn btn--blue-border change_category" name="cc-btn" id="cc-btn" title="Click to change request category" fdprocessedid="y81tke">Business Card Request                        &nbsp;
                        <svg class="icon icon-edit">
                            <use xlink:href="../img/sprite.svg#icon-edit"></use>
                        </svg>
                    </button>
                    <input type="hidden" name="change_category" id="change_category" value="4">
                    <script>
                        $("#cc-btn").click(function() {
                            $("#change_category").val(1);
                            $("#submit-ticket").submit();
                        });
                    </script>
                </div>
                                        <div class="main-group">
                    <div class="form-group">

                        <label for="create_name">
                            Name: <span class="important">*</span>
                        </label>
                        <input type="text" id="create_name" name="name" class="form-control " maxlength="50" value="Admin" readonly="" fdprocessedid="upiulm">


                        <!--<label for="create_dealership">
                    Dealership: <span class="important">*</span>
                </label>
                <input type="text" id="create_dealership" name="dealership" class="form-control " maxlength="50" value="">
			</div>-->
                    </div>
                                        <div class="form-group">
                        <label for="email">
                            Email: <span class="important">*</span>                        </label>
                        <input type="email" class="form-control " name="email" id="email" readonly="" maxlength="1000" value="asharma@blackrockdigital.agency" onblur="Javascript:hesk_suggestEmail('email', 'email_suggestions', 1, 1)" fdprocessedid="xtx7c">
                    </div>

                    <div id="email_suggestions"></div>
                </div>
                <div class="main-group">
                    <div class="form-group">
                        <label class="priority ">Priority: </label>
                        <div class="dropdown-select center out-close priority" data-value="3">
                            <select name="priority">
                                                                <option value="3" selected="">Low</option>
                                <option value="2">Medium</option>
                                <option value="1">High</option>
                              
                            </select>
                        <div class="label"><span>Low</span><svg class="icon icon-chevron-down"><use xlink:href="../img/sprite.svg#icon-chevron-down"></use></svg></div><ul class="dropdown-list"><li data-option="3" class="selected">Low</li><li data-option="2">Medium</li><li data-option="1">High</li></ul></div>
                    </div>
                    <div class="form-group ts left-alig" style="margin-left: 60px !important;" id="ticket-status-div">
                        <label>Status:</label>
                        <div class="dropdown-select center out-close" data-value="0">
                            <select id="status-select" name="status" onchange="do {
                                                                            hesk_update_status_color
                                                                                } while (
                                                                                do {
                                                                                    condition
                                                                                } while (condition);
                                                                                );(this.value)">
                                <option value="0" selected="">New</option><option value="1">Waiting reply</option><option value="5">On Hold</option><option value="6">Canceled</option><option value="7">Approved</option><option value="3">Resolved</option>                            </select>
                        <div class="label"><span class="open">New</span><svg class="icon icon-chevron-down"><use xlink:href="../img/sprite.svg#icon-chevron-down"></use></svg></div><ul class="dropdown-list"><li data-option="0" class="selected open">New</li><li data-option="1" class="waitingreply">Waiting reply</li><li data-option="5" class="onhold">On Hold</li><li data-option="6" style="color: rgb(0, 0, 0);">Canceled</li><li data-option="7" style="color: rgb(95, 20, 255);">Approved</li><li data-option="3" class="resolved">Resolved</li></ul></div>
                    </div>
                    					  
                </div>
                
                       
               
           
            <!-- START CUSTOM BEFORE -->
                      
                 <!-- <div class="main-group">
                <div class="form-group">
                    <label for="second_tag" style="margin-top: 21px;"> Manufacture :</label>
                    <label for="create_name">First Tag </label>
                    <input type="text" id="first_tag" name="first_tag" class="form-control " maxlength="50" value="">
                </div>
                <div class="form-group">
                    <label for="second_tag" style="margin-top: 44px;"></label>
                    <label for="second_tag" style="margin-top: 21px;">Second Tag<span class="important"></span></label>
                    <input type="text" class="form-control" name="second_tag" id="second_tag" maxlength="1000" value="">
                </div>

            </div> -->
            			
            			
            <div class="form-group" style="width:50%">
                                    <label>Dealership: <span class="important">*</span></label>
                                    <input readonly="" class="form-control " type="text" name="custom1" size="40" maxlength="255" value="" fdprocessedid="ojo7rx">
                                </div><div class="form-group" style="width:50%">
                                    <label>Department: <span class="important">*</span></label>
                                    <input readonly="" class="form-control " type="text" name="custom2" size="40" maxlength="255" value="" fdprocessedid="qg9koi">
                                </div>            <!-- END CUSTOM BEFORE -->
			            <!--
                        <div class="form-group">
                <label><a href="manage_ticket_templates.php" class="link">Manage request templates</a></label>
            </div>
        
        
                    <!-- Add more starts -->

            <!------ Include the above in your HEAD tag ---------->

            <div class="panel panel-default">
                <div class="panel-heading">Add Business Cards details</div>
                <div class="panel-body">

                    <div id="education_fields2">

                    </div>
                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="text" class="form-control " id="card_name" name="card_name[]" value="" placeholder="First Name" fdprocessedid="p905yj">
                        </div>
                    </div>

                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="text" class="form-control " id="lastname" name="lastname[]" value="" placeholder="Last Name" fdprocessedid="iywrm2">
                        </div>
                    </div>

                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="text" class="form-control " id="card_title" name="card_title[]" value="" placeholder="Title" fdprocessedid="lyp0rf">

                        </div>
                    </div>

                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="text" class="form-control " id="business_phone" name="business_phone[]" value="" placeholder="Business Phone" fdprocessedid="52efyb">
                        </div>
                    </div>



                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="text" class="form-control " id="card_email" name="card_email[]" value="" placeholder="Email" fdprocessedid="gpaqj8">
                        </div>
                    </div>

                    <div class="col-sm-3 nopadding">
                        <div class="form-group">
                            <input type="text" class="form-control " id="extension" name="extension[]" value="" placeholder="Extension" fdprocessedid="w7vm8x">
                        </div>
                    </div>



                    <div class="col-sm-3 nopadding">
                        <div class="form-group" style="display:flex;">
                            <input type="text" class="form-control " id="card_cell" name="card_cell[]" value="" placeholder="Cell Phone" fdprocessedid="9hoo8">
                            <div class="input-group-btn"><button class="btn btn-success" type="button" onclick="education_fields2();" fdprocessedid="o7jdo6"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button>
                            </div>
                        </div>
                    </div>


                    <div class="clear"></div>

                </div>
                <div class="panel-footer"><small>Press <span class="glyphicon glyphicon-plus gs"></span> to add another form field :)</small>, <small>Press <span class="glyphicon glyphicon-minus gs"></span> to remove form field :)</small></div>
            </div>
            <!-- Add more ends -->
        

        <!--<div class="form-group">
                <label>Subject: <span class="important">*</span></label>
                <span id="HeskSub"><input class="form-control " type="text" name="subject" id="subject" maxlength="70" value=""></span>
            </div>-->
        <div class="form-group">
                            <label>Message: </label>
                        <span id="HeskMsg">
                <textarea style="height: inherit" class="form-control " name="message" id="message" rows="12" cols="60"></textarea>
            </span>
        </div>
                <!-- END CUSTOM AFTER -->

                    <div class="attachments">
                <div class="block--attach">
                    <svg class="icon icon-attach">
                        <use xlink:href="../img/sprite.svg#icon-attach"></use>
                    </svg>
                    <div>
                                                    Attachments:
                                            </div>
                </div>
                <div class="dropzone dz-click-filedrop dz-clickable" id="filedrop">
        
    <div class="dz-default dz-message"><button class="dz-button" type="button" fdprocessedid="nwp3ra">Drag files here or click the 'Add File' button below to select files to upload.</button></div></div>
    <div class="btn btn-full fileinput-button filedropbutton-filedrop dz-click-filedrop dz-clickable">Add file</div><a class="link" href="../file_limits.php" target="_blank" onclick="Javascript:hesk_window('../file_limits.php',250,500);return false;">File upload limits</a><div id="attachment-holder-filedrop" class="hide"></div>
    <div id="previews" style="display:none">
        <div id="template" class="file-row">
            <!-- This is used as the file preview template -->
            <div class="attachment-row">
                <div class="name-size-delete">
                    <div class="name-size">
                        <div class="name">
                            <p class="name" data-dz-name=""></p>
                        </div>
                        <div class="size">
                            (<span data-dz-size=""></span>)
                        </div>
                    </div>
                    <div class="delete-button">
                        <svg class="icon icon-delete" data-dz-remove="">
                            <use xlink:href="../img/sprite.svg#icon-delete"></use>
                        </svg>
                    </div>
                </div>
                <div class="upload-progress">
                    <div style="border: 1px solid #d4d6e3; width: 100%; height: 19px">
                        <div style="font-size: 1px; height: 17px; width: 0px; border: none; background-color: green" data-dz-uploadprogress="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="error">
                <strong class="error text-danger" data-dz-errormessage=""></strong>
            </div>
        </div>
    </div><script>const HeskWithout = (list, rejectedItem) => list.filter((item) => item !== rejectedItem).map((item) => item);</script>
    <script>
    Dropzone.autoDiscover = false;
    var dropzonefiledrop = new Dropzone('#filedrop', {
        paramName: 'attachment',
        url: '../upload_attachment.php',
        parallelUploads: 20,
        maxFiles: 20,
        acceptedFiles: ".gif,.jpg,.png,.zip,.rar,.csv,.doc,.docx,.xls,.xlsx,.txt,.pdf",
        maxFilesize: 115, // MB
        dictDefaultMessage: "Drag files here or click the 'Add File' button below to select files to upload.",
        dictFallbackMessage: '',
        dictInvalidFileType: "Sorry, but the file type you tried to upload is not allowed.",
        dictResponseError: "An error occurred when trying to upload. Please try again later.",
        dictFileTooBig: "This attachment is larger than the max filesize permitted.",
        dictCancelUpload: "Cancel",
        dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
        dictRemoveFile: "Remove attachment",
        dictMaxFilesExceeded: "This file will not be uploaded becuase you have already uploaded the maximum number of files allowed.",
        previewTemplate: $('#previews').html(),
        clickable: '.dz-click-filedrop',
        uploadMultiple: false
    });
    
    dropzonefiledrop.on('success', function(file, response) {
        var jsonResponse = JSON.parse(response);
        // console.log(JSON.stringify(jsonResponse, null, 4));

        if(jsonResponse.hasOwnProperty('status') && jsonResponse['status'] == 'failure'){
            // Upload was OK, but something failed on the server-side
            dropzonefiledrop.emit('uploadprogress', file, 0);
            dropzonefiledrop.files = HeskWithout(dropzonefiledrop.files, file);
            dropzonefiledrop.options.error(file, jsonResponse['message']);
        } else {
            // The response will only be a JSON object holding the saved and real name
            outputAttachmentIdHolder(jsonResponse['file_key'], 'filedrop');

            // Add the database id to the file
            file['databaseResponse'] = jsonResponse['file_key'];
        }
    });
    dropzonefiledrop.on('addedfile', function() {
        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }

        $('.filedropbutton-filedrop').attr('disabled', disabled);
    });
    dropzonefiledrop.on('removedfile', function(file) {
        if (file.beingRetried) {
            return;
        }
    
        // Remove the attachment from the database and the filesystem.
        removeAttachment(filedrop, file['databaseResponse'], true);

        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }
        $('.filedropbutton-filedrop').attr('disabled', disabled);
        
        dropzonefiledrop.getRejectedFiles().forEach(function(file) {
            file.beingRetried = true;
            dropzonefiledrop.removeFile(file);
            file.status = undefined;
            file.accepted = undefined;
            file.beingRetried = false;
            dropzonefiledrop.addFile(file);
        });
    });
    dropzonefiledrop.on('queuecomplete', function() {
        $('input[type="submit"]').attr('disabled', false);
    });
    dropzonefiledrop.on('processing', function() {
        $('input[type="submit"]').attr('disabled', true);
    });
    dropzonefiledrop.on('uploadprogress', function(file, percentage) {
        $(file.previewTemplate).find('#percentage').text(percentage + '%');
    });
    dropzonefiledrop.on('error', function(file, message) {
        $(file.previewTemplate).addClass('alert-danger');
        
        var actualMessage = message.title + ': ' + message.message;
        if (!message.message) {
            actualMessage = message;
        }
        
        $(file.previewElement).addClass('dz-error').find('[data-dz-errormessage]').text(actualMessage);
    });
    </script>
                </div>
        
                    <!--<section class="param calendar">
                                <label>Due date:</label>
                <div class="calendar--button">
                    <button type="button">
                        <svg class="icon icon-calendar">
                            <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                        </svg>
                    </button>
                    <input name="due_date" id="due_date"
                           value=""
                           type="text" class="datepicker">
                </div>
                <div class="calendar--value" style="">
                <span></span>
                <i class="close">
                    <svg class="icon icon-close">
                        <use xlink:href="../img/sprite.svg#icon-close"></use>
                    </svg>
                </i>
                </div>
            </section>-->
            <br>
                                    <!-- <div class="form-group">
                    <label>Assign this request to:</label>
                        <select name="owner" id="owner-select" >
                            <option value="-1"> &gt; Unassigned &lt; </option>
                            <option value="321">Adam Still</option><option value="1">Admin</option><option value="213">Adnan Abouzeeni (Leamington GM)</option><option value="440">Afran Ahmed</option><option value="135">Ahmad</option><option value="922">Alban Burani</option><option value="341">Aleks Petrovic</option><option value="141">ALI</option><option value="942">Ali Zibai</option><option value="143">Allyssa</option><option value="133">Amber</option><option value="132">AMITESH</option><option value="103">Andrea</option><option value="107">ANDREA JIGA</option><option value="174">Andrew</option><option value="214">Anees Haq (WHITEOAK FORD &amp; LINCLON)</option><option value="191">Anthony</option><option value="544">Beth</option><option value="148">BILL ECHLIN</option><option value="888">Billy Henninger</option><option value="115">BOB KNIGHT</option><option value="928">Bowen Tong</option><option value="97">Brad Hicks</option><option value="203">Brandon</option><option value="198">BRIAN</option><option value="528">Brian Berg</option><option value="750">Brittney Wilson</option><option value="351">Brooke Armstrong</option><option value="368">Calvin Damian</option><option value="296">Che Shaydak (Porsche Motor City)</option><option value="53">Christopher</option><option value="165">Cody</option><option value="63">Colleen Williams</option><option value="118">CYNTHIA</option><option value="179">Dan</option><option value="92">Dan Pellizzari</option><option value="215">Dan Spasojevic (Mississauga Chrysler)</option><option value="35">Daniel Dasilva (Midtown Honda)</option><option value="952">Darren</option><option value="112">dave</option><option value="461">Dave Hartley</option><option value="69">David</option><option value="78">David</option><option value="96">David Lunetta</option><option value="235">Deanna Muir (Purchase)</option><option value="160">Dee</option><option value="114">DEREK GUILBEAULT</option><option value="342">Devon Kailer</option><option value="525">Diego Vitello</option><option value="332">Dilpreet Singh</option><option value="490">Dion</option><option value="481">Don Ottosen</option><option value="151">Doug</option><option value="91">Douglas Hilton</option><option value="109">DOYLE BRAZNELL</option><option value="946">Doyle Braznell</option><option value="82">Dwayne</option><option value="219">Elie Daher (Audi Rochesterhills)</option><option value="671">Elliot Brown’s</option><option value="308">Elvin</option><option value="367">Emily Sybert</option><option value="331">EMIR BALA</option><option value="286">Emp - Audi Rochestorhills</option><option value="270">Emp - Countryside Chrysler</option><option value="271">Emp - Eastway Toyota</option><option value="273">Emp - Leamington GM</option><option value="288">Emp - Lincoln of Windsor</option><option value="284">Emp - Management</option><option value="287">Emp - Masrati of Cleveland</option><option value="285">Emp - Mazda of Windsor</option><option value="280">Emp - Mercedes Benz north Olmsted</option><option value="268">Emp - Midtown Honda</option><option value="274">Emp - Mini Motor City</option><option value="282">Emp - Oakridge Ford</option><option value="276">Emp - Overseas Mercedes benz</option><option value="275">Emp - Overseas Motors BMW</option><option value="281">Emp - Performance Ford</option><option value="279">Emp - Porsche Cleveland</option><option value="277">Emp - Porsche Motor City</option><option value="283">Emp - Whiteoak Ford</option><option value="369">ERIN</option><option value="105">Ethan</option><option value="220">Frank Steffen (Cleveland Porsche)</option><option value="197">fred</option><option value="934">George Bakic</option><option value="364">George Zawany</option><option value="465">Gerardo Diaz</option><option value="221">Ghassan Gus (Overseas BMW)</option><option value="232">Hamoody Rafih</option><option value="910">Hanif Jiwani</option><option value="65">HANNAH</option><option value="111">Heather</option><option value="152">Heather</option><option value="155">Heaven</option><option value="326">Helena Rapiz</option><option value="188">Jacob</option><option value="463">JAILYN</option><option value="222">James Godfrey (Eastway Toyota)</option><option value="902">James Jenkins</option><option value="153">Jamie</option><option value="157">Jason</option><option value="715">Jawad Al-Hakim</option><option value="93">Jean-Paul chouinard</option><option value="399">Jean-Paul Chouinard</option><option value="189">Jeff</option><option value="250">Jennifer Howe</option><option value="945">Jerome B Hartford</option><option value="932">Jesse</option><option value="144">JESSE NASR</option><option value="164">Jessica</option><option value="940">JIHAD</option><option value="162">Jim</option><option value="455">Joe Kailer</option><option value="374">Joesph Claiborne</option><option value="209">John</option><option value="650">JOHN COCCA (JJ)</option><option value="394">Jonathon Welch</option><option value="113">JORDAN LESANSKY</option><option value="347">Joseph Gill</option><option value="130">Josh</option><option value="183">Justin Petrozzi</option><option value="729">Kayla Khattar</option><option value="122">KIRK HILLMAN</option><option value="159">Kristin Ott</option><option value="370">Kristin Yancy</option><option value="81">Kristy Clark</option><option value="948">LAURA HEBERT</option><option value="319">Lindsey Grego</option><option value="180">Lisa Warnes</option><option value="95">Lorraine Williams</option><option value="608">Lotisha Thompson-Ellis</option><option value="624">Louie Lisi</option><option value="766">Louis Palmeri</option><option value="195">Lucas</option><option value="46">Mackenzie</option><option value="190">MARCEL</option><option value="904">Maria Agustin</option><option value="334">MARK</option><option value="182">Mark Jennings</option><option value="224">Mark Stevens (Oakridge Ford)</option><option value="116">MARTHIE RESENDES</option><option value="645">Mat Wajda</option><option value="186">Matt</option><option value="156">Matt Kutsner</option><option value="629">Matt Roeder</option><option value="240">Matt Youssef</option><option value="451">Max Careau</option><option value="669">MEIXIA  SONG</option><option value="517">Melani Joseph</option><option value="359">Melissa Rapiz</option><option value="131">Michael</option><option value="357">Michael Moncrieffe</option><option value="208">Michael Moore</option><option value="565">Mike Diamond</option><option value="322">Mike Sinainos</option><option value="194">Moe</option><option value="196">MOE HAZIME</option><option value="125">Mounika</option><option value="149">Mounir</option><option value="234">Munawwar Ali</option><option value="100">Najib</option><option value="861">Nehad Syed</option><option value="617">NEXHAT   REKA</option><option value="227">Nicole langlois</option><option value="67">Oscar</option><option value="885">Pat Cubellis</option><option value="102">Patricia Ciampini</option><option value="94">Paul Cunningham</option><option value="301">Paul Twigg</option><option value="72">Pranali</option><option value="931">Reception</option><option value="163">RENEE</option><option value="207">Richard</option><option value="371">Robert W. Lucas</option><option value="632">Royston Peter</option><option value="911">Royston Peter</option><option value="106">russ</option><option value="643">RYAN ANDREW KASPERCZAK</option><option value="376">Ryan Cooper</option><option value="790">RYAN KASPERCZAK</option><option value="919">Ryan Suche</option><option value="51">Sam</option><option value="50">Samantha</option><option value="550">Sandra Cristina</option><option value="774">Sara Dawson</option><option value="841">SAYF</option><option value="36">Scott Story</option><option value="340">Sean Barbarotta</option><option value="48">Shauna Lines</option><option value="236">Sherri Holmes</option><option value="134">shila</option><option value="104">Shirley</option><option value="373">Steffan Degiovanni</option><option value="350">Stephen Pfaendtner</option><option value="618">Steve Cowell</option><option value="591">Steve Reid</option><option value="120">STEVE TOUPIN</option><option value="228">Surinder Singh (Windsor Mazda)</option><option value="123">TANYA FREDERICK</option><option value="297">Thomas Jeon</option><option value="206">Tiffany</option><option value="136">Tim</option><option value="943">Tim Hanna</option><option value="137">Timothy</option><option value="500">Tina Galarza</option><option value="193">Tom</option><option value="858">Tommy Tran</option><option value="894">Tyler</option><option value="746">Wes McGinnis</option><option value="128">yasmine</option><option value="233">Zeyad Rafih</option>                        </select>
                        <script>
                            $('#owner-select').selectize();
                        </script>
                </div>-->
        
        <!--<div class="form-group" style="margin-top: 20px">
                <label>:</label>
                <div class="checkbox-list">
                    <div class="checkbox-custom">
                        <input type="checkbox" id="create_notify1" name="notify" value="1" >
                        <label for="create_notify1"></label>
                    </div>
                                        <div class="checkbox-custom">
                        <input type="checkbox" id="create_show1" name="show" value="1" >
                        <label for="create_show1"></label>
                    </div>
                                    </div>
            </div>-->

                    <button type="submit" class="btn btn-full" fdprocessedid="pvmkp">Submit Request</button>
                <input type="hidden" name="token" value="a5e1fa86e3f0555c50bcc5414b48ca915b7b71b8">
        <input type="hidden" name="category" value="4">
        </form>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
    </div>
</div>
/*  http://localhost:8100/dashboard/tickets/new/4  */

/*  http://localhost:8100/dashboard/tickets/new/6 */
<div class="main__content categories ticket-create">


    <div class="table-wrap">

            <div class="main__content notice-flash ">
        <div class="notification blue">
            <b> </b> Use this form to formally request approval to hire a new employee within their department or team.        </div>
    </div>
	       <!--  <div class="notification red"> 
  <b>Note:</b> Deanna is on vacation from June 20 – July 7, 2025. She will not approve any requests until she returns.
</div>  -->
        <h3 style="font-size: 1.3rem; margin-top: 10px">Insert a new request</h3>
        <h4>Required fields are marked with <span class="important">*</span></h4>

        <form method="post" class="form was-validated  " action="admin_submit_ticket.php" name="form1" id="submit-ticket" enctype="multipart/form-data">

                            <div class="form-group" style="margin-bottom: 0px;">
                    <label for="create_name" style="display: inline;">
                        Category:
                    </label>
                    &nbsp; 
                    <button type="button" class="btn btn--blue-border change_category" name="cc-btn" id="cc-btn" title="Click to change request category" fdprocessedid="4crg4">HR Requisition                        &nbsp;
                        <svg class="icon icon-edit">
                            <use xlink:href="../img/sprite.svg#icon-edit"></use>
                        </svg>
                    </button>
                    <input type="hidden" name="change_category" id="change_category" value="6">
                    <script>
                        $("#cc-btn").click(function() {
                            $("#change_category").val(1);
                            $("#submit-ticket").submit();
                        });
                    </script>
                </div>
                                        <div class="main-group">
                    <div class="form-group">

                        <label for="create_name">
                            Name: <span class="important">*</span>
                        </label>
                        <input type="text" id="create_name" name="name" class="form-control " maxlength="50" value="Admin" readonly="" fdprocessedid="aiina">


                        <!--<label for="create_dealership">
                    Dealership: <span class="important">*</span>
                </label>
                <input type="text" id="create_dealership" name="dealership" class="form-control " maxlength="50" value="">
			</div>-->
                    </div>
                                        <div class="form-group">
                        <label for="email">
                            Email: <span class="important">*</span>                        </label>
                        <input type="email" class="form-control " name="email" id="email" readonly="" maxlength="1000" value="asharma@blackrockdigital.agency" onblur="Javascript:hesk_suggestEmail('email', 'email_suggestions', 1, 1)" fdprocessedid="9gu90c">
                    </div>

                    <div id="email_suggestions"></div>
                </div>
                <div class="main-group">
                    <div class="form-group">
                        <label class="priority ">Priority: </label>
                        <div class="dropdown-select center out-close priority" data-value="3">
                            <select name="priority">
                                                                <option value="3" selected="">Low</option>
                                <option value="2">Medium</option>
                                <option value="1">High</option>
                              
                            </select>
                        <div class="label"><span>Low</span><svg class="icon icon-chevron-down"><use xlink:href="../img/sprite.svg#icon-chevron-down"></use></svg></div><ul class="dropdown-list"><li data-option="3" class="selected">Low</li><li data-option="2">Medium</li><li data-option="1">High</li></ul></div>
                    </div>
                    <div class="form-group ts left-alig" style="margin-left: 60px !important;" id="ticket-status-div">
                        <label>Status:</label>
                        <div class="dropdown-select center out-close" data-value="0">
                            <select id="status-select" name="status" onchange="do {
                                                                            hesk_update_status_color
                                                                                } while (
                                                                                do {
                                                                                    condition
                                                                                } while (condition);
                                                                                );(this.value)">
                                <option value="0" selected="">New</option><option value="1">Waiting reply</option><option value="5">On Hold</option><option value="6">Canceled</option><option value="7">Approved</option><option value="3">Resolved</option>                            </select>
                        <div class="label"><span class="open">New</span><svg class="icon icon-chevron-down"><use xlink:href="../img/sprite.svg#icon-chevron-down"></use></svg></div><ul class="dropdown-list"><li data-option="0" class="selected open">New</li><li data-option="1" class="waitingreply">Waiting reply</li><li data-option="5" class="onhold">On Hold</li><li data-option="6" style="color: rgb(0, 0, 0);">Canceled</li><li data-option="7" style="color: rgb(95, 20, 255);">Approved</li><li data-option="3" class="resolved">Resolved</li></ul></div>
                    </div>
                    					  
                </div>
                
                       
               
           
            <!-- START CUSTOM BEFORE -->
            			
            			
            <div class="form-group" style="width:50%"><label>Dealership: <span class="important">*</span></label><select name="custom1" class="form-control " fdprocessedid="qgs56"><option value="">Select Dealership</option><option value="Brampton Chrysler">Brampton Chrysler</option><option value="My Mississauga Chrysler">My Mississauga Chrysler</option><option value="Midtown Honda">Midtown Honda</option><option value="JLR London">JLR London</option><option value="Countryside Chrysler">Countryside Chrysler</option><option value="Eastway Toyota">Eastway Toyota</option><option value="JLR Windsor">JLR Windsor</option><option value="Leamington GM">Leamington GM</option><option value="Mini Motor City">Mini Motor City</option><option value="Overseas Motors BMW">Overseas Motors BMW</option><option value="Overseas Mercedes benz">Overseas Mercedes benz</option><option value="Porsche Motor City">Porsche Motor City</option><option value="BMW Rochester hills">BMW Rochester hills</option><option value="Porsche Cleveland">Porsche Cleveland</option><option value="Mercedes Benz north Olmsted">Mercedes Benz north Olmsted</option><option value="Performance Ford">Performance Ford</option><option value="Oakridge Ford">Oakridge Ford</option><option value="Whiteoak Ford">Whiteoak Ford</option><option value="Management">Management</option><option value="Mazda of Windsor">Mazda of Windsor</option><option value="Audi Rochesterhills">Audi Rochesterhills</option><option value="Masrati of Cleveland">Masrati of Cleveland</option><option value="Lincoln of Windsor">Lincoln of Windsor</option><option value="WhiteOak Ford">WhiteOak Ford</option><option value="Toyota of Warren">Toyota of Warren</option></select></div><div class="form-group" style="width:50%">
                                    <label>Department: <span class="important">*</span></label>
                                    <input readonly="" class="form-control " type="text" name="custom2" size="40" maxlength="255" value="" fdprocessedid="r8klvt">
                                </div><div class="form-group" style="width:50%">
                                    <label>Job Title: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom17" size="40" maxlength="255" value="" fdprocessedid="5tu3r">
                                </div>
                                <div class="form-group">
                                    <label for="edit_">Job Type: <span class="important">*</span></label>
                                        <select name="custom18" id="custom18" tabindex="-1" class="selectized" style="display: none;"><option value="Full Time" selected="selected">Full Time</option></select><div class="selectize-control single"><div class="selectize-input items full has-options has-items"><div class="item" data-value="Full Time">Full Time</div><input type="select-one" autocomplete="off" tabindex="" id="custom18-selectized" readonly="" fdprocessedid="hcndar" style="width: 4px;"></div><div class="selectize-dropdown single" style="display: none;"><div class="selectize-dropdown-content"></div></div></div>
                                </div>
                                <script>
                                    $('#custom18').selectize();
                                </script>
                                <div class="form-group" style="width:50%">
                                    <label>Reporting manager: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom19" size="40" maxlength="255" value="" fdprocessedid="amc3cw">
                                </div>
                                <section class="param calendar">
                                    <label class="">Anticipated start date: <span class="important">*</span></label>
                                    <div class="calendar--button">
                                        <button type="button" fdprocessedid="6scqm4">
                                            <svg class="icon icon-calendar">
                                                <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                                            </svg>
                                        </button>
                                        <input name="custom20" id="custom20" value="" type="text" class="datepicker">
                                    </div>
                                    <div class="calendar--value">
                                        <span class=""><i></i></span>
                                        <i class="close">
                                            <svg class="icon icon-close">
                                                <use xlink:href="../img/sprite.svg#icon-close"></use>
                                            </svg>
                                        </i>
                                    </div>
                                </section>
                                <div class="form-group">
                                    <label for="edit_">Position status: <span class="important">*</span></label>
                                        <select name="custom21" id="custom21" tabindex="-1" class="selectized" style="display: none;"><option value="New" selected="selected">New</option></select><div class="selectize-control single"><div class="selectize-input items full has-options has-items"><div class="item" data-value="New">New</div><input type="select-one" autocomplete="off" tabindex="" id="custom21-selectized" readonly="" fdprocessedid="tqsjar" style="width: 4px;"></div><div class="selectize-dropdown single" style="display: none;"><div class="selectize-dropdown-content"></div></div></div>
                                </div>
                                <script>
                                    $('#custom21').selectize();
                                </script>
                                <div class="form-group" style="width:50%">
                                    <label>Proposed Annual Compensation: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom22" size="40" maxlength="255" value="" fdprocessedid="pqmue">
                                </div>
                                <div class="form-group">
                                    <label for="edit_">Pay frequency: <span class="important">*</span></label>
                                        <select name="custom23" id="custom23" tabindex="-1" class="selectized" style="display: none;"><option value="Hourly" selected="selected">Hourly</option></select><div class="selectize-control single"><div class="selectize-input items full has-options has-items"><div class="item" data-value="Hourly">Hourly</div><input type="select-one" autocomplete="off" tabindex="" id="custom23-selectized" readonly="" fdprocessedid="d69ts6" style="width: 4px;"></div><div class="selectize-dropdown single" style="display: none;"><div class="selectize-dropdown-content"></div></div></div>
                                </div>
                                <script>
                                    $('#custom23').selectize();
                                </script>
                                
                                <div class="form-group">
                                    <label for="edit_">Domain: <span class="important">*</span></label>
                                        <select name="custom24" id="custom24" tabindex="-1" class="selectized" style="display: none;"><option value="Fixed ops" selected="selected">Fixed ops</option></select><div class="selectize-control single"><div class="selectize-input items full has-options has-items"><div class="item" data-value="Fixed ops">Fixed ops</div><input type="select-one" autocomplete="off" tabindex="" id="custom24-selectized" readonly="" fdprocessedid="11nx5" style="width: 4px;"></div><div class="selectize-dropdown single" style="display: none;"><div class="selectize-dropdown-content"></div></div></div>
                                </div>
                                <script>
                                    $('#custom24').selectize();
                                </script>
                                            <!-- END CUSTOM BEFORE -->
			            <!--
                        <div class="form-group">
                <label><a href="manage_ticket_templates.php" class="link">Manage request templates</a></label>
            </div>
        
        
        

        <!--<div class="form-group">
                <label>Subject: <span class="important">*</span></label>
                <span id="HeskSub"><input class="form-control " type="text" name="subject" id="subject" maxlength="70" value=""></span>
            </div>-->
        <div class="form-group">
                            <label>Comments/Feedback: </label>
                        <span id="HeskMsg">
                <textarea style="height: inherit" class="form-control " name="message" id="message" rows="12" cols="60"></textarea>
            </span>
        </div>
                <!-- END CUSTOM AFTER -->

                    <div class="attachments">
                <div class="block--attach">
                    <svg class="icon icon-attach">
                        <use xlink:href="../img/sprite.svg#icon-attach"></use>
                    </svg>
                    <div>
                                                    Resume, Predictive Index or Other Attachment                                            </div>
                </div>
                <div class="dropzone dz-click-filedrop dz-clickable" id="filedrop">
        
    <div class="dz-default dz-message"><button class="dz-button" type="button" fdprocessedid="i3edt">Drag files here or click the 'Add File' button below to select files to upload.</button></div></div>
    <div class="btn btn-full fileinput-button filedropbutton-filedrop dz-click-filedrop dz-clickable">Add file</div><a class="link" href="../file_limits.php" target="_blank" onclick="Javascript:hesk_window('../file_limits.php',250,500);return false;">File upload limits</a><div id="attachment-holder-filedrop" class="hide"></div>
    <div id="previews" style="display:none">
        <div id="template" class="file-row">
            <!-- This is used as the file preview template -->
            <div class="attachment-row">
                <div class="name-size-delete">
                    <div class="name-size">
                        <div class="name">
                            <p class="name" data-dz-name=""></p>
                        </div>
                        <div class="size">
                            (<span data-dz-size=""></span>)
                        </div>
                    </div>
                    <div class="delete-button">
                        <svg class="icon icon-delete" data-dz-remove="">
                            <use xlink:href="../img/sprite.svg#icon-delete"></use>
                        </svg>
                    </div>
                </div>
                <div class="upload-progress">
                    <div style="border: 1px solid #d4d6e3; width: 100%; height: 19px">
                        <div style="font-size: 1px; height: 17px; width: 0px; border: none; background-color: green" data-dz-uploadprogress="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="error">
                <strong class="error text-danger" data-dz-errormessage=""></strong>
            </div>
        </div>
    </div><script>const HeskWithout = (list, rejectedItem) => list.filter((item) => item !== rejectedItem).map((item) => item);</script>
    <script>
    Dropzone.autoDiscover = false;
    var dropzonefiledrop = new Dropzone('#filedrop', {
        paramName: 'attachment',
        url: '../upload_attachment.php',
        parallelUploads: 20,
        maxFiles: 20,
        acceptedFiles: ".gif,.jpg,.png,.zip,.rar,.csv,.doc,.docx,.xls,.xlsx,.txt,.pdf",
        maxFilesize: 115, // MB
        dictDefaultMessage: "Drag files here or click the 'Add File' button below to select files to upload.",
        dictFallbackMessage: '',
        dictInvalidFileType: "Sorry, but the file type you tried to upload is not allowed.",
        dictResponseError: "An error occurred when trying to upload. Please try again later.",
        dictFileTooBig: "This attachment is larger than the max filesize permitted.",
        dictCancelUpload: "Cancel",
        dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
        dictRemoveFile: "Remove attachment",
        dictMaxFilesExceeded: "This file will not be uploaded becuase you have already uploaded the maximum number of files allowed.",
        previewTemplate: $('#previews').html(),
        clickable: '.dz-click-filedrop',
        uploadMultiple: false
    });
    
    dropzonefiledrop.on('success', function(file, response) {
        var jsonResponse = JSON.parse(response);
        // console.log(JSON.stringify(jsonResponse, null, 4));

        if(jsonResponse.hasOwnProperty('status') && jsonResponse['status'] == 'failure'){
            // Upload was OK, but something failed on the server-side
            dropzonefiledrop.emit('uploadprogress', file, 0);
            dropzonefiledrop.files = HeskWithout(dropzonefiledrop.files, file);
            dropzonefiledrop.options.error(file, jsonResponse['message']);
        } else {
            // The response will only be a JSON object holding the saved and real name
            outputAttachmentIdHolder(jsonResponse['file_key'], 'filedrop');

            // Add the database id to the file
            file['databaseResponse'] = jsonResponse['file_key'];
        }
    });
    dropzonefiledrop.on('addedfile', function() {
        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }

        $('.filedropbutton-filedrop').attr('disabled', disabled);
    });
    dropzonefiledrop.on('removedfile', function(file) {
        if (file.beingRetried) {
            return;
        }
    
        // Remove the attachment from the database and the filesystem.
        removeAttachment(filedrop, file['databaseResponse'], true);

        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }
        $('.filedropbutton-filedrop').attr('disabled', disabled);
        
        dropzonefiledrop.getRejectedFiles().forEach(function(file) {
            file.beingRetried = true;
            dropzonefiledrop.removeFile(file);
            file.status = undefined;
            file.accepted = undefined;
            file.beingRetried = false;
            dropzonefiledrop.addFile(file);
        });
    });
    dropzonefiledrop.on('queuecomplete', function() {
        $('input[type="submit"]').attr('disabled', false);
    });
    dropzonefiledrop.on('processing', function() {
        $('input[type="submit"]').attr('disabled', true);
    });
    dropzonefiledrop.on('uploadprogress', function(file, percentage) {
        $(file.previewTemplate).find('#percentage').text(percentage + '%');
    });
    dropzonefiledrop.on('error', function(file, message) {
        $(file.previewTemplate).addClass('alert-danger');
        
        var actualMessage = message.title + ': ' + message.message;
        if (!message.message) {
            actualMessage = message;
        }
        
        $(file.previewElement).addClass('dz-error').find('[data-dz-errormessage]').text(actualMessage);
    });
    </script>
                </div>
        
                    <!--<section class="param calendar">
                                <label>Due date:</label>
                <div class="calendar--button">
                    <button type="button">
                        <svg class="icon icon-calendar">
                            <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                        </svg>
                    </button>
                    <input name="due_date" id="due_date"
                           value=""
                           type="text" class="datepicker">
                </div>
                <div class="calendar--value" style="">
                <span></span>
                <i class="close">
                    <svg class="icon icon-close">
                        <use xlink:href="../img/sprite.svg#icon-close"></use>
                    </svg>
                </i>
                </div>
            </section>-->
            <br>
                                    <!-- <div class="form-group">
                    <label>Assign this request to:</label>
                        <select name="owner" id="owner-select" >
                            <option value="-1"> &gt; Unassigned &lt; </option>
                            <option value="321">Adam Still</option><option value="1">Admin</option><option value="213">Adnan Abouzeeni (Leamington GM)</option><option value="135">Ahmad</option><option value="341">Aleks Petrovic</option><option value="141">ALI</option><option value="143">Allyssa</option><option value="133">Amber</option><option value="132">AMITESH</option><option value="103">Andrea</option><option value="107">ANDREA JIGA</option><option value="174">Andrew</option><option value="214">Anees Haq (WHITEOAK FORD &amp; LINCLON)</option><option value="495">Aninneman</option><option value="191">Anthony</option><option value="544">Beth</option><option value="148">BILL ECHLIN</option><option value="115">BOB KNIGHT</option><option value="97">Brad Hicks</option><option value="203">Brandon</option><option value="198">BRIAN</option><option value="351">Brooke Armstrong</option><option value="368">Calvin Damian</option><option value="296">Che Shaydak (Porsche Motor City)</option><option value="53">Christopher</option><option value="165">Cody</option><option value="63">Colleen Williams</option><option value="118">CYNTHIA</option><option value="179">Dan</option><option value="92">Dan Pellizzari</option><option value="215">Dan Spasojevic (Mississauga Chrysler)</option><option value="35">Daniel Dasilva (Midtown Honda)</option><option value="112">dave</option><option value="69">David</option><option value="78">David</option><option value="96">David Lunetta</option><option value="235">Deanna Muir (Purchase)</option><option value="160">Dee</option><option value="114">DEREK GUILBEAULT</option><option value="342">Devon Kailer</option><option value="525">Diego Vitello</option><option value="332">Dilpreet Singh</option><option value="490">Dion</option><option value="151">Doug</option><option value="91">Douglas Hilton</option><option value="109">DOYLE BRAZNELL</option><option value="82">Dwayne</option><option value="219">Elie Daher (Audi Rochesterhills)</option><option value="671">Elliot Brown’s</option><option value="308">Elvin</option><option value="367">Emily Sybert</option><option value="331">EMIR BALA</option><option value="286">Emp - Audi Rochestorhills</option><option value="270">Emp - Countryside Chrysler</option><option value="271">Emp - Eastway Toyota</option><option value="273">Emp - Leamington GM</option><option value="288">Emp - Lincoln of Windsor</option><option value="284">Emp - Management</option><option value="287">Emp - Masrati of Cleveland</option><option value="285">Emp - Mazda of Windsor</option><option value="280">Emp - Mercedes Benz north Olmsted</option><option value="268">Emp - Midtown Honda</option><option value="274">Emp - Mini Motor City</option><option value="282">Emp - Oakridge Ford</option><option value="276">Emp - Overseas Mercedes benz</option><option value="275">Emp - Overseas Motors BMW</option><option value="281">Emp - Performance Ford</option><option value="279">Emp - Porsche Cleveland</option><option value="277">Emp - Porsche Motor City</option><option value="283">Emp - Whiteoak Ford</option><option value="369">ERIN</option><option value="105">Ethan</option><option value="220">Frank Steffen (Cleveland Porsche)</option><option value="197">fred</option><option value="364">George Zawany</option><option value="221">Ghassan Gus (Overseas BMW)</option><option value="232">Hamoody Rafih</option><option value="65">HANNAH</option><option value="111">Heather</option><option value="152">Heather</option><option value="155">Heaven</option><option value="326">Helena Rapiz</option><option value="188">Jacob</option><option value="222">James Godfrey (Eastway Toyota)</option><option value="153">Jamie</option><option value="157">Jason</option><option value="715">Jawad Al-Hakim</option><option value="93">Jean-Paul chouinard</option><option value="399">Jean-Paul Chouinard</option><option value="189">Jeff</option><option value="250">Jennifer Howe</option><option value="144">JESSE NASR</option><option value="164">Jessica</option><option value="162">Jim</option><option value="455">Joe Kailer</option><option value="209">John</option><option value="650">JOHN COCCA (JJ)</option><option value="394">Jonathon Welch</option><option value="113">JORDAN LESANSKY</option><option value="347">Joseph Gill</option><option value="130">Josh</option><option value="183">Justin Petrozzi</option><option value="122">KIRK HILLMAN</option><option value="159">Kristin Ott</option><option value="81">Kristy Clark</option><option value="319">Lindsey Grego</option><option value="180">Lisa Warnes</option><option value="95">Lorraine Williams</option><option value="624">Louie Lisi</option><option value="766">Louis Palmeri</option><option value="195">Lucas</option><option value="46">Mackenzie</option><option value="190">MARCEL</option><option value="334">MARK</option><option value="182">Mark Jennings</option><option value="224">Mark Stevens (Oakridge Ford)</option><option value="116">MARTHIE RESENDES</option><option value="186">Matt</option><option value="156">Matt Kutsner</option><option value="240">Matt Youssef</option><option value="451">Max Careau</option><option value="669">MEIXIA  SONG</option><option value="359">Melissa Rapiz</option><option value="131">Michael</option><option value="357">Michael Moncrieffe</option><option value="208">Michael Moore</option><option value="322">Mike Sinainos</option><option value="194">Moe</option><option value="196">MOE HAZIME</option><option value="125">Mounika</option><option value="149">Mounir</option><option value="234">Munawwar Ali</option><option value="100">Najib</option><option value="617">NEXHAT   REKA</option><option value="227">Nicole langlois</option><option value="67">Oscar</option><option value="102">Patricia Ciampini</option><option value="94">Paul Cunningham</option><option value="301">Paul Twigg</option><option value="72">Pranali</option><option value="931">Reception</option><option value="163">RENEE</option><option value="207">Richard</option><option value="371">Robert W. Lucas</option><option value="632">Royston Peter</option><option value="106">russ</option><option value="376">Ryan Cooper</option><option value="51">Sam</option><option value="50">Samantha</option><option value="841">SAYF</option><option value="36">Scott Story</option><option value="340">Sean Barbarotta</option><option value="48">Shauna Lines</option><option value="236">Sherri Holmes</option><option value="134">shila</option><option value="104">Shirley</option><option value="373">Steffan Degiovanni</option><option value="350">Stephen Pfaendtner</option><option value="120">STEVE TOUPIN</option><option value="228">Surinder Singh (Windsor Mazda)</option><option value="123">TANYA FREDERICK</option><option value="297">Thomas Jeon</option><option value="206">Tiffany</option><option value="136">Tim</option><option value="137">Timothy</option><option value="193">Tom</option><option value="894">Tyler</option><option value="238">Vickie</option><option value="128">yasmine</option><option value="233">Zeyad Rafih</option>                        </select>
                        <script>
                            $('#owner-select').selectize();
                        </script>
                </div>-->
        
        <!--<div class="form-group" style="margin-top: 20px">
                <label>:</label>
                <div class="checkbox-list">
                    <div class="checkbox-custom">
                        <input type="checkbox" id="create_notify1" name="notify" value="1" >
                        <label for="create_notify1"></label>
                    </div>
                                        <div class="checkbox-custom">
                        <input type="checkbox" id="create_show1" name="show" value="1" >
                        <label for="create_show1"></label>
                    </div>
                                    </div>
            </div>-->

                    <button type="submit" class="btn btn-full" fdprocessedid="wz1w8t">Submit Request</button>
                <input type="hidden" name="token" value="a5e1fa86e3f0555c50bcc5414b48ca915b7b71b8">
        <input type="hidden" name="category" value="6">
        </form>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
    </div>
</div>

/* http://localhost:8100/dashboard/tickets/new/6 */


/*   http://localhost:8100/dashboard/tickets/new/7  */
<div class="main__content categories ticket-create">


    <div class="table-wrap">

            <div class="main__content notice-flash ">
        <div class="notification blue">
            <b> </b> This is description for this category.        </div>
    </div>
	       <!--  <div class="notification red"> 
  <b>Note:</b> Deanna is on vacation from June 20 – July 7, 2025. She will not approve any requests until she returns.
</div>  -->
        <h3 style="font-size: 1.3rem; margin-top: 10px">Insert a new request</h3>
        <h4>Required fields are marked with <span class="important">*</span></h4>

        <form method="post" class="form was-validated  " action="admin_submit_ticket.php" name="form1" id="submit-ticket" enctype="multipart/form-data">

                            <div class="form-group" style="margin-bottom: 0px;">
                    <label for="create_name" style="display: inline;">
                        Category:
                    </label>
                    &nbsp; 
                    <button type="button" class="btn btn--blue-border change_category" name="cc-btn" id="cc-btn" title="Click to change request category" fdprocessedid="mq721n">Exit Interview                        &nbsp;
                        <svg class="icon icon-edit">
                            <use xlink:href="../img/sprite.svg#icon-edit"></use>
                        </svg>
                    </button>
                    <input type="hidden" name="change_category" id="change_category" value="7">
                    <script>
                        $("#cc-btn").click(function() {
                            $("#change_category").val(1);
                            $("#submit-ticket").submit();
                        });
                    </script>
                </div>
                                   
               
           
            <!-- START CUSTOM BEFORE -->
            			
            			
            <div class="form-group" style="width:50%">
                                    <label>Employee Title: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom25" size="40" maxlength="5000" value="" fdprocessedid="z5xkq">
                                </div><div class="form-group" style="width:50%">
                                    <label>Employee Name: <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom27" size="40" maxlength="5000" value="" fdprocessedid="1s1bai">
                                </div>
                                <div class="form-group">
                                    <label for="edit_">Employee Department: <span class="important">*</span></label>
                                        <select name="custom28" id="custom28" tabindex="-1" class="selectized" style="display: none;"><option value="New Sales" selected="selected">New Sales</option></select><div class="selectize-control single"><div class="selectize-input items full has-options has-items"><div class="item" data-value="New Sales">New Sales</div><input type="select-one" autocomplete="off" tabindex="" id="custom28-selectized" readonly="" fdprocessedid="3tlx2" style="width: 4px;"></div><div class="selectize-dropdown single" style="display: none;"><div class="selectize-dropdown-content"></div></div></div>
                                </div>
                                <script>
                                    $('#custom28').selectize();
                                </script>
                                
                                <div class="form-group">
                                    <label for="edit_">Employee Dealership: <span class="important">*</span></label>
                                       <select name="custom29" id="custom29" tabindex="-1" class="selectized" style="display: none;"><option value="Brampton Chrysler" selected="selected">Brampton Chrysler</option><option value="Toyota of Warren">Toyota of Warren</option></select><div class="selectize-control single"><div class="selectize-input items full has-options has-items"><div class="item" data-value="Brampton Chrysler">Brampton Chrysler</div><input type="select-one" autocomplete="off" tabindex="" id="custom29-selectized" readonly="" fdprocessedid="kmwmsd" style="width: 4px;"></div><div class="selectize-dropdown single" style="display: none;"><div class="selectize-dropdown-content"></div></div></div>
									
		<!--							
	<select name="custom29" id="custom29" class="form-control">
    <option value="">Select Dealership</option>
    <option value="Midtown Honda">Midtown Honda</option>
    <option value="JLR London">JLR London</option>
    <option value="Countryside Chrysler">Countryside Chrysler</option>
    <option value="Eastway Toyota">Eastway Toyota</option>
    <option value="JLR Windsor">JLR Windsor</option>
    <option value="Leamington GM">Leamington GM</option>
    <option value="Mini Motor City">Mini Motor City</option>
    <option value="Overseas Motors BMW">Overseas Motors BMW</option>
    <option value="Overseas Mercedes benz">Overseas Mercedes benz</option>
    <option value="Porsche Motor City">Porsche Motor City</option>
    <option value="BMW Rochester hills">BMW Rochester hills</option>
    <option value="Porsche Cleveland">Porsche Cleveland</option>
    <option value="Mercedes Benz north Olmsted">Mercedes Benz north Olmsted</option>
    <option value="Performance Ford">Performance Ford</option>
    <option value="Oakridge Ford">Oakridge Ford</option>
    <option value="Whiteoak Ford">Whiteoak Ford</option>
    <option value="Management">Management</option>
    <option value="Mazda of Windsor">Mazda of Windsor</option>
    <option value="Audi Rochesterhills">Audi Rochesterhills</option>
    <option value="Masrati of Cleveland">Masrati of Cleveland</option>
    <option value="Lincoln of Windsor">Lincoln of Windsor</option>
    <option value="WhiteOak Ford">WhiteOak Ford</option>
    <option value="Audi">Audi</option>
    <option value="Toyota Of Warren">Toyota Of Warren</option>
    <option value="Brampton Chrysler">Brampton Chrysler</option>
    <option value="My Mississauga Chrysler">My Mississauga Chrysler</option>
</select>
-->
									
                                </div>
                                <script>
                                    $('#custom29').selectize();
                                </script>
                                
                                <section class="param calendar">
                                    <label class="">Date of Joining: <span class="important">*</span></label>
                                    <div class="calendar--button">
                                        <button type="button" fdprocessedid="j5at9t">
                                            <svg class="icon icon-calendar">
                                                <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                                            </svg>
                                        </button>
                                        <input name="custom30" id="custom30" value="" type="text" class="datepicker">
                                    </div>
                                    <div class="calendar--value">
                                        <span class=""><i></i></span>
                                        <i class="close">
                                            <svg class="icon icon-close">
                                                <use xlink:href="../img/sprite.svg#icon-close"></use>
                                            </svg>
                                        </i>
                                    </div>
                                </section>
                                <section class="param calendar">
                                    <label class="">Last Date of Work: <span class="important">*</span></label>
                                    <div class="calendar--button">
                                        <button type="button" fdprocessedid="s9u2ei">
                                            <svg class="icon icon-calendar">
                                                <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                                            </svg>
                                        </button>
                                        <input name="custom31" id="custom31" value="" type="text" class="datepicker">
                                    </div>
                                    <div class="calendar--value">
                                        <span class=""><i></i></span>
                                        <i class="close">
                                            <svg class="icon icon-close">
                                                <use xlink:href="../img/sprite.svg#icon-close"></use>
                                            </svg>
                                        </i>
                                    </div>
                                </section><div class="form-group" style="width:50%">
                                    <label>What are your reasons for leaving? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom32" size="40" maxlength="5000" value="" fdprocessedid="kgbu9">
                                </div><div class="form-group" style="width:50%">
                                    <label>What were your job duties and responsibilities? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom33" size="40" maxlength="5000" value="" fdprocessedid="uj8c">
                                </div><div class="form-group" style="width:50%">
                                    <label>Did your duties change or become unsavory throughout your employment? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom34" size="40" maxlength="5000" value="" fdprocessedid="zex4zi">
                                </div><div class="form-group" style="width:50%">
                                    <label>What did you like about your job? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom35" size="40" maxlength="5000" value="" fdprocessedid="ds7e59">
                                </div><div class="form-group" style="width:50%">
                                    <label>What did you dislike about your job? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom36" size="40" maxlength="5000" value="" fdprocessedid="5yyg8j">
                                </div><div class="form-group" style="width:50%">
                                    <label>Were you satisfied with your career progression? If not, why? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom37" size="40" maxlength="5000" value="" fdprocessedid="skd3h">
                                </div><div class="form-group" style="width:50%">
                                    <label>Were you satisfied with your salary/benefits? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom38" size="40" maxlength="5000" value="" fdprocessedid="6g3xn">
                                </div><div class="form-group" style="width:50%">
                                    <label>How would you describe your working relationship with your Manager? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom39" size="40" maxlength="5000" value="" fdprocessedid="7fbi0r">
                                </div><div class="form-group" style="width:50%">
                                    <label>How would you describe your working relationship with your co-workers? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom40" size="40" maxlength="5000" value="" fdprocessedid="6urwd">
                                </div><div class="form-group" style="width:50%">
                                    <label>Do you have any suggestions on how to improve working conditions here? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom41" size="40" maxlength="5000" value="" fdprocessedid="1kqv4s">
                                </div><div class="form-group" style="width:50%">
                                    <label>Do you have any other comments you wish to make? <span class="important">*</span></label>
                                    <input class="form-control " type="text" name="custom42" size="40" maxlength="5000" value="" fdprocessedid="95k4b4">
                                </div>            <!-- END CUSTOM BEFORE -->
			            <!--
                        <div class="form-group">
                <label><a href="manage_ticket_templates.php" class="link">Manage request templates</a></label>
            </div>
        
        
        

        <!--<div class="form-group">
                <label>Subject: <span class="important">*</span></label>
                <span id="HeskSub"><input class="form-control " type="text" name="subject" id="subject" maxlength="70" value=""></span>
            </div>-->
        <div class="form-group">
                            <label>Message: </label>
                        <span id="HeskMsg">
                <textarea style="height: inherit" class="form-control " name="message" id="message" rows="12" cols="60"></textarea>
            </span>
        </div>
                <!-- END CUSTOM AFTER -->

                    <div class="attachments">
                <div class="block--attach">
                    <svg class="icon icon-attach">
                        <use xlink:href="../img/sprite.svg#icon-attach"></use>
                    </svg>
                    <div>
                                                    Attachments:
                                            </div>
                </div>
                <div class="dropzone dz-click-filedrop dz-clickable" id="filedrop">
        
    <div class="dz-default dz-message"><button class="dz-button" type="button" fdprocessedid="f46y7">Drag files here or click the 'Add File' button below to select files to upload.</button></div></div>
    <div class="btn btn-full fileinput-button filedropbutton-filedrop dz-click-filedrop dz-clickable">Add file</div><a class="link" href="../file_limits.php" target="_blank" onclick="Javascript:hesk_window('../file_limits.php',250,500);return false;">File upload limits</a><div id="attachment-holder-filedrop" class="hide"></div>
    <div id="previews" style="display:none">
        <div id="template" class="file-row">
            <!-- This is used as the file preview template -->
            <div class="attachment-row">
                <div class="name-size-delete">
                    <div class="name-size">
                        <div class="name">
                            <p class="name" data-dz-name=""></p>
                        </div>
                        <div class="size">
                            (<span data-dz-size=""></span>)
                        </div>
                    </div>
                    <div class="delete-button">
                        <svg class="icon icon-delete" data-dz-remove="">
                            <use xlink:href="../img/sprite.svg#icon-delete"></use>
                        </svg>
                    </div>
                </div>
                <div class="upload-progress">
                    <div style="border: 1px solid #d4d6e3; width: 100%; height: 19px">
                        <div style="font-size: 1px; height: 17px; width: 0px; border: none; background-color: green" data-dz-uploadprogress="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="error">
                <strong class="error text-danger" data-dz-errormessage=""></strong>
            </div>
        </div>
    </div><script>const HeskWithout = (list, rejectedItem) => list.filter((item) => item !== rejectedItem).map((item) => item);</script>
    <script>
    Dropzone.autoDiscover = false;
    var dropzonefiledrop = new Dropzone('#filedrop', {
        paramName: 'attachment',
        url: '../upload_attachment.php',
        parallelUploads: 20,
        maxFiles: 20,
        acceptedFiles: ".gif,.jpg,.png,.zip,.rar,.csv,.doc,.docx,.xls,.xlsx,.txt,.pdf",
        maxFilesize: 115, // MB
        dictDefaultMessage: "Drag files here or click the 'Add File' button below to select files to upload.",
        dictFallbackMessage: '',
        dictInvalidFileType: "Sorry, but the file type you tried to upload is not allowed.",
        dictResponseError: "An error occurred when trying to upload. Please try again later.",
        dictFileTooBig: "This attachment is larger than the max filesize permitted.",
        dictCancelUpload: "Cancel",
        dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
        dictRemoveFile: "Remove attachment",
        dictMaxFilesExceeded: "This file will not be uploaded becuase you have already uploaded the maximum number of files allowed.",
        previewTemplate: $('#previews').html(),
        clickable: '.dz-click-filedrop',
        uploadMultiple: false
    });
    
    dropzonefiledrop.on('success', function(file, response) {
        var jsonResponse = JSON.parse(response);
        // console.log(JSON.stringify(jsonResponse, null, 4));

        if(jsonResponse.hasOwnProperty('status') && jsonResponse['status'] == 'failure'){
            // Upload was OK, but something failed on the server-side
            dropzonefiledrop.emit('uploadprogress', file, 0);
            dropzonefiledrop.files = HeskWithout(dropzonefiledrop.files, file);
            dropzonefiledrop.options.error(file, jsonResponse['message']);
        } else {
            // The response will only be a JSON object holding the saved and real name
            outputAttachmentIdHolder(jsonResponse['file_key'], 'filedrop');

            // Add the database id to the file
            file['databaseResponse'] = jsonResponse['file_key'];
        }
    });
    dropzonefiledrop.on('addedfile', function() {
        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }

        $('.filedropbutton-filedrop').attr('disabled', disabled);
    });
    dropzonefiledrop.on('removedfile', function(file) {
        if (file.beingRetried) {
            return;
        }
    
        // Remove the attachment from the database and the filesystem.
        removeAttachment(filedrop, file['databaseResponse'], true);

        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }
        $('.filedropbutton-filedrop').attr('disabled', disabled);
        
        dropzonefiledrop.getRejectedFiles().forEach(function(file) {
            file.beingRetried = true;
            dropzonefiledrop.removeFile(file);
            file.status = undefined;
            file.accepted = undefined;
            file.beingRetried = false;
            dropzonefiledrop.addFile(file);
        });
    });
    dropzonefiledrop.on('queuecomplete', function() {
        $('input[type="submit"]').attr('disabled', false);
    });
    dropzonefiledrop.on('processing', function() {
        $('input[type="submit"]').attr('disabled', true);
    });
    dropzonefiledrop.on('uploadprogress', function(file, percentage) {
        $(file.previewTemplate).find('#percentage').text(percentage + '%');
    });
    dropzonefiledrop.on('error', function(file, message) {
        $(file.previewTemplate).addClass('alert-danger');
        
        var actualMessage = message.title + ': ' + message.message;
        if (!message.message) {
            actualMessage = message;
        }
        
        $(file.previewElement).addClass('dz-error').find('[data-dz-errormessage]').text(actualMessage);
    });
    </script>
                </div>
        
                    <!--<section class="param calendar">
                                <label>Due date:</label>
                <div class="calendar--button">
                    <button type="button">
                        <svg class="icon icon-calendar">
                            <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                        </svg>
                    </button>
                    <input name="due_date" id="due_date"
                           value=""
                           type="text" class="datepicker">
                </div>
                <div class="calendar--value" style="">
                <span></span>
                <i class="close">
                    <svg class="icon icon-close">
                        <use xlink:href="../img/sprite.svg#icon-close"></use>
                    </svg>
                </i>
                </div>
            </section>-->
            <br>
                                    <!-- <div class="form-group">
                    <label>Assign this request to:</label>
                        <select name="owner" id="owner-select" >
                            <option value="-1"> &gt; Unassigned &lt; </option>
                            <option value="321">Adam Still</option><option value="1">Admin</option><option value="213">Adnan Abouzeeni (Leamington GM)</option><option value="135">Ahmad</option><option value="141">ALI</option><option value="143">Allyssa</option><option value="133">Amber</option><option value="132">AMITESH</option><option value="103">Andrea</option><option value="107">ANDREA JIGA</option><option value="174">Andrew</option><option value="214">Anees Haq (WHITEOAK FORD &amp; LINCLON)</option><option value="191">Anthony</option><option value="544">Beth</option><option value="148">BILL ECHLIN</option><option value="115">BOB KNIGHT</option><option value="97">Brad Hicks</option><option value="203">Brandon</option><option value="198">BRIAN</option><option value="296">Che Shaydak (Porsche Motor City)</option><option value="53">Christopher</option><option value="165">Cody</option><option value="63">Colleen Williams</option><option value="118">CYNTHIA</option><option value="179">Dan</option><option value="92">Dan Pellizzari</option><option value="215">Dan Spasojevic (Mississauga Chrysler)</option><option value="35">Daniel Dasilva (Midtown Honda)</option><option value="112">dave</option><option value="69">David</option><option value="78">David</option><option value="235">Deanna Muir (Purchase)</option><option value="160">Dee</option><option value="114">DEREK GUILBEAULT</option><option value="332">Dilpreet Singh</option><option value="490">Dion</option><option value="151">Doug</option><option value="91">Douglas Hilton</option><option value="109">DOYLE BRAZNELL</option><option value="82">Dwayne</option><option value="219">Elie Daher (Audi Rochesterhills)</option><option value="671">Elliot Brown’s</option><option value="308">Elvin</option><option value="367">Emily Sybert</option><option value="286">Emp - Audi Rochestorhills</option><option value="270">Emp - Countryside Chrysler</option><option value="271">Emp - Eastway Toyota</option><option value="273">Emp - Leamington GM</option><option value="288">Emp - Lincoln of Windsor</option><option value="284">Emp - Management</option><option value="287">Emp - Masrati of Cleveland</option><option value="285">Emp - Mazda of Windsor</option><option value="280">Emp - Mercedes Benz north Olmsted</option><option value="268">Emp - Midtown Honda</option><option value="274">Emp - Mini Motor City</option><option value="282">Emp - Oakridge Ford</option><option value="276">Emp - Overseas Mercedes benz</option><option value="275">Emp - Overseas Motors BMW</option><option value="281">Emp - Performance Ford</option><option value="279">Emp - Porsche Cleveland</option><option value="277">Emp - Porsche Motor City</option><option value="283">Emp - Whiteoak Ford</option><option value="105">Ethan</option><option value="220">Frank Steffen (Cleveland Porsche)</option><option value="197">fred</option><option value="221">Ghassan Gus (Overseas BMW)</option><option value="232">Hamoody Rafih</option><option value="65">HANNAH</option><option value="111">Heather</option><option value="152">Heather</option><option value="155">Heaven</option><option value="326">Helena Rapiz</option><option value="188">Jacob</option><option value="222">James Godfrey (Eastway Toyota)</option><option value="153">Jamie</option><option value="157">Jason</option><option value="93">Jean-Paul chouinard</option><option value="399">Jean-Paul Chouinard</option><option value="189">Jeff</option><option value="250">Jennifer Howe</option><option value="144">JESSE NASR</option><option value="164">Jessica</option><option value="162">Jim</option><option value="455">Joe Kailer</option><option value="209">John</option><option value="394">Jonathon Welch</option><option value="113">JORDAN LESANSKY</option><option value="130">Josh</option><option value="183">Justin Petrozzi</option><option value="122">KIRK HILLMAN</option><option value="159">Kristin Ott</option><option value="81">Kristy Clark</option><option value="319">Lindsey Grego</option><option value="180">Lisa Warnes</option><option value="95">Lorraine Williams</option><option value="624">Louie Lisi</option><option value="766">Louis Palmeri</option><option value="195">Lucas</option><option value="46">Mackenzie</option><option value="190">MARCEL</option><option value="182">Mark Jennings</option><option value="224">Mark Stevens (Oakridge Ford)</option><option value="116">MARTHIE RESENDES</option><option value="186">Matt</option><option value="156">Matt Kutsner</option><option value="240">Matt Youssef</option><option value="451">Max Careau</option><option value="669">MEIXIA  SONG</option><option value="131">Michael</option><option value="208">Michael Moore</option><option value="322">Mike Sinainos</option><option value="194">Moe</option><option value="196">MOE HAZIME</option><option value="125">Mounika</option><option value="149">Mounir</option><option value="234">Munawwar Ali</option><option value="100">Najib</option><option value="227">Nicole langlois</option><option value="67">Oscar</option><option value="102">Patricia Ciampini</option><option value="94">Paul Cunningham</option><option value="301">Paul Twigg</option><option value="72">Pranali</option><option value="163">RENEE</option><option value="207">Richard</option><option value="371">Robert W. Lucas</option><option value="106">russ</option><option value="51">Sam</option><option value="50">Samantha</option><option value="841">SAYF</option><option value="36">Scott Story</option><option value="48">Shauna Lines</option><option value="236">Sherri Holmes</option><option value="134">shila</option><option value="104">Shirley</option><option value="373">Steffan Degiovanni</option><option value="120">STEVE TOUPIN</option><option value="228">Surinder Singh (Windsor Mazda)</option><option value="123">TANYA FREDERICK</option><option value="297">Thomas Jeon</option><option value="206">Tiffany</option><option value="136">Tim</option><option value="137">Timothy</option><option value="193">Tom</option><option value="238">Vickie</option><option value="128">yasmine</option><option value="233">Zeyad Rafih</option>                        </select>
                        <script>
                            $('#owner-select').selectize();
                        </script>
                </div>-->
        
        <!--<div class="form-group" style="margin-top: 20px">
                <label>:</label>
                <div class="checkbox-list">
                    <div class="checkbox-custom">
                        <input type="checkbox" id="create_notify1" name="notify" value="1" >
                        <label for="create_notify1"></label>
                    </div>
                                        <div class="checkbox-custom">
                        <input type="checkbox" id="create_show1" name="show" value="1" >
                        <label for="create_show1"></label>
                    </div>
                                    </div>
            </div>-->

                    <button type="submit" class="btn btn-full" fdprocessedid="ja6jfp">Submit Request</button>
                <input type="hidden" name="token" value="a5e1fa86e3f0555c50bcc5414b48ca915b7b71b8">
        <input type="hidden" name="category" value="7">
        </form>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
    </div>
</div>
/*   http://localhost:8100/dashboard/tickets/new/7 */

/*  http://localhost:8100/dashboard/tickets/new/8 */

<div class="main__content categories ticket-create">


    <div class="table-wrap">

            <div class="main__content notice-flash ">
        <div class="notification blue">
            <b> </b> Use this from to submit name and other relevant details        </div>
    </div>
	       <!--  <div class="notification red"> 
  <b>Note:</b> Deanna is on vacation from June 20 – July 7, 2025. She will not approve any requests until she returns.
</div>  -->
        <h3 style="font-size: 1.3rem; margin-top: 10px">Insert a new request</h3>
        <h4>Required fields are marked with <span class="important">*</span></h4>

        <form method="post" class="form was-validated  " action="admin_submit_ticket.php" name="form1" id="submit-ticket" enctype="multipart/form-data">

                            <div class="form-group" style="margin-bottom: 0px;">
                    <label for="create_name" style="display: inline;">
                        Category:
                    </label>
                    &nbsp; 
                    <button type="button" class="btn btn--blue-border change_category" name="cc-btn" id="cc-btn" title="Click to change request category" fdprocessedid="lgz1fn">Name Tag Request                        &nbsp;
                        <svg class="icon icon-edit">
                            <use xlink:href="../img/sprite.svg#icon-edit"></use>
                        </svg>
                    </button>
                    <input type="hidden" name="change_category" id="change_category" value="8">
                    <script>
                        $("#cc-btn").click(function() {
                            $("#change_category").val(1);
                            $("#submit-ticket").submit();
                        });
                    </script>
                </div>
                                        <div class="main-group">
                    <div class="form-group">

                        <label for="create_name">
                            Name: <span class="important">*</span>
                        </label>
                        <input type="text" id="create_name" name="name" class="form-control " maxlength="50" value="Admin" readonly="" fdprocessedid="sy19p6">


                        <!--<label for="create_dealership">
                    Dealership: <span class="important">*</span>
                </label>
                <input type="text" id="create_dealership" name="dealership" class="form-control " maxlength="50" value="">
			</div>-->
                    </div>
                                        <div class="form-group">
                        <label for="email">
                            Email: <span class="important">*</span>                        </label>
                        <input type="email" class="form-control " name="email" id="email" readonly="" maxlength="1000" value="asharma@blackrockdigital.agency" onblur="Javascript:hesk_suggestEmail('email', 'email_suggestions', 1, 1)" fdprocessedid="z3azo">
                    </div>

                    <div id="email_suggestions"></div>
                </div>
                <div class="main-group">
                    <div class="form-group">
                        <label class="priority ">Priority: </label>
                        <div class="dropdown-select center out-close priority" data-value="3">
                            <select name="priority">
                                                                <option value="3" selected="">Low</option>
                                <option value="2">Medium</option>
                                <option value="1">High</option>
                              
                            </select>
                        <div class="label"><span>Low</span><svg class="icon icon-chevron-down"><use xlink:href="../img/sprite.svg#icon-chevron-down"></use></svg></div><ul class="dropdown-list"><li data-option="3" class="selected">Low</li><li data-option="2">Medium</li><li data-option="1">High</li></ul></div>
                    </div>
                    <div class="form-group ts left-alig" style="margin-left: 60px !important;" id="ticket-status-div">
                        <label>Status:</label>
                        <div class="dropdown-select center out-close" data-value="0">
                            <select id="status-select" name="status" onchange="do {
                                                                            hesk_update_status_color
                                                                                } while (
                                                                                do {
                                                                                    condition
                                                                                } while (condition);
                                                                                );(this.value)">
                                <option value="0" selected="">New</option><option value="1">Waiting reply</option><option value="5">On Hold</option><option value="6">Canceled</option><option value="7">Approved</option><option value="3">Resolved</option>                            </select>
                        <div class="label"><span class="open">New</span><svg class="icon icon-chevron-down"><use xlink:href="../img/sprite.svg#icon-chevron-down"></use></svg></div><ul class="dropdown-list"><li data-option="0" class="selected open">New</li><li data-option="1" class="waitingreply">Waiting reply</li><li data-option="5" class="onhold">On Hold</li><li data-option="6" style="color: rgb(0, 0, 0);">Canceled</li><li data-option="7" style="color: rgb(95, 20, 255);">Approved</li><li data-option="3" class="resolved">Resolved</li></ul></div>
                    </div>
                    					  
                </div>
                
                       
               
           
            <!-- START CUSTOM BEFORE -->
            			
                        <div class="main-group">
                <div class="form-group" style="width:50%">
             
                    <label for="create_name">First Name </label>
                    <input type="text" id="first_name" required="" name="first_name" class="form-control " maxlength="50" value="" fdprocessedid="4gsk6">
                </div>
            </div>  
            <div class="main-group">
                <div class="form-group" style="width:50%">
                   
                    <label for="second_tag">Last Name<span class="important"></span></label>
                    <input type="text" class="form-control" required="" name="last_name" id="second_tag" maxlength="1000" value="" fdprocessedid="yr9zk">
                </div>
                </div>  
                <div class="main-group">
                <div class="form-group" style="width:50%">
              
                    <label for="second_tag">Title<span class="important"></span></label>
                    <input type="text" class="form-control" required="" name="title" id="second_tag" maxlength="1000" value="" fdprocessedid="66tp6c">
                </div>
                </div>  
                <div class="main-group">
                <div class="form-group" style="width:50%">
                
                    <label for="second_tag">Manufacture logo<span class="important"></span></label>
                    <div class="custom-file">
    <input type="file" class="custom-file-input" id="manufacture_logo" name="manufacture_logo">
    
  </div>
                            </div>
                            </div>  
                               <div class="main-group">
                    <div class="form-group" style="width:50%">

                        <label for="second_tag" class="form-label"> Reason code<span class="important"></span></label>
                        <input type="text" class="form-control" placeholder="Enter Reason code " required="" name="reason_code" id="reason_code" maxlength="1000" fdprocessedid="nba2lu">
						( 'New Employee', 'Title Change', 'Lost/Replacement' )
                      
                    </div>
                </div>
            			
                        <!-- END CUSTOM BEFORE -->
			            <!--
                        <div class="form-group">
                <label><a href="manage_ticket_templates.php" class="link">Manage request templates</a></label>
            </div>
        
        
        

        <!--<div class="form-group">
                <label>Subject: <span class="important">*</span></label>
                <span id="HeskSub"><input class="form-control " type="text" name="subject" id="subject" maxlength="70" value=""></span>
            </div>-->
        <div class="form-group">
                            <label>Message: </label>
                        <span id="HeskMsg">
                <textarea style="height: inherit" class="form-control " name="message" id="message" rows="12" cols="60"></textarea>
            </span>
        </div>
                <!-- END CUSTOM AFTER -->

                    <div class="attachments">
                <div class="block--attach">
                    <svg class="icon icon-attach">
                        <use xlink:href="../img/sprite.svg#icon-attach"></use>
                    </svg>
                    <div>
                                                    Attachments:
                                            </div>
                </div>
                <div class="dropzone dz-click-filedrop dz-clickable" id="filedrop">
        
    <div class="dz-default dz-message"><button class="dz-button" type="button" fdprocessedid="zgfkrh">Drag files here or click the 'Add File' button below to select files to upload.</button></div></div>
    <div class="btn btn-full fileinput-button filedropbutton-filedrop dz-click-filedrop dz-clickable">Add file</div><a class="link" href="../file_limits.php" target="_blank" onclick="Javascript:hesk_window('../file_limits.php',250,500);return false;">File upload limits</a><div id="attachment-holder-filedrop" class="hide"></div>
    <div id="previews" style="display:none">
        <div id="template" class="file-row">
            <!-- This is used as the file preview template -->
            <div class="attachment-row">
                <div class="name-size-delete">
                    <div class="name-size">
                        <div class="name">
                            <p class="name" data-dz-name=""></p>
                        </div>
                        <div class="size">
                            (<span data-dz-size=""></span>)
                        </div>
                    </div>
                    <div class="delete-button">
                        <svg class="icon icon-delete" data-dz-remove="">
                            <use xlink:href="../img/sprite.svg#icon-delete"></use>
                        </svg>
                    </div>
                </div>
                <div class="upload-progress">
                    <div style="border: 1px solid #d4d6e3; width: 100%; height: 19px">
                        <div style="font-size: 1px; height: 17px; width: 0px; border: none; background-color: green" data-dz-uploadprogress="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="error">
                <strong class="error text-danger" data-dz-errormessage=""></strong>
            </div>
        </div>
    </div><script>const HeskWithout = (list, rejectedItem) => list.filter((item) => item !== rejectedItem).map((item) => item);</script>
    <script>
    Dropzone.autoDiscover = false;
    var dropzonefiledrop = new Dropzone('#filedrop', {
        paramName: 'attachment',
        url: '../upload_attachment.php',
        parallelUploads: 20,
        maxFiles: 20,
        acceptedFiles: ".gif,.jpg,.png,.zip,.rar,.csv,.doc,.docx,.xls,.xlsx,.txt,.pdf",
        maxFilesize: 115, // MB
        dictDefaultMessage: "Drag files here or click the 'Add File' button below to select files to upload.",
        dictFallbackMessage: '',
        dictInvalidFileType: "Sorry, but the file type you tried to upload is not allowed.",
        dictResponseError: "An error occurred when trying to upload. Please try again later.",
        dictFileTooBig: "This attachment is larger than the max filesize permitted.",
        dictCancelUpload: "Cancel",
        dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
        dictRemoveFile: "Remove attachment",
        dictMaxFilesExceeded: "This file will not be uploaded becuase you have already uploaded the maximum number of files allowed.",
        previewTemplate: $('#previews').html(),
        clickable: '.dz-click-filedrop',
        uploadMultiple: false
    });
    
    dropzonefiledrop.on('success', function(file, response) {
        var jsonResponse = JSON.parse(response);
        // console.log(JSON.stringify(jsonResponse, null, 4));

        if(jsonResponse.hasOwnProperty('status') && jsonResponse['status'] == 'failure'){
            // Upload was OK, but something failed on the server-side
            dropzonefiledrop.emit('uploadprogress', file, 0);
            dropzonefiledrop.files = HeskWithout(dropzonefiledrop.files, file);
            dropzonefiledrop.options.error(file, jsonResponse['message']);
        } else {
            // The response will only be a JSON object holding the saved and real name
            outputAttachmentIdHolder(jsonResponse['file_key'], 'filedrop');

            // Add the database id to the file
            file['databaseResponse'] = jsonResponse['file_key'];
        }
    });
    dropzonefiledrop.on('addedfile', function() {
        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }

        $('.filedropbutton-filedrop').attr('disabled', disabled);
    });
    dropzonefiledrop.on('removedfile', function(file) {
        if (file.beingRetried) {
            return;
        }
    
        // Remove the attachment from the database and the filesystem.
        removeAttachment(filedrop, file['databaseResponse'], true);

        var numberOfFiles = $('#filedrop .file-row').length;

        var disabled = false;
        if (numberOfFiles >= 20) {
            disabled = true;
        }
        $('.filedropbutton-filedrop').attr('disabled', disabled);
        
        dropzonefiledrop.getRejectedFiles().forEach(function(file) {
            file.beingRetried = true;
            dropzonefiledrop.removeFile(file);
            file.status = undefined;
            file.accepted = undefined;
            file.beingRetried = false;
            dropzonefiledrop.addFile(file);
        });
    });
    dropzonefiledrop.on('queuecomplete', function() {
        $('input[type="submit"]').attr('disabled', false);
    });
    dropzonefiledrop.on('processing', function() {
        $('input[type="submit"]').attr('disabled', true);
    });
    dropzonefiledrop.on('uploadprogress', function(file, percentage) {
        $(file.previewTemplate).find('#percentage').text(percentage + '%');
    });
    dropzonefiledrop.on('error', function(file, message) {
        $(file.previewTemplate).addClass('alert-danger');
        
        var actualMessage = message.title + ': ' + message.message;
        if (!message.message) {
            actualMessage = message;
        }
        
        $(file.previewElement).addClass('dz-error').find('[data-dz-errormessage]').text(actualMessage);
    });
    </script>
                </div>
        
                    <!--<section class="param calendar">
                                <label>Due date:</label>
                <div class="calendar--button">
                    <button type="button">
                        <svg class="icon icon-calendar">
                            <use xlink:href="../img/sprite.svg#icon-calendar"></use>
                        </svg>
                    </button>
                    <input name="due_date" id="due_date"
                           value=""
                           type="text" class="datepicker">
                </div>
                <div class="calendar--value" style="">
                <span></span>
                <i class="close">
                    <svg class="icon icon-close">
                        <use xlink:href="../img/sprite.svg#icon-close"></use>
                    </svg>
                </i>
                </div>
            </section>-->
            <br>
                                    <!-- <div class="form-group">
                    <label>Assign this request to:</label>
                        <select name="owner" id="owner-select" >
                            <option value="-1"> &gt; Unassigned &lt; </option>
                            <option value="321">Adam Still</option><option value="1">Admin</option><option value="191">Anthony</option><option value="544">Beth</option><option value="118">CYNTHIA</option><option value="235">Deanna Muir (Purchase)</option><option value="671">Elliot Brown’s</option><option value="650">JOHN COCCA (JJ)</option><option value="729">Kayla Khattar</option><option value="624">Louie Lisi</option><option value="766">Louis Palmeri</option><option value="669">MEIXIA  SONG</option><option value="617">NEXHAT   REKA</option><option value="885">Pat Cubellis</option><option value="632">Royston Peter</option><option value="790">RYAN KASPERCZAK</option><option value="774">Sara Dawson</option><option value="236">Sherri Holmes</option><option value="373">Steffan Degiovanni</option><option value="618">Steve Cowell</option><option value="591">Steve Reid</option><option value="894">Tyler</option><option value="238">Vickie</option><option value="233">Zeyad Rafih</option>                        </select>
                        <script>
                            $('#owner-select').selectize();
                        </script>
                </div>-->
        
        <!--<div class="form-group" style="margin-top: 20px">
                <label>:</label>
                <div class="checkbox-list">
                    <div class="checkbox-custom">
                        <input type="checkbox" id="create_notify1" name="notify" value="1" >
                        <label for="create_notify1"></label>
                    </div>
                                        <div class="checkbox-custom">
                        <input type="checkbox" id="create_show1" name="show" value="1" >
                        <label for="create_show1"></label>
                    </div>
                                    </div>
            </div>-->

                    <button type="submit" class="btn btn-full" fdprocessedid="fnlan">Submit Request</button>
                <input type="hidden" name="token" value="a5e1fa86e3f0555c50bcc5414b48ca915b7b71b8">
        <input type="hidden" name="category" value="8">
        </form>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
    </div>
</div>