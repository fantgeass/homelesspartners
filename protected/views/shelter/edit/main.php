<script type='text/javascript'>
$(document).ready(function() {

    $("#shelterCoordinators").selectize({
        plugins: ['remove_button'],
    });

    $("#dropoffLocations").selectize({
        plugins: ['remove_button'],
    });


    $("#shelterForm").validate({
        submitHandler: function(form) {
            form.submit();
        },
        onsubmit: true,
        onkeyup: false,
        focusCleanup: true,
        messages: {
        },
        errorPlacement: function(error, element) {
        },
        highlight: function(element, errorClass) {
            $element = $(element);
            $element.closest("div.form-group").addClass(errorClass);
        },
        unhighlight: function(element, errorClass) {
            $element = $(element);
            $element.closest("div.form-group").removeClass(errorClass);
        },
        //where to post messages
        errorClass: "has-error",
        ignore: ":hidden",
        rules: {
            'name': 'required',
            'cityId': 'required',
            'ID_FORMAT': 'required'
        }
    });

    $("#citySelect").selectpicker({style: 'btn-white', menuStyle: 'dropdown'});
});
</script>

<div class='container'>
    <div class='row'>
        <div class='col-md-12'>
            <?php if(!empty($shelter)): ?>
            <h2>Edit Shelter</h2>

            <ul class="breadcrumb">
                <li>Admin</li>
                <li><a href='<?php echo $this->createUrl("shelter/index") ?>'>View Shelters</a></li>
                <li class='active'>Edit Shelter</li>
            </ul>
            <?php else: ?>
            <h2>Create Shelter</h2>

            <ul class="breadcrumb">
                <li>Admin</li>
                <li><a href='<?php echo $this->createUrl("shelter/index") ?>'>View Shelters</a></li>
                <li class='active'>Create Shelter</li>
            </ul>
            <?php endif; ?>
        </div>
        <div class='col-md-6'>
            <form id='shelterForm' action='<?php echo $this->createUrl("shelter/save") ?>' method='post' enctype="multipart/form-data">
                <?php if(!empty($shelter)): ?>
                <input type='hidden' name='shelterId' value='<?php echo $shelter->shelter_id ?>' />
                <?php endif; ?>

                <?php if(!empty($shelter)): ?>
                <input type='hidden' name='creatorId' value='<?php echo $shelter->creator_id ?>' />
                <?php else:?>
                    <input type='hidden' name='creatorId' value='<?php echo $userId ?>' />
                <?php endif; ?>

                <div class='form-group'>
                    <label style='display: block;'>City</label>
                    <select id='citySelect' name='cityId' />
                        <?php foreach ($cities as $city): ?>
                        <option value='<?php echo $city->city_id ?>' <?php echo ((!empty($shelter) && $shelter->city_id == $city->city_id)?' selected':'')?>><?php echo $city->name ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class='form-group'>
                    <label>Name</label>
                    <input type='text' class='form-control' name='name' value='<?php echo !empty($shelter)?$shelter->name:"" ?>' />
                </div>
                <div class='form-group'>
                    <label>Street</label>
                    <textarea class='form-control' name='street'><?php echo !empty($shelter)?$shelter->street:"" ?></textarea>
                </div>
                <div class='form-group'>
                    <label>Phone</label>
                    <input type='text' class='form-control' name='phone' value='<?php echo !empty($shelter)?$shelter->phone:"" ?>' />
                </div>
                <div class='form-group'>
                    <label>Biography</label>
                    <textarea class='form-control'rows="5" cols = "40" name='bio'><?php echo !empty($shelter)?$shelter->bio:"" ?></textarea>
                </div>

                <div class='form-group'>
                    <label>ID Format</label>
                    <input type='text' class='form-control' name='ID_FORMAT' value='<?php echo !empty($shelter)?$shelter->ID_FORMAT:"" ?>' />
                </div>
                <div class='form-group'>
                    <label>Website</label>
                    <input type='text' class='form-control' name='website' value='<?php echo !empty($shelter)?$shelter->website:"" ?>' />
                </div>
                <div class='form-group'>
                    <label>Email</label>
                    <input type='text' class='form-control' name='email' value='<?php echo !empty($shelter)?$shelter->email:"" ?>' />
                </div>
                <div class='form-group'>
                    <label>Mapped</label>
                    <input type="checkbox" name='mapped' value='1' <?php echo (!empty($shelter) && $shelter->mapped)?"checked='checked'":"" ?> data-toggle="switch" />
                </div>
                <div class='form-group'>
                    <label>Date Created: <?php echo !empty($shelter)?$shelter->date_created:"" ?></label>
                </div>
                <div class='form-group'>
                    <label>Enabled</label>
                    <input type="checkbox" name='enabled' value='1' <?php echo (!empty($shelter) && !empty($shelter->enabled))?"checked='checked'":"" ?> data-toggle="switch" />
                </div>

                <div class='form-group'>
                    <label>Shelter Coordinators</label>
                    <select id='shelterCoordinators' name='shelterCoordinators[]' multiple>
                        <?php foreach ($allShelterCoordinators as $user): ?>
                            <option value='<?php echo $user->user_id ?>' <?php echo in_array($user->user_id, $currentShelterCoordinators)?"selected='selected'":"" ?>><?php echo $user->email ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class='form-group'>
                    <label>Dropoff Locations</label>
                    <select id='dropoffLocations' name='dropoffLocations[]' multiple>
                        <?php foreach ($currentDropoffLocations as $location):?>
                            <option value='<?php echo $location['id']?>' selected='selected'><?php echo $location['name'] . ': ' . $location['address']; ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class='form-group'>
                    <label>Drop Off Details</label>
                    <textarea class='form-control' rows="5" cols = "40" name='dropoff_details'></textarea>
                </div>
                <div class='form-group'>
                    <label>Dropoff Location Name</label>
                    <input type='text' class='form-control' name='location-name' />
                </div>
                <div class='form-group'>
                    <label>Dropoff Location Address</label>
                    <input type='text' class='form-control' name='location-address' />
                </div>
                <div class='form-group'>
                    <label>Dropoff Location Notes</label>
                    <textarea class='form-control' name='location-notes'></textarea>
                </div>

                <div class='form-group'>
                    <label>Shelter Image</label>
                    <input type='file' class='form-control' name='image' />
                    <?php if(!empty($shelter) && !empty($shelter->img)): ?>
                    <img src='<?php echo $shelter->img ?>' />
                    <?php endif; ?>
                </div>

                <div class='form-group'>
                    <input type='submit' class='btn btn-success' value='Save' />
                </div>
            </form>
        </div>
    </div>
</div>

