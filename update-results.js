// update the options in the category and placing select boxes
function updateCategoryAndPlacingOptions(select)
{
    ride_type = select ? select[select.selectedIndex].getAttribute('ridetype') : 1;
    var i, include_tour, include_race;
    if(ride_type==4) {
        // Tours
        updateOptions('select_category', selectCategories, 0, 1);
        updateOptions('select_place', selectPlacings, 0, 1);
        document.getElementById('select_category').selectedIndex = 1;
        document.getElementById('select_place').selectedIndex = 1;
    } else {
        // Treat all other event types others as races
        updateOptions('select_category', selectCategories, 1, 0);
        updateOptions('select_place', selectPlacings, 1, 0);
    }
}

// update options in a single select box
function updateOptions(id, new_options, include_race, include_tour)
{
    options = document.getElementById(id).options;
    // clear existing options
    options.length=0;
    // load new options filtering for race/tour
    for(i=0; i< new_options.length; i++) {
        if((new_options[i][2] && include_tour) || (new_options[i][3] && include_race)) {
            options.add(new Option(new_options[i][1], new_options[i][0]));
        }
    }
}

function checkFields(form) 
{
    if(form.RaceID.value==0)
    {
        alert("You must select an event");
        form.RaceID.focus();
        return(false);
    }
    if(form.CategoryID.value==0)
    {
        alert("You must select a field");
        form.CategoryID.focus();
        return(false);
    }
    if(form.PlaceID.value==0)
    {
        alert("You must select a place");
        form.PlaceID.focus();
        return(false);
    }
    return(true);
}
