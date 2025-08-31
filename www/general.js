    function initform()
    {
        var element_value = document.getElementById('value');
        if ( element_value != null ) {
            element_value.focus();
        }
        var element_date = document.getElementById('date');
        if ( element_date != null ) {
            //element_date.value = "$date";
            element_date.value = new Date().toLocaleDateString('en-CA');
        }
    }