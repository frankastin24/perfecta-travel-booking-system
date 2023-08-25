<table>
    <tbody>

    </tbody>
</table>
<?php
global $post;
?>

<div class="<?= get_post_meta($post->ID, 'package_type', true) == 'ordinary' ? 'package-type-ordinary' : 'package-type-cruise' ?> " id="add_new_startdate_dialog">

    <h1>Add new start date</h1>

    <div class="close">X</div>

    <div class="field">
        <label>Start Date:</label>
        <input name="start_date" class="required" type="date">
        <p>This field is required</p>
    </div>
    <div class="field">
        <label>End Date:</label>
        <input name="end_date" class="required" type="date">
        <p>This field is required</p>
    </div>
    <div class="field show-cruise">
        <label>Price Upper/ Main deck:</label>
        <input name="price_upper" type="number">
        <p>This field is required</p>
    </div>
    <div class="field show-cruise">
        <label>Price Lower deck:</label>
        <input name="price_lower" type="number">
        <p>This field is required</p>
    </div>
    <div class="field show-ordinary">
        <label>Price Per Person:</label>
        <input name="price_per_person" type="number">
        <p>This field is required</p>
    </div>
    <div class="field show-ordinary">
        <label>Single Person Supplement:</label>
        <input name="single_person_supplement" type="number">
        <p>This field is required</p>
    </div>
    <div class="field">
        <label>Number of rooms:</label>
        <input name="number_rooms" class="required" type="number">
        <p>This field is required</p>
    </div>
    <div class="field">
        <label>Number of people per room:</label>
        <input name="people_per_room" class="required" type="number">
        <p>This field is required</p>
    </div>

    <button id="save_start_date">Save Date</button>

    <input type="hidden" name="json_data" />
</div>

</div>
<script>
    $ = jQuery;
    <?php
    global $post;

    ?>

    let start_dates = <?= !empty(get_post_meta($post->ID, 'start_dates', true)) ? json_encode(get_post_meta($post->ID, 'start_dates', true)) :  '[]'; ?>;


    function populate_start_dates() {

        let table_html = `
    <tr><th>Start Date</th>
    <th>End Date</th>`;

        if (package_type == 'cruise') {
            table_html += ` <th>Price Upper/ Main Deck</th>
    <th>Price Lower</th>`;
        } else {
            table_html += ` <th>Price Per Person</th>
    <th>Single Person Supplement</th>`;
        }


        table_html += `  <th>Number Of Rooms</th>
    <th>Number Of People Per Room</th>
    <th>Delete</th></tr>`;

        if (start_dates.length == 0) {
            table_html += '<tr><td colspan="4">No start dates added</td></tr>';
        } else {
            start_dates.forEach((startdate) => {
                table_html += `
    <tr>
    <td><input name="start_date" type="date" value="${startdate.start_date}"/></td>
    <td><input name="end_date" type="date" value="${startdate.end_date}"/></td>`

                if (package_type == 'cruise') {
                    table_html += `<td><input name="price_upper" type="number" value="${startdate.price_upper}"/></td>
    <td><input name="price_lower" type="number" value="${startdate.price_lower}"/></td>`;
                } else {
                    table_html += `<td><input name="price_per_person" type="number" value="${startdate.price_per_person}"/></td>
    <td><input name="single_person_supplement" type="number" value="${startdate.single_person_supplement}"/></td>`;
                }
                table_html += ` <td><input name="number_rooms" type="number" value="${startdate.number_rooms}"/></td>
    <td><input name="people_per_room" type="number" value="${startdate.people_per_room}"/></td>
    <td><button class="delete">Delete</button></td></tr>`;
            })
        }
        $('table tbody').html(table_html);
        $('input[name="json_data"]').val(JSON.stringify(start_dates));
    }

    $(() => {

        populate_start_dates();


        var package_type = "<?= get_post_meta($post->ID, 'package_type', true) == 'ordinary' ? 'ordinary' : 'cruise'; ?>";
        set_package_type();

        function set_package_type() {

            if (package_type == 'ordinary') {
                $('#add_new_startdate_dialog').removeClass('package-type-cruise').addClass('package-type-ordinary');
                $('.show-cruise').find('input').removeClass('required')
                $('.show-ordinary').find('input').addClass('required');
            } else {
                $('#add_new_startdate_dialog').removeClass('package-type-ordinary').addClass('package-type-cruise');
                $('.show-ordinary').find('input').removeClass('required');
                $('.show-cruise').find('input').addClass('required')
            }
        }

        $('select[name="package_type"]').on('change', (e) => {
            package_type = e.currentTarget.value;
            set_package_type();
            start_dates = [];
            populate_start_dates();
        })


        $('table tbody').on('click', '.delete', (e) => {
            const index = $(e.currentTarget).parent().parent().index() - 1;
            start_dates.splice(index, 1);
            $(e.currentTarget).parent().parent().remove();
            $('input[name="json_data"]').val(JSON.stringify(start_dates));
        })

        $('table tbody').on('input', 'input[type="number"]', (e) => {
            const index = $(e.currentTarget).parent().parent().index() - 1;

            start_dates[index][$(e.currentTarget).attr('name')] = $(e.currentTarget).val();

            $('input[name="json_data"]').val(JSON.stringify(start_dates));
        })

        $('table tbody').on('change', 'input[type="date"]', (e) => {
            const index = $(e.currentTarget).parent().parent().index() - 1;

            start_dates[index][$(e.currentTarget).attr('name')] = $(e.currentTarget).val();

            $('input[name="json_data"]').val(JSON.stringify(start_dates));
        })

        $('#save_start_date').on('click', (e) => {
            let isValid = true;
            e.preventDefault();

            var startdate = {};
            var packageType = $('select[name="package_type"]').val();

            $('.field input.required').each((index, el) => {


                if ($(el).val().trim() == '') {

                    isValid = false;

                    $(el).parent().addClass('invalid');

                } else {
                    startdate[$(el).attr('name')] = $(el).val();
                }
            });

            if (isValid) {

                start_dates.push(startdate);
                console.log(start_dates)
                $('input[name="json_data"]').val(JSON.stringify(start_dates));
                $('.field input').val('')
                populate_start_dates();
            }

        })
        $('input[type="number"]').on('input', (e) => {

            if ($(e.currentTarget).val().trim() != '') {
                $(e.currentTarget).parent().removeClass('invalid');
            }
        })

        $('input[type="date"]').on('change', (e) => {

            if ($(e.currentTarget).val().trim() != '') {
                $(e.currentTarget).parent().removeClass('invalid');
            }
        })



    })
</script>

<style>
    #wpfooter {
        position: relative;
    }

    .package-type-cruise .show-cruise {
        display: block;
    }

    .package-type-cruise .show-ordinary {
        display: none;
    }

    .package-type-ordinary .show-cruise {
        display: none;
    }

    .package-type-ordinary .show-ordinary {
        display: block;
    }

    .wrap h1 {
        padding: 0;
        margin-bottom: 10px;
    }

    table th {
        padding: 10px 10px;
        text-align: left;
    }

    table td {
        padding: 10px 10px;
        text-align: left;

    }

    .field {
        margin-bottom: 10px;
    }

    .field p {
        display: none;
        color: red;
        margin-top: -10px;

    }

    .field.invalid p {
        display: block;
    }

    .field.invalid input {
        display: block;
        border-color: red;
    }

    .field.invalid label {
        color: red;
    }


    label {
        width: 193px;
        display: block;
        float: left;
    }


    #add_new_startdate_dialog {
        margin-top: 40px;
        display: block;
        background: #FFF;
        border: 1px solid #CCC;
        padding: 10px 20px;
    }

    .close {
        position: absolute;
        right: 10px;
        top: 9px;
        font-size: 18px;
        cursor: pointer;
    }
</style>