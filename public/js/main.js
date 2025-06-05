$( document ).ready(function() {
	if(typeof get !== 'undefined'){
		// console.log(get);
		var from = get['from'];
		var id = get['id'];
		$("#"+from).val(id).change();
		// $("#"+from).attr('disabled',"true");
		$("#"+from).selectpicker('refresh');

	}
});

$(document).ready(function() {
    // Initialize the jQuery timepicker plugin on our text inputs
    $('.js-custom-timepicker').timepicker({
        timeFormat: 'HH:mm',   // 24-hour format
        interval: 30,          // Only allow increments of 30 minutes (0 and 30)
        minTime: '00:00',
        maxTime: '23:30',
        dynamic: false,
        dropdown: true,
        scrollbar: true,
        change: function(){
           $(this).trigger('change');
        }
    });
});



$("sidebarCollapse").on("click",function(e){
	$("#sidebar").toggleClass("vertical-nav-hidden");
	$(".page-content").toggleClass("page-content-full");
});

$("#sidebarCollapse").on("click",function(e){
	$("#sidebar").toggleClass("vertical-nav-small");
	$(".page-content").toggleClass("page-content-maxed");
});


$(".js-nav-has-sub-nav").on("click", function(e){
	// e.preventDefault();
	// $(this).siblings("ul.sub-nav").slideToggle("fast");
});

$(".js-connections-header a.js-show-connections").on("click", function(e){
	e.preventDefault();
	$(this).parents(".js-connections-header").siblings(".js-connections-content").slideToggle("fast");
});

$(".js-date-search-from").on("change",function(e){
	var from = $(this).val();
	if(from == "") {
		from = "2000-01-01";
	}

	var to = $(this).parent().siblings().children(".js-date-search-to").val();
	if(to == "") {
		to = "2030-01-01";
	}
	var interval = from + "|" + to;
	$(this).parent().siblings(".js-date-search-interval").val(interval);
});



$(".js-date-search-to").on("change",function(e){
	var to = $(this).val();
	if(to == "") {
		to = "2040-01-01";
	}

	var from = $(this).parent().siblings().children(".js-date-search-from").val();
	if(from == "") {
		from = "2000-01-01";
	}
	var interval = from + "|" + to;
	$(this).parent().siblings(".js-date-search-interval").val(interval);
});


$(".js-confirm").on("click", function(e){
	return confirm("Sigur doriti sa faceti aceasta actiune?");
});


// create name of inchiriere or oferta
$(document).ready(function() {

    // Disable the input fields by default
    $('.js-nume-client-oferta, .js-prenume-client-oferta, .js-telefon-client-oferta, .js-email-client-oferta').attr('readonly', true);

    // Enable the input fields if '0' is selected in the client dropdown
    $('select.js-client').change(function() {
        // console.log($(this).val());
        if ($(this).val() === '0') {
            $('.js-nume-client-oferta, .js-prenume-client-oferta, .js-telefon-client-oferta, .js-email-client-oferta').removeAttr('readonly');
        } else {
            $('.js-nume-client-oferta, .js-prenume-client-oferta, .js-telefon-client-oferta, .js-email-client-oferta').val("");
            $('.js-nume-client-oferta, .js-prenume-client-oferta, .js-telefon-client-oferta, .js-email-client-oferta').attr('readonly', true);

        }
        // Update the name based on the selected value
        updateNumeInchiriere();
    });

    function updateNumeInchiriere() {
        var masinaText = $('.js-masina option:selected').text().trim();
        var dataInceputText = $('.js-data-inceput').val().trim();
        var clientText;

        // Check if '0' is selected, and use the name and prenume inputs if so
        if ($('select.js-client').val() === '0') {
            var numeClient = $('.js-nume-client-oferta').val().trim();
            var prenumeClient = $('.js-prenume-client-oferta').val().trim();
            clientText = [numeClient, prenumeClient].filter(Boolean).join(' '); // Combine nume and prenume
        } else {
            clientText = $('select.js-client option:selected').text().trim();
        }

        var result = [masinaText, clientText, dataInceputText].filter(Boolean).join(' ');
        $('.js-nume-inchiriere').val(result);
    }

    // Attach change event listeners
    $('.js-masina').change(updateNumeInchiriere);
    $('select.js-client').change(updateNumeInchiriere);
    $('.js-data-inceput').on('input', updateNumeInchiriere); // Use 'input' event for date input changes

    // Additional listeners for nume and prenume inputs
    $('.js-nume-client-oferta, .js-prenume-client-oferta, .js-telefon-client-oferta').on('input', function() {
        // Only update when inputs are enabled
        if (!$('.js-nume-client-oferta').prop('disabled')) {
            updateNumeInchiriere();
        }
    });
});


//search the table function
$(document).ready(function() {
  // Attach event listener to the input field
  $('#carSearch').on('keyup', function() {
    var searchValue = $(this).val().toLowerCase();

    // Loop through the rows in tbody
    $('.my-bookings-table tbody tr').filter(function() {
      // Show or hide row based on search value in any column
      $(this).toggle($(this).text().toLowerCase().indexOf(searchValue) > -1);
    });
  });
});


//color cell dates based on current date. warning past and info next 2 weeks
$(document).ready(function() {
    // Get the current date
    var currentDate = new Date();

    // Function to handle date comparison and class assignment
    function handleDateComparison(cell, dateString) {
        // Parse the date string
        var cellDate = new Date(dateString.trim());

        // Check if the date is valid
        if (!isNaN(cellDate.getTime())) {
            // If the date is past the current date
            if (cellDate < currentDate) {
                cell.addClass('bg-warning text-danger');
            }

            // If the date is within the next two weeks
            var twoWeeksFromNow = new Date();
            twoWeeksFromNow.setDate(currentDate.getDate() + 14);
            if (cellDate >= currentDate && cellDate < twoWeeksFromNow) {
                cell.addClass('bg-info text-white');
            }
        }
    }

    // Handle cases where the class is on the TD
    $('td.js-check-date').each(function() {
        var cell = $(this);
        handleDateComparison(cell, cell.text());
    });

    // Handle cases where the class is on the TR and the date is inside the second TD
    $('tr.js-check-date').each(function() {
        var row = $(this);
        var secondTd = row.find('td').eq(0); // Get the second TD in the row
        // console.log(secondTd);
        handleDateComparison(secondTd, secondTd.text());
    });

    function handleKmComparison(revizieCell, kmActualiCell) {
        var revizie = parseInt(revizieCell.text().replace(/\D/g, ''), 10);
        var kmActuali = parseInt(kmActualiCell.text().replace(/\D/g, ''), 10);

        if (!isNaN(revizie) && !isNaN(kmActuali)) {
            if (kmActuali - revizie >= 20000) {
                revizieCell.addClass('bg-warning text-danger');
            }
        }
    }

    // 1. Handle when .js-revizie and .js-km-actuali are directly on <td>s
    $('.js-revizie').each(function () {
        var revizieCell = $(this);
        var row = revizieCell.closest('tr');
        var kmActualiCell = row.find('.js-km-actuali');

        if (kmActualiCell.length) {
            handleKmComparison(revizieCell, kmActualiCell);
        }
    });

    $('.js-km-actuali').each(function () {
        var kmActualiCell = $(this);
        var row = kmActualiCell.closest('tr');
        var revizieCell = row.find('.js-revizie');

        if (revizieCell.length) {
            handleKmComparison(revizieCell, kmActualiCell);
        }
    });

    // 2. Handle when the class is on the <tr>
    $('tr.js-revizie, tr.js-km-actuali').each(function () {
        var row = $(this);
        var revizieCell = row.find('td.js-revizie');
        var kmActualiCell = row.find('td.js-km-actuali');

        if (revizieCell.length && kmActualiCell.length) {
            handleKmComparison(revizieCell, kmActualiCell);
        }
    });


});


//scroll the table to the current date
$(document).ready(function() {
    // Select the table cell with the specific class or ID
    var targetColumn = $('.my-current-date'); // Use .my-target-class for class or #my-target-id for ID

    var scrollDate = getUrlParameter('scrollDate');
    if(scrollDate != null) {
        targetColumn = $('.my-bookings-table td[data-cell-date="' + scrollDate + '"]');
    }



    if (targetColumn.length) {
        // Get the position of the target column relative to its parent container
        var targetPosition = targetColumn.position().left - 290;

        // Scroll the container to the target column position
        $('.card').animate({
            scrollLeft: targetPosition
        }, 800); // 800ms animation duration, adjust as needed
    }
});


$(document).ready(function () {
    // On page load, disable the garantie input
    $('.js-garantie').prop('disabled', true);

    // When the dropdown value changes
    $('select.js-asigurare').on('change', function () {
        // Check if the selected value is '29' (Yes)
        if ($(this).val() === '29') {
            // Enable the garantie input
            $('.js-garantie').prop('disabled', false);
        } else {
            // Otherwise, disable and clear the garantie input
            $('.js-garantie').val('').prop('disabled', true);
        }
    });
});



$(document).ready(function() {
    /* ---------------------------------------------------------------------
       INITIAL SETUP
       --------------------------------------------------------------------- */
    // Disable select inputs until dates are chosen.
    $('select.js-clasa-pret').attr("disabled", true).selectpicker('refresh');
    $('select.js-template-clase-pret').attr("disabled", true).selectpicker('refresh');
    $('.js-discount-oferta').attr("readonly", true);
    $('.js-cost-extra').attr("disabled", true);
    $('.js-explicatie-cost-extra').attr("disabled", true);
    $('select.js-plata-avans').attr("disabled", true).selectpicker('refresh');
    $('.js-oferta-trimisa-text').attr("readonly", true);

    // Initial processing of date/time values.
    handleDateChange();

    //load client discount if client already selected
    $('.js-client').trigger('change');


    /* ---------------------------------------------------------------------
       UTILITY FUNCTIONS
       --------------------------------------------------------------------- */

    // Calculate the number of days between two dates and times.
    // Adds an extra day if the remainder is at least 4 hours.
    function calculateDays(startDate, endDate, startTime, endTime) {
        const start = new Date(`${startDate}T${startTime}`);
        const end = new Date(`${endDate}T${endTime}`);
        const diffTime = Math.abs(end - start);
        const threshold = 4 * 60 * 60 * 1000; // 4 hours in ms
        const days = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        const remainder = diffTime % (1000 * 60 * 60 * 24);
        return remainder >= threshold ? days + 1 : days;
    }

    // Update the options in the .js-clasa-pret select based on the pricing data.
    // When all options have finished updating (via AJAX), update both the oferta text and the final price.
    function updateSelectOptions(totalDays, discount = 0, costExtra = 0, startDate = "", endDate = "", startTime = "", endTime = "") {
        let options = $('.js-clasa-pret option');
        let plataAvans = $('select.js-plata-avans').val() || 0;
        let totalOptions = options.length;
        let processed = 0;

        options.each(function() {
            const option = $(this);
            const clasaPretId = option.val();

            if (clasaPretId != "") {
                $.ajax({
                    url: '/ajax/getOptionPrices.php',
                    method: 'POST',
                    data: { 
                        clasaPretId: clasaPretId,
                        totalDays: totalDays,
                        discount: discount,
                        costExtra: costExtra,
                        startDate: startDate,
                        endDate: endDate,
                        startTime: startTime,
                        endTime: endTime,
                        plataAvans: plataAvans
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.calculatedPrice) {
                            const updatedText = `${response.clasaPretName} - ${totalDays} zile (${response.calculatedPrice} Euro)`;
                            option.text(updatedText);
                            option.attr("pret", response.calculatedPrice);
                            $('.js-clasa-pret').selectpicker('refresh');
                        }
                        processed++;
                        if (processed === totalOptions) {
                            updateOfertaText();
                            updateFinalPrice();
                        }
                    },
                    error: function() {
                        processed++;
                        if (processed === totalOptions) {
                            updateOfertaText();
                            updateFinalPrice();
                        }
                    }
                });
            } else {
                processed++;
                if (processed === totalOptions) {
                    updateOfertaText();
                    updateFinalPrice();
                }
            }
        });
    }

    // Update the text input (.js-oferta-trimisa-text) with the comma‑separated texts of all selected options.
    function updateOfertaText(){
        var selectedOptionTexts = $('.js-oferta-trimisa option:selected').map(function(){
            return $(this).text();
        }).get();
        var joinedText = selectedOptionTexts.join(', ');
        $('.js-oferta-trimisa-text').val(joinedText);
    }

    // NEW: Update the final price field (.js-pret-final) based on the "pret" attribute
    // of the currently selected option in the .js-oferta-acceptata select.
    function updateFinalPrice(){
        var selectedOption = $('select.js-oferta-acceptata option:selected');
        var pretAcceptat = parseInt(selectedOption.attr('pret'));
        if (pretAcceptat) {
            $(".js-pret-final").val(pretAcceptat);
            $("select.js-status-oferta").val(16).change();
            $('select.js-status-oferta').selectpicker('refresh');
        } else {
            $(".js-pret-final").val("");
            $("select.js-status-oferta").val(18).change();
            $('select.js-status-oferta').selectpicker('refresh');
        }
    }

    // When date/time fields change, recalculate the days, update select options, and enable the inputs.
    function handleDateChange() {
        let startDate = $('.js-data-inceput').val();
        let endDate = $('.js-data-sfarsit').val();
        let startTime = $('.js-ora-inceput').val();
        let endTime = $('.js-ora-sfarsit').val();

        if (startDate && endDate && startTime && endTime) {
            $('select.js-clasa-pret').removeAttr("disabled").selectpicker('refresh');
            $('select.js-template-clase-pret').removeAttr("disabled").selectpicker('refresh');
            $('.js-discount-oferta').removeAttr("readonly");
            $('.js-cost-extra').removeAttr("disabled");
            $('.js-explicatie-cost-extra').removeAttr("disabled");
            $('select.js-plata-avans').removeAttr("disabled").selectpicker('refresh');


            let totalDays = calculateDays(startDate, endDate, startTime, endTime);
            let discount = parseFloat($('.js-discount-oferta').val()) || 0;
            let costExtra = parseFloat($('.js-cost-extra').val()) || 0;
            let plataAvans = $('select.js-plata-avans').val();
            // if(plataAvans == 29) { //29 means "Yes" is selected, so we add an extra 5 to the discount
            //     discount = discount + 5;
            // }
            // console.log(plataAvans);
            updateSelectOptions(totalDays, discount, costExtra, startDate, endDate, startTime, endTime);
            $(".js-durata-zile").val(totalDays);
        }
    }

    /* ---------------------------------------------------------------------
       EVENT LISTENERS
       --------------------------------------------------------------------- */

    // Update pricing when date/time inputs change.
    $('.js-data-inceput, .js-data-sfarsit, .js-ora-inceput, .js-ora-sfarsit').on('change', handleDateChange);

    // Update pricing options when discount changes.
    $('.js-discount-oferta, select.js-plata-avans, .js-cost-extra').on('change', function() {
        let startDate = $('.js-data-inceput').val();
        let endDate = $('.js-data-sfarsit').val();
        let startTime = $('.js-ora-inceput').val();
        let endTime = $('.js-ora-sfarsit').val();
        let totalDays = parseInt($(".js-durata-zile").val()) || 0;
        let discount = parseFloat($('.js-discount-oferta').val()) || 0;
        let costExtra = parseFloat($('.js-cost-extra').val()) || 0;
        let plataAvans = $('select.js-plata-avans').val();
        // if(plataAvans == 29) { //29 means "Yes" is selected, so we add an extra 5 to the discount
        //     discount = discount + 5;
        // }
        console.log(discount);
        // console.log(plataAvans);
        updateSelectOptions(totalDays, discount, costExtra, startDate, endDate, startTime, endTime);
    });



    // When the oferta acceptata select changes, update final price.
    $("select.js-oferta-acceptata").on('change', function(){
        updateFinalPrice();
    });

    // When either the multi-select (.js-oferta-trimisa) or discount changes, update oferta text.
    $('.js-oferta-trimisa, .js-discount-oferta').on('change', updateOfertaText);

    // When client dropdown changes, fetch istoric_client and possibly auto-fill discount.
    $('.js-client').on('change', function() {
        var clientId = $(this).val();
        if (clientId && clientId != "0") {
            $.ajax({
                url: '/ajax/getClientIstoric.php',
                type: 'POST',
                dataType: 'json',
                data: { client_id: clientId },
                success: function(response) {
                    if (response.istoric_client == 38) {
                        // Only pre-fill if discount is 0 or empty, and field is enabled
                        var $discount = $('.js-discount-oferta');
                        if (($discount.val() === "" || $discount.val() == 0) && !$discount.prop('disabled')) {
                            $discount.val(10).trigger('change');
                            // Optionally, you can show a notice to the user
                            // alert('Client cu ofertă acceptată: Discount precompletat cu 10%.');
                        }
                    }
                }
            });
        }
    });


    // When the template select changes, update the multi-select based on template values.
    $("select.js-template-clase-pret").on('change', function(){
        var idTemplate = parseInt($(this).find("option:selected").val());
        $.ajax({
            url: '/ajax/getOptionTemplates.php',
            type: 'POST',
            data: { idTemplate: idTemplate },
            success: function(response) {
                var data = JSON.parse(response);
                data.clasePret.forEach(function(value){
                    $('.js-oferta-trimisa option[value="' + value + '"]').prop("selected", true);
                });
                $('.js-oferta-trimisa').selectpicker('refresh').trigger('change');
            }, 
            error: function() {
                alert('A apărut o eroare.');
            }
        });
    });
});


//used for moving bookings
let moveMode = false;
let moveBooking = {};    // will hold { bookingId, startDate, endDate, startTime, endTime }


$(document).ready(function() {

    // 1) Click handler to enter “move mode”
    $(document).on('click', '.js-muta-rezervare', function(e) {
        e.stopPropagation();  // don’t trigger other handlers

        // if already in moveMode for this same booking, turn it off
        if (moveMode && moveBooking.bookingId === $(this).data('booking-id')) {
            moveMode = false;
            // clear highlight
            $('.my-bookings-table td.empty-cell').removeClass('bg-secondary').addClass('bg-white');
            return;
        }

        // read booking data
        moveBooking.bookingId   = $(this).data('booking-id');
        moveBooking.startDate   = $(this).data('start-date');
        moveBooking.endDate     = $(this).data('end-date');
        moveBooking.startTime   = $(this).data('start-time');
        moveBooking.endTime     = $(this).data('end-time');
        moveMode = true;
        // highlight that period across all cars:
        highlightDateRange(moveBooking.startDate, moveBooking.endDate);
        alert('Selectează o altă mașină din listă pentru a muta această rezervare.');
    });



    /* ---------------------------------------------------------------------
       OFFER CARD CLICK HANDLER
       --------------------------------------------------------------------- */
    $('.js-calendar-offer-card').on('click', function(e) {
        // If the click originated inside either toggle button, do nothing.
        if ($(e.target).closest('.js-permite-orice-masina, .js-arata-masini-libere, .js-cauta-masina').length > 0) {
            return;
        }

        // Toggle off if this card is already active.
        if ($(this).hasClass('active')) {
            $(this).removeClass('bg-info active').addClass('bg-white');
            // Hide and reset both toggle buttons.
            $(this).find('.js-permite-orice-masina, .js-arata-masini-libere, .js-cauta-masina')
                .hide()
                .removeClass('active btn-outline-primary')
                .addClass('btn-outline-light');
            // Show all rows and remove any cell highlight.
            $('.my-bookings-table tbody tr').show();
            $('.my-bookings-table td.empty-cell.bg-secondary')
                .removeClass('bg-secondary')
                .addClass('bg-white');
            return;
        }

        // Otherwise, activate this card and reset all others.
        $('.js-calendar-offer-card')
            .removeClass('bg-info active')
            .addClass('bg-white')
            .find('.js-permite-orice-masina, .js-arata-masini-libere, .js-cauta-masina')
                .hide()
                .removeClass('active btn-outline-primary')
                .addClass('btn-outline-light');
        $(this).removeClass('bg-white').addClass('bg-info active');
        // Show both toggle buttons for the active card.
        $(this).find('.js-permite-orice-masina, .js-arata-masini-libere, .js-cauta-masina').show();

        // Reapply filtering based on the active card's price class (default behavior).
        reapplyFilter($(this));
        
        // Scroll the container and highlight date range as before.
        const startDate = $(this).find('.js-calendar-card-start').text();
        const endDate = $(this).find('.js-calendar-card-end').text();
        const scrollContainer = $('.my-bookings-table').closest('.card');
        const startCell = $('.my-bookings-table th[data-cell-date="' + startDate + '"]');
        if (startCell.length) {
            const containerOffset = scrollContainer.offset().left;
            const cellOffset = startCell.offset().left;
            const scrollLeft = scrollContainer.scrollLeft() + (cellOffset - containerOffset) - 380;
            scrollContainer.animate({ scrollLeft: scrollLeft }, 600);
        }
        highlightDateRange(startDate, endDate);
    });


    /* ---------------------------------------------------------------------
       HELPER FUNCTIONS
       --------------------------------------------------------------------- */
    // This function highlights the date range columns between startDate and endDate.
    function highlightDateRange(startDate, endDate) {
        $('.my-bookings-table td.empty-cell').each(function() {
            const cellDate = $(this).data('cell-date');
            if (cellDate && cellDate >= startDate && cellDate <= endDate) {
                $(this).removeClass('bg-white').addClass('bg-secondary');
            } else {
                $(this).removeClass('bg-secondary').addClass('bg-white');
            }
        });
    }

    // This function re-applies the table filtering based on the active offer card and the states
    // of both toggle buttons ("Permite Orice Masina" and "Arata Doar Disponibile").
    function reapplyFilter($offerCard) {
        // Retrieve active offer card's data.
        let selectedIds = $offerCard.data('clase-pret').toString().split(',');
        let startDate = $offerCard.find('.js-calendar-card-start').text();
        let endDate = $offerCard.find('.js-calendar-card-end').text();

        // Check toggle button states.
        let permitActive = $offerCard.find('.js-permite-orice-masina').hasClass('active');
        let availableActive = $offerCard.find('.js-arata-masini-libere').hasClass('active');

        // Case 1: "Permite Orice Masina" active => ignore price class filter.
        if (permitActive) {
            // Start with all rows.
            $('.my-bookings-table tbody tr').show();
        } else {
            // Otherwise, apply price class filter.
            $('.my-bookings-table tbody tr').hide();
            selectedIds.forEach(function(id) {
                $('.my-bookings-table tbody tr[data-clasa-pret="' + id.trim() + '"]').show();
            });
        }

        // Now, if the "Arata Doar Disponibile" button is active, further filter rows to show only available ones.
        if (availableActive) {
            $('.my-bookings-table tbody tr').each(function() {
                let $row = $(this);
                let available = true;
                // For each cell that has a date attribute and represents a booking (not an empty cell):
                $row.find('td').each(function() {
                    let $cell = $(this);
                    if (!$cell.hasClass('empty-cell')) {
                        // Get cell's start date.
                        let cellStartDateStr = $cell.data('cell-date');
                        if (!cellStartDateStr) return;
                        let cellStart = new Date(cellStartDateStr);
                        // Determine cell span from colspan.
                        let colspan = parseInt($cell.attr('colspan')) || 1;
                        let cellEnd = new Date(cellStart);
                        cellEnd.setDate(cellEnd.getDate() + colspan - 1);
                        // Check for any overlap with the active period.
                        let activeStartDate = new Date(startDate);
                        let activeEndDate = new Date(endDate);
                        if (cellStart <= activeEndDate && cellEnd >= activeStartDate) {
                            available = false;
                            return false; // break out of cell loop
                        }
                    }
                });
                if (!available) {
                    $row.hide();
                }
            });
        }

        // Finally, highlight the active date range.
        highlightDateRange(startDate, endDate);
    }

    /* ---------------------------------------------------------------------
       "Permite Orice Masina" BUTTON HANDLER
       --------------------------------------------------------------------- */
    $(document).on('click', '.js-permite-orice-masina', function(e) {
        e.stopPropagation();
        var $button = $(this);
        // Get the parent offer card.
        var $offerCard = $button.closest('.js-calendar-offer-card');

        // Toggle the button's state.
        if (!$button.hasClass('active')) {
            $button.addClass('active').removeClass('btn-outline-light').addClass('btn-outline-primary');
        } else {
            $button.removeClass('active').removeClass('btn-outline-primary').addClass('btn-outline-light');
        }
        // Reapply filtering using the new combined state.
        reapplyFilter($offerCard);
    });

    /* ---------------------------------------------------------------------
       "Arata Doar Disponibile" BUTTON HANDLER
       --------------------------------------------------------------------- */
    $(document).on('click', '.js-arata-masini-libere', function(e) {
        e.stopPropagation();
        var $button = $(this);
        var $offerCard = $button.closest('.js-calendar-offer-card');

        // Toggle this button's active state.
        if (!$button.hasClass('active')) {
            $button.addClass('active').removeClass('btn-outline-light').addClass('btn-outline-primary');
        } else {
            $button.removeClass('active').removeClass('btn-outline-primary').addClass('btn-outline-light');
        }
        // Reapply filtering using the combined state of both buttons.
        reapplyFilter($offerCard);
    });

    $(document).ready(function() {
        // NEW: "Caută Mașina" button handler.
        // This button is assumed to be within the active offer card.
        $(document).on('click', '.js-cauta-masina', function(e) {
            e.stopPropagation();
            var $button = $(this);
            var $offerCard = $button.closest('.js-calendar-offer-card');

            // Retrieve booking details from the active card.
            var startDate = $offerCard.find('.js-calendar-card-start').text();
            var endDate = $offerCard.find('.js-calendar-card-end').text();
            var startTime = $offerCard.find('.js-calendar-ora-start').text();
            var endTime = $offerCard.find('.js-calendar-ora-end').text();
            // Get the price category information from the active card.
            var priceClasses = $offerCard.data('clase-pret'); // e.g., "12,15"

            // Provide visual feedback: disable button and update text.
            $button.prop("disabled", true).text("Căutare...");

            // AJAX call to search for an available car based on criteria.
            $.ajax({
                url: '/ajax/cautaMasina.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    priceClasses: priceClasses,
                    startDate: startDate,
                    endDate: endDate,
                    startTime: startTime,
                    endTime: endTime
                },
                success: function(response) {
                    $button.prop("disabled", false).text("Caută Mașina");
                    if (response.status === "success") {
                        var car = response.car;
                        var confirmMsg = "Mașina găsită: " + car.nume + ". Dorești să aloci această mașină rezervării?";
                        if (confirm(confirmMsg)) {
                            // Gather additional details from the active offer card.
                            var client = $offerCard.find('.js-calendar-card-client').data('client-id');
                            var pret = $offerCard.find('.js-calendar-card-pret').text();
                            var numeClient = $offerCard.find('.js-calendar-card-nume-client').text();
                            // Here, we use the found car's id and name as license.
                            var idMasina = car.id;
                            var license = car.nume;
                            var ofertaId = $offerCard.data('oferta-id');

                            // Call ajaxCreateInchiriere to create the booking.
                            ajaxCreateInchiriere(idMasina, client, startDate, endDate, startTime, endTime, pret, license, numeClient, ofertaId);
                        }
                    } else {
                        alert("Nu s-a găsit nicio mașină disponibilă în această categorie pentru perioada selectată.");
                    }
                },
                error: function(xhr, status, error) {
                    $button.prop("disabled", false).text("Caută Mașina");
                    alert("Eroare la căutarea mașinii: " + error);
                }
            });
        });
    });

});



//Booking Details in Modal From Calendar
$(document).ready(function(){
    // Bind click event on booking detail links
    $(document).on('click', '.js-booking-details-link', function(e) {
        e.preventDefault();
        var bookingId = $(this).data('booking-id');
        lastBookingIdForModal = bookingId; // Save it here
        // Get the booking ID from the data attribute.
        var bookingId = $(this).data('booking-id');
        // Also, grab the full booking URL (for the "View Full Booking" link) from the href attribute.
        var fullBookingUrl = $(this).attr('href');

        // Show a loading message in the modal content area.
        $('#bookingDetailsContent').html('<p>Loading booking details...</p>');

        // Make an AJAX call to retrieve booking details.
        $.ajax({
            url: '/ajax/getBookingDetails.php',
            type: 'POST',
            dataType: 'json',
            data: { booking_id: bookingId },
            success: function(response) {
                if (response.status === 'success') {
                    // Populate the modal body with the details returned.
                    $('#bookingDetailsContent').html(response.details);
                    // Set the full booking URL on the modal's "View Full Booking" button.
                    $('#bookingLink').attr('href', fullBookingUrl);
                    // Show the modal (using Bootstrap's modal API).
                    $('#bookingDetailsModal').modal('show');
                } else {
                    $('#bookingDetailsContent').html('<p>Error retrieving booking details.</p>');
                }
            },
            error: function() {
                $('#bookingDetailsContent').html('<p>Error retrieving booking details.</p>');
            }
        });
    });
});


// Handle click on the "Anulează" button
$(document).on('click', '.js-cancel-booking', function() {
    if (!lastBookingIdForModal) return;

    if (confirm("Ești sigur că vrei să anulezi această rezervare?")) {
        // Store current scroll position
        const $calendar = $('.calendar-container');
        const scrollLeft = $calendar.scrollLeft();

        $.ajax({
            url: '/ajax/cancelBooking.php',
            type: 'POST',
            dataType: 'json',
            data: { booking_id: lastBookingIdForModal },
            success: function(response) {
                if (response.status === "success") {
                    // Hide modal
                    $('#bookingDetailsModal').modal('hide');
                    // Re-ajax calendar content and restore scroll position
                    $.ajax({
                        url: window.location.href, // The same page (reload main content)
                        type: 'GET',
                        dataType: 'html',
                        success: function(pageHtml) {
                            // Parse returned HTML, find new calendar table, and replace it
                            const $newDoc = $('<div>').html(pageHtml);
                            const $newCalendar = $newDoc.find('.calendar-container');
                            $('.calendar-container').replaceWith($newCalendar);
                            // Restore scroll position instantly
                            $('.calendar-container').scrollLeft(scrollLeft);
                        },
                        error: function() {
                            location.reload(); // fallback
                        }
                    });
                } else {
                    alert("A apărut o eroare la anulare.");
                }
            },
            error: function() {
                alert("A apărut o eroare la anulare.");
            }
        });
    }
});


// When the DOM is ready, attach event handlers.
$(document).ready(function() {
    // --- HOVER EFFECTS ON CAR ROWS ---
    function hasActiveCard() {
        return $('.js-calendar-offer-card.active').length > 0;
    }

    // Add a hover effect on car rows only if an offer card is active.
    $('.my-bookings-table').on('mouseenter', '.js-calendar-car-column', function() {
        if (hasActiveCard() || moveMode) {
            $(this).addClass('bg-secondary text-white').css('cursor', 'pointer');
        }
    });

    $('.my-bookings-table').on('mouseleave', '.js-calendar-car-column', function() {
        if (hasActiveCard()  || moveMode) {
            $(this).removeClass('bg-secondary text-white').css('cursor', '');
        }
    });

    // --- CLICK HANDLER FOR CREATING A BOOKING ---
    $('.my-bookings-table').on('click', '.js-calendar-car-column', function() {


        //handle if we're in Move Mode, so we're moving an existing booking
        if (moveMode) {
            let newCarId = $(this).closest('tr').data('id-masina');
            // call new AJAX to move:
            ajaxMoveInchiriere(moveBooking.bookingId, newCarId,
                             moveBooking.startDate, moveBooking.endDate,
                             moveBooking.startTime, moveBooking.endTime);
            // reset moveMode
            moveMode = false;
            return;    // skip the normal “createInchiriere” logic
        }

        // Proceed only if there is an active offer card.
        let activeCard = $('.js-calendar-offer-card.active');
        if (!activeCard.length) return;

        // Retrieve the car ID from the parent row.
        let idMasina = $(this).closest('tr').data('id-masina');

        // Retrieve details from the active offer card.
        let startDate = activeCard.find('.js-calendar-card-start').text();
        let endDate = activeCard.find('.js-calendar-card-end').text();
        let startTime = activeCard.find('.js-calendar-ora-start').text();
        let endTime = activeCard.find('.js-calendar-ora-end').text();
        let pret = activeCard.find('.js-calendar-card-pret').text();
        let client = activeCard.find('.js-calendar-card-client').attr("data-client-id");
        let numeClient = activeCard.find('.js-calendar-card-nume-client').text();
        let license = $(this).closest('tr').data('license');
        let ofertaId = activeCard.data('oferta-id');
        // statusOferta is available but not used in our ajax call.
        // Call our AJAX creation function:
        ajaxCreateInchiriere(idMasina, client, startDate, endDate, startTime, endTime, pret, license, numeClient, ofertaId);
    });
});

/**
 * ajaxCreateInchiriere creates a new booking by sending a POST AJAX request to createInchiriere.php.
 *
 * @param {string} idMasina      - Car ID.
 * @param {string} client        - Client ID.
 * @param {string} startDate     - Booking start date (YYYY-MM-DD).
 * @param {string} endDate       - Booking end date (YYYY-MM-DD).
 * @param {string} startTime     - Booking start time (HH:MM).
 * @param {string} endTime       - Booking end time (HH:MM).
 * @param {string} pret          - Price value (as read from the active card).
 * @param {string} license       - Car license.
 * @param {string} numeClient    - Client name.
 * @param {string} ofertaId      - Offer ID.
 * @param {boolean} skipCheck    - If true, override minor overlap checks.
 * @param {boolean} skipCarCheck - If true, override car document/warning checks.
 * @param {boolean} skipOverlap  - If true, user has confirmed to override overlaps.
 */
function ajaxCreateInchiriere(idMasina, client, startDate, endDate, startTime, endTime, pret, license, numeClient, ofertaId, skipCheck = false, skipCarCheck = false, skipOverlap = false) {
    $.ajax({
        url: '/ajax/createInchiriere.php',
        type: 'POST',
        data: {
            masina_id: idMasina,
            client_id: client,
            data_inceput: startDate,
            data_sfarsit: endDate,
            ora_inceput: startTime,
            ora_sfarsit: endTime,
            pret: pret,
            license: license,
            numeClient: numeClient,
            ofertaId: ofertaId,
            skipCheck: skipCheck,
            skipCarCheck: skipCarCheck,
            skipOverlap: skipOverlap
        },
        success: function(response) {
            var data = JSON.parse(response);

            // --- Document and mileage warnings (errorCode "03") ---
            if (data.status === 'error' && (data.errorCode == "03")) {
                var message = data.message + " Sigur doresti sa continui?";
                if (confirm(message)) {
                    // When confirming document warnings, we override car warnings.
                    ajaxCreateInchiriere(idMasina, client, startDate, endDate, startTime, endTime, pret, license, numeClient, ofertaId, false, true, skipOverlap);
                }
            }
            // --- Heavy overlap warning (errorCode "04"): More than OVERLAPTIME hours overlap ---
            else if (data.status === 'error' && (data.errorCode == "04")) {
                var message = data.message + " Sigur doresti sa suprascrii rezervările existente?";
                if (confirm(message)) {
                    // Re-call with skipOverlap true to mark overlapped bookings as overwritten.
                    ajaxCreateInchiriere(idMasina, client, startDate, endDate, startTime, endTime, pret, license, numeClient, ofertaId, skipCheck, skipCarCheck, true);
                }
            }
            // --- Light overlap warning (errorCode "05"): Exactly OVERLAPTIME hours overlap ---
            else if (data.status === 'error' && (data.errorCode == "05")) {
                var message = data.message + " Sigur doresti sa continui (ambele rezervări vor rămâne active)?";
                if (confirm(message)) {
                    // Re-call with skipOverlap true. In the light overlap case, no booking status is changed.
                    ajaxCreateInchiriere(idMasina, client, startDate, endDate, startTime, endTime, pret, license, numeClient, ofertaId, skipCheck, skipCarCheck, true);
                }
            }
            // --- Success ---
            else {
                alert("Rezervarea se va realiza imediat - va rugam asteptati");
                // Redirect with a scrollDate parameter.
                window.location.href = window.location.pathname + '?scrollDate=' + encodeURIComponent(startDate);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}



/**
 * Move an existing booking to another car, with full confirm-and-retry on overlaps/docs.
 */
function ajaxMoveInchiriere(bookingId, newCarId, startDate, endDate, startTime, endTime,
                            skipCheck = false, skipCarCheck = false, skipOverlap = false) {
  $.ajax({
    url: '/ajax/mutaInchiriere.php',
    method: 'POST',
    dataType: 'json',
    data: {
      bookingId, newCarId,
      startDate, endDate, startTime, endTime,
      skipCheck, skipCarCheck, skipOverlap
    },
    success: function(res) {
      // Document warnings
      if (res.status==='error' && res.errorCode==='03') {
        if (confirm(res.message + ' Sigur?')) {
          ajaxMoveInchiriere(bookingId,newCarId,startDate,endDate,startTime,endTime,
                             false, true, skipOverlap);
        }
      }
      // Heavy overlap
      else if (res.status==='error' && res.errorCode==='04') {
        if (confirm(res.message)) {
          ajaxMoveInchiriere(bookingId,newCarId,startDate,endDate,startTime,endTime,
                             skipCheck, skipCarCheck, true);
        }
      }
      // Exact-4-hour overlap
      else if (res.status==='error' && res.errorCode==='05') {
        if (confirm(res.message)) {
          ajaxMoveInchiriere(bookingId,newCarId,startDate,endDate,startTime,endTime,
                             skipCheck, skipCarCheck, true);
        }
      }
      // Success or plain error
      else if (res.status==='success') {
        alert('Rezervarea a fost mutată cu succes.');
        window.location.href = window.location.pathname + '?scrollDate=' + encodeURIComponent(startDate);
      }
      else {
        alert('Eroare: ' + (res.message||'unknown'));
      }
    },
    error: function(xhr,status,err) {
      alert('Eroare AJAX: '+err);
    }
  });
}



//get the GET parameter
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(window.location.search);
    return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

//add simple buttons for accepting an offer or rejecting it as well as buttons for creating clients
$(document).ready(function() {
     $(".my-list-table tr").each(function() {
        var rowId = $(this).attr('data-item-id');
        var ofertaAcceptataTd = $(this).find('.js-oferta-acceptata');
        var discountTd = $(this).find('.js-discount-oferta');
        var pretFinalTd = $(this).find('.js-pret-final');
        var ofertaTrimisaTd = $(this).find('.js-oferta-trimisa');
        var statusOfertaTd = $(this).find('.js-status-oferta');

        if (ofertaAcceptataTd.length && statusOfertaTd.length) {
            // Move the 'js-oferta-acceptata' right after 'js-status-oferta'
            ofertaAcceptataTd.insertAfter(statusOfertaTd);
        }

        if (ofertaAcceptataTd.length && discountTd.length) {
            // Move the 'js-discount-oferta' right after 'js-oferta-acceptata'
            discountTd.insertAfter(ofertaAcceptataTd);
        }

        if (pretFinalTd.length && discountTd.length) {
            // Move the 'js-pret-final' right after 'js-discount-oferta'
            pretFinalTd.insertAfter(discountTd);
        }

        if (ofertaTrimisaTd.length && pretFinalTd.length) {
            // Move the 'js-oferta-trimisa' right after 'js-pret-final'
            ofertaTrimisaTd.insertAfter(pretFinalTd);
        }

        // Add buttons for accepting or rejecting offers
        var content = ofertaAcceptataTd.text();
        var itemId = $(this).attr('data-item-id');
        var status = statusOfertaTd.text();

        if (content == "" && status == "Trimisa") {
            ofertaAcceptataTd.append("<a href='/index/editare/oferte/" + itemId + "?action=acceptaOferta' class='btn btn-success js-accept-offer'>Accepta</a>");
            ofertaAcceptataTd.append("<a href='/oferte/refuza-oferta/" + itemId + "' class='btn btn-danger ms-3 js-refuse-offerrr'>Refuza</a>");
        }

        var client = $(this).find('.js-client a');
        var clientId = client.attr("data-item-id");
        // console.log(clientId);
        if(clientId == 0) {
            var numeClient = $(this).find('.js-nume-client-oferta');
            var prenumeClient = $(this).find('.js-prenume-client-oferta');
            var telefonClient = $(this).find('.js-telefon-client-oferta');
            var emailClient = $(this).find('.js-email-client-oferta');
            
            var addClientLink = "https://carhunt.logicnet.ro/index/creare/clienti?nume="+numeClient+" "+prenumeClient+"&telefon="+telefonClient+"&email="+emailClient+"&comingFromOffer="+rowId;
            // console.log(addClientLink);

            // client.append("<a href=''>Creeaza Client</a>");
        }
            
    });

});

//highlight the accepted offer field when coming from the accept button
$(document).ready(function() {
    // Check if the URL contains the action parameter with value "acceptaOferta"
    var urlParams = new URLSearchParams(window.location.search);
    var action = urlParams.get('action');

    if (action === 'acceptaOferta') {
        //change the offer to accepted
         $('.js-status-oferta').val(16).change(); // .change() triggers any event listeners attached to it
         $('.js-status-oferta').selectpicker('refresh');

        // Highlight the element with the class js-oferta-acceptata
        $('div.js-oferta-acceptata').addClass('highlighted pulse');

        // Remove the pulse effect after 2 seconds
        setTimeout(function() {
            $('.js-oferta-acceptata').removeClass('pulse');
        }, 4000);
    }
});

//set the default offer status to Trimisa
$(document).ready(function() {
    var statusOfertaId = $('select.js-status-oferta').val();
    // console.log(statusOfertaId);
    if(statusOfertaId == "") {
        $('select.js-status-oferta').val(18).change();
        $('select.js-status-oferta').selectpicker('refresh');
    }
});


// handle switching cars and/or dates for car handover
$(document).ready(function() {
    // ───────────────────────────────────────────────────────────────
    // Prevent selecting past dates/times on any pair of inputs
    // with classes .prevent-past-date and .prevent-past-time
    // ───────────────────────────────────────────────────────────────
    function preventPast() {
        var now   = new Date();
        var today = now.toISOString().slice(0,10);
        var hhmm  = ("0"+now.getHours()).slice(-2) + ":" + ("0"+now.getMinutes()).slice(-2);
        // set minimum on all date inputs
        $('.prevent-past-date').attr('min', today);
        // for each date, decide time min
        $('.prevent-past-date').each(function() {
            if ($(this).val() === today) {
                $('.prevent-past-time').attr('min', hhmm);
            } else {
                $('.prevent-past-time').attr('min', '00:00');
            }
        });
    }
    // initial
    preventPast();
    // whenever any of those dates changes
    $(document).on('change', '.prevent-past-date', preventPast);


    const modalSchimbariEl = document.getElementById('modalSchimbari');
    if (modalSchimbariEl) {
      modalSchimbari = new bootstrap.Modal(modalSchimbariEl);
    }
    let inchiriereId = '';
    let masinaId = '';
    let kmActuali = '';

    $(".js-schimbari-predare").on("click", function(e){
        e.preventDefault();
        inchiriereId = $(this).data('inchiriere-id');
        $('#inchiriereId').val(inchiriereId);

        masinaId = $(this).data('masina-id');
        $('#masinaId').val(masinaId);

        modalSchimbari.show();
    });

    $('#submitSchimbari').on('click', function() {
        if ($('#schimbariForm')[0].checkValidity()) {
            let formData = new FormData($('#schimbariForm')[0]);
            
            // Initial AJAX call without the 'confirm' flag
            $.ajax({
                url: '/ajax/realizeazaSchimbariPredare.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    let res;
                    try {
                        res = JSON.parse(response);
                    } catch (e) {
                        alert('Răspunsul de la server nu este valid.');
                        return;
                    }
                    
                    if (res.confirmation) {
                        if (confirm(res.confirmation)) {
                            formData.append('confirm', '1');
                            $.ajax({
                                url: '/ajax/realizeazaSchimbariPredare.php',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(finalResponse) {
                                    let finalRes;
                                    try {
                                        finalRes = JSON.parse(finalResponse);
                                    } catch (e) {
                                        alert('Răspunsul final de la server nu este valid.');
                                        return;
                                    }
                                    
                                    if(finalRes.success) {
                                        alert(finalRes.success);
                                        location.reload();
                                    } else if(finalRes.error) {
                                        alert(finalRes.error.join("\n"));
                                    }
                                },
                                error: function() {
                                    alert('A apărut o eroare la trimiterea datelor.');
                                }
                            });
                        }
                    }
                    else if (res.error) {
                        alert(res.error.join("\n"));
                    }
                    else if (res.success) {
                        alert(res.success);
                    }
                },
                error: function() {
                    alert('A apărut o eroare la trimiterea datelor.');
                }
            });
        } else {
            $('#schimbariForm')[0].reportValidity();
        }
    });

});


// trigger Modal for Retururi Changes
$(document).ready(function() {
    const modalUpdateReturEl = document.getElementById('modalUpdateRetur');
    if (modalUpdateReturEl) {
      modalUpdateRetur = new bootstrap.Modal(modalUpdateReturEl);
    }
    
    $('.js-schimbari-retur, .js-my-extinde-inchiriere').on('click', function(e) {
        e.preventDefault();
        let bookingId = $(this).data('inchiriere-id') || $('.js-single-details').data('id');
        $('#inchiriereIdRetur').val(bookingId);
        modalUpdateRetur.show();
    });

    $('#updateReturButton').on('click', function() {
        if ($('#updateReturForm')[0].checkValidity()) {
            let formData = new FormData($('#updateReturForm')[0]);
            // first call
            $.ajax({
                url: '/ajax/updateRetur.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    let res;
                    try {
                        res = JSON.parse(response);
                    } catch (e) {
                        alert('Răspuns invalid de la server.');
                        return;
                    }
                    
                    if (res.confirmation) {
                        if (confirm(res.confirmation)) {
                            formData.append('confirm', '1');
                            $.ajax({
                                url: '/ajax/updateRetur.php',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response2) {
                                    let res2;
                                    try {
                                        res2 = JSON.parse(response2);
                                    } catch(e) {
                                        alert('Răspuns invalid de la server.');
                                        return;
                                    }
                                    if (res2.success) {
                                        alert(res2.success);
                                        modalUpdateRetur.hide();
                                        location.reload();
                                    } else if (res2.error) {
                                        alert("Eroare: " + res2.error.join("\n"));
                                    }
                                },
                                error: function() {
                                    alert('A apărut o eroare la trimiterea datelor.');
                                }
                            });
                        }
                    } else if (res.success) {
                        alert(res.success);
                        modalUpdateRetur.hide();
                        location.reload();
                    } else if (res.error) {
                        alert("Eroare: " + res.error.join("\n"));
                    }
                },
                error: function() {
                    alert('A apărut o eroare la trimiterea datelor.');
                }
            });
        } else {
            $('#updateReturForm')[0].reportValidity();
        }
    });
});

//buttons for moving to an fro buffer
$(document).ready(function() {
    // Move to Buffer
    $(document).on('click', '.js-muta-in-buffer', function(e){
        e.preventDefault();
        var id = $(this).data('inchiriere-id');
        if(confirm("Sigur mutați această închiriere în Buffer?")) {
            $.post('/ajax/bufferHandler.php', { 
                mode: 'toBuffer', 
                inchiriere_id: id 
            }, function(res) {
                location.reload();
            }, 'json');
        }
    });

    // Remove from Buffer
    $(document).on('click', '.js-scoate-din-buffer', function(e){
        e.preventDefault();
        var id = $(this).data('inchiriere-id');
        var destinatie = $(this).siblings('select.js-buffer-destinatie').val();
        if(confirm("Sigur scoateți această închiriere din Buffer și o trimiteți la destinația selectată?")) {
            $.post('/ajax/bufferHandler.php', { 
                mode: 'fromBuffer', 
                inchiriere_id: id, 
                destinatie: destinatie 
            }, function(res) {
                location.reload();
            }, 'json');
        }
    });
});




function showIncasataLoading() {
  $('#infoSumaIncasata').html(
    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Se încarcă conversie...'
  );
}


//handle the pop up where we enter the data for the actual handover and return

$(document).ready(function() {
    const modalPredareReturEl = document.getElementById('modalPredareRetur');
    if (modalPredareReturEl) {
        modalPredareRetur = new bootstrap.Modal(modalPredareReturEl);
    }
    let inchiriereId = '';
    let masinaId = '';
    let kmActuali = '';

    // Open modal when a record button is clicked.
    $('.js-record-predare, .js-record-retur').on('click', function(e) {
        e.preventDefault();
        inchiriereId = $(this).data('inchiriere-id');
        $('#inregistrareInchiriereId').val(inchiriereId);
        masinaId = $(this).data('masina-id');
        $('#inregistrareMasinaId').val(masinaId);
        kmActuali = $(this).data('km-actuali');
        if ($(this).hasClass('js-record-predare')) {
            $('#inregistrareModalLabel').text('Date Predare');
            $('#inregistrareActionType').val('predare');
        } else {
            $('#inregistrareModalLabel').text('Date Retur');
            $('#inregistrareActionType').val('retur');
        }

        // Reset the conversion info and show loading each time modal is opened
        showIncasataLoading();

        let restPlataEUR = parseFloat($(this).data('rest-plata')) || 0;
        $.ajax({
            url: '/ajax/getEurRonRate.php',
            type: 'GET',
            dataType: 'json',
            success: function(rateRes) {
                let eurRon = rateRes.rate ? parseFloat(rateRes.rate) : null;
                if (eurRon) {
                    let restRON = Math.ceil(restPlataEUR * eurRon);
                    $('#sumaIncasatInfo').html(
                        `<div class="card border-info mb-3">
                            <div class="card-body py-2">
                                <strong>Suma de Încasat:</strong> ${restPlataEUR} EUR = ${restRON} RON
                            </div>
                        </div>`
                    );
                } else {
                    $('#sumaIncasatInfo').html('<div class="text-danger">Nu am putut obține cursul valutar.</div>');
                }
            }
        });

        modalPredareRetur.show();
    });

    // Submit the form after confirming the payment.
    $('#submitPredareRetur').on('click', function() {
        var $form = $('#predareReturForm');
        if ($form[0].checkValidity()) {
            // Check that the new kilometraj is not less than the current value.
            if (parseInt($("#kilometraj").val()) < parseInt(kmActuali)) {
                alert("Kilometrajul nu poate fi mai mic decat cel al masinii");
                return;
            }
            // Read payment fields.
            var incasatAmount = parseFloat($("#incasatAmount").val()) || 0;

            // Build the confirmation message.
            if (incasatAmount > 0) {
                var confMsg = "Confirmati ca ati incasat de la client " + incasatAmount + " RON?";
                if (!confirm(confMsg)) {
                    return;
                }
                submitPredareReturForm();
            } else {
                // If no payment amount is entered, simply submit.
                submitPredareReturForm();
            }
        } else {
            $form[0].reportValidity();
        }
    });

    function submitPredareReturForm() {
        let formData = new FormData($('#predareReturForm')[0]);
        $.ajax({
            url: '/ajax/inregistreazaPredare.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                } catch (e) {
                    alert("Răspuns invalid de la server.");
                    return;
                }
                if (data.status === 'success') {
                    alert("Datele au fost salvate cu succes!");
                    modalPredareRetur.hide();
                    location.reload();
                } else {
                    alert("Eroare: " + data.message);
                }
            },
            error: function() {
                alert("A apărut o eroare la trimiterea datelor.");
            }
        });
    }
});


//Function to add a hidden field called comingFromOffer to clients when we open a create client window straight from an offer
$(document).ready(function() {
    // Get the value of comingFromOffer
    const comingFromOfferValue = getUrlParameter('comingFromOffer');
    
    // Check if comingFromOffer exists and is not null
    if (comingFromOfferValue) {
        // Create a hidden input field
        $('<input>').attr({
            type: 'hidden',
            id: 'comingFromOffer',
            name: 'comingFromOffer',
            value: comingFromOfferValue
        }).appendTo('.js-create-form-clienti');
    }

    // Get the value of comingFromCalendar
    const comingFromCalendarValue = getUrlParameter('comingFromCalendar');
    
    // Check if comingFromCalendar exists and is not null
    if (comingFromCalendarValue) {
        // Create a hidden input field
        $('<input>').attr({
            type: 'hidden',
            id: 'comingFromCalendar',
            name: 'comingFromCalendar',
            value: comingFromCalendarValue
        }).appendTo('.js-create-form-clienti, .js-edit-form');
    }
});

//function to show and hide the extra info textarea in the predare reutr modal
$(document).ready(function() {
    // Initially hide the textarea parent div
    $('#extraInfoDiv').hide();
    
    // Listen for changes on the select element
    $('.js-predare-select').on('change', function() {
        if ($(this).val()) {
            // If a value is selected (not empty), show the div
            $('#extraInfoDiv').show();
        } else {
            // If the value is empty, hide the div again
            $('#extraInfoDiv').hide();
        }
    });
});


//Incasare Form
$(document).ready(function () {
    // Open modal on button click
    $('.js-my-incaseaza-plata').on('click', function (e) {
        e.preventDefault();
        var id = $(".js-single-details").data('id');  // Get the data-id from the clicked button
        var totalPlatit = $(this).attr("data-total-platit");
        var totalDePlata = $(this).attr("data-total-de-plata");
        var restDePlata = parseInt(totalDePlata) - parseInt(totalPlatit);
        $('#incasareModal').data('id', id);  // Store the id in the modal data
        $('#incasareModal #total-platit-pana-acum').html(totalPlatit);
        $('#incasareModal #rest-de-plata').html(restDePlata);
        $('#incasareModal').modal('show');   // Show the modal

    });

    // Update form action and validate the input
    $('#incasareForm').on('submit', function (e) {
        var id = $('#incasareModal').data('id');  // Retrieve the stored id
        var incasareValue = $('#incasareInput').val();  // Get the value from the input field

        // Validate if the input is a valid integer
        if (/^\d+$/.test(incasareValue)) {
            // Set the action attribute dynamically based on the input
            $(this).attr('action', '/inchirieri/inregistreaza-incasare/' + id + '/' + incasareValue);
        } else {
            // Prevent submission if validation fails
            e.preventDefault();
            // Show validation error
            $('#incasareInput').addClass('is-invalid');
        }
    });

    // Remove validation error when user starts typing again
    $('#incasareInput').on('input', function () {
        $(this).removeClass('is-invalid');
    });
});
